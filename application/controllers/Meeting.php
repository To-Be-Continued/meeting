<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Meeting extends CI_Controller {
	/*****************************************************************************************************
	 * 工具集
	 *****************************************************************************************************/


	/*
	 * 添加换行
	 */
	public function strsub($str)
	{
		$length = mb_strlen($str, 'utf-8');
		$ret="";
		for ($i=0; $i<$length; $i++)
			$ret=$ret.mb_substr($str, $i, 1, 'utf-8')."\n";
		return $ret;
	}

	
	/*
	 * 合并图像
	 */
	public function merge($data,$id,$text)
	{
		foreach ($data as $key => $value) {

			$data[$key]=$value['si_imgpath'];
		}
		$num = count($data);

		$background = 'uploads/sign_img/background.png'; // 背景图片

		$dst = imagecreatefromstring(file_get_contents($background));
		$font = 'C:\workSpace\ci_a27\font\text.ttf';
		$black = imagecolorallocate($dst, 242, 152, 7);

		//add \n
		$con=$this->strsub($text);
		imagefttext($dst, 50, 0, 75, 70, $black, $font, $con);
		imagepng($dst, 'uploads/sign_img/tmp.png');

		$back = 'uploads/sign_img/tmp.png'; // 背景图片
		$target_img = Imagecreatefrompng($back);
		$source = array();

		foreach ($data as $k => $v) {
			$source[$k]['source'] = Imagecreatefrompng($v);
			$source[$k]['size'] = getimagesize($v);
		}

		$tmpx = 300;
		$tmpy = 0;

		for ($i = 0; $i < $num; $i++) {
			imagecopy($target_img, $source[$i]['source'], $tmpx, $tmpy, 0, 0, $source[$i]['size'][0], $source[$i]['size'][1]);

			$tmpx = $tmpx + $source[$i]['size'][0];
			if ($tmpx > 2000)
			{
				$tmpx=300;
				$tmpy+=200;
			}
		}

		$url='uploads/sign_img/'.$id.'_img.png';

		Imagepng($target_img, $url);
		return base_url().$url;
	}

	/*****************************************************************************************************
	 * 主接口
	 *****************************************************************************************************/


	/*
	 * 建立会议
	 */
	public function set_up_meeting()
	{
		//config
		$members = array('token', 'm_theme', 'm_introduction', 'm_startdate','m_starttime','group_id',
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
 					'group_id'		 => $this->input->post('group_id')		,
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
			$this->load->model('Meeting_model','my_meeting');
 			$data = filter($post, $members);
 			$this->my_meeting->set_up_meeting($data);
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
			$this->load->model('Meeting_model','my_meeting');
  			$data = $this->my_meeting->release_meeting(filter($post, $members));

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
			$this->load->model('Meeting_model','my_meeting');
		    $data = $this->my_meeting->delete_meeting(filter($post, $members));

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
			$this->load->model('Meeting_model','my_meeting');
  			$data = $this->my_meeting->change_meeting(filter($post, $members));

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
	 * 会议参与者  fetch u_nickname
	 */
	public function meeting_actor()
	{
		//config
		$members = array('token', 'm_id');

		//get
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
  			if ( ! $this->form_validation->run('meeting_actor'))
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
			$this->load->model('Meeting_model','my_meeting');
  			$data = $this->my_meeting->meeting_actor(filter($post, $members));
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
			$this->load->model('Meeting_model','my_meeting');
			$data = $this->my_meeting->meeting_lucky_dog(filter($post,$members));
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
		$members = array('token', 'm_id', 'v_title', 'v_summary', 'v_option', 'v_type', 'v_starttime', 'v_endtime');

		//set vote
		try
		{
			//get post
			$post = get_post();
			if ( empty($post) )
			{
				$post = array(
					'm_id'        => $this->input->post('m_id'),
					'v_title'     => $this->input->post('v_title'),
					'v_summary'   => $this->input->post('v_summary'),
					'v_option'    => $this->input->post('v_option'),
					'v_type'      => $this->input->post('v_type'),
					'v_starttime' => $this->input->post('v_starttime'),
					'v_endtime'   => $this->input->post('v_endtime')
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
			$this->load->model('Meeting_model','my_meeting');
	 		$data = $this->my_meeting->set_vote(filter($post, $members));
 	  	}
 	 	catch (Exception $e)
 	 	{
 		 	output_data($e->getCode(), $e->getMessage(), array());
 		 	return;
 	 	}

 	 	//return
 	 	output_data(1, '设置成功', $data);

	}


	/*
	 * 投票详情
	 */
	public function vote_detail()
	{
		//config
		$members = array('token', 'm_id', 'v_id');

		//get post
		try
		{
			//get post
			$post = get_post();
			if ( empty($post) )
			{
				$post = array(
					'm_id' => $this->input->post('m_id'),
					'v_id' => $this->input->post('v_id')
				);
			}
			$post['token'] = get_token();

			//check form
			$this->load->library('form_validation');
	 		$this->form_validation->set_data($post);
	 		if ( ! $this->form_validation->run('vote_detail'))
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

			//get list
			$this->load->model('Meeting_model','my_meeting');
			$data = $this->my_meeting->vote_detail(filter($post,$members));

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
	 * 发布会议公告
	 */
	public function set_notice()
	{
		//config
		$members = array('token', 'm_id', 'n_title', 'n_text');

		//get post
		//get post
		try
		{
			//get post
			$post = get_post();
			if ( empty($post) )
			{
				$post = array(
					'm_id'    => $this->input->post('m_id'),
					'n_title' => $this->input->post('n_title'),
					'n_text'  => $this->input->post('n_text')
				);
			}
			$post['token'] = get_token();

			//check form
			$this->load->library('form_validation');
	 		$this->form_validation->set_data($post);
	 		if ( ! $this->form_validation->run('set_notice'))
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

			//do set
			$this->load->model('Meeting_model','my_meeting');
			$this->my_meeting->set_notice(filter($post,$members));

		}
		catch (Exception $e)
		{
			output_data($e->getCode(), $e->getMessage(), array());
			return;
		}

		//return
		output_data(1, '发布成功', array());
	}


	/*
	 * 获取会议公告
	 */
	public function get_notice()
	{
		//config
		$members = array('token', 'm_id');

		//get post
		try
		{
			$post = get_post();
			if(empty($post))
			{
				$post = array(
					'm_id' => $this->input->post('m_id')
				);
			}

			$post['token'] = get_token();

			//check form
			$this->load->library('form_validation');
	 		$this->form_validation->set_data($post);
	 		if ( ! $this->form_validation->run('get_notice'))
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

			//do set
			$this->load->model('Meeting_model','my_meeting');
			$data = $this->my_meeting->get_notice(filter($post,$members));


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
     * 签到
     */
   	public function do_sign()
   	{
   		//config
   		$members = array('token', 'm_id');

   		//do_sign
   		try
		{
			//get post
			$post = get_post();
			if ( empty($post) )
			{
				$post = array('m_id' => $this->input->post('m_id'));
			}
			$post['token'] = get_token();

			//check form
			$this->load->library('form_validation');
			$this->form_validation->set_data($post);
			if ( ! $this->form_validation->run('do_sign'))
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

			//过滤 && do sign
			$this->load->model('Meeting_model','my_meeting');
			$this->my_meeting->do_sign(filter($post, $members));

		}
		catch (Exception $e)
		{
			output_data($e->getCode(), $e->getMessage(), array());
			return;
		}

		//return
		output_data(1, '签到成功', array());

   	}


    /*
     * 生成会议链接
     */
    public function sign_in()
    {

    	$data = site_url().'/Meeting/do_sign';

    	output_data(1, '生成成功', $data);
    }


	/*
     * 创建签名事件
     */
    public function register_sign()
    {
    	//config
    	$members = array('token', 'm_id', 's_starttime', 's_endtime');

    	//get post
    	try
		{
			//get post
			$post = get_post();
			if ( empty($post) )
			{
				$post = array(
					'm_id' 		  => $this->input->post('m_id'),
					's_starttime' => $this->input->post('s_starttime'),
					's_endtime'   => $this->input->post('s_endtime')
				);
			}
			$post['token'] = get_token();

			//check form
			$this->load->library('form_validation');
			$this->form_validation->set_data($post);
			if ( ! $this->form_validation->run('register_sign'))
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

			//过滤 && do sign
			$this->load->model('Meeting_model','my_meeting');
			$data = $this->my_meeting->register_sign(filter($post, $members));

		}
		catch (Exception $e)
		{
			output_data($e->getCode(), $e->getMessage(), array());
			return;
		}

		//return
		output_data(1, '创建成功', $data);
    }


    /*
     * 上传签名图片
     */
    public function upload_sign()
    {
    	if ($_SERVER['REQUEST_METHOD'] == "OPTIONS")
		{
			return;
		}

		//config
		$members = array('token', 's_id', 'si_imgpath');

		//get m_id
		$post['token'] = get_token();
		$post['s_id'] = $this->input->post('s_id');
		$this->load->model('Meeting_model', 'my_meeting');
		$ret = $this->my_meeting->check_sign($post);

		$this->load->model('User_model', 'my_user');
		$user = $this->my_user->get($post);

		$name=$post['s_id'].'_'.$user;

		//upload config
		$config['upload_path'] = './uploads/sign_img/';
		$config['allowed_types'] = 'png';
		$config['file_name'] = $name;
		$config['overwrite'] = TRUE;
		$config['max_size'] = 10000;
		$config['max_width'] = 1980;
		$config['max_height'] = 1024;

		//upload
		try
		{
			//check
			if ( ! $ret )
			{
				throw new Exception("无该签名事件");
			}

			//do upload
			$this->load->library('upload', $config);

			if ( ! $this->upload->do_upload('userfile'))
        	{
            	throw new Exception($this->upload->display_errors());
	        }
    		else
        	{
        		$data = array('upload_data' => $this->upload->data());
            	$post['si_imgpath'] = base_url() . 'uploads/sign_img/' . $data['upload_data']['file_name'];;
            	//upload & filter
            	$this->load->model('Meeting_model', 'my_meeting');
            	$ret = $this->my_meeting->upload_sign(filter($post, $members));

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
     * 合并图片
     */
    public function get_pic()
    {
		//config
		$members = array('token', 's_id', 's_imgpath');

		//get s_id
		$post=get_post();
		if (empty($post))
		{
			$post['s_id'] = $this->input->post('s_id');
		}
		$post['token'] = get_token();


		$this->load->model('Meeting_model', 'my_meeting');
		$ret = $this->my_meeting->check_sign($post);

		$this->load->model('User_model', 'my_user');
		$user = $this->my_user->get($post);

		//merge img
		$data = $this->my_meeting->get_img($post);
		$text = $this->my_meeting->get_text($post);

		$post['s_imgpath']=$this->merge($data,$post['s_id'],$text);

		//do insert
		try
		{
            $this->load->model('Meeting_model', 'my_meeting');
            $ret = $this->my_meeting->get_pic(filter($post, $members));
		}
		catch(Exception $e)
		{
			output_data($e->getCode(), $e->getMessage(), array());
			return;
		}

		//return
		output_data(1, '获取成功', $ret);
    }


    /*
     * 上传会议头像
     */
    public function upload_img()
	{
		if ($_SERVER['REQUEST_METHOD'] == "OPTIONS")
		{
			return;
		}

		//config
		$members = array('token', 'm_id', 'm_imgpath');

		//get m_id
		$post['token'] = get_token();
		$post['m_id'] = $this->input->post('m_id');
		$this->load->model('Meeting_model', 'my_meeting');
		$createrId = $this->my_meeting->check_creatId($post);

		$this->load->model('User_model', 'my_user');
		$user = $this->my_user->get($post);

		//upload config
		$config['upload_path'] = './uploads/meeting_img/';
		$config['allowed_types'] = 'gif|jpg|png|jpeg';
		$config['file_name'] = $post['m_id'];
		$config['overwrite'] = TRUE;
		$config['max_size'] = 10000;
		$config['max_width'] = 1980;
		$config['max_height'] = 1024;

		//upload
		try
		{
			//check
			if ( ! $createrId )
			{
				throw new Exception("无该会议");
			}
			if ($user != $createrId[0]['m_createrId'])
			{
				throw new Exception("无权限更改头像");
			}

			//do upload
			$this->load->library('upload', $config);

			if ( ! $this->upload->do_upload('userfile'))
        	{
            	//$error = array('error' => $this->upload->display_errors());
            	//output_data(0, '上传失败', $error);
            	throw new Exception($this->upload->display_errors());
	        }
    		else
        	{
        		$data = array('upload_data' => $this->upload->data());
            	$post['m_imgpath'] = base_url() . 'uploads/meeting_img/' . $data['upload_data']['file_name'];;
            	//upload & filter
            	$this->load->model('Meeting_model', 'my_meeting');
            	$ret = $this->my_meeting->upload_img(filter($post, $members));

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
	 * get meeting icon
	 */
	public function get_icon()
	{
		//config
		$members = array('token', 'm_id');

		//get
		try
		{
			//get post
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
 			if ( ! $this->form_validation->run('get_meet_img'))
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
            $this->load->model('Meeting_model', 'my_meeting');
 			$data = $this->my_meeting->get_icon(filter($post, $members));
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
	 * 根据主题模糊搜索
	 */
	public function get_theme()
	{
		//config
   		$members = array('token', 'm_theme');

   		//do_sign
   		try
		{
			//get post
			$post = get_post();
			if ( empty($post) )
			{
				$post = array('m_theme' => $this->input->post('m_theme'));
			}
			$post['token'] = get_token();

			//check form
			$this->load->library('form_validation');
			$this->form_validation->set_data($post);
			if ( ! $this->form_validation->run('getlist_theme'))
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

			//过滤 && do sign
			$this->load->model('Meeting_model','my_meeting');
			$data = $this->my_meeting->get_theme(filter($post, $members));

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
  			$this->load->model('Meeting_model','my_meeting');
  			$data = $this->my_meeting->join_meeting(filter($post, $members));
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
	 * invite member
	 */
	public function invite_member()
	{
		//config
		$members = array('token', 'invite_tel', 'invited_tel', 'm_id');

		//invite
		try
		{
			//get POST
			$post = get_post();
			if ( empty($post) )
			{
				$post = array(
					'invite_tel'  => $this->input->post('invited_tel'),
					'invited_tel' => $this->input->post('invited_tel'),
					'm_id'        => $this->input->post('m_id')
				);
			}
			$post['token'] = get_token();

			//check form
  			$this->load->library('form_validation');
  			$this->form_validation->set_data($post);
  			if ( ! $this->form_validation->run('invite_meeting'))
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

  			//过滤 && invite
  			$this->load->model('Meeting_model','my_meeting');
  			$data = $this->my_meeting->invite_member(filter($post, $members));
		}
		catch (Exception $e)
		{
			output_data($e->getCode(), $e->getMessage(), array());
			return;
		}

		//return
  		output_data(1, '邀请成功', array());
	}


	/*
	 * delete meeting member
	 */
	public function delete_member()
	{
		//config
		$members = array('token', 'op_tel', 'del_tel', 'm_id');

		//delete
		try
		{
			//get POST
			$post = get_post();
			if ( empty($post) )
			{
				$post = array(
					'op_tel'  => $this->input->post('op_tel'),
					'del_tel' => $this->input->post('del_tel'),
					'm_id'    => $this->input->post('m_id')
				);
			}
			$post['token'] = get_token();

			//check form
  			$this->load->library('form_validation');
  			$this->form_validation->set_data($post);
  			if ( ! $this->form_validation->run('delete_member'))
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
  			$this->load->model('Meeting_model','my_meeting');
  			$data = $this->my_meeting->delete_member(filter($post, $members));
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
	 * 会议管理人员
	 */
	public function setting_manager()
	{
		//config
		$members = array('token', 'op_tel', 'set_tel', 'm_id');

		try
		{
			//get token
			$post = get_post();
			if ( empty($post) )
			{
				$post = array(
					'op_tel'  => $this->input->post('op_tel'),
					'set_tel' => $this->input->post('set_tel'),
					'm_id'    => $this->input->post('m_id')
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

  			//过滤 && set
  			$this->load->model('Meeting_model','my_meeting');
  			$this->my_meeting->setting_manager(filter($post, $members));
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
	 * 获取会议所有成员
	 */
	public function get_list()
	{
		//config
   		$members = array('token', 'm_id');

   		//do_sign
   		try
		{
			//get post
			$post = get_post();
			if ( empty($post) )
			{
				$post = array('m_id' => $this->input->post('m_id'));
			}
			$post['token'] = get_token();

			//check form
			$this->load->library('form_validation');
			$this->form_validation->set_data($post);
			if ( ! $this->form_validation->run('get_list'))
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

			//过滤 && do sign
			$this->load->model('Meeting_model','my_meeting');
			$data = $this->my_meeting->get_list(filter($post, $members));

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
	 * exit meeting
	 */
	public function exit_meeting()
	{
		//config
		$members = array('token', 'm_id');

		//delete
		try
		{
			//get POST
			$post = get_post();
			if ( empty($post) )
			{
				$post = array(
					'm_id'    => $this->input->post('m_id')
				);
			}
			$post['token'] = get_token();

			//check form
  			$this->load->library('form_validation');
  			$this->form_validation->set_data($post);
  			if ( ! $this->form_validation->run('exit_meeting'))
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
  			$this->load->model('Meeting_model','my_meeting');
  			$data = $this->my_meeting->exit_meeting(filter($post, $members));
		}
		catch (Exception $e)
		{
			output_data($e->getCode(), $e->getMessage(), array());
			return;
		}

		//return
  		output_data(1, '退出成功', array());
	}


	/*
	 * 添加会议标签
	 */
	public function add_label()
	{
		//config
		$members = array('token','m_id', 'l_id');

		try
		{
			//get POST
			$post = get_post();
			if ( empty($post) )
			{
				$post = array(
					'm_id'    => $this->input->post('m_id'),
					'l_id'    => $this->input->post('l_id')

				);
			}
			$post['token'] = get_token();

			//check form
  			$this->load->library('form_validation');
  			$this->form_validation->set_data($post);
  			if ( ! $this->form_validation->run('add_label'))
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

  			//过滤 && add
  			$this->load->model('Meeting_model','my_meeting');
  			$data = $this->my_meeting->add_label(filter($post, $members));
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
	 * 获取会议标签
	 */
	public function get_label()
	{
		//config
		$members = array('token','m_id');

		try
		{
			//get POST
			if ( ! $this->input->get('m_id'))
			{
				throw new Exception("必须指定会议ID");
			}
			$post['m_id']=$this->input->get('m_id');
			$post['token']=get_token();

  			//过滤 && get
  			$this->load->model('Meeting_model','my_meeting');
  			$data = $this->my_meeting->get_label(filter($post, $members));
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
	 * 推荐会议
	 */
	public function recommend()
	{
		//config
		$member = array('token');

		//get post
		try
		{
			$post['token'] = get_token();

			$this->load->model('Meeting_model', 'my_meeting');
			$data = $this->my_meeting->recommend(filter($post, $member));

		}
		catch (Exception $e)
		{
			output_data($e->getCode(), $e->getMessage(), array());
			return;
		}

		//return
		output_data(1,'您可能感兴趣', $data);
	}


	/*
	 * 发红包
	 */
	public function set_redpacket()
	{
		//config
		$members = array('token', 'r_name', 'r_money', 'r_num', 'r_type');

		try
		{
			//get POST
			$post = get_post();
			if ( empty($post) )
			{
				$post = array(
					'r_name'   => $this->input->post('r_name'),
					'r_money'  => $this->input->post('r_money'),
					'r_num'    => $this->input->post('r_num')
				);
			}
			$post['token'] = get_token();

			//check form
  			$this->load->library('form_validation');
  			$this->form_validation->set_data($post);
  			if ( ! $this->form_validation->run('set_red'))
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
  			$this->load->model('Meeting_model','my_meeting');
  			$data = $this->my_meeting->set_redpacket(filter($post, $members));
		}
		catch (Exception $e)
		{
			output_data($e->getCode(), $e->getMessage(), array());
			return;
		}

		//return
  		output_data(1, '发送成功', $data);
	}


	/*
	 * 抢红包
	 */
	public function snatch()
	{
		//config
		$members = array('token', 'r_id');

		try
		{
			//get POST
			if ( empty($post) )
			{
				$post = array(
					'r_id'   => $this->input->post('r_id')
				);
			}
			$post['token'] = get_token();

			//check form
  			$this->load->library('form_validation');
  			$this->form_validation->set_data($post);
  			if ( ! $this->form_validation->run('snatch_red'))
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
  			$this->load->model('Meeting_model','my_meeting');
  			$data = $this->my_meeting->snatch(filter($post, $members));
		}
		catch (Exception $e)
		{
			output_data($e->getCode(), $e->getMessage(), array());
			return;
		}

		//return
  		output_data(1, '抢到成功', $data);
	}


	/*
	 * 红包详情
	 */
	public function red_detail()
	{
		//config
		$members = array('token', 'r_id');

		try
		{
			//get POST
			if ( ! $this->input->get('r_id'))
			{
				throw new Exception('必须指定r_id');
			}
			$post['r_id'] = $this->input->get('r_id');
			$post['token'] = get_token();

  			//过滤 && delete
  			$this->load->model('Meeting_model','my_meeting');
  			$data = $this->my_meeting->red_detail(filter($post, $members));
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
	 * 删除会议标签
	 */
	public function delete_label()
	{
		//config
		$members = array('token','m_id', 'l_id');

		try
		{
			//get POST
			$post = get_post();
			if ( empty($post) )
			{
				$post = array(
					'm_id'    => $this->input->post('m_id'),
					'l_id'    => $this->input->post('l_id')

				);
			}
			$post['token'] = get_token();

			//check form
  			$this->load->library('form_validation');
  			$this->form_validation->set_data($post);
  			if ( ! $this->form_validation->run('delete_label'))
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
  			$this->load->model('Meeting_model','my_meeting');
  			$data = $this->my_meeting->delete_label(filter($post, $members));
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

?>
