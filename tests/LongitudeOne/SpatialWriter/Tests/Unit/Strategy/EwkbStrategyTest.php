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

use LongitudeOne\SpatialTypes\Exception\SpatialTypeExceptionInterface;
use LongitudeOne\SpatialTypes\Interfaces\LineStringInterface;
use LongitudeOne\SpatialTypes\Interfaces\MultiLineStringInterface;
use LongitudeOne\SpatialTypes\Interfaces\MultiPointInterface;
use LongitudeOne\SpatialTypes\Interfaces\MultiPolygonInterface;
use LongitudeOne\SpatialTypes\Interfaces\PointInterface;
use LongitudeOne\SpatialTypes\Interfaces\PolygonInterface;
use LongitudeOne\SpatialTypes\Types\Geometry\LineString;
use LongitudeOne\SpatialTypes\Types\Geometry\MultiLineString;
use LongitudeOne\SpatialTypes\Types\Geometry\MultiPoint;
use LongitudeOne\SpatialTypes\Types\Geometry\MultiPolygon;
use LongitudeOne\SpatialTypes\Types\Geometry\Point as GeometricPoint;
use LongitudeOne\SpatialTypes\Types\Geometry\Polygon;
use LongitudeOne\SpatialWriter\Exception\UnsupportedSpatialInterfaceException;
use LongitudeOne\SpatialWriter\Exception\UnsupportedSpatialTypeException;
use LongitudeOne\SpatialWriter\Strategy\EwkbBinaryStrategy;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \LongitudeOne\SpatialWriter\Strategy\EwkbBinaryStrategy
 */
