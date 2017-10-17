<?php 
/*
 * 项目开始类
 */
class APP{
	static $module = '';		//模块
	static $control = '';	//控制器
	static $action = '';		//方法
	//项目运行
	public static function run(){
		spl_autoload_register(array(__CLASS__,'autoload'));
		
		// [ 注意： 必须在路由前面  ]   程序错误处理        trigger_error() 接管
		set_error_handler(array(__CLASS__,"err"));
		// 捕获其他异常写入文件
		register_shutdown_function(array(__CLASS__,'fatelErr'));
		// 异常处理
		set_exception_handler(array(__CLASS__,'xphp_exception'));
		
		//初始化配置
		define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc() ? true : false );	//是否转义
		if(C('DATE_TIMEZONE_SET')){		//设置时区
			date_default_timezone_set(C('DATE_TIMEZONE_SET'));
		}
		//载入项目配置文件
		self::config();
		//调试开始时间
		if(C('DEBUG')){
			Debug::start('app_start');
		}
		//路由找对象
		self::init();
		if(C('DEBUG')){
			//Debug::show();
			Debug::show('app_start','app_end');
		}
		//所有非致命错误记录到日志
		Log::save();
	}
	
	//自动加载类文件
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
		/** if(strpos($classname,C('VIEW_FIX'))>0){
			$file = APP_PATH . '/' . C('VIEW_FIX') . '/' . $classname . C('CLASS_FIX') . '.php';
			return loadfile($file);
		} **/
		$file = FRAME . '/Libs/Bin/' . $classname . '.class.php';
		if(!loadfile($file)){
			error('错误: not found ' . $classname . '类 , 控制器必须由A()|B()方法获取');
		}
	}
	
	//初始化配置	
	static function init(){
		/*
		 * self::$module = $module = ucfirst(self::__group());	//模块
		 * self::$control = $control = ucfirst(self::__control());	//控制器
		 * self::$action = $action = self::__action();	//方法
		 * $control_file = self::$control . C('CONTROL_FIX') . C('CLASS_FIX') . '.php';
		 * $file = APP_PATH . '/' . self::$module . '/' . $control_file;
		*/
		/*
		define('MODULE',isset($_GET[C('VAR_MODULE')]) ? $_GET[C('VAR_MODULE')] : C('DEFAULT_GROUP'));
		define('CONTROL',isset($_GET[C('VAR_CONTROL')]) ? $_GET[C('VAR_CONTROL')] : C('DEFAULT_CONTROL'));
		define('ACTION',isset($_GET[C('VAR_ACTION')]) ? $_GET[C('VAR_ACTION')] : C('DEFAULT_ACTION'));
		$action = ACTION;
		*/
		//调用路由,处理URL
		URL::parseUrl();
		$file = APP_PATH . '/' . C('DEFAULT_GROUP') . '/' . CONTROL . C('CONTROL_FIX') . C('CLASS_FIX') . '.php';
		if(loadfile($file)){
			//$obj = A(MODULE . '.' . CONTROL);
			$obj = B(CONTROL);
			$action = ACTION;
			if(!method_exists($obj, $action)){
				error('模块：' . MODULE . ' 控制器： ' . CONTROL . ' 非法调用: ' . $action);
			}
			$obj->$action();
			//$control = B(CONTROL . C('CONTROL_FIX'));
 			//$control->$action();
		}
	}
	
	//获取分组
	private static function __group(){
		if(isset($_GET['m'])  && !empty($_GET['m'])){
			return $_GET['m'];
		}
		return C('DEFAULT_GROUP');
	}
	//获取模块
	private static function __control(){
		if(isset($_GET['c'])  && !empty($_GET['c'])){
			return $_GET['c'];
		}
		return C('DEFAULT_CONTROL');
	}
	//获取方法
	private static function __action(){
		if(isset($_GET['a'])  && !empty($_GET['a'])){
			return $_GET['a'];
		}
		return C('DEFAULT_ACTION');
	}
	
	
	//处理化配置文件处理
	static function config(){
		$config_file = CONFIG_PATH . '/conf.php';
		if(is_file($config_file)){
			C(require $config_file);
		}
	}
	//构建错误信息
	static protected function setMsg($errorMsg){
		if(is_array($errorMsg)){
			return "{$errorMsg['errno']} : <strong>{$errorMsg['errstr']}</strong> File: {$errorMsg['errfile']} 第  {$errorMsg['errline']} 行 ";
		}
		return false;
	}
	
	//错误处理函数	trigger_error('我是致命错误',E_ERROR);
	public static function err($errno,$errstr,$errfile,$errline){
		$errorMsg['errstr'] = $errstr ? $errstr : '';
		$errorMsg['errfile'] = $errfile ? $errfile : '';
		$errorMsg['errline'] = $errline ? $errline : '';
		$errorMsg['errno'] = $errno ? errtype($errno) : 0;
		$emsg = self::setMsg($errorMsg);
		switch ($errorMsg['errno']){
			case E_ERROR:	//$errmsg = "E_ERROR: [ $errno ] <strong>$errstr</strong> File: $errfile" . " [ $errline ] 行";
				error($emsg);
				Log::write($emsg);
				break;
			case E_USER_ERROR:
				error($emsg);
				//致敏性错误直接写入日志文件		Log::write("[ ERROR: ] [$errno] <strong>$errstr</strong> File: $errfile");
				Log::write($emsg);
				break;
			default:
				Log::set($emsg,$errorMsg['errno']);
				otherErr(func_get_args());
		}
	}
	
	//异常处理	throw new XExcption('我是异常信息');
	static function xphp_exception($e){
		error($e->show());
	}
	
	//其他错误处理  		echo 'aa'  没有';'明显报错，截取这个错误重新渲染
 	static public function fatelErr(){
			$error = error_get_last();	//获取错误，包含文件语法错误等
			if($error){
				$error_type = $error['type'];
				$error['_type_arg_'] = errtype($error_type);
				$err = array();
				if(in_array($error_type, array(1,16,64,256))){	//致命错误发送邮件
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
?>