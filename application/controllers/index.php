<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Index extends CI_Controller
{
	//test
	public function test()
	{
		//echo base_url('styles/index/js/');die;
		$this->load->view('index/vote.html');
	}
	
	//login
	public function login()
	{
		$this->load->view('index/login.html');
	}

	public function showhome()
	{
		if(!isset($_SESSION['token']))
		{
			redirect('index/login');
		}
		$form= array('token' => $_SESSION['token']);
		$this->load->model('index_model','get_user');
		$user = $this->get_user->get($form);
		//print_r($user);die;
		$this->load->view('index/home.html',$user); 
	}

    public function enterHome()
    {
    	//config
    	$members = array('u_tel','u_pwd');

    	$post = array(
    		'u_tel' => $this->input->post('u_tel'),
    		'u_pwd' => $this->input->post('u_pwd')
    	);
    	$this->form_validation->set_message('required','{field}不能为空!');
    	if(! $this->form_validation->run('login'))
    	{
    		//跳转，重定向
    		redirect('index/login');
    	}
    	$this->load->model('index_model','login'); 
    	$this->login->login($post); 
    }
   	/**
   	*投票数据
   	*/
  	public function get_voteData()
   	{
   		$post = array(
   			'v_id' => $this->input->post('v_id')
   		);
   		$this->load->model('index_model','vote');
   		$data = $this->vote->voteData($post);
   		
   		echo json_encode($data);
   	}
   	/**
   	*获取投票数据
   	*/
   	public function send_voteData()
   	{
   		$post = array(
   			'voteData' => json_decode(json_encode($this->input->post('voteData')),true),
   			'voteUsers' => json_decode(json_encode($this->input->post("voteUsers")),true)
   		);
   	
   		$this->load->model('index_model','vote');
   		$ret = $this->vote->insert_vote($post); 	
   		echo json_encode($ret);
   	}
   	/**
   	*抽奖数据
   	*/
   	public function get_drawData()
   	{
   		$post = array(
   			'm_id' => $this->input->post('m_id')
   		);
   		$this->load->model('index_model','draw');
   		$data = $this->draw->drawData($post);

   		echo json_encode($data);
   	}
}
?>