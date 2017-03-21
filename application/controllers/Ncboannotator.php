<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ncboannotator extends CI_Controller {

	public function __construct()
    {
        parent::__construct();
        $this->load->model('ncboannotator_model');
    }

	public function annotate()
	{
		$response = $this->ncboannotator_model->annotate_metadata();

		$this->output
	         ->set_content_type('application/json')
	         ->set_output(json_encode($response));
	}

}

/* End of file Ncboannotator.php */
/* Location: ./application/controllers/Ncboannotator.php */