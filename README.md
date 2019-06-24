# What is this?

Client library to interact with Membership Number service.

# Usage

To add this library to an existing application: 

```bash
composer require dcg/dcg-membership-number-client
``` 

Add this to the scripts section of `composer.json`: 
```json
"scripts": {
    "post-update-cmd": [
        "Dcg\\Client\\MembershipNumber\\Config\\FileCreator::createConfigFile"        
    ]
}
```

Run `composer install`