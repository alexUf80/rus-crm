<?php

class Fns extends Core
{
    private $url = "https://service.nalog.ru/inn-proc.do";
    
    function get_inn($surname, $name, $patronymic, $birthdate, $doctype, $docnumber, $docdate)
    {

        $data = array(
            "fam" => $surname,
            "nam" => $name,
            "otch" => $patronymic,
            "bdate" => $birthdate,
            "bplace" => "",
            "doctype" => $doctype,
            "docno" => $docnumber,
            "docdt" => $docdate,
            "c" => "innMy",
            "captcha" => "",
            "captchaToken" => ""
        );
        $options = array(
            'http' => array(
                'method'  => 'POST',
                'header'  => array(
                    'Content-type: application/x-www-form-urlencoded',
                ),
                'content' => http_build_query($data)
            ),
        );
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($data);echo '</pre><hr />';
        $context = stream_context_create($options);
        $result = file_get_contents($this->url, false, $context);
    
        return json_decode($result);
    }
}