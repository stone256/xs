<?php

        define('_X_INSTALL_FILE', __FILE__);

        define('_X_INSTALL_FILE0', __DIR__.'/.router.php');
        define('_X_INSTALL_FILE1', __DIR__.'/.setup.1.0.0.0.php.done');
        define('_X_INSTALL_FILE2', __DIR__.'/.setup.1.0.0.1.php.done');
        define('_X_INSTALL_FILE3', _X_CONFIG.'/local.php');


        define('_X_INSTALL_URL', '/sitemin/install');



        //force to entry through the install url
        $_url = xpAS::get($_SERVER, 'REDIRECT_URL');
        if($_url !== _X_INSTALL_URL) xpAS::go(_X_INSTALL_URL);

        //instalation routers
        $routers=[
                //sitemin_installer_install
                _X_INSTALL_URL => '/sitemin/installer/install@run',
        ];
