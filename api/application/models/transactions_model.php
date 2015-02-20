<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Transactions_model extends CI_Model {

    function __construct(){
        // Call the Model constructor
        parent::__construct();
    }

    public function get_transactions(){
    	$query = $this->db
            ->select('*')
            ->from('transactions')
            ->join('transactions_extended', 'transactions.id = transactions_extended.transaction_id', 'left outer')
            ->get();
    	return $query->result();
    }

}