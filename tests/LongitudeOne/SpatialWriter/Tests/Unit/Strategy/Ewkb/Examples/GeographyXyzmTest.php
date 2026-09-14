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

namespace LongitudeOne\SpatialWriter\Tests\Unit\Strategy\Ewkb\Examples;

use LongitudeOne\SpatialTypes\Types\Dimension4zm\Geography\GeographyCollection;
use LongitudeOne\SpatialTypes\Types\Dimension4zm\Geography\LineString;
use LongitudeOne\SpatialTypes\Types\Dimension4zm\Geography\MultiLineString;
use LongitudeOne\SpatialTypes\Types\Dimension4zm\Geography\MultiPoint;
use LongitudeOne\SpatialTypes\Types\Dimension4zm\Geography\MultiPolygon;
use LongitudeOne\SpatialTypes\Types\Dimension4zm\Geography\Point;
use LongitudeOne\SpatialTypes\Types\Dimension4zm\Geography\Polygon;
use LongitudeOne\SpatialTypes\Types\Dimension4zm\Geography\PolyhedralSurface;
use LongitudeOne\SpatialTypes\Types\Dimension4zm\Geography\Triangle;
use LongitudeOne\SpatialWriter\Strategy\Ewkb\EwkbTypeEncoder;
use LongitudeOne\SpatialWriter\Strategy\EwkbBinaryStrategy;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Explicit EWKB examples for Geography objects with elevation and measure (XYZM).
 *
 * @internal
 */
#[CoversClass(EwkbBinaryStrategy::class)]
#[CoversClass(EwkbTypeEncoder::class)]
class GeographyXyzmTest extends TestCase
{
    /** Write a geographic collection using the GEOMETRYCOLLECTION keyword. */
    public function testGeographyCollection(): void
    {
        $collection = new GeographyCollection(4326, [
            new Point(1, 2, 5, 10, 4326),
            new LineString([[0, 0, 6, 20], [2, 3, 7, 30]], 4326),
        ]);

        // Reference geometry in WKT: GEOMETRYCOLLECTION ZM (POINT ZM (1 2 5 10), LINESTRING ZM (0 0 6 20, 2 3 7 30))
        static::assertSame(
            '01070000e0e61000000200000001010000c0000000000000f03f00000000000000400000000000001440000000000000244001020000c0020000000000000000000000000000000000000000000000000018400000000000003440000000000000004000000000000008400000000000001c400000000000003e40',
            bin2hex((new EwkbBinaryStrategy())->executeStrategy($collection))
        );
    }

    /** Preserve the type and dimension of an empty GeographyCollection. */
    public function testGeographyCollectionEmpty(): void
    {
        $collection = new GeographyCollection(4326, []);

        // Reference geometry in WKT: GEOMETRYCOLLECTION ZM EMPTY
        static::assertSame(
            '01070000e0e610000000000000',
            bin2hex((new EwkbBinaryStrategy())->executeStrategy($collection))
        );
    }

    /** Write three points in their original order. */
    public function testLineString(): void
    {
        $line = new LineString([[0, 0, 5, 10], [2, 3, 6, 20], [4, 1, 7, 30]], 4326);

        // Reference geometry in WKT: LINESTRING ZM (0 0 5 10, 2 3 6 20, 4 1 7 30)
        static::assertSame(
            '01020000e0e610000003000000000000000000000000000000000000000000000000001440000000000000244000000000000000400000000000000840000000000000184000000000000034400000000000001040000000000000f03f0000000000001c400000000000003e40',
            bin2hex((new EwkbBinaryStrategy())->executeStrategy($line))
        );
    }

    /** Preserve the type and dimension of an empty LineString. */
    public function testLineStringEmpty(): void
    {
        $line = new LineString([], 4326);

        // Reference geometry in WKT: LINESTRING ZM EMPTY
        static::assertSame(
            '01020000e0e610000000000000',
            bin2hex((new EwkbBinaryStrategy())->executeStrategy($line))
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
            '01050000e0e61000000200000001020000c0020000000000000000000000000000000000000000000000000014400000000000002440000000000000004000000000000008400000000000001840000000000000344001020000c0020000000000000000001040000000000000f03f0000000000001c400000000000003e400000000000001440000000000000004000000000000020400000000000004440',
            bin2hex((new EwkbBinaryStrategy())->executeStrategy($lines))
        );
    }

    /** Preserve the type and dimension of an empty MultiLineString. */
    public function testMultiLineStringEmpty(): void
    {
        $lines = new MultiLineString([], 4326);

        // Reference geometry in WKT: MULTILINESTRING ZM EMPTY
        static::assertSame(
            '01050000e0e610000000000000',
            bin2hex((new EwkbBinaryStrategy())->executeStrategy($lines))
        );
    }

    /** Write two distinct points with their own WKB headers. */
    public function testMultiPoint(): void
    {
        $points = new MultiPoint([[1, 2, 5, 10], [3, 4, 6, 20]], 4326);

        // Reference geometry in WKT: MULTIPOINT ZM ((1 2 5 10), (3 4 6 20))
        static::assertSame(
            '01040000e0e61000000200000001010000c0000000000000f03f00000000000000400000000000001440000000000000244001010000c00000000000000840000000000000104000000000000018400000000000003440',
            bin2hex((new EwkbBinaryStrategy())->executeStrategy($points))
        );
    }

