<?php

class Leadgens extends Core
{
    
    public function send_postback($postback)
    {
        $methodname = 'Send'.ucfirst($postback->lead_name).'Action';
        
        if (method_exists($this, $methodname))
        {
            $this->$methodname($postback);
        }
        else
        {
            $this->update_postback($postback->id, [
                'sent_status' => 3,
                'sent_date' => date('Y-m-d H:i:s'),
            ]);
        }

    }
    
    /**
     * Leadgens::SendClick2moneyAction()
     * 
Поступление лида
https://c2mpbtrck.com/cpaCallback?cid={CID}&partner=[рекламодатель]&action=hold&lead_id={lead_id}
	Принято 		   
https://c2mpbtrck.com/cpaCallback?cid={CID}&partner=[рекламодатель]&action=approve&lead_id={lead_id}&payout={payout}
	Отклонено 		   
https://c2mpbtrck.com/cpaCallback?cid={CID}&partner=[рекламодатель]&action=reject&lead_id={lead_id}
Повторная выдача       
https://c2mpbtrck.com/cpaCallback?cid={CID}&partner=[рекламодатель]&action=secondary& lead_id={lead_id}&payout={payout}

     * @param mixed $postback
     * @return void
     */
    private function SendClick2moneyAction($postback)
    {
        $link = 'https://c2mpbtrck.com/cpaCallback';
        
        $build_query = [
            'cid' => $postback->click_hash,
            'lead_id' => $postback->webmaster,
        ];

        switch ($postback->type):
            case 'pending':
                $build_query['action'] = 'approve';
                break;
//            case 'approve':
//                $build_query['action'] = 'approve';
//                break;
//            case 'reject':
//                $build_query['action'] = 'reject';
//                break;
        endswitch;

        if (!empty($build_query))
        {
            $link_lead = $link.'?'.http_build_query($build_query);
            
            $ch = curl_init($link_lead);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            $res = curl_exec($ch);
            curl_close($ch);
    echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($link_lead, $res);echo '</pre><hr />';        
            $this->update_postback($postback->id, [
                'sent_status' => 2,
                'sent_date' => date('Y-m-d H:i:s'),
                'link' => $link_lead,
                'response' => serialize($res),
            ]);
        }
    }
    
    public function send_approved_postback_click2money($order_id, $order)
    {
        $base_link = 'https://c2mpbtrck.com/cpaCallback';
        $link_lead = $base_link . '?cid=' . $order->click_hash . '&action=approve&partner=ecozaym&lead_id=' . $order_id;

        $ch = curl_init($link_lead);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        $res = curl_exec($ch);
        curl_close($ch);

        $this->orders->update_order($order_id, array('leadcraft_postback_date' => date('Y-m-d H:i'), 'leadcraft_postback_type' => 'approved'));

        return $res;
    }

    public function send_cancelled_postback_click2money($order_id, $order)
    {
        $base_link = 'https://c2mpbtrck.com/cpaCallback';
        $link_lead = $base_link . '?cid=' . $order->click_hash . '&action=reject&partner=ecozaym&lead_id=' . $order_id;

        $ch = curl_init($link_lead);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        $res = curl_exec($ch);
        curl_close($ch);

        $this->orders->update_order($order_id, array('leadcraft_postback_date' => date('Y-m-d H:i'), 'leadcraft_postback_type' => 'cancelled'));

        return $res;
    }





