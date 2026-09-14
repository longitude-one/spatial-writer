# Test Execution Guide

This directory contains the automated test suite for the spatial writer library.

## How tests are executed

Tests are run through Composer using the project scripts defined in the root configuration. In practice, the typical command is:

```bash
composer test
```

This runs the PHPUnit suite configured for the repository and validates the behavior of the spatial serialization and binary strategy implementations.

## MySQL internal storage references

Use `HEX()` directly on the geometry to include MySQL's four-byte SRID prefix.
For longitude-first geographic coordinates, explicitly select the input order:

```sql
SELECT HEX(ST_GeomFromText('POINT(2.35 48.86)', 4326, 'axis-order=long-lat'));
-- E61000000101000000CDCCCCCCCCCC0240AE47E17A146E4840
```

Without that option, 4326 input defaults to latitude then longitude. Internal
storage still contains longitude then latitude. `ST_AsBinary()` exports WKB;
it does not return the internal representation with its SRID prefix.

`Strategy/MySQL/GeometryCoordinateOrderTest.php` and
`Strategy/MySQL/GeographyCoordinateOrderTest.php`, under
`LongitudeOne/SpatialWriter/Tests/Unit/`, contain literal regression examples
for all seven supported types, empty and nested collections, polygon holes,
and the Paris point from issue #5. Each family also has separate tests for
nested MultiPoint, MultiLineString and MultiPolygon values with SRID 4326
and an empty sibling collection. They assert complete literal bytes, including
a single outer SRID prefix. Run them with:

```bash
vendor/bin/phpunit --no-coverage --filter 'CoordinateOrderTest|MySQLStrategyTest'
```

## How to create test for the Extended WKB Strategy

This is the same process, but connect on a PostGis Database, and use this kind of request:

```sql
SELECT ST_AsEwkb(ST_GeomFromText('POINT(42.1 42.42)', 4326));
```

## How to create test for the WKB Strategy

This is the same process, but connect on a PostGis Database, and use this kind of request:

```sql
SELECT encode(ST_AsBinary(ST_GeomFromText('POINT Z (1 2 3)', 4326), 'NDR'), 'hex');
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


## Explicit ISO WKB examples

`LongitudeOne/SpatialWriter/Tests/Unit/Strategy/Wkb/Examples/` contains 136
literal examples covering all 68 concrete classes in both Geometry and
Geography families and all available XY, XYZ, XYM and XYZM dimensions.
Each type has a populated and an empty example, with direct constructors,
a complete hexadecimal expectation, and a WKT comment for readability.
Expected bytes were verified independently with GDAL 3.6.4's
`OGR_G_ExportToIsoWkb` using little-endian byte order. GDAL is not needed to
run the tests. No expected bytes or geometry fixtures are built at runtime.

These cases cover ISO type offsets, NaN empty points, polygon holes, triangles,
and adjacent polyhedral patches. `WkbRegressionTest` adds nested collections
with empty members and explicit failures for unsupported types/interfaces.
Every example with SRID 4326 verifies that WKB omits the SRID.

```bash
vendor/bin/phpunit --no-coverage tests/LongitudeOne/SpatialWriter/Tests/Unit/Strategy/Wkb
```


## Explicit EWKB examples

`LongitudeOne/SpatialWriter/Tests/Unit/Strategy/Ewkb/Examples/` contains 136
literal examples: populated and empty values for all 68 concrete classes.
Each test includes its complete constructor, WKT comment and expected hex.
The expectations use the GDAL-verified ISO coordinate payloads with EWKB
Z/M flags and a single outer SRID, following
[PostGIS's encoding rules](https://github.com/postgis/postgis/blob/master/liblwgeom/lwout_wkb.c).
They are fixed literals, independent of the PHP encoder; tests perform no
fixture or expected-byte generation and require no database.

`EwkbRegressionTest` covers zero SRIDs in every dimension, empty points,
nested collections with empty members, and unsupported types/interfaces.
Run all EWKB tests with:

```bash
vendor/bin/phpunit --no-coverage --filter Ewkb
```

For an independent PostGIS reference, use
`SELECT encode(ST_AsEWKB(ST_GeomFromEWKT('SRID=4326;POINT Z (1 2 3)'), 'NDR'), 'hex');`.


## MySQL rejected inputs

`Strategy/MySQL/GeometryInvalidInputTest.php` and
`Strategy/MySQL/GeographyInvalidInputTest.php` explicitly expect
`UnsupportedDimensionException` for Z/M/ZM inputs and
`UnsupportedSpatialTypeException` for unsupported empty geometries.
Cases cover all six non-collection XY types, dimensional empty points and
collections, nested empty points, and empty line/polygon members of
multi-geometries. Existing coordinate-order tests verify that empty XY
collections remain accepted.

```bash
vendor/bin/phpunit --no-coverage --filter InvalidInputTest
```
