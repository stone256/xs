<?php

function captcha($data) {
        global $config;
        $g = array('response' => $data['g-recaptcha-response'], 'secret' => _factory('sitemin_model_var')->get('sitemin/google/captcha/secret'), 'remoteip' => xpAS::get_client_ip());
        $a = xpAS::curlOut(_factory('sitemin_model_var')->get('sitemin/google/captcha/api'), http_build_query($g));
        $arr = json_decode($a);
        return $arr->success == ture;
}
