# Spatial Writer

The writer module provide an interface to convert any SpatialInterfaces to other formats.

This library provides four strategies to convert spatial interfaces to other formats:
 * A strategy to convert any spatial interfaces to extended well known binary (EWKB).
 * Another one to convert any spatial interfaces to well known binary (WKB).
 * A strategy to convert spatial interfaces to the internal MySQL storage format.
 * A strategy to convert spatial interfaces to well-known text (WKT).

Feel free to provide any other strategy to convert spatial interfaces to other formats.


## Current status
![longitude-one/spatial--writer](https://img.shields.io/badge/longitude--one-spatial--writer-blue)
![Stable release](https://img.shields.io/github/v/release/longitude-one/spatial-writer)
![Minimum PHP Version](https://img.shields.io/packagist/php-v/longitude-one/spatial-writer.svg?maxAge=3600)
[![Packagist License](https://img.shields.io/packagist/l/longitude-one/spatial-writer)](https://github.com/longitude-one/spatial-writer/blob/main/LICENSE)

[![Last integration test](https://github.com/longitude-one/spatial-writer/actions/workflows/php-oldest.yaml/badge.svg)](https://github.com/longitude-one/spatial-writer/actions/workflows/php-oldest.yaml)
[![Downloads](https://img.shields.io/packagist/dm/longitude-one/spatial-writer.svg)](https://packagist.org/packages/longitude-one/spatial-writer)
[![codecov](https://codecov.io/gh/longitude-one/spatial-writer/branch/main/graph/badge.svg?token=NIFES3ETWH)](https://codecov.io/gh/longitude-one/spatial-writer)


## Installation

```bash
composer require longitude-one/spatial-writer
```


## Well-Known Text (WKT)

Use `WktTextStrategy` directly or through `Writer`:

```php
use LongitudeOne\SpatialTypes\Types\Dimension2\Geometry\Point;
use LongitudeOne\SpatialWriter\Strategy\WktTextStrategy;
use LongitudeOne\SpatialWriter\Writer;

$writer = new Writer(new WktTextStrategy());
echo $writer->convert(new Point(42.1, 42.42, 4326));
// POINT (42.1 42.42)
```

The strategy supports every concrete type currently provided by `spatial-types`:
`POINT`, `LINESTRING`, `POLYGON`, `MULTIPOINT`, `MULTILINESTRING`,
`MULTIPOLYGON`, `GEOMETRYCOLLECTION`, `TRIANGLE`, and `POLYHEDRALSURFACE`.
Both Geometry and Geography families are supported. Collections retain member
order and can contain nested collections. Empty values use `TYPE EMPTY`.

Coordinates retain their X Y order, followed by Z and/or M when present. The
output includes the corresponding `Z`, `M`, or `ZM` marker, including for empty
values. For example, a `Dimension3z` point produces `POINT Z (1 2 3)`.
The [WKT format](https://postgis.net/docs/ST_AsText.html) omits SRID metadata;
changing the SRID does not reorder or transform coordinates.

Numbers use locale-independent JSON numeric formatting and PHP's
`serialize_precision` setting. Keep its default value of `-1` for shortest
round-trip floating-point output. Non-finite coordinates throw `JsonException`.
Types without an implemented geometry class, such as circular strings and TINs,
are rejected with `UnsupportedSpatialTypeException`.

`Writer` accepts `StrategyInterface` for both text and binary output.
All strategies implement this interface directly. Implement `executeStrategy()`
to add another output format.
