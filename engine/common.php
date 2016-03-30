<?php

//=============================================================================
function rvx_lang( $msg )
//=============================================================================
{
    $rvx =& get_engine();
    if( $rvx->Language )
        return $rvx->Language->Translate( $msg );
    else
        return $msg;
}

//=============================================================================
function rvx_log( $msg )
//=============================================================================
{
    $rvx =& get_engine();
    return $rvx->Log->Write( $msg );
}

function rvx_simple_error($msg)
{
    echo $msg;

    $rvx =& get_engine();

    if( ! $rvx->Database )
        return;

    // rollback database transactions
    $rvx->Database->Rollback();

    exit;
}

//=============================================================================
function rvx_error( $msg )
//=============================================================================
{
    /**
     * Function is called from API scope.
     *
     * Throws an API\Exception with the message.
     */
    if( defined('RVX_API_SCOPE') )
    {
        $rvx =& get_engine();
        $msg = rvx_lang( $msg );

        if( func_num_args() > 1 )
        {
            $arg = func_get_args();
            $msg = array_shift($arg);
            $msg = rvx_lang( $msg );
            $err = vsprintf( $msg, $arg );
        }
        else
        {
            $err = $msg;
        }

        if( ! $rvx->Database )
            return;

        // rollback database transactions
        $rvx->Database->Rollback();

        // throw exception
        throw new API\Exception( $err );

        return;
    }

    // function is called from RVX scope
    $rvx =& get_engine();
    $msg = rvx_lang( $msg );

    if( func_num_args() > 1 )
    {
        $arg = func_get_args();
        $msg = array_shift($arg);
        $msg = rvx_lang( $msg );
        $err = vsprintf( $msg, $arg );
    }
    else
    {
        $err = $msg;
    }

    if( ! $rvx->Database )
        return;

    // rollback database transactions
    $rvx->Database->Rollback();

    // show error
    echo $rvx->Exception->ShowError( rvx_lang('Error'), $err );

    // log error
    rvx_log( '[ERR] '.$err );

    if( DEBUG_MODE )
        echo $rvx->Exception->PrintDebugCallstack();

    exit;
}


//=============================================================================
function rvx_exception_handler( $severity, $message, $filepath, $line )
//=============================================================================
{
    // ignore PHP warnings
    if( $severity == E_STRICT )
        return;

    // rollback database tranasactions
    $rvx =& get_engine();
    $rvx->Database->Rollback();

    // show error
    echo $rvx->Exception->ShowPhpError( $severity, $message, $filepath, $line );
    exit;
}

//=============================================================================
function base_root()
//=============================================================================
{
    if( !isset($_SERVER['HTTP_HOST']) )
        return false;

    $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != 'off'  || $_SERVER['SERVER_PORT'] == 443 ) ? 'https://' : 'http://';
    $hostname = $_SERVER['HTTP_HOST'];
    $basepath = substr($_SERVER['SCRIPT_NAME'], 0, strrpos($_SERVER['SCRIPT_NAME'], "/")+1);
    return $protocol . $hostname . $basepath;
}

//=============================================================================
function base_url()
//=============================================================================
{
    if( !isset($_SERVER['HTTP_HOST']) )
        return false;

    $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != 'off' || $_SERVER['SERVER_PORT'] == 443 ) ? 'https://' : 'http://';
    $hostname = $_SERVER['HTTP_HOST'];
    $basepath = substr($_SERVER['SCRIPT_NAME'], 0, strrpos($_SERVER['SCRIPT_NAME'], "/")+1);
    return $protocol . $hostname . $basepath . 'index.php?';
}


//=============================================================================
function rvx_json_encode( $arr )
//=============================================================================
{
    if( version_compare(PHP_VERSION,"5.2","<") )
    {
        require_once( RVXPATH."json.php" );
        $json = new Services_JSON();
        return $json->encode($arr);
    }
    else
    {
        return json_encode($arr);
    }
}

//=============================================================================
function rvx_json_success()
//=============================================================================
{
    echo "{success:true}";
}

//=============================================================================
function rvx_validate_control( $flds )
//=============================================================================
{
    $controls = array();
    $captions = array();
    foreach( $flds as $fld => $val )
    {
        $controls[] = $fld;
        $captions[] = $val;
    }
    $data['success']  = true;
    $data['controls'] = $controls;
    $data['captions'] = $captions;
    echo rvx_json_encode( $data );
}

//=============================================================================
function rvx_validate_error( $ctrl, $msg )
//=============================================================================
{
    $data['success'] = false;
    $data['control'] = $ctrl;
    $data['message'] = $msg;
    echo rvx_json_encode( $data );
}

//=============================================================================
function rvx_is_number( $type )
//=============================================================================
{
    $number_types = array( FLD_INTEGER, FLD_NUMBER, FLD_MONEY );
    return in_array( $type, $number_types );
}

