<?php
/**
* 数据库操作
*/
class mysqlis
{ 
	private $db_config=array(
		 "db_port"		=>	3306			//数据库服务器连接端口默认3306
		,"db_name"		=>	"yqfw_test"		//连接的数据库名称
		,"db_pass"		=>	"123456"		//连接数据库服务器密码
		,"db_user"		=>	"root"			//连接数据库服务器用户
		,"db_host"		=>	"localhost"		//连接数据库服务器地址
		,"db_charset"	=>	"utf8"			//数据库编码默认采用utf8
	);

	private $db_port;		//数据库服务器连接端口
	private $db_host;		//连接数据库服务器地址
	private $db_user;		//连接数据库服务器用户
	private $db_pass;		//连接数据库服务器密码
	private $db_name;		//数据库名称
	private $db_charset;	//数据库编码默认采用utf8
	private $db_link; 		//数据库服务器连接对象
	private $db_name_link;	//数据库连接情况（布尔值）

	/*错误信息回报的等级-begin*/
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
	/*错误讯息回报的等级-end*/	
	//获取数据库操作影响的行数（关联数组）
	private $rows=[
		 "select"	=> 0		//查询操作影响行数
		,"insert"	=> 0		//插入操作影响行数
		,"update"	=> 0		//修改操作影响行数
		,"delete"	=> 0		//删除操作影响行数
	];

	function __construct($config_json)
	{
		//error_reporting(7);/*错误提示 1+2+4*/
		error_reporting(5);/*错误提示 1+4*/
		date_default_timezone_set("Asia/Shanghai");/*设置时间时区*/			
		if (is_array($config_json)) {
			foreach ($config_json as $key => $value) {
				$this->db_config[$key]=$value;
			}
		}

		$this->db_host=$this->db_config["db_host"].":".$this->db_config["db_port"];
		$this->db_user=$this->db_config["db_user"];
		$this->db_pass=$this->db_config["db_pass"];
		$this->db_name=$this->db_config["db_name"];
		$this->db_charset=$this->db_config["db_charset"];
		$this->db_link=mysqli_connect($this->db_host,$this->db_user,$this->db_pass);
		$this->db_name_link=mysqli_select_db($this->db_link,$this->db_name);
		mysqli_query("set names ".$this->db_charset);	/*数据库编码格式*/
	}

	function DB(){
		$this->db_host=$this->db_config["db_host"].":".$this->db_config["db_port"];
		$this->db_user=$this->db_config["db_user"];
		$this->db_pass=$this->db_config["db_pass"];
		$this->db_name=$this->db_config["db_name"];
		$this->db_charset=$this->db_config["db_charset"];
		$this->db_link=mysqli_connect($this->db_host,$this->db_user,$this->db_pass);
		//$this->$db_name_link=mysqli_select_db($this->db_name,$this->db_link);
		$this->db_name_link=mysqli_select_db($this->db_link,$this->db_name);
		mysqli_query("set names ".$this->db_charset);	/*数据库编码格式*/		
	}

