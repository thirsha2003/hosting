<?php
header('Access-Control-Allow-Origin: *'); //for allow any domain, insecure
header('Access-Control-Allow-Headers: *'); //for allow any headers, insecure
header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE'); //method allowed
header("HTTP/1.1 200 OK");

defined('BASEPATH') or exit('No direct script access allowed');

class Dashboard extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('json_output');

        $this->ci = &get_instance();
        $this->ci->load->database();
    }

    public function check_token()
    {
        $check_auth_user = $this->login->check_auth_user();
        if ($check_auth_user) {
            $token = $this->ci->input->get_request_header('token', true);
            $user_id = $this->ci->input->get_request_header('user-id', true);
            $checkuser = array('id' => $user_id, 'token' => $token);
            $this->db->where($checkuser);
            $count = $this->db->count_all_results("fpa_adminusers");
            if ($count == 1) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function addborrower()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            // $checkToken = $this->check_token();
            if (true) {
                $response['status'] = 200;
                $respStatus = $response['status'];
                $params = json_decode(file_get_contents('php://input'), true);
                try {
                    $name = $params['data']['name'];
                    $email = $params['data']['email'];
                    $phone = $params['data']['mobile'];
                    $created_by = $params['data']['created_by'];
                    $partner_id = isset($params['partner_id']) ? $params['partner_id'] : null;
                    $partner_name = isset($params['partner_name']) ? $params['partner_name'] : null;
                    $company_name = isset($params['data']['company_name']) ? $params['data']['company_name'] : null;
                    $emailandmobileverified = 1;
                    $add_user = $this->db->insert("fpa_users", array('name' => $name, 'email' => $email, 'mobile' => $phone, 'slug' => 'borrower', 'company_name' => $company_name, 'created_by' => $created_by, 'partner_id' => $partner_id, 'partner_name' => $partner_name, 'is_email_verified' => $emailandmobileverified, 'is_mobile_verified' => $emailandmobileverified));
                    $id = $this->db->insert_id();

                    $add_borrower = $this->db->insert("fp_borrower_user_details", array('user_id' => $id, 'name' => $name, 'email' => $email, 'phone' => $phone, 'company_name' => $company_name));
                    if ($add_user && $add_borrower) {
                        json_output(200, array('status' => 200, 'message' => 'successfully Added', "data" => $id));
                    } else {
                        json_output(200, array('status' => 400, 'message' => 'Bad request.'));
                    }
                } catch (Exception $e) {
                    json_output(200, array('status' => 401, 'message' => $e->getMessage()));
                }
            } else {
                json_output(400, array('status' => 400, 'message' => 'Bad token request.'));
            }
        }
    } // addborrower

    public function borrower_user_detail()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method == "POST") {
            $checkToken = $this->check_token();
            if (true) {
                $response['status'] = 200;
                $respStatus = $response['status'];
                $params = json_decode(file_get_contents('php://input'), true);

                $selectkey = isset($params['selectkey']) ? $params['selectkey'] : "*";
                $join = isset($params['key']) ? $params['key'] : "";
                $where = isset($params['where']) ? $params['where'] : "";

                $sql = "WITH borrowerTable as (SELECT b.slug, b.id, bd.company_industry, bd.company_name, bd.turnover, bd.networth, bd.company_type, bd.profilecomplete, b.rm_name, bd.city FROM fpa_users b, fp_borrower_user_details bd WHERE b.slug ='borrower' AND b.status in ('new','assigned','active') AND b.id = bd.user_id AND bd.company_name is not null) SELECT bd.rm_name ,  bd.slug, bd.profilecomplete ,bd.city,fp_entitytype.id,bd.id as borrower_id,fp_city.id as location_id, fp_city.name as location, fp_entitytype.name as entity_name,bd.company_name as company_name, bd.company_industry as company_industry,bd.turnover, bd.networth FROM borrowerTable as bd LEFT JOIN fp_city ON bd.city = fp_city.id LEFT JOIN fp_entitytype ON bd.company_type = fp_entitytype.id where bd.company_name is not null " . $where;

                $borrowerdetails = $this->db->query($sql)->result();
                $data = $this->db->query($sql);
                foreach ($data->result() as $row) {
                    $txnArr[] = $row->borrower_id;

                }
                $res = implode(",", $txnArr);
                $res = "(" . $res . ")";

                $result = 'SELECT bl.product_slug,bl.borrower_id,p.name  FROM fp_borrower_loanrequests bl ,fp_products p WHERE bl.product_slug=p.slug and bl.borrower_id in ' . $res;

                // $this->db->query($sql)-result();
                // $query = $this->db->get_where('fp_borrower_loanrequests', array('borrower_id' => $res))->result();
                // $trnn[]= $data->id;

                $resp = array('status' => 200, 'message' => 'Success', 'data' => $borrowerdetails, 'data1' => $this->db->query($result)->result());
                return json_output($respStatus, $resp);
            } else {
                return json_output(400, array('status' => 400, 'message' => $checkToken));
            }

        } else {
            return json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        }

    }

    // connector superadmin dashboard card start----------------------------

    public function totalborrowersleads()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method == "POST") {
            // $checkToken = $this->check_token();
            if (true) {
                $response['status'] = 200;
                $respStatus = $response['status'];
                $params = json_decode(file_get_contents('php://input'), true);

                $selectkey = isset($params['selectkey']) ? $params['selectkey'] : "*";
                $join = isset($params['key']) ? $params['key'] : "";
                $where = isset($params['where']) ? $params['where'] : "";
                $partnerid = isset($params['partnerid']) ? $params['partnerid'] : "";

                $sql = "SELECT COUNT(*) as Total_Borrower_Leads
                FROM fpa_users fu, fpa_partners fp
                WHERE fu.created_by = '$where' AND fu.partner_id = '$partnerid' AND fp.email=fu.created_by AND fu.slug='borrower' AND
                       fu.status IN ('new','assigned','active')";

                $resp = array('status' => 200, 'message' => 'Success', 'data' => $this->db->query($sql)->result());
                return json_output($respStatus, $resp);
            } else {
                return json_output(400, array('status' => 400, 'message' => "auth missing"));
            }

        } else {
            return json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        }
    }

    public function totaldraftleads()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method == "POST") {
            // $checkToken = $this->check_token();
            if (true) {
                $response['status'] = 200;
                $respStatus = $response['status'];
                $params = json_decode(file_get_contents('php://input'), true);

                $selectkey = isset($params['selectkey']) ? $params['selectkey'] : "*";
                $join = isset($params['key']) ? $params['key'] : "";
                $where = isset($params['where']) ? $params['where'] : "";
                $partnerid = isset($params['partnerid']) ? $params['partnerid'] : "";

                // $sql="SELECT count(*) as Total_Borrower_Leads
                // FROM fpa_users fu,fpa_partners fp
                // WHERE fu.created_by = fp.email  AND
                //     fu.slug ='borrower' and fu.status IN ('new','assigned','active')AND fu.rm_id IS NULL";

                $sql = "SELECT count(*) as Total_Draft_Leads
								FROM fpa_users fu,fpa_partners fp,fp_borrower_user_details bd
								WHERE fu.created_by = '$where' AND fu.partner_id = '$partnerid' AND fp.email = fu.created_by AND fu.id = bd.user_id AND bd.company_name IS NULL AND bd.pincode IS NULL  AND fu.slug='borrower' AND fu.rm_id IS NULL AND fu.status IN ('new','assigned','active')";

                $resp = array('status' => 200, 'message' => 'Success', 'data' => $this->db->query($sql)->result());
                return json_output($respStatus, $resp);
            } else {
                return json_output(400, array('status' => 400, 'message' => "auth missing"));
            }

        } else {
            return json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        }
    }

    public function totaleligibleleads()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method == "POST") {
            // $checkToken = $this->check_token();
            if (true) {
                $response['status'] = 200;
                $respStatus = $response['status'];
                $params = json_decode(file_get_contents('php://input'), true);

                $selectkey = isset($params['selectkey']) ? $params['selectkey'] : "*";
                $join = isset($params['key']) ? $params['key'] : "";
                $where = isset($params['where']) ? $params['where'] : "";
                $partnerid = isset($params['partnerid']) ? $params['partnerid'] : "";

                $sql = "SELECT count(*) as Total_Eligible_Leads
                FROM fpa_users fu,fpa_partners fp,fp_borrower_user_details bd
                WHERE fp.email = fu.created_by AND fu.id = bd.user_id AND  fu.created_by = '$where' AND fu.partner_id='$partnerid' AND bd.company_name IS NOT NULL AND bd.pincode IS NOT NULL   AND fu.slug='borrower' 
                AND fu.rm_id IS NULL";

                $resp = array('status' => 200, 'message' => 'Success', 'data' => $this->db->query($sql)->result());
                return json_output($respStatus, $resp);
            } else {
                return json_output(400, array('status' => 400, 'message' => $checkToken));
            }

        } else {
            return json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        }
    }

    public function totalassignedleads()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method == "POST") {
            // $checkToken = $this->check_token();
            if (true) {
                $response['status'] = 200;
                $respStatus = $response['status'];
                $params = json_decode(file_get_contents('php://input'), true);

                $selectkey = isset($params['selectkey']) ? $params['selectkey'] : "*";
                $join = isset($params['key']) ? $params['key'] : "";
                $where = isset($params['where']) ? $params['where'] : "";
                $partnerid = isset($params['partnerid']) ? $params['partnerid'] : "";

                        $sql = "SELECT count(*) as TotalAssigned_Leads
                        FROM fpa_users fu,fpa_partners fp,fp_borrower_user_details bd
                        WHERE fu.created_by = '$where' AND fu.partner_id='$partnerid' AND  fp.email = fu.created_by AND fu.id = bd.user_id AND bd.company_name IS NOT NULL AND bd.pincode IS NOT NULL   AND fu.slug='borrower' AND fu.status ='assigned' AND fu.rm_id IS NOT NULL";

                $resp = array('status' => 200, 'message' => 'Success', 'data' => $this->db->query($sql)->result());
                return json_output($respStatus, $resp);
            } else {
                return json_output(400, array('status' => 400, 'message' => $checkToken));
            }

        } else {
            return json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        }
    }

    public function totalapprovedprofile()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method == "POST") {
            // $checkToken = $this->check_token();
            if (true) {
                $response['status'] = 200;
                $respStatus = $response['status'];
                $params = json_decode(file_get_contents('php://input'), true);

                $selectkey = isset($params['selectkey']) ? $params['selectkey'] : "*";
                $join = isset($params['key']) ? $params['key'] : "";
                $where = isset($params['where']) ? $params['where'] : "";
                $partnerid = isset($params['partnerid']) ? $params['partnerid'] : "";

                $sql = "SELECT COUNT(*) as TotalApproved_Profile
                FROM fpa_users fu,fpa_partners fp,fp_borrower_loanrequests bl
                WHERE fu.created_by = '$where' AND fu.partner_id='$partnerid' AND fp.email=fu.created_by AND fu.id= bl.borrower_id AND bl.loan_request_status='CC Approved'";

                $resp = array('status' => 200, 'message' => 'Success', 'data' => $this->db->query($sql)->result());
                return json_output($respStatus, $resp);
            } else {
                return json_output(400, array('status' => 400, 'message' => $checkToken));
            }

        } else {
            return json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        }
    }

    // connector superadmin dashboard summarycard end-----------------------

    public function connector_borrower_list()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method == "POST") {
            //   $checkToken = $this->check_token();
            if (true) {
                $response['status'] = 200;
                $respStatus = $response['status'];
                $params = json_decode(file_get_contents('php://input'), true);

                $selectkey = isset($params['selectkey']) ? $params['selectkey'] : "*";
                $join = isset($params['key']) ? $params['key'] : "";
                $where = isset($params['where']) ? $params['where'] : "";

                $sql = "WITH borrowerTable as (SELECT b.slug, b.status, b.id, bd.company_industry, bd.company_name, bd.turnover, bd.networth, bd.company_type, bd.profilecomplete, b.rm_name, bd.city FROM fpa_users b, fp_borrower_user_details bd WHERE b.slug ='borrower' AND b.id = bd.user_id AND bd.company_name is not null) SELECT bd.rm_name , bd.status,  bd.slug, bd.profilecomplete ,bd.city,fp_entitytype.id,bd.id as borrower_id,fp_city.id as location_id, fp_city.name as location, fp_entitytype.name as entity_name,bd.company_name as company_name, bd.company_industry as company_industry,bd.turnover, bd.networth FROM borrowerTable as bd LEFT JOIN fp_city ON bd.city = fp_city.id LEFT JOIN fp_entitytype ON bd.company_type = fp_entitytype.id where bd.company_name is not null;";

                $borrowerdetails = $this->db->query($sql)->result();
                $data = $this->db->query($sql);
                foreach ($data->result() as $row) {
                    $txnArr[] = $row->borrower_id;

                }
                $res = implode(",", $txnArr);
                $res = "(" . $res . ")";

                $result = 'SELECT bl.product_slug,bl.borrower_id,p.name  FROM fp_borrower_loanrequests bl ,fp_products p WHERE bl.product_slug=p.slug and bl.borrower_id in ' . $res;

                // $this->db->query($sql)-result();
                // $query = $this->db->get_where('fp_borrower_loanrequests', array('borrower_id' => $res))->result();
                // $trnn[]= $data->id;

                $resp = array('status' => 200, 'message' => 'Success', 'data' => $borrowerdetails, 'data1' => $this->db->query($result)->result());
                return json_output($respStatus, $resp);
            } else {
                return json_output(400, array('status' => 400, 'message' => $checkToken));
            }

        } else {
            return json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        }

    }

    public function connectorprofile_details()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $response['status'] = 200;
            $respStatus = $response['status'];

            $params = json_decode(file_get_contents('php://input'), true);
            $params['user_id_fk'] = $this->input->get_request_header('User-ID', true);

            $selectkey = isset($params['selectkey']) ? $params['selectkey'] : "*";
            $join = isset($params['key']) ? $params['key'] : "";
            $where = isset($params['where']) ? $params['where'] : "";

            $sql = "SELECT " . $selectkey . " FROM " . $params['tableName'] . "  WHERE " . $where;

            $resp = array('status' => 200, 'message' => 'Success', 'data' => $this->db->query($sql)->row());
            json_output($respStatus, $resp);
        }
    }

    public function connectorprofile_details1()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $response['status'] = 200;
            $respStatus = $response['status'];

            $params = json_decode(file_get_contents('php://input'), true);
            $params['user_id_fk'] = $this->input->get_request_header('User-ID', true);

            $selectkey = isset($params['selectkey']) ? $params['selectkey'] : "*";
            $join = isset($params['key']) ? $params['key'] : "";
            $where = isset($params['where']) ? $params['where'] : "";

            $sql = "SELECT " . $selectkey . " FROM " . $params['tableName'] . "  WHERE " . $where;

            $resp = array('status' => 200, 'message' => 'Success', 'data' => $this->db->query($sql)->row());
            json_output($respStatus, $resp);
        }
    }

    public function connectorbasicprofile()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {

            $response['status'] = 200;
            $respStatus = $response['status'];
            if ($response['status'] == 200) {
                $params = json_decode(file_get_contents('php://input'), true);

                if ($params['tableName'] == "") {
                    $respStatus = 400;
                    $resp = array('status' => 400, 'message' => 'Fields Missing');
                } else {

                    $sql = "SELECT * FROM " . $params['tableName'] . " WHERE connector_id = " . $params['data']['connector_id'];

                    if (count($this->db->query($sql)->result()) == 0) {
                        $this->db->insert($params['tableName'], $params['data']);
                    } else {
                        $this->db->where('connector_id', $params['data']['connector_id']);
                        $this->db->update($params['tableName'], $params['data']);
                    }
                    $resp = array('status' => 200, 'message' => 'Inserted success', 'data' => $this->db->insert_id());
                }
                json_output($respStatus, $resp);
            }
        }
    }

    public function getconnectorprofile()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            // $check_auth_user = $this->login->check_auth_user();
            // if($check_auth_user == true){
            // $response = $this->login->auth();
            $response['status'] = 200;
            $respStatus = $response['status'];
            if ($response['status'] == 200) {
                $params = json_decode(file_get_contents('php://input'), true);
                $params['user_id_fk'] = $this->input->get_request_header('User-ID', true);

                if ($params['tableName'] == "") {
                    $respStatus = 400;
                    $resp = array('status' => 400, 'message' => 'Fields Missing');
                } else {
                    // $params['data']['user_id_fk'] = (int)$params['user_id_fk'];
                    $sql = "SELECT * FROM " . $params['tableName'] . " WHERE user_id=" . $params['data']['id'];
                    if (count($this->db->query($sql)->result()) == 0) {
                        $resp = array('status' => 201, 'message' => 'no data', 'data' => $this->db->query($sql)->result());
                    } else {
                        $resp = array('status' => 200, 'message' => 'success', 'data' => $this->db->query($sql)->result());
                    }

                }
                json_output($respStatus, $resp);
            }
            // }
        }
    }

    public function connectorspocdetails()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method == "POST") {
            // $checkToken = $this->check_token();
            if (true) {
                $response['status'] = 200;
                $respStatus = $response['status'];
                $params = json_decode(file_get_contents('php://input'), true);

                $selectkey = isset($params['selectkey']) ? $params['selectkey'] : "*";
                $join = isset($params['key']) ? $params['key'] : "";
                $where = isset($params['where']) ? $params['where'] : "";

                $sql = "SELECT cud.spoc_name as name, fl.name as location , fd.name as designation

							from fpa_users fpu, fp_connector_user_details  cud, fp_location fl , fp_departments fd

							where cud.user_id=fpu.id AND fpu.slug='connector'AND cud.department_slug=fd.slug AND fpu.is_email_verified =1 AND fpu.is_mobile_verified=1 AND fl.id=cud.location_id " . $where;

                $resp = array('status' => 200, 'message' => 'Success', 'data' => $this->db->query($sql)->result());
                return json_output($respStatus, $resp);
            } else {
                return json_output(400, array('status' => 400, 'message' => $checkToken));
            }
        } else {
            return json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        }
    }

    public function addconnector()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $checkToken = $this->check_token();
            if (true) {
                $response['status'] = 200;
                $respStatus = $response['status'];
                $params = json_decode(file_get_contents('php://input'), true);
                try {
                    $name = $params['data']['name'];
                    $email = $params['data']['email'];
                    $phone = $params['data']['mobile'];
                    $created_by = $params['data']['created_by'];
                    // $company_name = isset($params['data']['company_name'])?$params['data']['company_name']:null;
                    $emailandmobileverified = 1;
                    $add_user = $this->db->insert("fpa_users", array('name' => $name, 'email' => $email, 'mobile' => $phone, 'slug' => 'connector', 'created_by' => $created_by, 'is_email_verified' => $emailandmobileverified, 'is_mobile_verified' => $emailandmobileverified));
                    $id = $this->db->insert_id();
                    $location = $params['data']['lender_location'];
                    $designation = $params['data']['designation'];

                    $add_connector = $this->db->insert("fp_connector_user_details", array('user_id' => $id, 'spoc_name' => $name, 'spoc_email' => $email, 'spoc_mobile' => $phone, 'spoc_location' => $location, 'spoc_designation' => $designation, 'created_at' => $params['data']['created_at']));
                    if ($add_user && $add_connector) {
                        json_output(200, array('status' => 200, 'message' => 'successfully Added', "data" => $id));
                    } else {
                        json_output(200, array('status' => 400, 'message' => 'Bad request.'));
                    }
                } catch (Exception $e) {
                    json_output(200, array('status' => 401, 'message' => $e->getMessage()));
                }
            } else {
                json_output(400, array('status' => 400, 'message' => 'Bad request.'));
            }
        }

    }

    // addConnector

    // connector dashboard Endpoint-----------------------

    public function taskassignedtopartner()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method == "POST") {
            $checkToken = $this->check_token();
            if (true) {
                $response['status'] = 200;
                $respStatus = $response['status'];
                $params = json_decode(file_get_contents('php://input'), true);

                $where = isset($params['where']) ? $params['where'] : "";
                $createdby = isset($params['createdby']) ? $params['createdby'] : "";

                $sql = "SELECT count(*) as  total_tasksassingedtopartner  from fpa_users where partner_id = '$where' and created_by = '$createdby' ";
                $resp = array('status' => 200, 'message' => 'Success', 'data' => $this->db->query($sql)->result());
                return json_output($respStatus, $resp);
            } else {
                return json_output(400, array('status' => 400, 'message' => $checkToken));
            }
        } else {
            return json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        }
    }

    public function partner_incompleteprofiles()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method == "POST") {
            $checkToken = $this->check_token();
            if (true) {
                $response['status'] = 200;
                $respStatus = $response['status'];
                $params = json_decode(file_get_contents('php://input'), true);

                $where = isset($params['where']) ? $params['where'] : "";
                $createdby = isset($params['createdby']) ? $params['createdby'] : "";

                $sql = "SELECT count(*) as partner_dashboard_incompleteprofiles from fpa_users fu,fp_borrower_user_details bu  where fu.id= bu.user_id and bu.profilecomplete ='incomplete' and fu.partner_id='$where' and created_by = '$createdby' ";

                $resp = array('status' => 200, 'message' => 'Success', 'data' => $this->db->query($sql)->result());
                return json_output($respStatus, $resp);
            } else {
                return json_output(400, array('status' => 400, 'message' => $checkToken));
            }
        } else {
            return json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        }
    }
    public function partner_completeprofiles()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method == "POST") {
            $checkToken = $this->check_token();
            if (true) {
                $response['status'] = 200;
                $respStatus = $response['status'];
                $params = json_decode(file_get_contents('php://input'), true);

                $where = isset($params['where']) ? $params['where'] : "";
                $createdby = isset($params['createdby']) ? $params['createdby'] : "";

                // $sql = "SELECT count(*) as partnerdash_completedprofiles FROM fpa_taskdetails as ta, fp_borrower_user_details as bu
                // WHERE bu.profilecomplete ='complete'  ";

                $sql = "SELECT COUNT(*) as partnerdash_completedprofiles FROM fpa_users,fp_borrower_user_details WHERE fpa_users.id=fp_borrower_user_details.user_id and  fp_borrower_user_details.profilecomplete='completed' and fp_borrower_user_details.profilecomplete_percentage=100 and fpa_users.partner_id='$where' and created_by = '$createdby' ";

                $resp = array('status' => 200, 'message' => 'Success', 'data' => $this->db->query($sql)->result());
                return json_output($respStatus, $resp);
            } else {
                return json_output(400, array('status' => 400, 'message' => $checkToken));
            }
        } else {
            return json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        }
    }

    public function partnersubmittedby_cc()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method == "POST") {
            $checkToken = $this->check_token();
            if (true) {
                $response['status'] = 200;
                $respStatus = $response['status'];
                $params = json_decode(file_get_contents('php://input'), true);

                $where = isset($params['where']) ? $params['where'] : "";
                $createdby = isset($params['createdby']) ? $params['createdby'] : "";

                $sql = "SELECT COUNT(*) as CC_Pending FROM fpa_users,fp_borrower_loanrequests  WHERE fpa_users.id= fp_borrower_loanrequests.borrower_id and fp_borrower_loanrequests.loan_request_status='CC Approval Pending' and fpa_users.partner_id='$where'";

                $resp = array('status' => 200, 'message' => 'Success', 'data' => $this->db->query($sql)->result());
                return json_output($respStatus, $resp);
            } else {
                return json_output(400, array('status' => 400, 'message' => $checkToken));
            }

        } else {
            return json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        }

    }

    public function partnerdeals_sanctioned()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method == "POST") {
            $checkToken = $this->check_token();
            if (true) {
                $response['status'] = 200;
                $respStatus = $response['status'];
                $params = json_decode(file_get_contents('php://input'), true);

                $where = isset($params['where']) ? $params['where'] : "";
                $createdby = isset($params['createdby']) ? $params['createdby'] : "";

                // $sql = "SELECT COUNT(*) sanctioned FROM fpa_taskdetails td, fpa_adminusers ad, fpa_loan_applications la  WHERE td.task_assigned_to=ad.id AND ad.role_slug='rm' AND la.borrower_id=td.borrower_id and la.workflow_status='Deals Sanctioned'";

                $sql = "SELECT COUNT(*) as sanctioned FROM fpa_users,fpa_loan_applications WHERE fpa_users.id=fpa_loan_applications.borrower_id and fpa_loan_applications.loanapplication_status='Deal Sanctioned' and fpa_users.partner_id='$where'";
                $resp = array('status' => 200, 'message' => 'Success', 'data' => $this->db->query($sql)->result());
                return json_output($respStatus, $resp);
            } else {
                return json_output(400, array('status' => 400, 'message' => $checkToken));
            }

        } else {
            return json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        }

    }

    public function partnerdue_diligence()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method == "POST") {
            $checkToken = $this->check_token();
            if (true) {
                $response['status'] = 200;
                $respStatus = $response['status'];
                $params = json_decode(file_get_contents('php://input'), true);

                $selectkey = isset($params['selectkey']) ? $params['selectkey'] : "*";
                $join = isset($params['key']) ? $params['key'] : "";
                $where = isset($params['where']) ? $params['where'] : "";

                // $sql = "SELECT COUNT(*) duediligence FROM fpa_taskdetails td, fpa_adminusers ad, fp_borrower_loanrequests la  WHERE td.task_assigned_to=ad.id AND ad.role_slug='rm' AND la.borrower_id=td.borrower_id and la.loan_request_status='Due Diligence'";

                $sql = "SELECT COUNT(*) as duediligence FROM fpa_users ,fp_borrower_loanrequests WHERE fpa_users.id=fp_borrower_loanrequests.borrower_id and fp_borrower_loanrequests.loan_request_status='Due Diligence' and fpa_users.partner_id= '$where'";
                $resp = array('status' => 200, 'message' => 'Success', 'data' => $this->db->query($sql)->result());
                return json_output($respStatus, $resp);
            } else {
                return json_output(400, array('status' => 400, 'message' => $checkToken));
            }

        } else {
            return json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        }

    }

    public function partnerapproved_cc()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method == "POST") {
            $checkToken = $this->check_token();
            if (true) {
                $response['status'] = 200;
                $respStatus = $response['status'];
                $params = json_decode(file_get_contents('php://input'), true);

                $selectkey = isset($params['selectkey']) ? $params['selectkey'] : "*";
                $join = isset($params['key']) ? $params['key'] : "";
                $where = isset($params['where']) ? $params['where'] : "";

                $sql = "SELECT COUNT(*) as cc_approved FROM fpa_users,fp_borrower_loanrequests  WHERE fpa_users.id= fp_borrower_loanrequests.borrower_id and fp_borrower_loanrequests.loan_request_status='CC Approved' and fpa_users.partner_id= '$where'";

                $resp = array('status' => 200, 'message' => 'Success', 'data' => $this->db->query($sql)->result());
                return json_output($respStatus, $resp);
            } else {
                return json_output(400, array('status' => 400, 'message' => $checkToken));
            }

        } else {
            return json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        }

    }

    public function partnerdealssend_lender()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method == "POST") {
            $checkToken = $this->check_token();
            if (true) {
                $response['status'] = 200;
                $respStatus = $response['status'];
                $params = json_decode(file_get_contents('php://input'), true);

                $selectkey = isset($params['selectkey']) ? $params['selectkey'] : "*";
                $join = isset($params['key']) ? $params['key'] : "";
                $where = isset($params['where']) ? $params['where'] : "";

                $sql = "SELECT COUNT(*) as deals_lender FROM fpa_loan_applications la WHERE la.loanapplication_status='Deal Sent To Lender' and  la.partner='$where'";
                $resp = array('status' => 200, 'message' => 'Success', 'data' => $this->db->query($sql)->result());
                return json_output($respStatus, $resp);
            } else {
                return json_output(400, array('status' => 400, 'message' => $checkToken));
            }

        } else {
            return json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        }

    }

    public function partner_borrower_user_detail()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method == "POST") {
            $checkToken = $this->check_token();
            if (true) {
                $response['status'] = 200;
                $respStatus = $response['status'];
                $params = json_decode(file_get_contents('php://input'), true);

                $selectkey = isset($params['selectkey']) ? $params['selectkey'] : "*";
                $join = isset($params['key']) ? $params['key'] : "";
                $where = isset($params['where']) ? $params['where'] : "";
                $partnerid = isset($params['partnerid']) ? $params['partnerid'] : "";

                $sql = "WITH borrowerTable as (SELECT b.slug, b.id, bd.company_industry, bd.company_name, bd.turnover, bd.networth, bd.company_type, bd.profilecomplete, b.partner_name, b.partner_id , bd.city, pa.email FROM fpa_users b, fp_borrower_user_details bd , fpa_partners pa WHERE b.slug ='borrower' AND b.status in ('new','assigned','active') AND b.id = bd.user_id AND bd.company_name is not null AND b.created_by=pa.email AND b.partner_id=pa.partner_id)
                SELECT bd.partner_id, bd.partner_name,bd.slug, bd.profilecomplete ,bd.city,fp_entitytype.id,bd.id as borrower_id, bd.email, bd.partner_id, fp_city.id as location_id, fp_city.name as location, fp_entitytype.name as entity_name,bd.company_name as company_name, bd.company_industry as company_industry,bd.turnover, bd.networth FROM borrowerTable as bd LEFT JOIN fp_city ON bd.city = fp_city.id LEFT JOIN fp_entitytype ON bd.company_type = fp_entitytype.id where bd.company_name is not null  and  bd.email='$where' AND bd.partner_id='$partnerid'";

                $borrowerdetails = $this->db->query($sql)->result();
                $data = $this->db->query($sql);
                foreach ($data->result() as $row) {
                    $txnArr[] = $row->borrower_id;

                }
                $res = implode(",", $txnArr);
                $res = "(" . $res . ")";

                $result = 'SELECT bl.product_slug,bl.borrower_id,p.name  FROM fp_borrower_loanrequests bl ,fp_products p WHERE bl.product_slug=p.slug and bl.borrower_id in ' . $res;

                $resp = array('status' => 200, 'message' => 'Success', 'data' => $borrowerdetails, 'data1' => $this->db->query($result)->result());
                return json_output($respStatus, $resp);
            } else {
                return json_output(400, array('status' => 400, 'message' => "UnAuthorized!"));
            }

        } else {
            return json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        }

    } //----------------------- partner_borrower_user_detail ---------------------

    public function partner_catotalborrowerleads()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method == "POST") {
            $checkToken = $this->check_token();
            if (true) {
                $response['status'] = 200;
                $respStatus = $response['status'];
                $params = json_decode(file_get_contents('php://input'), true);

                $selectkey = isset($params['selectkey']) ? $params['selectkey'] : "*";
                $join = isset($params['key']) ? $params['key'] : "";
                $where = isset($params['where']) ? $params['where'] : "";

                $sql = "WITH borrowerTable as
								(SELECT b.slug, b.id, bd.company_industry, bd.company_name, bd.turnover, bd.networth, bd.company_type, bd.profilecomplete, b.partner_name, b.partner_id, bd.city, pa.email FROM fpa_users b, fp_borrower_user_details bd , fpa_partners pa WHERE b.slug ='borrower' AND b.status in ('new','assigned','active') AND b.id = bd.user_id AND bd.company_name is not null AND b.created_by=pa.email)
								SELECT bd.partner_id, bd.partner_name,bd.slug, bd.profilecomplete ,bd.city,fp_entitytype.id,bd.id as borrower_id, bd.email, fp_city.id as location_id, fp_city.name as location, fp_entitytype.name as entity_name,bd.company_name as company_name, bd.company_industry as company_industry,bd.turnover, bd.networth FROM borrowerTable as bd LEFT JOIN fp_city ON bd.city = fp_city.id LEFT JOIN fp_entitytype ON bd.company_type = fp_entitytype.id where bd.company_name is not null  and  bd.email='$where'";

                $borrowerdetails = $this->db->query($sql)->result();
                $data = $this->db->query($sql);
                foreach ($data->result() as $row) {
                    $txnArr[] = $row->borrower_id;

                }
                $res = implode(",", $txnArr);
                $res = "(" . $res . ")";

                $result = 'SELECT bl.product_slug,bl.borrower_id,p.name  FROM fp_borrower_loanrequests bl ,fp_products p WHERE bl.product_slug=p.slug and bl.borrower_id in ' . $res;

                // $this->db->query($sql)-result();
                // $query = $this->db->get_where('fp_borrower_loanrequests', array('borrower_id' => $res))->result();
                // $trnn[]= $data->id;

                $resp = array('status' => 200, 'message' => 'Success', 'data' => $borrowerdetails, 'data1' => $this->db->query($result)->result());
                return json_output($respStatus, $resp);
            } else {
                return json_output(400, array('status' => 400, 'message' => "UnAuthorized!"));
            }

        } else {
            return json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        }

    } //----------------------- partner_catotalborrowerleads ---------------------

    public function partner_catotaldraftleads()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method == "POST") {
            $checkToken = $this->check_token();
            if (true) {
                $response['status'] = 200;
                $respStatus = $response['status'];
                $params = json_decode(file_get_contents('php://input'), true);

                $selectkey = isset($params['selectkey']) ? $params['selectkey'] : "*";
                $join = isset($params['key']) ? $params['key'] : "";
                $where = isset($params['where']) ? $params['where'] : "";

                $sql = "WITH connectorTable as
                (SELECT b.slug, b.id, bd.company_industry, bd.company_name, bd.turnover, bd.networth, bd.company_type, bd.profilecomplete, b.partner_name, b.partner_id, bd.city, pa.email FROM fpa_users b, fp_borrower_user_details bd , fpa_partners pa WHERE b.slug ='borrower' AND b.status in ('new','assigned','active') AND b.id = bd.user_id AND bd.profilecomplete ='incomplete' AND b.created_by=pa.email AND b.rm_id is null)SELECT bd.partner_id, bd.partner_name,bd.slug, bd.profilecomplete ,bd.city,fp_entitytype.id,bd.id as borrower_id, bd.email, fp_city.id as location_id, fp_city.name as location, fp_entitytype.name as entity_name,bd.company_name as company_name, bd.company_industry as company_industry,bd.turnover, bd.networth FROM connectorTable as bd LEFT JOIN fp_city ON bd.city = fp_city.id LEFT JOIN fp_entitytype ON bd.company_type = fp_entitytype.id where bd.company_name is not null  and  bd.email='$where'";

                $borrowerdetails = $this->db->query($sql)->result();
                $data = $this->db->query($sql);
                foreach ($data->result() as $row) {
                    $txnArr[] = $row->borrower_id;

                }
                $res = implode(",", $txnArr);
                $res = "(" . $res . ")";

                $result = 'SELECT bl.product_slug,bl.borrower_id,p.name  FROM fp_borrower_loanrequests bl ,fp_products p WHERE bl.product_slug=p.slug and bl.borrower_id in ' . $res;

                // $this->db->query($sql)-result();
                // $query = $this->db->get_where('fp_borrower_loanrequests', array('borrower_id' => $res))->result();
                // $trnn[]= $data->id;

                $resp = array('status' => 200, 'message' => 'Success', 'data' => $borrowerdetails, 'data1' => $this->db->query($result)->result());
                return json_output($respStatus, $resp);
            } else {
                return json_output(400, array('status' => 400, 'message' => "UnAuthorized!"));
            }

        } else {
            return json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        }

    }
    //----------------------- partner_catotaldraftleads ---------------------
    public function partner_catotaleligibleleads()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method == "POST") {
            $checkToken = $this->check_token();
            if (true) {
                $response['status'] = 200;
                $respStatus = $response['status'];
                $params = json_decode(file_get_contents('php://input'), true);

                $selectkey = isset($params['selectkey']) ? $params['selectkey'] : "*";
                $join = isset($params['key']) ? $params['key'] : "";
                $where = isset($params['where']) ? $params['where'] : "";

                $sql = "WITH borrowerTable as
                (SELECT b.slug, b.id, bd.company_industry, bd.company_name, bd.turnover, bd.networth, bd.company_type, bd.profilecomplete, b.partner_name, b.partner_id, bd.city, pa.email FROM fpa_users b, fp_borrower_user_details bd , fpa_partners pa WHERE b.slug ='borrower' AND b.status in ('new','assigned','active') AND b.id = bd.user_id AND bd.profilecomplete='completed' AND bd.profilecomplete_percentage= 100 AND b.created_by=pa.email)
                SELECT bd.partner_id, bd.partner_name,bd.slug, bd.profilecomplete ,bd.city,fp_entitytype.id,bd.id as borrower_id, bd.email, fp_city.id as location_id, fp_city.name as location, fp_entitytype.name as entity_name,bd.company_name as company_name, bd.company_industry as company_industry,bd.turnover, bd.networth FROM borrowerTable as bd LEFT JOIN fp_city ON bd.city = fp_city.id LEFT JOIN fp_entitytype ON bd.company_type = fp_entitytype.id where bd.company_name is not null  and  bd.email='$where'";

                $borrowerdetails = $this->db->query($sql)->result();
                print_r ($borrowerdetails);
                $data = $this->db->query($sql);
                $txnArr=[];
                foreach ($data->result() as $row) {
                    $txnArr[] = $row->borrower_id;

                }
                if(empty($txnArr)){
                    return json_output(200, array('status' =>201, 'message'=>"NoData"));

                }
                else{
                $res = implode(",", $txnArr);
                $res = "(" . $res . ")";

                $result = 'SELECT bl.product_slug,bl.borrower_id,p.name  FROM fp_borrower_loanrequests bl ,fp_products p WHERE bl.product_slug=p.slug and bl.borrower_id in ' . $res;

                // $this->db->query($sql)-result();
                // $query = $this->db->get_where('fp_borrower_loanrequests', array('borrower_id' => $res))->result();
                // $trnn[]= $data->id;

                $resp = array('status' => 200, 'message' => 'Success', 'data' => $borrowerdetails, 'data1' => $this->db->query($result)->result());
                return json_output($respStatus, $resp);
            }
            } else {
                return json_output(400, array('status' => 400, 'message'=>"UnAuthorized!"));
            }

        } else {
            return json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        }

    } //----------------------- partner_catotaleligibleleads ---------------------

    public function partner_catotalassignedleads()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method == "POST") {
            $checkToken = $this->check_token();
            if (true) {
                $response['status'] = 200;
                $respStatus = $response['status'];
                $params = json_decode(file_get_contents('php://input'), true);

                $selectkey = isset($params['selectkey']) ? $params['selectkey'] : "*";
                $join = isset($params['key']) ? $params['key'] : "";
                $where = isset($params['where']) ? $params['where'] : "";

                $sql = "WITH borrowerTable as
                (SELECT b.slug, b.id, bd.company_industry, bd.company_name, bd.turnover, bd.networth, bd.company_type, bd.profilecomplete, b.partner_name, b.partner_id, bd.city, pa.email FROM fpa_users b, fp_borrower_user_details bd , fpa_partners pa , fp_borrower_loanrequests bl WHERE b.slug ='borrower' AND b.status in ('new','assigned','active') AND b.id = bd.user_id AND b.created_by=pa.email AND bl.borrower_id=b.id AND bl.loan_request_status='Due Diligence')
                SELECT bd.partner_id, bd.partner_name,bd.slug, bd.profilecomplete ,bd.city,fp_entitytype.id,bd.id as borrower_id, bd.email, fp_city.id as location_id, fp_city.name as location, fp_entitytype.name as entity_name,bd.company_name as company_name, bd.company_industry as company_industry,bd.turnover, bd.networth FROM borrowerTable as bd LEFT JOIN fp_city ON bd.city = fp_city.id LEFT JOIN fp_entitytype ON bd.company_type = fp_entitytype.id where bd.company_name is not null  and  bd.email='$where'";

                $borrowerdetails = $this->db->query($sql)->result();
                $data = $this->db->query($sql);
                foreach ($data->result() as $row) {
                    $txnArr[] = $row->borrower_id;

                }
                if(empty($txnArr)){
                    return json_output(200, array('status' =>201, 'message'=>"NoData"));

                }
                else{
                $res = implode(",", $txnArr);
                $res = "(" . $res . ")";

                $result = 'SELECT bl.product_slug,bl.borrower_id,p.name  FROM fp_borrower_loanrequests bl ,fp_products p WHERE bl.product_slug=p.slug and bl.borrower_id in ' . $res;

                // $this->db->query($sql)-result();
                // $query = $this->db->get_where('fp_borrower_loanrequests', array('borrower_id' => $res))->result();
                // $trnn[]= $data->id;

                $resp = array('status' => 200, 'message' => 'Success', 'data' => $borrowerdetails, 'data1' => $this->db->query($result)->result());
                return json_output($respStatus, $resp);
            }
            } else {
                return json_output(400, array('status' => 400, 'message' => "UnAuthorized!"));
            }

        } else {
            return json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        }

    } //----------------------- partner_catotalassignedleads ---------------------

    public function partner_catotalapprovedprofiles()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method == "POST") {
            $checkToken = $this->check_token();
            if (true) {
                $response['status'] = 200;
                $respStatus = $response['status'];
                $params = json_decode(file_get_contents('php://input'), true);

                $selectkey = isset($params['selectkey']) ? $params['selectkey'] : "*";
                $join = isset($params['key']) ? $params['key'] : "";
                $where = isset($params['where']) ? $params['where'] : "";

                $sql = "WITH borrowerTable as
                (SELECT b.slug, b.id, bd.company_industry, bd.company_name, bd.turnover, bd.networth, bd.company_type, bd.profilecomplete, b.partner_name, b.partner_id, bd.city, pa.email FROM fpa_users b, fp_borrower_user_details bd , fpa_partners pa , fp_borrower_loanrequests bl WHERE b.slug ='borrower' AND b.status in ('new','assigned','active') AND b.id = bd.user_id AND b.created_by=pa.email AND bl.borrower_id=b.id AND bl.loan_request_status='CC Approved')
                SELECT bd.partner_id, bd.partner_name,bd.slug, bd.profilecomplete ,bd.city,fp_entitytype.id,bd.id as borrower_id, bd.email, fp_city.id as location_id, fp_city.name as location, fp_entitytype.name as entity_name,bd.company_name as company_name, bd.company_industry as company_industry,bd.turnover, bd.networth FROM borrowerTable as bd LEFT JOIN fp_city ON bd.city = fp_city.id LEFT JOIN fp_entitytype ON bd.company_type = fp_entitytype.id where bd.company_name is not null  and  bd.emails='$where'";

                $borrowerdetails = $this->db->query($sql)->result();
                $data = $this->db->query($sql);
                foreach ($data->result() as $row) {
                    $txnArr[] = $row->borrower_id;

                }
                $res = implode(",", $txnArr);
                $res = "(" . $res . ")";

                $result = 'SELECT bl.product_slug,bl.borrower_id,p.name  FROM fp_borrower_loanrequests bl ,fp_products p WHERE bl.product_slug=p.slug and bl.borrower_id in ' . $res;

                // $this->db->query($sql)-result();
                // $query = $this->db->get_where('fp_borrower_loanrequests', array('borrower_id' => $res))->result();
                // $trnn[]= $data->id;

                $resp = array('status' => 200, 'message' => 'Success', 'data' => $borrowerdetails, 'data1' => $this->db->query($result)->result());
                return json_output($respStatus, $resp);
            } else {
                return json_output(400, array('status' => 400, 'message' => "UnAuthorized!"));
            }

        } else {
            return json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        }

    } //----------------------- partner_catotalapprovedprofiles ---------------------


    

