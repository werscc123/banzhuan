<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'gets';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;
$resources = [ //restful 资源配置 配置后自动计算路由 添加新规则路由请在文件末尾添加
    'coupons',
    'authorizations',
    'users',
    'rankings',
    'integrals'
];


function toCamelCase($str)//下划线转驼峰
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
function add_restful_route($resource,$p_resource='',&$route){
    $sub_resource = '';
    $format_p_resource = toCamelCase($p_resource);
    $format_resource = toCamelCase($resource);
    $h_p_resource = '';
    $f_resource = "/$1";//单个
    $f_resources = "";//列表
    if($p_resource){
        $h_p_resource = $p_resource.'/(:any)/';
        $f_resource = "/$1/$2";
        $f_resources = "/$1";
        $format_resource = $format_p_resource;
        $sub_resource = '_'.substr($resource,0,strlen($resource)-1);
    }
    $route["{$h_p_resource}$resource/(:any)"]["GET"] = $format_resource.'/get'.$sub_resource.$f_resource;
    $route["{$h_p_resource}$resource"]['GET'] = $format_resource."/get".$sub_resource."s".$f_resources;
    $route["{$h_p_resource}$resource"]['PATCH'] = $format_resource."/modify".$sub_resource."s".$f_resources;
    $route["{$h_p_resource}$resource"]["POST"] = $format_resource.'/create'.$sub_resource.$f_resources;
    $route["{$h_p_resource}$resource"]["PUT"]=$format_resource."/update".$sub_resource.$f_resources;
    $route["{$h_p_resource}$resource/(:any)"]["DELETE"] = $format_resource.'/delete'.$sub_resource.$f_resource;
    $route["{$h_p_resource}$resource/(:any)"]["PATCH"] = $format_resource."/modify".$sub_resource.$f_resource;
}
foreach ($resources as $r_k=>$r_v){
    if(is_array($r_v)){
        add_restful_route($r_k,'',$route);
        foreach ($r_v as $r_c_v){
            add_restful_route($r_c_v,$r_k,$route);
        }
    }else{
        add_restful_route($r_v,'',$route);
    }
}