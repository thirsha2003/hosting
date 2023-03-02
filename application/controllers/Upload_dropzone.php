<?php
header('Access-Control-Allow-Origin: *'); //for allow any domain, insecure
header('Access-Control-Allow-Headers: *'); //for allow any headers, insecure
header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE'); //method allowed
// header('Content-Type: multipart/form-data;');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");  
// defined('BASEPATH') OR exit('No direct script access allowed');

class Upload_dropzone
{
        function __construct()
		{
			
		}
		public function upload()
		{

        $borrowerdocPath = "uploads/";
		$postdata = file_get_contents("php://input");  
		$request = json_decode($postdata);  
		echo $request;
		foreach ($request->fileSource as $key => $value) 
		{  
     
			$image_parts = explode(";base64,", $value);  
			 
			$image_type_aux = explode("image/", $image_parts[0]);  
			 
			$image_type = $image_type_aux[1];  
			
			$image_base64 = base64_decode($image_parts[1]);  
			
			$file = $borrowerdocPath . uniqid() . '.'.$image_type;  
			
			file_put_contents($file, $image_base64);  
		}  
	}
    }