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

namespace LongitudeOne\SpatialEncoder\Strategy;

use LongitudeOne\SpatialTypes\Interfaces\SpatialInterface;

/**
 * Extends WKT with an SRID prefix when the spatial reference is nonzero.
 */
class EwktTextStrategy extends WktTextStrategy
{
    /**
     * Write WKT with a single SRID prefix for the outermost geometry.
     *
     * @see https://postgis.net/docs/ST_AsEWKT.html
     *
     * @param SpatialInterface $spatial the spatial object to encode
     */
    public function encode(SpatialInterface $spatial): string
    {
        $wkt = parent::encode($spatial);
        $srid = $spatial->getSrid();

        return 0 === $srid ? $wkt : sprintf('SRID=%d;%s', $srid, $wkt);
    }
}
