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
                $operations = $this->operations->get_operations(['type' => 'P2P', 'contract_id' => $contract->id]);
                if (count($operations) > 0) {
                    $this->contracts->update_contract($contract->id, array(
                        'status' => 2,
                    ));
                    continue;
                }
                $res = $this->best2pay->pay_contract_with_register($contract->id, $contract->service_insurance, $contract->service_sms);

                if ($res == 'APPROVED') {

                    //Создаем доки при повторной выдаче
                    $count_closed_contracts = $this->contracts->count_contracts(array(
                        'user_id' => $contract->user_id,
                    ));
                    if ($count_closed_contracts >= 2){
                        
                        $this->user = $this->users->get_user($contract->user_id);

                        $passport = str_replace([' ','-'], '', $this->user->passport_serial);
                        $passport_serial = substr($passport, 0, 4);
                        $passport_number = substr($passport, 4, 6);

                        $params = array(
                            'lastname' => $this->user->lastname,
                            'firstname' => $this->user->firstname,
                            'patronymic' => $this->user->patronymic,
                            'gender' => $this->user->gender,
                            'phone' => $this->user->phone_mobile,
                            'birth' => $this->user->birth,
                            'birth_place' => $this->user->birth_place,
                            'inn' => $this->user->inn,
                            'snils' => $this->user->snils,
                            'email' => $this->user->email,
                            'created' => $this->user->created,
            
                            'passport_serial' => $passport_serial,
                            'passport_number' => $passport_number,
                            'passport_date' => $this->user->passport_date,
                            'passport_code' => $this->user->subdivision_code,
                            'passport_issued' => $this->user->passport_issued,
            
                            // 'regindex' => $this->user->Regindex,
                            // 'regregion' => $this->user->Regregion,
                            // 'regcity' => $this->user->Regcity,
                            // 'regstreet' => $this->user->Regstreet,
                            // 'reghousing' => $this->user->Reghousing,
                            // 'regbuilding' => $this->user->Regbuilding,
                            // 'regroom' => $this->user->Regroom,
                            // 'faktindex' => $this->user->Faktindex,
                            // 'faktregion' => $this->user->Faktregion,
                            // 'faktcity' => $this->user->Faktcity,
                            // 'faktstreet' => $this->user->Faktstreet,
                            // 'fakthousing' => $this->user->Fakthousing,
                            // 'faktbuilding' => $this->user->Faktbuilding,
                            // 'faktroom' => $this->user->Faktroom,
            
                            'profession' => $this->user->profession,
                            'workplace' => $this->user->workplace,
                            'workphone' => $this->user->workphone,
                            // 'chief_name' => $this->user->chief_name,
                            // 'chief_position' => $this->user->chief_position,
                            // 'chief_phone' => $this->user->chief_phone,
                            'income' => $this->user->income,
                            'expenses' => $this->user->expenses,
            
                            'first_loan_amount' => $this->user->first_loan_amount,
                            'first_loan_period' => $this->user->first_loan_period,
            
                            'number' => $contract->order_id,
                            'create_date' => date('Y-m-d'),
                            'asp' => $this->user->sms,
                            'accept_code' => $contract->accept_code,
                        );
                        if (!empty($this->user->contact_person_name))
                        {
                            $params['contactperson_phone'] = $this->user->contact_person_phone;
            
                            $contact_person_name = explode(' ', $this->user->contact_person_name);
                            $params['contactperson_name'] = $this->user->contact_person_name;
                            $params['contactperson_lastname'] = isset($contact_person_name[0]) ? $contact_person_name[0] : '';
                            $params['contactperson_firstname'] = isset($contact_person_name[1]) ? $contact_person_name[1] : '';
                            $params['contactperson_patronymic'] = isset($contact_person_name[2]) ? $contact_person_name[2] : '';
                        }
                        if (!empty($this->user->contact_person2_name))
                        {
                            $params['contactperson2_phone'] = $this->user->contact_person_phone;
            
                            $contact_person2_name = explode(' ', $this->user->contact_person2_name);
                            $params['contactperson2_name'] = $this->user->contact_person2_name;
                            $params['contactperson2_lastname'] = isset($contact_person2_name[0]) ? $contact_person2_name[0] : '';
                            $params['contactperson2_firstname'] = isset($contact_person2_name[1]) ? $contact_person2_name[1] : '';
                            $params['contactperson2_patronymic'] = isset($contact_person2_name[2]) ? $contact_person2_name[2] : '';
                        }

                        // Согласие на ОПД
                        $this->documents->create_document(array(
                            'user_id' => $this->user->id,
                            'order_id' => $contract->order_id,
                            'contract_id' => $contract->id,
                            'type' => 'SOGLASIE_OPD',
                            'params' => json_encode($params),
                        ));
                        
                        // Заявление на получение займа
                        $this->documents->create_document(array(
                            'user_id' => $this->user->id,
                            'order_id' => $contract->order_id,
                            'contract_id' => $contract->id,
                            'type' => 'ANKETA_PEP',
                            'params' => json_encode($params),
                        ));
            
                    }

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
    
                            $xml = $this->best2pay->recurring_by_token($contract->card_id, $insurance_amount, $description);
                            $status = (string)$xml->state;
    
                            if ($status == 'APPROVED') {
                                $transaction = $this->transactions->get_register_id_transaction($xml->order_id);
    
                                $contract = $this->contracts->get_contract($contract->id);
    
                                $max_service_value = $this->operations->max_service_number();

                                $operation_id = $this->operations->add_operation(array(
                                    'contract_id' => $contract->id,
                                    'user_id' => $contract->user_id,
                                    'order_id' => $contract->order_id,
                                    'type' => 'INSURANCE',
                                    'amount' => $insurance_cost,
                                    'created' => date('Y-m-d H:i:s'),
                                    'transaction_id' => $transaction->id,
                                    'service_number' => $max_service_value,
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
                                    // 'loan_body_summ' => $contract->amount + $insurance_cost
                                    'loan_body_summ' => $contract->amount
                                ));

                                //создаем документы для страховки
                                $this->create_document('POLIS', $contract);
                                $this->create_document('KID', $contract);

                                // //Отправляем чек по страховке
                                // $return = $this->Cloudkassir->send_insurance($operation_id);

                                // if (!empty($return))
                                // {
                                //     $resp = json_decode($return);
    
                                //     $this->receipts->add_receipt(array(
                                //         'user_id' => $contract->user_id,
                                //         'name' => 'Страхование от несчастных случаев',
                                //         'order_id' => $contract->order_id,
                                //         'contract_id' => $contract->id,
                                //         'insurance_id' => $contract->insurance_id,
                                //         'receipt_url' => (string)$resp->Model->ReceiptLocalUrl,
                                //         'response' => serialize($return),
                                //         'created' => date('Y-m-d H:i:s'),
                                //     ));
                                // }
                            }
                        }
                    }

                    // Снимаем смс-информирование если есть
                    $sms_cost = 149;
                    if (!empty($contract->service_sms)) 
                    {
                        $sms_amount = $sms_cost * 100;

                        $description = 'СМС-информирование';

                        $xml = $this->best2pay->recurring_by_token($contract->card_id, $sms_amount, $description);
                        $status = (string)$xml->state;

                        if ($status == 'APPROVED') {
                            $transaction = $this->transactions->get_register_id_transaction($xml->order_id);

                            $contract = $this->contracts->get_contract($contract->id);

                            $max_service_value = $this->operations->max_service_number();

                            $operation_id = $this->operations->add_operation(array(
                                'contract_id' => $contract->id,
                                'user_id' => $contract->user_id,
                                'order_id' => $contract->order_id,
                                'type' => 'BUD_V_KURSE',
                                'amount' => $sms_cost,
                                'created' => date('Y-m-d H:i:s'),
                                'transaction_id' => $transaction->id,
                                'service_number' => $max_service_value,
                            ));

                            // $dt = new DateTime();
                            // $dt->add(new DateInterval('P1M'));
                            // $end_date = $dt->format('Y-m-d 23:59:59');

                            // try{
                            //     $contract->insurance = new InsurancesORM();
                            //     $contract->insurance->amount = $insurance_cost;
                            //     $contract->insurance->user_id = $contract->user_id;
                            //     $contract->insurance->order_id = $contract->order_id;
                            //     $contract->insurance->start_date = date('Y-m-d 00:00:00', time() + (1 * 86400));
                            //     $contract->insurance->end_date = $end_date;
                            //     $contract->insurance->operation_id = $operation_id;
                            //     $contract->insurance->save();

                            //     $contract->insurance->number = InsurancesORM::create_number($contract->insurance->id);

                            //     InsurancesORM::where('id', $contract->insurance->id)->update(['number' => $contract->insurance->number]);
                            // }catch (Exception $e)
                            // {

                            // }

                            // $this->contracts->update_contract($contract->id, array(
                            //     // 'insurance_id' => $contract->insurance_id,
                            //     'loan_body_summ' => $contract->amount + $sms_cost
                            // ));

                            //создаем документы для страховки
                            // $this->create_document('POLIS', $contract);
                            // $this->create_document('KID', $contract);

                            // if (!empty($return))
                            // {
                            //     $resp = json_decode($return);

                            //     $this->receipts->add_receipt(array(
                            //         'user_id' => $contract->user_id,
                            //         'order_id' => $contract->order_id,
                            //         'contract_id' => $contract->id,
                            //         'insurance_id' => $contract->insurance_id,
                            //         'receipt_url' => (string)$resp->Model->ReceiptLocalUrl,
                            //         'response' => serialize($return),
                            //         'created' => date('Y-m-d H:i:s'),
                            //     ));
                            // }
                        }
                    }

                    // if (!empty($contract->service_insurance)) {
                    //     $insurance_cost = $this->insurances->get_insurance_cost($contract->amount);
                    //     $contract->amount += $insurance_cost;
                    // }

                    // if (!empty($contract->service_sms)) {
                    //     $contract->amount += $sms_cost;
                    // }

                    $user = $this->users->get_users($contract->user_id);

                    $user = $user[0];
                    
                    $contract->user_phone_mobile = $user->phone_mobile;
                    $contract->user_email = $user->email;

                    $this->create_document('IND_USLOVIYA_NL', $contract);
                    $this->create_document('PRIL_1', $contract);

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

                    if($this->config->send_onec == 1)
                        Onec::sendRequest(['method' => 'send_loan', 'params' => $contract->order_id]);

                    $order = $this->orders->get_order($contract->order_id);
                    if (!empty($order->utm_source))
                    {
                        $this->leadgens->add_postback([
                            'order_id' => $order->order_id,
                            'created' => date('Y-m-d H:i:s'),
                            'lead_name' => $order->utm_source,
                            'webmaster' => $order->webmaster_id,
                            'click_hash' => $order->click_hash,
                            'offer_id' => 0,
                            'type' => 'approve',
                        ]);
                    }

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

        $params['pan'] = $this->cards->get_card($contract->card_id)->pan;

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
