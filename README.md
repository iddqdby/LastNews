# LastNews
## <a name="Description"></a>Description

Parser of last news from different sources.

Currently supported sources:
* tut.by
  * m *(default)*
  * finance
  * auto
  * sport
  * 42
  * lady
* nn.by
  * top *(default)*
  * economy
  * accidents
  * style
  * technologies
  * culture
  * sport
  * auto
  * love_and_sex
* elementy.ru

## How to set up

### If you want to use it from CLI as a standalone utility:

```sh
git clone https://github.com/iddqdby/LastNews
cd LastNews
composer install
```

After that you can use utility like this:

```sh
php run/cli.php tut.by
```

See ["Usage"](#Usage).

### If you want to use the library in your project:

Add to your `composer.json` following information.

```javascript
{
    "name": "yournamespace/yourappname",
    
    // ...
    
    "repositories": [
        // ...
        {
            "type": "vcs",
            "url": "https://github.com/iddqdby/LastNews"
        }
    ],
    "require": {
        // ...
        "iddqdby/last-news": "dev-master"
    }
}
```

Then run `composer update` as usual.

## <a name="Usage"></a>Usage

### From CLI:

```sh
php run/cli.php <source> <amount> <section>
```

For supported sources and sections see ["Description"](#Description).

`amount` and `section` are optional.

`source` defines the source to parse news from.

`amount` limits the number of **last** news to parse. Default is `0` (no limit).

`section` defines the section of the given source. Default is `''`.

### From your app:

```php
// Create the instance of reader
$reader = new LastNewsReader();

// Parse news and get result as an instance of IDDQDBY\LastNews\Parsers\Result\Excerpt class
$result = $reader->read( $source, $section, $amount );

// Parse news and process result with callback
$reader->read( $source, $section, $amount, function ( Excerpt $excerpt ) {
    // do something here
} );
```

See `run/cli.php` for basic example.

Also you can implement your own parsers. To do that, create class that implements `\IDDQDBY\LastNews\Parsers\IParser` interface or inherits from one of the abstract classes in `\IDDQDBY\LastNews\Parsers\` namespace.

Then you must register your class **or** instance of your class in `ParserProvider`:

```php
// Register the class;
// no-arg constructor will be used to instantiate the class
$reader->getParserProvider()->setParserClass( $your_custom_source_name, $your_custom_class_name );

// OR

// Register the instance of parser
$reader->getParserProvider()->setParser( $your_custom_source_name, $your_custom_parser );
```

## Requirements

PHP 5.4 or later.

## License

This program is licensed under the GNU General Public License Version 3. See [LICENSE](LICENSE).
