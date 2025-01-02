<?php

declare(strict_types=1);

namespace Commissions\CommissionTask\Service;

class Transaction
{
	public $transactions = [];
	public $userid;

	public function setUserId(int $userid): void
	{
		$this->userid = $userid;
	}

	public function getUserWithdrawTransactions(string $weekYear = ''): array
	{
		if (!isset($this->transactions[$this->userid])) {
			$this->transactions[$this->userid] = [];
		}

		if (!$weekYear) {
			return $this->transactions[$this->userid];
		}

		if (!isset($this->transactions[$this->userid][$weekYear])) {
			$this->transactions[$this->userid][$weekYear] = [];
		}

		return $this->transactions[$this->userid][$weekYear];
	}

	public function setUserWithdrawTransaction(string $weekYear, array $transaction): void
	{
		if ($transaction['operationType'] !== 'withdraw') {
			return;
		}
		if (!isset($this->transactions[$this->userid])) {
			$this->transactions[$this->userid] = [];
		}

		if (!isset($this->transactions[$this->userid][$weekYear])) {
			$this->transactions[$this->userid][$weekYear] = [];
		}

		$this->transactions[$this->userid][$weekYear][] = $transaction;
	}
}
