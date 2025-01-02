<?php

declare(strict_types=1);

namespace Commissions\CommissionTask\Service;

class TransactionProcessor
{
	public $commissionRate;
	public $sumHistory;
	public $transactionCount;
	public $commisionRates;
	public $weeklyWithdrawLimit;

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
		$this->weeklyWithdrawLimit = getenv('WEEKLY_WITHDRAW_LIMIT');
	}

	public function setUserId(int $userid): void
	{
		$this->transaction->setUserId($userid);
	}

	public function processTransaction(array $transaction): string
	{
		// Set the commission rate
		$this->commissionRate = $this->commisionRates[$transaction['userType']][$transaction['operationType']];

		// Get the formatted "Year-Week" in the ISO 8601 format
		$dateTime = new \DateTime($transaction['date']);
		$weekYear = $dateTime->format('o-W');

		// Get the transaction history
		$this->getTransactionHistory($weekYear);

		// Convert the amount to EUR
		$transaction['amount'] = (float) $transaction['amount'];
		if ($transaction['currency'] !== 'EUR') {
			$transaction['amount'] = $this->currency->convert($transaction['amount'], $transaction['currency']);
		}

		// Save the transaction
		$this->transaction->setUserWithdrawTransaction($weekYear, $transaction);

		// Get the exceeded amount
		$exceededAmount = $this->getExceededAmount($transaction);

		// Calculate the commission
		$commission = $this->currency->convert($exceededAmount * $this->commissionRate, $transaction['currency'], false);

		return $this->currency->round($commission, 2, $transaction['currency']);
	}

	public function getExceededAmount(array $currentTransaction): float
	{
		// Check if the user is a business or the operation type is deposit
		if (
			$currentTransaction['userType'] === 'business'
			|| $currentTransaction['operationType'] === 'deposit'
		) {
			return $currentTransaction['amount'];
		}

		// Check if the user havent exceeded the free transactions limit
		if ($this->transactionCount >= 3) {
			return $currentTransaction['amount'];
		}

		// Check if the user havent exceeded the free amount limit
		if ($this->sumHistory >= $this->weeklyWithdrawLimit) {
			return $currentTransaction['amount'];
		}

		// Calculate the free amount
		$freeAmount = $this->weeklyWithdrawLimit - $this->sumHistory;

		// Check if the current transaction amount is less than or equal to the free amount
		if ($currentTransaction['amount'] <= $freeAmount) {
			return 0.0;
		}

		// Return the exceeded amount
		return $currentTransaction['amount'] - $freeAmount;
	}

	public function getTransactionHistory(string $weekYear): void
	{
		// Set transaction history
		$transactionHistory = $this->transaction->getUserWithdrawTransactions($weekYear);

		// If no history, set the sum and count to 0
		if (!$transactionHistory) {
			$this->sumHistory = $this->transactionCount = 0;

			return;
		}

		// Set the transaction count
		$this->transactionCount = count($transactionHistory);

		// Set the sum of the transaction history
		$this->sumHistory = array_sum(array_column($transactionHistory, 'amount'));

		return;
	}
}
