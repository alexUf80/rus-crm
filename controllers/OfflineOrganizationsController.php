<?php

class OfflineOrganizationsController extends Controller
{
    public function fetch()
    {
        if ($this->request->method('post'))
        {
            switch ($this->request->post('action', 'string')):
                
                case 'add':
                    
                    $name = trim($this->request->post('name'));
                    
                    if (empty($name))
                    {
                        $this->json_output(array('error' => 'Укажите название организации'));
                    }
                    else
                    {
                        $item = array(
                            'name' => $name,
                        );
                        $id = $this->offline->add_organization($item);
                        
                        $this->json_output(array(
                            'id' => $id, 
                            'name' => $name, 
                            'success' => 'Организация добавлена'
                        ));
                    }
                    
                break;
                
                case 'update':
                    
                    $id = $this->request->post('id', 'integer');
                    $name = trim($this->request->post('name'));
                    
                    if (empty($name))
                    {
                        $this->json_output(array('error' => 'Укажите название'));
                    }
                    else
                    {
                        $item = array(
                            'name' => $name,
                        );
                        $this->offline->update_organization($id, $item);
                        
                        $this->json_output(array(
                            'id' => $id, 
                            'name' => $name, 
                            'success' => 'Организация обновлена'
                        ));                        
                    }
                    
                break;
                
                case 'delete':
                    
                    $id = $this->request->post('id', 'integer');
                    
                    $this->offline->delete_organization($id);
                    
                    $this->json_output(array(
                        'id' => $id, 
                        'success' => 'Организация удалена'
                    ));
                    
                break;
                
            endswitch;
        }
        
        $organizations = $this->offline->get_organizations();
        $this->design->assign('organizations', $organizations);
        
        return $this->design->fetch('offline_organizations.tpl');
    }
    
    
}