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
	 * 上传头像
	 */
	public function upload_img()
	{
		if ($_SERVER['REQUEST_METHOD'] == "OPTIONS") 
		{
			return;
		}

		//config
		$members = array('token', 'u_id', 'u_imgpath');

		//get u_tel
		$post['token'] = get_token();
		$this->load->model('User_model', 'my_user');
		$user = $this->my_user->get($post);
		$post['u_id'] = $user;

		//upload config
		$config['upload_path'] = './uploads/user_img/';
		$config['allowed_types'] = 'gif|jpg|png|jpeg';
		$config['file_name'] = $user;
		$config['overwrite'] = TRUE;
		$config['max_size'] = 10000;
		$config['max_width'] = 1980;
		$config['max_height'] = 1024;

		//upload
		try
		{
			$this->load->library('upload', $config);

			if ( ! $this->upload->do_upload('userfile'))
        	{
            	//$error = array('error' => );
            	//print_r($error);die;
            	//output_data(0, '上传失败', $error);die;
            	throw new Exception($this->upload->display_errors());
	        }
    		else
        	{
        		$data = array('upload_data' => $this->upload->data()); 
            	$post['u_imgpath'] = base_url().'uploads/user_img/'.$data['upload_data']['file_name'];;

            	//upload & filter            	
            	$this->load->model('User_model', 'user');
            	$ret = $this->user->upload_img(filter($post, $members));
            	
        	}
		}
		catch(Exception $e)
		{
			output_data($e->getCode(), $e->getMessage(), array());
			return;
		}

		//return
		output_data(1, '上传成功', $ret);
	}


	/*
	 * get user icon
	 */
	public function get_icon()
	{
		//config
		$members = array('token', 'op_tel');

		//get
		try
		{
			//get post
 			$post = get_post();
 			if ( empty($post) )
 			{
 				$post = array(
					'op_tel' => $this->input->post('ob_tel')
				);
 			}
			$post['token'] = get_token();

 			//check form
 			$this->load->library('form_validation');
 			$this->form_validation->set_data($post);
 			if ( ! $this->form_validation->run('get_img'))
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

 			//过滤 && get
 			$this->load->model('User_model','my_user');
 			$data = $this->my_user->get_icon(filter($post, $members));
		}
		catch (Exception $e)
		{
			output_data($e->getCode(), $e->getMessage(), array());
 			return;
		}

		//return
 		output_data(1, '获取成功', $data);

	}
}
?>
