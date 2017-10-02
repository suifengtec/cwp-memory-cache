<?php

class CWP_MC_Memcached extends CWP_MC_Base{
	private $prefix;
	private $memcached;

	public static function instance(array $data, $enabled = true, $persist = true, $maxttl = 3600)
	{
		static $self = false;

		if (!$self) {
			$self = new self($data, $enabled, $persist, $maxttl);
		}

		return $self;
	}

	protected function __construct(array $data, $enabled = true, $persist = true, $maxttl = 3600)
	{
		$this->prefix = (empty($data['prefix'])) ? md5($_SERVER['HTTP_HOST']) : $data['prefix'];

		$connect_type = !empty($data['connect_type'])?$data['connect_type']:'l';

		$result = false;
		/*长连接*/
		if($connect_type=='l'){
			$this->memcached = new Memcached( $this->prefix );

			if (!empty($data['server']) && count($this->memcached->getServerList()) == 0) {

					/*
					压缩?
					 */
					//$this->memcached->setOption(Memcached::OPT_COMPRESSION, false);
					/*
					二进制协议
					 */
					$this->memcached->setOption(Memcached::OPT_BINARY_PROTOCOL, true);
					/*
					https://help.aliyun.com/document_detail/26554.html
					重要，php memcached有个bug，当get的值不存在，有固定40ms延迟，开启这个参数，可以避免这个bug
					 */
					$this->memcached->setOption(Memcached::OPT_TCP_NODELAY, true);

					$result |= $this->memcached->addServer( $data['server'], 11211, 1);

			}else{

					/*啥都不干,这是长连接*/


			}

		}else{/*短连接*/

			$this->memcached = new Memcached();
			$result = false;
			if (!empty($data['server'])) {

				$result |= $this->memcached->addServer($data['server'], 11211, 1);
			}
		}
		

		if (!$result) {
			$persist = false;
		}

		parent::__construct($data, $enabled, $persist, $maxttl);
	}

	protected function do_delete($key, $group)
	{

		$this->maybe_flush_all_app_by_key($key, $group);

		return $this->memcached->delete($this->getKey($group, $key));
	}

	protected function do_flush()
	{
		$this->memcached->flush();
	}

	protected function do_get($group, $key, &$found, $ttl)
	{
		/*
		aboutcg 使用
		 */
		do_action('acg_ocs_flush_cache_action', $group, $key);

		/*
		通用
		 */
		do_action('cwp_mem_cache_flush_action', $group, $key);

		$result = $this->memcached->get($this->getKey($group, $key));
		$found  = (false !== $result);
		return $result;
	}

	protected function do_set($key, $data, $group, $ttl)
	{
		return $this->memcached->set($this->getKey($group, $key), $data, $ttl);
	}

	protected function getKey($group, $key)
	{
		return $this->prefix . '/' . $group . '/' . $key;
	}


/*Yahoooooo*/

	public function maybe_flush_all_app_by_key( $id, $group = '', $host ='localhost', $port = 11211, $weight = 1){


		global $cwp_mem_cache_apps;
		if(!is_array($cwp_mem_cache_apps)||empty($cwp_mem_cache_apps)){
			return ;
		}


		if (empty($group)) { 
			return ;
		}

		$memcache = $this->memcached;

		if(!$memcache){
			return ;
		}

		$groups = array(
			'user_meta',
			'usermeta',
			'bp',
			'users',
			'userlogins',
			'useremail',
			'userslugs'
			);

		if(!in_array($group, $groups)){
			return ;
		}
		if('bp'== $group&&false===strpos($id, 'bp_core_userdata_' )){
			return ;
		}



		foreach($cwp_mem_cache_apps as $app){

			$key = self::get_key($app, $group, $id);

			$memcache->delete($key);
		}


	}

	/*
	helper
	 */
	public static  function get_key($prefix, $group, $key){
		return $prefix . '/' . $group . '/' . $key;
	}

	/*
	实现 wp_object_cache 接口的状态方法
	 */
	public function stats(){


		$memcache = $this->memcached;

		/*从父类基础*/
		$stats = $memcache->getStats();

		$this->print_detail( $stats ); 

		/*
		进一步显示存储状况
		 */
		$this->getMemcacheKeys( $memcache );


	}

	/*
	hepler: 用于实现 wp_object_cache 接口的状态方法。
	 */
	public function getMemcacheKeys( $memcached ){

		/*
		$memcached->getDelayed($keys);*/

		/*$keys = $memcached->getAllKeys();*/
		$all = $memcached->fetchAll();

		if( $all ){
			echo '<h3>缓存项目的key:</h3>';
			foreach( $all as $k=>$v){

				echo $k .'=>'.json_encode($v).'<br>'; 
			}
		}
	}

}
