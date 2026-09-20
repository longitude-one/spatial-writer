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
use LongitudeOne\SpatialTypes\Types\Dimension2\Geography\MultiPoint;
use LongitudeOne\SpatialWriter\Exception\UnsupportedDimensionException;
use LongitudeOne\SpatialWriter\Strategy\GeoJsonStrategy;
use LongitudeOne\SpatialWriter\Writer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Explicit GeoJSON MultiPoint examples using the concrete model.
 *
 * @internal
 */
#[CoversClass(GeoJsonStrategy::class)]
#[CoversClass(Writer::class)]
class GeographyMultiPointTest extends TestCase
{
    /** Preserve coordinates and their order without reference metadata. */
    public function testAdditionalXyzPositions(): void
    {
        $multiPoint = new \LongitudeOne\SpatialTypes\Types\Dimension3z\Geography\MultiPoint([[3, 4, 9.5], [1, 2, -2], [-7, 8, 0]]);
        $writer = new Writer(new GeoJsonStrategy());

        static::assertJsonStringEqualsJsonString('{"type":"MultiPoint","coordinates":[[3,4,9.5],[1,2,-2],[-7,8,0]]}', $writer->convert($multiPoint));
        static::assertJsonStringEqualsJsonString('{"type":"MultiPoint","coordinates":[[3,4,9.5],[1,2,-2],[-7,8,0]]}', (new GeoJsonStrategy())->executeStrategy($multiPoint));
    }

    /** Preserve coordinates and their order without reference metadata. */
    public function testAntimeridian(): void
    {
        $multiPoint = new MultiPoint([[170, 45], [-170, 45]], 4326);
        $writer = new Writer(new GeoJsonStrategy());

        static::assertJsonStringEqualsJsonString('{"type":"MultiPoint","coordinates":[[170,45],[-170,45]]}', $writer->convert($multiPoint));
        static::assertJsonStringEqualsJsonString('{"type":"MultiPoint","coordinates":[[170,45],[-170,45]]}', (new GeoJsonStrategy())->executeStrategy($multiPoint));
    }

    /** Preserve coordinates and their order without reference metadata. */
    public function testDeclaredEpsg(): void
    {
        $multiPoint = new \LongitudeOne\SpatialTypes\Types\Dimension3z\Geography\MultiPoint([[1, 2, 3], [4, 5, 6]], new SpatialReference(4326, 'EPSG'));
        $writer = new Writer(new GeoJsonStrategy());

        static::assertJsonStringEqualsJsonString('{"type":"MultiPoint","coordinates":[[1,2,3],[4,5,6]]}', $writer->convert($multiPoint));
        static::assertJsonStringEqualsJsonString('{"type":"MultiPoint","coordinates":[[1,2,3],[4,5,6]]}', (new GeoJsonStrategy())->executeStrategy($multiPoint));
    }

    /** Preserve coordinates and their order without reference metadata. */
    public function testEmptyXy(): void
    {
        $multiPoint = new MultiPoint([]);
        $writer = new Writer(new GeoJsonStrategy());

        static::assertJsonStringEqualsJsonString('{"type":"MultiPoint","coordinates":[]}', $writer->convert($multiPoint));
        static::assertJsonStringEqualsJsonString('{"type":"MultiPoint","coordinates":[]}', (new GeoJsonStrategy())->executeStrategy($multiPoint));
    }

    /** Preserve coordinates and their order without reference metadata. */
    public function testEmptyXyz(): void
    {
        $multiPoint = new \LongitudeOne\SpatialTypes\Types\Dimension3z\Geography\MultiPoint([]);
        $writer = new Writer(new GeoJsonStrategy());

        static::assertJsonStringEqualsJsonString('{"type":"MultiPoint","coordinates":[]}', $writer->convert($multiPoint));
        static::assertJsonStringEqualsJsonString('{"type":"MultiPoint","coordinates":[]}', (new GeoJsonStrategy())->executeStrategy($multiPoint));
    }

    /** Preserve coordinates and their order without reference metadata. */
    public function testOtherSrid(): void
    {
        $multiPoint = new MultiPoint([[1, 2], [3, 4]], 3857);
        $writer = new Writer(new GeoJsonStrategy());

        static::assertJsonStringEqualsJsonString('{"type":"MultiPoint","coordinates":[[1,2],[3,4]]}', $writer->convert($multiPoint));
        static::assertJsonStringEqualsJsonString('{"type":"MultiPoint","coordinates":[[1,2],[3,4]]}', (new GeoJsonStrategy())->executeStrategy($multiPoint));
    }

    /** Reject an incompatible concrete input without modifying it. */
    public function testRejectEmptyXym(): void
    {
        $multiPoint = new \LongitudeOne\SpatialTypes\Types\Dimension3m\Geography\MultiPoint([]);
        $this->expectException(UnsupportedDimensionException::class);
        (new Writer(new GeoJsonStrategy()))->convert($multiPoint);
    }

