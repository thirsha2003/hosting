<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Menu_permission {
	private $ci;
	function __construct() {
		$this->ci =& get_instance();
    	$this->ci->load->database();
	}

	public function getPermission($role_id_fk){
      $response = array();
      $getParentMenu = $this->ci->db->where('parent_menu_id',0)->get('sp_menu')->result();
        if($getParentMenu){
          foreach ($getParentMenu as $key => $value) {
            $getSubMenu = $this->ci->db->where('parent_menu_id',$value->menu_id)->get('sp_menu')->result();
            if($getSubMenu){
              $childMenu = array();
              foreach ($getSubMenu as $k => $val) {
                $getPermission = $this->ci->db->where('role_id_fk',$role_id_fk)->where('menu_id_fk',$val->menu_id)->get('sp_menu_permission')->row();
                if($getPermission){
                    $is_view = $getPermission->is_view;
                    $is_create = $getPermission->is_create;
                    $is_edit = $getPermission->is_edit;
                    $is_delete = $getPermission->is_delete;
                }else{
                    $is_view = 0;
                    $is_create = 0;
                    $is_edit = 0;
                    $is_delete = 0;
                }

                $childMenu[$k] = array(
                  "menu_id" => $val->menu_id,
                  "menu_name" => $val->menu_name,
                  "url" => $val->url,
                  "icon" => $val->icon,
                  "parent_menu_id" => $val->parent_menu_id,
                  "is_view" => $is_view,
                  "is_create" => $is_create,
                  "is_edit" => $is_edit,
                  "is_delete" => $is_delete,
                );                
              }
            }
            $response[] = array(
              "menu_id" => $value->menu_id,
              "menu_name" => $value->menu_name,
              "url" => $value->url,
              "icon" => $value->icon,
              "child" => isset($childMenu)?$childMenu:[],
            );
          }
        }
      return $response;
    }
    public function savePermission($array,$role_id_fk){
        $this->ci->db->where('role_id_fk', $role_id_fk)->delete('sp_menu_permission');
        foreach ($array as $key => $val) {
             foreach ($val['child'] as $key => $value) {
             $data['menu_id_fk'] = $value['menu_id'];
             $data['role_id_fk'] = $role_id_fk;
             $data['is_view'] = $value['is_view'];
             $data['is_create'] = $value['is_create'];
             $data['is_edit'] = $value['is_edit'];
             $data['is_delete'] = $value['is_delete'];
             $this->ci->db->insert('sp_menu_permission',$data);
            }
        }
        return array('status' => 200,'message' => 'Inserted Successfully');
    }
    public function getaccountsPermission($role_id_fk){
      $response = array();
      $getParentMenu = $this->ci->db->where('parent_menu_id',0)->get('sp_accounts_menu')->result();
        if($getParentMenu){
          foreach ($getParentMenu as $key => $value) {
            $getSubMenu = $this->ci->db->where('parent_menu_id',$value->menu_id)->get('sp_accounts_menu')->result();
            if($getSubMenu){
              $childMenu = array();
              foreach ($getSubMenu as $k => $val) {
                $getPermission = $this->ci->db->where('role_id_fk',$role_id_fk)->where('menu_id_fk',$val->menu_id)->get('sp_accounts_menu_permission')->row();
                if($getPermission){
                    $is_view = $getPermission->is_view;
                    $is_create = $getPermission->is_create;
                    $is_edit = $getPermission->is_edit;
                    $is_delete = $getPermission->is_delete;
                }else{
                    $is_view = 0;
                    $is_create = 0;
                    $is_edit = 0;
                    $is_delete = 0;
                }

                $childMenu[$k] = array(
                  "menu_id" => $val->menu_id,
                  "menu_name" => $val->menu_name,
                  "url" => $val->url,
                  "icon" => $val->icon,
                  "parent_menu_id" => $val->parent_menu_id,
                  "is_view" => $is_view,
                  "is_create" => $is_create,
                  "is_edit" => $is_edit,
                  "is_delete" => $is_delete,
                );                
              }
            }
            $response[] = array(
              "menu_id" => $value->menu_id,
              "menu_name" => $value->menu_name,
              "url" => $value->url,
              "icon" => $value->icon,
              "child" => isset($childMenu)?$childMenu:[],
            );
          }
        }
      return $response;
    }
    public function saveaccountsPermission($array,$role_id_fk){
        $this->ci->db->where('role_id_fk', $role_id_fk)->delete('sp_accounts_menu_permission');
        foreach ($array as $key => $val) {
             foreach ($val['child'] as $key => $value) {
             $data['menu_id_fk'] = $value['menu_id'];
             $data['role_id_fk'] = $role_id_fk;
             $data['is_view'] = $value['is_view'];
             $data['is_create'] = $value['is_create'];
             $data['is_edit'] = $value['is_edit'];
             $data['is_delete'] = $value['is_delete'];
             $this->ci->db->insert('sp_accounts_menu_permission',$data);
            }
        }
        return array('status' => 200,'message' => 'Inserted Successfully');
    }
}
