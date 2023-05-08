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

				$pbdita["values"][$i]["key"] = $periodItem["key"];
				$pbdita["values"][$i]["value"] = $depriciation + $opAfterInterest; 

				$pbditaMargin["values"][$i]["key"] = $periodItem["key"];
				$pbditaMargin["values"][$i]["value"] = $netSales["values"][$i]["value"] != 0 ? $income["values"][$i]["value"] / $netSales["values"][$i]["value"] : 0;
				
				$interest["values"][$i]["key"] = $periodItem["key"];
				$interest["values"][$i]["value"] = GetProfitandLossLineItem($profitloss, "Total Interest", $periodItem["key"]); 

				$depreciation["values"][$i]["key"] = $periodItem["key"];
				$depreciation["values"][$i]["value"] = GetProfitandLossLineItem($profitloss, "Depreciation", $periodItem["key"]); 

				$oProfitAfterInterest["values"][$i]["key"] = $periodItem["key"];
				$oProfitAfterInterest["values"][$i]["value"] = $opAfterInterest;
				
				$incomeExpenses["values"][$i]["key"] = $periodItem["key"];
				$incomeExpenses["values"][$i]["value"] =  GetProfitandLossLineItem($profitloss, "Net of Non-operating Income / Expenses", $periodItem["key"]); 

				$profitBeforeTax["values"][$i]["key"] = $periodItem["key"];
				$profitBeforeTax["values"][$i]["value"] =  GetProfitandLossLineItem($profitloss, "Profit Before tax", $periodItem["key"]); 

				$profitAfterTax["values"][$i]["key"] = $periodItem["key"];
				$profitAfterTax["values"][$i]["value"] =  GetProfitandLossLineItem($profitloss, "Net Profit After tax", $periodItem["key"]); 
				
				$netProfitMargin["values"][$i]["key"] = $periodItem["key"];
				$netProfitMargin["values"][$i]["value"] =  $netSales["values"][$i]["value"] != 0 ? $profitBeforeTax["values"][$i]["value"] / $netSales["values"][$i]["value"] : 0;

				$netCashAccurals["values"][$i]["key"] = $periodItem["key"];
				$netCashAccurals["values"][$i]["value"] = $profitBeforeTax["values"][$i]["value"] + $interest["values"][$i]["value"];

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

				$nonCurrentAsetsExFA["values"][$i]["key"] = $periodItem["key"];
				$nonCurrentAsetsExFA["values"][$i]["value"] = $iInGC + $loanToGC + $investInOthers + $advToSupp + $deffRecv + $debtTo6Months + $secDeposits + $depWithGovt + $deferredTaxAsset + $otherNcAssets;

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
				$aTNW["values"][$i]["value"] = $nonCurrentAsetsExFA["values"][$i]["value"] - $tangNetworth["values"][$i]["value"];

				
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

				$totalTermLiabilities = $debMaturingAfter1Year + $prefShareCapMatLessThen12Year + $dealersDeposit + $termLoansFromBanks + $termLoansFromFinancialInstitution + $termDeposits + $deferredTaxLiabilities + $borrowingFromSubs + $unsecuredLoans + $otherTermLiabilities ;
				
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
				$LTDbyTNW["values"][$i]["value"] = $nonCurrentAsetsExFA["values"][$i]["value"] > 0 ? $aTNW["values"][$i]["value"] / $nonCurrentAsetsExFA["values"][$i]["value"] : 0;
				

				$TOLbyTNW["values"][$i]["key"] = $periodItem["key"];
				$TOLbyTNW["values"][$i]["value"] = $nonCurrentAsetsExFA["values"][$i]["value"] > 0 ? $wCapitalBorrowing["values"][$i]["value"] / $nonCurrentAsetsExFA["values"][$i]["value"] : 0;
				
				
				$TOLbyATNW["values"][$i]["key"] = $periodItem["key"];
				$TOLbyATNW["values"][$i]["value"] = $expInGroupCo["values"][$i]["value"] > 0 ? $wCapitalBorrowing["values"][$i]["value"] / $expInGroupCo["values"][$i]["value"] : 0;

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
				$finishedGoodsInclTradedGoods  = GetBalanceSheetLineItem($balanceSheet, "Finished Goods (incl Traded Goods)", $periodItem["key"]); 
				$otherConsumableSparesImported  = GetBalanceSheetLineItem($balanceSheet, "Other consumable spares – Imported", $periodItem["key"]); 
				$otherConsumableSparesIndigenous  = GetBalanceSheetLineItem($balanceSheet, "Other consumable spares -  Indigenous", $periodItem["key"]); 
				$advToSuppOfRMSS = GetBalanceSheetLineItem($balanceSheet, "Advances to suppliers of Raw materials/Stores/Spares", $periodItem["key"]); 
				$advancePaymentofTax  = GetBalanceSheetLineItem($balanceSheet, "Advance payment of tax", $periodItem["key"]); 
				$prepaidExpenses  = GetBalanceSheetLineItem($balanceSheet, "Prepaid Expenses", $periodItem["key"]); 
				$otherAdvancesCurrentAsset  = GetBalanceSheetLineItem($balanceSheet, "Other Advances/current Asset", $periodItem["key"]); 

				$totalCurrAssets =  $cashBalances  + $bankBalances  + $govtandothertrusteeSecurities + $fixedDepositswithBanks + $othersInvestmentsinSubsidiariesGroupCompanies + $domesticReceivables  + $exportReceivables  + $rawMaterialsImported + $rawMaterialsIndigenous + $workinprocess  + $finishedGoodsInclTradedGoods  + 				$otherConsumableSparesImported  + $otherConsumableSparesIndigenous  + $advToSuppOfRMSS + $advancePaymentofTax  + $prepaidExpenses  + $otherAdvancesCurrentAsset ;

				$totalCurrAssetsItem["values"][$i]["key"] = $periodItem["key"];
				$totalCurrAssetsItem["values"][$i]["value"] = $totalCurrAssets;

				$totalCurrLiabilitiesItem["values"][$i]["key"] = $periodItem["key"];
				$totalCurrLiabilitiesItem["values"][$i]["value"] = $totalCurrLiabilities;

				$netWorkingCapital["values"][$i]["key"] = $periodItem["key"];
				$netWorkingCapital["values"][$i]["value"] = $TOLbyTNW["values"][$i]["value"] - $TOLbyATNW["values"][$i]["value"];

				$currentRatio["values"][$i]["key"] = $periodItem["key"];
				$currentRatio["values"][$i]["value"] = $TOLbyATNW["values"][$i]["value"] != 0 ? $TOLbyTNW["values"][$i]["value"] / $TOLbyATNW["values"][$i]["value"] : 0;

				$rawMaterialsImported  = GetBalanceSheetLineItem($balanceSheet, " Raw Materials – Imported", $periodItem["key"]); 
				$rawMaterialsIndigenous  = GetBalanceSheetLineItem($balanceSheet, " Raw Materials – Indigenous", $periodItem["key"]); 
				$workinprocess  = GetBalanceSheetLineItem($balanceSheet, " Work in process", $periodItem["key"]); 
				$finishedGoodsInclTradedGoods  = GetBalanceSheetLineItem($balanceSheet, "Finished Goods (incl Traded Goods)", $periodItem["key"]); 
				$otherConsumableSparesImported  = GetBalanceSheetLineItem($balanceSheet, "Other consumable spares – Imported", $periodItem["key"]); 
				$otherConsumableSparesIndigenous  = GetBalanceSheetLineItem($balanceSheet, "Other consumable spares -  Indigenous", $periodItem["key"]); 

				$inventories =  $rawMaterialsImported + $rawMaterialsIndigenous + $workinprocess + $finishedGoodsInclTradedGoods + $otherConsumableSparesImported + $otherConsumableSparesIndigenous;
				$totalCostofSales = GetProfitandLossLineItem($profitloss, "Total Cost of Sales", $periodItem["key"]); 

				$inventoryHoldingPeriod["values"][$i]["key"] = $periodItem["key"];
				$inventoryHoldingPeriod["values"][$i]["value"] = $totalCostofSales != 0 ? ($inventories / $totalCostofSales) * 365 : 0;

				$tradeReceivables =  $domesticReceivables  + $exportReceivables;
				$netSalesVal = $netSales["values"][$i]["value"];

				$debtorsHoldingPeriod["values"][$i]["key"] = $periodItem["key"];
				$debtorsHoldingPeriod["values"][$i]["value"] = $netSalesVal > 0 ? ($tradeReceivables / $netSalesVal) * 365 : 0;

				$totalRawMaterials = $rawMaterialsImported  + $rawMaterialsIndigenous;

				$creditorsHoldingPeriod["values"][$i]["key"] = $periodItem["key"];
				$creditorsHoldingPeriod["values"][$i]["value"] = $totalRawMaterials > 0 ? ($creditorsforPurchasesOthers  / $totalRawMaterials) * 365 : 0;

				$totalDebtVal = $aTNW["values"][$i]["value"] + $longTermDebt["values"][$i]["value"] + $shortTermDebt["values"][$i]["value"] ;

				$debtEquityRatio["values"][$i]["key"] = $periodItem["key"];
				$debtEquityRatio["values"][$i]["value"] = $nonCurrentAsetsExFA["values"][$i]["value"] > 0 ? $totalDebtVal / $nonCurrentAsetsExFA["values"][$i]["value"] : 0;

				$debtPBITDARatio["values"][$i]["key"] = $periodItem["key"];
				$debtPBITDARatio["values"][$i]["value"] = $pbdita["values"][$i]["value"] > 0 ? $totalDebtVal / $pbdita["values"][$i]["value"] : 0;

				$intCovRatio["values"][$i]["key"] = $periodItem["key"];
				$intCovRatio["values"][$i]["value"] = $pbditaMargin["values"][$i]["value"] > 0 ? $income["values"][$i]["value"] / $pbditaMargin["values"][$i]["value"] : 0;

				
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
						$pbditaMargin["values"][$i]["value"] = DisplayAmount($fsDataItem->pbdita_margin, $unit);
						
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
						$netProfitMargin["values"][$i]["value"] =  DisplayAmount($fsDataItem->net_profit_margin, $unit);

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

			
			
			
			$opBeforeWCChangesAmt = 0;
			
			for($i = 0 ; $i < count($periods); $i++)
			{
				$periodItemLastYear = null;
				$periodItem = $periods[$i];

				if(count($periods) > $i+1)
				{
					$periodItemLastYear = $periods[$i+1];
				}

				$netProfitBeforTax["values"][$i]["key"] = $periodItem["key"];
				$netProfitBeforTax["values"][$i]["value"] =  GetProfitandLossLineItem($profitloss, "Profit Before tax", $periodItem["key"]); 

				$opBeforeWCChangesAmt+=$netProfitBeforTax["values"][$i]["value"];

				$adjustmentFor["values"][$i]["key"] = $periodItem["key"];
				$adjustmentFor["values"][$i]["value"] =  "";

				$depriciation["values"][$i]["key"] = $periodItem["key"];
				$depriciation["values"][$i]["value"] =  GetProfitandLossLineItem($profitloss, "Depreciation", $periodItem["key"]); 

				$opBeforeWCChangesAmt+=$depriciation["values"][$i]["value"];

				$dividendIncome["values"][$i]["key"] = $periodItem["key"];
				$dividendIncome["values"][$i]["value"] =  GetProfitandLossLineItem($profitloss, "Dividend received", $periodItem["key"]); 

				$opBeforeWCChangesAmt+=$dividendIncome["values"][$i]["value"];

				$interestExp["values"][$i]["key"] = $periodItem["key"];
				$interestExp["values"][$i]["value"] =  GetProfitandLossLineItem($profitloss, "Total Interest", $periodItem["key"]); 

				$opBeforeWCChangesAmt+=$interestExp["values"][$i]["value"];

				$interestRecvd["values"][$i]["key"] = $periodItem["key"];
				$interestRecvd["values"][$i]["value"] =  GetProfitandLossLineItem($profitloss, "Interest Income", $periodItem["key"]); 

				$opBeforeWCChangesAmt+=$interestRecvd["values"][$i]["value"];

				$plOnSaleOfFAI["values"][$i]["key"] = $periodItem["key"];
				$plOnSaleOfFAI["values"][$i]["value"] =  GetProfitandLossLineItem($profitloss, "Profit on sale of assets/ investments", $periodItem["key"]); 

				$opBeforeWCChangesAmt+=$plOnSaleOfFAI["values"][$i]["value"];

				$forexGainLoss["values"][$i]["key"] = $periodItem["key"];
				$forexGainLoss["values"][$i]["value"] =  GetProfitandLossLineItem($profitloss, "Forex gains", $periodItem["key"]); 

				$opBeforeWCChangesAmt+=$forexGainLoss["values"][$i]["value"];

				$extraordinaryExpenses = GetProfitandLossLineItem($profitloss, "Extraordinary Expenses ", $periodItem["key"]); 
				$extraordinaryIncome = GetProfitandLossLineItem($profitloss, "Extraordinary Income", $periodItem["key"]); 
				

				$exIncomeExpenses["values"][$i]["key"] = $periodItem["key"];
				$exIncomeExpenses["values"][$i]["value"] =  $extraordinaryExpenses - $extraordinaryIncome;

				$opBeforeWCChangesAmt+=$exIncomeExpenses["values"][$i]["value"];

				$opBeforeWCChanges["values"][$i]["key"] = $periodItem["key"];
				$opBeforeWCChanges["values"][$i]["value"] =  $opBeforeWCChangesAmt;


				$totalCurrentAssets = GetBalanceSheetAnalysisLineItem($bsAnalysis, "Total current assets", $periodItem["key"]); 
				$totalCurrentAssetsLastYear = 0;
				if($periodItemLastYear != null)
				{
					$totalCurrentAssetsLastYear = GetBalanceSheetAnalysisLineItem($bsAnalysis, "Total current assets", $periodItemLastYear["key"]); 
				}

				$changeInCurrentAssets["values"][$i]["key"] = $periodItem["key"];
				$changeInCurrentAssets["values"][$i]["value"] =  $totalCurrentAssets - $totalCurrentAssetsLastYear;


				$totalCurrentLiabilities = GetBalanceSheetAnalysisLineItem($bsAnalysis, "Total Current Liabilities", $periodItem["key"]); 
				$totalCurrentLiabilitiesLastYear = 0;
				if($periodItemLastYear != null)
				{
					$totalCurrentLiabilitiesLastYear = GetBalanceSheetAnalysisLineItem($bsAnalysis, "Total Current Liabilities", $periodItemLastYear["key"]); 
				}

				$changeInCurrentLiabilities["values"][$i]["key"] = $periodItem["key"];
				$changeInCurrentLiabilities["values"][$i]["value"] =  $totalCurrentLiabilities - $totalCurrentLiabilitiesLastYear;
				
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

				$shortTermBorrowings["values"][$i]["key"] = $periodItem["key"];
				$shortTermBorrowings["values"][$i]["value"] =  $bankBorrowings + $instOfTermLoanDeb + $instOfTermLoanDebToOthers + $depositsDuePayable1Year;

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
				$finishedGoodsInclTradedGoods  = GetBalanceSheetLineItem($balanceSheet, "Finished Goods (incl Traded Goods)", $periodItem["key"]); 
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
				else if (trim($prevLabel) == "Other Spares consumed" && trim($item["label"]) == "i) Imported")
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
				else if (trim($prevLabel) == "Other Spares consumed" && trim($item["label"]) == "ii) Indigenous")
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
							$totalIncome["values"][$i]["value"] =  DisplayAmount($plDataItem->other_income, $unit);

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




?>