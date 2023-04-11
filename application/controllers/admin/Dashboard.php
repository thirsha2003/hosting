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

class Dashboard extends CI_Controller
{
            public function __construct()
            {
                parent::__construct();
                $this->load->helper('json_output');
                $this->ci = & get_instance();
                $this->ci->load->database();

            }

            public function check_token()
            {
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
			}

            //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&//
            //                                 Dashboard Summary Card Methods                            //
            //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&//
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
									workflow_status IN ('Deal Sent To Lender','Deal Approved','Deal Sanctioned' ) ";

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
									workflow_status IN ('Deal Approved','Deal Sanctioned') ";
									
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
									workflow_status =  'Deal Sanctioned' ";
									
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
									WHERE workflow_status IN ('Deal Approved', 'Deal Sanctioned')  ";
									
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
									workflow_status =  'Deal Sanctioned' ";
									
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

            //^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^//
            //                              End of Dashboard Summary Card Methods                        //
            //^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^//

			public function gettotalnewleads()
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

									$sql = "SELECT  COUNT(*) as getTotalLeads FROM `fpa_users` WHERE  slug='borrower' AND status ='new'";
				
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


			public function getcompletedprofiles()
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

					$sql = "SELECT COUNT(*) as TotalProfilesCompleted FROM fp_borrower_user_details  WHERE profilecomplete_percentage ='100' and profilecomplete='completed'";
					
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

			public function getworkinprogressprofiles()
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

						$sql = "SELECT COUNT(*) as TotalWorkinProgressProfile FROM fp_borrower_user_details  WHERE profilecomplete_percentage >='50' && profilecomplete_percentage <='99'";
						
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

			public function gettotaluncompletedprofiles()
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

						$sql = "SELECT COUNT(*) as TotalUncompletedProfiles FROM fp_borrower_user_details  WHERE profilecomplete_percentage <'50'";
						
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


			//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&//
			//                    Portfolio Summary Card Methods                  //
			public function gettotalassignedapplications()
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

									$sql = "SELECT COUNT(status)  as TotalAssignedApplication FROM fpa_users t1  WHERE  t1.slug='borrower' and status='assigned';";
				
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


			public function gettotalincompletedprofiles()
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

									$sql = "SELECT count(*) as TotalIncompletedProfiles FROM  fp_borrower_user_details t1 WHERE t1.profilecomplete='incomplete'";
				
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

			public function gettotalcompletedprofiles()
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

									$sql = "SELECT count(*) as TotalcompletedProfiles FROM  fp_borrower_user_details t1 WHERE t1.profilecomplete='complete'";
				
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


			public function gettotalarchivedprofiles()
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

									$sql = "SELECT count(*) as TotalArchivedProfiles FROM `fpa_loan_applications` WHERE loanapplication_status ='Deal Archived'";
				
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
			// New flow : Load Request Table 
			public function gettotalduediligence()
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

						$sql = "SELECT COUNT(*) as TotalDue_Diligence FROM fp_borrower_loanrequests WHERE loan_request_status='Due Diligence'";
						
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
			// Method for CC Approval Pending
			public function gettotalccapprovalpending()
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

						$sql = "SELECT COUNT(*) as TotalCC_Approval_Pending FROM fp_borrower_loanrequests WHERE loan_request_status='CC Approval Pending'";
						
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

			// Method for CC Approved
			public function gettotalapprovedapplications()
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
		
									$sql = "SELECT COUNT(*) as TotalApprovedApplications FROM fp_borrower_loanrequests WHERE loan_request_status='CC Approved'";
				
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
		
		
			public function gettotalarchivedapplications()
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
		
									$sql = "SELECT COUNT(*) as TotalArchivedApplications FROM fp_borrower_loanrequests WHERE loan_request_status='CC Rejected'";
				
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
		
		
			public function gettotaldealsrejected()
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
		
									$sql = "SELECT count(*) as TotalDeals_Rejected FROM `fpa_loan_applications` WHERE  workflow_status='Deal Rejected'";
				
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
		
		
			public function gettotaldealsarchived()
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
		
									$sql = "SELECT count(*) as TotalDeals_Archived FROM `fpa_loan_applications` WHERE  workflow_status='Deal Archived'";
				
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

			//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&//
			//                    Approved rates of RM & Approved Rates of Lenders                  //
			//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&//         
			
			public function getapproveddealsbyrm()
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

									$sql = "SELECT T1.RM, T2.name, COUNT(T2.NAME) as count
									FROM fpa_loan_applications T1, fpa_adminusers T2
									WHERE T1.workflow_status IN ('Deal Approved','Deal Sanctioned') AND T1.RM = T2.ID
									GROUP BY T2.NAME ORDER BY count DESC";
							
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


			public function getapproveddealsbybank()
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

								$sql = "SELECT T2.lender_name AS bankname, COUNT(T2.lender_name ) as countlivedeals, 
								T2.image as banklogo
								FROM fpa_loan_applications T1, fp_lender_master T2
								WHERE T1.workflow_status IN ('Deal Approved','Deal Sanctioned', 'Deal Sent To Lender')  AND T1.lendermaster_id = T2.id
								GROUP BY T2.lender_name 
								ORDER BY countlivedeals DESC";
						
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

			


			//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%//
			//                                  RM/Admin  Dashboard                                    //
			//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%//

			public function taskassignedtorm()
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

