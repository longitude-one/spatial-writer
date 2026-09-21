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
| `GeoJsonStrategy`     | GeoJSON geometry text           | No                | XY, XYZ            | Point, LineString, MultiPoint, MultiLineString, Polygon, MultiPolygon and GeometryCollection encoding according to RFC 7946  |
| `WktTextStrategy`     | WKT Well Known Text             | No                | XY, XYZ, XYM, XYZM | Display, text exchanges, functions accepting WKT                 |
| `EwktTextStrategy`    | EWKT Extended Well Known Text.  | Yes, when nonzero | XY, XYZ, XYM, XYZM | Text exchanges that include the spatial reference                |
| `WkbBinaryStrategy`   | ISO WKB Well Known Binary.      | No                | XY, XYZ, XYM, XYZM | Exchanges with a standard WKB consumer                           |
| `EwkbBinaryStrategy`  | EWKB Extended Well Known Binary | Yes, when nonzero | XY, XYZ, XYM, XYZM | Exchanges with a consumer of the extended PostGIS format         |
| `MySQLBinaryStrategy` | Internal MySQL binary format    | Yes, as a prefix  | XY                 | Integrations expecting spatial values in MySQL's internal format |

The binary and WKT/EWKT strategies support `Point`, `LineString`, `Polygon`,
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

Coordinates are always written in X Y order: longitude then latitude for
geographic objects, including SRID 4326. The SRID appears once as a prefix;
collection members preserve the same coordinate order without extra prefixes.

MySQL's SQL constructors interpret geographic input using the SRID axis order
by default (latitude then longitude for 4326). To supply longitude-first WKT,
use `ST_GeomFromText('POINT(1 2)', 4326, 'axis-order=long-lat')`.
This input convention differs from the internal storage order.

