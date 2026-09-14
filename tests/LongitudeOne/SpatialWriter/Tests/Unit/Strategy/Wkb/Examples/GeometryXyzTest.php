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

use LongitudeOne\SpatialTypes\Types\Dimension3z\Geometry\GeometryCollection;
use LongitudeOne\SpatialTypes\Types\Dimension3z\Geometry\LineString;
use LongitudeOne\SpatialTypes\Types\Dimension3z\Geometry\MultiLineString;
use LongitudeOne\SpatialTypes\Types\Dimension3z\Geometry\MultiPoint;
use LongitudeOne\SpatialTypes\Types\Dimension3z\Geometry\MultiPolygon;
use LongitudeOne\SpatialTypes\Types\Dimension3z\Geometry\Point;
use LongitudeOne\SpatialTypes\Types\Dimension3z\Geometry\Polygon;
use LongitudeOne\SpatialTypes\Types\Dimension3z\Geometry\PolyhedralSurface;
use LongitudeOne\SpatialTypes\Types\Dimension3z\Geometry\Triangle;
use LongitudeOne\SpatialWriter\Strategy\Wkb\WkbTypeEncoder;
use LongitudeOne\SpatialWriter\Strategy\WkbBinaryStrategy;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Explicit ISO WKB examples for Geometry objects with elevation (XYZ).
 *
 * @internal
 */
#[CoversClass(WkbBinaryStrategy::class)]
#[CoversClass(WkbTypeEncoder::class)]
class GeometryXyzTest extends TestCase
{
    /** Write a collection containing a point and a line. */
    public function testGeometryCollection(): void
    {
        $collection = new GeometryCollection(4326, [
            new Point(1, 2, 5, 4326),
            new LineString([[0, 0, 6], [2, 3, 7]], 4326),
        ]);

        // Reference geometry in WKT: GEOMETRYCOLLECTION Z (POINT Z (1 2 5), LINESTRING Z (0 0 6, 2 3 7))
        static::assertSame(
            '01ef0300000200000001e9030000000000000000f03f0000000000000040000000000000144001ea03000002000000000000000000000000000000000000000000000000001840000000000000004000000000000008400000000000001c40',
            bin2hex((new WkbBinaryStrategy())->executeStrategy($collection))
        );
    }

    /** Preserve the type and dimension of an empty GeometryCollection. */
    public function testGeometryCollectionEmpty(): void
    {
        $collection = new GeometryCollection(4326, []);

        // Reference geometry in WKT: GEOMETRYCOLLECTION Z EMPTY
        static::assertSame(
            '01ef03000000000000',
            bin2hex((new WkbBinaryStrategy())->executeStrategy($collection))
        );
    }

    /** Write three points in their original order. */
    public function testLineString(): void
    {
        $line = new LineString([[0, 0, 5], [2, 3, 6], [4, 1, 7]], 4326);

        // Reference geometry in WKT: LINESTRING Z (0 0 5, 2 3 6, 4 1 7)
        static::assertSame(
            '01ea030000030000000000000000000000000000000000000000000000000014400000000000000040000000000000084000000000000018400000000000001040000000000000f03f0000000000001c40',
            bin2hex((new WkbBinaryStrategy())->executeStrategy($line))
        );
    }

    /** Preserve the type and dimension of an empty LineString. */
    public function testLineStringEmpty(): void
    {
        $line = new LineString([], 4326);

        // Reference geometry in WKT: LINESTRING Z EMPTY
        static::assertSame(
            '01ea03000000000000',
            bin2hex((new WkbBinaryStrategy())->executeStrategy($line))
        );
    }

    /** Write two lines in their original order. */
    public function testMultiLineString(): void
    {
        $lines = new MultiLineString([
            [[0, 0, 5], [2, 3, 6]],
            [[4, 1, 7], [5, 2, 8]],
        ], 4326);

        // Reference geometry in WKT: MULTILINESTRING Z ((0 0 5, 2 3 6), (4 1 7, 5 2 8))
        static::assertSame(
            '01ed0300000200000001ea0300000200000000000000000000000000000000000000000000000000144000000000000000400000000000000840000000000000184001ea030000020000000000000000001040000000000000f03f0000000000001c40000000000000144000000000000000400000000000002040',
            bin2hex((new WkbBinaryStrategy())->executeStrategy($lines))
        );
    }

