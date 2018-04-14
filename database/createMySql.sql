create database a27;
create table user_t (
    u_id              bigint auto_increment primary key not null,     /*用户ID*/
    u_imgpath         varchar(200),                                   /*用户头像*/
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


create table meeting_t  (
    m_id              bigint auto_increment primary key not null,    /*会议ID*/
    m_imgpath         varchar(200),                                  /*会议头像*/
    group_id          varchar(50),                                   /*群ID*/
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


create table meeting_participants  (
    u_id              bigint not null,                               /*用户ID*/
    m_id              bigint not null,                               /*会议ID*/
    is_admin          boolean default '0',                           /*是否管理人员*/
    signIn            boolean not null,                              /*是否签到*/
    luckyDog          boolean default '0',                           /*是否为幸运观众*/
    primary key(u_id,m_id),
    foreign key(u_id)references user_t(u_id) on delete cascade on update cascade,
    foreign key(m_id)references meeting_t(m_id) on delete cascade on update cascade
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


create table meeting_notice  (
    n_id              bigint auto_increment primary key not null,    /*公告ID*/
    m_id              bigint not null,                               /*会议ID*/
    u_id              bigint not null,                               /*发布ID*/
    n_title           text,                                          /*公告标题*/
    n_text            text,                                          /*公告内容*/
    n_time            TIMESTAMP Default CURRENT_TIMESTAMP            /*公告发布时间*/
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


create table meeting_vote  (
    v_id              bigint auto_increment primary key,             /*投票ID*/
    u_id              bigint not null,                               /*创建用户ID*/
    m_id              bigint not null,                               /*会议ID*/
    v_title           varchar(20),                                   /*投票主题*/
    v_summary         varchar(200),                                  /*投票说明*/
    v_type            boolean default '0',                           /*投票类型*/
    v_starttime       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,           /*投票开始时间*/
    v_endtime         TIMESTAMP                                      /*投票结束时间*/
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


create table vote_option(
    v_id              bigint,                                        /*投票ID*/
    m_id              bigint not null,                               /*会议ID*/
    v_option          varchar(20),                                   /*投票选项*/
    v_num             int default 0,                                 /*票数*/
    primary key(m_id, v_id, v_option),
    foreign key(v_id)references meeting_vote(v_id) on delete cascade on update cascade,
    foreign key(m_id)references meeting_t(m_id) on delete cascade on update cascade
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


create table vote_user(
    v_id              bigint,                                        /*投票ID*/
    v_option          varchar(20),                                   /*投票选项*/
    u_id              bigint,                                        /*创建用户ID*/
    v_flag            boolean default '0',                           /*是否投过票*/
    primary key(v_id, u_id,v_option),
    foreign key(v_id)references meeting_vote(v_id) on delete cascade on update cascade,
    foreign key(u_id)references user_t(u_id) on delete cascade on update cascade
)ENGINE=InnoDB DEFAULT CHARSET=utf8;


create table sys_label(
    l_id              bigint auto_increment not null primary key,    /*标签ID*/
    l_name            varchar(20)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;


create table meeting_label(
    ml_id             bigint auto_increment not null primary key,    /*ID*/
    m_id              bigint not null,                               /*会议ID*/
    l_id              bigint not null,                               /*标签ID*/
    foreign key(m_id)references meeting_t(m_id) on delete cascade on update cascade,
    foreign key(l_id)references sys_label(l_id) on delete cascade on update cascade
)ENGINE=InnoDB DEFAULT CHARSET=utf8;


create table sys_token  (
    token_id          bigint auto_increment  primary key not null,  /*token编号*/
    token             varchar(200) not null,                        /*token*/
    last_visit        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,          /*时间*/
    u_id              bigint(20)    not null                        /*用户id*/
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


create table red_packet(
    r_id              bigint auto_increment not null primary key,    /*红包ID*/
    u_id              bigint not null,                               /*发送用户ID*/
    r_money           bigint not null,                               /*红包金额*/
    r_num             bigint not null,                               /*红包数量*/
    r_name            varchar(50)                                   /*红包名称*/
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


create table user_snatch(
    us_id             bigint auto_increment not null primary key,    /*ID*/
    u_id              bigint not null,                               /*用户ID*/
    r_id              bigint not null,                               /*红包ID*/
    us_money          double not null,                               /*抢到红包金额*/
    r_banlance        double not null,                               /*余额*/
    foreign key(r_id)references red_packet(r_id) on delete cascade on update cascade
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


create table if not exists `user_power`(
    `user_power_id`    bigint auto_increment primary key not null,
    `sys_login`        boolean default '0',
    `opeate_data`      boolean default '0',
    `add_admin`        boolean default '0',
    `u_id`             bigint not null,
    foreign key(`u_id`)references user_t(`u_id`)on delete cascade on update cascade
)engine=InnoDB DEFAULT CHARSET=utf8;