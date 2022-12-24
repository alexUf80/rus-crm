<?php

class SudblockContractController extends Controller
{
    public function fetch()
    {
    	if (!($contract_id = $this->request->get('id', 'integer')))
            return false;

        if ($this->request->method('post'))
        {
            switch ($this->request->post('action', 'string')):
                
                case 'add_notification':
                    $this->add_notification_action();
                break;
                
                case 'send_sud':
                    $this->send_sud_action($contract_id);
                break;
                
                case 'sudprikaz':
                    $this->sudprikaz_action($contract_id);
                break;
                
                case 'send_fssp':
                    $this->send_fssp_action($contract_id);
                break;
                
                case 'ready_document':
                    $this->ready_document_action();
                break;

            endswitch;
            
        }


        switch ($this->request->get('action', 'string')):
            
            case 'create':
                $block = $this->request->get('block', 'string');
                if ($block == 'sud')
                    $this->create_sud_documents_action($contract_id);
                if ($block == 'fssp')
                    $this->create_fssp_documents_action($contract_id);
            break;
            
            case 'remove':
                $this->remove_document_action();
            break;                        
                                    
        endswitch;
        
        

        $managers = array();
        foreach ($this->managers->get_managers() as $m)
            $managers[$m->id] = $m;

        if ($contract = $this->sudblock->get_contract($contract_id))
        {
            $date1 = new DateTime(date('Y-m-d', strtotime($contract->created)));
            $date2 = new DateTime(date('Y-m-d'));
            
            $diff = $date2->diff($date1);
            $contract->delay = $diff->days;

            if (!empty($contract->user_id))
            {
                $contract->user = $this->users->get_user($contract->user_id);
            }
            
            if (!empty($contract->contract_id))
            {
                $contract->contract = $this->contracts->get_contract($contract->contract_id);
            
                $contract_operations = $this->operations->get_operations(array('contract_id'=>$contract->contract_id));
                $this->design->assign('contract_operations', $contract_operations);

//                $documents = $this->documents->get_documents(array('contract_id' => $contract->contract_id));
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($documents);echo '</pre><hr />';
//                $this->design->assign('documents', $documents);
                
                $comments = $this->comments->get_comments(array('user_id' => $contract->user_id));
                $this->design->assign('comments', $comments);

                $user = $this->users->get_user((int)$contract->user_id);
                $changelogs = $this->changelogs->get_changelogs(array('order_id'=>$contract->contract->order_id));
                foreach ($changelogs as $changelog)
                {
                    $changelog->user = $user;
                    if (!empty($changelog->manager_id) && !empty($managers[$changelog->manager_id]))
                        $changelog->manager = $managers[$changelog->manager_id];
                }
                $changelog_types = $this->changelogs->get_types();
                
                $this->design->assign('changelog_types', $changelog_types);
                $this->design->assign('changelogs', $changelogs);
                

            }
            
            if ($documents = $this->sudblock->get_documents(array('sudblock_contract_id' => $contract->id)))
            {
                $sud_documents = array();
                $fssp_documents = array();
                
                foreach ($documents as $doc)
                {
                    if ($doc->block == 'sud')
                        $sud_documents[] = $doc;
                    if ($doc->block == 'fssp')
                        $fssp_documents[] = $doc;
                }
                
                $this->design->assign('sud_documents', $sud_documents);
                $this->design->assign('fssp_documents', $fssp_documents);
            }

            $this->design->assign('contract', $contract);
            
            if (in_array('looker_link', $this->manager->permissions))
            {
                $looker_link = $this->users->get_looker_link($contract->user->id);
                $this->design->assign('looker_link', $looker_link);
            }

        
        }
        
        $statuses = array();
        foreach ($this->sudblock->get_statuses() as $st)
            $statuses[$st->id] = $st;
        $this->design->assign('statuses', $statuses);
        
        $notification_events = $this->notifications->get_events();
        $this->design->assign('notification_events', $notification_events);
        
        $default_notification_date = date('Y-m-d', time() + 86400 * 30);
        $this->design->assign('default_notification_date', $default_notification_date);
        
        if ($notifications = $this->notifications->get_notifications(array('sudblock_contract_id' => $contract->id)))
        {
            foreach ($notifications as $n)
            {
                if (!empty($n->event_id))
                    $n->event = $this->notifications->get_event($n->event_id);
            }
        }
        $this->design->assign('notifications', $notifications);
        
        return $this->design->fetch('sudblock_contract.tpl');
    }
    
