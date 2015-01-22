<?php


/*

*** Tree Structurs Works Classes
*** Is a part of iPloGic IPLF FrameWork 1.x
*** Version 1.0

*** Copyright (C) 2014 iPloGic, LLC. All rights reserved.
*** License GNU/GPL http://www.iplogic.ru/licenses/gpl/
*** Link http://www.iplogic.ru, info@iplogic.ru.

*** Manual page http://www.iplogic.ru/framework/manual/tree/

*/


class TREENODE
{
	public $name;
	public $id;
	public $parent;
	public $children_num=0;
	public $children = Array();
	public $items_num=0;
	public $special_items_num=0;

	function __construct($name, $id, $parent = '') {
		$this->name = $name;
		$this->id = $id;
		if ($parent != '') { $this->parent = $parent; }
	}

}


class TREE
{
	public $categories_table;
	public $count_children = false;
	public $items_table;
	public $all_items = false;
	public $count_special_items = false;
	public $special_items_field;
	public $special_items_value;
	public $id = 0;
	public $name = 'root';
	public $children_num=0;
	public $children = Array();
	public $items_num=0;
	public $special_items_num=0;
	private $nodes = Array();
	private $subnodes = Array();
	private $finish_level;
	private $lid = 1;


	public function CreateEmptyTree() {		$this->nodes[0] = new TREENODE('root',0,0);
		$this->id = 0;
		return true;	}

	public function CreateElementsFromTable( $id, $deepth = false ) {
		if ( !FRAME_CORE::DB()->TableExists(DB_PREFIX.$this->categories_table) ) { return false; }
		$this->id = $id;
		if ( $deepth ) {			$lev_to_sql = '';
			if ( $id == 0 ) {				$start_level = 1;
				$this->finish_level = $deepth;
				$lev_to_sql .= ' WHERE ';
			}
			else {				$sql = "SELECT `parents_line` FROM `".DB_PREFIX.$this->categories_table."` WHERE `id`='".$id."'";
				$rows = FRAME_CORE::DB()->GetFirstResult($sql);
				if ( !$rows ) { return false; }
				$pl = explode('#', $rows['parents_line']);
				$start_level = $pl[1];
				$this->finish_level = $start_level + $deepth;
				$lts1 = '(';
				$lts2 = ')';
			}
			$finish_level = $this->finish_level;
			if ( $this->count_children ) {				$finish_level++;			}
			for ( $i = $start_level; $i <= $finish_level; $i++ ) {
				$lev_to_sql_arr[] = "`parents_line` LIKE '%/#".$i."'";
			}
			$lev_to_sql .= implode(' OR ', $lev_to_sql_arr);		}
		if ( $id == 0 ) {
			$this->nodes[0] = new TREENODE('root',0,0);
			$sql = "SELECT `id`, `parent`, `name`, `parents_line`, `priority` FROM `".DB_PREFIX.$this->categories_table."`".$lev_to_sql." ORDER BY `priority`";
		}
		else {			$sql = "SELECT `id`, `parent`, `name`, `parents_line`, `priority` FROM `".DB_PREFIX.$this->categories_table."` WHERE ".($lev_to_sql!='' ? '(' : '').$lev_to_sql.($lev_to_sql!='' ? ') AND' : '')." ".$lts1."`id`='".$id."' OR `parents_line` LIKE '%/".$id."/%'".$lts2." ORDER BY `priority`";		}
		$rows = FRAME_CORE::DB()->GetResult($sql);
		if ($rows) {
			foreach($rows as $row){				if ( $row['name']!='' && $row['parent']!='' && $row['id']!='' ) {
					$ita = explode('#', $row['parents_line']);
					if ( $ita[1] > $this->finish_level && $deepth ) {
						$this->subnodes[$row['id']] = new TREENODE($row['name'],$row['id'],$row['parent']);
					}
					else {						$this->nodes[$row['id']] = new TREENODE($row['name'],$row['id'],$row['parent']);					}
				}
			}
			unset($rows);
			foreach($this->nodes as $node){
				if (!isset($this->nodes[$node->parent])) continue;
				$this->nodes[$node->parent]->children[] = $node->id;
				if ( $this->count_children ) {					$this->nodes[$node->parent]->children_num++;
				}
			}
			if ( $this->count_children ) {				foreach($this->subnodes as $subnode){					if (!isset($this->nodes[$subnode->parent])) continue;
					$this->nodes[$subnode->parent]->children_num++;
				}			}
			$this->subnodes = Array();
		}
		if ( !count( $this->nodes ) ) { return false; }
		return true;
	}

