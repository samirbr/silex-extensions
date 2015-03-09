<?php
namespace Samir\Image;

class GDAdapter 
{
	protected
	$sourceWidth,
	$sourceHeight,
	$sourceMime,
	$maxWidth,
	$maxHeight,
	$scale,
	$inflate,
	$quality,
	$source,
	$thumb,
  $file;
	
	/**
	 * List of accepted image types based on MIME
	 * descriptions that this adapter supports
	 */
	protected $imgTypes = array(
			'image/jpeg',
			'image/pjpeg',
			'image/png',
			'image/gif',
	);
	
	/**
	 * Stores function names for each image type
	 */
	protected $imgLoaders = array(
			'image/jpeg'  => 'imagecreatefromjpeg',
			'image/pjpeg' => 'imagecreatefromjpeg',
			'image/png'   => 'imagecreatefrompng',
			'image/gif'   => 'imagecreatefromgif',
	);
	
	/**
	 * Stores function names for each image type
	 */
	protected $imgCreators = array(
			'image/jpeg'  => 'imagejpeg',
			'image/pjpeg' => 'imagejpeg',
			'image/png'   => 'imagepng',
			'image/gif'   => 'imagegif',
	);
	
	public function __construct($maxWidth, $maxHeight, $scale, $inflate, $quality, $options)
	{
		if (!extension_loaded('gd')) {
			throw new \Exception ('GD not enabled. Check your php.ini file.');
		}
		
		$this->maxWidth = $maxWidth;
		$this->maxHeight = $maxHeight;
		$this->scale = $scale;
		$this->inflate = $inflate;
		$this->quality = $quality;
		$this->options = $options;
	}
	
	public function _loadFile($thumbnail, $image)
	{
		$imgData = @GetImageSize($image);
	
		if (!$imgData){
			throw new \Exception(sprintf('Could not load image %s', $image));
		}
	
		if (in_array($imgData['mime'], $this->imgTypes)) {
			$loader = $this->imgLoaders[$imgData['mime']];
			
			if(!function_exists($loader)) {
				throw new \Exception(sprintf('Function %s not available. Please enable the GD extension.', $loader));
			}
	
			$this->source = $loader($image);
			$this->sourceWidth = $imgData[0];
			$this->sourceHeight = $imgData[1];
			$this->sourceMime = $imgData['mime'];
			
			$thumbnail->initThumb($this->sourceWidth, $this->sourceHeight, $this->maxWidth, $this->maxHeight, $this->scale, $this->inflate);
	
			$this->thumb = imagecreatetruecolor($thumbnail->getThumbWidth(), $thumbnail->getThumbHeight());
			
			if ($imgData[0] == $this->maxWidth && $imgData[1] == $this->maxHeight) {
				$this->thumb = $this->source;
			} else {
				//imagecopyresampled($this->thumb, $this->source, 0, 0, 0, 0, $thumbnail->getThumbWidth(), $thumbnail->getThumbHeight(), $imgData[0], $imgData[1]);
        $this->keepTransparency($this->source, $this->thumb, $imgData['mime']); 
			}
	
			return true;
		} else {
			throw new \Exception(sprintf('Image MIME type %s not supported', $imgData['mime']));
		}
	}
	
	public function loadData($thumbnail, $image, $mime)
	{
		if (in_array($mime,$this->imgTypes)) {
			
			$this->source = imagecreatefromstring($image);
			$this->sourceWidth = imagesx($this->source);
			$this->sourceHeight = imagesy($this->source);
			$this->sourceMime = $mime;
			
			$thumbnail->initThumb($this->sourceWidth, $this->sourceHeight, $this->maxWidth, $this->maxHeight, $this->scale, $this->inflate);
	
			$this->thumb = imagecreatetruecolor($thumbnail->getThumbWidth(), $thumbnail->getThumbHeight());
			
			if ($this->sourceWidth == $this->maxWidth && $this->sourceHeight == $this->maxHeight) {
				$this->thumb = $this->source;
			} else {
				//imagecopyresampled($this->thumb, $this->source, 0, 0, 0, 0, $thumbnail->getThumbWidth(), $thumbnail->getThumbHeight(), $this->sourceWidth, $this->sourceHeight);
         $this->keepTransparency($this->source, $this->thumb, $mime); 
			}
	
			return true;
		} else {
			throw new \Exception(sprintf('Image MIME type %s not supported', $mime));
		}
	}
  
  protected function keepTransparency($source_img, $dest_img, $mime)
  {
    if ($mime == 'image/png' || $mime == 'image/gif') {
      $transparentIndex = @imagecolortransparent($source_img);
      
      if ($transparentIndex >= 0) {
        // Get the original image's transparent color's RGB values
        $trnprt_color = imagecolorsforindex($source_img, $transparentIndex);
  
        // Allocate the same color in the new image resource
        $transparentIndex = imagecolorallocate($dest_img, $trnprt_color['red'], $trnprt_color['green'], $trnprt_color['blue']);
  
        // Completely fill the background of the new image with allocated color.
        imagefill($dest_img, 0, 0, $transparentIndex);
  
        // Set the background color for new image to transparent
        imagecolortransparent($dest_img, $transparentIndex);          
      } elseif ($mime == 'image/png') {
        // Turn off transparency blending (temporarily)
        imagealphablending($dest_img, false);
  
        // Create a new transparent color for image
        $color = imagecolorallocatealpha($dest_img, 0, 0, 0, 127);
        // Completely fill the background of the new image with allocated color.
        imagefill($dest_img, 0, 0, $color);
  
        // Restore transparency blending
        imagesavealpha($dest_img, true);
      }        
    } 
  }
  
