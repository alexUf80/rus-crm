<?php

ini_set('max_execution_time', 40);

error_reporting(-1);
ini_set('display_errors', 'On');

error_reporting(0);

use PhpOffice\PhpSpreadsheet\Style\Alignment;

class StatisticsController extends Controller
{
    public function fetch()
    {
        switch ($this->request->get('action', 'string')):

            case 'main':
                return $this->action_main();
                break;

            case 'report':
                return $this->action_report();
                break;

            case 'conversion':
                return $this->action_conversion();
                break;

            case 'expired':
                return $this->action_expired();
                break;

            case 'prolongation_contracts':
                return $this->action_prolongation_contracts();
                break;

            case 'free_pk':
                return $this->action_free_pk();
                break;

            case 'scorista_rejects':
                return $this->action_scorista_rejects();
                break;

            case 'contracts':
                return $this->action_contracts();
                break;

            case 'payments':
                return $this->action_payments();
                break;

            case 'eventlogs':
                return $this->action_eventlogs();
                break;

            case 'penalties':
                return $this->action_penalties();
                break;

            case 'dailyreports':
                return $this->action_dailyreports();
                break;

            case 'adservices':
                return $this->action_adservices();
                break;
            
            case 'adservices_osv':
                return $this->action_adservices_osv();
                break;

            case 'loan_portfolio':
                return $this->action_loan_portfolio();
                break;

            case 'sources':
                return $this->action_sources();
                break;

            case 'conversions':
                return $this->action_conversions();
                break;

            case 'orders':
                return $this->action_orders();
                break;

            case 'leadgens':
                return $this->action_leadgens();
                break;

            case 'reconciliation':
                return $this->action_reconciliation();
                break;
            
            case 'collectors_expired':
                return $this->action_collectors_expired();
                break;
            
            case 'recovery_rate':
                return $this->action_recovery_rate();
                break;

            case 'kd_click':
                return $this->action_kd_click();
                break;

            case 'sspv':
                return $this->action_sspv();
                break;
    
                
            default:
                return false;

        endswitch;

    }

    private function action_main()
    {
        return $this->design->fetch('statistics/main.tpl');
    }

    private function action_report()
    {
        $this->statistics->get_operative_report('2021-05-01', '2021-05-30');

        return $this->design->fetch('statistics/report.tpl');
    }

    private function action_conversion()
    {
        return $this->design->fetch('statistics/conversion.tpl');
    }

