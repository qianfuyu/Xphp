<?php 
/*
 * 处理化配置选项
 */
return array(
	'SHOW_TIME' => 1,	//显示运行时间
	'DEBUG' => true,		//开启DEBUG
	'NOTICE_SHOW'=>1,	//开启提示性错误
	'DEBUG_OTHER' => true,	//未知错误处理
	'DEBUG_TPL' => FRAME . '/tpl/debug.tpl.php', 	//错误模板
	"ERROR_MESSAGE" => "页面出错",	//配置debug为false的统一报错信息
	
	//项目配置项
	'DEFAULT_GROUP'=>'Control',
	'DEFAULT_CONTROL'=>'Index',
	'DEFAULT_ACTION'=>'index',
	'CLASS_FIX'=>'.class',
	//定义模型,视图,控制器后缀
	'CONTROL_FIX'=>'Control',
	'MODEL_FIX'=>'Model',
	'VIEW_FIX'=>'View',
			
	//地址栏URL分隔依据
	'VAR_MODULE'=>'m',	//模块
	'VAR_CONTROL'=>'c',	//控制器
	'VAR_ACTION'=>'a',	//方法
	
	//优化页面文件后缀
	
	//初始化配置
	'DATE_TIMEZONE_SET' => 'PRC',
		
	//自动加载目录
	'AUTO_LOAD_PATH'=>array('Admin','View'),
		
	//日志处理
	'LOG_START'=>true,	//是否开启日志记录
	'LOG_TYPE'=>array('SQL','E_USER_WARNING','E_WARNING'),	//日志类型
	'LOG_SIZE'=>2000000,	//日志文件大小 2M
	
	//pathinfo
	'PATHINFO_DLI'=>'/',	//pathinfo分隔符
	'PATHINFO_VAR'=>'q',	//兼容模式的分隔符	$_GET变量
	'PATHINFO_HTML' => '.html',  //伪静态后缀
		
	//微信服务好配置	
	'WechatConf'=>array(
		'AppID'=>'wxe048b71683bfe49e',
		'AppSecret'=>'bcaf45efa960b6cd9d47a4c5885ba759',
	),
	//accessToken凭证缓存文件
	'accessToken_cacha_file' => LOG_PATH . 'accessToken_cacha_file',
		
		
);

?>