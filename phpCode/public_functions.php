<?php
/**
 * 获取数据类型
 * @param $var
 * @return string
 */
function get_type($var)
{
    if (is_array($var)) return "array";
    if (is_bool($var)) return "boolean";
    if (is_callable($var)) return "function reference";
    if (is_float($var)) return "float";
    if (is_int($var)) return "integer";
    if (is_null($var)) return "NULL";
    if (is_numeric($var)) return "numeric";
    if (is_object($var)) return "object";
    if (is_resource($var)) return "resource";
    if (is_string($var)) return "string";
    return "unknown type";
}

/**
 * 判断是否是函数
 * @param $argument
 * @return bool
 */
function is_function($argument)
{
    return isset($argument) && is_callable($argument);
}

/**
 * 判断是否是关联数组
 * @param $arr
 * @return bool
 */
function is_assoc($arr)
{
    return array_keys($arr) !== range(0, count($arr) - 1);
}

/**
 * 判断是否是数字
 * @param $value
 * @return bool
 */
function is_number($value)
{
    return gettype($value) === "integer" || gettype($value) === "double" ? true : false;
}

/**
 *把字符串分割为数组(一维数组)
 *@$str string  分割的字符串
 *@$charset string 字符串编码
 */
function str_cut($str, $charset = 'utf-8')
{
    $re['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
    $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
    $re['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
    $re['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
    preg_match_all($re[$charset], $str, $match);
    return $match[0];
}

/**
 * 返回随机字符串
 * @param int $l
 * @param string $mode
 * @param array $config
 * @param string $charset
 * @return string
 */
function randomString($l = 5, $mode = "n", $config = array(), $charset = "utf-8")
{
    $C = array(
        "n" => "0123456789",
        "s" => "abcdefghijklmnopqrstuvwxyz",
        "S" => "ABCDEFGHIJKLMNOPQRSTUVWXYZ",
        "ns" => "0123456789abcdefghijklmnopqrstuvwxyz",
        "nS" => "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ",
        "sS" => "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ",
        "nsS" => "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ",
        "all" => "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-_^+*=|,.~!@#",
    );
    if (is_array($config) && count($config) > 0) {
        foreach ($config as $key => $value) {
            $C[$key] = str_cut($value, $charset);
        }
    }
    $mode = empty($C[$mode]) ? "n" : $mode;
    $str = "";
    if (is_array($C[$mode]) && count($C[$mode]) > 0) {
        for ($i = 0, $len = count($C[$mode]); $i < $l; $i++) {
            $str .= $C[$mode][mt_rand(0, $len - 1)];
        }
    } else {
        for ($i = 0, $len = strlen($C[$mode]); $i < $l; $i++) {
            $str .= $C[$mode][mt_rand(0, $len - 1)];
        }
    }
    return $str;
}

/**
 * curl get 请求
 * @param string $url
 * @param array $options 关联数组(一维)，如：[CURLOPT_URL => 'http://www.example.com/',CURLOPT_POST => 1]
 * @param int $CURLOPT_TIMEOUT 设置cURL允许执行的最长秒数
 * @return mixed
 */
function curlGet($url = '', $options = array(), $CURLOPT_TIMEOUT = 30)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, $CURLOPT_TIMEOUT);
    if (!empty($options)) {
        curl_setopt_array($ch, $options);
    }
    //https请求 不验证证书和host
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $data = curl_exec($ch);
    if (curl_errno($ch)) {
        //打印错误信息
        var_dump(curl_error($ch));
    }
    curl_close($ch);
    return $data;
}

/**
 * @param string $url
 * @param string|array $postData post请求传输的数据
 * @param array $options 关联数组(一维)，如：[CURLOPT_URL => 'http://www.example.com/',CURLOPT_POST => 1]
 * @param int $CURLOPT_TIMEOUT 设置cURL允许执行的最长秒数
 * @return mixed
 */
function curlPost($url = '', $postData = '', $options = array(), $CURLOPT_TIMEOUT = 30)
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

/**
 * 递归查看目录列表
 * @param $dir
 * @return array
 */
function readDirs($dir)
{
    static $arr = array();
    $dir = str_replace("\\", "/", $dir);
    $dir = str_replace("//", "/", $dir);
    $root = str_replace("\\", '/', "{$_SERVER['DOCUMENT_ROOT']}");
    $_dir = strpos($dir, $root) === 0 ? str_replace($root, "/", $dir) : $dir;
    $dir = strpos($dir, "/") === 0 ? preg_replace("/\//i", $root, $dir, 1) : $dir;
    $dir .= '/';
    $dir_handle = openDir($dir);
    $preg = array(
        "a" => "/[\/]{1,10}/i",
        "b" => "/[\\]{1,10}/i",
    );
    while (false !== $list = readDir($dir_handle)) {
        if ($list == '.' || $list == '..') continue;
        $file_path = preg_replace($preg['a'], "/", $dir . $list, 10);
        $relative_path = preg_replace($preg['a'], '/', $_dir . $list, 10);
        if (is_file($file_path)) {
            $arr['file'][] = array(
                /*绝对路径(完整的文件路径)*/
                "path" => $file_path,
                /*相对路径*/
                "relative" => $relative_path,
                /*文件名*/
                "fileName" => $list,
                /*文件创建或修改时间*/
                "filectime" => filectime($file_path),
                "fctime" => date("Y-m-d H:i:s", filectime($file_path)),
                /*文件的上次访问时间*/
                "fileatime" => fileatime($file_path),
                "fatime" => date("Y-m-d H:i:s", fileatime($file_path)),
                /*文件的内容上次被修改的时间*/
                "filemtime" => filemtime($file_path) . randomString(3, "n"),
                "fmtime" => date("Y-m-d H:i:s", filemtime($file_path)),
                /*文件的所有者*/
                "fileowner" => fileowner($file_path),
                /*文件的大小 byte(字节)*/
                "filesize" => filesize($file_path),
            );
        }
        //输出该文件
        // echo $list, '<br>';
        //判断当前是否为目录
        if (is_dir($file_path)) {
            //是目录
            $arr["dir"][] = array(
                /*完整目录路径(决对路径)*/
                "path" => $file_path,
                /*相对路径*/
                "relative" => $relative_path,
                /*文件夹（目录）名称*/
                "dirName" => $list,
            );
            readDirs($dir . '/' . $list);
        }

    }
    closeDir($dir_handle);
    return $arr;
}

/**
 * 遍历删除目录和目录下所有文件
 * @param $dir
 * @return bool
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

/**
 * 返回数组的维度
 * @$arr    array   数组
 * @return  number  数组的维度
 */
function arrayLevel($arr)
{
    $al = array(0);
    if (!function_exists('aL')) {
        function aL($arr, &$al, $level = 0)
        {
            if (is_array($arr)) {
                $level++;
                $al[] = $level;
                foreach ($arr as $k => $v) {
                    aL($v, $al, $level);
                }
            }
        }
    }
    aL($arr, $al);
    return max($al);
}

/**
 * 数组排序 使用例如： sortArrByManyField($arr,'id',SORT_DESC,'field2',SORT_ASC)
 * @return mixed|null
 * @throws Exception
 */
function sortArrByManyField()
{
    $args = func_get_args();
    if (empty($args)) {
        return null;
    }
    $arr = array_shift($args);
    if (!is_array($arr)) {
        throw new Exception("第一个参数不为数组");
    }
    foreach ($args as $key => $field) {
        if (is_string($field)) {
            $temp = array();
            foreach ($arr as $index => $val) {
                $temp[$index] = $val[$field];
            }
            $args[$key] = $temp;
        }
    }
    $args[] = &$arr;//引用值
    call_user_func_array('array_multisort', $args);
    return array_pop($args);
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
 * 数据过滤函数
 * @param string|array $data 待过滤的字符串或字符串数组
 * @param bool $force 为true时忽略get_magic_quotes_gpc
 * @param bool $is_htmlspecialchars 为true时，防止被挂马，跨站攻击
 * @param bool $regexp 正则匹配转义字符
 * @return array|null|string|string[]
 */
function input($data, $force = false, $is_htmlspecialchars = false, $regexp = false)
{
    if (is_string($data)) {
        $data = trim($is_htmlspecialchars ? htmlspecialchars($data) : $data);
        if (($force == true) || (!get_magic_quotes_gpc())) {
            $data = addslashes($data); // 防止sql注入
        }
        if ($regexp) {
            if (is_array($regexp)) {
                $regexp = join('|', $regexp);
            }
            $data = preg_replace('/(' . $regexp . ')/', '\\\\$1', $data);
        }
        return $data;
    } elseif (is_array($data)) {
        foreach ($data as $key => $value) {
            $data[$key] = input($value, $force, $is_htmlspecialchars, $regexp);
        }
        return $data;
    } else {
        return $data;
    }
}

/**
 * 数据还原函数
 * @param unknown $data
 * @return string unknown
 */
function out($data)
{
    if (is_string($data)) {
        return $data = stripslashes($data);
    } elseif (is_array($data)) {
        foreach ($data as $key => $value) {
            $data[$key] = out($value);
        }
        return $data;
    } else {
        return $data;
    }
}

/**
 * 文本输入
 * @param unknown $str
 * @return Ambigous <mixed, string>
 */
function text_in($str)
{
    $str = strip_tags($str, '<br>');
    $str = str_replace(" ", "&nbsp;", $str);
    $str = str_replace("\n", "<br>", $str);
    if (!get_magic_quotes_gpc()) {
        $str = addslashes($str);
    }
    return $str;
}

/**
 * 文本输出
 * @param unknown $str
 * @return string
 */
function text_out($str)
{
    $str = str_replace("&nbsp;", " ", $str);
    $str = str_replace("<br>", "\n", $str);
    $str = stripslashes($str);
    return $str;
}

/**
 * html代码输入
 * @param unknown $str
 * @return string
 */
function html_in($str)
{
    $search = array(
        "'<script[^>]*?>.*?</script>'si", // 去掉 javascript
        "'<iframe[^>]*?>.*?</iframe>'si"  // 去掉iframe
    );
    $replace = array("", "");
    $str = @preg_replace($search, $replace, $str);
    $str = htmlspecialchars($str);
    if (!get_magic_quotes_gpc()) {
        $str = addslashes($str);
    }
    return $str;
}

/**
 * html代码输出
 * @param unknown $str
 * @return string
 */
function html_out($str)
{
    if (function_exists('htmlspecialchars_decode')) {
        $str = htmlspecialchars_decode($str);
    } else {
        $str = html_entity_decode($str);
    }
    $str = stripslashes($str);
    return $str;
}

/**分级列表部分 start */
$GLOBALS['tree'] = array(
    'pid' => 'pid',
    'id' => 'id',
    'tableName' => 'tree',
);
/**
 * 无限级分类树
 * @$arr   array  传入二维数，里面的一维数组存在父id和id
 * @$pid   string 父id
 * @$di    string id
 * @return array 返回树型结构的数组（多维数组）
 */
function get_tree($arr)
{
    $items = array();
    $pid = $GLOBALS['tree']['pid'];
    $id = $GLOBALS['tree']['id'];
    foreach ($arr as $key => $value) {
        $items[$value[$id]] = $value;
    }
    $tree = array(); //格式化好的树
    foreach ($items as $key => $item) {
        if (isset($items[$item[$pid]])) {
            $items[$item[$pid]]['is_have_child'] = 1;  //标记改元素有子元素
            $items[$item[$pid]]['child'][] = &$items[$item[$id]];
        } else {
            $tree[] = &$items[$item[$id]];
        }
    }
    return $tree;
}

/**
 * 给树形结构的多维数组添加属性 level,add_str,用于分级列表显示
 * @$treeArr        array  多维数组（树形结构的多维数组，结合方法 get_tree() ）
 * @$lv            number  用于记录子元素所在的层级
 * @$str1       string
 * @$str2       string
 */
function set_level(&$treeArr, $lv = 0, $str1 = '&nbsp;&nbsp;&nbsp;&nbsp;', $str2 = ' └ ')
{
    foreach ($treeArr as $key => $value) {
        if (isset($value['child'])) {
            set_level($treeArr[$key]['child'], $lv + 1, $str1, $str2);
        }
        $treeArr[$key]['level'] = $lv;
        $treeArr[$key]['add_str'] = add_str($lv, $str1, $str2);
    }
}

/**
 * @$new_arr    array       引用传入一个数组，用来组成一个新的二维数组(一个有层次的二维数组)
 * @$arrTree    array       树型结构的数组，多维数组，配合方法 get_tree() 使用
 */
function tree_level(&$new_arr, $arrTree)
{
    $pid = $GLOBALS['tree']['pid'];
    $id = $GLOBALS['tree']['id'];
    foreach ($arrTree as $key => $value) {
        if (isset($value['child'])) {
            $value_copy = $value;
            //$value_copy['level']= get_id_level($value[$id],$data);
            unset($value_copy['child']);
            $new_arr[] = $value_copy;
            unset($value_copy);
            tree_level($new_arr, $value['child']);
        } else {
            //$value['level']= get_id_level($value[$id],$data);
            $new_arr[] = $value;
        }
    }
}

/**
 * @$new_arr    array       引用传入一个数组，用来组成一个新的二维数组(一个有层次的二维数组,不去除子元素)
 * @$arrTree    array       树型结构的数组，多维数组，配合方法 get_tree() 使用
 */
function tree_level_all(&$new_arr, $arrTree)
{
    $pid = $GLOBALS['tree']['pid'];
    $id = $GLOBALS['tree']['id'];
    foreach ($arrTree as $key => $value) {
        if (isset($value['child'])) {
            $new_arr[] = $value;
            tree_level_all($new_arr, $value['child']);
        } else {
            $new_arr[] = $value;
        }
    }
}

function add_str($lv, $str1 = '&nbsp;&nbsp;&nbsp;&nbsp;', $str2 = ' └ ')
{
    for ($i = 0, $st = ''; $i < $lv; $i++) {
        $st .= $str1;
    }
    return $lv > 0 ? $st . $str2 : '';
}

/**
 * 获取多级列表
 * @$id_v       string id值，主要给该id值下的所有子元素设置 disabled 属性值为 1
 * @$lv         number 要显示的列表缩进级别
 * @data        array  二维数组，里面的一维数组存在父id和id
 * @return      array  返回有层次的二维数组
 */
function get_level_list($id_v = 1, $lv = 2, $data = '', $str1 = '&nbsp;&nbsp;&nbsp;&nbsp;', $str2 = ' └ ')
{
    $pid = $GLOBALS['tree']['pid'];
    $id = $GLOBALS['tree']['id'];
    if (is_array($data)) {
        foreach ($data as $key => $value) {
            if ($value[$id] == $id_v) {
                $table_data = $value;
                break;
            }
        }
        unset($key, $value);
    }

    //生成新的拥有层次的二维数组 ( $new_arr )
    $new_arr = array();
    $treeArr = get_tree($data);
    set_level($treeArr, 0, $str1, $str2);
    tree_level_all($new_arr, $treeArr);

    //筛选要渲染的元素（下面只显示二级缩进 列表）
    foreach ($new_arr as $key => $value) {
        if ($value['level'] > $lv) {
            unset($new_arr[$key]);
        }
    }

    //获取该元素的子元素 id(type_id)一维数组
    if (isset($table_data[$id])) {
        foreach ($new_arr as $k => $v) {
            if ($v[$id] == $table_data[$id]) {
                $treeArr1 = $v;
            }
        }
        tree_level($new_arr1, [$treeArr1]);
        $child_arr['c'] = $new_arr1;
        foreach ($child_arr['c'] as $key => $value) {
            $child_arr['n'][] = $value[$id];
        }
        isset($child_arr['n']) or ($child_arr['n'] = array());
        //给新数组添加属性方便前台模板渲染
        foreach ($new_arr as $key => $value) {
            $new_arr[$key]['add_str'] = add_str($value['level'], $str1, $str2);
            $new_arr[$key]['disabled'] = in_array($value[$id], $child_arr['n']) ? 1 : 0;
        }
        unset($child_arr);
    } else {
        foreach ($new_arr as $key => $value) {
            $new_arr[$key]['add_str'] = add_str($value['level'], $str1, $str2);
        }
    }
    unset($treeArr, $child_arr, $table_data, $new_arr1);
    return $new_arr;
}

/**
 * 获取树的路径
 * @param array $lv2_array  要查询的数组（二维数组）
 * @param array $new_array  存放查到的内容
 * @param int $id 要查找的子元素 id 值
 * @param string $id_field  查询的数组里面的id字段名称
 * @param string $pid_field  查询的数组里面的pid字段名称
 * @param bool $init
 * @param int $index
 * @return array
 */
function get_tree_path($lv2_array, &$new_array, $id = 0, $id_field = 'id', $pid_field = 'pid', $init = true, $index = 1)
{
    $index++;
    if (is_array($lv2_array) && count($lv2_array) > 0) {
        //是否格式化数组
        $init && ($lv2_array = reconfig_array($lv2_array, $id_field));
        $pid = $lv2_array[$id][$pid_field];
        $id = $lv2_array[$id][$id_field];
        //把当前的子元素添加入数组
        $init && $id && ($new_array[] = $lv2_array[$id]);
        if ($pid > 0) {
            $lv2_array[$pid] && ($new_array[] = $lv2_array[$pid]);
            //只允许 99 次递归，防止内存溢出
            ($index < 100) && get_tree_path($lv2_array, $new_array, $pid, $id_field, $pid_field, false, $index);
        }
    }
    return count($new_array) > 0 ? array_reverse($new_array,true) : false;
}

/**分级列表部分 end */

/**
 * 分页数据
 * @param $rowCount integer 总条数
 * @param $param array 引用传递参数 $request->param();
 * @param $g array 引用传递参数 $request->get();
 */
function paging($rowCount, &$param, &$g)
{
    ($param['page_rows']) or ($param['page_rows'] = 15);
    ($param['page']) or ($param['page'] = 1);

    //当前页数
    $g['page'] = intval($param['page']);
    //每页的行数
    $g['page_rows'] = intval($param['page_rows']);
    //总行数
    $g['rowCount'] = $rowCount;

    //总页数
    $g['pageCount'] = ($g['rowCount'] % $g['page_rows'] === 0) ? ($g['rowCount'] / $g['page_rows']) : ~~($g['rowCount'] / $g['page_rows']) + 1;
    //上一页
    $g['left_page'] = ($g['page'] - 1) > 0 ? $g['page'] - 1 : 1;
    //下一页
    $g['right_page'] = ($g['page'] + 1) < $g['pageCount'] ? $g['page'] + 1 : $g['pageCount'];
    //开始显示第几条
    $g['start'] = (($g['page'] - 1) * $g['page_rows'] + 1) > $g['rowCount'] ? $g['rowCount'] : ($g['page'] - 1) * $g['page_rows'] + 1;
    //结束显示第几条
    $g['end'] = ($g['start'] + $g['page_rows'] - 1) < $g['rowCount'] ? ($g['start'] + $g['page_rows'] - 1) : $g['rowCount'];

    //显示的分页数字按钮数量
    ($param['btn_num']) or ($param['btn_num'] = 5);
    $g['btn_num'] = intval($param['btn_num']);
    //显示的分页按钮值 [1][2][3][4]...
    $min = $g['page'] - (~~($g['btn_num'] / 2)) < 1 ? 1 : $g['page'] - (~~($g['btn_num'] / 2));
    ($g['btn_num'] % 2 === 0 && $g['page'] + 1 >= $g['pageCount']) and ($min++);
    if ($g['page'] + (~~($g['btn_num'] / 2)) > $g['pageCount']) {
        $min = $min - ($g['page'] + (~~($g['btn_num'] / 2) - $g['pageCount']));
        ($min < 1) and ($min = 1);
    }
    $max = $g['page'] + (~~($g['btn_num'] / 2)) > $g['pageCount'] ? $g['page'] + (~~($g['btn_num'] / 2)) : $g['pageCount'];
    for ($i = 0, $j = $min; $i < $g['btn_num']; $i++) {
        if ($j > $g['pageCount']) {
            break;
        }
        $g['btn_list'][] = $j;
        $j++;
    }
}

/**
 * 发送HTTP状态
 * @param integer $code 状态码
 * @return void
 */
function send_http_status($code)
{
    static $_status = array(
        // Informational 1xx
        100 => 'Continue',
        101 => 'Switching Protocols',
        // Success 2xx
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        // Redirection 3xx
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Moved Temporarily ', // 1.1
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        // 306 is deprecated but reserved
        307 => 'Temporary Redirect',
        // Client Error 4xx
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        // Server Error 5xx
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        509 => 'Bandwidth Limit Exceeded'
    );
    if (isset($_status[$code])) {
        header('HTTP/1.1 ' . $code . ' ' . $_status[$code]);
        // 确保FastCGI模式下正常
        header('Status:' . $code . ' ' . $_status[$code]);
    }
}

/**
 * 获取客户端IP地址
 * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
 * @return mixed
 */
function get_client_ip($type = 0)
{
    $type = $type ? 1 : 0;
    static $ip = NULL;
    if ($ip !== NULL) return $ip[$type];
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $pos = array_search('unknown', $arr);
        if (false !== $pos) unset($arr[$pos]);
        $ip = trim($arr[0]);
    } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    // IP地址合法验证
    $long = sprintf("%u", ip2long($ip));
    $ip = $long ? array($ip, $long) : array('0.0.0.0', 0);
    return $ip[$type];
}

/**
 * 解析简单的数据格式的字符串如 aa=value1,bb=value2
 * @param string $str
 * @param string $cut_str_level1
 * @param string $cut_str_level2
 * @return array|bool
 */
function my_get_data($str = '', $cut_str_level1 = ',', $cut_str_level2 = '=')
{
    if ($str) {
        $level1_arr = explode($cut_str_level1, $str);
        $data = array();
        if (is_array($level1_arr) && count($level1_arr) > 0) {
            foreach ($level1_arr as $key => $value) {
                $level2_arr = explode($cut_str_level2, $value);
                $k = trim($level2_arr[0]);
                unset($level2_arr[0]);
                $v = join($cut_str_level2, $level2_arr);
                $data[$k] = $v;
            }
        }
        return $data;
    }
    return false;
}

/**
 * 生成简单的数据格式的字符串 传入一维关联数组
 * @param string|array $data
 * @param string $cut_str_level1
 * @param string $cut_str_level2
 * @return bool|string
 */
function my_set_data($data = '', $cut_str_level1 = ',', $cut_str_level2 = '=')
{
    if (is_array($data) || is_object($data)) {
        $str = '';
        foreach ($data as $key => $value) {
            $str .= $key . $cut_str_level2 . $value . $cut_str_level1;
        }
        $str = trim($str, $cut_str_level1);
        return $str;
    }
    return false;
}

function my_md5($str1 = '', $str2 = '')
{
    $str = strtolower($str1 . $str2);
    return md5($str);
}


/**
 * 设置数组, 如: set_array($arr,'lv1.lv2',$value); set_array($arr,array('lv1','lv2'),$value);
 * @param $arr
 * @param string $name
 * @param string $value
 * @param int $index
 * @return mixed
 */
function set_array(&$arr, $name = '', $value = '', $index = 0)
{
    if ($name && is_array($name)) {
        $len = count($name);
        if ($len > 0 && $index < $len) {
            if (!isset($arr[$name[$index]]) || !is_array($arr[$name[$index]])) {
                $arr[$name[$index]] = array();
                if ($index == $len - 1) {
                    $arr[$name[$index]] = $value;
                    return $arr;
                }
            }
            set_array($arr[$name[$index]], $name, $value, $index + 1);
        }
    } elseif ($name && is_string($name) && strpos($name, '.') > 0) {
        $name = explode('.', $name);
        set_array($arr, $name, $value, $index);
    } else {
        $arr[$name] = $value;
    }
}

/**
 * 获取数组 如： get_array($arr,'lv1.lv2'); get_array($arr,array('lv1','lv2'));
 * @param $arr
 * @param $name
 * @param int $index
 * @return mixed
 */
function get_array(&$arr, $name, $index = 0)
{
    if ($name && is_array($name)) {
        $len = count($name);
        if ($len > 0 && $index < $len) {
            if ($index == $len - 1) {
                return $arr[$name[$index]];
            }
            return get_array($arr[$name[$index]], $name, (int)$index + 1);
        }
    } elseif ($name && is_string($name) && strpos($name, '.') > 0) {
        $name = explode('.', $name);
        return get_array($arr, $name, $index);
    } else {
        return $name ? $arr[$name] : $arr;
    }
}

/**
 * 设置和获取值【获取值如： C('aa.bb')、C()，设置值如：C('aa.bb',$value)、C($array_value)】
 * @param string|array $name
 * @param string|array $value
 * @return array|mixed
 */
function C($name = '', $value = '')
{
    static $data = array();

    if ($name && is_string($name) && $value) {
        //设置值
        set_array($data, $name, $value);
    } elseif ($name && is_array($name) && empty($value)) {
        //批量设置值
        foreach ($name as $k => $item) {
            $data[$k] = $item;
        }
    } elseif ($name && is_string($name) && empty($value)) {
        //获取值
        return get_array($data, $name);
    } else {
        //获取所有值
        return $data;
    }
}

/**
 * 必须是字母
 * @param $str
 * @return false|int
 */
function is_alp($str)
{
    return preg_match('/^[a-zA-Z]+$/', $str);
}

/**
 * 验证必须是汉字
 * @param $str
 * @param string $encoding
 * @return false|int
 */
function is_chs($str, $encoding = 'utf-8')
{
    return strtolower($encoding) === 'utf-8' ? preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', $str) : preg_match("/^[\x7f-\xff]+$/", $str);
}

/**
 * 必须是数字
 * @param $str
 * @return false|int
 */
function is_num($str)
{
    return preg_match('/^[0-9]+$/', $str);
}

/**
 * 必须是字母、数字、横线、下划线
 * @param $str
 * @return false|int
 */
function is_ald($str)
{
    return preg_match("/^[\-_—a-zA-Z0-9]+$/", $str);
}

/**
 * 必须是汉字、字母
 * @param $str
 * @param string $encoding
 * @param string $add_preg 额外添加的匹配字符
 * @return false|int
 */
function is_chsAlpha($str, $encoding = 'utf-8', $add_preg = '')
{
    return strtolower($encoding) === 'utf-8' ? preg_match('/^[a-zA-Z' . $add_preg . '\x{4e00}-\x{9fa5}]+$/u', $str) : preg_match("/^[a-zA-Z" . $add_preg . "\x7f-\xff]+$/", $str);
}

/**
 * 必须是汉字、字母和数字
 * @param $str
 * @param string $encoding
 * @param string $add_preg 额外添加的匹配字符
 * @return false|int
 */
function is_chsAlphaNum($str, $encoding = 'utf-8', $add_preg = '')
{
    return strtolower($encoding) === 'utf-8' ? preg_match('/^[a-zA-Z0-9' . $add_preg . '\x{4e00}-\x{9fa5}]+$/u', $str) : preg_match("/^[a-zA-Z0-9" . $add_preg . "\x7f-\xff]+$/", $str);
}

/**
 * 必须是汉字、字母、数字和下划线_及破折号-
 * @param $str
 * @param string $encoding
 * @param string $add_preg 额外添加的匹配字符
 * @return false|int
 */
function is_chsDash($str, $encoding = 'utf-8', $add_preg = '')
{
    return strtolower($encoding) === 'utf-8' ? preg_match('/^[\-_—a-zA-Z0-9' . $add_preg . '\x{4e00}-\x{9fa5}]+$/u', $str) : preg_match("/^[\-_—a-zA-Z0-9" . $add_preg . "\x7f-\xff]+$/", $str);
}

/**
 * 必须是电话号码（支持手机号码，3-4位区号，7-8位直播号码，1－4位分机号）
 * @param $str
 * @return false|int
 */
function is_phone($str)
{
    return preg_match('/((\d{11})|^((\d{7,8})|(\d{4}|\d{3})-(\d{7,8})|(\d{4}|\d{3})-(\d{7,8})-(\d{4}|\d{3}|\d{2}|\d{1})|(\d{7,8})-(\d{4}|\d{3}|\d{2}|\d{1}))$)/', $str);
}

/**
 * 二维数组查询(单字段查询),返回匹配项的数组（二维数组或者一维数组）
 * @param array $lv2_array 要查询的二维数组
 * @param string $field 要查询的字段
 * @param string $options 查询表达式(=|>|<|>=|<=|<>|regexp|==|!=)
 * @param string|int|double $value 查询条件(值)
 * @param string $get_field 匹配的列名称(字段名称)，不为空返回一维数组
 * @return bool|array
 */
function level2_array_query($lv2_array, $field = '', $options = '==', $value = '', $get_field = '')
{
    $options_filter = ['==', '=', '>', '<', '>=', '<=', 'regexp', '!=', '<>'];
    if (arrayLevel($lv2_array) == 2 && $field !== '' && in_array(strtolower($options), $options_filter)) {
        foreach ($lv2_array as $index => $item) {
            if (strtolower($options) === 'regexp' && preg_match('/' . $value . '/i', $item[$field])) {
                $new_lv2_array[] = $item;
                $get_field && ($lv1_array_field[] = $item[$get_field]);
            } elseif ($options === '>' && is_numeric($value) && $item[$field] > $value) {
                $new_lv2_array[] = $item;
                $get_field && ($lv1_array_field[] = $item[$get_field]);
            } elseif ($options === '<' && is_numeric($value) && $item[$field] < $value) {
                $new_lv2_array[] = $item;
                $get_field && ($lv1_array_field[] = $item[$get_field]);
            } elseif (in_array($options, ['!=', '<>']) && $item[$field] != $value) {
                $new_lv2_array[] = $item;
                $get_field && ($lv1_array_field[] = $item[$get_field]);
            } elseif ($options === '>=' && is_numeric($value) && $item[$field] >= $value) {
                $new_lv2_array[] = $item;
                $get_field && ($lv1_array_field[] = $item[$get_field]);
            } elseif ($options === '<=' && is_numeric($value) && $item[$field] <= $value) {
                $new_lv2_array[] = $item;
                $get_field && ($lv1_array_field[] = $item[$get_field]);
            } else {
                if (in_array($options, ['==', '=']) && $item[$field] == $value) {
                    //存储获取到的匹配项
                    $new_lv2_array[] = $item;
                    //存储获取到的匹配项的某个字段
                    $get_field && ($lv1_array_field[] = $item[$get_field]);
                }
            }
        }
        return $get_field ? $lv1_array_field : $new_lv2_array;
    }
    return false;
}

/**
 * 数字编码转换为字符
 * @param $code
 * @return string
 */
function chr_utf8($code)
{
    if ($code < 128) {
        $utf = chr($code);
    } else if ($code < 2048) {
        $utf = chr(192 + (($code - ($code % 64)) / 64));
        $utf .= chr(128 + ($code % 64));
    } else {
        $utf = chr(224 + (($code - ($code % 4096)) / 4096));
        $utf .= chr(128 + ((($code % 4096) - ($code % 64)) / 64));
        $utf .= chr(128 + ($code % 64));
    }
    return $utf;
}

/**
 * 字符转换为数字编码
 * @param $string
 * @param $offset
 * @return float|int
 */
function ord_utf8($string, &$offset)
{
    $code = ord(substr($string, $offset, 1));
    if ($code >= 128) {
        if ($code < 224) {
            //otherwise 0xxxxxxx
            $bytesnumber = 2;
        } else if ($code < 240) {
            //110xxxxx
            $bytesnumber = 3;
        } else if ($code < 248) {
            //1110xxxx
            $bytesnumber = 4;
        }
        //11110xxx
        $codetemp = $code - 192 - ($bytesnumber > 2 ? 32 : 0) - ($bytesnumber > 3 ? 16 : 0);
        for ($i = 2; $i <= $bytesnumber; $i++) {
            $offset++;
            //10xxxxxx
            $code2 = ord(substr($string, $offset, 1)) - 128;
            $codetemp = $codetemp * 64 + $code2;
        }
        $code = $codetemp;
    }
    $offset += 1;
    if ($offset >= strlen($string)) {
        $offset = -1;
    }
    return $code;
}

/**
 * 给多级列表添加字段 block 区块，用与记录下级跟上级之间的关系，更好的处理数据 【1.0】
 * @param array $new_lv2_array 引用变量，构建新的数组
 * @param array $lv2_array 二维关联数组（比如从数据库读取的数据）
 * @param bool $is_reconfig_array 是否整理数组
 * @param int $r 递归次数
 * @param int $r_max 限制最大递归次数，防止内存溢出奔溃(也可以当做多级列表级别)
 */
function set_tree_block(&$new_lv2_array, $lv2_array, $is_reconfig_array = true, $r = 1, $r_max = 10)
{
    error_reporting(5);
    $pid = $GLOBALS['tree']['pid'];
    $id = $GLOBALS['tree']['id'];
    if ($is_reconfig_array === true) {
        $items = array();
        foreach ($lv2_array as $index => $item) {
            $items[$item[$id]] = $item;
        }
        $new_lv2_array = $items;
    }
    foreach ($new_lv2_array as $index => $item) {
        if ($item[$pid] == 0) {
            $new_lv2_array[$index]['block'] = '0,';
        } elseif ($item[$pid] > 0 && $new_lv2_array[$item[$pid]] && $new_lv2_array[$item[$pid]]['block']) {
            $new_lv2_array[$index]['block'] || ($new_lv2_array[$index]['block'] = $new_lv2_array[$item[$pid]]['block'] . $new_lv2_array[$item[$pid]][$id] . ',');
        } else {
            continue;
        }
    }
    ($r < $r_max) && set_tree_block($new_lv2_array, $lv2_array, false, $r + 1, $r_max);
}

/**
 * 求两个日期之间相差的天数(默认$day1 大于 $day2)
 * @param string $day1
 * @param string $day2
 * @return float|int
 */
function diff_between2days($day1 = '', $day2 = ''){
    $second1 = strtotime($day1);
    $second2 = strtotime($day2);
    if ($second1 < $second2) {
        $tmp = $second2;
        $second2 = $second1;
        $second1 = $tmp;
    }
    return ($second1 - $second2) / 86400;
}

/**
 * 返回毫秒
 * @return float
 */
function m_second(){
    $t = strval(microtime());
    $l = explode(' ',$t);
    $s = $l[1];
    $ms = ~~(floatval($l[0]) * 1000);
    $v = strval($s) . strval($ms);
    return (float)$v;
}

/**
 * 返回微秒
 * @return string
 */
function u_second(){
    $t = strval(microtime());
    $l = explode(' ',$t);
    $s = $l[1];
    $ms = ~~(floatval($l[0]) * 1000000);
    $v = strval($s) . strval($ms);
    return $v;
}

function my_encode64($data){
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function my_decode64($data){
    return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
}

/**
 * 输出html代码
 * @param string $str
 * @param string|array $del_tagName
 * @return null|string|string[]
 */
function out_html($str, $del_tagName = 'script|iframe')
{
    if(!empty($del_tagName) && is_string($del_tagName)){
        $del_tagName = explode('|',$del_tagName);
    }
    if(is_array($del_tagName)){
        foreach ($del_tagName as $index => $tagName) {
            if(!in_array(strtolower($tagName),array('br','hr','input','img','link','meta'))){
                $search[] = "/<{$tagName}[^>]*?>.*?<\/{$tagName}>/si";
            }else{
                $search[] = "/<{$tagName}[^>]*?>/si";
            }
            $replace[] = "";
        }
    }else{
        $search = array(
            "/<script[^>]*?>.*?<\/script>/si", //去掉 javascript
            "/<iframe[^>]*?>.*?<\/iframe>/si", //去掉 iframe
        );
        $replace = array("", "");
    }
    $str = preg_replace($search, $replace, $str);
    if (function_exists('htmlspecialchars_decode')) {
        $str = htmlspecialchars_decode($str);
    } else {
        $str = html_entity_decode($str);
    }
    $str = stripslashes($str);
    return $str;
}




if (!function_exists('my_dump')) {
    function my_dump($var, $echo = true, $label = null, $strict = true)
    {
        $label = ($label === null) ? '' : rtrim($label) . ' ';
        if (!$strict) {
            if (ini_get('html_errors')) {
                $output = print_r($var, true);
                $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
            } else {
                $output = $label . print_r($var, true);
            }
        } else {
            ob_start();
            var_dump($var);
            $output = ob_get_clean();
            if (!extension_loaded('xdebug')) {
                $output = preg_replace('/\]\=\>\n(\s+)/m', '] => ', $output);
                $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
            }
        }
        if ($echo) {
            echo($output);
            return null;
        } else
            return $output;
    }
}

if (!function_exists('isMobile')) {
    /**
     * 功能：判断是否是移动端访问
     * @return bool
     */
    function isMobile()
    {
        // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
        if (isset($_SERVER['HTTP_X_WAP_PROFILE'])) {
            return true;
        }
        //此条摘自TPM智能切换模板引擎，适合TPM开发
        if (isset($_SERVER['HTTP_CLIENT']) && 'PhoneClient' == $_SERVER['HTTP_CLIENT']) {
            return true;
        }
        //如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
        if (isset($_SERVER['HTTP_VIA'])) //找不到为flase,否则为true
        {
            return stristr($_SERVER['HTTP_VIA'], 'wap') ? true : false;
        }
        //判断手机发送的客户端标志,兼容性有待提高
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $clientkeywords = array(
                'nokia', 'sony', 'ericsson', 'mot', 'samsung', 'htc', 'sgh', 'lg', 'sharp', 'sie-', 'philips', 'panasonic', 'alcatel', 'lenovo', 'iphone', 'ipod', 'blackberry', 'meizu', 'android', 'netfront', 'symbian', 'ucweb', 'windowsce', 'palm', 'operamini', 'operamobi', 'openwave', 'nexusone', 'cldc', 'midp', 'wap', 'mobile',
            );
            //从HTTP_USER_AGENT中查找手机浏览器的关键字
            if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
                return true;
            }
        }
        //协议法，因为有可能不准确，放到最后判断
        if (isset($_SERVER['HTTP_ACCEPT'])) {
            // 如果只支持wml并且不支持html那一定是移动设备

            // 如果支持wml和html但是wml在html之前则是移动设备
            if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
                return true;
            }
        }
        return false;
    }
}

