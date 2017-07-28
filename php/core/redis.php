<?php
/**
 * redis操作类
 *
 */
class my_redis{
	private $redis;

	/**
	* 实例化的对象,单例模式.
	* @var \iphp\db\Redis
	*/
	static private $_instance=array();

	private function __construct($config)
	{
		$this->redis    =    new Redis();
		$this->redis->connect($config['host'], $config['port']);
		 
		if($config['auth'])
		{
			$this->auth($config['auth']);
		}
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
     * 在队列中添加一个值
     * @param string $key 队列名称
     * @param string $value 添加队列的值。
     * @return bool 
     */
    public function rpush($key,$value)
    {
        return $this->redis->rpush($key,$value);
    }

	/**
     * 在队列中获取第一个值并阻塞队列
     * @param string $key 队列名称
     * @param string $timeout 超时时间
     * @return bool 
     */
    public function blpop($key,$timeout)
    {
        return $this->redis->blpop($key,$timeout);
    }

	/**
     * 设置字符串
     * @param string $key key
     * @param string $value 值
     * @return bool 
     */
    public function set($key,$value)
    {
        return $this->redis->set($key,$value);
    }

	/**
     * 设置带过期时间的字符串
     * @param string $key key
     * @param string $value 值
     * @return bool 
     */
    public function setex($key,$time_out,$value)
    {
        return $this->redis->setex($key,$time_out,$value);
    }

	/**
     * 获取字符串
     * @param string $key key
     * @return bool 
     */
    public function get($key)
    {
        return $this->redis->get($key);
    }

	/**
     * 删除字符串
     * @param string $key key
     * @return bool 
     */
    public function del($key)
    {
        return $this->redis->del($key);
    }
}