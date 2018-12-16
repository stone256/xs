<?php


class sitemin_model_mail {
	//table store mail
	var $mail_table = 'sitemin_mail';
	//table store recipient
	var $recipient_table = 'sitemin_mail_recipient';
	//table store attachment if have
	var $attachment_table = 'sitemin_mail_attachment';
	//mail table column
	var $columns = array('id','from','subject','body', 'created','sent','status','try');

	var $recipients = array('to', 'cc', 'bcc');

	function search($value, $field='from'){
		$name = addslashes($value);
		$field = addslashes($field);
		if(in_array($field, $this->columns)){
            $sql = "SELECT `{$field}` FROM {$this->mail_table} WHERE `{$field}` like '%$name%' LIMIT 12";
		}else{
			$sql = "SELECT `to` FROM {$this->recipient_table} where `to` like '%$name%' group by `to` LIMIT 12";
        }
        $rs['rows'] = xpTable::load($this->recipient_table)->q($sql);
		return $rs;
	}

	/**
	* put in queue
	*/
	function queuing($mrr){
		$arr = array(
		        'from' => $mrr['from'],
		        'subject' => $mrr['subject'],
		        'body' => $mrr['body'],
		    );

		$id = xpTable::load($this->mail_table)->insert($arr);

		foreach($this->recipients as $k=>$v){
		    if(!isset($mrr[$v])) continue;
		    $brr = is_array($mrr[$v]) ?$mrr[$v] : array($mrr[$v]);
		    foreach($brr as $ka=>$va) {
		        xpTable::load($this->recipient_table)->insert(array('mail_id'=>$id, 'email'=>$va, 'ccbcc'=>$v));
		    }
		}
		if(!isset($mrr['attachment'])) return $id;
		$brr = is_array($mrr['attachment']) ?$mrr['attachment'] : array($mrr['attachment']);
		foreach($brr as $ka=>$va) {
		    xpTable::load($this->attachment_table)->insert(array('mail_id'=>$id, 'content'=>$va));
		}
		return $id;
	}

    /**
     * send
     */
    function send($id){
        $mail = xpTable::load($this->mail_table)->get(array('id'=>$id));
        foreach((array)xpTable::load($this->recipient_table)->gets(array('mail_id'=>$id)) as $k=>$v){
            $mail[$v['ccbcc']][] = $v['to'];
        }
        foreach((array)xpTable::load($this->attachment_table)->gets(array('mail_id'=>$id), 'name,content') as $k=>$v){
            $mail['attachment'][] = $v;
        }

        $r = _factory('sitemin_model_mailsender')->send($mail);
        //update flag
        $try = $mail['try']++;
        $sent = date("Y-m-d H:i:s");
        xpTable::load($this->mail_table)->updates(array('sent'=>$sent, 'try'=>$try, 'status'=>'sent'), array('id'=>$id));
        return $r;
    }

    /**
     * delete entry form queue
     */
    function delete($id){
        //delete from mail table
        xpTable::load($this->mail_table)->deletes(array('id'=>$id));
        //delete from recipient table
        xpTable::load($this->recipient_table)->deletes(array('mail_id'=>$id));
        //delete from attachment table
        xpTable::load($this->attachment_table)->deletes(array('mail_id'=>$id));
    }

	function gets($q=array()) {

		$q = xpAS::escape(xpAS::trim($q));
		$search[] = " 1 ";
		if ($q['filter']['from']) $search[] = "m.from like  '%{$q['filter']['from']}%' ";
		if ($q['filter']['subject']) $search[]  = " m.subject like '%{$q['filter']['subject']}%' ";

		if ($q['filter']['to']) $search[]  = " t.to like '%{$q['filter']['to']}%' ";

		$sql = "SELECT DISTINCT(m.id) as mid FROM $this->mail_table as m JOIN {$this->recipient_table} as t ON t.mail_id = m.id WHERE ".implode(' AND ', $search);
		$rs = xpTable::load($this->mail_table)->q($sql);
		$count = count($rs);

		//calculate page and limit
		$page['total'] = $count;
		$page['length'] = $q['page_length'] ? $q['page_length'] : 6;
		$page['pagination_max_length'] = 10;
		$page['pages'] = ceil($count / $page['length']);

		$page['no'] = max(1, min($page['pages'], ((int)$q['currentpage'] ? (int)$q['currentpage'] : 1)));
		$page['current_shows'] = ceil($page['no'] / $page['pagination_max_length']); // 1...xxx
		$page['current_shows_length'] = min(min($page['pages'], ($page['current_shows']) * $page['pagination_max_length']) - ($page['current_shows'] - 1) * $page['pagination_max_length'], $page['pagination_max_length']);
		$page['omit'] = $page['pages'] > $page['pagination_max_length'];
		$page['backward'] = $page['current_shows'] > 1;
		$page['forward'] = $page['current_shows'] * $page['pagination_max_length'] < $page['pages'];

		$order = $q['sort'];
		$limit =(($page['no'] - 1) * $page['length']) . ",{$page['length']} ";

		if($order) $ORDER = "ORDER BY ". (preg_match('/^\-/', $order) ? "m.".substr($order, 1)." DESC" : "m.$order ASC " );

		$sql = "SELECT DISTINCT (m.id) as mid, m.* FROM $this->mail_table as m JOIN {$this->recipient_table} as t ON t.mail_id = m.id WHERE ".implode(' AND ', $search) ." $ORDER  LIMIT $limit";

		$rs['data'] = xpTable::load($this->mail_table)->q($sql);
		foreach ($rs['data'] as $k=>$v){
			$tos = xpTable::load($this->recipient_table)->gets(array('mail_id'=>$v['id']));
			$rs['data'][$k]['to'] = xpAS::get($tos, '*,to');
			$tos = xpTable::load($this->attachment_table)->gets(array('mail_id'=>$v['id']), "name");
			$rs['data'][$k]['attached'] = xpAS::get($tos, '*,name');
		}
		$rs['filter'] = $q['filter'];
		$rs['sort'] = $q['sort'];
		$rs['page'] = $page;

		return $rs;

	}



}