    /** Preserve the type and dimension of an empty MultiLineString. */
    public function testMultiLineStringEmpty(): void
    {
        $lines = new MultiLineString([], 4326);

        // Reference geometry in WKT: MULTILINESTRING Z EMPTY
        static::assertSame(
            '01ed03000000000000',
            bin2hex((new WkbBinaryStrategy())->executeStrategy($lines))
        );
    }

    /** Write two distinct points with their own WKB headers. */
    public function testMultiPoint(): void
    {
        $points = new MultiPoint([[1, 2, 5], [3, 4, 6]], 4326);

        // Reference geometry in WKT: MULTIPOINT Z ((1 2 5), (3 4 6))
        static::assertSame(
            '01ec0300000200000001e9030000000000000000f03f0000000000000040000000000000144001e9030000000000000000084000000000000010400000000000001840',
            bin2hex((new WkbBinaryStrategy())->executeStrategy($points))
        );
    }

    /** Preserve the type and dimension of an empty MultiPoint. */
    public function testMultiPointEmpty(): void
    {
        $points = new MultiPoint([], 4326);

        // Reference geometry in WKT: MULTIPOINT Z EMPTY
        static::assertSame(
            '01ec03000000000000',
            bin2hex((new WkbBinaryStrategy())->executeStrategy($points))
        );
    }

    /** Write two polygons with their own closed exterior rings. */
    public function testMultiPolygon(): void
    {
        $polygons = new MultiPolygon([
            [[[0, 0, 5], [2, 0, 5], [0, 2, 5], [0, 0, 5]]],
            [[[3, 3, 6], [5, 3, 6], [3, 5, 6], [3, 3, 6]]],
        ], 4326);

        // Reference geometry in WKT: MULTIPOLYGON Z (((0 0 5, 2 0 5, 0 2 5, 0 0 5)), ((3 3 6, 5 3 6, 3 5 6, 3 3 6)))
        static::assertSame(
            '01ee0300000200000001eb030000010000000400000000000000000000000000000000000000000000000000144000000000000000400000000000000000000000000000144000000000000000000000000000000040000000000000144000000000000000000000000000000000000000000000144001eb0300000100000004000000000000000000084000000000000008400000000000001840000000000000144000000000000008400000000000001840000000000000084000000000000014400000000000001840000000000000084000000000000008400000000000001840',
            bin2hex((new WkbBinaryStrategy())->executeStrategy($polygons))
        );
    }

    /** Preserve the type and dimension of an empty MultiPolygon. */
    public function testMultiPolygonEmpty(): void
    {
        $polygons = new MultiPolygon([], 4326);

        // Reference geometry in WKT: MULTIPOLYGON Z EMPTY
        static::assertSame(
            '01ee03000000000000',
            bin2hex((new WkbBinaryStrategy())->executeStrategy($polygons))
        );
    }

    /** Write a point with fractional and negative ordinates. */
    public function testPoint(): void
    {
        $point = new Point(2.5, -3, 5, 4326);

        // Reference geometry in WKT: POINT Z (2.5 -3 5)
        static::assertSame(
            '01e9030000000000000000044000000000000008c00000000000001440',
            bin2hex((new WkbBinaryStrategy())->executeStrategy($point))
        );
    }

    /** Preserve the type and dimension of an empty Point. */
    public function testPointEmpty(): void
    {
        $point = new Point(srid: 4326);

        // Reference geometry in WKT: POINT Z EMPTY
        static::assertSame(
            '01e9030000000000000000f87f000000000000f87f000000000000f87f',
            bin2hex((new WkbBinaryStrategy())->executeStrategy($point))
        );
    }

