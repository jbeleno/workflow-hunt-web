<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Web extends CI_Controller {

	public function __construct()
    {
        parent::__construct();
        $this->load->model('elasticsearch_model');
    }

	public function index()
	{
		$this->load->view('web/home');
	}

	public function results(){
		$query = @$this->input->get('query', TRUE);
		$offset = @$this->uri->segment(3);
		$offset = ($offset == '')? 0 : $offset;

		$response = $this->elasticsearch_model->search_in_metadata($query, $offset);

		$data['query'] = @$query;
		$data['status'] = @$response['status'];
		$data['results'] = @$response['results'];
		$data['total'] = @$response['total'];
		$data['msg'] = @$response['msg'];

		$this->load->library('pagination');

		$config['base_url'] = base_url().'index.php/web/results/';
		$config['total_rows'] = ($data['total'] == '')? 0 : $data['total'];
		$config['per_page'] = 10;
		$config['reuse_query_string'] = true;
		$config['num_tag_open'] = '<li class="page-item">';
		$config['num_tag_close'] = '</li>';
		$config['first_tag_open'] = '<li class="page-item">';
		$config['first_tag_close'] = '</li>';
		$config['last_tag_open'] = '<li class="page-item">';
		$config['last_tag_close'] = '</li>';
		$config['next_tag_open'] = '<li class="page-item">';
		$config['next_tag_close'] = '</li>';
		$config['prev_tag_open'] = '<li class="page-item">';
		$config['prev_tag_close'] = '</li>';
		$config['cur_tag_open'] = '<li class="page-item">';
		$config['cur_tag_close'] = '</li>';

		$this->pagination->initialize($config);

		$data['pagination'] = $this->pagination;

		$this->load->view('web/results', $data);
	}

}