// -----------------connector Superadmin API------------------------
// connector superadmin dashboard summarycard end-----------------------


public function partner_totalborrowerleads()
{
    $method = $_SERVER['REQUEST_METHOD'];
    if ($method == "POST") {
        $checkToken = $this->check_token();
        if (true) {
            $response['status'] = 200;
            $respStatus = $response['status'];
            $params = json_decode(file_get_contents('php://input'), true);

            $selectkey = isset($params['selectkey']) ? $params['selectkey'] : "*";
            $join = isset($params['key']) ? $params['key'] : "";
            $where = isset($params['where']) ? $params['where'] : "";
                            $partner_id = isset($params['partner_id']) ? $params['partner_id'] : "";

            $sql = "WITH borrowerTable as
            (SELECT b.slug, b.id, bd.company_industry, bd.company_name, bd.turnover, bd.networth, bd.company_type, bd.profilecomplete, b.partner_name, b.partner_id as pid, bd.city, pa.email as partemail,pa.company_name as partnercompany FROM fpa_users b, fp_borrower_user_details bd , fpa_partners pa WHERE b.slug ='borrower' AND b.status in ('new','assigned','active') AND b.id = bd.user_id AND bd.company_name is not null AND b.partner_name is NOT null AND b.created_by=pa.email)
            SELECT bd.pid, bd.partner_name,bd.slug, bd.profilecomplete ,bd.city,fp_entitytype.id,bd.id as borrower_id, bd.partemail,bd.partnercompany,bd.pid, fp_city.id as location_id, fp_city.name as location, fp_entitytype.name as entity_name,bd.company_name as company_name, bd.company_industry as company_industry,bd.turnover, bd.networth FROM borrowerTable as bd LEFT JOIN fp_city ON bd.city = fp_city.id LEFT JOIN fp_entitytype ON bd.company_type = fp_entitytype.id where bd.company_name is not null  and  bd.partemail='$where' OR pid ='$partner_id'";

            $borrowerdetails = $this->db->query($sql)->result();
            $data = $this->db->query($sql);
			$$txnArr = [];
			$result1 = "";
            foreach ($data->result() as $row) {
                $txnArr[] = $row->borrower_id;

            }
			if(!empty($txnArr)){

		
            $res = implode(",", $txnArr);
            $res = "(" . $res . ")";

            $result = 'SELECT bl.product_slug,bl.borrower_id,p.name  FROM fp_borrower_loanrequests bl ,fp_products p WHERE bl.product_slug=p.slug and bl.borrower_id in ' . $res;
			$result1 = $this->db->query($result)->result();
		}

			

            // $this->db->query($sql)-result();
            // $query = $this->db->get_where('fp_borrower_loanrequests', array('borrower_id' => $res))->result();
            // $trnn[]= $data->id;

            $resp = array('status' => 200, 'message' => 'Success', 'data' => $borrowerdetails, 'data1' => $result1);
            return json_output($respStatus, $resp);
        } else {
            return json_output(400, array('status' => 400, 'message' => $checkToken));
        }

    } else {
        return json_output(400, array('status' => 400, 'message' => 'Bad request.'));
    }

} //----------------------- partner_totalborrowerleads ---------------------

