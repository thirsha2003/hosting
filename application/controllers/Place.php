<?php
header('Access-Control-Allow-Origin: *'); //for allow any domain, insecure
header('Access-Control-Allow-Headers: *'); //for allow any headers, insecure
header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE'); //method allowed
header("HTTP/1.1 200 OK");

defined('BASEPATH') or exit('No direct script access allowed');

Class Place extends CI_Controller{


    public function __construct()
    {
        parent::__construct();
        $this->load->helper('json_output');

        $this->ci = &get_instance();
        $this->ci->load->database();
    }

    


public function getdistrict()
{
  $method = $_SERVER['REQUEST_METHOD'];
  if($method == 'POST')
  {
    
    $response['status']=200;
    $respStatus = $response['status'];
    $params = json_decode(file_get_contents('php://input'), TRUE);
      
        $selectkey   = isset($params['selectkey']) ? $params['selectkey'] : "*"; 
        $join     = isset($params['key']) ? $params['key'] : "";
        $where = isset($params['where']) ? $params['where'] : "";
    
          $sql = "SELECT * FROM fp_district where is_active =1 ORDER BY id DESC";
        
        
        $resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql)->result());
        json_output($respStatus,$resp);
    
  }
  else
  {
         json_output(400,array('status' => 400,'message' => 'Bad request.'));
  }
}

public function edit_district()
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

                $sql = "SELECT * FROM fp_district WHERE id='$where'";

                $resp = array('status' => 200, 'message' => 'Success', 'data' => $this->db->query($sql)->result());
                return json_output($respStatus, $resp);
            } else {
                return json_output(400, array('status' => 400, 'message' => "Unauthorized"));
            }

        } else {
            return json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        }

}

public function add_district()
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

                    $name = isset($params['data']['name']) ? $params['data']['name'] : null;
                    $state_slug = isset($params['data']['state_slug']) ? $params['data']['state_slug'] : null;

                    $state_id = isset($params['data']['state_id']) ? $params['data']['state_id'] : null;
                    $id = isset($params['data']['id']) ? $params['data']['id'] : null;

                    if(isset($params['data']['id'])){
                      
                    $district_dub_check = "SELECT id FROM " . $params['tableName'] . " WHERE id = " .$params['data']['id'];
  
                    }else{
                        $district_dub_check = "SELECT id FROM " . $params['tableName'] . " WHERE name = '" . $name . "'";
                    }

                   

                    

                    $state_id = "SELECT id FROM fp_state WHERE state_slug = '" . $state_slug . "'";
                    $stateid = $this->db->query($state_id)->result();


                    $inarr = array('name' => $name, 'state_slug' => $state_slug, 'state_id' => $stateid[0]->id, 'district_slug' => $name);

                    // print_r(count($this->db->query($district_dub_check)->result()));

                    if (count($this->db->query($district_dub_check)->result()) == 0) {
                        $this->db->insert($params['tableName'], $inarr);
                    $resp = array('status' => 200, 'message' => 'success', 'data' => $this->db->insert_id());


                    } else {

                        foreach($this->db->query($district_dub_check)->result() as $row ){
                            $district_id_dub = $row->id;
                        }

                        // print_r($district_id_dub);
                        
                        if(isset($params['data']['id']) && $district_id_dub ==  $params['data']['id']){
                            $this->db->where('id', $params['data']['id']);
                            $this->db->update($params['tableName'], $inarr);
                    $resp = array('status' => 200, 'message' => 'success');

                        }else{
                            // json_output(array('status'=>200))
                    $resp = array('status' => 201, 'message' => 'Duplicate Entry Not Allowed');
                            
                        }
                      

                    }
                }
                json_output($respStatus, $resp);
            }
           
        }
}
    

public function deletedistrict()
{ 
        $method = $_SERVER['REQUEST_METHOD'];
        if($method =="POST")
        {
                // $checkToken = $this->check_token();
                if(True)
                {
                        $response['status']=200;
                        $respStatus = $response['status'];
                        $params 	= json_decode(file_get_contents('php://input'), TRUE);

                        $selectkey 	= isset($params['selectkey']) ? $params['selectkey'] : "*"; 
                        $join 		= isset($params['key']) ? $params['key'] : "";
                        $where 		= isset($params['where']) ? $params['where'] : "";	

                        $sql = "update fp_district set is_active='0' where id='$where'";

                        $resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql));
                       
                        // return json_output($respStatus,$resp);
                        return json_output(200,array('status' => 200,'message' => "Deleted Successfully"));
                }
                else
                {
                    return json_output(400,array('status' => 400,'message' => "Unauthorized"));
                }
            
        }
        else
        {
                return json_output(400,array('status' => 400,'message' => 'Bad request.'));
        }
    
}



