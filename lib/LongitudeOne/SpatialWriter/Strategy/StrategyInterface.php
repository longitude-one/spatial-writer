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

namespace LongitudeOne\SpatialWriter\Strategy;

use LongitudeOne\SpatialTypes\Interfaces\SpatialInterface;

/**
 * Converts spatial objects to a binary or textual representation.
 */
interface StrategyInterface
{
    /**
     * Convert a spatial object to the strategy's output format.
     *
     * @param SpatialInterface $spatial the spatial object to convert
     */
    public function executeStrategy(SpatialInterface $spatial): string;
}
