<?php

header('Access-Control-Allow-Origin: *'); //for allow any domain, insecure
header('Access-Control-Allow-Headers: *'); //for allow any headers, insecure
header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE'); //method allowed
header("HTTP/1.1 200 OK");

defined('BASEPATH') OR exit('No direct script access allowed');
include APPPATH . 'ThirdParty/sendgrid-php/sendgrid-php.php';
include APPPATH . 'ThirdParty/mTalkz.php';
include APPPATH . 'libraries/Femail.php';
// include APPPATH . 'controllers/BorrowerAuth.php';
// include APPPATH . 'ThirdParty/probeapi.php';


//-----------------------------------------------------------------------------
//-----------------------------------------------------------------------------
//-----------------------------------------------------------------------------
class Userverification extends CI_Controller 
{

	var $otpemail_from   		= define_otp_fromemail;
	var $otpemail_subject 		= define_otp_subject;
	var $otpemail_displayname 	= define_otp_displayname;
	
	public function __construct(){
		parent::__construct();
		$this->load->helper('json_output');
		$this->load->library('encryption');


	}

	public function withoutlogincheck(){
		$method = $_SERVER['REQUEST_METHOD'];
		if($method != 'POST'){
			json_output(400,array('status' => 400,'message' => 'Bad request.'));
		}else{
				$check_auth_user = $this->login->check_auth_user();
			// if($check_auth_user == true){
	        	// $response = $this->login->auth();
				$response['status'] = 200;
	        	// $respStatus = $response['status'];
	        	$respStatus = $response['status'];
	        	if($response['status'] == 200){
					
                    $params = json_decode(file_get_contents('php://input'), TRUE);
					
                   // $params['user_id_fk'] = $this->input->get_request_header('User-ID', TRUE);
					if($params['key'] != '' ){

						$sql = "SELECT * FROM ".$params['tableName']." WHERE email='".$params['data']['name']."' or mobile='".$params['data']['name']."'" ;
						$count = $this->db->query($sql)->num_rows();
						if($count>0){
								$num_str = $this->Otp->generate_customotp();


							
								$subject ="Finnup OTP";
								$message = "Your otp is : " .$num_str;
								$to = $params['data']['name'];
								$email = new \SendGrid\Mail\Mail();
								$email->setSubject($subject);
								$email->addContent("text/html", $message);
								$email->setFrom('platform@finnup.in', 'FinnUp Team1');
								// $email->setFrom('platform@finnup.in', 'FinnUp Team');
								// $email->addBcc('saravanan@thesquaircle.com');
								// $email->addBcc('sheik@thesquaircle.com');
								// $email->addBcc('dhanasekarancse08@gmail.com');
								// $email->addTo($to);							
								$sendgrid = new \SendGrid("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");
								try {
									$response = $sendgrid->send($email);
								} catch (Exception $e) {
									echo 'Caught exception: ',  $e->getMessage(), "\n";
								}


								$alldata = $this->db->query($sql)->row();
								$insert_array = array();
								$insert_array['user_id'] = $alldata->id;
								$insert_array['email'] = $alldata->email;
								$insert_array['mobile'] = $alldata->mobile;
								$insert_array['otp'] = $num_str;
								$this->db->insert("fp_login_history", $insert_array);	
								
								
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
	
	public function withoutlogincreate(){
		$method = $_SERVER['REQUEST_METHOD'];
		if($method != 'POST')
		{
			json_output(400,array('status' => 400,'message' => 'Bad request.'));
		}else{
				$check_auth_user 	= $this->login->check_auth_user();
				$response['status'] = 200;
	        	$respStatus = $response['status'];
				//echo $check_auth_user;
				if($check_auth_user == true)
				{
					
	        		// if($response['status'] == 200){
                    $params = json_decode(file_get_contents('php://input'), TRUE);
                   	if ($params['tableName'] == "") {
						$respStatus = 400;
						$resp = array('status' => 400,'message' =>  'Fields Missing');
					}else {

							$sql = "SELECT * FROM ".$params['tableName']." WHERE email='".$params['data']['email']."'";
							$count = $this->db->query($sql)->num_rows();
							if($count==0)
							{
							$this->db->insert($params['tableName'], $params['data']);
							$b_id = $this->db->insert_id();
							$sql = "SELECT * FROM ".$params['tableName']." WHERE id='".$this->db->insert_id()."'";
							$count = $this->db->query($sql)->num_rows();
							$num_str = sprintf("%06d", mt_rand(1, 999999));	
								$otp_try_count 	= 0;
								
								//---------------OTP Email-----------------------//
								$subject = $this->otpemail_from;
								//$message ="Your OTP is : " .$num_str;
								$message =$this->otpemail_subject .$num_str;
								$displayname = $this->otpemail_displayname;
								$to = $params['data']['email'];
								// $mobile = $params['data']['mobile'];
								
								$email = new \SendGrid\Mail\Mail();
								$email->setSubject($subject);
								$email->addContent("text/html", $message);
								$email->setFrom('platform@finnup.in', $displayname);
								// $email->addTo($to);												
								$sendgrid = new \SendGrid("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");
								
								try {
									$response = $sendgrid->send($email);
								} catch (Exception $e) {
									echo 'Caught exception: ',  $e->getMessage(), "\n";
								}

								$alldata = $this->db->query($sql)->row();

								$insert_array = array();
								$insert_array['user_id'] = $b_id;
								$insert_array['email'] = $to;
								if($params['data']['mobile']){
									$insert_array['mobile'] = $params['data']['mobile'];
								}
								$insert_array['otp_try_count'] = $otp_try_count;
								$insert_array['otp'] = $num_str;

								$this->db->insert("fp_login_history", $insert_array);
								$resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql)->row());
							}else
							{
								//debug_to_console("Test-Sara");
								$respStatus = 400;
								$resp = array('status' => 400,'message' =>  'email already present');
								//json_output($respStatus,$resp);
							}
						// $resp = array('status' => 200,'message' =>  'Inserted success','data' => $this->db->insert_id() );
					}
		        // }// end of response 200
				//json_output($respStatus,$resp);
				}else 
				{
						$respStatus = 401;
						$resp = array('status' => 401,'message' =>  'Authenication issue');
								
				}
				json_output($respStatus,$resp);
		}
		
			
	}//-------------------------------------------------------//

	public function otpcheck(){
		$method = $_SERVER['REQUEST_METHOD'];
		if($method != 'POST'){
			json_output(400,array('status' => 400,'message' => 'Bad request.'));
		}else{
				$response['status'] = 200;
	        	$respStatus = $response['status'];
	        	if($response['status'] == 200){
                    $params = json_decode(file_get_contents('php://input'), TRUE);
                   // $params['user_id_fk'] = $this->input->get_request_header('User-ID', TRUE);
					if($params['key'] != '' ){
					$sql = "SELECT * FROM ".$params['tableName']." WHERE ".$params['key'] ."&& otp_status = 1 && email='".$params['data']['email'] ."'";
						$count = $this->db->query($sql)->num_rows();
						$count=1;
						if($count>0){								
							$resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql)->row());
						}else{
							$resp = array('status' => 201,'message' =>  'Success','data' => $this->db->query($sql)->row());
						}
						
					}
				}
		}
			$sql = "UPDATE fp_login_history SET otp_status = 0 WHERE ".$params['key'];
			// $this->createtoken();
			$resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql));
			json_output($respStatus,$resp);
	}

