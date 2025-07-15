<?php

namespace App\Exceptions;

use Exception;

class JsonApiException extends Exception
{
    public $response;

    public function __construct(array $response, $code = 400)
    {
        parent::__construct($response['message'] ?? 'Error', $code);
        $this->response = $response;
    }

    public function render($request)
    {
        return response()->json($this->response, $this->getCode());
    }
}
