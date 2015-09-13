<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Mint extends CI_Controller {

	public function index(){
		$this->config->load('mint');
		$this->load->model('Insert_model');
		try {
			$this->load->library('phpmintapi', array(
				'email' => $this->config->item('username'),
				'password' => $this->config->item('password'),
				'cookieFilePath' => FCPATH . 'cookie.txt'
			));
		} catch(Exception $e) {
			echo 'Caught exception: ' .  $e->getMessage() . "\n";
			exit;
		}

		try {
			$token = $this->phpmintapi->connect();
		} catch(Exception $e) {
			echo 'Caught exception: ' .  $e->getMessage() . "\n";
			exit;
		}

	// Keep making calls to get transcations until we have hit the end
		$offset = 0;
		$transactions = array();
		$formated_transactions = array();

		while (1) :
			$query = array(
				'queryNew' => '',
		        'offset' => $offset,
		        'filterType' => 'cash',
		        'acctChanged' => 'T',
		        'task' => 'transactions',
			);
			$data = $this->phpmintapi->getMintData($query);
			if (!$data){
				throw new Exception('No transactions');
				return;
			}
			$txns = $data->set[0]->data;
			if (!$txns) 
				break;
			$transactions = array_merge($transactions, $txns);
			$offset += sizeof($txns);
		endwhile;

		// Format Data
		foreach ($transactions as $transaction) {
			if (!$transaction->isSpending || $transaction->isDuplicate)
				continue;


			$transactionAmount = preg_replace("/[^0-9\.]/", "",$transaction->amount);
			if (!$transaction->isDebit) { $transactionAmount = -$transactionAmount; }
			$formated_transactions[] = array(
				'id' => $transaction->id,
				'account'  => $transaction->account,
				'category_id' => $transaction->categoryId,
				'date' => date('Y-m-d', strtotime($transaction->date)),
				'amount' => $transactionAmount,
				'merchant' => $transaction->merchant
				);
		}

		$this->Insert_model->insert_transactions($formated_transactions);

		$data = $this->phpmintapi->getMintData(array('task' => 'categories'));
		$categories = array();

		foreach ($data->set[0]->data as $category_parent) :
			$categories[] = array(
				'id' => $category_parent->id,
				'value' => $category_parent->value,
				'parent_id' => 0,
			);
			foreach ($category_parent->children as $category_child) :
				$categories[] = array(
					'id' => $category_child->id,
					'value' => $category_child->value,
					'parent_id' => $category_parent->id,
				);
			endforeach;
		endforeach;
		$this->Insert_model->insert_categories($categories);
	}
}

/* End of file mint.php */
/* Location: ./application/controllers/mint.php */