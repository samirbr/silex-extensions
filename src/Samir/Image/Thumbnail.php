<?php
namespace Samir\Image;

class Thumbnail 
{
	/**
	 * Width of thumbnail in pixels
	 */
	protected $thumbWidth;
	
	/**
	 * Height of thumbnail in pixels
	 */
	protected $thumbHeight;
	
	/**
	 * Temporary file if the source is not local
	 */
	protected $tempFile = null;
	
	/**
	 * Thumbnail constructor
	 *
	 * @param int (optional) max width of thumbnail
	 * @param int (optional) max height of thumbnail
	 * @param boolean (optional) if true image scales
	 * @param boolean (optional) if true inflate small images
	 * @param string (optional) adapter class name
	 * @param array (optional) adapter options
	 */
	public function __construct($maxWidth = null, $maxHeight = null, $scale = true, $inflate = true, $quality = 75, $adapterClass = null, $adapterOptions = array())
	{
    if (!$adapterClass) {
      if (extension_loaded('gd')) {
				$this->adapter = new GDAdapter($maxWidth, $maxHeight, $scale, $inflate, $quality, $adapterOptions);
			} else {
				$this->adapter = new ImageMagickAdapter($maxWidth, $maxHeight, $scale, $inflate, $quality, $adapterOptions);
			}
		}
	}
	
	/**
	 * Loads an image from a file or URL and creates an internal thumbnail out of it
	 *
	 * @param string filename (with absolute path) of the image to load. If the filename is a http(s) URL, then an attempt to download the file will be made.
	 *
	 * @return boolean True if the image was properly loaded
	 * @throws Exception If the image cannot be loaded, or if its mime type is not supported
	 */
	public function loadFile($image)
	{
		if (!is_readable($image)) {
			throw new \Exception(sprintf('The file "%s" is not readable.', $image));
		}
	
		$this->adapter->loadFile($this, $image);
	}
	
	/**
	 * Loads an image from a string (e.g. database) and creates an internal thumbnail out of it
	 *
	 * @param string the image string (must be a format accepted by imagecreatefromstring())
	 * @param string mime type of the image
	 *
	 * @return boolean True if the image was properly loaded
	 * @access public
	 * @throws Exception If image mime type is not supported
	 */
	public function loadData($image, $mime)
	{
		$this->adapter->loadData($this, $image, $mime);
	}
	
	/**
	 * Saves the thumbnail to the filesystem
	 * If no target mime type is specified, the thumbnail is created with the same mime type as the source file.
	 *
	 * @param string the image thumbnail file destination (with absolute path)
	 * @param string The mime-type of the thumbnail (possible values are 'image/jpeg', 'image/png', and 'image/gif')
	 *
	 * @access public
	 * @return void
	 */
	public function save($thumbDest, $targetMime = null)
	{
		return $this->adapter->save($this, $thumbDest, $targetMime);
	}
  
  public function smartSave($thumbDest, $targetMime = null)
	{
		$this->adapter->smartSave($this, $thumbDest, $targetMime);
	}
	
	/**
	 * Returns the thumbnail as a string
	 * If no target mime type is specified, the thumbnail is created with the same mime type as the source file.
	 *
	 *
	 * @param string The mime-type of the thumbnail (possible values are adapter dependent)
	 *
	 * @access public
	 * @return string
	 */
	public function toString($targetMime = null)
	{
		return $this->adapter->toString($this, $targetMime);
	}
	
	public function toResource()
	{
		return $this->adapter->toResource($this);
	}
	
	public function freeSource() 
	{
		if (!is_null($this->tempFile)) {
			unlink($this->tempFile);
		}
		$this->adapter->freeSource();
	}
	
	public function freeThumb()
	{
		$this->adapter->freeThumb();
	}
	
	public function freeAll()
	{
		$this->adapter->freeSource();
		$this->adapter->freeThumb();
	}
	
	/**
	 * Returns the width of the thumbnail
	 */
	public function getThumbWidth()
	{
		return $this->thumbWidth;
	}
	
	/**
	 * Returns the height of the thumbnail
	 */
	public function getThumbHeight()
	{
		return $this->thumbHeight;
	}
	
	/**
	 * Returns the mime type of the source image
	 */
	public function getMime()
	{
		return $this->adapter->getSourceMime();
	}
	
	/**
	 * Computes the thumbnail width and height
	 * Used by adapter
	 */
	public function initThumb($sourceWidth, $sourceHeight, $maxWidth, $maxHeight, $scale, $inflate)
	{
		if ($maxWidth > 0) {
			$ratioWidth = $maxWidth / $sourceWidth;
		}
		 
		if ($maxHeight > 0) {
			$ratioHeight = $maxHeight / $sourceHeight;
		}
	
		if ($scale) {
			if ($maxWidth && $maxHeight) {
				$ratio = ($ratioWidth < $ratioHeight) ? $ratioWidth : $ratioHeight;
			}
			
			if ($maxWidth xor $maxHeight) {
				$ratio = (isset($ratioWidth)) ? $ratioWidth : $ratioHeight;
			}
			
			if ((!$maxWidth && !$maxHeight) || (!$inflate && $ratio > 1)) {
				$ratio = 1;
			}
	
			$this->thumbWidth = floor($ratio * $sourceWidth);
			$this->thumbHeight = ceil($ratio * $sourceHeight);
		} else {
			if (!isset($ratioWidth) || (!$inflate && $ratioWidth > 1)) {
				$ratioWidth = 1;
			}
			
			if (!isset($ratioHeight) || (!$inflate && $ratioHeight > 1)) {
				$ratioHeight = 1;
			}
			
			$this->thumbWidth = floor($ratioWidth * $sourceWidth);
			$this->thumbHeight = ceil($ratioHeight * $sourceHeight);
		}
	}
	
	public function __destruct()
	{
		$this->freeAll();
	}
}
