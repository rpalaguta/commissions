<?php

declare(strict_types=1);

namespace Commissions\CommissionTask\Service;

class InputValidator
{
	private $errors = [];

	public function validateDate(string $date): bool
	{
		$d = \DateTime::createFromFormat('Y-m-d', $date);
		if (!$d || $d->format('Y-m-d') !== $date) {
			$this->errors[] = "Invalid date: $date";

			return false;
		}

		return true;
	}

	public function validateInteger($value): bool
	{
		if (filter_var($value, FILTER_VALIDATE_INT) === false) {
			$this->errors[] = "Invalid integer: $value";

			return false;
		}

		return true;
	}

	public function validateString($value, $allowedValues = null): bool
	{
		if (!is_string($value)) {
			$this->errors[] = 'The input is not a string.';
		}
		if (!$allowedValues) {
			return true;
		}
		if (!in_array($value, $allowedValues, true)) {
			$this->errors[] = "Invalid string: $value, allowed values: ".implode(', ', $allowedValues);

			return false;
		}

		return true;
	}

	public function validateFloat($value): bool
	{
		if (filter_var($value, FILTER_VALIDATE_FLOAT) === false) {
			$this->errors[] = "Invalid float: $value";

			return false;
		}

		return true;
	}

	public function validateCSVRow($row): bool
	{
		$this->errors = [];
		if (count($row) !== 6) {
			$this->errors[] = 'Invalid row length: '.count($row);

			return false;
		}

		list($date, $integer, $type, $transaction, $amount, $currency) = $row;

		$isValid = true;
		$isValid &= $this->validateDate($date);
		$isValid &= $this->validateInteger($integer);
		$isValid &= $this->validateString($type, ['private', 'business']);
		$isValid &= $this->validateString($transaction, ['deposit', 'withdraw']);
		$isValid &= $this->validateFloat($amount);
		$isValid &= $this->validateString($currency);

		return (bool) $isValid;
	}

	public function getErrors(): array
	{
		return $this->errors;
	}
}
