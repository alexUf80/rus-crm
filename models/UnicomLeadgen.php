<?php

class UnicomLeadgen extends Core
{
    public function send_approve_postback($order_id, $order)
    {
        $base_link = 'https://unicom24.ru/offer/postback/'.$order->click_hash.'/';
        $status = '?status=accept';
        $external_id = '&external_id='.$order_id;
        $link_lead = $base_link . $status . $external_id;

        $ch = curl_init($link_lead);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        $res = curl_exec($ch);
        curl_close($ch);

        $this->orders->update_order($order_id, array('leadcraft_postback_date' => date('Y-m-d H:i'), 'leadcraft_postback_type' => 'approved'));

        return $res;
    }
    public function send_cancell_postback($order_id, $order)
    {
        $base_link = 'https://unicom24.ru/offer/postback/'.$order->click_hash.'/';
        $status = '?status=reject';
        $external_id = '&external_id='.$order_id;
        $link_lead = $base_link . $status . $external_id;

        $ch = curl_init($link_lead);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        $res = curl_exec($ch);
        curl_close($ch);

        $this->orders->update_order($order_id, array('leadcraft_postback_date' => date('Y-m-d H:i'), 'leadcraft_postback_type' => 'cancelled'));

        return $res;
    }
}