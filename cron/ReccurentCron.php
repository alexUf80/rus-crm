<?php
error_reporting(-1);
ini_set('display_errors', 'On');

chdir(dirname(__FILE__).'/../');

require 'autoload.php';

class ReccurentCron extends Core
{
    
    public function __construct()
    {
    	parent::__construct();
    }

    public function run()
    {

        if ($contracts = $this->contracts->get_contracts(array('status' => [4]))) {

            //Получаем настройки рекурентов
            $setting = RecurrentConfigORM::query()->where('actual', '=', 1)->first();
            if (!$setting) {
                return;
            }
            $attempts = unserialize($setting->attempts);
            foreach ($contracts as $contract) {

                //Получаем дату просрочки
                $date1 = new DateTime(date('Y-m-d', strtotime($contract->return_date)));
                $date2 = new DateTime(date('Y-m-d'));
                $diff = $date2->diff($date1);

                echo "Count attempts ".count($attempts)."\r\n";
                //Если кол-во дней совпадает и нет попыток
                if (($diff->days >= $setting->days) && ($contract->reccurent_attempt < count($attempts))) {

                    // получаем текущую попытку для контракта
                    $attempt = $attempts[$contract->reccurent_attempt] ?? $attempts[0];
                    // получаем общую сумму для списания
                    // основной долг + проценты + пени
                    $contract_total_summ = $contract->loan_body_summ + $contract->loan_percents_summ + $contract->loan_peni_summ;

                    // если в настройке указаны проценты нужно перевести в деньги
                    if ($attempt['type'] != 'price') {
                        $percent = $attempt['summ'];
                        $amount = $contract_total_summ * ($percent / 100);
                    } else {
                        $amount = $attempt['summ'];
                    }

                    // если общая сумма списания превышает ту что в настройке,
                    // приравниваем её к общей сумме долга
                    if ($amount > $contract_total_summ) {
                        $amount = $contract_total_summ;
                    }

                    $amount = $amount * 100;

                    $order = $this->orders->get_order($contract->order_id);
                    echo "Start reccurent\r\n";
                    $reccurent_pay = $this->best2pay->reccurent_pay($order, $amount, $setting->id);
                    if (!$reccurent_pay) {
                        $this->contracts->update_contract($contract->id, array(
                            'reccurent_status' => 0,
                        ));
                    } else {
                        $amount = $amount / 100;
                        $save_amount = $amount;

                        $loan_peni_summ = $contract->loan_peni_summ;
                        $loan_percents_summ = $contract->loan_percents_summ;
                        $loan_body_summ = $contract->loan_body_summ;
                        echo "Amount = $amount calc peni\r\n";
                        if ($amount >= $loan_peni_summ) {
                            $amount -= $loan_peni_summ;
                            $loan_peni_summ = 0;

                            echo "Amount = $amount calc percents\r\n";
                            if ($amount >= $loan_percents_summ) {
                                $amount -= $loan_percents_summ;
                                $loan_percents_summ = 0;

                                echo "Amount = $amount calc body\r\n";
                                if ($amount >= $loan_body_summ) {
                                    $loan_body_summ = 0;
                                } else {
                                    $loan_body_summ -= $amount;
                                }

                            } else {
                                $loan_percents_summ -= $amount;
                            }

                        } else {
                            $loan_peni_summ -= $amount;
                        }

                        $save = [
                            'loan_body_summ' => $loan_body_summ,
                            'loan_percents_summ' => $loan_percents_summ,
                            'loan_peni_summ' => $loan_peni_summ,
                            'reccurent_attempt' => $contract->reccurent_attempt + 1,
                            'reccurent_summ' => $contract->reccurent_summ + $save_amount,
                            'reccurent_status' => 1,
                        ];

                        if ($loan_body_summ <= 0) {
                            $save['status'] = 3;
                            $save['close_date'] = date('Y-m-d H:i:s');
                            $save['collection_status'] = 0;
                            $save['collection_manager_id'] = 0;
                            $this->orders->update_order($contract->order_id, ['status' => 7]);

                        }
                        $this->contracts->update_contract($contract->id, $save);
                    }
                }


            }

        }


        exit;
    }
 
}

$cron = new ReccurentCron();
$cron->run();
