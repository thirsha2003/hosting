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

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


//-----------------------------------------------------------------------------
//-----------------------------------------------------------------------------
//-----------------------------------------------------------------------------
class Databasedump extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('json_output');
        // $this->load->library('S3_upload');
        // $this->load->library('S3');
    }  // construct




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
    } // download_excel_products





}
