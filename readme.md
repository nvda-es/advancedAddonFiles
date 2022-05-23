# Advanced Addon Files

The server back-end application which Add-on Updater always wanted and never had.

## Introduction

This small PHP application is an evolution of the get.php script used to shorten and maintain download links on the [NVDA add-ons website](https://addons.nvda-project.org). It allows managing add-ons and download links from an easy, intuitive and accessible web interface. Also, when listing available add-ons, it provides much more metadata than the original script.

## Features

* Easily maintain, add or remove add-ons and download links from the web interface. For each add-on, you can specify its name, summary, description, author and URL. Download links are managed separately, and contain extra information: file (short string passed to get.php), version, channel, minimum NVDA version and last tested NVDA version.
* No delays: changes are applied as soon as they are submitted. No more "It will be available within 30-40 minutes".
* User management: the system can contain multiple user accounts with different privileges.
* Three user roles: author, reviewer and administrator. The only difference between a reviewer and an administrator is that the last one can manage user accounts. Authors are the less privileged users: they only can edit their own add-ons and download links.
* No complex server setup: this application only needs a web server with PHP and SQLite to work. Apache is recommended for security reasons. PHP SQLite extension is enabled by default on most scenarios. The SQLite extension must be enabled separately on Windows. No additional database servers are required!
* Security: many use cases and possible scenarios have been taken into account during application development. The main goals related to security are avoiding SQL injections and unwanted privileges for roles other than administrator, encrypting passwords on database and preventing the database file from being downloaded.
* The get.php script, when called with the addonslist parameter, returns all possible information you may need for Add-on Updater or even a full NVDA add-ons store. In one hand, this allows code simplification for the add-on. In the other hand, no Add-on Updater updates are required when new add-ons are registered.
* The activity log, accessible publicly to everyone and very similar to a Git history, allows you to see all operations performed by all users. Even if the user doesn't write a log message, predefined messages are displayed.
* Backwards compatible: if the original get.php script were replaced by this application, all website download links would work with no changes.
* When managing links for an add-on, you can see the total number of downloads since they were updated. Download counter is reset to 0 after updating a link.

## Getting started

1. Download or clone this repository to your computer.
2. Edit config.php and change the default values (optional, but recommended). You can specify an absolute path to the database file. Ideally, it should be writable by the web server or the PHP process, but inaccessible from outside. The .htaccess file is nevertheless designed to protect this file if it ends with .db extension and is inside the directory which contains the application. The session name is used to create a session only for this application. It should be changed. The base URL contains the full application URL, with an ending slash.
3. If using Apache as your web server, ensure that the AllowOverride directive is set to All for your virtual host. Otherwise, the .htaccess file won't work.
4. Upload the application to your web server.
5. Visit the application URL and follow the instructions.

## Bugs

We are quite sure this application contains a few bugs, despite the time we have spent performing all kind of possible tests. If you discover one, let us know by opening an issue. Pull requests are also appreciated, specially those regarding visual appearance.

## Database description

The database structure is designed as follows:

addons table:
* id: integer primary key
* author: text
* name: text
* summary: text
* description: text
* url: text

links table:
* id: integer (add-on identifier from add-ons table)
* file: text (key passed as parameter to be redirected to the download link)
* version: text
* channel: text (stable, dev, lts...)
* minimum: text (year.version.0)
* lasttested: text (year.version.0)
* downloads: integer
* link: text

permissions table:
* user integer
* addon integer

users table:
* id integer primary key
* username text
* fullname text
* email text
* password text
* role integer (0=author, 1=reviewer, 2=admin)

log table:
* id integer primary key
* date text
* user text
* message text
