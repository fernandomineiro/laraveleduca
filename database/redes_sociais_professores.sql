alter table professor add column facebook_link varchar(60) after mini_curriculum;
alter table professor add column insta_link varchar(60) after facebook_link;
alter table professor add column twitter_link varchar(60) after insta_link;
alter table professor add column linkedin_link varchar(60) after twitter_link;
alter table professor add column youteber_link varchar(60) after linkedin_link;
