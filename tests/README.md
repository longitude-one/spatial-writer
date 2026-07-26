# Test Execution Guide

This directory contains the automated test suite for the spatial writer library.

## How tests are executed

Tests are run through Composer using the project scripts defined in the root configuration. In practice, the typical command is:

```bash
composer test
```

This runs the PHPUnit suite configured for the repository and validates the behavior of the spatial serialization and binary strategy implementations.

## How to create test for the MySql Strategy

Connect to your MySql database and execute a request like this one:

```sql
SELECT UNHEX(HEX(ST_GeomFromText('GEOMETRYCOLLECTION()', 4326)))
```

```txt
0xe6100000010700000000000000
```

Remove the `0x` before the result
`e6100000010700000000000000` can be used as a constant to test your geometry.

```php
# LongitudeOne/SpatialWriter/Tests/Unit
public function testCollection(CollectionInterface $collection, string $expected): void
{
    //Create an empty spatial collection with 4326 as SRID
    $collection - new Collection()->setSrid(4326);
    //Assert that expected string is the same than the result of our strategy
    static::assertSame(
        'e6100000010700000000000000', 
        mb_strtlozer(
            bin2hex(
                $this->strategy->executeStrategy($collection)
            )
        )
    );
}
```

Of course, you should use data providers.

## How to create test for the EWKB Strategy

This is the same process, but connect on a PostGis Database, and use this kind of request:

```sql
SELECT ST_ASEWKB(ST_GeomFromText('POINT(42.1 42.42)', 4326));
```

## Coverage

```bash
composer test-local
```

This command generates a coverage report at ./phpunit-cache/coverage.xml that can be imported by your IDE to track code coverage.

![Sunburst](https://codecov.io/gh/longitude-one/spatial-writer/graphs/sunburst.svg?token=NIFES3ETWH)