public function stateid_search()
{
  $method = $_SERVER['REQUEST_METHOD'];
  if($method == 'POST')
  {
    
    $response['status']=200;
    $respStatus = $response['status'];
    $params = json_decode(file_get_contents('php://input'), TRUE);
      
        $selectkey   = isset($params['selectkey']) ? $params['selectkey'] : "*"; 
        $join     = isset($params['key']) ? $params['key'] : "";
        $where = isset($params['where']) ? $params['where'] : "";
    
          $sql = "SELECT st.id, st.name FROM fp_state st WHERE st.name=".$where;
        
        
        $resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql)->result());
        json_output($respStatus,$resp);
    
  }
  else
  {
         json_output(400,array('status' => 400,'message' => 'Bad request.'));
  }
}


public function getlendermaster()
{
  $method = $_SERVER['REQUEST_METHOD'];
  if($method == 'POST')
  {
    
    $response['status']=200;
    $respStatus = $response['status'];
    $params = json_decode(file_get_contents('php://input'), TRUE);
      
        $selectkey   = isset($params['selectkey']) ? $params['selectkey'] : "*"; 
        $join     = isset($params['key']) ? $params['key'] : "";
        $where = isset($params['where']) ? $params['where'] : "";
    
          $sql = "SELECT lm.id, lm.slug as lenderslug, lm.lender_name, lm.lender_type, lm.hq_address, lm.hq_contact, lm.hq_email,lm.is_active, fin.id as banktypeid, fin.name banktypename, fin.slug FROM fp_lender_master lm LEFT JOIN fp_fin_institution fin ON fin.id=lm.lender_type WHERE lm.is_active=1";
        
        
        $resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql)->result());
        json_output($respStatus,$resp);
    
  }
  else
  {
         json_output(400,array('status' => 400,'message' => 'Bad request.'));
  }
}


public function getdeletedlendermaster()
{
  $method = $_SERVER['REQUEST_METHOD'];
  if($method == 'POST')
  {
    
    $response['status']=200;
    $respStatus = $response['status'];
    $params = json_decode(file_get_contents('php://input'), TRUE);
      
        $selectkey   = isset($params['selectkey']) ? $params['selectkey'] : "*"; 
        $join     = isset($params['key']) ? $params['key'] : "";
        $where = isset($params['where']) ? $params['where'] : "";
    
          $sql = "SELECT lm.id, lm.slug as lenderslug, lm.lender_name, lm.lender_type, lm.hq_address, lm.hq_contact, lm.hq_email,lm.is_active, fin.id as banktypeid, fin.name banktypename, fin.slug FROM fp_lender_master lm LEFT JOIN fp_fin_institution fin ON fin.id=lm.lender_type WHERE lm.is_active=0";
        
        
        $resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql)->result());
        json_output($respStatus,$resp);
    
  }
  else
  {
         json_output(400,array('status' => 400,'message' => 'Bad request.'));
  }
}

public function lenderenable()
{
		$method = $_SERVER['REQUEST_METHOD'];
		if($method =="POST")
		{
				// $checkToken = $this->check_token();
				if(True)
				{
						$response['status']=200;
						$respStatus = $response['status'];
						$params 	= json_decode(file_get_contents('php://input'), TRUE);

						$selectkey 	= isset($params['selectkey']) ? $params['selectkey'] : "*"; 
						$join 		= isset($params['key']) ? $params['key'] : "";
						$where 		= isset($params['where']) ? $params['where'] : "";	

						$status = 1;
						$sql = "update fp_lender_master set is_active=".$status." where id=".$where;
						$resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql));
						return json_output($respStatus,$resp);
				}
				else
				{
					return json_output(400,array('status' => 400,'message' => "Unauthorized"));
				}
			
		}
		else
		{
				return json_output(400,array('status' => 400,'message' => 'Bad request.'));
		}
	
} 

 
public function add_lendermaster()
{
    $method = $_SERVER['REQUEST_METHOD'];
    if ($method == "POST") {
        // $checkToken = $this->check_token();
        if (true) {
            $response['status'] = 200;
            $respStatus = $response['status'];
            $params = json_decode(file_get_contents('php://input'), true);

            $hqaddress = isset($params["data"]["hq_address"])? $params["data"]["hq_address"] : null;
            $hqcontact = isset($params["data"]["hq_contact"])? $params["data"]["hq_contact"] : null;
            $hqemail = isset($params["data"]["hq_email"])? $params["data"]["hq_email"] : null;

           $lenderdata = array(
            "lender_name"=>$params['data']['lender_name'],
           "lender_type"=>$params["data"]["lender_type"],
           "hq_address"=>$hqaddress,
           "hq_contact"=>$hqcontact,
           "hq_email"=>$hqemail,
           "slug"=>$params["data"]["lenderslug"]);
     
        $this->db->insert("fp_lender_master",$lenderdata);
        return json_output(200, array('status' => 200, 'message' => 'Insert Successfully!'));
        } 
        else {
            return json_output(500, array('status' => 500, 'message' => "Duplicate Entry"));
        }

    } 
    else {
        return json_output(400, array('status' => 400, 'message' => 'Bad request.'));
    }

} 


