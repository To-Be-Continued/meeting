<?php
defined ('BASEPATH') OR exit('No direct script access allowed');

class System_model extends CI_Model{
	/*****************************************************************************************************
	 * 私有工具集
	 *****************************************************************************************************/


	private function my_sort($arrays,$sort_key,$sort_order=SORT_ASC,$sort_type=SORT_NUMERIC )
	{   
        if(is_array($arrays))
        {   
            foreach ($arrays as $array)
            {   
                if(is_array($array))
                {   
                    $key_arrays[] = $array[$sort_key];   
                }
                else
                {   
                    return false;   
                }   
            }   
        }
        else
        {   
            return false;   
        }  
        array_multisort($key_arrays,$sort_order,$sort_type,$arrays);   
        return $arrays;   
    }



	/**********************************************************************************************
	 * 业务接口
	 **********************************************************************************************/


	/* 
	 * 首页 今日会议
	 */
	public function today_meetings($form)
	{
		//check token
		$this->load->model('User_model', 'my_user');
		if (isset($form['token']))
		{
			$this->my_user->check_token($form['token']);
		}

		//get
		$data = array('m_id', 'm_imgpath', 'm_theme', 'm_introduction', 'm_length', 'm_startdate');
		$where = array('m_startdate' => $form['m_startdate']);
		$ret = $this->db->select($data)
				  		   ->like($where)
						   ->get('meeting_t')
						   ->result_array();
		if ( ! $ret )
		{
			throw new Exception('今日无会议');
		}
		
		//return
		return $ret;
	}


	/*
	 * 首页 全部会议
	 */	
	public function all_meetings($form)
	{
		//check token &get user
		$this->load->model('User_model', 'my_user');
		if (isset($form['token']))
		{
			$this->my_user->check_token($form['token']);
		}

		$where = array('token' => $form['token']);
		$user = $this->db->select('u_id')
					 ->where($where)
					 ->get('sys_token')
					 ->result_array()[0]['u_id'];



		$wheres = $this->db->select('m_id')
						   ->where(array('u_id' => $user))
						   ->get('meeting_participants')
						   ->result_array();

		//get_list
		$data = array('m_id', 'm_imgpath', 'm_theme', 'm_introduction', 'm_length', 'm_startdate', 'm_starttime');
		if ( ! $wheres )
		{
			throw new Exception("无参加任何会议");
		}
		foreach ($wheres as $key => $value) 
		{
			$ret[$key] = $this->db->select($data)
						->where($value)
						->get('meeting_t')
						->result_array()[0];
		}

		$ans = $this->my_sort($ret, 'm_startdate', SORT_DESC, SORT_REGULAR);
		//return
		return $ans;
	}


	/*
	 * 我创建的会议
	 */
	public function setted_meeting($form)
	{
		//check token &get user
		$this->load->model('User_model', 'my_user');
		if (isset($form['token']))
		{
			$this->my_user->check_token($form['token']);
		}
		//get user
		$where = array('token' => $form['token']);
		$user = $this->db->select('u_id')
					 ->where($where)
					 ->get('sys_token')
					 ->result_array()[0]['u_id'];

		//get
		$data = array('m_id', 'm_imgpath', 'm_theme', 'm_introduction', 'm_length', 'm_startdate', 'm_starttime');
		$ret = $this->db->select($data)
						->where(array('m_createrId' => $user))
						->get('meeting_t')
						->result_array();

		if (! $ret )
		{
			throw new Exception("未创建过会议");
		}
		//return
		$ans = $this->my_sort($ret, 'm_startdate', SORT_DESC, SORT_REGULAR);
		return $ans;
	}


	/*
	 * 会议详情
	 */
	public function details_of_meeting($form)
	{
		//check token
		$this->load->model('User_model', 'my_user');
		if (isset($form['token']))
		{
			$this->my_user->check_token($form['token']);
		}

		//check m_id & release_meeting
		$where = array('m_id' => $form['m_id']);
		$data = array('m_theme', 'group_id', 'm_introduction', 'm_startdate', 'm_starttime','m_place', 'm_num', 'm_sponsor', 'm_organizer');
		if ( ! $ret = $this->db->select($data)
		 				   ->where($where)
						   ->get('meeting_t')
						   ->result_array())
		{
			throw new Exception("该会议不存在");
		}

		$ret[0]['s_id'] = null;
		
		if ($que = $this->db->select('s_id')
								->where($where)
								->get('meeting_sign')
								->result_array())
		{
			$ret[0]['s_id']=$que[0]['s_id'];
		}
		return $ret[0];
	}


	/*
	 * 我的名片
	 */
	public function personal_center($form)
	{
		//check token &get user
		$this->load->model('User_model', 'my_user');
		if (isset($form['token']))
		{
			$this->my_user->check_token($form['token']);
		}
		//get user
		$where = array('token' => $form['token']);
		$user = $this->db->select('u_id')
					 ->where($where)
					 ->get('sys_token')
					 ->result_array()[0];

		$data = array('u_imgpath', 'u_nickname', 'u_position', 'u_company', 'u_tel', 'u_email', 'u_qq', 'u_weChat', 'u_address');
		if ( ! $ret = $this->db->select($data)
							   ->where($user)
							   ->get('user_t')
							   ->result_array())
		{
			throw new Exception("获取失败");			
		}
		//return
		return $ret[0];
	}

}