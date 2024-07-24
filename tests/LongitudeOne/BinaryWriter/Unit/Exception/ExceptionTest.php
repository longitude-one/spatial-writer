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

namespace LongitudeOne\BinaryWriter\Tests\Unit\Exception;

use LongitudeOne\BinaryWriter\Exception\ExceptionInterface;
use LongitudeOne\BinaryWriter\Exception\UnavailableResourceException;
use LongitudeOne\BinaryWriter\Exception\UnsupportedDimensionException;
use LongitudeOne\BinaryWriter\Exception\UnsupportedSpatialInterfaceException;
use LongitudeOne\BinaryWriter\Exception\UnsupportedSpatialTypeException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \LongitudeOne\BinaryWriter\Exception\UnavailableResourceException
 * @covers \LongitudeOne\BinaryWriter\Exception\UnsupportedDimensionException
 * @covers \LongitudeOne\BinaryWriter\Exception\UnsupportedSpatialInterfaceException
 * @covers \LongitudeOne\BinaryWriter\Exception\UnsupportedSpatialTypeException
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
