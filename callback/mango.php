<?php
error_reporting(-1);
ini_set('display_errors', 'On');

chdir('..');
require_once 'autoload.php';

class MangoCallback extends Core
{
    public function __construct()
    {
    	parent::__construct();
        
        
        if ($this->request->method('post'))
            $this->run();
        else
            exit('ERROR METHOD');
    }
    
    private function run()
    {
        if ($json = $this->request->post('json'))
        {
            $data = json_decode($json);
            
            $request_method = str_replace('mango_callback', $SERVER['REQUEST_URI']);
            $request_method = trim(trim($request_method), '/');
            
            switch ($request_method):
                
                case 'events/call':
                    $this->call_action($data);
                break;
                
                case 'events/summary':
                    $this->summary_action($data);
                break;
                
                case 'events/record/added':
                    $this->record_action($data);
                break;
                
                case 'result/callback':
                    $this->result_action($data);
                break;
                
                    
            endswitch;
        
            
            $this->soap1c->logging($request_method, $_SERVER['REQUEST_URI'], $data, '', 'mango.txt');
            
        }
    }
    
    private function command2call($command_id)
    {
        if (strpos($command_id, 'ID_') !== false)
        {
            return str_replace('ID_', '', $command_id);
        }
        return NULL;
    }
    
    private function result_action($data)
    {
        if (!empty($data->command_id))
        {
            if ($mangocall_id = $this->command2call($data->command_id))            
                $this->mango->update_call($mangocall_id, array('result_code' => $data->result));
        }
    }
    
    private function call_action($data)
    {
        if ($data->call_state == 'Appeared')
        {
            if (empty($data->command_id) || !($mangocall_id = $this->command2call($data->command_id)))
                $mangocall_id = $this->mango->get_call_id($data->entry_id);
            
            $update = array(
                'entry_id' => $data->entry_id,
                'call_id' => $data->call_id,
                'from_extension' => isset($data->from->extension) ? $data->from->extension : '',
                'from_number' => isset($data->from->number) ? $data->from->number : '',
                'to_extension' => isset($data->to->extension) ? $data->to->extension : '',
                'to_number' => isset($data->to->number) ? $data->to->number : '',
            );
            
            if (!empty($mangocall_id))
            {
                $this->mango->update_call($mangocall_id, $update);                
            }
            else
            {
                $this->mango->add_call($update);
            }
        }
    }
    
    private function summary_action($data)
    {
        if ($mangocall_id = $this->mango->get_call_id($data->entry_id))
        {
            $mangocall = $this->mango->get_call($mangocall_id);
            
            $duration = $data->talk_time > 0 ? $data->end_time - $data->talk_time : 0;
            
            $this->mango->update_call($mangocall_id, array(
                'call_direction' => isset($data->call_direction) ? $data->call_direction : '',
                'create_time' => $data->create_time,
                'forward_time' => $data->forward_time,
                'talk_time' => $data->talk_time,
                'end_time' => $data->end_time,
                'entry_result' => $data->entry_result,
                'disconnect_reason' => $data->disconnect_reason,
                'duration' => $duration,
            ));
            
            if (!empty($mangocall->order_id))
            {
                if (!empty($duration))
                {
                    /*
                    $order = $this->orders->get_order((int)$mangocall->order_id);
                    if (empty($order->call_date))
                    {
                        $this->orders->update_order($mangocall->order_id, array(
                            'call_date' => $mangocall->created
                        ));
                    }
                    */
                }
            }
        }

    }

    private function record_action($data)
    {
        if ($mangocall_id = $this->mango->get_call_id($data->entry_id))
        {
            if ($record_link = $this->mango->get_record_link($data->recording_id))
            {
                $file = file_get_contents($record_link);
                do {
                    $filename = md5(rand().microtime()).'.mp3';
                } while (file_exists($this->config->root_dir.'files/calls/'.$filename));
                file_put_contents($this->config->root_dir.'files/calls/'.$filename, $file);
            }
            $this->mango->update_call($mangocall_id, array(
                'recording_id' => $data->recording_id,
                'record_file' => $filename
            ));
        }
    }
}

new MangoCallback();