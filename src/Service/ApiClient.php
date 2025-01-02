<?php

declare(strict_types=1);

namespace Commissions\CommissionTask\Service;

class ApiClient
{
	private $apiKey;
	private $apiUrl;

	public function __construct()
	{
		$this->apiKey = getenv('API_KEY');
		$this->apiUrl = getenv('API_URL');
	}

	public function getRequest(string $endpoint = '', string $params = ''): string
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "$this->apiUrl/$endpoint?access_key=$this->apiKey".$params);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		if (curl_errno($ch)) {
			echo 'Curl error: '.curl_error($ch);

			return '';
		}
		curl_close($ch);

		return $response;
	}

	public function getAllRates(): array
	{
		$response = $this->getRequest('latest');
		$rates = json_decode($response, true);

		return $rates['rates'] ?? [];
	}
}
