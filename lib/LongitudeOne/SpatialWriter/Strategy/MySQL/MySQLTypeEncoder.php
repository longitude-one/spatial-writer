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

use LongitudeOne\SpatialTypes\Enum\TypeEnum;
use LongitudeOne\SpatialTypes\Interfaces\SpatialInterface;
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
     * @throws UnsupportedSpatialTypeException when the spatial type is not supported
     */
    public function writeType(SpatialInterface $spatial): string
    {
        return match ($spatial->getType()) {
            TypeEnum::POINT->value => pack('L', 1),
            TypeEnum::LINESTRING->value => pack('L', 2),
            TypeEnum::POLYGON->value => pack('L', 3),
            TypeEnum::MULTIPOINT->value => pack('L', 4),
            TypeEnum::MULTILINESTRING->value => pack('L', 5),
            TypeEnum::MULTIPOLYGON->value => pack('L', 6),
            TypeEnum::COLLECTION->value => pack('L', 7),
            default => throw new UnsupportedSpatialTypeException(sprintf('MySQL adapter does not support spatial type %s', $spatial->getType())),
        };
    }
}
