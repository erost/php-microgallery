<?php
error_reporting(E_ALL | E_STRICT);

// *** MODIFY HERE ***/
date_default_timezone_set('Europe/Zurich');

define('USERNAME', 'admin');
define('PASSWORD', '1234');
define('TITLE', 'Photo Album by hurrdurr.com');

function getNavigationMenu() {
	return array(
		'Hurr Durr' => 'http://hurrdurr.com/', //valid URL
		'Hurr Durr Projects' => 'http://hurrdurr.com/projects'
	);
}

/*
Author: Taborelli, Eros <eros.taborelli@hurrdurr.com>, Jermini, Sylvain <sylvain.jermini@hurrdurr.com>
Version: 1.0
Date: 2010-01-03
Description: A minimalistic Photo Album with upload, descriptions and picture resize features.
License:
 Copyright (c) 2010-2013 hurrdurr.com 

 Permission is hereby granted, free of charge, to any person
 obtaining a copy of this software and associated documentation
 files (the "Software"), to deal in the Software without
 restriction, including without limitation the rights to use,
 copy, modify, merge, publish, distribute, sublicense, and/or sell
 copies of the Software, and to permit persons to whom the
 Software is furnished to do so, subject to the following
 conditions:

 The above copyright notice and this permission notice shall be
 included in all copies or substantial portions of the Software.

 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 OTHER DEALINGS IN THE SOFTWARE.


Powered by:
- jQuery 1.3.2 (http://jquery.com/)
- jQuery Lightbox plugin (http://leandrovieira.com/projects/jquery/lightbox/)
- jQuery Multiple File Upload Plugin (http://www.fyneworks.com/jquery/multiple-file-upload/)
*/

// *** CONFIGURATION ***/
define('GALLERY_DIR', realpath(dirname($_SERVER['SCRIPT_FILENAME'])) . DIRECTORY_SEPARATOR . 'gallery');
define('RESIZE_DIR', realpath(dirname($_SERVER['SCRIPT_FILENAME'])) . DIRECTORY_SEPARATOR . 'resize');
define('DESCRIPTION_DIR', realpath(dirname($_SERVER['SCRIPT_FILENAME'])) . DIRECTORY_SEPARATOR . 'descriptions');
define('DESCRIPTION_FILE_ALBUMS', 'albums.txt');
define('DESCRIPTION_FILE_PICTURES', 'pictures.txt');
define('HTACCESS_FILE', realpath(dirname($_SERVER['SCRIPT_FILENAME'])) . DIRECTORY_SEPARATOR . '.htaccess');

define('IMAGES_PER_PAGE', 18);
define('IMAGES_INCLUDES', '/\.(jpg|jpeg|gif|png)$/i');

function getBaseURL() {
	$request_array = explode('/', $_SERVER['REQUEST_URI']);
	$request_array = array_slice($request_array, 1, sizeof($request_array) - 2);
	$base_url = '/';
	foreach($request_array as $sub) {
		$base_url = $base_url . $sub . '/';
	}
	return $base_url;
}
define('BASE_URL', getBaseURL());

if(!file_exists(HTACCESS_FILE)) {
	$htaccess = <<<HTACCESS
<IfModule mod_rewrite.c>
     RewriteEngine on
     RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization},L]
</IfModule>
HTACCESS;
	$htaccessfp = fopen(HTACCESS_FILE, 'w');
	fwrite($htaccessfp, $htaccess);
	fclose($htaccessfp);
}

// *** ROUTES ***
dispatcher(array(
	'normal' => array(
		//public routes
		'' => array(
			'function' => 'index'
		),
		'index' => array(
			'function' => 'index'
		),
		'album' => array(
			'function' => 'album',
			'parameters' => array(
				'GET' => array(
					'name' => array( 'required' => true ),
					'page' => array( 'required' => false, 'default' => 1)
				)
			)
		),
		'image' => array(
			'function' => 'image',
			'parameters' => array(
				'GET' => array(
					'album' => array( 'required' => true ),
					'name' => array( 'required' => true ),
					'size' => array( 'required' => false , 'default' => 800)
				)
			)
		),
		'thumb' => array(
			'function' => 'thumb',
			'parameters' => array(
				'GET' => array(
					'album' => array( 'required' => true ),
					'name' => array( 'required' => true ),
					'size' => array( 'required' => true)
				)
			)
		),
		'res' => array(
			'function' => 'resources',
			'parameters' => array(
				'GET' => array(
					'name' => array( 'required' => true )
				)
			)
		)
	),
	'auth' => array(
		//admin routes
		'admin' => array(
			'function' => 'admin_index'
		),
		'admin_create' => array(
			'function' => 'admin_pre_create'
		),
		'admin_upload' => array(
			'function' => 'admin_pre_upload'
		),
		'create' => array(
			'function' => 'admin_create',
			'parameters' => array(
				'POST' => array(
					'name' => array( 'required' => true )
				)
			)
		),
		'delete' => array(
			'function' => 'admin_delete',
			'parameters' => array(
				'POST' => array(
					'name' => array( 'required' => true )
				)
			)
		),
		'deletepictures' => array(
			'function' => 'admin_deletepictures',
			'parameters' => array(
				'POST' => array(
					'name' => array( 'required' => true ),
					'deleteimages' => array( 'required' => true )
				)
			)
		),
		'upload' => array(
			'function' => 'admin_upload',
			'parameters' => array(
				'POST' => array(
					'album' => array( 'required' => true )
				),
				'FILES' => array(
					'MyImages' => array( 'required' => true )
				)
			)
		),
		'albumdescription' => array(
			'function' => 'admin_albumdescription',
			'parameters' => array(
				'POST' => array(
					'name' => array( 'required' => true ),
					'comment' => array( 'required' => true )
				)
			)
		),
		'albumpreview' => array(
			'function' => 'admin_albumpreview',
			'parameters' => array(
				'POST' => array(
					'name' => array( 'required' => true ),
					'preview' => array( 'required' => true )
				)
			)
		),
		'picturedescription' => array(
			'function' => 'admin_picturedescription',
			'parameters' => array(
				'POST' => array(
					'album' => array( 'required' => true ),
					'name' => array( 'required' => true ),
					'comment' => array( 'required' => true )
				)
			)
		),
		'modify' => array(
			'function' => 'admin_albummodify',
			'parameters' => array(
				'GET' => array(
					'name' => array( 'required' => true ),
					'page' => array( 'required' => false, 'default' => 1 )
				)
			)
		)
	)
));
// *** CONTROLLERS ***


// display the first page
function index() {
	$index = new Index();
	
	header('Content-type: text/html; charset=UTF-8');
	index_page_view($index, array());
}

class Index {
	public $galleryConf;
	public $albums;
	public $albumsDescriptions;
	
	function __construct() {
		$this->albums = array();
		$this->albumsDescriptions = getAlbumsSettings();

		$directories = getSubDirectoriesByDirectory(GALLERY_DIR);
		//get what's needed
		foreach($directories as $album) {
			$dir_e = GALLERY_DIR . DIRECTORY_SEPARATOR . $album;
			$picturesCount = $this->getPicturesCount($dir_e);
			if($picturesCount > 0) {
				$this->albums[$album] = array( 
					'description' => $this->getAlbumDescription($album),
					'images' => $picturesCount,
					'last_modified' => $this->getAlbumLastModified($dir_e),
					'preview' => $this->getAlbumPreview($album)
				);
			}
		}
	}
	
	function getPicturesCount($dir) {
		return count(readDirectoryContent($dir, IMAGES_INCLUDES));
	}
	
	function getAlbumLastModified($dir) {
		return date("m/d/y h:i", filemtime($dir));
	}
	
	function getAlbumDescription($album) {
		return isset($this->albumsDescriptions[$album]['description']) ? $this->albumsDescriptions[$album]['description'] : null;
	}
	
	function getAlbumPreview($album) {
		if(isset($this->albumsDescriptions[$album]['preview']) && is_file(GALLERY_DIR . DIRECTORY_SEPARATOR . $album . DIRECTORY_SEPARATOR . $this->albumsDescriptions[$album]['preview'])) {
			$preview = $this->albumsDescriptions[$album]['preview'];
		} else {
			$preview = getFirstPictureInAlbum($album);
		}
		return getCorrectThumbURL($album, $preview, 90);
	}
}

function album($name, $page = 1) {
	$dir = realpath(GALLERY_DIR . DIRECTORY_SEPARATOR . $name);
	if(!assureValidPath($dir)) {
		error_view('The requested path is not valid.');
		exit;
	}
	
	//clean the path
	$clean_path = cleanPath($dir);
	if($clean_path !== false && $clean_path !== $name && !empty($clean_path)) {
		//redirect to the clean path
		header('Location: '.$_SERVER['SCRIPT_NAME'].'?action=album&name='.$clean_path);
		return;
	}
	if(empty($clean_path)) {
		//redirect to index
		header('Location: '.$_SERVER['SCRIPT_NAME'].'?action=index');
		return;
	}
	
	$album = new Album($name, $page);
	
	if(! $album->isPageInAcceptableRange()) {
		error_view('Page number is out of range');
		exit;
	}
	
	header('Content-type: text/html; charset=UTF-8');
	album_page_view($album, array('paginate' => pagination_controls($album, 'album')), array('jsgallery' => 'script'));
}

class Album {

	public $name;
	public $pictures;
	public $page;
	public $total_pictures;
	public $total_pages;
	public $picturesDescriptions;
	
	function __construct($name, $page) {
		$this->name = $name;
		$this->page = $page;
		$this->pictures = array();
		$this->picturesDescriptions = getPicturesDescriptionsByAlbum($this->name);
		$this->getPictures();
	}
	
	public function isPageInAcceptableRange() {
		if($this->page === 1){
			return true;
		}
		return ($this->page > 0 && $this->page <= $this->total_pages);
	}
	
	private function getPictures() {
		$images = readDirectoryContent(GALLERY_DIR . DIRECTORY_SEPARATOR . $this->name, IMAGES_INCLUDES);
		
		$this->total_pictures = count($images);
		$this->total_pages = intval(ceil($this->total_pictures/IMAGES_PER_PAGE));
		$images = array_slice($images, ($this->page - 1) * IMAGES_PER_PAGE, IMAGES_PER_PAGE);
		
		foreach($images as $picture) {
			$this->pictures[$picture] = array (
				'description' => $this->getPictureDescription($picture),
				'small_preview_URL' => getCorrectThumbURL($this->name, $picture, 90),
				'big_preview_URL' => getCorrectThumbURL($this->name, $picture, 645)
			);
		}
	}
	
	private function getPictureDescription($picture) {
		return isset($this->picturesDescriptions[$picture]) ? $this->picturesDescriptions[$picture] : null;
	}
}

function image($album, $name, $size) {
	if(!assureValidPath(GALLERY_DIR . DIRECTORY_SEPARATOR . $album . DIRECTORY_SEPARATOR . $name)) {
		error_view('The requested path is not valid.');
		exit;
	}
	
	//clean the path
	$dir = realpath(GALLERY_DIR . DIRECTORY_SEPARATOR . $album);
	$clean_dir_path = cleanPath($dir);
	$image = realpath(GALLERY_DIR . DIRECTORY_SEPARATOR . $clean_dir_path .DIRECTORY_SEPARATOR . $name);
	$clean_image_path = cleanPath($image, GALLERY_DIR . DIRECTORY_SEPARATOR . $clean_dir_path);
	
	if(($clean_dir_path !== false && $clean_dir_path !== $album && !empty($clean_dir_path)) || ($clean_image_path !== false && $clean_image_path !== $name && !empty($clean_image_path))) {
		//redirect to the clean path
		header('Location: '.$_SERVER['SCRIPT_NAME'].'?action=image&album='.$clean_dir_path.'&name='.$clean_image_path);
		return;
	}
	if(empty($clean_image_path) && !empty($clean_dir_path)) {
		//redirect to album
		header('Location: '.$_SERVER['SCRIPT_NAME'].'?action=album&name='.$clean_dir_path);
		return;
	}
	if(empty($clean_dir_path)) {
		//redirect to index
		header('Location: '.$_SERVER['SCRIPT_NAME'].'?action=index');
		return;
	}
	
	$image = new Image($album, $name, $size);
	
	header('Content-type: text/html; charset=UTF-8');
	image_view($image , array());
}

class Image {

	public $name;
	public $size;
	public $description;
	
	public $sizes;
	
	public $prev_image;
	public $next_image;
	public $url;
	
	public $album_name;
	public $album_page;
	public $album_total_pictures;
	public $picture_index;
	
	function __construct($album, $name, $size) {
		$this->name = $name;
		$this->album_name = $album;
		$this->size = $this->getImageSize();
		$this->sizes = array(800, 1024, 1280, 1600);
		$this->description = $this->getPictureDescription();
		$this->url = $size === 'original' ? getURLBySize($size) . '/' . $this->album_name . '/' . $this->name : getCorrectThumbURL($this->album_name, $this->name, $size);
		$this->getAlbumInformations();
	}
	
	private function getPictureDescription() {
		$pictures_descriptions = getPicturesDescriptionsByAlbum($this->album_name);
		return isset($pictures_descriptions[$this->name]) ? $pictures_descriptions[$this->name] : null;
	}
	
	private function getAlbumInformations() {
		$images = array_values(readDirectoryContent(GALLERY_DIR . DIRECTORY_SEPARATOR . $this->album_name, IMAGES_INCLUDES));
		
		$this->album_total_pictures = count($images);
		$index = array_search($this->name, $images);
		
		$this->picture_index = $index + 1;
		if($index > 0) {
			$this->prev_image = $images[$index - 1];
		}
		if($index < $this->album_total_pictures - 1) {
			$this->next_image = $images[$index + 1];
		}
		
		$this->album_page = intval(ceil($this->picture_index / IMAGES_PER_PAGE));
	}
	
	private function getImageSize() {
		$file = realpath(GALLERY_DIR . DIRECTORY_SEPARATOR . $this->album_name . DIRECTORY_SEPARATOR . $this->name);
		$src_img = imagecreatefromstring(file_get_contents($file));
		
		$image_x = imagesx($src_img);
		$image_y = imagesy($src_img);
		
		imagedestroy($src_img);

		return $image_x > $image_y ? $image_x : $image_y;
	}
}

function admin_index() {
	$albums = getSubDirectoriesByDirectory(GALLERY_DIR);
	
	header('Content-type: text/html; charset=UTF-8');
	admin_view(null, $albums , array());
}

