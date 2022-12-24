<?php
ini_set("soap.wsdl_cache_enabled", 0);
ini_set('default_socket_timeout', '300');

class Soap1c extends Core
{
    private $log = 1;
    private $log_dir  = 'logs/';
    
    public function __construct()
    {
    	parent::__construct();
        
        $this->log_dir = $this->config->root_dir.$this->log_dir;
    }
    
    /**
     * Soap1c::get_premier_contracts()
     * 
     * @param mixed $date_from
     * @param mixed $date_to
     * @return void
     */
    public function get_premier_contracts($date_from, $date_to)
    {
        $request = new StdClass();
        $request->НачПериода = date('Ymd000000', strtotime($date_from));
    	$request->КонПериода = date('Ymd000000', strtotime($date_to));
        
        $result = $this->send('WebCRM', 'CessionPremier', $request);

		return empty($result->return) ? array() : json_decode($result->return);
    	
    }
    
    
    public function get_cession_info($contract_number, $faximile = 0)
    {
        $request = new StdClass();
        $request->number = $contract_number;
    	$request->facsimile = $faximile;
        
        $result = $this->send('WebCRM', 'GetCessionByNumber', $request);

		return $result;
    }
    
    
    /**
     * Soap1c::send_cessions()
     * Отправляет договора которые переведены на цессию
     * 
     * @param mixed $numbers
     * @return
     */
    public function send_cessions($numbers)
    {
        $request = new StdClass();
        $request->TextJson = json_encode($numbers);
    	
        $result = $this->send('WebCRM', 'Cess', $request);

		return $result->return;
    }
    
    /**
     * Soap1c::check_manager_name()
     * Проверяет на корректность имя менеджера для обмена с 1с
     * @param string $manager_name
     * @return boolean
     */
    public function check_manager_name($manager_name)
    {
        $request = new StdClass();
        $request->Сотрудник = $manager_name;
    	
        $result = $this->send('WebCRM', 'VerificationManager', $request);

		return $result->return;
    }
    
    
    public function send_collector($contract_number, $collector_id)
    {
    	if (!($collector = $this->managers->get_manager($collector_id)))
            return false;
            
        $request = new StdClass();
        $request->НомерЗайма = $contract_number;
        $request->Сотрудник = $collector->name_1c;

        
        $result = $this->send('WebCRM', 'ChangeResponsible', $request);
//  echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($result);echo '</pre><hr />';      
		return empty($result->return) ? array() : $result->return;

    }