									$sql = "With T1 AS (SELECT * FROM `fpa_taskdetails` WHERE ".$where. " group by borrower_id)  SELECT count(*) as total_tasksassingedtorm from T1 ";
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
			public function rmdash_incompleteprofiles()
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

									$sql = "WITH T1 AS (SELECT ta.rm FROM `fpa_taskdetails` as ta, fp_borrower_user_details bu 
									WHERE " .$where. " AND
									ta.borrower_id = bu.user_id AND
									bu.profilecomplete ='incomplete' 
									group by ta.borrower_id) SELECT count(*) AS rm_dashboard_incompleteprofiles from T1 ";
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
			public function rmdash_completeprofiles()
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

									$sql = "SELECT count(*) as rmdash_completedprofiles FROM fpa_taskdetails as ta, fp_borrower_user_details as bu 
									WHERE bu.profilecomplete ='complete'  ";
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
			public function getrm_borrower()
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

									$sql = "SELECT count(*) as Borrowers FROM fpa_taskdetails td, fpa_adminusers ad  WHERE td.task_assigned_to=ad.id AND ad.role_slug='rm' ".$where;
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
		
			public function rmincomplete_borrower()
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

									$sql = "SELECT count(*) as Incomplete FROM fpa_taskdetails td, fpa_adminusers ad, fp_borrower_user_details bu  WHERE td.task_assigned_to=ad.id AND ad.role_slug='rm' and bu.user_id=td.borrower_id and bu.profilecomplete='incomplete'".$where;
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

			public function rmcompleted()
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

									$sql = "SELECT count(*) as Completed FROM fpa_taskdetails td, fpa_adminusers ad, fp_borrower_user_details bu  WHERE td.task_assigned_to=ad.id AND ad.role_slug='rm' and bu.user_id=td.borrower_id and bu.profilecomplete='completed'".$where;
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

			public function rmsubmittedby_cc()
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

									$sql = "SELECT count(*) as CC_Pending FROM fpa_taskdetails td, fpa_adminusers ad, fp_borrower_user_details bu, fpa_loan_applications la WHERE td.task_assigned_to=ad.id AND ad.role_slug='rm' and bu.user_id=td.borrower_id and bu.profilecomplete='incompleted' AND la.workflow_status IN('CC Approval Pending', 'CC Approved') ".$where;
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

			public function rmdeals_sanctioned()
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

									$sql = "SELECT COUNT(*) sanctioned FROM fpa_taskdetails td, fpa_adminusers ad, fpa_loan_applications la  WHERE td.task_assigned_to=ad.id AND ad.role_slug='rm' AND la.borrower_id=td.borrower_id and la.workflow_status='Deals Sanctioned'";
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

			public function rmdue_diligence()
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

									$sql = "SELECT COUNT(*) duediligence FROM fpa_taskdetails td, fpa_adminusers ad, fp_borrower_loanrequests la  WHERE td.task_assigned_to=ad.id AND ad.role_slug='rm' AND la.borrower_id=td.borrower_id and la.loan_request_status='Due Diligence'";
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
	
			public function rmapproved_cc()
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

									$sql = "SELECT COUNT(*) cc_approved FROM fpa_taskdetails td, fpa_adminusers ad, fpa_loan_applications la  WHERE td.task_assigned_to=ad.id AND ad.role_slug='rm' AND la.borrower_id=td.borrower_id and la.workflow_status='CC Approved'".$where;
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

			public function rmdealssend_lender()
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

									$sql = "SELECT COUNT(*) deals_lender FROM fpa_taskdetails td, fpa_adminusers ad, fpa_loan_applications la  WHERE td.task_assigned_to=ad.id AND ad.role_slug='rm' AND la.borrower_id=td.borrower_id and la.workflow_status='Deals Sent to Lender'".$where;
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

	// -----------------------------END RM BORROWER DASHBOARD----------------------------


	public function  assigntome()
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

						$sql = "SELECT COUNT(*) as assigntome FROM fpa_users t1  WHERE t1.slug='borrower' and  t1.rm_id=".$where;


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


	
    } //----------------------assigntome --------------------------------------------------
	public function rmcompleteprofile()
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

							$sql = "SELECT count(*) as Completed FROM fpa_taskdetails td, fpa_adminusers ad, fp_borrower_user_details bu  WHERE td.task_assigned_to=ad.id AND ad.role_slug='rm' and bu.user_id=td.borrower_id and bu.profilecomplete='complete' AND td.task_assigned_to=".$where;

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
		
	} //-----------------------rmcompleteprofile--------------------------------------

	public function rmincompleteprofile()
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

							$sql = "SELECT count(*) as incompleted FROM fpa_taskdetails td, fpa_adminusers ad, fp_borrower_user_details bu  WHERE td.task_assigned_to=ad.id AND ad.role_slug='rm' and bu.user_id=td.borrower_id and bu.profilecomplete='incomplete' and
							bu.profilecomplete_percentage<60 AND td.task_assigned_to=".$where;


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
		
	}   //---------------------rmIncompleteprofile--------------------------------
   
	public function rmloanapplication()
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

							$sql = "SELECT COUNT(*) as applicationcount FROM fpa_loan_applications t1  WHERE t1.rm=".$where;


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
		
	}  //------------------------rmloanapplication ----------------------------------------





}// end of class---------------------------------------------------------------
