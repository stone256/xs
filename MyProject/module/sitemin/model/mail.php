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
/*
    function getssss($crr=null){
        //xpTable::load($this->attribute_table)->insert(array('mail_id'=>$id, 'name'=>$v, 'value'=>$va));
        if(!$crr) return false;
        if(!is_array($crr)) $crr = array($crr);
        $column_pattern = '/('.implode('|', $this->columns).')\s*\=/ims';
        foreach($crr as $kc=>$vc){
            switch(true){
                case is_array($vc):
                        if(in_array(key($vc), $this->columns)){

                        }
                    break;
                default:
                        if(preg_match($column_pattern, $vc)){
                            $frr[] = $vc;
                        }else{
                            $vrr[] = $vc;
                        }
                    break;
            }
        }


    }
*/

    function gets($q=array()) {

        $q = xpAS::escape(xpAS::trim($q));
        if ($q['filter']['from']) $search1[] = "from like  '%{$q['filter']['from']}%' ";
        if ($q['filter']['subject']) $search1[]  = " subject like '%{$q['filter']['subject']}%' ";

        if ($q['filter']['to']) $search2[]  = " ( (name='to' value like '%{$q['filter']['to']}%') OR (name='to' value like '%{$q['filter']['cc']}%') OR (name='to' value like '%{$q['filter']['bcc']}%')) ";


        $rs = xpTable::load($this->user_table)->get($search, 'COUNT(*) as c');
        $count = $rs['c'];

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

        $rs['data'] = xpTable::load($this->user_table)->gets($search, '*', $order, $limit);
        foreach ($rs['data'] as $k=>$v){
            $roles = xpTable::load($this->role_table)->gets(array('sitemin_id'=>$v['id']),'acl_role_id', 'acl_role_id');
            $role_ids = xpAS::get($roles, '*,acl_role_id');
            $_r = _factory('sitemin_model_acl_role')->gets(array('id'=>$role_ids));
            $rs['data'][$k]['userrole'] = implode(',', xpAS::get($_r, 'data,*,name'));
        }


        $rs['filter'] = $q['filter'];
        $rs['sort'] = $q['sort'];
        $rs['page'] = $page;

        return $rs;

    }



}
