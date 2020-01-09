<?php

interface sitemin_model_captcha{
        //validate returns
        function validate($data);

        //generate html block
        function html();

}
