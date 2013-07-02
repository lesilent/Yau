/*
Initialization SQL file for initializing test database and user

Command:
mysql -u root -p < cleanup.sql
*/

DROP USER 'yau_user'@'localhost';

DROP DATABASE IF EXISTS yautest;
