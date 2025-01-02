# Commission Rate Calculator

A simple script to calculate commission rates for user operations.

## Requirements
- PHP installed on your system
- Composer for managing dependencies

## Installation and Usage

1. Install dependencies:
   ```bash
   composer install
   ```
   
2. Make a copy of .env.example and rename it to .env, then add your credentials where needed

3. Run the script:
   ```bash
   php script.php input.csv
   ```

Replace `input.csv` with your desired input file.

## Input File Format
The input file should be a CSV file with the following structure:
```
date,user_id,user_type,operation_type,amount,currency
```
Example of csv file would look like this:
```
2014-12-31,4,private,withdraw,1200.00,EUR
2015-01-01,4,private,withdraw,1000.00,EUR
```

## Output
The script outputs the calculated commission fees for each operation in a new line.
