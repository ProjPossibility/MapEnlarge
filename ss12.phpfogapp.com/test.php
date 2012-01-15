<?php


ini_set('display_errors', 'On');
require_once(dirname(__FILE__).'/database.php');
$client = new couchClient('https://daifu:123456@daifu.cloudant.com:443/', 'ss12');

// $view_fn="function(doc) { emit(doc.date.date,doc); }";
// $design_doc->_id = '_design/all';
// $design_doc->language = 'javascript';
// $design_doc->views = array ( 'by_date'=> array ('map' => $view_fn ) );
// $client->storeDoc($design_doc);


$result = $client->getView('all','by_date');

echo "<pre>".print_r($result, true)."</pre>";
// try {
//    $all_docs = $client->getAllDocs();
//    foreach ($all_docs->rows as $row ) {
//       // echo "Document ".$row."<BR>\n";
//       print_r($row);
//    }   
//    // var_dump($all_docs);
// } catch (Exception $e) {
//      echo "ERROR: ".$e->getMessage()." (".$e->getCode().")<br>\n";
//      return false;
//  }
// $newDoc = new stdClass();
// $newDoc->uuid = "uuiddebug";
// $newDoc->_id = "id";
// $newDoc->image_data = print_r($_POST, true);
// $newDoc->date = new DateTime;
// $client->storeDoc($newDoc);

