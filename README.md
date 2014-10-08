UserfriendlySocialUserBundle
============================

Symfony 2 bundle for user management and authentication using OAuth and traditional password-based login.

The fantastic FOSUserBundle.

The convenient HWIOAuthBundle.

Bam! Smashed together, stir-fried for a bit, then left to simmer on a Symfony 2
project. Quick, painless, no bother at all.

If you want this bundle to be a bit more flexible, I'm accepting Pull Requests
as of now...

The UserfriendlySocialUserBundle combines the FOSUserBundle with the HWIOAuthBundle.
It provides 3rd party authentication via OAuth1 and OAuth2, offers a user entity
with the standard fields, and optionally ties into the group feature of the FOSUserBundle.

**Note:**

> This bundle is *not* developed in sync with Symfony's repository.
The current version seems to work reasonably well with Symfony 2.4, though.

Documentation
-------------

[Read the Documentation for master](https://github.com/userfriendly/SocialUserBundle/blob/master/Resources/doc/index.md)

All installation and configuration instructions are located
[here](https://github.com/userfriendly/SocialUserBundle/blob/master/Resources/doc/installation_and_configuration.md).

License
-------

This bundle is released under the MIT license.

Reporting an issue or a feature request
---------------------------------------

Issues and feature requests are tracked in the [Github issue tracker](https://github.com/userfriendly/SocialUserBundle/issues).

When reporting a bug, it may be a good idea to reproduce it in a basic project
built using the [Symfony Standard Edition](https://github.com/symfony/symfony-standard)
to allow developers of the bundle to reproduce the issue by simply cloning it
and following some steps.
