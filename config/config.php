<?php

// Security
defined('BASE_DIRECTORY') or die('No direct script access.');

// General settings, *ATTENTION! Configure files .htaccess in folder main and /public for RewriteBase
define('APP_PATH', 'http://localhost/'); // Path to application (Example: https://domain.com/dbmframework/, option: / or directory_name/ for local)
define('APP_ENV', "development"); // Application environments, by default "production" leave empty or change to "development"
define('APP_LANGUAGES', "PL|EN"); // Language default Polish: PL; add additional languages (PL|EN|DE|...) separated by a vertical line
define('APP_EMAIL', "email@dbm.org.pl"); // E-mail of the owner (adminstrator)
define('APP_NAME', "Design by Malina"); // Name of the owner (adminstrator)

// Database settings
define('DB_HOST', "localhost"); // Hostname for your database server
define('DB_USER', "root"); // Username for database account
define('DB_PASSWORD', ""); // Password for database account
define('DB_DATABASE', "dbm_cms"); // Name of database to connect

// Mailer settings
define('MAIL_SMTP', false); // SMTP ON/OFF; value true or false, if true enter the mail settings
define('MAIL_HOST', ""); // SMTP server (IsSMTP)
define('MAIL_USERNAME', ""); // SMTP account username
define('MAIL_PASSWORD', ""); // SMTP account password
