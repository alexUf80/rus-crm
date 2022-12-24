<?php

class SudblockDocumentsController extends Controller
{
    public function fetch()
    {
        if ($this->request->method('post'))
        {
            switch ($this->request->post('action', 'string')):
                
                case 'add':
                    
                    $name = trim($this->request->post('name'));
                    $provider = trim($this->request->post('provider'));
                    $base = trim($this->request->post('base', 'integer'));
                    $block = trim($this->request->post('block', 'string'));
                    $sudblock_contract_id = $this->request->post('sudblock_contract_id', 'integer');
                    
                    $file = $this->request->files('file');
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($file);echo '</pre><hr />';                    
                    if (empty($file['size']))
                    {
                        $this->json_output(array('error' => 'Загрузите файл'));
                    }
                    elseif (!empty($file['error']))
                    {
                        $this->json_output(array('error' => 'Ошибка при загрузке'));
                    }
                    elseif (empty($name))
                    {
                        $this->json_output(array('error' => 'Укажите название документа'));
                    }
                    elseif (empty($provider))
                    {
                        $this->json_output(array('error' => 'Укажите поставщика'));
                    }
                    else
                    {
                        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                        
                        $filename = md5(time().rand()).'.'.$ext;
                        
                        if (!empty($sudblock_contract_id))
                        {
                            if (!file_exists($this->config->root_dir.'files/sudblock/'.$sudblock_contract_id))
                                mkdir($this->config->root_dir.'files/sudblock/'.$sudblock_contract_id, 0775);
                            
                            $added_filename = $this->config->root_dir.'files/sudblock/'.$sudblock_contract_id.'/'.$filename;
                        }
                        else
                        {
                            $added_filename = $this->config->root_dir.'files/sudblock/'.$filename;
                        }
                        
                        if (move_uploaded_file($file['tmp_name'], $added_filename))
                        {
                            $document = array(
                                'name' => $name,
                                'provider' => $provider,
                                'filename' => $filename,
                                'base' => $base,
                                'block' => $block,
                                'created' => date('Y-m-d H:i:s'),
                                'ready' => 0,
                                'sudblock_contract_id' => $sudblock_contract_id,
                            );
                            $id = $this->sudblock->add_document($document);
                            
                            $this->json_output(array(
                                'id' => $id, 
                                'name' => $name, 
                                'provider' => $provider,
                                'filename' => $filename, 
                                'block' => $block,
                                'success' => 'Документ добавлен'
                            ));
                            
                        }
                        else
                        {
                            $this->json_output(array('error' => 'Не удалось сохранить документ'));
                        }
                    }
                    
                break;
                
                case 'update':
                    
                    $id = $this->request->post('id', 'integer');
                    $name = trim($this->request->post('name'));
                    $provider = trim($this->request->post('provider'));
                    $block = trim($this->request->post('block'));
                    
                    if (empty($name))
                    {
                        $this->json_output(array('error' => 'Укажите название документа'));
                    }
                    elseif (empty($provider))
                    {
                        $this->json_output(array('error' => 'Укажите поставщика договора'));
                    }
                    else
                    {
                        $reason = array(
                            'name' => $name,
                            'provider' => $provider,
                            'block' => $block,
                        );
                        $this->sudblock->update_document($id, $reason);
                        
                        $this->json_output(array(
                            'id' => $id, 
                            'name' => $name, 
                            'provider' => $provider, 
                            'block' => $block, 
                            'success' => 'Документ обновлен'
                        ));                        
                    }
                    
                break;
                
                case 'delete':
                    
                    $id = $this->request->post('id', 'integer');
                    
                    $this->sudblock->delete_document($id);
                    
                    $this->json_output(array(
                        'id' => $id, 
                        'success' => 'Документа удален'
                    ));
                    
                break;
                
            endswitch;
        }
        
        $documents = $this->sudblock->get_documents(array('base' => 1));
        $this->design->assign('documents', $documents);

        return $this->design->fetch('sudblock_documents.tpl');
    }
    
}