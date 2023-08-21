<?php
error_reporting(E-ALL);
ini_set('display_errors', 'On');

class CollectorContractsController extends Controller
{
    private $regions = [];

    private $cities = [];

    private $contract_dates = [];


    public function addContract($data)
    {
        $key = $this->addRegion($data['Regregion']);
        $this->contract_dates[] = [
            array_values($data),
            'region_key' => $key,
            'city_key' => $this->addCity($data['Regcity']),
        ];
        return $key;
    }


    public function addRegion($name)
    {
        if ($key = array_search($name, $this->regions)) {
            return $key;
        }
        $this->regions[] = $name;
        return count($this->regions) - 1;
    }

    public function addCity($name)
    {
        if ($key = array_search($name, $this->cities)) {
            return $key;
        }
        $this->cities[] = $name;
        return count($this->cities) - 1;
    }

    public function fetch()
    {
        $items_per_page = 50;

        if ($this->request->method('post')) {
            switch ($this->request->post('action', 'string')):

                case 'contactperson_comment':
                    $this->contactperson_comment_action();
                    break;

                case 'collection_manager':
                    $this->set_collection_manager_action();
                    break;

                case 'collection_status':
                    $this->set_collection_status_action();
                    break;

                case 'workout':
                    $this->set_workout_action();
                    break;

                case 'hide_prolongation':
                    $this->set_hide_prolongation_action();
                    break;

                case 'sud_label':
                    $this->set_sud_label_action();
                    break;

                case 'send_sms':
                    $this->send_sms_action();
                    break;

                case 'distribute':
                    $this->distribute_action();
                    break;

                case 'reset':
                    $this->reset_action();
                    break;

            endswitch;
        }

        $order_ids = array();
        $user_ids = array();
        $contracts = array();

        $filter = array();

        $sms_templates = $this->sms->get_templates();
        $this->design->assign('sms_templates', $sms_templates);

        if (!($period = $this->request->get('period')))
            $period = 'all';

        switch ($period):

            case 'month':
                $filter['inssuance_date_from'] = date('Y-m-01');
                break;

            case 'year':
                $filter['inssuance_date_from'] = date('Y-01-01');
                break;

            case 'all':
                $filter['inssuance_date_from'] = null;
                $filter['inssuance_date_to'] = null;
                break;

            case 'optional':
                $daterange = $this->request->get('daterange');
                $filter_daterange = array_map('trim', explode('-', $daterange));
                $filter['inssuance_date_from'] = date('Y-m-d', strtotime($filter_daterange[0]));
                $filter['inssuance_date_to'] = date('Y-m-d', strtotime($filter_daterange[1]));
                break;

        endswitch;

        $this->design->assign('period', $period);

        if ($search = $this->request->get('search')) {
            if (isset($search['order_id']))
                $search['order_id'] = explode(' ', $search['order_id']);

            $filter['search'] = array_filter($search);
            $this->design->assign('search', array_filter($search));
        }

        $filter['type'] = 'base';


        if (in_array($this->manager->role, ['collector', 'manager'])) {
            $filter['collection_status'] = array($this->manager->collection_status_id);
            $filter['collection_manager_id'] = $this->manager->id;
        } elseif (in_array($this->manager->role, array('developer', 'admin', 'senior collector'))) {
            $filter['collection_status'] = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10);
            $filter['collection_manager_id'] = null;
        }


        if ($this->request->get('status', 'integer')) {
            $filter_status = $this->request->get('status', 'integer');
            $filter['collection_status'] = array($filter_status);
            $this->design->assign('filter_status', $filter_status);
        }

        if (!($sort = $this->request->get('sort'))) {
            $sort = 'order_id_asc';
        }
        $this->design->assign('sort', $sort);
        $filter['sort'] = $sort;


        if ($page_count = $this->request->get('page_count')) {
            setcookie('page_count', $page_count, time() + 86400 * 30, '/');
            if ($page_count == 'all')
                $items_per_page = 10000;
            else
                $items_per_page = $page_count;

            $this->design->assign('page_count', $page_count);
        } elseif (!empty($_COOKIE['page_count'])) {
            if ($_COOKIE['page_count'] == 'all')
                $items_per_page = 10000;
            else
                $items_per_page = $_COOKIE['page_count'];

            $this->design->assign('page_count', $_COOKIE['page_count']);
        }


