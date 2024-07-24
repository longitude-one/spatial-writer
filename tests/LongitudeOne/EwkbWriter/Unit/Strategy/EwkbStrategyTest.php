<?php
/**
 * This file is part of the ewkb-writer project.
 *
 * PHP 8.1 | 8.2 | 8.3
 *
 * Copyright Alexandre Tranchant <alexandre.tranchant@gmail.com> 2024
 * Copyright Longitude One 2024
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

declare(strict_types=1);

namespace LongitudeOne\EwkbWriter\Tests\Unit\Strategy;

use LongitudeOne\EwkbWriter\Exception\UnsupportedSpatialInterfaceException;
use LongitudeOne\EwkbWriter\Exception\UnsupportedSpatialTypeException;
use LongitudeOne\EwkbWriter\Strategy\EwkbBinaryStrategy;
use LongitudeOne\Spatial\Exception\InvalidValueException;
use LongitudeOne\Spatial\PHP\Types\Geometry\LineString;
use LongitudeOne\Spatial\PHP\Types\Geometry\MultiLineString;
use LongitudeOne\Spatial\PHP\Types\Geometry\MultiPoint;
use LongitudeOne\Spatial\PHP\Types\Geometry\MultiPolygon;
use LongitudeOne\Spatial\PHP\Types\Geometry\Point as GeographicPoint;
use LongitudeOne\Spatial\PHP\Types\Geometry\Point as GeometricPoint;
use LongitudeOne\Spatial\PHP\Types\Geometry\Polygon;
use LongitudeOne\Spatial\PHP\Types\LineStringInterface;
use LongitudeOne\Spatial\PHP\Types\MultiLineStringInterface;
use LongitudeOne\Spatial\PHP\Types\MultiPointInterface;
use LongitudeOne\Spatial\PHP\Types\MultiPolygonInterface;
use LongitudeOne\Spatial\PHP\Types\PointInterface;
use LongitudeOne\Spatial\PHP\Types\PolygonInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \LongitudeOne\EwkbWriter\Strategy\EwkbBinaryStrategy
 */
class EwkbStrategyTest extends TestCase
{
    private const SRID_XY = 7035;
    private const SRID_YX = 4326;

    /**
     * WkbBinaryStrategy instance.
     */
    private EwkbBinaryStrategy $strategy;

    /**
     * Set up the test.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->strategy = new EwkbBinaryStrategy();
    }

    /**
     * Tear down the test.
     */
    protected function tearDown(): void
    {
        unset($this->strategy);
        parent::tearDown();
    }

    /**
     * Data provider for line-strings.
     *
     * @return \Generator<string, array{0: LineStringInterface, 1: string}, null, void>
     *
     * @throws InvalidValueException This should not happen, as the data provider only provides valid line-strings
     */
    public static function lineStringProvider(): \Generator
    {
        // Let's try the simplest line-string
        $origin = new GeometricPoint(0, 0);
        $summit = new GeometricPoint(0, 1);
        $destination = new GeometricPoint(1, 1);

        yield 'LINESTRING(0 0, 1 1)' => [
            new LineString([$origin, $destination]),
            '01020000000200000000000000000000000000000000000000000000000000F03F000000000000F03F',
        ];

        // Let's try a line-string with more points
        yield 'LINESTRING(0 0, 0 1, 1 1)' => [
            new LineString([$origin, $summit, $destination]),
            '010200000003000000000000000000000000000000000000000000000000000000000000000000F03F000000000000F03F000000000000F03F',
        ];

        // Let's try a line-string with a YX SRID
        yield 'SRID=4326;LINESTRING(0 0, 1 1)' => [
            (new LineString([$origin, $destination]))->setSrid(self::SRID_YX),
            '0102000020E61000000200000000000000000000000000000000000000000000000000F03F000000000000F03F',
        ];

        // Let's try a line-string with a XY SRID
        yield 'SRID=7035;LINESTRING(0 0, 1 1)' => [
            (new LineString([$origin, $destination]))->setSrid(self::SRID_XY),
            '01020000207B1B00000200000000000000000000000000000000000000000000000000F03F000000000000F03F',
        ];
    }

