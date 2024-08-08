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

namespace LongitudeOne\SpatialWriter\Helper;

use LongitudeOne\SpatialWriter\Exception\UnavailableResourceException;

class SridLoader
{
    private const SRID_XY_FILE = __DIR__.'/../Resources/srs_id_XY.csv';
    private const SRID_YX_FILE = __DIR__.'/../Resources/srs_id_YX.csv';

    /**
     * Load SRID values for which the axis order is XY.
     *
     * @return int[] SRID values for which the axis order is XY
     */
    public function loadSridAlphabeticOrder(): array
    {
        return $this->loadSridFromFile(self::SRID_XY_FILE);
    }

    /**
     * Load SRID values for which the axis order is YX.
     *
     * @return int[] SRID values for which the axis order is YX
     */
    public function loadSridReverseAlphabeticOrder(): array
    {
        return $this->loadSridFromFile(self::SRID_YX_FILE);
    }

    /**
     * Load SRID values from a file.
     *
     * @param string $filename the file to load
     *
     * @return int[] SRID values
     *
     * @throws UnavailableResourceException when the file does not exist
     */
    private function loadSridFromFile(string $filename): array
    {
        if (!file_exists($filename)) {
            throw new UnavailableResourceException(sprintf('The file %s does not exist.', $filename));
        }

        if (!is_readable($filename)) {
            throw new UnavailableResourceException(sprintf('The file %s is not readable.', $filename));
        }

        $file = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if (false === $file) {
            throw new UnavailableResourceException(sprintf('The file %s is not readable.', $filename));
        }

        return array_map('intval', $file ?: []);
    }
}
