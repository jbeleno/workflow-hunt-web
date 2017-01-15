<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Semanticannotation extends CI_Controller {

	public function __construct()
    {
        parent::__construct();
        $this->load->model('semantic_annotation_model');
    }

	public function annotate_workflows()
	{
		$response = $this->semantic_annotation_model->annotate_workflows();

		$this->output
	         ->set_content_type('application/json')
	         ->set_output(json_encode($response));
	}

}

/* End of file Semanticannotation.php */
/* Location: ./application/controllers/Semanticannotation.php */