<?php

class ClientController extends Controller
{
    public function fetch()
    {
    	if ($this->request->method('post'))
        {
            $id = $this->request->post('id', 'integer');
            $action = $this->request->post('action', 'string');
            
            switch($action):
                
                case 'contactdata':
                    $this->contactdata_action();
                break;

                case 'services':
                    $this->action_services();
                break;
                
                case 'personal':
                    $this->action_personal();
                break;

                case 'addresses':
                    $this->addresses_action();
                    break;

                case 'passport':
                    $this->action_passport();
                break;
                
                case 'reg_address':
                    $this->reg_address_action();
                break;
                
                case 'fakt_address':
                    $this->fakt_address_action();
                break;
                
                case 'contacts':
                    $this->contacts_action();
                break;
                
                case 'work':
                    $this->work_action();
                break;
                
                case 'work_address':
                    $this->work_address_action();
                break;
                
                case 'socials':
                    $this->socials_action();
                break;
                
                case 'images':
                    $this->action_images();
                break;

                case 'blocked':
                    $this->action_blocked();
                    break;

                case 'edit_phone':
                    $this->action_edit_phone();
                    break;
                
                
            endswitch;
            
        }
        else
        {
            if (!($id = $this->request->get('id', 'integer')))
                return false;
            
            if (!($client = $this->users->get_user($id)))
                return false;

            /* сохраняем историю займов из 1с
            $credits_history = $this->soap1c->get_client_credits($client->UID);
            $this->users->save_loan_history($client->id, $credits_history);
            */
            
            $client = $this->users->get_user($id);
            

            $user_close_orders = $this->orders->get_orders(array(
                'user_id' => $client->id,
                'type' => 'base', 
                'status' => array(7)
            ));
            $client->have_crm_closed = !empty($user_close_orders);

            $regaddress = $this->Addresses->get_address($client->regaddress_id);
            $faktaddress = $this->Addresses->get_address($client->faktaddress_id);
            $this->design->assign('regaddress', $regaddress->adressfull);
            $this->design->assign('faktaddress', $faktaddress->adressfull);


            $documents = $this->documents->get_documents(array('user_id'=>$client->id));
            $this->design->assign('documents', $documents);

            $managers = array();
            foreach ($this->managers->get_managers() as $m)
                $managers[$m->id] = $m;
            $this->design->assign('managers', $managers);

            $files = $this->users->get_files(array('user_id'=>$id));
            $this->design->assign('files', $files);
    
            $comments = $this->comments->get_comments(array('user_id' => $client->id));
            foreach ($comments as $comment)
            {
                $comment->letter = mb_substr($managers[$comment->manager_id]->name, 0, 1);
            }
            $this->design->assign('comments', $comments);
            
            /* получаем комменты из 1С
            if ($comments_1c_response = $this->soap1c->get_comments($client->UID))
            {
                $comments_1c = array();
                if (!empty($comments_1c_response->Комментарии))
                {
                    foreach ($comments_1c_response->Комментарии as $comm)
                    {
                        $comment_1c_item = new StdClass();
                        
                        $comment_1c_item->created = date('Y-m-d H:i:s', strtotime($comm->Дата));
                        $comment_1c_item->text = $comm->Комментарий;
                        $comment_1c_item->block = $comm->Блок;
                        
                        $comments_1c[] = $comment_1c_item;
                    }
                }
                
                usort($comments_1c, function($a, $b){
                    return strtotime($b->created) - strtotime($a->created);
                });
                
                $this->design->assign('comments_1c', $comments_1c);
                
                $blacklist_comments = array();
                if (!empty($comments_1c_response->Комментарии))
                {
                    foreach ($comments_1c_response->Комментарии as $comm)
                    {
                        $blacklist_comment = new StdClass();
                        
                        $blacklist_comment->created = date('Y-m-d H:i:s', strtotime($comm->Дата));
                        $blacklist_comment->text = $comm->Комментарий;
                        $blacklist_comment->block = $comm->Блок;
                        
                        $blacklist_comments[] = $blacklist_comment;
                    }
                }
                
                usort($blacklist_comments, function($a, $b){
                    return strtotime($b->created) - strtotime($a->created);
                });
                
                $this->design->assign('blacklist_comments', $blacklist_comments);
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($comments_1c_response);echo '</pre><hr />';
            }
            */



            $scorings = array();
            foreach ($this->scorings->get_scorings(array('user_id'=>$client->id)) as $scoring)
            {
                if ($scoring->type == 'juicescore')
                    $scoring->body = unserialize($scoring->body);
                if ($scoring->type == 'scorista')
                    $scoring->body = json_decode($scoring->body);
                
                $scorings[$scoring->type] = $scoring;
            }
            $this->design->assign('scorings', $scorings);
            
            $client->stages = 0;
            $client->stages += $client->stage_personal;
            $client->stages += $client->stage_passport;
            $client->stages += $client->stage_address;
            $client->stages += $client->stage_work;
            $client->stages += $client->stage_files;
            $client->stages += $client->stage_card;
            
            $changelogs = $this->changelogs->get_changelogs(array('user_id'=>$client->id));
            foreach ($changelogs as $changelog)
            {
                $changelog->user = $client;
                if (!empty($changelog->manager_id) && !empty($managers[$changelog->manager_id]))
                    $changelog->manager = $managers[$changelog->manager_id];
            }
            $changelog_types = $this->changelogs->get_types();
            
            $this->design->assign('changelog_types', $changelog_types);
            $this->design->assign('changelogs', $changelogs);
            
            if ($client->orders = $this->orders->get_orders(array('user_id'=>$client->id)))
                foreach ($client->orders as $o)
                    if ($o->contract_id)
                        $o->contract = $this->contracts->get_contract($o->contract_id);
            
            $this->design->assign('client', $client);
        
            $order_statuses = $this->orders->get_statuses();
            $this->design->assign('order_statuses', $order_statuses);
            
            $cards = $this->cards->get_cards(array('user_id'=>$client->id));
            $this->design->assign('cards', $cards);

            //подсчет возраста

            try{
                $born = new DateTime($client->birth); // дата рождения
                $age = $born->diff(new DateTime)->format('%y');
            }
            catch (Exception $e)
            {

            }

            $wordAge = $this->num2word($age, array('год', 'года', 'лет'));
            $clientAge = $age . ' ' . $wordAge;

            $this->design->assign('client_age', $clientAge);

        }
        
        $scoring_types = array();
        foreach ($this->scorings->get_types(array('active'=>true)) as $type)
            $scoring_types[$type->name] = $type;
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($scoring_types);echo '</pre><hr />';
        $this->design->assign('scoring_types', $scoring_types);
        
            $risk_op = ['complaint' => 'Жалоба', 'bankrupt' => 'Банкрот', 'refusal' => 'Отказ от взаимодействия',
                'refusal_thrd' => 'Отказ от взаимодействия с 3 лицами', 'death' => 'Смерть', 'anticollectors' => 'Антиколлекторы', 'mls' => 'Находится в МЛС',
                'bankrupt_init' => 'Инициировано банкротство', 'fraud' => 'Мошенничество'];

            $user_risk_op = $this->UsersRisksOperations->get_record($client->id);

            if(!empty($user_risk_op)){
                $this->design->assign('risk_op', $risk_op);
                $this->design->assign('user_risk_op', $user_risk_op);
            }
        return $this->design->fetch('client.tpl');
    }

    
    private function action_services()
    {
        $user_id = $this->request->post('user_id', 'integer');
    	
        $client = new StdClass();
        $client->service_sms = (int)$this->request->post('service_sms');
        $client->service_insurance = (int)$this->request->post('service_insurance');
        $client->service_reason = (int)$this->request->post('service_reason');
        
        $services_error = array();
        
        if (empty($services_error))
        {
            $update = array(
                'service_sms' => $client->service_sms,
                'service_insurance' => $client->service_insurance,
                'service_reason' => $client->service_reason,
            );

            $old_user = $this->users->get_user($user_id);
            $old_values = array();
            foreach ($update as $key => $val)
                if ($old_user->$key != $update[$key])
                    $old_values[$key] = $old_user->$key;
            
            $log_update = array();
            foreach ($update as $k => $u)
                if (isset($old_values[$k]))
                    $log_update[$k] = $u;
            
            $this->changelogs->add_changelog(array(
                'manager_id' => $this->manager->id,
                'created' => date('Y-m-d H:i:s'),
                'type' => 'services',
                'old_values' => serialize($old_values),
                'new_values' => serialize($log_update),
                'order_id' => 0,
                'user_id' => $user_id,
            ));

            $this->users->update_user($user_id, $update);
        }
        
        $this->design->assign('services_error', $services_error);
        
        $client->id = $user_id;

        $this->design->assign('client', $client);
    }

