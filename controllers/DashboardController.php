<?php

class DashboardController extends Controller
{
    public function fetch()
    {
    	return $this->design->fetch('dashboard.tpl');
    }
    
}