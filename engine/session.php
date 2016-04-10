<?php

class RSession
{

    public $Now;
    public $SessionLength = 604800;
    public $TimeToUpdate = 300;
    public $CookieName = "rvx_session";
    public $CookiePath = "/";
    public $CookieDomain = "";
    public $UserData = array( );

    public function RSession( )
    {
        $this->SessionRun( );
    }

    public function SessionRun( )
    {
        $this->Now = time( );
        if ( !$this->SessionRead( ) )
        {
            $this->SessionCreate( );
        }
        else
        {
            if ( $this->UserData['last_activity'] + $this->TimeToUpdate < $this->Now )
            {
                $this->SessionUpdate( );
            }
        }
    }

    public function SessionRead( )
    {
        $rvx =& get_engine( );
        $session = $rvx->Input->Cookie( $this->CookieName );
        if ( !$session )
        {
            return false;
        }
        $session_stripped = $this->StripSlashes( $session );
        $session = unserialize( $session_stripped );
        if ( !is_array( $session ) || !isset( $session['last_activity'] ) )
        {
            return FALSE;
        }
        if ( $session['last_activity'] + $this->SessionLength < $this->Now )
        {
            $this->SessionDestroy( );
            return FALSE;
        }
        if ( $session['ip_address'] != $rvx->Input->IpAddress( ) )
        {
            $this->SessionDestroy( );
            return FALSE;
        }
        if ( trim( $session['user_agent'] ) != trim( substr( $rvx->Input->UserAgent( ), 0, 50 ) ) )
        {
            $this->SessionDestroy( );
            return FALSE;
        }
        $this->UserData = $session;
        unset( $session );
        return true;
    }

    public function SessionWrite( )
    {
        $cookie_data = serialize( $this->UserData );
        setcookie( $this->CookieName, $cookie_data, time( ) + $this->SessionLength );
    }

    public function SessionCreate( )
    {
        $rvx =& get_engine( );
        $sessid = "";
        while ( strlen( $sessid ) < 32 )
        {
            $sessid .= mt_rand( 0, mt_getrandmax( ) );
        }
        $this->UserData = array( "session_id" => md5( uniqid( $sessid, TRUE ) ), "ip_address" => $rvx->Input->IpAddress( ), "user_agent" => substr( $rvx->Input->UserAgent( ), 0, 50 ), "last_activity" => $this->Now );
        $this->SessionWrite( );
    }

    public function SessionDestroy( )
    {
        setcookie( $this->CookieName, FALSE, time( ) - 31500000 );
    }

    public function SessionUpdate( )
    {
        $new_sessid = "";
        while ( strlen( $new_sessid ) < 32 )
        {
            $new_sessid .= mt_rand( 0, mt_getrandmax( ) );
        }
        $new_sessid = md5( uniqid( $new_sessid, TRUE ) );
        $this->UserData['session_id'] = $new_sessid;
        $this->UserData['last_activity'] = $this->Now;
        $this->SessionWrite( );
    }

    public function GetUserData( $item )
    {
        return !isset( $this->UserData[$item] ) ? false : $this->UserData[$item];
    }

    public function SetUserData( $newdata = array( ), $newval = "" )
    {
        if ( is_string( $newdata ) )
        {
            $newdata = array( $newdata => $newval );
        }
        if ( 0 < count( $newdata ) )
        {
            foreach ( $newdata as $key => $val )
            {
                $this->UserData[$key] = $val;
            }
        }
        $this->SessionWrite( );
    }

    public function StripSlashes( $vals )
    {
        if ( is_array( $vals ) )
        {
            foreach ( $vals as $key => $val )
            {
                $vals[$key] = $this->StripSlashes( $val );
            }
        }
        else
        {
            $vals = stripslashes( $vals );
        }
        return $vals;
    }

}

?>
