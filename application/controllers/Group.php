<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Group extends CI_Controller {

	public function __construct()
    {
        parent::__construct();
        $this->load->model('group_model');
    }

    public function insert_groups_ids()
	{
		$response = $this->group_model->insert_groups_ids();

		$this->output
	         ->set_content_type('application/json')
	         ->set_output(json_encode($response));
	}

	public function update_group_metadata()
	{
		$response = $this->group_model->update_group_metadata();

		$this->output
	         ->set_content_type('application/json')
	         ->set_output(json_encode($response));
	}

}

/* End of file Group.php */
/* Location: ./application/controllers/Group.php */