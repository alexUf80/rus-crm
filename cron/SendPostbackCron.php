<?php
error_reporting(-1);
ini_set('display_errors', 'On');


chdir(dirname(__FILE__) . '/../');

require 'autoload.php';

class SendPostbackCron extends Core
{
    public function __construct()
    {
        parent::__construct();
        $this->run();
    }

    private function run()
    {
        if ($postbacks = $this->leadgens->get_postbacks(['sent_status' => 0]))
        {
            foreach ($postbacks as $postback)
            {
                $this->leadgens->send_postback($postback);
            }
        }
echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($postbacks);echo '</pre><hr />';        
    }
}
$cron = new SendPostbackCron();