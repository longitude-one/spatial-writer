<?php
/**
 * This file is part of the binary-writer project.
 *
 * PHP 8.4 | 8.5
 *
 * Copyright Alexandre Tranchant <alexandre.tranchant@gmail.com> 2024-2026
 * Copyright Longitude One 2024-2026
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

declare(strict_types=1);

namespace LongitudeOne\SpatialWriter\Tests\Unit\Strategy\MySQL;

use LongitudeOne\SpatialTypes\Types\Dimension2\Geometry\GeometryCollection;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geometry\LineString;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geometry\MultiLineString;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geometry\MultiPoint;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geometry\MultiPolygon;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geometry\Point;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geometry\Polygon;
use LongitudeOne\SpatialWriter\Strategy\MySQLBinaryStrategy;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * MySQL longitude-first regression examples for two-dimensional Geometry objects.
 *
 * @internal
 */
#[CoversClass(MySQLBinaryStrategy::class)]
class GeometryCoordinateOrderTest extends TestCase
{
    /** Write a collection containing a point and a line. */
    public function testGeometryCollection(): void
    {
        $collection = new GeometryCollection(4326, [
            new Point(1, 2, 4326),
            new LineString([[0, 0], [2, 3]], 4326),
        ]);

        // Reference geometry in WKT: GEOMETRYCOLLECTION (POINT (1 2), LINESTRING (0 0, 2 3))
        static::assertSame(
            'e61000000107000000020000000101000000000000000000f03f00000000000000400102000000020000000000000000000000000000000000000000000000000000400000000000000840',
            bin2hex((new MySQLBinaryStrategy())->executeStrategy($collection))
        );
    }

    /** Preserve the type and dimension of an empty GeometryCollection. */
    public function testGeometryCollectionEmpty(): void
    {
        $collection = new GeometryCollection(4326, []);

        // Reference geometry in WKT: GEOMETRYCOLLECTION EMPTY
        static::assertSame(
            'e6100000010700000000000000',
            bin2hex((new MySQLBinaryStrategy())->executeStrategy($collection))
        );
    }

    /** Write three points in their original order. */
    public function testLineString(): void
    {
        $line = new LineString([[0, 0], [2, 3], [4, 1]], 4326);

        // Reference geometry in WKT: LINESTRING (0 0, 2 3, 4 1)
        static::assertSame(
            'e610000001020000000300000000000000000000000000000000000000000000000000004000000000000008400000000000001040000000000000f03f',
            bin2hex((new MySQLBinaryStrategy())->executeStrategy($line))
        );
    }

    /** Write two lines in their original order. */
    public function testMultiLineString(): void
    {
        $lines = new MultiLineString([
            [[0, 0], [2, 3]],
            [[4, 1], [5, 2]],
        ], 4326);

        // Reference geometry in WKT: MULTILINESTRING ((0 0, 2 3), (4 1, 5 2))
        static::assertSame(
            'e610000001050000000200000001020000000200000000000000000000000000000000000000000000000000004000000000000008400102000000020000000000000000001040000000000000f03f00000000000014400000000000000040',
            bin2hex((new MySQLBinaryStrategy())->executeStrategy($lines))
        );
    }

    /** Write two distinct points with their own WKB headers. */
    public function testMultiPoint(): void
    {
        $points = new MultiPoint([[1, 2], [3, 4]], 4326);

        // Reference geometry in WKT: MULTIPOINT ((1 2), (3 4))
        static::assertSame(
            'e61000000104000000020000000101000000000000000000f03f0000000000000040010100000000000000000008400000000000001040',
            bin2hex((new MySQLBinaryStrategy())->executeStrategy($points))
        );
    }

    /** Write two polygons with their own closed exterior rings. */
    public function testMultiPolygon(): void
    {
        $polygons = new MultiPolygon([
            [[[0, 0], [2, 0], [0, 2], [0, 0]]],
            [[[3, 3], [5, 3], [3, 5], [3, 3]]],
        ], 4326);

        // Reference geometry in WKT: MULTIPOLYGON (((0 0, 2 0, 0 2, 0 0)), ((3 3, 5 3, 3 5, 3 3)))
        static::assertSame(
            'e610000001060000000200000001030000000100000004000000000000000000000000000000000000000000000000000040000000000000000000000000000000000000000000000040000000000000000000000000000000000103000000010000000400000000000000000008400000000000000840000000000000144000000000000008400000000000000840000000000000144000000000000008400000000000000840',
            bin2hex((new MySQLBinaryStrategy())->executeStrategy($polygons))
        );
    }