public function update_lendermaster()
{
    $method = $_SERVER['REQUEST_METHOD'];
    if ($method == "POST") {
        // $checkToken = $this->check_token();
        if (true) {
            $response['status'] = 200;
            $respStatus = $response['status'];
            $params = json_decode(file_get_contents('php://input'), true);

            $where = isset($params['where']) ? $params['where'] : "";

            $hqaddress = isset($params["data"]["hq_address"])? $params["data"]["hq_address"] : null;
            $hqcontact = isset($params["data"]["hq_contact"])? $params["data"]["hq_contact"] : null;
            $hqemail = isset($params["data"]["hq_email"])? $params["data"]["hq_email"] : null;

           $lenderdata = array(
            "lender_name"=>$params['data']['lender_name'],
           "lender_type"=>$params["data"]["lender_type"],
           "hq_address"=>$hqaddress,
           "hq_contact"=>$hqcontact,
           "hq_email"=>$hqemail,
           "slug"=>$params["data"]["lenderslug"]);
     
           
           $this->db->where("id",$where);
        $this->db->update("fp_lender_master",$lenderdata);
        return json_output(200, array('status' => 200, 'message' => 'Updated Successfully!'));
        } 
        else {
            return json_output(500, array('status' => 500, 'message' => "Duplicate Entry"));
        }

    } 
    else {
        return json_output(400, array('status' => 400, 'message' => 'Bad request.'));
    }

} 

public function geteditlendermaster()
{
  $method = $_SERVER['REQUEST_METHOD'];
  if($method == 'POST')
  {
    
    $response['status']=200;
    $respStatus = $response['status'];
    $params = json_decode(file_get_contents('php://input'), TRUE);
      
        $selectkey   = isset($params['selectkey']) ? $params['selectkey'] : "*"; 
        $join     = isset($params['key']) ? $params['key'] : "";
        $where = isset($params['where']) ? $params['where'] : "";
    
          $sql = "SELECT lm.id, lm.slug as lenderslug, lm.lender_name, lm.lender_type, lm.hq_address, lm.hq_contact, lm.hq_email, lm.is_active,fin.id as banktypeid, fin.name banktypename, fin.slug FROM fp_lender_master lm LEFT JOIN fp_fin_institution fin ON fin.id=lm.lender_type where lm.id='$where'";
        
        
        $resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql)->result());
        json_output($respStatus,$resp);
    
  }
  else
  {
         json_output(400,array('status' => 400,'message' => 'Bad request.'));
  }
}


public function deletelendermaster()
{
        $method = $_SERVER['REQUEST_METHOD'];
        if($method =="POST")
        {
                // $checkToken = $this->check_token();
                if(True)
                {
                        $response['status']=200;
                        $respStatus = $response['status'];
                        $params 	= json_decode(file_get_contents('php://input'), TRUE);

                        $selectkey 	= isset($params['selectkey']) ? $params['selectkey'] : "*"; 
                        $join 		= isset($params['key']) ? $params['key'] : "";
                        $where 		= isset($params['where']) ? $params['where'] : "";	

                        $sql = "update fp_lender_master set is_active='0' where id='$where'";

                        $resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql));
                       
                        // return json_output($respStatus,$resp);
                        return json_output(200,array('status' => 200,'message' => "Deleted Successfully"));
                }
                else
                {
                    return json_output(400,array('status' => 400,'message' => "Unauthorized"));
                }
            
        }
        else
        {
                return json_output(400,array('status' => 400,'message' => 'Bad request.'));
        }
    
}




public function myproposalsnew()
{
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {

            $response['status'] = 200;
            $respStatus = $response['status'];

            $params = json_decode(file_get_contents('php://input'), true);

            $selectkey = isset($params['selectkey']) ? $params['selectkey'] : "*";
            $join = isset($params['key']) ? $params['key'] : "";
            $where = isset($params['where']) ? $params['where'] : "";

            // $sql = "SELECT fl.name as location,fl.id,fs.id as stateid,fs.name as state,fc.id,fc.name as city,fd.id as districtid,fd.name as district
            // FROM fp_state fs LEFT JOIN fp_district fd ON fd.state_id = fs.id LEFT JOIN fp_city fc ON fc.district_id = fd.id
            // LEFT JOIN fp_location fl ON fl.city_id = fc.id";

            $sql = "SELECT fs.id as stateid,fs.name as state,fc.id,fc.name as city,fd.id as districtid,fd.name as district FROM fp_state fs, fp_district fd, fp_city fc WHERE  fs.state_slug=fd.state_slug AND fd.district_slug=fc.district_slug AND fc.is_active=1 ORDER BY fc.created_at DESC";

            $resp = array('status' => 200, 'message' => 'Success', 'data' => $this->db->query($sql)->result());
            json_output($respStatus, $resp);

        }
}