        $current_page = $this->request->get('page', 'integer');
        $current_page = max(1, $current_page);
        $this->design->assign('current_page_num', $current_page);
        $this->design->assign('items_per_page', $items_per_page);

        $contracts_count = $this->contracts->count_contracts($filter);

        $pages_num = ceil($contracts_count / $items_per_page);
        $this->design->assign('total_pages_num', $pages_num);
        $this->design->assign('total_orders_count', $contracts_count);

        $filter['page'] = $current_page;
        $filter['limit'] = $items_per_page;

        $filter['sort_workout'] = 1;

        foreach ($this->contracts->get_contracts($filter) as $con) {
            $order_ids[] = $con->order_id;
            $user_ids[] = $con->user_id;

            $date1 = new DateTime(date('Y-m-d', strtotime($con->return_date)));
            $date2 = new DateTime(date('Y-m-d'));

            $diff = $date2->diff($date1);
            $con->delay = $diff->days;

            $contracts[$con->id] = $con;
        }


        if (!empty($contracts)) {
            $contactpersons = array();
            foreach ($this->contactpersons->get_contactpersons(array('user_id' => $user_ids)) as $cp) {
                if (!isset($contactpersons[$cp->user_id]))
                    $contactpersons[$cp->user_id] = array();
                $contactpersons[$cp->user_id][] = $cp;
            }

            $comments = array();
            $managers = [];
            foreach ($this->comments->get_comments(array('order_id' => $order_ids, 'official' => $this->settings->display_only_official_comments)) as $com) {
                if (empty($com->contactperson_id)) {
                    if (!isset($comments[$com->order_id]))
                        $comments[$com->order_id] = array();
                    $comments[$com->order_id][] = $com;
                    if (!isset($managers[$com->order_id]))
                        $managers[$com->order_id] = $com->manager_id;

                }
            }


            foreach ($this->managers->get_managers(['id' => $managers]) as $manager) {
                foreach ($managers as $key => &$manager_id) {
                    if ($manager_id == $manager->id) {
                        $comments[$key][0]->user_name = $manager->name;
                    }
                }
            }

//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($comments);echo '</pre><hr />';
            $orders = array();
            foreach ($this->orders->get_orders(array('id' => $order_ids)) as $o) {
                if (isset($comments[$o->order_id]))
                    $o->comments = $comments[$o->order_id];

                $orders[$o->order_id] = $o;
//                if (isset($contracts[$o->contract_id]))
//                    $contracts[$o->contract_id]->order = $o;
            }


            foreach ($contracts as $contract) {
                if (isset($orders[$contract->order_id])) {
                    $contract->order = $orders[$contract->order_id];

                    $contract->region_key = $this->addContract([
                        'Regregion' => $contract->order->Regregion,
                        'Regcity' => $contract->order->Regcity,
                    ]);
                }

                if (!empty($contactpersons[$contract->user_id])) {
                    $contract->contactpersons = $contactpersons[$contract->user_id];
                } else {

                    if (!empty($contract->order->contact_person_name) && !empty($contract->order->contact_person_phone)) {
                        $new_contactperson = array(
                            'user_id' => $contract->user_id,
                            'name' => $contract->order->contact_person_name,
                            'relation' => $contract->order->contact_person_relation,
                            'phone' => $contract->order->contact_person_phone,
                        );
                        $new_contactperson['id'] = $this->contactpersons->add_contactperson($new_contactperson);

                        $contract->contactpersons[] = (object)$new_contactperson;
                    }

                    if (!empty($contract->order->contact_person2_name) && !empty($contract->order->contact_person2_phone)) {
                        $new_contactperson2 = array(
                            'user_id' => $contract->user_id,
                            'name' => $contract->order->contact_person2_name,
                            'relation' => $contract->order->contact_person2_relation,
                            'phone' => $contract->order->contact_person2_phone,
                        );
                        $new_contactperson2['id'] = $this->contactpersons->add_contactperson($new_contactperson2);
                        $contract->contactpersons[] = (object)$new_contactperson2;
                    }

                }


                if (!empty($contract->order) && !empty($contract->order->time_zone))
                    $contract->client_time = date('Y-m-d H:i:s', time() + $contract->order->time_zone * 3600);
                else
                    $contract->client_time = date('Y-m-d H:i:s');


                $clock = date('H', strtotime($contract->client_time));
                $weekday = date('N', strtotime($contract->client_time));


                if (in_array($weekday, [6, 7])) {
                    if ($clock < 9 || $clock > 20)
                        $contract->client_time_warning = true;
                } else {
                    if ($clock < 8 || $clock > 21)
                        $contract->client_time_warning = true;
                }

                $user = $this->db->users->get_user($contract->user_id);
                $shift = $user->time_zone;

                if (empty($shift)) {
                    $time = 0;
                    $user_time = date('Y-m-d H:i:s', time() + $time * 3600);
                } else {
                    $user_time = date('Y-m-d H:i:s', time() + $shift * 3600);
                }

                $contract->user_time = DateTime::createFromFormat("Y-m-d H:i:s", $user_time);
                $contract->user_time = $contract->user_time->format('H:i');

                $contract->order->last_activity = date('d.m.Y H:i:s', $contract->order->last_activity);

            }
            // echo '<pre>';print_r ($contracts[0]->order->Regregion);echo '</pre>';
            // echo '<pre>';print_r ($contracts[102287]->order->Regregion);echo '</pre>';
            //  echo '<pre>';print_r ($contracts);echo '</pre>';

            $this->design->assign('contracts', $contracts);

        }

