<?php

namespace App\ThirdParty;
header('Access-Control-Allow-Origin: *'); //for allow any domain, insecure
header('Access-Control-Allow-Headers: *'); //for allow any headers, insecure
header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE'); //method allowed
header("HTTP/1.1 200 OK");

defined('BASEPATH') OR exit('No direct script access allowed');

class Probeapi extends CI_Controller 
{

    private $ci;

    public function __construct(){
		parent::__construct();
		$this->load->helper('json_output');

		// $this->load->library('encryption');
	}
    // function __construct() {

    //     $this->ci = & get_instance();            
    // }
    
    public function probeapi($cin)
    {    
        $probeAPI          = "https://api.probe42.in/probe_pro_sandbox/companies/";
        $probebasedetails     = "/base-details";
        $probeapi_key    = "HeqZByvSwm8PdxEL1drWA2LG9QF84PkaPHeyLvl0";
        $probeversion = "1.3";
        $cin='L74120MH1985PLC035308';

       
        $msgFormat = "json";        
        $url = $probeAPI;
        $fields ="apikey=". $probeapi_key."&senderid=".$cin."&number=".$probebasedetails."&message=". $probeversion."&format=".$msgFormat;
        // echo($url);  
        $result = $this->callprobe($url,$fields);
        return $result;
    }
    private function callprobe($url,$fields)
    {
            try{
                    $urlc=$url;
                    $fieldsc=$fields;
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $urlc);
                        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $fieldsc);
                        $result = curl_exec($ch);
            }catch (\Exception $e) {
                return false;
            }
                //$this->log("url");
                // $this->log($url);
                //$this->log("fields");
                // $this->log($fields);
            curl_close($ch);
            if ($result)
                return $result;
            else
                return false;

    }
    private function log($message) {
            print_r($message);
            echo "\n";
    } //---------------- End of function --------------------

}
