<?php
class IndexController extends CommonController{
	public function __auto(){

	}
	
	/* public function __empty()
	{
	    echo 'empty method';
	} */
	
	public function index(){
	    
	    $result = M('article')->field('title')->where('cid=1')->find();
	    p($result);
		$this->display();
	}
}
?>