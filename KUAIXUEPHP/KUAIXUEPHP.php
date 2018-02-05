<?php
final class KUAIXUEPHP{
	public static function run(){
		self::_set_const();
		defined('DEBUG') || define('DEBUG', false);
		if(DEBUG)
		{
		    self::_create_dir();
		    self::_import_file();
		}
		else 
		{
		    error_reporting(0);
		    require TEMP_PATH.'/~boot.php';
		}
		Application::run();
	}

	/**
	* _set_const 设置框架所需常量
	*/
	private static function _set_const(){
		$path = str_replace('\\', '/', __FILE__);
		define('KUAIXUEPHP_PATH',dirname($path));
		define('CONFIG_PATH',KUAIXUEPHP_PATH.'/Config');
		define('DATA_PATH',KUAIXUEPHP_PATH.'/Data');
		define('LIB_PATH',KUAIXUEPHP_PATH.'/Lib');
		define('CORE_PATH',LIB_PATH.'/Core');
		define('FUNCTION_PATH',LIB_PATH.'/Function');
		define('ROOT_PATH',dirname(KUAIXUEPHP_PATH));
		//临时目录
		define('TEMP_PATH', ROOT_PATH.'/Temp');
		//日志目录
		define('LOG_PATH', TEMP_PATH.'/Log');
		//应用目录
		define('APP_PATH', ROOT_PATH.'/'.APP_NAME);
		define('APP_CONFIG_PATH',APP_PATH.'/Config');
		define('APP_CONTROLLER_PATH',APP_PATH.'/Controller');
		define('APP_TPL_PATH',APP_PATH.'/Tpl');
		define('APP_PUBLIC_PATH', APP_TPL_PATH.'/Public');
		
		define('KUAIXUEAPP_VERSION','1.0');
	}

	/**
	* _create_dir 创建应用目录
	* @return |type| [description]
	*/
	private static function _create_dir(){
		$arr = array(
			APP_PATH,
			APP_CONFIG_PATH,
			APP_CONTROLLER_PATH,
			APP_TPL_PATH,
			APP_PUBLIC_PATH,
			TEMP_PATH,
			LOG_PATH
			);
		foreach ($arr as $k=> $v) {
			is_dir($v) || mkdir($v, 0777, true);
		}

		is_file(APP_TPL_PATH.'/success.html') || copy(DATA_PATH.'/Tpl/success.html', APP_TPL_PATH.'/success.html');
		is_file(APP_TPL_PATH.'/error.html') || copy(DATA_PATH.'/Tpl/error.html', APP_TPL_PATH.'/error.html');
	}

	/**
	* _import_file 载入框架所需文件
	*/
	private static function _import_file(){
		$fileArr = array(
			CORE_PATH.'/Log.class.php',
			FUNCTION_PATH.'/function.php',
			CORE_PATH.'/Controller.class.php',
			CORE_PATH.'/Application.class.php',
			);
		$str = '';
		foreach ($fileArr as $v) {
		    $str .= trim(substr(file_get_contents($v), 5));
		    require_once $v;
		}
		
		$str = "<?php \r\n ".$str;
		file_put_contents(TEMP_PATH . '/~boot.php', $str) || die('access not allow');
	}
}
KUAIXUEPHP::run();