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
use LongitudeOne\SpatialWriter\Exception\UnsupportedSpatialInterfaceException;
use LongitudeOne\SpatialWriter\Exception\UnsupportedSpatialTypeException;
use LongitudeOne\SpatialWriter\Strategy\MySQL\MySQLGeometryEncoder;
use LongitudeOne\SpatialWriter\Strategy\MySQL\MySQLHeaderEncoder;
use LongitudeOne\SpatialWriter\Strategy\MySQL\MySQLPointEncoder;
use LongitudeOne\SpatialWriter\Strategy\MySQL\MySQLTypeEncoder;

/**
 * MySQL adapter.
 *
 * This class is responsible for converting a spatial interface to the internal MySQL storage format.
 */
class MySQLBinaryStrategy implements BinaryStrategyInterface
{
    /**
     * @var MySQLGeometryEncoder encoder for MySQL geometry coordinate payloads
     */
    private MySQLGeometryEncoder $geometryEncoder;

    /**
     * @var MySQLHeaderEncoder encoder for MySQL binary headers
     */
    private MySQLHeaderEncoder $headerEncoder;

    /**
     * @var MySQLTypeEncoder encoder for MySQL geometry types
     */
    private MySQLTypeEncoder $typeEncoder;

    /**
     * Create a MySQL binary strategy.
     *
     * The optional encoders keep the strategy backward compatible with its former no-argument constructor,
     * while allowing individual encoders to be supplied by an application.
     */
    public function __construct()
    {
        $this->headerEncoder = new MySQLHeaderEncoder();
        $this->typeEncoder = new MySQLTypeEncoder();
        $this->geometryEncoder = new MySQLGeometryEncoder(
            $this->headerEncoder,
            $this->typeEncoder,
            new MySQLPointEncoder(),
        );
    }

    /**
     * Convert a spatial interface to the internal MySQL storage format.
     *
     * @param SpatialInterface $spatial the spatial interface to convert
     *
     * @return string a binary string representing the spatial interface in the internal MySQL storage format
     *
     * @throws UnsupportedSpatialInterfaceException when the spatial interface is not supported
     * @throws UnsupportedSpatialTypeException      when the spatial type is not supported
     */
    public function executeStrategy(SpatialInterface $spatial): string
    {
        return $this->headerEncoder->writeSrid($spatial->getSrid())
            .$this->headerEncoder->writeByteOrder()
            .$this->typeEncoder->writeType($spatial)
            .$this->geometryEncoder->writeCoordinates($spatial);
    }
}
