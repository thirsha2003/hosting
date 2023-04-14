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

class Admin_transation extends CI_Controller 
{

	public function __construct(){
		parent::__construct();
		$this->load->helper('json_output');		
	
			$this->ci =& get_instance();
			$this->ci->load->database();
		
	}	

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
	}

    
	public function loanapplication()
	{
			$method = $_SERVER['REQUEST_METHOD'];
			if($method =="POST")
			{
					$checkToken = $this->check_token();
					if($checkToken)	
					{
							$response['status']=200;
							$respStatus = $response['status'];
							$params 	= json_decode(file_get_contents('php://input'), TRUE);

							$selectkey 	= isset($params['selectkey']) ? $params['selectkey'] : "*"; 
							$join 		= isset($params['key']) ? $params['key'] : "";
							$where 		= isset($params['where']) ? $params['where'] : "";
							$id  = ($params['id']) ;
							$slug = ($params['slug']);	
							$product_id = ($params['product_id']);
				

							$sql = "select t2.is_created as is_created, t3.id AS Loanrequest,t2.borrower_id, t4.image as lender_image,
                                  t2.product_slug, t2.loanrequest_id as loanrequest_id , t2.loanapplication_status,t2.id as loanappid,  t3.roi_min,t3.roi_max,t3.loan_max,t3.loan_min,t3.tenor_min,t3.tenor_max,t3.created_by,t2.workflow_status, t2.lender_product_id as lender_product_id
                                  from 
                                  
                                  fp_borrower_loanrequests t3,
                                  fpa_loan_applications t2,
								  fp_lender_master t4
                                  Where t4.id= t2.lendermaster_id and t3.id = t2.loanrequest_id AND t2.borrower_id=".$id." AND t2.product_slug='".$slug."'  ";

								//   echo $sql;
							
								 $loan_app = $this->db->query($sql)->result(); 
								 $count = $this->db->query($sql)->num_rows(); 
						  
						   if($count >= 1){
				   
						   
								 $data = $this->db->query($sql);
								 foreach ($data->result() as $row){
								  
								   $txnArr[] = $row->lender_product_id;
									// "loanrequest_id" =>  $row->loanrequest_id

								   
								   
								 }
							

									$res = implode(",",$txnArr);

									// $res = array_filter($res, fn ($res) => !is_null($res));
								 $res  = "(".$res.")";
										$sql2 =" select * from fp_lender_master";
							// $sql2 = "select t1.roi_min,t1.roi_max,t1.loan_max,t1.loan_min,t1.tenor_min,t1.tenor_max, t1.id as lender_product_details_id,t2.id as lender_master_id,t2.image as lender_master_image,t2.lender_name as lender_master_name  from fp_lender_product_details t1,fp_lender_master t2 where t2.id=t1.lender_id and product_id =  ".$product_id." and t1.id not in".$res;

								 

							$resp = array('status' => 200,'message' =>  'Success','data' => $loan_app,'data2'=>$this->db->query($sql2)->result());
							return json_output($respStatus,$resp);

		// 					 $fpa_users = "UPDATE fpa_users 
		//    SET status ='assigned', rm_id='".$rmdata->id."',".
		//    "rm_name='".$rmdata->name."',".
		//    "rm_email='".$rmdata->email."' WHERE fpa_users.id=".$taskdata->borrower_id;

					}else{
						
						$sql2 = "select t1.roi_min,t1.roi_max,t1.loan_max,t1.loan_min,t1.tenor_min,t1.tenor_max, t1.id as lender_product_details_id,t2.id as lender_master_id,t2.image as lender_master_image,t2.lender_name as lender_master_name  from fp_lender_product_details t1,fp_lender_master t2 where t2.id=t1.lender_id and product_id =  ".$product_id;
						// echo $sql2;

						$resp = array('status' => 200,'message' =>  'Success','data' => $loan_app,'data2'=>$this->db->query($sql2)->result());
							return json_output($respStatus,$resp);

					}


					
			}
			else
			{
					return json_output(400,array('status' => 400,'message' => 'Bad request.'));
			}
	} 
	}
	public function loanapplication_lender_support()
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
						$id  = ($params['id']) ;
						$slug = ($params['slug']);	
						$product_id = ($params['product_id']);	
			
						// t1.loan_min, t1.loan_max,t1.roi_min,t1.roi_max,t1.tenor_min,t1.tenor_max t2.image,t2.lender_name t1.lender_id as lender_id 

						$sql = "select t1.roi_min,t1.roi_max,t1.loan_max,t1.loan_min,t1.tenor_min,t1.tenor_max, t1.id as lender_product_details_id,t2.id as lender_master_id,t2.image as lender_master_image,t2.lender_name as lender_master_name  from fp_lender_product_details t1,fp_lender_master t2 where t2.id=t1.lender_id and product_id =  ".$product_id;
						
						$resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql)->result());
						return json_output($respStatus,$resp);

	// 					 $fpa_users = "UPDATE fpa_users 
	//    SET status ='assigned', rm_id='".$rmdata->id."',".
	//    "rm_name='".$rmdata->name."',".
	//    "rm_email='".$rmdata->email."' WHERE fpa_users.id=".$taskdata->borrower_id;

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


