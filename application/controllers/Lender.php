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
						  $loan_id =isset($params['loan_app_id']) ? $params['loan_app_id'] : 0;


						$borrowerloanrequestdetails = $this->db->get_where('fp_borrower_loanrequests', array('id' => $borrower_loanrequests_id));

						if($borrowerloanrequestdetails->num_rows() >= 1){

						
						foreach ($borrowerloanrequestdetails->result() as $rows)
									{
										$borrowerloanslug = $rows->product_slug;
									}
									// print_r($borrowerloanrequestdetails->num_rows());
						
								}else{
									$borrowerloanslug = "";
								}




						  $sql ="select name, email,mobile from fpa_users where id=".$borrowerid;
						  $borrowerdata= $this->db->query($sql)->row();

						  $sql ="select name, email,mobile from fpa_users where id=".$lenderid;
						  $lenderdata= $this->db->query($sql)->row();





						  try{

							

							$intrest_by = array( 
							'lender_intrest_received'=>"yes", 
							'lender_interest_expressed_by' =>  $lenderid, 
							'loanapplication_status' =>  "Express Interest", 
							'workflow_status' =>  "Express Interest",
						
						);


						if($loan_id == 0){

						$conditions_loan_app = array( 'borrower_id'=>$params['data']['borrower_id'], 'loanrequest_id' =>  $params['data']['borrower_loanrequests_id'],
						"lendermaster_id"=>$lender_master_id);
					}else{
						$conditions_loan_app = array("id"=>$loan_id);
					}

						
							// $this->db->select('id');
							$this->db->from('fpa_loan_applications');
							$this->db->where($conditions_loan_app);
							$this->db->update("fpa_loan_applications",$intrest_by);

							$this->db->select('borrower_id');
							$loanapplicationid = $this->db->where($conditions_loan_app);

							$this->db->where($conditions_loan_app);
							$this->db->update("fpa_loan_applications",$intrest_by);
							// $num_results = $this->db->count_all_results();





							$conditions = array( 'borrower_id'=>$params['data']['borrower_id'], 'borrower_loanrequests_id' =>  $params['data']['borrower_loanrequests_id'], 'lender_id'=>$lenderid, "lender_master_id"=>$lender_master_id);
							
							$this->db->select('id');
							$this->db->from('fp_lender_proposals');
							$this->db->where($conditions);
							$num_results = $this->db->count_all_results();
							// return json_output(400,array('status' => 400,'message' => $num_results));

							$conditionsloanapplication = array( 'borrower_id'=>$params['data']['borrower_id'], 'loanrequest_id' =>  $params['data']['borrower_loanrequests_id'], "lendermaster_id"=>$lender_master_id);
							
							$this->db->select('id');
							$this->db->from('fpa_loan_applications');
							$this->db->where($conditionsloanapplication);
							$loan_app_check_count = $this->db->count_all_results();

							if($loan_app_check_count == 0){
								$arrData= array(
									"lender_id"=> $lenderid,
									"lendermaster_id"=> $params['data']['lender_master_id'],
									"borrower_id"=> $params['data']['borrower_id'],
									"loanrequest_id"=> $params['data']['borrower_loanrequests_id'],
									"product_slug"=> $borrowerloanslug,
									"is_created"=> "L",
									"lender_interest_expressed_by"=> $lenderid,
									"lender_intrest_received"=> "yes",
									'loanapplication_status' =>  "Express Interest", 
									'workflow_status' =>  "Express Interest",
								);
								// print_r($arrData);

								$this->db->insert('fpa_loan_applications', $arrData);
							}
							
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

	
	public function borrowerprofile_details()
	{
	 $method = $_SERVER['REQUEST_METHOD'];
	 if($method =="POST")
	 {
			//  $checkToken = $this->check_token();
			 if(True)
			 {
					 $response['status']=200;
					 $respStatus = $response['status'];
					 $params 	= json_decode(file_get_contents('php://input'), TRUE);
 
					 $selectkey 	= isset($params['selectkey']) ? $params['selectkey'] : "*"; 
					 $join 		= isset($params['key']) ? $params['key'] : "";
					 $where 		= isset($params['where']) ? $params['where'] : "";
					 $id  = $params['id'];
					 
					 if(isset($params['id'])){

						$sql = 'SELECT
						bud.date_of_incro_month as month, 
						bud.date_of_incro_year as years,
						bud.company_industry as platform, 
						bud.about_company as about, 
						bud.company_business_model as companymodel,
						bud.company_address as location, 
						bud.pincode as pincode, 
						bud.pan as pan, 
						bud.cin as cin,  
						bud.business_rating,
						ind.name as industry, 
						fpc.name as cityname,
						fpd.name as distname,
						fps.name as statename,
						bud.api_pincode
						FROM fp_borrower_user_details bud 
						LEFT JOIN fp_industry ind on ind.id=bud.company_industry 
						left join fp_city fpc on bud.city=fpc.id 
						left JOIN fp_district fpd on bud.district=fpd.id 
						left join fp_state fps on bud.state=fps.id WHERE bud.user_id = '.$id;

						$result= $this->db->query($sql)->result();
						$status = 200;
						$message = "success";
						
					 }else{
						$result = null ;
						$status = 0;
						$message = "ID missing Some field missing";
					  }				 
					 $resp = array('status' => $status,'message' => $message ,'data' => $result);
					 return json_output($respStatus,$resp);
			 }
			 else
			 {
				 return json_output(400,array('status' => 400,'message' => "missing authentication"));
			 }
		 
	 }
	 else
	 {
			 return json_output(400,array('status' => 400,'message' => 'Bad request.'));
	 }
 
	 } 

	//  End of borrowerprofile_details----------------------------------------------------------

	 public function directorprofile_details()
	 {
	  $method = $_SERVER['REQUEST_METHOD'];
	  if($method =="POST")
	  {
			 //  $checkToken = $this->check_token();
			  if(True)
			  {
					  $response['status']=200;
					  $respStatus = $response['status'];
					  $params 	= json_decode(file_get_contents('php://input'), TRUE);
  
					  $selectkey 	= isset($params['selectkey']) ? $params['selectkey'] : "*"; 
					  $join 		= isset($params['key']) ? $params['key'] : "";
					  $where 		= isset($params['where']) ? $params['where'] : "";
					  $id  = $params['id'];
					  
					  if(isset($params['id'])){
 
						 $sql = 'SELECT 
						 fp_director_details.id as dd_id, 
						 fp_director_types.typename,
						 fp_director_details.ownership,
						 fp_director_details.name ,
						 fp_director_details.date_appointment,
						 fp_director_details.is_promotor as promotor,
						 fp_director_details.about_director as director,
						 fp_director_details.pan,
						 fp_director_details.cibil_score,
						 fp_director_details.ownership,
                         fp_director_details.aadhar
						 FROM 
						 fp_director_details , fp_director_types
						 WHERE 
						 fp_director_types.id = fp_director_details.type && fp_director_details.status = 1 && fp_director_details.borrower_id='.$id;
 
						 $result= $this->db->query($sql)->result();
						 $status = 200;
						 $message = "success";
						 
					  }else{
						 $result = null ;
						 $status = 0;
						 $message = "ID missing Some field missing";
					   }				 
					  $resp = array('status' => $status,'message' => $message ,'data' => $result);
					  return json_output($respStatus,$resp);
			  }
			  else
			  {
				  return json_output(400,array('status' => 400,'message' => "missing authentication"));
			  }
		  
	  }
	  else
	  {
			  return json_output(400,array('status' => 400,'message' => 'Bad request.'));
	  }
  
	  } 

	//   End of directorprofile_details-------------------------------
 
	  
	 public function shareholder_details()
	 {
	  $method = $_SERVER['REQUEST_METHOD'];
	  if($method =="POST")
	  {
			 //  $checkToken = $this->check_token();
			  if(True)
			  {
					  $response['status']=200;
					  $respStatus = $response['status'];
					  $params 	= json_decode(file_get_contents('php://input'), TRUE);
  
					  $selectkey 	= isset($params['selectkey']) ? $params['selectkey'] : "*"; 
					  $join 		= isset($params['key']) ? $params['key'] : "";
					  $where 		= isset($params['where']) ? $params['where'] : "";
					  $id  = $params['id'];
					  
					  if(isset($params['id'])){
 
						 $sql = 'SELECT 
						 fp_director_shareholding.id as sid, 
						 fp_director_shareholding.director_type_id as dsid,
						 fp_shareholder_type.typename,
						 fp_director_shareholding.share_holder_name,
						 fp_director_shareholding.share_date,
						 fp_director_shareholding.share_holding 
						 FROM 
						 fp_shareholder_type,fp_director_shareholding
						 WHERE 
						 fp_director_shareholding.director_type_id = fp_shareholder_type.id && fp_director_shareholding.borrower_id='.$id;
 
						 $result= $this->db->query($sql)->result();
						 $status = 200;
						 $message = "success";
						 
					  }else{
						 $result = null ;
						 $status = 0;
						 $message = "ID missing Some field missing";
					   }				 
					  $resp = array('status' => $status,'message' => $message ,'data' => $result);
					  return json_output($respStatus,$resp);
			  }
			  else
			  {
				  return json_output(400,array('status' => 400,'message' => "missing authentication"));
			  }
		  
	  }
	  else
	  {
			  return json_output(400,array('status' => 400,'message' => 'Bad request.'));
	  }
  
	  } 

	//   End of shareholder_details------------------------------------------------------


	  public function borrowerdashboard()
	  {
	   $method = $_SERVER['REQUEST_METHOD'];
	   if($method =="POST")
	   {
			  //  $checkToken = $this->check_token();
			   if(True)
			   {
					   $response['status']=200;
					   $respStatus = $response['status'];
					   $params 	= json_decode(file_get_contents('php://input'), TRUE);
   
					   $selectkey 	= isset($params['selectkey']) ? $params['selectkey'] : "*"; 
					   $join 		= isset($params['key']) ? $params['key'] : "";
					   $where 		= isset($params['where']) ? $params['where'] : "";
					   $id  = $params['id'];
					   
					   if(isset($params['id'])){
  
						  $sql = 'SELECT  bd.company_name, bd.turnover, bd.networth, bd.profilecomplete, bd.profilecomplete_percentage , bd.gst, bd.cin, bl.name  as location ,t1.name as industry,t2.name as entity , bd.pan
						  FROM fp_borrower_user_details bd LEFT JOIN fp_location bl ON  bd.location=bl.id  LEFT JOIN fp_industry t1 ON t1.id=bd.company_industry LEFT JOIN  fp_entitytype t2 ON t2.id= bd.company_type    
											   WHERE bd.user_id='.$id;
  
						  $result= $this->db->query($sql)->result();
						  $status = 200;
						  $message = "success";
						  
					   }else{
						  $result = null ;
						  $status = 0;
						  $message = "ID missing Some field missing";
						}				 
					   $resp = array('status' => $status,'message' => $message ,'data' => $result);
					   return json_output($respStatus,$resp);
			   }
			   else
			   {
				   return json_output(400,array('status' => 400,'message' => "missing authentication"));
			   }
		   
	   }
	   else
	   {
			   return json_output(400,array('status' => 400,'message' => 'Bad request.'));
	   }
   
	   } 

	//    End of borrowerdashboard-------------------------------------------------------

 
	   public function borrowerdocs()
	  {
	   $method = $_SERVER['REQUEST_METHOD'];
	   if($method =="POST")
	   {
			  //  $checkToken = $this->check_token();
			   if(True)
			   {
					   $response['status']=200;
					   $respStatus = $response['status'];
					   $params 	= json_decode(file_get_contents('php://input'), TRUE);
   
					   $selectkey 	= isset($params['selectkey']) ? $params['selectkey'] : "*"; 
					   $join 		= isset($params['key']) ? $params['key'] : "";
					   $where 		= isset($params['where']) ? $params['where'] : "";
					   $id  = $params['id'];
					   
					   if(isset($params['id'])){
  
						  $sql = 'SELECT * 		 
						  FROM 
						  fp_borrower_docs bd   
						  WHERE bd.borrower_id='.$id;
  
						  $result= $this->db->query($sql)->result();
						  $status = 200;
						  $message = "success";
						  
					   }else{
						  $result = null ;
						  $status = 0;
						  $message = "ID missing Some field missing";
						}				 
					   $resp = array('status' => $status,'message' => $message ,'data' => $result);
					   return json_output($respStatus,$resp);
			   }
			   else
			   {
				   return json_output(400,array('status' => 400,'message' => "missing authentication"));
			   }
		   
	   }
	   else
	   {
			   return json_output(400,array('status' => 400,'message' => 'Bad request.'));
	   }
   
	   }

	//    End of borrowerdocs

	   public function gettotalloanapplicationslender()
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

                $sql = "SELECT count(*) as TotalLoan_Application  FROM`fpa_loan_applications` WHERE loanapplication_status !='inactive' and lendermaster_id=".$where;

                $resp = array('status' => 200, 'message' => 'Success', 'data' => $this->db->query($sql)->result());
                return json_output($respStatus, $resp);
            } else {
                return json_output(400, array('status' => 400, 'message' => "missing authentication"));
            }

        } else {
            return json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        }

    } // End of funciton gettotaladmin_recommended()---------------------------------------------

    public function gettotaladmin_recommended()
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

                $sql = "SELECT count(*) as TotalAdmin_Recommended FROM `fpa_loan_applications` WHERE
                            is_created IN ('A') and lendermaster_id=".$where;

                $resp = array('status' => 200, 'message' => 'Success', 'data' => $this->db->query($sql)->result());
                return json_output($respStatus, $resp);
            } else {
                return json_output(400, array('status' => 400, 'message' => "missing authentication"));
            }

        } else {
            return json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        }

    } // End of funciton gettotaladmin_recommended()---------------------------------------------

    public function gettotalapproved_deals()
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

                $sql = "SELECT count(*) as TotalDeals_ApprovedByLenders FROM `fpa_loan_applications` WHERE workflow_status IN ('Deal Approved','Deal Sanctioned') and lendermaster_id=".$where;

                $resp = array('status' => 200, 'message' => 'Success', 'data' => $this->db->query($sql)->result());
                return json_output($respStatus, $resp);
            } else {
                return json_output(400, array('status' => 400, 'message' => "missing authentication"));
            }

        } else {
            return json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        }

    } // End of funciton gettotalapproved_deals()---------------------------------------------

    public function gettotaldisbursed_deals()
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

                $sql = "SELECT count(*) as TotalDisbursed_Deals FROM `fpa_loan_applications` WHERE
                       workflow_status IN ('Deal Sanctioned') and lendermaster_id=".$where;

                $resp = array('status' => 200, 'message' => 'Success', 'data' => $this->db->query($sql)->result());
                return json_output($respStatus, $resp);
            } else {
                return json_output(400, array('status' => 400, 'message' => "missing authentication"));
            }

        } else {
            return json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        }

    } // End of funciton gettotaldisbursed_deals()---------------------------------------------

    public function gettotalinterest_expressed()
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

                $sql = "SELECT count(*) as TotalInterest_Expressed FROM `fpa_loan_applications` WHERE
                       lender_intrest_received='yes' and  lendermaster_id=".$where;

                $resp = array('status' => 200, 'message' => 'Success', 'data' => $this->db->query($sql)->result());
                return json_output($respStatus, $resp);
            } else {
                return json_output(400, array('status' => 400, 'message' => "missing authentication"));
            }

        } else {
            return json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        }

    } // End of funciton gettotalinterest_expressed()---------------------------------------------

    public function gettotaldiscussion_initiated()
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

                $sql = "SELECT count(*) as TotalDiscussion_Initiated FROM `fpa_loan_applications` WHERE
                       workflow_status IN ('Discussion Initiated') and lendermaster_id=".$where;

                $resp = array('status' => 200, 'message' => 'Success', 'data' => $this->db->query($sql)->result());
                return json_output($respStatus, $resp);
            } else {
                return json_output(400, array('status' => 400, 'message' => "missing authentication"));
            }

        } else {
            return json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        }

    } // End of funciton gettotaldiscussion_initiated()---------------------------------------------

    public function gettotalapproved_amount()
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

                $sql = "SELECT sum(approved_amount) as TotalAmount_Approved FROM `fpa_loan_applications`
                       WHERE workflow_status IN ('Deal Approved') and lendermaster_id=".$where;

                $resp = array('status' => 200, 'message' => 'Success', 'data' => $this->db->query($sql)->result());
                return json_output($respStatus, $resp);
            } else {
                return json_output(400, array('status' => 400, 'message' => "missing authentication"));
            }

        } else {
            return json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        }

    } // End of funciton gettotalapproved_amount()---------------------------------------------

    public function gettotaldisbursed_amount()
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

                $sql = "SELECT sum(sanctioned_amount) as TotalDisbursed_Amount FROM `fpa_loan_applications` WHERE workflow_status IN ('Deal Sanctioned') and lendermaster_id =".$where;

                $resp = array('status' => 200, 'message' => 'Success', 'data' => $this->db->query($sql)->result());
                return json_output($respStatus, $resp);
            } else {
                return json_output(400, array('status' => 400, 'message' => "missing authentication"));
            }

        } else {
            return json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        }

    } // End of funciton gettotaldisbursed_amount()---------------------------------------------; 


	public function lenderdashboard(){
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

                $sql = "SELECT t4.loan_min,t4.loan_max, t2.name as productname, t5.name, t3.turnover, t3.networth, t5.name, t3.company_name,t1.is_created,t3.user_id as borrower_id,t1.loanapplication_status as lastatus, t1.lender_intrest_received as lender_intrest_received, t1.id as loan_app_id, t1.loanrequest_id as lrid  FROM fpa_loan_applications t1  LEFT JOIN fp_products t2 ON t1.product_slug=t2.slug LEFT JOIN   fp_borrower_user_details t3 ON t3.user_id = t1.borrower_id LEFT JOIN  fp_borrower_loanrequests t4 ON t1.loanrequest_id=t4.id  LEFT JOIN fp_entitytype t5 ON t5.id=t3.company_type   WHERE t1.loanapplication_status in ('Deal Sent To Lender','New Loan','Express Interest','Discussion Initiated') $where";

                $resp = array('status' => 200, 'message' => 'Success', 'data' => $this->db->query($sql)->result());
                return json_output($respStatus, $resp);
            } else {
                return json_output(400, array('status' => 400, 'message' => "missing authentication"));
            }

        } else {
            return json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        }


	}




	public function profilepercentage()
   {
				$method = $_SERVER['REQUEST_METHOD'];
				if($method =="POST")
				{
				// $checkToken = $this->check_token();
				if(True)
				{
					$response['status']=200;
					$respStatus = $response['status'];
					$params  = json_decode(file_get_contents('php://input'), TRUE);

					$selectkey  = isset($params['selectkey']) ? $params['selectkey'] : "*"; 
					$join   = isset($params['key']) ? $params['key'] : "";
					$where   = isset($params['where']) ? $params['where'] : "";
					$percentages  = ($params['percentage']); 
					$borrower_id =isset($params['borrower_id'])? $params['borrower_id'] : "";
					// print_r($borrower_id);
					// print_r("7777777777777777777777777777777");
					// print_r($percentages);
					
					$fp_borrower= "UPDATE fp_borrower_user_details
					SET  profilecomplete_percentage=".$percentages." WHERE  fp_borrower_user_details.user_id=".$borrower_id." ";
					$percentage = $this->db->query($fp_borrower);			


					if ($percentages>=100){

					$complete= "UPDATE fp_borrower_user_details
					SET profilecomplete='completed'  WHERE  fp_borrower_user_details.user_id=".$borrower_id." ";
					$percentages = $this->db->query($complete);
					}  
				}
				else
				{
					return json_output(400,array('status' => 400,'message' => "missing authentication"));
				}
				
					}
					else
					{
				return json_output(400,array('status' => 400,'message' => 'Bad request.'));
					}

    
    }

	// End of profilepercentage

	public function borrowerdashboardproduct()
	{
	 $method = $_SERVER['REQUEST_METHOD'];
	 if($method =="POST")
	 {
			//  $checkToken = $this->check_token();
			 if(True)
			 {
					 $response['status']=200;
					 $respStatus = $response['status'];
					 $params 	= json_decode(file_get_contents('php://input'), TRUE);
 
					 $selectkey 	= isset($params['selectkey']) ? $params['selectkey'] : "*"; 
					 $join 		= isset($params['key']) ? $params['key'] : "";
					 $where 		= isset($params['where']) ? $params['where'] : "";
					 $id  = $params['id'];
					 
					 if(isset($params['id'])){

						$sql = 'SELECT pd.name FROM fp_borrower_loanrequests la, fp_products pd WHERE pd.slug=la.product_slug AND 
						la.borrower_id='.$id;

						$result= $this->db->query($sql)->result();
						$status = 200;
						$message = "success";
						
					 }else{
						$result = null ;
						$status = 0;
						$message = "ID missing Some field missing";
					  }				 
					 $resp = array('status' => $status,'message' => $message ,'data' => $result);
					 return json_output($respStatus,$resp);
			 }
			 else
			 {
				 return json_output(400,array('status' => 400,'message' => "missing authentication"));
			 }
		 
	 }
	 else
	 {
			 return json_output(400,array('status' => 400,'message' => 'Bad request.'));
	 }
 
	 } 

  //    End of borrowerdashboardproduct-------------------------------------------------------


  public function borrowerloanapplications()
  {
   $method = $_SERVER['REQUEST_METHOD'];
   if($method =="POST")
   {
		  //  $checkToken = $this->check_token();
		   if(True)
		   {
				   $response['status']=200;
				   $respStatus = $response['status'];
				   $params 	= json_decode(file_get_contents('php://input'), TRUE);

				   $selectkey 	= isset($params['selectkey']) ? $params['selectkey'] : "*"; 
				   $join 		= isset($params['key']) ? $params['key'] : "";
				   $where 		= isset($params['where']) ? $params['where'] : "";
				   $id  = $params['id'];
				   
				   if(isset($params['id'])){

					  $sql = "SELECT t4.loan_min,t4.loan_max, t4.roi_min, t4.roi_max, t4.tenor_min, t4.tenor_max,t2.name as productname, t5.name, t3.company_name,t1.is_created,t3.user_id as borrower_id,t1.loanapplication_status as lastatus,t1.workflow_status, t1.lender_intrest_received as lender_intrest_received, t1.id as loan_app_id, t1.loanrequest_id as lrid  FROM fpa_loan_applications t1  LEFT JOIN fp_products t2 ON t1.product_slug=t2.slug LEFT JOIN   fp_borrower_user_details t3 ON t3.user_id = t1.borrower_id LEFT JOIN  fp_borrower_loanrequests t4 ON t1.loanrequest_id=t4.id  LEFT JOIN fp_entitytype t5 ON t5.id=t3.company_type   WHERE t1.loanapplication_status='CC Approved' and t1.workflow_status='CC Approved'  and t1.borrower_id=".$id;

					  $result= $this->db->query($sql)->result();
					  $status = 200;
					  $message = "success";
					  
				   }else{
					  $result = null ;
					  $status = 0;
					  $message = "ID missing Some field missing";
					}				 
				   $resp = array('status' => $status,'message' => $message ,'data' => $result);
				   return json_output($respStatus,$resp);
		   }
		   else
		   {
			   return json_output(400,array('status' => 400,'message' => "missing authentication"));
		   }
	   
   }
   else
   {
		   return json_output(400,array('status' => 400,'message' => 'Bad request.'));
   }

   }

