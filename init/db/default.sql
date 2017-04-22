use `formscan`;


-- delete
delete from `keyword`;
delete from `userapikey`;
delete from `usersession`;
delete from `users`;
delete from `cron`;
delete from `settings`;




-- settings
insert into `settings` (`name`, `description`, `value`, `dateAdded`) VALUES('AdministratorEmail', 'Administrator email address used for notification (eg. user registration)', 'admin@factscan.net', '2016-01-01');
insert into `settings` (`name`, `description`, `value`, `dateAdded`) VALUES('EmailServerHost', 'SMTP server name (for email sending)', 'smtp.gmail.com', '2016-01-01');
insert into `settings` (`name`, `description`, `value`, `dateAdded`) VALUES('EmailServerPort', 'SMTP server port', '465', '2016-01-01');
insert into `settings` (`name`, `description`, `value`, `dateAdded`) VALUES('EmailServerUsername', 'SMTP username', 'info@urlonline.org', '2016-01-01');
insert into `settings` (`name`, `description`, `value`, `dateAdded`) VALUES('EmailServerPassword', 'SMTP password', 'tNx6TbwhX6Gf', '2016-01-01');
insert into `settings` (`name`, `description`, `value`, `dateAdded`) VALUES('EmailServerSSL', 'Email server secure (SSL)', 'ssl', '2016-01-01');



-- cron
insert into `cron` (`name`, `description`) VALUES('CronAlive', 'Verifies whether automated processes are alive');
insert into `cron` (`name`, `description`) VALUES('CronDocument1', 'Performs document conversion (worker 1)');



-- users
insert into `users` (`id`, `username`, `password`, `type`, `status`, `dateAdded`) VALUES(1, 'admin', MD5('password'), 2, 1, '2016-01-01');
insert into `users` (`id`, `username`, `password`, `type`, `status`, `dateAdded`) VALUES(2, 'user', MD5('password'), 1, 1, '2016-01-01');



-- userapikey
insert into `userapikey` (`userID`, `name`, `key`, `status`, `dateAdded`) VALUES(1, 'Web Test Key', 'fa4bc681f4d69a665078a347dac6b0fa', 1, '2016-01-01');



-- keyword

-- personal information
insert into `keyword` (`name`, `type`, `status`, `dateAdded`) VALUES("Credit Card Holder", 1, 1, '2016-01-01');
insert into `keyword` (`name`, `type`, `status`, `dateAdded`) VALUES("Title", 1, 1, '2016-01-01');
insert into `keyword` (`name`, `type`, `status`, `dateAdded`) VALUES("Name", 1, 1, '2016-01-01');
insert into `keyword` (`name`, `type`, `status`, `dateAdded`) VALUES("Full Name", 1, 1, '2016-01-01');
insert into `keyword` (`name`, `type`, `status`, `dateAdded`) VALUES("First Name", 1, 1, '2016-01-01');
insert into `keyword` (`name`, `type`, `status`, `dateAdded`) VALUES("Last Name", 1, 1, '2016-01-01');
insert into `keyword` (`name`, `type`, `status`, `dateAdded`) VALUES("Surname (Family name)", 1, 1, '2016-01-01');
insert into `keyword` (`name`, `type`, `status`, `dateAdded`) VALUES("Surname", 1, 1, '2016-01-01');
insert into `keyword` (`name`, `type`, `status`, `dateAdded`) VALUES("Family name", 1, 1, '2016-01-01');
insert into `keyword` (`name`, `type`, `status`, `dateAdded`) VALUES("Other Names", 1, 1, '2016-01-01');
insert into `keyword` (`name`, `type`, `status`, `dateAdded`) VALUES("Preferred name", 1, 1, '2016-01-01');
insert into `keyword` (`name`, `type`, `status`, `dateAdded`) VALUES("Date of birth", 1, 1, '2016-01-01');
insert into `keyword` (`name`, `type`, `status`, `dateAdded`) VALUES("Gender", 1, 1, '2016-01-01');
insert into `keyword` (`name`, `type`, `allowedValues`, `status`, `dateAdded`) VALUES("Sex", 10, "Male|Female", 1, '2016-01-01');