public function updateworkflowloanapplication(){

	$method = $_SERVER['REQUEST_METHOD'];
		if($method =="POST")
		{
			$response['status']=200;
			$respStatus = $response['status'];
			
			
				$checkToken = $this->check_token();
				if($checkToken){
					$params 	= json_decode(file_get_contents('php://input'), TRUE);
					$borrower_id 		= isset($params['data']['borrower_id']) ? $params['data']['borrower_id'] : "";
					$loanapp_id 		= isset($params['data']['loanapp_id']) ? $params['data']['loanapp_id'] : "";
					$assigned_to_id 		= isset($params['data']['assigned_to_id']) ? $params['data']['assigned_to_id'] : "";
					$remarks 		= isset($params['data']['remarks']) ? $params['data']['remarks'] : "";
					$loanapplication_status 		= isset($params['data']['loanapplication_status']) ? $params['data']['loanapplication_status'] : "";
					$us_email 		= isset($params['data']['us_email']) ? $params['data']['us_email'] : "";
					$us_name 		= isset($params['data']['us_name']) ? $params['data']['us_name'] : "";
					$us_id 		= isset($params['data']['us_id']) ? $params['data']['us_id'] : "";

					$query_str="SELECT * FROM fpa_adminusers where id = ".$assigned_to_id." limit 1";
					$query=$this->db->query($query_str);
					// fetch one row data
					$record=$query->row();
					$rm_name =  $record->name;
					$rm_email = $record->email;
					$rm_id = $record->id;

						$this->db->trans_start(); 

						$loan_application_trr = $this->db->query('SELECT * FROM fpa_loan_applications where id = '.$loanapp_id)->num_rows();
			 			if($loan_application_trr >= 1){
	   
			 			$querycount = $this->db->query('SELECT * FROM fpa_loan_applications where id = '.$loanapp_id)->row();
						 $fp_borrower_loanrequests_id = $querycount->loanrequest_id;
			
			 			   
						}else{
							exit;
						}

						$fp_borrower_loanrequests = array( 
							// 'loan_request_status'  => $loanapplication_status, 
							'loan_request_workflow_status'=> $loanapplication_status, 
							'loan_request_remark'   =>  $remarks,
						
						); 
						$this->db->where('id',$fp_borrower_loanrequests_id);
						$this->db->update('fp_borrower_loanrequests', $fp_borrower_loanrequests);   

					

						$fpa_users = array( 
							'rm_id'  => $assigned_to_id, 
							'rm_email'=> $rm_email, 
							'rm_name'   =>  $rm_name,
							'statusremark' => $remarks
						); 
						$this->db->where('id',$borrower_id);
						$this->db->update('fpa_users', $fpa_users);

						$fpa_taskdetails = array(

							'task_assigned_by'  => $us_id, 
							'rm_email'=> $rm_email, 
							'task_assigned_to'  => $rm_id, 
							'profile_work_status'  => $loanapplication_status, 
							'task_stage'  => $loanapplication_status, 
							'remarks' => $remarks

						);
						// $this->db->where('borrower_id',$borrower_id);
						// $this->db->update('fpa_taskdetails', $fpa_taskdetails);
						// $query = $this->db->get_where('fpa_taskdetails', array('borrower_id' => $borrower_id));
						// $record=$query->row();
						// $fpa_taskdetails_id =  $record->id;
						$querycount = $this->db->query('SELECT * FROM fpa_taskdetails where borrower_id = '.$borrower_id)->num_rows();
			 			if($querycount >= 1){
	   
			 			$querycount = $this->db->query('SELECT * FROM fpa_taskdetails where borrower_id = '.$borrower_id)->row();
						 $fpa_taskdetails_id = $querycount->id;
			
			 				$this->db->where('id',$fpa_taskdetails_id);
						 $this->db->update('fpa_taskdetails', $fpa_taskdetails);      
						}
						else{
						 $this->db->insert('fpa_taskdetails', $fpa_taskdetails);
						 $fpa_taskdetails_id = $this->db->insert_id();
						}

						$fpa_taskdetails_worklog = array(

							'taskdetail_id'  => $fpa_taskdetails_id, 
							'activity'=> $loanapplication_status, 
							'activity_remarks'  => $remarks, 
							

						);
						$this->db->insert('fpa_taskdetails_worklog', $fpa_taskdetails_worklog);

						if($loanapplication_status == "Deal Sent To Lender"){
							$fpa_loan_applications = array(
								"show_to_lender" => "yes",
								"show_to_lender_approved_by	" => $us_id,
								'rm'  => $assigned_to_id,
								'loanapplication_status'  => $loanapplication_status, 
								'workflow_status'=> $loanapplication_status, 
			
							);	
						}else{

							$fpa_loan_applications = array(
								"show_to_lender" => "no",
								'rm'  => $assigned_to_id,
								'loanapplication_status'  => $loanapplication_status, 
								'workflow_status'=> $loanapplication_status, 
			
							);

						}

						

						$this->db->where('id',$loanapp_id);
						$this->db->update('fpa_loan_applications', $fpa_loan_applications);

						if(isset($params['data']['sanctioned_amount']))
						{
							$sanctioned= "UPDATE fpa_loan_applications
						  	SET sanctioned_amount=".$params['data']['sanctioned_amount'] ." WHERE  fpa_loan_applications.borrower_id=".$borrower_id." ";
						   	$sanctionedamount = $this->db->query($sanctioned);

						}else if (isset($params['data']['approved_amount']))
						{
					 
							$approvedamount= "UPDATE fpa_loan_applications
						  	SET approved_amount=".$params['data']['approved_amount'] ." WHERE  fpa_loan_applications.borrower_id=".$borrower_id." ";
						   	$approvedamounts = $this->db->query($approvedamount);
					 
						}

						$fpa_loan_applications_worklog = array(

							'loanapplication_id'  => $loanapp_id, 
							'activity'=> $loanapplication_status, 
							'activity_remarks'  => $remarks, 
							
						);
						$this->db->insert('fpa_loan_applications_worklog', $fpa_loan_applications_worklog);

						

						if ($this->db->trans_status() === FALSE)
						  {
						    $this->db->trans_rollback();
							return json_output(200,array('status' => 200,'message' => 'Try again'));

						  }
						  else
						  {
						    $this->db->trans_complete();
						  }
					 
						return json_output(200,array('status' => 200,'message' => 'Updated Successfully'));
				}else{
					return json_output(400,array('status' => 400,'message' => 'token mismatch'));
				}

		}else{
			return json_output(400,array('status' => 400,'message' => 'Bad request.'));
		}



}


