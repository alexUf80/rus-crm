<?php

class LoanDoctorController extends Controller
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
        
		// $current_page = $this->request->get('page', 'integer');
		// $current_page = max(1, $current_page);
		// $this->design->assign('current_page_num', $current_page);

		// $clients_count = $this->users->count_users($filter);
		
		// $pages_num = ceil($clients_count/$items_per_page);
		// $this->design->assign('total_pages_num', $pages_num);
		// $this->design->assign('total_orders_count', $clients_count);

		// $filter['page'] = $current_page;
		// $filter['limit'] = $items_per_page;
    	
        
        $clients_to_select = $this->users->get_users($filter);

        $clients = [];
        foreach ($clients_to_select as $user) {
        
            $orders = $this->orders->get_orders(array('user_id' => $user->id, 'sort' => 'date_desc'));

            foreach ($orders as $o) {
                if($o->client_status == 'kd'){
                    if ($o->status == 5) {
                        $order = $o;
                        break;
                    }
                    continue;
                }
                $order = $o;
                break;
            }
            
            if ($order->status == 3 || $order->status == 8) {
                $user->link = $looker_link = $this->users->get_looker_link($order->user_id);;
                $clients[] = $user;
            }
        }
        // die;

        $this->design->assign('clients', $clients);
        
        return $this->design->fetch('loan_doctor.tpl');
    }
    
}