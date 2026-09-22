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

namespace LongitudeOne\SpatialEncoder\Tests\Unit\Strategy;

use LongitudeOne\Core\Enum\GeometryTypeEnum;
use LongitudeOne\SpatialEncoder\Exception\UnsupportedSpatialInterfaceException;
use LongitudeOne\SpatialEncoder\Exception\UnsupportedSpatialTypeException;
use LongitudeOne\SpatialEncoder\Strategy\WktTextStrategy;
use LongitudeOne\SpatialTypes\Interfaces\SpatialInterface;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geometry\GeometryCollection;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geometry\Point;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geometry\Polygon;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \LongitudeOne\SpatialEncoder\Strategy\WktTextStrategy
 */
class WktStrategyTest extends TestCase
{
    /**
     * Provide constructor arguments and WKT bodies for the available types.
     *
     * @param int[][]  $coordinates the ring coordinates
     * @param string[] $positions   the ring coordinate text
     *
     * @phpstan-param list<list<int>> $coordinates
     *
     * @return \Generator<string, array{list<mixed>, string}>
     */
    private static function typeProvider(array $coordinates, array $positions): \Generator
    {
        $ring = '('.implode(', ', $positions).')';

        yield 'Point' => [[...$coordinates[1], 4326], '('.$positions[1].')'];

        yield 'LineString' => [[$coordinates, 4326], $ring];

        yield 'Polygon' => [[[$coordinates], 4326], '('.$ring.')'];

        yield 'Triangle' => [[[$coordinates], 4326], '('.$ring.')'];

        yield 'MultiPoint' => [[[$coordinates[0], $coordinates[1]], 4326], '(('.$positions[0].'), ('.$positions[1].'))'];

        yield 'MultiLineString' => [[[$coordinates, $coordinates], 4326], '('.$ring.', '.$ring.')'];

        yield 'MultiPolygon' => [[[[$coordinates], [$coordinates]], 4326], '(('.$ring.'), ('.$ring.'))'];

        yield 'PolyhedralSurface' => [[[[$coordinates]], 4326], '(('.$ring.'))'];

        yield 'GeometryCollection' => [[4326, []], 'EMPTY'];
    }

    /**
     * Test every concrete geometry and geography type in each available dimension.
     *
     * @param SpatialInterface $spatial  the geometry to write
     * @param string           $expected the expected WKT
     */
    #[DataProvider('geometryProvider')]
    public function testGeometry(SpatialInterface $spatial, string $expected): void
    {
        static::assertSame($expected, (new WktTextStrategy())->encode($spatial));
    }

    /**
     * Provide both populated and empty instances, all declared with SRID 4326.
     *
     * @return \Generator<string, array{SpatialInterface, string}>
     */
    public static function geometryProvider(): \Generator
    {
        foreach (self::dimensionProvider() as $dimension => [$coordinates, $positions, $marker]) {
            foreach (['Geometry', 'Geography'] as $family) {
                $namespace = 'LongitudeOne\SpatialTypes\Types\\'.$dimension.'\\'.$family.'\\';
                foreach (self::typeProvider($coordinates, $positions) as $type => [$arguments, $body]) {
                    $className = $namespace.$type;
                    if ('GeometryCollection' === $type && 'Geography' === $family) {
                        $className = $namespace.'GeographyCollection';
                    }
                    if ('PolyhedralSurface' === $type && !in_array($dimension, ['Dimension3z', 'Dimension4zm'], true)) {
                        continue;
                    }

                    if ('GeometryCollection' === $type) {
                        /** @var class-string<SpatialInterface> $pointClass */
                        $pointClass = $namespace.'Point';
                        $arguments = [4326, [new $pointClass(...[...$coordinates[1], 4326])]];
                        $body = '(POINT'.$marker.' ('.$positions[1].'))';
                    }

                    /** @var class-string<SpatialInterface> $className */
                    $spatial = new $className(...$arguments);
                    $name = mb_strtoupper($type).$marker;

                    yield $dimension.' '.$family.' '.$type => [$spatial, $name.' '.$body];

                    $emptyArguments = match ($type) {
                        'Point' => ['srid' => 4326],
                        'GeometryCollection' => [4326, []],
                        default => [[], 4326],
                    };

                    yield $dimension.' '.$family.' '.$type.' empty' => [new $className(...$emptyArguments), $name.' EMPTY'];
                }
            }
        }
    }

    /**
     * Verify collection recursion preserves member types, order, empties and dimensions.
     *
     * @param int[][]  $coordinates the coordinates in the given dimension
     * @param string[] $positions   the expected coordinate text
     * @param string   $marker      the WKT dimension marker
     *
     * @phpstan-param ''|' Z'|' M'|' ZM' $marker
     */
    #[DataProvider('dimensionProvider')]
    public function testNestedCollections(array $coordinates, array $positions, string $marker): void
    {
        $dimension = match ($marker) {
            '' => 'Dimension2',
            ' Z' => 'Dimension3z',
            ' M' => 'Dimension3m',
            ' ZM' => 'Dimension4zm',
        };
        $namespace = 'LongitudeOne\SpatialTypes\Types\\'.$dimension.'\Geometry\\';

        /** @var class-string<SpatialInterface> $pointClass */
        $pointClass = $namespace.'Point';

        /** @var class-string<SpatialInterface> $collectionClass */
        $collectionClass = $namespace.'GeometryCollection';
        $point = new $pointClass(...$coordinates[1]);
        $collection = new $collectionClass(0, [new $pointClass(), new $collectionClass(0, [$point]), new $collectionClass()]);

        static::assertSame(
            'GEOMETRYCOLLECTION'.$marker.' (POINT'.$marker.' EMPTY, GEOMETRYCOLLECTION'.$marker.' (POINT'.$marker.' ('.$positions[1].')), GEOMETRYCOLLECTION'.$marker.' EMPTY)',
            (new WktTextStrategy())->encode($collection)
        );
    }

