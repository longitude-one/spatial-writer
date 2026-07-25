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

namespace LongitudeOne\SpatialWriter\Tests\Unit\Helper;

use LongitudeOne\SpatialWriter\Helper\SridLoader;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \LongitudeOne\SpatialWriter\Helper\SridLoader
 */
class SridLoaderTest extends TestCase
{
    /**
     * @var SridLoader srid loader to test
     */
    private SridLoader $sridLoader;

    /**
     * Set up the SRID loader.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->sridLoader = new SridLoader();
    }

    /**
     * Unset the SRID loader.
     */
    protected function tearDown(): void
    {
        unset($this->sridLoader);
        parent::tearDown();
    }

    /**
     * Test the loadSridXY method.
     */
    public function testLoadSridAlphabeticOrder(): void
    {
        $srids = $this->sridLoader->loadSridAlphabeticOrder();

        static::assertNotEmpty($srids);
        foreach ($srids as $srid) {
            static::assertNotEmpty($srid);
        }
    }

    /**
     * Test the loadSridYX method.
     */
    public function testLoadSridReverseAlphabeticOrder(): void
    {
        $srids = $this->sridLoader->loadSridReverseAlphabeticOrder();

        static::assertNotEmpty($srids);
        foreach ($srids as $srid) {
            static::assertNotEmpty($srid);
        }
    }
}
