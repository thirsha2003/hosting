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




}