public function profilepercentage()
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
					$percentages  = ($params['percentage']); 
					$borrower_id =($params['borrower_id']);
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
					return json_output(400,array('status' => 400,'message' => $checkToken));
				}
				
					}
					else
					{
				return json_output(400,array('status' => 400,'message' => 'Bad request.'));
					}

    
    }

   public function productslugs()
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

         $sql = 'SELECT bl.product_slug, bl.id  as lq_id, p.id as product_id, bl.borrower_id,p.name,p.image, bl.id FROM fp_borrower_loanrequests bl ,fp_products p WHERE bl.product_slug = p.slug and  bl.is_deleted = "no"  and bl.borrower_id ='.$id;
         
         
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
			   $params   = json_decode(file_get_contents('php://input'), TRUE);
 
			   $selectkey   = isset($params['selectkey']) ? $params['selectkey'] : "*"; 
			   $join     = isset($params['key']) ? $params['key'] : "";
			   $where     = isset($params['where']) ? $params['where'] : "";  
 
			   $sql = "SELECT tdw.created_at as created, tdw.taskdetail_id,tdw.activity as work, tdw.activity_remarks as comment, td.borrower_id, bu.name as borrower FROM fpa_taskdetails_worklog tdw, fpa_taskdetails as td, fp_borrower_user_details bu WHERE tdw.taskdetail_id = td.id AND td.borrower_id=bu.user_id ";
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



   public function lenderprofile_details()
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
					$id  = isset($params['id']) ?  $params['id']: 0;	

					$sql = 'SELECT   la.poc_name,la.email,la.mobile,lm.lender_name,fc.name as location ,fd.name ,lm.image as lenderimage,lm.hq_address as lenderaddress, lm.id as lendermasterid FROM  fp_lender_user_details la ,fp_lender_master lm ,fp_departments  fd ,fp_city  fc WHERE
					la.lender_master_id=lm.id AND la.department_slug=fd.slug AND la.location_id = fc.id AND la.user_id='.$id;
					
					
					$resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql)->result());
					return json_output($respStatus,$resp);
			}
			else
			{
				return json_output(400,array('status' => 400,'message' => 'Auth missing'));
			}
		
	}
	else
	{
			return json_output(400,array('status' => 400,'message' => 'Bad request.'));
	}

    }   // ------------------ lenderprofile_details ---------------------

public function  deal_recieved()
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

                    $sql = " SELECT COUNT(*) as dealapprovedcount FROM fpa_loan_applications as lp WHERE lp.workflow_status='Deal Approved' ";

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

}   // -------------------------- deal_recieved -----------------------------

