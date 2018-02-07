<?php
/**
 * 
 * @copyright Copyright (c) 2011 - 2025
 * @author yy 
 */

class ArticleModel extends Model
{
    public $table = 'article';
    
    public function get_all_data()
    {
        return $this->all();
    }
}