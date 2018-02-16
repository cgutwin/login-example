-- auto-generated definition
CREATE TABLE users
(
  ID              INT AUTO_INCREMENT
  PRIMARY KEY,
  Name_First      VARCHAR(128) NOT NULL,
  Name_Last       VARCHAR(128) NOT NULL,
  Email           VARCHAR(256) NOT NULL,
  Password        VARCHAR(256) NOT NULL,
  UserType        INT          NOT NULL,
  ProfileImageRef VARCHAR(512) NOT NULL
);
