<?php
/**
 * This file is part of the spatial-encoder project.
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

namespace LongitudeOne\SpatialEncoder\Tests\Unit\Strategy\Wkt\Examples;

use LongitudeOne\SpatialEncoder\Strategy\Wkt\WktTypeEncoder;
use LongitudeOne\SpatialEncoder\Strategy\WktTextStrategy;
use LongitudeOne\SpatialTypes\Types\Dimension3z\Geography\GeographyCollection;
use LongitudeOne\SpatialTypes\Types\Dimension3z\Geography\LineString;
use LongitudeOne\SpatialTypes\Types\Dimension3z\Geography\MultiLineString;
use LongitudeOne\SpatialTypes\Types\Dimension3z\Geography\MultiPoint;
use LongitudeOne\SpatialTypes\Types\Dimension3z\Geography\MultiPolygon;
use LongitudeOne\SpatialTypes\Types\Dimension3z\Geography\Point;
use LongitudeOne\SpatialTypes\Types\Dimension3z\Geography\Polygon;
use LongitudeOne\SpatialTypes\Types\Dimension3z\Geography\PolyhedralSurface;
use LongitudeOne\SpatialTypes\Types\Dimension3z\Geography\Triangle;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Explicit WKT examples for Geography objects with elevation (XYZ).
 *
 * @internal
 */
#[CoversClass(WktTextStrategy::class)]
#[CoversClass(WktTypeEncoder::class)]
class GeographyXyzTest extends TestCase
{
    /** Write a geographic collection using the GEOMETRYCOLLECTION keyword. */
    public function testGeographyCollection(): void
    {
        $collection = new GeographyCollection(4326, [
            new Point(1, 2, 5, 4326),
            new LineString([[0, 0, 6], [2, 3, 7]], 4326),
        ]);

        static::assertSame(
            'GEOMETRYCOLLECTION Z (POINT Z (1 2 5), LINESTRING Z (0 0 6, 2 3 7))',
            (new WktTextStrategy())->encode($collection)
        );
    }

    /** Preserve the type and dimension of an empty GeographyCollection. */
    public function testGeographyCollectionEmpty(): void
    {
        $collection = new GeographyCollection(4326, []);

        static::assertSame('GEOMETRYCOLLECTION Z EMPTY', (new WktTextStrategy())->encode($collection));
    }

    /** Write three points in their original order. */
    public function testLineString(): void
    {
        $line = new LineString([[0, 0, 5], [2, 3, 6], [4, 1, 7]], 4326);

        static::assertSame('LINESTRING Z (0 0 5, 2 3 6, 4 1 7)', (new WktTextStrategy())->encode($line));
    }

    /** Preserve the type and dimension of an empty LineString. */
    public function testLineStringEmpty(): void
    {
        $line = new LineString([], 4326);

        static::assertSame('LINESTRING Z EMPTY', (new WktTextStrategy())->encode($line));
    }

    /** Write two lines in their original order. */
    public function testMultiLineString(): void
    {
        $lines = new MultiLineString([
            [[0, 0, 5], [2, 3, 6]],
            [[4, 1, 7], [5, 2, 8]],
        ], 4326);

        static::assertSame('MULTILINESTRING Z ((0 0 5, 2 3 6), (4 1 7, 5 2 8))', (new WktTextStrategy())->encode($lines));
    }

    /** Preserve the type and dimension of an empty MultiLineString. */
    public function testMultiLineStringEmpty(): void
    {
        $lines = new MultiLineString([], 4326);

        static::assertSame('MULTILINESTRING Z EMPTY', (new WktTextStrategy())->encode($lines));
    }

    /** Write two distinct points with separate parentheses. */
    public function testMultiPoint(): void
    {
        $points = new MultiPoint([[1, 2, 5], [3, 4, 6]], 4326);

        static::assertSame('MULTIPOINT Z ((1 2 5), (3 4 6))', (new WktTextStrategy())->encode($points));
    }

    /** Preserve the type and dimension of an empty MultiPoint. */
    public function testMultiPointEmpty(): void
    {
        $points = new MultiPoint([], 4326);

        static::assertSame('MULTIPOINT Z EMPTY', (new WktTextStrategy())->encode($points));
    }