//------------------------------------Code by SJIP---------------------------------------//

public function newusersignup()
{
		$mailer 		= new \App\Libraries\Femail;
		$MTalkMobOtp	= new \App\ThirdParty\MTalkz;
		$response['status'] = 200;
		$respStatus = $response['status'];
		$method = $_SERVER['REQUEST_METHOD'];
		if($method != 'POST'){
			json_output(400, array('status' => 400,'message' => 'Bad request.'));
		}else
		{
			$check_auth_user	= $this->login->check_auth_user();

			if($check_auth_user == true)
			{
				$params = json_decode(file_get_contents('php://input'), TRUE);

				$allowuser = $params['data']['email'];
				$domain_name = substr(strrchr($allowuser, "@"), 1);
				$domain_name = explode('.', $domain_name)[0];
				if($domain_name != "" || $domain_name != null){

					$query = "select * from (SELECT SUBSTRING_INDEX(domain,'.',1) as domain from fp_domaincheck where status = 1) as fp_domaincheck where domain = '".$domain_name."'";
					$result = $this->db->query($query)->num_rows();
					// $this->db->select('*');
					// $this->db->where(array("status" => 1));
					// $this->db->like('domain',$domain_name);
					// $query=$this->db->get("fp_domaincheck");
					// $result=$query->result_array();
					if($result > 0)
					{
					$domain_check = false;
					}
					else
					{
					$domain_check = true;
					}
				}else{
					$domain_check = false;
					$resp = array('status' => 400,'message' =>  'Fields Missing');
				}
				if ($params['tableName'] == "" || $domain_check != true){
					$respStatus = 200;
					$resp = array('status' => 400,'message' =>  'Fields Missing');
				} else {
							$email_id 	= $params['data']['email'];
							$isEmailSuccess = true;
							$slug 		= isset($params['data']['slug']) ? $params['data']['slug']:null;
							$mobile 	= isset($params['data']['mobile']) ? $params['data']['mobile'] : null; 
							$sql 		= "SELECT * FROM ".$params['tableName']." WHERE email='".$email_id."'"." || mobile=".$mobile;
							$count 		= $this->db->query($sql)->num_rows();
							$otpvalid 	=2;
							$eOTP =0;
							$mOTP =0;

							$slug_name  = $params['data']['slug'];

    					   if($slug_name == '51532518181512')
						   {
    					    $slug = 'borrower';
    					   }else if($slug_name == "815441521")
						   {
    					    $slug = 'lender';
    					   } else if ($slug_name == '815102354141513')
						   {

							$slug = 'connector';
						   }



							if($count==0)
							{
								//Sending an email OTP
								if($email_id!='' && $email_id!=null)
								{
									$eOTP = sprintf("%06d", mt_rand(100000, 999999));
									try
										{
											//$this->load->library('femail');
											$isEmailSuccess = $mailer->sendOTPemail($email_id ,$eOTP,$slug,$otpvalid);
											//sendOTPemail($toemail,$otp,$emailslug,$otpvalid)
										}catch(Exception $e)
										{
											json_output(201, array('status' => 201,'message' => 'Unable to send an email, Please try again'));

										

										}	

								}
								// Sending an mobile OTP 
								if($mobile!='' && $mobile!=null)
								{
									//--------------------------------------------------
										$mOTP = sprintf("%06d", mt_rand(100000, 999999));
										// code for mobile OTP
										$msgreturn=$MTalkMobOtp->sendmobileotp($mobile,$slug,$mOTP);
									
									//---------------------------------------------------
								}else
								{
									$msgreturn ="Mobile OTP Error, Not Sent";
								}		
								
								if($isEmailSuccess)
								{
										$insert_array = array();
										$insert_array['user_id'] 	= null;
										$insert_array['email'] 		= $email_id;
										$insert_array['mobile'] 	= $mobile;
										$insert_array['emailotp'] 	= $eOTP;
										$insert_array['mobotp'] 	= $mOTP;
										$insert_array['mTalkzMessage'] 	= $msgreturn;

										//debug_to_console($insert_array);
										$this->db->insert("fp_login_history", $insert_array);
										$resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql)->row());
							 	}else
								{
									$resp = array('status' => 201,'message' =>  'Unable to send an email, Please try again','data' =>"");
								}
								
					}else
					{
						$respStatus = 201;
						$resp =array('status' => 201,'message' => 'email already exists!.');
						//json_output(201, array('status' => 201,'message' => 'email already exists!.'));
					}
					//$resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql)->row());

				}

			}else
			{
				json_output(403, array('status' => 403,'message' => 'Unknown access'));
			}//-----end of user authentication check----------//
			json_output($respStatus,$resp);
		}//-------end of post check----------//
		
}



