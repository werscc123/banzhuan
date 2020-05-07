<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User extends MY_Model{
    protected $table_name = 'user';
    protected $display_primary_name = 'uid';
    public function __construct()
    {
        parent::__construct();
    }

    public function create($req){
        //随机头像
        $rand = rand(1,28);
        $load= $this->__get('load');
        $load->helper('url');
        $file_url = site_url('uploads/'.$rand.'.jpg');
        $data['email'] = $req['login_name'];
        $load->helper('string');
        $data['salt']= random_string();
        $data['password'] = md5($req['password'].$data['salt']);
        $data['avatar'] = $file_url;
        if($result = parent::create($data)){
            $this->setPrimary($result['uid']);
            $this->modify(['name'=>'斑专'.str_pad($result['uid'],7,'0',STR_PAD_LEFT)]);
        }else{
            $this->setError(1000,'数据库错误');
            return false;
        }
        return $result;
    }

    public function rankings($req){
        $this->db->reset_query();
        $this->db->from($this->table_name);
        $this->db->order_by('point DESC');
        $this->db->limit($req['limit']);
        $array = $this->db->get()->result_array();
        if(in_array($req['user_info']['id'],array_column($array,'id'))){
            $has_current_user = 1;
        }else{
            $has_current_user =0;
        }
        $current_user_point = $req['user_info']['point'];
        $query = $this->db->query("select count(*) as count from t_user where point>".$current_user_point);
        $row = $query->row();
        $current_user_no = $row->count+1;
        $data = [];
        $i = 1;
        foreach($array as $value){
            $item = [];
            $item['uid'] = $value['id'];
            $item['name'] = $value['name'];
            $item['avatar'] = $value['avatar'];
            $item['point'] = $value['point'];
            $item['time'] = $value['point_time'];
            $item['top'] = $i++;
            $data[] = $item;
        }
        $result ['has_current_user'] = $has_current_user;
        $result['current_user_point'] = $current_user_point;
        $result['current_user_no'] = $current_user_no;
        $result['orders'] = $data;
        return $result;
    }
}