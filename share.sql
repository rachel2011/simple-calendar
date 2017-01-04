create table share(
    -> share_id MEDIUMINT UNSIGNED NOT NULL auto_increment,
    -> event_id MEDIUMINT UNSIGNED NOT NULL,
    -> share_userid INT(11) NOT NULL,
    -> primary key(share_id),
    -> foreign key(event_id) references event(event_id),
    -> foreign key(share_userid) references users(id)
    -> )engine = INNODB DEFAULT character SET = utf8 COLLATE = utf8_general_ci;