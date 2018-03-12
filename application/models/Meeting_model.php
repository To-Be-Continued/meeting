<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Meeting_model extends CI_Model {


	/*
	 * 签到
	 */
	public function do_sign($form)
	{
		//config
		$members = array('m_id', 'u_id');

		//check token &get u_id
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
}

?>
