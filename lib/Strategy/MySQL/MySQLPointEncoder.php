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

namespace LongitudeOne\SpatialEncoder\Strategy\MySQL;

use LongitudeOne\SpatialTypes\Interfaces\PointInterface;

/**
 * Encodes X then Y, including longitude then latitude for geographic points.
 */
class MySQLPointEncoder
{
    /**
     * Write point coordinates.
     *
     * @param PointInterface $point the point to write
     *
     * @return string a binary string representing ordered coordinates in the internal MySQL storage format
     */
    public function writePoint(PointInterface $point): string
    {
        return pack('ee', $point->getX(), $point->getY());
    }
}
