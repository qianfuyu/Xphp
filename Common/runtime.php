<?php 
//加载核心文件 | 创建相关目录
function runtime(){
	$files = require_once FRAME . '/Common/file.php';
	foreach ($files as $v){
		if(is_file($v)){
			require $v;
		}
	}
 	mkdirs();
 	//框架常规配置项
 	C(require FRAME . '/Libs/Conf/config.php');
 	$data = '';
 	foreach ($files as $v){
 		$data .= del_space($v);
 	}
 	$data = "<?php" . $data . "C(require FRAME . '/Libs/Conf/config.php');" . "?>";
	file_put_contents(Runtime . '/run.php', $data); 	
	friend_show();
}

//创建目录列表
function mkdirs(){
	if(!is_dir(Runtime)){
		mkdir(Runtime,0777,true);
	}
	if(!is_writable(Runtime)){
		error("目录没有写入权限");
	}
	//创建核心目录
	is_dir(CACHE_PATH) || mkdir(CACHE_PATH,0777);
	is_dir(LOG_PATH) || mkdir(LOG_PATH,0777);
	is_dir(TPL_PATH) || mkdir(TPL_PATH,0777);

	is_dir(TEMPLETE_PATH) || mkdir(TEMPLETE_PATH,0777);
	is_dir(CONFIG_PATH) || mkdir(CONFIG_PATH,0777);
	is_dir(MODULE_PATH) || mkdir(MODULE_PATH,0777);
	is_dir(CONTROL_PATH) || mkdir(CONTROL_PATH,0777);
	
	
}

//创建友好测试页
function friend_show(){
	$friend_path = APP_PATH . '/' . C('DEFAULT_GROUP');
	$friend_file = $friend_path . '/' . C('DEFAULT_CONTROL') . C('CONTROL_FIX') . C('CLASS_FIX') . '.php';
	if(!$friend_path){
		mkdir($friend_path,0777);
	}
	if(!is_file($friend_file)){
		die('友好页面不存在');
	}
}


?>