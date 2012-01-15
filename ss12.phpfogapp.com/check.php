<?php


// ini_set('display_errors', 'On');
require_once(dirname(__FILE__).'/database.php');

if ($_REQUEST['uuid']) {
   $db = new Database();
   $response = array();
   $obj = $db->find_image_by_id($_REQUEST['uuid'].'image');
   // print_r($obj);
   if ($obj) {
      $response['link'] = $obj->link;
      $response['status'] = true;
   } else {
      $response['status'] = false;
   }
   echo json_encode($response);
}
