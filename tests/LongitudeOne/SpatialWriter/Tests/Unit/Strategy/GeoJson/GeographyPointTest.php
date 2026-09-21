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
use LongitudeOne\SpatialTypes\Types\Dimension2\Geography\Point;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geography\Triangle;
use LongitudeOne\SpatialWriter\Exception\UnsupportedDimensionException;
use LongitudeOne\SpatialWriter\Exception\UnsupportedSpatialTypeException;
use LongitudeOne\SpatialWriter\Strategy\GeoJson\GeometryEncoder;
use LongitudeOne\SpatialWriter\Strategy\GeoJsonStrategy;
use LongitudeOne\SpatialWriter\Writer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Explicit GeoJSON point examples.
 *
 * @internal
 */
#[CoversClass(GeoJsonStrategy::class)]
#[CoversClass(GeometryEncoder::class)]
#[CoversClass(Writer::class)]
class GeographyPointTest extends TestCase
{
    /** Preserve the supplied point coordinates without reference metadata. */
    public function testEmptyXy(): void
    {
        $point = new Point();
        $writer = new Writer(new GeoJsonStrategy());

        static::assertJsonStringEqualsJsonString('{"type":"Point","coordinates":[]}', $writer->convert($point));
        static::assertJsonStringEqualsJsonString('{"type":"Point","coordinates":[]}', (new GeoJsonStrategy())->executeStrategy($point));
    }

    /** Preserve the supplied point coordinates without reference metadata. */
    public function testEmptyXyz(): void
    {
        $point = new \LongitudeOne\SpatialTypes\Types\Dimension3z\Geography\Point();
        $writer = new Writer(new GeoJsonStrategy());

        static::assertJsonStringEqualsJsonString('{"type":"Point","coordinates":[]}', $writer->convert($point));
        static::assertJsonStringEqualsJsonString('{"type":"Point","coordinates":[]}', (new GeoJsonStrategy())->executeStrategy($point));
    }

    /** Preserve the supplied point coordinates without reference metadata. */
    public function testEpsg(): void
    {
        $point = new Point(1, 2, new SpatialReference(4326, 'EPSG'));
        $writer = new Writer(new GeoJsonStrategy());

        static::assertJsonStringEqualsJsonString('{"type":"Point","coordinates":[1,2]}', $writer->convert($point));
        static::assertJsonStringEqualsJsonString('{"type":"Point","coordinates":[1,2]}', (new GeoJsonStrategy())->executeStrategy($point));
    }

    /** Preserve the supplied point coordinates without reference metadata. */
    public function testFractionalCoordinates(): void
    {
        $point = new Point(2.35, 48.86, 4326);
        $writer = new Writer(new GeoJsonStrategy());

        static::assertJsonStringEqualsJsonString('{"type":"Point","coordinates":[2.35,48.86]}', $writer->convert($point));
        static::assertJsonStringEqualsJsonString('{"type":"Point","coordinates":[2.35,48.86]}', (new GeoJsonStrategy())->executeStrategy($point));
    }

    /** Preserve the supplied point coordinates without reference metadata. */
    public function testOtherSrid(): void
    {
        $point = new Point(1, 2, 3857);
        $writer = new Writer(new GeoJsonStrategy());

        static::assertJsonStringEqualsJsonString('{"type":"Point","coordinates":[1,2]}', $writer->convert($point));
        static::assertJsonStringEqualsJsonString('{"type":"Point","coordinates":[1,2]}', (new GeoJsonStrategy())->executeStrategy($point));
    }

    /** Reject measured points without dropping or reinterpreting M. */
    public function testRejectEmptyXym(): void
    {
        $point = new \LongitudeOne\SpatialTypes\Types\Dimension3m\Geography\Point();
        $this->expectException(UnsupportedDimensionException::class);
        (new GeoJsonStrategy())->executeStrategy($point);
    }

    /** Reject measured points without dropping or reinterpreting M. */
    public function testRejectEmptyXyzm(): void
    {
        $point = new \LongitudeOne\SpatialTypes\Types\Dimension4zm\Geography\Point();
        $this->expectException(UnsupportedDimensionException::class);
        (new GeoJsonStrategy())->executeStrategy($point);
    }

    /** Do not convert a recognized non-GeoJSON type to another geometry. */
    public function testRejectTriangle(): void
    {
        $triangle = new Triangle([]);
        $this->expectException(UnsupportedSpatialTypeException::class);
        (new GeoJsonStrategy())->executeStrategy($triangle);
    }

    /** Reject measured points without dropping or reinterpreting M. */
    public function testRejectXym(): void
    {
        $point = new \LongitudeOne\SpatialTypes\Types\Dimension3m\Geography\Point(1, 2, 4);
        $this->expectException(UnsupportedDimensionException::class);
        (new GeoJsonStrategy())->executeStrategy($point);
    }

    /** Reject measured points without dropping or reinterpreting M. */
    public function testRejectXyzm(): void
    {
        $point = new \LongitudeOne\SpatialTypes\Types\Dimension4zm\Geography\Point(1, 2, 3, 4);
        $this->expectException(UnsupportedDimensionException::class);
        (new GeoJsonStrategy())->executeStrategy($point);
    }

    /** Preserve the supplied point coordinates without reference metadata. */
    public function testUnknownReference(): void
    {
        $point = new Point(1, 2, new SpatialReference(999999, 'CUSTOM'));
        $writer = new Writer(new GeoJsonStrategy());

        static::assertJsonStringEqualsJsonString('{"type":"Point","coordinates":[1,2]}', $writer->convert($point));
        static::assertJsonStringEqualsJsonString('{"type":"Point","coordinates":[1,2]}', (new GeoJsonStrategy())->executeStrategy($point));
    }

    /** Preserve the supplied point coordinates without reference metadata. */
    public function testXy(): void
    {
        $point = new Point(1, 2);
        $writer = new Writer(new GeoJsonStrategy());

        static::assertJsonStringEqualsJsonString('{"type":"Point","coordinates":[1,2]}', $writer->convert($point));
        static::assertJsonStringEqualsJsonString('{"type":"Point","coordinates":[1,2]}', (new GeoJsonStrategy())->executeStrategy($point));
    }

    /** Preserve the supplied point coordinates without reference metadata. */
    public function testXyz(): void
    {
        $point = new \LongitudeOne\SpatialTypes\Types\Dimension3z\Geography\Point(1, 2, 3);
        $writer = new Writer(new GeoJsonStrategy());

        static::assertJsonStringEqualsJsonString('{"type":"Point","coordinates":[1,2,3]}', $writer->convert($point));
        static::assertJsonStringEqualsJsonString('{"type":"Point","coordinates":[1,2,3]}', (new GeoJsonStrategy())->executeStrategy($point));
    }

    /** Preserve the supplied point coordinates without reference metadata. */
    public function testZeroSrid(): void
    {
        $point = new Point(1, 2, 0);
        $writer = new Writer(new GeoJsonStrategy());

        static::assertJsonStringEqualsJsonString('{"type":"Point","coordinates":[1,2]}', $writer->convert($point));
        static::assertJsonStringEqualsJsonString('{"type":"Point","coordinates":[1,2]}', (new GeoJsonStrategy())->executeStrategy($point));
    }
}
