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
use LongitudeOne\SpatialTypes\Types\Dimension2\Geography\MultiPoint;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geography\Point;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Explicit GeoJSON MultiPoint examples using the concrete model.
 *
 * @internal
 */
#[CoversClass(GeoJsonStrategy::class)]
#[CoversClass(GeometryEncoder::class)]
#[CoversClass(Encoder::class)]
class GeographyMultiPointTest extends TestCase
{
    /** Preserve coordinates and their order without reference metadata. */
    public function testAdditionalXyzPositions(): void
    {
        $multiPoint = new \LongitudeOne\SpatialTypes\Types\Dimension3z\Geography\MultiPoint([[3, 4, 9.5], [1, 2, -2], [-7, 8, 0]]);
        $encoder = new Encoder(new GeoJsonStrategy());

        $encoded = $encoder->encode($multiPoint);
        static::assertIsString($encoded);
        static::assertJsonStringEqualsJsonString('{"type":"MultiPoint","coordinates":[[3,4,9.5],[1,2,-2],[-7,8,0]]}', $encoded);
        static::assertJsonStringEqualsJsonString('{"type":"MultiPoint","coordinates":[[3,4,9.5],[1,2,-2],[-7,8,0]]}', (new GeoJsonStrategy())->encode($multiPoint));
    }

    /** Preserve coordinates and their order without reference metadata. */
    public function testAntimeridian(): void
    {
        $multiPoint = new MultiPoint([[170, 45], [-170, 45]], 4326);
        $encoder = new Encoder(new GeoJsonStrategy());

        $encoded = $encoder->encode($multiPoint);
        static::assertIsString($encoded);
        static::assertJsonStringEqualsJsonString('{"type":"MultiPoint","coordinates":[[170,45],[-170,45]]}', $encoded);
        static::assertJsonStringEqualsJsonString('{"type":"MultiPoint","coordinates":[[170,45],[-170,45]]}', (new GeoJsonStrategy())->encode($multiPoint));
    }

    /** Preserve coordinates and their order without reference metadata. */
    public function testDeclaredEpsg(): void
    {
        $multiPoint = new \LongitudeOne\SpatialTypes\Types\Dimension3z\Geography\MultiPoint([[1, 2, 3], [4, 5, 6]], new SpatialReference(4326, 'EPSG'));
        $encoder = new Encoder(new GeoJsonStrategy());

        $encoded = $encoder->encode($multiPoint);
        static::assertIsString($encoded);
        static::assertJsonStringEqualsJsonString('{"type":"MultiPoint","coordinates":[[1,2,3],[4,5,6]]}', $encoded);
        static::assertJsonStringEqualsJsonString('{"type":"MultiPoint","coordinates":[[1,2,3],[4,5,6]]}', (new GeoJsonStrategy())->encode($multiPoint));
    }

    /** Preserve coordinates and their order without reference metadata. */
    public function testEmptyXy(): void
    {
        $multiPoint = new MultiPoint([]);
        $encoder = new Encoder(new GeoJsonStrategy());

        $encoded = $encoder->encode($multiPoint);
        static::assertIsString($encoded);
        static::assertJsonStringEqualsJsonString('{"type":"MultiPoint","coordinates":[]}', $encoded);
        static::assertJsonStringEqualsJsonString('{"type":"MultiPoint","coordinates":[]}', (new GeoJsonStrategy())->encode($multiPoint));
    }

    /** Preserve coordinates and their order without reference metadata. */
    public function testEmptyXyz(): void
    {
        $multiPoint = new \LongitudeOne\SpatialTypes\Types\Dimension3z\Geography\MultiPoint([]);
        $encoder = new Encoder(new GeoJsonStrategy());

        $encoded = $encoder->encode($multiPoint);
        static::assertIsString($encoded);
        static::assertJsonStringEqualsJsonString('{"type":"MultiPoint","coordinates":[]}', $encoded);
        static::assertJsonStringEqualsJsonString('{"type":"MultiPoint","coordinates":[]}', (new GeoJsonStrategy())->encode($multiPoint));
    }