    public function ready_document_action()
    {
        $ready = $this->request->post('ready', 'integer');
        $document_id = $this->request->post('document_id', 'integer');
        
        $this->sudblock->update_document($document_id, array('ready' => $ready));

        $this->json_output(array(
            'success' => 1,
            'ready' => $ready
        ));
        exit;
        
    }
    
    private function send_fssp_action($contract_id)
    {
        $fssp_post_number = $this->request->post('post_number');
        
    	$this->sudblock->update_contract($contract_id, array('status' => 5, 'fssp_post_number' => $fssp_post_number));
    }
    
    private function send_sud_action($contract_id)
    {
        $sud_post_number = $this->request->post('post_number');
        
    	$this->sudblock->update_contract($contract_id, array('status' => 2, 'sud_post_number' => $sud_post_number));    	
    }
        
    private function sudprikaz_action($contract_id)
    {
        $sudprikaz_number = $this->request->post('sudprikaz_number');
        $sudprikaz_date = $this->request->post('sudprikaz_date');
        
    	$this->sudblock->update_contract($contract_id, array(
            'status' => 3, 
            'sudprikaz_number' => $sudprikaz_number,
            'sudprikaz_date' => $sudprikaz_date,
            'sudprikaz_added_date' => date('Y-m-d H:i:s'),
        ));    	

        $this->json_output(array('success' => 1));
    }
        
