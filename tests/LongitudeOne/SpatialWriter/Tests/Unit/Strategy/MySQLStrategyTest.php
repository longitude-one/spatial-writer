<?php
/**
 * This file is part of the binary-writer project.
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

namespace LongitudeOne\SpatialWriter\Tests\Unit\Strategy;

use LongitudeOne\SpatialWriter\Strategy\MySQLBinaryStrategy;
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
use LongitudeOne\SpatialTypes\Types\Geometry\Point;
use LongitudeOne\SpatialTypes\Types\Geometry\Polygon;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * MySQLStrategyTest class.
 *
 * The result values are checked with this kind of SQL command:
 * > select HEX(ST_GeomFromText('POINT(0 0)', 4326))
 *
 * @internal
 *
 * @covers \LongitudeOne\SpatialWriter\Strategy\MySQLBinaryStrategy
 */
class MySQLStrategyTest extends TestCase
{
    /**
     * @var MySQLBinaryStrategy the MySQL strategy to test
     */
    private MySQLBinaryStrategy $strategy;

    /**
     * Set up the MySQL strategy.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->strategy = new MySQLBinaryStrategy();
    }

    /**
     * Tear down the MySQL strategy.
     */
    protected function tearDown(): void
    {
        unset($this->strategy);
        parent::tearDown();
    }

    /**
     * LineString provider.
     *
     * @return \Generator<string, array{0: LineStringInterface, 1: string}, null, void>
     *
     * @throws SpatialTypeExceptionInterface this won't happen because provided coordinates are exact
     */
    public static function lineStringProvider(): \Generator
    {
        // Linestring without SRID
        yield 'SRID is null;LINESTRING(0 0, 1 2, 2.2 2.4)' => [
            new LineString([[0, 0], [1, 2], [2.2, 2.4]]),
            '0000000001020000000300000000000000000000000000000000000000000000000000F03F00000000000000409A999999999901403333333333330340',
        ];

        // Linestring with SRID XY
        yield 'SRID=7035;LINESTRING(0 0, 1 2, 2.2 2.4)' => [
            (new LineString([[0, 0], [1, 2], [2.2, 2.4]]))->setSrid(7035),
            '7B1B000001020000000300000000000000000000000000000000000000000000000000F03F00000000000000409A999999999901403333333333330340',
        ];

        // Linestring with SRID YX
        yield 'SRID=4326;LINESTRING(0 0, 1 2, 2.2 2.4)' => [
            (new LineString([[0, 0], [1, 2], [2.2, 2.4]]))->setSrid(4326),
            'E6100000010200000003000000000000000000000000000000000000000000000000000040000000000000F03F33333333333303409A99999999990140',
        ];
    }

    /**
     * MultiLineString provider.
     *
     * @return \Generator<string, array{0: MultiLineStringInterface, 1: string}, null, void>
     *
     * @throws SpatialTypeExceptionInterface this won't happen because provided coordinates are exact
     */
    public static function multiLineStringProvider(): \Generator
    {
        // MultiLineString without SRID
        yield 'SRID is null;MULTILINESTRING((-1 -1, -1 -2, -2.2 -2.4),(0 0, 1 2, 2.2 2.4))' => [
            new MultiLineString([
                [[-1, -1], [-1, -2], [-2.2, -2.4]],
                [[0, 0], [1, 2], [2.2, 2.4]],
            ]),
            '00000000010500000002000000010200000003000000000000000000F0BF000000000000F0BF000000000000F0BF00000000000000C09A999999999901C033333333333303C001020000000300000000000000000000000000000000000000000000000000F03F00000000000000409A999999999901403333333333330340',
        ];
    }

    /**
     * MultiPoint provider.
     *
     * @return \Generator<string, array{0: MultiPointInterface, 1: string}, null, void>
     *
     * @throws SpatialTypeExceptionInterface this won't happen because provided coordinates are exact
     */
    public static function multiPointProvider(): \Generator
    {
        // MultiPoint without SRID and two points
        yield 'SRID is null;MULTIPOINT((0 3), (1 2))' => [
            new MultiPoint([[0, 3], [1, 2]]),
            '000000000104000000020000000101000000000000000000000000000000000008400101000000000000000000F03F0000000000000040',
        ];
    }