**Behavior change (#5):** earlier versions swapped coordinates for SRIDs
classified as latitude-first. That swap and the unused SRID axis-order lookup
resources are removed. Callers
that previously reversed their coordinates to compensate should now pass
X/longitude first and Y/latitude second. Changing the writer does not repair
previously stored coordinates.

```php
$writer->setStrategy(new MySQLBinaryStrategy());
echo bin2hex($writer->convert($point));
// e61000000101000000000000000000f03f0000000000000040
```

Only XY objects are accepted. Z, M and ZM inputs throw
`UnsupportedDimensionException`, including empty collections with those
dimensions. Empty points, lines, polygons and multi-geometries throw
`UnsupportedSpatialTypeException`; only XY `GeometryCollection` (including
`GeographyCollection`) may be empty. These checks also apply to nested members.

Reference: [MySQL 8.4 spatial formats and internal storage](https://dev.mysql.com/doc/refman/8.4/en/gis-data-formats.html).

## Current Limitations and Test Examples

The MySQL encoder rejects additional dimensions and unsupported empty values.
Use XY objects, with empty values limited to geometry collections. WKB, EWKB,
WKT and EWKT support additional dimensions and other empty geometry types.

The MySQL strategy declares little-endian byte order (`01`) and explicitly
uses it for SRIDs, type identifiers, counts and coordinates. Its output is
independent of the machine's native byte order.

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

## GeoJSON — `GeoJsonStrategy`

This strategy supports Geometry and Geography Points, LineStrings, MultiPoints,
MultiLineStrings, Polygons, MultiPolygons and collections in XY and XYZ, including
EMPTY. Types outside the seven RFC 7946 geometry types are rejected. Feature and FeatureCollection composition belongs
to the consuming application.

```php
$writer->setStrategy(new \LongitudeOne\SpatialWriter\Strategy\GeoJsonStrategy());
echo $writer->convert($point);
// {"type":"Point","coordinates":[1,2]}
```

XYZ produces positions `[X,Y,Z]`; EMPTY XY and XYZ produce
`{"type":"Point","coordinates":[]}`. No artificial dimension metadata is added.
Measured XYM and XYZM inputs throw `UnsupportedDimensionException`, even EMPTY:
M is neither discarded nor written as altitude or a fourth ordinate.

The caller supplies WGS 84 longitude/latitude in degrees and, when present,
ellipsoidal height in metres. The writer preserves the coordinates without
reprojection, axis swapping or unit conversion. It does not validate that
reference from SRID or authority metadata: zero, unknown and non-4326 identifiers
are not reasons for rejection and are not serialized as a coordinate-system
extension. An absent reference uses the model's default SRID of zero.

`UnsupportedSpatialTypeException` is now explicitly public, with its existing
`\Exception` inheritance and constructor unchanged. It implements
`ExceptionInterface` and reports a type unsupported by the selected strategy,
including Triangle instead of silently converting it to Polygon.
`UnsupportedSpatialInterfaceException` remains internal and unchanged.

The shared public `UnsupportedGeometryStructureException` extends
`\InvalidArgumentException` and implements `ExceptionInterface`; subsequent
geometry contributions use it for the approved incompatible structures. A
non-empty LineString with only one position throws this exception. The concrete
model accepts singleton lines, but prevents EMPTY member points and mismatched
position dimensions at construction; fixtures do not bypass these invariants.
`JsonEncodingException` extends `\RuntimeException`, implements
`ExceptionInterface`, and wraps an encoding failure with the original
`\JsonException` as `previous`. No partial JSON is returned. The installed
spatial-types model accepts non-finite floats through Geometry Point constructors;
these encounter the ordinary JSON encoding failure path, without a separate
writer validation policy.

Numeric encoding uses PHP's `serialize_precision` setting; retain its default
`-1` for floating-point round trips. Whitespace and object-property ordering are
not a canonical serialization contract.

LineStrings retain every position in its supplied order, including Z:

```php
$line = new \LongitudeOne\SpatialTypes\Types\Dimension3z\Geometry\LineString([[1, 2, 3], [4, 5, 6]]);
echo $writer->convert($line);
// {"type":"LineString","coordinates":[[1,2,3],[4,5,6]]}
```

EMPTY XY and XYZ LineStrings produce `{"type":"LineString","coordinates":[]}`.
XYM and XYZM lines are rejected even when EMPTY. LineStrings crossing the
antimeridian, such as `[[170,45],[-170,45]]`, are encoded unchanged, without
cutting or detection for rejection. This deliberately does not apply the SHOULD
in RFC 7946 section 3.1.9: consumers may interpret or display an uncut line as
crossing the long way around the globe. Callers must prepare any cutting required
by their consumers before encoding.

MultiPoints preserve the number and order of their positions, including duplicates
and Z. A singleton remains a MultiPoint rather than becoming a Point:

```php
$multiPoint = new \LongitudeOne\SpatialTypes\Types\Dimension3z\Geometry\MultiPoint([[1, 2, 3], [4, 5, 6]]);
echo $writer->convert($multiPoint);
// {"type":"MultiPoint","coordinates":[[1,2,3],[4,5,6]]}
```

An XY or XYZ MultiPoint with no members produces
`{"type":"MultiPoint","coordinates":[]}`. Measured MultiPoints are rejected
even with no members. References are omitted and coordinates are unchanged,
under the same caller responsibilities described above.

The approved MultiPoint contract rejects any EMPTY Point member with
`UnsupportedGeometryStructureException`, including an aggregate made entirely
of EMPTY members. No member is omitted or replaced. The required spatial-types
`0.0.1-alpha.2` model supports constructing these aggregates, so the writer rejects
them through its public structural exception. An empty aggregate with no members
remains supported.

MultiLineStrings preserve each supplied line, its position order and Z, and the
order of the members. A single member remains wrapped as a MultiLineString:

```php
$multiLine = new \LongitudeOne\SpatialTypes\Types\Dimension2\Geometry\MultiLineString([
    [[1, 2], [3, 4]],
    [[5, 6], [7, 8]],
]);
echo $writer->convert($multiLine);
// {"type":"MultiLineString","coordinates":[[[1,2],[3,4]],[[5,6],[7,8]]]}
```

An XY or XYZ MultiLineString with no members produces
`{"type":"MultiLineString","coordinates":[]}`. Any EMPTY LineString member,
including an aggregate containing only EMPTY members, throws
`UnsupportedGeometryStructureException`. A member with only one position also
throws that exception, as a GeoJSON line requires at least two positions.
The model permits these line members; the writer rejects them without omitting,
flattening or repairing them, and never returns partial output or nested empty
coordinate arrays. XYM and XYZM are rejected even with no members.

Antimeridian-crossing members are preserved unchanged under the deliberate
non-application of RFC 7946 section 3.1.9 described above. The same caller
responsibilities, reference omission and JSON failure contract apply.

Polygons preserve exterior and interior ring order, all positions and Z:

```php
$polygon = new \LongitudeOne\SpatialTypes\Types\Dimension2\Geometry\Polygon([
    [[0, 0], [4, 0], [4, 4], [0, 4], [0, 0]],
    [[1, 1], [1, 2], [2, 2], [2, 1], [1, 1]],
]);
echo $writer->convert($polygon);
// {"type":"Polygon","coordinates":[[[0,0],[4,0],[4,4],[0,4],[0,0]],[[1,1],[1,2],[2,2],[2,1],[1,1]]]}
```

EMPTY XY and XYZ Polygons produce `{"type":"Polygon","coordinates":[]}`.
XYM and XYZM are rejected even when EMPTY. The same reference omission and JSON
failure contracts apply to Polygons.

Every exterior ring must have strictly positive signed XY area (counterclockwise)
and every interior ring strictly negative signed XY area (clockwise). Incompatible
orientation, including zero signed area, throws
`UnsupportedGeometryStructureException`. The writer never reverses, closes or
repairs a ring and never returns partial output. Z is preserved but does not
participate in the orientation calculation. The signed-area algorithm and
zero-area rejection are the [approved LongitudeOne encoding policy](https://github.com/longitude-one/spatial-writer/issues/13#issuecomment-5765188095);
RFC 7946 section 3.1.6 requires winding but does not prescribe that algorithm or
explicitly prescribe zero-area rejection.

The installed model already enforces at least four positions, closure and the
absence of consecutive duplicate positions. It permits collinear and crossed
rings. The writer rejects a crossed ring if its signed area is zero, but does not
separately check simplicity, self-intersections, hole containment or other topology.
A nonzero signed area is not proof of topological validity; the caller remains
responsible for that validation.

Antimeridian-crossing Polygon coordinates are preserved without unwrapping,
detection for rejection or cutting; orientation uses the supplied XY coordinates.
This deliberately does not apply the SHOULD in RFC 7946 section 3.1.9. Consumers
may display an uncut polygon across the long way around the globe, so callers
must prepare any required cutting before encoding.

MultiPolygons preserve Polygon order, ring order, position order and Z. A singleton
remains a MultiPolygon. Every member uses the same Polygon orientation checks
above, with no second validation policy:

```php
$multiPolygon = new \LongitudeOne\SpatialTypes\Types\Dimension2\Geometry\MultiPolygon([
    [[[0, 0], [2, 0], [0, 2], [0, 0]]],
    [[[3, 0], [5, 0], [3, 2], [3, 0]]],
]);
echo $writer->convert($multiPolygon);
// {"type":"MultiPolygon","coordinates":[[[[0,0],[2,0],[0,2],[0,0]]],[[[3,0],[5,0],[3,2],[3,0]]]]}
```

An XY or XYZ MultiPolygon with no members produces
`{"type":"MultiPolygon","coordinates":[]}`. Any EMPTY Polygon member throws
`UnsupportedGeometryStructureException`, including when all members are EMPTY.
An incompatible exterior or interior ring also rejects the whole conversion.
No member is omitted, repaired, reoriented or replaced, and no partial output is
returned. Measured XYM and XYZM aggregates are rejected even with no members.

The same reference omission, caller responsibilities and JSON failure contract
apply. Antimeridian-crossing members retain their supplied coordinates under the
deliberate non-application of RFC 7946 section 3.1.9 described above, with the same
possible long-way-around interpretation by consumers.

The approved contracts for later geometry contributions preserve nested,
singleton and homogeneous GeometryCollections. These deliberately do not apply
the SHOULD guidance of RFC 7946 section 3.1.8. Consumers may have limited support
for these collection forms;
preparation remains the caller's responsibility. Those types are not enabled by
this increment.

References: [RFC 7946 geometry objects and positions](https://www.rfc-editor.org/rfc/rfc7946.html#section-3.1),
[MultiPoint positions](https://www.rfc-editor.org/rfc/rfc7946.html#section-3.1.3),
[MultiLineString members](https://www.rfc-editor.org/rfc/rfc7946.html#section-3.1.5),
[Polygon rings](https://www.rfc-editor.org/rfc/rfc7946.html#section-3.1.6),
[MultiPolygon members](https://www.rfc-editor.org/rfc/rfc7946.html#section-3.1.7),
[coordinate reference system](https://www.rfc-editor.org/rfc/rfc7946.html#section-4),
[non-extensible types](https://www.rfc-editor.org/rfc/rfc7946.html#section-7).

GeometryCollections recursively preserve each member's type, coordinates, order
and nesting. GeographyCollection uses the same GeoJSON `GeometryCollection` type:

```php
$collection = new \LongitudeOne\SpatialTypes\Types\Dimension2\Geometry\GeometryCollection(4326, [
    new \LongitudeOne\SpatialTypes\Types\Dimension2\Geometry\Point(1, 2, 4326),
    new \LongitudeOne\SpatialTypes\Types\Dimension2\Geometry\LineString([[3, 4], [5, 6]], 4326),
]);
echo $writer->convert($collection);
// {"type":"GeometryCollection","geometries":[{"type":"Point","coordinates":[1,2]},{"type":"LineString","coordinates":[[3,4],[5,6]]}]}
```

A collection with no members produces
`{"type":"GeometryCollection","geometries":[]}`. EMPTY geometry members retain
their own empty `coordinates` arrays; nested collections retain their
`geometries` arrays. No `coordinates` member is added to a collection. XY and
XYZ are supported, preserving Z; XYM and XYZM are rejected even when EMPTY.
Reference metadata is omitted throughout, under the caller responsibilities above.

The approved policy deliberately preserves nested, singleton and homogeneous
collections instead of flattening them or converting them to another type.
This does not apply the SHOULD guidance in
[RFC 7946 section 3.1.8](https://datatracker.ietf.org/doc/html/rfc7946#section-3.1.8).
Consumers that expect simplified forms may not handle or display these collections
consistently; callers must prepare any simplification needed by their consumers.
Antimeridian crossings also remain unchanged under section 3.1.9 as described above.

All six other supported types use their normal encoders when nested in a
collection. A non-RFC member throws `UnsupportedSpatialTypeException`; an
incompatible member structure throws `UnsupportedGeometryStructureException`.
Failures propagate through every collection level: no member is omitted and no
partial result is returned. JSON failures retain the shared `JsonEncodingException`
contract and original exception.
