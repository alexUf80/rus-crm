<?php

class Mango extends Core
{
    private $api_key = 'h3m2k6wya0r9caehvgqn8vmb35xkqmbx';
    private $api_salt = 'fnqqkcel3vl6wqehxvuvg7x92kkjwl72';
    private $line_number = '78003332484';
    private $url = 'https://app.mango-office.ru/vpbx/commands/callback';
    
    private $yuk_api_key = 'h1mclkn054ez5o5ju0jtkthttjl00vb7';
    private $yuk_api_salt = 'mo9yynothsjd7ens4wbffjzzsapiwrf8';
    private $yuk_line_number = '79585382125';
    private $yuk_url = 'https://app.mango-office.ru/vpbx/commands/callback';
    
    
    public function __construct()
    {
        parent::__construct();
        
        $this->api_key = $this->settings->apikeys['mango']['api_key'];
        $this->api_salt = $this->settings->apikeys['mango']['api_salt'];
//        $this->line_number = $this->settings->apikeys['mango']['line_number'];

//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($this->api_key, $this->api_salt);echo '</pre><hr />';
//exit;
    }
    

    public function call_boostra($phone, $mango_number, $params = array()) {
        $url = 'https://app.mango-office.ru/vpbx/commands/callback';

        $mangocall_id = $this->add_call(array(
            'manager_id' => empty($params['manager_id']) ? 0 : $params['manager_id'],
            'order_id' => empty($params['order_id']) ? 0 : $params['order_id'],
            'user_id' => empty($params['user_id']) ? 0 : $params['user_id'],
            'created' => date('Y-m-d H:i:s'),
        ));

        $data = array(
            "command_id" => "ID_" . $mangocall_id,
            "from" => array(
                "extension" => $mango_number, // внутренний номер, за счет которого производится звонок. (например 101)            
            //"number" => "user1@vpbx400215406.mangosip.ru" // <- кто звонит (можно SIP)
            //"number" => "74953748405" // <- (можно номер)            
            ),
            "to_number" => $phone, // <- кому звонит
            "line_number" => $this->line_number // <- какой АОН 
        );
        $json = json_encode($data);
        $sign = hash('sha256', $this->api_key . $json . $this->api_salt);
        $postdata = array(
            'vpbx_api_key' => $this->api_key,
            'sign' => $sign,
            'json' => $json
        );

        $response = $this->send($url, $postdata);
        return $response;
    }

    public function get_history($phone) {
        $now = time() - 3600;
        $from = $now - 86400 * 30;

        $data = array(
            "date_from" => $from,
            "date_to" => $now,
            "call_party" => array(
                "number" => $phone
            ),
            "fields" => "records"
        );

        $json = json_encode($data);
        $sign = hash('sha256', $this->api_key . $json . $this->api_salt);
        $postdata = array(
            'vpbx_api_key' => $this->api_key,
            'sign' => $sign,
            'json' => $json
        );
        $response = $this->send('https://app.mango-office.ru/vpbx/stats/request', $postdata);
        sleep(1);
        $sign_result = hash('sha256', $this->api_key . $response . $this->api_salt);
        $post_data_result = array(
            'vpbx_api_key' => $this->api_key,
            'sign' => $sign_result,
            'json' => $response
        );
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($post_data_result, $response);echo '</pre><hr />';        
        $history_data = $this->send('https://app.mango-office.ru/vpbx/stats/result', $post_data_result);

        return $history_data;
    }

    public function get_record_link($record_id) {
        $data = array(
            "recording_id" => $record_id, // <- идентификатор записи (можно взять из уведомления о записи или из статистики вызовов)
            "action" => "download" // <- скачать ("play" - проиграть)
        );
        $json = json_encode($data);
        $sign = hash('sha256', $this->api_key . $json . $this->api_salt);
        $postdata = array(
            'vpbx_api_key' => $this->api_key,
            'sign' => $sign,
            'json' => $json
        );

        $response = $this->send('https://app.mango-office.ru/vpbx/queries/recording/post/', $postdata, 1);
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($response);echo '</pre><hr />';        
        $part_link = trim(substr($response, strripos($response, 'location') + 9));
        $expl = explode("\n", $part_link);
        $link = array_shift($expl);
        return trim($link); // вывести ссылку на mp3 
    }

    private function send($url, $postdata, $record = 0) {
        $post = http_build_query($postdata);
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        if ($record) {
            curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        }
        $response = curl_exec($ch);

        curl_close($ch);

        return $response;
    }




