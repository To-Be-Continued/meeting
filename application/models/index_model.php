 <?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Index_model extends CI_Model
{
	/*---------------------------------------------------------------------*/
	/***************************私有工具集**********************************/
	/*-------------------------------------------------------------------*/
	
	// 生成一个未被占用的Utoken
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
	
	//检测时间差
	private function is_timeout($last_visit)
	{
		$this->load->helper('date');
		$pre_unix = human_to_unix($last_visit);
		$now_unix = time();
		return $now_unix - $pre_unix < 0;
	}
	/*----------------------------------------------------------------------*/
	/**************************公开工具集************************************/
	/*--------------------------------------------------------------------*/

	// 检测凭据
	public function check_token($token)
	{
		$where = array('token' => $token);
		if ( ! $result = $this->db->select('last_visit')
									->where($where)
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
	
	// 获取用户信息和会议信息
	public function get($form)
	{
		//check token & get user
		if (isset($form['token']))
		{
			$this->check_token($form['token']);
		}
		$where = array('token' => $form['token']);
		//print_r($where);die;
		$u_id = $this->db->select('u_id')
					 ->where($where)
					 ->get('sys_token')
					 ->row_array()['u_id'];
	    $w = array('u_id' => $u_id); 
		$user['user'] = $this->db->select('u_tel,u_pwd')
					->where($w)
					->get('user_t')
					->row_array();
		return $user;
	}

	/*---------------------------------------------------------------------*/
	/***************************接口业务************************************/
	/*-------------------------------------------------------------------*/
	
	/**
	*web login
	*/
	public function m_login($form)  
	{
		$form['m_pwd'] = md5($form['m_pwd']); 	
		if(!$result = $this->db->select('m_createrId')
								->where($form)
								->get('meeting_t')
								->row_array())
		{
			echo "<script type='text/javascript'>alert('会议ID不存在或者密码错误')</script>";
			redirect('index/login');
		}
		//update token
		$where = array('u_id' => $result['m_createrId']);
		$user = $this->db->select('last_visit')
						->where($where)
						->get('sys_token')
						->row_array();
		
		$new_data = array('last_visit' => date('Y-m-d H:i:s',time()));
		
		if($this->is_timeout($user['last_visit']))
		{
			$new_data['token'] = $this->create_token();
		}	

		$this->db->update('sys_token',$new_data, $where);
		//return rel
		$ret = $this->db->select('token')
						   ->where($where)
						   ->get('sys_token')
						   ->row_array();
		/**
		*存入token,需要$config['sess_save_path'] = FCPATH.'public/sess_save_path';
		*/
		
		//session_start();
		$_SESSION['m_id'] = $form['m_id'];
		$_SESSION['token'] = $ret['token'];
		//echo $_SESSION['token'];die;
		//重定向
		redirect('index/showHome'); 
	}

	/**
	*投票数据
	*/
	public function voteData($data)
	{
		//获取会议投票信息
		$vote_info = $this->db->where($data)
						->get('meeting_vote')
						->row_array(); 
		$vote_info['v_starttime'] = strtotime($vote_info['v_starttime']);
		$vote_info['v_endtime'] = strtotime($vote_info['v_endtime']);
		
		//获取投票选项信息
		$where = array(
			'v_id' => $vote_info['v_id'],
			'm_id' => $vote_info['m_id']
		);	
		$option = $this->db->select('v_option')
								->where($where)
								->get('vote_option')
								->result_array();
		foreach ($option as $key => $value) {
			$vote_option[$key] = $value['v_option'];
		}
	
		$data = array(
			'vote_info' => $vote_info,
			'vote_option' => $vote_option
		);
		return $data;
	}
	/**
	*存入数据
	*/
	public function insert_vote($data)
	{
		foreach ($data['voteData'] as $key => $value) {
   			$this->db->replace('vote_option',$value);
   		}
   		foreach ($data['voteUsers'] as $key => $value) {
   			$where = array(
   				'u_tel' => $value['u_tel']
   			);
   			$u_id = $this->db->select('u_id')
   							->where($where)
   							->get("user_t")
   							->row_array();
   		    $data = array(
   		    	'v_id' => $value['v_id'],
   		    	'u_id' => $u_id['u_id'],
   		    	'v_option' => $value['v_option']
   		    );
   		    if(!$result = $this->db->select('u_id')
   								->where($u_id)
   								->get('vote_user')
   								->row_array())
   		    {
   				$this->db->insert('vote_user',$data);
   			}		
   		}
		return $u_id;
	} 
	/**
	*抽奖数据
	*/
	public function drawData($data)
	{
		$where = array(
			'm_id' => $data['m_id']
		);
		$part = $this->db->select('u_tel,u_imgpath')
							->where($where)
							->from('user_t')
							->join('meeting_participants','user_t.u_id = meeting_participants.u_id')
							->get()
							->result_array();
		foreach ($part as $key => $value) {
			$u_img[$key] = $value['u_imgpath'];
			$u_name[$key] = $value['u_tel'];
		}
		$draw_data = array(
			'u_img' => $u_img,
			'u_name' => $u_name
		);
		return $draw_data;
	}
	/**
	*签名墙数据
	*/
	public function signData($data)
	{
		$where = array(
			's_id' => $data['s_id']
		);
		$url = $this->db->select('s_imgpath')
						->where($where)
						->get('meeting_sign')
						->row_array();
		$url_data = array(
        	'url' => $url['s_imgpath']
      	);

      	return $url_data;
	}
	/**
	*确认密码是否修改
	*/
	public function check_pwd()
	{
		$where = array(
			'm_id' => $_SESSION['m_id']
		);
		
		$m_pwd = md5("123456");
		
		if($result = $this->db->select('m_pwd')
								->where($where)
								->get('meeting_t')
								->row_array())
		{
			if($result['m_pwd']==$m_pwd)
			{
				return false;
			}
		}

		return true;
	}
	public function alter_pwd($data)
	{
		$where = array(
			'm_id' => $_SESSION['m_id']
		);
		$m_pwd = array(
			'm_pwd' => md5($data['m_pwd'])
		);
		$this->db->where($where)
				->update('meeting_t',$m_pwd);
	}
}
?>