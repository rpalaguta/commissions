<?php

declare(strict_types=1);

namespace Commissions\CommissionTask\Service;

class Transaction
{
	public $transactions = [];
	public $userid;

	public function setUserId(int $userid): void
	{
		// Set the user ID
		$this->userid = $userid;
	}

	public function getUserWithdrawTransactions(string $weekYear = ''): array
	{
		// Check if the user has any transactions
		if (!isset($this->transactions[$this->userid])) {
			$this->transactions[$this->userid] = [];
		}

		// If no weekYear is not provided, return all transactions
		if (!$weekYear) {
			return $this->transactions[$this->userid];
		}

		// Check if the user has any transactions for the given weekYear
		if (!isset($this->transactions[$this->userid][$weekYear])) {
			$this->transactions[$this->userid][$weekYear] = [];
		}

		// Return the transactions for the given weekYear
		return $this->transactions[$this->userid][$weekYear];
	}

	public function setUserWithdrawTransaction(string $weekYear, array $transaction): void
	{
		// Check if the operation type is not "withdraw"
		if ($transaction['operationType'] !== 'withdraw') {
			return;
		}

		// Check if the user has any transactions
		if (!isset($this->transactions[$this->userid])) {
			$this->transactions[$this->userid] = [];
		}

		// Check if the user has any transactions for the given weekYear
		if (!isset($this->transactions[$this->userid][$weekYear])) {
			$this->transactions[$this->userid][$weekYear] = [];
		}

		// Save the transaction
		$this->transactions[$this->userid][$weekYear][] = $transaction;
	}
}
