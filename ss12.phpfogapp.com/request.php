<?php


// ini_set('display_errors', 'On');
require_once(dirname(__FILE__).'/mturk.lib.php');
require_once(dirname(__FILE__).'/database.php');

/**
 * Get the UUid and process request to Amazon Mturk library
 **/
class Request
{
   var $UUID;
   var $db;
   var $mt;
   var $AccessKey = 'AKIAJFWB7EXJNWGHLSTA';
   var $SecretKey = '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';
   
   function __construct(){
      // $this->UUID = $uuid;
      $this->db = new Database();
      // $this->mt = new mTurkInterface($this->AccessKey, $this->SecretKey); /* Create interface */
   }

   public function sendRequest()
   {
      $this->mt->SetOperation("mTurkOperation"); /* Set operation */
      $this->mt->Status     = "Reviewable"; /* Reviewable HITs only */
      $this->mt->PageSize   = 10;
      $this->mt->PageNumber = 1;
      $this->mt->Invoke(); /* Attack! */
   }

   public function showImageLink()
   {
      // $this->db->find_image_by_id($this->uuid);
   }

   public function getLatestImageLink() {
      $db_array = $this->db->find_all_image();
      return $db_array;
   }

}

$request = new Request();
// echo $request->sendRequest();
$imagelinks = $request->getLatestImageLink();
$latestImage = $imagelinks[count($imagelinks) - 1];
// $imageValue = $latestImage->value;
// echo "<pre>".print_r($imagelinks, true)."</pre>";
// echo "<pre>".print_r($imageValue, true)."</pre>";


// $mt = new mTurkInterface('AKIAJFWB7EXJNWGHLSTA', '3WNeSLRP8yEu6meJPv5ISd3N8IIR08jHQj6PqULv'); /* Create interface */
// $mt->SetOperation("CreateHIT"); /* Set operation */
// $mt->Status     = "Reviewable"; /* Reviewable HITs only */
// $mt->Timestamp = 
// // $mt->PageSize   = 10;
// // $mt->PageNumber = 1;
// $mt->Invoke(); /* Attack! */
//
?>

<html>
<title>Request</title>
<head></head>
<body>
<h3>Instructions:</h3>
<p>Look at the map picture and use street names to find the location on Google Maps. Submit.</p>

<img src="<?php echo $latestImage->value->image_data;?>" width="500px" height="500px">

<form action="response.php" method="post">
<input type="hidden" name="uuid" value="<?php echo $latestImage->value->uuid;?>"/>
LINK: <input type="text" name="link" />
<input type="submit" value="Submit" />
</form>
</body>
</html>
