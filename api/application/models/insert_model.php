<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Insert_model extends CI_Model {

    function __construct(){
        // Call the Model constructor
        parent::__construct();
    }

    function insert_categories($categories){
        $this->db->empty_table('categories');
        $this->db->insert_batch('categories', $categories);
    }

    function insert_transactions($transactions){
        $this->db->empty_table('transactions');
        $this->db->insert_batch('transactions', $transactions);
    }

}