public function getlocation()
{
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {

            $response['status'] = 200;
            $respStatus = $response['status'];

            $params = json_decode(file_get_contents('php://input'), true);

            $selectkey = isset($params['selectkey']) ? $params['selectkey'] : "*";
            $join = isset($params['key']) ? $params['key'] : "";
            $where = isset($params['where']) ? $params['where'] : "";

            $sql = "SELECT fs.id as stateid,fs.name as state,fc.id,fc.name as city,fd.id as districtid,fd.name as district, fl.pincode ,fl.location_slug as location,fl.name as name,fl.id as locationid
            FROM fp_location fl, fp_city fc , fp_district fd , fp_state fs
            WHERE fl.is_active=1 and fl.city_id=fc.id and fd.district_slug=fc.district_slug and fs.state_slug=fd.state_slug
            ORDER BY fl.created_at DESC";

            $resp = array('status' => 200, 'message' => 'Success', 'data' => $this->db->query($sql)->result());
            json_output($respStatus, $resp);

        }
}


public function edit_city()
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

                $sql = "SELECT fpc.id as city_id, fpc.name, fpc.city_slug, fpc.district_slug, fpc.district_id, fpd.id, fpd.name as districtname, fpd.district_slug,fpd.state_slug FROM fp_city fpc, fp_district fpd WHERE fpc.district_slug=fpd.district_slug AND fpc.id='$where'";

                $resp = array('status' => 200, 'message' => 'Success', 'data' => $this->db->query($sql)->result());
                return json_output($respStatus, $resp);
            } else {
                return json_output(400, array('status' => 400, 'message' => "Unauthorized"));
            }

        } else {
            return json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        }

}


public function addcity()
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

                    $district = isset($params['data']['district_slug']) ? $params['data']['district_slug'] : null;
                    $city = isset($params['data']['city_slug']) ? $params['data']['city_slug'] : null;

                    $id = isset($params['data']['id']) ? $params['data']['id'] : null;

                    $city_dub_check = "SELECT * FROM " . $params['tableName'] . " WHERE id = '" . $id . "'";

                    if (count($this->db->query($city_dub_check)->result()) == 0) {
                        $this->db->insert($params['tableName'], $params['data']);

                    } else {
                        $this->db->where('id', $params['data']['id']);
                        $this->db->update($params['tableName'], $params['data']);

                    }
                    $resp = array('status' => 200, 'message' => 'success', 'data' => $this->db->insert_id());
                }
                json_output($respStatus, $resp);
            }
           
        }
}


public function addlocation()
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

                    $name = isset($params['data']['district_slug']) ? $params['data']['district_slug'] : null;

                    $location_slug = isset($params['data']['location_slug']) ? $params['data']['location_slug'] : null;
                    $city = isset($params['data']['city_id']) ? $params['data']['city_id'] : null;
                    $pincode = isset($params['data']['pincode']) ? $params['data']['pincode'] : null;

                    $id = isset($params['data']['id']) ? $params['data']['id'] : null;

                    $city_id = isset($params['data']['city_id']) ? $params['data']['city_id'] : null;

                    $city_slug = "SELECT fc.city_slug  FROM fp_city as fc WHERE id = '" . $city_id . "'";

                    $cities_slug = $this->db->query($city_slug)->result();
                   

                    $city_dub_check = "SELECT * FROM " . $params['tableName'] . " WHERE id = '" . $id . "'";

                    $inarr = array('name' => $location_slug, 'city_slug' => $cities_slug[0]->city_slug, 'pincode' => $pincode, 'location_slug' => $location_slug, 'city_id' => $city_id);

                    if (count($this->db->query($city_dub_check)->result()) == 0) {
                        $this->db->insert($params['tableName'], $inarr);

                    } else {
                        $this->db->where('id', $params['data']['id']);
                        $this->db->update($params['tableName'], $inarr);

                    }
                    $resp = array('status' => 200, 'message' => 'success', 'data' => $this->db->insert_id());
                }
                json_output($respStatus, $resp);
            }

        }
}

public function edit_location()
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

                $sql = "SELECT
                fs.state_slug,
                fpc.id as city_id,
                fpc.name,
                fpc.city_slug,
                fpc.district_slug,
                fpc.district_id, fpd.id,
                fpd.name as districtname,
                fpd.district_slug,
                fpd.state_slug,
                fs.state_slug,
                fl.name,
               
                fl.location_slug,
                fl.pincode
                FROM fp_city fpc, fp_district fpd ,fp_state fs,fp_location fl
                WHERE fpc.district_slug=fpd.district_slug AND fl.city_slug = fpc.city_slug AND fs.state_slug = fpd.state_slug AND fl.id='$where'";

                $resp = array('status' => 200, 'message' => 'Success', 'data' => $this->db->query($sql)->result());
                return json_output($respStatus, $resp);
            } else {
                return json_output(400, array('status' => 400, 'message' => "Unauthorized"));
            }

        } else {
            return json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        }

}



