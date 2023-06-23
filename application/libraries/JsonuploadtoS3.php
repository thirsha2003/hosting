<?php
namespace App\Libraries;
use Aws\Credentials\Credentials;  
defined('BASEPATH') OR exit('No direct script access allowwed');

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;
use Aws\Credentials\CredentialProvider;


class JsonuploadtoS3 {

	function __construct() {
		$this->ci =& get_instance();
    	$this->ci->load->database();
	}


    public function aws_s3bucket($borrowerid,$foldername,$responsejson)
    {
        // $bucket = 'finnup';       
        $bucket = 'bucketinfo';     

        $keyname = "FINNBID".$borrowerid;
        $Folder_name = "finnup_json_dump/".$foldername;
        $Addkey_name = $Folder_name . $keyname . ".json";
        // $credentials = new Credentials('AKIAWJIM4CKQMIAM5R5L', 'GcL436Q16pUChV4ohqqna0QE9arhpGw8Q5sRorBV'); // live api_key
        $credentials = new Credentials('AKIAYRVTG64ZXVJBLFE5', 'yw8tfa+DIwTVtSdKLWYGRdhIH94LwizjubpVw0XW');   // local Usages


        $s3 = new S3Client([
            'region' => 'ap-south-1',
            'version' => 'latest',
            'credentials' => $credentials
        ]);
        try {
            // Upload data.
            $result = $s3->putObject([
                'Bucket' => $bucket,
                'Key' => $Addkey_name,
                'Body' =>  $responsejson,
                'ACL' => 'public-read'
            ]);
            $url = $result['ObjectURL'];

            

        } catch (S3Exception $e) {
            echo $e->getMessage() . PHP_EOL;
        }
        // return $url; 




    }  // aws_s3bucket

    public function aws_s3bucket_xlrt($borrowerid,$dmscode ,$foldername,$responsejson)
    {
        $bucket = 'finnup';

        $keyname = "FINNBID".$borrowerid.$dmscode;
        $Folder_name = "finnup_json_dump/".$foldername;
        $Addkey_name = $Folder_name . $keyname . ".json";
        $credentials = new Credentials('AKIAWJIM4CKQMIAM5R5L', 'GcL436Q16pUChV4ohqqna0QE9arhpGw8Q5sRorBV'); 


        $s3 = new S3Client([
            'region' => 'ap-south-1',
            'version' => 'latest',
            'credentials' => $credentials
        ]);
        try {
            // Upload data.
            $result = $s3->putObject([
                'Bucket' => $bucket,
                'Key' => $Addkey_name,
                'Body' =>  $responsejson,
                'ACL' => 'public-read'
            ]);
            $url = $result['ObjectURL'];

            

        } catch (S3Exception $e) {
            echo $e->getMessage() . PHP_EOL;
        }
        // return $url; 




    }  // aws_s3bucket




}
?>