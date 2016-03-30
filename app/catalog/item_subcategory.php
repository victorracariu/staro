<?php

include_once( RVXPATH.'controller.php' );

class Item_Subcategory extends RController
{
	function Item_Subcategory()
	{
        $this->ModelClass = 'Item_Subcategory_Model';
		parent::RController();
	}

}

class Item_Subcategory_Model extends RModel
{
    function Item_Subcategory_Model()
    {
        parent::RModel();
    }

    function CheckUnique()
    {
        $rvx =& get_engine();

        $sql = "SELECT Id
                FROM ItemSubcategory WHERE Id = '".$this->GetField('Id')."'";
        $id  = $rvx->Database->Retrieve($sql);

        return $id;
    }
}
?>