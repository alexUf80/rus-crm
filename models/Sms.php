<?php

class Sms extends Core
{
    private $login;
    private $password;
    private $originator;
    private $connect_id;

    private $template_types = array(
        'collection' => 'Коллекшин',
        'order' => 'Выдача',
        'sms_sales' => 'Продажа по смс'
    );

    public function __construct()
    {
        parent::__construct();

        $this->login = 'em@eco-zaim.ru';
        $this->password = 'Pass1029';

        $this->yuk_login = 'mkk.po@yandex.ru';
        $this->yuk_password = 'Pass1029';
        $this->yuk_originator = 'jurcompany1';
        $this->yuk_connect_id = '2681';

    }

    public function clear_phone($phone)
    {
        $remove_symbols = array(
            '(',
            ')',
            '-',
            ' ',
            '+'
        );
        return str_replace($remove_symbols, '', $phone);
    }


    public function send($phone, $message)
    {
        $phone = $this->clear_phone($phone);

        $res = $this->send_smsc($phone, $message);


        $sms_message = array(
            'user_id' =>  $this->request->post('user_id', 'integer'),
            'order_id' => $this->request->post('order_id', 'integer'),
            'code' => '',
            'phone' => $phone,
            'ip' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '',
            'created' => date('Y-m-d H:i:s'),
            'message' => $message,
            'response' => $res['resp'],
        );

        $this->add_message($sms_message);

        return $res;

    }


    public function send_easysms($phone, $message)
    {
        $params = http_build_query(array(
            'login' => $this->login,
            'password' => $this->password,
            'text' => $message,
            'phone' => $phone,
            'originator' => $this->originator
        ));

        $url = 'https://xml.smstec.ru/api/v1/easysms/' . $this->connect_id . '/send_sms?' . $params;

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

        $resp = curl_exec($ch);
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($resp, $url);echo '</pre><hr />';    

        curl_close($ch);

        return $resp;
    }

    public function send_smsc($phone, $message)
    {
        $login = 'BF02092021';
        $password = '82e63217cdda82fae80ec99e231b47967e99e2fc';


        $url = 'http://smsc.ru/sys/send.php?login=' . $login . '&psw=' . $password . '&phones=' . $phone . '&mes=' . $message . '';

        $resp = file_get_contents($url);

        return array('url' => $url, 'resp' => $resp);
    }


    public function get_code($phone)
    {
        $query = $this->db->placehold("
            SELECT code
            FROM __sms_messages
            WHERE phone = ?
            ORDER BY id DESC
            LIMIT 1
        ", $this->clear_phone($phone));
        $this->db->query($query);

        $code = $this->db->result('code');

        return $code;
    }

    public function get_message($id)
    {
        $query = $this->db->placehold("
            SELECT *
            FROM __sms_messages
            WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
        $result = $this->db->result();

        return $result;
    }

    public function get_messages($filter = array())
    {
        $id_filter = '';
        $keyword_filter = '';
        $phone_filter = '';
        $limit = 1000;
        $page = 1;

        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array)$filter['id']));

        if (!empty($filter['phone']))
            $phone_filter = $this->db->placehold("AND phone = ?", $this->clear_phone($filter['phone']));

        if (isset($filter['keyword'])) {
            $keywords = explode(' ', $filter['keyword']);
            foreach ($keywords as $keyword)
                $keyword_filter .= $this->db->placehold('AND (name LIKE "%' . $this->db->escape(trim($keyword)) . '%" )');
        }

        if (isset($filter['limit']))
            $limit = max(1, intval($filter['limit']));

        if (isset($filter['page']))
            $page = max(1, intval($filter['page']));

        $sql_limit = $this->db->placehold(' LIMIT ?, ? ', ($page - 1) * $limit, $limit);

        $query = $this->db->placehold("
            SELECT *
            FROM __sms_messages
            WHERE 1
                $id_filter
                $phone_filter
				$keyword_filter
            ORDER BY id DESC
            $sql_limit
        ");
        $this->db->query($query);
        $results = $this->db->results();

        return $results;
    }

