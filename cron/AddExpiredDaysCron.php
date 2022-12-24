<?php
error_reporting(-1);
ini_set('display_errors', 'On');


chdir(dirname(__FILE__) . '/../');

require 'autoload.php';

class AddExpiredDaysCron extends Core
{
    public function run()
    {
        $expiredContracts = ContractsORM::where('status', 4)->get();

        foreach ($expiredContracts as $contract) {
            $countExpiredDays = $contract->count_expired_days++;

            ContractsORM::where('id', $contract->id)->update(['count_expired_days' => $countExpiredDays]);
        }
    }
}

$cron = new AddExpiredDaysCron();
$cron->run();