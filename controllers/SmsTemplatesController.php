<?php

class SmsTemplatesController extends Controller
{
    public function fetch()
    {
        if ($this->request->method('post'))
        {
            switch ($this->request->post('action', 'string')):
                
                case 'add':
                    
                    $name = trim($this->request->post('name'));
                    $template = trim($this->request->post('template'));
                    $type = trim($this->request->post('type'));
                    
                    if (empty($name))
                    {
                        $this->json_output(array('error' => 'Укажите название шаблона'));
                    }
                    elseif (empty($template))
                    {
                        $this->json_output(array('error' => 'Укажите текст сообщения'));
                    }
                    else
                    {
                        $sms_template = array(
                            'name' => $name,
                            'template' => $template,
                            'type' => $type,
                        );
                        $id = $this->sms->add_template($sms_template);
                        
                        $this->json_output(array(
                            'id' => $id, 
                            'name' => $name, 
                            'template' => $template, 
                            'type' => $type,
                            'success' => 'Шаблон сообщения добавлен'
                        ));
                    }
                    
                break;
                
                case 'update':
                    
                    $id = $this->request->post('id', 'integer');
                    $name = trim($this->request->post('name'));
                    $template = trim($this->request->post('template'));
                    $type = trim($this->request->post('type'));
                    
                    if (empty($name))
                    {
                        $this->json_output(array('error' => 'Укажите название шаблона'));
                    }
                    elseif (empty($template))
                    {
                        $this->json_output(array('error' => 'Укажите текст сообщения'));
                    }
                    else
                    {
                        $sms_template = array(
                            'name' => $name,
                            'template' => $template,
                            'type' => $type,
                        );
                        $this->sms->update_template($id, $sms_template);
                        
                        $this->json_output(array(
                            'id' => $id, 
                            'name' => $name, 
                            'template' => $template, 
                            'type' => $type,
                            'success' => 'Шаблон обновлен'
                        ));                        
                    }
                    
                break;
                
                case 'delete':
                    
                    $id = $this->request->post('id', 'integer');
                    
                    $this->sms->delete_template($id);
                    
                    $this->json_output(array(
                        'id' => $id, 
                        'success' => 'Шаблон удален'
                    ));
                    
                break;
                
            endswitch;
        }
        
    	$sms_templates = $this->sms->get_templates();
        $this->design->assign('sms_templates', $sms_templates);
        
        $template_types = $this->sms->get_template_types();
        $this->design->assign('template_types', $template_types);
        
        return $this->design->fetch('sms_templates.tpl');
    }
    
    
}