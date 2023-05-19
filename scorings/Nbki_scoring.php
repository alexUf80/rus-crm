<?php

ini_set('max_execution_time', 60);

class Nbki_scoring extends Core
{
    private $scoring_id;

    public function __construct()
    {
        parent::__construct();
    }

    public function run_scoring($scoring_id)
    {
        $scoring = $this->scorings->get_scoring($scoring_id);
        $this->scoring_id = $scoring_id;

        $user = $this->users->get_user((int)$scoring->user_id);

        $regAddress = $this->Addresses->get_address($user->regaddress_id);

        if ($regAddress->locality)
            $city = $regAddress->locality;
        else
            $city = $regAddress->city;


        return $this->scoring(
            $user->firstname,
            $user->patronymic,
            $user->lastname,
            $city,
            $regAddress->street,
            $user->birth,
            $user->birth_place,
            $user->passport_serial,
            $user->passport_date,
            $user->passport_issued,
            $user->gender,
            $user->client_status,
            $user->inn,
            $iser->snils
        );


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
        $client_status,
        $inn,
        $snils
    )
    {
        $genderArr = [
            'male' => 1,
            'female' => 2
        ];

        $json = '{
    "user": {
        "passport": {
            "series": "' . substr($passport_serial, 0, 4) . '",
            "number": "' . substr($passport_serial, 5) . '",
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
        },
        "registration_numbers": {
            "taxpayer_number": "' . addslashes($inn) . '",
            "state_registration_number": "' . addslashes($snils) . '"
        }
    },
    "requisites": {
        "member_code": "1401SS000000",
        "user_id": "1401SS000002",
        "password": "934kjnG@"
    }
}';

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'http://185.182.111.110:9009/api/v2/history/',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
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


        if (empty($result)) {
            $add_scoring = array(
                'status' => 'error',
                'body' => '',
                'success' => (int)$result,
                'string_result' => 'Ошибка запроса'
            );

            $this->scorings->update_scoring($this->scoring_id, $add_scoring);

            return $add_scoring;
        }

        if (isset($result['status']) && $result['status'] == 'error') {
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

        if (in_array($client_status, ['nk', 'rep']))
            $scoring_type = $scoring_type->params['nk'];
        else
            $scoring_type = $scoring_type->params['pk'];


        $number_of_active = $scoring_type['nbki_number_of_active'];
        $nbki_share_of_unknown = $scoring_type['nbki_share_of_unknown'];
        $nbki_share_of_overdue = $scoring_type['nbki_share_of_overdue'];
        $open_to_close_ratio = $scoring_type['open_to_close_ratio'];

        if ($result['number_of_active'] >= $number_of_active) {
            $add_scoring = array(
                'status' => 'completed',
                'body' => serialize($result['data'] + ['report_url' => $result['report_url']]),
                'success' => 0,
                'string_result' => 'Превышен допустимый порог активных займов'
            );

            $this->scorings->update_scoring($this->scoring_id, $add_scoring);

            return $add_scoring;
        }


        if ($result['count_of_overdue'] >= $nbki_share_of_overdue) {
            $add_scoring = array(
                'status' => 'completed',
                'body' => serialize($result['data'] + ['report_url' => $result['report_url']]),
                'success' => 0,
                'string_result' => 'Превышен допустимый порог просроченных займов'
            );

            $this->scorings->update_scoring($this->scoring_id, $add_scoring);

            return $add_scoring;
        }

        $add_scoring = array(
            'status' => 'completed',
            'body' => serialize($result['data'] + ['report_url' => $result['report_url']]),
            'success' => 1,
            'string_result' => 'Проверки пройдены'
        );

        $this->scorings->update_scoring($this->scoring_id, $add_scoring);

        return $add_scoring;
    }
}