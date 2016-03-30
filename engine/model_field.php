<?php

//=============================================================================
class RModelField
//=============================================================================
{
	var $Model;			// object - parent model
	var $Caption;		// string
	var $FieldName;		// string
	var $FieldType;		// string (string, date, time, integer, number)
	var $TableName;		// string

	var $Mandatory;		// boolean -
	var $Unique;		// boolean -
	var $UpperCase;		// boolean - uppercase text
	var $LowerCase;		// boolean - uppercase text
	var $BlankDisabled;		// boolean - allow blank in text
	var $Limit;			// integer - max characters
	var $Precision;		// integer - number precision

	var $LookupModel;	// relation with a model
	var $LookupTable;	// relation with a table
	var $LookupKey;		// relation key field
	var $LookupName;	// relation display field
	var $LookupFree;
	var $LookupCombo;	// string delimited values |

	var $IniValue;		// variant - default initial value
	var $NewValue;		// variant - modified value
	var $OldValue;		// variant - existent value
	var $FmtValue;		// string - formated value as string
	var $IsModified;	// bool - set by SetField

//=============================================================================
	function RModelField( $model )
//=============================================================================
	{
		$this->Model = $model;
		$this->IsModified = false;
	}

//=============================================================================
	function LoadXml( $fld )
//=============================================================================
	{
		$attrs = $fld->attributes();

		$this->Caption   = (string)$attrs['caption'];
		$this->FieldName = (string)$attrs['name'];
		$this->FieldType = (string)$attrs['type'];
		$this->Mandatory = (bool)$attrs['mandatory'];
		$this->Unique    = (bool)$attrs['unique'];
		$this->UpperCase = (bool)$attrs['upper'];
		$this->LowerCase = (bool)$attrs['lower'];
		$this->BlankDisabled = (bool)$attrs['blankdisabled'];
		$this->Limit     = (integer)$attrs['limit'];
		$this->Precision = (integer)$attrs['precision'];

		$this->LookupModel = (string)$attrs['lookupmodel'];
		$this->LookupTable = (string)$attrs['lookuptable'];
		$this->LookupKey   = (string)$attrs['lookupkey'];
		$this->LookupName  = (string)$attrs['lookupname'];
		$this->LookupFree  = (bool)$attrs['lookupfree'];
		$this->LookupCombo = (string)$attrs['select'];
		$this->IniValue    = (string)$attrs['default'];

		$this->InitDefaults();
	}

//=============================================================================
	function InitDefaults()
//=============================================================================
	{
		// Id field
		if( $this->FieldName == 'Id' )
		{
			$this->FieldType  = FLD_INTEGER;
			$this->Mandatory  = false;
			$this->Unique     = false;
		}

		if( $this->FieldName == '' )
			rvx_error( 'model.xml: Attribute [field] is missing' );

		if( $this->FieldType == '' )
			rvx_error( 'model.xml: Attribute [type] is missing for field [%s]', $this->FieldName );


		// check valid field type
		$valid_field_types = array( FLD_STRING, FLD_INTEGER, FLD_NUMBER, FLD_MONEY, FLD_DATE, FLD_TIME, FLD_BOOL );
		if( !in_array( $this->FieldType, $valid_field_types ) )
			rvx_error( 'model.xml: Invalid type [%s] for field [%s]', $this->FieldType, $this->FieldName );

		if( $this->Mandatory == '' )
			$this->Mandatory = false;

		if( $this->Unique == '' )
			$this->Unique = false;

		if( $this->UpperCase == '' )
			$this->UpperCase = false;

		if( $this->Limit == '' )
			$this->Limit = 0;

		if( $this->LookupFree == '' )
			$this->LookupFree = false;

		// default caption is fieldname
		if( $this->Caption == '' )
			$this->Caption = $this->FieldName;

		// default LookupKey = Id & LookupName = LookupKey
		if( ( $this->LookupTable != '' ) && ( $this->LookupKey == '' ) )
			$this->LookupKey = 'Id';

		if( ( $this->LookupTable != '' ) && ( $this->LookupName == '' ) )
			$this->LookupName = $this->LookupKey;

		// default number = 0
		if( ( $this->IniValue == '' ) && ( $this->FieldType == FLD_INTEGER ) )
			$this->IniValue = 0;

		// default number = 0
		if( ( $this->IniValue == '' ) && ( $this->FieldType == FLD_NUMBER ) )
			$this->IniValue = 0;

		// default date
		if( ( $this->IniValue == '' ) && ( $this->FieldType == FLD_DATE ) )
			$this->IniValue = date('Y-m-d');

		// default time
		if( ( $this->IniValue == '' ) && ( $this->FieldType == FLD_TIME ) )
			$this->IniValue = date('H:i');

		// default bool
		if( ( $this->IniValue == '' ) && ( $this->FieldType == FLD_BOOL ) )
			$this->IniValue = 0;


		// translate
		$this->Caption = rvx_lang( $this->Caption );

		// initialize default value
		$this->LoadValue( $this->IniValue );
	}

//=============================================================================
	function SetValue( $v )
//=============================================================================
	{
		$this->NewValue = $v;
		$this->FmtValue = $v;
		$this->FormatValue( $v );
		$this->IsModified = true;
	}

//=============================================================================
	function GetValue()
//=============================================================================
	{
		return $this->NewValue;
	}

//=============================================================================
	function LoadValue( $v )
//=============================================================================
	{
		$this->NewValue = $v;
		$this->OldValue = $v;
		$this->FmtValue = $v;
		$this->FormatValue( $v );
	}

//=============================================================================
	function FormatValue( $v )
//=============================================================================
	{
		// search lookup field for display
		if( $this->LookupTable != '' )
		{
			$this->LoadLookup( $v );
			return;
		}
		if( $this->LookupCombo != '' )
		{
			$this->LoadCombo( $v );
			return;
		}

		$rvx = & get_engine();
		$this->FmtValue = $rvx->Mask->FormatField( $v, $this->FieldType );

		// TODO: format should consider field precision
		if( $this->Precision ) $this->FmtValue = $v;
	}

//=============================================================================
	function SaveValue( $v )
//=============================================================================
	{
		$this->NewValue = $v;
		$this->FmtValue = $v;

		// search lookup key for save
		if( $this->LookupTable != '' )
		{
			$this->SaveLookup( $v );
			return;
		}
		if( $this->LookupCombo != '' )
		{
			$this->SaveCombo( $v );
			return;
		}

		$rvx = & get_engine();
		$this->NewValue = $rvx->Mask->UnformatField( $v, $this->FieldType );
	}

//=============================================================================
	function LoadLookup( $v )
//=============================================================================
	{
		if( $this->LookupTable == '' )
			return;
		if( $this->LookupKey == $this->LookupName ) // selection only
			return;

		if( ! $v )
		{
			$this->FmtValue = '';
			return;
		}

		$rvx = &get_engine();
		$sql = "SELECT $this->LookupName FROM $this->LookupTable WHERE $this->LookupKey='$v'";
		$res = $rvx->Database->Retrieve( $sql );
		if( $res != NULL )
		{
			$this->FmtValue = $res;
		}
		else
		{
			// field has invalid lookup key
			$this->FmtValue = rvx_lang( '#!# Lookup' );
		}
	}

//=============================================================================
	function SaveLookup( $v )
//=============================================================================
	{
		$rvx = &get_engine();

		if( $this->LookupTable == '' )
			return;
		if( $this->LookupKey == $this->LookupName ) // selection only
			return;
		if( $this->LookupFree )	// no refferential integrity check
			return;

		if( $v == '' )  // null value
			return;

		// quang_vn wants exact match
		// $v = strtoupper($v);
		// $sql = "SELECT $this->LookupKey FROM $this->LookupTable WHERE UPPER($this->LookupName)='$v'";

		$sql = "SELECT $this->LookupKey FROM $this->LookupTable WHERE {$this->LookupName}='$v'";
		$res = $rvx->Database->Retrieve( $sql );
		if( $res != NULL )
		{
			$this->NewValue = $res;
		}
		else
		{
			rvx_error( 'Field has invalid lookup value: %s [%s]', $this->Caption, $v );
		}
	}

//=============================================================================
	function Validate()
//=============================================================================
	{
		//UTF8: mb_convert_case($this->NewValue, MB_CASE_UPPER, "UTF-8");

		if( $this->UpperCase )
			$this->NewValue = strtoupper($this->NewValue);

		if( $this->Mandatory )
			$this->ValidateMandatory();

		if( $this->Unique )
			$this->ValidateUnique();

		if( ( $this->LookupTable != '' ) && ( $this->LookupKey == $this->LookupName ) )
			$this->ValidateLookup();
	}


//=============================================================================
	function ValidateMandatory()
//=============================================================================
	{
		if( $this->NewValue == '' )
			rvx_error( 'Field is mandatory: %s', $this->Caption );

		if( ( $this->NewValue == 0 ) && rvx_is_number( $this->FieldType ) )
			rvx_error( 'Field is mandatory: %s', $this->Caption );
	}

//=============================================================================
	function ValidateUnique()
//=============================================================================
	{
		$rvx = & get_engine();
		$fld = $this->FieldName;
		$tbl = $this->Model->TableName;
		$key = $this->Model->TableKey;
		$prm[$fld] = $this->NewValue;
		$prm[$key] = $this->Model->GetField( $key );
		if( $fld == $key )
			return;

		$sql = "SELECT $fld FROM $tbl WHERE $fld=:$fld AND $key<>:$key";
		$res = $rvx->Database->Retrieve( $sql, $prm );
		if( $res != NULL )
			rvx_error( 'Field must be unique: %s', $this->Caption );
	}

//=============================================================================
	function ValidateLookup()
//=============================================================================
	{
		if( $this->NewValue == '' )
			return;
		if( $this->LookupFree )
			return;

		$excluded_lookups = array('City','Region','Country');
		if( in_array( $this->LookupTable, $excluded_lookups ) )
			return;

		$rvx = & get_engine();
		$sql = "SELECT $this->LookupKey FROM $this->LookupTable WHERE $this->LookupKey=:LookupKey";
		$res = $rvx->Database->Retrieve( $sql, array('LookupKey'=>$this->NewValue) );
		if( $res == NULL )
		{
			rvx_error( 'Field has invalid lookup value: %s [%s]', $this->Caption, $this->NewValue );
		}
	}

//=============================================================================
	function LoadCombo( $v )
//=============================================================================
	{
		$this->FmtValue = '';
		if( $v == 0 )
			return;

		$pieces = explode( "|", $this->LookupCombo );
		if( $v > count( $pieces ) )
			return;
		$this->FmtValue = $pieces[ $v-1 ];
	}

//=============================================================================
	function SaveCombo( $v )
//=============================================================================
	{
		if( $v == '' )
		{
			$this->NewValue = 0;
			return;
		}

		$pieces = explode( "|", $this->LookupCombo );
		if( in_array( $v, $pieces ) )
		{
			$this->NewValue = array_search( $v, $pieces ) + 1;
		}
		else
		{
			rvx_error( 'Field has invalid lookup value: %s [%s]', $this->Caption, $v );
		}
	}

//=============================================================================
	function GetPrecision()
//=============================================================================
	{
		if( $this->Precision )
			return $this->Precision;

		if( $this->FieldType == FLD_NUMBER )
			return FMT_NUMBER_DECIMALS;

		if( $this->FieldType == FLD_MONEY  )
			return FMT_MONEY_DECIMALS;

		return 0;
	}
}


//=============================================================================
class RModelRelation
//=============================================================================
{
	var $ForeignTbl;
	var $ForeignKey;
	var $FieldKey;

	function LoadXml( $rel )
	{
		$attrs = $rel->attributes();

		$this->ForeignTbl = (string)$attrs['table'];
		$this->ForeignKey = (string)$attrs['key'];
		$this->FieldKey   = (string)$attrs['field'];
		if( $this->FieldKey == '' )
			$this->FieldKey = 'Id';
	}
}


?>