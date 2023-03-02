<?php
header('Access-Control-Allow-Origin: *'); //for allow any domain, insecure
header('Access-Control-Allow-Headers: *'); //for allow any headers, insecure
header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE'); //method allowed
header("HTTP/1.1 200 OK");

defined('BASEPATH') OR exit('No direct script access allowed');
include APPPATH . 'ThirdParty/sendgrid-php/sendgrid-php.php';

class Lender extends CI_Controller 
{

	public function __construct(){
		parent::__construct();
		$this->load->helper('json_output');
	}

	public function getlist()
	{
		try
		{
			$method = $_SERVER['REQUEST_METHOD'];
			if($method != 'POST')
			{
					json_output(400,array('status' => 400,'message' => 'Bad request.'));
			}else
			{
					
				$check_auth_user	= $this->login->check_auth_user();
				if($check_auth_user == true)
				{
					$params = json_decode(file_get_contents('php://input'), TRUE);
					
					if($params['selectkey'] != '' )
					{
							$response['status']=200;
							$respStatus = $response['status'];
							$selectkey = isset($params['selectkey']) ? $params['selectkey'] : "*"; 
							$where = isset($params['where']) ? $params['where'] : "";
							$sql = "SELECT " .$selectkey. " FROM ".$params['tableName']."  WHERE ".$where;
							$resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql)->result());
							json_output($respStatus,$resp);
					}
				}
			}
		}catch(Exception $ex)
		{
			$msg=$ex->getMessage();
			//$this.log("Sara");
			$response['status']=400;
			$resp = array('status' => 400,'message' =>  $msg,'data' => "");
			json_output($respStatus,$resp);
		}
	}

	private function log($message) {
		print_r('----------------------------Sara-------------------------------------');
		print_r($message);
		print_r('----------------------------Sara-------------------------------------');
		echo "\n";
	}

	public function express_interest()
	{
		  $method = $_SERVER['REQUEST_METHOD'];
		  if($method != 'POST')
		  {
			  json_output(400,array('status' => 400,'message' => 'Bad request.'));
		  }
		  else
		  {
			  $check_auth_user = $this->login->check_auth_user();
			  // if($check_auth_user == true){
			  // $response = $this->login->auth();
			  $response['status'] = 200;
			  $respStatus = $response['status'];
			  if($response['status'] == 200)
			  {
				  $params = json_decode(file_get_contents('php://input'), TRUE);
				  if ($check_auth_user != true) 
				  {
					  $respStatus = 400;
					  $resp = array('status' => 400,'message' =>  'Fields Missing');
					  return json_output(400,array('status' => 400,'message' => 'Fields Missing'));
				  } 
				  else 
				  {
					  $borrowerid="";
					  $lenderid="";
					  $product="";

					 if(true)
					  {								
						  $lenderid=isset($params['data']['lender_id']) ? $params['data']['lender_id'] : 0;
						  $borrowerid=isset($params['data']['borrower_id']) ? $params['data']['borrower_id'] : 0;
						  $borrower_loanrequests_id =isset($params['data']['borrower_loanrequests_id']) ? $params['data']['borrower_loanrequests_id'] : 0;;
						  $lender_master_id =isset($params['data']['lender_master_id']) ? $params['data']['lender_master_id'] : 0;




						  $sql ="select name, email,mobile from fpa_users where id=".$borrowerid;
						  $borrowerdata= $this->db->query($sql)->row();

						  $sql ="select name, email,mobile from fpa_users where id=".$lenderid;
						  $lenderdata= $this->db->query($sql)->row();





						  try{

							

							$intrest_by = array( 
							'lender_intrest_received'=>"yes", 
							'lender_interest_expressed_by' =>  $lenderid, 
						
						);



						$conditions_loan_app = array( 'borrower_id'=>$params['data']['borrower_id'], 'loanrequest_id' =>  $params['data']['borrower_loanrequests_id'],
						"lendermaster_id"=>$lender_master_id);
						
							$this->db->select('id');
							$this->db->from('fpa_loan_applications');
							$this->db->where($conditions_loan_app);
							$this->db->update("fpa_loan_applications",$intrest_by);
							// $num_results = $this->db->count_all_results();



							$conditions = array( 'borrower_id'=>$params['data']['borrower_id'], 'borrower_loanrequests_id' =>  $params['data']['borrower_loanrequests_id'], 'lender_id'=>$lenderid, "lender_master_id"=>$lender_master_id);
							
							$this->db->select('id');
							$this->db->from('fp_lender_proposals');
							$this->db->where($conditions);
							$num_results = $this->db->count_all_results();
							// return json_output(400,array('status' => 400,'message' => $num_results));
							if($num_results == 0){
								$this->db->insert('fp_lender_proposals', $params['data']);

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

								  // $to = 'support@finnup.in';
								  //$to = 'rec2004@gmail.com';
								  $to = 'parthiban24242000@gmail.com';
								  $email = new \SendGrid\Mail\Mail();
								  $email->setSubject($subject);
								  $email->addContent("text/html", $message);
								  $email->setFrom("support@finnup.in", 'FinnUp Team');
								  $email->addTo($to);							
								  $sendgrid = new \SendGrid("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");
								  try {
									  $response = $sendgrid->send($email);
								  } catch (Exception $e) {
									  echo 'Caught exception: ',  $e->getMessage(), "\n";
								  }
								  $resp = array('status' => 200,'message' =>  'Inserted success','data' => "" );
								  return json_output(200,array('status' => 200,'message' => 'Submitted'));
								}
								
								else{
									return json_output(200,array('status' => 401,'message' => 'Already Submitted'));
								}

						  }catch(Exception $e)
						  {

							  echo 'Caught exception: ',  $e->getMessage(), "\n";
						  }

						



						 

							  
					  }//----------------end of Condition 2
					   


				  }

				  
			  }
		
	  }
	}


}//--------------------end of class-------------------------------------------
