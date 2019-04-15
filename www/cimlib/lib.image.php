<?php
class Image
{
	var $type;
	var $width;
	var $height;
	var $backgroundColor;
	
	function Image($type, $width, $height)
	{
		$this->type = $type;
		$this->width = $width;
		$this->height = $height;
	}
	
	function create()
	{
		$this->handle = @imagecreate($this->width, $this->height);
		@imagecolorallocate($this->handle, 251, 251, 251);
		return $this->handle;
	}
}

class CryptImage extends Image
{
	var $handle;
	var $fonts;
	var $bg;
	
	function CryptImage($type, $width, $height)
	{
		$this->handle = NULL;
		$this->fonts = array();
		$this->Image($type, $width, $height);
	}
	
	function apply($effect)
	{
		$effect->apply($this);
	}
	function setFonts ($f)
	{
		$this->fonts = $f;
	}
	
	function render($quality = 100)
	{
		header("Content-type: image/" . $this->type);
		@imageinterlace($this->handle, 1);
		@imagepng($this->handle, NULL, $quality);
		@imagedestroy($this->handle);
	}
}

class CryptPng extends CryptImage
{
	function CryptPng($width, $height)
	{
		$this->CryptImage('PNG', $width, $height);
	}
	
	function createFrom($src)
	{
		return ($this->handle = @imagecreatefrompng($src));
	}
	
	function render()
	{
		header("Content-type: image/" . $this->type);
		@imageinterlace($this->handle, 1);
		@imagepng($this->handle);
		@imagedestroy($this->handle);
	}
}

class CryptJpeg extends CryptImage
{
	function CryptJpeg( $width, $height)
	{
		$this->CryptImage('JPG', $width, $height);
	}
	
	function createFrom($src)
	{
		return ($this->handle = @imagecreatefromjpeg($src));
	}
	
	function render($quality = 100)
	{
		header("Content-type: image/" . $this->type);
		@imageinterlace($this->handle, 1);
		@imagejpeg($this->handle, NULL, $quality);
		@imagedestroy($this->handle);
	}
}
class CryptGif extends CryptImage
{
	function CryptGif($width, $height)
	{
		$this->CryptImage('GIF', $width, $height);
	}
	
	function createFrom($src)
	{
		return ($this->handle = @imagecreatefromgif($src));
	}
	
	function render($quality = 100)
	{
		header("Content-type: image/" . $this->type);
		@imageinterlace($this->handle, 1);
		@imagegif($this->handle, NULL, $quality);
		@imagedestroy($this->handle);
	}
}

class Effect
{
	function apply($image)
	{
		die('---');
	}
}

class GridEffect extends Effect
{
	function GridEffect($size, $r, $g, $b)
	{
		$this->size = $size;
		$this->r = $r;
		$this->g = $g;
		$this->b = $b;
	}
	
	function apply($image)
	{
		$bg = imagecolorallocate($image->handle, $this->r, $this->g, $this->b);
		for($i = 0, $x = 0, $z = $image->width; $i < $image->width; $i++, $z -= $this->size, $x += $this->size)
		{
			@imageline($image->handle, $x, 0, $x + 10, $image->height, $bg);
			@imageline($image->handle, $z, 0, $z - 10, $image->height, $bg);
		}
	}
}

class DotEffect extends Effect
{
	function apply($image)
	{
		for($i = 0; $i < $image->width; $i++)
		{
			imagesetpixel ( $image->handle, rand(0, $image->width), rand(0, $image->height), @imagecolorallocate($image->handle, rand(0, 255), rand(0, 255), rand(0, 255)));
		}
	}
}

class GradientEffect extends Effect
{
	function GradientEffect($r, $g, $b)
	{
		$this->r = $r;
		$this->g = $g;
		$this->b = $b;
	}
	
	function apply($image)
	{
		for($i = 0, $rd = $this->r, $gr = $this->g, $bl = $this->b; $i <= $image->height; $i++)
		{
			$bg = @imagecolorallocate($image->handle, $rd += 3, $gr += 3, $bl += 3);
			@imageline($image->handle, 0, $i, $image->width, $i, $bg);
		}
		$image->backgroundColor = $bg;
	}
}

class TextEffect extends Effect
{
	var $text;
	var $size;
	var $depth;
	var $fonts;
	
	function TextEffect($text, $size, $depth = 1, $color, $color2)
	{
		$this->text = $text;
		$this->size = $size;
		$this->depth = $depth;
		$this->fonts = array();
		$this->color = $color;
		$this->color2 = $color2;
	}
	
	function addFont($path)
	{
		if(file_exists($path))
		{
			$this->fonts[] = realpath($path);
		}
	}
	
	function apply($image)
	{
		$c = @imagecolorallocate($image->handle, $this->color[0], $this->color[1], $this->color[2]);
		$c2 = @imagecolorallocate($image->handle, $this->color2[0], $this->color2[1], $this->color2[2]);
		$width = $image->width;
		$height = $image->height;
		$text = strtoupper($this->text);
		$text = strtoupper($this->text);
		$this->fonts = $image->fonts;
		$charCount = count($this->fonts);
		for($i = 0, $strlen = strlen($this->text), $p = floor(abs((($width - ($this->size * $strlen)) / 2) - floor($this->size / 2))); $i < $strlen; $i++, $p += $this->size)
		{
			$f = $_SERVER['DOCUMENT_ROOT'].'/cimlib/'.$this->fonts[rand(0, sizeof($this->fonts) - 1)];
			$d = rand(-20, 20);
			$y = rand(floor($height / 2) + floor($this->size / 2), $height - floor($this->size / 2));
			for($b = 0; $b <= $this->depth; $b++)
			{
				imagettftext($image->handle, $this->size, $d, $p++, $y++, $c, $f, $this->text{$i});
			}
			@imagettftext($image->handle, $this->size, $d, $p, $y, $c2, $f, $this->text{$i});
		}
	}
}
?>