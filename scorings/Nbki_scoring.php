<?php

class Nbki_scoring extends Core
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

                $regaddress = $this->Addresses->get_address($user->regaddress_id);

                if ($regaddress->district) {
                    $city = $regaddress->district;
                } elseif ($regaddress->locality) {
                    $city = $regaddress->locality;
                } else {
                    $city = $regaddress->city;
                }

                return $this->scoring(
                    $user->firstname,
                    $user->patronymic,
                    $user->lastname,
                    $city,
                    $regaddress->street,
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
        "member_code": "9R01SS000000",
        "user_id": "9R01SS000002",
        "password": "Gpj895Rp"
    }
}';

//var_dump($json);
echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($json);echo '</pre><hr />';
//exit;
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'http://51.250.101.109/api/nbki_test',
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
$error = curl_error($curl);
echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($response, $error);echo '</pre><hr />';
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
            if (json_encode($result['data']) == "No subject found for this inquiry") {
                $add_scoring = array(
                    'body' => '',
                    'status' => 'error',
                    'success' => (int)true,
                    'string_result' => 'Неуспешный ответ: ' . 'субъект не найден',
                );
            } else {
                $add_scoring = array(
                    'body' => '',
                    'status' => 'error',
                    'success' => (int)false,
                    'string_result' => 'Неуспешный ответ: ' . json_encode($result['data'], JSON_UNESCAPED_UNICODE)
                );
            }


            $this->scorings->update_scoring($this->scoring_id, $add_scoring);

            return $add_scoring;
        }

        $scoring_type = $this->scorings->get_type('nbki');
        
        switch ($client_status) {
            case 'nk':
            case 'rep':
                $number_of_active_max = $scoring_type->params['nk']['nbki_number_of_active_max'];
                $number_of_active = $scoring_type->params['nk']['nbki_number_of_active'];
                $share_of_unknown = $scoring_type->params['nk']['nbki_share_of_unknown'];
                $share_of_overdue = $scoring_type->params['nk']['nbki_share_of_overdue'];
                $open_to_close_ratio = $scoring_type->params['nk']['open_to_close_ratio'];
                break;

            case 'pk':
            case 'crm':
                $number_of_active_max = $scoring_type->params['pk']['nbki_number_of_active_max'];
                $number_of_active = $scoring_type->params['pk']['nbki_number_of_active'];
                $share_of_unknown = $scoring_type->params['pk']['nbki_share_of_unknown'];
                $share_of_overdue = $scoring_type->params['pk']['nbki_share_of_overdue'];
                $open_to_close_ratio = $scoring_type->params['pk']['open_to_close_ratio'];
                break;

            default:
                $number_of_active_max = $scoring_type->params['nk']['nbki_number_of_active_max'];
                $number_of_active = $scoring_type->params['nk']['nbki_number_of_active'];
                $share_of_unknown = $scoring_type->params['nk']['nbki_share_of_unknown'];
                $share_of_overdue = $scoring_type->params['nk']['nbki_share_of_overdue'];
                $open_to_close_ratio = $scoring_type->params['nk']['open_to_close_ratio'];
                break;
        }


        if ($result['number_of_active'] >= $number_of_active_max) {
            $add_scoring = array(
                'status' => 'completed',
                'body' => serialize($result['data']),
                'success' => 0,
                'string_result' => 'превышен допустимый порог активных займов'
            );

            $this->scorings->update_scoring($this->scoring_id, $add_scoring);

            return $add_scoring;
        }

        if ($result['number_of_active'] >= $number_of_active) {
            if ($result['share_of_overdue'] >= $share_of_overdue || $result['share_of_unknown'] >= $share_of_unknown) {
                $add_scoring = array(
                    'status' => 'completed',
                    'body' => serialize($result['data']),
                    'success' => 0,
                    'string_result' => 'превышен допустимый порог доли просроченных или неизвестных займов'
                );

                $this->scorings->update_scoring($this->scoring_id, $add_scoring);

                return $add_scoring;
            }
        }

        if ($result['share_of_unknown'] > $share_of_unknown) {
            $add_scoring = array(
                'status' => 'completed',
                'body' => serialize($result['data']),
                'success' => 0,
                'string_result' => 'превышен допустимый порог доли неизвестных займов'
            );

            $this->scorings->update_scoring($this->scoring_id, $add_scoring);

            return $add_scoring;
        }

        if (isset($result['open_to_close_ratio'])) {
            if ($result['open_to_close_ratio'] > $open_to_close_ratio) {
                $add_scoring = array(
                    'status' => 'completed',
                    'body' => serialize($result['data']),
                    'success' => 0,
                    'string_result' => 'превышен порог соотношения открытых к закрытым за последние 30 дней'
                );
    
                $this->scorings->update_scoring($this->scoring_id, $add_scoring);
    
                return $add_scoring;
            }
        }

        $add_scoring = array(
            'status' => 'completed',
            'body' => serialize($result['data']),
            'success' => 1,
            'string_result' => 'Проверки пройдены'
        );

        $this->scorings->update_scoring($this->scoring_id, $add_scoring);

        return $add_scoring;
    }
}