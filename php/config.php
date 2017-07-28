<?php
$config = array(
	//redis配置
	'redis' => array(
		'host' => 'localhost',
		'port' => '6379',
		'auth' => ''
	),
	//数据库配置
	'db' => array(
		'host' => '127.0.0.1',
		'port' => '3306',
		'user' => 'root',
		'password' => 'dbrootpass',
		'db_name' => 'wechat'
	),
	//队列名称
	'list_name' => 'msg',
	'api_url' => 'http://localhost:8080/api/',
	'filter_key' => array(
		's_reminder' => '上课提醒',
		't_reminder' => '教师提醒',
		'accompany' => '陪伴',
		'evaluate' => '评价',
		'photo_start' => 'start',
		'gourp_start' => '开篇语',
		'gourp_end' => '结束语',
	),
);
