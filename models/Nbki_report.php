<?php

class Nbki_report extends Core
{
    private $username = '1401SS000002';
    private $authorization_code = '934kjnG@';


    public function send_operations($operations)
    {
        $orders = [];
        $items = [];
        foreach ($operations as $operation) {
            $format_date = date('Ymd', strtotime($operation->created));
            $operation_type = $operation->type == 'P2P' ? 'P2P' : 'PAY';

            if ($operation->amount <= 0)
                continue;

            if (!isset($items[$operation->order_id])) {
                $orders[$operation->order_id] = $this->orders->get_order($operation->order_id);

                $contract = $this->contracts->get_contract($orders[$operation->order_id]->contract_id);

                if(empty($contract->uid))
                    continue;

                $orders[$operation->order_id]->contract = $this->contracts->get_contract($orders[$operation->order_id]->contract_id);

                $orders[$operation->order_id]->payment_amount = '0';
                $orders[$operation->order_id]->principal_payment_amount = '0';
                $orders[$operation->order_id]->interest_payment_amount = '0';
                $orders[$operation->order_id]->other_payment_amount = '0';
                $orders[$operation->order_id]->total_amount = '0';
                $orders[$operation->order_id]->principal_total_amount = '0';
                $orders[$operation->order_id]->interest_total_amount = '0';
                $orders[$operation->order_id]->other_total_amount = '0';
                $orders[$operation->order_id]->amount_keep_code = '3';
                $orders[$operation->order_id]->terms_due_code = '1';
                $orders[$operation->order_id]->days_past_due = '0';
                $orders[$operation->order_id]->closed = '0';
            }

            if (!isset($items[$operation_type]))
                $items[$operation_type] = [];

            if (!isset($items[$operation_type][$format_date]))
                $items[$operation_type][$format_date] = [];

            if (!isset($items[$operation_type][$format_date][$operation->order_id])) {
                $items[$operation_type][$format_date][$operation->order_id] = $orders[$operation->order_id];
                $items[$operation_type][$format_date][$operation->order_id]->operation = $operation;
            }


            if ($operation_type == 'PAY') {
                $items[$operation_type][$format_date][$operation->order_id]->payment_date = date('d.m.Y', strtotime($operation->created));

                if ($operation->type == 'PAY') {
                    $transaction = $this->transactions->get_transaction($operation->transaction_id);
                    $items[$operation_type][$format_date][$operation->order_id]->principal_payment_amount += $transaction->loan_body_summ;
                    $items[$operation_type][$format_date][$operation->order_id]->interest_payment_amount += $transaction->loan_percents_summ;
                    $items[$operation_type][$format_date][$operation->order_id]->other_payment_amount += $transaction->loan_peni_summ;

                    $items[$operation_type][$format_date][$operation->order_id]->payment_amount += $transaction->loan_body_summ + $transaction->loan_percents_summ + $transaction->loan_peni_summ;
                    if ($operation->loan_body_summ <= 0)
                        $items[$operation_type][$format_date][$operation->order_id]->closed = 1;
                } else {
                    $items[$operation_type][$format_date][$operation->order_id]->payment_amount += $operation->amount;
                    if ($operation->loan_body_summ <= 0)
                        $items[$operation_type][$format_date][$operation->order_id]->closed = 1;
                }
                $items[$operation_type][$format_date][$operation->order_id]->amount_keep_code = '1';
                $items[$operation_type][$format_date][$operation->order_id]->terms_due_code = '2';
                $items[$operation_type][$format_date][$operation->order_id]->days_past_due = '0';
            }
        }

        foreach ($items['PAY'] as $operation_date => $orders) {
            foreach ($orders as $order) {
                $query_operations = $this->operations->get_operations(['contract_id' => $order->contract->id, 'type' => ['PAY']]);

                if (!empty($query_operations)) {
                    foreach ($query_operations as $query_operation) {
                        if (strtotime(date('Y-m-d', strtotime($query_operation->created))) <= strtotime(date('Y-m-d', strtotime($operation->created)))) {
                            if ($query_operation->type == 'PAY') {
                                if ($query_transaction = $this->transactions->get_transaction($query_operation->transaction_id)) {
                                    $order->total_amount += $query_transaction->loan_body_summ + $query_transaction->loan_percents_summ + $query_transaction->loan_peni_summ;
                                    $order->principal_total_amount += $query_transaction->loan_body_summ;
                                    $order->interest_total_amount += $query_transaction->loan_percents_summ;
                                    $order->other_total_amount += $query_transaction->loan_peni_summ;
                                }
                            }
                        }
                    }
                }
            }
        }

        $wrapper = $this->get_wrapper($items);

        $resp = $this->send($wrapper, 'v2/report/');

        return $resp;
    }

