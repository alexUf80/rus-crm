<?php

class Location_scoring extends Core
{
    private $user_id;
    private $order_id;
    private $audit_id;
    private $type;
    private $exception_regions;
    
    public function run_scoring($scoring_id)
    {
        $update = array();
        
    	$scoring_type = $this->scorings->get_type('location');
        
        if ($scoring = $this->scorings->get_scoring($scoring_id))
        {
            if ($order = $this->orders->get_order((int)$scoring->order_id))
            {
                if (empty($order->Regregion))
                {
                    $update = array(
                        'status' => 'error',
                        'string_result' => 'в заявке не указан регион регистрации'
                    );
                }
                else
                {
                    $exception_regions = array_map('trim', explode(',', $scoring_type->params['regions']));
                
                    $score = !in_array(mb_strtolower(trim($order->Regregion), 'utf8'), $exception_regions);
                
                    $update = array(
                        'status' => 'completed',
                        'body' => serialize(array('region' => $order->Regregion)),
                        'success' => $score
                    );
                    if ($score)
                        $update['string_result'] = 'Допустимый регион: '.$order->Regregion;
                    else
                        $update['string_result'] = 'Недопустимый регион: '.$order->Regregion;

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
        
        $this->type = $this->scorings->get_type('location');
        $this->exception_regions = explode(',', $this->type->params['regions']);
        
        
        $user = $this->users->get_user((int)$user_id);
        
        return $this->scoring($user->Regregion);        
    }

    private function scoring($region_name)
    {

        $region_user = explode(' ', $region_name);

        $score = 0;

        foreach ($this->exception_regions as $region) {
            foreach ($region_user as $value) {
                if (mb_strtolower($value, 'UTF-8') == $region) {
                    $score = 1;
                }
            }
        }
        
        $add_scoring = array(
            'user_id' => $this->user_id,
            'audit_id' => $this->audit_id,
            'type' => 'location',
            'body' => $region_name,
            'success' => (int)$score
        );
        if ($score == 0)
        {
            $add_scoring['string_result'] = 'Допустимый регион: '.$region_name;
        }
        if($score == 1)
        {
            $add_scoring['string_result'] = 'Недопустимый регион: '.$region_name;
        }
        
        $this->scorings->add_scoring($add_scoring);
        
        return $score;
    }

}