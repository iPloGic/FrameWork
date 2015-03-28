<?php

/*

*** Pagination Works Class
*** Is a part of iPloGic IPLF FrameWork 1.x
*** Version 1.0

*** Copyright (C) 2013 iPloGic, LLC. All rights reserved.
*** License GNU/GPL http://www.iplogic.ru/licenses/gpl/
*** Link http://www.iplogic.ru, info@iplogic.ru.

*** Manual page http://www.iplogic.ru/framework/manual/pagination/

*/


class PAGINATION
{
	public $onpage = 20;
	public $page = 1;
	public $start = 0;
	public $rel_canonical = true;
	private $type=1;
	private $left_pn=3;
	private $middle_pn=5;
	private $right_pn=3;
	private $template_prev_block="<a href='<[base_url]><[sect]>p=1/' title='Первая'><<</a> <a href='<[base_url]><[sect]>p=<[prev]>/' title='Предыдущая'><</a> ";
	private $template_next_block="<a href='<[base_url]><[sect]>p=<[next]>/' title='Следующая'>></a> <a href='<[base_url]><[sect]>p=<[pages_num]>/' title='Последняя'>>></a>";
	private $template_prev_block_na="<< < ";
	private $template_next_block_na="> >>";
	private $template_current_position='<b><[position]></b> ';
	private $template_position="<a href='<[base_url]><[sect]>p=<[position]>'><[position]></a> ";
	private $space = "... ";

	function __construct() {
		if ( FRAME_CORE::Parameter('pagination_position') != 1 && FRAME_CORE::Parameter('pagination_position') != '' ) {
			$this->page = FRAME_CORE::Parameter('pagination_position');
			$this->start = abs(( $this->page - 1 ) * $this->onpage );
		}
		return true;
	}

	public function SetOnPage($a) {
		$this->onpage = $a;
		$this->start = abs(( $this->page - 1 ) * $this->onpage );
		return true;
	}

	public function SetPType($a) {
		$this->type = $a;
		return true;
	}

	public function SetLeftPositions($a) {
		$this->left_pn = $a;
		return true;
	}

	public function SetRightPositions($a) {
		$this->right_pn = $a;
		return true;
	}

	public function SetMiddlePositions($a) {
		$this->middle_pn = $a;
		return true;
	}

	public function SetPrevBlockTemplate($a) {
		$this->template_prev_block = $a;
		return true;
	}

	public function SetNextBlockTemplate($a) {
		$this->template_next_block = $a;
		return true;
	}

	public function SetPrevBlockNaTemplate($a) {
		$this->template_prev_block_na = $a;
		return true;
	}

	public function SetNextBlockNaTemplate($a) {
		$this->template_next_block_na = $a;
		return true;
	}

	public function SetCurrentPositionTemplate($a) {
		$this->template_current_position = $a;
		return true;
	}

	public function SetPositionTemplate($a) {
		$this->template_position = $a;
		return true;
	}

	public function SetSpacer($a) {
		$this->space = $a;
		return true;
	}

	public function GetPagination($rowsall,$sect) {
		$sect = $sect.REF_END;
		$pages_num = @ceil($rowsall/$this->onpage);
		$prev = $this->page-1;
		$first = false;
		$last = false;
		if ( $prev < 1 ) {
			$prev = 1;
			$first = true;
		}
		$next = $this->page+1;
		if ( $next > $pages_num ) {
			$next = $pages_num;
			$last = true;
		}
		$page_choose .= $this->GetPrevBlock($sect,$prev,$first);
		for ($i=1; $i<=$pages_num; $i++) {
			$mr = abs($i-$this->page);
			if ($this->type==0 || ( $this->type==1 && ( $i<=$this->left_pn || $i>($pages_num-$this->left_pn) || $mr<=$this->middle_pn) )) {
				if ($i==$this->page) { $page_choose.=$this->GetCurrentPosition($i); }
				else  { $page_choose.=$this->GetPosition($i,$sect); }
			}
			else {
				$page_choose.=$this->space;
				$page_choose = str_replace($this->space.$this->space, $this->space, $page_choose);
			}
		}
		$page_choose.=$this->GetNextBlock($sect,$next,$pages_num,$last);
		if ( $this->rel_canonical ) {
			if ( $this->page != 1 ) {
				FRAME_CORE::SetParameter('<link rel="canonical" href="'.BASE_URL.$sect.'" />
','header_dynamic_tags','',true);
			}
		}
		return $page_choose;
	}

	private function GetPrevBlock($sect,$prev,$first) {
		if ($first) {
			$s = $this->template_prev_block_na;
		}
		else {
			$s = $this->template_prev_block;
		}
		$s = str_replace('<[base_url]>',BASE_URL,$s);
		$s = str_replace('<[sect]>',$sect,$s);
		$s = str_replace('<[prev]>',$prev,$s);
		return $s;
	}

	private function GetNextBlock($sect,$next,$pages_num,$last) {
		if ($last) {
			$s = $this->template_next_block_na;
		}
		else {
			$s = $this->template_next_block;
		}
		$s = str_replace('<[base_url]>',BASE_URL,$s);
		$s = str_replace('<[sect]>',$sect,$s);
		$s = str_replace('<[next]>',$next,$s);
		$s = str_replace('<[pages_num]>',$pages_num,$s);
		return $s;
	}

	private function GetCurrentPosition($i) {
		$s = $this->template_current_position;
		$s = str_replace('<[position]>',$i,$s);
		return $s;
	}

	private function GetPosition($i,$sect) {
		$s = $this->template_position;
		$s = str_replace('<[position]>',$i,$s);
		$s = str_replace('<[base_url]>',BASE_URL,$s);
		$s = str_replace('<[sect]>',$sect,$s);
		return $s;
	}

}


?>