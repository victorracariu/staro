<?php

include_once( RVXPATH.'controller.php' );
include_once( RVXPATH.'history_model.php' );
include_once( RVXPATH.'dialog.php' );
include_once( APPPATH.'catalog/item_mapping.php' );

class Item extends RController
{
	function Item()
	{
		parent::RController();
        $this->ModelClass = 'Item_Model';
        $this->FormClass = 'Item_Form';
        $this->ListClass = 'Item_List';
	}

    function SaveItem( $item )
    {
        $rvx =& get_engine();

        $mapping = new Item_Mapping();
        $mapping->LoadMapping();

        unset($item_model);
        $item_model = $this->CreateModel('catalog', 'item_model');
        $item_model->Load();

        unset($item['Errors']);

        unset($item_value_description);
        $item_value_description = array();

        $stop = 0;

        foreach( $item as $key => $value )
        {
            if(array_key_exists($key, $mapping->Mapping))
            {
                if( $mapping->Mapping[$key]['DbTable'] == 'Item' )
                {
                    $item_model->SetField($key, $value);
                }
                else{
                    $item_mapping = $mapping->GetMapping($key, $value);
                    $item_values = $mapping->MapFields($key, $item_mapping);

                    if( !$item_values['error'] )
                    {
                        $item_model->SetFields($item_values);
                    }else{
                        return $key.' '.$value.' NOT FOUND!';
                    }
                }
            }else{
                $item_value_description[] = array('Name'=>$key, 'Value'=>$value);
            }
        }

        // check unique item
        $id = $item_model->CheckUnique();
        $item_model->SetField('Id', $id);

        // save item
        $item_model->Save($id);
        $item_id = $item_model->GetField('Id');

        //item values model
        unset($item_value_model);
        $item_value_model = $rvx->CreateModel('catalog', 'item_value');

        foreach( $item_value_description as $row )
        {
            $valid = $item_value_model->CheckValid( $item_model->GetField('SubcategoryId'), $row['Name'] );

            if( !$valid )
                continue;

            $item_value_model->Load();

            $item_value_model->SetFields($row);
            $item_value_model->SetField('AttributeId', $valid);

            // set item value model the item id
            $item_value_model->SetField('ItemId', $item_id);

            // check unique attribute value
            $value_id = $item_value_model->CheckUnique();

            // save attribute value
            $item_value_model->Save($value_id);
            $rvx->Database->Commit();
        }
    }

    function EditTranslate()
    {
        $rvx =& get_engine();

        $dlg = new RDialog();
		$dlg->Init( 'Translate', base_url().'catalog/item/EditExecute' );
        $dlg->AddControl( CTRL_HIDDEN, 'Id', $rvx->Context->GetParam('id', true), true );
		$dlg->AddLookup( CTRL_COMBO, 'From', 'catalog/locale', 'Locale', 'Locale', '' );
        $dlg->AddLookup( CTRL_COMBO, 'To', 'catalog/locale', 'Locale', 'Locale', '' );
		$dlg->AddButton( BTN_SUBMIT );
		$dlg->Render();
    }

    function EditExecute()
    {
        $rvx =& get_engine();

        $id = $rvx->Input->Post('Id', true);
        $from = $rvx->Input->Post('From', true);
        $to = $rvx->Input->Post('To', true);

        if( $from == $to )
        {
            rvx_error('The values must be distinct');
        }

        $rvx->Router->Redirect('catalog/item/edit_view/id/'.$id.'/from/'.$from.'/to/'.$to);
    }

    function Edit_View()
    {
        parent::Edit();
    }
}

class Item_Model extends HistoryModel
{

    function CheckUnique()
    {
        $rvx =& get_engine();

        $sql = "SELECT Id
                FROM Item WHERE Code = '".$this->GetField('Code')."'";
        $id  = $rvx->Database->Retrieve($sql);

        return $id;
    }

    function OnBeforeSave($id)
    {
        $rvx =& get_engine();

        if(!$id)
            return true;

        $sql = "SELECT V.Value
                FROM ItemValue V, ItemAttribute A
                WHERE V.AttributeId = A.Id
                    AND (A.IsMandatory = 1 OR A.IsFilter = 1)
                    AND (V.Value IS NULL OR V.Value = '')
                    AND V.ItemId =".$id;
        $qry = $rvx->Database->QueryResult( $sql );

        if( empty($qry) )
        {
            $this->SetField('IsContent', 1);
        }
        else{
            $this->SetField('IsContent', 0);
        }
        
        parent::OnBeforeSave($id);
    }
}

class Item_Form extends RForm
{
    function Render()
    {
        $rvx =& get_engine();

        $from = $rvx->Context->GetParam('from');
        $to = $rvx->Context->GetParam('to');

        if($from != '' and $to != '')
        {
            foreach($this->Boxes as &$box)
            {
                if($box->Name == 'Box2')
                {
                    $box->GridModel = 'catalog/item_value_'.$from.'_'.$to;
                }
            }
        }
        parent::Render();
    }

    function RenderControl( $ctrl ) // override
    {
        if( $ctrl->Name == 'WordCount' )
		{
			//$this->HtmlControls .= file_get_contents( APPPATH.'catalog/item/js/wordcounter.js' );
		}
        return $ctrl->Render();
    }
}

class Item_List extends RList
{
	function FilterSql( $sql )
	{
        $rvx =& get_engine();

        $content = $rvx->Input->Post( 'content' );

        $sql = parent::FilterSql( $sql );

        $searchcol = $rvx->Input->Post('searchcol');
        $searchval = $rvx->Input->Post('searchval');
        $filter = $rvx->Input->Post('filter');

		$ana = new RSqlAnalyzer( $sql );

		if( $content != NULL )
        {
            $ana->AppendFilter( 'IsContent', FLD_INTEGER, $content );
        }

		$sql = $ana->BuildSql();

		return $sql;
	}
}

?>