class EwkbStrategyTest extends TestCase
{
    private const LINE_STRING_EXPECTED_WITH_MORE_POINTS = '010200000003000000000000000000000000000000000000000000000000000000000000000000F03F000000000000F03F000000000000F03F';
    private const LINE_STRING_EXPECTED_WITH_SRID_XY = '01020000207B1B00000200000000000000000000000000000000000000000000000000F03F000000000000F03F';
    private const LINE_STRING_EXPECTED_WITH_SRID_YX = '0102000020E61000000200000000000000000000000000000000000000000000000000F03F000000000000F03F';
    private const LINE_STRING_EXPECTED_WITHOUT_SRID = '01020000000200000000000000000000000000000000000000000000000000F03F000000000000F03F';
    private const MULTI_LINE_STRING_EXPECTED_MULTI_LINES = '01050000000200000001020000000200000000000000000000000000000000000000000000000000F03F000000000000F03F010200000003000000000000000000004000000000000000400000000000000040000000000000F03F00000000000008400000000000000840';
    private const MULTI_LINE_STRING_EXPECTED_SINGLE_LINE = '01050000000100000001020000000200000000000000000000000000000000000000000000000000F03F000000000000F03F';
    private const MULTI_POINT_EXPECTED = '01040000000300000001010000000000000000000000000000000000000001010000000000000000000000000000000000F03F0101000000000000000000F03F000000000000F03F';
    private const MULTI_POLYGON_EXPECTED_MULTI_POLYGONS = '01060000000200000001030000000100000005000000000000000000F0BF00000000000000000000000000000000000000000000F0BF000000000000F03F00000000000000000000000000000000000000000000F03F000000000000F0BF00000000000000000103000000010000000500000000000000000000C00000000000000000000000000000000000000000000000C0000000000000004000000000000000000000000000000000000000000000004000000000000000C00000000000000000';
    private const MULTI_POLYGON_EXPECTED_SINGLE_POLYGON = '01060000000100000001030000000100000005000000000000000000F0BF00000000000000000000000000000000000000000000F0BF000000000000F03F00000000000000000000000000000000000000000000F03F000000000000F0BF0000000000000000';
    private const POINT_EXPECTED_DEFAULT = '010100000000000000000000000000000000000000';
    private const POINT_EXPECTED_WITH_FLOATS = '01010000009A999999991945405C8FC2F5285C0340';
    private const POINT_EXPECTED_WITH_INVERTED_COORDINATES = '0101000000000000000000F03F000000000000F0BF';
    private const POINT_EXPECTED_WITH_SRID_XY_NO_EFFECT = '01010000207B1B0000000000000000F03F000000000000F0BF';
    private const POINT_EXPECTED_WITH_SRID_YX = '0101000020E610000000000000000000000000000000000000';
    private const POINT_EXPECTED_WITH_SRID_YX_NO_EFFECT = '0101000020E6100000000000000000F03F000000000000F0BF';
    private const POINT_EXPECTED_WITH_X_Y = '0101000000000000000000F0BF000000000000F03F';
    private const POLYGON_EXPECTED_ORDER_OF_POINTS = '01030000000100000005000000000000000000F0BF00000000000000000000000000000000000000000000F0BF000000000000F03F00000000000000000000000000000000000000000000F03F000000000000F0BF0000000000000000';
    private const POLYGON_EXPECTED_WITH_HOLE = '01030000000200000005000000000000000000F0BF00000000000000000000000000000000000000000000F0BF000000000000F03F00000000000000000000000000000000000000000000F03F000000000000F0BF00000000000000000500000000000000000000C00000000000000000000000000000000000000000000000C0000000000000004000000000000000000000000000000000000000000000004000000000000000C00000000000000000';
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
     * Data provider for line-strings.
     *
     * @return \Generator<string, array{0: LineStringInterface, 1: string}, null, void>
     *
     * @throws SpatialTypeExceptionInterface This should not happen, as the data provider only provides valid line-strings
     */
    public static function lineStringProvider(): \Generator
    {
        // Let's try the simplest line-string
        $origin = new GeometricPoint(0, 0);
        $summit = new GeometricPoint(0, 1);
        $destination = new GeometricPoint(1, 1);

        yield 'LINESTRING(0 0, 1 1)' => [
            new LineString([$origin, $destination]),
            self::LINE_STRING_EXPECTED_WITHOUT_SRID,
        ];

        // Let's try a line-string with more points
        yield 'LINESTRING(0 0, 0 1, 1 1)' => [
            new LineString([$origin, $summit, $destination]),
            self::LINE_STRING_EXPECTED_WITH_MORE_POINTS,
        ];

        // Let's try a line-string with a YX SRID
        yield 'SRID=4326;LINESTRING(0 0, 1 1)' => [
            (new LineString([$origin, $destination]))->setSrid(self::SRID_YX),
            self::LINE_STRING_EXPECTED_WITH_SRID_YX,
        ];

        // Let's try a line-string with a XY SRID
        yield 'SRID=7035;LINESTRING(0 0, 1 1)' => [
            (new LineString([$origin, $destination]))->setSrid(self::SRID_XY),
            self::LINE_STRING_EXPECTED_WITH_SRID_XY,
        ];
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
     * Data provider for multi-line-strings.
     *
     * @return \Generator<string, array{0: MultiLineStringInterface, 1: string}, null, void>
     *
     * @throws SpatialTypeExceptionInterface This should not happen, as the data provider only provides valid multi-line-strings
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
            self::MULTI_LINE_STRING_EXPECTED_SINGLE_LINE,
        ];

        // Let's try a multi-line-string with more line-strings
        yield 'MULTILINESTRING((0 0, 1 1), (2 2, 2 1, 3 3))' => [
            new MultiLineString([new LineString([$origin, $destination]), new LineString([$anotherOrigin, $intSummit, $anotherDestination])]),
            self::MULTI_LINE_STRING_EXPECTED_MULTI_LINES,
        ];
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
     * Data provider for multi-points.
     *
     * @return \Generator<string, array{0: MultiPointInterface, 1: string}, null, void>
     *
     * @throws SpatialTypeExceptionInterface This should not happen, as the data provider only provides valid multi-points
     */
    public static function multiPointProvider(): \Generator
    {
        // Let's try the simplest multi-point
        $origin = new GeometricPoint(0, 0);
        $summit = new GeometricPoint(0, 1);
        $destination = new GeometricPoint(1, 1);

        yield 'MULTIPOINT(0 0, 0 1, 1 1)' => [
            new MultiPoint([$origin, $summit, $destination]),
            self::MULTI_POINT_EXPECTED,
        ];
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
     * Data provider for multi-polygons.
     *
     * @return \Generator<string, array{0: MultiPolygonInterface, 1: string}, null, void>
     *
     * @throws SpatialTypeExceptionInterface This should not happen, as the data provider only provides valid multi-polygons
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
            self::MULTI_POLYGON_EXPECTED_SINGLE_POLYGON,
        ];

