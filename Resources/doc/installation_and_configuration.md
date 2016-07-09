Installation and configuration
==============================

## Prerequisites

The master version of the bundle requires Symfony 3.1 or higher.
For Symfony 2.4 or higher, use the sf2.4 branch.

## Installation

Installation is a reasonably quick process:

1. Download UserfriendlySocialUserBundle and its dependencies using composer
2. Enable the bundles
3. Create your User class
4. Configure your application's security.yml
5. Configure the UserfriendlySocialUserBundle
6. Configure the routing for the bundles
7. Update your database schema

### Step 1: Download UserfriendlySocialUserBundle and its dependencies using composer

Add UserfriendlySocialUserBundle in your composer.json:

``` json
{
    "repositories": [
        { "type": "vcs", "url": "https://github.com/userfriendly/SocialUserBundle" }
    ],
    "require": {
        "userfriendly/social-user-bundle": "dev-master"
    }
}
```

**Note:**

> The `repositories` entry will disappear once I feel this
bundle is mature enough to be published on Packagist.

Tell composer to download the bundle by running the command:

``` bash
$ php composer.phar update userfriendly/social-user-bundle
```

Composer will install the bundle along with its dependencies (if those are not
already installed) to your project's `vendor` directory.

### Step 2: Enable the bundle

Enable the bundle(s) in the kernel:

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        // Enable the bundle itself
        new Userfriendly\Bundle\SocialUserBundle\UserfriendlySocialUserBundle(),
        // Enable its dependencies if you aren't already using them
        new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
        new FOS\UserBundle\FOSUserBundle(),
        new HWI\OAuthBundle\HWIOAuthBundle(),
    );
}
```

### Step 3: Create your User class

The goal of this bundle is to persist some `User` class to a database. Your first
job, then, is to create the `User` class for your application. This class can look
and act however you want: add any properties or methods you find useful. This is
_your_ `User` class.

The bundle provides a base class for each DB driver which is already mapped for
most fields to make it easier to create your entity.

Here is how to do it if you are using Doctrine ORM:

1. Extend the `Userfriendly\Bundle\SocialUserBundle\Entity\User` class.
2. Map the `id` field. It must be `protected` as it is inherited from the parent class.

We recommend that you give your table name a prefix of `user__` (double underscore)
as that is what the two other tables created by this bundle will use. That way they
will be grouped together in your database administration tool.

Your class might look as simple as this:

``` php
<?php
// src/Acme/UserBundle/Entity/User.php

namespace Acme\UserBundle\Entity;

use Userfriendly\Bundle\SocialUserBundle\Entity\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="user__user")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
}
```

A `Group` class is optional. Many people find they don't need groups functionality.
If you do, do the same thing as for the `User` class above, except now you extend
your class from the `FOS\UserBundle\Entity\Group` base class.

See
[FOSUserBundle documentation](https://github.com/FriendsOfSymfony/FOSUserBundle/blob/master/Resources/doc/groups.md)
for more information. Ignore the bits about its configuration.


### Step 4: Configure your application's security.yml

In order for Symfony's security component to use the UserfriendlySocialUserBundle,
you must tell it to do so in the `security.yml` file. The `security.yml` file is
where the basic configuration for the security of your application is contained.

Below is the minimal configuration necessary to use the UserfriendlySocialUserBundle
in your application:

``` yaml
# app/config/security.yml

security:
    providers:
        ## We're using the FOS User bundle's provider for the traditional form login.
        fos_userbundle:
            id: fos_user.user_provider.username_email

    encoders:
        ## Enable an encoder for the traditional form login
        FOS\UserBundle\Model\UserInterface: sha512

    firewalls:
        dev:
            pattern:  ^/(_(profiler|wdt)|css|images|js)/
            security: false
        login:
            pattern:  ^/login$
            security: false
        main:
            ## Configure your main firewall to use both the form login and OAuth.
            ## In this example we're enabling two commonly used OAuth providers.
            pattern: ^/
            form_login:
                provider: fos_userbundle
                #csrf_provider: form.csrf_provider # this needs to be moved
                login_path: /login
                check_path: /login_check
                default_target_path: /
            oauth:
                resource_owners:
                    facebook:      /login/check-facebook
                    twitter:       /login/check-twitter
                login_path:        /login
                failure_path:      /login
                default_target_path: /
                ## Tell your firewall which provider will manage users for the OAuth login:
                oauth_user_provider:
                    service: uf.security.oauth_user_provider
            logout:
                path: /logout
                target: /
            anonymous:    true

    role_hierarchy:
        ROLE_ADMIN:       ROLE_USER
        ROLE_SUPER_ADMIN: ROLE_ADMIN

    access_control:
        - { path: ^/login$, role: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/register, role: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/resetting, role: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/admin/, role: ROLE_ADMIN }
```


### Step 5: Configure the UserfriendlySocialUserBundle

Now that you have modified your application's `security.yml` to work
with the UserfriendlySocialUserBundle, the next step is to configure it. The
UserfriendlySocialUserBundle sets sensible defaults for its dependencies,
so you will only need to add a few things in your own config.yml - the following
example configures the two commonly used OAuth providers added in the step above:

``` yaml
# app/config/config.yml

userfriendly_social_user:
    db_driver: orm
    firewall_name: main
    user_class: Acme\UserBundle\Entity\User
#    group_class: Acme\UserBundle\Entity\Group
    resource_owners:
        facebook:
            type:                facebook
            client_id:           "%facebook_client_id%"
            client_secret:       "%facebook_client_secret%"
            scope:               "email"
        twitter:
            type:                twitter
            client_id:           "%twitter_client_id%"
            client_secret:       "%twitter_client_secret%"

```

Client ID and secret are set by using parameters here. The values for those
would live in your `app/config/parameters.yml` file (and not be tracked by
your version control software).

Feel free to use the `scope` key to specify what data you want a provider to give
you about the authenticating user.

Please note that some providers may not give you certain data about their users,
for example Twitter will not hand out a user's email address.

In case you created a Group class in Step 3, add a value for the `group_class`
key in the `userfriendly_social_user` configuration.

**Note:**

> You can override any of the settings for the wrapped bundles by adding your
own configuration under their specific configuration keys, e.g. `fos_user` and
`hwi_oauth`, but we recommend you only do that in cases where you have specific
requirements not met by the defaults set by this bundle.


### Step 6: Configure the routing

Now that you have activated and configured the bundle, all that is left to do is
import the routing directives.

By importing the routing files you will have ready made pages for things such as
logging in, editing a user's profile, etc.

You will also need to define routes for the redirect
URLs of the OAuth providers you have configured above.

In YAML:

``` yaml
# app/config/routing.yml

userfriendly_social_user_routes:
    resource: "@UserfriendlySocialUserBundle/Resources/config/routing.yml"
    prefix:   /

facebook_login:
    pattern: /login/check-facebook

twitter_login:
    pattern: /login/check-twitter
```

**Note:**

> Alternatively, don't import the bundle's `routing.yml`
file, and configure all of the routes yourself. Your choice.


### Step 7: Update your database schema

Now that the bundle is configured, the last thing you need to do is update your
database schema.

For Doctrine ORM run the following command.

``` bash
$ php bin/console doctrine:schema:update --force
```

### Next Steps

Now that you have completed the basic installation and configuration of the
UserfriendlySocialUserBundle, you are ready to learn about more advanced
features and usages of the bundle.

The following documents are available:

* [Connecting user accounts](connect.md)
