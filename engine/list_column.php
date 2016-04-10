<?php

//=============================================================================
class RListColumn
//=============================================================================
{
	var $Caption;
	var $FieldName;
	var $FieldType;
	var $Precision;

	var $Align;
	var $Width;
	var $Hidden;
	var $Editor;
	var $UpperCase;

	var $LookupModel;
	var $LookupTable;
	var $LookupKey;
	var $LookupName;
	var $LookupUrl;

	var $FldValue;
	var $FmtValue;

//=============================================================================
	function LoadXml( $col )
//=============================================================================
	{
		$attrs = $col->attributes();

		$this->FieldName = (string)$attrs['field'];
		$this->FieldType = (string)$attrs['type'];
		$this->Precision = (string)$attrs['precision'];

		$this->Caption   = (string)$attrs['caption'];
		$this->Align     = (string)$attrs['align'];
		$this->Hidden    = (string)$attrs['hidden'];
		$this->Width     = (integer)$attrs['width'];
		$this->Editor    = (bool)$attrs['editor'];
		$this->UpperCase = (bool)$attrs['upper'];

		$this->Mandatory   = (bool)$attrs['mandatory'];
		$this->LookupModel = (string)$attrs['lookupmodel'];
		$this->LookupTable = (string)$attrs['lookuptable'];
		$this->LookupKey   = (string)$attrs['lookupkey'];
		$this->LookupName  = (string)$attrs['lookupname'];

		$this->InitDefaults();
	}

//=============================================================================
	function InitDefaults()
//=============================================================================
	{
		// validate mandatory attributes
		if( $this->FieldName == '' )
			rvx_error( 'list.xml: Attribute [field] is missing' );

		if( $this->Caption == '' )
			$this->Caption = $this->FieldName;

		if( $this->FieldType == '' )
			$this->FieldType = FLD_STRING;

		// check valid field type
		$valid_field_types = array( FLD_STRING, FLD_INTEGER, FLD_NUMBER, FLD_MONEY, FLD_DATE, FLD_TIME, FLD_BOOL, FLD_COMBO );
		if( !in_array( $this->FieldType, $valid_field_types ) )
			rvx_error( 'list.xml: Invalid type [%s] for column [%s]', $this->FieldType, $this->FieldName );

		// default alignment
		if( $this->Align == '' )
		{
			$number_alignments = array( FLD_INTEGER, FLD_NUMBER, FLD_MONEY );
			if( in_array( $this->FieldType, $number_alignments ) )
				$this->Align = ALG_RIGHT;
			else
				$this->Align = ALG_LEFT;
		}

		// check valid alignment
		$valid_alignments = array( ALG_LEFT, ALG_RIGHT, ALG_CENTER );
		if( !in_array( $this->Align, $valid_alignments ) )
			rvx_error( 'list.xml: Invalid alignment [%s] for column [%s]', $this->Align, $this->FieldName );

		// default hidden
		if( $this->Hidden == '' )
			$this->Hidden = "false";

		// default width
		if( $this->Width == '' )
			$this->Width = 200;

		// translate
		$this->Caption = rvx_lang( $this->Caption );
	}

//================================================================================================================
	function LoadValue( $value )
//================================================================================================================
	{
		$rvx = & get_engine();
		$this->FldValue = $value;
		$this->FmtValue = $rvx->Mask->FormatField( $value, $this->FieldType, true, $this->Precision );

		if( $this->LookupModel AND $this->FieldType == FLD_STRING )
		{
			$url = base_url().$this->LookupModel.'/viewlink/fld/'.$this->LookupName.'/val/'.$this->FmtValue;
			$this->FmtValue = '<a target=_blank href="'.$url.'">'.$this->FmtValue.'</a>';
		}
	}
}


?>