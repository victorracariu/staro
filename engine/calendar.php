<?php

include_once( RVXPATH."controller.php" );
class RCalendar
{

    public $GridTitle = NULL;
    public $GridUrl = NULL;
    public $Year = NULL;
    public $Month = NULL;
    public $Week = 0;
    public $TimeStart = "00:00";
    public $TimeEnd = "24:00";
    public $TimeSpan = "10";
    public $WeekDays = array
    (
        0 => "D",
        1 => "L",
        2 => "Ma",
        3 => "Mi",
        4 => "J",
        5 => "V",
        6 => "S"
    );
    public $HtmlParams = "{}";

    public function InitParams( )
    {
        $rvx =& get_engine( );
        $this->Week = $rvx->Context->GetParam( "week" );
        if ( $this->Week == "" )
        {
            $this->Week = $rvx->Input->Post( "week" );
        }
        if ( $this->Week == "" )
        {
            $this->Week = 0;
        }
        $day = date( "d" ) + 7 * $this->Week;
        $cdate = mktime( 0, 0, 0, date( "m" ), $day, date( "Y" ) );
        $dayow = date( "w", $cdate );
        if ( $dayow == 0 )
        {
            $dayow = 6;
        }
        $day = date( "d" ) + 7 * $this->Week - $dayow + 1;
        $cdate = mktime( 0, 0, 0, date( "m" ), $day, date( "Y" ) );
        $this->Year = date( "Y", $cdate );
        $this->Month = date( "m", $cdate );
        $this->Day = date( "d", $cdate );
    }

    public function InitDataset( )
    {
    }

    public function Index( )
    {
        $this->InitParams( );
        $grid_record = "{name:'Hour'}";
        $hour_model = "{header:'', width:40, dataIndex: 'Hour', sortable:false, fixed:true, menuDisabled:true, rowspan:undefined, id: 'numberer'}";
        $grid_model = $hour_model;
        $i = 0;
        while ( $i < 28 )
        {
            $cdate = mktime( 0, 0, 0, $this->Month, $this->Day + $i, $this->Year );
            $dayow = date( "w", $cdate );
            $field = "F".$i;
            $title = date( "d.m.Y", $cdate )." ".$this->WeekDays[$dayow];
            if ( $dayow == 1 && 0 < $i )
            {
                $grid_model .= ",\n".$hour_model;
            }
            if ( $dayow == 0 || $dayow == 6 )
            {
                $title = "<font color=\"red\">".$title."</font>";
            }
            $grid_record .= ",{name:'".$field."'}";
            $grid_model .= ",\n{header:'".$title."', width:80, dataIndex:'".$field."'}";
            ++$i;
        }
        $this->RenderParams( );
        $page_title = $this->GridTitle;
        include_once( RVXPATH."calendar_page.php" );
    }

    public function Fetch( )
    {
        $rvx =& get_engine( );
        $this->InitParams( );
        $this->InitDataset( );
        $time_start = strtotime( $this->TimeStart );
        $time_end = strtotime( $this->TimeEnd );
        $time_span = $this->TimeSpan;
        $time_crt = $time_start;
        while ( $time_crt <= $time_end )
        {
            $datarow = array( );
            $i = 0;
            while ( $i < 28 )
            {
                $date_crt = mktime( 0, 0, 0, $this->Month, $this->Day + $i, $this->Year );
                $date_caption = date( "Y-m-d", $date_crt );
                $time_caption = date( "H:i:s", $time_crt );
                $datarow['Hour'] = date( "H:i", $time_crt );
                $datarow["F".$i] = $this->GetDataCell( $date_caption, $time_caption );
                ++$i;
            }
            $dataset[] = $datarow;
            $time_crt = strtotime( "+{$time_span} minutes", $time_crt );
        }
        $data['total'] = count( $dataset );
        $data['results'] = $dataset;
        echo rvx_json_encode( $data );
    }

    public function GetDataCell( $date, $time )
    {
        foreach ( $this->Dataset as $row )
        {
            if ( $row['Date'] != $date )
            {
                continue;
            }
            if ( $row['Time'] != $time )
            {
                continue;
            }
            $cap = $row['Caption'];
            $url = $this->GridUrl."view/id/".$row['Id'];
            return "<a target=_blank href=\"".$url."\">".$cap."</a>";
        }
        return "";
    }

    public function RenderParams( )
    {
        $rvx =& get_engine( );
        $comma = "";
        $this->HtmlParams = "{";
        foreach ( $rvx->Context->Params as $p => $v )
        {
            $this->HtmlParams .= $comma;
            $this->HtmlParams .= "{$p}:'{$v}'";
            $comma = ",";
        }
        $this->HtmlParams .= "}";
    }

}

?>
