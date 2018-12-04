<?php


class sitemin_model_message {
	var $message_table = 'sitemin_message';
	var $receiver_table = 'sitemin_message_receiver';
	var $system_sender = array(
		-1=>'system',
		-2=>'cron',

	);
	function send_to_group($groups, $msg, $sender=null){
		if($groups){
			$groups = is_array($groups) ? $groups : preg_split('/\s*\,\s*/ims', $groups);
			foreach ($groups as $kg=>$vg){
				if(!is_numeric($vg)){
					$_g = _factory('sitemin_model_acl_role')->get(array('name'=>$vg));
					$groups[$kg] = $_g['id'];
				}
			}
			$receivers = _factory('sitemin_model_user')->get_user_id_by_role($groups);
		}
		$this->send_to($receivers, $msg, $sender, $groups);
	}

	/**
	 * $group = alc_role's id
	 */
	function send_to($receivers, $msg, $sender=null, $groups=null){
		if($receivers) $rc = is_array($receivers) ? $receivers : preg_split('/\s*\,\s*/ims', $receivers);
		if(!$sender) $sender = xpAS::get(_factory('sitemin_model_login_original')->current(), 'id');
		if(!count($rc) || !$sender) return;
		//create message;
		$arr = array(
				'from'=>$sender,
				'message'=>$msg,
			);
		if($groups) $arr['to_group'] = $groups;
		$mid  = xpTable::load($this->message_table)->insert($arr);
		foreach ($rc as $kc=>$vc){
			$arr = array(
				'sitemin_message_id'=>$mid,
				'sitemin_id'=>$vc,
				);
			xpTable::load($this->receiver_table)->insert($arr);
		}
	}

	/**
	 * list current available
	 *
	 * @param $user_id
	 */
	function lead($user_id=null){
		$u = (int)$user_id ? (int)$user_id : xpAS::get(_factory('sitemin_model_login')->current(), 'id');
		//get total unread msg
		$ret = xpTable::load($this->receiver_table)->get(array('sitemin_id'=>$u, 'viewed'=>'0000-00-00 00:00:00'), 'count(*) as unviewed' );
		//get max list items
		$mi = (int)_factory('sitemin_model_var')->get('sitemin/message/max_list_items');
		$msg = xpTable::load($this->receiver_table)->gets(array('sitemin_id'=>$u, 'viewed'=>'0000-00-00 00:00:00'), null, null, $mi);
		foreach((array)$msg as $k=>$m){
			$_m = xpTable::load($this->message_table)->get(array('id'=>$m['sitemin_message_id']));
			if($_m['from'] == -1){
				$_m['from'] = 'system';
			}else{
				$u = xpAS::_factory('sitemin_model_user')->detail($_m['from']);
				$_m['from'] = $u['email'] ? $u['email'] : base64_decode($u['username']);
			}
			$ret['msg'][] = $_m;
		}

		return $ret;
	}


	function lists($user_id=null){

		$u = (int)$user_id ? (int)$user_id : xpAS::get(_factory('sitemin_model_login')->current(), 'id');
		$msg = xpTable::load($this->receiver_table)->gets(array('sitemin_id'=>$u), '*', '-sitemin_message_id');
		foreach((array)$msg as $k=>$m){
			$_m = xpTable::load($this->message_table)->get(array('id'=>$m['sitemin_message_id']));
			$v['id'] = $m['id'];
			$v['From'] = $this->system_sender[$_m['from']];
			if(!$v['From']){
				$u = xpAS::_factory('sitemin_model_user')->detail($_m['from']);
				$v['From'] = $u['email'] ? $u['email'] : base64_decode($u['username']);
			}
			$v['Message'] = $_m['message'];
			$v['Date'] = $_m['created'];
			$v['Viewed'] = $m['viewed'] == '0000-00-00 00:00:00' ? false : true;
			$ret[] = $v;
		}

		return $ret;
	}

	function delete($q, $user_id=null){
		$u = (int)$user_id ? (int)$user_id : xpAS::get(_factory('sitemin_model_login')->current(), 'id');
		$msg = xpTable::load($this->receiver_table)->deletes(array('sitemin_id'=>$u, 'id'=>$q['id']));
		return 'ok';
	}

}
