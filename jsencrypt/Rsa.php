<?php
class Rsa{
    protected $public_key;
    protected $private_key;
    protected $config = [
        "private_key_bits" => 1024,           //字节数  512 1024 2048  4096 等
        "private_key_type" => OPENSSL_KEYTYPE_RSA   //加密类型
    ];

    public function __construct(){
        $this->generateKeys();
    }


    /**
     * 设置密钥的配置
     * @param $config
     */
    public function setConfig($config){
        $this->config = $config;
    }

    /**
     * 生成密钥对
     */
    public function generateKeys(){
        $res = openssl_pkey_new($this->config);
        openssl_pkey_export($res, $this->private_key);
        $this->public_key = openssl_pkey_get_details($res)["key"];
    }

    /**
     * 设置公钥
     * @param $public_key
     */
    public function setPublicKey($public_key){
        $this->public_key = $public_key;
    }

    /**
     * 设置私钥
     * @param $private_key
     */
    public function setPrivateKey($private_key){
        $this->private_key = $private_key;
    }

    public function isBadPublicKey($key){
        return openssl_pkey_get_public($key);
    }

    public function isBadPrivateKey($key){
        return openssl_pkey_get_private($key);
    }

    /**
     * 公钥加密数据
     * @param $str
     * @return string
     */
    public function publicEncrypt($str){
        openssl_public_encrypt($str,$encrpted,$this->public_key);
        return base64_encode($encrpted);
    }

    /**
     * 私钥解密数据
     * @param $str
     * @return mixed
     */
    public function privateDecrypt($str){
        openssl_private_decrypt(base64_decode($str),$res,$this->private_key);
        return $res;
    }


    /**
     * 私钥加密数据
     * @param $str
     * @return string
     */
    public function privateEncrypt($str){
        openssl_private_encrypt($str,$encrypted,$this->private_key);
        return base64_encode($encrypted);
    }


    /**
     * 公钥解密数据
     * @param $str
     * @return mixed
     */
    public function publicDecrypt($str){
        openssl_public_decrypt(base64_decode($str),$res,$this->public_key);
        return $res;
    }

    /**
     * 返回签名
     * @param $data
     * @return mixed
     */
    public function sign($data){
        openssl_sign($data,$signature,$this->private_key);
        return base64_encode($signature);
    }

    /**
     * 验证签名和数据是否一致
     * @param $data
     * @param $signature
     * @return int
     */
    public function verify($data,$signature){
        return openssl_verify($data,base64_decode($signature),$this->public_key);
    }


    /**
     * 打印密钥信息
     */
    public function printKeys(){
        echo "<pre>";
        print_r(['public_key'=>$this->public_key,'private_key'=>$this->private_key]);
        echo "</pre>";
    }
}