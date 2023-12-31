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


//-----------------------------------------------------------------------------
//-----------------------------------------------------------------------------
//-----------------------------------------------------------------------------
class Adminusers extends CI_Controller 
{
	private $_iv = 'xb5wypRrVgl0rYm1';
	private $ciphering = "AES-128-CTR";
	private $key = "finnup";
	private $options = 0;	
	
	public function __construct(){
		parent::__construct();
		$this->load->helper('json_output');		
	}

	public function passwordcheck()
	{
		$response['status'] = 200;
		$respStatus = $response['status'];
		$method = $_SERVER['REQUEST_METHOD'];
		// echo openssl_decrypt ("ffshuxM6R3c=", $this->ciphering, $this->key, $this->options, $this->_iv);
		 
		if($method != 'POST')
		{
			json_output(400,array('status' => 400,'message' => 'Bad request.'));
		}
	
		else{

			
		$check_auth_user  = $this->login->check_auth_user();
		if($check_auth_user)
		{
					if($response['status'] == 200)
					{
						$params = json_decode(file_get_contents('php://input'), TRUE);
						if ($params['tableName'] == "fpa_adminusers") 
						{

							$simple_string = $params['data']['password'];
							$iv_length = openssl_cipher_iv_length($this->ciphering);
							$encryption = openssl_encrypt($simple_string, $this->ciphering,
			           		$this->key, $this->options, $this->_iv);

							$checkuser = array('email' => $params['data']['email'], 'password' => $encryption,'status' => 1);
							$this->db->where($checkuser); 
							$count = $this->db->count_all_results($params['tableName']);

							if($count==1){
								$flag = array('email' => $params['data']['email'], 'password' => $encryption, 'password_status' => 1);
								$this->db->where($flag);
								$countflag = $this->db->count_all_results($params['tableName']);

								$query = $this->db->get_where('fpa_adminusers', array('email' => $params['data']['email']));
								foreach ($query->result() as $row)
									{
									    
										$txnArr[] = [
											'id'=>$row->id,
											'email'=>$row->email,
											'name'=>$row->name,
											'slug'=>$row->role_slug
												
									];
									}

								$token = $this->jwttoken->token($txnArr);

								$this->db->where('email', $params['data']['email']);
								$update = $this->db->update("fpa_adminusers", array('token'=>$token));
								$txnArr[] = [
									'token' => $token
										
									];
								if($countflag==0){
									$resp = array('status' => 200,'message' =>  'success','data'=>$txnArr);
									json_output($respStatus,$resp);
								}else{
									$resp = array('status' => 200,'message' =>  'success', 'type' => 'newUser', 'data'=>$txnArr);
									json_output($respStatus,$resp);
								}
							}else{
								json_output(200,array('status' => 401,'message' => 'Bad requests.'));
							}
						} else 
						{
							json_output(400,array('status' => 401,'message' => 'Bad request123.'));
						}
						// json_output($respStatus,$resp);
					}
					else
					{
				
					json_output(400,array('status' => 400,'message' => 'Bad request.'));
				}
			}else{
				json_output(400,array('status' => 400,'message' => 'Bad request.'));
			}
			}
	} // end of passwordcheck

	
	public function adminusercreate()
    {
        $response['status'] = 200;
        $respStatus = $response['status'];
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_user = $this->login->check_auth_user();
            if ($check_auth_user) {
                if ($response['status'] == 200) {
                    $params = json_decode(file_get_contents('php://input'), true);
                    $useremail = $params['data']['email'];

					$username = $params['data']['name'];
                    $userpassword = $params['data']['password'];
					$userrole = $params['data']['role_slug'];

                    $userroleslug = '';

                    if ($userrole == 'sa') {

                        $userroleslug = 'Super Admin';
                    } elseif ($userrole == 'ad') {
                        $userroleslug = 'Admin';
                    } elseif ($userrole == 'rm') {
                        $userroleslug = 'Relationship Manager';
                    } elseif ($userrole == 'cm') {
                        $userroleslug = 'Credit Manager';
                    } elseif ($userrole == 'cc') {
                        $userroleslug = 'Credit Committee';
                    }
					

                    if ($params['tableName'] == "fpa_adminusers") {
                        $simple_string = $params['data']['password'];
                        $iv_length = openssl_cipher_iv_length($this->ciphering);
                        $encryption = openssl_encrypt($simple_string, $this->ciphering,
                        $this->key, $this->options, $this->_iv);

                        $d_id = isset($params['data']['id']) ? $params['data']['id'] : "0";
				
                        $params['data']['password'] = $encryption;
                        $sql = "SELECT * FROM " . $params['tableName'] . " WHERE email ="."'". $useremail."'";
                        // $sql ="select email from fpa_adminusers where id=".$d_id;
                        // $userdata= $this->db->query($sql)->row();

                        if (count($this->db->query($sql)->result()) == 0) {
                            $var = $this->db->insert($params['tableName'], $params['data']);

                            if ($var == true) {
								$subject = "Dear ".$username.",";
                                $message = "Dear ".$username.","."<br/>"."<br/>"."<br/>"."FinnUp Superadmin has invited you to app.finnup.in/#/admin as a ". $userroleslug.", Please use the following link to set your password and login. " . "<br/>" ."<br/>" .
                                "Email :" . $useremail . "<br/>" .
                                "Password :" . $userpassword . "<br/>" .
                                // "Password :" . $userdata->password. "<br/>" .

                                "-----------------------------------------------<br/>
                               Team Finnup";

                                
                                $to = $useremail;
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
                        } else {
                            $this->db->where('id', $params['data']['id']);
                            $update = $this->db->update($params['tableName'], $params['data']);

                            if ($update == true) {

                                $subject = "Finnup App - Admin Login Credential";
                                $message = "Hello Finnup Admin! <br/><br/>" . "Please find the login credential below <br/><br/>" .
                                "Email :" . $useremail . "<br/>" .
                                "Password :" . $userpassword . "<br/>" .
                                // "Password :" . $userdata->password. "<br/>" .

                                "-----------------------------------------------<br/>
                           Team Finnup";

                                // $to = 'platform@finnup.in';
                                // $to = 'rec2004@gmail.com';
                                // $to = $userdata->email;
                                $to = $useremail;
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

                        }

                        $resp = array('status' => 200, 'message' => 'success', 'data' => $this->db->insert_id());

                    } else {
                        $respStatus = 400;
                        $resp = array('status' => 400, 'message' => 'Fields Missing');
                    }
                    json_output($respStatus, $resp);
                } else {
                    json_output(400, array('status' => 400, 'message' => 'Bad request.'));
                }
            }

        }

    } // end of adminusercreate 

	public function changepassword()
	{
		$response['status'] = 200;
		$respStatus = $response['status'];
		$method = $_SERVER['REQUEST_METHOD'];
		if($method != 'POST')
		{
			json_output(400,array('status' => 400,'message' => 'Bad request.'));
		}
	
		else{
		
		$check_auth_user  = $this->login->check_auth_user();
		if($check_auth_user)
		{
					if($response['status'] == 200)
					{
						$params = json_decode(file_get_contents('php://input'), TRUE);
						if ($params['tableName'] == "fpa_adminusers") 
						{

							$old_password = $params['data']['old_password'];
							// $new_password = $params['data']['new_password'];
              $new_password = $params['data']['password'];

							$iv_length = openssl_cipher_iv_length($this->ciphering);
							$old_password = openssl_encrypt($old_password, $this->ciphering, $this->key, $this->options, $this->_iv);
							// $new_password = openssl_encrypt($new_password, $this->ciphering, $this->key, $this->options, $this->_iv);
              $new_password = openssl_encrypt($new_password, $this->ciphering, $this->key, $this->options, $this->_iv);

							if($old_password != $new_password){
								$checkuser = array('email' => $params['data']['email'], 'password' => $old_password);
								$this->db->where($checkuser);
								$count = $this->db->count_all_results($params['tableName']);

								if($count==1){

									// $flag = array('email' => $params['data']['email'], 'password' => $old_password, 'status' => 1);
                                     $flag = array('email' => $params['data']['email'], 'password' => $old_password, 'password_status' => 1);
									$this->db->where($flag);
									$countflag = $this->db->count_all_results($params['tableName']);
								
									if($countflag==1){
										$data = array(
											'old_password' => $old_password,
											'password' => $new_password,
											'password_status'=> 0
											);
										$this->db->where('email' , $params['data']['email']);
										$this->db->update($params['tableName'], $data);

										$token = $this->jwttoken->token($params['data']['email']);

										$resp = array('status' => 200,'message' =>  'success', 'token'=> $token);
										json_output($respStatus,$resp);
									}else{

										$resp = array('status' => 400,'message' => 'Something When Worng ');
										json_output($respStatus,$resp);
									}
								}else{
									json_output(400,array('status' => 401,'message' => 'Bad request.'));
								}
							}else if($old_password == $new_password){
								
								$resp = array('status' => 401,'message' => 'old password and new password should not be same');
								json_output($respStatus,$resp);
							}else{
								$resp = array('status' => 401,'message' => 'Something When Worng');
								json_output($respStatus,$resp);
							}
						}else 
							{
								json_output(400,array('status' => 401,'message' => 'Bad request.'));
							}
							// json_output($respStatus,$resp);
					}else
					{
				
					json_output(400,array('status' => 400,'message' => 'Bad request.'));
				}
			}else{
				json_output(400,array('status' => 400,'message' => 'Bad request.'));
			}
			}
	}  // end of changepassword 

	public function get_fpa_users()	{
		$response['status'] = 200;
		$respStatus = $response['status'];
		$method = $_SERVER['REQUEST_METHOD'];
		if($method != 'POST')
		{
			json_output(400,array('status' => 400,'message' => 'Bad request.'));
		}

		else{

			$check_auth_user  = $this->login->check_auth_user();
			if($check_auth_user)
			{
						if($response['status'] == 200)
						{
							$params = json_decode(file_get_contents('php://input'), TRUE);
							$d_id = isset($params['data']['id']) ? $params['data']['id'] : "0";
							if ($params['tableName'] == "fpa_adminusers" ) 
							{
								// $simple_string = $params['data']['password'];
								$iv_length = openssl_cipher_iv_length($this->ciphering);
								if($params['where']){
									$query = $this->db->get_where('fpa_adminusers', array('id' => $params['where']));
								}else{
									$query = $this->db->get('fpa_adminusers');
								}
								foreach ($query->result() as $row)
									{
									    
										$txnArr[] = [
											'id'=>$row->id,
											'email'=>$row->email,
											'mobile'=>$row->mobile,
											'name'=>$row->name,
											'role_slug'=>$row->role_slug,
											'location_id'=>$row->location_id,
											'password'=>openssl_decrypt ($row->password, $this->ciphering, $this->key, $this->options, $this->_iv),
									
									];
									}
                                    $subject ="Finnup Admin Team ";
									$message = "Hello Finnup Admin! <br/><br/>" . "Please find the login credential below <br/><br/>" .
									"Email :" . $row->email . "<br/>" .
									 "Password :" .openssl_decrypt ($row->password, $this->ciphering, $this->key, $this->options, $this->_iv) . "<br/>" . 
								// $to =  $row->email;
								$to='parthiban24242000@gmail.com';
								// echo "$to";
								$email = new \SendGrid\Mail\Mail();
								$email->setSubject($subject);
								$email->addContent("text/html", $message);
								$email->setFrom('platform@finnup.in', 'FinnUp Team');
								// $email->setFrom('platform@finnup.in', 'FinnUp Team');
								// $email->addBcc('saravanan@thesquaircle.com');
								// $email->addBcc('sheik@thesquaircle.com');
								// $email->addBcc('dhanasekarancse08@gmail.com');
								 $email->addTo($to);							
								$sendgrid = new \SendGrid("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");
								try {
									$response = $sendgrid->send($email);
									// $txnArr[]=['otp' => $num_str];
									
								} catch (Exception $e) {
									echo 'Caught exception: ',  $e->getMessage(), "\n";
									json_output(400,array('status' => 400,'message' => 'Invalid Email Id.'));
								}

								$resp = array('status' => 200,'message' =>  'success','data' => $txnArr);
									
							} else 
							{
								$respStatus = 400;
								$resp = array('status' => 400,'message' =>  'Fields Missing');
							}
							json_output($respStatus,$resp);
						}
			else
			{
				json_output(400,array('status' => 400,'message' => 'Bad request.'));
			}
		}

		}

	}

	public function sendotp(){
		$response['status'] = 200;
		$respStatus = $response['status'];
		$method = $_SERVER['REQUEST_METHOD'];
		$params = json_decode(file_get_contents('php://input'), TRUE);
		if($method != 'POST')
		{
			json_output(400,array('status' => 400,'message' => 'Bad request.'));
		}
		else{
			if($params ['data']['email'] != "" || $params['data']['email'] != null )
			{
				$query = $this->db->get_where('fpa_adminusers', array('email' => $params['data']['email'],'status'=>1));
				if($query->result()){

					$this->db->where('email', $params['data']['email']);
					$this->db->where('is_active', 1);
					$this->db->update('fp_adminotp',array("is_active" => 0)); 
				
				
					foreach($query->result() as $row){
					
						$admin_id= $row->id;
						$admin_email = $row->email;
					
					}
					$num_str = 0;
					$num_str =  sprintf("%06d", mt_rand(100000, 999999));
							
								$subject ="Finnup OTP";
								$message = "Your Forgot otp is : " .$num_str;
								
								$to = $params['data']['email'];
								// echo "$to";
								$email = new \SendGrid\Mail\Mail();
								$email->setSubject($subject);
								$email->addContent("text/html", $message);
								$email->setFrom('platform@finnup.in', 'FinnUp Team');
								// $email->setFrom('platform@finnup.in', 'FinnUp Team');
								// $email->addBcc('saravanan@thesquaircle.com');
								// $email->addBcc('sheik@thesquaircle.com');
								// $email->addBcc('dhanasekarancse08@gmail.com');
								 $email->addTo($to);							
								$sendgrid = new \SendGrid("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");
								try {
									$response = $sendgrid->send($email);
									// $txnArr[]=['otp' => $num_str];
									
								} catch (Exception $e) {
									echo 'Caught exception: ',  $e->getMessage(), "\n";
									json_output(400,array('status' => 400,'message' => 'Invalid Email Id.'));
								}
								$data['data'] = [
									'admin_id' => $admin_id,
									'email'=>$admin_email,
									'otp'=>$num_str
									
								];
								$this->db->insert('fp_adminotp', $data['data']);
						

					json_output(200,array('status' => 200,'message' => "otp send"));
				}else{
					json_output(200,array('status' => 400,'message' => 'Invalid Email Id.'));
				}

			}else{
				json_output(200,array('status' => 400,'message' => 'Bad request.'));
			}
		}
	}  // end of sendotp



	public function check_otp(){

		$response['status'] = 200;
		$respStatus = $response['status'];
		$method = $_SERVER['REQUEST_METHOD'];
		$params = json_decode(file_get_contents('php://input'), TRUE);
		$email = isset($params['email']) ? $params['email'] :False;
		$otp = isset($params['otp']) ? $params['otp'] :False;
		// $mobile = isset($params['data']['mobile']) ? $params['data']['mobile'] : null; 
		if($method != 'POST')
		{
			json_output(400,array('status' => 400,'message' => 'Bad request.'));
		}else{
			

			if($email == true && $otp == true){
            //    echo "new";
			
				$curr = date('Y-m-d H:i:s');
				$last_min = date('Y-m-d H:i:s', strtotime('-5 minutes'));
			
				$this->db->select();
				$this->db->from('fp_adminotp');
				$this->db->where('email', $email);
				$this->db->where('otp', $otp);
				$this->db->where('is_active', 1);
				// $this->db->where('created_at >=', $last_min);
				// $this->db->where('created_at <=', $curr);
				$result = $this->db->get();

				if($result->num_rows() >= 1){
						$data['data']= 0;
					$this->db->where('email', $email);
					$this->db->where('otp', $otp);
					$this->db->where('is_active', 1);
					$this->db->update('fp_adminotp',array("is_active" => 0)); 
					json_output(200,array('status' => 200,'message' => "success"));
				}else{

				$this->db->from('fp_adminotp');
				$this->db->where('email', $email);
				$this->db->where('is_active', 1);
				$this->db->where('created_at <=', $last_min);
				$this->db->update('fp_adminotp',array("is_active" => 0));
				json_output(200,array('status' => 400,'message' => "Try Again"));
				}
			}
			else{
				json_output(200,array('status' => 400,'message' => "Try Agains"));
			}

				
			// $query = $this->db->get_where('fp_adminotp', array('email' => $params['email'] , 'otp' => $params['otp'], 'isActive'=> 1));
			// echo $query->num_rows();
			
		}

	}    // end of check_otp

	public function adminforgotpass(){
		$response['status'] = 200;
		$respStatus = $response['status'];
		$method = $_SERVER['REQUEST_METHOD'];
		$params = json_decode(file_get_contents('php://input'), TRUE);
		$email  = isset($params['email']) ? $params['email'] :False;
		$password  = isset($params['password']) ? $params['password'] :False;
		if($method != 'POST')
		{
			json_output(400,array('status' => 400,'message' => 'Bad request.'));
		}
		else{
			if($email == true && $password == true){

				$curr = date('Y-m-d H:i:s');
				$last_min = date('Y-m-d H:i:s', strtotime('-60 minutes'));
			
				$this->db->select();
				$this->db->from('fp_adminotp');
				$this->db->where('email', $email);
				$this->db->where('is_active', 0);
				// $this->db->where('created_at >=', $last_min);
				// $this->db->where('created_at <=', $curr);
				$otpcheck = $this->db->get();

				if($otpcheck->num_rows() >= 1){

				$password = openssl_encrypt($password, $this->ciphering, $this->key, $this->options, $this->_iv);
				$this->db->select();
				$this->db->from('fpa_adminusers');
				$this->db->where('email', $email);
				$old_password = $this->db->get();
				foreach($old_password->result() as $row){
					$old_pass = $row->password;
				}

				$data['data']=[
					'old_password' => $old_pass,
					'password'=> $password,
					'password_status'=>0
				];
				$adminname=$params['email'];
				// $this->db->from('fpa_adminusers');
				$this->db->where('email', $email);
				$this->db->update('fpa_adminusers',$data['data']); 
					// echo $old_password;
					json_output(200,array('status' => 200,'message' => "success"));
					
					$subject = "Dear Admin,";
                    $message = "Dear ".$adminname.","."<br/>"."<br/>"."Please click on the below link to login and reset the password." ."<br/>".
                   "link : app.finnup.in/#/admin."."<br/>"."<br/>".
                    "Thanks.";
                    
                    $to = 'platform@finnup.in';
                    $email = new \SendGrid\Mail\Mail();
                    $email->setSubject($subject);
                    $email->addContent("text/html", $message);
                    $email->setFrom("platform@finnup.in", 'FinnUp Team');
                    $email->addTo($to);
                    // $email->addTo($tos);
                    $sendgrid = new \SendGrid("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");
                    try {
                            $response = $sendgrid->send($email);
                    } catch (Exception $e) {
                            echo 'Caught exception: ', $e->getMessage(), "\n";
                    }

				}else{
					json_output(400,array('status' => 400,'message' => "Something When  Wrong guys"));
				}

			}else{
				json_output(200,array('status' => 400,'message' => "Something When Wrong"));
			}
			
		}
		
	}  // end of adminforgotpass

	public function domainblocklist()
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
							  $domain = isset($params['data']['domain']) ? $params['data']['domain'] : "";
							  $sql = "SELECT * FROM ".$params['tableName']." WHERE id =".$d_id;
							  $domain_dub_check = "SELECT * FROM ".$params['tableName']." WHERE domain = '".$domain."'";
								  
									  if(count($this->db->query($sql)->result())==0 ){
										if(count($this->db->query($domain_dub_check)->result())==0 ){
											$this->db->insert($params['tableName'], $params['data']);
										}else{
											$this->db->where('domain',$params['data']['domain'] );
											$this->db->update($params['tableName'], array("status"=> 1)); 
										}
										 
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

	public function connector()
	{
			$method = $_SERVER['REQUEST_METHOD'];
			if($method =="POST")
			{
					// $checkToken = $this->check_token();
					if(true)
					{
							$response['status']=200;
							$respStatus = $response['status'];
							$params 	= json_decode(file_get_contents('php://input'), TRUE);

							$selectkey 	= isset($params['selectkey']) ? $params['selectkey'] : "*"; 
							$join 		= isset($params['key']) ? $params['key'] : "";
							$where 		= isset($params['where']) ? $params['where'] : "";	

							$sql = "SELECT * FROM fp_connector_users where fp_connector_users.status='not created' and fp_connector_users.connector_status=1";


							$resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql)->result());
							return json_output($respStatus,$resp);
					}
					else
					{
						return json_output(400,array('status' => 400,'message' => "Unauthorized"));
					}
				
			}
			else
			{
					return json_output(400,array('status' => 400,'message' => 'Bad request.'));
			}
		
	} 

