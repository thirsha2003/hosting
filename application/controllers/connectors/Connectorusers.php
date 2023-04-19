<?php

header('Access-Control-Allow-Origin: *'); //for allow any domain, insecure
header('Access-Control-Allow-Headers: *'); //for allow any headers, insecure
header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE'); //method allowed
header("HTTP/1.1 200 OK");

defined('BASEPATH') or exit('No direct script access allowed');
include APPPATH . 'ThirdParty/sendgrid-php/sendgrid-php.php';
include APPPATH . 'ThirdParty/MTalkz.php';
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

                
                public function createpartner()
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
                               //$password       = isset($params['data']['password'])?$params['data']['password']:null;
                           $role       = isset($params['data']['role_slug'])?$params['data']['role_slug']:null;
                        //$role_name       = isset($params['data']['name'])?$params['data']['name']:null;
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
 //    Email notification  
              $subject = "Dear ".$name." ,";
              $message = "Dear ".$name.","."<br/>"."<br/>"."<br/>"."FinnUp Superadmin has invited you to app.finnup.in/#/connectorlogin as a ". $role_name.", Please use the following link to set your password and login. " . "<br/>" ."<br/>" .
                      "Email :" . $email . "<br/>" .
                      "Password :" .$simple_string. "<br/>" .
                    

                      "-----------------------------------------------<br/>
                     Team Finnup";
                            
               
               $to = $email;
               $email = new \SendGrid\Mail\Mail();
               $email->setSubject($subject);
               $email->addContent("text/html", $message);
               $email->setFrom("support@finnup.in", 'FinnUp Team');
               $email->addTo($to);
               $sendgrid = new \SendGrid("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");
               try {
                   $response = $sendgrid->send($email);
               } catch (Exception $e) {
                   echo 'Caught exception: ', $e->getMessage(), "\n";
               }
              
               json_output(200,array('status' => 200,'message' => 'Connector Add Sucessfuly !'));

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
                                                                                                'slug'=>$row->role_slug
                                                                                        ];
                                                                                }
        
                                                                        $token = $this->jwttoken->token($txnArr);
        
                                                                        $this->db->where('email', $params['data']['email']);
                                                                        $update = $this->db->update("fpa_partners", array('token'=>$token));
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
                                                                        $email->setFrom('support@finnup.in', 'FinnUp Team');
                                                                        // $email->setFrom('support@finnup.in', 'FinnUp Team');
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

	}    // end of check_otp
        

        
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

         
        


}// end of class Connetorusers----------------------------------------------------------------------------------------------------