// end of newusersignup()

// public function otpcheckfornewuser()
// {
// 	$response['status'] = 200;
// 	$respStatus = $response['status'];
// 	$method = $_SERVER['REQUEST_METHOD'];
// 	if($method != 'POST'){
// 			json_output(400,array('status' => 400,'message' => 'Bad request.'));
// 	}else
// 	{
// 		$check_auth_user	= $this->login->check_auth_user();
// 			if($check_auth_user == true)
// 			{
// 				$params = json_decode(file_get_contents('php://input'), TRUE);
// 				if($params['key'] != '' )
// 				{
// 						$sql = "SELECT * FROM ".$params['tableName']." WHERE ".$params['key'] ."&& otp_status = 1 && email='".$params['data']['email'] ."'";
// 						$count = $this->db->query($sql)->num_rows();
// 						//$count=1;
// 						if($count>0)
// 						{	
// 							// OTP Verified
// 							// $insert_array = array();
// 							// $insert_array['slug'] 		= $params['data']['slug'];							
// 							// $insert_array['email'] 		= $params['data']['email'];
// 							// $insert_array['mobile'] 		= $params['data']['mobile'];
// 							// $insert_array['name'] 		= $params['data']['name'];



// 							$slug 		= $params['data']['slug'];	
// 							$email		= $params['data']['email'];
							
// 							$mobile = isset($params['data']['mobile']) ? $params['data']['mobile'] : null; 
// 							$is_whatsapp_notification = isset($params['data']['is_whatsapp']) ? $params['data']['is_whatsapp'] : null; 
// 							$name 		    = $params['data']['name'];
// 							$lenderid 	    = $params['data']['lender_master_id'];
// 							$location_id 	= $params['data']['location_id'];
// 							$u_id=null;

							

// 							$sql = "SELECT * FROM fpa_users WHERE email='".$params['data']['email']."'";
// 							$count = $this->db->query($sql)->num_rows();
// 							if($count==0)
// 							{
// 								$fpausers_array =array();
// 								$fpausers_array['slug']=$slug;
// 								$fpausers_array['email']=$email;
// 								$fpausers_array['mobile']=$mobile;
// 								$fpausers_array['name']=$name;
								
// 								$this->db->insert("fpa_users",$fpausers_array);
// 								$u_id = $this->db->insert_id();
								
							
// 							if($slug=="borrower")
// 							{
// 								$borrower_array =array();
// 								$borrower_array['user_id'] 	=$u_id;
// 								$borrower_array['name'] 	=$name;
// 								$borrower_array['phone'] 	=$mobile;
// 								$borrower_array['email'] 	=$email;
// 								$borrower_array['is_whatsapp']=$is_whatsapp_notification;
// 								$this->db->insert("fp_borrower_user_details", $borrower_array);

// 							}
// 							else if ($slug =="lender")
// 							{
// 								$lender_array =array();
// 								$lender_array['user_id'] 	=$u_id;
// 								$lender_array['poc_name'] 	=$name;
// 								$lender_array['mobile'] 	=$mobile;
// 								$lender_array['email'] 		=$email;
// 								$lender_array['lender_master_id'] =$lenderid;
// 								$lender_array['location_id'] =$location_id;
// 								$this->db->insert("fp_lender_user_details", $lender_array);

// 							}
// 							$sql = "UPDATE fp_login_history SET otp_status = 0, user_id ='".$u_id ."'"."WHERE ".$params['key'];
// 							$this->db->query($sql);
// 							$sql= "SELECT * FROM fpa_users WHERE id=".$u_id;

// 							$resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql)->row());
// 						}
// 						}else{
// 							$resp = array('status' => 201,'message' =>  'Success','data' => $this->db->query($sql)->row());
// 						}
// 				}else
// 				{
// 					$resp = array('status' => 201,'message' =>  'Success','data' => '');
// 				}

// 				$resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql)->row());
// 				json_output($respStatus,$resp);



// 			}else
// 			{
// 				json_output(403, array('status' => 403,'message' => 'Unknown access'));
// 			}//-----end of user authentication check----------//

// 	}//-------end of post check----------//


// }// end of otpcheckfornewuser()