	public function AddItemsCount() {
		if ( !FRAME_CORE::DB()->TableExists(DB_PREFIX.$this->items_table) ) { return false; }
		$sql = "SELECT `id`, `parent`, `parents_line`".($this->count_special_items ? ", `".$this->special_items_field."`" : "")." FROM ".DB_PREFIX.$this->items_table." WHERE `parents_line` LIKE '%/".$this->id."/%'";
		$rows = FRAME_CORE::DB()->GetResult($sql);
		if ( $rows ) {			if ( !$this->all_items ) {
				foreach( $rows as $row ) {					if (!isset($this->nodes[$row['parent']])) continue;
					$this->nodes[$row['parent']]->items_num++;
					if ( $this->count_special_items && $row[$this->special_items_field] == $this->special_items_value ) {						$this->nodes[$row['parent']]->special_items_num++;					}				}
			}
			else {				foreach( $rows as $row ) {
					if (!isset($this->nodes[$row['parent']])) continue;
					$ita_ = explode('#', $row['parents_line']);
					$ita = explode('/', $ita_[0]);
					foreach ( $ita as $id ) {
						if ( $id!='' ) {
							$this->nodes[$id]->items_num++;
							if ( $this->count_special_items && $row[$this->special_items_field] == $this->special_items_value ) {
								$this->nodes[$id]->special_items_num++;
							}
						}
					}
				}			}		}
		return true;
	}

	public function AddLine( $line, $separator='/' ) {
		$pid = $this->id;
		$li=explode($separator,trim($line));
		foreach ( $li as $name ) {
			$name = trim($name);
			if ( $name == '' ) { return true; }
			$new = true;
			foreach ($this->nodes[$pid]->children as $child) {
				if ( $this->nodes[$child]->name == $name ) {
					$pid = $this->nodes[$child]->id;
					$new = false;
					break;
				}
			}
			if ( $new ) {
				$this->nodes[$this->lid] = new TREENODE($name, $this->lid, $pid);
				$this->nodes[$pid]->children[] = $this->lid;
				if ( $this->count_children ) {
					$this->nodes[$pid]->children_num++;
				}
				$pid = $this->lid;
				$this->lid++;
			}
		}
		return true;
	}

	public function AddElement( $element_key, $id, $name, $parent ) {
		$pe = false;
		foreach( $this->nodes as $node ) {			if ( $this->nodes->id == $parent ) { $pe = true; break; }		}
		if ( !$pe ) { return false; }
		$this->nodes[$element_key] = new TREENODE($name,$id,$parent);
		if ( $this->count_children ) {
			$this->nodes[$parent]->children_num++;
		}
		return true;
	}

	public function DeleteElement( $element_key ) {
		if ( !isset($this->nodes[$element_key]) ) { return false; }
		unset($this->nodes[$element_key]);
		if ( $this->count_children ) {
			$this->nodes[$parent]->children_num--;
		}
		return true;
	}

	public function ConstructObject() {
		if ( !count( $this->nodes ) ) { return false; }
		$this->name = $this->nodes[$this->id]->name;
		$this->children_num = $this->nodes[$this->id]->children_num;
		$this->special_items_num = $this->nodes[$this->id]->special_items_num;
		$this->items_num = $this->nodes[$this->id]->items_num;
		foreach( $this->nodes[$this->id]->children as $node_id ) {			$this->children[] = $this->nodes[$node_id];
			unset($this->nodes[$node_id]);		}
		unset($this->nodes[$this->id]);
		foreach( $this->children as $key => $node ) {
			$this->children[$key] = $this->InsertNodes( $node );
		}
		return true;
	}

	private function InsertNodes( $node ) {
		if ( !count($node->children) ) { return $node; }
		$children = $node->children;
		$node->children = Array();
		foreach( $children as $node_id ) {
			$node->children[] = $this->nodes[$node_id];
			unset($this->nodes[$node_id]);
		}
		foreach( $node->children as $key => $child ) {			$node->children[$key] = $this->InsertNodes( $child );		}
		return $node;
	}

}

?>