<?php 
 class Log{
	static public function write($msg, $level='ERROR', $type=3, $dest=NULL){
		if(!C('SAVE_LOG')) return;
		if(is_null($dest)){
			$dest = LOG_PATH.'/'.date('Y_m_d').'.log';
		}
		if(is_dir(LOG_PATH)) error_log("[TIME]:".date('Y-m-d H:i:s')." {$level}:{$msg}\r\n",$type,$dest);
	}
}function halt($error,$level='ERROR',$type=3,$dest=NULL)
{
    if(is_array($error))
    {
        Log::write($error['message'],$level,$type,$dest);
    }
    else 
    {
        Log::write($error,$level,$type,$dest);
    }
    $e = array();
    if(DEBUG)
    {
        if(!is_array($error))
        {
            $trace = debug_backtrace();
            $e['message'] = $error;
            $e['file'] = $trace[0]['file'];
            $e['line'] = $trace[0]['line'];
            $e['class'] = isset($trace[0]['class']) ? $trace[0]['class'] : '';
            $e['function'] = isset($trace[0]['function']) ? $trace[0]['function'] : '';
            
            ob_start();
            debug_print_backtrace();
            $e['track'] = htmlspecialchars(ob_get_clean());
        }
        else 
        {
            $e = $error; 
        }
    }
    else 
    {
        
        if($url = C('ERROR_URL'))
        {
            go($url);
        }
        else 
        {
            $e['message'] = C('ERROR_MSG');
        }
    }
    include DATA_PATH . '/Tpl/halt.html';
    die;
}

function p($arr)
{
    if(is_bool($arr))
    {
        var_dump($arr);
    }
    else if(is_null($arr))
    {
        var_dump(NULL);
    }
    else 
    {
        echo '<pre style="padding:10px;border-radius:5px;background:#f5f5f5;border:1px solid #ccc;font-size:15px;">';
        print_r($arr);
        echo '</pre>';
        
    }
    die;
    
}

function go($url,$time=0,$msg='')
{
    if(!headers_sent())
    {
        $time = 0 ? header('Location:'.$url) : header("refresh:{$time};url={$url}");
        die($msg);
    }
    else 
    {
        echo "<meta http-equiv='Refresh' content='{$time}';URL='{$url}'>";
        if($time) die($msg);
    }
}

//1.加载配置项
//C($sysConfig) C($userConfig);
//2.读取配置项
//C('CODE_LEN')
//3.临时动态改变配置项
//C('CODE_LEN',20);
//4.C();
function C($var = NULL, $value = NULL){
    static $config = array();
    //加载配置项
    if(is_array($var)){
        $config = array_merge($config,array_change_key_case($var,CASE_UPPER));
        return;
    }
    //读取或者动态改变配置项
    if(is_string($var)){
        $var = strtoupper($var);
        //两个参数传递
        if(!is_null($value)){
            $config[$var] = $value;
            return;
        }
        
        return isset($config[$var]) ? $config[$var] : NULL;
    }
    
    //返回所有配置项
    if(is_null($var) && is_null($value)){
        return $config;
    }
    
}

/**
 * 获取系统常量
 */
function print_const()
{
    $const = get_defined_constants(true);
    p($const);
}

function M($table)
{
    $obj = new Model($table);
    return $obj;
}/**
 *  Controller
 */
class Controller{
	private $var = array();
	public function __construct(){
		if(method_exists($this, '__init')){
			$this->__init();
		}
		if(method_exists($this, '__auto')){
			$this->__auto();
		}
	}
	protected function success($msg,$url=NULL,$time=3){
		$url = $url ? "window.location.href='" . $url . "'" : 'javascript:window.history.back(-1)';
		include APP_TPL_PATH.'/success.html';
		die;
	}

	protected function error($msg,$url=NULL,$time=3){
		$url = $url ? "window.location.href='" . $url . "'" : 'javascript:window.history.back(-1)';
		include APP_TPL_PATH.'/error.html';
		die;
	}
	
