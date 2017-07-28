<?php
require_once('./config.php');
require_once('./core/database.php');
require_once('./core/redis.php');
require_once('./core/webApi.php');

$redis = my_redis::getInstance($config['redis']);
$db = db::getInstance($config['db']);
$webApi = new webApi($config['api_url']);
//echo "<pre>";
//主体循环监控redis队列
while(true) {
	//从队列中获取第一条数据并阻塞队列
	$data = $redis->blpop($config['list_name'], 3);
	if(!$data){
		//break;
		continue;
	}
	$msg = json_decode($data[1],true);
	//var_dump($msg);

	//当消息不是群消息时不处理
	if($msg['msg']['msg_type_id'] != 3){
		//break;
		continue;
	}

	//当消息不是文字图片语音时不处理
	if(in_array($msg['msg']['content']['type'],array(0,4,3)) === false){
		//break;
		continue;
	}

	//保存媒体文件
	if($msg['msg']['content']['type'] == 3 || $msg['msg']['content']['type'] == 4){
		$type = $msg['msg']['content']['type'];
		$media_str = ($type == 3) ? $msg['msg']['content']['img'] : $msg['msg']['content']['voice'];
		$msg['msg']['content']['data'] = save_media($type,$media_str);
	}

	//从缓存中获取群名称，用户名称，微信名称
	$group_name = htmlspecialchars($redis->get($msg['bot_id'].'-'.$msg['msg']['user']['id']));//群名称
	$username = htmlspecialchars($redis->get($msg['bot_id'].'-'.$msg['msg']['content']['user']['id']));//用户名称
	$wx_name = htmlspecialchars($redis->get($msg['bot_id'].'-name'));//微信用户名称
	$msg['msg']['wx_name'] = $wx_name ? $wx_name : 'unknown';

	if(!$group_name || !$username){
		continue;
	}
	$msg['msg']['user']['name'] = $group_name;
	$msg['msg']['content']['user']['name'] = $username;

	//添加消息到数据库
	$res = addData($msg);

	//发送数据到接口
	$r = sendApi($msg['msg'],$config['filter_key']);
	
	//break;
}

function save_media($type,$media_str){
	if($type == 3){
		$jpg = hex2bin($media_str);

		$dirname = "./media/img/".date('Y-m-d',time());
		$filename = '/'.date("YmdHis",time()).rand(1000,9999).'.jpg';
		if(!file_exists($dirname)){
			mkdir ($dirname);
		} 	
		$file = fopen($dirname.$filename,"w");//打开文件准备写入  
		fwrite($file,$jpg);//写入  
		fclose($file);//关闭
		
		$data = $dirname.$filename;
		
	}else if($type == 4){
		$jpg = hex2bin($media_str);
		$dirname = "./media/voice/".date('Y-m-d',time());
		$filename = '/'.date("YmdHis",time()).rand(1000,9999).'.mp3';
		if(!file_exists($dirname)){
			mkdir ($dirname);
		} 
		$file = fopen($dirname.$filename,"w");//打开文件准备写入  
		fwrite($file,$jpg);//写入  
		fclose($file);//关闭
		
		$data = $dirname.$filename;
	}

	return $data;
}

function sendApi($msg,$config){
	global $redis;
	$uid = $msg['content']['user']['id'];
	$name = $msg['content']['user']['name'];
	$group_id = $msg['user']['id'];
	$group_name = $msg['user']['name'];
	$data = $msg['content']['data'];
	$type = 0;
	if(strpos($msg['content']['data'],$config['s_reminder']) !== false){
		$type = 1;
	}else if(strpos($msg['content']['data'],$config['t_reminder']) !== false){
		$type = 2;
	}else if(strpos($msg['content']['data'],$config['accompany']) !== false){
		$type = 3;
	}else if(strpos($msg['content']['data'],$config['evaluate']) !== false){
		$type = 4;
	}else if(strpos($msg['content']['data'],$config['photo_start']) !== false){
		$key = md5($group_id.'-'.$uid);
		$redis->setex($key.'-photo',600, '');
		$redis->setex($key.'-photo-count',600, 0);
	}else if(strpos($msg['content']['data'],$config['gourp_start']) !== false){
		$type = 5;
	}else if(strpos($msg['content']['data'],$config['gourp_end']) !== false){
		$type = 6;
	}

	if($msg['content']['type'] == 3){
		$key = md5($group_id.'-'.$uid);
		$str = $redis->get($key.'-photo');
		$count = $redis->get($key.'-photo-count');
		if($str !== false && $count !== false){
			if($count == 2){
				$redis->del($key.'-photo');
				$redis->del($key.'-photo-count');
				$type = 5;
				$data = $str.','.$data;
			}else{
				$redis->setex($key.'-photo',600, $str.','.$data);
				$redis->setex($key.'-photo-count',600, $count+1);
				return false;
			}
		}else{
			return false;
		}
	}

	if($type == 0){
		return false;
	}

	echo $uid."-".$name."-".$group_name."-".$data."-".$type."\n";
	
	return true;
}

 /**
 * 添加消息到数据库
 * @param array $msg 消息
 * @param bool 添加是否成功
 */
function addData($msg){
	global $db;
	
	$gourp_id = $msg['msg']['user']['id'];
	$group_name = $msg['msg']['user']['name'];
	$msg_id = $msg['msg']['msg_id'];
	$msg_type_id = $msg['msg']['msg_type_id'];
	$user_id = $msg['msg']['content']['user']['id'];
	$username = $msg['msg']['content']['user']['name'];
	$data_type = $msg['msg']['content']['type'];
	$data = $msg['msg']['content']['data'];
	$wx_name = $msg['msg']['wx_name'];

	$sql = 'insert into msg(wx_name,gourp_id,gourp_name,msg_id,msg_type_id,user_id,username,data_type,`data`,create_time) values("'.$wx_name.'","'.$gourp_id.'","'.$group_name.'","'.$msg_id.'",'.$msg_type_id.',"'.$user_id.'","'.$username.'",'.$data_type.',"'.$data.'",'.time().')';

	$result = $db->exec($sql);
	return $result;
}


 /**
 * 从redis中获取缓存的群名和用户
 * @param string $botId 登录的用户表示
 * @param string $action  获取的数据类型getGroupList群名  getGroupMembers群用户
 * @return array result  结果集
 */
function getApiRedis($botId,$action){
	global $redis;
	global $webApi;
	$api_action = array(
		'getGroupList' => 'get_group_list',
		'getGroupMembers' => 'get_group_members'
	);
	$key_arr = array(
		'getGroupList' => 'groupList',
		'getGroupMembers' => 'groupMembers'
	);
	$key = $botId . ':' . $key_arr[$action];
	$s = $redis->get($key);
	if($s) {
		$result = unserialize($s);
	}else{
		$url = $api_action[$action].'/?bot_id=' . $botId;
		$result = $webApi->$action($url);
		$redis->set($key, serialize($result));
	}
	return $result;
}