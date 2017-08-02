<?php
/**
 * Created by PhpStorm.
 * User: Fabiano Roberto
 * Date: 27/02/17
 * Time: 10:13
 */
namespace PaneeDesign\ApiBundle\Helper;

use PaneeDesign\ApiBundle\Exception\JsonException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ApiHelper
 *
 * @author Fabiano Roberto <fabiano@paneedesign.com>
 * @package PaneeDesign\ApiBundle\Helper
 */
class ApiHelper
{
    const CODE_ERROR     = 500;
    const CODE_SUCCESS   = 200;
    const CODE_DUPLICATE = 409;

    /**
     * Custom Response
     *
     * @param int $httpCode
     * @param int $code
     * @param string $message
     * @param string $description
     *
     * @return Response
     */
    public static function customResponse(
        $httpCode = self::CODE_ERROR,
        $code = self::CODE_ERROR,
        $message = 'Generic Error',
        $description = ''
    ) {
        $payload = [
            'code'        => $code,
            'message'     => $message,
            'description' => $description
        ];

        return new Response(
            \json_encode($payload),
            $httpCode,
            ['Content-Type' => 'application/json']
        );
    }

    /**
     * Success Response
     *
     * @param string|array $data
     * @return null
     */
    public static function successResponse($data = null)
    {
        return $data;
    }

    /**
     * Error Response
     *
     * @param int $httpCode
     * @param string $message
     * @throws JsonException
     */
    public static function errorResponse($httpCode = self::CODE_ERROR, $message = 'Internal Server Error')
    {
        throw new JsonException($message, $httpCode);
    }

    public static function timestamp2datetime($sTimestamp)
    {
        $toReturn = null;

        if ($sTimestamp !== null) {
            $date = new \DateTime();
            $date->setTimestamp($sTimestamp);

            $toReturn = $date->format('Y-m-dTH:i:s');
        }

        return $toReturn;
    }

    public static function formatRequestData($data, $deleteEmptyArrays = false)
    {
        $formattedData = [];

        foreach ($data as $key => $value) {
            if ($key === '_format') {
                continue;
            }

            if ($value === 'true') {
                $formattedData[$key] = true;
            } elseif ($value === 'false') {
                $formattedData[$key] = false;
            } elseif ($value === 'null') {
                $formattedData[$key] = null;
            } elseif ($value === '[]') {
                $formattedData[$key] = [];
            } elseif (is_array($value) === true) {
                $formattedData[$key] = self::formatRequestData($value);
            } else {
                $formattedData[$key] = $value;
            }

            if ($deleteEmptyArrays && empty($formattedData[$key]) && is_array($formattedData[$key])) {
                unset($formattedData[$key]);
            }
        }

        return $formattedData;
    }
}
