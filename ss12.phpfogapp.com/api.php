<?php

/**
 * Basically its API request and response the json
 *
 **/
class Start
{
   public $location = './images/';
   public $uuid;
   public $image_data;

   public function porcess()
   {
      $this->uuid = $_GET['uuid'];
      $this->image_data = $_GET['image'];

      $response = array();

      if ($this->image_data && $this->uuid) {
         $response['status'] = true;
         $this->save_image();
      } else {
         $response['status'] = false;
         $response['error'] = "No keywords";
      }

      echo json_encode($response);
   }

   public function save_image() {
      $fp = fopen($this->location . $this->uuid .'txt', 'w');
      fwrite($fp, $this->image_data);
      fclose($fp); 
   }

   public function get_image_data() {
      return $this->image_data;
   }
}


