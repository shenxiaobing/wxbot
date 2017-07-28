<?php
header('content-type:text/html;charset=utf-8');
require_once('./config.php');
require_once('./core/redis.php');

$data = file_get_contents('php://input');
$redis = my_redis::getInstance($config['redis']);
$r = $redis->rpush($config['list_name'],$data);

echo 1;
