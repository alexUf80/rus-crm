<?php

class ShortLinkController extends Controller
{
	function fetch()
	{
		$url = $this->request->get('page_url', 'string');

		$link = $this->shortlink->get_link($url);

        if (! empty($link))
        {
    		header('Location: ' . $link->link);
        	exit;
        }
        
        $this->design->assign('meta_title', 'Страница не найдена');
        return $this->design->fetch('404.tpl');
	}
}