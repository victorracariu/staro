<?php

include( "header.php" );
echo "\r\n";
echo "<s";
echo "cript type=\"text/javascript\" src=\"js/xcheckbox.js\"></script>\r\n";
echo "<s";
echo "cript type=\"text/javascript\">\r\nExt.onReady( function(){\r\n\r\nvar baseurl = '";
echo base_url( );
echo "';\r\nvar DialogTitle  = '";
echo $view->Title;
echo "';\r\nvar DialogAction = '";
echo $view->ActionUrl;
echo "';\r\nvar DialogColumns = ";
echo $view->Columns;
echo ";\r\nvar DialogMsgError = '";
echo $rvx->ErrorMessage;
echo "';\r\nvar RefreshOpener = ";
echo $view->RefreshOpener;
echo ";\r\nvar rvxFormKey = 0;\r\n\t\r\n// refresh list or grid beneath\r\nif( RefreshOpener )\r\n{\r\n\tif (window.opener && !window.opener.closed) {\r\n\t\twindow.opener.TriggerRefresh();\r\n\t}\r\n}\t\t\r\n\t\r\nExt.BLANK_IMAGE_URL = 'js/resources/images/default/s.gif';\t\r\nExt.QuickTips.init();\r\n\r\nvar rvxDialogReader = new Ext.data.JsonReader();\r\nrvxDialogReader.read = Ext.Rvx.CheckJson;\r\n\r\n\r\nvar rvxDialog = new Ext.FormPanel({\r\n\t";
echo "id:'rvxDialog',\r\n\ttitle:DialogTitle,\r\n\tframe:true,\r\n\turl:DialogAction+'/action/submit',\r\n\terrorReader:rvxDialogReader,\r\n\ttimeout:15000,\r\n\tbodyStyle:'padding:5px 5px 0',\r\n\tfileUpload:true,\r\n\tonSubmit:Ext.emptyFn\r\n});\r\n\r\nvar rvxBox = new Ext.form.FieldSet({\r\n\ttitle:'', \r\n\tautoHeight:true, \r\n\tstyle: 'vertical-align:top', \r\n\tcolspan:1, \r\n\tlayout:'tableform', \r\n\tlayoutConfig:{columns:DialogColumns},\r\n\t";
echo "formConfig:{bodyStyle:'padding:2px 10px', border:false,labelWidth:180}\r\n});\r\nvar btnSubmit = new Ext.form.Hidden({\r\n\tid:'Submit', \r\n\tname:'Submit', \r\n\tvalue:''\r\n})\r\n\r\n// display custom message text\r\nvar rvxMsgError = new Ext.Panel({\r\n\tname:'Error', \r\n\tautoWidth:true,\r\n\tautoHeight:true,\r\n\thideLabel:true,\r\n\treadOnly:true,\r\n\thtml:DialogMsgError,\r\n\tstyle:'border:1px solid red; padding:5px;'\r\n});\r\nif( ";
echo "DialogMsgError ) {\r\n\trvxDialog.add( rvxMsgError );\r\n}\r\nrvxDialog.add( rvxBox );\r\nrvxDialog.add( btnSubmit );\r\n\r\nfunction ControlFocus(o)\r\n{\r\n\t// save control original value\r\n\to.oldvalue = o.getValue();\r\n}\r\n\r\nfunction ControlValidate(o)\r\n{\r\n\tif( o.getValue() == '' ) \r\n\t\treturn;\r\n\tif( o.getValue() == o.oldvalue ) {\r\n\t\treturn;\r\n\t}\r\n\t\r\n\t// build list of control values prefixed by # (buggy submit)\r\n\tva";
echo "r ctrls = [ ";
echo $view->ControlNames;
echo " ];\r\n\tvar param = new Array();\r\n\tfor (var i = 0; i < ctrls.length; i++) {\r\n\t\tparam[ ctrls[i].name ] = '#' + ctrls[i].value;\r\n\t}\r\n\t\r\n\t// focused control\r\n\tparam['control'] = o.name;\r\n\tparam['value'] = o.getValue();\r\n\trvxDialog.el.mask('Validating...', 'x-mask-loading');\r\n\t\r\n\t// send them to the server\r\n\tExt.Ajax.request({ url: DialogAction+'/action/validate', params:param, success: OnControlSuccess,";
echo " failure: OnControlFailure });\r\n}\r\n\r\nfunction OnControlSuccess(response,options)\r\n{\r\n\trvxDialog.el.unmask();\r\n\t\r\n\t// catch possible error\r\n\ttry {\r\n\t\tvar msg = response.responseText;\r\n\t\tvar res = Ext.decode( msg );\r\n\t} catch(e) {\r\n\t\tif( msg == '' ) {\r\n\t\t\treturn true;\r\n\t\t}\r\n\t\tExt.Rvx.ShowError( msg );\r\n\t\treturn false;\r\n\t}\r\n\t\r\n\t// control value is not found/error\r\n\tif( !res.success && res.message && ";
echo "res.control )\r\n\t{\r\n\t\tvar ctrl = Ext.getCmp( res.control );\r\n\t\tif(ctrl) { \r\n\t\t\tctrl.focus(true);\r\n\t\t\tctrl.markInvalid( res.message );\r\n\t\t}\r\n\t\treturn false;\r\n\t}\r\n\t\r\n\t// update other controls depending on the focused one\r\n\tif( res.controls && res.captions ) \r\n\t{\r\n\t\tfor( var i = 0; i < res.controls.length; i++ ) \r\n\t\t{\r\n\t\t\tvar ctrl = Ext.getCmp( res.controls[i] );\r\n\t\t\tif( ctrl ) {\r\n\t\t\t\tctrl.setValue( re";
echo "s.captions[i] );\r\n\t\t\t\tctrl.oldvalue = res.captions[i];\r\n\t\t\t}\r\n\t\t}\r\n\t} \t\r\n\treturn true;\r\n}\r\n\r\nfunction OnControlFailure(response,options)\r\n{\r\n\tExt.Rvx.ShowError( response.responseText );\r\n}\r\n\r\n";
echo $view->ControlHtml;
echo $view->SpecialHtml;
echo "\t\r\nfunction HandleSubmit( btn )\r\n{\r\n\tbtnSubmit.setValue( btn.name );\r\n\trvxDialog.getForm().getEl().dom.action = rvxDialog.url;\r\n\trvxDialog.getForm().getEl().dom.submit();\t\r\n}\r\nfunction HandleCancel()\r\n{\r\n\thistory.back();\r\n}\r\nfunction HandleClose()\r\n{\r\n\twindow.close();\r\n}\r\n\r\nfunction HandleTrigger( url )\r\n{\r\n\tExt.Rvx.PopupWindow(url, 800, 500);\r\n\treturn true;\r\n}\r\n\r\nrvxDialog.render( 'rvxdialog' );\r";
echo "\n\r\nif( typeof(Grid) != 'undefined' ) \r\n{\r\n\tif( Grid.rendered ) \r\n\t{\r\n\t\tvar Grid_Resizable = new Ext.Resizable( 'Grid', {pinned:true, handles:'se'} );\r\n\t\tGrid_Resizable.on('resize', Grid.syncSize, Grid );\r\n\t}\r\n}\r\n\r\n});\r\n\r\nfunction TriggerValidate(o)\t// called by lookup forms\r\n{\r\n}\r\n\r\n\r\n</script>\r\n<div id='rvxdialog'></div> \r\n</body>";
?>