    /** Preserve longitude and latitude through nested collections and empty members. */
    public function testNestedCollection(): void
    {
        $collection = new GeometryCollection(4326, [
            new Point(2.35, 48.86, 4326),
            new GeometryCollection(4326, [new GeometryCollection(4326, [])]),
        ]);
        static::assertSame(
            'e61000000107000000020000000101000000cdcccccccccc0240ae47e17a146e4840010700000001000000010700000000000000',
            bin2hex((new MySQLBinaryStrategy())->executeStrategy($collection))
        );
    }

    /** Preserve MultiLineString inside nested collections, with one SRID prefix and an empty sibling. */
    public function testNestedMultiLineStringWithEmptyCollection(): void
    {
        $lines = new MultiLineString([
            [[0, 0], [2, 3]],
            [[4, 1], [5, 2]],
        ], 4326);
        $collection = new GeometryCollection(4326, [
            new GeometryCollection(4326, []),
            new GeometryCollection(4326, [$lines]),
        ]);

        static::assertSame(
            'e610000001070000000200000001070000000000000001070000000100000001050000000200000001020000000200000000000000000000000000000000000000000000000000004000000000000008400102000000020000000000000000001040000000000000f03f00000000000014400000000000000040',
            bin2hex((new MySQLBinaryStrategy())->executeStrategy($collection))
        );
    }

    /** Preserve MultiPoint inside nested collections, with one SRID prefix and an empty sibling. */
    public function testNestedMultiPointWithEmptyCollection(): void
    {
        $points = new MultiPoint([[1, 2], [3, 4]], 4326);
        $collection = new GeometryCollection(4326, [
            new GeometryCollection(4326, []),
            new GeometryCollection(4326, [$points]),
        ]);

        static::assertSame(
            'e61000000107000000020000000107000000000000000107000000010000000104000000020000000101000000000000000000f03f0000000000000040010100000000000000000008400000000000001040',
            bin2hex((new MySQLBinaryStrategy())->executeStrategy($collection))
        );
    }

    /** Preserve MultiPolygon inside nested collections, with one SRID prefix and an empty sibling. */
    public function testNestedMultiPolygonWithEmptyCollection(): void
    {
        $polygons = new MultiPolygon([
            [[[0, 0], [2, 0], [0, 2], [0, 0]]],
            [[[3, 3], [5, 3], [3, 5], [3, 3]]],
        ], 4326);
        $collection = new GeometryCollection(4326, [
            new GeometryCollection(4326, []),
            new GeometryCollection(4326, [$polygons]),
        ]);

        static::assertSame(
            'e610000001070000000200000001070000000000000001070000000100000001060000000200000001030000000100000004000000000000000000000000000000000000000000000000000040000000000000000000000000000000000000000000000040000000000000000000000000000000000103000000010000000400000000000000000008400000000000000840000000000000144000000000000008400000000000000840000000000000144000000000000008400000000000000840',
            bin2hex((new MySQLBinaryStrategy())->executeStrategy($collection))
        );
    }

    /** Match MySQL's longitude-first internal storage for Paris. */
    public function testParisPoint(): void
    {
        $point = new Point(2.35, 48.86, 4326);
        static::assertSame(
            'e61000000101000000cdcccccccccc0240ae47e17a146e4840',
            bin2hex((new MySQLBinaryStrategy())->executeStrategy($point))
        );
    }

    /** Write a point with fractional and negative ordinates. */
    public function testPoint(): void
    {
        $point = new Point(2.5, -3, 4326);

        // Reference geometry in WKT: POINT (2.5 -3)
        static::assertSame(
            'e61000000101000000000000000000044000000000000008c0',
            bin2hex((new MySQLBinaryStrategy())->executeStrategy($point))
        );
    }

    /** Write an exterior ring followed by an interior hole. */
    public function testPolygon(): void
    {
        $polygon = new Polygon([
            [[0, 0], [4, 0], [4, 4], [0, 4], [0, 0]],
            [[1, 1], [1, 2], [2, 2], [2, 1], [1, 1]],
        ], 4326);

        // Reference geometry in WKT: POLYGON ((0 0, 4 0, 4 4, 0 4, 0 0), (1 1, 1 2, 2 2, 2 1, 1 1))
        static::assertSame(
            'e610000001030000000200000005000000000000000000000000000000000000000000000000001040000000000000000000000000000010400000000000001040000000000000000000000000000010400000000000000000000000000000000005000000000000000000f03f000000000000f03f000000000000f03f0000000000000040000000000000004000000000000000400000000000000040000000000000f03f000000000000f03f000000000000f03f',
            bin2hex((new MySQLBinaryStrategy())->executeStrategy($polygon))
        );
    }
}
