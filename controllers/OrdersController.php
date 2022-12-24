<?php

error_reporting(0);
ini_set('display_errors', 'On');
class OrdersController extends Controller
{
    public function fetch()
    {
        $items_per_page = 20;

        $filter = array();

        if (!($period = $this->request->get('period')))
            $period = 'all';

        switch ($period):
            case 'today':
                $filter['date_from'] = date('Y-m-d');
                break;

            case 'yesterday':
                $filter['date_from'] = date('Y-m-d', time() - 86400);
                $filter['date_to'] = date('Y-m-d', time() - 86400);
                break;

            case 'month':
                $filter['date_from'] = date('Y-m-01');
                break;

            case 'year':
                $filter['date_from'] = date('Y-01-01');
                break;

            case 'all':
                $filter['date_from'] = null;
                $filter['date_to'] = null;
                break;


            case 'optional':
                $daterange = $this->request->get('daterange');
                $filter_daterange = array_map('trim', explode('-', $daterange));
                $filter['date_from'] = date('Y-m-d', strtotime($filter_daterange[0]));
                $filter['date_to'] = date('Y-m-d', strtotime($filter_daterange[1]));
                break;

        endswitch;

        if ($this->request->method('post')) {
            switch ($this->request->post('action', 'string')):

                case 'send_sms':
                    $this->send_sms_action();
                    break;

            endswitch;
        }
        $this->design->assign('period', $period);

        if ($this->request->get('my'))
        {
            $filter['manager_id'] = array($this->manager->id);
        }

        if (!($sort = $this->request->get('sort', 'string'))) {
            $sort = 'order_id_desc';
        }
        $filter['sort'] = $sort;
        $this->design->assign('sort', $sort);

        if ($search = $this->request->get('search')) {
            $filter['search'] = array_filter($search);
            $this->design->assign('search', array_filter($search));
        }

        if ($status = $this->request->get('status')) {
            $filter['status'] = $status;
            $this->design->assign('filter_status', $status);
        }

        if ($filter_client = $this->request->get('client')) {
            $filter['client'] = $filter_client;
            $this->design->assign('filter_client', $filter_client);
        }

        $current_page = $this->request->get('page', 'integer');
        $current_page = max(1, $current_page);
        $this->design->assign('current_page_num', $current_page);

        $orders_count = $this->orders->count_orders($filter);

        $pages_num = ceil($orders_count / $items_per_page);
        $this->design->assign('total_pages_num', $pages_num);
        $this->design->assign('total_orders_count', $orders_count);

        $filter['page'] = $current_page;
        $filter['limit'] = $items_per_page;

        $orders = array();
        foreach ($this->orders->get_orders($filter) as $order) {
            $order->scorings = array();
            $order->penalties = array();
            foreach ($this->scorings->get_scorings(array('order_id' => $order->order_id)) as $sc)
                $order->scorings[$sc->type] = $sc;
            if (empty($order->scorings) || !count($order->scorings)) {
                $order->scorings_result = 'Не проводился';
            } else {
                $order->scorings_result = 'Пройден';
                foreach ($order->scorings as $scoring) {
                    if (!$scoring->success)
                        $order->scorings_result = 'Не пройден: ' . $scoring->type;
                }
            }

            if (!empty($order->contract_id))
                $order->contract = $this->contracts->get_contract((int)$order->contract_id);

            $orders[$order->order_id] = $order;
        }

        if ($penalties = $this->penalties->get_penalties(array('order_id' => array_keys($orders)))) {
            foreach ($penalties as $p) {
                if (isset($orders[$p->order_id]))
                    $orders[$p->order_id]->penalties[] = $p;
            }
        }

        foreach ($orders as $order) {
            $user_close_orders = $this->orders->get_orders(array(
                'user_id' => $order->user_id,
                'type' => 'base',
                'status' => array(7)
            ));
            $order->have_crm_closed = !empty($user_close_orders);


        }

        $managers = array();
        foreach ($this->managers->get_managers() as $m)
            $managers[$m->id] = $m;
        $this->design->assign('managers', $managers);

        $scoring_types = $this->scorings->get_types();
        $this->design->assign('scoring_types', $scoring_types);

        $reasons = [];
        foreach ($this->reasons->get_reasons() as $r)
            $reasons[$r->id] = $r;
        $this->design->assign('reasons', $reasons);
        
        $this->design->assign('orders', $orders);

        $sms_templates = $this->sms->get_templates();
        $this->design->assign('sms_templates', $sms_templates);

        return $this->design->fetch('orders.tpl');
    }

    private function num2word($num, $words)
    {
        $num = $num % 100;
        if ($num > 19) {
            $num = $num % 10;
        }
        switch ($num) {
            case 1: {
                return($words[0]);
            }
            case 2: case 3: case 4: {
                return($words[1]);
            }
            default: {
                return($words[2]);
            }
        }
    }

    private function send_sms_action()
    {

        $user_id = $this->request->post('user_id', 'integer');
        $order_id = $this->request->post('order_id', 'integer');
        $template_id = $this->request->post('template_id', 'integer');
        $manager_id = $this->request->post('manager_id', 'integer');
        $text_sms = $this->request->post('text_sms', 'string');

        $user = $this->users->get_user((int)$user_id);

        $template = null;

        if ($text_sms) {

            $template = $text_sms;
        }

        if ($template_id) {

            $template = $this->sms->get_template($template_id);
            $template = $template->template;
        }

        if (!empty($order_id)) {
            $order = $this->orders->get_order($order_id);

            if ($order->contract_id) {
                $code = $this->helpers->c2o_encode($order->contract_id);
                $payment_link = $this->config->front_url . '/p/' . $code;
                $contract = $this->contracts->get_contract($order->contract_id);
                $osd_sum = $contract->loan_body_summ + $contract->loan_percents_summ + $contract->loan_charge_summ + $contract->loan_peni_summ;
            }

            $str_params =
                [
                    '{$payment_link}' => $payment_link,
                    '$firstname' => $user->firstname,
                    '$fio' => "$user->lastname $user->firstname $user->patronymic",
                    '$prolongation_sum' => $contract->loan_percents_summ,
                    '$final_sum' => $osd_sum
                ];

            $template = strtr($template, $str_params);
        }

        $resp = $this->sms->send(
        /*'79276928586'*/
            $user->phone_mobile,
            $template
        );

        $this->sms->add_message(array(
            'user_id' => $user->id,
            'order_id' => $order_id,
            'phone' => $user->phone_mobile,
            'message' => $template,
            'created' => date('Y-m-d H:i:s'),
        ));

        $this->changelogs->add_changelog(array(
            'manager_id' => $manager_id,
            'created' => date('Y-m-d H:i:s'),
            'type' => 'send_sms',
            'old_values' => '',
            'new_values' => $template,
            'user_id' => $user->id,
            'order_id' => $order_id,
        ));
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($resp);echo '</pre><hr />';
        $this->json_output(array('success' => true));
    }

}