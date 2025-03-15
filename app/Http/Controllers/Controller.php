<?php

namespace App\Http\Controllers;

abstract class Controller
{
    protected function success(string $message, $data, int $status = 200)
    {
        return response()->json([
            'message' => $message,
            'data' => $data,
            'errors' => []
        ], $status);
    }

    protected function failed(string $message, $errors, int $status = 500)
    {
        return response()->json([
            'message' => $message,
            'data' => [],
            'errors' => $errors
        ], $status);
    }
}