    private function get_wrapper($items)
    {
        $wrapper = new StdClass();
        $wrapper->MANY_EVENTS = [];

        $HEADER = new StdClass();
        $HEADER->username = $this->username;
        $HEADER->password = $this->authorization_code;

        $wrapper->HEADER = $HEADER;

        if (!empty($items['P2P'])) {
            foreach ($items['P2P'] as $operation_date => $orders) {
                foreach ($orders as $order)
                    $wrapper->MANY_EVENTS[] = $this->get_p2p_item($order);
            }
        }

        if (!empty($items['PAY'])) {
            foreach ($items['PAY'] as $operation_date => $orders) {
                foreach ($orders as $order)
                    $wrapper->MANY_EVENTS[] = $this->get_pay_item($order);
            }
        }

        return $wrapper;
    }

    private function get_pay_item($order)
    {
        $contract = $this->contracts->get_contract($order->contract_id);

        $passport_serial = str_replace([' ', '-'], '', $order->passport_serial);
        $passport_series = substr($passport_serial, 0, 4);
        $passport_number = substr($passport_serial, 4, 6);


        $data = new StdClass();

        $GROUPHEADER = new StdClass();
        $GROUPHEADER->event_number = "2.3";
        $GROUPHEADER->operation_code = "B";
        $GROUPHEADER->event_date = date('d.m.Y', strtotime($order->operation->created));

        $data->GROUPHEADER = $GROUPHEADER;


        $C1_NAME = new StdClass();
        $C1_NAME->surname = $this->clearing($order->lastname);
        $C1_NAME->name = $this->clearing($order->firstname);
        $C1_NAME->patronymic = $this->clearing($order->patronymic);

        $data->C1_NAME = $C1_NAME;


        $C2_PREVNAME = new StdClass();
        $C2_PREVNAME->is_prev_name = '0';

        $data->C2_PREVNAME = $C2_PREVNAME;


        $C3_BIRTH = new StdClass();
        $C3_BIRTH->birth_date = date('d.m.Y', strtotime($order->birth));
        $C3_BIRTH->country_code = '643';
        $C3_BIRTH->birth_place = $this->clearing($order->birth_place);

        $data->C3_BIRTH = $C3_BIRTH;


        $C4_ID = new StdClass();
        $C4_ID->country_code = '643';
        $C4_ID->document_code = '21';
        $C4_ID->series_number = $passport_series;
        $C4_ID->document_number = $passport_number;
        $C4_ID->issue_date = date('d.m.Y', strtotime($order->passport_date));
        $C4_ID->issued_by_division = $this->clearing($order->passport_issued);
        $C4_ID->division_code = $order->subdivision_code;

        $data->C4_ID = $C4_ID;


        $C5_PREVID = new StdClass();
        $C5_PREVID->is_prev_document = '0';

        $data->C5_PREVID = $C5_PREVID;


        $C6_REGNUM = new StdClass();
        $C6_REGNUM->taxpayer_code = '1';
        $C6_REGNUM->taxpayer_number = empty($order->inn) ? '000000000000' : $order->inn;

        $data->C6_REGNUM = $C6_REGNUM;


        $C17_UID = new StdClass();
        $C17_UID->uuid = $contract->uid;

        $data->C17_UID = $C17_UID;


        $C18_TRADE = new StdClass();
        $C18_TRADE->owner_indicator_code = '1';
        $C18_TRADE->opened_date = date('d.m.Y', strtotime($contract->inssuance_date));
        $C18_TRADE->trade_type_code = '1';
        $C18_TRADE->load_kind_code = '1';
        $C18_TRADE->account_type_code = '14';
        $C18_TRADE->is_consumer_loan = '1';
        $C18_TRADE->has_card = '1';
        $C18_TRADE->is_novation = '0';
        $C18_TRADE->is_money_source = '1';
        $C18_TRADE->is_money_borrower = '1';
        $C18_TRADE->close_date = date('d.m.Y', strtotime($contract->return_date));

        $data->C18_TRADE = $C18_TRADE;


        $C19_ACCOUNTAMT = new StdClass();
        $C19_ACCOUNTAMT->credit_limit = str_replace('.', ',', sprintf("%01.2f", $contract->amount));
        $C19_ACCOUNTAMT->currency_code = 'RUB';

        $data->C19_ACCOUNTAMT = $C19_ACCOUNTAMT;


        $interest_terms_amount = ($contract->amount * $contract->base_percent / 100 * $contract->period);
        $C21_PAYMTCONDITION = new StdClass();
        $C21_PAYMTCONDITION->principal_terms_amount = str_replace('.', ',', sprintf("%01.2f", $contract->amount));
        $C21_PAYMTCONDITION->principal_terms_amount_date = date('d.m.Y', strtotime($contract->return_date));
        $C21_PAYMTCONDITION->interest_terms_amount = str_replace('.', ',', sprintf("%01.2f", $interest_terms_amount));
        $C21_PAYMTCONDITION->interest_terms_amount_date = date('d.m.Y', strtotime($contract->return_date));
        $C21_PAYMTCONDITION->terms_frequency_code = '3';
        $C21_PAYMTCONDITION->interest_payment_due_date = date('d.m.Y', strtotime($contract->return_date));

        $data->C21_PAYMTCONDITION = $C21_PAYMTCONDITION;


        $C22_OVERALLVAL = new StdClass();
        $C22_OVERALLVAL->total_credit_amount_interest = sprintf("%01.2f", $contract->base_percent * 365);
        $C22_OVERALLVAL->total_credit_amount_monetary = str_replace('.', ',', sprintf("%01.2f", $interest_terms_amount));
        $C22_OVERALLVAL->total_credit_amount_date = date('d.m.Y', strtotime($contract->inssuance_date));

        $data->C22_OVERALLVAL = $C22_OVERALLVAL;


        $C24_FUNDDATE = new StdClass();
        $C24_FUNDDATE->funding_date = date('d.m.Y', strtotime($contract->inssuance_date));

        $data->C24_FUNDDATE = $C24_FUNDDATE;


        $C25_ARREAR = new StdClass();
        $C25_ARREAR->has_arrear = '1';
        $C25_ARREAR->start_amount_outstanding = str_replace('.', ',', sprintf("%01.2f", $contract->amount));
        $C25_ARREAR->is_last_payment_due = '1';
        $C25_ARREAR->amount_outstanding = str_replace('.', ',', sprintf("%01.2f", $contract->amount + $interest_terms_amount));
        $C25_ARREAR->principal_amount_outstanding = str_replace('.', ',', sprintf("%01.2f", $contract->amount));
        $C25_ARREAR->interest_amount_outstanding = str_replace('.', ',', sprintf("%01.2f", $interest_terms_amount));
        $C25_ARREAR->other_amount_outstanding = '0,00';
        $C25_ARREAR->calculation_date = date('d.m.Y', strtotime($contract->inssuance_date));

        $data->C25_ARREAR = $C25_ARREAR;


        $C26_DUEARREAR = new StdClass();
        $C26_DUEARREAR->start_date = date('d.m.Y', strtotime($contract->inssuance_date));
        $C26_DUEARREAR->is_last_payment_due = '1';
        $C26_DUEARREAR->amount_outstanding = str_replace('.', ',', sprintf("%01.2f", $contract->amount + $interest_terms_amount));
        $C26_DUEARREAR->principal_amount_outstanding = str_replace('.', ',', sprintf("%01.2f", $contract->amount));
        $C26_DUEARREAR->interest_amount_outstanding = str_replace('.', ',', sprintf("%01.2f", $interest_terms_amount));
        $C26_DUEARREAR->other_amount_outstanding = '0,00';
        $C26_DUEARREAR->calculation_date = date('d.m.Y', strtotime($contract->inssuance_date));

        $data->C26_DUEARREAR = $C26_DUEARREAR;


        $C27_PASTDUEARREAR = new StdClass();
        $C27_PASTDUEARREAR->amount_outstanding = '0,00';

        $data->C27_PASTDUEARREAR = $C27_PASTDUEARREAR;


        $C28_PAYMT = new StdClass();
        $C28_PAYMT->payment_date = date('d.m.Y', strtotime($order->operation->created));
        $C28_PAYMT->payment_amount = str_replace('.', ',', sprintf("%01.2f", $order->payment_amount));
        $C28_PAYMT->principal_payment_amount = str_replace('.', ',', sprintf("%01.2f", $order->principal_payment_amount));
        $C28_PAYMT->interest_payment_amount = str_replace('.', ',', sprintf("%01.2f", $order->interest_payment_amount));
        $C28_PAYMT->other_payment_amount = str_replace('.', ',', sprintf("%01.2f", $order->other_payment_amount));
        $C28_PAYMT->total_amount = str_replace('.', ',', sprintf("%01.2f", $order->total_amount));
        $C28_PAYMT->principal_total_amount = str_replace('.', ',', sprintf("%01.2f", $order->principal_total_amount));
        $C28_PAYMT->interest_total_amount = str_replace('.', ',', sprintf("%01.2f", $order->interest_total_amount));
        $C28_PAYMT->other_total_amount = str_replace('.', ',', sprintf("%01.2f", $order->other_total_amount));
        $C28_PAYMT->amount_keep_code = $order->amount_keep_code;
        $C28_PAYMT->terms_due_code = $order->terms_due_code;
        $C28_PAYMT->days_past_due = $order->days_past_due;

        $data->C28_PAYMT = $C28_PAYMT;


        $C29_MONTHAVERPAYMT = new StdClass();
        $C29_MONTHAVERPAYMT->average_payment_amount = round($contract->amount + $interest_terms_amount);
        $C29_MONTHAVERPAYMT->calculation_date = date('d.m.Y', strtotime($contract->inssuance_date));

        $data->C29_MONTHAVERPAYMT = $C29_MONTHAVERPAYMT;


        $C54_OBLIGACCOUNT = new StdClass();
        $C54_OBLIGACCOUNT->has_obligation = 1;

        $data->C54_OBLIGACCOUNT = $C54_OBLIGACCOUNT;


        $C56_OBLIGPARTTAKE = new StdClass();
        $C56_OBLIGPARTTAKE->flag_indicator_code = '1';
        $C56_OBLIGPARTTAKE->approved_loan_type_code = '1';
        $C56_OBLIGPARTTAKE->agreement_number = $contract->uid;
        $C56_OBLIGPARTTAKE->funding_date = date('d.m.Y', strtotime($contract->inssuance_date));
        $C56_OBLIGPARTTAKE->default_flag = '0';
        $C56_OBLIGPARTTAKE->loan_indicator = intval($order->closed) > 0 ? '1' : '0';

        $data->C56_OBLIGPARTTAKE = $C56_OBLIGPARTTAKE;

        return $data;
    }

