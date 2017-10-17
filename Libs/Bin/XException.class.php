<?php 
/*
 * 自定义异常类
 */
class XException extends Exception{
	public function __construct($message,$code=0){
		parent::__construct($message,$code);
	}
	
	public function show(){
		$trace = $this->getTrace();
		$error['message'] = "异常信息: " . $this->getMessage();
		$error['message'] .= "<br />异常文件: " . $this->file . ' [ ' . $this->line . ' ] ';
		$error['message'] .= "<br />异常类名: " . $trace[0]['class'];
		$error['message'] .= "<br />异常类型: " . $trace[0]['type'];
		$error['message'] .= "<br />异常方法: " . $trace[0]['function'] . '()';
		array_shift($trace);
		$info = '';
		foreach ($trace as $v){
			$class = isset($v['class']) ? $v['class'] : '';
			$type = isset($v['type']) ? $v['type'] : '';
			$file = isset($v['file']) ? $v['file'] : '';
			$info .= $file . '\t' . $class . $type . $v['function'] . '<br />';
		}
		$error['info'] = $info;
		Log::write($error['message']);
		return $error;
	}
	
	
}

?>