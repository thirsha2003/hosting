<?php

header('Access-Control-Allow-Origin: *'); //for allow any domain, insecure
header('Access-Control-Allow-Headers: *'); //for allow any headers, insecure
header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE'); //method allowed
header("HTTP/1.1 200 OK");

defined('BASEPATH') OR exit('No direct script access allowed');


class Filedownload extends CI_Controller 
{

        public function __construct(){
		parent::__construct();
		$this->load->helper('json_output');
	    }

        public function download()
        {

           // $params = json_decode(file_get_contents('php://input'), TRUE);
			$fname= $this->input->get_request_header('file_name', TRUE);

            //print_r($fname);
            // $params['user_id_fk'] = $this->input->get_request_header('User-ID', TRUE);
            
           

            $filePath="http://localhost/fin/api/uploads/".$fname;
            //print_r($filePath);
            //return $filePath;
            // Define headers 
            header("Cache-Control: public"); 
            header("Content-Description: File Transfer"); 
            header("Content-Disposition: attachment; filename =".$fname); 
            header("Content-Type: application/pdf"); 
            header("Content-Transfer-Encoding: binary"); 
            
            // Read the file 
            readfile($filePath); 
            //return;
            exit;
                        
            // $respStatu=readfile($filePath); 
            // json_output($filePath);
           
                
        }
        


}
