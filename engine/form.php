<?php

include_once( RVXPATH.'form_box.php' );
include_once( RVXPATH.'form_tab.php' );
include_once( RVXPATH.'form_control.php' );

//=============================================================================
class RForm
//=============================================================================
{
	var $Title;			// string
	var $Model;			// object model
	var $ModelUrl;		// string model url
	var $ModelKey;		// integer - record key id

        var $ConfirmPrint;        // int
        var $ControllerClass;        // int

	var $Columns;		// form columns
	var $Tabs;		// array of tab pages
	var $TabsNames;		// string
	var $Boxes;		// array of group boxes
	var $BoxesNames;	// string
	var $Controls;		// array of controls
	var $ControlNames;	// string
	var $FocusCtrl;		// string - first control to focus
	var $Actions = array();		// array of action objects
	var $Reports = array();		// array of form reports

	var $HtmlControls;	// string - controls definitions
	var $HtmlActions;	// string - actions definitions
	var $HtmlReports;	// string - qr reports array

	var $EditMode;		// boolean - appliable to all controls
	var $PostMode;		// boolean - show post buttons

//=============================================================================
	function Load()
//=============================================================================
	{
		$rvx = & get_engine();
		$fln = $rvx->Context->GetXmlPath( 'form.xml' );
		$this->LoadXml( $fln );
		$this->ModelUrl = base_url().$rvx->Context->Path.'/';

        // controller class
        $this->ControllerClass = $rvx->Router->ClassName;
	}

//=============================================================================
	function LoadXml( $fln )
//=============================================================================
	{
		$rvx =& get_engine();
		$xml = simplexml_load_file( $fln );

		// load form
		$attrs = $xml->form->attributes();
		$this->Title = (string)$attrs->title;
		$this->Columns = (integer)$attrs->columns;

		// validate
		if( $this->Title == '' ) {
			rvx_error( 'Error in form.xml definition: title attribute is missing' );
		}
		if( $this->Columns == '' ) {
			$this->Columns = 1;
		}

		// load tab pages
		if( !isset($xml->tabs) )
		{
			$x = new RFormTab();
			$x->Columns = $this->Columns;
			$this->Tabs[] = $x;
		}
		else foreach( $xml->tabs->tab as $tab )
		{
			$x = new RFormTab();
			$x->LoadXml( $tab );
			$this->Tabs[] = $x;
		}

		// load boxes
		if( !isset($xml->boxes) )
		{
			$x = new RFormBox();
			$this->Boxes[] = $x;
		}
		else foreach( $xml->boxes->box as $box )
		{
			$x = new RFormBox();
			$x->LoadXml( $box );
			$this->Boxes[] = $x;
		}

		// load actions
		if( !isset($xml->actions) ) {
			$this->Actions = array();
		}
		else foreach( $xml->actions->action as $act )
		{
			$a = new StdClass();
			$attrs = $act->attributes();
			$a->Caption = (string)$attrs->caption;
			$a->Caption = rvx_lang( $a->Caption );
			$a->Address = base_url().(string)$attrs->url;
			$a->IsPopup = (string)$attrs->popup;
			if( $a->IsPopup == '' ) {
				$a->IsPopup = 'false';
			}
			$this->Actions[] = $a;
		}

		// load controls
		foreach( $xml->controls->control as $ctrl )
		{
			$x = new RFormControl();
			$x->LoadXml( $ctrl );
			$this->Controls[] = $x;

			// first focus control
			if( ($this->FocusCtrl == '') && ( $x->Type != 'hidden') ) {
				$this->FocusCtrl = $x->Name;
			}
		}

		// load reports
		$fln = $rvx->Context->GetXmlPath( 'print.xml' );
		if( file_exists($fln) )
		{
			$i = 0;
			$xml = simplexml_load_file( $fln );
			foreach( $xml->prints->print as $p )
			{
				$attr = $p->attributes();
				$name = (string)$attr->caption;
				$name = rvx_lang( $name );
				$this->Reports[] = (++$i).'. '.$name;
			}
		}

		// translate
		$this->Title = rvx_lang( $this->Title );
	}

//=============================================================================
	function LoadTranslation()
//=============================================================================
	{
		$rvx =& get_engine();

		// load form
		$this->Title = (string)'Translate';
		$this->Columns = (integer)2;

		// validate
        $x = new RFormTab();
        $x->Tabs[] = array('Id'=>1, 'Name' => 'Tab1', 'Caption' => 'Detail', 'Columns' => 2);
        $this->Tabs[] = $x;

		// load boxes
		if( !isset($xml->boxes) )
		{
			$x = new RFormBox();
			$this->Boxes[] = $x;
		}
		else foreach( $xml->boxes->box as $box )
		{
			$x = new RFormBox();
			$x->LoadXml( $box );
			$this->Boxes[] = $x;
		}

		// load actions
		if( !isset($xml->actions) ) {
			$this->Actions = array();
		}
		else foreach( $xml->actions->action as $act )
		{
			$a = new StdClass();
			$attrs = $act->attributes();
			$a->Caption = (string)$attrs->caption;
			$a->Caption = rvx_lang( $a->Caption );
			$a->Address = base_url().(string)$attrs->url;
			$a->IsPopup = (string)$attrs->popup;
			if( $a->IsPopup == '' ) {
				$a->IsPopup = 'false';
			}
			$this->Actions[] = $a;
		}

		// load controls
		foreach( $xml->controls->control as $ctrl )
		{
			$x = new RFormControl();
			$x->LoadXml( $ctrl );
			$this->Controls[] = $x;

			// first focus control
			if( ($this->FocusCtrl == '') && ( $x->Type != 'hidden') ) {
				$this->FocusCtrl = $x->Name;
			}
		}

		// load reports
		$fln = $rvx->Context->GetXmlPath( 'print.xml' );
		if( file_exists($fln) )
		{
			$i = 0;
			$xml = simplexml_load_file( $fln );
			foreach( $xml->prints->print as $p )
			{
				$attr = $p->attributes();
				$name = (string)$attr->caption;
				$name = rvx_lang( $name );
				$this->Reports[] = (++$i).'. '.$name;
			}
		}

		// translate
		$this->Title = rvx_lang( $this->Title );
	}

//=============================================================================
	function Render()
//=============================================================================
	{
		// render tab pages
		$this->TabsNames = ''; $comma = '';

		foreach( $this->Tabs as $tab )	{
			$this->HtmlControls .= $tab->Render();

			$this->TabsNames .= $comma . $tab->Name; $comma = ',';
		}

		// render group boxes
		$this->BoxesNames = ''; $comma = '';
		foreach( $this->Boxes as $box )	{
			$this->HtmlControls .= $box->Render();
			$this->HtmlControls .= "Tab$box->Tab.add( $box->Name );\n";

			$this->BoxesNames .= $comma . $box->Name; $comma = ',';
		}

		// render controls
		$this->ControlNames = ''; $comma = '';
		foreach( $this->Controls as $ctrl ) {
			$this->HtmlControls .= $this->RenderControl( $ctrl );
			$this->HtmlControls .= "Box$ctrl->Box.add( $ctrl->Name );\n";

			$this->ControlNames .= $comma . $ctrl->Name; $comma = ",";
		}

		// render action menu
		$this->HtmlActions = "["; $comma = "";
		foreach( $this->Actions as $act )
		{
			if( $act->Caption == '-' )
			{
				$this->HtmlActions .= $comma . "'-'";
				continue;
			}
			$fct = "function(){ HandleAction('$act->Address', $act->IsPopup )}";
			$this->HtmlActions .= $comma . "\n\t{text:'$act->Caption', handler:$fct}";
			$comma = ",";
		}
		$this->HtmlActions .= "]";

		// render reports array
		$this->HtmlReports = '['; $comma = '';
		foreach( $this->Reports as $rpt )
		{
			$this->HtmlReports .= $comma . "['".$rpt."']";
			$comma = ',';
		}
		$this->HtmlReports .= "]";


		// show page
		$this->RenderPage();
	}

//=============================================================================
	function RenderControl( $ctrl )
//=============================================================================
	{
		return $ctrl->Render();
	}

//=============================================================================
	function RenderPage()
//=============================================================================
	{
		$rvx = & get_engine();

		$view = $this;
		$page_title = $this->Title;
		$page_language = $rvx->Language->Code;
		include_once( RVXPATH.'form_page.php' );
	}

//=============================================================================
	function SetModel( $m, $bEditMode )
//=============================================================================
	{
		$this->Model = $m;
		$this->EditMode = $bEditMode;
		$this->PostMode = $m->PostMode;
		$this->ModelKey = $m->GetField( $m->TableKey );
		$this->Title .= " [".$this->ModelKey."]";

		foreach( $this->Controls as & $ctrl )
		{
			if( ! array_key_exists( $ctrl->FieldName, $m->Fields ) )
			{
				$ctrl->Value = '#!#' . $ctrl->FieldName;
				continue;
			}

			$fld = $m->Fields[$ctrl->FieldName];
			$ctrl->Value = trim( $fld->FmtValue );

			// slashes form controls
			$ctrl->Value = addslashes( $ctrl->Value );

			if( $ctrl->Caption == '' )
				$ctrl->Caption = $fld->Caption;

			$ctrl->Mandatory   = $fld->Mandatory;
			$ctrl->UpperCase   = $fld->UpperCase;
			$ctrl->LowerCase   = $fld->LowerCase;
			$ctrl->BlankDisabled  = $fld->BlankDisabled;
			$ctrl->ReadOnly    = $ctrl->ReadOnly || ( ! $bEditMode );

			$ctrl->LookupModel = $fld->LookupModel;
			$ctrl->LookupTable = $fld->LookupTable;
			$ctrl->LookupKey   = $fld->LookupKey;
			$ctrl->LookupName  = $fld->LookupName;
			$ctrl->Precision   = $fld->GetPrecision();

			if( $fld->LookupCombo )
				$ctrl->ComboSelect = $fld->LookupCombo;
		}
	}

//=============================================================================
	function RenderButtons()
//=============================================================================
	{
	}
}


?>