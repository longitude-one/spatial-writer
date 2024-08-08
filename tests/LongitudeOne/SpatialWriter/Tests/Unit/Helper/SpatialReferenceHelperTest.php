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

namespace LongitudeOne\SpatialWriter\Tests\Unit\Helper;

use LongitudeOne\SpatialWriter\Helper\AxisOrderEnum;
use LongitudeOne\SpatialWriter\Helper\SpatialReferenceHelper;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \LongitudeOne\SpatialWriter\Helper\AxisOrderEnum
 * @covers \LongitudeOne\SpatialWriter\Helper\SpatialReferenceHelper
 * @covers \LongitudeOne\SpatialWriter\Helper\SridLoader
 */
class SpatialReferenceHelperTest extends TestCase
{
    /**
     * Data provider for the testGetAxisOrder method.
     *
     * @return \Generator<string, array{null|int, AxisOrderEnum}, null, void>
     */
    public static function sridProvider(): \Generator
    {
        yield 'SRID 4326 => YX' => [4326, AxisOrderEnum::YX];

        yield 'SRID 0 => XY' => [0, AxisOrderEnum::XY];

        yield 'SRID 7037 => XY' => [7037, AxisOrderEnum::XY];

        yield 'SRID unknown => XY' => [999999999, AxisOrderEnum::XY];

        yield 'SRID null => XY' => [null, AxisOrderEnum::XY];
    }

    /**
     * Test getAxisOrder method with some known values.
     *
     * @param null|int      $srid      SRID to test
     * @param AxisOrderEnum $axisOrder expected axis order
     */
    #[DataProvider('sridProvider')]
    public function testGetAxisOrder(?int $srid, AxisOrderEnum $axisOrder): void
    {
        static::assertSame($axisOrder, SpatialReferenceHelper::getAxisOrder($srid));
    }
}
