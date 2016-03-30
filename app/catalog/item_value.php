<?php

include_once( RVXPATH.'controller.php' );

class Item_Value extends RController
{
	function Item_Value()
	{
		parent::RController();
        $this->ModelClass = 'Item_Value_Model';
	}

	function GridSave()
	{
		$rvx =& get_engine();
		$fld = $rvx->Input->Post( 'gridfield', true );
		$key = $rvx->Input->Post( 'gridkey', true );
		$val = $rvx->Input->Post( 'gridvalue' );

        $rvx->Database->Update( 'ItemValue', array( $fld=>$val ), 'Id', $key );

        return rvx_json_success();
    }

    function GridLookup()
    {
        $rvx =& get_engine();
        $x = array('total'=>1, 'results' => array('Id' => '1', 'Name' => "1"));
        echo rvx_json_encode($x);
        //return rvx_json_success();
    }
}

class Item_Value_Model extends RModel
{
    function Item_Value_Model()
    {
        parent::RModel();
    }

    function CheckUnique()
    {
        $rvx =& get_engine();

        $itemid = $this->GetField('ItemId');
        $value_name = $this->GetField('Name');

        $sql = "SELECT Id
                FROM ItemValue
                WHERE ItemId = ".$itemid."
                AND Name = '".$value_name."'";
        $id  = $rvx->Database->Retrieve($sql);

        return $id;
    }

    function CheckValid($subcategory_id, $attribute_name)
    {
        $rvx =& get_engine();

        $sql = "SELECT Id
                FROM ItemAttribute
                WHERE SubcategoryId = ".$subcategory_id."
                AND Name = '".$attribute_name."'";
        $valid = $rvx->Database->Retrieve($sql);

        return $valid;
    }
}
?>