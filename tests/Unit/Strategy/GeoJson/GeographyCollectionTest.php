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
use LongitudeOne\SpatialEncoder\Exception\UnsupportedSpatialTypeException;
use LongitudeOne\SpatialEncoder\Strategy\GeoJson\GeometryEncoder;
use LongitudeOne\SpatialEncoder\Strategy\GeoJsonStrategy;
use LongitudeOne\SpatialTypes\Reference\SpatialReference;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geography\GeographyCollection;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geography\LineString;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geography\MultiLineString;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geography\MultiPoint;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geography\MultiPolygon;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geography\Point;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geography\Polygon;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geography\Triangle;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Explicit real Geography collection composition examples.
 *
 * @internal
 */
#[CoversClass(GeoJsonStrategy::class)]
#[CoversClass(GeometryEncoder::class)]
#[CoversClass(Encoder::class)]
class GeographyCollectionTest extends TestCase
{
    /** Preserve the complete collection structure and values. */
    public function testAllMemberTypes(): void
    {
        $collection = new GeographyCollection(0, [new Point(1, 2), new LineString([[3, 4], [5, 6]]), new MultiPoint([[7, 8], [9, 10]]), new MultiLineString([[[11, 12], [13, 14]]]), new Polygon([[[0, 0], [2, 0], [0, 2], [0, 0]]]), new MultiPolygon([[[[3, 0], [5, 0], [3, 2], [3, 0]]]])]);

        $encoded = (new Encoder(new GeoJsonStrategy()))->encode($collection);
        static::assertIsString($encoded);
        static::assertJsonStringEqualsJsonString('{"type":"GeometryCollection","geometries":[{"type":"Point","coordinates":[1,2]},{"type":"LineString","coordinates":[[3,4],[5,6]]},{"type":"MultiPoint","coordinates":[[7,8],[9,10]]},{"type":"MultiLineString","coordinates":[[[11,12],[13,14]]]},{"type":"Polygon","coordinates":[[[0,0],[2,0],[0,2],[0,0]]]},{"type":"MultiPolygon","coordinates":[[[[3,0],[5,0],[3,2],[3,0]]]]}]}', $encoded);
        static::assertJsonStringEqualsJsonString('{"type":"GeometryCollection","geometries":[{"type":"Point","coordinates":[1,2]},{"type":"LineString","coordinates":[[3,4],[5,6]]},{"type":"MultiPoint","coordinates":[[7,8],[9,10]]},{"type":"MultiLineString","coordinates":[[[11,12],[13,14]]]},{"type":"Polygon","coordinates":[[[0,0],[2,0],[0,2],[0,0]]]},{"type":"MultiPolygon","coordinates":[[[[3,0],[5,0],[3,2],[3,0]]]]}]}', (new GeoJsonStrategy())->encode($collection));
    }

    /** Preserve the complete collection structure and values. */
    public function testAntimeridian(): void
    {
        $collection = new GeographyCollection(0, [new LineString([[170, 45], [-170, 45]])]);

        $encoded = (new Encoder(new GeoJsonStrategy()))->encode($collection);
        static::assertIsString($encoded);
        static::assertJsonStringEqualsJsonString('{"type":"GeometryCollection","geometries":[{"type":"LineString","coordinates":[[170,45],[-170,45]]}]}', $encoded);
        static::assertJsonStringEqualsJsonString('{"type":"GeometryCollection","geometries":[{"type":"LineString","coordinates":[[170,45],[-170,45]]}]}', (new GeoJsonStrategy())->encode($collection));
    }

    /** Preserve the complete collection structure and values. */
    public function testEmptyXy(): void
    {
        $collection = new GeographyCollection(0, []);

        $encoded = (new Encoder(new GeoJsonStrategy()))->encode($collection);
        static::assertIsString($encoded);
        static::assertJsonStringEqualsJsonString('{"type":"GeometryCollection","geometries":[]}', $encoded);
        static::assertJsonStringEqualsJsonString('{"type":"GeometryCollection","geometries":[]}', (new GeoJsonStrategy())->encode($collection));
    }

    /** Preserve the complete collection structure and values. */
    public function testEmptyXyz(): void
    {
        $collection = new \LongitudeOne\SpatialTypes\Types\Dimension3z\Geography\GeographyCollection(0, []);

        $encoded = (new Encoder(new GeoJsonStrategy()))->encode($collection);
        static::assertIsString($encoded);
        static::assertJsonStringEqualsJsonString('{"type":"GeometryCollection","geometries":[]}', $encoded);
        static::assertJsonStringEqualsJsonString('{"type":"GeometryCollection","geometries":[]}', (new GeoJsonStrategy())->encode($collection));
    }

    /** Preserve the complete collection structure and values. */
    public function testHomogeneous(): void
    {
        $collection = new GeographyCollection(0, [new Point(3, 4), new Point(1, 2), new Point(3, 4)]);

        $encoded = (new Encoder(new GeoJsonStrategy()))->encode($collection);
        static::assertIsString($encoded);
        static::assertJsonStringEqualsJsonString('{"type":"GeometryCollection","geometries":[{"type":"Point","coordinates":[3,4]},{"type":"Point","coordinates":[1,2]},{"type":"Point","coordinates":[3,4]}]}', $encoded);
        static::assertJsonStringEqualsJsonString('{"type":"GeometryCollection","geometries":[{"type":"Point","coordinates":[3,4]},{"type":"Point","coordinates":[1,2]},{"type":"Point","coordinates":[3,4]}]}', (new GeoJsonStrategy())->encode($collection));
    }

