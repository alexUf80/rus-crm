<?php

error_reporting(-1);
ini_set('display_errors', 'On');

class OrderSoldController extends Controller
{
    public $import_files_dir = 'files/import/';
    public $import_file = 'whitelist.csv';
    public $allowed_extensions = array('csv', 'txt');

    public function fetch()
    {
        $this->design->assign('import_files_dir', $this->import_files_dir);
        if (!is_writable($this->import_files_dir))
            $this->design->assign('message_error', 'no_permission');


        if ($this->request->post('run')) {

            $import_file = $this->request->files("import_file");

            $file = file($import_file['tmp_name']);

            $array_products = [];

            foreach ($file as $product) {
                $processed_string = $this->processing_string($product);
                $processed_string = explode(";", $processed_string);
                $array_products[] = $processed_string;
            }

            array_shift($array_products);

            foreach ($array_products as $product) {

                $data = [
                    'number' => trim($product[0]),
                    'od' => (int)$product[1],
                    'percents' => (int)$product[2],
                    'date' => date('Y-m-d', strtotime('2022-06-24'))];

                $this->contracts->cession_from_csv($data);
            }
        }

        return $this->design->fetch('order_sold.tpl');

    }

    protected function processing_string($string)
    {
        $string = rtrim($string, "\n\r,");
        $string = str_replace('"', "", $string);

        return $string;
    }

}