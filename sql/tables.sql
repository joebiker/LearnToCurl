-- main event table
CREATE TABLE `learntocurl_dates` (
  `ID` int(11) NOT NULL auto_increment,
  `EVENT_DATE` datetime NOT NULL,
  `EVENT_NAME` varchar(255) NOT NULL,
  `EVENT_TYPE` varchar(5) NOT NULL default 'L',
  `MAX_GUESTS` int(11) NOT NULL default 8,
  `PRICE_ADULT` float NOT NULL default '20',
  `PRICE_JUNIOR` float NOT NULL default '10',
  `PRICE_DISC` float NOT NULL default '40',
  `COMMENTS` varchar(1000) default NULL,
  PRIMARY KEY  (`ID`),
  KEY `EVENT_DATE` (`EVENT_DATE`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- users registered for event
CREATE TABLE `learntocurl` (
  `GID` int(11) NOT NULL auto_increment,
  `GROUP_NAME` varchar(100) NOT NULL,
  `EMAIL` varchar(100) NOT NULL,
  `GROUP_ADULTS` int(2) NOT NULL,
  `GROUP_JUNIORS` int(2) NOT NULL,
  `CONFIRMATION` char(5) NOT NULL COMMENT 'First of name, 3 randon chars, number in party',
  `CREATE_DATE` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `CREATE_BROWSER` varchar(255) default NULL,
  `CREATE_IP` varchar(255) default NULL,
  `EDIT_COUNT` int(11) default '0' COMMENT 'number of times edited',
  `EDIT_DATE` timestamp NULL default NULL COMMENT 'last time edited',
  `EDIT_IP` varchar(255) default NULL COMMENT 'ip of editor',
  `ADMIN_EDIT_COUNT` int(11) default NULL COMMENT 'number of times admin changed record',
  `ADMIN_EDIT_DATE` int(11) default NULL COMMENT 'last time admin changed record',
  `OPENHOUSE_ID` int(11) default NULL COMMENT 'FK to openhouse_dates',
  `LOWEST_EXPERIENCE` varchar(255) default NULL COMMENT 'some text about their experience',
  `ATTENDED` int(11) default NULL,
  `WAIVER` int(2) default NULL,
  `PAID_DOLLARS` float default '0',
  `PAID_TYPE` varchar(100) default NULL,
  `PAID_DATE` timestamp NULL default NULL,
  `PAYPAL_TX_ID` varchar(100) default NULL,
  `LEARN_REFER` varchar(255) default NULL COMMENT 'refer from /learn/ page hit',
  `REG_REFER` varchar(255) default NULL COMMENT 'Refer from /learn/openhouse/',
  `USER_REFER` varchar(255) default NULL COMMENT 'user input how they learned about ECC',
  PRIMARY KEY  (`GID`),
  KEY `GROUP_NAME` (`GROUP_NAME`),
  KEY `OPENHOUSE_ID` (`OPENHOUSE_ID`),
  KEY `CONFIRMATION` (`CONFIRMATION`),
  CONSTRAINT `learntocurl_ibfk_1` FOREIGN KEY (`OPENHOUSE_ID`) REFERENCES `learntocurl_dates` (`ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