    /** Preserve the complete collection structure and values. */
    public function testNestedEmpty(): void
    {
        $collection = new GeographyCollection(0, [new GeographyCollection(0, [new Point()]), new GeographyCollection(0, [])]);

        $encoded = (new Encoder(new GeoJsonStrategy()))->encode($collection);
        static::assertIsString($encoded);
        static::assertJsonStringEqualsJsonString('{"type":"GeometryCollection","geometries":[{"type":"GeometryCollection","geometries":[{"type":"Point","coordinates":[]}]},{"type":"GeometryCollection","geometries":[]}]}', $encoded);
        static::assertJsonStringEqualsJsonString('{"type":"GeometryCollection","geometries":[{"type":"GeometryCollection","geometries":[{"type":"Point","coordinates":[]}]},{"type":"GeometryCollection","geometries":[]}]}', (new GeoJsonStrategy())->encode($collection));
    }

    /** Propagate failure without returning a partial collection. */
    public function testRejectClockwisePolygon(): void
    {
        $collection = new GeographyCollection(0, [new Point(1, 2), new Polygon([[[0, 0], [0, 2], [2, 0], [0, 0]]])]);

        $this->expectException(UnsupportedGeometryStructureException::class);
        (new Encoder(new GeoJsonStrategy()))->encode($collection);
    }

    /** Propagate failure without returning a partial collection. */
    public function testRejectDimension3m(): void
    {
        $collection = new \LongitudeOne\SpatialTypes\Types\Dimension3m\Geography\GeographyCollection(0, [new \LongitudeOne\SpatialTypes\Types\Dimension3m\Geography\Point(1, 2, 3)]);

        $this->expectException(UnsupportedDimensionException::class);
        (new Encoder(new GeoJsonStrategy()))->encode($collection);
    }

    /** Propagate failure without returning a partial collection. */
    public function testRejectDimension4zm(): void
    {
        $collection = new \LongitudeOne\SpatialTypes\Types\Dimension4zm\Geography\GeographyCollection(0, [new \LongitudeOne\SpatialTypes\Types\Dimension4zm\Geography\Point(1, 2, 3, 4)]);

        $this->expectException(UnsupportedDimensionException::class);
        (new Encoder(new GeoJsonStrategy()))->encode($collection);
    }

    /** Propagate failure without returning a partial collection. */
    public function testRejectEmptyDimension3m(): void
    {
        $collection = new \LongitudeOne\SpatialTypes\Types\Dimension3m\Geography\GeographyCollection(0, []);

        $this->expectException(UnsupportedDimensionException::class);
        (new Encoder(new GeoJsonStrategy()))->encode($collection);
    }

    /** Propagate failure without returning a partial collection. */
    public function testRejectEmptyDimension4zm(): void
    {
        $collection = new \LongitudeOne\SpatialTypes\Types\Dimension4zm\Geography\GeographyCollection(0, []);

        $this->expectException(UnsupportedDimensionException::class);
        (new Encoder(new GeoJsonStrategy()))->encode($collection);
    }

    /** Propagate failure without returning a partial collection. */
    public function testRejectEmptyMultiPointMember(): void
    {
        $collection = new GeographyCollection(0, [new Point(1, 2), new MultiPoint([new Point()])]);

        $this->expectException(UnsupportedGeometryStructureException::class);
        (new Encoder(new GeoJsonStrategy()))->encode($collection);
    }

    /** Propagate failure without returning a partial collection. */
    public function testRejectNestedSingletonLine(): void
    {
        $collection = new GeographyCollection(0, [new Point(1, 2), new GeographyCollection(0, [new LineString([[3, 4]])])]);

        $this->expectException(UnsupportedGeometryStructureException::class);
        (new Encoder(new GeoJsonStrategy()))->encode($collection);
    }

    /** Propagate failure without returning a partial collection. */
    public function testRejectNestedTriangle(): void
    {
        $collection = new GeographyCollection(0, [new Point(1, 2), new GeographyCollection(0, [new Triangle([[[0, 0], [2, 0], [0, 2], [0, 0]]])])]);

        $this->expectException(UnsupportedSpatialTypeException::class);
        (new Encoder(new GeoJsonStrategy()))->encode($collection);
    }

    /** Preserve the complete collection structure and values. */
    public function testXy(): void
    {
        $collection = new GeographyCollection(4326, [new Point(1, 2, 4326), new LineString([[3, 4], [5, 6]], 4326)]);

        $encoded = (new Encoder(new GeoJsonStrategy()))->encode($collection);
        static::assertIsString($encoded);
        static::assertJsonStringEqualsJsonString('{"type":"GeometryCollection","geometries":[{"type":"Point","coordinates":[1,2]},{"type":"LineString","coordinates":[[3,4],[5,6]]}]}', $encoded);
        static::assertJsonStringEqualsJsonString('{"type":"GeometryCollection","geometries":[{"type":"Point","coordinates":[1,2]},{"type":"LineString","coordinates":[[3,4],[5,6]]}]}', (new GeoJsonStrategy())->encode($collection));
    }

    /** Preserve the complete collection structure and values. */
    public function testXyzSingleton(): void
    {
        $collection = new \LongitudeOne\SpatialTypes\Types\Dimension3z\Geography\GeographyCollection(new SpatialReference(999999, 'CUSTOM'), [new \LongitudeOne\SpatialTypes\Types\Dimension3z\Geography\Point(1, 2, 3, new SpatialReference(999999, 'CUSTOM'))]);

        $encoded = (new Encoder(new GeoJsonStrategy()))->encode($collection);
        static::assertIsString($encoded);
        static::assertJsonStringEqualsJsonString('{"type":"GeometryCollection","geometries":[{"type":"Point","coordinates":[1,2,3]}]}', $encoded);
        static::assertJsonStringEqualsJsonString('{"type":"GeometryCollection","geometries":[{"type":"Point","coordinates":[1,2,3]}]}', (new GeoJsonStrategy())->encode($collection));
    }
}
