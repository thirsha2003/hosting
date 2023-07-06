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


class Connector_transation extends CI_Controller
{


    public function __construct(){
		parent::__construct();
		$this->load->helper('json_output');		
	
			$this->ci =& get_instance();
			$this->ci->load->database();
		
	}	


    public function  borrowerloanrequest()
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



							}
					
					else
					{
							return json_output(400,array('status' => 400,'message' => 'Bad request.'));
					}
			} 
			else {

				return json_output(400,array('status' => 400,'message' => 'Bad request.'));

			}
	}  //---------------------------- end of borrowerloanrequest-------------------

	
	public function loanapplication()
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
							$id  = ($params['id']) ;
							$slug = ($params['slug']);	
							$product_id = ($params['product_id']);

							if($where == "all"){


							// $sql = "select t2.is_created as is_created, t3.id AS Loanrequest,t2.borrower_id, t4.image as lender_image,
                            //       t2.product_slug,  t2.loanrequest_id as loanrequest_id , t2.loanapplication_status,t2.id as loanappid,  t3.roi_min,t3.roi_max,t3.loan_max,t3.loan_min,t3.tenor_min,t3.tenor_max,t3.created_by,t2.workflow_status, t2.lender_product_id as lender_product_id, t5.name as product_name,t6.poc_name ,t7.name as locationname, t2.created_at, t2.updated_at
                                  
							// 	  from 
                            //       fp_borrower_loanrequests t3,
                            //       fpa_loan_applications t2,
							// 	  fp_lender_master t4,
							// 	  fp_products t5,
							// 	  fp_lender_user_details t6,
							// 	  fp_city t7 
                            //       Where t6.location_id= t7.id and  t5.slug=t2.product_slug and  t4.id= t2.lendermaster_id and t3.id = t2.loanrequest_id AND t2.lender_id=t6.user_id AND  t2.lender_id IS NULL and  t2.borrower_id=".$id;  // old sql 

							$sql = "select t2.is_created as is_created, t3.id AS Loanrequest,t2.borrower_id, t4.image as lender_image,
							t2.product_slug,  t2.loanrequest_id as loanrequest_id , t2.loanapplication_status,t2.id as loanappid,  t3.roi_min,t3.roi_max,t3.loan_max,t3.loan_min,t3.tenor_min,t3.tenor_max,t3.created_by,t2.workflow_status, t2.lender_product_id as lender_product_id, t5.name as product_name,t6.poc_name ,t7.name as locationname, t2.created_at, t2.updated_at, t2.lender_id
							from 
							fp_borrower_loanrequests t3,
							fp_lender_master t4,
							fp_products t5,
						   fpa_loan_applications t2 LEFT JOIN  fp_lender_user_details t6 ON t2.lender_id=t6.user_id LEFT JOIN   fp_city t7 ON t6.location_id= t7.id Where t5.slug=t2.product_slug and  t4.id= t2.lendermaster_id and t3.id = t2.loanrequest_id   AND  t2.borrower_id=".$id;



							}else{

							// $sql = "select t2.is_created as is_created, t3.id AS Loanrequest,t2.borrower_id, t4.image as lender_image,
                            //       t2.product_slug, t2.loanrequest_id as loanrequest_id , t2.loanapplication_status,t2.id as loanappid,  t3.roi_min,t3.roi_max,t3.loan_max,t3.loan_min,t3.tenor_min,t3.tenor_max,t3.created_by,t2.workflow_status, t2.lender_product_id as lender_product_id , t5.name as product_name ,t6.poc_name, t7.name as locationname, t2.created_at, t2.updated_at
                            //       from 
                            //       fp_borrower_loanrequests t3,
                            //       fpa_loan_applications t2,
							// 	  fp_lender_master t4,
							// 	  fp_products t5,
							// 	  fp_lender_user_details t6,
							// 	  fp_city t7 
                            //       Where  t6.location_id= t7.id and t5.slug=t2.product_slug and  t4.id= t2.lendermaster_id and t3.id = t2.loanrequest_id AND t2.lender_id=t6.user_id AND   t2.lender_id IS NULL and t2.borrower_id=".$id." AND t2.product_slug='".$slug."'  ";  // old sql 


							$sql ="select t2.is_created as is_created, t3.id AS Loanrequest,t2.borrower_id, t4.image as lender_image,
							t2.product_slug,  t2.loanrequest_id as loanrequest_id , t2.loanapplication_status,t2.id as loanappid,  t3.roi_min,t3.roi_max,t3.loan_max,t3.loan_min,t3.tenor_min,t3.tenor_max,t3.created_by,t2.workflow_status, t2.lender_product_id as lender_product_id, t5.name as product_name,t6.poc_name ,t7.name as locationname, t2.created_at, t2.updated_at, t2.lender_id
							
							from 
							fp_borrower_loanrequests t3,
							fp_lender_master t4,
							fp_products t5,
						   fpa_loan_applications t2 LEFT JOIN  fp_lender_user_details t6 ON t2.lender_id=t6.user_id LEFT JOIN   fp_city t7 ON t6.location_id= t7.id Where t5.slug=t2.product_slug and  t4.id= t2.lendermaster_id and t3.id = t2.loanrequest_id   AND  t2.borrower_id=".$id." AND t2.product_slug='".$slug."'";

								//   echo $sql;
							}
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




}