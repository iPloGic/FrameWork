<?php

/*

*** Images Works Class
*** Is a part of iPloGic FrameWork 1.x
*** Version 1.1

*** Copyright (C) 2013 iPloGic, LLC. All rights reserved.
*** License GNU/GPL http://www.iplogic.ru/licenses/gpl/
*** Link http://www.iplogic.ru, info@iplogic.ru.

*** Manual page http://www.iplogic.ru/framework/manual/images/

*/


class IMAGE
{
	private $source = false;
	private $source_width = 0;
	private $source_height = 0;
	private $result = false;
	private $result_width = 0;
	private $result_height = 0;
	private $enclose_width = 0;
	private $enclose_height = 0;
	private $resize_meth = 1;
	private $ratio = 1;
	private $filigree = false;
	private $filigree_x_margin = 0;
	private $filigree_y_margin = 0;
	private $filigree_x_margin_pos = 'left';
	private $filigree_y_margin_pos = 'top';
	private $filigree_transparency = 30;
	private $result_ext = 'png';
	private $antialias = false;

	public function CreateFromFile($filename) {
		$idata = @getimagesize($filename);
		$this->source_width = $idata[0];
		$this->source_height = $idata[1];
		$ext = $idata[2];
		if( $ext == '2' ) { $this->source = @imagecreatefromjpeg($filename); return true; }
		elseif( $ext == '1' ) { $this->source = @imagecreatefromgif($filename); return true; }
		elseif( $ext == '3' ) { $this->source = @imagecreatefrompng($filename); return true; }
		return false;
	}

	public function CreateBlank($width, $height) {
		if ($this->source = @imagecreatetruecolor($width, $height)) {
			imageantialias($this->source, true);
			$this->antialias = true;
			$this->source_width = $width;
			$this->source_height = $height;
			$this->resize_meth = 3;
			return true;
		}
		else {
			return false;
		}
	}

	public function GetWorkObject() {
		if ( $this->source ) { return $this->source; }
		return false;
	}

	public function GetSourceWidth() {
		if ( $this->source ) { return $this->source_width; }
		return false;
	}

	public function GetSourceHeight() {
		if ( $this->source ) { return $this->source_height; }
		return false;
	}

	public function SetResizeMethod($m) {
		if ( $m == 'enclosed' ) { $this->resize_meth = 1; return true; }
		elseif ( $m == 'resize' ) { $this->resize_meth = 2; return true; }
		elseif ( $m == 'direct_ratio' ) { $this->resize_meth = 3; return true; }
		else { return false; }
	}

	public function SetRatio($ratio) {
		if ( $ratio > 0 ) {
			$this->ratio = 1/$ratio;
			return true;
		}
		else {
			return false;
		}
	}

	public function SetSizes($width, $height) {
		if ( $width > 0 && $height > 0 ) {
			$this->enclose_width = $width;
			$this->enclose_height = $height;
			return true;
		}
		else {
			return false;
		}
	}

	public function SetResultExtension($ext) {
		$e = Array('png','jpg','gif');
		if ( in_array($ext,$e) ) {
			$this->result_ext = $ext;
			return true;
		}
		else {
			return false;
		}
	}

	public function AddFiligree($filename, $xmargin, $ymargin, $xmargin_pos = 'left', $ymargin_pos = 'top', $transparency = 30) {
		$this->filigree = new IMAGE();
		if ( $this->filigree->CreateFromFile($filename) ) {
			$this->filigree_x_margin = $xmargin;
			$this->filigree_y_margin = $ymargin;
			$this->filigree_x_margin_pos = $xmargin_pos;
			$this->filigree_y_margin_pos = $ymargin_pos;
			$this->filigree_transparency = $transparency;
			return true;
		}
		$this->filigree = false;
		return false;
	}

	public function AntiAliasing($enabled) {
		imageantialias($this->source, $enabled);
		$this->antialias = $enabled;
		return true;
	}

	public function Point($x, $y) {
		return new POINT(round($x), round($y));
	}

	public function Fill($color) {		$c = $this->ColorRecource($color);
		imagefill($this->source, 0, 0, $c);
		imagecolordeallocate ($this->source , $c);
		return true;	}

	public function Pixel($color, $point) {		$c = $this->ColorRecource($color);
		imagesetpixel($this->source, $point->x, $point->y, $c);
		imagecolordeallocate ($this->source , $c);
		return true;	}

