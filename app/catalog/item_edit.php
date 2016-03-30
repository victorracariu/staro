<?php

include_once( RVXPATH.'controller.php' );

class Item_Edit extends RController
{
	function Item_Edit()
	{
		parent::RController();
        //$this->FormClass = 'Item_Edit_Model';
        $this->FormClass = 'Item_Edit_Form';
	}
}

class Item_Edit_Form extends RForm
{
    function Render()
    {
        $this->LoadTranslation();
        parent::Render();
    }
}