function admin_albummodify($name, $page = 1) {
	if(!assureValidPath(GALLERY_DIR . DIRECTORY_SEPARATOR . $name)) {
		echo json_encode(array('success' => false,'message' => 'Error: Invalid album requested.'));
		exit;
	}
	
	//path is clean?
	$dir = realpath(GALLERY_DIR . DIRECTORY_SEPARATOR . $name);
	$clean_dir_path = cleanPath($dir);
	if($clean_dir_path !== $name) {
		echo json_encode(array('success' => false,'message' => 'Error: Invalid album requested.'));
		exit;
	}
	
	$album = new Album($name, $page);
	
	if(! $album->isPageInAcceptableRange()) {
		error_view('Page number is out of range.');
		exit;
	}
	
	$albums_settings = getAlbumsSettings();
	$album_preview = isset($albums_settings[$album->name]['preview']) ? $albums_settings[$album->name]['preview'] : getFirstPictureInAlbum($album->name);
	$album_description = isset($albums_settings[$album->name]['description']) ? $albums_settings[$album->name]['description'] : null;
	
	$album_info = array(
		'preview' => getCorrectThumbURL($album->name, $album_preview, 90),
		'description' => $album_description,
		'paginate' => pagination_controls($album, 'modify')
	);
	
	admin_album_view($album, $album_info, getSubDirectoriesByDirectory(GALLERY_DIR), array('jquery-lightbox' => 'script', 'jsadmin' => 'script'));
}

function admin_pre_create() {
	admin_create_view(getSubDirectoriesByDirectory(GALLERY_DIR), array());
}

function admin_create($name) {
	header("Cache-Control: no-cache, must-revalidate");
	$dir = realpath(GALLERY_DIR . DIRECTORY_SEPARATOR . $name);
	
	if(strpos($dir, GALLERY_DIR) === 0 || file_exists($dir) ) {
		$status = array(
			'success' => false,
			'message' => 'An error occurred while creating the new album.'
		);
	} else {
		createDirectories(array(GALLERY_DIR,GALLERY_DIR . DIRECTORY_SEPARATOR . $name));
		$status = array(
			'success' => true,
			'message' => 'Album ' . htmlspecialchars($name) . ' created succesfully!'
		);
	}
	admin_status_view($status, getSubDirectoriesByDirectory(GALLERY_DIR), array());
}

function admin_delete($album) {
	header("Cache-Control: no-cache, must-revalidate");
	if(!assureValidPath(GALLERY_DIR . DIRECTORY_SEPARATOR . $album)) {
		echo json_encode(array('success' => false,'message' => 'Invalid album name.'));
		exit;
	}
	$dir = realpath(GALLERY_DIR . DIRECTORY_SEPARATOR . $album);
	
	//path is clean?
	$clean_dir_path = cleanPath($dir);
	if(empty($clean_dir_path) || $clean_dir_path !== $album) {
		echo json_encode(array('success' => false,'message' => 'Invalid album name.'));
		exit;
	}
	
	$d = dir($dir); 
	while($e = $d->read()) { 
		if (!in_array($e, array(".", ".."))) { 
			unlink($dir . DIRECTORY_SEPARATOR . $e); 
		} 
	} 
	$d->close(); 
	rmdir($dir);
	
	echo json_encode(array('success' => true, 'message' => 'Album deleted succesfully!', 'album' => htmlspecialchars($album)));
}

function admin_deletepictures($album, $delete_images) {
	header("Cache-Control: no-cache, must-revalidate");
	if(!assureValidPath(GALLERY_DIR . DIRECTORY_SEPARATOR . $album)) {
		echo json_encode(array('success' => false,'message' => 'Invalid album name.'));
		exit;
	}
	$dir = realpath(GALLERY_DIR . DIRECTORY_SEPARATOR . $album);
	
	//path is clean?
	$clean_dir_path = cleanPath($dir);
	if(empty($clean_dir_path) || $clean_dir_path !== $album) {
		echo json_encode(array('success' => false,'message' => 'Invalid album name.'));
		exit;
	}
	
	$images = readDirectoryContent($dir, IMAGES_INCLUDES);
	foreach($delete_images as $image) {
		if(in_array($image, $images)) {
			unlink($dir . DIRECTORY_SEPARATOR . $image);
		}
	}
	
	$albums_settings = getAlbumsSettings();
	if(isset($albums_settings[$album]['preview']) && is_file(GALLERY_DIR . DIRECTORY_SEPARATOR . $album . DIRECTORY_SEPARATOR . $albums_settings[$album]['preview'])) {
		$album_preview = $albums_settings[$album]['preview'];
	} else {
		$album_preview = getFirstPictureInAlbum($album);
	}
	
	echo json_encode(array('success' => true, 'message' => 'Album deleted succesfully!', 'pictures' => $delete_images, 'albumpreview' => getCorrectThumbURL($album, $album_preview, 90)));
}

function admin_pre_upload() {
	$albums = getSubDirectoriesByDirectory(GALLERY_DIR);
	admin_upload_view($albums, $albums, array('jquery-multiupload' => 'script', 'jsupload' => 'script'));
}

function admin_upload($album, $images) {
	header("Cache-Control: no-cache, must-revalidate");
	if(!assureValidPath(GALLERY_DIR . DIRECTORY_SEPARATOR . $album)) {
		$status = array(
			'success' => false,
			'message' => 'Invalid album name.'
		);
		admin_status_view($status, getSubDirectoriesByDirectory(GALLERY_DIR), array());
		exit;
	}
	$dir = realpath(GALLERY_DIR . DIRECTORY_SEPARATOR . $album);
	
	//path is clean?
	$clean_dir_path = cleanPath($dir);
	if(empty($clean_dir_path) || $clean_dir_path !== $album) {
		$status = array(
			'success' => false,
			'message' => 'Invalid album name.'
		);
		admin_status_view($status, getSubDirectoriesByDirectory(GALLERY_DIR), array());
		exit;
	}
	
	$status = array(
		'success' => true,
		'message' => 'Pictures uploaded succesfully!'
	);
	
	$size = count($images['name']);
	
	for($i = 0; $i < $size; $i++) {
		$target_path = $dir . DIRECTORY_SEPARATOR . basename( $images['name'][$i]); 

		if(!move_uploaded_file($images['tmp_name'][$i], $target_path)) {
			$status = array(
				'success' => false,
				'message' => 'Error while uploading pictures.'
			);
		}
	}
	
	admin_status_view($status, getSubDirectoriesByDirectory(GALLERY_DIR), array());
}

function admin_albumdescription($album, $description) {
	header("Cache-Control: no-cache, must-revalidate");
	if(!assureValidPath(GALLERY_DIR . DIRECTORY_SEPARATOR . $album)) {
		echo json_encode(array('hasMessage' => false, 'message' => null));
		exit;
	}
	
	//path is clean?
	$clean_dir_path = cleanPath(realpath(GALLERY_DIR . DIRECTORY_SEPARATOR . $album));
	if(empty($clean_dir_path) || $clean_dir_path !== $album) {
		echo json_encode(array('hasMessage' => false,'message' => 'Invalid album name.'));
		exit;
	}
	
	$albums_settings = getAlbumsSettings();
	
	$new_settings = array( 
		$album => array(
			'description' => !empty($description) ? $description : null,
			'preview' => isset($albums_settings[$album]['preview']) ? $albums_settings[$album]['preview'] : null
		)
	);
	
	writeAlbumsSettings(array_merge($albums_settings, $new_settings));
	
	echo json_encode(array('hasMessage' => !empty($description), 'message' => htmlspecialchars($description, ENT_QUOTES)));
}

function admin_albumpreview($album, $preview) {
	header("Cache-Control: no-cache, must-revalidate");
	if(!assureValidPath(GALLERY_DIR . DIRECTORY_SEPARATOR . $album . DIRECTORY_SEPARATOR . $preview)) {
		echo "0";
		exit;
	}

	//clean the path
	$dir = realpath(GALLERY_DIR . DIRECTORY_SEPARATOR . $album);
	$clean_dir_path = cleanPath($dir);
	$image = realpath(GALLERY_DIR . DIRECTORY_SEPARATOR . $clean_dir_path .DIRECTORY_SEPARATOR . $preview);
	$clean_image_path = cleanPath($image, GALLERY_DIR . DIRECTORY_SEPARATOR . $clean_dir_path);
	
	if($clean_dir_path === false || $clean_dir_path !== $album || empty($clean_dir_path) || $clean_image_path === false || $clean_image_path !== $preview || empty($clean_image_path)) {
		echo "0";
		exit;
	}
	
	$albums_settings = getAlbumsSettings();
	
	$new_settings = array( 
		$album => array(
			'preview' => $preview,
			'description' => isset($albums_settings[$album]['description']) ? $albums_settings[$album]['description'] : null
		)
	);
	
	writeAlbumsSettings(array_merge($albums_settings, $new_settings));
	
	echo getCorrectThumbURL($album, $preview, 90);
}

function admin_picturedescription($album, $picture, $description) {
	header("Cache-Control: no-cache, must-revalidate");
	if(!assureValidPath(GALLERY_DIR . DIRECTORY_SEPARATOR . $album . DIRECTORY_SEPARATOR . $picture)) {
		echo json_encode(array('hasMessage' => false, 'message' => null));
		exit;
	}
	
	//clean the path
	$dir = realpath(GALLERY_DIR . DIRECTORY_SEPARATOR . $album);
	$clean_dir_path = cleanPath($dir);
	$image = realpath(GALLERY_DIR . DIRECTORY_SEPARATOR . $clean_dir_path .DIRECTORY_SEPARATOR . $picture);
	$clean_image_path = cleanPath($image, GALLERY_DIR . DIRECTORY_SEPARATOR . $clean_dir_path);
	
	if($clean_dir_path === false || $clean_dir_path !== $album || empty($clean_dir_path) || $clean_image_path === false || $clean_image_path !== $picture || empty($clean_image_path)) {
		echo json_encode(array('hasMessage' => false, 'message' => null));
		exit;
	}
	
	$pictures_descriptions = getPicturesDescriptionsByAlbum($album);
	
	writePicturesDescriptionsByAlbum($album, array_merge($pictures_descriptions, array($picture => !empty($description) ? $description : null)));
	
	echo json_encode(array('hasMessage' => !empty($description), 'message' => htmlspecialchars($description, ENT_QUOTES)));
}


function thumb($album, $picture, $size) {
	$new_h = $size;
	$new_w = $size;
	
	$directory = getDirectoryBySize($size);
	
	$file = realpath(GALLERY_DIR . DIRECTORY_SEPARATOR . $album . DIRECTORY_SEPARATOR . $picture);
	
	if($file === false || strpos($file, GALLERY_DIR) !== 0 ) {
		die('Invalid');
		exit;
	}
	
	$thumb_file_path = $directory . DIRECTORY_SEPARATOR . $album . DIRECTORY_SEPARATOR . $picture;
		
	//correct header
	if(preg_match("/(jpg)|(jpeg)$/", strtolower($picture))) {
		header("Content-type: image/jpg");
	} elseif (preg_match("/(png)$/", strtolower($picture))) {
		header("Content-type: image/png");
	} elseif (preg_match("/(gif)$/", strtolower($picture))) {
		header("Content-type: image/gif");
	} else {
		die('Invalid format');
		exit;
	}
	if(file_exists($thumb_file_path) && filemtime($thumb_file_path) > filemtime($file)) {
		echo file_get_contents($thumb_file_path);
		exit;
	}
				
	$src_img = imagecreatefromstring(file_get_contents($file));
	
	$old_x = imagesx($src_img);
	$old_y = imagesy($src_img);
	$dst_img = null;
	
	//original image is smaller than the thumb. -> recopy the original one
	if($old_x <= $new_w && $old_y <= $new_h) {
		$dst_img = imagecreatetruecolor($old_x,$old_y);
		imagecopyresampled($dst_img,$src_img,0,0,0,0,$old_x,$old_y,$old_x,$old_y);
	} 
	//original image is bigger than the thumb
	else {
		if ($old_x > $old_y) {
			$thumb_w = $new_w;
			$thumb_h = $old_y * ($new_h/$old_x);
		}
		elseif ($old_x < $old_y) {
			$thumb_w = $old_x * ($new_w/$old_y);
			$thumb_h = $new_h;
		} else {
			$thumb_w = $new_w;
			$thumb_h = $new_h;
		}
		$dst_img = imagecreatetruecolor($thumb_w,$thumb_h);
		imagecopyresampled($dst_img,$src_img,0,0,0,0,$thumb_w,$thumb_h,$old_x,$old_y);
	} 
				
	createDirectories(array(RESIZE_DIR,$directory,$directory . DIRECTORY_SEPARATOR . $album));
	
	if(preg_match("/(jpg)|(jpeg)$/", strtolower($picture))) {
		@imagejpeg($dst_img, $thumb_file_path);
		imagejpeg($dst_img);
	} elseif (preg_match("/(png)$/", strtolower($picture))) {
		@imagepng($dst_img, $thumb_file_path);
		imagepng($dst_img);
	} else {
		@imagegif($dst_img, $thumb_file_path);
		imagegif($dst_img);
	}
	imagedestroy($dst_img); 
	imagedestroy($src_img); 
}

// *** API ********

function getAlbumsSettings() {
	$file = DESCRIPTION_DIR . DIRECTORY_SEPARATOR . DESCRIPTION_FILE_ALBUMS;
	if(file_exists($file)) {
		$handle = fopen($file, "r");
		$content = fread($handle, filesize($file));
		fclose($handle);
		
		return json_decode($content, true);
	} else {
		return array();
	}
}

function writeAlbumsSettings($settings) {
	createDirectories(array(DESCRIPTION_DIR));
	
	$file = DESCRIPTION_DIR . DIRECTORY_SEPARATOR . DESCRIPTION_FILE_ALBUMS;
	$handle = fopen($file, 'w');
	fwrite($handle, json_encode($settings));
	fclose($handle);
}

function getPicturesDescriptionsByAlbum($album) {
	$file = DESCRIPTION_DIR . DIRECTORY_SEPARATOR . $album . DIRECTORY_SEPARATOR . DESCRIPTION_FILE_PICTURES;
	if(file_exists($file)) {
		$handle = fopen($file, "r");
		$content = fread($handle, filesize($file));
		fclose($handle);
		
		return json_decode($content, true);
	} else {
		return array();
	}
}

function writePicturesDescriptionsByAlbum($album, $descriptions) {
	createDirectories(array(DESCRIPTION_DIR, DESCRIPTION_DIR . DIRECTORY_SEPARATOR . $album)); 
	$file = DESCRIPTION_DIR . DIRECTORY_SEPARATOR . $album . DIRECTORY_SEPARATOR . DESCRIPTION_FILE_PICTURES;
	$handle = fopen($file, 'w');
	fwrite($handle, json_encode($descriptions));
	fclose($handle);
}

// *** HELPERS ********

function getFirstPictureInAlbum($album) {
	$images = readDirectoryContent(GALLERY_DIR . DIRECTORY_SEPARATOR . $album, IMAGES_INCLUDES);
	return isset($images[0]) ? $images[0] : null;
}

