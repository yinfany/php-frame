<?php

final class Application{
	public static function run(){
		self::_init();
		self::_set_url();
		spl_autoload_register(array(__CLASS__, '_autoload'));
		self::_create_demo();
		self::_app_run();
	}

	/**
	 * 
	 */
	private static function _app_run(){
		$c = isset($_GET[C('VAR_CONTROLLER')]) ? $_GET[C('VAR_CONTROLLER')] : 'Index';
		$a = isset($_GET[C('VAR_ACTION')]) ? $_GET[C('VAR_ACTION')] : 'index';
		define('CONTROLLER', $c);
		define('ACTION', $a);
		
		$c .= 'Controller';
		
		
		$obj = new $c();
		$obj->$a();
	}

	/**
	 *  创建默认控制器
	 */
	private static function _create_demo(){
		$path = APP_CONTROLLER_PATH.'/IndexController.class.php';

		$str = <<<str
<?php
class IndexController extends Controller{
	public function index(){
		echo 'ok';
	}
}
?>
str;
		is_file($path) || file_put_contents($path, $str);
	}
	/**
	*  自动载入功能
	*/
	private static function _autoload($className){
		include APP_CONTROLLER_PATH . '/' . $className . '.class.php';
	}
	/**
	*  设置外部路径
	*/
	private static function _set_url(){
		//p($_SERVER);
		$path = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'];
		$path = str_replace('\\', '/', $path);
		define('__APP__', $path);
		define('__ROOT__', dirname(__APP__));

		define('__TPL__', __ROOT__.'/'. APP_NAME.'/Tpl');
		define('__PUBLIC__', __TPL__.'/Public');
	}
	/**
	* 初始化框架
	*/
	private static function _init(){
		//加载配置项
		C(include CONFIG_PATH . '/config.php');

		//用户配置项
		$userPath = APP_CONFIG_PATH.'/config.php';

		$userConfig = <<<str
<?php
return array(
	//配置项 => 配置值
	);
str;
		is_file($userPath) || file_put_contents($userPath, $userConfig);
		C(include $userPath);

		//设置默认时区
		date_default_timezone_set(C("DEFAULT_TIME_ZONE"));
		//是否开启session
		C('SESSION_AUTO_START') && session_start();
	}
}