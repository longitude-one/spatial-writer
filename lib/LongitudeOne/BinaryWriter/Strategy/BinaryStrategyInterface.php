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

namespace LongitudeOne\BinaryWriter\Strategy;

use LongitudeOne\SpatialTypes\Interfaces\SpatialInterface;

/**
 * This interface implements the strategy pattern.
 * It is used to convert a spatial interface to a binary string.
 * The strategy is implemented by each class implementing the current interface.
 */
interface BinaryStrategyInterface
{
    /**
     * Convert a spatial interface to another representation.
     *
     * @param SpatialInterface $spatial the spatial interface to convert
     *
     * @return string a string representing the spatial interface in the implemented representation
     */
    public function executeStrategy(SpatialInterface $spatial): string;
}
