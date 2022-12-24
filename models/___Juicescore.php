<?php

class Juicescore extends Core
{
    private $key = '4Tcp0xYulqt78ErhX7jhCllPoHAqL0';
    private $url = 'https://api.juicyscore.com/getscore/';
    
    public function __construct()
    {
    	parent::__construct();

        $this->key = $this->settings->apikeys['juicescore']['api_key'];
    }
    
    
    public function getscore($order_id)
    {
        if (!($order = $this->orders->get_order((int)$order_id)))
            return false;
        
        $email_expls = explode('@', $order->email);
        $prepare_email = substr($email_expls[0], 0, -1);
        
        $params = array(
            'account_id' => 'Boostra_RU',
            'client_id' => $order->user_id,
            'session_id' => $order->juicescore_session_id,
            'channel' => 'SITE',
            'time_utc3' => date('d.m.Y H:i:s', strtotime($order->date)),
            'version' => 12,
            'referrer' => '',
            'tenor' => $order->period,
            'time_local' => '',
            'ip' => $order->ip,
            'useragent' => '',
            'ph_country' => '7',
            'phone' => substr($order->phone_mobile, 1, 6),
            'mail' => $prepare_email,
            'application_id' => $order->order_id,
            'time_zone' => '',
            'amount' => $order->amount,
            'mac_address' => '',
            'deviceid' => '',
            'zip_billing' => '',
            'country_code_billing' => 'RU',
            'zip_shipping' => '',
            'country_code_shipping' => 'RU',
            'card_number' => '',
            'card_expiration_date' => '',
            'response_content_type' => 'json',
        );
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($params);echo '</pre><hr />';        
//exit;
        $url = $this->url.'?'.http_build_query($params);
        
        $headers = array(
            'session: '.$this->key
        );
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        
        $res = curl_exec($ch);
        
        curl_close($ch);
        
        return $res;
        
    }
    
    public function test()
    {
        
        $url = 'https://api.juicyscore.com/getscore/?
            account_id=Boostra_RU
            &application_id=123
            &client_id=124
            &channel=SITE
            &is_js=1
            &ip=54.38.34.205
            &useragent=Mozilla%2F5.0+%28Linux%3B+U%3B+Android+4.1.2%3B+en-US%3B+HUAWEI+P2-6011+Build%2FHuaweiP2-6011%29+AppleWebKit%2F534.30+%28KHTML%2C+like+Gecko%29+Version%2F4.0+UCBrowser%2F10.6.2.599+U3%2F0.8.0+Mobile+Safari%2F534.30
            &time_zone=6
            &time_local=15.10.2019+16:34:36
            &time_utc3=15.10.2019+16:34:36
            &ph_country=7
            &phone=961235
            &mail=asdfadad1234
            &referrer=http://cityadspix.com/click-FQDA856V-KHEQBB2A?bt=25
            &tl=1
            &sa=xtd4
            &sa2=tb.znvy.eh/frnepu?se=nzvtb
            &se2=dhrel
            &d=%Q0%O1%Q1%8O%Q1%81%Q1%80%Q0%OR+%Q0%O4%Q0%O5%Q0%OQ%Q1%8P%Q0%O3%Q0%O8+%Q0%OS%Q0%OR+%Q1%81%Q0%O8%Q1%81%Q1%82%Q0%O5%Q0%OP%Q0%O5+%Q1%81%Q0%OR%Q0%OQ%Q1%82%Q0%O0%Q0%ON%Q1%82&tenor=333&amount=250000
            &response_content_type=json
            &card_number=123456XXXX1234
            &card_expiration_date=11%2F19
            &session_id=w.20200819075042ae820a68-e1f0-11ea-bcce-e236344c85d1
            &zip_billing=123456
            &country_code_billing=RU
            &version=12
        ';
"
$endpoint = 'http://example.com/endpoint';
$params = array('foo' => 'bar');
$url = $endpoint . '?' . http_build_query($params);
curl_setopt($ch, CURLOPT_URL, $url);
";
        
        $headers = array(
            'session: '.$this->key
        );
        
        $url = 'https://api.juicyscore.com/getscore';
        
        $params = array(
        
        );
        
        $url .= '?' . http_build_query($params);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        
        $res = curl_exec($ch);
        
        curl_close($ch);
        
echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($res);echo '</pre><hr />';
    }
}