    private function contactdata_action()
    {
        $user_id = $this->request->post('user_id', 'integer');
        
        $client = new StdClass();
        
        $client->email = trim($this->request->post('email'));
        $client->birth = trim($this->request->post('birth'));
        $client->birth_place = trim($this->request->post('birth_place'));
        $client->passport_serial = trim($this->request->post('passport_serial'));
        $client->passport_date = trim($this->request->post('passport_date'));
        $client->subdivision_code = trim($this->request->post('subdivision_code'));
        $client->passport_issued = trim($this->request->post('passport_issued'));
        
        $client->social = trim($this->request->post('social'));
        
        $contactdata_error = array();
        
        if (empty($client->email))
            $personal_error[] = 'empty_email';
        if (empty($client->birth))
            $personal_error[] = 'empty_birth';
        if (empty($client->birth_place))
            $personal_error[] = 'empty_birth_place';
        if (empty($client->passport_serial))
            $personal_error[] = 'empty_passport_serial';
        if (empty($client->passport_date))
            $personal_error[] = 'empty_passport_date';
        if (empty($client->subdivision_code))
            $personal_error[] = 'empty_subdivision_code';
        if (empty($client->passport_issued))
            $personal_error[] = 'empty_passport_issued';
        if (empty($client->social))
            $personal_error[] = 'empty_socials';
        
        
        if (empty($contactdata_error))
        {
            $update = array(
                'email' => $client->email,
                'birth' => $client->birth,
                'birth_place' => $client->birth_place,
                'passport_serial' => $client->passport_serial,
                'passport_date' => $client->passport_date,
                'subdivision_code' => $client->subdivision_code,
                'passport_issued' => $client->passport_issued,
                'social' => $client->social,
            );

            $old_user = $this->users->get_user($user_id);
            $old_values = array();
            foreach ($update as $key => $val)
                if ($old_user->$key != $update[$key])
                    $old_values[$key] = $old_user->$key;
            
            $log_update = array();
            foreach ($update as $k => $u)
                if (isset($old_values[$k]))
                    $log_update[$k] = $u;
            
            $this->changelogs->add_changelog(array(
                'manager_id' => $this->manager->id,
                'created' => date('Y-m-d H:i:s'),
                'type' => 'contactdata',
                'old_values' => serialize($old_values),
                'new_values' => serialize($log_update),
                'order_id' => 0,
                'user_id' => $user_id,
            ));

            $this->users->update_user($user_id, $update);
        }
        
        $this->design->assign('contactdata_error', $contactdata_error);
        
        $client->id = $user_id;
        $this->design->assign('client', $client);

    }


