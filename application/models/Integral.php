<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Integral extends MY_Model{
    protected $table_name = 'integral';

    public function create($req){
        $this->db->trans_start();
        $this->db->set('value',$req['value']);
        $this->db->set('source',$req['source']);
        $this->db->set('type',$req['type']);
        $this->db->set('original',$req['user_info']['integral']);
        if($req['type']=='-1'){
            $current = $req['user_info']['integral']-$req['value'];
        }else{
            $current = $req['user_info']['integral']+$req['value'];
        }
        $this->db->set('current',$current);
        $this->db->set('uid',$req['user_info']['id']);
        $this->db->insert($this->table_name);
        $this->db->reset_query();
        $this->db->set('integral',$current);
        $this->db->where(['id'=>$req['user_info']['id']]);
        $this->db->update('user');
        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE)
        {
            $this->setError(1000,'数据库操作失败');
        }
        return true;
    }
}