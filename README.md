# DbM Framework

DbM Framework PHP MVC + DbM CMS, Version 3 – PHP >= 8.0  
All copyrights reserved by Design by Malina (DbM)  
Website: [www.dbm.org.pl](http://www.dbm.org.pl)  

## About the framework

DbM Framework is one of the fastest PHP solutions based on the MVC pattern, combining lightness, flexibility and efficiency with modern expansion possibilities. It allows you to easily add functions without interfering with the core, and the well-thought-out architecture ensures stability and security. It is an ideal choice for developers who value full control over the code and freedom in creating advanced web applications.  

DbM CMS is a ready-made framework-based solution for those who want to quickly launch a website or application without having to code. It supports both simple websites and complex database-based projects. If you don't have time to create your own modules, you can use ready-made tools for managing content, SEO and site structure. An effective solution that speeds up project development without sacrificing the flexibility of the framework.  

## Requirements

- [PHP](http://php.net) (>= 8.0)
- [MySQL](https://www.mysql.com) (for applications using the database)
- [Apache](https://httpd.apache.org)
- [Docker](https://www.docker.com/) (recommended)
- or alternatively [XAMPP](https://www.apachefriends.org/)

## Structure

- `application/` – framework core: classes, interfaces, libraries
- `data/` – data and files (write permissions required)
- `public/` – public files (domain root)
- `src/` – application logic: controllers, services, models, services
- `templates/` – view templates
- `tests/` – unit tests
- `translations/` – translation files
- `var/` – cache and logs (created automatically, write permissions required)
- `vendor/` – libraries installed by Composer

## Manual installation

1. Point the domain to the `public/` directory. In the `public/.htaccess` file, set the appropriate `RewriteBase`.
2. If you are using localhost, copy the `.htaccess` file from the `_Documents` directory to the root directory and adjust the `RewriteBase`.
3. Configure the `.env.example` file, then rename it to `.env`.

In the basic configuration, complete the **General settings** section:

```env
APP_URL="http://localhost/"
APP_NAME="Application Name"
APP_EMAIL="email@domain.com"
```

Then configure: Cache settings, Database settings, Mailer settings.

Manual installation makes the framework independent from other tools, equipped with its own autoloading. Executing the command `composer install` will automate the framework, create the Composer autoloading and install selected packages, e.g. for sending emails and development packages. After executing the command, the framework will work with Composer.

#### Additional information

When running the application in a production environment (on a remote server), **you should point the domain to the `/public/`** directory, because that is the root directory.

Additionally, depending on the server configuration, **you may need to disable the `open_basedir`** restriction in PHP settings. This security measure, known as "page separation", can block access to some directories and files outside the domain root directory, which will prevent the application from opening in the domain.

### Installing via Composer

If you prefer to install via Composer or your project requires additional packages:

```bash
git clone https://github.com/designbymalina/dbmframework.git
cd dbmframework
composer install
```

Installing via Composer will create the autoloading and download all dependencies.

## Routing

Adding a route in the `application/routes.php` file:

```shell
$router->addRoute('address', [NameController::class, 'methodName'], 'alias');
```

## Template engine

The framework uses the built-in template engine by default. You can replace it with e.g. Twig.

## Libraries

Used packages:

* [jQuery](https://jquery.com) - JavaScript library.
* [Bootstrap](https://getbootstrap.com) - Popular HTML, CSS, and JS library.
* [PHPMailer](https://github.com/PHPMailer/PHPMailer) - Library for sending emails.

**IMPORTANT!** When using DbM Framework add to the page (e.g. in the footer): "Created with <a href="https://dbm.org.pl/" title="DbM">DbM Framework</a>". The link should remain intact. Thank you for supporting the project's development!
