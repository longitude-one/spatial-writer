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

namespace LongitudeOne\SpatialWriter\Tests\Unit\Strategy\GeoJson;

use LongitudeOne\Core\Enum\GeometryTypeEnum;
use LongitudeOne\SpatialTypes\Interfaces\SpatialInterface;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geometry\LineString;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geometry\MultiLineString;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geometry\MultiPoint;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geometry\Point;
use LongitudeOne\SpatialWriter\Exception\ExceptionInterface;
use LongitudeOne\SpatialWriter\Exception\JsonEncodingException;
use LongitudeOne\SpatialWriter\Exception\UnsupportedDimensionException;
use LongitudeOne\SpatialWriter\Exception\UnsupportedGeometryStructureException;
use LongitudeOne\SpatialWriter\Exception\UnsupportedSpatialInterfaceException;
use LongitudeOne\SpatialWriter\Exception\UnsupportedSpatialTypeException;
use LongitudeOne\SpatialWriter\Strategy\GeoJsonStrategy;
use LongitudeOne\SpatialWriter\Writer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Public GeoJSON exception contracts and actual model encoding failures.
 *
 * @internal
 */
#[CoversClass(GeoJsonStrategy::class)]
#[CoversClass(JsonEncodingException::class)]
#[CoversClass(UnsupportedGeometryStructureException::class)]
#[CoversClass(UnsupportedSpatialTypeException::class)]
#[CoversClass(UnsupportedDimensionException::class)]
#[CoversClass(UnsupportedSpatialInterfaceException::class)]
#[CoversClass(Writer::class)]
class ExceptionContractTest extends TestCase
{
    /** The installed model accepts this float through its ordinary constructor. */
    public function testInfinityEncodingFailure(): void
    {
        $point = new Point(INF, 2);

        try {
            (new GeoJsonStrategy())->executeStrategy($point);
            static::fail('JSON encoding must fail.');
        } catch (JsonEncodingException $exception) {
            static::assertContains(ExceptionInterface::class, (new \ReflectionClass($exception))->getInterfaceNames());
            static::assertInstanceOf(\JsonException::class, $exception->getPrevious());
            static::assertSame(JSON_ERROR_INF_OR_NAN, $exception->getPrevious()->getCode());
        }
    }

    /** Preserve the approved public exception inheritance and constructor contract. */
    public function testJsonEncodingExceptionContract(): void
    {
        $previous = new \RuntimeException('Original failure');
        $exception = new JsonEncodingException('Failure', 12, $previous);

        $parent = (new \ReflectionClass($exception))->getParentClass();
        static::assertNotFalse($parent);
        static::assertSame(\RuntimeException::class, $parent->getName());
        static::assertContains(ExceptionInterface::class, (new \ReflectionClass($exception))->getInterfaceNames());
        static::assertSame('Failure', $exception->getMessage());
        static::assertSame(12, $exception->getCode());
        static::assertSame($previous, $exception->getPrevious());
    }

    /** The installed model accepts non-finite ordinates in real line strings. */
    public function testLineStringEncodingFailure(): void
    {
        $line = new LineString([[1, 2], [INF, 3]]);

        try {
            (new Writer(new GeoJsonStrategy()))->convert($line);
            static::fail('JSON encoding must fail.');
        } catch (JsonEncodingException $exception) {
            static::assertInstanceOf(\JsonException::class, $exception->getPrevious());
            static::assertSame(JSON_ERROR_INF_OR_NAN, $exception->getPrevious()->getCode());
        }
    }

    /** Non-finite line members use the shared JSON failure contract. */
    public function testMultiLineStringEncodingFailure(): void
    {
        $multiLine = new MultiLineString([[[1, 2], [3, 4]], [[5, 6], [INF, 8]]]);

        try {
            (new Writer(new GeoJsonStrategy()))->convert($multiLine);
            static::fail('JSON encoding must fail.');
        } catch (JsonEncodingException $exception) {
            static::assertInstanceOf(\JsonException::class, $exception->getPrevious());
            static::assertSame(JSON_ERROR_INF_OR_NAN, $exception->getPrevious()->getCode());
        }
    }

    /** Non-finite MultiPoint members use the shared JSON failure contract. */
    public function testMultiPointEncodingFailure(): void
    {
        $multiPoint = new MultiPoint([[1, 2], [INF, 3]]);

        try {
            (new Writer(new GeoJsonStrategy()))->convert($multiPoint);
            static::fail('JSON encoding must fail.');
        } catch (JsonEncodingException $exception) {
            static::assertInstanceOf(\JsonException::class, $exception->getPrevious());
            static::assertSame(JSON_ERROR_INF_OR_NAN, $exception->getPrevious()->getCode());
        }
    }

    /** The installed model accepts this float through its ordinary constructor. */
    public function testNanEncodingFailure(): void
    {
        $point = new Point(NAN, 2);

        try {
            (new GeoJsonStrategy())->executeStrategy($point);
            static::fail('JSON encoding must fail.');
        } catch (JsonEncodingException $exception) {
            static::assertContains(ExceptionInterface::class, (new \ReflectionClass($exception))->getInterfaceNames());
            static::assertInstanceOf(\JsonException::class, $exception->getPrevious());
            static::assertSame(JSON_ERROR_INF_OR_NAN, $exception->getPrevious()->getCode());
        }
    }

