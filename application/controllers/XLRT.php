<?php
header('Access-Control-Allow-Origin: *'); //for allow any domain, insecure
header('Access-Control-Allow-Headers: *'); //for allow any headers, insecure
header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE'); //method allowed
header("HTTP/1.1 200 OK");    
require 'vendor/autoload.php';

defined('BASEPATH') OR exit('No direct script access allowed');
include APPPATH . 'ThirdParty/sendgrid-php/sendgrid-php.php';
include APPPATH . 'ThirdParty/mTalkz.php';
include APPPATH . 'libraries/Femail.php';
include APPPATH . 'libraries/JsonuploadtoS3.php'; 
use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;


class XLRT extends CI_Controller 
{

	      public function __construct()
          {
		parent::__construct();
		$this->load->helper('json_output');
        $this->load->helper('finance');
        $this->load->library('encryption');
        $this->load->library('XLSXWriter');

		$this->ci =& get_instance();
		$this->ci->load->database();
          }


          public function xlrt_entityid_update()
          {

        $method = $_SERVER['REQUEST_METHOD'];
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {

            if (true) {
                $response['status'] = 200;
                $respStatus = $response['status'];
                $params = json_decode(file_get_contents('php://input'), TRUE);
                try {
                    $borrower_id = $params['borrower_id'];
                    $entity_id=$params['data']['entityid']; 
                    $entity_name=$params['data']['entityname']; 

                    $fp_borrower_details = array(
                        'xlrt_entity_id' => $entity_id,
                        'xlrt_custemer_name' => $entity_name,
                        
                    );

                    $this->db->where('user_id', $borrower_id);
                    $this->db->update('fp_borrower_user_details', $fp_borrower_details);
                    // $result = $this->db->query($sql)->result();

                    json_output(200, array('status' => 200, 'data'=>$entity_id,'data1'=>$entity_name, 'message' => 'successfully Added'));
                } catch (Exception $e) {
                    json_output(200, array('status' => 401, 'message' => $e->getMessage()));
                }
            } else {
                json_output(200, array('status' => 401, 'message' => "Auth Failed "));
            }

        }

          }   // xlrt_entityid_update

       
         public function PostProcessingStatus()
         {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method == 'POST') {
            ///////////commented to skip the user login
            $check_auth_user = true; // $this->login->check_auth_user();
            if ($check_auth_user == true) {
                // Get the incoming request data
                echo 'test';
                $request_data = json_decode(file_get_contents('php://input'), true);

                // Validate the request data
                if (empty($request_data)) {
                    http_response_code(400); // Bad request
                    echo json_encode(array('error' => 'Request data is empty'));
                    exit();
                }
                $token = $this->input->get_request_header('Access_code', TRUE);

                ///////////sample JWT token to pass info
                ////eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyIjoiZmlubnVwIn0.wQCWD2B69-H-svUxORcYYFxNRXQqcdf9K41dNIU5I-8
                ////////Access_code is the header
                $decoded = JWT::decode($token, 'finnupkey', true);


                if ($decoded->user == 'finnup') {
                    // Send the response back to the client
                    http_response_code(200); // OK
                    header('Content-Type: application/json');

                    // Prepare the response data
                    $response_data = array(
                        'success' => true,
                        'message' => 'Request received successfully',
                        'accesstoken' => $decoded,
                        'inputdata' => $request_data
                    );
                   
                    echo json_encode($response_data);
                    if (strtolower(trim($request_data['state'])) == strtolower('processingsuccess')) {
                        $accessToken = $this->authXLRT();
                    }
                    exit();
                } else {
                    return json_output(400, array('status' => 400, 'message' => 'Bad request.'));

                }

            } else {
                return json_output(400, array('status' => 401, 'message' => 'Unauthorized Request'));

            }
        } else {
            return json_output(400, array('status' => 400, 'message' => 'Bad request.'));

        }
         } // [ postprocessingStatus
           function headersToArray($str)
           {
           $headers = array();
           $headersTmpArray = explode("\r\n", $str);
           for ($i = 0; $i < count($headersTmpArray); ++$i) {
            // we dont care about the two \r\n lines at the end of the headers
            if (strlen($headersTmpArray[$i]) > 0) {
                // the headers start with HTTP status codes, which do not contain a colon so we can filter them out too
                if (strpos($headersTmpArray[$i], ":")) {
                    $headerName = substr($headersTmpArray[$i], 0, strpos($headersTmpArray[$i], ":"));
                    $headerValue = substr($headersTmpArray[$i], strpos($headersTmpArray[$i], ":") + 1);
                    $headers[$headerName] = $headerValue;
                }
            }
        }
        return $headers;
           } // headersTOArray
        
           function DisplayAmount($amount, $unit)
           {
                $displayAmount = 0;
        
                switch($unit)
                {
                    case "lakh" :
                        $displayAmount = $amount / 100000;
                        break;
                    case "million" : 
                        $displayAmount = $amount / 1000000;
                        break;
                    case "crore" : 
                        $displayAmount = $amount / 10000000;
                        break;
                    default:
                    $displayAmount = $amount;
                }
        
                return number_format((float)$displayAmount, 2, '.', '');
           } // DisplayAmount
        
           function GetBalanceSheetData11_old()
           {
					$method = $_SERVER['REQUEST_METHOD'];
					if($method =="POST")
					{
					                $response['status']=200;
									$respStatus = $response['status'];
									
									$params 	= json_decode(file_get_contents('php://input'), TRUE);
									$param	= json_decode(file_get_contents('php://input'), FALSE);
									// $param 	= file_get_contents('php://input');
									// $params 	= json_decode($jsonString);
									$jsonString 	= $param->jsondata;
									$where		= isset($params['key']) ? $params['key'] : "";
									$tablename		= isset($params['tableName']) ? $params['tableName'] : "";	

                                    $jsonData  = $jsonString;
                                    $type       = 'BS';
                                    
                                      
                                    // $jsonDatas = json_decode($jsonString, true);

                                    // print_r($jsonDatas);

                                    // print_r($jsonData);

                                    // print_r(json_decode($jsonData)); 
                                    // print_r($jsonData->status);
									// $jsonData = json_decode(json_encode($jsonString),FALSE);

                                    // print_r($jsonData->status);
                          $bsData = null;
                        // echo  gettype ($jsonData->body);
						
                        

                          if(isset($jsonData->status) && $jsonData->status == true && isset  ($jsonData->body) && count($jsonData->body) > 0)
                          {
								
                              foreach($jsonData->body as $bodydata)
                              {
								
                                  if($bodydata->fintype == $type || count($jsonData->body) == 1)
                                  {
                            
                            		$body = $bodydata;
									
                            		if(isset($body->periods) && $body->periods != null && count($body->periods) > 0)
                            		{
										
										
                                	foreach ($body->periods as $period) {
                                    
                                    $_period = array(
                                        "ptype" => $period->perioddef->ptype,
                                        "year" => $period->year,
                                        "key" => $period->key,
                                        "datattype" => $period->datatype->datattype
                                    );
        
                                    $bsData["periods"][] = $_period;
                                }
                            }
							
                            if(isset($body->components) && $body->components != null && count($body->components) > 0)
                            {

                               
                                foreach ($body->components as $component) {
        
                                    if($component->compcode == "BS")
                                    {
                                        if(isset($component->items) && $component->items != null && count($component->items) > 0)
                                        {
                                            foreach ($component->items as $finitem) {
        
                                                $_lineitem = array(
                                                    "classname" => $finitem->classification->classname,
                                                    "subclassname" => $finitem->classification->subclassname,
                                                    "label" => $finitem->label,
                                                    "values" => $finitem->values,
                                                    "calculatedvalues" => $finitem->calculatedvalues,
                                                );
                    
                                                   $bsData["lineitems"][] = $_lineitem;
        
                                            }
                                        }
                                       
                                    }
        
                                }
                            }
                                  }
                              }
                          }
                   }
                //var_dump($bsData);
                // return "SDS";
                // return $bsData;
                $resp = array('status' => 200,'message' =>  'Success','data' => $bsData);
                json_output(200,$resp);
                // else{
                //     json_output($respStatus,$resp);
                // }
   
           }
                
            // function GetIncomeStatementData($jsonData, $type)
            function GetBalanceSheetData_old()
            {
                $method = $_SERVER['REQUEST_METHOD'];
                $method = $_SERVER['REQUEST_METHOD'];
                if($method != 'POST')
                {
                    json_output(400,array('status' => 400,'message' => 'Bad request.'));
                }
                else
                {
                    $data = null;
                    $check_auth_user = $this->login->check_auth_user();
        
                    if($check_auth_user == true)
                    {
                        $params = json_decode(file_get_contents('php://input'), TRUE);
                        $borrowerid = $params['data']['id'];
        
                        $unit = "lakh";
        
                        $response['status']=200;
                        $respStatus = $response['status'];
        
                        $financialSummary = array("periods" => null, "lineitems"=> null);
                        $bsAnalysis = array("periods" => null, "lineitems"=> null);
                        $plAnalysis = array("periods" => null, "lineitems"=> null);
                        $cfAnalysis = array("periods" => null, "lineitems"=> null);
                        
        
                        $sql = " SELECT year, period_type, result_type FROM `fp_borrower_financials_summary` where borrower_id = '$borrowerid' order by year desc ";
                        $periodResults = $this->db->query($sql)->result();
                        $num_results =sizeof($periodResults);
        
                        if($num_results > 0)
                        {
                            $fsPeriods = null;
        
                            foreach($periodResults as $periodItem)
                            {
                                $fsPeriods[] = array("ptype" => $periodItem->period_type, "year" => $periodItem->year, "key" => $periodItem->year."-".$periodItem->period_type);
                            }
        
                            $sql = "SELECT * from fp_borrower_financials_summary where borrower_id = '$borrowerid' " ;
                            $fsDbData = $this->db->query($sql)->result();
                            $num_results =sizeof($fsDbData);
        
                            $results = GetFinancialSummaryFromDB($fsPeriods, $fsDbData, $unit);
        
                            $financialSummary = array("periods" => $fsPeriods, "lineitems"=> $results);
                        }
        
                        $sql = " SELECT year, period_type, result_type FROM `fp_borrower_financials_bsanalysis` where borrower_id = '$borrowerid' order by year desc ";
                        $periodResults = $this->db->query($sql)->result();
                        $num_results =sizeof($periodResults);
        
                        if($num_results > 0)
                        {
                            $bsPeriods = null;
        
                            foreach($periodResults as $periodItem)
                            {
                                $bsPeriods[] = array("ptype" => $periodItem->period_type, "year" => $periodItem->year, "key" => $periodItem->year."-".$periodItem->period_type);
                            }
        
                            $sql = "SELECT * from fp_borrower_financials_bsanalysis where borrower_id = '$borrowerid' " ;
                            $bsDbData = $this->db->query($sql)->result();
                            $num_results =sizeof($bsDbData);
        
                            $results = GetBalanceSheetAnalysisFromDB($bsPeriods, $bsDbData,$unit);
        
                            $bsAnalysis = array("periods" => $bsPeriods, "lineitems"=> $results);
                        }
        
        
                        $sql = " SELECT year, period_type, result_type FROM `fp_borrower_financials_planalysis` where borrower_id = '$borrowerid' order by year desc ";
                        $periodResults = $this->db->query($sql)->result();
                        $num_results =sizeof($periodResults);
        
                        if($num_results > 0)
                        {
                            $plPeriods = null;
        
                            foreach($periodResults as $periodItem)
                            {
                                $plPeriods[] = array("ptype" => $periodItem->period_type, "year" => $periodItem->year, "key" => $periodItem->year."-".$periodItem->period_type);
                            }
        
                            $sql = "SELECT * from fp_borrower_financials_planalysis where borrower_id = '$borrowerid' " ;
                            $plDbData = $this->db->query($sql)->result();
                            $num_results =sizeof($plDbData);
        
                            $results = GetProfitAndLossAnalysisFromDB($plPeriods, $plDbData, $unit);
        
                            $plAnalysis = array("periods" => $plPeriods, "lineitems"=> $results);
                        }
        
        
                        $sql = " SELECT year, period_type, result_type FROM `fp_borrower_financials_cfanalysis` where borrower_id = '$borrowerid' order by year desc ";
                        $periodResults = $this->db->query($sql)->result();
                        $num_results =sizeof($periodResults);
        
                        if($num_results > 0)
                        {
                            $cfPeriods = null;
        
                            foreach($periodResults as $periodItem)
                            {
                                $cfPeriods[] = array("ptype" => $periodItem->period_type, "year" => $periodItem->year, "key" => $periodItem->year."-".$periodItem->period_type);
                            }
        
                            $sql = "SELECT * from fp_borrower_financials_cfanalysis where borrower_id = '$borrowerid' " ;
                            $cfDbData = $this->db->query($sql)->result();
                            $num_results =sizeof($cfDbData);
        
                            $results = GetCashFlowAnalysisFromDB($cfPeriods, $cfDbData, $unit);
        
                            $cfAnalysis = array("periods" => $cfPeriods, "lineitems"=> $results);
                        }
        
                        
                        return json_output(200,array('status' => 200,'data' => array(
                            "financialsummary"=> $financialSummary,
                            "bsanalysis"=> $bsAnalysis,
                            "planalysis"=> $plAnalysis,
                            "cfanalysis"=> $cfAnalysis
                            ) ));
                    }
                    else
                    {
                        return json_output(200,array('status' => 400,'Message' => "Invalid Authentication"));
                    }
                }
            } // End of GetBalanceSheetdata

            public function xlrt_fileupload_response(){
                $method = $_SERVER['REQUEST_METHOD'];
                if($method =="POST")
                { 
                           if(True)
                           {
                                       $response['status']=200;
                                       $respStatus = $response['status'];
                                       $params  = json_decode(file_get_contents('php://input'), TRUE);
                                       $borrower_id =($params['borrower_id']);
                                       $documenttype = isset($params['data']['documenttype']) ?  $params['data']['documenttype']:"";
                                       $dmscode = isset($params['data']['dmscode']) ?  $params['data']['dmscode']:"";
                                       $filename = isset($params['data']['filename']) ?  $params['data']['filename']:"";
                                       $entityid = isset($params['data']['entityid']) ?  $params['data']['entityid']:"";
                                       $jwt_token = isset($params['jwt_token']) ?  $params['jwt_token']:"";
                                                                                   
                                       $inarr = array('borrower_id'=>$borrower_id,'documenttype'=> $documenttype,'dmscode'=>$dmscode,'filename'=> $filename,'entity_id'=> $entityid,'jwt_token'=> $jwt_token);
                                       $this->db->insert("fp_xlrt_response", $inarr);
                                       $id = $this->db->insert_id();



                                    //    document processing status 

                                    //    $document_upload = array("xlrt_document_status"=>"AFTER"); 


                                    //    $this->db->where("user_id", $borrower_id);
                                    //    $this->db->update("fp_borrower_user_details", $document_upload);

                                    //    document processing status complete 


                                       return json_output(200,array('status' => 200,'message' => 'Successfully Insert','data'=>$id));                                     
                           }
                           else
                           {
                               return json_output(400,array('status' => 400,'message' => 'Bad request'));
                           }
               }
               else
               {
                            return json_output(400,array('status' => 400,'message' => 'Bad request.'));
               }
    
            }    // end of xlrt_fileupload_response
            public function fp_xlrt_wrongresponse(){
                $method = $_SERVER['REQUEST_METHOD'];
                if($method =="POST")
                { 
                           if(True)
                           {
                                       $response['status']=200;
                                       $respStatus = $response['status'];
                                       $params  = json_decode(file_get_contents('php://input'), TRUE);
                                       $borrower_id =($params['borrower_id']);
                                       $documenttype = isset($params['data']['documenttype']) ?  $params['data']['documenttype']:"";
                                       $dmscode = isset($params['data']['dmscode']) ?  $params['data']['dmscode']:"";
                                       $filename = isset($params['data']['filename']) ?  $params['data']['filename']:"";
                                       $entityid = isset($params['data']['entityid']) ?  $params['data']['entityid']:"";
                                       $jwt_token = isset($params['jwt_token']) ?  $params['jwt_token']:"";
                                                                                   
                                       $inarr = array('borrower_id'=>$borrower_id,'documenttype'=> $documenttype,'dmscode'=>$dmscode,'filename'=> $filename,'entity_id'=> $entityid,'jwt_token'=> $jwt_token);
                                       $this->db->insert("fp_xlrt_wrongresponse", $inarr);
                                       $id = $this->db->insert_id();

                                       return json_output(200,array('status' => 200,'message' => 'Successfully Insert','data'=>$id));                                     
                           }
                           else
                           {
                               return json_output(400,array('status' => 400,'message' => 'Bad request'));
                           }
               }
               else
               {
                            return json_output(400,array('status' => 400,'message' => 'Bad request.'));
               }
    
            }     // fp_xlrt_wrongresponse   

            public function finnupxlrtwebhook()

            {
                
                $response['status'] = 200;
                $respStatus = $response['status'];
                $method = $_SERVER['REQUEST_METHOD'];  
                if ($method != 'POST') {
                    json_output(400, array('status' => 400, 'message' => 'Bad request.'));
                } else {

                    $token   = $this->ci->input->get_request_header('XlrtToken', TRUE);
                     
                    $xlrt_token=$this->config->item('XlrtToken');
                    
                    if($xlrt_token==$token){
                        if ($response['status'] == 200) {
                            
                            $params = json_decode(file_get_contents('php://input'), TRUE);
                            $state = isset($params['state']) ? $params['state'] : " ";
                            $dmscode = isset($params['dmscode']) ? $params['dmscode'] : " ";
                            $docname = isset($params['docname']) ? $params['docname'] : " ";

                            $logs=array("dmscode"=> $dmscode,"state"=>$state);
                            $this->db->insert('fp_xlrt_log',$logs);
                            if($state="ProcessingSuccess"){
                                $this->xlrtgetExtractionResponse($dmscode);      
                                $this->db->where(array('file_name'=>$docname));
                                $this->db->update('fp_xlrt_file_log', array("analysis"=>'yes',"before_after"=>"after","analysis_status"=>"success"));
                                json_output(200, array('status' => 200,'message' => 'Success!')); 
                            }else{
                                $this->db->where(array('file_name'=>$docname));
                                $this->db->update('fp_xlrt_file_log', array("analysis"=>'yes',"before_after"=>"after","analysis_status"=>"failed"));
                            }
                        }
                    }
                    else{
                        json_output(400,array('status' => 404,'message' => 'Authentication credentials were not provided'));
                    }  
                    //json_output(200, array('status' => 200,'message' => 'Success!')); 
                }
            }  
            //  end of finnupxlrtwebhook
            public function getborrowerid($dmscode){
                $arr = array();
                $sql = "select  borrower_id,jwt_token from fp_xlrt_response where dmscode = '"."$dmscode"."'";
                 $result = $this->db->query($sql)->result();

                 
             
                    $arr = [$result[0]->borrower_id,$result[0]->jwt_token
                    ];
                 return $arr;

            }  // End of getborrowerid 

            public function  xlrtgetExtractionResponse($dmscode){

                $aws= new \App\Libraries\JsonuploadtoS3;
                $borrower_data = $this->getborrowerid($dmscode);

                $borrowerid =  $borrower_data[0];
                $jwt_token =  $borrower_data[1];

                $extratin_url = "https://finnup.xlrt.ai/fst-api//financialdocs/";
                $extration_url_end = "/extraction?checkpoints=true&children=true&unitType=ACTUALS";
                $dmscodes = $dmscode; 
                $xlrt_str = $extratin_url.$dmscodes.$extration_url_end;

               $ch = curl_init();

                curl_setopt_array($ch, array(
                CURLOPT_URL => $xlrt_str,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    'accept: */*',
                    "Authorization: ".$jwt_token,
                ),
            )
            );
              $response_xlrt = curl_exec($ch);

            
              var_dump($response_xlrt);
              
              curl_close($ch);
              $xlrt_response= json_decode($response_xlrt);

              $this->AnalyzeData($borrowerid,$xlrt_response);


              //  AWS CODE START 

            // $projson = json_encode($response_xlrt);
            // $foldername ="XLRT/";
            // $aws->aws_s3bucket_xlrt($borrowerid,$dmscode,$foldername,$projson);

            // AWS END CODE

            }  // End of XlrtgetExtractionResponse
            


   
            


      //     code by ankit  20 JUNE 23 

      public  function GetBalanceSheetData11(){
                      $method = $_SERVER['REQUEST_METHOD'];
                      if($method =="POST")
                      {
                                      $response['status']=200;
                                      $respStatus = $response['status'];
                                      
                                      $params 	= json_decode(file_get_contents('php://input'), TRUE);
                                      $param	= json_decode(file_get_contents('php://input'), FALSE);
                                      // $param 	= file_get_contents('php://input');
                                      // $params 	= json_decode($jsonString);
                                      $jsonString 	= $param->jsondata;
                                      $where		= isset($params['key']) ? $params['key'] : "";
                                      $tablename		= isset($params['tableName']) ? $params['tableName'] : "";	
  
                                      $jsonData  = $jsonString;
                                      $type       = 'BS';
                                      
                                        
                                      // $jsonDatas = json_decode($jsonString, true);
  
                                      // print_r($jsonDatas);
  
                                      // print_r($jsonData);
  
                                      // print_r(json_decode($jsonData)); 
                                      // print_r($jsonData->status);
                                      // $jsonData = json_decode(json_encode($jsonString),FALSE);
  
                                      // print_r($jsonData->status);
                            $bsData = null;
                          // echo  gettype ($jsonData->body);
                          
                          
  
                            if(isset($jsonData->status) && $jsonData->status == true && isset  ($jsonData->body) && count($jsonData->body) > 0)
                            {
                                  
                                foreach($jsonData->body as $bodydata)
                                {
                                  
                                    if($bodydata->fintype == $type || count($jsonData->body) == 1)
                                    {
                              
                                      $body = $bodydata;
                                      
                                      if(isset($body->periods) && $body->periods != null && count($body->periods) > 0)
                                      {
                                          
                                          
                                      foreach ($body->periods as $period) {
                                      
                                      $_period = array(
                                          "ptype" => $period->perioddef->ptype,
                                          "year" => $period->year,
                                          "key" => $period->key,
                                          "datattype" => $period->datatype->datattype
                                      );
          
                                      $bsData["periods"][] = $_period;
                                  }
                              }
                              
                              if(isset($body->components) && $body->components != null && count($body->components) > 0)
                              {
  
                                 
                                  foreach ($body->components as $component) {
          
                                      if($component->compcode == "BS")
                                      {
                                          if(isset($component->items) && $component->items != null && count($component->items) > 0)
                                          {
                                              foreach ($component->items as $finitem) {
          
                                                  $_lineitem = array(
                                                      "classname" => $finitem->classification->classname,
                                                      "subclassname" => $finitem->classification->subclassname,
                                                      "label" => $finitem->label,
                                                      "values" => $finitem->values,
                                                      "calculatedvalues" => $finitem->calculatedvalues,
                                                  );
                      
                                                     $bsData["lineitems"][] = $_lineitem;
          
                                              }
                                          }
                                         
                                      }
          
                                  }
                              }
                                    }
                                }
                            }
  
  
                     }
                  //var_dump($bsData);
                  // return "SDS";
                  // return $bsData;
                  $resp = array('status' => 200,'message' =>  'Success','data' => $bsData);
                  json_output(200,$resp);
                 
  
                  // else{
                  //     json_output($respStatus,$resp);
                  // }
     
      }   //  END OF GETBALANCESHEETDATA1
       public function DownloadFinancials(){
  
          $method = $_SERVER['REQUEST_METHOD'];
          if($method != 'POST')
          {
              json_output(400,array('status' => 400,'message' => 'Bad request.'));
          }
          else
          {
              $downloadUrl = "http://localhost/fin/api/";   ///  CHAGING THIS URL IN LIVE   
            //   $downloadUrl = "https://app.finnup.in/api/";   ///   THIS URL IN LIVE   
              $data = null;
              $check_auth_user = $this->login->check_auth_user();
  
              if($check_auth_user == true)
              {
                  $params = json_decode(file_get_contents('php://input'), TRUE);
                  $borrowerid = $params['data']['id'];
                  $fintype = $params['fintype'];
  
                  $unit = "lakh";

                $bsSourceData = array();
                $bsSource = $this->DownloadBSSource($borrowerid);
                $bssLineItems = $bsSource["lineitems"];
                $bssRType = $bsSource["rtype"];
                $bssYears = $bsSource["years"];

                $plSourceData = array();
                $plSource = $this->DownloadPLSource($borrowerid);
                $plsLineItems = $plSource["lineitems"];
                $plsRType = $plSource["rtype"];
                $plsYears = $plSource["years"];


  
                  $financialSummaryData = array();
                  $financialSummary = $this->DownloadFSummary($borrowerid);
                  $fsLineItems = $financialSummary["lineitems"];
                  $fsRType = $financialSummary["rtype"];
                  $fsYears = $financialSummary["years"];
  
                  $bsAnalysisData = array();
                  $bsAnalysis = $this->DownloadBSAnalysis($borrowerid);
                  $bsLineItems = $bsAnalysis["lineitems"];
                  $bsRType = $bsAnalysis["rtype"];
                  $bsYears = $bsAnalysis["years"];
  
                  $plAnalysisData = array();
                  $plAnalysis = $this->DownloadPLAnalysis($borrowerid);
                  $plLineItems = $plAnalysis["lineitems"];
                  $plRType = $plAnalysis["rtype"];
                  $plYears = $plAnalysis["years"];
  
                  $cfAnalysisData = array();
                  $cfAnalysis = $this->DownloadCFAnalysis($borrowerid);
                  $cfLineItems = $cfAnalysis["lineitems"];
                  $cfRType = $cfAnalysis["rtype"];
                  $cfYears = $cfAnalysis["years"];
  
                  $writer = new XLSXWriter();

                  if(count($bssLineItems) > 0)
                {
                    for($i = 0; $i < count($bssLineItems); $i++)
                    {
                        $bssItem = $bssLineItems[$i];
                        $balancesheetSourceData[$i][] = $bssItem["label"];

                        if($bssItem["values"] != null && count($bssItem["values"]) > 0)
                        {
                            foreach($bssItem["values"] as $bssValue)
                            {
                                $balancesheetSourceData[$i][] = $bssValue["value"];
                            }
                        }
                    }

                    $writer->writeSheetRow('Balance Sheet', $bssRType);
                    $writer->writeSheetRow('Balance Sheet', $bssYears);

                    foreach($balancesheetSourceData as $row)
                    {    
                        $writer->writeSheetRow('Balance Sheet', $row);
                    }
                   
                }

                if(count($plsLineItems) > 0)
                {
                    for($i = 0; $i < count($plsLineItems); $i++)
                    {
                        $plsItem = $plsLineItems[$i];
                        $profitlossSourceData[$i][] = $plsItem["label"];

                        if($plsItem["values"] != null && count($plsItem["values"]) > 0)
                        {
                            foreach($plsItem["values"] as $plsValue)
                            {
                                $profitlossSourceData[$i][] = $plsValue["value"];
                            }
                        }
                    }

                    $writer->writeSheetRow('Profit & Loss', $plsRType);
                    $writer->writeSheetRow('Profit & Loss', $plsYears);

                    foreach($profitlossSourceData as $row)
                    {    
                        $writer->writeSheetRow('Profit & Loss', $row);
                    }
                   
                }



  
                  if(count($fsLineItems) > 0)
                  {
                      for($i = 0; $i < count($fsLineItems); $i++)
                      {
                          $fsItem = $fsLineItems[$i];
                          $financialSummaryData[$i][] = $fsItem["label"];
  
                          if($fsItem["values"] != null && count($fsItem["values"]) > 0)
                          {
                              foreach($fsItem["values"] as $fsValue)
                              {
                                  $financialSummaryData[$i][] = $fsValue["value"];
                              }
                          }
                      }
  
                      $writer->writeSheetRow('Financial Summary', $fsRType);
                      $writer->writeSheetRow('Financial Summary', $fsYears);
  
                      foreach($financialSummaryData as $row)
                      {    
                          $writer->writeSheetRow('Financial Summary', $row);
                      }
                     
                  }
  
                  if(count($bsLineItems) > 0)
                  {
                      for($i = 0; $i < count($bsLineItems); $i++)
                      {
                          $bsItem = $bsLineItems[$i];
                          $bsAnalysisData[$i][] = $bsItem["label"];
  
                          if($bsItem["values"] != null && count($bsItem["values"]) > 0)
                          {
                              foreach($bsItem["values"] as $bsValue)
                              {
                                  $bsAnalysisData[$i][] = $bsValue["value"];
                              }
                          }
                      }
  
                      $writer->writeSheetRow('Balance Sheet Analysis', $fsRType);
                      $writer->writeSheetRow('Balance Sheet Analysis', $fsYears);
  
                      foreach($bsAnalysisData as $row)
                      {
                          $writer->writeSheetRow('Balance Sheet Analysis', $row);
                      }  
                  }
  
                  if(count($plLineItems) > 0)
                  {
                      for($i = 0; $i < count($plLineItems); $i++)
                      {
                          $plItem = $plLineItems[$i];
                          $plAnalysisData[$i][] = $plItem["label"];
  
                          if($plItem["values"] != null && count($plItem["values"]) > 0)
                          {
                              foreach($plItem["values"] as $plValue)
                              {
                                  $plAnalysisData[$i][] = $plValue["value"];
                              }
                          }
                      }
  
                      $writer->writeSheetRow('Profit & Loss Analysis', $plRType);
                      $writer->writeSheetRow('Profit & Loss Analysis', $plYears);
  
                      foreach($plAnalysisData as $row)
                      {
                          $writer->writeSheetRow('Profit & Loss Analysis', $row);
                      }  
                  }
  
  
                  if(count($cfLineItems) > 0)
                  {
                      for($i = 0; $i < count($cfLineItems); $i++)
                      {
                          $cfItem = $cfLineItems[$i];
                          $cfAnalysisData[$i][] = $cfItem["label"];
  
                          if($cfItem["values"] != null && count($cfItem["values"]) > 0)
                          {
                              foreach($cfItem["values"] as $cfValue)
                              {
                                  $cfAnalysisData[$i][] = $cfValue["value"];
                              }
                          }
                      }
  
                      $writer->writeSheetRow('Cash FLow Analysis', $cfRType);
                      $writer->writeSheetRow('Cash FLow Analysis', $cfYears);
  
                      foreach($cfAnalysisData as $row)
                      {
                          $writer->writeSheetRow('Cash FLow Analysis', $row);
                      }  
                  }

                //    code by parthiban 
                
                $sql = "SELECT company_name FROM `fp_borrower_user_details` WHERE user_id=".$borrowerid;

                $company_name = $this->db->query($sql)->result();
                
                // End of code by parthiban 

  
                  $fileName = "downloads/financials/".$company_name[0]->company_name.".xlsx"; 
                //   $fileName = "downloads/financials/Borrower".$borrowerid.".xlsx"; 
                  $writer->writeToFile($fileName);
  
                  $response_data = array(
                      'status' => 200,
                      'success' => true,
                      'message' => 'Request received successfully',
                      'downloadurl' => $downloadUrl.$fileName 
                  );
                 
                  echo json_encode($response_data);
                  exit();
  
              }
              else
              {
                  return json_output(200,array('status' => 400,'Message' => "Invalid Authentication"));
              }
          }
  
          
  
      }   // END OF DOWNLOADFINANCIALS
      
         public function DownloadFinancialsOld(){
          
          $method = $_SERVER['REQUEST_METHOD'];
          if($method != 'POST')
          {
              json_output(400,array('status' => 400,'message' => 'Bad request.'));
          }
          else
          {
              $downloadUrl = "http://localhost/fin/api/index.php/XLRT/";
              $data = null;
              $check_auth_user = $this->login->check_auth_user();
  
              if($check_auth_user == true)
              {
                  $params = json_decode(file_get_contents('php://input'), TRUE);
                  $borrowerid = $params['data']['id'];
                  $fintype = $params['fintype'];
  
                  $unit = "lakh";
  
                  if($fintype == "bsanalysis")
                  {
                      $downloadUrl.="DownloadBSAnalysis";
                  }
                  else if($fintype == "financialsummary")
                  {
                      $downloadUrl.="DownloadFSummary";
                  }
                  else if($fintype == "planalysis")
                  {
                      $downloadUrl.="DownloadPLAnalysis";
                  }
                  else if($fintype == "cfanalysis")
                  {
                      $downloadUrl.="DownloadCFAnalysis";
                  }
                  $downloadUrl.="?borrowerid=".$borrowerid;
                 
  
                   $response_data = array(
                      'status' => 200,
                      'success' => true,
                      'message' => 'Request received successfully',
                      'downloadurl' => $downloadUrl
                  );
                 
                  echo json_encode($response_data);
                  exit();
              }
              else
              {
                  return json_output(200,array('status' => 400,'Message' => "Invalid Authentication"));
              }
          }
  
      }    // END OF DOWNLOADFINANCIALSOLD
  
       public  function DownloadFSummary($borrowerid){
          
          if($borrowerid  > 0)
          {
              $borrowerid = $borrowerid;
              $unit = "lakh";
  
              $response['status']=200;
              $respStatus = $response['status'];
  
              
              $bsAnalysis = array("periods" => null, "lineitems"=> null);
              
  
              $sql = " SELECT year, period_type, result_type FROM `fp_borrower_financials_summary` where borrower_id = '$borrowerid' and period_type='annual' order by year asc ";
              $periodResults = $this->db->query($sql)->result();
              $num_results =sizeof($periodResults);
  
              if($num_results > 0)
              {
                  $fsPeriods = null;
                  $fsPeriodRType[] = "";
                  $fsPeriodYear[] = "";
  
                  foreach($periodResults as $periodItem)
                  {
                      $fsPeriods[] = array("rtype" => $periodItem->result_type, "ptype" => $periodItem->period_type, "year" => $periodItem->year, "key" => $periodItem->year."-".$periodItem->period_type);
                      $fsPeriodRType[] = $periodItem->result_type;
                      $fsPeriodYear[] = $periodItem->year;
                  }
  
                  $sql = "SELECT * from fp_borrower_financials_summary where borrower_id = '$borrowerid'  and period_type='annual' " ;
                  $fsDbData = $this->db->query($sql)->result();
                  $num_results =sizeof($fsDbData);
  
                  $results = GetFinancialSummaryFromDB($fsPeriods, $fsDbData, $unit);
  
                  $financialSummary = array("periods" => $fsPeriods, "lineitems"=> $results, "rtype" => $fsPeriodRType, "years" => $fsPeriodYear);
                  $data = array("financials"=> $financialSummary, "ftype" => "Financial Summary");
  
                  //$this->load->view('downloadfinancials', $data);
  
                  return $financialSummary; 
              }
  
              
          }
  
          return null;
      }  // END OF DOWNLOADFSUMMARY
  
     public  function DownloadBSAnalysis($borrowerid){
          
          if($borrowerid > 0)
          {
              $unit = "lakh";
  
              $response['status']=200;
              $respStatus = $response['status'];
  
              
              $bsAnalysis = array("periods" => null, "lineitems"=> null);
              
  
              $sql = " SELECT year, period_type, result_type FROM `fp_borrower_financials_bsanalysis` where borrower_id = '$borrowerid' and period_type='annual' order by year asc ";
              $periodResults = $this->db->query($sql)->result();
              $num_results =sizeof($periodResults);
  
              if($num_results > 0)
              {
                  $bsPeriods = null;
                  $bsPeriodRType[] = "";
                  $bsPeriodYear[] = "";
  
                  foreach($periodResults as $periodItem)
                  {
                      $bsPeriods[] = array("rtype" => $periodItem->result_type, "ptype" => $periodItem->period_type, "year" => $periodItem->year, "key" => $periodItem->year."-".$periodItem->period_type);
                      $bsPeriodRType[] = $periodItem->result_type;
                      $bsPeriodYear[] = $periodItem->year;
                  }
  
                  $sql = "SELECT * from fp_borrower_financials_bsanalysis where borrower_id = '$borrowerid' and period_type='annual' " ;
                  $bsDbData = $this->db->query($sql)->result();
                  $num_results =sizeof($bsDbData);
  
                  $results = GetBalanceSheetAnalysisFromDB($bsPeriods, $bsDbData,$unit);
  
                  $bsAnalysis = array("periods" => $bsPeriods, "lineitems"=> $results, "rtype" => $bsPeriodRType, "years" => $bsPeriodYear);
                  
                  $data = array("financials"=> $bsAnalysis, "ftype" => "Balance Sheet Analysis");
  
                  //$this->load->view('downloadfinancials', $data);
                  return $bsAnalysis;
              }
          }
  
          return null;
      }   //  END OF DOWNLOADBSANALYSIS
  
     public  function DownloadPLAnalysis($borrowerid){
          
          if($borrowerid > 0)
          {
              $unit = "lakh";
  
              $response['status']=200;
              $respStatus = $response['status'];
  
              
              $bsAnalysis = array("periods" => null, "lineitems"=> null);
              
  
              $sql = " SELECT year, period_type, result_type FROM `fp_borrower_financials_planalysis` where borrower_id = '$borrowerid' and period_type='annual' order by year asc ";
              $periodResults = $this->db->query($sql)->result();
              $num_results =sizeof($periodResults);
  
              if($num_results > 0)
              {
                  $plPeriods = null;
                  $plPeriodRType[] = "";
                  $plPeriodYear[] = "";
  
                  foreach($periodResults as $periodItem)
                  {
                      $plPeriods[] = array("rtype" => $periodItem->result_type, "ptype" => $periodItem->period_type, "year" => $periodItem->year, "key" => $periodItem->year."-".$periodItem->period_type);
                      $plPeriodRType[] = $periodItem->result_type;
                      $plPeriodYear[] = $periodItem->year;
                  }
  
                  $sql = "SELECT * from fp_borrower_financials_planalysis where borrower_id = '$borrowerid' and period_type='annual' " ;
                  $plDbData = $this->db->query($sql)->result();
                  $num_results =sizeof($plDbData);
  
                  $results = GetProfitAndLossAnalysisFromDB($plPeriods, $plDbData, $unit);
  
                  $plAnalysis = array("periods" => $plPeriods, "lineitems"=> $results, "rtype"=> $plPeriodRType, "years"=> $plPeriodYear);
  
                  $data = array("financials"=> $plAnalysis, "ftype" => "Profit & Loss Analysis");
  
                  //$this->load->view('downloadfinancials', $data);
  
                  return $plAnalysis;
              }
  
          }
  
          return null;
      }    //  END OF  DOWNLOADPLANALYSIS
  
      public  function DownloadCFAnalysis($borrowerid){
          
          if($borrowerid > 0)
          {
              $unit = "lakh";
  
              $response['status']=200;
              $respStatus = $response['status'];
  
              
              $bsAnalysis = array("periods" => null, "lineitems"=> null);
              
  
              $sql = " SELECT year, period_type, result_type FROM `fp_borrower_financials_cfanalysis` where borrower_id = '$borrowerid' and period_type='annual' order by year asc ";
                  $periodResults = $this->db->query($sql)->result();
                  $num_results =sizeof($periodResults);
  
                  if($num_results > 0)
                  {
                      $cfPeriods = null;
                      $cfPeriodRType[] = "";
                      $cfPeriodYear[] = "";
  
                      foreach($periodResults as $periodItem)
                      {
                          $cfPeriods[] = array("rtype" => $periodItem->result_type, "ptype" => $periodItem->period_type, "year" => $periodItem->year, "key" => $periodItem->year."-".$periodItem->period_type);
                          $cfPeriodRType[] = $periodItem->result_type;
                          $cfPeriodYear[] = $periodItem->year;
                      }
  
                      $sql = "SELECT * from fp_borrower_financials_cfanalysis where borrower_id = '$borrowerid' and period_type='annual' " ;
                      $cfDbData = $this->db->query($sql)->result();
                      $num_results =sizeof($cfDbData);
  
                      $results = GetCashFlowAnalysisFromDB($cfPeriods, $cfDbData, $unit);
  
                      $cfAnalysis = array("periods" => $cfPeriods, "lineitems"=> $results, "rtype"=> $cfPeriodRType, "years"=> $cfPeriodYear);
  
                      $data = array("financials"=> $cfAnalysis, "ftype" => "Cash Flow Analysis");
  
                      //$this->load->view('downloadfinancials', $data);
  
                      return $cfAnalysis;
                  }
  
          }
  
          return null;
      }    // END OF  DOWNLOADCFANALYSIS


     public  function DownloadBSSource($borrowerid){
        
        if($borrowerid > 0)
        {
            $unit = "lakh";

            $response['status']=200;
            $respStatus = $response['status'];

            
            $bsAnalysis = array("periods" => null, "lineitems"=> null);
            

            $sql = " SELECT year, period_type, result_type FROM `fp_borrower_financials_bssource` where borrower_id = '$borrowerid' order by year desc ";
                $periodResults = $this->db->query($sql)->result();
				$num_results =sizeof($periodResults);

                if($num_results > 0)
                {
                    $bssPeriods = null;
                    $bssPeriodRType[] = "";
                    $bssPeriodYear[] = "";

                    foreach($periodResults as $periodItem)
                    {
                        $bssPeriods[] = array("rtype" => $periodItem->result_type, "ptype" => $periodItem->period_type, "year" => $periodItem->year, "key" => $periodItem->year."-".$periodItem->period_type);
                        $bssPeriodRType[] = $periodItem->result_type;
                        $bssPeriodYear[] = $periodItem->year;
                    }

                    $sql = "SELECT * from fp_borrower_financials_bssource where borrower_id = '$borrowerid' " ;
                    $bssDbData = $this->db->query($sql)->result();
                    $num_results =sizeof($bssDbData);

                    $results = GetBSSourceFromDB($bssPeriods, $bssDbData, $unit);

                    $bsSource = array("periods" => $bssPeriods, "lineitems"=> $results, "rtype"=> $bssPeriodRType, "years"=> $bssPeriodYear);

                    $data = array("financials"=> $bsSource, "ftype" => "Balance Sheet");

                    //$this->load->view('downloadfinancials', $data);

                    return $bsSource;
                }

        }

        return null;
    }

    public function DownloadPLSource($borrowerid){
        
        if($borrowerid > 0)
        {
            $unit = "lakh";

            $response['status']=200;
            $respStatus = $response['status'];

            
            $plSource = array("periods" => null, "lineitems"=> null);
            

            $sql = " SELECT year, period_type, result_type FROM `fp_borrower_financials_plsource` where borrower_id = '$borrowerid' order by year desc ";
                $periodResults = $this->db->query($sql)->result();
				$num_results =sizeof($periodResults);

                if($num_results > 0)
                {
                    $plsPeriods = null;
                    $plsPeriodRType[] = "";
                    $plsPeriodYear[] = "";

                    foreach($periodResults as $periodItem)
                    {
                        $plsPeriods[] = array("rtype" => $periodItem->result_type, "ptype" => $periodItem->period_type, "year" => $periodItem->year, "key" => $periodItem->year."-".$periodItem->period_type);
                        $plsPeriodRType[] = $periodItem->result_type;
                        $plsPeriodYear[] = $periodItem->year;
                    }

                    $sql = "SELECT * from fp_borrower_financials_plsource where borrower_id = '$borrowerid' " ;
                    $plsDbData = $this->db->query($sql)->result();
                    $num_results =sizeof($plsDbData);

                    $results = GetPLSourceFromDB($plsPeriods, $plsDbData, $unit);

                    $plSource = array("periods" => $plsPeriods, "lineitems"=> $results, "rtype"=> $plsPeriodRType, "years"=> $plsPeriodYear);

                    $data = array("financials"=> $plSource, "ftype" => "Profit & Loss");

                    //$this->load->view('downloadfinancials', $data);

                    return $plSource;
                }

        }

        return null;
    }
              
  public  function GetBalanceSheetData(){
          $method = $_SERVER['REQUEST_METHOD'];
          $method = $_SERVER['REQUEST_METHOD'];
          if($method != 'POST')
          {
              json_output(400,array('status' => 400,'message' => 'Bad request.'));
          }
          else
          {
              $data = null;
              $check_auth_user = $this->login->check_auth_user();
  
              if($check_auth_user == true)
              {
                  $params = json_decode(file_get_contents('php://input'), TRUE);
                  $borrowerid = $params['data']['id'];
  
                  $unit =$params['unit'] ;
  
                  $response['status']=200;
                  $respStatus = $response['status'];
  
                  $financialSummary = array("periods" => null, "lineitems"=> null);
                  $bsAnalysis = array("periods" => null, "lineitems"=> null);
                  $plAnalysis = array("periods" => null, "lineitems"=> null);
                  $cfAnalysis = array("periods" => null, "lineitems"=> null);
                  
  
                  $sql = " SELECT year, period_type, result_type FROM `fp_borrower_financials_summary` where borrower_id = '$borrowerid' and period_type='annual' order by year asc ";
                  
                  $periodResults = $this->db->query($sql)->result();
                  $num_results =sizeof($periodResults);
  
                  if($num_results > 0)
                  {
                      $fsPeriods = null;
  
                      foreach($periodResults as $periodItem)
                      {
                          $fsPeriods[] = array("ptype" => $periodItem->period_type, "year" => $periodItem->year, "key" => $periodItem->year."-".$periodItem->period_type);
                      }
  
                      $sql = "SELECT * from fp_borrower_financials_summary where borrower_id = '$borrowerid' and period_type='annual' " ;
                      $fsDbData = $this->db->query($sql)->result();
  
                      
  
                      $num_results = sizeof($fsDbData);
  
                      $results = GetFinancialSummaryFromDB($fsPeriods, $fsDbData, $unit);
  
                      
  
                      $financialSummary = array("periods" => $fsPeriods, "lineitems"=> $results);
                  }
  
                  $sql = " SELECT year, period_type, result_type FROM `fp_borrower_financials_bsanalysis` where borrower_id = '$borrowerid' and period_type='annual' order by year asc ";
                  $periodResults = $this->db->query($sql)->result();
                  $num_results =sizeof($periodResults);
  
                  if($num_results > 0)
                  {
                      $bsPeriods = null;
  
                      foreach($periodResults as $periodItem)
                      {
                          $bsPeriods[] = array("ptype" => $periodItem->period_type, "year" => $periodItem->year, "key" => $periodItem->year."-".$periodItem->period_type);
                      }
  
                      $sql = "SELECT * from fp_borrower_financials_bsanalysis where borrower_id = '$borrowerid'  and period_type='annual' " ;
                      $bsDbData = $this->db->query($sql)->result();
                      $num_results =sizeof($bsDbData);
  
                      $results = GetBalanceSheetAnalysisFromDB($bsPeriods, $bsDbData,$unit);
  
                      $bsAnalysis = array("periods" => $bsPeriods, "lineitems"=> $results);
                  }
  
  
                  $sql = " SELECT year, period_type, result_type FROM `fp_borrower_financials_planalysis` where borrower_id = '$borrowerid' and period_type='annual' order by year asc ";
                  $periodResults = $this->db->query($sql)->result();
                  $num_results =sizeof($periodResults);
  
                  if($num_results > 0)
                  {
                      $plPeriods = null;
  
                      foreach($periodResults as $periodItem)
                      {
                          $plPeriods[] = array("ptype" => $periodItem->period_type, "year" => $periodItem->year, "key" => $periodItem->year."-".$periodItem->period_type);
                      }
  
                      $sql = "SELECT * from fp_borrower_financials_planalysis where borrower_id = '$borrowerid' and period_type='annual' " ;
                      $plDbData = $this->db->query($sql)->result();
                      $num_results =sizeof($plDbData);
  
  
                      $results = GetProfitAndLossAnalysisFromDB($plPeriods, $plDbData, $unit);
  
  
                      $plAnalysis = array("periods" => $plPeriods, "lineitems"=> $results);
                  }
  
  
                  $sql = " SELECT year, period_type, result_type FROM `fp_borrower_financials_cfanalysis` where borrower_id = '$borrowerid' and period_type='annual' order by year asc ";
                  $periodResults = $this->db->query($sql)->result();
                  $num_results =sizeof($periodResults);
  
                  if($num_results > 0)
                  {
                      $cfPeriods = null;
  
                      foreach($periodResults as $periodItem)
                      {
                          $cfPeriods[] = array("ptype" => $periodItem->period_type, "year" => $periodItem->year, "key" => $periodItem->year."-".$periodItem->period_type);
                      }
  
                      $sql = "SELECT * from fp_borrower_financials_cfanalysis where borrower_id = '$borrowerid' and period_type='annual' " ;
                      $cfDbData = $this->db->query($sql)->result();
                      $num_results =sizeof($cfDbData);
  
                      $results = GetCashFlowAnalysisFromDB($cfPeriods, $cfDbData, $unit);
  
                      $cfAnalysis = array("periods" => $cfPeriods, "lineitems"=> $results);
                  }
                  return json_output(200,array('status' => 200,'data' => array(
                      "financialsummary"=> $financialSummary,
                      "bsanalysis"=> $bsAnalysis,
                      "planalysis"=> $plAnalysis,
                      "cfanalysis"=> $cfAnalysis
                      ) ));
              }
              else
              {
                  return json_output(200,array('status' => 400,'Message' => "Invalid Authentication"));
              }
          }
      }  //  END OF GETBALANCESHEETDATA
  
      public  function DownloadSample(){
          $writer = new XLSXWriter();
          $keywords = array('some','interesting','keywords');
  
          $writer->setTitle('Some Title');
          $writer->setSubject('Some Subject');
          $writer->setAuthor('Some Author');
          $writer->setCompany('Some Company');
          $writer->setKeywords($keywords);
          $writer->setDescription('Some interesting description');
          $writer->setTempDir(sys_get_temp_dir());//set custom tempdir
  
          //----
          $sheet1 = 'merged_cells';
          $header = array("string","string","string","string","string");
          $rows = array(
              array("Merge Cells Example"),
              array(100, 200, 300, 400, 500),
              array(110, 210, 310, 410, 510),
          );
          $writer->writeSheetHeader($sheet1, $header, $col_options = ['suppress_row'=>true] );
          foreach($rows as $row)
              $writer->writeSheetRow($sheet1, $row);
          $writer->markMergedCell($sheet1, $start_row=0, $start_col=0, $end_row=0, $end_col=4);
  
          //----
          $sheet2 = 'utf8';
          $rows = array(
              array('Spreadsheet','_'),
              array("Hoja de cálculo", "Hoja de c\xc3\xa1lculo"),
              array("Електронна таблица", "\xd0\x95\xd0\xbb\xd0\xb5\xd0\xba\xd1\x82\xd1\x80\xd0\xbe\xd0\xbd\xd0\xbd\xd0\xb0 \xd1\x82\xd0\xb0\xd0\xb1\xd0\xbb\xd0\xb8\xd1\x86\xd0\xb0"),//utf8 encoded
              array("電子試算表", "\xe9\x9b\xbb\xe5\xad\x90\xe8\xa9\xa6\xe7\xae\x97\xe8\xa1\xa8"),//utf8 encoded
          );
          $writer->writeSheet($rows, $sheet2);
  
          //----
          $sheet3 = 'fonts';
          $format = array('font'=>'Arial','font-size'=>10,'font-style'=>'bold,italic', 'fill'=>'#eee','color'=>'#f00','fill'=>'#ffc', 'border'=>'top,bottom', 'halign'=>'center');
          $writer->writeSheetRow($sheet3, $row=array(101,102,103,104,105,106,107,108,109,110), $format);
          $writer->writeSheetRow($sheet3, $row=array(201,202,203,204,205,206,207,208,209,210), $format);
  
  
          //----
          $sheet4 = 'row_options';
          $writer->writeSheetHeader($sheet4, ["col1"=>"string", "col2"=>"string"], $col_options = array('widths'=>[10,10]) );
          $writer->writeSheetRow($sheet4, array(101,'this text will wrap'    ), $row_options = array('height'=>30,'wrap_text'=>true));
          $writer->writeSheetRow($sheet4, array(201,'this text is hidden'    ), $row_options = array('height'=>30,'hidden'=>true));
          $writer->writeSheetRow($sheet4, array(301,'this text will not wrap'), $row_options = array('height'=>30,'collapsed'=>true));
          $writer->writeToFile('xlsx-advanced.xlsx');
      }   // END OF DOWNLOADSAMPLE
      
     public  function AnalyzeData($borrowerid,$xlrt_response)
    {           
                $financejson 	= $xlrt_response;
                $financialstype = "STA";

                $data["balancesheetdata"] = GetBalanceSheetData($financejson, $financialstype);
                $bsSourceLineItems = $data["balancesheetdata"]["lineitems"];

                $dataPeriods = $data["balancesheetdata"]["periods"];


                //$data["profitandloss"] = GetProfitAndLoss($financejson, $financialstype);

                $data["profitandloss"] = GetProfitAndLossNew($financejson, $financialstype, $dataPeriods);
                $plSourceLineItems = $data["profitandloss"];
                
                $data["balancesheetL"] = GetBalanceSheetLiabilities($financejson, $financialstype);
                $data["balancesheetA"] = GetBalanceSheetAssets($financejson, $financialstype);


                

                $planalysis = GetProfitAndLossAnalysisNew($data["profitandloss"], $data["balancesheetdata"]["periods"]);
                //$planalysisnew = GetProfitAndLossAnalysisNew($data["profitandloss"], $data["balancesheetdata"]["periods"]);
                $bsanalysis = GetBalanceSheetAnalysis($data["balancesheetdata"]["lineitems"], $data["balancesheetdata"]["periods"]);
                $financialsummary = GetFinancialSummary($data["balancesheetdata"]["lineitems"], $data["profitandloss"], $data["balancesheetdata"]["periods"]);
                $cfAnalysis = GetCashFlowAnalysis($data["balancesheetdata"]["lineitems"], $data["profitandloss"], $data["balancesheetdata"]["periods"], $bsanalysis);

                
                //$data["planalysisnew"] = array("periods" => $data["balancesheetdata"]["periods"], "lineitems" => $planalysisnew);
                $data["planalysis"] = array("periods" => $data["balancesheetdata"]["periods"], "lineitems" => $planalysis);
                $data["bsanalysis"] = array("periods" => $data["balancesheetdata"]["periods"], "lineitems" => $bsanalysis);
                $data["financialsummary"] = array("periods" => $data["balancesheetdata"]["periods"], "lineitems" => $financialsummary);
                $data["cfanalysis"] = array("periods" => $data["balancesheetdata"]["periods"], "lineitems" => $cfAnalysis);

                $periodYears = array();

                for($i = 0 ; $i < count($dataPeriods); $i++)
			    {
                    $currentDataPeriod = $dataPeriods[$i];

                    $period_type = $currentDataPeriod["ptype"];
                    $pmonth = "0";
                    $pyear = $currentDataPeriod["year"];

                    $periodYears[] = $pyear;
                }

                $periodYears = implode(",", $periodYears);


                $bsaInsertSql = " INSERT INTO `fp_borrower_financials_bsanalysis` (`borrower_id`, `period_type`, `month`, `year`, `result_type`, `equity_share_capital`, `reserve_and_surplus`, `total_equity`, `long_term_borrowings`, `deferred_tax_liability`, `other_liabilities`, `total_non_current_liabilities`, `short_term_borrowings`, `trade_payables`, `other_current_liabilities`, `total_current_liabilities`, `total_equity_and_liabilities`, `property_plant_equipments`, `intangible_assets`, `non_current_assets`, `total_non_current_assets`, `inventories`, `current_investments`, `trade_receivables`, `cash_bank_balance`, `other_current_assets`, `total_assets`, `total_current_assets`) ";
                $bsaInsertSql.= " VALUES ";
                
                
                // For Balance Sheet Analysis
                for($i = 0 ; $i < count($dataPeriods); $i++)
			    {
                    $currentDataPeriod = $dataPeriods[$i];

                    $period_type = $currentDataPeriod["ptype"];
                    $pmonth = "0";
                    $pyear = $currentDataPeriod["year"];
                    $result_type = "";
                    
                    $equity_share_capital = 0;
                    $reserve_and_surplus = 0;
                    $total_equity = 0;
                    $long_term_borrowings = 0;
                    $deferred_tax_liability = 0;
                    $other_liabilities = 0;
                    $total_non_current_liabilities = 0;
                    $short_term_borrowings = 0;
                    $trade_payables = 0;
                    $other_current_liabilities = 0;
                    $total_current_liabilities = 0;
                    $total_equity_and_liabilities = 0;

                    $property_plant_equipments = 0;
                    $intangible_assets = 0;
                    $non_current_assets = 0;
                    $total_non_current_assets = 0;
                    $inventories = 0;
                    $current_investments = 0;
                    $trade_receivables = 0;
                    $cash_bank_balance = 0;
                    $other_current_assets = 0;
                    $total_current_assets = 0;
                    $total_assets = 0;

                    foreach($bsanalysis as $lineitem)
                    {
                        if($lineitem["values"] != null && count($lineitem["values"]) > 0)
                        {
                            foreach($lineitem["values"] as $valueItem)
                            {
                                if($valueItem["key"] == $currentDataPeriod["key"])
                                {
                                    switch($lineitem["label"])
                                    {
                                        case "Equity Share Capital":
                                            $equity_share_capital = $valueItem["value"];
                                            break;
                                        case "Reserves and Surplus":
                                            $reserve_and_surplus = $valueItem["value"];
                                            break;
                                        case "Total Equity":
                                            $total_equity = $valueItem["value"];
                                            break;
                                        case "Long Term Borrowings":
                                            $long_term_borrowings = $valueItem["value"];
                                            break;
                                        case "Deferred tax liabilities":
                                            $deferred_tax_liability = $valueItem["value"];
                                            break;
                                        case "Other liabilities":
                                            $other_liabilities = $valueItem["value"];
                                            break;
                                        case "Total non current liabilities":
                                            $total_non_current_liabilities = $valueItem["value"];
                                            break;
                                        case "Short term Borrowings":
                                            $short_term_borrowings = $valueItem["value"];
                                            break;
                                        case "Trade payables":
                                            $trade_payables = $valueItem["value"];
                                            break;
                                        case "Other Current Liabilities":
                                            $other_current_liabilities = $valueItem["value"];
                                            break;
                                        case "Total Current Liabilities":
                                            $total_current_liabilities = $valueItem["value"];
                                            break;
                                        case "Total Equity and liabilities":
                                            $total_equity_and_liabilities = $valueItem["value"];
                                            break;
                                        case "Property, Plant & Equipments":
                                            $property_plant_equipments = $valueItem["value"];
                                            break;
                                        case "Intangible assets":
                                            $intangible_assets = $valueItem["value"];
                                        break;
                                        case "Non current assets":
                                            $non_current_assets = $valueItem["value"];
                                            break;
                                        case "Total Non current assets":
                                            $total_non_current_assets = $valueItem["value"];
                                            break;
                                        case "Inventories":
                                            $inventories = $valueItem["value"];
                                            break;
                                        case "Current Investments":
                                            $current_investments = $valueItem["value"];
                                        break;
                                        case "Trade Receivables":
                                            $trade_receivables = $valueItem["value"];
                                            break;
                                        case "Cash & Bank Balances":
                                            $cash_bank_balance = $valueItem["value"];
                                            break;
                                        case "Other  current assets":
                                            $other_current_assets = $valueItem["value"];
                                            break;
                                        case "Total current assets":
                                            $total_current_assets = $valueItem["value"];
                                            break;
                                        case "Total assets":
                                            $total_assets = $valueItem["value"];
                                            break;
                                    }
                                    
                                }
                            }
                        }
                        

                    }

                    $bsaInsertSql.= " ('$borrowerid', '$period_type', '$pmonth', '$pyear', 'audited', '$equity_share_capital', '$reserve_and_surplus', '$total_equity', ";
                    $bsaInsertSql.= " '$long_term_borrowings', '$deferred_tax_liability', '$other_liabilities', '$total_non_current_liabilities', ";
                    $bsaInsertSql.= " '$short_term_borrowings', '$trade_payables', '$other_current_liabilities', ";
                    $bsaInsertSql.= " '$total_current_liabilities', '$total_equity_and_liabilities', '$property_plant_equipments', '$intangible_assets', ";
                    $bsaInsertSql.= " '$non_current_assets', '$total_non_current_assets', ";
                    $bsaInsertSql.= " '$inventories', '$current_investments', '$trade_receivables', '$cash_bank_balance', '$other_current_assets', '$total_assets', '$total_current_assets') ";

                    if($i < count($dataPeriods) - 1)
                    {
                        $bsaInsertSql.= " , ";    
                    }
                    
                }

                

                $this->db->query("Delete from `fp_borrower_financials_bsanalysis` where borrower_id = '$borrowerid' and year in ($periodYears) ");
                $data["bsaInsertSql"] = $bsaInsertSql;
                $this->db->query($bsaInsertSql);

                // For Profit & Loss Analysis

                $plaInsertSql = " INSERT INTO `fp_borrower_financials_planalysis` (`borrower_id`, `period_type`, `month`, `year`, `result_type`, `revenue_from_operations`, `cost_of_material_purchased`, `depriciation_and_amortisation_expense`, `changes_in_inventories`, `employee_benefits_expenses`, `finance_cost`, `other_income`, `total_income`, `other_expenses`, `total_expenses`, `profit_before_tax`, `current_tax`, `deferred_tax`, `profit_after_tax`) ";
                $plaInsertSql.= " VALUES ";
                for($i = 0 ; $i < count($dataPeriods); $i++)
			    {
                    $currentDataPeriod = $dataPeriods[$i];

                    $period_type = $currentDataPeriod["ptype"];
                    $pmonth = "0";
                    $pyear = $currentDataPeriod["year"];
                    $result_type = "";
                    
                    $revenueFromOperations = 0;
                    $costOfMaterialPurchased = 0;
                    $depreciationAndAmortisationExpense = 0 ;
                    $changesInInventories = 0 ;
                    $employeeBenefitsExpense = 0;
                    $financeCosts = 0;
                    $otherIncome = 0;
                    $totalIncome = 0;
                    $otherExpenses = 0;
                    $totalExpenses = 0;
                    $profitBeforeTax = 0;
                    $currentTax = 0;
                    $deferredTax = 0;
                    $profitAfterTax = 0;
                    

                    foreach($planalysis as $lineitem)
                    {
                        if($lineitem["values"] != null && count($lineitem["values"]) > 0)
                        {
                            foreach($lineitem["values"] as $valueItem)
                            {
                                if($valueItem["key"] == $currentDataPeriod["key"])
                                {
                                    switch($lineitem["label"])
                                    {
                                        case "Revenue from Operations":
                                            $revenueFromOperations = $valueItem["value"];
                                            break;
                                        case "Cost of Material Purchased":
                                            $costOfMaterialPurchased = $valueItem["value"];
                                            break;
                                        case "Depreciation and amortisation expense":
                                            $depreciationAndAmortisationExpense = $valueItem["value"];
                                            break;
                                        case "Changes in inventories of finished and semi-finished goods, stock in trade and work in progress":
                                            $changesInInventories = $valueItem["value"];
                                            break;                                            
                                        case "Employee Benefits Expense":
                                            $employeeBenefitsExpense = $valueItem["value"];
                                            break;
                                        case "Finance Costs":
                                            $financeCosts = $valueItem["value"];
                                            break;
                                        case "Other Income":
                                            $otherIncome = $valueItem["value"];
                                            break;
                                        case "Total Income":
                                            $totalIncome = $valueItem["value"];
                                            break;
                                        case "Other Expenses":
                                            $otherExpenses = $valueItem["value"];
                                            break;
                                        case "Total Expenses":
                                            $totalExpenses = $valueItem["value"];
                                            break;
                                        case "Profit before Tax":
                                            $profitBeforeTax = $valueItem["value"];
                                            break;
                                        case "Current tax":
                                            $currentTax = $valueItem["value"];
                                            break;
                                        case "Deferred tax":
                                            $deferredTax = $valueItem["value"];
                                            break;
                                        case "Profit for the year":
                                            $profitAfterTax = $valueItem["value"];
                                            break;
                                    }
                                    
                                }
                            }
                        }
                        

                    }

                    $plaInsertSql.= " ('$borrowerid', '$period_type', '$pmonth', '$pyear', '$result_type', '$revenueFromOperations', '$costOfMaterialPurchased', ";
                    $plaInsertSql.= " '$depreciationAndAmortisationExpense', '$changesInInventories', '$employeeBenefitsExpense', '$financeCosts', '$otherIncome', '$totalIncome', ";
                    $plaInsertSql.= " '$otherExpenses', '$totalExpenses', '$profitBeforeTax', '$currentTax', '$deferredTax', '$profitAfterTax' ) ";

                    if($i < count($dataPeriods) - 1)
                    {
                        $plaInsertSql.= " , ";    
                    }
                    
                }

                $this->db->query("Delete from `fp_borrower_financials_planalysis` where borrower_id = '$borrowerid' and year in ($periodYears) ");
                $data["plaInsertSql"] = $plaInsertSql;
                $this->db->query($plaInsertSql);


                // For Financial Summary

                $fsInsertSql = " INSERT INTO `fp_borrower_financials_summary` (`borrower_id`, `period_type`, `month`, `year`, `result_type`, `net_sales`, `other_income`, `income`, `pbdita`, `pbdita_margin`, `interest`, `depriciation`, `operating_profit_after_interest`,  `income_expense`, `profit_before_tax`, `profit_after_tax`, `net_profit_margin`, `net_cash_accurals`, `fixed_assets_gross`, `fixed_assets_net`,                 `non_current_assets`, `tangible_networth`, `exposure_in_group_company`, `adjusted_tnw`, `long_term_debt`, `short_term_debt`, `working_capital_borrowing`, `total_outside_liabilities`, `ltw_tnw`, `tol_tnw`, `tol_atnw`, `total_current_assets`, `total_current_liabilities`, `net_working_capital`, `current_ratio`, `inventory_holding_period`, `debtor_holding_period`, `creditor_holding_period`, `debt_equity_ratio`, `debt_pbitda_ratio`, `interest_coverage_ratio`, `dscr`) ";
                $fsInsertSql.= " VALUES ";
                for($i = 0 ; $i < count($dataPeriods); $i++)
			    {
                    $currentDataPeriod = $dataPeriods[$i];

                    $period_type = $currentDataPeriod["ptype"];
                    $pmonth = "0";
                    $pyear = $currentDataPeriod["year"];
                    $result_type = "";
                    
                    $netSales = 0;
                    $otherIncome = 0;
                    $income = 0;
                    $pbdita = 0;
                    $pbditaMargin = 0;
                    $interest = 0;
                    $depreciation = 0;
                    $operatingProfitAfterInterest = 0;
                    $incomeExpenses = 0;
                    $profitBeforeTax = 0;
                    $profitAfterTax = 0;
                    $netProfitMargin = 0;
                    $netCashAccruals = 0;
                    $fixedAssetsGross = 0;
                    $fixedAssetsNet = 0;
                    $nonCurrentAssets = 0;
                    $tangibleNetworth = 0;
                    $exposureInGroupCo = 0;
                    $adjustedTNW = 0;
                    $longTermDebt = 0;
                    $shortTermDebt = 0;
                    $workingCapitalBorrowing = 0;
                    $totalOutsideLiabilities = 0;
                    $LTD_TNW = 0;
                    $TOL_TNW = 0;
                    $TOL_ATNW = 0;
                    $totalCurrentAssets = 0;
                    $totalCurrentLiabilities = 0;
                    $netWorkingCapital = 0;
                    $currentRatio = 0;
                    $inventoryHoldingPeriod = 0;
                    $debtorsHoldingPeriod = 0;
                    $creditorsHoldingPeriod = 0;
                    $debtEquityRatio = 0;
                    $debt_PBITDARatio = 0;
                    $interestCoverageRatio = 0;
                    $dscr = 0;
                    

                    foreach($financialsummary as $lineitem)
                    {
                        if($lineitem["values"] != null && count($lineitem["values"]) > 0)
                        {
                            foreach($lineitem["values"] as $valueItem)
                            {
                                if($valueItem["key"] == $currentDataPeriod["key"])
                                {
                                    switch($lineitem["label"])
                                    {
                                        case "Net Sales":
                                            $netSales = $valueItem["value"];
                                            break;
                                        case "Other Income":
                                            $otherIncome = $valueItem["value"];
                                            break;
                                        case "Income":
                                            $income = $valueItem["value"];
                                            break;
                                        case "PBDITA":
                                            $pbdita = $valueItem["value"];
                                            break;
                                        case "PBDITA Margin (%)":
                                            $pbditaMargin = $valueItem["value"];
                                            break;
                                        case "Interest":
                                            $interest = $valueItem["value"];
                                            break;
                                        case "Depreciation":
                                            $depreciation = $valueItem["value"];
                                            break;
                                        case "Operating Profit After Interest":
                                            $operatingProfitAfterInterest = $valueItem["value"];
                                            break;
                                        case "Income / Expenses":
                                            $incomeExpenses = $valueItem["value"];
                                            break;
                                        case "Profit Before Tax":
                                            $profitBeforeTax = $valueItem["value"];
                                            break;
                                        case "Profit After Tax":
                                            $profitAfterTax = $valueItem["value"];
                                            break;
                                        case "Net Profit Margin (%)":
                                            $netProfitMargin = $valueItem["value"];
                                            break;
                                        case "Net Cash Accruals (NCA)":
                                            $netCashAccruals = $valueItem["value"];
                                            break;
                                        case "Fixed Assets Gross":
                                            $fixedAssetsGross = $valueItem["value"];
                                            break;
                                        case "Fixed Assets Net":
                                            $fixedAssetsNet = $valueItem["value"];
                                            break;
                                        case "Non Current Assets (Ex. Fixed assets)":
                                            $nonCurrentAssets = $valueItem["value"];
                                            break;
                                        case "Tangible Networth (TNW)":
                                            $tangibleNetworth = $valueItem["value"];
                                            break;
                                        case "Exposure in Group Co./Subsidairies":
                                            $exposureInGroupCo = $valueItem["value"];
                                            break;
                                        case "Adjusted T N W (ATNW)":
                                            $adjustedTNW = $valueItem["value"];
                                            break;
                                        case "Long Term Debt (LTD)":
                                            $longTermDebt = $valueItem["value"];
                                            break;
                                        case "Short Term Debt (LTD)":
                                            $shortTermDebt = $valueItem["value"];
                                            break;
                                        case "Working Capital Borrowing":
                                            $workingCapitalBorrowing = $valueItem["value"];
                                            break;
                                        case "TOTAL OUTSIDE LIABILITIES":
                                            $totalOutsideLiabilities = $valueItem["value"];
                                            break;
                                        case "LTD/TNW":
                                            $LTD_TNW = $valueItem["value"];
                                            break;
                                        case "TOL/TNW":
                                            $TOL_TNW = $valueItem["value"];
                                            break;
                                        case "TOL/ATNW":
                                            $TOL_ATNW = $valueItem["value"];
                                            break;
                                        case "Total Current Assets":
                                            $totalCurrentAssets = $valueItem["value"];
                                            break;
                                        case "Total Current Liabilities":
                                            $totalCurrentLiabilities = $valueItem["value"];
                                            break;
                                        case "Net Working Capital":
                                            $netWorkingCapital = $valueItem["value"];
                                            break;
                                        case "Current Ratio":
                                            $currentRatio = $valueItem["value"];
                                            break;
                                        case "Inventory Holding period (days)":
                                            $inventoryHoldingPeriod = $valueItem["value"];
                                            break;
                                        case "Debtors Holding Period (days)":
                                            $debtorsHoldingPeriod = $valueItem["value"];
                                            break;
                                        case "Creditors Holding Period (days)":
                                            $creditorsHoldingPeriod = $valueItem["value"];
                                            break;
                                        case "Debt Equity Ratio":
                                            $debtEquityRatio = $valueItem["value"];
                                            break;
                                        case "Debt/PBITDA Ratio":
                                            $debt_PBITDARatio = $valueItem["value"];
                                            break;
                                        case "Interest Coverage Ratio":
                                            $interestCoverageRatio = $valueItem["value"];
                                            break;
                                        case "DSCR (Avg/Min)":
                                            $dscr = $valueItem["value"];
                                            break;
                                        
                                    }
                                    
                                }
                            }
                        }
                        

                    }

                    $fsInsertSql.= " ('$borrowerid', '$period_type', '$pmonth', '$pyear', '$result_type', '$netSales', '$otherIncome', ";
                    $fsInsertSql.= " '$income', '$pbdita', '$pbditaMargin', '$interest', '$depreciation', '$operatingProfitAfterInterest', ";
                    $fsInsertSql.= " '$incomeExpenses', '$profitBeforeTax', '$profitAfterTax', '$netProfitMargin', '$netCashAccruals',  ";
                    $fsInsertSql.= " '$fixedAssetsGross', '$fixedAssetsNet', '$nonCurrentAssets', '$tangibleNetworth', '$exposureInGroupCo', '$adjustedTNW', ";
                    $fsInsertSql.= " '$longTermDebt', '$shortTermDebt', '$workingCapitalBorrowing', '$totalOutsideLiabilities', '$LTD_TNW', '$TOL_TNW', ";
                    $fsInsertSql.= " '$TOL_ATNW', '$totalCurrentAssets', '$totalCurrentLiabilities', '$netWorkingCapital', '$currentRatio', '$inventoryHoldingPeriod', ";
                    $fsInsertSql.= " '$debtorsHoldingPeriod', '$creditorsHoldingPeriod', '$debtEquityRatio', '$debt_PBITDARatio ', '$interestCoverageRatio', '$dscr' ) ";
                    

                    if($i < count($dataPeriods) - 1)
                    {
                        $fsInsertSql.= " , ";    
                    }
                    
                }

                $this->db->query("Delete from `fp_borrower_financials_summary` where borrower_id = '$borrowerid' and year in ($periodYears) ");

                $data["fsInsertSql"] = $fsInsertSql;
                $this->db->query($fsInsertSql);

                //echo $fsInsertSql;


                $cfaInsertSql = " INSERT INTO `fp_borrower_financials_cfanalysis` (`borrower_id`, `period_type`, `month`, `year`, `result_type`, `net_profit_before_taxation`, `depreciation`, `dividend_income`, `interest_expense`, `interest_received`, `profit_loss_on_sale_of_fixed_assets`, `foreign_exchange_gains_loss`, `extraordinary_income_expense`, `operating_profit_before_wc_changes`, `changes_in_current_assets`, `changes_in_current_liabilities`, `net_cash_from_operating_activities`, `net_cash_from_investing_activities`, `net_cash_from_financing_activities`, `net_increase_in_cash_bank_balance`, `cash_bank_balance_in_begining`, `cash_bank_balance_at_end`) ";
                $cfaInsertSql.= " VALUES ";
                
                
                // For Balance Sheet Analysis
                for($i = 0 ; $i < count($dataPeriods); $i++)
			    {
                    $currentDataPeriod = $dataPeriods[$i];

                    $period_type = $currentDataPeriod["ptype"];
                    $pmonth = "0";
                    $pyear = $currentDataPeriod["year"];
                    $result_type = "";
                    

                    $net_profit_before_taxation = 0;
                    $depriciation = 0;
                    $dividend_income = 0;
                    $interest_expenses = 0;
                    $interest_received = 0;
                    $pl_on_sale_of_fa = 0;
                    $forex_gain_loss = 0;
                    $ex_income_expenses = 0;
                    $op_before_wc_changes = 0;

                    $changes_in_current_assets = 0;
                    $changes_in_current_liabilities = 0;
                    $net_cash_from_operating_activities = 0;
                    $net_cash_from_investing_activities = 0;
                    $net_cash_from_financing_activities = 0;
                    $net_increase_in_cash_bank_balance = 0;
                    $cash_bank_balance_in_begining = 0;
                    $casg_bank_balance_at_end = 0;

                    foreach($cfAnalysis as $lineitem)
                    {
                        if($lineitem["values"] != null && count($lineitem["values"]) > 0)
                        {
                            foreach($lineitem["values"] as $valueItem)
                            {
                                if($valueItem["key"] == $currentDataPeriod["key"])
                                {
                                    switch($lineitem["label"])
                                    {
                                        case "Net profit before taxation":
                                            $net_profit_before_taxation = $valueItem["value"];
                                            break;
                                        case "Depreciation":
                                            $depriciation = $valueItem["value"];
                                            break;
                                        case "Dividend Income":
                                            $dividend_income = $valueItem["value"];
                                            break;
                                        case "Interest Expenses":
                                            $interest_expenses = $valueItem["value"];
                                            break;
                                        case "Interest Income":
                                            $interest_received = $valueItem["value"];
                                            break;
                                        case "Profit / Loss on sale of fixed assets / investments":
                                            $pl_on_sale_of_fa = $valueItem["value"];
                                            break;
                                        case "Foreign exchange gain/loss":
                                            $forex_gain_loss = $valueItem["value"];
                                            break;
                                        case "Extraordinary income / expenses":
                                            $ex_income_expenses = $valueItem["value"];
                                            break;
                                        case "Operating profit before working capital changes":
                                            $op_before_wc_changes = $valueItem["value"];
                                            break;
                                        case "Change in current assets":
                                            $changes_in_current_assets = $valueItem["value"];
                                            break;
                                        case "Change in current liabilities":
                                            $changes_in_current_liabilities = $valueItem["value"];
                                            break;
                                    }
                                    
                                }
                            }
                        }
                        

                    }

                    $cfaInsertSql.= " ('$borrowerid', '$period_type', '$pmonth', '$pyear', '$result_type', '$net_profit_before_taxation', ";
                    $cfaInsertSql.= " '$depriciation', '$dividend_income', '$interest_expenses', '$interest_received', '$pl_on_sale_of_fa', '$forex_gain_loss', "; 
                    $cfaInsertSql.= " '$ex_income_expenses', '$op_before_wc_changes', '$changes_in_current_assets', '$changes_in_current_liabilities', '$net_cash_from_operating_activities', ";
                    $cfaInsertSql.= " '$net_cash_from_investing_activities', '$net_cash_from_financing_activities', '$net_increase_in_cash_bank_balance', '$cash_bank_balance_in_begining', '$casg_bank_balance_at_end') ";
                    

                    if($i < count($dataPeriods) - 1)
                    {
                        $cfaInsertSql.= " , ";    
                    }
                    
                }

                $this->db->query("Delete from `fp_borrower_financials_cfanalysis` where borrower_id = '$borrowerid' and year in ($periodYears) ");
                $data["cfaInsertSql"] = $cfaInsertSql;
                $this->db->query($cfaInsertSql);

                // For Balance Sheet Source

                $bssInsertSql = " INSERT INTO `fp_borrower_financials_bssource` (`borrower_id`, `period_type`, `month`, `year`, `result_type`, `bank_borrowings_from_applicant_bank`, `bank_borrowings_from_other_banks`, `st_borrowings_from_associates`, `st_borrowings_from_others`, `creditors_for_purchases_others`, `creditors_for_purchases_group_companies`, `creditors_for_expenses`, `advances_payments_from_customers`, `provisions_for_tax`, `provisions_for_deferred_tax`, `others`, `dividends_payable`, `statutory_liabilities_due_within_one_year`, `installments_of_term_to_banks`, `installments_of_term_loans_to_others`, `deposits_due_payable_within_a_year`, `other_current_liabilities_due_within_one_year`, `debentures_maturing_after_1_year`, `preference_share_capital_maturity_within_12_years`, `dealers_deposit`, `deferred_tax_liability`, `term_loans_from_banks`, `term_loans_from_financial_istitution`, `term_deposits`, `borrowings_from_subsidiaries`, `unsecured_loans`, `other_term_liabilities`, `equity_share_capital`, `share_capital_paid_up`, `share_application_money`, `general_reserve`, `revaluation_reserve`, `partners_capital`, `balance_in_partners_current_ac`, `share_premium`, `capital_subsidy`, `quasi_equity`, `balance_in_pl_account`, `cash_balances`, `bank_balances`, `govt_and_other_trustee_securities`, `fixed_deposits_with_banks`, `others_investments_in_subsidiaries`, `domestic_receivables`, `export_receivables`, `raw_Materials_imported`, `raw_materials_indigenous`, `work_in_process`, `finished_goods`, `other_consumable_spares_imported`, `other_consumable_spares_indigenous`, `adv_to_suppliers_of_raw_materials`, `adv_payment_of_tax`, `prepaid_expenses`, `other_advances_current_asset`, `gross_block`, `accumulated_depreciation`, `capital_wip`, `investments_in_group_concerns`, `loans_to_group_concerns`, `investments_in_others`, `adv_to_suppliers_of_capital_goods`, `deferred_receivables`, `debtors_more_than_6_months`, `others_loan_advances`, `security_deposits`, `deposits_with_government_dept`, `deferred_tax_asset`, `other_non_current_assets`, `goodwill_patents_trademarks`, `misc_exp_not_written_off`, `other_deferred_revenue_exp`, `borrowings_sub_total`, `total_current_liabilities`, `total_term_liabilities`, `total_outside_liabilities`, `net_worth`, `total_liabilities`, `investments`, `receivables`, `inventory`, `total_current_assets`, `net_block`, `total_non_current_assets`, `total_intangible_assets`, `total_assets` ) ";
                
                $bssInsertSql.= " VALUES ";
                
                for($i = 0 ; $i < count($dataPeriods); $i++)
			    {
                    $currentDataPeriod = $dataPeriods[$i];

                    $period_type = $currentDataPeriod["ptype"];
                    $pmonth = "0";
                    $pyear = $currentDataPeriod["year"];
                    $result_type = "";
                    
                    $bank_borrowings_from_applicant_bank = 0;
                    $bank_borrowings_from_other_banks = 0;

                    $borrowings_sub_total = 0;

                    $st_borrowings_from_associates = 0;
                    $st_borrowings_from_others = 0;
                    $creditors_for_purchases_others = 0;
                    $creditors_for_purchases_group_companies = 0;
                    $creditors_for_expenses = 0;
                    $advances_payments_from_customers = 0;
                    $provisions_for_tax = 0;
                    $provisions_for_deferred_tax = 0;
                    $others = 0;
                    $dividends_payable = 0;
                    $statutory_liabilities_due_within_one_year = 0;
                    $installments_of_term_to_banks = 0;
                    $installments_of_term_loans_to_others = 0;
                    $deposits_due_payable_within_a_year = 0;
                    $other_current_liabilities_due_within_one_year = 0;

                    $total_current_liabilities = 0;

                    $debentures_maturing_after_1_year = 0;
                    $preference_share_capital_maturity_within_12_years = 0;
                    $dealers_deposit = 0;
                    $deferred_tax_liability = 0;
                    $term_loans_from_banks = 0;
                    $term_loans_from_financial_istitution = 0;
                    $term_deposits = 0;
                    $borrowings_from_subsidiaries = 0;
                    $unsecured_loans = 0;
                    $other_term_liabilities = 0;

                    $total_term_liabilities = 0;
                    $total_outside_liabilities = 0;

                    $net_worth = 0;
                    $total_liabilities = 0;

                    $equity_share_capital = 0;
                    $share_capital_paid_up = 0;
                    $share_application_money = 0;
                    $general_reserve = 0;
                    $revaluation_reserve = 0;
                    $partners_capital = 0;
                    $balance_in_partners_current_ac = 0;
                    $share_premium = 0;
                    $capital_subsidy = 0;
                    $quasi_equity = 0;
                    $balance_in_pl_account = 0;
                    $cash_balances = 0;
                    $bank_balances = 0;

                    $investments = 0;

                    $govt_and_other_trustee_securities = 0;
                    $fixed_deposits_with_banks = 0;
                    $others_investments_in_subsidiaries = 0;

                    $receivables = 0;

                    $domestic_receivables = 0;
                    $export_receivables = 0;

                    $inventory = 0;

                    $raw_Materials_imported = 0;
                    $raw_materials_indigenous = 0;
                    $work_in_process = 0;
                    $finished_goods = 0;
                    $other_consumable_spares_imported = 0;
                    $other_consumable_spares_indigenous = 0;
                    $adv_to_suppliers_of_raw_materials = 0;
                    $advance_payment_of_tax = 0;
                    $prepaid_expenses = 0;
                    $other_advances_current_asset = 0;

                    $total_current_assets = 0;

                    $gross_block = 0;
                    $accumulated_depreciation = 0;

                    $net_block = 0;

                    $capital_wip = 0;
                    $investments_in_group_concerns = 0;
                    $loans_to_group_concerns = 0;
                    $investments_in_others = 0;
                    $adv_to_suppliers_of_capital_goods = 0;
                    $deferred_receivables = 0;
                    $debtors_more_than_6_months = 0;
                    $others_loan_advances = 0;
                    $security_deposits = 0;
                    $deposits_with_government_dept = 0;
                    $deferred_tax_asset = 0;
                    $other_non_current_assets = 0;

                    $total_non_current_assets = 0;

                    $goodwill_patents_trademarks = 0;
                    $misc_exp_not_written_off = 0;
                    $other_deferred_revenue_exp = 0;

                    $total_intangible_assets = 0;
                    $total_assets = 0;

                    
                    foreach($bsSourceLineItems as $lineitem)
                    {
                        if($lineitem["values"] != null && count($lineitem["values"]) > 0)
                        {
                            if(count($lineitem["values"]) > 0)
                            {
                                foreach($lineitem["values"] as $valueItem)
                                {
                                    
                                    if($valueItem->key == $currentDataPeriod["key"])
                                    {
                                        switch($lineitem["label"])
                                        {
                                            case "Bank Borrowings - From applicant Bank":
                                                $bank_borrowings_from_applicant_bank = $valueItem->value;
                                                break;
                                            case "Bank Borrowings - From other Banks":
                                                
                                                $bank_borrowings_from_other_banks = $valueItem->value;
                                                break;
                                                case "Short term borrowings from Associates & Group Concerns repayable within one year":
                                                    $st_borrowings_from_associates = $valueItem->value;
                                                    break;
                                                case "Short term borrowings from Others":
                                                    $st_borrowings_from_others = $valueItem->value;
                                                    break;
                                                case "Creditors for purchases – others":
                                                    $creditors_for_purchases_others = $valueItem->value;
                                                    break;
                                                case "Creditors for purchases – Group Companies":
                                                    $creditors_for_purchases_group_companies = $valueItem->value;
                                                    break;
                                                case "Creditors for expenses":
                                                    $creditors_for_expenses = $valueItem->value;
                                                    break;
                                                case "Advances/ payments from customers/deposits from dealers.":
                                                    $advances_payments_from_customers = $valueItem->value;
                                                    break;
                                                case "Provisions ":
                                                    break;
                                                case "        - Tax":
                                                   $provisions_for_tax = $valueItem->value;
                                                    break;
                                                case "        - Deferred tax": 
                                                    $provisions_for_deferred_tax = $valueItem->value;
                                                    break;
                                                case " - Others":
                                                    $others = $valueItem->value;
                                                    break;
                                                case "Statutory liabilities due within one year":
                                                    $statutory_liabilities_due_within_one_year = $valueItem->value;
                                                    break;
                                                case "Installments of Term Loans/Debentures (due within one year)- To banks ":
                                                    $installments_of_term_to_banks = $valueItem->value;
                                                    break;
                                                case "Installments of Term Loans/Debentures (due within one year)- To Others":
                                                    $installments_of_term_loans_to_others = $valueItem->value;
                                                    break;
                                                case "Deposits due / payable within a year":
                                                    $deposits_due_payable_within_a_year = $valueItem->value;
                                                    break;
                                                case "Other Current Liabilities due within one year":
                                                    $other_current_liabilities_due_within_one_year = $valueItem->value;
                                                    break;
                                                case "Debentures maturing after 1 year":
                                                    $debentures_maturing_after_1_year = $valueItem->value;
                                                    break;
                                                case "Preference share capital maturity < 12 years":
                                                    $preference_share_capital_maturity_within_12_years = $valueItem->value;
                                                    break;
                                                case "Dealer's Deposit":
                                                    $dealers_deposit = $valueItem->value;
                                                    break;
                                                case "Deferred Tax Liability":
                                                    $deferred_tax_liability = $valueItem->value;
                                                    break;
                                                case "Term Loans  - From Banks":
                                                    $term_loans_from_banks = $valueItem->value;
                                                    break;
                                                case "Term Loans - From Financial Institution":
                                                    $term_loans_from_financial_istitution = $valueItem->value;
                                                    break;
                                                case "Term Deposits":
                                                    $term_deposits = $valueItem->value;
                                                    break;
                                                case "Borrowings from subsidiaries / affiliates":
                                                    $borrowings_from_subsidiaries = $valueItem->value;
                                                    break;
                                                case "Unsecured Loans ":
                                                    $unsecured_loans = $valueItem->value;
                                                    break;
                                                case "Other term liabilities":
                                                    $other_term_liabilities = $valueItem->value;
                                                    break;
                                                case "Equity Share Capital ":
                                                    $equity_share_capital = $valueItem->value;
                                                    break;
                                                case "Share Capital (Paid-up)":
                                                    $share_capital_paid_up = $valueItem->value;
                                                    break;
                                                case "Share Application money":
                                                    $share_application_money = $valueItem->value;
                                                    break;
                                                case "General Reserve":
                                                    $general_reserve = $valueItem->value;
                                                    break;
                                                case "Revaluation Reserve":
                                                    $revaluation_reserve = $valueItem->value;
                                                    break;
                                                case "Partners capital / Proprietor's capital":
                                                    $partners_capital = $valueItem->value;
                                                    break;
                                                case "Balance in Partners' Current A/c (+ / -)":
                                                    $balance_in_partners_current_ac = $valueItem->value;
                                                    break;
                                                case "Other Reserves & Surplus:":
                                                    break;
                                                case "Share Premium":
                                                    $share_premium = $valueItem->value;
                                                    break;
                                                case "Capital subsidy":
                                                    $capital_subsidy = $valueItem->value;
                                                    break;
                                                case "Quasi Equity":
                                                    $quasi_equity = $valueItem->value;
                                                    break;
                                                case "Balance in P&L Account (+ / - )":
                                                    $balance_in_pl_account = $valueItem->value;
                                                    break;
                                                case "Cash Balances":
                                                    $cash_balances = $valueItem->value;
                                                    break;
                                                case "Bank Balances":
                                                    $bank_balances = $valueItem->value;
                                                    break;
                                                case "Govt. and other trustee Securities":
                                                    $govt_and_other_trustee_securities = $valueItem->value;
                                                    break;
                                                case "Fixed Deposits with Banks":
                                                    $fixed_deposits_with_banks = $valueItem->value;
                                                    break;
                                                case "Others – Investments in Subsidiaries/Group Companies":
                                                    $others_investments_in_subsidiaries = $valueItem->value;
                                                    break;
                                                case "Domestic Receivables ":
                                                    $domestic_receivables = $valueItem->value;
                                                    break;
                                                case "Export Receivables":
                                                    $export_receivables = $valueItem->value;
                                                    break;
                                                case " Raw Materials – Imported":
                                                    $raw_Materials_imported = $valueItem->value;
                                                    break;
                                                case " Raw Materials – Indigenous":
                                                    $raw_materials_indigenous = $valueItem->value;
                                                    break;
                                                case " Work in process":
                                                    $work_in_process = $valueItem->value;
                                                    break;
                                                case " Finished Goods (incl Traded Goods)":
                                                    $finished_goods = $valueItem->value;
                                                    break;
                                                case "Other consumable spares – Imported":
                                                    $other_consumable_spares_imported = $valueItem->value;
                                                    break;
                                                case "Other consumable spares -  Indigenous":
                                                    $other_consumable_spares_indigenous = $valueItem->value;
                                                    break;
                                                case "Advances to suppliers of Raw materials/Stores/Spares":
                                                    $adv_to_suppliers_of_raw_materials = $valueItem->value;
                                                    break;
                                                case "Advance payment of tax":
                                                    $advance_payment_of_tax = $valueItem->value;
                                                    break;
                                                case "Prepaid Expenses":
                                                    $prepaid_expenses = $valueItem->value;
                                                    break;
                                                case "Other Advances/current Asset":
                                                    $other_advances_current_asset = $valueItem->value;
                                                    break;
                                                case "Gross Block ":
                                                    $gross_block = $valueItem->value;
                                                    break;
                                                case "Less: Accumulated Depreciation":
                                                    $accumulated_depreciation = $valueItem->value;
                                                    break;
                                                case "Capital Work in progress":
                                                    $capital_wip = $valueItem->value;
                                                    break;
                                                case "Investments in Group concerns":
                                                    $investments_in_group_concerns = $valueItem->value;
                                                    break;
                                                case "Loans to group concerns / Advances to subsidiaries":
                                                    $loans_to_group_concerns = $valueItem->value;
                                                    break;
                                                case "Investments in others":
                                                    $investments_in_others = $valueItem->value;
                                                    break;
                                                case "Advances to suppliers of capital goods and contractors":
                                                    $adv_to_suppliers_of_capital_goods = $valueItem->value;
                                                    break;
                                                case "Deferred receivables (maturity exceeding one year)":
                                                    $deferred_receivables = $valueItem->value;
                                                    break;
                                                case "Debtors more than 6 months":
                                                    $debtors_more_than_6_months = $valueItem->value;
                                                    break;
                                                case "Others (Loans & Advances non current in nature, ICD’s etc.)":
                                                    $others_loan_advances = $valueItem->value;
                                                    break;
                                                case "Security deposits":
                                                    $security_deposits = $valueItem->value;
                                                    break;
                                                case "Deposits with Government departments":
                                                    $deposits_with_government_dept = $valueItem->value;
                                                    break;
                                                case "Deferred Tax Asset":
                                                    $deferred_tax_asset = $valueItem->value;
                                                    break;
                                                case "Other Non-current Assets":
                                                    $other_non_current_assets = $valueItem->value;
                                                    break;
                                                case "Intangible Assets:":
                                                    break;
                                                case "Goodwill, Patents & trademarks":
                                                    $goodwill_patents_trademarks = $valueItem->value;
                                                    break;
                                                case "Miscellaneous expenditure not w/off":
                                                    $misc_exp_not_written_off = $valueItem->value;
                                                    break;
                                                case "Other deferred revenue expenses":
                                                    $other_deferred_revenue_exp = $valueItem->value;
                                                    break;
                                        }
                                        
                                    }
                                }
                            }
                            
                        }
                        

                    }

                    $borrowings_sub_total = $bank_borrowings_from_applicant_bank + $bank_borrowings_from_other_banks;
                    $total_current_liabilities = $borrowings_sub_total + $st_borrowings_from_associates + $st_borrowings_from_others + $creditors_for_purchases_others + $creditors_for_purchases_group_companies + $creditors_for_expenses + $advances_payments_from_customers + $provisions_for_tax + $provisions_for_deferred_tax + $others + $dividends_payable + $statutory_liabilities_due_within_one_year + $installments_of_term_to_banks + $installments_of_term_loans_to_others + $deposits_due_payable_within_a_year + $other_current_liabilities_due_within_one_year;
                    $total_term_liabilities =  $debentures_maturing_after_1_year + $preference_share_capital_maturity_within_12_years + $dealers_deposit + $deferred_tax_liability + $term_loans_from_banks + $term_loans_from_financial_istitution + $term_deposits + $borrowings_from_subsidiaries + $unsecured_loans + $other_term_liabilities;
                    $total_outside_liabilities = $total_current_liabilities + $total_term_liabilities;

                    $net_worth = $share_capital_paid_up + $share_application_money + $general_reserve + $revaluation_reserve + $share_premium + $capital_subsidy + $quasi_equity + $balance_in_pl_account;
                    $total_liabilities = $net_worth + $total_outside_liabilities;

                    $investments =  $govt_and_other_trustee_securities + $fixed_deposits_with_banks + $others_investments_in_subsidiaries;

                    $receivables = $domestic_receivables + $export_receivables;
                    $inventory =  $raw_Materials_imported + $raw_materials_indigenous + $work_in_process + $finished_goods + $other_consumable_spares_imported + $other_consumable_spares_indigenous;

                    $total_current_assets = $cash_balances + $bank_balances + $investments + $receivables + $inventory ;
                    $total_current_assets+= $adv_to_suppliers_of_raw_materials + $advance_payment_of_tax + $prepaid_expenses + $other_advances_current_asset;

                    $net_block = $gross_block - $accumulated_depreciation;

                    $total_non_current_assets =  $investments_in_group_concerns + $loans_to_group_concerns + $investments_in_others + $adv_to_suppliers_of_capital_goods + $deferred_receivables + $debtors_more_than_6_months + $others_loan_advances + $security_deposits + $deposits_with_government_dept + $deferred_tax_asset + $other_non_current_assets;

                    $total_intangible_assets = $goodwill_patents_trademarks + $misc_exp_not_written_off + $other_deferred_revenue_exp;
                    $total_assets = $total_current_assets + $net_block + $capital_wip + $total_non_current_assets + $total_intangible_assets;

                    $bssInsertSql.= " ('$borrowerid', '$period_type', '$pmonth', '$pyear', 'audited', '$bank_borrowings_from_applicant_bank', '$bank_borrowings_from_other_banks', '$st_borrowings_from_associates', '$st_borrowings_from_others', '$creditors_for_purchases_others',  ";
                    $bssInsertSql.= " '$creditors_for_purchases_group_companies', '$creditors_for_expenses', '$advances_payments_from_customers', '$provisions_for_tax', '$provisions_for_deferred_tax', '$others', '$dividends_payable', '$statutory_liabilities_due_within_one_year', '$installments_of_term_to_banks',  ";
                    $bssInsertSql.= " '$installments_of_term_loans_to_others', '$deposits_due_payable_within_a_year', '$other_current_liabilities_due_within_one_year', '$debentures_maturing_after_1_year', '$preference_share_capital_maturity_within_12_years', '$dealers_deposit', '$deferred_tax_liability', '$term_loans_from_banks',  ";
                    $bssInsertSql.= " '$term_loans_from_financial_istitution', '$term_deposits', '$borrowings_from_subsidiaries', '$unsecured_loans', '$other_term_liabilities', '$equity_share_capital', '$share_capital_paid_up', '$share_application_money', '$general_reserve', '$revaluation_reserve', '$partners_capital', '$balance_in_partners_current_ac',  ";
                    $bssInsertSql.= " '$share_premium', '$capital_subsidy', '$quasi_equity', '$balance_in_pl_account', '$cash_balances', '$bank_balances', '$govt_and_other_trustee_securities', '$fixed_deposits_with_banks', '$others_investments_in_subsidiaries', '$domestic_receivables', '$export_receivables', '$raw_Materials_imported', '$raw_materials_indigenous',  ";
                    $bssInsertSql.= " '$work_in_process', '$finished_goods', '$other_consumable_spares_imported', '$other_consumable_spares_indigenous', '$adv_to_suppliers_of_raw_materials', '$advance_payment_of_tax', '$prepaid_expenses', '$other_advances_current_asset', '$gross_block', '$accumulated_depreciation', '$capital_wip', '$investments_in_group_concerns', ";
                    $bssInsertSql.= " '$loans_to_group_concerns', '$investments_in_others', '$adv_to_suppliers_of_capital_goods', '$deferred_receivables', '$debtors_more_than_6_months', '$others_loan_advances', '$security_deposits', '$deposits_with_government_dept', '$deferred_tax_asset', '$other_non_current_assets', '$goodwill_patents_trademarks', '$misc_exp_not_written_off', '$other_deferred_revenue_exp', ";
                    $bssInsertSql.= " '$borrowings_sub_total', '$total_current_liabilities', '$total_term_liabilities', '$total_outside_liabilities', '$net_worth', '$total_liabilities', ";
                    $bssInsertSql.= " '$investments', '$receivables', '$inventory', '$total_current_assets', '$net_block', '$total_non_current_assets', '$total_intangible_assets', '$total_assets') ";

                    if($i < count($dataPeriods) - 1)
                    {
                        $bssInsertSql.= " , ";    
                    }
                    
                    
                }

                $this->db->query("Delete from `fp_borrower_financials_bssource` where borrower_id = '$borrowerid' and year in ($periodYears) ");
                $data["bssInsertSql"] = $bssInsertSql;
                $this->db->query($bssInsertSql);

                // For - Profit & Loss Source

                $plsInsertSql = " INSERT INTO `fp_borrower_financials_plsource` (`borrower_id`, `period_type`, `month`, `year`, `result_type`, `sales_domestic`, `sales_export`, `sales_total`, `excise_duty`, `net_sales`, `perc_change_in_netsales`, `export_incentive`, `other_income`, `total_operating_income`, `raw_materials_imported`, `raw_materials_indigenous`, `other_spares_imported`, `other_spares_indegenous`, `power_and_fuel`, `direct_labour_and_wages`, `other_manf_exp`, `depreciation`, `cos_sub_total`, `op_stock_of_wip`, `cl_stock_of_wip`, `total_cos_of_prod`, `os_of_finished_goods`, `cs_of_finished_goods`, `total_cost_of_sales`, `salary_staff_exp`, `bad_debts`, `selling_admin_exp`, `admin_sell_exp_sub_total`, `operating_profit_before_int`, `interest_wc_loans`, `interest_term_loans`, `bank_charges`, `total_interest`, `operating_profit_after_interest`, `interest_income`, `profit_on_sale_of_assets`, `dividend_received`, `forex_gains`, `extraordinary_income`, `other_non_op_income`, `total_non_op_income`, `loss_on_sale_of_assets`, `extra_ordinary_expenses`, `forex_loses`, `other_non_op_expenses`, `total_non_op_exp`, `net_op_non_op_inc_exp`, `profit_before_tax`, `prov_for_tax_current`, `prov_for_tax_deferred`, `prov_for_tax_subtotal`, `net_profit_after_tax`, `dividend_paid`, `retained_profit`) ";
                
                $plsInsertSql.= " VALUES ";
                
                
                
                for($i = 0 ; $i < count($dataPeriods); $i++)
			    {
                    $currentDataPeriod = $dataPeriods[$i];

                    $period_type = $currentDataPeriod["ptype"];
                    $pmonth = "0";
                    $pyear = $currentDataPeriod["year"];
                    $result_type = "";
                    
                    $sales_domestic = 0;
                    $sales_export = 0;
                    $sales_total = 0;
                    $excise_duty = 0;
                    $net_sales = 0;
                    $perc_change_in_netsales = 0;
                    $export_incentive = 0;
                    $other_income = 0;
                    $total_operating_income = 0;
                    $raw_materials_imported = 0;
                    $raw_materials_indigenous = 0;
                    $other_spares_imported = 0;
                    $other_spares_indegenous = 0;
                    $power_and_fuel = 0;
                    $direct_labour_and_wages = 0;
                    $other_manf_exp = 0;
                    $depreciation = 0;
                    $cos_sub_total = 0;
                    $op_stock_of_wip = 0;
                    $cl_stock_of_wip = 0;
                    $total_cos_of_prod = 0;
                    $os_of_finished_goods = 0;
                    $cs_of_finished_goods = 0;
                    $total_cost_of_sales = 0;
                    $salary_staff_exp = 0;
                    $bad_debts = 0;
                    $selling_admin_exp = 0;

                    $other_admin_exp = 0;

                    $admin_sell_exp_sub_total = 0;
                    $operating_profit_before_int = 0;
                    $interest_wc_loans = 0;
                    $interest_term_loans = 0;
                    $bank_charges = 0;
                    $total_interest = 0;
                    $operating_profit_after_interest = 0;
                    $interest_income = 0;
                    $profit_on_sale_of_assets = 0;
                    $dividend_received = 0;
                    $forex_gains = 0;
                    $extraordinary_income = 0;
                    $other_non_op_income = 0;
                    $total_non_op_income = 0;
                    $loss_on_sale_of_assets = 0;
                    $extra_ordinary_expenses = 0;
                    $forex_loses = 0;
                    $other_non_op_expenses = 0;
                    $total_non_op_exp = 0;
                    $net_op_non_op_inc_exp = 0;
                    $profit_before_tax = 0;
                    $prov_for_tax_current = 0;
                    $prov_for_tax_deferred = 0;
                    $prov_for_tax_subtotal = 0;
                    $net_profit_after_tax = 0;
                    $dividend_paid = 0;
                    $retained_profit = 0;

                    
                    foreach($plSourceLineItems as $lineitem)
                    {
                        if($lineitem["values"] != null && count($lineitem["values"]) > 0)
                        {
                            if(count($lineitem["values"]) > 0)
                            {
                                foreach($lineitem["values"] as $valueItem)
                                {
                                    
                                    if($valueItem["key"] == $currentDataPeriod["key"])
                                    {
                                        switch($lineitem["label"])
                                        {
                                            case "- Domestic":
                                                $sales_domestic = $valueItem["value"];
                                                break;
                                            case "- Export":
                                                $sales_export = $valueItem["value"];
                                                break;
                                            case "Sub Total":
                                                
                                                if($lineitem["type"] == "sales")
                                                {
                                                    $sales_total = $valueItem["value"];
                                                }
                                                else if ($lineitem["type"] == "cost of sale")
                                                {
                                                    $cos_sub_total = $valueItem["value"];
                                                }
                                                else if ($lineitem["type"] == "other admin exp")
                                                {
                                                    $admin_sell_exp_sub_total = $valueItem["value"];
                                                }
                                                else if ($lineitem["type"] == "provision for tax")
                                                {
                                                    $prov_for_tax_subtotal = $valueItem["value"];
                                                }
                                                break;
                                            case "Less Excise Duty (if applicable)":
                                                $excise_duty = $valueItem["value"];
                                                break;
                                            case "Net Sales":
                                                $net_sales = $valueItem["value"];
                                            break;
                                            case "% wise rise/fall in net sales as compared to previous year":
                                                $perc_change_in_netsales = $valueItem["value"];
                                            break;
                                            case "Export Incentive":
                                                $export_incentive = $valueItem["value"];
                                            break;
                                            case "Other Income":
                                                $other_income = $valueItem["value"];
                                                break;
                                            case "Total Operating Income":
                                                $total_operating_income = $valueItem["value"];
                                                break;
                                            case " i) Imported":
                                                $raw_materials_imported = $valueItem["value"];
                                            break;
                                            case " ii) Indigenous":
                                                $raw_materials_indigenous = $valueItem["value"];
                                            break;
                                            case " i) Other Spares Imported":
                                                $other_spares_imported = $valueItem["value"];
                                            break;
                                            case " ii) Other Spares Indigenous":
                                                $other_spares_indegenous = $valueItem["value"];
                                            break;
                                            case "Power and fuel ":
                                                $power_and_fuel = $valueItem["value"];
                                            break;
                                            case "Direct labour and wages":
                                                $direct_labour_and_wages = $valueItem["value"];
                                            break;
                                            case "Other manufacturing expenses":
                                                $other_manf_exp = $valueItem["value"];
                                                
                                            break;
                                            case "Depreciation":
                                                $depreciation = $valueItem["value"];
                                            break;
                                            case "Add: Op. Stock of WIP":
                                                $op_stock_of_wip = $valueItem["value"];
                                            break;
                                            case "Less: Cl. Stock of WIP":
                                                $cl_stock_of_wip = $valueItem["value"];
                                            break;
                                            case "Total Cost of Production":
                                                $total_cos_of_prod = $valueItem["value"];
                                            break;
                                            case "Add Opening Stock of Finished Goods":
                                                $os_of_finished_goods = $valueItem["value"];
                                            
                                            break;
                                            case "Less: Closing Stock of Finished Goods":
                                                $cs_of_finished_goods = $valueItem["value"];
                                            break;
                                            case "Total Cost of Sales":
                                                $total_cost_of_sales = $valueItem["value"];
                                            break;
                                            case "Salary & Staff Expenses":
                                                $salary_staff_exp = $valueItem["value"];
                                            break;
                                            case "Bad Debts":
                                                $bad_debts = $valueItem["value"];
                                                
                                            break;
                                            case "Selling, Gen. & Administration Exp":
                                                $selling_admin_exp = $valueItem["value"];
                                            break;
                                            case "Other Administration Exp":
                                                $other_admin_exp = $valueItem["value"];
                                            break;
                                            case "Operating Profit before Interest":
                                                $operating_profit_before_int = $valueItem["value"];
                                            break;
                                            case "Interest - Working capital loans":
                                                $interest_wc_loans = $valueItem["value"];
                                            break;
                                            case "Interest - Term Loans/Fixed loans":
                                                $interest_term_loans = $valueItem["value"];
                                            break;
                                            case "Bank Charges":
                                                $bank_charges = $valueItem["value"];
                                            break;
                                            case "Total Interest":
                                                $total_interest = $valueItem["value"];
                                            break;
                                            case "Operating Profit after Interest":
                                                $operating_profit_after_interest = $valueItem["value"];
                                            break;
                                            case "Interest Income":
                                                $interest_income = $valueItem["value"];
                                            break;
                                            case "Profit on sale of assets/ investments":
                                                $profit_on_sale_of_assets = $valueItem["value"];
                                            break;
                                            case "Dividend received":
                                                $dividend_received = $valueItem["value"];
                                            break;
                                            case "Forex gains":
                                                $forex_gains = $valueItem["value"];
                                            break;
                                            case "Extraordinary Income":
                                                $extraordinary_income = $valueItem["value"];
                                            break;
                                            case "Other Non Operating Income":
                                                $other_non_op_income = $valueItem["value"];
                                            break;
                                            case "Total non-operating Income":
                                                $total_non_op_income = $valueItem["value"];
                                            break;
                                            case "Loss on sale of assets":
                                                $loss_on_sale_of_assets = $valueItem["value"];
                                            break;
                                            case "Extraordinary Expenses ":
                                                $extra_ordinary_expenses = $valueItem["value"];
                                            break;
                                            case "Forex losses":
                                                $forex_loses = $valueItem["value"];
                                            break;
                                            case "Other Non- operating expenses":
                                                $other_non_op_expenses = $valueItem["value"];
                                            break;
                                            case "Total Non-operating expenses":
                                                $total_non_op_exp = $valueItem["value"];
                                            break;
                                            case "Net of Non-operating Income / Expenses":
                                                $net_op_non_op_inc_exp = $valueItem["value"];
                                            break;
                                            case "Profit Before tax ":
                                                $profit_before_tax = $valueItem["value"];
                                            break;
                                            case "Current":
                                                $prov_for_tax_current = $valueItem["value"];
                                            break;
                                            case "Deferred":
                                                $prov_for_tax_deferred = $valueItem["value"];
                                            break;
                                            case "Net Profit After tax":
                                                $net_profit_after_tax = $valueItem["value"];
                                            break;
                                            case "Dividend Paid":
                                                $dividend_paid = $valueItem["value"];
                                            break;
                                            case "Retained Profit ":
                                                $retained_profit = $valueItem["value"];
                                            break;
                                        }
                                        
                                    }
                                }
                            }
                            
                        }
                        

                    }

                    
                    $plsInsertSql.= " ('$borrowerid','$period_type','$pmonth','$pyear','audited','$sales_domestic','$sales_export','$sales_total','$excise_duty','$net_sales','$perc_change_in_netsales','$export_incentive', ";
                    $plsInsertSql.= " '$other_income','$total_operating_income','$raw_materials_imported','$raw_materials_indigenous','$other_spares_imported','$other_spares_indegenous','$power_and_fuel','$direct_labour_and_wages','$other_manf_exp','$depreciation', ";
                    $plsInsertSql.= " '$cos_sub_total','$op_stock_of_wip','$cl_stock_of_wip','$total_cos_of_prod','$os_of_finished_goods','$cs_of_finished_goods','$total_cost_of_sales','$salary_staff_exp','$bad_debts','$selling_admin_exp','$admin_sell_exp_sub_total','$operating_profit_before_int','$interest_wc_loans', ";
                    $plsInsertSql.= " '$interest_term_loans','$bank_charges','$total_interest','$operating_profit_after_interest','$interest_income','$profit_on_sale_of_assets','$dividend_received','$forex_gains','$extraordinary_income','$other_non_op_income','$total_non_op_income','$loss_on_sale_of_assets','$extra_ordinary_expenses', ";
                    $plsInsertSql.= " '$forex_loses','$other_non_op_expenses','$total_non_op_exp','$net_op_non_op_inc_exp','$profit_before_tax','$prov_for_tax_current','$prov_for_tax_deferred','$prov_for_tax_subtotal','$net_profit_after_tax','$dividend_paid','$retained_profit')  ";
                    
                    if($i < count($dataPeriods) - 1)
                    {
                        $plsInsertSql.= " , ";    
                    }
                    
                    
                }

                

                $this->db->query("Delete from `fp_borrower_financials_plsource` where borrower_id = '$borrowerid' and year in ($periodYears) ");
                $data["plsInsertSql"] = $plsInsertSql;
                $this->db->query($plsInsertSql);


                $resp = array('status' => 200,'message' =>  'Success','data' => $data, 'sql' => $bssInsertSql);
                json_output(200,$resp);
			
            
			
        
	}   //  END OF ANALYZEDATA 
       


       public   function AnalyzeData_old($borrowerid,$xlrt_response){
    
            $financejson 	= $xlrt_response;
            $financialstype = "STA";

            $data["balancesheetdata"] = GetBalanceSheetData($financejson, $financialstype);

            $dataPeriods = $data["balancesheetdata"]["periods"];


            // $data["profitandloss"] = GetProfitAndLoss($financejson, $financialstype); 

            $data["profitandloss"] = GetProfitAndLossNew($financejson, $financialstype, $dataPeriods);
            
            
            $data["balancesheetL"] = GetBalanceSheetLiabilities($financejson, $financialstype);
            $data["balancesheetA"] = GetBalanceSheetAssets($financejson, $financialstype);


            

            $planalysis = GetProfitAndLossAnalysisNew($data["profitandloss"], $data["balancesheetdata"]["periods"]);
            //$planalysisnew = GetProfitAndLossAnalysisNew($data["profitandloss"], $data["balancesheetdata"]["periods"]);
            $bsanalysis = GetBalanceSheetAnalysis($data["balancesheetdata"]["lineitems"], $data["balancesheetdata"]["periods"]);
            $financialsummary = GetFinancialSummary($data["balancesheetdata"]["lineitems"], $data["profitandloss"], $data["balancesheetdata"]["periods"]);
            $cfAnalysis = GetCashFlowAnalysis($data["balancesheetdata"]["lineitems"], $data["profitandloss"], $data["balancesheetdata"]["periods"], $bsanalysis);

            
            //$data["planalysisnew"] = array("periods" => $data["balancesheetdata"]["periods"], "lineitems" => $planalysisnew);
            $data["planalysis"] = array("periods" => $data["balancesheetdata"]["periods"], "lineitems" => $planalysis);
            $data["bsanalysis"] = array("periods" => $data["balancesheetdata"]["periods"], "lineitems" => $bsanalysis);
            $data["financialsummary"] = array("periods" => $data["balancesheetdata"]["periods"], "lineitems" => $financialsummary);
            $data["cfanalysis"] = array("periods" => $data["balancesheetdata"]["periods"], "lineitems" => $cfAnalysis);

            $periodYears = array();

            for($i = 0 ; $i < count($dataPeriods); $i++)
            {
                $currentDataPeriod = $dataPeriods[$i];

                $period_type = $currentDataPeriod["ptype"];
                $pmonth = "0";
                $pyear = $currentDataPeriod["year"];

                $periodYears[] = $pyear;
            }

            $periodYears = implode(",", $periodYears);


            $bsaInsertSql = " INSERT INTO `fp_borrower_financials_bsanalysis` (`borrower_id`, `period_type`, `month`, `year`, `result_type`, `equity_share_capital`, `reserve_and_surplus`, `total_equity`, `long_term_borrowings`, `deferred_tax_liability`, `other_liabilities`, `total_non_current_liabilities`, `short_term_borrowings`, `trade_payables`, `other_current_liabilities`, `total_current_liabilities`, `total_equity_and_liabilities`, `property_plant_equipments`, `intangible_assets`, `non_current_assets`, `total_non_current_assets`, `inventories`, `current_investments`, `trade_receivables`, `cash_bank_balance`, `other_current_assets`, `total_assets`, `total_current_assets`) ";
            $bsaInsertSql.= " VALUES ";
            
            
            // For Balance Sheet Analysis
            for($i = 0 ; $i < count($dataPeriods); $i++)
            {
                $currentDataPeriod = $dataPeriods[$i];

                $period_type = $currentDataPeriod["ptype"];
                $pmonth = "0";
                $pyear = $currentDataPeriod["year"];
                $result_type = "";
                
                $equity_share_capital = 0;
                $reserve_and_surplus = 0;
                $total_equity = 0;
                $long_term_borrowings = 0;
                $deferred_tax_liability = 0;
                $other_liabilities = 0;
                $total_non_current_liabilities = 0;
                $short_term_borrowings = 0;
                $trade_payables = 0;
                $other_current_liabilities = 0;
                $total_current_liabilities = 0;
                $total_equity_and_liabilities = 0;

                $property_plant_equipments = 0;
                $intangible_assets = 0;
                $non_current_assets = 0;
                $total_non_current_assets = 0;
                $inventories = 0;
                $current_investments = 0;
                $trade_receivables = 0;
                $cash_bank_balance = 0;
                $other_current_assets = 0;
                $total_current_assets = 0;
                $total_assets = 0;

                foreach($bsanalysis as $lineitem)
                {
                    if($lineitem["values"] != null && count($lineitem["values"]) > 0)
                    {
                        foreach($lineitem["values"] as $valueItem)
                        {
                            if($valueItem["key"] == $currentDataPeriod["key"])
                            {
                                switch($lineitem["label"])
                                {
                                    case "Equity Share Capital":
                                        $equity_share_capital = $valueItem["value"];
                                        break;
                                    case "Reserves and Surplus":
                                        $reserve_and_surplus = $valueItem["value"];
                                        break;
                                    case "Total Equity":
                                        $total_equity = $valueItem["value"];
                                        break;
                                    case "Long Term Borrowings":
                                        $long_term_borrowings = $valueItem["value"];
                                        break;
                                    case "Deferred tax liabilities":
                                        $deferred_tax_liability = $valueItem["value"];
                                        break;
                                    case "Other liabilities":
                                        $other_liabilities = $valueItem["value"];
                                        break;
                                    case "Total non current liabilities":
                                        $total_non_current_liabilities = $valueItem["value"];
                                        break;
                                    case "Short term Borrowings":
                                        $short_term_borrowings = $valueItem["value"];
                                        break;
                                    case "Trade payables":
                                        $trade_payables = $valueItem["value"];
                                        break;
                                    case "Other Current Liabilities":
                                        $other_current_liabilities = $valueItem["value"];
                                        break;
                                    case "Total Current Liabilities":
                                        $total_current_liabilities = $valueItem["value"];
                                        break;
                                    case "Total Equity and liabilities":
                                        $total_equity_and_liabilities = $valueItem["value"];
                                        break;
                                    case "Property, Plant & Equipments":
                                        $property_plant_equipments = $valueItem["value"];
                                        break;
                                    case "Intangible assets":
                                        $intangible_assets = $valueItem["value"];
                                    break;
                                    case "Non current assets":
                                        $non_current_assets = $valueItem["value"];
                                        break;
                                    case "Total Non current assets":
                                        $total_non_current_assets = $valueItem["value"];
                                        break;
                                    case "Inventories":
                                        $inventories = $valueItem["value"];
                                        break;
                                    case "Current Investments":
                                        $current_investments = $valueItem["value"];
                                    break;
                                    case "Trade Receivables":
                                        $trade_receivables = $valueItem["value"];
                                        break;
                                    case "Cash & Bank Balances":
                                        $cash_bank_balance = $valueItem["value"];
                                        break;
                                    case "Other  current assets":
                                        $other_current_assets = $valueItem["value"];
                                        break;
                                    case "Total current assets":
                                        $total_current_assets = $valueItem["value"];
                                        break;
                                    case "Total assets":
                                        $total_assets = $valueItem["value"];
                                        break;
                                }
                                
                            }
                        }
                    }
                    

                }

                $bsaInsertSql.= " ('$borrowerid', '$period_type', '$pmonth', '$pyear', 'audited', '$equity_share_capital', '$reserve_and_surplus', '$total_equity', ";
                $bsaInsertSql.= " '$long_term_borrowings', '$deferred_tax_liability', '$other_liabilities', '$total_non_current_liabilities', ";
                $bsaInsertSql.= " '$short_term_borrowings', '$trade_payables', '$other_current_liabilities', ";
                $bsaInsertSql.= " '$total_current_liabilities', '$total_equity_and_liabilities', '$property_plant_equipments', '$intangible_assets', ";
                $bsaInsertSql.= " '$non_current_assets', '$total_non_current_assets', ";
                $bsaInsertSql.= " '$inventories', '$current_investments', '$trade_receivables', '$cash_bank_balance', '$other_current_assets', '$total_assets', '$total_current_assets') ";

                if($i < count($dataPeriods) - 1)
                {
                    $bsaInsertSql.= " , ";    
                }
                
            }

            

            $this->db->query("Delete from `fp_borrower_financials_bsanalysis` where borrower_id = '$borrowerid' and year in ($periodYears) ");
            $data["bsaInsertSql"] = $bsaInsertSql;
            $this->db->query($bsaInsertSql);

            // For Profit & Loss Analysis

            $plaInsertSql = " INSERT INTO `fp_borrower_financials_planalysis` (`borrower_id`, `period_type`, `month`, `year`, `result_type`, `revenue_from_operations`, `cost_of_material_purchased`, `depriciation_and_amortisation_expense`, `changes_in_inventories`, `employee_benefits_expenses`, `finance_cost`, `other_income`, `total_income`, `other_expenses`, `total_expenses`, `profit_before_tax`, `current_tax`, `deferred_tax`, `profit_after_tax`) ";
            $plaInsertSql.= " VALUES ";
            for($i = 0 ; $i < count($dataPeriods); $i++)
            {
                $currentDataPeriod = $dataPeriods[$i];

                $period_type = $currentDataPeriod["ptype"];
                $pmonth = "0";
                $pyear = $currentDataPeriod["year"];
                $result_type = "";
                
                $revenueFromOperations = 0;
                $costOfMaterialPurchased = 0;
                $depreciationAndAmortisationExpense = 0 ;
                $changesInInventories = 0 ;
                $employeeBenefitsExpense = 0;
                $financeCosts = 0;
                $otherIncome = 0;
                $totalIncome = 0;
                $otherExpenses = 0;
                $totalExpenses = 0;
                $profitBeforeTax = 0;
                $currentTax = 0;
                $deferredTax = 0;
                $profitAfterTax = 0;
                

                foreach($planalysis as $lineitem)
                {
                    if($lineitem["values"] != null && count($lineitem["values"]) > 0)
                    {
                        foreach($lineitem["values"] as $valueItem)
                        {
                            if($valueItem["key"] == $currentDataPeriod["key"])
                            {
                                switch($lineitem["label"])
                                {
                                    case "Revenue from Operations":
                                        $revenueFromOperations = $valueItem["value"];
                                        break;
                                    case "Cost of Material Purchased":
                                        $costOfMaterialPurchased = $valueItem["value"];
                                        break;
                                    case "Depreciation and amortisation expense":
                                        $depreciationAndAmortisationExpense = $valueItem["value"];
                                        break;
                                    case "Changes in inventories of finished and semi-finished goods, stock in trade and work in progress":
                                        $changesInInventories = $valueItem["value"];
                                        break;                                            
                                    case "Employee Benefits Expense":
                                        $employeeBenefitsExpense = $valueItem["value"];
                                        break;
                                    case "Finance Costs":
                                        $financeCosts = $valueItem["value"];
                                        break;
                                    case "Other Income":
                                        $otherIncome = $valueItem["value"];
                                        break;
                                    case "Total Income":
                                        $totalIncome = $valueItem["value"];
                                        break;
                                    case "Other Expenses":
                                        $otherExpenses = $valueItem["value"];
                                        break;
                                    case "Total Expenses":
                                        $totalExpenses = $valueItem["value"];
                                        break;
                                    case "Profit before Tax":
                                        $profitBeforeTax = $valueItem["value"];
                                        break;
                                    case "Current tax":
                                        $currentTax = $valueItem["value"];
                                        break;
                                    case "Deferred tax":
                                        $deferredTax = $valueItem["value"];
                                        break;
                                    case "Profit for the year":
                                        $profitAfterTax = $valueItem["value"];
                                        break;
                                }
                                
                            }
                        }
                    }
                    

                }

                $plaInsertSql.= " ('$borrowerid', '$period_type', '$pmonth', '$pyear', '$result_type', '$revenueFromOperations', '$costOfMaterialPurchased', ";
                $plaInsertSql.= " '$depreciationAndAmortisationExpense', '$changesInInventories', '$employeeBenefitsExpense', '$financeCosts', '$otherIncome', '$totalIncome', ";
                $plaInsertSql.= " '$otherExpenses', '$totalExpenses', '$profitBeforeTax', '$currentTax', '$deferredTax', '$profitAfterTax' ) ";

                if($i < count($dataPeriods) - 1)
                {
                    $plaInsertSql.= " , ";    
                }
                
            }

            $this->db->query("Delete from `fp_borrower_financials_planalysis` where borrower_id = '$borrowerid' and year in ($periodYears) ");
            $data["plaInsertSql"] = $plaInsertSql;
            $this->db->query($plaInsertSql);


            // For Financial Summary

            $fsInsertSql = " INSERT INTO `fp_borrower_financials_summary` (`borrower_id`, `period_type`, `month`, `year`, `result_type`, `net_sales`, `other_income`, `income`, `pbdita`, `pbdita_margin`, `interest`, `depriciation`, `operating_profit_after_interest`,  `income_expense`, `profit_before_tax`, `profit_after_tax`, `net_profit_margin`, `net_cash_accurals`, `fixed_assets_gross`, `fixed_assets_net`,                 `non_current_assets`, `tangible_networth`, `exposure_in_group_company`, `adjusted_tnw`, `long_term_debt`, `short_term_debt`, `working_capital_borrowing`, `total_outside_liabilities`, `ltw_tnw`, `tol_tnw`, `tol_atnw`, `total_current_assets`, `total_current_liabilities`, `net_working_capital`, `current_ratio`, `inventory_holding_period`, `debtor_holding_period`, `creditor_holding_period`, `debt_equity_ratio`, `debt_pbitda_ratio`, `interest_coverage_ratio`, `dscr`) ";
            $fsInsertSql.= " VALUES ";
            for($i = 0 ; $i < count($dataPeriods); $i++)
            {
                $currentDataPeriod = $dataPeriods[$i];

                $period_type = $currentDataPeriod["ptype"];
                $pmonth = "0";
                $pyear = $currentDataPeriod["year"];
                $result_type = "";
                
                $netSales = 0;
                $otherIncome = 0;
                $income = 0;
                $pbdita = 0;
                $pbditaMargin = 0;
                $interest = 0;
                $depreciation = 0;
                $operatingProfitAfterInterest = 0;
                $incomeExpenses = 0;
                $profitBeforeTax = 0;
                $profitAfterTax = 0;
                $netProfitMargin = 0;
                $netCashAccruals = 0;
                $fixedAssetsGross = 0;
                $fixedAssetsNet = 0;
                $nonCurrentAssets = 0;
                $tangibleNetworth = 0;
                $exposureInGroupCo = 0;
                $adjustedTNW = 0;
                $longTermDebt = 0;
                $shortTermDebt = 0;
                $workingCapitalBorrowing = 0;
                $totalOutsideLiabilities = 0;
                $LTD_TNW = 0;
                $TOL_TNW = 0;
                $TOL_ATNW = 0;
                $totalCurrentAssets = 0;
                $totalCurrentLiabilities = 0;
                $netWorkingCapital = 0;
                $currentRatio = 0;
                $inventoryHoldingPeriod = 0;
                $debtorsHoldingPeriod = 0;
                $creditorsHoldingPeriod = 0;
                $debtEquityRatio = 0;
                $debt_PBITDARatio = 0;
                $interestCoverageRatio = 0;
                $dscr = 0;
                

                foreach($financialsummary as $lineitem)
                {
                    if($lineitem["values"] != null && count($lineitem["values"]) > 0)
                    {
                        foreach($lineitem["values"] as $valueItem)
                        {
                            if($valueItem["key"] == $currentDataPeriod["key"])
                            {
                                switch($lineitem["label"])
                                {
                                    case "Net Sales":
                                        $netSales = $valueItem["value"];
                                        break;
                                    case "Other Income":
                                        $otherIncome = $valueItem["value"];
                                        break;
                                    case "Income":
                                        $income = $valueItem["value"];
                                        break;
                                    case "PBDITA":
                                        $pbdita = $valueItem["value"];
                                        break;
                                    case "PBDITA Margin (%)":
                                        $pbditaMargin = $valueItem["value"];
                                        break;
                                    case "Interest":
                                        $interest = $valueItem["value"];
                                        break;
                                    case "Depreciation":
                                        $depreciation = $valueItem["value"];
                                        break;
                                    case "Operating Profit After Interest":
                                        $operatingProfitAfterInterest = $valueItem["value"];
                                        break;
                                    case "Income / Expenses":
                                        $incomeExpenses = $valueItem["value"];
                                        break;
                                    case "Profit Before Tax":
                                        $profitBeforeTax = $valueItem["value"];
                                        break;
                                    case "Profit After Tax":
                                        $profitAfterTax = $valueItem["value"];
                                        break;
                                    case "Net Profit Margin (%)":
                                        $netProfitMargin = $valueItem["value"];
                                        break;
                                    case "Net Cash Accruals (NCA)":
                                        $netCashAccruals = $valueItem["value"];
                                        break;
                                    case "Fixed Assets Gross":
                                        $fixedAssetsGross = $valueItem["value"];
                                        break;
                                    case "Fixed Assets Net":
                                        $fixedAssetsNet = $valueItem["value"];
                                        break;
                                    case "Non Current Assets (Ex. Fixed assets)":
                                        $nonCurrentAssets = $valueItem["value"];
                                        break;
                                    case "Tangible Networth (TNW)":
                                        $tangibleNetworth = $valueItem["value"];
                                        break;
                                    case "Exposure in Group Co./Subsidairies":
                                        $exposureInGroupCo = $valueItem["value"];
                                        break;
                                    case "Adjusted T N W (ATNW)":
                                        $adjustedTNW = $valueItem["value"];
                                        break;
                                    case "Long Term Debt (LTD)":
                                        $longTermDebt = $valueItem["value"];
                                        break;
                                    case "Short Term Debt (LTD)":
                                        $shortTermDebt = $valueItem["value"];
                                        break;
                                    case "Working Capital Borrowing":
                                        $workingCapitalBorrowing = $valueItem["value"];
                                        break;
                                    case "TOTAL OUTSIDE LIABILITIES":
                                        $totalOutsideLiabilities = $valueItem["value"];
                                        break;
                                    case "LTD/TNW":
                                        $LTD_TNW = $valueItem["value"];
                                        break;
                                    case "TOL/TNW":
                                        $TOL_TNW = $valueItem["value"];
                                        break;
                                    case "TOL/ATNW":
                                        $TOL_ATNW = $valueItem["value"];
                                        break;
                                    case "Total Current Assets":
                                        $totalCurrentAssets = $valueItem["value"];
                                        break;
                                    case "Total Current Liabilities":
                                        $totalCurrentLiabilities = $valueItem["value"];
                                        break;
                                    case "Net Working Capital":
                                        $netWorkingCapital = $valueItem["value"];
                                        break;
                                    case "Current Ratio":
                                        $currentRatio = $valueItem["value"];
                                        break;
                                    case "Inventory Holding period (days)":
                                        $inventoryHoldingPeriod = $valueItem["value"];
                                        break;
                                    case "Debtors Holding Period (days)":
                                        $debtorsHoldingPeriod = $valueItem["value"];
                                        break;
                                    case "Creditors Holding Period (days)":
                                        $creditorsHoldingPeriod = $valueItem["value"];
                                        break;
                                    case "Debt Equity Ratio":
                                        $debtEquityRatio = $valueItem["value"];
                                        break;
                                    case "Debt/PBITDA Ratio":
                                        $debt_PBITDARatio = $valueItem["value"];
                                        break;
                                    case "Interest Coverage Ratio":
                                        $interestCoverageRatio = $valueItem["value"];
                                        break;
                                    case "DSCR (Avg/Min)":
                                        $dscr = $valueItem["value"];
                                        break;
                                    
                                }
                                
                            }
                        }
                    }
                    

                }

                $fsInsertSql.= " ('$borrowerid', '$period_type', '$pmonth', '$pyear', '$result_type', '$netSales', '$otherIncome', ";
                $fsInsertSql.= " '$income', '$pbdita', '$pbditaMargin', '$interest', '$depreciation', '$operatingProfitAfterInterest', ";
                $fsInsertSql.= " '$incomeExpenses', '$profitBeforeTax', '$profitAfterTax', '$netProfitMargin', '$netCashAccruals',  ";
                $fsInsertSql.= " '$fixedAssetsGross', '$fixedAssetsNet', '$nonCurrentAssets', '$tangibleNetworth', '$exposureInGroupCo', '$adjustedTNW', ";
                $fsInsertSql.= " '$longTermDebt', '$shortTermDebt', '$workingCapitalBorrowing', '$totalOutsideLiabilities', '$LTD_TNW', '$TOL_TNW', ";
                $fsInsertSql.= " '$TOL_ATNW', '$totalCurrentAssets', '$totalCurrentLiabilities', '$netWorkingCapital', '$currentRatio', '$inventoryHoldingPeriod', ";
                $fsInsertSql.= " '$debtorsHoldingPeriod', '$creditorsHoldingPeriod', '$debtEquityRatio', '$debt_PBITDARatio ', '$interestCoverageRatio', '$dscr' ) ";
                

                if($i < count($dataPeriods) - 1)
                {
                    $fsInsertSql.= " , ";    
                }
                
            }

            $this->db->query("Delete from `fp_borrower_financials_summary` where borrower_id = '$borrowerid' and year in ($periodYears) ");

            $data["fsInsertSql"] = $fsInsertSql;
            $this->db->query($fsInsertSql);

            //echo $fsInsertSql;



             


            $cfaInsertSql = " INSERT INTO `fp_borrower_financials_cfanalysis` (`borrower_id`, `period_type`, `month`, `year`, `result_type`, `net_profit_before_taxation`, `depreciation`, `dividend_income`, `interest_expense`, `interest_received`, `profit_loss_on_sale_of_fixed_assets`, `foreign_exchange_gains_loss`, `extraordinary_income_expense`, `operating_profit_before_wc_changes`, `changes_in_current_assets`, `changes_in_current_liabilities`, `net_cash_from_operating_activities`, `net_cash_from_investing_activities`, `net_cash_from_financing_activities`, `net_increase_in_cash_bank_balance`, `cash_bank_balance_in_begining`, `cash_bank_balance_at_end`) ";
            $cfaInsertSql.= " VALUES ";
            
            
            // For Balance Sheet Analysis
            for($i = 0 ; $i < count($dataPeriods); $i++)
            {
                $currentDataPeriod = $dataPeriods[$i];

                $period_type = $currentDataPeriod["ptype"];
                $pmonth = "0";
                $pyear = $currentDataPeriod["year"];
                $result_type = "";
                

                $net_profit_before_taxation = 0;
                $depriciation = 0;
                $dividend_income = 0;
                $interest_expenses = 0;
                $interest_received = 0;
                $pl_on_sale_of_fa = 0;
                $forex_gain_loss = 0;
                $ex_income_expenses = 0;
                $op_before_wc_changes = 0;

                $changes_in_current_assets = 0;
                $changes_in_current_liabilities = 0;
                $net_cash_from_operating_activities = 0;
                $net_cash_from_investing_activities = 0;
                $net_cash_from_financing_activities = 0;
                $net_increase_in_cash_bank_balance = 0;
                $cash_bank_balance_in_begining = 0;
                $casg_bank_balance_at_end = 0;

                foreach($cfAnalysis as $lineitem)
                {
                    if($lineitem["values"] != null && count($lineitem["values"]) > 0)
                    {
                        foreach($lineitem["values"] as $valueItem)
                        {
                            if($valueItem["key"] == $currentDataPeriod["key"])
                            {
                                switch($lineitem["label"])
                                {
                                    case "Net profit before taxation":
                                        $net_profit_before_taxation = $valueItem["value"];
                                        break;
                                    case "Depreciation":
                                        $depriciation = $valueItem["value"];
                                        break;
                                    case "Dividend Income":
                                        $dividend_income = $valueItem["value"];
                                        break;
                                    case "Interest Expenses":
                                        $interest_expenses = $valueItem["value"];
                                        break;
                                    case "Interest Income":
                                        $interest_received = $valueItem["value"];
                                        break;
                                    case "Profit / Loss on sale of fixed assets / investments":
                                        $pl_on_sale_of_fa = $valueItem["value"];
                                        break;
                                    case "Foreign exchange gain/loss":
                                        $forex_gain_loss = $valueItem["value"];
                                        break;
                                    case "Extraordinary income / expenses":
                                        $ex_income_expenses = $valueItem["value"];
                                        break;
                                    case "Operating profit before working capital changes":
                                        $op_before_wc_changes = $valueItem["value"];
                                        break;
                                    case "Change in current assets":
                                        $changes_in_current_assets = $valueItem["value"];
                                        break;
                                    case "Change in current liabilities":
                                        $changes_in_current_liabilities = $valueItem["value"];
                                        break;
                                }
                                
                            }
                        }
                    }
                    

                }

                $cfaInsertSql.= " ('$borrowerid', '$period_type', '$pmonth', '$pyear', '$result_type', '$net_profit_before_taxation', ";
                $cfaInsertSql.= " '$depriciation', '$dividend_income', '$interest_expenses', '$interest_received', '$pl_on_sale_of_fa', '$forex_gain_loss', "; 
                $cfaInsertSql.= " '$ex_income_expenses', '$op_before_wc_changes', '$changes_in_current_assets', '$changes_in_current_liabilities', '$net_cash_from_operating_activities', ";
                $cfaInsertSql.= " '$net_cash_from_investing_activities', '$net_cash_from_financing_activities', '$net_increase_in_cash_bank_balance', '$cash_bank_balance_in_begining', '$casg_bank_balance_at_end') ";
                

                if($i < count($dataPeriods) - 1)
                {
                    $cfaInsertSql.= " , ";    
                }
                
            }

            $this->db->query("Delete from `fp_borrower_financials_cfanalysis` where borrower_id = '$borrowerid' and year in ($periodYears) ");
            $data["cfaInsertSql"] = $cfaInsertSql;
            $this->db->query($cfaInsertSql);



            $resp = array('status' => 200,'message' =>  'Success','data' => $data, 'sql' => $plaInsertSql);
            json_output(200,$resp);
        
       
    
        }    // END OF ANALYZEDATA_old

        

     






}

