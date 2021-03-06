# LaminasGen

Do you know Symfony's generating commands ? It's the same, for **Laminas Framework** :)

## install it
Just use `composer require thomasleconte/laminas-gen` !

## Config it
You need to provide this script in your `composer.json` file for make this lib able to be used.

```json
    "scripts": {
        ...
        "laminas-gen": [
        "LaminasGen\\Handler::handle"
        ],
        ...
    }

```  

## Use it
### Module generation
`composer laminas-gen module <yourModuleName>` (By default, this will generate an associated controller for be able to use your module fast as possible. But you can disable it using an optional argument : `without-extra`. So you can use command like that `composer laminas-gen module <yourModuleName> without-extra`)

### Controller generation
`composer laminas-gen controller <yourControllerName> <existingModuleName>` (This will generate all associated CRUD views for again, use your controller fast as possible. And you cant disable it ... For the moment 🥱)

### Entity generation
`composer laminas-gen entity <yourEntityName> <yourModuleName>`. You will have to type each properties of your entity. `yourEntityName.php` and `yourEntityNameTable.php` files will be generated, with default getters and setters.

### Undo
`composer laminas-gen undo` (This will undo all last creations or modifications done by LaminasGen)  
`composer laminas-gen undo-all` (This will undo all creations or modification done since you use it)
## Details
When you will have installed this package, you will be able to edit templates in `src/Generators/templates/` folder. But keep in mind that you will have limited possibilites, due to number of variables understood by my script. So you can edit the script for make my script understanding **your** variables :)

##  Debug it
1 - Run autoload command before work on project : `composer dump-autoload -o`
2 - Good luck dude.

### Reminder
If you want to use package during debug, add these lines on your test project :
```json
    "repositories": [
        {
            "type": "path",
            "url": "absolute/or/relative/path/to/laminas-gen/folder"
        }
    ]
```
Then, just install it with : `composer require thomasleconte/laminas-gen @dev`.
Take care about `@dev`, composer uses this to pickup the source code and symlink it to your new package.