    /**
     * Data provider for multi-line-strings.
     *
     * @return \Generator<string, array{0: MultiLineStringInterface, 1: string}, null, void>
     *
     * @throws InvalidValueException This should not happen, as the data provider only provides valid multi-line-strings
     */
    public static function multiLineStringProvider(): \Generator
    {
        // Let's try the simplest multi-line-string
        $origin = new GeometricPoint(0, 0);
        $destination = new GeometricPoint(1, 1);

        $anotherOrigin = new GeometricPoint(2, 2);
        $intSummit = new GeometricPoint(2, 1);
        $anotherDestination = new GeometricPoint(3, 3);

        // Let's try a multi-line-string with a single line-string
        yield 'MULTILINESTRING((0 0, 1 1))' => [
            new MultiLineString([new LineString([$origin, $destination])]),
            '01050000000100000001020000000200000000000000000000000000000000000000000000000000F03F000000000000F03F',
        ];

        // Let's try a multi-line-string with more line-strings
        yield 'MULTILINESTRING((0 0, 1 1), (2 2, 2 1, 3 3))' => [
            new MultiLineString([new LineString([$origin, $destination]), new LineString([$anotherOrigin, $intSummit, $anotherDestination])]),
            '01050000000200000001020000000200000000000000000000000000000000000000000000000000F03F000000000000F03F010200000003000000000000000000004000000000000000400000000000000040000000000000F03F00000000000008400000000000000840',
        ];
    }

    /**
     * Data provider for multi-points.
     *
     * @return \Generator<string, array{0: MultiPointInterface, 1: string}, null, void>
     *
     * @throws InvalidValueException This should not happen, as the data provider only provides valid multi-points
     */
    public static function multiPointProvider(): \Generator
    {
        // Let's try the simplest multi-point
        $origin = new GeometricPoint(0, 0);
        $summit = new GeometricPoint(0, 1);
        $destination = new GeometricPoint(1, 1);

        yield 'MULTIPOINT(0 0, 0 1, 1 1)' => [
            new MultiPoint([$origin, $summit, $destination]),
            '01040000000300000001010000000000000000000000000000000000000001010000000000000000000000000000000000F03F0101000000000000000000F03F000000000000F03F',
        ];
    }

    /**
     * Data provider for multi-polygons.
     *
     * @return \Generator<string, array{0: MultiPolygonInterface, 1: string}, null, void>
     *
     * @throws InvalidValueException This should not happen, as the data provider only provides valid multi-polygons
     */
    public static function multiPolygonProvider(): \Generator
    {
        // Let's try the simplest multi-polygon
        $left = new GeometricPoint(-1, 0);
        $right = new GeometricPoint(1, 0);
        $top = new GeometricPoint(0, 1);
        $bottom = new GeometricPoint(0, -1);

        // Let's try a multi-polygon with a single polygon
        yield 'MULTIPOLYGON(((-1 0, 0 -1, 1 0, 0 1, -1 0)))' => [
            new MultiPolygon([new Polygon([[$left, $bottom, $right, $top, $left]])]),
            '01060000000100000001030000000100000005000000000000000000F0BF00000000000000000000000000000000000000000000F0BF000000000000F03F00000000000000000000000000000000000000000000F03F000000000000F0BF0000000000000000',
        ];

        // Let's try a multi-polygon with more polygons
        $moreLeft = new GeometricPoint(-2, 0);
        $moreRight = new GeometricPoint(2, 0);
        $moreTop = new GeometricPoint(0, 2);
        $moreBottom = new GeometricPoint(0, -2);

        yield 'MULTIPOLYGON(((-1 0, 0 -1, 1 0, 0 1, -1 0)), ((-2 0, 0 -2, 2 0, 0 2, -2 0)))' => [
            new MultiPolygon([new Polygon([[$left, $bottom, $right, $top, $left]]), new Polygon([[$moreLeft, $moreBottom, $moreRight, $moreTop, $moreLeft]])]),
            '01060000000200000001030000000100000005000000000000000000F0BF00000000000000000000000000000000000000000000F0BF000000000000F03F00000000000000000000000000000000000000000000F03F000000000000F0BF00000000000000000103000000010000000500000000000000000000C00000000000000000000000000000000000000000000000C0000000000000004000000000000000000000000000000000000000000000004000000000000000C00000000000000000',
        ];
    }

