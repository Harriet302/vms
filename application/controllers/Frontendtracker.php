<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Frontendtracker extends CI_Controller {

	 function __construct()
     {
          parent::__construct();
          $this->load->database();
          $this->load->model('drivers_model');
          $this->load->helper(array('form', 'url','string'));
          $this->load->library('form_validation');
          $this->load->library('session');
     }

	public function index()
	{
		$this->load->model('consignments_model');
		$data['vechiclelist'] = $this->consignments_model->getall_vechicle();
		$this->load->view('frontendtracker',$data);
	}
	
}
