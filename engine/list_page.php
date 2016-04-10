<?php

include( "header.php" );
echo "\r\n";
echo "<s";
echo "cript type=\"text/javascript\" src=\"js/menu/EditableItem.js\"></script>\r\n";
echo "<s";
echo "cript type=\"text/javascript\" src=\"js/menu/RangeMenu.js\"></script>\r\n";
echo "<s";
echo "cript type=\"text/javascript\" src=\"js/grid/GridFilters.js\"></script>\r\n";
echo "<s";
echo "cript type=\"text/javascript\" src=\"js/grid/filter/Filter.js\"></script>\r\n";
echo "<s";
echo "cript type=\"text/javascript\" src=\"js/grid/filter/StringFilter.js\"></script>\r\n";
echo "<s";
echo "cript type=\"text/javascript\" src=\"js/grid/filter/DateFilter.js\"></script>\r\n";
echo "<s";
echo "cript type=\"text/javascript\" src=\"js/grid/filter/ListFilter.js\"></script>\r\n";
echo "<s";
echo "cript type=\"text/javascript\" src=\"js/grid/filter/NumericFilter.js\"></script>\r\n";
echo "<s";
echo "cript type=\"text/javascript\" src=\"js/grid/filter/BooleanFilter.js\"></script>\r\n\r\n";
echo "<s";
echo "cript type=\"text/javascript\">\r\nvar rvxGrid;\r\nvar rvxGridTitle = '";
echo $view->Title;
echo "';\r\nvar rvxRecord  = Ext.data.Record.create([";
echo $view->ExtJsRecord( );
echo "]);\t\r\nvar rvxReader  = new Ext.data.JsonReader({root:'results', totalProperty:'total',id:'id'}, rvxRecord);\r\nvar rvxModel   = new Ext.grid.ColumnModel([new Ext.grid.PagedRowNumberer(), ";
echo $view->ExtJsModel( );
echo "]);\r\nvar rvxFilters = new Ext.ux.grid.GridFilters({filters:[ ";
echo $view->ExtJsFilters( );
echo " ]});\r\nvar rvxGridUrl = '";
echo $view->GetModelUrl( );
echo "';\r\nvar rvxParams  = ";
echo $view->HtmlParams;
echo ";\r\nvar baseurl    = '";
echo base_url( );
echo "';\r\nvar rvxLookupField   = '";
if ( isset( $lookupfield ) )
{
    echo $lookupfield;
}
echo "';\r\nvar rvxLookupTrigger = '";
if ( isset( $lookuptrigger ) )
{
    echo $lookuptrigger;
}
echo "';\r\nvar rvxLookupGrid    = '";
if ( isset( $lookupgrid ) )
{
    echo $lookupgrid;
}
echo "';\r\nvar rvxFormWidth  = ";
echo $view->FormWidth;
echo ";\r\nvar rvxFormHeight = ";
echo $view->FormHeight;
echo ";\r\nvar rvxSearchCols = [ ";
echo $view->ExtJsSearchColumns( );
echo " ];\r\nvar rvxSearchFirst = '";
echo $view->ExtJsFirstColumn( );
echo "';\r\nvar rvxActionMenus = ";
echo $view->HtmlActions;
echo ";\r\nvar rvxGridPageSize = ";
echo $this->PageSize;
echo ";\r\nvar rvxModelName = '";
echo $rvx->Context->Model;
echo "';\r\n</script>\r\n\r\n";
echo "<s";
echo "cript type=\"text/javascript\" src=\"js/rvx_list.js\"></script>\r\n\r\n";
echo "<s";
echo "cript type=\"text/javascript\">\r\nExt.onReady(function(){\r\nExt.BLANK_IMAGE_URL = 'js/resources/images/default/s.gif';\r\nExt.QuickTips.init();\t\r\nrvxRunList();\t\r\n});\r\n\r\n// refresh grid after updating a record\r\nvar rvxTriggerStore;\r\nfunction TriggerRefresh() \r\n{\r\n\trvxTriggerStore.reload();\r\n}\r\n</script>\r\n\r\n<body>\r\n</body>\r\n</html>";
?>
