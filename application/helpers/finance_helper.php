<?php

if(!function_exists('DisplayAmount')) {
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
}

if(!function_exists('GetBSLineItemData')) {
    function GetBSLineItemData($label, $values)
    {
    	$lineitem = null;

        $defaultJsonFile = './public/data/structure/bslineitems.json';
        $bslineitems = file_get_contents($defaultJsonFile);

        if($bslineitems != "")
        {
        	$bslineitems = json_decode($bslineitems, true);
        	if(json_last_error() == JSON_ERROR_NONE)
        	{
        		if(isset($bslineitems[$label]))
        		{
	        		$lineitem = $bslineitems[$label];
	        		$lineitem["label"] = $label;
	        		$lineitem["values"] = $values;
	        		//var_dump($lineitem);
	        		//die();
        		}
        		else
        		{
        			$lineitem = array();
        			$lineitem["label"] = $label;
	        		$lineitem["values"] = $values;
        		}
        	}
        	else
        	{
        		die(json_last_error());
        	}
        }


        return $lineitem;
    }
}


if(!function_exists('GetISLineItemData')) {
    function GetISLineItemData($label, $values)
    {
    	$lineitem = null;

        $defaultJsonFile = './public/data/structure/islineitems.json';
        $bslineitems = file_get_contents($defaultJsonFile);

        if($bslineitems != "")
        {
        	$bslineitems = json_decode($bslineitems, true);
        	if(json_last_error() == JSON_ERROR_NONE)
        	{
        		if(isset($bslineitems[$label]))
        		{
	        		$lineitem = $bslineitems[$label];
	        		$lineitem["label"] = $label;
	        		$lineitem["values"] = $values;
	        		$lineitem["componenttype"] = "IS";
	        		
	        		//var_dump($lineitem);
	        		//die();
        		}
        		else
        		{
        			$lineitem = array();
        			$lineitem["label"] = $label;
	        		$lineitem["values"] = $values;
	        		$lineitem["componenttype"] = "IS";
        		}
        	}
        	else
        	{
        		die(json_last_error());
        	}
        }


        return $lineitem;
    }
}

if(!function_exists('GetISLineItem')) {
    function GetISLineItem($lineitems, $itemtitle, $period)
    {
        foreach($lineitems as $item)
		{
			if($item["label"] == $itemtitle)
			{
				if($item["values"] != null)
				{
					foreach($item["values"] as $valItem)
					{
						if($valItem->key == $period)
						{
							return $valItem->value;
						}
					}
				}
			}
		}

        return 0;
    }
}

if(!function_exists('GetProfitandLossLineItem')) {
    function GetProfitandLossLineItem($lineitems, $itemtitle, $period)
    {
        foreach($lineitems as $item)
		{
			if($item["label"] == $itemtitle)
			{
				if($item["values"] != null)
				{
					foreach($item["values"] as $valItem)
					{
						if($valItem["key"] == $period)
						{
							return $valItem["value"];
						}
					}
				}
			}
		}

        return 0;
    }
}

if(!function_exists('GetBalanceSheetLineItem')) {
    function GetBalanceSheetLineItem($lineitems, $itemtitle, $period)
    {
        foreach($lineitems as $item)
		{
			if($item["label"] == $itemtitle)
			{
				if($item["values"] != null)
				{
					foreach($item["values"] as $valItem)
					{
						if($valItem->key == $period)
						{
							return $valItem->value;
						}
					}
				}
			}
		}

        return 0;
    }
}

if(!function_exists('GetBalanceSheetAnalysisLineItem')) {
    function GetBalanceSheetAnalysisLineItem($lineitems, $itemtitle, $period)
    {
        foreach($lineitems as $item)
		{
			if($item["label"] == $itemtitle)
			{
				if($item["values"] != null)
				{
					foreach($item["values"] as $valItem)
					{
						if($valItem["key"] == $period)
						{
							return $valItem["value"];
						}
					}
				}
			}
		}

        return 0;
    }
}

if(!function_exists('GetFinancialSummary')) 
{
    function GetFinancialSummary($balanceSheet, $profitloss, $periods)
	{
			$prevLabel = "";	
		
			$financialSummary = null;

			
			$netSales = array("label" => "Net Sales", "values" => null);
			$otherIncome = array("label" => "Other Income", "values" => null);
			$income = array("label" => "Income", "values" => null);
			$pbdita = array("label" => "PBDITA", "values" => null);
			$pbditaMargin = array("label" => "PBDITA Margin (%)", "values" => null);
			$interest = array("label" => "Interest", "values" => null);
			$depreciation = array("label" => "Depreciation", "values" => null);
			$oProfitAfterInterest = array("label" => "Operating Profit After Interest", "values" => null);
			$incomeExpenses = array("label" => "Income / Expenses", "values" => null);
			$profitBeforeTax = array("label" => "Profit Before Tax", "values" => null);
			$profitAfterTax = array("label" => "Profit After Tax", "values" => null);
			$netProfitMargin = array("label" => "Net Profit Margin (%)", "values" => null);
			$netCashAccurals = array("label" => "Net Cash Accruals (NCA)", "values" => null);

			$fixedAssetsGross = array("label" => "Fixed Assets Gross", "values" => null);
			$fixedAssetsNet = array("label" => "Fixed Assets Net", "values" => null);

			$nonCurrentAsetsExFA = array("label" => "Non Current Assets (Ex. Fixed assets)", "values" => null);

			$tangNetworth = array("label" => "Tangible Networth (TNW)", "values" => null);
			$expInGroupCo = array("label" => "Exposure in Group Co./Subsidairies", "values" => null);
			$aTNW = array("label" => "Adjusted T N W (ATNW)", "values" => null);

			$longTermDebt = array("label" => "Long Term Debt (LTD)", "values" => null);
			$shortTermDebt = array("label" => "Short Term Debt (LTD)", "values" => null);

			$wCapitalBorrowing = array("label" => "Working Capital Borrowing", "values" => null);
			$totalOutsideLiabilities = array("label" => "TOTAL OUTSIDE LIABILITIES", "values" => null);

			$LTDbyTNW = array("label" => "LTD/TNW", "values" => null);
			$TOLbyTNW = array("label" => "TOL/TNW", "values" => null);
			$TOLbyATNW = array("label" => "TOL/ATNW", "values" => null);

			$totalCurrAssetsItem = array("label" => "Total Current Assets", "values" => null);
			$totalCurrLiabilitiesItem = array("label" => "Total Current Liabilities", "values" => null);

			$netWorkingCapital = array("label" => "Net Working Capital", "values" => null);
			$currentRatio = array("label" => "Current Ratio", "values" => null);

			$inventoryHoldingPeriod = array("label" => "Inventory Holding period (days)", "values" => null);
			$debtorsHoldingPeriod = array("label" => "Debtors Holding Period (days)", "values" => null);
			$creditorsHoldingPeriod = array("label" => "Creditors Holding Period (days)", "values" => null);

			$debtEquityRatio = array("label" => "Debt Equity Ratio", "values" => null);
			$debtPBITDARatio = array("label" => "Debt/PBITDA Ratio", "values" => null);

			$intCovRatio = array("label" => "Interest Coverage Ratio", "values" => null);

			$dscr = array("label" => "DSCR (Avg/Min)", "values" => null);
			


			for($i = 0 ; $i < count($periods); $i++)
			{
				$periodItem = $periods[$i];
				$netSales["values"][$i]["key"] = $periodItem["key"];
				$netSales["values"][$i]["value"] =  GetProfitandLossLineItem($profitloss, "Net Sales", $periodItem["key"]); 
				
				$exportIncentive = GetProfitandLossLineItem($profitloss, "Export Incentive", $periodItem["key"]); 
				$otherIncomeItem = GetProfitandLossLineItem($profitloss, "Other Income", $periodItem["key"]); 

				$otherIncome["values"][$i]["key"] = $periodItem["key"];
				$otherIncome["values"][$i]["value"] =  $exportIncentive + $otherIncomeItem;

				$income["values"][$i]["key"] = $periodItem["key"];
				$income["values"][$i]["value"] = GetProfitandLossLineItem($profitloss, "Total Operating Income", $periodItem["key"]); 

				

				$depriciation = GetProfitandLossLineItem($profitloss, "Depreciation", $periodItem["key"]); 
				$opAfterInterest = GetProfitandLossLineItem($profitloss, "Operating Profit after Interest", $periodItem["key"]); 

				$opBeforeInterest = GetProfitandLossLineItem($profitloss, "Operating Profit before Interest", $periodItem["key"]); 

				$pbdita["values"][$i]["key"] = $periodItem["key"];
				$pbdita["values"][$i]["value"] = $depriciation + $opBeforeInterest; 

				$pbditaMargin["values"][$i]["key"] = $periodItem["key"];
				$pbditaMargin["values"][$i]["value"] = $netSales["values"][$i]["value"] != 0 ? ($pbdita["values"][$i]["value"] / $netSales["values"][$i]["value"]) * 100 : 0;
				
				

				$interest["values"][$i]["key"] = $periodItem["key"];
				$interest["values"][$i]["value"] = GetProfitandLossLineItem($profitloss, "Total Interest", $periodItem["key"]); 

				$depreciation["values"][$i]["key"] = $periodItem["key"];
				$depreciation["values"][$i]["value"] = GetProfitandLossLineItem($profitloss, "Depreciation", $periodItem["key"]); 

				$oProfitAfterInterest["values"][$i]["key"] = $periodItem["key"];
				$oProfitAfterInterest["values"][$i]["value"] = $opAfterInterest;
				
				$incomeExpenses["values"][$i]["key"] = $periodItem["key"];
				$incomeExpenses["values"][$i]["value"] =  GetProfitandLossLineItem($profitloss, "Net of Non-operating Income / Expenses", $periodItem["key"]); 


				$profitBeforeTax["values"][$i]["key"] = $periodItem["key"];
				$profitBeforeTax["values"][$i]["value"] =  GetProfitandLossLineItem($profitloss, "Profit Before tax ", $periodItem["key"]); 

				$profitAfterTax["values"][$i]["key"] = $periodItem["key"];
				$profitAfterTax["values"][$i]["value"] =  GetProfitandLossLineItem($profitloss, "Net Profit After tax", $periodItem["key"]); 
	
				$netProfitMargin["values"][$i]["key"] = $periodItem["key"];
				$netProfitMargin["values"][$i]["value"] =  $netSales["values"][$i]["value"] != 0 ? ($profitAfterTax["values"][$i]["value"] / $netSales["values"][$i]["value"]) * 100 : 0;

				$netCashAccurals["values"][$i]["key"] = $periodItem["key"];
				$netCashAccurals["values"][$i]["value"] = $profitAfterTax["values"][$i]["value"] + $depreciation["values"][$i]["value"];

				$grossBlock =  GetBalanceSheetLineItem($balanceSheet, "Gross Block ", $periodItem["key"]); 
				$cWorkInProgress =  GetBalanceSheetLineItem($balanceSheet, "Capital Work in progress", $periodItem["key"]); 
				$acumulatedDep =  GetBalanceSheetLineItem($balanceSheet, "Less: Accumulated Depreciation", $periodItem["key"]); 
				

				$fixedAssetsGross["values"][$i]["key"] = $periodItem["key"];
				$fixedAssetsGross["values"][$i]["value"] = $grossBlock + $cWorkInProgress;

				$fixedAssetsNet["values"][$i]["key"] = $periodItem["key"];
				$fixedAssetsNet["values"][$i]["value"] = $grossBlock - $acumulatedDep;

				$iInGC = GetBalanceSheetLineItem($balanceSheet, "Investments in Group concerns", $periodItem["key"]); 
				$loanToGC = GetBalanceSheetLineItem($balanceSheet, "Loans to group concerns / Advances to subsidiaries", $periodItem["key"]); 
				$investInOthers = GetBalanceSheetLineItem($balanceSheet, "Investments in others", $periodItem["key"]); 
				$advToSupp = GetBalanceSheetLineItem($balanceSheet, "Advances to suppliers of capital goods and contractors", $periodItem["key"]); 
				$deffRecv = GetBalanceSheetLineItem($balanceSheet, "Deferred receivables (maturity exceeding one year)", $periodItem["key"]); 
				$debtTo6Months = GetBalanceSheetLineItem($balanceSheet, "Debtors more than 6 months", $periodItem["key"]); 
				$secDeposits = GetBalanceSheetLineItem($balanceSheet, "Security deposits", $periodItem["key"]); 
				$depWithGovt = GetBalanceSheetLineItem($balanceSheet, "Deposits with Government departments", $periodItem["key"]); 
				$deferredTaxAsset = GetBalanceSheetLineItem($balanceSheet, "Deferred Tax Asset", $periodItem["key"]); 
				$otherNcAssets = GetBalanceSheetLineItem($balanceSheet, "Other Non-current Assets", $periodItem["key"]); 

				$otherLoansAdvICD = GetBalanceSheetLineItem($balanceSheet, "Others (Loans & Advances non current in nature, ICD’s etc.)", $periodItem["key"]); 

				

				//echo $iInGC." + ".$loanToGC." + ".$investInOthers." + ".$advToSupp." + ".$deffRecv." + ".$debtTo6Months." + ".$secDeposits." + ".$depWithGovt." + ".$deferredTaxAsset." + ".$otherNcAssets;
				//die();

				$nonCurrentAsetsExFA["values"][$i]["key"] = $periodItem["key"];
				$nonCurrentAsetsExFA["values"][$i]["value"] = $iInGC + $loanToGC + $investInOthers + $advToSupp + $deffRecv + $debtTo6Months + $secDeposits + $depWithGovt + $deferredTaxAsset + $otherNcAssets + $otherLoansAdvICD;

				$shareCapitalPaidUp = GetBalanceSheetLineItem($balanceSheet, "Share Capital (Paid-up)", $periodItem["key"]); 
				$shareAppMoney = GetBalanceSheetLineItem($balanceSheet, "Share Application money", $periodItem["key"]); 
				$genReserve = GetBalanceSheetLineItem($balanceSheet, "General Reserve", $periodItem["key"]); 
				$revalReserve = GetBalanceSheetLineItem($balanceSheet, "Revaluation Reserve", $periodItem["key"]); 

				$sharePrem = GetBalanceSheetLineItem($balanceSheet, "Share Premium", $periodItem["key"]); 
				$capitalSubsidy = GetBalanceSheetLineItem($balanceSheet, "Capital subsidy", $periodItem["key"]); 
				$quasiEquity = GetBalanceSheetLineItem($balanceSheet, "Quasi Equity", $periodItem["key"]); 
				$balinPLAc = GetBalanceSheetLineItem($balanceSheet, "Balance in P&L Account (+ / - )", $periodItem["key"]); 

				$netWorth = $shareCapitalPaidUp + $shareAppMoney + $genReserve + $revalReserve + $sharePrem + $capitalSubsidy + $quasiEquity + $balinPLAc;

				$goodwillPatentsTM = GetBalanceSheetLineItem($balanceSheet, "Goodwill, Patents & trademarks", $periodItem["key"]); 
				$miscExp = GetBalanceSheetLineItem($balanceSheet, "Miscellaneous expenditure not w/off", $periodItem["key"]); 
				$otherDeferredRevExp = GetBalanceSheetLineItem($balanceSheet, "Other deferred revenue expenses", $periodItem["key"]); 

				$totalIntangibleAssets = $goodwillPatentsTM + $miscExp + $otherDeferredRevExp;

				$tangNetworth["values"][$i]["key"] = $periodItem["key"];
				$tangNetworth["values"][$i]["value"] = $netWorth - $totalIntangibleAssets - $revalReserve;

				$expInGroupCo["values"][$i]["key"] = $periodItem["key"];
				$expInGroupCo["values"][$i]["value"] = $iInGC + $loanToGC;

				$aTNW["values"][$i]["key"] = $periodItem["key"];
				$aTNW["values"][$i]["value"] = $tangNetworth["values"][$i]["value"] - $expInGroupCo["values"][$i]["value"];

				
				$debMaturingAfter1Year = GetBalanceSheetLineItem($balanceSheet, "Debentures maturing after 1 year", $periodItem["key"]); 
				$prefShareCapMatLessThen12Year = GetBalanceSheetLineItem($balanceSheet, "Preference share capital maturity < 12 years", $periodItem["key"]); 
				$dealersDeposit = GetBalanceSheetLineItem($balanceSheet, "Dealer's Deposit", $periodItem["key"]); 

				$termLoansFromBanks = GetBalanceSheetLineItem($balanceSheet, "Term Loans  - From Banks", $periodItem["key"]); 
				$termLoansFromFinancialInstitution = GetBalanceSheetLineItem($balanceSheet, "Term Loans - From Financial Institution", $periodItem["key"]); 
				$termDeposits = GetBalanceSheetLineItem($balanceSheet, "Term Deposits", $periodItem["key"]); 

				$deferredTaxLiabilities =  GetBalanceSheetLineItem($balanceSheet, "Deferred Tax Liability", $periodItem["key"]); 
				
				$borrowingFromSubs = GetBalanceSheetLineItem($balanceSheet, "Borrowings from subsidiaries / affiliates", $periodItem["key"]); 
				$unsecuredLoans = GetBalanceSheetLineItem($balanceSheet, "Unsecured Loans ", $periodItem["key"]); 
				$otherTermLiabilities = GetBalanceSheetLineItem($balanceSheet, "Other term liabilities", $periodItem["key"]); 

				$totalTermLiabilities = $debMaturingAfter1Year + 
				$prefShareCapMatLessThen12Year + 
				$dealersDeposit + 
				$termLoansFromBanks + 
				$termLoansFromFinancialInstitution + 
				$termDeposits + 
				$deferredTaxLiabilities + 
				$borrowingFromSubs + 
				$unsecuredLoans + 
				$otherTermLiabilities ;

				$longTermDebt["values"][$i]["key"] = $periodItem["key"];
				$longTermDebt["values"][$i]["value"] = $totalTermLiabilities - $deferredTaxLiabilities;

				$shortTermBorrowingsFromAandG = GetBalanceSheetLineItem($balanceSheet, "Short term borrowings from Associates & Group Concerns repayable within one year", $periodItem["key"]); 
				$shortTermBorrowingsFromOthers = GetBalanceSheetLineItem($balanceSheet, "Short term borrowings from Others", $periodItem["key"]); 
				$instOfTermLoanDeb = GetBalanceSheetLineItem($balanceSheet, "Installments of Term Loans/Debentures (due within one year)- To banks ", $periodItem["key"]); 
				$instOfTermLoanDebToOthers = GetBalanceSheetLineItem($balanceSheet, "Installments of Term Loans/Debentures (due within one year)- To Others", $periodItem["key"]); 

				$shortTermDebt["values"][$i]["key"] = $periodItem["key"];
				$shortTermDebt["values"][$i]["value"] = $shortTermBorrowingsFromAandG + $shortTermBorrowingsFromOthers + $instOfTermLoanDeb + $instOfTermLoanDebToOthers;

				$bankBorrowings = GetBalanceSheetLineItem($balanceSheet, "Bank Borrowings - From applicant Bank", $periodItem["key"]) + GetBalanceSheetLineItem($balanceSheet, "Bank Borrowings - From other Banks", $periodItem["key"]); 

				$wCapitalBorrowing["values"][$i]["key"] = $periodItem["key"];
				$wCapitalBorrowing["values"][$i]["value"] = $bankBorrowings;

				$bankBorrowings = GetBalanceSheetLineItem($balanceSheet, "Bank Borrowings - From applicant Bank", $periodItem["key"]) + GetBalanceSheetLineItem($balanceSheet, "Bank Borrowings - From other Banks", $periodItem["key"]); 

				$shortTermBorrowingsFromAandG = GetBalanceSheetLineItem($balanceSheet, "Short term borrowings from Associates & Group Concerns repayable within one year", $periodItem["key"]); 
				$shortTermBorrowingsFromOthers = GetBalanceSheetLineItem($balanceSheet, "Short term borrowings from Others", $periodItem["key"]); 
				
				$creditorsforPurchasesOthers = GetBalanceSheetLineItem($balanceSheet, "Creditors for purchases – others", $periodItem["key"]); 
				$creditorsforPurchasesGroupCompanies = GetBalanceSheetLineItem($balanceSheet, "Creditors for purchases – Group Companies", $periodItem["key"]); 
				$creditorsForExpenses = GetBalanceSheetLineItem($balanceSheet, "Creditors for expenses", $periodItem["key"]); 
				$advPaymentFromCustomers = GetBalanceSheetLineItem($balanceSheet, "Advances/ payments from customers/deposits from dealers.", $periodItem["key"]); 

				$provisionTax = GetBalanceSheetLineItem($balanceSheet, "        - Tax", $periodItem["key"]); 
				$provisionDeferredTax = GetBalanceSheetLineItem($balanceSheet, "        - Deferred tax", $periodItem["key"]); 
				$provisionsOthers = GetBalanceSheetLineItem($balanceSheet, " - Others", $periodItem["key"]); 
				$dividendsPayable = GetBalanceSheetLineItem($balanceSheet, "Dividends Payable", $periodItem["key"]); 
				$statutoryLiabilitiesDueWithin1year = GetBalanceSheetLineItem($balanceSheet, "Statutory liabilities due within one year", $periodItem["key"]); 

				$instOfTermLoanDeb = GetBalanceSheetLineItem($balanceSheet, "Installments of Term Loans/Debentures (due within one year)- To banks ", $periodItem["key"]); 
				$instOfTermLoanDebToOthers = GetBalanceSheetLineItem($balanceSheet, "Installments of Term Loans/Debentures (due within one year)- To Others", $periodItem["key"]); 
				$depositsDuePayable1Year = GetBalanceSheetLineItem($balanceSheet, "Deposits due / payable within a year", $periodItem["key"]); 
				$otherCurrLiaDuePayable1Year = GetBalanceSheetLineItem($balanceSheet, "Other Current Liabilities due within one year", $periodItem["key"]); 

				$totalCurrLiabilities = $bankBorrowings + $shortTermBorrowingsFromAandG + $shortTermBorrowingsFromOthers + $creditorsforPurchasesOthers + $creditorsforPurchasesGroupCompanies + 				$creditorsForExpenses + $advPaymentFromCustomers + $provisionTax + $provisionDeferredTax + $provisionsOthers + $dividendsPayable + $statutoryLiabilitiesDueWithin1year + 				$instOfTermLoanDeb + $instOfTermLoanDebToOthers  + $depositsDuePayable1Year + $otherCurrLiaDuePayable1Year;

				$totalOutsideLiabilities["values"][$i]["key"] = $periodItem["key"];
				$totalOutsideLiabilities["values"][$i]["value"] = $totalTermLiabilities + $totalCurrLiabilities;

				$LTDbyTNW["values"][$i]["key"] = $periodItem["key"];
				$LTDbyTNW["values"][$i]["value"] = $tangNetworth["values"][$i]["value"] > 0 ? $longTermDebt["values"][$i]["value"] / $tangNetworth["values"][$i]["value"] : 0;
				

				$TOLbyTNW["values"][$i]["key"] = $periodItem["key"];
				$TOLbyTNW["values"][$i]["value"] = $tangNetworth["values"][$i]["value"] > 0 ? $totalOutsideLiabilities["values"][$i]["value"] / $tangNetworth["values"][$i]["value"] : 0;
				
				
				$TOLbyATNW["values"][$i]["key"] = $periodItem["key"];
				$TOLbyATNW["values"][$i]["value"] = $aTNW["values"][$i]["value"] > 0 ? $totalOutsideLiabilities["values"][$i]["value"] / $aTNW["values"][$i]["value"] : 0;

				$cashBalances  = GetBalanceSheetLineItem($balanceSheet, "Cash Balances", $periodItem["key"]); 
				$bankBalances  = GetBalanceSheetLineItem($balanceSheet, "Bank Balances", $periodItem["key"]); 
				$govtandothertrusteeSecurities  = GetBalanceSheetLineItem($balanceSheet, "Govt. and other trustee Securities", $periodItem["key"]); 
				$fixedDepositswithBanks  = GetBalanceSheetLineItem($balanceSheet, "Fixed Deposits with Banks", $periodItem["key"]); 
				$othersInvestmentsinSubsidiariesGroupCompanies  = GetBalanceSheetLineItem($balanceSheet, "Others – Investments in Subsidiaries/Group Companies", $periodItem["key"]); 
				$domesticReceivables  = GetBalanceSheetLineItem($balanceSheet, "Domestic Receivables ", $periodItem["key"]); 
				$exportReceivables  = GetBalanceSheetLineItem($balanceSheet, "Export Receivables", $periodItem["key"]); 
				
				$rawMaterialsImported  = GetBalanceSheetLineItem($balanceSheet, " Raw Materials – Imported", $periodItem["key"]); 
				$rawMaterialsIndigenous  = GetBalanceSheetLineItem($balanceSheet, " Raw Materials – Indigenous", $periodItem["key"]); 
				$workinprocess  = GetBalanceSheetLineItem($balanceSheet, " Work in process", $periodItem["key"]); 
				$finishedGoodsInclTradedGoods  = GetBalanceSheetLineItem($balanceSheet, " Finished Goods (incl Traded Goods)", $periodItem["key"]); 
				$otherConsumableSparesImported  = GetBalanceSheetLineItem($balanceSheet, "Other consumable spares – Imported", $periodItem["key"]); 
				$otherConsumableSparesIndigenous  = GetBalanceSheetLineItem($balanceSheet, "Other consumable spares -  Indigenous", $periodItem["key"]); 
				
				$advToSuppOfRMSS = GetBalanceSheetLineItem($balanceSheet, "Advances to suppliers of Raw materials/Stores/Spares", $periodItem["key"]); 
				$advancePaymentofTax  = GetBalanceSheetLineItem($balanceSheet, "Advance payment of tax", $periodItem["key"]); 
				$prepaidExpenses  = GetBalanceSheetLineItem($balanceSheet, "Prepaid Expenses", $periodItem["key"]); 
				$otherAdvancesCurrentAsset  = GetBalanceSheetLineItem($balanceSheet, "Other Advances/current Asset", $periodItem["key"]); 

				$totalCurrAssets =  $cashBalances  + $bankBalances  + $govtandothertrusteeSecurities + $fixedDepositswithBanks + 
				$othersInvestmentsinSubsidiariesGroupCompanies + $domesticReceivables  + $exportReceivables  + 
				$rawMaterialsImported + $rawMaterialsIndigenous + $workinprocess  + $finishedGoodsInclTradedGoods  + 
				$otherConsumableSparesImported  + $otherConsumableSparesIndigenous  + $advToSuppOfRMSS + $advancePaymentofTax  
				+ $prepaidExpenses  + $otherAdvancesCurrentAsset ;

				$totalCurrAssetsItem["values"][$i]["key"] = $periodItem["key"];
				$totalCurrAssetsItem["values"][$i]["value"] = $totalCurrAssets;

				$totalCurrLiabilitiesItem["values"][$i]["key"] = $periodItem["key"];
				$totalCurrLiabilitiesItem["values"][$i]["value"] = $totalCurrLiabilities;

				$netWorkingCapital["values"][$i]["key"] = $periodItem["key"];
				$netWorkingCapital["values"][$i]["value"] = $totalCurrAssets - $totalCurrLiabilities;

				$currentRatio["values"][$i]["key"] = $periodItem["key"];
				$currentRatio["values"][$i]["value"] = $totalCurrLiabilities != 0 ? $totalCurrAssets / $totalCurrLiabilities : 0;

				$rawMaterialsImported  = GetBalanceSheetLineItem($balanceSheet, " Raw Materials – Imported", $periodItem["key"]); 
				$rawMaterialsIndigenous  = GetBalanceSheetLineItem($balanceSheet, " Raw Materials – Indigenous", $periodItem["key"]); 

				$workinprocess  = GetBalanceSheetLineItem($balanceSheet, " Work in process", $periodItem["key"]); 
				$finishedGoodsInclTradedGoods  = GetBalanceSheetLineItem($balanceSheet, " Finished Goods (incl Traded Goods)", $periodItem["key"]); 
				$otherConsumableSparesImported  = GetBalanceSheetLineItem($balanceSheet, "Other consumable spares – Imported", $periodItem["key"]); 
				$otherConsumableSparesIndigenous  = GetBalanceSheetLineItem($balanceSheet, "Other consumable spares -  Indigenous", $periodItem["key"]); 

				$inventories =  $rawMaterialsImported + $rawMaterialsIndigenous + $workinprocess + $finishedGoodsInclTradedGoods + $otherConsumableSparesImported + $otherConsumableSparesIndigenous;
				$totalCostofSales = GetProfitandLossLineItem($profitloss, "Total Cost of Sales", $periodItem["key"]); 



				$inventoryHoldingPeriod["values"][$i]["key"] = $periodItem["key"];
				$inventoryHoldingPeriod["values"][$i]["value"] = $totalCostofSales != 0 ? ($inventories / $totalCostofSales) * 365 : 0;

				$tradeReceivables =  $domesticReceivables  + $exportReceivables;
				$netSalesVal = $netSales["values"][$i]["value"];

				$debtorsHoldingPeriod["values"][$i]["key"] = $periodItem["key"];
				$debtorsHoldingPeriod["values"][$i]["value"] = $netSalesVal != 0 ? ($tradeReceivables / $netSalesVal) * 365 : 0;

				$totalRawMaterials = $rawMaterialsImported  + $rawMaterialsIndigenous + $finishedGoodsInclTradedGoods;

				$creditorsHoldingPeriod["values"][$i]["key"] = $periodItem["key"];
				$creditorsHoldingPeriod["values"][$i]["value"] = $totalRawMaterials != 0 ? ($creditorsforPurchasesOthers  / $totalRawMaterials) * 365 : 0;

				$totalDebtVal =  $longTermDebt["values"][$i]["value"] + $shortTermDebt["values"][$i]["value"] + $wCapitalBorrowing["values"][$i]["value"] ;

				//echo $totalDebtVal." / ".$pbdita["values"][$i]["value"]."    ";


				$debtEquityRatio["values"][$i]["key"] = $periodItem["key"];
				$debtEquityRatio["values"][$i]["value"] = $tangNetworth["values"][$i]["value"] != 0 ? $totalDebtVal / $tangNetworth["values"][$i]["value"] : 0;

				$debtPBITDARatio["values"][$i]["key"] = $periodItem["key"];
				$debtPBITDARatio["values"][$i]["value"] = $pbdita["values"][$i]["value"] != 0 ? $totalDebtVal / $pbdita["values"][$i]["value"] : 0;

				$intCovRatio["values"][$i]["key"] = $periodItem["key"];
				$intCovRatio["values"][$i]["value"] = $interest["values"][$i]["value"] != 0 ? $pbdita["values"][$i]["value"] / $interest["values"][$i]["value"] : 0;

				
				$pidTotal =  $profitAfterTax["values"][$i]["value"] + $interest["values"][$i]["value"] + $depreciation["values"][$i]["value"];
				$totalInterestPay = $instOfTermLoanDeb + $instOfTermLoanDebToOthers + $interest["values"][$i]["value"];

				$dscr["values"][$i]["key"] = $periodItem["key"];
				$dscr["values"][$i]["value"] = $totalInterestPay != 0 ? $pidTotal / $totalInterestPay : 0;
				
			}

			$financialSummary[] = $netSales;
			$financialSummary[] = $otherIncome;
			$financialSummary[] = $income;
			$financialSummary[] = $pbdita;
			$financialSummary[] = $pbditaMargin;
			$financialSummary[] = $interest;
			$financialSummary[] = $depreciation;
			$financialSummary[] = $oProfitAfterInterest;
			$financialSummary[] = $incomeExpenses;
			$financialSummary[] = $profitBeforeTax;
			$financialSummary[] = $profitAfterTax;
			$financialSummary[] = $netProfitMargin;
			$financialSummary[] = $netCashAccurals;
			$financialSummary[] = $fixedAssetsGross;
			$financialSummary[] = $fixedAssetsNet;
			$financialSummary[] = $nonCurrentAsetsExFA;
			$financialSummary[] = $tangNetworth;
			$financialSummary[] = $expInGroupCo;
			$financialSummary[] = $aTNW;
			$financialSummary[] = $longTermDebt;
			$financialSummary[] = $shortTermDebt;
			$financialSummary[] = $wCapitalBorrowing;
			$financialSummary[] = $totalOutsideLiabilities;
			$financialSummary[] = $LTDbyTNW;
			$financialSummary[] = $TOLbyTNW;
			$financialSummary[] = $TOLbyATNW;
			$financialSummary[] = $totalCurrAssetsItem;
			$financialSummary[] = $totalCurrLiabilitiesItem;
			$financialSummary[] = $netWorkingCapital;
			$financialSummary[] = $currentRatio;
			$financialSummary[] = $inventoryHoldingPeriod;
			$financialSummary[] = $debtorsHoldingPeriod;
			$financialSummary[] = $creditorsHoldingPeriod;
			$financialSummary[] = $debtEquityRatio;
			$financialSummary[] = $debtPBITDARatio;
			$financialSummary[] = $intCovRatio;
			$financialSummary[] = $dscr;



			return $financialSummary;
	}
}

