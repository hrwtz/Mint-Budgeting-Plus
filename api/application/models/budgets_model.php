<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Budgets_model extends CI_Model {

    function __construct(){
        // Call the Model constructor
        parent::__construct();
    }

    function insert_budgets($budgets){
        foreach ($budgets as $budget) {
            // Check if a budget exists for the arguments
            $update_where = array(
                'category_id'=> $budget['category_id'],
                'start_date'=>$budget['start_date']
            );
            $query = $this->db->get_where('budgets', $update_where, 1, 0);
            if ($query->num_rows() == 0) {
                // A budget does not exist, insert one.
                $this->db->insert('budgets', $budget);
            } else {
                // A budget does exist, update it.
                $this->db->update('budgets', $budget, $update_where);
            }
        }

    	// $this->db->insert_batch('budgets', $budgets);
        return $this->db->affected_rows() > 0;
    }

    function delete_budget($budget_id){
    	$this->db->delete('budgets', array('id' => intval($budget_id)));
        return $this->db->affected_rows() > 0;
    }

    function get_budgets(/*$category, $start_date, $end_date*/){
        $query = $this->db
            ->select('*')
            ->from('budgets')
            //->where('category_id', $category)
            //->where('start_date >=', $start_date)
            //->where('end_date <=', $end_date)
            ->get();
        $results = $query->result();

        foreach ($results as &$result) :
            // Get Spent Amount
            $query = $this->db
                ->select('SUM(amount)')
                ->from('transactions')
                ->join('categories', 'transactions.category_id = categories.id')
                ->where('(category_id = '.$result->category_id.' OR parent_id = '.$result->category_id.')')
                ->where('date >=', $result->start_date)
                ->where('date <=', $result->end_date)
                ->get();
            $sum = $query->result();
            $sum = get_object_vars($sum[0]);
            $result->spent = $sum['SUM(amount)'];

            // Get Extended transactions
            $query = $this->db
                ->select('*')
                ->from('transactions t')
                ->join('transactions_extended te', 't.id = te.transaction_id')
                ->join('categories c', 't.category_id = c.id')
                ->where('(
                    (t.date >= "'.$result->start_date.'" AND t.date <= "'.$result->end_date.'")
                    OR
                    (te.end_date >= "'.$result->start_date.'" AND te.end_date <= "'.$result->end_date.'")
                    OR
                    ("'.$result->start_date.'" >= t.date AND "'.$result->start_date.'" <= te.end_date)
                )')
                ->where('(t.category_id = '.$result->category_id.' OR c.parent_id = '.$result->category_id.')')
                ->get();
            $result->transactions_extended = $query->result();
        endforeach;

        return $results;
    }

    function get_all_budget_categories($start_date, $end_date){
        $query = $this->db
            ->select('category_id')
            ->distinct('category_id')
            ->get('budgets');
        $results = $query->result();
        $categories = array();
        foreach ($results as $result) :
            $categories[] = $result->category_id;
        endforeach;
        return $categories;
    }

}