    /** Preserve coordinates and their order without reference metadata. */
    public function testOtherSrid(): void
    {
        $multiPoint = new MultiPoint([[1, 2], [3, 4]], 3857);
        $encoder = new Encoder(new GeoJsonStrategy());

        $encoded = $encoder->encode($multiPoint);
        static::assertIsString($encoded);
        static::assertJsonStringEqualsJsonString('{"type":"MultiPoint","coordinates":[[1,2],[3,4]]}', $encoded);
        static::assertJsonStringEqualsJsonString('{"type":"MultiPoint","coordinates":[[1,2],[3,4]]}', (new GeoJsonStrategy())->encode($multiPoint));
    }

    /** Reject an incompatible concrete input without modifying it. */
    public function testRejectEmptyXym(): void
    {
        $multiPoint = new \LongitudeOne\SpatialTypes\Types\Dimension3m\Geography\MultiPoint([]);
        $this->expectException(UnsupportedDimensionException::class);
        (new Encoder(new GeoJsonStrategy()))->encode($multiPoint);
    }

    /** Reject an incompatible concrete input without modifying it. */
    public function testRejectEmptyXyzm(): void
    {
        $multiPoint = new \LongitudeOne\SpatialTypes\Types\Dimension4zm\Geography\MultiPoint([]);
        $this->expectException(UnsupportedDimensionException::class);
        (new Encoder(new GeoJsonStrategy()))->encode($multiPoint);
    }

    /** Reject an EMPTY member after a non-empty position without returning partial output. */
    public function testRejectMixedEmptyMembersXy(): void
    {
        $multiPoint = new MultiPoint([new Point(1, 2), new Point()]);
        $this->expectException(UnsupportedGeometryStructureException::class);
        (new Encoder(new GeoJsonStrategy()))->encode($multiPoint);
    }

    /** Reject an EMPTY member after a non-empty position without returning partial output. */
    public function testRejectMixedEmptyMembersXyz(): void
    {
        $multiPoint = new \LongitudeOne\SpatialTypes\Types\Dimension3z\Geography\MultiPoint([new \LongitudeOne\SpatialTypes\Types\Dimension3z\Geography\Point(1, 2, 3), new \LongitudeOne\SpatialTypes\Types\Dimension3z\Geography\Point()]);
        $this->expectException(UnsupportedGeometryStructureException::class);
        (new Encoder(new GeoJsonStrategy()))->encode($multiPoint);
    }

    /** Distinguish an aggregate containing only EMPTY members from one with no members. */
    public function testRejectOnlyEmptyMembersXy(): void
    {
        $multiPoint = new MultiPoint([new Point(), new Point()]);
        $this->expectException(UnsupportedGeometryStructureException::class);
        (new Encoder(new GeoJsonStrategy()))->encode($multiPoint);
    }

    /** Distinguish an aggregate containing only EMPTY members from one with no members. */
    public function testRejectOnlyEmptyMembersXyz(): void
    {
        $multiPoint = new \LongitudeOne\SpatialTypes\Types\Dimension3z\Geography\MultiPoint([new \LongitudeOne\SpatialTypes\Types\Dimension3z\Geography\Point(), new \LongitudeOne\SpatialTypes\Types\Dimension3z\Geography\Point()]);
        $this->expectException(UnsupportedGeometryStructureException::class);
        (new Encoder(new GeoJsonStrategy()))->encode($multiPoint);
    }

    /** Reject an incompatible concrete input without modifying it. */
    public function testRejectXym(): void
    {
        $multiPoint = new \LongitudeOne\SpatialTypes\Types\Dimension3m\Geography\MultiPoint([[1, 2, 3], [4, 5, 6]]);
        $this->expectException(UnsupportedDimensionException::class);
        (new Encoder(new GeoJsonStrategy()))->encode($multiPoint);
    }

    /** Reject an incompatible concrete input without modifying it. */
    public function testRejectXyzm(): void
    {
        $multiPoint = new \LongitudeOne\SpatialTypes\Types\Dimension4zm\Geography\MultiPoint([[1, 2, 3, 4], [5, 6, 7, 8]]);
        $this->expectException(UnsupportedDimensionException::class);
        (new Encoder(new GeoJsonStrategy()))->encode($multiPoint);
    }

