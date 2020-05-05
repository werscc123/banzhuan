<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller{
    /** 入参 */
    protected $req;

    /** 出参 */
    private $_result = ['code'=>0,'message'=>'successful'];

    /** 当前用户 */
    protected $user_info;

    /** 是否鉴权 */
    protected $is_auth =true;

    /** 资源 */
    protected $resource_model;

    /** 参数校验规则 */
    protected $rules_config;

    /** 出参格式 */
    protected $result_rules;

    /** 当前method */
    protected $current_method;

    /** 当前票据 */
    protected $str_authorization;

    public function __construct()
    {
        parent::__construct();

        $this->current_method = $this->router->fetch_method();

        if($this->resource_model){
            $this->load->model($this->resource_model,'',true); //自动加载链接资源
        }

        $this->_getParams();//获取参数

        //加载必要库
        $this->load->library('form_validation');

        //校验参数
        if(!$this->checkParams()){
            $this->display();
        };
    }

    protected function checkParams(){
        if($this->is_auth===true){
            //需要鉴权
            if(!$this->checkAuth()){
                $this->setError(1003,'授权失败,没有权限',403);
                return false;
            }
        }
        if(isset($this->rules_config[$this->current_method])){
            $this->form_validation->set_data($this->req);
            $this->form_validation->set_rules($this->rules_config[$this->current_method]);
            $this->form_validation->set_error_delimiters('','');
            if($this->form_validation->run()===false){
                $this->setError(1001,trim($this->form_validation->error_string(),"\n"));
                return false;
            }
    
        }
       
        return true;
    }

    protected function setError($code,$message,$statusCode=406){
        $this->_result['code'] = $code;
        $this->_result['message'] = $message;
        set_status_header($statusCode);
    }

    protected function checkAuth(){
        if(!$this->str_authorization){
            return false;
        }
        $this->load->model('authorization');
        @list($a_header,$a_payload,$a_sign) = explode('.',$this->str_authorization);
        if(!$a_payload){
            return false;
        }
        if(!$payload = base64_decode($a_payload,true)){
            return false;
        }
        $payload_obj = @json_decode($payload);
        $uid = $payload_obj->uid;
        $this->load->model('user');
        $user = $this->user->getById($uid);
       
        $auth_key = $user['ele_jwt'];
        if(!$authorization = $this->authorization->checkAuth($this->str_authorization,$auth_key)){
            return false;
        }
        $this->user_info = $user;
        $this->req['user_info'] = $user;
        header('authorization: '.$authorization);
        return true;
    }

    private function format(&$data = null,$rules = null){
        if($data===null){
            $data = $this->_result;
        }
        if($rules===null){
            if(!isset($this->result_rules[$this->current_method])){
                return true;
            }
            $rules = $this->result_rules[$this->current_method];
        }
        // 根据出参规则
        foreach($data as $key=>$value){
            if(is_array($value)){
                //值是一个数组，进入校验转换格式
                $this->format($value,$rules[$key]);
            }
        }
       
        foreach($rules as $rule_key=>$rule){
            if(in_array($rule,['int','date-time'])){
                //特定需要转换的类型 因为php都是输出字符
                if($rule['format']==='int'){
                    $data[$rules] = intval($data[$rule_key]);
                }
            }
        }
        if(isset($data['code'])){
            $data['code'] = intval($data['code']);
        }
        return true;
    }

    private function _getParams(){

        //接收path 参数 用来设置资源主键
        $resources = $this->uri->segment_array();
        if(isset($resources[2])){
            $this->{$this->resource_model}->setPrimary($resources[2]);
        }
        // 接收query 参数
        $params = $this->input->get();
        $params&&$this->req= array_merge($this->req,$params);
        //接收form_data
        if(in_array($this->input->server('REQUEST_METHOD'),['PUT','PATCH','DELETE'])){
            if(!$params =  $this->input->input_stream()){
                $input_stream = $this->input->raw_input_stream;
                if(!$params = @json_decode($input_stream,true)){
                    preg_match_all('/name="(.*)"\s\s\s\s(\S*)/',$input_stream,$data);
                    if(isset($data[1])){
                        foreach ($data[1] as $k=>$v){
                            if(isset($data[2][$k])){
                                if(isset($this->req['form_data'][$v])){
                                    $params[$v] = $data[2][$k];
                                }
                            }
                        }
                    }
                }
            }
        }else{
            $params = $this->input->post();
        }
        $params&&$this->req= array_merge($this->req,$params);

        //接收header 参数 注入权限
        if(strstr($this->input->get_request_header('authorization'),'Bearer ')){
            $this->str_authorization = substr($this->input->get_request_header('authorization'),strlen('Bearer '));
        }else{
            $this->str_authorization = $this->input->get_request_header('authorization');
        }

    }

    public function _remap($name)
    {
        if(!strstr($name,'get')
            &&!strstr($name,'create')
            &&!strstr($name,'update')
            &&!strstr($name,'modify')
            &&!strstr($name,'delete')){
            show_404();
        }
        if(method_exists($this,$name)){
            $this->$name();
            $this->display();
        }else{
            $this->oper_resource($name);
        }

    }
    private function oper_resource($oper){
        $result = $this->{$this->resource_model}->$oper($this->req);
   
        if(!$result){
            if($this->{$this->resource_model}->hasError()){
                list($code,$message) = $this->{$this->resource_model}->getError();
                $this->setError($code,$message,500);
            }else{
                if($result===false){
                    $this->setError(1000,'system error',500);
                }else{
                    $this->_result  = $result;
                }
            }
        }else{
            if(is_array($result)){
                $this->_result = $result;
            }else{
                if($result===true){
                    $this->_result = [];
                }else{
                    $this->_result = (array)$result;
                }
            }
        }
        //输出前数据格式化
        $this->format();

        $this->display();
    }
    protected function display($data_format='json',$data=null){
        if($data){
            $this->_result = $data;
        }
        echo $this->load->view($data_format,['data'=>$this->_result],true);
        die(0);
    }
}