public function getlenderlocation()
{
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {

            $response['status'] = 200;
            $respStatus = $response['status'];

            $params = json_decode(file_get_contents('php://input'), true);

            $selectkey = isset($params['selectkey']) ? $params['selectkey'] : "*";
            $join = isset($params['key']) ? $params['key'] : "";
            $where = isset($params['where']) ? $params['where'] : "";

            $sql = "SELECT fpl.id, fpl.lender_master_id, fpl.location_id, loc.name, lm.lender_name , loc.city_slug FROM fp_lender_location fpl, fp_location loc, fp_lender_master lm WHERE fpl.lender_master_id=lm.id AND fpl.location_id=loc.id AND fpl.is_active=1 ORDER BY id DESC";

            $resp = array('status' => 200, 'message' => 'Success', 'data' => $this->db->query($sql)->result());
            json_output($respStatus, $resp);

        }
}

public function edit_lenderlocation()
{
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {

            $response['status'] = 200;
            $respStatus = $response['status'];

            $params = json_decode(file_get_contents('php://input'), true);

            $selectkey = isset($params['selectkey']) ? $params['selectkey'] : "*";
            $join = isset($params['key']) ? $params['key'] : "";
            $where = isset($params['where']) ? $params['where'] : "";

            $sql = "SELECT fpl.id, fpl.lender_master_id, fpl.location_id, loc.name, lm.lender_name , loc.city_slug FROM fp_lender_location fpl, fp_location loc, fp_lender_master lm WHERE fpl.lender_master_id=lm.id AND fpl.location_id=loc.id AND fpl.id='$where'";

            $resp = array('status' => 200, 'message' => 'Success', 'data' => $this->db->query($sql)->result());
            json_output($respStatus, $resp);

        }
}

public function add_lendermaster_location()
{
    $method = $_SERVER['REQUEST_METHOD'];
    if ($method == "POST") {
        // $checkToken = $this->check_token();
        if (true) {
            $response['status'] = 200;
            $respStatus = $response['status'];
            $params = json_decode(file_get_contents('php://input'), true);


           $lenderlocation = array(
            "lender_master_id"=>$params['data']['lender_master_id'],
           "location_id"=>$params["data"]["location_id"]);
     
        $this->db->insert("fp_lender_location",$lenderlocation);
        return json_output(200, array('status' => 200, 'message' => 'Insert Successfully!'));
        } 
        else {
            return json_output(500, array('status' => 500, 'message' => "Duplicate Entry"));
        }

    } 
    else {
        return json_output(400, array('status' => 400, 'message' => 'Bad request.'));
    }

} 


public function update_lenderlocation()
{
    $method = $_SERVER['REQUEST_METHOD'];
    if ($method == "POST") {
        // $checkToken = $this->check_token();
        if (true) {
            $response['status'] = 200;
            $respStatus = $response['status'];
            $params = json_decode(file_get_contents('php://input'), true);
            
            // $where = isset($params['where']) ? $params['where'] : "";
            $id = isset($params['data']['id']) ? $params['data']['id'] : null;



           $lenderlocation = array(
            "lender_master_id"=>$params['data']['lender_master_id'],
           "location_id"=>$params["data"]["location_id"]);
     
           
           $this->db->where("id",$id);
        $this->db->update("fp_lender_location",$lenderlocation);
        return json_output(200, array('status' => 200, 'message' => 'Updated Successfully!'));
        } 
        else {
            return json_output(500, array('status' => 500, 'message' => "Duplicate Entry"));
        }

    } 
    else {
        return json_output(400, array('status' => 400, 'message' => 'Bad request.'));
    }
}


public function deletelenderlocation()
{
        $method = $_SERVER['REQUEST_METHOD'];
        if($method =="POST")
        {
                // $checkToken = $this->check_token();
                if(True)
                {
                        $response['status']=200;
                        $respStatus = $response['status'];
                        $params 	= json_decode(file_get_contents('php://input'), TRUE);

                        $selectkey 	= isset($params['selectkey']) ? $params['selectkey'] : "*"; 
                        $join 		= isset($params['key']) ? $params['key'] : "";
                        $where 		= isset($params['where']) ? $params['where'] : "";	

                        $sql = "update fp_lender_location set is_active='0' where id='$where'";

                        $resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql));
                       
                        // return json_output($respStatus,$resp);
                        return json_output(200,array('status' => 200,'message' => "Deleted Successfully"));
                }
                else
                {
                    return json_output(400,array('status' => 400,'message' => "Unauthorized"));
                }
            
        }
        else
        {
                return json_output(400,array('status' => 400,'message' => 'Bad request.'));
        }
    
}


public function getlenderproduct()
{
    $method = $_SERVER['REQUEST_METHOD'];
  if($method == 'POST')
  {
    
    $response['status']=200;
    $respStatus = $response['status'];
    $params = json_decode(file_get_contents('php://input'), TRUE);
      
        $selectkey   = isset($params['selectkey']) ? $params['selectkey'] : "*"; 
        $join     = isset($params['key']) ? $params['key'] : "";
        $where = isset($params['where']) ? $params['where'] : "";
    
          $sql = "SELECT lpd.id, lpd.lender_id, lpd.product_id, lpd.product_slug, lpd.loan_min, lpd.loan_max, lpd.roi_min, lpd.roi_max, lpd.tenor_min, lpd.tenor_max, lpd.loan_unit, lpd.tenor_unit, lpd.is_active, fp.id as productid, fp.name as productname, fp.slug as productslug, lm.id as lendermasterid, lm.slug as lendermasterslug, lm.lender_name
          FROM 
          fp_lender_product_details lpd 
          LEFT JOIN fp_products fp on fp.id=lpd.product_id 
          LEFT JOIN fp_lender_master lm on lm.id=lpd.lender_id
          WHERE lpd.lender_id=lm.id AND lpd.product_slug=fp.slug AND lpd.is_active=1 ORDER BY lpd.id DESC";
        
        
        $resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql)->result());
        json_output($respStatus,$resp);
    
  }
  else
  {
         json_output(400,array('status' => 400,'message' => 'Bad request.'));
  }

}



