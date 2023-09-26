<?php
namespace Zongyanbin\Zego\Exceptions;
use Exception;
use Zongyanbin\Zego\Support\ZegoErrorCodes;
class ZegoException extends Exception
{
    public function __construct($code)
    {
        parent::__construct($this->get_message($code), $code);
    }

    private function get_message($code)
    {
        switch ($code) {
            case ZegoErrorCodes::appIDInvalid:
                return 'App ID is invalid';
            case ZegoErrorCodes::userIDInvalid:
                return 'User ID is invalid';
            case ZegoErrorCodes::secretInvalid:
                return 'App Secret must be a 16/24/32 bytes string';
            case ZegoErrorCodes::effectiveTimeInSecondsInvalid:
                return 'Token expiry is invalid';
        }
    }
}