-- address
insert into `keyword` (`name`, `type`, `status`, `dateAdded`) VALUES("Address", 1, 1, '2016-01-01');
insert into `keyword` (`name`, `type`, `status`, `dateAdded`) VALUES("Street 1", 1, 1, '2016-01-01');
insert into `keyword` (`name`, `type`, `status`, `dateAdded`) VALUES("Street 2", 1, 1, '2016-01-01');
insert into `keyword` (`name`, `type`, `status`, `dateAdded`) VALUES("Town", 1, 1, '2016-01-01');
insert into `keyword` (`name`, `type`, `status`, `dateAdded`) VALUES("City", 1, 1, '2016-01-01');
insert into `keyword` (`name`, `type`, `status`, `dateAdded`) VALUES("Town/City", 1, 1, '2016-01-01');
insert into `keyword` (`name`, `type`, `status`, `dateAdded`) VALUES("County", 1, 1, '2016-01-01');
insert into `keyword` (`name`, `type`, `status`, `dateAdded`) VALUES("State", 1, 1, '2016-01-01');
insert into `keyword` (`name`, `type`, `status`, `dateAdded`) VALUES("State/Region", 1, 1, '2016-01-01');
insert into `keyword` (`name`, `type`, `status`, `dateAdded`) VALUES("State/Province", 1, 1, '2016-01-01');
insert into `keyword` (`name`, `type`, `status`, `dateAdded`) VALUES("Postcode", 1, 1, '2016-01-01');
insert into `keyword` (`name`, `type`, `status`, `dateAdded`) VALUES("Postal Code", 1, 1, '2016-01-01');
insert into `keyword` (`name`, `type`, `status`, `dateAdded`) VALUES("Zip Code", 1, 1, '2016-01-01');
insert into `keyword` (`name`, `type`, `status`, `dateAdded`) VALUES("ZIP", 1, 1, '2016-01-01');
insert into `keyword` (`name`, `type`, `status`, `dateAdded`) VALUES("ZIP/Postal Code", 1, 1, '2016-01-01');
insert into `keyword` (`name`, `type`, `status`, `dateAdded`) VALUES("Country", 1, 1, '2016-01-01');


-- contact
insert into `keyword` (`name`, `type`, `status`, `dateAdded`) VALUES("Phone", 1, 1, '2016-01-01');
insert into `keyword` (`name`, `type`, `status`, `dateAdded`) VALUES("Mobile", 1, 1, '2016-01-01');
insert into `keyword` (`name`, `type`, `status`, `dateAdded`) VALUES("Telephone", 1, 1, '2016-01-01');
insert into `keyword` (`name`, `type`, `status`, `dateAdded`) VALUES("Home Telephone", 1, 1, '2016-01-01');
insert into `keyword` (`name`, `type`, `status`, `dateAdded`) VALUES("Home Phone", 1, 1, '2016-01-01');
insert into `keyword` (`name`, `type`, `status`, `dateAdded`) VALUES("Work Phone", 1, 1, '2016-01-01');
insert into `keyword` (`name`, `type`, `status`, `dateAdded`) VALUES("Cell Phone", 1, 1, '2016-01-01');
insert into `keyword` (`name`, `type`, `status`, `dateAdded`) VALUES("Daytime Telephone Number", 1, 1, '2016-01-01');
insert into `keyword` (`name`, `type`, `status`, `dateAdded`) VALUES("Daytime Phone Number", 1, 1, '2016-01-01');
insert into `keyword` (`name`, `type`, `status`, `dateAdded`) VALUES("Fax", 1, 1, '2016-01-01');
insert into `keyword` (`name`, `type`, `status`, `dateAdded`) VALUES("Email", 1, 1, '2016-01-01');
insert into `keyword` (`name`, `type`, `status`, `dateAdded`) VALUES("Email address", 1, 1, '2016-01-01');
insert into `keyword` (`name`, `type`, `status`, `dateAdded`) VALUES("Re-type Email", 1, 1, '2016-01-01');


-- registration
insert into `keyword` (`name`, `type`, `status`, `dateAdded`) VALUES("Password", 1, 1, '2016-01-01');
insert into `keyword` (`name`, `type`, `status`, `dateAdded`) VALUES("Re-type Password", 1, 1, '2016-01-01');
insert into `keyword` (`name`, `type`, `status`, `dateAdded`) VALUES("Organisation", 1, 1, '2016-01-01');
insert into `keyword` (`name`, `type`, `status`, `dateAdded`) VALUES("Company Name", 1, 1, '2016-01-01');
insert into `keyword` (`name`, `type`, `status`, `dateAdded`) VALUES("Position", 1, 1, '2016-01-01');
insert into `keyword` (`name`, `type`, `status`, `dateAdded`) VALUES("Role/Title", 1, 1, '2016-01-01');
insert into `keyword` (`name`, `type`, `status`, `dateAdded`) VALUES("Credit Card", 1, 1, '2016-01-01');
insert into `keyword` (`name`, `type`, `status`, `dateAdded`) VALUES("Credit Card (numbers only)", 1, 1, '2016-01-01');
insert into `keyword` (`name`, `type`, `status`, `dateAdded`) VALUES("Signature", 14, 1, '2016-01-01');
insert into `keyword` (`name`, `type`, `status`, `dateAdded`) VALUES("Applicant's signature", 14, 1, '2016-01-01');
insert into `keyword` (`name`, `type`, `status`, `dateAdded`) VALUES("Date", 7, 1, '2016-01-01');
insert into `keyword` (`name`, `type`, `status`, `dateAdded`) VALUES("Received by", 1, 1, '2016-01-01');
insert into `keyword` (`name`, `type`, `status`, `dateAdded`) VALUES("Notes", 2, 1, '2016-01-01');
insert into `keyword` (`name`, `type`, `status`, `dateAdded`) VALUES("Details", 2, 1, '2016-01-01');

