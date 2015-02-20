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

	public function get(){
		$this->load->helper('helpers');
		$this->load->model('Transactions_model');

		$transactions = $this->Transactions_model->get_transactions();

		$results = array();
	
		foreach ($transactions as $transaction) :
			$extended = false;
			if ($transaction->end_date){
				$start_date = new DateTime($transaction->date);
				$start_date = $start_date->format('Y-m-d');
				$extended = array(
					'start_date' => $start_date,
					'end_date' => $transaction->end_date,
					'monthCount' => get_months_between_dates($start_date, $end_date)
				);
			}
			$results[] = array(
				'transaction_id' => $transaction->transaction_id,
				'account' => $transaction->account,
				'category_id' => $transaction->category_id,
				'date' => $transaction->date,
				'amount' => $transaction->amount,
				'merchant' => $transaction->merchant,
				'extended' => $extended,
			);
		endforeach;

		$this->output->set_output(json_encode($results));
	}
}

/* End of file transactions.php */
/* Location: ./application/controllers/transactions.php */