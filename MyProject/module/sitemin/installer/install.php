<?php

/**
 * installer ver 1.0
 */

        define('_X_INSTALL_FILE0', _X_INSTALL_ROOT.'/.router.php');
        define('_X_INSTALL_FILE1', _X_INSTALL_ROOT.'/.setup.1.0.0.0.php.done');
        define('_X_INSTALL_FILE2', _X_INSTALL_ROOT.'/.setup.1.0.0.1.php.done');
        define('_X_INSTALL_FILE3', _X_CONFIG);
        define('_X_INSTALL_FILE4', _X_CONFIG.'/x2cli.php');
        define('_X_INSTALL_FILE5', _X_CONFIG.'/local.php.sample');


//model
class sitemin_installer_install{

        var $access_file = [
                _X_INSTALL_FILE0,
                _X_INSTALL_FILE1,
                _X_INSTALL_FILE2,
                _X_INSTALL_FILE3,
                _X_INSTALL_FILE4,
                _X_INSTALL_FILE5,
        ];

        function test_permission(){
                foreach ($this->access_file as $f) {
                        $p[ is_writable($f) ? 'ok' : 'failed'][] = $f;
                }
                return $p;
        }

        function test_connection($q){
                $q = xpAS::escape($q);

                $c['db']=[
                        'user'	=> $q['user'],
                        'host'	=> $q['host'],
                        'password' => $q['password'],
                        'database' => addslashes($q['database']),
                ];
                sleep(1);
                try{
                        $db = xpPdo::conn($c);
                        return false;
                } catch (Exception $e){
                        $m =  str_replace([". ", ": " ],[".\n ", ":\n\t"], $e->getMessage());
                        return $m;
                }

        }

        function install($q){

                $q = xpAS::escape($q);

                //change the files for start up
                rename(_X_INSTALL_FILE1, preg_replace('/\.done$/ims','', _X_INSTALL_FILE1) );
                rename(_X_INSTALL_FILE2, preg_replace('/\.done$/ims','', _X_INSTALL_FILE2) );

                //stop install act one next entry
                $con = file_get_contents(_X_INSTALL_FILE0);
                $con = str_replace('include "installer.php";', '#include "installer.php";', $con);
                file_put_contents(_X_INSTALL_FILE0, $con);


                //rebuild x2cli.php
                $con = $_SERVER;
                $con['X2CLI_CALL']= true;
                $s = '<';
                $s .= '?';
                $s .= "php\n\n\n";
                $s .= '$apache_data=';
                $s .= "\n" . var_export($con, 1);
                $s .= "\ndefine('__X_DEBUG', true);\n\n";
                file_put_contents(_X_INSTALL_FILE4, $s);
                /*
                $config['DATABASE']['a'] = array( //for testing database
                		'host'		=> 'localhost',
                		'database'	=> 'mydatabase',
                		'user'		=> 'myusername',
                		'password'	=> 'mypassword',
                );
                */
                //set the database config (_config('DATABASE,a'))
                $con = file_get_contents(_X_INSTALL_FILE5);
                //$con = preg_replace('/\s*\,/ims', "'host'=>'{$q['host']}'" , $con);
                $con = preg_replace('/\s*\'host\'\s*\=\>\s*\'localhost\'\s*\,/ims', "\n'host'=>'{$q['host']}'," , $con);
                $con = preg_replace('/\s*\'database\'\s*\=\>\s*\'mydatabase\'\s*\,/ims', "\n'database'=>'{$q['database']}'," , $con);
                $con = preg_replace('/\s*\'user\'\s*\=\>\s*\'myusername\'\s*\,/ims', "\n'user'=>'{$q['user']}'," , $con);
                $con = preg_replace('/\s*\'password\'\s*\=\>\s*\'mypassword\'\s*\,/ims', "\n'password'=>'{$q['password']}',\n" , $con);
                file_put_contents( _X_INSTALL_FILE5, $con);
                rename(_X_INSTALL_FILE5, str_replace('.sample', '', _X_INSTALL_FILE5));
                return;
        }

}
