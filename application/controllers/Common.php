<?php
header('Access-Control-Allow-Origin: *'); //for allow any domain, insecure
header('Access-Control-Allow-Headers: *'); //for allow any headers, insecure
header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE'); //method allowed
header("HTTP/1.1 200 OK");

defined('BASEPATH') OR exit('No direct script access allowed');
include APPPATH . 'ThirdParty/sendgrid-php/sendgrid-php.php';


class Common extends CI_Controller {

	public function __construct(){  
		parent::__construct();
		$this->load->helper('json_output');

	}
	public function borrowerloanrequest(){
		$method = $_SERVER['REQUEST_METHOD'];
		if($method != 'POST'){
		  json_output(400,array('status' => 400,'message' => 'Bad request.'));
		}else{
		  $response['status']=200;
		  $respStatus = $response['status'];
		  
			$params = json_decode(file_get_contents('php://input'), TRUE);
			$params['user_id_fk'] = $this->input->get_request_header('User-ID', TRUE);
			
			$selectkey = isset($params['selectkey']) ? $params['selectkey'] : "*"; 
			$join = isset($params['key']) ? $params['key'] : "";
			$where = isset($params['where']) ? $params['where'] : "";
			  
			$sql = "SELECT " .$selectkey. " FROM ".$params['tableName']."  WHERE ".$where;
			
			 // if($params['tableName']=="eventdetails" || $params['tableName']=="sp_contacts"){
			//     $where = isset($params['where']) ? $params['where'] : "1=1";
			//     $sql = "SELECT " .$selectkey. " FROM ".$params['tableName']." ".$join." WHERE ".$where;
			// }else{
			//     $sql = "SELECT " .$selectkey. " FROM ".$params['tableName']." ".$join." WHERE 1=1";
			// }
			$resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql)->result());
			json_output($respStatus,$resp);
		}
		}	
	public function withoutlogincheck(){
		$method = $_SERVER['REQUEST_METHOD'];
		if($method != 'POST'){
			json_output(400,array('status' => 400,'message' => 'Bad request.'));
		}else{
			// $check_auth_user = $this->login->check_auth_user();
			// if($check_auth_user == true){
	        	// $response = $this->login->auth();
				$response['status'] = 200;
	        	// $respStatus = $response['status'];
	        	$respStatus = $response['status'];
	        	if($response['status'] == 200){
					
                    $params = json_decode(file_get_contents('php://input'), TRUE);
					
                    $params['user_id_fk'] = $this->input->get_request_header('User-ID', TRUE);
					if($params['key'] != '' ){
			// echo APPPATH; 
			// echo "hai";
			// die;
						$sql = "SELECT * FROM ".$params['tableName']." WHERE email_id='".$params['data']['name']."' or phone_no='".$params['data']['name']."'";
						$count = $this->db->query($sql)->num_rows();
						if($count>0){
							$num_str = sprintf("%06d", mt_rand(1, 999999));	
		// $response = null;
								$subject ="Finnup OTP";
								$message = "Your otp is : " .$num_str;
								$to = $params['data']['name'];
								$email = new \SendGrid\Mail\Mail();
								$email->setSubject($subject);
								$email->addContent("text/html", $message);
								$email->setFrom('platform@finnup.in', 'FinnUp Team');
					
								// $email->addBcc('saravanan@thesquaircle.com');
								// $email->addBcc('sheik@thesquaircle.com');
								// $email->addBcc('dhanasekarancse08@gmail.com');

								$email->addTo($to);							

								$sendgrid = new \SendGrid("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");
								try {
									$response = $sendgrid->send($email);
								} catch (Exception $e) {
									echo 'Caught exception: ',  $e->getMessage(), "\n";
								}
								$alldata = $this->db->query($sql)->row();
								$insert_array = array();
								$insert_array['user_id'] = $alldata->id;
								$insert_array['otp'] = $num_str;
								$this->db->insert("otp_check", $insert_array);
								
							$resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql)->row());
						}else{
							$resp = array('status' => 201,'message' =>  'Success','data' => $this->db->query($sql)->row());
						}
						
						json_output($respStatus,$resp);
					}
				
				}
			// }
		}
	}
	// checking for post man 
	public function check()
	{
		$method = $_SERVER['REQUEST_METHOD'];
			if($method != 'POST')
			{
				json_output(400,array('status' => 400,'message' => 'Bad request.'));
			}else{
				// $check_auth_user = $this->login->check_auth_user();
				// if($check_auth_user == true){
		        	// $response = $this->login->auth();
					$response['status'] = 200;
		        	$respStatus = $response['status'];
					$resp = array('status' => 200,'message' =>  'Success','data' =>"Sara");
					json_output($respStatus,$resp);

				}
	}

	// end of function for postman 
	// Method to send an email to connector about lender proposal request
	// public function L2C(){

	// 	$method = $_SERVER['REQUEST_METHOD'];
	// 	if($method != 'POST'){
	// 		json_output(400,array('status' => 400,'message' => 'Bad request.'));
	// 	}else{
		
	// 							$subject ="Lender Request Awaiting!";
	// 							$message = "Hi Admin, There is a new  Lender Request Arrived, Pls check and contact";
	// 							// $to="jagan.vijay.104@gmail.com";
	// 							$email = new \SendGrid\Mail\Mail();
	// 							$email->setSubject($subject);
	// 							$email->addContent("text/html", $message);
	// 							$email->setFrom('platform@finnup.in', 'FinnUp Team');
	// 							$email->addBcc('saravanan@thesquaircle.com');
	// 							$email->addBcc('sheik@thesquaircle.com');
	// 							$email->addTo($to);		
	// 							$resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql)->row());		
	// 	}			
	// }

    public function withoutlogincreate(){
		$method = $_SERVER['REQUEST_METHOD'];
		if($method != 'POST'){
			json_output(400,array('status' => 400,'message' => 'Bad request.'));
		}else{
			$check_auth_user = $this->login->check_auth_user();
			// if($check_auth_user == true){
	        	// $response = $this->login->auth();
				$response['status'] = 200;
	        	$respStatus = $response['status'];
	        	if($response['status'] == 200){
                    $params = json_decode(file_get_contents('php://input'), TRUE);
                    $params['user_id_fk'] = $this->input->get_request_header('User-ID', TRUE);
					if ($params['tableName'] == "") {
						$respStatus = 400;
						$resp = array('status' => 400,'message' =>  'Fields Missing');
					} else {
						// $params['data']['user_id_fk'] = (int)$params['user_id_fk'];
						$this->db->insert($params['tableName'], $params['data']);
						$b_id = $this->db->insert_id();
						$sql = "SELECT * FROM ".$params['tableName']." WHERE id='".$this->db->insert_id()."'";
						
						$count = $this->db->query($sql)->num_rows();
						if($count>0){
							$num_str = sprintf("%06d", mt_rand(1, 999999));	
		// $response = null;
								$subject ="Finnup OTP";
								$message = "Your otp is : " .$num_str;
								$to = $params['data']['email_id'];
								$email = new \SendGrid\Mail\Mail();

								$email->setSubject($subject);
								$email->addContent("text/html", $message);
								$email->setFrom('platform@finnup.in', 'FinnUp Team');
							
								// $email->addBcc('saravanan@thesquaircle.com');
								// $email->addBcc('sheik@thesquaircle.com');
								// $email->addBcc('dhanasekarancse08@gmail.com');

								$email->addTo($to);							

								$sendgrid = new \SendGrid("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");
								try {
									$response = $sendgrid->send($email);
								} catch (Exception $e) {
									echo 'Caught exception: ',  $e->getMessage(), "\n";
								}
								$alldata = $this->db->query($sql)->row();
								$insert_array = array();
								$insert_array['user_id'] = $b_id;
								$insert_array['otp'] = $num_str;
								$this->db->insert("otp_check", $insert_array);
						}
						$resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql)->row());

						// $resp = array('status' => 200,'message' =>  'Inserted success','data' => $this->db->insert_id() );
					}
					json_output($respStatus,$resp);
		        }
			// }
		}
	}

	public function otpcheck(){
		$method = $_SERVER['REQUEST_METHOD'];
		if($method != 'POST'){
			json_output(400,array('status' => 400,'message' => 'Bad request.'));
		}else{
			// $check_auth_user = $this->login->check_auth_user();
			// if($check_auth_user == true){
	        	// $response = $this->login->auth();
				$response['status'] = 200;
	        	// $respStatus = $response['status'];
	        	$respStatus = $response['status'];
	        	if($response['status'] == 200){
					
                    $params = json_decode(file_get_contents('php://input'), TRUE);
					
                    $params['user_id_fk'] = $this->input->get_request_header('User-ID', TRUE);
					if($params['key'] != '' ){
						
					$sql = "SELECT * FROM ".$params['tableName']." WHERE ".$params['key'];
						$count = $this->db->query($sql)->num_rows();
						$count=1; //testing otp 
						if($count>0){								
							$resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql)->row());
						}else{
							$resp = array('status' => 201,'message' =>  'Success','data' => $this->db->query($sql)->row());
						}
						//
						json_output($respStatus,$resp);
					}
				
				}
			// }
		}
	}

	// public function commonsave(){
	// 	$method = $_SERVER['REQUEST_METHOD'];
	// 	if($method != 'POST'){
	// 		json_output(400,array('status' => 400,'message' => 'Bad request.'));
	// 	}else{
	// 		// $check_auth_user = $this->login->check_auth_user();
	// 		// if($check_auth_user == true){
	//         	// $response = $this->login->auth();
	// 			$response['status'] = 200;
	//         	$respStatus = $response['status'];
	//         	if($response['status'] == 200){
    //                 $params = json_decode(file_get_contents('php://input'), TRUE);
    //                 $params['user_id_fk'] = $this->input->get_request_header('User-ID', TRUE);

	// 				if ($params['tableName'] == "") {
	// 					$respStatus = 400;
	// 					$resp = array('status' => 400,'message' =>  'Fields Missing');
	// 				} else {
	// 					if($params['tableName']=='proceedtoproposal_details'){
	// 						$sql = "SELECT * FROM borrower_profile WHERE borrower_id=".$params['data']['borrower_id'];
	// 						$borr_dt  = $this->db->query($sql)->result();
	// 	// $response = null;
	// 							$subject ="Finnup App Proposal Alert! : Admin Action Required";
	// 							$message = "Hello Finnup Admin! <br/><br/>".
	// 							"Proposal Raised By :" .$borr_dt[0]->name."<br/>Company Name: " .$borr_dt[0]->company_name.
	// 							"<br/>Contact Email : " .$borr_dt[0]->email_id."<br/>Lender Name: " .$params['data']['lender_name'].
	// 							"<br/>Loan Amount : " .$params['data']['loan_upto'].
	// 							"<br/>Rate of Interest : " .$params['data']['min_roi'].
	// 							"<br/>Maximum Tenor: : " .$params['data']['max_tenor'].
	// 							"<br/><br/>"."Regards, <br/> Team Finnup";
								
	// 							//$to = 'platform@finnup.in';
	// 							// $to = 'jagan.vijay.104@gmail.com';
	// 							$to = 'rec2004@gmail.com';
							
	// 							$email = new \SendGrid\Mail\Mail();

	// 							$email->setSubject($subject);
	// 							$email->addContent("text/html", $message);
	// 							// $email->setFrom($borr_dt[0]->email_id, 'FinnUp Team');
	// 							$email->setFrom("platform@finnup.in", 'FinnUp Team');
	// 							// $email->addBcc('saravanan@thesquaircle.com');
	// 							// $email->addBcc('sheik@thesquaircle.com');
							
								
					
	// 							// $email->addBcc($borr_dt[0]->email_id);

	// 							$email->addTo($to);							

	// 							$sendgrid = new \SendGrid("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");
	// 							try {
	// 								$response = $sendgrid->send($email);
	// 							} catch (Exception $e) {
	// 								echo 'Caught exception: ',  $e->getMessage(), "\n";
	// 							}
	// 					}
	// 					$this->db->insert($params['tableName'], $params['data']);









	// 					$resp = array('status' => 200,'message' =>  'Inserted success','data' => $this->db->insert_id() );
	// 				}
	// 				json_output($respStatus,$resp);
	// 	        }
	// 		// }
	// 	}
	// }

	public function borrowerprofile(){
		$method = $_SERVER['REQUEST_METHOD'];
		if($method != 'POST'){
			json_output(400,array('status' => 400,'message' => 'Bad request.'));
		}else{
			// $check_auth_user = $this->login->check_auth_user();
			// if($check_auth_user == true){
	        	// $response = $this->login->auth();
				$response['status'] = 200;
	        	$respStatus = $response['status'];
	        	if($response['status'] == 200){
                    $params = json_decode(file_get_contents('php://input'), TRUE);
                    $params['user_id_fk'] = $this->input->get_request_header('User-ID', TRUE);

					if ($params['tableName'] == "") {
						$respStatus = 400;
						$resp = array('status' => 400,'message' =>  'Fields Missing');
					} else {
						// $params['data']['user_id_fk'] = (int)$params['user_id_fk'];
						$sql = "SELECT * FROM ".$params['tableName']." WHERE borrower_id=".$params['data']['borrower_id'];
                             
                                if(count($this->db->query($sql)->result())==0){
									$this->db->insert($params['tableName'], $params['data']);
                                }else{
									$this->db->where('borrower_id',$params['data']['borrower_id'] );
									$this->db->update($params['tableName'], $params['data']); 
                                }
						$resp = array('status' => 200,'message' =>  'Inserted success','data' => $this->db->insert_id());
					}
					json_output($respStatus,$resp);
		        }
			// }
		}
	}

	public function getborrowerprofile(){
		$method = $_SERVER['REQUEST_METHOD'];
		if($method != 'POST'){
			json_output(400,array('status' => 400,'message' => 'Bad request.'));
		}else{
			// $check_auth_user = $this->login->check_auth_user();
			// if($check_auth_user == true){
	        	// $response = $this->login->auth();
				$response['status'] = 200;
	        	$respStatus = $response['status'];
	        	if($response['status'] == 200){
                    $params = json_decode(file_get_contents('php://input'), TRUE);
                    $params['user_id_fk'] = $this->input->get_request_header('User-ID', TRUE);

					if ($params['tableName'] == "") {
						$respStatus = 400;
						$resp = array('status' => 400,'message' =>  'Fields Missing');
					} else {
						// $params['data']['user_id_fk'] = (int)$params['user_id_fk'];
						$sql = "SELECT * FROM ".$params['tableName']." WHERE user_id=".$params['data']['id'];
						if(count($this->db->query($sql)->result())==0){
							$resp = array('status' => 201,'message' =>  'no data','data' => $this->db->query($sql)->result() );
						}else{
							$resp = array('status' => 200,'message' =>  'success','data' => $this->db->query($sql)->result() );
						}
						
					}
					json_output($respStatus,$resp);
		        }
			// }
		}
	}

	public function getborrowerprofile_id(){
		$method = $_SERVER['REQUEST_METHOD'];
		if($method != 'POST'){
			json_output(400,array('status' => 400,'message' => 'Bad request.'));
		}else{
			// $check_auth_user = $this->login->check_auth_user();
			// if($check_auth_user == true){
	        	// $response = $this->login->auth();
				$response['status'] = 200;
	        	$respStatus = $response['status'];
	        	if($response['status'] == 200){
                    $params = json_decode(file_get_contents('php://input'), TRUE);
                    $params['user_id_fk'] = $this->input->get_request_header('User-ID', TRUE);

					if ($params['tableName'] == "") {
						$respStatus = 400;
						$resp = array('status' => 400,'message' =>  'Fields Missing');
					} else {
						// $params['data']['user_id_fk'] = (int)$params['user_id_fk'];
						$sql = "SELECT * FROM ".$params['tableName']." WHERE borrower_id=".$params['data']['borrower_id'];
						if(count($this->db->query($sql)->result())==0){
							$resp = array('status' => 201,'message' =>  'no data','data' => $this->db->query($sql)->result() );
						}else{
							$resp = array('status' => 200,'message' =>  'success','data' => $this->db->query($sql)->result() );
						}
						
					}
					json_output($respStatus,$resp);
		        }
			// }
		}
	}


	public function requestproposal(){
		$method = $_SERVER['REQUEST_METHOD'];
		if($method != 'POST'){
			json_output(400,array('status' => 400,'message' => 'Bad request.'));
		}else{
			// $check_auth_user = $this->login->check_auth_user();
			// if($check_auth_user == true){
	        	// $response = $this->login->auth();
				$response['status'] = 200;
	        	$respStatus = $response['status'];
	        	if($response['status'] == 200){
                    $params = json_decode(file_get_contents('php://input'), TRUE);
                    $params['user_id_fk'] = $this->input->get_request_header('User-ID', TRUE);

					if ($params['tableName'] == "") {
						$respStatus = 400;
						$resp = array('status' => 400,'message' =>  'Fields Missing');
					} else {
						// $params['data']['user_id_fk'] = (int)$params['user_id_fk'];
						$sql = "SELECT * FROM ".$params['tableName']." WHERE id=".$params['data']['id'];
						if(count($this->db->query($sql)->result())==0){
							$resp = array('status' => 201,'message' =>  'no data','data' => $this->db->query($sql)->result() );
						}else{
							$resp = array('status' => 200,'message' =>  'success','data' => $this->db->query($sql)->result() );
						}
					}
					json_output($respStatus,$resp);
		        }
			// }
		}
	}

	public function create(){
		$method = $_SERVER['REQUEST_METHOD'];
		if($method != 'POST'){
			json_output(400,array('status' => 400,'message' => 'Bad request.'));
		}else{
			$check_auth_user = $this->login->check_auth_user();
			if($check_auth_user == true){
	        	$response = $this->login->auth();
	        	$respStatus = $response['status'];
	        	if($response['status'] == 200){
                    $params = json_decode(file_get_contents('php://input'), TRUE);
                    $params['user_id_fk'] = $this->input->get_request_header('User-ID', TRUE);

					if ($params['tableName'] == "" || $params['data']['status'] == "") {
						$respStatus = 400;
						$resp = array('status' => 400,'message' =>  'Fields Missing');
					} else {
						$params['data']['user_id_fk'] = (int)$params['user_id_fk'];
						$params['data']['token'] =md5(uniqid(rand(), true));
						$this->db->insert($params['tableName'], $params['data']);                
						$resp = array('status' => 200,'message' =>  'Inserted success','data' => $this->db->insert_id() );
					}
					json_output($respStatus,$resp);
		        }
			}
		}
	}


	public function getlist(){
		$method = $_SERVER['REQUEST_METHOD'];
		if($method != 'POST'){
			json_output(400,array('status' => 400,'message' => 'Bad request.'));
		}else{
			$check_auth_user = $this->login->check_auth_user();
            // $check_auth_user=true;
			if($check_auth_user == true){
	        	$response = $this->login->auth();
                // $response['status']=200;
	        	$respStatus = $response['status'];
	        	if($response['status'] == 200){
                    $params = json_decode(file_get_contents('php://input'), TRUE);
                    $params['user_id_fk'] = $this->input->get_request_header('User-ID', TRUE);
					
					$selectkey = isset($params['selectkey']) ? $params['selectkey'] : "*"; 
					$join = isset($params['key']) ? $params['key'] : "";
                        if($params['tableName']=="eventdetails" || $params['tableName']=="sp_contacts"){
                            $where = isset($params['where']) ? $params['where'] : "1=1";
                            $sql = "SELECT " .$selectkey. " FROM ".$params['tableName']." ".$join." WHERE ".$where;
                        }else{
                            $sql = "SELECT " .$selectkey. " FROM ".$params['tableName']." ".$join." WHERE 1=1";
                        }
					
					$resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql)->result());
					json_output($respStatus,$resp);

				}
			}
		}
	}

	public function withoutlogingetlist(){
		$method = $_SERVER['REQUEST_METHOD'];
		if($method != 'POST'){
			json_output(400,array('status' => 400,'message' => 'Bad request.'));
		}else{
			$response['status']=200;
			$respStatus = $response['status'];
                    $params = json_decode(file_get_contents('php://input'), TRUE);
					
                    $params['user_id_fk'] = $this->input->get_request_header('User-ID', TRUE);
					
					$selectkey = isset($params['selectkey']) ? $params['selectkey'] : "*"; 
					$join = isset($params['key']) ? $params['key'] : "";
					if($params['tableName']=='lender_master'  ){
						$where = isset($params['where']) ? $params['where'] : "1=1";
						$sql = "SELECT " .$selectkey. " FROM ".$params['tableName']." ".$join." WHERE ".$where."";
					}else{
						$where = isset($params['where']) ? $params['where'] : "1=1";
						$sql = "SELECT " .$selectkey. " FROM ".$params['tableName']." ".$join." WHERE ".$where."";
					}
                        // if($params['tableName']=="eventdetails" || $params['tableName']=="sp_contacts"){
                        //     $where = isset($params['where']) ? $params['where'] : "1=1";
                        //     $sql = "SELECT " .$selectkey. " FROM ".$params['tableName']." ".$join." WHERE ".$where;
                        // }else{
                        //     $sql = "SELECT " .$selectkey. " FROM ".$params['tableName']." ".$join." WHERE 1=1";
                        // }
					$resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql)->result());
					json_output($respStatus,$resp);
		}
	}


	public function getcities()
{
  $method = $_SERVER['REQUEST_METHOD'];
  if($method == 'POST')
  {
    
    $response['status']=200;
    $respStatus = $response['status'];
    $params = json_decode(file_get_contents('php://input'), TRUE);
      
        $selectkey   = isset($params['selectkey']) ? $params['selectkey'] : "*"; 
        $join     = isset($params['key']) ? $params['key'] : "";
        $where = isset($params['where']) ? $params['where'] : "";
    
          // $sql = "SELECT * FROM fp_district where is_active =1 ORDER BY id DESC";
          $sql = "SELECT * 
          FROM fp_city
          WHERE is_active = 1
          ORDER BY id in (4,5,6,7,8,9,10,11,12,13,14,15) DESC ,name ASC";
        
        
        $resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql)->result());
        json_output($respStatus,$resp);
    
  }
  else
  {
         json_output(400,array('status' => 400,'message' => 'Bad request.'));
  }
}

	public function borrower_profile(){
		$method = $_SERVER['REQUEST_METHOD'];
		if($method != 'POST'){
			json_output(400,array('status' => 400,'message' => 'Bad request.'));
		}else{
			$response['status']=200;
			$respStatus = $response['status'];		
                    $params = json_decode(file_get_contents('php://input'), TRUE);
                    $params['user_id_fk'] = $this->input->get_request_header('User-ID', TRUE);
					
					$selectkey = isset($params['selectkey']) ? $params['selectkey'] : "*"; 
					$join = isset($params['key']) ? $params['key'] : "";
					if($params['tableName']=='borrower_profile'){
						$where = isset($params['where']) ? $params['where'] : "";
						$sql = "SELECT " .$selectkey. " FROM ".$params['tableName']." ".$join." WHERE ".$where."";
					}else{
						$sql = "SELECT " .$selectkey. " FROM ".$params['tableName']." ".$join." WHERE 1=1";
					}
                        // if($params['tableName']=="eventdetails" || $params['tableName']=="sp_contacts"){
                        //     $where = isset($params['where']) ? $params['where'] : "1=1";
                        //     $sql = "SELECT " .$selectkey. " FROM ".$params['tableName']." ".$join." WHERE ".$where;
                        // }else{
                        //     $sql = "SELECT " .$selectkey. " FROM ".$params['tableName']." ".$join." WHERE 1=1";
                        // }
					$resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql)->result());
					json_output($respStatus,$resp);
		}
	}

    public function getlistdatatable(){
		$method = $_SERVER['REQUEST_METHOD'];
		if($method != 'POST'){
			json_output(400,array('status' => 400,'message' => 'Bad request.'));
		}else{
			// $check_auth_user = $this->login->check_auth_user();
            $check_auth_user=true;
			if($check_auth_user == true){
	        	// $response = $this->login->auth();
                $response['status']=200;
	        	$respStatus = $response['status'];
	        	if($response['status'] == 200){
                    $params = json_decode(file_get_contents('php://input'), TRUE);
                    $params['user_id_fk'] = $this->input->get_request_header('User-ID', TRUE);
					$selectkey = isset($params['selectkey']) ? $params['selectkey'] : "*"; 
					$join = isset($params['key']) ? $params['key'] : "";
                    $limitqry = "LIMIT ".$params['start'].",".$params['length'];
					$sql = "SELECT " .$selectkey. " FROM ".$params['tableName']." ".$join." WHERE ".$params['tableName'].".status!='1' ".$limitqry; 
                    $countget = "SELECT " .$selectkey. " FROM ".$params['tableName']." WHERE ".$params['tableName'].".status!='1' ";
                    $count = $this->db->query($countget)->num_rows();
					$resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql)->result(),'recordsTotal'=>$count,'recordsFiltered'=>$count);
					json_output($respStatus,$resp);
				}
			}
		}
	}

	public function view(){
		$method = $_SERVER['REQUEST_METHOD'];
		if($method != 'POST'){
			json_output(400,array('status' => 400,'message' => 'Bad request.'));
		}else{
			$check_auth_user = $this->login->check_auth_user();
			if($check_auth_user == true){
	        	$response = $this->login->auth();
	        	$respStatus = $response['status'];
	        	if($response['status'] == 200){
					
                    $params = json_decode(file_get_contents('php://input'), TRUE);
                    $params['user_id_fk'] = $this->input->get_request_header('User-ID', TRUE);
					if($params['key'] != '' ){
						$sql = "SELECT * FROM ".$params['tableName']." WHERE ".$params['key'];
						$resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql)->row());
						json_output($respStatus,$resp);
					}
				
				}
			}
		}
	}

    public function viewall(){
		$method = $_SERVER['REQUEST_METHOD'];
		if($method != 'POST'){
			json_output(400,array('status' => 400,'message' => 'Bad request.'));
		}else{
			$check_auth_user = $this->login->check_auth_user();
			if($check_auth_user == true){
	        	$response = $this->login->auth();
	        	$respStatus = $response['status'];
	        	if($response['status'] == 200){
                    $params = json_decode(file_get_contents('php://input'), TRUE);
                    $params['user_id_fk'] = $this->input->get_request_header('User-ID', TRUE);
					if($params['key'] != '' ){
						$sql = "SELECT * FROM ".$params['tableName']." WHERE ".$params['key'];
						$resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql)->result());
						json_output($respStatus,$resp);
					}
				}
			}
		}
	}
	public function update(){
		$method = $_SERVER['REQUEST_METHOD'];
		if($method != 'POST'){
			json_output(400,array('status' => 400,'message' => 'Bad request.'));
		}else{
			$check_auth_user = $this->login->check_auth_user();
			if($check_auth_user == true){
	        	$response = $this->login->auth();
	        	$respStatus = $response['status'];
	        	if($response['status'] == 200){
                    $params = json_decode(file_get_contents('php://input'), TRUE);
                    $params['data']['user_id_fk'] = $this->input->get_request_header('User-ID', TRUE);
					if($params['tableName'] || $params['key'] && $params['data'] != '' ){
						
						$this->db->where($params['key']);
						$this->db->update($params['tableName'], $params['data']); 
                        
						$resp = array('status' => 200,'message' =>  'Success','data' => 'updated');
						json_output($respStatus,$resp);
					}
				
				}
			}
		}
	}

	public function delete(){
		$method = $_SERVER['REQUEST_METHOD'];
		if($method != 'POST'){
			json_output(400,array('status' => 400,'message' => 'Bad request.'));
		}else{
			$check_auth_user = $this->login->check_auth_user();
			if($check_auth_user == true){
	        	$response = $this->login->auth();
	        	$respStatus = $response['status'];
	        	if($response['status'] == 200){
					
                    $params = json_decode(file_get_contents('php://input'), TRUE);
                    $params['user_id_fk'] = $this->input->get_request_header('User-ID', TRUE);
					if($params['tableName'] || $params['key'] && $params['key'] != '' ){
						
						$data = ['status'=>'1'];
						
						$this->db->where($params['key']);
						$this->db->where('user_id_fk',$params['user_id_fk'] );
						$this->db->update($params['tableName'], $data); 
						$resp = array('status' => 200,'message' =>  'Success','data' => 'updated');
						json_output($respStatus,$resp);
					}
				
				}
			}
		}
	}


	public function emailtoconnector(){
		$method = $_SERVER['REQUEST_METHOD'];
		if($method != 'POST'){
		  json_output(400,array('status' => 400,'message' => 'Bad request.'));
		}else{
		  // $check_auth_user = $this->login->check_auth_user();
		  // if($check_auth_user == true){
			  //   $response = $this->login->auth();
			  //   $respStatus = $response['status'];
			  //   if($response['status'] == 200)
		  //   {
			$response['status'] = 200;
				// $respStatus = $response['status'];
				$respStatus = $response['status'];
			$subject ="Lender Request Awaiting!";
					$message = "Hi Admin, There is a new  Lender Request Arrived, Pls check and contact";
					$to='rec2004@gmail.com';
					$email = new \SendGrid\Mail\Mail();
					$email->setSubject($subject);
					$email->addContent("text/html", $message);
					$email->setFrom('platform@finnup.in', 'FinnUp Team');
					// $email->addBcc('saravanan@thesquaircle.com');
					// $email->addBcc('sheik@thesquaircle.com');
					$email->addTo($to);    
			$sendgrid = new \SendGrid("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");
					try {
					  $response = $sendgrid->send($email);
					} catch (Exception $e) {
					  echo 'Caught exception: ',  $e->getMessage(), "\n";
					}
					$resp = array('status' => 200,'message' =>  'Success','data' => 'An email sent to Connector, He will reach you shortly');    
			json_output($respStatus,$resp);
			// }
			
			// }
		  }
		}


	public function fp_fin_institution(){
			$method = $_SERVER['REQUEST_METHOD'];
			if($method != 'POST'){
				json_output(400,array('status' => 400,'message' => 'Bad request.'));
			}else{
				$response['status']=200;
				$respStatus = $response['status'];
						$params = json_decode(file_get_contents('php://input'), TRUE);
						$params['user_id_fk'] = $this->input->get_request_header('User-ID', TRUE);
						
						$selectkey = isset($params['selectkey']) ? $params['selectkey'] : "*"; 
						$join = isset($params['key']) ? $params['key'] : "";
						if($params['tableName']=='lender_details' || $params['tableName']=='loan_product_amount' ||  $params['tableName']=='proceedtoproposal_details' ||$params['tableName']=='lender_request' ){
							$where = isset($params['where']) ? $params['where'] : "";
							$sql = "SELECT " .$selectkey. " FROM ".$params['tableName']." ".$join." WHERE ".$where."";
						}else{
							$sql = "SELECT " .$selectkey. " FROM ".$params['tableName']." ".$join." WHERE 1=1";
						}
							// if($params['tableName']=="eventdetails" || $params['tableName']=="sp_contacts"){
							//     $where = isset($params['where']) ? $params['where'] : "1=1";
							//     $sql = "SELECT " .$selectkey. " FROM ".$params['tableName']." ".$join." WHERE ".$where;
							// }else{
							//     $sql = "SELECT " .$selectkey. " FROM ".$params['tableName']." ".$join." WHERE 1=1";
							// }
						$resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql)->result());
						json_output($respStatus,$resp);
			}
		}

	public function withoutlogingetlistwhere(){
			$method = $_SERVER['REQUEST_METHOD'];
			if($method != 'POST'){
				json_output(400,array('status' => 400,'message' => 'Bad request.'));
			}else{
				$response['status']=200;
				$respStatus = $response['status'];
				
						$params = json_decode(file_get_contents('php://input'), TRUE);
						$params['user_id_fk'] = $this->input->get_request_header('User-ID', TRUE);
						
						$selectkey = isset($params['selectkey']) ? $params['selectkey'] : "*"; 
						$join = isset($params['key']) ? $params['key'] : "";
						$where = isset($params['where']) ? $params['where'] : "";
									
						$sql = "SELECT " .$selectkey. " FROM ".$params['tableName']."  WHERE ".$where;
						
							
						
							// if($params['tableName']=="eventdetails" || $params['tableName']=="sp_contacts"){
							//     $where = isset($params['where']) ? $params['where'] : "1=1";
							//     $sql = "SELECT " .$selectkey. " FROM ".$params['tableName']." ".$join." WHERE ".$where;
							// }else{
							//     $sql = "SELECT " .$selectkey. " FROM ".$params['tableName']." ".$join." WHERE 1=1";
							// }
						$resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql)->row());
						json_output($respStatus,$resp);
			}
		}

	public function location(){
			$method = $_SERVER['REQUEST_METHOD'];
			if($method != 'POST'){
				json_output(400,array('status' => 400,'message' => 'Bad request.'));
			}else{
				$response['status']=200;
				$respStatus = $response['status'];
						$params = json_decode(file_get_contents('php://input'), TRUE);
						$params['user_id_fk'] = $this->input->get_request_header('User-ID', TRUE);
						
						$selectkey = isset($params['selectkey']) ? $params['selectkey'] : "*"; 
						$join = isset($params['key']) ? $params['key'] : "";
						if($params['tableName']=='fp_state' ||$params['tableName']=='fp_city' ||$params['tableName']=='fp_location' ||$params['tableName']=='fp_district' ){
							$where = isset($params['where']) ? $params['where'] : "";
							$sql = "SELECT " .$selectkey. " FROM ".$params['tableName']." ".$join." WHERE ".$where;
						}else{
							$sql = "SELECT " .$selectkey. " FROM ".$params['tableName']." ".$join." where 1=1";
						}
						$resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql)->result());
						json_output($respStatus,$resp);
			}
		}


	public function lender_search(){
			$method = $_SERVER['REQUEST_METHOD'];
			if($method != 'POST'){
				json_output(400,array('status' => 400,'message' => 'Bad request.'));
			}else{
				$response['status']=200;
				$respStatus = $response['status'];
						$params = json_decode(file_get_contents('php://input'), TRUE);
						$params['user_id_fk'] = $this->input->get_request_header('User-ID', TRUE);
						
						$selectkey = isset($params['selectkey']) ? $params['selectkey'] : "*"; 
						$join = isset($params['key']) ? $params['key'] : "";
						if($params['tableName']!='fp_lender_master'){
							$where = isset($params['where']) ? $params['where'] : "";
							$sql = "SELECT " .$selectkey. " FROM ".$params['tableName']." ".$join." WHERE ".$where;
						}else{
							$sql = "SELECT " .$selectkey. " FROM ".$params['tableName']." ".$join." where 1=1";
						}
							// if($params['tableName']=="eventdetails" || $params['tableName']=="sp_contacts"){
							//     $where = isset($params['where']) ? $params['where'] : "1=1";
							//     $sql = "SELECT " .$selectkey. " FROM ".$params['tableName']." ".$join." WHERE ".$where;
							// }else{
							//     $sql = "SELECT " .$selectkey. " FROM ".$params['tableName']." ".$join." WHERE 1=1";
							// }
						$resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql)->result());
						json_output($respStatus,$resp);
			}
		}
	
		public function borrowerprofile1(){
			$method = $_SERVER['REQUEST_METHOD'];
			if($method != 'POST'){
				json_output(400,array('status' => 400,'message' => 'Bad request.'));
			}else{
				// $check_auth_user = $this->login->check_auth_user();
				// if($check_auth_user == true){
					// $response = $this->login->auth();
					$response['status'] = 200;
					$respStatus = $response['status'];
					if($response['status'] == 200){
						$params = json_decode(file_get_contents('php://input'), TRUE);
						// $params['user_id_fk'] = $this->input->get_request_header('User-ID', TRUE);
	
						if ($params['tableName'] == "") {
							$respStatus = 400;
							$resp = array('status' => 400,'message' =>  'Fields Missing');
						} else {
							// $params['data']['user_id_fk'] = (int)$params['user_id_fk'];
							$sql = "SELECT * FROM ".$params['tableName']." WHERE user_id=".$params['data']['user_id'];
							$name=$params['data']['company_name'];
									if(count($this->db->query($sql)->result())==0){
										$this->db->insert($params['tableName'], $params['data']);
									}else{
										$this->db->where('user_id',$params['data']['user_id'] );
										$this->db->update($params['tableName'], $params['data']); 
									}
							$resp = array('status' => 200,'message' =>  'Inserted success','data' => $this->db->insert_id());


							// Email Notification
							$subject = "Dear Admin,";
							$message = "Dear Admin,"."<br/>"."<br/>"."Few changes have been done to the borrower ".$name.". 
							"."<br/>".
						   "Please visit the link to view the profile and changes"."<br/>".
						   "link : app.finnup.in/#/admin.";
			 
							$to = 'platform@finnup.in';
						   
						
						   
							$email = new \SendGrid\Mail\Mail();
							$email->setSubject($subject);
							$email->addContent("text/html", $message);
							$email->setFrom("platform@finnup.in", 'FinnUp Team');
							$email->addTo($to);
							$sendgrid = new \SendGrid("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");
							try {
								$response = $sendgrid->send($email);
							} catch (Exception $e) {
								echo 'Caught exception: ', $e->getMessage(), "\n";
							}







						}
						json_output($respStatus,$resp);
					}
				// }
			}
		}
	

