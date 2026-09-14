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

use LongitudeOne\SpatialTypes\Types\Dimension4zm\Geometry\GeometryCollection;
use LongitudeOne\SpatialTypes\Types\Dimension4zm\Geometry\LineString;
use LongitudeOne\SpatialTypes\Types\Dimension4zm\Geometry\MultiLineString;
use LongitudeOne\SpatialTypes\Types\Dimension4zm\Geometry\MultiPoint;
use LongitudeOne\SpatialTypes\Types\Dimension4zm\Geometry\MultiPolygon;
use LongitudeOne\SpatialTypes\Types\Dimension4zm\Geometry\Point;
use LongitudeOne\SpatialTypes\Types\Dimension4zm\Geometry\Polygon;
use LongitudeOne\SpatialTypes\Types\Dimension4zm\Geometry\PolyhedralSurface;
use LongitudeOne\SpatialTypes\Types\Dimension4zm\Geometry\Triangle;
use LongitudeOne\SpatialWriter\Strategy\Wkb\WkbTypeEncoder;
use LongitudeOne\SpatialWriter\Strategy\WkbBinaryStrategy;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Explicit ISO WKB examples for Geometry objects with elevation and measure (XYZM).
 *
 * @internal
 */
#[CoversClass(WkbBinaryStrategy::class)]
#[CoversClass(WkbTypeEncoder::class)]
class GeometryXyzmTest extends TestCase
{
    /** Write a collection containing a point and a line. */
    public function testGeometryCollection(): void
    {
        $collection = new GeometryCollection(4326, [
            new Point(1, 2, 5, 10, 4326),
            new LineString([[0, 0, 6, 20], [2, 3, 7, 30]], 4326),
        ]);

        // Reference geometry in WKT: GEOMETRYCOLLECTION ZM (POINT ZM (1 2 5 10), LINESTRING ZM (0 0 6 20, 2 3 7 30))
        static::assertSame(
            '01bf0b00000200000001b90b0000000000000000f03f00000000000000400000000000001440000000000000244001ba0b0000020000000000000000000000000000000000000000000000000018400000000000003440000000000000004000000000000008400000000000001c400000000000003e40',
            bin2hex((new WkbBinaryStrategy())->executeStrategy($collection))
        );
    }

    /** Preserve the type and dimension of an empty GeometryCollection. */
    public function testGeometryCollectionEmpty(): void
    {
        $collection = new GeometryCollection(4326, []);

        // Reference geometry in WKT: GEOMETRYCOLLECTION ZM EMPTY
        static::assertSame(
            '01bf0b000000000000',
            bin2hex((new WkbBinaryStrategy())->executeStrategy($collection))
        );
    }

    /** Write three points in their original order. */
    public function testLineString(): void
    {
        $line = new LineString([[0, 0, 5, 10], [2, 3, 6, 20], [4, 1, 7, 30]], 4326);

        // Reference geometry in WKT: LINESTRING ZM (0 0 5 10, 2 3 6 20, 4 1 7 30)
        static::assertSame(
            '01ba0b000003000000000000000000000000000000000000000000000000001440000000000000244000000000000000400000000000000840000000000000184000000000000034400000000000001040000000000000f03f0000000000001c400000000000003e40',
            bin2hex((new WkbBinaryStrategy())->executeStrategy($line))
        );
    }

    /** Preserve the type and dimension of an empty LineString. */
    public function testLineStringEmpty(): void
    {
        $line = new LineString([], 4326);

        // Reference geometry in WKT: LINESTRING ZM EMPTY
        static::assertSame(
            '01ba0b000000000000',
            bin2hex((new WkbBinaryStrategy())->executeStrategy($line))
        );
    }

    /** Write two lines in their original order. */
    public function testMultiLineString(): void
    {
        $lines = new MultiLineString([
            [[0, 0, 5, 10], [2, 3, 6, 20]],
            [[4, 1, 7, 30], [5, 2, 8, 40]],
        ], 4326);

        // Reference geometry in WKT: MULTILINESTRING ZM ((0 0 5 10, 2 3 6 20), (4 1 7 30, 5 2 8 40))
        static::assertSame(
            '01bd0b00000200000001ba0b0000020000000000000000000000000000000000000000000000000014400000000000002440000000000000004000000000000008400000000000001840000000000000344001ba0b0000020000000000000000001040000000000000f03f0000000000001c400000000000003e400000000000001440000000000000004000000000000020400000000000004440',
            bin2hex((new WkbBinaryStrategy())->executeStrategy($lines))
        );
    }