    private function action_personal()
    {
        $user_id = $this->request->post('user_id', 'integer');
        
        $client = new StdClass();
        $client->lastname = trim($this->request->post('lastname'));
        $client->firstname = trim($this->request->post('firstname'));
        $client->patronymic = trim($this->request->post('patronymic'));
        $client->gender = trim($this->request->post('gender'));
        $client->birth = trim($this->request->post('birth'));
        $client->birth_place = trim($this->request->post('birth_place'));
        
        $personal_error = array();
        
        if (empty($client->lastname))
            $personal_error[] = 'empty_lastname';
        if (empty($client->firstname))
            $personal_error[] = 'empty_firstname';
        if (empty($client->patronymic))
            $personal_error[] = 'empty_patronymic';
        if (empty($client->gender))
            $personal_error[] = 'empty_gender';
        if (empty($client->birth))
            $personal_error[] = 'empty_birth';
        if (empty($client->birth_place))
            $personal_error[] = 'empty_birth_place';
        
        if (empty($personal_error))
        {
            $update = array(
                'lastname' => $client->lastname,
                'firstname' => $client->firstname,
                'patronymic' => $client->patronymic,
                'gender' => $client->gender,
                'birth' => $client->birth,
                'birth_place' => $client->birth_place,
            );

            $old_user = $this->users->get_user($user_id);
            $old_values = array();
            foreach ($update as $key => $val)
                if ($old_user->$key != $update[$key])
                    $old_values[$key] = $old_user->$key;
            
            $log_update = array();
            foreach ($update as $k => $u)
                if (isset($old_values[$k]))
                    $log_update[$k] = $u;
            
            $this->changelogs->add_changelog(array(
                'manager_id' => $this->manager->id,
                'created' => date('Y-m-d H:i:s'),
                'type' => 'personal',
                'old_values' => serialize($old_values),
                'new_values' => serialize($log_update),
                'user_id' => $user_id,
            ));

            $this->users->update_user($user_id, $update);
        }
        
        $this->design->assign('personal_error', $personal_error);
        
        $client->id = $user_id;
        $this->design->assign('client', $client);
    }
    
