<?php
require_once(dirname(__FILE__).'/database.php');

/**
 * Show this page to the turk and they will response to the server
 **/
class Page
{
   var $uuid;
   var $image_data;
   var $db;

   function __construct($uuid)
   {
      $this->uuid = $uuid;
      $this->db = new Database();
      $this->image_data = $this->db->get_image_data($this->uuid);
   }

   public function getImageData() {
      return $this->image_data;
   }
}

$page = new Page();
?>

<html>
<title>Page</title>
<head></head>

<body>
<img src="<?php echo $page->getImageData();?>">
<form action="page.php">

</form>
</body>

</html>
