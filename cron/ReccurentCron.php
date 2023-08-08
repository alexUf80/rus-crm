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

                //Если кол-во дней совпадает и нет попыток
                if (($diff->days >= $setting->days) && ($contract->reccurent_attempt < count($attempts))) {

                    // получаем текущую попытку для контракта
                    $attempt = $attempts[$contract->reccurent_attempt];
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

                    $reccurent_pay = $this->best2pay->reccurent_pay($order, $amount, $setting);
                    if (!$reccurent_pay) {
                        $this->contracts->update_contract($contract->id, array(
                            'reccurent_status' => 0,
                        ));
                    } else {
                        $amount = $amount / 100;

                        $loan_peni_summ = $contract->loan_peni_summ;
                        $loan_percents_summ = $contract->loan_percents_summ;
                        $loan_body_summ = $contract->loan_body_summ;

                        $amount -= $loan_peni_summ;
                        if ($amount >= 0) {
                            $loan_peni_summ = 0;
                        }

                        $amount -= $loan_percents_summ;
                        if ($amount >= 0) {
                            $loan_percents_summ = 0;
                        }

                        if ($amount > 0) {
                            $loan_body_summ -= $amount;
                        }

                        $this->contracts->update_contract($contract->id, array(
                            'loan_body_summ' => $loan_body_summ,
                            'loan_percents_summ' => $loan_percents_summ,
                            'loan_peni_summ' => $loan_peni_summ,
                            'reccurent_status' => 1,
                            'reccurent_summ' => $amount / 100,
                            'reccurent_attempt' => $contract->reccurent_attempt + 1,
                        ));
                    }
                }


            }

        }


        exit;
    }
 
}

$cron = new ReccurentCron();
$cron->run();
