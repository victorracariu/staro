<?php

class RFormControl
{

    public $Box;
    public $Type;
    public $Name;
    public $Caption;
    public $FieldName;
    public $Value;
    public $Mandatory;
    public $UpperCase;
    public $Precision;
    public $LookupModel;
    public $LookupTable;
    public $LookupKey;
    public $LookupName;
    public $LookupUrl;
    public $ComboSelect;
    public $Width;
    public $Height;
    public $ColSpan;
    public $HideLabel;
    public $ReadOnly;
    public $ReadOnly2;
    public $OnValidate;
    public $SpecialTags;
    public $Handler;
    public $PosX;
    public $PosY;

    public function RFormControl( )
    {
        $this->Box = 1;
        $this->Type = FLD_STRING;
        $this->Mandatory = false;
        $this->UpperCase = false;
        $this->Precision = 2;
        $this->Width = 200;
        $this->Height = 50;
        $this->ColSpan = 1;
        $this->HideLabel = false;
        $this->ReadOnly = false;
        $this->ReadOnly2 = false;
        $this->OnValidate = false;
    }

    public function LoadXml( $ctrl )
    {
        $attrs = $ctrl->attributes( );
        $this->Box = (integer)$attrs['box'];;
        $this->Name = (string)$attrs['name'];;
        $this->Type = (string)$attrs['type'];;
        $this->FieldName = (string)$attrs['field'];
        $this->Caption = (string)$attrs['caption'];
        $this->Width = (integer)$attrs['width'];
        $this->Height = (integer)$attrs['height'];
        $this->HideLabel = (boolean)$attrs['hidelabel'];
        $this->ColSpan = (integer)$attrs['colspan'];
        $this->ReadOnly = (boolean)$attrs['readonly'];
        $this->ReadOnly2 = (integer)$attrs['readonly2'];
        $this->OnValidate = (boolean)$attrs['onvalidate'];
        $this->ComboSelect = (string)$attrs['comboselect'];
        $this->LookupUrl = (string)$attrs['lookupurl'];
        $this->PosX = (integer)$attrs['x'];
        $this->PosY = (integer)$attrs['y'];
        if ( $this->Name == "" )
        {
            $this->Name = $this->FieldName;
        }
        if ( $this->Caption == "" )
        {
            $this->Caption = $this->FieldName;
        }
        if ( $this->Type == "" )
        {
            $this->Type = CTRL_TEXT;
        }
        if ( $this->Width == 0 )
        {
            $this->Width = CTRL_WIDTH;
        }
        if ( $this->Box == "" )
        {
            $this->Box = 1;
        }
        $this->Caption = rvx_lang( $this->Caption );
        $this->ComboSelect = rvx_lang( $this->ComboSelect );
    }

    public function Render( )
    {
        if ( $this->Type == CTRL_LABEL )
        {
            return $this->RenderLabel( );
        }
        if ( $this->Type == CTRL_TEXT )
        {
            return $this->RenderText( );
        }
        if ( $this->Type == CTRL_PASS )
        {
            return $this->RenderPass( );
        }
        if ( $this->Type == CTRL_NUMBER )
        {
            return $this->RenderNumber( );
        }
        if ( $this->Type == CTRL_CHECK )
        {
            return $this->RenderCheck( );
        }
        if ( $this->Type == CTRL_MEMO )
        {
            return $this->RenderMemo( );
        }
        if ( $this->Type == CTRL_HIDDEN )
        {
            return $this->RenderHidden( );
        }
        if ( $this->Type == CTRL_HTML )
        {
            return $this->RenderHtmlEditor( );
        }
        if ( $this->ReadOnly )
        {
            if ( $this->LookupUrl )
            {
                $url = base_url( ).$this->LookupModel."/viewlink/fld/".$this->LookupName."/val/".$this->Value;
                $this->Caption = "<a target=_blank href=\"".$url."\">".$this->Caption."</a>";
            }
            return $this->RenderText( );
        }
        if ( $this->Type == CTRL_DATE )
        {
            return $this->RenderDate( );
        }
        if ( $this->Type == CTRL_TIME )
        {
            return $this->RenderTime( );
        }
        if ( $this->Type == CTRL_COMBO )
        {
            return $this->RenderCombo( );
        }
        if ( $this->Type == CTRL_LOOKUP )
        {
            return $this->RenderLookup( );
        }
        if ( $this->Type == CTRL_SELECT )
        {
            return $this->RenderSelect( );
        }
        if ( $this->Type == CTRL_UPLOAD )
        {
            return $this->RenderUpload( );
        }
        rvx_error( "form.xml: Invalid type [%s] for control [%s]", $this->Type, $this->Name );
    }

