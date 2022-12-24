<?php

class Scorista_scoring extends Core
{
    private $user_id;
    private $order_id;
    private $audit_id;
    private $type;
    
    private $username;
    private $token;
    
    public function __construct()
    {
    	parent::__construct();
        
        $this->username = $this->settings->apikeys['scorista']['username'];
        $this->token = $this->settings->apikeys['scorista']['token'];
    }
    

    public function run_scoring($scoring_id)
    {
        $update = array();
        
    	$scoring_type = $this->scorings->get_type('scorista');
        
        if ($scoring = $this->scorings->get_scoring($scoring_id))
        {
            if ($order = $this->orders->get_order((int)$scoring->order_id))
            {
                if (empty($order->lastname) || empty($order->firstname) || empty($order->patronymic) || empty($order->passport_serial) || empty($order->passport_date) || empty($order->birth))
                {
                    $update = array(
                        'status' => 'error',
                        'string_result' => 'в заявке не достаточно данных для проведения скоринга'
                    );
                }
                else
                {
                    $task = $this->create($order->order_id);
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump('TASK', $task);echo '</pre><hr />';
                    if (!empty($task->requestid))
                    {
                        do {
                            sleep(2);
                            $result = $this->get_result($task->requestid);
//            echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump('RESULT', $result);echo '</pre><hr />';
                        } while ($result->status != 'DONE');
            
                        $score = $result->data->additional->summary->score > $scoring_type->params['scorebal'];
                        
                        $update = array(
                            'status' => 'completed',
                            'body' => json_encode($result->data),
                            'success' => (int)$score,
                            'scorista_status' => $result->data->decision->decisionName,
                            'scorista_ball' => $result->data->additional->summary->score,
                        );
                        if ($score)
                        {
                            $update['string_result'] = 'Проверка пройдена: '.$result->data->additional->summary->score;
                        }
                        else
                        {
                            $update['string_result'] = 'Не прошел по баллу: '.$result->data->additional->summary->score;
                        }
                
                    }
                    elseif ($task->status == 'ERROR')
                    {
                        $update = array(
                            'status' => 'error',
                            'body' => json_encode($task->error->details),
                            'success' => 0,
                            'scorista_status' => '',
                            'scorista_ball' => 0,
                            'string_result' => $task->error->message
                        );
                    }
                    else
                    {
                        $update = array(
                            'status' => 'error',
                            'body' => json_encode($task),
                            'success' => 0,
                            'scorista_status' => '',
                            'scorista_ball' => 0,
                            'string_result' => 'Ошибка при запросе'
                        );
                        
                    }
                }
                
            }
            else
            {
                $update = array(
                    'status' => 'error',
                    'string_result' => 'не найдена заявка'
                );
            }
            
            if (!empty($update))
                $this->scorings->update_scoring($scoring_id, $update);
            
            return $update;
        }
    }
    

    public function run($audit_id, $user_id, $order_id)
    {
        $this->user_id = $user_id;
        $this->audit_id = $audit_id;
        $this->order_id = $order_id;
        
        $this->type = $this->scorings->get_type('scorista');

        $task = $this->create($order_id);
/**
object(stdClass)#19 (2) {
  ["status"]=>
  string(5) "ERROR"
  ["error"]=>
  object(stdClass)#18 (3) {
    ["code"]=>
    int(400)
    ["message"]=>
    string(44) "Ошибка валидации данных"
    ["details"]=>
    object(stdClass)#17 (1) {
      ["persona"]=>
      array(1) {
        [0]=>
        object(stdClass)#16 (1) {
          ["personalInfo"]=>
          array(1) {
            [0]=>
            object(stdClass)#15 (1) {
              ["issueDate"]=>
              array(1) {
                [0]=>
                string(67) "Паспорт РФ выдается в возрасте 14 лет."
              }
            }
          }
        }
      }
    }
  }
}
*/
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($task);echo '</pre><hr />';

        if (!empty($task->requestid))
        {
            do {
                sleep(2);
                $result = $this->get_result($task->requestid);
            } while ($result->status != 'DONE');
            $score = $result->data->additional->summary->score > $this->type->params['scorebal'];
            
            $add_scoring = array(
                'body' => json_encode($result->data),
                'type' => 'scorista',
                'audit_id' => $this->audit_id,
                'user_id' => $this->user_id,
                'success' => (int)$score,
                'scorista_status' => $result->data->decision->decisionName,
                'scorista_ball' => $result->data->additional->summary->score,
                'created' => date('Y-m-d H:i:s'),
            );
            if ($score)
            {
                $add_scoring['string_result'] = 'Скоринг пройден';
            }
            else
            {
                $add_scoring['string_result'] = 'Не прошел по баллу';
            }
    
            $this->scorings->add_scoring($add_scoring);
        
            return $score;
        }
        else
        {
            
        }
        return null;
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
            $expl_passport_serial = explode('-', $user->passport_serial);
            $personalInfo->passportSN = $expl_passport_serial[0].' '.$expl_passport_serial[1];
            $personalInfo->issueDate = $user->passport_date;
            $personalInfo->subCode = $user->subdivision_code;
            $personalInfo->issueAuthority = $user->passport_issued;
            
            $persona->personalInfo = $personalInfo;
            
            /** Адрес регистрации */
            $addressRegistration = new StdClass();
            $addressRegistration->postIndex = empty($user->Regindex) ? '000000' : $user->Regindex;
            $addressRegistration->region = $user->Regregion;
            $addressRegistration->city = empty($user->Regcity) ? $user->Reglocality : $user->Regcity;
            $addressRegistration->street = $user->Regstreet;
            $addressRegistration->house = $user->Reghousing;
            if ($user->Regbuilding)
                $addressRegistration->building = $user->Regbuilding;
            if ($user->Regroom)
                $addressRegistration->flat = $user->Regroom;
            
            $persona->addressRegistration = $addressRegistration;
            
            /** Фактический адрес проживания */
            $addressResidential = new StdClass();
            $addressResidential->postIndex = empty($user->Faktindex) ? '000000' : $user->Faktindex;
            $addressResidential->region = $user->Faktregion;
            $addressResidential->city = empty($user->Faktcity) ? $user->Faktlocality : $user->Faktcity;
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