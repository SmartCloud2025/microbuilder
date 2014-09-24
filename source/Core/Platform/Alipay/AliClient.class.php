<?php
namespace Core\Platform\Alipay;

import_third('aop.AopClient');
class AliClient extends \AopClient {

    public static $aliPublic = <<<'DOC'
-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDDI6d306Q8fIfCOaTXyiUeJHkr
IvYISRcc73s3vF1ZT7XN8RNPwJxo8pWaJMmvyTn9N4HQ632qJBVHf8sxHi/fEsra
prwCtzvzQETrNRwVxLO5jVmRGi60j8Ue1efIlzPXV9je9mkjzOmdssymZkh2QhUr
CmZYI/FCEa3/cNMW0QIDAQAB
-----END PUBLIC KEY-----
DOC;

    private $platform;

    function __construct($platform) {
        $this->platform = $platform;
        $this->appId = $platform['appid'];
    }


    public function  checkSignAndDecrypt($params, $isCheckSign, $isDecrypt) {
        return parent::checkSignAndDecrypt($params, null, null, $isCheckSign, $isDecrypt);
    }

    public function encryptAndSign($bizContent, $isEncrypt, $isSign) {
        return parent::encryptAndSign($bizContent, null, null, 'GBK', $isEncrypt, $isSign);
    }

    function verify($data, $sign, $rsaPublicKeyFilePath) {
        $pubKey = self::$aliPublic;
        $res = openssl_get_publickey($pubKey);
        $result = (bool) openssl_verify($data, base64_decode($sign), $res);
        openssl_free_key($res);
        return $result;
    }

    protected function sign($data) {
        $priKey = $this->platform['private_key'];
        $res = openssl_get_privatekey($priKey);
        openssl_sign($data, $sign, $res);
        openssl_free_key($res);
        $sign = base64_encode($sign);
        return $sign;
    }
}
