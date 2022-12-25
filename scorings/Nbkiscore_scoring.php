<?php

class Nbkiscore_scoring extends Core
{
    private $scoring_id;
    private $error = null;

    public function __construct()
    {
        parent::__construct();
    }

    public function run_scoring($scoring_id)
    {
        if ($scoring = $this->scorings->get_scoring($scoring_id)) {
            $this->scoring_id = $scoring_id;

            if ($user = $this->users->get_user((int)$scoring->user_id)) {
                if ($user->Regcity) {
                    $city = $user->Regcity;
                } elseif ($user->Reglocality) {
                    $city = $user->Reglocality;
                } else {
                    $city = $user->Regcity;
                }

                return $this->scoring(
                    $user->firstname,
                    $user->patronymic,
                    $user->lastname,
                    $city,
                    $user->Regstreet,
                    $user->birth,
                    $user->birth_place,
                    $user->passport_serial,
                    $user->passport_date,
                    $user->passport_issued,
                    $user->gender,
                    $user->client_status
                );
            } else {
                $update = array(
                    'status' => 'error',
                    'string_result' => 'не найден пользователь'
                );
                $this->scorings->update_scoring($scoring_id, $update);
                return $update;
            }
        }
    }

    public function scoring(
        $firstname,
        $patronymic,
        $lastname,
        $Regcity,
        $Regstreet,
        $birth,
        $birth_place,
        $passport_serial,
        $passport_date,
        $passport_issued,
        $gender,
        $client_status
    )
    {
        $genderArr = [
            'male' => 1,
            'female' => 2
        ];

        $json = '{
    "user": {
        "passport": {
            "series": "'. substr($passport_serial, 0, 4) .'",
            "number": "'. substr($passport_serial, 5) .'",
            "issued_date": "' . date('Y-m-d', strtotime($passport_date)) . '",
            "issued_by": "' . addslashes($passport_issued) . '",
            "issued_city": "' . addslashes($Regcity) . '"
        },
        "person": {
            "last_name": "' . addslashes($lastname) . '",
            "first_name": "' . addslashes($firstname) . '",
            "middle_name": "' . addslashes($patronymic) . '",
            "birthday": "' . date('Y-m-d', strtotime($birth)) . '",
            "birthday_city": "' . addslashes($birth_place) . '",
            "gender": ' . addslashes($genderArr[$gender]) . '
        },
        "registration_address": {
            "city": "' . addslashes($Regcity) . '",
            "street": "' . addslashes($Regstreet) . '"
        }
    },
    "requisites": {
        "member_code": "XF01RR000000",
        "user_id": "XF01RR000003",
        "password": "D35GTedte54@3q"
    }
}';

//var_dump($json);
//exit;
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'http://51.250.101.109/api/nbki_test2',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $json,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $result = json_decode($response, true);


        if (!$result) {
            $add_scoring = array(
                'status' => 'error',
                'body' => '',
                'success' => (int)$result,
                'string_result' => 'Ошибка запроса'
            );

            $this->scorings->update_scoring($this->scoring_id, $add_scoring);

            return $add_scoring;
        }

        if ($result['status'] == 'error') {
            $add_scoring = array(
                'body' => '',
                'status' => 'error',
                'success' => (int)false,
                'string_result' => 'Неуспешный ответ: ' . $result['data']
            );

            $this->scorings->update_scoring($this->scoring_id, $add_scoring);

            return $add_scoring;
        }

        switch ($client_status) {
            case 'nk':
            case 'rep':
                $max_score = $this->settings->nbkiscore['nk'];
                break;

            case 'pk':
            case 'crm':
                $max_score = $this->settings->nbkiscore['pk'];
                break;

            default:
                $max_score = $this->settings->nbkiscore['nk'];
                break;
        }


        if ($result['data'] > $max_score) {
            $add_scoring = array(
                'status' => 'completed',
                'body' => $result['data'],
                'success' => 1,
                'string_result' => 'скорбалл: '. $result['data']
            );

            $this->scorings->update_scoring($this->scoring_id, $add_scoring);

            return $add_scoring;
        } else {
            $add_scoring = array(
                'status' => 'completed',
                'body' => $result['data'],
                'success' => 0,
                'string_result' => 'скорбалл: '. $result['data']
            );
        }

        $this->scorings->update_scoring($this->scoring_id, $add_scoring);

        return $add_scoring;
    }
}