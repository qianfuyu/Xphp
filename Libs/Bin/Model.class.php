<?php
// +----------------------------------------------------------------------
// | Xphp by ZYJ
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://lw.ifdata.cn
// +----------------------------------------------------------------------
// | Version ( 0.0.1 )
// +----------------------------------------------------------------------
// | Author: ZYJ <zyjqianfuyu@163.com>
// +----------------------------------------------------------------------

/**
 * Xphp Model模型类
 */
class Model {
	protected $_conn = null;	 //链接资源
	protected $_config = array();	//数据库配置
	protected $_table = '';	//数据库表名
    protected $_pk = '';	 //主键名称
    protected $_opt = array();	 //选项数组
	protected $_error = '';	//数据库错误
	protected $_sql = '';	//执行sql
	
    
	/**
	 * 构造方法
	 * @param $table string 数据表名称
	 * @return $db mixed 数据库操作资源
	 */
	
	public function __construct($table){
		$this->_table = $table ? $table : error('表名不能为空');
		$this->_opt['field'] = isset($this->_opt['field']) ?  $this->_opt['field'] : ' * ';
		$this->_getConfig();
		$this->_conn = new mysqli($this->_config['myhost'],$this->_config['myuser'],$this->_config['mypass'],$this->_config['dbname']);
		if($this->_conn->connect_errno){
			error($this->_conn->error);
		}
		$this->_conn->query('set names ' . $this->_config['mychar']);
		$this->_getFields();
	}
	
	//sql错误处理函数
	protected function _errorMsg($sql,$error){
		var_dump($sql);
		var_dump($error);
		die();
	}
	
	//设置配置
	protected function _getConfig(){
		if(empty($this->_config)){
			$this->_config = array(
				'myhost'=>'localhost',
				'myuser'=>'root',
				'mypass'=>'root',
				'dbname'=>'myfar',
				'mychar'=>'utf8'
			);
		}
	}

	//获取表字段
	protected function _getFields(){
		$result = $this->_conn->query("desc {$this->_table}");	//注意面向对象风格,返回的是对象类型
		if(!$result){
			throw new XException('表不存在： ' . $this->_table);
		}
		$fields = array();
		while( ($row = $result->fetch_assoc()) != false ){
			$fields[] = $row['Field'];
		}
		$this->_pk = $fields[0];
		$this->_opt['fields'] = $fields;
	}
	
	//获取查询字段
	public function field($field=''){		
		if(empty($field)){
			$this->_opt['field'] = '*';
		}
		if($field){
			$fieldArr = is_string($field) ? explode(',', $field) : $field;
			$tmp = '';
			foreach($fieldArr as $v){
				if(in_array($v,$this->_opt['fields'])){
					$tmp .= '`' . $v . '`,';
				}
			}
			$this->_opt['field'] = rtrim($tmp,',');
		}
		return $this;
	}
	
	//封装where
	public function where($where='',$type='and'){
		if(!in_array($type, array('and','or'))){
			die();	
		}
		if(is_array($where)){
			$tmp = '';
			foreach ($where as $k => $v){
				if(in_array($k,$this->_opt['fields'])){
					$tmp .= $k . ' = ' . "'$v'" . " $type ";
				}
			}
			$where = substr($tmp,0,-4);
			$this->_opt['where'] = $tmp ? "where {$where}" : '';
		}
		if(is_string($where)){
			$this->_opt['where'] = "where {$where}";
		}
		if(empty($where)){
			$this->_opt['where'] = '';
		}
		return $this;
	}
	
	//封装limit	1   12,3 select * from test limit 12,3
	public function limit($limit){
		if (!empty($limit)) {
			$this->_opt['limit'] = "limit {$limit}";
		}else{
			$this->_opt['limit'] = '';
		}
		return $this;
	}
	
	//封装order
	public function order($order){
		$this->_opt['order'] = is_string($order) ? "order by {$order}" : '';
		return $this;
	}
	
	//封装group
	public function group($group){
		$this->_opt['group'] = is_string($group) ? "group by {$group}" : '';
		return $this;
	}	
	
	//封装having
	public function having($having){
		$this->_opt['having'] = is_string($having) ? "having {$having}" : '';
		return $this;
	}
	
	//封装select
	public function select($id=0){
		if($id){
			$ids = is_string($id) ? explode(',', $id) : $id;
			$where = " where {$this->_pk} in (";
			foreach ($ids as $v){
				if((int)($v) && $v>0){
					$where .= $v . ',';
				}
			}
			$where = rtrim($where,',') . ' )';
			$this->_sql = "select * from {$this->_table} {$where}";
		}else{
			$this->_sql = 'select ' . $this->_opt['field'] . ' from ' . $this->_table . ' ';
			$this->_sql .= $this->_getSql();
		}
		$data = $this->_getFetch($this->_sql);
		return $data;
	}
	