    /** Preserve the type and dimension of an empty MultiLineString. */
    public function testMultiLineStringEmpty(): void
    {
        $lines = new MultiLineString([], 4326);

        // Reference geometry in WKT: MULTILINESTRING ZM EMPTY
        static::assertSame(
            '01bd0b000000000000',
            bin2hex((new WkbBinaryStrategy())->executeStrategy($lines))
        );
    }

    /** Write two distinct points with their own WKB headers. */
    public function testMultiPoint(): void
    {
        $points = new MultiPoint([[1, 2, 5, 10], [3, 4, 6, 20]], 4326);

        // Reference geometry in WKT: MULTIPOINT ZM ((1 2 5 10), (3 4 6 20))
        static::assertSame(
            '01bc0b00000200000001b90b0000000000000000f03f00000000000000400000000000001440000000000000244001b90b00000000000000000840000000000000104000000000000018400000000000003440',
            bin2hex((new WkbBinaryStrategy())->executeStrategy($points))
        );
    }

    /** Preserve the type and dimension of an empty MultiPoint. */
    public function testMultiPointEmpty(): void
    {
        $points = new MultiPoint([], 4326);

        // Reference geometry in WKT: MULTIPOINT ZM EMPTY
        static::assertSame(
            '01bc0b000000000000',
            bin2hex((new WkbBinaryStrategy())->executeStrategy($points))
        );
    }

    /** Write two polygons with their own closed exterior rings. */
    public function testMultiPolygon(): void
    {
        $polygons = new MultiPolygon([
            [[[0, 0, 5, 10], [2, 0, 5, 10], [0, 2, 5, 10], [0, 0, 5, 10]]],
            [[[3, 3, 6, 20], [5, 3, 6, 20], [3, 5, 6, 20], [3, 3, 6, 20]]],
        ], 4326);

        // Reference geometry in WKT: MULTIPOLYGON ZM (((0 0 5 10, 2 0 5 10, 0 2 5 10, 0 0 5 10)), ((3 3 6 20, 5 3 6 20, 3 5 6 20, 3 3 6 20)))
        static::assertSame(
            '01be0b00000200000001bb0b00000100000004000000000000000000000000000000000000000000000000001440000000000000244000000000000000400000000000000000000000000000144000000000000024400000000000000000000000000000004000000000000014400000000000002440000000000000000000000000000000000000000000001440000000000000244001bb0b000001000000040000000000000000000840000000000000084000000000000018400000000000003440000000000000144000000000000008400000000000001840000000000000344000000000000008400000000000001440000000000000184000000000000034400000000000000840000000000000084000000000000018400000000000003440',
            bin2hex((new WkbBinaryStrategy())->executeStrategy($polygons))
        );
    }

    /** Preserve the type and dimension of an empty MultiPolygon. */
    public function testMultiPolygonEmpty(): void
    {
        $polygons = new MultiPolygon([], 4326);

        // Reference geometry in WKT: MULTIPOLYGON ZM EMPTY
        static::assertSame(
            '01be0b000000000000',
            bin2hex((new WkbBinaryStrategy())->executeStrategy($polygons))
        );
    }

    /** Write a point with fractional and negative ordinates. */
    public function testPoint(): void
    {
        $point = new Point(2.5, -3, 5, 10, 4326);

        // Reference geometry in WKT: POINT ZM (2.5 -3 5 10)
        static::assertSame(
            '01b90b0000000000000000044000000000000008c000000000000014400000000000002440',
            bin2hex((new WkbBinaryStrategy())->executeStrategy($point))
        );
    }

    /** Preserve the type and dimension of an empty Point. */
    public function testPointEmpty(): void
    {
        $point = new Point(srid: 4326);

        // Reference geometry in WKT: POINT ZM EMPTY
        static::assertSame(
            '01b90b0000000000000000f87f000000000000f87f000000000000f87f000000000000f87f',
            bin2hex((new WkbBinaryStrategy())->executeStrategy($point))
        );
    }

