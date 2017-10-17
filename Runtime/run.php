<?php 

define('CACHE_DIR', 'cache');		
define('LOG_DIR', 'log');			
define('TPL_DIR', 'tpl');			
define('MODULE_DIR','Module');		
define('CONFIG_DIR', 'config');		
define('TEMPLETE_DIR', 'Templete');	
define('CONTROL_DIR','Control');	



defined("CACHE_PATH") || define('CACHE_PATH', Runtime . '/' . CACHE_DIR);
defined("LOG_PATH") || define('LOG_PATH', Runtime . '/' . LOG_DIR);
defined("TPL_PATH") || define('TPL_PATH', Runtime . '/' . TPL_DIR);

defined("TEMPLETE_PATH") || define('TEMPLETE_PATH', APP_PATH . '/' . TEMPLETE_DIR);
defined("CONFIG_PATH") || define('CONFIG_PATH', APP_PATH . '/' . CONFIG_DIR);
defined("MODULE_PATH") || define('MODULE_PATH', APP_PATH . '/' . MODULE_DIR);
defined("CONTROL_PATH") || define('CONTROL_PATH', APP_PATH . '/' . CONTROL_DIR);


 

function output($arr=array()){
	echo '<pre>';
	print_r($arr);
	echo '</pre>';
	echo '<hr />';
	echo $arr? 'true':'false';
}

function error($msg){
	if(C('DEBUG')){
		if(!is_array($msg)){
			$calltrace = debug_backtrace();	
			$e['message'] = $msg;
			$info = '';
			foreach ($calltrace as $v){
				$file = isset($v['file']) ? $v['file'] : '';
				$line = isset($v['line']) ? ' [ ' . $v['line']  . ' ] ' : '';
				$class = isset($v['class']) ? $v['class'] : '';
				$type = isset($v['type']) ? $v['type'] : '';
				$function = isset($v['function']) ? $v['function'] : '';
				$info .= $file . $line . $class . $type . $function . "<br/>";
			}
			$e['info'] = $info;
			output($e);exit();
		}else{
			
			$e = $msg;
		}
	}else{
		$e['message'] = C('ERROR_MESSAGE');
	}
	include C('DEBUG_TPL');
	die();
}

function otherErr($e){
	if(C('DEBUG') && C('DEBUG_OTHER')){
		$time = number_format(microtime()-Debug::start('app_start'),4);
		$memory = memory_get_usage();
		$message = $e[1];
		$file = $e[2];
		$line = $e[3];
		$msg = "<h1 style='width:895px;font-size:13px;background-color:#333;height:20px;line-height:1.8em;padding:2px;margin-top:20px;color:#fff'>
			异常错误: $message
		</h1>
		<div>
			<table style='border:solid 1px #dcdcdc;width:900px'>
				<tr>
					<td> 时间 </td> 
					<td> 内存 </td>
					<td> 文件 </td>
					<td> 行号 </td>
				</tr>
				<tr>
					<td> $time </td> 
					<td> $memory </td> 
					<td> $file </td> 
					<td> $line </td>
				</tr>
			</table>
		</div>";
		echo $msg;
	}
}

function errtype($type){
	switch($type){
		case '1':
			return 'E_ERROR';	
		case '2':
			return 'E_WARNING';		
		case '4':
			return 'E_PARSE';	
		case '8':
			return 'E_NOTICE';	
		case '16':
			return 'E_CORE_ERROR';	
		case '32':
			return 'E_CORE_WARNING';	
		case '64':
			return 'E_COMPILE_ERROR';	
		case '128':
			return 'E_COMPILE_WARNING';	
		case '256':
			return 'E_USER_ERROR';	
		case '512':
			return 'E_USER_WARNING';	
		case '1024':
			return 'E_USER_NOTICE';		
		case '2048':
			return 'E_STRICT';		
		case '4096':
			return 'E_RECOVERABLE_ERROR';	
		case '8192':
			return 'E_DEPRECATED';	
		case '16384':
			return 'E_USER_DEPRECATED';	
	}
	return $type;
}
function loadfile($file=''){
	static $files = array();
	if(empty($file)){
		return $files;
	}
	$filePath = realpath($file) ? realpath($file) : $file;
	if(isset($files[$filePath])){
		return $files[$filePath];
	}
	if(!is_file($filePath)){
		error('文件  ' . $filePath . ' 不存在');
	}
	require $filePath;
	$files[$filePath] = true;
	return $files[$filePath];
	}

