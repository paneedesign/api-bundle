<?php
/**
 * Created by PhpStorm.
 * User: sergiosicari
 * Date: 27/02/17
 * Time: 10:13
 */

namespace PaneeDesign\ApiBundle\Helper;

class ApiHelper
{
    const CODE_ERROR     = 500;
    const CODE_SUCCESS   = 200;
    const CODE_DUPLICATE = 409;

    const MESSAGE_SUCCESS = 'ok';

    public static function customResponse($code = self::CODE_SUCCESS, $message = self::MESSAGE_SUCCESS, $result = []) {
        $toReturn = array(
            'code'    => $code,
            'message' => $message,
            'result'  => $result,
        );

        return $toReturn;
    }

    public static function successResponse($data = [])
    {
        return self::customResponse(self::CODE_SUCCESS, self::MESSAGE_SUCCESS, $data);
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

    public static function formatRequestData($data, $deleteEmptyArrays = false) {
        $formattedData = [];

        foreach($data as $key => $value) {
            if($key === '_format') {
                continue;
            }

            if($value === 'true') {
                $formattedData[$key] = true;
            } else if($value === 'false') {
                $formattedData[$key] = false;
            } else if($value === 'null') {
                $formattedData[$key] = null;
            } else if($value === '[]') {
                $formattedData[$key] = [];
            } else if(is_array($value) === true) {
                $formattedData[$key] = self::formatRequestData($value);
            } else {
                $formattedData[$key] = $value;
            }

            if ($deleteEmptyArrays && empty($formattedData[$key]) && is_array($formattedData[$key])){
                unset($formattedData[$key]);
            }
        }

        return $formattedData;
    }
}