    public function call($phone, $mango_number, $yuk = false)
    {
        $data = array(
            "command_id" => "ID" . rand(10000000,99999999),
            "from" => array(
            "extension" => $mango_number, // внутренний номер, за счет которого производится звонок. (например 101)
            
            ) ,
            "to_number" => $phone, // <- кому звонит
            "line_number" => empty($yuk) ? $this->line_number : $this->yuk_line_number // <- какой АОН 
        );

//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($this->api_key, $this->api_salt);echo '</pre><hr />';
        $json = json_encode($data);
        $sign = hash('sha256', (empty($yuk) ? $this->api_key : $this->yuk_api_key) . $json . (empty($yuk) ? $this->api_salt : $this->yuk_api_salt));
        $postdata = array(
            'vpbx_api_key' => empty($yuk) ? $this->api_key : $this->yuk_api_key,
            'sign' => $sign,
            'json' => $json
        );
        $post = http_build_query($postdata);
        $ch = curl_init($this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

        $response = curl_exec($ch);
        curl_close($ch);
        
        return $response; 
    }





    public function get_call_id($entry_id) {
        $query = $this->db->placehold("
            SELECT id
            FROM __mangocalls
            WHERE entry_id = ?
        ", (string) $entry_id);
        $this->db->query($query);

        return $this->db->result('id');
    }

    public function get_call($id) {
        $query = $this->db->placehold("
            SELECT * 
            FROM __mangocalls
            WHERE id = ?
        ", (int) $id);
        $this->db->query($query);
        $result = $this->db->result();

        return $result;
    }

    public function get_calls($filter = array()) {
        $id_filter = '';
        $phone_filter = '';
        $manager_id_filter = '';
        $user_id_filter = '';
        $limit = 1000;
        $page = 1;

        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array) $filter['id']));

        if (!empty($filter['manager_id']))
            $manager_id_filter = $this->db->placehold("AND manager_id IN (?@)", array_map('intval', (array) $filter['manager_id']));
        
        if (!empty($filter['user_id']))
            $user_id_filter = $this->db->placehold("AND user_id IN (?@)", array_map('intval', $filter['user_id']));

        if (!empty($filter['phone'])) {
            $phone = str_replace(array('-', ' ', '(', ')', '+'), '', $filter['phone']);
            $phone_filter = $this->db->placehold("AND (from_number = ? OR to_number = ?)", $phone, $phone);
        }

        if (isset($filter['limit']))
            $limit = max(1, intval($filter['limit']));

        if (isset($filter['page']))
            $page = max(1, intval($filter['page']));

        $sql_limit = $this->db->placehold(' LIMIT ?, ? ', ($page - 1) * $limit, $limit);

        $query = $this->db->placehold("
            SELECT * 
            FROM __mangocalls
            WHERE 1
                $id_filter
                $manager_id_filter
                $phone_filter
                $user_id_filter
            ORDER BY id ASC 
            $sql_limit
        ");
        
        $this->db->query($query);
        $results = $this->db->results();

        return $results;
    }

    public function count_calls($filter = array()) {
        $id_filter = '';
        $phone_filter = '';
        $manager_id_filter = '';

        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array) $filter['id']));

        if (!empty($filter['manager_id']))
            $manager_id_filter = $this->db->placehold("AND manager_id IN (?@)", array_map('intval', (array) $filter['manager_id']));

        if (!empty($filter['user_id']))
            $user_id_filter = $this->db->placehold("AND user_id IN (?@)", array_map('intval', (array) $filter['user_id']));

        if (!empty($filter['phone'])) {
            $phone = str_replace(array('-', ' ', '(', ')', '+'), '', $filter['phone']);
            $phone_filter = $this->db->placehold("AND (from_number = ? OR to_number = ?)", $phone, $phone);
        }

        $query = $this->db->placehold("
            SELECT COUNT(id) AS count
            FROM __mangocalls
            WHERE 1
                $id_filter
                $phone_filter 
                $manager_id_filter
                $user_id_filter
        ");
        $this->db->query($query);
        $count = $this->db->result('count');

        return $count;
    }

    public function add_call($call) {
        $call = (array) $call;

        if (empty($call['created']))
            $call['created'] = date('Y-m-d H:i:s');

        $query = $this->db->placehold("
            INSERT INTO __mangocalls SET ?%
        ", $call);
        $this->db->query($query);
        $id = $this->db->insert_id();
        return $id;
    }

    public function update_call($id, $call) {
        $query = $this->db->placehold("
            UPDATE __mangocalls SET ?% WHERE id = ?
        ", (array) $call, (int) $id);
        $this->db->query($query);
        return $id;
    }

}