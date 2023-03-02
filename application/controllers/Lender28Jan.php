<?php
header('Access-Control-Allow-Origin: *'); //for allow any domain, insecure
header('Access-Control-Allow-Headers: *'); //for allow any headers, insecure
header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE'); //method allowed
header("HTTP/1.1 200 OK");

defined('BASEPATH') OR exit('No direct script access allowed');

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


}//--------------------end of class-------------------------------------------