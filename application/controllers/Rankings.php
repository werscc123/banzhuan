<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Rankings extends MY_Controller{
    protected $is_auth = true;
    protected $resource_model = 'user';
    protected $req = [
        'limit'=>'',
    ];
    public function gets(){
        if(!isset($this->req['limit'])||empty($this->req['limit'])){
            $this->req['limit'] = 10;
        }
        $result = $this->user->rankings($this->req);
        $this->display('json',$result);
    }
}