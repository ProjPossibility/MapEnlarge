<?php

// ini_set('display_errors', 'On');
require_once(dirname(__FILE__).'/database.php');

$check = false;

if ($_POST['uuid']) {
   $client = new couchClient('https://daifu:123456@daifu.cloudant.com:443/', 'ss12');
   $newDoc = new stdClass();
   $newDoc->uuid = $_POST['uuid'];
   $newDoc->_id = $_POST['uuid'].'image';
   $newDoc->link = $_POST['link'];
   $newDoc->time = date("Y-m-d H:i:s");
   try {
      $response = $client->storeDoc($newDoc);
      $check = true;
   } catch (Exception $e) {
       echo "ERROR: ".$e->getMessage()." (".$e->getCode().")<br>\n";
   }
}

?>

<html>
<body>
<?php
if ($check) {
?>

THANK YOU! You submit it!

<?php
} else {
?>
Nope!
<?php }?>
</body>
</html>
