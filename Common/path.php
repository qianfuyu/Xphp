<?php 
/*
 * 框架目录结构 | 用于创建项目和框架所需文件
 */

//框架结构
define('CACHE_DIR', 'cache');		//缓存目录
define('LOG_DIR', 'log');			//日志目录
define('TPL_DIR', 'tpl');			//模板编译目录
define('MODULE_DIR','Module');		//项目模型目录
define('CONFIG_DIR', 'config');		//项目配置目录
define('TEMPLETE_DIR', 'Templete');	//项目视图目录
define('CONTROL_DIR','Control');	//项目控制器目录



defined("CACHE_PATH") || define('CACHE_PATH', Runtime . '/' . CACHE_DIR);
defined("LOG_PATH") || define('LOG_PATH', Runtime . '/' . LOG_DIR);
defined("TPL_PATH") || define('TPL_PATH', Runtime . '/' . TPL_DIR);

defined("TEMPLETE_PATH") || define('TEMPLETE_PATH', APP_PATH . '/' . TEMPLETE_DIR);
defined("CONFIG_PATH") || define('CONFIG_PATH', APP_PATH . '/' . CONFIG_DIR);
defined("MODULE_PATH") || define('MODULE_PATH', APP_PATH . '/' . MODULE_DIR);
defined("CONTROL_PATH") || define('CONTROL_PATH', APP_PATH . '/' . CONTROL_DIR);


?>