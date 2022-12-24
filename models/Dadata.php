<?php

class Dadata extends Core
{
    private $token;

    private $secret_key;

    private $url = 'https://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/';

    public function __construct()
    {
        $this->token = $this->settings->apikeys['dadata']['api_key'];
        $this->secret_key = $this->settings->apikeys['dadata']['secret_key'];
    }

    public function fetch_clean_api($type, $query)
    {
        if (is_array($query)) {
            $fields = $query;
        } else {
            $fields = [$query];
        }


        $result = false;
        if ($ch = curl_init("https://cleaner.dadata.ru/api/v1/clean/" . $type)) {
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Accept: application/json',
                'Authorization: Token ' . $this->token,
                'X-Secret: ' . $this->secret_key,
            ));
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            // json_encode
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
            $result = curl_exec($ch);
            $result = json_decode($result, true);
            curl_close($ch);
        }



        return $result;
    }


    public function get_party($query, $count = 50)
    {
        return $this->suggest("party", array("query" => $query, "count" => $count));
    }

    public function get_region($query, $count = 50)
    {
        $request = new StdClass();
        $request->query = $query;
        $request->count = $count;

        $request->from_bound = new StdClass();
        $request->from_bound->value = 'region';
        $request->to_bound = new StdClass();
        $request->to_bound->value = 'region';

        return $this->suggest("address", $request);
    }

    public function get_city($region_kladr_id, $query, $count = 50)
    {
        $request = new StdClass();
        $request->query = $query;
        $request->count = $count;

        $request->from_bound = new StdClass();
        $request->from_bound->value = 'city';
        $request->to_bound = new StdClass();
        $request->to_bound->value = 'settlement';

        if (!empty($region_kladr_id)) {
            $r = new StdClass();
            $r->kladr_id = $region_kladr_id;
            $request->locations = array($r);
            $request->restrict_value = true;
        }
        return $this->suggest("address", $request);
    }


    public function get_street($city_kladr_id, $query, $count = 50)
    {
        $request = new StdClass();
        $request->query = $query;
        $request->count = $count;

        $request->from_bound = new StdClass();
        $request->from_bound->value = 'street';
        $request->to_bound = new StdClass();
        $request->to_bound->value = 'street';

        if (!empty($city_kladr_id)) {
            $r = new StdClass();
            $r->kladr_id = $city_kladr_id;
            $request->locations = array($r);
            $request->restrict_value = true;
        }
        return $this->suggest("address", $request);
    }

    public function get_house($street_kladr_id, $query, $count = 50)
    {
        $request = new StdClass();
        $request->query = $query;
        $request->count = $count;

        $request->from_bound = new StdClass();
        $request->from_bound->value = 'house';
        $request->to_bound = new StdClass();
        $request->to_bound->value = 'house';

        if (!empty($street_kladr_id)) {
            $r = new StdClass();
            $r->kladr_id = $street_kladr_id;
            $request->locations = array($r);
            $request->restrict_value = true;
        }
        return $this->suggest("address", $request);
    }

    public function get_address($city_kladr_id, $query, $count = 50)
    {
        $request = array("query" => $query, "count" => $count);

        if (!empty($city_kladr_id)) {
            $r = new StdClass();
            $r->kladr_id = $city_kladr_id;
            $request['locations'] = array($r);
            $request['restrict_value'] = true;

            return $this->suggest("address", $request);
        }
    }

    public function suggest($type, $fields)
    {
        $result = false;
        if ($ch = curl_init($this->url . "$type")) {
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Accept: application/json',
                'Authorization: Token ' . $this->token
            ));
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            // json_encode
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
            $result = curl_exec($ch);
//             $result = json_decode($result, true);
            curl_close($ch);
        }
        return $result;
    }

}