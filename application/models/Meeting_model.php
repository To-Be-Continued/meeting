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


	/*
	 * 检测时间差
	 */
	public function is_timeout($last_visit)
	{
		$this->load->helper('date');
		$pre_unix = human_to_unix($last_visit);
		$now_unix = time();
		return $now_unix - $pre_unix < 0;
	}


	/*
	 * 检测是否存在签名事件
	 */
	public function check_sign($form)
	{
		if ( isset($form['s_id']) )
		{
			if ($ret = $this->db->select('s_id')
								->where(array('s_id' => $form['s_id']))
								->get('meeting_sign')
								->result_array())
			{
				return true;
			}
		}
		return false;
	}


	/*
	 * 获取图片
	 */
	public function get_img($form)
	{
		if (isset($form['s_id']))
		{
			$ret = $this->db->select('si_imgpath')
							->where(array('s_id' => $form['s_id']))
							->get('sign_img')
							->result_array();
			return $ret;
		}
	}


	/*
	 * 获取会议主题
	 */
	public function get_text($form)
	{
		if (isset($form['s_id']))
		{
			$ret = $this->db->select('m_theme')
							->join('meeting_t','meeting_sign.m_id=meeting_t.m_id')
							->get_where('meeting_sign',array('s_id'=>$form['s_id']))
							->result_array()[0]['m_theme'];

			return $ret;
		}
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
			$tmp['u_nickname'] = $this->db->select('u_nickname')
								  ->where($val)
								  ->get('user_t')
								  ->result_array()[0]['u_nickname'];
			$tmp['signIn'] = $this->db->select('signIn')
								  ->where($val)
								  ->get('meeting_participants')
								  ->result_array()[0]['signIn'];

			$ans[$key]=$tmp;
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
		$this->db->update('meeting_participants', $data, filter($form, $members));
	}


	/*
	 * 创建签名事件
	 */
	public function register_sign($form)
	{
		//config
		$members = array('m_id', 'u_id', 's_starttime', 's_endtime');

		//check token & get user
		if (isset($form['token']))
		{
			$this->load->model('User_model', 'my_user');
			$user = $this->my_user->get($form);
			$form['u_id'] = $user;
		}

		//check if creat
		if ($ret = $this->db->select('m_id')
							->where(array('m_id' => $form['m_id']))
							->get('meeting_sign')
							->result_array())
		{
			throw new Exception("已创建签名事件");
		}

		//do insert
		$this->db->insert('meeting_sign', filter($form, $members));
		$id = $this->db->insert_id();

		return $id;
	}


	/*
	 * 上传签名图片
	 */
	public function upload_sign($form)
	{
		//config
		$members = array('s_id', 'u_id', 'si_imgpath');

		//check token
		if (isset($form['token']))
		{
			$this->load->model('User_model', 'my_user');
			$user = $this->my_user->get($form);
			$form['u_id'] = $user;
		}

		//check time
		$time = $this->db->select(array('s_starttime', 's_endtime'))
						 ->where(array('s_id' => $form['s_id']))
						 ->get('meeting_sign')
						 ->result_array()[0];

		if ($this->is_timeout($time['s_starttime']))
		{
			throw new Exception("签名事件未开始");
		}

		if ( ! $this->is_timeout($time['s_endtime']))
		{
			throw new Exception("签名事件已结束");
		}

		//check if sign
		$where = array(
			'u_id' =>$form['u_id'],
			's_id' =>$form['s_id']
		);
		if ($ret = $this->db->select('u_id')
							->where($where)
							->get('sign_img')
							->result_array())
		{
			throw new Exception("已签名");
		}

		//do insert
		$this->db->insert('sign_img', filter($form, $members));
		return $form['si_imgpath'];
	}


	/*
	 * 获取签名墙图片
	 */
	public function get_pic($form)
	{
		//config
		$member = array('s_imgpath');

		//check token
		if (isset($form['token']))
		{
			$this->load->model('User_model', 'my_user');
			$this->my_user->check_token($form['token']);
		}

		//check s_id
		$where = array('s_id' => $form['s_id']);
		if ( ! $ret = $this->db->select('s_id')
							   ->where($where)
							   ->get('meeting_sign')
							   ->result_array())
		{
			throw new Exception("投票事件不存在");
		}

		//update
		$data = filter($form, $member);
		$this->db->update('meeting_sign', $data, $where);

		return $data;
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
		$data = array('u_tel', 'u_nickname', 'u_imgpath', 'is_admin', 'signIn');
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


	/*
	 * 添加会议标签
	 */
	public function add_label($form)
	{
		//config
		$members = array('m_id', 'l_id');

		//check token
		if (isset($form['token']))
		{
			$this->load->model('User_model', 'my_user');
  			$this->my_user->check_token($form['token']);
  		}

		//check m_id
		if ( ! $ret =  $this->db->select('m_id')
								->where(array('m_id' => $form['m_id']))
								->get('meeting_t')
								->result_array())
		{
			throw new Exception("该会议不存在");
		}

		//check l_id
		if ( ! $ret =  $this->db->select('l_id')
								->where(array('l_id' => $form['l_id']))
								->get('sys_label')
								->result_array())
		{
			throw new Exception("该标签不存在");
		}

		//check exist
		if (   $ret =  $this->db->select()
								->where(filter($form, $members))
								->get('meeting_label')
								->result_array())
		{
			throw new Exception("已添加该标签");
		}
		//do insert
		$this->db->insert('meeting_label', filter($form, $members));
	}


	/*
	 * 获取会议标签
	 */
	public function get_label($form)
	{
		//check token
		if (isset($form['token']))
		{
			$this->load->model('User_model', 'my_user');
  			$user = $this->my_user->get($form);
  		}

  		//get target
  		$where=array('m_id'=>$form['m_id']);
  		if (! $res = $this->db->where($where)
  							  ->get('meeting_t')
  							  ->result_array())
  		{
  			throw new Exception("会议不存在");
  		}

  		$data = $this->db->select('l_name')
  						 ->join('sys_label','sys_label.l_id=meeting_label.l_id')
  						 ->get_where('meeting_label',array('m_id'=>$form['m_id']))
  						 ->result_array();

  		$ret=null;
  		foreach ($data as $key => $value)
  		{
  			$ret[$key]=$value['l_name'];
  		}

  		//return
  		return $ret;
	}


	/*
	 * 推荐会议
	 */
	public function recommend($form)
	{
		//check token & get user
		if (isset($form['token']))
		{
			$this->load->model('User_model', 'my_user');
  			$user = $this->my_user->get($form);
  		}

  		//get user join meeting label & sort
  		$ret = $this->db->select('l_id')
  						->join('meeting_label','meeting_label.m_id=meeting_participants.m_id')
  						->get_where('meeting_participants', array('u_id'=>$user))
  						->result_array();

  		if (empty($ret))
		{
			$ret = $this->db->select('l_id')
							->get('meeting_label')
							->result_array();
			if (empty($ret))
			{
				return null;
			}
		}

  		foreach ($ret as $key => $val)
  		{
  			$_ret[$key]=$val['l_id'];
  		}
  		$tmp=array_count_values($_ret);
  		arsort($tmp);

  		//according l_id find m_id
  		foreach ($tmp as $key => $val)
  		{
  			if ($arr = $this->db->select('m_id')
  								->where(array('l_id' => $key))
  								->get('meeting_label')
  								->result_array())
  			{
  				break;
  			}
  		}

  		//filter
  		foreach ($arr as $key => $val)
  		{
  			$_arr[$key]=$val['m_id'];
  		}
  		$t = $this->db->select('m_id')
  					  ->where(array('u_id'=>$user))
  					  ->get('meeting_participants')
  					  ->result_array();
  		foreach ($t as $key => $val)
  		{
  			$t[$key]=$val['m_id'];
  		}
  		$res=array_diff($_arr, $t);

  		//recommend
  		$i=0;
  		$data = array('m_id', 'm_imgpath', 'm_theme', 'm_introduction', 'm_length', 'm_startdate', 'm_starttime');
  		$nowdate=date('Y-m-d', time());
  		$ans=null;
  		foreach ($res as $key => $val)
  		{
  			if ($que = $this->db->select($data)
  								->where('m_startdate >',$nowdate)
  								->where(array('meeting_t.m_id'=>$val))
  								->get('meeting_t')
  								->result_array())
  			{
  				$ans[$i++]=$que[0];
  			}
  		}
  		//return
  		return $ans;

	}


	/*
	 * 发红包
	 */
	public function set_redpacket($form)
	{
		//config
		$members = array('u_id', 'r_money', 'r_num', 'r_name');
		$members_banlance = array('us_money','r_banlance', 'r_id');

		//check token get user
		if (isset($form['token']))
		{
			$this->load->model('User_model','my_user');
			$user = $this->my_user->get($form);
			$form['u_id']=$user;
		}

		//do insert
		$this->db->insert('red_packet', filter($form, $members));

		//set each money
		$id = $this->db->insert_id();
		$data['r_id'] = $id;
		$data['r_banlance'] = $form['r_money'];
		$min=0.01;//每个人最少能收到0.01元
		for ($i=1; $i < $form['r_num']; $i++)
		{
			$safe_total=($data['r_banlance']-($form['r_num']-$i)*$min)/($form['r_num']-$i);//随机安全上限
    		$data['us_money']=mt_rand($min*100,$safe_total*100)/100;
    		$data['r_banlance']=$data['r_banlance']-$data['us_money'];
    		$this->db->insert('user_snatch',filter($data, $members_banlance));
		}
		$data['us_money']=$data['r_banlance'];
		$data['r_banlance']=0;
		$this->db->insert('user_snatch',filter($data, $members_banlance));

		return $id;
	}


	/*
	 * 抢红包
	 */
	public function snatch($form)
	{
		//check token get user
		if (isset($form['token']))
		{
			$this->load->model('User_model','my_user');
			$user = $this->my_user->get($form);
		}

		//check if snatch
		if ( $ret = $this->db->select('u_id')
							 ->where(array('u_id'=>$user,'r_id' => $form['r_id']))
							 ->get('user_snatch')
							 ->result_array())
		{
			throw new Exception("已抢过该红包");
		}

		//check if exist
		if ( $ret = $this->db->select(array('us_id','us_money'))
							 ->where('u_id =', 0)
							 ->where(array('r_id' => $form['r_id']))
							 ->get('user_snatch')
							 ->result_array())
		{
			$this->db->update('user_snatch', array('u_id'=>$user),$ret[0]);
			return $ret[0]['us_money'];
		}
		else
		{
			throw new Exception("抢完了");
		}
	}


	/*
	 * 获取红包详情
	 */
	public function red_detail($form)
	{
		//check token
		if (isset($form['token']))
		{
			$this->load->model('User_model','my_user');
			$user = $this->my_user->get($form);
		}

		$data['from'] = $this->db->select(array('u_tel','u_nickname','r_money', 'r_num'))
								 ->join('user_t','user_t.u_id=red_packet.u_id')
								 ->get_where('red_packet', array('r_id' => $form['r_id']))
								 ->result_array()[0];

		$banlance = $this->db->select('r_banlance')
							 ->where('u_id >', 0)
							 ->where(array('r_id' => $form['r_id']))
							 ->order_by('r_banlance','ASC')
							 ->get('user_snatch')
							 ->result_array();
		if (empty($banlance))
		{
			$data['from']['r_banlance']=$data['from']['r_money'];
		}
		else
		{
			$data['from']['r_banlance']=$banlance[0]['r_banlance'];
		}
		$data['other'] = $this->db->select(array('u_tel','u_nickname', 'us_money'))
								  ->join('user_t','user_t.u_id=user_snatch.u_id')
								  ->where('user_snatch.u_id >',0)
								  ->get_where('user_snatch', array('r_id' => $form['r_id']))
						  	  	  ->result_array();

		return $data;
	}


	/*
	 * 删除会议标签
	 */
	public function delete_label($form)
	{
		//config
		$members = array('m_id', 'l_id');

		//check token
		if (isset($form['token']))
		{
			$this->load->model('User_model', 'my_user');
  			$this->my_user->check_token($form['token']);
  		}

		//check m_id
		if ( ! $ret =  $this->db->select('m_id')
								->where(array('m_id' => $form['m_id']))
								->get('meeting_t')
								->result_array())
		{
			throw new Exception("该会议不存在");
		}

		//check l_id
		if ( ! $ret =  $this->db->select('l_id')
								->where(array('l_id' => $form['l_id']))
								->get('sys_label')
								->result_array())
		{
			throw new Exception("该标签不存在");
		}

		//do delete
		$this->db->delete('meeting_label', filter($form, $members));
	}
}

?>
