<?php

error_reporting(0);
ini_set('display_errors', 'On');

class OrderController extends Controller
{
    public function fetch()
    {
        if ($this->request->method('post')) {
            $action = $this->request->post('action', 'string');

            switch ($action):

                case 'change_manager':
                    $this->change_manager_action();
                    break;

                case 'fio':
                    $this->fio_action();
                    break;

                case 'contactdata':
                    $this->contactdata_action();
                    break;

                case 'add_contact':
                    $this->action_add_contact();
                    break;

                case 'delete_contact':
                    $this->action_delete_contact();
                    break;

                case 'edit_contact':
                    $this->action_edit_contact();
                    break;

                case 'get_contact':
                    $this->action_get_contact();
                    break;

                case 'contacts':
                    $this->contacts_action();
                    break;

                case 'addresses':
                    $this->addresses_action();
                    break;

                case 'work':
                    $this->work_action();
                    break;

                case 'amount':
                    $this->action_amount();
                    break;

                case 'cards':
                    $this->action_cards();
                    break;

                case 'contact_status':
                    $response = $this->action_contact_status();
                    $this->json_output($response);
                    break;

                case 'contactperson_status':
                    $response = $this->action_contactperson_status();
                    $this->json_output($response);
                    break;

                case 'status':
                    $status = $this->request->post('status', 'integer');
                    $response = $this->status_action($status);
                    $this->json_output($response);
                    break;

                // принять заявку
                case 'accept_order':
                    $response = $this->accept_order_action();
                    $this->json_output($response);
                    break;

                // одобрить заявку
                case 'approve_order':
                    $response = $this->approve_order_action();
                    $this->json_output($response);
                    break;

                // одобрить заявку
                case 'autoretry_accept':
                    $response = $this->autoretry_accept_action();
                    $this->json_output($response);
                    break;

                // отказать в заявке
                case 'reject_order':
                    $response = $this->reject_order_action();
                    $this->json_output($response);
                    break;

                // подтвердить контракт
                case 'confirm_contract':
                    $response = $this->confirm_contract_action();
                    $this->json_output($response);
                    break;

                case 'add_comment':
                    $this->action_add_comment();
                    break;

                case 'add_canicules':
                    $this->action_add_canicules();
                    break;

                case 'close_contract':
                    $this->action_close_contract();
                    break;

                case 'repay':
                    $this->action_repay();
                    break;


                case 'personal':
                    $this->action_personal();
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

                case 'workdata':
                    $this->workdata_action();
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

                case 'services':
                    $this->action_services();
                    break;

                case 'workout':
                    $this->action_workout();
                    break;

                case 'add_delete_blacklist':
                    $this->add_delete_blacklist();
                    break;

                case 'check_blacklist':
                    $this->check_blacklist();
                    break;

                case 'return_insure':
                    $this->action_return_insure();
                    break;

                case 'return_bud_v_kurse':
                    $this->action_return_bud_v_kurse();
                    break;

                case 'return_reject_reason':
                    $this->action_return_reject_reason();
                    break;

                case 'change_risk_lvl':
                    $this->action_change_risk_lvl();
                    break;

                case 'check_risk_lvl':
                    $this->action_check_risk_lvl();
                    break;

                case 'change_risk_operation':
                    $this->action_change_risk_operation();
                    break;

                case 'check_risk_operation':
                    $this->action_check_risk_operation();
                    break;

                case 'send_sms':
                    $this->send_sms_action();
                    break;

                case 'send_casual_sms':
                    $this->send_casual_sms_action();
                    break;

                case 'send_template_sms':
                    $this->action_send_template_sms();
                    break;

                case 'add_receipt':
                    return $this->action_add_receipt();
                    break;

                case 'restruct':
                    return $this->action_restruct();
                    break;

                case 'reccurent':
                    return $this->action_reccurent();
                    break;

                case 'confirm_asp':
                    return $this->action_confirm_asp();
                    break;

                case 'editLoanProfit':
                    return $this->action_editLoanProfit();
                    break;
                
                case 'editServices':
                    return $this->action_editServices();
                    break;

                case 'editServicesClosed':
                    return $this->action_editServicesClosed();
                    break;

                case 'add_risk':
                    $this->action_add_risk();
                    break;

                case 'hide_phone_operation':
                    $this->action_hide_phone_operation();
                    break;
                
                case 'add_pril_1':
                    return $this->action_add_pril_1();
                    break;
                    
                case 'to_onec':
                    return $this->action_to_onec();
                    break;

            endswitch;

        } else {
            $managers = array();
            foreach ($this->managers->get_managers() as $m)
                $managers[$m->id] = $m;

            $scoring_types = $this->scorings->get_types();

            // $token = "222e191767518127bcf15cc4d2a23c131404fdf2";
            // $secret = "6b90de07e9974eba848ac174b3eed2829a35ec5e";
            // $regaddress = $this->Addresses->get_address(78929);
            // $dadata = new \Dadata\DadataClient($token, $secret);
            // $result = $dadata->clean("address", $regaddress->adressfull);
            //  echo'<pre>';print_r($result['timezone']);echo'</pre>';
            // $client_time = $result['timezone'];
            // $this->design->assign('client_time', $result['timezone']);
            // echo'<pre>';print_r($scoring_types);echo'</pre>';


//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($scoring_types);echo '</pre><hr />';
            $this->design->assign('scoring_types', $scoring_types);

            //     $query = $this->db->placehold("
            //     SELECT
            //         id
            //     FROM __addresses
            //     WHERE 1
            //     AND region LIKE '%Самарская%'
            // ");
            // $this->db->query($query);
            // $usersRegaddress_id = $this->db->results();
            // $ids = [];
            // foreach ($usersRegaddress_id as $userRegaddress_id) {
            //     $ids[] = $userRegaddress_id->id;
            // }
            //  $ids = implode(',', $ids);
            // echo '<pre>';print_r("
            // SELECT
            //     id
            //     FROM __users
            //     WHERE regaddress_id iN(" . $ids . ")
            // ");echo'</pre>';
            //ntcn


            if ($order_id = $this->request->get('id', 'integer')) {
                if ($order = $this->orders->get_order($order_id)) {
                    $client = $this->users->get_user($order->user_id);

                    $client_time_zon = $client->time_zone;
                    //$client_time_zon = 'UTC-5';
                    //echo '<pre>';print_r($client_time_zon);echo'</pre>';
                    $client_time_zon = mb_substr($client_time_zon, 3);
                    //echo '<pre>';print_r($client_time_zon);echo'</pre>';
                    $client_time_zon = (int)$client_time_zon;
                    //echo '<pre>';print_r($client_time_zon);echo'</pre>';
                    $client_time_zon = $client_time_zon * 60 * 60;

                    //$time = new DateTimeZone('UTC');

                    $tz = 'UTC';
                    $timestamp = time();
                    $dt = new DateTime("now", new DateTimeZone($tz)); //first argument "must" be a string
                    $dt->setTimestamp($timestamp); //adjust the object to correct timestamp
                    //echo $dt->format('d.m.Y, H:i:s');
                    //echo '<pre>';print_r($dt->format('d.m.Y, H:i:s'));echo'</pre>';

                    $time = strtotime($dt->format('d.m.Y, H:i:s'));
                    $time = $time + $client_time_zon;
                    $time_zone = date("d.m.Y H:i:s", $time);
                    //echo '<pre>';print_r($dt->format($date));echo'</pre>';
                    //echo $date;

                    if(!$client->time_zone)
                    $time_zone = "Нет данных";

                    $this->design->assign('client_time', $client->time_zone);
                    $this->design->assign('client_time_zone', $time_zone);

                    $receipts1 = ReceiptsORM::where('order_id', $order_id)->get();
                    $rec = [];
                    foreach ($receipts1 as $receipt) {

                        $receipt->url = $this->Best2pay->checkReceiptUrl($receipt->receipt_url);
                    }
                    $this->design->assign('receipts1', $receipts1);

                    $regaddress = $this->Addresses->get_address($client->regaddress_id);
                    $faktaddress = $this->Addresses->get_address($client->faktaddress_id);
                    $this->design->assign('regaddress', $regaddress);
                    $this->design->assign('faktaddress', $faktaddress);

                    if (!empty($order->promocode_id)) {
                        $promocode = PromocodesORM::find($order->promocode_id);
                        $this->design->assign('promocode', $promocode);
                    }

                    $contacts = $this->Contactpersons->get_contactpersons(['user_id' => $order->user_id]);
                    $this->design->assign('contacts', $contacts);

                    $client = $this->users->get_user($order->user_id);
                    $this->design->assign('client', $client);

                    $work = worksORM::where('user_id', '=', $order->user_id)->get();

                    $this->design->assign('work', $work[0]);

                    //echo '<pre>';print_r($work);echo'</pre>';

                    //подсчет возраста
                    try {

                        $born = new DateTime($client->birth); // дата рождения
                        $age = $born->diff(new DateTime)->format('%y');

                        $wordAge = $this->num2word($age, array('год', 'года', 'лет'));
                        $clientAge = $age . ' ' . $wordAge;

                        $this->design->assign('client_age', $clientAge);
                    } catch (Exception $e) {

                    }


                    $canicules = $this->canicules->get_canicules(array('user_id' => $client->id));
                    $this->design->assign('canicules', $canicules);

                    $communications = $this->communications->get_communications(array('user_id' => $client->id));
                    $this->design->assign('communications', $communications);
                    $this->design->assign('order', $order);

                    $comments = $this->comments->get_comments(array('user_id' => $order->user_id, 'official' => $this->settings->display_only_official_comments));
                    foreach ($comments as $comment) {
                        $comment->letter = mb_substr($managers[$comment->manager_id]->name, 0, 1);
                    }
                    $this->design->assign('comments', $comments);

                    $files = $this->users->get_files(array('user_id' => $order->user_id));
                    $this->design->assign('files', $files);

                    $documents = array();
                    $availability_SOGLASIE_OPD = false;
                    $availability_ANKETA_PEP = false;
                    foreach ($this->documents->get_documents(array('user_id' => $order->user_id)) as $doc) {
                        if (empty($doc->order_id) || $doc->order_id == $order_id){
                            $documents[] = $doc;

                            if($doc->type == 'SOGLASIE_OPD')
                                $availability_SOGLASIE_OPD = true;
                            if($doc->type == 'ANKETA_PEP')
                                $availability_ANKETA_PEP = true;
                            if($doc->type == 'ANKETA_PEP_KD')
                                $availability_ANKETA_PEP = true;
                        }
                    }

                    if(!$availability_SOGLASIE_OPD){
                        foreach ($this->documents->get_documents(array('user_id' => $order->user_id)) as $doc) {
                            if($doc->type == 'SOGLASIE_OPD'){
                                $documents[] = $doc;
                                break;
                            }
                        }
                    }
                    if(!$availability_ANKETA_PEP){
                        foreach ($this->documents->get_documents(array('user_id' => $order->user_id)) as $doc) {
                            if($doc->type == 'ANKETA_PEP'){
                                $documents[] = $doc;
                                break;
                            }
                        }
                    }

                    $this->design->assign('documents', $documents);
                    $this->design->assign('receipts', $this->receipts->get_receipts($order->user_id));


                    $user_close_orders = $this->orders->get_orders(array(
                        'user_id' => $order->user_id,
                        'type' => 'base',
                        'status' => array(7)
                    ));
                    $order->have_crm_closed = !empty($user_close_orders);


                    if (!empty($order->contract_id)) {
                        $contract = $this->contracts->get_contract((int)$order->contract_id);
                        $this->design->assign('contract', $contract);

                        $filter = [];
                        $filter['from'] = date('Y-m-d H:i:s');
                        $filter['to'] = date('Y-m-d H:i:s');
                        $filter['order_id'] = $contract->order_id;
                        $count_canicules = $this->canicules->count_canicules($filter);
                        $this->design->assign('count_canicules', $count_canicules);

                        $code = $this->helpers->c2o_encode($contract->id);
                        $short_link = $this->config->main_domain . '/p/' . $code;
                        $this->design->assign('short_link', $short_link);

                        $date1 = new DateTime(date('Y-m-d', strtotime($contract->return_date)));
                        $date2 = new DateTime(date('Y-m-d'));

                        $diff = $date2->diff($date1);
                        $contract->delay = $diff->days;

                    }

                    $receipts= [];
                    if ($contract_operations = $this->operations->get_operations(array('order_id' => $order->order_id, 'sort' => 'created_asc'))) {
                        foreach ($contract_operations as $contract_operation) {
                            if (!empty($contract_operation->transaction_id))
                                $contract_operation->transaction = $this->transactions->get_transaction($contract_operation->transaction_id);

                            if ($contract_operation->type == 'P2P') {
                                $contract_operation->p2pOperation = P2pOperationORM::where('contract_id', $contract_operation->contract_id)
                                    ->where('status', 'APPROVED')
                                    ->first()
                                    ->toArray();
                            }

                            if ($contract_operation->type != 'RECURRENT') {
                                $transaction = $this->transactions->get_transaction($contract_operation->transaction_id);
                                $xml = simplexml_load_string($transaction->callback_response);
                                $ofd_link = (string)$xml->ofd_link;
                                
                                if($ofd_link){
                                    $ofd_link = str_replace("%3A", ":", $ofd_link);
                                    $ofd_link = str_replace("%2F", "/", $ofd_link);
                                    $ofd_link = str_replace("%3D", "=", $ofd_link);
                                    $ofd_link = str_replace("%3F", "?", $ofd_link);
                                    $ofd_link = str_replace("%26", "&", $ofd_link);
                                    $receipts[] = (object) array('receipt_url' => $ofd_link, 'created' => $transaction->created);
                                }
                            }

                            if ($contract->status == 3 && $contract_operation->type == 'INSURANCE' && date('Y-m-d', strtotime($contract->close_date)) == date('Y-m-d', strtotime($contract_operation->created))) {
                                $contract_close_insurance = $contract_operation;

                                $transaction_close_insurance = $this->transactions->get_transaction($contract_operation->transaction_id);
                                $contract_close_insurance_state = strripos($transaction_close_insurance->callback_response, '<order_state>COMPLETED</order_state>');
                            }
                                
                        }
                    }

                    if (isset($contract_close_insurance)) {
                        $this->design->assign('contract_close_insurance', $contract_close_insurance);
                        $this->design->assign('contract_close_insurance_state', $contract_close_insurance_state);
                    }
                    $this->design->assign('contract_operations', $contract_operations);
                    $this->design->assign('receipts', $receipts);

                    if (!empty($contract->insurance_id)) {
                        $contract_insurance = $this->insurances->get_insurance($contract->insurance_id);
                        $this->design->assign('contract_insurance', $contract_insurance);
                    }

                    $need_update_scorings = 0;
                    $inactive_run_scorings = 0;
                    $scorings = array();
                    if ($result_scorings = $this->scorings->get_scorings(array('order_id' => $order->order_id))) {
                        foreach ($result_scorings as $scoring) {
                            if ($scoring->type == 'juicescore') {
                                $scoring->body = unserialize($scoring->body);
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($scoring->body);echo '</pre><hr />';
                            }

                            if ($scoring->type == 'efrsb') {
                                $scoring->body = @unserialize($scoring->body);
                            }

                            if ($scoring->type == 'fssp') {
                                $fsspScore = json_decode($scoring->body, true);
                                $this->design->assign('fsspScore', $fsspScore);
                            }

                            if ($scoring->type == 'scorista') {
                                $scoring->body = json_decode($scoring->body);
                                if (!empty($scoring->body->equifaxCH))
                                    $scoring->body->equifaxCH = iconv('cp1251', 'utf8', base64_decode($scoring->body->equifaxCH));
                            }
                            if ($scoring->type == 'fssp') {
                                $scoring->body = @unserialize($scoring->body);
                                $scoring->found_46 = 0;
                                $scoring->found_47 = 0;
                                if (!empty($scoring->body->result[0]->result)) {
                                    foreach ($scoring->body->result[0]->result as $result) {
                                        if (!empty($result->ip_end)) {
                                            $ip_end = array_map('trim', explode(',', $result->ip_end));
                                            if (in_array(46, $ip_end))
                                                $scoring->found_46 = 1;
                                            if (in_array(47, $ip_end))
                                                $scoring->found_47 = 1;
                                        }
                                    }
                                }
                            }
                            if ($scoring->type == 'nbki') {
                                $scoring->body = unserialize($scoring->body);
                                if (isset($scoring->body['number_of_active'][0])) {
                                    $number_of_active = $scoring->body['number_of_active'][0];
                                    $this->design->assign('number_of_active', $number_of_active);
                                }
                                if (isset($scoring->body['open_to_close_ratio'][0])) {
                                    $open_to_close_ratio = $scoring->body['open_to_close_ratio'][0];
                                    $this->design->assign('open_to_close_ratio', $open_to_close_ratio);
                                }
                                if (isset($scoring->body['report_url'])) {
                                    $report_url = $scoring->body['report_url'];
                                    $this->design->assign('report_url', $report_url);
                                }
                            }


                            $scorings[$scoring->type] = $scoring;

                            if ($scoring->status == 'new' || $scoring->status == 'process' || $scoring->status == 'repeat') {
                                $need_update_scorings = 1;
                                if (isset($scoring_types[$scoring->type]->type) && $scoring_types[$scoring->type]->type == 'first')
                                    $inactive_run_scorings = 1;
                            }
                        }
                        /*
                        $scorings['efsrb'] = (object)array(
                            'success' => 1,
                            'string_result' => 'Проверка пройдена',
                            'status' => 'completed',
                            'created' => $scoring->created
                        );
                        */
                    }

                    //echo'<pre>';print_r($scorings);echo'</pre>';

                    $this->design->assign('scorings', $scorings);
                    $this->design->assign('need_update_scorings', $need_update_scorings);
                    $this->design->assign('inactive_run_scorings', $inactive_run_scorings);

//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($scorings, $scoring_types);echo '</pre><hr />';

                    $user = $this->users->get_user((int)$order->user_id);
                    $changelogs = $this->changelogs->get_changelogs(array('order_id' => $order_id));
                    foreach ($changelogs as $changelog) {
                        $changelog->user = $user;
                        if (!empty($changelog->manager_id) && !empty($managers[$changelog->manager_id]))
                            $changelog->manager = $managers[$changelog->manager_id];
                    }
                    $changelog_types = $this->changelogs->get_types();

                    $this->design->assign('changelog_types', $changelog_types);
                    $this->design->assign('changelogs', $changelogs);

                    $eventlogs = $this->eventlogs->get_logs(array('order_id' => $order_id));
                    $this->design->assign('eventlogs', $eventlogs);

                    $events = $this->eventlogs->get_events();
                    $this->design->assign('events', $events);


                    if ($eventlogs) {
                        $html = '';
                        foreach ($eventlogs as $eventlog) {
                            $event = $events[$eventlog->event_id];
                            $event_created = $eventlog->created;
                            $manager_name = $managers[$eventlog->manager_id]->name;

                            $html = $html . "<tr><td>{$event_created}</td><td>{$event}</td><td>{$manager_name}</a></td></tr>";
                        }

                        $this->design->assign('html', "<table>$html</table>");
                    }


                    $cards = array();
                    foreach ($this->cards->get_cards(array('user_id' => $order->user_id)) as $card)
                        $cards[$card->id] = $card;
                    foreach ($cards as $card)
                        $card->duplicates = $this->cards->find_duplicates($order->user_id, $card->pan, $card->expdate);

                    $this->design->assign('cards', $cards);


                    // получаем комменты из 1С
                    $orders = $this->orders->get_orders(array('user_id' => $order->user_id));

                    foreach ($orders as $orderr) {

                        if (!empty($orderr->contract_id))
                            $orderr->contract = $this->contracts->get_contract($orderr->contract_id);

                        if (!empty($orderr->contract) && ($orderr->contract->close_date)) {
                            $dateBegin = DateTime::createFromFormat("Y-m-d H:i:s", $orderr->contract->inssuance_date); //дата получения
                            $dateClose = DateTime::createFromFormat("Y-m-d H:i:s", $orderr->contract->close_date); //дата закрытия
                            $interval = $dateBegin->diff($dateClose);

                            $wordDay = $this->num2word($interval->days, array('день', 'дня', 'дней'));

                            $orderr->contract->usage_time = $interval->days . ' ' . $wordDay;
                        }
                    }
                    $this->design->assign('orders', $orders);


                    if (in_array('looker_link', $this->manager->permissions)) {
                        $looker_link = $this->users->get_looker_link($order->user_id);
                        $this->design->assign('looker_link', $looker_link);
                    }

                } else {
                    return false;
                }
            }
        }

        $scoring_types = array();
        foreach ($this->scorings->get_types(array('active' => true)) as $type)
            $scoring_types[$type->name] = $type;
        $this->design->assign('scoring_types', $scoring_types);

        $reject_reasons = array();
        foreach ($this->reasons->get_reasons() as $r)
            $reject_reasons[$r->id] = $r;
        $this->design->assign('reject_reasons', $reject_reasons);

        $order_statuses = $this->orders->get_statuses();
        $this->design->assign('order_statuses', $order_statuses);

        $penalty_types = $this->penalties->get_types();
        $this->design->assign('penalty_types', $penalty_types);

        $risk_op = ['complaint' => 'Жалоба', 'bankrupt' => 'Банкрот', 'refusal' => 'Отказ от взаимодействия',
            'refusal_thrd' => 'Отказ от взаимодействия с 3 лицами', 'death' => 'Смерть', 'anticollectors' => 'Антиколлекторы', 'mls' => 'Находится в МЛС',
            'bankrupt_init' => 'Инициировано банкротство', 'fraud' => 'Мошенничество', 'canicule' => 'о кредитных каникулах', 'request_cb' => 'о запросе ЦБ, ФССП'];

        $user_risk_op = $this->UsersRisksOperations->get_record($order->user_id);

        if (!empty($user_risk_op)) {
            $this->design->assign('risk_op', $risk_op);
            $this->design->assign('user_risk_op', $user_risk_op);
        }

        $sms_templates = $this->sms->get_templates();
        $this->design->assign('sms_templates', $sms_templates);

        $pdn = $client->pdn;
        $this->design->assign('pdn', $pdn);


        $operations_refund_services_arrs = $this->RefundForServices->get_done($order->contract_id);

        $operations_refund_services = [];
        foreach ($operations_refund_services_arrs as $key => $operations_refund_services_arr) {
            $operations_arr = explode(',', $operations_refund_services_arr->operations_ids);
            $operations_refund_services = array_merge($operations_refund_services, $operations_arr);
        }

        $this->design->assign('operations_refund_services', $operations_refund_services);
        

        $operations_service_count = 0;
        $operations = $this->operations->get_operations(array('contract_id' => $order->contract_id));
        foreach ($operations as $operation) {
            if (in_array($operation->type, ['BUD_V_KURSE', 'INSURANCE', 'INSURANCE_BC'])) {
                $operations_service_count++;
            }
        }
        $this->design->assign('operations_not_refund_services', $operations_service_count - count($operations_refund_services));


        $body = $this->design->fetch('order.tpl');

        if ($this->request->get('ajax', 'integer')) {
            echo $body;
            exit;
        }

        if ($local_time = $this->request->post('value')) {

            $user_id = $this->request->post('user_id', 'integer');

            if ($local_time == 'msk') {
                $time = ['time_zone' => 0];
            } else {
                $time = ['time_zone' => $local_time];
            }

            $this->users->update_user($user_id, $time);
        }
        return $body;
    }

