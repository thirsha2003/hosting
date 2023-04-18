<?php

header('Access-Control-Allow-Origin: *'); //for allow any domain, insecure
header('Access-Control-Allow-Headers: *'); //for allow any headers, insecure
header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE'); //method allowed
header("HTTP/1.1 200 OK");

defined('BASEPATH') OR exit('No direct script access allowed');
//-----------------------------------------------------------------------------
//-----------------------------------------------------------------------------
//-----------------------------------------------------------------------------
class CIBIL extends CI_Controller 
{
    public function __construct(){
		parent::__construct();
		$this->load->helper('json_output');
	}

    public function  cibilscore(){
        $response['status'] = 200;
		$respStatus = $response['status'];
		$method = $_SERVER['REQUEST_METHOD'];
		if($method != 'POST'){
			json_output(400, array('status' => 400,'message' => 'Bad request.')); 
		}
     else
		{ 
            if($response['status'] == 200)  
            // if(false)

					{
						$params = json_decode(file_get_contents('php://input'), TRUE);
            if(!isset($params['director_id'])){

              $this->db->insert('fp_director_details',$params['data2']);
              $params['director_id'] = $this->db->insert_id();

              $director =   $params['director_id'];
              

              // json_output(200, array('status' => 200 , 'message'=> 'success','director_id'=> $director)); 
            };
                             $data = [
                              "reference_id"=> (String)$params['cibilreference_id'],
                              "consent"=> true,
                              "consent_purpose"=> "FINNUP For Personal Testing",
                               "name"=> (String) $params ['data']['Name'],
                              "date_of_birth"=> "",
                              "address_type"=> "H",
                              "address"=> "",
                              "pincode"=> "",
                              "mobile"=> (String) $params['data']['DirectorPhone'],
                              "inquiry_purpose"=> "PL",
                              "document_type"=> "PAN",
                              "document_id"=> " "
                             ];
                             $datas = json_encode($data);
                            // if(  isset($params ['data']['Name']) &&  isset($params['data']['DirectorPhone']) &&   isset ($params['director_id'] ) )
                            if(true)
                          { 
                        $curl = curl_init();
                        curl_setopt_array($curl, array(
                          CURLOPT_URL => 'https://in.decentro.tech/v2/financial_services/credit_bureau/credit_report',
                          CURLOPT_RETURNTRANSFER => true,
                          CURLOPT_ENCODING => '',
                          CURLOPT_MAXREDIRS => 10,
                          CURLOPT_TIMEOUT => 0,
                          CURLOPT_FOLLOWLOCATION => true,
                          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                          CURLOPT_CUSTOMREQUEST => 'POST',
                          CURLOPT_POSTFIELDS =>strval($datas),
                          CURLOPT_HTTPHEADER => array(
                            'client_id: FinnUp_prod',
                            'client_secret: 7o4nJGMIyMgvzXYFAaU2Vt0mvd28iTWj',
                            'module_secret: gqYEn1AoaWGBmnS80DBfgvKrJKYfHFxv',
                            'provider_secret: H0iPWht6dpYgRazIsXToqez8HBhhPmQ3',
                            'accept: application/json',
                            'content-type: application/json'
                          ),
                        ));
                         $season_data = curl_exec($curl);
                         curl_close($curl);
                         $result = json_decode($season_data, true);
                         $responseData= $result;
                         
                        //  print_r($responseData);   
                         

                         

                         $responseoutput = $responseData['data']['cCRResponse']['cIRReportDataLst']['0']['cIRReportData'];
                       
                          $CibilValue='';
                          switch (true) {
                            case ($responseoutput['scoreDetails']['0']['value'] >= 300 && $responseoutput['scoreDetails']['0']['value'] <= 400):
                              $CibilValue=400; 
                              break;
                            case ($responseoutput['scoreDetails']['0']['value'] >= 400 && $responseoutput['scoreDetails']['0']['value'] <= 449):
                              $CibilValue=420; 
                              break;  
                            case ($responseoutput['scoreDetails']['0']['value'] >= 450 && $responseoutput['scoreDetails']['0']['value'] <= 499):
                                $CibilValue=470; 
                                break;        
                            case ($responseoutput['scoreDetails']['0']['value'] >= 500 && $responseoutput['scoreDetails']['0']['value'] <= 549):
                               $CibilValue=520; 
                               break;  
                            case ($responseoutput['scoreDetails']['0']['value'] >= 550 && $responseoutput['scoreDetails']['0']['value'] <= 599):
                                 $CibilValue=570; 
                                 break;  
                            case ($responseoutput['scoreDetails']['0']['value'] >= 600 && $responseoutput['scoreDetails']['0']['value'] <= 649):
                                   $CibilValue=620; 
                                   break;  
                            case ($responseoutput['scoreDetails']['0']['value'] >= 650 && $responseoutput['scoreDetails']['0']['value'] <= 699):
                                     $CibilValue=670; 
                                     break;  
                            case ($responseoutput['scoreDetails']['0']['value'] >= 700 && $responseoutput['scoreDetails']['0']['value'] <= 749):
                               $CibilValue=720; 
                               break;  
                            case ($responseoutput['scoreDetails']['0']['value'] >= 750 && $responseoutput['scoreDetails']['0']['value'] <= 799):
                                 $CibilValue=770; 
                                 break;  
                            case ($responseoutput['scoreDetails']['0']['value'] >= 800 && $responseoutput['scoreDetails']['0']['value'] <=849):
                                  $CibilValue=820; 
                                  break;  
                            case ($responseoutput['scoreDetails']['0']['value'] >= 850 && $responseoutput['scoreDetails']['0']['value'] <=899):
                                    $CibilValue=870; 
                                    break; 
                          };  // end of switch 

                          // cibil score update in director_details  
                           
                          $this->db->where(array('id'=>$params['director_id']));
                          $fp_director = $this->db->update('fp_director_details',array('cibil_score'=>$CibilValue));



                         
                          $totalaccounts= sizeof($responseoutput['retailAccountDetails']);
                           $total = 0;
                          for ($i = 0; $i < $totalaccounts; $i++) {
                            if(isset($responseoutput['retailAccountDetails'][$i]['balance'])){
                              $total = $total + (int)$responseoutput['retailAccountDetails'][$i]['balance'];
                             
                            } 
                            };   //  total of balance 
                            
                           $pastDueAmount=0;
                           for ( $j = 0; $j< $totalaccounts; $j++){
                              if(isset($responseoutput['retailAccountDetails'][$j]['pastDueAmount'])){
                                $pastDueAmount =  $pastDueAmount + (int)$responseoutput['retailAccountDetails'][$j]['pastDueAmount'];
                              }
                           };  // pastdueamount
                          
                           $creditLimit=0;
                           for ( $i = 0; $i< $totalaccounts; $i++){
                            if(isset($responseoutput['retailAccountDetails'][$i]['creditLimit'])){
                              $creditLimit = $creditLimit + (int)$responseoutput['retailAccountDetails'][$i]['creditLimit'];
                            }
                           }; // creditLimit 
                           
                           $sanctionamount=0;
                           for($i = 0; $i< $totalaccounts; $i++){
                            if(isset($responseoutput['retailAccountDetails'][$i]['sanctionAmount'])){
                              $sanctionamount =$sanctionamount + (int)$responseoutput['retailAccountDetails'][$i]['sanctionAmount'];
                            }
                           }; // sanctionAmount
                           $totalsanctionamount = $creditLimit + $sanctionamount ;

                            

                         $cibilsummary_details=[
                            'reference_id'=> $params['cibilreference_id'],
                            'director_id'=> $params['director_id'],
                            'fullname'=> $responseoutput['iDAndContactInfo']['personalInfo']['name']['fullName'],
                            'dob'=>$responseoutput['iDAndContactInfo']['personalInfo']['dateOfBirth'],
                            'identificationtype'=> "PAN",
                            'identificationnumber'=>$responseoutput['iDAndContactInfo']['identityInfo']['pANId']['0']['idNumber'],
                            'mobilenumber'=> $responseoutput['iDAndContactInfo']['phoneInfo']['0']['number'],
                            'equifax'=> $responseoutput['scoreDetails']['0']['value'],
                            'cibilscore'=> $CibilValue,
                            'totalaccounts'=>  $responseoutput['retailAccountsSummary']['noOfAccounts'],
                            'overdueaccounts'=> $totalaccounts,

                            'currentamount'=> $responseoutput['retailAccountsSummary'] ['totalBalanceAmount'],
                            'sanctionedamount'=> $responseoutput['retailAccountsSummary']['totalSanctionAmount'],
                            'overdueamount'=> $pastDueAmount,
                            'totalcurrentaccount'=> $responseoutput['retailAccountsSummary']['noOfActiveAccounts'],
                            'mostseverestatuswithin24months'=> $responseoutput['retailAccountsSummary']['mostSevereStatusWithIn24Months'],
                             'singlehighestcredit'=> $responseoutput['retailAccountsSummary'] ['singleHighestCredit'],
                             'singlehighestsanctionamount'=> $responseoutput['retailAccountsSummary'] ['singleHighestSanctionAmount'],
                             'totalhighcredit'=> $responseoutput['retailAccountsSummary'] ['totalHighCredit'],
                             'averageopenbalance'=> $responseoutput['retailAccountsSummary'] ['averageOpenBalance'],
                             'singlehighestbalance'=> $responseoutput['retailAccountsSummary'] ['singleHighestBalance'],
                             'noofpastdueaccounts'=> $responseoutput['retailAccountsSummary'] ['noOfPastDueAccounts'],
                             'noofzerobalanceaccounts'=> $responseoutput['retailAccountsSummary'] ['noOfZeroBalanceAccounts'],
                             'recentaccount'=> $responseoutput['retailAccountsSummary'] ['recentAccount'],
                             'oldestaccount'=> $responseoutput['retailAccountsSummary'] ['oldestAccount'],
                             'totalcreditlimit'=> $responseoutput['retailAccountsSummary'] ['totalCreditLimit'],
                             'totalmonthlypaymentamount'=> $responseoutput['retailAccountsSummary'] ['totalMonthlyPaymentAmount'], 
                         ];
                          $this->db->insert('fp_director_cibilsummary',$cibilsummary_details);

                         
                            // This line  code json insert into table 

                            // $responsejson=array(); 

                            $responsejson['responsejson'] = json_encode($responseData,true);  

                            $this->db->where('director_id', $params['director_id']);
                            $this->db->update('fp_director_cibilsummary',$responsejson);    

                          // End of  json 



                          $responseoutputs = $responseoutput['retailAccountDetails'];


                          // cibilaccountdetails   
                          foreach($responseoutputs as $cibilaccdetails){
                           
                            $lastpayment_date='';
                             if   (isset($cibilaccdetails['lastPaymentDate'])){
                              $lastpayment_date=$cibilaccdetails['lastPaymentDate'];  
                             }
                             elseif(isset($cibilaccdetails['dateClosed'])){
                              $lastpayment_date=$cibilaccdetails['dateClosed']; 
                             }

                             $termfrequency='';
                             if (isset($cibilaccdetails['termFrequency'])
                             ){
                               $termfrequency=$cibilaccdetails['termFrequency'];
                             }
                             else{
                              $termfrequency="Others";

                             }


                            $cibilaccountdetails=[
                                'director_id'=> $params['director_id'],
                                'account_type'=> $cibilaccdetails['accountType'],
                                'status'=>$cibilaccdetails['accountStatus'] ,
                                'currentbalance'=>$cibilaccdetails['balance'] ,
                                'membername'=> $cibilaccdetails['institution'],
                                'account_number'=>$cibilaccdetails['accountNumber'] ,
                                'ownership'=> $cibilaccdetails['ownershipType'] ,
                                'opened_date'=> $cibilaccdetails['dateOpened'] ,
                                'reported_date'=> $cibilaccdetails['dateReported'] ,

                                'account_open_status'=> $cibilaccdetails['open'],
                                'lastpayment_date'=> $lastpayment_date,
                                'termfrequency'=> $termfrequency,
                              ];
                              $this->db->insert('fp_director_cibilaccountdetails',$cibilaccountdetails);
                              $cibilaccountdetail_id = $this->db->insert_id();
                                     
                              $sql="select t1.account_number 
                              from fp_director_cibilaccountdetails t1 where t1.id=".$cibilaccountdetail_id;
                              $accountnumber = $this->db->query($sql)->row();



                                //  cibilpayments details 
                                   foreach ($cibilaccdetails['history48Months'] as $cibilpayments){

                                    $cibilpayments=[
                                       'director_id'=>$params['director_id'],
                                      'cibilaccountdetail_id'=>$cibilaccountdetail_id,
                                      'payment_date'=> $cibilpayments['key'],
                                      'payment_received'=>$cibilpayments['paymentStatus'],
                                      'account_number'=> $accountnumber->account_number,
                                    ];
                                    $this->db->insert('fp_director_cibilpayments',$cibilpayments);
                                   };
                          } 
                    

                    $fp_director_details = $this->db->get_where('fp_director_details', array('id' => $params['director_id']));
                          

                         json_output(200, array('status' => 200 , 'message'=> 'success','data'=>$fp_director_details));
                        }
                        else{
                          json_output(200, array('status' => 200 , 'message'=> 'Invalid Information'));
                        }


                        $fp_director_details = $this->db->get_where('fp_director_details', array('id' => $params['director_id']));
                          

                        json_output(200, array('status' => 200 , 'message'=> 'success','data'=>$fp_director_details));

          }
        

          if ($response['status'] == 200){

            json_output(200, array('status' =>200,'message' => 'Testing its  Working or wrong  .'));
          } 




                    else{
                      json_output(400, array('status' => 400,'message' => 'Bad request.'));
                    }

    }
}

} //----------------------- END OF CIBIL CLASS -------------------------


?>
