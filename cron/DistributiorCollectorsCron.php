<?php
error_reporting(-1);
ini_set('display_errors', 'On');
chdir(dirname(__FILE__) . '/../');

require 'autoload.php';

class DistributiorCollectorsCron extends Core
{
    public function __construct()
    {
        parent::__construct();

        $this->run();
    }

    private function run()
    {

        $backContracts = ContractsORM::selectRaw('id, (loan_body_summ + loan_percents_summ + loan_charge_summ + loan_peni_summ) as debt,
            order_id,
            collection_status,
            collection_manager_id,
            return_date')
            ->where('status', '<>',4)
            ->where('return_date', '>', date('Y-m-d'))
            ->orderByRaw('debt', 'desc')
            ->get();
        
        foreach ($backContracts as $contract) {
            if ($contract->collection_status != 0) {
                ContractsORM::where('id', $contract->id)->update(['collection_status' => 0]);
            }
        }

        $expiredContracts = ContractsORM::selectRaw('id, (loan_body_summ + loan_percents_summ + loan_charge_summ + loan_peni_summ) as debt,
            collection_status,
            collection_manager_id,
            return_date')
            ->where('status', 4)
            ->where('return_date', '<', date('Y-m-d'))
            ->orderByRaw('debt', 'desc')
            ->get();

        foreach ($expiredContracts as $contract) {

            $returnDate = new DateTime(date('Y-m-d', strtotime($contract->return_date)));
            $now = new DateTime(date('Y-m-d'));

            $dateDiff = date_diff($returnDate, $now)->days;

            $thisPeriod = CollectorPeriodsORM::where('period_from', '<=', $dateDiff)
                ->where('period_to', '>=', $dateDiff)
                ->first();



            if ($contract->collection_status == $thisPeriod->id && !empty($contract->collection_manager_id))
                continue;

            $collectorsMove = CollectorsMoveGroupORM::where('period_id', $thisPeriod->id)->first();

            if (empty($collectorsMove->collectors_id)) {
                ContractsORM::where('id', $contract->id)->update(['collection_status' => $thisPeriod->id]);
                continue;
            }

            $collectorsMoveId = json_decode($collectorsMove->collectors_id, true);
            
            $collectorsMoveId_worked = [];
            foreach ($collectorsMoveId as $collector_id) {
                $manager = ManagerORM::where('id', '=', $collector_id)->first();
                if($manager->blocked == 0){
                    $collectorsMoveId_worked[] = $collector_id;
                }
            }

            $lastCollectorId = array_shift($collectorsMoveId_worked);
            array_push($collectorsMoveId, $lastCollectorId);

            ContractsORM::where('id', $contract->id)->update(['collection_status' => $thisPeriod->id, 'collection_manager_id' => $lastCollectorId]);
            // CollectorsMoveGroupORM::where('id', $collectorsMove->id)->update(['collectors_id' => json_encode((object)$collectorsMoveId)]);
        }
    }
}

new DistributiorCollectorsCron();