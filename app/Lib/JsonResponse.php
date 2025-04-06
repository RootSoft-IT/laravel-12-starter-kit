<?php

namespace App\Lib;

use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class JsonResponse
{
    public static array $statusTexts = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',            // RFC2518
        103 => 'Early Hints',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',          // RFC4918
        208 => 'Already Reported',      // RFC5842
        226 => 'IM Used',               // RFC3229
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',    // RFC7238
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
        418 => 'I\'m a teapot',                                               // RFC2324
        421 => 'Misdirected Request',                                         // RFC7540
        422 => 'Unprocessable Entity',                                        // RFC4918
        423 => 'Locked',                                                      // RFC4918
        424 => 'Failed Dependency',                                           // RFC4918
        425 => 'Too Early',                                                   // RFC-ietf-httpbis-replay-04
        426 => 'Upgrade Required',                                            // RFC2817
        428 => 'Precondition Required',                                       // RFC6585
        429 => 'Too Many Requests',                                           // RFC6585
        431 => 'Request Header Fields Too Large',                             // RFC6585
        451 => 'Unavailable For Legal Reasons',                               // RFC7725
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',                                     // RFC2295
        507 => 'Insufficient Storage',                                        // RFC4918
        508 => 'Loop Detected',                                               // RFC5842
        510 => 'Not Extended',                                                // RFC2774
        511 => 'Network Authentication Required',                             // RFC6585
    ];


    public string $version = 'v1';
    /**
     * @var bool
     */
    private bool $status;
    private string|null $status_msg;
    /**
     * @var mixed|null
     */
    private mixed $message;
    /**
     * @var int
     */
    private int $code;
    private int|null $app_code;
    /**
     * @var array
     */
    private mixed $data;
    /**
     * @var null
     */
    private mixed $to;

    public function __construct(bool $status = true, $message = null, $code = 200, $data = [], $to = null)
    {

        $this->status = $status;
        if (!is_null($message) || empty($message)) {
            $message = str_replace('_', ' ', $message);
        } elseif (array_key_exists($code, self::$statusTexts)) {
            $message = self::$statusTexts[$code];
        }
        $this->message = $message;
        $this->code = $code;
        $this->data = $data;
        $this->to = $to;
    }

    public static function warning($data = [], $msg = 'Success', $to = null, $code = 200, $app_code = null): Response
    {
        $static = new static(true, $msg, $code, $data, $to);
        $static->app_code = $app_code;
        $static->status_msg = 'warning';
        return $static->toResponse();
    }

    public static function success($data = [], $msg = 'Success', $to = null, $code = 200, $app_code = null): Response
    {
        $static = new static(true, $msg, $code, $data, $to);
        $static->app_code = $app_code;
        $static->status_msg = 'success';
        return $static->toResponse();
    }

    public static function error(\Exception|string $message = 'error', $code = 400, array $allow_code = [404, 500, 401, 429], $data = [], $app_code = null): Response
    {
        if ($message instanceof \Exception) {
            $code = $message->getCode();
            $message = $message->getMessage();
        }
        $allow_code = [
            Response::HTTP_UNAUTHORIZED,
            Response::HTTP_FORBIDDEN,
            ...$allow_code
        ];
        if (count($allow_code) > 0 && !in_array($code, $allow_code)) {
            $code = 400;
        }
        $static = new static(false, $message, $code, $data);
        $static->app_code = $app_code;
        $static->status_msg = 'error';
        return $static->toResponse();
    }
    public static function to(bool $status = true, $message = null, $code = 200, $data = [], $to = null): Response
    {

        $static = new static($status, $message, $code, $data, $to);
        return $static->toResponse();
    }

    public function getCode(): int
    {
        if (!$this->app_code) {
            return $this->code;
        }
        return (int)"{$this->code}{$this->app_code}";
    }
    public function toResponse(): Response
    {
        $response = [
            'message' => $this->message,
            'data' => $this->data,
            'version' => $this->version
        ];
        if ($this->to) {
            $response['to'] = $this->to;
        }
        return new Response($response, $this->code);
    }
    public static function validate($attribute, $message): ValidationException
    {
        throw  ValidationException::withMessages([
            $attribute => [$message]
        ]);
    }
}