    private function get_p2p_item($order)
    {
        $contract = $this->contracts->get_contract($order->contract_id);
        $contract->payments = $this->operations->get_operations(['contract_id' => $contract->id, 'type' => 'PAY']);


        $passport_serial = str_replace([' ', '-'], '', $order->passport_serial);
        $passport_series = substr($passport_serial, 0, 4);
        $passport_number = substr($passport_serial, 4, 6);


        $data = new StdClass();

        $GROUPHEADER = new StdClass();
        $GROUPHEADER->event_number = "2.2";
        $GROUPHEADER->operation_code = "B";
        $GROUPHEADER->event_date = date('d.m.Y', strtotime($order->operation->created));

        $data->GROUPHEADER = $GROUPHEADER;


        $C1_NAME = new StdClass();
        $C1_NAME->surname = $this->clearing($order->lastname);
        $C1_NAME->name = $this->clearing($order->firstname);
        $C1_NAME->patronymic = $this->clearing($order->patronymic);

        $data->C1_NAME = $C1_NAME;


        $C2_PREVNAME = new StdClass();
        $C2_PREVNAME->is_prev_name = '0';

        $data->C2_PREVNAME = $C2_PREVNAME;


        $C3_BIRTH = new StdClass();
        $C3_BIRTH->birth_date = date('d.m.Y', strtotime($order->birth));
        $C3_BIRTH->country_code = '643';
        $C3_BIRTH->birth_place = $this->clearing($order->birth_place);

        $data->C3_BIRTH = $C3_BIRTH;


        $C4_ID = new StdClass();
        $C4_ID->country_code = '643';
        $C4_ID->document_code = '21';
        $C4_ID->series_number = $passport_series;
        $C4_ID->document_number = $passport_number;
        $C4_ID->issue_date = date('d.m.Y', strtotime($order->passport_date));
        $C4_ID->issued_by_division = $this->clearing($order->passport_issued);
        $C4_ID->division_code = $order->subdivision_code;

        $data->C4_ID = $C4_ID;


        $C5_PREVID = new StdClass();
        $C5_PREVID->is_prev_document = '0';

        $data->C5_PREVID = $C5_PREVID;


        $C6_REGNUM = new StdClass();
        $C6_REGNUM->taxpayer_code = '1';
        $C6_REGNUM->taxpayer_number = empty($order->inn) ? '000000000000' : $order->inn;

        $data->C6_REGNUM = $C6_REGNUM;


        $C17_UID = new StdClass();
        $C17_UID->uuid = $contract->uid;

        $data->C17_UID = $C17_UID;


        $C18_TRADE = new StdClass();
        $C18_TRADE->owner_indicator_code = '1';
        $C18_TRADE->opened_date = date('d.m.Y', strtotime($contract->inssuance_date));
        $C18_TRADE->trade_type_code = '1';
        $C18_TRADE->load_kind_code = '1';
        $C18_TRADE->account_type_code = '14';
        $C18_TRADE->is_consumer_loan = '1';
        $C18_TRADE->has_card = '1';
        $C18_TRADE->is_novation = '0';
        $C18_TRADE->is_money_source = '1';
        $C18_TRADE->is_money_borrower = '1';
        $C18_TRADE->close_date = date('d.m.Y', strtotime($contract->return_date));

        $data->C18_TRADE = $C18_TRADE;


        $C19_ACCOUNTAMT = new StdClass();
        $C19_ACCOUNTAMT->credit_limit = str_replace('.', ',', sprintf("%01.2f", $contract->amount));
        $C19_ACCOUNTAMT->currency_code = 'RUB';

        $data->C19_ACCOUNTAMT = $C19_ACCOUNTAMT;


        $interest_terms_amount = ($contract->amount * $contract->base_percent / 100 * $contract->period);
        $C21_PAYMTCONDITION = new StdClass();
        $C21_PAYMTCONDITION->principal_terms_amount = str_replace('.', ',', sprintf("%01.2f", $contract->amount));
        $C21_PAYMTCONDITION->principal_terms_amount_date = date('d.m.Y', strtotime($contract->return_date));
        $C21_PAYMTCONDITION->interest_terms_amount = str_replace('.', ',', sprintf("%01.2f", $interest_terms_amount));
        $C21_PAYMTCONDITION->interest_terms_amount_date = date('d.m.Y', strtotime($contract->return_date));
        $C21_PAYMTCONDITION->terms_frequency_code = '3';
        $C21_PAYMTCONDITION->interest_payment_due_date = date('d.m.Y', strtotime($contract->return_date));

        $data->C21_PAYMTCONDITION = $C21_PAYMTCONDITION;


        $C22_OVERALLVAL = new StdClass();
        $C22_OVERALLVAL->total_credit_amount_interest = sprintf("%01.2f", $contract->base_percent * 365);
        $C22_OVERALLVAL->total_credit_amount_monetary = str_replace('.', ',', sprintf("%01.2f", $interest_terms_amount));
        $C22_OVERALLVAL->total_credit_amount_date = date('d.m.Y', strtotime($contract->inssuance_date));

        $data->C22_OVERALLVAL = $C22_OVERALLVAL;


        $C24_FUNDDATE = new StdClass();
        $C24_FUNDDATE->funding_date = date('d.m.Y', strtotime($contract->inssuance_date));

        $data->C24_FUNDDATE = $C24_FUNDDATE;


        $C25_ARREAR = new StdClass();
        $C25_ARREAR->has_arrear = '1';
        $C25_ARREAR->start_amount_outstanding = str_replace('.', ',', sprintf("%01.2f", $contract->amount));
        $C25_ARREAR->is_last_payment_due = '1';
        $C25_ARREAR->amount_outstanding = str_replace('.', ',', sprintf("%01.2f", $contract->amount + $interest_terms_amount));
        $C25_ARREAR->principal_amount_outstanding = str_replace('.', ',', sprintf("%01.2f", $contract->amount));
        $C25_ARREAR->interest_amount_outstanding = str_replace('.', ',', sprintf("%01.2f", $interest_terms_amount));
        $C25_ARREAR->other_amount_outstanding = '0,00';
        $C25_ARREAR->calculation_date = date('d.m.Y', strtotime($contract->inssuance_date));

        $data->C25_ARREAR = $C25_ARREAR;


        $C26_DUEARREAR = new StdClass();
        $C26_DUEARREAR->start_date = date('d.m.Y', strtotime($contract->inssuance_date));
        $C26_DUEARREAR->is_last_payment_due = '1';
        $C26_DUEARREAR->amount_outstanding = str_replace('.', ',', sprintf("%01.2f", $contract->amount + $interest_terms_amount));
        $C26_DUEARREAR->principal_amount_outstanding = str_replace('.', ',', sprintf("%01.2f", $contract->amount));
        $C26_DUEARREAR->interest_amount_outstanding = str_replace('.', ',', sprintf("%01.2f", $interest_terms_amount));
        $C26_DUEARREAR->other_amount_outstanding = '0,00';
        $C26_DUEARREAR->calculation_date = date('d.m.Y', strtotime($contract->inssuance_date));

        $data->C26_DUEARREAR = $C26_DUEARREAR;


        $C27_PASTDUEARREAR = new StdClass();
        $C27_PASTDUEARREAR->amount_outstanding = '0,00';

        $data->C27_PASTDUEARREAR = $C27_PASTDUEARREAR;


        $C28_PAYMT = new StdClass();

        $C28_PAYMT->payment_amount = '0,00';
        $C28_PAYMT->principal_payment_amount = '0,00';
        $C28_PAYMT->interest_payment_amount = '0,00';
        $C28_PAYMT->other_payment_amount = '0,00';
        $C28_PAYMT->total_amount = '0,00';
        $C28_PAYMT->principal_total_amount = '0,00';
        $C28_PAYMT->interest_total_amount = '0,00';
        $C28_PAYMT->other_total_amount = '0,00';
        $C28_PAYMT->amount_keep_code = '3';
        $C28_PAYMT->terms_due_code = '1';
        $C28_PAYMT->days_past_due = '0';

        $data->C28_PAYMT = $C28_PAYMT;


        $C29_MONTHAVERPAYMT = new StdClass();
        $C29_MONTHAVERPAYMT->average_payment_amount = round($contract->amount + $interest_terms_amount);
        $C29_MONTHAVERPAYMT->calculation_date = date('d.m.Y', strtotime($contract->inssuance_date));

        $data->C29_MONTHAVERPAYMT = $C29_MONTHAVERPAYMT;


        $C56_OBLIGPARTTAKE = new StdClass();
        $C56_OBLIGPARTTAKE->flag_indicator_code = '1';
        $C56_OBLIGPARTTAKE->approved_loan_type_code = '1';
        $C56_OBLIGPARTTAKE->agreement_number = $contract->uid;
        $C56_OBLIGPARTTAKE->funding_date = date('d.m.Y', strtotime($contract->inssuance_date));
        $C56_OBLIGPARTTAKE->default_flag = '0';
        $C56_OBLIGPARTTAKE->loan_indindicator = '0';

        $data->C56_OBLIGPARTTAKE = $C56_OBLIGPARTTAKE;
        echo __FILE__ . ' ' . __LINE__ . '<br /><pre>';
        var_dump($data);
        echo '</pre><hr />';
        return $data;
    }

