<?php

include( "header.php" );
echo "\r\n";
echo "<s";
echo "cript type=\"text/javascript\" src=\"js/miframe.js\"></script>\r\n";
echo "<s";
echo "cript type=\"text/javascript\">\r\nExt.onReady(function(){\r\n\tExt.History.init();\t\r\n\t\r\n\tvar baseurl = '";
echo base_url( );
echo "';\r\n\tvar pagetitle = '";
echo $page_title;
echo "';\r\n\tvar pageperiod = '";
echo $page_period;
echo "';\r\n\tvar pagecompany = '<b>";
echo $page_company;
echo "</b>';\r\n\t\r\n\tExt.BLANK_IMAGE_URL = 'js/resources/images/default/s.gif';\r\n\tExt.QuickTips.init();\r\n\tvar rvxActiveLink = baseurl+'welcome';\r\n\tvar rvxActiveTitle = rvx_locale.txtWelcome;\r\n\t\r\n\tvar TopMenu = new Ext.Toolbar({\r\n\t\titems:[\r\n\t\t{ id:'menuWelcome', text:rvx_locale.txtHome, handler: OnMenuClick },\r\n\t\t";
echo $page_menu;
echo "\t\t{ id:'menuExit', text:rvx_locale.txtExit, handler:OnMenuClick },\r\n\t\t'->', \r\n\t\t{ id:'menuHome', text:pagecompany, menu:[\r\n\t\t\t{ id:'menuDashboard', text:rvx_locale.txtDashboard, handler: OnMenuClick },'-'\r\n\t\t\t
]}, ]});\r\n\t\r\n\tfunction ChangePage( url )\r\n\t{\r\n\t\tvar mainframe = Ext.getCmp('mainframe');\r\n\t\tmainframe.setSrc( url );\r\n\t\trvxActiveLink = url;\r\n\t\t\r\n\t\tvar i = url.indexOf(\"?\");\r\n\t\tif( i >= 0 ) {\r\n\t\t\tExt.History.add( url.substr(i+1) )";
echo ";\r\n\t\t}\r\n\t\t\r\n\t}\r\n\r\n\tfunction OnMenuClick(btn) \r\n\t{\r\n\t\tif( btn.id == 'menuWelcome' ) { \r\n\t\t\tChangePage( baseurl+'welcome' );\r\n\t\t}\r\n\t\tif( btn.id == 'menuDashboard' ) { \r\n\t\t\tChangePage( baseurl+'dashboard' );\r\n\t\t}\r\n\t\telse if( btn.id == 'menuExplorer' ) { \r\n\t\t\tChangePage( baseurl+'explorer' );\r\n\t\t}\r\n\t\telse if( btn.id == 'menuChangePass' ) { \r\n\t\t\tlocation.href = baseurl+'admin/changepass';\r\n\t\t}\r\n\t\telse ";
echo "if( btn.id == 'menuExit' ) { \r\n\t\t\tlocation.href = baseurl+'logout';\r\n\t\t}\r\n\t\telse if( btn.id == 'menuPeriod' ) {\r\n\t\t\tChangePage( baseurl + 'admin/period/select' );\r\n\t\t\t//Ext.Rvx.PopupWindow( 'index.php?admin/period/select', 450, 200 );\r\n\t\t}\r\n\t\telse {\r\n\t\t\tChangePage( btn.url );\r\n\t\t}\r\n    }\r\n\t\t\t\r\n\tvar TopPanel = new Ext.Panel({\r\n\t\tid:'TopPanel',\r\n\t\tregion:'north',\r\n\t\tmargins:'2 2 2 2',\r\n\t\theight:25,\r";
echo "\n\t\tborder:false,\r\n\t\titems:[TopMenu]\r\n\t});\r\n\t\r\n\tvar TreeMenu = new Ext.tree.TreePanel({\r\n\t\tid:'TreeMenu',\r\n\t\tanimate:true, \r\n\t\tautoScroll:true,\r\n\t\tloader:new Ext.tree.TreeLoader({dataUrl:baseurl+'main/fetchmenu/'}),\r\n\t\tcontainerScroll:true,\r\n\t\tborder:false,\r\n\t\trootVisible:false,\r\n\t\tregion:'west',\r\n\t\tautoScroll:true,\r\n\t\ttitle: '";
echo "',\r\n\t\tdeferredRender:false,\r\n\t\tcollapseMode:'mini',\r\n\t\tmargins:'0 0 2 2',\r\n\t\tsplit:true,\r\n\t\tcollapsible:true,\r\n\t\tborder:false,\r\n\t\twidth:200,\r\n\t\tminSize:150,\r\n\t\tmaxSize:300,\r\n\t\tlayout:'accordion',\r\n\t\tlayoutConfig:{\r\n\t\t\tanimate:true,\r\n\t\t\tsequence:true\r\n\t\t}\r\n\t});\r\n\t\r\n\tvar TreeRoot = new Ext.tree.AsyncTreeNode({\r\n\t\ttext:'Menu', \r\n\t\tdraggable:false,\r\n\t\tid:'root'\r\n\t});\r\n\tTreeMenu.setRootNode( TreeRoot )";
echo ";\r\n\t\t\t\t\t  \r\n\tvar CenterPanel = new Ext.Panel({\r\n\t\tid:'CenterPanel',\r\n\t\tregion:'center',\r\n\t\tmargins:'0 2 2 0',\r\n\t\tborder:false,\r\n\t\tlayout:'fit',\r\n\t\tdeferredRender:false,\r\n\t\titems:[{\r\n\t\t\txtype:'iframepanel',\r\n            id:'mainframe',\r\n\t\t\tborder:false,\r\n\t\t\tdefaultSrc:baseurl+'welcome',\t\r\n\t\t\tframeConfig:{name:'mainFrame'}\r\n\t\t}]\r\n\t});\r\n\t\r\n\tvar Viewport = new Ext.Viewport({\r\n\t\trenderTo:'rvxMain',\r\n \t";
echo "\tlayout:'border',\r\n\t\titems:[\r\n\t\t\tTopPanel,\r\n\t\t\tTreeMenu,\r\n\t\t\tCenterPanel\r\n\t\t]\r\n\t});\r\n\t\t\r\n\tTreeMenu.on( \"append\", function(TreePanel, parent, node, index) {\r\n\t\tnode.on( \"click\", function(n, ev) {\r\n\t\t\tif( node.leaf == false ){\r\n\t\t\t\treturn;\t\t\t\t\r\n\t\t\t}\t\r\n\t\t\tev.stopEvent();//don't open link in new window\r\n\t\t\t\r\n\t\t\tChangePage( node.attributes.href );\r\n\t\t\t\r\n\t\t\trvxActiveLink  = node.attributes.href;\r\n\t\t\trvx";
echo "ActiveTitle = node.attributes.text;\r\n\t\t});\r\n\t});\r\n\r\n\r\n\tExt.History.on('change', function(token){\r\n\t\tif(token) {\r\n\t\t\tnewActiveLink = 'index.php?' + token;\r\n\t\t\tif( rvxActiveLink != newActiveLink ) {\r\n\t\t\t\tChangePage( newActiveLink  );\r\n\t\t\t}\r\n\t\t}\r\n\t});\r\n\t\r\n\tif( top.location.hash != '' ) {\r\n\t\tChangePage( 'index.php?' + top.location.hash.substring(1) );\r\n\t}\r\n});\t\r\n\r\nfunction TriggerPeriodSelected( s )\r\n";
echo "{\r\n\tExt.getCmp('menuPeriod').setText( s );\r\n}\r\n</script>\r\n\t\r\n<div id=\"rvxMain\">\r\n\r\n<!-- Fields required for history management --> \r\n<form id=\"history-form\" class=\"x-hidden\"> \r\n    <input type=\"hidden\" id=\"x-history-field\" /> \r\n    <iframe id=\"x-history-frame\"></iframe> \r\n</form> \r\n\r\n</body>\r\n";
?>
