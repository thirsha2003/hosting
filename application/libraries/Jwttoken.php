<?php
defined('BASEPATH') OR exit('No direct script access allowwed');

class Jwttoken extends CI_Controller {

    // public function index(){
    //     echo 'jwt test'; 
    // }

	private $jwtSecretKey = 'Fc$007';
	private $jwttype = 'HS256';

	function __construct() {
		$this->ci =& get_instance();
    	$this->ci->load->database();
	}


    public function token($data){
        $jwt = new JWT();
        // $data = ['userId'=>007,'email'=>'spadmin@finnup.in','userType'=>'admin'];
		if($data){
			try{
			
			
			$token = $jwt->encode($data,$this->jwtSecretKey, $this->jwttype);
			

			}catch(Exception $e){
				return false;	
			}
			return $token;
		}else{
			return false;
		}
        
    }

    public function decodeToken(){
		$token   = $this->ci->input->get_request_header('token', TRUE);
		if($token)
		{
			$jwt = new JWT();
			try{
				$decodedToken = $jwt->decode($token,$this->jwtSecretKey,$this->jwttype);

			}catch(Exception $e) {	
				return false;	
			}

			return $decodedToken;
		}else{
			return false;
		}
        
    }

}

?>