function pagination_controls($gallery, $action) {
	$html = '';
	$path = $gallery->name;
	$page = $gallery->page;
	$page_count = $gallery->total_pages;
	
	//handle 0 pages when empty album too
	if($page_count < 2) {
		return;
	}

	$to_paginate = array(1, 2, 
		$page-3, $page-2, $page -1, 
		$page,
		$page + 1, $page + 2, $page + 3, 
		$page_count -1, $page_count);
	
	$filtered = array();
	for($i=0; $i < count($to_paginate); $i++) {
		$elem = $to_paginate[$i];
		if($elem > 0 && $elem <= $page_count) {
			$filtered[] = $elem;
		}
	}
	$to_paginate = array_unique($filtered);
	sort($to_paginate);
	
	$prev_value = 0;
	
	if($page != 1) {
		$html .= ' <a href="'.action_url($action, array('name' => $path, 'page' => $page - 1)).'">&lt; prev</a> ';
	}
	
	foreach($to_paginate as $to_paginate_page) {
		if($prev_value + 1 != $to_paginate_page) {
			$html .= ' ... ';
		}
		$prev_value = $to_paginate_page;
		$html .= ($to_paginate_page == $gallery->page) ? '<span class="currentPage">' . $to_paginate_page . '</span>' : ' <a href="'.action_url($action, array('name' => $path, 'page' => $to_paginate_page)).'">' . $to_paginate_page . '</a> ';
	}
	
	if($page != $page_count) {
		$html .= ' <a href="'.action_url($action, array('name' => $path, 'page' => $page + 1)).'">next  &gt;</a> ';
	}
	return $html;
}

function createDirectories($directories) {
	foreach($directories as $dir) {
		if(!is_dir($dir))
			@mkdir($dir);
	}
}

//get the subdirectories in the given directory
function getSubDirectoriesByDirectory($dir) {
	$h = dir($dir);
	$directories = array();
		
	while(($e = $h->read()) !== false) {
		if(!in_array($e, array(".", ".."))) {
			$dir_e = $dir . DIRECTORY_SEPARATOR .$e;
			if(is_dir($dir_e)) {
				$directories[] = $e;
			}
		}
	}
	$h->close();
	
	natcasesort($directories);
	return $directories;
}

//get the content of a directory based on a filter regexp
function readDirectoryContent($dir, $filter_regexp) {
	$content = array();
	$h = dir($dir);
		
	while(($e = $h->read()) !== false) {
		if(!in_array($e, array(".", ".."))) {
			$dir_e = $dir. DIRECTORY_SEPARATOR .$e;
			if (!is_dir($dir_e) && preg_match($filter_regexp, $dir_e)) {
				$content[] = $e;
			}
		}
	}
	$h->close();

	natcasesort($content);
	return $content;
}

function getURLBySize($size) {
	$array = array(
		90 => BASE_URL . 'resize/thumb' , 
		645 => BASE_URL . 'resize/645' , 
		800 => BASE_URL . 'resize/800' , 
		1024 => BASE_URL . 'resize/1024' , 
		1280 => BASE_URL . 'resize/1280' , 
		1600 => BASE_URL . 'resize/1600' ,
		'original' => BASE_URL . 'gallery'
	);
	if(array_key_exists($size, $array)) {
		return $array[$size];
	} else {
		error_view('Invalid image size requested.');
		exit;
	}
}

function getDirectoryBySize($size) {
	$array = array(
		90 => RESIZE_DIR . DIRECTORY_SEPARATOR . 'thumb' , 
		645 => RESIZE_DIR . DIRECTORY_SEPARATOR . '645' , 
		800 => RESIZE_DIR . DIRECTORY_SEPARATOR . '800' , 
		1024 => RESIZE_DIR . DIRECTORY_SEPARATOR . '1024' , 
		1280 => RESIZE_DIR . DIRECTORY_SEPARATOR . '1280' , 
		1600 => RESIZE_DIR . DIRECTORY_SEPARATOR . '1600'
	);
	if(array_key_exists($size, $array)) {
		return $array[$size];
	} else {
		error_view('Invalid image size requested.');
		exit;
	}
}

//get the Web Server URL or the thumb controller URL
function getCorrectThumbURL($album, $picture, $size) {
	$src_file_path = GALLERY_DIR . DIRECTORY_SEPARATOR . $album . DIRECTORY_SEPARATOR . $picture;
	$thumb_file_path = getDirectoryBySize($size) . DIRECTORY_SEPARATOR . $album . DIRECTORY_SEPARATOR . $picture;
	
	if($picture == null || !file_exists($src_file_path)) {
		return action_url('res', array('name' => 'lightbox-blank.gif'));
	} elseif(file_exists($thumb_file_path) && filemtime($thumb_file_path) > filemtime($src_file_path)) {
		return getURLBySize($size). '/' .$album. '/' . $picture;
	} else {
		return action_url('thumb', array('album' => $album, 'name' => $picture, 'size' => $size));
 	}
	
}

function assureValidPath($path) {
	$path = realpath($path);
	
	return !($path === false || strpos($path, GALLERY_DIR) !== 0 || !file_exists($path));
}

function action_url($action, $params = array()) {
	$p = '';
	foreach($params as $k => $v) {
		$p .= '&amp;'.$k.'='.urlencode($v);
	}
	return $_SERVER['SCRIPT_NAME'].'?action='.$action.$p;
}

function link_resource($type, $name) {
	switch($type) {
		case 'script':
			$html = "<script src='?action=res&amp;name=" . $name . "' type='text/javascript'></script>";
			break;
		case 'css':
			$html = "<link href='?action=res&amp;name=" . $name . "' rel='stylesheet' type='text/css'>";
			break;
	}
	return $html;
}

function get_val($arr, $key, $default = '') {
	return array_key_exists($key, $arr) ? $arr[$key] : $default;
}

function cleanPath($dir, $base_path = GALLERY_DIR) {
	//extract the resolved directory path (eg: path=/hurr/durr/.. will be resolved in /hurr)
	$resultant_path = str_replace('\\', '/', substr($dir, strlen($base_path)));
	if(is_string($resultant_path) && isset($resultant_path[0]) && ($resultant_path[0] == '/' || $resultant_path[0] == '\\')) {
		$resultant_path = substr($resultant_path, 1);
	}
	
	return $resultant_path;
}

// *** RESOURCES ********

function resources($name) {
	$resources = array(
		'jsgallery' => 'resource_jsgallery',
		'jsadmin' => 'resource_jsadmin',
		'jsupload' => 'resource_jsupload' ,
		'jquery-lightbox' => 'resource_jquery_lightbox',
		'jquery-multiupload' => 'resource_jquery_multiupload',
		'bin.gif' =>  'resource_image_bin',
		'lightbox-blank.gif'  => 'resource_image_blank',
		'lightbox-btn-close.gif'  => 'resource_image_close',
		'lightbox-ico-loading.gif'  => 'resource_image_loading'
	);
	call_user_func($resources[$name]);
}

function resource_jsgallery() {
	$gallery = <<<JS_GALLERY
		var CONST_LEFT = 37;
		var CONST_RIGHT = 39;
		var CONST_UP = 38;
		var CONST_DOWN = 40;
		
		$(document).keydown(function(event) {
			//get current picture and list size
			var currentSelect = $("#thumbimagelist > li").index($("#thumbimagelist > li > a > img.selected").parent('a').parent('li')) + 1;
			var listSize = $("#thumbimagelist li").index($("#thumbimagelist li:last")) + 1;
				
			switch(event.keyCode) {
				case CONST_LEFT:
					currentSelect = currentSelect == 1 ? listSize : currentSelect - 1;
					break;
				case CONST_RIGHT:
					currentSelect = currentSelect == listSize ? 1 : currentSelect + 1;
					break;
				case CONST_UP:
					if(listSize < 4) {
						break;
					} else if(currentSelect < 4) {
						//here come dragons
						if(currentSelect == 3) {
							//go to the first right item
							currentSelect = listSize - (listSize % 3);
						} else if(currentSelect == 1) {
							//go to the first left item
							var shift = [2,0,1];
							currentSelect = listSize - shift[listSize % 3];
						} else {
							//go to the first center item
							var shift = [1,2,0];
							currentSelect = listSize - shift[listSize % 3];
						}
					} else {
						currentSelect -= 3;
					}
					break;
				case CONST_DOWN:
					if(listSize < 4) {
						break;
					} else if((currentSelect + 3) > listSize) {
						//here come dragons again
						currentSelect = currentSelect % 3 == 0 ? 3 : currentSelect % 3;
					} else {
						currentSelect += 3;
					}
					break;
			}
				
			var moveTo = function(selection_) {
				//move to the correct picture
				$("#picturesrc").html('<a href="' + $("#thumbimagelist > li > a:eq(" + (selection_ - 1) + ")").attr("href") + '"><img src="' + $("#thumbimagelist > li > a:eq(" + (selection_ - 1) + ")").attr("rev") + '"//></a>');
				$("#picturedesc").text($("#thumbimagelist > li > a:eq(" + (selection_ - 1) + ")").children("img").attr("title"));
				
				$("#thumbimagelist > li > a > img.selected").removeClass("selected").fadeTo("normal", 0.50);
				$("#thumbimagelist > li > a:eq(" + (selection_ - 1) + ")").children("img").stop().addClass("selected").css({ opacity: "1.0" });
			};
			moveTo(currentSelect);
		});
		
		$(document).ready(function() {
			//move list in the correct place
			$("#thumbimagelist").html($("#rawimagelist").html());
			$("#rawimagelist").remove();
			
			//create list
			$("#thumbimagelist li a").each(function(i) {
				// set initial opacity
				$(this).children("img").css({ opacity: "0.5" });
				
				//add mouse event
				$(this).click(function() {
					$("#picturesrc").html('<a href="' + $(this).attr("href") + '"><img src="' + $(this).attr("rev") + '"//></a>');
					$("#picturedesc").text($(this).children("img").attr("title"));
					
					//make the picture the selected one
					$("#thumbimagelist > li > a > img.selected").removeClass("selected").fadeTo("normal", 0.50);
					$(this).children("img").stop().addClass("selected").css({ opacity: "1.0" });
					return false;
				});
				
				//add mouse over
				$(this).children("img").mouseover(function(){
					if( !$(this).hasClass("selected") )
					{
						$(this).fadeTo("normal", 1.00);
					}
				}).mouseout(function(){
					if( !$(this).hasClass("selected") )
					{
						$(this).fadeTo("normal", 0.50);
					}
				});
			});
			
			//initialize gallery (first pic)
			firstItem = $("#thumbimagelist > li:first > a");
			firstItem.children("img").addClass("selected").css({ opacity: "1.0" });
			$("#picturesrc").html('<a href="' + firstItem.attr("href") + '"><img src="' + firstItem.attr("rev") + '"//></a>');
			$("#picturedesc").text(firstItem.children("img").attr("title"));
			
		});
JS_GALLERY;
	header('Content-type: text/javascript');
	echo $gallery;
}

function resource_jsadmin() {
	$admin = <<<JS_ADMIN
		$(document).ready(function() {
			//album info
			var currentAlbumName = $("#adminAlbumInfo h1").text();
			
			//add/edit
			$("#adminAlbumInfo p:first").click(function() {
				currentText = $("#adminAlbumInfo p:first").text();
				$("#adminAlbumInfo p textarea:first").val(currentText);
				$("#adminAlbumInfo p:first").css({'display':'none'});
				$("#adminAlbumInfo p:last").css({'display':'block'});
				$("#adminAlbumInfo div.buttonBar:first").css({'display':'block'});
			});
				
			//cancel
			$("#adminAlbumInfo div.buttonBar input[type='reset']:first").click(function() {
				$("#adminAlbumInfo p:first").css({'display':'block'});
				$("#adminAlbumInfo p:last").css({'display':'none'});
				$("#adminAlbumInfo div.buttonBar:first").css({'display':'none'});
			});
				
			//save
			$("#adminAlbumInfo div.buttonBar input[type='submit']:first").click(function() {
				currentText = $("#adminAlbumInfo p textarea:first").val();
				$.post("?action=albumdescription", { name: currentAlbumName, comment: currentText },function(data){
					//parse JSON
					data = eval('(' + data + ')');
					if(data.hasMessage) {
						$("#adminAlbumInfo p:first").html(data.message);
					} else {
						$("#adminAlbumInfo p:first").html('<i>Click to add description</i>');
					}
					$("#adminAlbumInfo p:first").css({'display':'block'});
					$("#adminAlbumInfo p:last").css({'display':'none'});
					$("#adminAlbumInfo div.buttonBar:first").css({'display':'none'});
				});
			});
			
			//delete bars
			$("#topdeletebar input:first, #bottomdeletebar input:first").click(function() {
				$.post("?action=delete", { name: currentAlbumName, }, function(data) {
					data = eval('(' + data + ')');
					if(data.success) {
						$("#content").html('<p class="statusSuccess">' + data.message + '</p>');
						//delete entry in list
						$("#adminalbumlist li a").each(function() {
							if($(this).text() == currentAlbumName) {
								$(this).fadeOut(500, function () {
									$(this).remove();
								});
							}
						});
					}
				});
			});
			
			$("#topdeletebar input:last, #bottomdeletebar input:last").click(function() {
				var images = []
				$("div.controlBar p input:checked").each(function() {
					hurr = $(this).parent("p").parent("div").parent("div").find("a img").attr("alt");
					images.push(hurr);
				});
				$.post("?action=deletepictures", { name: currentAlbumName, 'deleteimages[]' : images }, function(data) {
					data = eval('(' + data + ')');
					if(data.success) {
						$("#adminImageList div.adminEntry").each(function() {
							if(jQuery.inArray($(this).find("a img").attr("alt"), data.pictures) != -1) {
								$(this).fadeOut(500, function () {
									$(this).remove();
								});
							}
						});
						$("#adminAlbumInfo img:first").attr("src",data.albumpreview);
						$("#adminAlbumInfo img:first").attr("alt",data.albumpreview);
					}
				});
			});
			
			//add pictures controls
			$("#adminImageList div.adminEntry").each(function() {
				var currentPictureName = $(this).find("a img").attr("alt");
				//set as album preview
				$(this).find("div.controlBar input[type='button']:first").click(function() {
					$.post("?action=albumpreview", { name: currentAlbumName, preview: currentPictureName },function(data) {
						if(data) {
							$("#adminAlbumInfo img:first").attr("src",data);
						}
					});
				});
				
				//add/edit
				$(this).find("div.descriptionBar p:first").click(function() {
					currentText = $(this).text();
					$(this).parent("div").find("p textarea:first").val(currentText);
					$(this).css({'display':'none'});
					$(this).parent("div").find("p:last").css({'display':'block'});
					$(this).parent("div").find("div.buttonBar:first").css({'display':'block'});
				});
				
				//cancel
				$(this).find("div.descriptionBar div.buttonBar input[type='reset']:first").click(function() {
					$(this).parent("div").parent("div").find("p:first").css({'display':'block'});
					$(this).parent("div").parent("div").find("p:last").css({'display':'none'});
					$(this).parent("div.buttonBar").css({'display':'none'});
				});
				
				//save
				$(this).find("div.descriptionBar div.buttonBar input[type='submit']:first").click(function() {
					var currentText = $(this).parent("div").parent("div").find("p textarea:first").val();
					var reference = this;
					$.post("?action=picturedescription", { album: currentAlbumName, name: currentPictureName, comment: currentText },function(data){
						//parse JSON
						data = eval('(' + data + ')');
						if(data.hasMessage) {
							$(reference).parent("div").parent("div").find("p:first").html(data.message);
						} else {
							$(reference).parent("div").parent("div").find("p:first").html('<i>Click to add description</i>');
						}
						$(reference).parent("div").parent("div").find("p:first").css({'display':'block'});
						$(reference).parent("div").parent("div").find("p:last").css({'display':'none'});
						$(reference).parent("div.buttonBar").css({'display':'none'});
					});
				});
				
				//lightbox
				$(this).find("a").lightBox({ imageLoading: '?action=res&name=lightbox-ico-loading.gif', imageBtnClose: '?action=res&name=lightbox-btn-close.gif', imageBlank: '?action=res&name=lightbox-blank.gif' });
			});
		});
JS_ADMIN;
	header('Content-type: text/javascript');
	echo $admin;
}

