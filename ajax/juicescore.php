<?php
error_reporting(-1);
ini_set('display_errors', 'On');

header("Content-type: application/json; charset=UTF-8");
header("Cache-Control: must-revalidate");
header("Pragma: no-cache");
header("Expires: -1");	

chdir('..');

require_once 'autoload.php';

$core = new Core();

switch ($core->request->get('action', 'string')):
    
    case 'run':
        
        $order_id = $core->request->get('order_id', 'integer');
        
        $order = $core->orders->get_order($order_id);
        
        if (!($scoring = $core->juicescore->getscore($order_id)))
        {
            $result = new StdClass();
            $result->error = 'undefined_order';
        }
        else
        {
            $scoring = (array)json_decode($scoring);
            if (isset($scoring['Predictors']))
                $scoring['Predictors'] = (array)$scoring['Predictors'];
                
            $result = $scoring;
            
            if (!empty($scoring['Success']))
            {
                $scoring = array(
                    'user_id' => $order->user_id,
                    'type' => 'juicescore',
                    'body' => serialize($scoring),
                    'success' => $scoring['AntiFraud score'] < 0.5,
                    'scorista_id' => '',
                );
                $core->scorings->add_scoring($scoring);
                
            }
            
        }
        
        
    break;
    
    default:
        
        $result = new StdClass();
        $result->error = 'undefined_action';
    
endswitch;

echo json_encode($result);