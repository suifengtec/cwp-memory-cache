<?php
/**
 * @Author: suifengtec
 * @Date:   2017-03-29 14:14:00
 * @Last Modified by:   suifengtec
 * @Last Modified time: 2017-10-01 08:42:52
 */
/**
 * Plugin Name: A CWP Memory Cache
 * Plugin URI: http://coolwp.com/PluginSlug.html
 * Description: Description.
 * Author: suifengtec
 * Author URI: https://coolwp.com
 * Version: 0.9.1
 * Text Domain: acgocs
 * Domain Path: /languages/
 *
 */
/*
0.9.0 : 开发完成;
0.9.1 : 配置项优化,支持长连接或短连接;


 */
if ( ! defined( 'ABSPATH' ) ){
	exit;	
}

if ( ! class_exists( 'CWP_Mem_Cache' ) ) :

final class CWP_Mem_Cache {

	private static $instance;

	public function __wakeup() {}
	public function __clone() {}
	public static function instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof CWP_Mem_Cache ) ) {
			self::$instance = new self();
			self::$instance->setup_constants();
			self::$instance->hooks();
		}

		return self::$instance;

	}

	public function hooks(){

/*
手动配置吧
 */
/*        register_activation_hook( __FILE__, array( $this, 'activate') );
        register_deactivation_hook( __FILE__, array( $this, 'deactivate') );
*/
        /*
        User ACTIONS 

			add_action( 'profile_update', '_hook_update_user_data' );
			add_action(  'user_register', '_hook_update_user_data' );
			add_action( 'password_reset', '_hook_update_user_data' );
         */
	
		/*add_action('password_reset', array( $this, 'flush_user_cache'));*/
	}
	



    /*
    设置项: 激活时不再写文件,使用时, 不再读文件, 直接放在内存里。
     */
	public function activate(){

		/*self::set_schedule_events();
		flush_rewrite_rules( false );*/

		/*$this->write_opts();*/
		if (file_exists(ABSPATH . 'wp-content/object-cache.php')) {
			if (!unlink(ABSPATH . 'wp-content/object-cache.php')) {
				die('WARNING: failed to delete <code>' . ABSPATH . 'wp-content/object-cache.php</code><br/>Please delete this file ASAP.');
			}
		}

		if (!copy(WP_CONTENT_DIR . '/plugins/cwp-memory-cache/object-cache.php', WP_CONTENT_DIR . '/object-cache.php')) {
			wp_die(sprintf('There was an error copying <code>%s</code> to <code>%s</code>', WP_CONTENT_DIR . '/plugins/cwp-memory-cache/object-cache.php', WP_CONTENT_DIR . '/object-cache.php'));
		}


	}



	public function deactivate(){

		if (file_exists(ABSPATH . 'wp-content/object-cache.php')) {
			if (!unlink(ABSPATH . 'wp-content/object-cache.php')) {
				die('WARNING: failed to delete <code>' . ABSPATH . 'wp-content/object-cache.php</code><br/>Please delete this file ASAP.');
			}
		}

	}


	private function setup_constants() {

		if ( ! defined( 'ACG_OCS_PLUGIN_DIR' ) ) {
			define( 'ACG_OCS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		}

	}

}

global $acgocs;
$acgocs = CWP_Mem_Cache::instance();

endif;
