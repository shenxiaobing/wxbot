<?php
/**
 * api操作类
 *
 */
class webApi{
	public $host;

	public function __construct($host)
	{
		$this->host = $host;
    }

	public function getGroupList($action){
		$apiUrl = $this->host.$action;
		$groupListJson = file_get_contents($apiUrl);
	
		$result = json_decode($groupListJson,true);
		$groupList = $result['data']['group_list'];
		$groupArray = array();
		foreach($groupList as $group) {
			$groupArray[$group['UserName']] = $group['NickName'];
		}
		return $groupArray;
	}

	public function getGroupMembers($action){
		$apiUrl = $this->host.$action;
		$groupMembersJson = file_get_contents($apiUrl);
		$result = json_decode($groupMembersJson, true);
		$groupMembers = $result['data']['group_members'];
		$groupMemberArray = array();
		foreach($groupMembers as $groupId=>$memberList) {
			foreach($memberList as $member) {
				$groupMemberArray[$groupId][$member['UserName']] = $member['NickName'];
			}
		}
		return $groupMemberArray;
	}

	public function getMedia($action){
		$apiUrl = $this->host.$action;
		$Json = file_get_contents($apiUrl);
		$arr = json_decode($Json,1);
		if($arr['code'] == 200){
			return $arr['data'];
		}else{
			return 'error';
		}
	}
}