    /** Write an exterior ring followed by an interior hole. */
    public function testPolygon(): void
    {
        $polygon = new Polygon([
            [[0, 0, 5, 10], [4, 0, 5, 10], [4, 4, 5, 10], [0, 4, 5, 10], [0, 0, 5, 10]],
            [[1, 1, 5, 10], [1, 2, 5, 10], [2, 2, 5, 10], [2, 1, 5, 10], [1, 1, 5, 10]],
        ], 4326);

        // Reference geometry in WKT: POLYGON ZM ((0 0 5 10, 4 0 5 10, 4 4 5 10, 0 4 5 10, 0 0 5 10), (1 1 5 10, 1 2 5 10, 2 2 5 10, 2 1 5 10, 1 1 5 10))
        static::assertSame(
            '01bb0b000002000000050000000000000000000000000000000000000000000000000014400000000000002440000000000000104000000000000000000000000000001440000000000000244000000000000010400000000000001040000000000000144000000000000024400000000000000000000000000000104000000000000014400000000000002440000000000000000000000000000000000000000000001440000000000000244005000000000000000000f03f000000000000f03f00000000000014400000000000002440000000000000f03f00000000000000400000000000001440000000000000244000000000000000400000000000000040000000000000144000000000000024400000000000000040000000000000f03f00000000000014400000000000002440000000000000f03f000000000000f03f00000000000014400000000000002440',
            bin2hex((new WkbBinaryStrategy())->executeStrategy($polygon))
        );
    }

    /** Preserve the type and dimension of an empty Polygon. */
    public function testPolygonEmpty(): void
    {
        $polygon = new Polygon([], 4326);

        // Reference geometry in WKT: POLYGON ZM EMPTY
        static::assertSame(
            '01bb0b000000000000',
            bin2hex((new WkbBinaryStrategy())->executeStrategy($polygon))
        );
    }

    /** Write two adjacent polygon patches sharing an edge. */
    public function testPolyhedralSurface(): void
    {
        $surface = new PolyhedralSurface([
            [[[0, 0, 5, 10], [1, 0, 5, 10], [1, 1, 5, 10], [0, 1, 5, 10], [0, 0, 5, 10]]],
            [[[1, 0, 5, 10], [2, 0, 5, 10], [2, 1, 5, 10], [1, 1, 5, 10], [1, 0, 5, 10]]],
        ], 4326);

        // Reference geometry in WKT: POLYHEDRALSURFACE ZM (((0 0 5 10, 1 0 5 10, 1 1 5 10, 0 1 5 10, 0 0 5 10)), ((1 0 5 10, 2 0 5 10, 2 1 5 10, 1 1 5 10, 1 0 5 10)))
        static::assertSame(
            '01c70b00000200000001bb0b000001000000050000000000000000000000000000000000000000000000000014400000000000002440000000000000f03f000000000000000000000000000014400000000000002440000000000000f03f000000000000f03f000000000000144000000000000024400000000000000000000000000000f03f00000000000014400000000000002440000000000000000000000000000000000000000000001440000000000000244001bb0b00000100000005000000000000000000f03f00000000000000000000000000001440000000000000244000000000000000400000000000000000000000000000144000000000000024400000000000000040000000000000f03f00000000000014400000000000002440000000000000f03f000000000000f03f00000000000014400000000000002440000000000000f03f000000000000000000000000000014400000000000002440',
            bin2hex((new WkbBinaryStrategy())->executeStrategy($surface))
        );
    }

    /** Preserve the type and dimension of an empty PolyhedralSurface. */
    public function testPolyhedralSurfaceEmpty(): void
    {
        $surface = new PolyhedralSurface([], 4326);

        // Reference geometry in WKT: POLYHEDRALSURFACE ZM EMPTY
        static::assertSame(
            '01c70b000000000000',
            bin2hex((new WkbBinaryStrategy())->executeStrategy($surface))
        );
    }

    /** Write three vertices and the closing position. */
    public function testTriangle(): void
    {
        $triangle = new Triangle([[[0, 0, 5, 10], [2, 0, 5, 10], [0, 2, 5, 10], [0, 0, 5, 10]]], 4326);

        // Reference geometry in WKT: TRIANGLE ZM ((0 0 5 10, 2 0 5 10, 0 2 5 10, 0 0 5 10))
        static::assertSame(
            '01c90b000001000000040000000000000000000000000000000000000000000000000014400000000000002440000000000000004000000000000000000000000000001440000000000000244000000000000000000000000000000040000000000000144000000000000024400000000000000000000000000000000000000000000014400000000000002440',
            bin2hex((new WkbBinaryStrategy())->executeStrategy($triangle))
        );
    }

    /** Preserve the type and dimension of an empty Triangle. */
    public function testTriangleEmpty(): void
    {
        $triangle = new Triangle([], 4326);

        // Reference geometry in WKT: TRIANGLE ZM EMPTY
        static::assertSame(
            '01c90b000000000000',
            bin2hex((new WkbBinaryStrategy())->executeStrategy($triangle))
        );
    }
}