    /**
     * MultiPolygon provider.
     *
     * @return \Generator<string, array{0: MultiPolygonInterface, 1: string}, null, void>
     *
     * @throws SpatialTypeExceptionInterface this won't happen because provided coordinates are exact
     */
    public static function multiPolygonProvider(): \Generator
    {
        // MultiPolygon without SRID
        yield 'SRID is null;MULTIPOLYGON(((0 0, 1 2, 2.2 2.4, 0 0)), ((-1 -1, -1 -2, -2.2 -2.4, -1 -1)))' => [
            new MultiPolygon([
                [[[0, 0], [1, 2], [2.2, 2.4], [0, 0]]],
                [[[-1, -1], [-1, -2], [-2.2, -2.4], [-1, -1]]],
            ]),
            '000000000106000000020000000103000000010000000400000000000000000000000000000000000000000000000000F03F00000000000000409A9999999999014033333333333303400000000000000000000000000000000001030000000100000004000000000000000000F0BF000000000000F0BF000000000000F0BF00000000000000C09A999999999901C033333333333303C0000000000000F0BF000000000000F0BF',
        ];
    }

    /**
     * Point provider.
     *
     * @return \Generator<string, array{0: PointInterface, 1: string}, null, void>
     *
     * @throws SpatialTypeExceptionInterface this won't happen because provided coordinates are exact
     */
    public static function pointProvider(): \Generator
    {
        // POINT With SRID
        yield 'SRID=4326;POINT(0 0)' => [
            (new Point(0, 0))->setSrid(4326),
            'E6100000010100000000000000000000000000000000000000',
        ];

        // POINT Without SRID
        yield 'SRID is null;POINT(1 2)' => [
            new Point(1, 2),
            '000000000101000000000000000000F03F0000000000000040',
        ];

        // POINT With SRID 0
        yield 'SRID=0;POINT(-1 -2)' => [
            (new Point(-1, -2))->setSrid(0),
            '000000000101000000000000000000F0BF00000000000000C0',
        ];

        // POINT with float values and YX SRID
        yield 'SRID=4326;POINT(42.1 42.42)' => [
            (new Point(42.1, 42.42))->setSrid(4326),
            'E61000000101000000F6285C8FC2354540CDCCCCCCCC0C4540',
        ];

        // POINT with XY SRID
        yield 'SRID=7035;POINT(42.1 42.42)' => [
            (new Point(42.1, 42.42))->setSrid(7035),
            '7B1B00000101000000CDCCCCCCCC0C4540F6285C8FC2354540',
        ];
    }

