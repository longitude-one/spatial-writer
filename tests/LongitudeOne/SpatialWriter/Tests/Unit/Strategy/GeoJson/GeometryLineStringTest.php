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
use LongitudeOne\SpatialTypes\Types\Dimension2\Geometry\LineString;
use LongitudeOne\SpatialWriter\Exception\UnsupportedDimensionException;
use LongitudeOne\SpatialWriter\Exception\UnsupportedGeometryStructureException;
use LongitudeOne\SpatialWriter\Strategy\GeoJson\GeometryEncoder;
use LongitudeOne\SpatialWriter\Strategy\GeoJsonStrategy;
use LongitudeOne\SpatialWriter\Writer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Explicit GeoJSON LineString examples using the concrete model.
 *
 * @internal
 */
#[CoversClass(GeoJsonStrategy::class)]
#[CoversClass(GeometryEncoder::class)]
#[CoversClass(Writer::class)]
class GeometryLineStringTest extends TestCase
{
    /** Preserve coordinates and their order without reference metadata. */
    public function testAdditionalPositions(): void
    {
        $line = new LineString([[3.5, 4.25], [1, 2], [-7, 8]]);
        $writer = new Writer(new GeoJsonStrategy());

        static::assertJsonStringEqualsJsonString('{"type":"LineString","coordinates":[[3.5,4.25],[1,2],[-7,8]]}', $writer->convert($line));
        static::assertJsonStringEqualsJsonString('{"type":"LineString","coordinates":[[3.5,4.25],[1,2],[-7,8]]}', (new GeoJsonStrategy())->executeStrategy($line));
    }

    /** Preserve coordinates and their order without reference metadata. */
    public function testAdditionalXyzPositions(): void
    {
        $line = new \LongitudeOne\SpatialTypes\Types\Dimension3z\Geometry\LineString([[3, 4, 9.5], [1, 2, -2], [-7, 8, 0]]);
        $writer = new Writer(new GeoJsonStrategy());

        static::assertJsonStringEqualsJsonString('{"type":"LineString","coordinates":[[3,4,9.5],[1,2,-2],[-7,8,0]]}', $writer->convert($line));
        static::assertJsonStringEqualsJsonString('{"type":"LineString","coordinates":[[3,4,9.5],[1,2,-2],[-7,8,0]]}', (new GeoJsonStrategy())->executeStrategy($line));
    }

    /** Preserve coordinates and their order without reference metadata. */
    public function testAntimeridian(): void
    {
        $line = new LineString([[170, 45], [-170, 45]], 4326);
        $writer = new Writer(new GeoJsonStrategy());

        static::assertJsonStringEqualsJsonString('{"type":"LineString","coordinates":[[170,45],[-170,45]]}', $writer->convert($line));
        static::assertJsonStringEqualsJsonString('{"type":"LineString","coordinates":[[170,45],[-170,45]]}', (new GeoJsonStrategy())->executeStrategy($line));
    }

    /** Preserve coordinates and their order without reference metadata. */
    public function testDeclaredEpsg(): void
    {
        $line = new \LongitudeOne\SpatialTypes\Types\Dimension3z\Geometry\LineString([[1, 2, 3], [4, 5, 6]], new SpatialReference(4326, 'EPSG'));
        $writer = new Writer(new GeoJsonStrategy());

        static::assertJsonStringEqualsJsonString('{"type":"LineString","coordinates":[[1,2,3],[4,5,6]]}', $writer->convert($line));
        static::assertJsonStringEqualsJsonString('{"type":"LineString","coordinates":[[1,2,3],[4,5,6]]}', (new GeoJsonStrategy())->executeStrategy($line));
    }

    /** Preserve coordinates and their order without reference metadata. */
    public function testEmptyXy(): void
    {
        $line = new LineString([]);
        $writer = new Writer(new GeoJsonStrategy());

        static::assertJsonStringEqualsJsonString('{"type":"LineString","coordinates":[]}', $writer->convert($line));
        static::assertJsonStringEqualsJsonString('{"type":"LineString","coordinates":[]}', (new GeoJsonStrategy())->executeStrategy($line));
    }

    /** Preserve coordinates and their order without reference metadata. */
    public function testEmptyXyz(): void
    {
        $line = new \LongitudeOne\SpatialTypes\Types\Dimension3z\Geometry\LineString([]);
        $writer = new Writer(new GeoJsonStrategy());

        static::assertJsonStringEqualsJsonString('{"type":"LineString","coordinates":[]}', $writer->convert($line));
        static::assertJsonStringEqualsJsonString('{"type":"LineString","coordinates":[]}', (new GeoJsonStrategy())->executeStrategy($line));
    }

