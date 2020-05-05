<?php
defined('BASEPATH') OR exit('No direct script access allowed');
if(! function_exists('to_under_score')){
    function to_under_score($str)
    {
            $dstr = preg_replace_callback('/([A-Z]+)/',function($matchs)
            {
                return '_'.strtolower($matchs[0]);
            },$str);
            return trim(preg_replace('/_{2,}/','_',$dstr),'_');
    }
}
if(! function_exists('to_camel_case')){
    //下划线命名到驼峰命名
    function to_camel_case($str)
    {
        $array = explode('_', $str);
        $result = $array[0];
        $len=count($array);
        if($len>1)
        {
            for($i=1;$i<$len;$i++)
            {
                $result.= ucfirst($array[$i]);
            }
        }
        return $result;
    }
}

if ( ! function_exists('uuid')){
    function  uuid()
    {
        $chars = md5(uniqid(mt_rand(), true));
        $uuid = substr ( $chars, 0, 8 ) . '-'
            . substr ( $chars, 8, 4 ) . '-'
            . substr ( $chars, 12, 4 ) . '-'
            . substr ( $chars, 16, 4 ) . '-'
            . substr ( $chars, 20, 12 );
        return $uuid ;
    }
}
/**
 * 获取图片的Base64编码(不支持url)
 * @date 2017-02-20 19:41:22
 *
 * @param $img_file 传入本地图片地址
 *
 * @return string
 */
function img_to_base64($img_file) {

    $img_base64 = '';
    if (file_exists($img_file)) {
        $app_img_file = $img_file; // 图片路径
        $img_info = getimagesize($app_img_file); // 取得图片的大小，类型等

        //echo '<pre>' . print_r($img_info, true) . '</pre><br>';
        $fp = fopen($app_img_file, "r"); // 图片是否可读权限

        if ($fp) {
            $filesize = filesize($app_img_file);
            $content = fread($fp, $filesize);
            $file_content = chunk_split(base64_encode($content)); // base64编码
            $img_type = 'jpg';
            switch ($img_info[2]) {           //判读图片类型
                case 1: $img_type = "gif";
                    break;
                case 2: $img_type = "jpg";
                    break;
                case 3: $img_type = "png";
                    break;
            }

            $img_base64 = 'data:image/' . $img_type . ';base64,' . $file_content;

        }
        fclose($fp);
    }

    return $img_base64; //返回图片的base64
}
if ( ! function_exists('guid')){
    function guid(){
        mt_srand((double) microtime() * 10000); //optional for php 4.2.0 and up.
        $charid = strtoupper(md5(uniqid(rand(), true)));
        $hyphen = chr(45); // “-”
        $uuid = chr(123)// “{”
        . substr($charid, 0, 8) . $hyphen
        . substr($charid, 8, 4) . $hyphen
        . substr($charid, 12, 4) . $hyphen
        . substr($charid, 16, 4) . $hyphen
        . substr($charid, 20, 12)
        . chr(125); // “}”
        return $uuid;
    }
}
if( !function_exists('get_img_url')){
    function get_img_url($uri){
        if(!$uri){
            return '';
        }
        if($url_info = parse_url($uri)){
            if(isset($url_info['scheme'])){
                return $uri;
            }
        }
        $CI = &get_instance();
        $CI->load->config("uri_config");
        $file_show = config_item('file_show');
        $json = file_get_contents($file_show.'?uri='.$uri);
        $data = json_decode($json,true);
        if(isset($data['code'])&&($data['code']===0||$data['code']==='0')){
            return $data['data']['url'];
        }else{
            return '';
        }
    }
}
