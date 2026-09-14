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

namespace LongitudeOne\SpatialWriter\Tests\Unit\Strategy\Wkb\Examples;

use LongitudeOne\SpatialTypes\Types\Dimension3m\Geometry\GeometryCollection;
use LongitudeOne\SpatialTypes\Types\Dimension3m\Geometry\LineString;
use LongitudeOne\SpatialTypes\Types\Dimension3m\Geometry\MultiLineString;
use LongitudeOne\SpatialTypes\Types\Dimension3m\Geometry\MultiPoint;
use LongitudeOne\SpatialTypes\Types\Dimension3m\Geometry\MultiPolygon;
use LongitudeOne\SpatialTypes\Types\Dimension3m\Geometry\Point;
use LongitudeOne\SpatialTypes\Types\Dimension3m\Geometry\Polygon;
use LongitudeOne\SpatialTypes\Types\Dimension3m\Geometry\Triangle;
use LongitudeOne\SpatialWriter\Strategy\Wkb\WkbTypeEncoder;
use LongitudeOne\SpatialWriter\Strategy\WkbBinaryStrategy;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Explicit ISO WKB examples for Geometry objects with a measure ordinate (XYM).
 *
 * @internal
 */
#[CoversClass(WkbBinaryStrategy::class)]
#[CoversClass(WkbTypeEncoder::class)]
class GeometryXymTest extends TestCase
{
    /** Write a collection containing a point and a line. */
    public function testGeometryCollection(): void
    {
        $collection = new GeometryCollection(4326, [
            new Point(1, 2, 10, 4326),
            new LineString([[0, 0, 20], [2, 3, 30]], 4326),
        ]);

        // Reference geometry in WKT: GEOMETRYCOLLECTION M (POINT M (1 2 10), LINESTRING M (0 0 20, 2 3 30))
        static::assertSame(
            '01d70700000200000001d1070000000000000000f03f0000000000000040000000000000244001d207000002000000000000000000000000000000000000000000000000003440000000000000004000000000000008400000000000003e40',
            bin2hex((new WkbBinaryStrategy())->executeStrategy($collection))
        );
    }

    /** Preserve the type and dimension of an empty GeometryCollection. */
    public function testGeometryCollectionEmpty(): void
    {
        $collection = new GeometryCollection(4326, []);

        // Reference geometry in WKT: GEOMETRYCOLLECTION M EMPTY
        static::assertSame(
            '01d707000000000000',
            bin2hex((new WkbBinaryStrategy())->executeStrategy($collection))
        );
    }

    /** Write three points in their original order. */
    public function testLineString(): void
    {
        $line = new LineString([[0, 0, 10], [2, 3, 20], [4, 1, 30]], 4326);

        // Reference geometry in WKT: LINESTRING M (0 0 10, 2 3 20, 4 1 30)
        static::assertSame(
            '01d2070000030000000000000000000000000000000000000000000000000024400000000000000040000000000000084000000000000034400000000000001040000000000000f03f0000000000003e40',
            bin2hex((new WkbBinaryStrategy())->executeStrategy($line))
        );
    }

    /** Preserve the type and dimension of an empty LineString. */
    public function testLineStringEmpty(): void
    {
        $line = new LineString([], 4326);

        // Reference geometry in WKT: LINESTRING M EMPTY
        static::assertSame(
            '01d207000000000000',
            bin2hex((new WkbBinaryStrategy())->executeStrategy($line))
        );
    }

    /** Write two lines in their original order. */
    public function testMultiLineString(): void
    {
        $lines = new MultiLineString([
            [[0, 0, 10], [2, 3, 20]],
            [[4, 1, 30], [5, 2, 40]],
        ], 4326);

        // Reference geometry in WKT: MULTILINESTRING M ((0 0 10, 2 3 20), (4 1 30, 5 2 40))
        static::assertSame(
            '01d50700000200000001d20700000200000000000000000000000000000000000000000000000000244000000000000000400000000000000840000000000000344001d2070000020000000000000000001040000000000000f03f0000000000003e40000000000000144000000000000000400000000000004440',
            bin2hex((new WkbBinaryStrategy())->executeStrategy($lines))
        );
    }

    /** Preserve the type and dimension of an empty MultiLineString. */
    public function testMultiLineStringEmpty(): void
    {
        $lines = new MultiLineString([], 4326);

        // Reference geometry in WKT: MULTILINESTRING M EMPTY
        static::assertSame(
            '01d507000000000000',
            bin2hex((new WkbBinaryStrategy())->executeStrategy($lines))
        );
    }

    /** Write two distinct points with their own WKB headers. */
    public function testMultiPoint(): void
    {
        $points = new MultiPoint([[1, 2, 10], [3, 4, 20]], 4326);

        // Reference geometry in WKT: MULTIPOINT M ((1 2 10), (3 4 20))
        static::assertSame(
            '01d40700000200000001d1070000000000000000f03f0000000000000040000000000000244001d1070000000000000000084000000000000010400000000000003440',
            bin2hex((new WkbBinaryStrategy())->executeStrategy($points))
        );
    }

