<?php

class Spreadsheet_Excel_Reader
{

    public $boundsheets = array( );
    public $formatRecords = array( );
    public $sst = array( );
    public $sheets = array( );
    public $data = NULL;
    public $pos = NULL;
    public $_ole = NULL;
    public $_defaultEncoding = NULL;
    public $_defaultFormat = Spreadsheet_Excel_Reader_DEF_NUM_FORMAT;
    public $_columnsFormat = array( );
    public $_rowoffset = 1;
    public $_coloffset = 1;
    public $dateFormats = array
    (
        14 => "d/m/Y",
        15 => "d-M-Y",
        16 => "d-M",
        17 => "M-Y",
        18 => "h:i a",
        19 => "h:i:s a",
        20 => "H:i",
        21 => "H:i:s",
        22 => "d/m/Y H:i",
        45 => "i:s",
        46 => "H:i:s",
        47 => "i:s.S"
    );
    public $numberFormats = array
    (
        1 => "%1.0f",
        2 => "%1.2f",
        3 => "%1.0f",
        4 => "%1.2f",
        5 => "%1.0f",
        6 => "\$%1.0f",
        7 => "\$%1.2f",
        8 => "\$%1.2f",
        9 => "%1.0f%%",
        10 => "%1.2f%%",
        11 => "%1.2f",
        37 => "%1.0f",
        38 => "%1.0f",
        39 => "%1.2f",
        40 => "%1.2f",
        41 => "%1.0f",
        42 => "\$%1.0f",
        43 => "%1.2f",
        44 => "\$%1.2f",
        48 => "%1.0f"
    );

    public function Spreadsheet_Excel_Reader( )
    {
        $this->_ole =& new OLERead( );
        $this->setUTFEncoder( "iconv" );
    }

    public function setOutputEncoding( $Encoding )
    {
        $this->_defaultEncoding = $Encoding;
    }

    public function setUTFEncoder( $encoder = "iconv" )
    {
        $this->_encoderFunction = "";
        if ( $encoder == "iconv" )
        {
            $this->_encoderFunction = function_exists( "iconv" ) ? "iconv" : "";
        }
        else if ( $encoder == "mb" )
        {
            $this->_encoderFunction = function_exists( "mb_convert_encoding" ) ? "mb_convert_encoding" : "";
        }
    }

    public function setRowColOffset( $iOffset )
    {
        $this->_rowoffset = $iOffset;
        $this->_coloffset = $iOffset;
    }

    public function setDefaultFormat( $sFormat )
    {
        $this->_defaultFormat = $sFormat;
    }

    public function setColumnFormat( $column, $sFormat )
    {
        $this->_columnsFormat[$column] = $sFormat;
    }

    public function read( $sFileName )
    {
        $errlevel = error_reporting( );
        error_reporting( $errlevel ^ E_NOTICE );
        $res = $this->_ole->read( $sFileName );
        if ( $res === false && $this->_ole->error == 1 )
        {
            exit( "The filename ".$sFileName." is not readable" );
        }
        $this->data = $this->_ole->getWorkBook( );
        $this->pos = 0;
        $this->_parse( );
        error_reporting( $errlevel );
    }

