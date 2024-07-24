<?php
/**
 * This file is part of the ewkb-writer project.
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

namespace LongitudeOne\EwkbWriter;

use LongitudeOne\EwkbWriter\Strategy\AdapterInterface;
use LongitudeOne\Spatial\PHP\Types\SpatialInterface;

interface WriterInterface
{
    /**
     * Writer constructor.
     *
     * @param AdapterInterface $adapter the strategy to use
     */
    public function __construct(AdapterInterface $adapter);

    /**
     * Convert a spatial interface to another representation.
     *
     * @param SpatialInterface $spatial the spatial interface to convert
     *
     * @return string a string representing the spatial interface in the implemented representation
     */
    public function convert(SpatialInterface $spatial): string;
}
