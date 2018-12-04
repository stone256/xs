<?php

class syslog {

	
	static $level = array("info","warn","err");
	
	
	static function msg($msg, $level=0){
		if(is_numeric($level)) $level = self::$level[abs(ceil($level) % 4)];
		if(!in_array($level, self::$level)) $level = self::$level[0];
		exec ('logger -p user.'.$level.' '.escapeshellarg (var_export($msg,1)) );
		
	}
	
	
//	
//        protected $actuallyLogErrors = true;
//
//        public function __construct ($actuallyLogErrors = true) {
//                $this->actuallyLogErrors = $actuallyLogErrors;
//        }
//
//        public function info ($message) {
//                return $this->reportMessage ('user.info', $message);
//        }
//        public function warning ($message) {
//                return $this->reportMessage ('user.warn', $message);
//        }
//        public function error ($message) {
//                return $this->reportMessage ('user.err', $message);
//        }
//
//        protected function reportMessage ($messageType, $message) {
//                if ($this->actuallyLogErrors) {
//#                               print ('logger -p '.escapeshellarg ($messageType).' '.escapeshellarg ($message))."\n";
//                        exec ('logger -p '.escapeshellarg ($messageType).' '.escapeshellarg ($message));
//                }
//                
//        }
}                   
                        
