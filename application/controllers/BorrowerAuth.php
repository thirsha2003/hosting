<?php
header('Access-Control-Allow-Origin: *'); //for allow any domain, insecure
header('Access-Control-Allow-Headers: *'); //for allow any headers, insecure
header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE'); //method allowed
header("HTTP/1.1 200 OK");

defined('BASEPATH') OR exit('No direct script access allowed');
include APPPATH . 'ThirdParty/sendgrid-php/sendgrid-php.php';

class BorrowerAuth extends CI_Controller {

	public function __construct(){
		parent::__construct();
		$this->load->helper('json_output');
		// $this->load->helper('json_output');
		$this->ci =& get_instance();
		$this->ci->load->database();
	}


	public function check_token(){
		$check_auth_user  = $this->login->check_auth_user();
		if($check_auth_user){
		$token   = $this->ci->input->get_request_header('token', TRUE);
		$user_id   = $this->ci->input->get_request_header('user-id', TRUE);
		$checkuser = array('id' => $user_id, 'token' => $token );
		$this->db->where($checkuser);
		$count = $this->db->count_all_results("fpa_users");
		if($count ==  1){
			return true;
		}else{
			return false;
		}
		}else{
			return false;
		}	
	}

	public function gettoken(){
		$method = $_SERVER['REQUEST_METHOD'];
				if($method != 'POST'){
					json_output(400,array('status' => 400,'message' => 'Bad request.'));
				}else{
					$checkToken = $this->check_token();
					// $checkToken = true;
					if($checkToken){
					$response['status']=200;
					$respStatus = $response['status'];
					$params = json_decode(file_get_contents('php://input'), TRUE);
					

						$auth_token = $this->finusercheck();
						// echo $auth_token;


					$resp = array('status' => 200,'message' =>  'Success','token' => $auth_token);
					json_output($respStatus,$resp);
				}else{
					json_output(400,array('status' => 400,'message' => 'Bad request.'));
				}
				}
			
	}

	public function createtoken($fpa_user_id = null){
			$check_auth_user  = $this->login->check_auth_user();
			if($check_auth_user){
				if($fpa_user_id != null ){
					$query = $this->db->get_where('fpa_users',array('id' => $fpa_user_id));
				}else{
					json_output(200,array('status' => 404,'message' => 'Field missing'));
				}
				if($query->num_rows() >= 1){
					foreach ($query->result() as $row)
					{		
					$txnArr[] = array(
						'email' => $row->email,
						'name' =>  $row->name,
						'id' =>  $row->id,
						'slug' =>  $row->slug,
						'now'=> date('Y-m-d H:i:s'),
						'random_key' => bin2hex(random_bytes(11))
					);
					$userid = $row->id;
					}
					$token = $this->jwttoken->token($txnArr);
					$this->db->where('id', $userid);
					$this->db->update('fpa_users',array('token'=>$token, 'token_time'=>date('Y-m-d H:i:s')));

					$resp = array('status' => 200,'message' =>  'Success','data' => $token, 'key' => $txnArr);
					json_output($respStatus,$resp);
				}else{
					json_output(200,array('status' => 404,'message' => 'Field missing'));
				}
			}else{
				json_output(400,array('status' => 404,'message' => 'Failed Auth'));
			}
			
		
	}

	public function finusercheck(){
		$check_auth_user  = $this->login->check_auth_user();
		if($check_auth_user){
			$token = $this->ci->input->get_request_header('token', TRUE);
			if($token){
				$curr = date('Y-m-d H:i:s');
				$last_min = date('Y-m-d H:i:s', strtotime('-20 minutes'));
				// echo $token;
				$this->db->select();
				$this->db->from('fpa_users');
				$this->db->where('token', $token);
				$this->db->where('token_time >=', $last_min);
				$this->db->where('token_time <=', $curr);
				$result = $this->db->get();

				if($result->num_rows() == 1){
					foreach($result->result() as $row){
						$txnArr[] = array(
							'email' => $row->email,
							'name' =>  $row->name,
							'id' =>  $row->id,
							'slug' =>  $row->slug,
							'now'=> date('Y-m-d H:i:s'),
							'random_key' => bin2hex(random_bytes(11))
						);
						$userid = $row->id;
						}
						$token = $this->jwttoken->token($txnArr);
						$this->db->where('id', $userid);
						$this->db->update('fpa_users',array('token'=>$token, 'token_time'=>date('Y-m-d H:i:s')));
	
						return $token;
				}else{
					return false;
				}
		
			}else{
				return false;
			}
		}else{
			return false;
		}
				
	
	}
}
