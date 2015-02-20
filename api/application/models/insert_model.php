<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Insert_model extends CI_Model {

    function __construct(){
        // Call the Model constructor
        parent::__construct();
    }

    function insert_categories($categories){
        $categories_ready = array();
        foreach ($categories as $category) {
            $categories_ready[] = array(
                'id' => $category->id,
                'value' => $category->value,
                'parent_id' => false
            );
            foreach ($category->children as $sub_category) {
                $categories_ready[] = array(
                    'id' => $sub_category->id,
                    'value' => $sub_category->value,
                    'parent_id' => $category->id
                );
            }
        }
        $this->db->insert_batch('categories', $categories_ready);
    }

    function insert_transactions($transactions){
        $transactions = $transactions->set[0]->data;
        foreach ($transactions as $transaction) {
            if ($transaction->isDuplicate || $transaction->isPending || $transaction->isTransfer)
                continue;
            $transactions_ready[] = array(
                'account' => $transaction->account,
                'category_id' => $transaction->categoryId,
                'date' => date("Y-m-d H:i:s", strtotime($transaction->date)),
                'amount' => str_replace('$', '', $transaction->amount),
                'merchant' => $transaction->merchant,
            );
        }
        $this->db->insert_batch('transactions', $transactions_ready);
    }
    


}