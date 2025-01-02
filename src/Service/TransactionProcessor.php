<?php

declare(strict_types=1);

namespace Commissions\CommissionTask\Service;

class TransactionProcessor
{
	public $commissionRate;
	public $sumHistory;
	public $transactionCount;
	public $commisionRates;

	public function __construct(
		public Transaction $transaction,
		public Currency $currency,
	) {
		$this->commisionRates = [
			'private' => [
				'withdraw' => getenv('PRIVATE_WITHDRAW_COMMISSION') / 100,
				'deposit' => getenv('PRIVATE_DEPOSIT_COMMISSION') / 100,
			],
			'business' => [
				'withdraw' => getenv('BUSINESS_WITHDRAW_COMMISSION') / 100,
				'deposit' => getenv('BUSINESS_DEPOSIT_COMMISSION') / 100,
			],
		];
	}

	public function setUserId(int $userid): void
	{
		$this->transaction->setUserId($userid);
	}

	public function processTransaction(array $transaction): string
	{
		$this->commissionRate = $this->commisionRates[$transaction['userType']][$transaction['operationType']];

		$dateTime = new \DateTime($transaction['date']);

		// Get the formatted "Year-Week" in the ISO 8601 format
		$weekYear = $dateTime->format('o-W');

		$this->getTransactionHistory($weekYear);
		$transaction['amount'] = (float) $transaction['amount'];
		if ($transaction['currency'] !== 'EUR') {
			$transaction['amount'] = $this->currency->convert($transaction['amount'], $transaction['currency']);
		}

		$this->transaction->setUserWithdrawTransaction($weekYear, $transaction);

		$exceededAmount = $this->getExceededAmount($transaction);
		$commission = $this->currency->convert($exceededAmount * $this->commissionRate, $transaction['currency'], false);

		return $this->currency->round($commission, 2, $transaction['currency']);
	}

	public function getExceededAmount(array $currentTransaction): float
	{
		if (
			$currentTransaction['userType'] === 'business'
			|| $currentTransaction['operationType'] === 'deposit'
		) {
			return $currentTransaction['amount'];
		}

		if ($this->transactionCount >= 3) {
			return $currentTransaction['amount'];
		}

		if ($this->sumHistory >= 1000.00) {
			return $currentTransaction['amount'];
		}

		$freeAmount = 1000.00 - $this->sumHistory;
		if ($currentTransaction['amount'] <= $freeAmount) {
			return 0.0;
		}

		return $currentTransaction['amount'] - $freeAmount;
	}

	public function getTransactionHistory(string $weekYear): void
	{
		// Set transaction history
		$transactionHistory = $this->transaction->getUserWithdrawTransactions($weekYear);

		if (!$transactionHistory) {
			$this->sumHistory = $this->transactionCount = 0;

			return;
		}

		$this->transactionCount = count($transactionHistory);

		$this->sumHistory = array_sum(array_column($transactionHistory, 'amount'));

		return;
	}
}
