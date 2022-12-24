<?php

ini_set('display_errors', 'On');
error_reporting(-1);

chdir('..');
require 'autoload.php';

class ConnexionsAjax extends Core
{
    public function __construct()
    {
        parent::__construct();

        $this->run();
    }

    public function run()
    {
        if ($user_id = $this->request->get('user_id', 'integer')) {
//$user_id = 100263;
            if ($user = $this->users->get_user($user_id)) {
                $result = array();

                $result['phone_mobile'] = new StdClass();
                $result['phone_mobile']->search = $user->phone_mobile;
                $result['phone_mobile']->found = array_filter($this->find_phone($user->id, $user->phone_mobile));

                $result['workphone'] = new StdClass();
                $result['workphone']->search = $user->workphone;
                $result['workphone']->found = array_filter($this->find_phone($user->id, $user->workphone));

                $result['passport'] = new StdClass();
                $result['passport']->search = $user->passport_serial;
                $result['passport']->found = array_filter($this->find_passport($user->id, $user->passport_serial));

                if ($user->snils) {
                    $result['snils'] = new StdClass();
                    $result['snils']->search = $user->snils;
                    $result['snils']->found = array_filter($this->find_snils($user->id, $user->snils));
                }


                if ($user->inn) {
                    $result['inn'] = new StdClass();
                    $result['inn']->search = $user->inn;
                    $result['inn']->found = array_filter($this->find_inn($user->id, $user->inn));
                }

                if ($user->reg_ip) {
                    $result['reg_ip'] = new StdClass();
                    $result['reg_ip']->search = $user->reg_ip;
                    $result['reg_ip']->found = array_filter($this->find_reg_ip($user->id, $user->reg_ip));
                }

                if ($user->last_ip) {
                    $result['last_ip'] = new StdClass();
                    $result['last_ip']->search = $user->last_ip;
                    $result['last_ip']->found = array_filter($this->find_last_ip($user->id, $user->last_ip));
                }

                $result['card_number'] = new StdClass();
                $result['card_number']->found = array_filter($this->find_card($user->id));

                $result['regaddress'] = new StdClass();
                $result['regaddress']->search = $user->Regregion . ' ' . $user->Regdistrict . ' ' . $user->Reglocality . ' ' . $user->Regcity . ' ' . $user->Regstreet . ' ' . $user->Reghousing . ' ' . $user->Regbuilding . ' ' . $user->Regroom;
                $result['regaddress']->found = array_filter($this->find_address($user->id, $user->Regregion, $user->Regdistrict, $user->Reglocality, $user->Regcity, $user->Regstreet, $user->Reghousing, $user->Regbuilding, $user->Regroom));

                $result['faktaddress'] = new StdClass();
                $result['faktaddress']->search = $user->Faktregion . ' ' . $user->Faktdistrict . ' ' . $user->Faktlocality . ' ' . $user->Faktcity . ' ' . $user->Faktstreet . ' ' . $user->Fakthousing . ' ' . $user->Faktbuilding . ' ' . $user->Faktroom;
                $result['faktaddress']->found = array_filter($this->find_address($user->id, $user->Faktregion, $user->Faktdistrict, $user->Faktlocality, $user->Faktcity, $user->Faktstreet, $user->Fakthousing, $user->Faktbuilding, $user->Faktroom));

                $result['contactperson1'] = new StdClass();
                $result['contactperson1']->search = $user->contact_person_phone;
                $result['contactperson1']->fio = $user->contact_person_name;
                $result['contactperson1']->found = array_filter($this->find_phone($user->id, $user->contact_person_phone));

                $result['contactperson2'] = new StdClass();
                $result['contactperson2']->search = $user->contact_person2_phone;
                $result['contactperson2']->fio = $user->contact_person2_name;
                $result['contactperson2']->found = array_filter($this->find_phone($user->id, $user->contact_person2_phone));

                $this->output($result);
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($result);echo '</pre><hr />';

            } else {
                echo 'USER NOT FOUND';
            }
        } else {
            echo 'UNDEFINED USER ID';
        }
    }

