create table user_t (
     u_id              bigint auto_increment primary key not null,     /*用户ID*/
     u_imgpath         varchar(50),                                    /*用户头像*/
     u_pwd             char(32) not null,                              /*密码，将输入得密码用MD5加密后存储*/
     u_nickname        varchar(12) not null,                           /*昵称*/
     u_position        varchar(15),                                    /*职位*/
     u_company         varchar(20),                                    /*公司*/
     u_email           varchar(20),                                    /*邮箱*/
     u_tel             varchar(20),                                    /*手机联系方式*/
     u_qq              varchar(20),                                    /*QQ*/
     u_weChat          varchar(20),                                    /*微信*/
     u_address         varchar(20),                                    /*地址*/
     u_showCard        boolean Default '1'                             /*显示卡片*/
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


create table meeting_t  (
     m_id              bigint auto_increment primary key not null,    /*会议ID*/
     m_imgpath         varchar(50),                                   /*会议头像*/
     m_theme           varchar(30) not null,                          /*会议主题*/
     m_introduction    varchar(50),                                   /*会议简洁*/
     m_startdate       date not null,                                 /*会议日期*/
     m_starttime       time not null,                                 /*会议时间*/
     m_length          int not null,                                  /*会议时长*/
     m_place           varchar(30) not null,                          /*会议地点*/
     m_sponsor         varchar(30) not null,                          /*会议主办方*/
     m_organizer       varchar(30) not null,                          /*会议承办方*/
     m_createrId       bigint not null,                               /*会议建立者ID*/
     m_open            boolean not null,                              /*会议是否公开*/
     m_autoJoin        boolean not null,                              /*会议是否允许自动加入*/
     m_3DSign          boolean not null,                              /*会议是否开启3D签名*/
     m_luckyDog        boolean not null,                              /*会议是否抽取幸运观众*/
     m_vote            boolean not null,                              /*会议是否开启投票*/
     m_num             int not null,                                   /*会议参与人数*/
     m_3DSignM         varchar(30)                                     /*存储签名图片得链接*/
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


create table meeting_participants  (
     u_id              bigint not null,                               /*用户ID*/
     m_id              bigint not null,                               /*会议ID*/
     signIn            boolean not null,                              /*是否签到*/
     luckyDog          boolean default '0',                           /*是否为幸运观众*/
    primary key(u_id,m_id),
    foreign key(u_id)references user_t(u_id) on delete cascade on update cascade,
    foreign key(m_id)references meeting_t(m_id) on delete cascade on update cascade
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


create table meeting_manager  (
	 u_id              bigint not null,                               /*用户ID*/
     m_id              bigint not null,                               /*会议ID*/
    primary key(u_id,m_id),
    foreign key(u_id)references user_t(u_id) on delete cascade on update cascade,
    foreign key(m_id)references meeting_t(m_id) on delete cascade on update cascade
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


create table meeting_vote  (
	 u_id              bigint not null,                               /*用户ID*/
     m_id              bigint not null,                               /*会议ID*/
     voteNum           int default 0,                                 /*票数*/
    primary key(u_id,m_id),
    foreign key(u_id)references user_t(u_id) on delete cascade on update cascade,
    foreign key(m_id)references meeting_t(m_id) on delete cascade on update cascade
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


create table sys_token  (
      token_id 	      bigint auto_increment  primary key not null,  /*token编号*/
      token 	      varchar(200) not null,                        /*token*/
      last_visit      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,			/*时间*/
      u_id            bigint(20)	not null						/*用户id*/
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
