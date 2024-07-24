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

namespace LongitudeOne\BinaryWriter\Exception;

/**
 * Unsupported (M or/and Z) dimensions exception.
 *
 * This exception is thrown when developers try to convert a spatial interface with more than 2 dimensions
 * into MySQL format. MySQL does not support Z nor M dimensions yet.
 */
class UnsupportedDimensionException extends \InvalidArgumentException implements ExceptionInterface
{
}
