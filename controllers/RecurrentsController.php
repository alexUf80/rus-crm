<?php

class RecurrentsController extends Controller
{
    public function fetch()
    {
        if ($this->request->method('post'))
        {
            $action = $this->request->post('action');

            if ($action == 'create') {
                RecurrentConfigORM::where('actual', '=', 1)->update(['actual' => 0]);
                RecurrentConfigORM::create([
                    'hour_time' => $this->request->post('hour_time'),
                    'day_avans' => $this->request->post('day_avans'),
                    'day_zp' => $this->request->post('day_zp'),
                    'days' => $this->request->post('days'),
                    'count_months' => $this->request->post('count_months'),
                    'max_count' => $this->request->post('max_count'),
                    'attempts' => serialize($this->request->post('attempts')),
                ]);
                echo json_encode(['status' => 'ok']);
                exit();
            }

            if ($action == 'get_actual_config') {
                $config = RecurrentConfigORM::query()->where('actual', '=', 1)->first();
                if ($config) {
                    $config->attempts = unserialize($config->attempts);
                    echo json_encode(['status' => 'ok', 'config' => $config]);
                    exit;
                }
                echo json_encode(['status' => 'error']);
                exit;
            }
        }


        return $this->design->fetch('recurrents.tpl');
    }


}