<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Authorization extends MY_Model{
    protected $table_name = '';
    protected $child_resource_model=['user'];



    public function __construct()
    {
        parent::__construct();
    }
    public function create($req){
        $ex_data = func_get_arg(0);
        $auth_key = func_get_arg(1);
        //生成一个JWT票据
        $header = [
            "typ"=>"JWT",//类型
            "alg"=> "HS256"//加密方式
        ];//目前只支持这种方式
        $a_header = base64_encode(json_encode($header));

        $aud = isset($_SERVER['REMOTE_HOST'])?$_SERVER['REMOTE_HOST']:$_SERVER['HTTP_HOST'];
        $time = time();
        $payload = [
            'iss'=>"Banana Pay",//签发者 目前写死
            "exp"=>$time+24*60*60,//到期时间
            "iat"=>$time,//签发时间
            "aud"=>$aud,//接收JWT的domain 域名
        ];
        $payload = array_merge($payload,$ex_data);
        ksort($payload);

        $a_payload = base64_encode(json_encode($payload));
        $sign = hash_hmac('sha256',$a_header.'.'.$a_payload,$auth_key);
        return $a_header.'.'.$a_payload.'.'.$sign;
    }
    public function checkAuth($authorization,$auth_key){
        list($a_header,$a_payload,$a_sign) = explode('.',$authorization);
        //spid
        if(!$payload = base64_decode($a_payload,true)){
            $this->_setError(1000,'jwt error');
            return false;
        }

        $payload_obj = @json_decode($payload);
        if(!isset($payload_obj->uid)||empty($payload_obj->uid)){
            $this->_setError(1000,'object user error');
            return false;
        }
        if($payload_obj->exp<=time()){
            $this->_setError(1000,'authorization expired');
            return false;
        }

        //校验域名
        $host = isset($_SERVER['REMOTE_HOST'])?$_SERVER['REMOTE_HOST']:$_SERVER['HTTP_HOST'];
        if($host!==$payload_obj->aud){
            $this->_setError(1000,'domain error');
            return false;
        }
        $sign = hash_hmac('sha256', $a_header.'.'.$a_payload, $auth_key);
        if($sign!==$a_sign){
            $this->_setError(1000,'authorization error');
            return false;
        }
        return $authorization;
    }

}