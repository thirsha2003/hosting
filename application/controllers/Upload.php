<?php
header('Access-Control-Allow-Origin: *'); //for allow any domain, insecure
header('Access-Control-Allow-Headers: *'); //for allow any headers, insecure
header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE'); //method allowed
// header('Content-Type: multipart/form-data;');
// header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");  
//defined('BASEPATH') OR exit('No direct script access allowed');
//------------------------------------------------------------------------------------/
//defined('BASEPATH') OR exit('No direct script access allowed');
if($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
	header('Access-Control-Allow-Methods: POST');
	header('Access-Control-Allow-Headers: Content-Type');
	exit;
}
class Upload extends CI_Controller
{

	

	public function __construct()
	{
		parent::__construct();
	}
	public function upload_post()
	{		
		$check_auth_user = $this->login->check_auth_user();
		$check_auth_user=true;
		if($check_auth_user == true)
		{	//$respStatus =200;		
			//$company_id = $this->input->get_request_header('company-id', TRUE);			
			if($this ->input->method())
			{

				$params = json_decode(file_get_contents('php://input'), TRUE);
				if(!$_FILES) {
		   		   json_output(500,'Please choose a file');
					return;
				}
				$upload_path = './uploads/';
				//file upload destination
				$config['upload_path'] = $upload_path;
				//allowed file types. * means all types
				// $config['allowed_types'] = '*';
				$config['allowed_types'] = 'gif|jpg|png|pdf|jpeg';
				//allowed max file size. 0 means unlimited file size
				$config['max_size'] = '0';
				//max file name size
				$config['max_filename'] = '255';
				//whether file name should be encrypted or not
				$config['encrypt_name'] = TRUE;
				$config['file_name'] = 'file_'.time();
				if(!is_dir($upload_path)){
					mkdir($upload_path, 0777, TRUE);
				}
				$this->load->library('upload',$config);	
				
				

					if (file_exists($upload_path . $_FILES['file']['name'])) 
					{
						//$this->response('File already exists => ' . $upload_path . $_FILES['file']['name']);
						return "file already there";
					} else {
						if (!file_exists($upload_path)) {
							mkdir($upload_path, 0777, true);
						}		
						if($this->upload->do_upload('file')) {
							//$this->response('File successfully uploaded => "' . $upload_path . $_FILES['file']['name']);
							return "Successfully Uploaded";
						} else {
							//$this->response('Error during file upload => "' . $this->upload->display_errors(), 500);
							return "error";
						}		
					}
												
			}
					
			
		}		
	}
	
	function upload_image(){

		
		$img = array(); // return variable
		$this->load->helper(array('file','directory'));
		if (!empty($collection)) 
		{
		   $path="uploads/";
		   if( !is_dir($path) ) {
			   mkdir($path);
		   }
		   $config['upload_path'] = $path; /* NB! create this dir! */
		   $config['allowed_types'] = 'gif|jpg|png|bmp|jpeg';
		   $config['file_name'] = 'image001'; 
		   $config['overwrite']=TRUE;
	   
		   $this->load->library('upload', $config);
	   
	   	//    $configThumb = array();
		//    $configThumb['image_library'] = 'gd2';
		//    $configThumb['source_image'] = '';
		//    $configThumb['create_thumb'] = FALSE;
		//    $configThumb['maintain_ratio'] = FALSE;
	   
		// 	 /* Load the image library */
		//    $this->load->library('image_lib');
	   
		//    if (isset($_FILES['image']['tmp_name'])) 
		//    {
			   $upload = $this->upload->do_upload('image');
	   		   /* File failed to upload - continue */
			   if($upload === FALSE)
			   {
				   $error = array('error' => $this->upload->display_errors());
				   $data['message']=$error['error'];
				   return;   
			   } 
			   /* Get the data about the file */
			   $data = $this->upload->data();
			   $img['image']='/'.$data['file_name'];
	   
		//    }
	   
		}

	// 		   return $img;
			
	// }// end of function upload_image

	// public function fupload()
	// {
	// 	$borrowerdocPath = "uploads/";
	// 	$postdata = file_get_contents("php://input");  
	// 	$request = json_decode($postdata);  
		


	// 	foreach ($request->fileSource as $key => $value) 
	// 	{  
     
	// 		$image_parts = explode(";base64,", $value);  
			 
	// 		$image_type_aux = explode("image/", $image_parts[0]);  
			 
	// 		$image_type = $image_type_aux[1];  
			
	// 		$image_base64 = base64_decode($image_parts[1]);  
			
	// 		$file = $borrowerdocPath . uniqid() . '.'.$image_type;  
			
	// 		file_put_contents($file, $image_base64);  
	// 	}  

	// }
	}

}//----------------end of class-------------------------------