    /** Write an exterior ring followed by an interior hole. */
    public function testPolygon(): void
    {
        $polygon = new Polygon([
            [[0, 0, 5], [4, 0, 5], [4, 4, 5], [0, 4, 5], [0, 0, 5]],
            [[1, 1, 5], [1, 2, 5], [2, 2, 5], [2, 1, 5], [1, 1, 5]],
        ], 4326);

        // Reference geometry in WKT: POLYGON Z ((0 0 5, 4 0 5, 4 4 5, 0 4 5, 0 0 5), (1 1 5, 1 2 5, 2 2 5, 2 1 5, 1 1 5))
        static::assertSame(
            '01eb030000020000000500000000000000000000000000000000000000000000000000144000000000000010400000000000000000000000000000144000000000000010400000000000001040000000000000144000000000000000000000000000001040000000000000144000000000000000000000000000000000000000000000144005000000000000000000f03f000000000000f03f0000000000001440000000000000f03f000000000000004000000000000014400000000000000040000000000000004000000000000014400000000000000040000000000000f03f0000000000001440000000000000f03f000000000000f03f0000000000001440',
            bin2hex((new WkbBinaryStrategy())->executeStrategy($polygon))
        );
    }

    /** Preserve the type and dimension of an empty Polygon. */
    public function testPolygonEmpty(): void
    {
        $polygon = new Polygon([], 4326);

        // Reference geometry in WKT: POLYGON Z EMPTY
        static::assertSame(
            '01eb03000000000000',
            bin2hex((new WkbBinaryStrategy())->executeStrategy($polygon))
        );
    }

    /** Write two adjacent polygon patches sharing an edge. */
    public function testPolyhedralSurface(): void
    {
        $surface = new PolyhedralSurface([
            [[[0, 0, 5], [1, 0, 5], [1, 1, 5], [0, 1, 5], [0, 0, 5]]],
            [[[1, 0, 5], [2, 0, 5], [2, 1, 5], [1, 1, 5], [1, 0, 5]]],
        ], 4326);

        // Reference geometry in WKT: POLYHEDRALSURFACE Z (((0 0 5, 1 0 5, 1 1 5, 0 1 5, 0 0 5)), ((1 0 5, 2 0 5, 2 1 5, 1 1 5, 1 0 5)))
        static::assertSame(
            '01f70300000200000001eb0300000100000005000000000000000000000000000000000000000000000000001440000000000000f03f00000000000000000000000000001440000000000000f03f000000000000f03f00000000000014400000000000000000000000000000f03f000000000000144000000000000000000000000000000000000000000000144001eb0300000100000005000000000000000000f03f000000000000000000000000000014400000000000000040000000000000000000000000000014400000000000000040000000000000f03f0000000000001440000000000000f03f000000000000f03f0000000000001440000000000000f03f00000000000000000000000000001440',
            bin2hex((new WkbBinaryStrategy())->executeStrategy($surface))
        );
    }

    /** Preserve the type and dimension of an empty PolyhedralSurface. */
    public function testPolyhedralSurfaceEmpty(): void
    {
        $surface = new PolyhedralSurface([], 4326);

        // Reference geometry in WKT: POLYHEDRALSURFACE Z EMPTY
        static::assertSame(
            '01f703000000000000',
            bin2hex((new WkbBinaryStrategy())->executeStrategy($surface))
        );
    }

    /** Write three vertices and the closing position. */
    public function testTriangle(): void
    {
        $triangle = new Triangle([[[0, 0, 5], [2, 0, 5], [0, 2, 5], [0, 0, 5]]], 4326);

        // Reference geometry in WKT: TRIANGLE Z ((0 0 5, 2 0 5, 0 2 5, 0 0 5))
        static::assertSame(
            '01f90300000100000004000000000000000000000000000000000000000000000000001440000000000000004000000000000000000000000000001440000000000000000000000000000000400000000000001440000000000000000000000000000000000000000000001440',
            bin2hex((new WkbBinaryStrategy())->executeStrategy($triangle))
        );
    }

    /** Preserve the type and dimension of an empty Triangle. */
    public function testTriangleEmpty(): void
    {
        $triangle = new Triangle([], 4326);

        // Reference geometry in WKT: TRIANGLE Z EMPTY
        static::assertSame(
            '01f903000000000000',
            bin2hex((new WkbBinaryStrategy())->executeStrategy($triangle))
        );
    }
}
