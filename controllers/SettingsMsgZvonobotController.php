<?php

class SettingsMsgZvonobotController extends Controller
{
    public function fetch()
    {
        if ($this->request->method('post'))
        {
            
            $this->settings->msg_zvonobot_d0 = $this->request->post('msg_zvonobot_d0');
            $this->settings->msg_zvonobot_d1 = $this->request->post('msg_zvonobot_d1');
            $this->settings->msg_zvonobot_d2 = $this->request->post('msg_zvonobot_d2');
            $this->settings->msg_zvonobot_d3 = $this->request->post('msg_zvonobot_d3');

            $this->settings->zvonobot_period_calls = $this->request->post('zvonobot_period_calls');


        }
        
        /*$user = $this->users->get_user(184540);

        $counter = $user->notify_counter;
        $now = new DateTime(); // текущее время на сервере

        //var_dump($counter);
        //var_dump($counter);
        //var_dump($counter);
        //var_dump($counter);
        //var_dump($counter);

        if (is_null($counter)) {
            $counter = ['zvonobot' => [
                                'month' => ['number' => $now->format('m'), 'count' => 1],
                                'week' => ['number' => $now->format('W'), 'count' => 1],
                                'day' => ['number' => $now->format('z'), 'count' => 1],
                                'last_call' => $now->format('Y-m-d H:i:s')
                                ]];
        }

        //преобразуем в json перед сохранением в бд
        $counter = json_encode($counter); 
        
        $this->users->update_user(184540, array('notify_counter' => $counter));*/

        return $this->design->fetch('settings_msg_zvonobot.tpl');
    }
}