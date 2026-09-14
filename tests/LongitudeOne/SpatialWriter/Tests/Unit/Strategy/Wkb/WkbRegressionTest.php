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

namespace LongitudeOne\SpatialWriter\Tests\Unit\Strategy\Wkb;

use LongitudeOne\Core\Enum\GeometryTypeEnum;
use LongitudeOne\SpatialTypes\Interfaces\SpatialInterface;
use LongitudeOne\SpatialTypes\Types\Dimension4zm\Geometry\GeometryCollection;
use LongitudeOne\SpatialTypes\Types\Dimension4zm\Geometry\LineString;
use LongitudeOne\SpatialTypes\Types\Dimension4zm\Geometry\Point;
use LongitudeOne\SpatialTypes\Types\Dimension4zm\Geometry\Triangle;
use LongitudeOne\SpatialWriter\Exception\UnsupportedSpatialInterfaceException;
use LongitudeOne\SpatialWriter\Exception\UnsupportedSpatialTypeException;
use LongitudeOne\SpatialWriter\Strategy\Wkb\WkbTypeEncoder;
use LongitudeOne\SpatialWriter\Strategy\WkbBinaryStrategy;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Regression tests for nested ISO WKB and unsupported spatial contracts.
 *
 * @internal
 */
#[CoversClass(WkbBinaryStrategy::class)]
#[CoversClass(WkbTypeEncoder::class)]
class WkbRegressionTest extends TestCase
{
    /** Preserve nested headers, empty members and all ordinates, omitting every SRID. */
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
            '01bf0b00000300000001b90b0000000000000000f87f000000000000f87f000000000000f87f000000000000f87f01bf0b00000200000001b90b0000000000000000f03f00000000000000400000000000000840000000000000104001ba0b00000000000001c90b000000000000',
            bin2hex((new WkbBinaryStrategy())->executeStrategy($collection))
        );
    }

    /** Reject an unsupported interface rather than emit a partial geometry. */
    public function testUnsupportedInterface(): void
    {
        $spatial = static::createStub(SpatialInterface::class);
        $spatial->method('getType')->willReturn(GeometryTypeEnum::POINT);
        $this->expectException(UnsupportedSpatialInterfaceException::class);
        (new WkbBinaryStrategy())->executeStrategy($spatial);
    }

    /** Reject enum cases without concrete supported spatial types. */
    public function testUnsupportedType(): void
    {
        $spatial = static::createStub(SpatialInterface::class);
        $spatial->method('getType')->willReturn(GeometryTypeEnum::CIRCULARSTRING);
        $this->expectException(UnsupportedSpatialTypeException::class);
        $this->expectExceptionMessage('WKB adapter does not support spatial type CircularString');
        (new WkbBinaryStrategy())->executeStrategy($spatial);
    }
}
