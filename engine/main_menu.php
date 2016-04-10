<?php

class RMainMenuItem
{

    public $Id;
    public $Name;
    public $Title;
    public $Folder;
    public $Url;
    public $UrlCustom;
    public $NoMenu;

}

class RMain_Menu
{

    public $Items = array( );

    public function FetchMenu( $parent_node )
    {
        if ( $parent_node == "root" )
        {
            $this->FetchRoot( );
        }
        else
        {
            $this->FetchModule( $parent_node );
        }
    }

    public function LoadRoot( )
    {
        $rvx =& get_engine( );
        $rvx->Language->Load( "system", "modules" );
        $xml = simplexml_load_file( "modules.xml" );
        foreach ( $xml->module as $x )
        {
            $attrs = $x->attributes( );
            $m = new RMainMenuItem( );
            $m->Id = (string)$attrs['id'];
            $m->Title = (string)$attrs['title'];
            $m->NoMenu = (boolean)$attrs['nomenu'];
            $m->Title = rvx_lang( $m->Title );
            $m->Folder = true;
            $this->Items[] = $m;
        }
    }

    public function FetchRoot( )
    {
        $this->LoadRoot( );
        $nodes = array( );
        foreach ( $this->Items as $item )
        {
            $nodes[] = array( "text" => "{$item->Title}", "id" => "{$item->Id}", "leaf" => false, "singleClickExpand" => true );
        }
        echo rvx_json_encode( $nodes );
    }

    public function LoadModule( $module )
    {
        $this->Items = array( );
        $rvx =& get_engine( );
        $rvx->Language->Load( $module );

        if ( file_exists( APXPATH.$module."/menu.xml" ) )
        {
            $xml = simplexml_load_file( APXPATH.$module."/menu.xml" );
        }
        else
        {
            $xml = simplexml_load_file( APPPATH.$module."/menu.xml" );
        }

        foreach ( $xml->menu as $x )
        {
            $attrs = $x->attributes( );
            $m = new RMainMenuItem( );

            $m->Id = (string)$attrs['id'];
            $m->Name = (string)$attrs['name'];
            $m->Title = (string)$attrs['title'];
            $m->Folder = (string)$attrs['folder'];
            $m->Url = (string)$attrs['url'];
            $m->NoMenu = ( boolean )$attrs['nomenu'];
            $m->UrlCustom = false;
            $m->Title = rvx_lang( $m->Title );

            if ( $m->Url == "" )
            {
                $m->Url = $module."/".$m->Name;
            }
            else
            {
                $m->UrlCustom = true;
            }
            $this->Items[] = $m;
        }
    }

    public function FetchModule( $parent_node )
    {
        $module = $parent_node;
        $n = strpos( $parent_node, "." );
        if ( $n != false )
        {
            $module = substr( $parent_node, 0, $n );
        }
        $this->LoadModule( $module );
        $nodes = array( );
        foreach ( $this->Items as $item )
        {
            $id = $item->Id;
            $parent = $module;
            $n = strrpos( $id, "." );
            if ( $n != false )
            {
                $parent .= ".".substr( $id, 0, $n );
            }
            if ( !( strcmp( $parent_node, $parent ) == 0 ) )
            {
                continue;
            }
            else if ( $item->Folder )
            {
                $nodes[] = array( "text" => "{$item->Title}", "id" => "{$module}.{$id}", "leaf" => false, "singleClickExpand" => true );
            }
            else if ( !$item->UrlCustom )
            {
                $nodes[] = array( "text" => "{$item->Title}", "id" => "{$module}.{$id}", "leaf" => true, "href" => "index.php?{$item->Url}" );
            }
            else
            {
                $nodes[] = array( "text" => "{$item->Title}", "id" => "{$module}.{$id}", "leaf" => true, "href" => "{$item->Url}" );
            }
        }
        echo rvx_json_encode( $nodes );
    }

    public function GetModuleCode( $title )
    {
        if ( $title == "" )
        {
            rvx_error( "Please select a module" );
        }
        $this->LoadRoot( );
        foreach ( $this->Items as $item )
        {
            if ( strcmp( $item->Title, $title ) == 0 )
            {
                return $item->Id;
                break;
            }
        }
        rvx_error( "Module code not found for [%s]", $title );
    }

    public function BuildMainMenu( )
    {
        $rvx =& get_engine( );
        $this->LoadRoot( );
        $roots = $this->Items;
        $this->Items = array( );
        $html = "";
        foreach ( $roots as $root )
        {
            if ( $root->NoMenu )
            {
                continue;
            }
            $this->LoadModule( $root->Id );
            $submenu = "";
            $comma = "";
            foreach ( $this->Items as $item )
            {
                if ( $item->NoMenu )
                {
                    continue;
                }
                if ( $item->Folder && $submenu == "" )
                {
                    continue;
                }
                $submenu .= $comma;
                if ( !$item->Folder )
                {
                    $submenu .= "{id:'menu_".$root->Id."_".$item->Id."',text:'".$item->Title."', url: 'index.php?{$item->Url}', handler:OnMenuClick}";
                }
                else
                {
                    $submenu .= "'-'";
                }
                $comma = ",";
            }
            $html .= "{id:'menu_{$root->Id}',text:'{$root->Title}', menu:[ {$submenu} ] },\n";
        }
        return $html;
    }

}

?>
