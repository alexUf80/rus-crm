<?php
error_reporting(-1);
ini_set('display_errors', 'On');

require('../autoload.php');

class ImportUsers extends Core
{
    private $import_dir;
    
    public function __construct()
    {
    	parent::__construct();
exit;        
        $this->import_dir = $this->config->root_dir.'base/eco/user/';
        
        $this->run();
    }
    
    
    private function run()
    {
        $scan = array_values(array_filter(scandir($this->import_dir), function($var){
            return $var != '.' && $var != '..';
        }));
        
        $filenumber = $this->request->get('file', 'integer');
        
        if ($filenumber == 0)
            $this->truncate();
    
        if (!isset($scan[$filenumber]))
            exit('Не найден файл');
        
        $content = json_decode(file_get_contents($this->import_dir.$scan[$filenumber]));
        
        foreach ($content->results as $item)
        {
            $this->import_item($item);
        }

        echo '<meta http-equiv="refresh" content="2;'.$this->request->url(array('file'=>$filenumber+1)).'">';
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($content->results);echo '</pre><hr />';    
    }
    
    private function import_item($item)
    {
        $user = new StdClass();
        
//        $user-> = 1;
        
        $user->enabled = 1;
        $user->loaded_from_1c = 1;
        $user->stage_personal = 1;
        $user->stage_passport = 1;
        $user->stage_address = 1;
        $user->stage_work = 1;
        $user->stage_files = 1;
        $user->stage_card = 1;
        
        $user->id_1c = empty($item->saved->mymfo_client_id) ? '' : $item->saved->mymfo_client_id;
        $user->unload_id = $item->objectId;
        $user->lastname = $item->last_name;
        $user->firstname = $item->first_name;
        $user->patronymic = $item->patronymic;
        $user->email = $item->emailOriginal;
        $user->phone_mobile = $item->phone;
        $user->birth_place = $item->birth_place;
        $user->birth = date('d.m.Y', strtotime($item->birthdate));
        $user->gender = $item->gender == 2 ? 'female' : 'male';
        
        $user->snils = $item->passport->snils;
        $user->passport_issued = $item->passport->issued_by;
        $user->passport_serial = $item->passport->series.' '.$item->passport->number;
        $user->passport_date = date('d.m.Y', strtotime($item->passport->issued_at));
        $user->subdivision_code = $item->passport->code;
        
        $user->Regregion = $item->address_reg->region;
        $user->Regcity = $item->address_reg->city;
        $user->Regindex = $item->address_reg->index;
        $user->Regroom = $item->address_reg->flat;
        $user->Regstreet = $item->address_reg->street;
        $user->Reghousing = $item->address_reg->house;
        
        $user->Faktregion = $item->address_res->region;
        $user->Faktcity = $item->address_res->city;
        $user->Faktindex = $item->address_res->index;
        $user->Faktroom = $item->address_res->flat;
        $user->Faktstreet = $item->address_res->street;
        $user->Fakthousing = $item->address_res->house;
        
        if (!empty($item->job))
        {
            $user->profession = $item->job->role;
            $user->workplace = $item->job->company;
            $user->workphone = $item->job->mainphone;
            $user->workaddress = $item->job->index.', '.$item->job->city.', '.$item->job->street.', '.$item->job->house.', '.$item->job->office;
            $user->expenses = $item->job->monthexpenses;
            $user->income = $item->job->income;
            $user->chief_phone = $item->job->contact_phone;
            $user->chief_name = $item->job->contact_person;
        }
        
/*
        $user_id = $this->users->add_user($user);
        
        if (!empty($item->passport_scan_first))
        {
            $this->users->add_file(array(
                'user_id' => $user_id,
                'name' => $item->passport_scan_first->name,
                'type' => 'passport1',
                'status' => 4,
            ));
        }
        if (!empty($item->passport_scan_second))
        {
            $this->users->add_file(array(
                'user_id' => $user_id,
                'name' => $item->passport_scan_second->name,
                'type' => 'passport2',
                'status' => 4,
            ));
        }
        if (!empty($item->passport_scan_selfie))
        {
            $this->users->add_file(array(
                'user_id' => $user_id,
                'name' => $item->passport_scan_selfie->name,
                'type' => 'face',
                'status' => 4,
            ));
        }
        
        echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($item);echo '</pre><hr />';
//exit;
*/
    }
    
    private function truncate()
    {
//        $this->db->query("TRUNCATE TABLE __users");
//        $this->db->query("TRUNCATE TABLE __files");
    }
    
}

new ImportUsers();