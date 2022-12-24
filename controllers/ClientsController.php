<?php

class ClientsController extends Controller
{
    public function fetch()
    {
        $items_per_page = 20;

    	$filter = array();

        if (!($sort = $this->request->get('sort', 'string')))
        {
            $sort = 'id_desc';
        }
        $filter['sort'] = $sort;
        $this->design->assign('sort', $sort);
        
        if ($search = $this->request->get('search'))
        {
            $filter['search'] = array_filter($search);
            $this->design->assign('search', array_filter($search));
        }
        
		$current_page = $this->request->get('page', 'integer');
		$current_page = max(1, $current_page);
		$this->design->assign('current_page_num', $current_page);

		$clients_count = $this->users->count_users($filter);
		
		$pages_num = ceil($clients_count/$items_per_page);
		$this->design->assign('total_pages_num', $pages_num);
		$this->design->assign('total_orders_count', $clients_count);

		$filter['page'] = $current_page;
		$filter['limit'] = $items_per_page;
    	
        
        $clients = $this->users->get_users($filter);
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($clients);echo '</pre><hr />';
        
        $this->design->assign('clients', $clients);
        
        return $this->design->fetch('clients.tpl');
    }
    
}