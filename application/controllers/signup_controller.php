<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Signup_Controller extends CI_Controller 
{
    
    public function __construct(){
        parent::__construct();
        
        $this->load->helper('form');
        $this->load->library('form_validation');
        $this->load->library('session');
       
        $this->load->library('email');
        $this->load->helper('url');
        $this->load->database();
        $this->load->model('Employee_Model');
		$this->load->library('email');
    }
    
    public function index(){

		$this->load->view('header');
        $this->load->view('signup_view');
        $this->load->view('footer');
	}
    
    
    public function signup(){
        $data['form']['txt_empname'] = 2;
        $data['form']['sel_parent_id'] =3; 
        //echo set_value('txt_empname', 2);
        $get_parent_select_box = $this->get_cat_selectlist(0, 0);
        $data['select_parent'] = $get_parent_select_box; 
        //$data['form']['txt_empname'] = 2;
        $this->form_validation->set_rules('txt_empname','name', 'trim|required');
        $this->form_validation->set_rules('txt_emp_addr','address', 'trim|required');
        $this->form_validation->set_rules('txt_email','Employee email', 'trim|required|valid_email|is_unique[employee.email]');
        
        $this->form_validation->set_rules('sel_parent_id','Employee Mananger', 'trim');
        $this->form_validation->set_rules('txt_username','Username', 'trim|required');
        $this->form_validation->set_rules('txt_username','Username', 'trim|required');
        $this->form_validation->set_rules('txt_password','Password', 'required');
        $this->form_validation->set_rules('txt_confirm_password', 'Password Confirmation', 'trim|required|matches[txt_password]');
        
        if($this->form_validation->run() == false){
            $this->load->view('header');
            $this->load->view('signup_view',$data);
            $this->load->view('footer');
            
        }else{
            //call db
            $data = array(
                'emp_name' => $this->input->post('txt_empname'),
                'address' => $this->input->post('txt_emp_addr'),
                'email' => $this->input->post('txt_email'),
                'username' => $this->input->post('txt_username'),
                'password' => md5($this->input->post('txt_password'))
            );
            
            
            if($this->Employee_Model->insertEmployee($data)){
                
                //send confirm mail
                if($this->Employee_Model->sendEmail($this->input->post('txt_email'))){
                    //redirect('Login_Controller/index');
                    //$msg = "Successfully registered with the sysytem.Conformation link has been sent to: ".$this->input->post('txt_email');
                    $this->session->set_flashdata('msg', '<div class="alert alert-success text-center">Successfully registered. Please confirm the mail that has been sent to your email. </div>');

                    $this->load->view('header');
                    $this->load->view('signup_view',$data);
                    $this->load->view('footer');
                }else{
                    
                    //$error = "Error, Cannot insert new user details!";
                    $this->session->set_flashdata('msg', '<div class="alert alert-danger text-center">Failed!! Please try again.</div>');
                    $this->load->view('header');
                    $this->load->view('signup_view',$data);
                    $this->load->view('footer');
                }
                
                
            }
        }
        
    }
    
    function confirmEmail($hashcode){
        //echo $hashcode;
        //exit;
        if($this->Employee_Model->verifyEmail($hashcode)){
            $this->session->set_flashdata('verify', '<div class="alert alert-success text-center">Email address is confirmed. Please login to the system</div>');
            redirect('Login_Controller/index');
        }else{
            $this->session->set_flashdata('verify', '<div class="alert alert-danger text-center">Email address is not confirmed. Please try to re-register.</div>');
            redirect('Login_Controller/index');
        }
    }

    function get_cat_selectlist($current_cat_id, $count) {
        
    static $option_results;
        
    // if there is no current category id set, start off at the top level (zero)
    if (!isset($current_cat_id)) {
        $current_cat_id =0;
    }
    // increment the counter by 1
    $count = $count+1;
   // query the database for the sub-categories of whatever the parent category is
   $sql =  'SELECT emp_id, emp_name from employee where parent_id =  '.$current_cat_id;
   $sql .=  ' order by emp_name asc ';
   $emp_data = $this->Employee_Model->query($sql);
         
        //$get_options = mysqli_query($con, $sql);
        //$num_options = mysqli_num_rows($get_options);
         
        // our category is apparently valid, so go ahead €¦
   if(isset($emp_data) && !empty($emp_data)){
    foreach($emp_data as $key => $emp_value){
        //if ($num_options > 0) {
        //print_r($emp_value);
        $cat_id = $emp_value['emp_id'];
        $cat_name = $emp_value['emp_name'];
        //while (list($cat_id, $cat_name) = $emp_value) {
         //print_r($cat_id);
        // if its not a top-level category, indent it to
        //show that its a child category
         
        if ($current_cat_id!=0) {
        //$indent_flag =  '|-';
         $indent_flag =  '&nbsp;';
        for ($x = 1; $x<= $count; $x++) {
            //if( $x==$count){
                //$indent_flag .=  '-';
            $indent_flag .=  '&nbsp;&nbsp';

            //}
            
        }
        }
        else
        {
        //$indent_flag = '|-';
            $indent_flag = '';

        }
        $cat_name = $indent_flag.$cat_name;
        $option_results[$cat_id] = $cat_name;
        // now call the function again, to recurse through the child categories
        $this->get_cat_selectlist($cat_id, $count );
        //}
       } 
        }
        return $option_results;
        }  
    
}

