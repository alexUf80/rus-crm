<?php


class Bias extends Core
{
    private $login = '';
    private $password = '';

    public function __construct()
    {
    	parent::__construct();
        
        $this->login = 'yno_khmelik';//$this->settings->apikeys['bias']['login'];
        $this->password = 'Adele2011!';//$this->settings->apikeys['bias']['password'];
    }


    public function get_info_from_passport($sernum, $service = 600000)
    {
        $sernum = str_replace(array(' ', '-'), '', $sernum);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://igls2.bias.ru/api/request',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{
              "login": "' . $this->login . '",
              "password": "' . $this->password . '",
              "timeout": -1,
              "services": [
                "' . $service . '"
              ],
              "searchFields": [
                {
                  "name": "sernum",
                  "value": "' . $sernum . '"
                }
              ]
            }',
            CURLOPT_SSL_VERIFYHOST => 0,  // don't verify ssl
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        return $response;
    }

    public function get_info_from_phone($phone, $service = 600004)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://igls2.bias.ru/api/request',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{
              "login": "' . $this->login . '",
              "password": "' . $this->password . '",
              "timeout": -1,
              "services": [
                "' . $service . '"
              ],
              "searchFields": [
                {
                  "name": "phone",
                  "value": "' . $phone . '"
                }
              ]
            }',
            CURLOPT_SSL_VERIFYHOST => 0,  // don't verify ssl
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        return $response;
    }

    public function tracking($uid)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://igls2.bias.ru/api/request',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{
              "login": "' . $this->login . '",
              "password": "' . $this->password . '",
              "timeout": -1,
              "uid": "' . $uid . '"
            }',
            CURLOPT_SSL_VERIFYHOST => 0,  // don't verify ssl
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        return $response;
    }
}