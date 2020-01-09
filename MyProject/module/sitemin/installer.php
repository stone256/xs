<?php

        define('_X_INSTALL_FILE', __FILE__);
        define('_X_INSTALL_ROOT', __DIR__);



        define('_X_INSTALL_URL', '/sitemin/install');



        //force to entry through the install url
        $_url = xpAS::get($_SERVER, 'REDIRECT_URL');
        if($_url !== _X_INSTALL_URL) xpAS::go(_X_INSTALL_URL);

        //instalation routers
        $routers=[
                //sitemin_installer_install
                _X_INSTALL_URL => '/sitemin/installer/install@run',
        ];
