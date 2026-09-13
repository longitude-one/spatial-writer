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

use LongitudeOne\SpatialTypes\Types\Dimension2\Geography\GeographyCollection;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geography\LineString;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geography\MultiLineString;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geography\MultiPoint;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geography\MultiPolygon;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geography\Point;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geography\Polygon;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geography\Triangle;
use LongitudeOne\SpatialWriter\Strategy\Wkt\WktTypeEncoder;
use LongitudeOne\SpatialWriter\Strategy\WktTextStrategy;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Explicit WKT examples for two-dimensional Geography objects.
 *
 * @internal
 */
#[CoversClass(WktTextStrategy::class)]
#[CoversClass(WktTypeEncoder::class)]
class GeographyXyTest extends TestCase
{
    /** Write a geographic collection using the GEOMETRYCOLLECTION keyword. */
    public function testGeographyCollection(): void
    {
        $collection = new GeographyCollection(4326, [
            new Point(1, 2, 4326),
            new LineString([[0, 0], [2, 3]], 4326),
        ]);

        static::assertSame(
            'GEOMETRYCOLLECTION (POINT (1 2), LINESTRING (0 0, 2 3))',
            (new WktTextStrategy())->executeStrategy($collection)
        );
    }

    /** Preserve the type and dimension of an empty GeographyCollection. */
    public function testGeographyCollectionEmpty(): void
    {
        $collection = new GeographyCollection(4326, []);

        static::assertSame('GEOMETRYCOLLECTION EMPTY', (new WktTextStrategy())->executeStrategy($collection));
    }

    /** Write three points in their original order. */
    public function testLineString(): void
    {
        $line = new LineString([[0, 0], [2, 3], [4, 1]], 4326);

        static::assertSame('LINESTRING (0 0, 2 3, 4 1)', (new WktTextStrategy())->executeStrategy($line));
    }

    /** Preserve the type and dimension of an empty LineString. */
    public function testLineStringEmpty(): void
    {
        $line = new LineString([], 4326);

        static::assertSame('LINESTRING EMPTY', (new WktTextStrategy())->executeStrategy($line));
    }

    /** Write two lines in their original order. */
    public function testMultiLineString(): void
    {
        $lines = new MultiLineString([
            [[0, 0], [2, 3]],
            [[4, 1], [5, 2]],
        ], 4326);

        static::assertSame('MULTILINESTRING ((0 0, 2 3), (4 1, 5 2))', (new WktTextStrategy())->executeStrategy($lines));
    }

    /** Preserve the type and dimension of an empty MultiLineString. */
    public function testMultiLineStringEmpty(): void
    {
        $lines = new MultiLineString([], 4326);

        static::assertSame('MULTILINESTRING EMPTY', (new WktTextStrategy())->executeStrategy($lines));
    }

    /** Write two distinct points with separate parentheses. */
    public function testMultiPoint(): void
    {
        $points = new MultiPoint([[1, 2], [3, 4]], 4326);

        static::assertSame('MULTIPOINT ((1 2), (3 4))', (new WktTextStrategy())->executeStrategy($points));
    }

    /** Preserve the type and dimension of an empty MultiPoint. */
    public function testMultiPointEmpty(): void
    {
        $points = new MultiPoint([], 4326);

        static::assertSame('MULTIPOINT EMPTY', (new WktTextStrategy())->executeStrategy($points));
    }

    /** Write two polygons with their own closed exterior rings. */
    public function testMultiPolygon(): void
    {
        $polygons = new MultiPolygon([
            [[[0, 0], [2, 0], [0, 2], [0, 0]]],
            [[[3, 3], [5, 3], [3, 5], [3, 3]]],
        ], 4326);

        static::assertSame(
            'MULTIPOLYGON (((0 0, 2 0, 0 2, 0 0)), ((3 3, 5 3, 3 5, 3 3)))',
            (new WktTextStrategy())->executeStrategy($polygons)
        );
    }

    /** Preserve the type and dimension of an empty MultiPolygon. */
    public function testMultiPolygonEmpty(): void
    {
        $polygons = new MultiPolygon([], 4326);

        static::assertSame('MULTIPOLYGON EMPTY', (new WktTextStrategy())->executeStrategy($polygons));
    }

    /** Write a point with fractional and negative ordinates. */
    public function testPoint(): void
    {
        $point = new Point(2.5, -3, 4326);

        static::assertSame('POINT (2.5 -3)', (new WktTextStrategy())->executeStrategy($point));
    }

    /** Preserve the type and dimension of an empty Point. */
    public function testPointEmpty(): void
    {
        $point = new Point(srid: 4326);

        static::assertSame('POINT EMPTY', (new WktTextStrategy())->executeStrategy($point));
    }

    /** Write an exterior ring followed by an interior hole. */
    public function testPolygon(): void
    {
        $polygon = new Polygon([
            [[0, 0], [4, 0], [4, 4], [0, 4], [0, 0]],
            [[1, 1], [1, 2], [2, 2], [2, 1], [1, 1]],
        ], 4326);

        static::assertSame(
            'POLYGON ((0 0, 4 0, 4 4, 0 4, 0 0), (1 1, 1 2, 2 2, 2 1, 1 1))',
            (new WktTextStrategy())->executeStrategy($polygon)
        );
    }

    /** Preserve the type and dimension of an empty Polygon. */
    public function testPolygonEmpty(): void
    {
        $polygon = new Polygon([], 4326);

        static::assertSame('POLYGON EMPTY', (new WktTextStrategy())->executeStrategy($polygon));
    }

    /** Write three vertices and the closing position. */
    public function testTriangle(): void
    {
        $triangle = new Triangle([[[0, 0], [2, 0], [0, 2], [0, 0]]], 4326);

        static::assertSame('TRIANGLE ((0 0, 2 0, 0 2, 0 0))', (new WktTextStrategy())->executeStrategy($triangle));
    }

    /** Preserve the type and dimension of an empty Triangle. */
    public function testTriangleEmpty(): void
    {
        $triangle = new Triangle([], 4326);

        static::assertSame('TRIANGLE EMPTY', (new WktTextStrategy())->executeStrategy($triangle));
    }
}