	protected function display($tpl=NULL){
		if(is_null($tpl)){
			$path = APP_TPL_PATH . '/' . CONTROLLER . '/' . ACTION . '.html';
		}else{
			$suffix = strrchr($tpl, '.');
			$tpl = empty($suffix) ? $tpl . '.html' : $tpl;
			$path =  APP_TPL_PATH . '/' . CONTROLLER . '/' . ACTION . $tpl;
		}
		if(!is_file($path)) halt($path.'模版文件不存在');
		extract($this->var);
		include $path;
	}
	
	protected function assign($var, $value){
		$this->var[$var] = $value;
	}
}final class Application{
	public static function run(){
		self::_init();
		set_error_handler(array(__CLASS__,'error'));
		register_shutdown_function(array(__CLASS__,'fatal_error'));
		self::_user_import();
		self::_set_url();
		spl_autoload_register(array(__CLASS__, '_autoload'));
		self::_create_demo();
		self::_app_run();
	}

	public static function fatal_error()
	{
	    if($e=error_get_last())
	    {
	        self::error($e['type'], $e['message'], $e['file'], $e['line']);
	    }
	}
	
	/**
	 * 自定义错误处理函数
	 * @param  $errno
	 * @param  $error
	 * @param  $file
	 * @param  $line
	 */
	public static function error($errno,$error,$file,$line)
	{
	    switch ($errno)
	    {
	        case E_ERROR:
	        case E_PARSE:
	        case E_CORE_ERROR:
	        case E_COMPILE_ERROR:
	        case E_USER_ERROR:
	            $msg = $error.$file."第{$line}行";
	            halt($msg);
	            break;
	            
	        case E_STRICT:
	            break;
	        case E_USER_WARNING:
	            break;
	        case E_USER_NOTICE:
	            break;
	        default:
	            /* if(DEBUG)
	            {
	                include DATA_PATH.'/Tpl/notice.html';
	            } */
	            break;
	    }
	}
	
	/**
	 * 实例化应用控制器
	 */
	private static function _app_run(){
		$c = isset($_GET[C('VAR_CONTROLLER')]) ? $_GET[C('VAR_CONTROLLER')] : 'Index';
		$a = isset($_GET[C('VAR_ACTION')]) ? $_GET[C('VAR_ACTION')] : 'index';
		define('CONTROLLER', $c);
		define('ACTION', $a);
		
		$c .= 'Controller';
		if(class_exists($c))
		{
		    $obj = new $c();
		    if(!method_exists($obj, $a))
		    {
		        if(method_exists($obj, '__empty'))
		        {
		            $obj->__empty();
		        }
		        else 
		        {
		            halt($c.'控制器中'.$a.'方法不存在');
		        }
		    }
		    else 
		    {
		        $obj->$a();
		    }
		    
		}
		else 
		{
		    $obj = new EmptyController();
		    $obj->index();
		}
		
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
	    switch (true)
	    {
	        //判断是否是控制器
	        case strlen($className) > 10 && substr($className,-10) == 'Controller':
	            $path = APP_CONTROLLER_PATH . '/' . $className . '.class.php';
	            if(!is_file($path)) 
	            {
	                $emptyPath = APP_CONTROLLER_PATH.'/EmptyController.class.php';
	                if(is_file($emptyPath))
	                {
	                    include $emptyPath;
	                    return;
	                }
	                halt($path.'控制器未找到');
	            }
	            include $path;
	            break;
	        default:
	            $path = TOOL_PATH.'/'.$className.'.class.php';
	            if(!is_file($path)) halt($path.'类未找到');
	            include $path;
	            break;
	    }
	    
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
    
		//加载公共配置项
		$commonPath = COMMON_CONFIG_PATH. '/config.php';
		$commonConfig = <<<str
<?php
return array(
	//配置项 => 配置值
	);
str;
		is_file($commonPath) || file_put_contents($commonPath, $commonConfig);
		C(include $commonPath);
		
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
	
	/**
	 * 
	 */
	private static function _user_import()
	{
	    $fileArr = C('AUTO_LOAD_FILE');
	    if(is_array($fileArr) && !empty($fileArr))
	    {
	        foreach ($fileArr as $v)
	        {
	            require_once COMMON_LIB_PATH.'/'.$v;
	        }
	    }
	}
}