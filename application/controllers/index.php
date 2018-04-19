<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Index extends CI_Controller
{
	//login
	public function login()
	{
		$this->load->view('index/login.html');
	}
  //quit
  public function quit()
  {
    //跳转，重定向
    redirect('index/login');
  }
	public function showhome()
	{
		if(!isset($_SESSION['token']))
		{
			redirect('index/login');
		}
		$form= array('token' => $_SESSION['token']);
    //print_r($form);die;
		$this->load->model('index_model','get_user');
		$user = $this->get_user->get($form);
		//print_r($user);die;
		$this->load->view('index/home.html',$user); 
	}

  public function enterHome()
  {
    //config
    $members = array('m_id','m_pwd');

    $post = array(
    		'm_id' => $this->input->post('m_id'),
    		'm_pwd' => $this->input->post('m_pwd')
    );
    $this->form_validation->set_message('required','{field}不能为空!');
    if(! $this->form_validation->run('m_login'))
    {
    	//跳转，重定向
    	redirect('index/login');
    }
    $this->load->model('index_model','m_login'); 
    $this->m_login->m_login($post); 
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
     /**
    *获得签名墙图片
    */
    public function get_signImg()
    {
      $post = array(
        's_id' => $this->input->post('s_id')
      );
      $this->load->model('index_model','sign');
      $data = $this->sign->signData($post); 
  
      echo json_encode($data);
    }
    /**
    *确认是否修改密码
    */
    public function check_pwd() 
    {
      if(!isset($_SESSION['token'])&&!isset($_SESSION['m_id']))
      {
        redirect('index/login');
      }
      $this->load->model('index_model','check');
      $data = $this->check->check_pwd();
      echo json_encode($data);
    }
    /**
    *修改密码
    */
    public function alter_pwd()
    {
      $post = array(
        'm_pwd' => $this->input->post('m_pwd')
      );
      if(!isset($_SESSION['token'])||!isset($_SESSION['m_id']))
      {
        redirect('index/login');
      }

      $this->load->model('index_model','alter');
      $this->alter->alter_pwd($post);
     
      echo json_encode($post);
    }
}
?>