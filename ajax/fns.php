<?php
error_reporting(-1);
ini_set('display_errors', 'On');

header("Content-type: application/json; charset=UTF-8");
header("Cache-Control: must-revalidate");
header("Pragma: no-cache");
header("Expires: -1");	

chdir('..');

require 'autoload.php';

$response = array();
$core = new Core();

if ($user_id = $core->request->get('user_id', 'integer'))
{
    if ($user = $core->users->get_user($user_id))
    {
        $birthday = date('d.m.Y', strtotime($user->birth));
        $passportdate = date('d.m.Y', strtotime($user->passport_date));
        $fns = $core->fns->get_inn($user->lastname, $user->firstname, $user->patronymic, $birthday, 21, $user->passport_serial, $passportdate);

        if (!empty($fns->code))
        {
            $response['success'] = 1;
            $response['inn'] = $fns->inn;

            $scoring = array(
                'user_id' => $user->id,
                'type' => 'fns',
                'body' => $fns->inn,
                'success' => 1,
                'scorista_id' => '',
            );
            $core->scorings->add_scoring($scoring);
        }
        else
        {
            $response['error'] = 'not_found';
            $scoring = array(
                'user_id' => $user->id,
                'type' => 'fns',
                'body' => '',
                'success' => 0,
                'scorista_id' => '',
            );
        }
    }
    else
    {
        $response['error'] = 'undefined_user';
    }
}
else
{
    $response['error'] = 'empty_user_id';
}

echo json_encode($response);    