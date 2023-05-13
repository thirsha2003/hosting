<?php
header('Access-Control-Allow-Origin: *'); //for allow any domain, insecure
header('Access-Control-Allow-Headers: *'); //for allow any headers, insecure
header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE'); //method allowed
header("HTTP/1.1 200 OK");

defined('BASEPATH') OR exit('No direct script access allowed');
include APPPATH . 'ThirdParty/sendgrid-php/sendgrid-php.php';

class Borrowerauth extends CI_Controller {

	public function __construct(){
		parent::__construct();
		$this->load->helper('json_output');
		// $this->load->helper('json_output');
		$this->ci =& get_instance();
		$this->ci->load->database();
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
					json_output(200,$resp);
				}else{
					json_output(200,array('status' => 404,'message' => 'Field missing'));
				}
			}else{
				json_output(400,array('status' => 404,'message' => 'Failed Auth'));
			}
			
		
	}

	public function finusercheck(){
		$method = $_SERVER['REQUEST_METHOD'];
		if($method != 'POST'){
			json_output(400,array('status' => 400,'message' => 'Bad request.'));
		}else{

	
		$check_auth_user  = $this->login->check_auth_user();
		if($check_auth_user){
			$token = $this->ci->input->get_request_header('fintoken', TRUE);
			if($token){
				$curr = date('Y-m-d H:i:s');
				$last_min = date('Y-m-d H:i:s', strtotime('-30 minutes'));  // push to live  that time  uncommoad this line 
				// $last_min = date('Y-m-d H:i:s', strtotime('-2 minutes'));
				// echo $token;
				$this->db->select();
				$this->db->from('fpa_users');
				$this->db->where('token', $token);
				// $this->db->where('token_time >=', $last_min);
				// $this->db->where('token_time <=', $curr);
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
	
						
						json_output(200,array('status' => 200,'token' => $token));
				}else{
					json_output(200,array('status' => 400,'message' => 'Bad request.'));
				}
		
			}else{
				json_output(200,array('status' => 400,'message' => 'Bad request.'));
			}
		}else{
			json_output(200,array('status' => 400,'message' => 'Bad request.'));
		}
				
	}
	}


	// XLRT Token


	public function xlrtcreatetoken(){
		// $check_auth_user  = $this->login->check_auth_user();
		if(true){
			if(false){
				// $query = $this->db->get_where('fpa_users',array('id' => $fpa_user_id));
			}else{
				// json_output(200,array('status' => 404,'message' => 'Field missing'));
			}
			$query = 1;
			if($query == 1){
				// foreach ($query->result() as $row)
				// {		
				$txnArr[] = array(
					'email' => "rahul@finnup.in",
					'name' => "finnup",
					'now'=> date('Y-m-d H:i:s'),
					'random_key' => bin2hex(random_bytes(11))
				);
				// $userid = $row->id;
				// }
				$token = $this->jwttoken->token($txnArr);
				echo $token;
				exit();
				// $this->db->where('id', $userid);
				// $this->db->update('fpa_users',array('token'=>$token, 'token_time'=>date('Y-m-d H:i:s')));

				$resp = array('status' => 200,'message' =>  'Success','data' => $token, 'key' => $txnArr);
				json_output(200,$resp);
			}else{
				json_output(200,array('status' => 404,'message' => 'Field missing'));
			}
		}else{
			json_output(400,array('status' => 404,'message' => 'Failed Auth'));
		}
		
	
}
}
