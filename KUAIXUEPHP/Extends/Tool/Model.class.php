<?php
/**
 * 模型类
 * @copyright Copyright (c) 2011 - 2025
 * @author yy 
 */

class Model
{
    //保存连接信息
    public static $link = NULL;
    //保存表名
    protected $table = NULL;
    //初始化表信息
    private $opt;
    //记录发送的sql
    public static $sqls = array();
    
    public function __construct($table=NULL)
    {
        $this->table = is_null($table) ? C('DB_PREFIX').$this->table : C('DB_PREFIX').$table;
        
        //连接数据库
        $this->_connect();
        
        //初始化sql信息
        $this->_opt();
    }
    
    /**
     * 查询
     * @param  $sql
     * @return array[]
     */
    public function query($sql)
    {
        self::$sqls[] = $sql;
        $link = self::$link;
        $result = $link->query($sql);
        if($link->error) halt('mysql错误'.$link->error.'<br/>SQL:'.$sql);
        $rows = array();
        while ($row = $result->fetch_assoc())
        {
            $rows[] = $row;
        }
        
        $result->free();
        $this->_opt();
        
        return $rows;
    }
    
    public function having($having)
    {
        $this->opt['having'] = ' HAVING '.$having;
        return $this;
    }
    
    public function group($group)
    {
        $this->opt['group'] = ' GROUP BY '.$group;
        return $this;
    }
    
    public function limit($limit)
    {
        $this->opt['limit'] = ' LIMIT '.$limit;
        return $this;
    }
    
    public function order($order)
    {
        $this->opt['order'] = ' ORDER BY '.$order;
        return $this;
    }
    
    public function where($where)
    {
        $this->opt['where'] = ' WHERE '.$where;
        return $this;
    }
    
    public function field($field)
    {
        $this->opt['field'] = $field;
        return $this;
    }
    
    public function findAll()
    {
        return $this->all();    
    }
    
    /**
     * 查询多条记录
     * @return array
     */
    public function all()
    {
        $sql = 'SELECT '.$this->opt['field'].' FROM '.$this->table.$this->opt['where'].$this->opt['group'].$this->opt['having'].$this->opt['order'].$this->opt['limit'];
        return $this->query($sql);
    }
    
    /**
     * 只取一条记录   
     */
    public function one()
    {
        return $this->find();
    }
    
    /**
     * 查询单条记录
     */
    public function find()
    {
        $data = $this->limit(1)->all();
        $data = current($data);
        return $data;
    }
    
    
    /**
     * 删除
     */
    public function delete()
    {
        if(empty($this->opt['where'])) halt('删除语句必须有where条件');
        $sql = "DELETE FROM ".$this->table.$this->opt['where'];
        return $this->exe($sql);
    }
    
    /**
     * 执行sql
     * @param  $sql
     */
    public function exe($sql)
    {
        self::$sqls[] = $sql;
        $link = self::$link;
        $bool = $link->query($sql);
        $this->_opt();
        if(is_object($bool))
        {
            halt('请用query方法发送查询sql');
        }
        if($bool)
        {
            return $link->insert_id ? $link->insert_id : $link->affected_rows;
        }
        else 
        {
            halt('mysql错误：'.$link->error.'<br/>SQL:'.$sql);
        }
    }
    
    /**
     * 添加
     * @param array $data
     */
    public function add($data)
    {
        if(empty($data) || !is_array($data)) halt('添加数据不能为空');
        $keys = array();
        $values = array();
        foreach ($data as $key=>$value)
        {
            $keys[] = "`".$this->_safe_str($key)."`";
            $values[] = "'".$this->_safe_str($value)."'";
        }
        $sql = 'insert into '.$this->table.'('.join(',', $keys) .')  values( '.join(",", $values).")";
        return $this->exe($sql);
    }
    
    /**
     * 更新
     * @param array $data
     */
    public function update($data)
    {
        if(empty($this->opt['where'])) halt('更新语句必须有where条件');
        if(empty($data) || !is_array($data)) halt('修改数据不能为空');
        $arr = array();
        foreach ($data as $key=>$value)
        {
            $arr[] =  sprintf("`%s`='%s'",$this->_safe_str($key),$this->_safe_str($value));
        }
        $sql = 'update '.$this->table .' set '.join(",", $arr);
        if($this->opt['where'])
        {
            $sql .= $this->opt['where'];
        }
        return $this->exe($sql);
    }
    
    /**
     * 设置发送的sql
     */
    private function _opt()
    {
        $this->opt = array(
            'field' => '*',
            'where' => '',
            'group' => '',
            'having' => '',
            'order' => '',
            'limit' => '',
        );
    }
    
    /**
     * _connect 连接
     */
    private function _connect()
    {
        if(is_null(self::$link))
        {
            if(empty(C('DB_DATABASE'))) halt('请先配置数据库');
            $link = new mysqli(C('DB_HOST'),C('DB_USER'),C('DB_PASSWORD'),C("DB_DATABASE"),C('DB_PORT'));
            if($link->connect_error) halt('数据库连接错误，请检查配置项');
            
            $link->set_charset(C('DB_CHARSET'));
            self::$link = $link;
        }
        return $link;
    }
    
    /**
     * 安全处理
     * @param string $str
     */
    private function _safe_str($str) 
    {
        if(get_magic_quotes_gpc())
        {
            $str = stripslashes($str);
        }
        
        return self::$link->real_escape_string($str);
    }
}