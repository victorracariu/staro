<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class Excel_Xml
{

    public $xml = NULL;

    public function Excel_Xml( )
    {
        $this->xml = simplexml_load_string( $this->BuildBackbone( ) );
    }

    public function LoadString( $string )
    {
        $string = str_replace( "<ss:Worksheet", "<Worksheet", $string );
        $string = str_replace( "</ss:Worksheet", "</Worksheet", $string );
        $this->xml = simplexml_load_string( $string );
        if ( $this->xml === FALSE )
        {
            throw new Exception( "Failed loading XML" );
        }
        $this->BuildIndexes( );
    }

    public function BuildXml( $data )
    {
        $table = $this->GetWorksheet( )->Table;
        $i = 0;
        for ( ; $i < count( $data[0] ); $i++ )
        {
            $table->addChild( "Column" );
        }
        $i = 0;
        for ( ; $i < count( $data ); $i++ )
        {
            $row = $table->addChild( "Row" );
            $row->addAttribute( "xmlns:ss:Index", $i + 1 );
            if ( !isset( $data[$i] ) )
            {
                continue;
            }
            foreach ( $data[$i] as $col )
            {
                $cell = $row->addChild( "Cell" );
                $cell->Data = $col;
                $cell->Data->addAttribute( "xmlns:ss:Type", "String" );
                if ( filter_var( $col, FILTER_VALIDATE_URL ) )
                {
                    $cell->addAttribute( "xmlns:ss:HRef", $col );
                }
            }
        }
    }

    public function BuildIndexes( )
    {
        $row_index = 1;
        foreach ( $this->GetWorksheet( )->Table->Row as $row )
        {
            $ssAttrs = $row->attributes( "ss", TRUE );
            $index = $ssAttrs['Index'];
            if ( !isset( $index ) )
            {
                $row->addAttribute( "ss:Index", $row_index++, "xmlns:ss" );
            }
            else
            {
                $row_index = $index + 1;
            }
            $cell_index = 1;
            foreach ( $row->Cell as $cell )
            {
                $ssAttrs = $cell->attributes( "ss", TRUE );
                $index = $ssAttrs['Index'];
                if ( !isset( $index ) )
                {
                    $cell->addAttribute( "ss:Index", $cell_index++, "xmlns:ss" );
                }
                else
                {
                    $cell_index = $index + 1;
                }
            }
        }
    }

    public function SetTableColumnWidth( $width )
    {
        $table = $this->GetWorksheet( )->Table;
        $table->addAttribute( "xmlns:ss:DefaultColumnWidth", $width );
    }

    public function SetBoldRow( $row )
    {
        $xls_row = $this->GetRow( $row );
        $cell_count = count( $xls_row->Cell );
        $i = 0;
        for ( ; $i < $cell_count; $i++ )
        {
            $this->SetBold( $row, $i );
        }
    }

    public function SetBold( $line, $col )
    {
        $cell = $this->GetWorksheet( )->Table->Row[$line]->Cell[$col];
        $cell->addAttribute( "xmlns:ss:StyleID", "s21" );
    }

    public function SetProtected( $line, $col )
    {
        $cell = $this->GetWorksheet( )->Table->Row[$line]->Cell[$col];
        $cell->addAttribute( "xmlns:ss:StyleID", "protected" );
    }

    public function BuildBackbone( )
    {
        $res = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n                        <?mso-application progid=\"Excel.Sheet\"?>\n                        <Workbook\n                           xmlns=\"urn:schemas-microsoft-com:office:spreadsheet\"\n                           xmlns:o=\"urn:schemas-microsoft-com:office:office\"\n                           xmlns:x=\"urn:schemas-microsoft-com:office:excel\"\n                           xmlns:ss=\"urn:schemas-microsoft-com:office:spreadsheet\"\n                           xmlns:html=\"http://www.w3.org/TR/REC-html40\">\n                          <DocumentProperties xmlns=\"urn:schemas-microsoft-com:office:office\">\n                            <Author>RVX</Author>\n                            <LastAuthor>RVX</LastAuthor>\n                            <Company>Fashion Days</Company>\n                          </DocumentProperties>\n                          <ExcelWorkbook xmlns=\"urn:schemas-microsoft-com:office:excel\">\n                            <ProtectStructure>False</ProtectStructure>\n                            <ProtectWindows>False</ProtectWindows>\n                          </ExcelWorkbook>\n                          <Styles>\n                            <Style ss:ID=\"Default\" ss:Name=\"Normal\">\n                              <Alignment ss:Vertical=\"Bottom\" />\n                            </Style>\n                            <Style ss:ID=\"unprotected\">\n                              <Protection ss:Protected=\"0\" />\n                            </Style>\n                            <Style ss:ID=\"protected\">\n                              <Protection ss:Protected=\"1\" />\n                            </Style>\n                            <Style ss:ID=\"s21\">\n                              <Font ss:Bold=\"1\" />\n                            </Style>\n                          </Styles>\n                          <Worksheet ss:Name=\"Sheet1\" ss:Protected=\"0\">\n                            <Table x:FullColumns=\"1\" x:FullRows=\"1\" ss:StyleID=\"unprotected\"></Table>\n                            <WorksheetOptions xmlns=\"urn:schemas-microsoft-com:office:excel\">\n                              <Selected />\n                              <ProtectObjects>False</ProtectObjects>\n                              <ProtectScenarios>False</ProtectScenarios>\n                            </WorksheetOptions>\n                          </Worksheet>\n                        </Workbook>";
        return $res;
    }

    public function GetString( )
    {
        return $this->xml->asXML( );
    }

    public function SaveAsFile( $fileName )
    {
        $this->xml->asXML( $fileName );
    }

    public function GetTableXml( )
    {
        return $this->GetWorksheet( )->Table;
    }

    public function GetWorksheet( )
    {
        $res = $this->xml->Worksheet;
        return $res;
    }

    public function GetRow( $index )
    {
        foreach ( $this->GetTableXml( )->Row as $row )
        {
            $ssAttrs = $row->attributes( "ss", TRUE );
            $rowIndex = $ssAttrs['Index'];
            if ( $index == $rowIndex )
            {
                return $row;
            }
        }
        throw new Exception( "Row with index {$index} not found." );
    }

    public function GetCellValue( $rowIndex, $cellIndex )
    {
        $row = $this->GetRow( $rowIndex );
        foreach ( $row->Cell as $cell )
        {
            $ssAttrs = $cell->attributes( "ss", TRUE );
            $cellIdx = $ssAttrs['Index'];
            if ( $cellIdx == $cellIndex )
            {
                return ( boolean )$cell->Data;
            }
        }
        return "";
    }

    public function GetRowsAfter( $index )
    {
        $res = array( );
        foreach ( $this->GetTableXml( )->Row as $row )
        {
            $ssAttrs = $row->attributes( "ss", TRUE );
            $rowIndex = $ssAttrs['Index'];
            if ( $index < $rowIndex )
            {
                $res[] = $row;
            }
        }
        return $res;
    }

    public function GetRowIndex( $row )
    {
        $ssAttrs = $row->attributes( "ss", TRUE );
        return $ssAttrs['Index'];
    }

}

?>
