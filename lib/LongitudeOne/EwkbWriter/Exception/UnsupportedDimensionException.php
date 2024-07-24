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

namespace LongitudeOne\EwkbWriter\Exception;

use JetBrains\PhpStorm\Pure;

/**
 * Unsupported (M or/and Z) dimensions exception.
 *
 * This exception is thrown when developers try to convert a spatial interface with more than 2 dimensions
 * into MySQL format. MySQL does not support Z nor M dimensions yet.
 */
class UnsupportedDimensionException extends \InvalidArgumentException implements ExceptionInterface
{
    /**
     * UnsupportedDimensionException constructor.
     *
     * @param string          $message  the exception message
     * @param int             $code     the exception code
     * @param null|\Throwable $previous the previous exception
     */
    #[Pure]
    public function __construct(string $message = '', int $code = 0, ?\Throwable $previous = null)
    {
        if (empty($message)) {
            $message = 'MySQL does not support Z nor M dimensions yet.';
        }

        parent::__construct($message, $code, $previous);
    }
}