function resource_jsupload() {
	$upload = <<<JS_UPLOAD
	$(document).ready(function() {
		$('#multiupload').MultiFile({ 
			STRING: {  
				remove: '<img src="?action=res&name=bin.gif" height="16" width="16" alt="x"/>'
			}
		});
				
		$('#resetQueue').click(function() {
			$('input:file').MultiFile('reset');
		});
		
		$('#uploadform').submit(function() {
			$("#status").html('<div align="center"><img src="?action=res&name=lightbox-ico-loading.gif"></div><div align="center">Uploading...</div>');
			return true;
		});
	});
JS_UPLOAD;
	header('Content-type: text/javascript');
	echo $upload;
}

function resource_style() {
	return <<<EOD_STYLE
body, html { font-family: "Trebuchet MS", Helvetica, Tahoma, Verdana, Arial; font-size: 11px; margin: 0; padding: 0; border: 0; height: 100%; }

img { border: 0px; }

a { color: #3466a4; text-decoration: none; }
a:hover { text-decoration: underline; }

h1 { clear: both; color: #3466a4; }

.clearfix { display: inline-block; }
.clearfix:after { content: "."; display: block; height: 0; font-size: 0; clear: both; visibility: hidden; }
* html .clearfix { height: 1px; }
.clearfix { display: block; }

#nonFooter { position: relative; min-height: 100%; }
* html #nonFooter { height: 100%; }

#headerBG { width: 100%; height: 62px; border-bottom: 1px solid silver; }
#headercontainer { width: 1000px; height: auto; margin: 0 auto; }
#menuheader { width: 100%; height: 63px; text-align: right; }
#logo { margin-top: 30px; float: left; color: #3466a4; font-size: 24px; }
#menucontainer { float: right; height: 50px; width: auto; }

ul#nav { width: auto; height: 50px; list-style: none; margin-top: 35px; }
ul#nav li { float: left; background-position: 0px 0px; margin-left: 15px; }
ul#nav li a { display: block; font-size: 18px; color: #3466a4; text-decoration: none; }
ul#nav li a:hover { text-decoration: underline; }

#footer { border-top: 1px solid silver; width: 100%; height: 24px; clear: both; position: relative; margin-top: -25px; }
#footercontent { width: 1000px; height: auto; margin: auto; color: gray; padding-top: 4px; text-align: center; }

#container { width: 1000px; height: auto; margin: 0 auto; padding-bottom: 10px; }
#webcontent { height: auto; clear: both; }
#content { width: 645px; height: auto; float: left; margin: 25px 15px; }
#contentlarge { width: 1000px; height: auto; float: left; margin: 25px 15px; }

#contentcontrols { height: 16px; width: auto; display: inline-block; margin-bottom: 5px; float: left; font-size: 12px; color: #3466a4; padding-bottom: 8px; border-bottom: 1px solid silver; }
#contentcontrols a { text-decoration: none; color: #3466a4; }
#contentcontrols a:hover { text-decoration: underline; }
#sizecontrols { height: 16px; width: auto; display: inline-block; margin-bottom: 5px; float: right; font-size: 12px; color: #3466a4; padding-bottom: 8px; border-bottom: 1px solid silver; }
#sizecontrols a { text-decoration: none; color: #3466a4; }
#sizecontrols a:hover { text-decoration: underline; }

p#picturedesc { clear: both; margin: 0; font-size: 12px; color: #333; }
p#picturesrc { text-align: center; } p#picturesrc img { max-height: 645px; max-width: 645px; }

#sidemenu { width: 300px; height: auto; float: right; }
.menubox { margin-top: 25px; width: 100%; border-left: 1px solid silver; }

ul.sidemenulist { width: auto; list-style: none; margin: 0; padding: 0; float: left; clear: both; }
ul.sidemenulist li { width: auto; height: auto; text-align: left; font-size: 12px; padding-left: 8px; }
ul.sidemenulist li a { height: auto; color: #3466a4; text-decoration: none; }
ul.sidemenulist li a:hover { text-decoration: underline; }

ul.thumbimagelist { margin: 0; padding: 0; float: left; clear: both; width: auto; height: auto; list-style: none; display: block; border: 0; }
ul.thumbimagelist li { float: left; width: 90px; height: 90px; display: block; margin-left: 10px; margin-bottom: 10px; }
ul.thumbimagelist li p { display: none; }
ul.thumbimagelist li a { display: table-cell; width: 90px; height: 90px; text-align: center; vertical-align: middle; }
ul.thumbimagelist li a img { vertical-align: middle; }

.albumEntry { clear: both; margin-bottom: 20px; }
.albumEntry div.albumPreview { float: left; width: 90px; display: table-cell; text-align: center; vertical-align: middle; margin-right: 10px; }
.albumEntry div.albumPreview img { vertical-align: middle; }
.albumEntry div.albumInfo { float: left; }
.albumEntry div.albumInfo h1 { color: #3466a4; clear: none; border: 0; margin: 0; }
.albumEntry div.albumInfo h4 { clear: none; color: black; border: 0; padding: 0; margin: 0; font-style: italic; font-size: 9px; font-weight: normal; }
.albumEntry div.albumInfo p { color: #333; }

#adminAlbumInfo { margin-bottom: 30px; }
#adminAlbumInfo img { display: block; clear: both; margin: 0; }
#adminAlbumInfo p { clear: both; color: #333; }
#adminAlbumInfo div.buttonBar { display: none; clear: both; }
#adminAlbumInfo div.buttonBar input { border: 1px solid #3466a4; margin-left: 20px; }

.adminEntry { height: 100px; width: 100%; clear: both; }
.adminEntry a { display: table-cell; width: 100px; height: 100px; text-align: center; vertical-align: middle; float: left; }
.adminEntry a img { vertical-align: middle; }
.adminEntry div.controlBar { width: 200px; float: left; }
.adminEntry div.controlBar p {float: left; width: 100%; clear: both; margin: 0px 0px 10px 0px; }
.adminEntry div.controlBar p input { border: 1px solid #3466a4; margin-left: 20px; float: left; clear: right; }
.adminEntry div.descriptionBar { float: left; width: 300px; }
.adminEntry div.descriptionBar p { color: #333; float: left; margin-left: 20px; margin-top: 0px; }
.adminEntry div.descriptionBar div.buttonBar { display: none; float: left; }
.adminEntry div.descriptionBar div.buttonBar input { border: 1px solid #3466a4; margin-left: 20px; float: left; }

.admindeletebar { clear: both; }
.admindeletebar input { border: 1px solid #3466a4; margin: 10px 20px 30px 0px; float: left; }

#adminImageList div.loading { display: table-cell; width: 645px; height: 645px; text-align: center; vertical-align: middle; float: left; }
#adminImageList div.loading img { vertical-align: middle; }

.currentPage { color: silver; font-weight: bold; }
.statusSuccess { font-weight: bold; font-size: 14px; color: green; }
.statusFail { font-weight: bold; font-size: 14px; color: red; }

#jquery-overlay { position: absolute; top: 0; left: 0; z-index: 90; width: 100%; height: 500px; }
#jquery-lightbox { position: absolute; top: 0; left: 0; width: 100%; z-index: 100; text-align: center; line-height: 0; }
#jquery-lightbox a img { border: none; }
#lightbox-container-image-box { position: relative; background-color: #fff; width: 250px; height: 250px; margin: 0 auto; }
#lightbox-container-image { padding: 10px; }
#lightbox-loading { position: absolute; top: 40%; left: 0%; height: 25%; width: 100%; text-align: center; line-height: 0; }
#lightbox-nav { position: absolute; top: 0; left: 0; height: 100%; width: 100%; z-index: 10; }
#lightbox-container-image-box > #lightbox-nav { left: 0; }
#lightbox-nav a { outline: none; }
#lightbox-nav-btnPrev, #lightbox-nav-btnNext { width: 49%; height: 100%; display: block; }
#lightbox-nav-btnPrev { left: 0; float: left; }
#lightbox-nav-btnNext { right: 0; float: right; }
#lightbox-container-image-data-box { font: 10px Verdana, Helvetica, sans-serif; background-color: #fff; margin: 0 auto; line-height: 1.4em; overflow: auto; width: 100%; padding: 0 10px 0; }
#lightbox-container-image-data { padding: 0 10px; color: #666; }
#lightbox-container-image-data #lightbox-image-details { width: 70%; float: left; text-align: left; }
#lightbox-image-details-caption { font-weight: bold; }
#lightbox-image-details-currentNumber { display: block; clear: left; padding-bottom: 1.0em; }
#lightbox-secNav-btnClose { width: 66px; float: right; padding-bottom: 0.7em; }
EOD_STYLE;
};

function resource_jquery_lightbox() {
	$lightbox = <<<JS_LIGHTBOX
	LyoqDQogKiBqUXVlcnkgbGlnaHRCb3ggcGx1Z2luDQogKiBUaGlzIGpRdWVyeSBwbHVnaW4gd2FzIGluc3BpcmVkIGFuZCBiYXNlZCBvbiBMaWdodGJveCAyIGJ5IExva2VzaCBEaGFrYXIgKGh0dHA6Ly93d3cuaHVkZGxldG9nZXRoZXIuY29tL3Byb2plY3RzL2xpZ2h0Ym94Mi8pDQogKiBhbmQgYWRhcHRlZCB0byBtZSBmb3IgdXNlIGxpa2UgYSBwbHVnaW4gZnJvbSBqUXVlcnkuDQogKiBAbmFtZSBqcXVlcnktbGlnaHRib3gtMC41LmpzDQogKiBAYXV0aG9yIExlYW5kcm8gVmllaXJhIFBpbmhvIC0gaHR0cDovL2xlYW5kcm92aWVpcmEuY29tDQogKiBAdmVyc2lvbiAwLjUNCiAqIEBkYXRlIEFwcmlsIDExLCAyMDA4DQogKiBAY2F0ZWdvcnkgalF1ZXJ5IHBsdWdpbg0KICogQGNvcHlyaWdodCAoYykgMjAwOCBMZWFuZHJvIFZpZWlyYSBQaW5obyAobGVhbmRyb3ZpZWlyYS5jb20pDQogKiBAbGljZW5zZSBDQyBBdHRyaWJ1dGlvbi1ObyBEZXJpdmF0aXZlIFdvcmtzIDIuNSBCcmF6aWwgLSBodHRwOi8vY3JlYXRpdmVjb21tb25zLm9yZy9saWNlbnNlcy9ieS1uZC8yLjUvYnIvZGVlZC5lbl9VUw0KICogQGV4YW1wbGUgVmlzaXQgaHR0cDovL2xlYW5kcm92aWVpcmEuY29tL3Byb2plY3RzL2pxdWVyeS9saWdodGJveC8gZm9yIG1vcmUgaW5mb3JtYXRpb25zIGFib3V0IHRoaXMgalF1ZXJ5IHBsdWdpbg0KICovDQpldmFsKGZ1bmN0aW9uKHAsYSxjLGssZSxyKXtlPWZ1bmN0aW9uKGMpe3JldHVybihjPGE/Jyc6ZShwYXJzZUludChjL2EpKSkrKChjPWMlYSk+MzU/U3RyaW5nLmZyb21DaGFyQ29kZShjKzI5KTpjLnRvU3RyaW5nKDM2KSl9O2lmKCEnJy5yZXBsYWNlKC9eLyxTdHJpbmcpKXt3aGlsZShjLS0pcltlKGMpXT1rW2NdfHxlKGMpO2s9W2Z1bmN0aW9uKGUpe3JldHVybiByW2VdfV07ZT1mdW5jdGlvbigpe3JldHVybidcXHcrJ307Yz0xfTt3aGlsZShjLS0paWYoa1tjXSlwPXAucmVwbGFjZShuZXcgUmVnRXhwKCdcXGInK2UoYykrJ1xcYicsJ2cnKSxrW2NdKTtyZXR1cm4gcH0oJyg2KCQpeyQuMk4uM2c9Nig0KXs0PTIzLjJIKHsyQjpcJyMzNFwnLDJnOjAuOCwxZDpGLDFNOlwnMTgvNS0zMy1ZLjE2XCcsMXY6XCcxOC81LTF1LTJRLjE2XCcsMUU6XCcxOC81LTF1LTJMLjE2XCcsMVc6XCcxOC81LTF1LTJJLjE2XCcsMTk6XCcxOC81LTJGLjE2XCcsMWY6MTAsMkE6M2QsMnM6XCcxalwnLDJvOlwnMzJcJywyajpcJ2NcJywyZjpcJ3BcJywyZDpcJ25cJyxoOltdLDk6MH0sNCk7ZiBJPU47NiAyMCgpezFYKE4sSSk7dSBGfTYgMVgoMWUsSSl7JChcJzFVLCAxUywgMVJcJykubCh7XCcxUVwnOlwnMkVcJ30pOzFPKCk7NC5oLkI9MDs0Ljk9MDs3KEkuQj09MSl7NC5oLjFKKHYgMW0oMWUuMTcoXCdKXCcpLDFlLjE3KFwnMnZcJykpKX1qezM2KGYgaT0wO2k8SS5CO2krKyl7NC5oLjFKKHYgMW0oSVtpXS4xNyhcJ0pcJyksSVtpXS4xNyhcJzJ2XCcpKSl9fTJuKDQuaFs0LjldWzBdIT0xZS4xNyhcJ0pcJykpezQuOSsrfUQoKX02IDFPKCl7JChcJ21cJykuMzEoXCc8ZSBnPSJxLTEzIj48L2U+PGUgZz0icS01Ij48ZSBnPSI1LXMtYi13Ij48ZSBnPSI1LXMtYiI+PDF3IGc9IjUtYiI+PGUgMlY9IiIgZz0iNS1rIj48YSBKPSIjIiBnPSI1LWstViI+PC9hPjxhIEo9IiMiIGc9IjUtay1YIj48L2E+PC9lPjxlIGc9IjUtWSI+PGEgSj0iIyIgZz0iNS1ZLTI5Ij48MXcgVz0iXCcrNC4xTStcJyI+PC9hPjwvZT48L2U+PC9lPjxlIGc9IjUtcy1iLVQtdyI+PGUgZz0iNS1zLWItVCI+PGUgZz0iNS1iLUEiPjwxaSBnPSI1LWItQS0xdCI+PC8xaT48MWkgZz0iNS1iLUEtMWciPjwvMWk+PC9lPjxlIGc9IjUtMXMiPjxhIEo9IiMiIGc9IjUtMXMtMjIiPjwxdyBXPSJcJys0LjFXK1wnIj48L2E+PC9lPjwvZT48L2U+PC9lPlwnKTtmIHo9MUQoKTskKFwnI3EtMTNcJykubCh7Mks6NC4yQiwySjo0LjJnLFM6elswXSxQOnpbMV19KS4xVigpO2YgUj0xcCgpOyQoXCcjcS01XCcpLmwoezFUOlJbMV0rKHpbM10vMTApLDFjOlJbMF19KS5FKCk7JChcJyNxLTEzLCNxLTVcJykuQyg2KCl7MWEoKX0pOyQoXCcjNS1ZLTI5LCM1LTFzLTIyXCcpLkMoNigpezFhKCk7dSBGfSk7JChHKS4yRyg2KCl7ZiB6PTFEKCk7JChcJyNxLTEzXCcpLmwoe1M6elswXSxQOnpbMV19KTtmIFI9MXAoKTskKFwnI3EtNVwnKS5sKHsxVDpSWzFdKyh6WzNdLzEwKSwxYzpSWzBdfSl9KX02IEQoKXskKFwnIzUtWVwnKS5FKCk7Nyg0LjFkKXskKFwnIzUtYiwjNS1zLWItVC13LCM1LWItQS0xZ1wnKS4xYigpfWp7JChcJyM1LWIsIzUtaywjNS1rLVYsIzUtay1YLCM1LXMtYi1ULXcsIzUtYi1BLTFnXCcpLjFiKCl9ZiBRPXYgMWooKTtRLjFQPTYoKXskKFwnIzUtYlwnKS4yRChcJ1dcJyw0LmhbNC45XVswXSk7MU4oUS5TLFEuUCk7US4xUD02KCl7fX07US5XPTQuaFs0LjldWzBdfTs2IDFOKDFvLDFyKXtmIDFMPSQoXCcjNS1zLWItd1wnKS5TKCk7ZiAxSz0kKFwnIzUtcy1iLXdcJykuUCgpO2YgMW49KDFvKyg0LjFmKjIpKTtmIDF5PSgxcisoNC4xZioyKSk7ZiAxST0xTC0xbjtmIDJ6PTFLLTF5OyQoXCcjNS1zLWItd1wnKS4zZih7UzoxbixQOjF5fSw0LjJBLDYoKXsyeSgpfSk7NygoMUk9PTApJiYoMno9PTApKXs3KCQuM2UuM2MpezFIKDNiKX1qezFIKDNhKX19JChcJyM1LXMtYi1ULXdcJykubCh7Uzoxb30pOyQoXCcjNS1rLVYsIzUtay1YXCcpLmwoe1A6MXIrKDQuMWYqMil9KX07NiAyeSgpeyQoXCcjNS1ZXCcpLjFiKCk7JChcJyM1LWJcJykuMVYoNigpezJ1KCk7MnQoKX0pOzJyKCl9OzYgMnUoKXskKFwnIzUtcy1iLVQtd1wnKS4zOChcJzM1XCcpOyQoXCcjNS1iLUEtMXRcJykuMWIoKTs3KDQuaFs0LjldWzFdKXskKFwnIzUtYi1BLTF0XCcpLjJwKDQuaFs0LjldWzFdKS5FKCl9Nyg0LmguQj4xKXskKFwnIzUtYi1BLTFnXCcpLjJwKDQuMnMrXCcgXCcrKDQuOSsxKStcJyBcJys0LjJvK1wnIFwnKzQuaC5CKS5FKCl9fTYgMnQoKXskKFwnIzUta1wnKS5FKCk7JChcJyM1LWstViwjNS1rLVhcJykubCh7XCdLXCc6XCcxQyBNKFwnKzQuMTkrXCcpIEwtT1wnfSk7Nyg0LjkhPTApezcoNC4xZCl7JChcJyM1LWstVlwnKS5sKHtcJ0tcJzpcJ00oXCcrNC4xditcJykgMWMgMTUlIEwtT1wnfSkuMTEoKS4xayhcJ0NcJyw2KCl7NC45PTQuOS0xO0QoKTt1IEZ9KX1qeyQoXCcjNS1rLVZcJykuMTEoKS4ybSg2KCl7JChOKS5sKHtcJ0tcJzpcJ00oXCcrNC4xditcJykgMWMgMTUlIEwtT1wnfSl9LDYoKXskKE4pLmwoe1wnS1wnOlwnMUMgTShcJys0LjE5K1wnKSBMLU9cJ30pfSkuRSgpLjFrKFwnQ1wnLDYoKXs0Ljk9NC45LTE7RCgpO3UgRn0pfX03KDQuOSE9KDQuaC5CLTEpKXs3KDQuMWQpeyQoXCcjNS1rLVhcJykubCh7XCdLXCc6XCdNKFwnKzQuMUUrXCcpIDJsIDE1JSBMLU9cJ30pLjExKCkuMWsoXCdDXCcsNigpezQuOT00LjkrMTtEKCk7dSBGfSl9anskKFwnIzUtay1YXCcpLjExKCkuMm0oNigpeyQoTikubCh7XCdLXCc6XCdNKFwnKzQuMUUrXCcpIDJsIDE1JSBMLU9cJ30pfSw2KCl7JChOKS5sKHtcJ0tcJzpcJzFDIE0oXCcrNC4xOStcJykgTC1PXCd9KX0pLkUoKS4xayhcJ0NcJyw2KCl7NC45PTQuOSsxO0QoKTt1IEZ9KX19MmsoKX02IDJrKCl7JChkKS4zMCg2KDEyKXsyaSgxMil9KX02IDFHKCl7JChkKS4xMSgpfTYgMmkoMTIpezcoMTI9PTJoKXtVPTJaLjJlOzF4PTI3fWp7VT0xMi4yZTsxeD0xMi4yWX0xND0yWC4yVyhVKS4yVSgpOzcoKDE0PT00LjJqKXx8KDE0PT1cJ3hcJyl8fChVPT0xeCkpezFhKCl9NygoMTQ9PTQuMmYpfHwoVT09MzcpKXs3KDQuOSE9MCl7NC45PTQuOS0xO0QoKTsxRygpfX03KCgxND09NC4yZCl8fChVPT0zOSkpezcoNC45IT0oNC5oLkItMSkpezQuOT00LjkrMTtEKCk7MUcoKX19fTYgMnIoKXs3KCg0LmguQi0xKT40LjkpezJjPXYgMWooKTsyYy5XPTQuaFs0LjkrMV1bMF19Nyg0Ljk+MCl7MmI9diAxaigpOzJiLlc9NC5oWzQuOS0xXVswXX19NiAxYSgpeyQoXCcjcS01XCcpLjJhKCk7JChcJyNxLTEzXCcpLjJUKDYoKXskKFwnI3EtMTNcJykuMmEoKX0pOyQoXCcxVSwgMVMsIDFSXCcpLmwoe1wnMVFcJzpcJzJTXCd9KX02IDFEKCl7ZiBvLHI7NyhHLjFoJiZHLjI4KXtvPUcuMjYrRy4yUjtyPUcuMWgrRy4yOH1qIDcoZC5tLjI1PmQubS4yNCl7bz1kLm0uMlA7cj1kLm0uMjV9antvPWQubS4yTztyPWQubS4yNH1mIHksSDs3KFouMWgpezcoZC50LjFsKXt5PWQudC4xbH1qe3k9Wi4yNn1IPVouMWh9aiA3KGQudCYmZC50LjFBKXt5PWQudC4xbDtIPWQudC4xQX1qIDcoZC5tKXt5PWQubS4xbDtIPWQubS4xQX03KHI8SCl7MXo9SH1qezF6PXJ9NyhvPHkpezFCPW99ansxQj15fTIxPXYgMW0oMUIsMXoseSxIKTt1IDIxfTs2IDFwKCl7ZiBvLHI7NyhaLjFaKXtyPVouMVo7bz1aLjJNfWogNyhkLnQmJmQudC4xRil7cj1kLnQuMUY7bz1kLnQuMVl9aiA3KGQubSl7cj1kLm0uMUY7bz1kLm0uMVl9MnE9diAxbShvLHIpO3UgMnF9OzYgMUgoMkMpe2YgMng9diAydygpOzFxPTJoOzNoe2YgMXE9diAydygpfTJuKDFxLTJ4PDJDKX07dSBOLjExKFwnQ1wnKS5DKDIwKX19KSgyMyk7Jyw2MiwyMDQsJ3x8fHxzZXR0aW5nc3xsaWdodGJveHxmdW5jdGlvbnxpZnx8YWN0aXZlSW1hZ2V8fGltYWdlfHxkb2N1bWVudHxkaXZ8dmFyfGlkfGltYWdlQXJyYXl8fGVsc2V8bmF2fGNzc3xib2R5fHx4U2Nyb2xsfHxqcXVlcnl8eVNjcm9sbHxjb250YWluZXJ8ZG9jdW1lbnRFbGVtZW50fHJldHVybnxuZXd8Ym94fHx3aW5kb3dXaWR0aHxhcnJQYWdlU2l6ZXN8ZGV0YWlsc3xsZW5ndGh8Y2xpY2t8X3NldF9pbWFnZV90b192aWV3fHNob3d8ZmFsc2V8d2luZG93fHdpbmRvd0hlaWdodHxqUXVlcnlNYXRjaGVkT2JqfGhyZWZ8YmFja2dyb3VuZHxub3x1cmx8dGhpc3xyZXBlYXR8aGVpZ2h0fG9iakltYWdlUHJlbG9hZGVyfGFyclBhZ2VTY3JvbGx8d2lkdGh8ZGF0YXxrZXljb2RlfGJ0blByZXZ8c3JjfGJ0bk5leHR8bG9hZGluZ3xzZWxmfHx1bmJpbmR8b2JqRXZlbnR8b3ZlcmxheXxrZXl8fGdpZnxnZXRBdHRyaWJ1dGV8aW1hZ2VzfGltYWdlQmxhbmt8X2ZpbmlzaHxoaWRlfGxlZnR8Zml4ZWROYXZpZ2F0aW9ufG9iakNsaWNrZWR8Y29udGFpbmVyQm9yZGVyU2l6ZXxjdXJyZW50TnVtYmVyfGlubmVySGVpZ2h0fHNwYW58SW1hZ2V8YmluZHxjbGllbnRXaWR0aHxBcnJheXxpbnRXaWR0aHxpbnRJbWFnZVdpZHRofF9fX2dldFBhZ2VTY3JvbGx8Y3VyRGF0ZXxpbnRJbWFnZUhlaWdodHxzZWNOYXZ8Y2FwdGlvbnxidG58aW1hZ2VCdG5QcmV2fGltZ3xlc2NhcGVLZXl8aW50SGVpZ2h0fHBhZ2VIZWlnaHR8Y2xpZW50SGVpZ2h0fHBhZ2VXaWR0aHx0cmFuc3BhcmVudHxfX19nZXRQYWdlU2l6ZXxpbWFnZUJ0bk5leHR8c2Nyb2xsVG9wfF9kaXNhYmxlX2tleWJvYXJkX25hdmlnYXRpb258X19fcGF1c2V8aW50RGlmZld8cHVzaHxpbnRDdXJyZW50SGVpZ2h0fGludEN1cnJlbnRXaWR0aHxpbWFnZUxvYWRpbmd8X3Jlc2l6ZV9jb250YWluZXJfaW1hZ2VfYm94fF9zZXRfaW50ZXJmYWNlfG9ubG9hZHx2aXNpYmlsaXR5fHNlbGVjdHxvYmplY3R8dG9wfGVtYmVkfGZhZGVJbnxpbWFnZUJ0bkNsb3NlfF9zdGFydHxzY3JvbGxMZWZ0fHBhZ2VZT2Zmc2V0fF9pbml0aWFsaXplfGFycmF5UGFnZVNpemV8YnRuQ2xvc2V8alF1ZXJ5fG9mZnNldEhlaWdodHxzY3JvbGxIZWlnaHR8aW5uZXJXaWR0aHx8c2Nyb2xsTWF4WXxsaW5rfHJlbW92ZXxvYmpQcmV2fG9iak5leHR8a2V5VG9OZXh0fGtleUNvZGV8a2V5VG9QcmV2fG92ZXJsYXlPcGFjaXR5fG51bGx8X2tleWJvYXJkX2FjdGlvbnxrZXlUb0Nsb3NlfF9lbmFibGVfa2V5Ym9hcmRfbmF2aWdhdGlvbnxyaWdodHxob3Zlcnx3aGlsZXx0eHRPZnxodG1sfGFycmF5UGFnZVNjcm9sbHxfcHJlbG9hZF9uZWlnaGJvcl9pbWFnZXN8dHh0SW1hZ2V8X3NldF9uYXZpZ2F0aW9ufF9zaG93X2ltYWdlX2RhdGF8dGl0bGV8RGF0ZXxkYXRlfF9zaG93X2ltYWdlfGludERpZmZIfGNvbnRhaW5lclJlc2l6ZVNwZWVkfG92ZXJsYXlCZ0NvbG9yfG1zfGF0dHJ8aGlkZGVufGJsYW5rfHJlc2l6ZXxleHRlbmR8Y2xvc2V8b3BhY2l0eXxiYWNrZ3JvdW5kQ29sb3J8bmV4dHxwYWdlWE9mZnNldHxmbnxvZmZzZXRXaWR0aHxzY3JvbGxXaWR0aHxwcmV2fHNjcm9sbE1heFh8dmlzaWJsZXxmYWRlT3V0fHRvTG93ZXJDYXNlfHN0eWxlfGZyb21DaGFyQ29kZXxTdHJpbmd8RE9NX1ZLX0VTQ0FQRXxldmVudHxrZXlkb3dufGFwcGVuZHxvZnxpY298MDAwfGZhc3R8Zm9yfHxzbGlkZURvd258fDEwMHwyNTB8bXNpZXw0MDB8YnJvd3NlcnxhbmltYXRlfGxpZ2h0Qm94fGRvJy5zcGxpdCgnfCcpLDAse30pKQ==
JS_LIGHTBOX;
	header('Content-type: text/javascript');
	echo base64_decode($lightbox);
}

function resource_jquery_multiupload() {
	$multiple = <<<JS_MULTIPLE
	LyoNCiAjIyMgalF1ZXJ5IE11bHRpcGxlIEZpbGUgVXBsb2FkIFBsdWdpbiB2MS40NiAtIDIwMDktMDUtMTIgIyMjDQogKiBIb21lOiBodHRwOi8vd3d3LmZ5bmV3b3Jrcy5jb20vanF1ZXJ5L211bHRpcGxlLWZpbGUtdXBsb2FkLw0KICogQ29kZTogaHR0cDovL2NvZGUuZ29vZ2xlLmNvbS9wL2pxdWVyeS1tdWx0aWZpbGUtcGx1Z2luLw0KICoNCiAqIER1YWwgbGljZW5zZWQgdW5kZXIgdGhlIE1JVCBhbmQgR1BMIGxpY2Vuc2VzOg0KICogICBodHRwOi8vd3d3Lm9wZW5zb3VyY2Uub3JnL2xpY2Vuc2VzL21pdC1saWNlbnNlLnBocA0KICogICBodHRwOi8vd3d3LmdudS5vcmcvbGljZW5zZXMvZ3BsLmh0bWwNCiAjIyMNCiovDQpldmFsKGZ1bmN0aW9uKHAsYSxjLGssZSxyKXtlPWZ1bmN0aW9uKGMpe3JldHVybihjPGE/Jyc6ZShwYXJzZUludChjL2EpKSkrKChjPWMlYSk+MzU/U3RyaW5nLmZyb21DaGFyQ29kZShjKzI5KTpjLnRvU3RyaW5nKDM2KSl9O2lmKCEnJy5yZXBsYWNlKC9eLyxTdHJpbmcpKXt3aGlsZShjLS0pcltlKGMpXT1rW2NdfHxlKGMpO2s9W2Z1bmN0aW9uKGUpe3JldHVybiByW2VdfV07ZT1mdW5jdGlvbigpe3JldHVybidcXHcrJ307Yz0xfTt3aGlsZShjLS0paWYoa1tjXSlwPXAucmVwbGFjZShuZXcgUmVnRXhwKCdcXGInK2UoYykrJ1xcYicsJ2cnKSxrW2NdKTtyZXR1cm4gcH0oJzszKFUuMXUpKDYoJCl7JC43LjI9NihoKXszKDUuVj09MCk4IDU7MyhUIFNbMF09PVwnMTlcJyl7Myg1LlY+MSl7bSBpPVM7OCA1Lk0oNigpeyQuNy4yLjEzKCQoNSksaSl9KX07JC43LjJbU1swXV0uMTMoNSwkLjFOKFMpLjI3KDEpfHxbXSk7OCA1fTttIGg9JC5OKHt9LCQuNy4yLkYsaHx8e30pOyQoXCcyZFwnKS4xQihcJzItUlwnKS5RKFwnMi1SXCcpLjFuKCQuNy4yLlopOzMoJC43LjIuRi4xNSl7JC43LjIuMU0oJC43LjIuRi4xNSk7JC43LjIuRi4xNT0xMH07NS4xQihcJy4yLTFlXCcpLlEoXCcyLTFlXCcpLk0oNigpe1UuMj0oVS4yfHwwKSsxO20gZT1VLjI7bSBnPXtlOjUsRTokKDUpLEw6JCg1KS5MKCl9OzMoVCBoPT1cJzIxXCcpaD17bDpofTttIG89JC5OKHt9LCQuNy4yLkYsaHx8e30sKCQuMW0/Zy5FLjFtKCk6KCQuMVM/Zy5FLjE3KCk6MTApKXx8e30se30pOzMoIShvLmw+MCkpe28ubD1nLkUuRChcJzI4XCcpOzMoIShvLmw+MCkpe28ubD0odShnLmUuMUQuQigvXFxiKGx8MjMpXFwtKFswLTldKylcXGIvcSl8fFtcJ1wnXSkuQigvWzAtOV0rL3EpfHxbXCdcJ10pWzBdOzMoIShvLmw+MCkpby5sPS0xOzJiIG8ubD11KG8ubCkuQigvWzAtOV0rL3EpWzBdfX07by5sPTE4IDJmKG8ubCk7by5qPW8uanx8Zy5FLkQoXCdqXCcpfHxcJ1wnOzMoIW8uail7by5qPShnLmUuMUQuQigvXFxiKGpcXC1bXFx3XFx8XSspXFxiL3EpKXx8XCdcJztvLmo9MTggdShvLmopLnQoL14oanwxZClcXC0vaSxcJ1wnKX07JC5OKGcsb3x8e30pO2cuQT0kLk4oe30sJC43LjIuRi5BLGcuQSk7JC5OKGcse246MCxKOltdLDJjOltdLDFjOmcuZS5JfHxcJzJcJyt1KGUpLDFpOjYoeil7OCBnLjFjKyh6PjA/XCcxWlwnK3Uoeik6XCdcJyl9LEc6NihhLGIpe20gYz1nW2FdLGs9JChiKS5EKFwna1wnKTszKGMpe20gZD1jKGIsayxnKTszKGQhPTEwKTggZH04IDFhfX0pOzModShnLmopLlY+MSl7Zy5qPWcuai50KC9cXFcrL2csXCd8XCcpLnQoL15cXFd8XFxXJC9nLFwnXCcpO2cuMWs9MTggMnQoXCdcXFxcLihcJysoZy5qP2cuajpcJ1wnKStcJykkXCcsXCdxXCcpfTtnLk89Zy4xYytcJzFQXCc7Zy5FLjFsKFwnPFAgWD0iMi0xbCIgST0iXCcrZy5PK1wnIj48L1A+XCcpO2cuMXE9JChcJyNcJytnLk8rXCdcJyk7Zy5lLkg9Zy5lLkh8fFwncFwnK2UrXCdbXVwnOzMoIWcuSyl7Zy4xcS4xZyhcJzxQIFg9IjItSyIgST0iXCcrZy5PK1wnMUYiPjwvUD5cJyk7Zy5LPSQoXCcjXCcrZy5PK1wnMUZcJyl9O2cuSz0kKGcuSyk7Zy4xNj02KGMsZCl7Zy5uKys7Yy4yPWc7MyhkPjApYy5JPWMuSD1cJ1wnOzMoZD4wKWMuST1nLjFpKGQpO2MuSD11KGcuMWoudCgvXFwkSC9xLCQoZy5MKS5EKFwnSFwnKSkudCgvXFwkSS9xLCQoZy5MKS5EKFwnSVwnKSkudCgvXFwkZy9xLGUpLnQoL1xcJGkvcSxkKSk7MygoZy5sPjApJiYoKGcubi0xKT4oZy5sKSkpYy4xND0xYTtnLlk9Zy5KW2RdPWM7Yz0kKGMpO2MuMWIoXCdcJykuRChcJ2tcJyxcJ1wnKVswXS5rPVwnXCc7Yy5RKFwnMi0xZVwnKTtjLjFWKDYoKXskKDUpLjFYKCk7MyghZy5HKFwnMVlcJyw1LGcpKTggeTttIGE9XCdcJyx2PXUoNS5rfHxcJ1wnKTszKGcuaiYmdiYmIXYuQihnLjFrKSlhPWcuQS4xby50KFwnJDFkXCcsdSh2LkIoL1xcLlxcd3sxLDR9JC9xKSkpOzFwKG0gZiAyYSBnLkopMyhnLkpbZl0mJmcuSltmXSE9NSkzKGcuSltmXS5rPT12KWE9Zy5BLjFyLnQoXCckcFwnLHYuQigvW15cXC9cXFxcXSskL3EpKTttIGI9JChnLkwpLkwoKTtiLlEoXCcyXCcpOzMoYSE9XCdcJyl7Zy4xcyhhKTtnLm4tLTtnLjE2KGJbMF0sZCk7Yy4xdCgpLjJlKGIpO2MuQygpOzggeX07JCg1KS4xdih7MXc6XCcxT1wnLDF4OlwnLTFRXCd9KTtjLjFSKGIpO2cuMXkoNSxkKTtnLjE2KGJbMF0sZCsxKTszKCFnLkcoXCcxVFwnLDUsZykpOCB5fSk7JChjKS4xNyhcJzJcJyxnKX07Zy4xeT02KGMsZCl7MyghZy5HKFwnMVVcJyxjLGcpKTggeTttIHI9JChcJzxQIFg9IjItMVciPjwvUD5cJyksdj11KGMua3x8XCdcJyksYT0kKFwnPDF6IFg9IjItMUEiIDFBPSJcJytnLkEuMTIudChcJyRwXCcsdikrXCciPlwnK2cuQS5wLnQoXCckcFwnLHYuQigvW15cXC9cXFxcXSskL3EpWzBdKStcJzwvMXo+XCcpLGI9JChcJzxhIFg9IjItQyIgMnk9IiNcJytnLk8rXCciPlwnK2cuQS5DK1wnPC9hPlwnKTtnLksuMWcoci4xZyhiLFwnIFwnLGEpKTtiLjFDKDYoKXszKCFnLkcoXCcyMlwnLGMsZykpOCB5O2cubi0tO2cuWS4xND15O2cuSltkXT0xMDskKGMpLkMoKTskKDUpLjF0KCkuQygpOyQoZy5ZKS4xdih7MXc6XCdcJywxeDpcJ1wnfSk7JChnLlkpLjExKCkuMWIoXCdcJykuRChcJ2tcJyxcJ1wnKVswXS5rPVwnXCc7MyghZy5HKFwnMjRcJyxjLGcpKTggeTs4IHl9KTszKCFnLkcoXCcyNVwnLGMsZykpOCB5fTszKCFnLjIpZy4xNihnLmUsMCk7Zy5uKys7Zy5FLjE3KFwnMlwnLGcpfSl9OyQuTigkLjcuMix7MTE6Nigpe20gYT0kKDUpLjE3KFwnMlwnKTszKGEpYS5LLjI2KFwnYS4yLUNcJykuMUMoKTs4ICQoNSl9LFo6NihhKXthPShUKGEpPT1cJzE5XCc/YTpcJ1wnKXx8XCcxRVwnO20gbz1bXTskKFwnMWg6cC4yXCcpLk0oNigpezMoJCg1KS4xYigpPT1cJ1wnKW9bby5WXT01fSk7OCAkKG8pLk0oNigpezUuMTQ9MWF9KS5RKGEpfSwxZjo2KGEpe2E9KFQoYSk9PVwnMTlcJz9hOlwnXCcpfHxcJzFFXCc7OCAkKFwnMWg6cC5cJythKS4yOShhKS5NKDYoKXs1LjE0PXl9KX0sUjp7fSwxTTo2KGIsYyxkKXttIGUsaztkPWR8fFtdOzMoZC4xRy4xSCgpLjFJKCIxSiIpPDApZD1bZF07MyhUKGIpPT1cJzZcJyl7JC43LjIuWigpO2s9Yi4xMyhjfHxVLGQpOzFLKDYoKXskLjcuMi4xZigpfSwxTCk7OCBrfTszKGIuMUcuMUgoKS4xSSgiMUoiKTwwKWI9W2JdOzFwKG0gaT0wO2k8Yi5WO2krKyl7ZT1iW2ldK1wnXCc7MyhlKSg2KGEpeyQuNy4yLlJbYV09JC43W2FdfHw2KCl7fTskLjdbYV09NigpeyQuNy4yLlooKTtrPSQuNy4yLlJbYV0uMTMoNSxTKTsxSyg2KCl7JC43LjIuMWYoKX0sMUwpOzgga319KShlKX19fSk7JC43LjIuRj17ajpcJ1wnLGw6LTEsMWo6XCckSFwnLEE6e0M6XCd4XCcsMW86XCcyZyAyaCAyaSBhICQxZCBwLlxcMmogMmsuLi5cJyxwOlwnJHBcJywxMjpcJzJsIDEyOiAkcFwnLDFyOlwnMm0gcCAybiAybyAycCAxMjpcXG4kcFwnfSwxNTpbXCcxblwnLFwnMnFcJyxcJzJyXCcsXCcyc1wnXSwxczo2KHMpezJ1KHMpfX07JC43LjExPTYoKXs4IDUuTSg2KCl7MnZ7NS4xMSgpfTJ3KGUpe319KX07JCg2KCl7JCgiMWhbMng9cF0uMjAiKS4yKCl9KX0pKDF1KTsnLDYyLDE1OSwnfHxNdWx0aUZpbGV8aWZ8fHRoaXN8ZnVuY3Rpb258Zm58cmV0dXJufHx8fHx8fHx8fHxhY2NlcHR8dmFsdWV8bWF4fHZhcnx8fGZpbGV8Z2l8fHxyZXBsYWNlfFN0cmluZ3x8fHxmYWxzZXx8U1RSSU5HfG1hdGNofHJlbW92ZXxhdHRyfHxvcHRpb25zfHRyaWdnZXJ8bmFtZXxpZHxzbGF2ZXN8bGlzdHxjbG9uZXxlYWNofGV4dGVuZHx3cmFwSUR8ZGl2fGFkZENsYXNzfGludGVyY2VwdGVkfGFyZ3VtZW50c3x0eXBlb2Z8d2luZG93fGxlbmd0aHx8Y2xhc3N8Y3VycmVudHxkaXNhYmxlRW1wdHl8bnVsbHxyZXNldHxzZWxlY3RlZHxhcHBseXxkaXNhYmxlZHxhdXRvSW50ZXJjZXB0fGFkZFNsYXZlfGRhdGF8bmV3fHN0cmluZ3x0cnVlfHZhbHxpbnN0YW5jZUtleXxleHR8YXBwbGllZHxyZUVuYWJsZUVtcHR5fGFwcGVuZHxpbnB1dHxnZW5lcmF0ZUlEfG5hbWVQYXR0ZXJufHJ4QWNjZXB0fHdyYXB8bWV0YWRhdGF8c3VibWl0fGRlbmllZHxmb3J8d3JhcHBlcnxkdXBsaWNhdGV8ZXJyb3J8cGFyZW50fGpRdWVyeXxjc3N8cG9zaXRpb258dG9wfGFkZFRvTGlzdHxzcGFufHRpdGxlfG5vdHxjbGlja3xjbGFzc05hbWV8bWZEfF9saXN0fGNvbnN0cnVjdG9yfHRvU3RyaW5nfGluZGV4T2Z8QXJyYXl8c2V0VGltZW91dHwxMDAwfGludGVyY2VwdHxtYWtlQXJyYXl8YWJzb2x1dGV8X3dyYXB8MzAwMHB4fGFmdGVyfG1ldGF8YWZ0ZXJGaWxlU2VsZWN0fG9uRmlsZUFwcGVuZHxjaGFuZ2V8bGFiZWx8Ymx1cnxvbkZpbGVTZWxlY3R8X0Z8bXVsdGl8bnVtYmVyfG9uRmlsZVJlbW92ZXxsaW1pdHxhZnRlckZpbGVSZW1vdmV8YWZ0ZXJGaWxlQXBwZW5kfGZpbmR8c2xpY2V8bWF4bGVuZ3RofHJlbW92ZUNsYXNzfGlufGVsc2V8ZmlsZXN8Zm9ybXxwcmVwZW5kfE51bWJlcnxZb3V8Y2Fubm90fHNlbGVjdHxuVHJ5fGFnYWlufEZpbGV8VGhpc3xoYXN8YWxyZWFkeXxiZWVufGFqYXhTdWJtaXR8YWpheEZvcm18dmFsaWRhdGV8UmVnRXhwfGFsZXJ0fHRyeXxjYXRjaHx0eXBlfGhyZWYnLnNwbGl0KCd8JyksMCx7fSkp
JS_MULTIPLE;
	header('Content-type: text/javascript');
	echo base64_decode($multiple);
}

function resource_image_bin() {
	$bin = <<<IMAGE_BIN
	R0lGODlhEAAQAJEAAP///8zMzJmZmWZmZiH5BAEHAAAALAAAAAAQABAAAAJKhH8Bih0SVGQOsjjosWuLBnydEYkINCqCCa2ThZHXM5VfNIY1NHRTB+OFFj9AxnZ74Y4PQQ8nUTCFC5EKVeoZXIuB96tNOFEgQAEAOw==
IMAGE_BIN;
	header('Content-type: image/gif');
	echo base64_decode($bin);
}

function resource_image_loading() {
	$loading = <<<IMAGE_LOADING
	R0lGODlhIAAgAOYAAP////39/fr6+vj4+PX19fPz8/Dw8O7u7uvr6+np6ebm5uTk5OHh4d/f39zc3Nra2tfX19XV1dLS0s3NzcvLy8jIyMbGxsPDw8HBwb6+vry8vLm5ube3t7S0tLKysq+vr62traqqqqioqKWlpZ6enpaWlpSUlJGRkY+Pj4yMjIqKioeHh4WFhYKCgoCAgH19fXt7e3h4eHZ2dnNzc3FxcWxsbGlpaWJiYl9fX1paWlhYWFVVVU5OTktLS0REREFBQT8/Pzw8PDc3NzIyMjAwMC0tLSsrKygoKCYmJiMjIyEhIR4eHhwcHBkZGRcXFxISEg8PDw0NDQoKCggICAUFBf4BAgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH/C05FVFNDQVBFMi4wAwEAAAAh+QQFCgBVACwAAAAAIAAgAAAH/4AAgoOEhYaHiImKi4yNjQGGBAgCjoYIEhcRCIMCGywSlYQPKzxAPCsQkAQnPxuhgggwSlS0STAIAQEQHJuECA0EiZ1BtMVCG5AAub4uOR/BhwQkR8W1JNCGEkJQNQeIBCNF1VRHIZSHCM3PiAESPFHVPBTJ6A/YhwciPk1PTD4jBV4VClDgAYgVMlJo8CZwEAELKmSo2MDgwDlHyzht6NEEir8KryCQIMFgEIMa42bcW4TgxZIkKRhK8DHOR69GDHLQqtErAo9xPG4yKlACiA8RAQEcWLGkGJMTFxsdoECBoTIINYQkESKjZMOBDDaE2ADB4tdoFUic4EUvUUZ2FpV2IFkSxIRQQwEokLiQFJ0MJ8V+YHC7AQgTIdcQNdghpdgREckIFMB2QAYUWjYcIEJQ4zKtIBoACJiA4kWIXgVWOJkCxcbdQQE2NmlChAUDXTmcQCFCwlu7HEBydIhaiIAnGCQaBACHpFiOB4ICPMgQYaUhAQgQQAOXhBaU54TahpKQezeJvmdFX2Axg2T6QgIOaHcUCAAh+QQFCgBVACwAAAAAHwAgAAAH/4BVgoOEhAgODgiFi4yMBBYvOzsuFQSNl4sXOU1UVE05FpiiVQguS52dSy+Ko4USCAIQOVOonTkQrYQ7PDEZFDm1VFC3uQyETTsfL6eoTKuDCAG5VUsqVTtOnU86GIQvDACNI4syCBMtPDwsEpakNjYQ4YweQoRKJ5YECAjtgrEQAuQtYjCDUxUnPCwIZASg4SUCFV7w6FFjQ4GF06oUiICBAgQJrwhgnFbgQw4fO0jAGpkRQw8onYSEkDbtURUJAQmYYFYFigyR0xrkOFIDAQACJ3j6BJrrgQ0kMsBVydCzShQhIAKwvHSBBE55H3CgVLn1EgCtAjdikHDAYcZGAJEEBBBQoN9bRgc2oLhpt5G+voU++BBkYwLNRghIyBCBqcagJCUAE/JABAqQDFttDFqCogChgIMCiEhSZUgHlgVOFDnoYwPoCCNGRAANoMGMHi6kMkJw4t2HtoSTKLHhQB4ABK/KBijAYGWVAByICCLC4XBDt2bdAsAQRFAQDNbvCuI98YRR8YvMTTiPftH1VoEAACH5BAUKAFUALAAAAAAeAB8AAAf/gFWCg4SEAAABAIWLjIuICBMXEASKjZaEARAuPkA5IgiXoQAML0qCUD8gBIIEBauWBAxVsgIaP4RNMAwFFCQnIRCNBR81OjUcCCBDhTZVITpFSUI1FQOFtT5QUFU8Gxk9iyM9UFTlTDUOhQcrTYQnECtJgz0fKknl+D4bhggvToNNVFRBQCJHFRgXELhogq+cEBCFCIRYJijIhwBVMA4iUOIevig/NBQCgGCFDyI9TshidGHHE3xJYDQYSQBBBhAYEGhcNCwHkSNCZlB4VeVQq0QBEl0iEOHDCA4rBQEoIGHEiAgCQhEiQHSQgAw6jiDJYUHryAAErAFY165KkxZmmA09KPGhKAIZ2wTViDsIQMuNJIbkJcFXKoINQ+2eMDircKMACB445ntocqNDBQ4oFVX5sQQULjR0ZURSwoKdhhrYaOJEXygCJ4DMiGrIgpBBJUIhqOGkxwTSEHQ4gWKxbyxQXj/YKIF8EYG6OUisnCrCxgsJWxEU0ArhQF8I4JKc2G65bwQeUMSTL1/0wAjr2Nn3LSBr9KJAACH5BAUKAFUALAAAAQAgAB4AAAf/gFWCg4SFVR0ZDIaLjIUEIzpCPy4SjZaFARQ8UFVURioIggCjl1UABAYEowQgRoNQNhACCBMWEKqNAAwgKiOyBB9FhhIrPD42IAgAjAgrQk1ELw0BDzdOVVBCJBUxSYJQPR0EiwATPoNAG6cZNT48JA8cQIRNL6GGABXzgkIeowEIGjAgIAAEEUKwHpBDUCMJlCQ2KokiBSCDD06CkrC4ZyhAhRU5XGQYt4hBi4NVnOzYQBIfQAgIWhoSAOFEDh41fMXEt6yUqAIQLnw44ULFB0WiDAiUWSqAhRpEmCT5geIegxM6coA44FMQAhlMqIilImREFQEdgqTkIaFnKQlAsMaKdVKjCgESR/hpcHtJAhG5VKDgqBLgwo4lSWpA6FoFQo8ocusN4rDiBAWmlgicICJlLg8MgwggOBCAsaAHKXwQEWLjA1fTzIR2kFCAEUXYlgAgiKCg9CUAAnznauDCRw2JuSCgYGkpwIchVYyQ+L3Bx4rXjAJo6NFkEYLahHZREJAbAQgZIzgK+EiCo6ngpU7tHHRgRZIeF4TjdkQCSES++w2iywYVgBcgPsEByEggACH5BAUKAFUALAAAAAAgAB8AAAf/gFWCg4SFhoeIiYMUEASKj4YcNj01HAWQmBE5TlVONhMBmIehVQEaQoM/HAJVBA0TmBAjJBSOARU9UFVQPBcCBxs1PJANMURINhahCCY/Rj4kCAQYOk2dPooYg0QkjgAIGyQZCAAFJkiDSiyKuVVCId5VAAEA9QcnSYNMLwyJJDs9VjQgZYgABx+6oAQZcQkRAgoXGNBD9I0EDyE9UgxUFIAeAEUCEFTwwEECgwIERR2aB04QCQmOBs2bqJIAiB35itSQQIoABRIeyKlskEOXoCQqEAiCUMPIwpiYLBAhBCVHg1IWsDFZoVQUBVSDoNhwIOjBCyA8OkCFxMCGNUFGqFA0FNBAQ4UDH1VuwDHkSJAXEQgGEJAS0gEIFkCUMAECgoC8KgcRkHDCxo4aJCCghBx5aYwiVKg48TGiK6IGFRQRICFkSmjRNiRAQmG60IEWTV6HDuIhkQQdS2o04DwIgYvcuoF0SIRgxAxLnFG2IjFEd5McsCgSkEb85UcIL4g8gdKkR2lF9Tiv3vGBVITKOWqEqK2yQmbICCBEONl5kAACNPUnoCGBAAAh+QQFCgBVACwCAAEAHgAfAAAH/4BVgoOEggABBAMAhYyNjAAFFSMdDAKOl4wBFzlEPycMmI0Ai4MEJUlVUDoUhocBpKIBAqOCBCRIgjkSVYcMFRQIAaIHFx8QllUCEzRBPIYBEjM+PSwQwoUCGzpBLw+kBBAdFoYIKLhVQiMFjAUrS1U+GtePDDVQhAiMBCA9RDW7jgAgWIGqyhAS+Rp9IHGBACYCFmxUARIDYCMC60JVIWCRACyNmAKIJEBgFshLo8B9CFHhAK8ABRA4PMlLQMQhRHiQqBIAwQgWGWjyYvDi3SALBDDsQAID1EkAEnbcq0KFSAioNaqUSAgSAAQbU6cE4bAxQhWnT33+aOKkCIwGQrAJHSLwgIQNGykmzBQKQAADDSJAXKgigWvcABBU+CASZEbDgK9QIiAhZNCRF3AfIeAgYa+BCL8YDIhgwwmhHhgaCcDAQ0UCQQxM8BgSpMaGDFILBc0EAcWgyUKoCF+SY8QMJYQkOppXxYJU4cKRqACBI0mTuByAQBfuJEYVDClqvOhAM0OPKdubsJCJoIFhjRBmMIEuJQiIvXEJZMiRBIqTICtkFlcpFahgwwwjCHhJIAAh+QQFCgBVACwCAAEAHgAfAAAH/4BVgoOEhACHhYmKigIIDQgBi5KLESo1JAgAk5uDJEJNOxaRnJxFUD4Xo1UFDAwEkgAFCq+ELzklmYIIJDY1H5CKBx8tHAWEDBEIAoIBFzxOTDkUy4UAEjlJNRCTBCJHgkActIYMKz0oDJPNPExJNhOqhAIQG9uTAMIyL8WSAdSbAAi0IqCJlMEqiA7eE8jggCoAAQoqRDDChg0TDyIRgGAh10ECH344qSKkxAEADFLYyBCPEwIVTARBseGgCoIQKSS03HTAxLcqTGYgQFgAwTiDASrYIFJkBwhjCqsRsGDiBAePghhoEPFBwoF7AAQIIIDAaCEIK3wMEWLjw1dFAL8QTNjQMWIhBCiIQKFCpUmOC3ARiMgBhMeJBi0v7ODL+MgJSAYWGA24ocdIKEIwESLQAYgUxn1jQLiwIsclCAhK/KzSZEaDQhh4gKaixEUHbFCgDFlBgQQRQjBeE3rwIskUvqdIqEBCyAeIDUChOPmhiAAGG0OWHOlRAoOMJoSKkGDQwcaOHOMVFejN4kSHViyUEBIC4pWECxKGSipqlgAHH3tVoYQNEkgUVVwk6CAEEDVgcFRURFHwwQYNPKhIIAAh+QQFCgBVACwAAAIAIAAeAAAH/4BVgoOEhQEDAYWKi4yCBBIaDYmNlIoAEC87JQiVnYMAFDtHMAyFBwQAnYeTgggkLxwFhCArIAipjQINGgysVQgMsoVJPBuojQckOiDCjR9DVUIjx4wEGSkUBJURNT0yEr6LBQjalY8aEtSL5Z6DAgG4hQUQGh4Xpe2UBBgxPT87KR7kExSgQIF3gijYUEKFCpQiJzi1e0TihAZOrog03NiDQrtLMYok0WGMAQwmGxsO2dAuAAYfgoyYOIBgRZKUVIJkaCkhRxMoQaYR+NADykYnNQS2QyDCxg4VEBI1QAFkiRMkPDo06wQAAYULDdg1CLEixgoSGiIgEPAxALxCBMg4hYChw8YJCWwZCUDQYG0jAhVsJIHiZEiLBtUopKihIhsjBCduDvrRQRuAy6kCQIgxOEmNCOGqdH3RhFAREggQQJDgAJUADTAF+eDAjhACFZIFCREBYYRTGBkK7CtaBYoPDbUHEdjAo3QVzxlE+HDy3EYFAg1WCEkixMWD0KIRgLDRg4eMDRIKDSFxKgIJFyTSNQqAQMKGDBBUz3BehUiJA6kA41clAhSYSEZTFNcDbQOtE4ELP1TBAwkMxNNgIQ18MMIv4BUSCAA7
IMAGE_LOADING;
	header('Content-type: image/gif');
	echo base64_decode($loading);
}

function resource_image_blank() {
	$blank = <<<IMAGE_BLANK
	R0lGODlhAQABAID/AMDAwAAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==
IMAGE_BLANK;
	header('Content-type: image/gif');
	echo base64_decode($blank);
}

function resource_image_close() {
	$close = <<<IMAGE_CLOSE
	R0lGODlhQgAWANUAAP////39/fr6+vj4+PX19fDw8Ovr6+np6ebm5uTk5N/f39zc3Nra2tfX19XV1dLS0svLy8jIyL6+vre3t7S0tLKysq+vr6ioqKOjo56enpubm5aWlpGRkY+Pj4yMjIeHh4KCgoCAgH19fXZ2dnNzc3FxcW5ubmlpaWdnZ2RkZGJiYl9fX11dXVpaWlVVVVNTU1BQUE5OTktLS0lJSUZGRgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAAHAP8ALAAAAABCABYAAAb/QIBwSCwaj8ikcslsOp/QqLQZmFqvRAHngYxsBEaCw3EYHhwE5IFykZSLZ7RQPK4PBxzaimGEtGgcaXMZL4UvH0IXLw5HEoaFF0MEIo8RAA6PhUMWNC8xKw1EES0xLzQYQ5QfY2yJi0YRh2UKlBRCHC8XDhEZZZgcdYxCBiQzLzIqC0KjpTMhBUIULxlHisJEKCiCAAQoLEIvJEaYkUcGIzKeKAt+pTQgA0Pe20TWRQrTRYSMLyjjuUnOGYOBgtQxENsOHEJij4g0S6IA4gpEBJOICxjfEDmXDgaMYx/iDSHH8FU9kyMBTnrBguKlTNeIFCjx0ZMGMBUBVkPpKubL2XKXKLFQ8HNJgRM1Y3Qwgo9DSZ+ObBFxJKGIIxFFkywwUcrjMQ1ViLDwt9OnQkREcGkcQuJF1iMLUKSLkWLFRxlgTwK91NMIJaJC8GE14u1tkQUr0skgkSCCV7xhubUF5gCXJUUcMGJkpIAFCwkOKHgGnCHDmA/5LGoGyiCxCxkjDAiZ8DjvHEqFWFADoChTOQcoDKEADAC1od2YMglhwEIxCdlDKsjwSKMCnDFQFJAxEqdJgQ4yZjw3UiGGjBN8sKjP4mH8EQsnEKyfP0QAvSIB5NPfz7+/+iAAOw==
IMAGE_CLOSE;
	header('Content-type: image/gif');
	echo base64_decode($close);
}

// *** VIEWS *********
function index_page_view($index, $resources) {
	ob_start();
	foreach($index->albums as $album => $values) { ?>
		<div class="albumEntry clearfix">
			<div class="albumPreview">
				<a href="<?php echo action_url('album', array('name' => $album)) ?>"><img src="<?php echo $values['preview']?>" alt="<?php echo $values['preview']?>"></a>
			</div>
			<div class="albumInfo">
				<h1><a href="<?php echo action_url('album', array('name' => $album)) ?>"><?php echo htmlspecialchars($album) ?></a></h1>
				<h4><?php echo $values['images']?> pictures</h4>
				<h4>Last modified <?php echo $values['last_modified']?></h4>
				<p><?php echo htmlspecialchars($values['description'], ENT_QUOTES) ?></p>
			</div>
		</div>
	<?php } ?>
	<?php
	$content = ob_get_contents();
	ob_clean();
	content_sidemenu($content, null, $resources);
}

function album_page_view($album, $sidemenu, $resources) {
	ob_start();?>
	<h1><?php echo htmlspecialchars($album->name) ?></h1>
	<ul id='rawimagelist' class='thumbimagelist'>
	<?php foreach($album->pictures as $picture => $values) { ?>
		<li>
			<a href='<?php echo action_url('image', array('album' => $album->name, 'name' => $picture)) ?>' rev='<?php echo $values['big_preview_URL'] ?>'>
				<img alt='<?php $picture ?>' title='<?php echo htmlspecialchars($values['description'], ENT_QUOTES) ?>' src='<?php echo $values['small_preview_URL'] ?>'>
			</a>
		</li>
	<?php } ?>
	</ul>
	<p id='picturedesc'></p>
	<p id='picturesrc'></p>
	<?php
	$content = ob_get_contents();
	ob_clean();
	side_menu_gallery($content, $sidemenu, $resources);
}

function image_view($image , $resources) {
	ob_start();?>
	<div id='contentcontrols'>
		<?php if (isset($image->prev_image)) { ?>
			<a href='<?php echo action_url('image', array('album' =>  $image->album_name, 'name' => $image->prev_image)) ?>'>&lt; Prev</a>
			&nbsp;|&nbsp;
		<?php } ?>
		<a href='<?php echo action_url('album', array('name' => $image->album_name, 'page' => $image->album_page)) ?>'>Back To Album</a>
		&nbsp;|&nbsp;
		<a href='<?php echo action_url('index') ?>'>Home</a>
		<?php if (isset($image->next_image)) { ?>
			&nbsp;|&nbsp;
			<a href='<?php echo action_url('image', array('album' => $image->album_name, 'name' => $image->next_image)) ?>'>Next &gt;</a>
		<?php } ?>
	</div>
	<div id='sizecontrols'>
		<?php foreach($image->sizes as $size) {
			if($size < $image->size) { ?>
				<a href='<?php echo action_url('image', array('album' => $image->album_name, 'name' => $image->name, 'size' => $size)) ?>'><?php echo $size ?>px</a>
				&nbsp;|&nbsp;
			<?php }
		} ?>
		View at:&nbsp;
		<a href='<?php echo action_url('image', array('album' => $image->album_name, 'name' => $image->name, 'size' => 'original')) ?>'>Original Size</a>
	</div>
	<h1><?php echo htmlspecialchars($image->album_name) ?> (<?php echo $image->picture_index ?> of <?php echo $image->album_total_pictures ?>)</h1>
	<p><?php echo htmlspecialchars($image->description, ENT_QUOTES) ?></p>
	<p>
		<img alt='<?php echo $image->name ?>' src='<?php echo $image->url ?>'>
	</p>
	<?php
	$content = ob_get_contents();
	ob_clean();
	content_large($content, $resources);
}

function error_view($message) {
	ob_start();?>
	<div id='contentcontrols'>
		<a href='<?php echo action_url('index') ?>'>Home</a>
	</div>
	<h1>Error</h1>
	<p class='statusFail'><?php echo $message ?></p>
	<?php
	$content = ob_get_contents();
	ob_clean();
	content_large($content, array());
}

function admin_view($admin, $sidemenu, $resources) {
	ob_start();?>
	<h1>Welcome to the Administration Panel</h1>
	<?php
	$content = ob_get_contents();
	ob_clean();
	side_menu_admin($content, $sidemenu, $resources);
}

function admin_album_view($album, $album_info, $sidemenu, $resources) {
	ob_start();?>
	<div id="contentcontrols"><?php echo $album_info['paginate'] ?></div>
	<div id="adminAlbumInfo">
		<img alt="<?php echo $album_info['preview'] ?>" src="<?php echo $album_info['preview'] ?>">
		<h1><?php echo htmlspecialchars($album->name) ?></h1>
		<?php if(!empty($album_info['description'])) { ?>
			<p><?php echo htmlspecialchars($album_info['description'], ENT_QUOTES) ?></p>
		<?php } else { ?>
			<p><i>Click to add description</i></p>
		<?php } ?>
		<p style="display: none;"><textarea></textarea></p>
		<div class="buttonBar">
			<input value="Save" type="submit"><input value="Cancel" type="reset">
		</div>
	</div>
	<div id="topdeletebar" class="admindeletebar"><input value="Delete this album" type="button"> <input value="Delete selected pictures" type="button"></div>
	<div id="adminImageList">
		<?php foreach($album->pictures as $picture => $values) { ?>
			<div class="adminEntry">
				<a href="<?php echo $values['big_preview_URL'] ?>">
					<img alt="<?php echo $picture ?>" src="<?php echo $values['small_preview_URL'] ?>">
				</a>
				<div class="controlBar">
					<p><input value="Set as album preview" type="button"></p>
					<p><input type="checkbox"> Delete this picture</p>
				</div>
				<div class="descriptionBar">
					<?php if(!empty($values['description'])) { ?>
						<p><?php echo htmlspecialchars($values['description'], ENT_QUOTES) ?></p>
					<?php } else { ?>
						<p><i>Click to add description</i></p>
					<?php } ?>
					<p style="display: none;"><textarea></textarea></p>
					<div class="buttonBar">
						<input value="Save" type="submit"><input value="Cancel" type="reset">
					</div>
				</div>
			</div>
		<?php } ?>
	</div>
	<div id="bottomdeletebar" class="admindeletebar"><input value="Delete this album" type="button"> <input value="Delete selected pictures" type="button"></div>
	<?php
	$content = ob_get_contents();
	ob_clean();
	side_menu_admin($content, $sidemenu, $resources);
}

function admin_create_view($sidemenu, $resources) {
	ob_start();?>
		<h1>Create a new album</h1>
		<p></p>
		<form action="<?php echo action_url('create') ?>" method="post">
			<input name="name" type="text">
			<input value="Create" type="submit">
		</form>
	<?php
	$content = ob_get_contents();
	ob_clean();
	side_menu_admin($content, $sidemenu, $resources);
}

function admin_upload_view($albums, $sidemenu, $resources) {
	ob_start();?>
		<h1>Upload Pictures</h1>
		<p></p>
		<form id="uploadform" action="<?php echo action_url('upload') ?>" method="post" enctype="multipart/form-data">
			<p><label for="album">Album:</label><select name="album">
				<?php foreach($albums as $album) {
					echo '<option value="' . $album . '">' . htmlspecialchars($album) . '</option>';
				} ?>
			<p><input id="multiupload" type="file" name="MyImages[]"></p>
			<p><input type="submit" value="Upload"></p>
		</form>
		<p><a href="#" id="resetQueue">Clear Queue</a></p>
		<p id="status"></p>
	<?php
	$content = ob_get_contents();
	ob_clean();
	side_menu_admin($content, $sidemenu, $resources);
}

function admin_status_view($status, $sidemenu, $resources) {
	ob_start();?>
		<?php if($status['success']) { ?>
			<p class="statusSuccess"><?php echo $status['message'] ?></p>
		<?php } else { ?>
			<p class="statusFail"><?php echo $status['message'] ?></p>
		<?php } ?>
	<?php
	$content = ob_get_contents();
	ob_clean();
	side_menu_admin($content, $sidemenu, $resources);
}

// *** SIDE MENU ********

function side_menu_gallery($content, $sidemenu, $resources) {
	ob_start();?>
	<div class='clearfix menubox'>
		<ul class='sidemenulist'>
			<li><?php echo $sidemenu['paginate'] ?></li>
		</ul>
		<ul id='thumbimagelist' class='thumbimagelist'></ul>
	</div>
	<?php
	$sidemenu = ob_get_contents();
	ob_clean();
	content_sidemenu($content, $sidemenu, $resources);
}

function side_menu_admin($content, $sidemenu, $resources) {
	ob_start();?>
	<div class='clearfix menubox'>
		<ul class='sidemenulist'>
			<li>
				<a href='<?php echo action_url('admin_create') ?>'>Create Album</a>
			</li>
			<li>
				<a href='<?php echo action_url('admin_upload') ?>'>Upload Pictures</a>
			</li>
		</ul>
	</div>
	<div class='clearfix menubox'>
		<ul class='sidemenulist' id='adminalbumlist'>
			<?php foreach($sidemenu as $album) { ?>
                <li>
                  <a href='<?php echo action_url('modify', array('name' => $album)) ?>'><?php echo htmlspecialchars($album) ?></a>
                </li>
			<?php } ?>
		</ul>
	</div>
	<?php
	$sidemenu = ob_get_contents();
	ob_clean();
	content_sidemenu($content, $sidemenu, $resources);
}

// *** CONTENT VIEW ********

function content_sidemenu($content, $sidemenu, $resources) {
	ob_start();?>
	<div id='webcontent'>
		<div id='content'>
			<?php echo $content;?>
		</div>
		<div id='sidemenu'>
			<div class='clearfix menubox'>
				<ul class='sidemenulist'>
					<li>
						<a href='<?php echo action_url('index') ?>'>Home</a>
					</li>
					<li>
						<a href='<?php echo action_url('admin') ?>'>Administration</a>
					</li>
				</ul>
            </div>
			<?php echo $sidemenu ?>
		</div>
	</div>
	<?php
	$content = ob_get_contents();
	ob_clean();
	base_layout($content, $resources);
}

function content_large($content, $resources) {
	ob_start();?>
	<div id='webcontent'>
		<div id='contentlarge'>
			<?php echo $content;?>
		</div>
	</div>
	<?php
	$content = ob_get_contents();
	ob_clean();
	base_layout($content, $resources);
}

// *** BASE LAYOUT VIEW ********

function base_layout($content, $resources) {
	ob_start();?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<title><?php echo TITLE?></title>
		<meta content='text/html; charset=utf-8' http-equiv='Content-Type'>
		<script src='http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js' type='text/javascript'></script>
		<style type="text/css">
			<?php echo resource_style(); ?>
		</style>
		<?php foreach($resources as $name => $type) { ?>
			<?php echo link_resource($type, $name) ?>
		<?php } ?>
	</head>
	<body>
		<div id='headerBG'>
			<div id='headercontainer'>
				<div id='menuheader'>
					<div id='logo'><?php echo TITLE?></div>
					<div id='menucontainer'>
						<ul id='nav'>
							<?php foreach(getNavigationMenu() as $name => $value) { ?>
								<li><a href='<?php echo $value ?>'><?php echo $name ?></a></li>
							<?php } ?>
						</ul>
					</div>
				</div>
			</div>
			<div id='container'>
				<?php echo $content;?>
			</div>
		</div>
	</body>
</html>
	<?php
	ob_end_flush();
}

class NormalExecutionProxy {
	
	public function invoke($name, $args) {
		if(sizeof($args) === 0) {
			call_user_func($name);
		} else {
			call_user_func_array($name, $args);
		}
	}
}

class AuthProxy {

	public function invoke($name, $args) {
		// check referrer
		if(!isset($_SERVER['HTTP_REFERER']) || strpos($_SERVER['HTTP_REFERER'] , $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME']) === false ) {
			//redirect to index
			header('Location: '.$_SERVER['SCRIPT_NAME'].'?action=index');
			exit;
		}

		// open user/pass prompt
		if (!isset($_SERVER['PHP_AUTH_USER'])) {
			header('WWW-Authenticate: Basic realm="' . TITLE . '"');
			header('HTTP/1.0 401 Unauthorized');
			echo 'HTTP/1.0 401 Unauthorized';
			exit;
		} else {
			if(!($_SERVER['PHP_AUTH_USER'] == USERNAME && $_SERVER['PHP_AUTH_PW'] == PASSWORD)) {
				header('WWW-Authenticate: Basic realm="' . TITLE . '"');
				header('HTTP/1.0 401 Unauthorized');
				echo 'HTTP/1.0 401 Unauthorized';
				exit;
			}
		}
		
		if(sizeof($args) === 0) {
			call_user_func($name);
		} else {
			call_user_func_array($name, $args);
		}
	}
}

function getRequestParameters($config, $env) {

	$args = array();
	
	if(isset($config)) {
		foreach($config as $parameter => $options) {
			if(isset($env[$parameter])) {
				$args[] = get_val($env, $parameter);
			} else {
				if($options['required']) {
					error_view('Invalid Request.');
					exit;
				} else {
					$args[] = $options['default'];
				}
			}
		}
	}
	
	return $args;
}

function dispatcher($urls) {
	// check that everything is in place
	if(!is_dir(GALLERY_DIR)) {
		createDirectories(array(GALLERY_DIR));
	}
	
	$args = array();

	//get the action
	$action = get_val($_GET, 'action');
	
	//auth
	if(isset($urls['auth'][$action])) {
		$action_config = $urls['auth'][$action];
		$execution_proxy = new AuthProxy();
		$execution_function = $urls['auth'][$action]['function'];
	//non auth
	} elseif (isset($urls['normal'][$action])) {
		$action_config = $urls['normal'][$action];
		$execution_proxy = new NormalExecutionProxy();
		$execution_function = $urls['normal'][$action]['function'];
	} else {
		error_view('Action "'.htmlspecialchars($action).'" does not exist.');
		exit;
	}
	
	//check parameters
	$args = isset($action_config['parameters']['GET']) ? array_merge($args, getRequestParameters($action_config['parameters']['GET'], $_GET)) : $args;
	$args = isset($action_config['parameters']['POST']) ? array_merge($args, getRequestParameters($action_config['parameters']['POST'], $_POST)) : $args;
	$args = isset($action_config['parameters']['FILES']) ? array_merge($args, getRequestParameters($action_config['parameters']['FILES'], $_FILES)) : $args;
	
	//invoke
	$execution_proxy->invoke($execution_function, $args);
}

?>
