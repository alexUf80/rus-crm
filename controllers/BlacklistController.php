<?php

class BlacklistController extends Controller
{	
	public $import_files_dir = 'files/import/';
	public $import_file = 'blacklist.csv';
	public $allowed_extensions = array('csv', 'txt');
	private $locale = 'ru_RU.UTF-8';

	private $column_delimiter      = ';';
	private $products_count        = 30;
	private $columns               = array();

	public function fetch()
	{
		$this->design->assign('import_files_dir', $this->import_files_dir);
		if(!is_writable($this->import_files_dir))
			$this->design->assign('message_error', 'no_permission');
		
		// Проверяем локаль
		$old_locale = setlocale(LC_ALL, 0);
		setlocale(LC_ALL, $this->locale);
		if(setlocale(LC_ALL, 0) != $this->locale)
		{
			$this->design->assign('message_error', 'locale_error');
			$this->design->assign('locale', $this->locale);			
		}
		setlocale(LC_ALL, $old_locale);
			
		
		if($this->request->post('run'))
		{
            $import_file = $this->request->files("import_file");
            $ext = strtolower(pathinfo($import_file['name'], PATHINFO_EXTENSION));
            
            if (empty($import_file))
            {
                $this->design->assign('error', 'Загрузите файл');
            }
            elseif (!in_array($ext, array('csv', 'txt')))
            {
                $this->design->assign('error', 'Принимаются файлы в формате csv');
            }
            else
            {
    			$uploaded_name = $this->request->files("import_file", "tmp_name");
    			$temp = tempnam($this->config->root_dir.$this->import_files_dir, 'temp_');
    			if(!move_uploaded_file($uploaded_name, $temp))
    			{
                	$this->design->assign('error', 'Не удалось загрузить файл!');
    			}
                else
                {
        			$remove_all = $this->request->post('remove_all', 'integer');
                    $this->design->assign('remove_all', $remove_all);
                    
                    if(!$this->convert_file($temp, $this->config->root_dir.$this->import_files_dir.$this->import_file))
        				$this->design->assign('error', 'Ошибка конвертации файла');
        			else
        				$this->design->assign('go_import',  1);
        			unlink($temp);	
                }
            }
        }
        
        if ($this->request->post('import'))
        {
            $result = $this->run_import();
            
            header('Content-type: application/json');
            echo json_encode($result);
            exit;
        }
        
		return $this->design->fetch('blacklist.tpl');
	}
	
    private function run_import()
    {
		// Для корректной работы установим локаль UTF-8
		setlocale(LC_ALL, 'ru_RU.UTF-8');
		
		$result = new stdClass;
		
		// Определяем колонки из первой строки файла
		$f = fopen($this->config->root_dir.$this->import_files_dir.$this->import_file, 'r');
		$this->columns = fgetcsv($f, null, $this->column_delimiter);

		// Переходим на заданную позицию, если импортируем не сначала
		if($from = $this->request->post('from'))
        {
			fseek($f, $from);
		}
        else
        {
            if ($this->request->post('remove_all', 'integer'))
                $this->db->query("TRUNCATE TABLE __blacklist");
        }
		// Массив импортированных товаров
		$imported_items = array();	
		
		// Проходимся по строкам, пока не конец файла
		// или пока не импортировано достаточно строк для одного запроса
		for($k=0; !feof($f) && $k<$this->products_count; $k++)
		{ 
			// Читаем строку
			$line = fgetcsv($f, 0, $this->column_delimiter);

			$item = array();			
			if(is_array($line))
            {
			// Проходимся по колонкам строки
    			foreach($this->columns as $i => $col)
    			{
    				// Создаем массив item[название_колонки]=значение
     				if(isset($line[$i]) && !empty($line) && !empty($col))
    					$item[$col] = $line[$i];
    			}
            }
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($item);echo '</pre><hr />';
			
			// Импортируем этот товар
	 		if($imported_item = $this->import_item($item))
				$imported_items[] = $imported_item;
		}
		
		// Запоминаем на каком месте закончили импорт
 		$from = ftell($f);
 		
 		// И закончили ли полностью весь файл
 		$result->end = feof($f);

		fclose($f);
		$size = filesize($this->config->root_dir.$this->import_files_dir.$this->import_file);
		
		// Создаем объект результата
		$result->from = $from;          // На каком месте остановились
		$result->totalsize = $size;     // Размер всего файла
		$result->items = $imported_items;   // Импортированные товары
	
		return $result;
	}
    
