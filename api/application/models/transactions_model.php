<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Transactions_model extends CI_Model {

    function __construct(){
        // Call the Model constructor
        parent::__construct();
    }

    public function get_transactions($start_date, $end_date, $category_id){
        $start_date = $this->db->escape($start_date);
        $end_date = $this->db->escape($end_date);
        $category_id = intval($category_id);

        $query = $this->db
            ->select('transactions.*,transactions_extended.id as te_id,transactions_extended.end_date')
            ->from('transactions')
            ->join('categories', 'transactions.category_id = categories.id')
            ->join('transactions_extended', 'transactions.id = transactions_extended.transaction_id', 'left outer')            
            ->where('((
                    transactions.date >= '.$start_date .'
                    AND
                    transactions.date <= '.$end_date.'
            ) OR (
                    (transactions.date >= '.$start_date.' AND transactions.date <= '.$end_date.')
                    OR
                    (transactions_extended.end_date >= '.$start_date.' AND transactions_extended.end_date <= '.$end_date.')
                    OR
                    ('.$start_date.' >= transactions.date AND '.$start_date.' <= transactions_extended.end_date)
            ))');
        if ($category_id !== 0)
            $query = $query->where('(category_id = '.$category_id.' OR parent_id = '.$category_id.')');
        $query = $query->get();

        return $query->result();
    }

}