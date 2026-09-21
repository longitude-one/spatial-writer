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
use LongitudeOne\SpatialTypes\Types\Dimension2\Geography\Polygon;
use LongitudeOne\SpatialWriter\Exception\UnsupportedDimensionException;
use LongitudeOne\SpatialWriter\Exception\UnsupportedGeometryStructureException;
use LongitudeOne\SpatialWriter\Strategy\GeoJson\GeometryEncoder;
use LongitudeOne\SpatialWriter\Strategy\GeoJsonStrategy;
use LongitudeOne\SpatialWriter\Writer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Explicit Geography Polygon GeoJSON examples.
 *
 * @internal
 */
#[CoversClass(GeoJsonStrategy::class)]
#[CoversClass(GeometryEncoder::class)]
#[CoversClass(Writer::class)]
class GeographyPolygonTest extends TestCase
{
    /** Keep supplied longitudes without cutting or unwrapping the ring. */
    public function testAntimeridian(): void
    {
        $polygon = new Polygon([[[170, 40], [170, 50], [-170, 50], [-170, 40], [170, 40]]], 4326);

        static::assertJsonStringEqualsJsonString('{"type":"Polygon","coordinates":[[[170,40],[170,50],[-170,50],[-170,40],[170,40]]]}', (new Writer(new GeoJsonStrategy()))->convert($polygon));
    }

    /** Reject clockwise exteriors without reversing the source ring. */
    public function testClockwiseExterior(): void
    {
        $polygon = new Polygon([[[0, 0], [0, 2], [2, 0], [0, 0]]]);

        try {
            (new Writer(new GeoJsonStrategy()))->convert($polygon);
            static::fail('Clockwise exterior must be rejected.');
        } catch (UnsupportedGeometryStructureException) {
            static::assertSame([[[0, 0], [0, 2], [2, 0], [0, 0]]], $polygon->toArray());
        }
    }

    /** Reject an incompatible hole without returning partial output. */
    public function testCounterclockwiseHole(): void
    {
        $polygon = new Polygon([[[0, 0], [4, 0], [4, 4], [0, 4], [0, 0]], [[1, 1], [2, 1], [2, 2], [1, 2], [1, 1]]]);

        $this->expectException(UnsupportedGeometryStructureException::class);
        (new Writer(new GeoJsonStrategy()))->convert($polygon);
    }

    /** A positive signed area does not claim or require ring simplicity. */
    public function testCrossedRingWithPositiveArea(): void
    {
        $polygon = new Polygon([[[0, 0], [3, 3], [0, 3], [2, 0], [0, 0]]]);

        static::assertJsonStringEqualsJsonString('{"type":"Polygon","coordinates":[[[0,0],[3,3],[0,3],[2,0],[0,0]]]}', (new Writer(new GeoJsonStrategy()))->convert($polygon));
    }

    /** EMPTY is an empty coordinates array. */
    public function testEmptyXy(): void
    {
        $polygon = new Polygon([]);

        static::assertJsonStringEqualsJsonString('{"type":"Polygon","coordinates":[]}', (new Writer(new GeoJsonStrategy()))->convert($polygon));
    }

    /** EMPTY has no artificial dimension marker. */
    public function testEmptyXyz(): void
    {
        $polygon = new \LongitudeOne\SpatialTypes\Types\Dimension3z\Geography\Polygon([]);

        static::assertJsonStringEqualsJsonString('{"type":"Polygon","coordinates":[]}', (new Writer(new GeoJsonStrategy()))->convert($polygon));
    }

    /** Preserve exterior and interior ring order and winding. */
    public function testHole(): void
    {
        $polygon = new Polygon([[[0, 0], [4, 0], [4, 4], [0, 4], [0, 0]], [[1, 1], [1, 2], [2, 2], [2, 1], [1, 1]]]);

        static::assertJsonStringEqualsJsonString('{"type":"Polygon","coordinates":[[[0,0],[4,0],[4,4],[0,4],[0,0]],[[1,1],[1,2],[2,2],[2,1],[1,1]]]}', (new Writer(new GeoJsonStrategy()))->convert($polygon));
    }

    /** Reject measured dimensions even when EMPTY. */
    public function testRejectEmptyXym(): void
    {
        $polygon = new \LongitudeOne\SpatialTypes\Types\Dimension3m\Geography\Polygon([]);

        $this->expectException(UnsupportedDimensionException::class);
        (new Writer(new GeoJsonStrategy()))->convert($polygon);
    }

    /** Reject measured dimensions even when EMPTY. */
    public function testRejectEmptyXyzm(): void
    {
        $polygon = new \LongitudeOne\SpatialTypes\Types\Dimension4zm\Geography\Polygon([]);

        $this->expectException(UnsupportedDimensionException::class);
        (new Writer(new GeoJsonStrategy()))->convert($polygon);
    }

