<?php 
/*
 * 日志处理类
 */
class Log{
	static $logs = array();
	//记录日志内容
	static public function set($msg,$type='notice'){
		if(in_array(strtoupper($type), C('LOG_TYPE'))){
			$date = date("Y-m-d H:i:s");
			self::$logs[] = " [ " . $type . ' ] ' .  $msg . ' ( ' . $date . ' ) ' . "\r\n";
		}
	}
	//存储日志内容到日志文件	存储非致命错误记录日志
	static public function save($message_type = 3, $destination = null, $extra_headers = null){
		if(!C('LOG_START')){
			return false;
		}
		if(is_null($destination)){
			$destination = LOG_PATH . '/' . date('Y_m_d') . '.log';
		}
		if($message_type == 3){
			if(is_file($destination) && filesize($destination) > C('LOG_SIZE')){
				rename($destination, dirname($destination) . '/' . time() . '.log');
			}
		}
		if(error_log(implode(',' , self::$logs),$message_type,$destination)){
			return true;
		}
		return false;
	}
	
	//直接写入日志文件	3 以文件的形式存储 文件存储         $extra_headers = null 不需要文件header头
	static public function write($message,$message_type = 3, $destination = null, $extra_headers = null){
		if(!C('LOG_START')){
			return false;
		}
		if(is_null($destination)){
			$destination = LOG_PATH . '/' . date('Y_m_d') . '.log';
		}
		if($message_type == 3){
			if(is_file($destination) && filesize($destination) > C('LOG_SIZE')){
				rename($destination, dirname($destination) . '/' . time() . '.log');
			}
		}
		$date = date("Y-m-d H:i:s");
		$message = $message . $date . "\r\n";
		error_log($message,$message_type,$destination);
	}
	
	
	
	
}


?>