    private function action_expired()
    {
        $count_days = 5;
        $this->design->assign('count_days', $count_days);

        $this->db->query("
            SELECT *
            FROM __contracts AS c
            WHERE status IN (2, 4)
            AND DATE(c.return_date) < ?
            ORDER BY c.return_date DESC
        ", date('Y-m-d'));

        $contracts = array();
        $user_ids = array();
        $order_ids = array();
        foreach ($this->db->results() as $c) {
            $user_ids[] = $c->user_id;
            $order_ids[] = $c->order_id;


            $c->expired_period = intval((strtotime(date('Y-m-d')) - strtotime(date('Y-m-d', strtotime($c->return_date)))) / 86400);

            $contracts[$c->id] = $c;
        }

        $users = array();
        if (!empty($user_ids)) {
            foreach ($this->users->get_users(array('id' => $user_ids, 'limit' => 10000)) as $user) {
                $user_age = date_diff(date_create(date('Y-m-d', strtotime($user->birth))), date_create(date('Y-m-d')));
                $user->age = $user_age->y;
                $user->Regcode = $this->helpers->get_region_code($user->Regregion);

                $users[$user->id] = $user;
            }
        }

        $orders = array();
        if (!empty($order_ids)) {
            foreach ($this->orders->get_orders(array('id' => $order_ids, 'limit' => 10000)) as $order)
                $orders[$order->order_id] = $order;
        }

        $contract_payments = array();
        if ($operations = $this->operations->get_operations(array('type' => 'PAY', 'contract_id' => array_keys($contracts), 'date_from' => date('Y-m-01')))) {
            foreach ($operations as $op) {
                if (!isset($contract_payments[$op->contract_id]))
                    $contract_payments[$op->contract_id] = array();
                $contract_payments[$op->contract_id][] = $op;
            }
        }


        foreach ($contracts as $contract) {
            if (isset($users[$contract->user_id])) {
                $contract->user = $users[$contract->user_id];

                $contract->user->regAddr = AdressesORM::find($contract->user->regaddress_id);
                $contract->user->faktAddr = AdressesORM::find($contract->user->faktaddress_id);

                $this->design->assign('regAddr', $contract->user->regAddr->adressfull);
                $this->design->assign('faktAddr', $contract->user->faktAddr->adressfull);
            }
            if (isset($orders[$contract->order_id]))
                $contract->order = $orders[$contract->order_id];

            if ($contract->order->client_status == 'nk')
                $contract->client_status = 'НК';
            elseif ($contract->order->client_status == 'pk')
                $contract->client_status = 'ПК';
            elseif ($contract->order->client_status == 'rep')
                $contract->client_status = 'НК';
            else
                $contract->client_status = 'н/д';

            $contract->payment_last_month = 0;
            if (isset($contract_payments[$contract->id])) {
                $contract->contract_payments = $contract_payments[$contract->id];
                foreach ($contract_payments[$contract->id] as $contract_payment)
                    $contract->payment_last_month += $contract_payment->amount;
            }

            $this->db->query("
            SELECT created,
            amount
            FROM s_operations
            WHERE order_id = ?
            AND `type` = 'PAY'
            ORDER BY created DESC 
            LIMIT 1
            ", $contract->order_id);

            $contract->last_operation = $this->db->result();
            
            $query = $this->db->placehold("
            SELECT o.created,
            o.amount,
            t.loan_body_summ as loan_body_summ,
            t.loan_percents_summ as loan_percents_summ,
            t.loan_peni_summ as loan_peni_summ
            FROM s_operations o
            LEFT JOIN s_transactions t
            ON o.transaction_id = t.id
            WHERE order_id = ?
            AND o.`type` = 'PAY'
            ", $contract->order_id);
            $this->db->query($query);

            $pay_body_summ = 0;
            $pay_percents_summ = 0;
            $pay_peni_summ = 0;
            foreach ($this->db->results() as $op) {
                $pay_body_summ += $op->loan_body_summ;
                $pay_percents_summ += $op->loan_percents_summ;
                $pay_peni_summ += $op->loan_peni_summ;
            }
            $contract->pay_body_summ = $pay_body_summ;
            $contract->pay_percents_summ = $pay_percents_summ;
            $contract->pay_peni_summ = $pay_peni_summ;

            $date1 = new DateTime(date('Y-m-d', strtotime($contract->return_date)));
            $date2 = new DateTime(date('Y-m-d'));

            $diff = $date2->diff($date1);
            if ($date1 < $date2) {
                $contract->delay = $diff->days;
            }
            else{
                $contract->delay = 0;
            }


            $contacts = $this->Contactpersons->get_contactpersons(['user_id' => $contract->user->id]);
            $contract->user->contact_person_name = $contacts[0]->name;
            $contract->user->contact_person_phone = $contacts[0]->phone; 
        }


        $this->design->assign('contracts', $contracts);

        if ($this->request->get('download') == 'excel') {
            $filename = 'files/reports/Просроченные займы.xls';
            require $this->config->root_dir . 'PHPExcel/Classes/PHPExcel.php';

            $excel = new PHPExcel();

            $excel->setActiveSheetIndex(0);
            $active_sheet = $excel->getActiveSheet();

            $active_sheet->setTitle('Просроченные займы ');

            $excel->getDefaultStyle()->getFont()->setName('Calibri')->setSize(12);
            $excel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

            $active_sheet->getColumnDimension('A')->setWidth(15);
            $active_sheet->getColumnDimension('B')->setWidth(20);
            $active_sheet->getColumnDimension('C')->setWidth(20);
            $active_sheet->getColumnDimension('D')->setWidth(20);
            $active_sheet->getColumnDimension('E')->setWidth(20);
            $active_sheet->getColumnDimension('F')->setWidth(20);
            $active_sheet->getColumnDimension('G')->setWidth(10);
            $active_sheet->getColumnDimension('H')->setWidth(20);
            $active_sheet->getColumnDimension('I')->setWidth(15);
            $active_sheet->getColumnDimension('J')->setWidth(15);
            $active_sheet->getColumnDimension('K')->setWidth(15);
            $active_sheet->getColumnDimension('L')->setWidth(15);
            $active_sheet->getColumnDimension('M')->setWidth(15);
            $active_sheet->getColumnDimension('N')->setWidth(15);
            $active_sheet->getColumnDimension('O')->setWidth(15);
            $active_sheet->getColumnDimension('P')->setWidth(15);
            $active_sheet->getColumnDimension('Q')->setWidth(15);
            $active_sheet->getColumnDimension('R')->setWidth(15);
            $active_sheet->getColumnDimension('S')->setWidth(15);
            $active_sheet->getColumnDimension('T')->setWidth(15);
            $active_sheet->getColumnDimension('U')->setWidth(15);
            $active_sheet->getColumnDimension('V')->setWidth(15);
            $active_sheet->getColumnDimension('W')->setWidth(15);
            $active_sheet->getColumnDimension('X')->setWidth(15);
            $active_sheet->getColumnDimension('Y')->setWidth(15);
            $active_sheet->getColumnDimension('Z')->setWidth(15);
            $active_sheet->getColumnDimension('AA')->setWidth(15);
            $active_sheet->getColumnDimension('AB')->setWidth(15);
            $active_sheet->getColumnDimension('AС')->setWidth(15);
            $active_sheet->getColumnDimension('AD')->setWidth(15);
            $active_sheet->getColumnDimension('AE')->setWidth(15);
            $active_sheet->getColumnDimension('AF')->setWidth(15);

            $active_sheet->setCellValue('A1', '№ п/п');
            $active_sheet->setCellValue('B1', 'Номер / ID кредитного договора');
            $active_sheet->setCellValue('C1', 'Дата кредитного договора');
            $active_sheet->setCellValue('D1', 'Тип выдачи (онлайн/оффлайн)');
            $active_sheet->setCellValue('E1', 'ФИО');
            $active_sheet->setCellValue('F1', 'Дата рождения');//---
            $active_sheet->setCellValue('G1', 'Серия паспорта');//---
            $active_sheet->setCellValue('H1', 'Номер паспорта');//---
            $active_sheet->setCellValue('I1', 'Дата выдачи паспорта');
            $active_sheet->setCellValue('J1', 'Кем выдан паспорт');//---
            $active_sheet->setCellValue('K1', 'ИНН заемщика');//---
            $active_sheet->setCellValue('L1', 'Пол (М/Ж)');//---
            $active_sheet->setCellValue('M1', 'Место рождения');//---
            $active_sheet->setCellValue('N1', 'Адрес регистрации');//---
            $active_sheet->setCellValue('O1', 'Адрес (фактически)');//---
            $active_sheet->setCellValue('P1', 'Телефон');//---
            $active_sheet->setCellValue('Q1', 'Дата планового окончания кредитного договора');//---
            $active_sheet->setCellValue('R1', 'Срок договора, дней');//---
            $active_sheet->setCellValue('S1', 'Просрочка , дней');//---
            $active_sheet->setCellValue('T1', 'Сумма выданного кредита, руб.');//---
            $active_sheet->setCellValue('U1', 'Сумма платежей с момента выдачи кредита, руб. ');//---
            $active_sheet->setCellValue('V1', 'Сумма оплат основного долга, руб.');//---
            $active_sheet->setCellValue('W1', 'Сумма оплат процентов, руб.');//---
            $active_sheet->setCellValue('X1', 'Сумма оплат штрафов, руб.');//---
            $active_sheet->setCellValue('Y1', 'Сумма основного долга, руб.');//---
            $active_sheet->setCellValue('Z1', 'Сумма долга  проценты, руб.');//---
            $active_sheet->setCellValue('AA1', 'Сумма долга штрафы, руб.');//---
            
            $active_sheet->setCellValue('AB1', 'ОСЗ (Общая Сумма Задолженности), руб.');//---
            $active_sheet->setCellValue('AC1', 'Процентная ставка в день, %');//---
            $active_sheet->setCellValue('AD1', 'Полная стоимость кредита');//---
            $active_sheet->setCellValue('AE1', 'УИД');//---
            $active_sheet->setCellValue('AF1', 'Дата формирования реестра');//---

            $i = 2;
            foreach ($contracts as $contract) {
                $active_sheet->setCellValue('A' . $i, ($i-1));
                $active_sheet->setCellValue('B' . $i, $contract->number);
                $active_sheet->setCellValue('C' . $i, date('d.m.Y', strtotime($contract->inssuance_date)));
                $active_sheet->setCellValue('D' . $i, 'Онлайн');
                $active_sheet->setCellValue('E' . $i, $contract->user->lastname . ' ' . $contract->user->firstname . ' ' . $contract->user->patronymic);
                $active_sheet->setCellValue('F' . $i, date('d.m.Y', strtotime($contract->user->birth)));
                $active_sheet->setCellValueExplicit('G' . $i, substr($contract->user->passport_serial, 0, 4));
                $active_sheet->setCellValue('H' . $i, substr($contract->user->passport_serial, 5, 6));

                $active_sheet->setCellValue('I' . $i, date('d.m.Y', strtotime($contract->user->passport_date)));
                $active_sheet->setCellValue('J' . $i, $contract->user->passport_issued);
                $active_sheet->setCellValue('K' . $i, $contract->user->inn);
                if ($contract->user->gender == 'male')
                    $gender = 'Мужской';
                else
                    $gender =  'Женский';
                $active_sheet->setCellValue('L' . $i, $gender);
                $active_sheet->setCellValue('M' . $i, $contract->user->birth_place);//---
                $active_sheet->setCellValue('N' . $i, $contract->user->regAddr->adressfull);//---
                $active_sheet->setCellValue('O' . $i, $contract->user->faktAddr->adressfull);//---
                $active_sheet->setCellValue('P' . $i, $contract->user->phone_mobile);//---

                $dto = new DateTime($contract->inssuance_date);
                $dto->modify('+'.$contract->period.' days');
                $active_sheet->setCellValue('Q' . $i, $dto->format('d.m.Y'));//---
                $active_sheet->setCellValue('R' . $i, $contract->period);//---
                $active_sheet->setCellValue('S' . $i, $contract->delay);//---
                $active_sheet->setCellValue('T' . $i, $contract->amount);//---
                $active_sheet->setCellValue('U' . $i, ($contract->pay_body_summ + $contract->pay_percents_summ + $contract->pay_peni_summ));//---
                $active_sheet->setCellValue('V' . $i, $contract->pay_body_summ);//---
                $active_sheet->setCellValue('W' . $i, $contract->pay_percents_summ);//---
                $active_sheet->setCellValue('X' . $i, $contract->pay_peni_summ);//---
                $active_sheet->setCellValue('Y' . $i, $contract->loan_body_summ);//---
                $active_sheet->setCellValue('Z' . $i, $contract->loan_percents_summ);//---
                $active_sheet->setCellValue('AA' . $i, $contract->loan_peni_summ);//--
                $active_sheet->setCellValue('AB' . $i, ($contract->loan_body_summ + $contract->loan_percents_summ + $contract->loan_peni_summ));//---
                $active_sheet->setCellValue('AC' . $i, $contract->base_percent);//---
                $active_sheet->setCellValue('AD' . $i, ($contract->loan_body_summ + $contract->loan_percents_summ + $contract->loan_peni_summ + $contract->pay_body_summ + $contract->pay_percents_summ + $contract->pay_peni_summ));//---
                $active_sheet->setCellValue('AE' . $i, $contract->uid);//---
                $active_sheet->setCellValue('AF' . $i, date('d.m.Y'));//---          

                $i++;
            }

            $objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');

            $objWriter->save($this->config->root_dir . $filename);

            header('Location:' . $this->config->root_url . '/' . $filename);
            exit;
        }

        return $this->design->fetch('statistics/expired.tpl');
    }

    private function action_prolongation_contracts()
    {
        $count_days = 5;
        $this->design->assign('count_days', $count_days);

        $this->db->query("
            SELECT *
            FROM __contracts AS c
            WHERE status IN (2, 4)
            AND DATE(c.return_date) <= ?
            AND DATE(c.return_date) >= ?
            ORDER BY c.return_date ASC
        ", date('Y-m-d', time() + $count_days * 86400), date('Y-m-d'));

        $contracts = array();
        $user_ids = array();
        $order_ids = array();
        foreach ($this->db->results() as $c) {
            $user_ids[] = $c->user_id;
            $order_ids[] = $c->order_id;

            $contracts[$c->id] = $c;
        }

        $users = array();
        if (!empty($user_ids)) {
            foreach ($this->users->get_users(array('id' => $user_ids, 'limit' => 10000)) as $user)
                $users[$user->id] = $user;
        }

        $orders = array();
        if (!empty($order_ids)) {
            foreach ($this->orders->get_orders(array('id' => $order_ids, 'limit' => 10000)) as $order)
                $orders[$order->order_id] = $order;
        }

        foreach ($contracts as $contract) {
            if (isset($users[$contract->user_id]))
                $contract->user = $users[$contract->user_id];
            if (isset($orders[$contract->order_id]))
                $contract->order = $orders[$contract->order_id];


//            $contract->prolongation_summ;
//            $contract->close_summ ;
        }

        $this->design->assign('contracts', $contracts);

        if ($this->request->get('download') == 'excel') {
            $filename = 'files/reports/К оплате в ближайшие 5 дней.xls';
            require $this->config->root_dir . 'PHPExcel/Classes/PHPExcel.php';

            $excel = new PHPExcel();

            $excel->setActiveSheetIndex(0);
            $active_sheet = $excel->getActiveSheet();

            $active_sheet->setTitle('К оплате в ближайшие ' . $count_days . ' дней');

            $excel->getDefaultStyle()->getFont()->setName('Calibri')->setSize(12);
            $excel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

            $active_sheet->getColumnDimension('A')->setWidth(15);
            $active_sheet->getColumnDimension('B')->setWidth(20);
            $active_sheet->getColumnDimension('C')->setWidth(20);
            $active_sheet->getColumnDimension('D')->setWidth(20);
            $active_sheet->getColumnDimension('E')->setWidth(20);
            $active_sheet->getColumnDimension('F')->setWidth(20);
            $active_sheet->getColumnDimension('G')->setWidth(10);
            $active_sheet->getColumnDimension('H')->setWidth(20);
            $active_sheet->getColumnDimension('I')->setWidth(15);
            $active_sheet->getColumnDimension('J')->setWidth(15);


            $active_sheet->setCellValue('A1', 'Дата платежа');
            $active_sheet->setCellValue('B1', 'Фамилия');
            $active_sheet->setCellValue('C1', 'Имя');
            $active_sheet->setCellValue('D1', 'Отчество');
            $active_sheet->setCellValue('E1', 'Номер телефона');
            $active_sheet->setCellValue('F1', 'Город');//---
            $active_sheet->setCellValue('G1', 'Всего продлений сделано');
            $active_sheet->setCellValue('H1', 'ID договора');//---
            $active_sheet->setCellValue('I1', 'Сумма к погашению');//---
            $active_sheet->setCellValue('J1', 'Сумма к продлению');//---

            $i = 2;
            foreach ($contracts as $contract) {
                $active_sheet->setCellValue('A' . $i, date('d.m.Y', strtotime($contract->return_date)));
                $active_sheet->setCellValue('B' . $i, $contract->user->lastname);
                $active_sheet->setCellValue('C' . $i, $contract->user->firstname);
                $active_sheet->setCellValue('D' . $i, $contract->user->patronymic);
                $active_sheet->setCellValue('E' . $i, $contract->user->phone_mobile);
                $active_sheet->setCellValue('F' . $i, $contract->user->Regregion);
                $active_sheet->setCellValue('G' . $i, $contract->prolongation);
                $active_sheet->setCellValue('H' . $i, $contract->number);
                $active_sheet->setCellValue('I' . $i, $contract->loan_body_summ + $contract->loan_percents_summ);
                $active_sheet->setCellValue('J' . $i, $contract->loan_percents_summ + $this->settings->prolongation_amount);


                $i++;
            }

            $objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');

            $objWriter->save($this->config->root_dir . $filename);

            header('Location:' . $this->config->root_url . '/' . $filename);
            exit;
        }

        return $this->design->fetch('statistics/prolongation_contracts.tpl');
    }

    private function action_free_pk()
    {
        return $this->design->fetch('statistics/free_pk.tpl');
    }

    private function action_scorista_rejects()
    {
        $reasons = array();
        foreach ($this->reasons->get_reasons() as $reason)
            $reasons[$reason->id] = $reason;
        $this->design->assign('reasons', $reasons);


        if ($daterange = $this->request->get('daterange')) {
            list($from, $to) = explode('-', $daterange);

            $date_from = date('Y-m-d', strtotime($from));
            $date_to = date('Y-m-d', strtotime($to));

            $this->design->assign('date_from', $date_from);
            $this->design->assign('date_to', $date_to);
            $this->design->assign('from', $from);
            $this->design->assign('to', $to);

            $query_reason = '';
            if ($filter_reason = $this->request->get('reason_id')) {
                if ($filter_reason != 'all') {
                    $query_reason = $this->db->placehold("AND o.reason_id = ?", (int)$filter_reason);
                }

                $this->design->assign('filter_reason', $filter_reason);
            }

            $query = $this->db->placehold("
                SELECT
                    o.id AS order_id,
                    o.date,
                    o.reason_id,
                    o.reject_reason,
                    o.user_id,
                    o.manager_id,
                    o.utm_source,
                    o.client_status,
                    u.lastname,
                    u.firstname,
                    u.patronymic,
                    u.phone_mobile,
                    u.email
                FROM __orders AS o
                LEFT JOIN __users AS u
                ON u.id = o.user_id
                WHERE o.status IN (3, 8)
                $query_reason
                AND DATE(o.date) >= ?
                AND DATE(o.date) <= ?
                GROUP BY order_id
            ", $date_from, $date_to);
            $this->db->query($query);

            $orders = array();
            foreach ($this->db->results() as $o)
                $orders[$o->order_id] = $o;

            if (!empty($orders))
                if ($scorings = $this->scorings->get_scorings(array('order_id' => array_keys($orders), 'type' => 'scorista')))
                    foreach ($scorings as $scoring)
                        $orders[$scoring->order_id]->scoring = $scoring;


            switch ($this->request->get('scoring')):

                case '499-':
                    foreach ($orders as $key => $order)
                        if (empty($order->scoring->scorista_ball) || $order->scoring->scorista_ball > 499)
                            unset($orders[$key]);
                    break;

                case '500-549':
                    foreach ($orders as $key => $order)
                        if (empty($order->scoring->scorista_ball) || $order->scoring->scorista_ball < 500 || $order->scoring->scorista_ball > 549)
                            unset($orders[$key]);
                    break;

                case '550+':
                    foreach ($orders as $key => $order)
                        if (empty($order->scoring->scorista_ball) || $order->scoring->scorista_ball < 550)
                            unset($orders[$key]);
                    break;

            endswitch;
            $this->design->assign('filter_scoring', $this->request->get('scoring'));


            if ($this->request->get('download') == 'excel') {
                $managers = array();
                foreach ($this->managers->get_managers() as $m)
                    $managers[$m->id] = $m;

                $filename = 'files/reports/Отчет по заявкам.xls';
                require $this->config->root_dir . 'PHPExcel/Classes/PHPExcel.php';

                $excel = new PHPExcel();

                $excel->setActiveSheetIndex(0);
                $active_sheet = $excel->getActiveSheet();

                $active_sheet->setTitle("Выдачи " . $from . "-" . $to);

                $excel->getDefaultStyle()->getFont()->setName('Calibri')->setSize(12);
                $excel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

                $active_sheet->getColumnDimension('A')->setWidth(15);
                $active_sheet->getColumnDimension('B')->setWidth(15);
                $active_sheet->getColumnDimension('C')->setWidth(45);
                $active_sheet->getColumnDimension('D')->setWidth(20);
                $active_sheet->getColumnDimension('E')->setWidth(20);
                $active_sheet->getColumnDimension('F')->setWidth(10);
                $active_sheet->getColumnDimension('G')->setWidth(10);
                $active_sheet->getColumnDimension('H')->setWidth(30);
                $active_sheet->getColumnDimension('I')->setWidth(15);

                $active_sheet->setCellValue('A1', 'Дата');
                $active_sheet->setCellValue('B1', 'Заявка');
                $active_sheet->setCellValue('C1', 'ФИО');
                $active_sheet->setCellValue('D1', 'Телефон');
                $active_sheet->setCellValue('E1', 'Email');
                $active_sheet->setCellValue('F1', 'Cегмент клиента');
                $active_sheet->setCellValue('G1', 'Менеджер');//---
                $active_sheet->setCellValue('H1', 'Причина');
                $active_sheet->setCellValue('I1', 'Скориста');//---
                $active_sheet->setCellValue('J1', 'Источник');//---

                $i = 2;
                foreach ($orders as $contract) {
                    $active_sheet->setCellValue('A' . $i, date('d.m.Y', strtotime($contract->date)));
                    $active_sheet->setCellValue('B' . $i, $contract->order_id);
                    $active_sheet->setCellValue('C' . $i, $contract->lastname . ' ' . $contract->firstname . ' ' . $contract->patronymic);
                    $active_sheet->setCellValue('D' . $i, $contract->phone_mobile);
                    $active_sheet->setCellValue('E' . $i, $contract->email);

                    $order_client_status = '';
                    if ($contract->client_status == 'pk'){
                        $order_client_status='ПК';
                    }
                    elseif ($contract->client_status == 'crm'){
                        $order_client_status='ПК CRM';
                    }
                    elseif ($contract->client_status == 'rep'){
                        $order_client_status='Повтор';
                    }
                    elseif ($contract->client_status == 'nk'){
                        $order_client_status='Новая';
                    }
                    $active_sheet->setCellValue('F' . $i, $order_client_status);
                    $active_sheet->setCellValue('G' . $i, $managers[$contract->manager_id]->name);
                    $active_sheet->setCellValue('H' . $i, ($contract->reason_id ? $reasons[$contract->reason_id]->admin_name : $contract->reject_reason));
                    $active_sheet->setCellValue('I' . $i, empty($contract->scoring) ? '' : $contract->scoring->scorista_ball);
                    $active_sheet->setCellValue('J' . $i, $contract->utm_source);


                    $i++;
                }

                $objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');

                $objWriter->save($this->config->root_dir . $filename);

                header('Location:' . $this->config->root_url . '/' . $filename);
                exit;
            }


            $this->design->assign('orders', $orders);
        }

        return $this->design->fetch('statistics/scorista_rejects.tpl');
    }

    private function action_contracts()
    {
        if ($daterange = $this->request->get('daterange')) {
            list($from, $to) = explode('-', $daterange);

            $date_from = date('Y-m-d', strtotime($from));
            $date_to = date('Y-m-d', strtotime($to));

            $this->design->assign('date_from', $date_from);
            $this->design->assign('date_to', $date_to);
            $this->design->assign('from', $from);
            $this->design->assign('to', $to);

// сделайте выгрузку в эксель, пожалуйста, по всем выданным займам:
// дата - номер договора - ФИО+ДР - сумма - ПК/НК.
            $query = $this->db->placehold("
                SELECT
                    c.id AS contract_id,
                    c.order_id AS order_id,
                    c.number,
                    c.inssuance_date AS date,
                    c.amount,
                    c.user_id,
                    c.status,
                    c.collection_status,
                    c.sold,
                    c.return_date,
                    c.close_date,
                    o.client_status,
                    o.date AS order_date,
                    o.manager_id,
                    o.period,
                    o.utm_source,
                    u.lastname,
                    u.firstname,
                    u.patronymic,
                    u.phone_mobile,
                    u.email,
                    u.birth,
                    u.pdn,
                    u.UID AS uid
                FROM __contracts AS c
                LEFT JOIN __users AS u
                ON u.id = c.user_id
                LEFT JOIN __orders AS o
                ON o.id = c.order_id
                WHERE c.status IN (2, 3, 4, 7)
                AND c.type = 'base'
                AND DATE(c.inssuance_date) >= ?
                AND DATE(c.inssuance_date) <= ?
                ORDER BY inssuance_date
            ", $date_from, $date_to);
            $this->db->query($query);

            $contracts = array();
            foreach ($this->db->results() as $c){

                $date1 = new DateTime(date('Y-m-d', strtotime($c->return_date)));
                $date2 = new DateTime(date('Y-m-d', strtotime($c->close_date)));

                $diff = $date2->diff($date1);
                if ($c->close_date) {
                    if ($c->return_date > $c->close_date)
                        $c->expired_days = -$diff->days;
                    else
                        $c->expired_days = $diff->days;
                }

                $contracts[$c->contract_id] = $c;
            }

            foreach ($contracts as $c) {
                if (empty($c->client_status)) {
                    $client_contracts = $this->contracts->get_contracts(array(
                        'user_id' => $c->user_id,
                        'status' => 3,
                        'close_date_to' => $c->date
                    ));
                    if (!empty($client_contracts)) {
                        $this->orders->update_order($c->order_id, array('client_status' => 'crm'));
                    } else {
                        /*
                        $loan_history = $this->soap1c->get_client_credits($c->uid);
                        if (!empty($loan_history))
                        {
                            $have_close_loans = 0;
                            foreach ($loan_history as $lh)
                            {
                                if (!empty($lh->ДатаЗакрытия))
                                {
                                    if (strtotime($lh->ДатаЗакрытия) < strtotime($c->date))
                                    {
                                        $have_close_loans = 1;
                                        $this->orders->update_order($c->order_id, array('client_status' => 'pk'));
                                    }
                                }
                            }
                        }
                        */
                        if (empty($have_close_loans)) {
                            $have_old_orders = 0;
                            $orders = $this->orders->get_orders(array('user_id' => $c->user_id, 'date_to' => $c->date));
                            foreach ($orders as $order) {
                                if ($order->order_id != $c->order_id) {
                                    $have_old_orders = 1;
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump('$order', $order);echo '</pre><hr />';
                                }
                            }

                            if (empty($have_old_orders)) {
                                $this->orders->update_order($c->order_id, array('client_status' => 'nk'));
                            } else {
                                $this->orders->update_order($c->order_id, array('client_status' => 'rep'));
                            }
                        }
                    }

                }
            }

            $statuses = $this->contracts->get_statuses();
            $this->design->assign('statuses', $statuses);

            $collection_statuses = $this->contracts->get_collection_statuses();
            $this->design->assign('collection_statuses', $collection_statuses);


            if ($this->request->get('download') == 'excel') {
                $managers = array();
                foreach ($this->managers->get_managers() as $m)
                    $managers[$m->id] = $m;

                $filename = 'files/reports/Отчет по договорам.xls';
                require $this->config->root_dir . 'PHPExcel/Classes/PHPExcel.php';

                $excel = new PHPExcel();

                $excel->setActiveSheetIndex(0);
                $active_sheet = $excel->getActiveSheet();

                $active_sheet->setTitle("Выдачи " . $from . "-" . $to);

                $excel->getDefaultStyle()->getFont()->setName('Calibri')->setSize(12);
                $excel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

                $active_sheet->getColumnDimension('A')->setWidth(15);
                $active_sheet->getColumnDimension('B')->setWidth(15);
                $active_sheet->getColumnDimension('C')->setWidth(15);
                $active_sheet->getColumnDimension('D')->setWidth(45);
                $active_sheet->getColumnDimension('E')->setWidth(20);
                $active_sheet->getColumnDimension('F')->setWidth(20);
                $active_sheet->getColumnDimension('G')->setWidth(10);
                $active_sheet->getColumnDimension('H')->setWidth(10);
                $active_sheet->getColumnDimension('I')->setWidth(30);
                $active_sheet->getColumnDimension('J')->setWidth(10);
                $active_sheet->getColumnDimension('K')->setWidth(10);
                $active_sheet->getColumnDimension('L')->setWidth(10);
                $active_sheet->getColumnDimension('M')->setWidth(10);
                $active_sheet->getColumnDimension('N')->setWidth(15);

                $active_sheet->setCellValue('A1', 'Дата');
                $active_sheet->setCellValue('B1', 'Договор');
                $active_sheet->setCellValue('C1', 'Заявка');
                $active_sheet->setCellValue('D1', 'ФИО');
                $active_sheet->setCellValue('E1', 'Телефон');
                $active_sheet->setCellValue('F1', 'Почта');
                $active_sheet->setCellValue('G1', 'Сумма');
                $active_sheet->setCellValue('H1', 'ПК/НК');
                $active_sheet->setCellValue('I1', 'Менеджер');
                $active_sheet->setCellValue('J1', 'Статус');
                $active_sheet->setCellValue('K1', 'Дата возврата');
                $active_sheet->setCellValue('L1', 'Дней просрочки');
                $active_sheet->setCellValue('M1', 'ПДН');
                $active_sheet->setCellValue('N1', 'Дней займа');
                $active_sheet->setCellValue('O1', 'UTM-источник');

                $i = 2;
                foreach ($contracts as $contract) {
                    if ($contract->client_status == 'pk')
                        $client_status = 'ПК';
                    elseif ($contract->client_status == 'nk')
                        $client_status = 'НК';
                    elseif ($contract->client_status == 'crm')
                        $client_status = 'ПК CRM';
                    elseif ($contract->client_status == 'rep')
                        $client_status = 'Повтор';
                    else
                        $client_status = '';

                    if (!empty($contract->collection_status)) {
                        if (empty($contract->sold))
                            $status = 'МКК ';
                        else
                            $status = 'ЮК ';
                        $status .= $collection_statuses[$contract->collection_status];
                    } else {
                        $status = $statuses[$contract->status];
                    }

                    $active_sheet->setCellValue('A' . $i, date('d.m.Y', strtotime($contract->date)));
                    $active_sheet->setCellValue('B' . $i, $contract->number);
                    $active_sheet->setCellValue('C' . $i, $contract->order_id);
                    $active_sheet->setCellValue('D' . $i, $contract->lastname . ' ' . $contract->firstname . ' ' . $contract->patronymic . ' ' . $contract->birth);
                    $active_sheet->setCellValue('E' . $i, $contract->phone_mobile);
                    $active_sheet->setCellValue('F' . $i, $contract->email);
                    $active_sheet->setCellValue('G' . $i, $contract->amount * 1);
                    $active_sheet->setCellValue('H' . $i, $client_status);
                    $active_sheet->setCellValue('I' . $i, $managers[$contract->manager_id]->name);
                    $active_sheet->setCellValue('J' . $i, $status);
                    $active_sheet->setCellValue('K' . $i, date('d.m.Y', strtotime($contract->return_date)));
                    $active_sheet->setCellValue('L' . $i, $contract->expired_days);
                    $active_sheet->setCellValue('M' . $i, $contract->pdn);
                    $active_sheet->setCellValue('N' . $i, $contract->period);
                    $active_sheet->setCellValue('O' . $i, (isset($contract->utm_source) ? $contract->utm_source : 'organic'));

                    $i++;
                }

                $objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');

                $objWriter->save($this->config->root_dir . $filename);

                header('Location:' . $this->config->root_url . '/' . $filename);
                exit;
            }

            $this->design->assign('contracts', $contracts);
        }

        return $this->design->fetch('statistics/contracts.tpl');
    }

    private function action_payments()
    {
        if ($operation_id = $this->request->get('operation_id', 'integer')) {
            if ($operation = $this->operations->get_operation($operation_id)) {
                $operation->contract = $this->contracts->get_contract($operation->contract_id);
                $operation->transaction = $this->transactions->get_transaction($operation->transaction_id);
                if ($operation->transaction->insurance_id)
                    $operation->transaction->insurance = $this->insurances->get_insurance($operation->transaction->insurance_id);

                /*
                if ($operation->type == 'REJECT_REASON')
                {
                    $result = $this->soap1c->send_reject_reason($operation);
                    if (!((isset($result->return) && $result->return == 'OK') || $result == 'OK'))
                    {
                        $order = $this->orders->get_order($operation->order_id);
                        $this->soap1c->send_order($order);
                        $result = $this->soap1c->send_reject_reason($operation);
                    }
                }
                else
                {
                    $result = $this->soap1c->send_payments(array($operation));
                }
                */
                if ((isset($result->return) && $result->return == 'OK') || $result == 'OK') {
                    $this->operations->update_operation($operation->id, array(
                        'sent_date' => date('Y-m-d H:i:s'),
                        'sent_status' => 2
                    ));
                    $this->json_output(array('success' => 'Операция отправлена'));
                } else {
                    $this->json_output(array('error' => 'Ошибка при отправке'));
                }

            } else {
                $this->json_output(array('error' => 'Операция не найдена'));
            }
        } elseif ($daterange = $this->request->get('daterange')) {
            $search_filter = '';

            list($from, $to) = explode('-', $daterange);

            $date_from = date('Y-m-d', strtotime($from));
            $date_to = date('Y-m-d', strtotime($to));

            $this->design->assign('date_from', $date_from);
            $this->design->assign('date_to', $date_to);
            $this->design->assign('from', $from);
            $this->design->assign('to', $to);

            if ($search = $this->request->get('search')) {
                if (!empty($search['created']))
                    $search_filter .= $this->db->placehold(' AND DATE(t.created) = ?', date('Y-m-d', strtotime($search['created'])));
                if (!empty($search['number']))
                    $search_filter .= $this->db->placehold(' AND c.number LIKE "%' . $this->db->escape($search['number']) . '%"');
                if (!empty($search['fio']))
                    $search_filter .= $this->db->placehold(' AND (u.lastname LIKE "%' . $this->db->escape($search['fio']) . '%" OR u.firstname LIKE "%' . $this->db->escape($search['fio']) . '%" OR u.patronymic LIKE "%' . $this->db->escape($search['fio']) . '%")');
                if (!empty($search['amount']))
                    $search_filter .= $this->db->placehold(' AND t.amount = ?', $search['amount'] * 100);
                if (!empty($search['card']))
                    $search_filter .= $this->db->placehold(' AND t.callback_response LIKE "%' . $this->db->escape($search['card']) . '%"');
                if (!empty($search['register_id']))
                    $search_filter .= $this->db->placehold(' AND t.register_id LIKE "%' . $this->db->escape($search['register_id']) . '%"');
                if (!empty($search['operation']))
                    $search_filter .= $this->db->placehold(' AND t.operation LIKE "%' . $this->db->escape($search['operation']) . '%"');
                if (!empty($search['description']))
                    $search_filter .= $this->db->placehold(' AND t.description LIKE "%' . $this->db->escape($search['description']) . '%"');

            }

            $query = $this->db->placehold("
                SELECT
                    `o`.id,
                    `o`.user_id,
                    `o`.contract_id,
                    `o`.order_id,
                    `o`.transaction_id,
                    `o`.type,
                    `o`.amount,
                    `t`.created,
                    `o`.sent_date,
                    `c`.number AS contract_number,
                    `c`.return_date,
                    `u`.lastname,
                    `u`.firstname,
                    `u`.patronymic,
                    `u`.birth,
                    `t`.register_id,
                    `t`.operation,
                    `t`.prolongation,
                    `t`.insurance_id,
                    `t`.description,
                    `t`.callback_response,
                    `i`.number AS insurance_number,
                    `i`.amount AS insurance_amount,
                    `t`.sector,
                    `o`.type_payment,
                    `or`.card_id as card,
                    `t`.id as transaction_id
                FROM __operations        AS `o`
                LEFT JOIN __contracts    AS `c` ON `c`.id = `o`.contract_id
                LEFT JOIN __users        AS `u` ON `u`.id = `o`.user_id
                LEFT JOIN __transactions AS `t` ON `t`.id = `o`.transaction_id
                LEFT JOIN __insurances   AS `i` ON `i`.id = `t`.insurance_id
                LEFT JOIN __orders    AS `or` ON `or`.id = `o`.order_id
                WHERE 1 
                $search_filter
                AND DATE(`t`.created) >= ?
                AND DATE(`t`.created) <= ?
                ORDER BY `t`.created
            ", $date_from, $date_to);
            $this->db->query($query);

            $operations = array();
            $INSURANCE_BC = 0;
            foreach ($this->db->results() as $op) {

                if(is_null($op->callback_response))
                    continue;
                
                if (!in_array($op->type, ['BUD_V_KURSE', 'SMS', 'INSURANCE'])) {
                    if (!empty($op->callback_response)) {
                        $callback = new SimpleXMLElement($op->callback_response);
                        if($callback->order_state != 'COMPLETED')
                            continue;
                    }
                }
                if($op->type == 'PAY'){
                    $op->amount += $INSURANCE_BC;
                }

                if($op->type == 'INSURANCE_BC'){
                    $INSURANCE_BC = $op->amount;
                    continue;
                }
                else{
                    $INSURANCE_BC = 0;
                }

                $operations[$op->id] = $op;

                $card = $this->cards->get_card($operations[$op->id]->card);
                $operations[$op->id]->pan = $card->pan;
                if(is_null($card->pan))
                    $operations[$op->id]->pan = $card->pan;
                elseif ($callback->pan)
                    $operations[$op->id]->pan = $callback->pan;
                else
                    $operations[$op->id]->pan = $callback->pan2;

                $transaction = $this->transactions->get_transaction($operations[$op->id]->transaction_id);
                $user = $this->users->get_user($transaction->user_id);
                if (!$operations[$op->id]->lastname){
                    $operations[$op->id]->lastname = $user->lastname;
                }
                if (!$operations[$op->id]->lastname){
                    $operations[$op->id]->lastname = $user->lastname;
                }
                if (!$operations[$op->id]->firstname){
                    $operations[$op->id]->firstname = $user->firstname;
                }
                if (!$operations[$op->id]->patronymic){
                    $operations[$op->id]->patronymic = $user->patronymic;
                }
                if (!$operations[$op->id]->birth){
                    $operations[$op->id]->birth = $user->birth;
                }
            }


            $statuses = $this->contracts->get_statuses();
            $this->design->assign('statuses', $statuses);

            $collection_statuses = $this->contracts->get_collection_statuses();
            $this->design->assign('collection_statuses', $collection_statuses);


            if ($this->request->get('download') == 'excel') {
                $managers = array();
                foreach ($this->managers->get_managers() as $m)
                    $managers[$m->id] = $m;

                $filename = 'files/reports/Отчет по оплатам.xls';
                require $this->config->root_dir . 'PHPExcel/Classes/PHPExcel.php';

                $excel = new PHPExcel();

                $excel->setActiveSheetIndex(0);
                $active_sheet = $excel->getActiveSheet();

                $active_sheet->setTitle("Выдачи " . $from . "-" . $to);

                $excel->getDefaultStyle()->getFont()->setName('Calibri')->setSize(12);
                $excel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

                $active_sheet->getColumnDimension('A')->setWidth(15);
                $active_sheet->getColumnDimension('B')->setWidth(15);
                $active_sheet->getColumnDimension('C')->setWidth(45);
                $active_sheet->getColumnDimension('D')->setWidth(20);
                $active_sheet->getColumnDimension('E')->setWidth(20);
                $active_sheet->getColumnDimension('F')->setWidth(10);
                $active_sheet->getColumnDimension('G')->setWidth(10);
                $active_sheet->getColumnDimension('H')->setWidth(30);
                $active_sheet->getColumnDimension('I')->setWidth(10);
                $active_sheet->getColumnDimension('J')->setWidth(15);

                $active_sheet->setCellValue('A1', 'Дата');
                $active_sheet->setCellValue('B1', 'Договор');
                $active_sheet->setCellValue('C1', 'ФИО');
                $active_sheet->setCellValue('D1', 'Сумма');
                $active_sheet->setCellValue('E1', 'Карта');
                $active_sheet->setCellValue('F1', 'Описание');
                $active_sheet->setCellValue('G1', 'B2P OrderID');
                $active_sheet->setCellValue('H1', 'B2P OperationID');
                $active_sheet->setCellValue('I1', 'Страховка');
                $active_sheet->setCellValue('J1', 'Дата возврата');

                $i = 2;
                foreach ($operations as $contract) {

                    $active_sheet->setCellValue('A' . $i, date('d.m.Y', strtotime($contract->created)));
                    $active_sheet->setCellValue('B' . $i, $contract->contract_number . ' ' . ($contract->sector == '7036' ? 'ЮК' : 'МКК'));
                    $active_sheet->setCellValue('C' . $i, $contract->lastname . ' ' . $contract->firstname . ' ' . $contract->patronymic . ' ' . $contract->birth);
                    $active_sheet->setCellValue('D' . $i, $contract->amount);
                    $active_sheet->setCellValue('E' . $i, $contract->pan);
                    $active_sheet->setCellValue('F' . $i, $contract->description . ' ' . ($contract->prolongation ? '(пролонгация)' : ''));
                    $active_sheet->setCellValue('G' . $i, $contract->register_id);
                    $active_sheet->setCellValue('H' . $i, $contract->operation);//--
                    $active_sheet->setCellValue('I' . $i, $contract->insurance_number . ' ' . ($contract->insurance_amount ? $contract->insurance_amount . ' руб' : ''));
                    $active_sheet->setCellValue('J' . $i, $contract->return_date);

                    $i++;
                }

                $objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');

                $objWriter->save($this->config->root_dir . $filename);

                header('Location:' . $this->config->root_url . '/' . $filename);
                exit;
            }

            $this->design->assign('operations', $operations);
        }

        return $this->design->fetch('statistics/payments.tpl');
    }

    private function action_eventlogs()
    {
        if ($daterange = $this->request->get('daterange')) {
            list($from, $to) = explode('-', $daterange);

            $date_from = date('Y-m-d', strtotime($from));
            $date_to = date('Y-m-d', strtotime($to));

            $this->design->assign('date_from', $date_from);
            $this->design->assign('date_to', $date_to);
            $this->design->assign('from', $from);
            $this->design->assign('to', $to);


            $query_manager_id = '';
            if ($filter_manager_id = $this->request->get('manager_id')) {
                if ($filter_manager_id != 'all')
                    $query_manager_id = $this->db->placehold("AND o.manager_id = ?", (int)$filter_manager_id);

                $this->design->assign('filter_manager_id', $filter_manager_id);
            }

            $query = $this->db->placehold("
                SELECT
                    o.id AS order_id,
                    o.date,
                    o.reason_id,
                    o.reject_reason,
                    o.user_id,
                    o.manager_id,
                    o.status,
                    u.lastname,
                    u.firstname,
                    u.patronymic
                FROM __orders AS o
                LEFT JOIN __users AS u
                ON u.id = o.user_id
                WHERE o.manager_id IS NOT NULL
                AND DATE(o.date) >= ?
                AND DATE(o.date) <= ?
                $query_manager_id
            ", $date_from, $date_to);
            $this->db->query($query);

            $orders = array();
            foreach ($this->db->results() as $o)
                $orders[$o->order_id] = $o;

            if (!empty($orders)) {
                foreach ($orders as $o) {
                    $o->eventlogs = $this->eventlogs->get_logs(array('order_id' => $o->order_id));
                }
            }

            $events = $this->eventlogs->get_events();
            $this->design->assign('events', $events);

            $reasons = $this->reasons->get_reasons();
            $this->design->assign('reasons', $reasons);


            if ($this->request->get('download') == 'excel') {
                $managers = array();
                foreach ($this->managers->get_managers() as $m)
                    $managers[$m->id] = $m;

                $order_statuses = $this->orders->get_statuses();

                $filename = 'files/reports/Логи событий.xls';
                require $this->config->root_dir . 'PHPExcel/Classes/PHPExcel.php';

                $excel = new PHPExcel();

                $excel->setActiveSheetIndex(0);
                $active_sheet = $excel->getActiveSheet();

                $active_sheet->setTitle("Логи " . $from . "-" . $to);

                $excel->getDefaultStyle()->getFont()->setName('Calibri')->setSize(12);
                $excel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

                $active_sheet->getColumnDimension('A')->setWidth(6);
                $active_sheet->getColumnDimension('B')->setWidth(30);
                $active_sheet->getColumnDimension('C')->setWidth(10);
                $active_sheet->getColumnDimension('D')->setWidth(10);
                $active_sheet->getColumnDimension('E')->setWidth(30);
                $active_sheet->getColumnDimension('F')->setWidth(30);

                $active_sheet->setCellValue('A1', '#');
                $active_sheet->setCellValue('B1', 'Заявка');
                $active_sheet->mergeCells('C1:F1');
                $active_sheet->setCellValue('C1', 'События');

                $style_bold = array(
                    'font' => array(
                        'name' => 'Calibri',
                        'size' => 13,
                        'bold' => true
                    ),
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                        'wrap' => true,
                    )
                );
                $active_sheet->getStyle('A1:C1')->applyFromArray($style_bold);

                $i = 2;
                $rc = 1;
                foreach ($orders as $order) {
                    $start_i = $i;

                    $a_indexes = 'A' . $i . ':A' . ($i + count($order->eventlogs) - 1);
                    if (count($order->eventlogs) > 2)
                        $active_sheet->mergeCells($a_indexes);
                    $active_sheet->getStyle($a_indexes)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $active_sheet->getStyle($a_indexes)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
                    $active_sheet->setCellValue('A' . $i, $rc);


                    $active_sheet->setCellValue('B' . $i, $order->order_id);
                    $active_sheet->setCellValue('B' . ($i + 1), 'Статус: ' . $order_statuses[$order->status]);
                    $active_sheet->setCellValue('B' . ($i + 2), 'Менеджер: ' . $managers[$order->manager_id]->name);

                    foreach ($order->eventlogs as $ev) {
                        $active_sheet->setCellValue('C' . $i, date('d.m.Y', strtotime($ev->created)));
                        $active_sheet->setCellValue('D' . $i, date('H:i:s', strtotime($ev->created)));
                        $active_sheet->setCellValue('E' . $i, $events[$ev->event_id]);
                        $active_sheet->setCellValue('F' . $i, $managers[$ev->manager_id]->name);

                        $i++;
                    }

                    $rc++;

                    $active_sheet->getStyle('A' . $start_i . ':F' . ($i - 1))->applyFromArray(
                        array(
                            'borders' => array(
                                'allborders' => array(
                                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                                    'color' => array('rgb' => '666666')
                                )
                            )
                        )
                    );
                    $active_sheet->getStyle('A' . $start_i . ':F' . ($i - 1))->applyFromArray(
                        array(
                            'borders' => array(
                                'top' => array(
                                    'style' => PHPExcel_Style_Border::BORDER_DOUBLE,
                                    'color' => array('rgb' => '222222')
                                ),
                                'bottom' => array(
                                    'style' => PHPExcel_Style_Border::BORDER_DOUBLE,
                                    'color' => array('rgb' => '222222')
                                ),
                                'left' => array(
                                    'style' => PHPExcel_Style_Border::BORDER_DOUBLE,
                                    'color' => array('rgb' => '222222')
                                ),
                                'right' => array(
                                    'style' => PHPExcel_Style_Border::BORDER_DOUBLE,
                                    'color' => array('rgb' => '222222')
                                )
                            )
                        )
                    );
                }

                $objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');

                $objWriter->save($this->config->root_dir . $filename);

                header('Location:' . $this->config->root_url . '/' . $filename);
                exit;
            }


            $this->design->assign('orders', $orders);
        }

        return $this->design->fetch('statistics/eventlogs.tpl');
    }

    private function action_penalties()
    {
        if ($daterange = $this->request->get('daterange')) {
            list($from, $to) = explode('-', $daterange);

            $date_from = date('Y-m-d', strtotime($from));
            $date_to = date('Y-m-d', strtotime($to));

            $this->design->assign('date_from', $date_from);
            $this->design->assign('date_to', $date_to);
            $this->design->assign('from', $from);
            $this->design->assign('to', $to);


            $filter = array();
            $filter['date_from'] = $date_from;
            $filter['date_to'] = $date_to;
            $filter['status'] = 4;

            if ($this->manager->role == 'user') {
                $filter['manager_id'] = $this->manager->id;
            } elseif ($filter_manager_id = $this->request->get('manager_id')) {
                if ($filter_manager_id != 'all')
                    $filter['manager_id'] = $filter_manager_id;

                $this->design->assign('filter_manager_id', $filter_manager_id);
            }

            $orders = array();
            if ($penalties = $this->penalties->get_penalties($filter)) {
                $order_ids = array();
                foreach ($penalties as $penalty)
                    $order_ids[] = $penalty->order_id;

                foreach ($this->orders->get_orders(array('id' => $order_ids)) as $order) {
                    $order->penalties = array();
                    $orders[$order->order_id] = $order;
                }

                foreach ($penalties as $penalty) {
                    if (isset($orders[$penalty->order_id]))
                        $orders[$penalty->order_id]->penalties[] = $penalty;
                }

                $total_summ = 0;
                $total_count = 0;
                foreach ($orders as $order) {
                    $total_count++;
                    $order->penalty_summ = 0;
                    foreach ($order->penalties as $p) {
                        if ($order->penalty_summ < $p->cost)
                            $order->penalty_summ = $p->cost;
                    }
                    $order->penalty_summ = min($order->penalty_summ, 500);
                    $total_summ += $order->penalty_summ;
                }

                $this->design->assign('total_summ', $total_summ);
                $this->design->assign('total_count', $total_count);
            }

            $this->design->assign('orders', $orders);

            $penalty_types = array();
            foreach ($this->penalties->get_types() as $t)
                $penalty_types[$t->id] = $t;
            $this->design->assign('penalty_types', $penalty_types);

            $penalty_statuses = $this->penalties->get_statuses();
            $this->design->assign('penalty_statuses', $penalty_statuses);

        }

        return $this->design->fetch('statistics/penalties.tpl');
    }

    private function action_dailyreports()
    {
        if ($daterange = $this->request->get('daterange')) {
            list($from, $to) = explode('-', $daterange);

            $date_from = date('Y-m-d', strtotime($from));
            $date_to = date('Y-m-d', strtotime($to));

            $this->design->assign('date_from', $date_from);
            $this->design->assign('date_to', $date_to);
            $this->design->assign('from', $from);
            $this->design->assign('to', $to);


            $filter = array();
            $filter['date_from'] = $date_from;
            $filter['date_to'] = $date_to;

            if ($this->manager->role == 'user') {
                $filter['manager_id'] = $this->manager->id;
            } elseif ($filter_manager_id = $this->request->get('manager_id')) {
                if ($filter_manager_id != 'all')
                    $filter['manager_id'] = $filter_manager_id;

                $this->design->assign('filter_manager_id', $filter_manager_id);
            }

            $final_array = [];

            //Выдано новых + сумма, Выдано повторно + сумма

            $filter['inssuance_date_from'] = $date_from;
            $filter['inssuance_date_to'] = $date_to;

            $inssuance_contracts = $this->contracts->get_contracts_orders($filter);

            $new_rep_orders = [];

            foreach ($inssuance_contracts as $contract) {
                $date = date('Y-m-d', strtotime($contract->inssuance_date));

                if (array_key_exists($date, $new_rep_orders) == false) {
                    $new_rep_orders[$date] = [
                        'count_new_orders' => 0,
                        'sum_new_orders' => 0,
                        'count_repeat_orders' => 0,
                        'sum_repeat_orders' => 0
                    ];
                }

                // $sum_insurance_obj = OperationsORM::where('order_id', $contract->id)->where('type', 'INSURANCE')->first();
                // if($sum_insurance_obj)
                //     $sum_insurance = $sum_insurance_obj['amount'];
                // else
                //     $sum_insurance = 0;

                $operations = $this->operations->get_operations(['type' => 'P2P', 'order_id' => $contract->id]);
                if ($contract->client_status == 'nk' || $contract->client_status == 'rep') {
                    $new_rep_orders[$date]['count_new_orders'] += 1;
                    $new_rep_orders[$date]['sum_new_orders'] += $operations[0]->amount;
                }
                if ($contract->client_status == 'pk' || $contract->client_status == 'crm') {
                    $new_rep_orders[$date]['count_repeat_orders'] += 1;
                    $new_rep_orders[$date]['sum_repeat_orders'] += $operations[0]->amount;
                }
            }

            foreach ($new_rep_orders as $date => $order) {
                $final_array[$date]['count_new_orders'] = $order['count_new_orders'];
                $final_array[$date]['sum_new_orders'] = $order['sum_new_orders'];
                $final_array[$date]['count_repeat_orders'] = $order['count_repeat_orders'];
                $final_array[$date]['sum_repeat_orders'] = $order['sum_repeat_orders'];
            }

            //Погашено
            $filter_closed_contracts['close_date_from'] = $date_from;
            $filter_closed_contracts['close_date_to'] = $date_to;
            $count_closed_contracts = [];

            $contracts = $this->contracts->get_contracts($filter_closed_contracts);

            foreach ($contracts as $contract) {
                $date = date('Y-m-d', strtotime($contract->close_date));

                if (array_key_exists($date, $count_closed_contracts) == false) {
                    $count_closed_contracts[$date] = ['count_closed_contracts' => 0];
                }
                $count_closed_contracts[$date]['count_closed_contracts'] += 1;
            }

            foreach ($count_closed_contracts as $date => $contract) {
                $final_array[$date]['count_closed_contracts'] = $contract['count_closed_contracts'];
            }

            $operations = $this->operations->get_operations_transactions($filter);
            $operations_by_date = [];

            foreach ($operations as $operation) {
                $date = date('Y-m-d', strtotime($operation->created));

                if (array_key_exists($date, $operations_by_date) == false) { 
                    $operations_by_date[$date]['count_prolongations'] = 0;
                    $operations_by_date[$date]['loan_body_summ'] = 0;
                    $operations_by_date[$date]['loan_charges_summ'] = 0;
                    $operations_by_date[$date]['count_insurance'] = 0;
                    $operations_by_date[$date]['count_insurance_old'] = 0;
                    $operations_by_date[$date]['count_insurance_new'] = 0;
                    $operations_by_date[$date]['sum_insurance'] = 0;
                    $operations_by_date[$date]['sum_insurance_old'] = 0;
                    $operations_by_date[$date]['sum_insurance_new'] = 0;
                    $operations_by_date[$date]['count_insurance_prolongation'] = 0;
                    $operations_by_date[$date]['sum_insurance_prolongation'] = 0;
                    $operations_by_date[$date]['count_sms_services'] = 0;
                    $operations_by_date[$date]['count_sms_services_old'] = 0;
                    $operations_by_date[$date]['count_sms_services_new'] = 0;
                    $operations_by_date[$date]['sum_sms_services'] = 0;
                    $operations_by_date[$date]['sum_sms_services_old'] = 0;
                    $operations_by_date[$date]['sum_sms_services_new'] = 0;
                    $operations_by_date[$date]['count_reject_reason'] = 0;
                    $operations_by_date[$date]['count_reject_reason_old'] = 0;
                    $operations_by_date[$date]['count_reject_reason_new'] = 0;
                    $operations_by_date[$date]['sum_reject_reason'] = 0;
                    $operations_by_date[$date]['sum_reject_reason_old'] = 0;
                    $operations_by_date[$date]['sum_reject_reason_new'] = 0;
                    $operations_by_date[$date]['count_return'] = 0;
                    $operations_by_date[$date]['sum_return'] = 0;
                    $operations_by_date[$date]['sum_cor_percents'] = 0;
                    $operations_by_date[$date]['sum_cor_body'] = 0;
                    $operations_by_date[$date]['count_cor_prolongations'] = 0;
                    $operations_by_date[$date]['count_cor_closed'] = 0;
                    $operations_by_date[$date]['count_partial_release'] = 0;
                }

                if ($operation->prolongation == 1 && $operation->type == 'PAY') {
                    $operations_by_date[$date]['count_prolongations'] += 1;

                    if ($operation->type_payment == 1) {
                        $operations_by_date[$date]['count_cor_prolongations'] += 1;
                    }
                }
                if ($operation->contract_id && $operation->type == 'PAY') {
                    $operations_by_date[$date]['loan_body_summ'] += $operation->loan_body_summ;

                    $charges_sum = $operation->loan_percents_summ + $operation->loan_charge_summ + $operation->loan_peni_summ;
                    $operations_by_date[$date]['loan_charges_summ'] += $charges_sum;

                    if ($operation->type_payment == 1) {
                        $operations_by_date[$date]['sum_cor_percents'] += $operation->loan_percents_summ;
                        $operations_by_date[$date]['sum_cor_body'] += $operation->loan_body_summ;
                    }

                    if ($operation->op_loan_percents_summ == 0 && $operation->op_loan_body_summ == 0 && $operation->type_payment == 1) {
                        $operations_by_date[$date]['count_cor_closed'] += 1;
                    }

                    if ($operation->prolongation == 0 && $operation->contract_is_closed == 0) {
                        $operations_by_date[$date]['count_partial_release']++;
                    }
                }

                $order = $this->orders->get_order($operation->order_id);
                
                if ($operation->type == 'INSURANCE') {
                    $operations_by_date[$date]['count_insurance'] += 1;
                    $operations_by_date[$date]['sum_insurance'] += $operation->amount;
                    if(in_array($order->client_status,array('nk', 'rep'))){
                        $operations_by_date[$date]['count_insurance_new'] += 1;
                        $operations_by_date[$date]['sum_insurance_new'] += $operation->amount;
                    }
                    else{
                        $operations_by_date[$date]['count_insurance_old'] += 1;
                        $operations_by_date[$date]['sum_insurance_old'] += $operation->amount;
                    }

                    if ($operation->prolongation == 1) {
                        $operations_by_date[$date]['count_insurance_prolongation'] += 1;
                        $operations_by_date[$date]['sum_insurance_prolongation'] += $operation->amount;
                    }
                }

                if ($operation->type == 'BUD_V_KURSE') {
                    $operations_by_date[$date]['count_sms_services'] += 1;
                    $operations_by_date[$date]['sum_sms_services'] += $operation->amount;
                    if(in_array($order->client_status,array('nk', 'rep'))){
                        $operations_by_date[$date]['count_sms_services_new'] += 1;
                        $operations_by_date[$date]['sum_sms_services_new'] += $operation->amount;
                    }
                    else{
                        $operations_by_date[$date]['count_sms_services_old'] += 1;
                        $operations_by_date[$date]['sum_sms_services_old'] += $operation->amount;
                    }
                }
                if ($operation->type == 'REJECT_REASON') {
                    $operations_by_date[$date]['count_reject_reason'] += 1;
                    $operations_by_date[$date]['sum_reject_reason'] += $operation->amount;
                    if(in_array($order->client_status,array('nk', 'rep'))){
                        $operations_by_date[$date]['count_reject_reason_new'] += 1;
                        $operations_by_date[$date]['sum_reject_reason_new'] += $operation->amount;
                    }
                    else{
                        $operations_by_date[$date]['count_reject_reason_old'] += 1;
                        $operations_by_date[$date]['sum_reject_reason_old'] += $operation->amount;
                    }
                }
                if (strrpos($operation->type, 'RETURN') !== false) {
                    $operations_by_date[$date]['count_return'] += 1;
                    $operations_by_date[$date]['sum_return'] += $operation->amount;
                }
            }

            foreach ($operations_by_date as $date => $operation) {
                $final_array[$date]['count_prolongations'] = $operation['count_prolongations'];
                $final_array[$date]['loan_body_summ'] = $operation['loan_body_summ'];
                $final_array[$date]['loan_charges_summ'] = $operation['loan_charges_summ'];
                $final_array[$date]['count_insurance'] = $operation['count_insurance'];
                $final_array[$date]['count_insurance_old'] = $operation['count_insurance_old'];
                $final_array[$date]['count_insurance_new'] = $operation['count_insurance_new'];
                $final_array[$date]['sum_insurance'] = $operation['sum_insurance'];
                $final_array[$date]['sum_insurance_old'] = $operation['sum_insurance_old'];
                $final_array[$date]['sum_insurance_new'] = $operation['sum_insurance_new'];
                $final_array[$date]['count_insurance_prolongation'] = $operation['count_insurance_prolongation'];
                $final_array[$date]['sum_insurance_prolongation'] = $operation['sum_insurance_prolongation'];
                $final_array[$date]['count_sms_services'] = $operation['count_sms_services'];
                $final_array[$date]['count_sms_services_old'] = $operation['count_sms_services_old'];
                $final_array[$date]['count_sms_services_new'] = $operation['count_sms_services_new'];
                $final_array[$date]['sum_sms_services'] = $operation['sum_sms_services'];
                $final_array[$date]['sum_sms_services_old'] = $operation['sum_sms_services_old'];
                $final_array[$date]['sum_sms_services_new'] = $operation['sum_sms_services_new'];
                $final_array[$date]['count_reject_reason'] = $operation['count_reject_reason'];
                $final_array[$date]['count_reject_reason_old'] = $operation['count_reject_reason_old'];
                $final_array[$date]['count_reject_reason_new'] = $operation['count_reject_reason_new'];
                $final_array[$date]['sum_reject_reason'] = $operation['sum_reject_reason'];
                $final_array[$date]['sum_reject_reason_old'] = $operation['sum_reject_reason_old'];
                $final_array[$date]['sum_reject_reason_new'] = $operation['sum_reject_reason_new'];
                $final_array[$date]['count_return'] = $operation['count_return'];
                $final_array[$date]['sum_return'] = $operation['sum_return'];
                $final_array[$date]['sum_cor_percents'] = $operation['sum_cor_percents'];
                $final_array[$date]['sum_cor_body'] = $operation['sum_cor_body'];
                $final_array[$date]['count_cor_prolongations'] = $operation['count_cor_prolongations'];
                $final_array[$date]['count_cor_closed'] = $operation['count_cor_closed'];
                $final_array[$date]['count_partial_release'] = $operation['count_partial_release'];
            }

            $operations = $this->operations->get_operations_insurance($filter);
            $operations_insurance_inssuance = [];
            $operations_insurance_close = [];

            foreach ($operations as $operation) {
                $date = date('Y-m-d', strtotime($operation->created));

                if ($operation->close_date) {
                    $close_date = date('Y-m-d', strtotime($operation->close_date));

                    if ($date == $close_date && $operation->amount == 200 || $operation->amount == 400) {

                        if (array_key_exists($date, $operations_insurance_close) == false) {
                            $operations_insurance_close[$date] = [
                                'count_insurance_close' => 0,
                                'sum_insurance_close' => 0];
                        }

                        $operations_insurance_close[$date]['count_insurance_close'] += 1;
                        $operations_insurance_close[$date]['sum_insurance_close'] += $operation->amount;
                    }
                }
                if ($operation->inssuance_date) {

                    $inssuance_date = date('Y-m-d', strtotime($operation->inssuance_date));

                    if ($date == $inssuance_date && $operation->type == 'INSURANCE') {
                        if (array_key_exists($date, $operations_insurance_inssuance) == false) {
                            $operations_insurance_inssuance[$date] = [
                                'count_insurance_inssuance' => 0,
                                'sum_insurance_inssuance' => 0,
                            ];
                        }
                        $operations_insurance_inssuance[$date]['count_insurance_inssuance'] += 1;
                        $operations_insurance_inssuance[$date]['sum_insurance_inssuance'] += $operation->amount;
                    }
                }
            }

            foreach ($operations_insurance_close as $date => $operation) {
                $final_array[$date]['count_insurance_close'] = $operation['count_insurance_close'];
                $final_array[$date]['sum_insurance_close'] = $operation['sum_insurance_close'];
            }

            foreach ($operations_insurance_inssuance as $date => $operation) {
                $final_array[$date]['count_insurance_inssuance'] = $operation['count_insurance_inssuance'];
                $final_array[$date]['sum_insurance_inssuance'] = $operation['sum_insurance_inssuance'];
            }

            $transactions = $this->transactions->get_transactions_cards($filter);
            $card_binding = [];

            foreach ($transactions as $transaction) {
                $date = date('Y-m-d', strtotime($transaction->operation_date));

                if (array_key_exists($date, $card_binding) == false) {
                    $card_binding[$date] = ['count_card_binding' => 0, 'sum_card_binding' => 0];
                }

                // $card_binding[$date]['count_card_binding'] += 1;
                // $card_binding[$date]['sum_card_binding'] += ($transaction->amount / 100);
            }

            foreach ($card_binding as $date => $operation) {
                $final_array[$date]['count_card_binding'] = $operation['count_card_binding'];
                $final_array[$date]['sum_card_binding'] = $operation['sum_card_binding'];
            }

            foreach ($final_array as $array) {
                if (array_key_exists('Итого', $final_array) == false) {
                    $final_array['Итого']['count_new_orders'] = 0;
                    $final_array['Итого']['sum_new_orders'] = 0;
                    $final_array['Итого']['count_repeat_orders'] = 0;
                    $final_array['Итого']['sum_repeat_orders'] = 0;
                    $final_array['Итого']['count_closed_contracts'] = 0;
                    $final_array['Итого']['count_prolongations'] = 0;
                    $final_array['Итого']['loan_body_summ'] = 0;
                    $final_array['Итого']['loan_charges_summ'] = 0;
                    $final_array['Итого']['count_insurance'] = 0;
                    $final_array['Итого']['count_insurance_old'] = 0;
                    $final_array['Итого']['count_insurance_new'] = 0;
                    $final_array['Итого']['sum_insurance'] = 0;
                    $final_array['Итого']['sum_insurance_old'] = 0;
                    $final_array['Итого']['sum_insurance_new'] = 0;
                    $final_array['Итого']['count_insurance_prolongation'] = 0;
                    $final_array['Итого']['sum_insurance_prolongation'] = 0;
                    $final_array['Итого']['count_sms_services'] = 0;
                    $final_array['Итого']['count_sms_services_old'] = 0;
                    $final_array['Итого']['count_sms_services_new'] = 0;
                    $final_array['Итого']['sum_sms_services'] = 0;
                    $final_array['Итого']['sum_sms_services_old'] = 0;
                    $final_array['Итого']['sum_sms_services_new'] = 0;
                    $final_array['Итого']['count_reject_reason'] = 0;
                    $final_array['Итого']['count_reject_reason_old'] = 0;
                    $final_array['Итого']['count_reject_reason_new'] = 0;
                    $final_array['Итого']['sum_reject_reason'] = 0;
                    $final_array['Итого']['sum_reject_reason_old'] = 0;
                    $final_array['Итого']['sum_reject_reason_new'] = 0;
                    $final_array['Итого']['count_return'] = 0;
                    $final_array['Итого']['sum_return'] = 0;
                    $final_array['Итого']['count_insurance_close'] = 0;
                    $final_array['Итого']['sum_insurance_close'] = 0;
                    $final_array['Итого']['count_insurance_inssuance'] = 0;
                    $final_array['Итого']['sum_insurance_inssuance'] = 0;
                    $final_array['Итого']['count_card_binding'] = 0;
                    $final_array['Итого']['sum_card_binding'] = 0;
                    $final_array['Итого']['sum_cor_percents'] = 0;
                    $final_array['Итого']['sum_cor_body'] = 0;
                    $final_array['Итого']['count_cor_prolongations'] = 0;
                    $final_array['Итого']['count_cor_closed'] = 0;
                    $final_array['Итого']['count_partial_release'] = 0;
                }
                $final_array['Итого']['count_new_orders'] += ($array['count_new_orders']) ?: 0;
                $final_array['Итого']['sum_new_orders'] += ($array['sum_new_orders']) ?: 0;
                $final_array['Итого']['count_repeat_orders'] += ($array['count_repeat_orders']) ?: 0;
                $final_array['Итого']['sum_repeat_orders'] += ($array['sum_repeat_orders']) ?: 0;
                $final_array['Итого']['count_closed_contracts'] += ($array['count_closed_contracts']) ?: 0;
                $final_array['Итого']['count_prolongations'] += ($array['count_prolongations']) ?: 0;
                $final_array['Итого']['loan_body_summ'] += ($array['loan_body_summ']) ?: 0;
                $final_array['Итого']['loan_charges_summ'] += ($array['loan_charges_summ']) ?: 0;
                $final_array['Итого']['count_insurance'] += ($array['count_insurance']) ?: 0;
                $final_array['Итого']['count_insurance_old'] += ($array['count_insurance_old']) ?: 0;
                $final_array['Итого']['count_insurance_new'] += ($array['count_insurance_new']) ?: 0;
                $final_array['Итого']['sum_insurance'] += ($array['sum_insurance']) ?: 0;
                $final_array['Итого']['sum_insurance_old'] += ($array['sum_insurance_old']) ?: 0;
                $final_array['Итого']['sum_insurance_new'] += ($array['sum_insurance_new']) ?: 0;
                $final_array['Итого']['count_insurance_prolongation'] += ($array['count_insurance_prolongation']) ?: 0;
                $final_array['Итого']['sum_insurance_prolongation'] += ($array['sum_insurance_prolongation']) ?: 0;
                $final_array['Итого']['count_sms_services'] += ($array['count_sms_services']) ?: 0;
                $final_array['Итого']['count_sms_services_old'] += ($array['count_sms_services_old']) ?: 0;
                $final_array['Итого']['count_sms_services_new'] += ($array['count_sms_services_new']) ?: 0;
                $final_array['Итого']['sum_sms_services'] += ($array['sum_sms_services']) ?: 0;
                $final_array['Итого']['sum_sms_services_old'] += ($array['sum_sms_services_old']) ?: 0;
                $final_array['Итого']['sum_sms_services_new'] += ($array['sum_sms_services_new']) ?: 0;
                $final_array['Итого']['count_reject_reason'] += ($array['count_reject_reason']) ?: 0;
                $final_array['Итого']['count_reject_reason_old'] += ($array['count_reject_reason_old']) ?: 0;
                $final_array['Итого']['count_reject_reason_new'] += ($array['count_reject_reason_new']) ?: 0;
                $final_array['Итого']['sum_reject_reason'] += ($array['sum_reject_reason']) ?: 0;
                $final_array['Итого']['sum_reject_reason_old'] += ($array['sum_reject_reason_old']) ?: 0;
                $final_array['Итого']['sum_reject_reason_new'] += ($array['sum_reject_reason_new']) ?: 0;
                $final_array['Итого']['count_return'] += ($array['count_return']) ?: 0;
                $final_array['Итого']['sum_return'] += ($array['sum_return']) ?: 0;
                $final_array['Итого']['count_insurance_close'] += ($array['count_insurance_close']) ?: 0;
                $final_array['Итого']['sum_insurance_close'] += ($array['sum_insurance_close']) ?: 0;
                $final_array['Итого']['count_insurance_inssuance'] += ($array['count_insurance_inssuance']) ?: 0;
                $final_array['Итого']['sum_insurance_inssuance'] += ($array['sum_insurance_inssuance']) ?: 0;
                $final_array['Итого']['count_card_binding'] += ($array['count_card_binding']) ?: 0;
                $final_array['Итого']['sum_card_binding'] += ($array['sum_card_binding']) ?: 0;
                $final_array['Итого']['sum_cor_percents'] += ($array['sum_cor_percents']) ?: 0;
                $final_array['Итого']['sum_cor_body'] += ($array['sum_cor_body']) ?: 0;
                $final_array['Итого']['count_cor_prolongations'] += ($array['count_cor_prolongations']) ?: 0;
                $final_array['Итого']['count_cor_closed'] += ($array['count_cor_closed']) ?: 0;
                $final_array['Итого']['count_partial_release'] += ($array['count_partial_release']) ?: 0;
            }

            if ($this->request->get('download') == 'excel') {

                $filename = 'files/reports/Отчет по дням.xls';
                require $this->config->root_dir . 'PHPExcel/Classes/PHPExcel.php';

                $excel = new PHPExcel();

                $excel->setActiveSheetIndex(0);
                $active_sheet = $excel->getActiveSheet();

                $active_sheet->setTitle($from . "-" . $to);

                $excel->getDefaultStyle()->getFont()->setName('Calibri')->setSize(12);
                $excel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

                $active_sheet->getColumnDimension('A')->setWidth(15);
                $active_sheet->getColumnDimension('B')->setWidth(15);
                $active_sheet->getColumnDimension('C')->setWidth(15);
                $active_sheet->getColumnDimension('D')->setWidth(15);
                $active_sheet->getColumnDimension('E')->setWidth(0);
                $active_sheet->getColumnDimension('F')->setWidth(15);
                $active_sheet->getColumnDimension('G')->setWidth(15);
                $active_sheet->getColumnDimension('H')->setWidth(15);
                $active_sheet->getColumnDimension('I')->setWidth(15);
                $active_sheet->getColumnDimension('J')->setWidth(15);
                $active_sheet->getColumnDimension('K')->setWidth(15);
                $active_sheet->getColumnDimension('L')->setWidth(15);
                $active_sheet->getColumnDimension('M')->setWidth(15);
                $active_sheet->getColumnDimension('N')->setWidth(15);
                $active_sheet->getColumnDimension('O')->setWidth(15);
                $active_sheet->getColumnDimension('P')->setWidth(15);
                $active_sheet->getColumnDimension('Q')->setWidth(15);
                $active_sheet->getColumnDimension('R')->setWidth(15);
                $active_sheet->getColumnDimension('S')->setWidth(15);
                $active_sheet->getColumnDimension('T')->setWidth(15);
                $active_sheet->getColumnDimension('U')->setWidth(15);

                $active_sheet->setCellValue('A1', 'Дата');
                $active_sheet->setCellValue('B1', 'Выдано новых/Сумма');
                $active_sheet->setCellValue('C1', 'Выдано повторных/Сумма');
                $active_sheet->setCellValue('D1', 'Погашено');
                $active_sheet->setCellValue('E1', 'Продлено');
                $active_sheet->setCellValue('F1', 'Получено ОД');
                $active_sheet->setCellValue('G1', 'Получено %%');
                $active_sheet->setCellValue('H1', 'Всего страховок/Сумма');
                $active_sheet->setCellValue('I1', 'Страховки при выдаче/Сумма');
                $active_sheet->setCellValue('J1', 'Страховки при продлении/Сумма');
                $active_sheet->setCellValue('K1', 'Страховки при закрытии/Сумма');
                $active_sheet->setCellValue('L1', '"СМС информирование"/Сумма');
                $active_sheet->setCellValue('M1', '"Узнай причину отказа"/Сумма');
                // $active_sheet->setCellValue('M1', '"Привязка карты"/Сумма');
                $active_sheet->setCellValue('N1', 'Итого доп продуктов/Сумма');
                $active_sheet->setCellValue('O1', 'Отменено доп продуктов/Сумма');
                $active_sheet->setCellValue('P1', 'Оплачено на р/сч ОД');
                $active_sheet->setCellValue('Q1', 'Оплачено на р/сч %%');
                $active_sheet->setCellValue('R1', 'Продления по р/сч');
                $active_sheet->setCellValue('S1', 'Погашения по р/сч');
                $active_sheet->setCellValue('T1', 'Частично погашено');

                $i = 2;
                foreach ($final_array as $date => $report) {
                    $count_add_services = $report['count_insurance'] + $report['count_sms_services'] + $report['count_reject_reason'];
                    // + $report['count_card_binding'];
                    $sum_add_services = $report['sum_insurance'] + $report['sum_sms_services'] + $report['sum_reject_reason'];
                    // + $report['sum_card_binding'];

                    $active_sheet->setCellValue('A' . $i, $date);
                    $active_sheet->setCellValue('B' . $i, $report['count_new_orders'] . 'шт /' . 
                        $report['sum_new_orders']. 'руб');
                    $active_sheet->setCellValue('C' . $i, $report['count_repeat_orders'] . 'шт /' . 
                        $report['sum_repeat_orders']  . 'руб');
                    $active_sheet->setCellValue('D' . $i, $report['count_closed_contracts']);
                    $active_sheet->setCellValue('E' . $i, $report['count_prolongations']);
                    $active_sheet->setCellValue('F' . $i, $report['loan_body_summ']);
                    $active_sheet->setCellValue('G' . $i, $report['loan_charges_summ']);
                    $active_sheet->setCellValue('H' . $i, $report['count_insurance'] . 'шт /' . $report['sum_insurance'] . 'руб');
                    $active_sheet->setCellValue('I' . $i, $report['count_insurance_inssuance'] . 'шт /' . $report['sum_insurance_inssuance'] . 'руб');
                    $active_sheet->setCellValue('J' . $i, $report['count_insurance_prolongation'] . 'шт /' . $report['sum_insurance_prolongation'] . 'руб');
                    $active_sheet->setCellValue('K' . $i, $report['count_insurance_close'] . 'шт /' . $report['sum_insurance_close'] . 'руб');
                    $active_sheet->setCellValue('L' . $i, $report['count_sms_services'] . 'шт /' . $report['sum_sms_services'] . 'руб');
                    $active_sheet->setCellValue('M' . $i, $report['count_reject_reason'] . 'шт /' . $report['sum_reject_reason'] . 'руб');
                    // $active_sheet->setCellValue('M' . $i, $report['count_card_binding'] . 'шт /' . $report['sum_card_binding'] . 'руб');
                    $active_sheet->setCellValue('N' . $i, $count_add_services . 'шт /' . $sum_add_services . 'руб');
                    $active_sheet->setCellValue('O' . $i, $report['count_return'] . 'шт /' . $report['sum_return'] . 'руб');
                    $active_sheet->setCellValue('P' . $i, $report['sum_cor_body'] . ' руб');
                    $active_sheet->setCellValue('Q' . $i, $report['sum_cor_percents'] . ' руб');
                    $active_sheet->setCellValue('R' . $i, $report['count_cor_prolongations'] . ' шт');
                    $active_sheet->setCellValue('S' . $i, $report['count_cor_closed'] . ' шт');
                    $active_sheet->setCellValue('T' . $i, $report['count_partial_release'] . ' шт');

                    $i++;
                }

                $objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');

                $objWriter->save($this->config->root_dir . $filename);

                header('Location:' . $this->config->root_url . '/' . $filename);
                exit;
            }

            ksort($final_array);

            $this->design->assign('final_array', $final_array);
        }

        return $this->design->fetch('statistics/dailyreports.tpl');
    }

    private function action_adservices()
    {
        if ($daterange = $this->request->get('daterange')) {
            list($from, $to) = explode('-', $daterange);

            $date_from = date('Y-m-d', strtotime($from));
            $date_to = date('Y-m-d', strtotime($to));

            $this->design->assign('date_from', $date_from);
            $this->design->assign('date_to', $date_to);
            $this->design->assign('from', $from);
            $this->design->assign('to', $to);


            $filter = array();
            $filter['date_from'] = $date_from;
            $filter['date_to'] = $date_to;

            // $ad_services = $this->operations->operations_contracts_insurance($filter);
            $ad_services = $this->operations->operations_contracts_insurance_reject($filter);

            foreach ($ad_services as $service) {
                $service->regAddr = AdressesORM::find($service->regaddress_id);
                $service->regAddr = $service->regAddr->adressfull;
            }

            $op_type = ['INSURANCE' => 'Страхование от НС', 'INSURANCE_BC' => 'Страхование от БК', 'BUD_V_KURSE' => 'Будь в курсе', 'REJECT_REASON' => 'Узнай причину отказа', 'INSURANCE_CLOSED' => 'Страхование БК'];
            $gender = ['male' => 'Мужской', 'female' => 'Женский'];

            $this->design->assign('ad_services', $ad_services);
            $this->design->assign('op_type', $op_type);
            $this->design->assign('gender', $gender);

            $card_binding = $this->transactions->get_transactions_cards_users($filter);

            $this->design->assign('card_binding', $card_binding);

            if ($this->request->get('download') == 'excel') {

                $filename = 'files/reports/Отчет по дополнительным услугам.xls';
                require $this->config->root_dir . 'PHPExcel/Classes/PHPExcel.php';

                $excel = new PHPExcel();

                $excel->setActiveSheetIndex(0);
                $active_sheet = $excel->getActiveSheet();

                $active_sheet->setTitle($from . "-" . $to);

                $excel->getDefaultStyle()->getFont()->setName('Calibri')->setSize(12);
                $excel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

                $active_sheet->getColumnDimension('A')->setWidth(15);
                $active_sheet->getColumnDimension('B')->setWidth(15);
                $active_sheet->getColumnDimension('C')->setWidth(15);
                $active_sheet->getColumnDimension('D')->setWidth(15);
                $active_sheet->getColumnDimension('E')->setWidth(15);
                $active_sheet->getColumnDimension('F')->setWidth(15);
                $active_sheet->getColumnDimension('G')->setWidth(15);
                $active_sheet->getColumnDimension('H')->setWidth(15);
                $active_sheet->getColumnDimension('I')->setWidth(15);
                $active_sheet->getColumnDimension('J')->setWidth(15);
                $active_sheet->getColumnDimension('K')->setWidth(15);
                $active_sheet->getColumnDimension('L')->setWidth(15);
                $active_sheet->getColumnDimension('M')->setWidth(15);
                $active_sheet->getColumnDimension('N')->setWidth(15);
                $active_sheet->getColumnDimension('O')->setWidth(15);

                $active_sheet->setCellValue('A1', 'Дата продажи');
                $active_sheet->setCellValue('B1', 'Договор займа');
                $active_sheet->setCellValue('C1', 'ID клиента');
                $active_sheet->setCellValue('D1', 'Номер полиса');
                $active_sheet->setCellValue('E1', 'Продукт');
                $active_sheet->setCellValue('F1', 'ID операции');
                $active_sheet->setCellValue('G1', 'УИД договора');
                $active_sheet->setCellValue('H1', 'ФИО, дата рождения');
                $active_sheet->setCellValue('I1', 'Номер телефона');
                $active_sheet->setCellValue('J1', 'Пол');
                $active_sheet->setCellValue('K1', 'Паспорт, серия номер');
                $active_sheet->setCellValue('L1', 'Адрес');
                $active_sheet->setCellValue('M1', 'Дата начала / завершения ответственности');
                $active_sheet->setCellValue('N1', 'Страховая сумма');
                $active_sheet->setCellValue('O1', 'Сумма оплаты/Страховая премия');

                $i = 2;
                foreach ($ad_services as $ad_service) {

                    $fio_birth = "$ad_service->lastname $ad_service->firstname $ad_service->patronymic $ad_service->birth";


                    $active_sheet->setCellValue('A' . $i, $ad_service->created);
                    $active_sheet->setCellValue('B' . $i, $ad_service->contract_id);
                    $active_sheet->setCellValue('C' . $i, $ad_service->user_id);
                    $active_sheet->setCellValue('D' . $i, $ad_service->number);

                    if ($ad_service->type == 'INSURANCE' && in_array($ad_service->amount_insurance, [200, 400]))
                        $active_sheet->setCellValue('E' . $i, 'Страхование БК');
                    else
                        $active_sheet->setCellValue('E' . $i, $op_type[$ad_service->type]);

                    $active_sheet->setCellValue('F' . $i, $ad_service->id);
                    $active_sheet->setCellValue('G' . $i, $ad_service->uid);
                    $active_sheet->setCellValue('H' . $i, $fio_birth);
                    $active_sheet->setCellValue('I' . $i, $ad_service->phone_mobile);
                    $active_sheet->setCellValue('J' . $i, $gender[$ad_service->gender]);
                    $active_sheet->setCellValue('K' . $i, $ad_service->passport_serial);
                    $active_sheet->setCellValue('L' . $i, $ad_service->regAddr);

                    if ($ad_service->start_date) {
                        $active_sheet->setCellValue('M' . $i, $ad_service->start_date . '/' . $ad_service->end_date);
                    } else {
                        $active_sheet->setCellValue('M' . $i, '-');
                    }
                    if ($ad_service->number) {
                        $active_sheet->setCellValue('N' . $i, ($ad_service->amount_contract * 3) . ' руб');
                    }
                    $active_sheet->setCellValue('O' . $i, $ad_service->amount_insurance . 'руб');

                    $i++;
                }

                foreach ($card_binding as $card) {

                    if ($ad_service->Regcity) {
                        $address = "$card->Regindex $card->Regcity $card->Regstreet_shorttype $card->Regstreet $card->Reghousing $card->Regroom";

                    } else {
                        $address = "$card->Regindex $card->Reglocality $card->Regstreet_shorttype $card->Regstreet $card->Reghousing $card->Regroom";
                    }

                    $fio_birth = "$card->lastname $card->firstname $card->patronymic $card->birth";


                    $active_sheet->setCellValue('A' . $i, $card->created);
                    $active_sheet->setCellValue('B' . $i, $card->contract_id);
                    $active_sheet->setCellValue('C' . $i, $card->user_id);
                    $active_sheet->setCellValue('D' . $i, $card->number);
                    $active_sheet->setCellValue('E' . $i, $card->description);
                    $active_sheet->setCellValue('F' . $i, $card->id);
                    $active_sheet->setCellValue('G' . $i, $card->uid);
                    $active_sheet->setCellValue('H' . $i, $fio_birth);
                    $active_sheet->setCellValue('I' . $i, $card->phone_mobile);
                    $active_sheet->setCellValue('J' . $i, $gender[$card->gender]);
                    $active_sheet->setCellValue('K' . $i, $card->passport_serial);
                    $active_sheet->setCellValue('L' . $i, $address);

                    if ($card->start_date) {
                        $active_sheet->setCellValue('M' . $i, $card->start_date . '/' . $card->end_date);
                    } else {
                        $active_sheet->setCellValue('M' . $i, '-');
                    }
                    if ($card->number) {
                        $active_sheet->setCellValue('N' . $i, ($card->amount_contract * 3) . ' руб');
                    }
                    $active_sheet->setCellValue('O' . $i, '1 руб');

                    $i++;
                }

                $objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');

                $objWriter->save($this->config->root_dir . $filename);

                header('Location:' . $this->config->root_url . '/' . $filename);
                exit;
            }

        }


        return $this->design->fetch('statistics/adservices.tpl');
    }

    private function action_adservices_osv()
    {
        if ($daterange = $this->request->get('daterange')) {
            list($from, $to) = explode('-', $daterange);

            $date_from = date('Y-m-d', strtotime($from));
            $date_to = date('Y-m-d', strtotime($to));

            $this->design->assign('date_from', $date_from);
            $this->design->assign('date_to', $date_to);
            $this->design->assign('from', $from);
            $this->design->assign('to', $to);


            $filter = array();
            $filter['date_from'] = $date_from;
            $filter['date_to'] = $date_to;

            $ad_services = $this->operations->operations_contracts_insurance_reject($filter);

            foreach ($ad_services as $service) {
                $service->regAddr = AdressesORM::find($service->regaddress_id);
                $service->regAddr = $service->regAddr->adressfull;
            }

            // $op_type = ['INSURANCE' => 'Страхование от НС', 'BUD_V_KURSE' => 'Будь в курсе', 'REJECT_REASON' => 'Узнай причину отказа', 'INSURANCE_CLOSED' => 'Страхование БК'];
            // $gender = ['male' => 'Мужской', 'female' => 'Женский'];

            $this->design->assign('ad_services', $ad_services);
            // $this->design->assign('op_type', $op_type);
            // $this->design->assign('gender', $gender);

            if ($this->request->get('download') == 'excel') {

                $filename = 'files/reports/Отчет по услугам в виде ОСВ.xls';
                require $this->config->root_dir . 'PHPExcel/Classes/PHPExcel.php';

                $excel = new PHPExcel();

                $excel->setActiveSheetIndex(0);
                $active_sheet = $excel->getActiveSheet();

                $active_sheet->setTitle($from . "-" . $to);

                $excel->getDefaultStyle()->getFont()->setName('Calibri')->setSize(12);
                $excel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

                $active_sheet->getColumnDimension('A')->setWidth(30);
                $active_sheet->getColumnDimension('B')->setWidth(15);
                $active_sheet->getColumnDimension('C')->setWidth(15);
                $active_sheet->getColumnDimension('D')->setWidth(15);
                $active_sheet->getColumnDimension('E')->setWidth(15);
                $active_sheet->getColumnDimension('F')->setWidth(15);
                $active_sheet->getColumnDimension('G')->setWidth(15);

                $active_sheet->setCellValue('A1', 'ФИО клиента');
                $active_sheet->setCellValue('A2', 'Идентификатор услуги');
                $active_sheet->mergeCells('B1:C1');   
                $active_sheet->setCellValue('B1', 'Сальдо на начало периода');
                $active_sheet->setCellValue('B2', 'Дебет');
                $active_sheet->setCellValue('C2', 'Кредит');
                $active_sheet->mergeCells('D1:E1');   
                $active_sheet->setCellValue('D1', 'Обороты за период');
                $active_sheet->setCellValue('D2', 'Дебет');
                $active_sheet->setCellValue('E2', 'Кредит');
                $active_sheet->mergeCells('F1:G1');   
                $active_sheet->setCellValue('F1', 'Сальдо на конец  периода');
                $active_sheet->setCellValue('F2', 'Дебет');
                $active_sheet->setCellValue('G2', 'Кредит');

                $active_sheet->getStyle('A1:G2')->getAlignment()->setHorizontal('center');
                $border = array(
                    'borders'=>array(
                        'allborders' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN,
                            'color' => array('rgb' => '000000')
                        ),
                    ),
                );    
                $active_sheet->getStyle('A1:G2')->applyFromArray($border);
                $border = array(
                    'borders'=>array(
                        'bottom' => array(
                            'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
                            'color' => array('rgb' => '000000')
                        ),
                    ),
                );    
                $active_sheet->getStyle('A1:G2')->applyFromArray($border);
                
                $i = 3;
                foreach ($ad_services as $ad_service) {

                    $fio_birth = "$ad_service->lastname $ad_service->firstname $ad_service->patronymic $ad_service->birth";

                    $active_sheet->setCellValue('A' . $i, $ad_service->lastname.' '.$ad_service->firstname.' '.$ad_service->patronymic);
                    if ($ad_service->type == 'INSURANCE')
                        $service_id = '60332810000000000005 - НС';
                    elseif ($ad_service->type == 'BUD_V_KURSE')
                        $service_id = '60332810000000000006 - СМС';
                    elseif ($ad_service->type == 'REJECT_REASON')
                        $service_id = '60332810000000000007 - ОТКАЗ';
                    else
                        $service_id = '60332810000000000008 - БК';
                    $active_sheet->setCellValue('A' . ($i+1), $service_id);
                    $active_sheet->mergeCells('B' . $i . ':B' . ($i+1));
                    $active_sheet->mergeCells('C' . $i . ':C' . ($i+1));
                    $active_sheet->mergeCells('D' . $i . ':D' . ($i+1));                    $active_sheet->setCellValue('D' . $i, $ad_service->amount_insurance);
                    $active_sheet->setCellValueExplicit('D' . $i, $ad_service->amount_insurance, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                    $active_sheet->mergeCells('E' . $i . ':E' . ($i+1));
                    $active_sheet->mergeCells('F' . $i . ':F' . ($i+1));
                    $active_sheet->mergeCells('G' . $i . ':G' . ($i+1));
 
                    $active_sheet->getStyle('A' . $i . ':G' . ($i+1))->getAlignment()->setHorizontal('center');
                    $border = array(
                        'borders'=>array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                'color' => array('rgb' => '000000')
                            ),
                        ),
                    );    
                    $active_sheet->getStyle('A' . $i . ':G' . ($i+1))->applyFromArray($border);
                    $border = array(
                        'borders'=>array(
                            'bottom' => array(
                                'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
                                'color' => array('rgb' => '000000')
                            ),
                        ),
                    ); 
                    $active_sheet->getStyle('A' . $i . ':G' . ($i+1))->applyFromArray($border);   

                    $i = $i + 2;
                }

                $objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');

                $objWriter->save($this->config->root_dir . $filename);

                header('Location:' . $this->config->root_url . '/' . $filename);
                exit;
            }

        }


        return $this->design->fetch('statistics/adservices_osv.tpl');
    }


    private function payment_split($contract_id , $date_to)
    {
        
        $query = $this->db->placehold("
            SELECT *
            FROM __operations        AS o
            WHERE o.contract_id = ?
            AND (o.type = 'P2P' OR o.type = 'PERCENTS' OR o.type = 'PENI')
            AND DATE(o.created) >= ?
            AND DATE(o.created) <= ?
            ORDER BY order_id, created, id
        ", $contract_id , '2023-01-01', $date_to);

        $this->db->query($query);

        $od = 0;
        $percents = 0;
        $peni = 0;

        foreach ($this->db->results() as $op) {
            if($op->type == 'P2P'){
                $od += $op->amount;
            }
            elseif($op->type == 'PERCENTS')
            {
                $percents += $op->amount;
            }
            elseif($op->type == 'PENI')
            {
                $peni += $op->amount;
            }        
        }

        return [$od, $percents, $peni];

    }
    private function action_loan_portfolio()
    {
        if ($daterange = $this->request->get('daterange')) {
            list($from, $to) = explode('-', $daterange);

            $date_from = date('Y-m-d', strtotime($from));
            $date_to = date('Y-m-d', strtotime($to));

            $this->design->assign('date_from', $date_from);
            $this->design->assign('date_to', $date_to);
            $this->design->assign('from', $from);
            $this->design->assign('to', $to);
            
            $date = date('Y-m-d', strtotime($this->request->get('date')));
            if($date < $date_from){
                $date = $date_to;
            }
            $this->design->assign('date', $date);
            $period = $this->request->get('period');
            
            $this->design->assign('period', $period);

            $query = $this->db->placehold("
                SELECT distinct *
                FROM __contracts
                WHERE  1
                AND DATE(accept_date) >= ?
                AND DATE(accept_date) <= ?
                AND (status = 2 OR status = 3 OR status = 4)
            ", $date_from, $date_to);
            $this->db->query($query);

            $issued_all = 0;
            $issued_count = 0;

            $issued_contracts_od = 0;
            $issued_contracts_percents = 0;
            $issued_contracts_peni = 0;
            $contracts = $this->db->results();

            foreach ($contracts as $c) {
                $issued_all += $c->amount;
                $issued_count += 1;


                if(date('Y-m-d', strtotime($c->close_date)) >= $date_from && 
                date('Y-m-d', strtotime($c->close_date)) <= $date){
                    $ret = $this->payment_split($c->id, date('Y-m-d', strtotime($c->close_date)));
                }
                else{
                    $ret = $this->payment_split($c->id, $date);

                }
                $issued_contracts_od += $ret[0];
                $issued_contracts_percents += $ret[1];
                $issued_contracts_peni += $ret[2];
            }
            $this->design->assign('issued_all', $issued_all);
            $this->design->assign('issued_count', $issued_count);
            $this->design->assign('issued_contracts_od', $issued_contracts_od);
            $this->design->assign('issued_contracts_percents', $issued_contracts_percents);
            $this->design->assign('issued_contracts_peni', $issued_contracts_peni);

            $query = $this->db->placehold("
                SELECT *
                FROM __contracts AS c
                WHERE 1
                AND DATE(c.return_date) > ?
                AND DATE(c.create_date) <= ?

                AND (DATE(c.close_date) > ?
                OR c.close_date is NULL)

                AND DATE(accept_date) >= ?
                AND DATE(accept_date) <= ?
                AND (status = 2 OR status = 3 OR status = 4)

                AND c.id NOT in(
                    SELECT distinct c.id
                    FROM __contracts AS c
                    RIGHT JOIN __prolongations AS p
                    ON c.id = p.contract_id
                    WHERE 1
                    AND DATE(p.created) >= ?
                    AND DATE(p.created) <= ?
                    AND DATE(c.accept_date) >= ?
                    AND DATE(c.accept_date) <= ?
                    AND (c.status = 2 OR c.status = 3 OR c.status = 4)

                    AND (DATE(c.close_date) > ? OR c.close_date IS null)
                    )
            ", $date, $date_to, $date, $date_from, $date_to,
            $date_from, $date, $date_from, $date_to, $date);

            $this->db->query($query);
            
            $count_active_contracts = 0;

            $active_contracts_od = 0;
            $active_contracts_percents = 0;
            $active_contracts_peni = 0;
            $contracts = $this->db->results();
            foreach ($contracts as $c) {
                $count_active_contracts += 1;

                $ret = $this->payment_split($c->id, $date);
                $active_contracts_od += $ret[0];
                $active_contracts_percents += $ret[1];
                $active_contracts_peni += $ret[2];
            }

            $this->design->assign('count_active_contracts', $count_active_contracts);
            $this->design->assign('active_contracts_od', $active_contracts_od);
            $this->design->assign('active_contracts_percents', $active_contracts_percents);
            $this->design->assign('active_contracts_peni', $active_contracts_peni);


            $query = $this->db->placehold("
                SELECT *
                FROM __contracts AS c
                WHERE 1
                AND DATE(c.return_date) <= ?
                AND (c.close_date > c.return_date OR c.close_date IS null)

                AND (DATE(c.close_date) > ?
                OR c.close_date is NULL)

                AND DATE(accept_date) >= ?
                AND DATE(accept_date) <= ?
                AND (status = 2 OR status = 3 OR status = 4)

                AND c.id NOT in(
                    SELECT distinct c.id
                    FROM __contracts AS c
                    RIGHT JOIN __prolongations AS p
                    ON c.id = p.contract_id
                    WHERE 1
                    AND DATE(p.created) >= ?
                    AND DATE(p.created) <= ?
                    AND DATE(c.accept_date) >= ?
                    AND DATE(c.accept_date) <= ?
                    AND (c.status = 2 OR c.status = 3 OR c.status = 4)

                    AND (DATE(c.close_date) > ? OR c.close_date IS null)
                    )
            ", $date, $date, $date_from, $date_to,
            $date_from, $date, $date_from, $date_to, $date);

            $this->db->query($query);
            
            $count_delay_contracts = 0;
            $delay_contracts_all = 0;

            $delay_contracts_od = 0;
            $delay_contracts_percents = 0;
            $delay_contracts_peni = 0;
            $contracts = $this->db->results();
            foreach ($contracts as $c) {
                $delay_contracts_all += $c->amount;
                $count_delay_contracts += 1;

                $ret = $this->payment_split($c->id, $date);
                $delay_contracts_od += $ret[0];
                $delay_contracts_percents += $ret[1];
                $delay_contracts_peni += $ret[2];
            }

            $this->design->assign('delay_contracts_all', $delay_contracts_all);
            $this->design->assign('count_delay_contracts', $count_delay_contracts);
            $this->design->assign('delay_contracts_od', $delay_contracts_od);
            $this->design->assign('delay_contracts_percents', $delay_contracts_percents);
            $this->design->assign('delay_contracts_peni', $delay_contracts_peni);


            $query = $this->db->placehold("
                SELECT *
                FROM __contracts AS c
                WHERE 1
                AND DATE(c.close_date) >= ?
                AND DATE(c.close_date) <= ?

                AND DATE(accept_date) >= ?
                AND DATE(accept_date) <= ?
                AND (status = 2 OR status = 3 OR status = 4)

                AND c.id NOT in(
                    SELECT distinct c.id
                    FROM __contracts AS c
                    RIGHT JOIN __prolongations AS p
                    ON c.id = p.contract_id
                    WHERE 1
                    AND DATE(p.created) >= ?
                    AND DATE(p.created) <= ?
                    AND DATE(c.accept_date) >= ?
                    AND DATE(c.accept_date) <= ?
                    AND (c.status = 2 OR c.status = 3 OR c.status = 4)
                    
                    AND (DATE(c.close_date) > ? OR c.close_date IS null)
                    )
            ", $date_from, $date, $date_from, $date_to,
            $date_from, $date, $date_from, $date_to, $date);

            $this->db->query($query);

            $count_closed_contracts = 0;
            $closed_contracts_all = 0;

            $closed_contracts_od = 0;
            $closed_contracts_percents = 0;
            $closed_contracts_peni = 0;
            foreach ($this->db->results() as $c) {
                $count_closed_contracts += 1;
                $closed_contracts_all += $c->amount;

                $ret = $this->payment_split($c->id, date('Y-m-d', strtotime($c->close_date)));
                $closed_contracts_od += $ret[0];
                $closed_contracts_percents += $ret[1];
                $closed_contracts_peni += $ret[2];
            }

            $this->design->assign('count_closed_contracts', $count_closed_contracts);
            $this->design->assign('closed_contracts_all', $closed_contracts_all);
            $this->design->assign('closed_contracts_od', $closed_contracts_od);
            $this->design->assign('closed_contracts_percents', $closed_contracts_percents);
            $this->design->assign('closed_contracts_peni', $closed_contracts_peni);

            $query = $this->db->placehold("
            SELECT distinct c.*
                FROM __contracts AS c
                RIGHT JOIN __prolongations AS p
                ON c.id = p.contract_id
                WHERE 1
                AND DATE(p.created) >= ?
                AND DATE(p.created) <= ?

                AND DATE(c.accept_date) >= ?
                AND DATE(c.accept_date) <= ?
                AND (c.status = 2 OR c.status = 3 OR c.status = 4)

                AND (DATE(c.close_date) > ? OR c.close_date IS null)

            ", $date_from, $date, $date_from, $date_to, $date);

            $this->db->query($query);
            
            $count_prolongation_contracts = 0;
            $prolongation_contracts_all = 0;

            $prolongation_contracts_od = 0;
            $prolongation_contracts_percents = 0;
            $prolongation_contracts_peni = 0;
            foreach ($this->db->results() as $c) {
                $count_prolongation_contracts += 1;
                $prolongation_contracts_all += $c->amount;

                $ret = $this->payment_split($c->id, $date);
                $prolongation_contracts_od += $ret[0];
                $prolongation_contracts_percents += $ret[1];
                $prolongation_contracts_peni += $ret[2];

            }

            $this->design->assign('count_prolongation_contracts', $count_prolongation_contracts);
            $this->design->assign('prolongation_contracts_all', $prolongation_contracts_all);
            $this->design->assign('prolongation_contracts_od', $prolongation_contracts_od);
            $this->design->assign('prolongation_contracts_percents', $prolongation_contracts_percents);
            $this->design->assign('prolongation_contracts_peni', $prolongation_contracts_peni);

            $query = $this->db->placehold("
            SELECT *
            FROM __contracts AS c
            WHERE 1
            #AND DATE(c.close_date) >= ?
            #AND DATE(c.close_date) <= ?

            #AND DATE(accept_date) >= ?
            #AND DATE(accept_date) <= ?
            AND (status = 7)

        ", $date_from, $date, $date_from, $date);

        $this->db->query($query);

        $count_cessia_contracts = 0;
        $cessia_contracts_all = 0;

        $cessia_contracts_od = 0;
        $cessia_contracts_percents = 0;
        $cessia_contracts_peni = 0;
        if ($date_from <= date('2023-12-18') && $date_to >= date('2023-12-18')) {
            foreach ($this->db->results() as $c) {
                $count_cessia_contracts += 1;
                $cessia_contracts_all += $c->amount;
    
                $cessia_close_date = date('2023-12-18');
                // if (!is_null($c->close_date)) {
                //     $cessia_close_date = date('Y-m-d', strtotime($c->close_date));
                // }
                $ret = $this->payment_split($c->id, $cessia_close_date);
                $cessia_contracts_od += $ret[0];
                $cessia_contracts_percents += $ret[1];
                $cessia_contracts_peni += $ret[2];
            }
        }

        $this->design->assign('count_cessia_contracts', $count_cessia_contracts);
        $this->design->assign('cessia_contracts_all', $cessia_contracts_all);
        $this->design->assign('cessia_contracts_od', $cessia_contracts_od);
        $this->design->assign('cessia_contracts_percents', $cessia_contracts_percents);
        $this->design->assign('cessia_contracts_peni', $cessia_contracts_peni);


            $period_start_date = $period == 'period' ? $date_from : '2020-01-01';

            $query = $this->db->placehold("
                SELECT o.*,
                t.callback_response,
                t.sector
                FROM __operations        AS o
                LEFT JOIN __transactions AS t ON t.id = o.transaction_id
                WHERE (o.type = 'PAY')
                AND DATE(o.created) <= ?
                AND o.contract_id IN
                (SELECT distinct id
                FROM __contracts
                WHERE  1
                AND DATE(accept_date) >= ?
                AND DATE(accept_date) <= ?
                AND (status = 2 OR status = 3 OR status = 4))
            ", $date, $period_start_date, $date_to);

            $this->db->query($query);

            $pay_all_contracts_od = 0;
            $pay_all_contracts_percents = 0;
            $pay_all_contracts_peni = 0;
            foreach ($this->db->results() as $op) {
                if($op->sector != 0){
                    if(is_null($op->callback_response))
                        continue;

                    try {
                        $callback = new SimpleXMLElement($op->callback_response);
                    } catch (\Throwable $th) {
                        continue;
                    }
                    
                    if($callback->order_state != 'COMPLETED')
                        continue;
                }
                else{
                    continue;
                }
                

                if($op->type == 'PAY'){
                    $ret = $this->payment_split($op->contract_id, date('Y-m-d', strtotime($op->created)));
                    // $pay_all_contracts_od += $op->amount - $ret[1] - $ret[2];
                    // $pay_all_contracts_percents += $ret[1];
                    // $pay_all_contracts_peni += $ret[2];

                    $transaction = $this->transactions->get_transaction($op->transaction_id);
                    $pay_all_contracts_od += $transaction->loan_body_summ;
                    $pay_all_contracts_percents += $transaction->loan_percents_summ;
                    $pay_all_contracts_peni += $transaction->loan_peni_summ;
                }
                else{
                    $pay_all_contracts_od += $op->amount;
                }
            }

            $this->design->assign('pay_all_contracts_od', $pay_all_contracts_od);
            $this->design->assign('pay_all_contracts_percents', $pay_all_contracts_percents);
            $this->design->assign('pay_all_contracts_peni', $pay_all_contracts_peni);


            // $query = $this->db->placehold("
            //     SELECT *
            //     FROM __operations        AS o
            //     WHERE (o.type = 'PAY' OR o.type = 'P2P' OR o.type = 'PERCENTS' OR o.type = 'PENI')
            //     AND DATE(o.created) >= ?
            //     AND DATE(o.created) <= ?
            //     ORDER BY order_id, created, id
            // ", $date_to, $date_to);

            // $this->db->query($query);

            // $od = 0;
            // $percents = 0;
            // $od_client = 0;
            // $percents_client = 0;
            // $order_id = 0;
            // foreach ($this->db->results() as $op) {
            //     if($order_id != $op->order_id){
            //         $order_id = $op->order_id;
            //         $od += $od_client;
            //         $percents += $percents_client;
            //         $od_client = 0;
            //         $percents_client = 0;
            //     }
            //     if($op->type == 'P2P'){
            //         $od_client = $op->amount;
            //     }
            //     else{
            //         $od_client = $op->loan_body_summ;
            //         $percents_client = $op->loan_percents_summ;
            //     }
            // }
            // $od += $od_client;
            // $percents += $percents_client;

            // $this->design->assign('od', $od);
            // $this->design->assign('percents', $percents);


            $query = $this->db->placehold("
                SELECT *
                FROM __operations        AS o
                WHERE (o.type = 'BUD_V_KURSE' OR o.type = 'INSURANCE' OR
                o.type = 'REJECT_REASON')
                AND DATE(o.created) >= ?
                AND DATE(o.created) <= ?
            ", $period_start_date, $date_to);

            $this->db->query($query);

            $services_all = 0;
            foreach ($this->db->results() as $op) {
                $services_all += $op->amount;
            }
            $this->design->assign('services_all', $services_all);


            if ($this->request->get('download') == 'excel') {

                $filename = 'files/reports/Отчет Портфель.xls';
                require $this->config->root_dir . 'PHPExcel/Classes/PHPExcel.php';

                $excel = new PHPExcel();

                $excel->setActiveSheetIndex(0);
                $active_sheet = $excel->getActiveSheet();

                $active_sheet->setTitle($from . "-" . $to);

                $excel->getDefaultStyle()->getFont()->setName('Calibri')->setSize(12);
                $excel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

                $active_sheet->getColumnDimension('A')->setWidth(30);
                $active_sheet->getColumnDimension('B')->setWidth(15);
                $active_sheet->getColumnDimension('C')->setWidth(15);

                $active_sheet->setCellValue('A1', 'Наименование');
                $active_sheet->setCellValue('B1', 'Количество ШТ');
                $active_sheet->setCellValue('C1', 'Всего');
                $active_sheet->setCellValue('D1', 'ОД');
                $active_sheet->setCellValue('E1', 'Проценты');
                $active_sheet->setCellValue('F1', 'Пени');

                $active_sheet->setCellValue('A2', 'Выдано');
                $active_sheet->setCellValue('B2' , $issued_count);
                $active_sheet->setCellValue('C2' , $issued_contracts_od+$issued_contracts_percents+$issued_contracts_peni);
                $active_sheet->setCellValue('D2' , $issued_contracts_od);
                $active_sheet->setCellValue('E2' , $issued_contracts_percents);
                $active_sheet->setCellValue('F2' , $issued_contracts_peni);

                $active_sheet->setCellValue('A3', 'Активные займы');
                $active_sheet->setCellValue('B3' , $count_active_contracts);
                $active_sheet->setCellValue('C3' , $active_contracts_od+$active_contracts_percents+$active_contracts_peni);
                $active_sheet->setCellValue('D3' , $active_contracts_od);
                $active_sheet->setCellValue('E3' , $active_contracts_percents);
                $active_sheet->setCellValue('F3' , $active_contracts_peni);

                $active_sheet->setCellValue('A4', 'Просроченные займы');
                $active_sheet->setCellValue('B4' , $count_delay_contracts);
                $active_sheet->setCellValue('C4' , $delay_contracts_od+$delay_contracts_percents+$delay_contracts_peni);
                $active_sheet->setCellValue('D4' , $delay_contracts_od);
                $active_sheet->setCellValue('E4' , $delay_contracts_percents);
                $active_sheet->setCellValue('F4' , $delay_contracts_peni);

                $active_sheet->setCellValue('A5', 'Закрытые договоры');
                $active_sheet->setCellValue('B5' , $count_closed_contracts);
                $active_sheet->setCellValue('C5' , $closed_contracts_od+$closed_contracts_percents+$closed_contracts_peni);
                $active_sheet->setCellValue('D5' , $closed_contracts_od);
                $active_sheet->setCellValue('E5' , $closed_contracts_percents);
                $active_sheet->setCellValue('F5' , $closed_contracts_peni);

                $active_sheet->setCellValue('A6', 'Продленные договоры');
                $active_sheet->setCellValue('B6' , $count_prolongation_contracts);
                $active_sheet->setCellValue('C6' , $prolongation_contracts_od+$prolongation_contracts_percents+$prolongation_contracts_peni);
                $active_sheet->setCellValue('D6' , $prolongation_contracts_od);
                $active_sheet->setCellValue('E6' , $prolongation_contracts_percents);
                $active_sheet->setCellValue('F6' , $prolongation_contracts_peni);

                $active_sheet->setCellValue('A7', 'Итого собрано (ОД + проценты)');
                $active_sheet->setCellValue('C7' , $pay_all_contracts_od+$pay_all_contracts_percents+$pay_all_contracts_peni);
                $active_sheet->setCellValue('D7' , $pay_all_contracts_od);
                $active_sheet->setCellValue('E7' , $pay_all_contracts_percents);
                $active_sheet->setCellValue('F7' , $pay_all_contracts_peni);

                $active_sheet->setCellValue('A8', 'Остаток ОД');
                $active_sheet->setCellValue('C8' , $issued_contracts_od - $pay_all_contracts_od);

                $active_sheet->setCellValue('A9', 'Начисленные и неоплаченные проценты');
                $active_sheet->setCellValue('C9' , $issued_contracts_percents - $pay_all_contracts_percents);
                $active_sheet->setCellValue('A10', 'Остаток ОД + проценты');
                $active_sheet->setCellValue('C10' , $issued_contracts_od - $pay_all_contracts_od + $issued_contracts_percents - $pay_all_contracts_percents);
                $active_sheet->setCellValue('A11', 'Сумма дополнительных услуг');
                $active_sheet->setCellValue('C11' , $services_all);

 

                $objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');

                $objWriter->save($this->config->root_dir . $filename);

                header('Location:' . $this->config->root_url . '/' . $filename);
                exit;
            }

        }


        return $this->design->fetch('statistics/loan_portfolio.tpl');
    }

    private function action_sources()
    {
        $integrations = $this->Integrations->get_integrations();
        $this->design->assign('integrations', $integrations);

        if ($action = $this->request->get('to-do', 'string')) {
            if ($action == 'report') {

                $daterange = $this->request->get('daterange');

                list($from, $to) = explode('-', $daterange);

                $date_from = date('Y-m-d', strtotime($from));
                $date_to = date('Y-m-d', strtotime($to));
                $this->design->assign('from', $from);
                $this->design->assign('to', $to);
                $this->design->assign('date_from', $date_from);
                $this->design->assign('date_to', $date_to);


                $filter = array();
                $filter['date_from'] = $date_from;
                $filter['date_to'] = $date_to;

                foreach ($integrations as $integration) {
                    $filter['integrations'][] = $integration->utm_source;
                }
                $filter['integrations'][] = 'Rbl API';
                $filter['integrations'][] = 'Hetag API';

                $utm_source_filter = $this->request->get('utm_source_filter');
                $utm_medium_filter = $this->request->get('utm_medium_filter');
                $utm_campaign_filter = $this->request->get('utm_campaign_filter');
                $utm_term_filter = $this->request->get('utm_term_filter');
                $utm_content_filter = $this->request->get('utm_content_filter');


                if ($this->request->get('utm_source'))
                    $filter['utm_source'][] = 'utm_source';

                if ($this->request->get('utm_medium'))
                    $filter['utm_source'][] = 'utm_medium';

                if ($this->request->get('utm_campaign'))
                    $filter['utm_source'][] = 'utm_campaign';

                if ($this->request->get('utm_term'))
                    $filter['utm_source'][] = 'utm_term';

                if ($this->request->get('utm_content'))
                    $filter['utm_source'][] = 'utm_content';


                $filtres = [];


                if ($utm_source_filter) {
                    $filter['utm_source_filter'] = $this->request->get('utm_source_filter_val');
                    $filtres['utm_source_filter'] = $filter['utm_source_filter'];
                }

                if ($utm_medium_filter) {
                    $filter['utm_medium_filter'] = $this->request->get('utm_medium_filter_val');
                    $filtres['utm_medium_filter'] = $filter['utm_medium_filter'];
                }


                if ($utm_campaign_filter) {
                    $filter['utm_campaign_filter'] = $this->request->get('utm_campaign_filter_val');
                    $filtres['utm_campaign_filter'] = $filter['utm_campaign_filter'];
                }


                if ($utm_term_filter) {
                    $filter['utm_term_filter'] = $this->request->get('utm_term_filter_val');
                    $filtres['utm_term_filter'] = $filter['utm_term_filter'];
                }


                if ($utm_content_filter) {
                    $filter['utm_content_filter'] = $this->request->get('utm_content_filter_val');
                    $filtres['utm_content_filter'] = $filter['utm_content_filter'];
                }

                $this->design->assign('filtres', $filtres);

                $group_by = $this->request->get('group_by');
                $filter['date_group_by'] = $this->request->get('date_group_by');
                $filter['group_by'] = $group_by;

                $this->design->assign('date_group_by', $filter['date_group_by']);

                $orders = $this->orders->get_orders_by_utm($filter);
                // var_dump('$orders');
                // die;

                $visits = $this->Visits->search_visits($filter);

                $this->design->assign('group_by', $group_by);

                $months = [
                    '01' => 'Январь',
                    '02' => 'Февраль',
                    '03' => 'Март',
                    '04' => 'Апрель',
                    '05' => 'Май',
                    '06' => 'Июнь',
                    '07' => 'Июль',
                    '08' => 'Август',
                    '09' => 'Сентябрь',
                    '10' => 'Октябрь',
                    '11' => 'Ноябрь',
                    '12' => 'Декабрь',
                ];

                $this->design->assign('months', $months);

                $all_params =
                    [
                        'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content',
                        'visits', 'all_orders', 'CR', 'orders_nk',
                        'orders_pk', 'orders_bk', 'accept_all',
                        'accept_nk', 'accept_pk', 'accept_bk',
                        'ar_all', 'ar_nk', 'ar_pk', 'ar_bk',
                        'reject_all', 'reject_all_prc',
                        'reject_nk', 'reject_nk_prc', 'reject_pk',
                        'reject_pk_prc', 'reject_bk', 'reject_bk_prc',
                        'check_all_summ', 'check_nk_summ', 'check_pk_summ',
                        'check_srch', 'check_srch_nk', 'check_srch_pk',
                        'orders_on_check'
                    ];

                foreach ($all_params as $k => $param) {
                    if ($this->request->get($param) == 1)
                        $all_get_params[$param] = $param;
                }

                $this->design->assign('all_get_params', $all_get_params);

                $months =
                    [
                        1 => 'Январь',
                        2 => 'Февраль',
                        3 => 'Март',
                        4 => 'Апрель',
                        5 => 'Май',
                        6 => 'Июнь',
                        7 => 'Июль',
                        8 => 'Август',
                        9 => 'Сентябрь',
                        10 => 'Октябрь',
                        11 => 'Ноябрь',
                        12 => 'Декабрь'
                    ];

                if ($filter['date_group_by'] == 'issuance') {

                    $contracts = $this->orders->get_orders_contracts_issuance($filter);

                    foreach ($orders as $key => $order) {
                        $orders[$key]->accept_all = 0;
                        $orders[$key]->accept_pk = 0;
                        $orders[$key]->accept_nk = 0;
                        $orders[$key]->accept_bk = 0;
                        foreach ($contracts as $k => $contract) {

                            if ($order->utm_source == $contract->utm_source) {
                                if ($contract->group_date == $order->group_date) {
                                    if ($this->request->get('accept_all') == 1)
                                        $orders[$key]->accept_all = ($contract->accept_all) ? $contract->accept_all : 0;

                                    if ($this->request->get('accept_pk') == 1)
                                        $orders[$key]->accept_pk = ($contract->accept_pk) ? $contract->accept_pk : 0;

                                    if ($this->request->get('accept_nk') == 1)
                                        $orders[$key]->accept_nk = ($contract->accept_nk) ? $contract->accept_nk : 0;

                                    if ($this->request->get('accept_bk') == 1)
                                        $orders[$key]->accept_bk = ($contract->accept_bk) ? $contract->accept_bk : 0;

                                }
                            }
                        }
                    }
                }

                if ($this->request->get('visits') == 1) {
                    foreach ($visits as $visit) {
                        foreach ($orders as $key => $order) {
                            if ($order->utm_source == $visit->utm_source) {
                                $orders[$key]->visits = $visit->count_visit;
                            }
                        }
                    }
                }

                foreach ($orders as $key => $order) {
                    if ($this->request->get('CR') == 1
                        && isset($order->all_orders)
                        && isset($order->visits)
                        && $order->all_orders != 0
                        && $order->visits != 0) {
                        $order->CR = (int)($order->all_orders / $order->visits * 100);
                    } else {
                        $order->CR = 0;
                    }

                    if ($this->request->get('ar_all') == 1
                        && isset($order->accept_all)
                        && isset($order->all_orders)
                        && $order->accept_all != 0
                        && $order->all_orders != 0) {
                        $order->ar_all = (int)($order->accept_all / $order->all_orders * 100);
                    } else {
                        $order->ar_all = 0;
                    }

                    if ($this->request->get('ar_nk') == 1
                        && isset($order->accept_nk)
                        && isset($order->orders_nk)
                        && $order->accept_nk != 0
                        && $order->orders_nk != 0) {
                        $order->ar_nk = (int)($order->accept_nk / $order->orders_nk * 100);
                    } else {
                        $order->ar_nk = 0;
                    }

                    if ($this->request->get('ar_pk') == 1
                        && isset($order->accept_pk)
                        && isset($order->orders_pk)
                        && $order->accept_pk != 0
                        && $order->orders_pk != 0) {
                        $order->ar_pk = (int)($order->accept_pk / $order->orders_pk * 100);
                    } else {
                        $order->ar_pk = 0;
                    }

                    if ($this->request->get('ar_bk') == 1
                        && isset($order->accept_bk)
                        && isset($order->orders_bk)
                        && $order->accept_bk != 0
                        && $order->orders_bk != 0) {
                        $order->ar_bk = (int)($order->accept_bk / $order->orders_bk * 100);
                    } else {
                        $order->ar_bk = 0;
                    }

                    if ($this->request->get('reject_all_prc') == 1
                        && isset($order->reject_all)
                        && isset($order->all_orders)
                        && $order->reject_all != 0
                        && $order->all_orders != 0) {
                        $order->reject_all_prc = (int)($order->reject_all / $order->all_orders * 100);
                    } else {
                        $order->reject_all_prc = 0;
                    }

                    if ($this->request->get('reject_nk_prc') == 1
                        && isset($order->reject_nk)
                        && isset($order->orders_nk)
                        && $order->reject_nk != 0
                        && $order->orders_nk != 0) {
                        $order->reject_nk_prc = (int)($order->reject_nk / $order->orders_nk * 100);
                    } else {
                        $order->reject_nk_prc = 0;
                    }

                    if ($this->request->get('reject_pk_prc') == 1
                        && isset($order->reject_pk)
                        && isset($order->orders_pk)
                        && $order->reject_pk != 0
                        && $order->orders_pk != 0) {
                        $order->reject_pk_prc = (int)($order->reject_pk / $order->orders_pk * 100);
                    } else {
                        $order->reject_pk_prc = 0;
                    }

                    if ($this->request->get('reject_bk_prc') == 1
                        && isset($order->reject_bk)
                        && isset($order->orders_bk)
                        && $order->reject_bk != 0
                        && $order->orders_bk != 0) {
                        $order->reject_bk_prc = (int)($order->reject_bk / $order->orders_bk * 100);
                    } else {
                        $order->reject_bk_prc = 0;
                    }
                }

                $i = 0;
                $results = array();

                foreach ($orders as $order) {
                    foreach ($all_get_params as $param) {
                        if (isset($order->{$param})) {

                            if ($group_by == 'week') {
                                $dto = new DateTime();
                                $dto->setISODate($order->year, $order->group_date);
                                $ret['week_start'] = $dto->format('d.m.Y');
                                $dto->modify('+6 days');
                                $ret['week_end'] = $dto->format('d.m.Y');

                                $key = $ret['week_start'] . ' - ' . $ret['week_end'];
                            } elseif ($group_by == 'month') {
                                $key = $months[$order->group_date];
                            } else {
                                $key = $order->group_date;
                            }

                            $results[$key][$i][$param] = $order->{$param};
                            $results[$key][$i]['visits'] = 0;
                        }
                    }
                    $i++;
                }

                $all_thead =
                    [
                        'utm_source' => 'Источник',
                        'utm_medium' => 'Канал',
                        'utm_campaign' => 'Кампания',
                        'utm_term' => 'Таргетинг',
                        'utm_content' => 'Контент',
                        'visits' => 'Визиты',
                        'all_orders' => 'Заявки',
                        'orders_nk' => 'Заявки НК',
                        'orders_pk' => 'Заявки ПК',
                        'orders_bk' => 'Заявки ПБ',
                        'CR' => 'CR %',
                        'accept_all' => 'Выдано',
                        'accept_nk' => 'Выдано НК',
                        'accept_pk' => 'Выдано ПК',
                        'accept_bk' => 'Выдано ПБ',
                        'ar_all' => 'AR %',
                        'ar_nk' => 'AR НК%',
                        'ar_pk' => 'AR ПК%',
                        'ar_bk' => 'AR ПБ%',
                        'reject_all' => 'Отказы',
                        'reject_all_prc' => 'Отказы %',
                        'reject_nk' => 'Отказы НК',
                        'reject_nk_prc' => 'Отказы НК%',
                        'reject_pk' => 'Отказы ПК',
                        'reject_pk_prc' => 'Отказы ПК%',
                        'reject_bk' => 'Отказы ПБ',
                        'reject_bk_prc' => 'Отказы ПБ%',
                        'check_all_summ' => 'Сумма',
                        'check_nk_summ' => 'Cумма НК',
                        'check_pk_summ' => 'Сумма ПК',
                        'check_srch' => 'СРЧ',
                        'check_srch_nk' => 'СРЧ НК',
                        'check_srch_pk' => 'СРЧ ПК',
                        'orders_on_check' => 'Проверка',
                    ];

                $group_results = array();
                $thead = array();

                foreach ($results as $key => $result) {
                    foreach ($result as $date => $value) {
                        foreach ($all_thead as $k => $head) {
                            if (array_key_exists($k, $value)) {
                                $group_results[$key][$date][$k] = $value[$k];
                                $thead[$k] = $head;
                            }
                        }
                    }
                }

                $this->design->assign('thead', $thead);
                $this->design->assign('results', $group_results);
            }
        }

        return $this->design->fetch('statistics/sources.tpl');
    }

    private function action_conversions()
    {
        if ($action = $this->request->get('to-do', 'string')) {
            if ($action == 'report') {

                $items_per_page = $this->request->get('page_count');

                if (empty($items_per_page))
                    $items_per_page = 25;

                $this->design->assign('page_count', $items_per_page);

                $daterange = $this->request->get('daterange');

                list($from, $to) = explode('-', $daterange);

                $date_from = date('Y-m-d', strtotime($from));
                $date_to = date('Y-m-d', strtotime($to));

                $this->design->assign('from', $from);
                $this->design->assign('to', $to);
                $this->design->assign('date_from', $date_from);
                $this->design->assign('date_to', $date_to);

                $filter = array();
                $filter['date_from'] = $date_from;
                $filter['date_to'] = $date_to;

                if ($this->request->get('utm_source_filter')) {
                    $filter['utm_source_filter'] = $this->request->get('utm_source_filter_val');
                    $filtres['utm_source_filter'] = $filter['utm_source_filter'];
                }

                if ($this->request->get('utm_medium_filter')) {
                    $filter['utm_medium_filter'] = $this->request->get('utm_medium_filter_val');
                    $filtres['utm_medium_filter'] = $filter['utm_medium_filter'];
                }


                if ($this->request->get('utm_campaign_filter')) {
                    $filter['utm_campaign_filter'] = $this->request->get('utm_campaign_filter_val');
                    $filtres['utm_campaign_filter'] = $filter['utm_campaign_filter'];
                }


                if ($this->request->get('utm_term_filter')) {
                    $filter['utm_term_filter'] = $this->request->get('utm_term_filter_val');
                    $filtres['utm_term_filter'] = $filter['utm_term_filter'];
                }


                if ($this->request->get('utm_content_filter')) {
                    $filter['utm_content_filter'] = $this->request->get('utm_content_filter_val');
                    $filtres['utm_content_filter'] = $filter['utm_content_filter'];
                }

                if (isset($filtres))
                    $this->design->assign('filtres', $filtres);


                if ($this->request->get('date_filter') == 1)
                    $filter['issuance'] = 1;

                $date_select = $this->request->get('date_filter');
                $this->design->assign('date_select', $date_select);

                $all_checkbox = [
                    'id' => 'Заявка',
                    'utm_source' => 'Источник',
                    'utm_medium' => 'Канал',
                    'utm_campaign' => 'Кампания',
                    'utm_term' => 'Таргетинг',
                    'click_hash' => 'Контент',
                    'client_status' => 'Статус клиента',
                    'status' => 'Статус заявки',
                    'leadcraft_postback_type' => 'Постбэк'
                ];

                $thead = array();

                $orders_statuses =
                    [
                        0 => 'Принята',
                        1 => 'На рассмотрении',
                        2 => 'Одобрена',
                        3 => 'Отказ',
                        4 => 'Готов к выдаче',
                        5 => 'Займ выдан',
                        6 => 'Не удалось выдать',
                        7 => 'Погашен',
                        8 => 'Отказ клиента',
                    ];

                $this->design->assign('orders_statuses', $orders_statuses);

                foreach ($all_checkbox as $key => $checkbox) {
                    if ($this->request->get($key) == 1) {
                        $filter['select'][] = $key;
                        $thead[$key] = $checkbox;
                    }
                }

                $current_page = $this->request->get('page', 'integer');
                $current_page = max(1, $current_page);
                $this->design->assign('current_page_num', $current_page);

                $orders = $this->orders->get_orders_for_conversions($filter);
                $orders_count = count($orders);

                $filter['page'] = $current_page;
                $filter['limit'] = $items_per_page;
                $orders = $this->orders->get_orders_for_conversions($filter);

                $pages_num = ceil($orders_count / $items_per_page);

                $this->design->assign('total_pages_num', $pages_num);
                $this->design->assign('total_orders_count', $orders_count);

                $this->design->assign('thead', $thead);
                $this->design->assign('orders', $orders);

                if ($this->request->get('download') == 'excel') {

                    unset($filter['page']);
                    unset($filter['limit']);

                    $orders = $this->orders->get_orders_for_conversions($filter);

                    $filename = 'files/reports/Отчет по конверсиям.xls';
                    require $this->config->root_dir . 'PHPExcel/Classes/PHPExcel.php';

                    $excel = new PHPExcel();

                    $excel->setActiveSheetIndex(0);
                    $active_sheet = $excel->getActiveSheet();

                    $active_sheet->setTitle($from . "-" . $to);

                    $excel->getDefaultStyle()->getFont()->setName('Calibri')->setSize(12);
                    $excel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

                    $active_sheet->getColumnDimension('A')->setWidth(25);
                    $active_sheet->setCellValue('A1', 'Дата');

                    $characters = ['B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K'];
                    $checkboxes = array_values($thead);

                    for ($i = 0; $i <= count($thead); $i++) {
                        $active_sheet->getColumnDimension("$characters[$i]")->setWidth(30);
                        $active_sheet->setCellValue("$characters[$i]" . '1', $checkboxes[$i]);
                    }

                    $i = 2;
                    foreach ($orders as $key => $order) {

                        $active_sheet->setCellValue('A' . $i, $order->date);

                        $ch = 0;
                        foreach ($order as $k => $value) {
                            if ($k != 'date') {
                                if ($k == 'status') {
                                    foreach ($orders_statuses as $kii => $status) {
                                        if ($kii == $value) {
                                            $active_sheet->setCellValue("$characters[$ch]" . $i, $status);
                                        }
                                    }
                                } else {
                                    $active_sheet->setCellValue("$characters[$ch]" . $i, $value);
                                }
                                $ch++;
                            }
                        }

                        $i++;
                    }

                    $objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');

                    $objWriter->save($this->config->root_dir . $filename);

                    header('Location:' . $this->config->root_url . '/' . $filename);
                    exit;
                }
            }
        }

        return $this->design->fetch('statistics/conversions.tpl');
    }

    private function action_orders()
    {
        if ($daterange = $this->request->get('daterange')) {
            list($from, $to) = explode('-', $daterange);

            $filter = array();
            $filter['date_from'] = date('Y-m-d', strtotime($from));
            $filter['date_to'] = date('Y-m-d', strtotime($to));

            $orders = $this->orders->orders_for_risks($filter);
            $orders_statuses = $this->orders->get_statuses();

            foreach ($orders as $key => $order) {
                $order->scoreballs = $this->NbkiScoreballs->get($order->order_id);

                if (empty($order->scoreballs)) {
                    unset($orders[$key]);
                    continue;
                } else {
                    $order->scoreballs->variables = json_decode($order->scoreballs->variables, true);
                    $order->scoreballs->variables['ball'] = $order->scoreballs->ball;
                    $order->scoreballs = $order->scoreballs->variables;
                }

                $order->idx = $this->scorings->get_idx_scoring($order->order_id);

                if (empty($order->idx)) {
                    unset($orders[$key]);
                    continue;
                } else
                    $order->idx = $order->idx->body;

                $order->status = $orders_statuses[$order->status];
            }

            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $spreadsheet->getDefaultStyle()->getFont()->setName('Times New Roman')->setSize(11);

            $sheet = $spreadsheet->getActiveSheet();
            $sheet->getDefaultRowDimension()->setRowHeight(55);
            $sheet->getColumnDimension('A')->setWidth(15);
            $sheet->getColumnDimension('B')->setWidth(15);
            $sheet->getColumnDimension('C')->setWidth(18);
            $sheet->getColumnDimension('D')->setWidth(30);
            $sheet->getColumnDimension('E')->setWidth(15);
            $sheet->getColumnDimension('F')->setWidth(30);
            $sheet->getColumnDimension('G')->setWidth(15);
            $sheet->getColumnDimension('H')->setWidth(15);
            $sheet->getColumnDimension('I')->setWidth(20);

            $styles_cells =
                [
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                ];

            $sheet->getStyle('A:AB')->applyFromArray($styles_cells);

            $sheet->setCellValue('A1', 'ID заявки');
            $sheet->setCellValue('B1', 'ID клиента');
            $sheet->setCellValue('C1', 'Признак new/old');
            $sheet->setCellValue('D1', 'Дата заявки');
            $sheet->setCellValue('E1', 'Решение');
            $sheet->setCellValue('F1', 'Причина отказа');
            $sheet->setCellValue('G1', 'Скоринговый бал');
            $sheet->setCellValue('H1', 'Балл Idx');
            $sheet->setCellValue('I1', 'Одобренный лимит');
            $sheet->setCellValue('J1', 'pdl_overdue_count');
            $sheet->setCellValue('K1', 'pdl_npl_limit_share');
            $sheet->setCellValue('L1', 'pdl_npl_90_limit_share');
            $sheet->setCellValue('M1', 'pdl_current_limit_max');
            $sheet->setCellValue('N1', 'pdl_last_3m_limit');
            $sheet->setCellValue('O1', 'pdl_last_good_max_limit');
            $sheet->setCellValue('P1', 'pdl_good_limit');
            $sheet->setCellValue('Q1', 'pdl_prolong_3m_limit');
            $sheet->setCellValue('R1', 'consum_current_limit_max');
            $sheet->setCellValue('S1', 'consum_good_limit');

            $sheet->setCellValue('T1', 'days_from_last_closed');
            $sheet->setCellValue('U1', 'prev_3000_500_paid_count_wo_del');
            $sheet->setCellValue('V1', 'sumPayedPercents');
            $sheet->setCellValue('W1', 'prev_max_delay');
            $sheet->setCellValue('X1', 'last_credit_delay');
            $sheet->setCellValue('Y1', 'current_overdue_sum');
            $sheet->setCellValue('Z1', 'closed_to_total_credits_count_share');
            $sheet->setCellValue('AA1', 'pdl_overdue_count');
            $sheet->setCellValue('AB1', 'pdl_npl_90_limit_share');

            $i = 2;

            foreach ($orders as $order) {

                $sheet->setCellValue('A' . $i, $order->order_id);
                $sheet->setCellValue('B' . $i, $order->user_id);
                $sheet->setCellValue('C' . $i, $order->client_status);
                $sheet->setCellValue('D' . $i, $order->date);
                $sheet->setCellValue('E' . $i, $order->status);
                $sheet->setCellValue('F' . $i, $order->reject_reason);
                $sheet->setCellValue('G' . $i, $order->scoreballs['ball']);
                $sheet->setCellValue('H' . $i, $order->idx);
                $sheet->setCellValue('I' . $i, $order->scoreballs['limit']);

                if ($order->client_status == 'new') {
                    $sheet->setCellValue('J' . $i, $order->scoreballs['pdl_overdue_count']);
                    $sheet->setCellValue('K' . $i, $order->scoreballs['pdl_npl_limit_share']);
                    $sheet->setCellValue('L' . $i, $order->scoreballs['pdl_npl_90_limit_share']);
                    $sheet->setCellValue('M' . $i, $order->scoreballs['pdl_current_limit_max']);
                    $sheet->setCellValue('N' . $i, $order->scoreballs['pdl_last_3m_limit']);
                    $sheet->setCellValue('O' . $i, $order->scoreballs['pdl_last_good_max_limit']);
                    $sheet->setCellValue('P' . $i, $order->scoreballs['pdl_good_limit']);
                    $sheet->setCellValue('Q' . $i, $order->scoreballs['pdl_prolong_3m_limit']);
                    $sheet->setCellValue('R' . $i, $order->scoreballs['consum_current_limit_max']);
                    $sheet->setCellValue('S' . $i, $order->scoreballs['consum_good_limit']);
                } else {
                    $sheet->setCellValue('T' . $i, $order->scoreballs['days_from_last_closed']);
                    $sheet->setCellValue('U' . $i, $order->scoreballs['prev_3000_500_paid_count_wo_del']);
                    $sheet->setCellValue('V' . $i, $order->scoreballs['sumPayedPercents']);
                    $sheet->setCellValue('W' . $i, $order->scoreballs['prev_max_delay']);
                    $sheet->setCellValue('X' . $i, $order->scoreballs['last_credit_delay']);
                    $sheet->setCellValue('Y' . $i, $order->scoreballs['current_overdue_sum']);
                    $sheet->setCellValue('Z' . $i, $order->scoreballs['closed_to_total_credits_count_share']);
                    $sheet->setCellValue('AA' . $i, $order->scoreballs['pdl_overdue_count']);
                    $sheet->setCellValue('AB' . $i, $order->scoreballs['pdl_npl_90_limit_share']);
                }

                $i++;
            }

            $filename = 'Отчет по заявкам.xlsx';
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save($this->config->root_dir . $filename);
            header('Location:' . $this->config->root_url . '/' . $filename);
            exit;
        }

        return $this->design->fetch('statistics/orders.tpl');
    }

    private function action_leadgens()
    {
        if ($daterange = $this->request->get('daterange')) {
            list($from, $to) = explode('-', $daterange);

            $items_per_page = $this->request->get('page_count');

            if (empty($items_per_page))
                $items_per_page = 25;

            $this->design->assign('page_count', $items_per_page);

            $date_from = date('Y-m-d', strtotime($from));
            $date_to = date('Y-m-d', strtotime($to));

            $this->design->assign('date_from', $date_from);
            $this->design->assign('date_to', $date_to);
            $this->design->assign('from', $from);
            $this->design->assign('to', $to);

            $filter = array();
            $filter['date_from'] = $date_from;
            $filter['date_to'] = $date_to;
            $filter['integration_filter'] = $this->request->get('integration_filter');

            $this->design->assign('integration_filter', $filter['integration_filter']);

            $current_page = $this->request->get('page', 'integer');
            $current_page = max(1, $current_page);
            $this->design->assign('current_page_num', $current_page);

            $count = $this->orders->count_leadgens($filter);

            $filter['page'] = $current_page;
            $filter['limit'] = $items_per_page;

            $orders = $this->orders->leadgens($filter);

            $orders_statuses = $this->orders->get_statuses();

            if (!empty($orders)) {
                foreach ($orders as $order)
                    $order->status = $orders_statuses[$order->status];

                $this->design->assign('orders', $orders);
            }

            $pages_num = ceil($count / $items_per_page);

            $this->design->assign('total_pages_num', $pages_num);
            $this->design->assign('total_orders_count', $count);

            if ($this->request->get('download') == 'excel') {

                unset($filter['page']);
                unset($filter['limit']);

                $orders = $this->orders->leadgens($filter);

                if (!empty($orders)) {
                    foreach ($orders as $order)
                        $order->status = $orders_statuses[$order->status];
                }

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

                $sheet->setCellValue('A1', 'Номер заявки');
                $sheet->setCellValue('B1', 'Номер контракта');
                $sheet->setCellValue('C1', 'Статус');
                $sheet->setCellValue('D1', 'Лидогенератор');
                $sheet->setCellValue('E1', 'ID клика');
                $sheet->setCellValue('F1', 'ID вебмастера');
                $sheet->setCellValue('G1', 'Дата создания');
                $sheet->setCellValue('H1', 'Сумма заявки');
                $sheet->setCellValue('I1', 'Сумма контракта');
                $sheet->setCellValue('J1', 'Ставка');

                $i = 2;

                foreach ($orders as $order) {

                    $sheet->setCellValue('A' . $i, $order->id);
                    $sheet->setCellValue('B' . $i, $order->number);
                    $sheet->setCellValue('C' . $i, $order->status);
                    $sheet->setCellValue('D' . $i, $order->utm_source);
                    $sheet->setCellValue('E' . $i, $order->click_hash);
                    $sheet->setCellValue('F' . $i, $order->webmaster_id);
                    $sheet->setCellValue('G' . $i, date('d.m.Y', strtotime($order->date)));
                    $sheet->setCellValue('H' . $i, $order->amount);
                    $sheet->setCellValue('I' . $i, $order->con_amount);
                    $sheet->setCellValue('J' . $i, 0.00);

                    $i++;
                }

                $filename = 'Отчет Лидген.xlsx';
                $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
                $writer->save($this->config->root_dir . $filename);
                header('Location:' . $this->config->root_url . '/' . $filename);
                exit;
            }
        }

        $integrations = IntegrationsORM::get();
        $this->design->assign('integrations', $integrations);

        return $this->design->fetch('statistics/leadgens.tpl');
    }

    private function action_reconciliation()
    {
        if ($daterange = $this->request->get('daterange')) {
            list($from, $to) = explode('-', $daterange);

            $date_from = date('Y-m-d', strtotime($from));
            $date_to = date('Y-m-d', strtotime($to));

            $this->design->assign('date_from', $date_from);
            $this->design->assign('date_to', $date_to);
            $this->design->assign('from', $from);
            $this->design->assign('to', $to);

            $query = $this->db->placehold("
                SELECT
                    c.id AS contract_id,
                    c.order_id AS order_id,
                    c.number,
                    c.inssuance_date AS date,
                    c.amount,
                    c.user_id,
                    c.status,
                    c.return_date,
                    c.loan_body_summ,
                    c.loan_percents_summ,
                    o.client_status,
                    o.date AS order_date,
                    u.lastname,
                    u.firstname,
                    u.patronymic,
                    u.UID AS uid
                FROM __contracts AS c
                LEFT JOIN __users AS u
                ON u.id = c.user_id
                LEFT JOIN __orders AS o
                ON o.id = c.order_id
                WHERE c.status IN (2, 3, 4, 7)
                AND c.type = 'base'
                AND DATE(c.inssuance_date) >= ?
                AND DATE(c.inssuance_date) <= ?
                ORDER BY inssuance_date
            ", $date_from, $date_to);
            $this->db->query($query);

            $contacts_query = $this->db->results();
            $contracts = array();
            foreach ($contacts_query as $c){
                $contracts[$c->contract_id] = $c;

                $query = $this->db->placehold("
                    SELECT * 
                    FROM __operations
                    WHERE contract_id = ?
                    AND type='PAY'
                    ORDER BY id DESC
                    LIMIT 1
                ", $c->contract_id);
                $this->db->query($query);
                $operation = $this->db->result();

                $contracts[$c->contract_id]->operation_amount = $operation->amount;
                $contracts[$c->contract_id]->operation_date = $operation->created;

            }

            if ($this->request->get('download') == 'excel') {
                $managers = array();
                foreach ($this->managers->get_managers() as $m)
                    $managers[$m->id] = $m;

                $filename = 'files/reports/Отчет по сверке.xls';
                require $this->config->root_dir . 'PHPExcel/Classes/PHPExcel.php';

                $excel = new PHPExcel();

                $excel->setActiveSheetIndex(0);
                $active_sheet = $excel->getActiveSheet();

                $active_sheet->setTitle("Выдачи " . $from . "-" . $to);

                $excel->getDefaultStyle()->getFont()->setName('Calibri')->setSize(12);
                $excel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

                $active_sheet->getColumnDimension('A')->setWidth(15);
                $active_sheet->getColumnDimension('B')->setWidth(15);
                $active_sheet->getColumnDimension('C')->setWidth(20);
                $active_sheet->getColumnDimension('D')->setWidth(20);                $active_sheet->getColumnDimension('H')->setWidth(30);
                $active_sheet->getColumnDimension('E')->setWidth(20);                $active_sheet->getColumnDimension('H')->setWidth(30);
                $active_sheet->getColumnDimension('F')->setWidth(20);                $active_sheet->getColumnDimension('H')->setWidth(30);
                $active_sheet->getColumnDimension('G')->setWidth(20);                $active_sheet->getColumnDimension('H')->setWidth(30);
                $active_sheet->getColumnDimension('H')->setWidth(20);                $active_sheet->getColumnDimension('H')->setWidth(30);

                $active_sheet->setCellValue('A1', 'Договор');
                $active_sheet->setCellValue('B1', 'Дата');
                $active_sheet->setCellValue('C1', 'Клиент');
                $active_sheet->setCellValue('D1', 'Остаток ОД');
                $active_sheet->setCellValue('E1', 'Остаток процентов');
                $active_sheet->setCellValue('F1', 'Остаток ответственности');
                $active_sheet->setCellValue('G1', 'Сумма последний оплаты');
                $active_sheet->setCellValue('H1', 'Дата последней оплаты');

                $i = 2;
                foreach ($contracts as $contract) {
                    

                    $active_sheet->setCellValue('A' . $i, $contract->number);
                    $active_sheet->setCellValue('B' . $i, date('d.m.Y', strtotime($contract->date)));
                    $active_sheet->setCellValue('C' . $i, $contract->lastname . ' ' . $contract->firstname . ' ' . $contract->patronymic . ' ' . $contract->birth);
                    $active_sheet->setCellValue('D' . $i, $contract->loan_body_summ);
                    $active_sheet->setCellValue('E' . $i, $contract->loan_percents_summ);
                    $active_sheet->setCellValue('F' . $i, $contract->loan_body_summ + $contract->loan_percents_summ);
                    $active_sheet->setCellValue('G' . $i, $contract->operation_amount);
                    if($contract->operation_date)
                        $active_sheet->setCellValue('H' . $i, date('d.m.Y', strtotime($contract->operation_date)));
                    else
                        $active_sheet->setCellValue('H' . $i, '');

                    $i++;
                }

                $objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');

                $objWriter->save($this->config->root_dir . $filename);

                header('Location:' . $this->config->root_url . '/' . $filename);
                exit;
            }

            $this->design->assign('contracts', $contracts);
        }

        return $this->design->fetch('statistics/reconciliation.tpl');
    }

    private function action_collectors_expired()
    {
        if ($date = $this->request->get('date')) {
            
            $this->design->assign('date', $date);

            $this->db->query("
                SELECT c.*,
                u.lastname,
                u.firstname,
                u.patronymic,
                u.id as user_id,
                u.phone_mobile
                FROM __contracts AS c
                LEFT JOIN __users AS u
                ON c.user_id = u.id
                WHERE c.status IN (2, 4)
                AND DATE(c.return_date) < ?

                ORDER BY c.return_date DESC
            ", $date);

            $risk_op = ['complaint' => 'Жалоба', 'bankrupt' => 'Банкрот', 'refusal' => 'Отказ от взаимодействия',
            'refusal_thrd' => 'Отказ от взаимодействия с 3 лицами', 'death' => 'Смерть', 'anticollectors' => 'Антиколлекторы', 'mls' => 'Находится в МЛС',
            'bankrupt_init' => 'Инициировано банкротство', 'fraud' => 'Мошенничество', 'canicule' => 'о кредитных каникулах', 'request_cb' => 'о запросе ЦБ, ФССП'];

            $this->design->assign('risk_op', $risk_op);

            $contracts = $this->db->results();
            foreach ($contracts as $key => $contract) {
                $order = $this->orders->get_order($contract->order_id);
                $contract->order = $order;

                $date1 = new DateTime(date('Y-m-d', strtotime($contract->return_date)));
                $date2 = new DateTime(date('Y-m-d'));

                $diff = $date2->diff($date1);

                $contracts[$key]->expired_days = $diff->days;

                $user_risk_op = $this->UsersRisksOperations->get_record($contract->user_id);

                $contracts[$key]->risk = $user_risk_op;
            }
            $this->design->assign('contracts', $contracts);
            
            $managers = $this->managers->get_managers();
            $this->design->assign('managers', $managers);
            
            $collection_statuses = $this->contracts->get_collection_statuses();
            $this->design->assign('collection_statuses', $collection_statuses);

            $collector_tags = array();
            foreach ($this->collector_tags->get_tags() as $ct)
                $collector_tags[$ct->id] = $ct;
            $this->design->assign('collector_tags', $collector_tags);
            
            if ($this->request->get('download') == 'excel') {

                $filename = 'files/reports/Просрочка для коллекторов.xls';
                require $this->config->root_dir . 'PHPExcel/Classes/PHPExcel.php';

                $excel = new PHPExcel();

                $excel->setActiveSheetIndex(0);
                $active_sheet = $excel->getActiveSheet();

                $active_sheet->setTitle("Просрочка для коллекторов");

                $excel->getDefaultStyle()->getFont()->setName('Calibri')->setSize(12);
                $excel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

                // $active_sheet->getColumnDimension('A')->setWidth(30);
                // $active_sheet->getColumnDimension('B')->setWidth(15);
                // $active_sheet->getColumnDimension('C')->setWidth(15);

                $active_sheet->setCellValue('A1', '№ контракта');
                $active_sheet->setCellValue('B1', 'ФИО');
                $active_sheet->setCellValue('C1', 'Телефон');
                $active_sheet->setCellValue('D1', 'Cумма долга');
                $active_sheet->setCellValue('E1', 'Дней просрочки');
                $active_sheet->setCellValue('F1', 'Коллектор');
                $active_sheet->setCellValue('G1', 'Тег');
                $active_sheet->setCellValue('H1', 'Риск-статус');

                $i = 2;
                foreach ($contracts as $contract) {
                    
                    $active_sheet->setCellValue('A' . $i, $contract->number);
                    $active_sheet->setCellValue('B' . $i, $contract->lastname . ' ' . $contract->firstname . ' ' . $contract->patronymic . ' ' . $contract->birth);
                    $active_sheet->setCellValue('C' . $i, $contract->phone_mobile);
                    $active_sheet->setCellValue('D' . $i, $contract->loan_body_summ+$contract->loan_percents_summ+$contract->loan_peni_summ);
                    $active_sheet->setCellValue('E' . $i, $contract->expired_days);

                    foreach ($managers as $m){
                        $mn = '';
                        if ($m->id == $contract->collection_manager_id){
                            $mn = $m->name;
                        }
                    }
                    $active_sheet->setCellValue('F' . $i, $mn);

                    if (!$contract->order->contact_status){
                        $tag = 'Нет данных';
                    }
                    else{
                        $tag = $collector_tags[$contract->order->contact_status]->name;
                    }
                    $active_sheet->setCellValue('G' . $i, $tag);

                    $val_all = '';
                    if (!empty($contract->risk)){
                        foreach ($contract->risk as $operation => $value){
                            foreach ($risk_op as $risk => $val){
                                if ($operation == $risk && $value == 1){
                                    $val_all .= $val.', '; 
                                }
                            }
                        }
                    }
                    $active_sheet->setCellValue('H' . $i, $val_all);

                    $i++;
                }

 

                $objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');

                $objWriter->save($this->config->root_dir . $filename);

                header('Location:' . $this->config->root_url . '/' . $filename);
                exit;
            }

        }


        return $this->design->fetch('statistics/collectors_expired.tpl');
    }

    private function action_collectors_expired1()
    {
        if ($daterange = $this->request->get('daterange')) {
            list($from, $to) = explode('-', $daterange);

            $date_from = date('Y-m-d', strtotime($from));
            $date_to = date('Y-m-d', strtotime($to));

            $this->design->assign('date_from', $date_from);
            $this->design->assign('date_to', $date_to);
            $this->design->assign('from', $from);
            $this->design->assign('to', $to);
            
            $date = date('Y-m-d', strtotime($this->request->get('date')));
            if($date < $date_from){
                $date = $date_to;
            }
            $this->design->assign('date', $date);
            $period = $this->request->get('period');
            
            $this->design->assign('period', $period);

        }


        return $this->design->fetch('statistics/collectors_expired1.tpl');
    }


    private function cmp($a, $b)
    {
        if (isset($a["date_payment"]) && isset($b["date_payment"])) {
            return strcmp($a["date_payment"], $b["date_payment"]);
        }
    }
    private function action_recovery_rate()
    {

        $begin_date = '2023-01';
        $end_date = date("Y-m");
        
        $query = $this->db->placehold("
            SELECT
                o.id,
                o.type,
                o.amount,
                o.created,
                o.transaction_id,
                c.id AS contract_id,
                c.inssuance_date,
                u.id AS uder_id,
                t.loan_body_summ
            FROM __operations AS o
            LEFT JOIN __contracts AS c
            ON c.id = o.contract_id
            LEFT JOIN __users AS u
            ON u.id = o.user_id
            LEFT JOIN __transactions AS t
            ON t.id = o.transaction_id
            WHERE (o.type = 'P2P' OR o.type = 'PAY' OR o.type = 'RECURRENT')
            AND c.inssuance_date >= ?
            ORDER BY o.created
        ", $begin_date);
        $this->db->query($query);

        $results = $this->db->results();
        
        $operations_by_date = [];
        foreach ($results as $op) {
            if (!array_key_exists(date('Y-m', strtotime($op->inssuance_date)), $operations_by_date)) {
                $operations_by_date[date('Y-m', strtotime($op->inssuance_date))][date('Y-m', strtotime('-1 MONTH', strtotime($begin_date)))]['date_payment'] = date("Y-m", strtotime('-1 MONTH', strtotime($begin_date)));
                $operations_by_date[date('Y-m', strtotime($op->inssuance_date))][date('Y-m', strtotime('-1 MONTH', strtotime($begin_date)))]['date_contract'] = date('Y-m', strtotime($op->inssuance_date));
                $current_date = date('Y-m', strtotime($op->inssuance_date));
            }

            if (!array_key_exists(date('Y-m', strtotime($op->created)), $operations_by_date[date('Y-m', strtotime($op->inssuance_date))])) {
                $operations_by_date[date('Y-m', strtotime($op->inssuance_date))][date('Y-m', strtotime($op->created))]['date_payment'] = date('Y-m', strtotime($op->created));
                $operations_by_date[date('Y-m', strtotime($op->inssuance_date))][date('Y-m', strtotime($op->created))]['P2P'] = 0;
                $operations_by_date[date('Y-m', strtotime($op->inssuance_date))][date('Y-m', strtotime($op->created))]['PAY'] = 0;
            }

            if ($op->type == 'P2P') {
                $operations_by_date[date('Y-m', strtotime($op->inssuance_date))][date('Y-m', strtotime($op->created))]['P2P'] += $op->amount;
            }
            else if ($op->type == 'PAY' || $op->type == 'RECURRENT') {
                $transaction = $this->transactions->get_transaction($op->transaction_id);
                if (isset($transaction)) {
                    $operations_by_date[date('Y-m', strtotime($op->inssuance_date))][date('Y-m', strtotime($op->created))]['PAY'] += $transaction->loan_body_summ + $transaction->loan_percents_summ + $transaction->loan_peni_summ;
                }
            }

        }
        $P2P = 0;
        $PAY = 0;
        foreach ($operations_by_date as $key => $operation_by_date) {
            $current_date = $begin_date;
            // if ($current_date != $operation_by_date[date('Y-m', strtotime('-1 MONTH', strtotime($begin_date)))]['date_contract']) {
            //     while ($current_date != $operation_by_date[date('Y-m', strtotime('-1 MONTH', strtotime($begin_date)))]['date_contract']) {
            //         $operations_by_date[$key][$current_date]['date_payment'] = $current_date;
            //         $operations_by_date[$key][$current_date]['P2P'] = 0;
            //         $operations_by_date[$key][$current_date]['PAY'] = 0;
            //         $current_date = date("Y-m", strtotime('+1 MONTH', strtotime($current_date)));
            //     }
            // }


            if (date('Y-m', strtotime($key)) < $begin_date) {
                continue;
            }

            foreach ($operation_by_date as $key_pay => $payment_by_date) {
                
                if ($key_pay == "2022-12") {
                    continue;
                }

                if ($current_date != $payment_by_date['date_payment']) {
                    while ($current_date != $payment_by_date['date_payment']) {
                        $operations_by_date[$key][$current_date]['date_payment'] = $current_date;
                        $operations_by_date[$key][$current_date]['P2P'] = 0;
                        $operations_by_date[$key][$current_date]['PAY'] = 0;
                        $current_date = date("Y-m", strtotime('+1 MONTH', strtotime($current_date)));
                    }
                }


                if (isset($payment_by_date['P2P']) || isset($payment_by_date['PAY'])) {
                    if ($payment_by_date['P2P'] != 0 || $payment_by_date['PAY'] != 0) {
                        $P2P += $payment_by_date['P2P'];
                        $PAY += $payment_by_date['PAY'];
                    }
                }
                $current_date = date("Y-m", strtotime('+1 MONTH', strtotime($current_date)));
            }

            if ($current_date <= $end_date) {
                while ($current_date != $end_date) {
                    $operations_by_date[$key][$current_date]['date_payment'] = $current_date;
                    $operations_by_date[$key][$current_date]['P2P'] = 0;
                    $operations_by_date[$key][$current_date]['PAY'] = 0;
                    $current_date = date("Y-m", strtotime('+1 MONTH', strtotime($current_date)));
                }
                $operations_by_date[$key][$current_date]['date_payment'] = $current_date;
                $operations_by_date[$key][$current_date]['P2P'] = 0;
                $operations_by_date[$key][$current_date]['PAY'] = 0;
            }

            usort($operations_by_date[$key], array($this, 'cmp'));
        }
        ksort($operations_by_date);

        $this->design->assign('operations_by_date', $operations_by_date);

        
        $this->design->assign('begin_date', $begin_date);
        $this->design->assign('end_date', $end_date);

        return $this->design->fetch('statistics/recovery_rate.tpl');
    }

    private function action_kd_click()
    {
        if ($daterange = $this->request->get('daterange')) {
            list($from, $to) = explode('-', $daterange);

            $date_from = date('Y-m-d', strtotime($from));
            $date_to = date('Y-m-d', strtotime($to));

            $this->design->assign('date_from', $date_from);
            $this->design->assign('date_to', $date_to);
            $this->design->assign('from', $from);
            $this->design->assign('to', $to);

            $query = $this->db->placehold("
                SELECT
                    kd.id AS contract_id,
                    kd.click_date,
                    u.id AS user_id,
                    u.lastname,
                    u.firstname,
                    u.patronymic,
                    u.phone_mobile
                FROM __kd_clicks AS kd
                LEFT JOIN __users AS u
                ON u.id = kd.user_id
                WHERE DATE(kd.click_date) >= ?
                AND DATE(kd.click_date) <= ?
                ORDER BY click_date
            ", $date_from, $date_to);
            $this->db->query($query);
            file_put_contents($this->config->root_dir.'files/sas.txt',$query);

            $clicks = $this->db->results();
            $this->design->assign('clicks', $clicks);

            if ($this->request->get('download') == 'excel') {
                $managers = array();
                foreach ($this->managers->get_managers() as $m)
                    $managers[$m->id] = $m;

                $filename = 'files/reports/Клики по КД.xls';
                require $this->config->root_dir . 'PHPExcel/Classes/PHPExcel.php';

                $excel = new PHPExcel();

                $excel->setActiveSheetIndex(0);
                $active_sheet = $excel->getActiveSheet();

                $active_sheet->setTitle("Клики по КД ");

                $excel->getDefaultStyle()->getFont()->setName('Calibri')->setSize(12);
                $excel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

                $active_sheet->getColumnDimension('A')->setWidth(30);
                $active_sheet->getColumnDimension('B')->setWidth(20);
                $active_sheet->getColumnDimension('C')->setWidth(15);

                $active_sheet->setCellValue('A1', 'ФИО');
                $active_sheet->setCellValue('B1', 'Телефон');
                $active_sheet->setCellValue('C1', 'Дата клика');

                $i = 2;
                foreach ($clicks as $click) {

                    $active_sheet->setCellValue('A' . $i, $click->lastname.' '.$click->firstname.' '.$click->patronymic);
                    $active_sheet->setCellValue('B' . $i, $click->phone_mobile);
                    $active_sheet->setCellValue('C' . $i, date('d.m.Y', strtotime($click->click_date)));

                    $i++;
                }

                $objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');

                $objWriter->save($this->config->root_dir . $filename);

                header('Location:' . $this->config->root_url . '/' . $filename);
                exit;
            }

            $this->design->assign('contracts', $contracts);
        }

        return $this->design->fetch('statistics/kd_click.tpl');
    }

    private function action_sspv()
    {
        if ($daterange = $this->request->get('daterange')) {
            list($from, $to) = explode('-', $daterange);

            $date_from = date('Y-m-d', strtotime($from));
            $date_to = date('Y-m-d', strtotime($to));

            $this->design->assign('date_from', $date_from);
            $this->design->assign('date_to', $date_to);
            $this->design->assign('from', $from);
            $this->design->assign('to', $to);

            $query = $this->db->placehold("
            SELECT *
                FROM s_contracts
                WHERE number in (
                    '1107-4049',
                    '0728-5185', '0927-5901', '0725-5142', '1004-5966', '1004-5965', '0926-5886', '0926-5887', '1003-5953', '1003-5950', '0925-5870', '0524-4733', '1002-5947', '1001-5941', '0825-5550', '1001-5940', '0915-5776', '0923-5855', '0425-4323', '0930-5927', '0930-5929', '0704-4956', '0913-5758', '0822-5514', '0929-5918', '0506-4471', '0822-5509', '0928-5904', '0926-5890', '0926-5884', '0926-5885', '0919-5807', '0925-5877', '0925-5876', '0925-5875', '0925-5871', '0909-5723', '0925-5867', '0805-5296', '0825-5556', '0924-5862', '0819-5473', '0917-5791', '0923-5852', '0923-5844', '0415-4109', '0612-4840', '0915-5775', '0914-5766', '0921-5831', '0610-4811', '0904-5696', '0819-5476', '0814-5411', '0821-5499', '0919-5809', '0816-5443', '0903-5681', '0912-5748', '0919-5810', '0610-4813', '0911-5740', '0918-5803', '0526-4741', '0918-5802', '0918-5800', '0813-5404', '0901-5648', '0910-5732', '0917-5795', '0802-5249', '0810-5364', '0429-4375', '0915-5778', '0830-5614', '0815-5428', '0614-4854', '0716-5044', '0526-4746', '0827-5583', '0912-5749', '0905-5702', '0905-5697', '0911-5736', '0826-5557', '0903-5673', '0811-5371', '0801-5236', '0426-4332', '0909-5722', '0824-5533', '0806-5305', '0831-5633', '0822-5512', '0822-5510', '0821-5502', '0802-5255', '0728-5179', '0830-5617', '0821-5490', '0721-5102', '0905-5698', '0905-5706', '0905-5699', '0904-5695', '0731-5225', '0903-5678', '0827-5582', '0903-5680', '0903-5679', '0903-5675', '0903-5671', '0902-5668', '0817-5455', '0403-3945', '0803-5272', '0902-5663', '0728-5178', '0902-5662', '0826-5562', '0630-4938', '0825-5552', '0901-5652', '0825-5543', '0825-5546', '0726-5155', '0824-5541', '0718-5068', '0721-5095', '0824-5538', '0823-5525', '0823-5524', '0624-4917', '0830-5618', '0829-5610', '0822-5507', '0829-5603', '0829-5609', '0829-5604', '0822-5505', '0726-5154', '0812-5392', '0828-5593', '0828-5588', '0728-5169', '0606-4781', '0724-5131', '0827-5574', '0827-5578', '0827-5575', '0811-5367', '0819-5478', '0819-5477', '0826-5570', '0716-5049', '0616-4877', '0701-4941', '0825-5554', '0825-5553', '0825-5549', '0719-5076', '0825-5548', '0818-5462', '0720-5092', '0824-5540', '0824-5536', '0824-5534', '0824-5532', '0824-5527', '0824-5530', '0817-5453', '0817-5448', '0817-5446', '0823-5519', '0823-5515', '0815-5429', '0820-5481', '0815-5427', '0815-5422', '0814-5420', '0819-5471', '0707-4972', '0814-5409', '0813-5408', '0618-4895', '0813-5397', '0706-4965', '0820-5483', '0820-5482', '0818-5466', '0818-5457', '0811-5368', '0818-5459', '0817-5456', '0810-5360', '0801-5235', '0810-5357', '0817-5447', '0713-5017', '0807-5318', '0815-5425', '0814-5419', '0807-5316', '0807-5312', '0729-5186', '0806-5308', '0703-4948', '0813-5401', '0813-5406', '0728-5170', '0619-4898', '0812-5395', '0805-5297', '0812-5388', '0727-5163', '0805-5295', '0812-5386', '0805-5286', '0712-5008', '0811-5379', '0811-5373', '0810-5366', '0725-5141', '0809-5337', '0802-5259', '0809-5336', '0802-5253', '0809-5338', '0801-5246', '0808-5328', '0801-5244', '0807-5321', '0805-5291'
                    )
        
            ");

            $this->db->query($query);
            $contracts = $this->db->results();

            
            foreach ($contracts as $key => $contract) {
                $query = $this->db->placehold("
                SELECT *
                FROM s_operations
                WHERE contract_id = ? AND type='PAY' 
                AND created>? AND created<?
    
                ",$contract->id, $date_from, $date_to);
                $this->db->query($query);
                $pays = $this->db->results();
                if (count($pays) > 0) {
                    foreach ($pays as $pay) {
                        $contracts[$key]->date_pay = $pay->created;
                        $contracts[$key]->amount = $pay->amount;
                        // echo date('d.m.Y', strtotime($pay->created)).'; '.$pay->amount.'; ';
                    }
                }
                else{
                    $contracts[$key]->date_pay = '';
                    $contracts[$key]->amount = 0.00;
                    // echo '; 0.00; ';
                }
    
                $all = $contract->loan_body_summ + $contract->loan_percents_summ + $contract->loan_peni_summ;
                $contracts[$key]->all = $all;
                // echo $all.'<br>';
                
            }
            

            if ($this->request->get('download') == 'excel') {
                $managers = array();
                foreach ($this->managers->get_managers() as $m)
                    $managers[$m->id] = $m;

                $filename = 'files/reports/Реестр ССПВ.xls';
                require $this->config->root_dir . 'PHPExcel/Classes/PHPExcel.php';

                $excel = new PHPExcel();

                $excel->setActiveSheetIndex(0);
                $active_sheet = $excel->getActiveSheet();

                $active_sheet->setTitle("ССПВ " . $from . "-" . $to);

                $excel->getDefaultStyle()->getFont()->setName('Calibri')->setSize(12);
                $excel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

                $active_sheet->getColumnDimension('A')->setWidth(15);
                $active_sheet->getColumnDimension('B')->setWidth(15);
                $active_sheet->getColumnDimension('C')->setWidth(15);
                $active_sheet->getColumnDimension('D')->setWidth(20);                $active_sheet->getColumnDimension('H')->setWidth(30);

                $active_sheet->setCellValue('A1', '№ договора');
                $active_sheet->setCellValue('B1', 'Дата платежа');
                $active_sheet->setCellValue('C1', 'Сумма платежа');
                $active_sheet->setCellValue('D1', 'Остаток задолженности');

                $i = 2;
                foreach ($contracts as $contract) {
                    

                    $active_sheet->setCellValue('A' . $i, $contract->number);
                    $active_sheet->setCellValue('B' . $i, $contract->date_pay!='' ? date('d.m.Y', strtotime($contract->date_pay)) : '');
                    $active_sheet->setCellValue('C' . $i, $contract->amount);
                    $active_sheet->setCellValue('D' . $i, $contract->all);
                    
                    $i++;
                }

                $objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');

                $objWriter->save($this->config->root_dir . $filename);

                header('Location:' . $this->config->root_url . '/' . $filename);
                exit;
            }

            $this->design->assign('contracts', $contracts);
        }

        return $this->design->fetch('statistics/sspv.tpl');
    }

}
