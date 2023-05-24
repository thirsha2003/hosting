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


class Connector_common extends CI_Controller
{

	private $_iv = 'xb5wypRrVgl0rYm1';
	private $ciphering = "AES-128-CTR";
	private $key = "finnup";
	private $options = 0;	


    public function __construct(){
		parent::__construct();
		$this->load->helper('json_output');   
		$this->load->library('encryption');	
	
			$this->ci =& get_instance();
			$this->ci->load->database();
		
	}	




	public function creater_connector()
	{
					$method = $_SERVER['REQUEST_METHOD'];
					if($method != 'POST')
					{
							json_output(400,array('status' => 400,'message' => 'Bad request.'));
					}
					else
					{

						$params = json_decode(file_get_contents('php://input'), TRUE);
						$allowuser = $params['data']['email'];
						$domain_name = substr(strrchr($allowuser, "@"), 1);
						$domain_name = explode('.', $domain_name)[0];
						// print_r($domain_name);
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
								return json_output(200,array('status' => 301, 'message' => 'Domain Not Allow'));
							}
							else
							{
							$domain_check = true;
							}
							}else{
								return json_output(200,array('status' => 301, 'message' => 'Domain Not Allow'));
							}
							

						
							//   $checkToken = $this->check_token();
							if($domain_check)
							{
									$response['status']=200;
									$respStatus = $response['status'];
									
									try
									{     

											$name   = $params['data']['name'];
											$email  = $params['data']['email'];
											$phone  = $params['data']['mobile'];
											$companyname  = $params['data']['company_name'];
											$partner_id = substr($companyname, 0, 4);
											$partner_id = strtoupper($partner_id);
											$partner_id = $partner_id.rand(1000, 9999);
											$simple_string = $params['data']['password'];
											if(strlen($companyname) <= 4 ){
												return json_output(200,array('status' => 402,'message' => "Invalid Company Name"));
											}
											$iv_length = openssl_cipher_iv_length($this->ciphering);
											$encryption = openssl_encrypt($simple_string, $this->ciphering,
											$this->key, $this->options, $this->_iv);
					
											// $d_id = isset($params['data']['id']) ? $params['data']['id'] : "0";
							
											$params['data']['password'] = $encryption;
											// $password       = isset($params['data']['password'])?$params['data']['password']:null;
											$role_slug       = isset($params['data']['role_slug'])?$params['data']['role_slug']:null;
											$partnerid      = $partner_id;
											$parent_id      = isset($params['data']['parent_id'])?$params['data']['parent_id']:null;
											$finnupspoc     = isset($params['data']['finnup_spoc'])?$params['data']['finnup_spoc']:null;
											$created_by =  isset($params['data']['created_by'])?$params['data']['created_by']:null;
											$userid = $this->db->insert("fpa_partners", array(
												'name'=>$name,
												'email'=>$email,
												'mobile'=>$phone,
												'password'=>$encryption,
												'role_slug'=> "sa" ,
												'parent_id'=>$parent_id,
												'company_name'=>$companyname,
												'partner_id'=>$partnerid,
												'finnup_spoc'=> $finnupspoc,
												'created_by'=>$created_by));

												 

												$fpa_users = "UPDATE fp_connector_users 
												SET status =0  WHERE email=".$email;


$subject = "Dear ".$name." ,";
$message = "Dear ".$name.","."<br/>"."<br/>"."<br/>"."FinnUp Superadmin has invited you to app.finnup.in/#/connector/login as a Super Admin, Please use the following link to set your password and login. " . "<br/>" ."<br/>" .
"Email :" . $email . "<br/>" .
"Password :" . $simple_string . "<br/>" .
// "Password :" . $userdata->password. "<br/>" .
                            "-----------------------------------------------<br/>
Team Finnup";  
 $to =$email;
$email = new \SendGrid\Mail\Mail ();
$email->setSubject($subject);
$email->addContent("text/html", $message);
$email->setFrom("support@finnup.in", 'FinnUp Team');
$email->addTo($to);

$sendgrid = new \SendGrid ("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");
try {
  $response = $sendgrid->send($email);
} catch (Exception $e) {
  echo 'Caught exception: ', $e->getMessage(), "\n";
}

                        json_output(200,array('status' => 200,'message' => 'successfully Added',"data"=>$userid));
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




}
