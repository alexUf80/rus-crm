<?php

class PenaltiesController extends Controller
{
    public function fetch()
    {
        if ($this->request->method('post'))
        {
            switch($this->request->post('action')):
                
                case 'add_penalty':
                    $this->add_action();
                break;
                
                case 'strike_penalty':
                    $this->strike_action();
                break;
                
                case 'reject_penalty':
                    $this->reject_action();
                break;
                
                case 'correct_penalty':
                    $this->correct_action();
                break;
                
            endswitch;
        }
        
        $items_per_page = 20;

    	$filter = array();
        
        if (!($period = $this->request->get('period')))
            $period = 'all';

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
             
             case 'all':
                $filter['date_from'] = null;
                $filter['date_to'] = null;
             break;
             
             case 'optional':
                $daterange = $this->request->get('daterange');
                $filter_daterange = array_map('trim', explode('-', $daterange));
                $filter['date_from'] = date('Y-m-d', strtotime($filter_daterange[0]));
                $filter['date_to'] = date('Y-m-d', strtotime($filter_daterange[1]));                
             break;
             
        endswitch;
        $this->design->assign('period', $period);
        
        
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
        
        if ($this->manager->role == 'user')
        {
            $filter['manager_id'] = $this->manager->id;
        }
        
		$current_page = $this->request->get('page', 'integer');
		$current_page = max(1, $current_page);
		$this->design->assign('current_page_num', $current_page);

		$penalties_count = $this->penalties->count_penalties($filter);
		
		$pages_num = ceil($penalties_count/$items_per_page);
		$this->design->assign('total_pages_num', $pages_num);
		$this->design->assign('total_orders_count', $penalties_count);

		$filter['page'] = $current_page;
		$filter['limit'] = $items_per_page;
    	
        $order_ids = array();
        $penalties = array();
        foreach ($this->penalties->get_penalties($filter) as $penalty)
        {
            $order_ids[] = $penalty->order_id;
            $penalties[] = $penalty;
        }
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($clients);echo '</pre><hr />';
        
        $orders = array();
        if (!empty($order_ids))
        {
            foreach ($this->orders->get_orders(array('id' => $order_ids)) as $o)
                $orders[$o->order_id] = $o;
        }
        
        foreach ($penalties as $penalty)
        {
            if (isset($orders[$penalty->order_id]))
                $penalty->order = $orders[$penalty->order_id];
        }
        
        $this->design->assign('penalties', $penalties);
        
        $penalty_types = array();
        foreach ($this->penalties->get_types() as $t)
            $penalty_types[$t->id] = $t;
        $this->design->assign('penalty_types', $penalty_types);
        
        $penalty_statuses = $this->penalties->get_statuses();
        $this->design->assign('penalty_statuses', $penalty_statuses);
        
        return $this->design->fetch('penalties.tpl');
    }
    
    private function strike_action()
    {
    	$penalty_id = $this->request->post('id');
        
        $this->penalties->update_penalty($penalty_id, array(
            'status' => 4,
            'strike_date' => date('Y-m-d H:i:s')
        ));
        
        $this->json_output(array('success' => 1));
    }
    
    private function reject_action()
    {
    	$penalty_id = $this->request->post('id');
        
        $this->penalties->update_penalty($penalty_id, array(
            'status' => 3,
            'reject_date' => date('Y-m-d H:i:s')
        ));
        
        $this->json_output(array('success' => 1));
    }
    
    private function correct_action()
    {
    	$penalty_id = $this->request->post('id');
        
        $this->penalties->update_penalty($penalty_id, array(
            'status' => 2,
            'correct_date' => date('Y-m-d H:i:s')
        ));
        
        $this->json_output(array('success' => 1));
    }
    
    private function add_action()
    {
    	$control_manager_id = $this->request->post('control_manager_id', 'integer');
    	$manager_id = $this->request->post('manager_id', 'integer');
    	$type_id = $this->request->post('type_id', 'integer');
    	$order_id = $this->request->post('order_id', 'integer');
    	$block = $this->request->post('block');
        $comment = $this->request->post('comment');
        
        if (empty($type_id))
        {
            $this->json_output(array('error' => 'Выберите причину штрафа'));
        }
        else
        {
            $type = $this->penalties->get_type($type_id);
            
            $params = array(
                'order_id' => $order_id,
                'manager_id' => $manager_id,
                'type_id' => $type_id,
                'comment' => $comment,
                'created' => date('Y-m-d H:i:s'),
                'control_manager_id' => $control_manager_id,
                'block' => $block,
                'cost' => $type->cost,
            );
            
            $this->penalties->add_penalty($params);
            
            $this->orders->update_order($order_id, array(
                'penalty_date' => date('Y-m-d H:i:s')
            ));
            
            $this->json_output(array('success' => 1));
        }
    }
    
}