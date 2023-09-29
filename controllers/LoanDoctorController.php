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

        $clients = $this->LoanDoctor->get_ld_all();
		
 		$clients_count = count($clients);
		
		$pages_num = ceil($clients_count/$items_per_page);
		$this->design->assign('total_pages_num', $pages_num);
		$this->design->assign('total_orders_count', $clients_count);

        $current_page = $this->request->get('page', 'integer');
		$current_page = max(1, $current_page);
		$this->design->assign('current_page_num', $current_page);

        $sql_limit = $this->db->placehold(' LIMIT ?, ? ', ($current_page-1)*$items_per_page, $items_per_page);

        $clients = $this->LoanDoctor->get_ld($filter);

        foreach ($clients as $key => $client) {
            if ($client->status == 3 || $client->status == 8) {
                $clients[$key]->link = $this->users->get_looker_link($client->id);
            }
        }

        $this->design->assign('clients', $clients);
        
        return $this->design->fetch('loan_doctor.tpl');
    }
    
}