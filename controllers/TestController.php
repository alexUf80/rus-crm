<?php

use PhpOffice\PhpSpreadsheet\IOFactory;

error_reporting(-1);
ini_set('display_errors', 'On');

class TestController extends Controller
{
    public function fetch()
    {

        //  $items =
        //     [
        //         34978, 35037
        //     ];



        // //$startDate = date('Y-m-d 00:00:00', strtotime('2023-03-29'));

        // //$items = ContractsORM::where('inssuance_date', '>=', $startDate)->get();

        // foreach ($items as $item)
        //     Onec::sendRequest(['method' => 'send_loan', 'params' => $item]);

        // $Regadress = json_decode($this->dadata->get_all('г Вологда'));
        // var_dump($Regadress);

        $token = "fbe410e22742f20985dd53a4da5f3adc465406c7";
        $secret = "0e39972df197e37429a1a8c02d892846a1fe3d86";
        

        $users = $this->users->get_users();
        foreach ($users as $user) {
            // !!!!!!!!!!!!!!!!!!!!!
            // $user = $this->users->get_user(33897);

            // !!!!!!!!!!!!!!!!!!!!!
            $address = $this->Addresses->get_address($user->faktaddress_id);
            if ($address) {
                $adressfull = $address->adressfull;

                var_dump($user->id);
                // var_dump($user->time_zone);
                // var_dump($adressfull);
    
                $dadata = new Dadata($token, $secret);
                $dadata->init();
                
                $result = $dadata->clean("address", $adressfull);
                print_r($result[0]['timezone']);
    
                $this->users->update_user($user->id, array('time_zone' => $result[0]['timezone']));
    
                $dadata->close();
                
                // die;   
            }
        }

        
        

        exit;
    }


    

    public function services()
    {
        $services = OperationsORM::whereIn('type', ['INSURANCE', 'INSURANCE_BC', 'REJECT_REASON'])->get();

        foreach ($services as $service) {
            $contract = ContractsORM::where('order_id', $service->order_id)->first();

            $item = new stdClass();
            $item->user_id = $service->user_id;
            $item->insurance_cost = $service->amount;
            $item->number = $contract->number;
            $item->operation_id = $service->id;
            $item->order_id = $service->order_id;

            if (in_array($service->type, ['INSURANCE', 'INSURANCE_BC']))
                $item->is_insurance = 1;
            else
                $item->is_insurance = 0;

            if (empty($contract->number) && $item->is_insurance == 1)
                continue;

            Onec::sendRequest(['method' => 'send_services', 'params' => $item]);
        }

        exit;
    }
}

class TooManyRequests extends Exception
    {
    }
    
    class Dadata
    {
        private $clean_url = "https://cleaner.dadata.ru/api/v1/clean";
        private $suggest_url = "https://suggestions.dadata.ru/suggestions/api/4_1/rs";
        private $token;
        private $secret;
        private $handle;
    
        public function __construct($token, $secret)
        {
            $this->token = $token;
            $this->secret = $secret;
        }
    
        /**
         * Initialize connection.
         */
        public function init()
        {
            $this->handle = curl_init();
            curl_setopt($this->handle, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($this->handle, CURLOPT_HTTPHEADER, array(
                "Content-Type: application/json",
                "Accept: application/json",
                "Authorization: Token " . $this->token,
                "X-Secret: " . $this->secret,
            ));
            curl_setopt($this->handle, CURLOPT_POST, 1);
        }
    
        /**
         * Clean service.
         * See for details:
         *   - https://dadata.ru/api/clean/address
         *   - https://dadata.ru/api/clean/phone
         *   - https://dadata.ru/api/clean/passport
         *   - https://dadata.ru/api/clean/name
         * 
         * (!) This is a PAID service. Not included in free or other plans.
         */
        public function clean($type, $value)
        {
            $url = $this->clean_url . "/$type";
            $fields = array($value);
            return $this->executeRequest($url, $fields);
        }
    
        /**
         * Find by ID service.
         * See for details:
         *   - https://dadata.ru/api/find-party/
         *   - https://dadata.ru/api/find-bank/
         *   - https://dadata.ru/api/find-address/
         */
        public function findById($type, $fields)
        {
            $url = $this->suggest_url . "/findById/$type";
            return $this->executeRequest($url, $fields);
        }
    
        /**
         * Reverse geolocation service.
         * See https://dadata.ru/api/geolocate/ for details.
         */
        public function geolocate($lat, $lon, $count = 10, $radius_meters = 100)
        {
            $url = $this->suggest_url . "/geolocate/address";
            $fields = array(
                "lat" => $lat,
                "lon" => $lon,
                "count" => $count,
                "radius_meters" => $radius_meters
            );
            return $this->executeRequest($url, $fields);
        }
    
        /**
         * Detect city by IP service.
         * See https://dadata.ru/api/iplocate/ for details.
         */
        public function iplocate($ip)
        {
            $url = $this->suggest_url . "/iplocate/address";
            $fields = array(
                "ip" => $ip
            );
            return $this->executeRequest($url, $fields);
        }
    
        /**
         * Suggest service.
         * See for details:
         *   - https://dadata.ru/api/suggest/address
         *   - https://dadata.ru/api/suggest/party
         *   - https://dadata.ru/api/suggest/bank
         *   - https://dadata.ru/api/suggest/name
         *   - ...
         */
        public function suggest($type, $fields)
        {
            $url = $this->suggest_url . "/suggest/$type";
            return $this->executeRequest($url, $fields);
        }
    
        /**
         * Close connection.
         */
        public function close()
        {
            curl_close($this->handle);
        }
    
        private function executeRequest($url, $fields)
        {
            curl_setopt($this->handle, CURLOPT_URL, $url);
            if ($fields != null) {
                curl_setopt($this->handle, CURLOPT_POST, 1);
                curl_setopt($this->handle, CURLOPT_POSTFIELDS, json_encode($fields));
            } else {
                curl_setopt($this->handle, CURLOPT_POST, 0);
            }
            $result = $this->exec();
            $result = json_decode($result, true);
            return $result;
        }
    
        private function exec()
        {
            $result = curl_exec($this->handle);
            $info = curl_getinfo($this->handle);
            if ($info['http_code'] == 429) {
                throw new TooManyRequests();
            } elseif ($info['http_code'] != 200) {
                throw new Exception('Request failed with http code ' . $info['http_code'] . ': ' . $result);
            }
            return $result;
        }
    }