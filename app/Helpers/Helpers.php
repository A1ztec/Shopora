<?php

namespace App\Helpers;

if (!\function_exists('jsonResponse')) {
    function jsonResponse($status, $code = 200, $message = null, $data = null)
    {
        return response()->json([
            'status' => $status,
            'code' => $code,
            'message' => $message,
            'data' => $data
        ]);
    }
}
