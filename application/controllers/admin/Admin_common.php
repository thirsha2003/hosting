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
class Admin_common extends CI_Controller 
{
			public function __construct(){
				parent::__construct();
				$this->load->helper('json_output');		
			
					$this->ci =& get_instance();
					$this->ci->load->database();
				
			}  // construct 
			public function check_token(){
				$check_auth_user  = $this->login->check_auth_user();
				if($check_auth_user){
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
				}else{
					return false;
				}	
			} // check_token 
			public function get_details(){
				$method = $_SERVER['REQUEST_METHOD'];
				if($method != 'POST'){
				json_output(400,array('status' => 400,'message' => 'Bad request.'));
				}else{
					$checkToken = $this->check_token();
					if($checkToken){
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
			public function profilecompletionstatusupdate(){
						 $method = $_SERVER['REQUEST_METHOD'];
						 if($method =="POST")
						 {
									$checkToken = $this->check_token();
									if(True)
									{
												$response['status']=200;
												$respStatus = $response['status'];
												$params  = json_decode(file_get_contents('php://input'), TRUE);
							
												$selectkey  = isset($params['selectkey']) ? $params['selectkey'] : "*"; 
												$join   = isset($params['key']) ? $params['key'] : "";
												$where   = isset($params['where']) ? $params['where'] : "";
												$activity  = ($params['activity']); 
												$borrower_id =($params['borrower_id']);
												$rm_email =($params['rm_email']);
											
												
												$sqltaskid="SELECT id FROM fpa_taskdetails 
												WHERE borrower_id=".$borrower_id. " AND rm_email='".$rm_email."'";

												$taskdetails_id = $this->db->query($sqltaskid)->row();	
																							
												$inarr = array('taskdetail_id' => $taskdetails_id->id, 'activity' => $activity, 'activity_remarks' => $activity,   'created_by' => $rm_email);
												$this->db->insert("fpa_taskdetails_worklog", $inarr);
																			
									}
									else
									{
										return json_output(400,array('status' => 400,'message' => $checkToken));
									}
						}
						else
						{
						 			return json_output(400,array('status' => 400,'message' => 'Bad request.'));
						}
			 
		}  //profilecompletionstatusupdate
			public function addborrower()
			{
				$method = $_SERVER['REQUEST_METHOD'];
				if($method != 'POST'){
				json_output(400,array('status' => 400,'message' => 'Bad request.'));
				}else{
				$checkToken = $this->check_token();
				if($checkToken){
				$response['status']=200;
				$respStatus = $response['status'];
				$params = json_decode(file_get_contents('php://input'), TRUE);
				try{
				$name = $params['data']['name'];
				$email = $params['data']['email'];
				$phone = $params['data']['mobile'];
				$entity_type = $params ['data']['entity_type'];
				$created_by =  $params['data']['created_by'];
				$company_name = isset($params['data']['company_name'])?$params['data']['company_name']:null;
				$emailandmobileverified =1;
				$add_user = $this->db->insert("fpa_users",array('name'=>$name,'email'=>$email, 'mobile'=>$phone ,'slug'=>'borrower', 'company_name'=>$company_name,'created_by'=>$created_by,'is_email_verified'=>$emailandmobileverified,'is_mobile_verified'=>$emailandmobileverified));
				$id = $this->db->insert_id();
				
				
				$add_borrower =$this->db->insert("fp_borrower_user_details", array('user_id'=>$id,'name'=>$name,'email'=>$email, 'phone'=>$phone,'company_name'=>$company_name,'company_type'=>$entity_type));
				if($add_user && $add_borrower){
				json_output(200,array('status' => 200,'message' => 'successfully Added',"data"=>$id));
               
				        $subject = "Dear Superadmin,";
                        $message = "Dear Superadmin,"."<br/>"."<br/>"."<br/>"."A new application for ".$name." has been created by the ".$created_by." .
                        Please click on the below link to view ".$name." or assign the same ."."<br/>"."<br/>".
                        "link : app.finnup.in/#/admin.";
                       
                        
                        // $to = 'aisha@finnup.in'; 
                        $to = 'parthibangnc51@gmail.com'; 

                        // $tos = "rahul@finnup.in";

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
				json_output(200,array('status' => 400,'message' => 'Bad request.'));
				}
				}catch(Exception $e){
				json_output(200,array('status' => 401,'message' => $e->getMessage()));
				}
				}else{
				json_output(400,array('status' => 400,'message' => 'Bad request.'));
				}
				}
			}	 // addborrower

			public function addborrowerold(){
				$method = $_SERVER['REQUEST_METHOD'];
				if($method != 'POST'){
				json_output(400,array('status' => 400,'message' => 'Bad request.'));
				}else{
					$checkToken = $this->check_token();
					if($checkToken){
					$response['status']=200;
					$respStatus = $response['status'];
					$params = json_decode(file_get_contents('php://input'), TRUE);
					try{
						$add_user = $this->db->insert("fpa_users", $params['data']);
						$id = $this->db->insert_id();
						$name = $params['data']['name'];
						$email = $params['data']['email'];
						$phone = $params['data']['mobile'];
						$add_borrower =$this->db->insert("fp_borrower_user_details", array('user_id'=>$id,'name'=>$name,'email'=>$email, 'phone'=>$phone));
						if($add_user && $add_borrower){
							json_output(200,array('status' => 200,'message' => 'successfully Added'));
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
			}  //  addborrowerold
			public function borrower_total(){
				$method = $_SERVER['REQUEST_METHOD'];
				if($method != 'POST'){
				json_output(400,array('status' => 400,'message' => 'Bad request.'));
				}else{
					$checkToken = $this->check_token();
					if(True){
					$response['status']=200;
					$respStatus = $response['status'];
					$params = json_decode(file_get_contents('php://input'), TRUE);
					$selectkey = isset($params['selectkey']) ? $params['selectkey'] : "*"; 
					$join = isset($params['key']) ? $params['key'] : "";
					$where = isset($params['where']) ? $params['where'] : "";	
					$sql = "SELECT count(*) as borrower_onboarded FROM `fpa_users` WHERE slug = 'borrower'";
					$resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql)->result());
					json_output($respStatus,$resp);
				}else{
					json_output(400,array('status' => 400,'message' => $checkToken));
				}
				}
			} // borrower_total
			public function lender_total()
			{
				$method = $_SERVER['REQUEST_METHOD'];
				if($method != 'POST'){
				json_output(400,array('status' => 400,'message' => 'Bad request.'));
				}else{
					$checkToken = $this->check_token();
					if(True){
					$response['status']=200;
					$respStatus = $response['status'];
					$params = json_decode(file_get_contents('php://input'), TRUE);
					$selectkey = isset($params['selectkey']) ? $params['selectkey'] : "*"; 
					$join = isset($params['key']) ? $params['key'] : "";
					$where = isset($params['where']) ? $params['where'] : "";	
					$sql = "SELECT count(*) as lender_register FROM `fpa_users` WHERE slug = 'lender'";
					$resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql)->result());
					json_output($respStatus,$resp);
				}else{
					json_output(400,array('status' => 400,'message' => $checkToken));
				}
				}
			} // lender_total
			// Code by V&I
			public function gettotalonboardedborrowers()
			{
					$method = $_SERVER['REQUEST_METHOD'];
					if($method =="POST")
					{
							$checkToken = $this->check_token();
							if(True)
							{
									$response['status']=200;
									$respStatus = $response['status'];
									$params 	= json_decode(file_get_contents('php://input'), TRUE);

									$selectkey 	= isset($params['selectkey']) ? $params['selectkey'] : "*"; 
									$join 		= isset($params['key']) ? $params['key'] : "";
									$where 		= isset($params['where']) ? $params['where'] : "";	

									$sql = "SELECT count(*) as TotalBorrowers_Onboarded FROM `fpa_users` WHERE 
									slug ='borrower' and status IN ('new','assigned','active')";
									$resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql)->result());
									return json_output($respStatus,$resp);
							}
							else
							{
								return json_output(400,array('status' => 400,'message' => $checkToken));
							}
						
					}
					else
					{
							return json_output(400,array('status' => 400,'message' => 'Bad request.'));
					}
				
			} // End of funciton gettotalonboardedborrowers()---------------------------------------------
			public function gettotalregisteredlenders()
			{
					$method = $_SERVER['REQUEST_METHOD'];
					if($method =="POST")
					{
							$checkToken = $this->check_token();
							if(True)
							{
									$response['status']=200;
									$respStatus = $response['status'];
									$params 	= json_decode(file_get_contents('php://input'), TRUE);

									$selectkey 	= isset($params['selectkey']) ? $params['selectkey'] : "*"; 
									$join 		= isset($params['key']) ? $params['key'] : "";
									$where 		= isset($params['where']) ? $params['where'] : "";	

									$sql = "SELECT count(*) as TotalLenders_Registered FROM `fpa_users` WHERE slug ='lender' and status IN ('new','assigned','active')";
									$resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql)->result());
									return json_output($respStatus,$resp);
							}
							else
							{
								return json_output(400,array('status' => 400,'message' => $checkToken));
							}
						
					}
					else
					{
							return json_output(400,array('status' => 400,'message' => 'Bad request.'));
					}
				
			} // End of funciton gettotalregisteredlenders()---------------------------------------------
			public function gettotalloanapplications()
			{
					$method = $_SERVER['REQUEST_METHOD'];
					if($method =="POST")
					{
							$checkToken = $this->check_token();
							if(True)
							{
									$response['status']=200;
									$respStatus = $response['status'];
									$params 	= json_decode(file_get_contents('php://input'), TRUE);

									$selectkey 	= isset($params['selectkey']) ? $params['selectkey'] : "*"; 
									$join 		= isset($params['key']) ? $params['key'] : "";
									$where 		= isset($params['where']) ? $params['where'] : "";	

									$sql = "SELECT count(*) as TotalLoanApplications_Requested FROM `fpa_loan_applications` WHERE loanapplication_status !='inactive'";
									$resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql)->result());
									return json_output($respStatus,$resp);
							}
							else
							{
								return json_output(400,array('status' => 400,'message' => $checkToken));
							}
						
					}
					else
					{
							return json_output(400,array('status' => 400,'message' => 'Bad request.'));
					}
				
			} // End of funciton gettotalloanapplications()---------------------------------------------

			public function gettotaldealssenttolenders()
			{
					$method = $_SERVER['REQUEST_METHOD'];
					if($method =="POST")
					{
							$checkToken = $this->check_token();
							if(True)
							{
									$response['status']=200;
									$respStatus = $response['status'];
									$params 	= json_decode(file_get_contents('php://input'), TRUE);

									$selectkey 	= isset($params['selectkey']) ? $params['selectkey'] : "*"; 
									$join 		= isset($params['key']) ? $params['key'] : "";
									$where 		= isset($params['where']) ? $params['where'] : "";	

									$sql = "SELECT count(*) as TotalDeals_SentToLenders FROM `fpa_loan_applications` WHERE 
									workflow_status =  'Deal Sent to Lender' AND loanapplication_status IN ('new','assigned','active')";

									$resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql)->result());
									return json_output($respStatus,$resp);
							}
							else
							{
								return json_output(400,array('status' => 400,'message' => $checkToken));
							}
						
					}
					else
					{
							return json_output(400,array('status' => 400,'message' => 'Bad request.'));
					}
				
			} // End of funciton gettotaldealssenttolenders()---------------------------------------------


			public function gettotaldealsapprovedbylenders()
			{
					$method = $_SERVER['REQUEST_METHOD'];
					if($method =="POST")
					{
							$checkToken = $this->check_token();
							if(True)
							{
									$response['status']=200;
									$respStatus = $response['status'];
									$params 	= json_decode(file_get_contents('php://input'), TRUE);

									$selectkey 	= isset($params['selectkey']) ? $params['selectkey'] : "*"; 
									$join 		= isset($params['key']) ? $params['key'] : "";
									$where 		= isset($params['where']) ? $params['where'] : "";	

									$sql = "SELECT count(*) as TotalDeals_ApprovedByLenders FROM `fpa_loan_applications` WHERE 
									workflow_status =  'Deals Approved' AND loanapplication_status ='active'";
									
									$resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql)->result());
									return json_output($respStatus,$resp);
							}
							else
							{
								return json_output(400,array('status' => 400,'message' => $checkToken));
							}
						
					}
					else
					{
							return json_output(400,array('status' => 400,'message' => 'Bad request.'));
					}
				
			} // End of funcitongettotaldealsapprovedbylenders()---------------------------------------------

			public function gettotaldealssanctionedbylenders()
			{
					$method = $_SERVER['REQUEST_METHOD'];
					if($method =="POST")
					{
							$checkToken = $this->check_token();
							if(True)
							{
									$response['status']=200;
									$respStatus = $response['status'];
									$params 	= json_decode(file_get_contents('php://input'), TRUE);

									$selectkey 	= isset($params['selectkey']) ? $params['selectkey'] : "*"; 
									$join 		= isset($params['key']) ? $params['key'] : "";
									$where 		= isset($params['where']) ? $params['where'] : "";	

									$sql = "SELECT count(*) as TotalDeals_SanctionedByLenders FROM `fpa_loan_applications` WHERE 
									workflow_status =  'Deals Sanctioned' AND loanapplication_status ='active'";
									
									$resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql)->result());
									return json_output($respStatus,$resp);
							}
							else
							{
								return json_output(400,array('status' => 400,'message' => $checkToken));
							}
						
					}
					else
					{
							return json_output(400,array('status' => 400,'message' => 'Bad request.'));
					}
				
			} // End of funciton gettotaldealssanctionedbylenders()---------------------------------------------

			public function gettotalapprovedamount()
			{
					$method = $_SERVER['REQUEST_METHOD'];
					if($method =="POST")
					{
							$checkToken = $this->check_token();
							if(True)
							{
									$response['status']=200;
									$respStatus = $response['status'];
									$params 	= json_decode(file_get_contents('php://input'), TRUE);

									$selectkey 	= isset($params['selectkey']) ? $params['selectkey'] : "*"; 
									$join 		= isset($params['key']) ? $params['key'] : "";
									$where 		= isset($params['where']) ? $params['where'] : "";	

									$sql = "SELECT sum(approved_amount) as TotalAmount_Approved FROM `fpa_loan_applications` 
									WHERE workflow_status='deals approved' AND loanapplication_status ='active' ";
									
									$resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql)->result());
									return json_output($respStatus,$resp);
							}
							else
							{
								return json_output(400,array('status' => 400,'message' => $checkToken));
							}
						
					}
					else
					{
							return json_output(400,array('status' => 400,'message' => 'Bad request.'));
					}
				
			} // End of funciton gettotalApprovedAmount()---------------------------------------------

			public function gettotalsanctionedamount()
			{
					$method = $_SERVER['REQUEST_METHOD'];
					if($method =="POST")
					{
							$checkToken = $this->check_token();
							if(True)
							{
									$response['status']=200;
									$respStatus = $response['status'];
									$params 	= json_decode(file_get_contents('php://input'), TRUE);

									$selectkey 	= isset($params['selectkey']) ? $params['selectkey'] : "*"; 
									$join 		= isset($params['key']) ? $params['key'] : "";
									$where 		= isset($params['where']) ? $params['where'] : "";	

									$sql = "SELECT sum(sanctioned_amount) as TotalDeals_SanctionedByLenders FROM `fpa_loan_applications` WHERE 
									workflow_status =  'deals sanctioned' AND loanapplication_status ='active'";
									
									$resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql)->result());
									return json_output($respStatus,$resp);
							}
							else
							{
								return json_output(400,array('status' => 400,'message' => $checkToken));
							}
						
					}
					else
					{
							return json_output(400,array('status' => 400,'message' => 'Bad request.'));
					}
				
			} // End of funciton gettotalSanctionedAmount()---------------------------------------------
	       public function taskassign_old()
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
				
				// $this->db->trans_start();
				 $id='';
				 $task_details = $this->db->insert("fpa_taskdetails", $params['data']);
				 $id = $this->db->insert_id();  
				
				
				 $sql =  "select borrower_id, id from fpa_taskdetails where fpa_taskdetails.id=".$id;
				 $taskdata = $this->db->query($sql)->row();
			
				 $task_details_worklog =$this->db->insert("fpa_taskdetails_worklog",array('taskdetail_id'=>$taskdata->id)); 
				    
				 $fpa_users = "UPDATE fpa_users SET status ='assigned' WHERE fpa_users.slug ='borrower' AND fpa_users.id=".$taskdata->borrower_id;
                 $fpa_user = $this->db->update('fpa_users',$fpa_users);
				
				 
				// $this->db->trans_complete();

				if($task_details && $task_details_worklog && $fpa_users){
					json_output(200,array('status' => 200,'message' => ' Task Assign '));
				}else{
					json_output(200,array('status' => 400,'message' => 'Bad1111 request.'));
				}
			}catch(Exception $e){
				json_output(200,array('status' => 401,'message' => $e->getMessage()));
			}
			}else{
				json_output(400,array('status' => 400,'message' => 'Bad request.'));
			}
		}
	       } // taskassign_old 

	       public function taskassign()
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
		  
		  //  $this->db->trans_start(); 
	  
		  // $this->db->trans_begin();  
		  $id='';
	  
				   $task_details = $this->db->insert("fpa_taskdetails", $params['data']);
				   $id = $this->db->insert_id();  
		  
				   
		   $sql =  "select borrower_id,id,task_assigned_to
		   from fpa_taskdetails where fpa_taskdetails.id=".$id; 
					$taskdata = $this->db->query($sql)->row();
		   
	  
		   $assigndata =  "select name,email,id
		   from fpa_adminusers where fpa_adminusers.id=".$taskdata->task_assigned_to;
	  
		   $rmdata = $this->db->query($assigndata)->row();
					$task_details_worklog =$this->db->insert("fpa_taskdetails_worklog",array('taskdetail_id'=>$taskdata->id)); 
					$fpa_users = "UPDATE fpa_users 
		   SET status ='assigned', rm_id='".$rmdata->id."',".
		   "rm_name='".$rmdata->name."',".
		   "rm_email='".$rmdata->email."' WHERE fpa_users.id=".$taskdata->borrower_id;


		   
                 $rm_name=$rmdata->name;
                 $company_name = $params['company_name'];

			
		  $checkdata = $this->db->query($fpa_users);
		
		  // if ($this->db->trans_status() === FALSE)
		  // {
		  //   $this->db->trans_rollback();
		  // }
		  // else
		  // {
		  //   $this->db->trans_commit();
		  // }
			  
		   
		  //  $this->db->trans_complete();
	  
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
    } // taskassign 



	      public function borrower_user_detail()
	      {
			$method = $_SERVER['REQUEST_METHOD'];
			if($method =="POST")
			{
					$checkToken = $this->check_token();
					if(True)
					{
							$response['status']=200;
							$respStatus = $response['status'];
							$params 	= json_decode(file_get_contents('php://input'), TRUE);

							$selectkey 	= isset($params['selectkey']) ? $params['selectkey'] : "*"; 
							$join 		= isset($params['key']) ? $params['key'] : "";
							$where 		= isset($params['where']) ? $params['where'] : "";	

							$sql = "WITH borrowerTable as (SELECT b.slug, b.id, bd.company_industry, bd.company_name, bd.turnover, bd.networth, bd.company_type, bd.profilecomplete, b.rm_name, bd.city FROM fpa_users b, fp_borrower_user_details bd WHERE b.slug ='borrower' AND b.status in ('new','assigned','active') AND b.id = bd.user_id AND bd.company_name is not null) SELECT bd.rm_name ,  bd.slug, bd.profilecomplete ,bd.city,fp_entitytype.id,bd.id as borrower_id,fp_city.id as location_id, fp_city.name as location, fp_entitytype.name as entity_name,bd.company_name as company_name, bd.company_industry as company_industry,bd.turnover, bd.networth FROM borrowerTable as bd LEFT JOIN fp_city ON bd.city = fp_city.id LEFT JOIN fp_entitytype ON bd.company_type = fp_entitytype.id where bd.company_name is not null order by bd.id desc ".$where;

							$borrowerdetails = $this->db->query($sql)->result(); 
							$data = $this->db->query($sql);
							foreach ($data->result() as $row){
								$txnArr[] = $row->borrower_id;
								
									
								
							}
							$res = implode(",",$txnArr);
							$res  = "(".$res.")";

							$result = 'SELECT bl.product_slug,bl.borrower_id,p.name  FROM fp_borrower_loanrequests bl ,fp_products p WHERE bl.product_slug=p.slug and bl.borrower_id in '.$res;

						
							// $this->db->query($sql)-result();
							// $query = $this->db->get_where('fp_borrower_loanrequests', array('borrower_id' => $res))->result();
							// $trnn[]= $data->id;

							$resp = array('status' => 200,'message' =>  'Success','data'=> $borrowerdetails,'data1' =>$this->db->query($result)->result());
							return json_output($respStatus,$resp);
					}
					else
					{
						return json_output(400,array('status' => 400,'message' => $checkToken));
					}
				
			}
			else
			{
					return json_output(400,array('status' => 400,'message' => 'Bad request.'));
			}
		
	      } // borrower_user_detail


	     public function borrower_profile_details()
	     {
	  $method = $_SERVER['REQUEST_METHOD'];
	  if($method =="POST")
	  {
		$checkToken = $this->check_token();
		if(True)
		{
		  $response['status']=200;
		  $respStatus = $response['status'];
		  $params  = json_decode(file_get_contents('php://input'), TRUE);
   
		  $selectkey  = isset($params['selectkey']) ? $params['selectkey'] : "*"; 
		  $join   = isset($params['key']) ? $params['key'] : "";
		  $where   = isset($params['where']) ? $params['where'] : ""; 
		  $id  = ($params['id']) ;
		  
		  
   
		  $sql = "WITH borrowerTable as (SELECT b.slug, b.id, bd.company_industry, bd.company_name, bd.turnover, bd.networth, bd.company_type, bd.profilecomplete, b.rm_name, bd.location ,bd.profilecomplete_percentage FROM fpa_users b, fp_borrower_user_details bd WHERE b.slug ='borrower' AND b.status in ('new','assigned','active') AND b.id = bd.user_id AND bd.company_name is not null AND b.id=".$id.") SELECT bd.slug, bd.profilecomplete_percentage, bd.profilecomplete ,bd.location,fp_entitytype.id,bd.id as borrower_id,fp_location.id as location_id, fp_location.name as location, fp_entitytype.name as entity_name,bd.company_name as company_name, bd.company_industry as company_industry,bd.turnover, bd.networth,fp_industry.name as fp_industry FROM borrowerTable as bd LEFT JOIN fp_location ON bd.location = fp_location.id LEFT JOIN fp_entitytype ON bd.company_type = fp_entitytype.id LEFT JOIN fp_industry ON bd.company_industry = fp_industry.id where bd.company_name is not null order by bd.id desc;";
   
   
		  $sqldata = 'SELECT bl.product_slug,bl.borrower_id,p.name  FROM fp_borrower_loanrequests bl ,fp_products p WHERE bl.product_slug =p.slug and bl.borrower_id = '.$id;
		  $resultdata = $this->db->query($sqldata)->result(); 
   
		  
		  $resp = array('status' => 200,'message' =>  'Success', 'data1'=>$resultdata,'data' => $this->db->query($sql)->result());
		  return json_output($respStatus,$resp);
		  
		  
		}
		else
		{
		 return json_output(400,array('status' => 400,'message' => $checkToken));
		}
	   
	  }
	  else
	  {
		return json_output(400,array('status' => 400,'message' => 'Bad request.'));
	  }
	 
	     } // borrower_profile_details 
     
	    public function borrower_newleads()
	    {
		$method = $_SERVER['REQUEST_METHOD'];
		if($method =="POST")
		{
			$checkToken = $this->check_token();
			if(True)
			{
				$response['status']=200;
				$respStatus = $response['status'];
				$params   = json_decode(file_get_contents('php://input'), TRUE);
  
				$selectkey   = isset($params['selectkey']) ? $params['selectkey'] : "*"; 
				$join     = isset($params['key']) ? $params['key'] : "";
				$where     = isset($params['where']) ? $params['where'] : "";  
  
				$sql = "WITH borrowerTable as (SELECT b.slug, b.id, bd.company_industry, bd.company_name, bd.turnover, bd.networth, bd.company_type, bd.profilecomplete, b.rm_name, bd.city, b.status FROM fpa_users b, fp_borrower_user_details bd WHERE b.slug ='borrower' AND b.status in ('new') AND b.id = bd.user_id AND bd.company_name is not null) SELECT bd.rm_name , bd.status,  bd.slug, bd.profilecomplete ,bd.city,fp_entitytype.id,bd.id as borrower_id,fp_city.id as location_id, fp_city.name as location, fp_entitytype.name as entity_name,bd.company_name as company_name, bd.company_industry as company_industry,bd.turnover, bd.networth FROM borrowerTable as bd LEFT JOIN fp_city ON bd.city = fp_city.id LEFT JOIN fp_entitytype ON bd.company_type = fp_entitytype.id where bd.company_name is not null order by bd.id desc";
  
				$borrowerdetails = $this->db->query($sql)->result(); 
				$data = $this->db->query($sql);
				
				foreach ($data->result() as $row){
				  $txnArr[] = $row->borrower_id;					
				  
				}
				
				
				$res = implode(",",$txnArr);
							$res  = "(".$res.")";

							$result = 'SELECT bl.product_slug,bl.borrower_id,p.name  FROM fp_borrower_loanrequests bl ,fp_products p WHERE bl.product_slug=p.slug and bl.borrower_id in '.$res;
  
			  
				// $this->db->query($sql)-result();
				// $query = $this->db->get_where('fp_borrower_loanrequests', array('borrower_id' => $res))->result();
				// $trnn[]= $data->id;
  
				$resp = array('status' => 200,'message' =>  'Success','data'=> $borrowerdetails,'data1' =>$this->db->query($result)->result());
				return json_output($respStatus,$resp);
			}
			else
			{
			  return json_output(400,array('status' => 400,'message' => $checkToken));
			}
		  
		}
		else
		{
			return json_output(400,array('status' => 400,'message' => 'Bad request.'));
		}
	  
	    } // borrower_newleads 


	public function borrower_leads_fullcomplete()
	{
		$method = $_SERVER['REQUEST_METHOD'];
		if($method =="POST")
		{
			$checkToken = $this->check_token();
			if(True)
			{
				$response['status']=200;
				$respStatus = $response['status'];
				$params   = json_decode(file_get_contents('php://input'), TRUE);
  
				$selectkey   = isset($params['selectkey']) ? $params['selectkey'] : "*"; 
				$join     = isset($params['key']) ? $params['key'] : "";
				$where     = isset($params['where']) ? $params['where'] : "";  
  
				$sql = "WITH borrowerTable as (SELECT b.slug, b.id, bd.company_industry, bd.company_name, bd.turnover, bd.networth, bd.company_type, bd.profilecomplete,bd.profilecomplete_percentage, b.rm_name, bd.city, b.status FROM fpa_users b, fp_borrower_user_details bd 
				WHERE b.slug ='borrower'  AND b.id = bd.user_id AND bd.company_name is not null AND bd.profilecomplete = 'completed' AND bd.profilecomplete_percentage =100) 
				SELECT bd.rm_name , bd.status,  bd.slug, bd.profilecomplete ,bd.profilecomplete_percentage,bd.city,fp_entitytype.id,bd.id as borrower_id,fp_city.id as location_id, fp_city.name as location, fp_entitytype.name as entity_name,bd.company_name as company_name, bd.company_industry as company_industry,bd.turnover, bd.networth 
				FROM borrowerTable as bd LEFT JOIN fp_city ON bd.city = fp_city.id LEFT JOIN fp_entitytype ON bd.company_type = fp_entitytype.id where bd.company_name is not null order by bd.id desc;";
  
				$borrowerdetails = $this->db->query($sql)->result(); 
				$data = $this->db->query($sql);
				
				foreach ($data->result() as $row){
				  $txnArr[] = $row->borrower_id;					
				  
				}
				
				
				$res = implode(",",$txnArr);
							$res  = "(".$res.")";

							$result = 'SELECT bl.product_slug,bl.borrower_id,p.name  FROM fp_borrower_loanrequests bl ,fp_products p WHERE bl.product_slug=p.slug and bl.borrower_id in '.$res;
  
			  
				// $this->db->query($sql)-result();
				// $query = $this->db->get_where('fp_borrower_loanrequests', array('borrower_id' => $res))->result();
				// $trnn[]= $data->id;
  
				$resp = array('status' => 200,'message' =>  'Success','data'=> $borrowerdetails,'data1' =>$this->db->query($result)->result());
				return json_output($respStatus,$resp);
			}
			else
			{
			  return json_output(400,array('status' => 400,'message' => $checkToken));
			}
		  
		}
		else
		{
			return json_output(400,array('status' => 400,'message' => 'Bad request.'));
		}
	  
	} // borrower_leads_fullcomplete 
	   public function borrower_leads_halfcomplete()
	   {
		$method = $_SERVER['REQUEST_METHOD'];
		if($method =="POST")
		{
			$checkToken = $this->check_token();
			if(True)
			{
				$response['status']=200;
				$respStatus = $response['status'];
				$params   = json_decode(file_get_contents('php://input'), TRUE);
  
				$selectkey   = isset($params['selectkey']) ? $params['selectkey'] : "*"; 
				$join     = isset($params['key']) ? $params['key'] : "";
				$where     = isset($params['where']) ? $params['where'] : "";  
  
				$sql = "WITH borrowerTable as (SELECT b.slug, b.id, bd.company_industry, bd.company_name, bd.turnover, bd.networth, bd.company_type, bd.profilecomplete,bd.profilecomplete_percentage, b.rm_name, bd.city, b.status FROM fpa_users b, fp_borrower_user_details bd 
				WHERE b.slug ='borrower' AND b.status in ('new','assigned','incomplete') AND b.id = bd.user_id AND bd.company_name is not null AND profilecomplete = 'incomplete' AND profilecomplete_percentage >=50 AND profilecomplete_percentage <=90) 
				SELECT bd.rm_name , bd.status,  bd.slug, bd.profilecomplete ,bd.profilecomplete_percentage,bd.city,fp_entitytype.id,bd.id as borrower_id,fp_city.id as location_id, fp_city.name as location, fp_entitytype.name as entity_name,bd.company_name as company_name, bd.company_industry as company_industry,bd.turnover, bd.networth 
				FROM borrowerTable as bd LEFT JOIN fp_city ON bd.city = fp_city.id LEFT JOIN fp_entitytype ON bd.company_type = fp_entitytype.id where bd.company_name is not null order by bd.id desc; ";
  
				$borrowerdetails = $this->db->query($sql)->result(); 
				$data = $this->db->query($sql);
				
				foreach ($data->result() as $row){
				  $txnArr[] = $row->borrower_id;					
				  
				}
				
				
				$res = implode(",",$txnArr);
							$res  = "(".$res.")";

							$result = 'SELECT bl.product_slug,bl.borrower_id,p.name  FROM fp_borrower_loanrequests bl ,fp_products p WHERE bl.product_slug=p.slug and bl.borrower_id in '.$res;
  
			  
				// $this->db->query($sql)-result();
				// $query = $this->db->get_where('fp_borrower_loanrequests', array('borrower_id' => $res))->result();
				// $trnn[]= $data->id;
  
				$resp = array('status' => 200,'message' =>  'Success','data'=> $borrowerdetails,'data1' =>$this->db->query($result)->result());
				return json_output($respStatus,$resp);
			}
			else
			{
			  return json_output(400,array('status' => 400,'message' => $checkToken));
			}
		  
		}
		else
		{
			return json_output(400,array('status' => 400,'message' => 'Bad request.'));
		}
	  
	   } // borrower_leads_halfcomplete 
	   public function borrower_leads_basecomplete()
	   {
		$method = $_SERVER['REQUEST_METHOD'];
		if($method =="POST")
		{
			$checkToken = $this->check_token();
			if(True)
			{
				$response['status']=200;
				$respStatus = $response['status'];
				$params   = json_decode(file_get_contents('php://input'), TRUE);
  
				$selectkey   = isset($params['selectkey']) ? $params['selectkey'] : "*"; 
				$join     = isset($params['key']) ? $params['key'] : "";
				$where     = isset($params['where']) ? $params['where'] : "";  
  
				$sql = "WITH borrowerTable as (SELECT b.slug, b.id, bd.company_industry, bd.company_name, bd.turnover, bd.networth, bd.company_type, bd.profilecomplete,bd.profilecomplete_percentage, b.rm_name, bd.city, b.status FROM fpa_users b, fp_borrower_user_details bd 
				WHERE b.slug ='borrower' AND b.status in ('new','assigned','incomplete') AND b.id = bd.user_id AND bd.company_name is not null AND profilecomplete = 'incomplete' AND profilecomplete_percentage <50) 
				SELECT bd.rm_name , bd.status,  bd.slug, bd.profilecomplete ,bd.profilecomplete_percentage,bd.city,fp_entitytype.id,bd.id as borrower_id,fp_city.id as location_id, fp_city.name as location, fp_entitytype.name as entity_name,bd.company_name as company_name, bd.company_industry as company_industry,bd.turnover, bd.networth 
				FROM borrowerTable as bd LEFT JOIN fp_city ON bd.city = fp_city.id LEFT JOIN fp_entitytype ON bd.company_type = fp_entitytype.id where bd.company_name is not null order by bd.id desc; ".$where;
  
				$borrowerdetails = $this->db->query($sql)->result(); 
				$data = $this->db->query($sql);
				
				foreach ($data->result() as $row){
				  $txnArr[] = $row->borrower_id;					
				  
				}
				
				
				$res = implode(",",$txnArr);
							$res  = "(".$res.")";

							$result = 'SELECT bl.product_slug,bl.borrower_id,p.name  FROM fp_borrower_loanrequests bl ,fp_products p WHERE bl.product_slug=p.slug and bl.borrower_id in '.$res;
  
			  
				// $this->db->query($sql)-result();
				// $query = $this->db->get_where('fp_borrower_loanrequests', array('borrower_id' => $res))->result();
				// $trnn[]= $data->id;
  
				$resp = array('status' => 200,'message' =>  'Success','data'=> $borrowerdetails,'data1' =>$this->db->query($result)->result());
				return json_output($respStatus,$resp);
			}
			else
			{
			  return json_output(400,array('status' => 400,'message' => $checkToken));
			}
		  
		}
		else
		{
			return json_output(400,array('status' => 400,'message' => 'Bad request.'));
		}
	  
	   } // borrower_leads_basecomplete 

	    public function loanapp_dash_details()
	    {
		$method = $_SERVER['REQUEST_METHOD'];
		if($method =="POST")
		{
			$checkToken = $this->check_token();
			if(True)
			{
				$response['status']=200;
				$respStatus = $response['status'];
				$params   = json_decode(file_get_contents('php://input'), TRUE);
  
				$selectkey   = isset($params['selectkey']) ? $params['selectkey'] : "*"; 
				$join     = isset($params['key']) ? $params['key'] : "";
				$where     = isset($params['where']) ? $params['where'] : "";  
  
				$sql = "SELECT 
				BO.company_name, 
				LR.id AS loanrequest_id, 
				LA.id As loanapplication_id, 
				LR.borrower_id, 
				BO.name AS borrowername, 
				LR.product_slug, 
				PR.name AS productname,
				LR.loanamount_slug,
				LR.tenor_max, LR.roi_min, 
				LA.rm, AU.name as rmname, LA.lendermaster_id, LM.lender_name,
				LA.workflow_status AS loanapplication_status, LM.image AS lender_logo
				FROM fp_borrower_loanrequests LR, fpa_loan_applications LA,
				fp_lender_master LM, fp_products PR, fp_borrower_user_details BO, fpa_adminusers AU
				WHERE LR.ID = LA.loanrequest_id 
				AND  LR.borrower_id = LA.borrower_id
				AND  BO.user_id = LR.borrower_id
				AND  LM.id = LA.lendermaster_id
				AND  PR.slug = LR.product_slug
				AND  LA.rm   = AU.id ".$where;

				if($where == " AND LR.loan_request_status='CC Approved'" || $where == " AND LR.loan_request_status='Due Diligence'" || $where == " AND LR.loan_request_status='CC Rejected'" || $where == " AND LR.loan_request_status='CC Approval Pending'"){

					$sql = "SELECT 
					BO.company_name, 
					LR.id AS loanrequest_id, 
					LR.borrower_id, 
					BO.name AS borrowername, 
					LR.product_slug, 
					PR.name AS productname,
					LR.loanamount_slug,
					LR.tenor_max, LR.roi_min, 
					LR.loan_request_status AS loanapplication_status,
					FPU.rm_name AS rmname
					FROM fp_borrower_loanrequests LR, 
					 fp_products PR, fp_borrower_user_details BO,
					 fpa_users FPU
					WHERE 
					 BO.user_id = LR.borrower_id
					 AND FPU.id = BO.user_id
					AND  PR.slug = LR.product_slug " .$where;
				}
  
				$borrowerdetails = $this->db->query($sql)->result(); 
				$data = $this->db->query($sql);

				
				foreach ($data->result() as $row){
				  $txnArr[] = $row->borrower_id;					
				  
				}
				
				
				$res = implode(",",$txnArr);
							$res  = "(".$res.")";

							$result = 'SELECT bl.product_slug,bl.borrower_id,p.name  FROM fp_borrower_loanrequests bl ,fp_products p WHERE bl.product_slug=p.slug and bl.borrower_id in '.$res;
  
			  
				// $this->db->query($sql)-result();
				// $query = $this->db->get_where('fp_borrower_loanrequests', array('borrower_id' => $res))->result();
				// $trnn[]= $data->id;
  
				$resp = array('status' => 200,'message' =>  'Success','data'=> $borrowerdetails,'data1' =>$this->db->query($result)->result());
				return json_output($respStatus,$resp);
			}
			else
			{
			  return json_output(400,array('status' => 400,'message' => "auth missing"));
			}
		  
		}
		else
		{
			return json_output(400,array('status' => 400,'message' => 'Bad request.'));
		}
	  
	    } // loanapp_dash_details 

	    public function borrower_list()
	    {
		$method = $_SERVER['REQUEST_METHOD'];
		if($method =="POST")
		{
			$checkToken = $this->check_token();
			if(True)
			{
				$response['status']=200;
				$respStatus = $response['status'];
				$params   = json_decode(file_get_contents('php://input'), TRUE);
  
				$selectkey   = isset($params['selectkey']) ? $params['selectkey'] : "*"; 
				$join     = isset($params['key']) ? $params['key'] : "";
				$where     = isset($params['where']) ? $params['where'] : "";  
  
				$sql = "WITH borrowerTable as (SELECT b.slug, b.status, b.id, bd.company_industry, bd.company_name, bd.turnover, bd.networth, bd.company_type, bd.profilecomplete, b.rm_name, bd.city FROM fpa_users b, fp_borrower_user_details bd WHERE b.slug ='borrower' AND b.id = bd.user_id AND bd.company_name is not null) SELECT bd.rm_name , bd.status,  bd.slug, bd.profilecomplete ,bd.city,fp_entitytype.id,bd.id as borrower_id,fp_city.id as location_id, fp_city.name as location, fp_entitytype.name as entity_name,bd.company_name as company_name, bd.company_industry as company_industry,bd.turnover, bd.networth FROM borrowerTable as bd LEFT JOIN fp_city ON bd.city = fp_city.id LEFT JOIN fp_entitytype ON bd.company_type = fp_entitytype.id where bd.company_name is not null  order by bd.id desc;";
  
				$borrowerdetails = $this->db->query($sql)->result(); 
				$data = $this->db->query($sql);
				foreach ($data->result() as $row){
				  $txnArr[] = $row->borrower_id;
				  
					
				  
				}
				$res = implode(",",$txnArr);
				$res  = "(".$res.")";
  
				$result = 'SELECT bl.product_slug,bl.borrower_id,p.name  FROM fp_borrower_loanrequests bl ,fp_products p WHERE bl.product_slug=p.slug and bl.borrower_id in '.$res;
  
			  
				// $this->db->query($sql)-result();
				// $query = $this->db->get_where('fp_borrower_loanrequests', array('borrower_id' => $res))->result();
				// $trnn[]= $data->id;
  
				$resp = array('status' => 200,'message' =>  'Success','data'=> $borrowerdetails,'data1' =>$this->db->query($result)->result());
				return json_output($respStatus,$resp);
			}
			else
			{
			  return json_output(400,array('status' => 400,'message' => $checkToken));
			}
		  
		}
		else
		{
			return json_output(400,array('status' => 400,'message' => 'Bad request.'));
		}
	  
	    } // borrower_list 

	public function totalassignedborrowers()
    {
      $method = $_SERVER['REQUEST_METHOD'];
      if($method =="POST")
      {
          $checkToken = $this->check_token();
          if(True)
          {
              $response['status']=200;
              $respStatus = $response['status'];
              $params   = json_decode(file_get_contents('php://input'), TRUE);

              $selectkey   = isset($params['selectkey']) ? $params['selectkey'] : "*"; 
              $join     = isset($params['key']) ? $params['key'] : "";
              $where     = isset($params['where']) ? $params['where'] : "";  

              $sql = "WITH borrowerTable as 
              (SELECT b.slug, b.id, bd.company_industry, bd.company_name, bd.turnover, bd.networth, 
               bd.company_type, bd.profilecomplete, b.rm_name, bd.city, ft.task_stage 
               FROM fpa_users b, fp_borrower_user_details bd,  fpa_taskdetails ft
               WHERE  b.id = bd.user_id AND ft.borrower_id=bd.user_id AND b.slug ='borrower' AND b.status in ('assigned','active') AND bd.company_name is not null)
               SELECT bd.rm_name ,  bd.slug, bd.profilecomplete ,bd.task_stage as stage ,bd.city,fp_entitytype.id,bd.id as borrower_id,fp_city.id as location_id,
               fp_city.name as location, fp_entitytype.name as entity_name,bd.company_name as company_name,    bd.company_industry as company_industry,bd.turnover, bd.networth 
               FROM borrowerTable as bd LEFT JOIN fp_city ON bd.city = fp_city.id LEFT JOIN fp_entitytype ON bd.company_type= fp_entitytype.id ";

              $borrowerdetails = $this->db->query($sql)->result(); 
              $data = $this->db->query($sql);
              foreach ($data->result() as $row){
                $txnArr[] = $row->borrower_id; 
              }
              $res = implode(",",$txnArr);
              $res  = "(".$res.")";

              $result = 'SELECT bl.product_slug,bl.borrower_id,p.name  FROM fp_borrower_loanrequests bl ,fp_products p WHERE bl.product_slug=p.slug and bl.borrower_id in '.$res;

            
              // $this->db->query($sql)-result();
              // $query = $this->db->get_where('fp_borrower_loanrequests', array('borrower_id' => $res))->result();
              // $trnn[]= $data->id;

              $resp = array('status' => 200,'message' =>  'Success','data'=> $borrowerdetails,'data1' =>$this->db->query($result)->result());
              return json_output($respStatus,$resp);
          }
          else
          {
            return json_output(400,array('status' => 400,'message' => $checkToken));
          }
        
      }
      else
      {
          return json_output(400,array('status' => 400,'message' => 'Bad request.'));
      }
    
    } // totalassignedborrowers 

      public function borrowers_incompleteprofiles()
    {
    $method = $_SERVER['REQUEST_METHOD'];
    if($method =="POST")
    {
        $checkToken = $this->check_token();
        if(True)
        {
            $response['status']=200;
            $respStatus = $response['status'];
            $params   = json_decode(file_get_contents('php://input'), TRUE);

            $selectkey   = isset($params['selectkey']) ? $params['selectkey'] : "*"; 
            $join     = isset($params['key']) ? $params['key'] : "";
            $where     = isset($params['where']) ? $params['where'] : "";  

            $sql = "WITH borrowerTable as (SELECT b.slug, b.id, bd.company_industry, bd.company_name, bd.turnover, bd.networth,bd.company_type, bd.profilecomplete, b.rm_name, bd.city, ft.task_stage 
            FROM fpa_users b,fp_borrower_user_details bd,  fpa_taskdetails ft WHERE  b.id = bd.user_id AND ft.borrower_id=bd.user_id AND b.slug ='borrower' AND b.status NOT IN ('completed') AND bd.company_name is not null) 
            SELECT bd.rm_name ,  bd.slug, bd.profilecomplete ,bd.task_stage as stage ,bd.city,fp_entitytype.id,bd.id as borrower_id,fp_city.id as location_id,fp_city.name as location, fp_entitytype.name 
            as entity_name,bd.company_name as company_name,bd.company_industry as company_industry,bd.turnover, bd.networth 
            FROM borrowerTable as bd LEFT JOIN fp_city ON bd.city = fp_city.id LEFT JOIN fp_entitytype ON bd.company_type= fp_entitytype.id ";
            

            $borrowerdetails = $this->db->query($sql)->result(); 
            $data = $this->db->query($sql);
            foreach ($data->result() as $row){
              $txnArr[] = $row->borrower_id;
              
                
              
            }
            $res = implode(",",$txnArr);
            $res  = "(".$res.")";

            $result = 'SELECT bl.product_slug,bl.borrower_id,p.name  FROM fp_borrower_loanrequests bl ,fp_products p WHERE bl.product_slug=p.slug and bl.borrower_id in '.$res;

          
            // $this->db->query($sql)-result();
            // $query = $this->db->get_where('fp_borrower_loanrequests', array('borrower_id' => $res))->result();
            // $trnn[]= $data->id;

            $resp = array('status' => 200,'message' =>  'Success','data'=> $borrowerdetails,'data1' =>$this->db->query($result)->result());
            return json_output($respStatus,$resp);
        }
        else
        {
          return json_output(400,array('status' => 400,'message' => $checkToken));
        }
      
    }
    else
    {
        return json_output(400,array('status' => 400,'message' => 'Bad request.'));
    }
  
    } // borrowers_incompleteprofiles 



   public function borrowers_completeprofiles()
      {
      $method = $_SERVER['REQUEST_METHOD'];
      if($method =="POST")
      {
          $checkToken = $this->check_token();
          if(True)
          {
              $response['status']=200;
              $respStatus = $response['status'];
              $params   = json_decode(file_get_contents('php://input'), TRUE);

              $selectkey   = isset($params['selectkey']) ? $params['selectkey'] : "*"; 
              $join     = isset($params['key']) ? $params['key'] : "";
              $where     = isset($params['where']) ? $params['where'] : "";  

              $sql = "WITH borrowerTable as (SELECT b.slug, b.id, bd.company_industry, bd.company_name, bd.turnover, bd.networth, bd.company_type, bd.profilecomplete, b.rm_name, bd.city 
              FROM fpa_users b, fp_borrower_user_details bd
              WHERE b.slug ='borrower' AND bd.profilecomplete ='completed' AND b.id = bd.user_id AND bd.company_name is not null) 
              SELECT bd.rm_name ,  bd.slug, bd.profilecomplete ,bd.city,fp_entitytype.id,bd.id as borrower_id,fp_city.id as location_id, fp_city.name as location, fp_entitytype.name as entity_name,bd.company_name as company_name, bd.company_industry as company_industry,bd.turnover, bd.networth 
              FROM borrowerTable as bd LEFT JOIN fp_city ON bd.city = fp_city.id LEFT JOIN fp_entitytype ON bd.company_type = fp_entitytype.id where bd.company_name is not null order by bd.id desc";

              $borrowerdetails = $this->db->query($sql)->result(); 
              $count = $this->db->query($sql)->num_rows(); 
       
                          if($count >= 1){
             
              $data = $this->db->query($sql);
              foreach ($data->result() as $row){
                $txnArr[] = $row->borrower_id;
              }
              $res = implode(",",$txnArr);
              $res  = "(".$res.")";

              $result = 'SELECT bl.product_slug,bl.borrower_id,p.name  FROM fp_borrower_loanrequests bl ,fp_products p WHERE bl.product_slug=p.slug and bl.borrower_id in '.$res;

            
              // $this->db->query($sql)-result();
              // $query = $this->db->get_where('fp_borrower_loanrequests', array('borrower_id' => $res))->result();
              // $trnn[]= $data->id;

              $resp = array('status' => 200,'message' =>  'Success','data'=> $borrowerdetails,'data1' =>$this->db->query($result)->result());
              return json_output($respStatus,$resp);
          }
          else
          {
            return json_output(200,array('status' => 400,'message' => "get one query", 'data'=>$borrowerdetails));
          }
        
      }
      else
      {
          return json_output(400,array('status' => 400,'message' => 'Bad request.'));
      }
    
        }
        else{
        $resp = array('status' => 200,'message' =>  'Request method fail');
            return json_output(400,$resp);
        }
    }  // borrowers_completeprofiles


   public function borrowers_archievedprofiles()
     {
      $method = $_SERVER['REQUEST_METHOD'];
      if($method =="POST")
      {
          $checkToken = $this->check_token();
          if(True)
          {
              $response['status']=200;
              $respStatus = $response['status'];
              $params   = json_decode(file_get_contents('php://input'), TRUE);

              $selectkey   = isset($params['selectkey']) ? $params['selectkey'] : "*"; 
              $join     = isset($params['key']) ? $params['key'] : "";
              $where     = isset($params['where']) ? $params['where'] : "";  

              $sql = "WITH borrowerTable as (SELECT b.slug, b.id, bd.company_industry, bd.company_name, bd.turnover, bd.networth, bd.company_type, bd.profilecomplete, b.rm_name, bd.city 
              FROM fpa_users b, fp_borrower_user_details bd
              WHERE b.slug ='borrower' AND b.status not in ('assigned','inactive','archieved') AND b.id = bd.user_id AND bd.company_name is not null) 
              SELECT bd.rm_name ,  bd.slug, bd.profilecomplete ,bd.city,fp_entitytype.id,bd.id as borrower_id,fp_city.id as location_id, fp_city.name as location,
              fp_entitytype.name as entity_name,bd.company_name as company_name, bd.company_industry as company_industry,bd.turnover, bd.networth 
              FROM borrowerTable as bd LEFT JOIN fp_city ON bd.city = fp_city.id LEFT JOIN fp_entitytype ON bd.company_type = fp_entitytype.id where bd.company_name is not null order by bd.id desc";

              $borrowerdetails = $this->db->query($sql)->result(); 
              $data = $this->db->query($sql);
              foreach ($data->result() as $row){
                $txnArr[] = $row->borrower_id; 
              }
              $res = implode(",",$txnArr);
              $res  = "(".$res.")";

              $result = 'SELECT bl.product_slug,bl.borrower_id,p.name  FROM fp_borrower_loanrequests bl ,fp_products p WHERE bl.product_slug=p.slug and bl.borrower_id in '.$res;
              // $this->db->query($sql)-result();
              // $query = $this->db->get_where('fp_borrower_loanrequests', array('borrower_id' => $res))->result();
              // $trnn[]= $data->id;

              $resp = array('status' => 200,'message' =>  'Success','data'=> $borrowerdetails,'data1' =>$this->db->query($result)->result());
              return json_output($respStatus,$resp);
          }
          else
          {
            return json_output(400,array('status' => 400,'message' => $checkToken));
          }
        
      }
      else
      {
          return json_output(400,array('status' => 400,'message' => 'Bad request.'));
      }
    
     } // borrowers_archievedprofiles 

     public function approved_amount()
     {
      $method = $_SERVER['REQUEST_METHOD'];
      if($method =="POST")
      {
          $checkToken = $this->check_token();
          if(True)
          {
              $response['status']=200;
              $respStatus = $response['status'];
              $params   = json_decode(file_get_contents('php://input'), TRUE);

              $selectkey   = isset($params['selectkey']) ? $params['selectkey'] : "*"; 
              $join     = isset($params['key']) ? $params['key'] : "";
              $where     = isset($params['where']) ? $params['where'] : "";  

              $sql = "SELECT 
        LR.ID AS LOANREQUEST_ID, LR.borrower_id, BO.name AS BORROWER,
        LR.product_slug, PR.name AS PRODUCT, LR.loanamount_slug,
        LR.tenor_max, LR.roi_min, 
        LA.ID AS LOANAPPLICATION_ID, LA.RM, LA.lendermaster_id, LA.workflow_status AS LOANAPPLICATION_STATUS,                   LM.lender_name AS BANK, LM.image AS BANKLOGO,
        LA.sanctioned_amount AS SANCTIONED_AMOUNT, LA.approved_amount AS APPROVED_AMOUNT
        FROM fp_borrower_loanrequests LR, fpa_loan_applications LA,
        fp_lender_master LM, fp_products PR, fp_borrower_user_details BO
        WHERE LR.ID = LA.loanrequest_id 
        AND  LR.borrower_id = LA.borrower_id
        AND  BO.user_id = LR.borrower_id
        AND  LM.id = LA.lendermaster_id
        AND  PR.slug = LR.product_slug
        AND  LA.workflow_status IN('deals approved','deals sanctioned')";

              $borrowerdetails = $this->db->query($sql)->result(); 
              $count = $this->db->query($sql)->num_rows(); 
       
        if($count >= 1){
              $data = $this->db->query($sql);
              foreach ($data->result() as $row){
                $txnArr[] = $row->borrower_id;
              }
              $res = implode(",",$txnArr);
              $res  = "(".$res.")";

              $result = 'SELECT bl.product_slug,bl.borrower_id,p.name  FROM fp_borrower_loanrequests bl ,fp_products p WHERE bl.product_slug=p.slug and bl.borrower_id in '.$res;
              // $this->db->query($sql)-result();
              // $query = $this->db->get_where('fp_borrower_loanrequests', array('borrower_id' => $res))->result();
              // $trnn[]= $data->id;

              $resp = array('status' => 200,'message' =>  'Success','data'=> $borrowerdetails,'data1' =>$this->db->query($result)->result());
              return json_output($respStatus,$resp);
          }else{
      $resp = array('status' => 200,'message' =>  'Success','data'=> $borrowerdetails);
            return json_output($respStatus,$resp);
      }
      }
          else
          {
            return json_output(400,array('status' => 400,'message' => $checkToken));
          }
        
      }
      else
      {
          return json_output(400,array('status' => 400,'message' => 'Bad request.'));
      }
     } // approved_amount 

    public function sanctioned_amount()
     {
      $method = $_SERVER['REQUEST_METHOD'];
      if($method =="POST")
      {
          $checkToken = $this->check_token();
          if(True)
          {
              $response['status']=200;
              $respStatus = $response['status'];
              $params   = json_decode(file_get_contents('php://input'), TRUE);

              $selectkey   = isset($params['selectkey']) ? $params['selectkey'] : "*"; 
              $join     = isset($params['key']) ? $params['key'] : "";
              $where     = isset($params['where']) ? $params['where'] : "";  

              $sql = "SELECT 
        LR.ID AS LOANREQUEST_ID, LR.borrower_id, BO.name AS BORROWER,
        LR.product_slug, PR.name AS PRODUCT, LR.loanamount_slug,
        LR.tenor_max, LR.roi_min, 
        LA.ID AS LOANAPPLICATION_ID, LA.RM, LA.lendermaster_id, LA.workflow_status AS LOANAPPLICATION_STATUS,
              LA.sanctioned_amount AS SANCTIONED_AMOUNT,
              LM.lender_name AS BANK, LM.image AS BANKLOGO
        FROM fp_borrower_loanrequests LR, fpa_loan_applications LA,
        fp_lender_master LM, fp_products PR, fp_borrower_user_details BO
        WHERE LR.ID = LA.loanrequest_id 
        AND  LR.borrower_id = LA.borrower_id
        AND  BO.user_id = LR.borrower_id
        AND  LM.id = LA.lendermaster_id
        AND  PR.slug = LR.product_slug
        AND  LA.workflow_status='deals sanctioned'";

              $borrowerdetails = $this->db->query($sql)->result(); 
              $count = $this->db->query($sql)->num_rows(); 
       
        if($count >= 1){
              $data = $this->db->query($sql);
              foreach ($data->result() as $row){
                $txnArr[] = $row->borrower_id;
                
              }
              $res = implode(",",$txnArr);
              $res  = "(".$res.")";

              $result = 'SELECT bl.product_slug,bl.borrower_id,p.name  FROM fp_borrower_loanrequests bl ,fp_products p WHERE bl.product_slug=p.slug and bl.borrower_id in '.$res;
              // $this->db->query($sql)-result();
              // $query = $this->db->get_where('fp_borrower_loanrequests', array('borrower_id' => $res))->result();
              // $trnn[]= $data->id;

              $resp = array('status' => 200,'message' =>  'Success','data'=> $borrowerdetails,'data1' =>$this->db->query($result)->result());
              return json_output($respStatus,$resp);
          }else{
      $resp = array('status' => 200,'message' =>  'Success','data'=> $borrowerdetails);
            return json_output($respStatus,$resp);
      }
     }
          else
          {
            return json_output(400,array('status' => 400,'message' => $checkToken));
          }
        
      }
      else
      {
          return json_output(400,array('status' => 400,'message' => 'Bad request.'));
      }
    }		// sanctioned_amount

 
	// public function lender_user_detail()
    // {
    //   $method = $_SERVER['REQUEST_METHOD'];
    //   if($method =="POST")
    //   {
    //       $checkToken = $this->check_token();
    //       if(true)
    //       {

    //           $response['status']=200;
    //           $respStatus = $response['status'];
    //           $params   = json_decode(file_get_contents('php://input'), TRUE);

    //           $selectkey   = isset($params['selectkey']) ? $params['selectkey'] : "*"; 
    //           $join     = isset($params['key']) ? $params['key'] : "";
    //           $where     = isset($params['where']) ? $params['where'] : "";  

	// 		  $sql = "SELECT la.user_id as lenderid, b.id as lender_user_id , b.rm_name  as AssignedTo, lm.id as lender_master_id, fc.name as location,lm.lender_name as bankname,fin.name as entitytype,
	// 		  (CASE 
	// 		   WHEN la.is_active=1 THEN 'Active'
	// 		   WHEN la.is_active=0 THEN 'In Active'
	// 		   END )as Active
			  
	// 		  FROM fpa_users b,fp_lender_user_details la ,fp_city fc ,fp_lender_master lm ,fp_fin_institution fin 
			  
	// 		  WHERE b.id=la.user_id AND la.location_id=fc.id AND la.lender_master_id=lm.id AND lm.lender_type=fin.id AND  b.slug='lender' AND b.status NOT IN ('inactive','archieved') ";
    //   $lender_master_id = $this->db->query($sql)->result();

    //   $data = $this->db->query($sql);
    //           foreach ($data->result() as $row){
    //     $resultss = 'SELECT id  from fpa_loan_applications where workflow_status = "Deals Sent To Lender"           and lendermaster_id ='.$row->lender_master_id;
    //     $workflowstatus = $this->db->query($resultss)->num_rows();
    //     $result = 'SELECT id  from fpa_loan_applications where workflow_status = "Deals      Approved"           and lendermaster_id ='.$row->lender_master_id;
    //     $Dealsapproved = $this->db->query($result)->num_rows();
    //         $txnArr[] = array("lender_master_id"=>$row->lender_master_id,
    //     "deal_send_to_lender"=>$workflowstatus,
    //     "Dealsapproved"=>$Dealsapproved,
    //     "AssignedTo"=>$row->AssignedTo,
    //     "location"=>$row->location,
    //     "bankname"=>$row->bankname,
    //     "entitytype"=>$row->entitytype,
    //     "Active"=>$row->Active,
    //     "lender_user_id"=>$row->lender_user_id,
    //   );        
    //           }
    //   //   'data' => $this->db->query($sql)->result()
    //           $resp = array('status' => 200,'message' =>  'Success', 'data'=>$txnArr);
    //           return json_output($respStatus,$resp);
    //       }
    //       else
    //       {
    //         return json_output(400,array('status' => 400,'message' => $checkToken));
    //       }
    //   }
    //   else
    //   {
    //       return json_output(400,array('status' => 400,'message' => 'Bad request.'));
    //   }
    // }

	public function lender_user_detail()
    {
      $method = $_SERVER['REQUEST_METHOD'];
      if($method =="POST")
      {
          $checkToken = $this->check_token();
          if(true)
          {

              $response['status']=200;
              $respStatus = $response['status'];
              $params   = json_decode(file_get_contents('php://input'), TRUE);

              $selectkey   = isset($params['selectkey']) ? $params['selectkey'] : "*"; 
              $join     = isset($params['key']) ? $params['key'] : "";
              $where     = isset($params['where']) ? $params['where'] : "";  

        $sql = "SELECT lm.image as lenderimage, la.id as lenderuserid, la.user_id as lenderid, b.id as lender_user_id ,la.poc_name, b.rm_name  as AssignedTo, lm.id as lender_master_id, fc.name as location,lm.lender_name as bankname,fin.name as entitytype,
        (CASE 
         WHEN la.is_active=1 THEN 'Active'
         WHEN la.is_active=0 THEN 'In Active'
         END )as Active  
        
        FROM fpa_users b,fp_lender_user_details la ,fp_city fc ,fp_lender_master lm ,fp_fin_institution fin 
        
        WHERE b.id=la.user_id AND la.location_id=fc.id AND la.lender_master_id=lm.id AND lm.lender_type=fin.id AND  b.slug='lender' AND b.status NOT IN ('inactive','archieved') ";
        $lender_master_id = $this->db->query($sql)->result();

        $data = $this->db->query($sql);
         foreach ($data->result() as $row){
        $resultss = 'SELECT id  from fpa_loan_applications where workflow_status = "Deals Sent To Lender"           and lendermaster_id ='.$row->lender_master_id;
        $workflowstatus = $this->db->query($resultss)->num_rows();
        $result = 'SELECT id  from fpa_loan_applications where workflow_status = "Deals Approved"           and lendermaster_id ='.$row->lender_master_id;
        $Dealsapproved = $this->db->query($result)->num_rows();
            $txnArr[] = array("lender_master_id"=>$row->lender_master_id,
        "deal_send_to_lender"=>$workflowstatus,
        "Dealsapproved"=>$Dealsapproved,
        "AssignedTo"=>$row->AssignedTo,
        "location"=>$row->location,
        "bankname"=>$row->bankname,
        "entitytype"=>$row->entitytype,
        "Active"=>$row->Active,
        "lender_user_id"=>$row->lender_user_id,
        "poc_name"=>$row->poc_name,
        "lenderuserid"=>$row->lenderuserid,
        "lendermasterimg"=>$row->lenderimage,
        );     
              }
      //   'data' => $this->db->query($sql)->result()
              $resp = array('status' => 200,'message' =>  'Success', 'data'=>$txnArr);
              return json_output($respStatus,$resp);
          }
          else
          {
            return json_output(400,array('status' => 400,'message' => $checkToken));
          }
      }
      else
      {
          return json_output(400,array('status' => 400,'message' => 'Bad request.'));
      }
    } //----------------------------lender_user_detail-----------------------
	public function lender_user_detail_statuswise()
    {
      $method = $_SERVER['REQUEST_METHOD'];
      if($method =="POST")
      {
          $checkToken = $this->check_token();
          if(true)
          {

              $response['status']=200;
              $respStatus = $response['status'];
              $params   = json_decode(file_get_contents('php://input'), TRUE);

              $selectkey   = isset($params['selectkey']) ? $params['selectkey'] : "*"; 
              $join     = isset($params['key']) ? $params['key'] : "";
              $where     = isset($params['where']) ? $params['where'] : "";  

              $sql = "SELECT b.rm_name  as AssignedTo, lm.id as lender_master_id, fc.name as location,lm.lender_name as bankname,fin.name as entitytype,
        (CASE 
         WHEN la.is_active=1 THEN 'Active'
         WHEN la.is_active=0 THEN 'In Active'
         END )as Active
        
        FROM fpa_users b,fp_lender_user_details la ,fp_city fc ,fp_lender_master lm ,fp_fin_institution fin 
        WHERE b.id=la.user_id AND la.location_id=fc.id AND la.lender_master_id=lm.id AND lm.lender_type=fin.id AND  b.slug='lender' ". $where;

      $lender_master_id = $this->db->query($sql)->result();
      $count = $this->db->query($sql)->num_rows();

	  if($count>=1){
      $data = $this->db->query($sql);
              foreach ($data->result() as $row){
        $resultss = 'SELECT id  from fpa_loan_applications where workflow_status = "Deals Sent To Lender"           and lendermaster_id ='.$row->lender_master_id;
        $workflowstatus = $this->db->query($resultss)->num_rows();
        $result = 'SELECT id  from fpa_loan_applications where workflow_status = "Deals Approved"           and lendermaster_id ='.$row->lender_master_id;
        $Dealsapproved = $this->db->query($result)->num_rows();
            $txnArr[] = array("lender_master_id"=>$row->lender_master_id,
        "deal_send_to_lender"=>$workflowstatus,
        "Dealsapproved"=>$Dealsapproved,
        "AssignedTo"=>$row->AssignedTo,
        "location"=>$row->location,
        "bankname"=>$row->bankname,
        "entitytype"=>$row->entitytype,
        "Active"=>$row->Active,
      );
              }
      //   'data' => $this->db->query($sql)->result()
              $resp = array('status' => 200,'message' =>  'Success', 'data'=>$txnArr);
              return json_output($respStatus,$resp);

			}else{
				$resp = array('status' => 200,'message' =>  'Success', 'data'=>$lender_master_id);
				return json_output($respStatus,$resp);
			}
          }
          else
          {
            return json_output(400,array('status' => 400,'message' => $checkToken));
          }
      }
      else
      {
          return json_output(400,array('status' => 400,'message' => 'Bad request.'));
      }
    } // lender_user_detail_statuswise


	public function addlender()
	{
		$method = $_SERVER['REQUEST_METHOD'];
		if($method != 'POST'){
		  json_output(400,array('status' => 400,'message' => 'Bad request.'));
		}else{
		 $checkToken = $this->check_token();
		 if($checkToken){
		   $response['status']=200;
		   $respStatus = $response['status'];
		 $params = json_decode(file_get_contents('php://input'), TRUE);
		 try{
		  $name = $params['data']['name'];
		  $email = $params['data']['email'];
		  $phone = $params['data']['mobile'];
          $created_by = $params['data']['created_by'];
		  $institution=$params['data']['lender_id'];

		  $add_user = $this->db->insert("fpa_users",array('name'=>$name,'email'=>$email, 'mobile'=>$phone ,'slug'=>'lender','created_by'=>$params['data']['created_by'],'lender_master_id'=>$institution));
		  
		  $id = $this->db->insert_id();
		  $department= $params['data']['lender_departments'];
		  $location=$params['data']['lender_location'];
		  $institution=$params['data']['lender_id'];
		  $designation=$params['data'] ['designation'];
		  $branch=$params['data']['Branch'];
		
		
		  $add_borrower =$this->db->insert("fp_lender_user_details", array('user_id'=>$id,'poc_name'=>$name,'email'=>$email, 'mobile'=>$phone,'department_slug'=>$department,'location_id'=>$location,'lender_master_id'=>$institution,'designation'=>$designation,'branch'=>$branch));
		  if($add_user && $add_borrower){

			// Email Notification
			$subject = "Dear ". $created_by.",";
			$message = "Dear ". $created_by.","."<br/>"."<br/>"."<br/>"."A new Lender Partner ".$name."  has been onboarded.<br/>Please visit the Lender's profile in detail to understand the product and the filtering criteria."."<br/>"."<br/>".
		   "Looking forward to building a portfolio with them.";

			
		   
		    $to = 'rahul@finnup.in';
		    $tos = 'aisha@finnup.in';
		   

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
			
		   json_output(200,array('status' => 200,'message' => 'successfully Added'));
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
	}  // addlender 

		 public function taskassignlender()
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
			  
			  //  $this->db->trans_start(); 
			
			  // $this->db->trans_begin();  
			  $id='';
			
				   $task_details = $this->db->insert("fpa_taskdetails", $params['data']);
				   $id = $this->db->insert_id();  
			  
				   
			   $sql =  "select lender_id,id,task_assigned_to
			   from fpa_taskdetails where fpa_taskdetails.id=".$id; 
				  $taskdata = $this->db->query($sql)->row();
			   
			
			   $assigndata =  "select name,email,id
			   from fpa_adminusers where fpa_adminusers.id=".$taskdata->task_assigned_to;
			
			   $rmdata = $this->db->query($assigndata)->row();
			
				  $task_details_worklog =$this->db->insert("fpa_taskdetails_worklog",array('taskdetail_id'=>$taskdata->id)); 
				  
				  $fpa_users = "UPDATE fpa_users 
			   SET status ='assigned', rm_id='".$rmdata->id."',".
			   "rm_name='".$rmdata->name."',".
			   "rm_email='".$rmdata->email."' WHERE fpa_users.id=".$taskdata->lender_id;
			  
			  
			  $checkdata = $this->db->query($fpa_users);
			  $rm_name=$rmdata->name;
              $company_name = $params['company_name'];



			
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
                        // $email->addTo($to);
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
	} // taskassignlender


		  //  -----------------------RM BORROWER DASHBOARD---------------------------- 

    public function rmborrower_dashboard()
    {
	$method = $_SERVER['REQUEST_METHOD'];
	if($method =="POST")
	{
			$checkToken = $this->check_token();
			if(true)
			{
					$response['status']=200;
					$respStatus = $response['status'];
					$params 	= json_decode(file_get_contents('php://input'), TRUE);

					$selectkey 	= isset($params['selectkey']) ? $params['selectkey'] : "*"; 
					$join 		= isset($params['key']) ? $params['key'] : "";
					$where 		= isset($params['where']) ? $params['where'] : "";	
					$loan_req 		= isset($params['loan_req']) ? $params['loan_req'] : "";
					if($loan_req =='')	{
					
					
					$sql="WITH borrowerTable as (SELECT b.slug,b.rm_id, b.id, bd.company_industry, bd.company_name, bd.turnover, bd.networth, bd.company_type, bd.profilecomplete, b.rm_name, bd.city ,bd.user_id,b.status
					FROM fpa_users b, fp_borrower_user_details bd 
					WHERE b.slug ='borrower' AND b.status in ('assigned','active') AND b.id = bd.user_id AND bd.company_name is not null )  
					SELECT bd.rm_name , bd.rm_id, bd.slug, bd.profilecomplete ,bd.city,fp_entitytype.id,bd.id as borrower_id,fp_city.id as location_id, fp_city.name as location, fp_entitytype.name as entity_name,bd.company_name as company_name, bd.company_industry as company_industry,bd.turnover, bd.networth 
					FROM borrowerTable as bd LEFT JOIN fp_city ON bd.city = fp_city.id LEFT JOIN fp_entitytype ON bd.company_type = fp_entitytype.id where bd.company_name is not null  ".$where." ";
					}

					else if($loan_req != ""){

						$sql="WITH borrowerTable as (SELECT b.slug,b.rm_id, b.id, bd.company_industry, bd.company_name, bd.turnover, bd.networth, bd.company_type, bd.profilecomplete, b.rm_name, bd.city ,bd.user_id,b.status
					FROM fpa_users b, fp_borrower_user_details bd 
					WHERE b.slug ='borrower' AND b.status in ('assigned','active') AND b.id = bd.user_id AND bd.company_name is not null )  
					SELECT bd.rm_name , bd.rm_id, bd.slug, bd.profilecomplete ,bd.city,fp_entitytype.id,bd.id as borrower_id,fp_city.id as location_id, fp_city.name as location, fp_entitytype.name as entity_name,bd.company_name as company_name, bd.company_industry as company_industry, bd.turnover, bd.networth , bd.user_id
					FROM fp_borrower_loanrequests lr, borrowerTable as bd LEFT JOIN fp_city ON bd.city = fp_city.id LEFT JOIN fp_entitytype ON bd.company_type = fp_entitytype.id where bd.company_name is not null and lr.borrower_id = bd.user_id ".$loan_req;


					}


					$borrowerdetails = $this->db->query($sql)->result(); 
					$count = $this->db->query($sql)->num_rows(); 
					if($count >=1){

				

					$data = $this->db->query($sql);
					foreach ($data->result() as $row){
						$txnArr[] = $row->borrower_id;
																							
					}
					$res = implode(",",$txnArr);
					$res  = "(".$res.")";

					$result = 'SELECT bl.product_slug,bl.borrower_id,p.name  FROM fp_borrower_loanrequests bl ,fp_products p WHERE bl.product_slug=p.slug and bl.borrower_id in '.$res;
				
					// $this->db->query($sql)-result();
					// $query = $this->db->get_where('fp_borrower_loanrequests', array('borrower_id' => $res))->result();
					// $trnn[]= $data->id;

					$resp = array('status' => 200,'message' =>  'Success','data'=> $borrowerdetails,'data1' =>$this->db->query($result)->result());
					return json_output($respStatus,$resp);
				}
				else{
					$resp = array('status' => 200,'message' =>  'Success','data'=> $borrowerdetails);
					return json_output($respStatus,$resp);
				}
			}
			else
			{
				return json_output(400,array('status' => 400,'message' => $checkToken));
			}
		
	}
	else
	{
			return json_output(400,array('status' => 400,'message' => 'Bad request.'));
	}

    } // rmborrower_dashboard



	public function lender_user()
    {
      $method = $_SERVER['REQUEST_METHOD'];
      if($method =="POST")
      {
          $checkToken = $this->check_token();
          if(true)
          {

              $response['status']=200;
              $respStatus = $response['status'];
              $params   = json_decode(file_get_contents('php://input'), TRUE);

              $selectkey   = isset($params['selectkey']) ? $params['selectkey'] : "*"; 
              $join     = isset($params['key']) ? $params['key'] : "";
              $where     = isset($params['where']) ? $params['where'] : "";  

			  $sql = "SELECT la.user_id as lenderid, b.rm_name  as AssignedTo, lm.id as lender_master_id, fc.name as location,lm.lender_name as bankname,fin.name as entitytype,
			  (CASE 
			   WHEN la.is_active=1 THEN 'Active'
			   WHEN la.is_active=0 THEN 'In Active'
			   END )as Active
			  
			  FROM fpa_users b,fp_lender_user_details la ,fp_city fc ,fp_lender_master lm ,fp_fin_institution fin 
			  
			  WHERE b.id=la.user_id AND la.location_id=fc.id AND la.lender_master_id=lm.id AND lm.lender_type=fin.id AND  b.slug='lender' AND b.status NOT IN ('inactive','archieved') ";

      $lender_master_id = $this->db->query($sql)->result();

      $data = $this->db->query($sql);
              foreach ($data->result() as $row){
        $resultss = 'SELECT id  from fpa_loan_applications where workflow_status = "Deals Sent To Lender" and lendermaster_id ='.$row->lender_master_id;
        $workflowstatus = $this->db->query($resultss)->num_rows();
        $result = 'SELECT id  from fpa_loan_applications where workflow_status = "Deals Approved"      and lendermaster_id ='.$row->lender_master_id;
        $Dealsapproved = $this->db->query($result)->num_rows();
        $txnArr[] = array("lender_master_id"=>$row->lender_master_id,
        "deal_send_to_lender"=>$workflowstatus,
        "Dealsapproved"=>$Dealsapproved,
        "AssignedTo"=>$row->AssignedTo,
        "location"=>$row->location,
        "bankname"=>$row->bankname,
        "entitytype"=>$row->entitytype,
        "Active"=>$row->Active,
     "lenderid"=>$row->lenderid
      );     
              }
      //   'data' => $this->db->query($sql)->result()
              $resp = array('status' => 200,'message' =>  'Success', 'data'=>$txnArr);
              return json_output($respStatus,$resp);
          }
          else
          {
            return json_output(400,array('status' => 400,'message' => $checkToken));
          }
      }
      else
      {
          return json_output(400,array('status' => 400,'message' => 'Bad request.'));
      }
    } // lender_user 


	public function activitylog()
	{
			$method = $_SERVER['REQUEST_METHOD'];
			if($method =="POST")
			{
					$checkToken = $this->check_token();
					if(true)
					{
							$response['status']=200;
							$respStatus = $response['status'];
							$params 	= json_decode(file_get_contents('php://input'), TRUE);

							$selectkey 	= isset($params['selectkey']) ? $params['selectkey'] : "*"; 
							$join 		= isset($params['key']) ? $params['key'] : "";
							$where 		= isset($params['where']) ? $params['where'] : "";	

							$sql="SELECT tdw.created_at as created,tdw.activity as work, tdw.activity_remarks as comment, bu.name as borrower 
							FROM fpa_taskdetails_worklog tdw, fpa_taskdetails as td, fp_borrower_user_details bu WHERE tdw.taskdetail_id = td.id AND td.borrower_id=bu.user_id".$where;

							
							$resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql)->result());
							return json_output($respStatus,$resp);
					}
					else
					{
						return json_output(400,array('status' => 400,'message' => $checkToken));
					}
			}
			else
			{
					return json_output(400,array('status' => 400,'message' => 'Bad request.'));
			}
	}  // activitylog --------------


	public function lender_location()
 {
   $method = $_SERVER['REQUEST_METHOD'];
   if($method =="POST")
   {
     $checkToken = $this->check_token();
     if(True)
     {
       $response['status']=200;
       $respStatus = $response['status'];
       $params  = json_decode(file_get_contents('php://input'), TRUE);

       $selectkey  = isset($params['selectkey']) ? $params['selectkey'] : "*"; 
       $join   = isset($params['key']) ? $params['key'] : "";
       $where   = isset($params['where']) ? $params['where'] : "";
       $id  = ($params['id']) ;
       
    

       $sql = "SELECT t2.name,t1.location_id FROM fp_lender_location t1 , fp_city t2 WHERE t1.location_id = t2.id  AND t1.lender_master_id=".$id;
       
       $resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql)->result());
       return json_output($respStatus,$resp);

  
     }
     else
     {
      return json_output(400,array('status' => 400,'message' => 'Bad request.'));
     } 
   }
   else
   {
     return json_output(400,array('status' => 400,'message' => 'Bad request.'));
   }
 }  // ---------------------------- lendr_location------------------------

 
 public function spocdetails()
 {
		 $method = $_SERVER['REQUEST_METHOD'];
		 if($method =="POST")
		 {
				 $checkToken = $this->check_token();
				 if(true)
				 {
						 $response['status']=200;
						 $respStatus = $response['status'];
						 $params 	= json_decode(file_get_contents('php://input'), TRUE);

						 $selectkey 	= isset($params['selectkey']) ? $params['selectkey'] : "*"; 
						 $join 		= isset($params['key']) ? $params['key'] : "";
						 $where 		= isset($params['where']) ? $params['where'] : "";	

						 $sql="SELECT lud.poc_name as name, fl.name as location, fd.name as designation   from fpa_users fpu, fp_lender_user_details  lud, fp_departments fd, fp_location fl where
						 lud.user_id=fpu.id AND fpu.slug='lender' AND lud.department_slug=fd.slug AND fpu.is_email_verified =0 AND fpu.is_mobile_verified=0 AND fl.id=lud.location_id ". $where;

						 $resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql)->result());
						 return json_output($respStatus,$resp);
				 }
				 else
				 {
					 return json_output(400,array('status' => 400,'message' => $checkToken));
				 }
		 }
		 else
		 {
				 return json_output(400,array('status' => 400,'message' => 'Bad request.'));
		 }
 }
 // -------------End spocdetails


 public function lenderproductlist()
 {
		 $method = $_SERVER['REQUEST_METHOD'];
		 if($method =="POST")
		 {
				 $checkToken = $this->check_token();
				 if(true)
				 {
						 $response['status']=200;
						 $respStatus = $response['status'];
						 $params 	= json_decode(file_get_contents('php://input'), TRUE);

						 $selectkey 	= isset($params['selectkey']) ? $params['selectkey'] : "*"; 
						 $join 		= isset($params['key']) ? $params['key'] : "";
						 $where 		= isset($params['where']) ? $params['where'] : "";	

						 $sql="SELECT
						 fp.name as product, 
						 lpd.loan_min as loanmin,
						 lpd.loan_max as loanmax,
						 lpd.roi_min as roimin,
						 lpd.roi_max as roimax,
						 lpd.tenor_min as tenormin,
						 lpd.tenor_max as tenormax,
						 lm.lender_name
						 from 
						 fp_lender_master lm, fp_lender_product_details lpd, fp_products fp, fp_lender_user_details lud, fpa_users fpu
						 where 
						 lm.id=lpd.lender_id AND fpu.id=lud.user_id AND lpd.product_id = fp.id AND lud.lender_master_id=lm.id ". $where;

						 $resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql)->result());
						 return json_output($respStatus,$resp);
				 }
				 else
				 {
					 return json_output(400,array('status' => 400,'message' => $checkToken));
				 }
		 }
		 else
		 {
				 return json_output(400,array('status' => 400,'message' => 'Bad request.'));
		 }
 }
 // -------------- End lenderproductlist

 public function loanproposals()
 {
		 $method = $_SERVER['REQUEST_METHOD'];
		 if($method =="POST")
		 {
				 $checkToken = $this->check_token();
				 if(true)
				 {
						 $response['status']=200;
						 $respStatus = $response['status'];
						 $params 	= json_decode(file_get_contents('php://input'), TRUE);

						 $selectkey 	= isset($params['selectkey']) ? $params['selectkey'] : "*"; 
						 $join 		= isset($params['key']) ? $params['key'] : "";
						 $where 		= isset($params['where']) ? $params['where'] : "";	

						 $sql="SELECT  la.loanrequest_id as lrid, u.name, u.rm_name,lm.image , lm.lender_name, p.name as productname, u.company_name as companyname, la.loanapplication_status as lastatus, la.workflow_status as wfstatus, la.lender_intrest_received,la.sanctioned_amount  as amount
						 FROM 
						 fpa_loan_applications la, 
						 fpa_users u,
						 fp_lender_master lm, 
						 fp_products p
						 where
						 u.id = la.borrower_id AND 
						 la.product_slug = p.slug AND la.loanapplication_status in ('Deal Sent To Lender' 'Deal Approved', 'Deal Sanctioned') AND la.workflow_status IN ('Deal Sent To Lender', 'Deal Approved', 'Deal Sanctioned') " .$where;

						 $resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql)->result());
						 return json_output($respStatus,$resp);
				 }
				 else
				 {
					 return json_output(400,array('status' => 400,'message' => $checkToken));
				 }
		 }
		 else
		 {
				 return json_output(400,array('status' => 400,'message' => 'Bad request.'));
		 }
 }
 // -----------------End loanproposals-----------------


 public function loanrequest()
 {
		 $method = $_SERVER['REQUEST_METHOD'];
		 if($method =="POST")
		 {
				 $checkToken = $this->check_token();
				 if(true)
				 {
						 $response['status']=200;
						 $respStatus = $response['status'];
						 $params 	= json_decode(file_get_contents('php://input'), TRUE);

						 $selectkey 	= isset($params['selectkey']) ? $params['selectkey'] : "*"; 
						 $join 		= isset($params['key']) ? $params['key'] : "";
						 $where 		= isset($params['where']) ? $params['where'] : "";	

						 $sql="SELECT  la.loanrequest_id as lrid, u.name, u.rm_name, lm.lender_name, p.name as productname, u.company_name as companyname, la.loanapplication_status as lastatus, la.workflow_status as wfstatus, la.lender_intrest_received,la.sanctioned_amount as amount, lm.image
						 FROM 
						 fpa_loan_applications la, 
						 fpa_users u,
						 fp_lender_master lm, 
						 fp_products p
						 where
						 u.id = la.borrower_id AND 
						 la.product_slug = p.slug AND la.loanapplication_status in ('Deal Sent To Lender','Deal Closed', 'Deal Approved', 'Deal Sanctioned') AND la.workflow_status in ('Deal Sent To Lender','Deal Closed', 'Deal Approved', 'Deal Sanctioned')" .$where;

						 $resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql)->result());
						 return json_output($respStatus,$resp);
				 }
				 else
				 {
					 return json_output(400,array('status' => 400,'message' => $checkToken));
				 }
		 }
		 else
		 {
				 return json_output(400,array('status' => 400,'message' => 'Bad request.'));
		 }
 }
 // -------------- End loanrequest---------------------

 public function assigntorm_email()
 {
		 $method = $_SERVER['REQUEST_METHOD'];
		 if($method =="POST")
		 {
				 $checkToken = $this->check_token();
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
					 return json_output(400,array('status' => 400,'message' => $checkToken));
				 }
		 }
		 else
		 {
				 return json_output(400,array('status' => 400,'message' => 'Bad request.'));
		 }
 } //------------------ end  of assigntorm_email  -----------------------

 public function designation()
 {
		 $method = $_SERVER['REQUEST_METHOD'];
		 if($method =="POST")
		 {
				 $checkToken = $this->check_token();
				 if(true)
				 {
						 $response['status']=200;
						 $respStatus = $response['status'];
						 $params 	= json_decode(file_get_contents('php://input'), TRUE);

						 $selectkey 	= isset($params['selectkey']) ? $params['selectkey'] : "*"; 
						 $join 		= isset($params['key']) ? $params['key'] : "";
						 $where 		= isset($params['where']) ? $params['where'] : "";	

						 $sql="SELECT * FROM fp_lender_designation";

						 $resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql)->result());
						 return json_output($respStatus,$resp);
				 }
				 else
				 {
					 return json_output(400,array('status' => 400,'message' => $checkToken));
				 }
		 }
		 else
		 {
				 return json_output(400,array('status' => 400,'message' => 'Bad request.'));
		 }
 } //----------------- end of designation ------------------------------------
 public function lendername()
 {
   $method = $_SERVER['REQUEST_METHOD'];
   if($method =="POST")
   {
     $checkToken = $this->check_token();
     if(true)
     {
       $response['status']=200;
       $respStatus = $response['status'];
       $params  = json_decode(file_get_contents('php://input'), TRUE);

       $selectkey  = isset($params['selectkey']) ? $params['selectkey'] : "*"; 
       $join   = isset($params['key']) ? $params['key'] : "";
       $where   = isset($params['where']) ? $params['where'] : ""; 
       $locationid = ($params['locationid']);
       $lendermastername=($params['lendermsterid']);

       $sql="SELECT t1.poc_name,t1.user_id as lenderid FROM  fp_lender_user_details t1 WHERE t1.lender_master_id= ".$lendermastername." and t1.location_id=".$locationid."";

       $resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql)->result());
       return json_output($respStatus,$resp);
     }
     else
     {
      return json_output(400,array('status' => 400,'message' => $checkToken));
     }
   }
   else
   {
     return json_output(400,array('status' => 400,'message' => 'Bad request.'));

    }
 }  // -------------------------------- end of lendername------------------------


 public function rm_borrower_user_detail()
	{
			$method = $_SERVER['REQUEST_METHOD'];
			if($method =="POST")
			{
					$checkToken = $this->check_token();
					if(True)
					{
							$response['status']=200;
							$respStatus = $response['status'];
							$params 	= json_decode(file_get_contents('php://input'), TRUE);

							$selectkey 	= isset($params['selectkey']) ? $params['selectkey'] : "*"; 
							$join 		= isset($params['key']) ? $params['key'] : "";
							$where 		= isset($params['where']) ? $params['where'] : "";	

							$sql = "WITH borrowerTable as (SELECT b.slug, b.id, bd.company_industry, bd.company_name, bd.turnover, bd.networth, bd.company_type, bd.profilecomplete, b.rm_name, b.rm_id, bd.city FROM fpa_users b, fp_borrower_user_details bd WHERE b.slug ='borrower' AND b.status in ('new','assigned','active') AND b.id = bd.user_id AND bd.company_name is not null) SELECT bd.rm_id, bd.rm_name,bd.slug, bd.profilecomplete ,bd.city,fp_entitytype.id,bd.id as borrower_id,fp_city.id as location_id, fp_city.name as location, fp_entitytype.name as entity_name,bd.company_name as company_name, bd.company_industry as company_industry,bd.turnover, bd.networth FROM borrowerTable as bd LEFT JOIN fp_city ON bd.city = fp_city.id LEFT JOIN fp_entitytype ON bd.company_type = fp_entitytype.id where bd.company_name is not null  and  bd.rm_id=".$where;

							$borrowerdetails = $this->db->query($sql)->result(); 
							$data = $this->db->query($sql);
							foreach ($data->result() as $row){
								$txnArr[] = $row->borrower_id;
								
									
								
							}
							$res = implode(",",$txnArr);
							$res  = "(".$res.")";

							$result = 'SELECT bl.product_slug,bl.borrower_id,p.name  FROM fp_borrower_loanrequests bl ,fp_products p WHERE bl.product_slug=p.slug and bl.borrower_id in '.$res;

						
							// $this->db->query($sql)-result();
							// $query = $this->db->get_where('fp_borrower_loanrequests', array('borrower_id' => $res))->result();
							// $trnn[]= $data->id;

							$resp = array('status' => 200,'message' =>  'Success','data'=> $borrowerdetails,'data1' =>$this->db->query($result)->result());
							return json_output($respStatus,$resp);
					}
					else
					{
						return json_output(400,array('status' => 400,'message' => $checkToken));
					}
				
			}
			else
			{
					return json_output(400,array('status' => 400,'message' => 'Bad request.'));
			}
		
	}  //----------------------- rm_borrower_user_detail ---------------------

	public function Portfolio_borrower_user_detail()
	{
	  $method = $_SERVER['REQUEST_METHOD'];
	  if($method =="POST")
	  {
		$checkToken = $this->check_token();
		if(True)
		{
		  $response['status']=200;
		  $respStatus = $response['status'];
		  $params  = json_decode(file_get_contents('php://input'), TRUE);
   
		  $selectkey  = isset($params['selectkey']) ? $params['selectkey'] : "*"; 
		  $join   = isset($params['key']) ? $params['key'] : "";
		  $where   = isset($params['where']) ? $params['where'] : ""; 
   
		  $sql = "WITH borrowerTable as (SELECT b.slug, b.id, bd.company_industry, bd.company_name, bd.turnover, bd.networth, bd.company_type, bd.profilecomplete, b.rm_name, bd.city,b.status FROM fpa_users b, fp_borrower_user_details bd WHERE b.slug ='borrower'  AND b.id = bd.user_id AND bd.company_name is not null) SELECT bd.rm_name ,  bd.slug, bd.profilecomplete ,bd.city,fp_entitytype.id,bd.id as borrower_id,fp_city.id as location_id, fp_city.name as location, fp_entitytype.name as entity_name,bd.company_name as company_name, bd.company_industry as company_industry,bd.turnover, bd.networth,bd.status FROM borrowerTable as bd LEFT JOIN fp_city ON bd.city = fp_city.id LEFT JOIN fp_entitytype ON bd.company_type = fp_entitytype.id where bd.company_name is not null ".$where."    order by bd.id desc";
   
		  // AND b.status in ('new','assigned','active')
   
		  $borrowerdetails = $this->db->query($sql)->result(); 
		  $data = $this->db->query($sql);
		  foreach ($data->result() as $row){
		   $txnArr[] = $row->borrower_id; 
		  }
		  $res = implode(",",$txnArr);
		  $res  = "(".$res.")";
   
		  $result = 'SELECT bl.product_slug,bl.borrower_id,p.name  FROM fp_borrower_loanrequests bl ,fp_products p WHERE bl.product_slug=p.slug and bl.borrower_id in '.$res;
   
		 
		  // $this->db->query($sql)-result();
		  // $query = $this->db->get_where('fp_borrower_loanrequests', array('borrower_id' => $res))->result();
		  // $trnn[]= $data->id;
   
		  $resp = array('status' => 200,'message' =>  'Success','data'=> $borrowerdetails,'data1' =>$this->db->query($result)->result());
		  return json_output($respStatus,$resp);
		}
		else
		{
		 return json_output(400,array('status' => 400,'message' => $checkToken));
		}
	   
	  }
	  else
	  {
		return json_output(400,array('status' => 400,'message' => 'Bad request.'));
	  }
	 
	} // portfolio_borrower_user_detail

	public function loanrequestdelete()
	{
		$method = $_SERVER['REQUEST_METHOD'];
		if($method =="POST")
		{
			$checkToken = $this->check_token();
			if(True)
			{
				$response['status']=200;
				$respStatus = $response['status'];
				$params   = json_decode(file_get_contents('php://input'), TRUE);
  
				$selectkey   = isset($params['selectkey']) ? $params['selectkey'] : "*";
				$where     = isset($params['where']) ? $params['where'] : "";  
				$id  = ($params['id']);
  
				$sql = "UPDATE fp_borrower_loanrequests
				SET  is_deleted='yes'
				WHERE fp_borrower_loanrequests.id = ".$id;

				$query = $this->db->query($sql);
				
				
				$resp = array('status' => 200,'message' =>  'Success','data' => 1 );
				return json_output($respStatus,$resp);
			}
			else
			{
			  return json_output(400,array('status' => 400,'message' => 'Bad request.'));
			}
		  
		}
		else
		{
			return json_output(400,array('status' => 400,'message' => 'Bad request.'));
		}
	  
	}  // loanrequestdelete




	public function updateborrower()
			{
				$method = $_SERVER['REQUEST_METHOD'];
				if($method != 'POST'){
				json_output(400,array('status' => 400,'message' => 'Bad request.'));
				}else{
				$checkToken = $this->check_token();
				if(true){
				$response['status']=200;
				$respStatus = $response['status'];
				$params = json_decode(file_get_contents('php://input'), TRUE);
				try{
				
					$borrower_id = isset($params['data'] ['borrower_id']) ? $params ['data']['borrower_id'] : ""; ;
					$company_name =  isset($params ['data'] ['company_name']) ? $params ['data']['company_name'] : "";
					$entity_type =   isset($params ['data']['entity_type']) ? $params ['data']['entity_type'] : "" ;

					$fp_borrower_details = array( 
						'company_name'  => $company_name, 
						'company_type '=> $entity_type, 
					); 
					
					$this->db->where('user_id',$borrower_id);
					$this->db->update('fp_borrower_user_details', $fp_borrower_details);
			
				json_output(200,array('status' => 200,'message' => 'successfully Added'));
				}
				catch(Exception $e){
				json_output(200,array('status' => 401,'message' => $e->getMessage()));
				}
				
				
				
			}
			else {
				json_output(200,array('status' => 401,'message' => "Auth Failed "));
			}

		}
	} // updateborrower


