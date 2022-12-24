<?php

class CollectorTagsController extends Controller
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
                        $this->json_output(array('error' => 'Укажите название тега'));
                    }
                    elseif (empty($color))
                    {
                        $this->json_output(array('error' => 'Укажите цвет тега'));
                    }
                    else
                    {
                        $tag = array(
                            'name' => $name,
                            'color' => $color,
                        );
                        $id = $this->collector_tags->add_tag($tag);
                        
                        $this->json_output(array(
                            'id' => $id, 
                            'name' => $name, 
                            'color' => $color, 
                            'success' => 'Тег добавлен'
                        ));
                    }
                    
                break;
                
                case 'update':
                    
                    $id = $this->request->post('id', 'integer');
                    $name = trim($this->request->post('name'));
                    $color = trim($this->request->post('color'));
                    
                    if (empty($name))
                    {
                        $this->json_output(array('error' => 'Укажите название тега'));
                    }
                    elseif (empty($color))
                    {
                        $this->json_output(array('error' => 'Укажите цвет тега'));
                    }
                    else
                    {
                        $tag = array(
                            'name' => $name,
                            'color' => $color,
                        );
                        $this->collector_tags->update_tag($id, $tag);
                        
                        $this->json_output(array(
                            'id' => $id, 
                            'name' => $name, 
                            'color' => $color, 
                            'success' => 'Тег обновлен'
                        ));                        
                    }
                    
                break;
                
                case 'delete':
                    
                    $id = $this->request->post('id', 'integer');
                    
                    $this->collector_tags->delete_tag($id);
                    
                    $this->json_output(array(
                        'id' => $id, 
                        'success' => 'Тег удален'
                    ));
                    
                break;
                
            endswitch;
        }
        
    	$tags = $this->collector_tags->get_tags();
        $this->design->assign('tags', $tags);
        
        
        return $this->design->fetch('collector_tags.tpl');
    }
    
    
}