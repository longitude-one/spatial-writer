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
use LongitudeOne\SpatialEncoder\Exception\UnsupportedSpatialTypeException;
use LongitudeOne\SpatialEncoder\Strategy\GeoJson\GeometryEncoder;
use LongitudeOne\SpatialEncoder\Strategy\GeoJsonStrategy;
use LongitudeOne\SpatialTypes\Reference\SpatialReference;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geometry\Point;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geometry\Triangle;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Explicit GeoJSON point examples.
 *
 * @internal
 */
#[CoversClass(GeoJsonStrategy::class)]
#[CoversClass(GeometryEncoder::class)]
#[CoversClass(Encoder::class)]
class GeometryPointTest extends TestCase
{
    /** Preserve the supplied point coordinates without reference metadata. */
    public function testEmptyXy(): void
    {
        $point = new Point();
        $encoder = new Encoder(new GeoJsonStrategy());

        $encoded = $encoder->encode($point);
        static::assertIsString($encoded);
        static::assertJsonStringEqualsJsonString('{"type":"Point","coordinates":[]}', $encoded);
        static::assertJsonStringEqualsJsonString('{"type":"Point","coordinates":[]}', (new GeoJsonStrategy())->encode($point));
    }

    /** Preserve the supplied point coordinates without reference metadata. */
    public function testEmptyXyz(): void
    {
        $point = new \LongitudeOne\SpatialTypes\Types\Dimension3z\Geometry\Point();
        $encoder = new Encoder(new GeoJsonStrategy());

        $encoded = $encoder->encode($point);
        static::assertIsString($encoded);
        static::assertJsonStringEqualsJsonString('{"type":"Point","coordinates":[]}', $encoded);
        static::assertJsonStringEqualsJsonString('{"type":"Point","coordinates":[]}', (new GeoJsonStrategy())->encode($point));
    }

    /** Preserve the supplied point coordinates without reference metadata. */
    public function testEpsg(): void
    {
        $point = new Point(1, 2, new SpatialReference(4326, 'EPSG'));
        $encoder = new Encoder(new GeoJsonStrategy());

        $encoded = $encoder->encode($point);
        static::assertIsString($encoded);
        static::assertJsonStringEqualsJsonString('{"type":"Point","coordinates":[1,2]}', $encoded);
        static::assertJsonStringEqualsJsonString('{"type":"Point","coordinates":[1,2]}', (new GeoJsonStrategy())->encode($point));
    }

    /** Preserve the supplied point coordinates without reference metadata. */
    public function testFractionalCoordinates(): void
    {
        $point = new Point(2.35, 48.86, 4326);
        $encoder = new Encoder(new GeoJsonStrategy());

        $encoded = $encoder->encode($point);
        static::assertIsString($encoded);
        static::assertJsonStringEqualsJsonString('{"type":"Point","coordinates":[2.35,48.86]}', $encoded);
        static::assertJsonStringEqualsJsonString('{"type":"Point","coordinates":[2.35,48.86]}', (new GeoJsonStrategy())->encode($point));
    }

    /** Preserve the supplied point coordinates without reference metadata. */
    public function testOtherSrid(): void
    {
        $point = new Point(1, 2, 3857);
        $encoder = new Encoder(new GeoJsonStrategy());

        $encoded = $encoder->encode($point);
        static::assertIsString($encoded);
        static::assertJsonStringEqualsJsonString('{"type":"Point","coordinates":[1,2]}', $encoded);
        static::assertJsonStringEqualsJsonString('{"type":"Point","coordinates":[1,2]}', (new GeoJsonStrategy())->encode($point));
    }

    /** Reject measured points without dropping or reinterpreting M. */
    public function testRejectEmptyXym(): void
    {
        $point = new \LongitudeOne\SpatialTypes\Types\Dimension3m\Geometry\Point();
        $this->expectException(UnsupportedDimensionException::class);
        (new GeoJsonStrategy())->encode($point);
    }

    /** Reject measured points without dropping or reinterpreting M. */
    public function testRejectEmptyXyzm(): void
    {
        $point = new \LongitudeOne\SpatialTypes\Types\Dimension4zm\Geometry\Point();
        $this->expectException(UnsupportedDimensionException::class);
        (new GeoJsonStrategy())->encode($point);
    }

    /** Do not encode a recognized non-GeoJSON type to another geometry. */
    public function testRejectTriangle(): void
    {
        $triangle = new Triangle([]);
        $this->expectException(UnsupportedSpatialTypeException::class);
        (new GeoJsonStrategy())->encode($triangle);
    }

    /** Reject measured points without dropping or reinterpreting M. */
    public function testRejectXym(): void
    {
        $point = new \LongitudeOne\SpatialTypes\Types\Dimension3m\Geometry\Point(1, 2, 4);
        $this->expectException(UnsupportedDimensionException::class);
        (new GeoJsonStrategy())->encode($point);
    }

    /** Reject measured points without dropping or reinterpreting M. */
    public function testRejectXyzm(): void
    {
        $point = new \LongitudeOne\SpatialTypes\Types\Dimension4zm\Geometry\Point(1, 2, 3, 4);
        $this->expectException(UnsupportedDimensionException::class);
        (new GeoJsonStrategy())->encode($point);
    }

    /** Preserve the supplied point coordinates without reference metadata. */
    public function testUnknownReference(): void
    {
        $point = new Point(1, 2, new SpatialReference(999999, 'CUSTOM'));
        $encoder = new Encoder(new GeoJsonStrategy());

        $encoded = $encoder->encode($point);
        static::assertIsString($encoded);
        static::assertJsonStringEqualsJsonString('{"type":"Point","coordinates":[1,2]}', $encoded);
        static::assertJsonStringEqualsJsonString('{"type":"Point","coordinates":[1,2]}', (new GeoJsonStrategy())->encode($point));
    }

    /** Preserve the supplied point coordinates without reference metadata. */
    public function testXy(): void
    {
        $point = new Point(1, 2);
        $encoder = new Encoder(new GeoJsonStrategy());

        $encoded = $encoder->encode($point);
        static::assertIsString($encoded);
        static::assertJsonStringEqualsJsonString('{"type":"Point","coordinates":[1,2]}', $encoded);
        static::assertJsonStringEqualsJsonString('{"type":"Point","coordinates":[1,2]}', (new GeoJsonStrategy())->encode($point));
    }

    /** Preserve the supplied point coordinates without reference metadata. */
    public function testXyz(): void
    {
        $point = new \LongitudeOne\SpatialTypes\Types\Dimension3z\Geometry\Point(1, 2, 3);
        $encoder = new Encoder(new GeoJsonStrategy());

        $encoded = $encoder->encode($point);
        static::assertIsString($encoded);
        static::assertJsonStringEqualsJsonString('{"type":"Point","coordinates":[1,2,3]}', $encoded);
        static::assertJsonStringEqualsJsonString('{"type":"Point","coordinates":[1,2,3]}', (new GeoJsonStrategy())->encode($point));
    }

    /** Preserve the supplied point coordinates without reference metadata. */
    public function testZeroSrid(): void
    {
        $point = new Point(1, 2, 0);
        $encoder = new Encoder(new GeoJsonStrategy());

        $encoded = $encoder->encode($point);
        static::assertIsString($encoded);
        static::assertJsonStringEqualsJsonString('{"type":"Point","coordinates":[1,2]}', $encoded);
        static::assertJsonStringEqualsJsonString('{"type":"Point","coordinates":[1,2]}', (new GeoJsonStrategy())->encode($point));
    }
}
