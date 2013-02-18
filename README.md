# php-microgallery

Self-contained PHP Photo Album

***

## Usage

### Requirements

* PHP 5.2 or later

### Upload Pictures

You can manually upload pictures to <gallery_home>/<album_name>, and the pictures will show up in the right album
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