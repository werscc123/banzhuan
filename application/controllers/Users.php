<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Users extends MY_Controller{
    protected $is_auth = false;
    protected $resource_model = 'user';
    protected $req =[
        'max_point'=>'',
        'login_name'=>'',
        'password'=>''
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
    public function checkParams(){
        if(!parent::checkParams()){
            return false;
        }
        if($this->current_method==='create'){
            //查询是否email已注册
            if($user = $this->user->getByEmail($this->req['login_name'])){
                $this->setError(1001,'邮箱已注册');
                return false;
            }
        }
        return true;
    }
    public function gets(){
        //校验权限
        if(!$this->checkAuth()){
            $this->setError(1003,'授权失败,没有权限',403);
            return false;
        }

        //返回用户信息
        $data =[
            'uid'=>intval($this->user_info['id']),
            'name'=>$this->user_info['name'],
            'avatar'=>$this->user_info['avatar'],
            'email'=>$this->user_info['email'],
            'point'=>$this->user_info['point']?intval($this->user_info['point']):0,
            'point_time'=>$this->user_info['point_time']?intval($this->user_info['point_time']):0,
            'integral'=>$this->user_info['integral']?intval($this->user_info['integral']):0
        ];
        $this->display('json',$data);
    }

    public function modifys(){
        if(!$this->checkAuth()){
            $this->setError(1003,'授权失败,没有权限',403);
            return false;
        }
        if(isset($this->req['max_point'])&&!empty($this->req['max_point'])){
            if(!is_numeric($this->req['max_point'])){
                $this->setError(1001,'参数错误，只能是整数');
                return false;
            }
        }
        $this->user->setPrimary($this->user_info['id']);
        $data['point'] = $this->req['max_point'];
        $data['point_time'] = time();
        $this->user->modify($data);
    }
}