        $fetch_api_regions = $this->dadata->fetch_clean_api('address', $this->regions);


        if (array_key_exists(0, $fetch_api_regions)) {
            $fetch_api_cities = $this->dadata->fetch_clean_api('address', $this->cities);

            foreach ($this->contract_dates as $key => $data) {
                if ($fetch_api_regions[$data['region_key']]['timezone']) {
                    $data['date'] = $this->helpers->get_current_time($fetch_api_regions[$data['region_key']]['timezone'], 'H:i:s');
                } else {
                    $data['date'] = $this->helpers->get_current_time($fetch_api_cities[$data['city_key']]['timezone'], 'H:i:s');
                }
            }
        } else {
            $key = 0;
            foreach ($contracts as $contract) {
                $this->contract_dates[$key]['date'] = (new DateTime($contract->client_time))->format('H:i:s');
                $user = $this->users->get_user($contract->user_id);
                $shift = str_replace("UTC+", "", $user->time_zone);
                $client_time = date('H:i:s', time() + $shift * 3600);
                $this->contract_dates[$key]['date'] = $client_time;
                $key++;
            }

        }


        $collection_statuses = CollectorPeriodsORM::get();
        $sortCollectors = [];

        foreach ($collection_statuses as $status) {
            $sortCollectors[$status->id] = $status->name;
        }

        $this->design->assign('collection_statuses', $sortCollectors);
        $this->design->assign('contract_dates', $this->contract_dates);

        $risk_op = ['complaint' => 'Жалоба', 'bankrupt' => 'Банкрот', 'refusal' => 'Отказ от взаимодействия',
            'refusal_thrd' => 'Отказ от взаимодействия с 3 лицами', 'death' => 'Смерть', 'anticollectors' => 'Антиколлекторы', 'mls' => 'Находится в МЛС',
            'bankrupt_init' => 'Инициировано банкротство', 'fraud' => 'Мошенничество', 'canicule' => "Кредитные каникулы"];

        $user_risk_op = $this->UsersRisksOperations->get_records();

        $this->design->assign('user_risk_op', $user_risk_op);
        $this->design->assign('risk_op', $risk_op);

        $collector_tags = array();
        foreach ($this->collector_tags->get_tags() as $ct)
            $collector_tags[$ct->id] = $ct;
        $this->design->assign('collector_tags', $collector_tags);

//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($collection_statuses);echo '</pre><hr />';

