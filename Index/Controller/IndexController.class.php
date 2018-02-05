<?php
class IndexController extends CommonController{
	public function __auto(){

	}
	public function index(){
	    $arr = array('a','b');
	    $this->assign('var', 'php1');
		$this->display();
	}
}
?>