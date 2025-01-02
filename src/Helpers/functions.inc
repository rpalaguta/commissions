<?php

function loadEnv($filePath)
{
    if (!file_exists($filePath)) {
        throw new Exception("The .env file does not exist at: $filePath");
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        // Split key-value pairs
        [$key, $value] = array_map('trim', explode('=', $line, 2));

        // Remove quotes if value is wrapped
        $value = trim($value, "\"'");

        // Set environment variables
        putenv("$key=$value");
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}
