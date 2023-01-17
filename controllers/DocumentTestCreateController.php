<?php

class CDocumentTestCreateController extends Controller
{
    public function fetch()
    {
        $user_id = $this->request->get('user_id', 'integer');
        $name_document = $this->request->get('name_document','string');


        $contract = $this->contracts->get_contract($user_id);
        $params = [];

        echo '<pre>';print_r($contract);echo '</pre>';
        // $document =
        //     [
        //         'user_id' => $contract->user_id,
        //         'order_id' => $contract->order_id,
        //         'contract_id' => $contract->id,
        //         'type' => 'DOP_RESTRUCT',
        //         'params' => json_encode($params),
        //         'created' => date('Y-m-d H:i:s')
        //     ];

        // $this->documents->create_document($document);
        
    }	
}