<?php

class ChangelogsController extends Controller
{
    public function fetch()
    {
        $items_per_page = 40;

    	$filter = array();

        if (!($period = $this->request->get('period')))
        {
            $period = 'year';
        }
        switch ($period):
             case 'today':
                $filter['date_from'] = date('Y-m-d');
             break;
             
             case 'yesterday':
                $filter['date_from'] = date('Y-m-d', time() - 86400);
                $filter['date_to'] = date('Y-m-d', time() - 86400);
             break;
             
             case 'month':
                $filter['date_from'] = date('Y-m-01');                
             break;
             
             case 'year':
                $filter['date_from'] = date('Y-01-01');
             break;
             
        endswitch;
        $this->design->assign('period', $period);

        if (!($sort = $this->request->get('sort', 'string')))
        {
            $sort = 'date_desc';
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

		$changelogs_count = $this->changelogs->count_changelogs($filter);
		
		$pages_num = ceil($changelogs_count/$items_per_page);
		$this->design->assign('total_pages_num', $pages_num);
		$this->design->assign('total_orders_count', $changelogs_count);

		$filter['page'] = $current_page;
		$filter['limit'] = $items_per_page;



        $users_ids = array();
        $changelogs = array();
        foreach ($this->changelogs->get_changelogs($filter) as $changelog)
        {
            if (!empty($changelog->user_id))
                $users_ids[] = $changelog->user_id;
            $changelogs[] = $changelog;
        }
        
        $users = array();
        if (!empty($users_ids))
        {
            foreach ($this->users->get_users(array('id'=>$users_ids)) as $u)
                $users[$u->id] = $u;
        }
        
        $managers = array();
        foreach ($this->managers->get_managers() as $m)
            $managers[$m->id] = $m;
        
        foreach ($changelogs as $changelog)
        {
            if (!empty($changelog->user_id) && !empty($users[$changelog->user_id]))
                $changelog->user = $users[$changelog->user_id];
            if (!empty($changelog->manager_id) && !empty($managers[$changelog->manager_id]))
                $changelog->manager = $managers[$changelog->manager_id];
        }
        
        $this->design->assign('changelogs', $changelogs);

        $types = $this->changelogs->get_types();
        $this->design->assign('types', $types);
        
        $managers = $this->managers->get_managers();
        $this->design->assign('managers', $managers);
        
        return $this->design->fetch('changelogs.tpl');
    }
    
}