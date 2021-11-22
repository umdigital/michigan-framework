Michigan Framework
===================
Wordpress base theme built on the Foundation Responsive Framework.

## Configuration
* Copy the *child-theme-template* directory to wp-content/themes and rename to your desired theme name slug.
* Edit the config.json and style.css files to your desire.  Make sure to update the comments in the style.css file.

Default theme configurations are set in a config.json file.  A child-theme can override the parent themes config.json file options.  A child theme does not need to contain every config.json option but only the ones it wants to change (ideal).

The following is configured within the config.json file
- Menus (can be disabled in child theme by setting value to `""`)
- Debug settings (for development, debug.enabled should be set to false before production)
- Thumbnail sizes
- Widget locations/sizes as well as some zone sizes

### PHP Config Override Function
When building out a theme and you want to create a custom page type that disables widget locations such as a homepage, you can use the following function to do so (not this cannot be used to enable widget locations as they will not get populated into the admin widget page).

```php
MichiganFramework::setConfig( 'zone', 'widget', 'option', value );
```

example:
```php
MichiganFramework::setConfig( 'content', 'widget_first', 'enabled', 0 );
MichiganFramework::setConfig( 'footer', 'widget_prefix1', 'enabled', 0 );
```

### Built-in Menu Locations
Header Menu: *This menu is positioned between section-header and section-content zones*


### Widgets
A widget location will not render if no widgets have been assigned to the location.


### Menus
Menus can be disabled by setting the value to an empty string `header-menu = ''`


### Admin Menu
When Debugging is enabled an Admin menu is created called *Michigan Framework* where debug options can be turned on/off live (page reload will reset to default debug options).


### Templates
#### /page-templates/
This is to place user selectable page templates.

#### /templates/
This is where the default page, posts, and homepage templates live.  Also for custom wordpress post types you can create posts-{POST_TYPE} or page-{POST_TYPE} files to override the default page/post templates.

### CSS & JS
- Place CSS within /styles/*.css to have them autoloaded into the theme
- Place JavaScript within /scripts/*.js to have them autoloaded into the theme
- NOTE: The files will be loaded in alphabetical order

## Blocks/Shortcodes
Custom blocks will be autoloaded in /blocks/\*/block.php automatically
### Accordion
An accessible & print friendly accordion that uses minimal JavaScript.

#### Shortcode example
```
[accordion title="Title of Accordion" class="addtional-css-classes-to-add"]
Your accordion content here
[/accordion]
```

## Functions
### Remote Image Resize
Cache image stored on disk in wp-content/uploads/mfw-image-cache/[MD5].ext
```
// returns the url of image
MichiganFramework::remoteImageThumb(
    'IMAGEURL',
    'WPIMAGESIZE', // e.g. (full, thumbnail, medium) (DEFAULT: full)
    'CROP',        // true or false (DEFAULT: null)
    'PATH',        // e.g. 'bios', 'projects' (DEFAULT: null)
    'EXPIRES'      // time in seconds (DEFAULT: 1 week)
);
```
