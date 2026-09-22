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

use LongitudeOne\Core\Enum\GeometryTypeEnum;
use LongitudeOne\SpatialEncoder\Exception\UnsupportedDimensionException;
use LongitudeOne\SpatialEncoder\Exception\UnsupportedSpatialTypeException;
use LongitudeOne\SpatialTypes\Interfaces\SpatialInterface;

/**
 * Encodes MySQL geometry type identifiers.
 *
 * Use pack('V') for 32-bit integers: MySQL's internal format requires little-endian
 * bytes. pack('L') uses the machine's native byte order and is not portable.
 */
class MySQLTypeEncoder
{
    /**
     * Write the binary MySQL type identifier for a spatial value.
     *
     * @param SpatialInterface $spatial the spatial interface
     *
     * @return string a binary string representing the type in the internal MySQL storage format
     *
     * @throws UnsupportedDimensionException   when coordinates include Z or M
     * @throws UnsupportedSpatialTypeException when the spatial type or empty value is not supported
     */
    public function writeType(SpatialInterface $spatial): string
    {
        $this->validateInput($spatial);

        return match ($spatial->getType()) {
            GeometryTypeEnum::POINT => pack('V', 1),
            GeometryTypeEnum::LINESTRING => pack('V', 2),
            GeometryTypeEnum::POLYGON => pack('V', 3),
            GeometryTypeEnum::MULTIPOINT => pack('V', 4),
            GeometryTypeEnum::MULTILINESTRING => pack('V', 5),
            GeometryTypeEnum::MULTIPOLYGON => pack('V', 6),
            GeometryTypeEnum::GEOMETRYCOLLECTION => pack('V', 7),
            default => throw new UnsupportedSpatialTypeException(sprintf('MySQL adapter does not support spatial type %s', $spatial->getType()->value)),
        };
    }

    /**
     * Validate each geometry before writing its header, including nested members.
     *
     * @param SpatialInterface $spatial the geometry to validate
     *
     * @throws UnsupportedDimensionException   when coordinates include Z or M
     * @throws UnsupportedSpatialTypeException when an empty value is not a collection
     */
    private function validateInput(SpatialInterface $spatial): void
    {
        if ($spatial->hasZ() || $spatial->hasM()) {
            throw new UnsupportedDimensionException('MySQL adapter only supports XY coordinates; Z and M are not supported.');
        }

        if ($spatial->isEmpty() && GeometryTypeEnum::GEOMETRYCOLLECTION !== $spatial->getType()) {
            throw new UnsupportedSpatialTypeException(sprintf('MySQL adapter does not support empty %s; only GeometryCollection may be empty.', $spatial->getType()->value));
        }
    }
}
