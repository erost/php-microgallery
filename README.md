# php-microgallery

Self-contained PHP Photo Album

***
## Demo 

A demo of the application is available at http://demo.erost.net

## Usage

### Requirements

* PHP 5.2 or later

### Upload Pictures

You can manually upload pictures to &lt;gallery_home&gt;/gallery/&lt;album_name&gt;, and the pictures will show up in the right album
Otherwise, you have the option of uploading the pictures from the administration panel

### Configuration

Modify index.php

#### Administration Panel credentials

```php
define('USERNAME', 'admin');
define('PASSWORD', '1234');
```

#### Gallery title (top left corner)

```php
define('TITLE', 'Photo Album by example.com');
```

#### Navigation (links on the top right corner)

```php
function getNavigationMenu() {
	return array(
		'Home' => 'http://example.com', //valid URL
		'Gallery' => 'http://gallery.example.com'
	);
}
```

### Project Status

The software provided here is not maintained anymore, nor any update is planned for the future.
I decided to make it available on my repository as a reference

### Possible Improvements

* Update the UI to 2013 standards (load images dynamically, drag and drop file upload, ...)
* Display Exif data
* Create an option in the Administration panel to pre-generate all the thumbnails, per size