    public function send_fssp($order_1c_id, $body)
    {
        $request = new StdClass();
        $request->НомерЗаявки = $order_1c_id;
        $request->TextJson = json_encode($body);

        
        $result = $this->send('WebCRM', 'FSSP', $request);
//  echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($result);echo '</pre><hr />';      
		return empty($result->return) ? array() : $result->return;
    	
    }
    
    
    public function send_fms($order_1c_id, $passport, $check)
    {
        $clear_passport = trim(str_replace(array(' ', '-', '_'), '', $passport));
        $passport_series = substr($clear_passport, 0, 4);
        $passport_number = substr($clear_passport, 4, 6);;
        
        $request = new StdClass();
        $request->НомерЗаявки = $order_1c_id;
        $request->ПаспортСерия = $passport_series;
        $request->ПаспортНомер = $passport_number;
        $request->Действителен = $check;
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($passport, $request);echo '</pre><hr />';
        $result = $this->send('WebCRM', 'FMS', $request);
//  echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($result);echo '</pre><hr />';      
		return empty($result->return) ? array() : $result->return;
    	
    }
    
    
    /**
     * Soap1c::send_order()
     * ОТправляет заявку в 1с
     * 
     * @param object $order
     * @return
     */
    public function send_order($order)
    {
        $order_passport_serial = str_replace(array(' ', '-'), '', $order->passport_serial);
		$passport_number = (string)substr($order_passport_serial, 4, 6);
        $passport_serial = (string)substr($order_passport_serial, 0, 4);        
        
        $request = (object)array(
            'partner' => 'NalPlus',
            'WebMaster' => '',
            'id' => $order->order_id,
            'last_name' => $order->lastname,
            'first_name' => $order->firstname,
            'middle_name' => $order->patronymic,
            'phone' => $this->format_phone($order->phone_mobile),
            'birthday' => date('Ymd', strtotime($order->birth)),
            'email' => $order->email,
            'PassportSerial' => $passport_serial,
            'PassportNumber' => $passport_number,
            'passport_date' => date('Ymd', strtotime($order->passport_date)),
            'subdivision_code' => $order->subdivision_code,
            'passport_issued' => $order->passport_issued,
            'Regindex' => $order->Regindex,
            'Regregion' => trim($order->Regregion.' '.$order->Regregion_shorttype),
            'Regdistrict' => '',
            'Regcity' => trim($order->Regcity.' '.$order->Regcity_shorttype),
            'Reglocality' => '',
            'Regstreet' => trim($order->Regstreet.' '.$order->Regstreet_shorttype),
            'Regbuilding' => empty($order->Regbuilding) ? '' : $order->Regbuilding,
            'Reghousing' => $order->Reghousing,
            'Regroom' => $order->Regroom,
            'Faktindex' => $order->Faktindex,
            'Faktregion' => trim($order->Faktregion.' '.$order->Faktregion_shorttype),
            'Faktdistrict' => '',
            'Faktcity' => trim($order->Faktcity.' '.$order->Faktcity_shorttype),
            'Faktlocality' => '',
            'Faktstreet' => trim($order->Faktstreet.' '.$order->Faktstreet_shorttype),
            'Faktbuilding' => empty($order->Faktbuilding) ? '' : $order->Faktbuilding,
            'Fakthousing' => $order->Fakthousing,
            'Faktroom' => $order->Faktroom,
            'BirthPlace' => $order->birth_place,
            'snils' => $order->snils,
            'contact_person_1_name' => $order->contact_person_name,
            'contact_person_1_phone' => $order->contact_person_phone,
            'contact_person_1_accounts' => $order->contact_person_relation,
            'contact_person_2_name' => $order->contact_person2_name,
            'contact_person_2_phone' => $order->contact_person2_phone,
            'contact_person_2_accounts' => $order->contact_person2_relation,
            'job_profession' => $order->profession,
            'job_address' => $order->workaddress,
            'job_phone' => $order->workphone,
            'job_employment' => '',
            'job_name' => $order->workplace,
            'job_how_long' => '',
            'job_activity' => '',
            'gender' => $order->gender == 'male' ? 'Мужской' : 'Женский',
            'education' => '',
            'marital_status' => '',
            'children' => '',
            'match_addresses' => '',
            'revenue_main' => '',
            'revenue_additional' => '',
            'revenue_family' => '',
            'costs_credit' => '',
            'costs_communa' => '',
            'costs_aliments' => '',
            'credit_history' => '',
            'credit_max' => '',
            'credit_last' => '',
            'bankrot' => '',
            'amount' => $order->amount,
            'period' => $order->period,
            'АвтоОтказНаСайте' => '',
            'ПричинаАвтоОтказа' => '',
            'utm_source' => '',
            'utm_medium' => '',
            'utm_campaign' => '',
            'utm_content' => '',
            'utm_term' => '',
            'click_hash' => '',
            'CodeSMS' => '',
            'TextQuery' => '',
        );
		$response = $this->send('Teleport', 'ObmenFull', $request);
        
		return $response->return;
        
    }
    
    /**
     * Soap1c::get_payments1c()
     * Возвращает оплаты зафиксированные в 1с за указанный период
     * 
     * @param string $from Y-m-d
     * @param string $to  Y-m-d
     * @return array
     */
    public function get_payments1c($from, $to)
    {
        $date_from = date('Ymd000000', strtotime($from));
        $date_to = date('Ymd000000', strtotime($to));
        
        $request = new StdClass();
        $request->НачПериода = $date_from;
        $request->КонПериода = $date_to;

        $result = $this->send('WebCRM', 'PaymentIn1c', $request);
        
		return empty($result->return) ? array() : json_decode($result->return);
    }
    
