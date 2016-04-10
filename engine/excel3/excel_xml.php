<?php

//=============================================================================
/**
 * Class Excel_Xml Exports data in the xml format for Excel
 */
class Excel_Xml
//=============================================================================
{
        var $xml; // xml object

//=============================================================================
        function Excel_Xml()
//=============================================================================
        {
                $this->xml = simplexml_load_string($this->BuildBackbone());
        }

//=============================================================================
        /**
         * Builds the xml from the given string
         *
         * @param string $string
         * @throws Exception When the given string is not a valid xml
         */
        function LoadString($string)
//=============================================================================
        {
                // OpenOffice is retarded and instead of <Worksheet>
                // adds a namespace and makes it <ss:Worksheet>
                $string = str_replace('<ss:Worksheet', '<Worksheet', $string);
                $string = str_replace('</ss:Worksheet', '</Worksheet', $string);

                $this->xml = simplexml_load_string($string);

                if ($this->xml === false)
                {
                        throw new Exception("Failed loading XML");
                }

                $this->BuildIndexes();
        }

//=============================================================================
        /**
         * Builds the xml given a 2d array of data. When it encounters a null row,
         * it inserts an empty row in the excel xml
         *
         * @param array $data
         */
        function BuildXml($data)
//=============================================================================
        {
                $table = $this->GetWorksheet()->Table;

                for ($i = 0; $i < count($data[0]); $i++)
                {
                        $table->addChild('Column');
                }

                for ($i = 0; $i < count($data); $i++)
                {
                        $row = $table->addChild('Row');
                        $row->addAttribute('xmlns:ss:Index', $i + 1);

                        // empty row
                        if (!isset($data[$i]))
                        {
                                continue;
                        }

                        foreach ($data[$i] as $col)
                        {
                                $cell = $row->addChild('Cell');
                                $cell->Data = $col; // don't use addChild here, it does not escape special chars
                                $cell->Data->addAttribute('xmlns:ss:Type', 'String');
                                
                                // if value is url
                                if(filter_var($col, FILTER_VALIDATE_URL))
                                { 
                                        $cell->addAttribute('xmlns:ss:HRef', $col);
                                }
                        }
                }
        }

//=============================================================================
        /**
         * Sets the index attribute for all rows and cells
         */
        function BuildIndexes()
//=============================================================================
        {
                $row_index = 1;

                foreach ($this->GetWorksheet()->Table->Row as $row)
                {
                        $ssAttrs = $row->attributes('ss', true);
                        $index = $ssAttrs['Index'];
                        if (!isset($index))
                        {
                                $row->addAttribute('ss:Index', $row_index++, 'xmlns:ss');
                        }
                        else
                        {
                                $row_index = $index + 1;
                        }

                        $cell_index = 1;
                        foreach($row->Cell as $cell)
                        {
                                $ssAttrs = $cell->attributes('ss', true);
                                $index = $ssAttrs['Index'];
                                if (!isset($index))
                                {
                                        $cell->addAttribute('ss:Index', $cell_index++, 'xmlns:ss');
                                }
                                else
                                {
                                        $cell_index = $index + 1;
                                }
                        }
                }
        }

//=============================================================================
        /**
         * Set the width of the columns to the given value
         *
         * @param int $width
         */
        function SetTableColumnWidth($width)
//=============================================================================
        {
                $table = $this->GetWorksheet()->Table;
                $table->addAttribute('xmlns:ss:DefaultColumnWidth', $width);
        }

//=============================================================================
        /**
         * Sets the font of the given row to bold
         *
         * @param int $row Row index, 0-based
         */
        function SetBoldRow($row)
//=============================================================================
        {
                $xls_row = $this->GetRow($row);
                $cell_count = count($xls_row->Cell);
                for( $i = 0; $i < $cell_count; $i++ )
                {
                        $this->SetBold( $row, $i );
                }
        }

//=============================================================================
        /**
         * Sets the font of the given cell to bold
         *
         * @param int $line Row index, 0-based
         * @param int $col Column index, 0-based
         */
        function SetBold($line, $col)
//=============================================================================
        {
                $cell = $this->GetWorksheet()->Table->Row[$line]->Cell[$col];
                $cell->addAttribute('xmlns:ss:StyleID', 's21');
        }

//=============================================================================
        /**
         * Protects the given cell against writing
         *
         * @param int $line Row index, 0-based
         * @param int $col Column index, 0-based
         */
        function SetProtected($line, $col)
//=============================================================================
        {
                $cell = $this->GetWorksheet()->Table->Row[$line]->Cell[$col];
                $cell->addAttribute('xmlns:ss:StyleID', 'protected');
        }

//=============================================================================
        /**
         * Create an Excel xml structure with an empty sheet
         *
         * @return string The xml string
         */
        function BuildBackbone()
//=============================================================================
        {
                $res = '<?xml version="1.0" encoding="UTF-8"?>
                        <?mso-application progid="Excel.Sheet"?>
                        <Workbook
                           xmlns="urn:schemas-microsoft-com:office:spreadsheet"
                           xmlns:o="urn:schemas-microsoft-com:office:office"
                           xmlns:x="urn:schemas-microsoft-com:office:excel"
                           xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
                           xmlns:html="http://www.w3.org/TR/REC-html40">
                          <DocumentProperties xmlns="urn:schemas-microsoft-com:office:office">
                            <Author>RVX</Author>
                            <LastAuthor>RVX</LastAuthor>
                            <Company>Fashion Days</Company>
                          </DocumentProperties>
                          <ExcelWorkbook xmlns="urn:schemas-microsoft-com:office:excel">
                            <ProtectStructure>False</ProtectStructure>
                            <ProtectWindows>False</ProtectWindows>
                          </ExcelWorkbook>
                          <Styles>
                            <Style ss:ID="Default" ss:Name="Normal">
                              <Alignment ss:Vertical="Bottom" />
                            </Style>
                            <Style ss:ID="unprotected">
                              <Protection ss:Protected="0" />
                            </Style>
                            <Style ss:ID="protected">
                              <Protection ss:Protected="1" />
                            </Style>
                            <Style ss:ID="s21">
                              <Font ss:Bold="1" />
                            </Style>
                          </Styles>
                          <Worksheet ss:Name="Sheet1" ss:Protected="0">
                            <Table x:FullColumns="1" x:FullRows="1" ss:StyleID="unprotected"></Table>
                            <WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">
                              <Selected />
                              <ProtectObjects>False</ProtectObjects>
                              <ProtectScenarios>False</ProtectScenarios>
                            </WorksheetOptions>
                          </Worksheet>
                        </Workbook>';

                return $res;
        }

//=============================================================================
        /**
         * Get xml as string value
         *
         * @return string XML
         */
        function GetString()
//=============================================================================
        {
                return $this->xml->asXML();
        }

//=============================================================================
        /**
         * Save excel xml to file
         *
         * @param string $fileName Where the excel xml will be saved
         */
        function SaveAsFile($fileName)
//=============================================================================
        {
                $this->xml->asXML($fileName);
        }

//=============================================================================
        /**
         * Returns the Table xml element, containing all rows and cells
         *
         * @return SimpleXMLElement
         */
        function GetTableXml()
//=============================================================================
        {
                return $this->GetWorksheet()->Table;
        }

//=============================================================================
        /**
         * Get the worksheet element
         *
         * @return SimpleXMLElement
         */
        function GetWorksheet()
//=============================================================================
        {
                $res = $this->xml->Worksheet;

                return $res;
        }

//=============================================================================
        /**
         * Retrieve excel row based on it's index
         *
         * @param $index Row index, 1-based
         * @return SimpleXMLElement
         * @throws Exception When row is not found
         */
        function GetRow($index)
//=============================================================================
        {
                foreach ($this->GetTableXml()->Row as $row)
                {
                        $ssAttrs = $row->attributes('ss', true);
                        $rowIndex = $ssAttrs['Index'];

                        if ($index == $rowIndex)
                        {
                                return $row;
                        }
                }

                throw new Exception("Row with index {$index} not found.");
        }

//=============================================================================
        /**
         * Retrieve cell value based on its coordinates
         *
         * @param int $rowIndex Row index, 1-based
         * @param int $cellIndex Cell index, 1-based
         * @return string
         * @throws Exception When row or cell not found
         */
        function GetCellValue($rowIndex, $cellIndex)
//=============================================================================
        {
                $row = $this->GetRow($rowIndex);

                foreach ($row->Cell as $cell)
                {
                        $ssAttrs = $cell->attributes('ss', true);
                        $cellIdx = $ssAttrs['Index'];

                        if ($cellIdx == $cellIndex)
                        {
                                return (string) $cell->Data;
                        }
                }

                // if cell not found, means it's empty
                return '';
        }

//=============================================================================
        /**
         * Retrieve all rows having Index greater than the given index
         *
         * @param int $index
         * @return array All rows that match
         */
        function GetRowsAfter($index)
//=============================================================================
        {
                $res = array();

                foreach ($this->GetTableXml()->Row as $row)
                {
                        $ssAttrs = $row->attributes('ss', true);
                        $rowIndex = $ssAttrs['Index'];

                        if ($rowIndex > $index)
                        {
                                $res[] = $row;
                        }
                }

                return $res;
        }

//=============================================================================
        /**
         * Get the Index attribute of the given row
         *
         * @param SimpleXMLElement $row
         * @return int
         */
        function GetRowIndex($row)
//=============================================================================
        {
                $ssAttrs = $row->attributes('ss', true);
                return $ssAttrs['Index'];
        }
}

?>