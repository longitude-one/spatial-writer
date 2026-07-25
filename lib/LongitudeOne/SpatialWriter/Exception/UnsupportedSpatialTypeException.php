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

namespace LongitudeOne\SpatialWriter\Exception;

/**
 * This exception is thrown when the spatial type is not supported.
 *
 * It should not happen, but it could happen if spatial-interface library is updated.
 *
 * @internal
 */
class UnsupportedSpatialTypeException extends \Exception implements ExceptionInterface
{
}
