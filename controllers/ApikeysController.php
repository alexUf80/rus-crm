<?php

class ApikeysController extends Controller
{
    public function fetch()
    {
        if ($this->request->method('post'))
        {
            $apikeys = $this->request->post('apikeys');
            
            $this->settings->apikeys = $apikeys;
        }
        else
        {
            $apikeys = $this->settings->apikeys;
        }
        
        $this->design->assign('apikeys', $apikeys);
        
        return $this->design->fetch('apikeys.tpl');
    }
}