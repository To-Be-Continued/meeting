<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_model extends CI_Model 
{
	/*********************************************************************
	****************
	*私有工具集
	**********************************************************************
	***************/
		/**
	 * 生成一个未被占用的Utoken
	 */
	private function create_token()
	{
		$this->load->helper('string');
		$token=random_string('alnum',30);
		while ($this->db->where(array('token'=>$token))
			->get('sys_token')
			->result_array());
		{
			$token=random_string('alnum',30);
		}
		return $token;
	}


	/**
	 * 检测时间差
	 */
	private function is_timeout($last_visit)
	{
		$this->load->helper('date');
		$pre_unix = human_to_unix($last_visit);
		$now_unix = time();
		return $now_unix - $pre_unix < 0;
	}
	/*********************************************************************
	***************
	*共有工具集
	**********************************************************************
	**************/

	/**
	 * 检测凭据
	 */
	public function check_token($token)
	{
		$where = array('token' => $token);
		if ( ! $result = $this->db->select('last_visit')
			->where(array('token' => $token))
			->get('sys_token')
			->result_array())
		{
			throw new Exception('会话已过期，请重新登陆', 401);
		}
		else
		{
			$user = $result[0];
			if ($this->is_timeout($user['last_visit']))
			{
				throw new Exception('会话已过期，请重新登陆', 401);
			}
			else
			{
				//刷新访问时间
				$new_data = array('last_visit' => date('Y-m-d H:i:s',time()));
				$this->db->update('sys_token', $new_data, $where);
			}
		}


	}
	/**************************************************************************
	******************
	*业务接口
	***************************************************************************
    *****************/
    
	/**
	*system users login
	*/
	public function login($form)
	{
		//print_r($post);
		$form['u_pwd'] = md5($form['u_pwd']);
		if( ! $result = $this->db->select('u_id')
		                         ->where($form)
		                         ->get('user_t') 
		                         ->row_array()) 
		{
			//print_r($form); die;
			echo "<script type='text/javascript'>alert('密码错误或手机号不存在')</script>";
			redirect('admin/adminLogin');
			
			
		}
		else
		{
			$where = array('u_id' => $result['u_id']);
			$power = $this->db->select('sys_login')
			                  ->where($where)
			                  ->get('user_power')
			                  ->row_array();
			 //权限设置
			if(isset($power)&&$power['sys_login'] == '1')
			{
				//update token
				$user = $this->db->select('token,last_visit')
				                 ->where($where)
				                 ->get('sys_token')
				                 ->row_array();

				$new_data = array('last_visit' => date('Y-m-d H:i:s',time()));
				
				if($this->is_timeout($user['last_visit']))  
				{
					$new_data['token'] = $this->create_token();
				}
				$this->db->update('sys_token',$new_data,$where);

				$token['token'] = $user['token'];
				$this->load->view('admin/main.html',$token);   
			}
			else
			{
				echo "<script type='text/javascript'>alert('无访问权限！')</script>";
				redirect('admin/adminLogin');
			}
		}
	}


	/**
	*return users data
	*/
	public function usersData($post)
	{
		if(!isset($post))
		{
			$post=0;
		}
		$this->db->limit(10,$post*10);
		$data['usersInfo']=$this->db->get('user_t')->result_array();
		//echo $this->db->count_all('user_t');die;
		if($post-$this->db->count_all('user_t')/10<0)
		{
			$post=$post+1;
		}
		$data['page']=$post;  
		return $data;
	}
	/**
	*return meets data
	*/
	public function meetsData($post)
	{
		if(!isset($post))
		{
			$post=0; 
		}
		$this->db->limit(10,$post*10);
		$data['meetsInfo']=$this->db->get('meeting_t')->result_array();
		if($post-$this->db->count_all('meeting_t')/10<0)
		{
			$post=$post+1;
		}
		$data['page']=$post;
		return $data; 
	}
	/**
	*return personal data
	*/
	public function perData($psot)
	{

	}
	/**
	*change personal message
	*/
	public function updateInfo($form) 
	{
		//check token
		if(isset($form['token']))
		{
			$this->check_token($form['token']);
		}
		
		$where = array('token' => $form['token']);
		$new_data = $form['data'];
		$new_data['u_pwd'] = md5($new_data['u_pwd']);
		$user = $this->db->select('u_id')
		         ->where($where)
		         ->get('sys_token')
		         ->row_array();
  		$this->db->update('user_t',$new_data,$user);
  		$this->load->view('admin/right/personal.html'); 
	}
	/**
	*system set
	*/
	public function systemSet($data)
	{
		
	}
}
?>