public function geteditlenderproduct()
{
  $method = $_SERVER['REQUEST_METHOD'];
  if($method == 'POST')
  {
    
    $response['status']=200;
    $respStatus = $response['status'];
    $params = json_decode(file_get_contents('php://input'), TRUE);
      
        $selectkey   = isset($params['selectkey']) ? $params['selectkey'] : "*"; 
        $join     = isset($params['key']) ? $params['key'] : "";
        $where = isset($params['where']) ? $params['where'] : "";
    
          $sql = "SELECT lpd.id, lpd.lender_id, lpd.product_id, lpd.product_slug, lpd.loan_min, lpd.loan_max, lpd.roi_min, lpd.roi_max, lpd.tenor_min, lpd.tenor_max, lpd.loan_unit, lpd.tenor_unit, lpd.is_active, fp.id as productid, fp.name as productname, fp.slug as productslug, lm.id as lendermasterid, lm.slug as lendermasterslug, lm.lender_name FROM fp_lender_product_details lpd LEFT JOIN fp_products fp on fp.id=lpd.product_id LEFT JOIN fp_lender_master lm on lm.id=lpd.lender_id WHERE lpd.lender_id=lm.id AND lpd.product_slug=fp.slug AND lpd.id='$where'";
        
        
        $resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql)->result());
        json_output($respStatus,$resp);
    
  }
  else
  {
         json_output(400,array('status' => 400,'message' => 'Bad request.'));
  }
}


public function add_lenderproduct_details()
{
    $method = $_SERVER['REQUEST_METHOD'];
    if ($method == "POST") {
        // $checkToken = $this->check_token();
        if (true) {
            $response['status'] = 200;
            $respStatus = $response['status'];
            $params = json_decode(file_get_contents('php://input'), true);

            
            $lenderid = isset($params["data"]["lender_id"])? $params["data"]["lender_id"] : null;
            $productslug = isset($params["data"]["product_slug"])? $params["data"]["product_slug"] : null;
            $loanmin = isset($params["data"]["loan_min"])? $params["data"]["loan_min"] : null;
            $loanmax = isset($params["data"]["loan_max"])? $params["data"]["loan_max"] : null;
            $roimin = isset($params["data"]["roi_min"])? $params["data"]["roi_min"] : null;
            $roimax = isset($params["data"]["roi_max"])? $params["data"]["roi_max"] : null;
            $tenormin = isset($params["data"]["tenor_min"])? $params["data"]["tenor_min"] : null;
            $tenormax = isset($params["data"]["tenor_max"])? $params["data"]["tenor_max"] : null;
            $loanunit = isset($params["data"]["loan_unit"])? $params["data"]["loan_unit"] : null;
            $tenorunit = isset($params["data"]["tenor_unit"])? $params["data"]["tenor_unit"] : null;

            $product_id = "SELECT * FROM fp_products WHERE slug = '" . $productslug . "'";
            $productid = $this->db->query($product_id)->result();


           $lenderdata = array(
       
        "lender_id"=>$lenderid,
        "product_id"=>$productid[0]->id,
        "product_slug"=>$productslug,
       "loan_min"=>$loanmin,
        "loan_max"=>$loanmax,
        "roi_min"=>$roimin,
        "roi_max"=>$roimax,
        "tenor_min"=>$tenormin,
        "tenor_max"=>$tenormax,
        "loan_unit"=>$loanunit,
        "tenor_unit"=>$tenorunit,
    
    );
     
        $this->db->insert("fp_lender_product_details",$lenderdata);
        return json_output(200, array('status' => 200, 'message' => 'Insert Successfully!'));
        json_output($respStatus,$resp);
        } 
        else {
            return json_output(500, array('status' => 500, 'message' => "Duplicate Entry"));
        }

    } 
    else {
        return json_output(400, array('status' => 400, 'message' => 'Bad request.'));
    }

} 

