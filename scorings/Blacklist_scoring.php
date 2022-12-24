<?php

class Blacklist_scoring extends Core
{
    private $order_id;

    public function run_scoring($scoring_id)
    {
        if ($scoring = $this->scorings->get_scoring($scoring_id))
        {
            if ($order = $this->orders->get_order($scoring->order_id))
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
                else
                {
                    $fio = "$order->lastname $order->firstname $order->patronymic";
                    $score = $this->blacklist->search($fio);
                    
                    
                    $update = array(
                        'status' => 'completed',
                        'body' => '',
                        'success' => empty($score) ? 1 : 0
                    );
                    if (!empty($score))
                    {
                        $person = $this->blacklist->get_person((int)$score);
                        $update['body'] = serialize($person);
                        $update['string_result'] = 'Пользователь найден в списке: '.$person->fio;
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