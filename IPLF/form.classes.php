<?php

/*

*** Forms Works Class
*** Is a part of iPloGic IPLF FrameWork 1.x
*** Version 1.0

*** Copyright (C) 2013 iPloGic, LLC. All rights reserved.
*** License GNU/GPL http://www.iplogic.ru/licenses/gpl/
*** Link http://www.iplogic.ru, info@iplogic.ru.

*** Manual page http://www.iplogic.ru/framework/manual/forms/

*/


class FORM
{
	public $template;
	public $template_type = 'text';
	public $action = './';
	public $method = 'POST';
	public $autocomplete = '';
	public $enctype = 'multipart/form-data';
	public $name = '';
	public $novalidate = false;
	public $target = '';
	public $attributs = '';
	public $doctype = 'xhtml';
	private $fields = Array();
	private $cfield = false;

	private $classes = Array(
		'select' => 'FORM_ELEMENT_SELECT',
		'textarea' => 'FORM_ELEMENT_TEXTAREA',
		'hidden' => 'FORM_ELEMENT_INPUT_HIDDEN',
		'text' => 'FORM_ELEMENT_INPUT_TEXT',
		'password' => 'FORM_ELEMENT_INPUT_PASSWORD',
		'file' => 'FORM_ELEMENT_INPUT_FILE',
		'button' => 'FORM_ELEMENT_INPUT_BUTTON',
		'submit' => 'FORM_ELEMENT_INPUT_SUBMIT',
		'reset' => 'FORM_ELEMENT_INPUT_RESET',
		'image' => 'FORM_ELEMENT_INPUT_IMAGE',
		'radio' => 'FORM_ELEMENT_INPUT_RADIO',
		'checkbox' => 'FORM_ELEMENT_INPUT_CHECKBOX',
		'datalist' => 'FORM_ELEMENT_INPUT_DATALIST',
		'number' => 'FORM_ELEMENT_INPUT_NUMBER',
		'rage' => 'FORM_ELEMENT_INPUT_RANGE',
		'date' => 'FORM_ELEMENT_INPUT_DATE',
		'datetime' => 'FORM_ELEMENT_INPUT_DATETIME',
		'datetime-local' => 'FORM_ELEMENT_INPUT_DATETIME_LOCAL',
		'time' => 'FORM_ELEMENT_INPUT_TIME',
		'email' => 'FORM_ELEMENT_INPUT_EMAIL',
		'search' => 'FORM_ELEMENT_INPUT_SEARCH',
		'url' => 'FORM_ELEMENT_INPUT_URL',
		'tel' => 'FORM_ELEMENT_INPUT_TEL',
		'color' => 'FORM_ELEMENT_INPUT_COLOR',
		'month' => 'FORM_ELEMENT_INPUT_MONTH',
		'week' => 'FORM_ELEMENT_INPUT_WEEK'
	);

	public function NewField($type,$name,$value = '') {
		$this->fields[$name] = new $this->classes[$type]();
		$this->fields[$name]->name = $name;
		$this->fields[$name]->type = $type;
		$this->cfield = $name;
		return true;
	}

	public function SetValue($value,$name = '') {
		if ($name!='' && !isset($this->fields[$name])) { return false; }
		if ($name=='' && !$this->cfield) { return false; }
		if ($name=='' && $this->cfield) { $name=$this->cfield; }
		$this->fields[$name]->value = $value;
		return true;
	}

	public function AddFieldAttribute($attribute, $value, $name = '') {
		if ($name!='' && !isset($this->fields[$name])) { return false; }
		if ($name=='' && !$this->cfield) { return false; }
		if ($name=='' && $this->cfield) { $name=$this->cfield; }
		if ( isset($this->fields[$name]->$attribute) ) { $this->fields[$name]->$attribute = $value; } else { return false; }
		return true;
	}

	public function AddTagAttributs($value, $name = '') {
		if ($name!='' && !isset($this->fields[$name])) { return false; }
		if ($name=='' && !$this->cfield) { return false; }
		if ($name=='' && $this->cfield) { $name=$this->cfield; }
		$this->fields[$name]->attributs = $value;
		return true;
	}