public function update_lenderproduct_details()
{
    $method = $_SERVER['REQUEST_METHOD'];
    if ($method == "POST") {
        // $checkToken = $this->check_token();
        if (true) {
            $response['status'] = 200;
            $respStatus = $response['status'];
            $params = json_decode(file_get_contents('php://input'), true);
            
            $id = isset($params['data']['id']) ? $params['data']['id'] : null;


            
            $lenderid = isset($params["data"]["lender_id"])? $params["data"]["lender_id"] : null;
            $productslug = isset($params["data"]["product_slug"])? $params["data"]["product_slug"] : null;
            $loanmin = isset($params["data"]["loan_min"])? $params["data"]["loan_min"] : null;
            $loanmax = isset($params["data"]["loan_max"])? $params["data"]["loan_max"] : null;
            $roimin = isset($params["data"]["roi_min"])? $params["data"]["roi_min"] : null;
            $roimax = isset($params["data"]["roi_max"])? $params["data"]["roi_max"] : null;
            $tenormin = isset($params["data"]["tenor_min"])? $params["data"]["tenor_min"] : null;
            $tenormax = isset($params["data"]["tenor_max"])? $params["data"]["tenor_max"] : null;
            $loanunit = isset($params["data"]["loan_unit"])? $params["data"]["loan_unit"] : null;
            $tenorunit = isset($params["data"]["tenor_unit"])? $params["data"]["tenor_unit"] : null;

            $product_id = "SELECT * FROM fp_products WHERE slug = '" . $productslug . "'";
            $productid = $this->db->query($product_id)->result();


           $lenderdata = array(
       
        "lender_id"=>$lenderid,
        "product_id"=>$productid[0]->id,
        "product_slug"=>$productslug,
       "loan_min"=>$loanmin,
        "loan_max"=>$loanmax,
        "roi_min"=>$roimin,
        "roi_max"=>$roimax,
        "tenor_min"=>$tenormin,
        "tenor_max"=>$tenormax,
        "loan_unit"=>$loanunit,
        "tenor_unit"=>$tenorunit,
    
    );
     
    
    $this->db->where("id",$id);

        $this->db->update("fp_lender_product_details",$lenderdata);
        return json_output(200, array('status' => 200, 'message' => 'Updated Successfully!'));
        json_output($respStatus,$resp);

        } 
        else {
            return json_output(500, array('status' => 500, 'message' => "Duplicate Entry"));
        }

    } 
    else {
        return json_output(400, array('status' => 400, 'message' => 'Bad request.'));
    }

} 


public function delete_lenderproduct_details()
{
        $method = $_SERVER['REQUEST_METHOD'];
        if($method =="POST")
        {
                // $checkToken = $this->check_token();
                if(True)
                {
                        $response['status']=200;
                        $respStatus = $response['status'];
                        $params 	= json_decode(file_get_contents('php://input'), TRUE);

                        $selectkey 	= isset($params['selectkey']) ? $params['selectkey'] : "*"; 
                        $join 		= isset($params['key']) ? $params['key'] : "";
                        $where 		= isset($params['where']) ? $params['where'] : "";	

                        $sql = "update fp_lender_product_details set is_active='0' where id='$where'";

                        $resp = array('status' => 200,'message' =>  'Success','data' => $this->db->query($sql));
                       
                        return json_output(200,array('status' => 200,'message' => "Deleted Successfully"));
                        return json_output($respStatus,$resp);

                }
                else
                {
                    return json_output(400,array('status' => 400,'message' => "Unauthorized"));
                }
            
        }
        else
        {
                return json_output(400,array('status' => 400,'message' => 'Bad request.'));
        }
    
}

public function getproduct()
{
    $method = $_SERVER['REQUEST_METHOD'];
    if ($method == 'POST') {

        $response['status'] = 200;
        $respStatus = $response['status'];
        $params = json_decode(file_get_contents('php://input'), true);

        $selectkey = isset($params['selectkey']) ? $params['selectkey'] : "*";
        $join = isset($params['key']) ? $params['key'] : "";
        $where = isset($params['where']) ? $params['where'] : "";

        $sql = "SELECT fp.id as id, fp.name,fp.slug,pt.name as products_type,fp.amounts,fp.tenor_min,fp.tenor_max,fp.tenor,fp.roi_min,fp.roi_max
      FROM fp_products fp, fp_products_type pt
      WHERE fp.products_type=pt.id AND fp.is_active = 1
      ORDER BY fp.id DESC ";

        $resp = array('status' => 200, 'message' => 'Success', 'data' => $this->db->query($sql)->result());
        json_output($respStatus, $resp);

    } else {
        json_output(400, array('status' => 400, 'message' => 'Bad request.'));
    }

} 


public function getedit_product()
{
    $method = $_SERVER['REQUEST_METHOD'];
    if ($method == 'POST') {

        $response['status'] = 200;
        $respStatus = $response['status'];
        $params = json_decode(file_get_contents('php://input'), true);

        $selectkey = isset($params['selectkey']) ? $params['selectkey'] : "*";
        $join = isset($params['key']) ? $params['key'] : "";
        $where = isset($params['where']) ? $params['where'] : "";

        $sql = "SELECT fp.name,fp.slug,pt.id as products_type,fp.amounts,fp.tenor_min,fp.tenor_max,fp.tenor,fp.roi_min,fp.roi_max
      FROM fp_products fp, fp_products_type pt
      WHERE fp.products_type=pt.id AND fp.id='$where'";

        $resp = array('status' => 200, 'message' => 'Success', 'data' => $this->db->query($sql)->result());
        json_output($respStatus, $resp);

    } else {
        json_output(400, array('status' => 400, 'message' => 'Bad request.'));
    }
}  

