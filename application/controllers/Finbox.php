<?php

header('Access-Control-Allow-Origin: *'); //for allow any domain, insecure
header('Access-Control-Allow-Headers: *'); //for allow any headers, insecure
header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE'); //method allowed
header("HTTP/1.1 200 OK");

require 'vendor/autoload.php';


defined('BASEPATH') OR exit('No direct script access allowed');
use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;

//-----------------------------------------------------------------------------
//-----------------------------------------------------------------------------
//-----------------------------------------------------------------------------
class Finbox extends CI_Controller 
{
	public function __construct(){
		parent::__construct();
		$this->load->helper('json_output');  
        // $this->load->library('S3_upload');
        // $this->load->library('S3');
	}

public function  finboxapi(){

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

					$entity_id = $params['entityid'];
					$borrower_id = $params['borrower_id'];
					$linked_id = $params['linkid'];

                    try{

                           // This url is  get pdfs 

						$Finboxapi ="https://portal.finbox.in/bank-connect/v1/entity/";
                        $finboxendpoint ="/get_pdfs";  
                        $entityid=$entity_id;
                        $finbox_str = $Finboxapi.$entityid.$finboxendpoint;
				
						$curl = curl_init();
                        curl_setopt_array($curl, array(
                        CURLOPT_URL =>$finbox_str,
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_CUSTOMREQUEST => 'GET',
                        CURLOPT_HTTPHEADER => array(
                            // 'x-api-key: PimLidLKA52ihOAJDApmDsJjS2nU5eDivjt4WLcB', // live Api_key
                              'x-api-key: U1FGtOHm70i1bkXhzFFFymXA5PxXe5vGNfrET9sN',  // Local Api_key
                            //  'server-hash: df080304798e48b8a2a309d2d7ca4686',   // live APi_key
                             'server-hash: 182c41095f884c8f8355e9fe6829d2c2',   // Local APi_key
                             'content-type: application/x-www-form-urlencoded'
                        ),
                        ));
                     
                        $response = curl_exec($curl);
                        curl_close($curl);
					    $result = json_decode ($response, true);

						foreach($result['statements'] as $responsedata){

							$finboxpdf=[
                            "borrower_id"=>$borrower_id,
                            "entity_id"=> $entity_id,
							"statement_id"=>$responsedata['statement_id'],
							"bank_name"=> $responsedata['bank_name'],
							"pdf_password"=> $responsedata['pdf_password'],
							"pdf_url"=>   isset( $responsedata['pdf_url'] ) ?  $responsedata['pdf_url']:'',
							"account_id"=> $responsedata['account_id'],
							"source"=> $responsedata['source'],
                            "message"=> $responsedata['message'],
							];

                                 if($finboxpdf['pdf_url']!='' || $finboxpdf['pdf_url']!=null) {

                                $responseoutput =   file_get_contents($finboxpdf['pdf_url']) ;
                        
                                    //  $bucket = 'finnup';   // live bucketname
                                   $bucket = 'bucketinfo';   // Local bucketname
                                   

                                   
                                  $keyname = "FINNBID".$borrower_id."/".$finboxpdf['statement_id'];
                                   $Folder_name = 'FINBOXPDF/';
                                
                                   $Addkey_name = $Folder_name.$keyname.".pdf";
    
                                   $s3 = new S3Client([
                                    'version' => 'latest',
                                    'region'  => 'ap-south-1'
                                ]);
                                try {
                                    // Upload data.
                                    $result = $s3->putObject([
                                        'Bucket' => $bucket,
                                        'Key'    => $Addkey_name,
                                        'Body'   => $responseoutput ,
                                        'ACL'    => 'public-read'
                                    ]);
                                
                                    // Print the URL to the object.
                                    // echo $result['ObjectURL'] . PHP_EOL; 
                                    $url = $result['ObjectURL'];
                                   

                                // print_r("file upload successfully in s3 bucket in pdf");
                                }
                                catch (S3Exception $e) {
                                    echo $e->getMessage() . PHP_EOL;
                                }
                                     
                            }


                            $pdffinbox=[
                                'borrower_id'=>$finboxpdf['borrower_id'],
                                'entity_id'=>$finboxpdf['entity_id'],
                                'statement_id'=>$finboxpdf['statement_id'],
                                'bank_name'=>$finboxpdf['bank_name'],
                                'pdf_password'=>$finboxpdf['pdf_password'],
                                'pdf_url'=>$finboxpdf['pdf_url'],
                                'account_id'=>$finboxpdf['account_id'],
                                'source'=>$finboxpdf['source'],
                                'message'=>$finboxpdf['message'],
                                's3_url'=>  isset( $url) ? $url:null,               
                            ];

                            $sql="select t1.statement_id from fp_finbox_pdfs t1  where  t1.statement_id ='".$pdffinbox['statement_id']."' and t1.borrower_id = ".$pdffinbox['borrower_id'];

                            if(count($this->db->query($sql)->result())==0){

                                $this->db->insert("fp_finbox_pdfs", $pdffinbox);

                            }
                            else{ 
                                $this->db->where('statement_id',$pdffinbox['statement_id']);
                                $this->db->update('fp_finbox_pdfs',$pdffinbox);
                            }

							  
						 }
                      
                        //    This Url is xlsx  End point 

                         
                        $Finboxapi ="https://portal.finbox.in/bank-connect/v1/entity/";
                        $finboxendpoint ="/xlsx_report";  
                        $entityid=$entity_id;
                        $finbox_str = $Finboxapi.$entityid.$finboxendpoint;
				
						$curl = curl_init();
                        curl_setopt_array($curl, array(
                        CURLOPT_URL =>$finbox_str,
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_CUSTOMREQUEST => 'GET',
                        CURLOPT_HTTPHEADER => array(
                        //  'x-api-key: PimLidLKA52ihOAJDApmDsJjS2nU5eDivjt4WLcB', // live Api_key
                      'x-api-key: U1FGtOHm70i1bkXhzFFFymXA5PxXe5vGNfrET9sN',  // Local Api_key
                        //  'server-hash: df080304798e48b8a2a309d2d7ca4686',   // live APi_key
                         'server-hash: 182c41095f884c8f8355e9fe6829d2c2',   // Local APi_key
                         'content-type: application/x-www-form-urlencoded'
                        ),
                        ));
                     
                        $response = curl_exec($curl);
                        curl_close($curl);
					    $result = json_decode ($response, true);


						foreach($result['reports'] as $responsedata){
							$finboxpdf=[
                            "borrower_id"=>$borrower_id,
							"xlsxlink"=>   isset( $responsedata['link']) ? $responsedata['link']:" ",
							"account_id"=> $responsedata['account_id'],
							];

                            $responseoutput = file_get_contents($finboxpdf['xlsxlink']);
                        
                            // $bucket = 'finnup';     // live bucketname
                            $bucket = 'bucketinfo';       // local bucketname


                            $keyname = "FINNBID".$borrower_id."/".$finboxpdf['account_id'];
                            $Folder_name = 'FINBOXXLSX/';
                            $Addkey_name = $Folder_name.$keyname.".xlsx";

                            $s3 = new S3Client([
                             'version' => 'latest',
                             'region'  => 'ap-south-1'
                         ]);
                         try {
                             // Upload data.
                             $result = $s3->putObject([
                                 'Bucket' => $bucket,
                                 'Key'    => $Addkey_name,
                                 'Body'   => $responseoutput ,
                                 'ACL'    => 'public-read'
                             ]);
                         
                             // Print the URL to the object.
                             // echo $result['ObjectURL'] . PHP_EOL; 
                             $url = $result['ObjectURL'];
                            
                        //  print_r("file upload successfully in s3 bucket in xlsx ");
                         }
                         catch (S3Exception $e) {
                             echo $e->getMessage() . PHP_EOL;
                         }
                
                        $finboxxlsx=[
                            "borrower_id"=>$finboxpdf['borrower_id'],
                            "xlsxlink"=>$finboxpdf['xlsxlink'],
                            "account_id"=>$finboxpdf['account_id'],
                            "s3_url"=>$url,
                        ];
                        
                        $sql = "select t1.account_id from fp_finbox_xlsx_report t1  where t1.account_id = '". $finboxxlsx['account_id']."' and t1.borrower_id = ".$finboxxlsx['borrower_id'];

                        if(count($this->db->query($sql)->result())==0){
                            $this->db->insert('fp_finbox_xlsx_report',$finboxxlsx);

                        }
                        else{
                            $this->db->where('account_id',$finboxxlsx['account_id']);
					      $this->db->update('fp_finbox_xlsx_report', $finboxxlsx);


                        }

                        }
                        // This url is monthlyanalysis
                        $Finboxapi ="https://portal.finbox.in/bank-connect/v1/entity/";
                        $finboxendpoint ="/monthly_analysis_updated";  
                        $entityid=$entity_id;
                        $finbox_str = $Finboxapi.$entityid.$finboxendpoint;
				
						$curl = curl_init();
                        curl_setopt_array($curl, array(
                        CURLOPT_URL =>$finbox_str,
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_CUSTOMREQUEST => 'GET',
                        CURLOPT_HTTPHEADER => array(
                            // 'x-api-key: PimLidLKA52ihOAJDApmDsJjS2nU5eDivjt4WLcB', // live Api_key
                          'x-api-key: U1FGtOHm70i1bkXhzFFFymXA5PxXe5vGNfrET9sN',  // Local Api_key
                            //  'server-hash: df080304798e48b8a2a309d2d7ca4686',   // live APi_key
                          'server-hash: 182c41095f884c8f8355e9fe6829d2c2',   // Local APi_key
                             'content-type: application/x-www-form-urlencoded'
                        ),
                        ));
                        $response = curl_exec($curl);
                        curl_close($curl);
					    $result = json_decode ($response, true);

                        $totalaccount = sizeof($result['accounts']);   

                        foreach($result['accounts'] as $row){
                                $statement_id = $row['statements'];
                                $count = sizeof($statement_id);


                                $monthcount = $row['months'];
                                $monthfirstvalue = $row['months'][0];
                                $monthslastvalues = end($monthcount);
                             
                                foreach($statement_id as $statement){

                                    $data = [$statement];
                                }


                            $accounts_details =[
                                'borrower_id'=>$borrower_id,
                                'entity_id'=>$entity_id,
                                'linked_id'=>$linked_id,
                                'bank_name'=>$row['bank'],
                                'account_id'=>$row['account_id'],
                                'ifsc'=>$row['ifsc'],
                                // 'statement_id'=>$row['last_updated'],
                                'from_date_oldest'=>$monthfirstvalue,
                                'todate_latest'=> $monthslastvalues,
                                'type_of_accounts'=>$row['account_category'],
                                'account_number'=>$row['account_number'],                              
                                'totalaccounts'=>$totalaccount,                              
                            ];

                            $sql="select t1.account_id from fp_finbox_accounts_details t1 where t1.account_id='".$accounts_details['account_id']."' and t1.borrower_id=".$accounts_details['borrower_id'];
                            
                            if(count($this->db->query($sql)->result())==0){

                                $this->db->insert("fp_finbox_accounts_details", $accounts_details);
                            }
                            else{
                                $this->db->where("account_id",$accounts_details['account_id']);
                                $this->db->update("fp_finbox_accounts_details", $accounts_details);

                            }

                            // $this->db->insert("fp_finbox_accounts_details", $accounts_details)
                        }
                         $monthly_analysis= $result['monthly_analysis']['account_id'];

                         foreach($monthly_analysis as $ma)
                         {
                                $keys = array_keys($ma);
                                foreach($keys as $key) {
                                    // print_r($key); 
                                    $avg_bal = $ma[$key]['monthly_analysis']['avg_bal'];
                                    $amt_credit = $ma[$key]['monthly_analysis']['amt_credit'];
                                    $amt_debit = $ma[$key]['monthly_analysis']['amt_debit'];
                                    $cnt_outward_cheque_bounce_debit = $ma[$key]['monthly_analysis']['cnt_outward_cheque_bounce_debit'];
                                    $cnt_inward_cheque_bounce_credit = $ma[$key]['monthly_analysis']['cnt_inward_cheque_bounce_credit'];
                                    $avg_credit_transaction_size = $ma[$key]['monthly_analysis']['avg_credit_transaction_size'];
                                    $avg_debit_transaction_size = $ma[$key]['monthly_analysis']['avg_debit_transaction_size'];


                                     // This are array of value 
                                    $avg_bal_values = array_values($avg_bal);
                                    
                                    $amt_credit_values = array_values($amt_credit);
                                    $amt_debit_values = array_values($amt_debit);
                                    $cnt_outward_cheque_bounce_debit_values = array_values($cnt_outward_cheque_bounce_debit);
                                    $cnt_inward_cheque_bounce_credit_values = array_values($cnt_inward_cheque_bounce_credit);
                                    $avg_credit_transaction_size_values = array_values($avg_credit_transaction_size);
                                    $avg_debit_transaction_size_values = array_values($avg_debit_transaction_size);
                                    // end of array of values 

                                    // This are array of count 
                                    $avg_bal_count = sizeof($avg_bal_values);
                                    $amt_credit_count = sizeof($amt_credit_values);
                                    $amt_debit_count = sizeof($amt_debit_values);
                                    $cnt_outward_cheque_bounce_debit_count = sizeof($cnt_outward_cheque_bounce_debit_values);

                                    $cnt_inward_cheque_bounce_credit_count = sizeof($cnt_inward_cheque_bounce_credit_values);

                                    $avg_credit_transaction_size_count = sizeof($avg_credit_transaction_size_values);
                                    $avg_debit_transaction_size_count = sizeof($avg_debit_transaction_size_values);

                                // print_r($avg_bal_count); 
                                    // End of array of count 
                                     $avg_bal_total = 0;
                                    for ($i=0; $i<=$avg_bal_count-1; $i++){
                                        $avg_bal_total = $avg_bal_total + (int) $avg_bal_values[$i];
                                    };
                                    $avg_bal_totals = $avg_bal_total / $avg_bal_count;
                                    
                                    //  print_r($avg_bal_totals);  
                                    $amt_credit_total = 0;
                                    for ($i=0; $i<=$amt_credit_count-1; $i++){

                                        $amt_credit_total = $amt_credit_total+$amt_credit_values[$i];
                                    };
                                    $amt_credit_totals = $amt_credit_total / $amt_credit_count;
                                    
                                    //  print_r($amt_credit_totals);  
                                    $amt_debit_total = 0;
                                    for ($i=0; $i<=$amt_debit_count-1; $i++){
                                        $amt_debit_total = $amt_debit_total+$amt_debit_values[$i];
                                    };
                                    $amt_debit_totals = $amt_debit_total / $amt_debit_count;

                                    //  print_r($amt_debit_totals);  

                                    $cnt_outward_cheque_bounce_debit_total = 0;
                                    for ($i=0;$i<=$cnt_outward_cheque_bounce_debit_count-1; $i++){
                                        $cnt_outward_cheque_bounce_debit_total = $cnt_outward_cheque_bounce_debit_total+ $cnt_outward_cheque_bounce_debit_values[$i];  
                                    } ;
                                    $cnt_outward_cheque_bounce_debit_totals = $cnt_outward_cheque_bounce_debit_total/$cnt_outward_cheque_bounce_debit_count; 

                                    // print_r($cnt_outward_cheque_bounce_debit_totals);  
                                    $cnt_inward_cheque_bounce_credit_total = 0 ;
                                    for($i=0; $i<=$cnt_inward_cheque_bounce_credit_count-1; $i++){
                                        $cnt_inward_cheque_bounce_credit_total = $cnt_inward_cheque_bounce_credit_total+$cnt_inward_cheque_bounce_credit_values[$i];
                                    };

                                    $cnt_inward_cheque_bounce_credit_totals= $cnt_inward_cheque_bounce_credit_total/$cnt_inward_cheque_bounce_credit_count;
                                    //  print_r($cnt_outward_cheque_bounce_debit_totals); 
                                    $avg_credit_transaction_size_total = 0 ;

                                    for ($i=0; $i<=$avg_credit_transaction_size_count-1; $i++){
                                        $avg_credit_transaction_size_total = $avg_credit_transaction_size_total + $avg_credit_transaction_size_values[$i];
                                    };

                                    $avg_credit_transaction_size_totals = $avg_credit_transaction_size_total/$avg_credit_transaction_size_count;
                                    // print_r($avg_credit_transaction_size_totals); 
                                    $avg_debit_transaction_size_total = 0 ;

                                    for ($i=0; $i<=$avg_debit_transaction_size_count-1; $i++){
                                        $avg_debit_transaction_size_total = $avg_debit_transaction_size_total + $avg_debit_transaction_size_values[$i];
                                    };
                                    $avg_debit_transaction_size_totals = $avg_debit_transaction_size_total/$avg_debit_transaction_size_count;

                                    // print_r($avg_debit_transaction_size_totals);
                                    $monthlydata=[
                                        'borrower_id'=>$borrower_id,
                                        'account_id'=>$key,
                                        'average_eod_balance'=>$avg_bal_totals,
                                        'total_amount_of_credit_transactions'=>$amt_credit_totals,
                                        'total_amount_of_debit_transactions'=>$amt_debit_totals,
                                         'total_no_of_outward_cheque_bounce'=>$cnt_outward_cheque_bounce_debit_totals,
                                         'total_no_of_inward_cheque_bounce'=>$cnt_inward_cheque_bounce_credit_totals,
                                         'average_credit_transaction_size'=> $avg_credit_transaction_size_totals,
                                         'average_debit_transaction_size'=>$avg_debit_transaction_size_totals,
                                    ];

                                    $sql="select t1.account_id from fp_finbox_monthly_details t1 where t1.account_id='".$monthlydata['account_id']."' and t1.borrower_id=".$monthlydata['borrower_id'];
                            
                                   if(count($this->db->query($sql)->result())==0){

                                  $this->db->insert("fp_finbox_monthly_details", $monthlydata);

                                 }
                                 else{
                                     $this->db->where("account_id",$monthlydata['account_id']);
                                     $this->db->update("fp_finbox_monthly_details",$monthlydata);
      
                                 }

                                    //  $this->db->insert('fp_finbox_monthly_details', $monthlydata);   
                                }
                        }
                        json_output(200, array('status' => 200 , 'message'=> 'success'));  	
					}
					catch(Exception $e)
					{
						echo 'Caught exception: ',  $e->getMessage(), "\n";
					}
				}
			}
} //  end of finbox 
}