//----------------------------------------------------------------------------------
public function myproposals(){
	$method = $_SERVER['REQUEST_METHOD'];
	if($method != 'POST'){
	  json_output(400,array('status' => 400,'message' => 'Bad request.'));
	}else{
		

		
	  $response['status']=200;
	  $respStatus = $response['status'];
	  
		  $params = json_decode(file_get_contents('php://input'), TRUE);
		
		  
		  $selectkey = isset($params['selectkey']) ? $params['selectkey'] : "*"; 
		  $join = isset($params['key']) ? $params['key'] : "";
		  $where = isset($params['where']) ? $params['where'] : "";
				
		  $sql = "SELECT " .$selectkey. " FROM ".$params['tableName']."  WHERE ".$where;
		    
		  $resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql)->result());
		  json_output($respStatus,$resp);
	
}
  }



  public function allupdate()
  {
			$method = $_SERVER['REQUEST_METHOD'];
			if($method != 'POST')
			{
				json_output(400,array('status' => 400,'message' => 'Bad request.'));
			}else
			{
				
					$response['status'] = 200;
					$respStatus = $response['status'];
					if($response['status'] == 200)
					{
						$params = json_decode(file_get_contents('php://input'), TRUE);
						if ($params['tableName'] == "") 
						{
							$respStatus = 400;
							$resp = array('status' => 400,'message' =>  'Fields Missing');
						} else 
						{
							$d_id = isset($params['data']['id']) ? $params['data']['id'] : "0";
							$sql = "SELECT * FROM ".$params['tableName']." WHERE id =".$d_id;
								
									if(count($this->db->query($sql)->result())==0){
										$this->db->insert($params['tableName'], $params['data']);
									}else{
										$this->db->where('id',$params['data']['id'] );
										$this->db->update($params['tableName'], $params['data']); 
									}
							$resp = array('status' => 200,'message' =>  'success','data' => $this->db->insert_id());
						}
						json_output($respStatus,$resp);
					}
				// }
			}
  }


  public function borrower_profiles(){
	$method = $_SERVER['REQUEST_METHOD'];
	if($method != 'POST'){
		json_output(400,array('status' => 400,'message' => 'Bad request.'));
	}else{
		$response['status']=200;
		$respStatus = $response['status'];		
				$params = json_decode(file_get_contents('php://input'), TRUE);
				$params['user_id_fk'] = $this->input->get_request_header('User-ID', TRUE);
				
				$selectkey = isset($params['selectkey']) ? $params['selectkey'] : "*"; 
				$join = isset($params['key']) ? $params['key'] : "";
				$where = isset($params['where']) ? $params['where'] : "";
				if($params['tableName']=='borrower_profile'){
					$where = isset($params['where']) ? $params['where'] : "";
					$sql = "SELECT " .$selectkey. " FROM ".$params['tableName']." ".$join." WHERE ".$where."";
				}else{
					$this->db->where('borrower_id',$params['data']['borrower_id']);
					$this->db->order_by('id','desc');
					$this->db->limit(1);
					$this->db->update($params['tableName'], $params['data']); 
					// $sql = "SELECT " .$selectkey. " FROM ".$params['tableName']." ".$join." WHERE ".$where."";
				}
					// if($params['tableName']=="eventdetails" || $params['tableName']=="sp_contacts"){
					//     $where = isset($params['where']) ? $params['where'] : "1=1";
					//     $sql = "SELECT " .$selectkey. " FROM ".$params['tableName']." ".$join." WHERE ".$where;
					// }else{
					//     $sql = "SELECT " .$selectkey. " FROM ".$params['tableName']." ".$join." WHERE 1=1";
					// }
				$resp = array('status' => 200,'message' =>  'Success');
				// query($sql)->
				json_output($respStatus,$resp);
	}
}

  public function deletefiles()
  {
			$method = $_SERVER['REQUEST_METHOD'];
			if($method != 'POST')
			{
				json_output(400,array('status' => 400,'message' => 'Bad request.'));
			}else
			{
					$response['status'] = 200;
					$respStatus = $response['status'];
					if($response['status'] == 200)
					{
						$params = json_decode(file_get_contents('php://input'), TRUE);
						if ($params['tableName'] == "") 
						{
							$respStatus = 400;
							$resp = array('status' => 400,'message' =>  'Fields Missing');
						} else 
					 {
					  $d_id = isset($params['data']) ? $params['data'] : "0";
					  $where = isset($params['where']) ? $params['where'] : "1=1";
						$this->db->where($params['col_name'], $where);
						if($this->db->delete($params['tableName'])){
						  // ---- start ---- 
					  $path_to_file = './uploads/'.$where;
				  
						  if(unlink($path_to_file)) {
						   $resp='DELETED SUCCESSFULLY';
							  }
							  else {
								 $resp= 'errors occured';
							  }
						  //  ---end --- 
						}
						}
						json_output($respStatus,$resp);
					}
				
			}









  }

  	//---------------Changes for email functionality 021222-------------------------------


	  public function bookacall()
	  {
				$method = $_SERVER['REQUEST_METHOD'];
				if($method != 'POST')
				{
					json_output(400,array('status' => 400,'message' => 'Bad request.'));
				}
				else
				{
					//$check_auth_user = $this->login->check_auth_user();
					//if($check_auth_user == true){
					//$response = $this->login->auth();
					$response['status'] = 200;
					$respStatus = $response['status'];
					if($response['status'] == 200)
					{
							$params = json_decode(file_get_contents('php://input'), TRUE);
							if ($params['tableName'] == "") 
							{
									$respStatus = 400;
									$resp = array('status' => 400,'message' =>  'Fields Missing');
							} 
							else 
							{
								$b_id='';
								$p_id='';
								$b_id = isset($params['data']['borrower_id']) ? $params['data']['borrower_id'] : 0;
								$p_id = isset($params['data']['product_id']) ? $params['data']['product_id'] : 0;
								//step0
								$sql = "SELECT * FROM  fp_book_a_call WHERE borrower_id =".$b_id ." AND product_id =".$p_id;
								
								if(count($this->db->query($sql)->result())==0)
								{
									//step1
									$this->db->insert($params['tableName'], $params['data']);
									$bookacall_id= $this->db->insert_id();
									
									//step2
									$sql ="
									SELECT 
									B.ID, B.EMAIL, B.MOBILE, U.NAME, P.NAME AS LOAN, L.AMOUNT
									FROM 
									FP_BOOK_A_CALL B, 
									FP_LOANAMOUNT L, 
									FP_BORROWER_USER_DETAILS U, 
									FP_PRODUCTS P
									WHERE
									B.PRODUCT_ID =P.ID AND
									B.BORROWER_ID =U.USER_ID AND
									B.LOANAMOUNT_ID =L.ID AND
									B.ID =".$bookacall_id;	

									$bookacalldata = $this->db->query($sql)->row();
									
									//step3
									$subject ="Finnup App Book a call Alert! : Admin Action Required";
									$message = "Hello Finnup Admin! <br/><br/>". "There is a loan request from a borrower. Please refer the details below <br/><br/>".
									"Borrower Name :" .$bookacalldata->NAME."<br/>".
									"Contact Email :" .$bookacalldata->EMAIL."<br/>".
									"Product Requested : " .$bookacalldata->LOAN."<br/>".
									"Amount Requested : " .$bookacalldata->AMOUNT."<br/>".	
									"-----------------------------------------------<br/>
									Team Finnup";

									//$to = 'platform@finnup.in';
									$to = 'rec2004@gmail.com';
									$email = new \SendGrid\Mail\Mail();
									$email->setSubject($subject);
									$email->addContent("text/html", $message);
									$email->setFrom("platform@finnup.in", 'FinnUp Team');
									$email->addTo($to);							
									$sendgrid = new \SendGrid("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");
									try {
										$response = $sendgrid->send($email);
									} catch (Exception $e) {
										echo 'Caught exception: ',  $e->getMessage(), "\n";
									}
									$resp = array('status' => 200,'message' =>  'Inserted success','data' => "" );
									
								}else
								{
									$respStatus = 201;
									$resp = array('status' => 201,'message' =>  'Already a request present for this product, our admin team will contact you shortly! ');
								}	
							
						}
						json_output($respStatus,$resp);
					}
			// }
				}
	  }//-------------end of function bookacall--------------------------------

	

	  public function commonsave()
	  {
			$method = $_SERVER['REQUEST_METHOD'];
			if($method != 'POST')
			{
				json_output(400,array('status' => 400,'message' => 'Bad request.'));
			}
			else
			{
				// $check_auth_user = $this->login->check_auth_user();
				// if($check_auth_user == true){
				// $response = $this->login->auth();
				$response['status'] = 200;
	        	$respStatus = $response['status'];
	        	if($response['status'] == 200)
				{
					$params = json_decode(file_get_contents('php://input'), TRUE);
                	if ($params['tableName'] == "") 
					{
						$respStatus = 400;
						$resp = array('status' => 400,'message' =>  'Fields Missing');
					} 
					else 
					{
						$borrowerid="";
						$lenderid="";
						$product="";

						if($params['tableName']=='fp_borrower_loanrequests')
						{								
							$borrowerid=isset($params['data']['borrower_id']) ? $params['data']['borrower_id'] : 0;
							$product=isset($params['data']['product_slug']) ? $params['data']['product_slug'] : "";
							$sql ="select name from fp_products where slug="."'".$product."'";
							$productname = $this->db->query($sql)->row();
							$sql ="select name, email,mobile from fpa_users where id=".$borrowerid;
							$userdata= $this->db->query($sql)->row();
							try{

								$this->db->insert($params['tableName'], $params['data']);
								$subject ="Finnup App Loan Request Alert! : Admin Action Required";
									$message = "Hello Finnup Admin! <br/><br/>". "There is a loan request from a borrower. Please refer the details below <br/><br/>".
									"Borrower Name :".$userdata->name."<br/>".
									"Product Requested :".$productname->name."<br/>".
									"Contact Email :".$userdata->email."<br/>".
									"Contact Mobile :".$userdata->mobile."<br/>".
									
									
									"-----------------------------------------------<br/>
									Team Finnup";

									$to = 'platform@finnup.in';
									//$to = 'rec2004@gmail.com';
									//$to = 'parthiban24242000@gmail.com';
									$email = new \SendGrid\Mail\Mail();
									$email->setSubject($subject);
									$email->addContent("text/html", $message);
									$email->setFrom("platform@finnup.in", 'FinnUp Team');
									$email->addTo($to);							
									$sendgrid = new \SendGrid("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");

									try {
										$response = $sendgrid->send($email);
									}
									 catch (Exception $e) {
										echo 'Caught exception: ',  $e->getMessage(), "\n";
									}
									$resp = array('status' => 200,'message' =>  'Inserted success','data' => "" );

							}catch(Exception $e)
							{
								echo 'Caught exception: ',  $e->getMessage(), "\n";
							}
							
								
						}//----------------end of Conditon 1
						else if($params['tableName']=='fp_lender_proposals')
						{								
							$lenderid=isset($params['data']['lender_id']) ? $params['data']['lender_id'] : 0;
							$borrowerid=isset($params['data']['borrower_id']) ? $params['data']['borrower_id'] : 0;
						
							$sql ="select name, email,mobile from fpa_users where id=".$borrowerid;
							$borrowerdata= $this->db->query($sql)->row();

							$sql ="select name, email,mobile from fpa_users where id=".$lenderid;
							$lenderdata= $this->db->query($sql)->row();

							try{

								$this->db->insert($params['tableName'], $params['data']);

								$subject ="Finnup App Loan Proposal Request Alert! : Admin Action Required";
									$message = "Hello Finnup Admin! <br/><br/>". "There is a proposal  from a lender to a borrower. Please refer the details below<br/><br/>".
									
									"Proposal submited by (Lender)<br/>".
									"----------------------------------------------<br/>".
									
									"Lender POC Name :" .$lenderdata->name."<br/>".
									"Lender Email    : " .$lenderdata->email."<br/>".
									"Lender Mobile   :" .$lenderdata->mobile."<br/><br/>".
									
									"Proposal submited to (Borrower)<br/>".
									"----------------------------------------------<br/>".
									"Borrower POC Name :" .$borrowerdata->name."<br/>".
									"Borrower Email :" .$borrowerdata->email."<br/>".
									"Borrower Mobile :" .$borrowerdata->mobile."<br/>".
						
									"-----------------------------------------------<br/>
									Team Finnup";

									$to = 'platform@finnup.in';
									//$to = 'rec2004@gmail.com';
									//$to = 'parthiban24242000@gmail.com';
									$email = new \SendGrid\Mail\Mail();
									$email->setSubject($subject);
									$email->addContent("text/html", $message);
									$email->setFrom("platform@finnup.in", 'FinnUp Team');
									$email->addTo($to);							
									$sendgrid = new \SendGrid("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");
									try {
										$response = $sendgrid->send($email);
									} catch (Exception $e) {
										echo 'Caught exception: ',  $e->getMessage(), "\n";
									}
									$resp = array('status' => 200,'message' =>  'Inserted success','data' => "" );

							}catch(Exception $e)
							{
								echo 'Caught exception: ',  $e->getMessage(), "\n";
							}





								
						}//----------------end of Condition 2
						else if($params['tableName']=='fp_book_a_call')
						{								
								$b_id='';
								$p_id='';
								$b_id= isset($params['data']['borrower_id']) ? $params['data']['borrower_id'] : 0;
								$p_id= isset($params['data']['product_id']) ? $params['data']['product_id'] : 0;
								//step0
								$sql = "SELECT * FROM  fp_book_a_call WHERE borrower_id =".$b_id ." AND product_id =".$p_id;
								
								if(count($this->db->query($sql)->result())==0)
								{
									//step1
									$this->db->insert($params['tableName'], $params['data']);
									$bookacall_id= $this->db->insert_id();
									
									//step2
									$sql ="
									SELECT 
									B.ID, B.EMAIL, B.MOBILE, U.NAME, P.NAME AS LOAN, L.AMOUNT
									FROM 
									FP_BOOK_A_CALL B, 
									FP_LOANAMOUNT L, 
									FP_BORROWER_USER_DETAILS U, 
									FP_PRODUCTS P
									WHERE
									B.PRODUCT_ID =P.ID AND
									B.BORROWER_ID =U.USER_ID AND
									B.LOANAMOUNT_ID =L.ID AND
									B.ID =".$bookacall_id;	

									$bookacalldata = $this->db->query($sql)->row();
									
									//step3
									$subject ="Finnup App Book a call Alert! : Admin Action Required";
									$message = "Hello Finnup Admin! <br/><br/>". "There is a loan request from a borrower. Please refer the details below <br/><br/>".
									"Borrower Name :".$bookacalldata->NAME."<br/>".
									"Contact Email :".$bookacalldata->EMAIL."<br/>".
									"Contact Mobile :".$bookacalldata->MOBILE."<br/>".
									"Product Requested :".$bookacalldata->LOAN."<br/>".
									"Amount Requested :".$bookacalldata->AMOUNT."<br/>".	
									"-----------------------------------------------<br/>
									Team Finnup";

									$to = 'platform@finnup.in';
									//$to = 'rec2004@gmail.com';
									$email = new \SendGrid\Mail\Mail();
									$email->setSubject($subject);
									$email->addContent("text/html", $message);
									$email->setFrom("platform@finnup.in", 'FinnUp Team');
									$email->addTo($to);							
									$sendgrid = new \SendGrid("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");
									try {
										$response = $sendgrid->send($email);
									} catch (Exception $e) {
										echo 'Caught exception: ',  $e->getMessage(), "\n";
									}
									$resp = array('status' => 200,'message' =>  'Inserted success','data' => "" );
									
								}else
								{
									$respStatus = 201;
									$resp = array('status' => 201,'message' =>  'Already a request present for this product, our admin team will contact you shortly! ');
								}	

								
						}//----------------end of conditon 3 other tables
						else 
						{
							$this->db->insert($params['tableName'], $params['data']);
						}//-----------------end of other tables

						$resp = array('status' => 200,'message' =>  'Inserted success','data' => $this->db->insert_id() );
					}

					json_output($respStatus,$resp);
		        }
			// }
		}
	  }
	


	public function otpresend()
	{
			  $method = $_SERVER['REQUEST_METHOD'];
			  if($method != 'POST')
			  {
				  json_output(400,array('status' => 400,'message' => 'Bad request.'));
			  }else
			  {
				  
					  $response['status'] = 200;
					  $respStatus = $response['status'];
					  if($response['status'] == 200)
					  {
						  $params = json_decode(file_get_contents('php://input'), TRUE);
						  if ($params['tableName'] == "") 
					{
						$respStatus = 400;
						$resp = array('status' => 400,'message' =>  'Fields Missing');
					} else{
					
					if($params['tableName']=='fp_login_history')

		            {                
                     $sql = "UPDATE fp_login_history SET emailotp_status = 0, mobotp_status = 0 WHERE  email='".$params['data']['email'] ."'";
	                 $respStatus=200;
	                $resp = array('status' => 200,'message' =>  'resend Success','data' => $this->db->query($sql));

			
	               }
				}

             json_output($respStatus,$resp);
					  }
				  
			  }
	}

	public function emailotpresend()
	{
			  $method = $_SERVER['REQUEST_METHOD'];
			  if($method != 'POST')
			  {
				  json_output(400,array('status' => 400,'message' => 'Bad request.'));
			  }else
			  {
				  
					  $response['status'] = 200;
					  $respStatus = $response['status'];
					  if($response['status'] == 200)
					  {
						  $params = json_decode(file_get_contents('php://input'), TRUE);
						  if ($params['tableName'] == "") 
					{
						$respStatus = 400;
						$resp = array('status' => 400,'message' =>  'Fields Missing');
					} else{
					
					if($params['tableName']=='fp_login_history')

		            {                
                     $sql = "UPDATE fp_login_history SET emailotp_status = 0 WHERE  email='".$params['data']['email'] ."'";
	                 $respStatus=200;
	                $resp = array('status' => 200,'message' =>  'resend Success','data' => $this->db->query($sql));

			
	               }
				}

             json_output($respStatus,$resp);
					  }
				  
			  }
	}
	public function mobileotpresend()
	{
			  $method = $_SERVER['REQUEST_METHOD'];
			  if($method != 'POST')
			  {
				  json_output(400,array('status' => 400,'message' => 'Bad request.'));
			  }else
			  {
				  
					  $response['status'] = 200;
					  $respStatus = $response['status'];
					  if($response['status'] == 200)
					  {
						  $params = json_decode(file_get_contents('php://input'), TRUE);
						  if ($params['tableName'] == "") 
					{
						$respStatus = 400;
						$resp = array('status' => 400,'message' =>  'Fields Missing');
					} else{
					
					if($params['tableName']=='fp_login_history')

		            {                
                     $sql = "UPDATE fp_login_history SET  mobotp_status = 0 WHERE  email='".$params['data']['mobile'] ."'";
	                 $respStatus=200;
	                $resp = array('status' => 200,'message' =>  'resend Success','data' => $this->db->query($sql));

			
	               }
				}

             json_output($respStatus,$resp);
					  }
				  
			  }
	}






	// 21.12.22 
	public function apidata(){
		$method = $_SERVER['REQUEST_METHOD'];
		if($method != 'POST'){
			json_output(400,array('status' => 400,'message' => 'Bad request.'));
		}else{
			// $check_auth_user = $this->login->check_auth_user();
			// if($check_auth_user == true){
				// $response = $this->login->auth();
				$response['status'] = 200;
				$respStatus = $response['status'];
				if($response['status'] == 200){
					$params = json_decode(file_get_contents('php://input'), TRUE);
					if ($params['tableName'] == "") {
						$respStatus = 400;
						$resp = array('status' => 400,'message' =>  'Fields Missing');
					} else {
						// $params['data']['user_id_fk'] = (int)$params['user_id_fk'];
						// $sql = "SELECT * FROM ".$params['tableName']." ";
						
						$sql = "SELECT * FROM ".$params['tableName']." WHERE borrower_id=".$params['data']['borrower_id'];
						// WHERE =".$params['data'];
						
							 
						// if(count($this->db->query($sql)->result())==0)
								if(count($this->db->query($sql)->result())==0){
									$this->db->insert($params['tableName'], $params['data']);
								}else{
									
								}
						$resp = array('status' => 200,'message' =>  'Inserted success','data' => $this->db->insert_id());
					}
					json_output($respStatus,$resp);
				}
			// }
		}
	}
	
	
