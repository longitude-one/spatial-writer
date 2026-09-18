# Repository Guidelines

## Project Structure & Module Organization

This PHP library converts spatial objects to WKB, EWKB, MySQL binary formats, and WKT text. Source lives in `lib/LongitudeOne/SpatialWriter/`, using Composer PSR-0 autoloading. `Writer` delegates conversion to `Strategy/StrategyInterface`; MySQL encoding helpers live in `Strategy/MySQL/`. Strategies preserve X/Y coordinate order; SRIDs do not trigger axis swapping.

Tests mirror the source under `tests/LongitudeOne/SpatialWriter/Tests/Unit/`. Quality tools have separate Composer installations and configuration under `quality/`. CI workflows live in `.github/workflows/`.

## Documentation

Documentation lives in [`docs/`](docs/). Read [`docs/strategies.md`](docs/strategies.md) for strategy behavior, examples, limitations, and external references. Keep documentation in English and update it when formats or public APIs change. See [`tests/README.md`](tests/README.md) for testing instructions and [`CONTRIBUTING.md`](CONTRIBUTING.md) for contribution guidance.

## Build, Test, and Development Commands

Use PHP 8.4 or newer and Composer 2; CI exercises PHP 8.4 and 8.5. There is no application server or build step.

- `composer install`: install library and test dependencies.
- `composer install-quality-tools`: install the four quality tool environments.
- `composer test`: run PHPUnit.
- `vendor/bin/phpunit --no-coverage --filter WkbStrategyTest`: run focused tests without coverage.
- `composer test-local`: run tests and print the coverage summary; enable PCOV or Xdebug coverage first. Reports go to `.phpunit-cache/`.
- `composer quality`: apply PHP CS Fixer, then run PHPStan at level 9, PHP Mess Detector, and PHP_CodeSniffer. Review formatter changes before committing.

## Coding Style & Naming Conventions

Use four-space indentation, `declare(strict_types=1);`, explicit types, and the existing file headers. Follow the configured PHP CS Fixer rules and PSR-2-based CodeSniffer standard. Use PascalCase class names, camelCase methods and variables, and uppercase constants. Match namespaces to directory paths.

For PHP changes, run `composer quality` and resolve all reported violations before submitting. PHP CS Fixer's configuration at `quality/php-cs-fixer/.php-cs-fixer.php` is authoritative; preserve its formatting. Also satisfy PHPStan, PHP Mess Detector, and PHP_CodeSniffer using their repository configurations. Do not weaken rules or add suppressions just to pass checks.

Use `LongitudeOne\Core\Enum\GeometryTypeEnum` for geometry types. Current fixtures use `SpatialTypes\Types\Dimension2` classes. Pass SRIDs and collection elements through constructors, keeping member SRIDs consistent with their parent.

## Testing Guidelines

Use PHPUnit 13, `*Test.php` classes and descriptive `test*` methods. New tests must be explicit and readable by a human: write complete constructors, literal coordinates and complete expected outputs directly in each test. Do not build geometry fixtures or expected results using loops, generators, dynamic class names or shared fixture factories. Prefer separate test methods over data providers. Assert exact hexadecimal output for binary changes, including SRID and axis-order cases. See `tests/README.md` for MySQL/PostGIS queries that generate reference fixtures. Coverage is reported in CI; no numeric minimum is configured.

## Commit & Pull Request Guidelines

Recent commits use prefixes such as `fix:`, `refactor:`, and `chore:` followed by a concise action. Keep commits focused. Describe the problem, resulting behavior, and validation commands in pull requests; link relevant issues and include binary examples when serialization changes.

Do not commit `vendor/`, `.phpunit-cache/`, or the ignored root `composer.lock`.
