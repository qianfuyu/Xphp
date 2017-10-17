<?php 
/*
 * 路由
 */
class URL{
	//保存PATHINFO信息
	static $pathinfo;
	//解析URL
	static public function parseUrl(){
		if(self::pathInfo() != false){
			$info = explode(C('PATHINFO_DLI'), self::$pathinfo);
			if ($info[0] == '') {
				array_shift($info);
			}
			if($info[0] != C('VAR_MODULE')){
				//$get['m'] = isset($info[0]) ? $info[0] : 'Index';
				//array_shift($info);
				$get['c'] = isset($info[0]) ? $info[0] : 'Index';
				array_shift($info);
				$get['a'] = isset($info[0]) ? $info[0] : 'index';
				array_shift($info);
			}
			$count = count($info);
			$count = ($count < 2) ? 0 : $count;
			for ($i=0;$i<$count;$i+=2){
				$get[$info[$i]] = $info[$i+1];
			}
			$_GET=$get;
		}
		//define('MODULE', isset($_GET['m']) ? $_GET['m'] : C('DEFAULT_GROUP'));
		define('MODULE','Control');
		define('CONTROL', empty($_GET['c']) ?  C('DEFAULT_CONTROL') : $_GET['c']);
		define('ACTION', empty($_GET['a']) ? C('DEFAULT_ACTION') : $_GET['a']);
	} 	
	
	//解析pathinfo
	static public function pathInfo(){
		//获取pathinfo变量
		if( isset($_GET[C('PATHINFO_VAR')]) ){
			$pathinfo = $_GET[C('PATHINFO_VAR')];
		}elseif(!empty($_SERVER['REQUEST_URI'])){
			$pathinfo = $_SERVER['REQUEST_URI'];
		}else{
			return false;
		}
		$pathinfo_html = '.' . trim( C('PATHINFO_HTML'),'.' );
		$pathinfo = str_ireplace($pathinfo_html, '', $pathinfo);
		if(stripos($pathinfo, C('PATHINFO_DLI')) === false){
			return false;
		}
		self::$pathinfo = $pathinfo;
		return true;
	}
	
}

?>