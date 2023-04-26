<?php
error_reporting(-1);
ini_set('display_errors', 'On');

chdir('..');
require_once 'autoload.php';

class ApiLead extends Core
{
    private $log_dir  = 'logs/';

    public function __construct()
    {
    	parent::__construct();
        
        
        if ($this->request->method('post')){
            
            // $query = $this->db->placehold("
            //     SELECT 
            //     u.id,
            //     u.first_loan_amount,
            //     u.first_loan_period,
            //     u.email,
            //     u.lastname,
            //     u.firstname,
            //     u.patronymic,
            //     u.gender,
            //     u.birth,
            //     u.birth_place,
            //     u.phone_mobile,
            //     u.passport_serial,
            //     u.passport_date,
            //     u.passport_issued,
            //     u.regaddress_id,
            //     u.faktaddress_id,
            //     u.profession,
            //     u.workplace,
            //     u.workphone,
            //     u.income,
            //     u.expenses,
            //     u.average_pay,
            //     u.amount_pay,
            //     cp.name as contact_person_name,
            //     cp.relation as contact_person_relation,
            //     cp.phone as contact_person_phone,
            //     w.name as work_name,
            //     w.director_phone as work_director_phone
            //     FROM s_users u
            //     LEFT JOIN s_contactpersons AS cp
            //     ON u.id = cp.user_id
            //     LEFT JOIN s_works AS w
            //     ON u.id = w.user_id
            //     WHERE u.id = 27900
            // ");
            // $this->db->query($query);
            // $user = $this->db->result();

            // $regaddress = $this->addresses->get_address($user->regaddress_id)->adressfull;
            // $user->regaddress = $regaddress;
            // $faktaddress = $this->addresses->get_address($user->faktaddress_id)->adressfull;
            // $user->faktaddress = $faktaddress;

            // $files  = $this->users->get_files(array('user_id'=>$user->id));
            // $user->files = [];
            // $user->files['dir'] = 'http://rus-client/files/users/';
            // foreach ($files as $file) {
            //     $user->files[$file->type] = $file->name;
            // }

            // $user->token = '3b42527be34ee985d8747ad190f0515e';

            // $json = json_encode($user, JSON_UNESCAPED_UNICODE);


            // echo $json;
            // die;


            // !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
            
            $this->json = $this->request->get('json');
            $this->run();
        }
        else{

            header('Content-type: json/application');
            $res = [
                'status' => false,
                'error' => 'Неверный тип запроса. Исапользуйте POST'
            ];
            $this->response($res, 400);
            exit;
        }
    }
    
    private function run()
    {

        header('Content-type: json/application');

        $error = [];
        $error1 = [];

        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            $res = [
                'status' => false,
                'error' => 'Неверный тип запроса. Исапользуйте POST'
            ];
            $this->response($res, 400);
            exit;
        }
        
        $json_array = (array)json_decode(file_get_contents('php://input'));


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

        if (!empty($error1)) {
            $res = [
                'status' => false,
                'error' => $error1[0]
            ];
            $this->response($res, 400);
            exit;
        }
        elseif (!empty($error)) {
            $res = [
                'status' => false,
                'error' => $error[0]
            ];
            $this->response($res, 409);
            exit;
        }


        $user_fields = ['email','lastname','firstname','patronymic',
        'birth','birth_place','phone_mobile','passport_serial','passport_date',
        'passport_issued','first_loan_amount','first_loan_period','social',
        'profession','workplace','workphone','income','expenses',
        'average_pay','amount_pay','enabled','stage_personal','stage_passport',
        'stage_address','stage_work','stage_files','stage_card','lead_partner_id'];

        $contact_fields = ['contact_person_name','contact_person_relation','contact_person_phone'];

        $work_fields = ['work_name','work_director_phone'];

        $user = [];
        $contact_person = [];
        $work = [];
        foreach ($json_array as $key => $value) {
            if (in_array($key, $user_fields)) {
                $user[$key] = $value;
            }
            if (in_array($key, $contact_fields)) {
                $key_new = substr($key, 15);
                $contact_person[$key_new] = $value;
            }
            if (in_array($key, $work_fields)) {
                $key_new = substr($key, 5);
                $work[$key_new] = $value;
            }
        }

        // Добавляем пользователя  
        $rand_code = mt_rand(1000, 9999);    
        $user['enabled'] = 1;
        $user['stage_personal'] = 1;
        $user['stage_passport'] = 1;
        $user['stage_address'] = 1;
        $user['stage_work'] = 0;
        $user['stage_files'] = 1;
        $user['stage_card'] = 0;
        $user['lead_partner_id'] = $tokens->ID;
        $user['sms'] = $rand_code;
        
        $user_id = $this->users->add_user($user);

        if ($user_id == 0){
            $res = [
                'status' => false,
                'error' => 'Ошибка добавления пользователя'
            ];
            $this->response($res, 500);
            exit;
        }

        // Добавляем контактное лицо
        $contactperson_id = $this->Contactpersons->add_contactperson($contact_person);
        if($contactperson_id != 0){
            $this->users->update_user($user_id, array('stage_contact' => 1));
        }

        // Добавляем данные по руководителю
        $work['user_id'] = $user_id;
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
            
            $file_extensions = ['JPEG','JPG','PNG','GIF','RAW','TIFF','BMP'];
            $file_info = new SplFileInfo($value);
            if(!in_array(mb_strtoupper($file_info->getExtension()), $file_extensions)){
                continue;
            }

            $file = $json_array['files']->dir.$value;
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
            $this->users->add_file($update);

        }


        // добавляем заявку
        $order = array(
            // 'card_id' => $card->id,
            'user_id' => $user_id,
            'amount' => $user['first_loan_amount'],
            'period' => $user['first_loan_period'],
            'first_loan' => 1,
            'date' => date('Y-m-d H:i:s'),
            'accept_sms' => $rand_code,
            'client_status' => 'api',
            'autoretry' => 1,
        );

        $order_id = $this->orders->add_order($order);

        $uid = 'a0'.$order_id.'-'.date('Y').'-'.date('md').'-'.date('Hi').'-01771ca07de7';
        $this->users->update_user($this->user_id, array(
            'UID' => $uid,
        ));


        // добавляем задание для проведения активных скорингов
        $scoring_types = $this->scorings->get_types();
        foreach ($scoring_types as $scoring_type)
        {
            if ($scoring_type->active && empty($scoring_type->is_paid))
            {
                $add_scoring = array(
                    'user_id' => $user_id,
                    'order_id' => $order_id,
                    'type' => $scoring_type->name,
                    'status' => 'new',
                    'created' => date('Y-m-d H:i:s')
                );
                $this->scorings->add_scoring($add_scoring);
            }
        }


        $res = [
            'status' => true,
        ];
        $this->response($res, 200);

        exit;
    }

    private function response($res, $http_response_code)
    {
        http_response_code(200);
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        $this->logging($res);
    }


    public function logging($res)
    {
        
        $str = PHP_EOL.'==================================================================='.PHP_EOL;
        $str .= date('d.m.Y H:i:s').PHP_EOL;
        $str .= json_encode($res, JSON_UNESCAPED_UNICODE).PHP_EOL;
        $str .= $this->json;

        file_put_contents($this->log_dir.'API.txt', $str, FILE_APPEND);
    }
}

new ApiLead();