    private function action_contact_status()
    {
        // $contact_status = $this->request->post('contact_status', 'integer');
        // $user_id = $this->request->post('user_id', 'integer');

        // $this->users->update_user($user_id, array('contact_status' => $contact_status));

        // return array('success' => 1, 'contact_status' => $contact_status);

        $contact_status = $this->request->post('contact_status', 'integer');
        $user_id = $this->request->post('user_id', 'integer');
        $contract_id = $this->request->post('contract_id', 'integer');
        $manager_id = $this->request->post('manager_id', 'integer');
        $order_id = $this->request->post('order_id', 'integer');

        $old_status = $this->users->get_user($user_id);

        $this->users->update_user($user_id, array('contact_status' => $contact_status));

        $this->UserContactStatuses->add_record(array(
            'created' => date('Y-m-d H:i:s'),
            'contract_id' => $contract_id,
            'user_id' => $user_id,
            'collector_tag_id' => $contact_status,
            'manager_id' => $manager_id
        ));

        $old_valeus = array(
            'status' => $old_status->contact_status,
            'manager_id' => $manager_id
        );
        $new_values = array(
            'status' => $contact_status,
            'manager_id' => $manager_id
        );

        $this->changelogs->add_changelog(array(
            'manager_id' => $manager_id,
            'created' => date('Y-m-d H:i:s'),
            'type' => 'contact_status',
            'old_values' => serialize($old_valeus),
            'new_values' => serialize($new_values),
            'order_id' => $order_id,
            'user_id' => $user_id,
        ));
    }

    private function action_contactperson_status()
    {
        $contact_status = $this->request->post('contact_status', 'integer');
        $contactperson_id = $this->request->post('contactperson_id', 'integer');

        $this->contactpersons->update_contactperson($contactperson_id, array('contact_status' => $contact_status));

        return array('success' => 1, 'contact_status' => $contact_status);
    }

    private function action_workout()
    {
        $order_id = $this->request->post('order_id', 'integer');
        $workout = $this->request->post('workout', 'integer');

        $this->orders->update_order($order_id, array('quality_workout' => $workout));

        return array('success' => 1, 'contact_status' => $contact_status);
    }

    private function confirm_contract_action()
    {
        $order_id = $this->request->post('order');
        $code = $this->request->post('code');

        $order = $this->orders->get_order($order_id);
        $order->phone_mobile = preg_replace("/[^,.0-9]/", '', $order->phone_mobile);

        $db_code = $this->sms->get_code($order->phone_mobile);

        if ($db_code != $code) {
            echo json_encode(['error' => 'Код не совпадает']);
        } else {
            $this->contracts->update_contract($order->contract_id, array(
                'status' => 1,
                'accept_code' => $code,
                'accept_date' => date('Y-m-d H:i:s')
            ));

            $this->orders->update_order($order->order_id, array(
                'status' => 4,
                'confirm_date' => date('Y-m-d H:i:s'),
            ));

            $this->changelogs->add_changelog(array(
                'manager_id' => $this->manager->id,
                'created' => date('Y-m-d H:i:s'),
                'type' => 'confirm_order',
                'old_values' => serialize(array('status' => 3)),
                'new_values' => serialize(array('status' => 4)),
                'order_id' => $order->order_id,
                'user_id' => $order->user_id,
            ));

            echo json_encode(['success' => 1]);

        }

        exit;

    }

    private function change_manager_action()
    {
        $order_id = $this->request->post('order_id', 'integer');
        $manager_id = $this->request->post('manager_id', 'integer');

        if (!($order = $this->orders->get_order((int)$order_id)))
            return array('error' => 'Неизвестный ордер');

        if (!in_array($this->manager->role, array('admin', 'developer')))
            return array('error' => 'Не хватает прав для выполнения операции', 'manager_id' => $order->manager_id);

        $update = array(
            'status' => empty($manager_id) ? 0 : 1,
            'manager_id' => $manager_id,
            'uid' => exec($this->config->root_dir . 'generic/uidgen'),
        );
        $this->orders->update_order($order_id, $update);

        $this->changelogs->add_changelog(array(
            'manager_id' => $this->manager->id,
            'created' => date('Y-m-d H:i:s'),
            'type' => 'status_order',
            'old_values' => serialize(array('status' => $order->status, 'manager_id' => $order->manager_id)),
            'new_values' => serialize($update),
            'order_id' => $order_id,
            'user_id' => $order->user_id,
        ));

        return array('success' => 1, 'status' => 1, 'manager' => $this->manager->name);

    }

    /**
     * OrderController::accept_order_action()
     * Принятие ордера в работу менеджером
     *
     * @return array
     */
    private function accept_order_action()
    {
        $order_id = $this->request->post('order_id', 'integer');

        if (!($order = $this->orders->get_order((int)$order_id)))
            return array('error' => 'Неизвестный ордер');

        if (!empty($order->manager_id) && $order->manager_id != $this->manager->id && !in_array($this->manager->role, array('admin', 'developer')))
            return array('error' => 'Ордер уже принят другим пользователем', 'manager_id' => $order->manager_id);

        $update = array(
            'status' => 1,
            'manager_id' => $this->manager->id,
            'uid' => exec($this->config->root_dir . 'generic/uidgen'),
            'accept_date' => date('Y-m-d H:i:s'),
        );
        $this->orders->update_order($order_id, $update);

        $this->changelogs->add_changelog(array(
            'manager_id' => $this->manager->id,
            'created' => date('Y-m-d H:i:s'),
            'type' => 'accept_order',
            'old_values' => serialize(array('status' => $order->status, 'manager_id' => $order->manager_id)),
            'new_values' => serialize($update),
            'order_id' => $order_id,
            'user_id' => $order->user_id,
        ));

        return array('success' => 1, 'status' => 1, 'manager' => $this->manager->name);
    }

