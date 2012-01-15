<?php

/**
 * A class that save the feed description to the database
 **/

require_once(dirname(__FILE__).'/PHP-on-Couch/lib/couch.php');
require_once(dirname(__FILE__).'/PHP-on-Couch/lib/couchClient.php');
require_once(dirname(__FILE__).'/PHP-on-Couch/lib/couchDocument.php');


class Database
{
    private $client;

    function __construct()
    {
        //connect to the database
       $this->client = new couchClient('https://daifu:123456@daifu.cloudant.com:443/', 'ss12');
       date_default_timezone_set('UTC');
    }

    public function save_image($data)
    {
        try {
            //look for the news_feed whether is existed or not
            $doc = $this->client->getDoc($data->uuid);
            $doc->image_data = $data->image_data;
            $newDoc->time = date("Y-m-d H:i:s");
            // update the document on CouchDB server
            $response = $this->client->storeDoc($doc);
        } catch (Exception $e) {
            // echo "ERROR: ".$e->getMessage()." (".$e->getCode().")<br>\n";
        }
        $newDoc = new stdClass();
        $newDoc->uuid = $data->uuid;
        $newDoc->_id = $data->uuid;
        $newDoc->image_data = $data->image_data;
        $newDoc->time = date("Y-m-d H:i:s");
        try {
           $response = $this->client->storeDoc($newDoc);
        } catch (Exception $e) {
            echo "ERROR: ".$e->getMessage()." (".$e->getCode().")<br>\n";
        }
    }

    public function find_image_by_id($id)
    {
        try {
            $doc = $this->client->getDoc($id);
            return $doc;
        } catch (Exception $e) {
            // echo "ERROR: ".$e->getMessage()." (".$e->getCode().")<br>\n";
            return false;
        }
    }

    public function find_all_image()
    {
       try {
          /* $docs = $this->client->getDoc('_all_docs'); */
          $result = $this->client->getView('all','by_date');
          return $result->rows;
       } catch (Exception $e) {
            // echo "ERROR: ".$e->getMessage()." (".$e->getCode().")<br>\n";
            return false;
        }
    }
}

