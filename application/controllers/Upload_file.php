<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include APPPATH . 'ThirdParty/sendgrid-php/sendgrid-php.php';

class Upload_File extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model('Borrowerdocs');
    }
    function index()
    {
        $data =array();
        $data['borrowerdocs'] =$this->BorrowerDocs->getRows();
        //return result;
    }

    function upload_post()
    {
        $user_id = $this->input->get_request_header('user_id', TRUE);	
        $doctype = $this->input->get_request_header('doctype', TRUE);	

        if(!empty($_FILES))
        {
                
                $upload_path = 'uploads/';
				//file upload destination
				$config['upload_path'] = $upload_path;
				//allowed file types. * means all types
				//$config['allowed_types'] = '*';
                $config['allowed_types'] = 'gif|jpg|png|pdf|jpeg';
				//allowed max file size. 0 means unlimited file size
				$config['max_size'] = '0';
				//max file name size
				$config['max_filename'] = '255';
				//whether file name should be encrypted or not
				$config['encrypt_name'] = TRUE;
				$config['file_name'] = 'file_'.time();
                
                $this->load->library('upload',$config);
                $this->upload->initialize($config);

                If($this->upload->do_upload('file'))
                {
                    
                    $fileData = $this->upload->data();
                    $uploadData['borrower_id']  = $user_id;
                    $uploadData['doc_type']     =$doctype;
                    $uploadData['file_name']    =$fileData['file_name'];
                    $uploadData['resdata']      = $user_id;
                    $uploadData['uploaded_at']  = date("Y-m-d H:i:s");
                    
                    $rowcnt =0;
                    if($doctype)
                    {
                            if($doctype=='OTHERS' || $doctype=='PANIND' || $doctype=='AADIND')
                            {
                                $insert = $this->Borrowerdocs->insert($uploadData);
                            }else
                            {
                                $rowcnt = $this->Borrowerdocs->getRowsDoctype($user_id,$doctype);
                            }
                    }
                    if($rowcnt>0)
                    {
                        $new_profile_update_data = array( 
                       
                            'doc_type' => $doctype,
                            'file_name' => $fileData['file_name'], 
                            'uploaded_at'  => date("Y-m-d H:i:s"),
                           
                            );
                        $this->db->where('user_id', $user_id);
                        $this->db->where('doc_type', $doctype);
                        $this->db->update('fp_borrower_docs',$new_profile_update_data);

                    }else
                    {
                        $insert = $this->Borrowerdocs->insert($uploadData);
                    }
                   if($doctype!='' && $doctype !=null)
                   {
                     if($doctype=="GST")
                     {
                        $new_profile_update_data = array( 
                       
                            'gst_url' => $fileData['file_name'],
                           
                            );

                            $this->db->where('user_id', $user_id);
                            $this->db->update('fp_borrower_user_details',$new_profile_update_data);

                     }else if($doctype=="PAN")
                     {
                        $new_profile_update_data = array( 
                       
                            'pan_url' => $fileData['file_name'],
                           
                            );
                            $this->db->where('user_id', $user_id);
                            $this->db->update('fp_borrower_user_details',$new_profile_update_data);
                     }else if($doctype=="UBP")
                     {
                        $new_profile_update_data = array( 
                       
                            'pdoc_url' => $fileData['file_name'],
                           
                            );
                            $this->db->where('user_id', $user_id);
                            $this->db->update('fp_borrower_user_details',$new_profile_update_data);
                     }
                   }
                   
                   

                }

				// if(!is_dir($upload_path)){
				// 	mkdir($upload_path, 0777, TRUE);
				// }
        }

        $this->notifyadmin($user_id,$doctype);

    }//----------end of upload

    function upload_multifile()
    {

    }
    function notifyadmin($user='',$doctype='')
    {
        if($user)
        {

        $sql ="select name, email,mobile from fpa_users where id=".$user;
		$borrowerdata= $this->db->query($sql)->row();
        $subject ="Finnup : Document upload Alert! : Admin Action Required";
									$message = "Hello Finnup Admin! <br/><br/>". 
                                    "Borrower  ".$borrowerdata->name.
                                    " has uploaded a ".
                                    $doctype." document in to the system. <br/>".
                                    "Please find the contact details below <br/>".
                                    "Borrower ID :" .$user."<br/>".
                                    "Borrower Name :" .$borrowerdata->name."<br/>".
									"Contact Email :" .$borrowerdata->email."<br/>".
									"Contact Mobile :" .$borrowerdata->mobile."<br/>".									
									"---------------------------------------------------<br/>
									Team Finnup";

									// $to = 'support@finnup.in';
									//$to = 'rec2004@gmail.com';
                                    $to = 'parthiban24242000@gmail.com'; 
									$email = new \SendGrid\Mail\Mail();
									$email->setSubject($subject);
									$email->addContent("text/html", $message);
									$email->setFrom("support@finnup.in", 'FinnUp Team');
									$email->addTo($to);							
									$sendgrid = new \SendGrid("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");
									try {
										$response = $sendgrid->send($email);
									} catch (Exception $e) {
										echo 'Caught exception: ',  $e->getMessage(), "\n";
									}
        }
    }


    function upload_posts()
    {
        $user_id = $this->input->get_request_header('user_id', TRUE);	
        $doctype = $this->input->get_request_header('doctype', TRUE);	
        $description = $this->input->get_request_header('des',TRUE);
        $actives =$this->input->get_request_header('active',TRUE);

        if(!empty($_FILES))
        {
                
                $upload_path = 'uploads/';
				//file upload destination
				$config['upload_path'] = $upload_path;
				//allowed file types. * means all types
				//$config['allowed_types'] = '*';
                $config['allowed_types'] = 'gif|jpg|png|pdf|jpeg';
				//allowed max file size. 0 means unlimited file size
				$config['max_size'] = '0';
				//max file name size
				$config['max_filename'] = '255';
				//whether file name should be encrypted or not
				$config['encrypt_name'] = TRUE;
				$config['file_name'] = 'file_'.time();
                
                $this->load->library('upload',$config);
                $this->upload->initialize($config);

                if($this->upload->do_upload('file'))
                {
                    
                    $fileData = $this->upload->data();
                    $uploadData['borrower_id']  = $user_id;
                    $uploadData['doc_type']     = $doctype;
                    $uploadData['file_name']    = $fileData['file_name'];
                    $uploadData['resdata']      = $user_id;
                    $uploadData['uploaded_at']  = date("Y-m-d H:i:s");
                    $uploadData['description'] = $description;
                    $uploadData['actives'] = $actives;
                    
                    $rowcnt = 0;
                    if($doctype)
                    {
                            if($doctype=='OTHERS' || $doctype=='PANIND' || $doctype=='AADIND')
                            {
                                $insert = $this->Borrowerdocs->insert($uploadData);
                            }else
                            {
                                $rowcnt = $this->Borrowerdocs->getRowsDoctype($user_id,$doctype);
                            }
                    }
                    // if($rowcnt>0) old one 
                    if(FALSE)
                    {
                        $new_profile_update_data = array( 
                       
                            'doc_type' => $doctype,
                            'file_name' => $fileData['file_name'], 
                            'uploaded_at'  => date("Y-m-d H:i:s"),
                           
                            );
                        $this->db->where('user_id', $user_id);
                        $this->db->where('doc_type', $doctype);
                        // $this->db->insert('fp_borrower_docs',$new_profile_update_data);
                        $this->db->update('fp_borrower_docs',$new_profile_update_data);

                    }else
                    {
                        $insert = $this->Borrowerdocs->insert($uploadData);
                    }

                    
                   
                   if($doctype!='' && $doctype !=null)
                   {
                     if($doctype=="GST" && $actives=="1")
                     {
                        $new_profile_update_data = array( 
                       
                            'gst_url' => $fileData['file_name'],
                            'gst'=>$description
                           
                            );

                            $this->db->where('user_id', $user_id);
                            $this->db->update('fp_borrower_user_details',$new_profile_update_data);

                     }else if($doctype=="PAN" && $actives=="1")
                     {
                        $new_profile_update_data = array( 
                       
                            'pan_url' => $fileData['file_name'],
                             'pan'=>$description
                           
                            );
                            $this->db->where('user_id', $user_id);
                            $this->db->update('fp_borrower_user_details',$new_profile_update_data);
                     }else if($doctype=="UBP")
                     {
                        $new_profile_update_data = array( 
                       
                            'pdoc_url' => $fileData['file_name'],
                           
                            );
                            $this->db->where('user_id', $user_id);
                            $this->db->update('fp_borrower_user_details',$new_profile_update_data);
                     }
                   }
                   
                   

                }

				// if(!is_dir($upload_path)){
				// 	mkdir($upload_path, 0777, TRUE);
				// }
        }

        $this->notifyadmin($user_id,$doctype);

    }//----------end of  multiple file  uploaded -----------
    

    
    

}