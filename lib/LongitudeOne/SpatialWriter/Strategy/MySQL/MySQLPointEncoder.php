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

namespace LongitudeOne\SpatialWriter\Strategy\MySQL;

use LongitudeOne\SpatialTypes\Interfaces\PointInterface;
use LongitudeOne\SpatialWriter\Helper\AxisOrderEnum;
use LongitudeOne\SpatialWriter\Helper\SpatialReferenceHelper;

/**
 * Encodes point coordinates using the axis order required by the SRID.
 */
class MySQLPointEncoder
{
    /**
     * Write point coordinates.
     *
     * @param PointInterface $point the point to write
     * @param null|int       $srid  the SRID of the containing spatial collection
     *
     * @return string a binary string representing ordered coordinates in the internal MySQL storage format
     */
    public function writePoint(PointInterface $point, ?int $srid): string
    {
        return match (SpatialReferenceHelper::getAxisOrder($srid ?? $point->getSrid())) {
            AxisOrderEnum::XY => pack('dd', $point->getX(), $point->getY()),
            AxisOrderEnum::YX => pack('dd', $point->getY(), $point->getX()),
        };
    }
}