if(!function_exists('GetFinancialSummaryFromDB')) 
{
    function GetFinancialSummaryFromDB($fsPeriods, $fsDbData, $unit = "million")
	{
			$prevLabel = "";	
		
			$financialSummary = null;

			
			$netSales = array("label" => "Net Sales", "values" => null);
			$otherIncome = array("label" => "Other Income", "values" => null);
			$income = array("label" => "Income", "values" => null);
			$pbdita = array("label" => "PBDITA", "values" => null);
			$pbditaMargin = array("label" => "PBDITA Margin (%)", "values" => null);
			$interest = array("label" => "Interest", "values" => null);
			$depreciation = array("label" => "Depreciation", "values" => null);
			$oProfitAfterInterest = array("label" => "Operating Profit After Interest", "values" => null);
			$incomeExpenses = array("label" => "Income / Expenses", "values" => null);
			$profitBeforeTax = array("label" => "Profit Before Tax", "values" => null);
			$profitAfterTax = array("label" => "Profit After Tax", "values" => null);
			$netProfitMargin = array("label" => "Net Profit Margin (%)", "values" => null);
			$netCashAccurals = array("label" => "Net Cash Accruals (NCA)", "values" => null);

			$fixedAssetsGross = array("label" => "Fixed Assets Gross", "values" => null);
			$fixedAssetsNet = array("label" => "Fixed Assets Net", "values" => null);

			$nonCurrentAsetsExFA = array("label" => "Non Current Assets (Ex. Fixed assets)", "values" => null);

			$tangNetworth = array("label" => "Tangible Networth (TNW)", "values" => null);
			$expInGroupCo = array("label" => "Exposure in Group Co./Subsidairies", "values" => null);
			$aTNW = array("label" => "Adjusted T N W (ATNW)", "values" => null);

			$longTermDebt = array("label" => "Long Term Debt (LTD)", "values" => null);
			$shortTermDebt = array("label" => "Short Term Debt (LTD)", "values" => null);

			$wCapitalBorrowing = array("label" => "Working Capital Borrowing", "values" => null);
			$totalOutsideLiabilities = array("label" => "TOTAL OUTSIDE LIABILITIES", "values" => null);

			$LTDbyTNW = array("label" => "LTD/TNW", "values" => null);
			$TOLbyTNW = array("label" => "TOL/TNW", "values" => null);
			$TOLbyATNW = array("label" => "TOL/ATNW", "values" => null);

			$totalCurrAssetsItem = array("label" => "Total Current Assets", "values" => null);
			$totalCurrLiabilitiesItem = array("label" => "Total Current Liabilities", "values" => null);

			$netWorkingCapital = array("label" => "Net Working Capital", "values" => null);
			$currentRatio = array("label" => "Current Ratio", "values" => null);

			$inventoryHoldingPeriod = array("label" => "Inventory Holding period (days)", "values" => null);
			$debtorsHoldingPeriod = array("label" => "Debtors Holding Period (days)", "values" => null);
			$creditorsHoldingPeriod = array("label" => "Creditors Holding Period (days)", "values" => null);

			$debtEquityRatio = array("label" => "Debt Equity Ratio", "values" => null);
			$debtPBITDARatio = array("label" => "Debt/PBITDA Ratio", "values" => null);

			$intCovRatio = array("label" => "Interest Coverage Ratio", "values" => null);
			$dscr = array("label" => "DSCR (Avg/Min)", "values" => null);
			

			for($i = 0 ; $i < count($fsPeriods); $i++)
			{
				$periodItem = $fsPeriods[$i];

				foreach($fsDbData as $fsDataItem)
				{
					if($fsDataItem->year == $periodItem["year"] && $periodItem["ptype"] == $fsDataItem->period_type)
					{
						$netSales["values"][$i]["key"] = $periodItem["key"];
						$netSales["values"][$i]["value"] =  DisplayAmount($fsDataItem->net_sales, $unit);

						$otherIncome["values"][$i]["key"] = $periodItem["key"];
						$otherIncome["values"][$i]["value"] =  DisplayAmount($fsDataItem->other_income, $unit);

						$income["values"][$i]["key"] = $periodItem["key"];
						$income["values"][$i]["value"] = DisplayAmount($fsDataItem->income, $unit);

						
						$pbdita["values"][$i]["key"] = $periodItem["key"];
						$pbdita["values"][$i]["value"] = DisplayAmount($fsDataItem->pbdita, $unit);

						$pbditaMargin["values"][$i]["key"] = $periodItem["key"];
						$pbditaMargin["values"][$i]["value"] = $fsDataItem->pbdita_margin;
						
						$interest["values"][$i]["key"] = $periodItem["key"];
						$interest["values"][$i]["value"] = DisplayAmount($fsDataItem->interest, $unit);

						$depreciation["values"][$i]["key"] = $periodItem["key"];
						$depreciation["values"][$i]["value"] = DisplayAmount($fsDataItem->depriciation, $unit);

						$oProfitAfterInterest["values"][$i]["key"] = $periodItem["key"];
						$oProfitAfterInterest["values"][$i]["value"] = DisplayAmount($fsDataItem->operating_profit_after_interest, $unit);
						
						$incomeExpenses["values"][$i]["key"] = $periodItem["key"];
						$incomeExpenses["values"][$i]["value"] =  DisplayAmount($fsDataItem->income_expense, $unit);

						

						$profitBeforeTax["values"][$i]["key"] = $periodItem["key"];
						$profitBeforeTax["values"][$i]["value"] =  DisplayAmount($fsDataItem->profit_before_tax, $unit);

						$profitAfterTax["values"][$i]["key"] = $periodItem["key"];
						$profitAfterTax["values"][$i]["value"] =  DisplayAmount($fsDataItem->profit_after_tax, $unit);
						
						$netProfitMargin["values"][$i]["key"] = $periodItem["key"];
						$netProfitMargin["values"][$i]["value"] =  $fsDataItem->net_profit_margin;

						$netCashAccurals["values"][$i]["key"] = $periodItem["key"];
						$netCashAccurals["values"][$i]["value"] = DisplayAmount($fsDataItem->net_cash_accurals, $unit);

						

						$fixedAssetsGross["values"][$i]["key"] = $periodItem["key"];
						$fixedAssetsGross["values"][$i]["value"] = DisplayAmount($fsDataItem->fixed_assets_gross, $unit);

						$fixedAssetsNet["values"][$i]["key"] = $periodItem["key"];
						$fixedAssetsNet["values"][$i]["value"] = DisplayAmount($fsDataItem->fixed_assets_net, $unit);

						$nonCurrentAsetsExFA["values"][$i]["key"] = $periodItem["key"];
						$nonCurrentAsetsExFA["values"][$i]["value"] = DisplayAmount($fsDataItem->non_current_assets, $unit);

						$tangNetworth["values"][$i]["key"] = $periodItem["key"];
						$tangNetworth["values"][$i]["value"] = DisplayAmount($fsDataItem->tangible_networth, $unit);

						$expInGroupCo["values"][$i]["key"] = $periodItem["key"];
						$expInGroupCo["values"][$i]["value"] = DisplayAmount($fsDataItem->exposure_in_group_company, $unit);

						$aTNW["values"][$i]["key"] = $periodItem["key"];
						$aTNW["values"][$i]["value"] = DisplayAmount($fsDataItem->adjusted_tnw, $unit);

						$longTermDebt["values"][$i]["key"] = $periodItem["key"];
						$longTermDebt["values"][$i]["value"] = DisplayAmount($fsDataItem->long_term_debt, $unit);


						$shortTermDebt["values"][$i]["key"] = $periodItem["key"];
						$shortTermDebt["values"][$i]["value"] = DisplayAmount($fsDataItem->short_term_debt, $unit);

						

						$wCapitalBorrowing["values"][$i]["key"] = $periodItem["key"];
						$wCapitalBorrowing["values"][$i]["value"] = DisplayAmount($fsDataItem->working_capital_borrowing, $unit);

						
						$totalOutsideLiabilities["values"][$i]["key"] = $periodItem["key"];
						$totalOutsideLiabilities["values"][$i]["value"] = DisplayAmount($fsDataItem->total_outside_liabilities, $unit);

						$LTDbyTNW["values"][$i]["key"] = $periodItem["key"];
						$LTDbyTNW["values"][$i]["value"] = $fsDataItem->ltw_tnw;
						

						$TOLbyTNW["values"][$i]["key"] = $periodItem["key"];
						$TOLbyTNW["values"][$i]["value"] = $fsDataItem->tol_tnw;
						
						
						$TOLbyATNW["values"][$i]["key"] = $periodItem["key"];
						$TOLbyATNW["values"][$i]["value"] = $fsDataItem->tol_atnw;

					
						$totalCurrAssetsItem["values"][$i]["key"] = $periodItem["key"];
						$totalCurrAssetsItem["values"][$i]["value"] = DisplayAmount($fsDataItem->total_current_assets, $unit);

						$totalCurrLiabilitiesItem["values"][$i]["key"] = $periodItem["key"];
						$totalCurrLiabilitiesItem["values"][$i]["value"] = DisplayAmount($fsDataItem->total_current_liabilities, $unit);

						$netWorkingCapital["values"][$i]["key"] = $periodItem["key"];
						$netWorkingCapital["values"][$i]["value"] = DisplayAmount($fsDataItem->net_working_capital, $unit);

						$currentRatio["values"][$i]["key"] = $periodItem["key"];
						$currentRatio["values"][$i]["value"] = $fsDataItem->current_ratio;

						$inventoryHoldingPeriod["values"][$i]["key"] = $periodItem["key"];
						$inventoryHoldingPeriod["values"][$i]["value"] = $fsDataItem->inventory_holding_period;

						$debtorsHoldingPeriod["values"][$i]["key"] = $periodItem["key"];
						$debtorsHoldingPeriod["values"][$i]["value"] = $fsDataItem->debtor_holding_period;

						$creditorsHoldingPeriod["values"][$i]["key"] = $periodItem["key"];
						$creditorsHoldingPeriod["values"][$i]["value"] = $fsDataItem->creditor_holding_period;

						$debtEquityRatio["values"][$i]["key"] = $periodItem["key"];
						$debtEquityRatio["values"][$i]["value"] = $fsDataItem->debt_equity_ratio;

						$debtPBITDARatio["values"][$i]["key"] = $periodItem["key"];
						$debtPBITDARatio["values"][$i]["value"] = $fsDataItem->debt_pbitda_ratio;

						$intCovRatio["values"][$i]["key"] = $periodItem["key"];
						$intCovRatio["values"][$i]["value"] = $fsDataItem->interest_coverage_ratio;

						$dscr["values"][$i]["key"] = $periodItem["key"];
						$dscr["values"][$i]["value"] = $fsDataItem->dscr;

						
					}
				}
				
			}

			$financialSummary[] = $netSales;
			$financialSummary[] = $otherIncome;
			$financialSummary[] = $income;
			$financialSummary[] = $pbdita;
			$financialSummary[] = $pbditaMargin;
			$financialSummary[] = $interest;
			$financialSummary[] = $depreciation;
			$financialSummary[] = $oProfitAfterInterest;
			$financialSummary[] = $incomeExpenses;
			$financialSummary[] = $profitBeforeTax;
			$financialSummary[] = $profitAfterTax;
			$financialSummary[] = $netProfitMargin;
			$financialSummary[] = $netCashAccurals;
			$financialSummary[] = $fixedAssetsGross;
			$financialSummary[] = $fixedAssetsNet;
			$financialSummary[] = $nonCurrentAsetsExFA;
			$financialSummary[] = $tangNetworth;
			$financialSummary[] = $expInGroupCo;
			$financialSummary[] = $aTNW;
			$financialSummary[] = $longTermDebt;
			$financialSummary[] = $shortTermDebt;
			$financialSummary[] = $wCapitalBorrowing;
			$financialSummary[] = $totalOutsideLiabilities;
			$financialSummary[] = $LTDbyTNW;
			$financialSummary[] = $TOLbyTNW;
			$financialSummary[] = $TOLbyATNW;
			$financialSummary[] = $totalCurrAssetsItem;
			$financialSummary[] = $totalCurrLiabilitiesItem;
			$financialSummary[] = $netWorkingCapital;
			$financialSummary[] = $currentRatio;
			$financialSummary[] = $inventoryHoldingPeriod;
			$financialSummary[] = $debtorsHoldingPeriod;
			$financialSummary[] = $creditorsHoldingPeriod;
			$financialSummary[] = $debtEquityRatio;
			$financialSummary[] = $debtPBITDARatio;
			$financialSummary[] = $intCovRatio;
			$financialSummary[] = $dscr;



			return $financialSummary;
	}
}


if(!function_exists('GetCashFlowAnalysisFromDB')) {
	function GetCashFlowAnalysisFromDB($cfPeriods, $cfDbData, $unit = "million"){
			$prevLabel = "";	
		
			$cfAnalysis = null;

			$netProfitBeforTax = array("label" => "Net profit before taxation", "values" => null);
			$adjustmentFor = array("label" => "Adjustment for :", "values" => null);
			$depriciation = array("label" => "Depreciation", "values" => null);
			$dividendIncome = array("label" => "Dividend Income", "values" => null);
			$interestExp = array("label" => "Interest Expenses", "values" => null);
			$interestRecvd = array("label" => "Interest Income", "values" => null);
			$plOnSaleOfFAI = array("label" => "Profit / Loss on sale of fixed assets / investments", "values" => null);
			$forexGainLoss = array("label" => "Foreign exchange gain/loss", "values" => null);
			$exIncomeExpenses = array("label" => "Extraordinary income / expenses", "values" => null);
			$opBeforeWCChanges = array("label" => "Operating profit before working capital changes", "values" => null);

			$changeInCurrentAssets = array("label" => "Change in current assets", "values" => null);
			$changeInCurrentLiabilities = array("label" => "Change in current liabilities", "values" => null);

			$netCashFromOperatingActivities = array("label" => "Net cash from operating activities", "values" => null);
			$netCashFromInvestingActivities = array("label" => "Net cash from investing activities", "values" => null);
			$netCashFromFinancingActivities = array("label" => "Net cash from financing activities", "values" => null);

			$netIncreaseinCashBankBalance = array("label" => "Net increase in Cash/Bank balance", "values" => null);
			$cashBankBalanceInBegining = array("label" => "Cash/Bank balance in the begining", "values" => null);
			$cashBankBalanceAtEnd = array("label" => "Cash/Bank balance at End", "values" => null);

			
			
			
			for($i = 0 ; $i < count($cfPeriods); $i++)
			{
				$periodItem = $cfPeriods[$i];

				foreach($cfDbData as $cfDataItem)
				{
					if($cfDataItem->year == $periodItem["year"] && $periodItem["ptype"] == $cfDataItem->period_type)
					{
						$netProfitBeforTax["values"][$i]["key"] = $periodItem["key"];
						$netProfitBeforTax["values"][$i]["value"] =  DisplayAmount($cfDataItem->net_profit_before_taxation, $unit);

						$depriciation["values"][$i]["key"] = $periodItem["key"];
						$depriciation["values"][$i]["value"] =  DisplayAmount($cfDataItem->depreciation, $unit);

						$dividendIncome["values"][$i]["key"] = $periodItem["key"];
						$dividendIncome["values"][$i]["value"] =  DisplayAmount($cfDataItem->dividend_income, $unit);

						$interestExp["values"][$i]["key"] = $periodItem["key"];
						$interestExp["values"][$i]["value"] =  DisplayAmount($cfDataItem->interest_expense, $unit);

						$interestRecvd["values"][$i]["key"] = $periodItem["key"];
						$interestRecvd["values"][$i]["value"] =  DisplayAmount($cfDataItem->interest_received, $unit);

						$plOnSaleOfFAI["values"][$i]["key"] = $periodItem["key"];
						$plOnSaleOfFAI["values"][$i]["value"] =  DisplayAmount($cfDataItem->profit_loss_on_sale_of_fixed_assets, $unit);

						$forexGainLoss["values"][$i]["key"] = $periodItem["key"];
						$forexGainLoss["values"][$i]["value"] =  DisplayAmount($cfDataItem->foreign_exchange_gains_loss, $unit);

						$exIncomeExpenses["values"][$i]["key"] = $periodItem["key"];
						$exIncomeExpenses["values"][$i]["value"] =  DisplayAmount($cfDataItem->extraordinary_income_expense, $unit);

						$opBeforeWCChanges["values"][$i]["key"] = $periodItem["key"];
						$opBeforeWCChanges["values"][$i]["value"] =  DisplayAmount($cfDataItem->operating_profit_before_wc_changes, $unit);

						$changeInCurrentAssets["values"][$i]["key"] = $periodItem["key"];
						$changeInCurrentAssets["values"][$i]["value"] =  DisplayAmount($cfDataItem->changes_in_current_assets, $unit);

						$changeInCurrentLiabilities["values"][$i]["key"] = $periodItem["key"];
						$changeInCurrentLiabilities["values"][$i]["value"] =  DisplayAmount($cfDataItem->changes_in_current_liabilities, $unit);

						$netCashFromOperatingActivities["values"][$i]["key"] = $periodItem["key"];
						$netCashFromOperatingActivities["values"][$i]["value"] =  DisplayAmount($cfDataItem->net_cash_from_operating_activities, $unit);

						$netCashFromInvestingActivities["values"][$i]["key"] = $periodItem["key"];
						$netCashFromInvestingActivities["values"][$i]["value"] =  DisplayAmount($cfDataItem->net_cash_from_investing_activities, $unit);

						$netCashFromFinancingActivities["values"][$i]["key"] = $periodItem["key"];
						$netCashFromFinancingActivities["values"][$i]["value"] =  DisplayAmount($cfDataItem->net_cash_from_financing_activities, $unit);

						$netIncreaseinCashBankBalance["values"][$i]["key"] = $periodItem["key"];
						$netIncreaseinCashBankBalance["values"][$i]["value"] =  DisplayAmount($cfDataItem->net_increase_in_cash_bank_balance, $unit);

						$cashBankBalanceInBegining["values"][$i]["key"] = $periodItem["key"];
						$cashBankBalanceInBegining["values"][$i]["value"] =  DisplayAmount($cfDataItem->cash_bank_balance_in_begining, $unit);

						$cashBankBalanceAtEnd["values"][$i]["key"] = $periodItem["key"];
						$cashBankBalanceAtEnd["values"][$i]["value"] =  DisplayAmount($cfDataItem->cash_bank_balance_at_end, $unit);

					}
				}		
			}

			$cfAnalysis[] = $netProfitBeforTax;
			$cfAnalysis[] = $adjustmentFor;
			$cfAnalysis[] = $depriciation;
			$cfAnalysis[] = $dividendIncome;
			$cfAnalysis[] = $interestExp;
			$cfAnalysis[] = $interestRecvd;
			$cfAnalysis[] = $plOnSaleOfFAI;
			$cfAnalysis[] = $forexGainLoss;
			$cfAnalysis[] = $exIncomeExpenses;
			$cfAnalysis[] = $opBeforeWCChanges;

			$cfAnalysis[] = $changeInCurrentAssets;
			$cfAnalysis[] = $changeInCurrentLiabilities;

			$cfAnalysis[] = $netCashFromOperatingActivities;
			$cfAnalysis[] = $netCashFromInvestingActivities;
			$cfAnalysis[] = $netCashFromFinancingActivities;

			$cfAnalysis[] = $netIncreaseinCashBankBalance;
			$cfAnalysis[] = $cashBankBalanceInBegining;
			$cfAnalysis[] = $cashBankBalanceAtEnd;

			return $cfAnalysis;
		}
}