	public function Line($color, $point1, $point2, $thickness = 1) {		$c = $this->ColorRecource($color);
		if ($thickness == 1) {
			imageline($this->source, $point1->x, $point1->y, $point2->x, $point2->y, $c);
		}
		elseif ($point1->x == $point2->x || $point1->y == $point2->y) {
			$t = $thickness / 2 - 0.5;
			imagefilledrectangle($image, round(min($point1->x, $point2->x) - $t), round(min($point1->y, $point2->y) - $t), round(max($point1->x, $point2->x) + $t), round(max($point1->y, $point2->y) + $t), $c);
		}
		else {
			$k = ($point2->y - $point1->y) / ($point2->x - $point1->x);
			$a = $t / sqrt(1 + pow($k, 2));
			$points = Array(
				round($point1->x - (1+$k)*$a), round($point1->y + (1-$k)*$a), round($point1->x - (1-$k)*$a), round($point1->y - (1+$k)*$a),
				round($point2->x + (1+$k)*$a), round($point2->y - (1-$k)*$a), round($point2->x + (1-$k)*$a), round($point2->y + (1+$k)*$a)
			);
			imagefilledpolygon($this->source, $points, 4, $c);
			imagepolygon($this->source, $points, 4, $c);
		}
		imagecolordeallocate ($this->source , $c);
		return true;	}

	public function Rectangle($color, $point1, $point2, $fill = false) {
		$c = $this->ColorRecource($color);
		if ( $fill ) {
			imagefilledrectangle($this->source, $point1->x, $point1->y, $point2->x, $point2->y, $c);
		}
		else {			imagerectangle($this->source, $point1->x, $point1->y, $point2->x, $point2->y, $c);		}
		imagecolordeallocate ($this->source , $c);
		return true;
	}

	public function Polygon($color, $points, $fill = false) {
		$c = $this->ColorRecource($color);
		$num = count($points);
		if ( $num < 3 ) { return false; }
		$coords = Array();
		foreach ( $points as $point ) { $coords[] = $point->x; $coords[] = $point->y; }
		if ( $fill ) {
			imagefilledpolygon($this->source, $coords, $num, $c);
		}
		else {
			imagepolygon($this->source, $coords, $num, $c);
		}
		imagecolordeallocate ($this->source , $c);
		return true;
	}

	public function Ellipse($color, $center, $width, $height = 0, $fill = false) {
		$c = $this->ColorRecource($color);
		if ( $height == 0 ) { $height = $width; }
		if ( $fill ) {
			imagefilledellipse($this->source, $center->x, $center->y, $width, $height, $c);
		}
		else {
			imageellipse($this->source, $center->x, $center->y, $width, $height, $c);
		}
		imagecolordeallocate ($this->source , $c);
		return true;
	}

	public function Arc($color, $center, $start, $end, $width, $height = 0, $fill = false) {
		$c = $this->ColorRecource($color);
		if ( $height == 0 ) { $height = $width; }
		if ( $fill ) {
			imagearc($this->source, $center->x, $center->y, $width, $height, $start, $end, $c);
			$a1 = $this->ArcPoint($center, $width, $height, $start);
			$p1 = new POINT($a1[0], $a1[1]);
			$a2 = $this->ArcPoint($center, $width, $height, $end);
			$p2 = new POINT($a2[0], $a2[1]);
			$aa = $this->antialias;
			$this->AntiAliasing(false);
			$this->Line($color, $p1, $p2);
			$a3 = $this->ArcPoint($center, ($width-4), ($height-4), (($start+$end)/2));
			$p3 = new POINT($a3[0], $a3[1]);
			$this->FillÑontour($color, $color, $p3);
			$this->AntiAliasing(false);
		}
		else {
			imagearc($this->source, $center->x, $center->y, $width, $height, $start, $end, $c);
		}
		imagecolordeallocate ($this->source , $c);
		return true;
	}

	public function Sector($color, $center, $start, $end, $width, $height = 0, $fill = false) {
		$c = $this->ColorRecource($color);
		if ( $height == 0 ) { $height = $width; }
		if ( $fill ) {
			imagefilledarc($this->source, $center->x, $center->y, $width, $height, $start, $end, $c, IMG_ARC_PIE);
		}
		else {
			imagefilledarc($this->source, $center->x, $center->y, $width, $height, $start, $end, $c, IMG_ARC_NOFILL|IMG_ARC_EDGED);
		}
		imagecolordeallocate ($this->source , $c);
		return true;
	}

	public function Text($text, $color, $point, $font, $size = 14, $angle = 0, $transparency = 0) {		$c = $this->ColorRecource($color, $transparency);
		$ret = imagettftext ($this->source, $size, $angle, $point->x, $point->y, $c, $font, $text);
		imagecolordeallocate ($this->source , $c);
		return true;	}

	public function FillÑontour($color, $contour_color, $point) {		$c = $this->ColorRecource($color);
		$cc = $this->ColorRecource($contour_color);
		imagefilltoborder($this->source, $point->x, $point->y, $cc, $c);
		imagecolordeallocate ($this->source , $c);
		imagecolordeallocate ($this->source , $cc);
		return true;	}