//    End of borrowerloanapplications


public function borrower_pincode()
{
 $method = $_SERVER['REQUEST_METHOD'];
 if($method =="POST")
 {
	
		 if(True)
		 {
				 $response['status']=200;
				 $respStatus = $response['status'];
				 $params 	= json_decode(file_get_contents('php://input'), TRUE);

				 $selectkey 	= isset($params['selectkey']) ? $params['selectkey'] : "*"; 
				 $join 		= isset($params['key']) ? $params['key'] : "";
				 $where 		= isset($params['where']) ? $params['where'] : "";
				 $id  = $params['id'];
				 $pincode  = $params['pincode'];
				 
				 if(isset($params['id'])){

					$sql = "SELECT 
					bud.id, 
					loc.name as locationname, 
					ct.name as cityname,
					dt.name as districtname, 
					st.name as statename 
					FROM 
					fp_borrower_user_details bud,fp_location loc, fp_city ct, fp_district dt, fp_state st 
					WHERE
					bud.api_pincode=loc.pincode and 
					loc.city_id=ct.id and 
					ct.district_id=dt.id and 
					dt.state_id=st.id 
					and bud.api_pincode= $pincode and bud.user_id= $id ";

					$result= $this->db->query($sql)->result();
					$status = 200;
					$message = "success";
					
				 }else{
					$result = null ;
					$status = 0;
					$message = "ID missing Some field missing";
				  }				 
				 $resp = array('status' => $status,'message' => $message ,'data' => $result);
				 return json_output($respStatus,$resp);
		 }
		 else
		 {
			 return json_output(400,array('status' => 400,'message' => "missing authentication"));
		 }
	 
 }
 else
 {
		 return json_output(400,array('status' => 400,'message' => 'Bad request.'));
 }

 } 

//   End of borrower_pincode-------------------------------



}//--------------------end of class-------------------------------------------
