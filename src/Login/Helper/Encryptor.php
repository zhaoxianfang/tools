<?php

namespace zxf\Login\Helper;

class Encryptor
{
    /**
     * 解密数据（微信小程序手机号）
     *
     * @return array
     *               array(4) {
     *               ["phoneNumber"]=>
     *               string(11) "1314666****"
     *               ["purePhoneNumber"]=>
     *               string(11) "1314666****"
     *               ["countryCode"]=>
     *               string(2) "86"
     *               ["watermark"]=>
     *               array(2) {
     *               ["timestamp"]=>
     *               int(1732589884)
     *               ["appid"]=>
     *               string(18) "wxb771b4b7fb****"
     *               }
     *               }
     *
     * @throws \Exception
     */
    public static function decryptData(string $sessionKey, string $iv, string $encrypted): array
    {
        $decrypted = AES::decrypt(
            base64_decode($encrypted, false),
            base64_decode($sessionKey, false),
            base64_decode($iv, false)
        );

        $decrypted = json_decode($decrypted, true);

        if (! $decrypted) {
            throw new \Exception('The given payload is invalid.');
        }

        return $decrypted;
    }
}
