shaftoe
=======

About
-----
Shaftoe is a very simple LAMP script to encrypt messages using PGP public keys. It uses Jason Hinkle's excellent
[php-gpg](https://github.com/jasonhinkle/php-gpg) libray, which was the only decent openpgp implementation I could find (seriously) that didn't rely on compiled binaries. At the moment, the app only has 2 methods:
    
    Saves a key to the database
    POST /key
    parameters: email, key
    returns: {msg: string, sucess: boolean}
    
    Encrypt a message using the key associated with email
    POST /encrypt
    parameters: email, message
    returns: {msg: string, success: boolean}

The idea behind this script is to allow applications to send secure communications to a user if they
would like them. 

For example, when signing up for a web application the user provides a public pgp key. All future email communications from the app are encrypted using this public key.

Example
-------
A working example (which is also included in the source) can be found here:

[http://toxiccode.com/shaftoe/example/](http://toxiccode.com/shaftoe/example/)

Installation
------------
Check out the project somewhere not in your apache document root.

Download and install [Composer](http://getcomposer.org/) into the poject directory. Composer is a dependenct management tool for php.

`$ curl -sS https://getcomposer.org/installer | php`

Use composer to install dependencies:

`php composer.phar install`

Set up your database. For now shaftoe uses a mysql database with one table. But any datastore should do. There is a file, schema.sql, which will setup the schema for you. 

Set the correct parameters in app/config/config.php. use config.php.sample as a template.

Make a directory somewhere in your apache root for shaftoe:

`mkdir /var/www/shaftoe && cd /var/www/shaftoe`

Create a symbolic link to "web/index.php"

`ln -s /home/ubuntu/Documents/shaftoe/web/index.php .`

Now you have to edit your apache config file. This can either be in the form of a .htaccess file or a global apache config file such as /etc/apache2/sites-available/default.

Add the FallbackResource directive pointing to index.php:

    <Directory /var/www/shaftoe>
        Options Indexes FollowSymLinks MultiViews
        AllowOverride None
        Order allow,deny
        allow from all
        FallbackResource index.php
    </Directory>

Restart apache. Shaftoe should be available at yourserver.com/shaftoe

If you'd like to install the example as well, just create a symbolic link to the example directory somewhere in your apache document root. By default it expects to placed in the same directory as the link to index.php

F.A.Q
-----

1   Why PHP/MYSQL?
    Because php-pgp works, and LAMP is so common. Chances are you already have it on your server.

