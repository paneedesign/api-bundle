<?php
/**
 * Created by PhpStorm.
 * User: fabianoroberto
 * Date: 27/05/16
 * Time: 10:36
 */

namespace PaneeDesign\ApiBundle\Exception;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class JsonException extends \Exception implements JsonExceptionInterface
{

	public function __construct($message = "", $code = Response::HTTP_INTERNAL_SERVER_ERROR, Exception $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}
}