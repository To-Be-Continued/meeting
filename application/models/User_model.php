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


	/**********************************************************************************************
	 * 业务接口
	 **********************************************************************************************/


	/**
	 * 注册
	 */
	public function register($form)
	{
		//config
		$members = array('u_tel', 'u_pwd');
		$member_token = array('token', 'last_visit', 'u_id');

		print_r($form);die;
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
		if (isset($form['token']))
		{
			$this->check_token($form['token']);
		}
		$where = array('token' => $form['token']);
		$user = $this->db->select('u_id')
					 ->where($where)
					 ->get('sys_token')
					 ->result_array()[0]['u_id'];

		//update
		$where = array('u_id' => $user);
		$this->db->update('user_t', filter($form, $members), $where);

	}


	/*
	 * 创建会议
	 */
	public function set_up_meeting($form)
	{
		//config
		$members = array('m_createrId', 'm_theme', 'm_introduction', 'm_startdate','m_starttime',
		  				 'm_length','m_place','m_sponsor','m_organizer','m_open',
						 'm_createrId','m_autoJoin','m_3DSign','m_luckyDog','m_vote');

		//check token &get user
		if (isset($form['token']))
		{
			$this->check_token($form['token']);
		}
		$where = array('token' => $form['token']);
		$user = $this->db->select('u_id')
					 ->where($where)
					 ->get('sys_token')
					 ->result_array()[0]['u_id'];
		$form['m_createrId'] = $user;
		
		//check exit meeting
		/*$where = filter($form, $members);
		if ( $ret = $this->db->select()
							 ->where($where)
							 ->get('meeting_t')
							 ->result_array())
		{
			throw new Exception("已创建该会议");
		}*/
		//create
		$data=filter($form,$members);
		$this->db->insert('meeting_t', $data);
		$id = $this->db->insert_id();
		$val = array(
			'm_id' => $id,
			'u_id' => $user
		);
		$this->db->insert('meeting_manager', $val);
		$this->db->insert('meeting_participants', $val);

	}


	/*
	 *发布会议
	 */
	public function release_meeting($form)
	{
		//check token
		if (isset($form['token']))
		{
			$this->check_token($form['token']);
		}

		//check m_id & release_meeting
		$where = array('m_id' => $form['m_id']);
		if ( ! $ret = $this->db->select()
		 				   ->where($where)
						   ->get('meeting_t')
						   ->result_array())
		{
			throw new Exception("该会议不存在");
		}

		return $ret[0];
	}


	/*
	 * 删除会议
	 */
	public function delete_meeting($form)
	{
		//check token
		if (isset($form['token']))
		{
			$this->check_token($form['token']);
		}

		//check m_id
		$where = array('m_id' => $form['m_id']);
		if ( ! $ret = $this->db->select('m_id')
		 				   ->where($where)
						   ->get('meeting_t')
						   ->result_array())
		{
		 	throw new Exception("该会议不存在");
		}

		//DO delete
	 	$this->db->delete('meeting_t', $where);
		$this->db->delete('meeting_participants', $where);
		$this->db->delete('meeting_manager', $where);
		$this->db->delete('meeting_vote', $where);
	}


	/*
	 * 修改会议
	 */
	public function change_meeting($form)
	{
		//config
		$members = array('m_createrId', 'm_theme', 'm_introduction', 'm_startdate','m_starttime',
		 				'm_length','m_place','m_sponsor','m_organizer','m_open',
						'm_autoJoin','m_3DSign','m_luckyDog','m_vote');

		//check token &get user
		if (isset($form['token']))
		{
			$this->check_token($form['token']);
		}
		$where = array('token' => $form['token']);
		$user = $this->db->select('u_id')
					 ->where($where)
					 ->get('sys_token')
					 ->result_array()[0]['u_id'];

		//create
		$form['m_createrId'] = $user;
		$where = array('m_id' => $form['m_id']);
		$data = filter($form,$members);
		//echo $this->db->set($data)->get_compiled_insert('meeting_t');die;
		$this->db->update('meeting_t', $data, $where);
	}


	/*
	 * 参加会议
	 */
	public function join_meeting($form)
	{
		//config
		$members = array('m_id', 'u_id');

		//check token &get user
  		if (isset($form['token']))
  		{
  			$this->check_token($form['token']);
  		}
  		$where = array('token' => $form['token']);
  		$user = $this->db->select('u_id')
  			 		 ->where($where)
  					 ->get('sys_token')
  					 ->result_array()[0]['u_id'];

		//check m_id
		$where = array('m_id' => $form['m_id']);

		if ( ! $ret = $this->db->select('m_id')
		 				   ->where($where)
						   ->get('meeting_t')
						   ->result_array())
		{
		 	throw new Exception("该会议不存在");
		}

		//check if join
		$where['u_id'] = $user;
		if ( $ret = $this->db->select()
		 				 ->where($where)
						 ->get('meeting_participants')
						 ->result_array())
		{
			throw new Exception("已加入该会议");
		}

		//join_meeting & update meeting num
		$form['u_id'] = $user;
		$this->db->insert('meeting_participants', filter($form, $members));
		$wheres = array('m_id' => $form['m_id']);
		$m_num = $this->db->select('m_num')
						  ->where($wheres)
						  ->get('meeting_t')
						  ->result_array()[0]['m_num'];
		$this->db->update('meeting_t', array('m_num' => $m_num + 1), $wheres);
	}


	/*
 	 * 会议参与者
 	 */
 	public function meeting_actor($form)
 	{

 	}


 	/*
 	 * 会议管理人员
 	 */
 	public function meeting_manager($form)
 	{
 		//config
 		$members = array('m_id', 'u_id');

 		//check token &get user
 		if (isset($form['token']))
  		{
  			$this->check_token($form['token']);
  		}
  		$where = array('token' => $form['token']);
  		$user = $this->db->select('u_id')
  			 		 ->where($where)
  					 ->get('sys_token')
  					 ->result_array()[0]['u_id'];

  		$wheres = array(
  			'm_id' => $form['m_id'],
  			'm_createrId' => $user
  		);
  		if ( ! $ret = $this->db->select(array('m_createrId','m_id'))
  							   ->where($wheres)
  							   ->get('meeting_t')
  							   ->result_array())
  		{
  			throw new Exception("无管理员权限");
  			
  		}

  		//do insert
  		$this->db->insert('meeting_manager', filter($form,$members));
 	}


 	/*
 	 * 抽取幸运观众
 	 */
 	public function meeting_lucky_dog($form)
 	{
 		//check token &get user
 		if (isset($form['token']))
  		{
  			$this->check_token($form['token']);
  		}
  		$where = array('token' => $form['token']);
  		$user = $this->db->select('u_id')
  			 		 ->where($where)
  					 ->get('sys_token')
  					 ->result_array()[0]['u_id'];

  		//check if power
  		$wheres = array(
  			'm_id' => $form['m_id'],
  			'u_id' => $user
  		);
  		if ( ! $ret = $this->db->select()
  							   ->where($wheres)
  							   ->get('meeting_manager')
  							   ->result_array())
  		{
  			throw new Exception("无抽奖权限");
  		}


  		//抽幸运观众
  		$con = array(
  			'm_id' => $form['m_id'],
  			'luckyDog' => 0
  		);
  		$num = $this->db->where($con)
  						->from('meeting_participants')
  						->count_all_results();

  		$ret = mt_rand(1, $num > 0 ? $num : 1);
  		$data = $this->db->select('u_id')
  						 ->where($con)
  						 ->order_by('u_id', 'RANDOM')
  						 ->get('meeting_participants')
  						 ->result_array();
		if ( $data )
		{
			$luckyboy = array(
				'm_id' => $form['m_id'],
				'u_id' => $data[$ret - 1]['u_id']
			);
			$this->db->update('meeting_participants', array('luckyDog' => 1), $luckyboy);

  			//return
	  		return $data[$ret - 1]['u_id'];	
		}

 	}


	/*
	 *设置投票功能
	 */
	public function set_vote($form)
	{
		//config
		$members = array('m_id', 'u_id');

		//check token &get user
		if (isset($form['token']))
		{
			$this->check_token($form['token']);
		}

		//check m_id
		$where = array('m_id' => $form['m_id']);
		if (! $ret = $this->db->select('m_id')
		 				   ->where($where)
						   ->get('meeting_t')
						   ->result_array())
		{
			throw new Exception("该会议不存在");
		}

		//do set
		$this->db->insert('meeting_vote',filter($form, $members));
	}


 	/*
 	 * 投票功能
 	 */
 	public function meeting_vote_function($form)
	{
		//check token
  		if (isset($form['token']))
  		{
  			$this->check_token($form['token']);
  		}

		//check m_id
		$where = array(
				'm_id' => $form['m_id'],
			  	'u_id' => $form['u_id']
				);
		if ( ! $ret = $this->db->select('voteNum')
		  					   ->where($where)
							   ->get('meeting_t')
							   ->result_array())
		{
		 	throw new Exception("该投票项不存在");
		}

		//do vote
		$voteNum = $ret[0]['voteNum'];
		$this->db->update('meeting_vote', array('voteNum' => $voteNum + 1), $where);
	}
}

?>
