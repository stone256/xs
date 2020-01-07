<?php


/**
 * @author peter wang <stone256@hotmail.com>
 * enable model load
 * remember the bottom one overwrite aboves'
 * all module from path "module"
 */

$modules[] = '/sitemin';


// you CAN put some initialize script here
//      beacuse this is the first place that system look into
//google bot check
// $config['google']['bot check']['api'] = 'https://www.google.com/recaptcha/api/siteverify';
// $config['google']['bot check']['key'] = '6LdVH2EUAAAAAD_CVXDvOmRZ3IQvM60XkBy_Kke_';
// $config['google']['bot check']['secret'] = '6LdVH2EUAAAAAOuilzwTZko59-xxMP5s30WBO_Mz';
//set cookie _x_captcha to this value, will skip captcha bot detection
// this is for live debug and if you working some where that google is not working well.

//uncomment follow line to enable google bot check
//$config['login']['captcha']['key'] = '7feda8c64c2ea4246c58472818d41c18cb28fe8c';
//if($_COOKIE['_x_captcha'] == $config['login']['captcha']['key'])
        // define( '_X_GCAPTCHA', false);	//set to 1 or true when you want catpcha and already got google captche key (v2.invisible)



// you MAT NOT want to put some codes here
//      beacuse at this stage the package has not yet been include you can only relay on standard php