	public function get_postback($id)
	{
		$query = $this->db->placehold("
            SELECT * 
            FROM s_leadgen_postbacks
            WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
        $result = $this->db->result();
	
        return $result;
    }
    
	public function get_postbacks($filter = array())
	{
		$id_filter = '';
        $sent_status = '';
        $keyword_filter = '';
        $limit = 1000;
		$page = 1;
        
        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array)$filter['id']));
        
        if (isset($filter['sent_status']))
            $sent_status = $this->db->placehold("AND sent_status = ?", (int)$filter['sent_status']);
        
		if(isset($filter['keyword']))
		{
			$keywords = explode(' ', $filter['keyword']);
			foreach($keywords as $keyword)
				$keyword_filter .= $this->db->placehold('AND (name LIKE "%'.$this->db->escape(trim($keyword)).'%" )');
		}
        
		if(isset($filter['limit']))
			$limit = max(1, intval($filter['limit']));

		if(isset($filter['page']))
			$page = max(1, intval($filter['page']));
            
        $sql_limit = $this->db->placehold(' LIMIT ?, ? ', ($page-1)*$limit, $limit);

        $query = $this->db->placehold("
            SELECT * 
            FROM s_leadgen_postbacks
            WHERE 1
                $id_filter
				$keyword_filter
                $sent_status
            ORDER BY id DESC 
            $sql_limit
        ");
        $this->db->query($query);
        $results = $this->db->results();
        
        return $results;
	}
    
	public function count_postbacks($filter = array())
	{
        $id_filter = '';
        $sent_status = '';
        $keyword_filter = '';
        
        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array)$filter['id']));
		
        if (isset($filter['sent_status']))
            $sent_status = $this->db->placehold("AND sent_status = ?", (int)$filter['sent_status']);
        
        if(isset($filter['keyword']))
		{
			$keywords = explode(' ', $filter['keyword']);
			foreach($keywords as $keyword)
				$keyword_filter .= $this->db->placehold('AND (name LIKE "%'.$this->db->escape(trim($keyword)).'%" )');
		}
                
		$query = $this->db->placehold("
            SELECT COUNT(id) AS count
            FROM s_leadgen_postbacks
            WHERE 1
                $id_filter
                $sent_status
                $keyword_filter
        ");
        $this->db->query($query);
        $count = $this->db->result('count');
	
        return $count;
    }
    
    public function add_postback($postback)
    {
		$query = $this->db->placehold("
            INSERT INTO s_leadgen_postbacks SET ?%
        ", (array)$postback);
        $this->db->query($query);
        $id = $this->db->insert_id();
        
        return $id;
    }
    
    public function update_postback($id, $postback)
    {
		$query = $this->db->placehold("
            UPDATE s_leadgen_postbacks SET ?% WHERE id = ?
        ", (array)$postback, (int)$id);
        $this->db->query($query);
        
        return $id;
    }
    
    public function delete_postback($id)
    {
		$query = $this->db->placehold("
            DELETE FROM s_leadgen_postbacks WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
    }

    
    public function sendRejectToAlians($orderId)
    {
    
        $order = $this->orders->get_order($orderId);
        $address = $this->addresses->get_address($order->faktaddress_id);
    
        $address_locacity = '';
        if($address->city){
            $address_locacity = $address->city;
        }
        else{
            $address_locacity = $address->locality_type . '.' . $address->locality;
        }
    
        $link = "https://alliancecpa.ru/api/contacts?phone=$order->phone_mobile&token=0114c21795eea60f82ae23444cfb0807&email=$order->email&name=$order->firstname&surname=$order->lastname&patronymic=$order->patronymic&date_birthday=$order->birth&geo=$address_locacity";

        $ch = curl_init($link);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_exec($ch);
        curl_close($ch);
    
        return 1;
    }

    public function sendApiVitkol($order_id)
    {

        $order = $this->orders->get_order($order_id);
        $user = $this->users->get_user($order->user_id);

        $data = [
            "external_id" => strval($order_id), 
            "first_name" => $user->firstname,
            "last_name" => $user->lastname, 
            "father_name" => $user->patronymic,
            "phone" => $user->phone_mobile, 
            "datetime" => $order->date,
            "refusal_datetime" => $order->reject_date, 
            "amount" => $order->amount,
            "company" => "rzs"
        ];

        $data_string = json_encode ($data, JSON_UNESCAPED_UNICODE);
        $curl = curl_init('http://51.250.86.237/leads');
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
        'accept: application/json',
        'token: f83e10123a6f5f4beacbde8a5076ebbcb5c8e67bb655d2f01836d',
        'Content-Type: application/json',
        'Content-Length: ' . strlen($data_string),
        ]);
        $result = curl_exec($curl);
        curl_close($curl);
        // echo '<pre>';
        return($result);
    }

}

