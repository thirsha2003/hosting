<?php
header('Access-Control-Allow-Origin: *'); //for allow any domain, insecure
header('Access-Control-Allow-Headers: *'); //for allow any headers, insecure
header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE'); //method allowed
header("HTTP/1.1 200 OK");
defined('BASEPATH') OR exit('No direct script access allowed');
include APPPATH . 'ThirdParty/sendgrid-php/sendgrid-php.php';

class XLRT extends CI_Controller 
{

	public function __construct()
    {
		parent::__construct();
		$this->load->helper('json_output');
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
                                    $type       = 'STA';
                                    
                $plData = null;
        
                if(isset($jsonData->status) && $jsonData->status == true && isset($jsonData->body) && is_array($jsonData->body) && count($jsonData->body) > 0)
                {
                    foreach($jsonData->body as $bodydata)
                    {
                        if($bodydata->fintype == $type  || count($jsonData->body) == 1)
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
        
                                    $plData["periods"][] = $_period;
                                }
                            }
        
                            if(isset($body->components) && $body->components != null && count($body->components) > 0)
                            {
                                foreach ($body->components as $component) {
        
                                    if($component->compcode == "IS")
                                    {
                                        if(isset($component->items) && $component->items != null && count($component->items) > 0)
                                        {
                                            foreach ($component->items as $finitem) {
        
                                                $_lineitem = array(
                                                    "componenttype" => $finitem->classification->componenttype,
                                                    "classname" => $finitem->classification->classname,
                                                    "subclassname" => $finitem->classification->subclassname,
                                                    "label" => $finitem->label,
                                                    "values" => $finitem->values,
                                                    "calculatedvalues" => $finitem->calculatedvalues,
                                                );
                    
                                                   $plData["lineitems"][] = $_lineitem;
        
                                            }
                                        }
                                       
                                    }
        
                                }
                            }
                            
        
                        }
                    }
                }
        
                // return $plData;
				$resp = array('status' => 200,'message' =>  'Success','data' => $plData);
                json_output(200,$resp);
                }else{

                }
	}
        
          
    function GetProfitAndLoss($jsonData, $type)
    {
        
                $profitAndLoss = null;
                $incomestatement = GetIncomeStatementData($jsonData, $type);
        
                if(isset($incomestatement) )
                {
                
                    if(isset($incomestatement["lineitems"]) && count($incomestatement["lineitems"]) > 0)
                    {
                        $itemIndex = 0 ;
                        $subTotal = array();
        
                        $subTotalValues = array();
                        $netSalesValues = array();
                        $totalOperatingIncomeValues = array();
                        $costOfSalesSubTotalValues = array();
                        $totalCostOfProductionValues = array();
                        $totalCostOfSalesValues = array();
                        $adminAndSellingExpValues = array();
                        $operatingProfitBeforeInterestValues = array();
                        $totalInterestValues = array();
                        $operatingProfitAfterInterestValues = array();
                        $totalNonOperatingIncome = array();
                        $totalNonOperatingExpenses = array();
                        $netOfNonOperatingIncomeExpenses = array();
                        $profitBeforeTax = array();
                        $provisionForTaxation = array();
        
                        foreach($incomestatement["lineitems"] as $lineitem)
                        {
                            $itemIndex++;
                            $_lineitem = null;
        
                            if((strpos($lineitem["classname"], "Revenue") !== false || $lineitem["componenttype"] == "IS") && $lineitem["label"] != "")
                            {
                                //$_lineitem  = array("lineitem" => $lineitem["label"], "values" => array());
                               
                                if(isset($incomestatement["periods"]) && count($incomestatement["periods"]) > 0)
                                {
                                    foreach($incomestatement["periods"] as $period)
                                    {
                            
                                        if(isset($lineitem["values"]) && count($lineitem["values"]) > 0)
                                        {
                                            $_lineitem[$period["key"]] = 0;
                                            
                                            foreach ($lineitem["values"] as $itemvalue) {
                                                if($itemvalue->periodkey == $period["key"])
                                                {
                                                    if($itemIndex == 4 || $itemIndex == 20 || $itemIndex == 22)
                                                    {
                                                        
                                                        //$_lineitem["values"][] = array("year"=> $period["key"], "value" => -$itemvalue->value);
                                                        $_lineitem[$period["key"]] = -$itemvalue->value;
        
                                                        $subTotal[] = array($period["key"] => -$itemvalue->value);
                                                    }
                                                    else{
                                                        //$_lineitem["values"][] = array("year"=> $period["key"], "value" => $itemvalue->value);
                                                        $_lineitem[$period["key"]] = $itemvalue->value;
                                                        $subTotal[] = array($period["key"] => $itemvalue->value);
                                                    }
        
                                                }
                                            }
                                        }
                                        else{
                                            $_lineitem[$period["key"]] = 0;
                                        }
                                    }
                                }
        
                                $profitAndLoss[$lineitem["label"]] = $_lineitem;
        
        
                                    if($itemIndex == 3)
                                    {
                                        //$lineitem  = array("lineitem" => "Sub Total", "values" => array());
                                        $lineitem  = null;
                    
                                        if(isset($incomestatement["periods"]) && count($incomestatement["periods"]) > 0)
                                        {
                                            foreach($incomestatement["periods"] as $period)
                                            {
                                                $valueTotal = 0;
        
                                                foreach($subTotal as $subTotalItem)
                                                {
                                                    foreach($subTotalItem as $key => $val)
                                                    {
                                                        if($key == $period["key"])
                                                        {
                                                            $valueTotal+=$val;
                                                        }
                                                    }
                                                }
                                                $subTotalValues[] = array($period["key"] => $valueTotal);
        
                                                
                                                $lineitem[$period["key"]] = $valueTotal;
                                               // $lineitem["values"][] = array("year"=> $period["key"], "value" => $valueTotal);
                                       
                                            }
                                            $subTotal = array();
                                        }
        
                                        $profitAndLoss["Sales Sub Total"] = $lineitem;
                                
                                    }
                                    else if($itemIndex == 4)
                                    {
                                        //$lineitem  = array("lineitem" => "Net Sales", "values" => array());
                                        $lineitem  = null;
                    
                                        if(isset($incomestatement["periods"]) && count($incomestatement["periods"]) > 0)
                                        {
                                            foreach($incomestatement["periods"] as $period)
                                            {
                                                $valueTotal = 0;
        
                                                foreach($subTotal as $subTotalItem)
                                                {
                                                    foreach($subTotalItem as $key => $val)
                                                    {
                                                        if($key == $period["key"])
                                                        {
                                                            $valueTotal+=$val;
                                                        }
                                                    }
                                                }
        
                                                foreach($subTotalValues as $subTotalValuesItem)
                                                {
                                                    foreach($subTotalValuesItem as $key => $val)
                                                    {
                                                        if($key == $period["key"])
                                                        {
                                                            $valueTotal+=$val;
                                                        }
                                                    }
                                                }
        
                                                $netSalesValues[] = array($period["key"] => $valueTotal);
        
                                                //$lineitem["values"][] = array("year"=> $period["key"], "value" => $valueTotal);
                                                $lineitem[$period["key"]] = $valueTotal;
        
                                            }
                                            $subTotal = array();
        
                                        }
        
                                        $profitAndLoss["Net Sales"] = $lineitem;
        
                                    }
                                    else if ($itemIndex == 7)
                                    {
                                        //$lineitem  = array("lineitem" => "Total Operating Income", "values" => array());
                                        $lineitem  = null;
                    
                                        if(isset($incomestatement["periods"]) && count($incomestatement["periods"]) > 0)
                                        {
                                            foreach($incomestatement["periods"] as $period)
                                            {
                                                $valueTotal = 0;
        
                                                foreach($subTotal as $subTotalItem)
                                                {
                                                    foreach($subTotalItem as $key => $val)
                                                    {
                                                        if($key == $period["key"])
                                                        {
                                                            $valueTotal+=$val;
                                                        }
                                                    }
                                                }
        
                                                foreach($netSalesValues as $netSalesValueItem)
                                                {
                                                    foreach($netSalesValueItem as $key => $val)
                                                    {
                                                        if($key == $period["key"])
                                                        {
                                                            $valueTotal+=$val;
                                                        }
                                                    }
                                                }
        
                                                $totalOperatingIncomeValues[] = array($period["key"] => $valueTotal);
                                                //$lineitem["values"][] = array("year"=> $period["key"], "value" => $valueTotal);
        
                                                $lineitem[$period["key"]] = $valueTotal;
        
                                            }
                                            $subTotal = array();
                                        }
        
                                        $profitAndLoss["Total Operating Income"] = $lineitem;
                        
                                    }
                                    else if ($itemIndex == 18)
                                    {
                                        //$lineitem  = array("lineitem" => "Sub Total", "values" => array());
                                        $lineitem  = null;
                    
                                        if(isset($incomestatement["periods"]) && count($incomestatement["periods"]) > 0)
                                        {
                                            foreach($incomestatement["periods"] as $period)
                                            {
                                                $valueTotal = 0;
        
                                                foreach($subTotal as $subTotalItem)
                                                {
                                                    foreach($subTotalItem as $key => $val)
                                                    {
                                                        if($key == $period["key"])
                                                        {
                                                            $valueTotal+=$val;
                                                        }
                                                    }
                                                }
        
                                                $costOfSalesSubTotalValues[] = array($period["key"] => $valueTotal);
        
                                                $lineitem[$period["key"]] = $valueTotal;
        
                                            }
        
                                            $subTotal = array();
                                        }
        
                                        $profitAndLoss["Cost of Sales - Sub Total"] = $lineitem;
                                    }
                                    else if ($itemIndex == 20)
                                    {
                                        //$lineitem  = array("lineitem" => "Total Cost of Production", "values" => array());
                                        $lineitem  = null;
                    
                                        if(isset($incomestatement["periods"]) && count($incomestatement["periods"]) > 0)
                                        {
                                            foreach($incomestatement["periods"] as $period)
                                            {
                                                $valueTotal = 0;
        
                                                foreach($subTotal as $subTotalItem)
                                                {
                                                    foreach($subTotalItem as $key => $val)
                                                    {
                                                        if($key == $period["key"])
                                                        {
                                                            $valueTotal+=$val;
                                                        }
                                                    }
                                                }
        
                                                
                                                foreach($costOfSalesSubTotalValues as $costOfSalesSubTotalValueItem)
                                                {
                                                    foreach($costOfSalesSubTotalValueItem as $key => $val)
                                                    {
                                                        if($key == $period["key"])
                                                        {
                                                            $valueTotal+=$val;
                                                        }
                                                    }
                                                }
        
                                                $totalCostOfProductionValues[] = array($period["key"] => $valueTotal);
                                                $lineitem[$period["key"]] = $valueTotal;
        
                                            }
                                            $subTotal = array();
                                        }
        
                                        $profitAndLoss["Total Cost of Production"] = $lineitem;
                        
                                    }
                                    else if ($itemIndex == 22)
                                    {
                                        //$lineitem  = array("lineitem" => "Total Cost of Sales", "values" => array());
                                        $lineitem  = null;
                    
                                        if(isset($incomestatement["periods"]) && count($incomestatement["periods"]) > 0)
                                        {
                                            foreach($incomestatement["periods"] as $period)
                                            {
                                                $valueTotal = 0;
        
                                                foreach($subTotal as $subTotalItem)
                                                {
                                                    foreach($subTotalItem as $key => $val)
                                                    {
                                                        if($key == $period["key"])
                                                        {
                                                            $valueTotal+=$val;
                                                        }
                                                    }
                                                }
        
                                                
                                                foreach($totalCostOfProductionValues as $totalCostOfProductionValueItem)
                                                {
                                                    foreach($totalCostOfProductionValueItem as $key => $val)
                                                    {
                                                        if($key == $period["key"])
                                                        {
                                                            $valueTotal+=$val;
                                                        }
                                                    }
                                                }
        
                                                $totalCostOfSalesValues[] = array($period["key"] => $valueTotal);
                                                $lineitem[$period["key"]] = $valueTotal;
        
                                            }
                                            $subTotal = array();
                                        }
        
                                        $profitAndLoss["Total Cost of Sales"] = $lineitem;
                        
                                    }
                                    else if ($itemIndex == 26)
                                    {
                                        //$lineitem  = array("lineitem" => "Administrative & Selling Sub Total", "values" => array());
                                        $lineitem  = null;
                    
                                        if(isset($incomestatement["periods"]) && count($incomestatement["periods"]) > 0)
                                        {
                                            foreach($incomestatement["periods"] as $period)
                                            {
                                                $valueTotal = 0;
        
                                                foreach($subTotal as $subTotalItem)
                                                {
                                                    foreach($subTotalItem as $key => $val)
                                                    {
                                                        if($key == $period["key"])
                                                        {
                                                            $valueTotal+=$val;
                                                        }
                                                    }
                                                }
                                                $adminAndSellingExpValues[] = array($period["key"] => $valueTotal);
                                                $lineitem[$period["key"]] = $valueTotal;
                                            }
                                            $subTotal = array();
                                        }
        
                                        $profitAndLoss["Administrative & Selling Sub Total"] = $lineitem;
        
                                        //$lineitem  = array("lineitem" => "Operating Profit before Interest", "values" => array());
                                        $lineitem  = null;
                            
                            
                                        if(isset($incomestatement["periods"]) && count($incomestatement["periods"]) > 0)
                                        {
                                            foreach($incomestatement["periods"] as $period)
                                            {
                                                $valueTotal = 0;
        
                                                foreach($totalOperatingIncomeValues as $totalOperatingIncomeValueItem)
                                                {
                                                    foreach($totalOperatingIncomeValueItem as $key => $val)
                                                    {
                                                        if($key == $period["key"])
                                                        {
                                                            $valueTotal+=$val;
                                                        }
                                                    }
                                                }
        
                                                foreach($totalCostOfSalesValues as $totalCostOfSalesValueItem)
                                                {
                                                    foreach($totalCostOfSalesValueItem as $key => $val)
                                                    {
                                                        if($key == $period["key"])
                                                        {
                                                            $valueTotal-=$val;
                                                        }
                                                    }
                                                }
        
                                                foreach($adminAndSellingExpValues as $adminAndSellingExpValueItem)
                                                {
                                                    foreach($adminAndSellingExpValueItem as $key => $val)
                                                    {
                                                        if($key == $period["key"])
                                                        {
                                                            $valueTotal-=$val;
                                                        }
                                                    }
                                                }
        
                                                
        
                                                $operatingProfitBeforeInterestValues[] = array($period["key"] => $valueTotal);
                                                $lineitem[$period["key"]] = $valueTotal;
        
                                    
        
                                            }
                                            $subTotal = array();
                                        }
        
                                        $profitAndLoss["Operating Profit before Interest"] = $lineitem;
                            
                                    }
                                    else if ($itemIndex == 30)
                                    {
                                        //$lineitem  = array("lineitem" => "Total Interest", "values" => array());
                                        $lineitem = null;
                    
                                        if(isset($incomestatement["periods"]) && count($incomestatement["periods"]) > 0)
                                        {
                                            foreach($incomestatement["periods"] as $period)
                                            {
                                                $valueTotal = 0;
        
                                                foreach($subTotal as $subTotalItem)
                                                {
                                                    foreach($subTotalItem as $key => $val)
                                                    {
                                                        if($key == $period["key"])
                                                        {
                                                            $valueTotal+=$val;
                                                        }
                                                    }
                                                }
        
                                                $totalInterestValues[] = array($period["key"] => $valueTotal);
                                                $lineitem[$period["key"]] = $valueTotal;
        
                                            }
                                            $subTotal = array();
                                        }
                                        $profitAndLoss["Total Interest"] = $lineitem;
        
                                        //$lineitem  = array("lineitem" => "Operating Profit after Interest", "values" => array());
                                        $lineitem  = null; 
                                    
                                        if(isset($incomestatement["periods"]) && count($incomestatement["periods"]) > 0)
                                        {
                                            foreach($incomestatement["periods"] as $period)
                                            {
                                                $valueTotal = 0;
        
                                                foreach($operatingProfitBeforeInterestValues as $operatingProfitBeforeInterestValueItem)
                                                {
                                                    foreach($operatingProfitBeforeInterestValueItem as $key => $val)
                                                    {
                                                        if($key == $period["key"])
                                                        {
                                                            $valueTotal+=$val;
                                                        }
                                                    }
                                                }
        
                                                foreach($totalInterestValues as $totalInterestValueItem)
                                                {
                                                    foreach($totalInterestValueItem as $key => $val)
                                                    {
                                                        if($key == $period["key"])
                                                        {
                                                            $valueTotal-=$val;
                                                        }
                                                    }
                                                }
                                                $operatingProfitAfterInterestValues[] = array($period["key"] => $valueTotal);
                                                $lineitem[$period["key"]] = $valueTotal;
        
                                        
                                            }
                                            $subTotal = array();
                                        }
        
                                        $profitAndLoss["Operating Profit after Interest"] = $lineitem;
        
                                    }
                                    else if ($itemIndex == 37)
                                    {
                                        //$lineitem  = array("lineitem" => "Total non-operating Income", "values" => array());
                                        $lineitem  = null;
                    
                                        if(isset($incomestatement["periods"]) && count($incomestatement["periods"]) > 0)
                                        {
                                            foreach($incomestatement["periods"] as $period)
                                            {
                                                $valueTotal = 0;
        
                                                foreach($subTotal as $subTotalItem)
                                                {
                                                    foreach($subTotalItem as $key => $val)
                                                    {
                                                        if($key == $period["key"])
                                                        {
                                                            $valueTotal+=$val;
                                                        }
                                                    }
                                                }
                                                $totalNonOperatingIncome[] = array($period["key"] => $valueTotal);
                                                $lineitem[$period["key"]] = $valueTotal;
                                            }
                                            
                                            $subTotal = array();
                                        }
                                        
                                        $profitAndLoss["Total non-operating Income"] = $lineitem;
                    
                                    }
                                    else if ($itemIndex == 42)
                                    {
                                        //$lineitem  = array("lineitem" => "", "values" => array());
                                        $lineitem = null;
                    
                                        if(isset($incomestatement["periods"]) && count($incomestatement["periods"]) > 0)
                                        {
                                            foreach($incomestatement["periods"] as $period)
                                            {
                                                $valueTotal = 0;
        
                                                foreach($subTotal as $subTotalItem)
                                                {
                                                    foreach($subTotalItem as $key => $val)
                                                    {
                                                        if($key == $period["key"])
                                                        {
                                                            $valueTotal+=$val;
                                                        }
                                                    }
                                                }
        
                                                $totalNonOperatingExpenses[] = array($period["key"] => $valueTotal);
                                                $lineitem[$period["key"]] = $valueTotal;
                                            }
                                            
                                            $subTotal = array();
                                        }
        
                                        $profitAndLoss["Total Non-operating expenses"] = $lineitem;
        
                                        $lineitem  = null;
                                
                                        if(isset($incomestatement["periods"]) && count($incomestatement["periods"]) > 0)
                                        {
                                            foreach($incomestatement["periods"] as $period)
                                            {
                                                $valueTotal = 0;
        
                                                foreach($totalNonOperatingIncome as $totalNonOperatingIncomeItem)
                                                {
                                                    foreach($totalNonOperatingIncomeItem as $key => $val)
                                                    {
                                                        if($key == $period["key"])
                                                        {
                                                            $valueTotal+=$val;
                                                        }
                                                    }
                                                }
        
                                                foreach($totalNonOperatingExpenses as $totalNonOperatingExpensesItem)
                                                {
                                                    foreach($totalNonOperatingExpensesItem as $key => $val)
                                                    {
                                                        if($key == $period["key"])
                                                        {
                                                            $valueTotal-=$val;
                                                        }
                                                    }
                                                }
        
                                                $netOfNonOperatingIncomeExpenses[] = array($period["key"] => $valueTotal);
                                                $lineitem[$period["key"]] = $valueTotal;
        
                                            }
                                                $subTotal = array();
                                        }
        
                                        $profitAndLoss["Net of Non-operating Income / Expenses"] = $lineitem;
        
                                        $lineitem  = null;
                                        
                                        if(isset($incomestatement["periods"]) && count($incomestatement["periods"]) > 0)
                                        {
                                            foreach($incomestatement["periods"] as $period)
                                            {
                                                $valueTotal = 0;
        
                                                foreach($operatingProfitAfterInterestValues as $operatingProfitAfterInterestValueItem)
                                                {
                                                    foreach($operatingProfitAfterInterestValueItem as $key => $val)
                                                    {
                                                        if($key == $period["key"])
                                                        {
                                                            $valueTotal+=$val;
                                                        }
                                                    }
                                                }
        
                                                foreach($netOfNonOperatingIncomeExpenses as $netOfNonOperatingIncomeExpensesItem)
                                                {
                                                    foreach($netOfNonOperatingIncomeExpensesItem as $key => $val)
                                                    {
                                                        if($key == $period["key"])
                                                        {
                                                            $valueTotal+=$val;
                                                        }
                                                    }
                                                }
        
                                                $profitBeforeTax[] = array($period["key"] => $valueTotal);
                                                $lineitem[$period["key"]] = $valueTotal;
        
                                            }
                                            
                                            $subTotal = array();
                                        }
        
                                        $profitAndLoss["Profit Before tax"] = $lineitem;
                                    }
                                    else if ($itemIndex == 45)
                                    {
        
                                        $lineitem  = null; //array("lineitem" => , "values" => array());
                
                                        if(isset($incomestatement["periods"]) && count($incomestatement["periods"]) > 0)
                                        {
                                            foreach($incomestatement["periods"] as $period)
                                            {
                                                $valueTotal = 0;
        
                                                foreach($subTotal as $subTotalItem)
                                                {
                                                    foreach($subTotalItem as $key => $val)
                                                    {
                                                        if($key == $period["key"])
                                                        {
                                                            $valueTotal+=$val;
                                                        }
                                                    }
                                                }
        
                                                $provisionForTaxation[] = array($period["key"] => $valueTotal);
                                                $lineitem[$period["key"]] = $valueTotal;
        
                                            }
                                            $subTotal = array();
                                        }
        
                                        $profitAndLoss["Provision for Taxation Sub Total"] = $lineitem;
        
                                        $lineitem  = null;//array("lineitem" => , "values" => array());
                                        
                                        if(isset($incomestatement["periods"]) && count($incomestatement["periods"]) > 0)
                                        {
                                            foreach($incomestatement["periods"] as $period)
                                            {
                                                $valueTotal = 0;
        
                                                foreach($profitBeforeTax as $profitBeforeTaxItem)
                                                {
                                                    foreach($profitBeforeTaxItem as $key => $val)
                                                    {
                                                        if($key == $period["key"])
                                                        {
                                                            $valueTotal+=$val;
                                                        }
                                                    }
                                                }
        
                                                foreach($provisionForTaxation as $provisionForTaxationItem)
                                                {
                                                    foreach($provisionForTaxationItem as $key => $val)
                                                    {
                                                        if($key == $period["key"])
                                                        {
                                                            $valueTotal-=$val;
                                                        }
                                                    }
                                                }
        
                                                $netProfitAfterTax[]  = array($period["key"] => $valueTotal);
                                                $lineitem[$period["key"]] = $valueTotal;
        
                                            }
                                            $subTotal = array();
                                        }
        
                                        $profitAndLoss["Net Profit After tax"] = $lineitem;
                                
                                    }
                            }
                        }
                    }
                }
        
                return $profitAndLoss;
    }
          
        
        
          
    function GetBalanceSheetLiabilities($jsonData, $type)
    {
        
                $balanceSheet = null;
                $bsData = GetBalanceSheetData($jsonData, $type);
        
                if(isset($bsData) )
                {
                
                    if(isset($bsData["lineitems"]) && count($bsData["lineitems"]) > 0)
                    {
                        if(isset($bsData["lineitems"]) && count($bsData["lineitems"]) > 0)
                        {
                            $itemIndex = 0 ;
                            $subTotal = array();
        
                            $subTotalValues = array();
                            $totalCurrentLiabilitiesValues = array();
                            $totalTermLiabilitiesValues = array();
                            $totalOutsideLiabilitiesValues = array();
                            $netWorthValues = array();
                            $totalLiabilitiesValues = array();
        
                            foreach($bsData["lineitems"] as $lineitem)
                            {
                                $itemIndex++;
                                $_lineitem = null;
        
                                if((strpos($lineitem["classname"], "Liabilities") !== false  || strpos($lineitem["classname"], "Equity") !== false) && $lineitem["label"] != "")
                                {
                    
                                    if(isset($bsData["periods"]) && count($bsData["periods"]) > 0)
                                    {
                                        foreach($bsData["periods"] as $period)
                                        {
                                            
                                            if(isset($lineitem["values"]) && count($lineitem["values"]) > 0)
                                            {
                                                foreach ($lineitem["values"] as $itemvalue) {
                                                    if($itemvalue->periodkey == $period["key"])
                                                    {
                                                        
                                                        if($itemIndex != 34 && $itemIndex != 35)
                                                        {
                                                            $subTotal[] = array($period["key"] => $itemvalue->value);
                                                            $_lineitem[$period["key"]] = $itemvalue->value;
                                                        }
                                                        
                                                    }
                                                }
                                            }
                                            else{
                                                $_lineitem[$period["key"]] = 0;
                                            }
                                
                                            
                                        }
                                    }
        
                                    $balanceSheet[trim($lineitem["label"])] = $_lineitem;
        
                        
        
                                    if($itemIndex == 3)
                                    {
        
                                        if(isset($bsData["periods"]) && count($bsData["periods"]) > 0)
                                        {
                                            foreach($bsData["periods"] as $period)
                                            {
                                                $valueTotal = 0;
        
                                                foreach($subTotal as $subTotalItem)
                                                {
                                                    foreach($subTotalItem as $key => $val)
                                                    {
                                                        if($key == $period["key"])
                                                        {
                                                            $valueTotal+=$val;
                                                        }
                                                    }
                                                }
                                                $subTotalValues[] = array($period["key"] => $valueTotal);
        
                                                $_lineitem[$period["key"]] = $valueTotal;
                                        
                                            }
                                                $subTotal = array();
                                        }
        
                                        $balanceSheet["Sub Total"] = $_lineitem;
                            
                                    }
                                    else if ($itemIndex == 18)
                                    {
                                        if(isset($bsData["periods"]) && count($bsData["periods"]) > 0)
                                        {
                                            foreach($bsData["periods"] as $period)
                                            {
                                                $valueTotal = 0;
        
                                                foreach($subTotal as $subTotalItem)
                                                {
                                                    foreach($subTotalItem as $key => $val)
                                                    {
                                                        if($key == $period["key"])
                                                        {
                                                            $valueTotal+=$val;
                                                        }
                                                    }
                                                }
        
                                                foreach($subTotalValues as $subTotalValueItem)
                                                {
                                                    foreach($subTotalValueItem as $key => $val)
                                                    {
                                                        if($key == $period["key"])
                                                        {
                                                            $valueTotal+=$val;
                                                        }
                                                    }
                                                }
        
                                                $totalCurrentLiabilitiesValues[] = array($period["key"] => $valueTotal);
        
                                                $_lineitem[$period["key"]] = $valueTotal;
        
                                            }
                                            $subTotal = array();
                                        }
        
                                        $balanceSheet["Total Current Liabilities"] = $_lineitem;
        
                                        
        
                                    }
                                    else if ($itemIndex == 28)
                                    {
                        
                                            if(isset($bsData["periods"]) && count($bsData["periods"]) > 0)
                                            {
                                                foreach($bsData["periods"] as $period)
                                                {
                                                    $valueTotal = 0;
        
                                                    foreach($subTotal as $subTotalItem)
                                                    {
                                                        foreach($subTotalItem as $key => $val)
                                                        {
                                                            if($key == $period["key"])
                                                            {
                                                                $valueTotal+=$val;
                                                            }
                                                        }
                                                    }
        
                                                    $totalTermLiabilitiesValues[] = array($period["key"] => $valueTotal);
                                                    $_lineitem[$period["key"]] = $valueTotal;
                                        
                                                }
                                                $subTotal = array();
                                            }
                                            
                                            $balanceSheet["Total Term Liabilities"] = $_lineitem;
        
                                            
                                            if(isset($bsData["periods"]) && count($bsData["periods"]) > 0)
                                            {
                                                foreach($bsData["periods"] as $period)
                                                {
                                                    $valueTotal = 0;
        
                                                    foreach($totalCurrentLiabilitiesValues as $totalCurrentLiabilitiesValueItem)
                                                    {
                                                        foreach($totalCurrentLiabilitiesValueItem as $key => $val)
                                                        {
                                                            if($key == $period["key"])
                                                            {
                                                                $valueTotal+=$val;
                                                            }
                                                        }
                                                    }
        
                                                    foreach($totalTermLiabilitiesValues as $totalTermLiabilitiesValueItem)
                                                    {
                                                        foreach($totalTermLiabilitiesValueItem as $key => $val)
                                                        {
                                                            if($key == $period["key"])
                                                            {
                                                                $valueTotal+=$val;
                                                            }
                                                        }
                                                    }
        
                                                    $totalOutsideLiabilitiesValues[] = array($period["key"] => $valueTotal);
                                                    $_lineitem[$period["key"]] = $valueTotal;
                                                }
                                                $subTotal = array();
                                            }
        
                                            $balanceSheet["Total Outside Liabilities"] = $_lineitem;
        
                                            
                                        
                                    }
                                    else if ($itemIndex == 40)
                                    {
                        
                                        if(isset($bsData["periods"]) && count($bsData["periods"]) > 0)
                                        {
                                            foreach($bsData["periods"] as $period)
                                            {
                                                $valueTotal = 0;
        
                                                foreach($subTotal as $subTotalItem)
                                                {
                                                    foreach($subTotalItem as $key => $val)
                                                    {
                                                        if($key == $period["key"])
                                                        {
                                                            $valueTotal+=$val;
                                                        }
                                                    }
                                                }
        
                                                $netWorthValues[] = array($period["key"] => $valueTotal);
                                                $_lineitem[$period["key"]] = $valueTotal;
                                            }
                                            $subTotal = array();
                                        }
        
                                        $balanceSheet["NET WORTH"] = $_lineitem;
        
                                        if(isset($bsData["periods"]) && count($bsData["periods"]) > 0)
                                        {
                                            foreach($bsData["periods"] as $period)
                                            {
                                                $valueTotal = 0;
        
                                                foreach($netWorthValues as $netWorthValueItem)
                                                {
                                                    foreach($netWorthValueItem as $key => $val)
                                                    {
                                                        if($key == $period["key"])
                                                        {
                                                            $valueTotal+=$val;
                                                        }
                                                    }
                                                }
        
                                                foreach($totalOutsideLiabilitiesValues as $totalOutsideLiabilitiesValueItem)
                                                {
                                                    foreach($totalOutsideLiabilitiesValueItem as $key => $val)
                                                    {
                                                        if($key == $period["key"])
                                                        {
                                                            $valueTotal+=$val;
                                                        }
                                                    }
                                                }
        
                                                $_lineitem[$period["key"]] = $valueTotal;
                                        
                                            }
                                            $subTotal = array();
                                        }
        
                                        $balanceSheet["TOTAL LIABILITIES"] = $_lineitem;
                                        
                                    }
        
                                }
                            }
                        }
                                                        
                    }
                }
        
                return $balanceSheet;
    }
          
        
          
    function GetBalanceSheetAssets($jsonData, $type)
    {
        
                $balanceSheet = null;
                $bsData = GetBalanceSheetData($jsonData, $type);
        
                if(isset($bsData) )
                {
                
                    if(isset($bsData["lineitems"]) && count($bsData["lineitems"]) > 0)
                    {
                        $itemIndex = 0 ;
                        $subTotal = array();
        
                        $groupSubTotal = array();
        
                        $subTotalValues = array();
                        $totalCurrentLiabilitiesValues = array();
                        $totalTermLiabilitiesValues = array();
                        $totalOutsideLiabilitiesValues = array();
                        $netWorthValues = array();
                        $totalLiabilitiesValues = array();
        
                        $totalCurrentAssetsValues = array();
                        $netBlockValues = array();
                        $totalNonCurrentAssetsValues = array();
                        $totalIntangibleAssetsValues = array();
                        
        
                        $trHtml = "";
        
                        foreach($bsData["lineitems"] as $lineitem)
                        {
                            $itemIndex++;
                            $_lineitem = NULL;
        
                            if((strpos($lineitem["classname"], "Assets") !== false) 
                            && $lineitem["label"] != "")
                            {
                                
                                if(($itemIndex >= 43 && $itemIndex <= 45)
                                || ($itemIndex >= 46 && $itemIndex <= 47)
                                || ($itemIndex >= 48 && $itemIndex <= 53)
                                || ($itemIndex >= 56 && $itemIndex <= 57)
                                || ($itemIndex >= 58 && $itemIndex <= 59))
                                {
                                    $title = "";
                                    if($itemIndex >= 43 && $itemIndex <= 45)
                                    {
                                        $title = "Investments";
                                    }
                                    else if ($itemIndex >= 46 && $itemIndex <= 47)
                                    {
                                        $title = "Recievables";
                                    }
                                    else if ($itemIndex >= 48 && $itemIndex <= 53)
                                    {
                                        $title = "Inventory";
                                    }
                                    else if ($itemIndex >= 56 && $itemIndex <= 57)
                                    {
                                        $title = "Other Current Assets";
                                    }
                                    else if ($itemIndex >= 58 && $itemIndex <= 59)
                                    {
                                        $title = "FIXED ASSETS";
                                    }
        
                                    
                                    
                                    
                                        if(isset($bsData["periods"]) && count($bsData["periods"]) > 0)
                                        {
                                            foreach($bsData["periods"] as $period)
                                            {
                                           
                                                if(isset($lineitem["values"]) && count($lineitem["values"]) > 0)
                                                {
                                                    foreach ($lineitem["values"] as $itemvalue) {
                                                        if($itemvalue->periodkey == $period["key"])
                                                        {
                                                            $trHtml.=$itemvalue->value;
        
                                                            
                                                            if($itemIndex == 59)
                                                            {
                                                                $balanceSheet[trim($lineitem["label"])][$period["key"]] = -$itemvalue->value;
                                                                $groupSubTotal[] = array($period["key"] => -$itemvalue->value);
                                                            }
                                                            else{
                                                                $balanceSheet[trim($lineitem["label"])][$period["key"]] = $itemvalue->value;
                                                                $groupSubTotal[] = array($period["key"] => $itemvalue->value);
                                                            }
                                                            
                                                            
                                                        }
                                                    }
                                                }
                                                else{
                                                    $balanceSheet[trim($lineitem["label"])][$period["key"]] = 0;
                                                }
                                                
                                    
                                            }
                                        }
                                        
        
                                        if($itemIndex == 45 || $itemIndex == 47 || $itemIndex == 53 || $itemIndex == 57
                                        || $itemIndex == 59)
                                        {
                                            
        
                                            if(isset($bsData["periods"]) && count($bsData["periods"]) > 0)
                                            {
                                                foreach($bsData["periods"] as $period)
                                                {
                                                    $valueTotal = 0;
        
                                                    foreach($groupSubTotal as $groupSubTotalItem)
                                                    {
                                                        foreach($groupSubTotalItem as $key => $val)
                                                        {
                                                            if($key == $period["key"])
                                                            {
                                                                $valueTotal+=$val;
                                                            }
                                                        }
                                                    }
        
                                                    if($itemIndex == 45 || $itemIndex == 47 || $itemIndex == 53 || $itemIndex == 57)
                                                    {
                                                        $subTotalValues[] = array($period["key"] => $valueTotal);
                                                    }
                                                    else if($itemIndex == 59)
                                                    {
                                                        $netBlockValues[] = array($period["key"] => $valueTotal);
                                                    }
                                                    
                                                    $balanceSheet[trim($title)][$period["key"]] = $valueTotal;
                                           
                                                }
        
                                                $groupSubTotal = array();
                                            }
        
                                            
                                        }
        
                    
                                }
                                else{
              
                                    if(isset($bsData["periods"]) && count($bsData["periods"]) > 0)
                                    {
                                        foreach($bsData["periods"] as $period)
                                        {
        
                                            if(isset($lineitem["values"]) && count($lineitem["values"]) > 0)
                                            {
                                                foreach ($lineitem["values"] as $itemvalue) {
                                                    if($itemvalue->periodkey == $period["key"])
                                                    {
                                                        
                                                    
                                                        $subTotal[] = array($period["key"] => $itemvalue->value);
        
                                                        $balanceSheet[trim($lineitem["label"])][$period["key"]] = $itemvalue->value;
                                                    }
                                                }
                                            }
                                            else {
                                                $balanceSheet[trim($lineitem["label"])][$period["key"]] = 0;
                                            }      
                                        }
                                    }
                                }
        
                                if ($itemIndex == 57)
                                {
                                    if(isset($bsData["periods"]) && count($bsData["periods"]) > 0)
                                    {
                                        foreach($bsData["periods"] as $period)
                                        {
                                            $valueTotal = 0;
        
                                            foreach($subTotal as $subTotalItem)
                                            {
                                                foreach($subTotalItem as $key => $val)
                                                {
                                                    if($key == $period["key"])
                                                    {
                                                        $valueTotal+=$val;
                                                    }
                                                }
                                            }
        
                                            foreach($subTotalValues as $subTotalValuesItem)
                                            {
                                                foreach($subTotalValuesItem as $key => $val)
                                                {
                                                    if($key == $period["key"])
                                                    {
                                                        $valueTotal+=$val;
                                                    }
                                                }
                                            }
        
                                            $totalCurrentAssetsValues[] = array($period["key"] => $valueTotal);
                                            
                                            $balanceSheet["Total Current Assets"][$period["key"]] = $valueTotal;
                                            
                                    
                                        }
                                        $subTotal = array();
                                        
                                    }
                                }
                                else if ($itemIndex == 59)
                                {
                                    if(isset($bsData["periods"]) && count($bsData["periods"]) > 0)
                                    {
                                        foreach($bsData["periods"] as $period)
                                        {
                                            $valueTotal = 0;
        
                                            foreach($netBlockValues as $netBlockValueItem)
                                            {
                                                foreach($netBlockValueItem as $key => $val)
                                                {
                                                    if($key == $period["key"])
                                                    {
                                                        $valueTotal+=$val;
                                                    }
                                                }
                                            }
        
                                            $balanceSheet["Net Block"][$period["key"]] = $valueTotal;
                                        }
                                            $subTotal = array();
                                    }
                                    
                                }
                                else if ($itemIndex == 60)
                                {
                                    if(isset($bsData["periods"]) && count($bsData["periods"]) > 0)
                                    {
                                        foreach($bsData["periods"] as $period)
                                        {
                                            $balanceSheet["NON-CURRENT ASSETS"][$period["key"]] = 0;
                                        }
                                    }
                              
                                    if(isset($bsData["periods"]) && count($bsData["periods"]) > 0)
                                    {
                                        foreach($bsData["periods"] as $period)
                                        {
                                            $balanceSheet["Investments / Book Debts / Advances / Deposits (which are not current assets):"][$period["key"]] = 0;
                                        }    
                                    }
                                }
                                else if ($itemIndex == 71)
                                {
                                    if(isset($bsData["periods"]) && count($bsData["periods"]) > 0)
                                    {
                                        foreach($bsData["periods"] as $period)
                                        {
                                            $valueTotal = 0;
        
                                            foreach($subTotal as $subTotalItem)
                                            {
                                                foreach($subTotalItem as $key => $val)
                                                {
                                                    if($key == $period["key"])
                                                    {
                                                        $valueTotal+=$val;
                                                    }
                                                }
                                            }
        
                                            $totalNonCurrentAssetsValues[] = array($period["key"] => $valueTotal);
                                            $balanceSheet["TOTAL NON CURRENT ASSETS"][$period["key"]] = $valueTotal;
                                    
                                        }
                                        
                                        $subTotal = array();
                                    }
                                }
                                else if ($itemIndex == 75)
                                {
                                    if(isset($bsData["periods"]) && count($bsData["periods"]) > 0)
                                    {
                                        foreach($bsData["periods"] as $period)
                                        {
                                            $valueTotal = 0;
        
                                            foreach($subTotal as $subTotalItem)
                                            {
                                                foreach($subTotalItem as $key => $val)
                                                {
                                                    if($key == $period["key"])
                                                    {
                                                        $valueTotal+=$val;
                                                    }
                                                }
                                            }
        
                                        
                                            $balanceSheet["TOTAL INTANGIBLE ASSETS"][$period["key"]] = $valueTotal;
                                            $totalIntangibleAssetsValues[] = array($period["key"] => $valueTotal);
                                    
                                        }
                                        $subTotal = array();
                                    }
                                            
                                    if(isset($bsData["periods"]) && count($bsData["periods"]) > 0)
                                    {
                                        foreach($bsData["periods"] as $period)
                                        {
                                            $valueTotal = 0;
        
                                            foreach($totalCurrentAssetsValues as $totalCurrentAssetsValueItem)
                                            {
                                                foreach($totalCurrentAssetsValueItem as $key => $val)
                                                {
                                                    if($key == $period["key"])
                                                    {
                                                        $valueTotal+=$val;
                                                    }
                                                }
                                            }
        
                                            foreach($netBlockValues as $netBlockValueItem)
                                            {
                                                foreach($netBlockValueItem as $key => $val)
                                                {
                                                    if($key == $period["key"])
                                                    {
                                                        $valueTotal+=$val;
                                                    }
                                                }
                                            }
        
                                            foreach($totalNonCurrentAssetsValues as $totalNonCurrentAssetsValueItem)
                                            {
                                                foreach($totalNonCurrentAssetsValueItem as $key => $val)
                                                {
                                                    if($key == $period["key"])
                                                    {
                                                        $valueTotal+=$val;
                                                    }
                                                }
                                            }                                        
        
                                            foreach($totalIntangibleAssetsValues as $totalIntangibleAssetsValueItem)
                                            {
                                                foreach($totalIntangibleAssetsValueItem as $key => $val)
                                                {
                                                    if($key == $period["key"])
                                                    {
                                                        $valueTotal+=$val;
                                                    }
                                                }
                                            }
        
                                            $balanceSheet["TOTAL ASSETS"][$period["key"]] = $valueTotal;
        
                                        }
                                        $subTotal = array();
                                    }
                                            
                                }
        
                            }
                        }
                    }
                }
        
                return $balanceSheet;
    }
          
        
    
}

