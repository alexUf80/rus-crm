<?php

class NotificationsController extends Controller
{
    public function fetch()
    {
        if ($this->request->method('post'))
        {
            switch($this->request->post('action')):
                
                case 'done':
                    $this->done_action();
                break;
                
            endswitch;
        }
        
        $items_per_page = 20;

    	$filter = array();
        
        $filter['done'] = 0;
        
        $mode = $this->request->get('mode');
        if ($mode == 'sudblock')
            $filter['sudblock_mode'] = 1;
        if ($mode == 'collection')
            $filter['collection_mode'] = 1;        
        
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

		$notifications_count = $this->notifications->count_notifications($filter);
		
		$pages_num = ceil($notifications_count/$items_per_page);
		$this->design->assign('total_pages_num', $pages_num);
		$this->design->assign('total_orders_count', $notifications_count);

		$filter['page'] = $current_page;
		$filter['limit'] = $items_per_page;
    	
        
        $notifications = array();
        foreach ($this->notifications->get_notifications($filter) as $note)
        {
            if (!empty($note->event_id))
                $note->event = $this->notifications->get_event($note->event_id);
            if (!empty($note->sudblock_contract_id))
                $note->contract = $this->sudblock->get_contract($note->sudblock_contract_id);        
            if (!empty($note->collection_contract_id))
                $note->contract = $this->contracts->get_contract($note->collection_contract_id);
            
            if (!empty($note->contract->user_id))
            {
                $note->user = $this->users->get_user($note->contract->user_id);
                if (!empty($note->user))
                    $note->user->client_time = $this->helpers->get_regional_time($note->user->Regregion);
                else
                    $note->user->client_time = date('Y-m-d H:i:s');
                $clock = date('H', strtotime($note->user->client_time));
                $weekday = date('N', strtotime($note->user->client_time));
                if ($weekday == 6 || $weekday == 7)
                    $note->user->client_time_warning = $clock < 9 || $clock > 20;
                else
                    $note->user->client_time_warning = $clock < 8 || $clock > 21;

            }
            
            $notifications[] = $note;
        }
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($clients);echo '</pre><hr />';
        
        $this->design->assign('notifications', $notifications);
        
        $collection_statuses = $this->contracts->get_collection_statuses();
        $this->design->assign('collection_statuses', $collection_statuses);
        
        if ($mode == 'collection')
            return $this->design->fetch('collector_notifications.tpl');
        else
            return $this->design->fetch('notifications.tpl');
    }
    
    private function done_action()
    {
    	$id = $this->request->post('id', 'integer');
        
        $this->notifications->update_notification($id, array('done' => 1));
        
        $this->json_output(array('success' => 1));
    }
    
}