<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Coupon extends MY_Model{
    protected $table_name = 'coupon';

    public function gets($req){
        $this->db->from($this->table_name);
        $this->db->where(['uid'=>$req['user_info']['id']]);
        $this->db->order_by('id desc');
        return $this->db->get()->result_array();
    }
    public function create($req){
        $this->db->trans_start();
        if($req['level']==1){
            //金箱子
            $data['number'] = 150;
        }elseif($req['level'==2]){
            $data['number'] = 100;
        }else{
            $data['number'] = 50;
        }

       
        $data['source'] = $req['level'];
        $data['type'] = 1;
        $data['exp'] = time();
        $data['uid'] = $req['user_info']['id'];
        $data['create_time'] = time();
        $this->db->set($data);
        $this->db->insert($this->table_name);
        $this->db->trans_complete();

        switch($req['level']){
            case 1:
                $integral = 10000;
            break;
            case 2:
                $integral = 2000;
            break;
            case 3:
                $integral = 800;
            break;
        }
        $this->db->reset_query();
        //添加积分记录
        $integral_record['value'] = $integral;
        $integral_record['type'] = -1;
        $integral_record['original'] =$req['user_info']['integral'];
        $integral_record['current'] =$req['user_info']['integral'] - $integral;
        $integral_record['source'] = '兑换积分';
        $integral_record['create_time'] = time();
        $integral_record['uid'] = $req['user_info']['id'];
        $this->db->set($integral_record);
        $this->db->insert('integral');

        $this->db->reset_query();
        //修改用户记录
        $user_data['integral'] = $req['user_info']['integral'] - $integral;
        $user_data['update_time'] = time();
        $this->db->set($user_data);
        $this->db->where(['id'=>$req['user_info']['id']]);
        $this->db->update('user');
        if ($this->db->trans_status() === FALSE)
        {
            $this->setError(1000,'数据库操作失败');
        }
        return $data;
    }
}