    public function RenderCommon( $extjs_class, $attributes )
    {
        $html = "var {$this->Name} = new {$extjs_class}({";
        $html .= "fieldLabel:'{$this->Caption}', name:'{$this->Name}', id:'{$this->Name}', value:'{$this->Value}', width:{$this->Width}";
        if ( 0 < strlen( $attributes ) )
        {
            $html .= ", {$attributes}";
        }
        $html .= $this->RenderAttributes( );
        $html .= "});\n";
        $html .= $this->RenderEvents( );
        return $html;
    }

    public function RenderAttributes( )
    {
        $html = "";
        if ( $this->Mandatory )
        {
            $html .= ", allowBlank:false";
        }
        if ( $this->UpperCase )
        {
            $html .= ", style:{textTransform: 'uppercase'}";
        }
        if ( $this->ReadOnly )
        {
            $html .= ", readOnly: true";
        }
        if ( $this->ReadOnly2 )
        {
            $html .= ", readOnly2: true";
        }
        if ( $this->HideLabel == true )
        {
            $html .= ", hideLabel: true";
        }
        if ( $this->ColSpan != "" )
        {
            $html .= ", colspan: {$this->ColSpan}";
        }
        if ( $this->PosX != "" )
        {
            $html .= ", x:{$this->PosX}";
        }
        if ( $this->PosY != "" )
        {
            $html .= ", y:{$this->PosY}";
        }
        return $html;
    }

    public function RenderLabel( )
    {
        $this->Value = "";
        $attrs = "text:'{$this->Caption}', cls: 'x-form-item-label x-form-item'";
        return $this->RenderCommon( "Ext.form.Label", $attrs );
    }

    public function RenderText( )
    {
        return $this->RenderCommon( "Ext.form.TextField", "" );
    }

    public function RenderPass( )
    {
        return $this->RenderCommon( "Ext.form.TextField", "inputType:'password'" );
    }

    public function RenderDate( )
    {
        $attr = "format:'d.m.Y'";
        return $this->RenderCommon( "Ext.form.DateField", $attr );
    }

    public function RenderTime( )
    {
        $attr = "format:'H:i'";
        return $this->RenderCommon( "Ext.form.TimeField", $attr );
    }

    public function RenderNumber( )
    {
        $attr = "decimalPrecision: {$this->Precision}, decimalSeparator:'.', itemCls:'rmoney'";
        return $this->RenderCommon( "Ext.form.NumberField", $attr );
    }

    public function RenderCheck( )
    {
        if ( $this->Value == "" )
        {
            $this->Value = 0;
        }
        $attr = "checked:".$this->Value;
        return $this->RenderCommon( "Ext.ux.XCheckbox", $attr );
    }

    public function RenderLookup( )
    {
        $url = base_url( ).$this->LookupModel;
        $url .= "/select";
        $url .= "/lkptrigger/{$this->Name}";
        $url .= "/lkpfield/{$this->LookupName}";
        $html = "{$this->Name}= new Ext.form.TriggerField({id:'{$this->Name}', name:'{$this->Name}', fieldLabel:'{$this->Caption}', value:'{$this->Value}'";
        if ( $this->Width != 0 )
        {
            $html .= ", width:{$this->Width}";
        }
        $html .= ", triggerClass:'x-form-search-trigger'";
        $html .= ", onTriggerClick: function() {HandleTrigger('{$url}');}";
        $html .= $this->RenderAttributes( );
        $html .= " });\n";
        $html .= $this->RenderEvents( );
        return $html;
    }

