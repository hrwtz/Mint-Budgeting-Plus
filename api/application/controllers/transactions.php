<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Transactions extends CI_Controller {

	function __construct(){
        // Call the Model constructor
        parent::__construct();

        // Set content type to json
        $this->output->set_content_type('application/json');
    }

	public function index(){
		
	}

	public function get($start_date, $end_date, $category_id = false){
		$this->load->model('Transactions_model');

		$transactions = $this->Transactions_model->get_transactions($start_date, $end_date, $category_id);

		$results = $this->_transactions_to_results($transactions);

		$this->output->set_output(json_encode($results));

	}

	public function get_by_budget($budgets){
		$this->load->model('Transactions_model');

		$transactions = $this->Transactions_model->get_transactions_by_budget($budgets);

		$results = $this->_transactions_to_results($transactions);

		$this->output->set_output(json_encode($results));
	}

	private function _transactions_to_results($transactions){
		$this->load->helper('helpers');

		$results = array();
		foreach ($transactions as $transaction) :
			$extended = false;
			if ($transaction->end_date){
				$start_date = new DateTime($transaction->date);
				$start_date = $start_date->format('Y-m-d');
				if ($transaction->te_id)
					$extended = array(
						'start_date' => $start_date,
						'end_date' => $transaction->end_date,
						'monthCount' => get_months_between_dates($start_date, $transaction->end_date)
					);
			}
			$results[] = array(
				'transaction_id' => $transaction->id,
				'account' => $transaction->account,
				'category_id' => $transaction->category_id,
				'date' => $transaction->date,
				'amount' => $transaction->amount,
				'merchant' => $transaction->merchant,
				'extended' => $extended,
			);
		endforeach;
		return $results;
	}
}

/* End of file transactions.php */
/* Location: ./application/controllers/transactions.php */