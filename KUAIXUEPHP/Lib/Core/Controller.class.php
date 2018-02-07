<?php

/**
 *  Controller
 */
class Controller extends SmartyView{
	private $var = array();
	public function __construct(){
	    if(C('SMARTY_ON')) parent::__construct();
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
	
	protected function get_tpl($tpl)
	{
	    if(is_null($tpl)){
	        $path = APP_TPL_PATH . '/' . CONTROLLER . '/' . ACTION . '.html';
	    }else{
	        $suffix = strrchr($tpl, '.');
	        $tpl = empty($suffix) ? $tpl . '.html' : $tpl;
	        $path =  APP_TPL_PATH . '/' . CONTROLLER . '/' . ACTION . $tpl;
	    }
	    return $path;
	}
	
	protected function display($tpl=NULL){
	    $path = $this->get_tpl($tpl);
	    
		if(!is_file($path)) halt($path.'模版文件不存在');
		if(C('SMARTY_ON'))
		{
		    parent::display($path);
		}
		else 
		{
		    extract($this->var);
		    include $path;
		}
		 
		
	}
	
	protected function assign($var, $value){
	    if(C('SMARTY_ON'))
	    {
	        parent::assign($var,$value);
	    }
		$this->var[$var] = $value;
	}
}