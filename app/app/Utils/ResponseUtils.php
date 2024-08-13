<?php

namespace App\Utils;

class ResponseUtils
{

    public static function makeResponse($status, $message, $data)
    {
        $response = [
            'status'        => $status,
            'message'       => $message,
            'response'      => $data
        ];

        return $response;
    }
}
