<?php

declare(strict_types=1);

use Commissions\CommissionTask\Service\ApiClient;
use Commissions\CommissionTask\Service\Currency;
use Commissions\CommissionTask\Service\Transaction;
use Commissions\CommissionTask\Service\TransactionProcessor;
use PHPUnit\Framework\TestCase;

class TransactionProcessorTest extends TestCase
{
	public function testProcessTransaction()
	{
		// Mock ApiClient
		$apiClientMock = $this->createMock(ApiClient::class);
		// Simulate fetching rates using the mocked ApiClient
		$apiClientMock->method('getAllRates')->willReturn([
			'USD' => 1.1497,
			'JPY' => 129.53,
			'EUR' => 1.0,
		]);
		$transaction = new Transaction();
		$currency = new Currency($apiClientMock);

		// Create the processor instance
		$processor = new TransactionProcessor($transaction, $currency);

		// 1st test: Transaction should return 0.00 (since there is no transaction history)
		$processor->setUserId(4);
		$transaction = [
			'date' => '2014-12-31',
			'userType' => 'private',
			'operationType' => 'withdraw',
			'amount' => 1200.00,
			'currency' => 'EUR',
		];

		$commission = $processor->processTransaction($transaction);
		$this->assertEquals('0.60', $commission);

		// 2nd test: A higher amount should return 0.00 as well (if within free amount)
		$processor->setUserId(4);
		$transaction = [
			'date' => '2015-01-01',
			'userType' => 'private',
			'operationType' => 'withdraw',
			'amount' => 1000.00,
			'currency' => 'EUR',
		];

		$commission = $processor->processTransaction($transaction);
		$this->assertEquals('3.00', $commission);

		// 3rd test:
		$processor->setUserId(4);
		$transaction = [
			'date' => '2016-01-05',
			'userType' => 'private',
			'operationType' => 'withdraw',
			'amount' => 1000.00,
			'currency' => 'EUR',
		];

		$commission = $processor->processTransaction($transaction);
		$this->assertEquals('0.00', $commission);

		// 4th test:
		$processor->setUserId(1);
		$transaction = [
			'date' => '2016-01-05',
			'userType' => 'private',
			'operationType' => 'deposit',
			'amount' => 200.00,
			'currency' => 'EUR',
		];

		$commission = $processor->processTransaction($transaction);
		$this->assertEquals('0.06', $commission);

		// 5th test:
		$processor->setUserId(2);
		$transaction = [
			'date' => '2016-01-06',
			'userType' => 'business',
			'operationType' => 'withdraw',
			'amount' => 300.00,
			'currency' => 'EUR',
		];

		$commission = $processor->processTransaction($transaction);
		$this->assertEquals('1.50', $commission);

		// 6th test:
		$processor->setUserId(1);
		$transaction = [
			'date' => '2016-01-06',
			'userType' => 'private',
			'operationType' => 'withdraw',
			'amount' => 30000.00,
			'currency' => 'JPY',
		];

		$commission = $processor->processTransaction($transaction);
		$this->assertEquals('0', $commission);

		// 7th test:
		$processor->setUserId(1);
		$transaction = [
			'date' => '2016-01-07',
			'userType' => 'private',
			'operationType' => 'withdraw',
			'amount' => 1000.00,
			'currency' => 'EUR',
		];

		$commission = $processor->processTransaction($transaction);
		$this->assertEquals('0.70', $commission);

		// 8th test:
		$processor->setUserId(1);
		$transaction = [
			'date' => '2016-01-07',
			'userType' => 'private',
			'operationType' => 'withdraw',
			'amount' => 100.00,
			'currency' => 'USD',
		];

		$commission = $processor->processTransaction($transaction);
		$this->assertEquals('0.30', $commission);

		// 9th test:
		$processor->setUserId(1);
		$transaction = [
			'date' => '2016-01-07',
			'userType' => 'private',
			'operationType' => 'withdraw',
			'amount' => 100.00,
			'currency' => 'EUR',
		];

		$commission = $processor->processTransaction($transaction);
		$this->assertEquals('0.30', $commission);

		// 10th test:
		$processor->setUserId(2);
		$transaction = [
			'date' => '2016-01-10',
			'userType' => 'business',
			'operationType' => 'deposit',
			'amount' => 10000.00,
			'currency' => 'EUR',
		];

		$commission = $processor->processTransaction($transaction);
		$this->assertEquals('3.00', $commission);

		// 11th test:
		$processor->setUserId(3);
		$transaction = [
			'date' => '2016-01-10',
			'userType' => 'private',
			'operationType' => 'withdraw',
			'amount' => 1000.00,
			'currency' => 'EUR',
		];

		$commission = $processor->processTransaction($transaction);
		$this->assertEquals('0.00', $commission);

		// 12th test:
		$processor->setUserId(1);
		$transaction = [
			'date' => '2016-02-15',
			'userType' => 'private',
			'operationType' => 'withdraw',
			'amount' => 300.00,
			'currency' => 'EUR',
		];

		$commission = $processor->processTransaction($transaction);
		$this->assertEquals('0.00', $commission);

		// 13th test:
		$processor->setUserId(5);
		$transaction = [
			'date' => '2016-02-19',
			'userType' => 'private',
			'operationType' => 'withdraw',
			'amount' => 3000000.00,
			'currency' => 'JPY',
		];

		$commission = $processor->processTransaction($transaction);
		$this->assertEquals('8612', $commission);
	}

	public function testGetExceededAmount()
	{
		$transactionMock = $this->createMock(Transaction::class);
		$currencyMock = $this->createMock(Currency::class);

		$processor = new TransactionProcessor($transactionMock, $currencyMock);

		$currentTransaction = [
			'userType' => 'private',
			'amount' => 1500,
			'operationType' => 'withdraw',
			'currency' => 'EUR',
		];

		$processor->sumHistory = 500;
		$processor->transactionCount = 2;

		$exceededAmount = $processor->getExceededAmount($currentTransaction);
		$this->assertEquals(1000, $exceededAmount);

		$processor->sumHistory = 1000;
		$processor->transactionCount = 3;

		$exceededAmount = $processor->getExceededAmount($currentTransaction);
		$this->assertEquals(1500, $exceededAmount);
	}

	public function testGetTransactionHistory()
	{
		$transactionMock = $this->createMock(Transaction::class);
		$currencyMock = $this->createMock(Currency::class);

		$transactionMock->method('getUserWithdrawTransactions')
			->willReturn([
				['amount' => 500, 'currency' => 'EUR'],
				['amount' => 300, 'currency' => 'EUR'],
			]);

		$processor = new TransactionProcessor($transactionMock, $currencyMock);

		$processor->setUserId(1);
		$processor->getTransactionHistory('2021-01');

		$this->assertEquals(2, $processor->transactionCount);
		$this->assertEquals(800, $processor->sumHistory);
	}
}
