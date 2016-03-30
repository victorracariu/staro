<?php

include_once( RVXPATH.'controller.php' );

class Item_Brand extends RController
{
	function Item_Brand()
	{
        $this->ModelClass = 'Item_Brand_Model';
		parent::RController();
	}

}

class Item_Brand_Model extends RModel
{
    function Item_Brand_Model()
    {
        parent::RModel();
    }

    function CheckUnique()
    {
        $rvx =& get_engine();

        $sql = "SELECT Id
                FROM ItemBrand WHERE Id = '".$this->GetField('Id')."'";
        $id  = $rvx->Database->Retrieve($sql);

        return $id;
    }
}
?>