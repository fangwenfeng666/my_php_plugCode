<?php
/**
* 数据库操作类
*/
class DB extends PDO
{
	private $config=[
		//连接数据库服务器的地址
		"db_host"		=> "localhost"
		//连接数据库服务器的用户
		,"db_user"		=> "root"
		//连接数据库服务器的密码
		,"db_pass"		=> ""
		//连接数据库服务器的类型
		,"db_type"		=> "mysql"
		//连接数据库服务器的端口
		,"db_port"		=> 3306
		//数据库编码默认采用utf8
		,"db_charset"	=> "utf8"
		//连接数据库的名称
		,"db_name"		=> ""
		//数据库连接类型（是否是长连接）
		,"db_link_type"	=> false
	];
	//数据库连接对象
	private $conn;
	//数据源名称或叫做 DSN，包含了请求连接到数据库的信息。
	private $dsn;
	//是否成功连接上数据库
	private $is_db_link=false;
	//数据库操作影响的行数
	private $rows=[
		 "select"	=> 0
		,"insert"	=> 0
		,"update"	=> 0
		,"delete"	=> 0
	];
	//错误等级提示
	private $error_1=E_ERROR;
	private $error_2=E_WARNING;
	private $error_4=E_PARSE;
	private $error_8=E_NOTICE;
	private $error_16=E_CORE_ERROR;
	private $error_32=E_CORE_WARNING;
	private $error_64=E_COMPILE_ERROR;
	private $error_128=E_COMPILE_WARNING;
	private $error_256=E_USER_ERROR;
	private $error_512=E_USER_WARNING;
	private $error_1024=E_USER_NOTICE;
	private $error_2047=E_ALL;
	private $error_2048=E_STRICT;

	function __construct($config)
	{
		//error_reporting(7);/*错误提示 1+2+4*/
		//错误提示 1+4
		error_reporting(5);
		//设置时间时区	
		date_default_timezone_set("Asia/Shanghai");
		
		if (is_array($config)) {
			foreach ($config as $key => $value) {
				$this->config[$key] = $value;
			}
		}
		$db_type=$this->config["db_type"];
		$db_host=$this->config["db_host"];
		$db_name=$this->config["db_name"];
		$db_port=$this->config["db_port"];
		$db_user=$this->config["db_user"];
		$db_pass=$this->config['db_pass'];
		$db_charset=$this->config["db_charset"];
		$db_link_type=$this->config["db_link_type"];
		$this->dsn="$db_type:host=$db_host;dbname=$db_name;port=$db_port";
		try{
			$this->conn= new PDO($this->dsn,$db_user,$db_pass,array( PDO::ATTR_PERSISTENT => $db_link_type));
			//是否成功连接上数据库服务器,布尔值
			$this->is_db_link = is_object($this->conn) ? true : false ;
		}
		catch (Exception $e){
			die( "<meta charset='utf-8'>数据库服务器连接出错".$e->getMessage());
		}
		
		//设置数据库编码
		$this->conn->query("SET NAMES $db_charset");

	}

