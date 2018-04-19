<?php

/*header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Utoken");
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');*/

defined('BASEPATH') OR exit('No direct script access allowed');


class Label_model extends CI_Model {


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
	public function get($form)
	{
		//config
		$members = array('l_id', 'l_name');

		//check token
		$this->load->model('User_model', 'user');
		if (isset($form['token']))
		{
		 	$this->user->check_token($form['token']);
		}

		//get target
		$where = array('l_id' => $form['l_id']);
		if (! $res = $this->db->where($where)
				 			  ->get('sys_label')
							  ->result_array())
		{
			throw new Exception('标签不存在');
		}

		$data = $res[0];
		$data = filter($data, $members);

		return $data;
	}


	/**
	 * 添加标签
	 */
	public function register($form)
	{

		//config
		$member = array('l_name');

		//check token
		$this->load->model('User_model', 'user');
		$this->user->check_token($form['token']);

		//check repeat
		if ( $repeat = $this->db->select('l_name')
								->where(array('l_name' => $form['l_name']))
								->get('sys_label')
								->result_array())
		{
			throw new Exception('已存在同名标签！');
		}

		//insert
		$this->db->insert('sys_label', filter($form, $member));

	}


	/**
	 * 修改标签
	 */
	public function update($form)
	{
		//config
		$members = array('l_id', 'l_name');

		//check token
		$this->load->model('User_model', 'user');
		$this->user->check_token($form['token']);

		//check repeat
		if ( $repeat = $this->db->select('l_name')
								->where(array('l_name' => $form['l_name']))
								->get('sys_label')
								->result_array())
		{
			throw new Exception('已存在同名标签');
		}

		//update
		$where = array('l_id' => $form['l_id']);
		$this->db->update('sys_label', filter($form, $members), $where);
	}


	/*
	 * 删除标签
	 */
	public function delete($form)
	{
		//config
		$member = array('l_id');

		//check token
		$this->load->model('User_model', 'user');
		$this->user->check_token($form['token']);

		//check l_id
		if(!$ret = $this->db->select('l_id')
							->where(array('l_id'=>$form['l_id']))
							->get('sys_label')
							->result_array())
		{
			throw new Exception("标签不存在");
		}

		//do delete
		$this->db->delete('sys_label', filter($form, $member));
		$this->db->delete('meeting_label', filter($form, $member));	
	}
}
