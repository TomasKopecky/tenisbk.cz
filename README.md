# ATS Blansko - Local Tennis Competition IS and CMS

Explore my project live at ([tenisbk.cz](https://tenisbk.cz))

![Project Image](https://github.com/ronadlisko/ronadlisko/blob/main/media/repos_images/tenisbk.cz/banner.png) 

---

## Introduction 💡
A PHP web application utilizing the Nette framework and PostgreSQL for comprehensive management of local tennis competitions. Originally developed as my bachelor’s thesis in 2019, this project has been continuously improved and is currently running on PHP 7.3 and Nette 2.4.

Admin access is granted upon request.

## Technologies Used 📖

<img src="https://github.com/ronadlisko/ronadlisko/blob/main/media/icons/php/php-original.svg" width="75"><img src="https://github.com/ronadlisko/ronadlisko/blob/main/media/icons/nette/nette-resized.png" width="80"><img src="https://github.com/ronadlisko/ronadlisko/blob/main/media/icons/postgresql/postgresql-original.svg" width="60"><img src="https://github.com/ronadlisko/ronadlisko/blob/main/media/icons/javascript/javascript-original.svg" width="57">

I've built the application using:
- PHP: Server-side scripting language.
- Nette: PHP framework for streamlined development.
- PostgreSQL: Robust database management system.
- JavaScript & jQuery: For interactive and dynamic user interfaces.

Frontend UI tools:
- AdminLTE: Dashboard and control panel template.
- DataTables: A jQuery plugin for table enhancement.

The web application is deployed on the ([tenisbk.cz](https://tenisbk.cz)) URL. The local development and testing is made on a XAMPP Apache server and Docker PostgreSQL container.

## Features 🌟
**Current Features Include:**
- Comprehensive competition agenda management including players, clubs, registrations, matches, and more.
- Detailed sections for club tables, player rankings, match callendar, and more.
- A CMS for article management.

**Future Development Plans:**
- Upgrade to Nette 3 and PHP 8.0.
- Enhance the CMS to support custom image uploads for articles.

## Screenshots and Demo 📸

You can find the application running on the tenisbk.cz URL where you can see the basic content. To see the admin CMS system for maintaning the players, competitions, matches, etc - watch the following video:

⏳ Coming soon

The admin access is available only upon request.

## Setup Instructions 🛠️

1. Clone this repository.
2. Use Composer to install dependencies:
   - Install Composer if you haven't already.
   - Run `composer update` from the project root.
3. Configure DB connection in `app/config.neon` or `app/config.local.neon`.
4. Implement DB authentication in `app/authenticator/MyAuthenticatorExample.php`.
5. Serve the app from `<server>/tenisbk.cz`, ensuring the `.htaccess` file in the `www` folder is configured correctly.

The application can be run on an Apache web server, including Docker, XAMPP, or WAMP setups, etc.

## Developer Notes 🔑

Sensitive data and original configuration files are not publicly available but can be requested. The following items are provided to aid your setup:

- **Configuration Files:** Example configuration files (`app/config/config_example.neon` and `app/config/config.local_example.neon`) are available. Remember to rename these files appropriately in `app/bootstrap.php`.
- **Database Access:** Due to personal data protection, the original PostgreSQL database is available only upon request.
- **Authentication Methods:** An example file (`app/authenticator/MyAuthenticatorExample.php`) outlines the database user login methods. This is also available upon request, with customization required for use in your environment.

## Project Origins 🏛️

This project was originally developed as my bachelor’s thesis in 2019 when styding on VŠPJ - you can download the original thesis ([here](https://is.vspj.cz/bp/get-bp/student/54954/thema/7531))
