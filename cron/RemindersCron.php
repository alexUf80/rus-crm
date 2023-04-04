<?php
error_reporting(-1);
ini_set('display_errors', 'On');
chdir(dirname(__FILE__) . '/../');

require 'autoload.php';

class RemindersCron extends Core
{
    public function __construct()
    {
        parent::__construct();
        $this->run();
    }

    private function run()
    {
        RegSegment::processing(1);
        ActiveSegment::processing(1);
        PendingSegment::processing(1);
        RepSegment::processing(1);
        ExpireSegment::processing(1);
    }
}

new RemindersCron();