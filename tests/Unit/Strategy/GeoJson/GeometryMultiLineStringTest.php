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

namespace LongitudeOne\SpatialEncoder\Tests\Unit\Strategy\GeoJson;

use LongitudeOne\SpatialEncoder\Encoder;
use LongitudeOne\SpatialEncoder\Exception\UnsupportedDimensionException;
use LongitudeOne\SpatialEncoder\Exception\UnsupportedGeometryStructureException;
use LongitudeOne\SpatialEncoder\Strategy\GeoJson\GeometryEncoder;
use LongitudeOne\SpatialEncoder\Strategy\GeoJsonStrategy;
use LongitudeOne\SpatialTypes\Reference\SpatialReference;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geometry\LineString;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geometry\MultiLineString;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Explicit Geometry MultiLineString GeoJSON examples.
 *
 * @internal
 */
#[CoversClass(GeoJsonStrategy::class)]
#[CoversClass(GeometryEncoder::class)]
#[CoversClass(Encoder::class)]
class GeometryMultiLineStringTest extends TestCase
{
    /** Preserve line membership, coordinates and order without reference metadata. */
    public function testAntimeridian(): void
    {
        $multiLine = new MultiLineString([[[170, 45], [-170, 45]], [[1, 2], [3, 4]]], 4326);

        $encoded = (new Encoder(new GeoJsonStrategy()))->encode($multiLine);
        static::assertIsString($encoded);
        static::assertJsonStringEqualsJsonString('{"type":"MultiLineString","coordinates":[[[170,45],[-170,45]],[[1,2],[3,4]]]}', $encoded);
        static::assertJsonStringEqualsJsonString('{"type":"MultiLineString","coordinates":[[[170,45],[-170,45]],[[1,2],[3,4]]]}', (new GeoJsonStrategy())->encode($multiLine));
    }

    /** Preserve line membership, coordinates and order without reference metadata. */
    public function testDeclaredReference(): void
    {
        $multiLine = new \LongitudeOne\SpatialTypes\Types\Dimension3z\Geometry\MultiLineString([[[1, 2, 3], [4, 5, 6]]], new SpatialReference(4326, 'EPSG'));

        $encoded = (new Encoder(new GeoJsonStrategy()))->encode($multiLine);
        static::assertIsString($encoded);
        static::assertJsonStringEqualsJsonString('{"type":"MultiLineString","coordinates":[[[1,2,3],[4,5,6]]]}', $encoded);
        static::assertJsonStringEqualsJsonString('{"type":"MultiLineString","coordinates":[[[1,2,3],[4,5,6]]]}', (new GeoJsonStrategy())->encode($multiLine));
    }

    /** Preserve line membership, coordinates and order without reference metadata. */
    public function testEmptyXy(): void
    {
        $multiLine = new MultiLineString([]);

        $encoded = (new Encoder(new GeoJsonStrategy()))->encode($multiLine);
        static::assertIsString($encoded);
        static::assertJsonStringEqualsJsonString('{"type":"MultiLineString","coordinates":[]}', $encoded);
        static::assertJsonStringEqualsJsonString('{"type":"MultiLineString","coordinates":[]}', (new GeoJsonStrategy())->encode($multiLine));
    }

    /** Preserve line membership, coordinates and order without reference metadata. */
    public function testEmptyXyz(): void
    {
        $multiLine = new \LongitudeOne\SpatialTypes\Types\Dimension3z\Geometry\MultiLineString([]);

        $encoded = (new Encoder(new GeoJsonStrategy()))->encode($multiLine);
        static::assertIsString($encoded);
        static::assertJsonStringEqualsJsonString('{"type":"MultiLineString","coordinates":[]}', $encoded);
        static::assertJsonStringEqualsJsonString('{"type":"MultiLineString","coordinates":[]}', (new GeoJsonStrategy())->encode($multiLine));
    }

