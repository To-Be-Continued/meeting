<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
* 
*/
class System extends CI_Controller {
	
	
	/*
	 * 首页_今日会议
	 */
	public function today_meetings()
	{
		//config
		$members = array('token', 'm_startdate');

		try
		{
			$post['token'] = get_token();
			$post['m_startdate'] = date('y-m-d', time());

			$this->load->model('System_model', 'my_sys');
			$data = $this->my_sys->today_meetings(filter($post, $members));
		}
		catch (Exception $e)
		{
			output_data($e->getCode(), $e->getMessage(), array());
			return;
		}

		//return
		output_data(1, '获取成功', $data);
	}


	/*
	 * 全部会议
	 */
	public function all_meetings()
	{
		//config
		$members = array('token');

		try 
		{
			$post['token'] = get_token();

			$this->load->model('System_model', 'my_sys');
			$data = $this->my_sys->all_meetings(filter($post, $members));			
		} 
		catch (Exception $e) 
		{
			output_data($e->getCode(), $e->getMessage(), array());
			return;	
		}

		//return
		output_data(1, '获取成功', $data);
	}


	/* 
	 * 我创建的会议
	 */
	public function setted_meeting()
	{
		//config
		$members = array('token');

		try 
		{
			$post['token'] = get_token();
			
			$this->load->model('System_model', 'my_sys');
			$data = $this->my_sys->setted_meeting(filter($post, $members));

		} 
		catch (Exception $e) 
		{
			output_data($e->getCode(), $e->getMessage(), array());
			return;		
		}

		//return
		output_data(1, '获取成功', $data);
	}


	/*
	 * 会议详情
	 */
	public function details_of_meeting()
	{
		//config
		$members = array('token', 'm_id');

		try 
		{
			//get post
			$post = get_post();
			if( empty($post) )
			{
				$post['m_id'] = $this->input->post('m_id');
			}
			$post['token'] = get_token();


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
  			$this->load->model('System_model','my_sys');
  			$data = $this->my_sys->details_of_meeting(filter($post, $members));
		} 
		catch (Exception $e)
		{
			output_data($e->getCode(), $e->getMessage(), array());
			return;
		}

		//return
  		output_data(1, '获取成功', $data);
	}


	/*
	 * 我的名片
	 */
	public function personal_center()
	{
		//config
		$members = array('token');

		try 
		{
			//get post
			$post['token'] = get_token();

			$this->load->model('System_model', 'my_sys');
			$data = $this->my_sys->personal_center(filter($post, $members));
		} 
		catch (Exception $e) 
		{
			output_data($e->getCode(), $e->getMessage(), array());
			return;				
		}

		//return
		output_data(1, '获取成功', $data);
	}


	/*
	 * 关于我们
	 */
}
?>