    /**
     * Soap1c::update_fields()
     * Обновляет в 1с данные по клиенту
     *      
     * @param mixed $order_id_1c
     * @param mixed $fields
     * @param string $uid
     * @return
     */
    public function update_fields($fields, $uid = '', $order_id_1c = '')
    {
    	$z = new StdClass();
        
        $z->НомерЗаявки = empty($order_id_1c) ? '' : $order_id_1c;
        $z->Uid = empty($uid) ? '' : $uid;
        
        $update = new StdClass();
        
        
        // место регистрации
        if (isset($fields['Regcity']))
            $update->АдресРегистрацииГород = $fields['Regcity'];
        if (isset($fields['Reghousing']))
            $update->АдресРегистрацииДом = $fields['Reghousing'];
        if (isset($fields['Regindex']))
            $update->АдресРегистрацииИндекс = $fields['Regindex'];
        if (isset($fields['Regroom']))
            $update->АдресРегистрацииКвартира = $fields['Regroom'];
        if (isset($fields['Regregion']))
            $update->АдресРегистрацииРегион = $fields['Regregion'];
        if (isset($fields['Regstreet']))
            $update->АдресРегистрацииУлица = $fields['Regstreet'];
        if (isset($fields['Regdistrict']))
            $update->АдресРегистрацииРайон = $fields['Regdistrict'];
        if (isset($fields['Reglocality']))
            $update->АдресРегистрацииНасПункт = $fields['Reglocality'];        
        
        // место фактического проживания
        if (isset($fields['Faktcity']))
            $update->АдресФактическогоПроживанияГород = $fields['Faktcity'];
        if (isset($fields['Fakthousing']))
            $update->АдресФактическогоПроживанияДом = $fields['Fakthousing'];
        if (isset($fields['Faktindex']))
            $update->АдресФактическогоПроживанияИндекс = $fields['Faktindex'];
        if (isset($fields['Faktroom']))
            $update->АдресФактическогоПроживанияКвартира = $fields['Faktroom'];
        if (isset($fields['Faktregion']))
            $update->АдресФактическогоПроживанияРегион = $fields['Faktregion'];
        if (isset($fields['Faktstreet']))
            $update->АдресФактическогоПроживанияУлица = $fields['Faktstreet'];
        if (isset($fields['Faktdistrict']))
            $update->АдресФактическогоПроживанияРайон = $fields['Faktdistrict'];
        if (isset($fields['Faktlocality']))
            $update->АдресФактическогоПроживанияНасПункт = $fields['Faktlocality'];        

        if (isset($fields['phone_mobile']))
            $update->АдресФактическогоПроживанияМобильныйТелефон = $fields['phone_mobile'];        

        // персональная информация
        if (isset($fields['birth']))
            $update->ДатаРожденияПоПаспорту = date('Ymd000000', strtotime($fields['birth']));
        if (isset($fields['birth_place']))
            $update->МестоРожденияПоПаспорту = $fields['birth_place'];
        if (isset($fields['gender']))
            $update->Пол = ($fields['gender'] == 'male') ? 'Мужской' : 'Женский';
        if (isset($fields['lastname']))
            $update->Фамилия = $fields['lastname'];
        if (isset($fields['firstname']))
            $update->Имя = $fields['firstname'];
        if (isset($fields['patronymic']))
            $update->Отчество = $fields['patronymic'];

        
        // паспортные данные
        if (isset($fields['passport_serial']))
        {
            $cl = str_replace(array(' ', '-'), '', $fields['passport_serial']);
            $update->ПаспортСерия = substr($cl, 0, 4);
            $update->ПаспортНомер = substr($cl, 4, 6);
        }
        if (isset($fields['subdivision_code']))
            $update->ПаспортКодПодразделения = $fields['subdivision_code'];
        if (isset($fields['passport_issued']))
            $update->ПаспортКемВыдан = $fields['passport_issued'];
        if (isset($fields['passport_date']))
            $update->ПаспортДатаВыдачи = date('Ymd000000', strtotime($fields['passport_date']));
            
            
        //  Данные о работе
        if (isset($fields['workplace']))
            $update->ОрганизацияНазвание = $fields['workplace'];
        if (isset($fields['profession']))
            $update->ОрганизацияДолжность = $fields['profession'];
        if (isset($fields['workaddress']))
            $update->ОрганизацияАдрес = $fields['workaddress'];
        if (isset($fields['workphone']))
            $update->ОрганизацияТелефон = $this->format_phone($fields['workphone']);
        if (isset($fields['chief_name']))
            $update->ОрганизацияФИОРуководителя = $fields['chief_name'];
        if (isset($fields['workcomment']))
            $update->ОрганизацияКомментарийКТелефону = $fields['workcomment'];

        if (isset($fields['income_base']))
            $update->ОрганизацияЕжемесячныйДоход = $fields['income_base'];
        if (isset($fields['expenses']))
            $update->ОбщаяСуммаРасходов = $fields['expenses'];
            
            

        // Контактные лица
        if (isset($fields['contactpersons']))
        {
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($fields['contactpersons']);echo '</pre><hr />';
            $update->КонтактныеЛица = ($fields['contactpersons']);
        }
                
        $z->TextJson = json_encode($update);
        
		$response = $this->send('WebCRM', 'GetChangingFields', $z);
        
		return empty($response->return) ? array() : json_decode($response->return);    	
    
    }
    

