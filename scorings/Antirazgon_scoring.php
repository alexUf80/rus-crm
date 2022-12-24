<?php

class Antirazgon_scoring extends Core
{
    private $user_id;
    private $order_id;
    private $audit_id;
    private $type;

    private $reason_id = 15;
    private $reject_reason  = 'К сожалению, у вас большая финансовая нагрузка';
    
    
    public function run_scoring($scoring_id)
    {
        $update = array();
        
    	$scoring_type = $this->scorings->get_type('antirazgon');
        
        if ($scoring = $this->scorings->get_scoring($scoring_id))
        {
            if ($order = $this->orders->get_order((int)$scoring->order_id))
            {
                if ($user = $this->users->get_user((int)$order->user_id))
                {
                    $last_credit = NULL;
                    $total_paid = 0;
                    $close_credits = array();
                    
                    
                    
                    if (!empty($user->loan_history))
                    {
                        foreach ($user->loan_history as $item)
                        {
                            if (empty($close_credits[$item->number]))
                            {
                                $item_credit = (object)array(
                                    'date' => $item->date,
                                    'close_date' => $item->close_date,
                                    'amount' => $item->amount,
                                    'total_paid' => $item->total_paid,
                                    'number' => $item->number,
                                    'type' => 'onec',
                                );
                                
                                
                                if (empty($last_credit) || strtotime($last_credit->close_date) < strtotime($item_credit->close_date))
                                    $last_credit = $item_credit;
                                
                                $total_paid += $item->total_paid;
                                
                                $close_credits[$item->number] = $item_credit;
                            }
                        }
                    }
                    if (!empty($last_credit))
                    {
                        $date1 = new DateTime(date('Y-m-d', strtotime($last_credit->date)));
                        $date2 = new DateTime(date('Y-m-d', strtotime($last_credit->close_date)));
                        
                        $interval = $date2->diff($date1);
                        $credit_period = $interval->days;
echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($date1, $date2, $credit_period);echo '</pre><hr />';                    
                        
                        if ($total_paid >= 15000)
                            $max_credit = 10000;
                        elseif ($total_paid >= 10000)
                            $max_credit = 8000;
                        elseif ($total_paid >= 6000)
                            $max_credit = 4000;
                        
                        
                        if ($credit_period <= 2) // 0-2 дня
                        {
                            $date2->add(new DateInterval('P90D'));
                            
                            $update = array(
                                'status' => 'completed',
                                'success' => 0,
                            );

                            if (time() < strtotime($date2->format('Y-m-d')))
                            {
                                if (empty($max_credit))
                                {
                                    $update['string_result'] = 'Антиразгон 0-2. Мараторий до '.$date2->format('d.m.Y');
    
                                    $this->orders->update_order($scoring->order_id, array(
//                                        'status' => 3,
//                                        'reason_id' => $this->reason_id,
//                                        'reject_reason' => $this->reject_reason,
                                        'antirazgon' => 1, 
                                        'antirazgon_date'=>$date2->format('Y-m-d')
                                    ));
    
//                                    $this->stop_last_scorings($order->user_id);
                                }
                                else
                                {
                                    $update['string_result'] = 'Антиразгон 0-2. Максимальная сумма '.$max_credit.'руб';
                                    $this->orders->update_order($scoring->order_id, array(
                                        'antirazgon' => 1, 
                                        'antirazgon_amount' => $max_credit
                                    ));                                
                                }
                            }
                        }
                        elseif ($credit_period <= 5)
                        {
                            $date2->add(new DateInterval('P7D'));
                            
                            $update = array(
                                'status' => 'completed',
                                'success' => 0,
                            );

                            if (time() < strtotime($date2->format('Y-m-d')))
                            {
                                if (empty($max_credit))
                                {
                                    $update['string_result'] = 'Антиразгон 3-5. Мараторий до '.$date2->format('d.m.Y');
    
                                    $this->orders->update_order($scoring->order_id, array(
//                                        'status' => 3,
//                                        'reason_id' => $this->reason_id,
//                                        'reject_reason' => $this->reject_reason,
                                        'antirazgon' => 2, 
                                        'antirazgon_date'=>$date2->format('Y-m-d')
                                    ));
    
//                                    $this->stop_last_scorings($order->user_id);
                                }
                                else
                                {
                                    $update['string_result'] = 'Антиразгон 3-5. Максимальная сумма '.$max_credit.'руб';
    
                                    $this->orders->update_order($scoring->order_id, array(
                                        'antirazgon' => 2, 
                                        'antirazgon_amount' => $max_credit
                                    ));                                
    
                                }
                            }
                        }
                        elseif ($credit_period <= 10)
                        {
                            $update = array(
                                'status' => 'completed',
                                'success' => 0,
                                'string_result' => 'Антиразгон 6-10. Рекомендуемая сумма '.$last_credit->amount.' руб',
                            );
                            
                        }
                        else
                        {
                            $update = array(
                                'status' => 'completed',
                                'success' => 1,
                                'string_result' => 'Проверка пройдена'
                            );
                            
                        }
                    }
                    else
                    {
                        $update = array(
                            'status' => 'completed',
                            'success' => 1,
                            'string_result' => 'Кредиты не найдены'
                        );
                        
                    }
                    
echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($update, $credit_period, $user->loan_history, $last_credit, $total_paid);echo '</pre><hr />';
                }
                else
                {
                    $update = array(
                        'status' => 'error',
                        'string_result' => 'Клиент не найден'
                    );
                    
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

    private function stop_last_scorings($user_id)
    {
        $this->db->query("
            UPDATE __scoring
            SET 
                status = 'stopped',
                string_result = 'Остановка по Антиразгону'
            WHERE user_id = ?
            AND status = 'new'
        ", $user_id);
    }
}