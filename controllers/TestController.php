<?php

use PhpOffice\PhpSpreadsheet\IOFactory;

error_reporting(-1);
ini_set('display_errors', 'On');

class TestController extends Controller
{
    public function fetch()
    {
        $tmp_name = $this->config->root_dir . '/files/leadgen.xlsx';
        $format = IOFactory::identify($tmp_name);
        $reader = IOFactory::createReader($format);
        $spreadsheet = $reader->load($tmp_name);

        $active_sheet = $spreadsheet->getActiveSheet();

        $first_row = 2;
        $last_row = $active_sheet->getHighestRow();

        for ($row = $first_row; $row <= $last_row; $row++) {
            $clickHash[] = $active_sheet->getCell('B' . $row)->getValue();
        }

        $this->db->query("
        SELECT us.lastname, us.firstname, us.patronymic, os.click_hash
        FROM s_users us
        JOIN s_orders os ON os.user_id = us.id
        WHERE os.click_hash IN (?@)
        ", $clickHash);

        $users = $this->db->results();

        echo '<pre>';
        var_dump($users);
        exit;
    }
}