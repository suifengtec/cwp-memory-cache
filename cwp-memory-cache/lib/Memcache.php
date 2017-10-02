<?php
/*
其超类 BaseCache.php
*/
class CWP_MC_Memcache extends CWP_MC_Base
{
	private $prefix;
	private $memcache;

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

		/*


			$this->memcache = new Memcache();
			$result = false;
			if (!empty($data['server'])) {
				foreach ($data['server'] as $x) {
					$result |= $this->memcache->addServer($x['h'], $x['p'], true, $x['w']);
				}
			}

		 */
		$this->memcache = new Memcache( );
		$result = false;
		if (!empty($data['server'])) {
			/*
			http://php.net/manual/zh/memcache.addserver.php

			当使用这个方法的时候(与Memcache::connect()和Memcache::pconnect()相反) 网络连接并不会立刻建立，而是直到真正使用的时候才建立。 因此在加入大量服务器到连接池中时也是没有开销的，因为它们可能并不会被使用。
			
			bool Memcache::addServer ( string $host [, int $port = 11211 [, bool $persistent [, int $weight [, int $timeout [, int $retry_interval [, bool $status [, callback $failure_callback [, int $timeoutms ]]]]]]]] )


			第三个参数用来确定是否为持久连接。
			$timeout: 默认为1s;
			retry_interval: 服务器连接失败时重试的间隔时间，默认值15秒。


			memcached 状态 本地仪表盘

			http://127.0.0.1/phptest/memcache-admin/


			 */
			$result |= $this->memcache->addServer($data['server'], 11211, true, 1);
			
		}


		if (!$result) {
			$persist = false;
		}

		parent::__construct($data, $enabled, $persist, $maxttl);
	}

	protected function do_delete($key, $group)
	{
		/*suifengtec*/
		$this->maybe_flush_all_app_by_key($key, $group);

		return $this->memcache->delete($this->getKey($group, $key));
	}

	protected function do_flush()
	{
		$this->memcache->flush();
	}

	protected function do_get($group, $key, &$found, $ttl)
	{
		$result = $this->memcache->get($this->getKey($group, $key));
		$found  = (false !== $result);
		return $result;
	}

	protected function do_set($key, $data, $group, $ttl)
	{

		/*suifengtec*/
		//parent::maybe_flush_all_app_by_key($key, $group);

		return $this->memcache->set($this->getKey($group, $key), $data, 0, $ttl);
	}

	/*
	获取key的方式
	app2/userlogins/admin
	 */
	protected function getKey($group, $key)
	{
		return $this->prefix . '/' . $group . '/' . $key;
	}


/*Yahoooooo*/

	/*
		EMOCBaseCache:: maybe_flush_all_app_by_key($id, $group = '')
		wp_cache_delete( $user_id,  'user_meta');
		wp_cache_delete( 'bp_core_userdata_' . $user_id, 'bp' );

		clean_user_cache();

		wp_cache_delete( $user->user_nicename, 'userslugs' );



	 */
	public function maybe_flush_all_app_by_key( $id, $group = '', $host ='localhost', $port = 11211, $weight = 1){


		global $cwp_mem_cache_apps;
		if(!is_array($cwp_mem_cache_apps)||empty($cwp_mem_cache_apps)){
			return ;
		}

		if (empty($group)) { 
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
			return false;
		}



		$r = array();




		
		$memcache = $this->memcache;

		if(!$memcache){
			return false;
		}


		foreach($cwp_mem_cache_apps as $app){

			$key = self::get_key($app, $group, $id);

			$memcache->delete($key);
		}


	}

	public static  function get_key($prefix, $group, $key){
		return $prefix . '/' . $group . '/' . $key;
	}



	public function stats(){

			
			if(class_exists('Memcache')){
				$memcache = $this->memcache;
			}elseif(class_exists('Memcached')){

				$memcache = $this->memcached;

			}
			
			$stats = $memcache->getStats();

			$this->print_detail( $stats ); 


			/*$aa  = $this->getMemcacheKeys( $memcache );*/

	}


	/*
	适用于 memcache
	 */
	public function getMemcacheKeys( $memcache ) { 

		if(!is_object($memcache)){
			return false;
		}


	    $list = array(); 
	    $allSlabs = $memcache->getExtendedStats('slabs'); 
	    $items = $memcache->getExtendedStats('items'); 




	    foreach($allSlabs as $server => $slabs) { 
        	if(!is_array($slabs)){
            		continue;
            	}
	        foreach($slabs as $slabId => $slabMeta) { 
	        	/*   echo $slabId .'=>'.json_encode($slabMeta).'<br>'; */
	           $cdump = $memcache->getExtendedStats('cachedump',(int)$slabId); 


        	if(!is_array($cdump)){
            		continue;
            	}
	            foreach($cdump as $keys => $arrVal) { 
	            	if(!is_array($arrVal)){
	            		continue;
	            	}
	                foreach($arrVal as $k => $v) {  
	                /*	if(false===strpos($k, 'app1'))      {}      */
	                /*.'=>'.json_encode($v).*/
	                		 echo $k .'<br>'; 
	                		/* echo $items[$server]['items'][$slabId]['age'] .'<br>'; */
	                	/*	var_dump($items[$server]['items'][$slabId][$keys][$arrVal][$k]); 
	                		 echo'<br>'; 
	                		  echo'<br>'; */
	                		/* echo $items[$server]['items'][$slabId][$keys][$arrVal][$k] .'<br>'; */
	                	      
	                   
	                } 
	           } 
	        } 
	    }    
	}


}