	public function AddOption($name,$value,$text,$selected=false,$disabled=false,$label='') {
		if ( $this->fields[$name]->type != 'select' && $this->fields[$name]->type != 'datalist') { return false; }
		if ( $this->fields[$name]->AddOption($value,$text,$selected,$disabled,$label) ) {
			return true;
		} else { return false; }
	}

	public function AddValue($value, $name = '') {
		if ($name!='' && !isset($this->fields[$name])) { return false; }
		if ($name=='' && !$this->cfield) { return false; }
		if ($name=='' && $this->cfield) { $name=$this->cfield; }
		$this->fields[$name]->values[]=$value;
		return true;
	}

	public function ChangeOptionAttributs($name,$ident,$attribute,$value) {
		if ( $this->fields[$name]->type != 'select' && $this->fields[$name]->type != 'datalist') { return false; }
		if ( $this->fields[$name]->ChangeOptionAttrib($ident,$attribute,$value) ) {
			return true;
		} else { return false; }
	}

	public function Render() {
		$html = '<form';
		$html .= ($this->name!='' ? ' name="'.$this->name.'"' : '');
		$html .= ($this->id!='' ? ' id="'.$this->id.'"' : '');
		$html .= ($this->action!='' ? ' action="'.$this->action.'"' : '');
		$html .= ($this->enctype!='' ? ' enctype="'.$this->enctype.'"' : '');
		$html .= ($this->method!='' ? ' method="'.$this->method.'"' : '');
		$html .= ($this->target!='' ? ' target="'.$this->target.'"' : '');
		$html .= ($this->novalidate ? ' novalidate' : '');
		$html .= ($this->autocomplete!='' ? ' autocomplete="'.$this->autocomplete.'"' : '');
		$html .= ($this->accept-charset!='' ? ' accept-charset="'.$this->accept-charset.'"' : '');
		$html .= ($this->attribs!='' ? ' "'.$this->attribs.'"' : '').'>';
		if ($this->template_type == 'file') {
			$this->template = file_get_contents($this->template);
		}
		foreach($this->fields as $field) {
			if ( $field->type == 'radio' ) {
				foreach( $field->values as $value ) {
					$field->val = $value;
					$this->template = str_replace('<['.$field->name.'||'.$field->val.']>',$field->Render($value),$this->template);
				}
			}
			else {
				$this->template = str_replace('<['.$field->name.']>',$field->Render(),$this->template);
			}
		}
		$html .= $this->template;
		$this->template = '';
		$html .= '</form>';
		return $html;
	}

	public function GetRequestValues() {
		$method = strtolower($this->method);
		foreach($this->fields as $field) {
			if ( $field->type == 'checkbox' ) {
				if ( isset($_REQUEST[$field->name]) ) {
					$field->value = 1;
					$field->checked = true;
				}
				else {
					$field->value = 0;
				}
			}
			else {
				if( strpos($field->name,'[') !== false ) {
					$s = explode('[',$field->name);
					$s[1] = str_replace(']','',$s[1]);
					$field->value[$s[1]] = FUNC::GrabRequestVar($method, $s[0], $s[1], $field->is_html);
				}
				else {
					$field->value = FUNC::GrabRequestVar($method, $field->name, '', $field->is_html);
				}
			}
		}
		return true;
	}

	public function GetFieldValue($name) {
		return $this->fields[$name]->value;
	}

	public function GetFieldsValues() {
		$values = Array();
		foreach($this->fields as $field) {
			$values[$field->name] = $field->value;
		}
		return $values;
	}

}


class FORM_ELEMENT
{
	public $form = '';
	public $name;
	public $type;
	public $id = '';
	public $value = '';
	public $readonly = false;      // textarea, text, password
	public $disabled = false;
	public $attributs = '';
	public $doctype = 'xhtml';
	public $maxlength = '';        // textarea, text, password
	public $placeholder = '';      // textarea, text, password
	public $tabindex = '';
	public $accesskey = '';
	public $autofocus = false;
	public $required = false;
	public $is_html = false;

	public function GetReadonly() {
		if ($this->readonly) {
			if ($doctype == 'xhtml') { return ' readonly="readonly"'; }
			else { return ' readonly'; }
		}
		return '';
	}

