<?php

class Telegram extends Core
{
	public function send_message($token, $chat_id, $text)
	{
		$getQuery = array(
            "chat_id" 	=> $chat_id,
            "text"  	=> $text,
            "parse_mode" => "html",
        );
        $ch = curl_init("https://api.telegram.org/bot". $token ."/sendMessage?" . http_build_query($getQuery));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);

        $resultQuery = curl_exec($ch);
        curl_close($ch);

        echo $resultQuery;
    }
    
}