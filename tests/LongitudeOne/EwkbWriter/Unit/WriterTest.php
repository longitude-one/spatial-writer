<?php
/**
 * This file is part of the ewkb-writer project.
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

namespace LongitudeOne\EwkbWriter\Unit;

use LongitudeOne\EwkbWriter\Strategy\MySQLBinaryStrategy;
use LongitudeOne\EwkbWriter\Strategy\WkbBinaryStrategy;
use LongitudeOne\EwkbWriter\Writer;
use LongitudeOne\Spatial\PHP\Types\Geometry\Point;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \LongitudeOne\EwkbWriter\Writer
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