	public function SaveImage($filename) {
		$this->CreateResult();
		if ($this->result) {
			switch ($this->result_ext) {
				case 'png': imagepng($this->result,$filename); break;
				case 'jpg': imagejpeg($this->result,$filename); break;
				case 'gif': imagegif($this->result,$filename); break;
			}
			return true;
		}
		return false;
	}

	public function ShowImage() {
		$this->CreateResult();
		if ($this->result) {
			switch ($this->result_ext) {
				case 'png': imagepng($this->result); break;
				case 'jpg': imagejpeg($this->result); break;
				case 'gif': imagegif($this->result); break;
			}
			return true;
		}
		return false;
	}

	public function Destroy($filigree = false) {
		if ( $this->source ) { imageDestroy( $this->source ); }
		if ( $this->filigree && $filigree ) { imageDestroy( $this->filigree ); }
		if ( $this->result ) { imageDestroy( $this->result ); }
		return true;
	}

	private function CreateResult() {
		if ( $this->source ) {
			if ( $this->resize_meth == 1 ) {
				$this->CalkRatio();
				$this->result_width = $this->source_width/$this->ratio;
				$this->result_height = $this->source_height/$this->ratio;
			}
			if ( $this->resize_meth == 2 ) {
				$this->result_width = $this->enclose_width;
				$this->result_height = $this->enclose_height;
			}
			if ( $this->resize_meth == 3 ) {
				$this->result_width = $this->source_width/$this->ratio;
				$this->result_height = $this->source_height/$this->ratio;
			}
			$this->result = imagecreatetruecolor($this->result_width, $this->result_height);
			$white = ImageColorAllocate($this->result, 255,255,255);
			imagefill($this->result, 1, 1, $white);
			imagecopyresampled($this->result, $this->source, 0, 0, 0, 0, $this->result_width, $this->result_height, $this->source_width, $this->source_height);
			if ( $this->filigree ) {
				if ( $this->filigree_x_margin_pos == 'right' ) {
					$this->filigree_x_margin = $this->result_width - $this->filigree->GetSourceWidth() - $this->filigree_x_margin;
				}
				if ( $this->filigree_y_margin_pos == 'bottom' ) {
					$this->filigree_y_margin = $this->result_height - $this->filigree->GetSourceHeight() - $this->filigree_y_margin;
				}
				imagecopymerge($this->result, $this->filigree->GetWorkObject(), $this->filigree_x_margin, $this->filigree_y_margin, 0, 0, $this->filigree->GetSourceWidth(), $this->filigree->GetSourceHeight(), $this->filigree_transparency);
			}
			return true;
		}
		return false;
	}

	private function CalkRatio() {
		(double)$ratiow=(double)$this->source_width/ (double)$this->enclose_width;
		(double)$ratioh=(double)$this->source_height/ (double)$this->enclose_height;
		if( $ratiow < 1 && $ratioh < 1 ) {
			if( $ratiow > $ratioh ) { $this->ratio = $ratiow; }
			else { $this->ratio = $ratioh; }
		}
		elseif( $ratiow > 1 && $ratioh > 1 ) {
			if( $ratiow < $ratioh ) { $this->ratio = $ratioh; }
			else { $this->ratio = $ratiow; }
		}
		elseif( $ratiow > 1 && $ratioh == 1 ) {
			$this->ratio = $ratiow;
		}
		elseif( $ratiow < 1 && $ratioh ==1 ) {
			$this->ratio = $ratioh;
		}
		elseif( $ratiow >= 1 && $ratioh < 1 ) {
			$this->ratio = $ratiow;
		}
		elseif( $ratiow <= 1 && $ratioh > 1 ) {
			$this->ratio = $ratioh;
		}
		elseif( $ratiow == 1 && $ratioh == 1 ) {
			$this->ratio = 1;
		}
	}

	private function ArcPoint($center, $width, $height, $angle) {
		$a = $width/2 - 0.5;
		$b = $height/2 - 0.5;
		$fi = (round(360-$angle)%360)*(3.14/180);
		$x = 250 + ($a*cos($fi));
		$y = 200 - ($b*sin($fi));
		return Array($x, $y);
	}

	private function ColorRecource($color, $alpha = 0) {
		$rgb = FUNC::HexToRgb($color);
		if ( $alpha == 0 ) {
			return imagecolorallocate($this->source, $rgb['red'], $rgb['green'], $rgb['blue']);
		}
		else {			$a = round((127/100)*$alpha);
			return imagecolorallocatealpha($this->source, $rgb['red'], $rgb['green'], $rgb['blue'], $a);		}
	}

}


class POINT
{	public $x;
	public $y;

	function __construct($x, $y) {		$this->x = $x;
		$this->y = $y;	}
}


?>