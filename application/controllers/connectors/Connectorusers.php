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
class Connectorusers extends CI_Controller
{

        private $_iv = 'xb5wypRrVgl0rYm1';
	private $ciphering = "AES-128-CTR";
	private $key = "finnup";
	private $options = 0;	
        
                public $otpemail_from = define_otp_fromemail;
                public $otpemail_subject = define_otp_subject;
                public $otpemail_displayname = define_otp_displayname;

                public function __construct()
                {
                    parent::__construct();
                    $this->load->helper('json_output');   
                    $this->load->library('encryption');
                }//--------------------------------------------------------------------------------


		public function createpartner_manageteam1()
                {
                                $method     = $_SERVER['REQUEST_METHOD'];
                                if($method != 'POST')
                                {
                                        json_output(400,array('status' => 400,'message' => 'Bad request.'));
                                }
                                else
                                {
                                        //   $checkToken = $this->check_token();
                                        if(true)
                                        {
                                                $response['status']=200;
                                                $respStatus = $response['status'];
                                                $params = json_decode(file_get_contents('php://input'), TRUE);

                                                try
                                                {
                                               

                                                  
                                                        $name   = $params['data']['name'];
                                                        $email  = $params['data']['email'];
                                                        $phone  = $params['data']['mobile'];
                                                        $companyname  = isset($params['data']['company_name'])?$params['data']['company_name']:null;
                                                        // ($params['tableName'] == "fpa_adminusers") {
                                                                $simple_string = $params['data']['password'];
                                                                $iv_length = openssl_cipher_iv_length($this->ciphering);
                                                                $encryption = openssl_encrypt($simple_string, $this->ciphering,
                                                                    $this->key, $this->options, $this->_iv);
                                        
                                                                // $d_id = isset($params['data']['id']) ? $params['data']['id'] : "0";
                                        
                                                                $params['data']['password'] = $encryption;
                                                        // $password       = isset($params['data']['password'])?$params['data']['password']:null;
                                                        $role       = isset($params['data']['role_slug'])?$params['data']['role_slug']:null;
                                                        $partnerid      = isset($params['data']['partner_id'])?$params['data']['partner_id']:null;
                                                        $parent_id      = isset($params['data']['parent_id'])?$params['data']['parent_id']:null;
                                                        $finnupspoc     = isset($params['data']['finnup_spoc'])?$params['data']['finnup_spoc']:null;
                                                        $created_by =  isset($params['data']['created_by'])?$params['data']['created_by']:null;

							$where = isset($params['where'])? $params['where'] : 0 ;

							if($where == 0){

							}
                                                   
                                                        // $userid = $this->db->insert("fpa_partners", array(
                                                            
                                                            
                                                        //     'name'=>$name,
                                                        //     'email'=>$email,
                                                        //     'mobile'=>$phone,
                                                        //     'password'=>$encryption,
                                                        //     'role_slug'=>  $role ,
                                                        //     'parent_id'=>$parent_id,
                                                        //     'company_name'=>$companyname,
                                                        //     'partner_id'=>$partnerid,
                                                        //     'finnup_spoc'=> $finnupspoc,
                                                        //     'created_by'=>$created_by));

							    $sql = "SELECT * FROM fpa_partners  WHERE id = ".$where;	
							    if (count($this->db->query($sql)->result()) == 0) {
								$var = $this->db->insert("fpa_partners", $params['data']);

							    }
							    else {
								$this->db->where('id', $params['where']);
								$update = $this->db->update("fpa_partners", $params['data']);
							    }	


                                                       
                                                       
                                                        json_output(200,array('status' => 200,'message' => 'successfully Added',"data"=>"successfull"));
                                                       


                                                }
                                                catch(Exception $e)
                                                {
                                                    json_output(200,array('status' => 401,'message' => $e->getMessage()));
                                                }
                                                }
                                                else
                                                {
                                                        json_output(400,array('status' => 400,'message' => 'Un Authorized Access!'));
                                                }
                                               
                                }
                                    
                                        
                                // }
                }  

		public function createpartner_manageteam()
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
				    $name   = $params['data']['name'];
				    $email  = $params['data']['email'];
				    $phone  = $params['data']['mobile'];
				    $companyname  = $params['data']['company_name'];
				    // ($params['tableName'] == "fpa_adminusers") {
					    $simple_string = $params['data']['password'];
					    $iv_length = openssl_cipher_iv_length($this->ciphering);
					    $encryption = openssl_encrypt($simple_string, $this->ciphering,
						$this->key, $this->options, $this->_iv);
		    