    /**
     * Provide coordinates and independently specified text for XY, XYZ, XYM and XYZM.
     *
     * @return \Generator<string, array{list<list<int>>, list<string>, ' M'|' Z'|' ZM'|''}>
     */
    public static function dimensionProvider(): \Generator
    {
        yield 'Dimension2' => [[[0, 0], [2, 0], [0, 2], [0, 0]], ['0 0', '2 0', '0 2', '0 0'], ''];

        yield 'Dimension3z' => [[[0, 0, 3], [2, 0, 3], [0, 2, 3], [0, 0, 3]], ['0 0 3', '2 0 3', '0 2 3', '0 0 3'], ' Z'];

        yield 'Dimension3m' => [[[0, 0, 4], [2, 0, 4], [0, 2, 4], [0, 0, 4]], ['0 0 4', '2 0 4', '0 2 4', '0 0 4'], ' M'];

        yield 'Dimension4zm' => [[[0, 0, 3, 4], [2, 0, 3, 4], [0, 2, 3, 4], [0, 0, 3, 4]], ['0 0 3 4', '2 0 3 4', '0 2 3 4', '0 0 3 4'], ' ZM'];
    }

    /**
     * Test non-finite ordinates cannot produce invalid WKT.
     *
     * @param float $coordinate the non-finite ordinate
     */
    #[DataProvider('nonFiniteProvider')]
    public function testNonFiniteCoordinate(float $coordinate): void
    {
        $this->expectException(\JsonException::class);
        (new WktTextStrategy())->encode(new Point($coordinate, 0));
    }

    /** @return \Generator<string, array{float}> */
    public static function nonFiniteProvider(): \Generator
    {
        yield 'infinity' => [INF];

        yield 'negative infinity' => [-INF];

        yield 'not a number' => [NAN];
    }

    /** Test formatting does not follow the process decimal separator. */
    public function testNumericLocale(): void
    {
        $previous = setlocale(LC_NUMERIC, '0');
        if (false === $previous || false === setlocale(LC_NUMERIC, 'fr_FR.UTF-8', 'fr_FR.utf8', 'fr_FR')) {
            static::markTestSkipped('A French numeric locale is not installed.');
        }

        try {
            static::assertSame('POINT (42.42 -2.5)', (new WktTextStrategy())->encode(new Point(42.42, -2.5)));
        } finally {
            setlocale(LC_NUMERIC, $previous);
        }
    }

    /** Test decimal, negative, very small and high-precision ordinates. */
    public function testNumericPrecision(): void
    {
        $collection = new GeometryCollection(0, [new Point(-42.42, 2.123456789012345), new Point(1.0e-20, PHP_INT_MAX)]);
        static::assertSame(
            'GEOMETRYCOLLECTION (POINT (-42.42 2.123456789012345), POINT (1.0e-20 '.PHP_INT_MAX.'))',
            (new WktTextStrategy())->encode($collection)
        );
    }

    /** Test polygons preserve exterior and interior ring order. */
    public function testPolygonWithHole(): void
    {
        $polygon = new Polygon([
            [[0, 0], [4, 0], [4, 4], [0, 4], [0, 0]],
            [[1, 1], [1, 2], [2, 2], [2, 1], [1, 1]],
        ]);
        static::assertSame(
            'POLYGON ((0 0, 4 0, 4 4, 0 4, 0 0), (1 1, 1 2, 2 2, 2 1, 1 1))',
            (new WktTextStrategy())->encode($polygon)
        );
    }

    /** Test an unsupported interface fails explicitly. */
    public function testUnsupportedInterface(): void
    {
        $spatial = static::createStub(SpatialInterface::class);
        $spatial->method('getType')->willReturn(GeometryTypeEnum::POINT);
        $spatial->method('isEmpty')->willReturn(false);
        $this->expectException(UnsupportedSpatialInterfaceException::class);
        (new WktTextStrategy())->encode($spatial);
    }

    /** Test unimplemented enum cases are rejected, even when empty. */
    public function testUnsupportedType(): void
    {
        $spatial = static::createStub(SpatialInterface::class);
        $spatial->method('getType')->willReturn(GeometryTypeEnum::CIRCULARSTRING);
        $spatial->method('isEmpty')->willReturn(true);
        $this->expectException(UnsupportedSpatialTypeException::class);
        $this->expectExceptionMessage('WKT adapter does not support spatial type CircularString');
        (new WktTextStrategy())->encode($spatial);
    }
}
