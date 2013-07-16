EcircleBundle
=============

EcircleBundle is part of the framework BigFoot created by C2IS.


Installation
------------

Add 'bigfoot/ecircle-bundle' into your composer.json file in the 'require' section:

``` json
// composer.json
"require": {
    ...
    ...
    "bigfoot/ecircle-bundle": "dev-master",
}
```

Update your project:

``` shell
php composer.phar update
```

Enter your credentials in the config file:

``` shell
# app/config.yml
...
...
parameters:
    bigfoot_ecircle:
        client:
            wsdl_url: 'http://webservices.ecircle-ag.com/soap/ecm.wsdl'
            request:
                account_1:
                    realm: 'http://your-ecircle-url.com'
                    user: 'User'
                    passwd: 'Password'

```

Create a class file into the directory Options with the name of the E-circle method followed by 'Options':

```php
// src/Bigfoot/Bundle/EcircleBundle/Options/SubscribeMemberByEmailOptions.php

namespace Bigfoot\Bundle\EcircleBundle\Options;

class subscribeMemberByEmailOptions
{
    public $email;
    public $groupId;
    public $session;
    public $sendMessage = false;


}
```
The parameters must be the same as the Ecircle method.


Create a new method in the service 'bigfoot_ecircle.client':

```php
// src/Bigfoot/Bundle/EcircleBundle/Services/BigfootEcircleClient.php
public function subscribeMemberByEmail($email,$groupId)
{

    if (!$this->sessionId) {
        throw new Exception('Client no connected');
    }

    $subscribeMemberByEmailOptions = $this->options('SubscribeMemberByEmail');

    $subscribeMemberByEmailOptions->email   = $email;
    $subscribeMemberByEmailOptions->session = $this->sessionId;
    $subscribeMemberByEmailOptions->groupId = $groupId;

    $result = $this->client->subscribeMemberByEmail($subscribeMemberByEmailOptions);

    return $result;
}
```

Usage
-----

Into an action method:

```php
// Controller/DefaultController.php
$client = $this->get('bigfoot_ecircle.client')->connect('account_1');
$retour = $client->subscribeMemberByEmail('example@email.com','99');
$client->disconnect();
```