public function partner_totaldraftleads()
{
    $method = $_SERVER['REQUEST_METHOD'];
    if ($method == "POST") {
        $checkToken = $this->check_token();
        if (true) {
            $response['status'] = 200;
            $respStatus = $response['status'];
            $params = json_decode(file_get_contents('php://input'), true);

            $selectkey = isset($params['selectkey']) ? $params['selectkey'] : "*";
            $join = isset($params['key']) ? $params['key'] : "";
            $where = isset($params['where']) ? $params['where'] : "";

            $sql = " WITH connectorTable as
            (SELECT b.slug, b.id, bd.company_industry, bd.company_name, bd.turnover, bd.networth, bd.company_type, bd.profilecomplete, b.partner_name,b.partner_id as pid, bd.city, pa.email as partemail,pa.company_name as partnercompany FROM fpa_users b, fp_borrower_user_details bd , fpa_partners pa WHERE b.slug ='borrower' AND b.status in ('new','assigned','active') AND b.id = bd.user_id AND b.created_by=pa.email AND bd.gst is null AND bd.pan is null AND bd.pincode is null AND bd.profilecomplete ='incomplete' AND b.rm_id is null)
            SELECT bd.pid, bd.partner_name,bd.slug, bd.profilecomplete ,bd.city,fp_entitytype.id,bd.id as borrower_id, bd.partemail,bd.partnercompany,bd.pid, fp_city.id as location_id, fp_city.name as location, fp_entitytype.name as entity_name,bd.company_name as company_name, bd.company_industry as company_industry,bd.turnover, bd.networth FROM connectorTable as bd LEFT JOIN fp_city ON bd.city = fp_city.id LEFT JOIN fp_entitytype ON bd.company_type = fp_entitytype.id where bd.company_name is not null  and  bd.partemail='$where'";

            $borrowerdetails = $this->db->query($sql)->result();
            $data = $this->db->query($sql);
            foreach ($data->result() as $row) {
                $txnArr[] = $row->borrower_id;

            }
            $res = implode(",", $txnArr);
            $res = "(" . $res . ")";

            $result = 'SELECT bl.product_slug,bl.borrower_id,p.name  FROM fp_borrower_loanrequests bl ,fp_products p WHERE bl.product_slug=p.slug and bl.borrower_id in ' . $res;

            // $this->db->query($sql)-result();
            // $query = $this->db->get_where('fp_borrower_loanrequests', array('borrower_id' => $res))->result();
            // $trnn[]= $data->id;

            $resp = array('status' => 200, 'message' => 'Success', 'data' => $borrowerdetails, 'data1' => $this->db->query($result)->result());
            return json_output($respStatus, $resp);
        } else {
            return json_output(400, array('status' => 400, 'message' => $checkToken));
        }

    } else {
        return json_output(400, array('status' => 400, 'message' => 'Bad request.'));
    }

}
//----------------------- partner_totaldraftleads ---------------------
public function partner_totaleligibleleads()
{
    $method = $_SERVER['REQUEST_METHOD'];
    if ($method == "POST") {
        $checkToken = $this->check_token();
        if (true) {
            $response['status'] = 200;
            $respStatus = $response['status'];
            $params = json_decode(file_get_contents('php://input'), true);

            $selectkey = isset($params['selectkey']) ? $params['selectkey'] : "*";
            $join = isset($params['key']) ? $params['key'] : "";
            $where = isset($params['where']) ? $params['where'] : "";

            $sql = "WITH connectorTable as
            (SELECT b.slug, b.id, bd.company_industry, bd.company_name, bd.turnover, bd.networth, bd.company_type, bd.profilecomplete, b.partner_name, b.partner_id as pid, bd.city,bd.gst is null AND bd.pan is null AND bd.pincode is null AND bd.rm_id is null AND pa.email as partemail,pa.company_name as partnercompany FROM fpa_users b, fp_borrower_user_details bd , fpa_partners pa WHERE b.slug ='borrower' AND b.status in ('new','assigned','active') AND b.id = bd.user_id AND  b.created_by=pa.email AND bd.profilecomplete ='completed'  AND bd.profilecomplete_percentage=100 AND b.rm_id is null)
            SELECT bd.pid, bd.partner_name,bd.slug, bd.profilecomplete ,bd.city,fp_entitytype.id,bd.id as borrower_id,bd.partemail,bd.partnercompany, fp_city.id as location_id, fp_city.name as location, fp_entitytype.name as entity_name,bd.company_name as company_name, bd.company_industry as company_industry,bd.turnover, bd.networth FROM connectorTable as bd LEFT JOIN fp_city ON bd.city = fp_city.id LEFT JOIN fp_entitytype ON bd.company_type = fp_entitytype.id where bd.company_name is not null  and  bd.partemail='$where'";

            $borrowerdetails = $this->db->query($sql)->result();
            $data = $this->db->query($sql);
            foreach ($data->result() as $row) {
                $txnArr[] = $row->borrower_id;

            }
            $res = implode(",", $txnArr);
            $res = "(" . $res . ")";

            $result = 'SELECT bl.product_slug,bl.borrower_id,p.name  FROM fp_borrower_loanrequests bl ,fp_products p WHERE bl.product_slug=p.slug and bl.borrower_id in ' . $res;

            // $this->db->query($sql)-result();
            // $query = $this->db->get_where('fp_borrower_loanrequests', array('borrower_id' => $res))->result();
            // $trnn[]= $data->id;

            $resp = array('status' => 200, 'message' => 'Success', 'data' => $borrowerdetails, 'data1' => $this->db->query($result)->result());
            return json_output($respStatus, $resp);
        } else {
            return json_output(400, array('status' => 400, 'message' => $checkToken));
        }

    } else {
        return json_output(400, array('status' => 400, 'message' => 'Bad request.'));
    }

} //----------------------- partner_totaleligibleleads ---------------------