					    // $d_id = isset($params['data']['id']) ? $params['data']['id'] : "0";
		    
					    $params['data']['password'] = $encryption;
				    // $password       = isset($params['data']['password'])?$params['data']['password']:null;
				    $role       = isset($params['data']['role_slug'])?$params['data']['role_slug']:null;
				    $partnerid      = isset($params['data']['partner_id'])?$params['data']['partner_id']:null;
				    $parent_id      = isset($params['data']['parent_id'])?$params['data']['parent_id']:null;
				    $finnupspoc     = isset($params['data']['finnup_spoc'])?$params['data']['finnup_spoc']:null;
				    $created_by =  isset($params['data']['created_by'])?$params['data']['created_by']:null;
	
		
				    if ($params['tableName'] == "fpa_partners") {
					// $simple_string = $params['data']['password'];
					// $iv_length = openssl_cipher_iv_length($this->ciphering);
					// $encryption = openssl_encrypt($simple_string, $this->ciphering,
					//     $this->key, $this->options, $this->_iv);
		
					// $d_id = isset($params['data']['id']) ? $params['data']['id'] : "0";
		
					$params['data']['password'] = $encryption;
					$sql = "SELECT * FROM " . $params['tableName'] . " WHERE email =" . "'" . $email . "'";
		
					if (count($this->db->query($sql)->result()) == 0) {
					    $var = $this->db->insert($params['tableName'], $params['data']);
		
					    
					} else {
					    $this->db->where('id', $params['data']['id']);
					    $update = $this->db->update($params['tableName'], $params['data']);
		
		
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
		
	        } 

                public function edit_manageteam()
                {
                        $method     = $_SERVER['REQUEST_METHOD'];
                         if($method != 'POST')
                         {
                               json_output(400,array('status' => 400,'message' => 'Bad request.'));
                               }
                               else
                               {
                               //   $checkToken = $this->check_token();
                         if(true)
                           {
                             $response['status']=200;
                             $respStatus = $response['status'];
                             $params = json_decode(file_get_contents('php://input'), TRUE);
                              try
                               {
                                  $name   = $params['data']['name'];
                                  $email  = $params['data']['email'];
                                  $phone  = $params['data']['mobile'];
                                //   $companyname  = $params['data']['company_name'];
                                  $simple_string = $params['data']['password'];
                                  $iv_length = openssl_cipher_iv_length($this->ciphering);
                                  $encryption = openssl_encrypt($simple_string, $this->ciphering,
                                      $this->key, $this->options, $this->_iv);
                                      $params['data']['password'] = $encryption;
                                 $id =isset($params["where"]) ? $params["where"] : null;

                                      //    $password       = isset($params['data']['password'])?$params['data']['password']:null;
                                  $role       = isset($params['data']['role_slug'])?$params['data']['role_slug']:null;
                               //    $role_name       = isset($params['data']['name'])?$params['data']['name']:null;
                                  $partnerid      = isset($params['data']['partner_id'])?$params['data']['partner_id']:null;
                                //   $parent_id      = isset($params['data']['parent_id'])?$params['data']['parent_id']:null;
                                //   $finnupspoc     = isset($params['data']['finnup_spoc'])?$params['data']['finnup_spoc']:null;
                                //   $created_by =  $params['data']['created_by'];
                                $this->db->where("id",$id);
                                          
                                $userid = $this->db->update("fpa_partners", array(
                                                   
                                                   
                                  'name'=>$name,
                                  'email'=>$email,
                                  'mobile'=>$phone,
                                  'password'=>$encryption,
                                  'role_slug'=>  $role ,
                                //   'parent_id'=>$parent_id,
                                //   'company_name'=>$companyname,
                                //   'partner_id'=>$partnerid,
                                //   'finnup_spoc'=> $finnupspoc,
                                //   'created_by'=>$created_by
                                ));
                                
                                //   $userid2 = $this->db->update("fp_connector_users", array(
                                                   
                                                   
                                //        'name'=>$name,
                                //        'email'=>$email,
                                //        'mobile'=>$phone,
                                //        'status'=>"created",
                                //        'password'=>$encryption,
                                //        'role_slug'=>  $role ,
                                //        'parent_id'=>$parent_id,
                                //        'company_name'=>$companyname,
                                //        'partner_id'=>$partnerid,
                                //        'finnup_spoc'=> $finnupspoc,
                                //        'created_by'=>$created_by
                                // ));
                                  $role_name='';
                                  if( $role='sa'){
                                       $role_name = 'Super Admin';
                                  }
                                  elseif( $role='ad'){
                                       $role_name = 'Admin';
                                  }
                                  elseif( $role='cc'){
                                       $role_name = 'Credit Commitee';
                                  }
                                  elseif( $role='cm'){
                                       $role_name = 'Credit Manager';
                                  }
                                  elseif( $role='rm'){
                                       $role_name = 'Relationship Manager';
                                  }
       
                                              
                                              
                                       json_output(200,array('status' => 200,'message' => 'successfully Added',"data"=>$userid));
                                //        json_output(200,array('status' => 200,'message' => 'successfully Added',"data"=>$userid2));
                                       //    Email notification  
                                       $subject = "Dear ".$name." ,";
                                       $message = "Dear ".$name.","."<br/>"."<br/>"."<br/>"."FinnUp Superadmin has invited you to app.finnup.in/#/connectorlogin as a ". $role_name.", Please use the following link to set your password and login. " . "<br/>" ."<br/>" .
                                               "Email :" . $email . "<br/>" .
                                               // "Password :" . $password . "<br/>" .
                                               // "Password :" . $userdata->password. "<br/>" .
       
                                               "-----------------------------------------------<br/>
                                               Team Finnup";
                                                       
                                       // $to = 'platform@finnup.in';
                                       //$to = 'rec2004@gmail.com';
                                       //$to = 'vinothskumar4@gmail.com';
                                       $to = $email;
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
                                       catch(Exception $e)
                                       {
                                               json_output(200,array('status' => 401,'message' => $e->getMessage()));
                                       }
                                       }
                                       else
                                       {
                                               json_output(400,array('status' => 400,'message' => 'Un Authorized Access!'));
                                       }
                                                       
                         }
                                                               
                                                                       
                                                               
               }    
                          
                public function createpartner()
                {
                                $method = $_SERVER['REQUEST_METHOD'];
                                if($method != 'POST')
                                {
                                        json_output(400,array('status' => 400,'message' => 'Bad request.'));
                                }
                                else
                                {
                                        //   $checkToken = $this->check_token();
                                        if(true)
                                        {
                                                $response['status']=200;
                                                $respStatus = $response['status'];
                                                $params = json_decode(file_get_contents('php://input'), TRUE);

                                                try
                                                {                                             
                                                        $name   = $params['data']['name'];
                                                        $email  = $params['data']['email'];
                                                        $phone  = $params['data']['mobile'];
                                                        $companyname  = $params['data']['company_name'];
                                                        // ($params['tableName'] == "fpa_adminusers") {
                                                                $simple_string = $params['data']['password'];
                                                                $iv_length = openssl_cipher_iv_length($this->ciphering);
                                                                $encryption = openssl_encrypt($simple_string, $this->ciphering,
                                                                    $this->key, $this->options, $this->_iv);
                                        
                                                                // $d_id = isset($params['data']['id']) ? $params['data']['id'] : "0";
                                        
                                                                $params['data']['password'] = $encryption;
                                                        // $password       = isset($params['data']['password'])?$params['data']['password']:null;
                                                        $role_slug       = isset($params['data']['role_slug'])?$params['data']['role_slug']:null;
                                                        $partnerid      = isset($params['data']['partner_id'])?$params['data']['partner_id']:null;
                                                        $parent_id      = isset($params['data']['parent_id'])?$params['data']['parent_id']:null;
                                                        $finnupspoc     = isset($params['data']['finnup_spoc'])?$params['data']['finnup_spoc']:null;
                                                        $created_by =  isset($params['data']['created_by'])?$params['data']['created_by']:null;
                                                   
                                                        $userid = $this->db->insert("fpa_partners", array(
                                                            
                                                            
                                                            'name'=>$name,
                                                            'email'=>$email,
                                                            'mobile'=>$phone,
                                                            'password'=>$encryption,
                                                            'role_slug'=>  $role_slug ,
                                                            'parent_id'=>$parent_id,
                                                            'company_name'=>$companyname,
                                                            'partner_id'=>$partnerid,
                                                            'finnup_spoc'=> $finnupspoc,
                                                            'created_by'=>$created_by));

                                                       
                                                       
                                                        json_output(200,array('status' => 200,'message' => 'successfully Added',"data"=>$userid));
                                                       


                                                }
                                                catch(Exception $e)
                                                {
                                                    json_output(200,array('status' => 401,'message' => $e->getMessage()));
                                                }
                                                }
                                                else
                                                {
                                                        json_output(400,array('status' => 400,'message' => 'Un Authorized Access!'));
                                                }
                                               
                                }
                                    
                                        
                                // }
                }  


                // End of createpartner--------------------------------------------------------------------------


        public function createpartnerwithemail()
        {
                 $method     = $_SERVER['REQUEST_METHOD'];
                  if($method != 'POST')
                  {
                        json_output(400,array('status' => 400,'message' => 'Bad request.'));
                        }
                        else
                        {
                        //   $checkToken = $this->check_token();
                  if(true)
                    {
                      $response['status']=200;
                      $respStatus = $response['status'];
                      $params = json_decode(file_get_contents('php://input'), TRUE);
                       try
                        {
                           $name   = $params['data']['name'];
                           $email  = $params['data']['email'];
                           $phone  = $params['data']['mobile'];
                           $companyname  = $params['data']['company_name'];
                           $simple_string = $params['data']['password'];
                           $iv_length = openssl_cipher_iv_length($this->ciphering);
                           $encryption = openssl_encrypt($simple_string, $this->ciphering,
                               $this->key, $this->options, $this->_iv);
                               $params['data']['password'] = $encryption;
                               //    $password       = isset($params['data']['password'])?$params['data']['password']:null;
                           $role       = isset($params['data']['role_slug'])?$params['data']['role_slug']:null;
                        //    $role_name       = isset($params['data']['name'])?$params['data']['name']:null;
                           $partnerid      = isset($params['data']['partner_id'])?$params['data']['partner_id']:null;
                           $parent_id      = isset($params['data']['parent_id'])?$params['data']['parent_id']:null;
                           $finnupspoc     = isset($params['data']['finnup_spoc'])?$params['data']['finnup_spoc']:null;
                           $created_by =  $params['data']['created_by'];
                                   
                           $userid = $this->db->insert("fpa_partners", array(
                                            
                                            
                           'name'=>$name,
                           'email'=>$email,
                           'mobile'=>$phone,
                           'password'=>$encryption,
                           'role_slug'=>  $role ,
                           'parent_id'=>$parent_id,
                           'company_name'=>$companyname,
                           'partner_id'=>$partnerid,
                           'finnup_spoc'=> $finnupspoc,
                           'created_by'=>$created_by));

                           $userid2 = $this->db->insert("fp_connector_users", array(
                                            
                                            
                                'name'=>$name,
                                'email'=>$email,
                                'mobile'=>$phone,
                                'status'=>"created",
                                // 'password'=>$encryption,
                                // 'role_slug'=>  $role ,
                                // 'parent_id'=>$parent_id,
                                'company_name'=>$companyname,
                                // 'partner_id'=>$partnerid,
                                // 'finnup_spoc'=> $finnupspoc,
                                'created_by'=>$created_by));
                           $role_name='';
                           if( $role='sa'){
                                $role_name = 'Super Admin';
                           }
                           elseif( $role='ad'){
                                $role_name = 'Admin';
                           }
                           elseif( $role='cc'){
                                $role_name = 'Credit Commitee';
                           }
                           elseif( $role='cm'){
                                $role_name = 'Credit Manager';
                           }
                           elseif( $role='rm'){
                                $role_name = 'Relationship Manager';
                           }

                                       
                                       
                                json_output(200,array('status' => 200,'message' => 'successfully Added',"data"=>$userid));
                                json_output(200,array('status' => 200,'message' => 'successfully Added',"data"=>$userid2));
                                //    Email notification  
                                $subject = "Dear ".$name." ,";
                                $message = "Dear ".$name.","."<br/>"."<br/>"."<br/>"."FinnUp Superadmin has invited you to app.finnup.in/#/connectorlogin as a ". $role_name.", Please use the following link to set your password and login. " . "<br/>" ."<br/>" .
                                        "Email :" . $email . "<br/>" .
                                        // "Password :" . $password . "<br/>" .
                                        // "Password :" . $userdata->password. "<br/>" .

                                        "-----------------------------------------------<br/>
                                        Team Finnup";
                                                
                                // $to = 'platform@finnup.in';
                                //$to = 'rec2004@gmail.com';
                                //$to = 'vinothskumar4@gmail.com';
                                $to = $email;
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
                                catch(Exception $e)
                                {
                                        json_output(200,array('status' => 401,'message' => $e->getMessage()));
                                }
                                }
                                else
                                {
                                        json_output(400,array('status' => 400,'message' => 'Un Authorized Access!'));
                                }
                                                
                  }
                                                        
                                                                
                                                        
        }  
                                        
                                        //------------------create partner along with email------------

                                        


                public function passwordcheck()
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
                                                        if ($params['tableName'] == "fpa_partners") 
                                                        {
        
                                                                $simple_string = $params['data']['password'];
                                                                $iv_length = openssl_cipher_iv_length($this->ciphering);
                                                                $encryption = openssl_encrypt($simple_string, $this->ciphering,
                                                           $this->key, $this->options, $this->_iv);
        
                                                                $checkuser = array('email' => $params['data']['email'], 'password' => $encryption,'status' => 1);
                                                                $this->db->where($checkuser); 
                                                                $count = $this->db->count_all_results($params['tableName']);
                                                                
                                                        
                                                                if($count==1)
                                                                        {
                                                                        $flag = array('email' => $params['data']['email'], 'password' => $encryption, 'password_status' => 1);
                                                                        $this->db->where($flag);
                                                                        $countflag = $this->db->count_all_results($params['tableName']);
        
                                                                        $query = $this->db->get_where('fpa_partners', array('email' => $params['data']['email']));
                                                                        foreach ($query->result() as $row)
                                                                                {
                                                                                    
                                                                                        $txnArr[] = [
                                                                                                'id'=>$row->id,
                                                                                                'email'=>$row->email,
                                                                                                'name'=>$row->name,
                                                                                                'slug'=>$row->role_slug,
                                                                                                'partner_id'=>$row->partner_id,
                                                                                                'parent_id'=>$row->parent_id,
                                                                                                'company_name'=>$row->company_name,
                                                                                                'finnup_spoc'=>$row->finnup_spoc
                                                                                        ];
                                                                                        
                                                                                }
        
                                                                        $token = $this->jwttoken->token($txnArr);
        
                                                                        $this->db->where('email', $params['data']['email']);
                                                                        $update = $this->db->update("fpa_partners", array('token'=>$token));
                                                                        $tokenArr[] = [
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
                                                        
                                                }
                                                else
                                                {
                                        
                                                json_output(400,array('status' => 400,'message' => 'Bad request.'));
                                        }
                                }else{
                                        json_output(400,array('status' => 400,'message' => 'Bad request.'));
                                }
                                }
                }
                 //------------------------------ end of passwordcheck


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
                                                         if ($params['tableName'] == "fpa_partners") 
                                                         {
         
                                                                 $old_password = $params['data']['old_password'];
                                                                 // $new_password = $params['data']['new_password'];
                      						 $new_password = $params['data']['password'];

								 $pwd_pattern = preg_match_all('$\S*(?=\S{8,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])(?=\S*[\W])\S*$', $new_password);

								//  print_r($pwd_pattern);
								//  exit;
         
                                                                 $iv_length = openssl_cipher_iv_length($this->ciphering);
                                                                 $old_password = openssl_encrypt($old_password, $this->ciphering, $this->key, $this->options, $this->_iv);
                                                                 // $new_password = openssl_encrypt($new_password, $this->ciphering, $this->key, $this->options, $this->_iv);
								 $new_password = openssl_encrypt($new_password, $this->ciphering, $this->key, $this->options, $this->_iv);
         
                                                                 if($old_password != $new_password && $pwd_pattern == 1){
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
                                                                                 json_output(200,array('status' => 401,'message' => 'Invalid Password'));
                                                                         }
                                                                 }else if($old_password == $new_password){
                                                                         
                                                                         $resp = array('status' => 402,'message' => 'old password and new password should not be same');
                                                                         json_output($respStatus,$resp);
                                                                 }else if($pwd_pattern != 1){
									$resp = array('status' => 403,'message' => 'Password Pattern not match');
									json_output($respStatus,$resp);

								 }
								 else{
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
                 }  
                 // end of changepassword 




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
                                        $query = $this->db->get_where('fpa_partners', array('email' => $params['data']['email'],'status'=>1));
                                        if($query->result()){
        
                                                $this->db->where('email', $params['data']['email']);
                                                $this->db->where('is_active', 1);
                                                $this->db->update('fp_partnerotp',array("is_active" => 0)); 
                                        
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
                                                                                'partner_id' => $admin_id,
                                                                                'email'=>$admin_email,
                                                                                'otp'=>$num_str
                                                                                
                                                                        ];
                                                                        $this->db->insert('fp_partnerotp', $data['data']);
                                                        
        
                                                json_output(200,array('status' => 200,'message' => "otp send"));
                                        }else{
                                                json_output(200,array('status' => 400,'message' => 'Invalid Email Id.'));
                                        }
        
                                }else{
                                        json_output(200,array('status' => 400,'message' => 'Bad request.'));
                                }
                        }
                }  
                // end of sendotp

                
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
			

			if($email == true && $otp== true){
            //    echo "new";
			
				$curr = date('Y-m-d H:i:s');
				$last_min = date('Y-m-d H:i:s', strtotime('-5 minutes'));
			
				$this->db->select();
				$this->db->from('fp_partnerotp');
				$this->db->where('email', $email);
				$this->db->where('otp', $otp);
				$this->db->where('is_active', 1);
				$this->db->where('created_at >=', $last_min);
				$this->db->where('created_at <=', $curr);
				$result = $this->db->get();

				if($result->num_rows() == 1){
						$data['data']= 0;
					$this->db->where('email', $email);
					$this->db->where('otp', $otp);
					$this->db->where('is_active', 1);
					$this->db->update('fp_partnerotp',array("is_active" => 0)); 
					json_output(200,array('status' => 200,'message' => "success"));
				}else{

				$this->db->from('fp_partnerotp');
				$this->db->where('email', $email);
				$this->db->where('is_active', 1);
				$this->db->where('created_at <=', $last_min);
				$this->db->update('fp_partnerotp',array("is_active" => 0)); 
				json_output(200,array('status' => 400,'message' => "Try Again"));
				}
			}
			else{
				json_output(200,array('status' => 400,'message' => "Try Agains"));
			}

				
			// $query = $this->db->get_where('fp_adminotp', array('email' => $params['email'] , 'otp' => $params['otp'], 'isActive'=> 1));
			// echo $query->num_rows();
			
		}

	}    
        // end of check_otp
        

        public function connectorforgotpassword(){
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
			if($email == true && $password== true){

				$curr = date('Y-m-d H:i:s');
				$last_min = date('Y-m-d H:i:s', strtotime('-60 minutes'));
			
				$this->db->select();
				$this->db->from('fp_partnerotp');
				$this->db->where('email', $email);
				$this->db->where('is_active', 0);
				$this->db->where('created_at >=', $last_min);
				$this->db->where('created_at <=', $curr);
				$otpcheck = $this->db->get();

				if($otpcheck->num_rows() >= 1){

				$password = openssl_encrypt($password, $this->ciphering, $this->key, $this->options, $this->_iv);
				$this->db->select();
				$this->db->from('fpa_partners');
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

				// $this->db->from('fpa_adminusers');
				$this->db->where('email', $email);
				$this->db->update('fpa_partners',$data['data']); 
					// echo $old_password;
					json_output(200,array('status' => 200,'message' => "success"));
				}else{
					json_output(400,array('status' => 400,'message' => "Something When  Wrong guys"));
				}

			}else{
				json_output(200,array('status' => 400,'message' => "Something When Wrong"));
			}
			
		}
		
	} 
         // end of adminforgotpass


         public function get_details(){
                $method = $_SERVER['REQUEST_METHOD'];
                if($method != 'POST'){
                json_output(400,array('status' => 400,'message' => 'Bad request.'));
                }else{
                        // $checkToken = $this->check_token();
                        if(true){
                        $response['status']=200;
                        $respStatus = $response['status'];
                        $params = json_decode(file_get_contents('php://input'), TRUE);
                        $selectkey = isset($params['selectkey']) ? $params['selectkey'] : "*"; 
                        $join = isset($params['key']) ? $params['key'] : "";
                        $where = isset($params['where']) ? $params['where'] : "";	
                        $sql = "SELECT " .$selectkey. " FROM ".$params['tableName']."  WHERE ".$where;
                        $resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql)->result());
                        json_output($respStatus,$resp);
                }else{
                        json_output(400,array('status' => 400,'message' => 'Bad request.'));
                }
                }
        } // get_details

        
        public function get_connector()
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

									$where 		= isset($params['where']) ? $params['where'] : "";
									// $createdby 		= isset($params['createdby']) ? $params['createdby'] : "";	

									// $sql="SELECT * FROM fpa_partners WHERE fpa_partners.parent_id='$where'";
									$sql="SELECT fp.* ,fr.name as role
									FROM fpa_partners fp,fpa_partner_roles fr
									WHERE fp.role_slug=fr.slug AND fp.status=1 AND fp.partner_id= '$where'";
									$resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql)->result());
									return json_output($respStatus,$resp);
							}
							else
							{
								return json_output(400,array('status' => 400,'message' => 'success'));
							}
						
					}
					else
					{
							return json_output(400,array('status' => 400,'message' => 'Bad request.'));
					}
        
        } 

        public function get_deletedconnector()
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

									$where 		= isset($params['where']) ? $params['where'] : "";
									// $createdby 		= isset($params['createdby']) ? $params['createdby'] : "";	

									// $sql="SELECT * FROM fpa_partners WHERE fpa_partners.parent_id='$where'";
									$sql="SELECT fp.* ,fr.name as role
									FROM fpa_partners fp,fpa_partner_roles fr
									WHERE fp.role_slug=fr.slug AND fp.status=0 AND fp.partner_id= '$where'";
									$resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql)->result());
									return json_output($respStatus,$resp);
							}
							else
							{
								return json_output(400,array('status' => 400,'message' => 'success'));
							}
						
					}
					else
					{
							return json_output(400,array('status' => 400,'message' => 'Bad request.'));
					}
        
        } 

        public function enable_deletedconnector()
        {
         $method = $_SERVER['REQUEST_METHOD'];
         if ($method == "POST") {
            // $checkToken = $this->check_token();
           if (true) {
              $response['status'] = 200;
              $respStatus = $response['status'];
              $params = json_decode(file_get_contents('php://input'), true);

              $selectkey = isset($params['selectkey']) ? $params['selectkey'] : "*";
              $join = isset($params['key']) ? $params['key'] : "";
              $where = isset($params['where']) ? $params['where'] : "";

              $sql = "update fpa_partners set status='1' where id='$where'";

              $resp = array('status' => 200, 'message' => 'Success', 'data' => $this->db->query($sql));

            // return json_output($respStatus,$resp);
            return json_output(200, array('status' => 200, 'message' => "Deleted Successfully"));
            } 
            else {
            return json_output(400, array('status' => 400, 'message' => "Unauthorized"));
            }

         }  
        else {
        return json_output(400, array('status' => 400, 'message' => 'Bad request.'));
           }

        }



	public function get_fpa_partners()
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
						'location_id'=>$row->location_id,
						'password'=>openssl_decrypt ($row->password, $this->ciphering, $this->key, $this->options, $this->_iv),
						'company_name'=>$row->company_name,
						'partner_id'=>$row->partner_id,
						'parent_id'=>$row->parent_id,
						'finnup_spoc'=>$row->finnup_spoc
						];
							}

                                                        $subject ="Finnup Admin Team ";
							$message = "Hello Finnup Admin! <br/><br/>" . "Please find the login credential below <br/><br/>" .
							"Email :" . $row->email . "<br/>" .
							 "Password :" .openssl_decrypt ($row->password, $this->ciphering, $this->key, $this->options, $this->_iv) . "<br/>" . 
						// $to =  $row->email;
						$to='parthiban24242000@gmail.com';// echo "$to";
						$email = new \SendGrid\Mail\Mail();
						$email->setSubject($subject);
						$email->addContent("text/html", $message);
						$email->setFrom('platform@finnup.in', 'FinnUp Team');
								
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


        public function gettotalsuperadmin()
	{
			$method = $_SERVER['REQUEST_METHOD'];
			if($method =="POST")
                        {
				// $checkToken = $this->check_token();
				if(True)
				{
				$response['status']=200;
			        $respStatus = $response['status'];
				$params 	= json_decode(file_get_contents('php://input'), TRUE);

				$selectkey 	= isset($params['selectkey']) ? $params['selectkey'] : "*"; 
				$join 		= isset($params['key']) ? $params['key'] : "";
				$where 		= isset($params['where']) ? $params['where'] : "";	

				$sql = "SELECT * FROM fpa_adminusers WHERE fpa_adminusers.role_slug='sa'";
				
        			$resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql)->result());
				return json_output($respStatus,$resp);
				}
				else
				{
					return json_output(400,array('status' => 400,'message' => "Auth Missing!"));
					
						
				}
                        }                
			else
			{
				return json_output(400,array('status' => 400,'message' => 'Bad request.'));
			}
				
			
        }


                
	public function sa_taskassign()
        {
                $method = $_SERVER['REQUEST_METHOD'];
                if($method != 'POST'){
                        json_output(400,array('status' => 400,'message' => 'Bad request.'));
                        }else{
                            // $checkToken = $this->check_token(); 
                        if(true){
                          $response['status']=200;
                          $respStatus = $response['status'];
                        $params = json_decode(file_get_contents('php://input'), TRUE);
                         
                        try{ 
                         
                         $id='';
                 
                        $task_details = $this->db->insert("fpa_sa_taskdetails", $params['data']);
                        $id = $this->db->insert_id();  
                         
                                          
                        $sql =  "select borrower_id,id,task_assigned_to
                        from fpa_sa_taskdetails where fpa_sa_taskdetails.id=".$id; 
                        $taskdata = $this->db->query($sql)->row();
                          
                 
                        $assigndata =  "select name,email,id
                          from fpa_adminusers where fpa_adminusers.id=".$taskdata->task_assigned_to;
                 
                        $rmdata = $this->db->query($assigndata)->row();
                                               $task_details_worklog =$this->db->insert("fpa_sa_taskdetails_worklog",array('sa_taskdetail_id'=>$taskdata->id)); 
                                               $fpa_users = "UPDATE fpa_users 
                          SET status ='assigned', sa_id='".$rmdata->id."',".
                          "sa_name='".$rmdata->name."',".
                          "sa_email='".$rmdata->email."' WHERE fpa_users.id=".$taskdata->borrower_id;
       
       
                          
                        $rm_name=$rmdata->name;
                        $company_name = $params['company_name'];
       
                               
                         $checkdata = $this->db->query($fpa_users);
                                        
                         if($task_details && $task_details_worklog && $fpa_users){
       
       
                                        $subject = "Dear ".$rm_name.",";
                               $message = "Dear ".$rm_name.","."<br/><br/>"."A new application for ".$company_name." has been assigned to you by the Superadmin.<br/>
                               Please click on the below link to view ".$company_name.".<br/><br/>".
                               "link : app.finnup.in/#/admin.";
                               
                               $to = "rahul@finnup.in";
                               $tos = "aisha@finnup.in";
                               $email = new \SendGrid\Mail\Mail();
                               $email->setSubject($subject);
                               $email->addContent("text/html", $message);
                               $email->setFrom("platform@finnup.in", 'FinnUp Team');
                               $email->addTo($to);
                               $email->addTo($tos);
                               $sendgrid = new \SendGrid("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");
                               try {
                                   $response = $sendgrid->send($email);
                               } catch (Exception $e) {
                                   echo 'Caught exception: ', $e->getMessage(), "\n";
                               }
       
       
       
                          json_output(200,array('status' => 200,'message' => 'Task assigned successfully!'));
                         }else{
                          json_output(200,array('status' => 400,'message' => 'Bad request.'));
                         }
                        }catch(Exception $e){
                         json_output(200,array('status' => 401,'message' => $e->getMessage()));
                        }
                        }else{
                         json_output(400,array('status' => 400,'message' => 'Bad request.'));
                        }
                         }
        } 
        // ---------------------End of taskassign 

        
        public function assigntosa_email()
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
       
                                                        $sql=" SELECT t1.email,t1.name FROM fpa_adminusers t1 WHERE t1.id=".$where;
       
                                                        $resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql)->result());
                                                        return json_output($respStatus,$resp);
                                        }
                                        else
                                        {
                                                return json_output(400,array('status' => 400,'message' => ''));
                                        }
                        }
                        else
                        {
                                        return json_output(400,array('status' => 400,'message' => 'Bad request.'));
                        }
        } //------------------ end  of assigntosa_email  -----------------------
       

        public function push_toadmin()
        {
                $method = $_SERVER['REQUEST_METHOD'];
		if($method =="POST")
		{
		// $checkToken = $this->check_token();
		if(True)
                {
			$response['status']=200;
			$respStatus = $response['status'];
			$params 	= json_decode(file_get_contents('php://input'), TRUE);

			$selectkey 	= isset($params['selectkey']) ? $params['selectkey'] : "*"; 
			$join 		= isset($params['key']) ? $params['key'] : "";
			$where 		= isset($params['where']) ? $params['where'] : "";	

			$sql = "update fpa_users set status='new' where id=".$where;
			$resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql));
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
        


}// end of class Connetorusers----------------------------------------------------------------------------------------------------
