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

namespace LongitudeOne\SpatialWriter\Tests\Unit;

use LongitudeOne\SpatialTypes\Types\Geometry\Point;
use LongitudeOne\SpatialWriter\Strategy\MySQLBinaryStrategy;
use LongitudeOne\SpatialWriter\Strategy\WkbBinaryStrategy;
use LongitudeOne\SpatialWriter\Writer;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \LongitudeOne\SpatialWriter\Writer
 */
class WriterTest extends TestCase
{
    /**
     * Test the binary writer with a mysql strategy.
     */
    public function testBinaryWriterWithMysqlStrategy(): void
    {
        $strategy = new MySQLBinaryStrategy();
        $writer = new Writer($strategy);
        $point = (new Point(1, 2))->setSrid(4326);
        static::assertSame(
            $strategy->executeStrategy($point),
            $writer->convert($point)
        );
    }

    /**
     * Test the binary writer with the Well-Known Binary strategy.
     */
    public function testBinaryWriterWithWkbStrategy(): void
    {
        $strategy = new WkbBinaryStrategy();
        $writer = new Writer($strategy);
        $point = (new Point(1, 2))->setSrid(4326);
        static::assertSame(
            $strategy->executeStrategy($point),
            $writer->convert($point)
        );
    }
}
