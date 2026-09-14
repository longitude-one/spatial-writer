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

namespace LongitudeOne\SpatialWriter\Tests\Unit\Strategy\Ewkb;

use LongitudeOne\Core\Enum\GeometryTypeEnum;
use LongitudeOne\SpatialTypes\Interfaces\SpatialInterface;
use LongitudeOne\SpatialTypes\Types\Dimension4zm\Geometry\GeometryCollection;
use LongitudeOne\SpatialTypes\Types\Dimension4zm\Geometry\LineString;
use LongitudeOne\SpatialTypes\Types\Dimension4zm\Geometry\Point;
use LongitudeOne\SpatialTypes\Types\Dimension4zm\Geometry\Triangle;
use LongitudeOne\SpatialWriter\Exception\UnsupportedSpatialInterfaceException;
use LongitudeOne\SpatialWriter\Exception\UnsupportedSpatialTypeException;
use LongitudeOne\SpatialWriter\Strategy\Ewkb\EwkbTypeEncoder;
use LongitudeOne\SpatialWriter\Strategy\EwkbBinaryStrategy;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Regression tests for nested EWKB and unsupported spatial contracts.
 *
 * @internal
 */
#[CoversClass(EwkbBinaryStrategy::class)]
#[CoversClass(EwkbTypeEncoder::class)]
class EwkbRegressionTest extends TestCase
{
    /** Preserve nested headers, empty members and all ordinates, writing the SRID only at the root. */
    public function testNestedCollectionWithEmptyMembers(): void
    {
        $collection = new GeometryCollection(4326, [
            new Point(srid: 4326),
            new GeometryCollection(4326, [
                new Point(1, 2, 3, 4, 4326),
                new LineString([], 4326),
            ]),
            new Triangle([], 4326),
        ]);

        // Reference geometry in WKT: GEOMETRYCOLLECTION ZM (POINT ZM EMPTY, GEOMETRYCOLLECTION ZM (POINT ZM (1 2 3 4), LINESTRING ZM EMPTY), TRIANGLE ZM EMPTY)
        static::assertSame(
            '01070000e0e61000000300000001010000c0000000000000f87f000000000000f87f000000000000f87f000000000000f87f01070000c00200000001010000c0000000000000f03f00000000000000400000000000000840000000000000104001020000c00000000001110000c000000000',
            bin2hex((new EwkbBinaryStrategy())->executeStrategy($collection))
        );
    }

    /** Reject an unsupported interface rather than emit a partial geometry. */
    public function testUnsupportedInterface(): void
    {
        $spatial = static::createStub(SpatialInterface::class);
        $spatial->method('getType')->willReturn(GeometryTypeEnum::POINT);
        $this->expectException(UnsupportedSpatialInterfaceException::class);
        (new EwkbBinaryStrategy())->executeStrategy($spatial);
    }

    /** Reject enum cases without concrete supported spatial types. */
    public function testUnsupportedType(): void
    {
        $spatial = static::createStub(SpatialInterface::class);
        $spatial->method('getType')->willReturn(GeometryTypeEnum::CIRCULARSTRING);
        $this->expectException(UnsupportedSpatialTypeException::class);
        $this->expectExceptionMessage('EWKB adapter does not support spatial type CircularString');
        (new EwkbBinaryStrategy())->executeStrategy($spatial);
    }

    /** Preserve dimension flags even when the SRID is zero. */
    public function testXyEmptyWithoutSrid(): void
    {
        $point = new \LongitudeOne\SpatialTypes\Types\Dimension2\Geometry\Point(srid: 0);
        static::assertSame(
            '0101000000000000000000f87f000000000000f87f',
            bin2hex((new EwkbBinaryStrategy())->executeStrategy($point))
        );
    }

    /** Preserve dimension flags even when the SRID is zero. */
    public function testXymEmptyWithoutSrid(): void
    {
        $point = new \LongitudeOne\SpatialTypes\Types\Dimension3m\Geometry\Point(srid: 0);
        static::assertSame(
            '0101000040000000000000f87f000000000000f87f000000000000f87f',
            bin2hex((new EwkbBinaryStrategy())->executeStrategy($point))
        );
    }

    /** Preserve dimension flags even when the SRID is zero. */
    public function testXymPointWithoutSrid(): void
    {
        $point = new \LongitudeOne\SpatialTypes\Types\Dimension3m\Geometry\Point(1, 2, 4, 0);
        static::assertSame(
            '0101000040000000000000f03f00000000000000400000000000001040',
            bin2hex((new EwkbBinaryStrategy())->executeStrategy($point))
        );
    }

    /** Preserve dimension flags even when the SRID is zero. */
    public function testXyPointWithoutSrid(): void
    {
        $point = new \LongitudeOne\SpatialTypes\Types\Dimension2\Geometry\Point(1, 2, 0);
        static::assertSame(
            '0101000000000000000000f03f0000000000000040',
            bin2hex((new EwkbBinaryStrategy())->executeStrategy($point))
        );
    }

    /** Preserve dimension flags even when the SRID is zero. */
    public function testXyzEmptyWithoutSrid(): void
    {
        $point = new \LongitudeOne\SpatialTypes\Types\Dimension3z\Geometry\Point(srid: 0);
        static::assertSame(
            '0101000080000000000000f87f000000000000f87f000000000000f87f',
            bin2hex((new EwkbBinaryStrategy())->executeStrategy($point))
        );
    }

    /** Preserve dimension flags even when the SRID is zero. */
    public function testXyzmEmptyWithoutSrid(): void
    {
        $point = new Point(srid: 0);
        static::assertSame(
            '01010000c0000000000000f87f000000000000f87f000000000000f87f000000000000f87f',
            bin2hex((new EwkbBinaryStrategy())->executeStrategy($point))
        );
    }

    /** Preserve dimension flags even when the SRID is zero. */
    public function testXyzmPointWithoutSrid(): void
    {
        $point = new Point(1, 2, 3, 4, 0);
        static::assertSame(
            '01010000c0000000000000f03f000000000000004000000000000008400000000000001040',
            bin2hex((new EwkbBinaryStrategy())->executeStrategy($point))
        );
    }

    /** Preserve dimension flags even when the SRID is zero. */
    public function testXyzPointWithoutSrid(): void
    {
        $point = new \LongitudeOne\SpatialTypes\Types\Dimension3z\Geometry\Point(1, 2, 3, 0);
        static::assertSame(
            '0101000080000000000000f03f00000000000000400000000000000840',
            bin2hex((new EwkbBinaryStrategy())->executeStrategy($point))
        );
    }
}
