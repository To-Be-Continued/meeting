<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model {

	/*****************************************************************************************************
	 * 私有工具集
	 *****************************************************************************************************/


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


	/**********************************************************************************************
	 * 公开工具集
	 **********************************************************************************************/


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


	/*
	 * 获取用户
	 */
	public function get($form)
	{
		//check token & get user
		if (isset($form['token']))
		{
			$this->check_token($form['token']);
		}
		$where = array('token' => $form['token']);
		$user = $this->db->select('u_id')
					 ->where($where)
					 ->get('sys_token')
					 ->result_array()[0]['u_id'];

		return $user;
	}


	/**********************************************************************************************
	 * 业务接口
	 **********************************************************************************************/


	/**
	 * 注册
	 */
	public function register($form)
	{
		//config
		$members = array('u_tel', 'u_pwd', 'u_imgpath');
		$member_token = array('token', 'last_visit', 'u_id');

		//check u_tel
		$where = array('u_tel' => $form['u_tel']);
		if ( $result = $this->db->select('u_tel')
			->where($where)
			->get('user_t')
			->result_array())
		{
			throw new Exception('该手机号已注册');
		}

		//DO register
		if ( $form['u_pwdF'] != $form['u_pwdS'])
		{
			throw new Exception("两次密码不一致");

		}

		$form['u_pwd']=md5($form['u_pwdF']);
		$form['u_imgpath'] = base_url() . 'uploads/user_img/user.jpg';
		$this->db->insert('user_t',filter($form,$members));
		$result = $this->db->select('u_id')
					->where($where)
					->get('user_t')
					->result_array()[0];
		$result['token'] = $this->create_token();
		$this->db->insert('sys_token',filter($result,$member_token));

	}


	/**
	 *登录
	 */
	public function login($form)
	{
		$form['u_pwd'] = md5($form['u_pwd']);
		if ( ! $result = $this->db->select('u_id')
							  ->where($form)
				   			  ->get('user_t')
							  ->result_array())
		{
			throw new Exception('手机号不存在或密码错误');
		}

		//update token
		$where = array('u_id' => $result[0]['u_id']);
		$user = $this->db->select('last_visit')
						 ->where($where)
						 ->get('sys_token')
						 ->result_array()[0];
		$new_data = array('last_visit' => date('Y-m-d H:i:s', time()));
		if($this->is_timeout($user['last_visit']))
		{
			$new_data['token'] = $this->create_token();
		}
		$this->db->update('sys_token',$new_data, $where);

		//return rel
		$ret = array(
			'token' => $this->db->select('token')
						   ->where($where)
						   ->get('sys_token')
						   ->result_array()[0]['token']);
		return $ret;
	}


	/*
	 * 修改名片
	 */
	public function change_business_card($form)
	{
		//config
		$members = array('u_nickname', 'u_position', 'u_company','u_email',
						'u_qq','u_weChat','u_address','u_showCard');

		//check token & get user
		/*if (isset($form['token']))
		{
			$this->check_token($form['token']);
		}
		$where = array('token' => $form['token']);
		$user = $this->db->select('u_id')
					 ->where($where)
					 ->get('sys_token')
					 ->result_array()[0]['u_id'];*/
		//check token & get user
		$user = $this->get($form);

		//update
		$where = array('u_id' => $user);
		$this->db->update('user_t', filter($form, $members), $where);

	}


	/*
	 * 上传头像
	 */
	public function upload_img($form)
	{
		//config
		$member = array('u_imgpath');

		//check token
		if (isset($form['token']))
		{
			$this->check_token($form['token']);
		}

		//select user
		$where = array('u_id' => $form['u_id']);
		$data = filter($form, $member);
		$this->db->update('user_t', $data, $where);

		return $data;
	}



	/*
	 * get icon
	 */
	public function get_icon($form)
	{
		//check token
		if (isset($form['token']))
		{
			$this->check_token($form['token']);
		}

		//get icon
		if ( ! $data = $this->db->select('u_imgpath')
								->where(array('u_tel' => $form['op_tel']))
								->get('user_t')
								->result_array())
		{
			throw new Exception("该用户不存在");	
		}
		return $data[0];
	}
}

?>