	public function GetDisabled() {
		if ($this->disabled) {
			if ($doctype == 'xhtml') { return ' disabled="disabled"'; }
			else { return ' disabled'; }
		}
		return '';
	}

}


class FORM_ELEMENT_OPTION
{
	public $text = '';
	public $value = '';
	public $selected = false;
	public $label = '';
	public $disabled = false;

	public function Render() {
		$html = '<option';
		$html .= ($this->value!='' ? ' value="'.$this->value.'"' : '');
		$html .= ($this->label!='' ? ' label="'.$this->label.'"' : '');
		$html .= ($this->selected ? ' selected' : '');
		$html .= ($this->disabled ? ' disabled' : '').'>'.$this->text.'</option>';
		return $html;
	}

}


class FORM_ELEMENT_SELECT extends FORM_ELEMENT
{
	public $multiple = false;
	public $size = '';
	public $value = false;
	private $options = Array();

	public function AddOption($value,$text,$selected=false,$disabled=false,$label='') {
		$option = new FORM_ELEMENT_OPTION();
		$option->text = $text;
		$option->value = $value;
		$option->selected = $selected;
		$option->label = $label;
		$option->disabled = $disabled;
		$this->options[$value] = $option;
		return true;
	}

	public function ChangeOptionAttrib($ident,$attribute,$value) {
		$atts = Array('selected','disabled','label');
		if ( !in_array($attribut,$atts) ) { return false; }
		if ( !isset($this->options[$ident]) ) { return false; }
		$this->options[$ident]->$attribute = $value;
		return true;
	}

	public function Render() {
		$html = '<select name="'.$this->name.'"';
		$html .= ($this->form!='' ? ' form="'.$this->form.'"' : '');
		$html .= ($this->id!='' ? ' id="'.$this->id.'"' : '');
		$html .= ($this->size!='' ? ' size="'.$this->size.'"' : '');
		$html .= ($this->multiple ? ' multiple' : '');
		$html .= ($this->tabindex!='' ? ' tabindex="'.$this->tabindex.'"' : '');
		$html .= ($this->accesskey!='' ? ' accesskey="'.$this->accesskey.'"' : '');
		$html .= ($this->autofocus ? ' autofocus' : '');
		$html .= ($this->required ? ' required' : '');
		$html .= $this->GetDisabled().$this->GetReadonly();
		$html .= ($this->attributs!='' ? ' '.$this->attributs.'' : '').'>';
		if ( $this->value ) {
			foreach($this->options as $option) {
				if ( $this->multiple && is_array($this->value) ) {
					if ( in_array($option->value,$this->value) ) { $option->selected = true; } else { $option->selected = false; }
				}
				else {
					if ( $this->value == $option->value ) { $option->selected = true; } else { $option->selected = false; }
				}
			}
		}
		foreach($this->options as $option) {
			$html .= $option->Render();
		}
		$html .= '</select>';
		return $html;
	}

}


class FORM_ELEMENT_TEXTAREA extends FORM_ELEMENT
{
	public $cols = '';
	public $rows = '';
	public $wrap = '';

	public function Render() {
		$html = '<textarea name="'.$this->name.'"';
		$html .= ($this->form!='' ? ' form="'.$this->form.'"' : '');
		$html .= ($this->id!='' ? ' id="'.$this->id.'"' : '');
		$html .= ($this->placeholder!='' ? ' placeholder="'.$this->placeholder.'"' : '');
		$html .= ($this->cols!='' ? ' cols="'.$this->cols.'"' : '');
		$html .= ($this->rows!='' ? ' rows="'.$this->rows.'"' : '');
		$html .= ($this->wrap!='' ? ' wrap="'.$this->wrap.'"' : '');
		$html .= ($this->tabindex!='' ? ' tabindex="'.$this->tabindex.'"' : '');
		$html .= ($this->maxlength!='' ? ' maxlength="'.$this->maxlength.'"' : '');
		$html .= ($this->accesskey!='' ? ' accesskey="'.$this->accesskey.'"' : '');
		$html .= ($this->autofocus ? ' autofocus' : '');
		$html .= ($this->required ? ' required' : '');
		$html .= $this->GetDisabled().$this->GetReadonly();
		$html .= ($this->attributs!='' ? ' '.$this->attributs.'' : '').'>'.$this->value.'</textarea>';
		return $html;
	}

}


