<?php
/**
	* 
	*/
class mysqli_db
{
	
	private $db_rows=[
		"select"	=> 0
		,"update"	=> 0
		,"insert"	=> 0
		,"delete"	=> 0
		,"db_rows"	=> 0
	];
	private $config=[
		//数据库服务器连接端口默认3306
		 "db_port"		=>	3306
		//连接的数据库名称		
		,"db_name"		=>	""
		//连接数据库服务器密码	
		,"db_pass"		=>	""
		//连接数据库服务器用户
		,"db_user"		=>	"root"	
		//连接数据库服务器地址
		,"db_host"		=>	"localhost"
		//数据库编码默认采用utf8
		,"db_charset"	=>	"utf8"
	];
	// 数据库连接对象
	private $db_link;
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

	function __construct($Conf=[],$fn)
	{
		header("Content-type: text/html; charset=utf-8");
		error_reporting(5);/*错误提示 1+4*/
		date_default_timezone_set("Asia/Shanghai");/*设置时间时区*/
		if (is_array($Conf)) {
			foreach ($Conf as $key => $value) {
				$this->config[$key]=$value;
			}
		}
		$this->db_link=mysqli_connect(
			$this->config['db_host']
			,$this->config['db_user']
			,$this->config['db_pass']
			,$this->config['db_name']
			,$this->config['db_port']
		);
		if (mysqli_connect_errno($this->db_link)) {
			isset($fn) and is_callable($fn) and ($fn());
			echo "连接 mysql 失败: " .iconv("","utf-8", mysqli_connect_error());
		}
		else{
			//数据库编码格式
			mysqli_query($this->db_link,"set names ".$this->config['db_charset']);
			// mysqli_select_db($this->db_link,$this->config['db_name']); //修改数据库连接
		}
	}
	/*数据库查询操作(获取多行(多条)数据),返回一个二维数组(关联数组)*/
	function selects($sql,$error_fn){
		$arr=array();
		$req=mysqli_query($this->db_link,$sql);
		$this->db_rows['db_rows'] = $this->db_rows['select'] = mysqli_affected_rows($this->db_link);
		if ($this->db_rows['select']>0) {
			while ($re=mysqli_fetch_array($req,MYSQLI_ASSOC)) {
				$arr[]=$re;
			}
			// 释放结果集
			mysqli_free_result($req);
			return $arr;
		}
		else{
			isset($error_fn) and is_callable($error_fn) and $error_fn();
			return false;
		}
	}
	/*数据库查询操作，获取一行(一条)数据,返回一维数组(关联数组)*/
	function getOne($sql,$error_fn){
		$req=mysqli_query($this->db_link,$sql);
		$this->db_rows['db_rows'] = $this->db_rows['select'] = mysqli_affected_rows($this->db_link);
		($this->db_rows['db_rows']<1) and isset($error_fn) and is_callable($error_fn) and $error_fn();
		return $this->db_rows['db_rows']>0 ? mysqli_fetch_array($req,MYSQLI_ASSOC) : false ;
	}
	function getAll($sql,$error_fn){
		return self::selects($sql,$error_fn);
		// return $this->selects($sql,$error_fn);
	}
	// 取得数据表的字段信息
	function getField($tableName,$error_fn){
		$req=mysqli_query($this->db_link,"show columns from $tableName");
		$this->db_rows['db_rows'] = $this->db_rows['select'] = mysqli_affected_rows($this->db_link);
		$arr=[];
		if ($this->db_rows['db_rows']>0) {
			while ($re=mysqli_fetch_array($req,MYSQLI_ASSOC)) {
				$arr[$re['Field']]=[
					// 字段名
					"name"		=> $re['Field']
					// 字段类型
					,"type"		=> $re['Type']
					// 是否允许空值
					,"null"		=> (strtolower($re['Null'])==='yes')? 1 : 0  //$value['Null']==='NO'
					// 默认值
					,"default"	=> $re['Default']
					// 是否是主键
					,"primary"	=> (strtolower($re['Key']) == 'pri') ? 1 : 0
					// 是否为自增值
					,"auto_inc"	=> (strtolower($re['Extra']) == 'auto_increment') ? 1 : 0
				];				
			}
			return $arr;
		}
		isset($error_fn) and is_callable($error_fn) and $error_fn();
		return false;
	}
	// 取得数据表的字段信息（详细信息）
	function getFieldMessage($dbName,$tableName,$error_fn){
		($dbName) or ($dbName=$this->config["db_name"]);
		if (empty($tableName)) {return false;}
		$sql="select * from information_schema.columns where TABLE_SCHEMA='$dbName' and TABLE_NAME='$tableName'";
		$arr=[];
		$req=mysqli_query($this->db_link,$sql);
		$this->db_rows['db_rows'] = $this->db_rows['select'] = mysqli_affected_rows($this->db_link);
		if ($this->db_rows['db_rows']>0) {
			while ($re=mysqli_fetch_array($req,MYSQLI_ASSOC)) {
				$arr[$re['COLUMN_NAME']]=[
						// 数据库名称
						"dbName"		=> $re['TABLE_SCHEMA']
						// 表名
						,"tableName"	=> $re['TABLE_NAME']
						// 字段名
						,"fieldName"	=> $re['COLUMN_NAME']
						// 字段类型
						,"type"			=> $re['COLUMN_TYPE']
						// 权限
						,"privileges"	=> $re['PRIVILEGES']
						// 字段描述
						,"comment"		=> $re['COLUMN_COMMENT']
						// 是否允许空值
						,"null"			=> strtolower($re['IS_NULLABLE'])==='yes' ? 1 : 0 
						// 数据类型
						,"DATA_TYPE"	=> $re['DATA_TYPE']
						// 数据类型长度
						,"length"		=> $re['CHARACTER_MAXIMUM_LENGTH']
						// 编码
						,"code"			=> $re['CHARACTER_SET_NAME']
						// 是否为自增值
						,"auto_inc"		=> strtolower($re['EXTRA'])==='auto_increment' ? 1 : 0
						// 是否是主键
						,"primary"		=> strtolower($re['COLUMN_KEY']) ==='pri' ? 1 : 0
						// 是否是唯一
						,"unique"		=> strtolower($re['COLUMN_KEY']) ==='uni' ? 1 : 0
						// 默认值
						,"default"		=> $re['COLUMN_DEFAULT']				
				];
			}
			return $arr;		
		}
		isset($error_fn) and is_callable($error_fn) and $error_fn();
		return false;
	}
	// 取得数据库里面的数据表
	function getTable($dbName='',$error_fn=''){
		$req=mysqli_query($this->db_link, $dbName ? "SHOW TABLES FROM $dbName" : "SHOW TABLES" );
		$this->db_rows['db_rows'] = $this->db_rows['select'] = mysqli_affected_rows($this->db_link);
		($dbName) or ($dbName=$this->config["db_name"]);
		$arr=[];
		if ($this->db_rows['db_rows']>0) {
			while ($re=mysqli_fetch_array($req,MYSQLI_ASSOC)) {
				$arr[]=$re['Tables_in_'.$dbName];
			}
			return $arr;
		}
		isset($error_fn) and is_callable($error_fn) and $error_fn();
		return false;
	}
	/*数据库插入操作*/
	function inserts($sql,$tableName,$C=[],$mode=true){
		if ($sql) {
			mysqli_query($this->db_link,$sql);
		}
		else{
			if (is_array($C)) {
				$k="";$v="";$field="";
				foreach ($C as $key => $value) {
					$k.="$key,";
					$v.="'$value',";
					$field="$key='$value',";					
				}
				$k=substr($k,0,strlen($k)-1);
				$v=substr($v,0,strlen($v)-1);
				$field=substr($field,0,strlen($field)-1);
				$sql="insert into $tableName( $k )values( $v )";
				$sqls="insert into $tableName set $field ";
				mysqli_query($this->db_link, $mode ? $sql : $sqls );
			}
			else{return false;}
		}
		$this->db_rows['db_rows'] = $this->db_rows['insert'] = mysqli_affected_rows($this->db_link);
		return $this->db_rows['insert']>0 ;
	}
	/*数据库修改(更新)操作*/
	function updates($sql,$tableName,$C=[],$wheres=""){
		if ($sql) {
			mysqli_query($this->db_link,$sql);
		}
		else{
			if (is_array($C)) {
				$field="";
				foreach($C as $key => $value){
					$field="$key='$value',";
				}
				$field=substr($field,0,strlen($field)-1);
				$sql="update $tableName set $field $wheres ";
				mysqli_query($this->db_link,$sql);
			}
			else{return false;}		
		}
		$this->db_rows['db_rows'] = $this->db_rows['update'] = mysqli_affected_rows($this->db_link);
		return $this->db_rows['update']>0 ;
	}
	/*数据库删除操作*/
	function deletes($sql){
		mysqli_query($this->db_link,$sql);
		$this->db_rows['db_rows'] = $this->db_rows['delete'] = mysqli_affected_rows($this->db_link);
		return $this->db_rows['delete']>0 ;
	}
	/*获取数据库操作影响的行数*/
	function get_db_rows($name="db_rows"){
		return $name ? $this->db_rows[$name] : $this->db_rows ;
	}
}
?>