if(!function_exists('GetCashFlowAnalysis')) 
{
    function GetCashFlowAnalysis($balanceSheet, $profitloss, $periods, $bsAnalysis)
	{
			$prevLabel = "";	
		
			$cfAnalysis = null;

			//echo json_encode($profitloss);
			//die();

			
			$netProfitBeforTax = array("label" => "Net profit before taxation", "values" => null);
			$adjustmentFor = array("label" => "Adjustment for :", "values" => null);
			$depriciation = array("label" => "Depreciation", "values" => null);
			$dividendIncome = array("label" => "Dividend Income", "values" => null);
			$interestExp = array("label" => "Interest Expenses", "values" => null);
			$interestRecvd = array("label" => "Interest Income", "values" => null);
			$plOnSaleOfFAI = array("label" => "Profit / Loss on sale of fixed assets / investments", "values" => null);
			$forexGainLoss = array("label" => "Foreign exchange gain/loss", "values" => null);
			$exIncomeExpenses = array("label" => "Extraordinary income / expenses", "values" => null);
			$opBeforeWCChanges = array("label" => "Operating profit before working capital changes", "values" => null);

			 
			$changeInCurrentAssets = array("label" => "Change in current assets", "values" => null);
			$changeInCurrentLiabilities = array("label" => "Change in current liabilities", "values" => null);

			$netCashFromOperatingActivities = array("label" => "Net cash from operating activities", "values" => null);
			$netCashFromInvestingActivities = array("label" => "Net cash from investing activities", "values" => null);
			$netCashFromFinancingActivities = array("label" => "Net cash from financing activities", "values" => null);

			$netIncreaseinCashBankBalance = array("label" => "Net increase in Cash/Bank balance", "values" => null);
			$cashBankBalanceInBegining = array("label" => "Cash/Bank balance in the begining", "values" => null);
			$cashBankBalanceAtEnd = array("label" => "Cash/Bank balance at End", "values" => null);

			
			
			
			
			
			for($i = 0 ; $i < count($periods); $i++)
			{
				$opBeforeWCChangesAmt = 0;
				$periodItemLastYear = null;
				$periodItem = $periods[$i];

				if(count($periods) > $i+1)
				{
					$periodItemLastYear = $periods[$i+1];
				}

				$netProfitBeforTax["values"][$i]["key"] = $periodItem["key"];
				$netProfitBeforTax["values"][$i]["value"] =  GetProfitandLossLineItem($profitloss, "Profit Before tax ", $periodItem["key"]); 

				

				$adjustmentFor["values"][$i]["key"] = $periodItem["key"];
				$adjustmentFor["values"][$i]["value"] =  "";

				$depriciation["values"][$i]["key"] = $periodItem["key"];
				$depriciation["values"][$i]["value"] =  GetProfitandLossLineItem($profitloss, "Depreciation", $periodItem["key"]); 

				

				$dividendIncome["values"][$i]["key"] = $periodItem["key"];
				$dividendIncome["values"][$i]["value"] =  GetProfitandLossLineItem($profitloss, "Dividend received", $periodItem["key"]); 

				

				$interestExp["values"][$i]["key"] = $periodItem["key"];
				$interestExp["values"][$i]["value"] =  GetProfitandLossLineItem($profitloss, "Total Interest", $periodItem["key"]); 

				$interestRecvd["values"][$i]["key"] = $periodItem["key"];
				$interestRecvd["values"][$i]["value"] =  GetProfitandLossLineItem($profitloss, "Interest Income", $periodItem["key"]); 

				$plOnSaleOfFAI["values"][$i]["key"] = $periodItem["key"];
				$plOnSaleOfFAI["values"][$i]["value"] =  GetProfitandLossLineItem($profitloss, "Profit on sale of assets/ investments", $periodItem["key"]); 

				$forexGainLoss["values"][$i]["key"] = $periodItem["key"];
				$forexGainLoss["values"][$i]["value"] =  GetProfitandLossLineItem($profitloss, "Forex gains", $periodItem["key"]); 


				$extraordinaryExpenses = GetProfitandLossLineItem($profitloss, "Extraordinary Expenses ", $periodItem["key"]); 
				$extraordinaryIncome = GetProfitandLossLineItem($profitloss, "Extraordinary Income", $periodItem["key"]); 
				

				$exIncomeExpenses["values"][$i]["key"] = $periodItem["key"];
				$exIncomeExpenses["values"][$i]["value"] =  $extraordinaryExpenses - $extraordinaryIncome;

				$opBeforeWCChangesAmt+=$netProfitBeforTax["values"][$i]["value"];
				$opBeforeWCChangesAmt+=$depriciation["values"][$i]["value"];
				$opBeforeWCChangesAmt+=$dividendIncome["values"][$i]["value"];
				$opBeforeWCChangesAmt+=$interestExp["values"][$i]["value"];
				$opBeforeWCChangesAmt+=$interestRecvd["values"][$i]["value"];
				$opBeforeWCChangesAmt+=$plOnSaleOfFAI["values"][$i]["value"];
				$opBeforeWCChangesAmt+=$forexGainLoss["values"][$i]["value"];
				$opBeforeWCChangesAmt+=$exIncomeExpenses["values"][$i]["value"];
				

				$opBeforeWCChanges["values"][$i]["key"] = $periodItem["key"];
				$opBeforeWCChanges["values"][$i]["value"] =  $opBeforeWCChangesAmt;


				$totalCurrentAssets = GetBalanceSheetAnalysisLineItem($bsAnalysis, "Total current assets", $periodItem["key"]); 
				$totalCurrentAssetsLastYear = 0;

				if($periodItemLastYear != null)
				{
					$totalCurrentAssetsLastYear = GetBalanceSheetAnalysisLineItem($bsAnalysis, "Total current assets", $periodItemLastYear["key"]); 
					$changeInCurrentAssetsVal = ($totalCurrentAssets - $totalCurrentAssetsLastYear);
				}
				else{
					$changeInCurrentAssetsVal = 0;
				}

				

				$changeInCurrentAssets["values"][$i]["key"] = $periodItem["key"];
				$changeInCurrentAssets["values"][$i]["value"] =  $changeInCurrentAssetsVal;


				$totalCurrentLiabilities = GetBalanceSheetAnalysisLineItem($bsAnalysis, "Total Current Liabilities", $periodItem["key"]); 
				$totalCurrentLiabilitiesLastYear = 0;
				if($periodItemLastYear != null)
				{
					$totalCurrentLiabilitiesLastYear = GetBalanceSheetAnalysisLineItem($bsAnalysis, "Total Current Liabilities", $periodItemLastYear["key"]); 
					$changeInCurrentLiabilitiesVal = $totalCurrentLiabilities - $totalCurrentLiabilitiesLastYear;
				}
				else{
					$changeInCurrentLiabilitiesVal = 0;
				}

				

				$changeInCurrentLiabilities["values"][$i]["key"] = $periodItem["key"];
				$changeInCurrentLiabilities["values"][$i]["value"] =  $changeInCurrentLiabilitiesVal;
				
			}

			$cfAnalysis[] = $netProfitBeforTax;
			$cfAnalysis[] = $adjustmentFor;
			$cfAnalysis[] = $depriciation;
			$cfAnalysis[] = $dividendIncome;
			$cfAnalysis[] = $interestExp;
			$cfAnalysis[] = $interestRecvd;
			$cfAnalysis[] = $plOnSaleOfFAI;
			$cfAnalysis[] = $forexGainLoss;
			$cfAnalysis[] = $exIncomeExpenses;
			$cfAnalysis[] = $opBeforeWCChanges;
			$cfAnalysis[] = $changeInCurrentAssets;
			$cfAnalysis[] = $changeInCurrentLiabilities;
			



			return $cfAnalysis;
	}
}

if(!function_exists('GetBalanceSheetAnalysis')) 
{
    function GetBalanceSheetAnalysis($balanceSheet, $periods)
	{
			$prevLabel = "";	
		
			$bsAnalysis = null;

			//Liabilities
			$equityShareCapital = array("label" => "Equity Share Capital", "values" => null);
			$reserveAndSurplus = array("label" => "Reserves and Surplus", "values" => null);
			$totalEquity = array("label" => "Total Equity", "values" => null);

			$longTermBorrowings = array("label" => "Long Term Borrowings", "values" => null);
			$deferredTaxLiabilities = array("label" => "Deferred tax liabilities", "values" => null);
			$otherLiabilities = array("label" => "Other liabilities", "values" => null);
			$totalNonCurrentLiabilities = array("label" => "Total non current liabilities", "values" => null);

			$shortTermBorrowings = array("label" => "Short term Borrowings", "values" => null);
			$tradepayables = array("label" => "Trade payables", "values" => null);
			$otherCurrentLiabilities = array("label" => "Other Current Liabilities", "values" => null);
			$totalCurrentLiabilities = array("label" => "Total Current Liabilities", "values" => null);
			
			$totalEquityAndLiabilities = array("label" => "Total Equity and liabilities", "values" => null);

			//Assets

			$propertyPlantAndEquip = array("label" => "Property, Plant & Equipments", "values" => null);
			$intangibleAsstets = array("label" => "Intangible assets", "values" => null);
			$nonCurrentAsstets = array("label" => "Non current assets", "values" => null);
			$totalNonCurrentAsstets = array("label" => "Total Non current assets", "values" => null);

			$inventories = array("label" => "Inventories", "values" => null);
			$currentInvestments = array("label" => "Current Investments", "values" => null);
			$tradeReceivables = array("label" => "Trade Receivables", "values" => null);
			$cashBankBalances = array("label" => "Cash & Bank Balances", "values" => null);
			$otherCurrentAssets= array("label" => "Other  current assets", "values" => null);

			$totalCurrentAssets= array("label" => "Total current assets", "values" => null);
			$totalAssets= array("label" => "Total assets", "values" => null);


			for($i = 0 ; $i < count($periods); $i++)
			{
				$periodItem = $periods[$i];

				$shareCapitalPaidUp = GetBalanceSheetLineItem($balanceSheet, "Share Capital (Paid-up)", $periodItem["key"]);
				$shareAppMoney = GetBalanceSheetLineItem($balanceSheet, "Share Application money", $periodItem["key"]);

				$equityShareCapital["values"][$i]["key"] = $periodItem["key"];
				$equityShareCapital["values"][$i]["value"] =  $shareCapitalPaidUp + $shareAppMoney;
				
				$generalReserve = GetBalanceSheetLineItem($balanceSheet, "General Reserve", $periodItem["key"]);
				$revaluationReserve = GetBalanceSheetLineItem($balanceSheet, "Revaluation Reserve", $periodItem["key"]);
				$partnerCapital = GetBalanceSheetLineItem($balanceSheet, "Partners capital / Proprietor's capital", $periodItem["key"]); 
				$balacneInPartnersCurrentAc = GetBalanceSheetLineItem($balanceSheet, "Balance in Partners' Current A/c (+ / -)", $periodItem["key"]); 

				$sharePremium = GetBalanceSheetLineItem($balanceSheet, "Share Premium", $periodItem["key"]); 
				$capitalSubsidy = GetBalanceSheetLineItem($balanceSheet, "Capital subsidy", $periodItem["key"]); 
				$balanceInPL = GetBalanceSheetLineItem($balanceSheet, "Balance in P&L Account (+ / - )", $periodItem["key"]); 

				$reserveAndSurplus["values"][$i]["key"] = $periodItem["key"];
				$reserveAndSurplus["values"][$i]["value"] =  $generalReserve + $revaluationReserve + $partnerCapital + $balacneInPartnersCurrentAc +$sharePremium + $capitalSubsidy + $balanceInPL;

				$totalEquity["values"][$i]["key"] = $periodItem["key"];
				$totalEquity["values"][$i]["value"] =  $equityShareCapital["values"][$i]["value"] + $reserveAndSurplus["values"][$i]["value"];

				$debMaturingAfter1Year = GetBalanceSheetLineItem($balanceSheet, "Debentures maturing after 1 year", $periodItem["key"]); 
				$prefShareCapMatLessThen12Year = GetBalanceSheetLineItem($balanceSheet, "Preference share capital maturity < 12 years", $periodItem["key"]); 
				$dealersDeposit = GetBalanceSheetLineItem($balanceSheet, "Dealer's Deposit", $periodItem["key"]); 

				$termLoansFromBanks = GetBalanceSheetLineItem($balanceSheet, "Term Loans  - From Banks", $periodItem["key"]); 
				$termLoansFromFinancialInstitution = GetBalanceSheetLineItem($balanceSheet, "Term Loans - From Financial Institution", $periodItem["key"]); 
				$termDeposits = GetBalanceSheetLineItem($balanceSheet, "Term Deposits", $periodItem["key"]); 

				$longTermBorrowings["values"][$i]["key"] = $periodItem["key"];
				$longTermBorrowings["values"][$i]["value"] =  $debMaturingAfter1Year + $prefShareCapMatLessThen12Year + $dealersDeposit + $termLoansFromBanks +  $termLoansFromFinancialInstitution + $termDeposits;

				$deferredTaxLiabilities["values"][$i]["key"] = $periodItem["key"];
				$deferredTaxLiabilities["values"][$i]["value"] =  GetBalanceSheetLineItem($balanceSheet, "Deferred Tax Liability", $periodItem["key"]); 
				
				$borrowingFromSubs = GetBalanceSheetLineItem($balanceSheet, "Borrowings from subsidiaries / affiliates", $periodItem["key"]); 
				$unsecuredLoans = GetBalanceSheetLineItem($balanceSheet, "Unsecured Loans ", $periodItem["key"]); 
				$otherTermLiabilities = GetBalanceSheetLineItem($balanceSheet, "Other term liabilities", $periodItem["key"]); 

				$otherLiabilities["values"][$i]["key"] = $periodItem["key"];
				$otherLiabilities["values"][$i]["value"] =  $borrowingFromSubs + $unsecuredLoans + $otherTermLiabilities;

				$totalNonCurrentLiabilities["values"][$i]["key"] = $periodItem["key"];
				$totalNonCurrentLiabilities["values"][$i]["value"] = $longTermBorrowings["values"][$i]["value"] + $deferredTaxLiabilities["values"][$i]["value"] + $otherLiabilities["values"][$i]["value"];

				$bankBorrowings = GetBalanceSheetLineItem($balanceSheet, "Bank Borrowings - From applicant Bank", $periodItem["key"]) + GetBalanceSheetLineItem($balanceSheet, "Bank Borrowings - From other Banks", $periodItem["key"]); 
				$instOfTermLoanDeb = GetBalanceSheetLineItem($balanceSheet, "Installments of Term Loans/Debentures (due within one year)- To banks ", $periodItem["key"]); 
				$instOfTermLoanDebToOthers = GetBalanceSheetLineItem($balanceSheet, "Installments of Term Loans/Debentures (due within one year)- To Others", $periodItem["key"]); 
				$depositsDuePayable1Year = GetBalanceSheetLineItem($balanceSheet, "Deposits due / payable within a year", $periodItem["key"]); 
				$otherCurrLiaDuePayable1Year = GetBalanceSheetLineItem($balanceSheet, "Other Current Liabilities due within one year", $periodItem["key"]); 
				

				$shortTermBorrowings["values"][$i]["key"] = $periodItem["key"];
				$shortTermBorrowings["values"][$i]["value"] =  $bankBorrowings + $instOfTermLoanDeb + $instOfTermLoanDebToOthers + $depositsDuePayable1Year + $otherCurrLiaDuePayable1Year;

				$creditorsforPurchasesOthers = GetBalanceSheetLineItem($balanceSheet, "Creditors for purchases – others", $periodItem["key"]); 
				$creditorsforPurchasesGroupCompanies = GetBalanceSheetLineItem($balanceSheet, "Creditors for purchases – Group Companies", $periodItem["key"]); 
				$creditorsForExpenses = GetBalanceSheetLineItem($balanceSheet, "Creditors for expenses", $periodItem["key"]); 
				$advPaymentFromCustomers = GetBalanceSheetLineItem($balanceSheet, "Advances/ payments from customers/deposits from dealers.", $periodItem["key"]); 

				$tradepayables["values"][$i]["key"] = $periodItem["key"];
				$tradepayables["values"][$i]["value"] =  $creditorsforPurchasesOthers + $creditorsforPurchasesGroupCompanies + $creditorsForExpenses + $advPaymentFromCustomers;

				$shortTermBorrowingsFromAandG = GetBalanceSheetLineItem($balanceSheet, "Short term borrowings from Associates & Group Concerns repayable within one year", $periodItem["key"]); 
				$shortTermBorrowingsFromOthers = GetBalanceSheetLineItem($balanceSheet, "Short term borrowings from Others", $periodItem["key"]); 
				$provisionTax = GetBalanceSheetLineItem($balanceSheet, "        - Tax", $periodItem["key"]); 
				$provisionDeferredTax = GetBalanceSheetLineItem($balanceSheet, "        - Deferred tax", $periodItem["key"]); 
				$provisionsOthers = GetBalanceSheetLineItem($balanceSheet, " - Others", $periodItem["key"]); 
				$dividendsPayable = GetBalanceSheetLineItem($balanceSheet, "Dividends Payable", $periodItem["key"]); 
				$statutoryLiabilitiesDueWithin1year = GetBalanceSheetLineItem($balanceSheet, "Statutory liabilities due within one year", $periodItem["key"]); 
				
				$otherCurrentLiabilities["values"][$i]["key"] = $periodItem["key"];
				$otherCurrentLiabilities["values"][$i]["value"] =  $shortTermBorrowingsFromAandG + $shortTermBorrowingsFromOthers + $provisionTax + $provisionDeferredTax + $provisionsOthers + $dividendsPayable + $statutoryLiabilitiesDueWithin1year;

				$totalCurrentLiabilities["values"][$i]["key"] = $periodItem["key"];
				$totalCurrentLiabilities["values"][$i]["value"] =  $shortTermBorrowings["values"][$i]["value"] + $tradepayables["values"][$i]["value"] + $otherCurrentLiabilities["values"][$i]["value"];


				$totalEquityAndLiabilities["values"][$i]["key"] = $periodItem["key"];
				$totalEquityAndLiabilities["values"][$i]["value"] =  $totalEquity["values"][$i]["value"] + $totalNonCurrentLiabilities["values"][$i]["value"] + $totalCurrentLiabilities["values"][$i]["value"];
				
				$propertyPlantAndEquip["values"][$i]["key"] = $periodItem["key"];
				$propertyPlantAndEquip["values"][$i]["value"] =  (GetBalanceSheetLineItem($balanceSheet, "Gross Block ", $periodItem["key"]) - GetBalanceSheetLineItem($balanceSheet, "Less: Accumulated Depreciation", $periodItem["key"])) + GetBalanceSheetLineItem($balanceSheet, "Capital Work in progress", $periodItem["key"]); 
				
				$intangibleAsstets["values"][$i]["key"] = $periodItem["key"];
				$intangibleAsstets["values"][$i]["value"] =  (GetBalanceSheetLineItem($balanceSheet, "Goodwill, Patents & trademarks", $periodItem["key"]) + GetBalanceSheetLineItem($balanceSheet, "Miscellaneous expenditure not w/off", $periodItem["key"])) + GetBalanceSheetLineItem($balanceSheet, "Other deferred revenue expenses", $periodItem["key"]); 


				$investmentsInGroupConcerns = GetBalanceSheetLineItem($balanceSheet, "Investments in Group concerns", $periodItem["key"]); 
				$loansToGroupConcerns = GetBalanceSheetLineItem($balanceSheet, "Loans to group concerns / Advances to subsidiaries", $periodItem["key"]); 
				$investmentsInOthers = GetBalanceSheetLineItem($balanceSheet, "Investments in others", $periodItem["key"]); 
				$advToSuppOfCapitalGAndC = GetBalanceSheetLineItem($balanceSheet, "Advances to suppliers of capital goods and contractors", $periodItem["key"]); 
				$deferredReceivables = GetBalanceSheetLineItem($balanceSheet, "Deferred receivables (maturity exceeding one year)", $periodItem["key"]); 
				$debtorsMoreThan6Months = GetBalanceSheetLineItem($balanceSheet, "Debtors more than 6 months", $periodItem["key"]); 
				$othersLoansAdv = GetBalanceSheetLineItem($balanceSheet, "Others (Loans & Advances non current in nature, ICD’s etc.)", $periodItem["key"]); 
				$securityDeposits = GetBalanceSheetLineItem($balanceSheet, "Security deposits", $periodItem["key"]); 
				$depositswithGovernmentdepartments = GetBalanceSheetLineItem($balanceSheet, "Deposits with Government departments", $periodItem["key"]); 
				$deferredTaxAsset = GetBalanceSheetLineItem($balanceSheet, "Deferred Tax Asset", $periodItem["key"]); 
				$otherNoncurrentAssets = GetBalanceSheetLineItem($balanceSheet, "Other Non-current Assets", $periodItem["key"]); 

				$nonCurrentAsstets["values"][$i]["key"] = $periodItem["key"];
				$nonCurrentAsstets["values"][$i]["value"] =  $investmentsInGroupConcerns + $loansToGroupConcerns + $investmentsInOthers + $advToSuppOfCapitalGAndC + $deferredReceivables + $debtorsMoreThan6Months + $othersLoansAdv + $securityDeposits + $depositswithGovernmentdepartments + $deferredTaxAsset + $otherNoncurrentAssets;
				
				$totalNonCurrentAsstets["values"][$i]["key"] = $periodItem["key"];
				$totalNonCurrentAsstets["values"][$i]["value"] =  $propertyPlantAndEquip["values"][$i]["value"]  + $intangibleAsstets["values"][$i]["value"] + $nonCurrentAsstets["values"][$i]["value"];
				
				$rawMaterialsImported  = GetBalanceSheetLineItem($balanceSheet, " Raw Materials – Imported", $periodItem["key"]); 
				$rawMaterialsIndigenous  = GetBalanceSheetLineItem($balanceSheet, " Raw Materials – Indigenous", $periodItem["key"]); 
				$workinprocess  = GetBalanceSheetLineItem($balanceSheet, " Work in process", $periodItem["key"]); 
				$finishedGoodsInclTradedGoods  = GetBalanceSheetLineItem($balanceSheet, " Finished Goods (incl Traded Goods)", $periodItem["key"]); 
				$otherConsumableSparesImported  = GetBalanceSheetLineItem($balanceSheet, "Other consumable spares – Imported", $periodItem["key"]); 
				$otherConsumableSparesIndigenous  = GetBalanceSheetLineItem($balanceSheet, "Other consumable spares -  Indigenous", $periodItem["key"]); 

				$inventories["values"][$i]["key"] = $periodItem["key"];
				$inventories["values"][$i]["value"] =  $rawMaterialsImported + $rawMaterialsIndigenous + $workinprocess + $finishedGoodsInclTradedGoods + $otherConsumableSparesImported + $otherConsumableSparesIndigenous;


				$govtandothertrusteeSecurities  = GetBalanceSheetLineItem($balanceSheet, "Govt. and other trustee Securities", $periodItem["key"]); 
				$fixedDepositswithBanks  = GetBalanceSheetLineItem($balanceSheet, "Fixed Deposits with Banks", $periodItem["key"]); 
				$othersInvestmentsinSubsidiariesGroupCompanies  = GetBalanceSheetLineItem($balanceSheet, "Others – Investments in Subsidiaries/Group Companies", $periodItem["key"]); 

				$currentInvestments["values"][$i]["key"] = $periodItem["key"];
				$currentInvestments["values"][$i]["value"] =  $govtandothertrusteeSecurities + $fixedDepositswithBanks + $othersInvestmentsinSubsidiariesGroupCompanies;

				$domesticReceivables  = GetBalanceSheetLineItem($balanceSheet, "Domestic Receivables ", $periodItem["key"]); 
				$exportReceivables  = GetBalanceSheetLineItem($balanceSheet, "Export Receivables", $periodItem["key"]); 
				
				$tradeReceivables["values"][$i]["key"] = $periodItem["key"];
				$tradeReceivables["values"][$i]["value"] =  $domesticReceivables + $exportReceivables;

				$cashBalances  = GetBalanceSheetLineItem($balanceSheet, "Cash Balances", $periodItem["key"]); 
				$bankBalances  = GetBalanceSheetLineItem($balanceSheet, "Bank Balances", $periodItem["key"]); 

				$cashBankBalances["values"][$i]["key"] = $periodItem["key"];
				$cashBankBalances["values"][$i]["value"] =  $cashBalances + $bankBalances;

				$advToSuppOfRMSS = GetBalanceSheetLineItem($balanceSheet, "Advances to suppliers of Raw materials/Stores/Spares", $periodItem["key"]); 
				$advancePaymentofTax  = GetBalanceSheetLineItem($balanceSheet, "Advance payment of tax", $periodItem["key"]); 
				$prepaidExpenses  = GetBalanceSheetLineItem($balanceSheet, "Prepaid Expenses", $periodItem["key"]); 
				$otherAdvancesCurrentAsset  = GetBalanceSheetLineItem($balanceSheet, "Other Advances/current Asset", $periodItem["key"]); 

				$otherCurrentAssets["values"][$i]["key"] = $periodItem["key"];
				$otherCurrentAssets["values"][$i]["value"] =  $advToSuppOfRMSS + $advancePaymentofTax + $prepaidExpenses + $otherAdvancesCurrentAsset;


			
				$totalCurrentAssets["values"][$i]["key"] = $periodItem["key"];
				$totalCurrentAssets["values"][$i]["value"] =  $inventories["values"][$i]["value"] + $currentInvestments["values"][$i]["value"] + $tradeReceivables["values"][$i]["value"] + $cashBankBalances["values"][$i]["value"] + $otherCurrentAssets["values"][$i]["value"];

				$totalAssets["values"][$i]["key"] = $periodItem["key"];
				$totalAssets["values"][$i]["value"] =  $totalCurrentAssets["values"][$i]["value"] + $totalNonCurrentAsstets["values"][$i]["value"] ;

			}

			$bsAnalysis[] = array("label" => "Equity", "values" => null);
			$bsAnalysis[] = $equityShareCapital;
			$bsAnalysis[] = $reserveAndSurplus;
			$bsAnalysis[] = $totalEquity;
			$bsAnalysis[] = array("label" => "Non current Liabilities", "values" => null);
			$bsAnalysis[] = $longTermBorrowings;
			$bsAnalysis[] = $deferredTaxLiabilities;
			$bsAnalysis[] = $otherLiabilities;
			$bsAnalysis[] = $totalNonCurrentLiabilities;
			$bsAnalysis[] = array("label" => "Current Liabilities", "values" => null);
			$bsAnalysis[] = $shortTermBorrowings;
			$bsAnalysis[] = $tradepayables;
			$bsAnalysis[] = $otherCurrentLiabilities;
			$bsAnalysis[] = $totalCurrentLiabilities;
			$bsAnalysis[] = $totalEquityAndLiabilities;
			$bsAnalysis[] = array("label" => "Non Current Assets", "values" => null);
			$bsAnalysis[] = $propertyPlantAndEquip;
			$bsAnalysis[] = $intangibleAsstets;
			$bsAnalysis[] = $nonCurrentAsstets;
			$bsAnalysis[] = $totalNonCurrentAsstets;
			$bsAnalysis[] = array("label" => "Current Assets", "values" => null);
			$bsAnalysis[] = $inventories;
			$bsAnalysis[] = $currentInvestments;
			$bsAnalysis[] = $tradeReceivables;
			$bsAnalysis[] = $cashBankBalances;
			$bsAnalysis[] = $otherCurrentAssets;
			$bsAnalysis[] = $totalCurrentAssets;
			$bsAnalysis[] = $totalAssets;

			return $bsAnalysis;
	}
}


