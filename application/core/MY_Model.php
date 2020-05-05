<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Model extends CI_Model{
    protected $table_name;//表名
    protected $child_resource_model = [];//子资源
    private $_primary;// 资源主键
    protected $primary_name = 'id'; //主键名默认为id
    private $_has_error = false; //是否存在错误
    protected $display_primary_name;//对外输出显示主键名
    private $_error =[]; //错误
    public function setPrimary($primary){
        $this->_primary = $primary;
        if($this->db){
            $this->db->where([$this->primary_name=>$this->_primary]);
        }
    }
    public function getPrimary(){
        return $this->_primary;
    }
    public function __construct()
    {
        parent::__construct();
        $load = $this->__get('load');
        foreach($this->child_resource_model as $resource_model){//自动实例化子资源
            $load->model($resource_model,'',true);
        }
        //绑定表名
        if(isset($this->db)){
            $this->db->from($this->table_name);
        }
    }

    public function __call($name, $arguments)
    {
        $load = $this->__get('load');
        $req = $arguments[0];
        $load->helper('tool');
        $name = strtolower(to_under_score($name));
        if(strstr($name,'_by_')){
            $db = clone $this->db;
            list($method,$primary_name) = explode('_by_',$name);
            $query = $db->query("select * from {$this->db->dbprefix($this->table_name)} where $primary_name = '$req'");
            return $query->row_array();
        }elseif($name==='get'){
            if(!$this->primary){
                $this->setError(1000,'system error ，未设置主键');
                return false;
            }
            return $this->db->get($this->table_name)->row_array();
        }elseif($name==='gets'){
            if(!$this->table_name){
                $this->setError(1001,'system error 资源不存在');
                return false;
            }
            $query = $this->db->get();
            return $result = $query->result_array();
        }elseif($name==='delete'){
            if(!$this->table_name&&!$this->primary){
                $this->setError(1001,'system error 服务器内部错误');
                return false;
            }
            return $this->db->delete();
        }elseif($name==='create'){
            foreach ($req as $key=>$value){
                if($value||$value===0||$value==='0'){
                    $this->db->set($key,$value);
                }
    
            }
            $this->db->set('create_time',time());
            if($this->db->insert($this->table_name)){
                return [$this->display_primary_name=>$this->db->insert_id()];
            }
            return false;
        }elseif($name==='modify'){
            foreach ($req as $key=>$value){
                if($value||$value==='0'||$value===0){
                    $this->db->set($key,$value);
                }
            }
            $this->db->set('update_time',time());
            return $this->db->update($this->table_name);
        }
    }

    protected final function setError($code,$message){
        $this->_has_error = true;
        $this->_error = ['code'=>$code,'message'=>$message];
    }
    public final function getError(){
        return [$this->error['code'],$this->error['message']];
    }
    public final function hasError(){
        //是否有错误
        return $this->_has_error;
    }
}