    private function approve_order_action()
    {
        $order_id = $this->request->post('order_id', 'integer');

        if (!($order = $this->orders->get_order((int)$order_id)))
            return array('error' => 'Неизвестный ордер');

        if (!empty($order->manager_id) && $order->manager_id != $this->manager->id && !in_array($this->manager->role, array('admin', 'developer')))
            return array('error' => 'Не хватает прав для выполнения операции');

        if ($order->amount > 30000)
            return array('error' => 'Сумма займа должна быть не более 30000 руб!');

        if ($order->period > 30)
            return array('error' => 'Срок займа должен быть не более 30 дней!');

        if ($order->status != 1)
            return array('error' => 'Неверный статус заявки, возможно Заявка уже одобрена или получен отказ');

        $update = array(
            'status' => 2,
            'manager_id' => $this->manager->id,
            'approve_date' => date('Y-m-d H:i:s'),
        );
        $old_values = array(
            'status' => $order->status,
            'manager_id' => $order->manager_id
        );

        $this->orders->update_order($order_id, $update);

        $this->changelogs->add_changelog(array(
            'manager_id' => $this->manager->id,
            'created' => date('Y-m-d H:i:s'),
            'type' => 'order_status',
            'old_values' => serialize($old_values),
            'new_values' => serialize($update),
            'order_id' => $order_id,
            'user_id' => $order->user_id,
        ));

        $accept_code = rand(1000, 9999);

        $order = $this->orders->get_order($order_id);

        $base_percent = $this->settings->loan_default_percent;

        if (!empty($order->promocode_id)) {
            $promocode = $this->Promocodes->get($order->promocode_id);
            $base_percent = $this->settings->loan_default_percent - ($promocode->discount / 100);
        }

        $new_contract = array(
            'order_id' => $order_id,
            'user_id' => $order->user_id,
            'card_id' => $order->card_id,
            'type' => 'base',
            'amount' => $order->amount,
            'period' => $order->period,
            'create_date' => date('Y-m-d H:i:s'),
            'status' => 0,
            'base_percent' => $base_percent,
            'charge_percent' => $this->settings->loan_charge_percent,
            'peni_percent' => $this->settings->loan_peni,
            'service_sms' => $order->service_sms,
            'service_reason' => $order->service_reason,
            'service_insurance' => $order->service_insurance,
            'accept_code' => $accept_code,
        );

        $user = $this->users->get_user($order->user_id);
        if($user->lead_partner_id == 0){
            $new_contract['card_id'] = $order->card_id;
        }

        $contract_id = $this->contracts->add_contract($new_contract);

        $this->orders->update_order($order_id, array('accept_sms' => $accept_code, 'contract_id' => $contract_id));

        // отправялем смс
        if(($user->lead_partner_id != 0) && ($user->partners_processed == 0)){
            $msg = 'Вам одобрен займ у партнера, получите деньги за 3 минуты в личном кабинете  https://rus-zaym.ru/lk';
            // $this->users->update_user($user->id, array('partners_processed' => 1));
        }
        else{
            $msg = 'Активируй займ ' . ($order->amount * 1) . ' в личном кабинете, код ' . $accept_code . ' https://rus-zaym.ru/lk';
        }
        $this->sms->send($order->phone_mobile, $msg);

        return array('success' => 1, 'status' => 2);

    }

    private function autoretry_accept_action()
    {
        return array('error' => 'отключена функция авторешения');

        $order_id = $this->request->post('order_id', 'integer');

        if (!($order = $this->orders->get_order((int)$order_id)))
            return array('error' => 'Неизвестный ордер');

        if (!empty($order->manager_id) && $order->manager_id != $this->manager->id && !in_array($this->manager->role, array('admin', 'developer')))
            return array('error' => 'Не хватает прав для выполнения операции');

        if ($order->amount > 30000)
            return array('error' => 'Сумма займа должна быть не более 30000 руб!');

        if ($order->period != 30)
            return array('error' => 'Срок займа должен быть 30 дней!');

        $update = array(
            'status' => 2,
            'amount' => $order->autoretry_summ,
            'manager_id' => $this->manager->id
        );
        $old_values = array(
            'status' => $order->status,
            'amount' => $order->amount,
            'manager_id' => $order->manager_id
        );

        $this->orders->update_order($order_id, $update);

        $this->changelogs->add_changelog(array(
            'manager_id' => $this->manager->id,
            'created' => date('Y-m-d H:i:s'),
            'type' => 'order_status',
            'old_values' => serialize($old_values),
            'new_values' => serialize($update),
            'order_id' => $order_id,
            'user_id' => $order->user_id,
        ));

        $accept_code = rand(1000, 9999);

        $base_percent = $this->settings->loan_default_percent;

        if (!empty($order->promocode_id)) {
            $promocode = $this->Promocodes->get($order->promocode_id);
            $base_percent = $this->settings->loan_default_percent - ($promocode->discount / 100);
        }

        $new_contract = array(
            'order_id' => $order_id,
            'user_id' => $order->user_id,
            'card_id' => $order->card_id,
            'type' => 'base',
            'amount' => $order->autoretry_summ,
            'period' => $order->period,
            'create_date' => date('Y-m-d H:i:s'),
            'status' => 0,
            'base_percent' => $base_percent,
            'charge_percent' => $this->settings->loan_charge_percent,
            'peni_percent' => $this->settings->loan_peni,
            'service_reason' => $order->service_reason,
            'service_insurance' => $order->service_insurance,
            'accept_code' => $accept_code,
        );
        $contract_id = $this->contracts->add_contract($new_contract);

        $this->orders->update_order($order_id, array('contract_id' => $contract_id));

        if (!empty($order->id_1c))
            $resp = $this->soap1c->block_order_1c($order->id_1c, 0);

        // отправялем смс
        $msg = 'Активируй займ ' . ($order->amount * 1) . ' в личном кабинете, код ' . $accept_code . ' https://rus-zaym.ru/lk';
        $this->sms->send($order->phone_mobile, $msg);

        return array('success' => 1, 'status' => 2);

    }

    private function reject_order_action()
    {
        $order_id = $this->request->post('order_id', 'integer');
        $reason_id = $this->request->post('reason', 'integer');
        $status = $this->request->post('status', 'integer');

        $order = $this->orders->get_order($order_id);
        $user = $this->users->get_user($order->user_id);
        
        if (!($order = $this->orders->get_order((int)$order_id)))
            return array('error' => 'Неизвестный ордер');

        $reason = $this->reasons->get_reason($reason_id);

        $contract = $this->contracts->get_contract($order->contract_id);

        $update = array(
            'status' => $status,
            'manager_id' => $this->manager->id,
            'reject_reason' => $reason->client_name,
            'reason_id' => $reason_id,
            'reject_date' => date('Y-m-d H:i:s'),
        );
        $old_values = array(
            'status' => $order->status,
            'manager_id' => $order->manager_id,
            'reject_reason' => $order->reject_reason
        );

        if (!empty($order->manager_id) && $order->manager_id != $this->manager->id && !in_array($this->manager->role, array('admin', 'developer')))
            return array('error' => 'Не хватает прав для выполнения операции');

//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($order);echo '</pre><hr />';
        $this->orders->update_order($order_id, $update);

        $this->changelogs->add_changelog(array(
            'manager_id' => $this->manager->id,
            'created' => date('Y-m-d H:i:s'),
            'type' => 'order_status',
            'old_values' => serialize($old_values),
            'new_values' => serialize($update),
            'order_id' => $order_id,
            'user_id' => $order->user_id,
        ));


        // $defaultCard = CardsORM::where('user_id', $order->user_id)->where('base_card', 1)->first();

        // $resp = $this->Best2pay->recurring_by_token($defaultCard->id, 3900, 'Списание за услугу "Причина отказа"', $order->order_id);
        // $status = (string)$resp->state;

        // $max_service_value = $this->operations->max_service_number();
        
        // if ($status == 'APPROVED') {

        //     $transaction = $this->transactions->get_operation_transaction((string)$resp->order_id, (string)$resp->id);

        //     $this->operations->add_operation(array(
        //         'contract_id' => 0,
        //         'user_id' => $order->user_id,
        //         'order_id' => $order->order_id,
        //         'type' => 'REJECT_REASON',
        //         'amount' => 39,
        //         'created' => date('Y-m-d H:i:s'),
        //         'transaction_id' => $transaction->id,
        //         'service_number' => $max_service_value,
        //     ));

        //     // //Отправляем чек по отказу
        //     // // $resp = $this->Cloudkassir->send_reject_reason($order->order_id);


        //     // if (!empty($resp)) {
        //     //     $resp = json_decode($resp);

        //     //     $this->receipts->add_receipt(array(
        //     //         'user_id' => $order->user_id,
        //     //         'name' => 'Информирование о причине отказа',
        //     //         'order_id' => $order->order_id,
        //     //         'contract_id' => 0,
        //     //         'insurance_id' => 0,
        //     //         'receipt_url' => (string)$resp->Model->ReceiptLocalUrl,
        //     //         'response' => serialize($resp),
        //     //         'created' => date('Y-m-d H:i:s')
        //     //     ));
        //     // }

        // }

        CardsORM::where('user_id', $order->user_id)->delete();

        if (!empty($order->utm_source) && $order->utm_source == 'leadstech')
            PostbacksCronORM::insert(['order_id' => $order->order_id, 'status' => 2, 'goal_id' => 3]);

        if (!empty($order->utm_source))
        {
            $this->leadgens->add_postback([
                'order_id' => $order->order_id,
                'created' => date('Y-m-d H:i:s'),
                'lead_name' => $order->utm_source,
                'webmaster' => $order->webmaster_id,
                'click_hash' => $order->click_hash,
                'offer_id' => 0,
                'type' => 'reject',
            ]);
        }

        // создаем документ на оказание услуг
        $user = $this->users->get_user($order->user_id);
        
        $params = [
            'contract' => $contract,
            'user' => $user,
        ];

        $document =
        [
            'user_id' => $order->user_id,
            'order_id' => $order->order_id,
            // 'contract_id' => $contract->id,
            'type' => 'INFORMATION_SERVICES_AGREEMENT',
            'params' => json_encode($params),
            'created' => date('Y-m-d H:i:s')
        ];

        $this->documents->create_document($document);


        // $this->operations->add_operation(array(
        //     'contract_id' => 0,
        //     'user_id' => $order->user_id,
        //     'order_id' => $order->order_id,
        //     'type' => 'REJECT_REASON',
        //     'amount' => 19,
        //     'created' => date('Y-m-d H:i:s'),
        //     'transaction_id' => 0,
        // ));

        $this->db->query("
                SELECT
                id,
                user_id,
                amount,
                register_id
                FROM s_transactions
                WHERE ts.`description` = 'Привязка карты'
                AND reason_code = 1
                and checked = 0
                and user_id = ?
                order by id desc
                ", $order->user_id);

        $transaction = $this->db->result();

        if(($user->lead_partner_id != 0) && ($user->partners_processed == 0)){
            $this->users->update_user($user->id, array('partners_processed' => 1));
        }

        $this->Best2pay->completeCardEnroll($transaction);

        // отправялем смс
        if($order->lead_partner_id == 0){
            $msg = $user->firstname . ', получите займ у наших партнеров: srazu-dengi.ru';
            $this->sms->send($order->phone_mobile, $msg);
        }

        $this->Leadsend->guruleads_send($order_id);

        $this->Leadgens->sendRejectToAlians($order->order_id);

        $this->Leadgens->sendApiVitkol($order_id);

        return array('success' => 1, 'status' => $status);
    }

    private function status_action($status)
    {
        $order_id = $this->request->post('order_id', 'integer');

        if (!($order = $this->orders->get_order((int)$order_id)))
            return array('error' => 'Неизвестный ордер');

        $update = array(
            'status' => $status,
        );
        $old_values = array(
            'status' => $order->status,
        );

        if ($status == 1) {
            if (!empty($order->manager_id) && $order->manager_id != $this->manager->id && !in_array($this->manager->role, array('admin', 'developer')))
                return array('error' => 'Ордер уже принят другим пользователем', 'manager_id' => $order->manager_id);

            $update['manager_id'] = $this->manager->id;
            $old_values['manager_id'] = '';
        }

        if (!empty($order->manager_id) && $order->manager_id != $this->manager->id)
            return array('error' => 'Не хватает прав для выполнения операции');

//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($order);echo '</pre><hr />';
        $this->orders->update_order($order_id, $update);

        $this->changelogs->add_changelog(array(
            'manager_id' => $this->manager->id,
            'created' => date('Y-m-d H:i:s'),
            'type' => 'order_status',
            'old_values' => serialize($old_values),
            'new_values' => serialize($update),
            'order_id' => $order_id,
            'user_id' => $order->user_id,
        ));

        return array('success' => 1, 'status' => $status);
    }

    private function action_cards()
    {
        $order_id = $this->request->post('order_id', 'integer');
        $user_id = $this->request->post('user_id', 'integer');
        $card_id = $this->request->post('card_id', 'integer');

        $order = new StdClass();
        $order->order_id = $order_id;
        $order->user_id = $user_id;
        $order->card_id = $card_id;

        $isset_order = $this->orders->get_order((int)$order_id);

        $order->status = $isset_order->status;
        $order->manager_id = $isset_order->manager_id;

        $this->design->assign('order', $order);

        $card_error = array();

        if (empty($card_id))
            $card_error[] = 'empty_card';

        if (empty($card_error)) {
            $update = array(
                'card_id' => $card_id
            );

            $old_order = $this->orders->get_order($order_id);
            $old_values = array();
            foreach ($update as $key => $val)
                if ($old_order->$key != $update[$key])
                    $old_values[$key] = $old_order->$key;

            $log_update = array();
            foreach ($update as $k => $u)
                if (isset($old_values[$k]))
                    $log_update[$k] = $u;

            $this->changelogs->add_changelog(array(
                'manager_id' => $this->manager->id,
                'created' => date('Y-m-d H:i:s'),
                'type' => 'card',
                'old_values' => serialize($old_values),
                'new_values' => serialize($log_update),
                'order_id' => $order_id,
            ));

            $this->orders->update_order($order_id, $update);

            if ($contract = $this->contracts->get_order_contract($order_id)) {
                $this->contracts->update_contract($contract->id, ['card_id' => $card_id]);
            }

        }
        $this->design->assign('card_error', $card_error);

        $cards = array();
        foreach ($this->cards->get_cards(array('user_id' => $order->user_id)) as $card)
            $cards[$card->id] = $card;
        $this->design->assign('cards', $cards);

    }

    private function action_amount()
    {
        $order_id = $this->request->post('order_id', 'integer');
        $user_id = $this->request->post('user_id', 'integer');
        $amount = $this->request->post('amount', 'integer');
        $period = $this->request->post('period', 'integer');

        $order = new StdClass();
        $order->order_id = $order_id;
        $order->user_id = $user_id;
        $order->amount = $amount;
        $order->period = $period;

        $isset_order = $this->orders->get_order((int)$order_id);

        $order->status = $isset_order->status;
        $order->manager_id = $isset_order->manager_id;

        $amount_error = array();

        if (empty($amount))
            $amount_error[] = 'empty_amount';
        if (empty($period))
            $amount_error[] = 'empty_period';

        if ($isset_order->status > 2 && !in_array($this->manager->role, array('admin', 'developer'))) {
            $amount_error[] = 'Невозможно изменить сумму в этом статусе заявки';
            $order->amount = $isset_order->amount;
            $order->period = $isset_order->period;
        }

        $this->design->assign('order', $order);

        if (empty($amount_error)) {
            $update = array(
                'amount' => $amount,
                'period' => $period
            );

            $old_order = $this->orders->get_order($order_id);
            $old_values = array();
            foreach ($update as $key => $val)
                if ($old_order->$key != $update[$key])
                    $old_values[$key] = $old_order->$key;

            $log_update = array();
            foreach ($update as $k => $u)
                if (isset($old_values[$k]))
                    $log_update[$k] = $u;

            $this->changelogs->add_changelog(array(
                'manager_id' => $this->manager->id,
                'created' => date('Y-m-d H:i:s'),
                'type' => 'period_amount',
                'old_values' => serialize($old_values),
                'new_values' => serialize($log_update),
                'order_id' => $order_id,
            ));

            $this->orders->update_order($order_id, $update);

            if (!empty($old_order->contract_id)) {
                $this->contracts->update_contract($old_order->contract_id, array(
                    'amount' => $amount,
                    'period' => $period
                ));
            }
        }
        $this->design->assign('amount_error', $amount_error);
    }

    private function contactdata_action()
    {
        $order_id = $this->request->post('order_id', 'integer');
        $user_id = $this->request->post('user_id', 'integer');

        $order = new StdClass();

        $order->email = trim($this->request->post('email'));
        $order->birth = trim($this->request->post('birth'));
        $order->birth_place = trim($this->request->post('birth_place'));
        $order->passport_serial = trim($this->request->post('passport_serial'));
        $order->passport_date = trim($this->request->post('passport_date'));
        $order->subdivision_code = trim($this->request->post('subdivision_code'));
        $order->passport_issued = trim($this->request->post('passport_issued'));

        $order->social = trim($this->request->post('social'));

        $contactdata_error = array();

        if (empty($order->email))
            $personal_error[] = 'empty_email';
        if (empty($order->birth))
            $personal_error[] = 'empty_birth';
        if (empty($order->birth_place))
            $personal_error[] = 'empty_birth_place';
        if (empty($order->passport_serial))
            $personal_error[] = 'empty_passport_serial';
        if (empty($order->passport_date))
            $personal_error[] = 'empty_passport_date';
        if (empty($order->subdivision_code))
            $personal_error[] = 'empty_subdivision_code';
        if (empty($order->passport_issued))
            $personal_error[] = 'empty_passport_issued';
        if (empty($order->social))
            $personal_error[] = 'empty_socials';


        if (empty($contactdata_error)) {
            $update = array(
                'email' => $order->email,
                'birth' => $order->birth,
                'birth_place' => $order->birth_place,
                'passport_serial' => $order->passport_serial,
                'passport_date' => $order->passport_date,
                'subdivision_code' => $order->subdivision_code,
                'passport_issued' => $order->passport_issued,
                'social' => $order->social,
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
                'order_id' => $order_id,
                'user_id' => $user_id,
            ));

            $this->users->update_user($user_id, $update);

            // редактирование в документах
            if (!empty($user_id)) {
                $documents = $this->documents->get_documents(array('user_id' => $user_id));
                foreach ($documents as $doc) {
                    if (isset($doc->params['email']))
                        $doc->params['email'] = $order->email;
                    if (isset($doc->params['birth']))
                        $doc->params['birth'] = $order->birth;
                    if (isset($doc->params['birth_place']))
                        $doc->params['birth_place'] = $order->birth_place;
                    if (isset($doc->params['passport_serial']))
                        $doc->params['passport_serial'] = $order->passport_serial;
                    if (isset($doc->params['passport_date']))
                        $doc->params['passport_date'] = $order->passport_date;
                    if (isset($doc->params['subdivision_code']))
                        $doc->params['subdivision_code'] = $order->subdivision_code;
                    if (isset($doc->params['passport_issued']))
                        $doc->params['passport_issued'] = $order->passport_issued;

                    $this->documents->update_document($doc->id, array('params' => $doc->params));
                }
            }
        }

        $this->design->assign('contactdata_error', $contactdata_error);

        $order->order_id = $order_id;
        $order->user_id = $user_id;

        $isset_order = $this->orders->get_order((int)$order_id);

        $order->status = $isset_order->status;
        $order->manager_id = $isset_order->manager_id;

        $this->design->assign('order', $order);

    }

