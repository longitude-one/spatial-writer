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

namespace LongitudeOne\SpatialWriter\Tests\Unit\Strategy\Wkt\Examples;

use LongitudeOne\SpatialTypes\Types\Dimension4zm\Geometry\GeometryCollection;
use LongitudeOne\SpatialTypes\Types\Dimension4zm\Geometry\LineString;
use LongitudeOne\SpatialTypes\Types\Dimension4zm\Geometry\MultiLineString;
use LongitudeOne\SpatialTypes\Types\Dimension4zm\Geometry\MultiPoint;
use LongitudeOne\SpatialTypes\Types\Dimension4zm\Geometry\MultiPolygon;
use LongitudeOne\SpatialTypes\Types\Dimension4zm\Geometry\Point;
use LongitudeOne\SpatialTypes\Types\Dimension4zm\Geometry\Polygon;
use LongitudeOne\SpatialTypes\Types\Dimension4zm\Geometry\PolyhedralSurface;
use LongitudeOne\SpatialTypes\Types\Dimension4zm\Geometry\Triangle;
use LongitudeOne\SpatialWriter\Strategy\Wkt\WktTypeEncoder;
use LongitudeOne\SpatialWriter\Strategy\WktTextStrategy;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Explicit WKT examples for Geometry objects with elevation and measure (XYZM).
 *
 * @internal
 */
#[CoversClass(WktTextStrategy::class)]
#[CoversClass(WktTypeEncoder::class)]
class GeometryXyzmTest extends TestCase
{
    /** Write a collection containing a point and a line. */
    public function testGeometryCollection(): void
    {
        $collection = new GeometryCollection(4326, [
            new Point(1, 2, 5, 10, 4326),
            new LineString([[0, 0, 6, 20], [2, 3, 7, 30]], 4326),
        ]);

        static::assertSame(
            'GEOMETRYCOLLECTION ZM (POINT ZM (1 2 5 10), LINESTRING ZM (0 0 6 20, 2 3 7 30))',
            (new WktTextStrategy())->executeStrategy($collection)
        );
    }

    /** Preserve the type and dimension of an empty GeometryCollection. */
    public function testGeometryCollectionEmpty(): void
    {
        $collection = new GeometryCollection(4326, []);

        static::assertSame('GEOMETRYCOLLECTION ZM EMPTY', (new WktTextStrategy())->executeStrategy($collection));
    }

    /** Write three points in their original order. */
    public function testLineString(): void
    {
        $line = new LineString([[0, 0, 5, 10], [2, 3, 6, 20], [4, 1, 7, 30]], 4326);

        static::assertSame('LINESTRING ZM (0 0 5 10, 2 3 6 20, 4 1 7 30)', (new WktTextStrategy())->executeStrategy($line));
    }

    /** Preserve the type and dimension of an empty LineString. */
    public function testLineStringEmpty(): void
    {
        $line = new LineString([], 4326);

        static::assertSame('LINESTRING ZM EMPTY', (new WktTextStrategy())->executeStrategy($line));
    }

    /** Write two lines in their original order. */
    public function testMultiLineString(): void
    {
        $lines = new MultiLineString([
            [[0, 0, 5, 10], [2, 3, 6, 20]],
            [[4, 1, 7, 30], [5, 2, 8, 40]],
        ], 4326);

        static::assertSame(
            'MULTILINESTRING ZM ((0 0 5 10, 2 3 6 20), (4 1 7 30, 5 2 8 40))',
            (new WktTextStrategy())->executeStrategy($lines)
        );
    }

    /** Preserve the type and dimension of an empty MultiLineString. */
    public function testMultiLineStringEmpty(): void
    {
        $lines = new MultiLineString([], 4326);

        static::assertSame('MULTILINESTRING ZM EMPTY', (new WktTextStrategy())->executeStrategy($lines));
    }

    /** Write two distinct points with separate parentheses. */
    public function testMultiPoint(): void
    {
        $points = new MultiPoint([[1, 2, 5, 10], [3, 4, 6, 20]], 4326);

        static::assertSame('MULTIPOINT ZM ((1 2 5 10), (3 4 6 20))', (new WktTextStrategy())->executeStrategy($points));
    }

    /** Preserve the type and dimension of an empty MultiPoint. */
    public function testMultiPointEmpty(): void
    {
        $points = new MultiPoint([], 4326);

        static::assertSame('MULTIPOINT ZM EMPTY', (new WktTextStrategy())->executeStrategy($points));
    }

