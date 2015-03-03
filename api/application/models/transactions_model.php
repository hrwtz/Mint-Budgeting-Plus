<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Transactions_model extends CI_Model {

    function __construct(){
        // Call the Model constructor
        parent::__construct();
    }

    public function get_transactions($start_date, $end_date, $category_id){
        $start_date = $this->db->escape($start_date);
        $end_date = $this->db->escape($end_date);
        $category_id = $this->db->escape($category_id);


        $query = $this->db
            ->select('transactions.*,transactions_extended.id as te_id,transactions_extended.end_date')
            ->from('transactions')
            ->join('categories', 'transactions.category_id = categories.id')
            ->join('transactions_extended', 'transactions.id = transactions_extended.transaction_id', 'left outer')            
            ->where('(
                    transactions.date >= '.$start_date .'
                    AND
                    transactions.date <= '.$end_date.'
            )')
            ->or_where('(
                    (transactions.date >= '.$start_date.' AND transactions.date <= '.$end_date.')
                    OR
                    (transactions_extended.end_date >= '.$start_date.' AND transactions_extended.end_date <= '.$end_date.')
                    OR
                    ('.$start_date.' >= transactions.date AND '.$start_date.' <= transactions_extended.end_date)
            )');
        if ($category_id)  
            $query = $query->where('(category_id = '.$category_id.' OR parent_id = '.$category_id.')');
        $query = $query->get();

        return $query->result();
    }

    public function get_transactions_by_budget($budgets){
        // Escape budgets input
        $budgets_array = explode(',', $budgets);
        foreach ($budgets_array as &$budget) :
            $budget = intval($budget);
        endforeach;
        $budgets = implode(',', $budgets_array);

        $qry = "SELECT transactions.*,transactions_extended.id as te_id,transactions_extended.end_date
            FROM transactions
            JOIN categories on transactions.category_id = categories.id
            JOIN budgets
            LEFT OUTER JOIN transactions_extended on (transactions.id = transactions_extended.transaction_id)
            WHERE 
            (transactions.category_id = budgets.category_id OR categories.parent_id = budgets.category_id)
            AND budgets.id IN (?)
            AND (
                (
                    transactions.date >= budgets.start_date 
                    AND
                    transactions.date <= budgets.end_date
                ) 
                OR
                (
                    (transactions.date >= budgets.start_date AND transactions.date <= budgets.end_date)
                    OR
                    (transactions_extended.end_date >= budgets.start_date AND transactions_extended.end_date <= budgets.end_date)
                    OR
                    (budgets.start_date >= transactions.date AND budgets.start_date <= transactions_extended.end_date)
                )

            )
            ORDER BY transactions.date";
        $query = $this->db->query($qry, $budgets);
        return $query->result();
    }

}