    /** Write two polygons with their own closed exterior rings. */
    public function testMultiPolygon(): void
    {
        $polygons = new MultiPolygon([
            [[[0, 0, 5], [2, 0, 5], [0, 2, 5], [0, 0, 5]]],
            [[[3, 3, 6], [5, 3, 6], [3, 5, 6], [3, 3, 6]]],
        ], 4326);

        static::assertSame(
            'MULTIPOLYGON Z (((0 0 5, 2 0 5, 0 2 5, 0 0 5)), ((3 3 6, 5 3 6, 3 5 6, 3 3 6)))',
            (new WktTextStrategy())->encode($polygons)
        );
    }

    /** Preserve the type and dimension of an empty MultiPolygon. */
    public function testMultiPolygonEmpty(): void
    {
        $polygons = new MultiPolygon([], 4326);

        static::assertSame('MULTIPOLYGON Z EMPTY', (new WktTextStrategy())->encode($polygons));
    }

    /** Write a point with fractional and negative ordinates. */
    public function testPoint(): void
    {
        $point = new Point(2.5, -3, 5, 4326);

        static::assertSame('POINT Z (2.5 -3 5)', (new WktTextStrategy())->encode($point));
    }

    /** Preserve the type and dimension of an empty Point. */
    public function testPointEmpty(): void
    {
        $point = new Point(srid: 4326);

        static::assertSame('POINT Z EMPTY', (new WktTextStrategy())->encode($point));
    }

    /** Write an exterior ring followed by an interior hole. */
    public function testPolygon(): void
    {
        $polygon = new Polygon([
            [[0, 0, 5], [4, 0, 5], [4, 4, 5], [0, 4, 5], [0, 0, 5]],
            [[1, 1, 5], [1, 2, 5], [2, 2, 5], [2, 1, 5], [1, 1, 5]],
        ], 4326);

        static::assertSame(
            'POLYGON Z ((0 0 5, 4 0 5, 4 4 5, 0 4 5, 0 0 5), (1 1 5, 1 2 5, 2 2 5, 2 1 5, 1 1 5))',
            (new WktTextStrategy())->encode($polygon)
        );
    }

    /** Preserve the type and dimension of an empty Polygon. */
    public function testPolygonEmpty(): void
    {
        $polygon = new Polygon([], 4326);

        static::assertSame('POLYGON Z EMPTY', (new WktTextStrategy())->encode($polygon));
    }

    /** Write two adjacent polygon patches sharing an edge. */
    public function testPolyhedralSurface(): void
    {
        $surface = new PolyhedralSurface([
            [[[0, 0, 5], [1, 0, 5], [1, 1, 5], [0, 1, 5], [0, 0, 5]]],
            [[[1, 0, 5], [2, 0, 5], [2, 1, 5], [1, 1, 5], [1, 0, 5]]],
        ], 4326);

        static::assertSame(
            'POLYHEDRALSURFACE Z (((0 0 5, 1 0 5, 1 1 5, 0 1 5, 0 0 5)), ((1 0 5, 2 0 5, 2 1 5, 1 1 5, 1 0 5)))',
            (new WktTextStrategy())->encode($surface)
        );
    }

    /** Preserve the type and dimension of an empty PolyhedralSurface. */
    public function testPolyhedralSurfaceEmpty(): void
    {
        $surface = new PolyhedralSurface([], 4326);

        static::assertSame('POLYHEDRALSURFACE Z EMPTY', (new WktTextStrategy())->encode($surface));
    }

    /** Write three vertices and the closing position. */
    public function testTriangle(): void
    {
        $triangle = new Triangle([[[0, 0, 5], [2, 0, 5], [0, 2, 5], [0, 0, 5]]], 4326);

        static::assertSame('TRIANGLE Z ((0 0 5, 2 0 5, 0 2 5, 0 0 5))', (new WktTextStrategy())->encode($triangle));
    }

    /** Preserve the type and dimension of an empty Triangle. */
    public function testTriangleEmpty(): void
    {
        $triangle = new Triangle([], 4326);

        static::assertSame('TRIANGLE Z EMPTY', (new WktTextStrategy())->encode($triangle));
    }
}