    /** Write two polygons with their own closed exterior rings. */
    public function testMultiPolygon(): void
    {
        $polygons = new MultiPolygon([
            [[[0, 0, 5, 10], [2, 0, 5, 10], [0, 2, 5, 10], [0, 0, 5, 10]]],
            [[[3, 3, 6, 20], [5, 3, 6, 20], [3, 5, 6, 20], [3, 3, 6, 20]]],
        ], 4326);

        static::assertSame(
            'MULTIPOLYGON ZM (((0 0 5 10, 2 0 5 10, 0 2 5 10, 0 0 5 10)), ((3 3 6 20, 5 3 6 20, 3 5 6 20, 3 3 6 20)))',
            (new WktTextStrategy())->executeStrategy($polygons)
        );
    }

    /** Preserve the type and dimension of an empty MultiPolygon. */
    public function testMultiPolygonEmpty(): void
    {
        $polygons = new MultiPolygon([], 4326);

        static::assertSame('MULTIPOLYGON ZM EMPTY', (new WktTextStrategy())->executeStrategy($polygons));
    }

    /** Write a point with fractional and negative ordinates. */
    public function testPoint(): void
    {
        $point = new Point(2.5, -3, 5, 10, 4326);

        static::assertSame('POINT ZM (2.5 -3 5 10)', (new WktTextStrategy())->executeStrategy($point));
    }

    /** Preserve the type and dimension of an empty Point. */
    public function testPointEmpty(): void
    {
        $point = new Point(srid: 4326);

        static::assertSame('POINT ZM EMPTY', (new WktTextStrategy())->executeStrategy($point));
    }

    /** Write an exterior ring followed by an interior hole. */
    public function testPolygon(): void
    {
        $polygon = new Polygon([
            [[0, 0, 5, 10], [4, 0, 5, 10], [4, 4, 5, 10], [0, 4, 5, 10], [0, 0, 5, 10]],
            [[1, 1, 5, 10], [1, 2, 5, 10], [2, 2, 5, 10], [2, 1, 5, 10], [1, 1, 5, 10]],
        ], 4326);

        static::assertSame(
            'POLYGON ZM ((0 0 5 10, 4 0 5 10, 4 4 5 10, 0 4 5 10, 0 0 5 10), (1 1 5 10, 1 2 5 10, 2 2 5 10, 2 1 5 10, 1 1 5 10))',
            (new WktTextStrategy())->executeStrategy($polygon)
        );
    }

    /** Preserve the type and dimension of an empty Polygon. */
    public function testPolygonEmpty(): void
    {
        $polygon = new Polygon([], 4326);

        static::assertSame('POLYGON ZM EMPTY', (new WktTextStrategy())->executeStrategy($polygon));
    }

    /** Write two adjacent polygon patches sharing an edge. */
    public function testPolyhedralSurface(): void
    {
        $surface = new PolyhedralSurface([
            [[[0, 0, 5, 10], [1, 0, 5, 10], [1, 1, 5, 10], [0, 1, 5, 10], [0, 0, 5, 10]]],
            [[[1, 0, 5, 10], [2, 0, 5, 10], [2, 1, 5, 10], [1, 1, 5, 10], [1, 0, 5, 10]]],
        ], 4326);

        static::assertSame(
            'POLYHEDRALSURFACE ZM (((0 0 5 10, 1 0 5 10, 1 1 5 10, 0 1 5 10, 0 0 5 10)), ((1 0 5 10, 2 0 5 10, 2 1 5 10, 1 1 5 10, 1 0 5 10)))',
            (new WktTextStrategy())->executeStrategy($surface)
        );
    }

    /** Preserve the type and dimension of an empty PolyhedralSurface. */
    public function testPolyhedralSurfaceEmpty(): void
    {
        $surface = new PolyhedralSurface([], 4326);

        static::assertSame('POLYHEDRALSURFACE ZM EMPTY', (new WktTextStrategy())->executeStrategy($surface));
    }

    /** Write three vertices and the closing position. */
    public function testTriangle(): void
    {
        $triangle = new Triangle([[[0, 0, 5, 10], [2, 0, 5, 10], [0, 2, 5, 10], [0, 0, 5, 10]]], 4326);

        static::assertSame('TRIANGLE ZM ((0 0 5 10, 2 0 5 10, 0 2 5 10, 0 0 5 10))', (new WktTextStrategy())->executeStrategy($triangle));
    }

    /** Preserve the type and dimension of an empty Triangle. */
    public function testTriangleEmpty(): void
    {
        $triangle = new Triangle([], 4326);

        static::assertSame('TRIANGLE ZM EMPTY', (new WktTextStrategy())->executeStrategy($triangle));
    }
}
