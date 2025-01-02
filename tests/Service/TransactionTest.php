<?php

declare(strict_types=1);

use Commissions\CommissionTask\Service\Transaction;
use PHPUnit\Framework\TestCase;

class TransactionTest extends TestCase
{
	public function testSetUserId()
	{
		$transaction = new Transaction();
		$transaction->setUserId(1);

		$this->assertEquals(1, $transaction->userid);
	}

	public function testGetUserWithdrawTransactions()
	{
		$transaction = new Transaction();
		$transaction->setUserId(1);

		$weekYear = '2021-01';
		$transactions = $transaction->getUserWithdrawTransactions($weekYear);

		$this->assertIsArray($transactions);
		$this->assertEmpty($transactions);
	}

	public function testSetUserWithdrawTransaction()
	{
		$transaction = new Transaction();
		$transaction->setUserId(1);

		$weekYear = '2021-01';
		$withdrawTransaction = [
			'operationType' => 'withdraw',
			'amount' => 1000,
			'currency' => 'EUR',
		];

		$transaction->setUserWithdrawTransaction($weekYear, $withdrawTransaction);
		$transactions = $transaction->getUserWithdrawTransactions($weekYear);

		$this->assertCount(1, $transactions);
		$this->assertEquals($withdrawTransaction, $transactions[0]);
	}

	public function testSetUserWithdrawTransactionIgnoresNonWithdraw()
	{
		$transaction = new Transaction();
		$transaction->setUserId(1);

		$weekYear = '2021-01';
		$depositTransaction = [
			'operationType' => 'deposit',
			'amount' => 1000,
			'currency' => 'EUR',
		];

		$transaction->setUserWithdrawTransaction($weekYear, $depositTransaction);
		$transactions = $transaction->getUserWithdrawTransactions($weekYear);

		$this->assertEmpty($transactions);
	}
}
