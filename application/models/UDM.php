<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class UDM extends CI_Model
{
	public function get_val($select,$array,$table)
	{
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
	public function insert($table,$value)
	{
		$this->db->insert($table,$value);
		return $this->db->insert_id();
	}
	public function update($arr,$table,$value)
	{
		$this->db->where($arr);
		$this->db->update($table, $value); 
	}
	public function delete($arr,$table)
	{
		$this->db->where($arr);
		return $this->db->delete($table); 
	}
	public function empty_table($table)
	{
		$this->db->empty_table($table);
	}
	public function result_count($table_name)
	{
		$this->db->from($table_name);
		return $this->db->count_all_results();
	}	
	public function page_records($limit,$start,$table,$order,$order_type)
	{
		$this->db->limit($limit,$start);
		$this->db->order_by($order,$order_type);
        $result = $this->db->get($table);
        if ($result->num_rows() > 0) 
        {
            return $result->result_array();
        }
        return false;
	}	
	public function select($select,$table,$order,$order_type)
	{	
		$this->db->select($select);
		$this->db->from($table);
		$this->db->order_by($order,$order_type);
		$query = $this->db->get();
		if($query->num_rows()>0)
		{
			return $query->result_array();
		}
		else
		{
			return false;
		}
	}
	public function select_where_one($select,$table,$where)
	{
		$this->db->select($select);
		$this->db->from($table);
		$this->db->where($where);
		$query = $this->db->get();
		if($query->num_rows()==1)
		{
			return $query->result_array();
		}
		else
		{
			return false;
		}
	}
	public function select_where_multi($select,$table,$where,$order,$order_type)
	{
		$this->db->select($select);
		$this->db->from($table);
		$this->db->where($where);
		$this->db->order_by($order,$order_type);
		$query = $this->db->get();
		if($query->num_rows()>0)
		{
			return $query->result_array();
		}
		else
		{
			return false;
		}
	}
	public function select_where_multi_group($select,$table,$where,$order,$order_type,$group_by)
	{
		$this->db->select($select);
		$this->db->from($table);
		$this->db->where($where);
		$this->db->group_by($group_by);
		$this->db->order_by($order,$order_type);
		$query = $this->db->get();
		if($query->num_rows()>0)
		{
			return $query->result_array();
		}
		else
		{
			return false;
		}
	}
	public function select_where_multi_limit($select,$table,$where,$order,$order_type,$limit)
	{
		$this->db->select($select);
		$this->db->from($table);
		$this->db->where($where);
		$this->db->order_by($order,$order_type);
		$this->db->limit($limit);
		$query = $this->db->get();
		if($query->num_rows()>0)
		{
			return $query->result_array();
		}
		else
		{
			return false;
		}
	}
	public function select_multi_limit($select,$table,$order,$order_type,$limit)
	{
		$this->db->select($select);
		$this->db->from($table);
		$this->db->order_by($order,$order_type);
		$this->db->limit($limit);
		$query = $this->db->get();
		if($query->num_rows()>0)
		{
			return $query->result_array();
		}
		else
		{
			return false;
		}
	}
	public function joinq($select,$table1,$table2,$join,$type,$where,$order,$order_type)
	{
		$this->db->select($select);
		$this->db->from($table1);
		$this->db->join($table2,$join,$type);
		$this->db->where($where);
		$this->db->order_by($order,$order_type);
		$query=$this->db->get();
		if($query->num_rows()>0)
		{
			return $query->result_array();
		}
		else
		{
			return false;
		}
	}
	public function joing($select,$table1,$table2,$join,$type,$where,$group_by,$order,$order_type)
	{
		$this->db->select($select);
		$this->db->from($table1);
		$this->db->join($table2,$join,$type);
		$this->db->where($where);
		$this->db->group_by($group_by);
		$this->db->order_by($order,$order_type);	
		$query=$this->db->get();
		if($query->num_rows()>0)
		{
			return $query->result_array();
		}
		else
		{
			return false;
		}
	}
	public function groupby($select,$table,$group_by,$order,$order_type)
	{
		$this->db->select($select);
		$this->db->from($table);
		$this->db->group_by($group_by);
		$this->db->order_by($order,$order_type);	
		$query=$this->db->get();
		if($query->num_rows()>0)
		{
			return $query->result_array();
		}
		else
		{
			return false;
		}
	}
	public function query($qry)
	{
		$query = $this->db->query($qry);
		if($query->num_rows()>0)
		{
			return $query->result_array();
		}
		else
		{
			return false;
		}
	}
	public function run_query($qry)
	{
		$this->db->query($qry);
	}
}
?>