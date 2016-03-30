<?php

include_once( RVXPATH.'controller.php' );

class Item_Category extends RController
{
	function Item_Category()
	{
		parent::RController();
	}

    function SaveCategory( $items )
    {
        $rvx =& get_engine();

        foreach( $items as $item )
        {
            $category = $rvx->Context->GetConfig( 'default_category' );

            // set subcategory
            $subcategory['Id'] = $item['Id'];
            $subcategory['Name'] = $item['SubcategoryName'];
            $subcategory['CategoryId'] = $category;

            $subcategory_id = $this->CheckSubcategoryUnique( $subcategory, 'Id' );
            if($subcategory_id)
            {
                $rvx->Database->Update( 'ItemSubcategory', $subcategory, 'Id', $subcategory['Id'] );
            }
            else{
                $rvx->Database->Insert( 'ItemSubcategory', $subcategory );
            }


            // set attribute
            $attribute['SubcategoryId'] = $item['Id'];
            $attribute['Name']          = $item['AttributeName'];
            $attribute['IsMandatory']   = strtolower(trim($item['IsMandatory'])) == 'ja'? 1 : 0;
            $attribute['IsFilter']      = strtolower(trim($item['IsFilter'])) == 'ja'? 1 : 0;

            $attribute_id = $this->CheckAttributeUnique( $attribute );
            if($attribute_id > 0)
            {
                $rvx->Database->Update( 'ItemAttribute', $attribute, 'Id', $attribute_id );
            }
            else {
                $rvx->Database->Insert( 'ItemAttribute', $attribute);
            }
        }
    }

    function CheckSubcategoryUnique( $value, $key )
    {
        $rvx =& get_engine();

        $sql = "SELECT ".$key."
                FROM ItemSubcategory
                WHERE ".$key." = '".$value[$key]."'";
        return $rvx->Database->Retrieve($sql);
    }

    function CheckAttributeUnique( $value )
    {
        $rvx =& get_engine();

        $sql = "SELECT Id
                FROM ItemAttribute
                WHERE SubcategoryId = '".$value['SubcategoryId']."'
                    AND Name = '".$value['Name']."'";
        return $rvx->Database->Retrieve($sql);
    }

}
?>