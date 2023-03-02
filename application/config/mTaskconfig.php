<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;


class mTalkzconfig extends BaseConfig 
{
	
    function __construct() {
    }
    //http://msg.mtalkz.com/V2/http-api.php?
    // public $mTalkz_apiurl ="https://msg.mtalkz.com/V2/http-api-post.php?";
    public $mTalkz_apiurl ="http://msg.mtalkz.com/V2/http-api.php?";
    public $senderid       ="FINNUP";
    public $mTalkzapi_key   ="wq60R503Me1d8Omx";
    public $msgTemplateBorrower ="Dear customer, use this One Time Password {#OTP#} to log in to your FinnUp Borrower account. This OTP will be valid for the next {#MIN#} mins.";
    public $msgTemplateLender ="Dear customer, use this One Time Password {#OTP#} to log in to your FinnUp Lender account. This OTP will be valid for the next {#MIN#} mins.";
    public $format  ="json";



}