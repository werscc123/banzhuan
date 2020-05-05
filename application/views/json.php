<?php
defined('BASEPATH') OR exit('No direct script access allowed');

header("Content-Type:application/json; charset=utf-8");

echo json_encode($data,JSON_UNESCAPED_UNICODE&JSON_UNESCAPED_SLASHES);