public function passwordcheck()
{
  $response['status'] = 200;
  $respStatus = $response['status'];
  $method = $_SERVER['REQUEST_METHOD'];
  if($method != 'POST')
  {
    json_output(400,array('status' => 400,'message' => 'Bad request.'));
  }else
  {
    if( true)
    {
        $params = json_decode(file_get_contents('php://input'), TRUE);
        if($params['tableName'] != '' )
        {
            $sql = "SELECT * FROM fpa_adminusers"." WHERE "." email='".$params['data']['email']."' && password='".$params['data']['password']."'&& status= 1 ";
            $usercheck = $this->db->query($sql)->num_rows();
			
            if($usercheck==0)
            {
              json_output(200, array('status' => 200,'message' => 'Success'));
            }else if($usercheck==1)
            {           
                 json_output(200, array('status' => 200,'message' => 'change'));              
            }else{
				json_output(201, array('status' => 201,'message' => 'invalid'));
			}
        }
    }
  }
}

public function newpassword(){
	$method = $_SERVER['REQUEST_METHOD'];
	if($method != 'POST'){
		json_output(400,array('status' => 400,'message' => 'Bad request.'));
	}else{
			$response['status'] = 200;
			$respStatus = $response['status'];
			if($response['status'] == 200){
				$params = json_decode(file_get_contents('php://input'), TRUE);
				if ($params['tableName']  == '') {
					$respStatus = 400;
					$resp = array('status' => 400,'message' =>  'Fields Missing');
				} else {
					
					$sql = "SELECT * FROM ".$params['tableName'] ; 
							if(count($this->db->query($sql)->result())==1){
								$this->db->insert($params['tableName'], $params['data']);
							}else{
								
							}
					$resp = array('status' => 200,'message' =>  'Inserted success','data' => $this->db->insert_id());
				}
				json_output($respStatus,$resp);
			}
		// }
	}
}



