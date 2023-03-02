<?php
class Common_model extends CI_Model{
	/* login */
	public function check_user($username, $password){
		  $this->db->where('username',$username);
		  $this->db->where('password',$password);
		  $this->db->where('user.status',1);
		  $this->db->join('role','role.role_id = user.role_id');
		  return $this->db->get('user')->row();
	}

	public function queries($options){
		$sql = '';
		if($options['option'] == 'select'){
			$sql .= "SELECT * FROM ".$options['table']." WHERE 1=1";
			if(isset($options['where'])){
				 if(is_array($options['where'])){
					 foreach($options['where'] as $key => $val){
						 $sql .= " AND ".$key." = '".$val."'";
					 }
				 }
		    }
		}

		if($options['option'] == 'update'){
			if(is_array($options['where'])){
				foreach($options['where'] as $key => $val){
					$this->db->where($key,$val);
				}
			}
			return $this->db->update($options['table'], $options['data']); 
		}
		if($options['option'] == 'insert'){
			$this->db->insert($options['table'], $options['data']);
            return $this->db->insert_id();
		}
		if($options['option'] == 'delete'){
			if(is_array($options['where'])){
				foreach($options['where'] as $key => $val){
					$this->db->where($key,$val);
				}
			}
			return $this->db->delete($options['table']); 
		}
		if($sql){
			if($options['type'] == 'row'){
			   return $this->db->query($sql)->row();
			}else{
			   return $this->db->query($sql)->result();
			}
		}
	}

	public function get_val($select,$array,$table){
		$this->db->select($select);
		$this->db->where($array);
		$this->db->from($table);
		$query = $this->db->get();
		if($query->num_rows()==1)
		{
			$data=$query->row_array();
			return $value=$data[$select];
		}
		else
		{
			return false;
		}
    }
	public function get_val_multiple($col_get,$where,$tbl){

		$this->db->select($col_get);
		if(is_array($where)){
			foreach ($where as $key => $value){
				$this->db->where($key,$value);
			}
		}
		$this->db->from($tbl);
		$query = $this->db->get();
		if($query->num_rows()==1)
		{
			$data=$query->row_array();
			return $data[$col_get];
		}
		else
		{
			return false;
		}
	}
}
