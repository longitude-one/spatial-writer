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
 * Encodes spatial objects to a binary or textual representation.
 */
interface StrategyInterface
{
    /**
     * Encode a spatial object to the strategy's output format.
     *
     * @param SpatialInterface $spatial the spatial object to encode
     */
    public function encode(SpatialInterface $spatial): mixed;
}
