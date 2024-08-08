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

namespace LongitudeOne\SpatialWriter\Tests\Unit\Exception;

use LongitudeOne\SpatialWriter\Exception\ExceptionInterface;
use LongitudeOne\SpatialWriter\Exception\UnavailableResourceException;
use LongitudeOne\SpatialWriter\Exception\UnsupportedDimensionException;
use LongitudeOne\SpatialWriter\Exception\UnsupportedSpatialInterfaceException;
use LongitudeOne\SpatialWriter\Exception\UnsupportedSpatialTypeException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \LongitudeOne\SpatialWriter\Exception\UnavailableResourceException
 * @covers \LongitudeOne\SpatialWriter\Exception\UnsupportedDimensionException
 * @covers \LongitudeOne\SpatialWriter\Exception\UnsupportedSpatialInterfaceException
 * @covers \LongitudeOne\SpatialWriter\Exception\UnsupportedSpatialTypeException
 */
class ExceptionTest extends TestCase
{
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
}
