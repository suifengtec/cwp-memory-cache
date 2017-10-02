<?php

if (!class_exists('CWP_MC_Utils', false)) :

final class CWP_MC_Utils
{
	private static $engines = array();
	private static $path = false;

	/*
	在 object-cache.php 中注册引擎, 原来注册了许多，实际的应用中，应仅注册使用的 memcache 或 memcached 引擎, 实例:

	EMOCCacheFactory::registerEngine('memcache',       'EMOCMemcache',          'Memcache',          'memcache_connect',      2, 'Memcache');
	EMOCCacheFactory::registerEngine('memcached',      'EMOCMemcached',         'Memcached',         'memcached',             2, 'Memcached');
	 */
	public static function registerEngine($id, $classname, $file, $checker, $has_options, $pretty, $force = false)
	{
		/*
		不被继承，所以这个永远不存在,也就没必要判断了。
		 */
		/*if (!self::$path) {}*/
		self::$path = dirname(__FILE__);
		
		/*
		$checker
		'memcache_connect'
		或
		'memcached'
		 */
		if (function_exists($checker) || class_exists($checker, true)) {
			if (file_exists(self::$path . DIRECTORY_SEPARATOR . $file . '.php')) {
				/*
				调用时,未强制载入,所以这里永远不运行
				 */
				if ($force) {
					include_once(self::$path . DIRECTORY_SEPARATOR . $file . '.php');
				}

				self::$engines[$id] = array($classname, $file, $has_options, $pretty);
				return true;
			}
		}

		return false;
	}

	/*
	在 object-cache.php 中的自定义函数 wp_cache_init() 中调用

	global $__emoc_options;

	$GLOBALS['wp_object_cache'] = EMOCCacheFactory::get($__emoc_options);

	 */
	public static function get( array $options)
	{
		if (empty($options['engine'])) {
			return null;
		}

		$enabled = (isset($options['enabled'])) ? $options['enabled'] : true;
		$persist = (isset($options['persist'])) ? $options['persist'] : false;
		$maxttl  = (isset($options['maxttl']))  ? $options['maxttl']  : 3600;

		$engine = strtolower($options['engine']);

		if (!isset(self::$engines[$engine])) {
			$item = reset(self::$engines);
			$name = key(self::$engines);
			trigger_error('Caching engine "' . $engine . '" is not available, falling back to ' . $name . '.', E_USER_WARNING);
		}
		else {
			$item = self::$engines[$engine];
		}

		include(self::$path . DIRECTORY_SEPARATOR . $item[1] . '.php');
		$params = isset($options['options'][$engine]) ? $options['options'][$engine] : array();
		$params['engine'] = $engine;

		return call_user_func(array($item[0], 'instance'), $params, $enabled, $persist, $maxttl);
	}

	/*
	在主文件 admin_menu() 方法中调用，用于生成二级菜单以及相应的配置项。
	 */
	public static function getEngines()
	{
		return self::$engines;
	}
}

endif;
