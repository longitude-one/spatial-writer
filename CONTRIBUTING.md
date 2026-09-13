# How to contribute to spatial-writer

This library is open to contributions. 
It comes with a set of quality tools to help you to maintain the quality of the code and pass the CI's tests. 

## How to run the quality tools

```bash
# install library-dependencies if not already done
composer install

# install quality tools
composer install-quality-tools

# launch quality checks
composer quality
```
 
In this repository we use PHP_CodeSniffer (phpcs), PHP CS Fixer, PHP Mess Detector (phpmd), and PHPStan to enforce and improve code quality: 

* **PHP_CodeSniffer** enforces the project's coding standards and flags style violations and suspicious constructs; 
* **PHP CS Fixer** automatically reformats source code to the configured style rules while preserving behavior; 
* **PHP Mess Detector** detects potential bugs, dead or unused code, and maintainability issues such as high cyclomatic complexity; 
* **PHPStan** performs advanced static analysis with type inference to surface type-related errors, incorrect API usage, and contract violations before runtime.


## How to run the quality tools

```bash
# Run all test
composer test-local
```