    private function create_sud_documents_action($sudblock_contract_id)
    {
        $document_dir = $this->config->root_dir.'files/sudblock/'.$sudblock_contract_id.'/';
        
        if (!($sudblock_contract = $this->sudblock->get_contract($sudblock_contract_id)))
            return false;
        
        // создаем папку для документов если ее нет
        if (!file_exists($document_dir))
            mkdir($document_dir, 0775);
        
        $isset_documents = $this->sudblock->get_documents(array('sudblock_contract_id' => $sudblock_contract_id));
        
        if ($contract = $this->contracts->get_contract($sudblock_contract->contract_id))
        {
            // договор займа
            $contract_document_created = 0;
            foreach ($isset_documents as $idoc)
                if ($idoc->parent == 'IND_USLOVIYA_NL')
                    $contract_document_created = 1;
            
            if (empty($contract_document_created))
            {
                if ($contract_document = $this->documents->get_contract_document($contract->id, 'IND_USLOVIYA_NL'))
                {
                    $contract_filename = md5(rand().time()).'.pdf';
                    $file = file_get_contents($this->config->front_url.'/document/'.$contract_document->user_id.'/'.$contract_document->id);
                    file_put_contents($document_dir.$contract_filename, $file);
                    
                    $this->sudblock->add_document(array(
                        'sudblock_contract_id' => $sudblock_contract->id,
                        'base' => 0,
                        'filename' => $contract_filename,
                        'name' => $contract_document->name,
                        'provider' => $sudblock_contract->provider,
                        'block' => 'sud',
                        'ready' => 0,
                        'parent' => 'IND_USLOVIYA_NL',
                        'created' => date('Y-m-d H:i:s'),
                    ));
                    
                }
            }
        }
        
        // создаем общие документы 
        if ($base_documents = $this->sudblock->get_documents(array('base' => 1, 'block' => 'sud')))
        {
            foreach ($base_documents as $bdoc)
            {
                if ($bdoc->provider == $sudblock_contract->provider)
                {
                    $bdoc_document_created = 0;
                    foreach ($isset_documents as $idoc)
                        if ($idoc->parent == $bdoc->id)
                            $bdoc_document_created = 1;
                
                    if (empty($bdoc_document_created))
                    {
                        $file = file_get_contents($this->config->root_dir.'files/sudblock/'.$bdoc->filename);
                        file_put_contents($document_dir.$bdoc->filename, $file);
                        
                        $this->sudblock->add_document(array(
                            'sudblock_contract_id' => $sudblock_contract->id,
                            'base' => 0,
                            'filename' => $bdoc->filename,
                            'name' => $bdoc->name,
                            'provider' => $sudblock_contract->provider,
                            'block' => 'sud',
                            'ready' => 0,
                            'parent' => $bdoc->id,
                            'created' => date('Y-m-d H:i:s'),
                        ));
                        
                    }
                }
            }
        }
        
        // генерируем документы по шаблону
        $document_types = $this->documents->get_sudblock_create_documents('sud');

        $order = $this->orders->get_order($contract->order_id);
        $user = $this->users->get_user($contract->user_id);
        
        if ($operations = $this->operations->get_operations(array('contract_id' => $contract->id, 'type' => 'PAY')))
        {
            $last_payment = NULL;
            foreach ($operations as $op)
            {
                if (empty($last_payment) || strtotime($last_payment) < strtotime($op->created))
                    $last_payment = $op->created;
            }
        }
        else
        {
            $last_payment = $contract->inssuance_date;
        }
        
        $regaddress_full = empty($user->Regindex) ? '' : $user->Regindex.', ';
        $regaddress_full .= trim($user->Regregion.' '.$user->Regregion_shorttype);
        $regaddress_full .= empty($user->Regcity) ? '' : trim(', '.$user->Regcity.' '.$user->Regcity_shorttype);
        $regaddress_full .= empty($user->Regdistrict) ? '' : trim(', '.$user->Regdistrict.' '.$user->Regdistrict_shorttype);
        $regaddress_full .= empty($user->Reglocality) ? '' : trim(', '.$user->Reglocality.' '.$user->Reglocality_shorttype);
        $regaddress_full .= empty($user->Reghousing) ? '' : ', д.'.$user->Reghousing;
        $regaddress_full .= empty($user->Regbuilding) ? '' : ', стр.'.$user->Regbuilding;
        $regaddress_full .= empty($user->Regroom) ? '' : ', к.'.$user->Regroom;
        
        $passport_serial = str_replace(array('-', ' '), '', $user->passport_serial);
        $passport_series = substr($passport_serial, 0, 4);
        $passport_number = substr($passport_serial, 4, 6);
        
        $exactor_name = '';
        if ($exactor = $this->managers->get_manager($sudblock_contract->manager_id))
            $exactor_name = $exactor->name;
        
        $params = array(
            'fio' => $user->lastname.' '.$user->firstname.' '.$user->patronymic,
            'birth' => $user->birth,
            'birth_place' => $user->birth_place,
            'document_date' => date('Y-m-d H:i:s'),
            'tribunal' => $sudblock_contract->tribunal,
            'first_number' => $sudblock_contract->first_number,
            'contract_date' => $contract->inssuance_date,
            'return_date' => $contract->return_date,
            'last_payment' => $last_payment,
            'regaddress_full' => $regaddress_full,
            'body_summ' => $sudblock_contract->loan_summ,
            'total_summ' => $sudblock_contract->total_summ,
            'passport_series' => $passport_series,
            'passport_number' => $passport_number,
            'passport_issued' => $user->passport_issued,
            'passport_date' => $user->passport_date,
            'passport_code' => $user->subdivision_code,
            'exactor_name' => $exactor_name,
            'sudprikaz_number' => $sudblock_contract->sudprikaz_number,
            'sudprikaz_date' => $sudblock_contract->sudprikaz_date,
        );
        
        foreach ($document_types as $dtype)
        {
            if ($found_doc = $this->documents->get_contract_document($sudblock_contract->contract_id, $dtype))
            {
                $this->documents->update_document($found_doc->id, array(
                    'params' => $params
                ));
            }
            else
            {
                $id =  $this->documents->create_document(array(
                    'user_id' => isset($user->id) ? $user->id : 0,
                    'order_id' => isset($contract->order_id) ? $contract->order_id : 0,
                    'contract_id' => isset($contract->id) ? $contract->id : 0,
                    'type' => $dtype,
                    'params' => $params,
                ));
                $found_doc = $this->documents->get_document($id);
            }
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($found_doc);echo '</pre><hr />';            
            $dtype_document_created = 0;
            foreach ($isset_documents as $idoc)
                if ($idoc->parent == $dtype)
                    $dtype_document_created = $idoc;
        
            if (empty($dtype_document_created))
            {
                $dtype_filename = md5(rand().time()).'.pdf';
                
                $this->sudblock->add_document(array(
                    'sudblock_contract_id' => $sudblock_contract->id,
                    'base' => 0,
                    'filename' => $dtype_filename,
                    'name' => $found_doc->name,
                    'provider' => $sudblock_contract->provider,
                    'block' => 'sud',
                    'ready' => 0,
                    'parent' => $dtype,
                    'created' => date('Y-m-d H:i:s'),
                ));
            }
            else
            {
                $dtype_filename = $dtype_document_created->filename;
            }

            $file = file_get_contents($this->config->front_url.'/document/'.$found_doc->user_id.'/'.$found_doc->id);
            file_put_contents($document_dir.$dtype_filename, $file);
        }        
        
        header('Location: '.$this->request->url(array('action'=>NULL, 'block'=>NULL)));
        exit;        
    }
    
