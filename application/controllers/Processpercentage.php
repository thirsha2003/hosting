<?php
header('Access-Control-Allow-Origin: *'); //for allow any domain, insecure
header('Access-Control-Allow-Headers: *'); //for allow any headers, insecure
header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE'); //method allowed
header("HTTP/1.1 200 OK");

defined('BASEPATH') OR exit('No direct script access allowed');

class Processpercentage extends CI_Controller {

	public function __construct(){
		parent::__construct();
		$this->load->helper('json_output');
	}



    public function profile_percentage(){

        try
		{
			$method = $_SERVER['REQUEST_METHOD'];
			if($method != 'POST')
			{
					json_output(400,array('status' => 400,'message' => 'Bad request.'));
			}else
			{
					
				
				if(true)
				{
					$params = json_decode(file_get_contents('php://input'), TRUE);
					
					if($params['borrower_id'] != '' )
					{
							$response['status']=200;
							$respStatus = $response['status'];
							 
							$borrower_id = isset($params['borrower_id']) ? $params['borrower_id'] : "";

                            $percentage=[];


                            $Shareholder="SELECT sum(share_holding)as HOLDING FROM `fp_director_shareholding` WHERE borrower_id=".$borrower_id;
                             $holding=$this->db->query($Shareholder)->result();
                             if ($holding==100){
                                $percentage.array_push(2);
                             }
                             else {

                                $percentage.array_push(0);

                             }    //  END OF SHAREHOLDER PERCENTAGE

                             // DOCUMENT UPLOAD PERCENTAGE CODE STARTING

                             $previousYear1 = date("Y", strtotime("-1 year"));
                             $previousYear2 = date("Y", strtotime("-2 year"));
                             $previousYear3 = date("Y", strtotime("-3 year"));
                             $doc_type="AF";
                             
                            $firstyear = $doc_type.$previousYear1 ;
                            $secondyear = $doc_type.$previousYear2;
                            $thirdyear = $doc_type.$previousYear3;

                             $afdocfirst = "SELECT COUNT(doc_type) FROM `fp_borrower_docs` WHERE borrower_id='". $borrower_id."' and delete_status=1 and doc_type LIKE '%$firstyear%'";
                             $afresult=$this->db->query($afdocfirst)->result();
                             if($afresult==1){
                                 $percentage.array_push(10);
                                 
                             }
                             else{
                                 $percentage.array_push(0);
 
                             }  // AFDOCFIRST

                             $afdocsecond = "SELECT COUNT(doc_type) FROM `fp_borrower_docs` WHERE borrower_id='".$borrower_id."' and delete_status=1 and doc_type LIKE '%$secondyear%'";
                             $afresultsecond=$this->db->query($afdocsecond)->result();
                             if($afresultsecond==1){
                                 $percentage.array_push(10);
                                 
                             }
                             else{
                                 $percentage.array_push(0);
 
                             } // AFDOCSECOND
                             $afdocthird = "SELECT COUNT(doc_type) FROM `fp_borrower_docs` WHERE borrower_id='".$borrower_id."' and delete_status=1 and doc_type LIKE '%$thirdyear%'";
                             $afresultthird=$this->db->query($afdocthird)->result();
                             if($afresultthird==1){
                                 $percentage.array_push(10);
                                 
                             }
                             else{
                                 $percentage.array_push(0);
 
                             } // AFDOCTHIRD

                              //----------------AFDOC PERCENTAGE DONE -------------
                              











						




                             
                          $result=100;

							$resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($result)->result());
							json_output($respStatus,$resp);

                            
					}
				}
			}
		}catch(Exception $ex)
		{
			$msg=$ex->getMessage();
			$response['status']=400;
			$resp = array('status' => 400,'message' =>  $msg,'data' => "");
			json_output($respStatus,$resp);
		}



    }




}