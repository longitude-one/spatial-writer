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

namespace LongitudeOne\SpatialWriter\Strategy\MySQL;

/**
 * Encodes the fixed header parts of MySQL's internal geometry format.
 */
class MySQLHeaderEncoder
{
    /**
     * Write the little-endian byte-order marker.
     *
     * @return string a binary string representing the first byte in the internal MySQL storage format
     */
    public function writeByteOrder(): string
    {
        return pack('C', 1);
    }

    /**
     * Write an SRID, using zero when it is absent.
     *
     * @param ?int $srid the spatial interface to write
     *
     * @return string a binary string representing the SRID in the internal MySQL storage format
     */
    public function writeSrid(?int $srid): string
    {
        return pack('L', $srid ?? 0);
    }
}