    private function action_passport()
    {
        $user_id = $this->request->post('user_id', 'integer');
        
        $client = new StdClass();
        $client->passport_serial = trim($this->request->post('passport_serial'));
        $client->passport_date = trim($this->request->post('passport_date'));
        $client->subdivision_code = trim($this->request->post('subdivision_code'));
        $client->passport_issued = trim($this->request->post('passport_issued'));
        
        $passport_error = array();
        
        if (empty($client->passport_serial))
            $passport_error[] = 'empty_passport_serial';
        if (empty($client->passport_date))
            $passport_error[] = 'empty_passport_date';
        if (empty($client->subdivision_code))
            $passport_error[] = 'empty_subdivision_code';
        if (empty($client->passport_issued))
            $passport_error[] = 'empty_passport_issued';
        
        if (empty($passport_error))
        {
            $update = array(
                'passport_serial' => $client->passport_serial,
                'passport_date' => $client->passport_date,
                'subdivision_code' => $client->subdivision_code,
                'passport_issued' => $client->passport_issued
            );

            $old_user = $this->users->get_user($user_id);
            $old_values = array();
            foreach ($update as $key => $val)
                if ($old_user->$key != $update[$key])
                    $old_values[$key] = $old_user->$key;
            
            $log_update = array();
            foreach ($update as $k => $u)
                if (isset($old_values[$k]))
                    $log_update[$k] = $u;
            
            $this->changelogs->add_changelog(array(
                'manager_id' => $this->manager->id,
                'created' => date('Y-m-d H:i:s'),
                'type' => 'passport',
                'old_values' => serialize($old_values),
                'new_values' => serialize($log_update),
                'user_id' => $user_id,
            ));

            $this->users->update_user($user_id, $update);
        }
        
        $this->design->assign('passport_error', $passport_error);
        
        $client->id = $user_id;
        $this->design->assign('client', $client);
    }
    
    private function reg_address_action()
    {
        $user_id = $this->request->post('user_id', 'integer');
        
        $client = new StdClass();
        $client->Regregion = trim($this->request->post('Regregion'));
        $client->Regcity = trim($this->request->post('Regcity'));
        $client->Regstreet = trim($this->request->post('Regstreet'));
        $client->Reghousing = trim($this->request->post('Reghousing'));
        $client->Regbuilding = trim($this->request->post('Regbuilding'));
        $client->Regroom = trim($this->request->post('Regroom'));
    
        $regaddress_error = array();
    
        if (empty($client->Regregion))
            $regaddress_error[] = 'empty_regregion';
        if (empty($client->Regcity))
            $regaddress_error[] = 'empty_regcity';
        if (empty($client->Regstreet))
            $regaddress_error[] = 'empty_regstreet';
        if (empty($client->Reghousing))
            $regaddress_error[] = 'empty_reghousing';
        
        if (empty($regaddress_error))
        {
            $update = array(
                'Regregion' => $client->Regregion,
                'Regcity' => $client->Regcity,
                'Regstreet' => $client->Regstreet,
                'Reghousing' => $client->Reghousing,
                'Regbuilding' => $client->Regbuilding,
                'Regroom' => $client->Regroom,
            );

            $old_user = $this->users->get_user($user_id);
            $old_values = array();
            foreach ($update as $key => $val)
                if ($old_user->$key != $update[$key])
                    $old_values[$key] = $old_user->$key;
            
            $log_update = array();
            foreach ($update as $k => $u)
                if (isset($old_values[$k]))
                    $log_update[$k] = $u;
            
            $this->changelogs->add_changelog(array(
                'manager_id' => $this->manager->id,
                'created' => date('Y-m-d H:i:s'),
                'type' => 'regaddress',
                'old_values' => serialize($old_values),
                'new_values' => serialize($log_update),
                'user_id' => $user_id,
            ));

            $this->users->update_user($user_id, $update);
        }
        
        $this->design->assign('regaddress_error', $regaddress_error);
        
        $client->id = $user_id;
        $this->design->assign('client', $client);
    }
    
    private function fakt_address_action()
    {
        $user_id = $this->request->post('user_id', 'integer');
        
        $client = new StdClass();
        $client->Faktregion = trim($this->request->post('Faktregion'));
        $client->Faktcity = trim($this->request->post('Faktcity'));
        $client->Faktstreet = trim($this->request->post('Faktstreet'));
        $client->Fakthousing = trim($this->request->post('Fakthousing'));
        $client->Faktbuilding = trim($this->request->post('Faktbuilding'));
        $client->Faktroom = trim($this->request->post('Faktroom'));
    
        $faktaddress_error = array();
    
        if (empty($client->Faktregion))
            $faktaddress_error[] = 'empty_faktregion';
        if (empty($client->Faktcity))
            $faktaddress_error[] = 'empty_faktcity';
        if (empty($client->Faktstreet))
            $faktaddress_error[] = 'empty_faktstreet';
        if (empty($client->Fakthousing))
            $faktaddress_error[] = 'empty_fakthousing';
        
        if (empty($faktaddress_error))
        {
            $update = array(
                'Faktregion' => $client->Faktregion,
                'Faktcity' => $client->Faktcity,
                'Faktstreet' => $client->Faktstreet,
                'Fakthousing' => $client->Fakthousing,
                'Faktbuilding' => $client->Faktbuilding,
                'Faktroom' => $client->Faktroom,
            );

            $old_user = $this->users->get_user($user_id);
            $old_values = array();
            foreach ($update as $key => $val)
                if ($old_user->$key != $update[$key])
                    $old_values[$key] = $old_user->$key;
            
            $log_update = array();
            foreach ($update as $k => $u)
                if (isset($old_values[$k]))
                    $log_update[$k] = $u;
            
            $this->changelogs->add_changelog(array(
                'manager_id' => $this->manager->id,
                'created' => date('Y-m-d H:i:s'),
                'type' => 'faktaddress',
                'old_values' => serialize($old_values),
                'new_values' => serialize($log_update),
                'user_id' => $user_id,
            ));

            $this->users->update_user($user_id, $update);
        }
        
        $this->design->assign('faktaddress_error', $faktaddress_error);
        
        $client->id = $user_id;
        $this->design->assign('client', $client);
    }
    
