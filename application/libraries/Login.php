<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Login {
	private $ci;
	var $user_service   = define_auth_user;
    var $auth_key       = define_auth_key;
	function __construct() {
		$this->ci =& get_instance();
    	$this->ci->load->database();
	}

	public function check_auth_user()
    {
            $user_service   = $this->ci->input->get_request_header('user-service', TRUE);
            $auth_key       = $this->ci->input->get_request_header('auth-key', TRUE);
            if($user_service == $this->user_service && $auth_key == $this->auth_key){
                
                return true;
            } else {
                return json_output(401,array('status' => 401,'message' => 'Unauthorized.'));
            }
	}//------------------------------------------------------------------------

	public function auth(){

        $users_id  = $this->ci->input->get_request_header('user_id', TRUE);
        $token     = $this->ci->input->get_request_header('Authorization-key', TRUE);
        $q  = $this->ci->db->select('expired_at')->from('sp_users_auth')->where('user_id_fk',$users_id)->where('token',$token)->get()->row();
		if($q == ""){
            return json_output(401,array('status' => 401,'message' => 'Unauthorized.'));
        } else {
            if($q->expired_at < date('Y-m-d H:i:s')){
                return json_output(401,array('status' => 401,'message' => 'Your session has been expired.'));
            } else {
                $updated_at = date('Y-m-d H:i:s');
                $expired_at = date("Y-m-d H:i:s", strtotime('+12 hours'));
                $this->ci->db->where('user_id_fk',$users_id)->where('token',$token)->update('sp_users_auth',array('expired_at' => $expired_at,'updated_at' => $updated_at));
                return array('status' => 200,'message' => 'Authorized.');
            }
        }
    }//-------------------------------------------------------------------------

    public function check_login($username,$password){
    	$q  = $this->ci->db->from('sp_users')->where('username',$username)->get()->row();
        if($q == ""){
            return array('status' => 400,'message' => 'User not found.');
        } else {
            $hashed_password = $q->password;
            $id              = $q->user_id;
            if (base64_decode($hashed_password) == $password) {
               $last_login = date('Y-m-d H:i:s');

               //$token = crypt(substr( md5(rand()), 0, 7),"st");
                
                /*JWT*/
                $jwt = new JWT();
                $jwtSecretKey = 'OnE$h00T';
                $data = ['userId'=>$q->user_id,'email'=>$q->email,'name'=>$q->name,'randstr'=>crypt(substr(md5(rand()), 0, 7),"st")];
                $token = $jwt->encode($data,$jwtSecretKey,'HS256');
                /*JWT*/

               $expired_at = date("Y-m-d H:i:s", strtotime('+12 hours'));
               $this->ci->db->trans_start();
               $this->ci->db->where('user_id',$id)->update('sp_users',array('last_login' => $last_login));
               $this->ci->db->insert('sp_users_auth',array('user_id_fk' => $id,'token' => $token,'expired_at' => $expired_at));
               if ($this->ci->db->trans_status() === FALSE){
                  $this->ci->db->trans_rollback();
                  return array('status' => 500,'message' => 'Internal server error.');
               } else {
                  $this->ci->db->trans_commit();
                  $company_name  ="";
                  $role_name  ="";
                  $c_name  = $this->ci->db->from('sp_company')->where('company_id',$q->company_id)->get()->row();
                  $r_name  = $this->ci->db->from('sp_role')->where('role_id',$q->role_id_fk)->get()->row();
                  if($c_name  != ""){
                      $company_name  = $c_name->company_name;
                  }
                   if($r_name  != ""){
                      $role_name  = $r_name->name;
                  }
                  return array(
                      'status' => 200,
                      'message' => 'Successfully login.',
                      'id' => $id,                       
                      'name' => $q->name, 
                      'company_id' =>  $q->company_id,
                      'company_name' =>  $company_name,
                      'role_name' =>  $role_name,
                      "username" => $q->username,
                      'role_id_fk' => $q->role_id_fk, 
                      'token' => $token,
                    );
               }
            } else {
               return array('status' => 400,'message' => 'Wrong password.');
               //json_output(403, array('status' => 403,'message' => 'Unknown access'));
            }
        }
    }//-------------------------------------------------------------------------

    public function logout(){
        $users_id  = $this->ci->input->get_request_header('user-id', TRUE);
        $token     = $this->ci->input->get_request_header('authorization-key', TRUE);
        $this->ci->db->where('user_id',$users_id)->where('token',$token)->delete('sp_users_auth');
        return array('status' => 200,'message' => 'Successfully logout.');
    }//-------------------------------------------------------------------------






}//------------------end of login class-----------------------------------------
