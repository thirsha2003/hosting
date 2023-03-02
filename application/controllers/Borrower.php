<?php
header('Access-Control-Allow-Origin: *'); //for allow any domain, insecure
header('Access-Control-Allow-Headers: *'); //for allow any headers, insecure
header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE'); //method allowed
header("HTTP/1.1 200 OK");

defined('BASEPATH') OR exit('No direct script access allowed');
include APPPATH . 'ThirdParty/sendgrid-php/sendgrid-php.php';

class Borrower extends CI_Controller {

	public function __construct(){
		parent::__construct();
		$this->load->helper('json_output');
	}

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
				  if ($params['tableName'] == "") 
				  {
					  $respStatus = 400;
					  $resp = array('status' => 400,'message' =>  'Fields Missing');
				  } 
				  else 
				  {
					  $borrowerid = $params['data']['borrower_id'];
					  $product=$params['data']['product_slug'];
					  $conditions = array( 'borrower_id'=>$borrowerid, "product_slug" =>  $product ,"status"=>"A");
					  $this->db->select('id');
					  $this->db->from('fp_borrower_loanrequests');
					  $this->db->where($conditions);
					  $num_results = $this->db->count_all_results();
					//   echo $num_results;
					  if($num_results == 0){
						$this->db->insert('fp_borrower_loanrequests	', $params['data']);
						$loan_request_id = $this->db->insert_id();
						return json_output(200,array('status' => 200,'Message' => "Added"));
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
	}

	public function borrower_requested_loan(){

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
				  $where 		= isset($params['where']) ? $params['where'] : "";
				  if ($params['data'] == "") 
				  {
					  $respStatus = 400;
					  $resp = array('status' => 400,'message' =>  'Fields Missing');
				  } 
				  else 
				  {
					//   $borrowerid = $params['data']['borrower_id'];
					$id=$params['data'];
					$sql = "SELECT fp_products.slug as product_slug, fp_products.name as product_name , fp_borrower_loanrequests.id, fp_borrower_loanrequests.loan_min , fp_borrower_loanrequests.loan_max , fp_borrower_loanrequests.tenor_min, fp_borrower_loanrequests.tenor_max, fp_borrower_loanrequests.roi_min, fp_borrower_loanrequests.roi_max, Date(fp_borrower_loanrequests.created_at) as reqdata, fp_borrower_loanrequests.lender_product_details_id ,fp_borrower_loanrequests.status as status, fp_borrower_loanrequests.borrower_id FROM fp_products, fp_borrower_loanrequests WHERE fp_borrower_loanrequests.product_slug = fp_products.slug and fp_borrower_loanrequests.borrower_id = ".$id ." ORDER BY fp_borrower_loanrequests.id DESC ". $where ;
					$results = $this->db->query($sql)->result();
					$num_results =sizeof($results);
					  if($num_results >0){
						return json_output(200,array('status' => 200,'data' => $results));
					  }else{
						return json_output(200,array('status' => 401,'Message' => "Invalid"));
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
	}

	public function dir_update()
	{
			  $method = $_SERVER['REQUEST_METHOD'];
			  if($method != 'POST')
			  {
				  json_output(400,array('status' => 400,'message' => 'Bad request.'));
			  }else
			  {
				  
					  $response['status'] = 200;
					  $respStatus = $response['status'];
					  if($response['status'] == 200)
					  {
						  $params = json_decode(file_get_contents('php://input'), TRUE);
						  if ($params['tableName'] == "") 
						  {
							  $respStatus = 400;
							  $resp = array('status' => 400,'message' =>  'Fields Missing');
						  } else 
						  {
							  $d_id = isset($params['data']['id']) ? $params['data']['id'] : "0";
							  $sql = "SELECT * FROM ".$params['tableName']." WHERE id =".$d_id;
								  
									  if(count($this->db->query($sql)->result())==0){
										  $this->db->insert($params['tableName'], $params['data']);
									  }else{
										  $this->db->where('id',$params['data']['id'] );
										  $this->db->update($params['tableName'], $params['data']); 
									  }
							  $resp = array('status' => 200,'message' =>  'success','data' => $this->db->insert_id());
						  }
						  json_output($respStatus,$resp);
					  }
				  // }
			  }
	}

	public function doc_upload(){

		$method = $_SERVER['REQUEST_METHOD'];
			  if($method != 'POST')
			  {
				  json_output(400,array('status' => 400,'message' => 'Bad request.'));
			  }else
			  {
				  
					  $response['status'] = 200;
					  $respStatus = $response['status'];
					  if($response['status'] == 200)
					  {
						  $params = json_decode(file_get_contents('php://input'), TRUE);
						  if (isset($params['data']) && isset($params['doc_type']) && isset($params['url']))  
						  {

							
							$br_id = $params['data'];
							$doc_type = $params['doc_type'];
							$url = $params['url'] ;
							

							
							$sql = "SELECT * FROM fp_borrower_docs WHERE delete_status = 1 and borrower_id =".$br_id." and doc_type = '".$doc_type."'";
								
									if(count($this->db->query($sql)->result())==0){
										$this->db->insert('fp_borrower_docs', array("borrower_id"=>$br_id,"doc_type"=>$doc_type,"file_name"=>$url));
										$resp = array('status' => 200,'message' =>  'success','data' => $this->db->insert_id());
									}else{
										$this->db->where(array('borrower_id'=>$br_id,"doc_type"=>$doc_type) );
										$this->db->update('fp_borrower_docs', array("delete_status"=>"0")); 
										$this->db->insert('fp_borrower_docs', array("borrower_id"=>$br_id,"doc_type"=>$doc_type,"file_name"=>$url));
										$resp = array('status' => 200,'message' =>  'success','data' => $this->db->insert_id());
									}
						  } else 
						  {
							$respStatus = 200;
							$resp = array('status' => 400,'message' =>  'Fields Missing');
						}
						  json_output($respStatus,$resp);
					  }
				  // }
			  }
	

	}



	public function doc_delect(){

		$method = $_SERVER['REQUEST_METHOD'];
			  if($method != 'POST')
			  {
				  json_output(400,array('status' => 400,'message' => 'Bad request.'));
			  }else
			  {
				  
					  $response['status'] = 200;
					  $respStatus = $response['status'];
					  if($response['status'] == 200)
					  {
						  $params = json_decode(file_get_contents('php://input'), TRUE);
						  if (isset($params['data']) && isset($params['url']))  
						  {

							
							$br_id = $params['data'];
							$url = $params['url'] ;
							

						  
							$sql = "SELECT * FROM fp_borrower_docs WHERE borrower_id =".$br_id." and file_name = '".$url."'";
								
									if(count($this->db->query($sql)->result())==0){
										json_output(400,array('status' => 400,'message' => 'Bad request.'));
									}else{
										$this->db->where(array('borrower_id'=>$br_id,"file_name"=>$url) );
										$this->db->update('fp_borrower_docs', array("delete_status"=>"0")); 
										$resp = array('status' => 200,'message' =>  'Deleted Success');
										
									}
							

						  } else 
						  {
							$respStatus = 200;
							$resp = array('status' => 400,'message' =>  'Fields Missing');
						}
						  json_output($respStatus,$resp);
					  }
				  // }
			  }
	

	}


public function dir_doc_delect(){

		$method = $_SERVER['REQUEST_METHOD'];
			  if($method != 'POST')
			  {
				  json_output(400,array('status' => 400,'message' => 'Bad request.'));
			  }else
			  {
				  
					  $response['status'] = 200;
					  $respStatus = $response['status'];
					  if($response['status'] == 200)
					  {
						  $params = json_decode(file_get_contents('php://input'), TRUE);
						  if (isset($params['data']) && isset($params['url']) && isset($params['doc']))  	
						  {

							
							$br_id = $params['data'];
							$url = $params['url'];
							$doc = $params['doc'];

							if($doc == 'pan'){
								$sql = "SELECT * FROM fp_director_details WHERE borrower_id =".$br_id." and pan_url = '".$url."'";
								if(count($this->db->query($sql)->result())==0){
										json_output(400,array('status' => 400,'message' => 'Bad request.'));
									}else{
										$this->db->where(array('borrower_id'=>$br_id,"pan_url"=>$url) );
										$this->db->update('fp_director_details', array("pan_url"=>null)); 
										$resp = array('status' => 200,'message' =>  'Deleted Success');
										
									}
							}elseif($doc == 'aadhar'){
								$sql = "SELECT * FROM fp_director_details WHERE borrower_id =".$br_id." and aadhar_url = '".$url."'";
								if(count($this->db->query($sql)->result())==0){
										json_output(400,array('status' => 400,'message' => 'Bad request.'));
									}else{
										$this->db->where(array('borrower_id'=>$br_id,"aadhar_url"=>$url) );
										$this->db->update('fp_director_details', array("aadhar_url"=>null)); 
										$resp = array('status' => 200,'message' =>  'Deleted Success');
										
									}
							}else{

							}
							

						  
						

						  } else 
						  {
							$respStatus = 200;
							$resp = array('status' => 400,'message' =>  'Fields Missing');
						}
						  json_output($respStatus,$resp);
					  }
				  // }
			  }
	

	}
	

	public function dir_addupdate()
	{
			  $method = $_SERVER['REQUEST_METHOD'];
			  if($method != 'POST')
			  {
				  json_output(400,array('status' => 400,'message' => 'Bad request.'));
			  }else
			  {
				  
					  $response['status'] = 200;
					  $respStatus = $response['status'];
					  if($response['status'] == 200)
					  {
						  $params = json_decode(file_get_contents('php://input'), TRUE);
						  if ($params['tableName'] == "") 
						  {
							  $respStatus = 400;
							  $resp = array('status' => 400,'message' =>  'Fields Missing');
						  } else 
						  {
							  $d_id = isset($params['data']['id']) ? $params['data']['id'] : "0";
							  $pan = isset($params['data']['pan']) ? $params['data']['pan'] : "0";
							  $sql = "SELECT * FROM ".$params['tableName']." WHERE status = 1 and id =".$d_id." or pan = '".$pan."'" ;
								  
									  if(count($this->db->query($sql)->result())==0){
										  $this->db->insert($params['tableName'], $params['data']);
									  }else{
										  $this->db->where('id',$params['data']['id'] );
										  $this->db->update($params['tableName'], $params['data']); 
									  }
							  $resp = array('status' => 200,'message' =>  'success','data' => $this->db->insert_id());
						  }
						  json_output($respStatus,$resp);
					  }
				  // }
			  }
	}


	public function doc_upload_des(){

		$method = $_SERVER['REQUEST_METHOD'];
			  if($method != 'POST')
			  {
				  json_output(400,array('status' => 400,'message' => 'Bad request.'));
			  }else
			  {
				  
					  $response['status'] = 200;
					  $respStatus = $response['status'];
					  if($response['status'] == 200)
					  {
						  $params = json_decode(file_get_contents('php://input'), TRUE);
						  if (isset($params['data']) && isset($params['doc_type']) && isset($params['url']))  
						  {

							
							$br_id = $params['data'];
							$doc_type = $params['doc_type'];
							$url = $params['url'] ;
							$des =isset($params['des']) ? $params['des'] : null;
							$active = isset($params['active']) ? $params['active'] : 0;
							

							
							$sql = "SELECT * FROM fp_borrower_docs WHERE delete_status = 1 and borrower_id =".$br_id." and doc_type = '".$doc_type."'";
								
									if(count($this->db->query($sql)->result())==0){
										$this->db->insert('fp_borrower_docs', array("borrower_id"=>$br_id,"doc_type"=>$doc_type,"file_name"=>$url,"description"=>$des,"actives"=>$active));
										$resp = array('status' => 200,'message' =>  'success','data' => $this->db->insert_id());
									}else{
										$this->db->where(array('borrower_id'=>$br_id,"doc_type"=>$doc_type));
										// $this->db->update('fp_borrower_docs', array("delete_status"=>"0"));
										$this->db->insert('fp_borrower_docs',array("borrower_id"=>$br_id,"doc_type"=>$doc_type,"file_name"=>$url,"description"=>$des,"actives"=>$active));
										$resp = array('status' => 200,'message' =>  'success','data' => $this->db->insert_id());
									}

									if($des != null && $active != 0 ){

										if($doc_type=='PAN'){
										$this->db->where(array('user_id'=>$br_id));
										$this->db->update('fp_borrower_user_details', array("pan"=>$des,"pan_url"=>$url));

										}else if($doc_type == 'GST'){
											$this->db->where(array('user_id'=>$br_id));
											$this->db->update('fp_borrower_user_details', array("gst"=>$des,"gst_url"=>$url));
										}

									

									}
						  } else 
						  {
							$respStatus = 200;
							$resp = array('status' => 400,'message' =>  'Fields Missing');
						}
						  json_output($respStatus,$resp);
					  }
				  // }
			  }
	

	}



	

	




}//--------------------end of class-------------------------------------------