if (!function_exists('inc_files')) {
    /**
     * 批量引用文件
     * @param $path array|string 引用的文件路径
     * @param $root_dir boolean|string 设置文件引用的根目录路径
     * @param $param array|string 传入一些变量参数
     */
    function inc_files($path, $root_dir = false, $param = '')
    {
        if (is_array($param)) {
            foreach ($param as $key => $value) {
                ${$key} = $value;
            }
        }
        if (is_array($path)) {
            foreach ($path as $key => $value) {
                inc_files($value, $root_dir);
            }
        } else {
            if (!in_array($root_dir, [false, null, ''])) {
                $path = preg_replace('/[\\\\\/]{2,}/i', '/', str_replace('\\', '/', $root_dir . '/' . $path));
            }
            if (is_file($path) && file_exists($path)) {
                return include_once $path;
            }
        }
    }
}
if (!function_exists('get_path_info')) {
    /**
     * @return mixed|string
     */
    function get_path_info()
    {
        if ($_SERVER['REDIRECT_PATH_INFO']) {
            return trim($_SERVER['REDIRECT_PATH_INFO'], '/');
        } elseif ($_SERVER['PATH_INFO']) {
            return trim($_SERVER['PATH_INFO'], '/');
        } elseif ($_SERVER['REQUEST_URI']) {
            $REQUEST_URI = preg_replace('/\?+.*/i', '', $_SERVER['REQUEST_URI']);
            $SCRIPT_NAME = str_replace('/', '\\/', $_SERVER['SCRIPT_NAME']);
            $REQUEST_URI = preg_replace('/' . $SCRIPT_NAME . '/i', '', $REQUEST_URI, 1);
            return trim($REQUEST_URI, '/');
        } else {
            return str_replace($_SERVER['SCRIPT_NAME'] . '/', '', $_SERVER['PHP_SELF']);
        }
    }
}
if (!function_exists('get_param')) {
    /**
     * @param string $name
     * @param int $offset 从第几项开始截取
     * @return array|mixed
     */
    function get_param($name = '', $offset = 2)
    {
        $path_info = get_path_info();
        $arr1 = explode('/', $path_info);
        $path_info_arr = array();
        $arr = array();
        /*foreach ($arr1 as $k1 => $v1){
            if($k1 >= $offset){
                $path_info_arr[] = $v1;
            }
        }*/
        $path_info_arr = array_slice($arr1, $offset);
        if (is_array($path_info_arr) && ($len = count($path_info_arr)) > 1) {
            foreach ($path_info_arr as $key => $value) {
                if ($key % 2 === 0) {
                    $arr[$path_info_arr[$key]] = $path_info_arr[$key + 1];
                }
            }
        }
        unset($arr1, $path_info_arr);
        return $name ? $arr[$name] : $arr;
    }
}
if (!function_exists('my_in_array')) {
    /**
     * 检测某个值是否存在 某个数组中
     * @param string|array|object|boolean|null $search_value
     * @param array $arr
     * @return bool
     */
    function my_in_array($search_value = '', $arr = array())
    {
        if (is_array($arr)) {
            foreach ($arr as $k => $v) {
                if ($search_value === $v) {
                    unset($arr, $k, $v, $search_value);
                    return true;
                }
            }
        }
        return false;
    }
}
if (!function_exists('field_array')) {
    /**
     * 获取二维数组里的某个字段的列值（一维数组）
     * @param array $array_level2 二维数组及多维数组
     * @param string $field
     * @param $newarray
     */
    function field_array($array_level2, $field = '', &$newarray)
    {
        if (is_array($array_level2) && arrayLevel($array_level2) > 1) {
            foreach ($array_level2 as $key => $value) {
                if (isset($value[$field]) && $value[$field] !== '') {
                    $newarray[] = $value[$field];
                }
            }
        }
    }
}
if (!function_exists('reconfig_array')) {
    /**
     * @param array $array_level2 二维数组及多维数组
     * @param string $index_field 设置某个字段的值为 数组下标
     * @return array
     */
    function reconfig_array($array_level2 = '', $index_field = 'id')
    {
        $arr = array();
        if (is_array($array_level2) && arrayLevel($array_level2) > 1) {
            foreach ($array_level2 as $k => $v) {
                $arr[$v[$index_field]] = $v;
            }
        }
        return $arr;
    }
}
if (!function_exists('del_html_tag')) {
    /**
     * 清除html标签
     * @param string $str
     * @return null|string|string[]
     */
    function del_html_tag($str = '')
    {
        $preg['tag'] = '/<.+?>/i';
        if (!is_array($str) || !is_object($str)) {
            $str = preg_replace($preg['tag'], '', $str);
        }
        return $str;
    }
}
if (!function_exists('string_filter')) {
    /**
     * 清除字符串里面的某些字符
     * @param string $str
     * @param array $filter
     * @return null|string|string[]
     */
    function string_filter($str = '', $filter = array("&nbsp;", "&emsp;", "\r", "\n", "\t"))
    {
        is_array($filter) && ($preg = join('|', $filter));
        return preg_replace("/($preg)/i", '', $str);
    }
}
if (!function_exists('detail_prev_next')) {
    /**
     * 构成文章的上一篇和下一篇的数据
     * @param array $lv2_array 二维数组【从数据库中获取的】
     * @param array $g
     * @param string $id
     */
    function detail_prev_next($lv2_array, &$g, $id = 'id')
    {
        $count = count($lv2_array);
        $Last = array(); //上一篇
        $Next = array(); //下一篇
        foreach ($lv2_array as $key => $item) {
            if ($item[$id] == $g[$id]) {
                if ($key - 1 >= 0) {
                    $Last = $lv2_array[$key - 1];
                }
                if ($key + 1 <= $count) {
                    $Next = $lv2_array[$key + 1];
                }
                break;
            }
        }
        $g['last'] = $Last;
        $g['next'] = $Next;
    }
}

if(!function_exists('is_email')){
    /**
     * 必须是邮箱地址
     * @param $str
     * @return false|int
     */
    function is_email($str)
    {
        return preg_match('/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/', $str);
    }
}
