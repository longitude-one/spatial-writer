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

namespace LongitudeOne\SpatialWriter\Tests\Unit\Strategy;

use LongitudeOne\SpatialTypes\Types\Dimension2\Geography\Point as GeographicPoint;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geometry\GeometryCollection;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geometry\LineString;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geometry\MultiPoint;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geometry\Point;
use LongitudeOne\SpatialTypes\Types\Dimension3m\Geometry\Point as PointM;
use LongitudeOne\SpatialTypes\Types\Dimension3z\Geometry\Point as PointZ;
use LongitudeOne\SpatialTypes\Types\Dimension4zm\Geometry\GeometryCollection as GeometryCollectionZm;
use LongitudeOne\SpatialTypes\Types\Dimension4zm\Geometry\Point as PointZm;
use LongitudeOne\SpatialWriter\Strategy\EwktTextStrategy;
use LongitudeOne\SpatialWriter\Strategy\Wkt\WktTypeEncoder;
use LongitudeOne\SpatialWriter\Strategy\WktTextStrategy;
use LongitudeOne\SpatialWriter\Writer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Explicit examples of conditional SRID prefixes and inherited WKT formatting.
 *
 * @internal
 */
#[CoversClass(EwktTextStrategy::class)]
#[CoversClass(WktTextStrategy::class)]
#[CoversClass(WktTypeEncoder::class)]
#[CoversClass(Writer::class)]
class EwktStrategyTest extends TestCase
{
    /** Preserve plain WKT for collections without a nonzero SRID. */
    public function testCollectionWithoutSrid(): void
    {
        $collection = new GeometryCollection(0, [new Point(1, 2), new GeometryCollection()]);

        static::assertSame(
            'GEOMETRYCOLLECTION (POINT (1 2), GEOMETRYCOLLECTION EMPTY)',
            (new EwktTextStrategy())->executeStrategy($collection)
        );
    }

    /** Keep dimension markers on nested geometries while prefixing only the root. */
    public function testCollectionZm(): void
    {
        $collection = new GeometryCollectionZm(4326, [new PointZm(1, 2, 3, 4, 4326)]);

        static::assertSame(
            'SRID=4326;GEOMETRYCOLLECTION ZM (POINT ZM (1 2 3 4))',
            (new EwktTextStrategy())->executeStrategy($collection)
        );
    }

    /** Prefix an empty collection. */
    public function testEmptyCollection(): void
    {
        $collection = new GeometryCollection(4326);

        static::assertSame('SRID=4326;GEOMETRYCOLLECTION EMPTY', (new EwktTextStrategy())->executeStrategy($collection));
    }

    /** An empty geometry with SRID zero has no prefix. */
    public function testEmptyPointWithoutSrid(): void
    {
        $point = new Point();

        static::assertSame('POINT EMPTY', (new EwktTextStrategy())->executeStrategy($point));
    }

    /** An empty geometry still carries its nonzero SRID. */
    public function testEmptyPointWithSrid(): void
    {
        $point = new Point(srid: 4326);

        static::assertSame('SRID=4326;POINT EMPTY', (new EwktTextStrategy())->executeStrategy($point));
    }

    /** Preserve the dimension of an empty point. */
    public function testEmptyPointZm(): void
    {
        $point = new PointZm(srid: 4326);

        static::assertSame('SRID=4326;POINT ZM EMPTY', (new EwktTextStrategy())->executeStrategy($point));
    }

    /** Preserve geography coordinates and decimal formatting. */
    public function testGeographicPoint(): void
    {
        $point = new GeographicPoint(2.5, -3, 4326);

        static::assertSame('SRID=4326;POINT (2.5 -3)', (new EwktTextStrategy())->executeStrategy($point));
    }

    /** Write the SRID once for a homogeneous collection. */
    public function testMultiPoint(): void
    {
        $points = new MultiPoint([[1, 2], [3, 4]], 4326);

        static::assertSame('SRID=4326;MULTIPOINT ((1 2), (3 4))', (new EwktTextStrategy())->executeStrategy($points));
    }

    /** Nested collection members must never receive their own SRID prefixes. */
    public function testNestedCollection(): void
    {
        $collection = new GeometryCollection(4326, [
            new Point(1, 2, 4326),
            new GeometryCollection(4326, [
                new LineString([[0, 0], [2, 3]], 4326),
                new Point(srid: 4326),
            ]),
            new GeometryCollection(4326),
        ]);

        static::assertSame(
            'SRID=4326;GEOMETRYCOLLECTION (POINT (1 2), GEOMETRYCOLLECTION (LINESTRING (0 0, 2 3), POINT EMPTY), GEOMETRYCOLLECTION EMPTY)',
            (new EwktTextStrategy())->executeStrategy($collection)
        );
    }

    /** Preserve the measure marker and ordinate. */
    public function testPointM(): void
    {
        $point = new PointM(1, 2, 4, 4326);

        static::assertSame('SRID=4326;POINT M (1 2 4)', (new EwktTextStrategy())->executeStrategy($point));
    }

    /** The default zero SRID produces plain WKT. */
    public function testPointWithDefaultSrid(): void
    {
        $point = new Point(1, 2);

        static::assertSame('POINT (1 2)', (new EwktTextStrategy())->executeStrategy($point));
    }

    /** Prefix a point with its nonzero SRID. */
    public function testPointWithSrid(): void
    {
        $point = new Point(1, 2, 4326);

        static::assertSame('SRID=4326;POINT (1 2)', (new EwktTextStrategy())->executeStrategy($point));
    }

    /** An explicit zero SRID also produces plain WKT. */
    public function testPointWithZeroSrid(): void
    {
        $point = new Point(1, 2, 0);

        static::assertSame('POINT (1 2)', (new EwktTextStrategy())->executeStrategy($point));
    }

    /** Preserve the elevation marker and ordinate. */
    public function testPointZ(): void
    {
        $point = new PointZ(1, 2, 3, 4326);

        static::assertSame('SRID=4326;POINT Z (1 2 3)', (new EwktTextStrategy())->executeStrategy($point));
    }

    /** Preserve elevation and measure in their original order. */
    public function testPointZm(): void
    {
        $point = new PointZm(1, 2, 3, 4, 4326);

        static::assertSame('SRID=4326;POINT ZM (1 2 3 4)', (new EwktTextStrategy())->executeStrategy($point));
    }

    /** Reusing a strategy must read the SRID of each new geometry. */
    public function testRepeatedConversions(): void
    {
        $strategy = new EwktTextStrategy();

        static::assertSame('SRID=4326;POINT (1 2)', $strategy->executeStrategy(new Point(1, 2, 4326)));
        static::assertSame('POINT (3 4)', $strategy->executeStrategy(new Point(3, 4)));
        static::assertSame('SRID=7035;POINT (5 6)', $strategy->executeStrategy(new Point(5, 6, 7035)));
    }

    /** The writer can switch between EWKT and plain WKT. */
    public function testWriterIntegration(): void
    {
        $point = new Point(1, 2, 4326);
        $writer = new Writer(new EwktTextStrategy());

        static::assertSame('SRID=4326;POINT (1 2)', $writer->convert($point));
        $writer->setStrategy(new WktTextStrategy());
        static::assertSame('POINT (1 2)', $writer->convert($point));
    }
}
