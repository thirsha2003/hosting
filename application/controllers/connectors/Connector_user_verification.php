<?php

header('Access-Control-Allow-Origin: *'); //for allow any domain, insecure
header('Access-Control-Allow-Headers: *'); //for allow any headers, insecure
header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE'); //method allowed
header("HTTP/1.1 200 OK");

defined('BASEPATH') or exit('No direct script access allowed');
include APPPATH . 'ThirdParty/sendgrid-php/sendgrid-php.php';
include APPPATH . 'ThirdParty/mTalkz.php';
include APPPATH . 'libraries/Femail.php';
// include APPPATH . 'controllers/BorrowerAuth.php';
// include APPPATH . 'ThirdParty/probeapi.php';

//-----------------------------------------------------------------------------
//-----------------------------------------------------------------------------
//-----------------------------------------------------------------------------
class Connector_user_verification extends CI_Controller
{

    public $otpemail_from = define_otp_fromemail;
    public $otpemail_subject = define_otp_subject;
    public $otpemail_displayname = define_otp_displayname;

    public function __construct()
      {
        parent::__construct();
        $this->load->helper('json_output');
        $this->load->library('encryption');

      }

    
      public function newusersignup()
     {
        $mailer = new \App\Libraries\Femail;
        $MTalkMobOtp = new \App\ThirdParty\MTalkz;
		// $isEmailSuccess = $mailer->sendOTPemail("parthibangnc51@gmail.com", "878787", "connector", "2");
		// print_r($isEmailSuccess);
		// exit();
		
        $response['status'] = 200;
        $respStatus = $response['status'];
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_user = $this->login->check_auth_user();

            if ($check_auth_user == true) {
                $params = json_decode(file_get_contents('php://input'), true);
                if ($params['tableName'] == "") {
                    $respStatus = 400;
                    $resp = array('status' => 400, 'message' => 'Fields Missing');
                } else {
                    $email_id = $params['data']['email'];
                    $isEmailSuccess = true;
                    $slug = isset($params['data']['slug']) ? $params['data']['slug'] : null;
                    $mobile = isset($params['data']['mobile']) ? $params['data']['mobile'] : null;
                    $connector = isset($params['data']['company_name']) ? $params['data']['company_name'] : null;
                    $sql = "SELECT * FROM " . $params['tableName'] . " WHERE email='" . $email_id . "'" . " || mobile=" . $mobile;
                    $count = $this->db->query($sql)->num_rows();
                    $otpvalid = 2;
                    $eOTP = 0;
                    $mOTP = 0;

                    $slug_name = $params['data']['slug'];
                    if ($slug_name == "815102354141513") {
                        $slug = 'connector';
                    } 


                    if ($count == 0) {
                        //Sending an email OTP
                        if ($email_id != '' && $email_id != null) {
                            $eOTP = sprintf("%06d", mt_rand(100000, 999999));
                            try
                            {
                                //$this->load->library('femail');
                                $isEmailSuccess = $mailer->sendOTPemail($email_id, $eOTP, $slug, $otpvalid);
                                //sendOTPemail($toemail,$otp,$emailslug,$otpvalid)
                            } catch (Exception $e) {
                                json_output(201, array('status' => 201, 'message' => 'Unable to send an email, Please try again'));
                            }

                        }
                        // Sending an mobile OTP
                        if ($mobile != '' && $mobile != null) {
                            //--------------------------------------------------

                            $mOTP = sprintf("%06d", mt_rand(100000, 999999));

                            // code for mobile OTP
                            $msgreturn = $MTalkMobOtp->sendmobileotp($mobile, $slug, $mOTP);

                            //---------------------------------------------------
                        } else {
                            $msgreturn = "Mobile OTP Error, Not Sent";
                        }

                        if ($isEmailSuccess) {
                            $insert_array = array();
                            $insert_array['connector_id'] = null;
                            $insert_array['email'] = $email_id;
                            $insert_array['mobile'] = $mobile;
                            $insert_array['emailotp'] = $eOTP;
                            $insert_array['mobotp'] = $mOTP;
                            $insert_array['mTalkzMessage'] = $msgreturn;

                            //debug_to_console($insert_array);
                            $this->db->insert("fp_connector_login_history", $insert_array);
                            $resp = array('status' => 200, 'message' => 'Success', 'data' => $this->db->query($sql)->row());
                        } else {
                            $resp = array('status' => 201, 'message' => 'Unable to send an email, Please try again', 'data' => "");
                        }

                    } else {
                        $respStatus = 201;
                        $resp = array('status' => 201, 'message' => 'email already exists!.');
                        //json_output(201, array('status' => 201,'message' => 'email already exists!.'));
                    }
                    //$resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql)->row());

                }

            } else {
                json_output(403, array('status' => 403, 'message' => 'Unknown access'));
            } //-----end of user authentication check----------//
            json_output($respStatus, $resp);
        } //-------end of post check----------//

    }


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

						if($count>0)
						{	

							$query = $this->db->get_where('fp_connector_users',array('mobile' => $params['data']['mobile']));
											foreach ($query->result() as $row)
											{		
												$txnArr[] = array(
													'email' => $row->email,
													'name' =>  $row->name,
													'id' =>  $row->id,
													'slug' =>  $row->slug,
													'company_name' => $row->company_name,
													'now'=> date('Y-m-d H:i:s'),
													'random_key' => bin2hex(random_bytes(11))
												);
											$userid = $row->id;
											}
											$token = $this->jwttoken->token($txnArr);
											$this->db->where('id', $userid);
											$this->db->update('fp_connector_users',array('token'=>$token, 'token_time'=>date('Y-m-d H:i:s')));
							// OTP Verified
								$sql1 ="UPDATE fp_connector_users SET is_mobile_verified = 1 "."WHERE mobile=".$mobile;
								$sql2 = "UPDATE fp_connector_login_history SET mobotp_status = 0 WHERE mobile=".$mobile; ;
								$this->db->query($sql1);
								$this->db->query($sql2);
								//----------------------transaction complete----------------------------------//
								
						}
						else
						{
							$respStatus=201;
							$resp = array('status' => 201,'message' =>  'Success','data' => $this->db->query($sql)->row(),'fintoken'=>$token);
							return json_output($respStatus,$resp);
						}
				}else
				{
					$respStatus=201;
					$resp = array('status' => 201,'message' =>  'Success','data' => '');
					json_output($respStatus,$resp);
				}
				$sql = "SELECT * FROM fp_connector_users WHERE mobile=".$mobile;
				$respStatus =200;
				$resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql)->row());
				json_output($respStatus,$resp);

			}
			else
			{
				json_output(403, array('status' => 403,'message' => 'Unknown access'));
			}//-----end of user authentication check----------//

	}//-------end of post check----------//
  }
  
    // ------------- verifyemail for connector---------------------
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
							  $connector = isset($params['data']['company_name']) ? $params['data']['company_name'] : null;
							  $is_whatsapp_notification = isset($params['data']['is_whatsapp']) ? $params['data']['is_whatsapp'] : null; 
							  $name 		    = $params['data']['name'];
							  // $lenderid 	    = $params['data']['lender_master_id'];
							  $location_id 	= $params['data']['location_id'];
							  
							  $u_id=null;
							  $slug_name  = $params['data']['slug'];
							   if($slug_name == "815102354141513"){
								  $slug = 'connector';
							  }
							  //--------------------------Check user email once again before insert 815102354141513
							  $sql = "SELECT * FROM fp_connector_users WHERE email='".$params['data']['email']."'";
							  $count = $this->db->query($sql)->num_rows();
							  if($count==0)
							  {
								  $fpausers_array =array();
								  $fpausers_array['name']=$name;
								  $fpausers_array['slug']=$slug;
								  $fpausers_array['email']=$email;
								  $fpausers_array['mobile']=$mobile;
								  $fpausers_array['company_name']=$connector;
								  $fpausers_array['is_email_verified']=1;
								  
								  $this->db->insert("fp_connector_users",$fpausers_array);
								  $u_id = $this->db->insert_id();
								  
								  
								  
								   if($slug=="connector")
								  {
									  $company_name = $params['data']['company_name'];
									  $connector_array =array();
									  $connector_array['user_id'] 			=$u_id;
									  $connector_array['name'] 			=$name;
									  $connector_array['phone'] 			=$mobile;
									  $connector_array['email'] 			=$email;
									//   $connector_array['company_name'] 			=$connector;
									  // $connector_array['company_name'] 	=$company_name;
									  // $connector_array['is_whatsapp']=$is_whatsapp_notification;
									  $this->db->insert("fp_connector_user_details", $connector_array);
  
								  }
  
								  $query = $this->db->get_where('fp_connector_users',array('email' => $params['data']['email']));
											  foreach ($query->result() as $row)
											  {		
												  $txnArr[] = array(
													  'email' => $row->email,
													  'name' =>  $row->name,
													  'id' =>  $row->id,
													  'slug' =>  $row->slug,
													  'company_name' =>  $row->company_name,
													  'now'=> date('Y-m-d H:i:s'),
													  'random_key' => bin2hex(random_bytes(11))
												  );
											  $userid = $row->id;
											  }
											  $token = $this->jwttoken->token($txnArr);
											  $this->db->where('id', $userid);
											  $this->db->update('fp_connector_users',array('token'=>$token, 'token_time'=>date('Y-m-d H:i:s')));
							  
								  $sql = "UPDATE fp_connector_login_history SET emailotp_status = 0, connector_id ='".$u_id ."'"."WHERE ".$params['key'];
								  $this->db->query($sql);
								  //----------------------transaction complete----------------------------------//
								  $sql= "SELECT * FROM fp_connector_users WHERE id=".$u_id;
  
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
  

  public function finuserlogincheck()
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
						  $insert_array['connector_id'] 	= $alldata->id;
						  $insert_array['email'] 		= $alldata->email;
						  $insert_array['mobile'] 	= $alldata->mobile;
						  //commented to block OTP, need to unblock the below two lines
						  $insert_array['emailotp'] 	= $OTP;
						  $insert_array['mobotp'] 	= $OTP;	
						  // $insert_array['emailotp'] = 1;
						  // $insert_array['mobotp'] 	= 1;
						  $this->db->insert("fp_connector_login_history", $insert_array);
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
								$sql = "SELECT * FROM fp_connector_users"." WHERE "." email='".$params['data']['email']."' && slug='".$params['data']['slug']."'";
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
											$query = $this->db->get_where('fp_connector_users',array('email' => $params['data']['email']));
											foreach ($query->result() as $row)
											{		
												$txnArr[] = array(
													'email' => $row->email,
													'name' =>  $row->name,
													'id' =>  $row->id,
													'slug' =>  $row->slug,
													'company_name' =>  $row->company_name,
													'now'=> date('Y-m-d H:i:s'),
													'random_key' => bin2hex(random_bytes(11))
												);
											$userid = $row->id;
											}
											$token = $this->jwttoken->token($txnArr);
											$this->db->where('id', $userid);
											$this->db->update('fp_connector_users',array('token'=>$token, 'token_time'=>date('Y-m-d H:i:s')));
             
											$sql = "UPDATE fp_connector_login_history SET emailotp_status = 0, mobotp_status =0 WHERE ".$params['key']." && email='".$params['data']['email'] ."'";
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

  
}  //------------------end of class----------------------------------------------
