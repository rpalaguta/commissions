<?php

declare(strict_types=1);

namespace Commissions\CommissionTask\Service;

class InputValidator
{
	private $errors = [];

	public function validateDate(string $date): bool
	{
		// Check if the date is in the correct format
		$d = \DateTime::createFromFormat('Y-m-d', $date);
		if (!$d || $d->format('Y-m-d') !== $date) {
			$this->errors[] = "Invalid date: $date";

			return false;
		}

		return true;
	}

	public function validateInteger($value): bool
	{
		// Check if the value is an integer
		if (filter_var($value, FILTER_VALIDATE_INT) === false) {
			$this->errors[] = "Invalid integer: $value";

			return false;
		}

		return true;
	}

	public function validateString($value, $allowedValues = null): bool
	{
		// Check if the value is a string
		if (!is_string($value)) {
			$this->errors[] = 'The input is not a string.';
		}

		// If no allowed values are provided, return true
		if (!$allowedValues) {
			return true;
		}

		// Check if the value is in the allowed values
		if (!in_array($value, $allowedValues, true)) {
			$this->errors[] = "Invalid string: $value, allowed values: ".implode(', ', $allowedValues);

			return false;
		}

		return true;
	}

	public function validateFloat($value): bool
	{
		// Check if the value is a float
		if (filter_var($value, FILTER_VALIDATE_FLOAT) === false) {
			$this->errors[] = "Invalid float: $value";

			return false;
		}

		return true;
	}

	public function validateCSVRow($row): bool
	{
		// Reset errors
		$this->errors = [];

		// Check if the row has the correct length
		if (count($row) !== 6) {
			$this->errors[] = 'Invalid row length: '.count($row);

			return false;
		}

		// Destructure the row
		list($date, $integer, $type, $transaction, $amount, $currency) = $row;

		// Validate the row
		$isValid = true;
		$isValid &= $this->validateDate($date);
		$isValid &= $this->validateInteger($integer);
		$isValid &= $this->validateString($type, ['private', 'business']);
		$isValid &= $this->validateString($transaction, ['deposit', 'withdraw']);
		$isValid &= $this->validateFloat($amount);
		$isValid &= $this->validateString($currency);

		// Return the validation result
		return (bool) $isValid;
	}

	public function getErrors(): array
	{
		// Return the errors
		return $this->errors;
	}
}
