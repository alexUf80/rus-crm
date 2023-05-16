<?php

class Leadsend extends Core
{
    private $log_dir  = 'logs/';
    // $this->Leadsend->guruleads_send(34058);

    public function guruleads_send($order_id) {
        $order = $this->orders->get_order($order_id);

        $birthday = date("Y-m-d", strtotime($order->birth));

        if ($order->passport_serial) {
            $passport = str_replace(array('-', ' '), '', $order->passport_serial);
            $passport_serial = substr($passport, 0, 4);
            $passport_number = substr($passport, 4, 6);
        } else {
            $passport = '';
            $passport_serial = '';
            $passport_number = '';
        }

        if ($order->passport_date) {
            $issued_date = date('Y-m-d', strtotime($order->passport_date));
        } else {
            $issued_date = '';
        }

        $regaddress = $this->Addresses->get_address($order->regaddress_id);
        $faktaddress = $this->Addresses->get_address($order->faktaddress_id);

        if($regaddress->city)
            $regaddress_city = $regaddress->city . " " . $regaddress->city_type;
        else
            $regaddress_city = $regaddress->locality . " " . $regaddress->locality_type;
        
        if($faktaddress->city)
            $faktaddress_city = $faktaddress->city . " " . $faktaddress->city_type;
        else
            $faktaddress_city = $faktaddress->locality . " " . $faktaddress->locality_type;

        $data = [
            'credit_amount' => $order->amount,
            'credit_duration' => $order->period,
            'last_name' => $order->lastname,
            'first_name' => $order->firstname,
            'middle_name' => $order->patronymic,
            'gender' => $order->gender,
            'phone' => $order->phone_mobile,
            'email' => $order->email,
            'birthday' => $birthday,
            'birthplace' => $order->birth_place,

            'passport_series' => $passport_serial,
            'passport_number' => $passport_number,
            'passport_issued_by' => $order->passport_issued,
            'passport_issued_date' => $issued_date,
            'passport_unit_code' => $order->subdivision_code,

            'registration_region_name' =>  $regaddress->region . " " . $regaddress->region_type,
            'registration_city_name' =>  $regaddress_city,
            'registration_street_name' =>  $regaddress->street . " " . $regaddress->street_type,
            'registration_house' =>  $regaddress->house,
            'registration_building' =>  $regaddress->building,
            'registration_apartment' =>  $regaddress->room,

            'actual_region_name' =>  $faktaddress->region . " " . $faktaddress->region_type,
            'actual_city_name' =>  $faktaddress_city,
            'actual_street_name' =>  $faktaddress->street . " " . $faktaddress->street_type,
            'actual_house' =>  $faktaddress->house,
            'actual_building' =>  $faktaddress->building,
            'actual_apartment' =>  $faktaddress->room,

            'working' => 1,
            'work_organization' => $order->workplace,
            'work_phone' => $order->workphone,
            'work_income' => $order->income,
            'work_address' => $order->workaddress,

        ];

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.guruleads.ru/1.0/leads/multi?access-token=51ccb534be04dc66792b9c8f7d46321f',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 6,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($data)
        ));
        $response = curl_exec($curl);

        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);
        $this->logging_(__METHOD__, 'send_lead', (array)$data, json_decode($response), 'guruleads.txt');
    }

    public function logging_($local_method, $service, $request, $response, $filename){
        $log_filename = $this->log_dir.$filename;

        if (date('d', filemtime($log_filename)) != date('d'))
        {
            $archive_filename = $this->log_dir.'archive/'.date('ymd', filemtime($log_filename)).'.'.$filename;
            rename($log_filename, $archive_filename);
            file_put_contents($log_filename, "\xEF\xBB\xBF");
        }

        if (isset($request['TextJson']))
            $request['TextJson'] = json_decode($request['TextJson']);
        if (isset($request['ArrayContracts']))
            $request['ArrayContracts'] = json_decode($request['ArrayContracts']);
        if (isset($request['ArrayOplata']))
            $request['ArrayOplata'] = json_decode($request['ArrayOplata']);

        $str = PHP_EOL.'==================================================================='.PHP_EOL;
        $str .= date('d.m.Y H:i:s').PHP_EOL;
        $str .= $service.PHP_EOL;
        $str .= var_export($request, true).PHP_EOL;
        $str .= var_export($response, true).PHP_EOL;
        $str .= 'END'.PHP_EOL;

        file_put_contents($this->log_dir.$filename, $str, FILE_APPEND);
    }
}