<?php

class Logout
{

    public function Index( )
    {
        $security = new RSecurity( );
        $security->Logout( );
        $this->Index( );
    }

}

?>
