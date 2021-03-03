<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/11/4
 * Time: 14:45
 */

class List_tree
{
    private $pid = 'pid';
    private $id = 'id';
    private $data = array();

    /**
     * 返回数组的维度
     * @param $arr
     * @return mixed
     */
    public function arrayLevel($arr)
    {
        $al = array(0);
        self::aL($arr, $al);
        return max($al);
    }

    /**
     * 配合方法 self::arrayLevel
     * @param $arr
     * @param $al
     * @param int $level
     */
    private function aL($arr, &$al, $level = 0)
    {
        if (is_array($arr)) {
            $level++;
            $al[] = $level;
            foreach ($arr as $k => $v) {
                self::aL($v, $al, $level);
            }
        }
    }

    /**
     * 浏览器友好的变量输出
     * @param mixed $var 变量
     * @param boolean $echo 是否输出 默认为True 如果为false 则返回输出字符串
     * @param string $label 标签 默认为空
     * @param boolean $strict 是否严谨 默认为true
     * @return void|string
     */
    public final function dump($var, $echo = true, $label = null, $strict = true)
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
        } else {
            return $output;
        }
    }

    /**
     * 数据过滤函数
     * @param string|array $data 待过滤的字符串或字符串数组
     * @param bool $force 为true时忽略get_magic_quotes_gpc
     * @param bool $is_htmlspecialchars 为true时，防止被挂马，跨站攻击
     * @param bool $regexp 正则匹配转义字符
     * @return array|null|string|string[]
     */
    public final function in($data, $force = false, $is_htmlspecialchars = false, $regexp = false)
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
                $data[$key] = self::in($value, $force, $is_htmlspecialchars, $regexp);
            }
            return $data;
        } else {
            return $data;
        }
    }

    /**
     * 无限级分类树
     * @param array $lv2_array 传入二维数，里面的一维数组存在父id和id
     * @return array
     */
    public final function get_tree($lv2_array)
    {
        $items = array();
        $pid = $this->pid;
        $id = $this->id;
        foreach ($lv2_array as $key => $value) {
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
     * @param array $treeArr 多维数组(树形结构的多维数组，结合方法 self::get_tree() )
     * @param int $lv
     * @param string $str1
     * @param string $str2
     */
    public final function set_level(&$treeArr, $lv = 0, $str1 = '&nbsp;&nbsp;&nbsp;&nbsp;', $str2 = ' └ ')
    {
        foreach ($treeArr as $key => $value) {
            if (isset($value['child'])) {
                self::set_level($treeArr[$key]['child'], $lv + 1, $str1, $str2);
            }
            $treeArr[$key]['level'] = $lv;
            $treeArr[$key]['add_str'] = self::add_str($lv, $str1, $str2);
        }
    }

    public function add_str($lv, $str1 = '&nbsp;&nbsp;&nbsp;&nbsp;', $str2 = ' └ ')
    {
        for ($i = 0, $st = ''; $i < $lv; $i++) {
            $st .= $str1;
        }
        return $lv > 0 ? $st . $str2 : '';
    }

    /**
     * 把树形结构的多维数组转换为一个有层次的二维数组
     * @param void $new_arr 引用传入一个变量或数组变量，用来存放一个有层次的二维数组
     * @param array $arrTree 树型结构的数组，多维数组，配合方法 get_tree() 使用
     */
    public final function tree_level(&$new_arr, $arrTree)
    {
        $pid = $this->pid;
        $id = $this->id;
        foreach ($arrTree as $key => $value) {
            if (isset($value['child'])) {
                $value_copy = $value;
                unset($value_copy['child']);
                $new_arr[] = $value_copy;
                unset($value_copy);
                self::tree_level($new_arr, $value['child']);
            } else {
                $new_arr[] = $value;
            }
        }
    }

    /**
     * 把树形结构的多维数组转换为一个有层次的多维维数组（二维数组不去除子元素）
     * @param void $new_arr 引用传入一个变量或数组变量，存放新构成的有层次的数组
     * @param array $arrTree 树型结构的数组，多维数组，配合方法 get_tree() 使用
     */
    public final function tree_level_all(&$new_arr, $arrTree)
    {
        foreach ($arrTree as $key => $value) {
            if (isset($value['child'])) {
                $new_arr[] = $value;
                self::tree_level_all($new_arr, $value['child']);
            } else {
                $new_arr[] = $value;
            }
        }
    }

    /**
     * @param int $id_v id值，主要给该id值下的所有子元素设置 disabled 属性值为 1
     * @param int $lv 要显示的列表缩进级别
     * @param string $data 二维数组，里面的一维数组存在父id和id
     * @param string $str1
     * @param string $str2
     * @return array 返回有层次的二维数组
     */
    public function get_level_list($id_v = 1, $lv = 2, $data = '', $str1 = '&nbsp;&nbsp;&nbsp;&nbsp;', $str2 = ' └ ')
    {
        $pid = $this->pid;
        $id = $this->id;
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
        $treeArr = self::get_tree($data);
        self::set_level($treeArr, 0, $str1, $str2);
        self::tree_level_all($new_arr, $treeArr);

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
            self::tree_level($new_arr1, [$treeArr1]);
            $child_arr['c'] = $new_arr1;
            foreach ($child_arr['c'] as $key => $value) {
                $child_arr['n'][] = $value[$id];
            }
            isset($child_arr['n']) or ($child_arr['n'] = array());
            //给新数组添加属性方便前台模板渲染
            foreach ($new_arr as $key => $value) {
                $new_arr[$key]['add_str'] = self::add_str($value['level'], $str1, $str2);
                $new_arr[$key]['disabled'] = in_array($value[$id], $child_arr['n']) ? 1 : 0;
            }
            unset($child_arr);
        } else {
            foreach ($new_arr as $key => $value) {
                $new_arr[$key]['add_str'] = self::add_str($value['level'], $str1, $str2);
            }
        }
        unset($treeArr, $child_arr, $table_data, $new_arr1);
        return $new_arr;
    }

    /**
     * 获取树的路径
     * @param array $lv2_array 要查询的数组（二维数组）
     * @param array $new_array 存放查到的内容
     * @param int $id 要查找的子元素 id 值
     * @param string $id_field 查询的数组里面的id字段名称
     * @param string $pid_field 查询的数组里面的pid字段名称
     * @param bool $init
     * @param int $index
     * @return array|bool
     */
    public final function get_tree_path($lv2_array, &$new_array, $id = 0, $id_field = 'id', $pid_field = 'pid', $init = true, $index = 1)
    {
        $index++;
        if (is_array($lv2_array) && count($lv2_array) > 0) {
            //是否格式化数组
            $init && ($lv2_array = self::reconfig_array($lv2_array, $id_field));
            $pid = $lv2_array[$id][$pid_field];
            $id = $lv2_array[$id][$id_field];
            //把当前的子元素添加入数组
            $init && $id && ($new_array[] = $lv2_array[$id]);
            if ($pid > 0) {
                $lv2_array[$pid] && ($new_array[] = $lv2_array[$pid]);
                //只允许 99 次递归，防止内存溢出
                ($index < 100) && self::get_tree_path($lv2_array, $new_array, $pid, $id_field, $pid_field, false, $index);
            }
        }
        return count($new_array) > 0 ? array_reverse($new_array, true) : false;
    }

    /**
     * @param string $array_level2 二维数组及多维数组
     * @param string $index_field 设置某个字段的值为数组下标
     * @return array
     */
    public function reconfig_array($array_level2 = '', $index_field = 'id')
    {
        $arr = array();
        if (is_array($array_level2) && self::arrayLevel($array_level2) > 1) {
            foreach ($array_level2 as $k => $v) {
                $arr[$v[$index_field]] = $v;
            }
        }
        return $arr;
    }

    /**
     * 给多级列表添加字段 block 区块，用与记录下级跟上级之间的关系，更好的处理数据 【1.0】
     * @param array $new_lv2_array 引用变量，构建新的数组
     * @param array $lv2_array 二维关联数组（比如从数据库读取的数据）
     * @param bool $is_reconfig_array 是否整理数组
     * @param int $r 递归次数
     * @param int $r_max 限制最大递归次数，防止内存溢出奔溃(也可以当做多级列表级别)
     */
    public final function set_tree_block(&$new_lv2_array, $lv2_array, $is_reconfig_array = true, $r = 1, $r_max = 7)
    {
        $pid = $this->pid;
        $id = $this->id;
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
        ($r < $r_max) && self::set_tree_block($new_lv2_array, $lv2_array, false, $r + 1, $r_max);
    }


    public function set_data($key = '', $value = '')
    {
        if (is_array($key)) {
            foreach ($key as $index => $item) {
                self::set_data($index, $item);
            }
        } else {
            $this->data[$key] = $value;
            if (in_array($key, array('id', 'pid')) && $value) {
                $this->{$key} = $value;
            }
        }
        return $this;
    }

    public function get_data($name)
    {
        if ($name) {
            return in_array($name, array('id', 'pid')) ? $this->{$name} : $this->data[$name];
        } else {
            return $this->data;
        }
    }

    /**
     * 设置数组, 如: set_array($arr,'lv1.lv2',$value); set_array($arr,array('lv1','lv2'),$value);
     * @param $arr
     * @param string $name
     * @param string $value
     * @param int $index
     * @return mixed
     */
    public final function set_array(&$arr, $name = '', $value = '', $index = 0)
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
                self::set_array($arr[$name[$index]], $name, $value, $index + 1);
            }
        } elseif ($name && is_string($name) && strpos($name, '.') > 0) {
            $name = explode('.', $name);
            self::set_array($arr, $name, $value, $index);
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
    public final function get_array(&$arr, $name, $index = 0)
    {
        if ($name && is_array($name)) {
            $len = count($name);
            if ($len > 0 && $index < $len) {
                if ($index == $len - 1) {
                    return $arr[$name[$index]];
                }
                return self::get_array($arr[$name[$index]], $name, (int)$index + 1);
            }
        } elseif ($name && is_string($name) && strpos($name, '.') > 0) {
            $name = explode('.', $name);
            return self::get_array($arr, $name, $index);
        } else {
            return $name ? $arr[$name] : $arr;
        }
    }

    /**
     * 设置和获取值【获取值如： self::C('aa.bb')、self::C()，设置值如：self::C('aa.bb',$value)、self::C($array_value)】
     * @param string|array $name
     * @param string|array $value
     * @return array|mixed
     */
    public final function C($name = '', $value = '')
    {
        //static $data = array();

        if ($name && is_string($name) && $value) {
            //设置值
            self::set_array($this->data, $name, $value);
        } elseif ($name && is_array($name) && empty($value)) {
            //批量设置值
            foreach ($name as $k => $item) {
                $this->data[$k] = $item;
            }
        } elseif ($name && is_string($name) && empty($value)) {
            //获取值
            return self::get_array($this->data, $name);
        } else {
            //获取所有值
            return $this->data;
        }
    }
}
