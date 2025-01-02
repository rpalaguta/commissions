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
		$factor = pow(10, $precision);

		$number = number_format(ceil((float) $amount * $factor) / $factor, $precision, '.', '');
		if (in_array($currency, $this->currenciesWithNoDecimals, true)) {
			$number = number_format(ceil((float) $amount), 0, '.', '');

			return $number;
		}

		return $number;
	}

	public function fetchRatesFromApi()
	{
		$rates = $this->apiClient->getAllRates();
		if (!$rates) {
			echo 'Failed to fetch rates from the API';
			exit;
		}

		$this->rates = $rates;
	}

	public function getRate(string $currency): float
	{
		if (!$this->rates) {
			$this->fetchRatesFromApi();
		}

		return $this->rates[$currency] ?? 1.0;
	}

	public function convert(float $amount, string $currency, bool $toEUR = true): float
	{
		$rate = $this->getRate($currency);
		// Convert the amount depending on the direction
		if ($toEUR) {
			$convertedAmount = $amount / $rate; // Convert to EUR
		} else {
			$convertedAmount = $amount * $rate; // Convert from EUR to the specified currency
		}

		return (float) round($convertedAmount, 4);
	}
}