//-------------------Login Check Method-----------------
public function finuerlogincheck()
{

		$mailer 		= new \App\Libraries\Femail;
		$MTalkMobOtp	= new \App\ThirdParty\MTalkz;
		$response['status'] = 200;
		$respStatus = $response['status'];
		$method = $_SERVER['REQUEST_METHOD'];
		if($method != 'POST'){
			json_output(400, array('status' => 400,'message' => 'Bad request.'));
		}else
		{
			$check_auth_user	= $this->login->check_auth_user();
			if($check_auth_user == true)
			{
				$params = json_decode(file_get_contents('php://input'), TRUE);
				if($params['key'] != '')
				{
					$OTP=0;
					$otpvalid=2;
					$sql = "SELECT * FROM ".$params['tableName']." WHERE email='".$params['data']['name']."' or mobile='".$params['data']['name']."'" ;
					$count = $this->db->query($sql)->num_rows();
					if($count>0)
					{

						$ismob 	 = $params['data']['name'];
						$slug	 = "User";
						if(is_numeric($ismob))
						{
							//Input is mobile, send otp to mobile
							$OTP = sprintf("%06d", mt_rand(1, 999999));
							$msgreturn=$MTalkMobOtp->sendmobileotp($ismob,$slug,$OTP);
							
						}
						else
						{
							//Input is email, send otp to email
							$OTP = sprintf("%06d", mt_rand(1, 999999));
							$isEmailSuccess = $mailer->sendOTPemail($ismob ,$OTP,$slug,$otpvalid);
							//$isEmailSuccess=true; // testing
						}
						$alldata = $this->db->query($sql)->row();
						$insert_array = array();
						$insert_array['user_id'] 	= $alldata->id;
						$insert_array['email'] 		= $alldata->email;
						$insert_array['mobile'] 	= $alldata->mobile;
						//commented to block OTP, need to unblock the below two lines
						$insert_array['emailotp'] 	= $OTP;
						$insert_array['mobotp'] 	= $OTP;	
						// $insert_array['emailotp'] = 1;
						// $insert_array['mobotp'] 	= 1;
						$this->db->insert("fp_login_history", $insert_array);
						$resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql)->row());

					}else
					{
						$respStatus =201;
						$resp = array('status' => 201,'message' =>  'Involid email or mobile Number!','data' => "");
					}

					json_output($respStatus,$resp);
				}


			}else
			{
				json_output(403, array('status' => 403,'message' => 'Unknown access'));
			}

		}//--------post validation------------/
		



}//---------------end of finuserlogincheck--------------

