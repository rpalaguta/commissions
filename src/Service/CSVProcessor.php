<?php

declare(strict_types=1);

namespace Commissions\CommissionTask\Service;

class CSVProcessor
{
	private $validator;
	private $apiClientService;
	private $currencyService;
	private $transactionService;
	private $transactionProcessorService;

	public function __construct()
	{
		$this->validator = new InputValidator();
		$this->apiClientService = new ApiClient();
		$this->currencyService = new Currency($this->apiClientService);
		$this->transactionService = new Transaction();
		$this->transactionProcessorService = new TransactionProcessor($this->transactionService, $this->currencyService);
	}

	public function processFile(string $inputFile): void
	{
		// Check if the file exists
		if (!file_exists($inputFile)) {
			echo "File not found: $inputFile\n";
			exit(1);
		}

		// Open the file for reading
		if (($handle = fopen($inputFile, 'r')) !== false) {
			while (($data = fgetcsv($handle, 1000, ',')) !== false) {
				// Validate the CSV row
				if (!$this->validator->validateCSVRow($data)) {
					print_r($this->validator->getErrors());
					continue;
				}

				// Destructure the CSV row into variables
				list($date, $userId, $userType, $operationType, $amount, $currency) = $data;

				// Create the transaction data array
				$transactionData = [
					'date' => $date,
					'userType' => $userType,
					'operationType' => $operationType,
					'amount' => $amount,
					'currency' => $currency,
				];

				// Set user ID in the processor
				$this->transactionProcessorService->setUserId((int) $userId);

				// Process the transaction
				$commission = $this->transactionProcessorService->processTransaction($transactionData);
				echo "$commission\n";
			}

			fclose($handle);
		} else {
			echo "Error opening file: $inputFile\n";
			exit(1);
		}
	}
}
