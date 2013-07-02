/*
Initialization SQL file for initializing test database and user

Command:
mysql -u root -p < init.sql
*/

CREATE DATABASE IF NOT EXISTS yautest;

CREATE USER 'yau_user'@'localhost' IDENTIFIED BY 'yaupwd';

GRANT ALL ON yautest.* TO 'yau_user'@'localhost';