<?php
defined('BASEPATH') OR exit('No direct script access allowed');

header("Content-Type:application/json; charset=utf-8");
set_status_header(404);
echo json_encode([
    'code'=>1002,
    'message'=>'this api is not exists'
]);