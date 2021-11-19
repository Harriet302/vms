<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Triptracking extends CI_Controller {

	 function __construct()
     {
          parent::__construct();
          $this->load->database();
          $this->load->helper(array('form', 'url','string'));
          $this->load->library('form_validation');
          $this->load->library('session');
     }

	public function index()
	{
		$trackcode = $this->uri->segment(2);
		$this->load->model('consignments_model');
		$consignmentdetails = $this->consignments_model->getall_consignments($trackcode);
		$data['consignmentdetails'] = (isset($consignmentdetails[0])?$consignmentdetails[0]:array());
		$this->load->view('triptracking',$data);
	}
	 
}
