<?php

class Helpers extends Core
{

    public function get_current_time($timezone,$format)
    {


        $timezone = (int)str_replace('UTC', '', $timezone);

        $currentDate = new DateTime(date($format, time()));

        $currentDate->setTimezone(new DateTimeZone('UTC'));

        if ($timezone > 0) {
            $currentDate->add(new DateInterval('PT' . $timezone . 'H'));
        } else {
            date_sub($currentDate, date_interval_create_from_date_string($timezone * (-1) . ' hours'));
        }

        return $currentDate->format($format);

    }

    public function get_region_code($region)
    {
        $codes = array(
            1 => "адыгея",
            2 => "башкортостан",
            3 => "бурятия",
            4 => "алтай",
            5 => "дагестан",
            6 => "ингушетия",
            7 => "кабардино-балкарская",
            8 => "калмыкия",
            9 => "карачаево-черкесская",
            10 => "карелия",
            11 => "коми",
            12 => "марий эл",
            13 => "мордовия",
            14 => "саха /якутия/",
            15 => "северная осетия - алания",
            16 => "татарстан",
            17 => "тыва",
            18 => "удмуртская",
            19 => "хакасия",
            20 => "чеченская",
            21 => "чувашская",
            22 => "алтайский",
            23 => "краснодарский",
            24 => "красноярский",
            25 => "приморский",
            26 => "ставропольский",
            27 => "хабаровский",
            28 => "амурская",
            29 => "архангельская",
            30 => "астраханская",
            31 => "белгородская",
            32 => "брянская",
            33 => "владимирская",
            34 => "волгоградская",
            35 => "вологодская",
            36 => "воронежская",
            37 => "ивановская",
            38 => "иркутская",
            39 => "калининградская",
            40 => "калужская",
            41 => "камчатский",
            42 => "кемеровская",
            43 => "кировская",
            44 => "костромская",
            45 => "курганская",
            46 => "курская",
            47 => "ленинградская",
            48 => "липецкая",
            49 => "магаданская",
            50 => "московская",
            51 => "мурманская",
            52 => "нижегородская",
            53 => "новгородская",
            54 => "новосибирская",
            55 => "омская",
            56 => "оренбургская",
            57 => "орловская",
            58 => "пензенская",
            59 => "пермский",
            60 => "псковская",
            61 => "ростовская",
            62 => "рязанская",
            63 => "самарская",
            64 => "саратовская",
            65 => "сахалинская",
            66 => "свердловская",
            67 => "смоленская",
            68 => "тамбовская",
            69 => "тверская",
            70 => "томская",
            71 => "тульская",
            72 => "тюменская",
            73 => "ульяновская",
            74 => "челябинская",
            75 => "забайкальский",
            76 => "ярославская",
            77 => "москва",
            78 => "санкт-петербург",
            82 => "крым",
            83 => "ненецкий автономный округ",
            86 => "ханты-мансийский автономный округ - югра",
            87 => "чукотский",
            89 => "ямало-ненецкий",
            92 => "севастополь",
            91 => "республика крым",

        );

        $region = trim(mb_strtolower($region));

        if (($index = array_search($region, $codes)) !== false) {
            return $index;
        }
    }

    public function get_regional_time($region)
    {
        $region_times = array(
            "адыгея" => 0,
            "башкортостан" => 2,
            "бурятия" => 5,
            "алтай" => 4,
            "дагестан" => 0,
            "ингушетия" => 0,
            "кабардино-балкарская" => 0,
            "калмыкия" => 0,
            "карачаево-черкесская" => 0,
            "карелия" => 0,
            "коми" => 0,
            "марий эл" => 0,
            "мордовия" => 0,
            "саха /якутия/" => 6,
            "северная осетия - алания" => 0,
            "татарстан",
            "тыва" => 4,
            "удмуртская" => 1,
            "хакасия" => 4,
            "чеченская",
            "чувашская" => 0,
            "алтайский" => 4,
            "краснодарский" => 0,
            "красноярский" => 4,
            "приморский" => 7,
            "ставропольский",
            "хабаровский" => 7,
            "амурская" => 6,
            "архангельская" => 0,
            "астраханская" => 1,
            "белгородская" => 0,
            "брянская" => 0,
            "владимирская" => 0,
            "волгоградская" => 0,
            "вологодская" => 0,
            "воронежская" => 0,
            "ивановская" => 0,
            "иркутская" => 5,
            "калининградская" => -1,
            "калужская" => 0,
            "камчатский" => 9,
            "кемеровская" => 4,
            "кировская" => 0,
            "костромская" => 0,
            "курганская" => 2,
            "курская" => 0,
            "ленинградская" => 0,
            "липецкая" => 0,
            "магаданская" => 8,
            "московская" => 0,
            "мурманская" => 0,
            "нижегородская" => 0,
            "новгородская" => 0,
            "новосибирская" => 4,
            "омская" => 3,
            "оренбургская" => 2,
            "орловская" => 0,
            "пензенская" => 0,
            "пермский" => 2,
            "псковская" => 0,
            "ростовская" => 0,
            "рязанская" => 0,
            "самарская" => 1,
            "саратовская" => 1,
            "сахалинская" => 8,
            "свердловская" => 2,
            "смоленская" => 0,
            "тамбовская" => 0,
            "тверская" => 0,
            "томская" => 4,
            "тульская" => 0,
            "тюменская" => 2,
            "ульяновская" => 1,
            "челябинская" => 2,
            "забайкальский" => 6,
            "ярославская" => 0,
            "москва" => 0,
            "санкт-петербург" => 0,
            "крым" => 0,
            "ханты-мансийский автономный округ - югра" => 2,
            "чукотский" => 9,
            "ямало-ненецкий" => 2,
            "севастополь" => 0,

        );

        $region = trim(mb_strtolower($region));

        $shift = 0;
        if (isset($region_times[$region]))
            $shift = $region_times[$region];

        return date('Y-m-d H:i:s', time() + $shift * 3600);
    }