public function  deal_recieved_forlenderprofilepage()
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

                    $sql = "SELECT COUNT(*) as dealapprovedcount, lu.lender_master_id 
					FROM fpa_loan_applications as lp, fp_lender_user_details as lu 
					WHERE lp.lendermaster_id =lu.lender_master_id AND ".$where;

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

}   // -------------------------- deal_recieved -----------------------------
public function deal_sanctioned()
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

                    $sql = " SELECT COUNT(*) as dealsanctionedcount FROM fpa_loan_applications as lp WHERE lp.workflow_status='Deal Sanctioned' ";

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
 // ------------------- End of deal_sanctoned ----------------------------------
 public function deal_rejected()
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
 
					 $sql = " SELECT COUNT(*) as Dealrejectedcount FROM fpa_loan_applications as lp WHERE lp.workflow_status='Deal Rejected' ";
 
 
					 $resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql)->result());
 
					 //echo $resp;
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
 
 	} // ---------------------------- End of deal_rejected ----------------------- 

   public function loanapplicationstatuschange(){

		$method = $_SERVER['REQUEST_METHOD'];
		 if($method =="POST")
		 {
		  $response['status']=200;
		  $respStatus = $response['status'];
		  
		  
		   $checkToken = $this->check_token();
		   if($checkToken){
			$params  = json_decode(file_get_contents('php://input'), TRUE);
	   
	   
			$borrower_id   = isset($params['data']['borrower_id']) ? $params['data']['borrower_id'] : "";
			$loanapp_id   = isset($params['data']['loanapp_id']) ? $params['data']['loanapp_id'] : "";
			$assigned_to_id   = isset($params['data']['assigned_to_id']) ? $params['data']['assigned_to_id'] : "";
			$remarks   = isset($params['data']['remarks']) ? $params['data']['remarks'] : "";
			$loanapplication_status   = isset($params['data']['loanapplication_status']) ? $params['data']['loanapplication_status'] : "";
			$loanrequestslug = ($params['data']['product_slug']) ?  $params ['data']['product_slug']:"";
	   
			$us_email   = isset($params['data']['us_email']) ? $params['data']['us_email'] : "";
			$us_name   = isset($params['data']['us_name']) ? $params['data']['us_name'] : "";
			$us_id   = isset($params['data']['us_id']) ? $params['data']['us_id'] : "";


			$borrower_name =isset($params['data']['borrower_name']) ?$params['data']['borrower_name']:"";
            $product_name =isset($params['data']['product_name']) ?$params['data']['product_name']:"";
	   
						  
	   
	   
			$query_str="SELECT * FROM fpa_adminusers where id = ".$assigned_to_id." limit 1";
			$query=$this->db->query($query_str);
			// fetch one row data
			$record=$query->row();
			$rm_name =  $record->name;
			$rm_email = $record->email;
			$rm_id = $record->id;
	   
			 $this->db->trans_start(); 
	   
			 $loanrequest = array( 
			  'loan_request_workflow_status'=>$loanapplication_status,
			  'loan_request_status' =>$loanapplication_status,
			  'loan_request_remark'=>$remarks,
			 ); 
			 $where_id = array( 
			  'borrower_id'=>$borrower_id,
			  'product_slug' =>$loanrequestslug,
			 );
			 $this->db->where($where_id);
			 $this->db->update('fp_borrower_loanrequests', $loanrequest);
			 $fpa_users = array( 
			  'rm_id'  => $assigned_to_id, 
			  'rm_email'=> $rm_email, 
			  'rm_name'   =>  $rm_name,
			  'statusremark' => $remarks
			 ); 
			 $this->db->where('id',$borrower_id);
			 $this->db->update('fpa_users', $fpa_users);
	   
			 $fpa_taskdetails = array(
	   
			  'task_assigned_by'  => $us_id, 
			  'rm_email'=> $rm_email, 
			  'task_assigned_to'  => $rm_id, 
			  'profile_work_status'  => $loanapplication_status, 
			  'task_stage'  => $loanapplication_status, 
			  'remarks' => $remarks,
			  'borrower_id' => $borrower_id
	   
			 );
			 $querycount = $this->db->query('SELECT * FROM fpa_taskdetails where borrower_id = '.$borrower_id)->num_rows();
			 if($querycount >= 1){
	   
			 $querycount = $this->db->query('SELECT * FROM fpa_taskdetails where borrower_id = '.$borrower_id)->row();
			 $fpa_taskdetails_id = $querycount->id;
			
			 $this->db->where('id',$fpa_taskdetails_id);
			 $this->db->update('fpa_taskdetails', $fpa_taskdetails);      
			}
			else{
			 $this->db->insert('fpa_taskdetails', $fpa_taskdetails);
			 $fpa_taskdetails_id = $this->db->insert_id();
			}
			 $fpa_taskdetails_worklog = array(
	   
			  'taskdetail_id'  => $fpa_taskdetails_id, 
			  'activity'=> $loanapplication_status, 
			  'activity_remarks'  => $remarks, 
			 );
			 $this->db->insert('fpa_taskdetails_worklog', $fpa_taskdetails_worklog);
			 if ($this->db->trans_status() === FALSE)
			   {
				 $this->db->trans_rollback();
			  return json_output(200,array('status' => 200,'message' => 'Try again'));
	   
			   }
			   else
			   {
				 $this->db->trans_complete();
			   }
                
			   if($loanapplication_status== "CC Approval Pending"){
				$subject = "Dear Superadmin,";
			   $message = "Dear Superadmin,"."<br/><br/>".$product_name." application from ".$borrower_name." - RM ".$rm_name." has been submitted to you for approval.. Kindly review and approve/reject the application."."<br/><br/>".
			   "link : app.finnup.in/#/admin.";
			   
			 
			   $to = "aisha@finnup.in"; 
			   $tos = "rahul@finnup.in";
			   $email = new \SendGrid\Mail\Mail();
			   $email->setSubject($subject);
			   $email->addContent("text/html", $message);
			   $email->setFrom("support@finnup.in", 'FinnUp Team');
			   $email->addTo($to); 
			   $email->addTo($tos);
			   $sendgrid = new \SendGrid("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");
			   try {
				   $response = $sendgrid->send($email);
			   } catch (Exception $e) {
				   echo 'Caught exception: ', $e->getMessage(), "\n";
			   }
		
		
			   }
               






			 
			 return json_output(200,array('status' => 200,'message' => 'Updated Successfully'));
		   }else{
			return json_output(400,array('status' => 400,'message' => 'token mismatch'));
		   }
	   
		 }else{
		  return json_output(400,array('status' => 400,'message' => 'Bad request.'));
		 }
	   
	   
	   
	   } // -------------------------- loanapplicati


	   public function lendermastername()
	   {
		$method = $_SERVER['REQUEST_METHOD'];
		if($method =="POST")
		{
		  $checkToken = $this->check_token();
		  if($checkToken)
		  {
			$response['status']=200;
			$respStatus = $response['status'];
			$params  = json_decode(file_get_contents('php://input'), TRUE);
	   
			$selectkey  = isset($params['selectkey']) ? $params['selectkey'] : "*"; 
			$join   = isset($params['key']) ? $params['key'] : "";
			$where   = isset($params['where']) ? $params['where'] : "";
			$product_slug   = isset($params['product_slug']) ? $params['product_slug'] : "";
			
	   
	   
			$query_str="SELECT * FROM fp_products where slug = '".$product_slug."' limit 1";
			   $query=$this->db->query($query_str);
			   // fetch one row data
			   $record=$query->row();
			   $product_id = $record->id;
	   
	   
		   //  $sql = "SELECT id as lendeid, lender_name FROM fp_lender_master";
	   
			$sql ="select t1.roi_min,t1.roi_max,t1.loan_max,t1.loan_min,t1.tenor_min,t1.tenor_max, t1.id as lender_product_details_id,t2.id as lender_master_id,t2.image as lender_master_image,t2.lender_name as lender_master_name from fp_lender_product_details t1,fp_lender_master t2 where t2.id=t1.lender_id and product_id =  ".$product_id;
			
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
	   
		
	   }  //--------------------- lendermastername----------------------


	   public function applicationstatus()
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

				 $sql = "SELECT * FROM `fpa_loanapplication_status`LIMIT 5";
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

 
} //----------------------  end of applicationstatus ---------------------------

public function  borrowerloanrequest()
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
							$id  = ($params['id']) ;
							$slug = ($params['slug']);	
							// $product_id = ($params['product_id']);
				
							// try{ 

								$sqls= "SELECT  
								t1.borrower_id, 
								t2.name as borrowername,
								t2.company_name as companyname, 
								t1.id AS Loanrequest,
								t1.product_slug, 
								t1.roi_min,
								t1.roi_max,
								t1.loan_max,
								t1.loan_min,
								t1.tenor_min,
								t1.tenor_max,
								t1.created_by,
								t1.loan_request_status,
								t3.name as product_name
								
								from 
								fp_borrower_loanrequests t1, fp_borrower_user_details t2,
								fp_products t3		                                
								Where 
								t3.slug = t1.product_slug and
								t1.borrower_id = t2.user_id and 
								t1.is_deleted = 'no' and
								t1.product_slug= '".$slug."' and
								t1.borrower_id= ".$id."
								";


							   $loan_app = $this->db->query($sqls)->result(); 
							   $resp = array('status' => 200,'message' =>  'Success','data' => $loan_app);
							   return json_output($respStatus,$resp);

						//   return json_output($respStatus,$resp);
							//    $count = $this->db->query($sql)->num_rows(); 

						// 		 if($count >= 1){
						// 	   $data = $this->db->query($sql);
						// 	   foreach ($data->result() as $row){
						// 		 $txnArr[] = $row->lender_product_id;
								 
						// 	   }
						// 	   $res = implode(",",$txnArr);
						// 	   $res  = "(".$res.")";                                     

						//   $sql2 = "select t1.roi_min,t1.roi_max,t1.loan_max,t1.loan_min,t1.tenor_min,t1.tenor_max, t1.id as lender_product_details_id,t2.id as lender_master_id,t2.image as lender_master_image,t2.lender_name as lender_master_name  from fp_lender_product_details t1,fp_lender_master t2 where t2.id=t1.lender_id and product_id =  ".$product_id." and t1.id not in".$res;

						  

						  
						//   $resp = array('status' => 200,'message' =>  'Success','data' => $loan_app,'data2'=>$this->db->query($sql2)->result());
						//   return json_output($respStatus,$resp);
							
							// }


							// else{
						
								// try{
		
								
								// $sql2 = "select t1.roi_min,t1.roi_max,t1.loan_max,t1.loan_min,t1.tenor_min,t1.tenor_max, t1.id as lender_product_details_id,t2.id as lender_master_id,t2.image as lender_master_image,t2.lender_name as lender_master_name  from fp_lender_product_details t1,fp_lender_master t2 where t2.id=t1.lender_id and product_id =  ".$product_id;
							
		
								// $resp = array('status' => 200,'message' =>  'Success','data' => $loan_app,'data2'=>$this->db->query($sql2)->result());
								// 	return json_output($respStatus,$resp);
								// }
								// catch(Exception $e)
								// 	{
								// 		$e->getMessage();
								// 		return json_output($respStatus,array('status' => 201,'messages' => $e,'data'=>null));
								// 	}
		
							// }


							}
							// else{

							// }
							// catch(Exception $e)
							// {
							// 	$e->getMessage();
							// 	return json_output($respStatus,array('status' => 201,'message' => $e ,'data'=>""));
							// }
					
					else
					{
							return json_output(400,array('status' => 400,'message' => 'Bad request.'));
					}
			} 
			else {

				return json_output(400,array('status' => 400,'message' => 'Bad request.'));

			}
	}  //---------------------------- end of borrowerloanrequest-------------------


  public function loan_request()
	{
		  $method = $_SERVER['REQUEST_METHOD'];
		  if($method != 'POST')
		  {
			  json_output(400,array('status' => 400,'message' => 'Bad request.'));
		  }
		  else
		  {
			  $check_auth_user = $this->login->check_auth_user();
			  if($check_auth_user == true){
			  $response['status'] = 200;
			  $respStatus = $response['status'];
			  if($response['status'] == 200)
			  {
				  $params = json_decode(file_get_contents('php://input'), TRUE);
				  if (false) 
				  {
					  $respStatus = 400;
					  $resp = array('status' => 400,'message' =>  'Fields Missing');
				  } 
				  else 
				  {
					  $borrowerid = $params['data']['borrower_id'];
					  $lender_product_details_id = $params['data']['lender_product_details_id'];
					//   $remarks 		= isset($params['data']['remarks']) ? $params['data']['remarks'] : "";
					  $params 	= json_decode(file_get_contents('php://input'), TRUE);
					$borrower_id 		= isset($params['data']['borrower_id']) ? $params['data']['borrower_id'] : "";
					// $loanapp_id 		= $loan_application_id;
					$assigned_to_id 		= isset($params['data']['assigned_to_id']) ? $params['data']['assigned_to_id'] : "";
					$remarks 		= isset($params['data']['remarks']) ? $params['data']['remarks'] : "";
					$loanapplication_status 		= isset($params['data']['loanapplication_status']) ? $params['data']['loanapplication_status'] : "";
					$us_email 		= isset($params['data']['us_email']) ? $params['data']['us_email'] : "";
					$us_name 		= isset($params['data']['us_name']) ? $params['data']['us_name'] : "";
					$us_id 		= isset($params['data']['us_id']) ? $params['data']['us_id'] : "";

					  //isset($params['data']['remarks']) ? $params['data']['remarks'] : "";
					//   $created_by = isset($params['data']['created_by']) ? $params['data']['created_by']:"";
					  $updated_by = isset($params['data']['updated_by']) ? $params['data']['updated_by']:"";
					  $product=$params['data']['product_slug'];
					  $conditions = array( 'borrower_id'=>$borrowerid, "lender_product_id" =>  $params['data']['lender_product_details_id']);
					  $this->db->select('id');
					  $this->db->from('fpa_loan_applications');
					  $this->db->where($conditions);
					  $num_results = $this->db->count_all_results();

					  if($num_results == 1 && $loanapplication_status == 'Deal Sent To Lender'){
						$loanstatus = array( 
						
							'loanapplication_status' =>  "Deal Sent To Lender", 
							'workflow_status' =>  "Deal Sent To Lender",
							// 'loan_request_remark'=>$remarks,
						
						);
						$this->db->where($conditions);
						$this->db->update('fpa_loan_applications', $loanstatus);
						return json_output(200,array('status' => 200,'Message' => "Added successfully"));
					  }





					 
					  
					//   echo $num_results;
					  else if($num_results == 0){
						$conditions = array('borrower_id'=>$borrowerid, "product_slug"=> $params['data']['product_slug'],'status'=>'A');
						$this->db->select('id');
						$this->db->from('fp_borrower_loanrequests');
						$this->db->where($conditions);
						$num_results = $this->db->count_all_results();
						// echo $num_results;
						if($num_results == 0){
							$this->db->insert('fp_borrower_loanrequests', $params['data']);
							$loan_request_id = $this->db->insert_id();
						}else{
							$query=  $this->db->get_where('fp_borrower_loanrequests', array('borrower_id'=>$borrowerid, 'product_slug'=>$params['data']['product_slug']));
							// $loan_request_id = $this->db->get();
							foreach ($query->result() as $row)
						  	{
							$loan_request_id  =  $row->id;
							// echo $loan_request_id;
						 	}
						}

						if(isset($loan_request_id)){

							$query = $this->db->get_where('fp_lender_product_details', array('id' => $params['data']['lender_product_details_id']));
					  		foreach ($query->result() as $row)
						  	{
								  $lender_id  =$row->lender_id;
						  	}

							$dataarr= array(
								'is_created'=>"A",
								'loanrequest_id' => $loan_request_id,
								'borrower_id' => $borrowerid,
								'lendermaster_id' => $lender_id,
								'product_slug' => $product,
								'lender_product_id' =>  $params['data']['lender_product_details_id'],	
								'loanapplication_status' =>  $params['data']['loanapplication_status'],	
								'workflow_status' =>  $params['data']['loanapplication_status'],	
								'rm' =>  $params['data']['rm'],	
								'lender_id'=> $params['data']['lenderuserid'] ? $params['data']['lenderuserid']:" ",
								
							  );

							$this->db->insert('fpa_loan_applications',  $dataarr);
							$loan_application_id = $this->db->insert_id();



							if(true){
								$params 	= json_decode(file_get_contents('php://input'), TRUE);
								$borrower_id 		= isset($params['data']['borrower_id']) ? $params['data']['borrower_id'] : "";
								$loanapp_id 		= $loan_application_id;
								$assigned_to_id 		= isset($params['data']['assigned_to_id']) ? $params['data']['assigned_to_id'] : "";
								$remarks 		= isset($params['data']['remarks']) ? $params['data']['remarks'] : "";
								$loanapplication_status 		= isset($params['data']['loanapplication_status']) ? $params['data']['loanapplication_status'] : "";
								$us_email 		= isset($params['data']['us_email']) ? $params['data']['us_email'] : "";
								$us_name 		= isset($params['data']['us_name']) ? $params['data']['us_name'] : "";
								$us_id 		= isset($params['data']['us_id']) ? $params['data']['us_id'] : "";
			
								$query_str="SELECT * FROM fpa_adminusers where id = ".$assigned_to_id." limit 1";
								$query=$this->db->query($query_str);
								// fetch one row data
								$record=$query->row();
								$rm_name =  $record->name;
								$rm_email = $record->email;
								$rm_id = $record->id;
			
									$this->db->trans_start(); 

									$loanrequest = array( 
										'loan_request_workflow_status'=>$loanapplication_status,
										'loan_request_status' =>$loanapplication_status,
										'loan_request_remark'=>$remarks,
				
									); 
				
									$where_id = array( 
										'borrower_id'=>$borrower_id,
										// 'product_slug' =>$loanrequestslug,
										'product_slug' =>$product,
										
				
									);
				
									$this->db->where($where_id);
									$this->db->update('fp_borrower_loanrequests', $loanrequest);
			
									$fpa_users = array( 
										'rm_id'  => $assigned_to_id, 
										'rm_email'=> $rm_email, 
										'rm_name'   =>  $rm_name,
										'statusremark' => $remarks
									); 
									$this->db->where('id',$borrower_id);
									$this->db->update('fpa_users', $fpa_users);
			
									$fpa_taskdetails = array(

										'task_assigned_by'  => $us_id, 
										'rm_email'=> $rm_email, 
										'task_assigned_to'  => $rm_id, 
										'profile_work_status'  => $loanapplication_status, 
										'task_stage'  => $loanapplication_status, 
										'remarks' => $remarks,
										'borrower_id' => $borrower_id
			
									);
									$querycount = $this->db->query('SELECT * FROM fpa_taskdetails where borrower_id = '.$borrower_id)->num_rows();
									
									if($querycount >= 1){
			
									$querycount = $this->db->query('SELECT * FROM fpa_taskdetails where borrower_id = '.$borrower_id)->row();
									$fpa_taskdetails_id = $querycount->id;
								
									$this->db->where('id',$fpa_taskdetails_id);
									$this->db->update('fpa_taskdetails', $fpa_taskdetails);						
								}
								else{
									$this->db->insert('fpa_taskdetails', $fpa_taskdetails);
									$fpa_taskdetails_id = $this->db->insert_id();
								}
									
			
									$fpa_taskdetails_worklog = array(
			
										'taskdetail_id'  => $fpa_taskdetails_id, 
										'activity'=> $loanapplication_status, 
										'activity_remarks'  => $remarks, 
										
			
									);
									$this->db->insert('fpa_taskdetails_worklog', $fpa_taskdetails_worklog);
			
									if($loanapplication_status == "Deal Sent To Lender"){
										$fpa_loan_applications = array(
											"show_to_lender" => "yes",
											"show_to_lender_approved_by	" => $us_id,
											'rm'  => $assigned_to_id,
											'loanapplication_status'  => $loanapplication_status, 
											'workflow_status'=> $loanapplication_status, 
						
										);	
									}else{
			
										$fpa_loan_applications = array(
											'rm'  => $assigned_to_id,
											'loanapplication_status'  => $loanapplication_status, 
											'workflow_status'=> $loanapplication_status, 
						
										);
			
									}
			
									$this->db->where('id',$loanapp_id);
									$this->db->update('fpa_loan_applications', $fpa_loan_applications);
			
									$fpa_loan_applications_worklog = array(
			
										'loanapplication_id'  => $loanapp_id, 
										'activity'=> $loanapplication_status, 
										'activity_remarks'  => $remarks, 
										
									);
									$this->db->insert('fpa_loan_applications_worklog', $fpa_loan_applications_worklog);
			
									
			
									if ($this->db->trans_status() === FALSE)
									  {
										$this->db->trans_rollback();
										return json_output(200,array('status' => 200,'message' => 'Try again'));
			
									  }
									  else
									  {
										$this->db->trans_complete();
									  }
							
							}

							$borrowerid=isset($params['data']['borrower_id']) ? $params['data']['borrower_id'] : 0;
							$product=isset($params['data']['product_slug']) ? $params['data']['product_slug'] : "";
							$sql ="select name from fp_products where slug="."'".$product."'";
							$productname = $this->db->query($sql)->row();
							$sql ="select name, email,mobile from fpa_users where id=".$borrowerid;

							$userdata= $this->db->query($sql)->row();

							
							$subject ="Finnup App Loan Request Alert! : Admin Action Required";
							$message = "Hello Finnup Admin! <br/><br/>". "There is a loan request from a borrower. Please refer the details below <br/><br/>".
							"Borrower Name :".$userdata->name."<br/>".
							"Product Requested :".$productname->name."<br/>".
							"Contact Email :".$userdata->email."<br/>".
							"Contact Mobile :".$userdata->mobile."<br/>".
							  
							  
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
							  }
							   catch (Exception $e) {
								  echo 'Caught exception: ',  $e->getMessage(), "\n";
							  }
							  return json_output(200,array('status' => 200,'Message' => "Added successfully", 'data'=>$loan_application_id ));

						}else{
							return json_output(200,array('status' => 400,'Message' => "Something When worng"));
						}
						
					  }else{
						return json_output(200,array('status' => 401,'Message' => "Already Submitted"));
					  }
					  
					//   $resp = array('status' => 200,'message' =>  'Inserted success','data' => $this->db->insert_id() );
				  }

				//   json_output($respStatus,$resp);
			  }else{
				return json_output(200,array('status' => 400,'Message' => "Response status When worng"));
			  }
		  // }
	  	}else{
			return json_output(200,array('status' => 400,'Message' => "Invalid Authentication"));
		}
		}
	}   //------------------------loan_request-------------------------------


 public function admin_create_loanrequest()
	{
	$method = $_SERVER['REQUEST_METHOD'];
	if($method != 'POST'){
	 json_output(400,array('status' => 400,'message' => 'Bad request.'));
	}else{
	 
	  $response['status'] = 200;
	  $respStatus = $response['status'];
	  if($response['status'] == 200){
	   $params = json_decode(file_get_contents('php://input'), TRUE);
	 
  
	   if ($params['tableName'] == "") {
		$respStatus = 400;
		$resp = array('status' => 400,'message' =>  'Fields Missing');
	   } 
	   else {
		
		$sql = "SELECT * FROM ".$params['tableName']." WHERE borrower_id='".$params['data']['borrower_id']."' and product_slug='".$params['data']['product_slug']."' and status='A' ";
		  
		  if(count($this->db->query($sql)->result())==0){
		   $this->db->insert($params['tableName'], $params['data']);
		  }else{
		   // $this->db->where('borrower_id',$params['data']['borrower_id'] and 'product_slug',$params['data']['product_slug'] );
		   // $this->db->update($params['tableName'], $params['data']); 
		  }
		$resp = array('status' => 200,'message' =>  'Inserted success','data' => $this->db->insert_id());
	   }
  
  
	   json_output($respStatus,$resp);
	  }
	 
	}
   } // ------------------- admin_create_loanrequest -------------------------



	
   public function activitylog_borrower_user()
   {
    $method = $_SERVER['REQUEST_METHOD'];
    if($method =="POST")
    {
     $checkToken = $this->check_token();
     if($checkToken)
     {
      $response['status']=200;
      $respStatus = $response['status'];
      $params   = json_decode(file_get_contents('php://input'), TRUE);
 
      $selectkey   = isset($params['selectkey']) ? $params['selectkey'] : "*"; 
      $join     = isset($params['key']) ? $params['key'] : "";
      $where     = isset($params['where']) ? $params['where'] : "";  
      $borrower_id     = isset($params['borrower_id']) ? $params['borrower_id'] : "";  
 
      $sql = "SELECT tdw.created_at as created, tdw.taskdetail_id,tdw.activity as work, tdw.activity_remarks as comment, td.borrower_id, bu.name as borrower, ad.name as rmname FROM fpa_taskdetails_worklog tdw, fpa_taskdetails as td, fp_borrower_user_details bu, fpa_adminusers ad WHERE tdw.taskdetail_id = td.id AND td.borrower_id=bu.user_id and td.rm_email=ad.email and td.borrower_id = "  . $borrower_id;
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






  public function getcibildetails()
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




			public function getcibil()
{
				$method = $_SERVER['REQUEST_METHOD'];
				if($method != 'POST'){
				  json_output(400,array('status' => 400,'message' => 'Bad request.'));
				}else{
				  $response['status']=200;
				  $respStatus = $response['status'];
				  
					  $params = json_decode(file_get_contents('php://input'), TRUE);
					
					  
					  $selectkey = isset($params['selectkey']) ? $params['selectkey'] : "*"; 
					  $join = isset($params['key']) ? $params['key'] : "";
					  $where = isset($params['where']) ? $params['where'] : "";
							
					  $sql = "SELECT " .$selectkey. " FROM ".$params['tableName']."  WHERE ".$where;
 
					   $cibilaccounts = $this->db->query($sql)->result();
					
					
					  foreach( $cibilaccounts as  $row ){
						$cibilacc_id=$row->id;
						$director_id=$row->director_id;

						$this->db->limit(6);
						
                       $payment_get = $this->db->get_where("fp_director_cibilpayments" , array('director_id'=>$director_id,'cibilaccountdetail_id'=>$cibilacc_id))->result();
					   
					

					  
					   
					$data[] = [
					'director_id'=> $row->director_id,
					'account_number'=> $row->account_number,
					'account_open_status'=> $row->account_open_status,
					'account_type'=> $row->account_type,
					'currentbalance'=> $row->currentbalance,
					'director_id'=> $row->director_id,
					'id'=> $row->id,
					'lastpayment_date'=> $row->lastpayment_date,
					'membername'=> $row->membername,
					'opened_date'=> $row->opened_date,
					'ownership'=> $row->ownership,	
					'reported_date'=> $row->reported_date,	
					'status'=> $row->status,
					'payment'=>$payment_get
					   ];
					  }
						
					  json_output(200,array('status' => 200,'message' => 'successfully Feteach Data',"data"=>$data));
					
				
			}
}  



}


