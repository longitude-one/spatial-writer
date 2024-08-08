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

namespace LongitudeOne\SpatialWriter;

use LongitudeOne\SpatialTypes\Interfaces\SpatialInterface;
use LongitudeOne\SpatialWriter\Strategy\BinaryStrategyInterface;

interface WriterInterface
{
    /**
     * Writer constructor.
     *
     * @param BinaryStrategyInterface $strategy the strategy to use
     */
    public function __construct(BinaryStrategyInterface $strategy);

    /**
     * Convert a spatial interface to another representation.
     *
     * @param SpatialInterface $spatial the spatial interface to convert
     *
     * @return string a string representing the spatial interface in the implemented representation
     */
    public function convert(SpatialInterface $spatial): string;

    /**
     * Get the current strategy.
     *
     * @return BinaryStrategyInterface the strategy to use
     */
    public function getStrategy(): BinaryStrategyInterface;

    /**
     * Set the strategy to use.
     *
     * @param BinaryStrategyInterface $strategy the strategy to use
     *
     * @return static the current instance
     */
    public function setStrategy(BinaryStrategyInterface $strategy): self;
}
