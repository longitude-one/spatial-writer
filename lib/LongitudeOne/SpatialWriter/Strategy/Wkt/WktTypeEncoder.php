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

namespace LongitudeOne\SpatialWriter\Strategy\Wkt;

use LongitudeOne\Core\Enum\GeometryTypeEnum;
use LongitudeOne\SpatialTypes\Interfaces\SpatialInterface;
use LongitudeOne\SpatialWriter\Exception\UnsupportedSpatialTypeException;

/**
 * Encodes WKT type names and coordinate dimension markers.
 */
class WktTypeEncoder
{
    /**
     * Write the WKT type name and optional Z, M, or ZM marker.
     *
     * @param SpatialInterface $spatial the spatial object to convert
     *
     * @throws UnsupportedSpatialTypeException when the spatial type is not supported
     */
    public function writeType(SpatialInterface $spatial): string
    {
        $type = match ($spatial->getType()) {
            GeometryTypeEnum::POINT,
            GeometryTypeEnum::LINESTRING,
            GeometryTypeEnum::POLYGON,
            GeometryTypeEnum::MULTIPOINT,
            GeometryTypeEnum::MULTILINESTRING,
            GeometryTypeEnum::MULTIPOLYGON,
            GeometryTypeEnum::GEOMETRYCOLLECTION,
            GeometryTypeEnum::TRIANGLE,
            GeometryTypeEnum::POLYHEDRALSURFACE => mb_strtoupper($spatial->getType()->value),
            default => throw new UnsupportedSpatialTypeException(sprintf('WKT adapter does not support spatial type %s', $spatial->getType()->value)),
        };
        $dimension = ($spatial->hasZ() ? 'Z' : '').($spatial->hasM() ? 'M' : '');

        return $type.('' === $dimension ? '' : ' '.$dimension);
    }
}
