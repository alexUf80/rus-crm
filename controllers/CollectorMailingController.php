<?php

class CollectorMailingController extends Controller
{
    public function fetch()
    {
        if ($this->request->method('post'))
        {
            $collectors = (array)$this->request->post('collectors');
            
            $sms = $this->request->post('sms', 'integer');
            $zvonobot = $this->request->post('zvonobot', 'integer');
            
            $mkk_check_number = $this->request->post('mkk_check_number');
            $yuk_check_number = $this->request->post('yuk_check_number');

            $mkk_text = $this->request->post('mkk_text');
            $yuk_text = $this->request->post('yuk_text');
            
            $this->design->assign('collectors', $collectors);
            $this->design->assign('sms', $sms);
            $this->design->assign('zvonobot', $zvonobot);
            $this->design->assign('mkk_check_number', $mkk_check_number);
            $this->design->assign('yuk_check_number', $yuk_check_number);
            $this->design->assign('mkk_text', $mkk_text);
            $this->design->assign('yuk_text', $yuk_text);
            
            
            $errors = array();
            
//            if (empty($collectors))
//                $errors[] = 'Выберите коллекторов для рассылки';
            
            if (empty($sms) && empty($zvonobot))
                $errors[] = 'Выберите тип рассылки (звонобот или смс-рассылка)';
            
            if (empty($mkk_text) && empty($yuk_text))
                $errors[] = 'Введите шаблоны сообщений';
            
            $this->design->assign('errors', $errors);
            
            if (empty($errors))
            {
                $mkk_numbers = array();
                $yuk_numbers = array();
                
                if (!empty($mkk_check_number))
                    $mkk_numbers[] = $mkk_check_number;                
                if (!empty($yuk_check_number))
                    $yuk_numbers[] = $yuk_check_number;
                
                if (!empty($collectors))
                {
                    if ($contracts = $this->contracts->get_contracts(array('collection_manager_id' => $collectors, 'status' => array(4,7))))
                    {
                        foreach ($contracts as $c)
                        {
                            if (empty($c->sold))
                                $mkk_numbers[$c->user_id] = '';
                            else
                                $yuk_numbers[$c->user_id] = '';
                        }
                    
                        if ($users = $this->users->get_users(array('id' => array_filter(array_merge(array_keys($mkk_numbers), array_keys($yuk_numbers))))))
                        {

                            foreach ($users as $u)
                            {
                                $u->client_time = $this->helpers->get_regional_time($u->Regregion);
                                $clock = date('H', strtotime($u->client_time));
                                $weekday = date('N', strtotime($u->client_time));
                                if ($weekday == 6 || $weekday == 7)
                                    $client_time_warning = $clock < 9 || $clock > 20;
                                else
                                    $client_time_warning = $clock < 8 || $clock > 21;

                                    if (isset($mkk_numbers[$u->id]))
                                    {
                                        $mkk_numbers[$u->id] = $u->phone_mobile;
                                    
                                        if (!empty($sms) && !empty($mkk_text))
                                        {
                                            $this->communications->add_communication(array(
                                                'user_id' => $u->id,
                                                'manager_id' => $this->manager->id,
                                                'created' => date('Y-m-d H:i:s'),
                                                'type' => 'sms',
                                                'content' => '',
                                            ));
                                        }
                                        
                                        if (!empty($zvonobot) && !empty($mkk_text))
                                        {
                                            $this->communications->add_communication(array(
                                                'user_id' => $u->id,
                                                'manager_id' => $this->manager->id,
                                                'created' => date('Y-m-d H:i:s'),
                                                'type' => 'zvonobot',
                                                'content' => 'mailing',
                                            ));
                                        }
                                    }
                                    
                                    if (isset($yuk_numbers[$u->id]))
                                    {
                                        $yuk_numbers[$u->id] = $u->phone_mobile;
                                    
                                        if (!empty($sms) && !empty($yuk_text))
                                        {
                                            $this->communications->add_communication(array(
                                                'user_id' => $u->id,
                                                'manager_id' => $this->manager->id,
                                                'created' => date('Y-m-d H:i:s'),
                                                'type' => 'sms',
                                                'content' => '',
                                            ));
                                        }
                                        
                                        if (!empty($zvonobot) && !empty($yuk_text))
                                        {
                                            $this->communications->add_communication(array(
                                                'user_id' => $u->id,
                                                'manager_id' => $this->manager->id,
                                                'created' => date('Y-m-d H:i:s'),
                                                'type' => 'zvonobot',
                                                'content' => 'mailing',
                                            ));
                                        }
                                    }
                                    
                                }
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($contracts);echo '</pre><hr />';                        
                        }
                    }
                }


                if (!empty($mkk_numbers) || !empty($yuk_numbers))
                {

                    if (!empty($sms))
                    {
                        if (!empty($mkk_numbers) && !empty($mkk_text))
                            $this->sms_send($mkk_numbers, $mkk_text, 0);
                        if (!empty($yuk_numbers) && !empty($yuk_text))
                            $this->sms_send($yuk_numbers, $yuk_text, 1);
                    }
                
                    if (!empty($zvonobot))
                    {

                        if (!empty($mkk_numbers) && !empty($mkk_text))
                            $this->zvonobot_call($mkk_numbers, $mkk_text, 0);
                        if (!empty($yuk_numbers) && !empty($yuk_text))
                            $this->zvonobot_call($yuk_numbers, $yuk_text, 1);
                    }

                }

                $this->design->assign('success', 1);
                
            }

            
        }
        
        
        if ($this->request->get('action') == 'calc')
        {
            $mkk_numbers = array();
            $yuk_numbers = array();
            $collectors = $this->request->get('collectors');
            if (!empty($collectors))
            {
                if ($contracts = $this->contracts->get_contracts(array('collection_manager_id' => $collectors, 'status' => array(4,7))))
                {
                    foreach ($contracts as $c)
                    {
                        if (empty($c->sold))
                            $mkk_numbers[$c->user_id] = '';
                        else
                            $yuk_numbers[$c->user_id] = '';
                    }
                
                    if ($users = $this->users->get_users(array('id' => array_filter(array_merge(array_keys($mkk_numbers), array_keys($yuk_numbers))))))
                    {
                        foreach ($users as $u)
                        {
                            if (isset($mkk_numbers[$u->id]))
                                $mkk_numbers[$u->id] = $u->phone_mobile;
                            if (isset($yuk_numbers[$u->id]))
                                $yuk_numbers[$u->id] = $u->phone_mobile;
                        }
                    }
                    
                }
            }
            
            header('Content-type: application/json');
            echo json_encode(array(
                'mkk' => count($mkk_numbers),
                'yuk' => count($yuk_numbers),
            ));
            exit;
        }
        
        
        $collectors = array();
        foreach ($this->managers->get_managers() as $m)
        {
            if ($m->role == 'collector' && empty($m->blocked))
            {
                if (!isset($collectors[$m->collection_status_id]))
                    $collectors[$m->collection_status_id] = array();

                if ($this->manager->role == 'team_collector')
                {
                    if (in_array($m->id, (array)$this->manager->team_id))
                        $collectors[$m->collection_status_id][] = $m;
                }
                else
                {
                    $collectors[$m->collection_status_id][] = $m;
                }
            }
        }
        $collectors = array_filter($collectors);
        
        $this->design->assign('collectors', $collectors);
/*
echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($collectors);echo '</pre><hr />';        
        $team_collectors = array();
        $managers = array();
        foreach ($this->managers->get_managers() as $m)
        {
            if ($m->role == 'team_collector')
            {
                $team_collectors[$m->id] = $m;
                $team_collectors[$m->id]->collectors = array();
            }
            $managers[$m->id] = $m;
        }
        foreach ($managers as $m)
        {
            if ($m->role == 'collector')
                $team_collectors[$m->team_id]->collectors[] = $m;
            
        }
        
        if ($this->manager->role == 'team_collector')
        {
            $team_collectors = array($this->manager->id => $team_collectors[$this->manager->id]);
        }
        
        $this->design->assign('team_collectors', $team_collectors);
*/        
        $collection_statuses = $this->contracts->get_collection_statuses();
        $this->design->assign('collection_statuses', $collection_statuses);

        return $this->design->fetch('collector_mailing.tpl');
    }
    
    private function sms_send($numbers, $text, $yuk = 0)
    {
        foreach ($numbers as $number)
        {
//echo 'SEND '.$number.'<br />';
            $this->sms->send($number, $text, $yuk);
        }        
    }
    
    private function zvonobot_call($numbers, $text, $yuk = 0)
    {

        $name = 'mailing_'.date('ymd');
    	$record_id = $this->zvonobot->create_record($name, $text, $yuk);
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($record_id['data']['id'], $record_id);echo '</pre><hr />';        
        foreach ($numbers as $number)
        {
//echo 'CALL '.$number.'-'.$record_id['data']['id'].'<br />';
            $resp = $this->zvonobot->call($number, $record_id['data']['id'], $yuk);

            $this->zvonobot->add_zvonobot(array(
                'user_id' => 0,
                'contract_id' => 0,
                'yuk' => $yuk,
                'zvonobot_id' => isset($resp['data'][0]['id']) ? $resp['data'][0]['id'] : null,
                'status' => isset($resp['data'][0]['status']) ? $resp['data'][0]['status'] : 'new',
                'body' => serialize($resp),
                'create_date' => date('Y-m-d H:i:s'),
                'phone' => $number,
            ));
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($resp);echo '</pre><hr />';        
        }
    }
    
}