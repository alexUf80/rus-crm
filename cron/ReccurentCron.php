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
            
            $attempt = $this->settings->reccurent_pay + 1;

            foreach ($contracts as $contract) {
                
                // if ($contract->order_id != 34111) {
                //     continue;
                // }

                $date1 = new DateTime(date('Y-m-d', strtotime($contract->return_date)));
                $date2 = new DateTime(date('Y-m-d'));
                $diff = $date2->diff($date1);


                var_dump($attempt, $contract->reccurent_status, $diff->days);
                if (($diff->days == 30) && (($attempt - 1) == $contract->reccurent_status)) {
                    var_dump($contract->id, $diff->days, ($contract->loan_percents_summ + $contract->loan_peni_summ));

                    switch ($attempt) {
                        case 1:
                            $amount = 10000;
                            $this->contracts->update_contract($contract->id, array(
                                'reccurent_summ' => $contract->loan_percents_summ,
                            ));
                            break;
                        
                        case 2:
                            $amount = round($contract->reccurent_summ / 100 * 10 * 100, 2);
                            break;
                        
                        case 3:
                            $amount = round($contract->reccurent_summ / 100 * 30 * 100, 2);
                            break;
                        
                        case 4:
                            $amount = round($contract->reccurent_summ / 100 * 40 * 100, 2);
                            break;
                        
                        case 5:
                            $amount = $contract->loan_percents_summ;
                            $this->contracts->update_contract($contract->id, array(
                                'reccurent_summ' => 0,
                            ));
                            break;
                        
                        default:
                            $amount = 0;
                            break;
                    }

                    $order = $this->orders->get_order($contract->order_id);
    
                    $reccurent_pay = $this->best2pay->reccurent_pay($order, $amount, $attempt);
                    if (!$reccurent_pay) {
                        $this->contracts->update_contract($contract->id, array(
                            'reccurent_status' => 0,
                        ));
                    }
                }


            }

            if($attempt == 5){
                $this->settings->reccurent_pay = 0;
            }
            else{
                $this->settings->reccurent_pay = $attempt;
            }
            
        }


        exit;
    }
 
}

$cron = new ReccurentCron();
$cron->run();