    public function _parse( )
    {
        $pos = 0;
        $code = ord( $this->data[$pos] ) | ord( $this->data[$pos + 1] ) << 8;
        $length = ord( $this->data[$pos + 2] ) | ord( $this->data[$pos + 3] ) << 8;
        $version = ord( $this->data[$pos + 4] ) | ord( $this->data[$pos + 5] ) << 8;
        $substreamType = ord( $this->data[$pos + 6] ) | ord( $this->data[$pos + 7] ) << 8;
        if ( $version != Spreadsheet_Excel_Reader_BIFF8 && $version != Spreadsheet_Excel_Reader_BIFF7 )
        {
            return false;
        }
        if ( $substreamType != Spreadsheet_Excel_Reader_WorkbookGlobals )
        {
            return false;
        }
        $pos += $length + 4;
        $code = ord( $this->data[$pos] ) | ord( $this->data[$pos + 1] ) << 8;
        $length = ord( $this->data[$pos + 2] ) | ord( $this->data[$pos + 3] ) << 8;
        while ( $code != Spreadsheet_Excel_Reader_Type_EOF )
        {
            switch ( $code )
            {
            case Spreadsheet_Excel_Reader_Type_SST :
                $spos = $pos + 4;
                $limitpos = $spos + $length;
                $uniqueStrings = $this->_GetInt4d( $this->data, $spos + 4 );
                $spos += 8;
                $i = 0;
                while ( $i < $uniqueStrings )
                {
                    if ( $spos == $limitpos )
                    {
                        $opcode = ord( $this->data[$spos] ) | ord( $this->data[$spos + 1] ) << 8;
                        $conlength = ord( $this->data[$spos + 2] ) | ord( $this->data[$spos + 3] ) << 8;
                        if ( $opcode != 60 )
                        {
                            return 0 - 1;
                        }
                        $spos += 4;
                        $limitpos = $spos + $conlength;
                    }
                    $numChars = ord( $this->data[$spos] ) | ord( $this->data[$spos + 1] ) << 8;
                    $spos += 2;
                    $optionFlags = ord( $this->data[$spos] );
                    ++$spos;
                    $asciiEncoding = ( $optionFlags & 1 ) == 0;
                    $extendedString = ( $optionFlags & 4 ) != 0;
                    $richString = ( $optionFlags & 8 ) != 0;
                    if ( $richString )
                    {
                        $formattingRuns = ord( $this->data[$spos] ) | ord( $this->data[$spos + 1] ) << 8;
                        $spos += 2;
                    }
                    if ( $extendedString )
                    {
                        $extendedRunLength = $this->_GetInt4d( $this->data, $spos );
                        $spos += 4;
                    }
                    $len = $asciiEncoding ? $numChars : $numChars * 2;
                    if ( $spos + $len < $limitpos )
                    {
                        $retstr = substr( $this->data, $spos, $len );
                        $spos += $len;
                    }
                    else
                    {
                        $retstr = substr( $this->data, $spos, $limitpos - $spos );
                        $bytesRead = $limitpos - $spos;
                        $charsLeft = $numChars - ( $asciiEncoding ? $bytesRead : $bytesRead / 2 );
                        $spos = $limitpos;
                        while ( 0 < $charsLeft )
                        {
                            $opcode = ord( $this->data[$spos] ) | ord( $this->data[$spos + 1] ) << 8;
                            $conlength = ord( $this->data[$spos + 2] ) | ord( $this->data[$spos + 3] ) << 8;
                            if ( $opcode != 60 )
                            {
                                return 0 - 1;
                            }
                            $spos += 4;
                            $limitpos = $spos + $conlength;
                            $option = ord( $this->data[$spos] );
                            $spos += 1;
                            if ( $asciiEncoding && $option == 0 )
                            {
                                $len = min( $charsLeft, $limitpos - $spos );
                                $retstr .= substr( $this->data, $spos, $len );
                                $charsLeft -= $len;
                                $asciiEncoding = true;
                            }
                            else if ( !$asciiEncoding && $option != 0 )
                            {
                                $len = min( $charsLeft * 2, $limitpos - $spos );
                                $retstr .= substr( $this->data, $spos, $len );
                                $charsLeft -= $len / 2;
                                $asciiEncoding = false;
                            }
                            else if ( !$asciiEncoding && $option == 0 )
                            {
                                $len = min( $charsLeft, $limitpos - $spos );
                                $j = 0;
                                while ( $j < $len )
                                {
                                    $retstr .= $this->data[$spos + $j].chr( 0 );
                                    ++$j;
                                }
                                $charsLeft -= $len;
                                $asciiEncoding = false;
                            }
                            else
                            {
                                $newstr = "";
                                $j = 0;
                                while ( $j < strlen( $retstr ) )
                                {
                                    $newstr = $retstr[$j].chr( 0 );
                                    ++$j;
                                }
                                $retstr = $newstr;
                                $len = min( $charsLeft * 2, $limitpos - $spos );
                                $retstr .= substr( $this->data, $spos, $len );
                                $charsLeft -= $len / 2;
                                $asciiEncoding = false;
                            }
                            $spos += $len;
                        }
                    }
                    $retstr = $asciiEncoding ? $retstr : $this->_encodeUTF16( $retstr );
                    if ( $richString )
                    {
                        $spos += 4 * $formattingRuns;
                    }
                    if ( $extendedString )
                    {
                        $spos += $extendedRunLength;
                    }
                    $this->sst[] = $retstr;
                    ++$i;
                }
                break;
                switch ( $code )
                {
                case Spreadsheet_Excel_Reader_Type_FILEPASS :
                    return false;
                case Spreadsheet_Excel_Reader_Type_NAME :
                    break;
                default :
                    switch ( $code )
                    {
                    case Spreadsheet_Excel_Reader_Type_FORMAT :
                        $indexCode = ord( $this->data[$pos + 4] ) | ord( $this->data[$pos + 5] ) << 8;
                        if ( $version == Spreadsheet_Excel_Reader_BIFF8 )
                        {
                            $numchars = ord( $this->data[$pos + 6] ) | ord( $this->data[$pos + 7] ) << 8;
                            if ( ord( $this->data[$pos + 8] ) == 0 )
                            {
                                $formatString = substr( $this->data, $pos + 9, $numchars );
                            }
                            else
                            {
                                $formatString = substr( $this->data, $pos + 9, $numchars * 2 );
                            }
                        }
                        else
                        {
                            $numchars = ord( $this->data[$pos + 6] );
                            $formatString = substr( $this->data, $pos + 7, $numchars * 2 );
                        }
                        $this->formatRecords[$indexCode] = $formatString;
                        break;
                    default :
                        switch ( $code )
                        {
                        case Spreadsheet_Excel_Reader_Type_XF :
                            $indexCode = ord( $this->data[$pos + 6] ) | ord( $this->data[$pos + 7] ) << 8;
                            if ( array_key_exists( $indexCode, $this->dateFormats ) )
                            {
                                $this->formatRecords['xfrecords'][] = array(
                                    "type" => "date",
                                    "format" => $this->dateFormats[$indexCode]
                                );
                            }
                            else if ( array_key_exists( $indexCode, $this->numberFormats ) )
                            {
                                $this->formatRecords['xfrecords'][] = array(
                                    "type" => "number",
                                    "format" => $this->numberFormats[$indexCode]
                                );
                            }
                            else
                            {
                                $isdate = FALSE;
                                if ( 0 < $indexCode )
                                {
                                    if ( isset( $this->formatRecords[$indexCode] ) )
                                    {
                                        $formatstr = $this->formatRecords[$indexCode];
                                    }
                                    if ( $formatstr && preg_match( "/[^hmsday\\/\\-:\\s]/i", $formatstr ) == 0 )
                                    {
                                        $isdate = TRUE;
                                        $formatstr = str_replace( "mm", "i", $formatstr );
                                        $formatstr = str_replace( "h", "H", $formatstr );
                                    }
                                }
                                if ( $isdate )
                                {
                                    $this->formatRecords['xfrecords'][] = array(
                                        "type" => "date",
                                        "format" => $formatstr
                                    );
                                }
                                else
                                {
                                    $this->formatRecords['xfrecords'][] = array(
                                        "type" => "other",
                                        "format" => "",
                                        "code" => $indexCode
                                    );
                                }
                            }
                            break;
                        default :
                            switch ( $code )
                            {
                            case Spreadsheet_Excel_Reader_Type_NINETEENFOUR :
                                $this->nineteenFour = ord( $this->data[$pos + 4] ) == 1;
                                break;
                            default :
                                switch ( $code )
                                {
                                case Spreadsheet_Excel_Reader_Type_BOUNDSHEET :
                                    $rec_offset = $this->_GetInt4d( $this->data, $pos + 4 );
                                    $rec_typeFlag = ord( $this->data[$pos + 8] );
                                    $rec_visibilityFlag = ord( $this->data[$pos + 9] );
                                    $rec_length = ord( $this->data[$pos + 10] );
                                    if ( $version == Spreadsheet_Excel_Reader_BIFF8 )
                                    {
                                        $chartype = ord( $this->data[$pos + 11] );
                                        if ( $chartype == 0 )
                                        {
                                            $rec_name = substr( $this->data, $pos + 12, $rec_length );
                                        }
                                        else
                                        {
                                            $rec_name = $this->_encodeUTF16( substr( $this->data, $pos + 12, $rec_length * 2 ) );
                                        }
                                    }
                                    else if ( $version == Spreadsheet_Excel_Reader_BIFF7 )
                                    {
                                        $rec_name = substr( $this->data, $pos + 11, $rec_length );
                                    }
                                    $this->boundsheets[] = array(
                                        "name" => $rec_name,
                                        "offset" => $rec_offset
                                    );
                                }
                            }
                        }
                    }
                }
            }
            $pos += $length + 4;
            $code = ord( $this->data[$pos] ) | ord( $this->data[$pos + 1] ) << 8;
            $length = ord( $this->data[$pos + 2] ) | ord( $this->data[$pos + 3] ) << 8;
        }
        foreach ( $this->boundsheets as $key => $val )
        {
            $this->sn = $key;
            $this->_parsesheet( $val['offset'] );
        }
        return true;
    }