    /** A later hole must also be checked before returning any output. */
    public function testRejectLaterCounterclockwiseHole(): void
    {
        $polygon = new Polygon([
            [[0, 0], [8, 0], [8, 8], [0, 8], [0, 0]],
            [[1, 1], [1, 2], [2, 2], [2, 1], [1, 1]],
            [[4, 4], [5, 4], [5, 5], [4, 5], [4, 4]],
        ]);

        $this->expectException(UnsupportedGeometryStructureException::class);
        (new Writer(new GeoJsonStrategy()))->convert($polygon);
    }

    /** M must not be serialized as altitude. */
    public function testRejectXym(): void
    {
        $polygon = new \LongitudeOne\SpatialTypes\Types\Dimension3m\Geography\Polygon([[[0, 0, 3], [2, 0, 3], [0, 2, 3], [0, 0, 3]]]);

        $this->expectException(UnsupportedDimensionException::class);
        (new Writer(new GeoJsonStrategy()))->convert($polygon);
    }

    /** M must not be discarded. */
    public function testRejectXyzm(): void
    {
        $polygon = new \LongitudeOne\SpatialTypes\Types\Dimension4zm\Geography\Polygon([[[0, 0, 3, 4], [2, 0, 3, 4], [0, 2, 3, 4], [0, 0, 3, 4]]]);

        $this->expectException(UnsupportedDimensionException::class);
        (new Writer(new GeoJsonStrategy()))->convert($polygon);
    }

    /** The bow-tie is rejected for zero signed area, without topology analysis. */
    public function testRejectZeroAreaCrossedRing(): void
    {
        $polygon = new Polygon([[[0, 0], [2, 2], [0, 2], [2, 0], [0, 0]]]);

        $this->expectException(UnsupportedGeometryStructureException::class);
        (new Writer(new GeoJsonStrategy()))->convert($polygon);
    }

    /** Collinear rings have no winding and are rejected by the approved policy. */
    public function testRejectZeroAreaExterior(): void
    {
        $polygon = new Polygon([[[0, 0], [1, 0], [2, 0], [0, 0]]]);

        $this->expectException(UnsupportedGeometryStructureException::class);
        (new Writer(new GeoJsonStrategy()))->convert($polygon);
    }

    /** The orientation policy applies to every interior ring as well. */
    public function testRejectZeroAreaHole(): void
    {
        $polygon = new Polygon([[[0, 0], [4, 0], [4, 4], [0, 4], [0, 0]], [[1, 1], [2, 1], [3, 1], [1, 1]]]);

        $this->expectException(UnsupportedGeometryStructureException::class);
        (new Writer(new GeoJsonStrategy()))->convert($polygon);
    }

    /** Varying height does not change the XY winding or position order. */
    public function testVaryingAltitudeAndMultipleHoles(): void
    {
        $polygon = new \LongitudeOne\SpatialTypes\Types\Dimension3z\Geography\Polygon([
            [[0, 0, 1], [8, 0, 2], [8, 8, 3], [0, 8, 4], [0, 0, 1]],
            [[1, 1, 5], [1, 2, 6], [2, 2, 7], [2, 1, 8], [1, 1, 5]],
            [[4, 4, 9], [4, 5, 10], [5, 5, 11], [5, 4, 12], [4, 4, 9]],
        ]);

        static::assertJsonStringEqualsJsonString('{"type":"Polygon","coordinates":[[[0,0,1],[8,0,2],[8,8,3],[0,8,4],[0,0,1]],[[1,1,5],[1,2,6],[2,2,7],[2,1,8],[1,1,5]],[[4,4,9],[4,5,10],[5,5,11],[5,4,12],[4,4,9]]]}', (new Writer(new GeoJsonStrategy()))->convert($polygon));
    }

    /** Preserve the exterior ring and the original object. */
    public function testXy(): void
    {
        $polygon = new Polygon([[[0, 0], [2, 0], [0, 2], [0, 0]]], 4326);

        static::assertJsonStringEqualsJsonString('{"type":"Polygon","coordinates":[[[0,0],[2,0],[0,2],[0,0]]]}', (new Writer(new GeoJsonStrategy()))->convert($polygon));
        static::assertSame([[[0, 0], [2, 0], [0, 2], [0, 0]]], $polygon->toArray());
    }

    /** Preserve every Z value and omit declared reference metadata. */
    public function testXyz(): void
    {
        $polygon = new \LongitudeOne\SpatialTypes\Types\Dimension3z\Geography\Polygon([[[0, 0, 3], [2, 0, 3], [0, 2, 3], [0, 0, 3]]], new SpatialReference(999999, 'CUSTOM'));

        static::assertJsonStringEqualsJsonString('{"type":"Polygon","coordinates":[[[0,0,3],[2,0,3],[0,2,3],[0,0,3]]]}', (new Writer(new GeoJsonStrategy()))->convert($polygon));
    }
}
