<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Consignments extends CI_Controller {

	 function __construct()
     {
          parent::__construct();
          $this->load->database();
          $this->load->model('consignments_model');
          $this->load->model('customer_model');	
          $this->load->model('drivers_model');	
          $this->load->helper(array('form', 'url','string'));
          $this->load->library('form_validation');
          $this->load->library('session');
     }

	public function index()
	{
		$data['consignmentslist'] = $this->consignments_model->getall_consignments();
		$this->template->template_render('consignments_management',$data);
	}

	public function addconsignments()
	{
		$data['customerlist'] = $this->consignments_model->getall_customer();
		$data['vechiclelist'] = $this->consignments_model->getall_vechicle();
		$data['driverlist'] = $this->consignments_model->getall_driverlist();
		$this->template->template_render('consignments_add', $data);
	}

	public function insertconsignments() 
	{
		$testxss = xssclean($_POST);
		if($testxss){
			$response = $this->consignments_model->add_consignments($this->input->post());
			$bookingemail = $this->input->post('bookingemail');
			if(isset($bookingemail)) {
				$this->sendtripemail($this->input->post());
			}
			if($response) {
				$this->session->set_flashdata('successmessage', 'New Consignment added successfully..');
			} else {
				$this->session->set_flashdata('warningmessage', 'Unexpected error..Try again');
			}
			redirect('consignments');
		} else {
			$this->session->set_flashdata('warningmessage', 'Error! Your input are not allowed.Please try again');
			redirect('consignments');
		}
	}
	public function editconsignment()
	{
		$data['customerlist'] = $this->consignments_model->getall_customer();
		$data['vechiclelist'] = $this->consignments_model->getall_vechicle();
		$data['driverlist'] = $this->consignments_model->getall_driverlist();
		$t_id = $this->uri->segment(3);
		$data['consignmentdetails'] = $this->consignments_model->get_consignmentdetails($t_id);
		
		$this->template->template_render('consignments_add',$data);
	}

	public function updateconsignments()
	{
		$testxss = xssclean($_POST);
		if($testxss){
			$response = $this->consignments_model->update_consignments($this->input->post());
			if($response) {
				$this->session->set_flashdata('successmessage', 'Consignment Updated successfully..');
			} else {
				$this->session->set_flashdata('warningmessage', 'Unexpected error..Try again');
			}
			redirect('consignments');
		} else {
			$this->session->set_flashdata('warningmessage', 'Error! Your input are not allowed.Please try again');
			redirect('consignments');
		}
	}
	public function details()
	{
		$data = array();
		$b_id = $this->uri->segment(3);
		$consignmentdetails = $this->consignments_model->get_consignmentdetails($b_id);
		if(isset($consignmentdetails[0]['t_id'])) {
			$customerdetails = $this->customer_model->get_customerdetails($consignmentdetails[0]['t_customer_id']);
			$driverdetails = $this->drivers_model->get_driverdetails($consignmentdetails[0]['t_driver']);
			$data['paymentdetails'] = $this->consignments_model->get_paymentdetails($consignmentdetails[0]['t_id']);
			$data['consignmentdetails'] = $consignmentdetails[0];
			$data['customerdetails'] = (isset($customerdetails[0]['c_id']))?$customerdetails[0]:'';
			$data['driverdetails'] =  (isset($driverdetails[0]['d_id']))?$driverdetails[0]:'';
		}
		$this->template->template_render('consignments_details',$data);
	}
	public function invoice()
	{
		$data = array();
		$b_id = $this->uri->segment(3);
		$consignmentdetails = $this->consignments_model->get_consignmentdetails($b_id);
		if(isset($consignmentdetails[0]['t_id'])) {
			$customerdetails = $this->customer_model->get_customerdetails($consignmentdetails[0]['t_customer_id']);
			$driverdetails = $this->drivers_model->get_driverdetails($consignmentdetails[0]['t_driver']);
			$data['paymentdetails'] = $this->consignments_model->get_paymentdetails($consignmentdetails[0]['t_id']);
			$data['consignmentdetails'] = $consignmentdetails[0];
			$data['customerdetails'] = (isset($customerdetails[0]['c_id']))?$customerdetails[0]:'';
			$data['driverdetails'] =  (isset($driverdetails[0]['d_id']))?$driverdetails[0]:'';
		}
		$this->load->view('invoice',$data);
	}
	public function trippayment()
	{
		$pyment = $this->input->post();
		$this->db->insert('trip_payments',$pyment);
		if($this->db->insert_id()) {
			$addincome = array('ie_v_id'=>$this->input->post('tp_v_id'),'ie_date'=>date('Y-m-d'),'ie_type'=>'income','ie_description'=>'payment from trip and '.$this->input->post('tp_notes'),'ie_amount'=>$this->input->post('tp_amount'),'ie_created_date'=>date('Y-m-d'));
			$this->db->insert('incomeexpense',$addincome);
			redirect('consignments/details/'.$pyment['tp_trip_id']);
		} else {
			$this->session->set_flashdata('warningmessage', 'Error!. Please try again');
			redirect('consignments/details/'.$pyment['tp_trip_id']);
		}
	}
	public function trippayment_delete()
	{
		$tp_id = $this->uri->segment(3);
		$response = $this->db->delete('trip_payments', array('tp_id' =>$tp_id));
		if($response) {
			$this->session->set_flashdata('successmessage', 'Payment record deleted successfully..');
		} else {
			$this->session->set_flashdata('warningmessage', 'Unexpected error..Try again');
		}
		redirect('consignments/details/'.$this->uri->segment(4));
	}
	public function addtripexpense() 	{
		$addtripexpense = $this->input->post();
		$trip_id = $addtripexpense['addtripexpense_trip_id'];
		unset($addtripexpense['addtripexpense_trip_id']);
		$response =  $this->db->insert('incomeexpense',$addtripexpense);
		if($response) {
			$this->session->set_flashdata('successmessage', 'Expense added successfully..');
		} else {
			$this->session->set_flashdata('warningmessage', 'Unexpected error..Try again');
		}
		redirect('consignments/details/'.$trip_id);
	}
	public function sendtripemail($data) {
		$this->load->model('email_model');	
		$gettemplate = $this->db->select('*')->from('email_template')->where('et_name','booking')->get()->result_array();
		if(!empty($gettemplate)) {
		    $emailcontent = $gettemplate[0]['et_body'];
			$value = '<b>Trip Details :</b><br><br> '.$data['t_trip_fromlocation']. ' <br><b>to</b><br> ' . $data['t_trip_tolocation']. ' <br>on<br> ' .$data['t_start_date'];
			$body = str_replace('{{bookingdetails}}', $value, $emailcontent);
			$custemail = $this->db->select('*')->from('customers')->where('c_id',$data['t_customer_id'])->get()->row()->c_email;
			$email = $this->email_model->sendemail($custemail,$gettemplate[0]['et_subject'],$body);
		}
	}
	public function sendtracking() {
		$this->load->model('email_model');
		$custemail = urldecode($_GET['email']);
		$url = base_url().'triptracking/'.$_GET['trackingcode'];
		$gettemplate = $this->db->select('*')->from('email_template')->where('et_name','tracking')->get()->result_array();
		if(!empty($gettemplate)) {
		    $emailcontent = $gettemplate[0]['et_body'];
			$body = str_replace('{{url}}', $url, $emailcontent);
			$email = $this->email_model->sendemail($custemail,$gettemplate[0]['et_subject'],$body);
			if($email) {
				$this->session->set_flashdata('successmessage', 'Email sent successfully..');
			} else {
				$this->session->set_flashdata('warningmessage', 'Unexpected error..Try again');
			}
			redirect('consignments/details/'.$_GET['t_id']);
		}
	}
}
