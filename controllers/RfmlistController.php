<?php

error_reporting(-1);
ini_set('display_errors', 'On');

class RfmlistController extends Controller
{

    public $import_files_dir = 'files/import/';
    public $import_file = 'rfmlist.xml';

    public function fetch()
    {
        $this->design->assign('import_files_dir', $this->import_files_dir);
        if (!is_writable($this->import_files_dir))
            $this->design->assign('message_error', 'no_permission');

        if ($this->request->post('run')) {
            $import_file = $this->request->files("import_file");
            $ext = strtolower(pathinfo($import_file['name'], PATHINFO_EXTENSION));

            if (empty($import_file)) {
                $this->design->assign('error', 'Загрузите файл');
            } elseif (!in_array($ext, array('xml'))) {
                $this->design->assign('error', 'Принимаются файлы в формате xml');
            } else {

                $uploaded_name = $this->request->files("import_file", "tmp_name");

                $xml = simplexml_load_file($uploaded_name);

                $success = true;

                foreach ($xml as $value) {

                        $string = str_replace(['<![CDATA[', ']]', '*', '>'], '', (string)$value->TERRORISTS_NAME);

                        $substr = substr($string, 1);

                        $prepare_item['fio'] = mb_strtolower($substr);

                        $result = $this->rfmlist->add_person($prepare_item);

                        if($result == false)
                        {
                            $success == false;
                        }
                }

                $this->design->assign('success', $success);

            }
        }
        return $this->design->fetch('rfmlist.tpl');
    }
}