class FORM_ELEMENT_INPUT extends FORM_ELEMENT
{
	public $autocomplete = '';     // text, password, email, search, url, tel
	public $checked = false;       // chechbox
	public $pattern = '';          // text, email, search, url, tel

	public function GetChecked() {
		if ( $this->value == 1 ) { $this->checked = true; }
		if ($this->checked) {
			if ($doctype == 'xhtml') { return ' checked="checked"'; }
			else { return ' checked'; }
		}
		return '';
	}

	public function RenderStart($type) {
		$html = '<input type="'.$type.'" name="'.$this->name.'"';
		$html .= ($this->form!='' ? ' form="'.$this->form.'"' : '');
		$html .= ($this->id!='' ? ' id="'.$this->id.'"' : '');
		if ($this->type!='radio') { $html .= ' value="'.$this->value.'"'; }
		return $html;
	}

	public function RenderEnd() {
		$html = ($this->tabindex!='' ? ' tabindex="'.$this->tabindex.'"' : '');
		$html .= ($this->accesskey!='' ? ' accesskey="'.$this->accesskey.'"' : '');
		$html .= ($this->autofocus ? ' autofocus' : '');
		$html .= ($this->required ? ' required' : '');
		$html .= $this->GetDisabled();
		$html .= ($this->attributs!='' ? ' '.$this->attributs.'' : '').($this->doctype=='xhtml' ? ' />' : '>');
		return $html;
	}

	public function RenderText() {
		$html = ($this->placeholder!='' ? ' placeholder="'.$this->placeholder.'"' : '');
		$html .= ($this->maxlength!='' ? ' maxlength="'.$this->maxlength.'"' : '');
		$html .= ($this->autocomplete!='' ? ' autocomplete="'.$this->autocomplete.'"' : '');
		$html .= $this->GetReadonly();
		return $html;
	}

}


class FORM_ELEMENT_INPUT_TEXT extends FORM_ELEMENT_INPUT
{
	public $size = '';

	public function Render() {
		$html = $this->RenderStart('text');
		$html .= ($this->pattern!='' ? ' pattern="'.$this->pattern.'"' : '');
		$html .= ($this->size!='' ? ' size="'.$this->size.'"' : '');
		$html .= $this->RenderText();
		$html .= $this->RenderEnd();
		return $html;
	}

}


class FORM_ELEMENT_INPUT_HIDDEN extends FORM_ELEMENT_INPUT
{

	public function Render() {
		$html = $this->RenderStart('hidden');
		$html .= $this->RenderEnd();
		return $html;
	}

}


class FORM_ELEMENT_INPUT_PASSWORD extends FORM_ELEMENT_INPUT
{
	public $size = '';

	public function Render() {
		$html = $this->RenderStart('password');
		$html .= ($this->size!='' ? ' size="'.$this->size.'"' : '');
		$html .= $this->RenderText();
		$html .= $this->RenderEnd();
		return $html;
	}

}


class FORM_ELEMENT_INPUT_FILE extends FORM_ELEMENT_INPUT
{
	public $accept = '';
	public $multiple = false;

	public function Render() {
		$html = $this->RenderStart('file');
		$html .= ($this->accept!='' ? ' accept="'.$this->accept.'"' : '');
		$html .= ($this->multiple ? ' multiple' : '');
		$html .= $this->RenderEnd();
		return $html;
	}

}


class FORM_ELEMENT_INPUT_BUTTON extends FORM_ELEMENT_INPUT
{

	public function Render() {
		$html = $this->RenderStart('button');
		$html .= $this->RenderEnd();
		return $html;
	}

}


class FORM_ELEMENT_INPUT_SUBMIT extends FORM_ELEMENT_INPUT
{
	public $formaction = '';
	public $formenctype = '';
	public $formmethod = '';
	public $formtarget = '';
	public $formnovalidate = false;

