<?php

namespace App\Exceptions\Api;

use Exception;

class JsonException extends Exception
{
    private $statusCode;
    private $msg;
    private $link;

    public function __construct(int $statusCode, $msg = null, $link = null)
    {
        $this->statusCode = $statusCode;
        $this->msg = $msg;
        $this->link = $link;
    }

    public function render()
    {
        $description = $this->getStatusDescription($this->statusCode);

        $json = [
            'ok' => false,
            'error' => $this->statusCode,
            'description' => $description,
        ];
        if ($this->msg !== null) {
            $json['message'] = $this->msg;
        }
        if ($this->link !== null) {
            $json['link'] = $this->link;
        }

        return response()->json($json, $this->statusCode);
    }

    private function getStatusDescription(int $code)
    {
        $httpStatuses = [
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            207 => 'Multi-Status',
            208 => 'Already Reported',
            226 => 'IM Used',

            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Payload Too Large',
            414 => 'URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Range Not Satisfiable',
            417 => 'Expectation Failed',
            418 => 'I\'m a teapot',
            421 => 'Misdirected Request',
            422 => 'Unprocessable Entity',
            423 => 'Locked',
            424 => 'Failed Dependency',
            425 => 'Too Early',
            426 => 'Upgrade Required',
            428 => 'Precondition Required',
            429 => 'Too Many Requests',
            431 => 'Request Header Fields Too Large',
            451 => 'Unavailable For Legal Reasons',

            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported',
            506 => 'Variant Also Negotiates',
            507 => 'Insufficient Storage',
            508 => 'Loop Detected',
            510 => 'Not Extended',
            511 => 'Network Authentication Required',
        ];
        return $httpStatuses[$code];
    }
}