    // private $c2o_codes = array(
    //     array('z', 'x', 'c', 'V', 'B', 'N', 'm', 'A', 's', '4'),
    //     array('Q', 'W', 'r', 'S', '6', 'Y', 'k', 'n', 'G', 'i'),
    //     array('T', '2', 'H', 'e', 'D', '1', '8', 'P', 'o', 'g'),
    //     array('O', 'u', 'Z', 'h', '0', 'I', 'J', '7', 'a', 'L'),
    //     array('v', 'w', 'p', 'E', 't', '5', 'b', '9', 'l', 'R'),
    //     array('d', '3', 'q', 'C', 'U', 'M', 'y', 'X', 'K', 'j'),
    // );

    // public function c2o_encode($id)
    // {
    //     $code = '';

    //     $chars = str_split($id);

    //     if (count($chars) != 6)
    //         return false;

    //     $code .= $this->c2o_codes[5][$chars[5]];
    //     $code .= $this->c2o_codes[4][$chars[4]];
    //     $code .= $this->c2o_codes[3][$chars[3]];
    //     $code .= $this->c2o_codes[2][$chars[2]];
    //     $code .= $this->c2o_codes[1][$chars[1]];
    //     $code .= $this->c2o_codes[0][$chars[0]];
    //     return $code;
    // }

    // public function c2o_decode($code)
    // {
    //     $id = '';

    //     $chars = str_split($code);

    //     if (count($chars) != 6)
    //         return false;

    //     $id .= array_search($chars[5], $this->c2o_codes[0]);
    //     $id .= array_search($chars[4], $this->c2o_codes[1]);
    //     $id .= array_search($chars[3], $this->c2o_codes[2]);
    //     $id .= array_search($chars[2], $this->c2o_codes[3]);
    //     $id .= array_search($chars[1], $this->c2o_codes[4]);
    //     $id .= array_search($chars[0], $this->c2o_codes[5]);

    //     return $id;

    // }

    private $c2o_codes = array(
        array('z', 'x', 'c', 'V', 'B', 'N', 'm', 'A', 's', '4'),
        array('Q', 'W', 'r', 'S', '6', 'Y', 'k', 'n', 'G', 'i'),
        array('T', '2', 'H', 'e', 'D', '1', '8', 'P', 'o', 'g'),
        array('O', 'u', 'Z', 'h', '0', 'I', 'J', '7', 'a', 'L'),
        array('v', 'w', 'p', 'E', 't', '5', 'b', '9', 'l', 'R'),
        array('d', '3', 'q', 'C', 'U', 'M', 'y', 'X', 'K', 'j'),        
    );
    
    public function c2o_encode($id)
    {
    	$code = '';
        
        $chars = str_split($id);

        for($i = 0; $i < count($chars); $i++)
            $code .= $this->c2o_codes[$i][$chars[$i]];

        return $code;
    }
    
    public function c2o_decode($code)
    {
        $id = '';

        $chars = str_split($code);

        for ($i = 0; $i < count($chars); $i++)
            $id .= array_search($chars[$i], $this->c2o_codes[$i]);

        return $id;

    }

    public function logging($local_method, $service, $request, $response, $filename, $log_dir)
    {
        $log_filename = $log_dir.$filename;
        
        if (date('d', filemtime($log_filename)) != date('d'))
        {
            $archive_filename = $log_dir.'archive/'.date('ymd', filemtime($log_filename)).'.'.$filename;
            rename($log_filename, $archive_filename);
            file_put_contents($log_filename, "\xEF\xBB\xBF");            
        }

        $str = PHP_EOL.'==================================================================='.PHP_EOL;
        $str .= date('d.m.Y H:i:s').PHP_EOL;
        $str .= $service.PHP_EOL;
        $str .= var_export($request, true).PHP_EOL;
        $str .= var_export($response, true).PHP_EOL;
        $str .= 'END'.PHP_EOL;
        
        file_put_contents($log_filename, $str, FILE_APPEND);
    }

}