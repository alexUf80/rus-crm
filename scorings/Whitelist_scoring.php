<?php

class Whitelist_scoring extends Core
{
    private $user_id;
    private $order_id;
    private $audit_id;
    private $type;


    public function run_scoring($scoring_id)
    {
        $update = array();
        
    	$scoring_type = $this->scorings->get_type('whitelist');
        
        if ($scoring = $this->scorings->get_scoring($scoring_id))
        {
            if ($order = $this->orders->get_order((int)$scoring->order_id))
            {
                
                
                if (empty($order->lastname))
                {
                    $update = array(
                        'status' => 'error',
                        'string_result' => 'в заявке не указана фамилия'
                    );
                }
                elseif (empty($order->firstname))
                {
                    $update = array(
                        'status' => 'error',
                        'string_result' => 'в заявке не указано имя'
                    );
                }
                elseif (empty($order->patronymic))
                {
                    $update = array(
                        'status' => 'error',
                        'string_result' => 'в заявке не указано отчество'
                    );
                }
                elseif (empty($order->phone_mobile))
                {
                    $update = array(
                        'status' => 'error',
                        'string_result' => 'в заявке не указан телефон'
                    );
                }
                else
                {
                    $fio = $order->lastname.' '.$order->firstname.' '.$order->patronymic;
                    $score = $this->whitelist->search($order->phone_mobile, $fio);
                    
                    
                    $update = array(
                        'status' => 'completed',
                        'body' => '',
                        'success' => empty($score) ? 0 : 1
                    );

                    if ($score)
                    {
                        $person = $this->whitelist->get_person((int)$score);
                        $update['body'] = serialize($person);
                        $update['string_result'] = 'Пользователь найден в списке: '.$person->fio.' '.$person->phone;
                        
                        $scoring_types = $this->scorings->get_types();
                        
                        if ($order_scorings = $this->scorings->get_scorings(array('order_id' => $scoring->order_id)))
                        {
                            foreach ($order_scorings as $order_scoring)
                            {
                                if ($scoring_types[$order_scoring->type]->is_paid && $order_scoring->status == 'new')
                                {
                                    $this->scorings->update_scoring($order_scoring->id, array(
                                        'status' => 'stopped',
                                        'string_result' => 'Остановка по Whitelist'
                                    ));
                                }
                                
                            }
                        }
                    }
                    else
                        $update['string_result'] = 'Клиент не найден в списке';

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


}