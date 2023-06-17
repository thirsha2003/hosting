<?php

header('Access-Control-Allow-Origin: *'); //for allow any domain, insecure
header('Access-Control-Allow-Headers: *'); //for allow any headers, insecure
header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE'); //method allowed
header("HTTP/1.1 200 OK");
header("hello: hellooo");
require 'vendor/autoload.php';

defined('BASEPATH') or exit('No direct script access allowed');

include APPPATH . 'ThirdParty/sendgrid-php/sendgrid-php.php';
include APPPATH . 'ThirdParty/mTalkz.php';
include APPPATH . 'libraries/Femail.php';

include APPPATH . 'libraries/JsonuploadtoS3.php'; 

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;
//-----------------------------------------------------------------------------
//-----------------------------------------------------------------------------
//-----------------------------------------------------------------------------
class Probe42 extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('json_output');
    }

    public function probeapi()
    {

       $aws= new \App\Libraries\JsonuploadtoS3;
        $response['status'] = 200;
        $respStatus = $response['status'];
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            if ($response['status'] == 200) {
                $params = json_decode(file_get_contents('php://input'), true);
                $cinorllpin = ($params['datas']);
                try
                {
                    // CIN  NUMBER  USING THIS CODE
                    if (strlen($cinorllpin) == 21) {
                        //  This url  using for cin number
                        $probeAPI = 'https://api.probe42.in/probe_pro/companies/';
                        $probebasedetails = "/base-details";
                        $cin = $cinorllpin;
                        $name_str = $probeAPI . $cin . $probebasedetails;
                        $urlc = $name_str;
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $urlc);
                        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
                        curl_setopt($ch, CURLOPT_HTTPHEADER, [
                            'x-api-version : 1.3',
                            'x-api-key : 0SoeQhURvn2H48V7qSi323dOEb2rwi7L9P0zqzxd',
                            'Accept: application/json',
                        ]);
                        $season_data = curl_exec($ch);
                        curl_close($ch);
                        $result = json_decode($season_data, true);

                            // aws code  start 
                          $projson= json_encode($season_data);
                          $foldername="PROBE/";
                          $aws->aws_s3bucket($params['borrowerid'],$foldername,$projson); 
                        // aws end code 

                        $responseData = $result['data'];

                        // Getting the comapy details
                        $companyDetails = $responseData['company'];
                        $incorporation_date = $companyDetails['incorporation_date'];

                        $date_parts = date_parse($incorporation_date);

                        $year = $date_parts['year'];
                        $month = $date_parts['month'];
                        $day = $date_parts['day'];

                        $months = '';

                        if ($month == '1') {
                            $months = 'January';
                        } else if ($month == '2') {
                            $months = 'February';
                        } else if ($month == '3') {
                            $months = 'March';
                        } else if ($month == '4') {
                            $months = 'April';
                        } else if ($month == '5') {
                            $months = 'may';
                        } else if ($month == '6') {
                            $months = 'June';
                        } else if ($month == '7') {
                            $months = 'July';
                        } else if ($month == '8') {
                            $months = 'August';
                        } else if ($month == '9') {
                            $months = 'September';
                        } else if ($month == '10') {
                            $months = 'October';
                        } else if ($month == '11') {
                            $months = 'November';
                        } else if ($month == '12') {
                            $months = 'December';
                        }
                        // object for send to DB
                        $borrowerbasedetails = [
                            'cin' => $companyDetails['cin'],
                            'company_name' => $companyDetails['legal_name'],
                            'classification' => $companyDetails['classification'],
                            'incorporation_date' => $companyDetails['incorporation_date'],
                            'date_of_incro_month' => $months,
                            'date_of_incro_year' => $year,
                            'paid_up_capital' => $companyDetails['paid_up_capital'],
                            'sum_of_charges' => $companyDetails['sum_of_charges'],
                            'authorized_capital' => $companyDetails['authorized_capital'],
                            'lei_number' => $companyDetails['lei']['number'],
                            'lei_status' => $companyDetails['lei']['status'],
                            'full_address' => $companyDetails['registered_address']['full_address'],
                            'address_line1' => $companyDetails['registered_address']['address_line1'],
                            'address_line2' => $companyDetails['registered_address']['address_line2'],
                            'api_city' => $companyDetails['registered_address']['city'],
                            'api_pincode' => $companyDetails['registered_address']['pincode'],
                            'api_state' => $companyDetails['registered_address']['state'],
                            // 'classification' => $companyDetails['classification'], 
                            'company_status' => $companyDetails['status'],
                            'next_cin' => $companyDetails['next_cin'],
                            'last_agm_date' => $companyDetails['last_agm_date'],
                            'last_filing_date' => $companyDetails['last_filing_date'],
                            'api_email' => $companyDetails['email'],
                            'efiling_status' => isset($companyDetails['efiling_status']) ? $companyDetails['efiling_status'] : null,
                            'cirp_status' => isset($companyDetails['cirp_status']) ? $companyDetails['cirp_status'] : null,
                            'active_compliance' => isset($companyDetails['active_compliance']) ? $companyDetails['active_compliance'] : null,
                            'status' => isset($companyDetails['status']) ? $companyDetails['status'] : null,
                            'pro_created_by'=>"P"

                        ];

                        

                        $where_id = array(
                            'user_id' => ($params['borrowerid']) ? $params['borrowerid'] : '');
                        $this->db->where($where_id);
                        $this->db->update('fp_borrower_user_details', $borrowerbasedetails);

                        // code by prathiban

                        $superadmin = "SELECT email
                            FROM fpa_adminusers
                            WHERE role_slug = 'sa'";
                        $emailtest = $this->db->query($superadmin)->result();

                        $company_names = $companyDetails['legal_name'];
                        $created_by = $params['created_by'];

                        foreach ($emailtest as $row) {

                            $subject = "Dear Superadmin,";
                            $message = "Dear Superadmin," . "<br/>" . "<br/>" . "<br/>" . "A new application for " . $company_names . " has been created by the " . $created_by . " .
                Please click on the below link to view " . $company_names . " or assign the same ." . "<br/>" . "<br/>" .
                                "link : app.finnup.in/#/admin.";
                            $email = new \SendGrid\Mail\Mail ();
                            $email->setSubject("$subject");
                            $email->addContent("text/html", $message);
                            $email->setFrom("support@finnup.in", 'FinnUp Team');

                            // $to = "parthibangnc51@gmail.com";
                            $email->addTo($row->email);
                            // $email->addTo($to);

                            $sendgrid = new \SendGrid ("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");
                            try {
                                $response = $sendgrid->send($email);
                            } catch (Exception $e) {
                                echo 'Caught exception: ', $e->getMessage(), "\n";
                            }
                        }
                        //    end of code by prathiban

                        // ------------------ End of borroweruserdetails-----------------------------
                        $companydirectors = $responseData['authorized_signatories'];

                        foreach ($companydirectors as $directors) {

                            $director_type = '';
                            if ($directors['designation'] == 'Managing Director') {
                                $director_type = 1;
                            } elseif ($directors['designation'] == 'Director') {
                                $director_type = 2;
                            } elseif ($directors['designation'] == 'Additional Director') {
                                $director_type = 3;
                            } elseif ($directors['designation'] == ' Executive Director') {
                                $director_type = 4;
                            } elseif ($directors['designation'] == ' Independent Director') {
                                $director_type = 5;
                            } elseif ($directors['designation'] == ' Nominee Director') {
                                $director_type = 6;
                            }
                            elseif ($directors['designation'] == 'Designated Partner'){
                                $director_type =7;
                            }
                            elseif ($directors['designation']){
                                $director_type =8;
                            }

                            // object for send to DB
                            $directorsdetails = [
                                'type' => $director_type,
                                'borrower_id' => isset($params['borrowerid']) ? $params['borrowerid'] : "",
                                'pan' => isset($directors['pan']) ? $directors['pan'] : null,
                                'din' => isset($directors['din']) ? $directors['din'] : null,
                                'name' => isset($directors['name']) ? $directors['name'] : null,
                                'designation_type' => isset($directors['designation']) ? $directors['designation'] : null,
                                'din_status' => isset($directors['din_status']) ? $directors['din_status'] : null,
                                'gender' => isset($directors['gender']) ? $directors['gender'] : null,
                                'date_of_birth' => isset($directors['date_of_birth']) ? $directors['date_of_birth'] : null,
                                'age' => isset($directors['age']) ? $directors['age'] : null,
                                'date_appointment' => isset($directors['date_of_appointment']) ? $directors['date_of_appointment'] : null,
                                'date_of_appointment_current' => isset($directors['date_of_appointment_for_current_designation']) ? $directors['date_of_appointment_for_current_designation'] : null,
                                'date_of_cessation' => isset($directors['date_of_cessation']) ? $directors['date_of_cessation'] : null,
                                'nationality' => isset($directors['nationality']) ? $directors['nationality'] : null,
                                'dsc_status' => isset($directors['dsc_status']) ? $directors['dsc_status'] : null,
                                'dec_expiry_date' => isset($directors['dsc_expiry_date']) ? $directors['dsc_expiry_date'] : null,
                                'father_name' => isset($directors['father_name']) ? $directors['father_name'] : null,
                                'address_line1' => isset($directors['address']['address_line1']) ? $directors['address']['address_line1'] : null,
                                'address_line2' => isset($directors['address']['address_line2']) ? $directors['address']['address_line2'] : null,
                                'api_city' => isset($directors['address']['city']) ? $directors['address']['city'] : null,
                                'api_state' => isset($directors['address']['state']) ? $directors['address']['state'] : null,
                                'api_pincode' => isset($directors['address']['pincode']) ? $directors['address']['pincode'] : null,
                                'api_country' => isset($directors['address']['country']) ? $directors['address']['country'] : null,
                                'pro_created_by'=>'P',


                            ];
                            $this->db->insert('fp_director_details', $directorsdetails);
                            $fp_director = $this->db->insert_id();

                            $sql = "select t1.pan , t1.id ,t1.din
                                                    from fp_director_details t1 where t1.id=" . $fp_director;
                            $director_data = $this->db->query($sql)->row();

                            //  This url using for director network

                            if ($director_data->pan != null) {

                                //  using this url pan

                                $probeAPI = 'https://api.probe42.in/probe_pro/director/network?pan=';
                                $pan = $director_data->pan;
                                $name_str = $probeAPI . $pan;
                                $urlc = $name_str;
                                $ch = curl_init();
                                curl_setopt($ch, CURLOPT_URL, $urlc);
                                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
                                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                                    'x-api-version : 1.3',
                                    'x-api-key : 0SoeQhURvn2H48V7qSi323dOEb2rwi7L9P0zqzxd',
                                    'Accept: application/json',
                                ]);
                                $season_data = curl_exec($ch);
                                curl_close($ch);
                                $result = json_decode($season_data, true);
                                $director_network = $result;


                            // Aws code start 
                            $projson= json_encode($season_data);
                            $foldername="PROBEDIR/";
                            $aws->aws_s3bucket($director_data->id,$foldername,$projson);
                            // Aws end code 



                                // isset($director_network['data']['director']);
                                $director_network['data']['director'] = isset($director_network['data']['director']) ? $director_network['data']['director'] : null;

                                if ($director_network['data']['director'] != null) {
                                    foreach ($director_network['data']['director'] as $director_data_new) {
                                        if ($director_data_new['network']['companies']) {
                                            foreach ($director_data_new['network']['companies'] as $director_company) {
                                                //  object for send to DB
                                                $director_company_details = [
                                                    'borrower_id' => isset($params['borrowerid']) ? $params['borrowerid'] : '',
                                                    'director_id' => $director_data->id,
                                                    'cin' => isset($director_company['cin']) ? $director_company['cin'] : null,
                                                    'legal_name' => isset($director_company['legal_name']) ? $director_company['legal_name'] : null,
                                                    'company_status' => isset($director_company['company_status']) ? $director_company['company_status'] : null,
                                                    'incorporation_date' => isset($director_company['incorporation_date']) ? $director_company['incorporation_date'] : null,
                                                    'paid_up_capital' => isset($director_company['paid_up_capital']) ? $director_company['paid_up_capital'] : null,
                                                    'sum_of_charges' => isset($director_company['sum_of_charges']) ? $director_company['sum_of_charges'] : null,
                                                    'city' => isset($director_company['city']) ? $director_company['city'] : null,
                                                    'designation' => isset($director_company['designation']) ? $director_company['designation'] : null,
                                                    'date_of_appointment' => isset($director_company['date_of_appointment']) ? $director_company['date_of_appointment'] : null,
                                                    'date_of_appointment_for_current_designation' => isset($director_company['date_of_appointment_for_current_designation']) ? $director_company['date_of_appointment_for_current_designation'] : null,
                                                    'date_of_cessation' => isset($director_company['date_of_cessation']) ? $director_company['date_of_cessation'] : null,
                                                    'active_compliance' => isset($director_company['active_compliance']) ? $director_company['active_compliance'] : null,

                                                ];

                                                $this->db->insert('fp_director_network', $director_company_details);
                                            }
                                        }
                                        if ($director_data_new['network']['llps']) {
                                            foreach ($director_data_new['network']['llps'] as $directorllps) {
                                                // object for send to DB
                                                $director_llps_details = [
                                                    'borrower_id' => isset($params['borrowerid']) ? $params['borrowerid'] : '',
                                                    'director_id' => $director_data->id,
                                                    'cin' => isset($directorllps['llpin']) ? $directorllps['llpin'] : null,
                                                    'legal_name' => isset($directorllps['legal_name']) ? $directorllps['legal_name'] : null,
                                                    'company_status' => isset($directorllps['status']) ? $directorllps['status'] : null,
                                                    'incorporation_date' => isset($directorllps['incorporation_date']) ? $directorllps['incorporation_date'] : null,
                                                    'sum_of_charges' => isset($directorllps['sum_of_charges']) ? $directorllps['sum_of_charges'] : null,
                                                    'city' => isset($directorllps['city']) ? $directorllps['city'] : null,
                                                    'designation' => isset($directorllps['designation']) ? $directorllps['designation'] : null,
                                                    'date_of_appointment' => isset($directorllps['date_of_appointment']) ? $directorllps['date_of_appointment'] : null,
                                                    'date_of_appointment_for_current_designation' => isset($directorllps['date_of_appointment_for_current_designation']) ? $directorllps['date_of_appointment_for_current_designation'] : null,
                                                    'date_of_cessation' => isset($directorllps['date_of_cessation']) ? $directorllps['date_of_cessation'] : null,
                                                ];
                                                $this->db->insert('fp_director_network', $director_llps_details);
                                            }
                                        }
                                    }
                                }

                            }
                            if ($director_data->pan == null) {

                                // using this url din

                                $probeAPI = 'https://api.probe42.in/probe_pro/director/network?din=';

                                $din = $director_data->din;
                                $name_str = $probeAPI . $pan;
                                $urlc = $name_str;
                                $ch = curl_init();
                                curl_setopt($ch, CURLOPT_URL, $urlc);
                                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
                                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                                    'x-api-version : 1.3',
                                    'x-api-key : 0SoeQhURvn2H48V7qSi323dOEb2rwi7L9P0zqzxd',
                                    'Accept: application/json',
                                ]);
                                $season_data = curl_exec($ch);
                                curl_close($ch);
                                $result = json_decode($season_data, true);
                                $director_network = $result;

                                // Aws code start 
                            $projson= json_encode($season_data);
                            $foldername="PROBEDIR/";
                            $aws->aws_s3bucket($director_data->id,$foldername,$projson);
                            // Aws end code 



                                foreach ($director_network['data']['director'] as $director_data_new) {
                                    if ($director_data_new['network']['companies']) {
                                        foreach ($director_data_new['network']['companies'] as $director_company) {
                                            //  object for send to DB
                                            $director_company_details = [
                                                'borrower_id' => isset($params['borrowerid']) ? $params['borrowerid'] : '',
                                                'director_id' => $director_data->id,
                                                'cin' => isset($director_company['cin']) ? $director_company['cin'] : null,
                                                'legal_name' => isset($director_company['legal_name']) ? $director_company['legal_name'] : null,
                                                'company_status' => isset($director_company['company_status']) ? $director_company['company_status'] : null,
                                                'incorporation_date' => isset($director_company['incorporation_date']) ? $director_company['incorporation_date'] : null,
                                                'paid_up_capital' => isset($director_company['paid_up_capital']) ? $director_company['paid_up_capital'] : null,
                                                'sum_of_charges' => isset($director_company['sum_of_charges']) ? $director_company['sum_of_charges'] : null,
                                                'city' => isset($director_company['city']) ? $director_company['city'] : null,
                                                'designation' => isset($director_company['designation']) ? $director_company['designation'] : null,
                                                'date_of_appointment' => isset($director_company['date_of_appointment']) ? $director_company['date_of_appointment'] : null,
                                                'date_of_appointment_for_current_designation' => isset($director_company['date_of_appointment_for_current_designation']) ? $director_company['date_of_appointment_for_current_designation'] : null,
                                                'date_of_cessation' => isset($director_company['date_of_cessation']) ? $director_company['date_of_cessation'] : null,
                                                'active_compliance' => isset($director_company['active_compliance']) ? $director_company['active_compliance'] : null,

                                            ];
                                            $this->db->insert('fp_director_network', $director_company_details);
                                        }
                                    }
                                    if ($director_data_new['network']['llps']) {
                                        foreach ($director_data_new['network']['llps'] as $directorllps) {
                                            // object for send to DB
                                            $director_llps_details = [
                                                'borrower_id' => isset($params['borrowerid']) ? $params['borrowerid'] : '',
                                                'director_id' => $director_data->id,
                                                'cin' => isset($directorllps['llpin']) ? $directorllps['llpin'] : null,
                                                'legal_name' => isset($directorllps['legal_name']) ? $directorllps['legal_name'] : null,
                                                'company_status' => isset($directorllps['company_status']) ? $directorllps['company_status'] : null,
                                                'incorporation_date' => isset($directorllps['incorporation_date']) ? $directorllps['incorporation_date'] : null,
                                                'paid_up_capital' => isset($directorllps['paid_up_capital']) ? $directorllps['paid_up_capital'] : null,
                                                'sum_of_charges' => $directorllps['sum_of_charges'],
                                                'city' => isset($directorllps['city']) ? $directorllps['city'] : null,
                                                'designation' => isset($directorllps['designation']) ? $directorllps['designation'] : null,
                                                'date_of_appointment' => isset($directorllps['date_of_appointment']) ? $directorllps['date_of_appointment'] : null,
                                                'date_of_appointment_for_current_designation' => isset($directorllps['date_of_appointment_for_current_designation']) ? $directorllps['date_of_appointment_for_current_designation'] : null,
                                                'date_of_cessation' => isset($directorllps['date_of_cessation']) ? $directorllps['date_of_cessation'] : null,
                                            ];
                                            $this->db->insert('fp_director_network', $director_llps_details);
                                        }
                                    }
                                }
                            }

                            //  ------------------- End of Today checking to pan or din ---------------------
                        } // ----------------end of directordetails --------------------------------------------
                        $shareholder = $responseData['open_charges'];
                        foreach ($shareholder as $apishareholder) {
                            $shareholderdetails = [
                                'borrower_id' => isset($params['borrowerid']) ? $params['borrowerid'] : '',
                                'open_charges_id' => isset($apishareholder['id']) ? $apishareholder['id'] : null,
                                'date' => isset($apishareholder['date']) ? $apishareholder['date'] : null,
                                'holder_name' => isset($apishareholder['holder_name']) ? $apishareholder['holder_name'] : null,
                                'amount' => isset($apishareholder['amount']) ? $apishareholder['amount'] : null,
                                'type' => isset($apishareholder['type']) ? $apishareholder['type'] : null,

                            ];
                            $this->db->insert('fp_open_charges', $shareholderdetails);
                        } //----------------------------- end of shareholderdetails --------------------------------
                        json_output(200, array('status' => 200, 'message' => 'success'));
                    } // --------------------------- END OF CIN NUMBER -------------------

                    // LLPIN NUMBER  USING THIS CODE
                    else {
                        // This url is llpin

                        $probeAPI = 'https://api.probe42.in/probe_pro/llps/';
                        $probebasedetails = "/base-details";
                        $cin = $cinorllpin;
                        $name_str = $probeAPI . $cin . $probebasedetails;
                        $urlc = $name_str;
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $urlc);
                        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
                        curl_setopt($ch, CURLOPT_HTTPHEADER, [
                            'x-api-version : 1.3',
                            'x-api-key : 0SoeQhURvn2H48V7qSi323dOEb2rwi7L9P0zqzxd',
                            'Accept: application/json',
                        ]);
                        $season_data = curl_exec($ch);
                        curl_close($ch);
                        $result = json_decode($season_data, true);



                        // aws code  start 

                        $projson= json_encode($season_data);
                        $foldername="PROBE/";
                        $aws->aws_s3bucket($params['borrowerid'],$foldername,$projson); 
                        //    aws end code 


                        $responseData = $result['data'];

                        $companydetails = $responseData['llp'];

                        $incorporation_date = $companydetails['incorporation_date'];

                        $date_parts = date_parse($incorporation_date);

                        $year = $date_parts['year'];
                        $month = $date_parts['month'];
                        $day = $date_parts['day'];

                        $months = '';

                        if ($month == '1') {
                            $months = 'January';
                        } else if ($month == '2') {
                            $months = 'February';
                        } else if ($month == '3') {
                            $months = 'March';
                        } else if ($month == '4') {
                            $months = 'April';
                        } else if ($month == '5') {
                            $months = 'may';
                        } else if ($month == '6') {
                            $months = 'June';
                        } else if ($month == '7') {
                            $months = 'July';
                        } else if ($month == '8') {
                            $months = 'August';
                        } else if ($month == '9') {
                            $months = 'September';
                        } else if ($month == '10') {
                            $months = 'October';
                        } else if ($month == '11') {
                            $months = 'November';
                        } else if ($month == '12') {
                            $months = 'December';
                        }
                        // object for  send to DB
                        $borroweruserbasedetails = [
                            'cin' => isset($companydetails['llpin']) ? $companydetails['llpin'] : null,
                            'company_name' => isset($companydetails['legal_name']) ? $companydetails['legal_name'] : null,
                            'efiling_status' => isset($companydetails['efiling_status']) ? $companydetails['efiling_status'] : null,
                            'sum_of_charges' => isset($companydetails['sum_of_charges']) ? $companydetails['sum_of_charges'] : null,
                            'cirp_status' => isset($companydetails['cirp_status']) ? $companydetails['cirp_status'] : null,
                            'incorporation_date' => isset($companydetails['incorporation_date']) ? $companydetails['incorporation_date'] : null,
                            'date_of_incro_month' => $months,
                            'date_of_incro_year' => $year,
                            'lei_number' => isset($companydetails['lei']['number']) ? $companydetails['lei']['number'] : null,
                            'lei_status' => isset($companydetails['lei']['status']) ? $companydetails['lei']['status'] : null,
                            'full_address' => isset($companydetails['registered_address']['full_address']) ? $companydetails['registered_address']['full_address'] : null,
                            'address_line1' => isset($companydetails['registered_address']['address_line1']) ? $companydetails['registered_address']['address_line1'] : null,
                            'address_line2' => isset($companydetails['registered_address']['address_line2']) ? $companydetails['registered_address']['address_line2'] : null,
                            'api_city' => isset($companydetails['registered_address']['city']) ? $companydetails['registered_address']['city'] : null,
                            'api_pincode' => isset($companydetails['registered_address']['pincode']) ? $companydetails['registered_address']['pincode'] : null,
                            'api_state' => isset($companydetails['registered_address']['state']) ? $companydetails['registered_address']['state'] : null,
                            // 'api_country' => isset($companydetails['address']['country']) ? $companydetails['address']['country'] : null,
                            'classification' => isset($companydetails['classification']) ? $companydetails['classification'] : null,
                            'api_email' => isset($companydetails['email']) ? $companydetails['email'] : null,
                            'last_agm_date' => isset($companydetails['last_financial_reporting_date']) ? $companydetails['last_financial_reporting_date'] : null,
                            'last_filing_date' => isset($companydetails['last_annual_returns_filed_date']) ? $companydetails['last_annual_returns_filed_date'] : null,
                            // 'total_obligation_of_contribution'=>isset($companydetails['total_obligation_of_contribution'])?$companydetails['total_obligation_of_contribution']:null,

                            'active_compliance' => isset($companyDetails['active_compliance']) ? $companydetails['active_compliance'] : null,
                            'status' => isset($companyDetails['status']) ? $companydetails['status'] : null,
                            'pro_created_by'=>"P"
                        ];

                        $where_id = array(
                            'user_id' => ($params['borrowerid']) ? $params['borrowerid'] : '');
                        $this->db->where($where_id);
                        $this->db->update('fp_borrower_user_details', $borroweruserbasedetails);

                        //  ----------------------- end of borroweruserdetails ------------------
 
                        
                        $director = $responseData['directors'];


                        foreach ($director as $directors) {
                            // object for send to DB
                            $director_type = '';
                            if ($directors['designation'] == 'Managing Director') {
                                $director_type = 1;
                            } elseif ($directors['designation'] == 'Director') {
                                $director_type = 2;
                            } elseif ($directors['designation'] == 'Additional Director') {
                                $director_type = 3;
                            } elseif ($directors['designation'] == 'Executive Director') {
                                $director_type = 4;
                            } elseif ($directors['designation'] == 'Independent Director') {
                                $director_type = 5;
                            } elseif ($directors['designation'] == 'Nominee Director') {
                                $director_type = 6;
                            }
                            elseif ($directors['designation'] == 'Designated Partner'){
                                $director_type =7;
                            }
                            elseif ($directors['designation']){
                                $director_type =8;
                            }
                            
                            $directordetails = [
                                'type' => $director_type,
                                'borrower_id' => ($params['borrowerid']) ? $params['borrowerid'] : '',
                                'pan' => isset($directors['pan']) ? $directors['pan'] : null,
                                'din' => isset($directors['din']) ? $directors['din'] : null,
                                'name' => isset($directors['name']) ? $directors['name'] : null,
                                'designation_type' => isset($directors['designation']) ? $directors['designation'] : null,
                                'din_status' => isset($directors['din_status']) ? $directors['din_status'] : null,
                                'gender' => isset($directors['gender']) ? $directors['gender'] : null,
                                'date_of_birth' => isset($directors['date_of_birth']) ? $directors['date_of_birth'] : null,
                                'age' => isset($directors['age']) ? $directors['age'] : null,
                                'date_appointment' => isset($directors['date_of_appointment']) ? $directors['date_of_appointment'] : null,
                                'date_of_appointment_current' => isset($directors['date_of_appointment_for_current_designation']) ? $directors['date_of_appointment_for_current_designation'] : null,
                                'date_of_cessation' => isset($directors['date_of_cessation']) ? $directors['date_of_cessation'] : null,
                                'nationality' => isset($directors['nationality']) ? $directors['nationality'] : null,
                                'dsc_status' => isset($directors['dsc_status']) ? $directors['dsc_status'] : null,
                                'dec_expiry_date' => isset($directors['dsc_expiry_date']) ? $directors['dsc_expiry_date'] : null,
                                'father_name' => isset($directors['father_name']) ? $directors['father_name'] : null,
                                'address_line1' => isset($directors['address']['address_line1']) ? $directors['address']['address_line1'] : null,
                                'address_line2' => isset($directors['address']['address_line2']) ? $directors['address']['address_line2'] : null,
                                'api_city' => isset($directors['address']['city']) ? $directors['address']['city'] : null,
                                'api_state' => isset($directors['address']['state']) ? $directors['address']['state'] : null,
                                'api_pincode' => isset($directors['address']['pincode']) ? $directors['address']['pincode'] : null,
                                'api_country' => isset($directors['address']['country']) ? $directors['address']['country'] : null,
                                'pro_created_by'=>'P',


                            ];
                            $this->db->insert('fp_director_details', $directordetails);
                            $fp_director = $this->db->insert_id();

                        


                            $sql = "select t1.pan,t1.id,t1.din
                              from fp_director_details t1 where t1.id=" . $fp_director;
                            $director_data = $this->db->query($sql)->row();
                            //  This url using for director network

                            if ($director_data->pan != null) {
                                //  using this url pan
                                $probeAPI = 'https://api.probe42.in/probe_pro/director/network?pan=';
                                $pan = $director_data->pan;
                                $name_str = $probeAPI . $pan;
                                $urlc = $name_str;
                                $ch = curl_init();
                                curl_setopt($ch, CURLOPT_URL, $urlc);
                                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
                                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                                    'x-api-version : 1.3',
                                    'x-api-key : 0SoeQhURvn2H48V7qSi323dOEb2rwi7L9P0zqzxd',
                                    'Accept: application/json',
                                ]);
                                $season_data = curl_exec($ch);
                                curl_close($ch);
                                $result = json_decode($season_data, true);
                                $director_network = $result;

                                // aws start code 
                            $projson= json_encode($season_data);
                            $foldername="PROBEDIR/";
                            $aws->aws_s3bucket($director_data->id,$foldername,$projson);
                            // aws end code 



                                foreach ($director_network['data']['director'] as $director_data_new) {
                                    if ($director_data_new['network']['companies']) {
                                        foreach ($director_data_new['network']['companies'] as $director_company) {
                                            //  object for send to DB
                                            $director_company_details = [
                                                'borrower_id' => isset($params['borrowerid']) ? $params['borrowerid'] : '',
                                                'director_id' => $director_data->id,
                                                'cin' => isset($director_company['cin']) ? $director_company['cin'] : null,
                                                'legal_name' => isset($director_company['legal_name']) ? $director_company['legal_name'] : null,
                                                'company_status' => isset($director_company['company_status']) ? $director_company['company_status'] : null,
                                                'incorporation_date' => isset($director_company['incorporation_date']) ? $director_company['incorporation_date'] : null,
                                                'paid_up_capital' => isset($director_company['paid_up_capital']) ? $director_company['paid_up_capital'] : null,
                                                'sum_of_charges' => isset($director_company['sum_of_charges']) ? $director_company['sum_of_charges'] : null,
                                                'city' => isset($director_company['city']) ? $director_company['city'] : null,
                                                'designation' => isset($director_company['designation']) ? $director_company['designation'] : null,
                                                'date_of_appointment' => isset($director_company['date_of_appointment']) ? $director_company['date_of_appointment'] : null,
                                                'date_of_appointment_for_current_designation' => isset($director_company['date_of_appointment_for_current_designation']) ? $director_company['date_of_appointment_for_current_designation'] : null,
                                                'date_of_cessation' => isset($director_company['date_of_cessation']) ? $director_company['date_of_cessation'] : null,
                                                'active_compliance' => isset($director_company['active_compliance']) ? $director_company['active_compliance'] : null,

                                            ];
                                            $this->db->insert('fp_director_network', $director_company_details);
                                        }
                                    }
                                    if ($director_data_new['network']['llps']) {
                                        foreach ($director_data_new['network']['llps'] as $directorllps) {
                                            // object for send to DB
                                            $director_llps_details = [
                                                'borrower_id' => isset($params['borrowerid']) ? $params['borrowerid'] : '',
                                                'director_id' => $director_data->id,
                                                'cin' => isset($directorllps['llpin']) ? $directorllps['llpin'] : null,
                                                'legal_name' => isset($directorllps['legal_name']) ? $directorllps['legal_name'] : null,
                                                'company_status' => isset($directorllps['status']) ? $directorllps['status'] : null,
                                                'incorporation_date' => isset($directorllps['incorporation_date']) ? $directorllps['incorporation_date'] : null,
                                                'sum_of_charges' => isset($directorllps['sum_of_charges']) ? $directorllps['sum_of_charges'] : null,
                                                'city' => isset($directorllps['city']) ? $directorllps['city'] : null,
                                                'designation' => isset($directorllps['designation']) ? $directorllps['designation'] : null,
                                                'date_of_appointment' => isset($directorllps['date_of_appointment']) ? $directorllps['date_of_appointment'] : null,
                                                'date_of_appointment_for_current_designation' => isset($directorllps['date_of_appointment_for_current_designation']) ? $directorllps['date_of_appointment_for_current_designation'] : null,
                                                'date_of_cessation' => isset($directorllps['date_of_cessation']) ? $directorllps['date_of_cessation'] : null,
                                            ];
                                            $this->db->insert('fp_director_network', $director_llps_details);
                                        }
                                    }
                                }
                            }
                            if ($director_data->pan == null) {
                                // using this url din
                                $probeAPI = 'https://api.probe42.in/probe_pro/director/network?din=';
                                $din = $director_data->din;
                                $name_str = $probeAPI . $pan;
                                $urlc = $name_str;
                                $ch = curl_init();
                                curl_setopt($ch, CURLOPT_URL, $urlc);
                                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
                                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                                    'x-api-version : 1.3',
                                    'x-api-key : 0SoeQhURvn2H48V7qSi323dOEb2rwi7L9P0zqzxd',
                                    'Accept: application/json',
                                ]);
                                $season_data = curl_exec($ch);
                                curl_close($ch);
                                $result = json_decode($season_data, true);
                                $director_network = $result;
                                  
                                // aws start code 

                                $projson= json_encode($season_data);
                                $foldername="PROBEDIR/";
                                $aws->aws_s3bucket($director_data->id,$foldername,$projson);

                                // aws end code 

                                foreach ($director_network['data']['director'] as $director_data_new) {
                                    if ($director_data_new['network']['companies']) {
                                        foreach ($director_data_new['network']['companies'] as $director_company) {
                                            //  object for send to DB
                                            $director_company_details = [
                                                'borrower_id' => isset($params['borrowerid']) ? $params['borrowerid'] : '',
                                                'director_id' => $director_data->id,
                                                'cin' => isset($director_company['cin']) ? $director_company['cin'] : null,
                                                'legal_name' => isset($director_company['legal_name']) ? $director_company['legal_name'] : null,
                                                'company_status' => isset($director_company['company_status']) ? $director_company['company_status'] : null,
                                                'incorporation_date' => isset($director_company['incorporation_date']) ? $director_company['incorporation_date'] : null,
                                                'paid_up_capital' => isset($director_company['paid_up_capital']) ? $director_company['paid_up_capital'] : null,
                                                'sum_of_charges' => isset($director_company['sum_of_charges']) ? $director_company['sum_of_charges'] : null,
                                                'city' => isset($director_company['city']) ? $director_company['city'] : null,
                                                'designation' => isset($director_company['designation']) ? $director_company['designation'] : null,
                                                'date_of_appointment' => isset($director_company['date_of_appointment']) ? $director_company['date_of_appointment'] : null,
                                                'date_of_appointment_for_current_designation' => isset($director_company['date_of_appointment_for_current_designation']) ? $director_company['date_of_appointment_for_current_designation'] : null,
                                                'date_of_cessation' => isset($director_company['date_of_cessation']) ? $director_company['date_of_cessation'] : null,
                                                'active_compliance' => isset($director_company['active_compliance']) ? $director_company['active_compliance'] : null,
                                            ];
                                            $this->db->insert('fp_director_network', $director_company_details);
                                        }
                                    }
                                    if ($director_data_new['network']['llps']) {
                                        foreach ($director_data_new['network']['llps'] as $directorllps) {
                                            // object for send to DB
                                            $director_llps_details = [
                                                'borrower_id' => isset($params['borrowerid']) ? $params['borrowerid'] : '',
                                                'director_id' => $director_data->id,
                                                'cin' => isset($directorllps['llpin']) ? $directorllps['llpin'] : null,
                                                'legal_name' => isset($directorllps['legal_name']) ? $directorllps['legal_name'] : null,
                                                'company_status' => isset($directorllps['company_status']) ? $directorllps['company_status'] : null,
                                                'incorporation_date' => isset($directorllps['incorporation_date']) ? $directorllps['incorporation_date'] : null,
                                                'paid_up_capital' => isset($directorllps['paid_up_capital']) ? $directorllps['paid_up_capital'] : null,
                                                'sum_of_charges' => isset($directorllps['sum_of_charges']) ? $directorllps['sum_of_charges'] : null,
                                                'city' => isset($directorllps['city']) ? $directorllps['city'] : null,
                                                'designation' => isset($directorllps['designation']) ? $directorllps['designation'] : null,
                                                'date_of_appointment' => isset($directorllps['date_of_appointment']) ? $directorllps['date_of_appointment'] : null,
                                                'date_of_appointment_for_current_designation' => isset($directorllps['date_of_appointment_for_current_designation']) ? $directorllps['date_of_appointment_for_current_designation'] : null,
                                                'date_of_cessation' => isset($directorllps['date_of_cessation']) ? $directorllps['date_of_cessation'] : null,
                                            ];
                                            $this->db->insert('fp_director_network', $director_llps_details);
                                        }
                                    }
                                }
                            }
                        } // --------------------------- End of directordetails --------------------------
                        $shareholder = $responseData['open_charges'];
                        foreach ($shareholder as $apishareholder) {

                            $shareholderdetails = [
                                'borrower_id' => ($params['borrowerid']) ? $params['borrowerid'] : '',
                                'open_charges_id' => $apishareholder['id'],
                                'date' => isset($apishareholder['date']) ? $apishareholder['date'] : null,
                                'holder_name' => isset($apishareholder['holder_name']) ? $apishareholder['holder_name'] : null,
                                'amount' => isset($apishareholder['amount']) ? $apishareholder['amount'] : null,
                                'type' => isset($apishareholder['type']) ? $apishareholder['type'] : null,

                            ];
                            $this->db->insert('fp_open_charges', $shareholderdetails);

                        }
                        json_output(200, array('status' => 200, 'message' => 'success'));
                    } // ---------- End of  LLPIN NUMBER -------------
                    json_output(200, array('status' => 200, 'message' => 'success'));
                } catch (Exception $e) {
                    echo 'Caught exception: ', $e->getMessage(), "\n";
                }
            } else {

                json_output(400, array('status' => 400, 'message' => 'Bad request.'));

            }

        }
    }   // end of probeapi 



    public  function borroweradd(){
        $response['status'] = 200;
        // $respStatus = $response['status'];
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            if ($response['status'] == 200) {
                $params = json_decode(file_get_contents('php://input'), true);

                $company_name = $params['data'] ;
                $borrower_id = $params['borrower_id'];
                $created_by = $params['created_by'];

                 $borrower = array("user_id"=> number_format( $borrower_id));
                 $company = array("company_name"=>$company_name,"pro_created_by"=>"U");

                $this->db->where($borrower);
                $this->db->update("fp_borrower_user_details",$company);

                // Email Notification 
                    $results = "SELECT email
                    FROM fpa_adminusers
                    WHERE role_slug = 'sa'";
                    $emailtest = $this->db->query($results)->result();

                    foreach ($emailtest as $row) {

                        $subject = "Dear Superadmin,";
                        $message = "Dear Superadmin," . "<br/>" . "<br/>" . "<br/>" . "A new application for " . $company_name . " has been created by the " . $created_by . " .
                            Please click on the below link to view " . $company_name . " or assign the same ." . "<br/>" . "<br/>" .
                            "link : app.finnup.in/#/admin.";
                        $email = new \SendGrid\Mail\Mail ();
                        $email->setSubject("$subject");
                        $email->addContent("text/html", $message);
                        $email->setFrom("support@finnup.in", 'FinnUp Team');

                        $email->addTo($row->email);
                        $sendgrid = new \SendGrid ("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");
                        try {
                            $response = $sendgrid->send($email);
                        } catch (Exception $e) {
                            echo 'Caught exception: ', $e->getMessage(), "\n";
                        }
                    }

                json_output(200, array('status' => 200, 'message' => 'success'));

    }
    else {
        json_output(200, array('status' => 400, 'message' => 'Bad Request'));        
    }
}
    }  // borroweradd 

}