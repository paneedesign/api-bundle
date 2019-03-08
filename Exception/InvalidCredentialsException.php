<?php

declare(strict_types=1);

/**
 * User: Fabiano Roberto <fabiano@paneedesign.com>
 * Date: 26/02/19
 * Time: 15:00.
 */

namespace PaneeDesign\ApiBundle\Exception;

class InvalidCredentialsException extends \Exception
{
    public function __construct()
    {
        parent::__construct('Bad credentials');
    }
}