	public function partner_data()
	{
			$method = $_SERVER['REQUEST_METHOD'];
			if($method =="POST")
			{
					// $checkToken = $this->check_token();
					if(true)
					{
							$response['status']=200;
							$respStatus = $response['status'];
							$params 	= json_decode(file_get_contents('php://input'), TRUE);

							$selectkey 	= isset($params['selectkey']) ? $params['selectkey'] : "*"; 
							$join 		= isset($params['key']) ? $params['key'] : "";
							$where 		= isset($params['where']) ? $params['where'] : "";	

							$sql = "SELECT pa.id, pa.name, pa.email, pa.mobile, pa.password,pa.company_name, pa.role_slug, pa.partner_id, pa.created_at, pr.name as rolename, pr.slug FROM fpa_partners pa, fpa_partner_roles pr WHERE pa.role_slug=pr.slug order by pa.id desc";


							$resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql)->result());
							return json_output($respStatus,$resp);
					}
					else
					{
						return json_output(400,array('status' => 400,'message' => "Unauthorized"));
					}
				
			}
			else
			{
					return json_output(400,array('status' => 400,'message' => 'Bad request.'));
			}
		
	} 
	// ------End of partner_data

	public function getapproved_connectors()
	{
			$method = $_SERVER['REQUEST_METHOD'];
			if($method =="POST")
			{
					// $checkToken = $this->check_token();
					if(true)
					{
							$response['status']=200;
							$respStatus = $response['status'];
							$params 	= json_decode(file_get_contents('php://input'), TRUE);

							$selectkey 	= isset($params['selectkey']) ? $params['selectkey'] : "*";

							$sql = "SELECT * FROM `fp_connector_users` WHERE status='created'";


							$resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql)->result());
							return json_output($respStatus,$resp);
					}
					else
					{
						return json_output(400,array('status' => 400,'message' => "Unauthorized"));
					}
				
			}
			else
			{
					return json_output(400,array('status' => 400,'message' => 'Bad request.'));
			}
		
	} 


	public function rejectedpartner_data()
	{
			$method = $_SERVER['REQUEST_METHOD'];
			if($method =="POST")
			{
					// $checkToken = $this->check_token();
					if(true)
					{
							$response['status']=200;
							$respStatus = $response['status'];
							$params 	= json_decode(file_get_contents('php://input'), TRUE);

							$selectkey 	= isset($params['selectkey']) ? $params['selectkey'] : "*";

							$sql = "SELECT * FROM fp_connector_users cu WHERE cu.status='not created' AND cu.connector_status=0";


							$resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql)->result());
							return json_output($respStatus,$resp);
					}
					else
					{
						return json_output(400,array('status' => 400,'message' => "Unauthorized"));
					}
				
			}
			else
			{
					return json_output(400,array('status' => 400,'message' => 'Bad request.'));
			}
		
	} 
	// ------End of rejectedpartner_data


	
	public function get_fpa_partners()	{
		$response['status'] = 200;
		$respStatus = $response['status'];
		$method = $_SERVER['REQUEST_METHOD'];
		if($method != 'POST')
		{
			json_output(400,array('status' => 400,'message' => 'Bad request.'));
		}

		else{

			$check_auth_user  = $this->login->check_auth_user();
			if($check_auth_user)
			{
						if($response['status'] == 200)
						{
							$params = json_decode(file_get_contents('php://input'), TRUE);
							$d_id = isset($params['data']['id']) ? $params['data']['id'] : "0";
							if ($params['tableName'] == "fpa_partners" ) 
							{
								// $simple_string = $params['data']['password'];
								$iv_length = openssl_cipher_iv_length($this->ciphering);
								if($params['where']){
									$query = $this->db->get_where('fpa_partners', array('id' => $params['where']));
								}else{
									$query = $this->db->get('fpa_partners');
								}
								foreach ($query->result() as $row)
									{
									    
										$txnArr[] = [
											'id'=>$row->id,
											'email'=>$row->email,
											'mobile'=>$row->mobile,
											'name'=>$row->name,
											'role_slug'=>$row->role_slug,
											'company_name'=>$row->company_name,
											'password'=>openssl_decrypt ($row->password, $this->ciphering, $this->key, $this->options, $this->_iv),
									
									];
									}
                                    $subject ="Finnup Admin Team ";
									$message = "Hello Finnup Admin! <br/><br/>" . "Please find the login credential below <br/><br/>" .
									"Email :" . $row->email . "<br/>" .
									 "Password :" .openssl_decrypt ($row->password, $this->ciphering, $this->key, $this->options, $this->_iv) . "<br/>" . 
								$to =  $row->email;
								// $to='parthiban24242000@gmail.com';
								// echo "$to";
								$email = new \SendGrid\Mail\Mail();
								$email->setSubject($subject);
								$email->addContent("text/html", $message);
								$email->setFrom('support@finnup.in', 'FinnUp Team');
								// $email->setFrom('platform@finnup.in', 'FinnUp Team');
								// $email->addBcc('saravanan@thesquaircle.com');
								// $email->addBcc('sheik@thesquaircle.com');
								// $email->addBcc('dhanasekarancse08@gmail.com');
								 $email->addTo($to);							
								$sendgrid = new \SendGrid("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");
								try {
									$response = $sendgrid->send($email);
									// $txnArr[]=['otp' => $num_str];
									
								} catch (Exception $e) {
									echo 'Caught exception: ',  $e->getMessage(), "\n";
									json_output(400,array('status' => 400,'message' => 'Invalid Email Id.'));
								}

								$resp = array('status' => 200,'message' =>  'success','data' => $txnArr);
									
							} else 
							{
								$respStatus = 400;
								$resp = array('status' => 400,'message' =>  'Fields Missing');
							}
							json_output($respStatus,$resp);
						}
			else
			{
				json_output(400,array('status' => 400,'message' => 'Bad request.'));
			}
		}

		}

	}




} // -------------------------- end ---------------------
