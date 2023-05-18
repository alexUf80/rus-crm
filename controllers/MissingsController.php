<?php

ini_set('max_execution_time', 40);

class MissingsController extends Controller
{

    public function fetch() {
        if (!in_array('missings', $this->manager->permissions))
            return $this->design->fetch('403.tpl');

        if ($this->request->method('post'))
        {
            switch ($this->request->post('action', 'string')):

                case 'set_manager':
                    $this->set_manager_action();
                    break;

                case 'close_missing':
                    $this->close_missing_action();
                    break;

                case 'send_sms':
                    $this->send_sms_action();
                    break;
            endswitch;
        }

        $items_per_page = 20;

        $filter = array();

        $filter['missing'] = 300;

        if (in_array($this->manager->role, array('contact_center')))
        {
            $filter['missing_status'] = 0;
//            $filter['missing_manager_id'] = $this->manager->id;
        }

        if (!($sort = $this->request->get('sort', 'string'))) {
            $sort = 'id_desc';
        }
        $filter['sort'] = $sort;
        $this->design->assign('sort', $sort);

        if ($search = $this->request->get('search')) {
            $filter['search'] = array_filter($search);
            $this->design->assign('search', array_filter($search));
        }

        $current_page = $this->request->get('page', 'integer');
        $current_page = max(1, $current_page);
        $this->design->assign('current_page_num', $current_page);

        $clients_count = $this->users->count_users($filter);

        $pages_num = ceil($clients_count / $items_per_page);
        $this->design->assign('total_pages_num', $pages_num);
        $this->design->assign('total_orders_count', $clients_count);

        $filter['page'] = $current_page;
        $filter['limit'] = $items_per_page;

        $clients = $this->users->get_users($filter);

        foreach ($clients as $client) {
            $usersId[] = $client->id;
        }

        $calls = $this->mango->get_calls(array('user_id'=>$usersId));
        foreach ($clients as $client) {
            foreach ($calls as $call) {
                if($client->id == $call->id){
                    $client->dump = $call;
                    $client->dump->callDate = date('d-m-Y H:i:s', $call->create_time);
                }
            }
        }


        $clients = array_map(function($var) {
            if (!empty($var->stage_card))
            {
                $var->stages = 7;
                $var->last_stage_date = $var->card_added_date;
            }
            elseif (!empty($var->stage_files))
            {
                $var->stages = 6;
                $var->last_stage_date = $var->files_added_date;
            }
            elseif (!empty($var->stage_work))
            {
                $var->stages = 5;
                $var->last_stage_date = $var->work_added_date;
            }
            elseif (!empty($var->stage_address))
            {
                $var->stages = 4;
                $var->last_stage_date = $var->address_data_added_date;
            }
            elseif (!empty($var->stage_passport))
            {
                $var->stages = 3;
                $var->last_stage_date = $var->passport_date_added_date;
            }
            elseif (!empty($var->stage_personal))
            {
                $var->stages = 2;
                $var->last_stage_date = $var->stage_personal_date;
            }
            else
            {
                $var->stages = 1;
                $var->last_stage_date = $var->created;
            }

            return $var;
        }, $clients);

        $this->design->assign('clients', $clients);

        $statistic = new StdClass();

        $st_params = array(
            'date_from' => date('Y-m-d 00:00:00'),
            'date_to' => date('Y-m-d 20:00:00'),
            'missing_status' => 1,
        );
        $statistic->closed = $this->users->count_users($st_params);

        $cmplt_params = array(
            'date_from' => date('Y-m-d 00:00:00'),
            'date_to' => date('Y-m-d 23:59:59'),
            'missing_status' => 1,
            'completed' => 1
        );
        $statistic->completed = $this->users->count_users($cmplt_params);



        $this->design->assign('statistic', $statistic);

        $sms_templates = $this->sms->get_templates(array('id' => 9));
        $this->design->assign('sms_templates', $sms_templates);

        return $this->design->fetch('missings.tpl');
    }

    public function set_manager_action()
    {
        if ($user_id = $this->request->post('user_id', 'integer'))
        {
            if ($user = $this->users->get_user($user_id))
            {
                if (empty($user->missing_manager_id))
                {
                    $this->users->update_user($user_id, array(
                        'missing_manager_id' => $this->manager->id
                    ));

                    $this->json_output(array('success' => 1, 'manager_name' => $this->manager->name));
                }
                else
                {
                    $this->json_output(array('error' => 'Заявка уже принята'));
                }
            }
            else
            {
                $this->json_output(array('error' => 'UNDEFINED_USER'));
            }
        }
        else
        {
            $this->json_output(array('error' => 'EMPTY_USER_ID'));
        }

    }

    public function close_missing_action()
    {
        if ($user_id = $this->request->post('user_id', 'integer'))
        {
            if ($user = $this->users->get_user($user_id))
            {
                if (empty($user->missing_status))
                {
                    $this->users->update_user($user_id, array(
                        'missing_status' => 1
                    ));

                    $this->json_output(array('success' => 1));
                }
                else
                {
                    $this->json_output(array('error' => 'Заявка уже завершена'));
                }
            }
            else
            {
                $this->json_output(array('error' => 'UNDEFINED_USER'));
            }
        }
        else
        {
            $this->json_output(array('error' => 'EMPTY_USER_ID'));
        }


    }

    private function send_sms_action()
    {
        $user_id = $this->request->post('user_id');
        $user = $this->users->get_user((int)$user_id);
        $template = $this->sms->get_templates(array('id' => 9));

        $resp = $this->sms->send(
            $user->phone_mobile,
            $template[0]->template
        );

        $this->users->update_user($user_id, array(
            'missing_status' => 1
        ));
        
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($resp);echo '</pre><hr />';
        $this->json_output(array('success'=>true));
    }
}
