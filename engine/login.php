<?php

class RLogin
{

    public $Company = NULL;
    public $Username = NULL;
    public $Password = NULL;
    public $Language = NULL;
    public $CompanyList = NULL;
    public $LanguageList = NULL;
    public $LanguageArray = array
    (
        "en" => "english"
    );

    public function RLogin( )
    {
        $rvx =& get_engine( );
        $rvx->Language->Load( "system", "login" );
    }

    public function Index( )
    {
        $rvx =& get_engine( );
        $rvx->Session->SessionDestroy( );
        $this->Company = $rvx->Input->Cookie( "rvx_company" );
        $this->Language = $rvx->Input->Cookie( "rvx_language" );
        $this->Username = $rvx->Input->Cookie( "rvx_username" );
        if ( $this->Language != "" && array_key_exists( $this->Language, $this->LanguageArray ) )
        {
            $page_language = $this->Language;
            $this->Language = $this->LanguageArray[$this->Language];
        }
        else
        {
            $page_language = "en";
            $this->Language = "english";
        }
        $comma = "";
        foreach ( $this->LanguageArray as $code => $name )
        {
            $this->LanguageList .= $comma."[\"".$name."\"]";
            $comma = ",";
        }
        $comma = "";
        $xml = simplexml_load_file( DATABASES_FILE );
        foreach ( $xml->database as $db )
        {
            $attrs = $db->attributes( );
            $this->CompanyList .= $comma."[\"".( string )$attrs['name']."\"]";
            $comma = ",";
        }
        if ( strlen( $this->CompanyList ) == 0 )
        {
            return $rvx->Router->Redirect( "connect" );
        }
        $view = $this;
        $page_title = "Login";
        include_once( RVXPATH."login_page.php" );
    }

    public function Check( )
    {
        $rvx =& get_engine( );
        $company = $rvx->Input->Post( "Company", true );
        $username = $rvx->Input->Post( "Username", true );
        $password = $rvx->Input->Post( "Password", true );
        $language = $rvx->Input->Post( "Language", true );

        $langkey = array_search( $language, $this->LanguageArray );
        if ( $langkey === FALSE )
        {
            return rvx_error( "Invalid language selected" );
        }
        $language = $langkey;
        $security = new RSecurity( );

        if ( $security->LoginCompany( $company, $username, $password, $language ) )
        {
            setcookie( "rvx_company", $company, time( ) + 60 * 60 * 24 * 7 );
            setcookie( "rvx_username", $username, time( ) + 60 * 60 * 24 * 7 );
            setcookie( "rvx_language", $language, time( ) + 60 * 60 * 24 * 7 );
            rvx_json_success( );
        }
    }

}

?>
