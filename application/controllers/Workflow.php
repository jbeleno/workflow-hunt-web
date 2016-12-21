<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Workflow extends CI_Controller {

	public function __construct()
    {
        parent::__construct();
        $this->load->model('workflow_model');
    }

	public function collect_metadata()
	{
		//$this->workflow_model->insert_workflow_ids(50);
		$this->workflow_model->update_workflow_metadata();

		$this->output
	         ->set_content_type('application/json')
	         ->set_output(json_encode(array('status' => 'OK')));
	}

}

/* End of file Workflow.php */
/* Location: ./application/controllers/Workflow.php */