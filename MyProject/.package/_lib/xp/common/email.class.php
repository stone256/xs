<?php
/**
 * class wrap around htmlMimeMail class
 *
 */
class email {
	function send($arr) {

		$mail = new htmlMimeMail();
		$mail->setHtmlCharset('UTF-8');
		$mail->setFrom(self::pack($arr['from']));
		$mail->setSubject(self::filter($arr['subject']));
		$mail->setHTML(xpAS::priority_get($arr['body'], $arr['html'], $arr['content']));
		if ($arr['cc']) {
			$arr['cc'] = is_array($arr['cc']) ? $arr['cc'] : explode(';', $arr['cc']);
			foreach ($arr['cc'] as $cc) $mail->setCc(self::pack($cc));
		}
		if ($arr['bcc']) {
			$arr['bcc'] = is_array($arr['bcc']) ? $arr['bcc'] : explode(';', $arr['bcc']);
			foreach ($arr['bcc'] as $bcc) $mail->setBcc(self::pack($bcc));
		}
		if (is_array($arr['attachment'])) foreach ($arr['attachment'] as $k => $v) {
			$mail->addAttachment($v['content'], $v['name']);
		}
		$to = xpAS::priority_get($arr['to'], $arr['email']);
		$to = is_array($to) ? $to : explode(';', $to);
		foreach ($to as $k => $t) {
			$to[$k] = self::pack($t);
		}
		return $mail->send($to);
	}
	/**
	 * packing email address from abc@cba.com,aaa => aaa<abc@cba.com>
	 *
	 * @param unknown_type $str
	 */
	function pack($str=null,$default = 'admin@system.api,admin'){
		$str = $str ? $str : $default;
		//filter \n\r ;
		$str = self::filter($str,true);
		$s = explode(',',$str); 
		return count($s)>1 ? "{$s[1]}<{$s[0]}>" : $s[0];
	}
	
	function filter($str,$sc=false){
		$str = str_replace(array("\n","\r"),array('',''),$str);	
		return $sc ? array_shift(explode(';',$str)) : $str;	
	}	
	
}