        // Let's try a multi-polygon with more polygons
        $moreLeft = new GeometricPoint(-2, 0);
        $moreRight = new GeometricPoint(2, 0);
        $moreTop = new GeometricPoint(0, 2);
        $moreBottom = new GeometricPoint(0, -2);

        yield 'MULTIPOLYGON(((-1 0, 0 -1, 1 0, 0 1, -1 0)), ((-2 0, 0 -2, 2 0, 0 2, -2 0)))' => [
            new MultiPolygon([new Polygon([[$left, $bottom, $right, $top, $left]]), new Polygon([[$moreLeft, $moreBottom, $moreRight, $moreTop, $moreLeft]])]),
            self::MULTI_POLYGON_EXPECTED_MULTI_POLYGONS,
        ];
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
     * Data provider for points.
     *
     * @return \Generator<string, array{0: PointInterface, 1: string}, null, void>
     *
     * @throws SpatialTypeExceptionInterface This should not happen, as the data provider only provides valid points
     */
    public static function pointProvider(): \Generator
    {
        // Try the simplest point
        yield 'GEOMETRIC POINT(0 0)' => [
            new GeometricPoint(0, 0),
            self::POINT_EXPECTED_DEFAULT,
        ];

        // Let's try a geographic point
        yield 'GEOGRAPHIC POINT(0 0)' => [
            new GeometricPoint(0, 0),
            self::POINT_EXPECTED_DEFAULT,
        ];

        // Let's add a SRID to the point
        yield 'SRID=4326;GEOMETRIC POINT(0 0)' => [
            (new GeometricPoint(0, 0))->setSrid(self::SRID_YX),
            self::POINT_EXPECTED_WITH_SRID_YX,
        ];

        // Let's check a point with X and Y different from 0
        yield 'POINT(-1 1)' => [
            new GeometricPoint(-1, 1),
            self::POINT_EXPECTED_WITH_X_Y,
        ];

        // Let's check a point with X and Y in the opposite order
        yield 'POINT(1 -1)' => [
            new GeometricPoint(1, -1),
            self::POINT_EXPECTED_WITH_INVERTED_COORDINATES,
        ];

        // Let's check float values
        yield 'POINT(42.2 2.42)' => [
            new GeometricPoint(42.2, 2.42),
            self::POINT_EXPECTED_WITH_FLOATS,
        ];

        // Let's check that the SRID YX does NOT affect the result
        yield 'SRID=4326;POINT(1 -1)' => [
            (new GeometricPoint(1, -1))->setSrid(self::SRID_YX),
            self::POINT_EXPECTED_WITH_SRID_YX_NO_EFFECT,
        ];

        // Let's check that the SRID XY does NOT affect the result
        yield 'SRID=7035; POINT(1 -1)' => [
            (new GeometricPoint(1, -1))->setSrid(self::SRID_XY),
            self::POINT_EXPECTED_WITH_SRID_XY_NO_EFFECT,
        ];
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

    /**
     * Data provider for polygons.
     *
     * @return \Generator<string, array{0: PolygonInterface, 1: string}, null, void>
     *
     * @throws SpatialTypeExceptionInterface This should not happen, as the data provider only provides valid polygons
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
            self::POLYGON_EXPECTED_ORDER_OF_POINTS,
        ];

        $moreLeft = new GeometricPoint(-2, 0);
        $moreRight = new GeometricPoint(2, 0);
        $moreTop = new GeometricPoint(0, 2);
        $moreBottom = new GeometricPoint(0, -2);

        // Let's try a polygon with a hole
        yield 'POLYGON((-1 0, -1 -1, 1 0, 1 0, -1 0), (-2 0, -2 -2, 2 0, 2 0, -2 0))' => [
            new Polygon([[$left, $bottom, $right, $top, $left], [$moreLeft, $moreBottom, $moreRight, $moreTop, $moreLeft]]),
            self::POLYGON_EXPECTED_WITH_HOLE,
        ];
    }
}