    /**
     * Data provider for points.
     *
     * @return \Generator<string, array{0: PointInterface, 1: string}, null, void>
     *
     * @throws InvalidValueException This should not happen, as the data provider only provides valid points
     */
    public static function pointProvider(): \Generator
    {
        // Try the simplest point
        yield 'GEOMETRIC POINT(0 0)' => [
            new GeometricPoint(0, 0),
            '010100000000000000000000000000000000000000',
        ];

        // Let's try a geographic point
        yield 'GEOGRAPHIC POINT(0 0)' => [
            new GeographicPoint(0, 0),
            '010100000000000000000000000000000000000000',
        ];

        // Let's add a SRID to the point
        yield 'SRID=4326;GEOMETRIC POINT(0 0)' => [
            (new GeometricPoint(0, 0))->setSrid(self::SRID_YX),
            '0101000020E610000000000000000000000000000000000000',
        ];

        // Let's check a point with X and Y different from 0
        yield 'POINT(-1 1)' => [
            new GeometricPoint(-1, 1),
            '0101000000000000000000F0BF000000000000F03F',
        ];

        // Let's check a point with X and Y in the opposite order
        yield 'POINT(1 -1)' => [
            new GeometricPoint(1, -1),
            '0101000000000000000000F03F000000000000F0BF',
        ];

        // Let's check float values
        yield 'POINT(42.2 2.42)' => [
            new GeometricPoint(42.2, 2.42),
            '01010000009A999999991945405C8FC2F5285C0340',
        ];

        // Let's check that the SRID YX does NOT affect the result
        yield 'SRID=4326;POINT(1 -1)' => [
            (new GeometricPoint(1, -1))->setSrid(self::SRID_YX),
            '0101000020E6100000000000000000F03F000000000000F0BF',
        ];

        // Let's check that the SRID XY does NOT affect the result
        yield 'SRID=7035; POINT(1 -1)' => [
            (new GeometricPoint(1, -1))->setSrid(self::SRID_XY),
            '01010000207B1B0000000000000000F03F000000000000F0BF',
        ];
    }

    /**
     * Data provider for polygons.
     *
     * @return \Generator<string, array{0: PolygonInterface, 1: string}, null, void>
     *
     * @throws InvalidValueException This should not happen, as the data provider only provides valid polygons
     */
    public static function polygonProvider(): \Generator
    {
        // Let's try a simple polygon
        $left = new GeometricPoint(-1, 0);
        $right = new GeometricPoint(1, 0);
        $top = new GeometricPoint(0, 1);
        $bottom = new GeometricPoint(0, -1);

        // Let's check order of points
        yield 'POLYGON((-1 0, -0 -1, 1 0, 0 1, -1 0))' => [
            new Polygon([[$left, $bottom, $right, $top, $left]]),
            '01030000000100000005000000000000000000F0BF00000000000000000000000000000000000000000000F0BF000000000000F03F00000000000000000000000000000000000000000000F03F000000000000F0BF0000000000000000',
        ];

        $moreLeft = new GeometricPoint(-2, 0);
        $moreRight = new GeometricPoint(2, 0);
        $moreTop = new GeometricPoint(0, 2);
        $moreBottom = new GeometricPoint(0, -2);

        // Let's try a polygon with a hole
        yield 'POLYGON((-1 0, -1 -1, 1 0, 1 0, -1 0), (-2 0, -2 -2, 2 0, 2 0, -2 0))' => [
            new Polygon([[$left, $bottom, $right, $top, $left], [$moreLeft, $moreBottom, $moreRight, $moreTop, $moreLeft]]),
            '01030000000200000005000000000000000000F0BF00000000000000000000000000000000000000000000F0BF000000000000F03F00000000000000000000000000000000000000000000F03F000000000000F0BF00000000000000000500000000000000000000C00000000000000000000000000000000000000000000000C0000000000000004000000000000000000000000000000000000000000000004000000000000000C00000000000000000',
        ];
    }

