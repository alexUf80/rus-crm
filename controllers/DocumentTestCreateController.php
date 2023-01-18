<?php

class DocumentTestCreateController extends Controller
{
    public function fetch()
    {

        $user_id = $this->request->get('user_id', 'integer');
        $name_document = $this->request->get('name_document','string');
        //echo '<pre>';print_r($contract);echo '</pre>';
        $contracts = $this->contracts->get_contracts(['user_id' => $user_id]);
        echo '<pre>';print_r($contracts);echo '</pre>';
        $contract = $this->contracts->get_contract(113921);
        // $params = [];

        $contract_order = $this->orders->get_order((int)$contract->order_id);

        $regaddress_full = empty($contract_order->Regindex) ? '' : $contract_order->Regindex . ', ';
        $regaddress_full .= trim($contract_order->Regregion . ' ' . $contract_order->Regregion_shorttype);
        $regaddress_full .= empty($contract_order->Regcity) ? '' : trim(', ' . $contract_order->Regcity . ' ' . $contract_order->Regcity_shorttype);
        $regaddress_full .= empty($contract_order->Regdistrict) ? '' : trim(', ' . $contract_order->Regdistrict . ' ' . $contract_order->Regdistrict_shorttype);
        $regaddress_full .= empty($contract_order->Reglocality) ? '' : trim(', ' . $contract_order->Reglocality . ' ' . $contract_order->Reglocality_shorttype);
        $regaddress_full .= empty($contract_order->Reghousing) ? '' : ', д.' . $contract_order->Reghousing;
        $regaddress_full .= empty($contract_order->Regbuilding) ? '' : ', стр.' . $contract_order->Regbuilding;
        $regaddress_full .= empty($contract_order->Regroom) ? '' : ', к.' . $contract_order->Regroom;

        $passport_series = substr(str_replace(array(' ', '-'), '', $contract_order->passport_serial), 0, 4);
        $passport_number = substr(str_replace(array(' ', '-'), '', $contract_order->passport_serial), 4, 6);
        $subdivision_code = $contract_order->subdivision_code;
        $passport_issued = $contract_order->passport_issued;
        $passport_date = $contract_order->passport_date;

        $document_params = array(
            'lastname' => $contract_order->lastname,
            'firstname' => $contract_order->firstname,
            'patronymic' => $contract_order->patronymic,
            'birth' => $contract_order->birth,
            'phone' => $contract_order->phone_mobile,
            'regaddress_full' => $regaddress_full,
            'passport_series' => $passport_series,
            'passport_number' => $passport_number,
            'passport_serial' => $contract_order->passport_serial,
            'subdivision_code' => $subdivision_code,
            'passport_issued' => $passport_issued,
            'passport_date' => $passport_date,
            
            'created' => date('Y-m-d H:i:s'),
            'base_percent' => $contract->base_percent,
            'amount' => $contract->amount,
            'number' => $contract->number,
            'order_created' => $contract_order->date,

        );

        // $document_params['return_date'] = $new_return_date;
        // $document_params['return_date_day'] = date('d', strtotime($new_return_date));
        // $document_params['return_date_month'] = date('m', strtotime($new_return_date));
        // $document_params['return_date_year'] = date('Y', strtotime($new_return_date));
        // $document_params['period'] = $period;

        // echo '<pre>';print_r($contract);echo '</pre>';

        
        $document =
            [
                'user_id' => $contract->user_id,
                'order_id' => $contract->order_id,
                'contract_id' => $contract->id,
                'type' => $name_document,
                'params' => $document_params,
                'created' => date('Y-m-d H:i:s')
            ];

            // $this->documents->create_document($document);
            return 1;
        
    }	
}