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

namespace LongitudeOne\SpatialWriter\Strategy\Ewkb;

use LongitudeOne\Core\Enum\GeometryTypeEnum;
use LongitudeOne\SpatialTypes\Interfaces\SpatialInterface;
use LongitudeOne\SpatialWriter\Exception\UnsupportedSpatialTypeException;

/**
 * Encodes PostGIS EWKB type identifiers, dimension flags and optional SRID flag.
 */
class EwkbTypeEncoder
{
    /** Base geometry identifiers, before EWKB flags. */
    private const array TYPE_CODES = [
        GeometryTypeEnum::POINT->value => 1,
        GeometryTypeEnum::LINESTRING->value => 2,
        GeometryTypeEnum::POLYGON->value => 3,
        GeometryTypeEnum::MULTIPOINT->value => 4,
        GeometryTypeEnum::MULTILINESTRING->value => 5,
        GeometryTypeEnum::MULTIPOLYGON->value => 6,
        GeometryTypeEnum::GEOMETRYCOLLECTION->value => 7,
        GeometryTypeEnum::POLYHEDRALSURFACE->value => 15,
        GeometryTypeEnum::TRIANGLE->value => 17,
    ];

    /**
     * Write a little-endian type identifier, with EWKB Z/M/SRID flags.
     *
     * @param SpatialInterface $spatial     the spatial object to encode
     * @param bool             $includeSrid whether the header includes an SRID
     *
     * @throws UnsupportedSpatialTypeException when no implementation exists for the type
     */
    public function writeType(SpatialInterface $spatial, bool $includeSrid): string
    {
        $type = self::TYPE_CODES[$spatial->getType()->value]
            ?? throw new UnsupportedSpatialTypeException(sprintf('EWKB adapter does not support spatial type %s', $spatial->getType()->value));
        $dimension = ($spatial->hasZ() ? 0x80000000 : 0) | ($spatial->hasM() ? 0x40000000 : 0) | ($includeSrid ? 0x20000000 : 0);

        return pack('V', $type | $dimension);
    }
}
