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

namespace LongitudeOne\SpatialEncoder\Tests\Unit\Exception;

use LongitudeOne\SpatialEncoder\Exception\ExceptionInterface;
use LongitudeOne\SpatialEncoder\Exception\UnavailableResourceException;
use LongitudeOne\SpatialEncoder\Exception\UnsupportedDimensionException;
use LongitudeOne\SpatialEncoder\Exception\UnsupportedSpatialInterfaceException;
use LongitudeOne\SpatialEncoder\Exception\UnsupportedSpatialTypeException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \LongitudeOne\SpatialEncoder\Exception\UnavailableResourceException
 * @covers \LongitudeOne\SpatialEncoder\Exception\UnsupportedDimensionException
 * @covers \LongitudeOne\SpatialEncoder\Exception\UnsupportedSpatialInterfaceException
 * @covers \LongitudeOne\SpatialEncoder\Exception\UnsupportedSpatialTypeException
 */
class ExceptionTest extends TestCase
{
    /**
     * Let's check that all internal exceptions are instances of ExceptionInterface.
     *
     * @param object $exception exception to test
     */
    #[DataProvider('exceptionProvider')]
    public function testUnavailableResourceException(object $exception): void
    {
        static::assertInstanceOf(\Exception::class, $exception);
        static::assertInstanceOf(ExceptionInterface::class, $exception);
    }

    /**
     * @return \Generator<string, array{0: object}, null, void>
     */
    public static function exceptionProvider(): \Generator
    {
        yield 'UnavailableResourceException' => [new UnavailableResourceException()];

        yield 'UnsupportedSpatialTypeException' => [new UnsupportedSpatialTypeException()];

        yield 'UnsupportedDimensionException' => [new UnsupportedDimensionException()];

        yield 'UnsupportedSpatialInterfaceException' => [new UnsupportedSpatialInterfaceException()];
    }
}
