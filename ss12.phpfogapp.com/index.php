<?php

// ini_set('display_errors', 'On');
require_once(dirname(__FILE__).'/start.php');

// var_dump($_POST);

// $client = new couchClient('https://daifu:123456@daifu.cloudant.com:443/', 'ss12');
// $newDoc = new stdClass();
// $newDoc->uuid = "uuiddebug";
// // $newDoc->_id = "id";
// $newDoc->image = print_r($_POST, true);
// $newDoc->date = new DateTime;
// $client->storeDoc($newDoc);
if (isset($_REQUEST['uuid']) && isset($_REQUEST['image'])) {
   $start = new Start();
   $start->process();
} else {
   echo "{'status': false}";
}
?>

