<?php
/**
 * Smarty模版视图
 * @copyright Copyright (c) 2011 - 2025
 * @author yy 
 */

class SmartyView
{
    private static $smarty = NULL;
    
    public function __construct()
    {
        if(!is_null(self::$smarty)) return;
        $smarty = new Smarty();
        //模版目录
        $smarty->template_dir = APP_TPL_PATH.'/'.CONTROLLER.'/';
        //编译目录
        $smarty->compile_dir = APP_COMPILE_PATH;
        //缓存目录
        $smarty->cache_dir = APP_CACHE_PATH;
        //边界符
        $smarty->left_delimiter = C('LEFT_DELIMITER');
        $smarty->right_delimiter = C('RIGHT_DELIMITER');
        //缓存
        $smarty->caching = C('CACHE_ON');
        $smarty->cache_lifetime = C('CACHE_LIFETIME');
        
        self::$smarty = $smarty;
    }
    
    protected function display($tpl)
    {
        self::$smarty->display($tpl,$_SERVER['REQUEST_URI']);
    }
    
    protected function assign($var,$value)
    {
        self::$smarty->assign($var,$value);
    }
    
    protected function is_cached($tpl=NULL)
    {
        if(!C('SMARTY_ON')) halt('请先开启smarty!');
        $tpl = $this->get_tpl($tpl);
        return self::$smarty->is_cached($tpl,$_SERVER['REQUEST_URI']);
    }
}