    private function contacts_action()
    {
        $user_id = $this->request->post('user_id', 'integer');
        
        $client = new StdClass();
        $client->contact_person_name = trim($this->request->post('contact_person_name'));
        $client->contact_person_phone = trim($this->request->post('contact_person_phone'));
        $client->contact_person_relation = trim($this->request->post('contact_person_relation'));
        $client->contact_person2_name = trim($this->request->post('contact_person2_name'));
        $client->contact_person2_phone = trim($this->request->post('contact_person2_phone'));
        $client->contact_person2_relation = trim($this->request->post('contact_person2_relation'));
        
        $contacts_error = array();
        
        if (empty($client->contact_person_name))
            $contacts_error[] = 'empty_contact_person_name';
        if (empty($client->contact_person_phone))
            $contacts_error[] = 'empty_contact_person_phone';
        if (empty($client->contact_person_relation))
            $contacts_error[] = 'empty_contact_person_relation';
        if (empty($client->contact_person2_name))
            $contacts_error[] = 'empty_contact_person2_name';
        if (empty($client->contact_person2_phone))
            $contacts_error[] = 'empty_contact_person2_phone';
        if (empty($client->contact_person2_relation))
            $contacts_error[] = 'empty_contact_person2_relation';
        
        if (empty($contacts_error))
        {
            $update = array(
                'contact_person_name' => $client->contact_person_name,
                'contact_person_phone' => $client->contact_person_phone,
                'contact_person_relation' => $client->contact_person_relation,
                'contact_person2_name' => $client->contact_person2_name,
                'contact_person2_phone' => $client->contact_person2_phone,
                'contact_person2_relation' => $client->contact_person2_relation,
            );

            $old_user = $this->users->get_user($user_id);
            $old_values = array();
            foreach ($update as $key => $val)
                if ($old_user->$key != $update[$key])
                    $old_values[$key] = $old_user->$key;
            
            $log_update = array();
            foreach ($update as $k => $u)
                if (isset($old_values[$k]))
                    $log_update[$k] = $u;
            
            $this->changelogs->add_changelog(array(
                'manager_id' => $this->manager->id,
                'created' => date('Y-m-d H:i:s'),
                'type' => 'contacts',
                'old_values' => serialize($old_values),
                'new_values' => serialize($log_update),
                'user_id' => $user_id,
            ));

            $this->users->update_user($user_id, $update);
        }
        
        $this->design->assign('contacts_error', $contacts_error);
        
        $client->id = $user_id;
        $this->design->assign('client', $client);
    }
    
