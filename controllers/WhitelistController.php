<?php

ini_set('max_execution_time', 120);
error_reporting(-1);
ini_set('display_errors', 'Off');
class WhitelistController extends Controller
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

            for ($row = $first_row; $row <= $last_row; $row++) {

                $birth = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp($active_sheet->getCell('D' . $row)->getValue());
                $lastname = $active_sheet->getCell('C' . $row)->getValue();
                $firstname = $active_sheet->getCell('A' . $row)->getValue();
                $patronymic = $active_sheet->getCell('B' . $row)->getValue();

                $fio = "$lastname $firstname $patronymic";

                $client=
                    [
                        'fio' => $fio,
                        'birth' => date('Y-m-d', $birth),
                        'amount' => $active_sheet->getCell('E' . $row)->getValue(),
                        'sector' => $active_sheet->getCell('F' . $row)->getValue()
                    ];

                $this->whitelist->add_person($client);

                if($row % 500 == 0)
                    usleep(300000);
            }

            $this->design->assign('success', "Файл успешно загружен");
        }

        $count = $this->whitelist->count_persons();
        $this->design->assign('count', $count);

        return $this->design->fetch('whitelist.tpl');
    }

}