    /** Preserve line membership, coordinates and order without reference metadata. */
    public function testMemberAndPositionOrder(): void
    {
        $multiLine = new MultiLineString([[[7.5, 8], [5, 6], [-2, 1]], [[3, 4], [1, 2]]]);

        $encoded = (new Encoder(new GeoJsonStrategy()))->encode($multiLine);
        static::assertIsString($encoded);
        static::assertJsonStringEqualsJsonString('{"type":"MultiLineString","coordinates":[[[7.5,8],[5,6],[-2,1]],[[3,4],[1,2]]]}', $encoded);
        static::assertJsonStringEqualsJsonString('{"type":"MultiLineString","coordinates":[[[7.5,8],[5,6],[-2,1]],[[3,4],[1,2]]]}', (new GeoJsonStrategy())->encode($multiLine));
    }

    /** Preserve line membership, coordinates and order without reference metadata. */
    public function testOtherSrid(): void
    {
        $multiLine = new MultiLineString([new LineString([[1, 2], [3, 4]], 3857)], 3857);

        $encoded = (new Encoder(new GeoJsonStrategy()))->encode($multiLine);
        static::assertIsString($encoded);
        static::assertJsonStringEqualsJsonString('{"type":"MultiLineString","coordinates":[[[1,2],[3,4]]]}', $encoded);
        static::assertJsonStringEqualsJsonString('{"type":"MultiLineString","coordinates":[[[1,2],[3,4]]]}', (new GeoJsonStrategy())->encode($multiLine));
    }

    /** Reject an incompatible line member instead of returning partial output. */
    public function testRejectAllEmpty(): void
    {
        $multiLine = new MultiLineString([new LineString([]), new LineString([])]);

        $this->expectException(UnsupportedGeometryStructureException::class);
        (new Encoder(new GeoJsonStrategy()))->encode($multiLine);
    }

    /** Reject an incompatible line member instead of returning partial output. */
    public function testRejectAllEmptyXyz(): void
    {
        $multiLine = new \LongitudeOne\SpatialTypes\Types\Dimension3z\Geometry\MultiLineString([[], []]);

        $this->expectException(UnsupportedGeometryStructureException::class);
        (new Encoder(new GeoJsonStrategy()))->encode($multiLine);
    }

    /** Reject an incompatible line member instead of returning partial output. */
    public function testRejectEmptyFirst(): void
    {
        $multiLine = new MultiLineString([new LineString([]), new LineString([[1, 2], [3, 4]])]);

        $this->expectException(UnsupportedGeometryStructureException::class);
        (new Encoder(new GeoJsonStrategy()))->encode($multiLine);
    }

    /** Reject an incompatible line member instead of returning partial output. */
    public function testRejectEmptyLast(): void
    {
        $multiLine = new MultiLineString([new LineString([[1, 2], [3, 4]]), new LineString([])]);

        $this->expectException(UnsupportedGeometryStructureException::class);
        (new Encoder(new GeoJsonStrategy()))->encode($multiLine);
    }

    /** Reject measured dimensions even when no line members are present. */
    public function testRejectEmptyXym(): void
    {
        $multiLine = new \LongitudeOne\SpatialTypes\Types\Dimension3m\Geometry\MultiLineString([]);

        $this->expectException(UnsupportedDimensionException::class);
        (new Encoder(new GeoJsonStrategy()))->encode($multiLine);
    }

    /** Reject measured dimensions even when no line members are present. */
    public function testRejectEmptyXyzm(): void
    {
        $multiLine = new \LongitudeOne\SpatialTypes\Types\Dimension4zm\Geometry\MultiLineString([]);

        $this->expectException(UnsupportedDimensionException::class);
        (new Encoder(new GeoJsonStrategy()))->encode($multiLine);
    }

    /** Reject an incompatible line member instead of returning partial output. */
    public function testRejectEmptyXyzMember(): void
    {
        $multiLine = new \LongitudeOne\SpatialTypes\Types\Dimension3z\Geometry\MultiLineString([[[1, 2, 3], [4, 5, 6]], []]);

        $this->expectException(UnsupportedGeometryStructureException::class);
        (new Encoder(new GeoJsonStrategy()))->encode($multiLine);
    }

    /** Reject an incompatible line member instead of returning partial output. */
    public function testRejectSingletonMember(): void
    {
        $multiLine = new MultiLineString([[[1, 2], [3, 4]], [[5, 6]]]);

        $this->expectException(UnsupportedGeometryStructureException::class);
        (new Encoder(new GeoJsonStrategy()))->encode($multiLine);
    }

