<?php
/*
 * 文件处理类
 */ 
class File{
	//转换目录为标准结构
	public static function path($path){
		$dirname = str_ireplace('\\', '/', $path);
		return substr($path, -1) == '/' ? $path : $path . '/';
	}
	//获取文件扩展名
	public static function getExt($file){
		return substr(strrchr($file , '.'),1);
	}
	//获取目录内容
	static public function tree($path,$exts='',$son=0,$list=array()){
		$path = self::path($path);
		if(is_array($exts)){
			implode('|', $exts);
		}
		static $id = 0;
		foreach (glob($path . '*') as $v){
			$id++;
			if(!$exts || preg_match("/\.($exts)/i", $v)){
				$list[$id]['name'] = basename($v);
				$list[$id]['type'] = filetype($v);
				$list[$id]['ctime'] = filectime($v);
				$list[$id]['atime'] = fileatime($v);
				$list[$id]['filesize'] = number_format((filesize($v)/1024),2) . 'Kb';
				$list[$id]['iswrite'] = is_writable($v);
				$list[$id]['isread'] = is_readable($v);
			}
			if($son){
				if(is_dir($v)){
					$list = self::tree($v,$exts,$son,$list);
				}
			}
		}
		return $list;
	}
	
	//只获取目录结构
	static function treePath($path,$pid=0,$son=0,$list=array()){
		$path = self::path($path);
		static $id = 0;
		foreach (glob($path . '*') as $v){
			if(is_dir($v)){
				$id++;
				$list[$id]['id'] = $id;
				$list[$id]['pid'] = $pid;
				$list[$id]['name'] = basename($v);
				$list[$id]['path'] = realpath($v);
				if ($son) {
					$list = self::tree($v,'',$son,$list);
				}
			}
		}
		return $list;
	}
	
	//删除目录
	static public function delPath($path){
		$path = self::path($path);
		if(!is_dir($path))return false;
		foreach (glob($path . '*') as $v){
			is_dir($v) ? self::delPath($v) : unlink($v);
		}
		rmdir($path);
	}
	
	//支持层级的目录结构创建
	static function create($path,$auth='0777'){
		$path = self::path($path);
		if(is_dir($path)){	//如果已存在,直接返回真
			return true;
		}
		$paths = explode('/', $path);
		$dir = '';
		foreach ($paths as $v){
			$dir .= $v . '/';
			if(is_dir($dir)){
				continue;
			}
			mkdir($dir,$auth);
		}
		return is_dir($path);
	}
	
	//复制目录内容
	static public function copy($oldPath,$newPath){
		$oldPath = self::path($oldPath);
		$newPath = self::path($newPath);
		if(!is_dir($oldPath)){
			error('复制失败: ' . $oldPath . ' 不存在');
		}
		if(!is_dir($newPath)){
			self::create($newPath);
		}
		foreach (glob($oldPath . '*') as $v){
			$toFile = $newPath . basename($v);
			if(is_file($toFile))continue;
			if(is_dir($v)){
				self::copy($v, $toFile);
			}else{
				copy($v,$toFile);
				chmod($toFile, '0777');
			}
		}
		return true;
	}
	
}


?>