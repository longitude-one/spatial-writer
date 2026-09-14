# Serialization Strategies

Spatial Writer converts `spatial-types` objects into text or binary data.
All strategies implement
[`StrategyInterface`](../lib/LongitudeOne/SpatialWriter/Strategy/StrategyInterface.php)
and expose `executeStrategy(SpatialInterface $spatial): string`.
[`Writer`](../lib/LongitudeOne/SpatialWriter/Writer.php) delegates conversion to
the selected strategy. Conversion does not perform any reprojection.

## Choosing a Strategy

This table describes the capabilities of the repository's current implementation.

| Class.                | Output                          | SRID in output    | Dimensions written | Usage                                                            |
| --------------------- | ------------------------------- | ----------------- | ------------------ | ---------------------------------------------------------------- |
| `WktTextStrategy`     | WKT Well Known Text             | No                | XY, XYZ, XYM, XYZM | Display, text exchanges, functions accepting WKT                 |
| `EwktTextStrategy`    | EWKT Extended Well Known Text.  | Yes, when nonzero | XY, XYZ, XYM, XYZM | Text exchanges that include the spatial reference                |
| `WkbBinaryStrategy`   | ISO WKB Well Known Binary.      | No                | XY, XYZ, XYM, XYZM | Exchanges with a standard WKB consumer                           |
| `EwkbBinaryStrategy`  | EWKB Extended Well Known Binary | Yes, when nonzero | XY, XYZ, XYM, XYZM | Exchanges with a consumer of the extended PostGIS format         |
| `MySQLBinaryStrategy` | Internal MySQL binary format    | Yes, as a prefix  | XY                 | Integrations expecting spatial values in MySQL's internal format |

All strategies support `Point`, `LineString`, `Polygon`,
`MultiPoint`, `MultiLineString`, `MultiPolygon`, and geometry collections.
WKB, EWKB, WKT and EWKT also support `Triangle` and `PolyhedralSurface`. Classes in the Geometry
and Geography families use the same output type names; for example,
`GeographyCollection` becomes `GEOMETRYCOLLECTION`.

## Common Usage

The examples in the following sections reuse the imports, point, and writer
below. The SRID is passed to the spatial object's constructor.

```php
require 'vendor/autoload.php';

use LongitudeOne\SpatialTypes\Types\Dimension2\Geometry\Point;
use LongitudeOne\SpatialWriter\Strategy\EwkbBinaryStrategy;
use LongitudeOne\SpatialWriter\Strategy\EwktTextStrategy;
use LongitudeOne\SpatialWriter\Strategy\MySQLBinaryStrategy;
use LongitudeOne\SpatialWriter\Strategy\WkbBinaryStrategy;
use LongitudeOne\SpatialWriter\Strategy\WktTextStrategy;
use LongitudeOne\SpatialWriter\Writer;

$point = new Point(1, 2, 4326);
$writer = new Writer(new WktTextStrategy());

echo $writer->convert($point);
// POINT (1 2)
```

The strategy can also be called directly:
`(new WktTextStrategy())->executeStrategy($point)`.
Binary outputs are PHP byte strings. `bin2hex()` is used to display them;
it is not part of their serialization format.

## WKT — `WktTextStrategy`

[This strategy](../lib/LongitudeOne/SpatialWriter/Strategy/WktTextStrategy.php)
produces a readable representation with parentheses and space-separated
coordinates. It preserves X Y order, followed by Z and M when present.
It writes the `Z`, `M`, or `ZM` markers, including for empty values such as
`POINT Z EMPTY`. Collections can be nested.

All currently available concrete types are covered, including their empty
variants. Polyhedral surfaces are available only in XYZ and XYZM.
The SRID is omitted, as required by WKT; output starting with `SRID=4326;`
would be EWKT, which this strategy does not produce.

Numeric formatting uses `json_encode()` and PHP's `serialize_precision`
setting. Keeping its default value of `-1` produces a short representation
that preserves floating-point values when read back. A non-finite coordinate
throws a `JsonException`.

