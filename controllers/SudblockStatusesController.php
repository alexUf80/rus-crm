<?php

class SudblockStatusesController extends Controller
{
    public function fetch()
    {
        if ($this->request->method('post'))
        {
            switch ($this->request->post('action', 'string')):
                
                case 'add':
                    
                    $name = trim($this->request->post('name'));
                    $color = trim($this->request->post('color'));
                    
                    if (empty($name))
                    {
                        $this->json_output(array('error' => 'Укажите название статуса'));
                    }
                    elseif (empty($color))
                    {
                        $this->json_output(array('error' => 'Укажите цвет статуса'));
                    }
                    else
                    {
                        $status = array(
                            'name' => $name,
                            'color' => $color,
                        );
                        $id = $this->sudblock->add_status($status);
                        
                        $this->json_output(array(
                            'id' => $id, 
                            'name' => $name, 
                            'color' => $color, 
                            'success' => 'Статус добавлен'
                        ));
                    }
                    
                break;
                
                case 'update':
                    
                    $id = $this->request->post('id', 'integer');
                    $name = trim($this->request->post('name'));
                    $color = trim($this->request->post('color'));
                    
                    if (empty($name))
                    {
                        $this->json_output(array('error' => 'Укажите название статуса'));
                    }
                    elseif (empty($color))
                    {
                        $this->json_output(array('error' => 'Укажите цвет статуса'));
                    }
                    else
                    {
                        $status = array(
                            'name' => $name,
                            'color' => $color,
                        );
                        $this->sudblock->update_status($id, $status);
                        
                        $this->json_output(array(
                            'id' => $id, 
                            'name' => $name, 
                            'color' => $color, 
                            'success' => 'Статус обновлен'
                        ));                        
                    }
                    
                break;
                
                case 'delete':
                    
                    $id = $this->request->post('id', 'integer');
                    
                    $this->sudblock->delete_status($id);
                    
                    $this->json_output(array(
                        'id' => $id, 
                        'success' => 'Статус удален'
                    ));
                    
                break;
                
            endswitch;
        }
        
    	$statuses = $this->sudblock->get_statuses();
        $this->design->assign('statuses', $statuses);
        
        
        return $this->design->fetch('sudblock_statuses.tpl');
    }
    
    
}