public function partner_totalassignedleads()
{
    $method = $_SERVER['REQUEST_METHOD'];
    if ($method == "POST") {
        $checkToken = $this->check_token();
        if (true) {
            $response['status'] = 200;
            $respStatus = $response['status'];
            $params = json_decode(file_get_contents('php://input'), true);

            $selectkey = isset($params['selectkey']) ? $params['selectkey'] : "*";
            $join = isset($params['key']) ? $params['key'] : "";
            $where = isset($params['where']) ? $params['where'] : "";

            $sql = "WITH borrowerTable as
            (SELECT b.slug, b.id, bd.company_industry, bd.company_name, bd.turnover, bd.networth, bd.company_type, bd.profilecomplete, b.partner_name, b.partner_id as pid, bd.city, pa.email as partemail,pa.company_name as partnercompany FROM fpa_users b, fp_borrower_user_details bd , fpa_partners pa WHERE b.slug ='borrower' AND b.status='assigned' AND b.id = bd.user_id AND bd.gst is not null AND bd.pan is not null AND bd.pincode is not null AND bd.rm_id is not null   b.rm_id is not null and b.created_by=pa.email)
            SELECT bd.pid, bd.partner_name,bd.slug, bd.profilecomplete ,bd.city,fp_entitytype.id,bd.id as borrower_id, bd.partemail,bd.partnercompany, fp_city.id as location_id, fp_city.name as location, fp_entitytype.name as entity_name,bd.company_name as company_name, bd.company_industry as company_industry,bd.turnover, bd.networth FROM borrowerTable as bd LEFT JOIN fp_city ON bd.city = fp_city.id LEFT JOIN fp_entitytype ON bd.company_type = fp_entitytype.id where bd.company_name is not null  and  bd.partemail='$where'";

            $borrowerdetails = $this->db->query($sql)->result();
            $data = $this->db->query($sql);
            foreach ($data->result() as $row) {
                $txnArr[] = $row->borrower_id;

            }
            $res = implode(",", $txnArr);
            $res = "(" . $res . ")";

            $result = 'SELECT bl.product_slug,bl.borrower_id,p.name  FROM fp_borrower_loanrequests bl ,fp_products p WHERE bl.product_slug=p.slug and bl.borrower_id in ' . $res;

            // $this->db->query($sql)-result();
            // $query = $this->db->get_where('fp_borrower_loanrequests', array('borrower_id' => $res))->result();
            // $trnn[]= $data->id;

            $resp = array('status' => 200, 'message' => 'Success', 'data' => $borrowerdetails, 'data1' => $this->db->query($result)->result());
            return json_output($respStatus, $resp);
        } else {
            return json_output(400, array('status' => 400, 'message' => $checkToken));
        }

    } else {
        return json_output(400, array('status' => 400, 'message' => 'Bad request.'));
    }

} //----------------------- partner_totalassignedleads ---------------------