    /**
     * Test the conversion of line-strings into their Well-Known Binary representation.
     *
     * @param LineStringInterface $lineString line-string to test
     * @param string              $expected   expected result in hexadecimal format
     *
     * @throws UnsupportedSpatialInterfaceException Data provider only provides valid line-strings
     * @throws UnsupportedSpatialTypeException      Data provider only provides valid line-strings
     */
    #[DataProvider('lineStringProvider')]
    public function testLineString(LineStringInterface $lineString, string $expected): void
    {
        static::assertSame(mb_strtolower($expected), bin2hex($this->strategy->executeStrategy($lineString)));
    }

    /**
     * Test the conversion of multi-line-strings into their Well-Known Binary representation.
     *
     * @param MultiLineStringInterface $multiLineString multi-line-string to test
     * @param string                   $expected        expected result in hexadecimal format
     *
     * @throws UnsupportedSpatialInterfaceException Data provider only provides valid multi-line-strings
     * @throws UnsupportedSpatialTypeException      Data provider only provides valid multi-line-strings
     */
    #[DataProvider('multiLineStringProvider')]
    public function testMultiLineString(MultiLineStringInterface $multiLineString, string $expected): void
    {
        static::assertSame(mb_strtolower($expected), bin2hex($this->strategy->executeStrategy($multiLineString)));
    }

    /**
     * Test the conversion of multi-points into their Well-Known Binary representation.
     *
     * @param MultiPointInterface $multiPoint multi-point to test
     * @param string              $expected   expected result in hexadecimal format
     *
     * @throws UnsupportedSpatialInterfaceException Data provider only provides valid multi-points
     * @throws UnsupportedSpatialTypeException      Data provider only provides valid multi-points
     */
    #[DataProvider('multiPointProvider')]
    public function testMultiPoint(MultiPointInterface $multiPoint, string $expected): void
    {
        static::assertSame(mb_strtolower($expected), bin2hex($this->strategy->executeStrategy($multiPoint)));
    }

    /**
     * Test the conversion of multi-polygons into their Well-Known Binary representation.
     *
     * @param MultiPolygonInterface $multiPolygon multi-polygon to test
     * @param string                $expected     expected result in hexadecimal format
     *
     * @throws UnsupportedSpatialInterfaceException Data provider only provides valid multi-polygons
     * @throws UnsupportedSpatialTypeException      Data provider only provides valid multi-polygons
     */
    #[DataProvider('multiPolygonProvider')]
    public function testMultiPolygon(MultiPolygonInterface $multiPolygon, string $expected): void
    {
        static::assertSame(mb_strtolower($expected), bin2hex($this->strategy->executeStrategy($multiPolygon)));
    }

    /**
     * Test the conversion of points into their Well-Known Binary representation.
     *
     * @param PointInterface $point    point to test
     * @param string         $expected expected result in hexadecimal format
     *
     * @throws UnsupportedSpatialInterfaceException Data provider only provides valid points
     * @throws UnsupportedSpatialTypeException      Data provider only provides valid points
     */
    #[DataProvider('pointProvider')]
    public function testPoint(PointInterface $point, string $expected): void
    {
        static::assertSame(mb_strtolower($expected), bin2hex($this->strategy->executeStrategy($point)));
    }

    /**
     * Test the conversion of polygons into their Well-Known Binary representation.
     *
     * @param PolygonInterface $polygon  polygon to test
     * @param string           $expected expected result in hexadecimal format
     *
     * @throws UnsupportedSpatialInterfaceException Data provider only provides valid polygons
     * @throws UnsupportedSpatialTypeException      Data provider only provides valid polygons
     */
    #[DataProvider('polygonProvider')]
    public function testPolygon(PolygonInterface $polygon, string $expected): void
    {
        static::assertSame(mb_strtolower($expected), bin2hex($this->strategy->executeStrategy($polygon)));
    }
}
