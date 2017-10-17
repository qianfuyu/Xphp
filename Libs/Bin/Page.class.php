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
 * Xphp 分页类
 */
class Page{
	private $__total;		//总条数
	private $__totalpage;	//总页数 	9	1 2 3 4 5 6 7 8 9 
	private $__pagerow;		//页码数量	6	 1 2 3 ... 8 9 10
	private $__pagerows;	//每页显示条数
	private $__selfpage;	//当前页
	private $__url;			//生成url地址
	private $__startid;		//当前页开始ID
	private $__endid;		//当前页结束ID
	private $__desc = array();	//分页配置选项
	/*
	 * @param int $total 总计数目
	 * @param int $rows 每页显示条数
	 * @param int $pagerow 显示数目
	 * @param array $desc 分页显示显示
	 * @return object $this 分页对象
	 */
	public function __construct($total,$rows=10,$pagerow=3,$desc=''){
		$pagerow = ($pagerow < 3) ? 3 : $pagerow;
		$this->__total = $total;		//总条数
		$this->__pagerows = $rows;		//每页显示条数
		$this->__totalpage = ceil($this->__total / $this->__pagerows);	//总页数
		$this->__pagerow = min($pagerow,$this->__totalpage-1);	//页码数量
		$this->__selfpage = min($this->__totalpage,max((int)@$_GET['page'],1));//当前页
		$this->__startid = ( ($this->__selfpage - 1) * $this->__pagerows ) + 1;//当前页开始ID
		$this->__endid = min( $this->__total,($this->__selfpage * $this->__pagerows) );//当前页结束ID
		$this->__url = $this->resUrl();	//生成当前url地址
		$this->__desc = $this->desc($desc);	//配置文字描述
	}
	
	//重构url
	protected function resUrl(){
		$url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $_SERVER['PHP_SELF'];
		$param = parse_url($url);
		if(isset($param['query'])){
			parse_str($param['query'],$parse);
			unset($parse['page']);
			$url = $param['path'] . '?' . http_build_query($parse) . '&page=';
		}else{
			$url = strstr($url, '?') ? $url . 'page=' : $url . '?page=';
		}
		return $url;
	}
	
	/*
	 * 配置文字描述
	 */
	protected function desc($desc=''){
		$pageconf = array(
			'prev' => '上一页',
			'next' => '下一页',
			'first' => '首页',
			'end' => '末页',
			'unit' => '条',			
		);
		if(empty($desc) || !is_array($desc)){
			return $pageconf;
		}
		return array_merge($pageconf,$desc);
	}
	
	//构建limit语句	limit 0,10
	public function limit(){
		return max(0,($this->__selfpage-1)*$this->__pagerows) . ',' . $this->__pagerows;
	}
	
	//上一页	<a href="" '="" index.php?&page="3'">上一页</a>
	public function prev(){
		if($this->__selfpage>1){
			$before = $this->__selfpage-1;
			return "<a href={$this->__url}{$before}>{$this->__desc['prev']}</a>";
		}
		return $this->__desc['first'];
	}
	
	//下一页	<a href="" '="" index.php?&page="3'">上一页</a>
	public function next(){
		if($this->__selfpage < $this->__totalpage){
			$next = $this->__selfpage+1;
			return "<a href={$this->__url}{$next}>{$this->__desc['next']}</a>";
		}
		return $this->__desc['end'];
	}
	//首页
	public function first(){
		return ($this->__selfpage>1) ? "<a href={$this->__url}1>{$this->__desc['first']}</a>" : ''; 
	}
	//末页
	public function end(){
		return ($this->__selfpage<$this->__totalpage) ? "<a href={$this->__url}{$this->__totalpage}>{$this->__desc['end']}</a>" : ''; 
	}
	//记录当前页
	public function selfid(){
		return $this->__startid . $this->__desc['unit'] . ' - ' . $this->__endid . $this->__desc['unit'];
	}
	//当前页
	public function selfpage(){
		return '当前第 ' . $this->__selfpage . ' 页';
	}
	//统计数据信息
	public function count(){
		return '<span>总共 ' . $this->__totalpage . ' 页  ' . $this->__total . $this->__desc['unit'];
	}
	//分页列表信息	1 2 3 4 5 6 7 8 9 10    1 2 3 ... 8 9 10
	protected function _pagelist(){
		$pagelist = '';
		$start = max(1,(min( $this->__selfpage - ceil($this->__pagerow / 2),$this->__totalpage - $this->__pagerow)) );
		$end = $start + $this->__pagerow;
		for ($i=$start;$i<=$end;$i++){
			if($i == $this->__selfpage){
				$pagelist[$i]['url'] = '';
				$pagelist[$i]['str'] = $i;
				continue;
			}
			$pagelist[$i]['url'] = $this->__url . $i;
			$pagelist[$i]['str'] = $i;
		}
		return $pagelist;
	}
	//构建分页列表
	public function doPage(){
		$pages = $this->_pagelist();
		$pagelist = '';
		foreach ($pages as $v){
			$pagelist .= empty($v['url']) ? '<strong>' . $v['str'] . '</strong>' : "<a href={$v['url']}>{$v['str']}</a>";
		}
		return $pagelist;
	}
	
}





