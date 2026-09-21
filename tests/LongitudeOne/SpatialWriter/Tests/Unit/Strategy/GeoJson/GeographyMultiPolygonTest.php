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

namespace LongitudeOne\SpatialWriter\Tests\Unit\Strategy\GeoJson;

use LongitudeOne\SpatialTypes\Reference\SpatialReference;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geography\MultiPolygon;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geography\Polygon;
use LongitudeOne\SpatialWriter\Exception\UnsupportedDimensionException;
use LongitudeOne\SpatialWriter\Exception\UnsupportedGeometryStructureException;
use LongitudeOne\SpatialWriter\Strategy\GeoJson\GeometryEncoder;
use LongitudeOne\SpatialWriter\Strategy\GeoJsonStrategy;
use LongitudeOne\SpatialWriter\Writer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Explicit Geography MultiPolygon GeoJSON examples.
 *
 * @internal
 */
#[CoversClass(GeoJsonStrategy::class)]
#[CoversClass(GeometryEncoder::class)]
#[CoversClass(Writer::class)]
class GeographyMultiPolygonTest extends TestCase
{
    /** Keep crossings without cutting or unwrapping. */
    public function testAntimeridian(): void
    {
        $multiPolygon = new MultiPolygon([[[[170, 40], [170, 50], [-170, 50], [-170, 40], [170, 40]]]], 4326);

        static::assertJsonStringEqualsJsonString('{"type":"MultiPolygon","coordinates":[[[[170,40],[170,50],[-170,50],[-170,40],[170,40]]]]}', (new Writer(new GeoJsonStrategy()))->convert($multiPolygon));
        static::assertJsonStringEqualsJsonString('{"type":"MultiPolygon","coordinates":[[[[170,40],[170,50],[-170,50],[-170,40],[170,40]]]]}', (new GeoJsonStrategy())->executeStrategy($multiPolygon));
    }

    /** An aggregate without members is supported. */
    public function testEmptyXy(): void
    {
        $multiPolygon = new MultiPolygon([]);

        static::assertJsonStringEqualsJsonString('{"type":"MultiPolygon","coordinates":[]}', (new Writer(new GeoJsonStrategy()))->convert($multiPolygon));
        static::assertJsonStringEqualsJsonString('{"type":"MultiPolygon","coordinates":[]}', (new GeoJsonStrategy())->executeStrategy($multiPolygon));
    }

    /** An empty XYZ aggregate has no dimension marker. */
    public function testEmptyXyz(): void
    {
        $multiPolygon = new \LongitudeOne\SpatialTypes\Types\Dimension3z\Geography\MultiPolygon([]);

        static::assertJsonStringEqualsJsonString('{"type":"MultiPolygon","coordinates":[]}', (new Writer(new GeoJsonStrategy()))->convert($multiPolygon));
        static::assertJsonStringEqualsJsonString('{"type":"MultiPolygon","coordinates":[]}', (new GeoJsonStrategy())->executeStrategy($multiPolygon));
    }

    /** Reject the whole aggregate through the approved structural contract. */
    public function testRejectClockwiseLaterExterior(): void
    {
        $multiPolygon = new MultiPolygon([[[[0, 0], [2, 0], [0, 2], [0, 0]]], [[[3, 0], [3, 2], [5, 0], [3, 0]]]]);

        $this->expectException(UnsupportedGeometryStructureException::class);
        (new Writer(new GeoJsonStrategy()))->convert($multiPolygon);
    }

    /** Reject the whole aggregate through the approved structural contract. */
    public function testRejectCounterclockwiseLaterHole(): void
    {
        $multiPolygon = new MultiPolygon([[[[10, 0], [12, 0], [10, 2], [10, 0]]], [[[0, 0], [4, 0], [4, 4], [0, 4], [0, 0]], [[1, 1], [2, 1], [2, 2], [1, 2], [1, 1]]]]);

        $this->expectException(UnsupportedGeometryStructureException::class);
        (new Writer(new GeoJsonStrategy()))->convert($multiPolygon);
    }

    /** Reject the whole aggregate through the approved structural contract. */
    public function testRejectEmptyMember(): void
    {
        $multiPolygon = new MultiPolygon([new Polygon([[[0, 0], [2, 0], [0, 2], [0, 0]]]), new Polygon([])]);

        $this->expectException(UnsupportedGeometryStructureException::class);
        (new Writer(new GeoJsonStrategy()))->convert($multiPolygon);
    }

    /** Reject measured dimensions without discarding M. */
    public function testRejectEmptyXym(): void
    {
        $multiPolygon = new \LongitudeOne\SpatialTypes\Types\Dimension3m\Geography\MultiPolygon([]);

        $this->expectException(UnsupportedDimensionException::class);
        (new Writer(new GeoJsonStrategy()))->convert($multiPolygon);
    }

    /** Reject measured dimensions without discarding M. */
    public function testRejectEmptyXyzm(): void
    {
        $multiPolygon = new \LongitudeOne\SpatialTypes\Types\Dimension4zm\Geography\MultiPolygon([]);

        $this->expectException(UnsupportedDimensionException::class);
        (new Writer(new GeoJsonStrategy()))->convert($multiPolygon);
    }

