<?php

/*header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Utoken");
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');*/

defined('BASEPATH') OR exit('No direct script access allowed');


class User extends CI_Controller {


	/*****************************************************************************************************
	 * 测试区域
	 *****************************************************************************************************/
	public function test()
	{
	}


	/*****************************************************************************************************
	 * 工具集
	 *****************************************************************************************************/


	/*****************************************************************************************************
	 * 主接口
	 *****************************************************************************************************/

	/**
	 * 注册
	 */
	public function register()
	{

		//config
		$members = array('u_tel', 'u_pwdF', 'u_pwdS');

		//register
		try
		{
			//get post
			$post = get_post();
			if ( empty($post) )
			{
				$post = array(
					'u_tel'  => $this->input->post('u_tel') ,
					'u_pwdF' => $this->input->post('u_pwdF'),
					'u_pwdS' => $this->input->post('u_pwdS')
				);
			}

			//check form
			$this->load->library('form_validation');
			$this->form_validation->set_data($post);
			if ( ! $this->form_validation->run('register'))
			{
				$this->load->helper('form');
				foreach ($members as $member)
				{
					if (form_error($member))
					{
						throw new Exception(strip_tags(form_error($member)));
					}
				}
				return;
			}

			//过滤 && register
			$this->load->model('User_model','my_user');
			$this->my_user->register(filter($post, $members));

		}
		catch (Exception $e)
		{
			output_data($e->getCode(), $e->getMessage(), array());
			return;
		}

		//return
		output_data(1, '注册成功', array());
	}


	/**
	 * login
	 */
	public function login()
	{
		//config
		$members = array('u_tel', 'u_pwd');

		//login
 		try
 		{
 			//get post
 			$post = get_post();
 			if ( empty($post) )
 			{
 				$post = array(
	 				'u_tel' => $this->input->post('u_tel'),
					'u_pwd' => $this->input->post('u_pwd')
				);
 			}

 			//check form
 			$this->load->library('form_validation');
 			$this->form_validation->set_data($post);
 			if ( ! $this->form_validation->run('login'))
 			{
 				$this->load->helper('form');
 				foreach ($members as $member)
 				{
 					if (form_error($member))
 					{
 						throw new Exception(strip_tags(form_error($member)));
 					}
 				}
 				return;
 			}

 			//过滤 && login
 			$this->load->model('User_model','my_user');
 			$data = $this->my_user->login(filter($post, $members));
 		}
 		catch (Exception $e)
 		{
 			output_data($e->getCode(), $e->getMessage(), array());
 			return;
 		}

 		//return
 		output_data(1, '登录成功', $data);
	}


	 /*
	  * 修改名片
	  */
	 public function change_business_card()
	 {
		//config
		$members = array('token', 'u_nickname', 'u_position', 'u_company','u_email',
 						 'u_qq','u_weChat','u_address','u_showCard');

		//edit
		try
		{
			//get post
 			$post = get_post();
 			if ( empty($post) )
 			{
 				$post = array(
	 				'u_nickname' => $this->input->post('u_nickname'),
					'u_position' => $this->input->post('u_position'),
					'u_company'  => $this->input->post('u_company')	,
					'u_email' 	 => $this->input->post('u_email')	,
					'u_qq' 		 => $this->input->post('u_qq')		,
					'u_weChat' 	 => $this->input->post('u_weChat')	,
					'u_address'  => $this->input->post('u_address')	,
					'u_showCard' => $this->input->post('u_showCard')
				);
 			}
			$post['token'] = get_token();

 			//check form
 			$this->load->library('form_validation');
 			$this->form_validation->set_data($post);
 			if ( ! $this->form_validation->run('info'))
 			{
 				$this->load->helper('form');
 				foreach ($members as $member)
 				{
 					if (form_error($member))
 					{
 						throw new Exception(strip_tags(form_error($member)));
 					}
 				}
 				return;
 			}

 			//过滤 && change
 			$this->load->model('User_model','my_user');
 			$this->my_user->change_business_card(filter($post, $members));
		}
		catch (Exception $e)
		{
			output_data($e->getCode(), $e->getMessage(), array());
 			return;
		}

		//return
 		output_data(1, '修改成功', array());
	 }


