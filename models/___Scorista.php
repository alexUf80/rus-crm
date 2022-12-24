<?php

class Scorista extends Core
{
    private $username;
    private $token;
    
    public function __construct()
    {
    	parent::__construct();
        
        $this->username = $this->settings->apikeys['scorista']['username'];
        $this->token = $this->settings->apikeys['scorista']['token'];
    }
    
    
    public function create($order_id)
    {
        if ($order = $this->orders->get_order((int)$order_id))
        {
            $user = $this->users->get_user((int)$order->user_id);
            
            if (empty($user))
            {
                return (object)array('error' => 'undefined_user');
            }
            
            $data = new StdClass();
            $data->form = new StdClass();
            
            $persona = new StdClass();
            
            /** Обшая информация */
            $personalInfo = new StdClass();
            $personalInfo->lastName = $user->lastname;
            $personalInfo->firstName = $user->firstname;
            $personalInfo->patronimic = $user->patronymic;
            $personalInfo->gender = $user->gender == 'male' ? 1 : 2;
            $personalInfo->birthDate = $user->birth;
            $personalInfo->placeOfBirth = $user->birth_place;
            $expl_passport_serial = explode(' ', $user->passport_serial);
            $personalInfo->passportSN = $expl_passport_serial[0].$expl_passport_serial[1].' '.$expl_passport_serial[2];
            $personalInfo->issueDate = $user->passport_date;
            $personalInfo->subCode = $user->subdivision_code;
            $personalInfo->issueAuthority = $user->passport_issued;
            
            $persona->personalInfo = $personalInfo;
            
            /** Адрес регистрации */
            $addressRegistration = new StdClass();
            $addressRegistration->postIndex = '000000';
            $addressRegistration->region = $user->Regregion;
            $addressRegistration->city = $user->Regcity;
            $addressRegistration->street = $user->Regstreet;
            $addressRegistration->house = $user->Reghousing;
            if ($user->Regbuilding)
                $addressRegistration->building = $user->Regbuilding;
            if ($user->Regroom)
                $addressRegistration->flat = $user->Regroom;
            
            $persona->addressRegistration = $addressRegistration;
            
            /** Фактический адрес проживания */
            $addressResidential = new StdClass();
            $addressResidential->postIndex = '000000';
            $addressResidential->region = $user->Faktregion;
            $addressResidential->city = $user->Faktcity;
            $addressResidential->street = $user->Faktstreet;
            $addressResidential->house = $user->Fakthousing;
            if ($user->Faktbuilding)
                $addressResidential->building = $user->Faktbuilding;
            if ($user->Faktroom)
                $addressResidential->flat = $user->Faktroom;
            
            $persona->addressResidential = $addressResidential;
            
            /** Контакная информация */
            $contactInfo = new StdClass();
            $contactInfo->cellular = $user->phone_mobile;
            $contactInfo->cellularState = 2; // Статус подтверждения мобильного телефона (2. Проходил проверку и был подтверждён)
            $contactInfo->cellularMethod = 2; // Способ подтверждения (2. По СМС-коду)
            $contactInfo->phone = empty($user->landline_phone) ? 'НЕТ' : $user->landline_phone;
            $contactInfo->phoneState = 1; // Статус подтверждения домашнего телефона (1. Не проходил проверку)
            $contactInfo->phoneMethod = 4; // Способ подтверждения (4. нет)
            $contactInfo->email = empty($user->email) ? 'НЕТ' : $user->email;
            $contactInfo->emailState = 1; // Статус подтверждения личного Email (1. Не проходил проверку)
            $contactInfo->emailMethod = 4; // Способ подтверждения личного Email (4. нет)
            
            $persona->contactInfo = $contactInfo;
            
            
            $employment = new StdClass();
            $employment->jobCategory = 10; // Нет в анкете
            
            $persona->employment = $employment;
            
            $data->form->persona = $persona;
            
            
            $info = new StdClass();
            
            $loan = new StdClass();
            $loan->loanID = $order->order_id;
            $loan->staffMember = 'CRM';
            $loan->loanPeriod = $order->period;
            $loan->loanSum = $order->amount;
            $loan->dayRate = 1; // Процентная ставка в день
            $loan->loanCurrency = 'RUB';
            $loan->fullRepaymentAmount = $order->amount + ($order->period * $order->amount / 100); // Cумма к возврату на плановую дату погашения
            $loan->applicationSourceType = 1; // Канал привлечения заявки (1. Интернет)
            $loan->agreementSignatureMethod = 2; // Способ подписания договора (2. На сайте заказчика онлайн)
            $loan->loanReceivingMethod = 11; // Способ получения займа (11. Другое)
            $loan->loanRepaymentMethod = 1; // Предполагаемый способ возврата займа (1. Перевод с банковской карты)
            
            $info->loan = $loan;
            
            $repaymentSchedule = new StdClass();
            $repaymentSchedule->repaymentDate = date('d.m.Y', time() + 86400 * $order->amount);
            $repaymentSchedule->repaymentAmount = $order->amount + ($order->period * $order->amount / 100);
            
            $info->repaymentSchedule = $repaymentSchedule;
            
            $borrowingHistory = new StdClass();
            $borrowingHistory->numberLoansRepaid = 0; // Количество ранее взятых и погашенных займов
            
            $info->borrowingHistory = $borrowingHistory;
            
            $data->form->info = $info;
            
            
            $loanReceivingMethod = new StdClass();
            
            $cash = new StdClass();
            $cash->cash = 0;
            
            $loanReceivingMethod->cash = $cash;
            
            $data->form->loanReceivingMethod = $loanReceivingMethod;
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($data);echo '</pre><hr />';
            
            return $this->send($data);
        }
        else
        {
            return (object)array('error' => 'undefined_order');
        }
    }
    
    public function get_result($request_id)
    {
        $data = new StdClass();
        $data->requestID = $request_id;
        
        return $this->send($data);
    }
    
    public function send($data)
    {
        $url = 'https://api.scorista.ru/mixed/json';
        
        $nonce = sha1(uniqid(true));
        $password = sha1($nonce.$this->token);
        
        $headers = array(
            'Content-Type: application/json',
            'username: '.$this->username,
            'nonce: '.$nonce,
            'password: '.$password,
        );
        
        $data_string = json_encode($data, JSON_UNESCAPED_UNICODE);
        
        $ch = curl_init($url);
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); 
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_POST, 1);
        
        $result = curl_exec($ch);
        $info = curl_getinfo($ch);
        
        curl_close($ch);
        
        $result = json_decode($result);
        
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($info, $result);echo '</pre><hr />';
        
        return $result;
    }
}