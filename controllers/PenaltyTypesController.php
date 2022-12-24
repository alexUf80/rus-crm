<?php

class PenaltyTypesController extends Controller
{
    public function fetch()
    {
        if ($this->request->method('post'))
        {
            switch ($this->request->post('action', 'string')):
                
                case 'add':
                    
                    $name = trim($this->request->post('name'));
                    $cost = trim($this->request->post('cost'));
                    
                    if (empty($name))
                    {
                        $this->json_output(array('error' => 'Укажите название штрафа'));
                    }
                    elseif (empty($cost))
                    {
                        $this->json_output(array('error' => 'Укажите сумму штрафа'));
                    }
                    else
                    {
                        $item = array(
                            'name' => $name,
                            'cost' => $cost,
                        );
                        $id = $this->penalties->add_type($item);
                        
                        $this->json_output(array(
                            'id' => $id, 
                            'name' => $name, 
                            'cost' => $cost, 
                            'success' => 'Штраф добавлен'
                        ));
                    }
                    
                break;
                
                case 'update':
                    
                    $id = $this->request->post('id', 'integer');
                    $name = trim($this->request->post('name'));
                    $cost = trim($this->request->post('cost'));
                    
                    if (empty($name))
                    {
                        $this->json_output(array('error' => 'Укажите название штрафа'));
                    }
                    elseif (empty($cost))
                    {
                        $this->json_output(array('error' => 'Укажите сумму штрафа'));
                    }
                    else
                    {
                        $item = array(
                            'name' => $name,
                            'cost' => $cost,
                        );
                        $this->penalties->update_type($id, $item);
                        
                        $this->json_output(array(
                            'id' => $id, 
                            'name' => $name, 
                            'cost' => $cost,
                            'success' => 'Штраф обновлен'
                        ));                        
                    }
                    
                break;
                
                case 'delete':
                    
                    $id = $this->request->post('id', 'integer');
                    
                    $this->penalties->delete_type($id);
                    
                    $this->json_output(array(
                        'id' => $id, 
                        'success' => 'Штраф удален'
                    ));
                    
                break;
                
            endswitch;
        }
        
        
        $types = $this->penalties->get_types();
        $this->design->assign('types', $types);
        
        return $this->design->fetch('penalty_types.tpl');
    }
    
    
}