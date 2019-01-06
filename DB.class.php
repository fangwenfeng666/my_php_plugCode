<?php 
/**
* 
*/
class mysql_pdo extends PDO
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
		,"db_rows"	=> 0
	];
	function __construct($C,$fn)
	{
		//错误提示 1+4
		error_reporting(5);
		//设置时间时区	
		date_default_timezone_set("Asia/Shanghai");
		if (is_array($C)) {
			foreach ($C as $key => $value) {
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
			isset($fn) and is_callable($fn) and ($fn());
			die( "<meta charset='utf-8'>数据库服务器连接出错".$e->getMessage());
		}
		//设置数据库编码
		$this->conn->query("SET NAMES $db_charset");
	}
	//查询操作
	function selects($sql,$error_fn){
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
			$this->rows["db_rows"]=$this->rows["select"]=$object->rowCount();
			return $arr;
			// return $object->fetchAll();
		}
		else{
			isset($error_fn) and is_callable($error_fn) and $error_fn();
			return false;
		}
	}
	function getOne($sql,$error_fn){
		$object=$this->conn->query($sql,PDO::FETCH_ASSOC);
		$arr=array();
		if(is_object($object)){
			// foreach ($object as $key => $value) {
			// 	$arr[]=$value;
			// }
			$this->rows["db_rows"]=$this->rows["select"]=$object->rowCount();
			return $object->fetch();
		}else{
			isset($error_fn) and is_callable($error_fn) and $error_fn();
			return false;
		}
	}
	function getAll($sql,$error_fn){
		$object=$this->conn->query($sql,PDO::FETCH_ASSOC);
		if(is_object($object)){
			$this->rows["db_rows"]=$this->rows["select"]=$object->rowCount();
			return $object->fetchAll();
		}else{
			isset($error_fn) and is_callable($error_fn) and $error_fn();
			return false;
		}		
	}
	// 取得数据表的字段信息
	function getField($tableName,$error_fn){
		$object=$this->conn->query("show columns from $tableName",PDO::FETCH_ASSOC);
		$arr=[];
		if (is_object($object) && $object->rowCount()>0 ) {
			foreach ($object as $key => $value) {
				$arr[$value['Field']]=[
					// 字段名
					"name"		=> $value['Field']
					// 字段类型
					,"type"		=> $value['Type']
					// 是否允许空值
					,"null"		=> (strtolower($value['Null'])==='yes')? 1 : 0  //$value['Null']==='NO'
					// 默认值
					,"default"	=> $value['Default']
					// 是否是主键
					,"primary"	=> (strtolower($value['Key']) == 'pri') ? 1 : 0
					// 是否为自增值
					,"auto_inc"	=> (strtolower($value['Extra']) == 'auto_increment') ? 1 : 0
				];
			}
			return $arr;
		}else{
			isset($error_fn) and is_callable($error_fn) and $error_fn();
			// return $object->fetchAll();
			return false;
		}
	}
	// 取得数据表的字段信息（详细信息）
	function getFieldMessage($dbName,$tableName,$error_fn){
		($dbName) or ($dbName=$this->config["db_name"]);
		if (empty($tableName)) {return false;}
		$sql="select * from information_schema.columns where TABLE_SCHEMA='$dbName' and TABLE_NAME='$tableName'";
		$arr=[];
		$object=$this->conn->query($sql,PDO::FETCH_ASSOC);
		if (is_object($object) &&  $object->rowCount()>0 ) {
			foreach ($object as $key => $value) {
				$arr[$value['COLUMN_NAME']]=[
					// 数据库名称
					"dbName"		=> $value['TABLE_SCHEMA']
					// 表名
					,"tableName"	=> $value['TABLE_NAME']
					// 字段名
					,"fieldName"	=> $value['COLUMN_NAME']
					// 字段类型
					,"type"			=> $value['COLUMN_TYPE']
					// 权限
					,"privileges"	=> $value['PRIVILEGES']
					// 字段描述
					,"comment"		=> $value['COLUMN_COMMENT']
					// 是否允许空值
					,"null"			=> strtolower($value['IS_NULLABLE'])==='yes' ? 1 : 0 
					// 数据类型
					,"DATA_TYPE"	=> $value['DATA_TYPE']
					// 数据类型长度
					,"length"		=> $value['CHARACTER_MAXIMUM_LENGTH']
					// 编码
					,"code"			=> $value['CHARACTER_SET_NAME']
					// 是否为自增值
					,"auto_inc"		=> strtolower($value['EXTRA'])==='auto_increment' ? 1 : 0
					// 是否是主键
					,"primary"		=> strtolower($value['COLUMN_KEY']) ==='pri' ? 1 : 0
					// 是否是唯一
					,"unique"		=> strtolower($value['COLUMN_KEY']) ==='uni' ? 1 : 0
					// 默认值
					,"default"		=> $value['COLUMN_DEFAULT']
				];
			}
			return $arr; 
		}
		isset($error_fn) and is_callable($error_fn) and $error_fn($object);
		return $object->fetchAll();

	}
	// 取得数据库里面的数据表
	function getTable($dbName='',$error_fn=''){
		$object=$this->conn->query( $dbName ? "SHOW TABLES FROM $dbName" : "SHOW TABLES" ,PDO::FETCH_ASSOC);
		($dbName) or ($dbName=$this->config["db_name"]);
		$arr=[];
		if (is_object($object) && $object->rowCount()>0) {
			foreach ($object as $key => $value) {
				$arr[$key]=$value['Tables_in_'.$dbName];
			}
			return $arr;
		}
		isset($error_fn) and is_callable($error_fn) and $error_fn($object);
		// return $object->fetchAll();
		return false;
	}
	//插入操作
	function inserts($sql,$tableName,$C=[],$mode=true){
		/*
		$obj=$this->conn->query($sql);
		return ($this->rows["insert"]=$obj->rowCount())>0 ? true : false;
		*/
		if ($sql) {
			return ($this->rows["db_rows"]=$this->rows["insert"]=$this->conn->exec($sql))>0 ? true : false;
		}
		else{
			if (is_array($C)) {
				$k=""; $v=""; $field="";
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
				//echo $sql;
				return ($this->rows["db_rows"]=$this->rows["insert"]=$this->conn->exec($mode ? $sql : $sqls ))>0 ? true : false;
			}
			else{
				return false;
			}
		}
	}
	//修改操作
	function updates($sql,$tableName,$C=[],$wheres=""){
		if (!$sql) {
			if (is_array($C)) {
				$field="";
				foreach($C as $key => $value){
					$field="$key='$value',";
				}
				$field=substr($field,0,strlen($field)-1);
				$sql="update $tableName set $field $wheres ";
			}
		}
		return ($this->rows["db_rows"]=$this->rows["update"]=$this->conn->exec($sql))>0 ? true : false;
	}
	//删除操作
	function deletes($sql){
		return ($this->rows["db_rows"]=$this->rows["delete"]=$this->conn->exec($sql))>0 ? true : false;
	}
	//获取数据库操作影响的行数
	function get_db_rows($type='db_rows'){
		return $type ? $this->rows[$type] : $this->rows;
	}
}
?>