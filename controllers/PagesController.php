<?php

class PagesController extends Controller
{
    public function fetch()
    {
    	
        $pages = $this->pages->get_pages();
        $this->design->assign('pages', $pages);
        
        return $this->design->fetch('pages.tpl');
    }
    
}