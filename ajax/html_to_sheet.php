<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

chdir('..');
require 'autoload.php';

$name = $_GET['name'];
$code = $_GET['code'];

if ($code !== 'f4_67giI33') {
  exit;
}
$core = new Core();

$eventlogs = $core->eventlogs->get_logs(array('order_id' => $name));
$events = $core->eventlogs->get_events();

$managers = array();
foreach ($core->managers->get_managers() as $m) {
  $managers[$m->id] = $m;
}

if ($eventlogs) {
  $html = '';
  foreach ($eventlogs as $eventlog) {
      $event = $events[$eventlog->event_id];
      $event_created = $eventlog->created;
      $manager_name = $managers[$eventlog->manager_id]->name;

      $html = $html . "<tr><td>{$event_created}</td><td>{$event}</td><td>{$manager_name}</td></tr>";
  }
}

echo "<table>{$html}</table>";
