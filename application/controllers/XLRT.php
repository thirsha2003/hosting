<?php
header('Access-Control-Allow-Origin: *'); //for allow any domain, insecure
header('Access-Control-Allow-Headers: *'); //for allow any headers, insecure
header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE'); //method allowed
header("HTTP/1.1 200 OK");
defined('BASEPATH') OR exit('No direct script access allowed');
include APPPATH . 'ThirdParty/sendgrid-php/sendgrid-php.php';
include APPPATH . 'ThirdParty/MTalkz.php';
include APPPATH . 'libraries/Femail.php';

class XLRT extends CI_Controller 
{

	public function __construct()
    {
		parent::__construct();
		$this->load->helper('json_output');
        $this->load->helper('finance');
        $this->load->library('encryption');
    }

     function PostProcessingStatus()
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
    }

    function authXLRT()
    {
        $url = "https://finnupuat.xlrt.ai/auth-api/gatekeeper/login";

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $headers = array(
            "Accept: */*",
            "Content-Type: application/json",
            "Access-Control-Expose-Headers:X-Custom-header"
        );
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $data = <<<DATA
                {
                "password": xlrt12809,
                "userid": "finnupuat1"
                }
                DATA;

        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

        $resp = curl_exec($curl);
        $headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $headerStr = substr($resp, 0, $headerSize);
        $bodyStr = substr($resp, $headerSize);

        // convert headers to array
        $headers = $this->headersToArray($headerStr);
        curl_close($curl);
        return $headers['Authorization'];
    }

    function getExtractionResponse($dmsCode, $accesstoken)
    {
        $url = 'https://finnupuat.xlrt.ai/fst-api/financialdocs/' + $dmsCode + '/extraction';

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $headers = array(
            "Accept: */*",
            "Authorization:" . $accesstoken
        );
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $resp = curl_exec($curl);
    }

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
    }
 
        
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
    }
            
        
        
    function GetBalanceSheetData11()
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
    function GetBalanceSheetData()
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

                
                return json_output(200,array('status' => 200,'data' => array(
                    "financialsummary"=> $financialSummary,
                    "bsanalysis"=> $bsAnalysis,
                    "planalysis"=> $plAnalysis
                    ) ));
			}
			else
			{
				return json_output(200,array('status' => 400,'Message' => "Invalid Authentication"));
			}
        }
	}
        
    function AnalyzeData()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $method = $_SERVER['REQUEST_METHOD'];
        if($method != 'POST')
        {
            json_output(400,array('status' => 400,'message' => 'Bad request.'));
        }
        else
        {
            $check_auth_user = $this->login->check_auth_user();
			if($check_auth_user == true)
            {
                $params = json_decode(file_get_contents('php://input'), TRUE);
                $borrowerid = $params['data']['id'];

                $response['status']=200;
                $respStatus = $response['status'];
                
                $params 	= json_decode(file_get_contents('php://input'), TRUE);
                $param	= json_decode(file_get_contents('php://input'), FALSE);
                
                $financejson 	= $param->jsondata;    //  XLRT response data 
                $financialstype = "STA";

                $data["balancesheetdata"] = GetBalanceSheetData($financejson, $financialstype);
                $data["profitandloss"] = GetProfitAndLoss($financejson, $financialstype);
                
                
                $data["balancesheetL"] = GetBalanceSheetLiabilities($financejson, $financialstype);
                $data["balancesheetA"] = GetBalanceSheetAssets($financejson, $financialstype);


                $dataPeriods = $data["balancesheetdata"]["periods"];

                $planalysis = GetProfitAndLossAnalysis($data["profitandloss"], $data["balancesheetdata"]["periods"]);
                $bsanalysis = GetBalanceSheetAnalysis($data["balancesheetdata"]["lineitems"], $data["balancesheetdata"]["periods"]);
                $financialsummary = GetFinancialSummary($data["balancesheetdata"]["lineitems"], $data["profitandloss"], $data["balancesheetdata"]["periods"]);
                $cfAnalysis = GetCashFlowAnalysis($data["balancesheetdata"]["lineitems"], $data["profitandloss"], $data["balancesheetdata"]["periods"]);

                $data["planalysis"] = array("periods" => $planalysis, "lineitems" => $planalysis);
                $data["bsanalysis"] = array("periods" => $data["balancesheetdata"]["periods"], "lineitems" => $bsanalysis);
                $data["financialsummary"] = array("periods" => $data["balancesheetdata"]["periods"], "lineitems" => $financialsummary);
                $data["cfanalysis"] = array("periods" => $data["balancesheetdata"]["periods"], "lineitems" => $cfAnalysis);


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

                $this->db->query("Delete from `fp_borrower_financials_bsanalysis` where borrower_id = '$borrowerid' ");
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

                $this->db->query("Delete from `fp_borrower_financials_planalysis` where borrower_id = '$borrowerid' ");
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

                $this->db->query("Delete from `fp_borrower_financials_summary` where borrower_id = '$borrowerid' ");

                $data["fsInsertSql"] = $fsInsertSql;
                $this->db->query($fsInsertSql);
                

                $resp = array('status' => 200,'message' =>  'Success','data' => $data);
                json_output(200,$resp);
			}
			else
			{
				return json_output(200,array('status' => 400,'Message' => "Invalid Authentication"));
			}
        }
	}
        
}

