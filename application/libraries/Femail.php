<?php
namespace App\Libraries;
defined('BASEPATH') OR exit('No direct script access allowed');
include APPPATH . 'ThirdParty/sendgrid-php/sendgrid-php.php';

class Femail 
{
                private $ci;
                var $user_service   = define_auth_user;
                var $auth_key       = define_auth_key;
                var $message        = "";

                function __construct() {
                    $this->ci =& get_instance();                  
                }
                //---------------------------------------------------
                           
              
                public function verifyEmail($toemail,$otp,$emailslug,$otpvalid)
                {
                               $subject = "Finnup Team : Verify your email id!";
                               $messagetoborrower   ="Dear customer, use this One Time Password ".$otp
                               ." to verify your email to complete the FinnUp Borrower account signup Process. 
                               This OTP will be valid for the next ".$otpvalid." mins.";
                               $messagetolender   ="Dear customer, use this One Time Password ".$otp
                               ." to verify your email to complete the FinnUp Lender account signup process. 
                               This OTP will be valid for the next ".$otpvalid." mins.";
                               $messagetoothers   ="Dear customer, use this One Time Password ".$otp
                               ." to log in to your FinnUp account. 
                               This OTP will be valid for the next ".$otpvalid." mins.";

                               if($emailslug=='borrower')
                               {
                                    $message = $messagetoborrower;
                               }
                               else if($emailslug=='lender')
                               {
                                    $message = $messagetolender;
                               }else
                               {
                                   $message = $messagetoothers;
                               }                                 

								$to    = $toemail;
								$email = new \SendGrid\Mail\Mail();
								$email->setSubject($subject);
								$email->addContent("text/html", $message);
								$email->setFrom('support@finnup.in', 'FinnUp Team');
								$email->addTo($to);							
								$sendgrid = new \SendGrid("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");
								try {
									    $response = $sendgrid->send($email);
								}catch (\Exception $e) {
									echo 'Caught email exception: ',  $e->getMessage(), "\n";
                                             return false;
								}
                                        return true;
                }

                public function sendOTPemail($toemail="",$otp="",$emailslug="Finnup User",$otpvalid='2')
                {
                               $subject = "OTP from Finnup!";
                               $messagetoborrower   ="Dear customer, use this One Time Password ".$otp
                                                    ." to log in to your FinnUp Borrower account. 
                                                    This OTP will be valid for the next ".$otpvalid." mins.";

                               $messagetolender   ="Dear customer, use this One Time Password ".$otp
                               ." to log in to your FinnUp Borrower account. 
                               This OTP will be valid for the next".$otpvalid." mins.";

                               $messagetoothers   ="Dear customer, use this One Time Password ".$otp
                               ." to log in to your FinnUp account. 
                               This OTP will be valid for the next ".$otpvalid." mins.";
                               if($emailslug=='borrower')
                               {
                                    $message = $messagetoborrower;
                               } else if($emailslug=='lender')
                               {
                                   $message = $messagetolender;
                               }
                               else 
                               {
                                    $message = $messagetoothers;
                               }                                 

								$to    = $toemail;
								$email = new \SendGrid\Mail\Mail();
								$email->setSubject($subject);
								$email->addContent("text/html", $message);
								$email->setFrom('support@finnup.in', 'FinnUp Team');
								$email->addTo($to);							
								$sendgrid = new \SendGrid("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");
								try {
									    $response = $sendgrid->send($email);
								}catch(\Exception $e) 
                                        {
									echo 'Caught email exception: ',  $e->getMessage(), "\n";
									 print_r("--------------------error--------------------------");
                                             return false;
								}
                                return true;
                }
                
}//----------------- end of class fpemail--------------------------------------