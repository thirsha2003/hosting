<?php

header('Access-Control-Allow-Origin: *'); //for allow any domain, insecure
header('Access-Control-Allow-Headers: *'); //for allow any headers, insecure
header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE'); //method allowed
header("HTTP/1.1 200 OK");

require 'vendor/autoload.php';


defined('BASEPATH') or exit('No direct script access allowed');
include APPPATH . 'ThirdParty/sendgrid-php/sendgrid-php.php';
include APPPATH . 'ThirdParty/mTalkz.php';
include APPPATH . 'libraries/Femail.php';
include APPPATH . 'libraries/JsonuploadtoS3.php'; 
use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


//-----------------------------------------------------------------------------
//-----------------------------------------------------------------------------
//-----------------------------------------------------------------------------
class Finbox extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('json_output');
        // $this->load->library('S3_upload');
        // $this->load->library('S3');
    }  // construct
    public function finnup_finboxwebhook()
    {

        $response['status'] = 200;
        $respStatus = $response['status'];
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            if ($response['status'] == 200) {
                $params = json_decode(file_get_contents('php://input'), TRUE);


                $entity_id = isset($params['entity_id']) ? $params['entity_id'] : " ";
                $statement_id = isset($params['statement_id']) ? $params['statement_id'] : " ";
                $linked_id = isset($params['link_id']) ? $params['link_id'] : " ";
                $progress = isset($params['progress']) ? $params['progress'] : " ";
                $reason = isset($params['reason']) ? $params['reason'] : " ";


                $logs=array("entity_id"=> $entity_id,"statement_id"=>$statement_id,"link_id"=> $linked_id,"progress"=> $progress,"reason"=>$reason);
                $this->db->insert('fp_finbox_log',$logs);
                if($progress="completed"){
                    $this->finboxapi_pdfxlsx_ma($entity_id, $statement_id, $linked_id, $progress, $reason);
                    json_output(200, array('status' => 200,'message' => 'Success!')); 

                }

            }

            //json_output(200, array('status' => 200,'message' => 'Success!')); 

        }

    } // end of finnup_finboxwebhook()

    public function finboxapi()
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

                    $entity_id = $params['entityid'];
                    // $borrower_id = $params['borrower_id'];  
                    $linked_id = $params['linkid'];


                    $fp_borrower_details = array(
                        'finbox_entity_id' => $entity_id,
                        // 'finbox_link_id' => $linked_id,
                        'finbox_processing'=>"Before",
                    );

                    $this->db->where('finbox_link_id', $linked_id);
                    $this->db->update('fp_borrower_user_details', $fp_borrower_details);


                    json_output(200, array('status' => 200, 'message' => 'successfully Added'));
                } catch (Exception $e) {
                    json_output(200, array('status' => 401, 'message' => $e->getMessage()));
                }
            } else {
                json_output(200, array('status' => 401, 'message' => "Auth Failed "));
            }

        }

    } // finboxapi_linkidupdate
    private function getborrowerid($entity_id, $linked_id)
    {
        $sql = "select  user_id from fp_borrower_user_details where finbox_link_id = '" . $linked_id . "' and finbox_entity_id= '" . $entity_id . "'";
        $result = $this->db->query($sql)->result();
          
        return $result[0]->user_id;

    } // getborrowerid End   

    public function finboxapi_pdfxlsx_ma($entity_id, $statement_id, $linked_id, $progress, $reason)
    {
        $aws= new \App\Libraries\JsonuploadtoS3;

        $borrower_id = $this->getborrowerid($entity_id, $linked_id);

        $complete = "UPDATE fp_borrower_user_details
					SET finbox_processing ='After'  WHERE  fp_borrower_user_details.user_id=".$borrower_id;
		$percentages = $this->db->query($complete);

          if ($borrower_id==null){
              return json_output(200,array('status' => 401, 'data'=>$entity_id,'message' => "Something went wrong"));
          }

        try {
            // This url is  get pdfs 

            $Finboxapi = "https://portal.finbox.in/bank-connect/v1/entity/";
            $finboxendpoint = "/get_pdfs";
            $entityid = $entity_id; 
            $finbox_str = $Finboxapi . $entityid . $finboxendpoint;
            $curl = curl_init();

           

            curl_setopt_array($curl, array(
                CURLOPT_URL => $finbox_str,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    'x-api-key: PimLidLKA52ihOAJDApmDsJjS2nU5eDivjt4WLcB',
                    'server-hash: df080304798e48b8a2a309d2d7ca4686',
                    'content-type: application/x-www-form-urlencoded'
                ),
            )
            );

            $response = curl_exec($curl);
            curl_close($curl);
            $result = json_decode($response, true);

                        //  AWS CODE START 

                        $projson= json_encode($response);
                        $foldername="FINBOXPDF/";
                        $aws->aws_s3bucket($borrower_id,$foldername,$projson);

                        // AWS END CODE 

            
            foreach ($result['statements'] as $responsedata) {
                $finboxpdf = [
                    "borrower_id" => isset($borrower_id) ? $borrower_id : 0,
                    "entity_id" => isset($entity_id) ? $entity_id : "Not Avaiable",
                    "statement_id" => isset($responsedata['statement_id']) ? $responsedata['statement_id'] : "Not Avaiable",
                    "bank_name" => isset($responsedata['bank_name']) ? $responsedata['bank_name'] : "Not Avaiable",
                    "pdf_password" => isset($responsedata['pdf_password']) ? $responsedata['pdf_password'] : "Not Avaiable",
                    "pdf_url" => isset($responsedata['pdf_url']) ? $responsedata['pdf_url'] :
                    "Not Avaiable",
                    "account_id" => isset($responsedata['account_id']) ? $responsedata['account_id'] : "Not Avaiable",
                    "source" => isset($responsedata['source']) ? $responsedata['source'] : "Not Avaiable",
                    "message" => isset($responsedata['message']) ? $responsedata['message'] : "Not Avaiable",
                ];

                

                $responseoutput = file_get_contents($finboxpdf['pdf_url']);



                $bucket = 'finnup';
                $keyname = "FINNBID" . $borrower_id . "/" . $finboxpdf['statement_id'];
                $Folder_name = 'finboxpdf/';
                $Addkey_name = $Folder_name . $keyname . ".pdf";

                $credentials = new Aws\Credentials\Credentials('AKIAWJIM4CKQMIAM5R5L', 'GcL436Q16pUChV4ohqqna0QE9arhpGw8Q5sRorBV');

                $s3 = new S3Client([
                    'region' => 'ap-south-1',
                    'version' => 'latest',
                    'credentials' => $credentials
                ]);
                try {
                    // Upload data.
                    $result = $s3->putObject([
                        'Bucket' => $bucket,
                        'Key' => $Addkey_name,
                        'Body' => $responseoutput,
                        'ACL' => 'public-read'
                    ]);
                    $url = $result['ObjectURL'];
                } catch (S3Exception $e) {
                    echo $e->getMessage() . PHP_EOL;
                }


                $pdffinbox = [
                    'borrower_id' => $finboxpdf['borrower_id'],
                    'entity_id' => $finboxpdf['entity_id'],
                    'statement_id' => $finboxpdf['statement_id'],
                    'bank_name' => $finboxpdf['bank_name'],
                    'pdf_password' => $finboxpdf['pdf_password'],
                    'pdf_url' => $finboxpdf['pdf_url'],
                    'account_id' => $finboxpdf['account_id'],
                    'source' => $finboxpdf['source'],
                    'message' => isset($finboxpdf['message']) ? $finboxpdf['message'] : "Not Avaiable",
                    's3_url' => isset($url) ? $url : null,
                ];

                $sql = "select t1.statement_id from fp_finbox_pdfs t1  where  t1.statement_id ='" . $pdffinbox['statement_id'] . "' and t1.borrower_id = " . $pdffinbox['borrower_id'];

                if (count($this->db->query($sql)->result()) == 0) {
                    $this->db->insert("fp_finbox_pdfs", $pdffinbox);
                } else {
                    $this->db->where('statement_id', $pdffinbox['statement_id']);
                    $this->db->update('fp_finbox_pdfs', $pdffinbox);
                }



                // documnet upload in db 
                $documenturl=$pdffinbox['s3_url'];
                $strexplode = explode(".com/", $documenturl);
                  $file_name = $strexplode[1];
                  
                  $doc_type = $finboxpdf['bank_name'].$finboxpdf['statement_id'];

                  $pdf_document = array("borrower_id"=>$finboxpdf['borrower_id'],"doc_type"=>$doc_type,"file_name"=>$file_name);


                  $sql = "select doc_type from fp_borrower_docs   where  doc_type ='" .  $doc_type . "' and borrower_id = " . $pdffinbox['borrower_id'];
                 
                  
                if(count($this->db->query($sql)->result()) == 0){

                    $this->db->insert("fp_borrower_docs",$pdf_document);

                }
                else{

                    $this->db->where('doc_type', $doc_type);
                    $this->db->update('fp_borrower_docs', $pdf_document);

                }


            }

            //    This Url is xlsx  End point 




            $Finboxapi = "https://portal.finbox.in/bank-connect/v1/entity/";
            $finboxendpoint = "/xlsx_report";
            $entityid = $entity_id;
            $finbox_str = $Finboxapi . $entityid . $finboxendpoint;

            $curl = curl_init();


            // print_r($finbox_str);

            curl_setopt_array($curl, array(
                CURLOPT_URL => $finbox_str,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    'x-api-key: PimLidLKA52ihOAJDApmDsJjS2nU5eDivjt4WLcB',
                    'server-hash: df080304798e48b8a2a309d2d7ca4686',
                    'content-type: application/x-www-form-urlencoded'
                ),
            )
            );

            $response = curl_exec($curl);
            curl_close($curl);
            $result = json_decode($response, true);


             //  AWS CODE START 

             $projson = json_encode($response);
             $foldername ="FINBOXXLSX/";
             $aws->aws_s3bucket($borrower_id,$foldername,$projson);

             // AWS END CODE 

            // print_r($result);

            foreach ($result['reports'] as $responsedata) {
                $finboxpdf = [
                    "borrower_id" => $borrower_id,
                    "xlsxlink" => isset($responsedata['link']) ? $responsedata['link'] : " ",
                    "account_id" => $responsedata['account_id'] ? $responsedata['account_id'] : " ",
                ];

                // print_r($finboxpdf['xlsxlink']);

                if (isset($responsedata['link']) != "" && $responsedata['account_id'] != "") {
                    $responseoutput = file_get_contents($finboxpdf['xlsxlink']);


                    $bucket = 'finnup';
                    $keyname = "FINNBID" . $borrower_id . "/" . $finboxpdf['account_id'];
                    $Folder_name = 'finboxxlsx/';
                    $Addkey_name = $Folder_name . $keyname . ".xlsx";

                    // print_r($Addkey_name);

                    $credentials = new Aws\Credentials\Credentials('AKIAWJIM4CKQMIAM5R5L', 'GcL436Q16pUChV4ohqqna0QE9arhpGw8Q5sRorBV');

                    $s3 = new S3Client([
                        'version' => 'latest',
                        'region' => 'ap-south-1',
                        'credentials' => $credentials

                    ]);
                    try {
                        // Upload data.
                        $result = $s3->putObject([
                            'Bucket' => $bucket,
                            'Key' => $Addkey_name,
                            'Body' => $responseoutput,
                            'ACL' => 'public-read'
                        ]);

                        // Print the URL to the object.
                        // echo $result['ObjectURL'] . PHP_EOL; 
                        $url = $result['ObjectURL'];

                        // print_r($url);

                        //  print_r("file upload successfully in s3 bucket in xlsx ");
                    } catch (S3Exception $e) {
                        echo $e->getMessage() . PHP_EOL;
                    }

                }

                $finboxxlsx = [
                    "borrower_id" => $finboxpdf['borrower_id'],
                    "xlsxlink" => $finboxpdf['xlsxlink'],
                    "account_id" => $finboxpdf['account_id'],
                    "s3_url" => isset($url) ? $url : " ",
                ];

                $sql = "select t1.account_id from fp_finbox_xlsx_report t1  where t1.account_id = '" . $finboxxlsx['account_id'] . "' and t1.borrower_id = " . $finboxxlsx['borrower_id'];

                if (count($this->db->query($sql)->result()) == 0) {

                    $this->db->insert('fp_finbox_xlsx_report', $finboxxlsx);

                    // print_r("Data insert Into the Table");

                } else {
                    $this->db->where('account_id', $finboxxlsx['account_id']);
                    $this->db->update('fp_finbox_xlsx_report', $finboxxlsx);

                }
                 
                 // documnet upload in db 
                 $documenturl=$finboxxlsx['s3_url'];
                 $strexplode = explode(".com/", $documenturl);
                   $file_name = $strexplode[1];
                   
                   $doc_type = "FINBOXXLSX".$finboxxlsx["account_id"];
 
                   $pdf_document = array("borrower_id"=>$finboxxlsx['borrower_id'],"doc_type"=>$doc_type,"file_name"=>$file_name);
 
 
                   $sql = "select doc_type from fp_borrower_docs   where  doc_type ='".$doc_type ."' and borrower_id = " . $pdffinbox['borrower_id'];
                  
                   
                 if(count($this->db->query($sql)->result()) == 0){
 
                     $this->db->insert("fp_borrower_docs",$pdf_document);
 
                 }
                 else{
 
                     $this->db->where('doc_type', $doc_type);
                     $this->db->update('fp_borrower_docs', $pdf_document);
 
                 }






            }

            // This url is monthlyanalysis

            $Finboxapi = "https://portal.finbox.in/bank-connect/v1/entity/";
            $finboxendpoint = "/monthly_analysis_updated";
            $entityid = $entity_id;
            $finbox_str = $Finboxapi . $entityid . $finboxendpoint;

            // print_r($finbox_str);


            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => $finbox_str,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    'x-api-key: PimLidLKA52ihOAJDApmDsJjS2nU5eDivjt4WLcB',
                    'server-hash: df080304798e48b8a2a309d2d7ca4686',
                    'content-type: application/x-www-form-urlencoded'
                ),
            )
            );

            $response = curl_exec($curl);

            curl_close($curl);

            $result = json_decode($response, true);

            //  AWS CODE START 

            $projson = json_encode($response);
            $foldername ="FINBOXMONTHLY/";
            $aws->aws_s3bucket($borrower_id,$foldername,$projson);

            // AWS END CODE 




            // print_r($result);


            $totalaccount = sizeof($result['accounts']);

            foreach ($result['accounts'] as $row) {
                $statement_id = $row['statements'];
                $count = sizeof($statement_id);


                $monthcount = $row['months'];
                $monthfirstvalue = $row['months'][0];
                $monthslastvalues = end($monthcount);

                foreach ($statement_id as $statement) {

                    $data = [$statement];
                }


                $accounts_details = [
                    'borrower_id' => $borrower_id,
                    'entity_id' => $entity_id,
                    'linked_id' => $linked_id,
                    'bank_name' => $row['bank'],
                    'account_id' => $row['account_id'],
                    'ifsc' => $row['ifsc'],
                    // 'statement_id'=>$row['last_updated'],
                    'from_date_oldest' => $monthfirstvalue,
                    'todate_latest' => $monthslastvalues,
                    'type_of_accounts' => $row['account_category'],
                    'account_number' => $row['account_number'],
                    'totalaccounts' => $totalaccount,
                ];

                $sql = "select t1.account_id from fp_finbox_accounts_details t1 where t1.account_id='" . $accounts_details['account_id'] . "' and t1.borrower_id=" . $accounts_details['borrower_id'];

                if (count($this->db->query($sql)->result()) == 0) {

                    $this->db->insert("fp_finbox_accounts_details", $accounts_details);
                } else {
                    $this->db->where("account_id", $accounts_details['account_id']);
                    $this->db->update("fp_finbox_accounts_details", $accounts_details);
                }


                // $this->db->insert("fp_finbox_accounts_details", $accounts_details)
            }
            $monthly_analysis = $result['monthly_analysis']['account_id'];

            foreach ($monthly_analysis as $ma) {
                $keys = array_keys($ma);
                foreach ($keys as $key) {
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
                    for ($i = 0; $i <= $avg_bal_count - 1; $i++) {
                        $avg_bal_total = $avg_bal_total + (int) $avg_bal_values[$i];
                    }
                    ;
                    $avg_bal_totals = $avg_bal_total / $avg_bal_count;

                    //  print_r($avg_bal_totals);  
                    $amt_credit_total = 0;
                    for ($i = 0; $i <= $amt_credit_count - 1; $i++) {

                        $amt_credit_total = $amt_credit_total + $amt_credit_values[$i];
                    }
                    ;
                    // $amt_credit_totals = $amt_credit_total / $amt_credit_count;   
                    $amt_credit_totals = $amt_credit_total;  

                    //  print_r($amt_credit_totals);  
                    $amt_debit_total = 0;
                    for ($i = 0; $i <= $amt_debit_count - 1; $i++) {
                        $amt_debit_total = $amt_debit_total + $amt_debit_values[$i];
                    }
                    ;
                    // $amt_debit_totals = $amt_debit_total / $amt_debit_count; 
                    $amt_debit_totals = $amt_debit_total;

                    //  print_r($amt_debit_totals);  

                    $cnt_outward_cheque_bounce_debit_total = 0;
                    for ($i = 0; $i <= $cnt_outward_cheque_bounce_debit_count - 1; $i++) {
                        $cnt_outward_cheque_bounce_debit_total = $cnt_outward_cheque_bounce_debit_total + $cnt_outward_cheque_bounce_debit_values[$i];
                    }
                    ;
                    // $cnt_outward_cheque_bounce_debit_totals = $cnt_outward_cheque_bounce_debit_total / $cnt_outward_cheque_bounce_debit_count;
                    $cnt_outward_cheque_bounce_debit_totals = $cnt_outward_cheque_bounce_debit_total;

                    // print_r($cnt_outward_cheque_bounce_debit_totals);  
                    $cnt_inward_cheque_bounce_credit_total = 0;
                    for ($i = 0; $i <= $cnt_inward_cheque_bounce_credit_count - 1; $i++) {
                        $cnt_inward_cheque_bounce_credit_total = $cnt_inward_cheque_bounce_credit_total + $cnt_inward_cheque_bounce_credit_values[$i];
                    }
                    ;

                    // $cnt_inward_cheque_bounce_credit_totals = $cnt_inward_cheque_bounce_credit_total / $cnt_inward_cheque_bounce_credit_count;
                    $cnt_inward_cheque_bounce_credit_totals = $cnt_inward_cheque_bounce_credit_total;

                    //  print_r($cnt_outward_cheque_bounce_debit_totals); 
                    $avg_credit_transaction_size_total = 0;

                    for ($i = 0; $i <= $avg_credit_transaction_size_count - 1; $i++) {
                        $avg_credit_transaction_size_total = $avg_credit_transaction_size_total + $avg_credit_transaction_size_values[$i];
                    }
                    ;

                    $avg_credit_transaction_size_totals = $avg_credit_transaction_size_total / $avg_credit_transaction_size_count;
                    // print_r($avg_credit_transaction_size_totals); 
                    $avg_debit_transaction_size_total = 0;

                    for ($i = 0; $i <= $avg_debit_transaction_size_count - 1; $i++) {
                        $avg_debit_transaction_size_total = $avg_debit_transaction_size_total + $avg_debit_transaction_size_values[$i];
                    }
                    ;
                    $avg_debit_transaction_size_totals = $avg_debit_transaction_size_total / $avg_debit_transaction_size_count;

                    // print_r($avg_debit_transaction_size_totals);
                    $monthlydata = [
                        'borrower_id' => $borrower_id,
                        'account_id' => $key,
                        'average_eod_balance' => $avg_bal_totals,
                        'total_amount_of_credit_transactions' => $amt_credit_totals,
                        'total_amount_of_debit_transactions' => $amt_debit_totals,
                        'total_no_of_outward_cheque_bounce' => $cnt_outward_cheque_bounce_debit_totals,
                        'total_no_of_inward_cheque_bounce' => $cnt_inward_cheque_bounce_credit_totals,
                        'average_credit_transaction_size' => $avg_credit_transaction_size_totals,
                        'average_debit_transaction_size' => $avg_debit_transaction_size_totals,
                    ];

                    $sql = "select t1.account_id from fp_finbox_monthly_details t1 where t1.account_id='" . $monthlydata['account_id'] . "' and t1.borrower_id=" . $monthlydata['borrower_id'];

                    if (count($this->db->query($sql)->result()) == 0) {

                        $this->db->insert("fp_finbox_monthly_details", $monthlydata);

                    } else {
                        $this->db->where("account_id", $monthlydata['account_id']);
                        $this->db->update("fp_finbox_monthly_details", $monthlydata);

                    }

                    //  $this->db->insert('fp_finbox_monthly_details', $monthlydata);   
                }
            }
            json_output(200, array('status' => 200, 'message' => 'success'));
        } catch (Exception $e) {
            echo 'Caught exception: ', $e->getMessage(), "\n";
        }


    } //  end of finboxapi_pdfxlsx_ma 



    public function finboxapi_old()
    {

        $response['status'] = 200;
        $respStatus = $response['status'];
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            if ($response['status'] == 200) {
                $params = json_decode(file_get_contents('php://input'), TRUE);

                $entity_id = $params['entityid'];
                $borrower_id = $params['borrower_id'];
                $linked_id = $params['linkid'];


                sleep($this->config->item('sleeptime'));

                $sleepcon = true;

                try {
                    //  This url should be a webhook url 
                    $curl = curl_init();

                    curl_setopt_array($curl, array(
                        CURLOPT_URL => 'https://portal.finbox.in/bank-connect/v1/entity/update_webhook/',
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => '',
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => 'POST',
                        CURLOPT_POSTFIELDS => '{
                            "webhook_url": "https://app.finnup.in/api/index.php/Finwebhook/finwebhooktest",
                            "webhook_mode": 1
                        }',
                        CURLOPT_HTTPHEADER => array(
                            'x-api-key: PimLidLKA52ihOAJDApmDsJjS2nU5eDivjt4WLcB',
                            'server-hash: df080304798e48b8a2a309d2d7ca4686',
                            'Content-Type: application/json'
                        ),
                    )
                    );

                    $response = curl_exec($curl);

                    curl_close($curl);
                    echo $response;




                    // This url is  get pdfs 

                    $Finboxapi = "https://portal.finbox.in/bank-connect/v1/entity/";
                    $finboxendpoint = "/get_pdfs";
                    $entityid = $entity_id;
                    $finbox_str = $Finboxapi . $entityid . $finboxendpoint;
                    $curl = curl_init();

                    print_r($finbox_str);

                    print_r("-------------Url------------------");

                    curl_setopt_array($curl, array(
                        CURLOPT_URL => $finbox_str,
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => '',
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => 'GET',
                        CURLOPT_HTTPHEADER => array(
                            'x-api-key: PimLidLKA52ihOAJDApmDsJjS2nU5eDivjt4WLcB',
                            'server-hash: df080304798e48b8a2a309d2d7ca4686',
                            'content-type: application/x-www-form-urlencoded'
                        ),
                    )
                    );

                    $response = curl_exec($curl);
                    curl_close($curl);
                    $result = json_decode($response, true);

                    print_r($result);

                    print_r("--------result-----------");

                    foreach ($result['statements'] as $responsedata) {
                        $finboxpdf = [
                            "borrower_id" => isset($borrower_id) ? $borrower_id : 0,
                            "entity_id" => isset($entity_id) ? $entity_id : "Not Avaiable",
                            "statement_id" => isset($responsedata['statement_id']) ? $responsedata['statement_id'] : "Not Avaiable",
                            "bank_name" => isset($responsedata['bank_name']) ? $responsedata['bank_name'] : "Not Avaiable",
                            "pdf_password" => isset($responsedata['pdf_password']) ? $responsedata['pdf_password'] : "Not Avaiable",
                            "pdf_url" => isset($responsedata['pdf_url']) ? $responsedata['pdf_url'] :
                            "Not Avaiable",
                            "account_id" => isset($responsedata['account_id']) ? $responsedata['account_id'] : "Not Avaiable",
                            "source" => isset($responsedata['source']) ? $responsedata['source'] : "Not Avaiable",
                            "message" => isset($responsedata['message']) ? $responsedata['message'] : "Not Avaiable",
                        ];

                        print_r($finboxpdf['pdf_url']);
                        print_r("-------pdf_url------------");

                        $responseoutput = file_get_contents($finboxpdf['pdf_url']);



                        $bucket = 'finnup';
                        $keyname = "FINNBID" . $borrower_id . "/" . $finboxpdf['statement_id'];
                        $Folder_name = 'finboxpdf/';
                        $Addkey_name = $Folder_name . $keyname . ".pdf";

                        print_r($Addkey_name);




                        print_r("-------Keyname------------");

                        $credentials = new Aws\Credentials\Credentials('AKIAWJIM4CKQMIAM5R5L', 'GcL436Q16pUChV4ohqqna0QE9arhpGw8Q5sRorBV');

                        $s3 = new S3Client([
                            'region' => 'ap-south-1',
                            'version' => 'latest',
                            'credentials' => $credentials


                        ]);

                        try {
                            // Upload data.
                            $result = $s3->putObject([
                                'Bucket' => $bucket,
                                'Key' => $Addkey_name,
                                'Body' => $responseoutput,
                                'ACL' => 'public-read'
                            ]);
                            // Print the URL to the object.
                            // echo $result['ObjectURL'] . PHP_EOL; 
                            $url = $result['ObjectURL'];

                            print_r($url);

                            print_r("-------S3_url------------");

                            // print_r("file upload successfully in s3 bucket in pdf");
                        } catch (S3Exception $e) {
                            echo $e->getMessage() . PHP_EOL;
                        }


                        $pdffinbox = [
                            'borrower_id' => $finboxpdf['borrower_id'],
                            'entity_id' => $finboxpdf['entity_id'],
                            'statement_id' => $finboxpdf['statement_id'],
                            'bank_name' => $finboxpdf['bank_name'],
                            'pdf_password' => $finboxpdf['pdf_password'],
                            'pdf_url' => $finboxpdf['pdf_url'],
                            'account_id' => $finboxpdf['account_id'],
                            'source' => $finboxpdf['source'],
                            'message' => isset($finboxpdf['message']) ? $finboxpdf['message'] : "Not Avaiable",
                            's3_url' => isset($url) ? $url : null,
                        ];

                        $sql = "select t1.statement_id from fp_finbox_pdfs t1  where  t1.statement_id ='" . $pdffinbox['statement_id'] . "' and t1.borrower_id = " . $pdffinbox['borrower_id'];

                        if (count($this->db->query($sql)->result()) == 0) {

                            $this->db->insert("fp_finbox_pdfs", $pdffinbox);


                            print_r("Data Insert into the Table");

                        } else {
                            $this->db->where('statement_id', $pdffinbox['statement_id']);
                            $this->db->update('fp_finbox_pdfs', $pdffinbox);
                        }
                    }

                    //    This Url is xlsx  End point 




                    $Finboxapi = "https://portal.finbox.in/bank-connect/v1/entity/";
                    $finboxendpoint = "/xlsx_report";
                    $entityid = $entity_id;
                    $finbox_str = $Finboxapi . $entityid . $finboxendpoint;

                    $curl = curl_init();


                    print_r($finbox_str);

                    curl_setopt_array($curl, array(
                        CURLOPT_URL => $finbox_str,
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => '',
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => 'GET',
                        CURLOPT_HTTPHEADER => array(
                            'x-api-key: PimLidLKA52ihOAJDApmDsJjS2nU5eDivjt4WLcB',
                            'server-hash: df080304798e48b8a2a309d2d7ca4686',
                            'content-type: application/x-www-form-urlencoded'
                        ),
                    )
                    );

                    $response = curl_exec($curl);
                    curl_close($curl);
                    $result = json_decode($response, true);

                    print_r($result);




                    foreach ($result['reports'] as $responsedata) {
                        $finboxpdf = [
                            "borrower_id" => $borrower_id,
                            "xlsxlink" => isset($responsedata['link']) ? $responsedata['link'] : " ",
                            "account_id" => $responsedata['account_id'] ? $responsedata['account_id'] : " ",
                        ];

                        print_r($finboxpdf['xlsxlink']);

                        if (isset($responsedata['link']) != "" && $responsedata['account_id'] != "") {
                            $responseoutput = file_get_contents($finboxpdf['xlsxlink']);


                            $bucket = 'finnup';
                            $keyname = "FINNBID" . $borrower_id . "/" . $finboxpdf['account_id'];
                            $Folder_name = 'finboxxlsx/';
                            $Addkey_name = $Folder_name . $keyname . ".xlsx";

                            print_r($Addkey_name);

                            $credentials = new Aws\Credentials\Credentials('AKIAWJIM4CKQMIAM5R5L', 'GcL436Q16pUChV4ohqqna0QE9arhpGw8Q5sRorBV');

                            $s3 = new S3Client([
                                'version' => 'latest',
                                'region' => 'ap-south-1',
                                'credentials' => $credentials

                            ]);
                            try {
                                // Upload data.
                                $result = $s3->putObject([
                                    'Bucket' => $bucket,
                                    'Key' => $Addkey_name,
                                    'Body' => $responseoutput,
                                    'ACL' => 'public-read'
                                ]);

                                // Print the URL to the object.
                                // echo $result['ObjectURL'] . PHP_EOL; 
                                $url = $result['ObjectURL'];

                                print_r($url);

                                //  print_r("file upload successfully in s3 bucket in xlsx ");
                            } catch (S3Exception $e) {
                                echo $e->getMessage() . PHP_EOL;
                            }

                        }

                        $finboxxlsx = [
                            "borrower_id" => $finboxpdf['borrower_id'],
                            "xlsxlink" => $finboxpdf['xlsxlink'],
                            "account_id" => $finboxpdf['account_id'],
                            "s3_url" => isset($url) ? $url : " ",
                        ];

                        $sql = "select t1.account_id from fp_finbox_xlsx_report t1  where t1.account_id = '" . $finboxxlsx['account_id'] . "' and t1.borrower_id = " . $finboxxlsx['borrower_id'];

                        if (count($this->db->query($sql)->result()) == 0) {

                            $this->db->insert('fp_finbox_xlsx_report', $finboxxlsx);

                            print_r("Data insert Into the Table");

                        } else {
                            $this->db->where('account_id', $finboxxlsx['account_id']);
                            $this->db->update('fp_finbox_xlsx_report', $finboxxlsx);

                        }

                    }

                    // This url is monthlyanalysis

                    $Finboxapi = "https://portal.finbox.in/bank-connect/v1/entity/";
                    $finboxendpoint = "/monthly_analysis_updated";
                    $entityid = $entity_id;
                    $finbox_str = $Finboxapi . $entityid . $finboxendpoint;

                    print_r($finbox_str);


                    $curl = curl_init();

                    curl_setopt_array($curl, array(
                        CURLOPT_URL => $finbox_str,
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => '',
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => 'GET',
                        CURLOPT_HTTPHEADER => array(
                            'x-api-key: PimLidLKA52ihOAJDApmDsJjS2nU5eDivjt4WLcB',
                            'server-hash: df080304798e48b8a2a309d2d7ca4686',
                            'content-type: application/x-www-form-urlencoded'
                        ),
                    )
                    );

                    $response = curl_exec($curl);

                    curl_close($curl);

                    $result = json_decode($response, true);

                    print_r($result);


                    $totalaccount = sizeof($result['accounts']);

                    foreach ($result['accounts'] as $row) {
                        $statement_id = $row['statements'];
                        $count = sizeof($statement_id);


                        $monthcount = $row['months'];
                        $monthfirstvalue = $row['months'][0];
                        $monthslastvalues = end($monthcount);

                        foreach ($statement_id as $statement) {

                            $data = [$statement];
                        }


                        $accounts_details = [
                            'borrower_id' => $borrower_id,
                            'entity_id' => $entity_id,
                            'linked_id' => $linked_id,
                            'bank_name' => $row['bank'],
                            'account_id' => $row['account_id'],
                            'ifsc' => $row['ifsc'],
                            // 'statement_id'=>$row['last_updated'],
                            'from_date_oldest' => $monthfirstvalue,
                            'todate_latest' => $monthslastvalues,
                            'type_of_accounts' => $row['account_category'],
                            'account_number' => $row['account_number'],
                            'totalaccounts' => $totalaccount,
                        ];

                        $sql = "select t1.account_id from fp_finbox_accounts_details t1 where t1.account_id='" . $accounts_details['account_id'] . "' and t1.borrower_id=" . $accounts_details['borrower_id'];

                        if (count($this->db->query($sql)->result()) == 0) {

                            $this->db->insert("fp_finbox_accounts_details", $accounts_details);
                        } else {
                            $this->db->where("account_id", $accounts_details['account_id']);
                            $this->db->update("fp_finbox_accounts_details", $accounts_details);
                        }


                        // $this->db->insert("fp_finbox_accounts_details", $accounts_details)
                    }
                    $monthly_analysis = $result['monthly_analysis']['account_id'];

                    foreach ($monthly_analysis as $ma) {
                        $keys = array_keys($ma);
                        foreach ($keys as $key) {
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
                            for ($i = 0; $i <= $avg_bal_count - 1; $i++) {
                                $avg_bal_total = $avg_bal_total + (int) $avg_bal_values[$i];
                            }
                            ;
                            $avg_bal_totals = $avg_bal_total / $avg_bal_count;

                            //  print_r($avg_bal_totals);  
                            $amt_credit_total = 0;
                            for ($i = 0; $i <= $amt_credit_count - 1; $i++) {

                                $amt_credit_total = $amt_credit_total + $amt_credit_values[$i];
                            }
                            ;
                            $amt_credit_totals = $amt_credit_total / $amt_credit_count;

                            //  print_r($amt_credit_totals);  
                            $amt_debit_total = 0;
                            for ($i = 0; $i <= $amt_debit_count - 1; $i++) {
                                $amt_debit_total = $amt_debit_total + $amt_debit_values[$i];
                            }
                            ;
                            $amt_debit_totals = $amt_debit_total / $amt_debit_count;

                            //  print_r($amt_debit_totals);  

                            $cnt_outward_cheque_bounce_debit_total = 0;
                            for ($i = 0; $i <= $cnt_outward_cheque_bounce_debit_count - 1; $i++) {
                                $cnt_outward_cheque_bounce_debit_total = $cnt_outward_cheque_bounce_debit_total + $cnt_outward_cheque_bounce_debit_values[$i];
                            }
                            ;
                            $cnt_outward_cheque_bounce_debit_totals = $cnt_outward_cheque_bounce_debit_total / $cnt_outward_cheque_bounce_debit_count;

                            // print_r($cnt_outward_cheque_bounce_debit_totals);  
                            $cnt_inward_cheque_bounce_credit_total = 0;
                            for ($i = 0; $i <= $cnt_inward_cheque_bounce_credit_count - 1; $i++) {
                                $cnt_inward_cheque_bounce_credit_total = $cnt_inward_cheque_bounce_credit_total + $cnt_inward_cheque_bounce_credit_values[$i];
                            }
                            ;

                            $cnt_inward_cheque_bounce_credit_totals = $cnt_inward_cheque_bounce_credit_total / $cnt_inward_cheque_bounce_credit_count;
                            //  print_r($cnt_outward_cheque_bounce_debit_totals); 
                            $avg_credit_transaction_size_total = 0;

                            for ($i = 0; $i <= $avg_credit_transaction_size_count - 1; $i++) {
                                $avg_credit_transaction_size_total = $avg_credit_transaction_size_total + $avg_credit_transaction_size_values[$i];
                            }
                            ;

                            $avg_credit_transaction_size_totals = $avg_credit_transaction_size_total / $avg_credit_transaction_size_count;
                            // print_r($avg_credit_transaction_size_totals); 
                            $avg_debit_transaction_size_total = 0;

                            for ($i = 0; $i <= $avg_debit_transaction_size_count - 1; $i++) {
                                $avg_debit_transaction_size_total = $avg_debit_transaction_size_total + $avg_debit_transaction_size_values[$i];
                            }
                            ;
                            $avg_debit_transaction_size_totals = $avg_debit_transaction_size_total / $avg_debit_transaction_size_count;

                            // print_r($avg_debit_transaction_size_totals);
                            $monthlydata = [
                                'borrower_id' => $borrower_id,
                                'account_id' => $key,
                                'average_eod_balance' => $avg_bal_totals,
                                'total_amount_of_credit_transactions' => $amt_credit_totals,
                                'total_amount_of_debit_transactions' => $amt_debit_totals,
                                'total_no_of_outward_cheque_bounce' => $cnt_outward_cheque_bounce_debit_totals,
                                'total_no_of_inward_cheque_bounce' => $cnt_inward_cheque_bounce_credit_totals,
                                'average_credit_transaction_size' => $avg_credit_transaction_size_totals,
                                'average_debit_transaction_size' => $avg_debit_transaction_size_totals,
                            ];

                            $sql = "select t1.account_id from fp_finbox_monthly_details t1 where t1.account_id='" . $monthlydata['account_id'] . "' and t1.borrower_id=" . $monthlydata['borrower_id'];

                            if (count($this->db->query($sql)->result()) == 0) {

                                $this->db->insert("fp_finbox_monthly_details", $monthlydata);

                            } else {
                                $this->db->where("account_id", $monthlydata['account_id']);
                                $this->db->update("fp_finbox_monthly_details", $monthlydata);

                            }

                            //  $this->db->insert('fp_finbox_monthly_details', $monthlydata);   
                        }
                    }
                    json_output(200, array('status' => 200, 'message' => 'success'));
                } catch (Exception $e) {
                    echo 'Caught exception: ', $e->getMessage(), "\n";
                }
            }
        }
    } //  end


    public function linkid_add_update()
    {

        $method = $_SERVER['REQUEST_METHOD'];
        if ($method != 'POST')      
        {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } 
        else  {

            if (true) {
                $response['status'] = 200;
                $respStatus = $response['status'];
                $params = json_decode(file_get_contents('php://input'), TRUE);
                try {

                    // $entity_id = $params['entityid'];
                    $borrower_id = $params['borrower_id'];
                    $linked_id = $params['linkid'];


                    $fp_borrower_details = array(
                        // 'finbox_entity_id' => $entity_id,
                        'finbox_link_id' => $linked_id,
                       
                    );

                    $this->db->select();
                    $this->db->from('fp_borrower_user_details');
                    $this->db->where('user_id', $borrower_id);
                    $result = $this->db->get();
                    $txnArr = array();
                    if($result->num_rows() == 1){
                        foreach($result->result() as $row){
                            $txnArr = array(
                                
                                'borrower_id' =>  $row->user_id,
                                "finbox_link_id" => $row->finbox_link_id,
                               
                            );
                            // print_r($row->finbox_link_id);
                            $finbox_link_id = $row->finbox_link_id;
                            }

                            if($finbox_link_id == null || $finbox_link_id == "" ){
                                $this->db->where('user_id', $borrower_id);
                                $this->db->update('fp_borrower_user_details', $fp_borrower_details);
                                $txnArr = array(
                                        
                                    'borrower_id' =>  $borrower_id,
                                    "finbox_link_id" => $linked_id,
                                   
                                );
                            }

                    }else{
                        $txnArr = "Borrwer is not exists";
                    }

                    json_output(200, array('status' => 200, 'data' =>  $txnArr));
                } catch (Exception $e) {
                    json_output(200, array('status' => 401, 'message' => $e->getMessage()));
                }
            } else {
                json_output(200, array('status' => 401, 'message' => "Auth Failed "));
            }

        }

    } // finboxapi_linkidupdate

    public function download_excel_borrower()
    {

        $method = $_SERVER['REQUEST_METHOD'];
        // if ($method != 'POST')      
        if (false)    
        {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } 

        else {        
            $fileName = 'fp_borrower_user_details.xlsx';  

            $spreadsheet = new Spreadsheet(); 
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setCellValue('A1', 'Id');
            $sheet->setCellValue('B1', 'user_id');
            $sheet->setCellValue('C1', 'name');
            $sheet->setCellValue('D1', 'phone');
            $sheet->setCellValue('E1', 'email');
            $sheet->setCellValue('F1', 'is_whatsapp'); 
            $sheet->setCellValue('G1', 'cin'); 
            $sheet->setCellValue('H1', 'company_name'); 
            $sheet->setCellValue('I1', 'gst'); 
            $sheet->setCellValue('J1', 'gst_url'); 
            $sheet->setCellValue('K1', 'pan'); 
            $sheet->setCellValue('L1', 'pan_url'); 
            $sheet->setCellValue('M1', 'company_type'); 
            $sheet->setCellValue('N1', 'about_company'); 
            $sheet->setCellValue('O1', 'company_industry'); 
            $sheet->setCellValue('P1', 'company_address'); 
            $sheet->setCellValue('Q1', 'state'); 
            $sheet->setCellValue('R1', 'district'); 
            $sheet->setCellValue('S1', 'city'); 
            $sheet->setCellValue('T1', 'location'); 
            $sheet->setCellValue('U1', 'pincode'); 
            $sheet->setCellValue('V1', 'company_website'); 
            $sheet->setCellValue('W1', 'company_business_model'); 
            $sheet->setCellValue('X1', 'data_of_incro_month'); 
            $sheet->setCellValue('Y1', 'data_of_incro_year'); 
            $sheet->setCellValue('Z1', 'is_active'); 

            $sheet->setCellValue('AA1', 'created_at');
            $sheet->setCellValue('AB1', 'update_at');
            $sheet->setCellValue('AC1', 'business_age');
            $sheet->setCellValue('AD1', 'landline_number');
            $sheet->setCellValue('AE1', 'alternative_number');
            $sheet->setCellValue('AF1', 'business_rating'); 
            $sheet->setCellValue('AG1', 'corp_address'); 
            $sheet->setCellValue('AH1', 'corp_city'); 
            $sheet->setCellValue('AI1', 'corp_state'); 
            $sheet->setCellValue('AJ1', 'corp_district'); 
            $sheet->setCellValue('AK1', 'corp_pincode'); 
            $sheet->setCellValue('AL1', 'fact_address'); 
            $sheet->setCellValue('AM1', 'fact_city'); 
            $sheet->setCellValue('AN1', 'fact_state'); 
            $sheet->setCellValue('AO1', 'fact_district'); 
            $sheet->setCellValue('AP1', 'fact_pincode'); 
            $sheet->setCellValue('AQ1', 'isnoloanoutstanding'); 
            $sheet->setCellValue('AR1', 'pdoc_url'); 
            $sheet->setCellValue('AS1', 'turnover'); 
            $sheet->setCellValue('AT1', 'networth'); 
            $sheet->setCellValue('AU1', 'rm_id'); 
            $sheet->setCellValue('AV1', 'profilecomplete_percentage'); 
            $sheet->setCellValue('AW1', 'profilecomplete'); 
            $sheet->setCellValue('AX1', 'paid_up_capital'); 
            $sheet->setCellValue('AY1', 'sum_of_charges'); 
            $sheet->setCellValue('AZ1', 'authorized_capital'); 



            $sheet->setCellValue('BA1', 'lei_number');
            $sheet->setCellValue('BB1', 'lei_status');
            $sheet->setCellValue('BC1', 'full_address');
            $sheet->setCellValue('BD1', 'address_line1');
            $sheet->setCellValue('BE1', 'address_line2');
            $sheet->setCellValue('BF1', 'api_city'); 
            $sheet->setCellValue('BG1', 'api_state'); 
            $sheet->setCellValue('BH1', 'classification'); 
            $sheet->setCellValue('BI1', 'last_agm_date'); 
            $sheet->setCellValue('BJ1', 'next_cin'); 
            $sheet->setCellValue('BK1', 'company_status'); 
            $sheet->setCellValue('BL1', 'last_filling_date'); 
            $sheet->setCellValue('BM1', 'api_email'); 
            $sheet->setCellValue('BN1', 'api_pincode'); 
            $sheet->setCellValue('BO1', 'efiling_status'); 
            $sheet->setCellValue('BP1', 'active_compliance'); 
            $sheet->setCellValue('BQ1', 'cirp_status'); 
            $sheet->setCellValue('BR1', 'status'); 
            $sheet->setCellValue('BS1', 'incorporation_date'); 
            $sheet->setCellValue('BT1', 'is_same_as'); 
            $sheet->setCellValue('BU1', 'finbox_link_id'); 
            $sheet->setCellValue('BV1', 'finbox_entity_id'); 
            $sheet->setCellValue('BW1', 'finbox_processing'); 
            $sheet->setCellValue('BX1', 'xlrt_custemer_name'); 
            $sheet->setCellValue('BY1', 'xlrt_entity_id'); 
            $sheet->setCellValue('BZ1', 'pro_created_by');

            $sql="SELECT * FROM fp_borrower_user_details";

            $employeeData=$this->db->query($sql)->result();
            $rows = 2;
            foreach ($employeeData as $val){

                $sheet->setCellValue('A' . $rows, $val->id);
                $sheet->setCellValue('B' . $rows, $val->user_id);
                $sheet->setCellValue('C' . $rows, $val->name);
                $sheet->setCellValue('D' . $rows, $val->phone);
                $sheet->setCellValue('E' . $rows, $val->email);
                $sheet->setCellValue('F' . $rows, $val->is_whatsapp);
                $sheet->setCellValue('G' . $rows, $val->cin);
                $sheet->setCellValue('H' . $rows, $val->company_name);
                $sheet->setCellValue('I' . $rows, $val->gst);
                $sheet->setCellValue('J' . $rows, $val->gst_url);
                $sheet->setCellValue('K' . $rows, $val->pan);
                $sheet->setCellValue('L' . $rows, $val->pan_url);
                $sheet->setCellValue('M' . $rows, $val->company_type);
                $sheet->setCellValue('N' . $rows, $val->about_company);
                $sheet->setCellValue('O' . $rows, $val->company_industry);
                $sheet->setCellValue('P' . $rows, $val->company_address);
                $sheet->setCellValue('Q' . $rows, $val->state);
                $sheet->setCellValue('R' . $rows, $val->district);
                $sheet->setCellValue('S' . $rows, $val->city);
                $sheet->setCellValue('T' . $rows, $val->location);
                $sheet->setCellValue('U' . $rows, $val->pincode);
                $sheet->setCellValue('V' . $rows, $val->company_website);
                $sheet->setCellValue('W' . $rows, $val->company_business_model);
                $sheet->setCellValue('X' . $rows, $val->date_of_incro_month);
                $sheet->setCellValue('Y' . $rows, $val->date_of_incro_year);
                $sheet->setCellValue('Z' . $rows, $val->is_active);

                $sheet->setCellValue('AA' . $rows, $val->created_at);
                $sheet->setCellValue('AB' . $rows, $val->updated_at);
                $sheet->setCellValue('AC' . $rows, $val->business_age);
                $sheet->setCellValue('AD' . $rows, $val->landline_number);
                $sheet->setCellValue('AE' . $rows, $val->alternative_number);
                $sheet->setCellValue('AF' . $rows, $val->business_rating);
                $sheet->setCellValue('AG' . $rows, $val->corp_address);
                $sheet->setCellValue('AH' . $rows, $val->corp_city);
                $sheet->setCellValue('AI' . $rows, $val->corp_state);
                $sheet->setCellValue('AJ' . $rows, $val->corp_distict);
                $sheet->setCellValue('AK' . $rows, $val->corp_pincode);
                $sheet->setCellValue('AL' . $rows, $val->fact_address);
                $sheet->setCellValue('AM' . $rows, $val->fact_city);
                $sheet->setCellValue('AN' . $rows, $val->fact_state);
                $sheet->setCellValue('AO' . $rows, $val->fact_distict);
                $sheet->setCellValue('AP' . $rows, $val->fact_pincode);
                $sheet->setCellValue('AQ' . $rows, $val->isnoloanoutstanding);
                $sheet->setCellValue('AR' . $rows, $val->pdoc_url);
                $sheet->setCellValue('AS' . $rows, $val->turnover);
                $sheet->setCellValue('AT' . $rows, $val->networth);
                $sheet->setCellValue('AU' . $rows, $val->rm_id);
                $sheet->setCellValue('AV' . $rows, $val->profilecomplete_percentage);
                $sheet->setCellValue('AW' . $rows, $val->profilecomplete);
                $sheet->setCellValue('AX' . $rows, $val->paid_up_capital);
                $sheet->setCellValue('AY' . $rows, $val->sum_of_charges);
                $sheet->setCellValue('AZ' . $rows, $val->authorized_capital);


                $sheet->setCellValue('BA' . $rows, $val->lei_number);
                $sheet->setCellValue('BB' . $rows, $val->lei_status);
                $sheet->setCellValue('BC' . $rows, $val->full_address);
                $sheet->setCellValue('BD' . $rows, $val->address_line1);
                $sheet->setCellValue('BE' . $rows, $val->address_line2);
                $sheet->setCellValue('BF' . $rows, $val->api_city);
                $sheet->setCellValue('BG' . $rows, $val->api_state);
                $sheet->setCellValue('BH' . $rows, $val->classification);
                $sheet->setCellValue('BI' . $rows, $val->last_agm_date);
                $sheet->setCellValue('BJ' . $rows, $val->next_cin);
                $sheet->setCellValue('BK' . $rows, $val->company_status);
                $sheet->setCellValue('BL' . $rows, $val->last_filing_date);
                $sheet->setCellValue('BM' . $rows, $val->api_email);
                $sheet->setCellValue('BN' . $rows, $val->api_pincode);
                $sheet->setCellValue('BO' . $rows, $val->efiling_status);
                $sheet->setCellValue('BP' . $rows, $val->active_compliance);
                $sheet->setCellValue('BQ' . $rows, $val->cirp_status);
                $sheet->setCellValue('BR' . $rows, $val->status);
                $sheet->setCellValue('BS' . $rows, $val->incorporation_date);
                $sheet->setCellValue('BT' . $rows, $val->is_sameas);
                $sheet->setCellValue('BU' . $rows, $val->finbox_link_id);
                $sheet->setCellValue('BV' . $rows, $val->finbox_entity_id);
                $sheet->setCellValue('BW' . $rows, $val->finbox_processing);
                $sheet->setCellValue('BX' . $rows, $val->xlrt_custemer_name);
                $sheet->setCellValue('BY' . $rows, $val->xlrt_entity_id);
                $sheet->setCellValue('BZ' . $rows, $val->pro_created_by);
                $rows++;
            } 
            
            // $spreadsheet = new Spreadsheet();
		// $sheet = $spreadsheet->getActiveSheet();
		// $sheet->setCellValue('A1', 'Hello World !');
		
		$writer = new Xlsx($spreadsheet);

        // $writer->save('php://output'); 
        // $objPHPExcel = new PHPExcel();
        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-Disposition: attachment; filename=\"results.xls\"");
        header("Cache-Control: max-age=0");
        
 
        //  echo($sheet);  
      
            $writer->save($fileName); // download file /
            redirect(base_url().$fileName);  
            // $writer->save('php://output'); // download file 
            json_output(200, array('status' => 401, 'message' =>  $writer->save('php://output')));
            // exit();
          
            // $filename ="excelreport.xlsx";
            // $contents = "testdata1 \t testdata2 \t testdata3 \t \n";
            // header('Content-type: application/ms-excel');
            // header('Content-Disposition: attachment; filename='.$filename);
            // echo $contents;
        }
    } // download_excel_borrower

    public function download_excel_lender()
    {

        $method = $_SERVER['REQUEST_METHOD'];
        // if ($method != 'POST')      
        if (false)    
        {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } 

        else {        
            $fileName = 'fp_lender_user_details.xlsx';  

            $spreadsheet = new Spreadsheet(); 
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setCellValue('A1', 'Id');
            $sheet->setCellValue('B1', 'user_id');
            $sheet->setCellValue('C1', 'lender_master_id');
            $sheet->setCellValue('D1', 'poc_name');
            $sheet->setCellValue('E1', 'email');
            $sheet->setCellValue('F1', 'mobile'); 
            $sheet->setCellValue('G1', 'location_id'); 
            $sheet->setCellValue('H1', 'whatsapp_notification'); 
            $sheet->setCellValue('I1', 'department_slug'); 
            $sheet->setCellValue('J1', 'designation'); 
            $sheet->setCellValue('K1', 'is_important_contact'); 
            $sheet->setCellValue('L1', 'branch'); 
            $sheet->setCellValue('M1', 'image'); 
            $sheet->setCellValue('N1', 'is_active'); 
            $sheet->setCellValue('O1', 'lender_status'); 
            $sheet->setCellValue('P1', 'created_at'); 
            $sheet->setCellValue('Q1', 'updated_at'); 


            
            $sql="SELECT * FROM fp_lender_user_details";

            $employeeData=$this->db->query($sql)->result();
            $rows = 2;
            foreach ($employeeData as $val){

                $sheet->setCellValue('A' . $rows, $val->id);
                $sheet->setCellValue('B' . $rows, $val->user_id);
                $sheet->setCellValue('C' . $rows, $val->lender_master_id);
                $sheet->setCellValue('D' . $rows, $val->poc_name);
                $sheet->setCellValue('E' . $rows, $val->email);
                $sheet->setCellValue('F' . $rows, $val->mobile);
                $sheet->setCellValue('G' . $rows, $val->location_id);
                $sheet->setCellValue('H' . $rows, $val->whatsapp_notification);
                $sheet->setCellValue('I' . $rows, $val->department_slug);
                $sheet->setCellValue('J' . $rows, $val->designation);
                $sheet->setCellValue('K' . $rows, $val->is_important_contact);
                $sheet->setCellValue('L' . $rows, $val->branch);
                $sheet->setCellValue('M' . $rows, $val->image);
                $sheet->setCellValue('N' . $rows, $val->is_active);
                $sheet->setCellValue('O' . $rows, $val->lender_status);
                $sheet->setCellValue('P' . $rows, $val->created_at);
                $sheet->setCellValue('Q' . $rows, $val->update_at);
                $rows++;
            } 
            
            // $spreadsheet = new Spreadsheet();
		// $sheet = $spreadsheet->getActiveSheet();
		// $sheet->setCellValue('A1', 'Hello World !');
		
		$writer = new Xlsx($spreadsheet);

        // $writer->save('php://output'); 
        // $objPHPExcel = new PHPExcel();
        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-Disposition: attachment; filename=\"results.xls\"");
        header("Cache-Control: max-age=0");
        
 
        //  echo($sheet);  
      
            $writer->save($fileName); // download file /
            redirect(base_url().$fileName);  
            // $writer->save('php://output'); // download file 
            json_output(200, array('status' => 401, 'message' =>  $writer->save('php://output')));
            // exit();
          
            // $filename ="excelreport.xlsx";
            // $contents = "testdata1 \t testdata2 \t testdata3 \t \n";
            // header('Content-type: application/ms-excel');
            // header('Content-Disposition: attachment; filename='.$filename);
            // echo $contents;
        }
    } // download_excel_lender


    public function download_excel_loanrequest()
    {

        $method = $_SERVER['REQUEST_METHOD'];
        // if ($method != 'POST')      
        if (false)    
        {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } 

        else {        
            $fileName = 'fp_borrower_loanrequests.xlsx';  

            $spreadsheet = new Spreadsheet(); 
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setCellValue('A1', 'Id');
            $sheet->setCellValue('B1', 'borrower_id');
            $sheet->setCellValue('C1', 'product_slug');
            $sheet->setCellValue('D1', 'loanamount_slug');
            $sheet->setCellValue('E1', 'loan_min');
            $sheet->setCellValue('F1', 'loan_max'); 
            $sheet->setCellValue('G1', 'tenor_min'); 
            $sheet->setCellValue('H1', 'tenor_max'); 
            $sheet->setCellValue('I1', 'roi_min'); 
            $sheet->setCellValue('J1', 'roi_max'); 
            $sheet->setCellValue('K1', 'entity_slug'); 
            $sheet->setCellValue('L1', 'loan_request_status'); 
            $sheet->setCellValue('M1', 'loan_request_workflow_status'); 
            $sheet->setCellValue('N1', 'loan_request_remark'); 
            $sheet->setCellValue('O1', 'created_at'); 
            $sheet->setCellValue('P1', 'updated_at'); 
            $sheet->setCellValue('Q1', 'lender_product_details_id'); 
            $sheet->setCellValue('R1', 'location'); 
            $sheet->setCellValue('S1', 'status'); 
            $sheet->setCellValue('T1', 'page_selector'); 
            $sheet->setCellValue('U1', 'created_by'); 
            $sheet->setCellValue('V1', 'updated_by'); 
            $sheet->setCellValue('W1', 'is_deleted'); 

            
            $sql="SELECT * FROM fp_borrower_loanrequests";

            $employeeData=$this->db->query($sql)->result();
            $rows = 2;
            foreach ($employeeData as $val){

                $sheet->setCellValue('A' . $rows, $val->id);
                $sheet->setCellValue('B' . $rows, $val->borrower_id);
                $sheet->setCellValue('C' . $rows, $val->product_slug);
                $sheet->setCellValue('D' . $rows, $val->loanamount_slug);
                $sheet->setCellValue('E' . $rows, $val->loan_min);
                $sheet->setCellValue('F' . $rows, $val->loan_max);
                $sheet->setCellValue('G' . $rows, $val->tenor_min);
                $sheet->setCellValue('H' . $rows, $val->tenor_max);
                $sheet->setCellValue('I' . $rows, $val->roi_min);
                $sheet->setCellValue('J' . $rows, $val->roi_max);
                $sheet->setCellValue('K' . $rows, $val->entity_slug);
                $sheet->setCellValue('L' . $rows, $val->loan_request_status);
                $sheet->setCellValue('M' . $rows, $val->loan_request_workflow_status);
                $sheet->setCellValue('N' . $rows, $val->loan_request_remark);
                $sheet->setCellValue('O' . $rows, $val->created_at);
                $sheet->setCellValue('P' . $rows, $val->updated_at);
                $sheet->setCellValue('Q' . $rows, $val->lender_product_details_id);
                $sheet->setCellValue('R' . $rows, $val->location);
                $sheet->setCellValue('S' . $rows, $val->status);
                $sheet->setCellValue('T' . $rows, $val->page_selector);
                $sheet->setCellValue('U' . $rows, $val->created_by);
                $sheet->setCellValue('V' . $rows, $val->updated_by);
                $sheet->setCellValue('W' . $rows, $val->is_deleted);
                $rows++;
            } 
            
            // $spreadsheet = new Spreadsheet();
		// $sheet = $spreadsheet->getActiveSheet();
		// $sheet->setCellValue('A1', 'Hello World !');
		
		$writer = new Xlsx($spreadsheet);

        // $writer->save('php://output'); 
        // $objPHPExcel = new PHPExcel();
        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-Disposition: attachment; filename=\"results.xls\"");
        header("Cache-Control: max-age=0");
        
 
        //  echo($sheet);  
      
            $writer->save($fileName); // download file /
            redirect(base_url().$fileName);  
            // $writer->save('php://output'); // download file 
            json_output(200, array('status' => 401, 'message' =>  $writer->save('php://output')));
            // exit();
          
            // $filename ="excelreport.xlsx";
            // $contents = "testdata1 \t testdata2 \t testdata3 \t \n";
            // header('Content-type: application/ms-excel');
            // header('Content-Disposition: attachment; filename='.$filename);
            // echo $contents;
        }
    } // download_excel_loanrequest


    public function download_excel_loanapplication()
    {

        $method = $_SERVER['REQUEST_METHOD'];
        // if ($method != 'POST')      
        if (false)    
        {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } 

        else {        
            $fileName = 'fpa_loanapplication.xlsx';  

            $spreadsheet = new Spreadsheet(); 
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setCellValue('A1', 'Id');
            $sheet->setCellValue('B1', 'loanrequest_id');
            $sheet->setCellValue('C1', 'borrower_id');
            $sheet->setCellValue('D1', 'rm');
            $sheet->setCellValue('E1', 'lendermaster_id');
            $sheet->setCellValue('F1', 'product_slug'); 
            $sheet->setCellValue('G1', 'loanapplication_status'); 
            $sheet->setCellValue('H1', 'workflow_status'); 
            $sheet->setCellValue('I1', 'approved_amount'); 
            $sheet->setCellValue('J1', 'sanctioned_amount'); 
            $sheet->setCellValue('K1', 'created_at'); 
            $sheet->setCellValue('L1', 'created_by'); 
            $sheet->setCellValue('M1', 'updated_by'); 
            $sheet->setCellValue('N1', 'updated_at'); 
            $sheet->setCellValue('O1', 'lender_product_id'); 
            $sheet->setCellValue('P1', 'is_created'); 
            $sheet->setCellValue('Q1', 'lender_intrest_received'); 
            $sheet->setCellValue('R1', 'lender_interest_expressed_by'); 
            $sheet->setCellValue('S1', 'show_to_lender'); 
            $sheet->setCellValue('T1', 'show_to_lender_approved_by'); 
            $sheet->setCellValue('U1', 'lender_id'); 
            $sheet->setCellValue('V1', 'lender_spocname'); 
        

            
            $sql="SELECT * FROM fpa_loan_applications";

            $employeeData=$this->db->query($sql)->result();
            $rows = 2;
            foreach ($employeeData as $val){

                $sheet->setCellValue('A' . $rows, $val->id);
                $sheet->setCellValue('B' . $rows, $val->loanrequest_id); 	
                $sheet->setCellValue('C' . $rows, $val->borrower_id);
                $sheet->setCellValue('D' . $rows, $val->rm);
                $sheet->setCellValue('E' . $rows, $val->lendermaster_id);
                $sheet->setCellValue('F' . $rows, $val->product_slug);
                $sheet->setCellValue('G' . $rows, $val->loanapplication_status);
                $sheet->setCellValue('H' . $rows, $val->workflow_status);
                $sheet->setCellValue('I' . $rows, $val->approved_amount);
                $sheet->setCellValue('J' . $rows, $val->sanctioned_amount);
                $sheet->setCellValue('K' . $rows, $val->created_at);
                $sheet->setCellValue('L' . $rows, $val->created_by);
                $sheet->setCellValue('M' . $rows, $val->updated_by);
                $sheet->setCellValue('N' . $rows, $val->updated_at);
                $sheet->setCellValue('O' . $rows, $val->lender_product_id);
                $sheet->setCellValue('P' . $rows, $val->is_created);
                $sheet->setCellValue('Q' . $rows, $val->lender_intrest_received);
                $sheet->setCellValue('R' . $rows, $val->lender_interest_expressed_by);
                $sheet->setCellValue('S' . $rows, $val->show_to_lender);
                $sheet->setCellValue('T' . $rows, $val->show_to_lender_approved_by);
                $sheet->setCellValue('U' . $rows, $val->lender_id);
                $sheet->setCellValue('V' . $rows, $val->lender_spocname);
                
                $rows++;
            } 
            
            // $spreadsheet = new Spreadsheet();
		// $sheet = $spreadsheet->getActiveSheet();
		// $sheet->setCellValue('A1', 'Hello World !');
		
		$writer = new Xlsx($spreadsheet);

        // $writer->save('php://output'); 
        // $objPHPExcel = new PHPExcel();
        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-Disposition: attachment; filename=\"results.xls\"");
        header("Cache-Control: max-age=0");
        
 
        //  echo($sheet);  
      
            $writer->save($fileName); // download file /
            redirect(base_url().$fileName);  
            // $writer->save('php://output'); // download file 
            json_output(200, array('status' => 401, 'message' =>  $writer->save('php://output')));
            // exit();
          
            // $filename ="excelreport.xlsx";
            // $contents = "testdata1 \t testdata2 \t testdata3 \t \n";
            // header('Content-type: application/ms-excel');
            // header('Content-Disposition: attachment; filename='.$filename);
            // echo $contents;
        }
    } // download_excel_loanapplication

    public function download_excel_lendermaster()
    {

        $method = $_SERVER['REQUEST_METHOD'];
        // if ($method != 'POST')      
        if (false)    
        {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } 

        else {        
            $fileName = 'fp_lender_master.xlsx';  

            $spreadsheet = new Spreadsheet(); 
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setCellValue('A1', 'Id');
            $sheet->setCellValue('B1', 'slug');
            $sheet->setCellValue('C1', 'image');
            $sheet->setCellValue('D1', 'lender_name');
            $sheet->setCellValue('E1', 'lender_type');
            $sheet->setCellValue('F1', 'hq_address'); 
            $sheet->setCellValue('G1', 'hq_contact'); 
            $sheet->setCellValue('H1', 'hq_email'); 
            $sheet->setCellValue('I1', 'group_slug'); 
            $sheet->setCellValue('J1', 'display_order'); 
            $sheet->setCellValue('K1', 'is_active'); 
            $sheet->setCellValue('L1', 'created_at'); 
            $sheet->setCellValue('M1', 'updated_at'); 

            $sql="SELECT * FROM fp_lender_master";

            $employeeData=$this->db->query($sql)->result();
            $rows = 2;
            foreach ($employeeData as $val){

                $sheet->setCellValue('A' . $rows, $val->id);
                $sheet->setCellValue('B' . $rows, $val->slug); 	
                $sheet->setCellValue('C' . $rows, $val->image);
                $sheet->setCellValue('D' . $rows, $val->lender_name);
                $sheet->setCellValue('E' . $rows, $val->lender_type);
                $sheet->setCellValue('F' . $rows, $val->hq_address);
                $sheet->setCellValue('G' . $rows, $val->hq_contact);
                $sheet->setCellValue('H' . $rows, $val->hq_email);
                $sheet->setCellValue('I' . $rows, $val->group_slug);
                $sheet->setCellValue('J' . $rows, $val->display_order);
                $sheet->setCellValue('K' . $rows, $val->is_active);
                $sheet->setCellValue('L' . $rows, $val->created_at);
                $sheet->setCellValue('M' . $rows, $val->updated_at);
                $rows++;
            } 
            
            // $spreadsheet = new Spreadsheet();
		// $sheet = $spreadsheet->getActiveSheet();
		// $sheet->setCellValue('A1', 'Hello World !');
		
		$writer = new Xlsx($spreadsheet);

        // $writer->save('php://output'); 
        // $objPHPExcel = new PHPExcel();
        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-Disposition: attachment; filename=\"results.xls\"");
        header("Cache-Control: max-age=0");
        
 
        //  echo($sheet);  
      
            $writer->save($fileName); // download file /
            redirect(base_url().$fileName);  
            // $writer->save('php://output'); // download file 
            json_output(200, array('status' => 401, 'message' =>  $writer->save('php://output')));
            // exit();
          
            // $filename ="excelreport.xlsx";
            // $contents = "testdata1 \t testdata2 \t testdata3 \t \n";
            // header('Content-type: application/ms-excel');
            // header('Content-Disposition: attachment; filename='.$filename);
            // echo $contents;
        }
    } // download_excel_lendermaster



    public function download_excel_products()
    {

        $method = $_SERVER['REQUEST_METHOD'];
        // if ($method != 'POST')      
        if (false)    
        {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } 

        else {        
            $fileName = 'fp_products.xlsx';  

            $spreadsheet = new Spreadsheet(); 
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setCellValue('A1', 'Id');
            $sheet->setCellValue('B1', 'name');
            $sheet->setCellValue('C1', 'slug');
            $sheet->setCellValue('D1', 'products_type');
            $sheet->setCellValue('E1', 'image');
            $sheet->setCellValue('F1', 'is_active'); 
            $sheet->setCellValue('G1', 'created_at'); 
            $sheet->setCellValue('H1', 'updated_at'); 
            $sheet->setCellValue('I1', 'display_order'); 
            $sheet->setCellValue('J1', 'amounts'); 
            $sheet->setCellValue('K1', 'tenor_min'); 
            $sheet->setCellValue('L1', 'tenor_max'); 
            $sheet->setCellValue('M1', 'tenor'); 
            $sheet->setCellValue('N1', 'roi_min'); 
            $sheet->setCellValue('O1', 'roi_max'); 
                        
            $sql="SELECT * FROM fp_products";

            $employeeData=$this->db->query($sql)->result();
            $rows = 2;
            foreach ($employeeData as $val){

                $sheet->setCellValue('A' . $rows, $val->id);
                $sheet->setCellValue('B' . $rows, $val->name); 	
                $sheet->setCellValue('C' . $rows, $val->slug);
                $sheet->setCellValue('D' . $rows, $val->products_type);
                $sheet->setCellValue('E' . $rows, $val->image);
                $sheet->setCellValue('F' . $rows, $val->is_active);
                $sheet->setCellValue('G' . $rows, $val->created_at);
                $sheet->setCellValue('H' . $rows, $val->updated_at);
                $sheet->setCellValue('I' . $rows, $val->display_order);
                $sheet->setCellValue('J' . $rows, $val->amounts);
                $sheet->setCellValue('K' . $rows, $val->tenor_min);
                $sheet->setCellValue('L' . $rows, $val->tenor_max);
                $sheet->setCellValue('M' . $rows, $val->tenor);
                $sheet->setCellValue('N' . $rows, $val->roi_min);
                $sheet->setCellValue('O' . $rows, $val->roi_max);
                $rows++;
            } 
            
            // $spreadsheet = new Spreadsheet();
		// $sheet = $spreadsheet->getActiveSheet();
		// $sheet->setCellValue('A1', 'Hello World !');
		
		$writer = new Xlsx($spreadsheet);

        // $writer->save('php://output'); 
        // $objPHPExcel = new PHPExcel();
        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-Disposition: attachment; filename=\"results.xls\"");
        header("Cache-Control: max-age=0");
        
 
        //  echo($sheet);  
      
            $writer->save($fileName); // download file /
            redirect(base_url().$fileName);  
            // $writer->save('php://output'); // download file 
            json_output(200, array('status' => 401, 'message' =>  $writer->save('php://output')));
            // exit();
          
            // $filename ="excelreport.xlsx";
            // $contents = "testdata1 \t testdata2 \t testdata3 \t \n";
            // header('Content-type: application/ms-excel');
            // header('Content-Disposition: attachment; filename='.$filename);
            // echo $contents;
        }
    } // download_excel_lendermaster











}