function C($name,$value=null){
	static $config = array();
	if(is_null($name)){
		return $config;
	}
	if(is_string($name)){
		$name = strtolower($name);
		if(!strstr($name,'.')){
			if(is_null($value)){
				return isset($config[$name]) ? $config[$name] : null;
			}else{
				$config[$name] = $value;
				return ;
			}
		}
		$name = explode('.', $name);
		if(is_null($value)){
			return isset($config[$name][0][1]) ? $config[$name][0][1] : null; 
		}else {
			$config[$name][0][1] = $value;
			return ;
		}
	}
	if(is_array($name)){
		$config = array_merge($config,array_change_key_case($name));	
		return true;
	}
}

function B($class,$method=null,$args=array()){
	static $result = array();
	if(strpos($class,C('CONTROL_FIX')) === false){
		$class .= C('CONTROL_FIX');
	}
	$name = empty($args) ? $class . $method : $class . $method . _md5($args);
	if(!isset($result[$name])){
		$obj = new $class();
		if(!is_null($method) && method_exists($obj, $method)){
			if(!empty($args)){
				$result[$name] = call_user_func_array(array($obj,$method), array($args));
			}else{
				$result[$name] = $obj->$method();
			}
		}else {
			$result[$name] = $obj;
		}
	}
	return $result[$name];
}
function A($control){
	static $controls = array();
	if(strstr($control , '.' )){
		$tmp = explode('.', $control);
		$module = $tmp[0];
		$control = $tmp[1];
	}else{
		$module = MODULE;
	}
	$control = $control . C('CONTROL_FIX');
	if(isset($controls[$control])){
		return $controls[$control];
	}
	$controlpath = APP_PATH . '/' . $module . '/' . $control . C('CLASS_FIX') . '.php';
	if(loadfile($controlpath)){
		if(class_exists($control)){
			$controls[$control] = new $control();
			return $controls[$control];
		}else{
			error('非法模块: ' . $control);
		}
	}else{
			error('文件不存在: ' . $controlpath);
	}
}
function M($table){
	$table = C('table_fix') ? C('table_fix') . $table : $table;
	$obj = new Model($table);
	if(!$obj)error('不存在表: ' . $table);
	return $obj;
}

function _md5($var){
	return md5(serialize($var));
}
function del_space($filename){
	$data = file_get_contents($filename);
	$data = substr($data,0,5) == '<?php' ? substr($data, 5) : $data;
	$data = substr($data,-2) == '?>' ? substr($data, 0,-2) : $data;
	$preg_arr = array('/\/\*.*?\*\/\s*/is','/\/\/.*?[\r\n]/is');
	$data = preg_replace($preg_arr, '' , $data);
	return $data;
}



 
class APP{
	static $module = '';		
	static $control = '';	
	static $action = '';		
	
	public static function run(){
		spl_autoload_register(array(__CLASS__,'autoload'));
		
		
		set_error_handler(array(__CLASS__,"err"));
		
		register_shutdown_function(array(__CLASS__,'fatelErr'));
		
		set_exception_handler(array(__CLASS__,'xphp_exception'));
		
		
		define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc() ? true : false );	
		if(C('DATE_TIMEZONE_SET')){		
			date_default_timezone_set(C('DATE_TIMEZONE_SET'));
		}
		
		self::config();
		
		if(C('DEBUG')){
			Debug::start('app_start');
		}
		