    private function import_item($item)
    {
		$imported_item = new stdClass;
				
        $prepare_item = array();
        
        foreach ($item as $key => $value)
        {
            $k = mb_strtolower($key);
            if ($k == 'телефон')
                $prepare_item['phone'] = $value;
            if ($k == 'фио')
                $prepare_item['fio'] = mb_strtolower($value);
            if ($k == 'фамилия')
                $prepare_item['lastname'] = mb_strtolower($value);
            if ($k == 'имя')
                $prepare_item['firstname'] = mb_strtolower($value);
            if ($k == 'отчество')
                $prepare_item['patronymic'] = mb_strtolower($value);
        }
        
        if (isset($prepare_item['lastname'], $prepare_item['firstname']))
        {
            $prepare_item['fio'] = $prepare_item['lastname'].' '.$prepare_item['firstname'].' '.$prepare_item['patronymic'];
            unset($prepare_item['lastname'], $prepare_item['firstname'], $prepare_item['patronymic']);
        }
        
        if (!empty($prepare_item['fio']))
            $prepare_item['id'] = $this->blacklist->add_person($prepare_item);
        
        return $prepare_item;
	}
    
	private function convert_file($source, $dest)
	{
		// Узнаем какая кодировка у файла
		$teststring = file_get_contents($source, null, null, null, 1000000);
		
		if (preg_match('//u', $teststring)) // Кодировка - UTF8
		{
			// Просто копируем файл
			return copy($source, $dest);
		}
		else
		{
			// Конвертируем в UFT8
			if(!$src = fopen($source, "r"))
				return false;
			
			if(!$dst = fopen($dest, "w"))
				return false;
			
			while (($line = fgets($src, 4096)) !== false)
			{
				$line = $this->win_to_utf($line);
				fwrite($dst, $line);
			}
			fclose($src);
			fclose($dst);
			return true;
		}
	}
	
	private function win_to_utf($text)
	{
		if(function_exists('iconv'))
		{
			return @iconv('windows-1251', 'UTF-8', $text);
		}
		else
		{	
			$t = '';
			for($i=0, $m=strlen($text); $i<$m; $i++)
			{
				$c=ord($text[$i]);
				if ($c<=127) {$t.=chr($c); continue; }
				if ($c>=192 && $c<=207)    {$t.=chr(208).chr($c-48); continue; }
				if ($c>=208 && $c<=239) {$t.=chr(208).chr($c-48); continue; }
				if ($c>=240 && $c<=255) {$t.=chr(209).chr($c-112); continue; }
//				if ($c==184) { $t.=chr(209).chr(209); continue; };
//				if ($c==168) { $t.=chr(208).chr(129);  continue; };
				if ($c==184) { $t.=chr(209).chr(145); continue; }; #ё
				if ($c==168) { $t.=chr(208).chr(129); continue; }; #Ё
				if ($c==179) { $t.=chr(209).chr(150); continue; }; #і
				if ($c==178) { $t.=chr(208).chr(134); continue; }; #І
				if ($c==191) { $t.=chr(209).chr(151); continue; }; #ї
				if ($c==175) { $t.=chr(208).chr(135); continue; }; #ї
				if ($c==186) { $t.=chr(209).chr(148); continue; }; #є
				if ($c==170) { $t.=chr(208).chr(132); continue; }; #Є
				if ($c==180) { $t.=chr(210).chr(145); continue; }; #ґ
				if ($c==165) { $t.=chr(210).chr(144); continue; }; #Ґ
				if ($c==184) { $t.=chr(209).chr(145); continue; }; #Ґ            
			}
			return $t;
		}
	}
	
}