    /** Preserve coordinates and their order without reference metadata. */
    public function testRepeatedPositions(): void
    {
        $multiPoint = new MultiPoint([[3.5, 4.25], [1, 2], [3.5, 4.25]]);
        $encoder = new Encoder(new GeoJsonStrategy());

        $encoded = $encoder->encode($multiPoint);
        static::assertIsString($encoded);
        static::assertJsonStringEqualsJsonString('{"type":"MultiPoint","coordinates":[[3.5,4.25],[1,2],[3.5,4.25]]}', $encoded);
        static::assertJsonStringEqualsJsonString('{"type":"MultiPoint","coordinates":[[3.5,4.25],[1,2],[3.5,4.25]]}', (new GeoJsonStrategy())->encode($multiPoint));
    }

    /** Preserve a singleton as a MultiPoint. */
    public function testSingletonXy(): void
    {
        $multiPoint = new MultiPoint([[1, 2]]);
        $encoded = (new Encoder(new GeoJsonStrategy()))->encode($multiPoint);
        static::assertIsString($encoded);
        static::assertJsonStringEqualsJsonString('{"type":"MultiPoint","coordinates":[[1,2]]}', $encoded);
    }

    /** Preserve a singleton as a MultiPoint. */
    public function testSingletonXyz(): void
    {
        $multiPoint = new \LongitudeOne\SpatialTypes\Types\Dimension3z\Geography\MultiPoint([[1, 2, 3]]);
        $encoded = (new Encoder(new GeoJsonStrategy()))->encode($multiPoint);
        static::assertIsString($encoded);
        static::assertJsonStringEqualsJsonString('{"type":"MultiPoint","coordinates":[[1,2,3]]}', $encoded);
    }

    /** Preserve coordinates and their order without reference metadata. */
    public function testUnknownReference(): void
    {
        $multiPoint = new MultiPoint([[1, 2], [3, 4]], new SpatialReference(999999, 'CUSTOM'));
        $encoder = new Encoder(new GeoJsonStrategy());

        $encoded = $encoder->encode($multiPoint);
        static::assertIsString($encoded);
        static::assertJsonStringEqualsJsonString('{"type":"MultiPoint","coordinates":[[1,2],[3,4]]}', $encoded);
        static::assertJsonStringEqualsJsonString('{"type":"MultiPoint","coordinates":[[1,2],[3,4]]}', (new GeoJsonStrategy())->encode($multiPoint));
    }

    /** Preserve coordinates and their order without reference metadata. */
    public function testXy(): void
    {
        $multiPoint = new MultiPoint([[1, 2], [3, 4]]);
        $encoder = new Encoder(new GeoJsonStrategy());

        $encoded = $encoder->encode($multiPoint);
        static::assertIsString($encoded);
        static::assertJsonStringEqualsJsonString('{"type":"MultiPoint","coordinates":[[1,2],[3,4]]}', $encoded);
        static::assertJsonStringEqualsJsonString('{"type":"MultiPoint","coordinates":[[1,2],[3,4]]}', (new GeoJsonStrategy())->encode($multiPoint));
    }

    /** Preserve coordinates and their order without reference metadata. */
    public function testXyz(): void
    {
        $multiPoint = new \LongitudeOne\SpatialTypes\Types\Dimension3z\Geography\MultiPoint([[1, 2, 3], [4, 5, 6]]);
        $encoder = new Encoder(new GeoJsonStrategy());

        $encoded = $encoder->encode($multiPoint);
        static::assertIsString($encoded);
        static::assertJsonStringEqualsJsonString('{"type":"MultiPoint","coordinates":[[1,2,3],[4,5,6]]}', $encoded);
        static::assertJsonStringEqualsJsonString('{"type":"MultiPoint","coordinates":[[1,2,3],[4,5,6]]}', (new GeoJsonStrategy())->encode($multiPoint));
    }
}
