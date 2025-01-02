<?php

declare(strict_types=1);

use Commissions\CommissionTask\Service\ApiClient;
use Commissions\CommissionTask\Service\Currency;
use PHPUnit\Framework\TestCase;

class CurrencyTest extends TestCase
{
	public function testRound()
	{
		// Mock ApiClient
		$apiClientMock = $this->createMock(ApiClient::class);

		// Simulate fetching rates using the mocked ApiClient
		$apiClientMock->method('getAllRates')->willReturn([
			'USD' => 1.1497,
			'JPY' => 129.53,
			'EUR' => 1.0,
		]);

		$currency = new Currency($apiClientMock);

		$this->assertEquals('100.00', $currency->round(100, 2));
		$this->assertEquals('100', $currency->round(100, 0));
		$this->assertEquals('100.13', $currency->round(100.1234, 2));
		$this->assertEquals('100.124', $currency->round(100.1234, 3));
	}

	public function testGetRate()
	{
		// Mock ApiClient
		$apiClientMock = $this->createMock(ApiClient::class);

		// Mock getAllRates to set the rates for Currency
		$apiClientMock->method('getAllRates')->willReturn([
			'USD' => 1.1497,
			'JPY' => 129.53,
			'EUR' => 1.0,
		]);

		$currency = new Currency($apiClientMock);

		// Now that the rates are mocked, this test will pass using the mocked values
		$this->assertEquals(1.1497, $currency->getRate('USD'));
		$this->assertEquals(129.53, $currency->getRate('JPY'));
		$this->assertEquals(1.0, $currency->getRate('EUR'));
	}

	public function testConvert()
	{
		// Mock ApiClient
		$apiClientMock = $this->createMock(ApiClient::class);

		// Mock getAllRates to set the rates for Currency
		$apiClientMock->method('getAllRates')->willReturn([
			'USD' => 1.1497,
			'JPY' => 129.53,
			'EUR' => 1.0,
		]);

		$currency = new Currency($apiClientMock);

		// Testing the conversion method
		$this->assertEquals(round(100 / 1.1497, 4), $currency->convert(100, 'USD', true));
		$this->assertEquals(round(100 * 1.1497, 4), $currency->convert(100, 'USD', false));
		$this->assertEquals(round(100 / 129.53, 4), $currency->convert(100, 'JPY', true));
		$this->assertEquals(round(100 * 129.53, 4), $currency->convert(100, 'JPY', false));
	}
}
