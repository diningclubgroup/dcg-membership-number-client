# What is this?

Client library to interact with Membership Number service.

# Usage

To add this library to an existing application, 

* Add the following repository to the app's composer.json,
```javascript
"repositories": [
    {
        "type": "vcs",
        "url": "https://git@bitbucket.org/tastecard/dcg-lib-membership-number-client.git"
    }
]    
```   
            
* Add the following to the _require_ section, 
```javascript
"dcg/dcg-lib-membership-number-client": "dev-master"
```    

* Add this to the scripts section: 
```json
"scripts": {
    "post-update-cmd": [
        "Dcg\\Client\\MembershipNumber\\Config\\FileCreator::createConfigFile"        
    ]
}
```

* Run composer install