    /** Reject an incompatible line member instead of returning partial output. */
    public function testRejectSingletonXyzMember(): void
    {
        $multiLine = new \LongitudeOne\SpatialTypes\Types\Dimension3z\Geometry\MultiLineString([[[1, 2, 3]]]);

        $this->expectException(UnsupportedGeometryStructureException::class);
        (new Encoder(new GeoJsonStrategy()))->encode($multiLine);
    }

    /** Reject measured dimensions even when no line members are present. */
    public function testRejectXym(): void
    {
        $multiLine = new \LongitudeOne\SpatialTypes\Types\Dimension3m\Geometry\MultiLineString([[[1, 2, 3], [4, 5, 6]]]);

        $this->expectException(UnsupportedDimensionException::class);
        (new Encoder(new GeoJsonStrategy()))->encode($multiLine);
    }

    /** Reject measured dimensions even when no line members are present. */
    public function testRejectXyzm(): void
    {
        $multiLine = new \LongitudeOne\SpatialTypes\Types\Dimension4zm\Geometry\MultiLineString([[[1, 2, 3, 4], [5, 6, 7, 8]]]);

        $this->expectException(UnsupportedDimensionException::class);
        (new Encoder(new GeoJsonStrategy()))->encode($multiLine);
    }

    /** Preserve line membership, coordinates and order without reference metadata. */
    public function testSingleMember(): void
    {
        $multiLine = new MultiLineString([[[3, 4], [1, 2]]]);

        $encoded = (new Encoder(new GeoJsonStrategy()))->encode($multiLine);
        static::assertIsString($encoded);
        static::assertJsonStringEqualsJsonString('{"type":"MultiLineString","coordinates":[[[3,4],[1,2]]]}', $encoded);
        static::assertJsonStringEqualsJsonString('{"type":"MultiLineString","coordinates":[[[3,4],[1,2]]]}', (new GeoJsonStrategy())->encode($multiLine));
    }

    /** Preserve line membership, coordinates and order without reference metadata. */
    public function testUnknownReference(): void
    {
        $multiLine = new MultiLineString([[[1, 2], [3, 4]]], new SpatialReference(999999, 'CUSTOM'));

        $encoded = (new Encoder(new GeoJsonStrategy()))->encode($multiLine);
        static::assertIsString($encoded);
        static::assertJsonStringEqualsJsonString('{"type":"MultiLineString","coordinates":[[[1,2],[3,4]]]}', $encoded);
        static::assertJsonStringEqualsJsonString('{"type":"MultiLineString","coordinates":[[[1,2],[3,4]]]}', (new GeoJsonStrategy())->encode($multiLine));
    }

    /** Preserve line membership, coordinates and order without reference metadata. */
    public function testXy(): void
    {
        $multiLine = new MultiLineString([[[1, 2], [3, 4]], [[5, 6], [7, 8]]]);

        $encoded = (new Encoder(new GeoJsonStrategy()))->encode($multiLine);
        static::assertIsString($encoded);
        static::assertJsonStringEqualsJsonString('{"type":"MultiLineString","coordinates":[[[1,2],[3,4]],[[5,6],[7,8]]]}', $encoded);
        static::assertJsonStringEqualsJsonString('{"type":"MultiLineString","coordinates":[[[1,2],[3,4]],[[5,6],[7,8]]]}', (new GeoJsonStrategy())->encode($multiLine));
    }

    /** Preserve line membership, coordinates and order without reference metadata. */
    public function testXyz(): void
    {
        $multiLine = new \LongitudeOne\SpatialTypes\Types\Dimension3z\Geometry\MultiLineString([[[1, 2, 3], [4, 5, 6]], [[7, 8, 9], [10, 11, 12]]]);

        $encoded = (new Encoder(new GeoJsonStrategy()))->encode($multiLine);
        static::assertIsString($encoded);
        static::assertJsonStringEqualsJsonString('{"type":"MultiLineString","coordinates":[[[1,2,3],[4,5,6]],[[7,8,9],[10,11,12]]]}', $encoded);
        static::assertJsonStringEqualsJsonString('{"type":"MultiLineString","coordinates":[[[1,2,3],[4,5,6]],[[7,8,9],[10,11,12]]]}', (new GeoJsonStrategy())->encode($multiLine));
    }
}