	public function Render() {
		$html = $this->RenderStart('submit');
		$html .= ($this->formaction!='' ? ' formaction="'.$this->formaction.'"' : '');
		$html .= ($this->formenctype!='' ? ' formenctype="'.$this->formenctype.'"' : '');
		$html .= ($this->formmethod!='' ? ' formmethod="'.$this->formmethod.'"' : '');
		$html .= ($this->formtarget!='' ? ' formtarget="'.$this->formtarget.'"' : '');
		$html .= ($this->formnovalidate ? ' formnovalidate' : '');
		$html .= $this->RenderEnd();
		return $html;
	}

}


class FORM_ELEMENT_INPUT_RESET extends FORM_ELEMENT_INPUT
{

	public function Render() {
		$html = $this->RenderStart('reset');
		$html .= $this->RenderEnd();
		return $html;
	}

}


class FORM_ELEMENT_INPUT_IMAGE extends FORM_ELEMENT_INPUT
{
	public $align = '';
	public $alt = '';
	public $border = '';
	public $src = '';

	public function Render() {
		$html = $this->RenderStart('image');
		$html .= ($this->align!='' ? ' align="'.$this->align.'"' : '');
		$html .= ($this->alt!='' ? ' alt="'.$this->alt.'"' : '');
		$html .= ($this->border!='' ? ' border="'.$this->border.'"' : '');
		$html .= ($this->src!='' ? ' src="'.$this->src.'"' : '');
		$html .= $this->RenderEnd();
		return $html;
	}

}


class FORM_ELEMENT_INPUT_RADIO extends FORM_ELEMENT_INPUT
{
	public $values = Array();
	public $val = '';

	public function Render($value) {
		$html = $this->RenderStart('radio');
		$html .= ' value="'.$this->val.'"';
		if ( $this->value == $value ) {
			if ($doctype == 'xhtml') { $html .= ' checked="checked"'; }
			else { $html .= ' checked'; }
		}
		else {
			$html .= '';
		}
		$html .= $this->GetChecked();
		$html .= $this->RenderEnd();
		return $html;
	}

}


class FORM_ELEMENT_INPUT_CHECKBOX extends FORM_ELEMENT_INPUT
{

	public function Render() {
		$html = $this->RenderStart('checkbox');
		$html .= $this->GetChecked();
		$html .= $this->RenderEnd();
		return $html;
	}

}


class FORM_ELEMENT_INPUT_DATALIST extends FORM_ELEMENT_INPUT
{
	private $options = Array();

	public function AddOption($value,$text,$selected=false,$disabled=false,$label='') {
		$option = new FORM_ELEMENT_OPTION();
		$option->text = $text;
		$option->value = $value;
		$option->selected = $selected;
		$option->label = $label;
		$option->disabled = $disabled;
		$this->options[$value] = $option;
		return true;
	}

	public function ChangeOptionAttrib($ident,$attribute,$value) {
		$atts = Array('selected','disabled','label');
		if ( !in_array($attribut,$atts) ) { return false; }
		if ( !isset($this->options[$ident]) ) { return false; }
		$this->options[$ident]->$attribute = $value;
		return true;
	}

	public function Render() {
		if ($this->id=='') { return false; }
		$html = '<input list="'.$this->id.'"';
		$html .= ($this->form!='' ? ' form="'.$this->form.'"' : '');
		$html .= $this->RenderEnd();
		$html .= '<datalist id="'.$this->id.'">';
		foreach($options as $option) {
			$html .= $option->Render();
		}
		$html .= '</datalist>';
		return $html;
	}

}


class FORM_ELEMENT_INPUT_NUMBER extends FORM_ELEMENT_INPUT
{
	public $max = '';
	public $min = '';
	public $step = '';

	public function Render() {
		$html = $this->RenderStart('number');
		$html .= ($this->min!='' ? ' min="'.$this->min.'"' : '');
		$html .= ($this->max!='' ? ' max="'.$this->max.'"' : '');
		$html .= ($this->step!='' ? ' step="'.$this->step.'"' : '');
		$html .= $this->RenderEnd();
		return $html;
	}

}


class FORM_ELEMENT_INPUT_RANGE extends FORM_ELEMENT_INPUT
{
	public $max = '';
	public $min = '';
	public $step = '';