  public function loadFile($thumbnail, $filename)
  {
    $this->file = $filename;
  }
  
  public function save($thumbnail, $thumb_name, $mime, $proportional = true)
  {
    $width = $this->maxWidth;
    $height = $this->maxHeight;
    
    if ( $height <= 0 && $width <= 0 ) {
      return false;
    }

    $info = getimagesize($this->file);
    $image = '';

    $final_width = 0;
    $final_height = 0;
    list($width_old, $height_old) = $info;

    if ($proportional) {
      if ($width == 0) $factor = $height/$height_old;
      elseif ($height == 0) $factor = $width/$width_old;
      else $factor = min ( $width / $width_old, $height / $height_old);   

      $final_width = round ($width_old * $factor);
      $final_height = round ($height_old * $factor);

    } else {
      $final_width = ( $width <= 0 ) ? $width_old : $width;
      $final_height = ( $height <= 0 ) ? $height_old : $height;
    }
    
    switch ( $info[2] ) {
      case IMAGETYPE_GIF:
        $image = imagecreatefromgif($this->file);
      break;
      case IMAGETYPE_JPEG:
        $image = imagecreatefromjpeg($this->file);
      break;
      case IMAGETYPE_PNG:
        $image = imagecreatefrompng($this->file);
      break;
      default:
        return false;
    }
    
    $image_resized = imagecreatetruecolor( $final_width, $final_height );
        
    if ( ($info[2] == IMAGETYPE_GIF) || ($info[2] == IMAGETYPE_PNG) ) {
      $trnprt_indx = imagecolortransparent($image);
   
      // If we have a specific transparent color
      if ($trnprt_indx >= 0) {
   
        // Get the original image's transparent color's RGB values
        $trnprt_color    = imagecolorsforindex($image, $trnprt_indx);
   
        // Allocate the same color in the new image resource
        $trnprt_indx    = imagecolorallocate($image_resized, $trnprt_color['red'], $trnprt_color['green'], $trnprt_color['blue']);
   
        // Completely fill the background of the new image with allocated color.
        imagefill($image_resized, 0, 0, $trnprt_indx);
   
        // Set the background color for new image to transparent
        imagecolortransparent($image_resized, $trnprt_indx);
   
      
      } 
      // Always make a transparent background color for PNGs that don't have one allocated already
      elseif ($info[2] == IMAGETYPE_PNG) {
   
        // Turn off transparency blending (temporarily)
        imagealphablending($image_resized, false);
   
        // Create a new transparent color for image
        $color = imagecolorallocatealpha($image_resized, 0, 0, 0, 127);
   
        // Completely fill the background of the new image with allocated color.
        imagefill($image_resized, 0, 0, $color);
   
        // Restore transparency blending
        imagesavealpha($image_resized, true);
      }
    }

    imagecopyresampled($image_resized, $image, 0, 0, 0, 0, $final_width, $final_height, $width_old, $height_old);
    
    switch ( $info[2] ) {
      case IMAGETYPE_GIF:
        imagegif($image_resized, $thumb_name);
      break;
      case IMAGETYPE_JPEG:
        imagejpeg($image_resized, $thumb_name);
      break;
      case IMAGETYPE_PNG:
        imagepng($image_resized, $thumb_name);
      break;
      default:
        return false;
    }

    return true;
  }
	
	public function _save($thumbnail, $thumbDest, $targetMime = null)
	{
		if($targetMime !== null) {
			$creator = $this->imgCreators[$targetMime];
		} else {
			$creator = $this->imgCreators[$thumbnail->getMime()];
		}
	
		if ($creator == 'imagejpeg') {
			imagejpeg($this->thumb, $thumbDest, $this->quality);
		} else {
      $creator($this->thumb, $thumbDest);
		}
	}
	
	public function toString($thumbnail, $targetMime = null)
	{
		if ($targetMime !== null) {
			$creator = $this->imgCreators[$targetMime];
		} else {
			$creator = $this->imgCreators[$thumbnail->getMime()];
		}
	
		ob_start();
		$creator($this->thumb);
	
		return ob_get_clean();
	}
	
	public function toResource()
	{
		return $this->thumb;
	}
	
	public function freeSource()
	{
		if (is_resource($this->source)) {
			imagedestroy($this->source);
		}
	}
	
	public function freeThumb()
	{
		if (is_resource($this->thumb)) {
			imagedestroy($this->thumb);
		}
	}
	
	public function getSourceMime()
	{
		return $this->sourceMime;
	}
}
