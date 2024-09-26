<?php

class Nbki_report extends Core
{
    private $username = '1401SS000000';
    private $authorization_code = '934kjnG1';


    public function send_operations($operations)
    {
        $orders = [];
        $items = [];
        foreach ($operations as $operation) {
            if (in_array($operation->order_id, [45777, 50979, 51829, 52060]) ) {
                continue;
            }
            $format_date = date('Ymd', strtotime($operation->created));
            // $operation_type = $operation->type == 'P2P' ? 'P2P' : 'PAY';
            $operation_type = $operation->type;

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


            if ($operation_type == 'PAY' || $operation_type == 'CLOSE') {
                $items[$operation_type][$format_date][$operation->order_id]->payment_date = date('d.m.Y', strtotime($operation->created));

                if ($operation->type == 'PAY' || $operation_type == 'CLOSE') {
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

        if (isset($items['PAY'])) {
            foreach ($items['PAY'] as $operation_date => $orders) {
                foreach ($orders as $order) {
                    $query_operations = $this->operations->get_operations(['contract_id' => $order->contract->id, 'type' => ['PAY']]);

                    if (!empty($query_operations)) {
                        foreach ($query_operations as $query_operation) {
                            if (strtotime(date('Y-m-d', strtotime($query_operation->created))) == strtotime(date('Y-m-d', strtotime($operation_date)))) {
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
        }

        if (isset($items['CLOSE'])) {
            foreach ($items['CLOSE'] as $operation_date => $orders) {
                foreach ($orders as $order) {
                    $query_operations = $this->operations->get_operations(['contract_id' => $order->contract->id, 'type' => ['PAY']]);
    
                    if (!empty($query_operations)) {
                        foreach ($query_operations as $query_operation) {
                            if (strtotime(date('Y-m-d', strtotime($query_operation->created))) == strtotime(date('Y-m-d', strtotime($operation_date)))) {
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
        }

        $wrapper = $this->get_wrapper($items);

        $resp = $this->send($wrapper, 'v2/report/');
        // $resp = $this->send($wrapper, 'v2/report/temp-rutdf-5/');

        return $resp;
    }

    private function get_wrapper($items)
    {
        $wrapper = new StdClass();
        $wrapper->MANY_EVENTS = [];

        $HEADER = new StdClass();
        $HEADER->username = $this->username;
        $HEADER->password = $this->authorization_code;
        $HEADER->creation_date = date('d.m.Y');

        $wrapper->HEADER = $HEADER;

        if (!empty($items['P2P'])) {
            foreach ($items['P2P'] as $operation_date => $orders) {
                foreach ($orders as $order){
                    // var_dump($order->id);
                    $wrapper->MANY_EVENTS[] = $this->get_p2p_item($order);
                }
            }
        }

        if (!empty($items['PAY'])) {
            foreach ($items['PAY'] as $operation_date => $orders) {
                foreach ($orders as $order){
                    $wrapper->MANY_EVENTS[] = $this->get_pay_item($order);
                }
            }
        }

        if (!empty($items['CLOSE'])) {
            foreach ($items['CLOSE'] as $operation_date => $orders) {
                foreach ($orders as $order){
                    $wrapper->MANY_EVENTS[] = $this->get_close_item($order);
                }
            }
        }

        if (!empty($items['CESSIA'])) {
            foreach ($items['CESSIA'] as $operation_date => $orders) {
                foreach ($orders as $order){
                    $wrapper->MANY_EVENTS[] = $this->get_cessia_item($order);
                }
            }
        }

        if (!empty($items['CANICULE'])) {
            foreach ($items['CANICULE'] as $operation_date => $orders) {
                foreach ($orders as $order){
                    $wrapper->MANY_EVENTS[] = $this->get_canicule_item($order);
                }
            }
        }

        if (!empty($items['PENI'])) {
            foreach ($items['PENI'] as $operation_date => $orders) {
                foreach ($orders as $order){
                    $wrapper->MANY_EVENTS[] = $this->get_peni_item($order);
                }
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
        $C6_REGNUM->is_special_tax = '0';

        $data->C6_REGNUM = $C6_REGNUM;


        $C17_UID = new StdClass();
        $C17_UID->uuid = $contract->uid;

        $data->C17_UID = $C17_UID;


        $C18_TRADE = new StdClass();
        $C18_TRADE->owner_indicator_code = '1';
        $C18_TRADE->opened_date = date('d.m.Y', strtotime($contract->inssuance_date));
        $C18_TRADE->trade_type_code = '1';
        $C18_TRADE->load_kind_code = '13';
        $C18_TRADE->account_type_code = '14';
        $C18_TRADE->is_consumer_loan = '1';
        $C18_TRADE->has_card = '1';
        $C18_TRADE->is_novation = '0';
        $C18_TRADE->is_money_source = '1';
        $C18_TRADE->is_money_borrower = '1';
        $C18_TRADE->close_date = date('d.m.Y', strtotime($contract->return_date));
        $C18_TRADE->lender_type_code = '2';
        $C18_TRADE->has_obtaining_part_creditor = '0';
        $C18_TRADE->has_credit_line = '0';
        $C18_TRADE->is_interest_rate_float = '0';
        $C18_TRADE->has_transfer_part_creditor = '0';
        $C18_TRADE->commit_date = date('d.m.Y', strtotime($contract->inssuance_date));

        $data->C18_TRADE = $C18_TRADE;


        $C19_ACCOUNTAMT = new StdClass();
        $C19_ACCOUNTAMT->credit_limit = str_replace('.', ',', sprintf("%01.2f", $contract->amount));
        $C19_ACCOUNTAMT->currency_code = 'RUB';
        $C19_ACCOUNTAMT->commit_currency_code = 'RUB';
        $C19_ACCOUNTAMT->amount_date = date('d.m.Y', strtotime($contract->inssuance_date));
        $C19_ACCOUNTAMT->commit_uuid = $contract->uid;

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
        $C22_OVERALLVAL->total_credit_amount_interest = str_replace('.', ',', sprintf("%01.2f", $contract->base_percent * 365));
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
        $C27_PASTDUEARREAR->calculation_date = date('d.m.Y', strtotime($contract->inssuance_date));

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
        $C54_OBLIGACCOUNT->interest_rate = str_replace('.', ',', sprintf("%01.2f", $contract->base_percent * 365));
        $C54_OBLIGACCOUNT->has_preferential_financing = '0';

        $data->C54_OBLIGACCOUNT = $C54_OBLIGACCOUNT;


        $C56_OBLIGPARTTAKE = new StdClass();
        $C56_OBLIGPARTTAKE->flag_indicator_code = '1';
        $C56_OBLIGPARTTAKE->approved_loan_type_code = '13';
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
        $C6_REGNUM->is_special_tax = '0';

        $data->C6_REGNUM = $C6_REGNUM;


        $C17_UID = new StdClass();
        $C17_UID->uuid = $contract->uid;

        $data->C17_UID = $C17_UID;


        $C18_TRADE = new StdClass();
        $C18_TRADE->owner_indicator_code = '1';
        $C18_TRADE->opened_date = date('d.m.Y', strtotime($contract->inssuance_date));
        $C18_TRADE->trade_type_code = '1';
        $C18_TRADE->load_kind_code = '13';
        $C18_TRADE->account_type_code = '14';
        $C18_TRADE->is_consumer_loan = '1';
        $C18_TRADE->has_card = '1';
        $C18_TRADE->is_novation = '0';
        $C18_TRADE->is_money_source = '1';
        $C18_TRADE->is_money_borrower = '1';
        $C18_TRADE->close_date = date('d.m.Y', strtotime($contract->return_date));
        $C18_TRADE->lender_type_code = '2';
        $C18_TRADE->has_obtaining_part_creditor = '0';
        $C18_TRADE->has_credit_line = '0';
        $C18_TRADE->is_interest_rate_float = '0';
        $C18_TRADE->has_transfer_part_creditor = '0';
        $C18_TRADE->commit_date = date('d.m.Y', strtotime($contract->inssuance_date));

        $data->C18_TRADE = $C18_TRADE;


        $C19_ACCOUNTAMT = new StdClass();
        $C19_ACCOUNTAMT->credit_limit = str_replace('.', ',', sprintf("%01.2f", $contract->amount));
        $C19_ACCOUNTAMT->currency_code = 'RUB';
        $C19_ACCOUNTAMT->commit_currency_code = 'RUB';
        $C19_ACCOUNTAMT->amount_date = date('d.m.Y', strtotime($contract->inssuance_date));
        $C19_ACCOUNTAMT->commit_uuid = $contract->uid;

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
        $C22_OVERALLVAL->total_credit_amount_interest = str_replace('.', ',', sprintf("%01.2f", $contract->base_percent * 365));
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
        $C27_PASTDUEARREAR->calculation_date = date('d.m.Y', strtotime($contract->inssuance_date));

        $data->C27_PASTDUEARREAR = $C27_PASTDUEARREAR;


        $C28_PAYMT = new StdClass();

        $C28_PAYMT->payment_amount = '0,00';
        // $C28_PAYMT->principal_payment_amount = '0,00';
        // $C28_PAYMT->interest_payment_amount = '0,00';
        // $C28_PAYMT->other_payment_amount = '0,00';
        // $C28_PAYMT->total_amount = '0,00';
        // $C28_PAYMT->principal_total_amount = '0,00';
        // $C28_PAYMT->interest_total_amount = '0,00';
        // $C28_PAYMT->other_total_amount = '0,00';
        $C28_PAYMT->amount_keep_code = '3';
        $C28_PAYMT->terms_due_code = '1';
        $C28_PAYMT->days_past_due = '0';

        $data->C28_PAYMT = $C28_PAYMT;


        $C29_MONTHAVERPAYMT = new StdClass();
        $C29_MONTHAVERPAYMT->average_payment_amount = round($contract->amount + $interest_terms_amount);
        $C29_MONTHAVERPAYMT->calculation_date = date('d.m.Y', strtotime($contract->inssuance_date));

        $data->C29_MONTHAVERPAYMT = $C29_MONTHAVERPAYMT;


        $C54_OBLIGACCOUNT = new StdClass();
        $C54_OBLIGACCOUNT->has_obligation = 1;
        $C54_OBLIGACCOUNT->interest_rate = str_replace('.', ',', sprintf("%01.2f", $contract->base_percent * 365));
        $C54_OBLIGACCOUNT->has_preferential_financing = '0';

        $data->C54_OBLIGACCOUNT = $C54_OBLIGACCOUNT;


        $C56_OBLIGPARTTAKE = new StdClass();
        $C56_OBLIGPARTTAKE->flag_indicator_code = '1';
        $C56_OBLIGPARTTAKE->approved_loan_type_code = '13';
        $C56_OBLIGPARTTAKE->agreement_number = $contract->uid;
        $C56_OBLIGPARTTAKE->funding_date = date('d.m.Y', strtotime($contract->inssuance_date));
        $C56_OBLIGPARTTAKE->default_flag = '0';
        $C56_OBLIGPARTTAKE->loan_indindicator = '0';

        $data->C56_OBLIGPARTTAKE = $C56_OBLIGPARTTAKE;
        // echo __FILE__ . ' ' . __LINE__ . '<br /><pre>';
        // var_dump($data);
        // echo '</pre><hr />';
        return $data;
    }

    private function get_close_item($order)
    {
        $contract = $this->contracts->get_contract($order->contract_id);

        $passport_serial = str_replace([' ', '-'], '', $order->passport_serial);
        $passport_series = substr($passport_serial, 0, 4);
        $passport_number = substr($passport_serial, 4, 6);


        $data = new StdClass();

        $GROUPHEADER = new StdClass();
        $GROUPHEADER->event_number = "2.5";
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
        $C6_REGNUM->is_special_tax = '0';

        $data->C6_REGNUM = $C6_REGNUM;


        $C17_UID = new StdClass();
        $C17_UID->uuid = $contract->uid;

        $data->C17_UID = $C17_UID;


        $C18_TRADE = new StdClass();
        $C18_TRADE->owner_indicator_code = '1';
        $C18_TRADE->opened_date = date('d.m.Y', strtotime($contract->inssuance_date));
        $C18_TRADE->trade_type_code = '1';
        $C18_TRADE->load_kind_code = '13';
        $C18_TRADE->account_type_code = '14';
        $C18_TRADE->is_consumer_loan = '1';
        $C18_TRADE->has_card = '1';
        $C18_TRADE->is_novation = '0';
        $C18_TRADE->is_money_source = '1';
        $C18_TRADE->is_money_borrower = '1';
        $C18_TRADE->close_date = date('d.m.Y', strtotime($contract->return_date));
        $C18_TRADE->lender_type_code = '2';
        $C18_TRADE->has_obtaining_part_creditor = '0';
        $C18_TRADE->has_credit_line = '0';
        $C18_TRADE->is_interest_rate_float = '0';
        $C18_TRADE->has_transfer_part_creditor = '0';
        $C18_TRADE->commit_date = date('d.m.Y', strtotime($contract->inssuance_date));

        $data->C18_TRADE = $C18_TRADE;


        $C19_ACCOUNTAMT = new StdClass();
        $C19_ACCOUNTAMT->credit_limit = str_replace('.', ',', sprintf("%01.2f", $contract->amount));
        $C19_ACCOUNTAMT->currency_code = 'RUB';
        $C19_ACCOUNTAMT->commit_currency_code = 'RUB';
        $C19_ACCOUNTAMT->amount_date = date('d.m.Y', strtotime($contract->inssuance_date));
        $C19_ACCOUNTAMT->commit_uuid = $contract->uid;

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
        $C22_OVERALLVAL->total_credit_amount_interest = str_replace('.', ',', sprintf("%01.2f", $contract->base_percent * 365));
        $C22_OVERALLVAL->total_credit_amount_monetary = str_replace('.', ',', sprintf("%01.2f", $interest_terms_amount));
        $C22_OVERALLVAL->total_credit_amount_date = date('d.m.Y', strtotime($contract->inssuance_date));

        $data->C22_OVERALLVAL = $C22_OVERALLVAL;


        $C24_FUNDDATE = new StdClass();
        $C24_FUNDDATE->funding_date = date('d.m.Y', strtotime($contract->inssuance_date));

        $data->C24_FUNDDATE = $C24_FUNDDATE;


        $ret_date_array = $this->ret_date_data($order);

        $ret_date = $ret_date_array[0];
        $days_past_due = $ret_date_array[1];
        $ret_date_body_summ = $ret_date_array[2];
        $ret_date_percents_summ = $ret_date_array[3];
        $ret_date_peni_summ = $ret_date_array[4];

        $C25_ARREAR = new StdClass();
        $C25_ARREAR->has_arrear = '1';
        $C25_ARREAR->start_amount_outstanding = str_replace('.', ',', sprintf("%01.2f", $contract->amount));
        $C25_ARREAR->is_last_payment_due = '1';
        // $C25_ARREAR->amount_outstanding = '0,00';
        $C25_ARREAR->amount_outstanding = str_replace('.', ',', sprintf("%01.2f", $ret_date_body_summ + $ret_date_percents_summ + $ret_date_peni_summ));
        $C25_ARREAR->principal_amount_outstanding = str_replace('.', ',', sprintf("%01.2f", $ret_date_body_summ));
        $C25_ARREAR->interest_amount_outstanding = str_replace('.', ',', sprintf("%01.2f", $ret_date_percents_summ + $ret_date_peni_summ));
        $C25_ARREAR->other_amount_outstanding = '0,00';
        // $C25_ARREAR->calculation_date = date('d.m.Y', strtotime($contract->inssuance_date));
        $C25_ARREAR->calculation_date = date('d.m.Y', strtotime($contract->close_date));


        $data->C25_ARREAR = $C25_ARREAR;


        $C26_DUEARREAR = new StdClass();
        $C26_DUEARREAR->start_date = date('d.m.Y', strtotime($contract->inssuance_date));
        $C26_DUEARREAR->is_last_payment_due = '1';
        // if ($ret_date_peni_summ) {
            $C26_DUEARREAR->amount_outstanding = str_replace('.', ',', sprintf("%01.2f", $ret_date_body_summ + $ret_date_percents_summ + $ret_date_peni_summ));
            $C26_DUEARREAR->principal_amount_outstanding = str_replace('.', ',', sprintf("%01.2f", $ret_date_body_summ));
            $C26_DUEARREAR->interest_amount_outstanding = str_replace('.', ',', sprintf("%01.2f", $ret_date_percents_summ + $ret_date_peni_summ));
            $C26_DUEARREAR->other_amount_outstanding = '0,00';
        // }
        // else{
        //     $C26_DUEARREAR->amount_outstanding = '0,00';
        //     $C26_DUEARREAR->principal_amount_outstanding = '0,00';
        //     $C26_DUEARREAR->interest_amount_outstanding = '0,00';
        //     $C26_DUEARREAR->other_amount_outstanding = '0,00';
        // }
        // $C26_DUEARREAR->calculation_date = date('d.m.Y', strtotime($contract->inssuance_date));
        $C26_DUEARREAR->calculation_date = date('d.m.Y', strtotime($contract->close_date));

        $data->C26_DUEARREAR = $C26_DUEARREAR;


        $C27_PASTDUEARREAR = new StdClass();
        $C27_PASTDUEARREAR->amount_outstanding = '0,00';
        // $C27_PASTDUEARREAR->calculation_date = date('d.m.Y', strtotime($this->last_missed_percents_payment($order)));
        $C27_PASTDUEARREAR->calculation_date = date('d.m.Y', strtotime($contract->close_date));

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
        // $C28_PAYMT->amount_keep_code = $order->amount_keep_code;
        // $C28_PAYMT->terms_due_code = $order->terms_due_code;
        // $C28_PAYMT->days_past_due = $order->days_past_due;
        $C28_PAYMT->amount_keep_code = '1';
        $C28_PAYMT->terms_due_code = '2';
        $C28_PAYMT->days_past_due = '0';

        $data->C28_PAYMT = $C28_PAYMT;


        $C29_MONTHAVERPAYMT = new StdClass();
        // $C29_MONTHAVERPAYMT->average_payment_amount = round($contract->amount + $interest_terms_amount);
        $C29_MONTHAVERPAYMT->average_payment_amount = 0;
        // $C29_MONTHAVERPAYMT->calculation_date = date('d.m.Y', strtotime($contract->inssuance_date));
        $C29_MONTHAVERPAYMT->calculation_date = date('d.m.Y', strtotime($contract->close_date));

        $data->C29_MONTHAVERPAYMT = $C29_MONTHAVERPAYMT;


        $C38_OBLIGTERMINATION  = new StdClass();
        $C38_OBLIGTERMINATION->loan_indicator = 1;
        $C38_OBLIGTERMINATION->loan_indicator_date = date('d.m.Y', strtotime($contract->close_date));

        $data->C38_OBLIGTERMINATION  = $C38_OBLIGTERMINATION ;


        $C54_OBLIGACCOUNT = new StdClass();
        $C54_OBLIGACCOUNT->has_obligation = 1;
        $C54_OBLIGACCOUNT->interest_rate = str_replace('.', ',', sprintf("%01.2f", $contract->base_percent * 365));
        $C54_OBLIGACCOUNT->has_preferential_financing = '0';

        $data->C54_OBLIGACCOUNT = $C54_OBLIGACCOUNT;


        $C56_OBLIGPARTTAKE = new StdClass();
        $C56_OBLIGPARTTAKE->flag_indicator_code = '1';
        $C56_OBLIGPARTTAKE->approved_loan_type_code = '13';
        $C56_OBLIGPARTTAKE->agreement_number = $contract->uid;
        $C56_OBLIGPARTTAKE->funding_date = date('d.m.Y', strtotime($contract->inssuance_date));
        $C56_OBLIGPARTTAKE->default_flag = '0';
        // $C56_OBLIGPARTTAKE->loan_indicator = intval($order->closed) > 0 ? '1' : '0';
        $C56_OBLIGPARTTAKE->loan_indicator = 1;

        $data->C56_OBLIGPARTTAKE = $C56_OBLIGPARTTAKE;

        return $data;
    }

    private function get_cessia_item($order)
    {
        $contract = $this->contracts->get_contract($order->contract_id);

        $passport_serial = str_replace([' ', '-'], '', $order->passport_serial);
        $passport_series = substr($passport_serial, 0, 4);
        $passport_number = substr($passport_serial, 4, 6);


        $data = new StdClass();

        $GROUPHEADER = new StdClass();
        $GROUPHEADER->event_number = "2.11";
        $GROUPHEADER->operation_code = "B";
        // $GROUPHEADER->event_date = date('d.m.Y', strtotime($order->operation->created));
        $GROUPHEADER->event_date = date('d.m.Y', strtotime($contract->cession));

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
        $C6_REGNUM->is_special_tax = '0';

        $data->C6_REGNUM = $C6_REGNUM;


        $C17_UID = new StdClass();
        $C17_UID->uuid = $contract->uid;

        $data->C17_UID = $C17_UID;


        // $C18_TRADE = new StdClass();
        // $C18_TRADE->owner_indicator_code = '1';
        // $C18_TRADE->opened_date = date('d.m.Y', strtotime($contract->inssuance_date));
        // $C18_TRADE->trade_type_code = '1';
        // $C18_TRADE->load_kind_code = '13';
        // $C18_TRADE->account_type_code = '14';
        // $C18_TRADE->is_consumer_loan = '1';
        // $C18_TRADE->has_card = '1';
        // $C18_TRADE->is_novation = '0';
        // $C18_TRADE->is_money_source = '1';
        // $C18_TRADE->is_money_borrower = '1';
        // $C18_TRADE->close_date = date('d.m.Y', strtotime($contract->return_date));
        // $C18_TRADE->lender_type_code = '2';
        // $C18_TRADE->has_obtaining_part_creditor = '0';
        // $C18_TRADE->has_credit_line = '0';
        // $C18_TRADE->is_interest_rate_float = '0';
        // $C18_TRADE->has_transfer_part_creditor = '0';
        // $C18_TRADE->commit_date = date('d.m.Y', strtotime($contract->inssuance_date));

        // $data->C18_TRADE = $C18_TRADE;


        // $C19_ACCOUNTAMT = new StdClass();
        // $C19_ACCOUNTAMT->credit_limit = str_replace('.', ',', sprintf("%01.2f", $contract->amount));
        // $C19_ACCOUNTAMT->currency_code = 'RUB';
        // $C19_ACCOUNTAMT->commit_currency_code = 'RUB';
        // $C19_ACCOUNTAMT->amount_date = date('d.m.Y', strtotime($contract->inssuance_date));
        // $C19_ACCOUNTAMT->commit_uuid = $contract->uid;

        // $data->C19_ACCOUNTAMT = $C19_ACCOUNTAMT;


        // $interest_terms_amount = ($contract->amount * $contract->base_percent / 100 * $contract->period);
        // $C21_PAYMTCONDITION = new StdClass();
        // $C21_PAYMTCONDITION->principal_terms_amount = str_replace('.', ',', sprintf("%01.2f", $contract->amount));
        // $C21_PAYMTCONDITION->principal_terms_amount_date = date('d.m.Y', strtotime($contract->return_date));
        // $C21_PAYMTCONDITION->interest_terms_amount = str_replace('.', ',', sprintf("%01.2f", $interest_terms_amount));
        // $C21_PAYMTCONDITION->interest_terms_amount_date = date('d.m.Y', strtotime($contract->return_date));
        // $C21_PAYMTCONDITION->terms_frequency_code = '3';
        // $C21_PAYMTCONDITION->interest_payment_due_date = date('d.m.Y', strtotime($contract->return_date));

        // $data->C21_PAYMTCONDITION = $C21_PAYMTCONDITION;


        // $C22_OVERALLVAL = new StdClass();
        // $C22_OVERALLVAL->total_credit_amount_interest = str_replace('.', ',', sprintf("%01.2f", $contract->base_percent * 365));
        // $C22_OVERALLVAL->total_credit_amount_monetary = str_replace('.', ',', sprintf("%01.2f", $interest_terms_amount));
        // $C22_OVERALLVAL->total_credit_amount_date = date('d.m.Y', strtotime($contract->inssuance_date));

        // $data->C22_OVERALLVAL = $C22_OVERALLVAL;


        // $C24_FUNDDATE = new StdClass();
        // $C24_FUNDDATE->funding_date = date('d.m.Y', strtotime($contract->inssuance_date));

        // $data->C24_FUNDDATE = $C24_FUNDDATE;


        // $ret_date_array = $this->ret_date_data($order);

        // $ret_date = $ret_date_array[0];
        // $days_past_due = $ret_date_array[1];
        // $ret_date_body_summ = $ret_date_array[2];
        // $ret_date_percents_summ = $ret_date_array[3];
        // $ret_date_peni_summ = $ret_date_array[4];


        // $C25_ARREAR = new StdClass();
        // $C25_ARREAR->has_arrear = '0';
        // // $C25_ARREAR->start_amount_outstanding = str_replace('.', ',', sprintf("%01.2f", $contract->amount));
        // // $C25_ARREAR->is_last_payment_due = '0';
        // // // $C25_ARREAR->amount_outstanding = '0,00';
        // // // $C25_ARREAR->principal_amount_outstanding = '0,00';
        // // // $C25_ARREAR->interest_amount_outstanding = '0,00';
        // // // $C25_ARREAR->other_amount_outstanding = '0,00';
        // // $C25_ARREAR->amount_outstanding = str_replace('.', ',', sprintf("%01.2f", $ret_date_body_summ + $ret_date_percents_summ + $ret_date_peni_summ));
        // // $C25_ARREAR->principal_amount_outstanding = str_replace('.', ',', sprintf("%01.2f", $ret_date_body_summ));
        // // $C25_ARREAR->interest_amount_outstanding = str_replace('.', ',', sprintf("%01.2f", $ret_date_percents_summ + $ret_date_peni_summ));
        // // $C25_ARREAR->other_amount_outstanding = '0,00';
        // // $C25_ARREAR->calculation_date = date('d.m.Y', strtotime($contract->cession));

        // $data->C25_ARREAR = $C25_ARREAR;


        // $C26_DUEARREAR = new StdClass();
        // $C26_DUEARREAR->start_date = date('d.m.Y', strtotime($contract->inssuance_date));
        // $C26_DUEARREAR->is_last_payment_due = '1';
        // // $C26_DUEARREAR->amount_outstanding = '0,00';
        // // $C26_DUEARREAR->principal_amount_outstanding = '0,00';
        // // $C26_DUEARREAR->interest_amount_outstanding = '0,00';
        // // $C26_DUEARREAR->other_amount_outstanding = '0,00';
        // $C26_DUEARREAR->amount_outstanding = str_replace('.', ',', sprintf("%01.2f", $ret_date_body_summ + $ret_date_percents_summ + $ret_date_peni_summ));
        // $C26_DUEARREAR->principal_amount_outstanding = str_replace('.', ',', sprintf("%01.2f", $ret_date_body_summ));
        // $C26_DUEARREAR->interest_amount_outstanding = str_replace('.', ',', sprintf("%01.2f", $ret_date_percents_summ + $ret_date_peni_summ));
        // $C26_DUEARREAR->other_amount_outstanding = '0,00';
        // $C26_DUEARREAR->calculation_date = date('d.m.Y', strtotime($contract->cession));

        // $data->C26_DUEARREAR = $C26_DUEARREAR;


        // $C27_PASTDUEARREAR = new StdClass();
        // $C27_PASTDUEARREAR->amount_outstanding = '0,00';
        // // $C27_PASTDUEARREAR->calculation_date = date('d.m.Y', strtotime($ret_date));
        // $C27_PASTDUEARREAR->calculation_date = date('d.m.Y', strtotime($contract->cession));

        // $data->C27_PASTDUEARREAR = $C27_PASTDUEARREAR;

        
        // $last_payment_operation = $this->last_payment_data($order);

        // $C28_PAYMT = new StdClass();

        // if (!$last_payment_operation) {
        //     $C28_PAYMT->payment_amount = '0,00';
        //     $C28_PAYMT->amount_keep_code = '3';
        //     $C28_PAYMT->terms_due_code = '3';
        //     // $C28_PAYMT->days_past_due = $days_past_due;
        //     $C28_PAYMT->days_past_due = 0;
        // }
        // else{

        //     $last_payment_transaction = $this->transactions->get_transaction($last_payment_operation->transaction_id);

        //     $C28_PAYMT->payment_date = date('d.m.Y', strtotime($last_payment_operation->created));
        //     $C28_PAYMT->payment_amount = str_replace('.', ',', sprintf("%01.2f", $last_payment_operation->amount));
        //     $C28_PAYMT->principal_payment_amount = str_replace('.', ',', sprintf("%01.2f", $last_payment_transaction->loan_body_summ));
        //     $C28_PAYMT->interest_payment_amount = str_replace('.', ',', sprintf("%01.2f",  $last_payment_transaction->loan_percents_summ + $last_payment_transaction->loan_peni_summ));
        //     $C28_PAYMT->other_payment_amount = "0,00";
        //     $C28_PAYMT->total_amount = str_replace('.', ',', sprintf("%01.2f", $last_payment_operation->total_amount));
        //     $C28_PAYMT->principal_total_amount = str_replace('.', ',', sprintf("%01.2f", $last_payment_operation->principal_total_amount));
        //     $C28_PAYMT->interest_total_amount = str_replace('.', ',', sprintf("%01.2f", $last_payment_operation->interest_total_amount));
        //     $C28_PAYMT->other_total_amount = "0,00";
        //     $C28_PAYMT->amount_keep_code = '3';
        //     $C28_PAYMT->terms_due_code = '3';
        //     // $C28_PAYMT->days_past_due = $days_past_due;
        //     $C28_PAYMT->days_past_due = 0;
        // }

        // $data->C28_PAYMT = $C28_PAYMT;


        // $C29_MONTHAVERPAYMT = new StdClass();
        // $C29_MONTHAVERPAYMT->average_payment_amount = 0;
        // $C29_MONTHAVERPAYMT->calculation_date = date('d.m.Y', strtotime($contract->cession));

        // $data->C29_MONTHAVERPAYMT = $C29_MONTHAVERPAYMT;


        $C45_SUBMITHOLD = new StdClass();
        $C45_SUBMITHOLD->hold_code = "3";
        $C45_SUBMITHOLD->hold_date = date('d.m.Y', strtotime($contract->cession));

        $data->C45_SUBMITHOLD = $C45_SUBMITHOLD;


        $C51_ACQUIRERLEGAL  = new StdClass();
        $C51_ACQUIRERLEGAL->right_of_claim_code = "3";
        $C51_ACQUIRERLEGAL->right_of_claim_rus = "1";
        $C51_ACQUIRERLEGAL->right_of_claim_full_name = "ОБЩЕСТВО С ОГРАНИЧЕННОЙ ОТВЕТСТВЕННОСТЬЮ «ПРОФЕССИОНАЛЬНАЯ КОЛЛЕКТОРСКАЯ ОРГАНИЗАЦИЯ «ЦЕРБЕР»";
        $C51_ACQUIRERLEGAL->right_of_claim_name = "ООО «ПКО «ЦЕРБЕР»";
        $C51_ACQUIRERLEGAL->right_of_claim_registration_number = "1147746738028";
        $C51_ACQUIRERLEGAL->right_of_claim_tax_code = "1";
        $C51_ACQUIRERLEGAL->right_of_claim_tax_number = "7709957219";
        $C51_ACQUIRERLEGAL->right_of_claim_date = date('d.m.Y', strtotime($contract->cession));

        $data->C51_ACQUIRERLEGAL  = $C51_ACQUIRERLEGAL ;
        

        $C54_OBLIGACCOUNT = new StdClass();
        $C54_OBLIGACCOUNT->has_obligation = 1;
        $C54_OBLIGACCOUNT->interest_rate = str_replace('.', ',', sprintf("%01.2f", $contract->base_percent * 365));
        $C54_OBLIGACCOUNT->has_preferential_financing = '0';

        $data->C54_OBLIGACCOUNT = $C54_OBLIGACCOUNT;


        // $C56_OBLIGPARTTAKE = new StdClass();
        // $C56_OBLIGPARTTAKE->flag_indicator_code = '1';
        // $C56_OBLIGPARTTAKE->approved_loan_type_code = '13';
        // $C56_OBLIGPARTTAKE->agreement_number = $contract->uid;
        // $C56_OBLIGPARTTAKE->funding_date = date('d.m.Y', strtotime($contract->inssuance_date));
        // $C56_OBLIGPARTTAKE->default_flag = '0';
        // $C56_OBLIGPARTTAKE->loan_indicator = 1;

        // $data->C56_OBLIGPARTTAKE = $C56_OBLIGPARTTAKE;

        return $data;
    }

    private function get_canicule_item($order)
    {
        $contract = $this->contracts->get_contract($order->contract_id);

        $passport_serial = str_replace([' ', '-'], '', $order->passport_serial);
        $passport_series = substr($passport_serial, 0, 4);
        $passport_number = substr($passport_serial, 4, 6);


        $data = new StdClass();

        $GROUPHEADER = new StdClass();
        $GROUPHEADER->event_number = "2.1";
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
        $C6_REGNUM->is_special_tax = '0';

        $data->C6_REGNUM = $C6_REGNUM;


        $C17_UID = new StdClass();
        $C17_UID->uuid = $contract->uid;

        $data->C17_UID = $C17_UID;


        $C18_TRADE = new StdClass();
        $C18_TRADE->owner_indicator_code = '1';
        $C18_TRADE->opened_date = date('d.m.Y', strtotime($contract->inssuance_date));
        $C18_TRADE->trade_type_code = '1';
        $C18_TRADE->load_kind_code = '13';
        $C18_TRADE->account_type_code = '14';
        $C18_TRADE->is_consumer_loan = '1';
        $C18_TRADE->has_card = '1';
        $C18_TRADE->is_novation = '0';
        $C18_TRADE->is_money_source = '1';
        $C18_TRADE->is_money_borrower = '1';
        $C18_TRADE->close_date = date('d.m.Y', strtotime($contract->return_date));
        $C18_TRADE->lender_type_code = '2';
        $C18_TRADE->has_obtaining_part_creditor = '0';
        $C18_TRADE->has_credit_line = '0';
        $C18_TRADE->is_interest_rate_float = '0';
        $C18_TRADE->has_transfer_part_creditor = '0';
        $C18_TRADE->commit_date = date('d.m.Y', strtotime($contract->inssuance_date));

        $data->C18_TRADE = $C18_TRADE;


        $C19_ACCOUNTAMT = new StdClass();
        $C19_ACCOUNTAMT->credit_limit = str_replace('.', ',', sprintf("%01.2f", $contract->amount));
        $C19_ACCOUNTAMT->currency_code = 'RUB';
        $C19_ACCOUNTAMT->commit_currency_code = 'RUB';
        $C19_ACCOUNTAMT->amount_date = date('d.m.Y', strtotime($contract->inssuance_date));
        $C19_ACCOUNTAMT->commit_uuid = $contract->uid;

        $data->C19_ACCOUNTAMT = $C19_ACCOUNTAMT;

        $C20_COBORROWER  = new StdClass();
        $C20_COBORROWER->has_solidary = 0;

        $data->C20_COBORROWER  = $C20_COBORROWER ;

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
        $C22_OVERALLVAL->total_credit_amount_interest = str_replace('.', ',', sprintf("%01.2f", $contract->base_percent * 365));
        $C22_OVERALLVAL->total_credit_amount_monetary = str_replace('.', ',', sprintf("%01.2f", $interest_terms_amount));
        $C22_OVERALLVAL->total_credit_amount_date = date('d.m.Y', strtotime($contract->inssuance_date));

        $data->C22_OVERALLVAL = $C22_OVERALLVAL;


        $C23_AMENDMENT = new StdClass();
        $C23_AMENDMENT->is_amendment = 1;
        $C23_AMENDMENT->amendment_date = date('d.m.Y', strtotime($contract->canicule));
        $C23_AMENDMENT->amendment_type_code = 1;
        $C23_AMENDMENT->special_amendment_type_code = 9;
        $C23_AMENDMENT->amendment_start_date = date('d.m.Y', strtotime($contract->canicule));

        $data->C23_AMENDMENT = $C23_AMENDMENT;

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
        $C27_PASTDUEARREAR->calculation_date = date('d.m.Y', strtotime($contract->inssuance_date));

        $data->C27_PASTDUEARREAR = $C27_PASTDUEARREAR;


        $C28_PAYMT = new StdClass();
        // $C28_PAYMT->payment_date = date('d.m.Y', strtotime($order->operation->created));
        $C28_PAYMT->payment_amount = str_replace('.', ',', sprintf("%01.2f", $order->payment_amount));
        // $C28_PAYMT->principal_payment_amount = str_replace('.', ',', sprintf("%01.2f", $order->principal_payment_amount));
        // $C28_PAYMT->interest_payment_amount = str_replace('.', ',', sprintf("%01.2f", $order->interest_payment_amount));
        // $C28_PAYMT->other_payment_amount = str_replace('.', ',', sprintf("%01.2f", $order->other_payment_amount));
        // $C28_PAYMT->total_amount = str_replace('.', ',', sprintf("%01.2f", $order->total_amount));
        // $C28_PAYMT->principal_total_amount = str_replace('.', ',', sprintf("%01.2f", $order->principal_total_amount));
        // $C28_PAYMT->interest_total_amount = str_replace('.', ',', sprintf("%01.2f", $order->interest_total_amount));
        // $C28_PAYMT->other_total_amount = str_replace('.', ',', sprintf("%01.2f", $order->other_total_amount));
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
        $C54_OBLIGACCOUNT->interest_rate = str_replace('.', ',', sprintf("%01.2f", $contract->base_percent * 365));
        $C54_OBLIGACCOUNT->has_preferential_financing = '0';

        $data->C54_OBLIGACCOUNT = $C54_OBLIGACCOUNT;


        // $C56_OBLIGPARTTAKE = new StdClass();
        // $C56_OBLIGPARTTAKE->flag_indicator_code = '1';
        // $C56_OBLIGPARTTAKE->approved_loan_type_code = '13';
        // $C56_OBLIGPARTTAKE->agreement_number = $contract->uid;
        // $C56_OBLIGPARTTAKE->funding_date = date('d.m.Y', strtotime($contract->inssuance_date));
        // $C56_OBLIGPARTTAKE->default_flag = '0';
        // $C56_OBLIGPARTTAKE->loan_indicator = intval($order->closed) > 0 ? '1' : '0';

        // $data->C56_OBLIGPARTTAKE = $C56_OBLIGPARTTAKE;

        return $data;
    }

    // !!!!!!!!!!!!!!!!!!!!!!!!!!!!
    // ПРОСРОЧКА 31-й и более день. Для 1-го дня нужно дописывать и проверять
    private function get_peni_item($order)
    {
        $contract = $this->contracts->get_contract($order->contract_id);

        $passport_serial = str_replace([' ', '-'], '', $order->passport_serial);
        $passport_series = substr($passport_serial, 0, 4);
        $passport_number = substr($passport_serial, 4, 6);


        $ret_date_array = $this->ret_date_data($order);

        $ret_date = $ret_date_array[0];
        $days_past_due = $ret_date_array[1];
        $ret_date_body_summ = $ret_date_array[2];
        $ret_date_percents_summ = $ret_date_array[3];
        $ret_date_peni_summ = $ret_date_array[4];
        $new_ret_date = $ret_date_array[5];

        
        $data = new StdClass();

        $GROUPHEADER = new StdClass();
        $GROUPHEADER->event_number = "2.3";
        $GROUPHEADER->operation_code = "B";
        // $GROUPHEADER->event_date = date('d.m.Y', strtotime($order->operation->created));
        $GROUPHEADER->event_date = date('d.m.Y', strtotime($new_ret_date));

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
        $C6_REGNUM->is_special_tax = '0';

        $data->C6_REGNUM = $C6_REGNUM;


        $C17_UID = new StdClass();
        $C17_UID->uuid = $contract->uid;

        $data->C17_UID = $C17_UID;


        $C18_TRADE = new StdClass();
        $C18_TRADE->owner_indicator_code = '1';
        $C18_TRADE->opened_date = date('d.m.Y', strtotime($contract->inssuance_date));
        $C18_TRADE->trade_type_code = '1';
        $C18_TRADE->load_kind_code = '13';
        $C18_TRADE->account_type_code = '14';
        $C18_TRADE->is_consumer_loan = '1';
        $C18_TRADE->has_card = '1';
        $C18_TRADE->is_novation = '0';
        $C18_TRADE->is_money_source = '1';
        $C18_TRADE->is_money_borrower = '1';
        $C18_TRADE->close_date = date('d.m.Y', strtotime($contract->return_date));
        $C18_TRADE->lender_type_code = '2';
        $C18_TRADE->has_obtaining_part_creditor = '0';
        $C18_TRADE->has_credit_line = '0';
        $C18_TRADE->is_interest_rate_float = '0';
        $C18_TRADE->has_transfer_part_creditor = '0';
        $C18_TRADE->commit_date = date('d.m.Y', strtotime($contract->inssuance_date));

        $data->C18_TRADE = $C18_TRADE;


        $C19_ACCOUNTAMT = new StdClass();
        $C19_ACCOUNTAMT->credit_limit = str_replace('.', ',', sprintf("%01.2f", $contract->amount));
        $C19_ACCOUNTAMT->currency_code = 'RUB';
        $C19_ACCOUNTAMT->commit_currency_code = 'RUB';
        $C19_ACCOUNTAMT->amount_date = date('d.m.Y', strtotime($contract->inssuance_date));
        $C19_ACCOUNTAMT->commit_uuid = $contract->uid;

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
        $C22_OVERALLVAL->total_credit_amount_interest = str_replace('.', ',', sprintf("%01.2f", $contract->base_percent * 365));
        $C22_OVERALLVAL->total_credit_amount_monetary = str_replace('.', ',', sprintf("%01.2f", $interest_terms_amount));
        $C22_OVERALLVAL->total_credit_amount_date = date('d.m.Y', strtotime($contract->inssuance_date));

        $data->C22_OVERALLVAL = $C22_OVERALLVAL;


        $C24_FUNDDATE = new StdClass();
        $C24_FUNDDATE->funding_date = date('d.m.Y', strtotime($contract->inssuance_date));

        $data->C24_FUNDDATE = $C24_FUNDDATE;


        $C25_ARREAR = new StdClass();
        $C25_ARREAR->has_arrear = '1';
        $C25_ARREAR->start_amount_outstanding = str_replace('.', ',', sprintf("%01.2f", $contract->amount));
        $C25_ARREAR->is_last_payment_due = '0';

        $C25_ARREAR->amount_outstanding = str_replace('.', ',', sprintf("%01.2f", $ret_date_body_summ + $ret_date_percents_summ + $ret_date_peni_summ));
        $C25_ARREAR->principal_amount_outstanding = str_replace('.', ',', sprintf("%01.2f", $ret_date_body_summ));
        $C25_ARREAR->interest_amount_outstanding = str_replace('.', ',', sprintf("%01.2f", $ret_date_percents_summ + $ret_date_peni_summ));
        $C25_ARREAR->other_amount_outstanding = '0,00';
        // $C25_ARREAR->calculation_date = date('d.m.Y', strtotime($ret_date));
        // $C25_ARREAR->calculation_date = date('d.m.Y', time());
        $C25_ARREAR->calculation_date = date('d.m.Y', strtotime($new_ret_date));

        $data->C25_ARREAR = $C25_ARREAR;
        

        $C26_DUEARREAR = new StdClass();
        // $C26_DUEARREAR->start_date = date('d.m.Y', strtotime($contract->inssuance_date));
        // $C26_DUEARREAR->is_last_payment_due = '0';
        $C26_DUEARREAR->amount_outstanding = '0,00';
        // $C26_DUEARREAR->principal_amount_outstanding = '0,00';
        // $C26_DUEARREAR->interest_amount_outstanding = '0,00';
        // $C26_DUEARREAR->other_amount_outstanding = '0,00';
        // $C26_DUEARREAR->calculation_date = date('d.m.Y', strtotime($ret_date));

        $data->C26_DUEARREAR = $C26_DUEARREAR;

        $C27_PASTDUEARREAR = new StdClass();
        // $C27_PASTDUEARREAR->past_due_date = date('d.m.Y', strtotime($ret_date));
        $C27_PASTDUEARREAR->past_due_date = date('d.m.Y', strtotime($contract->return_date));
        $C27_PASTDUEARREAR->is_last_payment_due = "0";
        $C27_PASTDUEARREAR->amount_outstanding = str_replace('.', ',', sprintf("%01.2f", $ret_date_body_summ + $ret_date_percents_summ + $ret_date_peni_summ));
        $C27_PASTDUEARREAR->principal_amount_outstanding = str_replace('.', ',', sprintf("%01.2f", $ret_date_body_summ));
        $C27_PASTDUEARREAR->interest_amount_outstanding = str_replace('.', ',', sprintf("%01.2f", $ret_date_percents_summ + $ret_date_peni_summ));
        $C27_PASTDUEARREAR->other_amount_outstanding = '0,00';
        // $C27_PASTDUEARREAR->calculation_date = date('d.m.Y', strtotime($ret_date));
        // $C27_PASTDUEARREAR->calculation_date = date('d.m.Y', time());
        $C27_PASTDUEARREAR->calculation_date = date('d.m.Y', strtotime($new_ret_date));
        $C27_PASTDUEARREAR->principal_missed_date = date('d.m.Y', strtotime($contract->return_date));
        $C27_PASTDUEARREAR->interest_missed_date = date('d.m.Y', strtotime($contract->return_date));

        $data->C27_PASTDUEARREAR = $C27_PASTDUEARREAR;

        
        $last_payment_operation = $this->last_payment_data($order);

        $C28_PAYMT = new StdClass();

        if (!$last_payment_operation) {
            $C28_PAYMT->payment_amount = '0,00';
            $C28_PAYMT->amount_keep_code = '3';
            $C28_PAYMT->terms_due_code = '3';
            $C28_PAYMT->days_past_due = $days_past_due;
        }
        else{

            $last_payment_transaction = $this->transactions->get_transaction($last_payment_operation->transaction_id);

            $C28_PAYMT->payment_date = date('d.m.Y', strtotime($last_payment_operation->created));
            $C28_PAYMT->payment_amount = str_replace('.', ',', sprintf("%01.2f", $last_payment_operation->amount));
            $C28_PAYMT->principal_payment_amount = str_replace('.', ',', sprintf("%01.2f", $last_payment_transaction->loan_body_summ));
            $C28_PAYMT->interest_payment_amount = str_replace('.', ',', sprintf("%01.2f",  $last_payment_transaction->loan_percents_summ + $last_payment_transaction->loan_peni_summ));
            $C28_PAYMT->other_payment_amount = "0,00";
            $C28_PAYMT->total_amount = str_replace('.', ',', sprintf("%01.2f", $last_payment_operation->total_amount));
            $C28_PAYMT->principal_total_amount = str_replace('.', ',', sprintf("%01.2f", $last_payment_operation->principal_total_amount));
            $C28_PAYMT->interest_total_amount = str_replace('.', ',', sprintf("%01.2f", $last_payment_operation->interest_total_amount));
            $C28_PAYMT->other_total_amount = "0,00";
            $C28_PAYMT->amount_keep_code = '3';
            $C28_PAYMT->terms_due_code = '3';
            $C28_PAYMT->days_past_due = $days_past_due;
        }



        $data->C28_PAYMT = $C28_PAYMT;


        $C29_MONTHAVERPAYMT = new StdClass();
        $C29_MONTHAVERPAYMT->average_payment_amount = round($ret_date_body_summ + $ret_date_percents_summ + $ret_date_peni_summ);
        $C29_MONTHAVERPAYMT->calculation_date = date('d.m.Y', strtotime($ret_date));

        $data->C29_MONTHAVERPAYMT = $C29_MONTHAVERPAYMT;
        

        $C54_OBLIGACCOUNT = new StdClass();
        $C54_OBLIGACCOUNT->has_obligation = 1;
        $C54_OBLIGACCOUNT->interest_rate = str_replace('.', ',', sprintf("%01.2f", $contract->base_percent * 365));
        $C54_OBLIGACCOUNT->has_preferential_financing = '0';

        $data->C54_OBLIGACCOUNT = $C54_OBLIGACCOUNT;


        $C56_OBLIGPARTTAKE = new StdClass();
        $C56_OBLIGPARTTAKE->flag_indicator_code = '1';
        $C56_OBLIGPARTTAKE->approved_loan_type_code = '13';
        $C56_OBLIGPARTTAKE->agreement_number = $contract->uid;
        $C56_OBLIGPARTTAKE->funding_date = date('d.m.Y', strtotime($contract->inssuance_date));
        $C56_OBLIGPARTTAKE->default_flag = $days_past_due > 90 ? '1' : '0';
        $C56_OBLIGPARTTAKE->loan_indicator = 0;

        $data->C56_OBLIGPARTTAKE = $C56_OBLIGPARTTAKE;

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

    public function last_missed_percents_payment($order)
    {
        $this->db->query("
            SELECT * FROM __operations 
            WHERE type IN ('PENI', 'PAY')
            AND order_id = ?
            ORDER BY created
        ", $order->order_id);
        
        $operations = $this->db->results();

        $contract = $this->contracts->get_contract($order->contract_id);
        $missed_payment_date = $contract->inssuance_date;
        $missed_payment_date_search = true;

        foreach ($operations as $operation) {
            if ($operation->type == 'PENI') {
                if ($missed_payment_date_search) {
                    $missed_payment_date = $operation->created;
                    $missed_payment_date_search = false;
                }
            }
            else{
                $missed_payment_date_search = true;
            }
        }

        return $missed_payment_date;
    }

    public function ret_date_data($order)
    {
        $contract = $this->contracts->get_contract($order->contract_id);

        $ret_date = date('Y-m-d', strtotime($contract->return_date) + 86400);
        $last_ret_date = $ret_date;
        
        $new_ret_date = date('Y-m-d', strtotime($ret_date) + 30 * 86400);
        $days_past_due = 1;
        
        while ($new_ret_date < date('Y-m-d') && $new_ret_date > '2022-12-31') {
            if ($new_ret_date < date('Y-m-d')) {
                $ret_date = $new_ret_date;
                $last_ret_date = $ret_date;
                $days_past_due += 30;
            }
            $new_ret_date = date('Y-m-d', strtotime($ret_date) + 30 * 86400);
        }

        // Если займ закрыт
        if ($contract->status == 3) {
            $ret_date = date('Y-m-d', strtotime($contract->close_date));
            $new_ret_date = date('Y-m-d', strtotime($contract->close_date));
        }

        $ret_date_operations = $this->operations->get_operations(['contract_id' => $order->contract->id, 'date_from' => $ret_date, 'date_to' => $ret_date, 'type' => ['PENI', 'PERCENTS'], 'sort' => 'created_asc']);

        if (!count($ret_date_operations)) {
            $this->db->query("
                SELECT * FROM `s_operations` 
                WHERE contract_id=? AND type in ('PENI', 'PERCENTS')
                ORDER BY created desc LIMIT 2
            ", $order->contract->id);
            
            $ret_date_operations = $this->db->results();
            
        }

        
        if (!count($ret_date_operations)) {
            $this->db->query("
            SELECT * FROM `s_operations` 
            WHERE contract_id=? AND type in ('PAY', 'P2P')
            ORDER BY created asc LIMIT 2
            ", $order->contract->id);
            
            $ret_date_operations1 = $this->db->results();

            $p2pDate = '';
            $payDate = '';
            foreach ($ret_date_operations1 as $ret_date_operation) {
                if ($ret_date_operation->type == 'P2P') {
                    $p2pDate = $ret_date_operation->created;
                }
                if ($ret_date_operation->type == 'PAY') {
                    $payDate = $ret_date_operation->created;
                }
            }

            $ret_date_operations = [];
            if($p2pDate && date('Y-m-d', strtotime($p2pDate)) == date('Y-m-d', strtotime($payDate))){
                foreach ($ret_date_operations1 as $ret_date_operation) {
                    if ($ret_date_operation->type == 'P2P') {
                        $ret_date_operations[] = $ret_date_operation;
                    }
                }
            }
            
        }
        
        if ($ret_date_operations) {
            $ret_date_body_summ = 0;
            $ret_date_percents_summ = 0;
            $ret_date_peni_summ = 0;
            foreach ($ret_date_operations as $ret_date_operation) {
                if ($ret_date_operation->type == 'P2P') {
                    $ret_date_body_summ = $ret_date_operation->amount;
                }
                else{
                    if ($ret_date_body_summ < $ret_date_operation->loan_body_summ) {
                        $ret_date_body_summ = $ret_date_operation->loan_body_summ;
                    }
                    if ($ret_date_percents_summ < $ret_date_operation->loan_percents_summ) {
                        $ret_date_percents_summ = $ret_date_operation->loan_percents_summ;
                    }
                    if ($ret_date_peni_summ < $ret_date_operation->loan_peni_summ) {
                        $ret_date_peni_summ = $ret_date_operation->loan_peni_summ;
                    }
                }
            }
        }
        else{
            $ret_date_body_summ = $contract->loan_body_summ;
            $ret_date_percents_summ = $contract->loan_percents_summ;
            $ret_date_peni_summ = $contract->loan_peni_summ;
        }

        return [$ret_date, $days_past_due, $ret_date_body_summ, $ret_date_percents_summ, $ret_date_peni_summ, $last_ret_date];
    }

    public function last_payment_data($order)
    {
        $last_payment_operations = $this->operations->get_operations(['contract_id' => $order->contract->id, 'type' => ['PAY'], 'sort' => 'created_asc']);

        if ($last_payment_operations) {

            $total_amount = 0;
            $principal_total_amount = 0;
            $interest_total_amount = 0;
            foreach ($last_payment_operations as $last_payment_operation) {
                $last_payment = $last_payment_operation;

                $last_payment_transaction = $this->transactions->get_transaction($last_payment_operation->transaction_id);
                
                $total_amount += $last_payment_transaction->loan_body_summ + $last_payment_transaction->loan_percents_summ + $last_payment_transaction->loan_peni_summ;
                $principal_total_amount += $last_payment_transaction->loan_body_summ;
                $interest_total_amount += $last_payment_transaction->loan_percents_summ + $last_payment_transaction->loan_peni_summ;
            }
            $last_payment->total_amount = $total_amount;
            $last_payment->principal_total_amount = $principal_total_amount;
            $last_payment->interest_total_amount = $interest_total_amount;
            return $last_payment;
        }
        else{
            return false;
        }
    }

}