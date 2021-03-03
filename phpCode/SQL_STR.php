<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2020/6/17
 * Time: 23:24
 */

class SQL_STR
{
    public function __construct($C = array(), $fn = null)
    {
        error_reporting(5);//1+4
        date_default_timezone_set("Asia/Shanghai");
        //$args = func_get_args();
        //call_user_func_array(array($this, 'initialize'), $args);

        //数据库连接配置
        if (is_array($C)) {
            foreach ($C as $key => $item) {
                $this->config[$key] = $item;
            }
        }
        //拼接 dsn
        if ($this->config['dsn']) {
            $this->dsn = $this->config['dsn'];
        } elseif (strtolower($this->config['db_type']) === 'mysql') {
            $this->dsn = "{$this->config['db_type']}:host={$this->config['db_host']};dbname={$this->config['db_name']};port={$this->config['db_port']}";
        } elseif (strtolower($this->config['db_type']) === 'mssql') {
            $mssqldriver = '{SQL Server}';
            //$mssqldriver = '{SQL Server Native Client 11.0}';
            //$mssqldriver = '{ODBC Driver 11 for SQL Server}';
            $Server = $this->config['db_port'] ? "{$this->config['db_host']},{$this->config['db_port']}" : $this->config['db_host'];
            $this->dsn = "odbc:Driver=$mssqldriver;Server=$Server;Database={$this->config['db_name']}";
        } else {
            $this->dsn = "{$this->config['db_type']}:host={$this->config['db_host']};dbname={$this->config['db_name']};port={$this->config['db_port']}";
        }
        //连接数据库
        try {
            $this->conn = new PDO($this->dsn, $this->config['db_user'], $this->config['db_pass'], array(PDO::ATTR_PERSISTENT => $this->config['db_link_type']));
        } catch (Exception $exception) {
            isset($fn) and is_callable($fn) and ($fn($exception));
        }
        if (in_array(strtolower($this->config['db_type']), array('mysql'))) {
            //设置数据库编码
            $this->conn->query('SET NAMES ' . $this->config['db_charset']);
        }
        return $this;
    }

