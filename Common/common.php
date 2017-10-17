<?php 

/**
 * 输出测试
 */
function output($arr=array()){
	echo '<pre>';
	print_r($arr);
	echo '</pre>';
	echo '<hr />';
	echo $arr? 'true':'false';
}

/**
 * 全局致命错误处理函数  会停止后面运行  die()
 */
function error($msg){
	if(C('DEBUG')){
		if(!is_array($msg)){
			$calltrace = debug_backtrace();	//回调异常 加载的文件都会被显示出来
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
			//如果是数组就表明是throw exception 跑出来的错误
			$e = $msg;
		}
	}else{
		$e['message'] = C('ERROR_MESSAGE');
	}
	include C('DEBUG_TPL');
	die();
}

/*
 * 非致命错误处理
 */
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

/*
 * 错误级别
 */
function errtype($type){
	switch($type){
		case '1':
			return 'E_ERROR';	//致命的运行错误
		case '2':
			return 'E_WARNING';		//运行时警告
		case '4':
			return 'E_PARSE';	//编译时解析错误
		case '8':
			return 'E_NOTICE';	//运行时提醒
		case '16':
			return 'E_CORE_ERROR';	//PHP启动时初始化过程中的致命错误
		case '32':
			return 'E_CORE_WARNING';	//PHP启动时初始化过程中的警告
		case '64':
			return 'E_COMPILE_ERROR';	//编译时致命性错
		case '128':
			return 'E_COMPILE_WARNING';	//编译时警告(非致命性错)
		case '256':
			return 'E_USER_ERROR';	//用户自定义的错误消息    trigger_error('必须为整数',E_USER_ERROR)
		case '512':
			return 'E_USER_WARNING';	//用户自定义的警告消息
		case '1024':
			return 'E_USER_NOTICE';		// 用户自定义的提醒消息
		case '2048':
			return 'E_STRICT';		//编码标准化警告   版本兼容性错误
		case '4096':
			return 'E_RECOVERABLE_ERROR';	//捕致命错误	set_error_handler
		case '8192':
			return 'E_DEPRECATED';	//运行时通知,启用后将会对在未来版本中可能无法正常工作的代码给出警告
		case '16384':
			return 'E_USER_DEPRECATED';	//用户产少的警告信息,trigger_error()产生的
	}
	return $type;
}
/*
 * 载入文件
 */
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
	/**
	if(!isset($files[$file])){
		if(!is_file($file)){
			$msg = "<span style='color:#f00;'>{$file}文件不存在</span>";
		}else{
			require $file;
			$files[$file] = true;
			$msg = "<span style='color:#f00;'>{$file}文件载入成功</span>";
		}
		if(C('DEBUG')){
			call_user_func_array(array("Debug","msg"), array($msg));	
		}
		return $files[$file];
	}
	**/
}

/**
 * 获取配置文件
 */
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
		$config = array_merge($config,array_change_key_case($name));	//把数组键名全改为小写
		return true;
	}
}

/**
 * 实例化控制器	B('Test');
 */
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
/*
 * 实例化模型对象  
 * A('Admin.TestControl');
 */
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
/*
 * 获取表对象 | 实例化模型 
 */
function M($table){
	$table = C('table_fix') ? C('table_fix') . $table : $table;
	$obj = new Model($table);
	if(!$obj)error('不存在表: ' . $table);
	return $obj;
}

/*
 * 生成唯一序列号
 */
function _md5($var){
	return md5(serialize($var));
}
/*
 * 格式化内容，去掉空白
 */
function del_space($filename){
	$data = file_get_contents($filename);
	$data = substr($data,0,5) == '<?php' ? substr($data, 5) : $data;
	$data = substr($data,-2) == '?>' ? substr($data, 0,-2) : $data;
	$preg_arr = array('/\/\*.*?\*\/\s*/is','/\/\/.*?[\r\n]/is');
	$data = preg_replace($preg_arr, '' , $data);
	return $data;
}



?>