//=============================================================================
function rvx_safenr( $v )
//=============================================================================
{
    return ( $v != '' ) ? $v : 0;
}

//=============================================================================
function rvx_box_begin( $title )
//=============================================================================
{
    $title = rvx_lang( $title );
    $page_title = $title;
    include( RVXPATH.'header_box.php' );
    if( $title )
    {
        echo '<h2>'.$title.'</h2><hr>'."\n";
    }
    flush();
    //ob_flush();
}

//=============================================================================
function rvx_box_msg( $msg )
//=============================================================================
{
    echo rvx_lang( $msg )."<br>\n";
    flush();
    return true;
}

//=============================================================================
function rvx_box_success( $link = '' )
//=============================================================================
{
    if( $link ) {
        echo '<br><font color="green"><b><a href="index.php?'.$link.'">'.rvx_lang('Finished').'</a></b></font>'."<br>\n";
    } else {
        echo '<br><font color="green"><b>'.rvx_lang('Finished').'</b></font>'."<br>\n";
    }
    flush();
    //ob_flush();
    return true;
}


//=============================================================================
function rvx_box_error( $msg, $goback = false )
//=============================================================================
{
    // log error
    rvx_log( '[ERR] '.$msg );
    echo '<font color="red"><b>'.$msg.'</b></font><br>'."\n";
    if( $goback )
    {
        echo '<a href="javascript:history.back()">'.rvx_lang('Back').'</a>'."\n";
    }
    flush();
    //ob_flush();
    return false;
}

//=============================================================================
function rvx_wanted( $value, $msg )
//=============================================================================
{
    if( !isset($value) || ( $value == 0 ) )
        rvx_error( $msg );
}

//=============================================================================
function rvx_table_begin( $title, $cols, $lens = array() )
//=============================================================================
{
    $colw = 100 / count($cols);
    $html = '<br><table id="rvx_table">';
    if( $title )
        $html.= '<tr><th colspan="'.count($cols).'">'.$title.'</th></tr>';

    $html.= '<tr>';
    for( $i = 0; $i < count($cols); $i++ )
    {
        $col = $cols[$i];
        $len = $colw;
        $alg = 'left';

        if( array_key_exists( $i, $lens ) )
            $len = $lens[$i];

        $html.= '<th width="'.$len.'%">'.rvx_lang($col).'</th>';
    }
    $html.= '</tr>';
    echo $html."\n";
}

//=============================================================================
function rvx_table_row( $cols, $color = '', $algs = array() )
//=============================================================================
{
    $html = '<tr>';
    if( $color )
        $html = '<tr style="background:'.$color.'">';

    $i = 0;
    foreach( $cols as $fld => $val )
    {
        $alg = array_key_exists( $i, $algs ) ? $algs[$i++] : 'left';

        $html.='<td align="'.$alg.'">'.$val.'</td>';
    }
    $html.= '</tr>';
    echo $html."\n";
    flush();
    //ob_flush();
}

//=============================================================================
function rvx_table_end()
//=============================================================================
{
    echo '</table><br>'."\n";
}

//=============================================================================
function rvx_upload_file( $ctrl, $dst_file = '' )
//=============================================================================
{
    $rvx =& get_engine();
    if( isset( $_FILES ) == 0  )
        return rvx_error( 'Select a file to upload' );

    if( $_FILES[ $ctrl ]['name'] == '' )
        return rvx_error( 'Select a file to upload' );

    $src_file = $_FILES[ $ctrl ]['name'];
    $src_temp = $_FILES[ $ctrl ]['tmp_name'];
    $src_info = pathinfo($src_file);

    // create today folder
    $dir = 'pub/'.date('Ymd').'/';
    if( ! file_exists( $dir ) )
        mkdir( $dir );

    // upload in today folder
    if( $dst_file == '' )
        $dst_file = $dir.$rvx->Context->Username.'_'.date('Ymdhis').'.'.$src_info['extension'];

    if( $src_file == ''  )
        return rvx_error( 'Please upload a file' );

    if( ! rvx_upload( $src_temp, $dst_file ) )
        return false;

    return $dst_file;
}

//=============================================================================
function rvx_upload( $src, $dst )
//=============================================================================
{
    $blacklist = array(".php", ".phtml", ".php3", ".php4", ".php5");
    foreach( $blacklist as $item )
    {
        if( preg_match( "/$item\$/i", $dst ) )
            return rvx_error( 'Cannot upload PHP files' );
    }
    if( ! move_uploaded_file( $src, $dst ) )
        return rvx_error( 'Cannot upload file: %s', $src );

    return true;
}

//=============================================================================
function rvx_alert( $msg )
//=============================================================================
{
    $msg = rvx_lang($msg);
    echo "<script>alert( '$msg' );</script>\n";
}

function show_error( $msg )
{
    rvx_error($msg);
}

?>