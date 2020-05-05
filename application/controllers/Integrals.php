<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Integrals extends MY_Controller{
    protected $is_auth = true;
    protected $resource_model = 'integral';
    protected $req = [
        'type'=>'',
        'value'=>'',
        'source'=>''
    ];
    protected $rules_config = [
        'create'=>[
            [
                'field'=>'type',
                'label'=>'type',
                'rules'=>['required','in_list[1,-1]'],
                ['required'=>'type can\'t empty']
            ],
            [
                'field'=>'value',
                'label'=>'value',
                'rules'=>['required','integer'],
                ['required'=>'value can\'t empty'],
            ],
            [
                'field'=>'source',
                'label'=>'source',
                'rules'=>['required'],
                ['required'=>'source can\'t empty'],
            ],
        ]
    ];
}