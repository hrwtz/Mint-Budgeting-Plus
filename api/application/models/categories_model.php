<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Categories_model extends CI_Model {

    function __construct(){
        // Call the Model constructor
        parent::__construct();
    }

    public function get_category_name($category_id){
    	$query = $this->db
    		->get_where('categories', array('id' => intval($category_id)), 1);
    	return $query->result();
    }

    public function get_categories(){
    	$query = $this->db->get('categories');
    	return $query->result();
    }

}