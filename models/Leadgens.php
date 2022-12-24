<?php

class Leadgens extends Core
{
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
}