    private function work_action()
    {
        $user_id = $this->request->post('user_id', 'integer');
        
        $client = new StdClass();
        $client->workplace = trim($this->request->post('workplace'));
        $client->workaddress = trim($this->request->post('workaddress'));
        $client->workcomment = trim($this->request->post('workcomment'));
        $client->profession = trim($this->request->post('profession'));
        $client->workphone = trim($this->request->post('workphone'));
        $client->income = trim($this->request->post('income'));
        $client->expenses = trim($this->request->post('expenses'));
        $client->chief_name = trim($this->request->post('chief_name'));
        $client->chief_position = trim($this->request->post('chief_position'));
        $client->chief_phone = trim($this->request->post('chief_phone'));

        $work_error = array();
        
        if (empty($client->workplace))
            $work_error[] = 'empty_workplace';
        if (empty($client->profession))
            $work_error[] = 'empty_profession';
        if (empty($client->workphone))
            $work_error[] = 'empty_workphone';
        if (empty($client->income))
            $work_error[] = 'empty_income';
        if (empty($client->expenses))
            $work_error[] = 'empty_expenses';
        if (empty($client->chief_name))
            $work_error[] = 'empty_chief_name';
        if (empty($client->chief_phone))
            $work_error[] = 'empty_chief_phone';
        if (empty($client->chief_phone))
            $work_error[] = 'empty_chief_phone';
            
        if (empty($work_error))
        {
            $update = array(
                'workplace' => $client->workplace,
                'workaddress' => $client->workaddress,
                'workcomment' => $client->workcomment,
                'profession' => $client->profession,
                'workphone' => $client->workphone,
                'income' => $client->income,
                'expenses' => $client->expenses,
                'chief_name' => $client->chief_name,
                'chief_position' => $client->chief_position,
                'chief_phone' => $client->chief_phone,
            );

            $old_user = $this->users->get_user($user_id);
            $old_values = array();
            foreach ($update as $key => $val)
                if ($old_user->$key != $update[$key])
                    $old_values[$key] = $old_user->$key;
            
            $log_update = array();
            foreach ($update as $k => $u)
                if (isset($old_values[$k]))
                    $log_update[$k] = $u;
            
            $this->changelogs->add_changelog(array(
                'manager_id' => $this->manager->id,
                'created' => date('Y-m-d H:i:s'),
                'type' => 'workdata',
                'old_values' => serialize($old_values),
                'new_values' => serialize($log_update),
                'user_id' => $user_id,
            ));

            $this->users->update_user($user_id, $update);
        }
        
        $this->design->assign('work_error', $workdata_error);
        
        $client->id = $user_id;
        $this->design->assign('client', $client);
    }
    
    
    private function work_address_action()
    {
        $user_id = $this->request->post('user_id', 'integer');
        
        $client = new StdClass();
        $client->Workregion = trim($this->request->post('Workregion'));
        $client->Workcity = trim($this->request->post('Workcity'));
        $client->Workstreet = trim($this->request->post('Workstreet'));
        $client->Workhousing = trim($this->request->post('Workhousing'));
        $client->Workbuilding = trim($this->request->post('Workbuilding'));
        $client->Workroom = trim($this->request->post('Workroom'));
    
        $workaddress_error = array();
    
        if (empty($client->Workregion))
            $workaddress_error[] = 'empty_workregion';
        if (empty($client->Workcity))
            $workaddress_error[] = 'empty_workcity';
        if (empty($client->Workstreet))
            $workaddress_error[] = 'empty_workstreet';
        if (empty($client->Workhousing))
            $workaddress_error[] = 'empty_workhousing';
        
        if (empty($workaddress_error))
        {
            $update = array(
                'Workregion' => $client->Workregion,
                'Workcity' => $client->Workcity,
                'Workstreet' => $client->Workstreet,
                'Workhousing' => $client->Workhousing,
                'Workbuilding' => $client->Workbuilding,
                'Workroom' => $client->Workroom,
            );

            $old_user = $this->users->get_user($user_id);
            $old_values = array();
            foreach ($update as $key => $val)
                if ($old_user->$key != $update[$key])
                    $old_values[$key] = $old_user->$key;
            
            $log_update = array();
            foreach ($update as $k => $u)
                if (isset($old_values[$k]))
                    $log_update[$k] = $u;
            
            $this->changelogs->add_changelog(array(
                'manager_id' => $this->manager->id,
                'created' => date('Y-m-d H:i:s'),
                'type' => 'workaddress',
                'old_values' => serialize($old_values),
                'new_values' => serialize($log_update),
                'user_id' => $user_id,
            ));

            $this->users->update_user($user_id, $update);
        }
        
        $this->design->assign('workaddress_error', $workaddress_error);
        
        $client->id = $user_id;
        $this->design->assign('client', $client);
    }
    
