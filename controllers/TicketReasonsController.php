<?php

class TicketReasonsController extends Controller
{
    public function fetch()
    {
    	
        return $this->design->fetch('ticket_reasons.tpl');
    }
    
}