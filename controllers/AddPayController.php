<?php

error_reporting(-1);
ini_set('display_errors', 'Off');

class AddPayController extends Controller
{
    public function fetch()
    {
        $order_id = $this->request->get('order_id');
        $user_id = $this->request->get('user_id');

        if ($this->request->post('action') == 'send_pay') {

            $date = date('Y-m-d', strtotime($this->request->post('date')));
            $sum = $this->request->post('sum');
            $pay_type = $this->request->post('pay_type');
            $pay_source = $this->request->post('pay_source');

            $query = $this->db->placehold("
            SELECT * 
            FROM __contracts
            WHERE order_id = ?
            ", (int)$order_id);
            $this->db->query($query);
            $contract = $this->db->result();

            $transaction_id = $this->transactions->add_transaction(array(
                'user_id' => $user_id,
                'amount' => $sum * 100,
                'sector' => 0,
                'register_id' => 0,
                'reference' => ' ',
                'description' => 'Оплата по договору ' . $contract->number,
                'created' => $date,
                'prolongation' => ($pay_type == 1) ? 0 : 1,
                'commision_summ' => 0,
                'sms' => 0,
                'body' => ' ',
            ));


            $rest_amount = $sum;

            // списываем проценты
            if ($contract->loan_percents_summ > 0) {
                if ($rest_amount >= $contract->loan_percents_summ) {
                    $contract_loan_percents_summ = 0;
                    $rest_amount -= $contract->loan_percents_summ;
                    $transaction_loan_percents_summ = $contract->loan_percents_summ;
                } else {
                    $contract_loan_percents_summ = $contract->loan_percents_summ - $rest_amount;
                    $transaction_loan_percents_summ = $rest_amount;
                    $rest_amount = 0;
                }
            }

            // списываем основной долг
            if ($contract->loan_body_summ > 0) {
                if ($rest_amount >= $contract->loan_body_summ) {
                    $contract_loan_body_summ = 0;
                    $transaction_loan_body_summ = $contract->loan_body_summ;
                } else {
                    $contract_loan_body_summ = $contract->loan_body_summ - $rest_amount;
                    $transaction_loan_body_summ = $rest_amount;
                }
            }

            $this->transactions->update_transaction($transaction_id, array(
                'loan_body_summ' => $transaction_loan_body_summ,
                'loan_percents_summ' => $transaction_loan_percents_summ,
            ));

            $this->operations->add_operation(array(
                'contract_id' => $contract->id,
                'user_id' => $user_id,
                'order_id' => $order_id,
                'transaction_id' => $transaction_id,
                'type' => 'PAY',
                'amount' => $sum,
                'created' => $date,
                'sent_date' => $date,
                'loan_body_summ' => $contract_loan_body_summ,
                'loan_percents_summ' => $contract_loan_percents_summ,
                'loan_charge_summ' => 0,
                'loan_peni_summ' => 0,
                'type_payment' => ($pay_source == 1) ? 0 : 1
            ));

            if (!empty($contract->collection_status)) {
                $date1 = new DateTime(date('Y-m-d', strtotime($contract->return_date)));
                $date2 = new DateTime(date('Y-m-d'));

                $diff = $date2->diff($date1);
                $contract->expired_days = $diff->days;

                $collection_order = array(
                    'transaction_id' => $transaction_id,
                    'manager_id' => $contract->collection_manager_id,
                    'contract_id' => $contract->id,
                    'created' => $date,
                    'body_summ' => $contract_loan_body_summ,
                    'percents_summ' => empty($contract_loan_percents_summ) ? 0 : $contract_loan_percents_summ,
                    'charge_summ' => 0,
                    'peni_summ' => 0,
                    'commision_summ' => 0,
                    'closed' => 0,
                    'prolongation' => 0,
                    'collection_status' => $contract->collection_status,
                    'expired_days' => $contract->expired_days,
                );

                $this->collections->add_collection($collection_order);
            }

            $this->contracts->update_contract($contract->id, array(
                'loan_body_summ' => $contract_loan_body_summ,
                'loan_percents_summ' => $contract_loan_percents_summ,
            ));

            if ($pay_source == 1) {
                if ($contract_loan_percents_summ == 0 && $contract_loan_body_summ == 0) {
                    $this->contracts->update_contract($contract->id, array(
                        'close_date' => $date,
                        'collection_status' => 0,
                        'status' => 3,
                    ));

                    $this->orders->update_order($order_id, array(
                        'status' => 7,
                    ));
                }
            } else {
                if($pay_type == 2){
                    if ($contract->status == 2) {
                        $new_return_date = date('Y-m-d H:i:s', strtotime($contract->return_date . "+{$this->settings->prolongation_period} days"));
                    } else {
                        $new_return_date = date('Y-m-d H:i:s', strtotime($date . " +{$this->settings->prolongation_period} days"));
                    }

                    $max_return_date = date('Y-m-d H:i:s', strtotime($contract->inssuance_date . '+151 days'));

                    $this->contracts->update_contract($contract->id, array(
                        'return_date' => ($new_return_date < $max_return_date) ? $new_return_date : $max_return_date,
                        'prolongation' => $contract->prolongation + 1,
                        'collection_status' => 0,
                        'status' => 2,
                    ));
                }
            }

            exit;

        } else {
            $this->design->assign('order_id', $order_id);
            $this->design->assign('user_id', $user_id);

            return $this->design->fetch('add_pay.tpl');
        }
    }
}