	public function Render() {
		$html = $this->RenderStart('range');
		$html .= ($this->min!='' ? ' min="'.$this->min.'"' : '');
		$html .= ($this->max!='' ? ' max="'.$this->max.'"' : '');
		$html .= ($this->step!='' ? ' step="'.$this->step.'"' : '');
		$html .= $this->RenderEnd();
		return $html;
	}

}


class FORM_ELEMENT_INPUT_DATE extends FORM_ELEMENT_INPUT
{
	public $max = '';
	public $min = '';

	public function Render() {
		$html = $this->RenderStart('date');
		$html .= ($this->min!='' ? ' min="'.$this->min.'"' : '');
		$html .= ($this->max!='' ? ' max="'.$this->max.'"' : '');
		$html .= $this->RenderEnd();
		return $html;
	}

}


class FORM_ELEMENT_INPUT_DATETIME extends FORM_ELEMENT_INPUT
{

	public function Render() {
		$html = $this->RenderStart('datetime');
		$html .= $this->RenderEnd();
		return $html;
	}

}


class FORM_ELEMENT_INPUT_DATETIME_LOCAL extends FORM_ELEMENT_INPUT
{

	public function Render() {
		$html = $this->RenderStart('datetime-local');
		$html .= $this->RenderEnd();
		return $html;
	}

}


class FORM_ELEMENT_INPUT_TIME extends FORM_ELEMENT_INPUT
{

	public function Render() {
		$html = $this->RenderStart('time');
		$html .= $this->RenderEnd();
		return $html;
	}

}


class FORM_ELEMENT_INPUT_EMAIL extends FORM_ELEMENT_INPUT
{
	public $multiple = false;

	public function Render() {
		$html = $this->RenderStart('email');
		$html .= ($this->autocomplete!='' ? ' autocomplete="'.$this->autocomplete.'"' : '');
		$html .= ($this->pattern!='' ? ' pattern="'.$this->pattern.'"' : '');
		$html .= ($this->multiple ? ' multiple' : '');
		$html .= $this->RenderEnd();
		return $html;
	}

}


class FORM_ELEMENT_INPUT_SEARCH extends FORM_ELEMENT_INPUT
{

	public function Render() {
		$html = $this->RenderStart('search');
		$html .= ($this->autocomplete!='' ? ' autocomplete="'.$this->autocomplete.'"' : '');
		$html .= ($this->pattern!='' ? ' pattern="'.$this->pattern.'"' : '');
		$html .= $this->RenderEnd();
		return $html;
	}

}


class FORM_ELEMENT_INPUT_URL extends FORM_ELEMENT_INPUT
{

	public function Render() {
		$html = $this->RenderStart('url');
		$html .= ($this->autocomplete!='' ? ' autocomplete="'.$this->autocomplete.'"' : '');
		$html .= ($this->pattern!='' ? ' pattern="'.$this->pattern.'"' : '');
		$html .= $this->RenderEnd();
		return $html;
	}

}


class FORM_ELEMENT_INPUT_TEL extends FORM_ELEMENT_INPUT
{

	public function Render() {
		$html = $this->RenderStart('tel');
		$html .= ($this->autocomplete!='' ? ' autocomplete="'.$this->autocomplete.'"' : '');
		$html .= ($this->pattern!='' ? ' pattern="'.$this->pattern.'"' : '');
		$html .= $this->RenderEnd();
		return $html;
	}

}


class FORM_ELEMENT_INPUT_COLOR extends FORM_ELEMENT_INPUT
{

	public function Render() {
		$html = $this->RenderStart('color');
		$html .= $this->RenderEnd();
		return $html;
	}

}


class FORM_ELEMENT_INPUT_MONTH extends FORM_ELEMENT_INPUT
{

	public function Render() {
		$html = $this->RenderStart('month');
		$html .= $this->RenderEnd();
		return $html;
	}

}


class FORM_ELEMENT_INPUT_WEEK extends FORM_ELEMENT_INPUT
{

	public function Render() {
		$html = $this->RenderStart('week');
		$html .= $this->RenderEnd();
		return $html;
	}

}


?>