        if ($this->request->get('download') == 'excel') {

            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $spreadsheet->getDefaultStyle()->getFont()->setName('Calibri')->setSize(12);

            $sheet = $spreadsheet->getActiveSheet();
            $sheet->getDefaultRowDimension()->setRowHeight(20);
            $sheet->getColumnDimension('A')->setWidth(20);
            $sheet->getColumnDimension('B')->setWidth(40);
            $sheet->getColumnDimension('C')->setWidth(18);
            $sheet->getColumnDimension('D')->setWidth(35);
            $sheet->getColumnDimension('E')->setWidth(15);
            $sheet->getColumnDimension('F')->setWidth(15);
            $sheet->getColumnDimension('G')->setWidth(15);
            $sheet->getColumnDimension('H')->setWidth(15);
            $sheet->getColumnDimension('I')->setWidth(20);
            $sheet->getColumnDimension('J')->setWidth(20);
            $sheet->getColumnDimension('K')->setWidth(20);
            $sheet->getColumnDimension('L')->setWidth(20);
            $sheet->getColumnDimension('M')->setWidth(20);
            $sheet->getColumnDimension('N')->setWidth(20);

            $sheet->setCellValue('A1', 'Пользователь');
            $sheet->setCellValue('B1', 'ID');
            $sheet->setCellValue('C1', 'Статус');
            $sheet->setCellValue('D1', 'ФИО');
            $sheet->setCellValue('E1', 'Дата рождения');
            $sheet->setCellValue('F1', 'Последнее посещение');
            $sheet->setCellValue('G1', 'ОД, руб');
            $sheet->setCellValue('H1', '%, руб');
            $sheet->setCellValue('I1', 'Итог, руб');
            $sheet->setCellValue('J1', 'Время клиента');
            $sheet->setCellValue('K1', 'Телефон');
            $sheet->setCellValue('L1', 'Просрочен');
            $sheet->setCellValue('M1', 'Дата платежа');
            $sheet->setCellValue('N1', 'Тег');

            $i = 2;

            foreach ($contracts as $contract) {
                $lastname = $contract->order->lastname;
                $fristname = $contract->order->firstname;
                $patronymic = $contract->order->patronymic;

                $fio = "$lastname $fristname $patronymic";

                $sheet->setCellValue('A' . $i, $managers[$contract->collection_manager_id]->name);
                $sheet->setCellValue('B' . $i, $contract->order->order_id);
                $sheet->setCellValue('C' . $i, $sortCollectors[$contract->collection_status]);
                $sheet->setCellValue('D' . $i, $fio);
                $sheet->setCellValue('E' . $i, $contract->order->birth);
                $sheet->setCellValue('F' . $i, $contract->order->last_activity);
                $sheet->setCellValue('G' . $i, $contract->loan_body_summ);
                $sheet->setCellValue('H' . $i, $contract->loan_percents_summ + $contract->loan_charge_summ + $contract->loan_peni_summ);
                $sheet->setCellValue('I' . $i, $contract->loan_body_summ + $contract->loan_percents_summ + $contract->loan_charge_summ + $contract->loan_peni_summ);
                $sheet->setCellValue('J' . $i, $contract->user_time);
                $sheet->setCellValue('K' . $i, $contract->order->phone_mobile);
                $sheet->setCellValue('L' . $i, $contract->delay);
                $sheet->setCellValue('M' . $i, $contract->return_date);
                $sheet->setCellValue('N' . $i, $collector_tags[$contract->order->contact_status]->name);

                $i++;
            }

            $filename = 'Collections.xlsx';
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save($this->config->root_dir . $filename);
            header('Location:' . $this->config->root_url . '/' . $filename);
            exit;
        }

