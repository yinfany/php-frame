<?php
class IndexController extends CommonController{
	public function __auto(){

	}
	
	/* public function __empty()
	{
	    echo 'empty method';
	} */
	
	public function index()
	{
	    if(!$this->is_cached())
	    {
	        $this->assign('test', time());
	    }
	    //$this->assign('test', time());
	    $this->display();
	}
}
?>