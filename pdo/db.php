<?php
include "../functions.php";
include "./config.php";
/**默认支持mysql
 * Class Db利用pdo封装一个数据库处理类
 */
class Db{
    protected $_host;
    protected $_pdo;
    protected $_user;
    protected $_pwd;
    protected $_db_name;
    protected $_type;
    protected $_tb_name;
    protected $_opt;
    protected $_where=[];
    protected $_join='';
    protected $_field='*';
    protected $_limit='';
    protected $_order='';
    protected $_offset='';
    protected $_group='';
    static protected $_ins = null;

    final protected function __construct ($user = USER, $passwd = PASSWD, $db_name = DB_NAME, $host = HOST, $type=TYPE)
    {
        $this->_user = $user;
        $this->_passwd = $passwd;
        $this->_db_name = $db_name;
        $this->_user = $user;
        $this->_type = $type;
        $dsn = "$type:host=$host;dbname=$db_name";
        $pdo = new PDO($dsn,$user,$passwd);
        $this->_pdo = $pdo;
    }

    final protected function __clone (){}

    public static function getIns(){
        static::$_ins===null && (static::$_ins=new static());
        return static::$_ins;
    }

    public function query($sql){
        return $this->_pdo->query($sql);
    }

    public function table($tb_name){
        $this->_tb_name = $tb_name;
        $this->_field($tb_name);
        return $this;
    }

    public function join($join){
        $this->_join = $join;
        return $this;
    }

    public function order($order){
        if(!empty($order)){
            $this->_order = ' order by '.$order;
        }else{
            $this->_order = '';
        }
        return $this;
    }

    /**如果分页sql不是这样的需要自己进行重写,此适合mysql
     * @param $num
     * @return $this
     */
    public function limit($limit,$offset=0){
        if(!empty($order)){
            $this->_limit = " limit $limit, $offset ";
        }else{
            $this->_limit = '';
        }
        return $this;
    }

    public function group($group){
        if(!empty($group)){
            $this->_group = ' group by '.$group;
        }else{
            $this->_group = '';
        }
        return $this;
    }

    /**
     * 获取插入数据的id
     */
    protected function _id(){
        return $this->_pdo->lastInsertId();
    }

    /**
     * 获取数据表的字段
     */
    protected function _field($tb_name){
        $sql = " desc $tb_name ";
        $data = $this->_pdo->query($sql);
        $key = '';
        $fields = [];
        foreach ($data as $v){
            $v['Key'] === 'PRI' && $key=$v['Key'];
            $fields[] = $v['Field'];
        }
        $this->_opt['key'] = $key;
        $this->_opt['fields'] = $fields;
    }

    /**
     * @param $field限定表的查询字段
     */
    public function field($field){
        if(is_array($field)){
            $this->_field = implode(', ', $field) ;
        }else if(is_string($field)){
            $this->_field = $field;
        }
        empty(trim($this->_field)) && $this->_field = '*' ;
        return $this;
    }

    /**根据条件查询返回最后的数据
     * @param $conditions
     * @return bool
     */
    public function find($conditions){
        $bind = $this->_create_where($conditions);
        $sql = " select ".$this->_field." from ".$this->_tb_name.$this->_where.$this->_join.$this->_group.$this->_order.$this->_limit.$this->_offset;
        $shd = $this->_pdo->prepare($sql);
        $this->_clear();
        $shd->execute($bind);
        $temp = [] ;
        foreach ($shd->fetch() as $k=>$row) {
            $temp[$k] = $row ;
        }
        return $temp;
        //返回数组数据
    }

    /**根据条件删除数据
     * @param $conditions
     */
    public function delete($conditions){
        $bind = $this->_create_where($conditions);
        $sql = " delete from ".$this->_tb_name.$this->_where;
        $shd = $this->_pdo->prepare($sql);
        $this->_clear();
        return $shd->execute($bind);
    }

    /**插入数据函数
     * @param $data
     * @return bool|string
     */
    public function insert($data){
        $keys = array_keys($data);
        $column = implode(',',$keys);
        $prepares = implode(', :',$keys);
        $prepares = ":".$prepares;
        $temp = [];
        foreach($data as $k=>$v){
            $temp[":$k"] = $v;
        }
        $sql = " insert into ".$this->_tb_name." ( $column ) values ( $prepares ) ";
        $shd = $this->_pdo->prepare($sql);
        if($shd->execute($temp)){
            return $this->_id();
        }
        $this->_clear();
        return false;
    }

    /**
     * @param $data 要更新成的数据
     * @param null $conditions 更新的条件
     * @return bool =,like,<,>,between,and,or,in,!=
     */
    public function update($data,$conditions=null){
        $rows = $temp = [];
        $bind = $this->_create_where($conditions);
        foreach($data as $k=>$v){
            $rows[] = ($k." = :u$k");
            $temp[":u$k"] = $v;
        }
        $sql = "update ".$this->_tb_name." set ".implode(', ',$rows).$this->_where;
        $shd = $this->_pdo->prepare($sql);
        $this->_clear();
        return $shd->execute($temp);
    }

    public function orwhere($where){

    }

    /**< > != <> like in between
     * @param $conditions   给定的条件
     * @return array    返回相应的绑定数据数组
     */
    public function _create_where($conditions){
        //相对应得绑定数据
        $bind = [];
        if(empty($conditions)){
            $this->_where = '';
        }else if(is_string($conditions)){
            $this->_where = " where ".$conditions;
        }else if(is_array($conditions)){
            $where = [];
            foreach ($conditions as $k=>$v){
                if(is_array($v)){
                    switch ($v[0]){
                        case '<':
                        case '>':
                        case '<>':
                        case '!=':
                            $where[] = " $k $v[0] :$k " ;
                            $bind[":$k"] = $v[1] ;
                            break;
                        case 'like':
                            $where[] = " $k like :$k ";
                            $bind[":$k"] = "%$v[1]%" ;
                            break;
                        case 'in':
                            $where[] = " $k in ( :$k ) ";
                            $bind[":$k"] = " '".implode("' , '",$v[1])."' " ;
                            break;
                        case 'between':
                            $where[] = " $k between $v[1] and $v[2]";
                            break;

                    }
                }else{
                    $where[] = " $k = :$k ";
                    $bind[":$k"] = $v ;
                }
            }
            $where = implode(' and ',$where);
            if(empty(trim($where))){
                $this->_where = '';
            }else{
                $this->_where = " where ".$where;
            }
        }
        return $bind;
    }

    /**
     * 清空绑定数据
     */
    public function _clear(){
        $this->_where=[];
        $this->_join='';
        $this->_field='*';
        $this->_limit='';
        $this->_order='';
        $this->_offset='';
        $this->_group='';
    }

    public function _bindsql($sql,$data){
        foreach ($data as $k=>$v){
            $sql = str_replace($k,$v,$sql);
        }
        return $sql;
    }
}