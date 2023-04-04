<?php defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Use SSL
|--------------------------------------------------------------------------
|
| Run this over HTTP or HTTPS. HTTPS (SSL) is more secure but can cause problems
| on incorrectly configured servers.
|
*/

$config['use_ssl'] = TRUE;

/*
|--------------------------------------------------------------------------
| Verify Peer
|--------------------------------------------------------------------------
|
| Enable verification of the HTTPS (SSL) certificate against the local CA
| certificate store.
|
*/

$config['verify_peer'] = TRUE;

/*
|--------------------------------------------------------------------------
| Access Key
|--------------------------------------------------------------------------
|
| Your Amazon S3 access key.
|
*/

$config['access_key'] = 'AKIAYRVTG64ZXVJBLFE5';

/*
|--------------------------------------------------------------------------
| Secret Key
|--------------------------------------------------------------------------
|
| Your Amazon S3 Secret Key.
|
*/

$config['secret_key'] = 'yw8tfa+DIwTVtSdKLWYGRdhIH94LwizjubpVw0XW';

/*
|--------------------------------------------------------------------------
| Bucket Name
|--------------------------------------------------------------------------
|
| Your Amazon Bucket Name.
|
*/

$config['bucket_name'] = 'bucketinfo';

/*
|--------------------------------------------------------------------------
| Bucket Folder Name
|--------------------------------------------------------------------------
|
| Your Amazon Bucket Folder Name.
|
*/

$config['folder_name'] = 'WRITE_YOUR_S3_BUCKETS_FOLDER_NAME';

/*
|--------------------------------------------------------------------------
| Bucket Folder Name
|--------------------------------------------------------------------------
|
| Your Amazon Bucket Folder Name.
|
*/

$config['s3_url'] = 'https://finnup.s3.ap-south-1.amazonaws.com/';

/*
|--------------------------------------------------------------------------
| Use Enviroment?
|--------------------------------------------------------------------------
|
| Get Settings from enviroment instead of this file? 
| Used as best-practice on Heroku
|
*/

$config['get_from_enviroment'] = FALSE;

/*
|--------------------------------------------------------------------------
| Access Key Name
|--------------------------------------------------------------------------
|
| Name for access key in enviroment
|
*/
$config['access_key_envname'] = 'http://finnup.cu3siqxd9aqt.ap-south-1.rds.amazonaws.com/';

/*
|--------------------------------------------------------------------------
| Access Key Name
|--------------------------------------------------------------------------
|
| Name for access key in enviroment
|

*/
$config['secret_key_envname'] = 'http://finnup.cu3siqxd9aqt.ap-south-1.rds.amazonaws.com/';

/*
|--------------------------------------------------------------------------
| If get from enviroment, do so and overwrite fixed vars above
|--------------------------------------------------------------------------
|
*/

if ($config['get_from_enviroment']){
	$config['access_key'] = getenv($config['access_key_envname']);
	$config['secret_key'] = getenv($config['secret_key_envname']);

}