		self::init();
		if(C('DEBUG')){
			
			Debug::show('app_start','app_end');
		}
		
		Log::save();
	}
	
	
	static public function autoload($classname){
		static $autofiles = array();
		if(strpos($classname,C('CONTROL_FIX'))>0){
			$file = APP_PATH . '/' . C('CONTROL_FIX') . '/' . $classname . C('CLASS_FIX') . '.php';
			return loadfile($file);
		}
		if(strpos($classname,C('MODEL_FIX'))>0){
			$file = APP_PATH . '/' . C('MODEL_FIX') . '/' . $classname . C('CLASS_FIX') . '.php';
			return loadfile($file);
		}
		$file = FRAME . '/Libs/Bin/' . $classname . '.class.php';
		if(!loadfile($file)){
			error('错误: not found ' . $classname . '类 , 控制器必须由A()|B()方法获取');
		}
	}
	
	
	static function init(){
		
		URL::parseUrl();
		$file = APP_PATH . '/' . C('DEFAULT_GROUP') . '/' . CONTROL . C('CONTROL_FIX') . C('CLASS_FIX') . '.php';
		if(loadfile($file)){
			
			$obj = B(CONTROL);
			$action = ACTION;
			if(!method_exists($obj, $action)){
				error('模块：' . MODULE . ' 控制器： ' . CONTROL . ' 非法调用: ' . $action);
			}
			$obj->$action();
			
 			
		}
	}
	
	
	private static function __group(){
		if(isset($_GET['m'])  && !empty($_GET['m'])){
			return $_GET['m'];
		}
		return C('DEFAULT_GROUP');
	}
	
	private static function __control(){
		if(isset($_GET['c'])  && !empty($_GET['c'])){
			return $_GET['c'];
		}
		return C('DEFAULT_CONTROL');
	}
	
	private static function __action(){
		if(isset($_GET['a'])  && !empty($_GET['a'])){
			return $_GET['a'];
		}
		return C('DEFAULT_ACTION');
	}
	
	
	
	static function config(){
		$config_file = CONFIG_PATH . '/conf.php';
		if(is_file($config_file)){
			C(require $config_file);
		}
	}
	
	static protected function setMsg($errorMsg){
		if(is_array($errorMsg)){
			return "{$errorMsg['errno']} : <strong>{$errorMsg['errstr']}</strong> File: {$errorMsg['errfile']} 第  {$errorMsg['errline']} 行 ";
		}
		return false;
	}
	
	
	public static function err($errno,$errstr,$errfile,$errline){
		$errorMsg['errstr'] = $errstr ? $errstr : '';
		$errorMsg['errfile'] = $errfile ? $errfile : '';
		$errorMsg['errline'] = $errline ? $errline : '';
		$errorMsg['errno'] = $errno ? errtype($errno) : 0;
		$emsg = self::setMsg($errorMsg);
		switch ($errorMsg['errno']){
			case E_ERROR:	
				error($emsg);
				Log::write($emsg);
				break;
			case E_USER_ERROR:
				error($emsg);
				
				Log::write($emsg);
				break;
			default:
				Log::set($emsg,$errorMsg['errno']);
				otherErr(func_get_args());
		}
	}
	
	
	static function xphp_exception($e){
		error($e->show());
	}
	
	
 	static public function fatelErr(){
			$error = error_get_last();	
			if($error){
				$error_type = $error['type'];
				$error['_type_arg_'] = errtype($error_type);
				$err = array();
				if(in_array($error_type, array(1,16,64,256))){	
					$err['message'] =  "<br />错误代号: " . $error['_type_arg_'];
					$err['message'] .= "<br />异常文件: " . $error['file'];
					$err['message'] .= "<br />异常行号: " . $error['line'];
					$err['message'] .= "<br />异常信息: " . $error['message'];
					error($err);
				}
				otherErr($error['type'],$error['message'],$error['file'],$error['line']);
			}
 	}
	
}
C(require FRAME . '/Libs/Conf/config.php');?>