    private function socials_action()
    {
        $user_id = $this->request->post('user_id', 'integer');
    	
        $client = new StdClass();
        $client->social_fb = trim($this->request->post('social_fb'));
        $client->social_inst = trim($this->request->post('social_inst'));
        $client->social_vk = trim($this->request->post('social_vk'));
        $client->social_ok = trim($this->request->post('social_ok'));
        
        $socials_error = array();
        
        if (empty($socials_error))
        {
            $update = array(
                'social_fb' => $client->social_fb,
                'social_inst' => $client->social_inst,
                'social_vk' => $client->social_vk,
                'social_ok' => $client->social_ok,
            );

            $old_user = $this->users->get_user($user_id);
            $old_values = array();
            foreach ($update as $key => $val)
                if ($old_user->$key != $update[$key])
                    $old_values[$key] = $old_user->$key;
            
            $log_update = array();
            foreach ($update as $k => $u)
                if (isset($old_values[$k]))
                    $log_update[$k] = $u;
            
            $this->changelogs->add_changelog(array(
                'manager_id' => $this->manager->id,
                'created' => date('Y-m-d H:i:s'),
                'type' => 'socials',
                'old_values' => serialize($old_values),
                'new_values' => serialize($log_update),
                'user_id' => $user_id,
            ));

            $this->users->update_user($user_id, $update);
        }
        
        $this->design->assign('socials_error', $socials_error);
        
        $client->id = $user_id;
        $this->design->assign('client', $client);
    }
    
    
    private function action_images()
    {
        $user_id = $this->request->post('user_id', 'integer');
        
        $statuses = $this->request->post('status');
        foreach ($statuses as $file_id => $status)
        {
            $update = array(
                'status' => $status,
                'id' => $file_id
            );
            $old_files = $this->users->get_file($file_id);
            $old_values = array();
            foreach ($update as $key => $val)
                $old_values[$key] = $old_files->$key;
            if ($old_values['status'] != $update['status'])
            {
                $this->changelogs->add_changelog(array(
                    'manager_id' => $this->manager->id,
                    'created' => date('Y-m-d H:i:s'),
                    'type' => 'images',
                    'old_values' => serialize($old_values),
                    'new_values' => serialize($update),
                    'user_id' => $user_id,
                    'file_id' => $file_id,
                ));
            }
        
            $this->users->update_file($file_id, array('status' => $status));
        }
                
        $client = new StdClass();
        $client->id = $user_id;
        $this->design->assign('client', $client);
        
        $files = $this->users->get_files(array('user_id'=>$user_id));
        $this->design->assign('files', $files);
    }

    private function action_blocked()
    {
        $user_id = $this->request->post('user_id', 'integer');

        $client = new StdClass();
        $client->blocked = (int)$this->request->post('blocked');

        $update = array(
            'blocked' => $client->blocked,
        );

        $old_user = $this->users->get_user($user_id);
        $old_values = array();
        foreach ($update as $key => $val)
            if ($old_user->$key != $update[$key])
                $old_values[$key] = $old_user->$key;

        $log_update = array();
        foreach ($update as $k => $u)
            if (isset($old_values[$k]))
                $log_update[$k] = $u;

        $this->changelogs->add_changelog(array(
            'manager_id' => $this->manager->id,
            'created' => date('Y-m-d H:i:s'),
            'type' => 'services',
            'old_values' => serialize($old_values),
            'new_values' => serialize($log_update),
            'order_id' => 0,
            'user_id' => $user_id,
        ));

        $this->users->update_user($user_id, $update);

        $client->id = $user_id;

        $this->design->assign('client', $client);
    }

    private function num2word($num, $words)
    {
        $num = $num % 100;
        if ($num > 19) {
            $num = $num % 10;
        }
        switch ($num) {
            case 1: {
                return($words[0]);
            }
            case 2: case 3: case 4: {
                return($words[1]);
            }
            default: {
                return($words[2]);
            }
        }
    }

