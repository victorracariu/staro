<?php


class RMain
{

    public function RMain( )
    {
        $rvx =& get_engine( );
        $rvx->Security->Login( );
        $rvx->Context->Load( );
    }

    public function Index( )
    {
        $rvx =& get_engine( );
        $page_username = $rvx->Context->Username;
        $page_company = $rvx->Context->Company." [".$rvx->Context->Username."]";
        $page_language = $rvx->Language->Code;
        $page_title = $page_company;
        if ( $rvx->Context->IsAdmin( ) )
        {
            $page_company = $rvx->Context->Company." [<font color=\"red\">".$rvx->Context->Username."</font>]";
        }
        $mainmenu = $rvx->LoadManager( "Main_Menu" );
        $page_menu = $mainmenu->BuildMainMenu( );
        include_once( RVXPATH."main_page.php" );
    }

    public function FetchMenu( )
    {
        $rvx =& get_engine( );
        $parent = $rvx->Input->Post( "node" );
        $mainmenu = $rvx->LoadManager( "Main_Menu" );
        $mainmenu->FetchMenu( $parent );
    }

    public function BuildSwitchMenu( )
    {
        $rvx =& get_engine( );
        $menu = "";
        $i = 0;
        $comma = "";
        include_once( RVXPATH."connection.php" );
        $conn = new RConnection( );
        foreach ( $conn->Connections as $c )
        {
            $menu .= $comma."\n{ id:'menuSwitch".$i."', text:'".$c->Name."', handler: function() { location.href = baseurl+'".__FILE__."/switchdb/x/company/".$i."';} }";
            $comma = ",";
            ++$i;
        }
        echo "{ id:'menuSwitch', text:rvx_locale.txtSwitchDb, menu:[".$menu."] }";
    }

    public function BuildCreateMenu( )
    {
        $rvx =& get_engine( );
        echo "{ id:'menuCreate', text:rvx_locale.txtCreateDb, handler: function() { location.href = baseurl+'connect/';} }";
    }

    public function SwitchDb( )
    {
        $rvx =& get_engine( );
        $rvx->Session->SessionRead( );
        $i = $rvx->Context->GetParam( "company" );
        include_once( RVXPATH."connection.php" );
        $conn = new RConnection( );
        $comp = $conn->Connections[$i]->Name;
        $user = $rvx->Session->GetUserData( "username" );
        $pass = $rvx->Session->GetUserData( "password" );
        $lang = $rvx->Session->GetUserData( "language" );
        $rvx->Security->LoginCompany( $comp, $user, $pass, $lang, TRUE );
        $rvx->Session->SetUserData( array(
            "userid" => $this->UserId( $user )
        ) );
        $rvx->Context->Load( );
        $rvx->Router->Redirect( __FILE__ );
    }

    public function UserId( $user )
    {
        $rvx =& get_engine( );
        $sql = "SELECT Id FROM User WHERE Username = :Username";
        $params = array(
            "Username" => $user
        );
        return $rvx->Database->Retrieve( $sql, $params );
    }

}

?>