public function addlenderproduct()
{
    $method = $_SERVER['REQUEST_METHOD'];
    if ($method == "POST") {
        // $checkToken = $this->check_token();
        if (true) {
            $response['status'] = 200;
            $respStatus = $response['status'];
            $params = json_decode(file_get_contents('php://input'), true);

            $name = isset($params["data"]["name"]) ? $params["data"]["name"] : null;
            $slug = isset($params["data"]["slug"]) ? $params["data"]["slug"] : null;
            $products_type = isset($params["data"]["products_type"]) ? $params["data"]["products_type"] : null;
            $amounts = isset($params["data"]["amounts"]) ? $params["data"]["amounts"] : null;
            $tenormin = isset($params["data"]["tenor_min"]) ? $params["data"]["tenor_min"] : null;
            $tenormax = isset($params["data"]["tenor_max"]) ? $params["data"]["tenor_max"] : null;
            $tenor = isset($params["data"]["tenor"]) ? $params["data"]["tenor"] : null;
            $roimin = isset($params["data"]["roi_min"]) ? $params["data"]["roi_min"] : null;
            $roimax = isset($params["data"]["roi_max"]) ? $params["data"]["roi_max"] : null;

            // $product_id = "SELECT id FROM fp_products WHERE slug = '" . $productslug . "'";
            // $productid = $this->db->query($product_id)->result();

            $lenderproduct = array(

                "name" => $name,
                "slug" => $slug,
                "products_type" => $products_type,
                "amounts" => $amounts,
                "tenor_min" => $tenormin,
                "tenor_max" => $tenormax,
                "tenor" => $tenor,
                "roi_min" => $roimin,
                "roi_max" => $roimax,

            );

            $this->db->insert("fp_products", $lenderproduct);
            return json_output(200, array('status' => 200, 'message' => 'Insert Successfully!'));
        } else {
            return json_output(500, array('status' => 500, 'message' => "Duplicate Entry"));
        }

    } else {
        return json_output(400, array('status' => 400, 'message' => 'Bad request.'));
    }

}



public function updatelenderproduct()
{
    $method = $_SERVER['REQUEST_METHOD'];
    if ($method == "POST") {
        // $checkToken = $this->check_token();
        if (true) {
            $response['status'] = 200;
            $respStatus = $response['status'];
            $params = json_decode(file_get_contents('php://input'), true);

            $id =isset($params["where"]) ? $params["where"] : null;

            $name = isset($params["data"]["name"]) ? $params["data"]["name"] : null;
            $slug = isset($params["data"]["slug"]) ? $params["data"]["slug"] : null;
            $products_type = isset($params["data"]["products_type"]) ? $params["data"]["products_type"] : null;
            $amounts = isset($params["data"]["amounts"]) ? $params["data"]["amounts"] : null;
            $tenormin = isset($params["data"]["tenor_min"]) ? $params["data"]["tenor_min"] : null;
            $tenormax = isset($params["data"]["tenor_max"]) ? $params["data"]["tenor_max"] : null;
            $tenor = isset($params["data"]["tenor"]) ? $params["data"]["tenor"] : null;
            $roimin = isset($params["data"]["roi_min"]) ? $params["data"]["roi_min"] : null;
            $roimax = isset($params["data"]["roi_max"]) ? $params["data"]["roi_max"] : null;

            // $product_id = "SELECT id FROM fp_products WHERE slug = '" . $productslug . "'";
            // $productid = $this->db->query($product_id)->result();

            $lenderproduct = array(

                "name" => $name,
                "slug" => $slug,
                "products_type" => $products_type,
                "amounts" => $amounts,
                "tenor_min" => $tenormin,
                "tenor_max" => $tenormax,
                "tenor" => $tenor,
                "roi_min" => $roimin,
                "roi_max" => $roimax,

            );
            $this->db->where("id",$id);
            $this->db->update("fp_products", $lenderproduct);
            return json_output(200, array('status' => 200, 'message' => 'Insert Successfully!'));
        } else {
            return json_output(500, array('status' => 500, 'message' => "Duplicate Entry"));
        }

    } else {
        return json_output(400, array('status' => 400, 'message' => 'Bad request.'));
    }

}  



public function deletelenderproduct()
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

            $sql = "update fp_products set is_active='0' where id='$where'";

            $resp = array('status' => 200, 'message' => 'Success', 'data' => $this->db->query($sql));

            // return json_output($respStatus,$resp);
            return json_output(200, array('status' => 200, 'message' => "Deleted Successfully"));
        } else {
            return json_output(400, array('status' => 400, 'message' => "Unauthorized"));
        }

    } else {
        return json_output(400, array('status' => 400, 'message' => 'Bad request.'));
    }

}





}