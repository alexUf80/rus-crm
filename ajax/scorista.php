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
    
    case 'create':
        
        $order_id = $core->request->get('order_id', 'integer');
        $order = $core->orders->get_order((int)$order_id);
        
        $result = $core->scorista->create($order_id);
        
        if ($result->status == 'OK')
        {
            $scoring = array(
                'user_id' => $order->user_id,
                'type' => 'scorista',
                'body' => '',
                'success' => 0,
                'scorista_id' => $result->requestid,
            );
            $core->scorings->add_scoring($scoring);
        }
        
    break;
    
    case 'result':
        
        $request_id = $core->request->get('request_id');
        
        $result = $core->scorista->get_result($request_id);
        
        if ($result->status == 'DONE')
        {
            $scoring_id = $core->scorings->get_scorista_scoring_id($request_id);
            
            $scoring = array(
                'body' => json_encode($result->data),
                'success' => (int) ($result->data->decision->decisionName == 'Одобрен'),
                'scorista_status' => $result->data->decision->decisionName,
                'scorista_ball' => $result->data->additional->summary->score,
            );
            $core->scorings->update_scoring($scoring_id, $scoring);
        }
        
    break;
    
    default:
        
        $result = new StdClass();
        $result->error = 'undefined_action';
    
endswitch;

echo json_encode($result);