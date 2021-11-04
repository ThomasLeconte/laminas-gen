# LaminasGen

Do you know Symfony's generating commands ? It's the same, for **Laminas Framework** :)

## install it
Just use `composer install thomasleconte/laminas-gen` !

## Config it
When library is installed, you can use `vendor/bin/laminas-gen-console <arguments>` to generate items. But if you want, you can create your own command for call this script in `composer.json` :

```json
    "scripts": {
        ...
        "laminas-gen": "vendor/bin/laminas-gen-console",
        ...
    }

```  
Then you will be able to use `composer laminas-gen <arguments>` instead of `vendor/bin/laminas-gen-console <arguments>`.

## Use it
### Module generation
`composer laminas-gen module <yourModuleName>` (By default, this will generate an associated controller for be able to use your module fast as possible. But you can disable it using an optional argument : `without-extra`. So you can use command like that `composer laminas-gen module <yourModuleName> without-extra`)

### Controller generation
`composer laminas-gen controller <yourControllerName> <existingModuleName>` (This will generate all associated CRUD views for again, use your controller fast as possible. And you cant disable it ... For the moment 🥱)

### Undo
`composer laminas-gen undo` (This will undo all last creations or modifications done by LaminasGen)  
`composer laminas-gen undo-all` (This will undo all creations or modification done since you use it)
## Details
When you will have installed this package, you will be able to edit templates in `src/Generators/templates/` folder. But keep in mind that you will have limited possibilites, due to number of variables understood by my script. So you can edit the script for make my script understanding **your** variables :)

##  Debug it
1 - Run autoload command before work on project : `composer dump-autoload -o`  
2 - Good luck dude.
