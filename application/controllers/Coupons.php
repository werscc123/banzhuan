<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Coupons extends MY_Controller{
    protected $is_auth = true;
    protected $resource_model = 'coupon';

    protected $req = [
        'level'=>'',
    ];
    protected $rules_config = [
        'create'=>[
            [
                'field'=>'level',
                'label'=>'level',
                'rules'=>['required','in_list[1,2,3]'],
                ['required'=>'level can\'t empty']
            ],
        ]
    ];
    
    public function checkParams(){
        if(!parent::checkParams()){
            return false;
        }
        if($this->current_method==='create'){
            //判断用户积分是否足够
            if(($this->req['level']==1&&$this->user_info['integral']<10000)||
            ($this->req['level']==2&&$this->user_info['integral']<2000)
            || ($this->req['level']==3&&$this->user_info['integral']<800)){
                $this->setError(1001,'用户积分不足');
                return false;
            }
        }
        return true;
    }
}