	//查询操作
	function selects($sql){
		//成功返回一个对象（类似实例后的类）关联数组
		//PDO::FETCH_ASSOC——关联数组形式；
		//PDO::FETCH_NUM——数字索引数组形式；
		//PDO::FETCH_BOTH——两种数组形式都有，这是默认的；
		//PDO::FETCH_OBJ——按照对象的形式，类似于以前的 mysql_fetch_object()。
		$object=$this->conn->query($sql,PDO::FETCH_ASSOC);
		if (is_object($object)) {
			$arr=array();
			$i=0;
			foreach ($object as $key => $value) {
				$arr[$i]=$value;
				$i++;
			}
			//$this->rows["select"]=count($arr);
			$this->rows["select"]=$object->rowCount();
			return $arr;
		}
		else{
			return false;
		}
	}
	//插入操作
	function inserts($sql){
		/*
		$obj=$this->conn->query($sql);
		return ($this->rows["insert"]=$obj->rowCount())>0 ? true : false;
		*/
		return ($this->rows["insert"]=$this->conn->exec($sql))>0 ? true : false;
	}
	//修改操作
	function updates($sql){
		return ($this->rows["update"]=$this->conn->exec($sql))>0 ? true : false;
	}
	//删除操作
	function deletes($sql){
		return ($this->rows["delete"]=$this->conn->exec($sql))>0 ? true : false;
	}
	//获取配置信息或私有变量的值
	function getConfig($name){
		$config=[];
		$config=$this->config;
		$config["conn"]=$this->conn;
		$config["dsn"]=$this->dsn;
		$config["is_db_link"]=$this->is_db_link;
		$config['rows']=$this->rows;
		return empty($name) ? $config : $config[$name];

	}
	//获取数据库操作影响的行数
	function get_rows(){
		return $this->rows;
	}
	//传入关联数组（一维）编码json字符串（数据）
	function str_json($arr1_json=array(),$config){
		$json_str="";
		if (!$config) {
			$config=array(
				"key"		=> "<=>"
				,"json"		=> "<,>"
			);
		}
		foreach ($arr1_json as $key => $value) {
			$json_str.=$key.$config["key"].$value.$config["json"];
		}
		$json_str_len=strlen($json_str);
		$c_json_len=strlen($config["json"]);
		return substr($json_str,0,$json_str_len-$c_json_len);
	}
	//传入数组（二维,数组 里面存放关联数组）编码数组json字符串（数据）
	function str_arr_json($arr2_json=array(),$config){
		$arr_json_str="";
		if (!$config) {
			$config=array(
				"key"		=> "<=>"
				,"json"		=> "<,>"
				,"arr"		=> "<#>"
			);
		}
		foreach ($arr2_json as $key => $value) {
			$arr_json_str.=$this->str_json($value,$config).$config["arr"];
		}
		$arr_json_str_len=strlen($arr_json_str);
		$c_arr_len=strlen($config["arr"]);
		return substr($arr_json_str,0,$arr_json_str_len-$c_arr_len);
	}
	//编码json字符串（数据）
	function en_str_json($arr1_json=array(),$config){
		if (!$config) {
			$config=array(
				"key"		=> "<=>"
				,"json"		=> "<,>"
			);
		}
		return $this->str_json($arr1_json,$config);	
	}
	//编码数组json字符串（数据）
	function en_str_arr_json($arr2_json=array(),$config){
		if (!$config) {
			$config=array(
				"key"		=> "<=>"
				,"json"		=> "<,>"
				,"arr"		=> "<#>"
			);
		}
		return $this->str_arr_json($arr2_json,$config);		
	}
	//解码json字符串（数据）
	function de_str_json($strings,$config){
		if (!$strings) {
			exit;
		}
		if (!$config||!is_array($config)) {
			$config=array(
				"key"		=> "<=>"
				,"json"		=> "<,>"				
			);
		}
		$key=$config['key'];
		$json=$config['json'];
		$str_json=array();
		$str_json_arr1=explode($json,$strings);
		foreach ($str_json_arr1 as $k => $v) {
			$str_key_arr1=explode($key,$v);//字符串分割
			$str_json[$str_key_arr1[0]]=$str_key_arr1[1];
		}
		return $str_json;//一维数组（一个关联数组）

	}
	//解码数组json字符串（数据）
	function de_str_arr_json($strings,$config){
		if (!$strings) {
			exit;
		}
		if (!$config||!is_array($config)) {
			$config=array(
				"key"		=> "<=>"
				,"json"		=> "<,>"
				,"arr"		=> "<#>"				
			);
		}
		$arr=$config['arr'];//字符串分割模式
		$str_arr_json=array();
		$str_arr_json_arr1=explode($arr,$strings);//字符串分割
		foreach ($str_arr_json_arr1 as $k => $v) {
			$str_arr_json[$k]=$this->de_str_json($v,$config);
		}
		return $str_arr_json;//二维数组（多个关联数组）
		//preg_split(pattern, subject)
	}
	//判断是否是关联数组
	function is_assoc($arr) {
		return array_keys($arr) !== range(0, count($arr) - 1);
	}
	function is_obj($arr){
		return array_keys($arr) !== range(0, count($arr) - 1);
	}
	//判断是否是数字
	function is_number($value){
		return gettype($value)==="integer"||gettype($value)==="double"? true : false;
	}
	//返回数组的维度
	function arrayLevel($arr){
	    $al = array(0);
	    //&$al 引用$al数组的地址（指针）
	    function aL($arr,&$al,$level=0){
	        if(is_array($arr)){
	            $level++;
	            $al[] = $level;
	            foreach($arr as $k => $v){
	                aL($v,$al,$level);
	            }
	        }
	    }
	    aL($arr,$al);
	    return max($al);
	}
}
?>