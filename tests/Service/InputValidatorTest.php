<?php

declare(strict_types=1);

use Commissions\CommissionTask\Service\InputValidator;
use PHPUnit\Framework\TestCase;

class InputValidatorTest extends TestCase
{
	public function testValidateDate()
	{
		$validator = new InputValidator();

		$this->assertTrue($validator->validateDate('2021-01-01'));
		$this->assertFalse($validator->validateDate('2021-01-32'));
		$this->assertContains('Invalid date: 2021-01-32', $validator->getErrors());
	}

	public function testValidateInteger()
	{
		$validator = new InputValidator();

		$this->assertTrue($validator->validateInteger(123));
		$this->assertFalse($validator->validateInteger('abc'));
		$this->assertContains('Invalid integer: abc', $validator->getErrors());
	}

	public function testValidateString()
	{
		$validator = new InputValidator();

		$this->assertTrue($validator->validateString('private', ['private', 'business']));
		$this->assertFalse($validator->validateString('invalid', ['private', 'business']));
		$this->assertContains('Invalid string: invalid, allowed values: private, business', $validator->getErrors());
	}

	public function testValidateFloat()
	{
		$validator = new InputValidator();

		$this->assertTrue($validator->validateFloat(123.45));
		$this->assertFalse($validator->validateFloat('abc'));
		$this->assertContains('Invalid float: abc', $validator->getErrors());
	}

	public function testValidateCSVRow()
	{
		$validator = new InputValidator();

		$validRow = ['2021-01-01', '1', 'private', 'deposit', '1000.00', 'EUR'];
		$invalidRow = ['2021-01-32', 'abc', 'invalid', 'invalid', 'abc', 123];

		$this->assertTrue($validator->validateCSVRow($validRow));
		$this->assertFalse($validator->validateCSVRow($invalidRow));
		$this->assertContains('Invalid date: 2021-01-32', $validator->getErrors());
		$this->assertContains('Invalid integer: abc', $validator->getErrors());
		$this->assertContains('Invalid string: invalid, allowed values: private, business', $validator->getErrors());
		$this->assertContains('Invalid string: invalid, allowed values: deposit, withdraw', $validator->getErrors());
		$this->assertContains('Invalid float: abc', $validator->getErrors());
		$this->assertContains('The input is not a string.', $validator->getErrors());
	}
}
