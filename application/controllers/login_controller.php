<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Login_Controller extends CI_Controller {
    
    public function __construct(){
        parent::__construct();
        
        $this->load->helper('form');
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->helper('url');
        $this->load->database();
        $this->load->model('Employee_Model');   
    }
    
    public function index()
	{
		$this->load->view('header');
        $this->load->view('login_view');
        $this->load->view('footer');
	}
    
    
    public function login(){
        
        $this->form_validation->set_rules('txt_username','Username', 'trim|required');
        $this->form_validation->set_rules('txt_password','Password', 'trim|required');
        
        if($this->form_validation->run() == false){
            $this->load->view('header');
            $this->load->view('login_view');
            $this->load->view('footer');
        }else{
            
            $username = $this->input->post('txt_username');
            $password = md5($this->input->post('txt_password'));
            
            if(!$this->session->userdata('login_attempts')){
                    $this->session->set_userdata('login_attempts',0);
             }       

            if($this->session->userdata('login_attempts') <3)
            {    
            if($this->Employee_Model->loginUser($username, $password)){
                $this->session->unset_userdata('login_attempts');
                
                $userInfo = $this->Employee_Model->loginUser($username, $password);
                $this->session->set_flashdata('login_msg', '<div class="alert alert-success text-center">Successfully logged in</div>');
                //$this->load->view('header');
                //$this->load->view('tasks_view');
                //$this->load->view('footer');
                redirect('Task_Controller/index');
                
            }else{
                $session_attamp = $this->session->userdata('login_attempts');

                $this->session->set_userdata('login_attempts',($session_attamp+1));
               // $_SESSION["attempts"] = $_SESSION["attempts"] + 1;
                $this->session->set_flashdata('login_msg', '<div class="alert alert-danger text-center">Login Failed!! Please try again.</div>');
                $this->load->view('header');
                $this->load->view('login_view');
                $this->load->view('footer');
                
            }
         }else{
                $this->session->set_flashdata('login_msg', '<div class="alert alert-danger text-center">You have failed too many times, dude.</div>');
                $this->load->view('header');
                $this->load->view('login_view');
                $this->load->view('footer');
                
            }
         //}
        }
    }
    
    public function logout(){
        
    }
}