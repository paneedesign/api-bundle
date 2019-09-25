<?php

declare(strict_types=1);
/**
 * User: Fabiano Roberto <fabiano.roberto@ped.technology>
 * Date: 27/05/16
 * Time: 10:36
 */

namespace PaneeDesign\ApiBundle\Exception;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class JsonException extends \Exception implements JsonExceptionInterface
{
    /**
     * JsonException constructor.
     *
     * @param string         $message
     * @param int            $code
     * @param Exception|null $previous
     */
    public function __construct($message = '', $code = Response::HTTP_INTERNAL_SERVER_ERROR, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
