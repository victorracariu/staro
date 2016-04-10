<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

echo "Grid_FetchUrl = '";
echo $grid->FetchUrl;
echo "';\r\nGrid_SaveUrl  = '";
echo $grid->SaveUrl;
echo "';\r\nGrid_ModelUrl = '";
echo $grid->ModelUrl;
echo "';\r\nGrid_Width    = ";
echo $grid->Width;
echo ";\r\nGrid_Height   = ";
echo $grid->Height;
echo ";\r\nGrid_ColSpan  = ";
echo $grid->ColSpan;
echo ";\r\nvar Grid_DataRecord = new Ext.data.Record.create([";
echo $grid->HtmlFields;
echo "]);\r\nvar Grid_DataReader = new Ext.data.JsonReader({root:'results', totalProperty:'total', id:'id'}, Grid_DataRecord);\r\nvar Grid_DataStore  = new Ext.data.Store({proxy: new Ext.data.HttpProxy({url:Grid_FetchUrl, method:'POST'}),baseParams:{parentid:rvxFormKey},reader:Grid_DataReader});\r\nvar Grid_ColModel   = new Ext.grid.ColumnModel([new Ext.grid.RowNumberer(), ";
echo $grid->HtmlColumns;
echo "]);\r\nvar Grid_Toolbar    = '';\r\nif( Grid_ModelUrl )\r\n{\r\n\tGrid_Toolbar = new Ext.Toolbar({items:[{text: 'Export Excel', icon: 'img/page_save.png', cls: 'x-btn-text-icon', handler: Grid_HandleExportExcel }]});} function Grid_ValidateEdit( e ){var rec = Grid.getStore().getAt(e.row);if(!rec) {Ext.Rvx.ShowError( rvx_locale.txtSelectRecord );\r\n\t\treturn;\r\n\t}\r\n\tExt.Ajax.request({\r\n\t\turl:";
echo " Grid_SaveUrl,\r\n\t\tparams: { \r\n\t\t\t'gridkey': rec.data['Id'],\r\n\t\t\t'gridrow': e.row,\r\n\t\t\t'gridfield': e.field,\r\n\t\t\t'gridvalue': e.value\r\n\t\t},\r\n\t\tfailure: function(response,options) {\r\n\t\t\tExt.Rvx.ShowError( response.responseText );\r\n\t\t\treturn false;\r\n\t\t},                                      \r\n\t\tsuccess: function(response,options) {\r\n\t\t\tvar res = {};\r\n\t\t\ttry {\r\n\t\t\t\tres = Ext.decode( response.responseT";
echo "ext );\r\n\t\t\t} catch(e) {\r\n\t\t\t\tExt.Rvx.ShowError( response.responseText );\t\r\n\t\t\t\treturn false;\r\n\t\t\t}\r\n\t\t\t\r\n\t\t\tvar res = {};\r\n\t\t\ttry {\r\n\t\t\t\tres = Ext.decode( response.responseText );\r\n\t\t\t} catch(e) {\r\n\t\t\t\tExt.Rvx.ShowError( response.responseText );\t\r\n\t\t\t\treturn false;\r\n\t\t\t}\r\n\t\t\t\r\n\t\t\tvar rec = Grid.getStore().getAt( e.row );\r\n\t\t\trec.set( e.field, e.value );\r\n\t\t\t\r\n\t\t\tif( res.fields && res.values ) \r\n\t\t";
echo "\t{\r\n\t\t\t\tfor( var i = 0; i < res.fields.length; i++ ) \r\n\t\t\t\t{\r\n\t\t\t\t\trec.set( res.fields[i], res.values[i] );\r\n\t\t\t\t}\r\n\t\t\t}\t\t\t\r\n\t\t\trec.commit();\r\n\t\t\t\r\n\t\t\t// update other controls depending on the focused one\r\n\t\t\tif( res.controls && res.captions ) \r\n\t\t\t{\r\n\t\t\t\tfor( var i = 0; i < res.controls.length; i++ ) \r\n\t\t\t\t{\r\n\t\t\t\t\tvar ctrl = Ext.getCmp( res.controls[i] );\r\n\t\t\t\t\tif( ctrl ) \r\n\t\t\t\t\t{\r\n\t\t\t\t\t\tctrl.setVa";
echo "lue( res.captions[i] );\r\n\t\t\t\t\t\tctrl.value    = res.captions[i];  \r\n\t\t\t\t\t\tctrl.oldvalue = res.captions[i];\r\n\t\t\t\t\t}\r\n\t\t\t\t}\r\n\t\t\t} \t\t\t\t\r\n\t\t}                                   \r\n\t});\r\n};\r\n\t\t\r\nGrid = new Ext.grid.EditorGridPanel({ id:'Grid', title:'', height: Grid_Height, width: Grid_Width, \r\n\tcolModel:Grid_ColModel, \r\n\tstore:Grid_DataStore, \r\n\tcolspan:Grid_ColSpan,\r\n\tclicksToEdit:1,\r\n\ttbar:Grid_Toolbar";
echo ",\r\n\tplugins:[";
echo $grid->HtmlPlugins;
echo "]});
        //Grid.on('beforeedit', Grid_Edit, this, false);
       Grid.on('validateedit', Grid_ValidateEdit, this, false);
Grid_DataStore.load({params: {start:0, limit:1000}});";
?>