    public function send_order_status($order_id_1c, $status)
    {
    	$request = new StdClass();
		$request->id = $order_id_1c;
		$request->Status = $status;

		$response = $this->send('WebCRM', 'StatusesApplication', $request);
        
		return empty($response->return) ? array() : json_decode($response->return);    	
    }
    

    /**
     * Soap1c::get_client_credits()
     * Возвращает историю займов клиента по уид
     * @param string $uid
     * @return
     */
    public function get_client_credits($uid)
    {
    	$request = new StdClass();
		$request->UID = $uid;

		$response = $this->send('WebCRM', 'HistoryZaim', $request);
        
		return empty($response->return) ? array() : json_decode($response->return);
    }
    
    /**
     * Soap1c::send_order_images()
     * Отправляет изображения привязанные к заявке
     * @param mixed $order_id
     * @param mixed $images
     * @return
     */
    public function send_order_images($order_id, $images)
    {
    	$request = new StdClass();
        $request->id = $order_id;
        foreach ($images as $im)
            $im->TypeFile = 'Foto';
        $request->Files = json_encode($images);

		$response = $this->send('WebCRM', 'RequestFiles', $request);
        
		return empty($response->return) ? array() : $response->return;   	
        
    }

    /**
     * Soap1c::send_order_images()
     * Отправляет документы привязанные к заявке
     * @param mixed $order_id
     * @param mixed $images
     * @return
     */
    public function send_order_files($order_id, $documents)
    {
    	$request = new StdClass();
        $request->id = $order_id;
        foreach ($documents as $doc)
            $doc->TypeFile = 'Document';
        $request->Files = json_encode($documents);

		$response = $this->send('WebCRM', 'RequestFiles', $request);
        
		return empty($response->return) ? array() : $response->return;   	
        
    }

    /**
     * Soap1c::send_rejection()
     * Отправляет в 1с успешнуб опреацию по доп услуге Причина отказа
     * Должен содержать обьект transaction
     * 
     * @param object $operation
     * @return string
     */
    public function send_reject_reason($operation)
    {
    	$request = new StdClass();
        $request->aid = $operation->order_id;
        $request->OrderID = $operation->transaction->register_id;
        $request->OperationID = $operation->transaction->operation;
        $request->RejectionDate = date('YmdHis', strtotime($operation->transaction->created)); // формат ггггММддччммсс
        $request->Amount = (float)$operation->amount;

		$response = $this->send('WebCRM', 'RejectionSum', $request);

		return empty($response->return) ? NULL : $response->return;    	
    }
    
    
    /**
     * Soap1c::get_movements()
     * Возвращает движения по кредиту по номеру договора
     * 
     * @param string $number
     * @return void
     */
    public function get_movements($number)
    {
    	$request = new StdClass();
        $request->Number = $number;

		$response = $this->send('WebCRM', 'Movements', $request);
        
		return empty($response->return) ? array() : json_decode($response->return);
    }
    
    /**
     * Soap1c::GetCheckBlockZayavka()
     * Проверяет заблокирована ли заявка
     * 
     * @param string $order_id_1c
     * @return
     */
    public function check_block_order_1c($order_id_1c)
    {
        $request = new StdClass();
        $request->НомерЗаявки = $order_id_1c;
        
        $result = $this->send('WebCRM', 'GetCheckBlockZayavka', $request);
        
		return $result->return;
    }
    
    /**
     * Soap1c::block_order_1c()
     * Блокирует или разблокирует заявку в 1с
     * 
     * @param string $order_id_1c
     * @param integer $status (0 - разблокировать заявяку, 1 - заблокировать заявку)
     * @return
     */
    public function block_order_1c($order_id_1c, $status)
    {
        $request = new StdClass();
        $request->НомерЗаявки = $order_id_1c;
        $request->Block = $status;

        $result = $this->send('WebCRM', 'GetBlockZayavka', $request);
        
		return $result->return;
    }

    public function get_comments($user_uid)
    {
        if (empty($user_uid))
            return false;
        $request = new StdClass();
        $request->UID = $user_uid;

        $result = $this->send('WebCRM', 'Comments1С', $request);
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($result);echo '</pre><hr />';        
        return isset($result->return) ? json_decode($result->return) : $result;
    }