public function rmcreateborrower()
{
		$method = $_SERVER['REQUEST_METHOD'];
		if($method =="POST")
		{
				$checkToken = $this->check_token();
				if(true)
				{
						$response['status']=200;
						$respStatus = $response['status'];
						$params 	= json_decode(file_get_contents('php://input'), TRUE);
	
						$selectkey 	= isset($params['selectkey']) ? $params['selectkey'] : "*"; 
						$join 		= isset($params['key']) ? $params['key'] : "";
						$where 		= isset($params['where']) ? $params['where'] : "";	
						
						$sql = "WITH borrowerTable as (SELECT b.slug,b.rm_id, b.id, bd.company_industry, bd.company_name, bd.turnover, bd.networth, bd.company_type, bd.profilecomplete, b.rm_name, bd.city
						FROM fpa_users b, fp_borrower_user_details bd 
						WHERE b.slug ='borrower' AND b.status in ('new') AND  b.id = bd.user_id AND bd.company_name is not null AND b.created_by = '" .$where. "' )  
						SELECT bd.rm_name , bd.rm_id, bd.slug, bd.profilecomplete ,bd.city,fp_entitytype.id,bd.id as borrower_id,fp_city.id as location_id, fp_city.name as location, fp_entitytype.name as entity_name,bd.company_name as company_name, bd.company_industry as company_industry,bd.turnover, bd.networth 
						FROM borrowerTable as bd LEFT JOIN fp_city ON bd.city = fp_city.id LEFT JOIN fp_entitytype ON bd.company_type = fp_entitytype.id where bd.company_name is not null order by bd.id desc ";
	
	
						$borrowerdetails = $this->db->query($sql)->result(); 
						$count = $this->db->query($sql)->num_rows(); 
						if($count >=1){
	
					
	
						$data = $this->db->query($sql);
						foreach ($data->result() as $row){
							$txnArr[] = $row->borrower_id;
																								
						}
						$res = implode(",",$txnArr);
						$res  = "(".$res.")";
	
						$result = 'SELECT bl.product_slug,bl.borrower_id,p.name  FROM fp_borrower_loanrequests bl ,fp_products p WHERE bl.product_slug=p.slug and bl.borrower_id in '.$res;
					
						// $this->db->query($sql)-result();
						// $query = $this->db->get_where('fp_borrower_loanrequests', array('borrower_id' => $res))->result();
						// $trnn[]= $data->id;
	
						$resp = array('status' => 200,'message' =>  'Success','data'=> $borrowerdetails,'data1' =>$this->db->query($result)->result());
						return json_output($respStatus,$resp);
					}
					else{
						$resp = array('status' => 200,'message' =>  'Success','data'=> $borrowerdetails);
						return json_output($respStatus,$resp);
					}
				}
				else
				{
					return json_output(400,array('status' => 400,'message' => $checkToken));
				}
			
		}
		else
		{
				return json_output(400,array('status' => 400,'message' => 'Bad request.'));
		}
	
}  // rmcreateborrower 




