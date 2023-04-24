<?php
error_reporting(-1);
ini_set('display_errors', 'On');

chdir('..');
require_once 'autoload.php';

class MangoCallback extends Core
{
    public function __construct()
    {
    	parent::__construct();
        
        
        if ($this->request->method('get')){
            
            $query = $this->db->placehold("
                SELECT 
                u.id,
                u.first_loan_amount,
                u.first_loan_period,
                u.email,
                u.lastname,
                u.firstname,
                u.patronymic,
                u.gender,
                u.birth,
                u.birth_place,
                u.phone_mobile,
                u.passport_serial,
                u.passport_date,
                u.passport_issued,
                u.regaddress_id,
                u.faktaddress_id,
                u.profession,
                u.workplace,
                u.workphone,
                u.income,
                u.expenses,
                u.average_pay,
                u.amount_pay,
                cp.name as contact_person_name,
                cp.relation as contact_person_relation,
                cp.phone as contact_person_phone,
                w.name as work_name,
                w.director_phone as work_director_phone
                FROM s_users u
                LEFT JOIN s_contactpersons AS cp
                ON u.id = cp.user_id
                LEFT JOIN s_works AS w
                ON u.id = w.user_id
                WHERE u.id = 27900
            ");
            $this->db->query($query);
            $user = $this->db->result();

            $regaddress = $this->addresses->get_address($user->regaddress_id)->adressfull;
            $user->regaddress = $regaddress;
            $faktaddress = $this->addresses->get_address($user->faktaddress_id)->adressfull;
            $user->faktaddress = $faktaddress;

            $files  = $this->users->get_files(array('user_id'=>$user->id));
            $user->files = [];
            $user->files['dir'] = 'http://rus-client/files/users/';
            foreach ($files as $file) {
                $user->files[$file->type] = $file->name;
            }

            $user->token = '3b42527be34ee985d8747ad190f0515e';

            $json = json_encode($user, JSON_UNESCAPED_UNICODE);


            // echo $json;
            // die;


            // !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
            
            $this->json = $this->request->get('json');
            $this->run();
        }
        else{
            exit('ERROR METHOD');
        }
    }
    
    private function run()
    {
        $json = json_decode($this->json);
        $json_array = (array)$json;
        // var_dump($json_array);

        // Проверка партнера
        $query = $this->db->placehold("
            SELECT * 
            FROM __partners
            WHERE token = ?
        ", $json_array['token']);
        $this->db->query($query);

        $tokens = $this->db->result();
        if(!isset($tokens) || empty($tokens)){
            var_dump('Неверный токен!!!');

            // ДАЛЬШЕ ПРОВЕРКА С ВОЗВРАТОМ json

            die;
        }

        //  // Проверка по номеру телефона
        // $query = $this->db->placehold("
        //     SELECT *
        //     FROM __users
        //     WHERE phone_mobile = ?
        // ", $json_array['phone_mobile']);
        // $this->db->query($query);
        // $is_phone = $this->db->result();

        // if(isset($is_phone) && !empty($is_phone)){
        //     var_dump('Есть пользователь с таким телефоном!!!');

        //     // ДАЛЬШЕ ПРОВЕРКА С ВОЗВРАТОМ json
        //     die;
        // }

        // // Проверка по паспорту
        // $query = $this->db->placehold("
        //     SELECT *
        //     FROM __users
        //     WHERE passport_serial = ?
        // ", $json_array['passport_serial']);
        // $this->db->query($query);
        // $is_passport = $this->db->result();

        // if(isset($is_passport) && !empty($is_passport)){
        //     var_dump('Есть пользователь с таким паспортом!!!');

        //     // ДАЛЬШЕ ПРОВЕРКА С ВОЗВРАТОМ json
        //     die;
        // }

       






        // // Добавляем пользователя
        $user = array_slice($json_array, 1, 22);
        
        $user['enabled'] = 1;
        $user['stage_personal'] = 1;
        $user['stage_passport'] = 1;
        $user['stage_address'] = 1;
        $user['stage_work'] = 1;
        $user['stage_files'] = 1;
        $user['stage_card'] = 0;
        $user['lead_partner_id'] = $tokens->ID;
        
        var_dump($user);
        // die;

        $user_id = $this->users->add_user($user);
        var_dump($user_id);

        // $user_id = 28869;


        // Добавляем контактное лицо
        $contact_person_o = array_slice($json_array, 23, 3);

        $contact_person['user_id'] = $user_id;
        foreach ($contact_person_o as $key_o => $value) {
            $key = substr($key_o, 15);
            $contact_person[$key] = $value;
        }

        $this->Contactpersons->add_contactperson($contact_person);


        // Добавляем данные по руководителю
        $work_o = array_slice($json_array, 26, 2);

        $work['user_id'] = $user_id;
        foreach ($work_o as $key_o => $value) {
            $key = substr($key_o, 5);
            $work[$key] = $value;
        }

        worksORM::create($work);


        // Добавляем адреса
        $regaddress_string = $json_array['regaddress'];
        $faktaddress_string = $json_array['faktaddress'];
        
        if (!empty($regaddress_string)) {
            $Regadress = json_decode($this->dadata->get_all($regaddress_string))->suggestions[0];
            $regaddress = [];
            $regaddress['adressfull'] = $regaddress_string;
            $regaddress['zip'] = $Regadress->data->postal_code ?? '';
            $regaddress['region'] = $Regadress->data->region ?? '';
            $regaddress['region_type'] = $Regadress->data->region_type ?? '';
            $regaddress['city'] = $Regadress->data->city ?? '';
            $regaddress['city_type'] = $Regadress->data->city_type ?? '';
            $regaddress['district'] = $Regadress->data->city_district ?? '';
            $regaddress['district_type'] = $Regadress->data->city_district_type ?? '';
            $regaddress['locality'] = $Regadress->data->settlement ?? '';
            $regaddress['locality_type'] = $Regadress->data->settlement_type ?? '';
            $regaddress['street'] = $Regadress->data->street ?? '';
            $regaddress['street_type'] = $Regadress->data->street_type ?? '';
            $regaddress['house'] = $Regadress->data->house ?? '';
            $regaddress['building'] = $Regadress->data->block ?? '';
            $regaddress['room'] = $Regadress->data->flat ?? '';
            $regaddress['okato'] = $Regadress->data->okato ?? '';
            $regaddress['oktmo'] = $Regadress->data->oktmo ?? '';
        }

        if (!empty($faktaddress_string)) {
            $Fakt_adress = json_decode($this->dadata->get_all($faktaddress_string))->suggestions[0];
            $faktaddress = [];
            $faktaddress['adressfull'] = $faktaddress_string;
            $faktaddress['zip'] = $Fakt_adress->data->postal_code ?? '';
            $faktaddress['region'] = $Fakt_adress->data->region ?? '';
            $faktaddress['region_type'] = $Fakt_adress->data->region_type ?? '';
            $faktaddress['city'] = $Fakt_adress->data->city ?? '';
            $faktaddress['city_type'] = $Fakt_adress->data->city_type ?? '';
            $faktaddress['district'] = $Fakt_adress->data->city_district ?? '';
            $faktaddress['district_type'] = $Fakt_adress->data->city_district_type ?? '';
            $faktaddress['locality'] = $Fakt_adress->data->settlement ?? '';
            $faktaddress['locality_type'] = $Fakt_adress->data->settlement_type ?? '';
            $faktaddress['street'] = $Fakt_adress->data->street ?? '';
            $faktaddress['street_type'] = $Fakt_adress->data->street_type ?? '';
            $faktaddress['house'] = $Fakt_adress->data->house ?? '';
            $faktaddress['building'] = $Fakt_adress->data->block ?? '';
            $faktaddress['room'] = $Fakt_adress->data->flat ?? '';
            $faktaddress['okato'] = $Fakt_adress->data->okato ?? '';
            $faktaddress['oktmo'] = $Fakt_adress->data->oktmo ?? '';
        }

        $regaddress_id = $this->Addresses->add_address($regaddress);
        $faktaddress_id = $this->Addresses->add_address($faktaddress);
        $this->users->update_user($user_id, array('regaddress_id' => $regaddress_id, 'faktaddress_id' => $faktaddress_id));


        // Добавляем файлы
        $files = (array)$json_array['files'];
        foreach ($files as $key => $value) {

            if($key == 'dir'){
                continue;
            }

            $file = $json_array['files']->dir. '/' .$json_array['files']->{$key};
            $path_info = pathinfo($file);
            $newfile = 'c:\OSPanel\\' . $path_info['basename'];
            
            if (!copy($file, $newfile)) {
                // ответ json
                echo "не удалось скопировать $file...\n";
            }
            else{
                $update = array(
                    'user_id' => $user_id,
                    'name' => $value,
                    'type' => $key,
                    'status' => 0
                );
                $this->users->add_file($update);
            }
        }
        

        die;


        

        // $this->Contactpersons->add_contactperson($contact_person);


        // !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
        // ПРОВЕРЯТЬ НА ПОВТОРНОГО ПОЛЬЗОВАТЕЛЯ ПО НОМЕРУ ТЕЛЕФОНА???
        // !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
        // !!!1

        // @@@s_users
        // first_loan_amount
        // first_loan_period
        // service_sms = 1
        // service_insurance = 1
        // service_reason = 1
        // enabled = 1
        // created = текущая дата
        // -----------------------------------

        // !!!2 - Шаг ... 04 rzsdevgitg_rusz Введены данные заемщика.sql

        // @@@s_contactpersons
        // все поля

        // @@@s_users
        // email
        // lastname
        // firstname
        // patronymic
        // gender
        // birth
        // birth_place
        // phone_mobile
        // -----------------------------------

        // !!!3 - Шаг ... 05 rzsdevgitg_rusz Введен паспорт заемщика (passport).sql

        // @@@s_users
        // passport_serial
        // passport_date
        // passport_issued
        // inn = 0
        // -----------------------------------

        // !!!4 - Шаг address 06 rzsdevgitg_rusz Введен адрес заемщика (address).sql
        // МОЖЕТ ТОЛЬКО ПОЛНЫЕ АДРЕСА А РАЗБИВКА - программно

        // @@@s_addresses  ---  2 адреса (прописка и фактический)
        // zip
        // okato
        // oktmo
        // adressfull
        // region
        // region_type
        // district
        // district_type
        // city
        // city_type
        // locality
        // locality_type
        // street
        // street_type
        // house
        // building
        // room

        // @@@s_users
        // regaddress_id
        // faktaddress_id
        // -----------------------------------

        // !!!5 - Шаг work 07 rzsdevgitg_rusz Введены данные о работе заемщика (work).sql

        // @@@s_works - данные руководителя
        // name
        // director_phone

        // @@@s_users
        // profession
        // workplace
        // workphone
        // income
        // expenses
        // average_pay
        // amount_pay
        // -----------------------------------

        // !!!6 - Шаг files 08 rzsdevgitg_rusz Введены фотографии заемщика (files).sql

        // @@@s_files
        // все поля - там есть и юзер id
        // -----------------------------------

        // !!!7 - ПРИВЯЗЫВАТЬ КАРТУ ПРИ ПЕРВОМ ВХОДЕ КЛИЕНТА 
        // --(Вводит СМС и на страницу карты
        // Добавляется карта и документы SOGLASIE_OPD и ANKETA_REP, атакже заявка
        // @@@s_orders --- 09 rzsdevgitg_rusz !!!(был переход на страницу б2п) Введена карта заемщика.sql
        // accept_sms
        // card_id и т.д
        // -----------------------
        // , затем вводит пароль)
        // -----------------------------------

        // ПОСЛЕ ЭТОГО ИДЕТ РАБОТА МЕНЕДЖЕРА



        // !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

        // $user = array(
        //     'first_loan_amount' => $amount,
        //     'first_loan_period' => $period,
        //     'phone_mobile' => $phone,
        //     'sms' => $code,
        //     'service_reason' => $service_reason,
        //     'service_insurance' => $service_insurance,
        //     'service_sms' => $service_sms,
        //     'reg_ip' => $_SERVER['REMOTE_ADDR'],
        //     'last_ip' => $_SERVER['REMOTE_ADDR'],
        //     'enabled' => 1,
        //     'created' => date('Y-m-d H:i:s'),
        // );

        // $user_id = $this->users->add_user($user);


        // header('Content-type: json/application');
        // $sss = $this->users->get_user(27861);
        // $sss = 'sss';
        // echo $sss;
        // var_dump($_SERVER['REQUEST_METHOD']);
        // if($_SERVER['REQUEST_METHOD'] = 'POST'){
            // if ($this->request->method('post'))
            
            // file_put_contents('C:\OSPanel\123.txt',$amount);
            
            
        // }
        // else{
            // var_dump($_SERVER['REQUEST_METHOD']);
            
            // var_dump($this->json);
            // var_dump($_POST);
        // }
    }
}

new MangoCallback();