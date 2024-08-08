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

namespace LongitudeOne\SpatialWriter\Helper;

/**
 * Each Spatial Reference System (SRS) is defined by its SRID and its AXIS ORDER for Geography.
 *
 * This helper class provides a method to get the axis order for a given SRID.
 */
class SpatialReferenceHelper
{
    /**
     * Default axis order: XY.
     */
    public static AxisOrderEnum $defaultAxisOrder = AxisOrderEnum::XY;

    /**
     * @var array<int> SRID values for which the axis order is YX
     */
    public static array $xySrids = [];

    /**
     * @var array<int> SRID values for which the axis order is XY
     */
    public static array $yxSrids = [];

    /**
     * Get the axis order for a given SRID.
     *
     * @param ?int $srid Spatial Reference System Identifier
     */
    public static function getAxisOrder(?int $srid): AxisOrderEnum
    {
        if (null === $srid) {
            return self::$defaultAxisOrder;
        }

        if (empty(self::$xySrids) || empty(self::$yxSrids)) {
            $loader = new SridLoader();
            self::$xySrids = $loader->loadSridAlphabeticOrder();
            self::$yxSrids = $loader->loadSridReverseAlphabeticOrder();
        }

        if (in_array($srid, self::$xySrids)) {
            return AxisOrderEnum::XY;
        }

        if (in_array($srid, self::$yxSrids)) {
            return AxisOrderEnum::YX;
        }

        return self::$defaultAxisOrder;
    }
}
