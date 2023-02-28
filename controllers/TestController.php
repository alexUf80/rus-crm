<?php

use PhpOffice\PhpSpreadsheet\IOFactory;

error_reporting(-1);
ini_set('display_errors', 'On');

class TestController extends Controller
{
    public function fetch()
    {
        $res = $this->Nbki_scoring->run_scoring(32809);
        var_dump($res);
        exit;
    }
}