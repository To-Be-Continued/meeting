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
			'field' => 'm_id',
			'label' => '会议ID',
			'rules' => 'required'
		),
		array(
			'field' => 'v_title',
			'label' => '投票主题',
			'rules' => 'required|max_length[20]|min_length[2]'
		),
		array(
			'field' => 'v_summary',
			'label' => '投票说明',
			'rules' => 'required|max_length[200]|min_length[1]'
		),
		array(
			'field' => 'v_type',
			'label' => '投票类型',
			'rules' => 'required'
		),
		array(
			'field' => 'v_starttime',
			'label' => '发布时间',
			'rules' => 'required'
		),
		array(
			'field' => 'v_endtime',
			'label' => '结束时间',
			'rules' => 'required'
		)
	),
	'vote_detail' => array(
		array(
			'field' => 'm_id',
			'label' => '会议ID',
			'rules' => 'required'
		),
		array(
			'field' => 'v_id',
			'label' => '投票ID',
			'rules' => 'required'
		)
	),
	'set_notice' => array(
		array(
			'field' => 'm_id',
			'label' => '会议ID',
			'rules' => 'required'
		),
		array(
			'field' => 'n_title',
			'label' => '公告标题|max_length[200]|min_length[1]',
			'rules' => 'required'
		),
		array(
			'field' => 'n_text',
			'label' => '公告内容',
			'rules' => 'required|max_length[200]|min_length[1]'
		)
	),
	'get_notice' => array(
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
			'field' => 'op_tel',
			'label' => '操作人员',
			'rules' => 'required'
		),
		array(
			'field' => 'set_tel',
			'label' => '被设置人员',
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
	),
	'meeting_actor' => array(
		array(
			'field' => 'm_id',
			'label' => '会议ID',
			'rules' => 'required'
		)
	),
	'getlist_theme' => array(
		array(
			'field' => 'm_theme',
			'label' => '主题',
			'rules' => 'required|max_length[30]'
		)
	),
	'get_list' => array(
		array(
			'field' => 'm_id',
			'label' => '会议ID',
			'rules' => 'required'
		)
	),
	'invite_meeting' => array(
		array(
			'field' => 'invite_tel',
			'label' => '邀请人员',
			'rules' => 'required'
		),
		array(
			'field' => 'invited_tel',
			'label' => '被邀请人员',
			'rules' => 'required'
		),
		array(
			'field' => 'm_id',
			'label' => '会议ID',
			'rules' => 'required'
		)
	),
	'delete_member' => array(
		array(
			'field' => 'op_tel',
			'label' => '操作人员',
			'rules' => 'required'
		),
		array(
			'field' => 'del_tel',
			'label' => '被删除人员',
			'rules' => 'required'
		),
		array(
			'field' => 'm_id',
			'label' => '会议ID',
			'rules' => 'required'
		)
	),
	'get_img' => array(
		array(
			'field' => 'op_tel',
			'label' => '获取对象',
			'rules' => 'required'
		)
	),
	'get_meet_img' => array(
		array(
			'field' => 'm_id',
			'label' => '会议对象',
			'rules' => 'required'
		)
	),
	'exit_meeting' => array(
		array(
			'field' => 'm_id',
			'label' => '会议ID',
			'rules' => 'required'
		)
	),
	'label_register' => array(
		array(
			'field' => 'l_name',
			'label' => '标签名',
			'rules' => 'required'
		)
	),
	'label_update' => array(
		array(
			'field' => 'l_id',
			'label' => '标签ID',
			'rules' => 'required'
		),
		array(
			'field' => 'l_name',
			'label' => '标签名',
			'rules' => 'required'
		)
	),
	'label_delete' => array(
		array(
			'field' => 'l_id',
			'label' => '标签ID',
			'rules' => 'required'
		)
	),
	'add_label' => array(
		array(
			'field' => 'l_id',
			'label' => '标签ID',
			'rules' => 'required'
		),
		array(
			'field' => 'm_id',
			'label' => '会议ID',
			'rules' => 'required'
		)
	),
	'set_red' => array(
		array(
			'field' => 'r_name',
			'label' => '红包名称',
			'rules' => 'required'
		),
		array(
			'field' => 'r_num',
			'label' => '红包个数',
			'rules' => 'required|greater_than[0]'
		),
		array(
			'field' => 'r_money',
			'label' => '红包金额',
			'rules' => 'required|greater_than[0]'
		)
	),
	'snatch_red' => array(
		array(
			'field' => 'r_id',
			'label' => '红包ID',
			'rules' => 'required'
		)
	),

);




?>
