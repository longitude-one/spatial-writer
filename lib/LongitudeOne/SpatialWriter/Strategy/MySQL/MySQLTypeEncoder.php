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

use LongitudeOne\Core\Enum\GeometryTypeEnum;
use LongitudeOne\SpatialTypes\Interfaces\SpatialInterface;
use LongitudeOne\SpatialWriter\Exception\UnsupportedDimensionException;
use LongitudeOne\SpatialWriter\Exception\UnsupportedSpatialTypeException;

/**
 * Encodes MySQL geometry type identifiers.
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
            GeometryTypeEnum::POINT => pack('L', 1),
            GeometryTypeEnum::LINESTRING => pack('L', 2),
            GeometryTypeEnum::POLYGON => pack('L', 3),
            GeometryTypeEnum::MULTIPOINT => pack('L', 4),
            GeometryTypeEnum::MULTILINESTRING => pack('L', 5),
            GeometryTypeEnum::MULTIPOLYGON => pack('L', 6),
            GeometryTypeEnum::GEOMETRYCOLLECTION => pack('L', 7),
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
