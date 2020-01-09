<?php

class sitemin_model_captcha_local implements sitemin_model_captcha{
        static $on = false;

        //validate returns
        function validate($q){
                $vcode = $q['vcode'];
                return xpCaptcha::check($vcode);

        }

        //generate html block
        function html(){
                $s =  '
                <label for="vcode" ><span class="fa fa-picture-o"></span> Vcode (Click the image to get a new one)</label>
                <img class="vcode-image mt-2 mb-2"
                        style="cursor:pointer;border:1px solid #bbb;padding:1%;width:100%"
                        onclick="this.src=\''._X_URL.'/sitemin/helper/vcode?a=\'+Math.random()"
                        src="'._X_URL.'/sitemin/helper/vcode">
                <input type="text" placeholder="Click the image to get a new one" required="" name="vcode" class="form-control input-lg vcode-input">
                ';
                return $s;
        }

        function test(){
                die(__FILE__);
        }
}
