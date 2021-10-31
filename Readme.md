# LaminasGen

Do you know Symfony's generating commands ? It's the same, for **Laminas Framework** :)

## install it
Just use `composer install thomasleconte/laminas-gen` !

## Use it
When library is installed, you can use `vendor/bin/laminas-gen-console <arguments>` to generate items. But if you want, you can create your own command for call this script in `composer.json` :

```json
    "scripts": {
        ...
        "laminas-gen": "vendor/bin/laminas-gen-console",
        ...
    }

```  
Then you will be able to use `composer laminas-gen <arguments>` instead of `vendor/bin/laminas-gen-console <arguments>`.

##  Debug it
1 - Run autoload command before work on project : `composer dump-autoload -o`  
2 - Good luck dude.