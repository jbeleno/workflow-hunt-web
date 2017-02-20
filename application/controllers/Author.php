<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Author extends CI_Controller {

	public function __construct()
    {
        parent::__construct();
        $this->load->model('author_model');
    }

    public function insert_users_ids()
	{
		$response = $this->author_model->insert_users_ids();

		$this->output
	         ->set_content_type('application/json')
	         ->set_output(json_encode($response));
	}

	public function update_user_metadata()
	{
		$response = $this->author_model->update_user_metadata();

		$this->output
	         ->set_content_type('application/json')
	         ->set_output(json_encode($response));
	}


}

/* End of file Author.php */
/* Location: ./application/controllers/Author.php */