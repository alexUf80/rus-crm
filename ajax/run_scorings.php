<?php
error_reporting(-1);
ini_set('display_errors', 'On');

chdir('..');

require 'autoload.php';

class RunScoringsApp extends Core
{
    private $response = array();
    
    public function run()
    {
    	$action = $this->request->get('action', 'string');
        
        switch ($action):
            
            case 'create':
            
                $type = $this->request->get('type', 'string');
                $order_id = $this->request->get('order_id', 'integer');
                
                $scoring_types = $this->scorings->get_types();
                
                if ($order = $this->orders->get_order($order_id))
                {
                    switch ($type):
                        
                        case 'free':
                            
                            foreach ($scoring_types as $scoring_type)
                            {
                                if ($scoring_type->type == 'first')
                                {
                                    $add_scoring = array(
                                        'user_id' => $order->user_id,
                                        'order_id' => $order->order_id,
                                        'type' => $scoring_type->name,
                                        'status' => 'new',
                                    );
                                    $this->scorings->add_scoring($add_scoring);
                                }
                            }
                            $this->response['success'] = 1;
                            
                        break;
                        
                        case 'all':
                        
                            foreach ($scoring_types as $scoring_type)
                            {
                                $add_scoring = array(
                                    'user_id' => $order->user_id,
                                    'order_id' => $order->order_id,
                                    'type' => $scoring_type->name,
                                    'status' => 'new',
                                );
                                $this->scorings->add_scoring($add_scoring);
                            }
                            $this->response['success'] = 1;
                            
                        break;
                        
                        case 'local_time':
                        case 'location':
                        case 'fms':
                        case 'fns':
                        case 'fssp':
                        case 'fssp2':
                        case 'scorista':
                        case 'juicescore':
                        case 'whitelist':
                        case 'blacklist':
                        case 'rfmlist':
                        case 'efrsb':
                        case 'antirazgon':
                        case 'nbki':
                        case 'nbkiscore':
                        case 'age':
                        case 'svo':
                        case 'idx':
                            
                            $add_scoring = array(
                                'user_id' => $order->user_id,
                                'order_id' => $order->order_id,
                                'type' => $type,
                                'status' => 'new',
                            );
                            $this->scorings->add_scoring($add_scoring);

                            $this->response['success'] = 1;
                            
                            
                        break;
                        
                    endswitch;
                }
                else
                {
                    $this->response['error'] = 'undefined_order';
                }
                
            break;
            
        endswitch;
    
        echo json_encode($this->response);
    }
    
}

$app = new RunScoringsApp();
$app->run();