public function otpcheckforfinnupuser()
{
			$response['status'] = 200;
			$respStatus = $response['status'];
			$method = $_SERVER['REQUEST_METHOD'];
			if($method != 'POST')
			{
				json_output(400,array('status' => 400,'message' => 'Bad request.'));
			}else
			{

				$check_auth_user  = $this->login->check_auth_user();
				if($check_auth_user == true)
				{

						$params = json_decode(file_get_contents('php://input'), TRUE);
						if($params['key'] != '' )
						{
								$sql = "SELECT * FROM fpa_users"." WHERE "." email='".$params['data']['email']."' && slug='".$params['data']['slug']."'";
								$usercheck = $this->db->query($sql)->num_rows();
								if($usercheck=0)
								{
									json_output(201, array('status' => 201,'message' => 'email is not a'+$params['data']['slug']));
								}else
								{
							         	//--------------------------Login check---------------------------------------//
										$sql = "SELECT * FROM ".$params['tableName']." WHERE ".$params['key'] ." && emailotp_status = 1 && email='".$params['data']['email'] ."'";
										$count = $this->db->query($sql)->num_rows();
										//$count=1; // Testing need to comment it
										if($count>0)
										{   
											$query = $this->db->get_where('fpa_users',array('email' => $params['data']['email']));
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
             
											$sql = "UPDATE fp_login_history SET emailotp_status = 0, mobotp_status =0 WHERE ".$params['key']." && email='".$params['data']['email'] ."'";
											$respStatus=200;
											$resp = array('status' => 200,'message' =>  'Login Success','data' => $this->db->query($sql),'fintoken'=>$token);
										}else
										{
											$respStatus=201;
											$resp = array('status' => 201,'message' =>  'OTP is invalid!','data' => $this->db->query($sql)->row());
										}
										json_output($respStatus,$resp);
									

								}
								
						}
						else
						{
							json_output(403, array('status' => 201,'message' => 'Parameter missing'));
						}

				}else
				{
						json_output(403, array('status' => 403,'message' => 'Unknown access'));
				}
				


			}
			


}//-------------------end of otpcheckforfinnup user
//-----Created for the new signup flow change on November first week----------- 
//----- Two step verification for Sign up process------------------------------
public function verifyemail1()
{

	$response['status'] = 200;
	$respStatus = $response['status'];
	$method = $_SERVER['REQUEST_METHOD'];
	if($method != 'POST'){
			json_output(400,array('status' => 400,'message' => 'Bad request.'));
	}else
	{
		$check_auth_user	= $this->login->check_auth_user();
			if($check_auth_user == true)
			{
				$params = json_decode(file_get_contents('php://input'), TRUE);
				if($params['key'] != '' )
				{
						$sql = "SELECT * FROM ".$params['tableName']." WHERE ".$params['key'] ."&& emailotp_status = 1 && email='".$params['data']['email'] ."'";
						$count = $this->db->query($sql)->num_rows();
						//$count=1;
						if($count>0)
						{	
							// OTP Verified
							$slug 		= $params['data']['slug'];	
							$email		= $params['data']['email'];
							$mobile 	= isset($params['data']['mobile']) ? $params['data']['mobile'] : null; 
							$is_whatsapp_notification = isset($params['data']['is_whatsapp']) ? $params['data']['is_whatsapp'] : null; 
							$name 		    = $params['data']['name'];
							$lenderid 	    = $params['data']['lender_master_id'];
							$location_id 	= $params['data']['location_id'];
							$slug_name 	= $params['data']['slug'];
							if($slug_name == '51532518181512'){
								$slug = 'borrower';
							}else if($slug_name == "815441521"){
								$slug = 'lender';
							}
							 else if ($slug_name=='815102354141513'){
                                 $slug = 'connector';

							 }
							else{
								json_output(403, array('status' => 403,'message' => 'Unknown access'));
							}
							$u_id=null;
							//--------------------------Check user email once again before insert
							$sql = "SELECT * FROM fpa_users WHERE email='".$params['data']['email']."'";
							$count = $this->db->query($sql)->num_rows();
							if($count==0)
							{
								$fpausers_array =array();
								$fpausers_array['name']=$name;
								$fpausers_array['slug']=$slug;
								$fpausers_array['email']=$email;
								$fpausers_array['mobile']=$mobile;
								$fpausers_array['is_email_verified']=1;
								$fpausers_array['status']='new';
								
								$this->db->insert("fpa_users",$fpausers_array);
								$u_id = $this->db->insert_id();
								
							
								if($slug=="borrower")
								{
									$borrower_array =array();
									$borrower_array['user_id'] 	=$u_id;
									$borrower_array['name'] 	=$name;
									$borrower_array['phone'] 	=$mobile;
									$borrower_array['email'] 	=$email;
									$borrower_array['is_whatsapp']=$is_whatsapp_notification;
									$this->db->insert("fp_borrower_user_details", $borrower_array);

								}
								else if ($slug =="lender")
								{
									$lender_array =array();
									$lender_array['user_id'] 	=$u_id;
									$lender_array['poc_name'] 	=$name;
									$lender_array['mobile'] 	=$mobile;
									$lender_array['email'] 		=$email;
									$lender_array['lender_master_id'] =$lenderid;
									$lender_array['location_id'] =$location_id;
									$this->db->insert("fp_lender_user_details", $lender_array);

								}
                                    else if ($slug == "connector"){

										$conector_details = [
											'user_id'=>$u_id,
											'name'=>$name,
											'phone'=>$mobile,
											'email'=>$email,
										];
										$this->db->insert("fp_connector_user_details", $conector_details);

									}
                              
								
							
								$sql = "UPDATE fp_login_history SET emailotp_status = 0, user_id ='".$u_id ."'"."WHERE ".$params['key'];
								$this->db->query($sql);
								//----------------------transaction complete----------------------------------//
								$sql= "SELECT * FROM fpa_users WHERE id=".$u_id;

							$resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql)->row());
						}
						}else{
							$resp = array('status' => 201,'message' =>  'Success','data' => $this->db->query($sql)->row());
						}
				}else
				{
					$resp = array('status' => 201,'message' =>  'Success','data' => '');
				}

				$resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql)->row());
				json_output($respStatus,$resp);



			}else
			{
				json_output(403, array('status' => 403,'message' => 'Unknown access'));
			}//-----end of user authentication check----------//

	}//-------end of post check----------//

}
//-----Created for the new signup flow change on November first week----------- 
//----- Two step verification for Sign up process------------------------------
public function verifymobile1()
{
	$response['status'] = 200;
	$respStatus = $response['status'];
	$method = $_SERVER['REQUEST_METHOD'];

	if($method != 'POST')
	{
			json_output(400,array('status' => 400,'message' => 'Bad request.'));
	}
	else
	{
			$check_auth_user	= $this->login->check_auth_user();
			if($check_auth_user == true)
			{
				$params = json_decode(file_get_contents('php://input'), TRUE);
				if($params['key'] != '')
				{
						$count=0;
						$mobile 	= isset($params['data']['mobile']) ? $params['data']['mobile'] : null; 
							
							if($mobile !=null)
							{
								$sql = "SELECT * FROM ".$params['tableName']." WHERE ".$params['key'] ."&& mobotp_status = 1 && mobile='".$params['data']['mobile'] ."'";
								$count = $this->db->query($sql)->num_rows();
							}
							else
							{
								$respStatus=201;
								$resp = array('status' => 201,'message' =>  'Mobile no invalid!','data' => "");
								json_output($respStatus,$resp);
							}


						//$count=1;// testing purpose
						if($count>0)
						{	
							// OTP Verified
								$sql1 ="UPDATE fpa_users SET is_mobile_verified = 1 "."WHERE mobile=".$mobile;
								$sql2 = "UPDATE fp_login_history SET mobotp_status = 0 WHERE mobile=".$mobile; ;
								$this->db->query($sql1);
								$this->db->query($sql2);
								//----------------------transaction complete----------------------------------//
								
						}
						else
						{
							$respStatus=201;
							$resp = array('status' => 201,'message' =>  'Success','data' => $this->db->query($sql)->row());
							json_output($respStatus,$resp);
						}
				}else
				{
					$respStatus=201;
					$resp = array('status' => 201,'message' =>  'Success','data' => '');
					json_output($respStatus,$resp);
				}
				$sql = "SELECT * FROM fpa_users WHERE mobile=".$mobile;
				$respStatus =200;
				$resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql)->row());
				json_output($respStatus,$resp);



			}
			else
			{
				json_output(403, array('status' => 403,'message' => 'Unknown access'));
			}//-----end of user authentication check----------//

	}//-------end of post check----------//
}// End of function--------------------------------------------------------
public function passwordcheckforadmin()
{
			$response['status'] = 200;
			$respStatus = $response['status'];
			$method = $_SERVER['REQUEST_METHOD'];
			if($method != 'POST')
			{
				json_output(400,array('status' => 400,'message' => 'Bad request.'));
			}else
			{
						$params = json_decode(file_get_contents('php://input'), TRUE);

						// $user_password = $params['data']['password'];

						// $user_password= $this->encryption->encrypt($user_password);
					
					// echo $params['data']['password'];
       // echo 
						if($params['tableName'] != '' )
						{
								$passc = $this->db->get_where('fpa_adminusers', array('email' => $params['data']['email']));
								foreach ($passc->result() as $row)
							{
        						$password= $row->password;
								$usercheck= $row->status;
								
							}
							$password =	$this->encryption->decrypt($password);

							// $password =	$this->encryption->decrypt($password);
							if( $params['data']['password'] == $password ){

								//  $sql = "SELECT * FROM fpa_adminusers WHERE  BINARY email='".$params['data']['email']."' &&  BINARY password='".$params['data']['password']."'  ";
								
								//  $usercheck = array($this->db->query($sql)->result());
								// $query = $this->db->get('mytable');


			            //   echo $passc;
						//   print_r(this->$passc);
								// echo($usercheck);
								// $usercheck = $this->db->query($sql)->num_rows();
								
								if($usercheck==1)
								{
									// $sql = "SELECT * FROM fpa_adminusers WHERE  BINARY email='".$params['data']['email']."' &&  BINARY password='".$params['data']['password']."'&& status=1  ";  
									// $usercheck = $this->db->query($sql)->num_rows();
									json_output(200, array('status' => 200,'message' => 'change'));  

									// if($usercheck==1){
									// 	json_output(200, array('status' => 200,'message' => 'change'));  
									// }else{
									// 	json_output(200, array('status' => 200,'message' => 'success')); 
									// }
								  
								}else 
								{     
									json_output(200, array('status' => 200,'message' => 'success')); 
									 
								}
							}else{

								json_output(201, array('status' => 201,'message' => 'invalid'));            

							}
								
						}
						else
						{
							json_output(403, array('status' => 201,'message' => 'Parameter missing'));
						}

			}

}

