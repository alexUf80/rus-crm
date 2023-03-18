<?php

ini_set('max_execution_time', 120);
error_reporting(-1);
ini_set('display_errors', 'Off');
class BlacklistController extends Controller
{
    public function fetch()
    {
        if ($this->request->post('run')) {
            $tmp_name = $_FILES['import_file']['tmp_name'];
            $format = \PhpOffice\PhpSpreadsheet\IOFactory::identify($tmp_name);

            if($format == 'Csv'){
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
                $reader->setInputEncoding('Windows-1251');
                $reader->setDelimiter(';');
                $reader->setEnclosure('');
                $reader->setSheetIndex(0);
            }else{
                $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($format);
            }
            $spreadsheet = $reader->load($tmp_name);

            $active_sheet = $spreadsheet->getActiveSheet();

            $first_row = 2;
            $last_row = $active_sheet->getHighestRow();

            if(!empty($this->request->post('remove_all')))
                BlacklistORM::truncate();

            for ($row = $first_row; $row <= $last_row; $row++) {

                $birth = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp($active_sheet->getCell('D' . $row)->getValue());
                $lastname = mb_strtoupper($active_sheet->getCell('A' . $row)->getValue());
                $firstname = mb_strtoupper($active_sheet->getCell('B' . $row)->getValue());
                $patronymic = mb_strtoupper($active_sheet->getCell('C' . $row)->getValue());

                $fio = "$lastname $firstname $patronymic";

                $client =
                    [
                        'fio' => strtoupper($fio),
                        'birth' => date('Y-m-d', $birth)
                    ];

                BlacklistORM::insert($client);

                if($row % 500 == 0)
                    usleep(300000);
            }

            $this->design->assign('success', "Файл успешно загружен");
        }

        $count = $this->whitelist->count_persons();
        $this->design->assign('count', $count);

        return $this->design->fetch('blacklist.tpl');
    }

}