    private function contacts_action()
    {
        $order_id = $this->request->post('order_id', 'integer');
        $user_id = $this->request->post('user_id', 'integer');

        $order = new StdClass();
        $order->contact_person_name = trim($this->request->post('contact_person_name'));
        $order->contact_person_phone = trim($this->request->post('contact_person_phone'));
        $order->contact_person_relation = trim($this->request->post('contact_person_relation'));
        $order->contact_person2_name = trim($this->request->post('contact_person2_name'));
        $order->contact_person2_phone = trim($this->request->post('contact_person2_phone'));
        $order->contact_person2_relation = trim($this->request->post('contact_person2_relation'));

        $contacts_error = array();

        if (empty($order->contact_person_name))
            $contacts_error[] = 'empty_contact_person_name';
        if (empty($order->contact_person_phone))
            $contacts_error[] = 'empty_contact_person_phone';
        if (empty($order->contact_person2_name))
            $contacts_error[] = 'empty_contact_person2_name';
        if (empty($order->contact_person2_phone))
            $contacts_error[] = 'empty_contact_person2_phone';

        if (empty($contacts_error)) {
            $update = array(
                'contact_person_name' => $order->contact_person_name,
                'contact_person_phone' => $order->contact_person_phone,
                'contact_person_relation' => $order->contact_person_relation,
                'contact_person2_name' => $order->contact_person2_name,
                'contact_person2_phone' => $order->contact_person2_phone,
                'contact_person2_relation' => $order->contact_person2_relation,
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
                'order_id' => $order_id,
                'user_id' => $user_id,
            ));

            $this->users->update_user($user_id, $update);
        }

        $this->design->assign('contacts_error', $contacts_error);

        $order->order_id = $order_id;
        $order->user_id = $user_id;

        $isset_order = $this->orders->get_order((int)$order_id);

        $order->status = $isset_order->status;
        $order->manager_id = $isset_order->manager_id;

        $this->design->assign('order', $order);
    }

    private function fio_action()
    {
        $order_id = $this->request->post('order_id', 'integer');
        $user_id = $this->request->post('user_id', 'integer');

        $order = new StdClass();
        $order->lastname = trim($this->request->post('lastname'));
        $order->firstname = trim($this->request->post('firstname'));
        $order->patronymic = trim($this->request->post('patronymic'));
        $order->phone_mobile = trim($this->request->post('phone_mobile'));

        $fio_error = array();

        if (empty($order->lastname))
            $contacts_error[] = 'empty_lastname';
        if (empty($order->firstname))
            $contacts_error[] = 'empty_firstname';
        if (empty($order->patronymic))
            $contacts_error[] = 'empty_patronymic';
        if (empty($order->phone_mobile))
            $contacts_error[] = 'empty_phone_mobile';

        if (empty($fio_error)) {
            $update = array(
                'lastname' => $order->lastname,
                'firstname' => $order->firstname,
                'patronymic' => $order->patronymic,
                'phone_mobile' => $order->phone_mobile,
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
                'type' => 'fio',
                'old_values' => serialize($old_values),
                'new_values' => serialize($log_update),
                'order_id' => $order_id,
                'user_id' => $user_id,
            ));

            $this->users->update_user($user_id, $update);

            // редактирование в документах
            if (!empty($user_id)) {
                $documents = $this->documents->get_documents(array('user_id' => $user_id));
                foreach ($documents as $doc) {
                    if (isset($doc->params['lastname']))
                        $doc->params['lastname'] = $order->lastname;
                    if (isset($doc->params['firstname']))
                        $doc->params['firstname'] = $order->firstname;
                    if (isset($doc->params['patronymic']))
                        $doc->params['patronymic'] = $order->patronymic;
                    if (isset($doc->params['fio']))
                        $doc->params['fio'] = $order->lastname . ' ' . $order->firstname . ' ' . $order->patronymic;
                    if (isset($doc->params['phone']))
                        $doc->params['phone'] = $order->phone_mobile;

                    $this->documents->update_document($doc->id, array('params' => $doc->params));
                }
            }

        }

        $this->design->assign('fio_error', $fio_error);

        $order->order_id = $order_id;
        $order->user_id = $user_id;

        $isset_order = $this->orders->get_order((int)$order_id);

//        $this->soap1c->update_fields($update, '', $isset_order->id_1c);

        $order->status = $isset_order->status;
        $order->manager_id = $isset_order->manager_id;
        $order->phone_mobile = $isset_order->phone_mobile;

        $this->design->assign('order', $order);
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
        $order->Okato = trim($this->request->post('Okato'));
        $order->Oktmo = trim($this->request->post('Oktmo'));

        $addresses_error = array();

        if (empty($order->Regregion))
            $addresses_error[] = 'empty_regregion';

        if (empty($order->Faktregion))
            $addresses_error[] = 'empty_faktregion';

        if (empty($addresses_error)) {
            // $update = array(
            //     'Regregion' => $order->Regregion,
            //     'Regregion_shorttype' => $order->Regregion_shorttype,
            //     'Regcity' => $order->Regcity,
            //     'Regcity_shorttype' => $order->Regcity_shorttype,
            //     'Regdistrict' => $order->Regdistrict,
            //     'Regdistrict_shorttype' => $order->Regdistrict_shorttype,
            //     'Reglocality' => $order->Reglocality,
            //     'Reglocality_shorttype' => $order->Reglocality_shorttype,
            //     'Regstreet' => $order->Regstreet,
            //     'Regstreet_shorttype' => $order->Regstreet_shorttype,
            //     'Reghousing' => $order->Reghousing,
            //     'Regbuilding' => $order->Regbuilding,
            //     'Regroom' => $order->Regroom,
            //     'Regindex' => $order->Regindex,
            // );

            // $regaddressAdressfull = $order->Regregion ?? '';
            // $regaddressAdressfull = $regaddressAdressfull . $order->Regregion ? $order->Regregion_shorttype : '';

            // Изменение адреса регистрации
            $regaddress = [];
            $regaddress['adressfull'] = '';
            $regaddress['zip'] = $order->Regindex ?? '';
            $regaddress['region'] = $order->Regregion ?? '';
            $regaddress['region_type'] = $order->Regregion_shorttype ?? '';
            $regaddress['city'] = $order->Regcity ?? '';
            $regaddress['city_type'] = $order->Regcity_shorttype ?? '';
            $regaddress['district'] = $order->Regdistrict ?? '';
            $regaddress['district_type'] = $order->Regdistrict_shorttype ?? '';
            $regaddress['locality'] = $order->Reglocality ?? '';
            $regaddress['locality_type'] = $order->Reglocality_shorttype ?? '';
            $regaddress['street'] = $order->Regstreet ?? '';
            $regaddress['street_type'] = $order->Regstreet_shorttype ?? '';
            $regaddress['house'] = $order->Reghousing ?? '';
            $regaddress['building'] = $order->Regbuilding ?? '';
            $regaddress['room'] = $order->Regroom ?? '';
            $regaddress['okato'] = $order->Okato ?? '';
            $regaddress['oktmo'] = $order->Oktmo ?? '';

            $regaddress['adressfull'] = $regaddress['region'] ? $regaddress['region'] . " " . $regaddress['region_type'] : "";
            $regaddress['adressfull'] .= $regaddress['city'] ? ", " . $regaddress['city'] . " " . $regaddress['city_type'] : "";
            $regaddress['adressfull'] .= $regaddress['district'] ? ", " . $regaddress['district'] . " " . $regaddress['district_type'] : "";
            $regaddress['adressfull'] .= $regaddress['locality'] ? ", " . $regaddress['locality'] . " " . $regaddress['locality_type'] : "";
            $regaddress['adressfull'] .= $regaddress['street'] ? ", " . $regaddress['street'] . " " . $regaddress['street_type'] : "";
            $regaddress['adressfull'] .= $regaddress['house'] ? ", д " . $regaddress['house'] : "";
            $regaddress['adressfull'] .= $regaddress['building'] ? ", стр " . $regaddress['building'] : "";
            $regaddress['adressfull'] .= $regaddress['room'] ? ", кв " . $regaddress['room'] : "";

            $this->Addresses->update_address($this->request->post('regaddress_id', 'integer'), $regaddress);


            // Изменение фактического адреса
            $faktaddress = [];
            $faktaddress['adressfull'] = '';
            $faktaddress['zip'] = $order->Faktindex ?? '';
            $faktaddress['region'] = $order->Faktregion ?? '';
            $faktaddress['region_type'] = $order->Faktregion_shorttype ?? '';
            $faktaddress['city'] = $order->Faktcity ?? '';
            $faktaddress['city_type'] = $order->Faktcity_shorttype ?? '';
            $faktaddress['district'] = $order->Faktdistrict ?? '';
            $faktaddress['district_type'] = $order->Faktdistrict_shorttype ?? '';
            $faktaddress['locality'] = $order->Faktlocality ?? '';
            $faktaddress['locality_type'] = $order->Faktlocality_shorttype ?? '';
            $faktaddress['street'] = $order->Faktstreet ?? '';
            $faktaddress['street_type'] = $order->Faktstreet_shorttype ?? '';
            $faktaddress['house'] = $order->Fakthousing ?? '';
            $faktaddress['building'] = $order->Faktbuilding ?? '';
            $faktaddress['room'] = $order->Faktroom ?? '';
            $faktaddress['okato'] = $order->Okato ?? '';
            $faktaddress['oktmo'] = $order->Oktmo ?? '';

            $faktaddress['adressfull'] = $faktaddress['region'] ? $faktaddress['region'] . " " . $faktaddress['region_type'] : "";
            $faktaddress['adressfull'] .= $faktaddress['city'] ? ", " . $faktaddress['city'] . " " . $faktaddress['city_type'] : "";
            $faktaddress['adressfull'] .= $faktaddress['district'] ? ", " . $faktaddress['district'] . " " . $faktaddress['district_type'] : "";
            $faktaddress['adressfull'] .= $faktaddress['locality'] ? ", " . $faktaddress['locality'] . " " . $faktaddress['locality_type'] : "";
            $faktaddress['adressfull'] .= $faktaddress['street'] ? ", " . $faktaddress['street'] . " " . $faktaddress['street_type'] : "";
            $faktaddress['adressfull'] .= $faktaddress['house'] ? ", д " . $faktaddress['house'] : "";
            $faktaddress['adressfull'] .= $faktaddress['building'] ? ", стр " . $faktaddress['building'] : "";
            $faktaddress['adressfull'] .= $faktaddress['room'] ? ", кв " . $faktaddress['room'] : "";

            $this->Addresses->update_address($this->request->post('faktaddress_id', 'integer'), $faktaddress);


            $old_user = $this->users->get_user($user_id);
            $old_values = array();
            foreach ($update as $key => $val)
                if ($old_user->$key != $update[$key])
                    $old_values[$key] = $old_user->$key;

            $log_update = array();
            foreach ($update as $k => $u)
                if (isset($old_values[$k]))
                    $log_update[$k] = $u;

            // $this->changelogs->add_changelog(array(
            //     'manager_id' => $this->manager->id,
            //     'created' => date('Y-m-d H:i:s'),
            //     'type' => 'regaddress',
            //     'old_values' => serialize($old_values),
            //     'new_values' => serialize($log_update),
            //     'order_id' => $order_id,
            //     'user_id' => $user_id,
            // ));

            // $this->users->update_user($user_id, $update);

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

            // $this->changelogs->add_changelog(array(
            //     'manager_id' => $this->manager->id,
            //     'created' => date('Y-m-d H:i:s'),
            //     'type' => 'faktaddress',
            //     'old_values' => serialize($old_values),
            //     'new_values' => serialize($log_update),
            //     'order_id' => $order_id,
            //     'user_id' => $user_id,
            // ));

            // $this->users->update_user($user_id, $update);

            $regaddress = $this->Addresses->get_address($this->request->post('regaddress_id', 'integer'));
            $faktaddress = $this->Addresses->get_address($this->request->post('faktaddress_id', 'integer'));

            $this->design->assign('regaddress', $regaddress);
            $this->design->assign('faktaddress', $faktaddress);

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

    private function work_action()
    {
        $order_id = $this->request->post('order_id', 'integer');
        $user_id = $this->request->post('user_id', 'integer');

        $order = new StdClass();
        $order->workplace = trim($this->request->post('workplace'));
        $order->workaddress = trim($this->request->post('workaddress'));
        $order->workcomment = trim($this->request->post('workcomment'));
        $order->profession = trim($this->request->post('profession'));
        $order->workphone = trim($this->request->post('workphone'));
        $order->income = trim($this->request->post('income'));
        $order->expenses = trim($this->request->post('expenses'));
        $order->chief_name = trim($this->request->post('chief_name'));
        // $order->chief_position = trim($this->request->post('chief_position'));
        $order->chief_phone = trim($this->request->post('chief_phone'));
        
        $work->name = trim($this->request->post('chief_name'));
        $work->director_phone = trim($this->request->post('chief_phone'));
        
        $work_error = array();

        if (empty($order->workplace))
            $work_error[] = 'empty_workplace';
        if (empty($order->profession))
            $work_error[] = 'empty_profession';
        if (empty($order->workphone))
            $work_error[] = 'empty_workphone';
        if (empty($order->income))
            $work_error[] = 'empty_income';
        if (empty($order->expenses))
            $work_error[] = 'empty_expenses';
        if (empty($order->chief_name))
            $work_error[] = 'empty_chief_name';
        if (empty($order->chief_phone))
            $work_error[] = 'empty_chief_phone';
        if (empty($order->chief_phone))
            $work_error[] = 'empty_chief_phone';


        if (empty($work_error)) {
            $update = array(
                'workplace' => $order->workplace,
                'workaddress' => $order->workaddress,
                'workcomment' => $order->workcomment,
                'profession' => $order->profession,
                'workphone' => $order->workphone,
                'income' => $order->income,
                'expenses' => $order->expenses,
                // 'chief_name' => $order->chief_name,
                // 'chief_position' => $order->chief_position,
                // 'chief_phone' => $order->chief_phone,
            );
            $update_work = array(
                'name' => $order->chief_name,
                // 'chief_position' => $order->chief_position,
                'director_phone' => $order->chief_phone,
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
                'order_id' => $order_id,
                'user_id' => $user_id,
            ));

            $this->users->update_user($user_id, $update);

            $work = $this->works->get_work_by_user_id($user_id);
            $this->works->update_work($work->id, $update_work);
        }

        $this->design->assign('work_error', $work_error);

        $order->order_id = $order_id;
        $order->user_id = $user_id;

        $isset_order = $this->orders->get_order((int)$order_id);

        $order->status = $isset_order->status;
        $order->manager_id = $isset_order->manager_id;

//        $this->soap1c->update_fields($update, '', $isset_order->id_1c);

        $this->design->assign('order', $order);
        $work = $this->works->get_work_by_user_id($user_id);
        $this->design->assign('work', $work);

    }


    private function action_personal()
    {
        $order_id = $this->request->post('order_id', 'integer');
        $user_id = $this->request->post('user_id', 'integer');

        $order = new StdClass();
        $order->lastname = trim($this->request->post('lastname'));
        $order->firstname = trim($this->request->post('firstname'));
        $order->patronymic = trim($this->request->post('patronymic'));
        $order->gender = trim($this->request->post('gender'));
        $order->birth = trim($this->request->post('birth'));
        $order->birth_place = trim($this->request->post('birth_place'));

        $personal_error = array();

        if (empty($order->lastname))
            $personal_error[] = 'empty_lastname';
        if (empty($order->firstname))
            $personal_error[] = 'empty_firstname';
        if (empty($order->patronymic))
            $personal_error[] = 'empty_patronymic';
        if (empty($order->gender))
            $personal_error[] = 'empty_gender';
        if (empty($order->birth))
            $personal_error[] = 'empty_birth';
        if (empty($order->birth_place))
            $personal_error[] = 'empty_birth_place';

        if (empty($personal_error)) {
            $update = array(
                'lastname' => $order->lastname,
                'firstname' => $order->firstname,
                'patronymic' => $order->patronymic,
                'gender' => $order->gender,
                'birth' => $order->birth,
                'birth_place' => $order->birth_place,
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
                'order_id' => $order_id,
                'user_id' => $user_id,
            ));

            $this->users->update_user($user_id, $update);
        }

        $this->design->assign('personal_error', $personal_error);

        $order->order_id = $order_id;
        $order->user_id = $user_id;

        $isset_order = $this->orders->get_order((int)$order_id);

        $order->status = $isset_order->status;
        $order->manager_id = $isset_order->manager_id;

