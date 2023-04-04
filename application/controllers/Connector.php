<?php
header('Access-Control-Allow-Origin: *'); //for allow any domain, insecure
header('Access-Control-Allow-Headers: *'); //for allow any headers, insecure
header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE'); //method allowed
header("HTTP/1.1 200 OK");

defined('BASEPATH') OR exit('No direct script access allowed');

class Connector extends CI_Controller {

	public function __construct(){
		parent::__construct();
		$this->load->helper('json_output');

		$this->ci =& get_instance();
					$this->ci->load->database();
	} // construct 

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


	public function totalborrowersleads()
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

							$sql = "SELECT count(*) as Total_Borrower_Leads FROM `fpa_users` WHERE 
							slug ='borrower' and status IN ('new','assigned','active') ";
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
	} // totalborrowersleads



	public function totaldraftleads()
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

							$sql = 'SELECT count(*) as Total_Draft_Leads
							FROM fpa_users u,fp_borrower_user_details bd
							WHERE  u.id=bd.user_id and bd.company_name IS NULL  and u.slug="borrower" and u.rm_id IS NULL';
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
	} // totaldraftleads 



	public function totaleligibleleads()
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

							$sql = 'SELECT count(*) as Total_Eligible_Leads
							FROM fpa_users u,fp_borrower_user_details bd
							WHERE  u.id=bd.user_id and bd.company_name IS NOT NULL and u.slug="borrower" AND u.rm_id IS NULL';
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
	}   // totaleligibleleads


	
	public function totalassignedleads()
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

							$sql = "SELECT COUNT(*) as TotalAssigned_Leads FROM fpa_users u  WHERE u.slug='borrower' and status='assigned'";
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
	}   //totalassignedleads


	public function totalapprovedprofile()
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

							$sql = "SELECT count(*) as TotalApproved_Profile FROM `fp_borrower_loanrequests`
							WHERE loan_request_status ='ccapproved' or loan_request_workflow_status='ccapproved'";
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
	}  //  totalapprovedprofiles

	public function connector_borrower_list()
  {
    $method = $_SERVER['REQUEST_METHOD'];
    if($method =="POST")
    {
    //   $checkToken = $this->check_token();
      if(True)
      {
        $response['status']=200;
        $respStatus = $response['status'];
        $params   = json_decode(file_get_contents('php://input'), TRUE);
  
        $selectkey   = isset($params['selectkey']) ? $params['selectkey'] : "*"; 
        $join     = isset($params['key']) ? $params['key'] : "";
        $where     = isset($params['where']) ? $params['where'] : "";  
  
        $sql = "WITH borrowerTable as (SELECT b.slug, b.status, b.id, bd.company_industry, bd.company_name, bd.turnover, bd.networth, bd.company_type, bd.profilecomplete, b.rm_name, bd.city FROM fpa_users b, fp_borrower_user_details bd WHERE b.slug ='borrower' AND b.id = bd.user_id AND bd.company_name is not null) SELECT bd.rm_name , bd.status,  bd.slug, bd.profilecomplete ,bd.city,fp_entitytype.id,bd.id as borrower_id,fp_city.id as location_id, fp_city.name as location, fp_entitytype.name as entity_name,bd.company_name as company_name, bd.company_industry as company_industry,bd.turnover, bd.networth FROM borrowerTable as bd LEFT JOIN fp_city ON bd.city = fp_city.id LEFT JOIN fp_entitytype ON bd.company_type = fp_entitytype.id where bd.company_name is not null;";
  
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
    
  }  // connector_borrower_list 

  public function connectorprofile_details()
  {
      $method = $_SERVER['REQUEST_METHOD'];
       if($method =="POST")
       {
		//    $checkToken = $this->check_token();
		   if(True)
		   {
				   $response['status']=200;
				   $respStatus = $response['status'];
				   $params 	= json_decode(file_get_contents('php://input'), TRUE);

				   $selectkey 	= isset($params['selectkey']) ? $params['selectkey'] : "*"; 
				   $join 		= isset($params['key']) ? $params['key'] : "";
				   $where 		= isset($params['where']) ? $params['where'] : "";
				   $id  = $params['id'];	

				   $sql = "SELECT " .$selectkey. " FROM ".$params['tableName']." WHERE id= " .$id;
				   
				   
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
 }   // ------------------ lenderprofile_details ---------------------


public function connectorbasicprofile(){
	$method = $_SERVER['REQUEST_METHOD'];
	if($method != 'POST'){
		json_output(400,array('status' => 400,'message' => 'Bad request.'));
	}else{
		// $check_auth_user = $this->login->check_auth_user();
		// if($check_auth_user == true){
			// $response = $this->login->auth();
			$response['status'] = 200;
			$respStatus = $response['status'];
			if($response['status'] == 200){
				$params = json_decode(file_get_contents('php://input'), TRUE);
				// $params['user_id_fk'] = $this->input->get_request_header('User-ID', TRUE);

				if ($params['tableName'] == "") {
					$respStatus = 400;
					$resp = array('status' => 400,'message' =>  'Fields Missing');
				} else {
					// $params['data']['user_id_fk'] = (int)$params['user_id_fk'];
					$sql = "SELECT * FROM ".$params['tableName']." WHERE connector_id = ".$params['data']['connector_id'];
						 
							if(count($this->db->query($sql)->result())==0){
								$this->db->insert($params['tableName'], $params['data']);
							}else{
								$this->db->where('connector_id',$params['data']['connector_id'] );
								$this->db->update($params['tableName'], $params['data']); 
							}
					$resp = array('status' => 200,'message' =>  'Inserted success','data' => $this->db->insert_id());
				}
				json_output($respStatus,$resp);
			}
		// }
	}
}   // connectorbaiscprofile

public function getconnectorprofile(){
	$method = $_SERVER['REQUEST_METHOD'];
	if($method != 'POST'){
		json_output(400,array('status' => 400,'message' => 'Bad request.'));
	}else{
		// $check_auth_user = $this->login->check_auth_user();
		// if($check_auth_user == true){
			// $response = $this->login->auth();
			$response['status'] = 200;
			$respStatus = $response['status'];
			if($response['status'] == 200){
				$params = json_decode(file_get_contents('php://input'), TRUE);
				$params['user_id_fk'] = $this->input->get_request_header('User-ID', TRUE);

				if ($params['tableName'] == "") {
					$respStatus = 400;
					$resp = array('status' => 400,'message' =>  'Fields Missing');
				} else {
					// $params['data']['user_id_fk'] = (int)$params['user_id_fk'];
					$sql = "SELECT * FROM ".$params['tableName']." WHERE user_id=".$params['data']['id'];
					if(count($this->db->query($sql)->result())==0){
						$resp = array('status' => 201,'message' =>  'no data','data' => $this->db->query($sql)->result() );
					}else{
						$resp = array('status' => 200,'message' =>  'success','data' => $this->db->query($sql)->result() );
					}
					
				}
				json_output($respStatus,$resp);
			}
		// }
	}
} // getconnectorprofile

public function connectorspocdetails()
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
 
							// $sql="SELECT cud.spoc_name as name, fl.name as location , fd.name as designation   
							// from fpa_users fpu, fp_connector_user_details  cud, fp_location fl , fp_departments fd
							// where cud.user_id=fpu.id AND fpu.slug='connector'AND cud.department_slug=fd.slug AND fpu.is_email_verified =1 AND fpu.is_mobile_verified=1 AND fl.id=cud.location_id ". $where;
                           
						   $sql = " select t2.name as location ,t1.spoc_name, t1.spoc_designation from fp_connector_user_details t1,fp_location t2  where t1.spoc_location=t2.id and ";
                     
 
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
}  // connectorspocdetails 


public function addconnector()
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
		$created_by =  $params['data']['created_by'];
		$emailandmobileverified =1;
		$add_user = $this->db->insert("fpa_users",array('name'=>$name,'email'=>$email, 'mobile'=>$phone ,'slug'=>'connector', 'created_by'=>$created_by,'is_email_verified'=>$emailandmobileverified,'is_mobile_verified'=>$emailandmobileverified));
		$id = $this->db->insert_id();
		$location=$params['data']['location'];
		$designation=$params['data']['designation'];
				
		$add_connector =$this->db->insert("fp_connector_user_details", array('user_id'=>$id,'spoc_name'=>$name,'spoc_email'=>$email, 'spoc_mobile'=>$phone,'spoc_location'=>$location,'spoc_designation'=>$designation,'created_by'=>$params['data']['created_by']));
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


public function connectorprofile_details1()
{
   $method = $_SERVER['REQUEST_METHOD'];
       if($method != 'POST'){
         json_output(400,array('status' => 400,'message' => 'Bad request.'));
       }else{
         $response['status']=200;
         $respStatus = $response['status'];
         
             $params = json_decode(file_get_contents('php://input'), TRUE);
             $params['user_id_fk'] = $this->input->get_request_header('User-ID', TRUE);
             
             $selectkey = isset($params['selectkey']) ? $params['selectkey'] : "*"; 
             $join = isset($params['key']) ? $params['key'] : "";
             $where = isset($params['where']) ? $params['where'] : "";
                   
             $sql = "SELECT " .$selectkey. " FROM ".$params['tableName']."  WHERE ".$where;
           
             $resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql)->row());
             json_output($respStatus,$resp);
       }
}  // connectorprofile_details1
















}//--------------------end of class-------------------------------------------