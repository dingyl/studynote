CREATE DATABASE IF NOT EXISTS news default charset utf8 COLLATE utf8_general_ci;
use news;
create table news
(
  id integer auto_increment not null primary key,
  title varchar(128),
  content text,
  created_at integer
);