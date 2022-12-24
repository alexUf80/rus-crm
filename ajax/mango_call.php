<?php

error_reporting(-1);
ini_set('display_errors', 'On');

header("Content-type: application/json; charset=UTF-8");
header("Cache-Control: must-revalidate");
header("Pragma: no-cache");
header("Expires: -1");		

session_start();
chdir('..');

require 'autoload.php';

$core = new Core();

$response = array();

$phone = $core->request->get('phone');
$yuk = $core->request->get('yuk', 'integer');
//$phone = '79276053000';

if(isset($_SESSION['manager_id']))
{
	$manager = $core->managers->get_manager(intval($_SESSION['manager_id']));
}

if (empty($manager))
{
    $response['error'] = 'unknown_manager';
}
else
{
    if (empty($manager->mango_number))
    {
        $response['error'] = 'empty_mango';
    }
    else
    {
        $response['success'] = json_decode($core->mango->call($phone, $manager->mango_number, $yuk));
        
        // cделать добавление в таблицу communications
        
    }
}

echo json_encode($response);


