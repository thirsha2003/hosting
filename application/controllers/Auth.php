<?php
header('Access-Control-Allow-Origin: *'); //for allow any domain, insecure
header('Access-Control-Allow-Headers: *'); //for allow any headers, insecure
header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE'); //method allowed
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller 
{

	public function __construct()
	{
        parent::__construct();
		$this->load->helper('json_output','jwt_helper');
	}

	public function login()
	{
		$method = $_SERVER['REQUEST_METHOD'];
		if($method != 'POST'){
			json_output(400,array('status' => 400,'message' => 'Bad request.'));
		} else {
			$check_auth_user = $this->login->check_auth_user();
			if($check_auth_user == true){
					$params = json_decode(file_get_contents('php://input'), TRUE);
		        	$username = $params['username'];
		        	$password = $params['password'];
		           	$response = $this->login->check_login($username,$password);
                    // print_r($token); exit;
				json_output($response['status'],$response);
			}
		}
	}//------------------end of login----------------------------------

	public function logout()
	{	
		$method = $_SERVER['REQUEST_METHOD'];
		if($method != 'POST'){
			json_output(400,array('status' => 400,'message' => 'Bad request.'));
		} else {
			$check_auth_user = $this->login->check_auth_user();
			if($check_auth_user == true){
		        	$response = $this->login->logout();
				json_output($response['status'],$response);
			}
		}
	}//----------------end of logout------------------------------------
	



}//---------------end of class------------------------------------------
