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

use LongitudeOne\SpatialWriter\Strategy\BinaryStrategyInterface;
use LongitudeOne\SpatialTypes\Interfaces\SpatialInterface;

/**
 * Writer class.
 * This class is the main class of the library.
 */
class Writer implements WriterInterface
{
    /**
     * Writer constructor.
     *
     * @param BinaryStrategyInterface $strategy The converter
     */
    public function __construct(private BinaryStrategyInterface $strategy)
    {
    }

    /**
     * Convert a spatial interface to a format specified by the internal adapter.
     *
     * @param SpatialInterface $spatial The spatial interface to convert
     *
     * @return string a binary string representing the spatial interface in the format specified by the internal adapter
     */
    public function convert(SpatialInterface $spatial): string
    {
        return $this->strategy->executeStrategy($spatial);
    }

    /**
     * Get the current strategy.
     */
    public function getStrategy(): BinaryStrategyInterface
    {
        return $this->strategy;
    }

    /**
     * Set a new strategy to use.
     *
     * @param BinaryStrategyInterface $strategy the strategy to use
     *
     * @return static the current instance
     */
    public function setStrategy(BinaryStrategyInterface $strategy): self
    {
        $this->strategy = $strategy;

        return $this;
    }
}
