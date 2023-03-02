<?php
defined('BASEPATH') OR exit('No direct script access allowwed');

class JwttestController extends CI_Controller {

    // public function index(){
    //     echo 'jwt test'; 
    // }


    public function token(){
        $jwt = new JWT();
        $jwtSecretKey = 'Fc$007';
        $data = ['userId'=>007,'email'=>'spadmin@finnup.in','userType'=>'admin'];
        $token = $jwt->encode($data,$jwtSecretKey,'HS256');
        return $token;
    }

    public function decodeToken(){
        $token = $this->url->segment(5);
        $jwt = new JWT();
        $jwtSecretKey = 'Fc$007';
        $data = ['userId'=>007,'email'=>'spadmin@finnup.in','userType'=>'admin'];
        $decodedToken = $jwt->decode($data,$jwtSecretKey,'HS256');
        print_r($decodedToken);
    }




	
}

?>
