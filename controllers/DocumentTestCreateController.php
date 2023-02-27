<?php

class DocumentTestCreateController extends Controller
{
    public function fetch()
    {

        $user_id = $this->request->get('user_id', 'integer');
        $name_document = $this->request->get('name_document','string');
        //echo '<pre>';print_r($contract);echo '</pre>';
        $user = $this->users->get_user($user_id);
        $query = $this->db->placehold("
            SELECT * 
            FROM __contracts
            WHERE user_id = ?
        ", (int)$user->id);
        $this->db->query($query);
        $result = $this->db->result();
        $contract = $result;

        // $query = $this->db->placehold("
        //     SELECT *
        //     FROM _contracts
        //     WHERE user_id = 27736
        // ");
        // $this->db->query($query);
        
        // $results = $this->db->results();
        //echo '<pre>';print_r($results);echo '</pre>';
        // $contract = ContractsORM::where('user_id', $user_id)->get();
        // $contract = $contract[count($contract)-1];
        // $params = [];

        $contract_order = $this->orders->get_order((int)$user->order_id);
        //$user = $this->users->get_user($user_id);

        $passport_series = substr(str_replace(array(' ', '-'), '', $contract_order->passport_serial), 0, 4);
        $passport_number = substr(str_replace(array(' ', '-'), '', $contract_order->passport_serial), 4, 6);
        $subdivision_code = $contract_order->subdivision_code;
        $passport_issued = $contract_order->passport_issued;
        $passport_date = $contract_order->passport_date;


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
            'birth_place' => $user->birth_place,
            'phone' => $contract_order->phone_mobile,
            'regaddress_full' => $regaddress_full,
            'passport_series' => $passport_series,
            'passport_number' => $passport_number,
            'passport_serial' => $contract_order->passport_serial,
            'subdivision_code' => $subdivision_code,
            'passport_issued' => $passport_issued,
            'passport_date' => $passport_date,

            'regindex' => $contract_order->Regindex,
            'regregion' => $contract_order->Regregion,
            'regcity' => $contract_order->Regcity,
            'reglocality' => $contract_order->Reglocality,
            'reghousing' => $contract_order->Reghousing,
            'regbuilding' => $contract_order->Regbuilding,
            'regroom' => $contract_order->Regroom,
            
            'created' => date('Y-m-d H:i:s'),
            'base_percent' => $contract->base_percent,
            'amount' => $contract->amount,
            'number' => $contract->number,
            'order_created' => $contract_order->date,
            'loan_body_summ' => $contract->loan_body_summ,
            'return_date' => $contract->return_date,
            'accept_code' =>   $contract->accept_code,

            'return_amount_percents' => $contract->loan_percents_summ,

            'faktregindex' => $user->Faktregindex,
            'faktregion' => $user->Faktregion,
            'faktcity' => $user->Faktcity,
            'faktstreet' => $user->Faktstreet,
            'fakthousing' => $user->Fakthousing,
            'faktbuilding' => $user->Faktbuilding,
            'faktroom' => $user->Faktroom,
            

            'inn' => $user->inn,
            'snils' => $user->snils,
            'profession' => $user->profession,
            'workplace' => $user->workplace,
            'workaddress' => $user->workaddress,
            'phone' => $user->phone_mobile,

        );

        // $document_params['return_date'] = $new_return_date;
        // $document_params['return_date_day'] = date('d', strtotime($new_return_date));
        // $document_params['return_date_month'] = date('m', strtotime($new_return_date));
        // $document_params['return_date_year'] = date('Y', strtotime($new_return_date));
        // $document_params['period'] = $period;
        echo '<pre>';print_r($contract);echo '</pre>';
        //  echo '<pre>';print_r($contract->loan_body_summ);echo '</pre>';
        //  echo '<pre>';print_r($regaddress_full);echo '</pre>';
        //  echo '<pre>';print_r($contract_order);echo '</pre>';
         
        //  echo '<pre>';print_r($document_params);echo '</pre>';


         //$document_params2 = [];
         $this->documents->create_document(array(
            'user_id' => $user_id,
            'order_id' => $contract->order_id,
            'contract_id' => $contract->id,
            'type' => $name_document,
            'params' => json_encode($document_params)
        ));
            return 1;
        
    }	
}