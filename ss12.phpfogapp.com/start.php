<?php

require_once(dirname(__FILE__).'/database.php');
/**
 * Basically its API request and response the json
 *
 **/
class Start
{
   var $location = './images/';
   var $uuid;
   var $image_data;
   var $database;

   function __construct()
   {
      $this->uuid = $_REQUEST['uuid'];
      $this->image_data = $_REQUEST['image'];
   }

   public function process()
   {
      $response = array();

      if ($this->image_data && $this->uuid) {
         $response['status'] = true;
         $this->save_image();
      } else {
         $response['status'] = false;
      }

      echo json_encode($response);
   }

   public function save_image() {
      $fp = fopen($this->location . $this->uuid .'.txt', 'w');
      fwrite($fp, $this->image_data);
      fclose($fp); 
      $this->database = new Database();
      $this->database->save_image($this);
   }

   public function get_image_data() {
      return $this->image_data;
   }
}
