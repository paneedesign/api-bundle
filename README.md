Pane e Design - Api Bundle
==============================

Api management for Symfony3 projects.

Installation
============

Step 1: Download the Bundle
---------------------------

Pane&Design repository is private so, add to `composer.json` this `vcs`

```json
    "repositories" : [
        ...
        {
            "type" : "vcs",
            "url" : "git@bitbucket.org:paneedesign/api-bundle.git"
        }
    ],
    ...
    "require": {
        ...
        "paneedesign/api-bundle": "^2.0"   
    }
```

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require "paneedesign/api-bundle"
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Step 2: Enable the Bundle
-------------------------

Then, enable the bundle by adding it to the list of registered bundles
in the `app/AppKernel.php` file of your project:

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...
            new \FOS\OAuthServerBundle\FOSOAuthServerBundle(),
            new \FOS\RestBundle\FOSRestBundle(),
            new \JMS\SerializerBundle\JMSSerializerBundle(),
            new \PaneeDesign\ApiBundle\PedApiBundle(),
        );

        // ...
        
        if (in_array($this->getEnvironment(), ['dev', 'test'], true)) {
            // ...
            $bundles[] = new \Nelmio\ApiDocBundle\NelmioApiDocBundle();
        }
    }

    // ...
}
```

Step 3: Configurations
----------------------

Add parameters

```
// app/config/parameters.yml.dist
parameters:
    ...
    api_server_host:              'https://api.paneedesign.com'
    api_type:                     oauth2
    api_access_token_expire_at:   ~
    api_refresh_token_expire_at:  ~
```

Add configuration

```yml
// app/config/config.yml
imports:
    - { resource: "@PedApiBundle/Resources/config/config.yml" }
...
```

```yml
// app/config/config_dev.yml
imports:
    - { resource: "@PedApiBundle/Resources/config/custom/nelmio_api_doc.yml" }
...
```

```yml
// app/config/routing.yml
ped_api:
    resource: '@PedApiBundle/Resources/config/routing.yml'
    prefix:   /
...
```

Implement API

```yml
// app/config/custom/api.yml
public_v1:
    type:         rest
    prefix:       /v1
    resource:     "@PedApiBundle/Controller/Api/ApiPublicController.php"
    name_prefix:  api_1_
    ...
```