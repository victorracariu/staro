Ext.BLANK_IMAGE_URL="../../resources/images/default/s.gif";Ext.Updater.defaults.loadScripts=true;Ext.Rvx=function(){var b;function a(c,d){return['<div class="msg">','<div class="x-box-tl"><div class="x-box-tr"><div class="x-box-tc"></div></div></div>','<div class="x-box-ml"><div class="x-box-mr"><div class="x-box-mc"><h3>',c,"</h3>",d,"</div></div></div>",'<div class="x-box-bl"><div class="x-box-br"><div class="x-box-bc"></div></div></div>',"</div>"].join("")}return{ShowInfo:function(f,e){if(!b){b=Ext.DomHelper.insertFirst(document.body,{id:"msg-div"},true)}b.alignTo(document,"t-t");var d=String.format.apply(String,Array.prototype.slice.call(arguments,1));var c=Ext.DomHelper.append(b,{html:a(f,d)},true);c.pause(1).ghost("t",{remove:true})},ReturnFocus:function(){if(typeof(rvxFocusedCtrl)=="undefined"){return true}var c=Ext.getCmp(rvxFocusedCtrl);if(c){c.focus(true)}},ShowError:function(c){Ext.Msg.show({title:rvx_locale.txtError,msg:c,buttons:Ext.Msg.OK,width:400,minWidth:400,icon:Ext.MessageBox.ERROR,fn:Ext.Rvx.ReturnFocus})},ShowWarning:function(c){Ext.Msg.show({title:rvx_locale.txtWarning,msg:c,buttons:Ext.Msg.OK,width:400,minWidth:400,icon:Ext.MessageBox.WARNING,fn:Ext.Rvx.ReturnFocus})},CheckJson:function(d){var c=Object();if((d.responseText.charAt(0)!="{")&&(d.responseText.charAt(0)!="[")){d.invalidDataFormat=true;return c}var e=Ext.decode(d.responseText);if(e.success!=true){return c}c.success=true;return c},PopupWindow:function(e,f,c){win="win"+Math.round(Math.random()*100000);str="location=1,status=1,resizable=1, scrollbars=1";if(window.screen){var h=window.screen.availWidth-10;var d=window.screen.availHeight-30;var g=(h-f)/2;var i=(d-c)/2;if(f==999){f=window.screen.availWidth;g=0}if(c==999){c=window.screen.availHeight;i=0}str+=",width="+f+",height="+c;str+=",left="+g;str+=",top="+i}window.open(e,win,str)},BlockEdit:function(d,e){if(d instanceof Ext.Component){var c=d.getEl();if(d.readOnly2){return}if(c){c.dom.setAttribute("readOnly",e);c.dom.readOnly=e}d.readOnly=e}if(d instanceof Ext.form.ComboBox){if(e){d.disable()}else{d.enable()}}}}}();Ext.namespace("Ext.ux.layout");Ext.ux.layout.TableFormLayout=Ext.extend(Ext.layout.TableLayout,{renderAll:function(e,f){var b=e.items.items;for(var d=0,a=b.length;d<a;d++){var g=b[0];if(g&&(!g.rendered||!this.isValidParent(g,f))){this.renderItem(g,d,f)}}},renderItem:function(f,a,d){if(f&&!f.rendered){var e=this.getNextCell(f);var b=new Ext.Panel(Ext.apply(this.container.formConfig,{layout:"form",items:f,renderTo:e}))}}});Ext.Container.LAYOUTS.tableform=Ext.ux.layout.TableFormLayout;Ext.grid.CheckColumn=function(a){Ext.apply(this,a);if(!this.id){this.id=Ext.id()}this.renderer=this.renderer.createDelegate(this)};Ext.grid.CheckColumn.prototype={init:function(a){this.grid=a;this.grid.on("render",function(){var b=this.grid.getView();b.mainBody.on("mousedown",this.onMouseDown,this)},this)},onMouseDown:function(d,c){if(c.className&&c.className.indexOf("x-grid3-cc-"+this.id)!=-1){d.stopEvent();var b=this.grid.getView().findRowIndex(c);if(!this.grid.getColumnModel().isCellEditable(this.grid,1,b)){return false}var a=this.grid.store.getAt(b);a.set(this.dataIndex,1-a.data[this.dataIndex]);d.field=this.dataIndex;d.value=a.data[this.dataIndex];d.row=b;this.grid.fireEvent("validateedit",d)}},renderer:function(b,c,a){c.css+=" x-grid3-check-col-td";return'<div class="x-grid3-check-col'+(b==1?"-on":"")+" x-grid3-cc-"+this.id+'">&#160;</div>'}};Ext.ux.TwinCombo=Ext.extend(Ext.form.ComboBox,{initComponent:function(){this.triggerConfig={tag:"span",cls:"x-form-twin-triggers",cn:[{tag:"img",src:Ext.BLANK_IMAGE_URL,cls:"x-form-trigger "+this.trigger1Class},{tag:"img",src:Ext.BLANK_IMAGE_URL,cls:"x-form-trigger "+this.trigger2Class}]};this.onTrigger2Click=this.onTrigger2Click.createInterceptor(function(){this.collapse()});Ext.ux.TwinCombo.superclass.initComponent.call(this)},getTrigger:Ext.form.TwinTriggerField.prototype.getTrigger,initTrigger:Ext.form.TwinTriggerField.prototype.initTrigger,onTrigger1Click:Ext.form.ComboBox.prototype.onTriggerClick,trigger1Class:Ext.form.ComboBox.prototype.triggerClass,trigger2Class:"x-form-search-trigger"});function formatBoolean(a,b){b.css+=" x-grid3-check-col-td";return'<div class="x-grid3-check-col'+(a?"-on":"")+'"></div>'}Ext.grid.PagedRowNumberer=function(a){Ext.apply(this,a);if(this.rowspan){this.renderer=this.renderer.createDelegate(this)}};Ext.grid.PagedRowNumberer.prototype={header:"",width:35,sortable:false,fixed:false,hideable:false,dataIndex:"",id:"numberer",rowspan:undefined,renderer:function(c,f,a,g,d,b){if(this.rowspan){f.cellAttr='rowspan="'+this.rowspan+'"'}var e=b.lastOptions.params.start;if(isNaN(e)){e=0}e=e+g+1;e=Number(e).toLocaleString();return e}};