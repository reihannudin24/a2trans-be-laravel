<?php

namespace App\Helpers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ResponseHelper
{
    public static function errorResponse($status, $message, $redirect = null)
    {
        return Response::json([
            'status' => $status,
            'message' => $message,
            'redirect' => $redirect
        ], $status);
    }

    public static function successResponse($status, $message, $data = [], $redirect = null)
    {
        return Response::json([
            'status' => $status,
            'message' => $message,
            'data' => $data,
            'redirect' => $redirect
        ], $status);
    }
}
