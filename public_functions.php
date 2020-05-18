function is_function($argument){
	return isset($argument) && is_callable($argument);
}

//判断是否是关联数组
function is_assoc($arr) {
	return array_keys($arr) !== range(0, count($arr) - 1);
}

//判断是否是数字
function is_number($value){
	return gettype($value)==="integer"||gettype($value)==="double"? true : false;
}

/**
 * 返回数组的维度
 * @$arr    array   数组
 * @return  number  数组的维度
 */
function arrayLevel($arr){
    $al = array(0);
    if(!function_exists('aL')){
        function aL($arr,&$al,$level=0){
            if(is_array($arr)){
                $level++;
                $al[] = $level;
                foreach($arr as $k => $v){
                    aL($v,$al,$level);
                }
            }
        }
    }
    aL($arr,$al);
    return max($al);
}

/**
 *把字符串分割为数组(一维数组)
 *@$str string  分割的字符串
 *@$charset string 字符串编码
 */
function str_cut($str,$charset='utf-8'){
    $re['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
    $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
    $re['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
    $re['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
    preg_match_all($re[$charset], $str, $match);
    return $match[0];
}

//返回随机字符串
function randomString($l=5,$mode="n",$config=[],$charset="utf-8"){
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
			$C[$key]=str_cut($value, $charset);
		}
	}
	$mode= empty($C[$mode]) ? "n"	:	$mode ;
	$str="";
    if(is_array($C[$mode]) && count($C[$mode])>0 ){
        for($i=0,$len=count($C[$mode]);$i<$l;$i++){
            $str.=$C[$mode][mt_rand(0,$len-1)];
        }
    }
    else{
        for($i=0,$len=strlen($C[$mode]);$i<$l;$i++){
            $str.=$C[$mode][mt_rand(0,$len-1)];
        }
    }
	return $str;
}

/**
 * 字符串截取和返回字符串的长度
 * @param string $str 要截取的字符串
 * @param int $start 字符串截取的初始位置，从0开始
 * @param int $length 字符串截取的长度
 * @param string $charset 字符串编码
 * @param bool $suffix 是否添加后缀
 * @param bool $strlen 是否返回字符串的长度(false不返回,true返回)
 * @return int|string
 */
function my_substr($str, $start = 0, $length, $charset = 'utf-8', $suffix = true, $strlen = false)
{
    $charset || ($charset = 'utf-8');
    //正则表达式匹配编码
    $re['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
    $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
    $re['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
    $re['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
    //返回字符串长度
    if ($strlen) {
        if (function_exists('mb_strlen')) {
            $count = mb_strlen($str, $charset);
        } elseif (function_exists('iconv_strlen')) {
            $count = iconv_strlen($str, $charset);
        } else {
            preg_match_all($re[$charset], $str, $match);
            $count = count($match[0]);
        }
        return $count;
    }
    //截取字符串
    if (function_exists("mb_substr"))
        $slice = mb_substr($str, $start, $length, $charset);
    elseif (function_exists('iconv_substr')) {
        $slice = iconv_substr($str, $start, $length, $charset);
        if (false === $slice) {
            $slice = '';
        }
    } else {
        preg_match_all($re[$charset], $str, $match);
        $slice = join("", array_slice($match[0], $start, $length));
    }
    //字数不满添加后缀 ...
    if ($suffix) {
        $count = my_substr($str, $start, $length, $charset, false, true);
        if ($count > $length) {
            return $slice . '......';
        } else {
            return $slice;
        }
    } else {
        return $slice;
    }
}

/**
 * 返回字符串长度
 * @param string $str
 * @param string $charset
 * @return int
 */
function my_strlen($str, $charset = 'utf-8')
{
    $charset || ($charset = 'utf-8');
    //正则表达式匹配编码
    $re['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
    $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
    $re['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
    $re['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
    //返回字符串长度
    if (function_exists('mb_strlen')) {
        $count = mb_strlen($str, $charset);
    } elseif (function_exists('iconv_strlen')) {
        $count = iconv_strlen($str, $charset);
    } else {
        preg_match_all($re[$charset], $str, $match);
        $count = count($match[0]);
    }
    return $count;
}

/**
 * 字符串分隔
 * @param string $str
 * @param int $split_length
 * @param string $charset
 * @return array|array[]|bool|false|string[]
 */
function my_str_split($str, $split_length = 1, $charset = 'utf-8')
{
    if (func_num_args() == 1 && strtolower($charset) === 'utf-8') {
        return preg_split('/(?<!^)(?!$)/u', $str);
    }
    if ($split_length < 1) {
        return false;
    }
    $len = my_strlen($str, $charset);
    $arr = array();
    for ($i = 0; $i < $len; $i += $split_length) {
        $s = my_substr($str, $i, $split_length, $charset, false);
        $arr[] = $s;
    }
    return $arr;
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

function curlGet($url = '', $options = array(), $CURLOPT_TIMEOUT = 30)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, $CURLOPT_TIMEOUT); //设置cURL允许执行的最长秒数
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
function curlPost($url = '', $postData = array(), $options = array(), $CURLOPT_TIMEOUT = 30)
{
    if (is_array($postData)) {
        $postData = http_build_query($postData);
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_TIMEOUT, $CURLOPT_TIMEOUT); //设置cURL允许执行的最长秒数
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

/*递归查看目录列表*/
function readDirs($dir) {
	static $arr=array();
	$dir = str_replace("\\", "/", $dir);
	$dir = str_replace("//", "/", $dir);
	$root= str_replace("\\",'/',"{$_SERVER['DOCUMENT_ROOT']}");
	$_dir=strpos($dir,$root) === 0 ? str_replace($root,"/",$dir) : $dir;
	$dir= strpos($dir, "/") === 0 ? preg_replace("/\//i",$root,$dir,1) : $dir ;
	$dir .= '/';
    $dir_handle = openDir($dir);
 	$preg=[
 		"a"	=> "/[\/]{1,10}/i"
 		,"b"	=> "/[\\]{1,10}/i"
 	];
    while(false !== $list=readDir($dir_handle)) {
        if ($list=='.' || $list=='..') continue;
        $file_path=preg_replace($preg['a'], "/",$dir.$list,10);
        $relative_path = preg_replace($preg['a'], '/', $_dir.$list,10);
        if( is_file($file_path) ){
	 		$arr['file'][] = [
	        /*绝对路径(完整的文件路径)*/
	        "path"      =>  $file_path
	        /*相对路径*/
	        ,"relative" =>  $relative_path
	        /*文件名*/
	        ,"fileName" =>  $list
	        /*文件创建或修改时间*/
	        ,"filectime"=>  filectime($file_path)
	        ,"fctime"   =>  date("Y-m-d H:i:s",filectime($file_path))
	        /*文件的上次访问时间*/
	        ,"fileatime"=>  fileatime($file_path)
	        ,"fatime"   =>  date("Y-m-d H:i:s",fileatime($file_path))
	        /*文件的内容上次被修改的时间*/
	        ,"filemtime"=>  filemtime($file_path).randomString(3,"n")
	        ,"fmtime"   =>  date("Y-m-d H:i:s",filemtime($file_path))
	        /*文件的所有者*/
	        ,"fileowner"=>  fileowner($file_path)
	        /*文件的大小 byte(字节)*/
	        ,"filesize" =>  filesize($file_path)
			];        	
        }

        
        //输出该文件
        // echo $list, '<br>';
        //判断当前是否为目录
        if(is_dir($file_path)) {
            //是目录
           $arr["dir"][]=[
                /*完整目录路径(决对路径)*/
                "path"      =>  $file_path
                /*相对路径*/
                ,"relative" => $relative_path
                /*文件夹（目录）名称*/
                ,"dirName"  =>  $list
            ];
            readDirs($dir . '/' . $list);
        }
 
    }
 	
    closeDir($dir_handle);
    return $arr;
}

/**
 * 遍历删除目录和目录下所有文件
 * @param unknown $dir
 * @return boolean
 */
function del_dir($dir)
{
    if (!is_dir($dir)) {
        return false;
    }
    $handle = opendir($dir);
    while (($file = readdir($handle)) !== false) {
        if ($file != "." && $file != "..") {
            is_dir("$dir/$file") ? del_dir("$dir/$file") : @unlink("$dir/$file");
        }
    }
    if (readdir($handle) == false) {
        closedir($handle);
        @rmdir($dir);
    }
}

/**
 * 获取文件扩展名
 * @param unknown $file
 * @return mixed
 */
function get_extension($file)
{
    return end(explode('.', $file));
}
/**
 * 判断是否SSL协议
 * @return boolean
 */
function is_ssl()
{
    if (isset($_SERVER['HTTPS']) && ('1' == $_SERVER['HTTPS'] || 'on' == strtolower($_SERVER['HTTPS']))) {
        return true;
    } elseif (isset($_SERVER['SERVER_PORT']) && ('443' == $_SERVER['SERVER_PORT'])) {
        return true;
    }
    return false;
}

/**
 * 获取顶级域名
 * @param unknown $url
 * @return unknown
 */
function get_top_domain($url = '')
{
    $url = empty($url) ? get_domain() : $url;
    $host = strtolower($url);
    if (strpos($host, '/') !== false) {
        $parse = @parse_url($host);
        $host = $parse['host'];
    }
    $topleveldomaindb = array(
        'com',
        'edu',
        'gov',
        'int',
        'mil',
        'net',
        'org',
        'biz',
        'info',
        'pro',
        'name',
        'museum',
        'coop',
        'aero',
        'xxx',
        'idv',
        'mobi',
        'cc',
        'me'
    );
    $str = '';
    foreach ($topleveldomaindb as $v) {
        $str .= ($str ? '|' : '') . $v;
    }

    $matchstr = "[^\.]+\.(?:(" . $str . ")|\w{2}|((" . $str . ")\.\w{2}))$";
    if (preg_match("/" . $matchstr . "/ies", $host, $matchs)) {
        $domain = $matchs['0'];
    } else {
        $domain = $host;
    }
    return $domain;
}