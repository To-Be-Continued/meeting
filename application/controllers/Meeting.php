<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Meeting extends CI_Controller {

    
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
			if ( ! $this->form_validation->run('set_sign'))
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
			$this->my_meeting->register(filter($post, $members));

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
     * 生成签到图片
     */
    public function D_sign()
    {

    }


    
}

?>
