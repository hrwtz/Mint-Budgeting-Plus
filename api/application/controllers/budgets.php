<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Budgets extends CI_Controller {

	function __construct(){
        // Call the Model constructor
        parent::__construct();

        // Set content type to json
        $this->output->set_content_type('application/json');
    }

	public function index(){
		
	}

	// Adds or updates budgets
	public function add(){
		// Get arguments
		$default_args = array(
			'start' => date('Ym'),
			'end' => date('Ym'),
			'category_id' => 0,
			'amount' => 0,
			'is_rollover' => false
		);
		// UPDATE TO PUT
		$user_args = $this->input->get(NULL, TRUE) ? $this->input->get(NULL, TRUE) : array();
		$args = array_merge($default_args, $user_args);

		// Throw any warnings
		if ($this->_add_warning($args)) :
			$warn = $this->_add_warning($args);
			$this->output->set_output(json_encode(array('success' => false,'error' => $warn)));
			return;
		endif;

		// Create insert value for each month
		$start = $month = strtotime($args['start'] . '01');
		$end = strtotime($args['end'] . '01');
		$current = strtotime(date('Y-m-1'));
		$budgets = array();

		while($month <= $end && $month <= $current) :
		    $budgets[] = array(
		    	'category_id' => $args['category_id'],
		    	'start_date' => date('Y-m-01', $month),
		    	'end_date' => date('Y-m-t', $month),
		    	'is_rollover' => !!$args['is_rollover'],
		    	'amount' => number_format((float) $args['amount'], 2)
		    );
		    $month = strtotime("+1 month", $month);
		endwhile;

		// Insert budgets
		$this->load->model('Budgets_model');
		$inserted_budgets = $this->Budgets_model->insert_budgets($budgets);
		$success = !!$inserted_budgets;

		// Output json
		$this->output->set_output(json_encode(array('success' => $success)));
	}

	protected function _add_warning($args){
		// Check that category exists
		$this->load->model('Categories_model');
		$category_name = $this->Categories_model->get_category_name($args['category_id']);
		if ( !$category_name ) :
			return 'Category does not exist';
		endif;

		// Check that start and end dates are dates
		if (!strtotime($args['start'] . '01')) :
			return 'Start date is not a valid date';
		endif;

		if (!strtotime($args['end'] . '01')) :
			return 'End date is not a valid date';
		endif;

		// Check that Start date is this or previous month
		if (strtotime($args['start'] . '01') > strtotime(date('Ym01'))) :
			return 'Start date is in the future';
		endif;

		// Check end date is after start date
		if ($args['start'] > $args['end']) :
			return 'End date is before start date';
		endif;

		// Check if start date is before any transactions in the DB
	}

	public function delete(){
		// UPDATE TO DELETE
		$user_args = $this->input->get(NULL, TRUE);
		$budget_id = $user_args['id'];
		$error = false;
		if ($budget_id) :
			$this->load->model('Budgets_model');
			$deleted_budgets = $this->Budgets_model->delete_budget($budget_id);	
			$success = !!$deleted_budgets;
		else :
			$error = 'Budget ID does not exist';
			$success = false;
		endif;
		
		$this->output->set_output(json_encode(array('success'=>$success,'error' => $error)));
	}

	public function update(){
		
	}

	public function get(){
		$this->load->helper('helpers');
		$this->load->model('Budgets_model');

		$results = array();
		$months = array();
		
		// Get Budgets
		$budgets = $this->Budgets_model->get_budgets();
		
		foreach ($budgets as $budget) :
			// Get spent amount and extended transactions
			$budget->spent = $this->Budgets_model->get_budget_spent($budget->category_id, $budget->start_date, $budget->end_date);
			$budget->transactions_extended = $this->Budgets_model->get_budget_extended($budget->category_id, $budget->start_date, $budget->end_date);


			$extended_amount = 0;
			$minus_transaction = 0;
			foreach ($budget->transactions_extended as $transaction_extended) :
				// get months amount between two dates
				$month_count = get_months_between_dates($transaction_extended->date, $transaction_extended->end_date);
				$amount = $transaction_extended->amount;
				$extended_amount -= ($amount / $month_count);
				// If transaction was in budget month, subtract cost of transaction
				if ($transaction_extended->date >= $budget->start_date && $transaction_extended->date <= $budget->end_date)
					$minus_transaction += $amount;
			endforeach;


			$months[] = array(
				'date' => $budget->start_date,
				'category_id' => $budget->category_id,
				'budget_id' => $budget->id,
				'budgeted_amount' => +$budget->amount,
				'spent_amount' => $budget->spent ? +$budget->spent - $minus_transaction : - $minus_transaction,
				'rollover_amount' => 0,
				'extended_amount' => +sprintf('%0.2f', $extended_amount),
				'is_rollover' => !!$budget->is_rollover
			);
		endforeach;

		foreach ($months as $month) :
			if (!isset($results[$month['date']])) :
				$results[$month['date']] = array();
			endif;
			$results[$month['date']][] = $month;
		endforeach;


		ksort($results);

		// Get rollover amount
		$previous_value = null;
		// For each month grouping
		foreach ($results as &$month) :
			// For each budget in month grouping
			foreach ($month as &$budget) :
				// If budget isn't rollover, skip
				if (!$budget['is_rollover']) :
					continue;
				endif;

				// If previous month grouping exists
				if ($previous_value) :
					$previous_same_budget = null;
					// Get previous budget with same category
					foreach ($previous_value as $previous_budget) :
						if ($previous_budget['category_id'] == $budget['category_id']) :
							$previous_same_budget = $previous_budget;
							break;
						endif; 
					endforeach;
					// If previous month was rollover, add to amount
					if ($previous_same_budget['is_rollover']) :
						$budget['rollover_amount'] = $previous_same_budget['budgeted_amount'] + $previous_same_budget['extended_amount'] - $previous_same_budget['spent_amount'] + $previous_same_budget['rollover_amount'];
					endif;
				endif;
			endforeach;
		$previous_value = $month;
		endforeach;

		// Do everything else budget if exists
		foreach ($results as &$month) :
			foreach ($month as &$budget) :
				if ($budget['category_id'] === '0') :
					$budget['spent_amount'] = 0;

					$used_categories = [];

					// Get categories already used
					foreach ($month as $get_budget) :
						if ($get_budget['category_id'] !== '0') :
							$used_categories[] = $get_budget['category_id'];
						endif;
					endforeach;

					// Get categories for transactions in everything else
					$budget['else'] = $this->Budgets_model->get_else_categories($used_categories, $get_budget['date'], get_end_of_month($get_budget['date']));

					// get spent amount
					$else_spent_total = 0;
					foreach ($budget['else'] as &$cat) :
						$else_spent = $this->Budgets_model->get_budget_spent($cat, $get_budget['date'], get_end_of_month($get_budget['date']));
						$cat = array(
							'category_id' => $cat,
							'spent' => $else_spent
						);
						$else_spent_total += $else_spent;
					endforeach;

					$budget['spent_amount'] = $else_spent_total;

					break;
				endif;
			endforeach;
		endforeach;

		// Get total amounts
		foreach ($results as &$month) :
			$total_spent = 0;
			$total_budgeted = 0;
			foreach ($month as $budgeted_month) :
				$total_spent += $budgeted_month['spent_amount'] - $budgeted_month['rollover_amount'] - $budgeted_month['extended_amount'];
				$total_budgeted += $budgeted_month['budgeted_amount'];
			endforeach;
			$month['totals'] = array(
				'spent_amount' => $total_spent,
				'budgeted_amount' => $total_budgeted,
			);
		endforeach;

		// Only display latest 12 months
		$results = array_slice($results, -12);

		$this->output->set_output(json_encode($results));
	}

}

/* End of file budgets.php */
/* Location: ./application/controllers/budgets.php */