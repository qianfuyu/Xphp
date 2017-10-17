<?php 
class Debug{
	/**
	static $debug = array();
	static function show(){
		self::$debug[] = '运行时间： ' . run_time('start','end') . ' 秒';	//保存载入文件
		echo "<div style='border:solid 2px #dcdcdc;width:680px;margin:20px;padding:10px;font-size:16px'><ul style='list-style:none;padding:0px;margin:0px'>";
		foreach (self::$debug as $v){
			echo '<li>' . $v . '</li>';
		}
		echo "</ul></div>";
	}
	
	//配置显示信息
	static function msg($msg){
		self::$debug[] = $msg;
	}
	**/
	//运行时间
	static $runtime;
	//内存占用
	static $memory;
	//内存峰值
	static $memorymax;
	//调试开始
	static function start($start){
		self::$runtime[$start] = microtime(true);
		self::$memory[$start] = memory_get_usage();
		self::$memorymax[$start] = memory_get_peak_usage();
	}
	//项目运行时间
	static function runtime($start,$end='',$decimals=4){
		if(!isset(self::$runtime[$start])){
			error('必须设置项目起点');
		}
		if(empty(self::$runtime[$end])){
			self::$runtime[$end] = microtime(true);
			return number_format(self::$runtime[$end]-self::$runtime[$start],$decimals);
		}
	}
	//内存占用峰值
	static function memorymax($start,$end=''){
		if(!isset(self::$memorymax[$start])){
			return false;
		}
		if(!empty($end)){
			self::$memorymax[$end] = memory_get_peak_usage();
		}
		return max(self::$memorymax[$start],self::$memorymax[$end]);
	}
	//显示项目结果
	static function show($start,$end){
		$e['message'] = "项目运行时间  " . self::runtime($start,$end) . ' 内存峰值 ' . self::memorymax($start,$end)/1024 . 'KB';
		$load_file_list = loadfile();	//加载的所有文件
		$info = '';
		$i = 1;
		foreach ($load_file_list as $k => $v){
			$info .= '[' . $i++ . ']' . $k . '<br />';
		}
		$e['info'] = $info;
		include C('DEBUG_TPL');
	}
}

?>