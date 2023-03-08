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
					{
						$params = json_decode(file_get_contents('php://input'), TRUE);


            // =$params['director_id'];
            if(!isset($params['director_id'])){

              $this->db->insert('fp_director_details',$params['data2']);
              $params['director_id'] = $this->db->insert_id();
            }

            
            // echo $params['director_id'];

            // die;
						// $param = json_decode(file_get_contents('php://input'), FALSE);


            // $obj = json_decode(json_encode($param->data), true);

            // print_r($obj);

                 
                             $data = [
                                 "reference_id"=>"8623-3245-0000-0005",
                                 "consent"=>true,
                                 "consent_purpose"=>"Sara testing",
                                 "name"=> $params ['data']['Name'],
                                 "mobile"=> $params['data']['DirectorPhone'],
                                 "PAN"=> $params['data']['Pan'],
                                 "address_type"=>"H",
                                 "inquiry_purpose"=>"PL",
                                 "document_type"=>"PAN",
                                 "document_id"=> $params['data']['Pan']
                             ];
                              
                            if($params ['data']['Name'] && $params['data']['DirectorPhone'] && $params['data']['Pan'] &&  $params['director_id'] ){

                          $cibilAPI ='https://in.decentro.tech/v2/financial_services/credit_bureau/credit_report';
                           $name_str =$cibilAPI;
                           $urlc=$name_str;
                           $ch = curl_init();
                           curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                           curl_setopt($ch, CURLOPT_URL, $urlc);
                           curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                           curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                           curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
                           curl_setopt($ch, CURLOPT_HTTPHEADER, [
                            'client_id: FinnUp_prod',
                            'client_secret: 7o4nJGMIyMgvzXYFAaU2Vt0mvd28iTWj',
                            'module_secret: gqYEn1AoaWGBmnS80DBfgvKrJKYfHFxv',
                            'provider_secret: H0iPWht6dpYgRazIsXToqez8HBhhPmQ3',
                            'accept: application/json',
                            'content-type: application/json'
                         ]);            
                         $season_data = curl_exec($ch);
                         curl_close($ch);
                         $result = json_decode($season_data, true);
                         $responseData= $result;

                         print_r($responseData);  

                    
                         //  $this->db->insert('fp_cibil_details',$responeData);

                         $responseoutput = $responseData['data']['cCRResponse']['cIRReortDataLst']['0']['cIRReportData'];
                        //  $responseoutput = $obj['data']['cCRResponse']['cIRReportDataLst']['0']['cIRReportData'];     

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
                          };
                                        // cibil score update in director_details  
                           
                          $this->db-where(array('id'=>$params['director_id']));
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
                            // 'director_id'=> $params['director_id'],
                            'fullname'=> $responseoutput['iDAndContactInfo']['personalInfo']['name']['fullName'],
                            'dob'=>$responseoutput['iDAndContactInfo']['personalInfo']['dateOfBirth'],
                            'identificationtype'=> "PAN",
                            'identificationnumber'=>$responseoutput['iDAndContactInfo']['identityInfo']['pANId']['0']['idNumber'],
                            'mobilenumber'=> $responseoutput['iDAndContactInfo']['phoneInfo']['1']['number'],
                            'equifax'=> $responseoutput['scoreDetails']['0']['value'],
                            'cibilscore'=> $CibilValue ,
                            'totalaccounts'=> $totalaccounts,  
                            'overdueaccounts'=> $totalaccounts,
                            'currentamount'=> $total,
                            'sanctionedamount'=> $totalsanctionamount ,
                            'overdueamount'=> $pastDueAmount,
                         ];
                          $this->db->insert('fp_director_cibilsummary',$cibilsummary_details);


                          $responseoutputs = $responseoutput['retailAccountDetails'];

                          // cibilaccountdetails   
                          foreach($responseoutputs as $cibilaccdetails){
                            $cibilaccountdetails=[
                                // 'director_id'=> $params['director_id'],
                                'account_type'=> $cibilaccdetails['accountType'],
                                'status'=>$cibilaccdetails['accountStatus'] ,
                                'currentbalance'=>$cibilaccdetails['balance'] ,
                                'membername'=> $cibilaccdetails['institution'],
                                'account_number'=>$cibilaccdetails['accountNumber'] ,
                                'ownership'=> $cibilaccdetails['ownershipType'] ,
                                'opened_date'=> $cibilaccdetails['dateOpened'] ,
                                'reported_date'=> $cibilaccdetails['dateReported'] ,
                                'lastpayment_date'=> $cibilaccdetails['dateReported'] ,
                              ];
                              $this->db->insert('fp_director_cibilaccountdetails',$cibilaccountdetails);
                              $cibilaccountdetail_id = $this->db->insert_id();
                                //  cibilpayments details 
                                   foreach ($cibilaccdetails['history48Months'] as $cibilpayments){

                                    $cibilpayments=[
                                       'director_id'=>$params['director_id'],
                                      'cibilaccountdetail_id'=>$cibilaccountdetail_id,
                                      'payment_date'=> $cibilpayments['key'],
                                      'payment_received'=>$cibilpayments['paymentStatus'],
                                      'account_number'=> $cibilaccdetails['accountNumber'],
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
                    }

                    else{
                      json_output(400, array('status' => 400,'message' => 'Bad request.'));
                    }

                }
}

} //----------------------- END OF CIBIL CLASS -------------------------


?>
