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

namespace LongitudeOne\SpatialWriter;

use LongitudeOne\SpatialTypes\Interfaces\SpatialInterface;
use LongitudeOne\SpatialWriter\Strategy\StrategyInterface;

/**
 * Writer class.
 * This class is the main class of the library.
 */
class Writer implements WriterInterface
{
    /**
     * Writer constructor.
     *
     * @param StrategyInterface $strategy The converter
     */
    public function __construct(private StrategyInterface $strategy)
    {
    }

    /**
     * Convert a spatial interface to a format specified by the internal adapter.
     *
     * @param SpatialInterface $spatial The spatial interface to convert
     *
     * @return string a string representing the spatial interface in the format specified by the strategy
     */
    public function convert(SpatialInterface $spatial): string
    {
        return $this->strategy->executeStrategy($spatial);
    }

    /**
     * Get the current strategy.
     */
    public function getStrategy(): StrategyInterface
    {
        return $this->strategy;
    }

    /**
     * Set a new strategy to use.
     *
     * @param StrategyInterface $strategy the strategy to use
     *
     * @return self the current instance
     */
    public function setStrategy(StrategyInterface $strategy): self
    {
        $this->strategy = $strategy;

        return $this;
    }
}
