# Test Execution Guide

This directory contains the automated test suite for the spatial writer library.

## How tests are executed

Tests are run through Composer using the project scripts defined in the root configuration. In practice, the typical command is:

```bash
composer test
```

This runs the PHPUnit suite configured for the repository and validates the behavior of the spatial serialization and binary strategy implementations.

## Test structure

The tests are organized by component and geometry type. For example:

- unit tests for the strategy implementations
- coverage-oriented fixtures and expectations
- data provider-based cases for geometry conversion scenarios

All tests use PHPUnit data providers to exercise multiple input cases with a single test method.

## How the tests work

Each test usually performs three steps:

1. Create or load a geometry object.
2. Execute the strategy under test.
3. Compare the produced binary output with the expected hexadecimal value.

The expected values are stored directly in the test cases and are used to verify that the writer produces the correct Well-Known Binary (WKB) representation.

## Coverage

```bash
composer test-local
```

This command generates a coverage report at ./phpunit-cache/coverage.xml that can be imported by your IDE to track code coverage.

![Sunburst](https://codecov.io/gh/longitude-one/spatial-writer/graphs/sunburst.svg?token=NIFES3ETWH)