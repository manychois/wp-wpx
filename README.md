# Wpx
Wpx provides you a set of utility functions to help you developing WordPress themes or plugins.

## Installation
`composer require manychois/wp-wpx`

Then in your plugin file or theme functions.php, write this to start using Wpx:
```php
require_once(__DIR__ . '/vendor/autoload.php');
$wpx = new \Manychois\Wpx\Utility(new \Manychois\Wpx\WpContext());
$wpx->activate();
```

## Features
+ Check [UtilityInterface](https://github.com/manychois/wp-wpx/blob/master/src/UtilityInterface.php) for available methods. Some highlights:
    + `minimizeHead()`  
      Remove certain WordPress default stuff in `<head>` tag, e.g. generator tag, emoji script.
    + `registerStyle()` / `registerScript()`  
      Outputting tag like `<link rel="stylesheet" href="..." integrity="..." crossorigin="anonymous" />` has become possible.
    + `getMenuItem()`  
      No more manipulating output from `wp_nav_menu()`. Wpx provides you hierarchy of menu data for extreme flexibility.
    + `getPostPaginationLinks()`  
      Again, no more manipulation on output from `paginate_links()`.
+ Bundle `\Manychois\Views\View` to help you build HTML template in parent-child structure. Reference: https://github.com/manychois/php-views
+ Provide `\Manychois\Wpx\TagBuilder` to simplify HTML tag construction.
+ Register useful stylesheets and JavaScripts for admin pages:
    + **wpx-jquery-ui**: CSS of Jquery UI theme Smoothness.
    + **wpx-codemirror**: Latest CodeMirror core script.

## To-do list
- Add helper styles and functions to simplify plugin admin screen development.

## License
This project is licensed under MIT License.

## Author
This library is created by Siu Pang Tommy Choi.