public function partner_totalapprovedprofiles()
{
    $method = $_SERVER['REQUEST_METHOD'];
    if ($method == "POST") {
        $checkToken = $this->check_token();
        if (true) {
            $response['status'] = 200;
            $respStatus = $response['status'];
            $params = json_decode(file_get_contents('php://input'), true);

            $selectkey = isset($params['selectkey']) ? $params['selectkey'] : "*";
            $join = isset($params['key']) ? $params['key'] : "";
            $where = isset($params['where']) ? $params['where'] : "";

            $sql = "WITH borrowerTable as
            (SELECT b.slug, b.id, bd.company_industry, bd.company_name, bd.turnover, bd.networth, bd.company_type, bd.profilecomplete, b.partner_name, b.partner_id as pid, bd.city, pa.email as partemail,pa.company_name as partnercompany FROM fpa_users b, fp_borrower_user_details bd , fpa_partners pa ,fp_borrower_loanrequests bl WHERE b.slug ='borrower' AND b.id= bl.borrower_id AND b.status in ('new','assigned','active') AND b.id = bd.user_id AND  b.created_by=pa.email AND bl.loan_request_status='CC Approved')
            SELECT bd.pid, bd.partner_name,bd.slug, bd.profilecomplete ,bd.city,fp_entitytype.id,bd.id as borrower_id,bd.partemail,bd.partnercompany, fp_city.id as location_id, fp_city.name as location, fp_entitytype.name as entity_name,bd.company_name as company_name, bd.company_industry as company_industry,bd.turnover, bd.networth FROM borrowerTable as bd LEFT JOIN fp_city ON bd.city = fp_city.id LEFT JOIN fp_entitytype ON bd.company_type = fp_entitytype.id where bd.company_name is not null  and  bd.partemail='$where'";

            $borrowerdetails = $this->db->query($sql)->result();
            $data = $this->db->query($sql);
            foreach ($data->result() as $row) {
                $txnArr[] = $row->borrower_id;

            }
            $res = implode(",", $txnArr);
            $res = "(" . $res . ")";

            $result = 'SELECT bl.product_slug,bl.borrower_id,p.name  FROM fp_borrower_loanrequests bl ,fp_products p WHERE bl.product_slug=p.slug and bl.borrower_id in ' . $res;

            // $this->db->query($sql)-result();
            // $query = $this->db->get_where('fp_borrower_loanrequests', array('borrower_id' => $res))->result();
            // $trnn[]= $data->id;

            $resp = array('status' => 200, 'message' => 'Success', 'data' => $borrowerdetails, 'data1' => $this->db->query($result)->result());
            return json_output($respStatus, $resp);
        } else {
            return json_output(400, array('status' => 400, 'message' => $checkToken));
        }

    } else {
        return json_output(400, array('status' => 400, 'message' => 'Bad request.'));
    }

} //----------------------- partner_totalapprovedprofiles ---------------------
 

} //--------------------end of class-------------------------------------------
