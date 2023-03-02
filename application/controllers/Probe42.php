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
          try{
                           $probeAPI ='https://api.probe42.in/probe_pro_sandbox/companies/';
                           $probebasedetails ="/base-details";
                           $cin=isset($params['data']) ? $params['data'] : ''; 
                           // $cin    =  "L74120MH1985PLC035308";
                           $name_str =$probeAPI.$cin.$probebasedetails;
                           $urlc=$name_str;
            
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $urlc);
                        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
                        curl_setopt($ch, CURLOPT_HTTPHEADER, [
                            'x-api-version : 1.3',
                            'x-api-key : HeqZByvSwm8PdxEL1drWA2LG9QF84PkaPHeyLvl0'
                        ]);            
                        $season_data = curl_exec($ch);
                        curl_close($ch);
                        $result = json_decode($season_data, true);
                        $company = $result;
                     
                        // $borrower_id=isset($params['data']['borrower_id']) ? $params['data']['borrower_id'] : ''; 

                        $company = $result['data']['company'];    // api_details_comapny

                           $cinnext['cin'] = $company['cin'];
                           $cinnext['company_name'] = $company['legal_name'];
                           $cinnext['classification'] =$company['classification'];
                        //    $cinnext['incorporation_date'] =$company['incorporation_date'];
                           $cinnext['paid_up_capital'] =$company['paid_up_capital'];
                           $cinnext['sum_of_charges'] =$company['sum_of_charges'];
                           $cinnext['authorized_capital'] =$company['authorized_capital'];
                           $cinnext['lei_number'] =$company['lei']['number'];
                           $cinnext['lei_status'] =$company['lei']['status'];
                           $cinnext['full_address'] =$company['registered_address']['full_address'];
                           $cinnext['address_line1'] =$company['registered_address']['address_line1'];
                           $cinnext['address_line2'] =$company['registered_address']['address_line2'];
                           $cinnext['api_city'] =$company['registered_address']['city'];
                           $cinnext['api_pincode'] =$company['registered_address']['pincode'];
                           $cinnext['api_state'] =$company['registered_address']['state'];
                           $cinnext['classification'] = $company['classification'];
                           $cinnext['company_status'] = $company['status'];
                        //    $cinnext['next_cin'] = $company['next_cin'];
                           $cinnext['last_agm_date'] = $company['last_agm_date'];
                           $cinnext['last_filing_date'] = $company['last_filing_date'];
                            $cinnext['api_email'] = $company['email'];
                            $cinnext['user_id'] =($params['borrowerid']) ? $params['borrowerid'] : ''; 
                            print_r($cinnext);
                            print_r("-------------good news da-------------------");


                            if(count($this->db->get($cinnext)->result())==0){
                                $this->db->insert('fp_borrower_user_details',array($cinnext)); 
                            }else{
                                 $this->db->where('user_id',$params['borrowerid'] );
                                $this->db->update('fp_borrower_user_details',$cinnext); 
                            }
                            // if(true){

                            //         $this->db->insert('fp_borrower_user_details',$cinnext); 
                            // }
                            // else{

                            //     }
                                    json_output(200, array('status' => 200,' data success'));
                            //   user_id      // $borrower_id=isset($params['data']['borrower_id']) ? $params['data']['borrower_id'] : '';
                               $companydirectors = $result['data']['authorized_signatories'];  // api_company_directors_details
                                 foreach($companydirectors as $directors){
                                $directorsdetails['type']  = 1;
                                $directorsdetails['borrower_id'] =  15;
                                $directorsdetails['pan'] = $directors['pan'];
                                $directorsdetails['din'] = $directors['din'];
                                $directorsdetails['name'] = $directors['name'];
                                $directorsdetails['designation_type'] = $directors['designation'];
                                $directorsdetails['din_status'] = $directors['din_status'];
                                $directorsdetails['gender'] = $directors['gender'];
                                $directorsdetails['date_of_birth'] = $directors['date_of_birth'];
                                $directorsdetails['age'] = $directors['age'];
                                $directorsdetails['date_of_appointment'] = $directors['date_of_appointment'];
                                $directorsdetails['date_of_appiontment_current'] = $directors['date_of_appointment_for_current_designation'];
                                $directorsdetails['date_of_cessation'] = $directors['date_of_cessation'];
                                $directorsdetails['nationality'] = $directors['nationality'];
                                $directorsdetails['dsc_status'] = $directors['dsc_status'];
                                $directorsdetails['dec_expiry_date'] = $directors['dsc_expiry_date'];
                                $directorsdetails['father_name'] = $directors['father_name'];
                                $directorsdetails['address_line1'] = $directors['address']['address_line1'];
                                $directorsdetails['address_line2'] = $directors['address']['address_line2'];
                                $directorsdetails['api_city'] = $directors['address']['city'];
                                $directorsdetails['api_state'] = $directors['address']['state'];
                                $directorsdetails['api_pincode'] = $directors['address']['pincode'];
                                $directorsdetails['api_country'] = $directors['address']['country'];
                                if(true){

                                    // $this->db->insert('fp_director_details',$directorsdetails);
                                }else{

                                }
                                }
                        $shareholder = $result['data']['open_charges'];
                        foreach($shareholder as $apishareholder){

                            $shareholderdetails['api_id'] = $apishareholder['id'];
                            $shareholderdetails['share_date'] = $apishareholder['date'];
                            $shareholderdetails['share_holder_name'] = $apishareholder['holder_name'];
                            $shareholderdetails['amount'] = $apishareholder['amount'];
                            $shareholderdetails['type'] = $apishareholder['type'];
                            if(true){
                                // $this->db->insert('fp_director_shareholding',$shareholderdetails);

                            }else{

                            }
                        }

            json_output(200, array('status' => 200 , 'message'));
        }
        catch(Exception $e)
							{
								echo 'Caught exception: ',  $e->getMessage(), "\n";
							}
							
                        }
           
		}
		
      

}










}