if(!function_exists('GetBalanceSheetAnalysisFromDB')) 
{
    function GetBalanceSheetAnalysisFromDB($bsPeriods, $bsDbData, $unit = "million")
	{
			$prevLabel = "";	
		
			$bsAnalysis = null;

			//Liabilities
			$equityShareCapital = array("label" => "Equity Share Capital", "values" => null);
			$reserveAndSurplus = array("label" => "Reserves and Surplus", "values" => null);
			$totalEquity = array("label" => "Total Equity", "values" => null);

			$longTermBorrowings = array("label" => "Long Term Borrowings", "values" => null);
			$deferredTaxLiabilities = array("label" => "Deferred tax liabilities", "values" => null);
			$otherLiabilities = array("label" => "Other liabilities", "values" => null);
			$totalNonCurrentLiabilities = array("label" => "Total non current liabilities", "values" => null);

			$shortTermBorrowings = array("label" => "Short term Borrowings", "values" => null);
			$tradepayables = array("label" => "Trade payables", "values" => null);
			$otherCurrentLiabilities = array("label" => "Other Current Liabilities", "values" => null);
			$totalCurrentLiabilities = array("label" => "Total Current Liabilities", "values" => null);
			
			$totalEquityAndLiabilities = array("label" => "Total Equity and liabilities", "values" => null);

			//Assets

			$propertyPlantAndEquip = array("label" => "Property, Plant & Equipments", "values" => null);
			$intangibleAsstets = array("label" => "Intangible assets", "values" => null);
			$nonCurrentAsstets = array("label" => "Non current assets", "values" => null);
			$totalNonCurrentAsstets = array("label" => "Total Non current assets", "values" => null);

			$inventories = array("label" => "Inventories", "values" => null);
			$currentInvestments = array("label" => "Current Investments", "values" => null);
			$tradeReceivables = array("label" => "Trade Receivables", "values" => null);
			$cashBankBalances = array("label" => "Cash & Bank Balances", "values" => null);
			$otherCurrentAssets= array("label" => "Other  current assets", "values" => null);

			$totalCurrentAssets= array("label" => "Total current assets", "values" => null);
			$totalAssets= array("label" => "Total assets", "values" => null);


			for($i = 0 ; $i < count($bsPeriods); $i++)
			{
				$periodItem = $bsPeriods[$i];

				foreach($bsDbData as $bsDataItem)
				{
					if($bsDataItem->year == $periodItem["year"] && $periodItem["ptype"] == $bsDataItem->period_type)
					{

						$equityShareCapital["values"][$i]["key"] = $periodItem["key"];
						$equityShareCapital["values"][$i]["value"] =  DisplayAmount($bsDataItem->equity_share_capital, $unit);
						
						$reserveAndSurplus["values"][$i]["key"] = $periodItem["key"];
						$reserveAndSurplus["values"][$i]["value"] =  DisplayAmount($bsDataItem->reserve_and_surplus, $unit);

						$totalEquity["values"][$i]["key"] = $periodItem["key"];
						$totalEquity["values"][$i]["value"] =  DisplayAmount($bsDataItem->total_equity, $unit);

						$longTermBorrowings["values"][$i]["key"] = $periodItem["key"];
						$longTermBorrowings["values"][$i]["value"] =  DisplayAmount($bsDataItem->long_term_borrowings, $unit);

						$deferredTaxLiabilities["values"][$i]["key"] = $periodItem["key"];
						$deferredTaxLiabilities["values"][$i]["value"] =  DisplayAmount($bsDataItem->deferred_tax_liability, $unit);

						$otherLiabilities["values"][$i]["key"] = $periodItem["key"];
						$otherLiabilities["values"][$i]["value"] =  DisplayAmount($bsDataItem->other_liabilities, $unit);

						$totalNonCurrentLiabilities["values"][$i]["key"] = $periodItem["key"];
						$totalNonCurrentLiabilities["values"][$i]["value"] = DisplayAmount($bsDataItem->total_non_current_liabilities, $unit);

						$shortTermBorrowings["values"][$i]["key"] = $periodItem["key"];
						$shortTermBorrowings["values"][$i]["value"] =  DisplayAmount($bsDataItem->short_term_borrowings, $unit);

					
						$tradepayables["values"][$i]["key"] = $periodItem["key"];
						$tradepayables["values"][$i]["value"] =  DisplayAmount($bsDataItem->trade_payables, $unit);
						
						$otherCurrentLiabilities["values"][$i]["key"] = $periodItem["key"];
						$otherCurrentLiabilities["values"][$i]["value"] =  DisplayAmount($bsDataItem->other_current_liabilities, $unit);

						$totalCurrentLiabilities["values"][$i]["key"] = $periodItem["key"];
						$totalCurrentLiabilities["values"][$i]["value"] =  DisplayAmount($bsDataItem->total_current_liabilities, $unit);


						$totalEquityAndLiabilities["values"][$i]["key"] = $periodItem["key"];
						$totalEquityAndLiabilities["values"][$i]["value"] =  DisplayAmount($bsDataItem->total_equity_and_liabilities, $unit);
						
						$propertyPlantAndEquip["values"][$i]["key"] = $periodItem["key"];
						$propertyPlantAndEquip["values"][$i]["value"] =  DisplayAmount($bsDataItem->property_plant_equipments, $unit);
						
						$intangibleAsstets["values"][$i]["key"] = $periodItem["key"];
						$intangibleAsstets["values"][$i]["value"] =  DisplayAmount($bsDataItem->intangible_assets, $unit);

						$nonCurrentAsstets["values"][$i]["key"] = $periodItem["key"];
						$nonCurrentAsstets["values"][$i]["value"] =  DisplayAmount($bsDataItem->non_current_assets, $unit);
						
						$totalNonCurrentAsstets["values"][$i]["key"] = $periodItem["key"];
						$totalNonCurrentAsstets["values"][$i]["value"] =  DisplayAmount($bsDataItem->total_non_current_assets, $unit);
						
					
						$inventories["values"][$i]["key"] = $periodItem["key"];
						$inventories["values"][$i]["value"] =  DisplayAmount($bsDataItem->inventories, $unit);

						$currentInvestments["values"][$i]["key"] = $periodItem["key"];
						$currentInvestments["values"][$i]["value"] =  DisplayAmount($bsDataItem->current_investments, $unit);

						$tradeReceivables["values"][$i]["key"] = $periodItem["key"];
						$tradeReceivables["values"][$i]["value"] =  DisplayAmount($bsDataItem->trade_receivables, $unit);

						$cashBankBalances["values"][$i]["key"] = $periodItem["key"];
						$cashBankBalances["values"][$i]["value"] =  DisplayAmount($bsDataItem->cash_bank_balance, $unit);

						$otherCurrentAssets["values"][$i]["key"] = $periodItem["key"];
						$otherCurrentAssets["values"][$i]["value"] =  DisplayAmount($bsDataItem->other_current_assets, $unit);
					
						$totalCurrentAssets["values"][$i]["key"] = $periodItem["key"];
						$totalCurrentAssets["values"][$i]["value"] =  DisplayAmount($bsDataItem->total_current_assets, $unit);

						$totalAssets["values"][$i]["key"] = $periodItem["key"];
						$totalAssets["values"][$i]["value"] =  DisplayAmount($bsDataItem->total_assets, $unit);

					}
				}
			}

			$bsAnalysis[] = array("label" => "Equity", "values" => null);
			$bsAnalysis[] = $equityShareCapital;
			$bsAnalysis[] = $reserveAndSurplus;
			$bsAnalysis[] = $totalEquity;
			$bsAnalysis[] = array("label" => "Non current Liabilities", "values" => null);
			$bsAnalysis[] = $longTermBorrowings;
			$bsAnalysis[] = $deferredTaxLiabilities;
			$bsAnalysis[] = $otherLiabilities;
			$bsAnalysis[] = $totalNonCurrentLiabilities;
			$bsAnalysis[] = array("label" => "Current Liabilities", "values" => null);
			$bsAnalysis[] = $shortTermBorrowings;
			$bsAnalysis[] = $tradepayables;
			$bsAnalysis[] = $otherCurrentLiabilities;
			$bsAnalysis[] = $totalCurrentLiabilities;
			$bsAnalysis[] = $totalEquityAndLiabilities;
			$bsAnalysis[] = array("label" => "Non Current Assets", "values" => null);
			$bsAnalysis[] = $propertyPlantAndEquip;
			$bsAnalysis[] = $intangibleAsstets;
			$bsAnalysis[] = $nonCurrentAsstets;
			$bsAnalysis[] = $totalNonCurrentAsstets;
			$bsAnalysis[] = array("label" => "Current Assets", "values" => null);
			$bsAnalysis[] = $inventories;
			$bsAnalysis[] = $currentInvestments;
			$bsAnalysis[] = $tradeReceivables;
			$bsAnalysis[] = $cashBankBalances;
			$bsAnalysis[] = $otherCurrentAssets;
			$bsAnalysis[] = $totalCurrentAssets;
			$bsAnalysis[] = $totalAssets;

			return $bsAnalysis;
	}
}

 if(!function_exists('GetBalanceSheetData')) {
    function GetBalanceSheetData($jsonData, $type)
    {
        $bsData = null;

     

        if(isset($jsonData->status) && $jsonData->status == true && isset($jsonData->body) && is_array($jsonData->body->financials) && count($jsonData->body->financials) > 0)
        {
          
            foreach($jsonData->body->financials as $bodydata)
            {
             
                
                if($bodydata->fintype == $type || count($jsonData->body->financials) == 1)
                {
                   
                    $body = $bodydata;

                    if(isset($body->periods) && $body->periods != null && count($body->periods) > 0)
                    {
                     
                        foreach ($body->periods as $period) {
                            
                            $_period = array(
                                "ptype" => $period->ptype,
                                "year" => $period->year,
                                "key" => $period->key,
                                "datattype" => "INR"
                            );

                            $bsData["periods"][] = $_period;
                        }
                    }

                    if(isset($body->components) && $body->components != null && count($body->components) > 0)
                    {
                        foreach ($body->components as $component) {

                            if($component->code == "BS")
                            {
                                if(isset($component->items) && $component->items != null && count($component->items) > 0)
                                {
                                    foreach ($component->items as $finitem) {

                                        $_lineitem = array(
                                            //"classname" => $finitem->classification->classname,
                                            //"subclassname" => $finitem->classification->subclassname,
                                            "label" => $finitem->name,
                                            "values" => $finitem->values,
                                            //"calculatedvalues" => $finitem->calculatedvalues,
                                        );

                                         $_lineitem = GetBSLineItemData($finitem->name, $finitem->values);
            
                                           $bsData["lineitems"][] = $_lineitem;

                                    }
                                }
                               
                            }

                        }
                    }
                    

                }
            }
        }

        //echo json_encode($bsData);
        //die();

        return $bsData;
    }
  }


  if(!function_exists('GetIncomeStatementData')) {
    function GetIncomeStatementData($jsonData, $type)
    {
        $plData = null;

        if(isset($jsonData->status) && $jsonData->status == true && isset($jsonData->body) && is_array($jsonData->body->financials) && count($jsonData->body->financials) > 0)
        {
             foreach($jsonData->body->financials as $bodydata)
            {
                if($bodydata->fintype == $type  || count($jsonData->body->financials) == 1)
                {
                    $body = $bodydata;

                    if(isset($body->periods) && $body->periods != null && count($body->periods) > 0)
                    {
                        foreach ($body->periods as $period) {
                            
                            $_period = array(
                                "ptype" => $period->ptype,
                                "year" => $period->year,
                                "key" => $period->key,
                                "datattype" => "INR"//$period->datatype->datattype
                            );

                            $plData["periods"][] = $_period;
                        }
                    }

                    if(isset($body->components) && $body->components != null && count($body->components) > 0)
                    {
                        foreach ($body->components as $component) {

                            if($component->code == "IS")
                            {
                                if(isset($component->items) && $component->items != null && count($component->items) > 0)
                                {
                                    foreach ($component->items as $finitem) {

                                        $_lineitem = array(
                                            //"componenttype" => $finitem->classification->componenttype,
                                            //"classname" => $finitem->classification->classname,
                                            //"subclassname" => $finitem->classification->subclassname,
                                            "label" => $finitem->name,
                                            "values" => $finitem->values,
                                            //"calculatedvalues" => $finitem->calculatedvalues,
                                        );

                                         $_lineitem = GetISLineItemData($finitem->name, $finitem->values);
            
                                           $plData["lineitems"][] = $_lineitem;

                                    }
                                }
                               
                            }

                        }
                    }
                    

                }
            }
        }

        
		//echo json_encode($plData);

        return $plData;
    }
  }
  
 
  if(!function_exists('GetProfitAndLossAnalysisNew')) {
    function GetProfitAndLossAnalysisNew($profitAndLoss, $periods){

		$plAnalysis = null;

		$particulars = array("label" => "Particulars", "values" => null);
		$revenueFromOperations = array("label" => "Revenue from Operations", "values" => null);
		$otherIncome = array("label" => "Other Income", "values" => null);
		$totalIncome = array("label" => "Total Income", "values" => null);
		$expenses = array("label" => "Expenses", "values" => null);
		$costOfMaterialsPurchased = array("label" => "Cost of Material Purchased", "values" => null);
		$changesInInv = array("label" => "Changes in inventories of finished and semi-finished goods, stock in trade and work in progress", "values" => null);
		$employeeBenefitExp = array("label" => "Employee Benefits Expense", "values" => null);
		$financeCost = array("label" => "Finance Costs", "values" => null);
		$depreciation = array("label" => "Depreciation and amortisation expense", "values" => null);
		$otherExp = array("label" => "Other Expenses", "values" => null);
		$totalExp = array("label" => "Total Expenses", "values" => null);
		$profitBeforeTax = array("label" => "Profit before Tax", "values" => null);
		$taxExpenses = array("label" => "Tax Expenses", "values" => null);
		$currentTax = array("label" => "Current tax", "values" => null);
		$deferredTax = array("label" => "Deferred tax", "values" => null);
		$profitForTheYear = array("label" => "Profit for the year", "values" => null);

		for($i = 0 ; $i < count($periods); $i++)
			{
				$periodItem = $periods[$i];
				
				$revenueFromOperations["values"][$i]["key"] = $periodItem["key"];
				$revenueFromOperations["values"][$i]["value"] =  GetProfitandLossLineItem($profitAndLoss, "Total Operating Income", $periodItem["key"]); 

				$otherIncome["values"][$i]["key"] = $periodItem["key"];
				$otherIncome["values"][$i]["value"] =  GetProfitandLossLineItem($profitAndLoss, "Total non-operating Income", $periodItem["key"]); 

				$totalIncomeVal = $revenueFromOperations["values"][$i]["value"] + $otherIncome["values"][$i]["value"];

				$totalIncome["values"][$i]["key"] = $periodItem["key"];
				$totalIncome["values"][$i]["value"] =  $totalIncomeVal;

				$rmImported = GetProfitandLossLineItem($profitAndLoss, "                        i) Imported", $periodItem["key"]); 
				$rmIndigenous =  GetProfitandLossLineItem($profitAndLoss, "                        ii) Indigenous", $periodItem["key"]); 

				$osImported = GetProfitandLossLineItem($profitAndLoss, "                        i) Other Spares Imported", $periodItem["key"]); 
				$osIndigenous =  GetProfitandLossLineItem($profitAndLoss, "                        ii) Other Spares Indigenous", $periodItem["key"]); 

				$costOfMaterialsPurchasedVal = $rmImported+$rmIndigenous+$osImported+$osIndigenous;

				$costOfMaterialsPurchased["values"][$i]["key"] = $periodItem["key"];
				$costOfMaterialsPurchased["values"][$i]["value"] = $costOfMaterialsPurchasedVal;

				$clStockOfWip = GetProfitandLossLineItem($profitAndLoss, "Less: Cl. Stock of WIP", $periodItem["key"]); 
				$clStockOfFG = GetProfitandLossLineItem($profitAndLoss, "Less: Closing Stock of Finished Goods", $periodItem["key"]); 

				$opStockOfWip = GetProfitandLossLineItem($profitAndLoss, "Add: Op. Stock of WIP", $periodItem["key"]); 
				$opStockOfFG = GetProfitandLossLineItem($profitAndLoss, "Add Opening Stock of Finished Goods", $periodItem["key"]); 
				
				$changeInInvVal = -($clStockOfWip + $clStockOfFG - $opStockOfWip - $opStockOfFG);
				$changesInInv["values"][$i]["key"] = $periodItem["key"];
				$changesInInv["values"][$i]["value"] = $changeInInvVal;

				$employeeBenefitExp["values"][$i]["key"] = $periodItem["key"];
				$employeeBenefitExp["values"][$i]["value"] =  GetProfitandLossLineItem($profitAndLoss, "Salary & Staff Expenses", $periodItem["key"]); 

				$financeCost["values"][$i]["key"] = $periodItem["key"];
				$financeCost["values"][$i]["value"] =  GetProfitandLossLineItem($profitAndLoss, "Total Interest", $periodItem["key"]); 

				$depreciation["values"][$i]["key"] = $periodItem["key"];
				$depreciation["values"][$i]["value"] =  GetProfitandLossLineItem($profitAndLoss, "Depreciation", $periodItem["key"]); 

				$powerAndFuel = GetProfitandLossLineItem($profitAndLoss, "Power and fuel ", $periodItem["key"]); 
				$directLabourAndWages = GetProfitandLossLineItem($profitAndLoss, "Direct labour and wages", $periodItem["key"]); 
				$otherManfExp = GetProfitandLossLineItem($profitAndLoss, "Other manufacturing expenses", $periodItem["key"]); 
				$badDebts = GetProfitandLossLineItem($profitAndLoss, "Bad Debts", $periodItem["key"]); 
				$sellingGenAdminExp = GetProfitandLossLineItem($profitAndLoss, "Selling, Gen. & Administration Exp", $periodItem["key"]); 

				$otherAdminExp = GetProfitandLossLineItem($profitAndLoss, "Other Administration Exp", $periodItem["key"]); 
				

				$totalNonOperatingExp = GetProfitandLossLineItem($profitAndLoss, "Total Non-operating expenses", $periodItem["key"]); 

				$otherExpVal = $powerAndFuel + $directLabourAndWages + $otherManfExp + $badDebts + $sellingGenAdminExp + $totalNonOperatingExp + $otherAdminExp;

				$otherExp["values"][$i]["key"] = $periodItem["key"];
				$otherExp["values"][$i]["value"] = $otherExpVal;

				$totalExpVal = $costOfMaterialsPurchasedVal + $changeInInvVal + $employeeBenefitExp["values"][$i]["value"] + $financeCost["values"][$i]["value"]
				+ $depreciation["values"][$i]["value"] + $otherExpVal;

				$totalExp["values"][$i]["key"] = $periodItem["key"];
				$totalExp["values"][$i]["value"] = $totalExpVal;

				

				$profitBeforeTaxVal = $totalIncomeVal - $totalExpVal;

				$profitBeforeTax["values"][$i]["key"] = $periodItem["key"];
				$profitBeforeTax["values"][$i]["value"] = $profitBeforeTaxVal;


				$currentTax["values"][$i]["key"] = $periodItem["key"];
				$currentTax["values"][$i]["value"] =  GetProfitandLossLineItem($profitAndLoss, "Current", $periodItem["key"]); 

				$deferredTax["values"][$i]["key"] = $periodItem["key"];
				$deferredTax["values"][$i]["value"] =  GetProfitandLossLineItem($profitAndLoss, "Deferred", $periodItem["key"]); 

				$profitForTheYear["values"][$i]["key"] = $periodItem["key"];
				$profitForTheYear["values"][$i]["value"] = $profitBeforeTaxVal -  $currentTax["values"][$i]["value"]  - $deferredTax["values"][$i]["value"];
			}


		$plAnalysis[] = $particulars;
		$plAnalysis[] = $revenueFromOperations;
		$plAnalysis[] = $otherIncome;
		$plAnalysis[] = $totalIncome;
		$plAnalysis[] = $expenses;
		$plAnalysis[] = $costOfMaterialsPurchased;
		$plAnalysis[] = $changesInInv;
		$plAnalysis[] = $employeeBenefitExp;
		$plAnalysis[] = $financeCost;
		$plAnalysis[] = $depreciation;
		$plAnalysis[] = $otherExp;
		$plAnalysis[] = $totalExp;
		$plAnalysis[] = $profitBeforeTax;
		$plAnalysis[] = $taxExpenses;
		$plAnalysis[] = $currentTax;
		$plAnalysis[] = $deferredTax;
		$plAnalysis[] = $profitForTheYear;


		return $plAnalysis;
	}
}


  if(!function_exists('GetProfitAndLossAnalysis')) {
    function GetProfitAndLossAnalysis($profitAndLoss, $periods){
			$prevLabel = "";	
		
			$plAnalysis = null;
			$totalIncome = array("label" => "Total Income", "values" => null);
			$costOfMP = array("label" => "Cost of Material Purchased", "values" => null);
			$changeInInv = array("label" => "Changes in inventories of finished and semi-finished goods, stock in trade and work in progress", "values" => null);
			$otherExpenses = array("label" => "Other Expenses", "values" => null);
			$totalExpenses = array("label" => "Total Expenses", "values" => null);
			$profitBeforeTax = array("label" => "Profit Before Tax", "values" => null);
			$profitAfterTax = array("label" => "Profit After Tax", "values" => null);

			foreach($profitAndLoss as $item)
			{
				if($item["label"] == "Total Operating Income")
				{
					$plAnalysis[] = array("label" => "Revenue from Operations", "values" => $item["values"]);
					if($totalIncome["values"] != null)
					{
						for($i = 0 ; $i < count($totalIncome["values"]); $i++)
						{
							$totalIncome["values"][$i]["value"]+= $item["values"][$i]["value"];
						}
					}
					else
					{
						$totalIncome["values"] = $item["values"];
					}
					
				}
				else if ($item["label"] == "Total non-operating Income")
				{
					$plAnalysis[] = array("label" => "Other Income", "values" => $item["values"]);
					if($totalIncome["values"] != null)
					{
						for($i = 0 ; $i < count($totalIncome["values"]); $i++)
						{
							$totalIncome["values"][$i]["value"]+= $item["values"][$i]["value"];
						}
					}
					else
					{
						$totalIncome["values"] = $item["values"];
					}

					$plAnalysis[] = array("label" => "Total Income", "values" => $totalIncome["values"]);
				}
				else if ($item["label"] == "Raw materials consumed " && $prevLabel != "Raw materials consumed ")
				{
					$prevLabel = $item["label"];
					
				}
				else if ($prevLabel == "Raw materials consumed " && trim($item["label"]) == "i) Imported")
				{
					if($costOfMP["values"] != null)
					{
						for($i = 0 ; $i < count($costOfMP["values"]); $i++)
						{
							$costOfMP["values"][$i]["value"]+= $item["values"][$i]["value"];
						}
					}
					else
					{
						$costOfMP["values"] = $item["values"];
					}

				}
				else if ($prevLabel == "Raw materials consumed " && trim($item["label"]) == "ii) Indigenous")
				{

					if($costOfMP["values"] != null)
					{
						for($i = 0 ; $i < count($costOfMP["values"]); $i++)
						{
							if(isset($item["values"][$i]["value"]))
							{
								$costOfMP["values"][$i]["value"]+= $item["values"][$i]["value"];
							}
						}
					}
					else
					{
						$costOfMP["values"] = $item["values"];
					}
				}
				else if (trim($item["label"]) == "Other Spares consumed" && trim($prevLabel) != "Other Spares consumed")
				{
					$prevLabel = $item["label"];
				}
				else if (trim($prevLabel) == "Other Spares consumed" && trim($item["label"]) == "i) Other Spares Imported")
				{
					if($costOfMP["values"] != null)
					{
						for($i = 0 ; $i < count($costOfMP["values"]); $i++)
						{
							$costOfMP["values"][$i]["value"]+= $item["values"][$i]["value"];
						}
					}
					else
					{
						$costOfMP["values"] = $item["values"];
					}
				}
				else if (trim($prevLabel) == "Other Spares consumed" && trim($item["label"]) == "ii) Other Spares Indigenous")
				{
					if($costOfMP["values"] != null)
					{
						for($i = 0 ; $i < count($costOfMP["values"]); $i++)
						{
							$costOfMP["values"][$i]["value"]+= $item["values"][$i]["value"];
						}
					}
					else
					{
						$costOfMP["values"] = $item["values"];
					}

					$plAnalysis[] = array("label" => "Cost of Material Purchased", "values" => $costOfMP["values"]);

					if($totalExpenses["values"] != null)
					{
						for($i = 0 ; $i < count($costOfMP["values"]); $i++)
						{
							$totalExpenses["values"][$i]["value"]+= $costOfMP["values"][$i]["value"];
						}
					}
					else
					{
						$totalExpenses["values"] = $costOfMP["values"];
					}

					$prevLabel = "";
				}
				else if ($item["label"] == "Less: Cl. Stock of WIP")
				{
					if($changeInInv["values"] != null)
					{
						for($i = 0 ; $i < count($changeInInv["values"]); $i++)
						{
							$changeInInv["values"][$i]["value"]+= $item["values"][$i]["value"];
						}
					}
					else
					{
						$changeInInv["values"] = $item["values"];
					}
				}
				else if ($item["label"] == "Less: Closing Stock  of Finished Goods")
				{
					if($changeInInv["values"] != null)
					{
						for($i = 0 ; $i < count($changeInInv["values"]); $i++)
						{
							$changeInInv["values"][$i]["value"]+= $item["values"][$i]["value"];
						}
					}
					else
					{
						$changeInInv["values"] = $item["values"];
					}

				}
				else if ($item["label"] == "Add: Op. Stock of WIP")
				{
					if($changeInInv["values"] != null)
					{
						for($i = 0 ; $i < count($changeInInv["values"]); $i++)
						{
							$changeInInv["values"][$i]["value"]-= $item["values"][$i]["value"];
						}
					}
					else
					{
						$changeInInv["values"] = $item["values"];
					}

				}
				else if ($item["label"] == "Add: Opening Stock of Finished Goods")
				{
					if($changeInInv["values"] != null)
					{
						for($i = 0 ; $i < count($changeInInv["values"]); $i++)
						{
							if(isset($item["values"][$i]["value"]))
							{
								$changeInInv["values"][$i]["value"]-= $item["values"][$i]["value"];
							}
						}
					}
					else
					{
						$changeInInv["values"] = $item["values"];
					}

					$plAnalysis[] = array("label" => "Changes in inventories of finished and semi-finished goods, stock in trade and work in progress", "values" => $changeInInv["values"]);
					
					for($i = 0 ; $i < count($periods); $i++)
					{
						if(isset($changeInInv["values"][$i]))
						{
							$totalExpenses["values"][$i]+= $changeInInv["values"][$i];
						}
					}
				}
				else if ($item["label"] == "Salary & Staff Expenses")
				{
					$plAnalysis[] = array("label" => "Employee Benefits Expense", "values" => $item["values"]);

					for($i = 0 ; $i < count($periods); $i++)
					{
						if(isset($item["values"][$i]))
						{
							$totalExpenses["values"][$i]+= $item["values"][$i];
						}
					}
					
				}
				else if ($item["label"] == "Total Interest")
				{
					$plAnalysis[] = array("label" => "Finance Costs", "values" => $item["values"]);

					for($i = 0 ; $i < count($periods); $i++)
					{
						if(isset($item["values"][$i]))
						{
							$totalExpenses["values"][$i]+= $item["values"][$i];
						}
					}
					
				}
				else if ($item["label"] == "Depreciation")
				{
					$plAnalysis[] = array("label" => "Depreciation and amortisation expense", "values" => $item["values"]);

					for($i = 0 ; $i < count($periods); $i++)
					{
						if(isset($item["values"][$i]))
						{
							$totalExpenses["values"][$i]+= $item["values"][$i];
						}
					}
					
				}
				else if ($item["label"] == "Power and fuel ")
				{
					if($otherExpenses["values"] != null)
					{
						for($i = 0 ; $i < count($otherExpenses["values"]); $i++)
						{
							if(isset($item["values"][$i]))
							{
								$otherExpenses["values"][$i]["value"]+= $item["values"][$i]["value"];
							}
						}
					}
					else
					{
						$otherExpenses["values"] = $item["values"];
					}
				}
				else if ($item["label"] == "Direct labour and wages")
				{
					if($otherExpenses["values"] != null)
					{
						for($i = 0 ; $i < count($otherExpenses["values"]); $i++)
						{
							if(isset($item["values"][$i]))
							{
								$otherExpenses["values"][$i]["value"]+= $item["values"][$i]["value"];
							}
						}
					}
					else
					{
						$otherExpenses["values"] = $item["values"];
					}
				}
				else if ($item["label"] == "Other manufacturing expenses")
				{
					if($otherExpenses["values"] != null)
					{
						for($i = 0 ; $i < count($otherExpenses["values"]); $i++)
						{
							if(isset($item["values"][$i]))
							{
								$otherExpenses["values"][$i]["value"]+= $item["values"][$i]["value"];
							}
						}
					}
					else
					{
						$otherExpenses["values"] = $item["values"];
					}
				}
				else if ($item["label"] == "Bad Debts")
				{
					if($otherExpenses["values"] != null)
					{
						for($i = 0 ; $i < count($otherExpenses["values"]); $i++)
						{
							if(isset($item["values"][$i]))
							{
								$otherExpenses["values"][$i]["value"]+= $item["values"][$i]["value"];
							}
						}
					}
					else
					{
						$otherExpenses["values"] = $item["values"];
					}
				}
				else if ($item["label"] == "Selling, Gen. & Administration Exp")
				{
					if($otherExpenses["values"] != null)
					{
						for($i = 0 ; $i < count($otherExpenses["values"]); $i++)
						{
							$otherExpenses["values"][$i]["value"]+= $item["values"][$i]["value"];
						}
					}
					else
					{
						$otherExpenses["values"] = $item["values"];
					}
				}
				else if ($item["label"] == "Total Non-operating expenses")
				{
					if($otherExpenses["values"] != null)
					{
						for($i = 0 ; $i < count($otherExpenses["values"]); $i++)
						{
							$otherExpenses["values"][$i]["value"]+= $item["values"][$i]["value"];
						}
					}
					else
					{
						$otherExpenses["values"] = $item["values"];
					}

					$plAnalysis[] = array("label" => "Other Expenses", "values" => $otherExpenses["values"]);

					for($i = 0 ; $i < count($periods); $i++)
					{
						$totalExpenses["values"][$i]+= $otherExpenses["values"][$i];
					}

					$plAnalysis[] = array("label" => "Total Expenses", "values" => $totalExpenses["values"]);

					for($i = 0 ; $i < count($periods); $i++)
					{
						$profitBeforeTax["values"][$i]["key"] = $totalIncome["values"][$i]["key"];
						$profitBeforeTax["values"][$i]["value"] = $totalIncome["values"][$i]["value"] - $totalExpenses["values"][$i]["value"];
					}

					$plAnalysis[] = array("label" => "Profit Before Tax", "values" => $profitBeforeTax["values"]);
				}
				else if ($item["label"] == "Provision for taxation:" && $prevLabel != "Provision for taxation:")
				{
					$prevLabel = $item["label"];
					
				}
				else if ($prevLabel == "Provision for taxation:" && trim($item["label"]) == "Current")
				{
					$plAnalysis[] = array("label" => "Current tax", "values" => $item["values"]);
				}
				else if ($prevLabel == "Provision for taxation:" && trim($item["label"]) == "Deferred")
				{
					$plAnalysis[] = array("label" => "Deferred tax", "values" => $item["values"]);

					$prevLabel = "";

					
					for($i = 0 ; $i < count($periods); $i++)
					{
						$profitAfterTax["values"][$i]["key"] = $profitBeforeTax["values"][$i]["key"];
						$profitAfterTax["values"][$i]["value"] = $profitBeforeTax["values"][$i]["value"] - $totalExpenses["values"][$i]["value"];
					}

					$plAnalysis[] = array("label" => "Profit After Tax", "values" => $profitAfterTax["values"]);


				}
				
				

				
			}

			return $plAnalysis;
		}
	}

	if(!function_exists('GetProfitAndLossAnalysisFromDB')) {
		function GetProfitAndLossAnalysisFromDB($plPeriods, $plDbData, $unit = "million"){
				$prevLabel = "";	
			
				$plAnalysis = null;

				$revenueFromOps = array("label" => "Revenue from Operations", "values" => null);
				$otherIncome = array("label" => "Other Income", "values" => null);
				$totalIncome = array("label" => "Total Income", "values" => null);
				

				$costOfMP = array("label" => "Cost of Material Purchased", "values" => null);
				$changeInInv = array("label" => "Changes in inventories of finished and semi-finished goods, stock in trade and work in progress", "values" => null);
				$employeeBenefitExp = array("label" => "Employee Benefits Expense", "values" => null);
				$financeCosts = array("label" => "Finance Costs", "values" => null);
				$depAndAmortisationExp = array("label" => "Depreciation and amortisation expense", "values" => null);
				$otherExpenses = array("label" => "Other Expenses", "values" => null);
				$totalExpenses = array("label" => "Total Expenses", "values" => null);
				$profitBeforeTax = array("label" => "Profit Before Tax", "values" => null);
				$currentTax = array("label" => "Current Tax", "values" => null);
				$deferredTax = array("label" => "Deferred Tax", "values" => null);
				$profitAfterTax = array("label" => "Profit After Tax", "values" => null);
				

	
				
				for($i = 0 ; $i < count($plPeriods); $i++)
				{
					$periodItem = $plPeriods[$i];

					foreach($plDbData as $plDataItem)
					{
						if($plDataItem->year == $periodItem["year"] && $periodItem["ptype"] == $plDataItem->period_type)
						{

							$revenueFromOps["values"][$i]["key"] = $periodItem["key"];
							$revenueFromOps["values"][$i]["value"] =  DisplayAmount($plDataItem->revenue_from_operations, $unit);

							$otherIncome["values"][$i]["key"] = $periodItem["key"];
							$otherIncome["values"][$i]["value"] =  DisplayAmount($plDataItem->other_income, $unit);

							$totalIncome["values"][$i]["key"] = $periodItem["key"];
							$totalIncome["values"][$i]["value"] =  DisplayAmount($plDataItem->total_income, $unit);

							$costOfMP["values"][$i]["key"] = $periodItem["key"];
							$costOfMP["values"][$i]["value"] =  DisplayAmount($plDataItem->cost_of_material_purchased, $unit);

							$changeInInv["values"][$i]["key"] = $periodItem["key"];
							$changeInInv["values"][$i]["value"] =  DisplayAmount($plDataItem->changes_in_inventories, $unit);

							$employeeBenefitExp["values"][$i]["key"] = $periodItem["key"];
							$employeeBenefitExp["values"][$i]["value"] =  DisplayAmount($plDataItem->employee_benefits_expenses, $unit);

							$financeCosts["values"][$i]["key"] = $periodItem["key"];
							$financeCosts["values"][$i]["value"] =  DisplayAmount($plDataItem->finance_cost, $unit);

							$depAndAmortisationExp["values"][$i]["key"] = $periodItem["key"];
							$depAndAmortisationExp["values"][$i]["value"] =  DisplayAmount($plDataItem->depriciation_and_amortisation_expense, $unit);

							$otherExpenses["values"][$i]["key"] = $periodItem["key"];
							$otherExpenses["values"][$i]["value"] =  DisplayAmount($plDataItem->other_expenses, $unit);

							$totalExpenses["values"][$i]["key"] = $periodItem["key"];
							$totalExpenses["values"][$i]["value"] =  DisplayAmount($plDataItem->total_expenses, $unit);

							$profitBeforeTax["values"][$i]["key"] = $periodItem["key"];
							$profitBeforeTax["values"][$i]["value"] =  DisplayAmount($plDataItem->profit_before_tax, $unit);

							$currentTax["values"][$i]["key"] = $periodItem["key"];
							$currentTax["values"][$i]["value"] =  DisplayAmount($plDataItem->current_tax, $unit);

							$deferredTax["values"][$i]["key"] = $periodItem["key"];
							$deferredTax["values"][$i]["value"] =  DisplayAmount($plDataItem->deferred_tax, $unit);

							$profitAfterTax["values"][$i]["key"] = $periodItem["key"];
							$profitAfterTax["values"][$i]["value"] =  DisplayAmount($plDataItem->profit_after_tax, $unit);

						}
					}		
				}

				$plAnalysis[] = $revenueFromOps;
				$plAnalysis[] = $otherIncome;
				$plAnalysis[] = $totalIncome;
				$plAnalysis[] = array("label" => "Expenses", "values" => null);

				$plAnalysis[] = $costOfMP;
				$plAnalysis[] = $changeInInv;
				$plAnalysis[] = $employeeBenefitExp;
				$plAnalysis[] = $financeCosts;
				$plAnalysis[] = $depAndAmortisationExp;
				$plAnalysis[] = $otherExpenses;
				$plAnalysis[] = $totalExpenses;
				$plAnalysis[] = $profitBeforeTax;
				$plAnalysis[] = array("label" => "Tax Expenses", "values" => null);
				$plAnalysis[] = $currentTax;
				$plAnalysis[] = $deferredTax;
				$plAnalysis[] = $profitAfterTax;
	
				return $plAnalysis;
			}
	}

 if(!function_exists('GetProfitAndLossNew')) {
    function GetProfitAndLossNew($jsonData, $type, $periods){

        $profitAndLoss = null;
		
        $incomestatement = GetIncomeStatementData($jsonData, $type);
		$isLineItems = $incomestatement["lineitems"];
		
		$sales = array("label" => "Sales", "values" => null);
		$salesDomestic = array("label" => "- Domestic", "values" => null);
		$salesExport = array("label" => "- Export", "values" => null);
		$salesSubTotal = array("label" => "Sub Total", "values" => null, "type" => "sales");
		$exciseDuty = array("label" => "Less Excise Duty (if applicable)", "values" => null);
		$netSales = array("label" => "Net Sales", "values" => null);

		$riseFallInNetSales = array("label" => "% wise rise/fall in net sales as compared to previous year", 
			"values" => null);
		$otherIncomes = array("label" => "Other Incomes", "values" => null);
		$exportIncentives = array("label" => "Export Incentive", "values" => null);
		$otherIncome = array("label" => "Other Income", "values" => null);
		$totalOperatingIncome = array("label" => "Total Operating Income", "values" => null);

		$costOfSales = array("label" => "Cost of Sales", "values" => null);
		$rawMatConsumed = array("label" => "Raw materials consumed ", "values" => null);
		$rawMatImported = array("label" => "                        i) Imported", "values" => null);
		$rawMatIndigenous = array("label" => "                        ii) Indigenous", "values" => null);

		$otherSparesConsumed = array("label" => "Other Spares consumed ", "values" => null);
		$otherSparesImported = array("label" => "                        i) Other Spares Imported", "values" => null);
		$otherSparesIndigenous = array("label" => "                        ii) Other Spares Indigenous", "values" => null);

		$powerAndFuel = array("label" => "Power and fuel ", "values" => null);
		$directLabourAndWages = array("label" => "Direct labour and wages", "values" => null);
		$otherManfExp = array("label" => "Other manufacturing expenses", "values" => null);
		$depriciation = array("label" => "Depreciation", "values" => null);
		$costOfSalesSubTotal = array("label" => "Sub Total", "values" => null, "type" => "cost of sale");
		
		$opStockWIP = array("label" => "Add: Op. Stock of WIP", "values" => null);
		$clStockWIP = array("label" => "Less: Cl. Stock of WIP", "values" => null);
		$totalCostOfProd = array("label" => "Total Cost of Production", "values" => null);

		$opStockOfFG = array("label" => "Add Opening Stock of Finished Goods", "values" => null);
		$clStockOfFG = array("label" => "Less: Closing Stock  of Finished Goods", "values" => null);
		$totalCostOfSales = array("label" => "Total Cost of Sales", "values" => null);
		
		$adnAndSellExp = array("label" => "Administrative and Selling expenses", "values" => null);
		$salaryAndStaffExp = array("label" => "Salary & Staff Expenses", "values" => null);
		$badDebts = array("label" => "Bad Debts", "values" => null);
		$sellGenAdmExp = array("label" => "Selling, Gen. & Administration Exp", "values" => null);
		$otherAdmExp = array("label" => "Other Administration Exp", "values" => null);
		
		$admSubTotal = array("label" => "Sub Total", "values" => null, "type" => "other admin exp");
		$opBeforeInterest = array("label" => "Operating Profit before Interest", "values" => null);

		
		$financeCharges = array("label" => "Finance Charges", "values" => null);
		$interestWCLoans = array("label" => "Interest - Working capital loans", "values" => null);
		$interestTermLoans = array("label" => "Interest - Term Loans/Fixed loans", "values" => null);
		$bankCharges = array("label" => "Bank Charges", "values" => null);
		$totalInterest = array("label" => "Total Interest", "values" => null);
		$opAfterInterest = array("label" => "Operating Profit after Interest", "values" => null);
		
		
		$nonOpItems = array("label" => "Non Operating Items", "values" => null);
		$addNonOpItem = array("label" => "Add Non Operating Income", "values" => null);
		$interestIncome = array("label" => "Interest Income", "values" => null);
		$profitOnSaleOfAssets = array("label" => "Profit on sale of assets/ investments", "values" => null);
		$divRceived = array("label" => "Dividend received", "values" => null);
		$forexGains = array("label" => "Forex gains", "values" => null);
		$extraOrdIncome = array("label" => "Extraordinary Income", "values" => null);
		$otherNonOpIncome = array("label" => "Other Non Operating Income", "values" => null);
		$totalNonOpIncome = array("label" => "Total non-operating Income", "values" => null);

		
		$deductNonOpExp = array("label" => "Deduct Non Operating Expenses", "values" => null);
		$lossOnSaleOfAsset = array("label" => "Loss on sale of assets", "values" => null);
		$extraOrdExp = array("label" => "Extraordinary Expenses ", "values" => null);
		$forexLoses = array("label" => "Forex losses", "values" => null);
		$otherNonOpExp = array("label" => "Other Non- operating expenses", "values" => null);
		$totalNonOpExp = array("label" => "Total Non-operating expenses", "values" => null);
		$netNonOpIncomeExp = array("label" => "Net of Non-operating Income / Expenses", "values" => null);
		$profitBeforeTax = array("label" => "Profit Before tax ", "values" => null);

		
		$provForTaxation = array("label" => "Provision for taxation:", "values" => null);
		$currentProv = array("label" => "Current", "values" => null);
		$deferredProv = array("label" => "Deferred", "values" => null);
		$provSubTotal = array("label" => "Sub Total", "values" => null, "type" => "provision for tax");
		$netProfitAfterTax = array("label" => "Net Profit After tax", "values" => null);
		$dividendPaid = array("label" => "Dividend Paid", "values" => null);
		$retainedProfit = array("label" => "Retained Profit ", "values" => null);
		

		

		for($i = 0 ; $i < count($periods); $i++)
		{
			$periodItem = $periods[$i];

			$sales["values"][$i]["key"] = $periodItem["key"];
			$sales["values"][$i]["value"] =  GetISLineItem($isLineItems, "Sales", $periodItem["key"]); 
			
			$salesDomestic["values"][$i]["key"] = $periodItem["key"];
			$salesDomestic["values"][$i]["value"] = GetISLineItem($isLineItems, " - Domestic", $periodItem["key"]);

			$salesExport["values"][$i]["key"] = $periodItem["key"];
			$salesExport["values"][$i]["value"] = GetISLineItem($isLineItems, " - Export", $periodItem["key"]);

			$salesSubTotal["values"][$i]["key"] = $periodItem["key"];
			$salesSubTotal["values"][$i]["value"] = $salesDomestic["values"][$i]["value"] + $salesExport["values"][$i]["value"];

			$exciseDuty["values"][$i]["key"] = $periodItem["key"];
			$exciseDuty["values"][$i]["value"] =  GetISLineItem($isLineItems, "Less Excise Duty (if applicable)", $periodItem["key"]); 

			$netSales["values"][$i]["key"] = $periodItem["key"];
			$netSales["values"][$i]["value"] = $salesSubTotal["values"][$i]["value"] - $exciseDuty["values"][$i]["value"];
			
			$otherIncomes["values"][$i]["key"] = $periodItem["key"];
			$otherIncomes["values"][$i]["value"] =  GetISLineItem($isLineItems, "Other Incomes", $periodItem["key"]); 

			$exportIncentives["values"][$i]["key"] = $periodItem["key"];
			$exportIncentives["values"][$i]["value"] =  GetISLineItem($isLineItems, "Export Incentive", $periodItem["key"]); 
			
			$otherIncome["values"][$i]["key"] = $periodItem["key"];
			$otherIncome["values"][$i]["value"] =  GetISLineItem($isLineItems, "Other Income", $periodItem["key"]); 

			$totalOperatingIncome["values"][$i]["key"] = $periodItem["key"];
			$totalOperatingIncome["values"][$i]["value"] = $netSales["values"][$i]["value"] + $exportIncentives["values"][$i]["value"] + $otherIncome["values"][$i]["value"];

			$costOfSales["values"][$i]["key"] = $periodItem["key"];
			$costOfSales["values"][$i]["value"] =  GetISLineItem($isLineItems, "Cost of Sales", $periodItem["key"]); 

			$rawMatConsumed["values"][$i]["key"] = $periodItem["key"];
			$rawMatConsumed["values"][$i]["value"] =  GetISLineItem($isLineItems, "Raw materials consumed ", $periodItem["key"]); 

			$rawMatImported["values"][$i]["key"] = $periodItem["key"];
			$rawMatImported["values"][$i]["value"] =  GetISLineItem($isLineItems, "                        i) Imported", $periodItem["key"]); 

			$rawMatIndigenous["values"][$i]["key"] = $periodItem["key"];
			$rawMatIndigenous["values"][$i]["value"] =  GetISLineItem($isLineItems, "                        ii) Indigenous", $periodItem["key"]); 

			$otherSparesConsumed["values"][$i]["key"] = $periodItem["key"];
			$otherSparesConsumed["values"][$i]["value"] =  GetISLineItem($isLineItems, "Other Spares consumed ", $periodItem["key"]); 

			$otherSparesImported["values"][$i]["key"] = $periodItem["key"];
			$otherSparesImported["values"][$i]["value"] =  GetISLineItem($isLineItems, "                        i) Other Spares Imported", $periodItem["key"]); 

			$otherSparesIndigenous["values"][$i]["key"] = $periodItem["key"];
			$otherSparesIndigenous["values"][$i]["value"] =  GetISLineItem($isLineItems, "                        i) Other Spares Indigenous", $periodItem["key"]); 

			$powerAndFuel["values"][$i]["key"] = $periodItem["key"];
			$powerAndFuel["values"][$i]["value"] =  GetISLineItem($isLineItems, "Power and fuel ", $periodItem["key"]); 
			
			$directLabourAndWages["values"][$i]["key"] = $periodItem["key"];
			$directLabourAndWages["values"][$i]["value"] =  GetISLineItem($isLineItems, "Direct labour and wages", $periodItem["key"]); 
			
			$otherManfExp["values"][$i]["key"] = $periodItem["key"];
			$otherManfExp["values"][$i]["value"] =  GetISLineItem($isLineItems, "Other manufacturing expenses", $periodItem["key"]); 

			$depriciation["values"][$i]["key"] = $periodItem["key"];
			$depriciation["values"][$i]["value"] =  GetISLineItem($isLineItems, "Depreciation", $periodItem["key"]); 

			$costOfSalesSubTotal["values"][$i]["key"] = $periodItem["key"];
			$costOfSalesSubTotal["values"][$i]["value"] =  $rawMatImported["values"][$i]["value"] + 
			$rawMatIndigenous["values"][$i]["value"] + $otherSparesImported["values"][$i]["value"] + $otherSparesIndigenous["values"][$i]["value"] +
			$powerAndFuel["values"][$i]["value"] +$directLabourAndWages["values"][$i]["value"]+$otherManfExp["values"][$i]["value"] +$depriciation["values"][$i]["value"] ;

			$opStockWIP["values"][$i]["key"] = $periodItem["key"];
			$opStockWIP["values"][$i]["value"] =  GetISLineItem($isLineItems, "Add: Op. Stock of WIP", $periodItem["key"]); 
			$clStockWIP["values"][$i]["key"] = $periodItem["key"];
			$clStockWIP["values"][$i]["value"] =  GetISLineItem($isLineItems, "Less: Cl. Stock of WIP", $periodItem["key"]); 
			$totalCostOfProd["values"][$i]["key"] = $periodItem["key"];
			$totalCostOfProd["values"][$i]["value"] =  $costOfSalesSubTotal["values"][$i]["value"] + 	$opStockWIP["values"][$i]["value"] -$clStockWIP["values"][$i]["value"]; 

			$opStockOfFG["values"][$i]["key"] = $periodItem["key"];
			$opStockOfFG["values"][$i]["value"] =  GetISLineItem($isLineItems, "Add: Opening Stock of Finished Goods", $periodItem["key"]); 
			$clStockOfFG["values"][$i]["key"] = $periodItem["key"];
			$clStockOfFG["values"][$i]["value"] =  GetISLineItem($isLineItems, "Less: Closing Stock  of Finished Goods", $periodItem["key"]); 
			$totalCostOfSales["values"][$i]["key"] = $periodItem["key"];
			$totalCostOfSales["values"][$i]["value"] = $costOfSalesSubTotal["values"][$i]["value"] + $opStockOfFG["values"][$i]["value"] - $clStockOfFG["values"][$i]["value"];

			$adnAndSellExp["values"][$i]["key"] = $periodItem["key"];
			$adnAndSellExp["values"][$i]["value"] =  GetISLineItem($isLineItems, "Administrative and Selling expenses", $periodItem["key"]); 
			$salaryAndStaffExp["values"][$i]["key"] = $periodItem["key"];
			$salaryAndStaffExp["values"][$i]["value"] =  GetISLineItem($isLineItems, "Salary & Staff Expenses", $periodItem["key"]); 
			$badDebts["values"][$i]["key"] = $periodItem["key"];
			$badDebts["values"][$i]["value"] =  GetISLineItem($isLineItems, "Bad Debts", $periodItem["key"]); 
			$sellGenAdmExp["values"][$i]["key"] = $periodItem["key"];
			$sellGenAdmExp["values"][$i]["value"] =  GetISLineItem($isLineItems, "Selling, Gen. & Administration Exp", $periodItem["key"]); 
			$otherAdmExp["values"][$i]["key"] = $periodItem["key"];
			$otherAdmExp["values"][$i]["value"] =  GetISLineItem($isLineItems, "Other Administration Exp", $periodItem["key"]); 
			
			$admSubTotal["values"][$i]["key"] = $periodItem["key"];
			$admSubTotal["values"][$i]["value"] =  $salaryAndStaffExp["values"][$i]["value"]+$badDebts["values"][$i]["value"]+$sellGenAdmExp["values"][$i]["value"] +$otherAdmExp["values"][$i]["value"];

			$opBeforeInterest["values"][$i]["key"] = $periodItem["key"];
			$opBeforeInterest["values"][$i]["value"] = $totalOperatingIncome["values"][$i]["value"] - $totalCostOfSales["values"][$i]["value"] - $admSubTotal["values"][$i]["value"];

			
			$financeCharges["values"][$i]["key"] = $periodItem["key"];
			$financeCharges["values"][$i]["value"] = GetISLineItem($isLineItems, "Finance Charges", $periodItem["key"]); 
			$interestWCLoans["values"][$i]["key"] = $periodItem["key"];
			$interestWCLoans["values"][$i]["value"] = GetISLineItem($isLineItems, "Interest - Working capital loans", $periodItem["key"]); 
			$interestTermLoans["values"][$i]["key"] = $periodItem["key"];
			$interestTermLoans["values"][$i]["value"] = GetISLineItem($isLineItems, "Interest - Term Loans/Fixed loans", $periodItem["key"]); 
			$bankCharges["values"][$i]["key"] = $periodItem["key"];
			$bankCharges["values"][$i]["value"] = GetISLineItem($isLineItems, "Bank Charges", $periodItem["key"]); 
			$totalInterest["values"][$i]["key"] = $periodItem["key"];
			$totalInterest["values"][$i]["value"] = $interestWCLoans["values"][$i]["value"] + $interestTermLoans["values"][$i]["value"] + $bankCharges["values"][$i]["value"] ;
			$opAfterInterest["values"][$i]["key"] = $periodItem["key"];
			$opAfterInterest["values"][$i]["value"] = $opBeforeInterest["values"][$i]["value"] - $totalInterest["values"][$i]["value"];


			
			$addNonOpItem["values"][$i]["key"] = $periodItem["key"];
			$addNonOpItem["values"][$i]["value"] = GetISLineItem($isLineItems, "Add: Non Operating Income", $periodItem["key"]); 
			$interestIncome["values"][$i]["key"] = $periodItem["key"];
			$interestIncome["values"][$i]["value"] = GetISLineItem($isLineItems, "Interest Income", $periodItem["key"]); 
			$profitOnSaleOfAssets["values"][$i]["key"] = $periodItem["key"];
			$profitOnSaleOfAssets["values"][$i]["value"] = GetISLineItem($isLineItems, "Profit on sale of assets/ investments", $periodItem["key"]); 
			$divRceived["values"][$i]["key"] = $periodItem["key"];
			$divRceived["values"][$i]["value"] = GetISLineItem($isLineItems, "Dividend received", $periodItem["key"]); 
			$forexGains["values"][$i]["key"] = $periodItem["key"];
			$forexGains["values"][$i]["value"] = GetISLineItem($isLineItems, "Forex gains", $periodItem["key"]); 
			$extraOrdIncome["values"][$i]["key"] = $periodItem["key"];
			$extraOrdIncome["values"][$i]["value"] = GetISLineItem($isLineItems, "Extraordinary Income", $periodItem["key"]); 
			$otherNonOpIncome["values"][$i]["key"] = $periodItem["key"];
			$otherNonOpIncome["values"][$i]["value"] = GetISLineItem($isLineItems, "Other Non Operating Income", $periodItem["key"]); 
			$totalNonOpIncome["values"][$i]["key"] = $periodItem["key"];
			$totalNonOpIncome["values"][$i]["value"] = $interestIncome["values"][$i]["value"] + $profitOnSaleOfAssets["values"][$i]["value"] +$divRceived["values"][$i]["value"] +  $forexGains["values"][$i]["value"] + $extraOrdIncome["values"][$i]["value"] + $otherNonOpIncome["values"][$i]["value"];
			
				
			$deductNonOpExp["values"][$i]["key"] = $periodItem["key"];
			$deductNonOpExp["values"][$i]["value"] = GetISLineItem($isLineItems, "Deduct Non Operating Expenses", $periodItem["key"]); 
			$lossOnSaleOfAsset["values"][$i]["key"] = $periodItem["key"];
			$lossOnSaleOfAsset["values"][$i]["value"] = GetISLineItem($isLineItems, "Loss on sale of assets", $periodItem["key"]); 
			$extraOrdExp["values"][$i]["key"] = $periodItem["key"];
			$extraOrdExp["values"][$i]["value"] = GetISLineItem($isLineItems, "Extraordinary Expenses ", $periodItem["key"]); 
			$forexLoses["values"][$i]["key"] = $periodItem["key"];
			$forexLoses["values"][$i]["value"] = GetISLineItem($isLineItems, "Forex losses", $periodItem["key"]); 
			$otherNonOpExp["values"][$i]["key"] = $periodItem["key"];
			$otherNonOpExp["values"][$i]["value"] = GetISLineItem($isLineItems, "Other Non- operating expenses", $periodItem["key"]); 
			
			$totalNonOpExp["values"][$i]["key"] = $periodItem["key"];
			$totalNonOpExp["values"][$i]["value"] = $lossOnSaleOfAsset["values"][$i]["value"] + $extraOrdExp["values"][$i]["value"] + $forexLoses["values"][$i]["value"] + $otherNonOpExp["values"][$i]["value"];

			$netNonOpIncomeExp["values"][$i]["key"] = $periodItem["key"];
			$netNonOpIncomeExp["values"][$i]["value"] = $totalNonOpIncome["values"][$i]["value"] - abs($totalNonOpExp["values"][$i]["value"]);

			$profitBeforeTax["values"][$i]["key"] = $periodItem["key"];
			$profitBeforeTax ["values"][$i]["value"]= $opAfterInterest["values"][$i]["value"] + $netNonOpIncomeExp["values"][$i]["value"];

			$provForTaxation["values"][$i]["key"] = $periodItem["key"];
			$provForTaxation["values"][$i]["value"] = GetISLineItem($isLineItems, "Provision for taxation:", $periodItem["key"]); 
			$currentProv["values"][$i]["key"] = $periodItem["key"];
			$currentProv["values"][$i]["value"] = GetISLineItem($isLineItems, " Current", $periodItem["key"]); 
			$deferredProv["values"][$i]["key"] = $periodItem["key"];
			$deferredProv["values"][$i]["value"] = GetISLineItem($isLineItems, " Deferred", $periodItem["key"]); 
			$provSubTotal["values"][$i]["key"] = $periodItem["key"];
			$provSubTotal["values"][$i]["value"] = $currentProv["values"][$i]["value"] + $deferredProv["values"][$i]["value"];
			$netProfitAfterTax["values"][$i]["key"] = $periodItem["key"];
			$netProfitAfterTax["values"][$i]["value"] = $profitBeforeTax ["values"][$i]["value"] - $provSubTotal["values"][$i]["value"];
			$dividendPaid["values"][$i]["key"] = $periodItem["key"];
			$dividendPaid["values"][$i]["value"] = 0;
			$retainedProfit["values"][$i]["key"] = $periodItem["key"];
			$retainedProfit["values"][$i]["value"] = $netProfitAfterTax["values"][$i]["value"] - $dividendPaid["values"][$i]["value"];
		}

		$profitAndLoss[] = $sales;
		$profitAndLoss[] = $salesDomestic;
		$profitAndLoss[] = $salesExport;
		$profitAndLoss[] = $salesSubTotal;
		$profitAndLoss[] = $exciseDuty;
		$profitAndLoss[] = $netSales;
		$profitAndLoss[] = $riseFallInNetSales;
		
		
		$profitAndLoss[] = $otherIncomes;
		$profitAndLoss[] = $exportIncentives;
		$profitAndLoss[] = $otherIncome;

		$profitAndLoss[] = $totalOperatingIncome;
		$profitAndLoss[] = $costOfSales;
		$profitAndLoss[] = $rawMatConsumed;
		$profitAndLoss[] = $rawMatImported;
		$profitAndLoss[] = $rawMatIndigenous;

		$profitAndLoss[] = $otherSparesConsumed;
		$profitAndLoss[] = $otherSparesImported;
		$profitAndLoss[] = $otherSparesIndigenous;

		$profitAndLoss[] = $powerAndFuel;
		$profitAndLoss[] = $directLabourAndWages;
		$profitAndLoss[] = $otherManfExp;
		$profitAndLoss[] = $depriciation;
		$profitAndLoss[] = $costOfSalesSubTotal;

	
		$profitAndLoss[] = $opStockWIP;
		$profitAndLoss[] = $clStockWIP;
		$profitAndLoss[] = $totalCostOfProd;

		$profitAndLoss[] = $opStockOfFG;
		$profitAndLoss[] = $clStockOfFG;
		$profitAndLoss[] = $totalCostOfSales;

		$profitAndLoss[] = $adnAndSellExp;
		$profitAndLoss[] = $salaryAndStaffExp;
		$profitAndLoss[] = $badDebts;
		$profitAndLoss[] = $sellGenAdmExp;
		$profitAndLoss[] = $otherAdmExp;
		$profitAndLoss[] = $admSubTotal;
		$profitAndLoss[] = $opBeforeInterest;

		
		$profitAndLoss[] = $financeCharges;
		$profitAndLoss[] = $interestWCLoans;
		$profitAndLoss[] = $interestTermLoans;
		$profitAndLoss[] = $bankCharges;
		$profitAndLoss[] = $totalInterest;
		$profitAndLoss[] = $opAfterInterest;


		$profitAndLoss[] = $nonOpItems;
		$profitAndLoss[] = $addNonOpItem;
		$profitAndLoss[] = $interestIncome;
		$profitAndLoss[] = $profitOnSaleOfAssets;
		$profitAndLoss[] = $divRceived;
		$profitAndLoss[] = $forexGains;
		$profitAndLoss[] = $extraOrdIncome;
		$profitAndLoss[] = $otherNonOpIncome;
		$profitAndLoss[] = $totalNonOpIncome;

		$profitAndLoss[] = $deductNonOpExp;
		$profitAndLoss[] = $lossOnSaleOfAsset;
		$profitAndLoss[] = $extraOrdExp;
		$profitAndLoss[] = $forexLoses;
		$profitAndLoss[] = $otherNonOpExp;
		$profitAndLoss[] = $totalNonOpExp;
		$profitAndLoss[] = $netNonOpIncomeExp;
		$profitAndLoss[] = $profitBeforeTax;

		$profitAndLoss[] = $provForTaxation;
		$profitAndLoss[] = $currentProv;
		$profitAndLoss[] = $deferredProv;
		$profitAndLoss[] = $provSubTotal;
		$profitAndLoss[] = $netProfitAfterTax;
		$profitAndLoss[] = $dividendPaid;
		$profitAndLoss[] = $retainedProfit;
		

        return $profitAndLoss;
    }
  }


  if(!function_exists('GetProfitAndLoss')) {
    function GetProfitAndLoss($jsonData, $type){

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

				$subTotalResetIndex = array();

				

                foreach($incomestatement["lineitems"] as $lineitem)
                {
                    $itemIndex++;
                    $_lineitem = null;

                    try 
                    {
	                    if((strpos($lineitem["classname"], "Revenue") !== false || $lineitem["componenttype"] == "IS") && $lineitem["label"] != "")
	                    {
	                        //$_lineitem  = array("lineitem" => $lineitem["label"], "values" => array());
	                       
	                        if(isset($incomestatement["periods"]) && count($incomestatement["periods"]) > 0)
	                        {
	                            foreach($incomestatement["periods"] as $period)
	                            {
	                    
	                                if(isset($lineitem["values"]) && count($lineitem["values"]) > 0)
	                                {
	                                    //$_lineitem[$period["key"]] = 0;
	                                    
	                                    foreach ($lineitem["values"] as $itemvalue) {
	                                        if($itemvalue->key == $period["key"])
	                                        {
	                                            if($itemIndex == 4 || $itemIndex == 20 || $itemIndex == 22)
	                                            {
	                                                
	                                                //$_lineitem["values"][] = array("year"=> $period["key"], "value" => -$itemvalue->value);
	                                                $_lineitem[] = array("key"=> $period["key"], "value" => -$itemvalue->value);

	                                                $subTotal[] = array($period["key"] => -$itemvalue->value);
	                                            }
	                                            else{
	                                                //$_lineitem["values"][] = array("year"=> $period["key"], "value" => $itemvalue->value);
													$_lineitem[] = array("key"=> $period["key"], "value" => -$itemvalue->value);
	                                                $subTotal[] = array($period["key"] => $itemvalue->value);
	                                            }

	                                        }
	                                    }
	                                }
	                                else{
	                                    $_lineitem[] = array("key"=> $period["key"], "value" => 0);
	                                }
	                            }
	                        }

							
							//$subTotalResetIndex[] = $subTotal;

	                        $profitAndLoss[] = array("label" => $lineitem["label"], "values" => $_lineitem);


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

	                                        
											$lineitem[] = array("key"=> $period["key"], "value" => $valueTotal);
	                                       // $lineitem["values"][] = array("year"=> $period["key"], "value" => $valueTotal);
	                               
	                                    }
	                                    $subTotal = array();
										$subTotalResetIndex[] = $itemIndex;
	                                }

	                                //$profitAndLoss["Sales Sub Total"] = $lineitem;
									$profitAndLoss[] = array("label" => "Sales Sub Total", "values" => $lineitem);
									
	                        
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
	                                        //$lineitem[$period["key"]] = $valueTotal;
											$lineitem[] = array("key"=> $period["key"], "value" => $valueTotal);

	                                    }
	                                    $subTotal = array();
										$subTotalResetIndex[] = $itemIndex;

	                                }

	                                //$profitAndLoss["Net Sales"] = $lineitem;
									$profitAndLoss[] = array("label" => "Net Sales", "values" => $lineitem);

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

	                                        //$lineitem[$period["key"]] = $valueTotal;
											$lineitem[] = array("key"=> $period["key"], "value" => $valueTotal);

	                                    }
	                                    $subTotal = array();
										$subTotalResetIndex[] = $itemIndex;
	                                }

	                                //$profitAndLoss[""] = $lineitem;
									$profitAndLoss[] = array("label" => "Total Operating Income", "values" => $lineitem);
	                
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

	                                        //$lineitem[$period["key"]] = $valueTotal;
											$lineitem[] = array("key"=> $period["key"], "value" => $valueTotal);

	                                    }

	                                    $subTotal = array();
										$subTotalResetIndex[] = $itemIndex;
	                                }

	                                //$profitAndLoss["Cost of Sales - Sub Total"] = $lineitem;
									$profitAndLoss[] = array("label" => "Cost of Sales - Sub Total", "values" => $lineitem);
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
	                                        //$lineitem[$period["key"]] = $valueTotal;
											$lineitem[] = array("key"=> $period["key"], "value" => $valueTotal);

	                                    }
	                                    $subTotal = array();
										$subTotalResetIndex[] = $itemIndex;
	                                }

	                                //$profitAndLoss["Total Cost of Production"] = $lineitem;
									$profitAndLoss[] = array("label" => "Total Cost of Production", "values" => $lineitem);
	                
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
	                                        //$lineitem[$period["key"]] = $valueTotal;
											$lineitem[] = array("key"=> $period["key"], "value" => $valueTotal);

	                                    }
	                                    $subTotal = array();
										$subTotalResetIndex[] = $itemIndex;
	                                }

	                                //$profitAndLoss["Total Cost of Sales"] = $lineitem;
									$profitAndLoss[] = array("label" => "Total Cost of Sales", "values" => $lineitem);

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
	                                        //$lineitem[$period["key"]] = $valueTotal;
											$lineitem[] = array("key"=> $period["key"], "value" => $valueTotal);
	                                    }
	                                    $subTotal = array();
										$subTotalResetIndex[] = $itemIndex;
	                                }

	                                //$profitAndLoss["Administrative & Selling Sub Total"] = $lineitem;
									$profitAndLoss[] = array("label" => "Administrative & Selling Sub Total", "values" => $lineitem);

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
	                                        //$lineitem[$period["key"]] = $valueTotal;
											$lineitem[] = array("key"=> $period["key"], "value" => $valueTotal);
	                            

	                                    }
	                                    $subTotal = array();
										$subTotalResetIndex[] = $itemIndex;
	                                }

	                                //$profitAndLoss["Operating Profit before Interest"] = $lineitem;
									$profitAndLoss[] = array("label" => "Operating Profit before Interest", "values" => $lineitem);

	                    
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
	                                        //$lineitem[$period["key"]] = $valueTotal;
											$lineitem[] = array("key"=> $period["key"], "value" => $valueTotal);

	                                    }
	                                    $subTotal = array();
										$subTotalResetIndex[] = $itemIndex;
	                                }
	                                //$profitAndLoss["Total Interest"] = $lineitem;
									$profitAndLoss[] = array("label" => "Total Interest", "values" => $lineitem);

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
	                                        //$lineitem[$period["key"]] = $valueTotal;
											$lineitem[] = array("key"=> $period["key"], "value" => $valueTotal);
	                                
	                                    }
	                                    $subTotal = array();
										
	                                }

	                                
									$profitAndLoss[] = array("label" => "Operating Profit after Interest", "values" => $lineitem);

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
	                                        //$lineitem[$period["key"]] = $valueTotal;

											$lineitem[] = array("key"=> $period["key"], "value" => $valueTotal);
	                                    }
	                                    
	                                    $subTotal = array();
										$subTotalResetIndex[] = $itemIndex;
	                                }
	                                
	                                //$profitAndLoss["Total non-operating Income"] = $lineitem;
									$profitAndLoss[] = array("label" => "Total non-operating Income", "values" => $lineitem);
	            
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
	                                        //$lineitem[$period["key"]] = $valueTotal;
											$lineitem[] = array("key"=> $period["key"], "value" => $valueTotal);
	                                    }
	                                    
	                                    $subTotal = array();
	                                }

	                                //$profitAndLoss["Total Non-operating expenses"] = $lineitem;
									$profitAndLoss[] = array("label" => "Total Non-operating expenses", "values" => $lineitem);

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
	                                        //$lineitem[$period["key"]] = $valueTotal;
											$lineitem[] = array("key"=> $period["key"], "value" => $valueTotal);

	                                    }
	                                        $subTotal = array();
	                                }

	                                //$profitAndLoss["Net of Non-operating Income / Expenses"] = $lineitem;
									$profitAndLoss[] = array("label" => "Net of Non-operating Income / Expenses", "values" => $lineitem);

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
	                                        //$lineitem[$period["key"]] = $valueTotal;
											$lineitem[] = array("key"=> $period["key"], "value" => $valueTotal);

	                                    }
	                                    
	                                    $subTotal = array();
	                                }

	                                //$profitAndLoss["Profit Before tax"] = $lineitem;
									$profitAndLoss[] = array("label" => "Profit Before tax", "values" => $lineitem);
	                            }
	                            else if ($itemIndex == 44)
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
	                                        //$lineitem[$period["key"]] = $valueTotal;
											$lineitem[] = array("key"=> $period["key"], "value" => $valueTotal);

	                                    }
	                                    $subTotal = array();
	                                }

	                                //$profitAndLoss["Provision for Taxation Sub Total"] = $lineitem;
									$profitAndLoss[] = array("label" => "Provision for Taxation Sub Total", "values" => $lineitem);

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
	                                        //$lineitem[$period["key"]] = $valueTotal;
											$lineitem[] = array("key"=> $period["key"], "value" => $valueTotal);

	                                    }
	                                    $subTotal = array();
	                                }

	                                //$profitAndLoss["Net Profit After tax"] = $lineitem;
									$profitAndLoss[] = array("label" => "Net Profit After tax", "values" => $lineitem);
	                        
	                            }
	                    }
					}
					catch(Exception $e) {
					  echo 'Message: ' .$e->getMessage();
					  var_dump($lineitem);
					}

                }
            }
        }

		$profitAndLoss[] = $profitAfterTax = array("label" => "Sub Total Reset", "values" => array(), "test" => $subTotalResetIndex);

        return $profitAndLoss;
    }
  }


  if(!function_exists('GetBalanceSheetLiabilities')) {
    function GetBalanceSheetLiabilities($jsonData, $type){

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

						try
	                    {
	                        if((strpos($lineitem["classname"], "Liabilities") !== false  || strpos($lineitem["classname"], "Equity") !== false) && $lineitem["label"] != "")
	                        {
	            
	                            if(isset($bsData["periods"]) && count($bsData["periods"]) > 0)
	                            {
	                                foreach($bsData["periods"] as $period)
	                                {
	                                    
	                                    if(isset($lineitem["values"]) && count($lineitem["values"]) > 0)
	                                    {
	                                        foreach ($lineitem["values"] as $itemvalue) {
	                                            if($itemvalue->key == $period["key"])
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
	                	catch(Exception  $ex)
	                	{
	                		var_dump($ex->getMessage());
	                		var_dump($lineitem);
	                	}
                    }
                }
                								
            }
        }

        return $balanceSheet;
    }
  }

  if(!function_exists('GetBalanceSheetAssets')) {
    function GetBalanceSheetAssets($jsonData, $type){

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
                                                if($itemvalue->key == $period["key"])
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
                                            if($itemvalue->key == $period["key"])
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

  if(!function_exists('GetBSSourceFromDB')) 
  {
	  function GetBSSourceFromDB($bssPeriods, $bssDbData, $unit = "million")
	  {
			$prevLabel = "";	
		  
			$balanceSheet = null;
  
			  
			$currentLiabilities = array("label" => "CURRENT LIABILITIES", "values" => null);
			$stBorrowingsFromBanks = array("label" => "Short term borrowings from Banks", "values" => null);
			  
			$bank_borrowings_from_applicant_bank  = array("label" => "Bank Borrowings - From applicant Bank", "values" => null);
			$bank_borrowings_from_other_banks  = array("label" => "Bank Borrowings - From other Banks", "values" => null);

			$borrowings_sub_total = array("label" => "Sub Total", "values" => null);

			$st_borrowings_from_associates = array("label" => "Short term borrowings from Associates & Group Concerns repayable within one year", "values" => null);
			$st_borrowings_from_others = array("label" => "Short term borrowings from Others", "values" => null);
			$creditors_for_purchases_others = array("label" => "Creditors for purchases – others", "values" => null);
			$creditors_for_purchases_group_companies = array("label" => "Creditors for purchases – Group Companies", "values" => null);
			$creditors_for_expenses = array("label" => "Creditors for expenses", "values" => null);
			$advances_payments_from_customers = array("label" => "Advances/ payments from customers/deposits from dealers.", "values" => null);
			$provisions = array("label" => "Provisons", "values" => null);
			$provisions_for_tax = array("label" => " - Tax", "values" => null);
			$provisions_for_deferred_tax = array("label" => " - Deferred Tax", "values" => null);
			$others  = array("label" => " - Others", "values" => null);

			$dividends_payable  = array("label" => "Dividends Payable", "values" => null);
			$statutory_liabilities_due_within_one_year  = array("label" => "Statutory liabilities due within one year", "values" => null);
			$installments_of_term_to_banks  = array("label" => "Installments of Term Loans/Debentures (due within one year)- To banks ", "values" => null);
			$installments_of_term_loans_to_others  = array("label" => "Installments of Term Loans/Debentures (due within one year)- To Others", "values" => null);
			$deposits_due_payable_within_a_year  = array("label" => "Deposits due / payable within a year", "values" => null);
			$other_current_liabilities_due_within_one_year  = array("label" => "Other Current Liabilities due within one year", "values" => null);

			$total_current_liabilities  = array("label" => "Total Current Liabilities", "values" => null);

			$term_liabilities  = array("label" => "TERM LIABILITIES", "values" => null);

			$debentures_maturing_after_1_year  = array("label" => "Debentures maturing after 1 year", "values" => null);
			$preference_share_capital_maturity_within_12_years  = array("label" => "Preference share capital maturity < 12 years", "values" => null);
			$dealers_deposit  = array("label" => "Dealer's Deposit", "values" => null);
			$deferred_tax_liability  = array("label" => "Deferred Tax Liability", "values" => null);
			$term_loans_from_banks  = array("label" => "Term Loans  - From Banks", "values" => null);
			$term_loans_from_financial_istitution  = array("label" => "Term Loans - From Financial Institution", "values" => null);
			$term_deposits  = array("label" => "Term Deposits", "values" => null);
			$borrowings_from_subsidiaries  = array("label" => "Borrowings from subsidiaries / affiliates", "values" => null);
			$unsecured_loans  = array("label" => "Unsecured Loans", "values" => null);
			$other_term_liabilities  = array("label" => "Other term liabilities", "values" => null);

			$total_term_liabilities  = array("label" => "Total Term Liabilities", "values" => null);
			$total_outside_liabilities  = array("label" => "TOTAL OUTSIDE LIABILITIES", "values" => null);

			$net_worth_title  = array("label" => "NET WORTH", "values" => null);

			$equity_share_capital  = array("label" => "Equity Share Capital ", "values" => null);
			$share_capital_paid_up  = array("label" => "Share Capital (Paid-up)", "values" => null);
			$share_application_money  = array("label" => "Share Application money", "values" => null);
			$general_reserve  = array("label" => "General Reserve", "values" => null);
			$revaluation_reserve  = array("label" => "Revaluation Reserve", "values" => null);
			$partners_capital  = array("label" => "Partners capital / Proprietor's capital", "values" => null);
			$balance_in_partners_current_ac  = array("label" => "Balance in Partners' Current A/c (+ / -)", "values" => null);
			
			$other_reserve_and_surplus  = array("label" => "Other Reserves & Surplus:", "values" => null);
			
			$share_premium  = array("label" => "Share Premium", "values" => null);
			$capital_subsidy  = array("label" => "Capital subsidy", "values" => null);
			$quasi_equity  = array("label" => "Quasi Equity", "values" => null);
			$balance_in_pl_account  = array("label" => "Balance in P&L Account (+ / - )", "values" => null);

			$net_worth  = array("label" => "NET WORTH", "values" => null);
			$total_liabilities  = array("label" => "Total Liabilities", "values" => null);

			$balance_sheet_assets_input  = array("label" => "BALANCE SHEET (ASSETS) INPUT", "values" => null);
			$current_assets_title  = array("label" => "CURRENT ASSETS", "values" => null);
			

			$cash_balances  = array("label" => "Cash Balances", "values" => null);
			$bank_balances  = array("label" => "Bank Balances", "values" => null);

			$investments  = array("label" => "Investments", "values" => null);

			$govt_and_other_trustee_securities  = array("label" => "Govt. and other trustee Securities", "values" => null);
			$fixed_deposits_with_banks  = array("label" => "Fixed Deposits with Banks", "values" => null);
			$others_investments_in_subsidiaries  = array("label" => "Others – Investments in Subsidiaries/Group Companies", "values" => null);

			$receivables  = array("label" => "Receivables", "values" => null);

			$domestic_receivables  = array("label" => "Domestic Receivables ", "values" => null);
			$export_receivables  = array("label" => "Export Receivables", "values" => null);

			$inventory  = array("label" => "Inventory", "values" => null);

			$raw_Materials_imported  = array("label" => " Raw Materials – Imported", "values" => null);
			$raw_materials_indigenous  = array("label" => " Raw Materials – Indigenous", "values" => null);
			$work_in_process  = array("label" => " Work in process", "values" => null);
			$finished_goods  = array("label" => " Finished Goods (incl Traded Goods)", "values" => null);
			$other_consumable_spares_imported  = array("label" => "Other consumable spares – Imported", "values" => null);
			$other_consumable_spares_indigenous  = array("label" => "Other consumable spares -  Indigenous", "values" => null);
			$adv_to_suppliers_of_raw_materials  = array("label" => "Advances to suppliers of Raw materials/Stores/Spares", "values" => null);
			$advance_payment_of_tax  = array("label" => "Advance payment of tax", "values" => null);

			$other_current_assets  = array("label" => "Other Current Assets", "values" => null);

			$prepaid_expenses  = array("label" => "Prepaid Expenses", "values" => null);
			$other_advances_current_asset  = array("label" => "Other Advances/current Asset", "values" => null);

			$total_current_assets  = array("label" => "TOTAL CURRENT ASSETS", "values" => null);

			$fixed_assets_title  = array("label" => "FIXED ASSETS", "values" => null);
			

			$gross_block  = array("label" => "Gross Block", "values" => null);
			$accumulated_depreciation  = array("label" => "Less: Accumulated Depreciation", "values" => null);

			$net_block  = array("label" => "Net Block", "values" => null);

			$capital_wip  = array("label" => "Capital Work in progress", "values" => null);

			$non_current_assets_title  = array("label" => "NON-CURRENT ASSETS", "values" => null);
			$investments_book_debts_title  = array("label" => "Investments / Book Debts / Advances / Deposits (which are not current assets):", "values" => null);
			

			$investments_in_group_concerns  = array("label" => "Investments in Group concerns", "values" => null);
			$loans_to_group_concerns  = array("label" => "Loans to group concerns / Advances to subsidiaries", "values" => null);
			$investments_in_others  = array("label" => "Investments in others", "values" => null);
			$adv_to_suppliers_of_capital_goods  = array("label" => "Advances to suppliers of capital goods and contractors", "values" => null);
			$deferred_receivables  = array("label" => "Deferred receivables (maturity exceeding one year)", "values" => null);
			$debtors_more_than_6_months  = array("label" => "Debtors more than 6 months", "values" => null);
			$others_loan_advances  = array("label" => "Others (Loans & Advances non current in nature, ICD’s etc.)", "values" => null);
			$security_deposits  = array("label" => "Security deposits", "values" => null);
			$deposits_with_government_dept  = array("label" => "Deposits with Government departments", "values" => null);
			$deferred_tax_asset  = array("label" => "Deferred Tax Asset", "values" => null);
			$other_non_current_assets  = array("label" => "Other Non-current Assets", "values" => null);

			$total_non_current_assets  = array("label" => "TOTAL NON CURRENT ASSETS", "values" => null);

			$intangible_assets_title  = array("label" => "Intangible Assets:", "values" => null);


			$goodwill_patents_trademarks  = array("label" => "Goodwill, Patents & trademarks", "values" => null);
			$misc_exp_not_written_off  = array("label" => "Miscellaneous expenditure not w/off", "values" => null);
			$other_deferred_revenue_exp  = array("label" => "Other deferred revenue expenses", "values" => null);

			$total_intangible_assets  = array("label" => "TOTAL INTANGIBLE ASSETS", "values" => null);
			$total_assets  = array("label" => "TOTAL ASSETS", "values" => null);
			  
			  
  
			  for($i = 0 ; $i < count($bssPeriods); $i++)
			  {
				  $periodItem = $bssPeriods[$i];
  
				  foreach($bssDbData as $bssDataItem)
				  {
					  if($bssDataItem->year == $periodItem["year"] && $periodItem["ptype"] == $bssDataItem->period_type)
					  {
						$bank_borrowings_from_applicant_bank["values"][$i]["key"] = $periodItem["key"];
						$bank_borrowings_from_applicant_bank["values"][$i]["value"] =  DisplayAmount($bssDataItem->bank_borrowings_from_applicant_bank, $unit);

						$bank_borrowings_from_other_banks["values"][$i]["key"] = $periodItem["key"];
						$bank_borrowings_from_other_banks["values"][$i]["value"] =  DisplayAmount($bssDataItem->bank_borrowings_from_other_banks, $unit);

						$borrowings_sub_total["values"][$i]["key"] = $periodItem["key"];
						$borrowings_sub_total["values"][$i]["value"] =  DisplayAmount($bssDataItem->borrowings_sub_total, $unit);

						$st_borrowings_from_associates["values"][$i]["key"] = $periodItem["key"];
						$st_borrowings_from_associates["values"][$i]["value"] =  DisplayAmount($bssDataItem->st_borrowings_from_associates, $unit);

						$st_borrowings_from_others["values"][$i]["key"] = $periodItem["key"];
						$st_borrowings_from_others["values"][$i]["value"] =  DisplayAmount($bssDataItem->st_borrowings_from_others, $unit);

						$creditors_for_purchases_others["values"][$i]["key"] = $periodItem["key"];
						$creditors_for_purchases_others["values"][$i]["value"] =  DisplayAmount($bssDataItem->creditors_for_purchases_others, $unit);

						$creditors_for_purchases_group_companies["values"][$i]["key"] = $periodItem["key"];
						$creditors_for_purchases_group_companies["values"][$i]["value"] =  DisplayAmount($bssDataItem->creditors_for_purchases_group_companies, $unit);

						$creditors_for_expenses["values"][$i]["key"] = $periodItem["key"];
						$creditors_for_expenses["values"][$i]["value"] =  DisplayAmount($bssDataItem->creditors_for_expenses, $unit);

						$advances_payments_from_customers["values"][$i]["key"] = $periodItem["key"];
						$advances_payments_from_customers["values"][$i]["value"] =  DisplayAmount($bssDataItem->advances_payments_from_customers, $unit);

						$provisions_for_tax["values"][$i]["key"] = $periodItem["key"];
						$provisions_for_tax["values"][$i]["value"] =  DisplayAmount($bssDataItem->provisions_for_tax, $unit);

						$provisions_for_deferred_tax["values"][$i]["key"] = $periodItem["key"];
						$provisions_for_deferred_tax["values"][$i]["value"] =  DisplayAmount($bssDataItem->provisions_for_deferred_tax, $unit);

						$others["values"][$i]["key"] = $periodItem["key"];
						$others["values"][$i]["value"] =  DisplayAmount($bssDataItem->others, $unit);

						$dividends_payable["values"][$i]["key"] = $periodItem["key"];
						$dividends_payable["values"][$i]["value"] =  DisplayAmount($bssDataItem->dividends_payable, $unit);

						$statutory_liabilities_due_within_one_year["values"][$i]["key"] = $periodItem["key"];
						$statutory_liabilities_due_within_one_year["values"][$i]["value"] =  DisplayAmount($bssDataItem->statutory_liabilities_due_within_one_year, $unit);

						$installments_of_term_to_banks["values"][$i]["key"] = $periodItem["key"];
						$installments_of_term_to_banks["values"][$i]["value"] =  DisplayAmount($bssDataItem->installments_of_term_to_banks, $unit);

						$installments_of_term_loans_to_others["values"][$i]["key"] = $periodItem["key"];
						$installments_of_term_loans_to_others["values"][$i]["value"] =  DisplayAmount($bssDataItem->installments_of_term_loans_to_others, $unit);

						$deposits_due_payable_within_a_year["values"][$i]["key"] = $periodItem["key"];
						$deposits_due_payable_within_a_year["values"][$i]["value"] =  DisplayAmount($bssDataItem->deposits_due_payable_within_a_year, $unit);

						$other_current_liabilities_due_within_one_year["values"][$i]["key"] = $periodItem["key"];
						$other_current_liabilities_due_within_one_year["values"][$i]["value"] =  DisplayAmount($bssDataItem->other_current_liabilities_due_within_one_year, $unit);

						$total_current_liabilities["values"][$i]["key"] = $periodItem["key"];
						$total_current_liabilities["values"][$i]["value"] =  DisplayAmount($bssDataItem->total_current_liabilities, $unit);

						$debentures_maturing_after_1_year["values"][$i]["key"] = $periodItem["key"];
						$debentures_maturing_after_1_year["values"][$i]["value"] =  DisplayAmount($bssDataItem->debentures_maturing_after_1_year, $unit);

						$preference_share_capital_maturity_within_12_years["values"][$i]["key"] = $periodItem["key"];
						$preference_share_capital_maturity_within_12_years["values"][$i]["value"] =  DisplayAmount($bssDataItem->preference_share_capital_maturity_within_12_years, $unit);

						$dealers_deposit["values"][$i]["key"] = $periodItem["key"];
						$dealers_deposit["values"][$i]["value"] =  DisplayAmount($bssDataItem->dealers_deposit, $unit);

						$deferred_tax_liability["values"][$i]["key"] = $periodItem["key"];
						$deferred_tax_liability["values"][$i]["value"] =  DisplayAmount($bssDataItem->deferred_tax_liability, $unit);

						$term_loans_from_banks["values"][$i]["key"] = $periodItem["key"];
						$term_loans_from_banks["values"][$i]["value"] =  DisplayAmount($bssDataItem->term_loans_from_banks, $unit);

						$term_loans_from_financial_istitution["values"][$i]["key"] = $periodItem["key"];
						$term_loans_from_financial_istitution["values"][$i]["value"] =  DisplayAmount($bssDataItem->term_loans_from_financial_istitution, $unit);

						$term_deposits["values"][$i]["key"] = $periodItem["key"];
						$term_deposits["values"][$i]["value"] =  DisplayAmount($bssDataItem->term_deposits, $unit);

						$borrowings_from_subsidiaries["values"][$i]["key"] = $periodItem["key"];
						$borrowings_from_subsidiaries["values"][$i]["value"] =  DisplayAmount($bssDataItem->borrowings_from_subsidiaries, $unit);

						$unsecured_loans["values"][$i]["key"] = $periodItem["key"];
						$unsecured_loans["values"][$i]["value"] =  DisplayAmount($bssDataItem->unsecured_loans, $unit);

						$other_term_liabilities["values"][$i]["key"] = $periodItem["key"];
						$other_term_liabilities["values"][$i]["value"] =  DisplayAmount($bssDataItem->other_term_liabilities, $unit);

						$total_term_liabilities["values"][$i]["key"] = $periodItem["key"];
						$total_term_liabilities["values"][$i]["value"] =  DisplayAmount($bssDataItem->total_term_liabilities, $unit);

						$total_outside_liabilities["values"][$i]["key"] = $periodItem["key"];
						$total_outside_liabilities["values"][$i]["value"] =  DisplayAmount($bssDataItem->total_outside_liabilities, $unit);

						$equity_share_capital["values"][$i]["key"] = $periodItem["key"];
						$equity_share_capital["values"][$i]["value"] =  DisplayAmount($bssDataItem->equity_share_capital, $unit);

						$share_capital_paid_up["values"][$i]["key"] = $periodItem["key"];
						$share_capital_paid_up["values"][$i]["value"] =  DisplayAmount($bssDataItem->share_capital_paid_up, $unit);

						$share_application_money["values"][$i]["key"] = $periodItem["key"];
						$share_application_money["values"][$i]["value"] =  DisplayAmount($bssDataItem->share_application_money, $unit);

						$general_reserve["values"][$i]["key"] = $periodItem["key"];
						$general_reserve["values"][$i]["value"] =  DisplayAmount($bssDataItem->general_reserve, $unit);

						$revaluation_reserve["values"][$i]["key"] = $periodItem["key"];
						$revaluation_reserve["values"][$i]["value"] =  DisplayAmount($bssDataItem->revaluation_reserve, $unit);

						$partners_capital["values"][$i]["key"] = $periodItem["key"];
						$partners_capital["values"][$i]["value"] =  DisplayAmount($bssDataItem->partners_capital, $unit);

						$balance_in_partners_current_ac["values"][$i]["key"] = $periodItem["key"];
						$balance_in_partners_current_ac["values"][$i]["value"] =  DisplayAmount($bssDataItem->balance_in_partners_current_ac, $unit);

						$share_premium["values"][$i]["key"] = $periodItem["key"];
						$share_premium["values"][$i]["value"] =  DisplayAmount($bssDataItem->share_premium, $unit);

						$capital_subsidy["values"][$i]["key"] = $periodItem["key"];
						$capital_subsidy["values"][$i]["value"] =  DisplayAmount($bssDataItem->capital_subsidy, $unit);

						$quasi_equity["values"][$i]["key"] = $periodItem["key"];
						$quasi_equity["values"][$i]["value"] =  DisplayAmount($bssDataItem->quasi_equity, $unit);

						$balance_in_pl_account["values"][$i]["key"] = $periodItem["key"];
						$balance_in_pl_account["values"][$i]["value"] =  DisplayAmount($bssDataItem->balance_in_pl_account, $unit);

						$net_worth["values"][$i]["key"] = $periodItem["key"];
						$net_worth["values"][$i]["value"] =  DisplayAmount($bssDataItem->net_worth, $unit);

						$cash_balances["values"][$i]["key"] = $periodItem["key"];
						$cash_balances["values"][$i]["value"] =  DisplayAmount($bssDataItem->cash_balances, $unit);

						$bank_balances["values"][$i]["key"] = $periodItem["key"];
						$bank_balances["values"][$i]["value"] =  DisplayAmount($bssDataItem->bank_balances, $unit);

						$investments["values"][$i]["key"] = $periodItem["key"];
						$investments["values"][$i]["value"] =  DisplayAmount($bssDataItem->investments, $unit);

						$govt_and_other_trustee_securities["values"][$i]["key"] = $periodItem["key"];
						$govt_and_other_trustee_securities["values"][$i]["value"] =  DisplayAmount($bssDataItem->govt_and_other_trustee_securities, $unit);

						$fixed_deposits_with_banks["values"][$i]["key"] = $periodItem["key"];
						$fixed_deposits_with_banks["values"][$i]["value"] =  DisplayAmount($bssDataItem->fixed_deposits_with_banks, $unit);

						$others_investments_in_subsidiaries["values"][$i]["key"] = $periodItem["key"];
						$others_investments_in_subsidiaries["values"][$i]["value"] =  DisplayAmount($bssDataItem->others_investments_in_subsidiaries, $unit);

						$receivables["values"][$i]["key"] = $periodItem["key"];
						$receivables["values"][$i]["value"] =  DisplayAmount($bssDataItem->receivables, $unit);

						$domestic_receivables["values"][$i]["key"] = $periodItem["key"];
						$domestic_receivables["values"][$i]["value"] =  DisplayAmount($bssDataItem->domestic_receivables, $unit);

						$export_receivables["values"][$i]["key"] = $periodItem["key"];
						$export_receivables["values"][$i]["value"] =  DisplayAmount($bssDataItem->export_receivables, $unit);

						$inventory["values"][$i]["key"] = $periodItem["key"];
						$inventory["values"][$i]["value"] =  DisplayAmount($bssDataItem->inventory, $unit);

						$raw_Materials_imported["values"][$i]["key"] = $periodItem["key"];
						$raw_Materials_imported["values"][$i]["value"] =  DisplayAmount($bssDataItem->raw_Materials_imported, $unit);

						$raw_materials_indigenous["values"][$i]["key"] = $periodItem["key"];
						$raw_materials_indigenous["values"][$i]["value"] =  DisplayAmount($bssDataItem->raw_materials_indigenous, $unit);

						$work_in_process["values"][$i]["key"] = $periodItem["key"];
						$work_in_process["values"][$i]["value"] =  DisplayAmount($bssDataItem->work_in_process, $unit);

						$finished_goods["values"][$i]["key"] = $periodItem["key"];
						$finished_goods["values"][$i]["value"] =  DisplayAmount($bssDataItem->finished_goods, $unit);

						$other_consumable_spares_imported["values"][$i]["key"] = $periodItem["key"];
						$other_consumable_spares_imported["values"][$i]["value"] =  DisplayAmount($bssDataItem->other_consumable_spares_imported, $unit);

						$other_consumable_spares_indigenous["values"][$i]["key"] = $periodItem["key"];
						$other_consumable_spares_indigenous["values"][$i]["value"] =  DisplayAmount($bssDataItem->other_consumable_spares_indigenous, $unit);

						$adv_to_suppliers_of_raw_materials["values"][$i]["key"] = $periodItem["key"];
						$adv_to_suppliers_of_raw_materials["values"][$i]["value"] =  DisplayAmount($bssDataItem->adv_to_suppliers_of_raw_materials, $unit);
						
						$advance_payment_of_tax["values"][$i]["key"] = $periodItem["key"];
						$advance_payment_of_tax["values"][$i]["value"] =  DisplayAmount($bssDataItem->adv_payment_of_tax, $unit);
						
						//$other_current_assets["values"][$i]["key"] = $periodItem["key"];
						//$other_current_assets["values"][$i]["value"] =  DisplayAmount($bssDataItem->other_current_assets, $unit);

						$prepaid_expenses["values"][$i]["key"] = $periodItem["key"];
						$prepaid_expenses["values"][$i]["value"] =  DisplayAmount($bssDataItem->prepaid_expenses, $unit);

						$other_advances_current_asset["values"][$i]["key"] = $periodItem["key"];
						$other_advances_current_asset["values"][$i]["value"] =  DisplayAmount($bssDataItem->other_advances_current_asset, $unit);

						$total_current_assets["values"][$i]["key"] = $periodItem["key"];
						$total_current_assets["values"][$i]["value"] =  DisplayAmount($bssDataItem->total_current_assets, $unit);

						$gross_block["values"][$i]["key"] = $periodItem["key"];
						$gross_block["values"][$i]["value"] =  DisplayAmount($bssDataItem->gross_block, $unit);

						$accumulated_depreciation["values"][$i]["key"] = $periodItem["key"];
						$accumulated_depreciation["values"][$i]["value"] =  DisplayAmount($bssDataItem->accumulated_depreciation, $unit);

						$net_block["values"][$i]["key"] = $periodItem["key"];
						$net_block["values"][$i]["value"] =  DisplayAmount($bssDataItem->net_block, $unit);
						
						$capital_wip["values"][$i]["key"] = $periodItem["key"];
						$capital_wip["values"][$i]["value"] =  DisplayAmount($bssDataItem->capital_wip, $unit);
						
						$investments_in_group_concerns["values"][$i]["key"] = $periodItem["key"];
						$investments_in_group_concerns["values"][$i]["value"] =  DisplayAmount($bssDataItem->investments_in_group_concerns, $unit);
						
						$loans_to_group_concerns["values"][$i]["key"] = $periodItem["key"];
						$loans_to_group_concerns["values"][$i]["value"] =  DisplayAmount($bssDataItem->loans_to_group_concerns, $unit);
						
						$investments_in_others["values"][$i]["key"] = $periodItem["key"];
						$investments_in_others["values"][$i]["value"] =  DisplayAmount($bssDataItem->investments_in_others, $unit);

						$adv_to_suppliers_of_capital_goods["values"][$i]["key"] = $periodItem["key"];
						$adv_to_suppliers_of_capital_goods["values"][$i]["value"] =  DisplayAmount($bssDataItem->adv_to_suppliers_of_capital_goods, $unit);

						$deferred_receivables["values"][$i]["key"] = $periodItem["key"];
						$deferred_receivables["values"][$i]["value"] =  DisplayAmount($bssDataItem->deferred_receivables, $unit);

						$debtors_more_than_6_months["values"][$i]["key"] = $periodItem["key"];
						$debtors_more_than_6_months["values"][$i]["value"] =  DisplayAmount($bssDataItem->debtors_more_than_6_months, $unit);

						$others_loan_advances["values"][$i]["key"] = $periodItem["key"];
						$others_loan_advances["values"][$i]["value"] =  DisplayAmount($bssDataItem->others_loan_advances, $unit);

						$security_deposits["values"][$i]["key"] = $periodItem["key"];
						$security_deposits["values"][$i]["value"] =  DisplayAmount($bssDataItem->security_deposits, $unit);

						$deposits_with_government_dept["values"][$i]["key"] = $periodItem["key"];
						$deposits_with_government_dept["values"][$i]["value"] =  DisplayAmount($bssDataItem->deposits_with_government_dept, $unit);

						$deferred_tax_asset["values"][$i]["key"] = $periodItem["key"];
						$deferred_tax_asset["values"][$i]["value"] =  DisplayAmount($bssDataItem->deferred_tax_asset, $unit);

						$other_non_current_assets["values"][$i]["key"] = $periodItem["key"];
						$other_non_current_assets["values"][$i]["value"] =  DisplayAmount($bssDataItem->other_non_current_assets, $unit);

						$total_non_current_assets["values"][$i]["key"] = $periodItem["key"];
						$total_non_current_assets["values"][$i]["value"] =  DisplayAmount($bssDataItem->total_non_current_assets, $unit);

						$goodwill_patents_trademarks["values"][$i]["key"] = $periodItem["key"];
						$goodwill_patents_trademarks["values"][$i]["value"] =  DisplayAmount($bssDataItem->goodwill_patents_trademarks, $unit);

						$misc_exp_not_written_off["values"][$i]["key"] = $periodItem["key"];
						$misc_exp_not_written_off["values"][$i]["value"] =  DisplayAmount($bssDataItem->misc_exp_not_written_off, $unit);

						$other_deferred_revenue_exp["values"][$i]["key"] = $periodItem["key"];
						$other_deferred_revenue_exp["values"][$i]["value"] =  DisplayAmount($bssDataItem->other_deferred_revenue_exp, $unit);

						$total_intangible_assets["values"][$i]["key"] = $periodItem["key"];
						$total_intangible_assets["values"][$i]["value"] =  DisplayAmount($bssDataItem->total_intangible_assets, $unit);

						$total_assets["values"][$i]["key"] = $periodItem["key"];
						$total_assets["values"][$i]["value"] =  DisplayAmount($bssDataItem->total_assets, $unit);
						
		  
					  }
				  }
				  
			  }
  
			  $balanceSheet[] = $currentLiabilities;
			  $balanceSheet[] = $stBorrowingsFromBanks;
			  $balanceSheet[] = $bank_borrowings_from_applicant_bank ;
			  $balanceSheet[] = $bank_borrowings_from_other_banks ;

			  $balanceSheet[] = $borrowings_sub_total ;

			  $balanceSheet[] = $st_borrowings_from_associates ;
			  $balanceSheet[] = $st_borrowings_from_others ;
			  $balanceSheet[] = $creditors_for_purchases_others ;
			  $balanceSheet[] = $creditors_for_purchases_group_companies ;
			  $balanceSheet[] = $creditors_for_expenses ;
			  $balanceSheet[] = $advances_payments_from_customers ;
			  $balanceSheet[] = $provisions;
			  $balanceSheet[] = $provisions_for_tax ;
			  $balanceSheet[] = $provisions_for_deferred_tax ;
			  $balanceSheet[] = $others ;
			  $balanceSheet[] = $dividends_payable ;
			  $balanceSheet[] = $statutory_liabilities_due_within_one_year ;
			  $balanceSheet[] = $installments_of_term_to_banks ;
			  $balanceSheet[] = $installments_of_term_loans_to_others ;
			  $balanceSheet[] = $deposits_due_payable_within_a_year ;
			  $balanceSheet[] = $other_current_liabilities_due_within_one_year ;

			  $balanceSheet[] = $total_current_liabilities ;
			  $balanceSheet[] = $term_liabilities;

			  $balanceSheet[] = $debentures_maturing_after_1_year ;
			  $balanceSheet[] = $preference_share_capital_maturity_within_12_years ;
			  $balanceSheet[] = $dealers_deposit ;
			  $balanceSheet[] = $deferred_tax_liability ;
			  $balanceSheet[] = $term_loans_from_banks ;
			  $balanceSheet[] = $term_loans_from_financial_istitution ;
			  $balanceSheet[] = $term_deposits ;
			  $balanceSheet[] = $borrowings_from_subsidiaries ;
			  $balanceSheet[] = $unsecured_loans ;
			  $balanceSheet[] = $other_term_liabilities ;

			  $balanceSheet[] = $total_term_liabilities ;
			  $balanceSheet[] = $total_outside_liabilities ;

			  
			  $balanceSheet[] = $net_worth_title;
			  $balanceSheet[] = $equity_share_capital ;
			  $balanceSheet[] = $share_capital_paid_up ;
			  $balanceSheet[] = $share_application_money ;
			  $balanceSheet[] = $general_reserve ;
			  $balanceSheet[] = $revaluation_reserve ;
			  $balanceSheet[] = $partners_capital ;
			  $balanceSheet[] = $balance_in_partners_current_ac ;
			  
			  $balanceSheet[] = $other_reserve_and_surplus;

			  $balanceSheet[] = $share_premium ;
			  $balanceSheet[] = $capital_subsidy ;
			  $balanceSheet[] = $quasi_equity ;
			  $balanceSheet[] = $balance_in_pl_account ;
			  
			  $balanceSheet[] = $net_worth ;
			  $balanceSheet[] = $total_liabilities ;

			  $balanceSheet[] = $balance_sheet_assets_input;
			  $balanceSheet[] = $current_assets_title;

			  $balanceSheet[] = $cash_balances ;
			  $balanceSheet[] = $bank_balances ;

			  $balanceSheet[] = $investments ;

			  $balanceSheet[] = $govt_and_other_trustee_securities ;
			  $balanceSheet[] = $fixed_deposits_with_banks ;
			  $balanceSheet[] = $others_investments_in_subsidiaries ;

			  $balanceSheet[] = $receivables ;

			  $balanceSheet[] = $domestic_receivables ;
			  $balanceSheet[] = $export_receivables ;

			  $balanceSheet[] = $inventory ;

			  $balanceSheet[] = $raw_Materials_imported ;
			  $balanceSheet[] = $raw_materials_indigenous ;
			  $balanceSheet[] = $work_in_process ;
			  $balanceSheet[] = $finished_goods ;
			  $balanceSheet[] = $other_consumable_spares_imported ;
			  $balanceSheet[] = $other_consumable_spares_indigenous ;
			  $balanceSheet[] = $adv_to_suppliers_of_raw_materials ;
			  $balanceSheet[] = $advance_payment_of_tax ;
			  $balanceSheet[] = $other_current_assets;
			  
			  $balanceSheet[] = $fixed_assets_title;

			  $balanceSheet[] = $prepaid_expenses ;
			  $balanceSheet[] = $other_advances_current_asset ;

			  $balanceSheet[] = $total_current_assets ;

			  $balanceSheet[] = $gross_block ;
			  $balanceSheet[] = $accumulated_depreciation ;

			  $balanceSheet[] = $net_block ;
			  $balanceSheet[] = $non_current_assets_title;
			  $balanceSheet[] = $investments_book_debts_title;

			  $balanceSheet[] = $capital_wip ;
			  $balanceSheet[] = $investments_in_group_concerns ;
			  $balanceSheet[] = $loans_to_group_concerns ;
			  $balanceSheet[] = $investments_in_others ;
			  $balanceSheet[] = $adv_to_suppliers_of_capital_goods ;
			  $balanceSheet[] = $deferred_receivables ;
			  $balanceSheet[] = $debtors_more_than_6_months ;
			  $balanceSheet[] = $others_loan_advances ;
			  $balanceSheet[] = $security_deposits ;
			  $balanceSheet[] = $deposits_with_government_dept ;
			  $balanceSheet[] = $deferred_tax_asset ;
			  $balanceSheet[] = $other_non_current_assets ;

			  $balanceSheet[] = $total_non_current_assets ;

			  $balanceSheet[] = $intangible_assets_title ;

			  $balanceSheet[] = $goodwill_patents_trademarks ;
			  $balanceSheet[] = $misc_exp_not_written_off ;
			  $balanceSheet[] = $other_deferred_revenue_exp ;

			  $balanceSheet[] = $total_intangible_assets ;
			  $balanceSheet[] = $total_assets ;
			  
  
  
  
			  return $balanceSheet;
	  }
  }

  if(!function_exists('GetPLSourceFromDB')) 
  {
	  function GetPLSourceFromDB($plsPeriods, $plsDbData, $unit = "million")
	  {
		$profitAndLoss = null;
		
		
		$sales = array("label" => "Sales", "values" => null);
		$salesDomestic = array("label" => "- Domestic", "values" => null);
		$salesExport = array("label" => "- Export", "values" => null);
		$salesSubTotal = array("label" => "Sub Total", "values" => null, "type" => "sales");
		$exciseDuty = array("label" => "Less Excise Duty (if applicable)", "values" => null);
		$netSales = array("label" => "Net Sales", "values" => null);

		$riseFallInNetSales = array("label" => "% wise rise/fall in net sales as compared to previous year", 
			"values" => null);
		$otherIncomes = array("label" => "Other Incomes", "values" => null);
		$exportIncentives = array("label" => "Export Incentive", "values" => null);
		$otherIncome = array("label" => "Other Income", "values" => null);
		$totalOperatingIncome = array("label" => "Total Operating Income", "values" => null);

		$costOfSales = array("label" => "Cost of Sales", "values" => null);
		$rawMatConsumed = array("label" => "Raw materials consumed ", "values" => null);
		$rawMatImported = array("label" => "                        i) Imported", "values" => null);
		$rawMatIndigenous = array("label" => "                        ii) Indigenous", "values" => null);

		$otherSparesConsumed = array("label" => "Other Spares consumed ", "values" => null);
		$otherSparesImported = array("label" => "                        i) Other Spares Imported", "values" => null);
		$otherSparesIndigenous = array("label" => "                        ii) Other Spares Indigenous", "values" => null);

		$powerAndFuel = array("label" => "Power and fuel ", "values" => null);
		$directLabourAndWages = array("label" => "Direct labour and wages", "values" => null);
		$otherManfExp = array("label" => "Other manufacturing expenses", "values" => null);
		$depriciation = array("label" => "Depreciation", "values" => null);
		$costOfSalesSubTotal = array("label" => "Sub Total", "values" => null, "type" => "cost of sale");
		
		$opStockWIP = array("label" => "Add: Op. Stock of WIP", "values" => null);
		$clStockWIP = array("label" => "Less: Cl. Stock of WIP", "values" => null);
		$totalCostOfProd = array("label" => "Total Cost of Production", "values" => null);

		$opStockOfFG = array("label" => "Add Opening Stock of Finished Goods", "values" => null);
		$clStockOfFG = array("label" => "Less: Closing Stock  of Finished Goods", "values" => null);
		$totalCostOfSales = array("label" => "Total Cost of Sales", "values" => null);
		
		$adnAndSellExp = array("label" => "Administrative and Selling expenses", "values" => null);
		$salaryAndStaffExp = array("label" => "Salary & Staff Expenses", "values" => null);
		$badDebts = array("label" => "Bad Debts", "values" => null);
		$sellGenAdmExp = array("label" => "Selling, Gen. & Administration Exp", "values" => null);
		$otherAdmExp = array("label" => "Other Administration Exp", "values" => null);
		
		$admSubTotal = array("label" => "Sub Total", "values" => null, "type" => "other admin exp");
		$opBeforeInterest = array("label" => "Operating Profit before Interest", "values" => null);

		
		$financeCharges = array("label" => "Finance Charges", "values" => null);
		$interestWCLoans = array("label" => "Interest - Working capital loans", "values" => null);
		$interestTermLoans = array("label" => "Interest - Term Loans/Fixed loans", "values" => null);
		$bankCharges = array("label" => "Bank Charges", "values" => null);
		$totalInterest = array("label" => "Total Interest", "values" => null);
		$opAfterInterest = array("label" => "Operating Profit after Interest", "values" => null);
		
		
		$nonOpItems = array("label" => "Non Operating Items", "values" => null);
		$addNonOpItem = array("label" => "Add Non Operating Income", "values" => null);
		$interestIncome = array("label" => "Interest Income", "values" => null);
		$profitOnSaleOfAssets = array("label" => "Profit on sale of assets/ investments", "values" => null);
		$divRceived = array("label" => "Dividend received", "values" => null);
		$forexGains = array("label" => "Forex gains", "values" => null);
		$extraOrdIncome = array("label" => "Extraordinary Income", "values" => null);
		$otherNonOpIncome = array("label" => "Other Non Operating Income", "values" => null);
		$totalNonOpIncome = array("label" => "Total non-operating Income", "values" => null);

		
		$deductNonOpExp = array("label" => "Deduct Non Operating Expenses", "values" => null);
		$lossOnSaleOfAsset = array("label" => "Loss on sale of assets", "values" => null);
		$extraOrdExp = array("label" => "Extraordinary Expenses ", "values" => null);
		$forexLoses = array("label" => "Forex losses", "values" => null);
		$otherNonOpExp = array("label" => "Other Non- operating expenses", "values" => null);
		$totalNonOpExp = array("label" => "Total Non-operating expenses", "values" => null);
		$netNonOpIncomeExp = array("label" => "Net of Non-operating Income / Expenses", "values" => null);
		$profitBeforeTax = array("label" => "Profit Before tax ", "values" => null);

		
		$provForTaxation = array("label" => "Provision for taxation:", "values" => null);
		$currentProv = array("label" => "Current", "values" => null);
		$deferredProv = array("label" => "Deferred", "values" => null);
		$provSubTotal = array("label" => "Sub Total", "values" => null, "type" => "provision for tax");
		$netProfitAfterTax = array("label" => "Net Profit After tax", "values" => null);
		$dividendPaid = array("label" => "Dividend Paid", "values" => null);
		$retainedProfit = array("label" => "Retained Profit ", "values" => null);
		

		for($i = 0 ; $i < count($plsPeriods); $i++)
		{
			$periodItem = $plsPeriods[$i];

			foreach($plsDbData as $plsDataItem)
			{
				if($plsDataItem->year == $periodItem["year"] && $periodItem["ptype"] == $plsDataItem->period_type)
				{
				
					$salesDomestic["values"][$i]["key"] = $periodItem["key"];
					$salesDomestic["values"][$i]["value"] = DisplayAmount($plsDataItem->sales_domestic, $unit);

					$salesExport["values"][$i]["key"] = $periodItem["key"];
					$salesExport["values"][$i]["value"] = DisplayAmount($plsDataItem->sales_export, $unit);

					$salesSubTotal["values"][$i]["key"] = $periodItem["key"];
					$salesSubTotal["values"][$i]["value"] = DisplayAmount($plsDataItem->sales_total, $unit);

					$exciseDuty["values"][$i]["key"] = $periodItem["key"];
					$exciseDuty["values"][$i]["value"] =  DisplayAmount($plsDataItem->excise_duty, $unit);

					$netSales["values"][$i]["key"] = $periodItem["key"];
					$netSales["values"][$i]["value"] = DisplayAmount($plsDataItem->net_sales, $unit);
					

					$exportIncentives["values"][$i]["key"] = $periodItem["key"];
					$exportIncentives["values"][$i]["value"] =  DisplayAmount($plsDataItem->export_incentive, $unit);
					
					$otherIncome["values"][$i]["key"] = $periodItem["key"];
					$otherIncome["values"][$i]["value"] =  DisplayAmount($plsDataItem->other_income, $unit);

					$totalOperatingIncome["values"][$i]["key"] = $periodItem["key"];
					$totalOperatingIncome["values"][$i]["value"] = DisplayAmount($plsDataItem->total_operating_income, $unit);

					$rawMatImported["values"][$i]["key"] = $periodItem["key"];
					$rawMatImported["values"][$i]["value"] =  DisplayAmount($plsDataItem->raw_materials_imported, $unit);

					$rawMatIndigenous["values"][$i]["key"] = $periodItem["key"];
					$rawMatIndigenous["values"][$i]["value"] =  DisplayAmount($plsDataItem->raw_materials_indigenous, $unit);

					$otherSparesImported["values"][$i]["key"] = $periodItem["key"];
					$otherSparesImported["values"][$i]["value"] =  DisplayAmount($plsDataItem->other_spares_imported, $unit);

					$otherSparesIndigenous["values"][$i]["key"] = $periodItem["key"];
					$otherSparesIndigenous["values"][$i]["value"] =  DisplayAmount($plsDataItem->other_spares_indegenous, $unit);

					$powerAndFuel["values"][$i]["key"] = $periodItem["key"];
					$powerAndFuel["values"][$i]["value"] =  DisplayAmount($plsDataItem->power_and_fuel, $unit);
					
					$directLabourAndWages["values"][$i]["key"] = $periodItem["key"];
					$directLabourAndWages["values"][$i]["value"] =  DisplayAmount($plsDataItem->direct_labour_and_wages, $unit);
					
					$otherManfExp["values"][$i]["key"] = $periodItem["key"];
					$otherManfExp["values"][$i]["value"] = DisplayAmount($plsDataItem->other_manf_exp, $unit);

					$depriciation["values"][$i]["key"] = $periodItem["key"];
					$depriciation["values"][$i]["value"] =  DisplayAmount($plsDataItem->depreciation, $unit);

					$costOfSalesSubTotal["values"][$i]["key"] = $periodItem["key"];
					$costOfSalesSubTotal["values"][$i]["value"] =  DisplayAmount($plsDataItem->cos_sub_total, $unit);

					$opStockWIP["values"][$i]["key"] = $periodItem["key"];
					$opStockWIP["values"][$i]["value"] =  DisplayAmount($plsDataItem->op_stock_of_wip, $unit);
					$clStockWIP["values"][$i]["key"] = $periodItem["key"];
					$clStockWIP["values"][$i]["value"] =  DisplayAmount($plsDataItem->cl_stock_of_wip, $unit);
					
					$totalCostOfProd["values"][$i]["key"] = $periodItem["key"];
					$totalCostOfProd["values"][$i]["value"] =  DisplayAmount($plsDataItem->total_cos_of_prod, $unit);

					$opStockOfFG["values"][$i]["key"] = $periodItem["key"];
					$opStockOfFG["values"][$i]["value"] =  DisplayAmount($plsDataItem->os_of_finished_goods, $unit);
					$clStockOfFG["values"][$i]["key"] = $periodItem["key"];
					$clStockOfFG["values"][$i]["value"] =  DisplayAmount($plsDataItem->cs_of_finished_goods, $unit);
					$totalCostOfSales["values"][$i]["key"] = $periodItem["key"];
					$totalCostOfSales["values"][$i]["value"] = DisplayAmount($plsDataItem->total_cost_of_sales, $unit);

					
					$salaryAndStaffExp["values"][$i]["key"] = $periodItem["key"];
					$salaryAndStaffExp["values"][$i]["value"] =  DisplayAmount($plsDataItem->salary_staff_exp, $unit);
					$badDebts["values"][$i]["key"] = $periodItem["key"];
					$badDebts["values"][$i]["value"] =  DisplayAmount($plsDataItem->bad_debts, $unit);
					$sellGenAdmExp["values"][$i]["key"] = $periodItem["key"];
					$sellGenAdmExp["values"][$i]["value"] =  DisplayAmount($plsDataItem->selling_admin_exp, $unit);
					$otherAdmExp["values"][$i]["key"] = $periodItem["key"];
					$otherAdmExp["values"][$i]["value"] =  DisplayAmount($plsDataItem->other_admin_exp, $unit);
					
					$admSubTotal["values"][$i]["key"] = $periodItem["key"];
					$admSubTotal["values"][$i]["value"] = DisplayAmount($plsDataItem->admin_sell_exp_sub_total, $unit);

					$opBeforeInterest["values"][$i]["key"] = $periodItem["key"];
					$opBeforeInterest["values"][$i]["value"] = DisplayAmount($plsDataItem->operating_profit_before_int, $unit);

					
					
					$interestWCLoans["values"][$i]["key"] = $periodItem["key"];
					$interestWCLoans["values"][$i]["value"] = DisplayAmount($plsDataItem->interest_wc_loans, $unit);
					$interestTermLoans["values"][$i]["key"] = $periodItem["key"];
					$interestTermLoans["values"][$i]["value"] = DisplayAmount($plsDataItem->interest_term_loans, $unit);
					$bankCharges["values"][$i]["key"] = $periodItem["key"];
					$bankCharges["values"][$i]["value"] = DisplayAmount($plsDataItem->bank_charges, $unit);
					$totalInterest["values"][$i]["key"] = $periodItem["key"];
					$totalInterest["values"][$i]["value"] = DisplayAmount($plsDataItem->total_interest, $unit);
					$opAfterInterest["values"][$i]["key"] = $periodItem["key"];
					$opAfterInterest["values"][$i]["value"] = DisplayAmount($plsDataItem->operating_profit_after_interest, $unit);


					$interestIncome["values"][$i]["key"] = $periodItem["key"];
					$interestIncome["values"][$i]["value"] = DisplayAmount($plsDataItem->interest_income, $unit);
					$profitOnSaleOfAssets["values"][$i]["key"] = $periodItem["key"];
					$profitOnSaleOfAssets["values"][$i]["value"] = DisplayAmount($plsDataItem->profit_on_sale_of_assets, $unit);
					$divRceived["values"][$i]["key"] = $periodItem["key"];
					$divRceived["values"][$i]["value"] = DisplayAmount($plsDataItem->dividend_received, $unit);
					$forexGains["values"][$i]["key"] = $periodItem["key"];
					$forexGains["values"][$i]["value"] = DisplayAmount($plsDataItem->forex_gains, $unit);
					$extraOrdIncome["values"][$i]["key"] = $periodItem["key"];
					$extraOrdIncome["values"][$i]["value"] = DisplayAmount($plsDataItem->extraordinary_income, $unit);
					$otherNonOpIncome["values"][$i]["key"] = $periodItem["key"];
					$otherNonOpIncome["values"][$i]["value"] = DisplayAmount($plsDataItem->other_non_op_income, $unit);
					$totalNonOpIncome["values"][$i]["key"] = $periodItem["key"];
					$totalNonOpIncome["values"][$i]["value"] = DisplayAmount($plsDataItem->total_non_op_income, $unit);
					
						
					$lossOnSaleOfAsset["values"][$i]["key"] = $periodItem["key"];
					$lossOnSaleOfAsset["values"][$i]["value"] = DisplayAmount($plsDataItem->loss_on_sale_of_assets, $unit);
					$extraOrdExp["values"][$i]["key"] = $periodItem["key"];
					$extraOrdExp["values"][$i]["value"] = DisplayAmount($plsDataItem->extra_ordinary_expenses, $unit);
					$forexLoses["values"][$i]["key"] = $periodItem["key"];
					$forexLoses["values"][$i]["value"] = DisplayAmount($plsDataItem->forex_loses, $unit);
					$otherNonOpExp["values"][$i]["key"] = $periodItem["key"];
					$otherNonOpExp["values"][$i]["value"] = DisplayAmount($plsDataItem->other_non_op_expenses, $unit);
					
					$totalNonOpExp["values"][$i]["key"] = $periodItem["key"];
					$totalNonOpExp["values"][$i]["value"] = DisplayAmount($plsDataItem->total_non_op_exp, $unit);

					$netNonOpIncomeExp["values"][$i]["key"] = $periodItem["key"];
					$netNonOpIncomeExp["values"][$i]["value"] = DisplayAmount($plsDataItem->net_op_non_op_inc_exp, $unit);

					$profitBeforeTax["values"][$i]["key"] = $periodItem["key"];
					$profitBeforeTax ["values"][$i]["value"]= DisplayAmount($plsDataItem->profit_before_tax, $unit);

					$currentProv["values"][$i]["key"] = $periodItem["key"];
					$currentProv["values"][$i]["value"] = DisplayAmount($plsDataItem->prov_for_tax_current, $unit);
					$deferredProv["values"][$i]["key"] = $periodItem["key"];
					$deferredProv["values"][$i]["value"] = DisplayAmount($plsDataItem->prov_for_tax_deferred, $unit);
					$provSubTotal["values"][$i]["key"] = $periodItem["key"];
					$provSubTotal["values"][$i]["value"] = DisplayAmount($plsDataItem->prov_for_tax_subtotal, $unit);
					$netProfitAfterTax["values"][$i]["key"] = $periodItem["key"];
					$netProfitAfterTax["values"][$i]["value"] = DisplayAmount($plsDataItem->net_profit_after_tax, $unit);
					$dividendPaid["values"][$i]["key"] = $periodItem["key"];
					$dividendPaid["values"][$i]["value"] = DisplayAmount($plsDataItem->dividend_paid, $unit);
					$retainedProfit["values"][$i]["key"] = $periodItem["key"];
					$retainedProfit["values"][$i]["value"] = DisplayAmount($plsDataItem->retained_profit, $unit);
				}
			}	
		}



		$profitAndLoss[] = $sales;
		$profitAndLoss[] = $salesDomestic;
		$profitAndLoss[] = $salesExport;
		$profitAndLoss[] = $salesSubTotal;
		$profitAndLoss[] = $exciseDuty;
		$profitAndLoss[] = $netSales;
		$profitAndLoss[] = $riseFallInNetSales;
		
		
		$profitAndLoss[] = $otherIncomes;
		$profitAndLoss[] = $exportIncentives;
		$profitAndLoss[] = $otherIncome;

		$profitAndLoss[] = $totalOperatingIncome;
		$profitAndLoss[] = $costOfSales;
		$profitAndLoss[] = $rawMatConsumed;
		$profitAndLoss[] = $rawMatImported;
		$profitAndLoss[] = $rawMatIndigenous;

		$profitAndLoss[] = $otherSparesConsumed;
		$profitAndLoss[] = $otherSparesImported;
		$profitAndLoss[] = $otherSparesIndigenous;

		$profitAndLoss[] = $powerAndFuel;
		$profitAndLoss[] = $directLabourAndWages;
		$profitAndLoss[] = $otherManfExp;
		$profitAndLoss[] = $depriciation;
		$profitAndLoss[] = $costOfSalesSubTotal;

	
		$profitAndLoss[] = $opStockWIP;
		$profitAndLoss[] = $clStockWIP;
		$profitAndLoss[] = $totalCostOfProd;

		$profitAndLoss[] = $opStockOfFG;
		$profitAndLoss[] = $clStockOfFG;
		$profitAndLoss[] = $totalCostOfSales;

		$profitAndLoss[] = $adnAndSellExp;
		$profitAndLoss[] = $salaryAndStaffExp;
		$profitAndLoss[] = $badDebts;
		$profitAndLoss[] = $sellGenAdmExp;
		$profitAndLoss[] = $otherAdmExp;
		$profitAndLoss[] = $admSubTotal;
		$profitAndLoss[] = $opBeforeInterest;

		
		$profitAndLoss[] = $financeCharges;
		$profitAndLoss[] = $interestWCLoans;
		$profitAndLoss[] = $interestTermLoans;
		$profitAndLoss[] = $bankCharges;
		$profitAndLoss[] = $totalInterest;
		$profitAndLoss[] = $opAfterInterest;


		$profitAndLoss[] = $nonOpItems;
		$profitAndLoss[] = $addNonOpItem;
		$profitAndLoss[] = $interestIncome;
		$profitAndLoss[] = $profitOnSaleOfAssets;
		$profitAndLoss[] = $divRceived;
		$profitAndLoss[] = $forexGains;
		$profitAndLoss[] = $extraOrdIncome;
		$profitAndLoss[] = $otherNonOpIncome;
		$profitAndLoss[] = $totalNonOpIncome;

		$profitAndLoss[] = $deductNonOpExp;
		$profitAndLoss[] = $lossOnSaleOfAsset;
		$profitAndLoss[] = $extraOrdExp;
		$profitAndLoss[] = $forexLoses;
		$profitAndLoss[] = $otherNonOpExp;
		$profitAndLoss[] = $totalNonOpExp;
		$profitAndLoss[] = $netNonOpIncomeExp;
		$profitAndLoss[] = $profitBeforeTax;

		$profitAndLoss[] = $provForTaxation;
		$profitAndLoss[] = $currentProv;
		$profitAndLoss[] = $deferredProv;
		$profitAndLoss[] = $provSubTotal;
		$profitAndLoss[] = $netProfitAfterTax;
		$profitAndLoss[] = $dividendPaid;
		$profitAndLoss[] = $retainedProfit;
		

        return $profitAndLoss;
	  }
  }

?>