    /** The installed model accepts this float through its ordinary constructor. */
    public function testNegativeInfinityEncodingFailure(): void
    {
        $point = new Point(-INF, 2);

        try {
            (new GeoJsonStrategy())->executeStrategy($point);
            static::fail('JSON encoding must fail.');
        } catch (JsonEncodingException $exception) {
            static::assertContains(ExceptionInterface::class, (new \ReflectionClass($exception))->getInterfaceNames());
            static::assertInstanceOf(\JsonException::class, $exception->getPrevious());
            static::assertSame(JSON_ERROR_INF_OR_NAN, $exception->getPrevious()->getCode());
        }
    }

    /** Preserve the approved public exception inheritance and constructor contract. */
    public function testUnsupportedDimensionExceptionContract(): void
    {
        $previous = new \RuntimeException('Original failure');
        $exception = new UnsupportedDimensionException('Failure', 12, $previous);

        $parent = (new \ReflectionClass($exception))->getParentClass();
        static::assertNotFalse($parent);
        static::assertSame(\InvalidArgumentException::class, $parent->getName());
        static::assertContains(ExceptionInterface::class, (new \ReflectionClass($exception))->getInterfaceNames());
        static::assertSame('Failure', $exception->getMessage());
        static::assertSame(12, $exception->getCode());
        static::assertSame($previous, $exception->getPrevious());
    }

    /** Preserve the approved public exception inheritance and constructor contract. */
    public function testUnsupportedGeometryStructureExceptionContract(): void
    {
        $previous = new \RuntimeException('Original failure');
        $exception = new UnsupportedGeometryStructureException('Failure', 12, $previous);

        $parent = (new \ReflectionClass($exception))->getParentClass();
        static::assertNotFalse($parent);
        static::assertSame(\InvalidArgumentException::class, $parent->getName());
        static::assertContains(ExceptionInterface::class, (new \ReflectionClass($exception))->getInterfaceNames());
        static::assertSame('Failure', $exception->getMessage());
        static::assertSame(12, $exception->getCode());
        static::assertSame($previous, $exception->getPrevious());
    }

    /** Reject a declared LineString without its corresponding interface. */
    public function testUnsupportedLineStringInterfaceThroughWriter(): void
    {
        $spatial = static::createStub(SpatialInterface::class);
        $spatial->method('getType')->willReturn(GeometryTypeEnum::LINESTRING);
        $spatial->method('hasM')->willReturn(false);

        $this->expectException(UnsupportedSpatialInterfaceException::class);
        (new Writer(new GeoJsonStrategy()))->convert($spatial);
    }

    /** Reject a declared MultiLineString without its corresponding interface. */
    public function testUnsupportedMultiLineStringInterfaceThroughWriter(): void
    {
        $spatial = static::createStub(SpatialInterface::class);
        $spatial->method('getType')->willReturn(GeometryTypeEnum::MULTILINESTRING);
        $spatial->method('hasM')->willReturn(false);

        $this->expectException(UnsupportedSpatialInterfaceException::class);
        (new Writer(new GeoJsonStrategy()))->convert($spatial);
    }

    /** Reject a declared MultiPoint without its corresponding interface. */
    public function testUnsupportedMultiPointInterfaceThroughWriter(): void
    {
        $spatial = static::createStub(SpatialInterface::class);
        $spatial->method('getType')->willReturn(GeometryTypeEnum::MULTIPOINT);
        $spatial->method('hasM')->willReturn(false);

        $this->expectException(UnsupportedSpatialInterfaceException::class);
        (new Writer(new GeoJsonStrategy()))->convert($spatial);
    }

    /** Reject a declared Point without its interface through the public writer. */
    public function testUnsupportedPointInterfaceThroughWriter(): void
    {
        $spatial = static::createStub(SpatialInterface::class);
        $spatial->method('getType')->willReturn(GeometryTypeEnum::POINT);
        $spatial->method('hasM')->willReturn(false);
        $writer = new Writer(new GeoJsonStrategy());

        $this->expectException(UnsupportedSpatialInterfaceException::class);
        $writer->convert($spatial);
    }

    /** Preserve the approved public exception inheritance and constructor contract. */
    public function testUnsupportedSpatialTypeExceptionContract(): void
    {
        $previous = new \RuntimeException('Original failure');
        $exception = new UnsupportedSpatialTypeException('Failure', 12, $previous);

        $parent = (new \ReflectionClass($exception))->getParentClass();
        static::assertNotFalse($parent);
        static::assertSame(\Exception::class, $parent->getName());
        static::assertContains(ExceptionInterface::class, (new \ReflectionClass($exception))->getInterfaceNames());
        static::assertSame('Failure', $exception->getMessage());
        static::assertSame(12, $exception->getCode());
        static::assertSame($previous, $exception->getPrevious());
    }
}