    /**
     * 把一维数组转换为特定字符串内容（用于sql语句）
     * @param string $lv1_array
     * @param string $name
     * @return string
     */
    public function array2string($lv1_array = '', $name = 'v')
    {
        $k = '';
        $v = '';
        $fields = '';
        if (is_array($lv1_array) && count($lv1_array) > 0) {
            foreach ($lv1_array as $key => $value) {
                $k .= "$key,";
                $v .= is_int($value) || is_float($value) ? "$value," : "'$value',";
                $fields .= is_int($value) || is_float($value) ? "$key=$value," : "$key='$value',";
            }
            $data['k'] = $k = trim($k, ',');
            $data['v'] = $v = trim($v, ',');
            $data['fields'] = $fields = trim($fields, ',');
            $data['insert'] = " ( $k )values( $v ) ";
            $data['update'] = " $fields ";
            $name = strtolower($name);
            return in_array($name, array('k', 'v', 'fields', 'insert', 'update')) ? $data[$name] : $data;
        } else {
            return '';
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
    public function input($data, $force = false, $is_htmlspecialchars = false, $regexp = false)
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
                $data[$key] = self::input($value, $force, $is_htmlspecialchars, $regexp);
            }
            return $data;
        } else {
            return $data;
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
    public function dump($var, $echo = true, $label = null, $strict = true)
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
     * 获取数据类型
     * @param $var
     * @return string
     */
    public function get_type($var)
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
     * 判断是否是关联数组
     * @param $arr
     * @return bool
     */
    public function is_assoc($arr)
    {
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    /**
     * sql语句参数绑定（主要在 where 部分）
     * @param string $strings
     * @param array $bind
     * @return mixed|string
     */
    public function where_param_bind($strings = '', $bind = array())
    {
        if ($strings && is_string($strings)) {
            if (is_array($bind) && count($bind) > 0) {
                foreach ($bind as $key => $value) {
                    if (is_int($value) || is_float($value)) {
                        $strings = str_replace(":$key", "$value", $strings);
                    } else {
                        $strings = str_replace(":$key", "'$value'", $strings);
                    }
                }
            }
        }
        return $strings;
    }

    private $config = array(
        //连接数据库服务器的地址
        "db_host" => "localhost",
        //连接数据库服务器的用户
        "db_user" => "root",
        //连接数据库服务器的密码
        "db_pass" => "123456",
        //连接数据库服务器的类型
        "db_type" => "mysql",
        //连接数据库服务器的端口
        "db_port" => 3306,
        //数据库编码默认采用utf8
        "db_charset" => "utf8",
        //连接数据库的名称
        "db_name" => "",
        //数据库连接类型（是否是长连接）
        "db_link_type" => false,
        //连接dsn,数据源名称或叫做 DSN，包含了请求连接到数据库的信息
        "dsn" => "",
    );
    //数据库连接对象
    private $conn;
    //数据源名称或叫做 DSN，包含了请求连接到数据库的信息。
    private $dsn;

    private $tableName = "";
    private $fields = "*";
    private $joins = "";
    private $wheres = "";
    private $groupBys = "";
    private $orders = "";
    private $limits = "";
    private $type = array(
        "insert" => "",
        "update" => "",
    );

    public function initialize()
    {
        $args = func_get_args();
        $C = $args[0];
        $fn = $args[1];
        //数据库连接配置
        if (is_array($C)) {
            foreach ($C as $key => $item) {
                $this->config[$key] = $item;
            }
        }
        //拼接 dsn
        if ($this->config['dsn']) {
            $this->dsn = $this->config['dsn'];
        } elseif (strtolower($this->config['db_type']) === 'mysql') {
            $this->dsn = "{$this->config['db_type']}:host={$this->config['db_host']};dbname={$this->config['db_name']};port={$this->config['db_port']}";
        } elseif (strtolower($this->config['db_type']) === 'mssql') {
            $mssqldriver = '{SQL Server}';
            //$mssqldriver = '{SQL Server Native Client 11.0}';
            //$mssqldriver = '{ODBC Driver 11 for SQL Server}';
            $Server = $this->config['db_port'] ? "{$this->config['db_host']},{$this->config['db_port']}" : $this->config['db_host'];
            $this->dsn = "odbc:Driver=$mssqldriver;Server=$Server;Database={$this->config['db_name']}";
        } else {
            $this->dsn = "{$this->config['db_type']}:host={$this->config['db_host']};dbname={$this->config['db_name']};port={$this->config['db_port']}";
        }
        //连接数据库
        try {
            $this->conn = new PDO($this->dsn, $this->config['db_user'], $this->config['db_pass'], array(PDO::ATTR_PERSISTENT => $this->config['db_link_type']));
        } catch (Exception $exception) {
            isset($fn) and is_callable($fn) and ($fn($exception));
        }
        if (in_array(strtolower($this->config['db_type']), array('mysql'))) {
            //设置数据库编码
            $this->conn->query('SET NAMES ' . $this->config['db_charset']);
        }
        return $this;
    }

    /**
     * 初始化变量（sql语句拼接）
     */
    public function init()
    {
        $this->fields = "*";
        $this->joins = "";
        $this->wheres = "";
        $this->groupBys = "";
        $this->orders = "";
        $this->limits = "";
        $this->type = array(
            "insert" => "",
            "update" => "",
        );
        return $this;
    }

    public function where_copy($field = '', $options = '', $condition = '')
    {
        $args = func_get_args();
        $args_len = count($args);
        $where_str = '';
        if (is_array($field) && !is_array($options) && !is_array($condition)) {
            $or_and = strtoupper($options);
            foreach ($field as $key => $value) {
                $where_str .= "$key = '$value' $or_and ";
            }
            $this->wheres .= rtrim($where_str, "$or_and ");
        } elseif ($field != '' && !is_array($field) && is_array($options) && in_array(strtolower($args[$args_len - 1]), array('or', 'and'))) {
            $field_arr = explode('|', $field);
            $or_and = strtoupper(trim($args[$args_len - 1]));
            if (is_array($field_arr) && count($field_arr) > 1) {
                //多字段相同条件查询 $options 为一维数组 array("$opt","$value")【$opt为where里面的运算字符如： =|<>|<|>|in|not in|like|not like|regexp|...】
                foreach ($field_arr as $field) {
                    $opt = $options[0];
                    $val = $options[1];
                    $where_str .= "$field $opt '$val' $or_and ";
                }
                $where_str = rtrim($where_str, "$or_and ");
            } else {
                //单字段多条件查询 $options 为二维数组 array(array("=","$value"),array("$opt1","$value1"),..)
                foreach ($options as $key => $value) {
                    if (is_array($value)) {
                        $opt = $value[0];
                        $val = $value[1];
                        $where_str .= "$field $opt '$val' $or_and ";
                    }
                }
                $where_str = substr($where_str, 0, strlen($where_str) - strlen($or_and) - 1);
            }
            $this->wheres .= "($where_str)";
        } elseif (is_string($field) && $field != '' && in_array(strtolower($options), array('in', 'not in'))) {
            if (is_array($condition)) {
                foreach ($condition as $key => $value) {
                    $where_str .= "'$value',";
                }
                $where_str = "(" . substr($where_str, 0, strlen($where_str) - 1) . ")";
                $this->wheres .= "$field $options $where_str";
            } else {
                $this->wheres .= "$field $options ($condition)";
            }
        } elseif (is_string($field) && $field != '' && empty($condition)) {
            //where 的字符串条件查询，也可以进行参数绑定(如： $this->where("field = :name", array("name"=>"value") ))
            if (is_array($options) && count($options) > 0) {
                $where_str = $field;
                foreach ($options as $key => $value) {
                    $where_str = str_replace(":$key", "'$value'", $where_str);
                }
            } elseif ($options != '' && !is_array($options) && !is_object($options)) {
                $where_str = "$field = '$options'";
            } else {
                $where_str = $field;
            }
            $this->wheres .= "($where_str)";
        } else {
            $this->wheres .= "$field $options '$condition' ";
        }
        $this->wheres = $this->wheres . " AND ";
        return $this;
    }

    /**
     * 构建 where 条件语句
     * @param string|array $field
     * @param string|array $options
     * @param string|array $condition
     * @return $this
     */
    public function where($field = '', $options = '', $condition = '')
    {
        $args = func_get_args();
        $args_len = count($args);
        $where_str = '';
        if (is_array($field) && in_array(strtolower($options), array('or', 'and')) && empty($condition)) {
            //$field 为数组的时候（1-3维数组）
            $arr_lv = self::arrayLevel($field);
            if ($arr_lv > 0 && $arr_lv < 4) {
                foreach ($field as $k => $v) {
                    if (is_array($v) && count($v) > 0) {
                        //['字段名', '选项表达式(=|<|>|<>|like|not like|regexp|not regexp|....)', '值', 'and|or']
                        $f = $v[0];
                        $opt = $v[1];
                        $val = $v[2];
                        $or_and = $v[3] ? strtoupper($v[3]) : 'AND';
                        if (in_array(strtolower(trim($opt)), array('in', 'not in'))) {
                            if (is_array($val)) {
                                $val = self::array2string($val, 'v');
                                $where_str .= "$f $opt ($val) $or_and ";
                            } else {
                                continue;
                            }
                        } else {
                            $where_str .= is_int($val) || is_float($val) ? "$f $opt $val $or_and " : "$f $opt '$val' $or_and ";
                        }
                    } else {
                        $or_and = $options ? strtoupper($options) : 'AND';
                        $where_str .= is_int($v) || is_float($v) ? "$k = $v $or_and " : "$k = '$v' $or_and ";
                    }
                }
                $where_str = rtrim(rtrim($where_str), $or_and);
                $this->wheres .= "($where_str)";
            }
        } elseif ($field && is_string($field) && in_array(strtolower($args[$args_len - 1]), array('or', 'and'))) {
            if ($args_len >= 3) {
                //同一字段多个查询条件,如： $this->where('name', ['like','abc%'], ['like','%haha%'], 'or')
                $or_and = strtoupper($args[$args_len - 1]);
                for ($i = 1, $len = $args_len - 1; $i < $len; $i++) {
                    if (is_array($args[$i]) && count($args[$i]) > 1) {
                        $opt = $args[$i][0];
                        $val = $args[$i][1];
                        if (is_array($val) && in_array(strtolower($opt), array('in', 'not in'))) {
                            $val = self::array2string($val, 'v');
                            $where_str .= "$field $opt ($val) $or_and ";
                        } elseif (is_int($val) || is_float($val)) {
                            $where_str .= "$field $opt $val $or_and ";
                        } else {
                            $where_str .= "$field $opt '$val' $or_and ";
                        }
                    }
                }
                $where_str = rtrim(rtrim($where_str), $or_and);
                $this->wheres .= "($where_str)";
            }
        } elseif (preg_match('/(\&|\|)/i', $field) && $options && is_string($options) && $condition && (is_array($condition) || is_string($condition))) {
            //多字段相同查询条件,如：$this->where('name|title', 'like', 'abc%');  $this->where('name&title', 'like', '%haha%');
            $preg = '/([a-zA-z0-9_]+\||[a-zA-z0-9_]+\&|[a-zA-z0-9_]+)/i';
            preg_match_all($preg, $field, $lv2_array);
            foreach ($lv2_array[0] as $k => $v) {
                if (strpos($v, '|') > 0) {
                    $new_array[] = array('field' => trim(trim($v), '|'), 'or_and' => 'OR', 'pre' => '|');
                } elseif (strpos($v, '&') > 0) {
                    $new_array[] = array('field' => trim(trim($v), '&'), 'or_and' => 'AND', 'pre' => '&');
                } else {
                    $new_array[] = array('field' => trim($v), 'or_and' => 'OR', 'pre' => '');
                }
            }
            foreach ($new_array as $k => $v) {
                $f = $v['field'];
                $or_and = $v['or_and'];
                if (is_array($condition) && in_array(strtolower($options), array('in', 'not in'))) {
                    $condition = self::array2string($condition, 'v');
                    $where_str .= "$f $options ($condition) $or_and ";
                } elseif (is_int($condition) || is_float($condition)) {
                    $where_str .= "$f $options $condition $or_and ";
                } else {
                    $where_str .= "$f $options '$condition' $or_and ";
                }
            }
            $where_str = rtrim(rtrim($where_str), $or_and);
            $this->wheres .= "($where_str)";

        } elseif ($field && is_string($field) && !is_object($options) && empty($condition)) {
            if (is_array($options)) {
                //where 的字符串条件查询，也可以进行参数绑定(如： $this->where("field = :name", array("name"=>"value") ))
                $where_str = self::where_param_bind($field, $options);
            } elseif ($options && !is_array($options)) {
                $where_str = is_int($options) || is_float($options) ? "$field = $options" : "$field = '$options'";
            } else {
                $where_str = $field;
            }
            $where_str = rtrim($where_str);
            $this->wheres .= "($where_str)";
        } else {
            if (is_array($condition) && in_array(strtolower($options), array('in', 'not in'))) {
                $condition = self::array2string($condition, 'v');
                $options = strtoupper($options);
                $this->wheres .= "$field $options ($condition) ";
            } elseif (is_int($condition) || is_float($condition)) {
                $this->wheres .= "$field $options $condition ";
            } else {
                $this->wheres .= "$field $options '$condition' ";
            }
        }
        $this->wheres = $this->wheres . " AND ";
        return $this;
    }

    public function whereOr()
    {
        $args = func_get_args();
        call_user_func_array(array($this, 'where'), $args);
        $this->wheres = rtrim(rtrim($this->wheres), 'AND') . 'OR ';
        return $this;
    }

    /**
     * 左连接
     * @param string $join 如：tableName t
     * @param string $condition 如：t.id = a.id
     * @return $this
     */
    public function leftJoin($join = '', $condition = '')
    {
        $this->joins .= "LEFT JOIN $join ON $condition ";
        return $this;
    }

    /**
     * 右连接
     * @param string $join 如：tableName t
     * @param string $condition 如：t.id = a.id
     * @return $this
     */
    public function rightJoin($join = '', $condition = '')
    {
        $this->joins .= "RIGHT JOIN $join ON $condition ";
        return $this;
    }

    /**
     * 连接
     * @param string $join
     * @param string $condition
     * @return $this
     */
    public function join($join = '', $condition = '')
    {
        $this->joins .= "JOIN $join ON $condition ";
        return $this;
    }

    /**
     * 截取数据范围
     * @param string|int $offset 开始位置（从0开始）
     * @param string|int $length 截取的长度
     * @return $this
     */
    public function limit($offset = '', $length = '')
    {
        if ($offset !== '' || intval($offset) >= 0) {
            if (!empty($length)) {
                $this->limits = "LIMIT $offset ,$length";
            } else {
                $this->limits = "LIMIT $offset";
            }
        }
        return $this;
    }

    /**
     * 排序
     * @param string|array $field 字段名，数组时必须是一维关联数组
     * @param string $order 排序类型(desc|ASC)
     * @return $this
     */
    public function order($field, $order)
    {
        if (is_array($field)) {
            foreach ($field as $key => $value) {
                $this->orders .= "$key $value ,";
            }
        } else {
            $this->orders .= "$field $order ,";
        }
        //$this->orders = substr($this->orders,0,strlen($this->orders)-1 );
        return $this;
    }

    /**
     * 分组
     * @param string $field
     * @param bool $with_rollup 可以实现在分组统计数据基础上再进行相同的统计（SUM,AVG,COUNT…）
     * @return $this
     */
    public function groupBy($field = '', $with_rollup = false)
    {
        $this->groupBys = rtrim(" $field");
        ($with_rollup) and ($this->groupBys = " $field WITH ROLLUP");
        return $this;
    }

    /**
     * 表名
     * @param string $tableName 表名（tableName、db.tableName）
     * @return $this
     */
    public function table($tableName = '')
    {
        self::init();
        $this->tableName = " $tableName ";
        return $this;
    }

    public function name($name)
    {
        self::init();
        return self::table($this->config['table_prefix'] . $name);
    }

    /**
     * 查询的字段
     * @param string $field 字段（field1,field2,t.*,c.f1,c.f2..）
     * @return $this
     */
    public function field($field = '')
    {
        $this->fields = !empty($field) ? $field : "*";
        return $this;
    }

    /**
     * 更新语句(更新的数据需要使用SQL函数)
     * @param string|array $field
     * @param string $value
     * @return $this
     */
    public function exp($field = '', $value = '')
    {
        if (is_array($field)) {
            foreach ($field as $key => $v) {
                $this->exp($key, $v);
            }
        } elseif ($field !== '' && $value !== '') {
            $this->type['update'] = ltrim(trim($this->type['update']), ',');
            $this->type['update'] .= ",$field=$value";
        }
        return $this;
    }

    /**
     * 插入和修改的条件语句整理
     * @param array|string $field 推荐传入数组(一维关联数组)
     * @param null|string $value
     * @return $this
     */
    public function data($field, $value = null)
    {
        if (is_array($field)) {
            $data = self::array2string($field, null);
            $this->type['insert'] = $data['insert'];
            $this->type['update'] = $data['update'];
        } else {
            if (!empty($field) && !empty($value)) {
                $this->type['insert'] = "($field)VALUES($value)";
                $this->type['update'] = "$field='$value'";
            } elseif (!empty($field) && empty($value)) {
                $this->type['update'] = $this->type['insert'] = $field;
            } else {
                $this->type['update'] = $this->type['insert'] = "";
            }
        }
        return $this;
    }


    /**
     * 返回查询结果 （多条数据，二维数组）
     * @return array|boolean
     */
    public function select()
    {
        $sql = $this->structure_select();
        if ($this->isGetSql == true) {
            return $sql;
        }
        $re = $this->conn->query($sql, PDO::FETCH_ASSOC);
        if ($this->isError()) {
            return false;
        }
        if ($re) {
            return $re->fetchAll();
        }
        return false;
    }

    public function find()
    {
        $this->limit(1);
        $sql = $this->structure_select();
        if ($this->isGetSql == true) {
            return $sql;
        }
        $re = $this->conn->query($sql, PDO::FETCH_ASSOC);
        if ($this->isError()) {
            return false;
        }
        return $re->fetch();
    }

    public function count($field = '*')
    {
        $this->sql_str_exec();
        $this->fields = $this->fields && $this->fields != "*" ? rtrim(trim($this->fields), ",") . "," : "";
        $num = "num_" . time();
        $sql = "SELECT {$this->fields}COUNT($field) AS $num FROM {$this->tableName} {$this->joins} {$this->wheres} {$this->orders} {$this->limits}";
        if ($this->isGetSql == true) {
            return $sql;
        }
        $re = $this->conn->query($sql, PDO::FETCH_ASSOC);
        if ($this->isError()) {
            return false;
        }
        return $re->fetch()[$num];
    }

    /**
     * 获取某个字段的值
     * @param $field string 字段名（单个字段）
     * @return string 返回字段值
     */
    public function value($field = '')
    {
        $this->limit(1);
        $sql = $this->structure_select();
        if ($this->isGetSql == true) {
            return $sql;
        }
        $re = $this->conn->query($sql, PDO::FETCH_ASSOC);
        if ($this->isError()) {
            return false;
        }
        return $re->fetch()[$field];
    }


    public function insert($data = '')
    {
        if (!empty($data)) {
            $this->data($data);
        }
        $sql = self::structure_insert();
        if ($this->isGetSql == true) {
            return $sql;
        }
        $re = $this->conn->exec($sql);
        if ($this->isError()) {
            return false;
        }
        return $re; //影响的行数
    }

    public function insertGetId($data = '', $field = null)
    {
        if (!empty($data)) {
            $this->data($data);
        }
        $sql = self::structure_insert();
        if ($this->isGetSql == true) {
            return $sql;
        }
        $re = $this->conn->exec($sql);
        if ($this->isError()) {
            return false;
        }
        $insertId = $this->conn->lastInsertId($field); //获取插入的自增id 字段的值
        return $insertId;
    }

    public function update($data = '')
    {
        if (!empty($data)) {
            $this->data($data);
        }
        $sql = self::structure_update();
        if ($this->isGetSql == true) {
            return $sql;
        }
        $re = $this->conn->exec($sql);
        if ($this->isError()) {
            return false;
        }
        return $re; //影响的行数
    }

    public function delete()
    {
        $sql = self::structure_delete();
        if ($this->isGetSql == true) {
            return $sql;
        }
        $re = $this->conn->exec($sql);
        if ($this->isError()) {
            return false;
        }
        return $re; //影响的行数
    }


    public function sql_select($sql = '', $error_fn = '')
    {
        //成功返回一个对象（类似实例后的类）关联数组
        //PDO::FETCH_ASSOC——关联数组形式；
        //PDO::FETCH_NUM——数字索引数组形式；
        //PDO::FETCH_BOTH——两种数组形式都有，这是默认的；
        //PDO::FETCH_OBJ——按照对象的形式，类似于以前的 mysql_fetch_object()。
        $object = $this->conn->query($sql, PDO::FETCH_ASSOC);
        if ($this->conn->errorCode() != '00000') {
            isset($error_fn) and is_callable($error_fn) and $error_fn($object);
            return false;
        }
        $arr = [];
        $i = 0;
        if (is_object($object)) {
            foreach ($object as $key => $value) {
                //$arr[$i] = $value; $i++;
                $arr[] = $value;
            }
            if ($object->rowCount() < 1 || count($arr) < 1) {
                return false;
            } else {
                return $arr;
            }
        }
        return false;
    }

    /**
     * 返回数组（二维数组和空数组，多条数据）
     * @param $sql string sql查询语句
     * @param $error_fn callable 错误回调函数
     */
    public function getAll($sql, $error_fn)
    {
        $object = $this->conn->query($sql, PDO::FETCH_ASSOC);
        if ($this->conn->errorCode() != '00000') {
            isset($error_fn) and is_callable($error_fn) and $error_fn($object);
            return false;
        }
        if (is_object($object)) {
            return $object->fetchAll();
        } else {
            return false;
        }
    }

    /**
     * 返回一维数组（一条数据）
     * @param $sql string sql查询语句
     * @param $error_fn callable 错误回调函数
     */
    public function getRow($sql, $error_fn)
    {
        $object = $this->conn->query($sql, PDO::FETCH_ASSOC);
        if ($this->conn->errorCode() != '00000') {
            isset($error_fn) and is_callable($error_fn) and $error_fn($object);
            return false;
        }
        if (is_object($object)) {
            return $object->fetch();
        } else {
            return false;
        }
    }

    /*
     * 插入数据
     * @param $sql string|array 插入语句或者一维关联数组
     * @param $tableName string 表名（当$sql为数组时有效）
     * @param $get_insertId number (1=返回插入的id)
     * @return boolean|number
     * */
    public function sql_insert($sql, $tableName = '', $get_insertId = 1, $mode = 0)
    {
        if (is_array($sql) && !empty($tableName)) {
            $k = "";
            $v = "";
            $field = "";
            foreach ($sql as $key => $value) {
                $k .= "$key,";
                $v .= "'$value',";
                $field .= "$key='$value',";
            }
            $k = substr($k, 0, strlen($k) - 1);
            $v = substr($v, 0, strlen($v) - 1);
            $field = substr($field, 0, strlen($field) - 1);
            $sql_str = "INSERT INTO $tableName( $k )values( $v )";
            $sql_strs = "INSERT INTO $tableName SET $field ";
            $re = $this->conn->exec($mode ? $sql_str : $sql_strs);
        } else {
            $re = $this->conn->exec($sql);
        }
        if ($this->conn->errorCode() != '00000') {
            return false;
        }
        if ($re) {
            $insertId = $this->conn->lastInsertId();
            if ($get_insertId == 1) {
                //插入的自增id字段的值
                return $insertId;
            }
        }
        return $re; //影响的行数
    }

    public function sql_update($sql, $tableName = '', $wheres = '')
    {
        if (is_array($sql) && !empty($tableName)) {
            $field = "";
            foreach ($sql as $key => $value) {
                $field .= "$key='$value',";
            }
            $field = substr($field, 0, strlen($field) - 1);
            $sql_str = "UPDATE $tableName SET $field $wheres ";
            $re = $this->conn->exec($sql_str);
        } else {
            $re = $this->conn->exec($sql);
        }
        if ($this->conn->errorCode() != '00000') {
            return false;
        }
        return $re; //影响的行数
    }

    public function sql_delete($sql)
    {
        $re = $this->conn->exec($sql);
        if ($this->conn->errorCode() != '00000') {
            return false;
        }
        return $re; //影响的行数
    }

    public function exec($sql)
    {
        $re = $this->conn->exec($sql);
        if ($this->conn->errorCode() != '00000') {
            return false;
        }
        return $re; //影响的行数
    }

    public function query($sql)
    {
        $object = $this->conn->query($sql, PDO::FETCH_ASSOC);
        if ($this->conn->errorCode() != '00000') {
            isset($error_fn) and is_callable($error_fn) and $error_fn($object);
            return false;
        }
        if (is_object($object)) {
            return $object->fetchAll();
        } else {
            return false;
        }
    }


    /**
     * 获取 where 条件语句
     * @return string
     */
    public function get_where_str()
    {
        return $this->wheres;
    }

    /**
     * 构建 select 查询语句
     * @return string
     */
    protected function structure_select()
    {
        self::sql_str_exec();
        return $sql = "SELECT {$this->fields} FROM {$this->tableName} {$this->joins} {$this->wheres} {$this->groupBys} {$this->orders} {$this->limits}";
    }

    /**
     * 构建 insert 插入语句
     * @return string
     */
    protected function structure_insert()
    {
        $sql = "INSERT INTO {$this->tableName}{$this->type['insert']}";
        return $sql;
    }

    /**
     * 构建 update 更改语句
     * @return string
     */
    protected function structure_update()
    {
        self::sql_str_exec();
        //去掉首位和末尾的字符","
        $this->type['update'] = empty($this->type['update']) ? '' : trim(trim($this->type['update']), ',');
        $sql = "UPDATE {$this->tableName} SET {$this->type['update']} {$this->wheres} {$this->orders} {$this->limits}";
        return $sql;
    }

    /**
     * 构建 delete 删除语句
     * @return string
     */
    protected function structure_delete()
    {
        self::sql_str_exec();
        $sql = "DELETE FROM {$this->tableName} {$this->wheres} {$this->orders} {$this->limits}";
        return $sql;
    }

    /**
     * 处理sql语句的拼接
     * @return object
     */
    protected function sql_str_exec()
    {
        $this->orders = empty($this->orders) ? '' : 'ORDER BY ' . rtrim(rtrim($this->orders), ',');
        $this->wheres = empty($this->wheres) ? '' : 'WHERE ' . preg_replace('/(and|or)$/i', '', rtrim($this->wheres));
        $this->groupBys = empty($this->groupBys) ? '' : 'GROUP BY ' . $this->groupBys;
        return $this;
    }

    /**
     * sql语句操作数据库是否出错
     * @return boolean (true=出错，false=未出错)
     */
    public function isError()
    {
        if (strtolower($this->config['db_type']) === 'mysql') {
            return $this->conn->errorCode() != '00000';
        } elseif (strtolower($this->config['db_type']) === 'mssql') {
            return $this->conn->errorCode() != '00000';
        } else {
            return $this->conn->errorCode() != '00000';
        }
    }

    /**
     * 获取错误信息
     * @param $type integer (0=获取错误码,1=数据库错误码,2=错误描述)
     * @return string|int|array
     */
    public function getError($type = 0)
    {
        $err = $this->conn->errorInfo();
        return in_array($type, [0, 1, 2]) ? $err[$type] : $err;
    }

    /**
     * 获取sql语句
     * @param $type boolean (true=获取sql语句，默认false)
     * @return object
     */
    public function getSql($type = true)
    {
        $this->isGetSql = $type;
        return $this;
    }
}
