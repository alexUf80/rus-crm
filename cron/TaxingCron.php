<?php
error_reporting(-1);
ini_set('display_errors', 'On');
ini_set('max_execution_time', 12000);


//chdir('/home/v/vse4etkoy2/nalic_eva-p_ru/public_html/');
chdir(dirname(__FILE__) . '/../');

require 'autoload.php';

/**
 * IssuanceCron
 *
 * Скрипт производит начисление процентов, просрочек, пеней
 *
 * @author Ruslan Kopyl
 * @copyright 2021
 * @version $Id$
 * @access public
 */
class TaxingCron extends Core
{
    public function __construct()
    {
        parent::__construct();
        $this->run();
    }

    private function run()
    {
        //Перевод в просрочку всех у кого подошел срок
        $this->contracts->check_expiration_contracts();

        //автоотказ на одобренные заявки через 14 дней.
        $this->orders->check_overdue_orders();

        //Начисления
        if ($contracts = $this->contracts->get_contracts(array('status' => [2, 4], 'type' => 'base', 'stop_profit' => 0, 'is_restructed' => 0))) {
            foreach ($contracts as $contract) {
                // // !!!!!!!
                // if ($contract->order_id != 34583) {
                //     continue;
                // }
                
                $filter = [];
                $filter['from'] = date('Y-m-d H:i:s');
                $filter['to'] = date('Y-m-d H:i:s');
                $filter['order_id'] = $contract->order_id;
                $count_canicules = $this->canicules->count_canicules($filter);
                if ($count_canicules) {
                    $canicule = $this->canicules->get_canicules($filter)[0];
                    $canicule_type = $canicule->type;
                    $canicule_from = $canicule->from_date;

                    // менять в OrderController
                    $kk_base_percent = 190.059;
                    if ($canicule_from > '2024-02-15') {
                        $kk_base_percent = 190.839;
                    }
                }
                
                $this->db->query("
                select sum(amount) as sum_taxing
                from s_operations
                where contract_id = ?
                and (`type` = 'PERCENTS'
                or `type` = 'PENI')
                ", $contract->id);

                $sum_taxing = $this->db->result();

                $max_loan_value = 1.3;
                $diff_to_new_max  = intval((strtotime(date('Y-m-d', strtotime($contract->inssuance_date))) - strtotime(date('Y-m-d', strtotime('2023-07-01')))) / 86400);
                if ($diff_to_new_max < 0) {
                    $max_loan_value = 1.5;
                }

                $taxing_limit = $contract->amount * $max_loan_value;
                $stop_taxing = 0;


                //Начисление процентов
                $percents_summ = round($contract->loan_body_summ / 100 * $contract->base_percent, 2);
                
                // если каникулы 
                if(!is_null($contract->canicule)){
                    $percents_summ = round($contract->loan_body_summ / 100 * $contract->base_percent * 2 / 3, 2);
                    if ($contract->order_id == 71209 || $contract->order_id == 71064) {
                        $percents_summ = 0;
                    }
                }
                if($count_canicules > 0){
                    if (isset($canicule_type) && $canicule_type == 'svo') {
 
                        $percents_summ = round($contract->loan_body_summ / 100 * $kk_base_percent / 365, 2);
                    }
                }

                if ($percents_summ > ($taxing_limit - $sum_taxing->sum_taxing)) {
                    $percents_summ = $taxing_limit - $sum_taxing->sum_taxing;
                    $stop_taxing = 1;
                }

                if ($stop_taxing == 0 && $percents_summ > 0) {
                    $this->operations->add_operation(array(
                        'contract_id' => $contract->id,
                        'user_id' => $contract->user_id,
                        'order_id' => $contract->order_id,
                        'type' => 'PERCENTS',
                        'amount' => $percents_summ,
                        'created' => date('Y-m-d H:i:s'),
                        'loan_body_summ' => $contract->loan_body_summ,
                        'loan_percents_summ' => $contract->loan_percents_summ + $percents_summ,
                        'loan_charge_summ' => $contract->loan_charge_summ,
                        'loan_peni_summ' => $contract->loan_peni_summ,
                    ));
    
                    //Начисление пени, если просрочен займ
                    if ($contract->status == 4 && $stop_taxing == 0 && is_null($contract->canicule) && $count_canicules == 0) {
                        $diff_days = date_diff(
                            new DateTime(date('Y-m-d', strtotime($contract->inssuance_date))),
                            new DateTime(date('Y-m-d', strtotime($contract->return_date)))
                        );
                        $cals_percents_summ = round($percents_summ * $diff_days->days, 2);
                        $peni_summ = round(($contract->loan_body_summ + $cals_percents_summ) * 0.2 / 365, 2);
                        if ($peni_summ > ($taxing_limit - $sum_taxing->sum_taxing)) {
                            $peni_summ = $taxing_limit - $sum_taxing->sum_taxing;
                            $stop_taxing = 1;
                        }
    
                        $this->contracts->update_contract($contract->id, array(
                            'loan_peni_summ' => $contract->loan_peni_summ + $peni_summ
                        ));
    
    
                        $this->operations->add_operation(array(
                            'contract_id' => $contract->id,
                            'user_id' => $contract->user_id,
                            'order_id' => $contract->order_id,
                            'type' => 'PENI',
                            'amount' => $peni_summ,
                            'created' => date('Y-m-d H:i:s'),
                            'loan_body_summ' => $contract->loan_body_summ,
                            'loan_percents_summ' => $contract->loan_percents_summ,
                            'loan_charge_summ' => $contract->loan_charge_summ,
                            'loan_peni_summ' => $contract->loan_peni_summ + $peni_summ,
                        ));
                    }
                    $this->contracts->update_contract($contract->id, array(
                        'loan_percents_summ' => $contract->loan_percents_summ + $percents_summ,
                        'stop_profit' => $stop_taxing
                    ));
                }

                $this->contracts->update_contract($contract->id, array(
                    'stop_profit' => $stop_taxing
                ));
            }
        }
    }


}

$cron = new TaxingCron();
