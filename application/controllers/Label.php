<?php

/*header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, token");
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');*/

defined('BASEPATH') OR exit('No direct script access allowed');


class Label extends CI_Controller {


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
	 * 获取标签记录
	 */
	public function get()
	{
		//config
		$members = array('token', 'l_id');

		//get
		try 
		{
			//get post
			$post['token'] = get_token();
			if ( ! $this->input->get('l_id'))
			{
				throw new Exception('必须指定l_id');
			}
			$post['l_id'] = $this->input->get('l_id');			

			//Do get
			$this->load->model('Label_model', 'my_label');
			$data = $this->my_label->get(filter($post, $members));
		 	
		} 
		catch (Exception $e) 
		{
		 	output_data($e->getCode(), $e->getMessage(), array());
			return;
		}
		
		//return
		output_data(1, '获取成功', $data);
	}
	

    /**
	 * 增加标签
	 */
	public function register()
	{
		//config
		$members = array('token', 'l_name');

		//post
		try
		{
			//get post
			$post = get_post();
			if (empty($post))
			{
				$post = array(
					'l_name' => $this->input->post('l_name')
				);
			}
			$post['token'] = get_token();

			//check form
			$this->load->library('form_validation');
			$this->form_validation->set_data($post);
			if ( ! $this->form_validation->run('label_register'))
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

			//过滤 && insert
			$this->load->model('Label_model', 'my_label');
			$this->my_label->register(filter($post, $members));

		}
		catch (Exception $e)
		{
			output_data($e->getCode(), $e->getMessage(), array());
			return;
		}

		//return
		output_data(1, '增加成功', array());

	}


	/**
	 * 修改标签
	 */
	public function update()
	{
		//config
		$members = array('token', 'l_id', 'l_name');

		//post
		try
		{
			//get post
			$post = get_post();
			if (empty($post))
			{
				$post = array(
					'l_id'   => $this->input->post('l_id'),
					'l_name' => $this->input->post('l_name')
				);
			}
			$post['token'] = get_token();

			//check form
			$this->load->library('form_validation');
			$this->form_validation->set_data($post);
			if ( ! $this->form_validation->run('label_update'))
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

			//DO update
			$this->load->model('Label_model', 'my_label');
			$this->my_label->update(filter($post, $members));

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
	 * 删除标签
	 */
	public function delete()
	{
		//config
		$members = array('token', 'l_id');

		//post
		try
		{
			//get post
			$post = get_post();
			if (empty($post))
			{
				$post = array(
					'l_id'   => $this->input->post('l_id')
				);
			}
			$post['token'] = get_token();

			//check form
			$this->load->library('form_validation');
			$this->form_validation->set_data($post);
			if ( ! $this->form_validation->run('label_delete'))
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

			//DO update
			$this->load->model('Label_model', 'my_label');
			$this->my_label->delete(filter($post, $members));

		}
		catch (Exception $e)
		{
			output_data($e->getCode(), $e->getMessage(), array());
			return;
		}

		//return
		output_data(1, '删除成功', array());
	} 

}
