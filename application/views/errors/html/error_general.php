<?php
defined('BASEPATH') OR exit('No direct script access allowed');

header("Content-Type:application/json; charset=utf-8");
set_status_header(500);
echo json_encode([
    'code'=>1000,
    'message'=>isset($message)?$message:'network error!'
],JSON_UNESCAPED_UNICODE&JSON_UNESCAPED_SLASHES);
die(0);