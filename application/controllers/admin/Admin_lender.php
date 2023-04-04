<?php

header('Access-Control-Allow-Origin: *'); //for allow any domain, insecure
header('Access-Control-Allow-Headers: *'); //for allow any headers, insecure
header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE'); //method allowed
header("HTTP/1.1 200 OK");
header("hello: hellooo");

defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH . 'ThirdParty/sendgrid-php/sendgrid-php.php';
include APPPATH . 'ThirdParty/mTalkz.php';
include APPPATH . 'libraries/Femail.php';

// include ('./config.php');
class Admin_lender extends CI_Controller 
{
			public function __construct(){
				parent::__construct();
				$this->load->helper('json_output');		
			
					$this->ci =& get_instance();
					$this->ci->load->database();
				
			}  // construct 
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
			} // check_token 

			 
			public function update_initiated(){
				$method = $_SERVER['REQUEST_METHOD'];
				if($method != 'POST'){
				json_output(400,array('status' => 400,'message' => 'Bad request.'));
				}else{
					$checkToken = $this->check_token();
					if(TRUE){
					$response['status']=200;
					$respStatus = $response['status'];
					$params = json_decode(file_get_contents('php://input'), TRUE);
					$loan_app_id = isset($params['loan_app_id']) ? $params['loan_app_id'] : ""; 

					if($loan_app_id != ""){

					$this->db->where('id', $loan_app_id);
					$this->db->update('fpa_loan_applications',array("workflow_status" => "Discussion Initiated","loanapplication_status" => "Discussion Initiated")); 
					$this->db->insert('fpa_loan_applications_worklog', array("loanapplication_id"=>$loan_app_id,"activity"=>"Discussion Initiated"));
					
					$resp = array('status' => 200,'message' =>  'Success','data' => "Status Changed");
					json_output($respStatus,$resp);

					}else{
						json_output(400,array('status' => 400,'message' => 'Some field missing'));
					}
						
					
				}else{
					json_output(400,array('status' => 400,'message' => 'Bad request.'));
				}
				}
			} // get_details

		}
