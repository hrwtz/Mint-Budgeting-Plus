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
        return $results;
    }

    function get_budget_spent($category, $start_date, $end_date){
        $query = $this->db
            ->select('SUM(amount)')
            ->from('transactions')
            ->join('categories', 'transactions.category_id = categories.id')
            ->where('(category_id = '.$category.' OR parent_id = '.$category.')')
            ->where('date >=', $start_date)
            ->where('date <=', $end_date)
            ->get();
        $sum = $query->result();
        $sum = get_object_vars($sum[0]);
        return $sum['SUM(amount)'];
    }

    // Gets categories for transactions in everything else budget
    function get_else_categories($not_category, $start_date, $end_date){
        $query = $this->db
            ->select('category_id')
            ->from('transactions')
            ->join('categories', 'transactions.category_id = categories.id')
            ->where_not_in('category_id', implode(',', $not_category))
            ->where_not_in('parent_id', implode(',', $not_category))
            ->where('date >=', $start_date)
            ->where('date <=', $end_date)
            ->group_by('category_id')
            ->order_by('category_id')
            ->get();
        $results = $query->result();
        $categories = [];
        foreach ($results as $result) :
            $categories[] = $result->category_id;
        endforeach;

        return $categories;
    }

    function get_budget_extended($category, $start_date, $end_date){
         $query = $this->db
            ->select('*')
            ->from('transactions t')
            ->join('transactions_extended te', 't.id = te.transaction_id')
            ->join('categories c', 't.category_id = c.id')
            ->where('(
                (t.date >= "'.$start_date.'" AND t.date <= "'.$end_date.'")
                OR
                (te.end_date >= "'.$start_date.'" AND te.end_date <= "'.$end_date.'")
                OR
                ("'.$start_date.'" >= t.date AND "'.$start_date.'" <= te.end_date)
            )')
            ->where('(t.category_id = '.$category.' OR c.parent_id = '.$category.')')
            ->get();
        $results = $query->result();
        return $query->result();
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