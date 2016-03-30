<?php

include_once RVXPATH.'controller.php';

class Item_Mapping extends RController
{
    var $Mapping;

    function Item_Mapping()
    {
        parent::RController();
    }

    function LoadMapping()
    {
        $rvx =& get_engine();

        $sql = "SELECT * FROM ItemMapping";
        $qry = $rvx->Database->QueryResult($sql);

        foreach( $qry as $row )
        {
            foreach($row as $key => $field)
            {
                $this->Mapping[$row['ExcelField']][$key] = $field;
            }
        }
    }

    function GetMapping($key, $value)
    {
        $rvx =& get_engine();

        $qry = array();
        if(array_key_exists($key, $this->Mapping))
        {
            if( $this->Mapping[$key]['DbTable'] == 'Item' )
            {
                return 'Item';
            }else{
                $sql = "SELECT *
                        FROM ".$this->Mapping[$key]['DbTable']."
                        WHERE ".$this->Mapping[$key]['DbField']." = '".mysql_escape_string($value)."'";
                $qry = $rvx->Database->QueryRow($sql);
            }
        }

        return $qry;
    }

    function MapFields( $key, $values )
    {
        switch ($key) {
            case 'Subcategory':
                    $ret = $this->MapSubcategory($values);
                break;
            case 'Brand':
                    $ret = $this->MapBrand($values);
                break;
            default:
                break;
        }

        return $ret;
    }

    function MapSubcategory($values)
    {
        $ret['CategoryId'] = $values['CategoryId'];
        $ret['SubcategoryId'] = $values['Id'];

        if($ret['SubcategoryId'] > 0  )
            return $ret;
        else
            return array('error' => 1);

    }

    function MapBrand($values)
    {
        $ret['BrandId'] = $values['Id'];

        if($ret['BrandId'] > 0  )
            return $ret;
        else
            return array('error' => 1);
    }
}