        return $this->design->fetch('collector_contracts.tpl');
    }

    private function set_collection_manager_action()
    {
        $contract_id = $this->request->post('contract_id', 'integer');
        $manager_id = $this->request->post('manager_id', 'integer');

        $this->contracts->update_contract($contract_id, array('collection_manager_id' => $manager_id));

        $this->json_output(array('success' => 1));
        exit;
    }

    private function set_workout_action()
    {
        $contract_id = $this->request->post('contract_id', 'integer');
        $workout = $this->request->post('workout', 'integer');

        $res = $this->contracts->update_contract($contract_id, array('collection_workout' => $workout));

        $this->json_output(array('success' => $res));
        exit;
    }

    private function set_hide_prolongation_action()
    {
        $contract_id = $this->request->post('contract_id', 'integer');
        $hide_prolongation = $this->request->post('hide_prolongation', 'integer');

        $res = $this->contracts->update_contract($contract_id, array('hide_prolongation' => $hide_prolongation));

        $this->json_output(array('success' => $res));
        exit;
    }

    private function set_sud_label_action()
    {
        $sud = $this->request->post('sud', 'integer');
        $contract_id = $this->request->post('contract_id', 'integer');

        $old_contract = $this->contracts->get_contract((int)$contract_id);

        $this->contracts->update_contract($contract_id, array('sud' => $sud));

        if (!empty($sud)) {
            $user = $this->users->get_user($old_contract->user_id);
            $sudblock_contract = array(
                'number' => $old_contract->number,
                'first_number' => $old_contract->number,
                'user_id' => $old_contract->user_id,
                'contract_id' => $old_contract->id,
                'firstname' => $user->firstname,
                'lastname' => $user->lastname,
                'patronymic' => $user->patronymic,
                'created' => date('Y-m-d H:i:s'),
                'status' => 1,
                'loan_summ' => $old_contract->loan_body_summ,
                'total_summ' => $old_contract->loan_body_summ + $old_contract->loan_percents_summ + $old_contract->loan_charge_summ + $old_contract->loan_peni_summ,
                'region' => trim($user->Regregion . ' ' . $user->Regregion_shorttype),
                'provider' => 'Наличное плюс',
            );
            if ($tribunal = $this->tribunals->find_tribunal($user->Regregion))
                $sudblock_contract['tribunal'] = $tribunal->sud;

            $this->sudblock->add_contract($sudblock_contract);
        }


        $this->changelogs->add_changelog(array(
            'manager_id' => $this->manager->id,
            'created' => date('Y-m-d H:i:s'),
            'type' => 'collection_status',
            'old_values' => serialize(array(
                'sud' => $old_contract->sud,
            )),
            'new_values' => serialize(array(
                'sud' => $sud,
            )),
            'order_id' => $old_contract->order_id,
            'user_id' => $old_contract->user_id,
        ));

        $this->json_output(array('success' => 1));
        exit;
    }

    private function set_collection_status_action()
    {
        $contract_id = $this->request->post('contract_id', 'integer');
        $status_id = $this->request->post('status_id', 'integer');

        $old_contract = $this->contracts->get_contract((int)$contract_id);

        $this->contracts->update_contract($contract_id, array('collection_status' => $status_id, 'collection_handchange' => 1));

        $this->changelogs->add_changelog(array(
            'manager_id' => $this->manager->id,
            'created' => date('Y-m-d H:i:s'),
            'type' => 'collection_status',
            'old_values' => serialize(array(
                'collection_status' => $old_contract->collection_status,
                'collection_handchange' => $old_contract->collection_handchange
            )),
            'new_values' => serialize(array(
                'collection_status' => $status_id,
                'collection_handchange' => 1
            )),
            'order_id' => $old_contract->order_id,
            'user_id' => $old_contract->user_id,
        ));


        $this->json_output(array('success' => 1));
        exit;
    }

    private function contactperson_comment_action()
    {
        $comment = trim($this->request->post('text'));
        $contactperson_id = $this->request->post('contactperson_id', 'integer');
        $order_id = $this->request->post('order_id', 'integer');

        if ($contactperson = $this->contactpersons->get_contactperson($contactperson_id)) {
            if (!empty($comment)) {
                $this->contactpersons->update_contactperson($contactperson_id, array('comment' => $comment));
                $this->comments->add_comment(array(
                    'order_id' => $order_id,
                    'user_id' => $contactperson->user_id,
                    'contactperson_id' => $contactperson_id,
                    'manager_id' => $this->manager->id,
                    'text' => $comment,
                    'created' => date('Y-m-d H:i:s'),
                    'sent' => 0,
                    'status' => 0,
                ));
                $this->json_output(array('success' => 1));
            } else {
                $this->json_output(array('error' => 'Напишите комментарий'));

            }
        } else {
            $this->json_output(array('error' => 'Контакное лицо не найдено'));
        }
        exit;
    }

    private function send_sms_action()
    {
        $user_id = $this->request->post('user_id');
        $order_id = $this->request->post('order_id');
        $template_id = $this->request->post('template_id');
        $text_sms = $this->request->post('text_sms');

        $user = $this->users->get_user($user_id);
        $manager = $this->managers->get_manager($this->manager->id);

        $limitCommuniactions = $this->communications->check_user($user_id);

        if (!$limitCommuniactions) {
            $this->json_output(array('error' => 'Превышен лимит коммуникаций'));
            exit;
        }

        if (!empty($text_sms)) {
            $template = $text_sms;
            $template .= " ООО МКК Финансовый Аспект ecozaym24.ru/lk/login";
            $template .= " $manager->phone ";
        }

        if (!empty($template_id)) {

            $template = $this->sms->get_template($template_id);
            $template = $template->template;
            $template .= " ООО МКК Финансовый Аспект ecozaym24.ru/lk/login";
            $template .= " $manager->phone ";
        }

        if (!empty($order_id)) {
            $order = $this->orders->get_order($order_id);

            if ($order->contract_id) {
                $code = $this->helpers->c2o_encode($order->contract_id);
                $payment_link = $this->config->front_url . '/p/' . $code;
                $contract = $this->contracts->get_contract($order->contract_id);
                $osd_sum = $contract->loan_body_summ + $contract->loan_percents_summ;

                $str_params =
                    [
                        '{$payment_link}',
                        '$firstname',
                        '$fio',
                        '$prolongation_sum',
                        '$final_sum'
                    ];

                $str_replace =
                    [
                        $payment_link,
                        $user->firstname,
                        "$user->lastname $user->firstname $user->patronymic",
                        $contract->loan_percents_summ,
                        $osd_sum
                    ];

                $template = str_replace($str_params, $str_replace, $template);
            }
        }

        $resp = $this->sms->send(
        /*'79276928586'*/
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
            'manager_id' => $this->manager->id,
            'created' => date('Y-m-d H:i:s'),
            'type' => 'send_sms',
            'old_values' => '',
            'new_values' => $template,
            'user_id' => $user->id,
            'order_id' => $order_id,
        ));

        $this->Communications->add_communication(array(
            'user_id' => $user_id,
            'manager_id' => $this->manager->id,
            'created' => date('Y-m-d H:i:s'),
            'type' => 'sms',
            'content' => $template,
            'outer_id' => 0,
            'from_number' => '',
            'to_number' => $user->phone_mobile,
            'yuk' => 0,
            'result' => ''
        ));
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($resp);echo '</pre><hr />';
        $this->json_output(array('success' => true));
    }

    private function distribute_action()
    {
        $managers = $this->request->post('managers');
        $contracts = $this->request->post('contracts');
        $type = $this->request->post('type');

        $tmp_name = $_FILES['file']['tmp_name'];

        $all_managers = array();
        foreach ($this->managers->get_managers() as $m)
            $all_managers[$m->id] = $m;

        if (empty($managers)) {
            $this->json_output(array('error' => 'Нет пользователей для распределения'));
        } elseif ($type == 'null') {
            $this->json_output(array('error' => 'Выберите вид распределения'));
        } elseif (empty($contracts) && $type != 'optional' && $type != 'file') {
            $this->json_output(array('error' => 'Нет договоров для распределения'));
        } elseif (empty($tmp_name) && $type == 'file') {
            $this->json_output(array('error' => 'Не выбран файл для распределения'));
        } else {
            switch ($type):

                case 'file':
                    
                    $format = \PhpOffice\PhpSpreadsheet\IOFactory::identify($tmp_name);
            
                    if($format == 'Csv'){
                        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
                        $reader->setInputEncoding('Windows-1251');
                        $reader->setDelimiter(';');
                        $reader->setEnclosure('');
                        $reader->setSheetIndex(0);
                    }else{
                        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($format);
                    }
            
                    $spreadsheet = $reader->load($tmp_name);
            
                    $active_sheet = $spreadsheet->getActiveSheet();
            
                    $first_row = 2;
                    $last_row = $active_sheet->getHighestRow();
            
                    $contracts = [];
                    for ($row = $first_row; $row <= $last_row; $row++) {
                        
                        $query = $this->db->placehold("
                            SELECT * 
                            FROM __contracts
                            WHERE number = ?
                            ORDER BY id DESC
                        ", mb_strtoupper($active_sheet->getCell('A' . $row)->getValue()));
                        $this->db->query($query);
                        $result = $this->db->result();
                        $contracts[] = $result->id;
                    }
                    
                case 'checked':
                case 'all':
                    $distribute = array();
                    $i = 0;
                    $count_managers = count($managers);

                    $timestamp_group_movings = date('Y-m-d H:i:s');

                    foreach ($contracts as $contract_id) {

                        
                        $contract = $this->contracts->get_contract($contract_id);
                        if($contract != ''){
                            $distribute[$contract_id] = $managers[$i];
                            // file_put_contents('c:\OSPanel\peop.txt',$contract);
                        }
                        $from_manager = $contract->collection_manager_id;

                        $this->contracts->update_contract($contract_id, array(
                            'collection_manager_id' => $managers[$i],
                            'collection_workout' => 0,
                            'collection_tag' => '',
                            'collection_handchange' => 1
                        ));

                        $manager = $this->managers->get_manager($managers[$i]);

                        $date1 = new DateTime(date('Y-m-d', strtotime($contract->return_date)));
                        $date2 = new DateTime(date('Y-m-d'));
                        $diff = $date2->diff($date1)->days;

                        $this->collections->add_moving(array(
                            'initiator_id' => (int)$this->manager->id,
                            'from_manager_id' => $from_manager,
                            'manager_id' => $managers[$i],
                            'contract_id' => $contract->id,
                            'from_date' => date('Y-m-d H:i:s'),
                            'summ_body' => $contract->loan_body_summ,
                            'summ_percents' => $contract->loan_percents_summ + $contract->loan_peni_summ + $contract->loan_charge_summ,
                            'collection_status' => $manager->collection_status_id,
                            'timestamp_group_movings' => $timestamp_group_movings,
                            'expired_days' => $diff,
                        ));

                        $this->UserContactStatuses->add_record(array(
                            'created' => date('Y-m-d H:i:s'),
                            'contract_id' => $contract_id,
                            'user_id' => $contract->user_id,
                            'collector_tag_id' => 0,
                            'manager_id' => $managers[$i]
                        ));

                        $i++;
                        if ($i == $count_managers)
                            $i = 0;
                    }

                    if (count($distribute) == 0) {
                        $this->json_output(array('error' => 'В выбраном для распределения файле нет номеров договоров'));
                    }

                    break;

                case 'optional':

                    if (!($period = $this->request->get('period')))
                        $period = 'all';

                    switch ($period):

                        case 'month':
                            $filter['inssuance_date_from'] = date('Y-m-01');
                            break;

                        case 'year':
                            $filter['inssuance_date_from'] = date('Y-01-01');
                            break;

                        case 'all':
                            $filter['inssuance_date_from'] = null;
                            $filter['inssuance_date_to'] = null;
                            break;

                        case 'optional':
                            $daterange = $this->request->get('daterange');
                            $filter_daterange = array_map('trim', explode('-', $daterange));
                            $filter['inssuance_date_from'] = date('Y-m-d', strtotime($filter_daterange[0]));
                            $filter['inssuance_date_to'] = date('Y-m-d', strtotime($filter_daterange[1]));
                            break;

                    endswitch;

                    if ($search = $this->request->get('search')) {
                        $filter['search'] = array_filter($search);
                    }

                    $filter['type'] = 'base';


                    if ($this->manager->role == 'collector') {
                        $filter['collection_status'] = array($this->manager->collection_status_id);
                        $filter['collection_manager_id'] = $this->manager->id;
                    } elseif (in_array($this->manager->role, array('developer', 'admin', 'senior collector'))) {
                        $filter['collection_status'] = array(1, 2, 3, 4, 5, 6, 7, 8, 9);
                        $filter['collection_manager_id'] = null;
                    } elseif ($this->manager->role == 'team_collector') {
                        $filter['collection_status'] = array(1, 2, 3, 4, 5, 6, 7, 8, 9);
                        $filter['collection_manager_id'] = $this->manager->team_id;
                    }

                    if ($filter_status = $this->request->get('status', 'integer')) {
                        $filter['collection_status'] = array($filter_status);
                    }

                    $filter['sort'] = 'total_desc';
                    $filter['limit'] = 10000;

                    foreach ($this->contracts->get_contracts($filter) as $con) {
                        $contracts[] = $con;
                    }

                    $contracts_count = count($contracts);
                    $quantity = $this->request->post('quantity');

                    if ($contracts_count > $quantity)
                        $coef = $contracts_count / $quantity;
                    else
                        $coef = 1;

                    $reset = 1;
                    $prepare_contracts = array();
                    $summ_coef = 0;
                    while (count($prepare_contracts) < $quantity) {
                        $current_index = intval($summ_coef);
                        $prepare_contracts[$current_index] = $contracts[$current_index];

                        $summ_coef += $coef;

                        if (intval($summ_coef) > $contracts_count - 1) {
                            $summ_coef = $reset;
                            $reset++;
                            if ($reset > 2) {
                                $summ_coef = 0;
                                $coef = 1;
                            }
                        }
                    }


                    $distribute = array();
                    $i = 0;
                    $count_managers = count($managers);
                    foreach ($prepare_contracts as $contract) {
                        $distribute[$contract->id] = $managers[$i];

                        $contract = $this->contracts->get_contract($contract_id);
                        $from_manager = $contract->collection_manager_id;

                        $this->contracts->update_contract($contract->id, array(
                            'collection_manager_id' => $managers[$i],
                            'collection_workout' => 0,
                            'collection_tag' => '',
                            'collection_handchange' => 1
                        ));

                        $this->users->update_user($contract->user_id, array('contact_status' => 0));
                        $manager = $this->managers->get_manager($managers[$i]);

                        $date1 = new DateTime(date('Y-m-d', strtotime($contract->return_date)));
                        $date2 = new DateTime(date('Y-m-d'));
                        $diff = $date2->diff($date1)->days;

                        $this->collections->add_moving(array(
                            'initiator_id' => (int)$this->manager->id,
                            'manager_id' => $managers[$i],
                            'from_manager_id' => $from_manager,
                            'contract_id' => $contract->id,
                            'from_date' => date('Y-m-d H:i:s'),
                            'summ_body' => $contract->loan_body_summ,
                            'summ_percents' => $contract->loan_percents_summ + $contract->loan_peni_summ + $contract->loan_charge_summ,
                            'collection_status' => $manager->collection_status_id,
                            'timestamp_group_movings' => $timestamp_group_movings,
                            'expired_days' => $diff,
                        ));

                        $this->UserContactStatuses->add_record(array(
                            'created' => date('Y-m-d H:i:s'),
                            'contract_id' => $contract_id,
                            'user_id' => $contract->user_id,
                            'collector_tag_id' => 0,
                            'manager_id' => $managers[$i]
                        ));

                        $i++;
                        if ($i == $count_managers)
                            $i = 0;
                    }


                    break;

            endswitch;


            $this->json_output(array('success' => '1', 'distribute' => $distribute));
        }
    }

    private function reset_action()
    {
        $contracts_to_reset = trim($this->request->post('contracts_to_reset'));
        $contracts_to_reset = substr($contracts_to_reset,0,-1);
        $contract_ids = explode(",", $contracts_to_reset);
        foreach ($contract_ids as $contract_id) {
            // $contract = $this->contracts->get_contract($contract_id);
            $this->contracts->update_contract($contract_id, array(
                'collection_workout' => 0,
            ));
        }

        $this->json_output(array('success' => '1', 'reset' => $reset));
    }
}