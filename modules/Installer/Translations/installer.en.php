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
    'installer.button.home_page' => 'Go to the home page',
    'installer.button.add_modules' => 'Install additional modules',
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
    ', // not used
    'installer.step.cmslite.title' => 'Installing CMS Lite',
    'installer.step.cmslite.content' => '
        <p>In this step, the <strong>CMS Lite</strong> module will be installed and configured.</p>
        <p>CMS Lite provides a lightweight and flexible content management layer that allows you to:</p>
        <ul>
            <li>Create and manage pages</li>
            <li>Control the homepage and site structure</li>
            <li>Extending functionality with additional CMS modules</li>
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
        <p>In this step the authentication system will be prepared.</p>
        <p>The system will configure the basic structure required for:</p>
        <ul>
            <li>User accounts</li>
            <li>Login and logout mechanisms</li>
            <li>Session management and security</li>
        </ul>
        <p>The authentication system allows access to the administration panel and protected areas of the application.</p>
        <p>Authentication features can be easily extended later with additional modules.</p>
    ',
    'installer.step.admin.title' => 'Create administration panel',
    'installer.step.admin.content' => '
        <p>This step installs and configures the administration panel.</p>
        <p>The administration panel allows you to:</p>
        <ul>
            <li>Manage page content</li>
            <li>Control users and permissions</li>
            <li>Manage modules and plugins</li>
        </ul>
        <p>During installation the system will automatically create starter accounts:</p>
        <ul>
            <li><strong>Admin</strong> (login or email) - password: <strong>Admin123</strong></li>
            <li><strong>Lucy</strong> - password: <strong>Test123</strong></li>
            <li><strong>John</strong> - password: <strong>Test123</strong></li>
        </ul>
        <p>After installation you will be able to log in to the administrator account and manage your website using a user-friendly interface.</p>
        <p><strong>Security recommendation:</strong> change the default account passwords after the first login.</p>
    ',
    'installer.step.finish.title' => 'Congratulations!',
    'installer.step.finish.content' => '
        <p>The installation of <strong>DbM CMS Platform</strong> has been completed.</p>
        <p>The system is now ready to run with the homepage module. If the <strong>packages</strong> directory also contains packages for the <strong>Authentication</strong> and <strong>Administration Panel</strong> modules, the installer can automatically detect and add them to the system.</p>
        <p>The best experience with the system is achieved with the three core modules:</p>
        <ul>
            <li>Homepage</li>
            <li>Authentication</li>
            <li>Administration panel</li>
        </ul>
        <p>Together they create a complete DbM CMS configuration, allowing convenient management of content, users and additional modules directly from the panel.</p>
        <p><em>DbM CMS is designed as a modular platform – you can start with the basics and expand the system as your project grows.</em></p>
        <p>If you have the additional modules: `authentication.zip` and `admin.zip`, copy their archives to the <strong>packages</strong> directory, and then select <strong>add modules</strong> in the next step. Otherwise, you can proceed to the main page.</p>
        <p>If you have just installed additional modules, please continue to return to the main page.</p>
        <p>For security reasons, make sure the installer is no longer available.</p>
        <p>Thank you for using DbM CMS.</p>
    ',
    'installer.requirements.msg.core requirements' => 'Essential system requirements',
    'installer.requirements.msg.cms_requirements' => 'Essential requirements for CMS Lite',
    'installer.requirements.msg.admin_requirements' => 'Authentication and administration panel installation requirements',
    'installer.requirements.msg.php_ok' => 'PHP version ≥ %s meets the requirements',
    'installer.requirements.msg.php_fail' => 'PHP version must be ≥ %s',
    'installer.requirements.msg.directories_ok' => 'Required directories are writable',
    'installer.requirements.msg.directories_fail' => 'The following directories are not writable: `{files}`. Change permissions.',
    'installer.requirements.msg.extension_ok' => 'Extension `%s` is loaded',
    'installer.requirements.msg.extension_fail' => 'Missing extension `%s`',
    'installer.database.msg.host_missing' => 'The hostname is required. Please complete the database configuration in the .env file.',
    'installer.database.msg.name_missing' => 'The database name is required.',
    'installer.database.msg.user_missing' => 'The username is required.',
    'installer.database.msg.connection_failed' => 'The database connection failed. Please check the configuration in the .env file.',
    'installer.database.msg.not_exists' => 'The database does not exist. Please complete the database configuration in the .env file.',
    'installer.database.msg.table_exists' => 'Tables for module already exist in the database. The database must be cleared before installation.',
    'installer.database.msg.table_not_exists' => 'The database is missing module tables that should be installed in the authentication module.',
    'installer.alert.already_installed' => 'The module has already been installed.',
    'installer.alert.invalid_package_structure' => 'Error unpacking package. Please check file %s and try again..<br />%s',
    'installer.alert.archive_is_missing' => 'The package `%s` is missing.<br>Download it from GitHub or from <a href="https://dbm.org.pl/" target="_blank">DbM Framework</a>',
    'installer.alert.module_verification_failed' => 'The module verification failed. Please check the module, clear the cache and try again.',
    'installer.alert.installation_error' => 'An error occurred during installation!', // not used
    'installer.alert.installation_process' => 'Package installation process... prepare the archive or remove any remnants if you are reinstalling!',
    'installer.alert.installation_ready' => 'Installation has already been performed... clear your browser cache and cookies if you want to retry the installation process!',
    'installer.alert.installation_success' => 'Installation completed successfully. You can now add optional extensions or go to the homepage.',
    'installer.alert.installation_completed' => 'Installation complete.', // not used
];
