<?php

header('Access-Control-Allow-Origin: *'); //for allow any domain, insecure
header('Access-Control-Allow-Headers: *'); //for allow any headers, insecure
header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE'); //method allowed
header("HTTP/1.1 200 OK");

defined('BASEPATH') OR exit('No direct script access allowed');
//-----------------------------------------------------------------------------
//-----------------------------------------------------------------------------
//-----------------------------------------------------------------------------
class Probe42 extends CI_Controller 
{
	public function __construct(){
		parent::__construct();
		$this->load->helper('json_output');
	}

public function probeapi()
{
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
            $cinorllpin=($params['datas']);
          try
          {                  
            // CIN  NUMBER  USING THIS CODE 
                  if (strlen($cinorllpin)==21)
                {         
                            //  This url  using for cin number 
                           $probeAPI ='https://api.probe42.in/probe_pro_sandbox/companies/';
                           $probebasedetails ="/base-details"; 
                           $cin=$cinorllpin;
                           $name_str =$probeAPI.$cin.$probebasedetails;
                           $urlc=$name_str;
                              $ch = curl_init();
                              curl_setopt($ch, CURLOPT_URL, $urlc);
                              curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                              curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                              curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
                              curl_setopt($ch, CURLOPT_HTTPHEADER, [
                                            'x-api-version : 1.3',
                                             'x-api-key : HeqZByvSwm8PdxEL1drWA2LG9QF84PkaPHeyLvl0',
                                              'Accept: application/json'
                           ]);            
                            $season_data = curl_exec($ch);
                            curl_close($ch);
                            $result = json_decode($season_data, true);
                            $responseData= $result['data'];



                            // Getting the comapy details
                            $companyDetails= $responseData['company'];
                            // object for send to DB 
                            $borrowerbasedetails=[
                              'cin'=>$companyDetails['cin'],
                              'company_name'=>$companyDetails['legal_name'],
                              'classification'=>$companyDetails['classification'],
                              'incorporation_date'=>$companyDetails['incorporation_date'],
                              'paid_up_capital'=>$companyDetails['paid_up_capital'],
                              'sum_of_charges'=> $companyDetails['sum_of_charges'],
                              'authorized_capital'=>$companyDetails['authorized_capital'],
                              'lei_number'=>$companyDetails['lei']['number'],
                              'lei_status'=>$companyDetails['lei']['status'],
                              'full_address'=>$companyDetails['registered_address']['full_address'],
                              'address_line1'=>$companyDetails['registered_address']['address_line1'],
                              'address_line2'=>$companyDetails['registered_address']['address_line2'],
                              'api_city'=>$companyDetails['registered_address']['city'],
                              'api_pincode'=>$companyDetails['registered_address']['pincode'],
                              'api_state'=>$companyDetails['registered_address']['state'],
                              'classification'=> $companyDetails['classification'],
                              'company_status'=> $companyDetails['status'],
                              'next_cin'=> $companyDetails['next_cin'],
                              'last_agm_date'=> $companyDetails['last_agm_date'],
                              'last_filing_date'=> $companyDetails['last_filing_date'],
                              'api_email'=> $companyDetails['email'], 
                            ];
                                  
                            // if( ($params['borrowerid']) && $borrowerbasedetails->cin==0){

                            $where_id= array (
                                'user_id'=>($params['borrowerid']) ? $params['borrowerid'] : '' );
                            $this->db->where($where_id);
                            $this->db->update('fp_borrower_user_details', $borrowerbasedetails);
                      // }
                            // else{
                            
                            //   json_output(200, array('status' => 200 , 'message'=> 'ALREDAY  REGISTERED'));

                            // }

                            // ------------------ End of borroweruserdetails-----------------------------
                               $companydirectors = $responseData['authorized_signatories'];   
                                 foreach($companydirectors as $directors){
                                  // object for send to DB 
                                  $directorsdetails=[
                                    'type'=>1,
                                    'borrower_id'=>isset($params['borrowerid']) ? $params['borrowerid'] : '',
                                    'pan'=> $directors['pan'],
                                    'din'=> $directors['din'],
                                    'name'=> $directors['name'],
                                    'designation_type'=> $directors['designation'],
                                    'din_status'=> $directors['din_status'],
                                    'gender'=> $directors['gender'],
                                    'date_of_birth'=> $directors['date_of_birth'],
                                    'age'=> $directors['age'],
                                    'date_of_appointment'=> $directors['date_of_appointment'],
                                    'date_of_appiontment_current'=> $directors['date_of_appointment_for_current_designation'],
                                    'date_of_cessation'=> $directors['date_of_cessation'],
                                    'nationality'=> $directors['nationality'],
                                    'dsc_status'=> $directors['dsc_status'] ,
                                    'dec_expiry_date'=> $directors['dsc_expiry_date'],
                                    'father_name'=> $directors['father_name'],
                                    'address_line1'=> $directors['address']['address_line1'],
                                    'address_line2'=> $directors['address']['address_line2'],
                                    'api_city'=> $directors['address']['city'],
                                    'api_state'=> $directors['address']['state'],
                                    'api_pincode'=> $directors['address']['pincode'],
                                  ];                  
                                                      //  if(($params['borrowerid'])==0 && ($directorsdetails->pan==0 || $directorsdetails->din==0 )){
                                                        $this->db->insert('fp_director_details', $directorsdetails);
                                                        $fp_director = $this->db->insert_id();
                                                      //  }
                                                      //  else{
                                                      //   json_output(200, array('status' => 200 , 'message'=> 'ALREDAY INSERT DIRECTOR DATA')); 
                                                      //  }
                                                   

                                                    $sql="select t1.pan , t1.id ,t1.din
                                                    from fp_director_details t1 where t1.id=".$fp_director;
                                                    $director_data = $this->db->query($sql)->row();

                                                  //  This url using for director network
                                                  //  Today check to pan or din 
                                                  if($director_data->pan!=null){

                                                    //  using this url pan 

                                                    $probeAPI ='https://api.probe42.in/probe_pro_sandbox/director/network?pan=';
                                                    $pan= $director_data->pan;
                                                    $name_str=$probeAPI.$pan;
                                                    $urlc=$name_str;
                                                    $ch = curl_init();
                                                    curl_setopt($ch, CURLOPT_URL, $urlc);
                                                    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                                                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
                                                    curl_setopt($ch, CURLOPT_HTTPHEADER, [
                                                              'x-api-version : 1.3',
                                                                'x-api-key : HeqZByvSwm8PdxEL1drWA2LG9QF84PkaPHeyLvl0',
                                                                'Accept: application/json'
                                                    ]);            
                                                    $season_data = curl_exec($ch);
                                                    curl_close($ch);
                                                    $result = json_decode($season_data, true);
                                                    $director_network = $result;

                                                    foreach ($director_network['data']['director'] as $director_data_new ){
                                                      if($director_data_new['network']['companies'])
                                                      {
                                                      foreach ($director_data_new['network']['companies']  as $director_company )
                                                      {
                                                        //  object for send to DB
                                                        $director_company_details=[
                                                          'borrower_id'=>isset($params['borrowerid']) ? $params['borrowerid'] : '',
                                                           'director_id'=>$director_data->id,
                                                           'cin'=>$director_company['cin'],
                                                           'legal_name'=>$director_company['legal_name'],
                                                           'company_status'=>$director_company['company_status'],
                                                           'incorporation_date'=>$director_company['incorporation_date'],
                                                           'paid_up_capital'=>$director_company['paid_up_capital'],
                                                           'sum_of_charges'=>$director_company['sum_of_charges'],
                                                           'city'=>$director_company['city'],
                                                           'designation'=>$director_company['designation'],
                                                           'date_of_appointment'=>$director_company['date_of_appointment'],
                                                           'date_of_appointment_for_current_designation'=>$director_company['date_of_appointment_for_current_designation'],
                                                           'date_of_cessation'=>$director_company['date_of_cessation'],
                                                           'active_compliance'=>$director_company['active_compliance'],

                                                        ]; 
                                                          //  if($director_company_details->director_id  and $director_company_details->cin ){

                                                            $this->db->insert('fp_director_network', $director_company_details);

                                                          //  }
                                                          //  else{
                                                          //   json_output(200, array('status' => 200 , 'message'=> 'ALREDAY INSERT DIRECTOR DATA')); 
                                                          //  } 
                                                      } 
                                                    }
                                                    if ($director_data_new['network']['llps']){
                                                      foreach ($director_data_new['network']['llps'] as $directorllps){
                                                        // object for send to DB 
                                                        $director_llps_details=[
                                                          'borrower_id'=>isset($params['borrowerid']) ? $params['borrowerid'] : '',
                                                          'director_id'=>$director_data->id,
                                                          'cin'=>$directorllps['llpin'],
                                                          'legal_name'=>$directorllps['legal_name'],
                                                          'company_status'=>$directorllps['status'],
                                                          'incorporation_date'=>$directorllps['incorporation_date'],
                                                          'sum_of_charges'=>$directorllps['sum_of_charges'],
                                                          'city'=>$directorllps['city'],
                                                          'designation'=>$directorllps['designation'],
                                                          'date_of_appointment'=>$directorllps['date_of_appointment'],
                                                          'date_of_appointment_for_current_designation'=>$directorllps['date_of_appointment_for_current_designation'],
                                                          'date_of_cessation'=>$directorllps['date_of_cessation'], 
                                                        ];
                                                        $this->db->insert('fp_director_network',  $director_llps_details);
                                                      }
                                                    }
                                                  } 

                                                  }
                                                   if($director_data->pan==null){

                                                    // using this url din

                                                    $probeAPI ='https://api.probe42.in/probe_pro_sandbox/director/network?din=';
                                                    
                                                    $din=$director_data->din;
                                                    $name_str =$probeAPI.$pan;
                                                    $urlc=$name_str;
                                                    $ch = curl_init();
                                                    curl_setopt($ch, CURLOPT_URL, $urlc);
                                                    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                                                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
                                                    curl_setopt($ch, CURLOPT_HTTPHEADER, [
                                                              'x-api-version : 1.3',
                                                                'x-api-key : HeqZByvSwm8PdxEL1drWA2LG9QF84PkaPHeyLvl0',
                                                                'Accept: application/json'
                                                    ]);            
                                                    $season_data = curl_exec($ch);
                                                    curl_close($ch);
                                                    $result = json_decode($season_data, true);
                                                    $director_network = $result;

                                                    foreach ($director_network['data']['director'] as $director_data_new ){
                                                      if($director_data_new['network']['companies'])
                                                      {
                                                      foreach ($director_data_new['network']['companies']  as $director_company )
                                                      {
                                                        //  object for send to DB
                                                        $director_company_details=[
                                                          'borrower_id'=>isset($params['borrowerid']) ? $params['borrowerid'] : '',
                                                           'director_id'=>$director_data->id,
                                                           'cin'=>$director_company['cin'],
                                                           'legal_name'=>$director_company['legal_name'],
                                                           'company_status'=>$director_company['company_status'],
                                                           'incorporation_date'=>$director_company['incorporation_date'],
                                                           'paid_up_capital'=>$director_company['paid_up_capital'],
                                                           'sum_of_charges'=>$director_company['sum_of_charges'],
                                                           'city'=>$director_company['city'],
                                                           'designation'=>$director_company['designation'],
                                                           'date_of_appointment'=>$director_company['date_of_appointment'],
                                                           'date_of_appointment_for_current_designation'=>$director_company['date_of_appointment_for_current_designation'],
                                                           'date_of_cessation'=>$director_company['date_of_cessation'],
                                                           'active_compliance'=>$director_company['active_compliance'],

                                                        ]; 
                                                        $this->db->insert('fp_director_network', $director_company_details);
                                                      } 
                                                    }
                                                    if ($director_data_new['network']['llps']){
                                                      foreach ($director_data_new['network']['llps'] as $directorllps){
                                                        // object for send to DB 
                                                        $director_llps_details=[
                                                          'borrower_id'=>isset($params['borrowerid']) ? $params['borrowerid'] : '',
                                                          'director_id'=>$director_data->id,
                                                          'cin'=>$directorllps['llpin'],
                                                          'legal_name'=>$directorllps['legal_name'],
                                                          'company_status'=>$directorllps['company_status'],
                                                          'incorporation_date'=>$directorllps['incorporation_date'],
                                                          'paid_up_capital'=>$directorllps['paid_up_capital'],
                                                          'sum_of_charges'=>$directorllps['sum_of_charges'],
                                                          'city'=>$directorllps['city'],
                                                          'designation'=>$directorllps['designation'],
                                                          'date_of_appointment'=>$directorllps['date_of_appointment'],
                                                          'date_of_appointment_for_current_designation'=>$directorllps['date_of_appointment_for_current_designation'],
                                                          'date_of_cessation'=>$directorllps['date_of_cessation'], 
                                                        ];
                                                        $this->db->insert('fp_director_network',  $director_llps_details);
                                                      }
                                                    }
                                                  } 
                                                   }

                                                  //  ------------------- End of Today checking to pan or din ---------------------   
                              } // ----------------end of directordetails --------------------------------------------
                              $shareholder = $responseData['open_charges'];
                              foreach($shareholder as $apishareholder)
                              {
                                $shareholderdetails=[
                                  'borrower_id'=> ($params['borrowerid']) ? $params['borrowerid'] : '',
                                  'open_charges_id'=>$apishareholder['id'],
                                  'date'=>$apishareholder['date'],
                                  'holder_name'=>$apishareholder['holder_name'],
                                  'amount'=>$apishreholder['amount'],
                                  'type'=>$apishareholder['type'],
  
                                ];
                               $this->db->insert('fp_open_charges',$shareholderdetails);
                              }  //----------------------------- end of shareholderdetails --------------------------------
                              json_output(200, array('status' => 200 , 'message'=> 'success'));           
                } // --------------------------- END OF CIN NUMBER -------------------


                // LLPIN NUMBER  USING THIS CODE 
                else
                        { 
                                // This url is llpin  

                          $probeAPI ='https://api.probe42.in/probe_pro_sandbox/llps/';
                           $probebasedetails ="/base-details";
                           $cin= $cinorllpin; 
                           $name_str =$probeAPI.$cin.$probebasedetails;
                           $urlc=$name_str;
                              $ch = curl_init();
                              curl_setopt($ch, CURLOPT_URL, $urlc);
                              curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                              curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                              curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
                              curl_setopt($ch, CURLOPT_HTTPHEADER, [
                                            'x-api-version : 1.3',
                                             'x-api-key : HeqZByvSwm8PdxEL1drWA2LG9QF84PkaPHeyLvl0',
                                              'Accept: application/json'
                           ]);            
                            $season_data = curl_exec($ch);
                            curl_close($ch);
                            $result = json_decode($season_data, true);
                            $responseData = $result['data'];
                            $companydetails = $responseData['llp']; 
                            // object for  send to DB 
                            $borroweruserbasedetails=[
                              'cin'=>$companydetails['llpin'],
                              'company_name'=>$companydetails['legal_name'],
                              'efiling_status'=>$companydetails['efiling_status'],
                              'sum_of_charges'=>$companydetails['sum_of_charges'],
                              'cirp_status'=>$companydetails['cirp_status'],
                              'efiling_status'=>$companydetails['efiling_status'],
                              'incorporation_date'=>$companydetails['incorporation_date'],
                              'lei_number'=>$companydetails['lei']['number'],
                              'lei_status'=>$companydetails['lei']['status'],
                              'full_address'=>$companydetails['registered_address']['full_address'],
                              'address_line1'=>$companydetails['registered_address']['address_line1'],
                              'address_line2'=>$companydetails['registered_address']   ['address_line2'],
                              'api_city'=>$companydetails['registered_address']['city'],
                              'api_pincode'=>$companydetails['registered_address']['pincode'],
                              'api_state'=>$companydetails['registered_address']['state'],
                              'classification'=>$companydetails['classification'],
                              'api_email'=>$companydetails['email'],
                              'last_agm_date'=>$companydetails['last_financial_reporting_date'],
                              'last_filing_date'=>$companydetails['last_annual_returns_filed_date'],
                              'total_obligation_of_contributio'=>$companydetails['total_obligation_of_contribution'],
                            ];
                              
                            // if(($params['borrowerid']) ? $params['borrowerid'] : '' &&  $borroweruserbasedetails['cin']==null){

                            // };
                            // else {

                            //   json_output(200, array('status' => 200 , 'message'=> 'ALREDAY  REGISTERED'));
                            // };

                          $where_id= array (
                          'user_id'=>($params['borrowerid']) ? $params['borrowerid'] : '' );
                          $this->db->where($where_id);
                          $this->db->update('fp_borrower_user_details', $borroweruserbasedetails);
                          
                          //  ----------------------- end of borroweruserdetails ------------------ 

                            $director=$responseData['directors']; 
                            foreach($director as $directors){
                              // object for send to DB 
                              $directordetails=[
                                'type' =>1,
                                'borrower_id'=>($params['borrowerid']) ? $params ['borrowerid'] : '',
                                'pan'=> $directors['pan'],
                                'din'=> $directors['din'],
                                'name'=> $directors['name'],
                                'designation_type'=>$directors['designation'],
                                'din_status'=> $directors['din_status'],
                                'gender'=> $directors['gender'],
                                'date_of_birth'=> $directors['date_of_birth'],
                                'age'=> $directors['age'],
                                'date_of_appointment'=> $directors['date_of_appointment'],
                                'date_of_appiontment_current'=>$directors['date_of_appointment_for_current_designation'],
                                'date_of_cessation'=>$directors['date_of_cessation'],
                                'nationality'=> $directors['nationality'],
                                'dsc_status'=> $directors['dsc_status'],
                                'dec_expiry_date'=> $directors['dsc_expiry_date'],
                                'father_name'=> $directors['father_name'],
                                'address_line1'=> $directors['address']['address_line1'],
                                'address_line2'=> $directors['address']['address_line2'],
                                'api_city' => $directors['address']['city'],
                                'api_state'=> $directors['address']['state'],
                                'api_pincode'=> $directors['address']['pincode'],
                              ];
                              $this->db->insert('fp_director_details', $directordetails);
                              $fp_director = $this->db->insert_id();

                              $sql="select t1.pan,t1.id,t1.din
                              from fp_director_details t1 where t1.id=".$fp_director;
                              $director_data = $this->db->query($sql)->row();
                            //  This url using for director network 
                            
                            if($director_data->pan!=null){
                              //  using this url pan 
                              $probeAPI ='https://api.probe42.in/probe_pro_sandbox/director/network?pan=';
                              $pan= $director_data->pan;
                              $name_str=$probeAPI.$pan;
                              $urlc=$name_str;
                              $ch = curl_init();
                              curl_setopt($ch, CURLOPT_URL, $urlc);
                              curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                              curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                              curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
                              curl_setopt($ch, CURLOPT_HTTPHEADER, [
                                        'x-api-version : 1.3',
                                          'x-api-key : HeqZByvSwm8PdxEL1drWA2LG9QF84PkaPHeyLvl0',
                                          'Accept: application/json'
                              ]);            
                              $season_data = curl_exec($ch);
                              curl_close($ch);
                              $result = json_decode($season_data, true);
                              $director_network = $result;

                              foreach ($director_network['data']['director'] as $director_data_new ){
                                if($director_data_new['network']['companies'])
                                {
                                foreach ($director_data_new['network']['companies']  as $director_company )
                                {
                                  //  object for send to DB
                                  $director_company_details=[
                                    'borrower_id'=>isset($params['borrowerid']) ? $params['borrowerid'] : '',
                                     'director_id'=>$director_data->id,
                                     'cin'=>$director_company['cin'],
                                     'legal_name'=>$director_company['legal_name'],
                                     'company_status'=>$director_company['company_status'],
                                     'incorporation_date'=>$director_company['incorporation_date'],
                                     'paid_up_capital'=>$director_company['paid_up_capital'],
                                     'sum_of_charges'=>$director_company['sum_of_charges'],
                                     'city'=>$director_company['city'],
                                     'designation'=>$director_company['designation'],
                                     'date_of_appointment'=>$director_company['date_of_appointment'],
                                     'date_of_appointment_for_current_designation'=>$director_company['date_of_appointment_for_current_designation'],
                                     'date_of_cessation'=>$director_company['date_of_cessation'],
                                     'active_compliance'=>$director_company['active_compliance'],

                                  ]; 
                                  $this->db->insert('fp_director_network', $director_company_details);
                                } 
                              }
                              if ($director_data_new['network']['llps']){
                                foreach ($director_data_new['network']['llps'] as $directorllps){
                                  // object for send to DB 
                                  $director_llps_details=[
                                    'borrower_id'=>isset($params['borrowerid']) ? $params['borrowerid'] : '',
                                    'director_id'=>$director_data->id,
                                    'cin'=>$directorllps['llpin'],
                                    'legal_name'=>$directorllps['legal_name'],
                                    'company_status'=>$directorllps['status'],
                                    'incorporation_date'=>$directorllps['incorporation_date'],
                                    'sum_of_charges'=>$directorllps['sum_of_charges'],
                                    'city'=>$directorllps['city'],
                                    'designation'=>$directorllps['designation'],
                                    'date_of_appointment'=>$directorllps['date_of_appointment'],
                                    'date_of_appointment_for_current_designation'=>$directorllps['date_of_appointment_for_current_designation'],
                                    'date_of_cessation'=>$directorllps['date_of_cessation'], 
                                  ];
                                  $this->db->insert('fp_director_network',  $director_llps_details);
                                }
                              }
                            } 
                            }
                             if($director_data->pan==null){
                              // using this url din
                              $probeAPI ='https://api.probe42.in/probe_pro_sandbox/director/network?din=';
                              $din=$director_data->din;
                              $name_str =$probeAPI.$pan;
                              $urlc=$name_str;
                              $ch = curl_init();
                              curl_setopt($ch, CURLOPT_URL, $urlc);
                              curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                              curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                              curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
                              curl_setopt($ch, CURLOPT_HTTPHEADER, [
                                        'x-api-version : 1.3',
                                          'x-api-key : HeqZByvSwm8PdxEL1drWA2LG9QF84PkaPHeyLvl0',
                                          'Accept: application/json'
                              ]);            
                              $season_data = curl_exec($ch);
                              curl_close($ch);
                              $result = json_decode($season_data, true);
                              $director_network = $result;

                              foreach ($director_network['data']['director'] as $director_data_new ){
                                if($director_data_new['network']['companies'])
                                {
                                foreach ($director_data_new['network']['companies']  as $director_company )
                                {
                                  //  object for send to DB
                                  $director_company_details=[
                                    'borrower_id'=>isset($params['borrowerid']) ? $params['borrowerid'] : '',
                                     'director_id'=>$director_data->id,
                                     'cin'=>$director_company['cin'],
                                     'legal_name'=>$director_company['legal_name'],
                                     'company_status'=>$director_company['company_status'],
                                     'incorporation_date'=>$director_company['incorporation_date'],
                                     'paid_up_capital'=>$director_company['paid_up_capital'],
                                     'sum_of_charges'=>$director_company['sum_of_charges'],
                                     'city'=>$director_company['city'],
                                     'designation'=>$director_company['designation'],
                                     'date_of_appointment'=>$director_company['date_of_appointment'],
                                     'date_of_appointment_for_current_designation'=>$director_company['date_of_appointment_for_current_designation'],
                                     'date_of_cessation'=>$director_company['date_of_cessation'],
                                     'active_compliance'=>$director_company['active_compliance'],
                                  ]; 
                                  $this->db->insert('fp_director_network', $director_company_details);
                                } 
                              }
                              if ($director_data_new['network']['llps']){
                                foreach ($director_data_new['network']['llps'] as $directorllps){
                                  // object for send to DB 
                                  $director_llps_details=[
                                    'borrower_id'=>isset($params['borrowerid']) ? $params['borrowerid'] : '',
                                    'director_id'=>$director_data->id,
                                    'cin'=>$directorllps['llpin'],
                                    'legal_name'=>$directorllps['legal_name'],
                                    'company_status'=>$directorllps['company_status'],
                                    'incorporation_date'=>$directorllps['incorporation_date'],
                                    'paid_up_capital'=>$directorllps['paid_up_capital'],
                                    'sum_of_charges'=>$directorllps['sum_of_charges'],
                                    'city'=>$directorllps['city'],
                                    'designation'=>$directorllps['designation'],
                                    'date_of_appointment'=>$directorllps['date_of_appointment'],
                                    'date_of_appointment_for_current_designation'=>$directorllps['date_of_appointment_for_current_designation'],
                                    'date_of_cessation'=>$directorllps['date_of_cessation'], 
                                  ];
                                  $this->db->insert('fp_director_network',  $director_llps_details);
                                }
                              }
                            } 
                             }
                            } // --------------------------- End of directordetails --------------------------
                            $shareholder = $responseData['open_charges'];
                            foreach($shareholder as $apishareholder)
                            {

                              $shareholderdetails=[
                                'borrower_id'=> ($params['borrowerid']) ? $params      ['borrowerid'] : '',
                                'open_charges_id'=>$apishareholder['id'],
                                'date'=>$apishareholder['date'],
                                'holder_name'=>$apishareholder['holder_name'],
                                'amount'=>$apishreholder['amount'],
                                'type'=>$apishareholder['type'],

                              ];
                             $this->db->insert('fp_open_charges',$shareholderdetails);
                                
                            } 
                        } // ---------- End of  LLPIN NUMBER -------------
                       json_output(200, array('status' => 200 , 'message'=> 'success'));
              }

        catch(Exception $e)
							{
								echo 'Caught exception: ',  $e->getMessage(), "\n";
							} 
                        }

                        else {
                   
                          json_output(400, array('status' => 400,'message' => 'Bad request.')); 

                        }
           
		}
}
}







