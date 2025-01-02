<?php

declare(strict_types=1);

namespace Commissions\CommissionTask\Service;

class Currency
{
	private $rates = [];
	private $currenciesWithNoDecimals = ['JPY'];

	public function __construct(
		private ApiClient $apiClient,
	) {
	}

	public function round($amount, int $precision, string $currency = 'EUR'): string
	{
		// Round the amount to the specified precision
		$factor = pow(10, $precision);

		$number = number_format(ceil((float) $amount * $factor) / $factor, $precision, '.', '');

		// Check if the currency has no decimals
		if (in_array($currency, $this->currenciesWithNoDecimals, true)) {
			$number = number_format(ceil((float) $amount), 0, '.', '');

			return $number;
		}

		// Return the rounded number
		return $number;
	}

	public function fetchRatesFromApi()
	{
		// Fetch the rates from the API
		$rates = $this->apiClient->getAllRates();
		if (!$rates) {
			echo 'Failed to fetch rates from the API';
			exit;
		}

		// Set the rates
		$this->rates = $rates;
	}

	public function getRate(string $currency): float
	{
		// Fetch the rates if they are not set
		if (!$this->rates) {
			$this->fetchRatesFromApi();
		}

		// Return the rate for the specified currency
		return $this->rates[$currency] ?? 1.0;
	}

	public function convert(float $amount, string $currency, bool $toEUR = true): float
	{
		// Get the rate for the specified currency
		$rate = $this->getRate($currency);
		// Convert the amount depending on the direction
		if ($toEUR) {
			$convertedAmount = $amount / $rate; // Convert to EUR
		} else {
			$convertedAmount = $amount * $rate; // Convert from EUR to the specified currency
		}

		// Return the converted amount, rounded to 4 decimal places
		return (float) round($convertedAmount, 4);
	}
}
