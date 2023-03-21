<?php

class NbkiReportsController extends Controller
{
    public function fetch()
    {
        
        $filter = [];
        $filter['limit'] = 20;
        
        if (!($filter['page'] = $this->request->get('page')))
            $filter['page'] = 1;
        
        
		$orders_count = $this->nbki_report->count_reports($filter);
		$pages_num = ceil($orders_count/$filter['limit']);
		$this->design->assign('total_pages_num', $pages_num);
		$this->design->assign('total_orders_count', $orders_count);
		$this->design->assign('current_page_num', $filter['page']);

        $reports = array();        
        foreach ($this->nbki_report->get_reports($filter) as $report)
            $reports[] = $report;
        
        
        $this->design->assign('reports', $reports);
        
        return $this->design->fetch('nbki_reports.tpl');
    }
    
}