<?php
// +----------------------------------------------------------------------
// | Xphp by ZYJ
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://www.golxkj.com
// +----------------------------------------------------------------------
// | Version ( 0.0.1 )
// +----------------------------------------------------------------------
// | Author: ZYJ <zyjqianfuyu@163.com>
// +----------------------------------------------------------------------

function run_time($start,$end='',$decimial=3){
	static $times = array();
	if($end!=''){
		$times[$end] = microtime();
		return number_format($times[$end] - $times[$start],$decimial);
	}
	$times[$start]=microtime();
}
run_time('start');
//框架名称
defined('APP_NAME') || define('APP_NAME', 'Xphp');
//框架主目录
if (!defined('FRAME')) {
	define('FRAME',dirname($_SERVER['SCRIPT_FILENAME']) . '/' . APP_NAME);
}
//Runtime目录
define('Runtime', FRAME . '/Runtime');
//加载编译文件
$runtime = Runtime . '/run.php';
if(!is_file($runtime)){
	require $runtime;
}else{
	include FRAME . '/Common/runtime.php';
	runtime();
}
APP::run();
echo "<br />项目运行时间:[ " . run_time('start','end',4) . ' ]秒';

