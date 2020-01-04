<?php


//model
class sitemin_installer_install{

        function test_connection($q){
                $q = xpAS::escape($q);
                $c['db']=[
                        'user'	=> $q['user'],
                        'host'	=> $q['host'],
                        'password' => $q['password'],
                        'database' => $q['database']
                ];
                try{
                        $db = xpPdo::conn($c);
                        return true;
                } catch (Exception $e){
                     return false;
                }

        }
}