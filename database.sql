CREATE TABLE `toggl_project` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`mantis_project_id` INT(11) NOT NULL DEFAULT '0',
	`toggl_project_id` INT(11) NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`),
	UNIQUE INDEX `toggl_project_id` (`toggl_project_id`)
)
COLLATE='latin1_swedish_ci'
ENGINE=MyISAM
AUTO_INCREMENT=12
;

CREATE TABLE `toggl_user` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`toggl_id` INT(11) NOT NULL DEFAULT '0',
	`mantis_id` INT(11) NOT NULL DEFAULT '0',
	`toggl_name` VARCHAR(100) NOT NULL DEFAULT '0',
	`password` VARCHAR(100) NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`),
	UNIQUE INDEX `toggl_id` (`toggl_id`)
)
COLLATE='latin1_swedish_ci'
ENGINE=MyISAM
;

CREATE DEFINER=`expanda`@`%` PROCEDURE `toggl_project_assign`(IN `togglid` INT, IN `mantisid` INT)
	LANGUAGE SQL
	NOT DETERMINISTIC
	CONTAINS SQL
	SQL SECURITY DEFINER
	COMMENT ''
BEGIN
/*  DECLARE id int; */
  IF NOT EXISTS (SELECT * FROM toggl_project WHERE mantis_project_id = mantisid AND toggl_project_id = togglid) THEN
     insert into toggl_project (toggl_project_id,mantis_project_id) values (togglid, mantisid);
/*    SET id = 1;*/
    
  END IF;
  
END