    /** Preserve the type and dimension of an empty MultiPoint. */
    public function testMultiPointEmpty(): void
    {
        $points = new MultiPoint([], 4326);

        // Reference geometry in WKT: MULTIPOINT ZM EMPTY
        static::assertSame(
            '01040000e0e610000000000000',
            bin2hex((new EwkbBinaryStrategy())->executeStrategy($points))
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
            '01060000e0e61000000200000001030000c00100000004000000000000000000000000000000000000000000000000001440000000000000244000000000000000400000000000000000000000000000144000000000000024400000000000000000000000000000004000000000000014400000000000002440000000000000000000000000000000000000000000001440000000000000244001030000c001000000040000000000000000000840000000000000084000000000000018400000000000003440000000000000144000000000000008400000000000001840000000000000344000000000000008400000000000001440000000000000184000000000000034400000000000000840000000000000084000000000000018400000000000003440',
            bin2hex((new EwkbBinaryStrategy())->executeStrategy($polygons))
        );
    }

    /** Preserve the type and dimension of an empty MultiPolygon. */
    public function testMultiPolygonEmpty(): void
    {
        $polygons = new MultiPolygon([], 4326);

        // Reference geometry in WKT: MULTIPOLYGON ZM EMPTY
        static::assertSame(
            '01060000e0e610000000000000',
            bin2hex((new EwkbBinaryStrategy())->executeStrategy($polygons))
        );
    }

    /** Write a point with fractional and negative ordinates. */
    public function testPoint(): void
    {
        $point = new Point(2.5, -3, 5, 10, 4326);

        // Reference geometry in WKT: POINT ZM (2.5 -3 5 10)
        static::assertSame(
            '01010000e0e6100000000000000000044000000000000008c000000000000014400000000000002440',
            bin2hex((new EwkbBinaryStrategy())->executeStrategy($point))
        );
    }

    /** Preserve the type and dimension of an empty Point. */
    public function testPointEmpty(): void
    {
        $point = new Point(srid: 4326);

        // Reference geometry in WKT: POINT ZM EMPTY
        static::assertSame(
            '01010000e0e6100000000000000000f87f000000000000f87f000000000000f87f000000000000f87f',
            bin2hex((new EwkbBinaryStrategy())->executeStrategy($point))
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
            '01030000e0e610000002000000050000000000000000000000000000000000000000000000000014400000000000002440000000000000104000000000000000000000000000001440000000000000244000000000000010400000000000001040000000000000144000000000000024400000000000000000000000000000104000000000000014400000000000002440000000000000000000000000000000000000000000001440000000000000244005000000000000000000f03f000000000000f03f00000000000014400000000000002440000000000000f03f00000000000000400000000000001440000000000000244000000000000000400000000000000040000000000000144000000000000024400000000000000040000000000000f03f00000000000014400000000000002440000000000000f03f000000000000f03f00000000000014400000000000002440',
            bin2hex((new EwkbBinaryStrategy())->executeStrategy($polygon))
        );
    }

    /** Preserve the type and dimension of an empty Polygon. */
    public function testPolygonEmpty(): void
    {
        $polygon = new Polygon([], 4326);

        // Reference geometry in WKT: POLYGON ZM EMPTY
        static::assertSame(
            '01030000e0e610000000000000',
            bin2hex((new EwkbBinaryStrategy())->executeStrategy($polygon))
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
            '010f0000e0e61000000200000001030000c001000000050000000000000000000000000000000000000000000000000014400000000000002440000000000000f03f000000000000000000000000000014400000000000002440000000000000f03f000000000000f03f000000000000144000000000000024400000000000000000000000000000f03f00000000000014400000000000002440000000000000000000000000000000000000000000001440000000000000244001030000c00100000005000000000000000000f03f00000000000000000000000000001440000000000000244000000000000000400000000000000000000000000000144000000000000024400000000000000040000000000000f03f00000000000014400000000000002440000000000000f03f000000000000f03f00000000000014400000000000002440000000000000f03f000000000000000000000000000014400000000000002440',
            bin2hex((new EwkbBinaryStrategy())->executeStrategy($surface))
        );
    }

    /** Preserve the type and dimension of an empty PolyhedralSurface. */
    public function testPolyhedralSurfaceEmpty(): void
    {
        $surface = new PolyhedralSurface([], 4326);

        // Reference geometry in WKT: POLYHEDRALSURFACE ZM EMPTY
        static::assertSame(
            '010f0000e0e610000000000000',
            bin2hex((new EwkbBinaryStrategy())->executeStrategy($surface))
        );
    }

    /** Write three vertices and the closing position. */
    public function testTriangle(): void
    {
        $triangle = new Triangle([[[0, 0, 5, 10], [2, 0, 5, 10], [0, 2, 5, 10], [0, 0, 5, 10]]], 4326);

        // Reference geometry in WKT: TRIANGLE ZM ((0 0 5 10, 2 0 5 10, 0 2 5 10, 0 0 5 10))
        static::assertSame(
            '01110000e0e610000001000000040000000000000000000000000000000000000000000000000014400000000000002440000000000000004000000000000000000000000000001440000000000000244000000000000000000000000000000040000000000000144000000000000024400000000000000000000000000000000000000000000014400000000000002440',
            bin2hex((new EwkbBinaryStrategy())->executeStrategy($triangle))
        );
    }

    /** Preserve the type and dimension of an empty Triangle. */
    public function testTriangleEmpty(): void
    {
        $triangle = new Triangle([], 4326);

        // Reference geometry in WKT: TRIANGLE ZM EMPTY
        static::assertSame(
            '01110000e0e610000000000000',
            bin2hex((new EwkbBinaryStrategy())->executeStrategy($triangle))
        );
    }
}
