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

namespace LongitudeOne\SpatialEncoder\Tests\Unit;

use LongitudeOne\SpatialEncoder\Encoder;
use LongitudeOne\SpatialEncoder\Strategy\EwkbBinaryStrategy;
use LongitudeOne\SpatialEncoder\Strategy\MySQLBinaryStrategy;
use LongitudeOne\SpatialEncoder\Strategy\WkbBinaryStrategy;
use LongitudeOne\SpatialEncoder\Strategy\WktTextStrategy;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geometry\Point;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \LongitudeOne\SpatialEncoder\Encoder
 */
class EncoderTest extends TestCase
{
    /**
     * Test the binary encoder with a mysql strategy.
     */
    public function testBinaryEncoderWithMysqlStrategy(): void
    {
        $strategy = new MySQLBinaryStrategy();
        $encoder = new Encoder($strategy);
        $point = new Point(1, 2, 4326);
        static::assertSame(
            $strategy->encode($point),
            $encoder->encode($point)
        );
    }

    /**
     * Test the binary encoder with the Well-Known Binary strategy.
     */
    public function testBinaryEncoderWithWkbStrategy(): void
    {
        $strategy = new WkbBinaryStrategy();
        $encoder = new Encoder($strategy);
        $point = new Point(1, 2, 4326);
        static::assertSame(
            $strategy->encode($point),
            $encoder->encode($point)
        );
    }

    /**
     * Test the binary encoder with the Well-Known Binary strategy.
     */
    public function testEwkbEncoderWithEwkbStrategy(): void
    {
        $badStrategy = new MySQLBinaryStrategy();
        $strategy = new EwkbBinaryStrategy();
        $encoder = new Encoder($badStrategy);
        static::assertSame($badStrategy, $encoder->getStrategy());
        $encoder->setStrategy($strategy);
        $point = new Point(1, 2, 4326);
        static::assertSame(
            $strategy->encode($point),
            $encoder->encode($point)
        );
        static::assertNotSame(
            $badStrategy->encode($point),
            $encoder->encode($point)
        );
    }

    /** Test text strategies can be supplied and exchanged with binary strategies. */
    public function testTextEncoder(): void
    {
        $point = new Point(1, 2, 4326);
        $textStrategy = new WktTextStrategy();
        $binaryStrategy = new WkbBinaryStrategy();
        $encoder = new Encoder($textStrategy);
        static::assertSame($textStrategy, $encoder->getStrategy());
        static::assertSame('POINT (1 2)', $encoder->encode($point));
        static::assertSame($encoder, $encoder->setStrategy($binaryStrategy));
        static::assertSame($binaryStrategy->encode($point), $encoder->encode($point));
        $encoder->setStrategy($textStrategy);
        static::assertSame('POINT (1 2)', $encoder->encode($point));
    }
}