	/*
	 * 建立会议
	 */
	public function set_up_meeting()
	{
		//config
		$members = array('token', 'm_theme', 'm_introduction', 'm_startdate','m_starttime',
		 				'm_length','m_place','m_sponsor','m_organizer','m_open',
						'm_autoJoin','m_3DSign','m_luckyDog','m_vote','m_num');

		//set_up
		try
		{
			//get post
 			$post = get_post();
 			if ( empty($post) )
 			{
 				$post = array(
 					'm_theme' 		 => $this->input->post('m_theme')		,
 					'm_introduction' => $this->input->post('m_introduction'),
 					'm_startdate' 	 => $this->input->post('m_startdate')	,
 					'm_starttime' 	 => $this->input->post('m_starttime')	,
 					'm_length' 		 => $this->input->post('m_length')		,
 					'm_place' 		 => $this->input->post('m_place')		,
 					'm_sponsor' 	 => $this->input->post('m_sponsor')		,
 					'm_organizer'    => $this->input->post('m_organizer')	,
 					'm_open' 		 => $this->input->post('m_open')		,
 					'm_autoJoin' 	 => $this->input->post('m_autoJoin')	,
 					'm_3DSign' 		 => $this->input->post('m_3DSign')		,
 					'm_luckyDog' 	 => $this->input->post('m_luckyDog')	,
 					'm_vote' 		 => $this->input->post('m_vote')

 				);
 			}
			$post['token'] = get_token();

 			//check form
 			$this->load->library('form_validation');
 			$this->form_validation->set_data($post);
 			if ( ! $this->form_validation->run('meeting_info'))
 			{
 				$this->load->helper('form');
 				foreach ($members as $member)
 				{
 					if (form_error($member))
 					{
 						throw new Exception(strip_tags(form_error($member)));
 					}
 				}
 				return;
 			}

 			//过滤 && set_up
 			$this->load->model('User_model','my_user');
 			$data = filter($post, $members);
 			$this->my_user->set_up_meeting($data);
		}
		catch (Exception $e)
		{
			output_data($e->getCode(), $e->getMessage(), array());
 			return;
		}

		//return
 		output_data(1, '创建成功', array());
	}


	/*
	 * 发布会议
	 */
	public function release_meeting()
	{
		//config
		$members = array('token','m_id');

		//release
		try
		{
			//get post
 			$post['token'] = get_token();
			if($this->input->get('m_id'))
			{
				$post['m_id'] = $this->input->get('m_id');
			}

  			//check form
  			$this->load->library('form_validation');
  			$this->form_validation->set_data($post);
  			if ( ! $this->form_validation->run('release_meeting'))
  			{
  				$this->load->helper('form');
  				foreach ($members as $member)
  				{
  					if (form_error($member))
  					{
  						throw new Exception(strip_tags(form_error($member)));
  					}
  				}
  				return;
  			}

  			//过滤 && release
  			$this->load->model('User_model','my_user');
  			$data = $this->my_user->release_meeting(filter($post, $members));

		}
		catch (Exception $e)
		{
			output_data($e->getCode(), $e->getMessage(), array());
			return;
		}

		//return
  		output_data(1, '发布成功', $data);

	}


	/*
	 * 删除会议
	 */
	public function delete_meeting()
	{
		//config
		$members = array('token','m_id');

		//delete
		try
		{
			//get post
			$post = get_post();
			if ( empty($post) )
		    {
			    $post['m_id'] = $this->input->post('m_id');
		    }
		    $post['token'] = get_token();

		    //check form
		    $this->load->library('form_validation');
		    $this->form_validation->set_data($post);
		    if ( ! $this->form_validation->run('delete_meeting'))
		    {
		 	    $this->load->helper('form');
		 	    foreach ($members as $member)
			    {
				    if (form_error($member))
				    {
					    throw new Exception(strip_tags(form_error($member)));
				    }
			    }
			    return;
		    }

		    //过滤 && delete
		    $this->load->model('User_model','my_user');
		    $data = $this->my_user->delete_meeting(filter($post, $members));

		}
		catch (Exception $e)
		{
			output_data($e->getCode(), $e->getMessage(), array());
			return;
		}

		//return
	    output_data(1, '删除成功', array());
	}


