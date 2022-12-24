<?php

class Fns_scoring extends Core
{
    private $user_id;
    private $order_id;
    private $audit_id;
    private $type;
    
    private $url = "https://service.nalog.ru/inn-proc.do";
    

    
    public function run_scoring($scoring_id)
    {
        $update = array();
        
    	$scoring_type = $this->scorings->get_type('fns');
        
        if ($scoring = $this->scorings->get_scoring($scoring_id))
        {
            if ($order = $this->orders->get_order((int)$scoring->order_id))
            {
                if (!empty($order->inn))
                {
                    $update = array(
                        'status' => 'completed',
                        'body' => 'ИНН уже указан у клиента',
                        'success' => 1,
                        'string_result' => $order->inn
                    );
                    
                }
                else
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
                        $birthday = date('d.m.Y', strtotime($order->birth));
                        $passportdate = date('d.m.Y', strtotime($order->passport_date));
                        $fns = $this->get_inn($order->lastname, $order->firstname, $order->patronymic, $birthday, 21, $order->passport_serial, $passportdate);
    
                    
                        $score = !empty($fns->inn);
                    
                        if (empty($score) && $scoring->repeat_count < 2)
                        {
                            $update = array(
                                'status' => 'repeat',
                                'body' => serialize($fns),
                                'string_result' => 'ПОВТОРНЫЙ ЗАПРОС',
                                'repeat_count' => $scoring->repeat_count + 1,
                            );
                            
                        }
                        else
                        {
                            $update = array(
                                'status' => 'completed',
                                'body' => serialize($fns),
                                'success' => $score,
                                'string_result' => empty($fns->inn) ? 'ИНН не найден' : $fns->inn
                            );
                            
                            if (!empty($score))
                            {
                                $this->users->update_user($order->user_id, array('inn' => $fns->inn));
                            }
                        }
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
        
        $this->type = $this->scorings->get_type('fns');
    	
        $user = $this->users->get_user((int)$user_id);

        return $this->scoring($user);
    }
    
    public function scoring($user)
    {
        $birthday = date('d.m.Y', strtotime($user->birth));
        $passportdate = date('d.m.Y', strtotime($user->passport_date));
        $fns = $this->get_inn($user->lastname, $user->firstname, $user->patronymic, $birthday, 21, $user->passport_serial, $passportdate);

        if (!empty($fns->code))
        {
            $scoring = array(
                'user_id' => $user->id,
                'audit_id' => $this->audit_id,
                'type' => 'fns',
                'body' => $fns->inn,
                'success' => 1,
                'scorista_id' => '',
                'string_result' => 'ИНН найден'
            );
            $this->scorings->add_scoring($scoring);
            
            
        }
        else
        {
            $scoring = array(
                'user_id' => $user->id,
                'audit_id' => $this->audit_id,
                'type' => 'fns',
                'body' => '',
                'success' => 0,
                'scorista_id' => '',
                'string_result' => 'ИНН не найден'
            );
            $this->scorings->add_scoring($scoring);
        }

    }
    public function get_inn($surname, $name, $patronymic, $birthdate, $doctype, $docnumber, $docdate)
    {
        $docnumber_clear = str_replace(array('-', ' ', ' '), '', $docnumber);
        $docno = substr($docnumber_clear, 0, 2).' '.substr($docnumber_clear, 2, 2).' '.substr($docnumber_clear, 4, 6);
        
        $data = array(
            "fam" => $surname,
            "nam" => $name,
            "otch" => $patronymic,
            "bdate" => $birthdate,
            "bplace" => "",
            "doctype" => $doctype,
            "docno" => $docno,
            "docdt" => $docdate,
            "c" => "innMy",
            "captcha" => "",
            "captchaToken" => ""
        );
        $options = array(
            'https' => array(
                'method'  => 'POST',
                'header'  => array(
                    'Content-type: application/x-www-form-urlencoded',
                ),
                'content' => http_build_query($data)
            ),
        );


        $ch = curl_init($this->url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        
        $resp = curl_exec($ch);
echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($data, $resp);echo '</pre><hr />';
        return json_decode($resp);
    }
}