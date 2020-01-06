<?php


/**
 * @author peter wang <stone256@hotmail.com>
 * enable model load
 * remember the bottom one overwrite aboves'
 * all module from path "module"
 */


$modules['examples'] = '/examples';


// you CAN put some initialize script here
//      beacuse this is the first place that system look into

// you MAT NOT want to put some codes here
//      beacuse at this stage the package has not yet been include you can only relay on standard php