    public function _parsesheet( $spos )
    {
        $cont = true;
        $code = ord( $this->data[$spos] ) | ord( $this->data[$spos + 1] ) << 8;
        $length = ord( $this->data[$spos + 2] ) | ord( $this->data[$spos + 3] ) << 8;
        $version = ord( $this->data[$spos + 4] ) | ord( $this->data[$spos + 5] ) << 8;
        $substreamType = ord( $this->data[$spos + 6] ) | ord( $this->data[$spos + 7] ) << 8;
        if ( $version != Spreadsheet_Excel_Reader_BIFF8 && $version != Spreadsheet_Excel_Reader_BIFF7 )
        {
            return 0 - 1;
        }
        if ( $substreamType != Spreadsheet_Excel_Reader_Worksheet )
        {
            return 0 - 2;
        }
        $spos += $length + 4;
        while ( $cont )
        {
            $lowcode = ord( $this->data[$spos] );
            if ( $lowcode == Spreadsheet_Excel_Reader_Type_EOF )
            {
                break;
            }
            $code = $lowcode | ord( $this->data[$spos + 1] ) << 8;
            $length = ord( $this->data[$spos + 2] ) | ord( $this->data[$spos + 3] ) << 8;
            $spos += 4;
            $this->sheets[$this->sn]['maxrow'] = $this->_rowoffset - 1;
            $this->sheets[$this->sn]['maxcol'] = $this->_coloffset - 1;
            unset( $FN_41129880['rectype'] );
            $this->multiplier = 1;
            switch ( $code )
            {
            case Spreadsheet_Excel_Reader_Type_DIMENSION :
                if ( !isset( $this->numRows ) )
                {
                    if ( $length == 10 || $version == Spreadsheet_Excel_Reader_BIFF7 )
                    {
                        $this->sheets[$this->sn]['numRows'] = ord( $this->data[$spos + 2] ) | ord( $this->data[$spos + 3] ) << 8;
                        $this->sheets[$this->sn]['numCols'] = ord( $this->data[$spos + 6] ) | ord( $this->data[$spos + 7] ) << 8;
                    }
                    else
                    {
                        $this->sheets[$this->sn]['numRows'] = ord( $this->data[$spos + 4] ) | ord( $this->data[$spos + 5] ) << 8;
                        $this->sheets[$this->sn]['numCols'] = ord( $this->data[$spos + 10] ) | ord( $this->data[$spos + 11] ) << 8;
                    }
                }
                break;
            default :
                switch ( $code )
                {
                case Spreadsheet_Excel_Reader_Type_MERGEDCELLS :
                    $cellRanges = ord( $this->data[$spos] ) | ord( $this->data[$spos + 1] ) << 8;
                    $i = 0;
                    while ( $i < $cellRanges )
                    {
                        $fr = ord( $this->data[$spos + 8 * $i + 2] ) | ord( $this->data[$spos + 8 * $i + 3] ) << 8;
                        $lr = ord( $this->data[$spos + 8 * $i + 4] ) | ord( $this->data[$spos + 8 * $i + 5] ) << 8;
                        $fc = ord( $this->data[$spos + 8 * $i + 6] ) | ord( $this->data[$spos + 8 * $i + 7] ) << 8;
                        $lc = ord( $this->data[$spos + 8 * $i + 8] ) | ord( $this->data[$spos + 8 * $i + 9] ) << 8;
                        if ( 0 < $lr - $fr )
                        {
                            $this->sheets[$this->sn]['cellsInfo'][$fr + 1][$fc + 1]['rowspan'] = $lr - $fr + 1;
                        }
                        if ( 0 < $lc - $fc )
                        {
                            $this->sheets[$this->sn]['cellsInfo'][$fr + 1][$fc + 1]['colspan'] = $lc - $fc + 1;
                        }
                        ++$i;
                    }
                    break;
                default :
                    switch ( $code )
                    {
                    case Spreadsheet_Excel_Reader_Type_RK :
                    case Spreadsheet_Excel_Reader_Type_RK2 :
                        $row = ord( $this->data[$spos] ) | ord( $this->data[$spos + 1] ) << 8;
                        $column = ord( $this->data[$spos + 2] ) | ord( $this->data[$spos + 3] ) << 8;
                        $rknum = $this->_GetInt4d( $this->data, $spos + 6 );
                        $numValue = $this->_GetIEEE754( $rknum );
                        if ($this->isDate($spos)) {
                             list($string, $raw) = $this->createDate($numValue);
                        }
                        else
                        {
                            $raw = $numValue;
                            if ( isset( $this->_columnsFormat[$column + 1] ) )
                            {
                                $this->curformat = $this->_columnsFormat[$column + 1];
                            }
                            $string = sprintf( $this->curformat, $numValue * $this->multiplier );
                        }
                        $this->addcell( $row, $column, $string, $raw );
                        break;
                    default :
                        switch ( $code )
                        {
                        case Spreadsheet_Excel_Reader_Type_LABELSST :
                            $row = ord( $this->data[$spos] ) | ord( $this->data[$spos + 1] ) << 8;
                            $column = ord( $this->data[$spos + 2] ) | ord( $this->data[$spos + 3] ) << 8;
                            $xfindex = ord( $this->data[$spos + 4] ) | ord( $this->data[$spos + 5] ) << 8;
                            $index = $this->_GetInt4d( $this->data, $spos + 6 );
                            $this->addcell( $row, $column, $this->sst[$index] );
                            break;
                        default :
                            switch ( $code )
                            {
                            case Spreadsheet_Excel_Reader_Type_MULRK :
                                $row = ord( $this->data[$spos] ) | ord( $this->data[$spos + 1] ) << 8;
                                $colFirst = ord( $this->data[$spos + 2] ) | ord( $this->data[$spos + 3] ) << 8;
                                $colLast = ord( $this->data[$spos + $length - 2] ) | ord( $this->data[$spos + $length - 1] ) << 8;
                                $columns = $colLast - $colFirst + 1;
                                $tmppos = $spos + 4;
                                $i = 0;
                                while ( $i < $columns )
                                {
                                    $numValue = $this->_GetIEEE754( $this->_GetInt4d( $this->data, $tmppos + 2 ) );
                                    if ( $this->isDate( $tmppos - 4 ) )
                                    {
                                        list($string, $raw) = $this->createDate($numValue);
                                    }
                                    else
                                    {
                                        $raw = $numValue;
                                        if ( isset( $this->_columnsFormat[$colFirst + $i + 1] ) )
                                        {
                                            $this->curformat = $this->_columnsFormat[$colFirst + $i + 1];
                                        }
                                        $string = sprintf( $this->curformat, $numValue * $this->multiplier );
                                    }
                                    $tmppos += 6;
                                    $this->addcell( $row, $colFirst + $i, $string, $raw );
                                    ++$i;
                                }
                                break;
                            default :
                                switch ( $code )
                                {
                                case Spreadsheet_Excel_Reader_Type_NUMBER :
                                    $row = ord( $this->data[$spos] ) | ord( $this->data[$spos + 1] ) << 8;
                                    $column = ord( $this->data[$spos + 2] ) | ord( $this->data[$spos + 3] ) << 8;
                                    $tmp = unpack( "ddouble", substr( $this->data, $spos + 6, 8 ) );
                                    if ( $this->isDate( $spos ) )
                                    {
                                        list($string, $raw) = $this->createDate($tmp['double']);
                                    }
                                    else
                                    {
                                        if ( isset( $this->_columnsFormat[$column + 1] ) )
                                        {
                                            $this->curformat = $this->_columnsFormat[$column + 1];
                                        }
                                        $raw = $this->createNumber( $spos );
                                        $string = sprintf( $this->curformat, $raw * $this->multiplier );
                                    }
                                    $this->addcell( $row, $column, $string, $raw );
                                    break;
                                default :
                                    switch ( $code )
                                    {
                                    case Spreadsheet_Excel_Reader_Type_FORMULA :
                                    case Spreadsheet_Excel_Reader_Type_FORMULA2 :
                                        $row = ord( $this->data[$spos] ) | ord( $this->data[$spos + 1] ) << 8;
                                        $column = ord( $this->data[$spos + 2] ) | ord( $this->data[$spos + 3] ) << 8;
                                        if ( ord( $this->data[$spos + 6] ) == 0 && ord( $this->data[$spos + 12] ) == 255 && ord( $this->data[$spos + 13] ) == 255 )
                                        {
                                        }
                                        else if ( ord( $this->data[$spos + 6] ) == 1 && ord( $this->data[$spos + 12] ) == 255 && ord( $this->data[$spos + 13] ) == 255 )
                                        {
                                        }
                                        else if ( ord( $this->data[$spos + 6] ) == 2 && ord( $this->data[$spos + 12] ) == 255 && ord( $this->data[$spos + 13] ) == 255 )
                                        {
                                        }
                                        else if ( ord( $this->data[$spos + 6] ) == 3 && ord( $this->data[$spos + 12] ) == 255 && ord( $this->data[$spos + 13] ) == 255 )
                                        {
                                        }
                                        else
                                        {
                                            $tmp = unpack( "ddouble", substr( $this->data, $spos + 6, 8 ) );
                                            if ( $this->isDate( $spos ) )
                                            {
                                                list($string, $raw) = $this->createDate($tmp['double']);
                                            }
                                            else
                                            {
                                                if ( isset( $this->_columnsFormat[$column + 1] ) )
                                                {
                                                    $this->curformat = $this->_columnsFormat[$column + 1];
                                                }
                                                $raw = $this->createNumber( $spos );
                                                $string = sprintf( $this->curformat, $raw * $this->multiplier );
                                            }
                                            $this->addcell( $row, $column, $string, $raw );
                                        }
                                        break;
                                    default :
                                        switch ( $code )
                                        {
                                        case Spreadsheet_Excel_Reader_Type_BOOLERR :
                                            $row = ord( $this->data[$spos] ) | ord( $this->data[$spos + 1] ) << 8;
                                            $column = ord( $this->data[$spos + 2] ) | ord( $this->data[$spos + 3] ) << 8;
                                            $string = ord( $this->data[$spos + 6] );
                                            $this->addcell( $row, $column, $string );
                                            break;
                                        default :
                                            switch ( $code )
                                            {
                                            case Spreadsheet_Excel_Reader_Type_ROW :
                                            case Spreadsheet_Excel_Reader_Type_DBCELL :
                                            case Spreadsheet_Excel_Reader_Type_MULBLANK :
                                                break;
                                            default :
                                                switch ( $code )
                                                {
                                                case Spreadsheet_Excel_Reader_Type_LABEL :
                                                    $row = ord( $this->data[$spos] ) | ord( $this->data[$spos + 1] ) << 8;
                                                    $column = ord( $this->data[$spos + 2] ) | ord( $this->data[$spos + 3] ) << 8;
                                                    $this->addcell( $row, $column, substr( $this->data, $spos + 8, ord( $this->data[$spos + 6] ) | ord( $this->data[$spos + 7] ) << 8 ) );
                                                    break;
                                                default :
                                                    switch ( $code )
                                                    {
                                                    case Spreadsheet_Excel_Reader_Type_EOF :
                                                    }
                                                    $cont = false;
                                                    break;
                                                    break;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            $spos += $length;
        }
        if ( !isset( $this->sheets[$this->sn]['numRows'] ) )
        {
            $this->sheets[$this->sn]['numRows'] = $this->sheets[$this->sn]['maxrow'];
        }
        if ( !isset( $this->sheets[$this->sn]['numCols'] ) )
        {
            $this->sheets[$this->sn]['numCols'] = $this->sheets[$this->sn]['maxcol'];
        }
    }

    public function isDate( $spos )
    {
        $xfindex = ord( $this->data[$spos + 4] ) | ord( $this->data[$spos + 5] ) << 8;
        if ( $this->formatRecords['xfrecords'][$xfindex]['type'] == "date" )
        {
            $this->curformat = $this->formatRecords['xfrecords'][$xfindex]['format'];
            $this->rectype = "date";
            return true;
        }
        if ( $this->formatRecords['xfrecords'][$xfindex]['type'] == "number" )
        {
            $this->curformat = $this->formatRecords['xfrecords'][$xfindex]['format'];
            $this->rectype = "number";
            if ( $xfindex == 9 || $xfindex == 10 )
            {
                $this->multiplier = 100;
            }
        }
        else
        {
            $this->curformat = $this->_defaultFormat;
            $this->rectype = "unknown";
        }
        return false;
    }

    public function createDate( $numValue )
    {
        if ( 1 < $numValue )
        {
            $utcDays = $numValue - ( $this->nineteenFour ? Spreadsheet_Excel_Reader_utcOffsetDays1904 : Spreadsheet_Excel_Reader_utcOffsetDays );
            $utcValue = round( $utcDays * Spreadsheet_Excel_Reader_msInADay );
            $string = date( $this->curformat, $utcValue );
            $raw = $utcValue;
        }
        else
        {
            $raw = $numValue;
            $hours = floor( $numValue * 24 );
            $mins = floor( $numValue * 24 * 60 ) - $hours * 60;
            $secs = floor( $numValue * Spreadsheet_Excel_Reader_msInADay ) - $hours * 60 * 60 - $mins * 60;
            $string = date( $this->curformat, mktime( $hours, $mins, $secs ) );
        }
        return array(
            $string,
            $raw
        );
    }

    public function createNumber( $spos )
    {
        $rknumhigh = $this->_GetInt4d( $this->data, $spos + 10 );
        $rknumlow = $this->_GetInt4d( $this->data, $spos + 6 );
        $sign = ( $rknumhigh & 2.14748e+009 ) >> 31;
        $exp = ( $rknumhigh & 2146435072 ) >> 20;
        $mantissa = 1048576 | $rknumhigh & 1048575;
        $mantissalow1 = ( $rknumlow & 2.14748e+009 ) >> 31;
        $mantissalow2 = $rknumlow & 2147483647;
        $value = $mantissa / pow( 2, 20 - ( $exp - 1023 ) );
        if ( $mantissalow1 != 0 )
        {
            $value += 1 / pow( 2, 21 - ( $exp - 1023 ) );
        }
        $value += $mantissalow2 / pow( 2, 52 - ( $exp - 1023 ) );
        if ( $sign )
        {
            $value = 0 - 1 * $value;
        }
        return $value;
    }

    public function addcell( $row, $col, $string, $raw = "" )
    {
        $this->sheets[$this->sn]['maxrow'] = max( $this->sheets[$this->sn]['maxrow'], $row + $this->_rowoffset );
        $this->sheets[$this->sn]['maxcol'] = max( $this->sheets[$this->sn]['maxcol'], $col + $this->_coloffset );
        $this->sheets[$this->sn]['cells'][$row + $this->_rowoffset][$col + $this->_coloffset] = $string;
        if ( $raw )
        {
            $this->sheets[$this->sn]['cellsInfo'][$row + $this->_rowoffset][$col + $this->_coloffset]['raw'] = $raw;
        }
        if ( isset( $this->rectype ) )
        {
            $this->sheets[$this->sn]['cellsInfo'][$row + $this->_rowoffset][$col + $this->_coloffset]['type'] = $this->rectype;
        }
    }

    public function _GetIEEE754( $rknum )
    {
        if ( ( $rknum & 2 ) != 0 )
        {
            $value = $rknum >> 2;
        }
        else
        {
            $sign = ( $rknum & 2.14748e+009 ) >> 31;
            $exp = ( $rknum & 2146435072 ) >> 20;
            $mantissa = 1048576 | $rknum & 1048572;
            $value = $mantissa / pow( 2, 20 - ( $exp - 1023 ) );
            if ( $sign )
            {
                $value = 0 - 1 * $value;
            }
        }
        if ( ( $rknum & 1 ) != 0 )
        {
            $value /= 100;
        }
        return $value;
    }

    public function _encodeUTF16( $string )
    {
        $result = $string;
        if ( $this->_defaultEncoding )
        {
            switch ( $this->_encoderFunction )
            {
            case "iconv" :
                $result = iconv( "UTF-16LE", $this->_defaultEncoding, $string );
                break;
            case "mb_convert_encoding" :
                $result = mb_convert_encoding( $string, $this->_defaultEncoding, "UTF-16LE" );
            }
        }
        return $result;
    }

    public function _GetInt4d( $data, $pos )
    {
        return ord( $data[$pos] ) | ord( $data[$pos + 1] ) << 8 | ord( $data[$pos + 2] ) << 16 | ord( $data[$pos + 3] ) << 24;
    }

}

require_once( "excel_oleread.inc" );
define( "Spreadsheet_Excel_Reader_BIFF8", 1536 );
define( "Spreadsheet_Excel_Reader_BIFF7", 1280 );
define( "Spreadsheet_Excel_Reader_WorkbookGlobals", 5 );
define( "Spreadsheet_Excel_Reader_Worksheet", 16 );
define( "Spreadsheet_Excel_Reader_Type_BOF", 2057 );
define( "Spreadsheet_Excel_Reader_Type_EOF", 10 );
define( "Spreadsheet_Excel_Reader_Type_BOUNDSHEET", 133 );
define( "Spreadsheet_Excel_Reader_Type_DIMENSION", 512 );
define( "Spreadsheet_Excel_Reader_Type_ROW", 520 );
define( "Spreadsheet_Excel_Reader_Type_DBCELL", 215 );
define( "Spreadsheet_Excel_Reader_Type_FILEPASS", 47 );
define( "Spreadsheet_Excel_Reader_Type_NOTE", 28 );
define( "Spreadsheet_Excel_Reader_Type_TXO", 438 );
define( "Spreadsheet_Excel_Reader_Type_RK", 126 );
define( "Spreadsheet_Excel_Reader_Type_RK2", 638 );
define( "Spreadsheet_Excel_Reader_Type_MULRK", 189 );
define( "Spreadsheet_Excel_Reader_Type_MULBLANK", 190 );
define( "Spreadsheet_Excel_Reader_Type_INDEX", 523 );
define( "Spreadsheet_Excel_Reader_Type_SST", 252 );
define( "Spreadsheet_Excel_Reader_Type_EXTSST", 255 );
define( "Spreadsheet_Excel_Reader_Type_CONTINUE", 60 );
define( "Spreadsheet_Excel_Reader_Type_LABEL", 516 );
define( "Spreadsheet_Excel_Reader_Type_LABELSST", 253 );
define( "Spreadsheet_Excel_Reader_Type_NUMBER", 515 );
define( "Spreadsheet_Excel_Reader_Type_NAME", 24 );
define( "Spreadsheet_Excel_Reader_Type_ARRAY", 545 );
define( "Spreadsheet_Excel_Reader_Type_STRING", 519 );
define( "Spreadsheet_Excel_Reader_Type_FORMULA", 1030 );
define( "Spreadsheet_Excel_Reader_Type_FORMULA2", 6 );
define( "Spreadsheet_Excel_Reader_Type_FORMAT", 1054 );
define( "Spreadsheet_Excel_Reader_Type_XF", 224 );
define( "Spreadsheet_Excel_Reader_Type_BOOLERR", 517 );
define( "Spreadsheet_Excel_Reader_Type_UNKNOWN", 65535 );
define( "Spreadsheet_Excel_Reader_Type_NINETEENFOUR", 34 );
define( "Spreadsheet_Excel_Reader_Type_MERGEDCELLS", 229 );
define( "Spreadsheet_Excel_Reader_utcOffsetDays", 25569 );
define( "Spreadsheet_Excel_Reader_utcOffsetDays1904", 24107 );
define( "Spreadsheet_Excel_Reader_msInADay", 24 * 60 * 60 );
define( "Spreadsheet_Excel_Reader_DEF_NUM_FORMAT", "%s" );
if ( !function_exists( "file_get_contents" ) )
{
    function file_get_contents( $filename, $use_include_path = 0 )
    {
        $data = "";
        $file = @fopen( $filename, "rb", $use_include_path );
        if ( $file )
        {
            while ( !feof( $file ) )
            {
                $data .= fread( $file, 1024 );
            }
            fclose( $file );
        }
        else
        {
            $data = FALSE;
        }
        return $data;
    }
}
?>
