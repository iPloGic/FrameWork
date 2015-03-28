<?php

/*

*** Images Works Class
*** Is a part of iPloGic FrameWork 1.x
*** Version 1.0

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
	private $filigree_oposition = 30;
	private $result_ext = 'png';

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
			$this->source_width = $width;
			$this->source_height = $height;
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
		elseif ( $m == 'direct_ratio' ) { $this->ratio_meth = 3; return true; }
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

	public function AddFiligree($filename, $xmargin, $ymargin, $xmargin_pos = 'left', $ymargin_pos = 'top', $oposition = 30) {
		$this->filigree = new IMAGE();
		if ( $this->filigree->CreateFromFile($filename) ) {
			$this->filigree_x_margin = $xmargin;
			$this->filigree_y_margin = $ymargin;
			$this->filigree_x_margin_pos = $xmargin_pos;
			$this->filigree_y_margin_pos = $ymargin_pos;
			$this->filigree_oposition = $oposition;
			return true;
		}
		$this->filigree = false;
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

	public function SaveImage($savepath) {
		$this->CreateResult();
		if ($this->result) {
			switch ($this->result_ext) {
				case 'png': imagepng($this->result,$savepath); break;
				case 'jpg': imagejpeg($this->result,$savepath); break;
				case 'gif': imagegif($this->result,$savepath); break;
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
				imagecopymerge($this->result, $this->filigree->GetWorkObject(), $this->filigree_x_margin, $this->filigree_y_margin, 0, 0, $this->filigree->GetSourceWidth(), $this->filigree->GetSourceHeight(), $this->filigree_oposition);
			}
			return true;
		}
		return false;
	}

	public function Destroy() {
		if ( $this->source ) { imageDestroy( $this->source ); }
		if ( $this->filigree ) { imageDestroy( $this->filigree ); }
		if ( $this->result ) { imageDestroy( $this->result ); }
		return true;
	}

}


?>