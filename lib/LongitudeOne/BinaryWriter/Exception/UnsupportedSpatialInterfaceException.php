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
 * This exception is thrown when the spatial interface is not supported.
 *
 * It should not happen, but it could happen if spatial-interface library is updated.
 *
 * @internal
 */
class UnsupportedSpatialInterfaceException extends \Exception implements ExceptionInterface
{
}
