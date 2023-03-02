<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class BorrowerDocs extends CI_Model
{
    function __construct()
    {
        $this->tableName ="fp_borrower_docs";
    }

    public function getRows($id='')
    {
        $this->db->select('id,borrower_id, doc_type, file_name, uploaded_at');
        $this->db->from('fp_borrower_dcos');
        if($id)
        {
            $this->db->where('id',$id);
            $q=$this->db->get();
            $result=$q->row_array();
        }else
        {
            $this->db->order_by('uploaded_at','desc');
            $q=$this->db->get();
            $result=$q->result_array();

        }

        return !empty($result)?$result:false;
    }

    public function insert($data=array())
    {
        $insert = $this->db->insert('fp_borrower_docs',$data);
        return $insert?true:false;

    }
    public function getRowsDoctype($id='', $doctype='')
    {
        $this->db->select('id,borrower_id, doc_type, file_name, uploaded_at');
        $this->db->from('fp_borrower_docs');
        if($id)
        {
            $this->db->where('borrower_id',$id);
            $this->db->where('doc_type',$doctype);
            $q=$this->db->get();
            $result=$q->num_rows();
            
        }
        return !empty($result)?$result:0;
    }
}