    /** Preserve coordinates and their order without reference metadata. */
    public function testOtherSrid(): void
    {
        $line = new LineString([[1, 2], [3, 4]], 3857);
        $writer = new Writer(new GeoJsonStrategy());

        static::assertJsonStringEqualsJsonString('{"type":"LineString","coordinates":[[1,2],[3,4]]}', $writer->convert($line));
        static::assertJsonStringEqualsJsonString('{"type":"LineString","coordinates":[[1,2],[3,4]]}', (new GeoJsonStrategy())->executeStrategy($line));
    }

    /** Reject an incompatible concrete input without modifying it. */
    public function testRejectEmptyXym(): void
    {
        $line = new \LongitudeOne\SpatialTypes\Types\Dimension3m\Geometry\LineString([]);
        $this->expectException(UnsupportedDimensionException::class);
        (new Writer(new GeoJsonStrategy()))->convert($line);
    }

    /** Reject an incompatible concrete input without modifying it. */
    public function testRejectEmptyXyzm(): void
    {
        $line = new \LongitudeOne\SpatialTypes\Types\Dimension4zm\Geometry\LineString([]);
        $this->expectException(UnsupportedDimensionException::class);
        (new Writer(new GeoJsonStrategy()))->convert($line);
    }

    /** Reject an incompatible concrete input without modifying it. */
    public function testRejectSingletonXy(): void
    {
        $line = new LineString([[1, 2]]);
        $this->expectException(UnsupportedGeometryStructureException::class);
        (new Writer(new GeoJsonStrategy()))->convert($line);
    }

    /** Reject an incompatible concrete input without modifying it. */
    public function testRejectSingletonXyz(): void
    {
        $line = new \LongitudeOne\SpatialTypes\Types\Dimension3z\Geometry\LineString([[1, 2, 3]]);
        $this->expectException(UnsupportedGeometryStructureException::class);
        (new Writer(new GeoJsonStrategy()))->convert($line);
    }

    /** Reject an incompatible concrete input without modifying it. */
    public function testRejectXym(): void
    {
        $line = new \LongitudeOne\SpatialTypes\Types\Dimension3m\Geometry\LineString([[1, 2, 3], [4, 5, 6]]);
        $this->expectException(UnsupportedDimensionException::class);
        (new Writer(new GeoJsonStrategy()))->convert($line);
    }

    /** Reject an incompatible concrete input without modifying it. */
    public function testRejectXyzm(): void
    {
        $line = new \LongitudeOne\SpatialTypes\Types\Dimension4zm\Geometry\LineString([[1, 2, 3, 4], [5, 6, 7, 8]]);
        $this->expectException(UnsupportedDimensionException::class);
        (new Writer(new GeoJsonStrategy()))->convert($line);
    }

    /** Preserve coordinates and their order without reference metadata. */
    public function testUnknownReference(): void
    {
        $line = new LineString([[1, 2], [3, 4]], new SpatialReference(999999, 'CUSTOM'));
        $writer = new Writer(new GeoJsonStrategy());

        static::assertJsonStringEqualsJsonString('{"type":"LineString","coordinates":[[1,2],[3,4]]}', $writer->convert($line));
        static::assertJsonStringEqualsJsonString('{"type":"LineString","coordinates":[[1,2],[3,4]]}', (new GeoJsonStrategy())->executeStrategy($line));
    }

    /** Preserve coordinates and their order without reference metadata. */
    public function testXy(): void
    {
        $line = new LineString([[1, 2], [3, 4]]);
        $writer = new Writer(new GeoJsonStrategy());

        static::assertJsonStringEqualsJsonString('{"type":"LineString","coordinates":[[1,2],[3,4]]}', $writer->convert($line));
        static::assertJsonStringEqualsJsonString('{"type":"LineString","coordinates":[[1,2],[3,4]]}', (new GeoJsonStrategy())->executeStrategy($line));
    }

    /** Preserve coordinates and their order without reference metadata. */
    public function testXyz(): void
    {
        $line = new \LongitudeOne\SpatialTypes\Types\Dimension3z\Geometry\LineString([[1, 2, 3], [4, 5, 6]]);
        $writer = new Writer(new GeoJsonStrategy());

        static::assertJsonStringEqualsJsonString('{"type":"LineString","coordinates":[[1,2,3],[4,5,6]]}', $writer->convert($line));
        static::assertJsonStringEqualsJsonString('{"type":"LineString","coordinates":[[1,2,3],[4,5,6]]}', (new GeoJsonStrategy())->executeStrategy($line));
    }
}
