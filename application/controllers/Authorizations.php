<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Authorizations extends MY_Controller{
    protected $is_auth = false;
    protected $resource_model = 'authorization';
    protected $req = [
        'login_name'=>'',
        'password'=>'',
    ];
    protected $rules_config = [
        'create'=>[
            [
                'field'=>'login_name',
                'label'=>'login_name',
                'rules'=>['required','min_length[5]','valid_email'],
                ['required'=>'login name can\'t empty']
            ],
            [
                'field'=>'password',
                'label'=>'password',
                'rules'=>['required','min_length[8]'],
                ['required'=>'password can\'t empty'],
            ],
        ]
    ];
    protected $result_rules =[
        'create'=>[
            'authorization'=>'string',
            'uid'=>'int'
        ]
    ];
    protected function checkParams()
    {
        if(!parent::checkParams()){
            return false;
        }
        $method = $this->router->fetch_method();
        if($method=='create'){//创建时
            //校验login_name 是否存在
            if(!$user = $this->{$this->resource_model}->user->getByEmail($this->req['login_name'])){
                if($this->{$this->resource_model}->user->hasError()){
                    list($code,$message) = $this->{$this->resource_model}->user->getError();
                    $this->setError($code,$message);
                }else{
                    $this->setError(1000,'user not is exists');
                }
                return false;
            }
            //校验密码是否正确 todo 调用 thrift 接口
            if($user['password'] !==md5($this->req['password'].$user['salt'])){
                $this->setError(1001,'password error');
                return false;
            }
        }else if ($method=='delete'){
            if(!$this->{$this->resource_model}->getPrimary()){
                $this->setError(1001,'authorization 参数不存在');
                return false;
            }
        }
        return true;
    }

    public function create(){
        $login_name = $this->req['login_name'];
        //拿到user
        $user = $this->authorization->user->getByEmail($login_name);
        $ex_data = ['uid'=>$user['id'],'name'=>$user['name']];
        $auth_key = md5(time().$user['id']);
        $authorization = $this->authorization->create($ex_data,$auth_key);
        //保存到数据库
        $this->authorization->setPrimary($user['id']);
        $this->authorization->user->modify(['ele_jwt'=>$auth_key]);
        $this->display('json',['authorization'=>$authorization,'uid'=>(int)$user['id']]);
    }

    public function delete(){
        $str_authorization = $this->{$this->resource_model}->getPrimary();
       
        $authorization = urldecode($str_authorization);
        @list($header,$payload,$sign) = @explode('.',$authorization);
        $payload_arr = @json_decode(base64_decode($payload));
         //spid
        if(!$payload_arr){
            $this->setError(1001,'authorization error');
            return false;
        }
        $user = $this->authorization->user->getById($payload_arr->uid);
 
        if(!$user){
            $this->setError(1001,'user not exists');
            return false;
        }
        if(!$this->authorization->checkAuth($authorization,$user['ele_jwt'])){
            $this->setError(1001,'authorization error');
            return false;
        }

        $this->authorization->setPrimary($user['id']);
        if(!$this->authorization->user->modify(['ele_jwt'=>'0'])){
            $this->setError(1000,'network error');
            return false;
        }
        $this->display();
    }
}