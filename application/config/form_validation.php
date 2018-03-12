<?php

$config = array(
	'register' => array(
		array(
			'field' => 'u_tel',
			'label' => '手机号',
			'rules' => 'required|max_length[11]'
		),
		array(
			'field' => 'u_pwdF',
			'label' => '密码',
			'rules' => 'required|max_length[32]'
		),
		array(
			'field' => 'u_pwdS',
			'label' => '密码',
			'rules' => 'required|max_length[32]'
		)
	),
	'login' => array(
		array(
			'field' => 'u_tel',
			'label' => '手机号',
			'rules' => 'required|max_length[11]'
		),
		array(
			'field' => 'u_pwd',
			'label' => '密码',
			'rules' => 'required|max_length[32]'
		)
	),
	'info' => array(
		array(
			'field' => 'u_nickname',
			'label' => '用户名',
			'rules' => 'required|max_length[12]'
		),
		array(
			'field' => 'u_position',
			'label' => '职位',
			'rules' => 'max_length[20]'
		),
		array(
			'field' => 'u_company',
			'label' => '公司',
			'rules' => 'max_length[20]'
		),
		array(
			'field' => 'u_email',
			'label' => '邮箱',
			'rules' => 'max_length[20]'
		),
		array(
			'field' => 'u_qq',
			'label' => 'QQ',
			'rules' => 'max_length[20]'
		),
		array(
			'field' => 'u_weChat',
			'label' => '微信',
			'rules' => 'max_length[20]'
		),
		array(
			'field' => 'u_address',
			'label' => '地址',
			'rules' => 'max_length[20]'
		),
		array(
			'field' => 'u_showCard',
			'label' => '显示卡片',
			'rules' => 'numeric'
		)
	),
	'meeting_info' => array(
		array(
			'field' => 'm_theme',
			'label' => '主题',
			'rules' => 'required|max_length[30]'
		),
		array(
			'field' => 'm_introduction',
			'label' => '简介',
			'rules' => 'max_length[50]'
		),
		array(
			'field' => 'm_startdate',
			'label'	=> '会议日期',
			'rules' => 'required'
		),
		array(
			'field' => 'm_starttime',
			'label'	=> '会议时间',
			'rules' => 'required'
		),
		array(
			'field' => 'm_length',
			'label' => '会议时长',
			'rules' => 'required'
		),
		array(
			'field' => 'm_place',
			'label' => '地点',
			'rules' => 'required|max_length[30]'
		),
		array(
			'field' => 'm_sponsor',
			'label' => '主办方',
			'rules' => 'required|max_length[30]'
		),
		array(
			'field' => 'm_organizer',
			'label' => '承办方',
			'rules' => 'required|max_length[30]'
		),
		array(
			'field' => 'm_open',
			'label' => '公开项',
			'rules' => 'required'
		),
		array(
			'field' => 'm_autoJoin',
			'label' => '自动加入项',
			'rules' => 'required'
		),
		array(
			'field' => 'm_3DSign',
			'label' => '3D签名项',
			'rules' => 'required'
		),
		array(
			'field' => 'm_luckyDog',
			'label' => '抽奖项',
			'rules' => 'required'
		),
		array(
			'field' => 'm_vote',
			'label' => '投票项',
			'rules' => 'required'
		)
	),
	'release_meeting' => array(
		array(
			'field' => 'm_id',
			'label' => '会议ID',
			'rules' => 'required'
		)
	),
	'delete_meeting' => array(
		array(
			'field' => 'm_id',
			'label' => '会议ID',
			'rules' => 'required'
		)
	),
	'join_meeting' => array(
		array(
			'field' => 'm_id',
			'label' => '会议ID',
			'rules' => 'required'
		)
	),
	'set_vote' => array(
		array(
			'field' => 'u_id',
			'label' => '用户ID',
			'rules' => 'required'
		),
		array(
			'field' => 'm_id',
			'label' => '会议ID',
			'rules' => 'required'
		)
	),
	'set_sign' => array(
		array(
			'field' => 'u_id',
			'label' => '用户ID',
			'rules' => 'required'
		),
		array(
			'field' => 'm_id',
			'label' => '会议ID',
			'rules' => 'required'
		)	
	),
	'details_of_meeting' => array(
		array(
			'field' => 'm_id',
			'label' => '会议ID',
			'rules' => 'required'
		)
	),
	'add_manager' => array(
		array(
			'field' => 'u_id',
			'label' => '用户ID',
			'rules' => 'required'
		),
		array(
			'field' => 'm_id',
			'label' => '会议ID',
			'rules' => 'required'
		)
	),
	'meeting_lucky_dog' => array(
		array(
			'field' => 'm_id',
			'label' => '会议ID',
			'rules' => 'required'
		)
	)

);




?>
