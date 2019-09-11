<?php
/**
 * This file is part of the SalesForceRest package.
 *
 * (c) Alessandro Orrù <alessandro.orru@aleostudio.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace AleoStudio\SalesForceRest\Exceptions;

use \Exception;


class SalesForceException extends Exception
{
    /**
     * Constructor.
     *
     * @param string          $unit
     * @param int             $code
     * @param \Exception|null $previous
     */
    public function __construct(string $unit, int $code = 0, Exception $previous = null)
    {
        parent::__construct("Bad comparison unit: '$unit'", $code, $previous);
    }
}
