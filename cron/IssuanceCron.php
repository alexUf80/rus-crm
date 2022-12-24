<?php
error_reporting(-1);
ini_set('display_errors', 'On');


//chdir('/home/v/vse4etkoy2/nalic_eva-p_ru/public_html/');
chdir(dirname(__FILE__) . '/../');

require 'autoload.php';

/**
 * IssuanceCron
 *
 * Скрипт выдает кредиты, и списывает страховку
 *
 * @author Ruslan Kopyl
 * @copyright 2021
 * @version $Id$
 * @access public
 */
class IssuanceCron extends Core
{
    public function __construct()
    {
        parent::__construct();

        $i = 0;
        while ($i < 10) {
            $this->run();
            $i++;
        }
    }

    private function run()
    {
        if ($contracts = $this->contracts->get_contracts(array('status' => 1, 'limit' => 1))) {

            foreach ($contracts as $contract) {
                $res = $this->best2pay->pay_contract_with_register($contract->id, $contract->service_insurance);

                if ($res == 'APPROVED') {

                    //TODO: Создаем доки при выдаче

                    $ob_date = new DateTime();
                    $ob_date->add(DateInterval::createFromDateString($contract->period . ' days'));
                    $return_date = $ob_date->format('Y-m-d H:i:s');


                    // Снимаем страховку если есть
                    if (!empty($contract->service_insurance)) 
                    {
                        $insurance_cost = $this->insurances->get_insurance_cost($contract->amount);

                        if ($insurance_cost > 0)
                        {
                            $insurance_amount = $insurance_cost * 100;
    
                            $description = 'Страховой полис';
    
                            $xml = $this->best2pay->purchase_by_token($contract->card_id, $insurance_amount, $description);
                            $status = (string)$xml->state;
    
                            if ($status == 'APPROVED') {
                                $transaction = $this->transactions->get_register_id_transaction($xml->order_id);
    
                                $contract = $this->contracts->get_contract($contract->id);
    
                                $operation_id = $this->operations->add_operation(array(
                                    'contract_id' => $contract->id,
                                    'user_id' => $contract->user_id,
                                    'order_id' => $contract->order_id,
                                    'type' => 'INSURANCE',
                                    'amount' => $insurance_cost,
                                    'created' => date('Y-m-d H:i:s'),
                                    'transaction_id' => $transaction->id,
                                ));
    
                                $dt = new DateTime();
                                $dt->add(new DateInterval('P1M'));
                                $end_date = $dt->format('Y-m-d 23:59:59');

                                try{
                                    $contract->insurance = new InsurancesORM();
                                    $contract->insurance->amount = $insurance_cost;
                                    $contract->insurance->user_id = $contract->user_id;
                                    $contract->insurance->order_id = $contract->order_id;
                                    $contract->insurance->start_date = date('Y-m-d 00:00:00', time() + (1 * 86400));
                                    $contract->insurance->end_date = $end_date;
                                    $contract->insurance->operation_id = $operation_id;
                                    $contract->insurance->save();

                                    $contract->insurance->number = InsurancesORM::create_number($contract->insurance->id);

                                    InsurancesORM::where('id', $contract->insurance->id)->update(['number' => $contract->insurance->number]);
                                }catch (Exception $e)
                                {

                                }

                                    $this->contracts->update_contract($contract->id, array(
                                    'insurance_id' => $contract->insurance_id,
                                    'loan_body_summ' => $contract->amount + $insurance_cost
                                ));

                                //создаем документы для страховки
                                $this->create_document('POLIS', $contract);
                                $this->create_document('KID', $contract);
    
    
                                //Отправляем чек по страховке
                                $return = $this->Cloudkassir->send_insurance($operation_id);

                                if (!empty($return))
                                {
                                    $resp = json_decode($return);
    
                                    $this->receipts->add_receipt(array(
                                        'user_id' => $contract->user_id,
                                        'order_id' => $contract->order_id,
                                        'contract_id' => $contract->id,
                                        'insurance_id' => $contract->insurance_id,
                                        'receipt_url' => (string)$resp->Model->ReceiptLocalUrl,
                                        'response' => serialize($return),
                                        'created' => date('Y-m-d H:i:s'),
                                    ));
                                }
                            }
                        }
                    }

                    if (!empty($contract->service_insurance)) {
                        $insurance_cost = $this->insurances->get_insurance_cost($contract->amount);
                        $contract->amount += $insurance_cost;
                    }

                    $this->create_document('IND_USLOVIYA_NL', $contract);

                    $this->contracts->update_contract($contract->id, array(
                        'status' => 2,
                        'inssuance_date' => date('Y-m-d H:i:s'),
                        'loan_body_summ' => $contract->amount,
                        'loan_percents_summ' => 0,
                        'return_date' => $return_date,
                    ));

                    $this->orders->update_order($contract->order_id, array('status' => 5));

                    $this->operations->add_operation(array(
                        'contract_id' => $contract->id,
                        'user_id' => $contract->user_id,
                        'order_id' => $contract->order_id,
                        'type' => 'P2P',
                        'amount' => $contract->amount,
                        'created' => date('Y-m-d H:i:s'),
                    ));

                    $order = OrdersORM::find($contract->order_id);

                    if(!empty($order->utm_source) && $order->utm_source == 'leadstech')
                        PostbacksCronORM::insert(['order_id' => $order->id, 'status' => 1, 'goal_id' => 3]);

                    $this->db->query("
                    SELECT *
                    FROM s_transactions
                    where user_id = ?
                    and description = 'Привязка карты'
                    and reason_code = 1
                    order by id DESC
                    limit 1
                    ", $contract->user_id);

                    $transaction = $this->db->result();

                    $this->best2pay->reverseCardEnroll($transaction->register_id, $contract->order_id);

                }else {
                    $this->contracts->update_contract($contract->id, array('status' => 6));

                    $this->orders->update_order($contract->order_id, array('status' => 6)); // статус 6 - не удалосб выдать

                    if ($order = $this->orders->get_order((int)$contract->order_id)) {
                        $this->soap1c->send_order_status($order->id_1c, 'Отказано');
                    }
                }
            }
        }
    }

    public function create_document($document_type, $contract)
    {
        $ob_date = new DateTime();
        $ob_date->add(DateInterval::createFromDateString($contract->period . ' days'));
        $return_date = $ob_date->format('Y-m-d H:i:s');

        $return_amount = round($contract->amount + $contract->amount * $contract->base_percent * $contract->period / 100, 2);
        $return_amount_rouble = (int)$return_amount;
        $return_amount_kop = ($return_amount - $return_amount_rouble) * 100;

        $contract_order = $this->orders->get_order((int)$contract->order_id);

        $insurance_cost = $this->insurances->get_insurance_cost($contract_order);

        $params = array(
            'lastname' => $contract_order->lastname,
            'firstname' => $contract_order->firstname,
            'patronymic' => $contract_order->patronymic,
            'phone' => $contract_order->phone_mobile,
            'birth' => $contract_order->birth,
            'number' => $contract->number,
            'contract_date' => date('Y-m-d H:i:s'),
            'created' => date('Y-m-d H:i:s'),
            'return_date' => $return_date,
            'return_date_day' => date('d', strtotime($return_date)),
            'return_date_month' => date('m', strtotime($return_date)),
            'return_date_year' => date('Y', strtotime($return_date)),
            'return_amount' => $return_amount,
            'return_amount_rouble' => $return_amount_rouble,
            'return_amount_kop' => $return_amount_kop,
            'base_percent' => $contract->base_percent,
            'amount' => $contract->amount,
            'period' => $contract->period,
            'return_amount_percents' => round($contract->amount * $contract->base_percent * $contract->period / 100, 2),
            'passport_serial' => $contract_order->passport_serial,
            'passport_date' => $contract_order->passport_date,
            'subdivision_code' => $contract_order->subdivision_code,
            'passport_issued' => $contract_order->passport_issued,
            'passport_series' => substr(str_replace(array(' ', '-'), '', $contract_order->passport_serial), 0, 4),
            'passport_number' => substr(str_replace(array(' ', '-'), '', $contract_order->passport_serial), 4, 6),
            'asp' => $contract->accept_code,
            'insurance_summ' => $insurance_cost,
        );

        $params['user'] = $this->users->get_user($contract->user_id);
        $params['order'] = $this->orders->get_order($contract->order_id);
        $params['contract'] = $contract;


        $this->documents->create_document(array(
            'user_id' => $contract->user_id,
            'order_id' => $contract->order_id,
            'contract_id' => $contract->id,
            'type' => $document_type,
            'params' => json_encode($params),
        ));

    }

}

$cron = new IssuanceCron();