	/*
	 * 修改会议
	 */
	public function change_meeting()
	{
		//config
		$members = array('token', 'm_id', 'm_theme', 'm_introduction', 'm_startdate','m_starttime',
		 				'm_length','m_place','m_sponsor','m_organizer','m_open',
						'm_autoJoin','m_3DSign','m_luckyDog','m_vote');

		//change
		try
		{
			//get post
			$post = get_post();
			if ( empty($post) )
			{
				$post = array(
					'm_id'			 => $this->input->post('m_id')			,
 					'm_theme' 		 => $this->input->post('m_theme')		,
 					'm_introduction' => $this->input->post('m_introduction'),
 					'm_startdate' 	 => $this->input->post('m_startdate')	,
 					'm_starttime'	 => $this->input->post('m_starttime')	,
 					'm_length' 		 => $this->input->post('m_length')		,
 					'm_place' 		 => $this->input->post('m_place')		,
 					'm_sponsor' 	 => $this->input->post('m_sponsor')		,
 					'm_organizer'    => $this->input->post('m_organizer')	,
 					'm_open' 		 => $this->input->post('m_open')		,
 					'm_autoJoin' 	 => $this->input->post('m_autoJoin')	,
 					'm_3DSign' 		 => $this->input->post('m_3DSign')		,
 					'm_luckyDog' 	 => $this->input->post('m_luckyDog')	,
 					'm_vote' 		 => $this->input->post('m_vote')

 				);
			}
 			$post['token'] = get_token();

  			//check form
  			$this->load->library('form_validation');
  			$this->form_validation->set_data($post);
  			if ( ! $this->form_validation->run('meeting_info'))
  			{
  				$this->load->helper('form');
  				foreach ($members as $member)
  				{
  					if (form_error($member))
  					{
  						throw new Exception(strip_tags(form_error($member)));
  					}
  				}
  				return;
  			}

  			//过滤 && change
  			$this->load->model('User_model','my_user');
  			$data = $this->my_user->change_meeting(filter($post, $members));

		}
		catch (Exception $e)
		{
			output_data($e->getCode(), $e->getMessage(), array());
			return;
		}

		//return
  		output_data(1, '修改成功', array());
	}


	/*
	 * 参加会议
	 */
	public function join_meeting()
	{
		//config
		$members = array('token', 'm_id');

		//join
		try
		{
			//get POST
			$post = get_post();
			if ( empty($post) )
			{
				$post = array(
					'm_id' => $this->input->post('m_id')
				);
			}
			$post['token'] = get_token();

			//check form
  			$this->load->library('form_validation');
  			$this->form_validation->set_data($post);
  			if ( ! $this->form_validation->run('join_meeting'))
  			{
  				$this->load->helper('form');
  				foreach ($members as $member)
  				{
  					if (form_error($member))
  					{
  						throw new Exception(strip_tags(form_error($member)));
  					}
  				}
  				return;
  			}

  			//过滤 && join
  			$this->load->model('User_model','my_user');
  			$data = $this->my_user->join_meeting(filter($post, $members));
		}
		catch (Exception $e)
		{
			output_data($e->getCode(), $e->getMessage(), array());
			return;
		}

		//return
  		output_data(1, '加入成功', array());
	}


	/*
	 * 会议参与者  fetch u_nickname
	 */
	public function meeting_actor()
	{
		//config

	}


