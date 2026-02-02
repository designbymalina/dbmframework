<?php

declare(strict_types=1);

// Installer translation (English en-EN)
return [
    'installer.lang' => 'en',
    'installer.engine' => 'DbM Framework',
    'installer.navbar.home' => 'Home (Create New Project)',
    'installer.navbar.extensions' => 'Extensions',
    'installer.navbar.download' => 'Download',
    'installer.header.title' => 'Welcome to DbM CMS!',
    'installer.header.subtitle' => 'DbM Framework / DbM CMS Platform installation assistant',
    'installer.content.title' => 'Installation Assistant',
    'installer.progressbar.installation' => 'Installation progress',
    'installer.progressbar.not_started' => 'Progress bar is not included!',
    'installer.button.next_step' => 'Next step',
    'installer.button.back' => 'Back',
    'installer.step.start.title' => 'Start installation',
    'installer.step.start.content' => '
        <p><strong>DbM CMS</strong> is a fast and modern content management system, designed with simplicity of use and installation in mind. A ready-made solution based on a framework for those who want to quickly launch a website or application without having to code. It supports both simple pages and complex database-driven projects. If you don&amp;t have time to create your own modules, you can use ready-made tools for managing content, SEO and site structure. There are also ready-made modules (plugins) available, such as CMS Lite, CMS Core, CMS Pro and others, which you can quickly install and customize to your needs. An effective solution that speeds up project development without losing the flexibility of the framework.</p>
        <p>The installation process consists of a few simple steps and takes about 5 minutes.</p>
        <p>Before you start using the application, please read the documentation at: <a href="https://dbm.org.pl/twarz/dbmframework" class="link-offset-2 link-offset-3-hover link-underline link-underline-opacity-0 link-underline-opacity-75-hover" target="_blank">DbM Framework</a>.</p>
        <ol>
            <li>Go to the &quot;<strong>Installation and Configuration</strong>&quot; section and follow the steps described there.</li>
            <li>Complete the configuration data in the <strong>.env</strong> file and verify the <strong>.htaccess</strong> files.</li>
            <li>After completing the configuration and following the next steps, the Platform will be ready for use.</li>
        </ol>
        <p>Need help? Check detailed instructions or contact the author.</p>
    ',
    'installer.step.requirements.title' => 'Checking requirements',
    'installer.step.requirements.content' => '
        <p>Before proceeding with the installation, the system will verify whether your server environment meets all the necessary requirements.</p>
        <p>The following elements will be checked:</p>
        <ul>
            <li>PHP version and required extensions</li>
            <li>File and directory permissions</li>
            <li>Server configuration compatibility</li>
            <li>Availability of required PHP functions</li>
        </ul>
        <p>If any issues are detected, you will be informed with details on how to fix them before continuing.</p>
        <p>This step ensures that the application will run correctly and securely after installation.</p>
    ',
    'installer.step.cmslite.title' => 'Installing CMS Lite',
    'installer.step.cmslite.content' => '
        <p>In this step, the <strong>CMS Lite</strong> module will be installed and configured.</p>
        <p>CMS Lite provides a lightweight and flexible content management layer that allows you to:</p>
        <ul>
            <li>Create and manage pages</li>
            <li>Control the homepage and site structure</li>
            <li>Extend functionality later with additional CMS modules</li>
        </ul>
        <p>The module will automatically integrate with the routing system and become the main content handler of your website.</p>
        <p>You can later upgrade or extend CMS Lite without reinstalling the system.</p>
    ',
    'installer.step.database.title' => 'Database connection',
    'installer.step.database.content' => '
        <p>This step verifies the connection to your database and prepares the system for further installation steps.</p>
        <p>The installer will:</p>
        <ul>
            <li>Check database credentials and connection</li>
            <li>Validate database server compatibility</li>
            <li>Prepare the environment for database migrations</li>
        </ul>
        <p>No data will be modified at this stage. This step only ensures that the database is ready to be used by the system.</p>
        <p>Actual database structure will be created in the next steps.</p>
    ',
    'installer.step.authentication.title' => 'Create authentication system',
    'installer.step.authentication.content' => '
        <p>In this step, the authentication system will be prepared.</p>
        <p>The system will set up the basic structure required for:</p>
        <ul>
            <li>User accounts</li>
            <li>Login and logout mechanisms</li>
            <li>Session handling and security</li>
        </ul>
        <p>This functionality is required for accessing the administration panel and managing protected areas of the application.</p>
        <p>You will be able to extend authentication features later if needed.</p>
    ',
    'installer.step.admin.title' => 'Create an admin panel',
    'installer.step.admin.content' => '
        <p>This step installs and configures the administrative panel.</p>
        <p>The admin panel allows you to:</p>
        <ul>
            <li>Manage website content</li>
            <li>Configure system settings</li>
            <li>Control users and permissions</li>
        </ul>
        <p>After installation, you will be able to log in using the administrator account and manage your website through a user-friendly interface.</p>
    ',
    'installer.step.finish.title' => 'Congratulations!',
    'installer.step.finish.content' => '
        <p>Your <strong>DbM CMS Lite</strong> installation has been successfully completed.</p>
        <p>Your system is now ready to use. You can start building your website, managing content, and expanding functionality with additional modules.</p>
        <p>For security reasons, please ensure the installer is no longer accessible.</p>
        <p>Enjoy working with DbM CMS!</p>
    ',
    'installer.requirements.msg.min_requirements' => 'Essential requirements for CMS Lite',
    'installer.requirements.msg.php_ok' => 'PHP version ≥ %s meets the requirements',
    'installer.requirements.msg.php_fail' => 'PHP version must be ≥ %s',
    'installer.requirements.msg.directories_ok' => 'Required directories are writable',
    'installer.requirements.msg.directories_fail' => 'The following directories are not writable: `{files}`. Change permissions.',
    'installer.requirements.msg.language_ok' => 'Language configuration is correct',
    'installer.requirements.msg.language_fail' => '%s',
    'installer.alert.content_not_load' => 'Could not load content.',
    'installer.alert.no_payload' => 'Could not install module.',
    'installer.alert.no_step' => 'No step selected.',
    'installer.alert.already_installed' => 'The module has already been installed.',
    'installer.alert.invalid_package_structure' => 'Error unpacking package. Please check file %s and try again..<br />%s',
    'installer.alert.archive_is_missing' => 'The package `%s` is missing.<br>Download it from GitHub or from <a href="https://dbm.org.pl/" target="_blank">DbM Framework</a>',
    'installer.alert.installation_ready' => 'The package is ready for installation. To install, click the button.',
    'installer.alert.installation_process' => 'Package installation process...',
    'installer.alert.installation_error' => 'An error occurred during installation!',
    'installer.alert.installation_success' => 'The installation of package `%s` was successful.',
    'installer.alert.installation_success_cmslite' => 'The installation of package CMS Lite was successful. You can see <a href="./" target="_blank">Home page &raquo;</a>',
    'installer.alert.database_connection_failed' => 'Database connection failed. Check the configuration in the .env file.',
    'installer.alert.database_not_exists' => 'Database `%s` does not exist. Complete the database configuration in the .env file.',
    'installer.alert.database_name_missing' => 'Database name is missing. Complete the database configuration in the .env file.',
    'installer.alert.form_fill_fields' => 'Fill in the form fields.',
];