    public function count_messages($filter = array())
    {
        $id_filter = '';
        $phone_filter = '';
        $keyword_filter = '';

        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array)$filter['id']));

        if (!empty($filter['phone']))
            $phone_filter = $this->db->placehold("AND phone = ?", $this->clear_phone($filter['phone']));

        if (isset($filter['keyword'])) {
            $keywords = explode(' ', $filter['keyword']);
            foreach ($keywords as $keyword)
                $keyword_filter .= $this->db->placehold('AND (name LIKE "%' . $this->db->escape(trim($keyword)) . '%" )');
        }

        $query = $this->db->placehold("
            SELECT COUNT(id) AS count
            FROM __sms_messages
            WHERE 1
                $id_filter
                $phone_filter
                $keyword_filter
        ");
        $this->db->query($query);
        $count = $this->db->result('count');

        return $count;
    }

    public function add_message($message)
    {
        $message = (array)$message;

        if (isset($message['phone']))
            $message['phone'] = $this->clear_phone($message['phone']);

        $query = $this->db->placehold("
            INSERT INTO __sms_messages SET ?%
        ", $message);
        $this->db->query($query);
        $id = $this->db->insert_id();

        return $id;
    }

    public function update_message($id, $message)
    {
        $message = (array)$message;

        if (isset($message['phone']))
            $message['phone'] = $this->clear_phone($message['phone']);

        $query = $this->db->placehold("
            UPDATE __sms_messages SET ?% WHERE id = ?
        ", $message, (int)$id);
        $this->db->query($query);

        return $id;
    }

    public function delete_message($id)
    {
        $query = $this->db->placehold("
            DELETE FROM __sms_messages WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
    }


    public function get_template_types()
    {
        return $this->template_types;
    }

    public function get_template($id)
    {
        $query = $this->db->placehold("
            SELECT *
            FROM __sms_templates
            WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
        $result = $this->db->result();

        return $result;
    }

    public function get_templates($filter = array())
    {
        $id_filter = '';
        $type_filter = '';
        $keyword_filter = '';
        $limit = 1000;
        $page = 1;

        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array)$filter['id']));

        if (!empty($filter['type']))
            $type_filter = $this->db->placehold("AND type = ?", (string)$filter['type']);

        if (isset($filter['keyword'])) {
            $keywords = explode(' ', $filter['keyword']);
            foreach ($keywords as $keyword)
                $keyword_filter .= $this->db->placehold('AND (name LIKE "%' . $this->db->escape(trim($keyword)) . '%" )');
        }

        if (isset($filter['limit']))
            $limit = max(1, intval($filter['limit']));

        if (isset($filter['page']))
            $page = max(1, intval($filter['page']));

        $sql_limit = $this->db->placehold(' LIMIT ?, ? ', ($page - 1) * $limit, $limit);

        $query = $this->db->placehold("
            SELECT *
            FROM __sms_templates
            WHERE 1
                $id_filter
                $type_filter
				$keyword_filter
            ORDER BY id DESC
            $sql_limit
        ");
        $this->db->query($query);
        $results = $this->db->results();

        return $results;
    }

    public function count_templates($filter = array())
    {
        $id_filter = '';
        $type_filter = '';
        $keyword_filter = '';

        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array)$filter['id']));

        if (!empty($filter['type']))
            $type_filter = $this->db->placehold("AND type = ?", (string)$filter['type']);

        if (isset($filter['keyword'])) {
            $keywords = explode(' ', $filter['keyword']);
            foreach ($keywords as $keyword)
                $keyword_filter .= $this->db->placehold('AND (name LIKE "%' . $this->db->escape(trim($keyword)) . '%" )');
        }

        $query = $this->db->placehold("
            SELECT COUNT(id) AS count
            FROM __sms_templates
            WHERE 1
                $id_filter
                $type_filter
                $keyword_filter
        ");
        $this->db->query($query);
        $count = $this->db->result('count');

        return $count;
    }

    public function add_template($sms_template)
    {
        $query = $this->db->placehold("
            INSERT INTO __sms_templates SET ?%
        ", (array)$sms_template);
        $this->db->query($query);
        $id = $this->db->insert_id();

        return $id;
    }

    public function update_template($id, $sms_template)
    {
        $query = $this->db->placehold("
            UPDATE __sms_templates SET ?% WHERE id = ?
        ", (array)$sms_template, (int)$id);
        $this->db->query($query);

        return $id;
    }

    public function delete_template($id)
    {
        $query = $this->db->placehold("
            DELETE FROM __sms_templates WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
    }
}