// public function newpasswords()
// {
//   $response['status'] = 200;
//   $respStatus = $response['status'];
//   $method = $_SERVER['REQUEST_METHOD'];
//   if($method != 'POST')
//   {
//     json_output(400,array('status' => 400,'message' => 'Bad request.'));
//   }else
//   {
//     if(true)
//     {
//         $params = json_decode(file_get_contents('php://input'), TRUE);
//         if($params['key'] != '' )
//         {
// 			commentline    $sql = "SELECT * FROM fpa_adminusers WHERE  BINARY ='".$params['data']['email']."' &&  BINARY password='".$params['data']['password']."'  ";
// 			$sql = $sql = "SELECT * FROM fpa_adminusers WHERE ";
// 			if(count($this->db->query($sql)->result())==0){
// 				$this->db->insert($params['tableName'], $params['data']);
// 			}else
//             if($usercheck>=0)
//             {
//               json_output(200, array('status' => 200,'message' => 'Success'));
//             }else
//             {           
//                  json_output(201, array('status' => 201,'message' => 'Invalid'));              
//             }
//         }
//     }
//   }
// }


// ---- Anathai -------- 

	public function changepassword()
    {
        $response['status'] = 200;
        $respStatus = $response['status'];
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {

            // $check_auth_user  = $this->login->check_auth_user();
            if (true) {

                $params = json_decode(file_get_contents('php://input'), true);
                if ($params['tableName'] != '') {

                    $sql = "UPDATE fpa_adminusers SET newuser = 0 .  password ='" . $params['data']['password'] . "'  && old_password='" . $params['data']['old_password'] . "' WHERE   ";

                    // $sql = "SELECT * FROM fpa_adminusers WHERE  email='" . $params['data']['name'] . "' && password='" . $params['data']['password'] . "'";

                    $usercheck = $this->db->query($sql)->num_rows();

                    if ($usercheck >= 1) {
                        json_output(200, array('status' => 200, 'message' => 'Success'));
                    } else {

                        json_output(201, array('status' => 201, 'message' => 'Invalid'));

                    }

                }

            }

        }
    }
