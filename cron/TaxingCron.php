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

        //Начисления
        if ($contracts = $this->contracts->get_contracts(array('status' => [2, 4], 'type' => 'base', 'stop_profit' => 0, 'is_restructed' => 0))) {

            foreach ($contracts as $contract) {

                $this->db->query("
                select sum(amount) as sum_taxing
                from s_operations
                where contract_id = ?
                and `type` in ('PERCENTS', 'PENI')
                ", $contract->id);

                $sum_taxing = $this->db->result();

                $taxing_limit = $contract->amount * 1.5;
                $stop_taxing = 0;



                //Начисление процентов
                $percents_summ = round($contract->loan_body_summ / 100 * $contract->base_percent, 2);

                if($percents_summ > ($taxing_limit - $sum_taxing))
                {
                    $percents_summ = $taxing_limit - $sum_taxing;
                    $stop_taxing = 1;
                }

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
                if ($contract->status == 4 && $stop_taxing == 0) {
                    $peni_summ = round((0.05 / 100) * $contract->loan_body_summ, 2);

                    if($peni_summ > ($taxing_limit - $sum_taxing))
                    {
                        $peni_summ = $taxing_limit - $sum_taxing;
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
        }
    }


}

$cron = new TaxingCron();
