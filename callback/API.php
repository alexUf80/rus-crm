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

        header('Content-type: json/application');

        $error = [];

        $json = json_decode($this->json);
        $json_array = (array)$json;

        // Проверка партнера
        $query = $this->db->placehold("
            SELECT * 
            FROM __partners
            WHERE token = ?
        ", $json_array['token']);
        $this->db->query($query);

        $tokens = $this->db->result();
        if(!isset($tokens) || empty($tokens)){
            $error1[] = 'Неверный токен партнера';
        }

        // Проверка по номеру телефона
        $query = $this->db->placehold("
            SELECT *
            FROM __users
            WHERE phone_mobile = ?
        ", $json_array['phone_mobile']);
        $this->db->query($query);
        $is_phone = $this->db->result();

        if(isset($is_phone) && !empty($is_phone)){
            $error[] = 'Есть пользователь с таким телефоном';
        }

        // Проверка по паспорту
        $query = $this->db->placehold("
            SELECT *
            FROM __users
            WHERE passport_serial = ?
        ", $json_array['passport_serial']);
        $this->db->query($query);
        $is_passport = $this->db->result();

        if(isset($is_passport) && !empty($is_passport)){
            $error[] = 'Есть пользователь с таким паспортом';
        }

        if (isset($error1)) {
            $res = [
                'status' => false,
                'error' => $error[0]
            ];
            http_response_code(400);
        }
        elseif (isset($error)) {
            $res = [
                'status' => false,
                'error' => $error[0]
            ];
            http_response_code(409);
        }
        else{
            $res = [
                'status' => true
            ];
            http_response_code(200);
        }

        echo json_encode($res);
        die;

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
        
        $user_id = $this->users->add_user($user);


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

            $file = $json_array['files']->dir.$json_array['files']->{$key};
            $path_info = pathinfo($file);
            $newfile = $this->config->root_dir.'files/users/'.$path_info['basename'];
            $newfile = str_replace("rus-crm", "rus-client", $newfile);

            $ch = curl_init($file);
            $fp = fopen($newfile, "w");
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_exec($ch);
            if(curl_error($ch)) {
                fwrite($fp, curl_error($ch));
            }
            curl_close($ch);
            fclose($fp);

            $update = array(
                'user_id' => $user_id,
                'name' => $value,
                'type' => $key,
                'status' => 0
            );
            var_dump($update);
            $this->users->add_file($update);
            var_dump('$update');


        }
        
        die;

    }
}

new MangoCallback();