public function addconnector()
{
    $method = $_SERVER['REQUEST_METHOD'];
    if($method != 'POST'){
    json_output(400,array('status' => 400,'message' => 'Bad request.'));
    }else{
    $checkToken = $this->check_token();
    if($checkToken){
    $response['status']=200;
    $respStatus = $response['status'];
    $params = json_decode(file_get_contents('php://input'), TRUE);
    try{
    $name = $params['data']['name'];
    $email = $params['data']['email'];
    $phone = $params['data']['mobile'];
    $created_by =  $params['data']['created_by'];
    $domain = isset($params['data']['companyname']) ? $params['data']['companyname'] : "";

   
    $emailandmobileverified =1;
    $add_user = $this->db->insert("fpa_users",array('name'=>$name,'email'=>$email, 'mobile'=>$phone ,'slug'=>'connector', 'created_by'=>$created_by,'is_email_verified'=>$emailandmobileverified,'is_mobile_verified'=>$emailandmobileverified));
    $id = $this->db->insert_id();
    
    
    $add_connector =$this->db->insert("fp_connector_user_details", array('user_id'=>$id,'name'=>$name,'email'=>$email, 'phone'=>$phone ,'domain' => $domain));
    if($add_user && $add_connector){
    json_output(200,array('status' => 200,'message' => 'successfully Added',"data"=>$id));
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
}   // addConnector







public function admin_lender_loanproposals()
 {
   $method = $_SERVER['REQUEST_METHOD'];
   if($method =="POST")
   {
     $checkToken = $this->check_token();
     if(true)
     {
       $response['status']=200;
       $respStatus = $response['status'];
       $params  = json_decode(file_get_contents('php://input'), TRUE);

       $selectkey  = isset($params['selectkey']) ? $params['selectkey'] : "*"; 
       $loanapplication_status = isset($params['loanapplication_status']) ? $params['loanapplication_status'] : "";
       $where   = isset($params['where']) ? $params['where'] : ""; 
       $is_created   = isset($params['is_created']) ? $params['is_created'] : ""; 

       $sql="SELECT la.loanrequest_id as lrid ,lm.image , lm.lender_name, p.name as productname, bu.company_name as companyname, la.loanapplication_status as lastatus, la.workflow_status as wfstatus, la.lender_intrest_received,bl.loanamount_slug,bl.loan_min,bl.loan_max,bl.tenor_min,bl.tenor_max,bl.roi_min,bl.roi_max as amount,la.is_created, bu.user_id as borrower_id, p.id as product_id, la.id as loan_app_id

     FROM fpa_loan_applications la,fp_borrower_user_details bu,fp_products p,fp_borrower_loanrequests bl,fp_lender_master lm
     
     WHERE bu.user_id = la.borrower_id 
     and la.product_slug = p.slug and bl.id = la.loanrequest_id and lm.id = la.lendermaster_id AND
     la.loanapplication_status in ".$loanapplication_status .$where.$is_created;

       $resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql)->result());
       return json_output($respStatus,$resp);
     }
     else
     {
      return json_output(400,array('status' => 400,'message' => "Token Failed"));
     }
   }
   else
   {
     return json_output(400,array('status' => 400,'message' => 'Bad request.'));
   }
 }
 // -----------------End loanproposals-----------------


 public function bankstatement()
 {
   $method = $_SERVER['REQUEST_METHOD'];
   if($method =="POST")
   {
     $checkToken = $this->check_token();
     if(true)
     {
       $response['status']=200;
       $respStatus = $response['status'];
       $params  = json_decode(file_get_contents('php://input'), TRUE);

       $selectkey  = isset($params['selectkey']) ? $params['selectkey'] : "*"; 
       $where   = isset($params['where']) ? $params['where'] : ""; 
      

       $sql="SELECT t1.bank_name,t1.account_id, t1.ifsc,t1.type_of_accounts,t1.account_number,t2.total_amount_of_credit_transactions,t2.total_amount_of_debit_transactions,t2.total_no_of_inward_cheque_bounce,t2.total_no_of_outward_cheque_bounce,t2.average_eod_balance,t2.average_credit_transaction_size,t2.average_debit_transaction_size,t1.totalaccounts,t1.from_date_oldest,t1.todate_latest,t3.s3_url FROM  fp_finbox_accounts_details t1 ,fp_finbox_monthly_details t2, fp_finbox_xlsx_report t3 
	   WHERE t1.account_id = t2.account_id and 
	   t2.account_id = t3.account_id and 
	   t1.borrower_id =".$where;

       $resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql)->result());
       return json_output($respStatus,$resp);
     }
     else
     {
      return json_output(400,array('status' => 400,'message' => "Token Failed"));
     }
   }
   else
   {
     return json_output(400,array('status' => 400,'message' => 'Bad request.'));
   }
 }
 // -----------------End loanproposals-----------------



} // -------------------------- end ---------------------
