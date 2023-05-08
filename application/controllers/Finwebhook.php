<?php

header('Access-Control-Allow-Origin: *'); //for allow any domain, insecure
header('Access-Control-Allow-Headers: *'); //for allow any headers, insecure
header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE'); //method allowed
header("HTTP/1.1 200 OK");

defined('BASEPATH') OR exit('No direct script access allowed');
include APPPATH . 'ThirdParty/sendgrid-php/sendgrid-php.php';
include APPPATH . 'ThirdParty/mTalkz.php';
include APPPATH . 'libraries/Femail.php';

class Finwebhook extends CI_Controller 
{

    public function __construct(){
		parent::__construct();
		$this->load->helper('json_output');  
	}



  public function finwebhooktest(){

  $response['status'] = 200;
	$respStatus = $response['status'];
	$method = $_SERVER['REQUEST_METHOD'];
	if($method != 'POST'){
		json_output(400, array('status' => 400,'message' => 'Bad request.')); 
	}
 else
	{ 
		if($response['status'] == 200 ) 
				{
					$params = json_decode(file_get_contents('php://input'), TRUE);
		      $MTalkMobOtp	= new \App\ThirdParty\MTalkz;
          $respon = $MTalkMobOtp->sendmobileotp('9710922263',"borrower",'123456');
                    // json_output(200, array('status' => 200 , 'message'=> $respon));   
                }
            }

    }

}








?>