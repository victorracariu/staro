<?php

echo "function Grid_ValidateEdit( e )\r\n{\r\n\tvar rec = Grid.getStore().getAt(e.row);\t\r\n\tif(!rec) {\r\n\t\tExt.Rvx.ShowError( rvx_locale.txtSelectRecord );\r\n\t\treturn;\r\n\t}\r\n\tExt.Ajax.request({\r\n\t\turl: Grid_SaveUrl,\r\n\t\tparams: { \r\n\t\t\t'gridkey': rec.data['Id'],\r\n\t\t\t'gridrow': e.row,\r\n\t\t\t'gridfield': e.field,\r\n\t\t\t'gridvalue': e.value\r\n\t\t},\r\n\t\tfailure: function(response,options) {\r\n\t\t\tExt.Rvx.ShowError( response.re";
echo "sponseText );\r\n\t\t\treturn false;\r\n\t\t},                                      \r\n\t\tsuccess: function(response,options) {\r\n\t\t\tvar res = {};\r\n\t\t\ttry {\r\n\t\t\t\tres = Ext.decode( response.responseText );\r\n\t\t\t} catch(e) {\r\n\t\t\t\tExt.Rvx.ShowError( response.responseText );\t\r\n\t\t\t\treturn false;\r\n\t\t\t}\r\n\t\t\t\r\n\t\t\tvar res = {};\r\n\t\t\ttry {\r\n\t\t\t\tres = Ext.decode( response.responseText );\r\n\t\t\t} catch(e) {\r\n\t\t\t\tExt.Rvx.Show";
echo "Error( response.responseText );\t\r\n\t\t\t\treturn false;\r\n\t\t\t}\r\n\t\t\t\r\n\t\t\tvar rec = Grid.getStore().getAt( e.row );\r\n\t\t\trec.set( e.field, e.value );\r\n\t\t\t\r\n\t\t\tif( res.fields && res.values ) \r\n\t\t\t{\r\n\t\t\t\tfor( var i = 0; i < res.fields.length; i++ ) \r\n\t\t\t\t{\r\n\t\t\t\t\trec.set( res.fields[i], res.values[i] );\r\n\t\t\t\t}\r\n\t\t\t}\t\t\t\r\n\t\t\trec.commit();\r\n\t\t}                                   \r\n\t});\r\n};\r\n";
echo "function Grid_Edit(a){
        var b=Grid.getStore().getAt(a.row);
        if(!b){Ext.Rvx.ShowError(rvx_locale.txtSelectRecord);
                return
        }
        Ext.Ajax.request({
                url:Grid_ModelUrl+\"/xxx\",
                params:{lookupname:\"Name\", lookupkey:\"Id\", query:b.data.Id, gridkey:b.data.Id,gridrow:a.row,gridfield:a.field,gridvalue:a.value,parentid:Ext.getCmp(\"Id\").getValue()},
                failure:function(c,d){Grid.el.unmask();Ext.Rvx.ShowError(c.responseText);return false},success:function(c,d){Grid.el.unmask();var g={};try{g=Ext.decode(c.responseText)}catch(h){Ext.Rvx.ShowError(c.responseText);return false}var j=Grid.getStore().getAt(h.row);j.commit();if(g.fields&&g.values){var j=Grid.getStore().getAt(g.row);for(var f=0;f<g.fields.length;f++){j.set(g.fields[f],g.values[f])}j.commit()}ControlSuccess(c,d)}})}";
?>