//        $this->soap1c->update_fields($update, '', $isset_order->id_1c);

        $this->design->assign('order', $order);
    }

    private function action_passport()
    {
        $order_id = $this->request->post('order_id', 'integer');
        $user_id = $this->request->post('user_id', 'integer');

        $order = new StdClass();
        $order->passport_serial = trim($this->request->post('passport_serial'));
        $order->passport_date = trim($this->request->post('passport_date'));
        $order->subdivision_code = trim($this->request->post('subdivision_code'));
        $order->passport_issued = trim($this->request->post('passport_issued'));

        $passport_error = array();

        if (empty($order->passport_serial))
            $passport_error[] = 'empty_passport_serial';
        if (empty($order->passport_date))
            $passport_error[] = 'empty_passport_date';
        if (empty($order->subdivision_code))
            $passport_error[] = 'empty_subdivision_code';
        if (empty($order->passport_issued))
            $passport_error[] = 'empty_passport_issued';

        if (empty($passport_error)) {
            $update = array(
                'passport_serial' => $order->passport_serial,
                'passport_date' => $order->passport_date,
                'subdivision_code' => $order->subdivision_code,
                'passport_issued' => $order->passport_issued
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
                'order_id' => $order_id,
                'user_id' => $user_id,
            ));

            $this->users->update_user($user_id, $update);
        }

        $this->design->assign('passport_error', $passport_error);

        $order->order_id = $order_id;
        $order->user_id = $user_id;

        $isset_order = $this->orders->get_order((int)$order_id);

        $order->status = $isset_order->status;
        $order->manager_id = $isset_order->manager_id;

