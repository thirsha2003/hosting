<?php
namespace App\ThirdParty;
defined('BASEPATH') OR exit('No direct script access allowed');

class MTalkz 
{
    private $ci;
    function __construct() {

        $this->ci = & get_instance();            
    }
    
    public function sendmobileotp($mobile,$slug,$otp)
    {
        // public $mTalkz_apiurl ="https://msg.mtalkz.com/V2/http-api-post.php?";
        // public $sendderid       ="FINNUP";
        // public $mTalkzapi_key   ="wq60R503Me1d8Omx";
        $slugbl=$slug;
        $msgTemplateBorrower 	="Dear customer, use this One Time Password " .$otp. " to log in to your FinnUp Borrower account. This OTP will be valid for the next 2 mins.";
        $msgTemplateLender 		="Dear customer, use this One Time Password " .$otp. " to log in to your FinnUp Lender account. This OTP will be valid for the next 2 mins.";
		$msgTemplateConnector     ="Dear customer, use this One Time Password " .$otp. " to log in to your FinnUp Lender account. This OTP will be valid for the next 2 mins.";
        $msgTemplateOthers		="Dear customer, use this One Time Password " .$otp. " to log in to your FinnUp account. This OTP will be valid for the next 2 mins.";
        
        //    $config              = config('config\mTalkzconfig');
        //    $mTalkzAPI           = $config->mTalkz_apiurl;
        //    $mTalkzSenderId      = $config->senderid;
        //    $mTalkzApi_Key       = $config->mTalkzapi_key;
        //    $msgTemplateB        = $config->msgTemplateBorrower;
        //    $msgTemplateL        = $config->msgTemplateLender;
        //    $msgFormat           = $config->format;
            
        $mTalkzAPI           = "http://msg.mtalkz.com/V2/http-api.php?";
        $mTalkzSenderId      = "FINNUP";
        $mTalkzApi_Key       = "wq60R503Me1d8Omx";
        if($slugbl=='borrower')
        {
            $msgTemplateB =$msgTemplateBorrower;
        }else if($slugbl=="lender")
        {
            $msgTemplateB =$msgTemplateLender;
            
        } else if ($slugbl=="connector"){

            $msgTemplateB =$msgTemplateConnector;

        }
        
        
        else
        {
            $msgTemplateB =$msgTemplateOthers;
        }
        $msgFormat = "json";
                 
        $url = $mTalkzAPI;
        $fields ="apikey=".$mTalkzApi_Key."&senderid=".$mTalkzSenderId."&number=".$mobile."&message=".$msgTemplateB."&format=".$msgFormat;
        // echo($url);  
        $result = $this->callmtalk($url,$fields);
        return $result;
    }
    private function callmtalk($url,$fields)
    {
            try{
                    $urlc=$url;
                    $fieldsc=$fields;
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $urlc);
                        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $fieldsc);
                        $result = curl_exec($ch);
            }catch (\Exception $e) {
                return false;
            }
                //$this->log("url");
                // $this->log($url);
                //$this->log("fields");
                // $this->log($fields);
            curl_close($ch);
            if ($result)
                return $result;
            else
                return false;

    }

    private function log($message) {
            print_r($message);
            echo "\n";
    } //---------------- End of function --------------------
    
    public function probe42($cin)
    {
        $probe42API           = "https://api.probe42.in/probe_pro_sandbox/companies/".$cin."/base-details";
        // $probe42details       = "";
        $probe42Api_Key       = "HeqZByvSwm8PdxEL1drWA2LG9QF84PkaPHeyLvl0";
        $probe42version       =  "1.3";
        

        $msgFormat           = "json";
                 
        $url = $probe42API;
        $fields ="apikey=".$probe42Api_Key."&version=".$probe42version."&format=".$msgFormat;
        $result = $this->callprobe42($url,$fields);
        return $result;

    }

    private function callprobe42($url,$fields)
    {
            try{

                    $urlc=$url;
                    $fieldsc=$fields;
                   
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $urlc);
                        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $fieldsc);
                        // curl_setopt($ch, CURLOPT_POSTFIELDS, $url);
                        // curl_setopt($ch, CURLOPT_POSTFIELDS, $urls);
                        $result = curl_exec($ch);
            }catch (\Exception $e) {
                return false;
            }
                //$this->log("url");
                // $this->log($url);
                //$this->log("fields");
                // $this->log($fields);
            curl_close($ch);
            if ($result)
                return $result;
            else
                return false;

    }










}//------------------------------------end of class----------------------------
