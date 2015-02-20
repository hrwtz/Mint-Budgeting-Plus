<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Categories extends CI_Controller {

	function __construct(){
        // Call the Model constructor
        parent::__construct();

        // Set content type to json
        $this->output->set_content_type('application/json');
    }

	public function index(){
		
	}

	public function get(){

		$this->load->model('Categories_model');
		$categories = $this->Categories_model->get_categories();

		$results = array();
	
		foreach ($categories as $category) :
			$results[] = array(
				'category_id' => $category->id,
				'name' => $category->value,
				'isL1' => !$category->parent_id,
				'parent_id' => $category->parent_id,
			);
		endforeach;

		$this->output->set_output(json_encode($results));
	}
}

/* End of file categories.php */
/* Location: ./application/controllers/categories.php */