<?php


include_once( RVXPATH."controller.php" );
include_once( RVXPATH."main_menu.php" );
class Welcome extends RController
{

    public function Welcome( )
    {
        parent::rcontroller( );
        $rvx =& get_engine( );
        $rvx->Language->Load( "system", "modules" );
    }

    public function Index( )
    {
        $rvx =& get_engine( );
        $root = new RMain_Menu( );
        $menu = new RMain_Menu( );
        $root->LoadRoot( );
        echo "<link rel=\"stylesheet\" href=\"js/welcome.css\" type=\"text/css\" />\n";
        echo "<b>".$rvx->Context->Company." [".$rvx->Context->Username."] - ".date('Y')."</b>";
        foreach ( $root->Items as $root_item )
        {
            $rvx->Language->Load( $root_item->Id );
            $root_item->Title = rvx_lang( $root_item->Title );
            echo "<table><th>".$root_item->Title."</th><tr><td><div id=\"cpanel\">\n";
            echo "\n";
            $menu->LoadModule( $root_item->Id );
            foreach ( $menu->Items as $menu_item )
            {
                if ( $menu_item->Folder )
                {
                    continue;
                }
                $pic = $menu_item->Name;
                if ( !file_exists( "img/".$menu_item->Name.".png" ) )
                {
                    $pic = "general";
                }
                echo "<div style=\"float: left;\"><div class=\"icon\">";
                echo "<a href=\"index.php?".$root_item->Id."/".$menu_item->Name."\">";
                echo "<img src=\"img/".$pic.".png\" alt=\"".$menu_item->Title."\" align=\"middle\" border=\"0\">";
                echo "<span>".$menu_item->Title."</span></a></div></div>\n";
            }
            echo "</div></td></tr></table><br>";
        }
    }

}

?>
