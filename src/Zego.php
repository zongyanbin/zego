<?php

namespace Zongyanbin\Zego;

use Zongyanbin\Zego\Exceptions\ZegoException;
use Zongyanbin\Zego\Support\ZegoErrorCodes;

class Zego
{
    private static function makeNonce(){
        $nonce = rand();
        return $nonce;
    }

    private static function makeRandomIv($number = 16){
        $str = "0123456789abcdefghijklmnopqrstuvwxyz";

        $result = [];
        $strLen = strlen($str);
        for ($i = 0; $i < $number; $i++ ){
            $result[] = $str[rand(0,$strLen-1)];
        }
        return implode('',$result);
    }

    /**
     * Generate Zego Token
     *
     * @param string $user_id User ID of user
     * @param array $payload Payload data
     *
     * @return string returns Zego Token
     */
    public function generateToken04(string $user_id, array $payload = [])
    {
        $appId = (int) config('zego.app_id');
        $appSecret = config('zego.app_secret');
        $expiry = (int) config('zego.token_expiry');
        if ($appId == 0) {
            throw new ZegoException(ZegoErrorCodes::appIDInvalid);
        }
        if ($user_id == '') {
            throw new ZegoException(ZegoErrorCodes::userIDInvalid);
        }
        if ($expiry <= 0) {
            throw new ZegoException(ZegoErrorCodes::effectiveTimeInSecondsInvalid);
        }
        $keyLen = strlen($appSecret);
        if ($keyLen != 32) {
            throw new ZegoException(ZegoErrorCodes::secretInvalid);
        }

        $forTestNoce = -626114709072274507;//9223372036854775807
        $forTestCreateTime = 1619769776;
        $forTestIv = "exn62lbokoa8n8jp";

        //demo
        //$forTestNoce = 9022377734291506982;
        //$forTestCreateTime = 1619663663;
        //$forTestIv = "forkislbyn0u28qw";

        $testMode = false;

        $timestamp = $testMode ? $forTestCreateTime : time();//-for test +3600 = 1619667263

        $nonce = $testMode ? $forTestNoce : self::makeNonce();

        $data_package = [
            'app_id' => $appId,
            'user_id' => $user_id,
            'nonce' => $nonce,
            'ctime' => $timestamp,
            'expire' => $timestamp + $expiry,
            'payload' => empty($payload) ? '' : json_encode($payload),
        ];

        $cipher = 'aes-128-cbc';
        $plaintext = json_encode($data_package, JSON_BIGINT_AS_STRING);
        switch ($keyLen) {
            case 16:
                $cipher = 'aes-128-cbc';
                break;
            case 24:
                $cipher = 'aes-192-cbc';
                break;
            case 32:
                $cipher = 'aes-256-cbc';
                break;
            default:
                throw new ZegoException(ZegoErrorCodes::secretInvalid);
        }

        $iv = $testMode ? $forTestIv : self::makeRandomIv();
        $encrypted = openssl_encrypt($plaintext, $cipher, $appSecret, OPENSSL_RAW_DATA, $iv);
        //64位有符号整型时间戳-BigEndian + 16位无符号整型iv字节长度计数-BigEndian + iv字符串 + 16位无符号整型aes加密后字符串字节长度计数-BigEndian + aes加密后字符串
        $packData = [
            strlen($iv), $iv, strlen($encrypted), $encrypted
        ];
        $binary = pack('J', $data_package['expire']); //J 无符号长长整型(64位，大端字节序)
        $binary .= pack('na*na*', ...$packData);
        return '04' . base64_encode($binary);
    }
}