    /** Reject an incompatible concrete input without modifying it. */
    public function testRejectEmptyXyzm(): void
    {
        $multiPoint = new \LongitudeOne\SpatialTypes\Types\Dimension4zm\Geography\MultiPoint([]);
        $this->expectException(UnsupportedDimensionException::class);
        (new Writer(new GeoJsonStrategy()))->convert($multiPoint);
    }

    /** Reject an incompatible concrete input without modifying it. */
    public function testRejectXym(): void
    {
        $multiPoint = new \LongitudeOne\SpatialTypes\Types\Dimension3m\Geography\MultiPoint([[1, 2, 3], [4, 5, 6]]);
        $this->expectException(UnsupportedDimensionException::class);
        (new Writer(new GeoJsonStrategy()))->convert($multiPoint);
    }

    /** Reject an incompatible concrete input without modifying it. */
    public function testRejectXyzm(): void
    {
        $multiPoint = new \LongitudeOne\SpatialTypes\Types\Dimension4zm\Geography\MultiPoint([[1, 2, 3, 4], [5, 6, 7, 8]]);
        $this->expectException(UnsupportedDimensionException::class);
        (new Writer(new GeoJsonStrategy()))->convert($multiPoint);
    }

    /** Preserve coordinates and their order without reference metadata. */
    public function testRepeatedPositions(): void
    {
        $multiPoint = new MultiPoint([[3.5, 4.25], [1, 2], [3.5, 4.25]]);
        $writer = new Writer(new GeoJsonStrategy());

        static::assertJsonStringEqualsJsonString('{"type":"MultiPoint","coordinates":[[3.5,4.25],[1,2],[3.5,4.25]]}', $writer->convert($multiPoint));
        static::assertJsonStringEqualsJsonString('{"type":"MultiPoint","coordinates":[[3.5,4.25],[1,2],[3.5,4.25]]}', (new GeoJsonStrategy())->executeStrategy($multiPoint));
    }

    /** Preserve a singleton as a MultiPoint. */
    public function testSingletonXy(): void
    {
        $multiPoint = new MultiPoint([[1, 2]]);
        static::assertJsonStringEqualsJsonString('{"type":"MultiPoint","coordinates":[[1,2]]}', (new Writer(new GeoJsonStrategy()))->convert($multiPoint));
    }

    /** Preserve a singleton as a MultiPoint. */
    public function testSingletonXyz(): void
    {
        $multiPoint = new \LongitudeOne\SpatialTypes\Types\Dimension3z\Geography\MultiPoint([[1, 2, 3]]);
        static::assertJsonStringEqualsJsonString('{"type":"MultiPoint","coordinates":[[1,2,3]]}', (new Writer(new GeoJsonStrategy()))->convert($multiPoint));
    }

    /** Preserve coordinates and their order without reference metadata. */
    public function testUnknownReference(): void
    {
        $multiPoint = new MultiPoint([[1, 2], [3, 4]], new SpatialReference(999999, 'CUSTOM'));
        $writer = new Writer(new GeoJsonStrategy());

        static::assertJsonStringEqualsJsonString('{"type":"MultiPoint","coordinates":[[1,2],[3,4]]}', $writer->convert($multiPoint));
        static::assertJsonStringEqualsJsonString('{"type":"MultiPoint","coordinates":[[1,2],[3,4]]}', (new GeoJsonStrategy())->executeStrategy($multiPoint));
    }

    /** Preserve coordinates and their order without reference metadata. */
    public function testXy(): void
    {
        $multiPoint = new MultiPoint([[1, 2], [3, 4]]);
        $writer = new Writer(new GeoJsonStrategy());

        static::assertJsonStringEqualsJsonString('{"type":"MultiPoint","coordinates":[[1,2],[3,4]]}', $writer->convert($multiPoint));
        static::assertJsonStringEqualsJsonString('{"type":"MultiPoint","coordinates":[[1,2],[3,4]]}', (new GeoJsonStrategy())->executeStrategy($multiPoint));
    }

    /** Preserve coordinates and their order without reference metadata. */
    public function testXyz(): void
    {
        $multiPoint = new \LongitudeOne\SpatialTypes\Types\Dimension3z\Geography\MultiPoint([[1, 2, 3], [4, 5, 6]]);
        $writer = new Writer(new GeoJsonStrategy());

        static::assertJsonStringEqualsJsonString('{"type":"MultiPoint","coordinates":[[1,2,3],[4,5,6]]}', $writer->convert($multiPoint));
        static::assertJsonStringEqualsJsonString('{"type":"MultiPoint","coordinates":[[1,2,3],[4,5,6]]}', (new GeoJsonStrategy())->executeStrategy($multiPoint));
    }
}
