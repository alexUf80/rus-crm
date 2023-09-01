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

        $timestamp_group_movings = date('Y-m-d H:i:s');

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

        // убираем по риск-статусу
        $backRiskContracts = ContractsORM::selectRaw('id, (loan_body_summ + loan_percents_summ + loan_charge_summ + loan_peni_summ) as debt,
            collection_status,
            collection_manager_id,
            user_id')
            ->where('status', 4)
            ->where('return_date', '<', date('Y-m-d'))
            ->orderByRaw('debt', 'desc')
            ->get();

        $this->risk_statuses($backRiskContracts);
        

        // Распределение

        $expiredContracts = ContractsORM::selectRaw('id, (loan_body_summ + loan_percents_summ + loan_charge_summ + loan_peni_summ) as debt,
            collection_status,
            collection_manager_id,
            return_date,
            user_id')
            ->where('status', 4)
            ->where('return_date', '<', date('Y-m-d'))
            ->orderByRaw('debt', 'desc')
            ->get();

        $query = $this->db->placehold("
            SELECT * 
            FROM __collectors_move_groups");
        $this->db->query($query);
        $collectors_move_groups = $this->db->results();

        $groups_current_manager_number = [];
        foreach ($collectors_move_groups as $collectors_move_group) {
            if($collectors_move_group->collectors_id != null){
                $groups_current_manager_number[$collectors_move_group->id] = 1;
            }
        }

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

            // $lastCollectorId = array_shift($collectorsMoveId_worked);
            // array_push($collectorsMoveId, $lastCollectorId);
            $current_group_id = $collectorsMove->id;
            $lastCollectorId = $collectorsMoveId_worked[$groups_current_manager_number[$current_group_id] - 1];

            if ($this->is_in_risk($contract, $lastCollectorId)) {
                $lastCollectorId_prew = $lastCollectorId;
                for ($i = 0; $i <= count($collectorsMoveId_worked); $i++) {
                    
                    $lastCollectorId = $collectorsMoveId_worked[$groups_current_manager_number[$current_group_id] - 1];
                    if (!$this->is_in_risk($contract, $lastCollectorId)) {
                        break;
                    }
                    $groups_current_manager_number[$collectorsMove->id]++;
                    if($groups_current_manager_number[$collectorsMove->id] > count($collectorsMoveId_worked)){
                        $groups_current_manager_number[$collectorsMove->id] = 1;
                    }
                }
                if ($lastCollectorId_prew == $lastCollectorId) {
                    continue;
                }
            }


            $groups_current_manager_number[$collectorsMove->id]++;
            if($groups_current_manager_number[$collectorsMove->id] > count($collectorsMoveId_worked)){
                $groups_current_manager_number[$collectorsMove->id] = 1;
            }

            $from_manager = $contract->collection_manager_id;

            ContractsORM::where('id', $contract->id)->update(['collection_status' => $thisPeriod->id, 'collection_manager_id' => $lastCollectorId]);
            // CollectorsMoveGroupORM::where('id', $collectorsMove->id)->update(['collectors_id' => json_encode((object)$collectorsMoveId)]);

            $date1 = new DateTime(date('Y-m-d', strtotime($contract->return_date)));
            $date2 = new DateTime(date('Y-m-d'));
            $diff = $date2->diff($date1)->days;

            $this->collections->add_moving(array(
                'initiator_id' => 0,
                'manager_id' => $lastCollectorId,
                'from_manager_id' => $from_manager,
                'contract_id' => $contract->id,
                'from_date' => date('Y-m-d H:i:s'),
                'summ_body' => $contract->loan_body_summ,
                'summ_percents' => $contract->loan_percents_summ + $contract->loan_peni_summ + $contract->loan_charge_summ,
                'collection_status' => $manager->collection_status_id,
                'timestamp_group_movings' => $timestamp_group_movings,
                'expired_days' => $diff,
            ));
        }
    }

    private function risk_statuses($backRiskContracts)
    {
        foreach ($backRiskContracts as $contract) {
            $user_risk_op = (array)$this->UsersRisksOperations->get_record($contract->user_id);
            $user_risk_op = array_diff_key($user_risk_op, ["id"=>0, "user_id"=>0]);
            $user_risk_op = array_diff($user_risk_op, [0]);
            $user_risk_op = array_keys($user_risk_op);
            
            $added_risk_statuses = (array)$this->ManagerRiskStatuses->get_record($contract->collection_manager_id);
            $added_risk_statuses = array_diff_key($added_risk_statuses, ["id"=>0, "manager_id"=>0, "created"=>0]);
            $added_risk_statuses = array_diff($added_risk_statuses, [0]);
            $added_risk_statuses = array_keys($added_risk_statuses);

            $risk_op = $this->is_in_risk($contract,$contract->collection_manager_id);
            if ($risk_op) {
                ContractsORM::where('id', $contract->id)->update(['collection_status' => 0, 'collection_manager_id' => 0]);
            }
        }
    }

    private function is_in_risk($contract, $manager_id)
    {
        $user_risk_op = (array)$this->UsersRisksOperations->get_record($contract->user_id);
        $user_risk_op = array_diff_key($user_risk_op, ["id"=>0, "user_id"=>0]);
        $user_risk_op = array_diff($user_risk_op, [0]);
        $user_risk_op = array_keys($user_risk_op);
        
        $added_risk_statuses = (array)$this->ManagerRiskStatuses->get_record($manager_id);
        $added_risk_statuses = array_diff_key($added_risk_statuses, ["id"=>0, "manager_id"=>0, "created"=>0]);
        $added_risk_statuses = array_diff($added_risk_statuses, [0]);
        $added_risk_statuses = array_keys($added_risk_statuses);

        $risk_op = array_intersect_assoc($user_risk_op, $added_risk_statuses);
        
        return $risk_op;
    }
}

new DistributiorCollectorsCron();