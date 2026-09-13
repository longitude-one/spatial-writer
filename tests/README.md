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
    $collection = new GeometryCollection(4326);
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

## How to create test for the Extended WKB Strategy

This is the same process, but connect on a PostGis Database, and use this kind of request:

```sql
SELECT ST_AsEwkb(ST_GeomFromText('POINT(42.1 42.42)', 4326));
```

## How to create test for the WKB Strategy

This is the same process, but connect on a PostGis Database, and use this kind of request:

```sql
SELECT ST_AsBinary(ST_GeomFromText('POINT(42.1 42.42)', 4326));
```

## Coverage

```bash
composer test-local
```

This command generates a coverage report at ./phpunit-cache/coverage.xml that can be imported by your IDE to track code coverage.

![Sunburst](https://codecov.io/gh/longitude-one/spatial-writer/graphs/sunburst.svg?token=NIFES3ETWH)


## WKT strategy

Run `vendor/bin/phpunit --no-coverage --filter WktStrategyTest` for text output.
The datasets exercise each available Geometry/Geography class and dimension,
empty values, nested collections, polygon holes, numeric precision, and errors.
Compare complete WKT strings, including parentheses and dimension markers.
PostGIS `SELECT ST_AsText(ST_GeomFromText('POINT(42.1 42.42)', 4326));`
provides reference output; whitespace around punctuation may differ.

### Explicit WKT examples

`LongitudeOne/SpatialWriter/Tests/Unit/Strategy/Wkt/Examples/` contains eight
standalone test classes: `GeometryXyTest`, `GeometryXyzTest`, `GeometryXymTest`,
`GeometryXyzmTest`, and their `Geography` equivalents.

Each class spells out one populated and one empty example for every concrete
type available in that family and dimension. Each test contains a direct
constructor call with literal coordinates and a complete literal WKT assertion.
There are no data providers, loops, shared fixtures, or calculated expected
strings in these files. Populated and empty tests are adjacent.

The 136 examples cover 68 concrete classes. Polyhedral surfaces are available
only in XYZ and XYZM. Examples include polygon holes, multiple members, mixed
collections, and adjacent surface patches.

Run this additional suite independently:

```bash
vendor/bin/phpunit --no-coverage tests/LongitudeOne/SpatialWriter/Tests/Unit/Strategy/Wkt/Examples
```


## EWKT strategy

Run `vendor/bin/phpunit --no-coverage --filter EwktStrategyTest` for explicit
EWKT examples. These tests cover nonzero, zero, and default SRIDs; empty
values; Z/M/ZM dimensions; nested collections with a single outer SRID
prefix; repeated conversions; and integration with `Writer`.