// -----------  End -------------- 




// 10.1.22 
public function  addborroweruser()
	  {
			$method = $_SERVER['REQUEST_METHOD'];
			if($method != 'POST')
			{
				json_output(400,array('status' => 400,'message' => 'Bad request.'));
			}
			else
			{
				//  $check_auth_user = $this->login->check_auth_user();
				//  if($check_auth_user == true){
				//  $response = $this->login->auth();
				$response['status'] = 200;
	        	$respStatus = $response['status'];
	        	if($response['status'] == 200)
				{
					$params = json_decode(file_get_contents('php://input'), TRUE);
                	if ($params['tableName'] == "") 
					{
						$respStatus = 400;
						$resp = array('status' => 400,'message' =>  'Fields Missing');
					} 
					else 
					{
					    $this->db->insert($params['tableName'], $params['data']);
						$resp = array('status' => 200,'message' =>  'Inserted success','data' => $this->db->insert_id() );
					}

					json_output($respStatus,$resp);
		        }
			// }
		}
	  }


	public function check_token(){
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
	}


	public function loan_request()
	{
		  $method = $_SERVER['REQUEST_METHOD'];
		  if($method != 'POST')
		  {
			  json_output(400,array('status' => 400,'message' => 'Bad request.'));
		  }
		  else
		  {
			  $check_auth_user = $this->login->check_auth_user();
			  if($check_auth_user == true){
			  $response['status'] = 200;
			  $respStatus = $response['status'];
			  if($response['status'] == 200)
			  {
				  $params = json_decode(file_get_contents('php://input'), TRUE);
				  if ($params['tableName'] == "") 
				  {
					  $respStatus = 400;
					  $resp = array('status' => 400,'message' =>  'Fields Missing');
				  } 
				  else 
				  {
					  $borrowerid = $params['data']['borrower_id'];
					  $lender_product_details_id = $params['data']['lender_product_details_id'];
					  $product=$params['data']['product_slug'];
					  $conditions = array( 'borrower_id'=>$borrowerid, "lender_product_id" =>  $params['data']['lender_product_details_id']);
					  $this->db->select('id');
					  $this->db->from('fpa_loan_applications');
					  $this->db->where($conditions);
					  $num_results = $this->db->count_all_results();
					//   echo $num_results;
					  if($num_results == 0){
						$conditions = array('borrower_id'=>$borrowerid, "product_slug"=> $params['data']['product_slug'],'status'=>'A');
						$this->db->select('id');
						$this->db->from('fp_borrower_loanrequests');
						$this->db->where($conditions);
						$num_results = $this->db->count_all_results();
						// echo $num_results;
						if($num_results == 0){
							$this->db->insert('fp_borrower_loanrequests	', $params['data']);
							$loan_request_id = $this->db->insert_id();
						}else{
							$query=  $this->db->get_where('fp_borrower_loanrequests', array('borrower_id'=>$borrowerid, 'product_slug'=>$params['data']['product_slug']));
							// $loan_request_id = $this->db->get();
							foreach ($query->result() as $row)
						  	{
							$loan_request_id  =  $row->id;
							// echo $loan_request_id;
						 	}
						}

						if(isset($loan_request_id)){

							$query = $this->db->get_where('fp_lender_product_details', array('id' => $params['data']['lender_product_details_id']));
					  		foreach ($query->result() as $row)
						  	{
								  $lender_id  =$row->lender_id;
						  	}

							$dataarr= array(
								'loanrequest_id' => $loan_request_id,
								'borrower_id' => $borrowerid,
								'lendermaster_id' => $lender_id,
								'product_slug' => $product,
								'lender_product_id' =>  $params['data']['lender_product_details_id'],	
							  );

							$this->db->insert('fpa_loan_applications',  $dataarr);
							$loan_application_id = $this->db->insert_id();



							$borrowerid=isset($params['data']['borrower_id']) ? $params['data']['borrower_id'] : 0;
							$product=isset($params['data']['product_slug']) ? $params['data']['product_slug'] : "";
							$sql ="select name from fp_products where slug="."'".$product."'";
							$productname = $this->db->query($sql)->row();
							$sql ="select name, email,mobile from fpa_users where id=".$borrowerid;

							$userdata= $this->db->query($sql)->row();
							$subject ="Finnup App Loan Request Alert! : Admin Action Required";
							$message = "Hello Finnup Admin! <br/><br/>". "There is a loan request from a borrower. Please refer the details below <br/><br/>".
							"Borrower Name :".$userdata->name."<br/>".
							"Product Requested :".$productname->name."<br/>".
							"Contact Email :".$userdata->email."<br/>".
							"Contact Mobile :".$userdata->mobile."<br/>".
							  
							  
							  "-----------------------------------------------<br/>
							  Team Finnup";

							  $to = 'platform@finnup.in';
							  //$to = 'rec2004@gmail.com';
							//   $to = 'parthiban24242000@gmail.com';
							  $email = new \SendGrid\Mail\Mail();
							  $email->setSubject($subject);
							  $email->addContent("text/html", $message);
							  $email->setFrom("platform@finnup.in", 'FinnUp Team');
							  $email->addTo($to);							
							  $sendgrid = new \SendGrid("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");

							  try {
								  $response = $sendgrid->send($email);
							  }
							   catch (Exception $e) {
								  echo 'Caught exception: ',  $e->getMessage(), "\n";
							  }
							  return json_output(200,array('status' => 200,'Message' => "Added successfully", 'data'=>$loan_application_id ));

						}else{
							return json_output(200,array('status' => 400,'Message' => "Something When worng"));
						}
						
					  }else{
						return json_output(200,array('status' => 401,'Message' => "Already Submitted"));
					  }
					  
					//   $resp = array('status' => 200,'message' =>  'Inserted success','data' => $this->db->insert_id() );
				  }

				//   json_output($respStatus,$resp);
			  }else{
				return json_output(200,array('status' => 400,'Message' => "Response status When worng"));
			  }
		  // }
	  	}else{
			return json_output(200,array('status' => 400,'Message' => "Invalid Authentication"));
		}
		}
	}

	public function pincodedata_search()
    {
      $method = $_SERVER['REQUEST_METHOD'];
      if($method == 'POST')
      {
        
        $response['status']=200;
        $respStatus = $response['status'];
        $params = json_decode(file_get_contents('php://input'), TRUE);
          
            $selectkey   = isset($params['selectkey']) ? $params['selectkey'] : "*"; 
            $join     = isset($params['key']) ? $params['key'] : "";
            $where = isset($params['where']) ? $params['where'] : "";
            if($params['tableName']!=''){
              
              $sql = "SELECT " .$selectkey. " FROM ".$params['tableName']." ".$join." WHERE ".$where;
            }
            else
            {
              $sql = "SELECT l.id as locationid, l.name as locationname, l.city_id,
              c.name as cityname,c.district_id, d.name as districtname,d.state_id,
              s.name as statename FROM fp_location l,fp_city c,fp_district d,fp_state s
              WHERE l.city_id=c.id AND c.district_id=d.id AND d.state_id=s.id AND l.pincode = ".$where;
            }
            
            $resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql)->result());
            json_output($respStatus,$resp);
        
      }
      else
      {
             json_output(400,array('status' => 400,'message' => 'Bad request.'));
      }
    }


	public function allupdatenew()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {

            $response['status'] = 200;
            $respStatus = $response['status'];
            if ($response['status'] == 200) {
                $params = json_decode(file_get_contents('php://input'), true);
                if ($params['tableName'] == "") {
                    $respStatus = 400;
                    $resp = array('status' => 400, 'message' => 'Fields Missing');
                } else {
                    $d_id = isset($params['data']['id']) ? $params['data']['id'] : "0";

                    $selectkey = isset($params['selectkey']) ? $params['selectkey'] : "";
                    $sql = "SELECT * FROM " . $params['tableName'] . " WHERE id =" . $d_id;
                   
                   

                    if (count($this->db->query($sql)->result()) == 0) {
                        $this->db->insert($params['tableName'], $params['data']);

                       
                        $share_holdingdate = array('share_date' => $params['data']['share_date']);

                        $this->db->where('borrower_id', $params['data']['borrower_id']);
                        $this->db->update('fp_director_shareholding', $share_holdingdate);

                    } else {
                        $this->db->where('id', $params['data']['id']);
                        $this->db->update($params['tableName'], $params['data']);

                    }
                    $resp = array('status' => 200, 'message' => 'success', 'data' => $this->db->insert_id());
                }
                json_output($respStatus, $resp);
            }
            // }
        }
    }






	// 22/06/23  code by parthiban 


	public function loanrequestedit()
  {
			$method = $_SERVER['REQUEST_METHOD'];
			if($method != 'POST')
			{
				json_output(400,array('status' => 400,'message' => 'Bad request.'));
			}else
			{
				
					$response['status'] = 200;
					$respStatus = $response['status'];
					if($response['status'] == 200)
					{
						$params = json_decode(file_get_contents('php://input'), TRUE);
						if ($params['tableName'] == "") 
						{
							$respStatus = 400;
							$resp = array('status' => 400,'message' =>  'Fields Missing');
						} else 
						{
							$d_id = isset($params['data']['id']) ? $params['data']['id'] : "0";
							$sql = "SELECT * FROM ".$params['tableName']." WHERE id =".$d_id;
								
									if(count($this->db->query($sql)->result())==0){
										$this->db->insert($params['tableName'], $params['data']);
									}else{



										
                                          
									$condition= array('id'=>$params['data']['id'] ,'borrower_id'=>$params['data']['borrower_id'],'product_slug'=>$params['data']['product_slug']);


								
									$this->db->where($condition);
									$count = $this->db->count_all_results($params['tableName']);
									if ($count == 1) {
										$this->db->where($condition);
										$this->db->update($params['tableName'], $params['data']); 
										
										$resp = array('status' => 200,'message' =>  'success','data' => 'Does Not Updated Successfully');

									
									} else {
										$resp = array('status' => 201,'message' =>  'success','data' => 'Does Not Updated Successfully');


									
									}



									



									// 	$update = mysqli_query($uinsert) ;

									// 	if(mysqli_affected_rows($update) == 1 ){ 
									// 		$resp = array('status' => 200,'message' =>  'success','data' =>'Updated Successfully');
									
									//    }
									//    else{
									// 	$resp = array('status' => 200,'message' =>  'success','data' => 'Does Not Updated Successfully');

									
									//    }
									}
							// $resp = array('status' => 200,'message' =>  'success','data' => $this->db->insert_id());
						}
						json_output($respStatus,$resp);
					}
				// }
			}
  }



}//------------------end of class------------------------------------------
//   $this->db->insert($params['tableName'], $params['data']);


