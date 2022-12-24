<?php
error_reporting(-1);
ini_set('display_errors', 'On');
ini_set('max_execution_time', '600');

require '../autoload.php';

//exit;

//phpinfo();

$core = new Core();

//$_GET['password'];
if ($_GET['password'] == 'Hjkdf8d') {
    $scoring = $core->scorings->get_scoring($_GET['id']);
    $body = unserialize($scoring->body);

    if (isset($body['outerHTML'])) {
        echo $body['outerHTML'];
    } elseif (isset($body['result'])) {
        echo $body['result'];
    } else {
        echo '';
    }
}

if (isset($_GET['run'])) {
    $scoring = $core->Fssp2_scoring->run_scoring($_GET['id']);
    echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($scoring);echo '</pre><hr />';
}

exit;