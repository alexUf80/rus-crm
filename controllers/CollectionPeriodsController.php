<?php

class CollectionPeriodsController extends Controller
{
    public function fetch()
    {
    	if ($this->request->method('post'))
        {
            $this->settings->collection_periods = $this->request->post('collection_periods');
        }
        
        $collection_statuses = $this->contracts->get_collection_statuses();
        $this->design->assign('collection_statuses', $collection_statuses);
        
        $this->design->assign('collection_periods', $this->settings->collection_periods);
        
        return $this->design->fetch('collection_periods.tpl');
    }
    
}