    /** Preserve the type and dimension of an empty MultiPoint. */
    public function testMultiPointEmpty(): void
    {
        $points = new MultiPoint([], 4326);

        // Reference geometry in WKT: MULTIPOINT M EMPTY
        static::assertSame(
            '01d407000000000000',
            bin2hex((new WkbBinaryStrategy())->executeStrategy($points))
        );
    }

    /** Write two polygons with their own closed exterior rings. */
    public function testMultiPolygon(): void
    {
        $polygons = new MultiPolygon([
            [[[0, 0, 10], [2, 0, 10], [0, 2, 10], [0, 0, 10]]],
            [[[3, 3, 20], [5, 3, 20], [3, 5, 20], [3, 3, 20]]],
        ], 4326);

        // Reference geometry in WKT: MULTIPOLYGON M (((0 0 10, 2 0 10, 0 2 10, 0 0 10)), ((3 3 20, 5 3 20, 3 5 20, 3 3 20)))
        static::assertSame(
            '01d60700000200000001d3070000010000000400000000000000000000000000000000000000000000000000244000000000000000400000000000000000000000000000244000000000000000000000000000000040000000000000244000000000000000000000000000000000000000000000244001d30700000100000004000000000000000000084000000000000008400000000000003440000000000000144000000000000008400000000000003440000000000000084000000000000014400000000000003440000000000000084000000000000008400000000000003440',
            bin2hex((new WkbBinaryStrategy())->executeStrategy($polygons))
        );
    }

    /** Preserve the type and dimension of an empty MultiPolygon. */
    public function testMultiPolygonEmpty(): void
    {
        $polygons = new MultiPolygon([], 4326);

        // Reference geometry in WKT: MULTIPOLYGON M EMPTY
        static::assertSame(
            '01d607000000000000',
            bin2hex((new WkbBinaryStrategy())->executeStrategy($polygons))
        );
    }

    /** Write a point with fractional and negative ordinates. */
    public function testPoint(): void
    {
        $point = new Point(2.5, -3, 10, 4326);

        // Reference geometry in WKT: POINT M (2.5 -3 10)
        static::assertSame(
            '01d1070000000000000000044000000000000008c00000000000002440',
            bin2hex((new WkbBinaryStrategy())->executeStrategy($point))
        );
    }

    /** Preserve the type and dimension of an empty Point. */
    public function testPointEmpty(): void
    {
        $point = new Point(srid: 4326);

        // Reference geometry in WKT: POINT M EMPTY
        static::assertSame(
            '01d1070000000000000000f87f000000000000f87f000000000000f87f',
            bin2hex((new WkbBinaryStrategy())->executeStrategy($point))
        );
    }

    /** Write an exterior ring followed by an interior hole. */
    public function testPolygon(): void
    {
        $polygon = new Polygon([
            [[0, 0, 10], [4, 0, 10], [4, 4, 10], [0, 4, 10], [0, 0, 10]],
            [[1, 1, 10], [1, 2, 10], [2, 2, 10], [2, 1, 10], [1, 1, 10]],
        ], 4326);

        // Reference geometry in WKT: POLYGON M ((0 0 10, 4 0 10, 4 4 10, 0 4 10, 0 0 10), (1 1 10, 1 2 10, 2 2 10, 2 1 10, 1 1 10))
        static::assertSame(
            '01d3070000020000000500000000000000000000000000000000000000000000000000244000000000000010400000000000000000000000000000244000000000000010400000000000001040000000000000244000000000000000000000000000001040000000000000244000000000000000000000000000000000000000000000244005000000000000000000f03f000000000000f03f0000000000002440000000000000f03f000000000000004000000000000024400000000000000040000000000000004000000000000024400000000000000040000000000000f03f0000000000002440000000000000f03f000000000000f03f0000000000002440',
            bin2hex((new WkbBinaryStrategy())->executeStrategy($polygon))
        );
    }

    /** Preserve the type and dimension of an empty Polygon. */
    public function testPolygonEmpty(): void
    {
        $polygon = new Polygon([], 4326);

        // Reference geometry in WKT: POLYGON M EMPTY
        static::assertSame(
            '01d307000000000000',
            bin2hex((new WkbBinaryStrategy())->executeStrategy($polygon))
        );
    }

    /** Write three vertices and the closing position. */
    public function testTriangle(): void
    {
        $triangle = new Triangle([[[0, 0, 10], [2, 0, 10], [0, 2, 10], [0, 0, 10]]], 4326);

        // Reference geometry in WKT: TRIANGLE M ((0 0 10, 2 0 10, 0 2 10, 0 0 10))
        static::assertSame(
            '01e10700000100000004000000000000000000000000000000000000000000000000002440000000000000004000000000000000000000000000002440000000000000000000000000000000400000000000002440000000000000000000000000000000000000000000002440',
            bin2hex((new WkbBinaryStrategy())->executeStrategy($triangle))
        );
    }

    /** Preserve the type and dimension of an empty Triangle. */
    public function testTriangleEmpty(): void
    {
        $triangle = new Triangle([], 4326);

        // Reference geometry in WKT: TRIANGLE M EMPTY
        static::assertSame(
            '01e107000000000000',
            bin2hex((new WkbBinaryStrategy())->executeStrategy($triangle))
        );
    }
}