    /**
     * Soap1c::send_comments()
     * отправляет комментарии в 1с, только по выданным кредитам
     * должен содержать обьект contract
     * должен содержать обьект contactperson если комеентарий к конт лицу
     * 
     * @param mixed $comments
     * @return
     */
    public function send_comments($comments)
    {
        $managers = array();
        foreach ($this->managers->get_managers() as $m)
            $managers[$m->id] = $m;
        
    	$items = array();
        foreach ($comments as $comment)
        {
            $item = new StdClass();
            
            $item->НомерЗайма = $comment->contract->number;//обязательно в номере ТИРЕ. длина номера 11 символов!!!
            $item->Дата = date('YmdHis', strtotime($comment->created));
            $item->Комментарий = $comment->text;
            $item->Сотрудник = $managers[$comment->manager_id]->name_1c;
            
            if (!empty($comment->contactperson))
            {
                $expl = array_map('trim', explode(' ', $comment->contactperson->name));
                $contactperson_lastname = (string)array_shift($expl);
                $contactperson_firstname = (string)array_shift($expl);
                $contactperson_patronymic = (string)implode(' ', $expl);
            
                $item->КЛ_Фамилия = $contactperson_lastname;
                $item->КЛ_Имя = $contactperson_firstname;
                $item->КЛ_Отчество = $contactperson_patronymic;
            }
            
            $items[] = $item;
        }
        
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($comments, $items);echo '</pre><hr />';            
        $request = new StdClass();
        $request->TextJson = json_encode($items);
        $result = $this->send('WebCRM', 'Comments', $request);
        
        return $result;

    }
    
    
    public function send_operations($operations)
    {
        $percents = array();
        $charges = array();
        foreach ($operations as $operation)
        {
            if ($operation->type == 'PERCENTS')
            {
                $item = new StdClass();
                
                $item->НомерЗайма = $operation->contract->number;
                $item->ДатаНачисления = date('YmdHis', strtotime($operation->created));
                $item->ВидОплаты = 'Проценты';
                $item->ОстатокОД = (float)$operation->loan_body_summ;
                $item->СуммаПроцент = (float)$operation->amount;
                $item->СуммаОтветственности = 0;
                $item->СуммаПени = 0;

                $percents[] = $item;
            }
            else
            {
                if (isset($charges[$operation->contract_id]))
                {
                    if ($operation->type == 'PENI')
                    {
                        $charges[$operation->contract_id]->СуммаПени = (float)$operation->amount;
                    }
                    if ($operation->type == 'CHARGE')
                    {
                        $charges[$operation->contract_id]->СуммаОтветственности = (float)$operation->amount;
                    }
                    
                }
                else
                {
                    $item = new StdClass();
                    
                    $item->НомерЗайма = $operation->contract->number;
                    $item->ДатаНачисления = date('YmdHis', strtotime($operation->created));
                    
                    $item->ВидОплаты = 'Просрочка';
                    $item->ОстатокОД = (float)$operation->loan_body_summ;
                    $item->СуммаПроцент = 0;
                    if ($operation->type == 'PENI')
                    {
                        $item->СуммаПени = (float)$operation->amount;
                        $item->СуммаОтветственности = 0;
                    }
                    if ($operation->type == 'CHARGE')
                    {
                        $item->СуммаПени = 0;
                        $item->СуммаОтветственности = (float)$operation->amount;
                    }
                    
                    $charges[$operation->contract_id] = $item;
                }
            }
        }
        
        if (!empty($charges))
        {
            $prepare_charges = array_chunk($charges, 100);
        
            foreach ($prepare_charges as $prepare_charge)
            {
                $request = new StdClass();
                $request->TextJson = json_encode($prepare_charge);
                $result = $this->send('WebCRM', 'Calculation', $request);
    
                if (!isset($result->return) || $result->return != 'OK')
                    return false;
            }
        }
        
        if (!empty($percents))
        {
            $prepare_percents = array_chunk($percents, 100);
        
            foreach ($prepare_percents as $prepare_percent)
            {
                $request = new StdClass();
                $request->TextJson = json_encode($prepare_percent);
                $result = $this->send('WebCRM', 'Calculation', $request);
    
                if (!isset($result->return) || $result->return != 'OK')
                    return false;
            }
        }
        
        return $result;
    }
    
    
    /**
     * Soap1c::send_payments()
     * Отсылает данные по оплатам клиентов
     * Должен содержать обьекты contract, insurance
     * @param mixed $transactions
     * @return
     */
    public function send_payments($operations)
    {
    	$items = array();
        foreach ($operations as $operation)
        {
            $item = new StdClass();
            
            $item->НомерЗайма = $operation->contract->number;//обязательно в номере ТИРЕ. длина номера 11 символов!!!
            $item->ДатаОплаты = date('YmdHis', strtotime($operation->created));
            $item->НомеорОплаты = date('md', strtotime($operation->created)).'-'.$operation->transaction->id;//обязательно в номере ТИРЕ. длина номера 11 символов!!!
            
            $item->ID_Заказ = $operation->transaction->register_id;
            $item->ID_УспешнаяОперация = $operation->transaction->operation;
            
            $item->СуммаОД = $operation->transaction->loan_body_summ;
            $item->СуммаПроцент = $operation->transaction->loan_percents_summ;
            $item->СуммаОтветственности = $operation->transaction->loan_charge_summ;
            $item->СуммаПени = $operation->transaction->loan_peni_summ;
            $item->СуммаКомиссии = $operation->transaction->commision_summ;
            
            $item->Пролонгация = empty($operation->transaction->prolongation) ? 0 : 1;  //1 - истина, 0 ложь
            
            $item->Страховка = new StdClass();
            $item->Страховка->СуммаСтраховки = empty($operation->transaction->insurance) ? 0 : $operation->transaction->insurance->amount;
            $item->Страховка->НомерСтраховки = empty($operation->transaction->insurance) ? '' : $operation->transaction->insurance->number;
            $item->Страховка->КредитнаяЗащита = empty($operation->transaction->insurance->protection) ? 0 : 1;
        
            $items[] = $item;
        }

        $request = new StdClass();
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($items);echo '</pre><hr />';
        $request->ArrayOplata = json_encode($items);
        $result = $this->send('WebCRM', 'Oplata', $request);
        
        return $result;
    }
    
    
    /**
     * Soap1c::send_contracts()
     * 
     * Метод отсылает в 1с данные по выданным кредитам
     * 
     * @param array $contracts
     * обьект $contract входящий в массив должен содержать обьекты user, p2pcredit, order, insurance
     *  Пример получения:
     *     $contract->user = $core->users->get_user((int)$contract->user_id);
     *     $contract->order = $core->orders->get_order((int)$contract->order_id);
     *     $contract->p2pcredit = $core->best2pay->get_contract_p2pcredit($contract->id);
     *     $contract->insurance = $core->insurances->get_insurance($contract->insurance_id);
     * @return string - в случае успеха должно вернуться ОК
     */
    public function send_contracts($contracts)
    {
        $managers = array();
        foreach ($this->managers->get_managers() as $m)
            $managers[$m->id] = $m;
        
        $items = array();
        foreach ($contracts as $contract)
        {
            $item = new StdClass();

            $item->Организация = 'NalPlus';
            $item->Номер = $contract->number; // 12345-67890обязательно в номере ТИРЕ. длина номера 11 символов!!!
            $item->id_заявка = $contract->order_id;
            $item->Сумма = $contract->amount;  //в рублях
            $item->Срок = (int)$contract->period;
            $item->Дата = date('YmdHis', strtotime($contract->accept_date)); //формат ггггММддччммсс
            $item->ПроцентСтавка = $contract->base_percent;
            $item->МераОтветственности = $contract->charge_percent;
            $item->УИД_Займ = $contract->uid; 
            $item->УИД_Заявка = $contract->order->uid; 
            $item->КодСМС = $contract->accept_code;
            $item->Менеджер = empty($contract->order->manager_id) ? '' : $managers[$contract->order->manager_id]->name_1c;
            
            $item->Payment = new StdClass();
            $item->Payment->CardId = $contract->card_id; 
            $item->Payment->Дата = date('YmdHis', strtotime($contract->p2pcredit->date)); 
            $item->Payment->PaymentId = $contract->p2pcredit->operation_id; 
            $item->Payment->OrderId = $contract->p2pcredit->register_id; 
            
            $item->Страховка = new StdClass();
            $item->Страховка->Сумма = empty($contract->insurance) ? 0 : $contract->insurance->amount; //сумма страховки
            $item->Страховка->Номер = empty($contract->insurance) ? '' : $contract->insurance->number; //'200H3NZI16300047__'; по правилам от страховой компании
            $item->Страховка->OrderID = empty($contract->insurance->transaction) ? '' : $contract->insurance->transaction->register_id; 
            $item->Страховка->OperationID = empty($contract->insurance->transaction) ? '' : $contract->insurance->transaction->operation; 
            $item->Страховка->КредитнаяЗащита =  empty($contract->insurance->protection) ? 0 : 1;
            
            $item->Клиент = new StdClass();
            $item->Клиент->Фамилия = $contract->user->lastname;
            $item->Клиент->Имя = $contract->user->firstname;
            $item->Клиент->Отчество = $contract->user->patronymic;
            $item->Клиент->ДатаРожденияПоПаспорту = date('YmdHis', strtotime($contract->user->birth));

            $item->Клиент->АдресРегистрацииИндекс = $contract->user->Regindex;
            $item->Клиент->АдресРегистрацииРегион = trim($contract->user->Regregion.' '.$contract->user->Regregion_shorttype);
            $item->Клиент->АдресРегистрацииРайон = trim($contract->user->Regdistrict.' '.$contract->user->Regdistrict_shorttype);
            $item->Клиент->АдресРегистрацииГород = trim($contract->user->Regcity.' '.$contract->user->Regcity_shorttype);
            $item->Клиент->АдресРегистрацииНасПункт = trim($contract->user->Reglocality.' '.$contract->user->Reglocality_shorttype);
            $item->Клиент->АдресРегистрацииУлица = trim($contract->user->Regstreet.' '.$contract->user->Regstreet_shorttype);
            $item->Клиент->АдресРегистрацииДом = $contract->user->Reghousing.(empty($contract->user->Regbuilding) ? '' : ' стр. '.$contract->user->Regbuilding);
            $item->Клиент->АдресРегистрацииКвартира = $contract->user->Regroom;
            $item->Клиент->АдресРегистрацииТелефон = '';
            
            $item->Клиент->АдресФактическогоПроживанияИндекс = $contract->user->Faktindex;
            $item->Клиент->АдресФактическогоПроживанияРегион = trim($contract->user->Faktregion.' '.$contract->user->Faktregion_shorttype);
            $item->Клиент->АдресФактическогоПроживанияРайон = trim($contract->user->Faktdistrict.' '.$contract->user->Faktdistrict_shorttype);
            $item->Клиент->АдресФактическогоПроживанияГород = trim($contract->user->Faktcity.' '.$contract->user->Faktcity_shorttype);
            $item->Клиент->АдресФактическогоПроживанияНасПункт = trim($contract->user->Faktlocality.' '.$contract->user->Faktlocality_shorttype);;
            $item->Клиент->АдресФактическогоПроживанияУлица = trim($contract->user->Faktstreet.' '.$contract->user->Faktstreet_shorttype);
            $item->Клиент->АдресФактическогоПроживанияДом = $contract->user->Fakthousing.(empty($contract->user->Faktbuilding) ? '' : ' стр. '.$contract->user->Faktbuilding);
            $item->Клиент->АдресФактическогоПроживанияКвартира = $contract->user->Faktroom;
            $item->Клиент->АдресФактическогоПроживанияТелефон = '';

            $item->Клиент->АдресФактическогоПроживанияМобильныйТелефон = $this->format_phone($contract->user->phone_mobile); //TODO: формат 8(ххх)ххх-хх-хх

            $item->Клиент->ИНН = $contract->user->inn;
            $item->Клиент->КоличествоИждевенцев = '';
            $item->Клиент->МестоРожденияПоПаспорту = $contract->user->birth_place;
            $item->Клиент->Образование = '';
            
            $item->Клиент->ОрганизацияАдрес = $contract->user->workaddress;
            $item->Клиент->ОрганизацияГрафикЗанятости = '';
            $item->Клиент->ОрганизацияДолжность = $contract->user->profession;
            $item->Клиент->ОрганизацияЕжемесячныйДоход = $contract->user->income;
            $item->Клиент->ОрганизацияНазвание = $contract->user->workplace;
            $item->Клиент->ОрганизацияСтажРаботыЛет = '';
            $item->Клиент->ОрганизацияСфераДеятельности = '';
            $item->Клиент->ОрганизацияТелефон = $this->format_phone($contract->user->workphone);
            $item->Клиент->ОрганизацияФИОРуководителя = $contract->user->chief_name;
            $item->Клиент->ОрганизацияТелефонРуководителя = $this->format_phone($contract->user->chief_phone);
            
            $item->Клиент->ПаспортДатаВыдачи = date('YmdHis', strtotime($contract->user->passport_date)); //формат ггггММддччммсс
            $item->Клиент->ПаспортКемВыдан = $contract->user->passport_issued;
            $item->Клиент->ПаспортКодПодразделения = $contract->user->subdivision_code;
            $item->Клиент->ПаспортНомер = (string)substr(str_replace(array(' ', '-'), '', $contract->user->passport_serial), 4, 6);
            $item->Клиент->ПаспортСерия = (string)substr(str_replace(array(' ', '-'), '', $contract->user->passport_serial), 0, 4);

            $item->Клиент->Пол = $contract->user->gender == 'male' ? 'Мужской' : 'Женский';
//            $item->Клиент->СемейноеСтатус = '';
            
            $item->Клиент->КонтактныеЛица = array();
            
            $contactperson = new StdClass();
            $contact_person_array = explode(' ', $contract->user->contact_person_name);
            $contactperson->Фамилия = isset($contact_person_array[0]) ? $contact_person_array[0] : '';
            $contactperson->Имя =  isset($contact_person_array[1]) ? $contact_person_array[1] : ''; 
            $contactperson->Отчество =  isset($contact_person_array[2]) ? $contact_person_array[2] : '';
            $contactperson->ТелефонМобильный = $this->format_phone($contract->user->contact_person_phone);
            $contactperson->СтепеньРодства = $contract->user->contact_person_relation;
            
            $item->Клиент->КонтактныеЛица[] = $contactperson;
            
            $contactperson2 = new StdClass();
            $contact_person2_array = explode(' ', $contract->user->contact_person2_name);
            $contactperson2->Фамилия = isset($contact_person2_array[0]) ? $contact_person2_array[0] : '';
            $contactperson2->Имя = isset($contact_person2_array[1]) ? $contact_person2_array[1] : '';
            $contactperson2->Отчество = isset($contact_person2_array[2]) ? $contact_person2_array[2] : '';
            $contactperson2->ТелефонМобильный = $this->format_phone($contract->user->contact_person2_phone);
            $contactperson2->СтепеньРодства = $contract->user->contact_person2_relation;
            
            $item->Клиент->КонтактныеЛица[] = $contactperson2;
            
            
            $items[] = $item; 
        }
echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump(($items));echo '</pre><hr />';
        $request = new StdClass();
        $request->ArrayContracts = json_encode($items);
        $result = $this->send('WebCRM', 'Request', $request);
        
        return $result;
    }
    