    private function find_phone($user_id, $phone)
    {
        $prepare_phone = $this->prepare_phone($phone);

        $str_split_phone = str_split($prepare_phone, 1);
        $first_part = [];
        $second_part = [];
        $final_part = [];
        $i = 0;

        foreach ($str_split_phone as $digit) {
            if ($i < 3) {
                $first_part[] = $digit;
            }


            if ($i >= 3 && $i < 6) {
                $second_part[] = $digit;
            }

            if ($i >= 6) {
                $final_part[] = $digit;
            }

            $i++;
        }

        $first_part_number = '+7(' . implode($first_part) . ')';
        $second_part_number = implode($second_part);
        $final_part_number = implode($final_part);

        $prepare_phone = '7'.$prepare_phone;

        $another_number = "$first_part_number $second_part_number-$final_part_number";

        $results = array();

        $query = $this->db->placehold("
            SELECT 
                id,
                lastname,
                firstname,
                patronymic,
                phone_mobile AS user_phone
            FROM __users
            WHERE id != ?
            AND phone_mobile in (?, ?)
        ", $user_id, (string)$prepare_phone, (string)$another_number);
        $this->db->query($query);
        $results['users'] = $this->db->results();


        $query = $this->db->placehold("
            SELECT 
                id,
                lastname,
                firstname,
                patronymic,
                phone_mobile AS user_phone
            FROM __users
            WHERE id != ?
            AND workphone in (?, ?)
        ", $user_id, (string)$prepare_phone, (string)$another_number);
        $this->db->query($query);
        $results['workphone'] = $this->db->results();

        $query = $this->db->placehold("
            SELECT 
                id,
                lastname,
                firstname,
                patronymic,
                phone_mobile AS user_phone
            FROM __users
            WHERE id != ?
            AND chief_phone in (?, ?)
        ", $user_id, (string)$prepare_phone, (string)$another_number);
        $this->db->query($query);
        $results['chief_phone'] = $this->db->results();


        $results['contactpersons'] = array();
        $query = $this->db->placehold("
            SELECT 
                cr.name AS cp_name,
                cr.relation AS cp_relation,
                cr.phone AS cp_phone,
                u.id,
                u.lastname,
                u.firstname,
                u.patronymic,
                u.phone_mobile AS user_phone
            FROM __contactpersons AS cr
            JOIN __users AS u
            ON cr.user_id = u.id
            WHERE cr.user_id != ?
            AND phone in (?, ?)
        ", $user_id, (string)$prepare_phone, (string)$another_number);
        $this->db->query($query);

        $results['contactpersons'] = array_merge($results['contactpersons'], $this->db->results());

        $query = $this->db->placehold("
            SELECT 
                contact_person_name AS cp_name,
                contact_person_relation AS cp_relation,
                contact_person_phone AS cp_phone,
                id,
                lastname,
                firstname,
                patronymic,
                phone_mobile AS user_phone
            FROM __users
            WHERE id != ?
            AND contact_person_phone in (?, ?)
        ", $user_id, (string)$prepare_phone, (string)$another_number);
        $this->db->query($query);
        $results['contactpersons'] = array_merge($results['contactpersons'], $this->db->results());

        $query = $this->db->placehold("
            SELECT 
                contact_person2_name AS cp_name,
                contact_person2_relation AS cp_relation,
                contact_person2_phone AS cp_phone,
                id,
                lastname,
                firstname,
                patronymic,
                phone_mobile AS user_phone
            FROM __users
            WHERE id != ?
            AND contact_person2_phone in (?, ?)
        ", $user_id, (string)$prepare_phone, (string)$another_number);
        $this->db->query($query);
        $results['contactpersons'] = array_merge($results['contactpersons'], $this->db->results());

        return $results;
    }

    private function find_address($user_id, $region, $district, $locality, $city, $street, $housing, $building, $room)
    {
        $results = array();

        $query = $this->db->placehold("
            SELECT 
                id, 
                lastname,
                firstname,
                patronymic,
                phone_mobile
            FROM __users
            WHERE id != ?
            AND Regregion LIKE '%" . $this->db->escape($region) . "%'
            AND Regdistrict LIKE '%" . $this->db->escape($district) . "%'
            AND Reglocality LIKE '%" . $this->db->escape($locality) . "%'
            AND Regcity LIKE '%" . $this->db->escape($city) . "%'
            AND Regstreet LIKE '%" . $this->db->escape($street) . "%'
            AND Reghousing LIKE '%" . $this->db->escape($housing) . "%'
            AND Regbuilding LIKE '%" . $this->db->escape($building) . "%'
            AND Regroom LIKE '%" . $this->db->escape($room) . "%'
        ", $user_id);
        $this->db->query($query);

        $results['regaddress'] = $this->db->results();


        $query = $this->db->placehold("
            SELECT 
                id, 
                lastname,
                firstname,
                patronymic,
                phone_mobile
            FROM __users
            WHERE id != ?
            AND Faktregion LIKE '%" . $this->db->escape($region) . "%'
            AND Faktdistrict LIKE '%" . $this->db->escape($district) . "%'
            AND Faktlocality LIKE '%" . $this->db->escape($locality) . "%'
            AND Faktcity LIKE '%" . $this->db->escape($city) . "%'
            AND Faktstreet LIKE '%" . $this->db->escape($street) . "%'
            AND Fakthousing LIKE '%" . $this->db->escape($housing) . "%'
            AND Faktbuilding LIKE '%" . $this->db->escape($building) . "%'
            AND Faktroom LIKE '%" . $this->db->escape($room) . "%'
        ", $user_id);
        $this->db->query($query);

        $results['faktaddress'] = $this->db->results();

        return $results;
    }

    private function find_passport($user_id, $passport)
    {
        $query = $this->db->placehold("
            SELECT 
                id, 
                lastname,
                firstname,
                patronymic,
                phone_mobile
            FROM __users
            WHERE id != ?
            AND passport_serial = ?
        ", $user_id, $passport);
        $this->db->query($query);

        $results['passport'] = $this->db->results();

        return $results;
    }

    private function find_snils($user_id, $snils)
    {
        $query = $this->db->placehold("
            SELECT
                id,
                lastname,
                firstname,
                patronymic
            FROM s_users
            WHERE id != ?
            AND snils = ?
        ", $user_id, $snils);

        $this->db->query($query);

        $results['snils'] = $this->db->results();

        return $results;
    }

    private function find_inn($user_id, $inn)
    {
        $query = $this->db->placehold("
            SELECT 
                id, 
                lastname,
                firstname,
                patronymic,
                phone_mobile
            FROM __users
            WHERE id != ?
            AND inn = ?
        ", $user_id, $inn);
        $this->db->query($query);

        $results['inn'] = $this->db->results();

        return $results;
    }

    private function find_reg_ip($user_id, $reg_ip)
    {
        $query = $this->db->placehold("
            SELECT 
                id, 
                lastname,
                firstname,
                patronymic,
                phone_mobile
            FROM __users
            WHERE id != ?
            AND reg_ip = '$reg_ip'
        ", $user_id);
        $this->db->query($query);

        $results['reg_ip'] = $this->db->results();

        return $results;
    }

    private function find_last_ip($user_id, $last_ip)
    {
        $query = $this->db->placehold("
            SELECT 
                id, 
                lastname,
                firstname,
                patronymic
            FROM __users
            WHERE id != $user_id
            AND last_ip = '$last_ip'
        ");
        $this->db->query($query);

        $results['last_ip'] = $this->db->results();

        return $results;
    }

    private function find_card($user_id)
    {


        $query = $this->db->placehold("
            SELECT pan
            FROM __cards 
            WHERE user_id = ?
        ", $user_id);

        $this->db->query($query);

        $result = $this->db->result();

        if($result)
        {
            $pan = $result->pan;

            $query = $this->db->placehold("
            SELECT  
                us.id,
                us.lastname,
                us.firstname,
                us.patronymic,
                cr.pan
            FROM __users as us
            join __cards as cr on cr.user_id = us.id
            WHERE us.id != ?
            AND cr.pan = '$pan'
        ", $user_id);
            $this->db->query($query);

            $results['card_number'] = $this->db->results();

            return $results;
        }

        else
        {
            return $results['card_number'] = ['null' => null];
        }
    }


    private function prepare_phone($phone)
    {
        $prepare_phone = str_replace(array(' ', '-', '(', ')', '+'), '', $phone);
        $prepare_phone = substr($prepare_phone, -10);
        return $prepare_phone;
    }

    private function output($results)
    {
        $this->design->assign('results', $results);
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($results);echo '</pre><hr />';
        echo $this->design->fetch('connexions.tpl');

    }
}

new ConnexionsAjax();