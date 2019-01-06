<?php
/*1+4*/
error_reporting(5);
// 判断是否是函数(方法)
function is_function($argument){
	return isset($argument) && is_callable($argument);
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
//把数组、关联数组转为json格式字符串
function JSON_STR($arr){
	if (is_array($arr)) {
		$arr1=$arr;
		//return str_replace("\"", "\\\"",json_encode($arr,JSON_UNESCAPED_UNICODE));
		return json_encode($arr1,JSON_UNESCAPED_UNICODE);
	}
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
//返回随机字符串
function randomString($l=5,$mode="n",$config=[]){
	$C=[
		"n"			=>	"0123456789"
		,"s"		=>	"abcdefghijklmnopqrstuvwxyz"
		,"S"		=>	"ABCDEFGHIJKLMNOPQRSTUVWXYZ"
		,"ns"		=>	"0123456789abcdefghijklmnopqrstuvwxyz"
		,"nS"		=>	"0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"
		,"sS"		=>	"abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"
		,"nsS"		=>	"0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"
		,"all"		=>	"0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-_^+*=|,.~!@#"
	];
	if (is_array($config)) {
		foreach ($config as $key => $value) {
			$C[$key]=$value;
		}
	}
	$mode= empty($C[$mode]) ? "n"	:	$mode ;
	$str="";
	for($i=0,$len=strlen($C[$mode]);$i<$l;$i++){
		$str.=$C[$mode][mt_rand(0,$len-1)];
	}
	return $str;
}
//写入文件 @$mode string 文件写入方式 [r,r+]（读取文件） [w,w+]（覆盖写入文件） [a,a+]（向文件末尾写入）
function set_file($str,$url,$mode="w"){
	//打开文件
	$f=fopen($url, $mode);
	//写入内容
	fwrite($f, $str);
	//关闭文件
	fclose($f);
}
//读取文件	@$url string 文件（资源）地址 @$mode string 文件读取模式 @$r_type string 返回值的类型（是否是数组还是字符串）
function get_file($url,$mode="r",$r_type="arr"){
	$arr=[];
	$str=file_get_contents($url);
	$f = fopen($url,$mode);
	while(! feof($f))
	{
	  	$arr[]=str_replace("\r\n","", fgets($f));
	}
	fclose($f);
	$C=[
		"arr"		=>	$arr
		,"str"		=>	$str
		,"0"		=>	$arr
		,"1"		=>	$str
	];
	return $C[$r_type];
}

function curlGet($url = '', $options = array())
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    if (!empty($options)) {
        curl_setopt_array($ch, $options);
    }
    //https请求 不验证证书和host
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}
/*
*@postDate array|object 数组或关联数组(一维)
*@$options array 关联数组(一维)，如：[CURLOPT_URL => 'http://www.example.com/',CURLOPT_POST => 1]
*/
function curlPost($url = '', $postData = array(), $options = array())
{
    if (is_array($postData)) {
        $postData = http_build_query($postData);
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30); //设置cURL允许执行的最长秒数
    if (!empty($options)) {
        curl_setopt_array($ch, $options);
    }
    //https请求 不验证证书和host
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}

//--2018--
function clearLang(&$arr,$is_del=false){
    ($is_del) and ($arr=array());
}
//--2018--
/**
*@$arr array 传入要操作的数组 @$name array 传入键值(数组长度表示要操作数组的维度,格式 ["key1","key2","key3",...])
*@$value string|array|object 要设置修改的值(为空的话是取值) 
*/
function setLang(&$arr,$name=[],$value){
    $arrStr="\$arr";
    $arr_=[];
    for($i=0,$len=count($name),$j=$len-1;$i<$len;$i++){
        $arrStr.="[\"{$name[$i]}\"]";
        $arr_[$i]=$arrStr;
    }
    if (is_null($value)||empty($value)) {
        eval("\$vv=$arr_[$j];");
        return $vv;
    }
    for($i=0;$i<$j;$i++){
        eval("is_array({$arr_[$i]}) or ({$arr_[$i]}=[]);");
    }
    eval("$arr_[$j]=\$value;");
}
/**
 * 获取和设置配置参数 支持批量定义
 * @param string|array $name 配置变量
 * @param mixed $value 配置值
 * @param mixed $default 默认值
 * @return mixed
 */
function Config($name=null, $value=null,$default=null) {
    static $_config = array();
    // 无参数时获取所有
    if (empty($name)) {
        return $_config;
    }
    // 优先执行设置获取或赋值
    if (is_string($name)) {
        if (!strpos($name, '.')) {
            $name = strtoupper($name);
            if (is_null($value))
                return isset($_config[$name]) ? $_config[$name] : $default;
            $_config[$name] = $value;
            return null;
        }
        // 二维数组设置和获取支持
        $name = explode('.', $name);
        $len = count($name); //--2018--
        $name[0]   =  strtoupper($name[0]);
        if (is_null($value)){ //--2018-- 获取值
            if ($len>2) {
                return setLang($_config,$name);
            }
            return isset($_config[$name[0]][$name[1]]) ? $_config[$name[0]][$name[1]] : $default;
        }
        else{ //--2018-- 设置值
            ($len>2) and (setLang($_config,$name,$value));
            ($len<=2) and ($_config[$name[0]][$name[1]] = $value);
        }
        return null;
    }
    // 批量设置
    if (is_array($name)){
        $_config = array_merge($_config, array_change_key_case($name,CASE_UPPER));
        return null;
    }
    return null; // 避免非法参数
}


/**
*查看目录
*@dir string 目录路径
*/
function see_dir($dir,$error_fn){
	$root="{$_SERVER['DOCUMENT_ROOT']}/";
	$_dir=strpos($dir,$root) === 0 ? str_replace($root,"/",$dir) : $dir;
	$dir= strpos($dir, "/") === 0 ? preg_replace("/\//i",$root,$dir,1) : $dir ;
	$arr=[
		// 存储路径是文件的部分
		"file" 				=> []
		// 存储路径是目录（文件夹）的部分
		,"dir"				=> []
		// 存储当前浏览的绝对路径
		,"absolute_path"	=> $dir
		// 存储当前浏览的相对路径
		,"relative_path"	=> $_dir
	];
	if(is_dir($dir)){
		// 打开目录
		if($d=opendir($dir)){
			// 浏览目录列表
			while ( ($list=readdir($d)) !==false) {
				if (is_file($dir.$list)) {
					$arr['file'][] = [
                        /*绝对路径(完整的文件路径)*/
                        "path"      =>  $dir.$list
                        /*相对路径*/
                        ,"relative" => $_dir.$list
                        /*文件名*/
                        ,"fileName" =>  $list
                        /*文件创建或修改时间*/
                        ,"filectime"=>  filectime($dir.$list)
                        ,"fctime"   =>  date("Y-m-d H:i:s",filectime($dir.$list))
                        /*文件的上次访问时间*/
                        ,"fileatime"=>  fileatime($dir.$list)
                        ,"fatime"   =>  date("Y-m-d H:i:s",fileatime($dir.$list))
                        /*文件的内容上次被修改的时间*/
                        ,"filemtime"=>  filemtime($dir.$list).randomString(3,"n")
                        ,"fmtime"   =>  date("Y-m-d H:i:s",filemtime($dir.$list))
                        /*文件的所有者*/
                        ,"fileowner"=>  fileowner($dir.$list)
                        /*文件的大小 byte(字节)*/
                        ,"filesize" =>  filesize($dir.$list)
					];
				}
				else{
                    $arr["dir"][]=[
                        /*完整目录路径(决对路径)*/
                        "path"      =>  $dir.$list
                        /*相对路径*/
                        ,"relative" => $_dir.$list
                        /*文件夹（目录）名称*/
                        ,"dirName"  =>  $list
                    ];
				}
			}
			/*关闭目录*/
			closedir($d);
			return $arr;
		}
		else{
			isset($error_fn) and is_callable($error_fn) and $error_fn();
			return false;
		}
	}
	else{
		$arr['message']='not dir';
		return $arr;
	}
}

?>
