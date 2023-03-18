<?php

use PhpOffice\PhpSpreadsheet\IOFactory;

error_reporting(-1);
ini_set('display_errors', 'On');

class TestController extends Controller
{
    public function fetch()
    {
        $orders = OrdersORM::where('status', 5)->get();

        foreach ($orders as $order) {
            Onec::sendRequest(['method' => 'send_loan', 'params' => $order->id]);
        }
    }

    public function services()
    {
        $services = OperationsORM::whereIn('type', ['INSURANCE', 'INSURANCE_BC', 'REJECT_REASON'])->get();

        foreach ($services as $service) {
            $contract = ContractsORM::where('order_id', $service->order_id)->first();

            $item = new stdClass();
            $item->user_id = $service->user_id;
            $item->insurance_cost = $service->amount;
            $item->number = $contract->number;
            $item->operation_id = $service->id;
            $item->order_id = $service->order_id;

            if (in_array($service->type, ['INSURANCE', 'INSURANCE_BC']))
                $item->is_insurance = 1;
            else
                $item->is_insurance = 0;

            if (empty($contract->number) && $item->is_insurance == 1)
                continue;

            Onec::sendRequest(['method' => 'send_services', 'params' => $item]);
        }

        exit;
    }
}