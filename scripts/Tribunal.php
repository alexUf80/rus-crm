<?php

error_reporting(-1);
ini_set('display_errors', 'On');

chdir('..');

require 'autoload.php';
require_once "PHPExcel/Classes/PHPExcel.php";

$response = array();

class TribunalScript extends Core
{
    private $xlsx_filename;
    private $csv_filename;
    
    public $codes = array(
        22 => "алтайский",
        28 => "амурская",
        29 => "архангельская",
        30 => "астраханская",
        31 => "белгородская",
        32 => "брянская",
        33 => "владимирская",
        34 => "волгоградская",
        35 => "вологодская",
        36 => "воронежская",
        77 => "москва",
        79 => "еврейская",
        75 => "забайкальский",
        37 => "ивановская",
        38 => "иркутская",
        7 => "кабардино-балкарская",
        39 => "калининградская",
        40 => "калужская",
        41 => "камчатский",
        9 => "карачаево-черкесская",
        42 => "кемеровская",
        43 => "кировская",
        44 => "костромская",
        23 => "краснодарский",
        24 => "красноярский",
        45 => "курганская",
        46 => "курская",
        47 => "ленинградская",
        48 => "липецкая",
        49 => "магаданская",
        50 => "московская",
        51 => "мурманская",
        83 => "ненецкий",
        52 => "нижегородская",
        53 => "новгородская",
        54 => "новосибирская",
        55 => "омская",
        56 => "оренбургская",
        57 => "орловская",
        58 => "пензенская",
        59 => "пермский",
        25 => "приморский",
        60 => "псковская",
        1 => "адыгея",
        2 => "башкортостан",
        3 => "бурятия",
        4 => "алтай",
        5 => "дагестан",
        6 => "ингушетия",
        8 => "калмыкия",
        10 => "карелия",
        11 => "коми",
        91 => "крым",
        12 => "марий эл",
        13 => "мордовия",
        14 => "саха /якутия/",
        15 => "северная осетия - алания",
        16 => "татарстан",
        17 => "тыва",
        19 => "хакасия",
        61 => "ростовская",
        62 => "рязанская",
        63 => "самарская",
        78 => "санкт-петербург",
        64 => "саратовская",
        65 => "сахалинская",
        66 => "свердловская",
        92 => "севастополь",
        67 => "смоленская",
        26 => "ставропольский",
        68 => "тамбовская",
        69 => "тверская",
        70 => "томская",
        71 => "тульская",
        72 => "тюменская",
        18 => "удмуртская",
        73 => "ульяновская",
        27 => "хабаровский", 
        86 => "ханты-мансийский автономный округ - югра",
        74 => "челябинская",
        20 => "чеченская",
        21 => "чувашская",
        87 => "чукотский",
        89 => "ямало-ненецкий",
        76 => "ярославская",  
    );



    public function __construct()
    {
    	parent::__construct();
        
        if (empty($this->is_developer))
        {
            exit('<center><h1 style-"color:red">ACCESS DENIED</h1></center>');
        }
        
        $this->xlsx_filename = $this->config->root_dir.'files/sudy.xlsx';

        $this->trucate_table();
        $this->run();
    }
    
    private function run()
    {
        $excel = PHPExcel_IOFactory::load($this->xlsx_filename);
         
        $start = 2;
        $res = array();
        for ($i= $start; $i <= 1000; $i++)
        {
            $item = new stdClass();
        		
            $item->code = trim($excel->getActiveSheet()->getCell('A'.$i )->getValue()); 
            $item->region = trim($excel->getActiveSheet()->getCell('B'.$i )->getValue()); 	
            $item->sud = trim($excel->getActiveSheet()->getCell('D'.$i )->getValue()); 
            $item->mir_sud = trim($excel->getActiveSheet()->getCell('E'.$i )->getValue()); 
            
            $item->find_name = empty($this->codes[intval($item->code)]) ? '' : $this->codes[intval($item->code)];

            if($item->code == null) 
                continue;
        		
            
            $this->tribunals->add_tribunal($item);
            
            $res[] = $item;
        }
        
        echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($res);echo '</pre><hr />';
        
    }
    
    public function trucate_table()
    {
    	$this->db->query("TRUCATE TABLE s_tribunals");
    }
    
    
    private function get_code($region_name)
    {
        
        $index = array_search(mb_strtolower($region_name, 'utf8'), $this->codes);
        
        if (mb_strtolower($region_name, 'utf8') == 'кемеровская область - кузбасс')
            $index = 42;
        
        return $index;            
    }
}
new TribunalScript();