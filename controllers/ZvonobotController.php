<?php

class ZvonobotController extends Controller
{
    public function fetch()
    {
        $items_per_page = 20;

    	$filter = array();

        if (!($sort = $this->request->get('sort', 'string')))
        {
            $sort = 'date_desc';
        }
        $filter['sort'] = $sort;
        $this->design->assign('sort', $sort);

		$current_page = $this->request->get('page', 'integer');
		$current_page = max(1, $current_page);
		$this->design->assign('current_page_num', $current_page);

		$clients_count = $this->zvonobot->count_zvonobots($filter);
		
		$pages_num = ceil($clients_count/$items_per_page);
		$this->design->assign('total_pages_num', $pages_num);
		$this->design->assign('total_orders_count', $clients_count);

		$filter['page'] = $current_page;
		$filter['limit'] = $items_per_page;

    	$zvonobot_items = $this->zvonobot->get_zvonobots($filter);
        
        foreach ($zvonobot_items as $zvonobot_item)
        {
            $zvonobot_item->contract = $this->contracts->get_contract($zvonobot_item->contract_id);
            $zvonobot_item->user = $this->users->get_user($zvonobot_item->user_id);
        }
        $this->design->assign('zvonobot_items', $zvonobot_items);

        return $this->design->fetch('zvonobot.tpl');
    }
    
}