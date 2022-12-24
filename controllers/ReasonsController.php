<?php

class ReasonsController extends Controller
{
    public function fetch()
    {
        if ($this->request->method('post'))
        {
            switch ($this->request->post('action', 'string')):
                
                case 'add':
                    
                    $admin_name = trim($this->request->post('admin_name'));
                    $client_name = trim($this->request->post('client_name'));
                    $type = trim($this->request->post('type', 'string'));
                    $maratory = trim($this->request->post('maratory'));
                    
                    if (empty($admin_name))
                    {
                        $this->json_output(array('error' => 'Укажите название для администратора'));
                    }
                    elseif (empty($client_name))
                    {
                        $this->json_output(array('error' => 'Укажите название для клиентов'));
                    }
                    else
                    {
                        $reason = array(
                            'admin_name' => $admin_name,
                            'client_name' => $client_name,
                            'type' => $type,
                            'maratory' => $maratory,
                        );
                        $id = $this->reasons->add_reason($reason);
                        
                        $this->json_output(array(
                            'id' => $id, 
                            'admin_name' => $admin_name, 
                            'client_name' => $client_name, 
                            'type' => $type, 
                            'maratory' => $maratory,
                            'success' => 'Причина отказа добавлена'
                        ));
                    }
                    
                break;
                
                case 'update':
                    
                    $id = $this->request->post('id', 'integer');
                    $admin_name = trim($this->request->post('admin_name'));
                    $client_name = trim($this->request->post('client_name'));
                    $type = trim($this->request->post('type', 'string'));
                    $maratory = trim($this->request->post('maratory', 'string'));
                    
                    if (empty($admin_name))
                    {
                        $this->json_output(array('error' => 'Укажите название для администратора'));
                    }
                    elseif (empty($client_name))
                    {
                        $this->json_output(array('error' => 'Укажите название для клиентов'));
                    }
                    else
                    {
                        $reason = array(
                            'admin_name' => $admin_name,
                            'client_name' => $client_name,
                            'type' => $type,
                            'maratory' => $maratory,
                        );
                        $this->reasons->update_reason($id, $reason);
                        
                        $this->json_output(array(
                            'id' => $id, 
                            'admin_name' => $admin_name, 
                            'client_name' => $client_name, 
                            'type' => $type, 
                            'maratory' => $maratory,
                            'success' => 'Причина отказа обновлена'
                        ));                        
                    }
                    
                break;
                
                case 'delete':
                    
                    $id = $this->request->post('id', 'integer');
                    
                    $this->reasons->delete_reason($id);
                    
                    $this->json_output(array(
                        'id' => $id, 
                        'success' => 'Причина отказа удалена'
                    ));
                    
                break;
                
            endswitch;
        }
        
    	$reasons = $this->reasons->get_reasons();
        $this->design->assign('reasons', $reasons);
        
        return $this->design->fetch('reasons.tpl');
    }
    
    
}