    private function send($data, $url = 'v1/report/test/')
    {
        $url = 'http://185.182.111.110:9009/api/' . $url;

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));

        $json_res = curl_exec($curl);
        $error = curl_error($curl);

        curl_close($curl);

        $res = json_decode($json_res);

        $this->soap1c->logging(__METHOD__, $url, $data, $res, 'nbki.txt');
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($data, $error);echo '</pre><hr />';
        return $res;
    }

    public function get_report($id)
    {
        $query = $this->db->placehold("
            SELECT * 
            FROM __nbki_reports
            WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
        $result = $this->db->result();

        return $result;
    }

    public function get_reports($filter = array())
    {
        $id_filter = '';
        $keyword_filter = '';
        $limit = 1000;
        $page = 1;

        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array)$filter['id']));

        if (isset($filter['keyword'])) {
            $keywords = explode(' ', $filter['keyword']);
            foreach ($keywords as $keyword)
                $keyword_filter .= $this->db->placehold('AND (name LIKE "%' . $this->db->escape(trim($keyword)) . '%" )');
        }

        if (isset($filter['limit']))
            $limit = max(1, intval($filter['limit']));

        if (isset($filter['page']))
            $page = max(1, intval($filter['page']));

        $sql_limit = $this->db->placehold(' LIMIT ?, ? ', ($page - 1) * $limit, $limit);

        $query = $this->db->placehold("
            SELECT * 
            FROM __nbki_reports
            WHERE 1
                $id_filter
				$keyword_filter
            ORDER BY id DESC 
            $sql_limit
        ");
        $this->db->query($query);
        $results = $this->db->results();

        return $results;
    }

    public function count_reports($filter = array())
    {
        $id_filter = '';
        $keyword_filter = '';

        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array)$filter['id']));

        if (isset($filter['keyword'])) {
            $keywords = explode(' ', $filter['keyword']);
            foreach ($keywords as $keyword)
                $keyword_filter .= $this->db->placehold('AND (name LIKE "%' . $this->db->escape(trim($keyword)) . '%" )');
        }

        $query = $this->db->placehold("
            SELECT COUNT(id) AS count
            FROM __nbki_reports
            WHERE 1
                $id_filter
                $keyword_filter
        ");
        $this->db->query($query);
        $count = $this->db->result('count');

        return $count;
    }

    public function add_report($nbki_report)
    {
        $query = $this->db->placehold("
            INSERT INTO __nbki_reports SET ?%
        ", (array)$nbki_report);
        $this->db->query($query);
        $id = $this->db->insert_id();

        return $id;
    }

    public function update_report($id, $nbki_report)
    {
        $query = $this->db->placehold("
            UPDATE __nbki_reports SET ?% WHERE id = ?
        ", (array)$nbki_report, (int)$id);
        $this->db->query($query);

        return $id;
    }

    public function delete_report($id)
    {
        $query = $this->db->placehold("
            DELETE FROM __nbki_reports WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
    }

    public function clearing($string)
    {
        $replace = [
            '   ' => ' ',
            '  ' => ' ',
            ' -' => '-',
            '- ' => '-',
        ];

        $string = str_replace(array_keys($replace), array_values($replace), $string);
        $string = trim($string);

        return $string;
    }

}