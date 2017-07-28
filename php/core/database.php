<?php
/**
 * mysql操作类
 *
 */
class db{
	private $db;

	/**
	* 实例化的对象,单例模式.
	* @var \iphp\db\Redis
	*/
	static private $_instance=array();

	private function __construct($config)
	{
		$this->db = new PDO("mysql:host=".$config['host'].";dbname=".$config['db_name'],$config['user'],$config['password']);

		$this->db->query("SET NAMES utf8");
    }

	 /**
	 * 得到实例化的对象.
	 * 为每个数据库建立一个连接
	 * 如果连接超时，将会重新建立一个连接
	 * @param array $config
	 * @param int $dbId
	 * @return \iphp\db\Redis
	 */
	public static function getInstance($config)
	{

		if(! (static::$_instance instanceof self))
		{
		   
			static::$_instance = new self($config);
		}
		return static::$_instance;
	}

	private function __clone(){}

	/**
     * 执行sql
     * @param string $sql sql语句
     * @return bool 
     */
    public function exec($sql)
    {
        return $this->db->exec($sql);
    }

	/**
     * 查询sql
     * @param string $sql sql语句
     * @return bool 
     */
    public function query($sql)
    {
        return $this->db->query($sql);
    }

	
}