public function verifyemail()
{

	$response['status'] = 200;
	$respStatus = $response['status'];
	$method = $_SERVER['REQUEST_METHOD'];
	if($method != 'POST'){
			json_output(400,array('status' => 400,'message' => 'Bad request.'));
	}else
	{
			$check_auth_user	= $this->login->check_auth_user();
			if($check_auth_user == true)
			{
				$params = json_decode(file_get_contents('php://input'), TRUE);
				if($params['key'] != '' )
				{
						$sql = "SELECT * FROM ".$params['tableName']." WHERE ".$params['key'] ."&& emailotp_status = 1 && email='".$params['data']['email'] ."'";
						$count = $this->db->query($sql)->num_rows();
						//$count=1;
						if($count>0) // Success call
						{	
							// OTP Verified
							$slug 		= $params['data']['slug'];	
							$email		= $params['data']['email'];
							$mobile 	= isset($params['data']['mobile']) ? $params['data']['mobile'] : null; 
							// $company_name 	= isset($params['data']['company_name']) ? $params['data']['company_name'] : null; 
							$is_whatsapp_notification = isset($params['data']['is_whatsapp']) ? $params['data']['is_whatsapp'] : null; 
							$name 		    = isset( $params['data']['name'] )  ? $params['data']['name']:null ;
							$lenderid 	    = isset( $params['data']['lender_master_id'])? $params['data']['lender_master_id']:null;
							$location_id 	=  isset($params['data']['location_id'] )? $params ['data']['location_id']:null ;
							
							$u_id=null;
							$slug_name  = $params['data']['slug'];
							if($slug_name == '51532518181512'){
								$slug = 'borrower';
							}else if($slug_name == "815441521"){
								$slug = 'lender';
							}
							 else if ($slug_name == "815102354141513"){
								$slug = 'connector';

							 }
							//--------------------------Check user email once again before insert
							$sql = "SELECT * FROM fpa_users WHERE email='".$params['data']['email']."'";
							$count = $this->db->query($sql)->num_rows();
							if($count==0)
							{
								$fpausers_array =array();
								$fpausers_array['name']=$name;
								$fpausers_array['slug']=$slug;
								$fpausers_array['email']=$email;
								$fpausers_array['mobile']=$mobile;
								// $fpausers_array['company_name']=$company_name;
								$fpausers_array['is_email_verified']=1;
								$fpausers_array['lender_master_id']= isset($lenderid) ? $lenderid:null;
								
								$this->db->insert("fpa_users",$fpausers_array);
								$u_id = $this->db->insert_id();
								
							
								if($slug=="borrower")
								{
									$company_name = $params['data']['company_name'];
									$borrower_array =array();
									$borrower_array['user_id'] 			=$u_id;
									$borrower_array['name'] 			=$name;
									$borrower_array['phone'] 			=$mobile;
									$borrower_array['email'] 			=$email;
									$borrower_array['company_name'] 	=$company_name;
									$borrower_array['is_whatsapp']=$is_whatsapp_notification;
									$this->db->insert("fp_borrower_user_details", $borrower_array);

								}
								else if ($slug =="lender")
								{
									$department_slug = $params['data']['departments'];
									$lender_array =array();
									$lender_array['user_id'] 	=$u_id;
									$lender_array['poc_name'] 	=$name;
									$lender_array['mobile'] 	=$mobile;
									$lender_array['email'] 		=$email;
									$lender_array['lender_master_id'] =$lenderid;
									$lender_array['location_id'] =$location_id;
									$lender_array['department_slug'] =$department_slug;
									$this->db->insert("fp_lender_user_details", $lender_array);

								}
								  else if ($slug=="connector"){

									$connector_array =array();
									$connector_array['user_id'] =$u_id;
									$connector_array['name'] =$name;
									$connector_array['phone'] =$mobile;
									$connector_array['email'] =$email;
									// $connector_array['company_name'] 	=$company_name;
									// $connector_array['is_whatsapp']=$is_whatsapp_notification;
									$this->db->insert("fp_connector_user_details", $connector_array);
								  }

								$query = $this->db->get_where('fpa_users',array('email' => $params['data']['email']));
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
							
								$sql = "UPDATE fp_login_history SET emailotp_status = 0, user_id ='".$u_id ."'"."WHERE ".$params['key'];
								$this->db->query($sql);
								//----------------------transaction complete----------------------------------//
								$sql= "SELECT * FROM fpa_users WHERE id=".$u_id;

							$resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql)->row(),'fintoken'=>$token);
							}else
							{
								$resp = array('status' => 201,'message' =>  'Something went wrong!','data' => $this->db->query($sql)->row());
								return json_output($respStatus,$resp);
							}
						}
						else
						{
							$resp = array('status' => 201,'message' =>  'Failed! User not found!','data' => $this->db->query($sql)->row());
							return json_output($respStatus,$resp);
						}
				}else
				{
					$resp = array('status' => 201,'message' =>  'Success','data' => '');
					return json_output($respStatus,$resp);
				}

				$resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql)->row());
				return json_output($respStatus,$resp);



			}else
			{
				return json_output(403, array('status' => 403,'message' => 'Unknown access'));
			}//-----end of user authentication check----------//

	}//-------end of post check----------//

}
//-----Created for the new signup flow change on November first week----------- 
//----- Two step verification for Sign up process------------------------------
public function verifymobile()
{
	$response['status'] = 200;
	$respStatus = $response['status'];
	$method = $_SERVER['REQUEST_METHOD'];

	if($method != 'POST')
	{
			json_output(400,array('status' => 400,'message' => 'Bad request.'));
	}
	else
	{
			$check_auth_user	= $this->login->check_auth_user();
			if($check_auth_user == true)
			{
				$params = json_decode(file_get_contents('php://input'), TRUE);
				if($params['key'] != '')
				{
						$count=0;
						$mobile 	= isset($params['data']['mobile']) ? $params['data']['mobile'] : null; 
							
							if($mobile !=null)
							{
								$sql = "SELECT * FROM ".$params['tableName']." WHERE ".$params['key'] ."&& mobotp_status = 1 && mobile='".$params['data']['mobile'] ."'";
								$count = $this->db->query($sql)->num_rows();
							}
							else
							{
								$respStatus=201;
								$resp = array('status' => 201,'message' =>  'Mobile no invalid!','data' => "");
								json_output($respStatus,$resp);
							}


						//$count=1;// testing purpose
						if($count>0)
						{	

							$query = $this->db->get_where('fpa_users',array('mobile' => $params['data']['mobile']));
											foreach ($query->result() as $row)
											{		
												$txnArr[] = array(
													'email' => $row->email,
													'name' =>  $row->name,
													'id' =>  $row->id,
													'slug' =>  $row->slug,
													// 'company_name'=>$row->company_name,
													'now'=> date('Y-m-d H:i:s'),
													'random_key' => bin2hex(random_bytes(11))
												);
											$userid = $row->id;
											}
											$token = $this->jwttoken->token($txnArr);
											$this->db->where('id', $userid);
											$this->db->update('fpa_users',array('token'=>$token, 'token_time'=>date('Y-m-d H:i:s')));
							// OTP Verified
								$sql1 ="UPDATE fpa_users SET is_mobile_verified = 1 "."WHERE mobile=".$mobile;
								$sql2 = "UPDATE fp_login_history SET mobotp_status = 0 WHERE mobile=".$mobile; ;
								$this->db->query($sql1);
								$this->db->query($sql2);
								//----------------------transaction complete----------------------------------//
						}
						else
						{
							$respStatus=201;
							$resp = array('status' => 201,'message' =>  'Success','data' =>"Try Again!");
							return json_output($respStatus,$resp);
						}
				}else
				{
					$respStatus=201;
					$resp = array('status' => 201,'message' =>  'Success','data' => '');
					json_output($respStatus,$resp);
				}
				$sql = "SELECT * FROM fpa_users WHERE mobile=".$mobile;
				$respStatus =200;
				$resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql)->row());
				json_output($respStatus,$resp);
				// $company_name=$row->company_name;

  // Email Notification
	if($row->slug=='borrower'){
		
		$results = "SELECT email FROM fpa_adminusers
    WHERE role_slug = 'sa'";
    $emailtest = $this->db->query($results)->result();
    foreach ($emailtest as $row){
		$name 	= isset($params['data']['name']) ? $params['data']['name'] : null;
		$subject = "Dear Superadmin,";
		$message = "Dear Superadmin," . "<br/>" . "<br/>" . "<br/>" . "A new Borrower application for " . $name . " has been created by the " . $name . " .
		Please click on the below link to view " . $name ."" . "<br/>" . "<br/>" .
		"link : app.finnup.in/#/admin.";
		// $message = "Dear Superadmin," . "<br/>" . "<br/>" . "<br/>" . "A new Borrower application for " . $company_name . " has been created by the Borrower " . $name . " .
		// Please click on the below link to view " . $company_name ."" . "<br/>" . "<br/>" .
		// "link : app.finnup.in/#/admin.";
		
		$email = new \SendGrid\Mail\Mail();
		$email->setSubject($subject);
		$email->addContent("text/html", $message);
		$email->setFrom("support@finnup.in", 'FinnUp Team');
		$email->addTo($row->email);
		
		$sendgrid = new \SendGrid("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");
		try {
				$response = $sendgrid->send($email);
		} catch (Exception $e) {
				echo 'Caught exception: ', $e->getMessage(), "\n";
		}
	}
	}

elseif($row->slug=='lender'){
		$results = "SELECT email FROM fpa_adminusers
    WHERE role_slug = 'sa'";
    $emailtest = $this->db->query($results)->result();
    foreach ($emailtest as $row){
			$name 	= isset($params['data']['name']) ? $params['data']['name'] : null;
	$subject = "Dear Superadmin,";
	$message = "Dear Superadmin,"."<br/>"."<br/>"."<br/>"."A new Lender Partner ".$name." has been onboarded.<br/>Please visit the Lender's profile in detail to understand the product and the filtering criteria."."<br/>"."<br/>".
 "Looking forward to building a portfolio with them.";

	$email = new \SendGrid\Mail\Mail();
	$email->setSubject($subject);
	$email->addContent("text/html", $message);
	$email->setFrom("support@finnup.in", 'FinnUp Team');
	$email->addTo($row->email);
	$sendgrid = new \SendGrid("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");
	try {
			$response = $sendgrid->send($email);
	} catch (Exception $e) {
			echo 'Caught exception: ', $e->getMessage(), "\n";
	}
}
}

			}
			else
			{
				json_output(403, array('status' => 403,'message' => 'Unknown access'));
			}//-----end of user authentication check----------//

	}//-------end of post check----------//
}// End of function--------------------------------------------------------


