<?php

class TicketStatusesController extends Controller
{
    public function fetch()
    {
    	
        return $this->design->fetch('ticket_statuses.tpl');
    }
    
}