	/*
	 * 会议管理人员 
	 */
	public function meeting_manager()
	{
		//config
		$members = array('token', 'm_id', 'u_id');

		try 
		{
			//get token
			$post = get_post();
			if ( empty($post) )
			{
				$post = array(
					'm_id' => $this->input->post('m_id'),
					'u_id' => $this->input->post('u_id')
				);
			}
			$post['token'] = get_token();

			//check form
  			$this->load->library('form_validation');
  			$this->form_validation->set_data($post);
  			if ( ! $this->form_validation->run('add_manager'))
  			{
  				$this->load->helper('form');
  				foreach ($members as $member)
  				{
  					if (form_error($member))
  					{
  						throw new Exception(strip_tags(form_error($member)));
  					}
  				}
  				return;
  			}

  			//过滤 && join
  			$this->load->model('User_model','my_user');
  			$data = $this->my_user->meeting_manager(filter($post, $members));
		}
		catch (Exception $e)
		{
			output_data($e->getCode(), $e->getMessage(), array());
			return;
		}

		//return
  		output_data(1, '添加成功', array());
	}


	/*
	 * 抽取幸运观众
	 */
	public function meeting_lucky_dog()
	{
		//config
		$members = array('token', 'm_id');

		try 
		{
			//get token
			$post = get_post();
			if (empty($post))
			{
				$post['m_id'] = $this->input->post('m_id');
			}
			$post['token'] = get_token();

			//check form
  			$this->load->library('form_validation');
  			$this->form_validation->set_data($post);
  			if ( ! $this->form_validation->run('meeting_lucky_dog'))
  			{
  				$this->load->helper('form');
  				foreach ($members as $member)
  				{
  					if (form_error($member))
  					{
  						throw new Exception(strip_tags(form_error($member)));
  					}
  				}
  				return;
  			}

  			//过滤 & 抽奖
			$this->load->model('User_model', 'my_user');
			$data = $this->my_user->meeting_lucky_dog(filter($post,$members));
		} 
		catch (Exception $e)
 	 	{
 		 	output_data($e->getCode(), $e->getMessage(), array());
 		 	return;
 	 	}
		
		//return
		output_data(1,'幸运观众', $data);
	}


	/*
	 * 设置投票项
	 */
	public function set_vote()
	{
		//config
		$members = array('token', 'm_id', 'u_id');

		//set vote
		try
		{
			//get post
			$post = get_post();
			if ( empty($post) )
			{
				$post = array(
					'm_id' => $this->input->post('m_id'),
					'u_id' => $this->input->post('u_id')
				);
			}
			$post['token'] = get_token();

			//check form
			$this->load->library('form_validation');
	 		$this->form_validation->set_data($post);
	 		if ( ! $this->form_validation->run('set_vote'))
	 		{
	 			$this->load->helper('form');
	 			foreach ($members as $member)
	 			{
	 				if (form_error($member))
	 				{
	 					throw new Exception(strip_tags(form_error($member)));
	 				}
	 			}
	 			return;
	 		}

	 		//过滤 && set
	 		$this->load->model('User_model','my_user');
	 		$this->my_user->set_vote(filter($post, $members));
 	  	}
 	 	catch (Exception $e)
 	 	{
 		 	output_data($e->getCode(), $e->getMessage(), array());
 		 	return;
 	 	}

 	 	//return
 	 	output_data(1, '设置成功', array());

	}


	/*
	 * 投票功能
	 */
	public function meeting_vote_function()
	{
		//config
		$members = array('token', 'm_id', 'u_id');

		//vote
		try
		{
			//get post
			$post = get_post();
			$post['token'] = get_token();
			
			//check form
			$this->load->library('form_validation');
	 		$this->form_validation->set_data($post);
	 		if ( ! $this->form_validation->run('set_vote'))
	 		{
	 			$this->load->helper('form');
	 			foreach ($members as $member)
	 			{
	 				if (form_error($member))
	 				{
	 					throw new Exception(strip_tags(form_error($member)));
	 				}
	 			}
	 			return;
	 		}

			//do vote
			$this->load->model('User_model','my_user');
			$this->my_user->meeting_vote_function(filter($members,$post));

		}
		catch (Exception $e)
		{
			output_data($e->getCode(), $e->getMessage(), array());
			return;
		}

		//return
		output_data(1, '投票成功', array());
	}

}
?>
