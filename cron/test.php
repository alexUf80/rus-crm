<?php

error_reporting(-1);
ini_set('display_errors', 'On');
ini_set('memory_limit', '1024M');

chdir(dirname(__FILE__) . '/../');

require 'autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

class test extends Core
{

    public function __construct()
    {
        parent::__construct();
        $this->competeCardEnroll();
    }

    private function import_addresses()
    {
        $tmp_name = $this->config->root_dir . '/files/clients.xlsx';
        $format = IOFactory::identify($tmp_name);
        $reader = IOFactory::createReader($format);
        $spreadsheet = $reader->load($tmp_name);

        $active_sheet = $spreadsheet->getActiveSheet();

        $first_row = 5;
        $last_row = $active_sheet->getHighestRow();

        for ($row = $first_row; $row <= $last_row; $row++) {

            $outer_id = $active_sheet->getCell('P' . $row)->getValue();

            if (empty($outer_id))
                continue;

            $Regindex = $active_sheet->getCell('I' . $row)->getValue();
            $Regregion = $active_sheet->getCell('R' . $row)->getValue();
            $Regcity = $active_sheet->getCell('S' . $row)->getValue();
            $Regstreet = $active_sheet->getCell('T' . $row)->getValue();
            $Regbuilding = $active_sheet->getCell('U' . $row)->getValue();
            $Regroom = $active_sheet->getCell('X' . $row)->getValue();

            $Faktindex = $active_sheet->getCell('J' . $row)->getValue();
            $Faktregion = $active_sheet->getCell('Z' . $row)->getValue();
            $Faktcity = $active_sheet->getCell('AA' . $row)->getValue();
            $Faktstreet = $active_sheet->getCell('AB' . $row)->getValue();
            $Faktbuilding = $active_sheet->getCell('AC' . $row)->getValue();
            $Faktroom = $active_sheet->getCell('AF' . $row)->getValue();

            $regaddress = "$Regindex $Regregion $Regcity $Regstreet $Regbuilding $Regroom";
            $faktaddress = "$Faktindex $Faktregion $Faktcity $Faktstreet $Faktbuilding $Faktroom";

            $faktaddres = [];
            $faktaddres['adressfull'] = $faktaddress;
            $faktaddres['zip'] = $Faktindex;
            $faktaddres['region'] = $Faktregion;
            $faktaddres['city'] = $Faktcity;
            $faktaddres['street'] = $Faktstreet;
            $faktaddres['building'] = $Faktbuilding;
            $faktaddres['room'] = $Faktroom;

            $regaddres = [];
            $regaddres['adressfull'] = $regaddress;
            $regaddres['zip'] = $Regindex;
            $regaddres['region'] = $Regregion;
            $regaddres['city'] = $Regcity;
            $regaddres['street'] = $Regstreet;
            $regaddres['building'] = $Regbuilding;
            $regaddres['room'] = $Regroom;

            foreach ($regaddres as $key => $address) {
                if ($address == '#NULL!')
                    unset($regaddres[$key]);
            }

            foreach ($faktaddres as $key => $address) {
                if ($address == '#NULL!')
                    unset($faktaddres[$key]);
            }

            $this->db->query("
            SELECT *
            from s_users
            where outer_id = ?
            ", $outer_id);

            $user = $this->db->result();


            $this->Addresses->update_address($user->regaddress_id, $regaddres);
            $this->Addresses->update_address($user->faktaddress_id, $faktaddres);

        }
    }

    private function import_clients()
    {
        $tmp_name = $this->config->root_dir . '/files/clients.xlsx';
        $format = IOFactory::identify($tmp_name);
        $reader = IOFactory::createReader($format);
        $spreadsheet = $reader->load($tmp_name);

        $active_sheet = $spreadsheet->getActiveSheet();

        $first_row = 5;
        $last_row = $active_sheet->getHighestRow();

        for ($row = $first_row; $row <= $last_row; $row++) {

            $created = $active_sheet->getCell('AQ' . $row)->getFormattedValue();
            $birth = $active_sheet->getCell('D' . $row)->getFormattedValue();
            $passport_date = $active_sheet->getCell('AK' . $row)->getFormattedValue();

            $outer_id = $active_sheet->getCell('P' . $row)->getValue();

            if (empty($outer_id))
                continue;

            $Regindex = $active_sheet->getCell('I' . $row)->getValue();
            $Regregion = $active_sheet->getCell('R' . $row)->getValue();
            $Regcity = $active_sheet->getCell('S' . $row)->getValue();
            $Regstreet = $active_sheet->getCell('T' . $row)->getValue();
            $Regbuilding = $active_sheet->getCell('U' . $row)->getValue();
            $Regroom = $active_sheet->getCell('X' . $row)->getValue();

            $Faktindex = $active_sheet->getCell('J' . $row)->getValue();
            $Faktregion = $active_sheet->getCell('Z' . $row)->getValue();
            $Faktcity = $active_sheet->getCell('AA' . $row)->getValue();
            $Faktstreet = $active_sheet->getCell('AB' . $row)->getValue();
            $Faktbuilding = $active_sheet->getCell('AC' . $row)->getValue();
            $Faktroom = $active_sheet->getCell('AF' . $row)->getValue();

            $regaddress = "$Regindex $Regregion $Regcity $Regstreet $Regbuilding $Regroom";
            $faktaddress = "$Faktindex $Faktregion $Faktcity $Faktstreet $Faktbuilding $Faktroom";

            $reg_id = $this->Addresses->add_address(['adressfull' => $regaddress]);
            $fakt_id = $this->Addresses->add_address(['adressfull' => $faktaddress]);

            $fio = explode(' ', $active_sheet->getCell('A' . $row)->getValue());

            $phone = preg_replace("/[^,.0-9]/", '', $active_sheet->getCell('K' . $row)->getValue());
            $phone = str_split($phone);
            $phone[0] = '7';
            $phone = implode('', $phone);

            $user = [
                'firstname' => ucfirst($fio[1]),
                'lastname' => ucfirst($fio[0]),
                'patronymic' => ucfirst($fio[2]),
                'outer_id' => $outer_id,
                'phone_mobile' => $phone,
                'email' => $active_sheet->getCell('AG' . $row)->getValue(),
                'gender' => $active_sheet->getCell('AN' . $row)->getValue() == 'Мужской' ? 'male' : 'female',
                'birth' => date('d.m.Y', strtotime($birth)),
                'birth_place' => $active_sheet->getCell('G' . $row)->getValue(),
                'passport_serial' => $active_sheet->getCell('AH' . $row)->getValue() . '-' . $active_sheet->getCell('AI' . $row)->getValue(),
                'passport_date' => date('d.m.Y', strtotime($passport_date)),
                'passport_issued' => $active_sheet->getCell('AJ' . $row)->getValue(),
                'subdivision_code' => $active_sheet->getCell('H' . $row)->getValue(),
                'snils' => $active_sheet->getCell('AM' . $row)->getValue(),
                'inn' => $active_sheet->getCell('AL' . $row)->getValue(),
                'workplace' => $active_sheet->getCell('L' . $row)->getValue(),
                'workaddress' => $active_sheet->getCell('M' . $row)->getValue(),
                'profession' => $active_sheet->getCell('N' . $row)->getValue(),
                'workphone' => $active_sheet->getCell('O' . $row)->getValue(),
                'income' => $active_sheet->getCell('AO' . $row)->getValue(),
                'expenses' => $active_sheet->getCell('AP' . $row)->getValue(),
                'regaddress_id' => $reg_id,
                'faktaddress_id' => $fakt_id,
                'created' => date('Y-m-d H:i:s', strtotime($created))
            ];

            $this->users->add_user($user);
        }
    }

    private function import_orders()
    {
        $tmp_name = $this->config->root_dir . '/files/orders.xlsx';
        $format = IOFactory::identify($tmp_name);
        $reader = IOFactory::createReader($format);
        $spreadsheet = $reader->load($tmp_name);

        $active_sheet = $spreadsheet->getActiveSheet();

        $first_row = 5;
        $last_row = $active_sheet->getHighestRow();

        for ($row = $first_row; $row <= $last_row; $row++) {

            $id = $active_sheet->getCell('D' . $row)->getValue();

            if (empty($id))
                continue;

            $created = $active_sheet->getCell('A' . $row)->getFormattedValue();
            $created = date('Y-m-d H:i:s', strtotime($created));

            $reject_reason = '';

            if ($active_sheet->getCell('I' . $row)->getValue() === 'Отказ') {
                $reject_reason = $active_sheet->getCell('N' . $row)->getValue();
                $status = 3;
            }

            if (in_array($active_sheet->getCell('I' . $row)->getFormattedValue(), ['Выдан', 'В суде', 'Отправлена претензия', 'Передан на судебную стадию', "Подписан (дистанционно)", "Получен исполнительный лист", "У коллектора"]))
                $status = 5;

            if ($active_sheet->getCell('I' . $row)->getValue() === 'На рассмотрении')
                $status = 1;

            if ($active_sheet->getCell('I' . $row)->getValue() === 'Оплачен' || $active_sheet->getCell('I' . $row)->getValue() === 'Списан')
                $status = 7;

            if ($active_sheet->getCell('I' . $row)->getValue() === 'Отменен')
                $status = 8;

            if ($active_sheet->getCell('I' . $row)->getValue() === 'Одобрен' || $active_sheet->getCell('I' . $row)->getValue() === 'Одобрен предварительно')
                $status = 2;


            if ($active_sheet->getCell('Q' . $row)->getValue() === 'ONLINE-0,5!')
                $loantype_id = 2;
            elseif ($active_sheet->getCell('Q' . $row)->getValue() === 'ВСЕМ-0,9!')
                $loantype_id = 3;
            else
                $loantype_id = 1;

            $loantype = $this->Loantypes->get_loantype($loantype_id);


            $new_order = [
                'outer_id' => $id,
                'date' => $created,
                'loantype_id' => $loantype_id,
                'period' => 30,
                'amount' => $active_sheet->getCell('G' . $row)->getValue(),
                'accept_date' => $created,
                'confirm_date' => $created,
                'status' => $status,
                'percent' => $loantype->percent,
                'reject_reason' => $reject_reason
            ];

            $order_id = $this->orders->add_order($new_order);

            $this->db->query("
                SELECT *
                FROM s_users
                where outer_id = ?
                ", $active_sheet->getCell('O' . $row)->getValue());

            $user = $this->db->result();

            if (!empty($user))
                $this->orders->update_order($order_id, ['user_id' => $user->id]);

        }
        exit;
    }

    private function import_contracts()
    {
        $tmp_name = $this->config->root_dir . '/files/contracts.xlsx';
        $format = IOFactory::identify($tmp_name);
        $reader = IOFactory::createReader($format);
        $spreadsheet = $reader->load($tmp_name);

        $active_sheet = $spreadsheet->getActiveSheet();

        $first_row = 2;
        $last_row = $active_sheet->getHighestRow();

        for ($row = $first_row; $row <= $last_row; $row++) {

            $created = $active_sheet->getCell('B' . $row)->getFormattedValue();
            $created = date('Y-m-d H:i:s', strtotime($created));

            $issuance_date = $active_sheet->getCell('C' . $row)->getFormattedValue();
            $issuance_date = date('Y-m-d H:i:s', strtotime($issuance_date));

            $return_date = $active_sheet->getCell('E' . $row)->getFormattedValue();
            $return_date = date('Y-m-d', strtotime($return_date));

            $new_contract =
                [
                    'number' => $active_sheet->getCell('A' . $row)->getValue(),
                    'type' => 'base',
                    'period' => 30,
                    'uid' => $active_sheet->getCell('K' . $row)->getValue(),
                    'amount' => $active_sheet->getCell('F' . $row)->getValue(),
                    'status' => 0,
                    'create_date' => $created,
                    'inssuance_date' => $issuance_date,
                    'return_date' => $return_date
                ];

            $contract_id = $this->contracts->add_contract($new_contract);

            $this->db->query("
                SELECT *
                FROM s_users
                where outer_id = ?
                ", $active_sheet->getCell('N' . $row)->getValue());

            $user = $this->db->result();

            if (!empty($user))
                $this->contracts->update_contract($contract_id, ['user_id' => $user->id]);

            $this->db->query("
                SELECT *
                FROM s_orders
                where outer_id = ?
                ", $active_sheet->getCell('M' . $row)->getValue());

            $order = $this->db->result();

            $loantype = $this->Loantypes->get_loantype($order->loantype_id);
            $percent = $loantype->percent;

            $statuses = array(
                1 => 0,
                3 => 8,
                5 => 2,
                7 => 3,
                8 => 8
            );

            $new_contract =
                [
                    'order_id' => $order->id,
                    'base_percent' => $percent,
                    'status' => $statuses[$order->status]
                ];

            $this->contracts->update_contract($contract_id, $new_contract);
            $this->orders->update_order($order->id, ['contract_id' => $contract_id]);
        }
    }

    private function import_operations()
    {
        $tmp_name = $this->config->root_dir . '/files/operations.xlsx';
        $format = IOFactory::identify($tmp_name);
        $reader = IOFactory::createReader($format);
        $spreadsheet = $reader->load($tmp_name);

        $active_sheet = $spreadsheet->getActiveSheet();

        $first_row = 5;
        $last_row = $active_sheet->getHighestRow();

        for ($row = $first_row; $row <= $last_row; $row++) {

            $number = $active_sheet->getCell('B' . $row)->getValue();

            $this->db->query("
            SELECT *
            FROM s_operations
            WHERE `number` = ?
            ", $number);

            $opertion = $this->db->result();

            if (!empty($opertion))
                continue;


            $id = $active_sheet->getCell('B' . $row)->getValue();
            $created = $active_sheet->getCell('H' . $row)->getFormattedValue();
            $created = date('Y-m-d H:i:s', strtotime($created));
            $type = 'P2P';
            $amount = $active_sheet->getCell('K' . $row)->getValue();

            if ($active_sheet->getCell('J' . $row)->getValue() === 'Погашение') {
                $type = 'PAY';
                $amount = $active_sheet->getCell('L' . $row)->getValue();
            }

            $this->db->query("
            SELECT *
            FROM s_contracts
            where `number` = ?
            ", $id);

            $contract = $this->db->result();

            $this->operations->add_operation([
                'contract_id' => $contract->id,
                'user_id' => $contract->user_id,
                'order_id' => $contract->order_id,
                'type' => $type,
                'amount' => $amount,
                'created' => $created
            ]);
        }
    }

    private function import_balance()
    {
        $tmp_name = $this->config->root_dir . '/files/balances.xlsx';
        $format = IOFactory::identify($tmp_name);
        $reader = IOFactory::createReader($format);
        $spreadsheet = $reader->load($tmp_name);

        $active_sheet = $spreadsheet->getActiveSheet();

        $first_row = 2;
        $last_row = $active_sheet->getHighestRow();

        for ($row = $first_row; $row <= $last_row; $row++) {
            $id = $active_sheet->getCell('B' . $row)->getValue();
            $od = $active_sheet->getCell('G' . $row)->getValue();
            $prc = $active_sheet->getCell('I' . $row)->getValue() + $active_sheet->getCell('H' . $row)->getValue();
            $peni = $active_sheet->getCell('K' . $row)->getFormattedValue();

            if ($peni == "#NULL!") {
                $peni = 0;
            }

            $contract =
                [
                    'loan_peni_summ' => (float)$peni
                ];

            $this->db->query("
            UPDATE s_contracts 
            SET ?% 
            WHERE `number` = ?
            ", $contract, $id);
        }
    }

    private function statuses()
    {
        $this->db->query("
        SELECT *
        from s_contracts
        where `status` = 3
        ");

        $contracts = $this->db->results();

        foreach ($contracts as $contract) {
            $this->db->query("
            UPDATE s_orders
            set `status` = 5
            where contract_id = ?
            ", $contract->id);
        }
    }

    private function edit_orders_amount()
    {
        $tmp_name = $this->config->root_dir . '/files/contracts.xlsx';
        $format = IOFactory::identify($tmp_name);
        $reader = IOFactory::createReader($format);
        $spreadsheet = $reader->load($tmp_name);

        $active_sheet = $spreadsheet->getActiveSheet();

        $first_row = 2;
        $last_row = $active_sheet->getHighestRow();

        for ($row = $first_row; $row <= $last_row; $row++) {
            $this->db->query("
                UPDATE s_orders
                SET `amount` = ?
                where outer_id = ?
                ", $active_sheet->getCell('F' . $row)->getValue(), $active_sheet->getCell('M' . $row)->getValue());
        }
    }

    private function import_phones()
    {
        $tmp_name = $this->config->root_dir . '/files/clients.xlsx';
        $format = IOFactory::identify($tmp_name);
        $reader = IOFactory::createReader($format);
        $spreadsheet = $reader->load($tmp_name);

        $active_sheet = $spreadsheet->getActiveSheet();

        $first_row = 5;
        $last_row = $active_sheet->getHighestRow();

        for ($row = $first_row; $row <= $last_row; $row++) {

            $outer_id = $active_sheet->getCell('P' . $row)->getValue();

            if (empty($outer_id))
                continue;

            $phone = preg_replace("/[^,.0-9]/", '', $active_sheet->getCell('K' . $row)->getValue());
            $phone = str_split($phone);
            $phone[0] = '7';
            $phone = implode('', $phone);

            $this->db->query("
            UPDATE s_users
            SET phone_mobile = ?
            where outer_id = ?
            ", $phone, $outer_id);
        }
    }

    private function import_prolongations()
    {
        $tmp_name = $this->config->root_dir . '/files/orders.xlsx';
        $format = IOFactory::identify($tmp_name);
        $reader = IOFactory::createReader($format);
        $spreadsheet = $reader->load($tmp_name);

        $active_sheet = $spreadsheet->getActiveSheet();

        $first_row = 2;
        $last_row = $active_sheet->getHighestRow();

        for ($row = $first_row; $row <= $last_row; $row++) {
            $fio = $active_sheet->getCell('B' . $row)->getValue();
        }
    }

    private function competeCardEnroll()
    {
        $this->db->query("
        SELECT
        ts.id,
        ts.user_id,
        ts.amount,
        ts.register_id
        FROM s_orders os
        JOIN s_transactions ts ON os.user_id = ts.user_id
        WHERE ts.`description` = 'Привязка карты'
        AND reason_code = 1
        AND os.`status` = 3
        and checked = 0
        and created > '2022-11-25 00:00:00'
        order by id desc
        ");

        $transactions = $this->db->results();

        foreach ($transactions as $transaction)
            $this->Best2pay->completeCardEnroll($transaction);
    }


}

new test();