    private function create_fssp_documents_action($sudblock_contract_id)
    {
        $document_dir = $this->config->root_dir.'files/sudblock/'.$sudblock_contract_id.'/';
        
        if (!($sudblock_contract = $this->sudblock->get_contract($sudblock_contract_id)))
            return false;
        
        // создаем папку для документов если ее нет
        if (!file_exists($document_dir))
            mkdir($document_dir, 0775);
        
        $isset_documents = $this->sudblock->get_documents(array('sudblock_contract_id' => $sudblock_contract_id));
        
        $contract = $this->contracts->get_contract($sudblock_contract->contract_id);
        
        
        // создаем общие документы 
        if ($base_documents = $this->sudblock->get_documents(array('base' => 1, 'block' => 'fssp')))
        {
            foreach ($base_documents as $bdoc)
            {
                if ($bdoc->provider == $sudblock_contract->provider)
                {
                    $bdoc_document_created = 0;
                    foreach ($isset_documents as $idoc)
                        if ($idoc->parent == $bdoc->id)
                            $bdoc_document_created = 1;
                
                    $file = file_get_contents($this->config->root_dir.'files/sudblock/'.$bdoc->filename);

                    file_put_contents($document_dir.$bdoc->filename, $file);
                        
                    if (empty($bdoc_document_created))
                    {
                        $this->sudblock->add_document(array(
                            'sudblock_contract_id' => $sudblock_contract->id,
                            'base' => 0,
                            'filename' => $bdoc->filename,
                            'name' => $bdoc->name,
                            'provider' => $sudblock_contract->provider,
                            'block' => 'fssp',
                            'ready' => 0,
                            'parent' => $bdoc->id,
                            'created' => date('Y-m-d H:i:s'),
                        ));
                        
                    }
                }
            }
        }
        
        // генерируем документы по шаблону
        $document_types = $this->documents->get_sudblock_create_documents('fssp');
        
        $order = $this->orders->get_order($contract->order_id);
        $user = $this->users->get_user($contract->user_id);
        
        if ($operations = $this->operations->get_operations(array('contract_id' => $contract->id, 'type' => 'PAY')))
        {
            $last_payment = NULL;
            foreach ($operations as $op)
            {
                if (empty($last_payment) || strtotime($last_payment) < strtotime($op->created))
                    $last_payment = $op->created;
            }
        }
        else
        {
            $last_payment = $contract->inssuance_date;
        }
        
        $regaddress_full = empty($user->Regindex) ? '' : $user->Regindex.', ';
        $regaddress_full .= trim($user->Regregion.' '.$user->Regregion_shorttype);
        $regaddress_full .= empty($user->Regcity) ? '' : trim(', '.$user->Regcity.' '.$user->Regcity_shorttype);
        $regaddress_full .= empty($user->Regdistrict) ? '' : trim(', '.$user->Regdistrict.' '.$user->Regdistrict_shorttype);
        $regaddress_full .= empty($user->Reglocality) ? '' : trim(', '.$user->Reglocality.' '.$user->Reglocality_shorttype);
        $regaddress_full .= empty($user->Reghousing) ? '' : ', д.'.$user->Reghousing;
        $regaddress_full .= empty($user->Regbuilding) ? '' : ', стр.'.$user->Regbuilding;
        $regaddress_full .= empty($user->Regroom) ? '' : ', к.'.$user->Regroom;
        
        $passport_serial = str_replace(array('-', ' '), '', $user->passport_serial);
        $passport_series = substr($passport_serial, 0, 4);
        $passport_number = substr($passport_serial, 4, 6);
        
        $exactor_name = '';
        $exactor_phone = '';
        if ($exactor = $this->managers->get_manager($sudblock_contract->manager_id))
        {        
            $exactor_name = $exactor->name;
            $exactor_phone = $exactor->phone;
        }
                
        $params = array(
            'fio' => $user->lastname.' '.$user->firstname.' '.$user->patronymic,
            'birth' => $user->birth,
            'birth_place' => $user->birth_place,
            'document_date' => date('Y-m-d H:i:s'),
            'tribunal' => $sudblock_contract->tribunal,
            'first_number' => $sudblock_contract->first_number,
            'contract_date' => $contract->inssuance_date,
            'return_date' => $contract->return_date,
            'last_payment' => $last_payment,
            'regaddress_full' => $regaddress_full,
            'body_summ' => $sudblock_contract->loan_summ,
            'total_summ' => $sudblock_contract->total_summ,
            'passport_series' => $passport_series,
            'passport_number' => $passport_number,
            'passport_issued' => $user->passport_issued,
            'passport_date' => $user->passport_date,
            'passport_code' => $user->subdivision_code,
            'exactor_name' => $exactor_name,
            'exactor_phone' => $exactor_phone,
            'sudprikaz_number' => $sudblock_contract->sudprikaz_number,
            'sudprikaz_date' => $sudblock_contract->sudprikaz_date,
        );
        
        foreach ($document_types as $dtype)
        {
            if ($found_doc = $this->documents->get_contract_document($sudblock_contract->contract_id, $dtype))
            {
                $this->documents->update_document($found_doc->id, array(
                    'params' => $params
                ));
            }
            else
            {
                $id =  $this->documents->create_document(array(
                    'user_id' => isset($user->id) ? $user->id : 0,
                    'order_id' => isset($contract->order_id) ? $contract->order_id : 0,
                    'contract_id' => isset($contract->id) ? $contract->id : 0,
                    'type' => $dtype,
                    'params' => $params,
                ));
                $found_doc = $this->documents->get_document($id);
            }
            
            $dtype_document_created = 0;
            foreach ($isset_documents as $idoc)
                if ($idoc->parent == $dtype)
                    $dtype_document_created = $idoc;
        
            if (empty($dtype_document_created))
            {
                $dtype_filename = md5(rand().time()).'.pdf';
                
                $this->sudblock->add_document(array(
                    'sudblock_contract_id' => $sudblock_contract->id,
                    'base' => 0,
                    'filename' => $dtype_filename,
                    'name' => $found_doc->name,
                    'provider' => $sudblock_contract->provider,
                    'block' => 'fssp',
                    'ready' => 0,
                    'parent' => $dtype,
                    'created' => date('Y-m-d H:i:s'),
                ));
            }
            else
            {
                $dtype_filename = $dtype_document_created->filename;
            }

            $file = file_get_contents($this->config->front_url.'/document/'.$found_doc->user_id.'/'.$found_doc->id);
            file_put_contents($document_dir.$dtype_filename, $file);
        }
                
        
        
                header('Location: '.$this->request->url(array('action'=>NULL, 'block'=>NULL)));
        exit;
    }
    
    private function remove_document_action()
    {
        $document_id = $this->request->get('document');
        
        $this->sudblock->delete_document($document_id);
        
        header('Location:'.$this->request->url(array('action'=>NULL, 'document'=>NULL)));
        exit;                                        
            
    }            
    
    
    
    
    private function add_notification_action()
    {
        $notification = array(
            'sudblock_contract_id' => $this->request->post('contract_id', 'integer'),
            'manager_id' => $this->manager->id,
            'created' => date('Y-m-d H:i:s'),
            'notification_date' => date('Y-m-d', strtotime($this->request->post('notification_date'))),
            'comment' => $this->request->post('comment'),
            'event_id' => $this->request->post('event_id', 'integer')
        );
        
        if (empty($notification['event_id']))
        {
            $this->json_output(array('error' => 'Выберите событие'));
        }
        else
        {
            $id = $this->notifications->add_notification($notification);
        
            $this->json_output(array('success' => $id));
        }
    }
}