	//封装find
	public function find($id=''){
		if(empty($this->_opt['field'])){
			$this->_opt['field'] = ' * ';
		}
		if($id){
			$this->_sql = 'select ' . $this->_opt['field'] . ' from ' . $this->_table . ' where ' . $this->_pk . ' = ' . $id;
			return $data = $this->_getFetch($this->_sql);
		}
		$this->_sql = 'select ' . $this->_opt['field'] . ' from ' . $this->_table . ' ';
		$this->_sql .= $this->_getSql(false);
		$data = $this->_getFetch($this->_sql);
		return $data;
	}
	//获取数据集对象
	protected function _getFetch($sql){
		$result = $this->_conn->query($sql);	//mysqli面向对象风格,得到的是对象
		if(!$result){
			$this->_errorMsg($sql,$this->_conn->error);
		}
		$data = array();
		while ( ($res = $result->fetch_assoc()) != false ){
			$data[] = $res;
		}
		return $data;
	}

	//构造SQL语句
	protected function _getSql($type=true){	//0 select 1 find 
		$sql = '';
		if(isset($this->_opt['where'])){
			$sql .= $this->_opt['where'] . ' ';
		}
		if(isset($this->_opt['group'])){
			$sql .= $this->_opt['group'] . ' ';
		}
		if(isset($this->_opt['order'])){
			$sql .= $this->_opt['order'] . ' ';
		}
		if(isset($this->_opt['having'])){
			$sql .= $this->_opt['having'] . ' ';
		}
		if(isset($this->_opt['limit']) && $type === true){
			$sql .= $this->_opt['limit'] . ' ';
		}	
		if($type === false){
			$sql .= 'limit 1' . ' ';
		}
		return $sql;	
	}
	
	//封装create
	public function create($data){
		$tmp = array();
		foreach ($data as $k=>$v){
			if(in_array($k, $this->_opt['fields'])){
				$tmp[$k] = addslashes($v);
			}
		}
		return $tmp;
	}
	
	//封装insert	insert into test (`name`,`pass`) values ('aa','aa')
	public function insert($data){
		$data = $this->create($data);
		if(empty($data))return false;
		$this->value($data);
		$this->_sql = 'insert into ' . $this->_table . ' ( ' . $this->_opt['key'] . ' ) values ( ' . $this->_opt['val'] . ' )';
		return $this->query($this->_sql);
	}
	
	protected function value($data){
		if($data){
			$val = $key = '';
			foreach($data as $k=>$v){
				if($k == $this->_pk){
					continue;
				}
				$key .= '`' . $k . '`,';
				$val .= "'" . $v . "',";
			}
			$this->_opt['key'] = rtrim($key,',');
			$this->_opt['val'] = rtrim($val,',');				
		}
	}

	//封装delete
	public function delete($id=''){
		$id = $id ? (string)$id : '';
		if(empty($id) && empty($this->_opt['where'])){
			die('删除条件不能为空');
		}		
		if($id && isset($this->_opt['where'])){
			die('where条件存在,delete要为空');
		}
		$this->_sql = "delete from {$this->_table} ";
		if($id){
			$this->_sql .= "where {$this->_pk} in ( ";
			$ids = is_string($id) ? explode(',', $id) : $id;
			foreach ($ids as $v){
				if($v>0){
					$this->_sql .= $v . ',';
				}
			}
			$this->_sql = rtrim($this->_sql,',') . ')';
		}elseif ($this->_opt['where']){
			$this->_sql .= $this->_opt['where'];
		}
		return $this->query($this->_sql);
		
	}

	//封装update	自定义框架必须使用数组的方式来插入修改数据	仿TP方式写入
	public function update($data){
		if(!is_array($data))return false;
		$data = $this->create($data);
		if(!array_key_exists($this->_pk, $data))return false;
		$upArr = '';	// Address = 'Zhongshan 23', City = 'Nanjing' WHERE LastName = 'Wilson'
		foreach ($data as $k=>$v){
			if($this->_pk == $k){
				$id = ((int)$v > 0) ? addslashes($v) : die('主键ID不存在');
				continue;
			}
			$v = addslashes($v);
			$upArr .=  "$k = '$v' , "; 
		}
		$upArr = substr($upArr, 0, -2);
		$this->_sql = 'update ' . $this->_table . ' set ' . $upArr . ' where ' . $this->_pk . ' = ' . $id;
		return $this->query($this->_sql);
		
	}
	
	//封装操作方法
	protected function query($sql){
		$this->_conn->query($sql) || $this->_errorMsg($sql,$this->_conn->error);
		return $this->_conn->affected_rows;
	}
	
	//获取选项
	public function __get($name){
		if($this->_opt[$name]){
			return $this->_opt[$name];
		}
		return false;
	}

	
	//统计方法
	public function count($count=''){
		if(empty($count)){
			$count = '*';
		}
		$this->_sql = "select count({$count}) as count from {$this->_table} ";
		if(isset($this->_opt['where'])){
			$this->_sql .= $this->_opt['where'];
		}
		return $this->_getFetch($this->_sql)[0]['count'];
	}
	
}
