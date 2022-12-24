<?php

class OfflinePointsController extends Controller
{
    public function fetch()
    {
        if ($this->request->method('post'))
        {
            switch ($this->request->post('action', 'string')):
                
                case 'add':
                    
                    $city = trim($this->request->post('city'));
                    $address = trim($this->request->post('address'));
                    
                    if (empty($city))
                    {
                        $this->json_output(array('error' => 'Укажите название города'));
                    }
                    elseif (empty($address))
                    {
                        $this->json_output(array('error' => 'Укажите адрес'));
                    }
                    else
                    {
                        $item = array(
                            'city' => $city,
                            'address' => $address,
                        );
                        $id = $this->offline->add_point($item);
                        
                        $this->json_output(array(
                            'id' => $id, 
                            'city' => $city, 
                            'address' => $address, 
                            'success' => 'Отделение добавлено добавлен'
                        ));
                    }
                    
                break;
                
                case 'update':
                    
                    $id = $this->request->post('id', 'integer');
                    $city = trim($this->request->post('city'));
                    $address = trim($this->request->post('address'));
                    
                    if (empty($city))
                    {
                        $this->json_output(array('error' => 'Укажите название города'));
                    }
                    elseif (empty($address))
                    {
                        $this->json_output(array('error' => 'Укажите адрес'));
                    }
                    else
                    {
                        $item = array(
                            'city' => $city,
                            'address' => $address,
                        );
                        $this->offline->update_point($id, $item);
                        
                        $this->json_output(array(
                            'id' => $id, 
                            'city' => $city, 
                            'address' => $address,
                            'success' => 'Отделение обновлено'
                        ));                        
                    }
                    
                break;
                
                case 'delete':
                    
                    $id = $this->request->post('id', 'integer');
                    
                    $this->offline->delete_point($id);
                    
                    $this->json_output(array(
                        'id' => $id, 
                        'success' => 'Отделение удалено'
                    ));
                    
                break;
                
            endswitch;
        }
        
        $offline_points = $this->offline->get_points();
        $this->design->assign('offline_points', $offline_points);
        
        return $this->design->fetch('offline_points.tpl');
    }
    
    
}