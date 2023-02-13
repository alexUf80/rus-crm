<?php
error_reporting(-1);
ini_set('display_errors', 'On');
chdir(dirname(__FILE__) . '/../');

require 'autoload.php';

class GetTimeZoneCron extends Core
{
    protected $token = "222e191767518127bcf15cc4d2a23c131404fdf2";
    protected $secret = "6b90de07e9974eba848ac174b3eed2829a35ec5e";

    public function __construct()
    {
        parent::__construct();
        $this->run();
    }

    private function run()
    {
        $users = UsersORM::whereNull('time_zone')->get();

        if (!empty($users)) {
            foreach ($users as $user) {
                $regaddress = $this->Addresses->get_address($user->regaddress_id);

                if (!empty($regaddress)) {
                    $dadata = new \Dadata\DadataClient($this->token, $this->secret);
                    $result = $dadata->clean("address", $regaddress->adressfull);
                    UsersORM::where('id', $user->id)->update(['time_zone' => $result['timezone']]);
                }
            }
        }
    }
}

new GetTimeZoneCron();