    public function RenderCombo( )
    {
        $url = base_url( ).$this->LookupModel;
        $url .= "/select";
        $url .= "/lkptrigger/{$this->Name}";
        $url .= "/lkpfield/{$this->LookupName}";
        $cbxStore = $this->Name."Store";
        $lkpKey = $this->LookupKey;
        $lkpName = $this->LookupName;
        $html = "var {$cbxStore} = new Ext.data.Store({";
        $html .= "proxy: new Ext.data.HttpProxy({url: '".base_url( ).$this->LookupModel."/combo/'}),";
        $html .= "baseParams: {lookupkey:'{$lkpKey}', lookupname: '{$lkpName}'},";
        $html .= "reader: new Ext.data.JsonReader( {totalProperty:'total', root:'results', id:'Id'}, [{name:'Id'}, {name:'Name'}] )";
        $html .= "});\n";
        $html .= "var {$this->Name} = new Ext.ux.TwinCombo";
        $html .= "({store:{$cbxStore}, valueField:'Id', displayField:'Name', ";
        $html .= "fieldLabel:'{$this->Caption}', name:'{$this->Name}', id:'{$this->Name}', value:'{$this->Value}', width:{$this->Width},";
        $html .= "mode:'remote', triggerAction:'all', typeAhead:true, emptyText:'', selectOnFocus:true, minChars:2";
        $html .= ", onTrigger2Click: function() {HandleTrigger('{$url}');}";
        $html .= $this->RenderAttributes( );
        $html .= "});\n";
        $html .= $this->RenderEvents( );
        return $html;
    }

    public function RenderSelect( )
    {
        $cbxStore = $this->Name."Store";
        $cbxValues = "";
        $pieces = explode( "|", $this->ComboSelect );
        $comma = "";
        foreach ( $pieces as $p )
        {
            if ( $p != "" )
            {
                $cbxValues .= $comma."['{$p}']";
                $comma = ",";
            }
        }
        $html = "var {$cbxStore} = new Ext.data.SimpleStore({ fields:['name'], data:[ {$cbxValues} ] });\n";
        $html .= "var {$this->Name} = new Ext.form.ComboBox({";
        $html .= "id:'{$this->Name}', name:'{$this->Name}', fieldLabel:'{$this->Caption}', value:'{$this->Value}', width:{$this->Width},";
        $html .= "store: {$cbxStore}, displayField:'name', mode:'local', triggerAction:'all', typeAhead:true, emptyText:'', selectOnFocus:true";
        $html .= $this->RenderAttributes( );
        $html .= "});\n";
        $html .= $this->RenderEvents( );
        return $html;
    }

    public function RenderMemo( )
    {
        if ( $this->Height == 0 )
        {
            $this->Height = 50;
        }
        $jsval = $this->Value;
        $jsval = preg_replace( "/\n/", "\\n", $jsval );
        $jsval = preg_replace( "/\r/", "\\r", $jsval );
        if ( $jsval == "" )
        {
            $jsval = " ";
        }
        $html = "var {$this->Name} = new Ext.form.TextArea({";
        $html .= "fieldLabel:'{$this->Caption}', name:'{$this->Name}', id:'{$this->Name}', width:{$this->Width}, height:{$this->Height}, value:'{$jsval}'";
        if ( $this->SpecialTags != "" )
        {
            $html .= ",".$this->SpecialTags;
        }
        $html .= $this->RenderAttributes( );
        $html .= "});\n";
        return $html;
    }

    public function RenderHidden( )
    {
        $html = "var {$this->Name} = new Ext.form.Hidden({";
        $html .= "id:'{$this->Name}', name:'{$this->Name}', value:'{$this->Value}'";
        $html .= "});\n";
        return $html;
    }

    public function RenderUpload( )
    {
        $attr = "inputType:'file'";
        return $this->RenderCommon( "Ext.form.TextField", $attr );
    }

    public function RenderEvents( )
    {
        $res = "";
        if ( $this->OnValidate )
        {
            $res .= $this->Name.".isblur=true;\n";
            $res .= "Ext.getCmp('{$this->Name}').on('blur', ControlValidate);\n";
            $res .= "Ext.getCmp('{$this->Name}').on('focus', ControlFocus);\n";
        }
        return $res;
    }

    public function RenderHtmlEditor( )
    {
        if ( $this->Height == 0 )
        {
            $this->Height = 50;
        }
        $jsval = $this->Value;
        $jsval = preg_replace( "/\n/", "\\n", $jsval );
        $jsval = preg_replace( "/\r/", "\\r", $jsval );
        if ( $jsval == "" )
        {
            $jsval = " ";
        }
        $html = "var {$this->Name} = new Ext.form.HtmlEditor({";
        $html .= "fieldLabel:'{$this->Caption}', name:'{$this->Name}', id:'{$this->Name}', width:{$this->Width}, height:{$this->Height}, value:'{$jsval}'";
        if ( $this->SpecialTags != "" )
        {
            $html .= ",".$this->SpecialTags;
        }
        $html .= $this->RenderAttributes( );
        $html .= "});\n";
        return $html;
    }

}

?>