	function getConfig(){
		$db_name_link= $this->db_name_link ? "true" : "false" ;
		return [
		 "db_name"		=>	$this->db_name
		,"db_host"		=>	$this->db_host
		,"db_user"		=>	$this->db_user
		,"db_charset"	=> 	$this->db_charset
		,"db_config"	=>	$this->db_config
		,"db_name_link"	=>	$db_name_link
		];
	}
	function setConfig($config_json){
		$this->db_host=$config_json["db_host"].":".$config_json["db_port"];
		$this->db_user=$config_json["db_user"];
		$this->db_pass=$config_json["db_pass"];
		$this->db_name=$config_json["db_name"];
		$this->db_charset=$config_json["db_charset"];		
	}
	//获取数据库操作影响的行数 select,insert,update,delect 返回一个关联数组
	function get_rows(){
		return $this->rows;
	}
	function debug(){
		echo "<meta charset='utf-8'>";
		if (!$this->db_link) {
			echo "连接数据库服务器失败<br>";
			exit;
		}
		else{
			echo "连接数据库服务器成功<br>";			
		}
		if ($this->db_name_link===true) {
			echo $this->db_name."数据库连接成功<br>";
		}
		else{
			echo $this->db_name."数据库连接失败<br>";
			
		}
	}
	function selects($sql){
		/*select *(或者字段名多个字段用逗号分隔) from 表名1,表明2... where .... */
		$arr=array();/*二维数组*/
		$req=mysqli_query($sql);
		$rows=mysqli_affected_rows();
		$this->rows["select"]=$rows;
		if ($rows>0) {
			//echo "查询成功";
			$i=0;
			while ($re=mysqli_fetch_array($req,MYSQL_ASSOC)) {/*只获取关联数组*/
				//foreach ($re as $key => $value) {/*遍历关联数组*/
					//$arr[$i][$key]=$value;
				//}	
				$arr[$i]=$re;
				$i++;
			}

			return $arr;
		}
		else{
			return false;
		}
	}
	function inserts($sql,$table_name="",$key_names=array(),$key_values=array()){/*4个参数*/
		/*insert into 表名(字段1,字段2,字段3,..)values('字段1值','字段2值','字段3值',...);*/
		if ($sql===0||$sql==null||$sql==""||$sql==false) {
			$kn="";
			$kv="";
			for ($i=0,$len=count($key_names); $i <$len ; $i++) { 
				$kn.=$key_names[$i].",";
				$kv.="'".$key_values[$i]."',";
			}
			$sqls="insert into $table_name(".substr($kn,0,strlen($kn)-1).")values(".substr($kv,0,strlen($kv)-1).")";
			//echo $sqls;
			$req=mysqli_query($sqls);
		}
		else{
			$req=mysqli_query($sql);
		}
		$rows=mysqli_affected_rows();
		$this->rows["insert"]=$rows;//获取影响行数
		if($rows>0){	/*插入数据成功*/
			//echo "插入数据成功";
			return true;
		}
		else{
			//echo "插入数据失败";
			return false;
		}
	}
	function updates($sql,$table_name="",$key_names=array(),$new_key_value=array(),$wheres=""){/*5个参数*/
		/*update 表名 set 字段1='值1',字段2='值2',... where 字段='某值'... （and  or）*/
		if($sql===0||$sql==null||$sql==""||$sql==false){
			$kn="";
			for ($i=0,$len=count($key_names); $i < $len; $i++) { 
				$kn.=$key_names[$i]."='".$new_key_value[$i]."',";
			}
			$sqls="update $table_name set ".substr($kn,0,strlen($kn)-1)." ".$wheres;
			//echo $sqls;
			$req=mysqli_query($sqls);
		}
		else{
			$req=mysqli_query($sql);
			//echo $sql;
		}
		$rows=mysqli_affected_rows();
		$this->rows["update"]=$rows;
		if($rows>0){	/*修改数据成功*/
			//echo "true";
			return true;
		}
		else{
			//echo "false";
			return false;
		}
	}
	function deletes($sql,$table_name,$wheres=""){
		/* delete from 表名 where 字段1='值1' ... */
		if ($sql===0||$sql==null||$sql==""||$sql==false) {
			$sqls="delete from $table_name ".$wheres;
			mysqli_query($sqls);
		}
		else{
			$req=mysqli_query($sql);
		}
		$rows=mysqli_affected_rows();
		$this->rows["delete"]=$rows;
		if($rows>0){	/*删除数据成功*/
			return true;
		}
		else{
			return false;
		}		
	}

	function keys($arr=array()){
		if (count($arr)>0) {
			$str="";
			foreach ($arr as $key => $value) {
				$str.="'$key':'$value',";
			}
			return "{".substr($str,0,strlen($str)-1)."},";
		}
		else{
			return "[]";
		}
	}
	function arr_json($arr=array()){
		$str="";
		if (count($arr)>0) {
			for($i=0,$len=count($arr);$i<$len;$i++){
				$str.=$this->keys($arr[$i]);
			}
			return "[".substr($str,0,strlen($str)-1)."]";
		}
		else{
			return "[]";
		}
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
	function is_number($value){
		return gettype($value)==="integer"||gettype($value)==="double"? true : false;
	}
}
//$conn=new mysqls();
?>

