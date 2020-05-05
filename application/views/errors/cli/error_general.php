<?php
defined('BASEPATH') OR exit('No direct script access allowed');

header("Content-Type:application/json; charset=utf-8");

echo json_encode([
    'code'=>isset($code)?$code:1000,
    'message'=>isset($header)?$message:'network error!'
]);

die(0);