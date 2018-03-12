create table if not exists `user_t` (
    `u_id`             bigint auto_increment primary key not null,
    `u_pwd`            char(32) not null,
    `u_nickname`       varchar(12) not null,
    `u_position`       varchar(15),
    `u_company`        varchar(20),
    `u_email`          varchar(20),
    `u_tel`            varchar(20),
    `u_qq`             varchar(20),
    `u_weChat`         varchar(20),
    `u_address`        varchar(20),
    `u_showCard`       boolean DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


create table if not exists `meeting_t` (
    `m_id` bigint auto_increment primary key not null,
    `m_theme` varchar(30) not null,
    `m_introduction` varchar(50),
    `m_startdata` date not null,
    `m_starttime` time not null,
    `m_length` int not null,
    `m_place` varchar(30) not null,
    `m_sponsor` varchar(30) not null,
    `m_organizer` varchar(30) not null,
    `m_createrId` bigint not null,
    `m_open` boolean not null,
    `m_autoJoin` boolean not null,
    `m_3DSign` boolean not null,
    `m_luckyDog` boolean not null,
    `m_vote` boolean not null,
    `m_num` int not null,
    `m_3DSignM` varchar(30)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


create table if not exists `meeting_participants` (
    `u_id`             bigint not null,
    `m_id`             bigint not null,
    `signIn`           boolean default '0',
    `luckyDog`         boolean default '0',
    primary key(`u_id`,`m_id`),
    foreign key(`u_id`)references user_t(`u_id`) on delete cascade on update cascade,
    foreign key(`m_id`)references meeting_t(`m_id`) on delete cascade on update cascade
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


create table if not exists `meeting_manager` (
	`u_id`             bigint not null,
    `m_id`             bigint not null,
    primary key(`u_id`,`m_id`),
    foreign key(`u_id`)references user_t(`u_id`) on delete cascade on update cascade,
    foreign key(`m_id`)references meeting_t(`m_id`) on delete cascade on update cascade
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


create table if not exists `meeting_vote` (
	`u_id`             bigint not null,
    `m_id`             bigint not null,
    `voteNum`          int default 0,
    primary key(`u_id`,`m_id`),
    foreign key(`u_id`)references user_t(`u_id`) on delete cascade on update cascade,
    foreign key(`m_id`)references meeting_t(`m_id`) on delete cascade on update cascade
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


create table if not exists `sys_token` (
     `token_id`	      bigint auto_increment  primary key not null,
     `token`	      varchar(200) not null,
     `last_visit`	  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
     `u_id`           bigint(20)	not null
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