    /** Reject the whole aggregate through the approved structural contract. */
    public function testRejectOnlyEmptyMembers(): void
    {
        $multiPolygon = new MultiPolygon([new Polygon([]), new Polygon([])]);

        $this->expectException(UnsupportedGeometryStructureException::class);
        (new Writer(new GeoJsonStrategy()))->convert($multiPolygon);
    }

    /** Reject measured dimensions without discarding M. */
    public function testRejectXym(): void
    {
        $multiPolygon = new \LongitudeOne\SpatialTypes\Types\Dimension3m\Geography\MultiPolygon([[[[0, 0, 3], [2, 0, 3], [0, 2, 3], [0, 0, 3]]]]);

        $this->expectException(UnsupportedDimensionException::class);
        (new Writer(new GeoJsonStrategy()))->convert($multiPolygon);
    }

    /** Reject measured dimensions without discarding M. */
    public function testRejectXyzm(): void
    {
        $multiPolygon = new \LongitudeOne\SpatialTypes\Types\Dimension4zm\Geography\MultiPolygon([[[[0, 0, 3, 4], [2, 0, 3, 4], [0, 2, 3, 4], [0, 0, 3, 4]]]]);

        $this->expectException(UnsupportedDimensionException::class);
        (new Writer(new GeoJsonStrategy()))->convert($multiPolygon);
    }

    /** Reject the whole aggregate through the approved structural contract. */
    public function testRejectZeroAreaExterior(): void
    {
        $multiPolygon = new MultiPolygon([[[[0, 0], [1, 0], [2, 0], [0, 0]]]]);

        $this->expectException(UnsupportedGeometryStructureException::class);
        (new Writer(new GeoJsonStrategy()))->convert($multiPolygon);
    }

    /** Preserve multiple holes, member order and decimal coordinates. */
    public function testRingOrder(): void
    {
        $multiPolygon = new MultiPolygon([[[[0, 0], [8, 0], [8, 8], [0, 8], [0, 0]], [[1, 1], [1, 2], [2, 2], [2, 1], [1, 1]], [[4, 4], [4, 5], [5, 5], [5, 4], [4, 4]]], [[[10.5, 0], [12, 0], [10.5, 2], [10.5, 0]]]]);

        static::assertJsonStringEqualsJsonString('{"type":"MultiPolygon","coordinates":[[[[0,0],[8,0],[8,8],[0,8],[0,0]],[[1,1],[1,2],[2,2],[2,1],[1,1]],[[4,4],[4,5],[5,5],[5,4],[4,4]]],[[[10.5,0],[12,0],[10.5,2],[10.5,0]]]]}', (new Writer(new GeoJsonStrategy()))->convert($multiPolygon));
        static::assertJsonStringEqualsJsonString('{"type":"MultiPolygon","coordinates":[[[[0,0],[8,0],[8,8],[0,8],[0,0]],[[1,1],[1,2],[2,2],[2,1],[1,1]],[[4,4],[4,5],[5,5],[5,4],[4,4]]],[[[10.5,0],[12,0],[10.5,2],[10.5,0]]]]}', (new GeoJsonStrategy())->executeStrategy($multiPolygon));
    }

    /** Preserve member and position order with SRID omitted. */
    public function testXy(): void
    {
        $multiPolygon = new MultiPolygon([[[[0, 0], [2, 0], [0, 2], [0, 0]]], [[[3, 0], [5, 0], [3, 2], [3, 0]]]], 4326);

        static::assertJsonStringEqualsJsonString('{"type":"MultiPolygon","coordinates":[[[[0,0],[2,0],[0,2],[0,0]]],[[[3,0],[5,0],[3,2],[3,0]]]]}', (new Writer(new GeoJsonStrategy()))->convert($multiPolygon));
        static::assertJsonStringEqualsJsonString('{"type":"MultiPolygon","coordinates":[[[[0,0],[2,0],[0,2],[0,0]]],[[[3,0],[5,0],[3,2],[3,0]]]]}', (new GeoJsonStrategy())->executeStrategy($multiPolygon));
    }

    /** Keep the singleton wrapper, Z and coordinates without reference metadata. */
    public function testXyz(): void
    {
        $multiPolygon = new \LongitudeOne\SpatialTypes\Types\Dimension3z\Geography\MultiPolygon([[[[0, 0, 3], [2, 0, 3], [0, 2, 3], [0, 0, 3]]]], new SpatialReference(999999, 'CUSTOM'));

        static::assertJsonStringEqualsJsonString('{"type":"MultiPolygon","coordinates":[[[[0,0,3],[2,0,3],[0,2,3],[0,0,3]]]]}', (new Writer(new GeoJsonStrategy()))->convert($multiPolygon));
        static::assertJsonStringEqualsJsonString('{"type":"MultiPolygon","coordinates":[[[[0,0,3],[2,0,3],[0,2,3],[0,0,3]]]]}', (new GeoJsonStrategy())->executeStrategy($multiPolygon));
    }
}
