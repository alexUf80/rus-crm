<?php


class Zvonobot extends Core
{
    private $apiKey;
    private $outgoingPhone;
    private $yuk_apiKey;
    private $yuk_outgoingPhone;

    public function __construct()
    {
        parent::__construct();

        $this->apiKey = $this->settings->apikeys['zvonobot']['apiKey'];
        $this->outgoingPhone = $this->settings->apikeys['zvonobot']['outgoingPhone'];
    }

    public function get_outgoing_phone($yuk)
    {
        return empty($yuk) ? $this->outgoingPhone : $this->yuk_outgoingPhone;
    }


    public function create_record($name, $text, $yuk = 0)
    {
        if (empty($yuk))
            $apiKey = $this->apiKey;
        else
            $apiKey = $this->yuk_apiKey;

        if ($curl = curl_init()) {
            $json = '{
                  "apiKey": "' . $apiKey . '",
                  "source": "text",
                  "text": "' . $text . '",
                  "name": "' . $name . '"
                }';

            curl_setopt($curl, CURLOPT_URL, 'https://lk.zvonobot.ru/apiCalls/createRecord');
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $json);

            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'accept: application/json'));
            $out = curl_exec($curl);

            curl_close($curl);
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($out);echo '</pre><hr />';
            return json_decode($out, true);
        }
    }

    public function call($phone, $record_id, $yuk = 0)
    {
        if (empty($record_id))
            return false;

        if ($curl = curl_init()) {
            $json = '{
                  "apiKey": "' . (empty($yuk) ? $this->apiKey : $this->yuk_apiKey) . '",
                  "phone": "' . $phone . '",
                  "outgoingPhone": "' . (empty($yuk) ? $this->outgoingPhone : $this->yuk_outgoingPhone) . '",
                  "record": {
                    "id": "' . $record_id . '"
         },
                  "record.gender" : "1"
                }';

            curl_setopt($curl, CURLOPT_URL, 'https://lk.zvonobot.ru/apiCalls/create');
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $json);

            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'accept: application/json'));
            $out = curl_exec($curl);

            curl_close($curl);
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($json);echo '</pre><hr />';
            $out = json_decode($out, true);

            $this->soap1c->logging(__METHOD__, 'https://lk.zvonobot.ru/apiCalls/create', $json, $out, 'zvonobot.txt');

            return $out;
        }
    }

    public function check($id, $yuk = 0)
    {
        $requestArray = array(
            'apiKey' => empty($yuk) ? $this->apiKey : $this->yuk_apiKey
        );

        $json = json_encode($requestArray);

        if ($curl = curl_init()) {
            curl_setopt($curl, CURLOPT_URL, 'https://lk.zvonobot.ru/apiCalls/get?apiCallIdList[]=' . $id);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $json);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'accept: application/json'));
            curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
            $out = curl_exec($curl);

            curl_close($curl);

            return json_decode($out, true);
        }
    }


    public function get_zvonobot($id)
    {
        $query = $this->db->placehold("
            SELECT * 
            FROM __zvonobots
            WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
        $result = $this->db->result();

        return $result;
    }

    public function get_zvonobots($filter = array())
    {
        $id_filter = '';
        $contract_id_filter = '';
        $status_filter = '';
        $create_date_filter = '';
        $keyword_filter = '';
        $limit = 1000;
        $page = 1;
        $sort = 'z.id ASC';

        if (!empty($filter['sort'])) {
            switch ($filter['sort']):

                case 'date_desc':
                    $sort = 'z.create_date DESC';
                    break;

                case 'date_asc':
                    $sort = 'z.create_date ASC';
                    break;

                case '':
                    $sort = '';
                    break;

                case '':
                    $sort = '';
                    break;

            endswitch;
        }

        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND z.id IN (?@)", array_map('intval', (array)$filter['id']));

        if (!empty($filter['contract_id']))
            $contract_id_filter = $this->db->placehold("AND z.contract_id IN (?@)", array_map('intval', (array)$filter['contract_id']));

        if (!empty($filter['status']))
            $status_filter = $this->db->placehold("AND z.status IN (?@)", array_map('strval', (array)$filter['status']));

        if (!empty($filter['create_date']))
            $create_date_filter = $this->db->placehold("AND DATE(z.create_date) = ?", $filter['create_date']);

        if (isset($filter['keyword'])) {
            $keywords = explode(' ', $filter['keyword']);
            foreach ($keywords as $keyword)
                $keyword_filter .= $this->db->placehold('AND (z.name LIKE "%' . $this->db->escape(trim($keyword)) . '%" )');
        }

        if (isset($filter['limit']))
            $limit = max(1, intval($filter['limit']));

        if (isset($filter['page']))
            $page = max(1, intval($filter['page']));

        $sql_limit = $this->db->placehold(' LIMIT ?, ? ', ($page - 1) * $limit, $limit);

        $query = $this->db->placehold("
            SELECT * 
            FROM __zvonobots AS z
            WHERE 1
                $id_filter
                $contract_id_filter
				$create_date_filter
                $status_filter
                $keyword_filter
            ORDER BY $sort
            $sql_limit
        ");
        $this->db->query($query);
        $results = $this->db->results();

        return $results;
    }

    public function count_zvonobots($filter = array())
    {
        $id_filter = '';
        $status_filter = '';
        $contract_id_filter = '';
        $keyword_filter = '';

        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array)$filter['id']));

        if (!empty($filter['contract_id']))
            $contract_id_filter = $this->db->placehold("AND contract_id IN (?@)", array_map('intval', (array)$filter['contract_id']));

        if (!empty($filter['status']))
            $status_filter = $this->db->placehold("AND status IN (?@)", array_map('strval', (array)$filter['status']));

        if (isset($filter['keyword'])) {
            $keywords = explode(' ', $filter['keyword']);
            foreach ($keywords as $keyword)
                $keyword_filter .= $this->db->placehold('AND (name LIKE "%' . $this->db->escape(trim($keyword)) . '%" )');
        }

        $query = $this->db->placehold("
            SELECT COUNT(id) AS count
            FROM __zvonobots
            WHERE 1
                $id_filter
                $contract_id_filter
                $status_filter
                $keyword_filter
        ");
        $this->db->query($query);
        $count = $this->db->result('count');

        return $count;
    }

    public function add_zvonobot($zvonobot)
    {
        $query = $this->db->placehold("
            INSERT INTO __zvonobots SET ?%
        ", (array)$zvonobot);
        $this->db->query($query);
        $id = $this->db->insert_id();

        return $id;
    }

    public function update_zvonobot($id, $zvonobot)
    {
        $query = $this->db->placehold("
            UPDATE __zvonobots SET ?% WHERE id = ?
        ", (array)$zvonobot, (int)$id);
        $this->db->query($query);

        return $id;
    }

    public function delete_zvonobot($id)
    {
        $query = $this->db->placehold("
            DELETE FROM __zvonobots WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
    }
}