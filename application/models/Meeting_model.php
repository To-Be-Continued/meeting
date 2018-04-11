<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Meeting_model extends CI_Model {


	/**********************************************************************************************
	 * 公开工具集
	 **********************************************************************************************/


	/*
	 * 检查用户创建者
	 */
	public function check_creatId($form)
	{
		$where = array('m_id' => $form['m_id']);
		$user = $this->db->select('m_createrId')
					 ->where($where)
					 ->get('meeting_t')
					 ->result_array();
		return $user;
	}

	/**********************************************************************************************
	 * 业务接口
	 **********************************************************************************************/
	

	/*
	 * 创建会议
	 */
	public function set_up_meeting($form)
	{
		//config
		$members = array('m_createrId', 'm_theme', 'm_introduction', 'm_startdate','m_starttime','group_id',
		  				 'm_length','m_place','m_sponsor','m_organizer','m_open',
						 'm_createrId','m_autoJoin','m_3DSign','m_luckyDog','m_vote', 'm_imgpath');

		//check token &get user
		$this->load->model('User_model', 'my_user');	
		$user = $this->my_user->get($form);		 

		$form['m_createrId'] = $user;
		$form['m_imgpath'] = base_url() . 'uploads/meeting_img/meeting.jpg';

		//check exit meeting
		$where = filter($form, $members);
		if ( $ret = $this->db->select()
							 ->where($where)
							 ->get('meeting_t')
							 ->result_array())
		{
			throw new Exception("已创建该会议");
		}
		//create
		$data=filter($form,$members);
		$this->db->insert('meeting_t', $data);
		$id = $this->db->insert_id();
		$val = array(
			'm_id'     => $id,
			'u_id' 	   => $user,
			'is_admin' => '1'
		);
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
			$this->load->model('User_model', 'my_user');
  			$this->my_user->check_token($form['token']);
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
			$this->load->model('User_model', 'my_user');
  			$this->my_user->check_token($form['token']);
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
		$this->load->model('User_model', 'my_user');
  		$user = $this->my_user->get($form);

		//create
		$form['m_createrId'] = $user;
		$where = array('m_id' => $form['m_id']);
		$data = filter($form,$members);
		//echo $this->db->set($data)->get_compiled_insert('meeting_t');die;
		$this->db->update('meeting_t', $data, $where);
	}


	/*
 	 * 会议参与者
 	 */
 	public function meeting_actor($form)
 	{
 		//check token & get user
 		$this->load->model('User_model', 'my_user');
  		$user = $this->my_user->get($form);

		//check m_id
		$where = array('m_id' => $form['m_id']);

		if ( ! $ret = $this->db->select('m_id')
		 				   ->where($where)
						   ->get('meeting_t')
						   ->result_array())
		{
		 	throw new Exception("该会议不存在");
		}

		$ret = $this->db->select('u_id')
						->where($where)
						->get('meeting_participants')
						->result_array();

		foreach ($ret as $key => $val) 
		{
			$ans[$key] = $this->db->select('u_nickname')
								  ->where($val)
								  ->get('user_t')
								  ->result_array()[0];
		}
		
		return $ans;
 	}


 	/*
 	 * 抽取幸运观众
 	 */
 	public function meeting_lucky_dog($form)
 	{
 		//check token & get user
 		$this->load->model('User_model', 'my_user');
  		$user = $this->my_user->get($form);

  		//check if power
  		$wheres = array(
  			'm_id' => $form['m_id'],
  			'u_id' => $user
  		);
  		if ( ! $admin = $this->db->select('is_admin')
  								 ->where($wheres)
  							   	 ->get('meeting_participants')
  							   	 ->result_array()[0]['is_admin'])
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
		$members = array('m_id', 'u_id', 'v_title', 'v_summary','v_type', 'v_starttime', 'v_endtime');

		//check token
		if (isset($form['token']))
		{
			$this->load->model('User_model', 'my_user');
  			$this->my_user->check_token($form['token']);
			$user=$this->my_user->get($form);
			$form['u_id']=$user;
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

		//check vote option
		if (! isset($form['v_option']))
		{
			throw new Exception("必须设置投票项");
		}

		//object translate into json array
		$arr=json_decode(json_encode($form['v_option']), true);
		if (empty($arr))
		{
			throw new Exception("投票项不能为空");
		}

		//do set
		$data=filter($form, $members);
		if (  $ret = $this->db->select()
							  ->where($data)
							  ->get('meeting_vote')
							  ->result_array())
		{
			throw new Exception("已创建该投票项");
		}
		$this->db->insert('meeting_vote',$data);
		$id = $this->db->insert_id();

		//insert vote option
		$ret = array(
			'm_id' => $form['m_id'],
			'v_id' => $id
		);
		foreach ($arr as $key => $val) 
		{
			$ret['v_option']=$val;
			$this->db->insert('vote_option',$ret);
		}
		return $id;
	}


	/*
 	 * 投票详情
 	 */
 	public function vote_detail($form)
	{
		//check token
  		if (isset($form['token']))
  		{
  			$this->load->model('User_model', 'my_user');
  			$this->my_user->check_token($form['token']);
			$user=$this->my_user->get($form);
  		}

		//check m_id
		$where = array(
				'm_id' => $form['m_id'],
			  	'v_id' => $form['v_id']
				);
		$data = array('v_title', 'v_summary', 'v_type', 'v_starttime', 'v_endtime');
		if ( ! $ret = $this->db->select($data)
		  					   ->where($where)
							   ->get('meeting_vote')
							   ->result_array()[0])
		{
		 	throw new Exception("该投票项不存在");
		}
		$ret['option'] = $this->db->select(array('v_option','v_num'))
								   ->where($where)
								   ->get('vote_option')
								   ->result_array();
		$wheres = array(
			'v_id' => $form['v_id'],
			'u_id' => $user
		);
		if (! $flag = $this->db->select('v_flag')
								  ->where($wheres)
								  ->get('vote_user')
								  ->result_array())
		{
			$ret['v_flag'] = 0;
		}
		else
		{
			$ret['v_flag'] = $flag[0]['v_flag'];
		}
		//return
		return $ret;
	}


	/*
	 * 发布会议公告
	 */
	public function set_notice($form)
	{
		$members = array('m_id', 'u_id', 'n_title', 'n_text', 'n_time');
		//check token
		if (isset($form['token']))
		{
			$this->load->model('User_model','my_user');
			$this->my_user->check_token($form['token']);
			$user = $this->my_user->get($form);
			$form['u_id'] = $user;
		}

		//check m_id
		if (! $this->db->select()
					   ->where(array('m_id'=>$form['m_id']))
					   ->get('meeting_t')
					   ->result_array())
		{
			throw new Exception("该会议不存在");
		}

		//check ifadmin 
		$where = array(
  			'm_id' => $form['m_id'],
  			'u_id' => $user
  		);
		if ( ! $admin = $this->db->select('is_admin')
  								 ->where($where)
  							   	 ->get('meeting_participants')
  							   	 ->result_array()[0]['is_admin'])
  		{
  			throw new Exception("无发布权限");
  		}

  		$data=filter($form, $members);
		if (  $ret = $this->db->select()
							  ->where($data)
							  ->get('meeting_notice')
							  ->result_array())
		{
			throw new Exception("已发布该公告");
		}

		//do insert
		$form['n_time']= date('Y-m-d H:i', time());
		$this->db->insert('meeting_notice',filter($form,$members));

	}


	/*
	 * 获取会议公告
	 */
	public function get_notice($form)
	{
		//check token
		if (isset($form['token']))
		{
			$this->load->model('User_model', 'my_user');
			$this->my_user->check_token($form['token']);
		}

		//check m_id
		if (! $this->db->select()
					   ->where(array('m_id'=>$form['m_id']))
					   ->get('meeting_t')
					   ->result_array())
		{
			throw new Exception("该会议不存在");
		}

		//get_notice
		$data = $this->db->select(array('n_title', 'n_text', 'n_time', 'u_tel', 'u_nickname'))
						 ->join('user_t','user_t.u_id=meeting_notice.u_id')
					   	 ->get_where('meeting_notice', array('m_id'=>$form['m_id']))
						 ->result_array();
		
		//return
		return $data;
	}
 	

	/*
	 * 签到
	 */
	public function do_sign($form)
	{
		//config
		$members = array('m_id', 'u_id');

		//check token & get u_id
		if (isset($form['token']))
		{
			$this->load->model('User_model', 'my_user');
			$this->my_user->check_token($form['token']);
		}
		$where = array('token' => $form['token']);
		$form['u_id'] = $this->db->select('u_id')
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
			throw new Exception("会议不存在");
		}

		//do sign
		$data['signIn'] = true;
		$this->db->update('meeting_participants', $data, filter($members, $form));
	}


	/*
	 * 上传会议头像
	 */
	public function upload_img($form)
	{
		//config
		$member = array('m_imgpath');

		//check token
		if (isset($form['token']))
		{
			$this->load->model('User_model', 'my_user');
			$this->my_user->check_token($form['token']);
		}

		//check m_id
		$where = array('m_id' => $form['m_id']);
		if ( ! $ret = $this->db->select('m_id')
							   ->where($where)
							   ->get('meeting_t')
							   ->result_array())
		{
			throw new Exception("会议不存在");
		}

		//update
		$data = filter($form, $member);
		$this->db->update('meeting_t', $data, $where);

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
			$this->load->model('User_model', 'my_user');
			$this->my_user->check_token($form['token']);
		}

		//get icon
		if ( ! $data = $this->db->select('m_imgpath')
								->where(array('m_id' => $form['m_id']))
								->get('meeting_t')
								->result_array())
		{
			throw new Exception("该会议不存在");	
		}
		return $data[0];
	}


	/*
	 * get theme
	 */
	public function get_theme($form)
	{
		//check token
		if (isset($form['token']))
		{
			$this->load->model('User_model', 'my_user');
			$this->my_user->check_token($form['token']);
		}

		$data = array('m_id', 'm_theme', 'm_introduction', 'm_length', 'm_startdate');
		$where = array('m_theme' => $form['m_theme']);
		$ret = $this->db->select($data)
				  		   ->like($where)
						   ->get('meeting_t')
						   ->result_array();

		return $ret;
	}


	/*
	 * 参加会议
	 */
	public function join_meeting($form)
	{
		//config
		$members = array('m_id', 'u_id');

		//check token & get user;
		$this->load->model('User_model', 'my_user');
  		$user = $this->my_user->get($form);

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
	 * invite member
	 */
	public function invite_member($form)
	{

		//config
		$members = array('m_id', 'u_id');

		//check token
		$this->load->model('User_model', 'my_user');
  		$this->my_user->get($form);

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
		if (! $id = $this->db->select('u_id')
							 ->where(array('u_tel'=>$form['invited_tel']))
						     ->get('user_t')
						     ->result_array())
		{
			throw new Exception("该用户不存在");
		}
		$where = array(
				'u_id' => $id[0]['u_id'],
				'm_id' => $form['m_id']
			);
		if ( $ret = $this->db->select($where)
		 				 ->where($where)
						 ->get('meeting_participants')
						 ->result_array())
		{
			throw new Exception("已加入该会议");
		}

		//join_meeting & update meeting num
		$this->db->insert('meeting_participants', filter($where, $members));
		$wheres = array('m_id' => $form['m_id']);
		$m_num = $this->db->select('m_num')
						  ->where($wheres)
						  ->get('meeting_t')
						  ->result_array()[0]['m_num'];
		$this->db->update('meeting_t', array('m_num' => $m_num + 1), $wheres);
	}


	/*
	 * delete member
	 */
	public function delete_member($form)
	{
		//check token & get id
		$this->load->model('User_model', 'my_user');
  		$this->my_user->get($form);

  		$user = $this->db->select('u_id')
  						 ->where(array('u_tel'=>$form['op_tel']))
  						 ->get('user_t')
  						 ->result_array();

  		if (empty($user))
  		{
  			throw new Exception("该用户不存在");
  		}

  		$where = array(
  			'm_id' => $form['m_id'],
  			'u_id' => $user[0]['u_id']
  		);
  		if ( ! $admin = $this->db->select('is_admin')
  								 ->where($where)
  							   	 ->get('meeting_participants')
  							   	 ->result_array()[0]['is_admin'])
  		{
  			throw new Exception("无管理员权限");
  		}

  		//check if exist
  		if (! $id = $this->db->select('u_id')
							 ->where(array('u_tel' => $form['del_tel']))
						     ->get('user_t')
						     ->result_array())
		{
			throw new Exception("该用户不存在");
		}

		//do delete
		$wheres = array(
				'm_id' => $form['m_id'],
  				'u_id' => $id[0]['u_id']
			);
		$this->db->delete('meeting_participants', $wheres);
	}


	/*
 	 * setting manager
 	 */
 	public function setting_manager($form)
 	{
 		//check token & get id
		$this->load->model('User_model', 'my_user');
  		$this->my_user->get($form);

  		$user = $this->db->select('u_id')
  						 ->where(array('u_tel'=>$form['op_tel']))
  						 ->get('user_t')
  						 ->result_array();

  		if (empty($user))
  		{
  			throw new Exception("该用户不存在");
  		}

  		$where = array(
  			'm_id' => $form['m_id'],
  			'u_id' => $user[0]['u_id']
  		);
  		if ( ! $admin = $this->db->select('is_admin')
  								 ->where($where)
  							   	 ->get('meeting_participants')
  							   	 ->result_array()[0]['is_admin'])
  		{
  			throw new Exception("无管理员权限");
  		}

  		//check if exist
  		if (! $id = $this->db->select('u_id')
							 ->where(array('u_tel' => $form['set_tel']))
						     ->get('user_t')
						     ->result_array())
		{
			throw new Exception("该用户不存在");
		}

		//check if join meeting
		$wheres = array(
				'm_id' => $form['m_id'],
  				'u_id' => $id[0]['u_id']
			);
		if (! $ret = $this->db->select()
							  ->where($wheres)
							  ->get('meeting_participants')
							  ->result_array())
		{
			throw new Exception("未加入该会议");
		}

  		//do update
  		$this->db->update('meeting_participants', array('is_admin' => 1), $wheres);
 	}


	/*
	 * get list
	 */
	public  function get_list($form)
	{
		//check token
		if (isset($form['token']))
		{
			$this->load->model('User_model', 'my_user');
			$this->my_user->check_token($form['token']);
		}

		//get list
		$data = array('u_tel', 'u_nickname', 'u_imgpath', 'is_admin');
		$ret = $this->db->select($data)
					   ->join('user_t','user_t.u_id=meeting_participants.u_id')
					   ->get_where('meeting_participants', array('m_id'=>$form['m_id']))
					   ->result_array();

		return $ret;
		
	}


	/*
	 * exit meeting
	 */
	public function exit_meeting($form)
	{
		//check token & get id
		$this->load->model('User_model', 'my_user');
  		$user = $this->my_user->get($form);

		//check if join meeting
		$wheres = array(
				'm_id' => $form['m_id'],
  				'u_id' => $user
			);
		if (! $ret = $this->db->select()
							  ->where($wheres)
							  ->get('meeting_participants')
							  ->result_array())
		{
			throw new Exception("未加入该会议");
		}

		//check if exist meeting
		if ( ! $ret =  $this->db->select('m_createrId')
								->where(array('m_id' => $form['m_id']))
								->get('meeting_t')
								->result_array())
		{
			throw new Exception("该会议不存在");
		}
		//check if creater
		if ( $user == $ret[0]['m_createrId'])
		{
			$this->db->delete('meeting_t', array('m_id' => $form['m_id']));
			$this->db->delete('meeting_participants', array('m_id' => $form['m_id']));
		}

		//do exit
		$wheres = array(
			'm_id' => $form['m_id'],
			'u_id' => $user
		);
		$this->db->delete('meeting_participants', $wheres);
	}
}

?>
