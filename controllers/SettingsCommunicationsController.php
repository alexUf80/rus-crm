<?php

class SettingsCommunicationsController extends Controller
{
    public function fetch()
    {
        
        
        if ($this->request->method('post'))
        {
            
            $this->settings->limit_communications = $this->request->post('limit_communications');
        }
        else
        {
            
        }
        

  
        return $this->design->fetch('settings_communications.tpl');
    }
}