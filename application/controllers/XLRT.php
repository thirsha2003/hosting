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

              print_r( $response_xlrt);
              curl_close($ch);
              $xlrt_response= json_decode($response_xlrt);

              $this->AnalyzeData($borrowerid,$xlrt_response);


              //  AWS CODE START 

            // $projson = json_encode($response_xlrt);
            // $foldername ="XLRT/";
            // $aws->aws_s3bucket_xlrt($borrowerid,$dmscode,$foldername,$projson);

            // AWS END CODE

          

              

            }  // End of XlrtgetExtractionResponse
            


   
             public function AnalyzeData_old($borrowerid,$xlrt_response )
            {
                
                        
                    
                 
                        $financejson 	= $xlrt_response;
                        $financialstype = "STA";
        
                        $data["balancesheetdata"] = GetBalanceSheetData($financejson, $financialstype);

                        

                        $data["profitandloss"] = GetProfitAndLoss($financejson, $financialstype);
                        
                        

                        $data["balancesheetL"] = GetBalanceSheetLiabilities($financejson, $financialstype);
                        $data["balancesheetA"] = GetBalanceSheetAssets($financejson, $financialstype);
        
        
                        $dataPeriods = $data["balancesheetdata"]["periods"];
        
                        $planalysis = GetProfitAndLossAnalysis($data["profitandloss"], $data["balancesheetdata"]["periods"]);
                        $bsanalysis = GetBalanceSheetAnalysis($data["balancesheetdata"]["lineitems"], $data["balancesheetdata"]["periods"]);
                        $financialsummary = GetFinancialSummary($data["balancesheetdata"]["lineitems"], $data["profitandloss"], $data["balancesheetdata"]["periods"]);
                        $cfAnalysis = GetCashFlowAnalysis($data["balancesheetdata"]["lineitems"], $data["profitandloss"], $data["balancesheetdata"]["periods"], $bsanalysis);
        
                        $data["planalysis"] = array("periods" => $planalysis, "lineitems" => $planalysis);
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
                                                case "Profit Before Tax":
                                                    $profitBeforeTax = $valueItem["value"];
                                                    break;
                                                case "Current tax":
                                                    $currentTax = $valueItem["value"];
                                                    break;
                                                case "Deferred tax":
                                                    $deferredTax = $valueItem["value"];
                                                    break;
                                                case "Profit After Tax":
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
        
                        $fsInsertSql = " INSERT INTO `fp_borrower_financials_summary` (`borrower_id`, `period_type`, `month`, `year`, `result_type`, `net_sales`, `other_income`, `income`, `pbdita`, `pbdita_margin`, `interest`, `depriciation`, `operating_profit_after_interest`,  `income_expense`, `profit_before_tax`, `profit_after_tax`, `net_profit_margin`, `net_cash_accurals`, `fixed_assets_gross`, `fixed_assets_net`,                 `non_current_assets`, `tangible_networth`, `exposure_in_group_company`, `adjusted_tnw`, `long_term_debt`, `short_term_debt`, `working_capital_borrowing`, `total_outside_liabilities`, `ltw_tnw`, `tol_tnw`, `tol_atnw`, `total_current_assets`, `total_current_liabilities`, `net_working_capital`, `current_ratio`, `inventory_holding_period`, `debtor_holding_period`, `creditor_holding_period`, `debt_equity_ratio`, `debt_pbitda_ratio`, `interest_coverage_ratio`) ";
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
                            $fsInsertSql.= " '$debtorsHoldingPeriod', '$creditorsHoldingPeriod', '$debtEquityRatio', '$debt_PBITDARatio ', '$interestCoverageRatio' ) ";
                            
        
                            if($i < count($dataPeriods) - 1)
                            {
                                $fsInsertSql.= " , ";    
                            }
                            
                        }
        
                        $this->db->query("Delete from `fp_borrower_financials_summary` where borrower_id = '$borrowerid' and year in ($periodYears) ");
        
                        $data["fsInsertSql"] = $fsInsertSql;
                        $this->db->query($fsInsertSql);
        
        
        
                         
        
        
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
        
        
        
                        $resp = array('status' => 200,'message' =>  'Success','data' => $data, 'sql' => "Delete from `fp_borrower_financials_cfanalysis` where borrower_id = '$borrowerid' and year in ($periodYears) ");
                        json_output(200,$resp);
                    


                    
                
            }  // AnalyzeData_old 


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
            //   $downloadUrl = "http://localhost/fin/api/";   ///  CHAGING THIS URL IN LIVE   
              $downloadUrl = "https://app.finnup.in/api/";   ///   THIS URL IN LIVE   
              $data = null;
              $check_auth_user = $this->login->check_auth_user();
  
              if($check_auth_user == true)
              {
                  $params = json_decode(file_get_contents('php://input'), TRUE);
                  $borrowerid = $params['data']['id'];
                  $fintype = $params['fintype'];
  
                  $unit = "lakh";
  
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
              
  
              $sql = " SELECT year, period_type, result_type FROM `fp_borrower_financials_summary` where borrower_id = '$borrowerid' order by year desc ";
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
  
                  $sql = "SELECT * from fp_borrower_financials_summary where borrower_id = '$borrowerid' " ;
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
              
  
              $sql = " SELECT year, period_type, result_type FROM `fp_borrower_financials_bsanalysis` where borrower_id = '$borrowerid' order by year desc ";
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
  
                  $sql = "SELECT * from fp_borrower_financials_bsanalysis where borrower_id = '$borrowerid' " ;
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
              
  
              $sql = " SELECT year, period_type, result_type FROM `fp_borrower_financials_planalysis` where borrower_id = '$borrowerid' order by year desc ";
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
  
                  $sql = "SELECT * from fp_borrower_financials_planalysis where borrower_id = '$borrowerid' " ;
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
              
  
              $sql = " SELECT year, period_type, result_type FROM `fp_borrower_financials_cfanalysis` where borrower_id = '$borrowerid' order by year desc ";
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
  
                      $sql = "SELECT * from fp_borrower_financials_cfanalysis where borrower_id = '$borrowerid' " ;
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
                  
  
                  $sql = " SELECT year, period_type, result_type FROM `fp_borrower_financials_summary` where borrower_id = '$borrowerid' order by year asc ";
                  
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
  
                      
  
                      $num_results = sizeof($fsDbData);
  
                      $results = GetFinancialSummaryFromDB($fsPeriods, $fsDbData, $unit);
  
                      
  
                      $financialSummary = array("periods" => $fsPeriods, "lineitems"=> $results);
                  }
  
                  $sql = " SELECT year, period_type, result_type FROM `fp_borrower_financials_bsanalysis` where borrower_id = '$borrowerid' order by year asc ";
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
  
  
                  $sql = " SELECT year, period_type, result_type FROM `fp_borrower_financials_planalysis` where borrower_id = '$borrowerid' order by year asc ";
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
  
  
                  $sql = " SELECT year, period_type, result_type FROM `fp_borrower_financials_cfanalysis` where borrower_id = '$borrowerid' order by year asc ";
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





       public   function AnalyzeData($borrowerid,$xlrt_response){
    

  
          
            
            $financejson 	= $xlrt_response;
            $financialstype = "STA";

            

            $data["balancesheetdata"] = GetBalanceSheetData($financejson, $financialstype);

            $dataPeriods = $data["balancesheetdata"]["periods"];


            $data["profitandloss"] = GetProfitAndLoss($financejson, $financialstype);

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
        
       
    
        }    // END OF ANALYZEDATA

        
}