    private function addresses_action()
    {
        $order_id = $this->request->post('order_id', 'integer');
        $user_id = $this->request->post('user_id', 'integer');

        $order = new StdClass();
        $order->Regindex = trim($this->request->post('Regindex'));
        $order->Regregion = trim($this->request->post('Regregion'));
        $order->Regregion_shorttype = trim($this->request->post('Regregion_shorttype'));
        $order->Regdistrict = trim($this->request->post('Regdistrict'));
        $order->Regdistrict_shorttype = trim($this->request->post('Regdistrict_shorttype'));
        $order->Reglocality = trim($this->request->post('Reglocality'));
        $order->Reglocality_shorttype = trim($this->request->post('Reglocality_shorttype'));
        $order->Regcity = trim($this->request->post('Regcity'));
        $order->Regcity_shorttype = trim($this->request->post('Regcity_shorttype'));
        $order->Regstreet = trim($this->request->post('Regstreet'));
        $order->Regstreet_shorttype = trim($this->request->post('Regstreet_shorttype'));
        $order->Reghousing = trim($this->request->post('Reghousing'));
        $order->Regbuilding = trim($this->request->post('Regbuilding'));
        $order->Regroom = trim($this->request->post('Regroom'));

        $order->Faktindex = trim($this->request->post('Faktindex'));
        $order->Faktregion = trim($this->request->post('Faktregion'));
        $order->Faktregion_shorttype = trim($this->request->post('Faktregion_shorttype'));
        $order->Faktdistrict = trim($this->request->post('Faktdistrict'));
        $order->Faktdistrict_shorttype = trim($this->request->post('Faktdistrict_shorttype'));
        $order->Faktlocality = trim($this->request->post('Faktlocality'));
        $order->Faktlocality_shorttype = trim($this->request->post('Faktlocality_shorttype'));
        $order->Faktcity = trim($this->request->post('Faktcity'));
        $order->Faktcity_shorttype = trim($this->request->post('Faktcity_shorttype'));
        $order->Faktstreet = trim($this->request->post('Faktstreet'));
        $order->Faktstreet_shorttype = trim($this->request->post('Faktstreet_shorttype'));
        $order->Fakthousing = trim($this->request->post('Fakthousing'));
        $order->Faktbuilding = trim($this->request->post('Faktbuilding'));
        $order->Faktroom = trim($this->request->post('Faktroom'));

        $addresses_error = array();

        if (empty($order->Regregion))
            $addresses_error[] = 'empty_regregion';

        if (empty($order->Faktregion))
            $addresses_error[] = 'empty_faktregion';

        if (empty($addresses_error)) {
            $update = array(
                'Regregion' => $order->Regregion,
                'Regregion_shorttype' => $order->Regregion_shorttype,
                'Regcity' => $order->Regcity,
                'Regcity_shorttype' => $order->Regcity_shorttype,
                'Regdistrict' => $order->Regdistrict,
                'Regdistrict_shorttype' => $order->Regdistrict_shorttype,
                'Reglocality' => $order->Reglocality,
                'Reglocality_shorttype' => $order->Reglocality_shorttype,
                'Regstreet' => $order->Regstreet,
                'Regstreet_shorttype' => $order->Regstreet_shorttype,
                'Reghousing' => $order->Reghousing,
                'Regbuilding' => $order->Regbuilding,
                'Regroom' => $order->Regroom,
                'Regindex' => $order->Regindex,
            );

            $old_user = $this->users->get_user($user_id);
            $old_values = array();
            foreach ($update as $key => $val)
                if ($old_user->$key != $update[$key])
                    $old_values[$key] = $old_user->$key;

            $log_update = array();
            foreach ($update as $k => $u)
                if (isset($old_values[$k]))
                    $log_update[$k] = $u;

            $this->changelogs->add_changelog(array(
                'manager_id' => $this->manager->id,
                'created' => date('Y-m-d H:i:s'),
                'type' => 'regaddress',
                'old_values' => serialize($old_values),
                'new_values' => serialize($log_update),
                'order_id' => $order_id,
                'user_id' => $user_id,
            ));

            $this->users->update_user($user_id, $update);

            $update = array(
                'Faktregion' => $order->Faktregion,
                'Faktregion_shorttype' => $order->Faktregion_shorttype,
                'Faktcity' => $order->Faktcity,
                'Faktcity_shorttype' => $order->Faktcity_shorttype,
                'Faktdistrict' => $order->Faktdistrict,
                'Faktdistrict_shorttype' => $order->Faktdistrict_shorttype,
                'Faktlocality' => $order->Faktlocality,
                'Faktlocality_shorttype' => $order->Faktlocality_shorttype,
                'Faktstreet' => $order->Faktstreet,
                'Faktstreet_shorttype' => $order->Faktstreet_shorttype,
                'Fakthousing' => $order->Fakthousing,
                'Faktbuilding' => $order->Faktbuilding,
                'Faktroom' => $order->Faktroom,
                'Faktindex' => $order->Faktindex,
            );

            $old_user = $this->users->get_user($user_id);
            $old_values = array();
            foreach ($update as $key => $val)
                if ($old_user->$key != $update[$key])
                    $old_values[$key] = $old_user->$key;

            $log_update = array();
            foreach ($update as $k => $u)
                if (isset($old_values[$k]))
                    $log_update[$k] = $u;

            $this->changelogs->add_changelog(array(
                'manager_id' => $this->manager->id,
                'created' => date('Y-m-d H:i:s'),
                'type' => 'faktaddress',
                'old_values' => serialize($old_values),
                'new_values' => serialize($log_update),
                'order_id' => $order_id,
                'user_id' => $user_id,
            ));

            $this->users->update_user($user_id, $update);

        }

        $this->design->assign('addresses_error', $addresses_error);

        $order->order_id = $order_id;
        $order->user_id = $user_id;

        $isset_order = $this->orders->get_order((int)$order_id);

        $order->status = $isset_order->status;
        $order->manager_id = $isset_order->manager_id;

//        $this->soap1c->update_fields($update, '', $isset_order->id_1c);

        $this->design->assign('order', $order);

    }

    private function action_edit_phone()
    {
        $phone   = $this->request->post('phone');
        $user_id = $this->request->post('user');

        $this->users->update_user($user_id, ['phone_mobile' => $phone]);
        exit;
    }
    
}