    /**
     * Polygon provider.
     *
     * The result values are checked with this kind of SQL command:
     * > select HEX(ST_GeomFromText('POINT(0 0)', 4326))
     *
     * @return \Generator<string, array{0: PolygonInterface, 1: string}, null, void>
     *
     * @throws SpatialTypeExceptionInterface this won't happen because provided coordinates are exact
     */
    public static function polygonProvider(): \Generator
    {
        // Polygon without SRID
        yield 'SRID is null;POLYGON((0 0, 1 2, 2.2 2.4, 0 0))' => [
            new Polygon([[[0, 0], [1, 2], [2.2, 2.4], [0, 0]]]),
            '000000000103000000010000000400000000000000000000000000000000000000000000000000F03F00000000000000409A99999999990140333333333333034000000000000000000000000000000000',
        ];

        // Polygon with two lines and one SRID YX
        yield 'SRID=4326;POLYGON((0 0, 0 1, 1 1, 1 0, 0 0),(0 0, 1 2, 2.2 2.4, 6 6, 0 0))' => [
            (new Polygon([
                [[0, 0], [0, 1], [1, 1], [1, 0], [0, 0]],
                [[0, 0], [1, 2], [2.2, 2.4], [6, 6], [0, 0]],
            ]))->setSrid(4326),
            'E61000000103000000020000000500000000000000000000000000000000000000000000000000F03F0000000000000000000000000000F03F000000000000F03F0000000000000000000000000000F03F0000000000000000000000000000000005000000000000000000000000000000000000000000000000000040000000000000F03F33333333333303409A999999999901400000000000001840000000000000184000000000000000000000000000000000',
        ];

        // Polygon with two lines and one SRID XY
        yield 'SRID=7035;POLYGON((0 0, 0 1, 1 1, 1 0, 0 0),(0 0, 1 2, 2.2 2.4, 6 6, 0 0))' => [
            (new Polygon([
                [[0, 0], [0, 1], [1, 1], [1, 0], [0, 0]],
                [[0, 0], [1, 2], [2.2, 2.4], [6, 6], [0, 0]],
            ]))->setSrid(7035),
            '7B1B000001030000000200000005000000000000000000000000000000000000000000000000000000000000000000F03F000000000000F03F000000000000F03F000000000000F03F0000000000000000000000000000000000000000000000000500000000000000000000000000000000000000000000000000F03F00000000000000409A9999999999014033333333333303400000000000001840000000000000184000000000000000000000000000000000',
        ];
    }

    /**
     * Let's check the MySQL strategy with a linestring.
     *
     * @param LineStringInterface $lineString the linestring to convert
     * @param string              $expected   the expected result in hexadecimal format
     */
    #[DataProvider('lineStringProvider')]
    public function testLineString(LineStringInterface $lineString, string $expected): void
    {
        static::assertSame($expected, mb_strtoupper(bin2hex($this->strategy->executeStrategy($lineString))));
    }

    /**
     * Let's check the MySQL strategy with a multilinestring.
     *
     * @param MultiLineStringInterface $multiLineString the multilinestring to convert
     * @param string                   $expected        the expected result in hexadecimal format
     */
    #[DataProvider('multiLineStringProvider')]
    public function testMultiLineString(MultiLineStringInterface $multiLineString, string $expected): void
    {
        static::assertSame($expected, mb_strtoupper(bin2hex($this->strategy->executeStrategy($multiLineString))));
    }

    /**
     * Let's check the MySQL strategy with a multipoint.
     *
     * @param MultiPointInterface $multiPoint the multipoint to convert
     * @param string              $expected   the expected result in hexadecimal format
     */
    #[DataProvider('multiPointProvider')]
    public function testMultiPoint(MultiPointInterface $multiPoint, string $expected): void
    {
        static::assertSame($expected, mb_strtoupper(bin2hex($this->strategy->executeStrategy($multiPoint))));
    }

    /**
     * Let's check the MySQL strategy with a multipolygon.
     *
     * @param MultiPolygonInterface $multiPolygon the multipolygon to convert
     * @param string                $expected     the expected result in hexadecimal format
     */
    #[DataProvider('multiPolygonProvider')]
    public function testMultiPolygon(MultiPolygonInterface $multiPolygon, string $expected): void
    {
        static::assertSame($expected, mb_strtoupper(bin2hex($this->strategy->executeStrategy($multiPolygon))));
    }

    /**
     * Let's check the MySQL strategy with a point.
     *
     * @param PointInterface $point    the point to convert
     * @param string         $expected the expected result in hexadecimal format
     */
    #[DataProvider('pointProvider')]
    public function testPoint(PointInterface $point, string $expected): void
    {
        static::assertSame($expected, mb_strtoupper(bin2hex($this->strategy->executeStrategy($point))));
    }

    /**
     * Let's check the MySQL strategy with a polygon.
     *
     * @param PolygonInterface $polygon  the polygon to convert
     * @param string           $expected the expected result in hexadecimal format
     */
    #[DataProvider('polygonProvider')]
    public function testPolygon(PolygonInterface $polygon, string $expected): void
    {
        static::assertSame($expected, mb_strtoupper(bin2hex($this->strategy->executeStrategy($polygon))));
    }
}
