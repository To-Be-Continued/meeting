<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Admin extends CI_Controller
{
	/**
	*后台登入
	*/
	public function adminLogin() 
	{
		$this->load->view('admin/login.html'); 
	}
	/**
	*显示admin/main.html
	*/
	public function home()
	{
		$this->load->view('admin/main.html'); 
	}
	/**
	*显示admin/top/top.html
	*/
	public function showTop()
	{
		$this->load->view('admin/top/top.html');
	}
	/**
	*显示admin/left/left.html
	*/
	public function showLeft()
	{
		$this->load->view('admin/left/left.html');
	}
	/**
	*显示admin/right/default.html
	*/
	public function showDefault()
	{
		$this->load->view('admin/right/workspace.html');
	}
	/**
	*显示admin/right/workspace.html
	*/
	public function showWorkspace()
	{
		$this->load->view('admin/right/workspace.html');
	}
	/**
	*显示admin/right/about.html
	*/
	public function showAbout()
	{
		$this->load->view('admin/right/about.html');	
	}
	/**
	*显示admin/right/set.html
	*/
	public function showSet()
	{
		$this->load->view('admin/right/set.html');
	}
	/**
	*显示admin/right/personal.html
	*/
	public function showPersonal()
	{
		$this->load->view('admin/right/personal.html'); 
	}
	public function systemSet()
	{
		$this->load->view('admin/right/systemSet.html'); 
	}
	/************************************************************************************
	*******************
	*database operate
	*************************************************************************************
	******************/
	/**
	*登入提交表单
	*/
	public function getaccess()
	{
		$post=array(
			'u_tel'=>$this->input->post('u_tel'),
			'u_pwd'=>$this->input->post('u_pwd')
		);
		//print_r($post); die;
		$this->form_validation->set_data($post);
		$this->form_validation->set_message('required','{field}不能为空');
		if($this->form_validation->run('login'))
		{
			$this->load->model('admin_model');
			$this->admin_model->login($post);
			
		}
		else
		{	
			$this->load->view('admin/login.html'); 
		}
	}
	/**
	*显示admin/right/users.html
	*/
	public function showUsers()
	{
		$post=$this->input->post('page');
		$this->load->model('admin_model');
		$data=$this->admin_model->usersData($post);
		$this->load->view('admin/right/users.html',$data);
	}
	/**
	*显示admin/right/meets.html
	*/
	public function showMeets()
	{   
		$post=$this->input->post('page');
		$this->load->model('admin_model');
		$data=$this->admin_model->meetsData($post);
		//print_r($data);die;
		$this->load->view('admin/right/meets.html',$data);
	}
	public function updateInfo()
	{
		$post = array(
			'data' => array(
					'u_nickname' => $this->input->post('u_nickname'),
					'u_pwd' => $this->input->post('u_pwd'),
					'u_tel' => $this->input->post('u_tel'),
					'u_email' => $this->input->post('u_email')),
			'token' => $this->input->post('token')
		);
			
			$this->load->model('admin_model','updateInfo');
			$this->updateInfo->updateInfo($post);
	}

}
?>