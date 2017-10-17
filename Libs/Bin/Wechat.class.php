<?php
/*
 * by 李伟
 */
class Wechat{
	protected static $appid = '';
	protected static $appsecret = '';
	//单例对象
	private static $wechat = null;
	private function __construct(){}
	private function __clone(){}
	public static function conn($options=''){
		$options = empty($options) ? C('WechatConf') : $options;
		self::$appid = $options['AppID'];
		self::$appsecret = $options['AppSecret'];
		if(!self::$wechat){
			self::$wechat = new self;
		}
		return self::$wechat;
	}
	
	/*
	 * 构造请求方法，实现接口调用     |  curl方法实现
	 */
	protected function _request($curl, $https = true, $method = 'GET', $data = null){
		$ch = curl_init();  //先初始化
		curl_setopt($ch, CURLOPT_URL, $curl);
		curl_setopt($ch, CURLOPT_HEADER, false);//不接收头信息
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);//不是直接输出
		if($https){
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		}
		if($method == 'POST'){
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		}
		$response = curl_exec($ch);
		curl_close($ch);
		return $response;
	}
	
	/*
	 * 获取AccessToken 缓存到文件
	 */
	protected function _getAccessToken(){
		$accessToken_cacha_file = C('accessToken_cacha_file') ? C('accessToken_cacha_file') : '';   //token 缓存文件
		if(file_exists($accessToken_cacha_file)){
			$response = file_get_contents($accessToken_cacha_file); //读取文件内容
			$content = json_decode($response,true);	//深度转化数组
			if(time() - filemtime($accessToken_cacha_file) < $content['expires_in']) return $content['access_token'];
		}
		$curl = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . self::$appid . '&secret=' . self::$appsecret;
		$response = $this->_request($curl);
		file_put_contents($accessToken_cacha_file, $response);	//json格式数据写入缓存文件
		$response = json_decode($response,true); //object response
		if(isset($response['errcode']))return false;
		return $response['access_token'];
	}
	
	/*
	 * 获取二维码   | 商品打折等二维码
	 */
	public function _getTicket(){}
	
	/*
	 * 检查安全性  | 一次调用
	 */
	public function valid(){
		$echoStr = $_GET["echostr"];
		if($this->checkSignature()){
			echo $echoStr;
			exit;
		}
	}
	/*
	 * 成为开发者  | 一次调用
	 */
	private function checkSignature(){
		$signature = $_GET["signature"];
		$timestamp = $_GET["timestamp"];
		$nonce = $_GET["nonce"];
		$token = Token;
		$tmpArr = array($token, $timestamp, $nonce);
		sort($tmpArr, SORT_STRING);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );
	
		if( $tmpStr == $signature ){
			return true;
		}else{
			return false;
		}
	}
	
	
}
?>