//        $this->soap1c->update_fields($update, '', $isset_order->id_1c);

        $this->design->assign('order', $order);
    }

    private function reg_address_action()
    {
        $order_id = $this->request->post('order_id', 'integer');
        $user_id = $this->request->post('user_id', 'integer');

        $order = new StdClass();
        $order->Regindex = trim($this->request->post('Regindex'));
        $order->Regregion = trim($this->request->post('Regregion'));
        $order->Regregion_shorttype = trim($this->request->post('Regregion_shorttype'));
        $order->Regcity = trim($this->request->post('Regcity'));
        $order->Regcity_shorttype = trim($this->request->post('Regcity_shorttype'));
        $order->Regdistrict = trim($this->request->post('Regdistrict'));
        $order->Reglocality = trim($this->request->post('Reglocality'));
        $order->Regstreet = trim($this->request->post('Regstreet'));
        $order->Reghousing = trim($this->request->post('Reghousing'));
        $order->Regbuilding = trim($this->request->post('Regbuilding'));
        $order->Regroom = trim($this->request->post('Regroom'));

        $regaddress_error = array();

        if (empty($order->Regregion))
            $regaddress_error[] = 'empty_regregion';
        if (empty($order->Regcity))
            $regaddress_error[] = 'empty_regcity';
        if (empty($order->Regstreet))
            $regaddress_error[] = 'empty_regstreet';
        if (empty($order->Reghousing))
            $regaddress_error[] = 'empty_reghousing';

        if (empty($regaddress_error)) {
            $update = array(
                'Regindex' => $order->Regindex,
                'Regregion' => $order->Regregion,
                'Regregion_shorttype' => $order->Regregion_shorttype,
                'Regcity' => $order->Regcity,
                'Regcity_shorttype' => $order->Regcity_shorttype,
                'Regdistrict' => $order->Regdistrict,
                'Reglocality' => $order->Reglocality,
                'Regstreet' => $order->Regstreet,
                'Reghousing' => $order->Reghousing,
                'Regbuilding' => $order->Regbuilding,
                'Regroom' => $order->Regroom,
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
        }

        $this->design->assign('regaddress_error', $regaddress_error);

        $order->order_id = $order_id;
        $order->user_id = $user_id;

        $isset_order = $this->orders->get_order((int)$order_id);

        $order->status = $isset_order->status;
        $order->manager_id = $isset_order->manager_id;

//        $this->soap1c->update_fields($update, '', $isset_order->id_1c);

        $this->design->assign('order', $order);
    }

    private function fakt_address_action()
    {
        $order_id = $this->request->post('order_id', 'integer');
        $user_id = $this->request->post('user_id', 'integer');

        $order = new StdClass();
        $order->Faktindex = trim($this->request->post('Faktindex'));
        $order->Faktregion = trim($this->request->post('Faktregion'));
        $order->Faktregion_shorttype = trim($this->request->post('Faktregion_shorttype'));
        $order->Faktcity = trim($this->request->post('Faktcity'));
        $order->Faktcity_shorttype = trim($this->request->post('Faktcity_shorttype'));
        $order->Faktdistrict = trim($this->request->post('Faktdistrict'));
        $order->Faktlocality = $this->request->post('Faktlocality');
        $order->Faktstreet = trim($this->request->post('Faktstreet'));
        $order->Fakthousing = trim($this->request->post('Fakthousing'));
        $order->Faktbuilding = trim($this->request->post('Faktbuilding'));
        $order->Faktroom = trim($this->request->post('Faktroom'));

        $faktaddress_error = array();

        if (empty($order->Faktregion))
            $faktaddress_error[] = 'empty_faktregion';
        if (empty($order->Faktcity))
            $faktaddress_error[] = 'empty_faktcity';
        if (empty($order->Faktstreet))
            $faktaddress_error[] = 'empty_faktstreet';
        if (empty($order->Fakthousing))
            $faktaddress_error[] = 'empty_fakthousing';

        if (empty($faktaddress_error)) {
            $update = array(
                'Faktindex' => $order->Faktindex,
                'Faktregion' => $order->Faktregion,
                'Faktregion_shorttype' => $order->Faktregion_shorttype,
                'Faktcity' => $order->Faktcity,
                'Faktcity_shorttype' => $order->Faktcity_shorttype,
                'Faktdistrict' => $order->Faktdistrict,
                'Faktlocality' => $order->Faktlocality,
                'Faktstreet' => $order->Faktstreet,
                'Fakthousing' => $order->Fakthousing,
                'Faktbuilding' => $order->Faktbuilding,
                'Faktroom' => $order->Faktroom,
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

        $this->design->assign('faktaddress_error', $faktaddress_error);

        $order->order_id = $order_id;
        $order->user_id = $user_id;

        $isset_order = $this->orders->get_order((int)$order_id);

        $order->status = $isset_order->status;
        $order->manager_id = $isset_order->manager_id;

//        $this->soap1c->update_fields($update, '', $isset_order->id_1c);

        $this->design->assign('order', $order);
    }


    private function workdata_action()
    {
        $order_id = $this->request->post('order_id', 'integer');
        $user_id = $this->request->post('user_id', 'integer');

        $order = new StdClass();
        $order->work_scope = trim($this->request->post('work_scope'));
        $order->profession = trim($this->request->post('profession'));
        $order->work_phone = trim($this->request->post('work_phone'));
        $order->workplace = trim($this->request->post('workplace'));
        $order->workdirector_name = trim($this->request->post('workdirector_name'));
        $order->income_base = trim($this->request->post('income_base'));

        $workdata_error = array();

        if (empty($order->work_scope))
            $workaddress_error[] = 'empty_work_scope';
        if (empty($order->income_base))
            $workaddress_error[] = 'empty_income_base';

        if (empty($workdata_error)) {
            $update = array(
                'work_scope' => $order->work_scope,
                'profession' => $order->profession,
                'work_phone' => $order->work_phone,
                'workplace' => $order->workplace,
                'workdirector_name' => $order->workdirector_name,
                'income_base' => $order->income_base,
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
                'order_id' => $order_id,
                'user_id' => $user_id,
            ));

            $this->users->update_user($user_id, $update);
        }

        $this->design->assign('workdata_error', $workdata_error);

        $order->order_id = $order_id;
        $order->user_id = $user_id;

        $isset_order = $this->orders->get_order((int)$order_id);

        $order->status = $isset_order->status;
        $order->manager_id = $isset_order->manager_id;

//        $this->soap1c->update_fields($update, '', $isset_order->id_1c);

        $this->design->assign('order', $order);
    }


    private function work_address_action()
    {
        $order_id = $this->request->post('order_id', 'integer');
        $user_id = $this->request->post('user_id', 'integer');

        $order = new StdClass();
        $order->Workregion = trim($this->request->post('Workregion'));
        $order->Workcity = trim($this->request->post('Workcity'));
        $order->Workstreet = trim($this->request->post('Workstreet'));
        $order->Workhousing = trim($this->request->post('Workhousing'));
        $order->Workbuilding = trim($this->request->post('Workbuilding'));
        $order->Workroom = trim($this->request->post('Workroom'));

        $workaddress_error = array();

        if (empty($order->Workregion))
            $workaddress_error[] = 'empty_workregion';
        if (empty($order->Workcity))
            $workaddress_error[] = 'empty_workcity';
        if (empty($order->Workstreet))
            $workaddress_error[] = 'empty_workstreet';
        if (empty($order->Workhousing))
            $workaddress_error[] = 'empty_workhousing';

        if (empty($workaddress_error)) {
            $update = array(
                'Workregion' => $order->Workregion,
                'Workcity' => $order->Workcity,
                'Workstreet' => $order->Workstreet,
                'Workhousing' => $order->Workhousing,
                'Workbuilding' => $order->Workbuilding,
                'Workroom' => $order->Workroom,
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
                'order_id' => $order_id,
                'user_id' => $user_id,
            ));

            $this->users->update_user($user_id, $update);
        }

        $this->design->assign('workaddress_error', $workaddress_error);

        $order->order_id = $order_id;
        $order->user_id = $user_id;

        $isset_order = $this->orders->get_order((int)$order_id);

        $order->status = $isset_order->status;
        $order->manager_id = $isset_order->manager_id;

//        $this->soap1c->update_fields($update, '', $isset_order->id_1c);

        $this->design->assign('order', $order);
    }

    private function socials_action()
    {
        $order_id = $this->request->post('order_id', 'integer');
        $user_id = $this->request->post('user_id', 'integer');

        $order = new StdClass();
        $order->social_fb = trim($this->request->post('social_fb'));
        $order->social_inst = trim($this->request->post('social_inst'));
        $order->social_vk = trim($this->request->post('social_vk'));
        $order->social_ok = trim($this->request->post('social_ok'));

        $socials_error = array();

        if (empty($socials_error)) {
            $update = array(
                'social_fb' => $order->social_fb,
                'social_inst' => $order->social_inst,
                'social_vk' => $order->social_vk,
                'social_ok' => $order->social_ok,
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
                'order_id' => $order_id,
                'user_id' => $user_id,
            ));

            $this->users->update_user($user_id, $update);
        }

        $this->design->assign('socials_error', $socials_error);

        $order->order_id = $order_id;
        $order->user_id = $user_id;

        $isset_order = $this->orders->get_order((int)$order_id);

        $order->status = $isset_order->status;
        $order->manager_id = $isset_order->manager_id;

        $this->design->assign('order', $order);
    }

    private function action_images()
    {
        $order_id = $this->request->post('order_id', 'integer');
        $user_id = $this->request->post('user_id', 'integer');

        $statuses = $this->request->post('status');
        foreach ($statuses as $file_id => $status) {
            $update = array(
                'status' => $status,
                'id' => $file_id
            );
            $old_files = $this->users->get_file($file_id);
            $old_values = array();
            foreach ($update as $key => $val)
                $old_values[$key] = $old_files->$key;
            if ($old_values['status'] != $update['status']) {
                $this->changelogs->add_changelog(array(
                    'manager_id' => $this->manager->id,
                    'created' => date('Y-m-d H:i:s'),
                    'type' => 'images',
                    'old_values' => serialize($old_values),
                    'new_values' => serialize($update),
                    'user_id' => $user_id,
                    'order_id' => $order_id,
                    'file_id' => $file_id,
                ));
            }

            $this->users->update_file($file_id, array('status' => $status));

            if ($status == 3) {
                $this->users->update_user($user_id, array('stage_files' => 0));
            } else {
                $have_reject = 0;
                if ($files = $this->users->get_files(array('user_id' => $user_id))) {
                    foreach ($files as $item)
                        if ($item->status == 3)
                            $have_reject = 1;
                }
                if (empty($have_reject))
                    $this->users->update_user($user_id, array('stage_files' => 1));
                else
                    $this->users->update_user($user_id, array('stage_files' => 0));

            }


        }

        $order = new StdClass();
        $order->order_id = $order_id;
        $order->user_id = $user_id;

        $isset_order = $this->orders->get_order((int)$order_id);

        $order->status = $isset_order->status;
        $order->manager_id = $isset_order->manager_id;

        $this->design->assign('order', $order);

        $files = $this->users->get_files(array('user_id' => $user_id));

        /*Отправляемв 1с
        $need_send = array();
        $files_dir = str_replace('https://', 'http://', $this->config->front_url.'/files/users/');
        foreach ($files as $f)
        {
            if ($f->sent_1c == 0 && $f->status == 2)
            {
                $need_send_item = new StdClass();
                $need_send_item->id = $f->id;
                $need_send_item->user_id = $f->user_id;
                $need_send_item->type = $f->type;
                $need_send_item->url = $files_dir.$f->name;

                $need_send[] = $need_send_item;
            }
        }
        if (!empty($need_send))
        {
            $send_resp = $this->soap1c->send_order_images($order->order_id, $need_send);
            if ($send_resp == 'OK')
                foreach ($need_send as $need_send_file)
                    $this->users->update_file($need_send_file->id, array('sent_1c' => 1, 'sent_date' => date('Y-m-d H:i:s')));
        }
        */

        $this->design->assign('files', $files);
    }

    private function action_services()
    {
        $order_id = $this->request->post('order_id', 'integer');
        $user_id = $this->request->post('user_id', 'integer');
        $contract_id = $this->request->post('contract_id');

        $order = new StdClass();
        $order->service_sms = (int)$this->request->post('service_sms');
        $order->service_insurance = (int)$this->request->post('service_insurance');
        $order->service_reason = (int)$this->request->post('service_reason');

        $services_error = array();

        if (empty($services_error)) {
            $update = array(
                'service_sms' => $order->service_sms,
                'service_insurance' => $order->service_insurance,
                'service_reason' => $order->service_reason,
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
                'order_id' => $order_id,
                'user_id' => $user_id,
            ));

            $this->users->update_user($user_id, $update);
            $this->contracts->update_contract($contract_id, $update);
        }

        $this->design->assign('services_error', $services_error);

        $order->order_id = $order_id;
        $order->user_id = $user_id;

        $isset_order = $this->orders->get_order((int)$order_id);

        $order->status = $isset_order->status;
        $order->manager_id = $isset_order->manager_id;

        $this->design->assign('order', $order);
    }

    private function action_add_comment()
    {
        $user_id = $this->request->post('user_id', 'integer');
        $contactperson_id = $this->request->post('contactperson_id', 'integer');
        $order_id = $this->request->post('order_id', 'integer');
        $text = $this->request->post('text');
        $official = $this->request->post('official', 'integer');

        if (empty($text)) {
            $this->json_output(array('error' => 'Напишите комментарий!'));
        } else {
            $comment = array(
                'manager_id' => $this->manager->id,
                'user_id' => $user_id,
                'contactperson_id' => $contactperson_id,
                'order_id' => $order_id,
                'text' => $text,
                'official' => $official,
                'created' => date('Y-m-d H:i:s'),
            );

            if ($comment_id = $this->comments->add_comment($comment)) {
                $this->json_output(array(
                    'success' => 1,
                    'created' => date('d.m.Y H:i:s'),
                    'text' => $text,
                    'official' => $official,
                    'manager_name' => $this->manager->name,
                ));
            } else {
                $this->json_output(array('error' => 'Не удалось добавить!'));
            }
        }
    }

    private function action_add_canicules()
    {
        $user_id = $this->request->post('user_id', 'integer');
        $order_id = $this->request->post('order_id', 'integer');
        $type  = $this->request->post('type');
        $canicule_from = $this->request->post('canicule_from');
        $canicule_to = $this->request->post('canicule_to');
        $canicule_type = $this->request->post('canicule_type');

        if (empty($canicule_from) || empty($canicule_to)) {
            $this->json_output(array('error' => 'Выберите даты каникул!'));
        } else {

            // менять в TaxingCron
            $kk_base_percent = 190.059;
            if ($canicule_from > '2024-02-15') {
                $kk_base_percent = 190.839;
            }

            $canicule_from = date($canicule_from.' 00:00:00');
            $canicule_to = date($canicule_to.' 23:59:59');

            $comment = array(
                'user_id' => $user_id,
                'order_id' => $order_id,
                'manager_id' => $this->manager->id,
                'created' => date('Y-m-d H:i:s'),
                'type' => $canicule_type,
                'from_date' => $canicule_from,
                'to_date' => $canicule_to,
                'active' => 1,
            );

            $order = $this->orders->get_order($order_id);
            $contract = $this->contracts->get_contract($order->contract_id);
            if ($contract->return_date < $canicule_to) {
                if ($canicule_id = $this->canicules->add_canicule($comment)) {

                    $percents_sum_full = round($contract->loan_body_summ / 100 * $contract->base_percent, 2);

                    $get_operations_arr = [];
                    $get_operations_arr['order_id'] = $order_id;
                    $get_operations_arr['date_from'] = date('Y-m-d H:i:s', strtotime($canicule_from)- 86400);
                    $get_operations_arr['date_to'] = date('Y-m-d 23:59:59', strtotime($canicule_from)- 86400);

                    $operations = $this->operations->get_operations($get_operations_arr);

                    $persents_to_date = 0;
                    $peni_to_date = 0;
                    foreach ($operations as $operation) {
                        if ($persents_to_date < $operation->loan_percents_summ) {
                            $persents_to_date = $operation->loan_percents_summ;
                        }
                        if ($peni_to_date < $operation->loan_peni_summ) {
                            $peni_to_date = $operation->loan_peni_summ;
                        }
                    }

                    $get_operations_arr = [];
                    $get_operations_arr['order_id'] = $order_id;
                    $get_operations_arr['date_from'] = date('Y-m-d H:i:s', strtotime($canicule_from));
                    $get_operations_arr['date_to'] = date('Y-m-d H:i:s');

                    $operations = $this->operations->get_operations($get_operations_arr);

                    foreach ($operations as $operation) {
                        if ($operation->type == 'PENI') {
                            $operation_arr = [];
                            $operation_arr['amount'] = 0;
                            $operation_arr['loan_peni_summ'] = $peni_to_date;
                            $operation_arr['loan_percents_summ'] = $persents_to_date;
                            $this->operations->update_operation($operation->id, $operation_arr);
                        }
                        if ($operation->type == 'PERCENTS') {

                            $operation_arr = [];
                            $operation_arr['loan_peni_summ'] = $peni_to_date;
                            if ($canicule_type == 'svo') {
                                $percents_sum = round($contract->loan_body_summ / 100 * $kk_base_percent / 365, 2);
                                $persents_to_date += $percents_sum;

                                $operation_arr['amount'] = $percents_sum;
                                $operation_arr['loan_percents_summ'] = $persents_to_date;
                            }
                            else{
                                $persents_to_date += $percents_sum_full;
                            }
                            $this->operations->update_operation($operation->id, $operation_arr);
                        }
                    }

                    // Срок погашения заявки - последний день кредитных каникул
                    $contract_arr = [];
                    $contract_arr['return_date'] = $canicule_to;
                    $contract_arr['status'] = 2;
                    $contract_arr['loan_peni_summ'] = $peni_to_date;
                    $contract_arr['loan_percents_summ'] = $persents_to_date;
                    $this->contracts->update_contract($order->contract_id, $contract_arr);

                    $this->json_output(array(
                        'success' => 1,
                        'created' => date('d.m.Y H:i:s'),
                        'type' => $canicule_type,
                        'from' => $canicule_from,
                        'to' => $canicule_to,
                        'manager_name' => $this->manager->name,
                    ));
                } else {
                    $this->json_output(array('error' => 'Не удалось добавить!'));
                }
            }
            else{
                $this->json_output(array('error' => 'Дата окончания КК меньше даты окончания займа!'));
            }
        }
    }

    private function action_close_contract()
    {
        $user_id = $this->request->post('user_id', 'integer');
        $order_id = $this->request->post('order_id', 'integer');
        $comment = $this->request->post('comment');
        $close_date = $this->request->post('close_date');

        if (empty($comment)) {
            $this->json_output(array('error' => 'Напишите комментарий к закрытию!'));
        } elseif (empty($close_date)) {
            $this->json_output(array('error' => 'Укажите дату закрытия!'));
        } else {
            if ($order = $this->orders->get_order($order_id)) {
                if ($contract = $this->contracts->get_contract($order->contract_id)) {
                    $comment = array(
                        'manager_id' => $this->manager->id,
                        'user_id' => $user_id,
                        'contactperson_id' => 0,
                        'order_id' => $order_id,
                        'text' => 'Закрыт в CRM. ' . $comment,
                        'created' => date('Y-m-d H:i:s'),
                    );

                    if ($comment_id = $this->comments->add_comment($comment)) {
                        $this->orders->update_order($order_id, array('status' => 7));

                        $this->contracts->update_contract($contract->id, array(
                            'status' => 3,
                            'close_date' => date('Y-m-d H:i:s', strtotime($close_date)),
                            'loan_body_summ' => 0,
                            'loan_percents_summ' => 0,
                            'loan_charge_summ' => 0,
                            'loan_peni_summ' => 0,
                            'collection_status' => 0,
                            'collection_manager_id' => 0,
                        ));

                        $this->json_output(array(
                            'success' => 1,
                            'created' => date('d.m.Y H:i:s'),
                            'manager_name' => $this->manager->name,
                        ));
                    } else {
                        $this->json_output(array('error' => 'Не удалось добавить комментарий!'));
                    }
                } else {
                    $this->json_output(array('error' => 'Договор не найден!'));
                }
            } else {
                $this->json_output(array('error' => 'Заявка не найдена!'));
            }

        }
    }

    public function action_repay()
    {
        $contract_id = $this->request->post('contract_id', 'integer');

        if (!in_array('repay_button', $this->manager->permissions))
            $this->json_output(array('error' => 'Не хватает прав!'));

        if ($contract = $this->contracts->get_contract($contract_id)) {
            if ($order = $this->orders->get_order($contract->order_id)) {
                if ($order->status != 6 || $contract->status != 6) {
                    $this->json_output(array('error' => 'Невозможно выполнить!'));
                } else {
                    $this->contracts->update_contract($contract->id, array(
                        'status' => 1,
                        'sent_status' => 0,
                        'uid' => exec($this->config->root_dir . 'generic/uidgen'),
                    ));
                    $this->orders->update_order($contract->order_id, array('status' => 4, 'sent_1c' => 0));

                    $this->changelogs->add_changelog(array(
                        'manager_id' => $this->manager->id,
                        'created' => date('Y-m-d H:i:s'),
                        'type' => 'status',
                        'old_values' => serialize(array('status' => 6)),
                        'new_values' => serialize(array('status' => 4)),
                        'order_id' => $contract->order_id,
                        'user_id' => $contract->user_id,
                    ));


                    $this->json_output(array(
                        'success' => 1,
                        'created' => date('d.m.Y H:i:s'),
                        'text' => 'Статус договора изменен',
                        'manager_name' => $this->manager->name,
                    ));
                }

            } else {
                $this->json_output(array('error' => 'Заявка не найдена!'));
            }
        } else {
            $this->json_output(array('error' => 'Договор не найден!'));
        }
    }

    public function add_delete_blacklist()
    {
        $user_id = $this->request->post('user_id', 'integer');
        $user = $this->users->get_user($user_id);
        $phone_mobile = $user->phone_mobile;
        $fio = "$user->lastname $user->firstname $user->patronymic";
        $result_search = $this->blacklist->search($fio);
        $old_value = 0;

        if ($result_search) {
            $this->blacklist->delete_person($result_search);
            $old_value = 1;
        } else {
            $person = array('fio' => mb_strtolower($fio, 'UTF-8'), 'phone' => $phone_mobile);
            $this->blacklist->add_person($person);
        }

        $new_value = ($old_value == 1) ? 0 : 1;

        $this->changelogs->add_changelog(array(
            'manager_id' => $this->manager->id,
            'created' => date('Y-m-d H:i:s'),
            'type' => 'blacklist',
            'old_values' => serialize(array('in_blacklist' => $old_value)),
            'new_values' => serialize(array('in_blacklist' => $new_value)),
            'user_id' => $user_id
        ));
    }

    public function check_blacklist()
    {
        $user_id = $this->request->post('user_id', 'integer');

        $user = $this->users->get_user($user_id);

        $fio = "$user->lastname $user->firstname $user->patronymic";

        $fio = trim($fio);

        $result_search = $this->blacklist->search($fio);

        if ($result_search) {
            echo 1;
            exit;
        }
    }


    public function action_return_insure()
    {
        if ($contract_id = $this->request->post('contract_id', 'integer')) {
            if ($contract = $this->contracts->get_contract($contract_id)) {
                if (empty($contract->insurance_returned)) {
                    if ($insurance = $this->insurances->get_insurance($contract->insurance_id)) {
                        if ($operation = $this->operations->get_operation($insurance->operation_id)) {
                            if ($transaction = $this->transactions->get_transaction($operation->transaction_id)) {
                                $return = $this->best2pay->return_of_additional_services($transaction, $contract);
                                if ($return == 'APPROVED') {
                                    $this->contracts->update_contract($contract->id, array('insurance_returned' => 1));
                                    $this->Ekam->send_return_insure($operation->id);

                                    $res = $this->Ekam->send_return_reject_reason($operation->id);

                                    $data =
                                        [
                                            'user_id' => $operation->user_id,
                                            'order_id' => $operation->order_id,
                                            'contract_id' => $operation->contract_id,
                                            'insurance_id' => '',
                                            'receipt_url' => $res,
                                            'response' => $res,
                                            'created' => date('Y-m-d')

                                        ];

                                    $this->Receipts->add_receipt($data);

                                    $this->json_output(array('success' => 1, 'return' => $return));
                                } else {
                                    $this->json_output(array('error' => 'Не удалось вернуть страховку'));
                                }
                            } else {
                                $this->json_output(array('error' => 'Не найдена транзакция'));
                            }
                        } else {
                            $this->json_output(array('error' => 'Не найдена операция'));
                        }
                    } else {
                        $this->json_output(array('error' => 'Не найдена страховка'));
                    }

                } else {
                    $this->json_output(array('error' => 'Страховка уже возвращена'));
                }
            } else {
                $this->json_output(array('error' => 'Не найден договор №' . $contract_id));
            }
        } else {
            $this->json_output(array('error' => 'Не указан номер договора'));
        }
    }

    public function action_return_bud_v_kurse()
    {
        if ($contract_id = $this->request->post('contract_id', 'integer')) {
            if ($contract = $this->contracts->get_contract($contract_id)) {
                if (empty($contract->bud_v_kurse_returned)) {
                    if ($contract->service_sms) {
                        $operations = $this->operations->get_operations(['type' => 'BUD_V_KURSE', 'contract_id' => $contract->id]);
                        if (isset($operations[0])) {
                            if ($transaction = $this->transactions->get_transaction($operations[0]->transaction_id)) {
                                $return = $this->best2pay->return_of_additional_services($transaction, $contract, 'RETURN_BUD_V_KURSE');
                                if ($return == 'APPROVED') {
                                    $this->contracts->update_contract($contract->id, array('bud_v_kurse_returned' => 1));

                                    $res = $this->Ekam->send_return_bud_v_kurse($operations[0]->id);

                                    $data =
                                        [
                                            'user_id' => $operations[0]->user_id,
                                            'order_id' => $operations[0]->order_id,
                                            'contract_id' => $operations[0]->contract_id,
                                            'insurance_id' => '',
                                            'receipt_url' => $res->online_cashier_url,
                                            'response' => $res,
                                            'created' => date('Y-m-d')

                                        ];

                                    $this->Receipts->add_receipt($data);

                                    $this->json_output(array('success' => 1, 'return' => $return));
                                } else {
                                    $this->json_output(array('error' => 'Не удалось вернуть услугу'));
                                }
                            } else {
                                $this->json_output(array('error' => 'Не найдена транзакция'));
                            }
                        } else {
                            $this->json_output(array('error' => 'Не найдена операция'));
                        }
                    } else {
                        $this->json_output(array('error' => 'Не найдена услуга'));
                    }

                } else {
                    $this->json_output(array('error' => 'Услуга уже возвращена'));
                }
            } else {
                $this->json_output(array('error' => 'Не найден договор №' . $contract_id));
            }
        } else {
            $this->json_output(array('error' => 'Не указан номер договора'));
        }
    }

    public function action_return_reject_reason()
    {
        if ($order_id = $this->request->post('order_id', 'integer')) {
            if ($order = $this->orders->get_order($order_id)) {
                if (empty($order->reject_reason_returned)) {
                    if ($order->reject_reason) {
                        $operations = $this->operations->get_operations(['type' => 'REJECT_REASON', 'order_id' => $order->id]);
                        if (isset($operations[0])) {
                            if ($transaction = $this->transactions->get_transaction($operations[0]->transaction_id)) {
                                $return = $this->best2pay->return_of_additional_services($transaction, $order, 'RETURN_REJECT_REASON');
                                if ($return == 'APPROVED') {

                                    $this->orders->update_order($order->id, array('reject_reason_returned' => 1));

                                    $res = $this->Ekam->send_return_reject_reason($operations[0]->id);

                                    $data =
                                        [
                                            'user_id' => $operations[0]->user_id,
                                            'order_id' => $order->order_id,
                                            'contract_id' => $operations[0]->contract_id,
                                            'insurance_id' => '',
                                            'receipt_url' => $res,
                                            'response' => $res,
                                            'created' => date('Y-m-d')

                                        ];

                                    $this->Receipts->add_receipt($data);

                                    $this->json_output(array('success' => 1, 'return' => $return));
                                } else {
                                    $this->json_output(array('error' => 'Не удалось вернуть услугу'));
                                }
                            } else {
                                $this->json_output(array('error' => 'Не найдена транзакция'));
                            }
                        } else {
                            $this->json_output(array('error' => 'Не найдена операция'));
                        }
                    } else {
                        $this->json_output(array('error' => 'Не найдена услуга'));
                    }

                } else {
                    $this->json_output(array('error' => 'Услуга уже возвращена'));
                }
            } else {
                $this->json_output(array('error' => 'Не найден договор №' . $order_id));
            }
        } else {
            $this->json_output(array('error' => 'Не указан номер заявки'));
        }
    }

    public function action_change_risk_lvl()
    {
        $risk_lvl = $this->request->post('risk_lvl', 'integer');
        $user_id = $this->request->post('user_id', 'integer');

        $user = $this->users->update_user($user_id, array('risk_lvl' => $risk_lvl));

        return $user;
    }

    public function action_check_risk_lvl()
    {
        $user_id = $this->request->post('user_id', 'integer');

        $user = $this->users->get_user($user_id);

        echo json_encode(['lvl' => $user->risk_lvl]);

        exit;
    }

    public function action_change_risk_operation()
    {
        $user_id = $this->request->post('user_id', 'integer');
        $operation = $this->request->post('operation', 'string');

        $find_user = $this->UsersRisksOperations->get_record($user_id);

        if ($find_user) {

            $operations = [$operation => $find_user->{$operation} == 0 ? 1 : 0];

            $this->UsersRisksOperations->update_record($user_id, $operations);

            $find_user = $this->UsersRisksOperations->get_record($user_id);

            $i = 0;

            $delete = true;

            foreach ($find_user as $flag) {
                if ($i >= 2) {
                    if ($flag == 1) {
                        $delete = false;
                        break;
                    }
                }

                $i++;
            }

            if ($delete) {
                $this->UsersRisksOperations->delete_record($find_user->user_id);
            }

        } else {

            $data = ['user_id' => $user_id, "$operation" => 1];

            $this->UsersRisksOperations->add_record($data);

        }

        echo 'success';
    }

    public function action_check_risk_operation()
    {
        $user_id = $this->request->post('user_id', 'integer');

        $find_user = $this->UsersRisksOperations->get_record($user_id);

        echo json_encode($find_user);
        exit;
    }

    private function send_sms_action()
    {
        $order_id = $this->request->post('order_id');
        $order = $this->orders->get_order($order_id);
        $order->phone_mobile = preg_replace("/[^,.0-9]/", '', $order->phone_mobile);
        $code = random_int(0000, 9999);

        $message = "Ваш код: " . $code;

        $resp = $this->sms->send_smsc($order->phone_mobile, $message);
        $resp = $resp['resp'];

        $message =
            [
                'code' => $code,
                'phone' => $order->phone_mobile,
                'response' => "$resp"
            ];

        $this->sms->add_message($message);

        echo json_encode(['code' => $code]);
        exit;
    }

    private function send_casual_sms_action()
    {
        $order_id = $this->request->post('order_id');
        $message = $this->request->post('text_sms');
        $order = $this->orders->get_order($order_id);
        $order->phone_mobile = preg_replace("/[^,.0-9]/", '', $order->phone_mobile);
        
        

        // $message = "Ваш код: " . $code;

        $resp = $this->sms->send_smsc($order->phone_mobile, $message);
        $resp = $resp['resp'];

        $message =
            [
                'code' => $code,
                'phone' => $order->phone_mobile,
                'response' => "$resp"
            ];

        $this->sms->add_message($message);

        echo json_encode(['code' => $code]);
        exit;
    }

    private function action_send_template_sms()
    {
        $user_id = $this->request->post('user_id', 'integer');
        $order_id = $this->request->post('order_id', 'integer');
        $template_id = $this->request->post('template_id', 'integer');
        $manager_id = $this->request->post('manager_id', 'integer');
        $text_sms = $this->request->post('text_sms', 'string');

        $user = $this->users->get_user((int)$user_id);

        $template = null;

        if ($text_sms) {

            $template = $text_sms;
        }

        if ($template_id) {

            $template = $this->sms->get_template($template_id);
            $template = $template->template;
        }

        if (!empty($order_id)) {
            $order = $this->orders->get_order($order_id);

            if ($order->contract_id) {
                $code = $this->helpers->c2o_encode($order->contract_id);
                $payment_link = $this->config->front_url . '/p/' . $code;
                $contract = $this->contracts->get_contract($order->contract_id);
                $osd_sum = $contract->loan_body_summ + $contract->loan_percents_summ + $contract->loan_charge_summ + $contract->loan_peni_summ;
                $prolongation_sum = $contract->loan_percents_summ + $contract->loan_peni_summ;
            }

            $str_params =
                [
                    '{$payment_link}' => $payment_link,
                    '$firstname' => $user->firstname,
                    '$fio' => "$user->lastname $user->firstname $user->patronymic",
                    '$percent' => $contract->loan_percents_summ,
                    '$final_sum' => $osd_sum,
                    '$prolongation_sum' => $prolongation_sum,
                    '%d' => $contract->accept_code,
                    '$accept_code' => $contract->accept_code,
                    '$amount' => $order->amount,
                    '$credit' => $contract->amount,
                    '$payment' => $contract->loan_body_summ + $contract->loan_percents_summ,
                    '$payday' => date('d.m.Y', strtotime($contract->return_date)),
                    '$contract' => $contract->number,
                    '$crd1000' => $contract->amount + 1000,
                    '$crd2000' => $contract->amount + 2000,
                    '$crd3000' => $contract->amount + 3000,
                    '$crd4000' => $contract->amount + 4000,
                    '$crd5000' => $contract->amount + 5000,
                    '$crd6000' => $contract->amount + 6000,
                    '$crd7000' => $contract->amount + 7000,
                    '$crd8000' => $contract->amount + 8000,
                    '$crd9000' => $contract->amount + 9000,
                    '$crd10000' => $contract->amount + 10000,
                    '$today' => date('d.m.Y'),
                    '$tomorrow' => date('d.m.Y', strtotime('+1 day')),
                    '$user_phone' => $order->phone_mobile,
                    '$3days' => date('d.m.Y', strtotime('+3 days')),
                    '$5days' => date('d.m.Y', strtotime('+5 days')),
                    '$7days' => date('d.m.Y', strtotime('+7 days')),
                ];

            $template = strtr($template, $str_params);
        }

        $this->sms->send(
            $user->phone_mobile,
            $template
        );

        $this->sms->add_message(array(
            'user_id' => $user->id,
            'order_id' => $order_id,
            'phone' => $user->phone_mobile,
            'message' => $template,
            'created' => date('Y-m-d H:i:s'),
        ));

        $this->changelogs->add_changelog(array(
            'manager_id' => $manager_id,
            'created' => date('Y-m-d H:i:s'),
            'type' => 'send_sms',
            'old_values' => '',
            'new_values' => $template,
            'user_id' => $user->id,
            'order_id' => $order_id,
        ));

        $this->json_output(array('success' => true));
    }

    private function num2word($num, $words)
    {
        $num = $num % 100;
        if ($num > 19) {
            $num = $num % 10;
        }
        switch ($num) {
            case 1:
                {
                    return ($words[0]);
                }
            case 2:
            case 3:
            case 4:
                {
                    return ($words[1]);
                }
            default:
                {
                    return ($words[2]);
                }
        }
    }

    private function action_add_receipt()
    {
        $type = $this->request->post('type');
        $issuance = $this->request->post('issuance_flag');
        $order_id = $this->request->post('order_id');
        $operation_id = $this->request->post('operation_id');
        $receipt_uid = md5(time());


        $order = $this->orders->get_order($order_id);

        switch ($type):
            case 'reject_reason':
                $operations = $this->operations->get_operations(['order_id' => $order_id, 'type' => 'REJECT_REASON']);
                $type = 'RETURN_REJECT_REASON';
                $operation_id = $operations[0]->id;
                $operation_amount = $operations[0]->amount;
                $title = 'Возврат услуги "Узнай причину отказа"';

                // // $res = $this->Cloudkassir->return_reject_reason($order_id);
                break;

            case 'POLIS_STRAHOVANIYA':
                $operation = $this->operations->get_operation($operation_id);
                $operation_amount = $operation->amount;
                $type = 'RETURN_INSURANCE';
                $title = 'Возврат услуги "Страхование от несчастного случая"';

                // // $res = $this->Cloudkassir->return_insurance($operation->id);
                break;

            case 'POLIS_ZAKRITIE':
                $operation = $this->operations->get_operation($operation_id);
                $operation_amount = $operation->amount;
                $type = 'RETURN_INSURANCE';

                // $title = 'Возврат услуги "Страхование банковской карты"';
                break;

        endswitch;

        $receipt =
            [
                'title' => $title,
                'order_id' => $receipt_uid,
                'amount' => $operation_amount,
                'email' => $order->email
            ];

        $res = $this->Ekam->return_receipt_request($receipt);
        $res = json_decode($res);

        $data =
            [
                'user_id' => $order->user_id,
                'name' => $title,
                'order_id' => $order->order_id,
                'contract_id' => $order->contract_id,
                'insurance_id' => 0,
                'receipt_url' => $res->online_cashier_url,
                'response' => json_encode($res),
                'created' => date('Y-m-d H:i:s')

            ];

        $this->Receipts->add_receipt($data);

        $this->operations->add_operation(array(
            'contract_id' => $order->contract_id,
            'user_id' => $order->user_id,
            'order_id' => $order_id,
            'transaction_id' => 0,
            'type' => $type,
            'amount' => $operation_amount,
            'created' => date('Y-m-d H:i:s'),
            'sent_date' => date('Y-m-d H:i:s'),
            'loan_body_summ' => 0,
            'loan_percents_summ' => 0,
            'loan_charge_summ' => 0,
            'loan_peni_summ' => 0,
            'type_payment' => 0
        ));
        exit;
    }

    private function action_add_contact()
    {
        $user_id = $this->request->post('user_id');
        $fio = strtoupper($this->request->post('fio'));
        $phone = trim($this->request->post('phone'));
        $relation = $this->request->post('relation');
        $comment = $this->request->post('comment');

        $contact =
            [
                'user_id' => $user_id,
                'name' => $fio,
                'phone' => $phone,
                'relation' => $relation,
                'comment' => $comment
            ];

        $this->Contactpersons->add_contactperson($contact);
        exit;
    }

    private function action_delete_contact()
    {
        $id = $this->request->post('id');

        $this->Contactpersons->delete_contactperson($id);

        exit;
    }

    private function action_edit_contact()
    {
        $id = $this->request->post('id');

        $fio = strtoupper($this->request->post('fio'));
        $phone = trim($this->request->post('phone'));
        $relation = $this->request->post('relation');
        $comment = $this->request->post('comment');

        $contact =
            [
                'name' => $fio,
                'phone' => $phone,
                'relation' => $relation,
                'comment' => $comment
            ];

        $this->Contactpersons->update_contactperson($id, $contact);
        exit;
    }

    private function action_get_contact()
    {
        $id = $this->request->post('id');

        $contact = $this->Contactpersons->get_contactperson($id);

        echo json_encode($contact);
        exit;
    }

    private function action_restruct()
    {
        $dates = $this->request->post('date');
        $payments = $this->request->post('payment');
        $payOd = $this->request->post('payOd');
        $payPrc = $this->request->post('payPrc');
        $payPeni = $this->request->post('payPeni');
        $orderId = $this->request->post('orderId');
        $userId = $this->request->post('userId');
        $contractId = $this->request->post('contractId');

        $contract = ContractsORM::find($contractId);
        $user = UsersORM::find($userId);

        $paymentSchedules = array_replace_recursive($dates, $payments, $payOd, $payPrc, $payPeni);

        $totalPaymens =
            [
                'date' => 'Итого',
                'payment' => 0,
                'payOd' => 0,
                'payPrc' => 0,
                'payPeni' => 0
            ];

        foreach ($paymentSchedules as $schedule) {
            $totalPaymens['payment'] += $schedule['payment'];
            $totalPaymens['payOd'] += $schedule['payOd'];
            $totalPaymens['payPrc'] += $schedule['payPrc'];
            $totalPaymens['payPeni'] += $schedule['payPeni'];

            $payment =
                [
                    'order_id' => $orderId,
                    'user_id' => $userId,
                    'contract_id' => $contractId,
                    'plan_date' => date('Y-m-d', strtotime($schedule['date'])),
                    'plan_payment' => $schedule['payment'],
                    'plan_od' => $schedule['payOd'],
                    'plan_prc' => $schedule['payPrc'],
                    'plan_peni' => $schedule['payPeni']
                ];

            PaymentsToSchedules::insert($payment);
        }

        array_push($paymentSchedules, $totalPaymens);

        $schedule = new PaymentsSchedulesORM();
        $schedule->order_id = $orderId;
        $schedule->user_id = $userId;
        $schedule->contract_id = $contractId;
        $schedule->init_od = $contract->loan_body_summ;
        $schedule->init_prc = $contract->loan_percents_summ;
        $schedule->init_peni = $contract->loan_peni_summ;
        $schedule->actual = 1;
        $schedule->payment_schedules = json_encode($paymentSchedules);
        $schedule->save();

        PaymentsToSchedules::where('contract_id', $contractId)
            ->update(['schedules_id' => $schedule->id]);

        ContractsORM::where('id', $contractId)->update(['status' => 10]);

        $params = [
            'contract' => $contract,
            'user' => $user,
            'schedules' => $schedule
        ];

        $document =
            [
                'user_id' => $contract->user_id,
                'order_id' => $contract->order_id,
                'contract_id' => $contract->id,
                'type' => 'DOP_RESTRUCT',
                'params' => json_encode($params),
                'created' => date('Y-m-d H:i:s')
            ];

        $this->documents->create_document($document);

        $document =
            [
                'user_id' => $contract->user_id,
                'order_id' => $contract->order_id,
                'contract_id' => $contract->id,
                'type' => 'GRAPH_RESTRUCT',
                'params' => json_encode($params),
                'created' => date('Y-m-d H:i:s')
            ];

        $this->documents->create_document($document);

        exit;
    }

    private function action_confirm_asp()
    {
        $phone = $this->request->post('phone');
        $code = $this->request->post('code');
        $contractId = $this->request->post('contract');

        $this->db->query("
        SELECT code, created
        FROM s_sms_messages
        WHERE phone = ?
        AND code = ?
        ORDER BY created DESC
        LIMIT 1
        ", $phone, $code);

        $result = $this->db->result();

        if (empty($result)) {

            echo json_encode(['error' => 1]);
            exit;

        } else {

            $nextPay = PaymentsToSchedules::where('contract_id', $contractId)->orderBy('id', 'asc')->first();

            $updateContract =
                [
                    'loan_body_summ' => $nextPay->plan_od,
                    'loan_percents_summ' => $nextPay->plan_prc,
                    'loan_peni_summ' => $nextPay->plan_peni,
                    'next_pay' => date('Y-m-d', strtotime($nextPay->plan_date)),
                    'payment_id' => $nextPay->id,
                    'status' => 11,
                    'stop_profit' => 1,
                    'is_restructed' => 1
                ];

            ContractsORM::where('id', $contractId)->update($updateContract);

            echo json_encode(['success' => 1]);
            exit;
        }
    }

    private function action_editLoanProfit()
    {
        $contractId = $this->request->post('contractId');
        $bodySum = $this->request->post('body');
        $prcSum = $this->request->post('prc');
        $peniSum = $this->request->post('peni');
        $stopProfit = $this->request->post('stopProfit');

        $bodySum = str_replace(',', '.', $bodySum);
        $prcSum = str_replace(',', '.', $prcSum);
        $peniSum = str_replace(',', '.', $peniSum);

        if (empty($stopProfit))
            $stopProfit = 0;
        else
            $stopProfit = 1;

        $update =
            [
                'loan_body_summ' => $bodySum,
                'loan_percents_summ' => $prcSum,
                'loan_peni_summ' => $peniSum,
                'stop_profit' => $stopProfit
            ];

        ContractsORM::where('id', $contractId)->update($update);
        exit;
    }

    private function action_editServices()
    {
        $contractId = $this->request->post('contractId');
        $operations_ids = $this->request->post('operationsIds');

        $operations_ids_arr = explode(',', $operations_ids);
        $operations_refund_services_arrs = $this->RefundForServices->get_done($contractId);
        foreach ($operations_refund_services_arrs as $operations_refund_services_arr) {
            $operations_refund_services = explode(',', $operations_refund_services_arr->operations_ids);
            foreach ($operations_refund_services as $operations_refund_service) {
                foreach ($operations_ids_arr as $operations_ids_val) {
                    if ($operations_refund_service == $operations_ids_val) {
                        exit;
                    }
                }
            }
        }

        // Код для СМС
        $contract = $this->contracts->get_contract($contractId);
        $order = $this->orders->get_order($contract->order_id);
        $order->phone_mobile = preg_replace("/[^,.0-9]/", '', $order->phone_mobile);
        $code = random_int(1000, 9999);

        // Данные для заявления
        // в договор
        $contract = $this->contracts->get_contract($contractId);
        if (!is_null($contract->edit_services)) {
            exit;
        }

        $update =
        [
            'edit_services' => $code
        ];
        ContractsORM::where('id', $contractId)->update($update);
        
        // список операций, которые надо вернуть
        $refund_for_services_add = array(
            'contract_id' => $contractId,
            'operations_ids' => $operations_ids,
            'sms' => $code,
        );
        
        $refund_for_services_id = $this->RefundForServices->add($refund_for_services_add);


        // СМС
        $message = "Подпишите в ЛК. Код возврата средств за доп услуги: " . $code;

        $resp = $this->sms->send_smsc($order->phone_mobile, $message);
        $resp = $resp['resp'];

        $sms_message =
            [
                'code' => $code,
                'phone' => $order->phone_mobile,
                'response' => "$resp",
                'message' => $message
            ];

        $this->sms->add_message($sms_message);

        echo json_encode(['code' => $code]);
        exit;
    }

    private function action_editServicesClosed()
    {
        $contractId = $this->request->post('contractId');
        $operations_ids = $this->request->post('operationsIds');
        
        $contract = $this->contracts->get_contract($contractId);

        $operation = $this->operations->get_operation($operations_ids);
        $transaction = $this->transactions->get_transaction($operation->transaction_id);
        
        $b2p_status = $this->Best2pay->return_insurance($transaction, $contract);
        $operation_info = $this->Best2pay->get_operation_info($transaction->sector, $transaction->register_id, $transaction->operation);

        $this->transactions->update_transaction($transaction->id, array(
            'callback_response' => $operation_info
        ));
        exit;
    }

    private function action_reccurent()
    {
        $orderId = $this->request->post('orderId');
        $userId = $this->request->post('userId');
        $contractId = $this->request->post('contractId');
        $percent = $this->request->post('percent');
        $contract = $this->contracts->get_contract($contractId);
        $order = $this->orders->get_order($orderId);
        $contract_total_summ = $contract->loan_body_summ + $contract->loan_percents_summ + $contract->loan_peni_summ;
        $summ = round(($contract_total_summ * ($percent / 100)), 2) * 100;
        if ($summ > ($contract_total_summ * 100)) {
            $summ = round($contract_total_summ, 2) * 100;
        }
        $reccurent_pay = $this->best2pay->reccurent_pay($order, $summ, 'Карточка заявки');

        if ($reccurent_pay) {
            $summ = $summ / 100;
            $save_amount = $summ;
            $loan_peni_summ = $contract->loan_peni_summ;
            $loan_percents_summ = $contract->loan_percents_summ;
            $loan_body_summ = $contract->loan_body_summ;

            $transaction_peni_summ = 0;
            $transaction_percent_summ = 0;
            $transaction_body_summ = 0;
            // $pay
            echo "Amount = $summ calc peni\r\n";
            if ($summ >= $loan_peni_summ) {
                $summ -= $loan_peni_summ;
                $loan_peni_summ = 0;
                $transaction_peni_summ = $loan_peni_summ;

                echo "Amount = $summ calc percents\r\n";
                if ($summ >= $loan_percents_summ) {
                    $summ -= $loan_percents_summ;
                    $loan_percents_summ = 0;
                    $transaction_percent_summ = $loan_percents_summ;

                    echo "Amount = $summ calc body\r\n";
                    if ($summ >= $loan_body_summ) {
                        $transaction_body_summ = $loan_body_summ;
                        $loan_body_summ = 0;
                    } else {
                        $loan_body_summ -= $summ;
                        $transaction_body_summ = $summ;
                    }

                } else {
                    $loan_percents_summ -= $summ;
                    $transaction_percent_summ = $summ;
                }

            } else {
                $loan_peni_summ -= $summ;
                $transaction_peni_summ = $summ;
            }

            $operation = OperationsORM::query()->where('id', '=', $reccurent_pay)->first();
            if ($operation) {
                echo "Operation exis\r\n";
                $operation->update([
                    'loan_body_summ' => $loan_body_summ,
                    'loan_percents_summ' => $loan_percents_summ,
                    'loan_peni_summ' => $loan_peni_summ
                ]);
            }
            $transaction = TransactionsORM::query()->where('id', '=', $operation->transaction_id)->first();
            if ($transaction) {
                echo "Transaction exis\r\n";
                $transaction->update([
                    'loan_body_summ' => $transaction_body_summ,
                    'loan_percents_summ' => $transaction_percent_summ,
                    'loan_peni_summ' => $transaction_peni_summ
                ]);
            }

            $save = [
                'loan_body_summ' => $loan_body_summ,
                'loan_percents_summ' => $loan_percents_summ,
                'loan_peni_summ' => $loan_peni_summ,
                'reccurent_attempt' => $contract->reccurent_attempt + 1,
                'reccurent_summ' => $contract->reccurent_summ + $save_amount,
                'reccurent_status' => 1,
            ];

            if ($loan_body_summ <= 0) {
                $save['status'] = 3;
                $save['close_date'] = date('Y-m-d H:i:s');
                $save['collection_status'] = 0;
                $save['collection_manager_id'] = 0;
                $this->orders->update_order($contract->order_id, ['status' => 7]);
            }
            $this->contracts->update_contract($contract->id, $save);
        }

        exit;
    }

    private function action_add_risk()
    {
        $user_id = $this->request->post('user_id', 'integer');
        $manager_id = $this->request->post('manager_id', 'integer');
        $text = $this->request->post('text');

        $user_risk_statuses_obj = $this->UsersRisksOperations->get_record($user_id);
        $user_risk_statuses = (array) $user_risk_statuses_obj;

        unset($user_risk_statuses['id']);
        $user_risk_statuses['created'] = date('Y-m-d H:i:s');
        $user_risk_statuses['comment'] = $text;
        $user_risk_statuses['manager_id'] = $manager_id;

        $this->UserRiskStatuses->add_record($user_risk_statuses);

        $this->json_output(array('success' => 'success'));
    }

    private function action_hide_phone_operation()
    {
        $contact_id = $this->request->post('contact_id', 'integer');
        $contact = $this->Contactpersons->get_contactperson($contact_id);
        if ($contact->contact_hide == 1) {
            $contact_hide = 0;
        }
        else{
            $contact_hide = 1;
        }
        $this->contactpersons->update_contactperson($contact_id, array('contact_hide' => $contact_hide));
    }

    private function action_add_pril_1()
    {
        $contract_id = $this->request->post('contract_id');

        $contract = $this->contracts->get_contract($contract_id);
        $document_type = 'PRIL_1';
        
        $ob_date = new DateTime();
        $ob_date->add(DateInterval::createFromDateString($contract->period . ' days'));
        $return_date = $ob_date->format('Y-m-d H:i:s');

        $return_amount = round($contract->amount + $contract->amount * $contract->base_percent * $contract->period / 100, 2);
        $return_amount_rouble = (int)$return_amount;
        $return_amount_kop = ($return_amount - $return_amount_rouble) * 100;

        $contract_order = $this->orders->get_order((int)$contract->order_id);
        
        $insurance_cost = $this->insurances->get_insurance_cost($contract_order);

        $params = array(
            'lastname' => $contract_order->lastname,
            'firstname' => $contract_order->firstname,
            'patronymic' => $contract_order->patronymic,
            'phone' => $contract_order->phone_mobile,
            'birth' => $contract_order->birth,
            'number' => $contract->number,
            'contract_date' => date('Y-m-d H:i:s'),
            'created' => date('Y-m-d H:i:s'),
            'return_date' => $return_date,
            'return_date_day' => date('d', strtotime($return_date)),
            'return_date_month' => date('m', strtotime($return_date)),
            'return_date_year' => date('Y', strtotime($return_date)),
            'return_amount' => $return_amount,
            'return_amount_rouble' => $return_amount_rouble,
            'return_amount_kop' => $return_amount_kop,
            'base_percent' => $contract->base_percent,
            'amount' => $contract->amount,
            'period' => $contract->period,
            'return_amount_percents' => round($contract->amount * $contract->base_percent * $contract->period / 100, 2),
            'passport_serial' => $contract_order->passport_serial,
            'passport_date' => $contract_order->passport_date,
            'subdivision_code' => $contract_order->subdivision_code,
            'passport_issued' => $contract_order->passport_issued,
            'passport_series' => substr(str_replace(array(' ', '-'), '', $contract_order->passport_serial), 0, 4),
            'passport_number' => substr(str_replace(array(' ', '-'), '', $contract_order->passport_serial), 4, 6),
            'asp' => $contract->accept_code,
            'insurance_summ' => $insurance_cost,
        );

        $params['user'] = $this->users->get_user($contract->user_id);
        $params['order'] = $this->orders->get_order($contract->order_id);
        $params['contract'] = $contract;

        $params['pan'] = $this->cards->get_card($contract->card_id)->pan;

        $sas = $this->documents->create_document(array(
            'user_id' => $contract->user_id,
            'order_id' => $contract->order_id,
            'contract_id' => $contract->id,
            'type' => $document_type,
            'params' => json_encode($params),
        ));


        exit;
    }
    
    private function action_to_onec()
    {
        $user_id = $this->request->post('user_id');
        $order_id = $this->request->post('order_id');
        $operation_id = $this->request->post('operation_id');

        $operation = $this->operations->get_operation($operation_id);
        

        $this->db->query("
            SELECT 
                o.id,
                o.order_id,
                o.contract_id,
                o.created,
                o.amount,
                t.register_id,
                t.operation,
                t.loan_body_summ,
                t.loan_percents_summ,
                t.loan_peni_summ,
                t.prolongation,
                c.number AS contract_number,
                c.close_date
            FROM __operations AS o
            LEFT JOIN s_transactions AS t
            ON t.id = o.transaction_id
            LEFT JOIN s_contracts AS c
            ON c.id = o.contract_id
            WHERE o.id = ?
        ", (int)$operation_id);

        $payment = $this->db->result();
        $result = Onec::sendPayment($payment);
        
        exit;
    }
}
