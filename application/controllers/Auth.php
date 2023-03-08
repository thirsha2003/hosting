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
		$this->ci =& get_instance();
		$this->ci->load->database();
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

	public function check_token(){
		$check_auth_user  = $this->login->check_auth_user();
		if($check_auth_user){
		$token   = $this->ci->input->get_request_header('token', TRUE);
		$user_id   = $this->ci->input->get_request_header('user-id', TRUE);
		$checkuser = array('id' => $user_id, 'token' => $token );
		$this->db->where($checkuser);
		$count = $this->db->count_all_results("fpa_adminusers");
		if($count ==  1){
			return true;
		}else{
			return false;
		}
		}else{
			return false;
		}	
	}
	public function adminauthcheck(){
		$method = $_SERVER['REQUEST_METHOD'];
		if($method != 'POST'){
		json_output(400,array('status' => 400,'message' => 'Bad request.'));
		}else{
			$checkToken = $this->check_token();
			if($checkToken){
			$response['status']=200;
			$respStatus = $response['status'];
			$params = json_decode(file_get_contents('php://input'), TRUE);
			// $selectkey = isset($params['selectkey']) ? $params['selectkey'] : "*"; 
			// $join = isset($params['key']) ? $params['key'] : "";
			// $where = isset($params['where']) ? $params['where'] : "";	
			// $sql = "SELECT " .$selectkey. " FROM ".$params['tableName']."  WHERE ".$where;
			$resp = array('status' => 200,'message' =>  'Success');
			json_output($respStatus,$resp);
		}else{
			json_output(200,array('status' => 400,'message' => 'Bad request.'));
		}
		}
	}
	



}//---------------end of class------------------------------------------