    /**
     * Soap1c::format_phone()
     * Форматирует номер телефона в формат принимаемый 1с
     * формат 8(ххх)ххх-хх-хх
     * 
     * @param string $phone
     * @return string $format_phone
     */
    public function format_phone($phone)
    {
        if (empty($phone))
            return '';
        
        if ($phone == 'не указан' || $phone == 'не указана')
            return '';
        
        $replace_params = array('(', ')', ' ', '-', '+');
        $clear_phone = str_replace($replace_params, '', $phone);
        
        $substr_phone = mb_substr($clear_phone, -10, 10, 'utf8');
        $format_phone = '8('.mb_substr($substr_phone, 0, 3, 'utf8').')'.mb_substr($substr_phone, 3, 3, 'utf8').'-'.mb_substr($substr_phone, 6, 2, 'utf8').'-'.mb_substr($substr_phone, 8, 2, 'utf8');
        
        return $format_phone;
    }
    
    /**
     * Soap1c::send()
     * 
     * @param string $service
     * @param string $method
     * @param array $request
     * @return
     */
    private function send($service, $method, $request)
    {
/*
        try {
			$service_url = "http://46.29.78.222:88/work/ws/".$service.".1cws?wsdl";
            $client = new SoapClient($service_url);

			$response = $client->__soapCall($method, array($request));
		} catch (Exception $fault) {
			$response = $fault;
		}
*/        
        if (!empty($this->log))
            $this->logging(__METHOD__, $service.' '.$method, (array)$request, (array)$response);
        
            return $response;
    }

    public function logging($local_method, $service, $request, $response, $filename = 'soap.txt')
    {
        $log_filename = $this->log_dir.$filename;
        
        if (date('d', filemtime($log_filename)) != date('d'))
        {
            $archive_filename = $this->log_dir.'archive/'.date('ymd', filemtime($log_filename)).'.'.$filename;
            rename($log_filename, $archive_filename);
            file_put_contents($log_filename, "\xEF\xBB\xBF");            
        }


        $str = PHP_EOL.'==================================================================='.PHP_EOL;
        $str .= date('d.m.Y H:i:s').PHP_EOL;
        $str .= $service.PHP_EOL;
        $str .= var_export($request, true).PHP_EOL;
        $str .= var_export($response, true).PHP_EOL;
        $str .= 'END'.PHP_EOL;
        
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($str);echo '</pre><hr />';
        
        file_put_contents($this->log_dir.$filename, $str, FILE_APPEND);
    }
}