References: [GEOS WKT syntax and examples](https://libgeos.org/specifications/wkt/),
[PostGIS WKT conversion: ST_AsText](https://postgis.net/docs/ST_AsText.html).

## EWKT — `EwktTextStrategy`

[This strategy](../lib/LongitudeOne/SpatialWriter/Strategy/EwktTextStrategy.php)
extends `WktTextStrategy`. It adds `SRID=<value>;` before the WKT when the
object's SRID is nonzero. A default or explicit SRID of zero produces plain
WKT with no prefix.

```php
$writer->setStrategy(new EwktTextStrategy());
echo $writer->convert($point);
// SRID=4326;POINT (1 2)
```

All geometry types, coordinate ordering, dimension markers, empty values,
and numeric formatting are inherited from WKT. For example, an empty XYZM
point with SRID 4326 produces `SRID=4326;POINT ZM EMPTY`. For collections,
the prefix appears only once, before the outermost geometry; nested members
retain their WKT representation without additional SRID prefixes.

The strategy performs no coordinate transformation. Its dimension markers
follow `WktTextStrategy`, so the text can differ from PostGIS's own formatting.

Reference: [PostGIS EWKT output: ST_AsEWKT](https://postgis.net/docs/ST_AsEWKT.html).

## WKB — `WkbBinaryStrategy`

[This strategy](../lib/LongitudeOne/SpatialWriter/Strategy/WkbBinaryStrategy.php)
writes the byte-order indicator, the type identifier, then the coordinates
and counts required for composite objects. It preserves X Y order and ignores
the SRID. The recipient must therefore know the reference system separately.

```php
$writer->setStrategy(new WkbBinaryStrategy());
echo bin2hex($writer->convert($point));
// 0101000000000000000000f03f0000000000000040
```

The output follows ISO WKB in little-endian byte order on every platform.
It preserves X Y, followed by Z and M when present. Type identifiers use
`+1000` for XYZ, `+2000` for XYM, and `+3000` for XYZM; these are ISO offsets,
not EWKB flags. Base identifiers are 1–7 for the standard geometries,
15 for `PolyhedralSurface`, and 17 for `Triangle`.

All concrete Geometry/Geography classes are supported. Polyhedral surfaces
are available in XYZ and XYZM. Composite members carry their own WKB headers;
polygon and triangle rings contain only point counts and coordinates.
The SRID is omitted from both the outer geometry and its members.

```php
$pointZ = new \LongitudeOne\SpatialTypes\Types\Dimension3z\Geometry\Point(1, 2, 3, 4326);
echo bin2hex($writer->convert($pointZ));
// 01e9030000000000000000f03f00000000000000400000000000000840
```

An empty point contains one IEEE-754 quiet NaN per ordinate
(`000000000000f87f` in little-endian hexadecimal), preserving its dimensional
type identifier. Other empty types contain a zero member or ring count.
Nested empty members are retained. A consumer must support the corresponding
ISO dimensions and geometry types to read this output.

References: [GEOS WKB structure](https://libgeos.org/specifications/wkb/#iso-wkb),
[PostGIS WKB conversion: ST_AsBinary](https://postgis.net/docs/ST_AsBinary.html),
[GDAL geometry type identifiers](https://github.com/OSGeo/gdal/blob/master/ogr/ogr_core.h).

## EWKB — `EwkbBinaryStrategy`

[This strategy](../lib/LongitudeOne/SpatialWriter/Strategy/EwkbBinaryStrategy.php)
uses the PostGIS extension of WKB to include the SRID. When the SRID is
nonzero, it sets the type's `0x20000000` flag and writes the SRID after the
type. An SRID of zero omits this field and flag. Coordinate order is X Y,
followed by Z and M when present. Dimension flags remain present even with
a zero SRID: `0x80000000` for Z, `0x40000000` for M, or both for ZM.
These flags differ from ISO WKB dimensional offsets.

```php
$writer->setStrategy(new EwkbBinaryStrategy());
echo bin2hex($writer->convert($point));
// 0101000020e6100000000000000000f03f0000000000000040
```

All 68 concrete Geometry/Geography classes are supported in their available
dimensions, including triangles and polyhedral surfaces. Empty points contain
one quiet NaN per ordinate; other empty geometries contain a zero count.
All integers and coordinates are explicitly little-endian.

The SRID is written only in the outermost geometry, including when empty.
Members of multi-geometries, collections and polyhedral surfaces inherit it;
their individual headers retain their type and dimension flags. This also
applies to nested collections. Earlier versions repeated the SRID in some
multi-geometry members; output now follows PostGIS's single outer SRID.

For example, `POINT Z (1 2 3)` with SRID 4326 produces
`01010000a0e6100000000000000000f03f00000000000000400000000000000840`.
Curved geometries and TIN remain unsupported because the current dependency
provides no concrete classes for them.

References: [GEOS EWKB flags and structure](https://libgeos.org/specifications/wkb/#extended-wkb),
[PostGIS ST_AsEWKB documentation](https://postgis.net/docs/ST_AsEWKB.html).

## Internal MySQL Format — `MySQLBinaryStrategy`

[This strategy](../lib/LongitudeOne/SpatialWriter/Strategy/MySQLBinaryStrategy.php)
writes a four-byte SRID prefix followed by a binary representation of the
geometry. This prefix belongs to MySQL's internal format: do not pass this
output unchanged to a function that expects WKB alone.

In the current implementation, `SpatialReferenceHelper` determines XY or YX
order from the SRID lists in `Resources/`. With the default settings, SRID
4326 causes Y to be written before X. Collection members use their containing
collection's SRID. An SRID missing from the lists uses the helper's default
axis order, initially XY.

```php
$writer->setStrategy(new MySQLBinaryStrategy());
echo bin2hex($writer->convert($point));
// e610000001010000000000000000000040000000000000f03f
```

The MySQL documentation states that only `GeometryCollection` can be empty
in its internal format. Being able to create other empty objects in
`spatial-types` therefore does not guarantee that MySQL will accept them.

Reference: [MySQL 8.4 spatial formats and internal storage](https://dev.mysql.com/doc/refman/8.4/en/gis-data-formats.html).

## Current Limitations and Test Examples

The MySQL encoder reads only X and Y: additional Z or M coordinates are
not yet preserved. It does not correctly handle `POINT EMPTY`. Use nonempty
`Dimension2` objects with this encoder; WKB, EWKB, WKT and EWKT provide explicit support for
empty values and additional dimensions.

The MySQL strategy declares little-endian byte order (`01`) and uses
`pack()` calls in the machine's native byte order. Its current implementation
therefore assumes a little-endian machine.

Curved geometries and TIN have no concrete classes in the current dependency
and are not implemented by WKB. Unsupported types throw an `UnsupportedSpatialTypeException`.
An unrecognized spatial interface may throw an
`UnsupportedSpatialInterfaceException`.

For complete inputs and outputs, see the
[explicit EWKB examples](../tests/LongitudeOne/SpatialWriter/Tests/Unit/Strategy/Ewkb/Examples/),
[explicit WKB examples](../tests/LongitudeOne/SpatialWriter/Tests/Unit/Strategy/Wkb/Examples/),
[explicit WKT examples](../tests/LongitudeOne/SpatialWriter/Tests/Unit/Strategy/Wkt/Examples/)
and the [testing guide](../tests/README.md). To add a format, implement
`StrategyInterface` and pass the new strategy to `Writer`.