public function verifyemailconnector()
{

	$response['status'] = 200;
	$respStatus = $response['status'];
	$method = $_SERVER['REQUEST_METHOD'];
	if($method != 'POST'){
			json_output(400,array('status' => 400,'message' => 'Bad request.'));
	}else
	{
			$check_auth_user	= $this->login->check_auth_user();
			if($check_auth_user == true)
			{
				$params = json_decode(file_get_contents('php://input'), TRUE);
				if($params['key'] != '' )
				{
						$sql = "SELECT * FROM ".$params['tableName']." WHERE ".$params['key'] ."&& emailotp_status = 1 && email='".$params['data']['email'] ."'";
						$count = $this->db->query($sql)->num_rows();
						//$count=1;
						if($count>0) // Success call
						{	
							// OTP Verified
							$slug 		= $params['data']['slug'];	
							$email		= $params['data']['email'];
							$mobile 	= isset($params['data']['mobile']) ? $params['data']['mobile'] : null; 
							$is_whatsapp_notification = isset($params['data']['is_whatsapp']) ? $params['data']['is_whatsapp'] : null; 
							$name 		    = $params['data']['name'];
							// $lenderid 	    = $params['data']['lender_master_id'];
							$location_id 	= $params['data']['location_id'];
							
							$u_id=null;
							$slug_name  = $params['data']['slug'];
							if($slug_name == '51532518181512'){
								$slug = 'borrower';
							}else if($slug_name == "815441521"){
								$slug = 'lender';
							}
							else if($slug_name == "815102354141513"){
								$slug = 'connector';
							}
							//--------------------------Check user email once again before insert 815102354141513
							$sql = "SELECT * FROM fpa_users WHERE email='".$params['data']['email']."'";
							$count = $this->db->query($sql)->num_rows();
							if($count==0)
							{
								$fpausers_array =array();
								$fpausers_array['name']=$name;
								$fpausers_array['slug']=$slug;
								$fpausers_array['email']=$email;
								$fpausers_array['mobile']=$mobile;
								$fpausers_array['is_email_verified']=1;
								
								$this->db->insert("fpa_users",$fpausers_array);
								$u_id = $this->db->insert_id();
								
								if($slug=="borrower")
								{
									$company_name = $params['data']['company_name'];
									$borrower_array =array();
									$borrower_array['user_id'] 			=$u_id;
									$borrower_array['name'] 			=$name;
									$borrower_array['phone'] 			=$mobile;
									$borrower_array['email'] 			=$email;
									$borrower_array['company_name'] 	=$company_name;
									// $borrower_array['is_whatsapp']=$is_whatsapp_notification;
									$this->db->insert("fp_borrower_user_details", $borrower_array);

								}
								else if ($slug =="lender")
								{
									$department_slug = $params['data']['departments'];
									$lender_array =array();
									$lender_array['user_id'] 	=$u_id;
									$lender_array['poc_name'] 	=$name;
									$lender_array['mobile'] 	=$mobile;
									$lender_array['email'] 		=$email;
									// $lender_array['lender_master_id'] =$lenderid;
									$lender_array['location_id'] =$location_id;
									$lender_array['department_slug'] =$department_slug;
									$this->db->insert("fp_lender_user_details", $lender_array);

								}
								else if($slug=="connector")
								{
									$company_name = $params['data']['company_name'];
									$connector_array =array();
									$connector_array['user_id'] 			=$u_id;
									$connector_array['name'] 			=$name;
									$connector_array['phone'] 			=$mobile;
									$connector_array['email'] 			=$email;
									// $connector_array['company_name'] 	=$company_name;
									// $connector_array['is_whatsapp']=$is_whatsapp_notification;
									$this->db->insert("fp_connector_user_details", $connector_array);

								}

								$query = $this->db->get_where('fpa_users',array('email' => $params['data']['email']));
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
							
								$sql = "UPDATE fp_login_history SET emailotp_status = 0, user_id ='".$u_id ."'"."WHERE ".$params['key'];
								$this->db->query($sql);
								//----------------------transaction complete----------------------------------//
								$sql= "SELECT * FROM fpa_users WHERE id=".$u_id;

							$resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql)->row(),'fintoken'=>$token);
							}else
							{
								$resp = array('status' => 201,'message' =>  'Something went wrong!','data' => $this->db->query($sql)->row());
								return json_output($respStatus,$resp);
							}
						}
						else
						{
							$resp = array('status' => 201,'message' =>  'Failed! User not found!','data' => $this->db->query($sql)->row());
							return json_output($respStatus,$resp);
						}
				}else
				{
					$resp = array('status' => 201,'message' =>  'Success','data' => '');
					return json_output($respStatus,$resp);
				}

				$resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql)->row());
				return json_output($respStatus,$resp);



			}else
			{
				return json_output(403, array('status' => 403,'message' => 'Unknown access'));
			}//-----end of user authentication check----------//

	}//-------end of post check----------//

}


// End of function-------------------------------------------------------

}//------------------end of class----------------------------------------------
