<?php
function generate_schedule_index_bydate($sqlDB, $league = 'all', $min_schedule = '', $max_schedule = '')
{
    global $schedule_url, $schedule_dir, $month_list_ina, $tv_url, $tv_jadwal_url;

    /*$CURRENT_SEASON = '45';
    $CURRENT_SEASON_YEAR = '2014';*/
    $CURRENT_SEASON = '51';
    $CURRENT_SEASON_YEAR = '2015';

    $score_url = $schedule_url . 'hasil_pertandingan/';
    
    //season lama
    /*$SEASONS = array(
        '46' => array('name' => 'Inggris - Liga Primer', 'standing_url' => 'http://www.bola.net/jadwal_skor/klasemen_liga_inggris.html'),
        '48' => array('name' => 'Spanyol - La Liga', 'standing_url' => 'http://www.bola.net/jadwal_skor/klasemen_liga_spanyol.html'),
        '47' => array('name' => 'Italia - Serie A', 'standing_url' => 'http://www.bola.net/jadwal_skor/klasemen_liga_italia.html'),
        '50' => array('name' => 'UEFA - Liga Champions', 'standing_url' => 'http://www.bola.net/jadwal_skor/klasemen_liga_champions.html'),
        '45' => array('name' => 'Indonesia - Super League', 'standing_url' => 'http://www.bola.net/jadwal_skor/klasemen_liga_indonesia.html')
    );*/
/*    $SEASONS = array(
        '59' => array('name' => 'Inggris - Liga Primer', 'standing_url' => BOLAURL.'jadwal_skor/klasemen_liga_inggris.html'),
        '61' => array('name' => 'Spanyol - La Liga', 'standing_url' => BOLAURL.'jadwal_skor/klasemen_liga_spanyol.html'),
        '60' => array('name' => 'Italia - Serie A', 'standing_url' => BOLAURL.'jadwal_skor/klasemen_liga_italia.html'),
        '62' => array('name' => 'UEFA - Liga Champions', 'standing_url' => BOLAURL.'jadwal_skor/klasemen_liga_champions.html'),
        //'51' => array('name' => 'Indonesia - Super League', 'standing_url' => 'http://www.bola.net/jadwal_skor/klasemen_liga_indonesia.html'),
        '57' => array('name' => 'EURO 2016', 'standing_url' => BOLAURL.'jadwal_skor/klasemen_euro_2016.html'),
        '58' => array('name' => 'Indonesia Liga 1', 'standing_url' => BOLAURL.'jadwal_skor/klasemen_liga_indonesia.html')
    );
*/
    $SEASONS = array(
        '66' => array('name' => 'Inggris - Liga Primer', 'standing_url' => BOLAURL.'jadwal_skor/klasemen_liga_inggris.html'),
        '68' => array('name' => 'Spanyol - La Liga', 'standing_url' => BOLAURL.'jadwal_skor/klasemen_liga_spanyol.html'),
        '67' => array('name' => 'Italia - Serie A', 'standing_url' => BOLAURL.'jadwal_skor/klasemen_liga_italia.html'),
        '62' => array('name' => 'UEFA - Liga Champions', 'standing_url' => BOLAURL.'jadwal_skor/klasemen_liga_champions.html'),
        //'51' => array('name' => 'Indonesia - Super League', 'standing_url' => 'http://www.bola.net/jadwal_skor/klasemen_liga_indonesia.html'),
        '57' => array('name' => 'EURO 2016', 'standing_url' => BOLAURL.'jadwal_skor/klasemen_euro_2016.html'),
        '65' => array('name' => 'Indonesia Liga 1', 'standing_url' => BOLAURL.'jadwal_skor/klasemen_liga_indonesia.html')
    );

    $menu_top = '
        <div class="topmenu">
            <a href="' . $schedule_url . 'score.html">Skor Terkini</a>
            &nbsp;&nbsp;&nbsp;
            <a href="' . $schedule_url . '">Jadwal Pertandingan</a>
            &nbsp;&nbsp;&nbsp;
            <a href="' . $tv_jadwal_url . '">Jadwal TV</a>
            &nbsp;&nbsp;
            <a href="' . $schedule_url . 'klasemen_liga_inggris.html">Klasemen</a>
            &nbsp;&nbsp;
            <a href="' . $schedule_url . 'preview.html">Preview</a>
            &nbsp;&nbsp;
            <a href="' . $schedule_url . 'review.html">Review</a>
        </div>
    ';

    $selected_cat = array(1, 2, 3, 4, 5, 6, 8);
    if ($league == 'all') {
        $where_league = '';
        $fname = 'index';
    } else {
        $where_league = " AND country='$league'";
        switch (strtolower($league)) {
            case 'inggris':
                $fname = 'index_liga_inggris';
                $selected_cat = 1;
                break;
            case 'italia':
                $fname = 'index_seri_a';
                $selected_cat = 2;
                break;
            case 'spanyol':
                $fname = 'index_la_liga';
                $selected_cat = 3;
                break;
        }
    }


    /*$share = '
        <!--SOCIALTAB-->
        <div id="bl-social-tabs"></div>
        <script type="text/javascript">
            $(document).ready(function() {
                $("#bl-social-tabs").klsocial({
                    url:"' . $schedule_url . $fname . '.html",
                    comment_count:"-1"
                });
            });
        </script>
        <!--ENDSOCIALTAB-->
    ';*/

    $othernews = get_related_news_league($sqlDB, $selected_cat);
    $related_news = '';
    if (count($othernews) > 0) {
        $related_news = '
            <br/><div class="ntbottom">Berita Liga ' . ($league == 'all'?'':ucwords($league)) . ' Terbaru</div>
        ';
        foreach ($othernews as $v) {
            $v['schedule'] = preg_replace('/\:\d\d$/', '', $v['schedule']);
            $v['schedule'] = preg_replace('/(\d{4,4})\-(\d{2,2})\-(\d{2,2}) (\d{2,2})\:(\d{2,2})/', '\3-\2-\1 \4:\5', $v['schedule']);
            $related_news .= '
                <div class="bcontent1">
                    <span>(' . $v['schedule'] . ')</span>
                    <a href="' . BASEURL . $v['category_url'] . '/' . $v['url'] . '.html" style="font-weight:normal;">' . $v['title'] . '</a>
                </div>
            ';
        }
        $related_news .= '<div class="clear"></div>';
    }

    $q3 = "SELECT DATE(MIN(schedule)) as min_schedule, DATE(MAX(schedule)) as max_schedule FROM `dbschedule` WHERE idseason>='45' AND level<>'0' ";
    //$q3 = "SELECT DATE(MIN(schedule)) as min_schedule, DATE(NOW()) as max_schedule FROM `dbschedule` WHERE idseason>='45' AND level<>'0' ";
    $r3 = $sqlDB->sql_query($q3, true) or die(__LINE__ . ' = ' . mysql_error());

    $row3 = $sqlDB->sql_fetchrow($r3);
    $min_schedule_all = $row3['min_schedule'];
    $max_schedule_all = $row3['max_schedule'];
        
    if (!$min_schedule) {
        $min_schedule = $min_schedule_all;
    }
    if (!$max_schedule) {
        $max_schedule = $max_schedule_all;
    }
    
    //$min_schedule = '2014-06-30';
    //$max_schedule = '2014-12-31';
    
    $write_index = false;
    $today = date('Y-m-d');
    $date = $min_schedule;
    while (strtotime($date) <= strtotime($max_schedule)) {
        $content = '
            <link rel="stylesheet" href="'.ASSET_KLIMG.'assets/css/min/single/0.1/schedule.pack.css">
            <script src="'.ASSET_KLIMG.'assets/js/min/single/0.1/schedule.pack.js"></script>
              <script>
              $(function() {
                $( "#schedule_date_opt" ).datepicker({
                    dateFormat: "yy-mm-dd",
                    minDate: new Date("'.$min_schedule_all.'"),
                    maxDate: new Date("'.$max_schedule_all.'"),
                    onSelect: function(selectedDate) {
                        window.location.href="'.$schedule_url.$fname.'-"+selectedDate+".html";
                    }
                });
                $( "#schedule_date_opt" ).keypress(function( event ) {
                    if ( event.which == 13 ) {
                        window.location.href="'.$schedule_url.$fname.'-"+$(this).val()+".html";
                    }
                });
              });
              </script>
            <div class="bigcon">
                <div class="bigcon2">
                    <div class="nav">
                        <a href="/" style="text-decoration:none;">HOME</a> &raquo; JADWAL PERTANDINGAN
                    </div>
                    ' . $menu_top .'
                    <div class="greentitle">JADWAL PERTANDINGAN</div>
                    
                    
                    <div class="schedule_date_box">
                        Pilih <strong>Tanggal</strong> &nbsp;&nbsp;&nbsp;: 
                        <input type="text" name="schedule_date_opt" id="schedule_date_opt" class="bola_inptext" value="'.$date.'" />
                    </div>
        ';

        list($y, $m, $d) = explode('-', $date);
        $format_date = $d . ' ' . $month_list_ina[$m - 1] . ' ' . $y;
        
        
        $next_schedule = schedule_check_next_date($sqlDB, $date, $league);
        list($y, $m, $d) = explode('-', $next_schedule);
        $next_schedule_ina = $d . ' ' . $month_list_ina[$m - 1] . ' ' . $y;
        $next_schedule_url = $schedule_url.$fname.'-'.$next_schedule.'.html';
        $file_next_schedule = $schedule_dir.$fname.'-'.$next_schedule.'.html';
        $content_next_schedule = ((is_file($file_next_schedule)) ? '<a href="'.$next_schedule_url.'">Berikutnya &raquo;</a>' : '');
        $content .= '
            <div class="schedule_box">
            <h2 class="title_date">
                '.strtoupper($format_date).'
                '.$content_next_schedule.'
                <br class="clear"/>
            </h2>
        ';

        
        
        $schedule_exist = false;
        foreach ($SEASONS as $_season_id_ => $_season_) {
            $_row_class_ = '';
            $schedule_league = '';
            $q = "SELECT * FROM dbschedule WHERE idseason='$_season_id_' AND level<>'0' AND DATE(schedule)='$date' $where_league ORDER BY schedule DESC";
            $r = $sqlDB->sql_query($q, true) or die(__LINE__ . ' = ' . mysql_error());
            while ($row = $sqlDB->sql_fetchrow($r)) {
                $_info_ = '';
                if ($row['level'] == '1') {
                    $_info_ = date('H:i', strtotime($row['schedule']));
                } else {
                    $fnameDetail = getURL($row['title'] . '-' . $row['home'] . $row['home2'] . '-vs-' . $row['away'] . $row['away2'] . '-' . date('Y-m-d', strtotime($row['schedule'])));
                    $filenameDetail = $score_url . $fnameDetail . '.html';

                    $_info_ = '<a href="' . $filenameDetail . '">'.$row['goal_home'].'-'.$row['goal_away'].'</a>';
                }
                $schedule_league .= '
                <div class="schedule_league_row'.$_row_class_.'">    
                    <span class="home">'.$row['home'] . $row['home2'].'</span> 
                    <span class="info">'.$_info_.'</span> 
                    <span class="away">'.$row['away'] . $row['away2'].'</span>
                    <br class="clear"/>
                </div>
                ';
                $_row_class_ = ($_row_class_)?'':' dark';
            }
            if ($schedule_league) {
                $schedule_league_title = $_season_['name'];
                if ($_season_['standing_url']) {
                    $schedule_league_title = '<a href="'.$_season_['standing_url'].'">'.$_season_['name'].'</a>';
                }
                $schedule_league = '
                    <div class="schedule_league">
                        <div class="schedule_league_title">'.$schedule_league_title.'</div>
                        '.$schedule_league.'
                    </div>
                ';
                $content .= $schedule_league;
                $schedule_exist = true;
            }
        }

        $SCHEDULE_OTHER = array();
        $q = "SELECT * FROM dbschedule WHERE  idseason = '0' AND level<>'0' AND DATE(schedule)='$date' $where_league ORDER BY schedule DESC";
        $r = $sqlDB->sql_query($q, true) or die(__LINE__ . ' = ' . mysql_error());
        while ($row = $sqlDB->sql_fetchrow($r)) {
            $SCHEDULE_OTHER[$row['title']][] = $row;
        }

        foreach ($SCHEDULE_OTHER as $_title_ => $rows) {
            $_row_class_ = '';
            $schedule_league = '';
            foreach ($rows as $row) {
                $_info_ = '';
                if ($row['level'] == '1') {
                    $_info_ = date('H:i', strtotime($row['schedule']));
                } else {
                    $fnameDetail = getURL($row['title'] . '-' . $row['home'] . $row['home2'] . '-vs-' . $row['away'] . $row['away2'] . '-' . date('Y-m-d', strtotime($row['schedule'])));
                    $filenameDetail = $score_url . $fnameDetail . '.html';

                    $_info_ = '<a href="' . $filenameDetail . '">'.$row['goal_home'].'-'.$row['goal_away'].'</a>';
                }
                $schedule_league .= '
                <div class="schedule_league_row'.$_row_class_.'">    
                    <span class="home">'.$row['home'] . $row['home2'].'</span> 
                    <span class="info">'.$_info_.'</span> 
                    <span class="away">'.$row['away'] . $row['away2'].'</span>
                    <br class="clear"/>
                </div>
                ';
                $_row_class_ = ($_row_class_)?'':' dark';
            }
            if ($schedule_league) {
                $schedule_league = '
                    <div class="schedule_league">
                        <div class="schedule_league_title">'.$_title_.'</div>
                        '.$schedule_league.'
                    </div>
                ';
                $content .= $schedule_league;
                $schedule_exist = true;
            }
        }

        if (!$schedule_exist) {
            $content .= '<h2 style="text-align:center">TIDAK ADA JADWAL <br/><br/><a class="bluelink" href="'.$next_schedule_url.'">Lihat Jadwal Tanggal '.$next_schedule_ina.'</a></h2> <br/> ';
        }
        
        $content .= '
                </div>
                <div class="clear"></div>
                ' . $related_news . '
                </div>
            </div>
        ';
        
        $filename = $schedule_dir . $fname . '-'.$date . '.html';
        $fileurl = $schedule_url . $fname . '-'.$date . '.html';

        $metatitle = 'Jadwal dan Skor Pertandingan Sepak Bola Terkini, Klasemen Sementara Liga, Preview dan Review Pertandingan, Live Score';
        $metakey = 'Jadwal Pertandingan, Skor Pertandingan, Sepak Bola, Liga Inggris, Piala FA, Piala Carling, Piala Liga Inggris, Community Shield, Liga Spanyol, Piala Raja Spanyol, Liga Italia, Coppa Italia, Super Coppa Italia, Jadwal Pertandingan, Liga Super Indonesia, Piala Indonesia, Hasil Pertandingan, Klasemen, Preview Pertandingan, Review Pertandingan.';
        $metadesc = 'Jadwal dan Skor Pertandingan Sepak Bola Terkini, Klasemen Sementara Liga, Preview dan Review Pertandingan, Live Score.';

        
        write_file($filename, $content, $metatitle, $metakey, $metadesc, '', true, 'full', 5);
        insert_property_og($filename, $metatitle, $fileurl, '', '109215469105623', $metadesc, 'Jadwal & Skor');
        file_put_contents($filename, str_replace(ASSET_KLIMG.'assets/css/min/3.7.5/bola.css', ASSET_KLIMG.'assets/css/min/3.7.6/bola.css', file_get_contents($filename)));
        
        echo generated_link($fileurl);
        
        if (!$write_index && $schedule_exist && $date >= $today) {
            $filename = $schedule_dir . $fname . '.html';
            $fileurl = $schedule_url . $fname . '.html';
            write_file($filename, $content, 'Skor Sepakbola, liga ' . $league, 'Skor Sepakbola Terkini Liga ' . $league, 'Skor Sepakbola Terkini Liga' . $league, '', true, 'full', 5);
            insert_property_og($filename, 'Skor Sepakbola Liga ' . $league, $fileurl, '', '109215469105623', $metadesc, 'Jadwal & Skor');
            file_put_contents($filename, str_replace(ASSET_KLIMG.'assets/css/min/3.7.5/bola.css', ASSET_KLIMG.'assets/css/min/3.7.6/bola.css', file_get_contents($filename)));
            set_top_tagbar($sqlDB, $filename);
            $write_index = true;
            
            echo generated_link($fileurl);
        }

        $date = date("Y-m-d", strtotime("+1 day", strtotime($date)));
        
        $sqlDB->sql_close();
        sleep(1);
        $sqlDB = new sql_db(DBHOST, DBUSER, DBPASS, DBNAME);
        $sqlDB->sql_query('SET SESSION wait_timeout=1800');
    }
}


function schedule_check_next_date($sqlDB, $date = '', $league = '')
{
    $where_league = "";
    if ($league && $league != 'all') {
        $where_league = " AND country='$league'";
    }
    $q = "SELECT schedule FROM dbschedule WHERE level<>'0' AND DATE(schedule)>'$date' $where_league ORDER BY schedule ASC LIMIT 1";
    $r = $sqlDB->sql_query($q, true) or die(__LINE__ . ' = ' . mysql_error());
    while ($row = $sqlDB->sql_fetchrow($r)) {
        if (isset($row['schedule']) && $row['schedule']) {
            return date('Y-m-d', strtotime($row['schedule']));
        }
    }
    $q = "SELECT schedule FROM dbschedule WHERE level<>'0' AND DATE(schedule)<='$date' ORDER BY schedule DESC LIMIT 1";
    $r = $sqlDB->sql_query($q, true) or die(__LINE__ . ' = ' . mysql_error());
    while ($row = $sqlDB->sql_fetchrow($r)) {
        if (isset($row['schedule']) && $row['schedule']) {
            return date('Y-m-d', strtotime($row['schedule']));
        }
    }
    return '';
}

/**
 * generate index schedule
 * generate schedule index per leage [inggris, spain, etc]
 *
 * @map url: http://www.bola.net/jadwal_skor/$index.html
 * @map url: http://www.bola.net/jadwal_skor/$index_seri_a.html
 * @map url: http://www.bola.net/jadwal_skor/$index_la_liga.html
 */
function generate_schedule_index($sqlDB, $league = 'all')
{
    global $schedule_url, $schedule_dir, $month_list_ina, $tv_url, $tv_jadwal_url;

    $CURRENT_SEASON = '35';
    $CURRENT_SEASON_YEAR = '2014';

    $score_url = $schedule_url . 'hasil_pertandingan/';


    $menu_top = '
        <div class="topmenu">
            <a href="' . $schedule_url . 'score.html">Skor Terkini</a>
            &nbsp;&nbsp;&nbsp;
            <a href="' . $schedule_url . '">Jadwal Pertandingan</a>
            &nbsp;&nbsp;&nbsp;
            <a href="' . $tv_jadwal_url . '">Jadwal TV</a>
            &nbsp;&nbsp;
            <a href="' . $schedule_url . 'klasemen_liga_inggris.html">Klasemen</a>
            &nbsp;&nbsp;
            <a href="' . $schedule_url . 'preview.html">Preview</a>
            &nbsp;&nbsp;
            <a href="' . $schedule_url . 'review.html">Review</a>
        </div>
    ';
    $title = '';
    if ($league == 'all') {
        $where_league = '';
        $fname = 'index';
        $today = date('Y-m-d');
        generate_schedule_index_bydate($sqlDB, 'all', date("Y-m-d", strtotime("-7 day", strtotime($today))), date("Y-m-d", strtotime("+3 day", strtotime($today))));

        return '';
    } else {
        $where_league = " AND country='$league'";
        switch (strtolower($league)) {
            case 'inggris':
                $fname = 'index_liga_inggris';
                $selected_cat = 1;
                $title = 'INGGRIS LIGA PREMIER';
                break;
            case 'italia':
                $fname = 'index_seri_a';
                $selected_cat = 2;
                $title = 'ITALIA LIGA SERIE A';
                break;
            case 'spanyol':
                $fname = 'index_la_liga';
                $selected_cat = 3;
                $title = 'LA LIGA';
                break;
        }
    }


    /*$share = '
        <!--SOCIALTAB-->
        <div id="bl-social-tabs"></div>
        <script type="text/javascript">
            $(document).ready(function() {
                $("#bl-social-tabs").klsocial({
                    url:"' . $schedule_url . $fname . '.html",
                    comment_count:"-1"
                });
            });
        </script>
        <!--ENDSOCIALTAB-->
    ';*/

    $othernews = get_related_news_league($sqlDB, $selected_cat);
    $related_news = '';
    if (count($othernews) > 0) {
        $related_news = '
            <br/><div class="ntbottom">Berita Liga ' . ucwords($league) . ' Terbaru</div>
        ';
        foreach ($othernews as $v) {
            $v['schedule'] = preg_replace('/\:\d\d$/', '', $v['schedule']);
            $v['schedule'] = preg_replace('/(\d{4,4})\-(\d{2,2})\-(\d{2,2}) (\d{2,2})\:(\d{2,2})/', '\3-\2-\1 \4:\5', $v['schedule']);
            $related_news .= '
                <div class="bcontent1">
                    <span>(' . $v['schedule'] . ')</span>
                    <a href="' . BASEURL . $v['category_url'] . '/' . $v['url'] . '.html" style="font-weight:normal;">' . $v['title'] . '</a>
                </div>
            ';
        }
        $related_news .= '<div class="clear"></div>';
    }

    $q3 = "SELECT schedule, pekan FROM `dbschedule` WHERE idseason>='$CURRENT_SEASON' AND level<>'0' $where_league GROUP BY pekan ORDER BY pekan";

    $r3 = $sqlDB->sql_query($q3, true) or die(__LINE__ . ' = ' . mysql_error());
    $numn = $sqlDB->sql_numrows($r3);

    $cnt = 0;
    $show_now = time();
    $write_index = true;

    while ($row3 = $sqlDB->sql_fetchrow($r3)) {
        $content = '
            <div class="bigcon">
                <div class="bigcon2">
                    <div class="nav">
                        <a href="/" style="text-decoration:none;">HOME</a> &raquo; JADWAL PERTANDINGAN
                    </div>
                    ' . $menu_top . '
                    <div class="greentitle">JADWAL PERTANDINGAN '.$title.'</div>
                    
                    <div class="jdskor1">Pilih Kompetisi: '.dropdown_klasemen_options($league).'</div>
        ';

        $paging = schedule_paging($row3['pekan'], 38, $fname);
        $content .= $paging;

        $content .= '
            <div class="jdboxx">
        ';

        $q = "SELECT * FROM dbschedule WHERE idseason>='$CURRENT_SEASON' AND level<>'0' AND pekan='$row3[pekan]' $where_league ORDER BY schedule DESC";
        $r = $sqlDB->sql_query($q, true) or die(__LINE__ . ' = ' . mysql_error());
        $num = $sqlDB->sql_numrows($r);
        if ($num == 0) {
            continue;
        }

        while ($row = $sqlDB->sql_fetchrow($r)) {
            if ($row['level'] == '1') {
                $detail = '
                    <div class="xjdbox2c">&nbsp;</div>
                    <div class="xjdbox2g">
                        ---
                    </div>
                ';
            } else {
                $fnameDetail = getURL($row['title'] . '-' . $row['home'] . $row['home2'] . '-vs-' . $row['away'] . $row['away2'] . '-' . date('Y-m-d', strtotime($row['schedule'])));
                $filenameDetail = $score_url . $fnameDetail . '.html';

                $detail = '
                    <div class="xjdbox2c">
                        ' . $row['goal_home'] . '
                        -
                        ' . $row['goal_away'] . '
                    </div>
                    <div class="xjdbox2g">
                        <a href="' . $filenameDetail . '" class="bluelink">detail</a>
                    </div>
                ';
            }

            $schedule_time = strtotime($row['schedule']);
            list($tanggal, $jam) = explode(' ', $row['schedule']);
            $tanggal = preg_replace('/(\d+)\-(\d+)\-(\d+)/', '\3/\2/\1', $tanggal);
            $jam = preg_replace('/\:00$/', '', $jam);
            $show_time = ($schedule_time < $show_now) && ($row['level'] == '2') ? 'FULL TIME' : $tanggal . ' ' . $jam;

            $content .= '
                <div class="xjdbox2">
                    <div class="xjdbox2a">
                        ' . $show_time . ' 
                    </div>
                    <div class="xjdbox2b">
                        ' . $row['home'] . $row['home2'] . '
                        -
                        ' . $row['away'] . $row['away2'] . '
                    </div>
                    ' . $detail . '
                    <div class="clear"></div>
                </div>
            ';
        }

        $sqlDB->sql_freeresult($r);

        $content .= '
                </div>
                <div class="clear"></div>
                ' . $related_news . '
                </div>
            </div>
        ';

        $filename = $schedule_dir . $fname . $row3['pekan'] . '.html';
        $fileurl = $schedule_url . $fname . $row3['pekan'] . '.html';

        $metatitle = 'Jadwal dan Skor Pertandingan Sepak Bola Terkini, Klasemen Sementara Liga, Preview dan Review Pertandingan, Live Score';
        $metakey = 'Jadwal Pertandingan, Skor Pertandingan, Sepak Bola, Liga Inggris, Piala FA, Piala Carling, Piala Liga Inggris, Community Shield, Liga Spanyol, Piala Raja Spanyol, Liga Italia, Coppa Italia, Super Coppa Italia, Jadwal Pertandingan, Liga Super Indonesia, Piala Indonesia, Hasil Pertandingan, Klasemen, Preview Pertandingan, Review Pertandingan.';
        $metadesc = 'Jadwal dan Skor Pertandingan Sepak Bola Terkini, Klasemen Sementara Liga, Preview dan Review Pertandingan, Live Score.';

        write_file($filename, $content, $metatitle, $metakey, $metadesc, '', true, 'full', 5);
        echo "<a href=\"$fileurl\" target=\"_blank\">" . $fileurl . "</a>\n<br/>";
        insert_property_og($filename, $metatitle, $fileurl, '', '', '', 'Jadwal & Skor');

        if ($write_index) {
            $filename = $schedule_dir . $fname . '.html';
            $fileurl = $schedule_url . $fname . '.html';

            write_file($filename, $content, 'Skor Sepakbola, liga ' . $league, 'Skor Sepakbola Terkini Liga ' . $league, 'Skor Sepakbola Terkini Liga' . $league, '', true, 'full', 5);
            insert_property_og($filename, 'Skor Sepakbola Liga ' . $league, $fileurl, '', '', '', 'Jadwal & Skor');

            echo generated_link($fileurl);

            $_schedule_ = strtotime($row3['schedule']);

            /* liga spanyol, mundur 1 minggu [2013-09-21] */
            if ($league == 'spanyol' && (strtotime($row3['schedule'] . " +1 DAY") >= $show_now)) {
                $_schedule_ = strtotime($row3['schedule'] . " +1 DAY");
            }
            if ($_schedule_ > $show_now) {
                $write_index = false;
            }
        }
    }
    $sqlDB->sql_freeresult($r3);

    // empty till week 38
    for ($x = $numn + 1; $x <= 38; $x++) {
        $content = '
          <div class="bigcon">
            <div class="bigcon2">
                <div class="nav"><a href="/" style="text-decoration:none;">HOME</a> &raquo; JADWAL PERTANDINGAN</div>
                ' . $menu_top . '
                <div class="greentitle">JADWAL PERTANDINGAN</div>
                <div class="jdskor1">Pilih Kompetisi: '.dropdown_klasemen_options($league).'</div>
        ';

        $paging = schedule_paging($x, 38, $fname);
        $content .= $paging;

        $content .= '
                <div class="jdboxx">
                    <div class="xjdbox2">
                        <center>-</center>
                    </div>
                </div>
                <div class="clear"></div>
                <br/>
                <br/>
                </div>
            </div>
        ';

        $filename = $schedule_dir . $fname . $x . '.html';
        $fileurl = $schedule_url . $fname . $x . '.html';

        $metatitle = 'Jadwal dan Skor Pertandingan Sepak Bola Terkini, Klasemen Sementara Liga, Preview dan Review Pertandingan, Live Score';
        $metakey = 'Jadwal Pertandingan, Skor Pertandingan, Sepak Bola, Liga Inggris, Piala FA, Piala Carling, Piala Liga Inggris, Community Shield, Liga Spanyol, Piala Raja Spanyol, Liga Italia, Coppa Italia, Super Coppa Italia, Jadwal Pertandingan, Liga Super Indonesia, Piala Indonesia, Hasil Pertandingan, Klasemen, Preview Pertandingan, Review Pertandingan.';
        $metadesc = 'Jadwal dan Skor Pertandingan Sepak Bola Terkini, Klasemen Sementara Liga, Preview dan Review Pertandingan, Live Score.';

        write_file($filename, $content, $metatitle, $metakey, $metadesc, '', true, 'full', 5);
        insert_property_og($filename, $metatitle, $fileurl, '', '', '', 'Jadwal & Skor');
        echo generated_link($fileurl);

        if ($x == 1) {
            $filename = $schedule_dir . $fname . '.html';
            $fileurl = $schedule_url . $fname . '.html';

            write_file($filename, $content, 'Skor Sepakbola, liga ' . $league, 'Skor Sepakbola Terkini Liga ' . $league, 'Skor Sepakbola Terkini Liga' . $league, '', true, 'full', 5);
            insert_property_og($filename, 'Skor Sepakbola, liga ' . $league, $fileurl, '', '', '', 'Jadwal & Skor');
            echo generated_link($fileurl);
        }
    }
}

/**
 * generate index score
 *
 * @map url: www.bola.net/jadwal_skor/score.html
 * @map url: www.bola.net/jadwal_skor/score_$liga.html
 */
function generate_score_index($sqlDB, $league = 'all')
{
    global $tv_jadwal_url, $schedule_url, $schedule_dir, $month_list_ina, $tv_url;

    $score_dir = $schedule_dir . 'hasil_pertandingan/';
    $score_url = $schedule_url . 'hasil_pertandingan/';
    
    $array_champions = range('A', 'H'); #champions
    $share = '';
    // ub - lea;gue list
    $array = array('inggris' => 1, 'italia' => 2, 'spanyol' => 3, 'indonesia' => 4,'champions'=>5);
    $array_league_name = array('inggris' => 'Liga Premier', 'italia' => 'Liga Serie A', 'spanyol' => 'La Liga', 'indonesia' => 'Super League','champions'=>'Liga Champions');
    $array_country = array(1 => 'inggris', 2 => 'italia', 3 => 'spanyol', 4 => 'indonesia',5=>'UEFA Penyisihan');
    // end ub - league list

    /*$share = '
        <!--SOCIALTAB-->
        <div id="bl-social-tabs"></div>
        <script type="text/javascript">
            $(document).ready(function() {
                $("#bl-social-tabs").klsocial({
                    url:"'.BOLAURL.'jadwal_skor/score.html",
                    comment_count:"-1"
                });
            });
        </script>
        <!--ENDSOCIALTAB-->
    ';*/

    $menu_top = '
        <div class="topmenu">
            <a href="' . $schedule_url . 'score.html">Skor Terkini</a>
            &nbsp;&nbsp;&nbsp;
            <a href="' . $schedule_url . '">Jadwal Pertandingan</a>
            &nbsp;&nbsp;&nbsp;
            <a href="' . $tv_jadwal_url . '">Jadwal TV</a>
            &nbsp;&nbsp;
            <a href="' . $schedule_url . 'klasemen_liga_inggris.html">Klasemen</a>
            &nbsp;&nbsp;
            <a href="' . $schedule_url . 'preview.html">Preview</a>
            &nbsp;&nbsp;
            <a href="' . $schedule_url . 'review.html">Review</a>
        </div>
    ' . $share;

    if (isset($array_league_name[$league])) {
        /*if($league == 'UEFA 16 Besar'){
            $title_add = ' '.strtoupper($array_league_name[$league]);
        }else{
            $title_add = ' '.strtoupper($league).' '.strtoupper($array_league_name[$league]);
        }*/
        $title_add = ' '.strtoupper($league).' '.strtoupper($array_league_name[$league]);
    } else {
        $title_add = '';
    }
    
    $content = '
        <div class="bigcon">
            <div class="bigcon2">
                <div class="nav"><a href="/" style="text-decoration:none;">HOME</a> &raquo; SKOR TERKINI</div>
                ' . $menu_top . '
                <div class="greentitle">HASIL PERTANDINGAN'.$title_add.'</div>
                <div class="jdskor1">Pilih Kompetisi: '.dropdown_score_options($league).'</div>
    ';

    // ub - season
    $idseason = "";

    if ($league != "all") {
        $cat = $array[$league];
        $qs = "SELECT season_id FROM dbseason WHERE season_cat_id = $cat AND season_status !='0' ORDER BY season_id DESC LIMIT 1";
        $rs = $sqlDB->sql_query($qs, true) or die(__LINE__ . ' = ' . mysql_error());
        $season = $sqlDB->sql_fetchrow($rs);
        $idseason = $season["season_id"];
    }
    // end ub - season

    if ($league == 'all') {
        $where_league = '';
        $fname = 'score';
    } else {
        // ub - change to season based
        if ($league != 'champions') {
            $where_league = " AND country='" . $array_country[$array[$league]] . "' AND idseason='$idseason'";
        } else {
            //$where_league = " AND country like '%" . $array_country[$array[$league]] . "%' AND idseason='$idseason'";
            $where_league = " AND ( country like '%" . $array_country[$array[$league]] . "%' OR country LIKE '%UEFA 16 Besar%' )AND idseason='$idseason'";
        }
        
        // end ub - change to season based

        switch (strtolower($league)) {
            case 'inggris': $fname = 'score_liga_premier';
                break;
            case 'italia': $fname = 'score_seri_a';
                break;
            case 'spanyol': $fname = 'score_la_liga';
                break;
            case 'indonesia': $fname = 'score_indonesia';
                break;
            case 'champions': $fname = 'score_liga_champions';
        }
    }

    $q3 = "
        SELECT DATE(schedule) AS d FROM `dbschedule`
        WHERE schedule<=NOW() AND schedule != '0000-00-00 00-00-00' AND level='2' $where_league
        GROUP BY DATE(schedule) ORDER BY schedule DESC";
    
    $r3 = $sqlDB->sql_query($q3, true) or die(__LINE__ . ' = ' . mysql_error());
    $num3 = $sqlDB->sql_numrows($r3);
    //echo $q3 . "<hr/>" . $num3;
    
    $cnt = $page = 0;
    $standing_data = '';
    while ($row3 = $sqlDB->sql_fetchrow($r3)) {
        $q2 = "
            SELECT country, title, schedule FROM `dbschedule`
            WHERE schedule<=NOW() AND level='2' AND DATE(schedule)='$row3[d]' $where_league
            GROUP BY country, title";
        $r2 = $sqlDB->sql_query($q2, true) or die(__LINE__ . ' = ' . mysql_error());

        list($tahun, $bulan, $hari) = array_map('intval', explode('-', $row3['d']));
        $bulan = $month_list_ina[$bulan - 1];
        $date_title = $hari . ' ' . $bulan . ' ' . $tahun;
        $content .= '
            <div class="jdboxx">
                <div class="jdbox" style="color:#6B816A;">
                    <strong>' . $date_title . '</strong>
                </div>
        ';

        $show_now = time();
        while ($row2 = $sqlDB->sql_fetchrow($r2)) {
            // ub - season where clause
            $season_where = "";
            if ($league != "all") {
                $season_where = " AND idseason=$idseason ";
            }
            // end ub - season where clause

            $q = "
                SELECT * FROM dbschedule
                WHERE schedule<=NOW() AND level='2' AND country='$row2[country]' AND title='$row2[title]' AND DATE(schedule)='$row3[d]' $season_where
                ORDER BY schedule DESC";
            $r = $sqlDB->sql_query($q, true) or die(__LINE__ . ' = ' . mysql_error());
            $num = $sqlDB->sql_numrows($r);
            if ($num == 0) {
                continue;
            }

            $content .= '
                <div class="jdtgl">
                    <a href="#" class="greenlink"><strong>' . $row2['country'] . ' ' . $row2['title'] . '</strong></a>
                </div>
            ';

            while ($row = $sqlDB->sql_fetchrow($r)) {
                $fnameDetail = getURL($row['title'] . '-' . $row['home'] . $row['home2'] . '-vs-' . $row['away'] . $row['away2'] . '-' . $row3['d']);
                $filenameDetail = $score_url . $fnameDetail . '.html';
                $is_file_detail = (is_file($score_dir.$fnameDetail . '.html') ? $filenameDetail : '#');
                $detail = '<a href="' . $is_file_detail . '" class="bluelink">detail</a>';
                $schedule_time = strtotime($row['schedule']);
                list($tanggal, $jam) = explode(' ', $row['schedule']);
                $jam = preg_replace('/\:00$/', '', $jam);
                $show_time = ($schedule_time < $show_now) && ($row['level'] == '2') ? 'FT' : $jam;

                $content .= '
                    <div class="jdbox2">
                        <div class="jdbox2a">
                            ' . $show_time . ' 
                        </div>
                        <div class="jdbox2b">
                            ' . $row['home'] . $row['home2'] . '
                        </div>
                        <div class="jdbox2c">
                            ' . $row['goal_home'] . '
                        </div>
                        <div class="jdbox2d">
                            -
                        </div>
                        <div class="jdbox2e">
                            ' . $row['goal_away'] . '
                        </div>
                        <div class="jdbox2f">
                            ' . $row['away'] . $row['away2'] . '
                        </div>
                        <div class="jdbox2g">
                            ' . $detail . '
                        </div>
                </div>
                ';
                $standing_data[] = array("schedule"=>$row['schedule'],
                "home"=>$row['home'] . $row['home2'],
                "goal_home"=>$row['goal_home'],
                "goal_away"=>$row['goal_away'],
                "away"=>$row['away'] . $row['away2']);
            }
            $sqlDB->sql_freeresult($r);
            $content .= '<div class="clear"></div>';
        }
        $sqlDB->sql_freeresult($r2);
        $content .= '
                </div>
                <div class="clear"></div>
                <br/>
        ';

        $cnt++;
        if ($cnt % 10 == 0 || $cnt == $num3) {
            $paging = global_paging_10($page, ceil($num3 / 10), $fname);

            $content .= '
                        <br/>
                        ' . $paging . '
                    </div>
                </div>';

            $filename = $schedule_dir . $fname . ($page == 0 ? '' : $page) . '.html';
            $fileurl = $schedule_url . $fname . ($page == 0 ? '' : $page) . '.html';

            write_file($filename, $content, 'Skor Sepakbola', 'Skor Sepakbola Terkini', 'Skor Sepakbola Terkini', '', true, 'full', 5);
            insert_property_og($filename, 'Skor Sepakbola Terkini', $fileurl, '', '', '', 'Jadwal & Skor/Skor Terkini');
            echo generated_link($fileurl);

            $content = '
                <div class="bigcon">
                    <div class="bigcon2">
                        <div class="nav"><a href="/" style="text-decoration:none;">HOME</a> &raquo; SKOR TERKINI</div>
                        ' . $menu_top . '
                        <div class="greentitle">HASIL PERTANDINGAN'.$title_add.'</div>
                        <div class="jdskor1">Pilih Kompetisi: '.dropdown_score_options($league).'</div>
                ';
            $page++;
        }
    }
    $sqlDB->sql_freeresult($r3);
    
    
    $memcache_obj = new Memcache;
    bola_memcached_connect($memcache_obj);
    if ($standing_data) {
        $memcache_obj->set("hasil_pretandingan_".strtolower($league), serialize($standing_data), false, 24 * 3600);
    }
    
    $memcache_obj->close();
}

/**
 * Generate klasemen, liga inggris, italia, spanyol, indonesia
 * And view 10 top score each league
 */
function generate_schedule_klasemen($sqlDB)
{
    global $schedule_url, $schedule_dir, $tv_url, $tv_jadwal_url, $temporary_folder;
    
    $cache_name = '_bola_standing_data_';
    $_cache_filename_ = $temporary_folder.'klasemen/'.$cache_name;
    
    //$CURRENT_SEASON_YEAR = 2014;
    $CURRENT_SEASON_YEAR = "2017/2018";

    $array = array(1 => 'inggris', 2 => 'italia', 3 => 'spanyol');
    $array_title = array(1 => 'Liga Premier Inggris', 2 => 'Liga Italia Seri A', 3 => 'La Liga Spanyol');
    $inggris = '';

    $STANDING_STATUS_CONFIG = array(
        '1' => array(
            '1' => 'champion',
            '2' => 'champion',
            '3' => 'champion',
            '4' => 'champion_qual',
            '5' => 'euro',
            '18' => 'relegation',
            '19' => 'relegation',
            '20' => 'relegation'
        ),
        '2' => array(
            '1' => 'champion',
            '2' => 'champion',
            '3' => 'champion_qual',
            '4' => 'euro',
            '5' => 'euro',
            '18' => 'relegation',
            '19' => 'relegation',
            '20' => 'relegation'
        ),
        '3' => array(
            '1' => 'champion',
            '2' => 'champion',
            '3' => 'champion',
            '4' => 'champion_qual',
            '5' => 'euro',
            '6' => 'euro',
            '18' => 'relegation',
            '19' => 'relegation',
            '20' => 'relegation'
        )
    );
    
    $menu_top = '
        <div class="topmenu">
            <a href="' . $schedule_url . 'score.html">Skor Terkini</a>
            &nbsp;&nbsp;&nbsp;
            <a href="' . $schedule_url . '">Jadwal Pertandingan</a>
            &nbsp;&nbsp;&nbsp;
            <a href="' . $tv_jadwal_url . '">Jadwal TV</a>
            &nbsp;&nbsp;
            <a href="' . $schedule_url . 'klasemen_liga_inggris.html">Klasemen</a>
            &nbsp;&nbsp;
            <a href="' . $schedule_url . 'preview.html">Preview</a>
            &nbsp;&nbsp;
            <a href="' . $schedule_url . 'review.html">Review</a>
        </div>
    ';

    $memcache_obj = new Memcache;
    bola_memcached_connect($memcache_obj);

    foreach ($array as $k => $v) {
        $STANDING_STATUS = isset($STANDING_STATUS_CONFIG[$k])?$STANDING_STATUS_CONFIG[$k]:'';
        $standing_data_latest = $memcache_obj->get($cache_name . $k);
        
        $standing_data_latest = false;
        //get from file
        if (!$standing_data_latest) {
            if (is_file($_cache_filename_. $k)) {
                $standing_data_latest = file_get_contents($_cache_filename_. $k);
            }
        }
        
        if ($standing_data_latest) {
            $standing_data_latest = unserialize($standing_data_latest);
            $_temp_ = array();
            foreach ($standing_data_latest as $key => $item) {
                $item['mark'] = isset($item['mark'])?$item['mark']:'';
                $_temp_[$item['club']] = $item;
            }
            
            $standing_data_latest = $_temp_;
        }
        
        $standing_data = array();
        /*$share = '
            <!--SOCIALTAB-->
            <div id="bl-social-tabs"></div>
            <script type="text/javascript">
            $(document).ready(function() {
                $("#bl-social-tabs").klsocial({
                    url:"' . $schedule_url . 'klasemen_liga_' . $v . '.html",
                    comment_count:"-1"
                });
            });
            </script>
            <!--ENDSOCIALTAB-->
        ';*/

        $additional_text = '';
        switch ($k) {
            case 1: $infotext = '<div style="padding: 5px 0px 0px 0px;">Update terbaru Klasemen Liga Inggris ' . $CURRENT_SEASON_YEAR . ' dari pertandingan Liga Premier Inggris terakhir. Simak juga daftar <a href="#topskorman" class="bluelink">Top Skor</a> sementara.</div>';
                $metatitle = 'Klasemen Liga Inggris - Update Terbaru';
                $metadesc = 'Klasemen Liga Inggris ' . $CURRENT_SEASON_YEAR . ', update terbaru Live Score pertandingan Liga Inggris terakhir, disertai daftar Top Skor';
                break;
            case 2: $infotext = '<div style="padding: 5px 0px 0px 0px;">Update terbaru Klasemen Liga Italia ' . $CURRENT_SEASON_YEAR . ' dari pertandingan Liga Serie A Italia terakhir. Simak juga daftar <a href="#topskorman" class="bluelink">Top Skor</a> sementara.</div>';
                $metatitle = 'Klasemen Liga Italia - Update Terbaru';
                $metadesc = 'Klasemen Liga Italia ' . $CURRENT_SEASON_YEAR . ', update terbaru Live Score pertandingan Liga Italia terakhir, disertai daftar Top Skor';
                //$additional_text = 'Parma terkena sanksi pengurangan 3 poin';
                break;
            case 3: $infotext = '<div style="padding: 5px 0px 0px 0px;">Update terbaru Klasemen Liga Spanyol ' . $CURRENT_SEASON_YEAR . ' dari pertandingan La Liga Spanyol terakhir. Simak juga daftar <a href="#topskorman" class="bluelink">Top Skor</a> sementara.</div>';
                $metatitle = 'Klasemen Liga Spanyol - Update Terbaru';
                $metadesc = 'Klasemen Liga Spanyol ' . $CURRENT_SEASON_YEAR . ', update terbaru Live Score pertandingan La Liga Spanyol terakhir, disertai daftar Top Skor';
                break;
            case 4: $infotext = '<div style="padding: 5px 0px 0px 0px;">Update terbaru Klasemen Liga Super Indonesia ' . $CURRENT_SEASON_YEAR . ' dari pertandingan ISL terakhir. Simak juga daftar <a href="#topskorman" class="bluelink">Top Skor</a> sementara.</div>';
                $metatitle = 'Klasemen Liga Indonesia ISL - Update Terbaru';
                $metadesc = 'Klasemen Liga Indonesia ISL ' . $CURRENT_SEASON_YEAR . ', update terbaru Live Score pertandingan Liga Indonesia ISL terakhir, disertai daftar Top Skor';
                break;
            default: $infotext = '';
        }

        $recent_news = '
            <div class="greentitle" style="margin-bottom:10px; margin-top:10px; text-align:left; font-size:18px">
                BERITA TERBARU ' . (strtoupper($array_title[$k])) . '
            </div>
            <ul style="list-style:none; margin:0px; padding:0px">
        ';

        $q = $sqlDB->sql_query("
                    SELECT a.title, a.url, a.schedule, b.category_url FROM dbnews a, dbcategory b
                    WHERE a.category = b.category_id AND a.category = '" . ($k == 27 ? 4 : $k) . "' AND a.level != '0' AND a.schedule <= NOW()
                    ORDER BY a.schedule DESC LIMIT 10");
        while ($row = $sqlDB->sql_fetchrow($q)) {
            $recent_news .= '
                <li style="padding:5px 10px; border-bottom:1px dashed #aaa">
                    <span style="color:#666">(' . date("d-m-Y H:i", strtotime($row["schedule"])) . ')</span>
                    <a href="/' . $row["category_url"] . '/' . $row["url"] . '.html" class="greenlink">' . $row["title"] . '</a>
                </li>
            ';
        }

        $recent_news .= '
            </ul>
        ';

        $content = '
            <div class="bigcon">
                <div class="bigcon2">
                    <div class="nav">
                        <a href="/" style="text-decoration:none;">HOME</a> &raquo;
                        KLASEMEN ' . (strtoupper($array_title[$k])) . '
                    </div>
                    ' . $menu_top . '
                    <h1 class="greentitle" style="margin: 0px;">Klasemen ' . (ucwords($array_title[$k])) . '</h1>
                    ' . $infotext . ' 
                    <div class="jdskor1">Pilih Kompetisi: '.dropdown_klasemen_options($v).'</div>
                    <!--SKLAS-->
                    <div class="klasemen_row">
                        <div class="jdsmall1"><strong>No</strong></div>
                        <div class="jdwide1"><span class="mark ">&nbsp;</span><strong>Klub</strong></div>
                        <div class="jdsmall1"><strong>MN</strong></div>
                        <div class="jdsmall1"><strong>M</strong></div>
                        <div class="jdsmall1"><strong>S</strong></div>
                        <div class="jdsmall1"><strong>K</strong></div>
                        <div class="jdsmall1"><strong>MG</strong></div>
                        <div class="jdsmall1"><strong>KG</strong></div>
                        <div class="jdsmall1"><strong>SG</strong></div>
                        <div class="jdsmall1"><strong>Poin</strong></div>
                        <br class="clear" />
                    </div>
                    
                <!--SSTANDINGS-->
        ';

        $qs = "
            SELECT season_id FROM dbseason
            WHERE season_cat_id='$k' AND season_status='1' ORDER BY season_id DESC LIMIT 1";
        $rs = $sqlDB->sql_query($qs, true);
        $rows = $sqlDB->sql_fetchrow($rs);
        $season_id = $rows['season_id'];

        $q = "
            SELECT A.team_id, A.team_name FROM dbteam A, dbparticipant B
            WHERE part_season_id='$season_id' AND team_id=part_team_id AND B.part_status<>'0' ORDER BY team_name";

        $r = $sqlDB->sql_query($q, true);
        if (!$r) {
            return false;
        }

        $tmp_file = LOGDIR . 'klasemen_tmp_file.txt';
        $tmp_file2 = LOGDIR . 'klasemen_tmp_file2.txt';
        if (is_file($tmp_file)) {
            unlink($tmp_file);
        }
        if (is_file($tmp_file2)) {
            unlink($tmp_file2);
        }
        $save_arr = array();
        while ($row = $sqlDB->sql_fetchrow($r)) {
            $club_data = get_schedule_club_data($sqlDB, $row['team_name'], $season_id);
            $ab = $club_data['home_goal'] - $club_data['away_goal'];
            
            file_put_contents($tmp_file, $row['team_id'] . "\t" . $club_data['point'] . "\t" . $ab . "\t" . $club_data['home_goal'] . "\t" . $row['team_name'] . "\n", FILE_APPEND);
            $save_arr[$row['team_id']] = $club_data;
        }
        exec('cat ' . $tmp_file . ' | sort -k2.1nr -k3.1nr -k4.1nr -k5 > ' . $tmp_file2, $out);
        $sqlDB->sql_freeresult($r);
        $sqlDB->sql_freeresult($rs);

        $css = 2;
        $counter = 1;
        $arr_data = array_filter(array_map('trim', file($tmp_file2)));

        // spesial untuk member page
        $member_page_file = LOGDIR . 'member_page_' . $v . '.txt';
        if (is_file($member_page_file)) {
            file_put_contents($member_page_file, "");
        }

        //spanyol head to head
        if ($k == '3') {
            $tmp_point = $is_headtohead = array();
            foreach ($arr_data as $vdata) {
                list($team_id, $team_point, $team_selisih, $away_goal, $team_name) = explode("\t", $vdata);
                if (!array_key_exists($team_point, $tmp_point)) {
                    $tmp_point[$team_point]['team'] = $team_name;
                    $tmp_point[$team_point]['home_goal'] = (int) $away_goal;
                    $tmp_point[$team_point]['away_goal'] = $away_goal - $team_selisih;
                } else {
                    if (!array_key_exists($team_point, $is_headtohead)) {
                        $is_headtohead[$team_point] = array($team_name, $tmp_point[$team_point]['team'], $save_arr[$team_id]['home_goal'], $save_arr[$team_id]['away_goal'], $tmp_point[$team_point]['home_goal'], $tmp_point[$team_point]['away_goal']);
                    }
                }
            }
            
            if (count($is_headtohead) > 0) {
                $is_headtohead_arr = $is_headtohead;
                
                foreach ($is_headtohead_arr as $is_headtohead) {
                    $club1 = $is_headtohead[0];
                    $club2 = $is_headtohead[1];
                    $club1_gf = $is_headtohead[2];
                    $club1_ga = $is_headtohead[3];
                    $club2_gf = $is_headtohead[4];
                    $club2_ga = $is_headtohead[5];
                    $headtoheadwinner = generate_schedule_headtohead_spanyol($sqlDB, $season_id, $club1, $club2, $club1_gf, $club1_ga, $club2_gf, $club2_ga);

                    $club1_pos = -1;
                    $club2_pos = -1;
                    $league_pos = 0;
                    foreach ($arr_data as $vdata) {
                        list($team_id, $team_point, $team_selisih, $away_goal, $team_name) = explode("\t", $vdata);
                        if ($team_name == $club1) {
                            $club1_pos = $league_pos;
                        }
                        if ($team_name == $club2) {
                            $club2_pos = $league_pos;
                        }
                        $league_pos++;
                    }
                    reset($arr_data);

                    if ($headtoheadwinner == 1 && $club1_pos > $club2_pos) {
                        $switch_tmp = $arr_data[$club1_pos];
                        $arr_data[$club1_pos] = $arr_data[$club2_pos];
                        $arr_data[$club2_pos] = $switch_tmp;
                    } elseif ($headtoheadwinner == 2 && $club1_pos < $club2_pos) {
                        $switch_tmp = $arr_data[$club1_pos];
                        $arr_data[$club1_pos] = $arr_data[$club2_pos];
                        $arr_data[$club2_pos] = $switch_tmp;
                    }
                }
            }
            
            
            //sementar stuck eibar granada switched
            /*$switch_tmp = $arr_data[16];
            $arr_data[16] = $arr_data[17];
            $arr_data[17] = $switch_tmp;

            echo 'Here ARR DATA '.__FUNCTION__.' :  '.__LINE__.'<br /><br />';
            echo '<pre>';
            var_export($arr_data);
            echo '</pre>';*/
        }
        
        foreach ($arr_data as $vdata) {
            list($team_id, $team_point, $team_selisih, $away_goal, $team_name) = explode("\t", $vdata);
            $xdata = $save_arr[$team_id];
            $team_name = trim($team_name);
            
            $q2 = "SELECT url FROM dbarticles WHERE team_id='$team_id' AND category='16' LIMIT 1";
            $r2 = $sqlDB->sql_query($q2);
            $row2 = $sqlDB->sql_fetchrow($r2);
            
            $_standing_club_url_ = '';
            if ($row2['url']) {
                $_standing_club_url_ = $row2['url'];
            }
            
            $mark = '';
            
            #khusus inggris,italia,spanyol panah up and down dinonaktifkan dulu, karena belum ada pertandingan
            if (($k != 3)) {
                if (isset($standing_data_latest[$team_name])) {
                    $_team_last_ = $standing_data_latest[$team_name];
                    
                    $mark = $_team_last_['mark'];
                    if ($_team_last_['no'] != $counter) {
                        if ($_team_last_['no'] > $counter) {
                            $mark = 'up';
                        }
                        if ($_team_last_['no'] < $counter) {
                            $mark = 'down';
                        }
                    } else {
                        if ($_team_last_['played'] != $xdata['played']) {
                            $mark = '';
                        }
                    }
                }
            }
            $css_row = 'rank ';
            if ($STANDING_STATUS) {
                $css_row .= isset($STANDING_STATUS[$counter])?$STANDING_STATUS[$counter]:'';
            }
            
            $_gd_ = $xdata['home_goal'] - $xdata['away_goal'];
            if ($_gd_ > 0) {
                $_gd_ = '+'.$_gd_;
            } elseif ($_gd_ > 0) {
                $_gd_ = '-'.$_gd_;
            }
            $content .= '
                <div class="klasemen_row">
                    <div class="jdsmall' . $css . '"><span class="'.$css_row.'">' . $counter . '</span></div>
                    <div class="jdwide' . $css . '"><span class="mark '.$mark.'">&nbsp;</span><strong><a href="/club/' . $row2['url'] . '.html" class="greenlink">'.trim($team_name) . '</a></strong></div>
                    <div class="jdsmall' . $css . '">' . $xdata['played'] . '</div>
                    <div class="jdsmall' . $css . '">' . $xdata['win'] . '</div>
                    <div class="jdsmall' . $css . '">' . $xdata['draw'] . '</div>
                    <div class="jdsmall' . $css . '">' . $xdata['loose'] . '</div>
                    <div class="jdsmall' . $css . '">' . $xdata['home_goal'] . '</div>
                    <div class="jdsmall' . $css . '">' . $xdata['away_goal'] . '</div>
                    <div class="jdsmall' . $css . '">' . $_gd_ . '</div>
                    <div class="jdsmall' . $css . '"><strong>' . $xdata['point'] . '</strong></div>
                    <br class="clear" />
                </div>
            ';

            file_put_contents($member_page_file, $counter . "\t" . trim($team_name) . "\t" . $xdata['played'] . "\t" . $xdata['point'] . "\n", FILE_APPEND);

            if ($counter == 3) {
                $content .= '<!--EKLAE-->';
            }

            $css = $css == 1 ? 2 : 1;
            $zone_start = '';
            $zone_end = '';
            $deduction_point = '0';
            if ($team_id) {
                $klasement_query = "REPLACE INTO dbklasemen (`rank`, `last_rank`, `zone_start`, `zone_end`, `team_id`, `club_name`, `session_id`, `matches_total`, `matches_won`, `matches_draw`, `matches_lost`, `goals_pro`, `goals_against`, `points`, `deduction_points`, `league`, `last_update_time`) VALUES (
        '$counter','$counter','$zone_start','$zone_end','$team_id','$team_name','$season_id','" . $xdata['played'] . "','" . $xdata['win'] . "','" . $xdata['draw'] . "','" . $xdata['loose'] . "','" . $xdata['home_goal'] . "','" . $xdata['away_goal'] . "','" . $xdata['point'] . "','$deduction_point','$v',NOW()
        )";
                $sqlDB->sql_query($klasement_query);
            }
            
            $standing_data[] = array(
                'no' => $counter,
                'club' => $team_name,
                'club_url' => $_standing_club_url_,
                'played' => $xdata['played'],
                'win' => $xdata['win'],
                'draw' => $xdata['draw'],
                'lost' => $xdata['loose'],
                'home_goal' => $xdata['home_goal'],
                'away_goal' => $xdata['away_goal'],
                'point' => $xdata['point'],
                'mark' => $mark,
                'css_rank' => $css_row
            );
            
            $counter++;
        }
        
        //cache
        if ($standing_data) {
            $memcache_obj->set($cache_name . $k, serialize($standing_data), false, 24 * 7200);
            file_put_contents($_cache_filename_. $k, serialize($standing_data));
        }
        
        
        
        $topscore = get_ten_topscore($v);

        $additional_text_html = '';
        if ($additional_text) {
            $additional_text_html = '<p><strong>Keterangan</strong><br/>'.$additional_text.'</p>';
        }
        $content .= '
                    <!--ESTANDINGE-->    
                    '.$additional_text_html.'
                    <div class="klasemen_note">
                        <p><span class="note_color champion">&nbsp;</span><span class="note_text">UEFA Champions League</span></p>
                        <p><span class="note_color champion_qual">&nbsp;</span><span class="note_text">Champions League Qualifier</span></p>
                        <p><span class="note_color euro">&nbsp;</span><span class="note_text">UEFA Europa League</span></p>
                        <p style="margin: 0px; width: 90px;"><span class="note_color relegation">&nbsp;</span><span class="note_text" style="width: 65px;">Relegation</span></p>
                        <br class="clear"/>
                    </div>
                    <br/>' . $topscore . '
                    <br/>' . $recent_news . '
                </div>
            </div>
        ';

        $filename = $schedule_dir . 'klasemen_liga_' . $v . '.html';
        $fileurl = $schedule_url . 'klasemen_liga_' . $v . '.html';

        $metakey = explode(' ', trim(strtolower($metadesc)));
        $metakey = array_unique(array_filter(array_map('trim', $metakey)));
        $metakey = array_slice($metakey, 0, 50);
        $metakey = implode(',', $metakey);
        write_file($filename, $content, $metatitle, $metakey, $metadesc, '', true, 'full', 5);
        insert_property_og($filename, 'Klasemen Liga Inggris, Liga Italia, Liga Spanyol', $fileurl, '', '', $metadesc, 'Jadwal & Skor/Klasemen');
        
        echo generated_link($fileurl);
    }
    $memcache_obj->close();
}

/**
 * get 10 top score
 *
 */
function get_ten_topscore($league)
{
    $file = CACHEDIR . 'cache_topscore_' . md5($league);
    if (!is_file($file)) {
        return '';
    }
    $data = file($file);
    $data = array_slice($data, 0, 10);
    $content = '
        <a name="topskorman"></a>
        <div class="greentitle">TOP SKOR</div>
        <!--SSCRS-->
        <div class="jdtop1"><strong>No</strong></div>
        <div class="jdtopa1"><strong>Nama</strong></div>
        <div class="jdtop1"><strong>Goal</strong></div>
        <div class="jdtopa1"><strong>Klub</strong></div>
        <div class="clear"></div>
    ';
    $count = 1;
    foreach ($data as $v) {
        $arr = explode("\t", trim($v));
        $player = isset($arr[0]) ? $arr[0] : '';
        $goal = isset($arr[1]) ? $arr[1] : '';
        $team = isset($arr[2]) ? $arr[2] : '';
        $url = isset($arr[3]) ? $arr[3] : '';
        $counter = $count % 2 == 1 ? 2 : 1;
        $content .= '
            <div class="jdtop' . $counter . '">' . $count . '</div>
            <div class="jdtopa' . $counter . '"><a href="/profile/' . $url . '/" class="greenlink">' . $player . '</a></div>
            <div class="jdtop' . $counter . '">' . $goal . '</div>
            <div class="jdtopa' . $counter . '">' . $team . '</div>
            <div class="clear"></div>
        ';
        if ($count == 3) {
            $content .= '<!--ESCRE-->';
        }
        $count++;
    }
    $content .= '
        <div style="text-align:right;">
            <br/>
            <a href="topskor_liga_' . $league . '.html" class="greenlink"><b>Top Skor Selengkapnya</b></a>
        </div>
    ';
    @unlink($file);

    return $content;
}

/**
 * get club schedule
 */
function get_schedule_club_data($sqlDB, $club, $season_id, $title = '')
{
    $data = array(
        'played' => 0,
        'point' => 0,
        'home' => 0,
        'away' => 0,
        'win' => 0,
        'draw' => 0,
        'loose' => 0,
        'home_goal' => 0, // total goal, ngegoalin
        'away_goal' => 0 // conceded, kebobolan
    );
    
    $where_title = '';
    if ($title) {
        $where_title = " AND title = '$title' ";
    }
    $q = "
        SELECT * FROM dbschedule
        WHERE (TRIM(home)='$club' OR TRIM(home2)='$club' OR TRIM(away)='$club' OR TRIM(away2)='$club') AND level='2' AND schedule < NOW() AND schedule <> '0000-00-00 00:00:00' AND idseason='$season_id' $where_title
        ORDER BY schedule";

    $r = $sqlDB->sql_query($q);

    $data['played'] = $sqlDB->sql_numrows($r);
    while ($row = $sqlDB->sql_fetchrow($r)) {
        $row['home'] = trim($row['home']);
        $row['home2'] = trim($row['home2']);
        $row['away'] = trim($row['away']);
        $row['away2'] = trim($row['away2']);
        if ($row['home'] == $club || $row['home2'] == $club) {
            if ($row['goal_home'] > $row['goal_away']) {
                $data['win'] ++;
                $data['point'] += 3;
            } elseif ($row['goal_home'] == $row['goal_away']) {
                $data['draw'] ++;
                $data['point'] += 1;
            } else {
                $data['loose'] ++;
            }

            $data['home_goal'] += $row['goal_home'];
            $data['away_goal'] += $row['goal_away'];
            $data['home'] ++;
        } elseif ($row['away'] == $club || $row['away2'] == $club) {
            if ($row['goal_away'] > $row['goal_home']) {
                $data['win'] ++;
                $data['point'] += 3;
            } elseif ($row['goal_home'] == $row['goal_away']) {
                $data['point'] += 1;
                $data['draw'] ++;
            } else {
                $data['loose'] ++;
            }

            $data['home_goal'] += $row['goal_away'];
            $data['away_goal'] += $row['goal_home'];
            $data['away'] ++;
        }
    }
    $sqlDB->sql_freeresult($r);

    //parma #20150413
    if ($season_id == '47' && strtolower($club) == 'parma') {
        $data['point'] = $data['point'] - 3;
    }
    
    return $data;
}

function get_schedule_club_data_euro($sqlDB, $club, $season_id, $title = '')
{
    $data = array(
        'played' => 0,
        'point' => 0,
        'home' => 0,
        'away' => 0,
        'win' => 0,
        'draw' => 0,
        'loose' => 0,
        'home_goal' => 0, // total goal, ngegoalin
        'away_goal' => 0 // conceded, kebobolan
    );
    
    $where_title = '';
    if ($title) {
        $where_title = " AND title = '$title' ";
    }
    $q = "
        SELECT * FROM dbschedule
        WHERE (TRIM(home)='$club' OR TRIM(home2)='$club' OR TRIM(away)='$club' OR TRIM(away2)='$club') AND level='2' AND schedule<NOW() AND schedule<>'0000-00-00 00:00:00' AND idseason='$season_id' $where_title
        ORDER BY schedule";
    //echo $q."<br/>";
    $r = $sqlDB->sql_query($q);

    $data['played'] = $sqlDB->sql_numrows($r);
    while ($row = $sqlDB->sql_fetchrow($r)) {
        $row['home'] = trim($row['home']);
        $row['home2'] = trim($row['home2']);
        $row['away'] = trim($row['away']);
        $row['away2'] = trim($row['away2']);
        if ($row['home'] == $club || $row['home2'] == $club) {
            if ($row['goal_home'] > $row['goal_away']) {
                $data['win'] ++;
                $data['point'] += 3;
            } elseif ($row['goal_home'] == $row['goal_away']) {
                $data['draw'] ++;
                $data['point'] += 1;
            } else {
                $data['loose'] ++;
            }

            $data['home_goal'] += $row['goal_home'];
            $data['away_goal'] += $row['goal_away'];
            $data['home'] ++;
        } elseif ($row['away'] == $club || $row['away2'] == $club) {
            if ($row['goal_away'] > $row['goal_home']) {
                $data['win'] ++;
                $data['point'] += 3;
            } elseif ($row['goal_home'] == $row['goal_away']) {
                $data['point'] += 1;
                $data['draw'] ++;
            } else {
                $data['loose'] ++;
            }

            $data['home_goal'] += $row['goal_away'];
            $data['away_goal'] += $row['goal_home'];
            $data['away'] ++;
        }
    }
    $sqlDB->sql_freeresult($r);

    //parma #20150413
    if ($season_id == '47' && strtolower($club) == 'parma') {
        $data['point'] = $data['point'] - 3;
    }
    
    return $data;
}

/**
 * generate top score page
 *
 */
function generate_schedule_topscore($sqlDB)
{
    global $schedule_url, $schedule_dir, $tv_url, $tv_jadwal_url, $profile_dir, $profile_url;

    $array = array(1 => 'inggris', 2 => 'italia', 3 => 'spanyol');
    $array_title = array(1 => 'Liga Premier Inggris', 2 => 'Liga Italia Seri A', 3 => 'La Liga Spanyol');

    $inggris = '';

    $menu_top = '
        <div class="topmenu">
            <a href="' . $schedule_url . 'score.html">Skor Terkini</a>
            &nbsp;&nbsp;&nbsp;
            <a href="' . $schedule_url . '">Jadwal Pertandingan</a>
            &nbsp;&nbsp;&nbsp;
            <a href="' . $tv_jadwal_url . '">Jadwal TV</a>
            &nbsp;&nbsp;
            <a href="' . $schedule_url . 'klasemen_liga_inggris.html">Klasemen</a>
            &nbsp;&nbsp;
            <a href="' . $schedule_url . 'preview.html">Preview</a>
            &nbsp;&nbsp;
            <a href="' . $schedule_url . 'review.html">Review</a>
        </div>
    ';
    $menu_top .= '
        <div class="topmenu">
            <a href="'.$schedule_url.'topskor_liga_inggris.html">Liga Inggris</a>
            &nbsp;&nbsp;&nbsp;
            <a href="'.$schedule_url.'topskor_liga_italia.html">Liga Italia</a>
            &nbsp;&nbsp;&nbsp;
            <a href="'.$schedule_url.'topskor_liga_spanyol.html">Liga Spanyol</a>
            &nbsp;&nbsp;
            <a href="'.$schedule_url.'topskor_liga_indonesia.html">ISL</a>
            &nbsp;&nbsp;
            <a href="'.$schedule_url.'topskor_liga_champions.html">Liga Champions</a>
        </div>
    ';
    $metatitle = "";
    foreach ($array as $k => $v) {
        $metatitle = ucfirst($v);
        $content = '
            <div class="bigcon">
                <div class="bigcon2">
                    <div class="nav">
                        <a href="/" style="text-decoration:none;">HOME</a> &raquo;
                        KLASEMEN ' . (strtoupper($array_title[$k])) . '
                    </div>
                    ' . $menu_top . '
                    <br/>
                    <div class="greentitle">Top Skor ' . (ucwords($array_title[$k])) . '</div>
                    <br/>
        ';

        $qs = "SELECT season_id FROM dbseason WHERE season_cat_id='$k' AND season_status='1' ORDER BY season_id DESC LIMIT 1";
        $rs = $sqlDB->sql_query($qs, true) or die(__LINE__ . ' = ' . mysql_error());
        $rows = $sqlDB->sql_fetchrow($rs);
        $season_id = $rows['season_id'];

        $tmp_file = BOLADIR . 'topscore_tmp_file.txt';
        $tmp_file2 = BOLADIR . 'topscore_tmp_file2.txt';
        if (is_file($tmp_file)) {
            unlink($tmp_file);
        }
        if (is_file($tmp_file2)) {
            unlink($tmp_file2);
        }

        $q = "
            SELECT A.team_id, A.team_name FROM dbteam A, dbparticipant B
            WHERE part_season_id='$season_id' AND team_id=part_team_id AND B.part_status<>'0'
            ORDER BY team_name";
        $r = $sqlDB->sql_query($q, true) or die(__LINE__ . ' = ' . mysql_error());
        while ($row = $sqlDB->sql_fetchrow($r)) {
            $qsc = "SELECT * FROM dbschedule WHERE (home='$row[team_name]' OR home2='$row[team_name]') AND idseason='$season_id' AND level='2' AND schedule<>'0000-00-00 00:00:00'";
            
            $rsc = $sqlDB->sql_query($qsc, true) or die(__LINE__ . ' = ' . mysql_error());

            while ($rowsc = $sqlDB->sql_fetchrow($rsc)) {
                $qd = "SELECT * FROM dbscore WHERE id_schedule='$rowsc[id]' AND info='goal' AND level='1'";
                $rd = $sqlDB->sql_query($qd, true) or die(__LINE__ . ' = ' . mysql_error());
                while ($rowd = $sqlDB->sql_fetchrow($rd)) {
                    $is_my_team = '';

                    /* get player club from player_profile (case midseason player transfer) [2013-08-27] */
                    $rowd['player_name'] = kln_real_escape_string($rowd['player_name']);
                    $qplayerclub = "SELECT B.team_name as player_club FROM player_profile A JOIN dbteam B ON A.player_club = B.team_id WHERE (player_name='{$rowd['player_name']}' OR player_fullname='{$rowd['player_name']}') AND player_status='1' LIMIT 1";
                    $rplayerclub = $sqlDB->sql_query($qplayerclub, true) or die(__LINE__ . ' = ' . mysql_error());
                    while ($rowplayerclub = $sqlDB->sql_fetchrow($rplayerclub)) {
                        $is_my_team = (isset($rowplayerclub['player_club']) && $rowplayerclub['player_club']) ? str_replace(' ', '_', $rowplayerclub['player_club']) : '';
                    }
                    /* end of midseason transfer */
                    
                    $is_my_team = '';
                    if (!$is_my_team) {
                        $is_my_team = ($rowd['home'] == 1) ? $rowsc['home'] . $rowsc['home2'] : $rowsc['away'] . $rowsc['away2'];
                    }
                    file_put_contents($tmp_file, str_replace(' ', '_', $rowd['player_name']) . "\t" . str_replace(' ', '_', $is_my_team) . "\n", FILE_APPEND);
                }
                $sqlDB->sql_freeresult($rd);
            }
            $sqlDB->sql_freeresult($rsc);
        }
        $sqlDB->sql_freeresult($r);
        $sqlDB->sql_freeresult($rs);

        exec('cat ' . $tmp_file . ' | awk \'{data[$1]+=1;kelub[$1]=$2;} END {for (x in data) {print x "\t" data[x] "\t" kelub[x]}}\' | sort -k2nr > ' . $tmp_file2);
        $data = array_filter(array_map('trim', file($tmp_file2)));
        $count = 2;
        $counter = 1;
        $content .= '
            <div class="jdtop1"><strong>No</strong></div>
            <div class="jdtopa1"><strong>Nama</strong></div>
            <div class="jdtop1"><strong>Goal</strong></div>
            <div class="jdtopa1"><strong>Klub</strong></div>
            <div class="clear"></div>
        ';
        
        $topscore_data = array();
        $cache_topscore = '';

        foreach ($data as $vdata) {
            list($player, $goal, $team) = explode("\t", $vdata);
            $player = str_replace('_', ' ', $player);
            $team = str_replace('_', ' ', $team);

            $qp = "SELECT player_url FROM player_profile WHERE player_name='" . kln_real_escape_string($player) . "' LIMIT 1";
            $rp = $sqlDB->sql_query($qp, true) or die(__LINE__ . ' = ' . mysql_error());
            $rowp = $sqlDB->sql_fetchrow($rp);

            $_player_name_ = stripslashes($player);
            $_player_url_ = $profile_url;
            if (isset($rowp['player_url']) && $rowp['player_url'] && is_file($profile_dir.$rowp['player_url'].'/index.html')) {
                $_player_name_ = '<a href="/profile/' . $rowp['player_url'] . '/" class="greenlink">' . stripslashes($player) . '</a>';
                $_player_url_ = $profile_url.$rowp['player_url'].'/';
            }
            
            $content .= '
                <div class="jdtop' . $count . '">' . $counter . '</div>
                <div class="jdtopa' . $count . '">'.$_player_name_.'</div>
                <div class="jdtop' . $count . '">' . $goal . '</div>
                <div class="jdtopa' . $count . '">' . $team . '</div>
                <div class="clear"></div>
            ';
            $cache_topscore .= stripslashes($player) . "\t$goal\t$team\t" . $rowp['player_url'] . "\n";

            $count = $count == 1 ? 2 : 1;
            $counter++;

            $sqlDB->sql_freeresult($rp);
            
            if ($counter <= 25) {
                $topscore_data[] = array(
                    'rank' => $counter,
                    'player' => stripslashes($player),
                    'player_url' => $_player_url_,
                    'team' => $team,
                    'goal' => $goal,
                );
            }
        }
        
        if ($topscore_data) {
            $memcache = new Memcache;
            bola_memcached_connect($memcache);
            $memcache->set('bolanet_topscore_data_'.$k, serialize($topscore_data), false, 24*3600);
            write_file_direct(TEMPDIR.'topscore/bolanet_topscore_data_'.$k, serialize($topscore_data));
            $memcache->close();
        }
    
        file_put_contents(CACHEDIR . 'cache_topscore_' . md5($v), $cache_topscore);
        file_put_contents(CACHEDIR . 'xcache_topscore_' . md5($v), $cache_topscore);

        $content .= '
                    <br/>
                </div>
            </div>
        ';

        $filename = $schedule_dir . 'topskor_liga_' . $v . '.html';
        $fileurl = $schedule_url . 'topskor_liga_' . $v . '.html';

        write_file($filename, $content, 'Top Skor Liga '.$metatitle, 'Top Skor Klasemen Liga Inggris, Top Skor Liga Italia, Top Skor Liga Spanyol', 'Top Skor Klasemen Liga Inggris, Top Skor Liga Italia, Top Skor Liga Spanyol', '', true, 'full', 5);
        echo generated_link($fileurl);
    }
}

/**
 * pagination for schedule
 *
 */
function schedule_paging($page, $total, $url)
{
    global $cdn_url;

    $return = '';
    $start = $page - 6;
    if ($start < 1) {
        $start = 1;
    }
    $end = $start + 13;
    if ($end > $total) {
        $start = $start - ($end - $total);
        $end = $total;
        if ($start < 1) {
            $start = 1;
        }
    }

    $linkleft = $page - 1 < 1 ? 1 : $page - 1;
    $linkright = $page + 1 > $total ? $total : $page + 1;

    $left = '<a href="' . $url . $linkleft . '.html"><img src="' . $cdn_url . 'library/i/v1/scheduleleft.jpg" alt="" style="margin:-5px;"/></a> ';
    $right = ' <a href="' . $url . $linkright . '.html"><img src="' . $cdn_url . 'library/i/v1/scheduleright.jpg" alt="" style="margin:-5px;"/></a>';

    for ($i = $start; $i <= $end; $i++) {
        if ($i == $page) {
            $return .= '<span class="paging_nolink" style="border:1px solid #ffffff;">' . str_pad($i, 2, '0', STR_PAD_LEFT) . '</span>';
        } else {
            $return .= '<span class="paging"><a href="' . $url . $i . '.html">' . str_pad($i, 2, '0', STR_PAD_LEFT) . '</a></span>';
        }
    }

    return '<div class="shcpaging">PEKAN KE ' . $left . $return . $right . '</div>';
}

/**
 * get league related news
 */
function get_related_news_league($sqlDB, $selected_cat)
{
    $where_category = " A.category='$selected_cat' ";
    if (is_array($selected_cat)) {
        $where_category = " A.category IN (" . implode(",", $selected_cat) . ") ";
    }
    $return = array();
    $sql = "
        SELECT A.schedule, A.title,A.url,B.category_url
        FROM
            dbnews A
        LEFT JOIN
            dbcategory B
        ON
            B.category_id=A.category
        WHERE
            $where_category
            AND A.schedule <NOW()
            AND A.schedule <> '00-00-0000 00:00:00'
            AND A.level>0
        ORDER BY A.schedule DESC
        LIMIT 0,10
    ";

    $res = $sqlDB->sql_query($sql);
    $return = $sqlDB->sql_fetchrowset($res);
    $sqlDB->sql_freeresult();
    return $return;
}

/**
 * special design schedule for ISL
 * also can used for all league
 */
function generate_schedule_index_isl($sqlDB)
{
    global $schedule_url, $schedule_dir, $month_list_ina, $tv_url, $tv_jadwal_url;

    $CURRENT_SEASON = '51';
    $league = 'indonesia';
    $score_dir = $schedule_dir . 'hasil_pertandingan/';
    $score_url = $schedule_url . 'hasil_pertandingan/';

    $arr_remove = array(
        ' Jakut',
        ' Surabaya',
        ' Wamena',
        ' Bandung',
        ' Balikpapan',
        ' Jakarta',
        ' Kediri',
        ' Jayapura',
        ' Makassar',
        ' Malang',
        ' Pekanbaru',
        ' Indonesia',
        ' Samarinda',
        ' Lamongan',
        ' Jepara'
    );

    $menu_top = '
        <div class="topmenu">
            <a href="' . $schedule_url . 'score.html">Skor Terkini</a>
            &nbsp;&nbsp;&nbsp;
            <a href="' . $schedule_url . '">Jadwal Pertandingan</a>
            &nbsp;&nbsp;&nbsp;
            <a href="' . $tv_jadwal_url . '">Jadwal TV</a>
            &nbsp;&nbsp;
            <a href="' . $schedule_url . 'klasemen_liga_inggris.html">Klasemen</a>
            &nbsp;&nbsp;
            <a href="' . $schedule_url . 'preview.html">Preview</a>
            &nbsp;&nbsp;
            <a href="' . $schedule_url . 'review.html">Review</a>
        </div>
    ';
    /*
      if ($league == 'all')
      {
      $where_league = '';
      $fname = 'index';
      }
      else
      {
      $where_league = ' AND country=\''.$league.'\'';
      switch(strtolower($league))
      {
      case 'inggris' : $fname = 'index_liga_premier'; $selected_cat = 1; break;
      case 'italia' : $fname = 'index_seri_a'; $selected_cat = 2; break;
      case 'spanyol' : $fname = 'index_la_liga'; $selected_cat = 3; break;
      case 'indonesia' : $fname = 'index_indonesia'; $selected_cat = 4; break;
      }
      }
     *
     */
    $where_league = " AND country='indonesia' AND idseason = '$CURRENT_SEASON' ";
    $fname = 'index_indonesia';
    $selected_cat = 4;

    /*$share = '
        <!--SOCIALTAB-->
        <div id="bl-social-tabs"></div>
        <script type="text/javascript">
            $(document).ready(function() {
                $("#bl-social-tabs").klsocial({
                    url:"'.BOLAURL.'jadwal_skor/' . $fname . '.html",
                    comment_count:"-1"
                });
              });
        </script>
        <!--ENDSOCIALTAB-->
    ';*/

    $qcount = "SELECT COUNT(*) AS t FROM `dbschedule` WHERE level<>'0' $where_league";
    $rcount = $sqlDB->sql_query($qcount, true) or die(__LINE__ . ' = ' . mysql_error());
    $rowcount = $sqlDB->sql_fetchrow($rcount);
    $total_schedule = $rowcount['t'];
    $sqlDB->sql_freeresult($rcount);

    $othernews = get_related_news_league($sqlDB, $selected_cat);
    $related_news = '';
    if (count($othernews) > 0) {
        $related_news = '
          <br/><br/><div class="ntbottom">Berita Liga ' . ucwords($league) . ' Terbaru</div>
        ';
        foreach ($othernews as $v) {
            $v['schedule'] = preg_replace('/\:\d\d$/', '', $v['schedule']);
            $v['schedule'] = preg_replace('/(\d{4,4})\-(\d{2,2})\-(\d{2,2}) (\d{2,2})\:(\d{2,2})/', '\3-\2-\1 \4:\5', $v['schedule']);
            $related_news .= '
                <div class="bcontent1">
                    <span>(' . $v['schedule'] . ')</span>
                    <a href="' . BASEURL . $v['category_url'] . '/' . $v['url'] . '.html" style="font-weight:normal;">' . $v['title'] . '</a>
                </div>
            ';
        }
        $related_news .= '<div class="clear"></div>';
    }

    $page = 1;
    $counter = 0;
    $limit_view = 20;
    $total_page = ceil($total_schedule / $limit_view);
    $tmp_date = '';
    $show_first = 0;
    $show_now = time();

    $q3 = "SELECT DATE(schedule) AS d FROM `dbschedule` WHERE level<>'0' $where_league GROUP BY DATE(schedule) ORDER BY schedule";
    $r3 = $sqlDB->sql_query($q3, true) or die(__LINE__ . ' = ' . mysql_error());
    while ($row3 = $sqlDB->sql_fetchrow($r3)) {
        $q2 = "SELECT country, title, schedule FROM `dbschedule` WHERE level<>'0' AND DATE(schedule)='$row3[d]' $where_league GROUP BY country, title";
        $r2 = $sqlDB->sql_query($q2, true) or die(__LINE__ . ' = ' . mysql_error());

        list($tahun, $bulanint, $hari) = array_map('intval', explode('-', $row3['d']));
        $bulan = $month_list_ina[$bulanint - 1];
        $date_title = $hari . ' ' . $bulan . ' ' . $tahun;

        while ($row2 = $sqlDB->sql_fetchrow($r2)) {
            $q = "SELECT * FROM dbschedule WHERE level<>'0' AND country='$row2[country]' AND title='$row2[title]' AND DATE(schedule)='$row3[d]' ORDER BY schedule DESC";
            $r = $sqlDB->sql_query($q, true) or die(__LINE__ . ' = ' . mysql_error());
            $num = $sqlDB->sql_numrows($r);
            if ($num == 0) {
                continue;
            }

            while ($row = $sqlDB->sql_fetchrow($r)) {
                if ($counter % $limit_view == 0) {
                    $content = '
                        <div class="bigcon">
                            <div class="bigcon2">
                                <div class="nav">
                                    <a href="/" style="text-decoration:none;">HOME</a> &raquo; JADWAL PERTANDINGAN
                                </div>
                                ' . $menu_top . '
                                <br/>
                                <div class="greentitle">JADWAL PERTANDINGAN</div>
                                <div class="jdskor1">Pilih Kompetisi: '.dropdown_klasemen_options($league).'</div>
                    ';
                }
                $counter++;

                if ($tmp_date != $date_title) {
                    if ($tmp_date) {
                        $content .= '
                                </div>
                            <div class="clear"></div>
                            <br/>
                        ';
                    }

                    $content .= '
                        <div class="jdboxx">
                            <div class="jdbox" style="color:#6B816A;">
                                <strong>' . $date_title . '</strong>
                            </div>
                    ';
                    $tmp_date = $date_title;
                }

                if ($row['level'] == '2') {
                    $fnameDetail = getURL($row['title'] . '-' . $row['home'] . $row['home2'] . '-vs-' . $row['away'] . $row['away2'] . '-' . date('Y-m-d', strtotime($row['schedule'])));
                    $filenameDetail = $score_url . $fnameDetail . '.html';
                    $detail = '<a href="' . $filenameDetail . '" class="bluelink">detail</a>';
                } else {
                    $detail = '---';
                }

                $schedule_time = strtotime($row['schedule']);
                list($tanggal, $jam) = explode(' ', $row['schedule']);
                $jam = preg_replace('/\:00$/', '', $jam);
                $show_time = $row['level'] == '2' ? 'FT' : $jam;
                if (!$show_first) {
                    $show_first = $schedule_time;
                }

                $wilayah = trim(str_replace('Liga Indonesia Wilayah', '', $row['title']));

                $qstadium = "SELECT team_stadion FROM dbteam WHERE TRIM(LOWER(team_name))='" . strtolower(trim($row['home'])) . "' LIMIT 1";
                $rstadium = $sqlDB->sql_query($qstadium, true) or die(__LINE__ . ' = ' . mysql_error());
                $rowstadium = $sqlDB->sql_fetchrow($rstadium);
                $lapangan = $rowstadium['team_stadion'] ? $rowstadium['team_stadion'] : '-';
                $sqlDB->sql_freeresult($rstadium);

                $home_content = str_replace($arr_remove, '', $row['home'] . $row['home2']);
                $away_content = str_replace($arr_remove, '', $row['away'] . $row['away2']);

                $content .= '
                    <div class="jdbox2 isl">
                        <div class="jdbox2a">
                            ' . $show_time . ' 
                        </div>
                        <div class="jdboxwil">
                            ' . $wilayah . ' 
                        </div>
                        <div class="jdislbox">
                            ' . $lapangan . '
                        </div>
                        <div class="jdislbox2">
                            ' . $home_content . '
                        </div>
                        <div class="jdislbox2">
                            ' . $away_content . '
                        </div>
                        <div class="jdislbox3">
                            ' . $detail . '
                        </div>
                    </div>
                    <div class="clear"></div>
                ';
                $standing_data[] = array("schedule"=>$date_title,"show_time"=>$show_time,"wilayah"=>$wilayah,"lapangan"=>$lapangan,"home_content"=>$home_content,"away_content"=>$away_content);
                if ($counter % $limit_view == 0 || $counter == $total_schedule) {
                    if ($tmp_date == $date_title) {
                        $content .= '
                                </div>
                            <div class="clear"></div>
                            <br/>
                        ';
                    }

                    $tmp_date = '';
                    $paging = schedule_paging_10($page, $total_page, $fname);

                    $content .= '
                                <br/>
                                ' . $paging . '
                                ' . $related_news . '
                            </div>
                        </div>
                    ';

                    $filename = $schedule_dir . $fname . $page . '.html';
                    $fileurl = $schedule_url . $fname . $page . '.html';

                    write_file($filename, $content, 'Jadwal Liga Indonesia Terkini (Liga Super Indonesia)', 'Jadwal Liga Indonesia Terkini (Liga Super Indonesia)', 'Jadwal Liga Indonesia (Liga Super Indonesia) Terkini');
                    echo generated_link($fileurl);

                    //starting schedule page
                    if ($page == 1) {
                        $filename = $schedule_dir . $fname . '.html';
                        $fileurl = $schedule_url . $fname . '.html';
                        write_file($filename, $content, 'Jadwal Liga Indonesia Terkini (Liga Super Indonesia)', 'Jadwal Liga Indonesia Terkini (Liga Super Indonesia)', 'Jadwal Liga Indonesia (Liga Super Indonesia) Terkini');
                        echo generated_link($fileurl);
                    }

                    //index page is current schedule
                    if ($show_now >= $show_first && $show_now <= $schedule_time) {
                        $filename = $schedule_dir . $fname . '.html';
                        $fileurl = $schedule_url . $fname . '.html';
                        write_file($filename, $content, 'Jadwal Liga Indonesia Terkini (Liga Super Indonesia)', 'Jadwal Liga Indonesia Terkini (Liga Super Indonesia)', 'Jadwal Liga Indonesia (Liga Super Indonesia) Terkini');
                        echo generated_link($fileurl);
                    }

                    $show_first = 0;
                    $page = intval($page) + 1;
                    
                    $memcache_obj = new Memcache;
                    bola_memcached_connect($memcache_obj);
                    $memcache_obj->set("schedule_liga_isl", serialize($standing_data), false, 24 * 3600);
                    $memcache_obj->close();
                }
            }
            $sqlDB->sql_freeresult($r);
        }
        $sqlDB->sql_freeresult($r2);
    }

    $sqlDB->sql_freeresult($r3);
}

/**
 * schedule pagination
 */
function schedule_paging_10($page, $total, $url)
{
    $return = '';
    $start = $page - 4;
    if ($start < 1) {
        $start = 1;
    }
    $end = $start + 9;
    if ($end > $total) {
        $start = $start - ($end - $total);
        $end = $total;
        if ($start < 1) {
            $start = 1;
        }
    }

    for ($i = $start; $i <= $end; $i++) {
        if ($i == $page) {
            $return .= '<span class="paging_nolink">' . str_pad($i, 2, '0', STR_PAD_LEFT) . '</span>';
        } else {
            $return .= '<span class="paging"><a href="' . $url . $i . '.html">' . str_pad($i, 2, '0', STR_PAD_LEFT) . '</a></span>';
        }
    }

    return $return;
}

/**
 * generate schedule dan hasil pertandingan for club
 *
 * @map url: www.bola.net/club/hasil-pertandingan/$team.html
 */
function generate_schedule_list($sqlDB)
{
    global $library_dir, $library_url, $club_dir, $club_url, $schedule_url, $team_media, $team_media_url;

    $CURRENT_SEASON = '35'; //28

    $history_dir = $club_dir . 'hasil-pertandingan/';
    $history_url = $club_url . 'hasil-pertandingan/';
    $score_url = $schedule_url . 'hasil_pertandingan/';

    if (!is_dir($history_dir)) {
        mkdir($history_dir);
    }

    $array = array(1 => 'inggris', 2 => 'italia', 3 => 'spanyol', 4 => 'indonesia');
    $inggris = '';
    $show_now = time();
    foreach ($array as $k => $v) {
        $qs = "
            SELECT season_id FROM dbseason
            WHERE season_cat_id='$k' AND season_status='1' ORDER BY season_id DESC LIMIT 1";
        $rs = $sqlDB->sql_query($qs, true);
        $rows = $sqlDB->sql_fetchrow($rs);

        $q = "
            SELECT A.team_id, A.team_name, A.team_image
            FROM dbteam A, dbparticipant B
            WHERE A.team_status='1' AND B.part_status='1' AND part_season_id='$rows[season_id]' AND team_id=part_team_id
            ORDER BY team_name";
        $r = $sqlDB->sql_query($q, true) or die(__LINE__ . ' = ' . mysql_error());
        $num = $sqlDB->sql_numrows($r);
        if (!$r) {
            return false;
        }
        $ccount = 1;
        while ($row = $sqlDB->sql_fetchrow($r)) {
            $q2 = "SELECT url FROM dbarticles WHERE team_id='$row[team_id]' AND category='16' LIMIT 1";
            $r2 = $sqlDB->sql_query($q2, true) or die(__LINE__ . ' = ' . mysql_error());
            $row2 = $sqlDB->sql_fetchrow($r2);

            $q3 = "SELECT DATE(schedule) AS dd, dbschedule.* FROM `dbschedule` WHERE idseason>='$CURRENT_SEASON' AND (home='$row[team_name]' OR home2='$row[team_name]' OR away='$row[team_name]' OR away2='$row[team_name]') AND level='2' AND schedule<=NOW() ORDER BY schedule DESC";
            $r3 = $sqlDB->sql_query($q3, true) or die(__LINE__ . ' = ' . mysql_error());
            $list = '';
            while ($row3 = $sqlDB->sql_fetchrow($r3)) {
                $fname = getURL($row3['title'] . '-' . $row3['home'] . $row3['home2'] . '-vs-' . $row3['away'] . $row3['away2'] . '-' . $row3['dd']);
                $fileurl = $score_url . $fname . '.html';

                $detail = '<a href="' . $fileurl . '" class="bluelink">detail</a>';
                $schedule_time = strtotime($row3['schedule']);
                list($tanggal, $jam) = explode(' ', $row3['schedule']);
                $jam = preg_replace('/\:00$/', '', $jam);
                $show_time = ($schedule_time < $show_now) && ($row3['level'] == '2') ? 'FT' : $jam;
                $list .= '
                    <div class="jdbox2" style="padding:3px 0px;">
                        <div class="jdbox2a">
                            ' . $tanggal . '
                        </div>
                        <div class="jdbox2b">
                            ' . $row3['home'] . $row3['home2'] . '
                        </div>
                        <div class="jdbox2c">
                            ' . $row3['goal_home'] . '
                        </div>
                        <div class="jdbox2d">
                            -
                        </div>
                        <div class="jdbox2e">
                            ' . $row3['goal_away'] . '
                        </div>
                        <div class="jdbox2f">
                            ' . $row3['away'] . $row3['away2'] . '
                        </div>
                        <div class="jdbox2g">
                            ' . $detail . '
                        </div>
                        <div class="clear"></div>
                </div>
                ';
            }
            $sqlDB->sql_freeresult($r3);

            $image_club = $team_media_url . $v . '/' . $row['team_image'];
            $share = get_linkshare($history_url . getURL($row['team_name']) . '.html', 'Hasil Pertandingan  ' . ucfirst($row['team_name']) . ' Musim Ini', false);

            $content = '
                <div class="bigcon">
                    <div class="bigcon2">
                        <div class="nav">
                            <a href="/" style="text-decoration:none;">HOME</a> &raquo;
                            HASIL PERTANDINGAN ' . strtoupper($row['team_name']) . '
                        </div>
                        <center>
                        <h1>
                            Hasil Pertandingan
                            <a href="' . $club_url . $row2['url'] . '.html" class="greenlink">' . $row['team_name'] . '</a>
                            Musim Ini
                        </h1>
                        </center>
                        ' . $list . '
                        <div class="clear"></div>
                    </div>
                </div>
                <script type="text/javascript">
                    $(document).ready(function() {
                        $(\'.share\').slideDown(400);
                    });
                </script>
            ';

            $filename = $history_dir . getURL($row['team_name']) . '.html';
            $filename_url = str_replace($history_dir, $history_url, $filename);
            write_file($filename, $content, 'Hasil Pertandingan  ' . ucfirst($row['team_name']) . ' Musim Ini', 'Hasil Pertandingan  ' . ucfirst($row['team_name']) . ' Musim Ini', 'Hasil Pertandingan  ' . ucfirst($row['team_name']) . ' Musim Ini', '', true, 'full', 5);
            echo generated_link($filename_url);
        }
        $sqlDB->sql_freeresult();
    }
}

/**
 * generate schedule topik
 */
function generate_schedule_topik($sqlDB, $date = '')
{
    global $schedule_url, $schedule_dir, $month_list_ina, $day_list_ina, $bigmatch_dir, $bigmatch_url, $library_dir, $library_url, $cdn_url;

    $stopik_dir = $bigmatch_dir . 'pertandingan/';
    $stopik_url = $bigmatch_url . 'pertandingan/';
    if (!is_dir($stopik_dir)) {
        mkdir($stopik_dir);
    }
    $i=1;
    $q = "SELECT DATE(schedule) AS dd, A.* FROM dbschedule A WHERE A.schedule<DATE_ADD(NOW(), INTERVAL 1 WEEK) ORDER BY A.schedule DESC LIMIT 0,100";
    if ($date) {
        $q = "SELECT DATE(schedule) AS dd, A.* FROM dbschedule A WHERE DATE(A.schedule)='$date' ORDER BY A.schedule DESC";
    }
    
    $r = $sqlDB->sql_query($q, true) or die(__LINE__ . ' = ' . mysql_error());
    while ($row = $sqlDB->sql_fetchrow($r)) {
        $home_club = $row['home'] ? $row['home'] : $row['home2'];
        $away_club = $row['away'] ? $row['away'] : $row['away2'];

        $q4 = "SELECT B.url FROM `dbteam` A LEFT JOIN dbarticles B ON A.team_id=B.team_id AND B.category='16' WHERE A.team_name='$home_club' LIMIT 1";
        $r4 = $sqlDB->sql_query($q4, true) or die('something happen');
        if ($row4 = $sqlDB->sql_fetchrow($r4)) {
            $home_link = '<a href="/club/' . $row4['url'] . '.html">' . $home_club . '</a>';
        } else {
            $home_link = $home_club;
        }
        $sqlDB->sql_freeresult($r4);

        $q5 = "SELECT B.url FROM `dbteam` A LEFT JOIN dbarticles B ON A.team_id=B.team_id AND B.category='16' WHERE A.team_name='$away_club' LIMIT 1";
        $r5 = $sqlDB->sql_query($q5, true) or die('something happen');
        if ($row5 = $sqlDB->sql_fetchrow($r5)) {
            $away_link = '<a href="/club/' . $row5['url'] . '.html">' . $away_club . '</a>';
        } else {
            $away_link = $away_club;
        }
        $sqlDB->sql_freeresult($r5);

        $list2 = '';
        $q3 = "SELECT dbnews.*,category_name,category_url FROM dbnews,dbcategory WHERE (`club1` LIKE  '" . $home_club . "' OR `club2` LIKE  '" . $home_club . "' OR `club3` LIKE  '" . $home_club . "' OR `club1` LIKE  '" . $away_club . "' OR `club2` LIKE  '" . $away_club . "' OR `club3` LIKE  '" . $away_club . "') AND level='1' AND schedule<=NOW() AND dbnews.category=dbcategory.category_id ORDER BY schedule DESC LIMIT 50";
        $r3 = $sqlDB->sql_query($q3, true) or die('something happen');
        $num3 = $sqlDB->sql_numrows($r3);
        $total3 = ceil($num3 / 10);
        $page = 0;
        $counter3 = 1;
        while ($row3 = $sqlDB->sql_fetchrow($r3)) {
            $datetime = preg_replace('/(\d{4,4})\-(\d{2,2})\-(\d{2,2}) (\d{2,2})\:(\d{2,2})\:(\d{2,2})/', '\3-\2-\1 \4:\5', $row3['schedule']);
            $hlimage = $library_dir . 'p/thumbnail/' . str_pad($row3['idnews'], 10, '0', STR_PAD_LEFT) . '.jpg';
            if (is_file($hlimage)) {
                $img = $cdn_url . 'library/p/thumbnail/' . str_pad($row3['idnews'], 10, '0', STR_PAD_LEFT) . '.jpg';
                $img = '<div class="bcontent2_image"><a href="' . BASEURL . $row3['category_url'] . '/' . $row3['url'] . '.html"><img src="' . $img . '" width="130"/></a><br class="clear"/><div>&nbsp;</div></div>';
            } else {
                $img = '';
            }

            $title_cencored = strlen($row3['title']) > 55 ? substr($row3['title'], 0, 53) . '..' : $row3['title'];
            $synopsis_cencored = strlen($row3['synopsis']) > 170 ? substr($row3['synopsis'], 0, 168) . '..' : $row3['synopsis'];

            $hari = $day_list_ina[date('w', strtotime($row3['schedule']))];

            $list2 .= '
                    <div class="bcontent2">
                      <div class="indschedule">' . $hari . ', ' . $datetime . '</div>
                      <span><a href="' . BASEURL . $row3['category_url'] . '/' . $row3['url'] . '.html">' . $title_cencored . '</a></span>
                      <br class="clear"/>
                      ' . $img . '
                      <div>' . $row3['synopsis'] . '</div>
                      <br class="clear" />
                    </div>
            ';

            if ($counter3 % 10 == 0 || $num3 == $counter3) {
                $fname = getURL($row['title'] . '-' . $row['home'] . $row['home2'] . '-vs-' . $row['away'] . $row['away2'] . '-' . $row['dd']);
                $paging = global_paging_10($page, $total3, $fname);
                $content = '
                  <div class="bigcon">
                    <div class="bigcon2">
                    <div class="nav"><a href="/" style="text-decoration:none;">HOME</a> &raquo; LIPUTAN ' . strtoupper($row['home'] . $row['home2']) . ' VS ' . strtoupper($row['away'] . $row['away2']) . '</div>
                        <div class="sch-container">
                            <center><h1>' . $home_link . ' VS ' . $away_link . '</h1></center>
                            ' . $list2 . '
                            <div class="clear"></div>
                            <center>' . $paging . '</center>
                        </div>
                    </div>
                  </div>
                ';

                $filename = $stopik_dir . $fname . ($page == 0 ? '' : $page) . '.html';
                $fileurl = $stopik_url . $fname . ($page == 0 ? '' : $page) . '.html';
                write_file($filename, $content, 'Liputan pertandingan ' . $row['home'] . $row['home2'] . ' vs ' . $row['away'] . $row['away2'] . ($page == 0 ? '' : ', halaman ' . $page), 'hasil, pertandingan, liputan, sepak bola, bola, ' . $row['home'] . $row['home2'] . ', vs, ' . $row['away'] . $row['away2'], 'Liputan pertandingan ' . $row['home'] . $row['home2'] . ' vs ' . $row['away'] . $row['away2'] . ($page == 0 ? '' : ', halaman ' . $page), '', true, 'full', 5);
                echo "<a href=\"$fileurl\" target=\"_blank\">" . $fileurl . "</a><br/>\n";

                $list2 = '';
                $page++;
                $i++;
            }
            $counter3++;
        }
        $sqlDB->sql_freeresult($r3);
    }
    $sqlDB->sql_freeresult();
    echo "TOTAL: $i";
}
/**
 * Generate klasemen, liga champions agregat ( 16 besar)
 * And view 10 top score
 */
function generate_schedule_klasemen_champions($sqlDB)
{
    global $schedule_url, $schedule_dir, $tv_url, $tv_jadwal_url;

    //generate topscore first
    generate_schedule_topscore_champions($sqlDB);

    $CURRENT_SEASON_YEAR = 2016;
    $k = 5;
    $v = 'champions';
    $league_title = 'Liga Champions';

    $menu_top = '
        <div class="topmenu">
            <a href="' . $schedule_url . 'score.html">Skor Terkini</a>
            &nbsp;&nbsp;&nbsp;
            <a href="' . $schedule_url . '">Jadwal Pertandingan</a>
            &nbsp;&nbsp;&nbsp;
            <a href="' . $tv_jadwal_url . '">Jadwal TV</a>
            &nbsp;&nbsp;
            <a href="' . $schedule_url . 'klasemen_liga_inggris.html">Klasemen</a>
            &nbsp;&nbsp;
            <a href="' . $schedule_url . 'preview.html">Preview</a>
            &nbsp;&nbsp;
            <a href="' . $schedule_url . 'review.html">Review</a>
        </div>
    ';

    $recent_news = '
        <div class="greentitle" style="margin-bottom:10px; margin-top:10px; text-align:left; font-size:18px">
            BERITA TERBARU ' . (strtoupper("Liga Champions")) . '
        </div>
        <ul style="list-style:none; margin:0px; padding:0px">
    ';

    $q = $sqlDB->sql_query("
                    SELECT a.title, a.url, a.schedule, b.category_url FROM dbnews a, dbcategory b
                    WHERE a.category = b.category_id AND a.category = '5' AND a.level != '0' AND a.schedule <= NOW()
                    ORDER BY a.schedule DESC LIMIT 10");
    while ($row = $sqlDB->sql_fetchrow($q)) {
        $recent_news .= '
            <li style="padding:5px 10px; border-bottom:1px dashed #aaa">
                <span style="color:#666">(' . date("d-m-Y H:i", strtotime($row["schedule"])) . ')</span>
                <a href="/' . $row["category_url"] . '/' . $row["url"] . '.html" class="greenlink">' . $row["title"] . '</a>
            </li>
        ';
    }

    $recent_news .= '
        </ul>
    ';

    /*$share = '
        <!--SOCIALTAB-->
            <div id="bl-social-tabs"></div>
            <script type="text/javascript">
                $(document).ready(function() {
                    $("#bl-social-tabs").klsocial({
                        url:"' . $schedule_url . 'klasemen_liga_' . $v . '.html",
                        comment_count:"-1"
                    });
                });
            </script>
        <!--ENDSOCIALTAB-->
    ';*/

    $content = '
        <div class="bigcon">
            <div class="bigcon2">
                <div class="nav">
                    <a href="/" style="text-decoration:none;">HOME</a> &raquo;
                    KLASEMEN ' . (strtoupper($league_title)) . '
                </div>
                ' . $menu_top . '
                <h1 class="greentitle" style="margin: 0px;">Klasemen ' . (ucwords($league_title)) . '</h1>
                <div class="jdskor1">Pilih Kompetisi: '.dropdown_klasemen_options($v).'</div>
                <!--SKLAS-->
    ';
    
    $qs = "SELECT season_id FROM dbseason WHERE season_cat_id='$k' AND season_status='1' ORDER BY season_id DESC LIMIT 1";
    $rs = $sqlDB->sql_query($qs, true);
    $rows = $sqlDB->sql_fetchrow($rs);
    $season_id = $rows['season_id'];
    
    $unique_random_id = 0;
    $counter = 1;
    $standing_data = array();
    $today = date("Y-m-d H:i:s");
    
    // Final
    $content .= '<div class="knockout-box">
                <div class="greentitle" style="text-align: left;">Babak Final</div>
                <div class="knockout-list">';
    $q = "
        SELECT home, home2, away, away2,schedule,kickoff,goal_home,goal_away FROM dbschedule
        WHERE country='UEFA Final' AND title='Liga Champions' AND idseason='$season_id' AND (home='Juventus' OR away='Real Madrid') AND level<>'0'";
    
    $r = $sqlDB->sql_query($q, true);
    
    while ($row = $sqlDB->sql_fetchrowset($r)) {
        foreach ($row as $row) {
            $standing_data[] = array(
                'kategori'=>'final',
                'data' => $row,
                'home' => array($row[0]));
            $content .= '<table class="table-knockout">
               <tbody>';
            $day = get_day_ind($row['schedule']);
                
            $date = preg_replace('/\:\d\d$/', '', $row['schedule']);
            $date = preg_replace('/(\d{4,4})\-(\d{2,2})\-(\d{2,2}) (\d{2,2})\:(\d{2,2})/', '\3-\2-\1', $date);
            $date = str_replace('2015', '15', $date);
            $date = str_replace('-', '/', $date);
            
            $time = preg_replace('/\:\d\d$/', '', $row['schedule']);
            $time = preg_replace('/(\d{4,4})\-(\d{2,2})\-(\d{2,2}) (\d{2,2})\:(\d{2,2})/', '\4:\5', $time);
            $goal = '';

            if ($row['schedule'] <= $today) {
                $goal = $row['goal_home']."-".$row['goal_away'];
            } else {
                $goal = '';
            }
            $data = (($goal != '') ? $goal : $time);

            $content .= '
                            <tr style="background: #DADADA;">
                                <td class="day text-left">'.$day.'</td>
                                <td class="date">'.$date.'</td>
                                <td class="klub-box text-right"><span class="klub">'.$row['home'].'</span></td>
                                <td class="time text-center" style="background: #BEBEBE;">'.$data.'</td>
                                <td class="klub-box text-left"><span class="klub">'.$row['away'].'</span></td>
                            </tr>
                        ';
        }
        $content .= '</tbody></table>';
    }
    $content .= '</div></div>';
    
    // semifinal
    $tim_perempatfinal = array('Atletico Madrid', 'Juventus');
    $content .= '<div class="knockout-box">
                <div class="greentitle" style="text-align: left;">Babak Semifinal</div>
                <div class="knockout-list">';
    
    foreach ($tim_perempatfinal as $key=>$val) {
        $q = "
            SELECT home, home2, away, away2,schedule,kickoff,goal_home,goal_away FROM dbschedule
            WHERE country='UEFA Semifinal' AND title='Liga Champions' AND idseason='$season_id' AND (home='$val' OR away='$val') AND level<>'0'";
        
        $r = $sqlDB->sql_query($q, true);
        
        while ($row = $sqlDB->sql_fetchrowset($r)) {
            $skor_club_1 = $row[0]['goal_home']+$row[1]['goal_away'];
            $skor_club_2 = $row[0]['goal_away']+$row[1]['goal_home'];
            $skor_agr =  $skor_club_1."-".$skor_club_2;
            $standing_data[] = array(
                'kategori'=>'semifinal',
                'data' => $row,
                'home' => array($row[0]['home']),
                'skor_agr'=>array($skor_agr));
            foreach ($row as $row) {
                if ($row['schedule'] <= $today) {
                    $skor_agr = $skor_agr;
                } else {
                    $skor_agr = '';
                }
                if (($row['away']==$val)) {
                    $content .= '<table class="table-knockout">
                        <thead>
                            <tr>
                                <td colspan="2"><strong>Agr</strong></td>
                                <td class="klub-box text-right">'.$row['home'].'</td>
                                <td class="skor text-center">'.$skor_agr.'</td>
                                <td class="klub-box text-left">'.$row['away'].'</td>
                            </tr>
                        </thead><tbody>';
                }
                $day = get_day_ind($row['schedule']);
                    
                $date = preg_replace('/\:\d\d$/', '', $row['schedule']);
                $date = preg_replace('/(\d{4,4})\-(\d{2,2})\-(\d{2,2}) (\d{2,2})\:(\d{2,2})/', '\3-\2-\1', $date);
                $date = str_replace('2015', '15', $date);
                $date = str_replace('-', '/', $date);
                
                $time = preg_replace('/\:\d\d$/', '', $row['schedule']);
                $time = preg_replace('/(\d{4,4})\-(\d{2,2})\-(\d{2,2}) (\d{2,2})\:(\d{2,2})/', '\4:\5', $time);
                $goal = '';

                if ($row['schedule'] <= $today) {
                    $goal = $row['goal_home']."-".$row['goal_away'];
                } else {
                    $goal = '';
                }
                $data = (($goal != '') ? $goal : $time);

                $content .= '
                                <tr>
                                    <td class="day text-left">'.$day.'</td>
                                    <td class="date">'.$date.'</td>
                                    <td class="klub-box text-right"><span class="klub">'.$row['home'].'</span></td>
                                    <td class="time text-center">'.$data.'</td>
                                    <td class="klub-box text-left"><span class="klub">'.$row['away'].'</span></td>
                                </tr>
                            ';
            }
            $counter++;
            $content .= '</tbody></table>';
        }
    }
    $content .= '</div></div>';
    
    // perempat final
    $tim_perempatfinal = array('AS Monaco','Barcelona','Leicester City','Real Madrid');
    $content .= '<div class="knockout-box">
                <div class="greentitle" style="text-align: left;">Babak Perempat Final</div>
                <div class="knockout-list">';
    
    foreach ($tim_perempatfinal as $key=>$val) {
        $q = "
            SELECT home, home2, away, away2,schedule,kickoff,goal_home,goal_away FROM dbschedule
            WHERE country='UEFA Perempat Final' AND title='Liga Champions' AND idseason='$season_id' AND (home='$val' OR away='$val') AND level<>'0'";
        
        $r = $sqlDB->sql_query($q, true);
        
        while ($row = $sqlDB->sql_fetchrowset($r)) {
            $skor_club_1 = $row[0]['goal_home']+$row[1]['goal_away'];
            $skor_club_2 = $row[0]['goal_away']+$row[1]['goal_home'];
            $skor_agr =  $skor_club_1."-".$skor_club_2;
            $standing_data[] = array(
                'kategori'=>'perempat final',
                'data' => $row,
                'home' => array($row[0]['home']),
                'skor_agr'=>array($skor_agr));
            foreach ($row as $row) {
                if ($row['schedule'] <= $today) {
                    $skor_agr = $skor_agr;
                } else {
                    $skor_agr = '';
                }
                if (($row['away']==$val)) {
                    $content .= '<table class="table-knockout">
                        <thead>
                            <tr>
                                <td colspan="2"><strong>Agr</strong></td>
                                <td class="klub-box text-right">'.$row['home'].'</td>
                                <td class="skor text-center">'.$skor_agr.'</td>
                                <td class="klub-box text-left">'.$row['away'].'</td>
                            </tr>
                        </thead><tbody>';
                }
                $day = get_day_ind($row['schedule']);
                    
                $date = preg_replace('/\:\d\d$/', '', $row['schedule']);
                $date = preg_replace('/(\d{4,4})\-(\d{2,2})\-(\d{2,2}) (\d{2,2})\:(\d{2,2})/', '\3-\2-\1', $date);
                $date = str_replace('2017', '17', $date);
                $date = str_replace('-', '/', $date);
                
                $time = preg_replace('/\:\d\d$/', '', $row['schedule']);
                $time = preg_replace('/(\d{4,4})\-(\d{2,2})\-(\d{2,2}) (\d{2,2})\:(\d{2,2})/', '\4:\5', $time);
                $goal = '';

                if ($row['schedule'] <= $today) {
                    $goal = $row['goal_home']."-".$row['goal_away'];
                } else {
                    $goal = '';
                }
                $data = (($goal != '') ? $goal : $time);

                $content .= '
                                <tr>
                                    <td class="day text-left">'.$day.'</td>
                                    <td class="date">'.$date.'</td>
                                    <td class="klub-box text-right"><span class="klub">'.$row['home'].'</span></td>
                                    <td class="time text-center">'.$data.'</td>
                                    <td class="klub-box text-left"><span class="klub">'.$row['away'].'</span></td>
                                </tr>
                            ';
            }
            $counter++;
            $content .= '</tbody></table>';
        }
    }
    $content .= '</div></div>';

    // 16 besar
    $tim = array('Benfica','PSG','Real Madrid','Bayern Munchen','AS Monaco','Bayer Leverkusen','Porto','Sevilla');
    
    $content .= '<div class="knockout-box">
                <div class="greentitle" style="text-align: left;">Babak 16 Besar</div>
                <div class="knockout-list">';

    foreach ($tim as $key=>$val) {
        $q = "
            SELECT home, home2, away, away2,schedule,kickoff,goal_home,goal_away FROM dbschedule
            WHERE country='UEFA 16 Besar' AND title='Liga Champions' AND idseason='$season_id' AND (home='$val' OR away='$val') AND level<>'0'";
        $r = $sqlDB->sql_query($q, true);
        
        while ($row = $sqlDB->sql_fetchrowset($r)) {
            $skor_club_1 = $row[0]['goal_home']+$row[1]['goal_away'];
            $skor_club_2 = $row[0]['goal_away']+$row[1]['goal_home'];
            $skor_agr =  $skor_club_1."-".$skor_club_2;
            $standing_data[] = array(
                'kategori'=>"16 besar",
                'data' => $row,
                'home' => array($row[0]['home']),
                'skor_agr'=>array($skor_agr));
            foreach ($row as $row) {
                if ($row['schedule'] <= $today) {
                    $skor_agr = $skor_agr;
                } else {
                    $skor_agr = '';
                }
                if (($row['home']==$val)) {
                    $content .= '<table class="table-knockout">
                        <thead>
                            <tr>
                                <td colspan="2"><strong>Agr</strong></td>
                                <td class="klub-box text-right">'.$row['home'].'</td>
                                <td class="skor text-center">'.$skor_agr.'</td>
                                <td class="klub-box text-left">'.$row['away'].'</td>
                            </tr>
                        </thead><tbody>';
                }
                $day = get_day_ind($row['schedule']);
                    
                $date = preg_replace('/\:\d\d$/', '', $row['schedule']);
                $date = preg_replace('/(\d{4,4})\-(\d{2,2})\-(\d{2,2}) (\d{2,2})\:(\d{2,2})/', '\3-\2-\1', $date);
                $date = str_replace(array('2016', '2017'), array('16', '17'), $date);
                $date = str_replace('-', '/', $date);
                
                $time = preg_replace('/\:\d\d$/', '', $row['schedule']);
                $time = preg_replace('/(\d{4,4})\-(\d{2,2})\-(\d{2,2}) (\d{2,2})\:(\d{2,2})/', '\4:\5', $time);
                $goal = '';

                if ($row['schedule'] <= $today) {
                    $goal = $row['goal_home']."-".$row['goal_away'];
                } else {
                    $goal = '';
                }
                $data = (($goal != '') ? $goal : $time);

                $content .= '
                                <tr>
                                    <td class="day text-left">'.$day.'</td>
                                    <td class="date">'.$date.'</td>
                                    <td class="klub-box text-right"><span class="klub">'.$row['home'].'</span></td>
                                    <td class="time text-center">'.$data.'</td>
                                    <td class="klub-box text-left"><span class="klub">'.$row['away'].'</span></td>
                                </tr>
                            ';
            }
            $counter++;
            $content .= '</tbody></table>';
        }
    }
    $content .= '</div></div>';
    
    /*if(count($standing_data)==8){
        $memcache_obj = new Memcache;
        bola_memcached_connect($memcache_obj);
        $memcache_obj->set('_bola_standing_champions_16_besar', serialize($standing_data), false, 7 * 24 * 3600);
        write_file_direct(TEMPDIR.'klasemen/_bola_standing_data_'.$k, serialize($standing_data));
        $memcache_obj->close();
    }*/
    if ($standing_data) {
        $memcache_obj = new Memcache;
        bola_memcached_connect($memcache_obj);
        $memcache_obj->set('_bola_standing_champions_klasemen_', serialize($standing_data), false, 7 * 24 * 3600);
        write_file_direct(TEMPDIR.'klasemen/_bola_standing_data_'.$k, serialize($standing_data));
        $memcache_obj->close();
    }

    $topscore = get_ten_topscore($v);
    $content .= '
                <br/><br/>' . $topscore . '
                <br/>' . $recent_news . '
            </div>
        </div>
    ';

    $filename = $schedule_dir . 'klasemen_liga_' . $v . '.html';
    $fileurl = $schedule_url . 'klasemen_liga_' . $v . '.html';

    $metatitle = 'Update Klasemen Liga Champions ' . $CURRENT_SEASON_YEAR . ' Terbaru';
    $metadesc = 'Update terbaru Klasemen Liga Champions ' . $CURRENT_SEASON_YEAR . ' dari Live Score pertandingan Liga Champions terakhir disertai daftar Top Skor';
    $metakey = explode(' ', trim(strtolower($metadesc)));
    $metakey = array_unique(array_filter(array_map('trim', $metakey)));
    $metakey = array_slice($metakey, 0, 50);
    $metakey = implode(',', $metakey);

    write_file($filename, $content, $metatitle, $metakey, $metadesc, '', true, 'full', 5);
    insert_property_og($filename, $metatitle, $fileurl, '', '', $metadesc, 'Jadwal & Skor/Klasemen');
    echo generated_link($fileurl);
}
/**
 * Generate klasemen, liga champions ( grup a-h)
 * And view 10 top score
 */
function generate_schedule_klasemen_champions_group($sqlDB)
{
    global $schedule_url, $schedule_dir, $tv_url, $tv_jadwal_url;
    
    //generate topscore first
    generate_schedule_topscore_champions($sqlDB);

    $CURRENT_SEASON_YEAR = 2017;
    $k = 5;
    $v = 'champions';
    $league_title = 'Liga Champions';
    
    
    //for right klasemen http://www.bola.net/champions/index.html
    $qs = "SELECT season_id FROM dbseason WHERE season_cat_id='$k' AND season_status='1' ORDER BY season_id DESC LIMIT 1";
    $rs = $sqlDB->sql_query($qs, true);
    $rows = $sqlDB->sql_fetchrow($rs);
    $season_id = $rows['season_id'];
    //$season_id = 56;
    
    $array_champions = range('A', 'H');
    $standing_data_old = array();
    
    $unique_random_id = 0;
    
        /*$share = '
        <!--SOCIALTAB-->
            <div id="bl-social-tabs"></div>
            <script type="text/javascript">
                $(document).ready(function() {
                    $("#bl-social-tabs").klsocial({
                        url:"' . $schedule_url . 'klasemen_liga_' . $v . '.html",
                        comment_count:"-1"
                    });
                });
            </script>
        <!--ENDSOCIALTAB-->
    ';*/
    $menu_top = '
            <div class="topmenu">
                <a href="' . $schedule_url . 'score.html">Skor Terkini</a>
                &nbsp;&nbsp;&nbsp;
                <a href="' . $schedule_url . '">Jadwal Pertandingan</a>
                &nbsp;&nbsp;&nbsp;
                <a href="' . $tv_jadwal_url . '">Jadwal TV</a>
                &nbsp;&nbsp;
                <a href="' . $schedule_url . 'klasemen_liga_inggris.html">Klasemen</a>
                &nbsp;&nbsp;
                <a href="' . $schedule_url . 'preview.html">Preview</a>
                &nbsp;&nbsp;
                <a href="' . $schedule_url . 'review.html">Review</a>
            </div>
        ';

    $content = '
        <div class="bigcon">
            <div class="bigcon2">
                <div class="nav">
                    <a href="/" style="text-decoration:none;">HOME</a> &raquo;
                    KLASEMEN ' . (strtoupper($league_title)) . '
                </div>
                ' . $menu_top . '
                <h1 class="greentitle" style="margin: 0px;">Klasemen ' . (ucwords($league_title)) . '</h1>
                <div class="jdskor1">Pilih Kompetisi: '.dropdown_klasemen_options($v).'</div>
                <!--SKLAS-->
    ';
    foreach ($array_champions as $ac) {
        $q = "
            SELECT home, home2, away, away2 FROM dbschedule
            WHERE country='UEFA Penyisihan Grup $ac' AND title='Liga Champions' AND idseason='$season_id' AND level<>'0'";
        $r = $sqlDB->sql_query($q, true);
        if (!$r) {
            return false;
        }
        //echo $q .'<br/>';

        $theclub = array();
        while ($row = $sqlDB->sql_fetchrow($r)) {
            if (!in_array($row['home'], $theclub)) {
                array_push($theclub, $row['home']);
            }
            if (!in_array($row['home2'], $theclub)) {
                array_push($theclub, $row['home2']);
            }
            if (!in_array($row['away'], $theclub)) {
                array_push($theclub, $row['away']);
            }
            if (!in_array($row['away2'], $theclub)) {
                array_push($theclub, $row['away2']);
            }
        }
        $sqlDB->sql_freeresult($r);
        
        $content .= '<h1>Klasemen Grup ' . $ac . '</h1>
                <div class="klasemen_row">
                    <div class="jdsmall1"><strong>No</strong></div>
                    <div class="jdwide1" style="width:230px;"><strong>Klub</strong></div>
                    <div class="jdsmall1"><strong>Main</strong></div>
                    <div class="jdsmall1"><strong>M</strong></div>
                    <div class="jdsmall1"><strong>S</strong></div>
                    <div class="jdsmall1"><strong>K</strong></div>
                    <div class="jdsmallg1"><strong>SG</strong></div>
                    <div class="jdsmall1"><strong>Poin</strong></div>
                    <br class="clear" />
                </div>';
                
        $theclub = array_unique(array_filter(array_map('trim', $theclub)));

        $tmp_file = LOGDIR . 'klasemen_champions_tmp_file.txt';
        $tmp_file2 = LOGDIR . 'klasemen_champions_tmp_file2.txt';
        if (is_file($tmp_file)) {
            unlink($tmp_file);
        }
        if (is_file($tmp_file2)) {
            unlink($tmp_file2);
        }
        $save_arr = array();

        $unique_team_id = 1;
        $tmp_point = $is_headtohead = array();
        foreach ($theclub as $detailclub) {
            $q3 = "SELECT team_id, team_name FROM dbteam WHERE team_name='$detailclub' LIMIT 1";
            $r3 = $sqlDB->sql_query($q3);
            if ($row3 = $sqlDB->sql_fetchrow($r3)) {
            } else {
                $row3['team_id'] = '-' . $unique_team_id++;
            }

            $club_data = get_schedule_club_data($sqlDB, $detailclub, $season_id);
            $ab = $club_data['home_goal'] - $club_data['away_goal'];

            file_put_contents($tmp_file, $row3['team_id'] . "\t" . $club_data['point'] . "\t" . $ab . "\t" . $club_data['home_goal'] . "\t" . $detailclub . "\n", FILE_APPEND);
            $save_arr[$row3['team_id']] = $club_data;

            if (!array_key_exists($club_data['point'], $tmp_point)) {
                $tmp_point[$club_data['point']] = $detailclub;
            } else {
                if (!array_key_exists($club_data['point'], $is_headtohead)) {
                    $is_headtohead[$club_data['point']] = array($detailclub, $tmp_point[$club_data['point']]);
                }
            }
        }

        exec('cat ' . $tmp_file . ' | sort -k2.1nr -k3.1nr -k4.1nr -k5 > ' . $tmp_file2, $out);
        $sqlDB->sql_freeresult($r);
        $sqlDB->sql_freeresult($rs);

        $css = 2;
        $counter = 1;
        $arr_data = array_filter(array_map('trim', file($tmp_file2)));

        if (count($is_headtohead) > 0) {
            $is_headtohead = array_shift($is_headtohead);

            $club1 = $is_headtohead[0];
            $club2 = $is_headtohead[1];
            $headtoheadwinner = generate_schedule_headtohead($sqlDB, $season_id, $club1, $club2);

            $club1_pos = -1;
            $club2_pos = -1;
            $league_pos = 0;
            foreach ($arr_data as $vdata) {
                list($team_id, $team_point, $team_selisih, $away_goal, $team_name) = explode("\t", $vdata);
                if ($team_name == $club1) {
                    $club1_pos = $league_pos;
                }
                if ($team_name == $club2) {
                    $club2_pos = $league_pos;
                }
                $league_pos++;
            }
            reset($arr_data);

            if ($ac == 'H') {
                //echo "$club1_pos  $club2_pos === $club1 $club2 $headtoheadwinner";
            }

            if ($headtoheadwinner == 1 && $club1_pos > $club2_pos) {
                $switch_tmp = $arr_data[$club1_pos];
                $arr_data[$club1_pos] = $arr_data[$club2_pos];
                $arr_data[$club2_pos] = $switch_tmp;
            } elseif ($headtoheadwinner == 2 && $club1_pos < $club2_pos) {
                $switch_tmp = $arr_data[$club1_pos];
                $arr_data[$club1_pos] = $arr_data[$club2_pos];
                $arr_data[$club2_pos] = $switch_tmp;
            }
        }
        
        // off head to head hack musim 2016
        /*if (in_array(strtolower($ac), array('a', 'b'))) {
            $temp_data = array();
            foreach ($arr_data as $vdata) {
                list($team_id, $team_point, $team_selisih, $away_goal, $team_name) = explode("\t", $vdata);
                $temp_data[] = $save_arr[$team_id];
            }
            $arr_data = champions_standing_manual_h2h(strtolower($ac), $temp_data, $arr_data);
        }*/
        
        
        $standing_data_old[$ac] = array();
        foreach ($arr_data as $vdata) {
            list($team_id, $team_point, $team_selisih, $away_goal, $team_name) = explode("\t", $vdata);
            $xdata = $save_arr[$team_id];

            $_standing_club_url_ = '';
            $q2 = "SELECT url FROM dbarticles WHERE team_id='$team_id' AND category='16' LIMIT 1";
            $r2 = $sqlDB->sql_query($q2);
            if ($row2 = $sqlDB->sql_fetchrow($r2)) {
                $clubname = '<a href="/club/' . $row2['url'] . '.html" class="greenlink">' . trim($team_name) . '</a>';
                $_standing_club_url_ = $row2['url'];
            } else {
                $clubname = trim($team_name);
            }
            $content .= '
                <div class="klasemen_row">
                <div class="jdsmall' . $css . '">' . $counter . '</div>
                <div class="jdwide' . $css . '" style="width:230px;">' . $clubname . '</div>
                <div class="jdsmall' . $css . '">' . $xdata['played'] . '</div>
                <div class="jdsmall' . $css . '">' . $xdata['win'] . '</div>
                <div class="jdsmall' . $css . '">' . $xdata['draw'] . '</div>
                <div class="jdsmall' . $css . '">' . $xdata['loose'] . '</div>
                <div class="jdsmallg' . $css . '">' . $xdata['home_goal'] . ' - ' . $xdata['away_goal'] . '</div>
                <div class="jdsmall' . $css . '">' . $xdata['point'] . '</div>
                <br class="clear" />
                                </div>
            ';
            $standing_data_old[$ac][] = array(
                'no' => $counter,
                'club' => trim($team_name),
                'club_url' => $_standing_club_url_,
                'played' => $xdata['played'],
                'win' => $xdata['win'],
                'draw' => $xdata['draw'],
                'lost' => $xdata['loose'],
                'home_goal' => $xdata['home_goal'],
                'away_goal' => $xdata['away_goal'],
                'point' => $xdata['point'],
            );

            $zone_start = '';
            $zone_end = '';
            $deduction_point = '0';
            $team_id = $unique_random_id . '0900' . $team_id; // liga champions only, 010 -> 0900 edited at 20130920

            $klasement_query = "REPLACE INTO dbklasemen (`rank`, `last_rank`, `zone_start`, `zone_end`, `team_id`, `club_name`, `session_id`, `matches_total`, `matches_won`, `matches_draw`, `matches_lost`, `goals_pro`, `goals_against`, `points`, `deduction_points`, `league`, `last_update_time`) VALUES (
            '$counter','$counter','$zone_start','$zone_end','$team_id','$team_name','$season_id','" . $xdata['played'] . "','" . $xdata['win'] . "','" . $xdata['draw'] . "','" . $xdata['loose'] . "','" . $xdata['home_goal'] . "','" . $xdata['away_goal'] . "','" . $xdata['point'] . "','$deduction_point','champions " . strtolower($ac) . "',NOW()
            )";
            if ($team_id == '103') {
                //echo $klasement_query ."\n";
            }
            $sqlDB->sql_query($klasement_query);
            $unique_random_id++;

            $counter++;
            $css = $css == 1 ? 2 : 1;
        }
    }
    if ($standing_data_old) {
        $memcache_obj = new Memcache;
        bola_memcached_connect($memcache_obj);
        $memcache_obj->set('_bola_standing_champions_data_', serialize($standing_data_old), false, 7 * 24 * 3600);
        write_file_direct(TEMPDIR.'klasemen/_bola_standing_data_'.$k, serialize($standing_data_old));
        $memcache_obj->close();
    }
    // eof right index champion http://www.bola.net/champions/index.html
    
    $recent_news = '
        <div class="greentitle" style="margin-bottom:10px; margin-top:10px; text-align:left; font-size:18px">
            BERITA TERBARU ' . (strtoupper("Liga Champions")) . '
        </div>
        <ul style="list-style:none; margin:0px; padding:0px">
    ';

    $q = $sqlDB->sql_query("
                    SELECT a.title, a.url, a.schedule, b.category_url FROM dbnews a, dbcategory b
                    WHERE a.category = b.category_id AND a.category = '5' AND a.level != '0' AND a.schedule <= NOW()
                    ORDER BY a.schedule DESC LIMIT 10");
    while ($row = $sqlDB->sql_fetchrow($q)) {
        $recent_news .= '
            <li style="padding:5px 10px; border-bottom:1px dashed #aaa">
                <span style="color:#666">(' . date("d-m-Y H:i", strtotime($row["schedule"])) . ')</span>
                <a href="/' . $row["category_url"] . '/' . $row["url"] . '.html" class="greenlink">' . $row["title"] . '</a>
            </li>
        ';
    }

    $recent_news .= '
        </ul>
    ';

    $topscore = get_ten_topscore($v);
    $content .= '
                <br/><br/>' . $topscore . '
                <br/>' . $recent_news . '
            </div>
        </div>
    ';

    $filename = $schedule_dir . 'klasemen_liga_' . $v . '.html';
    $fileurl = $schedule_url . 'klasemen_liga_' . $v . '.html';
    
    $metatitle = 'Klasemen Liga Champions - Update Terbaru';
    $metadesc = 'Klasemen Liga Champions ' . $CURRENT_SEASON_YEAR . ', update terbaru Live Score pertandingan Liga Champions terakhir, disertai daftar Top Skor';
    //$metatitle = 'Update Klasemen Liga Champions ' . $CURRENT_SEASON_YEAR . ' Terbaru';
    //$metadesc = 'Update terbaru Klasemen Liga Champions ' . $CURRENT_SEASON_YEAR . ' dari Live Score pertandingan Liga Champions terakhir disertai daftar Top Skor';
    
    $metakey = explode(' ', trim(strtolower($metadesc)));
    $metakey = array_unique(array_filter(array_map('trim', $metakey)));
    $metakey = array_slice($metakey, 0, 50);
    $metakey = implode(',', $metakey);

    write_file($filename, $content, $metatitle, $metakey, $metadesc, '', true, 'full', 5);
    insert_property_og($filename, $metatitle, $fileurl, '', '', $metadesc, 'Jadwal & Skor/Klasemen');
    echo generated_link($fileurl);
}

function champions_standing_manual_h2h($group, $data, $return)
{
    $temp = $return;
    if ($group == 'a' && isset($data[0]['played']) && $data[0]['played'] == '3') {
        $return[0] = $temp[1];
        $return[1] = $temp[0];
    }
    if ($group == 'b' && isset($data[0]['played']) && $data[0]['played'] == '3') {
        $return[1] = $temp[2];
        $return[2] = $temp[1];
    }
    return $return;
}

function generate_schedule_topscore_champions($sqlDB)
{
    global $schedule_url, $schedule_dir, $tv_url, $tv_jadwal_url, $profile_dir, $profile_url;

    $k = 5;
    $v = 'champions';
    $league_title = 'Liga Champions';

    $menu_top = '
        <div class="topmenu">
            <a href="' . $schedule_url . 'score.html">Skor Terkini</a>
            &nbsp;&nbsp;&nbsp;
            <a href="' . $schedule_url . '">Jadwal Pertandingan</a>
            &nbsp;&nbsp;&nbsp;
            <a href="' . $tv_jadwal_url . '">Jadwal TV</a>
            &nbsp;&nbsp;
            <a href="' . $schedule_url . 'klasemen_liga_inggris.html">Klasemen</a>
            &nbsp;&nbsp;
            <a href="' . $schedule_url . 'preview.html">Preview</a>
            &nbsp;&nbsp;
            <a href="' . $schedule_url . 'review.html">Review</a>
        </div>
    ';
    $menu_top .= '
        <div class="topmenu">
            <a href="'.$schedule_url.'topskor_liga_inggris.html">Liga Inggris</a>
            &nbsp;&nbsp;&nbsp;
            <a href="'.$schedule_url.'topskor_liga_italia.html">Liga Italia</a>
            &nbsp;&nbsp;&nbsp;
            <a href="'.$schedule_url.'topskor_liga_spanyol.html">Liga Spanyol</a>
            &nbsp;&nbsp;
            <a href="'.$schedule_url.'topskor_liga_indonesia.html">ISL</a>
            &nbsp;&nbsp;
            <a href="'.$schedule_url.'topskor_liga_champions.html">Liga Champions</a>
        </div>
    ';
    $content = '
        <div class="bigcon">
            <div class="bigcon2">
                <div class="nav">
                    <a href="/" style="text-decoration:none;">HOME</a> &raquo;
                    TOP SKOR ' . (strtoupper($league_title)) . '
                </div>
                ' . $menu_top . '
                <br/>
                <div class="greentitle">Top Skor ' . (ucwords($league_title)) . '</div>
                <br/>
    ';

    //champion season ID
    $qs = "SELECT season_id FROM dbseason WHERE season_cat_id='$k' AND season_status='1' ORDER BY season_id DESC LIMIT 1";
    $rs = $sqlDB->sql_query($qs, true);
    $rows = $sqlDB->sql_fetchrow($rs);
    $season_id = $rows['season_id'];

    $tmp_file = BOLADIR . 'topscore_tmp_file.txt';
    $tmp_file2 = BOLADIR . 'topscore_tmp_file2.txt';
    if (is_file($tmp_file)) {
        unlink($tmp_file);
    }
    if (is_file($tmp_file2)) {
        unlink($tmp_file2);
    }

    $q = "(SELECT CONCAT(home, home2) as team_name FROM dbschedule
                WHERE title='Liga Champions' AND idseason='$season_id' AND level<>'0')
                UNION
                (SELECT CONCAT(away, away2) as team_name FROM dbschedule
                WHERE title='Liga Champions' AND idseason='$season_id' AND level<>'0')
                ";
    $r = $sqlDB->sql_query($q, true) or die(__LINE__ . ' = ' . mysql_error());
    while ($row = $sqlDB->sql_fetchrow($r)) {
        $qsc = "SELECT * FROM dbschedule WHERE (home='$row[team_name]' OR home2='$row[team_name]') AND idseason='$season_id' AND level='2' AND schedule<>'0000-00-00 00:00:00'";
        $rsc = $sqlDB->sql_query($qsc, true) or die(__LINE__ . ' = ' . mysql_error());

        while ($rowsc = $sqlDB->sql_fetchrow($rsc)) {
            $qd = "SELECT * FROM dbscore WHERE id_schedule='$rowsc[id]' AND info='goal' AND level='1'";
            $rd = $sqlDB->sql_query($qd, true) or die(__LINE__ . ' = ' . mysql_error());
            while ($rowd = $sqlDB->sql_fetchrow($rd)) {
                $is_my_team = '';

                /* get player club from player_profile (case midseason player transfer) [2013-08-27] */
                $rowd['player_name'] = kln_real_escape_string($rowd['player_name']);
                //$qplayerclub = "SELECT player_club FROM player_profile WHERE (player_name='{$rowd['player_name']}' OR player_fullname='{$rowd['player_name']}') AND player_status='1' LIMIT 1";
                $qplayerclub = "SELECT B.team_name as player_club FROM player_profile A JOIN dbteam B ON A.player_club = B.team_id WHERE (player_name='{$rowd['player_name']}' OR player_fullname='{$rowd['player_name']}') AND player_status='1' LIMIT 1";
                $rplayerclub = $sqlDB->sql_query($qplayerclub, true) or die(__LINE__ . ' = ' . mysql_error());
                while ($rowplayerclub = $sqlDB->sql_fetchrow($rplayerclub)) {
                    $is_my_team = (isset($rowplayerclub['player_club']) && $rowplayerclub['player_club']) ? str_replace(' ', '_', $rowplayerclub['player_club']) : '';
                }
                /* end of midseason transfer */

                if (!$is_my_team) {
                    $is_my_team = ($rowd['home'] == 1) ? $rowsc['home'] . $rowsc['home2'] : $rowsc['away'] . $rowsc['away2'];
                }
                file_put_contents($tmp_file, str_replace(' ', '_', $rowd['player_name']) . "\t" . str_replace(' ', '_', $is_my_team) . "\n", FILE_APPEND);
            }
            $sqlDB->sql_freeresult($rd);
        }
        $sqlDB->sql_freeresult($rsc);
    }
    $sqlDB->sql_freeresult($r);

    exec('cat ' . $tmp_file . ' | awk \'{data[$1]+=1;kelub[$1]=$2;} END {for (x in data) {print x "\t" data[x] "\t" kelub[x]}}\' | sort -k2nr > ' . $tmp_file2);
    $data = array_filter(array_map('trim', file($tmp_file2)));
    $count = 2;
    $counter = 1;
    $content .= '
        <div class="jdtop1"><strong>No</strong></div>
        <div class="jdtopa1"><strong>Nama</strong></div>
        <div class="jdtop1"><strong>Goal</strong></div>
        <div class="jdtopa1"><strong>Klub</strong></div>
        <div class="clear"></div>
    ';
    $cache_topscore = '';
    $topscore_data = array();
    foreach ($data as $vdata) {
        list($player, $goal, $team) = explode("\t", $vdata);
        $player = str_replace('_', ' ', $player);
        $team = str_replace('_', ' ', $team);

        $qp = "SELECT player_url FROM player_profile WHERE player_name='" . kln_real_escape_string($player) . "' LIMIT 1";
        $rp = $sqlDB->sql_query($qp, true) or die(__LINE__ . ' = ' . mysql_error());
        $rowp = $sqlDB->sql_fetchrow($rp);

        $_player_url_ = $profile_url;
        if (isset($rowp['player_url']) && $rowp['player_url'] && is_file($profile_dir.$rowp['player_url'].'/index.html')) {
            $_player_url_ = $profile_url.$rowp['player_url'].'/';
        }
            
        $content .= '
            <div class="jdtop' . $count . '">' . $counter . '</div>
            <div class="jdtopa' . $count . '"><a href="/profile/' . $rowp['player_url'] . '/" class="greenlink">' . stripslashes($player) . '</a></div>
            <div class="jdtop' . $count . '">' . $goal . '</div>
            <div class="jdtopa' . $count . '">' . $team . '</div>
            <div class="clear"></div>
        ';
        $cache_topscore .= stripslashes($player) . "\t$goal\t$team\t" . $rowp['player_url'] . "\n";

        $count = $count == 1 ? 2 : 1;
        $counter++;

        $sqlDB->sql_freeresult($rp);
        
        if ($counter <= 25) {
            $topscore_data[] = array(
                'rank' => $counter,
                'player' => stripslashes($player),
                'player_url' => $_player_url_,
                'team' => $team,
                'goal' => $goal,
            );
        }
    }
    file_put_contents(CACHEDIR . 'cache_topscore_' . md5($v), $cache_topscore);
    file_put_contents(CACHEDIR . 'xcache_topscore_' . md5($v), $cache_topscore);

    $content .= '
                <br/>
            </div>
        </div>
    ';

    if ($topscore_data) {
        $memcache = new Memcache;
        bola_memcached_connect($memcache);
        $memcache->set('bolanet_topscore_data_'.$k, serialize($topscore_data), false, 24*3600);
        write_file_direct(TEMPDIR.'topscore/bolanet_topscore_data_'.$k, serialize($topscore_data));
        $memcache->close();
    }
    
    $filename = $schedule_dir . 'topskor_liga_' . $v . '.html';
    $fileurl = $schedule_url . 'topskor_liga_' . $v . '.html';

    write_file($filename, $content, 'Top Skor Klasemen '.$league_title, 'Top Skor Klasemen Liga Inggris, Top Skor Liga Italia, Top Skor Liga Spanyol', 'Top Skor Klasemen Liga Inggris, Top Skor Liga Italia, Top Skor Liga Spanyol', '', true, 'full', 5);
    echo generated_link($fileurl);
}

/**
 * generate head 2 head in schedule
 */
function generate_schedule_headtohead($sqlDB, $season_id, $club1, $club2)
{
    $club1_home = 0;
    $club1_away = 0;
    $club2_home = 0;
    $club2_away = 0;

    $q = "
        SELECT * FROM dbschedule
        WHERE (TRIM(home)='$club1' OR TRIM(home2)='$club1') AND (TRIM(away)='$club2' OR TRIM(away2)='$club2') AND level='2' AND schedule<NOW() AND idseason='$season_id'
        ORDER BY schedule";
    $r = $sqlDB->sql_query($q);
    while ($row = $sqlDB->sql_fetchrow($r)) {
        $club1_home += $row['goal_home'];
        $club2_away += $row['goal_away'];
    }
    $sqlDB->sql_freeresult($r);

    $q = "
        SELECT * FROM dbschedule
        WHERE (TRIM(home)='$club2' OR TRIM(home2)='$club2') AND (TRIM(away)='$club1' OR TRIM(away2)='$club1') AND level='2' AND schedule<NOW() AND idseason='$season_id'
        ORDER BY schedule";
    $r = $sqlDB->sql_query($q);
    while ($row = $sqlDB->sql_fetchrow($r)) {
        $club2_home += $row['goal_home'];
        $club1_away += $row['goal_away'];
    }
    $sqlDB->sql_freeresult($r);

    $total1 = $club1_home + $club1_away;
    $total2 = $club2_home + $club2_away;
    
    
    if ($total1 > $total2) {
        return 1;
    }
    if ($total2 > $total1) {
        return 2;
    }
    if ($club1_away > $club2_away) {
        return 1;
    }
    if ($club2_away > $club1_away) {
        return 2;
    }
    return 0;
}


/**
 * generate head 2 head in spanyol
 */
function generate_schedule_headtohead_spanyol($sqlDB, $season_id, $club1, $club2, $club1_gf, $club1_ga, $club2_gf, $club2_ga, $title = '')
{
    $where_schedule = '';
    if ($title) {
        $where_schedule = " AND title = '$title' ";
    }
    
    $club1_home = 0;
    $club1_away = 0;
    $club2_home = 0;
    $club2_away = 0;

    $q = "
        SELECT * FROM dbschedule
        WHERE (TRIM(home)='$club1' OR TRIM(home2)='$club1') AND (TRIM(away)='$club2' OR TRIM(away2)='$club2') AND level='2' AND schedule<NOW() AND idseason='$season_id' $where_schedule
        ORDER BY schedule";
    $r = $sqlDB->sql_query($q);
    while ($row = $sqlDB->sql_fetchrow($r)) {
        $club1_home += $row['goal_home'];
        $club2_away += $row['goal_away'];
    }
    $sqlDB->sql_freeresult($r);

    $q = "
        SELECT * FROM dbschedule
        WHERE (TRIM(home)='$club2' OR TRIM(home2)='$club2') AND (TRIM(away)='$club1' OR TRIM(away2)='$club1') AND level='2' AND schedule<NOW() AND idseason='$season_id' $where_schedule
        ORDER BY schedule";
    $r = $sqlDB->sql_query($q);
    while ($row = $sqlDB->sql_fetchrow($r)) {
        $club2_home += $row['goal_home'];
        $club1_away += $row['goal_away'];
    }
    $sqlDB->sql_freeresult($r);

    $total1 = $club1_home + $club1_away;
    $total2 = $club2_home + $club2_away;
    
    //total head 2 head goal
    if ($total1 > $total2) {
        return 1;
    }
    if ($total2 > $total1) {
        return 2;
    }
    
    //selisih goal
    if (($club1_gf - $club1_ga) > ($club2_gf - $club2_ga)) {
        return 1;
    }
    if (($club1_gf - $club1_ga) < ($club2_gf - $club2_ga)) {
        return 2;
    }
    
        
    //goal memasukkan terbanyak
    if ($club1_gf > $club2_gf) {
        return 1;
    }
    if ($club1_gf < $club2_gf) {
        return 2;
    }
        
    
    return 0;
}

/**
 * generate head 2 head in isc
 */
function generate_schedule_headtohead_indonesia($sqlDB, $season_id, $club1, $club2, $club1_gf, $club1_ga, $club2_gf, $club2_ga, $title = '')
{
    $where_schedule = '';
    if ($title) {
        $where_schedule = " AND title = '$title' ";
    }
    
    $club1_home = 0;
    $club1_away = 0;
    $club2_home = 0;
    $club2_away = 0;

    $q = "
        SELECT * FROM dbschedule
        WHERE (TRIM(home)='$club1' OR TRIM(home2)='$club1') AND (TRIM(away)='$club2' OR TRIM(away2)='$club2') AND level='2' AND schedule<NOW() AND idseason='$season_id' $where_schedule
        ORDER BY schedule";

    $r = $sqlDB->sql_query($q);
    while ($row = $sqlDB->sql_fetchrow($r)) {
        $club1_home += $row['goal_home'];
        $club2_away += $row['goal_away'];
    }
    $sqlDB->sql_freeresult($r);

    $q = "
        SELECT * FROM dbschedule
        WHERE (TRIM(home)='$club2' OR TRIM(home2)='$club2') AND (TRIM(away)='$club1' OR TRIM(away2)='$club1') AND level='2' AND schedule<NOW() AND idseason='$season_id' $where_schedule
        ORDER BY schedule";
        
    $r = $sqlDB->sql_query($q);
    while ($row = $sqlDB->sql_fetchrow($r)) {
        $club2_home += $row['goal_home'];
        $club1_away += $row['goal_away'];
    }
    $sqlDB->sql_freeresult($r);

    $total1 = $club1_home + $club1_away;
    $total2 = $club2_home + $club2_away;
    
    //total head 2 head goal
    if ($total1 > $total2) {
        return 1;
    }
    if ($total2 > $total1) {
        return 2;
    }
    
    //selisih goal
    if (($club1_gf - $club1_ga) > ($club2_gf - $club2_ga)) {
        return 1;
    }
    if (($club1_gf - $club1_ga) < ($club2_gf - $club2_ga)) {
        return 2;
    }
    
        
    //goal memasukkan terbanyak
    if ($club1_gf > $club2_gf) {
        return 1;
    }
    if ($club1_gf < $club2_gf) {
        return 2;
    }
        
    
    return 0;
}
/**
 * special design schedule for Liga Champions
 * also can used for all league
 */
function generate_schedule_index_champions($sqlDB, $league = 'champions')
{
    global $schedule_url, $schedule_dir, $month_list_ina, $tv_url, $tv_jadwal_url;
    
    $CHAMPION_SEASON = '50';

    $score_dir = $schedule_dir . 'hasil_pertandingan/';
    $score_url = $schedule_url . 'hasil_pertandingan/';

    $menu_top = '
        <div class="topmenu">
            <a href="' . $schedule_url . 'score.html">Skor Terkini</a>
            &nbsp;&nbsp;&nbsp;
            <a href="' . $schedule_url . '">Jadwal Pertandingan</a>
            &nbsp;&nbsp;&nbsp;
            <a href="' . $tv_jadwal_url . '">Jadwal TV</a>
            &nbsp;&nbsp;
            <a href="' . $schedule_url . 'klasemen_liga_inggris.html">Klasemen</a>
            &nbsp;&nbsp;
            <a href="' . $schedule_url . 'preview.html">Preview</a>
            &nbsp;&nbsp;
            <a href="' . $schedule_url . 'review.html">Review</a>
        </div>
    ';

    $where_league = 'AND title=\'Liga Champions\' AND country LIKE \'UEFA%\'';
    $fname = 'index_liga_champions';
    $selected_cat = 5;

    /*$share = '
            <!--SOCIALTAB-->
            <div id="bl-social-tabs"></div>
            <script type="text/javascript">
                $(document).ready(function() {
                    $("#bl-social-tabs").klsocial({
                        url:"'.BOLAURL.'jadwal_skor/' . $fname . '.html",
                        comment_count:"-1"
                    });
                  });
            </script>
            <!--ENDSOCIALTAB-->
        ';*/

    $qcount = "SELECT COUNT(*) AS t FROM `dbschedule` WHERE idseason='$CHAMPION_SEASON' AND level<>'0' $where_league";
    $rcount = $sqlDB->sql_query($qcount, true) or die(__LINE__ . ' = ' . mysql_error());
    $rowcount = $sqlDB->sql_fetchrow($rcount);
    $total_schedule = $rowcount['t'];
    $sqlDB->sql_freeresult($rcount);

    $othernews = get_related_news_league($sqlDB, $selected_cat);
    $related_news = '';
    if (count($othernews) > 0) {
        $related_news = '
            <br/><br/><div class="ntbottom">Berita Liga ' . ucwords($league) . ' Terbaru</div>
        ';
        foreach ($othernews as $v) {
            $v['schedule'] = preg_replace('/\:\d\d$/', '', $v['schedule']);
            $v['schedule'] = preg_replace('/(\d{4,4})\-(\d{2,2})\-(\d{2,2}) (\d{2,2})\:(\d{2,2})/', '\3-\2-\1 \4:\5', $v['schedule']);
            $related_news .= '
                <div class="bcontent1">
                    <span>(' . $v['schedule'] . ')</span>
                    <a href="' . BOLAURL . $v['category_url'] . '/' . $v['url'] . '.html" style="font-weight:normal;">' . $v['title'] . '</a>
                </div>
            ';
        }
        $related_news .= '<div class="clear"></div>';
    }

    $page = 1;
    $counter = 0;
    $limit_view = 20;
    ;
    $total_page = ceil($total_schedule / $limit_view);
    $tmp_date = '';
    $show_first = 0;
    $show_now = time();
    $is_index_generated = false;

    $q3 = "
        SELECT DATE(schedule) AS d FROM `dbschedule`
        WHERE idseason='$CHAMPION_SEASON' AND level<>'0' $where_league GROUP BY DATE(schedule)
        ORDER BY schedule";
    $r3 = $sqlDB->sql_query($q3, true) or die(__LINE__ . ' = ' . mysql_error());

    while ($row3 = $sqlDB->sql_fetchrow($r3)) {
        $q2 = "
            SELECT country, title, schedule FROM `dbschedule`
            WHERE idseason='$CHAMPION_SEASON' AND level<>'0' AND DATE(schedule)='$row3[d]' $where_league
            GROUP BY country, title";
        $r2 = $sqlDB->sql_query($q2, true) or die(__LINE__ . ' = ' . mysql_error());

        list($tahun, $bulanint, $hari) = array_map('intval', explode('-', $row3['d']));
        $bulan = $month_list_ina[$bulanint - 1];
        $date_title = $hari . ' ' . $bulan . ' ' . $tahun;

        while ($row2 = $sqlDB->sql_fetchrow($r2)) {
            $q = "
                SELECT * FROM dbschedule
                WHERE idseason='$CHAMPION_SEASON' AND level<>'0' AND country='$row2[country]' AND title='$row2[title]' AND DATE(schedule)='$row3[d]'
                ORDER BY schedule DESC";
            $r = $sqlDB->sql_query($q, true) or die(__LINE__ . ' = ' . mysql_error());
            $num = $sqlDB->sql_numrows($r);
            if ($num == 0) {
                continue;
            }

            while ($row = $sqlDB->sql_fetchrow($r)) {
                if ($counter % $limit_view == 0) {
                    $content = '
                        <div class="bigcon">
                            <div class="bigcon2">
                                <div class="nav">
                                    <a href="/" style="text-decoration:none;">HOME</a> &raquo;
                                    JADWAL PERTANDINGAN
                                </div>
                                ' . $menu_top . '
                                <br/>
                                <div class="greentitle">JADWAL PERTANDINGAN LIGA '.strtoupper($league).'</div>
                                <div class="jdskor1">Pilih Kompetisi: '.dropdown_klasemen_options($league).'</div>
                    ';
                }
                $counter++;

                if ($tmp_date != $date_title) {
                    if ($tmp_date) {
                        $content .= '
                                </div>
                                <div class="clear"></div>
                                <br/>
                        ';
                    }

                    $content .= '
                        <div class="jdboxx">
                            <div class="jdbox" style="color:#6B816A;">
                                <strong>' . $date_title . '</strong>
                            </div>
                    ';
                    $tmp_date = $date_title;
                }

                if ($row['level'] == '2') {
                    $fnameDetail = getURL($row['title'] . '-' . $row['home'] . $row['home2'] . '-vs-' . $row['away'] . $row['away2'] . '-' . date('Y-m-d', strtotime($row['schedule'])));
                    $filenameDetail = $score_url . $fnameDetail . '.html';
                    $detail = '<a href="' . $filenameDetail . '" class="bluelink">detail</a>';
                } else {
                    $detail = '---';
                }

                $schedule_time = strtotime($row['schedule']);
                list($tanggal, $jam) = explode(' ', $row['schedule']);
                $jam = preg_replace('/\:00$/', '', $jam);
                $show_time = $row['level'] == '2' ? 'FT' : $jam;
                if (!$show_first) {
                    $show_first = $schedule_time;
                }

                $qstadium = "SELECT team_stadion FROM dbteam WHERE TRIM(LOWER(team_name))='" . strtolower(trim($row['home'])) . "' LIMIT 1";
                $rstadium = $sqlDB->sql_query($qstadium, true) or die(__LINE__ . ' = ' . mysql_error());
                $rowstadium = $sqlDB->sql_fetchrow($rstadium);
                $lapangan = $rowstadium['team_stadion'] ? $rowstadium['team_stadion'] : '-';
                $sqlDB->sql_freeresult($rstadium);

                $home_content = $row['home'] . $row['home2'];
                $away_content = $row['away'] . $row['away2'];
                $country_content = str_replace('UEFA Penyisihan ', '', $row2['country']);

                $content .= '
                    <div class="jdbox2">
                        <div class="jdbox2a">
                            ' . $show_time . ' 
                        </div>
                        <div class="jdislbox" style="width: 90px;">
                            ' . $country_content . '
                        </div>
                        <div class="jdislbox2" style="width: 120px;">
                            ' . $home_content . '
                        </div>
                        <div class="jdislbox2" style="width: 120px;">
                            ' . $away_content . '
                        </div>
                        <div class="jdislbox3">
                            ' . $detail . '
                        </div>
                    </div>
                    <div class="clear"></div>
                ';

                if ($counter % $limit_view == 0 || $counter == $total_schedule) {
                    if ($tmp_date == $date_title) {
                        $content .= '
                                </div>
                            <div class="clear"></div>
                            <br/>
                        ';
                    }

                    $tmp_date = '';
                    $paging = schedule_paging_10($page, $total_page, $fname);

                    $content .= '
                                <br/>
                                ' . $paging . '
                                ' . $related_news . '
                            </div>
                        </div>
                    ';

                    $filename = $schedule_dir . $fname . $page . '.html';
                    $fileurl = $schedule_url . $fname . $page . '.html';

                    write_file($filename, $content, 'Jadwal Liga Indonesia Terkini (Liga Super Indonesia)', 'Jadwal Liga Indonesia Terkini (Liga Super Indonesia)', 'Jadwal Liga Indonesia (Liga Super Indonesia) Terkini', '', true, 'full', 5);
                    echo generated_link($fileurl);

                    // ub - penambahan satu set kondisi ketika $show_now >= $schedule_time tapi front index belum di generate
                    //if (($show_now >= $show_first && $show_now <= $schedule_time) || ($show_now >= $show_first && $show_now >= $schedule_time && $total_schedule==$counter && !$is_index_generated))
                    if (($show_now <= $show_first && !$is_index_generated) || ($show_now >= $show_first && $show_now >= $schedule_time && $total_schedule == $counter && !$is_index_generated)) {
                        $filename = $schedule_dir . $fname . '.html';
                        $fileurl = $schedule_url . $fname . '.html';
                        write_file($filename, $content, 'Jadwal Liga Indonesia Terkini (Liga Super Indonesia)', 'Jadwal Liga Indonesia Terkini (Liga Super Indonesia)', 'Jadwal Liga Indonesia (Liga Super Indonesia) Terkini');
                        echo generated_link($fileurl);
                        $is_index_generated = true;
                    }
                    $show_first = 0;

                    $page = intval($page) + 1;
                }
            }
            $sqlDB->sql_freeresult($r);
        }
        $sqlDB->sql_freeresult($r2);
    }
    $sqlDB->sql_freeresult($r3);
}

/*
 * generate 100 detail pertandingan/schedule score
 * 2012-05-31
 */

function generate_schedule_score($sqlDB, $start = '', $end = '', $limit = 100)
{
    $limits_query = " LIMIT 0, $limit";
    if ($limit == -1) {
        $limits_query = '';
    }
    $where_date = '';
    if ($start && $end) {
        $where_date = " AND DATE(A.schedule) >= '$start' AND DATE(A.schedule) <= '$end' ";
    } else {
        //di mundurkan max 3 hari yang lalu, tanpa limit terlalu berat
        $start = date('Y-m-d', (time() - 259200));
        $end = date('Y-m-d', (time()));
        
        $where_date = " AND DATE(A.schedule) >= '$start' AND DATE(A.schedule) <= '$end' ";
    }
    //$q = "SELECT A.id FROM dbschedule A WHERE A.schedule<=NOW() AND A.level='2' ORDER BY A.schedule DESC $limits_query";
    $q = "SELECT A.id FROM dbschedule A WHERE A.schedule<=NOW() AND A.level='2' $where_date ORDER BY A.schedule DESC $limits_query";

    $r = $sqlDB->sql_query($q, true) or die(__LINE__ . ' = ' . mysql_error());
    while ($row = $sqlDB->sql_fetchrow($r)) {
        generate_schedule_score_byid($sqlDB, $row['id']);
    }
    $sqlDB->sql_freeresult();
}

/**
 * generate schedule score by id [match detail - hasil pertandingan]
 * @lichul trvs
 *
 * @map url: www.bola.net/jadwal_skor/hasil_pertandingan/$match.html
 */
function generate_schedule_score_byid($sqlDB, $id)
{
    global $schedule_url, $schedule_dir, $club_url, $month_list_ina, $tv_url, $cdn_url, $library_dir, $photo_library_media, $team_media_url, $image_library_url ;
    
    $score_dir = $schedule_dir . 'hasil_pertandingan/';
    $score_url = $schedule_url . 'hasil_pertandingan/';
    if (!is_dir($score_dir)) {
        mkdir($score_dir);
    }

    $include = '';

    /*$q = "
        SELECT DATE(schedule) AS dd, A.*, B.season_name
        FROM dbschedule A LEFT JOIN dbseason B ON A.idseason=B.season_id
        WHERE A.schedule<=NOW() AND A.level='2' AND A.id='$id' LIMIT 1";*/
    $q = "
        SELECT DATE(schedule) AS dd, A.*, B.season_name
        FROM dbschedule A LEFT JOIN dbseason B ON A.idseason=B.season_id
        WHERE A.schedule != '0000-00-00 00:00:00' AND A.level>='1' AND A.id='$id' LIMIT 1";
    $r = $sqlDB->sql_query($q, true) or die(__LINE__ . ' = ' . mysql_error());
    while ($row = $sqlDB->sql_fetchrow($r)) {
        $q2 = "SELECT * FROM dbscore WHERE id_schedule='" . $row['id'] . "' AND level='1' ORDER BY minute*1 asc";
        $r2 = $sqlDB->sql_query($q2, true) or die('something happen');

        $skor_home = $skor_home_pk = 0;
        $skor_away = $skor_away_pk = 0;

        $match_extra = $match_pk = '';
        $match_detail_part[0] = $match_detail_part[1] = $match_detail_part[2] = '';

        $class_row = 'row_light';
        $match_detail = '
            <br/>
            <h1 class="header"><a href="">DETAIL PERTANDINGAN</a></h1>
            <div class="match_preview" style="padding:0">
            <div class="match_detail">
        ';
        while ($row2 = $sqlDB->sql_fetchrow($r2)) {
            $class_row = ($class_row == 'row_light') ? 'row_dark' : 'row_light';

            $home_info = '';
            $home_player = '';

            $away_info = '';
            $away_player = '';

            if ($row2['home'] == 1) {
                if ($row2['info'] == 'owngoal') {
                    $skor_away++;
                } else {
                    if (in_array($row2['info'], array('goal', 'penalty goal')) && ($row2['time_action'] == 0)) {
                        $skor_home++;
                    }
                    if (($row2['time_action'] == 2) && ($row2['info'] == 'penalty goal')) {
                        $skor_home_pk++;
                    }
                }

                $home_info = $row2['info'];
                $home_player = $row2['player_name'];
                $match_detail_part[$row2['time_action']] .= '
                    <div class="' . $class_row . '">
                        <div class="col_home">
                            <span class="match_icon ' . get_match_icon($home_info) . '"></span>
                            <span class="info">' . $home_player . '</span>
                            <div class="clear"></div>
                        </div>
                        <div class="col_m">' . $row2['minute'] . '"</div>
                        <div class="col_away">
                            <span class="match_icon"></span>
                            <span class="info"></span>
                            <div class="clear"></div>
                        </div>
                        <div class="clear"></div>
                    </div>
                ';
            } elseif ($row2['away'] == 1) {
                if ($row2['info'] == 'owngoal') {
                    $skor_home++;
                } else {
                    if (in_array($row2['info'], array('goal', 'penalty goal')) && ($row2['time_action'] == 0)) {
                        $skor_away++;
                    }
                    if (($row2['time_action'] == 2) && ($row2['info'] == 'penalty goal')) {
                        $skor_away_pk++;
                    }
                }

                $away_info = $row2['info'];
                $away_player = $row2['player_name'];
                $match_detail_part[$row2['time_action']] .= '
                <div class="' . $class_row . '">
                    <div class="col_home">
                        <span class="match_icon "></span>
                        <span class="info"></span>
                        <div class="clear"></div>
                    </div>
                    <div class="col_m">' . $row2['minute'] . '"</div>
                    <div class="col_away">
                        <span class="match_icon ' . get_match_icon($away_info) . '"></span>
                        <span class="info">' . $away_player . '</span>
                        <div class="clear"></div>
                    </div>
                    <div class="clear"></div>
                </div>
                ';
            }
        }

        if ($match_detail_part[1]) {
            $match_detail_part[1] = '<br/><div style="padding:5px 0 3px 0;text-align:center;background:#BFE915"><strong>EXTRA TIME</strong></div>' . $match_detail_part[1];
        }
        if ($match_detail_part[2]) {
            $match_detail_part[2] = '<br/><div style="padding:5px 0 0 3px;text-align:center;background:#BFE915"><strong>PENALTY SHOT OUT</strong></div>' . $match_detail_part[2];
        }
        $match_detail .= $match_detail_part[0] . $match_detail_part[1] . $match_detail_part[2] . '
                </div>        
            </div>
        ';

        $sqlDB->sql_freeresult($r2);

        /* Statistik Pertandingan */
        $possession_graph = $statistik_graph = '';
        if ($row['possession_home'] && $row['possession_away']) {
            $possession_graph = "
                var home_color = '#c0e815';
                var away_color = '#999'; /*#6699cc*/
                
                var posession_data = [
                    {
                        name: '" . strtoupper($row['away'] . $row['away2']) . "',
                        y: " . $row['possession_away'] . ",
                        sliced: true,
                        color: away_color,
                    },
                    {
                        name: '" . strtoupper($row['home'] . $row['home2']) . "',
                        y: " . $row['possession_home'] . ",
                        sliced: false,
                        color: home_color,
                    }
                ];
                
                var piechart = new Highcharts.Chart({
                    chart: {
                        renderTo: 'possession',
                        plotBackgroundColor: null,
                        plotBorderWidth: null,
                        plotShadow: false,
                        backgroundColor: '#f4f4f4',
                        margin:0,
                    },
                    title: {
                        text: 'POSSESSION',
                        y:10
                    },
                    credits: {
                        enabled: true,
                        href: 'http://www.bola.net',
                        text: 'Bola.net'
                    },
                    tooltip: {
                        formatter: function() {
                            return '<b>'+ this.point.name +'</b>: '+ Math.round(this.percentage) +' %';
                        }
                    },
                    plotOptions: {
                        pie: {
                            allowPointSelect: false,
                            cursor: 'pointer',
                            size:140,
                            shadow:false,
                            dataLabels: {
                                enabled: true,
                                color: '#444',
                                connectorColor: '#fff',
                                distance:-20,
                                formatter: function() {
                                    return '<b>'+ Math.round(this.percentage) +' %</b><br/>'+this.point.name;
                                }
                            }
                        }
                    },
                    series: [{
                        type: 'pie',
                        name: 'Ball Possession',
                        data: posession_data
                    }],
                    exporting: {
                        enabled:false,
                    }
                });
            ";
        }

        if ($row['corner_home'] && $row['shot_on_home'] && $row['shot_off_home'] && $row['foul_home'] && $row['offside_home']) {
            $max_value = 0;
            foreach (array('home', 'away') as $ha) {
                foreach (array('corner', 'shot_on', 'shot_off', 'foul', 'offside') as $key) {
                    if ($row[$key . '_' . $ha] > $max_value) {
                        $max_value = $row[$key . '_' . $ha];
                    }
                }
            }

            $min_value = 0 - $max_value;

            $statistik_graph = "
                var names = new Array();
                names['COR'] = names['NERS'] = 'CORNERS';
                names['SHOTSO'] = names['N TARGET'] = 'SHOTS ON TARGET';
                names['SHOT'] = names['S WIDE'] = 'SHOTS WIDE';
                names['FO'] = names['ULS'] = 'FOULS';
                names['OFF'] = names['SIDES'] = 'OFFSIDES';
                
                var max_value = " . $max_value . ";
                var min_value = " . $min_value . ";
                var font_color = '#444';                
                var bar_bg_color = '#eee';
                
                var home_labels = {
                    items: [
                        {
                            html: '<b>" . ($row['corner_home']) . "</b>',
                            style:{
                                left: '10px',
                                top: '25px',
                                fontSize:'10px',
                                fontWeight:'bold',
                                color: font_color
                            }
                        },
                        {
                            html: '<b>" . ($row['shot_on_home']) . "</b>',
                            style:{
                                left: '10px',
                                top: '60px',
                                fontSize:'10px',
                                fontWeight:'bold',
                                color: font_color
                            }
                        },
                        {
                            html: '<b>" . ($row['shot_off_home']) . "</b>',
                            style:{
                                left: '10px',
                                top: '95px',
                                fontSize:'10px',
                                fontWeight:'bold',
                                color: font_color
                            }
                        },
                        {
                            html: '<b>" . ($row['foul_home']) . "</b>',
                            style:{
                                left: '10px',
                                top: '130px',
                                fontSize:'10px',
                                fontWeight:'bold',
                                color: font_color
                            }
                        },
                        {
                            html: '<b>" . ($row['offside_home']) . "</b>',
                            style:{
                                left: '10px',
                                top: '165px',
                                fontSize:'10px',
                                fontWeight:'bold',
                                color: font_color
                            }
                        },
                    ]
                };
                
                var away_labels = {
                    items: [
                        {
                            html: '<b>" . ($row['corner_away']) . "</b>',
                            style:{
                                left: '115px',
                                top: '25px',
                                fontSize:'10px',
                                fontWeight:'bold',
                                color: font_color
                            }
                        },
                        {
                            html: '<b>" . ($row['shot_on_away']) . "</b>',
                            style:{
                                left: '115px',
                                top: '60px',
                                fontSize:'10px',
                                fontWeight:'bold',
                                color: font_color
                            }
                        },
                        {
                            html: '<b>" . ($row['shot_off_away']) . "</b>',
                            style:{
                                left: '115px',
                                top: '95px',
                                fontSize:'10px',
                                fontWeight:'bold',
                                color: font_color
                            }
                        },
                        {
                            html: '<b>" . ($row['foul_away']) . "</b>',
                            style:{
                                left: '115px',
                                top: '130px',
                                fontSize:'10px',
                                fontWeight:'bold',
                                color: font_color
                            }
                        },
                        {
                            html: '<b>" . ($row['offside_away']) . "</b>',
                            style:{
                                left: '115px',
                                top: '165px',
                                fontSize:'10px',
                                fontWeight:'bold',
                                color: font_color
                            }
                        },
                    ]
                };
                
                var home_series = [
                    {
                        name: '" . strtoupper($row['home'] . $row['home2']) . "',
                        data: [
                            {
                                color: bar_bg_color,
                                y: (min_value + " . ($row['corner_home']) . ")
                            }, 
                            {
                                color: bar_bg_color,
                                y: (min_value + " . ($row['shot_on_home']) . ")
                            }, 
                            {
                                color: bar_bg_color,
                                y: (min_value + " . ($row['shot_off_home']) . ")
                            }, 
                            {
                                color: bar_bg_color,
                                y: (min_value + " . ($row['foul_home']) . ")
                            }, 
                            {
                                color: bar_bg_color,
                                y: (min_value + " . ($row['offside_home']) . ")
                            }
                        ]
                    }, 
                    {
                        name: '" . strtoupper($row['home'] . $row['home2']) . "',
                        data: [" . (0 - $row['corner_home']) . ", " . (0 - $row['shot_on_home']) . ", " . (0 - $row['shot_off_home']) . ", " . (0 - $row['foul_home']) . ", " . (0 - $row['offside_home']) . "]
                    }
                ];
                
                var away_series = [            
                    {
                        name: '" . strtoupper($row['away'] . $row['away2']) . "',
                        data: [
                            {
                                color: bar_bg_color,
                                y: (max_value - " . ($row['corner_away']) . ")
                            }, 
                            {
                                color: bar_bg_color,
                                y: (max_value - " . ($row['shot_on_away']) . ")
                            }, 
                            {
                                color: bar_bg_color,
                                y: (max_value - " . ($row['shot_off_away']) . ")
                            }, 
                            {
                                color: bar_bg_color,
                                y: (max_value - " . ($row['foul_away']) . ")
                            }, 
                            {
                                color: bar_bg_color,
                                y: (max_value - " . ($row['offside_away']) . ")
                            }
                        ]
                    }, 
                    {
                        name: '" . strtoupper($row['away'] . $row['away2']) . "',
                        data: [" . ($row['corner_away']) . ", " . ($row['shot_on_away']) . ", " . ($row['shot_off_away']) . ", " . ($row['foul_away']) . ", " . ($row['offside_away']) . "]
                    }
                ];
                
                var chart_home = new Highcharts.Chart({
                    chart: {
                        renderTo: 'statistik_home',
                        type: 'bar',
                        marginTop:20,
                        marginRight:0,
                        marginLeft:0,
                        spacingRight: 0,
                        spacingLeft: 0,
                        plotBackgroundColor: '#f4f4f4',
                        backgroundColor: '#f4f4f4',
                    },
                    credits: {
                        enabled: false
                    },
                    title: {
                        text: '',
                    },
                    labels: home_labels,
                    legend:{
                        enabled:false,
                    },
                    xAxis: {
                        categories: ['COR', 'SHOTS O', 'SHOT', 'FO', 'OFF'],
                        lineWidth: 0,
                        gridLineWidth: 0,
                        tickWidth: 0,
                        labels:{
                            align: 'right',
                            style:{
                                fontWeight: 'bold',
                                color: font_color,
                                fontSize:'10px'
                            },
                            y:17,
                            x:150
                        }
                    },
                    yAxis:{
                        labels:{
                            enabled:false
                        },
                        title: {
                            text:''
                        },
                        gridLineWidth: 0,
                        lineWidth:0,
                        min: min_value,
                        max: 0
                    },
                    plotOptions: {
                        series: {
                            stacking: 'normal',
                            dataLabels: {
                                enabled: false,
                                align: 'right',
                                color: '#111',
                                x: 0,
                                y:17,
                                formatter: function(){
                                    return '<b>'+ Math.abs(this.y)+'</b>';
                                }
                            },
                            borderWidth: 0,
                            lineWidth: 0,
                            pointPadding: 0.15,
                            groupPadding: 0.25,
                            shadow:false,
                            color: home_color
                        }
                    },
                    series: home_series,        
                    tooltip: {
                        formatter: function(){
                            return ''+ this.series.name +',<br/>'+ names[this.point.category] +': '+
                                '<b>'+ Highcharts.numberFormat(Math.abs(this.point.y), 0)+'</b>';
                        }
                    },
                });
                
                var chart_away = new Highcharts.Chart({
                    chart: {
                        renderTo: 'statistik_away',
                        type: 'bar',
                        marginTop:20,
                        marginRight:10,
                        marginLeft:0,
                        spacingRight: 0,
                        spacingLeft: 0,
                        plotBackgroundColor: '#f4f4f4',
                        backgroundColor: '#f4f4f4',
                    },
                    
                    credits: {
                        enabled: false
                    },
                    title: {
                        text: '',
                    },
                    credit:{
                        enabled:false,
                    },
                    labels: away_labels,
                    legend:{
                        enabled:false,
                    },
                    xAxis: {
                        categories: ['NERS', 'N TARGET', 'S WIDE', 'ULS', 'SIDES'],
                        lineWidth: 0,
                        labels:{
                            align: 'left',
                            overflow: 'justify',
                            padding:2,
                            style:{
                                fontWeight: 'bold',
                                color: font_color,
                                fontSize:'10px'
                            },
                            y:17,
                            x:1    
                        }
                    },
                    yAxis:{        
                        labels:{
                            enabled:false                   
                        },
                        title: {
                            text:'',
                            
                        },
                        gridLineWidth: 0,
                        min:0,
                        max: max_value
                    },
                    plotOptions: {
                        series: {
                            stacking: 'normal',
                            dataLabels: {
                                enabled: false,
                                align: 'right',
                                color: '#f00',
                                x: 25,
                                y: 17,
                                formatter: function(){
                                    return '<b>'+ Math.abs(this.y)+'</b>';
                                }
                            },
                            borderWidth: 0,
                            pointPadding: 0.15,
                            groupPadding: 0.25,
                            shadow:false,
                            color: away_color
                        }
                    },
                    
                    series: away_series,        
                    tooltip: {
                        formatter: function(){
                            return ''+ this.series.name +',<br/>'+ names[this.point.category] +': '+
                                '<b>'+ Highcharts.numberFormat(Math.abs(this.point.y), 0)+'</b>';
                        }
                    },
                });
            ";
        }

        $match_statistik = '';
        if ($possession_graph || $statistik_graph) {
            $match_statistik = '
                <script type="text/javascript">
                $(function () {
                    $(document).ready(function() {
                        ' . $possession_graph . '
                        ' . $statistik_graph . '
                    })
                })
                </script>
                <br/>
                <h1 class="header"><a href="">STATISTIK PERTANDINGAN</a></h1>
                <div class="match_preview">
                    <div id="statistik" style="width:300px;height:210px;float:left;margin-left:0px;">            
                        <div id="statistik_home" style="height:210px;float:left;width:150px;"></div>
                        <div id="statistik_away" style="height:210px;float:left;width:150px;"></div>
                        <br class="clear"/>
                    </div>
                    <div id="possession" style="width:165px;height:240px; float:left;"></div>
                    <div class="clear"></div>
                </div>
            ';
        }

        $_arr_team_dir_ = array(1 => 'inggris', 2 => 'italia', 3 => 'spanyol', 4 => 'indonesia', 99 => 'other');
        
        $home_club = $row['home'] ? $row['home'] : $row['home2'];
        $away_club = $row['away'] ? $row['away'] : $row['away2'];
        
        $home_link = '';
        $home_logo = $cdn_url . 'library/i/v2/club-logo-default.png';
        $q4 = "SELECT A.team_logo, A.team_category_id, B.image, B.url FROM `dbteam` A LEFT JOIN dbarticles B ON A.team_id=B.team_id AND B.category='16' WHERE A.team_name='$home_club' AND A.team_status = '1' LIMIT 1";
        $r4 = $sqlDB->sql_query($q4, true) or die('something happen');
        if ($row4 = $sqlDB->sql_fetchrow($r4)) {
            $row4['url'] = trim($row4['url']);
            if ($row4['url']) {
                $home_link = $club_url . $row4['url'] . '.html';
            }
            if ($row4['team_logo']) {
                $home_logo = imagelib_url($row4['team_logo'], '175');
            }
        }
        $sqlDB->sql_freeresult($r4);

        $away_link = '';
        $away_logo = $cdn_url . 'library/i/v2/club-logo-default-175.png';
        $q5 = "SELECT A.team_logo, A.team_category_id, B.image, B.url FROM `dbteam` A LEFT JOIN dbarticles B ON A.team_id=B.team_id AND B.category='16' WHERE A.team_name='$away_club' AND A.team_status = '1' LIMIT 1";
        $r5 = $sqlDB->sql_query($q5, true) or die('something happen');
        if ($row5 = $sqlDB->sql_fetchrow($r5)) {
            $row5['url'] = trim($row5['url']);
            if ($row5['url']) {
                $away_link = $club_url . $row5['url'] . '.html';
            }
            
            if ($row5['team_logo']) {
                $away_logo =  imagelib_url($row5['team_logo'], '175');
            }
        }

        $sqlDB->sql_freeresult($r5);

        $fname = getURL($row['title'] . '-' . $row['home'] . $row['home2'] . '-vs-' . $row['away'] . $row['away2'] . '-' . $row['dd']);
        $filename = $score_dir . $fname . '.html';
        $fileurl = $score_url . $fname . '.html';

        
        $match_date = date('d F Y', strtotime($row['schedule']));

        $pk_info = '';
        if ($skor_home_pk > 0 || $skor_away_pk > 0) {
            $pk_info = "Adu Penalti $skor_home_pk - $skor_away_pk <br/>";
        }
        $kompetisi = $kickoff = $venue = '';
        if ($row['title'] || $row['season_name']) {
            $kompetisi = 'Kompetisi: ' . $row['title'] . ' ' . $row['season_name'] . '<br/>';
        }
        if ($row['kickoff']) {
            $kickoff = 'Kick-off: ' . $row['kickoff'] . '<br/>';
        }
        if ($row['venue']) {
            $venue = 'Venue: ' . $row['venue'] . '<br/>';
        }
        //wo information
        if ($row['is_wo'] == 1) {
            $skor_home = $row['goal_home'];
            $skor_away = $row['goal_away'];
            $_winner = '<a href="' . $home_link . '" class="team_name"/>' . strtoupper($home_club) . '</a>';
            if ($row['goal_away'] > $row['goal_home']) {
                $_winner = '<a href="' . $away_link . '" class="team_name"/>' . strtoupper($away_club) . '</a>';
            }
            $venue = '<br/><strong>' . $_winner . ' menang WO</strong><br/>';
            $match_detail = '';
        }

        /*$share = '
        <div class="share" style="margin-top:10px;display:block;">
            <div id="bl-social-tabs"></div>
            <script type="text/javascript">
                $(document).ready(function() {
                    $("#bl-social-tabs").klsocial({
                        url:"' . $fileurl . '",
                        comment_count:-1
                    });
                });
            </script>
        </div>
        ';*/

        $match_header = '
            <div class="match_preview">
                <div class="team_home">
                    <center>
                        <a href="' . $home_link . '" class="team_name"/>' . strtoupper($row['home'] . $row['home2']) . '</a>
                        <a href="' . $home_link . '"/><img src="' . $home_logo . '" alt=""/></a>
                    </center>
                </div>                            
                <div class="match_skor">
                    <center>
                        <div class="skor">' . $skor_home . ' - ' . $skor_away . '</div>
                        <div class="info">' . $pk_info . '</div>
                    </center>
                </div>
                <div class="team_away">
                    <center>
                        <a href="' . $away_link . '" class="team_name"/>' . strtoupper($row['away'] . $row['away2']) . '</a>
                        <a href="' . $away_link . '"/><img src="' . $away_logo . '" alt=""/></a>
                    </center>
                </div>
                <div class="clear"></div>
                
                <br/>
                <div class="match_info">
                    ' . $kompetisi . '
                    Tanggal: ' . $match_date . '<br/>
                    ' . $kickoff . '
                    ' . $venue . '
                </div>
                
            </div>
        ';

        $match_submenu = '
            <div class="match_detail_menu">
        ';

        //lineup
        $lineup_html = '<span>SUSUNAN PEMAIN</span>';
        $_lineup_ = generate_schedule_lineup($sqlDB, $id);
        if ($_lineup_) {
            $lineup_html = '<a href="' . $_lineup_ . '">SUSUNAN PEMAIN</a>';
        }

        //preview
        $news_preview_html = '<span>PREVIEW</span>';
        $news_preview_url = '';
        if ($row['news_preview']) {
            if (!function_exists('generate_per_id')) {
                include FUNCTIONDIR . "function.news.php";
            }
            $dircat = getAllNewsCat($sqlDB);
            $sql = "SELECT dbnews.*, category_name, category_url
            FROM dbnews,dbcategory
            WHERE idnews = '{$row['news_preview']}' AND schedule <> '00-00-0000 00:00:00' AND dbnews.category = dbcategory.category_id
            LIMIT 1";
            $resprev = $sqlDB->sql_query($sql, true) or die(__LINE__ . ' = ' . mysql_error());
            while ($rowprev = $sqlDB->sql_fetchrow($resprev)) {
                $news_preview_url = BOLAURL . $dircat[$rowprev['category']][1] . '/' . $rowprev['url'] . '.html';
            }

            if ($news_preview_url) {
                $news_preview_html = '<a href="' . $news_preview_url . '">PREVIEW</a>';
            }
        }

        $match_report_url = '<a href="' . $fileurl . '">MATCH REPORT</a>';

        $news_review_html = '<span>REVIEW</span>';
        $news_review_url = '';
        if ($row['news_review']) {
            if (!function_exists('generate_per_id')) {
                include FUNCTIONDIR . "function.news.php";
            }
            $dircat = getAllNewsCat($sqlDB);
            $sql = "SELECT dbnews.*, category_name, category_url
                FROM dbnews,dbcategory
                WHERE idnews = '{$row['news_review']}' AND schedule <> '00-00-0000 00:00:00' AND dbnews.category = dbcategory.category_id
                LIMIT 1";
            $resrev = $sqlDB->sql_query($sql, true) or die(__LINE__ . ' = ' . mysql_error());
            while ($rowrev = $sqlDB->sql_fetchrow($resrev)) {
                $news_review_url = BOLAURL . $dircat[$rowrev['category']][1] . '/' . $rowrev['url'] . '.html';
            }

            if ($news_review_url) {
                $news_review_html = '<a href="' . $news_review_url . '">REVIEW</a>';
            }
        }
        $match_submenu .= $lineup_html . $news_preview_html . $match_report_url . $news_review_html;
        $match_submenu .= '</div>';

        generate_schedule_lineup($sqlDB, $id, $match_header . str_replace('>SUSUNAN PEMAIN<', ' class="active">SUSUNAN PEMAIN<', $match_submenu));

        if (!function_exists('generate_per_id')) {
            include FUNCTIONDIR . "function.news.php";
        }
        if ($row['news_preview']) {
            generate_per_id($sqlDB, $row['news_preview'], true, $match_header . str_replace('>PREVIEW<', ' class="active">PREVIEW<', $match_submenu));
        }
        if ($row['news_review']) {
            generate_per_id($sqlDB, $row['news_review'], true, $match_header . str_replace('>REVIEW<', ' class="active">REVIEW<', $match_submenu));
        }


        $content = $include . '
            <div class="bigcon">
                <div class="bigcon2" style="padding:10px 0">
                    <div class="nav"><a href="/" style="text-decoration:none;">HOME</a> &raquo; HASIL PERTANDINGAN ' . strtoupper($row['home'] . $row['home2']) . ' VS ' . strtoupper($row['away'] . $row['away2']) . '</div>                
                    <div class="match_wrap">
                        ' . $match_header . '
                        ' . str_replace('>MATCH REPORT<', ' class="active">MATCH REPORT<', $match_submenu) . '
                            
                        ' . $match_detail . '
                        ' . $match_statistik . '
                        <br/>
                        <div class="match_link">
                            <a class="bluelink" href="' . $schedule_url . 'score.html">HASIL PERTANDINGAN LAINNYA</a>
                        </div>
                    </div>
                    
                </div>
            </div>
        ';

        //6682
        write_file($filename, $content, 'Hasil pertandingan ' . $row['home'] . $row['home2'] . ' vs ' . $row['away'] . $row['away2'], 'hasil, pertandingan, sepak bola, bola, ' . $row['home'] . $row['home2'] . ', vs, ' . $row['away'] . $row['away2'], 'Hasil pertandingan ' . $row['home'] . $row['home2'] . ' vs ' . $row['away'] . $row['away2'], '', true, 'full', 5);

        $meta_og_image = '';
        insert_property_og($filename, 'Hasil pertandingan ' . $row['home'] . $row['home2'] . ' vs ' . $row['away'] . $row['away2'], $fileurl, $meta_og_image, '109215469105623', 'Hasil pertandingan ' . $row['home'] . $row['home2'] . ' vs ' . $row['away'] . $row['away2']);

        //replace js - add param &cart
        $f_content = file_get_contents($filename);
        $f_content = str_replace(
                array(
            '<script type="text/javascript" src="'.APPSURL2.'assets/js/min/index.php?v1.1"></script>',
            '<script type="text/javascript" src="'.APPSURL2.'assets/js/min/index.php?v1.2"></script>',
            '<script type="text/javascript" src="'.APPSURL2.'assets/js/min/index.php?v1.5"></script>'
                ), '<script type="text/javascript" src="'.APPSURL2.'assets/js/min/index.php?v1.1&chart"></script>', $f_content);
        $f_content = str_replace('</head>', '<script type="text/javascript" src="'.APPSURL2.'assets/js/min/single/0.0.2/highcharts.js"></script></head>', $f_content);
        @file_put_contents($filename, $f_content, LOCK_EX);

        echo generated_link($fileurl);
    }
    $sqlDB->sql_freeresult();
}

function generate_schedule_lineup($sqlDB, $id, $match_submenu = '')
{
    global $schedule_url, $schedule_dir, $club_url, $month_list_ina, $cdn_url, $library_dir, $photo_library_media;

    $lineup_dir = $schedule_dir . 'susunan-pemain/';
    $lineup_url = $schedule_url . 'susunan-pemain/';
    if (!is_dir($lineup_dir)) {
        mkdir($lineup_dir);
    }

    $q = "
        SELECT DATE(schedule) AS dd, A.*, B.season_name
        FROM dbschedule A LEFT JOIN dbseason B ON A.idseason=B.season_id
        WHERE A.schedule<=NOW() AND A.level='2' AND A.id='$id' LIMIT 1";
    $r = $sqlDB->sql_query($q, true) or die(__LINE__ . ' = ' . mysql_error());
    while ($row = $sqlDB->sql_fetchrow($r)) {

        //build line up
        $home_starter = $home_subs = array();
        $away_starter = $away_subs = array();
        $q_lineup = "SELECT * FROM dbschedule_lineup WHERE id_schedule='$id' ORDER BY home DESC, subs ASC, sequence ASC";
        $r_lineup = $sqlDB->sql_query($q_lineup);
        while ($row_lineup = $sqlDB->sql_fetchrow($r_lineup)) {
            if ($row_lineup['home'] == '1') {
                if ($row_lineup['subs'] == '0') {
                    $home_starter[] = array($row_lineup['player_name'], $row_lineup['player_number'], $row_lineup['player_position']);
                } else {
                    $home_subs[] = array($row_lineup['player_name'], $row_lineup['player_number'], $row_lineup['player_position']);
                }
            } else {
                if ($row_lineup['subs'] == '0') {
                    $away_starter[] = array($row_lineup['player_name'], $row_lineup['player_number'], $row_lineup['player_position']);
                } else {
                    $away_subs[] = array($row_lineup['player_name'], $row_lineup['player_number'], $row_lineup['player_position']);
                }
            }
        }

        $lineup_home = '';
        foreach ($home_starter as $player) {
            $qp = "SELECT player_url FROM player_profile WHERE player_name='" . kln_real_escape_string($player[0]) . "' LIMIT 1";
            $rp = $sqlDB->sql_query($qp, true) or die(__LINE__ . ' = ' . mysql_error());
            $rowp = $sqlDB->sql_fetchrow($rp);
            if (isset($rowp['player_url']) && $rowp['player_url']) {
                $player[0] = '<a href="' . BOLAURL . 'profile/' . $rowp['player_url'] . '/" class="greenlink">' . stripslashes($player[0]) . '</a>';
            }

            $lineup_home .= '
                <div class="lineup_item">
                    (' . $player[1] . ') &nbsp; <strong>' . $player[0] . '</strong>
                </div>
            ';
        }
        if ($home_subs) {
            $lineup_home .= '<div class="lineup_subs_header">Substitution</div>';
            foreach ($home_subs as $player) {
                $qp = "SELECT player_url FROM player_profile WHERE player_name='" . kln_real_escape_string($player[0]) . "' LIMIT 1";
                $rp = $sqlDB->sql_query($qp, true) or die(__LINE__ . ' = ' . mysql_error());
                $rowp = $sqlDB->sql_fetchrow($rp);
                if (isset($rowp['player_url']) && $rowp['player_url']) {
                    $player[0] = '<a href="' . BOLAURL . 'profile/' . $rowp['player_url'] . '/" class="greenlink">' . stripslashes($player[0]) . '</a>';
                }
                $lineup_home .= '
                    <div class="lineup_item">
                        (' . $player[1] . ') &nbsp; <strong>' . $player[0] . '</strong>
                    </div>
                ';
            }
        }

        $lineup_away = '';
        foreach ($away_starter as $player) {
            $qp = "SELECT player_url FROM player_profile WHERE player_name='" . kln_real_escape_string($player[0]) . "' LIMIT 1";
            $rp = $sqlDB->sql_query($qp, true) or die(__LINE__ . ' = ' . mysql_error());
            $rowp = $sqlDB->sql_fetchrow($rp);
            if (isset($rowp['player_url']) && $rowp['player_url']) {
                $player[0] = '<a href="' . BOLAURL . 'profile/' . $rowp['player_url'] . '/" class="greenlink">' . stripslashes($player[0]) . '</a>';
            }

            $lineup_away .= '
                <div class="lineup_item">
                     <strong>' . $player[0] . '</strong>  &nbsp; (' . $player[1] . ')
                </div>
            ';
        }
        if ($away_subs) {
            $lineup_away .= '<div class="lineup_subs_header">Substitution</div>';
            foreach ($away_subs as $player) {
                $qp = "SELECT player_url FROM player_profile WHERE player_name='" . kln_real_escape_string($player[0]) . "' LIMIT 1";
                $rp = $sqlDB->sql_query($qp, true) or die(__LINE__ . ' = ' . mysql_error());
                $rowp = $sqlDB->sql_fetchrow($rp);
                if (isset($rowp['player_url']) && $rowp['player_url']) {
                    $player[0] = '<a href="' . BOLAURL . 'profile/' . $rowp['player_url'] . '/" class="greenlink">' . stripslashes($player[0]) . '</a>';
                }

                $lineup_away .= '
                    <div class="lineup_item">
                        (' . $player[1] . ') &nbsp; <strong>' . $player[0] . '</strong>
                    </div>
                ';
            }
        }

        $lineup_html = '
        <div class="match_preview" style="padding:0">
            <div class="match_lineup">
                <div class="lineup_home">' . $lineup_home . '</div>
                <div class="lineup_away">' . $lineup_away . '</div>
                <div class="clear"></div>
            </div>
        </div>
        ';

        $fname = getURL($row['title'] . '-' . $row['home'] . $row['home2'] . '-vs-' . $row['away'] . $row['away2'] . '-' . $row['dd']);
        $filename = $lineup_dir . $fname . '.html';
        $fileurl = $lineup_url . $fname . '.html';

        if (!$match_submenu) {
            return $fileurl;
        }

        /*$share = '
        <div class="share" style="margin-top:10px;display:block;">
            <div id="bl-social-tabs"></div>
            <script type="text/javascript">
                $(document).ready(function() {
                    $("#bl-social-tabs").klsocial({
                        url:"' . $fileurl . '",
                        comment_count:-1
                    });
                });
            </script>
        </div>
        ';*/

        $content = '
            <div class="bigcon">
                <div class="bigcon2" style="padding:10px 0">
                    <div class="nav"><a href="/" style="text-decoration:none;">HOME</a> &raquo; SUSUNAN PEMAIN ' . strtoupper($row['home'] . $row['home2']) . ' VS ' . strtoupper($row['away'] . $row['away2']) . '</div>                
                    <div class="match_wrap">
                        ' . $match_submenu . '
                        
                        ' . $lineup_html . '
                        <br/>
                    </div>
                    
                </div>
            </div>
        ';

        //6682
        write_file($filename, $content, 'Susunan Pemain ' . $row['home'] . $row['home2'] . ' vs ' . $row['away'] . $row['away2'], 'hasil, pertandingan, sepak bola, bola, ' . $row['home'] . $row['home2'] . ', vs, ' . $row['away'] . $row['away2'], 'Hasil pertandingan ' . $row['home'] . $row['home2'] . ' vs ' . $row['away'] . $row['away2'], '', true, 'full', 5);

        $meta_og_image = '';
        insert_property_og($filename, 'Susunan Pemain ' . $row['home'] . $row['home2'] . ' vs ' . $row['away'] . $row['away2'], $fileurl, $meta_og_image, '109215469105623', 'Hasil pertandingan ' . $row['home'] . $row['home2'] . ' vs ' . $row['away'] . $row['away2']);

        //replace js - add param &cart
        $f_content = file_get_contents($filename);
        $f_content = str_replace('<script type="text/javascript" src="'.APPSURL2.'assets/js/min/index.php?v1.1"></script>', '<script type="text/javascript" src="'.APPSURL2.'assets/js/min/index.php?v1.1&chart"></script>', $f_content);
        @file_put_contents($filename, $f_content, LOCK_EX);

        echo generated_link($fileurl);
    }
    $sqlDB->sql_freeresult();
}

/**
 * get match icon for statistik pertandingan in match detail
 *
 */
function get_match_icon($info)
{
    $icons = array(
        'player in' => 'player_in',
        'player out' => 'player_out',
        'penalty goal' => 'pen_goal',
        'penalty fail' => 'pen_fail',
    );
    if (isset($icons[$info])) {
        return $icons[$info];
    }
    return $info;
}
/**
 * Generate klasemen, liga champions
 * And view 10 top score
 */
function generate_schedule_klasemen_isl($sqlDB, $echo = true)
{
    global $schedule_url, $schedule_dir, $tv_url, $tv_jadwal_url, $temporary_folder;
     
    //generate topscore first
    generate_schedule_topscore_isl($sqlDB, $echo);
    
    //$cache_name = '_bola_standing_isl_2015_data_';
    $cache_name = '_bola_standing_data_4';
    $_cache_filename_ = $temporary_folder.'klasemen/'.$cache_name;
    
    //$CURRENT_SEASON_YEAR = 2015;
    $CURRENT_SEASON_YEAR = "2016/2017";
    $league_name = 'indonesia';
    
    $array = array(4 => 'indonesia');
    $array_title = array(4 => 'Indonesia Super League');
    $inggris = '';

    $menu_top = '
        <div class="topmenu">
            <a href="' . $schedule_url . 'score.html">Skor Terkini</a>
            &nbsp;&nbsp;&nbsp;
            <a href="' . $schedule_url . '">Jadwal Pertandingan</a>
            &nbsp;&nbsp;&nbsp;
            <a href="' . $tv_jadwal_url . '">Jadwal TV</a>
            &nbsp;&nbsp;
            <a href="' . $schedule_url . 'klasemen_liga_inggris.html">Klasemen</a>
            &nbsp;&nbsp;
            <a href="' . $schedule_url . 'preview.html">Preview</a>
            &nbsp;&nbsp;
            <a href="' . $schedule_url . 'review.html">Review</a>
        </div>
    ';
    
    foreach ($array as $k => $v) {
        $standing_data = array();
        /*$share = '
            <!--SOCIALTAB-->
            <div id="bl-social-tabs"></div>
            <script type="text/javascript">
            $(document).ready(function() {
                $("#bl-social-tabs").klsocial({
                    url:"' . $schedule_url . 'klasemen_liga_' . $v . '.html",
                    comment_count:"-1"
                });
            });
            </script>
            <!--ENDSOCIALTAB-->
        ';*/

        $additional_text = '';
        switch ($k) {
            case 4: $infotext = '<div style="padding: 5px 0px 0px 0px;">Update terbaru Klasemen Liga Super Indonesia ' . $CURRENT_SEASON_YEAR . ' dari pertandingan ISL terakhir. Simak juga daftar <a href="#topskorman" class="bluelink">Top Skor</a> sementara.</div>';
                //$metatitle = 'Update Klasemen Liga Indonesia ISL ' . $CURRENT_SEASON_YEAR . ' Terbaru';
                //$metadesc = 'Update terbaru Klasemen Liga Super Indonesia ' . $CURRENT_SEASON_YEAR . ' dari Live Score pertandingan ISL terakhir disertai daftar Top Skor';
                $metatitle = 'Klasemen Liga Indonesia ISL - Update Terbaru';
                $metadesc = 'Klasemen Liga Indonesia ISL ' . $CURRENT_SEASON_YEAR . ', update terbaru Live Score pertandingan Liga Indonesia ISL terakhir, disertai daftar Top Skor';
                break;
            default: $infotext = '';
        }

        $recent_news = '
            <div class="greentitle" style="margin-bottom:10px; margin-top:10px; text-align:left; font-size:18px">
                BERITA TERBARU ' . (strtoupper($array_title[$k])) . '
            </div>
            <ul style="list-style:none; margin:0px; padding:0px">
        ';

        $q = $sqlDB->sql_query("
                    SELECT a.title, a.url, a.schedule, b.category_url FROM dbnews a, dbcategory b
                    WHERE a.category = b.category_id AND a.category = '" . ($k == 27 ? 4 : $k) . "' AND a.level != '0' AND a.schedule <= NOW()
                    ORDER BY a.schedule DESC LIMIT 10");
        while ($row = $sqlDB->sql_fetchrow($q)) {
            $recent_news .= '
                <li style="padding:5px 10px; border-bottom:1px dashed #aaa">
                    <span style="color:#666">(' . date("d-m-Y H:i", strtotime($row["schedule"])) . ')</span>
                    <a href="/' . $row["category_url"] . '/' . $row["url"] . '.html" class="greenlink">' . $row["title"] . '</a>
                </li>
            ';
        }
        
        $recent_news .= '
            </ul>
        ';

        $content = '
            <div class="bigcon">
                <div class="bigcon2">
                    <div class="nav">
                        <a href="/" style="text-decoration:none;">HOME</a> &raquo;
                        KLASEMEN ' . (strtoupper($array_title[$k])) . '
                    </div>
                    ' . $menu_top . '
                    <h1 class="greentitle" style="margin: 0px;">Klasemen ' . (ucwords($array_title[$k])) . '</h1>
                    ' . $infotext . ' 
                    <div class="jdskor1">Pilih Kompetisi: '.dropdown_klasemen_options($v).'</div>
                    <!--SKLAS-->
                    <div class="klasemen_row">
                        <div class="jdsmall1"><strong>No</strong></div>
                        <div class="jdwide1"><span class="mark ">&nbsp;</span><strong>Klub</strong></div>
                        <div class="jdsmall1"><strong>MN</strong></div>
                        <div class="jdsmall1"><strong>M</strong></div>
                        <div class="jdsmall1"><strong>S</strong></div>
                        <div class="jdsmall1"><strong>K</strong></div>
                        <div class="jdsmall1"><strong>MG</strong></div>
                        <div class="jdsmall1"><strong>KG</strong></div>
                        <div class="jdsmall1"><strong>SG</strong></div>
                        <div class="jdsmall1"><strong>Poin</strong></div>
                        <br class="clear" />
                    </div>
                    
                <!--SSTANDINGS-->
        ';

        $qs = "
            SELECT season_id FROM dbseason
            WHERE season_cat_id='$k' AND season_status='1' ORDER BY season_id DESC LIMIT 1";
        $rs = $sqlDB->sql_query($qs, true);
        $rows = $sqlDB->sql_fetchrow($rs);
        $season_id = $rows['season_id'];

        $q = "
            SELECT A.team_id, A.team_name FROM dbteam A, dbparticipant B
            WHERE part_season_id='$season_id' AND team_id=part_team_id AND B.part_status<>'0' ORDER BY team_name";
        $r = $sqlDB->sql_query($q, true);
        if (!$r) {
            return false;
        }
        
        $tmp_file = LOGDIR . 'klasemen_tmp_file.txt';
        $tmp_file2 = LOGDIR . 'klasemen_tmp_file2.txt';
        if (is_file($tmp_file)) {
            unlink($tmp_file);
        }
        if (is_file($tmp_file2)) {
            unlink($tmp_file2);
        }
        $save_arr = array();
        while ($row = $sqlDB->sql_fetchrow($r)) {
            $club_data = get_schedule_club_data($sqlDB, $row['team_name'], $season_id);
            $ab = $club_data['home_goal'] - $club_data['away_goal'];
            
            file_put_contents($tmp_file, $row['team_id'] . "\t" . $club_data['point'] . "\t" . $ab . "\t" . $club_data['home_goal'] . "\t" . $row['team_name'] . "\n", FILE_APPEND);
            $save_arr[$row['team_id']] = $club_data;
        }
        exec('cat ' . $tmp_file . ' | sort -k2.1nr -k3.1nr -k4.1nr -k5 > ' . $tmp_file2, $out);
        $sqlDB->sql_freeresult($r);
        $sqlDB->sql_freeresult($rs);

        $css = 2;
        $counter = 1;
        $arr_data = array_filter(array_map('trim', file($tmp_file2)));

        // spesial untuk member page
        $member_page_file = LOGDIR . 'member_page_' . $v . '.txt';
        if (is_file($member_page_file)) {
            file_put_contents($member_page_file, "");
        }

        foreach ($arr_data as $vdata) {
            list($team_id, $team_point, $team_selisih, $away_goal, $team_name) = explode("\t", $vdata);
            $xdata = $save_arr[$team_id];
            $team_name = trim($team_name);
            
            $q2 = "SELECT url FROM dbarticles WHERE team_id='$team_id' AND category='16' LIMIT 1";
            $r2 = $sqlDB->sql_query($q2);
            $row2 = $sqlDB->sql_fetchrow($r2);
            
            $_standing_club_url_ = '';
            if ($row2['url']) {
                $_standing_club_url_ = $row2['url'];
            }
            
            $mark = '';
            if (isset($standing_data_latest[$team_name])) {
                $_team_last_ = $standing_data_latest[$team_name];
                
                $mark = $_team_last_['mark'];
                if ($_team_last_['no'] != $counter) {
                    if ($_team_last_['no'] > $counter) {
                        $mark = 'up';
                    }
                    if ($_team_last_['no'] < $counter) {
                        $mark = 'down';
                    }
                } else {
                    if ($_team_last_['played'] != $xdata['played']) {
                        $mark = '';
                    }
                }
            }
            
            $css_row = 'rank ';
            /*if($STANDING_STATUS)
            {
                $css_row .= isset($STANDING_STATUS[$counter])?$STANDING_STATUS[$counter]:'';
            }*/
            
            $_gd_ = $xdata['home_goal'] - $xdata['away_goal'];
            if ($_gd_ > 0) {
                $_gd_ = '+'.$_gd_;
            } elseif ($_gd_ > 0) {
                $_gd_ = '-'.$_gd_;
            }
            $row2['url'] = isset($row2['url']) ? "/club/".$row2['url'].".html" : '';
            $content .= '
                <div class="klasemen_row">
                    <div class="jdsmall' . $css . '"><span class="'.$css_row.'">' . $counter . '</span></div>
                    <div class="jdwide' . $css . '"><span class="mark '.$mark.'">&nbsp;</span><strong><a href="' . $row2['url'] . '" class="greenlink">'.trim($team_name) . '</a></strong></div>
                    <div class="jdsmall' . $css . '">' . $xdata['played'] . '</div>
                    <div class="jdsmall' . $css . '">' . $xdata['win'] . '</div>
                    <div class="jdsmall' . $css . '">' . $xdata['draw'] . '</div>
                    <div class="jdsmall' . $css . '">' . $xdata['loose'] . '</div>
                    <div class="jdsmall' . $css . '">' . $xdata['home_goal'] . '</div>
                    <div class="jdsmall' . $css . '">' . $xdata['away_goal'] . '</div>
                    <div class="jdsmall' . $css . '">' . $_gd_ . '</div>
                    <div class="jdsmall' . $css . '"><strong>' . $xdata['point'] . '</strong></div>
                    <br class="clear" />
                </div>
            ';

            file_put_contents($member_page_file, $counter . "\t" . trim($team_name) . "\t" . $xdata['played'] . "\t" . $xdata['point'] . "\n", FILE_APPEND);

            if ($counter == 3) {
                $content .= '<!--EKLAE-->';
            }

            $css = $css == 1 ? 2 : 1;
            $zone_start = '';
            $zone_end = '';
            $deduction_point = '0';
            if ($team_id) {
                $klasement_query = "REPLACE INTO dbklasemen (`rank`, `last_rank`, `zone_start`, `zone_end`, `team_id`, `club_name`, `session_id`, `matches_total`, `matches_won`, `matches_draw`, `matches_lost`, `goals_pro`, `goals_against`, `points`, `deduction_points`, `league`, `last_update_time`) VALUES (
        '$counter','$counter','$zone_start','$zone_end','$team_id','$team_name','$season_id','" . $xdata['played'] . "','" . $xdata['win'] . "','" . $xdata['draw'] . "','" . $xdata['loose'] . "','" . $xdata['home_goal'] . "','" . $xdata['away_goal'] . "','" . $xdata['point'] . "','$deduction_point','$v',NOW()
        )";
                $sqlDB->sql_query($klasement_query);
            }
            
            $standing_data[] = array(
                'no' => $counter,
                'club' => $team_name,
                'club_url' => $_standing_club_url_,
                'played' => $xdata['played'],
                'win' => $xdata['win'],
                'draw' => $xdata['draw'],
                'lost' => $xdata['loose'],
                'home_goal' => $xdata['home_goal'],
                'away_goal' => $xdata['away_goal'],
                'point' => $xdata['point'],
                'mark' => $mark,
                'css_rank' => $css_row
            );
            
            $counter++;
        }
        
        //cache
        /*if($standing_data)
        {
            $memcache_obj->set($cache_name, serialize($standing_data), false, 7 * 24 * 3600);
            file_put_contents($_cache_filename_, serialize($standing_data));
        }*/
        //echo $cache_name . $k;
        
        if ($standing_data) {
            $memcache_obj = new Memcache;
            bola_memcached_connect($memcache_obj);
            $memcache_obj->set($cache_name, serialize($standing_data), false, 7 * 24 * 3600);
            write_file_direct(TEMPDIR.'klasemen/_bola_standing_data_4', serialize($standing_data));
            $memcache_obj->close();
        }
        
        $topscore = get_ten_topscore($league_name);

        $additional_text_html = '';
        if ($additional_text) {
            $additional_text_html = '<p><strong>Keterangan</strong><br/>'.$additional_text.'</p>';
        }
        $content .= '
                    <!--ESTANDINGE-->    
                    '.$additional_text_html.'
                    <!--<div class="klasemen_note">
                        <p><span class="note_color champion">&nbsp;</span><span class="note_text">UEFA Champions League</span></p>
                        <p><span class="note_color champion_qual">&nbsp;</span><span class="note_text">Champions League Qualifier</span></p>
                        <p><span class="note_color euro">&nbsp;</span><span class="note_text">UEFA Europa League</span></p>
                        <p style="margin: 0px; width: 90px;"><span class="note_color relegation">&nbsp;</span><span class="note_text" style="width: 65px;">Relegation</span></p>
                        <br class="clear"/>
                    </div>-->
                    <br/>' . $topscore . '
                    <br/>' . $recent_news . '
                </div>
            </div>
        ';

        $filename = $schedule_dir . 'klasemen_liga_' . $v . '.html';
        $fileurl = $schedule_url . 'klasemen_liga_' . $v . '.html';

        $metakey = explode(' ', trim(strtolower($metadesc)));
        $metakey = array_unique(array_filter(array_map('trim', $metakey)));
        $metakey = array_slice($metakey, 0, 50);
        $metakey = implode(',', $metakey);
        write_file($filename, $content, $metatitle, $metakey, $metadesc, '', true, 'full', 5);
        insert_property_og($filename, 'Indonesia Super League 2015', $fileurl, '');
        
        echo generated_link($fileurl);
    }
    $memcache_obj->close();
}

function generate_schedule_topscore_isl($sqlDB, $echo = true)
{
    global $schedule_url, $schedule_dir, $tv_url, $tv_jadwal_url, $profile_dir, $profile_url;

    $k = 4;
    $v = 'indonesia';
    $league_title = 'Indonesia Super League';

    $menu_top = '
        <div class="topmenu">
            <a href="' . $schedule_url . 'score.html">Skor Terkini</a>
            &nbsp;&nbsp;&nbsp;
            <a href="' . $schedule_url . '">Jadwal Pertandingan</a>
            &nbsp;&nbsp;&nbsp;
            <a href="' . $tv_jadwal_url . '">Jadwal TV</a>
            &nbsp;&nbsp;
            <a href="' . $schedule_url . 'klasemen_liga_inggris.html">Klasemen</a>
            &nbsp;&nbsp;
            <a href="' . $schedule_url . 'preview.html">Preview</a>
            &nbsp;&nbsp;
            <a href="' . $schedule_url . 'review.html">Review</a>
        </div>
    ';
    $menu_top .= '
        <div class="topmenu">
            <a href="'.$schedule_url.'topskor_liga_inggris.html">Liga Inggris</a>
            &nbsp;&nbsp;&nbsp;
            <a href="'.$schedule_url.'topskor_liga_italia.html">Liga Italia</a>
            &nbsp;&nbsp;&nbsp;
            <a href="'.$schedule_url.'topskor_liga_spanyol.html">Liga Spanyol</a>
            &nbsp;&nbsp;
            <a href="'.$schedule_url.'topskor_liga_indonesia.html">ISL</a>
            &nbsp;&nbsp;
            <a href="'.$schedule_url.'topskor_liga_champions.html">Liga Champions</a>
        </div>
    ';
    $content = '
        <div class="bigcon">
            <div class="bigcon2">
                <div class="nav">
                    <a href="/" style="text-decoration:none;">HOME</a> &raquo;
                    TOP SKOR ' . (strtoupper($league_title)) . '
                </div>
                ' . $menu_top . '
                <br/>
                <div class="greentitle">Top Skor ' . (ucwords($league_title)) . '</div>
                <br/>
    ';

    //indonesia season ID
    //$SEASON_ID = 45; //season isl 2014
    $SEASON_ID = 51;

    $tmp_file = BOLADIR . 'topscore_isl_tmp_file.txt';
    $tmp_file2 = BOLADIR . 'topscore_isl_tmp_file2.txt';
    if (is_file($tmp_file)) {
        unlink($tmp_file);
    }
    if (is_file($tmp_file2)) {
        unlink($tmp_file2);
    }

    $q = "(SELECT CONCAT(home, home2) as team_name FROM dbschedule
                WHERE country='Indonesia' AND idseason='$SEASON_ID' AND level<>'0')
                UNION
                (SELECT CONCAT(away, away2) as team_name FROM dbschedule
                WHERE country='Indonesia' AND idseason='$SEASON_ID' AND level<>'0')
                ";
//    echo $q.'<br/>';
    $r = $sqlDB->sql_query($q, true) or die(__LINE__ . ' = ' . mysql_error());
    while ($row = $sqlDB->sql_fetchrow($r)) {
        $qsc = "SELECT * FROM dbschedule WHERE (home='$row[team_name]' OR home2='$row[team_name]') AND idseason='$SEASON_ID' AND level='2' AND schedule<>'0000-00-00 00:00:00'";
        $rsc = $sqlDB->sql_query($qsc, true) or die(__LINE__ . ' = ' . mysql_error());
        
        while ($rowsc = $sqlDB->sql_fetchrow($rsc)) {
            $qd = "SELECT * FROM dbscore WHERE id_schedule='$rowsc[id]' AND info='goal' AND level='1'";
            
            $rd = $sqlDB->sql_query($qd, true) or die(__LINE__ . ' = ' . mysql_error());
            while ($rowd = $sqlDB->sql_fetchrow($rd)) {
                $is_my_team = '';

               
                $rowd['player_name'] = kln_real_escape_string($rowd['player_name']);
                
                 /* get player club from player_profile (case midseason player transfer) [2013-08-27] */
                $qplayerclub = "SELECT B.team_name as player_club FROM player_profile A JOIN dbteam B ON A.player_club = B.team_id WHERE (player_name='{$rowd['player_name']}' OR player_fullname='{$rowd['player_name']}') AND player_status='1' LIMIT 1";
                $rplayerclub = $sqlDB->sql_query($qplayerclub, true) or die(__LINE__ . ' = ' . mysql_error());
                while ($rowplayerclub = $sqlDB->sql_fetchrow($rplayerclub)) {
                    $is_my_team = (isset($rowplayerclub['player_club']) && $rowplayerclub['player_club']) ? str_replace(' ', '_', $rowplayerclub['player_club']) : '';
                }
                /* end of midseason transfer */
                
                $is_my_team = '';
                if (!$is_my_team) {
                    $is_my_team = ($rowd['home'] == 1) ? $rowsc['home'] . $rowsc['home2'] : $rowsc['away'] . $rowsc['away2'];
                }
                file_put_contents($tmp_file, str_replace(' ', '_', $rowd['player_name']) . "\t" . str_replace(' ', '_', $is_my_team) . "\n", FILE_APPEND);
            }
            $sqlDB->sql_freeresult($rd);
        }
        $sqlDB->sql_freeresult($rsc);
    }
    $sqlDB->sql_freeresult($r);

    exec('cat ' . $tmp_file . ' | awk \'{data[$1]+=1;kelub[$1]=$2;} END {for (x in data) {print x "\t" data[x] "\t" kelub[x]}}\' | sort -k2nr > ' . $tmp_file2);
    $data = array_filter(array_map('trim', file($tmp_file2)));
    $count = 2;
    $counter = 1;
    $content .= '
        <div class="jdtop1"><strong>No</strong></div>
        <div class="jdtopa1"><strong>Nama</strong></div>
        <div class="jdtop1"><strong>Goal</strong></div>
        <div class="jdtopa1"><strong>Klub</strong></div>
        <div class="clear"></div>
    ';
    
    $topscore_data = array();
    $cache_topscore = '';
    foreach ($data as $vdata) {
        list($player, $goal, $team) = explode("\t", $vdata);
        $player = str_replace('_', ' ', $player);
        $team = str_replace('_', ' ', $team);

        $qp = "SELECT player_url FROM player_profile WHERE player_name='" . kln_real_escape_string($player) . "' LIMIT 1";
        $rp = $sqlDB->sql_query($qp, true) or die(__LINE__ . ' = ' . mysql_error());
        $rowp = $sqlDB->sql_fetchrow($rp);
        
        $_player_url_ = $profile_url;
        if (isset($rowp['player_url']) && $rowp['player_url'] && is_file($profile_dir.$rowp['player_url'].'/index.html')) {
            $_player_url_ = $profile_url.$rowp['player_url'].'/';
        }
        
        $content .= '
            <div class="jdtop' . $count . '">' . $counter . '</div>
            <div class="jdtopa' . $count . '"><a href="/profile/' . $rowp['player_url'] . '/" class="greenlink">' . stripslashes($player) . '</a></div>
            <div class="jdtop' . $count . '">' . $goal . '</div>
            <div class="jdtopa' . $count . '">' . $team . '</div>
            <div class="clear"></div>
        ';
        $cache_topscore .= stripslashes($player) . "\t$goal\t$team\t" . $rowp['player_url'] . "\n";
        
        $count = $count == 1 ? 2 : 1;
        $counter++;

        $sqlDB->sql_freeresult($rp);
        
        if ($counter <= 25) {
            $topscore_data[] = array(
                'rank' => $counter,
                'player' => stripslashes($player),
                'player_url' => $_player_url_,
                'team' => $team,
                'goal' => $goal,
            );
        }
    }
    if ($topscore_data) {
        $memcache = new Memcache;
        bola_memcached_connect($memcache);
        $memcache->set('bolanet_topscore_data_'.$k, serialize($topscore_data), false, 24*3600);
        write_file_direct(TEMPDIR.'topscore/bolanet_topscore_data_'.$k, serialize($topscore_data));
        $memcache->close();
    }
    
    file_put_contents(CACHEDIR . 'cache_topscore_' . md5($v), $cache_topscore);
    file_put_contents(CACHEDIR . 'xcache_topscore_' . md5($v), $cache_topscore);

    $content .= '
                <br/>
            </div>
        </div>
    ';

    $filename = $schedule_dir . 'topskor_liga_' . $v . '.html';
    $fileurl = $schedule_url . 'topskor_liga_' . $v . '.html';

    write_file($filename, $content, 'Top Skor Liga Indonesia', 'Top Skor Klasemen Liga Inggris, Top Skor Liga Italia, Top Skor Liga Spanyol', 'Top Skor Klasemen Liga Inggris, Top Skor Liga Italia, Top Skor Liga Spanyol', '', true, 'full', 5);
    
    if ($echo) {
        echo generated_link($fileurl);
    }
}

/**
 * get league related news by tag
 */
function get_related_news_league_bytag($sqlDB, $tag_id)
{
    $return = array();
    $sql = "
        SELECT A.schedule, A.title,A.url,B.category_url
        FROM
            dbtags_content A
        LEFT JOIN
            dbcategory B
        ON
            B.category_id=A.category
        WHERE
            A.tags_id='$tag_id'
            AND A.schedule <NOW()
            AND A.schedule <> '00-00-0000 00:00:00'
            AND A.level>0
        ORDER BY A.schedule DESC
        LIMIT 0,10
    ";
    $res = $sqlDB->sql_query($sql);
    $return = $sqlDB->sql_fetchrowset($res);
    $sqlDB->sql_freeresult();
    return $return;
}




function generate_schedule_klasemen_piala_aff2014($sqlDB)
{
    global $schedule_url, $schedule_dir, $tv_url, $tv_jadwal_url;

    return '';
    
    //generate topscore first
    generate_schedule_topscore_piala_aff2014($sqlDB);

    $CURRENT_SEASON_YEAR = 2014;
    $k = 5;
    $v = 'piala_aff';
    $league_title = 'Piala AFF 2014';
    $array_champions = range('A', 'B');
    $db_title = 'AFF 2014 Grup';
    
    $menu_top = '
        <div class="topmenu">
            <a href="' . $schedule_url . 'score.html">Skor Terkini</a>
            &nbsp;&nbsp;&nbsp;
            <a href="' . $schedule_url . '">Jadwal Pertandingan</a>
            &nbsp;&nbsp;&nbsp;
            <a href="' . $tv_jadwal_url . '">Jadwal TV</a>
            &nbsp;&nbsp;
            <a href="' . $schedule_url . 'klasemen_liga_inggris.html">Klasemen</a>
            &nbsp;&nbsp;
            <a href="' . $schedule_url . 'preview.html">Preview</a>
            &nbsp;&nbsp;
            <a href="' . $schedule_url . 'review.html">Review</a>
        </div>
    ';

    $recent_news = '
        <div class="greentitle" style="margin-bottom:10px; margin-top:10px; text-align:left; font-size:18px">
            BERITA TERBARU ' . (strtoupper($league_title)) . '
        </div>
        <ul style="list-style:none; margin:0px; padding:0px">
    ';

    $recent_news_arr = get_related_news_league_bytag($sqlDB, '6739');
    foreach ($recent_news_arr as $row) {
        $recent_news .= '
            <li style="padding:5px 10px; border-bottom:1px dashed #aaa">
                <span style="color:#666">(' . date("d-m-Y H:i", strtotime($row["schedule"])) . ')</span>
                <a href="/' . $row["category_url"] . '/' . $row["url"] . '.html" class="greenlink">' . $row["title"] . '</a>
            </li>
        ';
    }

    $recent_news .= '
        </ul>
    ';

    /*$share = '
        <!--SOCIALTAB-->
            <div id="bl-social-tabs"></div>
            <script type="text/javascript">
                $(document).ready(function() {
                                    $("#bl-social-tabs").klsocial({
                                            url:"' . $schedule_url . 'klasemen_'.$v.'html",
                                            comment_count:"-1"
                                    });
                });
            </script>
        <!--ENDSOCIALTAB-->
    ';*/

    $content = '
        <div class="bigcon">
            <div class="bigcon2">
                <div class="nav">
                    <a href="/" style="text-decoration:none;">HOME</a> &raquo;
                    KLASEMEN ' . (strtoupper($league_title)) . '
                </div>
                ' . $menu_top . '
                <h1 class="greentitle" style="margin: 0px;">Klasemen ' . (ucwords($league_title)) . '</h1>
                <div style="padding: 5px 0px 0px 0px;">Update terbaru ' . (ucwords($league_title)) . ' dari pertandingan ' . (ucwords($league_title)) . '.</div>
                <div class="jdskor1">
                                    Pilih Kompetisi: '.dropdown_klasemen_options($v).'
                </div>
                <!--SKLAS-->
    ';

    $season_id = 0;
    $standing_data = array();

    $unique_random_id = 0;
    foreach ($array_champions as $ac) {
        $q = "
                SELECT home, home2, away, away2 FROM dbschedule
                WHERE country='Vietnam' AND title='$db_title $ac' AND level<>'0'";
        #echo "$q<br/>";
        $r = $sqlDB->sql_query($q, true);
        if (!$r) {
            return false;
        }

        $theclub = array();
        while ($row = $sqlDB->sql_fetchrow($r)) {
            if (!in_array($row['home'], $theclub)) {
                array_push($theclub, $row['home']);
            }
            if (!in_array($row['home2'], $theclub)) {
                array_push($theclub, $row['home2']);
            }
            if (!in_array($row['away'], $theclub)) {
                array_push($theclub, $row['away']);
            }
            if (!in_array($row['away2'], $theclub)) {
                array_push($theclub, $row['away2']);
            }
        }
        $sqlDB->sql_freeresult($r);

        $theclub = array_unique(array_filter(array_map('trim', $theclub)));



        $content .= '<h1>Klasemen Grup ' . $ac . '</h1><div class="jdsmall1"><strong>No</strong></div>
        <div class="jdwide1"><strong>Klub</strong></div>
        <div class="jdsmall1"><strong>Main</strong></div>
        <div class="jdsmall1"><strong>M</strong></div>
        <div class="jdsmall1"><strong>S</strong></div>
        <div class="jdsmall1"><strong>K</strong></div>
        <div class="jdsmallg1"><strong>SG</strong></div>
        <div class="jdsmall1"><strong>Poin</strong></div>
        <br class="clear" />';

        $tmp_file = LOGDIR . 'klasemen_'.$v.'_tmp_file.txt';
        $tmp_file2 = LOGDIR . 'klasemen_'.$v.'_tmp_file2.txt';
        if (is_file($tmp_file)) {
            unlink($tmp_file);
        }
        if (is_file($tmp_file2)) {
            unlink($tmp_file2);
        }
        $save_arr = array();

        $unique_team_id = 1;
        $tmp_point = $is_headtohead = array();
        foreach ($theclub as $detailclub) {
            $q3 = "SELECT team_id, team_name FROM dbteam WHERE team_name='$detailclub' LIMIT 1";
            $r3 = $sqlDB->sql_query($q3);
            if ($row3 = $sqlDB->sql_fetchrow($r3)) {
            } else {
                $row3['team_id'] = '-' . $unique_team_id++;
            }

            $club_data = get_schedule_club_data($sqlDB, $detailclub, 0, "$db_title $ac");
            $ab = $club_data['home_goal'] - $club_data['away_goal'];

            file_put_contents($tmp_file, $row3['team_id'] . "\t" . $club_data['point'] . "\t" . $ab . "\t" . $club_data['home_goal'] . "\t" . $detailclub . "\n", FILE_APPEND);
            $save_arr[$row3['team_id']] = $club_data;

            if (!array_key_exists($club_data['point'], $tmp_point)) {
                $tmp_point[$club_data['point']] = $detailclub;
            } else {
                if (!array_key_exists($club_data['point'], $is_headtohead)) {
                    $is_headtohead[$club_data['point']] = array($detailclub, $tmp_point[$club_data['point']]);
                }
            }
        }

        exec('cat ' . $tmp_file . ' | sort -k2.1nr -k3.1nr -k4.1nr -k5 > ' . $tmp_file2, $out);
        $sqlDB->sql_freeresult($r);

        $css = 2;
        $counter = 1;
        $arr_data = array_filter(array_map('trim', file($tmp_file2)));

        if (count($is_headtohead) > 0) {
            $is_headtohead = array_shift($is_headtohead);

            $club1 = $is_headtohead[0];
            $club2 = $is_headtohead[1];
            $headtoheadwinner = generate_schedule_headtohead($sqlDB, $season_id, $club1, $club2);

            $club1_pos = -1;
            $club2_pos = -1;
            $league_pos = 0;
            foreach ($arr_data as $vdata) {
                list($team_id, $team_point, $team_selisih, $away_goal, $team_name) = explode("\t", $vdata);
                if ($team_name == $club1) {
                    $club1_pos = $league_pos;
                }
                if ($team_name == $club2) {
                    $club2_pos = $league_pos;
                }
                $league_pos++;
            }
            reset($arr_data);

            if ($headtoheadwinner == 1 && $club1_pos > $club2_pos) {
                $switch_tmp = $arr_data[$club1_pos];
                $arr_data[$club1_pos] = $arr_data[$club2_pos];
                $arr_data[$club2_pos] = $switch_tmp;
            } elseif ($headtoheadwinner == 2 && $club1_pos < $club2_pos) {
                $switch_tmp = $arr_data[$club1_pos];
                $arr_data[$club1_pos] = $arr_data[$club2_pos];
                $arr_data[$club2_pos] = $switch_tmp;
            }
        }

        $standing_data[$ac] = array();
        foreach ($arr_data as $vdata) {
            list($team_id, $team_point, $team_selisih, $away_goal, $team_name) = explode("\t", $vdata);
            $xdata = $save_arr[$team_id];

            $q2 = "SELECT url FROM dbarticles WHERE team_id='$team_id' AND category='16' LIMIT 1";
            $r2 = $sqlDB->sql_query($q2);
            if ($row2 = $sqlDB->sql_fetchrow($r2)) {
                $clubname = '<a href="/club/' . $row2['url'] . '.html" class="greenlink">' . trim($team_name) . '</a>';
            } else {
                $clubname = trim($team_name);
            }

            $content .= '
                <div class="jdsmall' . $css . '">' . $counter . '</div>
                <div class="jdwide' . $css . '">' . $clubname . '</div>
                <div class="jdsmall' . $css . '">' . $xdata['played'] . '</div>
                <div class="jdsmall' . $css . '">' . $xdata['win'] . '</div>
                <div class="jdsmall' . $css . '">' . $xdata['draw'] . '</div>
                <div class="jdsmall' . $css . '">' . $xdata['loose'] . '</div>
                <div class="jdsmallg' . $css . '">' . $xdata['home_goal'] . ' - ' . $xdata['away_goal'] . '</div>
                <div class="jdsmall' . $css . '">' . $xdata['point'] . '</div>
                <br class="clear" />
            ';
            $standing_data[$ac][] = array(
                'no' => $counter,
                'club' => trim($team_name),
                'played' => $xdata['played'],
                'win' => $xdata['win'],
                'draw' => $xdata['draw'],
                'lost' => $xdata['loose'],
                'home_goal' => $xdata['home_goal'],
                'away_goal' => $xdata['away_goal'],
                'point' => $xdata['point'],
            );

            $zone_start = '';
            $zone_end = '';
            $deduction_point = '0';
            $team_id = $unique_random_id . '0900' . $team_id;
            
            $unique_random_id++;

            $counter++;
            $css = $css == 1 ? 2 : 1;
        }
    }

    if ($standing_data) {
        $memcache_obj = new Memcache;
        bola_memcached_connect($memcache_obj);
        $memcache_obj->set('bolanet_standing_'.$v, serialize($standing_data), false, 7 * 24 * 3600);
        write_file_direct(TEMPDIR.'klasemen/bolanet_standing_'.$v, serialize($standing_data));
        $memcache_obj->close();
    }

    $topscore = get_ten_topscore($v);
    $topscore = str_replace('topskor_liga_'.$v.'.html', 'topskor_'.$v.'.html', $topscore);
    $content .= '
                <br/><br/>' . $topscore . '
                <br/>' . $recent_news . '
            </div>
        </div>
    ';

    $filename = $schedule_dir . 'klasemen_' . $v . '.html';
    $fileurl = $schedule_url . 'klasemen_' . $v . '.html';

    $metatitle = 'Update Klasemen '.$league_title.' ' . $CURRENT_SEASON_YEAR . ' Terbaru';
    $metadesc = 'Update terbaru Klasemen '.$league_title.' ' . $CURRENT_SEASON_YEAR . ' dari Live Score pertandingan '.$league_title.' terakhir disertai daftar Top Skor';
    $metakey = explode(' ', trim(strtolower($metadesc)));
    $metakey = array_unique(array_filter(array_map('trim', $metakey)));
    $metakey = array_slice($metakey, 0, 50);
    $metakey = implode(',', $metakey);

    write_file($filename, $content, $metatitle, $metakey, $metadesc, '', true, 'full', 5);
    insert_property_og($filename, 'Daftar Klasemen '.$league_title, $fileurl, '');
    echo generated_link($fileurl);
}



function generate_schedule_topscore_piala_aff2014($sqlDB)
{
    global $schedule_url, $schedule_dir, $tv_url, $tv_jadwal_url;
    
    return '';
    
    $v = 'piala_aff';
    $league_title = 'Piala AFF 2014';
    $db_title = 'AFF 2014';
    $db_country = 'Vietnam';
    
    $menu_top = '
        <div class="topmenu">
            <a href="' . $schedule_url . 'score.html">Skor Terkini</a>
            &nbsp;&nbsp;&nbsp;
            <a href="' . $schedule_url . '">Jadwal Pertandingan</a>
            &nbsp;&nbsp;&nbsp;
            <a href="' . $tv_jadwal_url . '">Jadwal TV</a>
            &nbsp;&nbsp;
            <a href="' . $schedule_url . 'klasemen_liga_inggris.html">Klasemen</a>
            &nbsp;&nbsp;
            <a href="' . $schedule_url . 'preview.html">Preview</a>
            &nbsp;&nbsp;
            <a href="' . $schedule_url . 'review.html">Review</a>
        </div>
    ';

    $content = '
        <div class="bigcon">
            <div class="bigcon2">
                <div class="nav">
                    <a href="/" style="text-decoration:none;">HOME</a> &raquo;
                    TOP SKOR ' . (strtoupper($league_title)) . '
                </div>
                ' . $menu_top . '
                <br/>
                <div class="greentitle">Top Skor ' . (ucwords($league_title)) . '</div>
                <br/>
    ';

    $tmp_file = BOLADIR . 'topscore_tmp_file.txt';
    $tmp_file2 = BOLADIR . 'topscore_tmp_file2.txt';
    if (is_file($tmp_file)) {
        unlink($tmp_file);
    }
    if (is_file($tmp_file2)) {
        unlink($tmp_file2);
    }

    $q = "(SELECT CONCAT(home, home2) as team_name FROM dbschedule
                WHERE title LIKE '$db_title%' AND level<>'0')
                UNION
                (SELECT CONCAT(away, away2) as team_name FROM dbschedule
                WHERE title LIKE '$db_title%' AND level<>'0')
                ";
    $r = $sqlDB->sql_query($q, true) or die(__LINE__ . ' = ' . mysql_error());
    while ($row = $sqlDB->sql_fetchrow($r)) {
        $qsc = "SELECT * FROM dbschedule WHERE (home='$row[team_name]' OR home2='$row[team_name]') AND title LIKE '$db_title%' AND country LIKE '$db_country' AND level='2' AND schedule<>'0000-00-00 00:00:00'";
        $rsc = $sqlDB->sql_query($qsc, true) or die(__LINE__ . ' = ' . mysql_error());

        while ($rowsc = $sqlDB->sql_fetchrow($rsc)) {
            $qd = "SELECT * FROM dbscore WHERE id_schedule='$rowsc[id]' AND info='goal' AND level='1'";
            $rd = $sqlDB->sql_query($qd, true) or die(__LINE__ . ' = ' . mysql_error());
            while ($rowd = $sqlDB->sql_fetchrow($rd)) {
                $is_my_team = '';

                /* get player club from player_profile (case midseason player transfer) [2013-08-27] */
                $rowd['player_name'] = kln_real_escape_string($rowd['player_name']);
                //$qplayerclub = "SELECT player_club FROM player_profile WHERE (player_name='{$rowd['player_name']}' OR player_fullname='{$rowd['player_name']}') AND player_status='1' LIMIT 1";
                $qplayerclub = "SELECT B.team_name as player_club FROM player_profile A JOIN dbteam B ON A.player_nationality = B.team_id WHERE (player_name='{$rowd['player_name']}' OR player_fullname='{$rowd['player_name']}') AND player_status='1' LIMIT 1";
                $rplayerclub = $sqlDB->sql_query($qplayerclub, true) or die(__LINE__ . ' = ' . mysql_error());
                while ($rowplayerclub = $sqlDB->sql_fetchrow($rplayerclub)) {
                    $is_my_team = (isset($rowplayerclub['player_club']) && $rowplayerclub['player_club']) ? str_replace(' ', '_', $rowplayerclub['player_club']) : '';
                }
                /* end of midseason transfer */

                if (!$is_my_team) {
                    $is_my_team = ($rowd['home'] == 1) ? $rowsc['home'] . $rowsc['home2'] : $rowsc['away'] . $rowsc['away2'];
                }
                file_put_contents($tmp_file, str_replace(' ', '_', $rowd['player_name']) . "\t" . str_replace(' ', '_', $is_my_team) . "\n", FILE_APPEND);
            }
            $sqlDB->sql_freeresult($rd);
        }
        $sqlDB->sql_freeresult($rsc);
    }
    $sqlDB->sql_freeresult($r);

    exec('cat ' . $tmp_file . ' | awk \'{data[$1]+=1;kelub[$1]=$2;} END {for (x in data) {print x "\t" data[x] "\t" kelub[x]}}\' | sort -k2nr > ' . $tmp_file2);
    $data = array_filter(array_map('trim', file($tmp_file2)));
    $count = 2;
    $counter = 1;
    $content .= '
        <div class="jdtop1"><strong>No</strong></div>
        <div class="jdtopa1"><strong>Nama</strong></div>
        <div class="jdtop1"><strong>Goal</strong></div>
        <div class="jdtopa1"><strong>Klub</strong></div>
        <div class="clear"></div>
    ';
    
    $topscore_data = array();
    $cache_topscore = '';
    foreach ($data as $vdata) {
        list($player, $goal, $team) = explode("\t", $vdata);
        $player = str_replace('_', ' ', $player);
        $team = str_replace('_', ' ', $team);

        $qp = "SELECT player_url FROM player_profile WHERE player_name='" . kln_real_escape_string($player) . "' LIMIT 1";
        $rp = $sqlDB->sql_query($qp, true) or die(__LINE__ . ' = ' . mysql_error());
        $rowp = $sqlDB->sql_fetchrow($rp);

        $content .= '
            <div class="jdtop' . $count . '">' . $counter . '</div>
            <div class="jdtopa' . $count . '"><a href="/profile/' . $rowp['player_url'] . '/" class="greenlink">' . stripslashes($player) . '</a></div>
            <div class="jdtop' . $count . '">' . $goal . '</div>
            <div class="jdtopa' . $count . '">' . $team . '</div>
            <div class="clear"></div>
        ';
        $cache_topscore .= stripslashes($player) . "\t$goal\t$team\t" . $rowp['player_url'] . "\n";

        $count = $count == 1 ? 2 : 1;
        $counter++;

        $sqlDB->sql_freeresult($rp);
        
        $topscore_item = array();
        $topscore_item['player_name'] = $player;
        $topscore_item['player_club'] = $team;
        $topscore_item['total_goal'] = $goal;
        $topscore_data[] = $topscore_item;
    }
    file_put_contents(CACHEDIR . 'cache_topscore_' . md5($v), $cache_topscore);
    file_put_contents(CACHEDIR . 'xcache_topscore_' . md5($v), $cache_topscore);

    $content .= '
                <br/>
            </div>
        </div>
    ';

    $filename = $schedule_dir . 'topskor_' . $v . '.html';
    $fileurl = $schedule_url . 'topskor_' . $v . '.html';

    if ($topscore_data) {
        $memcache_obj = new Memcache;
        bola_memcached_connect($memcache_obj);
        $memcache_obj->set('bolanet_topskor_'.$v, serialize($topscore_data), false, 7 * 24 * 3600);
        write_file_direct(TEMPDIR.'topscore/bolanet_topskor_'.$v, serialize($topscore_data));
        $memcache_obj->close();
    }
    
    write_file($filename, $content, 'Top Skor Klasemen '.$league_title.'', 'Top Skor Klasemen Liga Inggris, Top Skor Liga Italia, Top Skor Liga Spanyol', 'Top Skor Klasemen Liga Inggris, Top Skor Liga Italia, Top Skor Liga Spanyol', '', true, 'full', 5);
    echo generated_link($fileurl);
}

function get_day_ind($date, $show_time = false, $conj = ' at ', $month_type = 'number', $separator = ' ')
{
    $days = array('ming', 'sen', 'sel', 'rab', 'kam', 'jum', 'sab');
    $months = array('januari', 'februari', 'maret', 'april', 'mei', 'juni', 'juli', 'agustus', 'september', 'oktober', 'november', 'desember');
    if ($month_type == 'number') {
        $months = array('01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12');
    }
    
    if ($show_time) {
        $date = explode("-", date('w-d-n-Y-H-i', strtotime($date)));
        return ucfirst($days[$date[0]]). ', '. $date[1]. $separator. ucfirst($months[$date[2]-1]). $separator. $date[3]. $conj . $date[4]. ':'. $date[5];
    }
    $date = explode("-", date('w-d-n-Y', strtotime($date)));
    return ucfirst($days[$date[0]]);
}

function dropdown_klasemen_options($v = '')
{
    $html = '
        <select name="liga" id="liga" onchange="javascript:document.location=this.value;">
            <option value="klasemen_liga_inggris.html" ' . ($v == 'inggris' ? 'selected="selected"' : '') . '>Liga Premier Inggris</option>
            <option value="klasemen_liga_italia.html" ' . ($v == 'italia' ? 'selected="selected"' : '') . '>Liga Italia Seri A</option>
            <option value="klasemen_liga_spanyol.html" ' . ($v == 'spanyol' ? 'selected="selected"' : '') . '>La Liga Spanyol</option>
            <option value="klasemen_liga_indonesia.html" ' . ($v == 'indonesia' ? 'selected="selected"' : '') . '>Indonesia Liga 1</option>
            <option value="klasemen_liga_champions.html"' . ($v == 'champions' ? 'selected="selected"' : '') . '>Liga Champions</option>
        </select>';
    return $html;
}

function dropdown_score_options($v = '')
{
    $html = '
        <select name="liga" id="liga" onchange="javascript:document.location=this.value;">
            <option value="score_liga_premier.html" ' . ($v == 'inggris' ? 'selected="selected"' : '') . '>Liga Premier Inggris</option>
            <option value="score_seri_a.html" ' . ($v == 'italia' ? 'selected="selected"' : '') . '>Liga Italia Seri A</option>
            <option value="score_la_liga.html" ' . ($v == 'spanyol' ? 'selected="selected"' : '') . '>La Liga Spanyol</option>
            <option value="score_indonesia.html" ' . ($v == 'indonesia' ? 'selected="selected"' : '') . '>Indonesia Liga 1</option>
            <option value="score_liga_champions.html"' . ($v == 'champions' ? 'selected="selected"' : '') . '>Liga Champions</option>
        </select>';
    return $html;
}

function generate_klasemen_copa_america($sqlDB)
{
    global $schedule_url, $schedule_dir, $tv_url, $tv_jadwal_url;
    
    $CURRENT_SEASON_YEAR = 2015;
    $k = 5;
    $v = 'copa_america';
    $league_title = 'Copa America 2015';
    $array_champions = range('A', 'C');
    $db_country = 'Copa America 2015';
    $db_title = 'Grup';
    
    //generate topscore first
    generate_schedule_topscore_global($sqlDB, $v, $league_title, $db_country);
    
    $menu_top = '
        <div class="topmenu">
            <a href="' . $schedule_url . 'score.html">Skor Terkini</a>
            &nbsp;&nbsp;&nbsp;
            <a href="' . $schedule_url . '">Jadwal Pertandingan</a>
            &nbsp;&nbsp;&nbsp;
            <a href="' . $tv_jadwal_url . '">Jadwal TV</a>
            &nbsp;&nbsp;
            <a href="' . $schedule_url . 'klasemen_liga_inggris.html">Klasemen</a>
            &nbsp;&nbsp;
            <a href="' . $schedule_url . 'preview.html">Preview</a>
            &nbsp;&nbsp;
            <a href="' . $schedule_url . 'review.html">Review</a>
        </div>
    ';

    $recent_news = '
        <div class="greentitle" style="margin-bottom:10px; margin-top:10px; text-align:left; font-size:18px">
            BERITA TERBARU ' . (strtoupper($league_title)) . '
        </div>
        <ul style="list-style:none; margin:0px; padding:0px">
    ';

    $recent_news_arr = get_related_news_league_bytag($sqlDB, '11245');

    foreach ($recent_news_arr as $row) {
        $recent_news .= '
            <li style="padding:5px 10px; border-bottom:1px dashed #aaa">
                <span style="color:#666">(' . date("d-m-Y H:i", strtotime($row["schedule"])) . ')</span>
                <a href="/' . $row["category_url"] . '/' . $row["url"] . '.html" class="greenlink">' . $row["title"] . '</a>
            </li>
        ';
    }

    $recent_news .= '
        </ul>
    ';

    /*$share = '
        <!--SOCIALTAB-->
            <div id="bl-social-tabs"></div>
            <script type="text/javascript">
                $(document).ready(function() {
                                    $("#bl-social-tabs").klsocial({
                                            url:"' . $schedule_url . 'klasemen_'.$v.'html",
                                            comment_count:"-1"
                                    });
                });
            </script>
        <!--ENDSOCIALTAB-->
    ';*/

    $content = '
        <div class="bigcon">
            <div class="bigcon2">
                <div class="nav">
                    <a href="/" style="text-decoration:none;">HOME</a> &raquo;
                    KLASEMEN ' . (strtoupper($league_title)) . '
                </div>
                ' . $menu_top . '
                <h1 class="greentitle" style="margin: 0px;">Klasemen ' . (ucwords($league_title)) . '</h1>
                <div style="padding: 5px 0px 0px 0px;">Update terbaru ' . (ucwords($league_title)) . ' dari pertandingan ' . (ucwords($league_title)) . '.</div>
                <div class="jdskor1">
                                    Pilih Kompetisi: '.dropdown_klasemen_options($v).'
                </div>
                <!--SKLAS-->
    ';

    $season_id = 0;
    $standing_data = array();

    $unique_random_id = 0;
    
    foreach ($array_champions as $ac) {
        $q = "
                SELECT home, home2, away, away2 FROM dbschedule
                WHERE title='$db_title $ac' AND country='$db_country' AND level<>'0'";
        
        $r = $sqlDB->sql_query($q, true);
        if (!$r) {
            return false;
        }
        $theclub = array();
        while ($row = $sqlDB->sql_fetchrow($r)) {
            if (!in_array($row['home'], $theclub)) {
                array_push($theclub, $row['home']);
            }
            if (!in_array($row['home2'], $theclub)) {
                array_push($theclub, $row['home2']);
            }
            if (!in_array($row['away'], $theclub)) {
                array_push($theclub, $row['away']);
            }
            if (!in_array($row['away2'], $theclub)) {
                array_push($theclub, $row['away2']);
            }
        }
        $sqlDB->sql_freeresult($r);

        $theclub = array_unique(array_filter(array_map('trim', $theclub)));



        $content .= '<h1>Klasemen Grup ' . $ac . '</h1><div class="jdsmall1"><strong>No</strong></div>
        <div class="jdwide1"><strong>Klub</strong></div>
        <div class="jdsmall1"><strong>Main</strong></div>
        <div class="jdsmall1"><strong>M</strong></div>
        <div class="jdsmall1"><strong>S</strong></div>
        <div class="jdsmall1"><strong>K</strong></div>
        <div class="jdsmallg1"><strong>SG</strong></div>
        <div class="jdsmall1"><strong>Poin</strong></div>
        <br class="clear" />';

        $tmp_file = LOGDIR . 'klasemen_'.$v.'_tmp_file.txt';
        $tmp_file2 = LOGDIR . 'klasemen_'.$v.'_tmp_file2.txt';
        if (is_file($tmp_file)) {
            unlink($tmp_file);
        }
        if (is_file($tmp_file2)) {
            unlink($tmp_file2);
        }
        $save_arr = array();

        $unique_team_id = 1;
        $tmp_point = $is_headtohead = array();
        foreach ($theclub as $detailclub) {
            $q3 = "SELECT team_id, team_name FROM dbteam WHERE team_name='$detailclub' LIMIT 1";
            $r3 = $sqlDB->sql_query($q3);
            if ($row3 = $sqlDB->sql_fetchrow($r3)) {
            } else {
                $row3['team_id'] = '-' . $unique_team_id++;
            }

            $club_data = get_schedule_club_data($sqlDB, $detailclub, 0, "$db_title $ac");
            $ab = $club_data['home_goal'] - $club_data['away_goal'];

            file_put_contents($tmp_file, $row3['team_id'] . "\t" . $club_data['point'] . "\t" . $ab . "\t" . $club_data['home_goal'] . "\t" . $detailclub . "\n", FILE_APPEND);
            $save_arr[$row3['team_id']] = $club_data;

            if (!array_key_exists($club_data['point'], $tmp_point)) {
                $tmp_point[$club_data['point']] = $detailclub;
            } else {
                if (!array_key_exists($club_data['point'], $is_headtohead)) {
                    $is_headtohead[$club_data['point']] = array($detailclub, $tmp_point[$club_data['point']]);
                }
            }
        }

        exec('cat ' . $tmp_file . ' | sort -k2.1nr -k3.1nr -k4.1nr -k5 > ' . $tmp_file2, $out);
        $sqlDB->sql_freeresult($r);

        $css = 2;
        $counter = 1;
        $arr_data = array_filter(array_map('trim', file($tmp_file2)));

        if (count($is_headtohead) > 0) {
            $is_headtohead = array_shift($is_headtohead);

            $club1 = $is_headtohead[0];
            $club2 = $is_headtohead[1];
            $headtoheadwinner = generate_schedule_headtohead($sqlDB, $season_id, $club1, $club2);

            $club1_pos = -1;
            $club2_pos = -1;
            $league_pos = 0;
            foreach ($arr_data as $vdata) {
                list($team_id, $team_point, $team_selisih, $away_goal, $team_name) = explode("\t", $vdata);
                if ($team_name == $club1) {
                    $club1_pos = $league_pos;
                }
                if ($team_name == $club2) {
                    $club2_pos = $league_pos;
                }
                $league_pos++;
            }
            reset($arr_data);

            if ($headtoheadwinner == 1 && $club1_pos > $club2_pos) {
                $switch_tmp = $arr_data[$club1_pos];
                $arr_data[$club1_pos] = $arr_data[$club2_pos];
                $arr_data[$club2_pos] = $switch_tmp;
            } elseif ($headtoheadwinner == 2 && $club1_pos < $club2_pos) {
                $switch_tmp = $arr_data[$club1_pos];
                $arr_data[$club1_pos] = $arr_data[$club2_pos];
                $arr_data[$club2_pos] = $switch_tmp;
            }
        }

        $standing_data[$ac] = array();
        foreach ($arr_data as $vdata) {
            list($team_id, $team_point, $team_selisih, $away_goal, $team_name) = explode("\t", $vdata);
            $xdata = $save_arr[$team_id];
            
            $q2 = "SELECT url FROM dbarticles WHERE team_id='$team_id' AND category='16' LIMIT 1";
            $r2 = $sqlDB->sql_query($q2);
            if ($row2 = $sqlDB->sql_fetchrow($r2)) {
                $clubname = '<a href="/club/' . $row2['url'] . '.html" class="greenlink">' . trim($team_name) . '</a>';
            } else {
                $clubname = trim($team_name);
            }

            $content .= '
                <div class="jdsmall' . $css . '">' . $counter . '</div>
                <div class="jdwide' . $css . '">' . $clubname . '</div>
                <div class="jdsmall' . $css . '">' . $xdata['played'] . '</div>
                <div class="jdsmall' . $css . '">' . $xdata['win'] . '</div>
                <div class="jdsmall' . $css . '">' . $xdata['draw'] . '</div>
                <div class="jdsmall' . $css . '">' . $xdata['loose'] . '</div>
                <div class="jdsmallg' . $css . '">' . $xdata['home_goal'] . ' - ' . $xdata['away_goal'] . '</div>
                <div class="jdsmall' . $css . '">' . $xdata['point'] . '</div>
                <br class="clear" />
            ';
            $standing_data[$ac][] = array(
                'no' => $counter,
                'club' => trim($team_name),
                'played' => $xdata['played'],
                'win' => $xdata['win'],
                'draw' => $xdata['draw'],
                'lost' => $xdata['loose'],
                'home_goal' => $xdata['home_goal'],
                'away_goal' => $xdata['away_goal'],
                'point' => $xdata['point'],
            );

            $zone_start = '';
            $zone_end = '';
            $deduction_point = '0';
            $team_id = $unique_random_id . '0999' . $team_id;
            
            $unique_random_id++;

            $counter++;
            $css = $css == 1 ? 2 : 1;
        }
    }

    if ($standing_data) {
        $memcache_obj = new Memcache;
        bola_memcached_connect($memcache_obj);
        $memcache_obj->set('bolanet_standing_'.$v, serialize($standing_data), false, 7 * 24 * 3600);
        write_file_direct(TEMPDIR.'klasemen/bolanet_standing_'.$v, serialize($standing_data));
        $memcache_obj->close();
    }

    $topscore = get_ten_topscore($v);
    $topscore = str_replace('topskor_liga_'.$v.'.html', 'topskor_'.$v.'.html', $topscore);
    $content .= '
                <br/><br/>' . $topscore . '
                <br/>' . $recent_news . '
            </div>
        </div>
    ';

    $filename = $schedule_dir . 'klasemen_' . $v . '.html';
    $fileurl = $schedule_url . 'klasemen_' . $v . '.html';
    
    $metatitle = 'Update Klasemen '.$league_title.' ' . $CURRENT_SEASON_YEAR . ' Terbaru';
    $metadesc = 'Update terbaru Klasemen '.$league_title.' ' . $CURRENT_SEASON_YEAR . ' dari Live Score pertandingan '.$league_title.' terakhir disertai daftar Top Skor';
    $metakey = explode(' ', trim(strtolower($metadesc)));
    $metakey = array_unique(array_filter(array_map('trim', $metakey)));
    $metakey = array_slice($metakey, 0, 50);
    $metakey = implode(',', $metakey);

    write_file($filename, $content, $metatitle, $metakey, $metadesc, '', true, 'full', 5);
    insert_property_og($filename, 'Daftar Klasemen '.$league_title, $fileurl, '');
    echo generated_link($fileurl);
}

# this function off , this is klasemen sea games 2015
function generate_klasemenSeaGames($sqlDB)
{
    global $schedule_url, $schedule_dir, $tv_url, $tv_jadwal_url;
    
    $CURRENT_SEASON_YEAR = 2015;
    $k = 5;
    $v = 'sea_games';
    $league_title = 'Sea Games 2015';
    $array_champions = range('A', 'B');
    $db_country = 'SEA Games 2015';
    $db_title = 'Grup';
    
    generate_schedule_topscore_global($sqlDB, $v, $league_title, $db_country);
    
    $menu_top = '
        <div class="topmenu">
            <a href="' . $schedule_url . 'score.html">Skor Terkini</a>
            &nbsp;&nbsp;&nbsp;
            <a href="' . $schedule_url . '">Jadwal Pertandingan</a>
            &nbsp;&nbsp;&nbsp;
            <a href="' . $tv_jadwal_url . '">Jadwal TV</a>
            &nbsp;&nbsp;
            <a href="' . $schedule_url . 'klasemen_liga_inggris.html">Klasemen</a>
            &nbsp;&nbsp;
            <a href="' . $schedule_url . 'preview.html">Preview</a>
            &nbsp;&nbsp;
            <a href="' . $schedule_url . 'review.html">Review</a>
        </div>
    ';

    $recent_news = '
        <div class="greentitle" style="margin-bottom:10px; margin-top:10px; text-align:left; font-size:18px">
            BERITA TERBARU ' . (strtoupper($league_title)) . '
        </div>
        <ul style="list-style:none; margin:0px; padding:0px">
    ';

    $recent_news_arr = get_related_news_league_bytag($sqlDB, '11196');

    foreach ($recent_news_arr as $row) {
        $recent_news .= '
            <li style="padding:5px 10px; border-bottom:1px dashed #aaa">
                <span style="color:#666">(' . date("d-m-Y H:i", strtotime($row["schedule"])) . ')</span>
                <a href="/' . $row["category_url"] . '/' . $row["url"] . '.html" class="greenlink">' . $row["title"] . '</a>
            </li>
        ';
    }

    $recent_news .= '
        </ul>
    ';

    /*$share = '
        <!--SOCIALTAB-->
            <div id="bl-social-tabs"></div>
            <script type="text/javascript">
                $(document).ready(function() {
                                    $("#bl-social-tabs").klsocial({
                                            url:"' . $schedule_url . 'klasemen_'.$v.'html",
                                            comment_count:"-1"
                                    });
                });
            </script>
        <!--ENDSOCIALTAB-->
    ';*/

    $content = '
        <div class="bigcon">
            <div class="bigcon2">
                <div class="nav">
                    <a href="/" style="text-decoration:none;">HOME</a> &raquo;
                    KLASEMEN ' . (strtoupper($league_title)) . '
                </div>
                ' . $menu_top . '
                <h1 class="greentitle" style="margin: 0px;">Klasemen ' . (ucwords($league_title)) . '</h1>
                <div style="padding: 5px 0px 0px 0px;">Update terbaru ' . (ucwords($league_title)) . ' dari pertandingan ' . (ucwords($league_title)) . '.</div>
                <div class="jdskor1">
                                    Pilih Kompetisi: '.dropdown_klasemen_options($v).'
                </div>
                <!--SKLAS-->
    ';

    $season_id = 0;
    $standing_data = array();

    $unique_random_id = 0;
    
    foreach ($array_champions as $ac) {
        $q = "
                SELECT home, home2, away, away2 FROM dbschedule
                WHERE title='$db_title $ac' AND country='$db_country' AND level<>'0'";
        
        $r = $sqlDB->sql_query($q, true);
        if (!$r) {
            return false;
        }
        $theclub = array();
        while ($row = $sqlDB->sql_fetchrow($r)) {
            if (!in_array($row['home'], $theclub)) {
                array_push($theclub, $row['home']);
            }
            if (!in_array($row['home2'], $theclub)) {
                array_push($theclub, $row['home2']);
            }
            if (!in_array($row['away'], $theclub)) {
                array_push($theclub, $row['away']);
            }
            if (!in_array($row['away2'], $theclub)) {
                array_push($theclub, $row['away2']);
            }
        }
        $sqlDB->sql_freeresult($r);

        $theclub = array_unique(array_filter(array_map('trim', $theclub)));



        $content .= '<h1>Klasemen Grup ' . $ac . '</h1><div class="jdsmall1"><strong>No</strong></div>
        <div class="jdwide1"><strong>Klub</strong></div>
        <div class="jdsmall1"><strong>Main</strong></div>
        <div class="jdsmall1"><strong>M</strong></div>
        <div class="jdsmall1"><strong>S</strong></div>
        <div class="jdsmall1"><strong>K</strong></div>
        <div class="jdsmallg1"><strong>SG</strong></div>
        <div class="jdsmall1"><strong>Poin</strong></div>
        <br class="clear" />';

        $tmp_file = LOGDIR . 'klasemen_'.$v.'_tmp_file.txt';
        $tmp_file2 = LOGDIR . 'klasemen_'.$v.'_tmp_file2.txt';
        if (is_file($tmp_file)) {
            unlink($tmp_file);
        }
        if (is_file($tmp_file2)) {
            unlink($tmp_file2);
        }
        $save_arr = array();

        $unique_team_id = 1;
        $tmp_point = $is_headtohead = array();
        foreach ($theclub as $detailclub) {
            $q3 = "SELECT team_id, team_name FROM dbteam WHERE team_name='$detailclub' LIMIT 1";
            $r3 = $sqlDB->sql_query($q3);
            if ($row3 = $sqlDB->sql_fetchrow($r3)) {
            } else {
                $row3['team_id'] = '-' . $unique_team_id++;
            }

            $club_data = get_schedule_club_data($sqlDB, $detailclub, 0, "$db_title $ac");
            $ab = $club_data['home_goal'] - $club_data['away_goal'];

            file_put_contents($tmp_file, $row3['team_id'] . "\t" . $club_data['point'] . "\t" . $ab . "\t" . $club_data['home_goal'] . "\t" . $detailclub . "\n", FILE_APPEND);
            $save_arr[$row3['team_id']] = $club_data;

            if (!array_key_exists($club_data['point'], $tmp_point)) {
                $tmp_point[$club_data['point']] = $detailclub;
            } else {
                if (!array_key_exists($club_data['point'], $is_headtohead)) {
                    $is_headtohead[$club_data['point']] = array($detailclub, $tmp_point[$club_data['point']]);
                }
            }
        }

        exec('cat ' . $tmp_file . ' | sort -k2.1nr -k3.1nr -k4.1nr -k5 > ' . $tmp_file2, $out);
        $sqlDB->sql_freeresult($r);

        $css = 2;
        $counter = 1;
        $arr_data = array_filter(array_map('trim', file($tmp_file2)));

        if (count($is_headtohead) > 0) {
            $is_headtohead = array_shift($is_headtohead);

            $club1 = $is_headtohead[0];
            $club2 = $is_headtohead[1];
            $headtoheadwinner = generate_schedule_headtohead($sqlDB, $season_id, $club1, $club2);

            $club1_pos = -1;
            $club2_pos = -1;
            $league_pos = 0;
            foreach ($arr_data as $vdata) {
                list($team_id, $team_point, $team_selisih, $away_goal, $team_name) = explode("\t", $vdata);
                if ($team_name == $club1) {
                    $club1_pos = $league_pos;
                }
                if ($team_name == $club2) {
                    $club2_pos = $league_pos;
                }
                $league_pos++;
            }
            reset($arr_data);

            if ($headtoheadwinner == 1 && $club1_pos > $club2_pos) {
                $switch_tmp = $arr_data[$club1_pos];
                $arr_data[$club1_pos] = $arr_data[$club2_pos];
                $arr_data[$club2_pos] = $switch_tmp;
            } elseif ($headtoheadwinner == 2 && $club1_pos < $club2_pos) {
                $switch_tmp = $arr_data[$club1_pos];
                $arr_data[$club1_pos] = $arr_data[$club2_pos];
                $arr_data[$club2_pos] = $switch_tmp;
            }
        }

        $standing_data[$ac] = array();
        foreach ($arr_data as $vdata) {
            list($team_id, $team_point, $team_selisih, $away_goal, $team_name) = explode("\t", $vdata);
            $xdata = $save_arr[$team_id];
            
            $q2 = "SELECT url FROM dbarticles WHERE team_id='$team_id' AND category='16' LIMIT 1";
            $r2 = $sqlDB->sql_query($q2);
            if ($row2 = $sqlDB->sql_fetchrow($r2)) {
                $clubname = '<a href="/club/' . $row2['url'] . '.html" class="greenlink">' . trim($team_name) . '</a>';
            } else {
                $clubname = trim($team_name);
            }

            $content .= '
                <div class="jdsmall' . $css . '">' . $counter . '</div>
                <div class="jdwide' . $css . '">' . $clubname . '</div>
                <div class="jdsmall' . $css . '">' . $xdata['played'] . '</div>
                <div class="jdsmall' . $css . '">' . $xdata['win'] . '</div>
                <div class="jdsmall' . $css . '">' . $xdata['draw'] . '</div>
                <div class="jdsmall' . $css . '">' . $xdata['loose'] . '</div>
                <div class="jdsmallg' . $css . '">' . $xdata['home_goal'] . ' - ' . $xdata['away_goal'] . '</div>
                <div class="jdsmall' . $css . '">' . $xdata['point'] . '</div>
                <br class="clear" />
            ';
            $standing_data[$ac][] = array(
                'no' => $counter,
                'club' => trim($team_name),
                'played' => $xdata['played'],
                'win' => $xdata['win'],
                'draw' => $xdata['draw'],
                'lost' => $xdata['loose'],
                'home_goal' => $xdata['home_goal'],
                'away_goal' => $xdata['away_goal'],
                'point' => $xdata['point'],
            );

            $zone_start = '';
            $zone_end = '';
            $deduction_point = '0';
            $team_id = $unique_random_id . '0999' . $team_id;
            
            $unique_random_id++;

            $counter++;
            $css = $css == 1 ? 2 : 1;
        }
    }

    if ($standing_data) {
        $memcache_obj = new Memcache;
        bola_memcached_connect($memcache_obj);
        $memcache_obj->set('bolanet_standing_'.$v, serialize($standing_data), false, 7 * 24 * 3600);
        write_file_direct(TEMPDIR.'klasemen/bolanet_standing_'.$v, serialize($standing_data));
        $memcache_obj->close();
    }

    $topscore = get_ten_topscore($v);
    $topscore = str_replace('topskor_liga_'.$v.'.html', 'topskor_'.$v.'.html', $topscore);
    $content .= '
                <br/><br/>' . $topscore . '
                <br/>' . $recent_news . '
            </div>
        </div>
    ';

    $filename = $schedule_dir . 'klasemen_' . $v . '.html';
    $fileurl = $schedule_url . 'klasemen_' . $v . '.html';
    
    $metatitle = 'Update Klasemen '.$league_title.' ' . $CURRENT_SEASON_YEAR . ' Terbaru';
    $metadesc = 'Update terbaru Klasemen '.$league_title.' ' . $CURRENT_SEASON_YEAR . ' dari Live Score pertandingan '.$league_title.' terakhir disertai daftar Top Skor';
    $metakey = explode(' ', trim(strtolower($metadesc)));
    $metakey = array_unique(array_filter(array_map('trim', $metakey)));
    $metakey = array_slice($metakey, 0, 50);
    $metakey = implode(',', $metakey);

    write_file($filename, $content, $metatitle, $metakey, $metadesc, '', true, 'full', 5);
    insert_property_og($filename, 'Daftar Klasemen '.$league_title, $fileurl, '');
    echo generated_link($fileurl);
}

function generate_klasemen_euro2016($sqlDB)
{
    global $schedule_url, $schedule_dir, $tv_url, $tv_jadwal_url;
    

    $v = 'euro_2016';
    $league_title = 'Euro 2016';
    $array_champions = range('A', 'F');
    $db_country = 'Prancis';
    $db_title = 'EURO 2016 Grup';
    $season_id = 57;
    
    generate_schedule_topscore_global($sqlDB, $v, $league_title, $db_country, $season_id);
    
    $menu_top = '
        <div class="topmenu">
            <a href="' . $schedule_url . 'score.html">Skor Terkini</a>
            &nbsp;&nbsp;&nbsp;
            <a href="' . $schedule_url . '">Jadwal Pertandingan</a>
            &nbsp;&nbsp;&nbsp;
            <a href="' . $tv_jadwal_url . '">Jadwal TV</a>
            &nbsp;&nbsp;
            <a href="' . $schedule_url . 'klasemen_liga_inggris.html">Klasemen</a>
            &nbsp;&nbsp;
            <a href="' . $schedule_url . 'preview.html">Preview</a>
            &nbsp;&nbsp;
            <a href="' . $schedule_url . 'review.html">Review</a>
        </div>
    ';

    $recent_news = '
        <div class="greentitle" style="margin-bottom:10px; margin-top:10px; text-align:left; font-size:18px">
            BERITA TERBARU ' . (strtoupper($league_title)) . '
        </div>
        <ul style="list-style:none; margin:0px; padding:0px">
    ';

    $recent_news_arr = get_related_news_league_bytag($sqlDB, '5934');

    foreach ($recent_news_arr as $row) {
        $recent_news .= '
            <li style="padding:5px 10px; border-bottom:1px dashed #aaa">
                <span style="color:#666">(' . date("d-m-Y H:i", strtotime($row["schedule"])) . ')</span>
                <a href="/' . $row["category_url"] . '/' . $row["url"] . '.html" class="greenlink">' . $row["title"] . '</a>
            </li>
        ';
    }

    $recent_news .= '
        </ul>
    ';

    /*$share = '
        <!--SOCIALTAB-->
            <div id="bl-social-tabs"></div>
            <script type="text/javascript">
                $(document).ready(function() {
                                    $("#bl-social-tabs").klsocial({
                                            url:"' . $schedule_url . 'klasemen_'.$v.'html",
                                            comment_count:"-1"
                                    });
                });
            </script>
        <!--ENDSOCIALTAB-->
    ';*/

    $content = '
        <div class="bigcon">
            <div class="bigcon2">
                <div class="nav">
                    <a href="/" style="text-decoration:none;">HOME</a> &raquo;
                    KLASEMEN ' . (strtoupper($league_title)) . '
                </div>
                ' . $menu_top . '
                <h1 class="greentitle" style="margin: 0px;">Klasemen ' . (ucwords($league_title)) . '</h1>
                <div style="padding: 5px 0px 0px 0px;">Update terbaru ' . (ucwords($league_title)) . ' dari pertandingan ' . (ucwords($league_title)) . '.</div>
                <div class="jdskor1">
                                    Pilih Kompetisi: '.dropdown_klasemen_options($v).'
                </div>
                <!--SKLAS-->
    ';

    $standing_data = array();

    $unique_random_id = 0;
    
    foreach ($array_champions as $ac) {
        $q = "SELECT home, home2, away, away2 FROM dbschedule
        WHERE title='$db_title $ac' AND country='$db_country' AND level<>'0'";

        $r = $sqlDB->sql_query($q, true);
        
        if (!$r) {
            return false;
        }
        $theclub = array();
        while ($row = $sqlDB->sql_fetchrow($r)) {
            if (!in_array($row['home'], $theclub)) {
                array_push($theclub, $row['home']);
            }
            if (!in_array($row['home2'], $theclub)) {
                array_push($theclub, $row['home2']);
            }
            if (!in_array($row['away'], $theclub)) {
                array_push($theclub, $row['away']);
            }
            if (!in_array($row['away2'], $theclub)) {
                array_push($theclub, $row['away2']);
            }
        }
        $sqlDB->sql_freeresult($r);
        $theclub = array_unique(array_filter(array_map('trim', $theclub)));

        $content .= '<h1>Klasemen Grup ' . $ac . '</h1><div class="jdsmall1"><strong>No</strong></div>
        <div class="jdwide1"><strong>Team</strong></div>
        <div class="jdsmall1"><strong>Main</strong></div>
        <div class="jdsmall1"><strong>M</strong></div>
        <div class="jdsmall1"><strong>S</strong></div>
        <div class="jdsmall1"><strong>K</strong></div>
        <div class="jdsmallg1"><strong>SG</strong></div>
        <div class="jdsmall1"><strong>Poin</strong></div>
        <br class="clear" />';

        $tmp_file = LOGDIR . 'klasemen_'.$v.'_tmp_file.txt';
        $tmp_file2 = LOGDIR . 'klasemen_'.$v.'_tmp_file2.txt';
        if (is_file($tmp_file)) {
            unlink($tmp_file);
        }
        if (is_file($tmp_file2)) {
            unlink($tmp_file2);
        }
        $save_arr = array();

        $unique_team_id = 1;
        $tmp_point = $is_headtohead = array();
       
        foreach ($theclub as $detailclub) {
            $q3 = "SELECT team_id, team_name FROM dbteam WHERE team_name='$detailclub' LIMIT 1";
            $r3 = $sqlDB->sql_query($q3);
            if ($row3 = $sqlDB->sql_fetchrow($r3)) {
            } else {
                $row3['team_id'] = '-' . $unique_team_id++;
            }

            $club_data = get_schedule_club_data($sqlDB, $detailclub, $season_id, "$db_title $ac");
            $ab = $club_data['home_goal'] - $club_data['away_goal'];

            file_put_contents($tmp_file, $row3['team_id'] . "\t" . $club_data['point'] . "\t" . $ab . "\t" . $club_data['home_goal'] . "\t" . $detailclub . "\n", FILE_APPEND);
            $save_arr[$row3['team_id']] = $club_data;

            if (!array_key_exists($club_data['point'], $tmp_point)) {
                $tmp_point[$club_data['point']] = $detailclub;
            } else {
                if (!array_key_exists($club_data['point'], $is_headtohead)) {
                    $is_headtohead[$club_data['point']] = array($detailclub, $tmp_point[$club_data['point']]);
                }
            }
        }

        exec('cat ' . $tmp_file . ' | sort -k2.1nr -k3.1nr -k4.1nr -k5 > ' . $tmp_file2, $out);
        $sqlDB->sql_freeresult($r);

        $css = 2;
        $counter = 1;
        $arr_data = array_filter(array_map('trim', file($tmp_file2)));

        if (count($is_headtohead) > 0) {
            $is_headtohead = array_shift($is_headtohead);

            $club1 = $is_headtohead[0];
            $club2 = $is_headtohead[1];
            $headtoheadwinner = generate_schedule_headtohead($sqlDB, $season_id, $club1, $club2);

            $club1_pos = -1;
            $club2_pos = -1;
            $league_pos = 0;
            foreach ($arr_data as $vdata) {
                list($team_id, $team_point, $team_selisih, $away_goal, $team_name) = explode("\t", $vdata);
                if ($team_name == $club1) {
                    $club1_pos = $league_pos;
                }
                if ($team_name == $club2) {
                    $club2_pos = $league_pos;
                }
                $league_pos++;
            }
            reset($arr_data);

            if ($headtoheadwinner == 1 && $club1_pos > $club2_pos) {
                $switch_tmp = $arr_data[$club1_pos];
                $arr_data[$club1_pos] = $arr_data[$club2_pos];
                $arr_data[$club2_pos] = $switch_tmp;
            } elseif ($headtoheadwinner == 2 && $club1_pos < $club2_pos) {
                $switch_tmp = $arr_data[$club1_pos];
                $arr_data[$club1_pos] = $arr_data[$club2_pos];
                $arr_data[$club2_pos] = $switch_tmp;
            }
        }

        $standing_data[$ac] = array();
        
        foreach ($arr_data as $vdata) {
            list($team_id, $team_point, $team_selisih, $away_goal, $team_name) = explode("\t", $vdata);
            $xdata = $save_arr[$team_id];
            
            $q2 = "SELECT url FROM dbarticles WHERE team_id='$team_id' AND category='16' LIMIT 1";
            $r2 = $sqlDB->sql_query($q2);
            if ($row2 = $sqlDB->sql_fetchrow($r2)) {
                $clubname = '<a href="/club/' . $row2['url'] . '.html" class="greenlink">' . trim($team_name) . '</a>';
            } else {
                $clubname = trim($team_name);
            }

            $content .= '
                <div class="jdsmall' . $css . '">' . $counter . '</div>
                <div class="jdwide' . $css . '">' . $clubname . '</div>
                <div class="jdsmall' . $css . '">' . $xdata['played'] . '</div>
                <div class="jdsmall' . $css . '">' . $xdata['win'] . '</div>
                <div class="jdsmall' . $css . '">' . $xdata['draw'] . '</div>
                <div class="jdsmall' . $css . '">' . $xdata['loose'] . '</div>
                <div class="jdsmallg' . $css . '">' . $xdata['home_goal'] . ' - ' . $xdata['away_goal'] . '</div>
                <div class="jdsmall' . $css . '">' . $xdata['point'] . '</div>
                <br class="clear" />
            ';
            $standing_data[$ac][] = array(
                'no' => $counter,
                'club' => trim($team_name),
                'played' => $xdata['played'],
                'win' => $xdata['win'],
                'draw' => $xdata['draw'],
                'lost' => $xdata['loose'],
                'home_goal' => $xdata['home_goal'],
                'away_goal' => $xdata['away_goal'],
                'point' => $xdata['point'],
            );

            $zone_start = '';
            $zone_end = '';
            $deduction_point = '0';
            $team_id = $unique_random_id . '0999' . $team_id;
            
            $unique_random_id++;

            $counter++;
            $css = $css == 1 ? 2 : 1;
        }
    }

    if ($standing_data) {
        $memcache_obj = new Memcache;
        bola_memcached_connect($memcache_obj);
        $memcache_obj->set('bolanet_standing_'.$v, serialize($standing_data), false, 14 * 24 * 3600);
        write_file_direct(TEMPDIR.'klasemen/bolanet_standing_'.$v, serialize($standing_data));
        $memcache_obj->close();
    }

    $topscore = get_ten_topscore($v);
    $topscore = str_replace('topskor_liga_'.$v.'.html', 'topskor_'.$v.'.html', $topscore);
    $content .= '
                <br/><br/>' . $topscore . '
                <br/>' . $recent_news . '
            </div>
        </div>
    ';

    $filename = $schedule_dir . 'klasemen_' . $v . '.html';
    $fileurl = $schedule_url . 'klasemen_' . $v . '.html';
    
    $metatitle = 'Update Klasemen '.$league_title.' Terbaru';
    $metadesc = 'Update terbaru Klasemen '.$league_title.' dari Live Score pertandingan '.$league_title.' terakhir disertai daftar Top Skor';
    $metakey = explode(' ', trim(strtolower($metadesc)));
    $metakey = array_unique(array_filter(array_map('trim', $metakey)));
    $metakey = array_slice($metakey, 0, 50);
    $metakey = implode(',', $metakey);

    write_file($filename, $content, $metatitle, $metakey, $metadesc, '', true, 'full', 5);
    insert_property_og($filename, 'Daftar Klasemen '.$league_title, $fileurl, '');
    echo generated_link($fileurl);
}

/**
 * Generate klasemen, liga indonesia soccer championship, Liga 1
 * And view 10 top score
 */
function generate_schedule_klasemen_isc($sqlDB, $echo = true)
{
    global $schedule_url, $schedule_dir, $tv_url, $tv_jadwal_url, $temporary_folder;
     
    //generate topscore first
    //generate_schedule_topscore_isl($sqlDB, $echo);
    
    $cache_name = '_bola_standing_data_4';
    //$_cache_filename_ = $temporary_folder.'klasemen/'.$cache_name;
    
    $CURRENT_SEASON_YEAR = 2017;
    $league_name = 'indonesia';
    
    $array = array(4 => 'indonesia');
    $name_liga = 'Indonesia Liga 1';
    $array_title = array(4 => $name_liga);
    $inggris = '';

    $menu_top = '
        <div class="topmenu">
            <a href="' . $schedule_url . 'score.html">Skor Terkini</a>
            &nbsp;&nbsp;&nbsp;
            <a href="' . $schedule_url . '">Jadwal Pertandingan</a>
            &nbsp;&nbsp;&nbsp;
            <a href="' . $tv_jadwal_url . '">Jadwal TV</a>
            &nbsp;&nbsp;
            <a href="' . $schedule_url . 'klasemen_liga_inggris.html">Klasemen</a>
            &nbsp;&nbsp;
            <a href="' . $schedule_url . 'preview.html">Preview</a>
            &nbsp;&nbsp;
            <a href="' . $schedule_url . 'review.html">Review</a>
        </div>
    ';

    $memcache_obj = new Memcache;
    bola_memcached_connect($memcache_obj);

    foreach ($array as $k => $v) {
        $standing_data = array();
        /*$share = '
            <!--SOCIALTAB-->
            <div id="bl-social-tabs"></div>
            <script type="text/javascript">
            $(document).ready(function() {
                $("#bl-social-tabs").klsocial({
                    url:"' . $schedule_url . 'klasemen_liga_' . $v . '.html",
                    comment_count:"-1"
                });
            });
            </script>
            <!--ENDSOCIALTAB-->
        ';*/

        $additional_text = '';
        switch ($k) {
            case 4: $infotext = '<div style="padding: 5px 0px 0px 0px;">Update terbaru Klasemen ' . $name_liga . $CURRENT_SEASON_YEAR . ' dari pertandingan '. $name_liga .' terakhir. Simak juga daftar <a href="#topskorman" class="bluelink">Top Skor</a> sementara.</div>';
                $metatitle = 'Update Klasemen Liga ' . $name_liga. $CURRENT_SEASON_YEAR . ' Terbaru';
                $metadesc = 'Update terbaru Klasemen Liga ' . $name_liga . $CURRENT_SEASON_YEAR . ' dari Live Score pertandingan '.$name_liga.' terakhir disertai daftar Top Skor';
                break;
            default: $infotext = '';
        }

        $recent_news = '
            <div class="greentitle" style="margin-bottom:10px; margin-top:10px; text-align:left; font-size:18px">
                BERITA TERBARU ' . (strtoupper($array_title[$k])) . '
            </div>
            <ul style="list-style:none; margin:0px; padding:0px">
        ';

        $q = $sqlDB->sql_query("
                    SELECT a.title, a.url, a.schedule, b.category_url FROM dbnews a, dbcategory b
                    WHERE a.category = b.category_id AND a.category = '" . ($k == 27 ? 4 : $k) . "' AND a.level != '0' AND a.schedule <= NOW()
                    ORDER BY a.schedule DESC LIMIT 10");
        while ($row = $sqlDB->sql_fetchrow($q)) {
            $recent_news .= '
                <li style="padding:5px 10px; border-bottom:1px dashed #aaa">
                    <span style="color:#666">(' . date("d-m-Y H:i", strtotime($row["schedule"])) . ')</span>
                    <a href="/' . $row["category_url"] . '/' . $row["url"] . '.html" class="greenlink">' . $row["title"] . '</a>
                </li>
            ';
        }
        
        $recent_news .= '
            </ul>
        ';

        $content = '
            <div class="bigcon">
                <div class="bigcon2">
                    <div class="nav">
                        <a href="/" style="text-decoration:none;">HOME</a> &raquo;
                        KLASEMEN ' . (strtoupper($array_title[$k])) . '
                    </div>
                    ' . $menu_top . '
                    <h1 class="greentitle" style="margin: 0px;">Klasemen ' . (ucwords($array_title[$k])) . '</h1>
                    ' . $infotext . ' 
                    <div class="jdskor1">Pilih Kompetisi: '.dropdown_klasemen_options($v).'</div>
                    <!--SKLAS-->
                    <div class="klasemen_row">
                        <div class="jdsmall1"><strong>No</strong></div>
                        <div class="jdwide1"><span class="mark ">&nbsp;</span><strong>Klub</strong></div>
                        <div class="jdsmall1"><strong>MN</strong></div>
                        <div class="jdsmall1"><strong>M</strong></div>
                        <div class="jdsmall1"><strong>S</strong></div>
                        <div class="jdsmall1"><strong>K</strong></div>
                        <div class="jdsmall1"><strong>MG</strong></div>
                        <div class="jdsmall1"><strong>KG</strong></div>
                        <div class="jdsmall1"><strong>SG</strong></div>
                        <div class="jdsmall1"><strong>Poin</strong></div>
                        <br class="clear" />
                    </div>
                    
                <!--SSTANDINGS-->
        ';

        $qs = "
            SELECT season_id FROM dbseason
            WHERE season_cat_id='$k' AND season_status='1' ORDER BY season_id DESC LIMIT 1";
        $rs = $sqlDB->sql_query($qs, true);
        $rows = $sqlDB->sql_fetchrow($rs);
        $season_id = $rows['season_id'];
        
        generate_schedule_topscore_global($sqlDB, 'liga_indonesia', 'Liga Indonesia', $league_name, $season_id);
        
        $q = "
            SELECT DISTINCT A.team_id, A.team_name FROM dbteam A, dbparticipant B
            WHERE part_season_id='$season_id' AND team_id=part_team_id AND B.part_status<>'0' ORDER BY team_name";
        
        $r = $sqlDB->sql_query($q, true);
        if (!$r) {
            return false;
        }
        
        $tmp_file = LOGDIR . 'klasemen_tmp_file.txt';
        $tmp_file2 = LOGDIR . 'klasemen_tmp_file2.txt';
        if (is_file($tmp_file)) {
            unlink($tmp_file);
        }
        if (is_file($tmp_file2)) {
            unlink($tmp_file2);
        }
        $save_arr = array();
        while ($row = $sqlDB->sql_fetchrow($r)) {
            $club_data = get_schedule_club_data($sqlDB, $row['team_name'], $season_id);
            $ab = $club_data['home_goal'] - $club_data['away_goal'];
            file_put_contents($tmp_file, $row['team_id'] . "\t" . $club_data['point'] . "\t" . $ab . "\t" . $club_data['home_goal'] . "\t" . $row['team_name'] . "\n", FILE_APPEND);
            $save_arr[$row['team_id']] = $club_data;
        }
        exec('cat ' . $tmp_file . ' | sort -k2.1nr -k3.1nr -k4.1nr -k5 > ' . $tmp_file2, $out);
        $sqlDB->sql_freeresult($r);
        $sqlDB->sql_freeresult($rs);
        
        $css = 2;
        $counter = 1;
        $arr_data = array_filter(array_map('trim', file($tmp_file2)));
        // spesial untuk member page
        $member_page_file = LOGDIR . 'member_page_' . $v . '.txt';
        if (is_file($member_page_file)) {
            file_put_contents($member_page_file, "");
        }

        $tmp_point = $is_headtohead = array();
        foreach ($arr_data as $vdata) {
            list($team_id, $team_point, $team_selisih, $away_goal, $team_name) = explode("\t", $vdata);
            if (!array_key_exists($team_point, $tmp_point)) {
                $tmp_point[$team_point]['team'] = $team_name;
                $tmp_point[$team_point]['home_goal'] = (int) $away_goal;
                $tmp_point[$team_point]['away_goal'] = $away_goal - $team_selisih;
            } else {
                if (!array_key_exists($team_point, $is_headtohead)) {
                    $is_headtohead[$team_point] = array($team_name, $tmp_point[$team_point]['team'], $save_arr[$team_id]['home_goal'], $save_arr[$team_id]['away_goal'], $tmp_point[$team_point]['home_goal'], $tmp_point[$team_point]['away_goal']);
                }
            }
        }

        if (count($is_headtohead) > 0) {
            $is_headtohead_arr = $is_headtohead;
            foreach ($is_headtohead_arr as $is_headtohead) {
                $club1 = $is_headtohead[0];
                $club2 = $is_headtohead[1];
                $club1_gf = $is_headtohead[2];
                $club1_ga = $is_headtohead[3];
                $club2_gf = $is_headtohead[4];
                $club2_ga = $is_headtohead[5];
                $headtoheadwinner = generate_schedule_headtohead_indonesia($sqlDB, $season_id, $club1, $club2, $club1_gf, $club1_ga, $club2_gf, $club2_ga);
                $club1_pos = -1;
                $club2_pos = -1;
                $league_pos = 0;
                foreach ($arr_data as $vdata) {
                    list($team_id, $team_point, $team_selisih, $away_goal, $team_name) = explode("\t", $vdata);
                    if ($team_name == $club1) {
                        $club1_pos = $league_pos;
                    }
                    if ($team_name == $club2) {
                        $club2_pos = $league_pos;
                    }
                    $league_pos++;
                }
                reset($arr_data);
                if ($headtoheadwinner == 1 && $club1_pos > $club2_pos) {
                    $switch_tmp = $arr_data[$club1_pos];
                    $arr_data[$club1_pos] = $arr_data[$club2_pos];
                    $arr_data[$club2_pos] = $switch_tmp;
                } elseif ($headtoheadwinner == 2 && $club1_pos < $club2_pos) {
                    $switch_tmp = $arr_data[$club1_pos];
                    $arr_data[$club1_pos] = $arr_data[$club2_pos];
                    $arr_data[$club2_pos] = $switch_tmp;
                }
            }
        }

        foreach ($arr_data as $vdata) {
            list($team_id, $team_point, $team_selisih, $away_goal, $team_name) = explode("\t", $vdata);
            $xdata = $save_arr[$team_id];
            $team_name = trim($team_name);
            
            $q2 = "SELECT url FROM dbarticles WHERE team_id='$team_id' AND category='16' LIMIT 1";
            $r2 = $sqlDB->sql_query($q2);
            $row2 = $sqlDB->sql_fetchrow($r2);
            
            $_standing_club_url_ = '';
            if ($row2['url']) {
                $_standing_club_url_ = $row2['url'];
            }
            
            $mark = '';
            
            $css_row = 'rank ';
            /*if($STANDING_STATUS)
            {
                $css_row .= isset($STANDING_STATUS[$counter])?$STANDING_STATUS[$counter]:'';
            }*/
            
            $_gd_ = $xdata['home_goal'] - $xdata['away_goal'];
            if ($_gd_ > 0) {
                $_gd_ = '+'.$_gd_;
            } elseif ($_gd_ > 0) {
                $_gd_ = '-'.$_gd_;
            }
            $row2['url'] = isset($row2['url']) ? "/club/".$row2['url'].".html" : '';
            $content .= '
                <div class="klasemen_row">
                    <div class="jdsmall' . $css . '"><span class="'.$css_row.'">' . $counter . '</span></div>
                    <div class="jdwide' . $css . '"><span class="mark '.$mark.'">&nbsp;</span><strong><a href="' . $row2['url'] . '" class="greenlink">'.trim($team_name) . '</a></strong></div>
                    <div class="jdsmall' . $css . '">' . $xdata['played'] . '</div>
                    <div class="jdsmall' . $css . '">' . $xdata['win'] . '</div>
                    <div class="jdsmall' . $css . '">' . $xdata['draw'] . '</div>
                    <div class="jdsmall' . $css . '">' . $xdata['loose'] . '</div>
                    <div class="jdsmall' . $css . '">' . $xdata['home_goal'] . '</div>
                    <div class="jdsmall' . $css . '">' . $xdata['away_goal'] . '</div>
                    <div class="jdsmall' . $css . '">' . $_gd_ . '</div>
                    <div class="jdsmall' . $css . '"><strong>' . $xdata['point'] . '</strong></div>
                    <br class="clear" />
                </div>
            ';

            file_put_contents($member_page_file, $counter . "\t" . trim($team_name) . "\t" . $xdata['played'] . "\t" . $xdata['point'] . "\n", FILE_APPEND);

            if ($counter == 3) {
                $content .= '<!--EKLAE-->';
            }

            $css = $css == 1 ? 2 : 1;
            $zone_start = '';
            $zone_end = '';
            $deduction_point = '0';
            if ($team_id) {
                $klasement_query = "REPLACE INTO dbklasemen (`rank`, `last_rank`, `zone_start`, `zone_end`, `team_id`, `club_name`, `session_id`, `matches_total`, `matches_won`, `matches_draw`, `matches_lost`, `goals_pro`, `goals_against`, `points`, `deduction_points`, `league`, `last_update_time`) VALUES (
        '$counter','$counter','$zone_start','$zone_end','$team_id','$team_name','$season_id','" . $xdata['played'] . "','" . $xdata['win'] . "','" . $xdata['draw'] . "','" . $xdata['loose'] . "','" . $xdata['home_goal'] . "','" . $xdata['away_goal'] . "','" . $xdata['point'] . "','$deduction_point','$v',NOW()
        )";
                $sqlDB->sql_query($klasement_query);
            }
            
            $standing_data[] = array(
                'no' => $counter,
                'club' => $team_name,
                'club_url' => $_standing_club_url_,
                'played' => $xdata['played'],
                'win' => $xdata['win'],
                'draw' => $xdata['draw'],
                'lost' => $xdata['loose'],
                'home_goal' => $xdata['home_goal'],
                'away_goal' => $xdata['away_goal'],
                'point' => $xdata['point'],
                'mark' => $mark,
                'css_rank' => $css_row
            );
            
            $counter++;
        }
        
        //cache
        if ($standing_data) {
            $memcache_obj->set($cache_name, serialize($standing_data), false, 7 * 24 * 3600);
            //file_put_contents($_cache_filename_, serialize($standing_data));
        }
        //echo $cache_name . $k;
        
        /*if($standing_data)
        {
            $memcache_obj = new Memcache;
            bola_memcached_connect($memcache_obj);
            $memcache_obj->set($cache_name, serialize($standing_data), false, 7 * 24 * 3600);
            write_file_direct(TEMPDIR.'klasemen/_bola_standing_data_isc', serialize($standing_data));
            $memcache_obj->close();
        }*/
        
        $topscore = get_ten_topscore('liga_'.$v);
        $topscore = str_replace('topskor_liga_'.$v.'.html', 'topskor_'.$v.'.html', $topscore);

        $additional_text_html = '';
        if ($additional_text) {
            $additional_text_html = '<p><strong>Keterangan</strong><br/>'.$additional_text.'</p>';
        }
        $content .= '
                    <!--ESTANDINGE-->    
                    '.$additional_text_html.'
                    <!--<div class="klasemen_note">
                        <p><span class="note_color champion">&nbsp;</span><span class="note_text">UEFA Champions League</span></p>
                        <p><span class="note_color champion_qual">&nbsp;</span><span class="note_text">Champions League Qualifier</span></p>
                        <p><span class="note_color euro">&nbsp;</span><span class="note_text">UEFA Europa League</span></p>
                        <p style="margin: 0px; width: 90px;"><span class="note_color relegation">&nbsp;</span><span class="note_text" style="width: 65px;">Relegation</span></p>
                        <br class="clear"/>
                    </div>-->
                    <br/>' . $topscore . '
                    <br/>' . $recent_news . '
                </div>
            </div>
        ';
        
        $filename = $schedule_dir . 'klasemen_liga_' . $v . '.html';
        $fileurl = $schedule_url . 'klasemen_liga_' . $v . '.html';

        $metakey = explode(' ', trim(strtolower($metadesc)));
        $metakey = array_unique(array_filter(array_map('trim', $metakey)));
        $metakey = array_slice($metakey, 0, 50);
        $metakey = implode(',', $metakey);
        write_file($filename, $content, $metatitle, $metakey, $metadesc, '', true, 'full', 5);
        insert_property_og($filename, $name_liga . ' ' . $CURRENT_SEASON_YEAR, $fileurl, '', '', $name_liga, 'Jadwal & Skor/Klasemen');
        
        echo generated_link($fileurl);
    }
    $memcache_obj->close();
}

function generate_schedule_klasemen_isc_dev($sqlDB, $echo = true)
{
    global $schedule_url, $schedule_dir, $tv_url, $tv_jadwal_url, $temporary_folder;
     
    //generate topscore first
    //generate_schedule_topscore_isl($sqlDB, $echo);
    
    $cache_name = '_bola_standing_data_4';
    //$_cache_filename_ = $temporary_folder.'klasemen/'.$cache_name;
    
    $CURRENT_SEASON_YEAR = 2017;
    $league_name = 'indonesia';
    
    $array = array(4 => 'indonesia');
    $name_liga = 'Indonesia Liga 1';
    $array_title = array(4 => $name_liga);
    $inggris = '';

    $menu_top = '
        <div class="topmenu">
            <a href="' . $schedule_url . 'score.html">Skor Terkini</a>
            &nbsp;&nbsp;&nbsp;
            <a href="' . $schedule_url . '">Jadwal Pertandingan</a>
            &nbsp;&nbsp;&nbsp;
            <a href="' . $tv_jadwal_url . '">Jadwal TV</a>
            &nbsp;&nbsp;
            <a href="' . $schedule_url . 'klasemen_liga_inggris.html">Klasemen</a>
            &nbsp;&nbsp;
            <a href="' . $schedule_url . 'preview.html">Preview</a>
            &nbsp;&nbsp;
            <a href="' . $schedule_url . 'review.html">Review</a>
        </div>
    ';

    $memcache_obj = new Memcache;
    bola_memcached_connect($memcache_obj);

    foreach ($array as $k => $v) {
        $standing_data = array();
        /*$share = '
            <!--SOCIALTAB-->
            <div id="bl-social-tabs"></div>
            <script type="text/javascript">
            $(document).ready(function() {
                $("#bl-social-tabs").klsocial({
                    url:"' . $schedule_url . 'klasemen_liga_' . $v . '.html",
                    comment_count:"-1"
                });
            });
            </script>
            <!--ENDSOCIALTAB-->
        ';*/

        $additional_text = '';
        switch ($k) {
            case 4: $infotext = '<div style="padding: 5px 0px 0px 0px;">Update terbaru Klasemen ' . $name_liga . $CURRENT_SEASON_YEAR . ' dari pertandingan '. $name_liga .' terakhir. Simak juga daftar <a href="#topskorman" class="bluelink">Top Skor</a> sementara.</div>';
                $metatitle = 'Update Klasemen Liga ' . $name_liga. $CURRENT_SEASON_YEAR . ' Terbaru';
                $metadesc = 'Update terbaru Klasemen Liga ' . $name_liga . $CURRENT_SEASON_YEAR . ' dari Live Score pertandingan '.$name_liga.' terakhir disertai daftar Top Skor';
                break;
            default: $infotext = '';
        }

        $recent_news = '
            <div class="greentitle" style="margin-bottom:10px; margin-top:10px; text-align:left; font-size:18px">
                BERITA TERBARU ' . (strtoupper($array_title[$k])) . '
            </div>
            <ul style="list-style:none; margin:0px; padding:0px">
        ';

        $q = $sqlDB->sql_query("
                    SELECT a.title, a.url, a.schedule, b.category_url FROM dbnews a, dbcategory b
                    WHERE a.category = b.category_id AND a.category = '" . ($k == 27 ? 4 : $k) . "' AND a.level != '0' AND a.schedule <= NOW()
                    ORDER BY a.schedule DESC LIMIT 10");
        while ($row = $sqlDB->sql_fetchrow($q)) {
            $recent_news .= '
                <li style="padding:5px 10px; border-bottom:1px dashed #aaa">
                    <span style="color:#666">(' . date("d-m-Y H:i", strtotime($row["schedule"])) . ')</span>
                    <a href="/' . $row["category_url"] . '/' . $row["url"] . '.html" class="greenlink">' . $row["title"] . '</a>
                </li>
            ';
        }
        
        $recent_news .= '
            </ul>
        ';

        $content = '
            <div class="bigcon">
                <div class="bigcon2">
                    <div class="nav">
                        <a href="/" style="text-decoration:none;">HOME</a> &raquo;
                        KLASEMEN ' . (strtoupper($array_title[$k])) . '
                    </div>
                    ' . $menu_top . '
                    <h1 class="greentitle" style="margin: 0px;">Klasemen ' . (ucwords($array_title[$k])) . '</h1>
                    ' . $infotext . ' 
                    <div class="jdskor1">Pilih Kompetisi: '.dropdown_klasemen_options($v).'</div>
                    <!--SKLAS-->
                    <div class="klasemen_row">
                        <div class="jdsmall1"><strong>No</strong></div>
                        <div class="jdwide1"><span class="mark ">&nbsp;</span><strong>Klub</strong></div>
                        <div class="jdsmall1"><strong>MN</strong></div>
                        <div class="jdsmall1"><strong>M</strong></div>
                        <div class="jdsmall1"><strong>S</strong></div>
                        <div class="jdsmall1"><strong>K</strong></div>
                        <div class="jdsmall1"><strong>MG</strong></div>
                        <div class="jdsmall1"><strong>KG</strong></div>
                        <div class="jdsmall1"><strong>SG</strong></div>
                        <div class="jdsmall1"><strong>Poin</strong></div>
                        <br class="clear" />
                    </div>
                    
                <!--SSTANDINGS-->
        ';

        $qs = "
            SELECT season_id FROM dbseason
            WHERE season_cat_id='$k' AND season_status='1' ORDER BY season_id DESC LIMIT 1";
        $rs = $sqlDB->sql_query($qs, true);
        $rows = $sqlDB->sql_fetchrow($rs);
        $season_id = $rows['season_id'];
        
        generate_schedule_topscore_global($sqlDB, 'liga_indonesia', 'Liga Indonesia', $league_name, $season_id);
        
        $q = "
            SELECT DISTINCT A.team_id, A.team_name FROM dbteam A, dbparticipant B
            WHERE part_season_id='$season_id' AND team_id=part_team_id AND B.part_status<>'0' ORDER BY team_name";
        
        $r = $sqlDB->sql_query($q, true);
        if (!$r) {
            return false;
        }
        
        $tmp_file = LOGDIR . 'klasemen_tmp_file.txt';
        $tmp_file2 = LOGDIR . 'klasemen_tmp_file2.txt';
        if (is_file($tmp_file)) {
            unlink($tmp_file);
        }
        if (is_file($tmp_file2)) {
            unlink($tmp_file2);
        }
        $save_arr = array();
        while ($row = $sqlDB->sql_fetchrow($r)) {
            $club_data = get_schedule_club_data($sqlDB, $row['team_name'], $season_id);
            $ab = $club_data['home_goal'] - $club_data['away_goal'];
            file_put_contents($tmp_file, $row['team_id'] . "\t" . $club_data['point'] . "\t" . $ab . "\t" . $club_data['home_goal'] . "\t" . $row['team_name'] . "\n", FILE_APPEND);
            $save_arr[$row['team_id']] = $club_data;
        }
        exec('cat ' . $tmp_file . ' | sort -k2.1nr -k3.1nr -k4.1nr -k5 > ' . $tmp_file2, $out);
        $sqlDB->sql_freeresult($r);
        $sqlDB->sql_freeresult($rs);
        
        $css = 2;
        $counter = 1;
        $arr_data = array_filter(array_map('trim', file($tmp_file2)));
        // spesial untuk member page
        $member_page_file = LOGDIR . 'member_page_' . $v . '.txt';
        if (is_file($member_page_file)) {
            file_put_contents($member_page_file, "");
        }

        $tmp_point = $is_headtohead = array();
        foreach ($arr_data as $vdata) {
            list($team_id, $team_point, $team_selisih, $away_goal, $team_name) = explode("\t", $vdata);
            if (!array_key_exists($team_point, $tmp_point)) {
                $tmp_point[$team_point]['team'] = $team_name;
                $tmp_point[$team_point]['home_goal'] = (int) $away_goal;
                $tmp_point[$team_point]['away_goal'] = $away_goal - $team_selisih;
            } else {
                if (!array_key_exists($team_point, $is_headtohead)) {
                    $is_headtohead[$team_name] = array($team_name, $tmp_point[$team_point]['team'], $save_arr[$team_id]['home_goal'], $save_arr[$team_id]['away_goal'], $tmp_point[$team_point]['home_goal'], $tmp_point[$team_point]['away_goal']);
                }
            }
        }
        echo '<pre>';
        
        if (count($is_headtohead) > 0) {
            $is_headtohead_arr = $is_headtohead;
            foreach ($is_headtohead_arr as $is_headtohead) {
                $club1 = $is_headtohead[0];
                $club2 = $is_headtohead[1];
                $club1_gf = $is_headtohead[2];
                $club1_ga = $is_headtohead[3];
                $club2_gf = $is_headtohead[4];
                $club2_ga = $is_headtohead[5];
                $headtoheadwinner = generate_schedule_headtohead_indonesia($sqlDB, $season_id, $club1, $club2, $club1_gf, $club1_ga, $club2_gf, $club2_ga);
                $club1_pos = -1;
                $club2_pos = -1;
                $league_pos = 0;
                foreach ($arr_data as $vdata) {
                    list($team_id, $team_point, $team_selisih, $away_goal, $team_name) = explode("\t", $vdata);
                    if ($team_name == $club1) {
                        $club1_pos = $league_pos;
                    }
                    if ($team_name == $club2) {
                        $club2_pos = $league_pos;
                    }
                    $league_pos++;
                }
                reset($arr_data);
                echo $club1 . " - " . $club2 . $headtoheadwinner . " " . $club1_pos . " " . $club2_pos . "<br/>";
                if ($headtoheadwinner == 1 && $club1_pos > $club2_pos) {
                    $switch_tmp = $arr_data[$club1_pos];
                    $arr_data[$club1_pos] = $arr_data[$club2_pos];
                    $arr_data[$club2_pos] = $switch_tmp;
                } elseif ($headtoheadwinner == 2 && $club1_pos < $club2_pos) {
                    $switch_tmp = $arr_data[$club1_pos];
                    $arr_data[$club1_pos] = $arr_data[$club2_pos];
                    $arr_data[$club2_pos] = $switch_tmp;
                }
            }
        }
        print_r($arr_data);

        exit;
        foreach ($arr_data as $k => $vdata) {
            list($team_id, $team_point, $team_selisih, $away_goal, $team_name) = explode("\t", $vdata);
            $xdata = $save_arr[$team_id];
            $team_name = trim($team_name);
            
            $q2 = "SELECT url FROM dbarticles WHERE team_id='$team_id' AND category='16' LIMIT 1";
            $r2 = $sqlDB->sql_query($q2);
            $row2 = $sqlDB->sql_fetchrow($r2);
            
            $_standing_club_url_ = '';
            if ($row2['url']) {
                $_standing_club_url_ = $row2['url'];
            }
            
            $mark = '';
            
            $css_row = 'rank ';
            /*if($STANDING_STATUS)
            {
                $css_row .= isset($STANDING_STATUS[$counter])?$STANDING_STATUS[$counter]:'';
            }*/
            
            $_gd_ = $xdata['home_goal'] - $xdata['away_goal'];
            if ($_gd_ > 0) {
                $_gd_ = '+'.$_gd_;
            } elseif ($_gd_ > 0) {
                $_gd_ = '-'.$_gd_;
            }
            $row2['url'] = isset($row2['url']) ? "/club/".$row2['url'].".html" : '';
            
            $content .= '
                <div class="klasemen_row">
                    <div class="jdsmall' . $css . '"><span class="'.$css_row.'">' . $counter . '</span></div>
                    <div class="jdwide' . $css . '"><span class="mark '.$mark.'">&nbsp;</span><strong><a href="' . $row2['url'] . '" class="greenlink">'.trim($team_name) . '</a></strong></div>
                    <div class="jdsmall' . $css . '">' . $xdata['played'] . '</div>
                    <div class="jdsmall' . $css . '">' . $xdata['win'] . '</div>
                    <div class="jdsmall' . $css . '">' . $xdata['draw'] . '</div>
                    <div class="jdsmall' . $css . '">' . $xdata['loose'] . '</div>
                    <div class="jdsmall' . $css . '">' . $xdata['home_goal'] . '</div>
                    <div class="jdsmall' . $css . '">' . $xdata['away_goal'] . '</div>
                    <div class="jdsmall' . $css . '">' . $_gd_ . '</div>
                    <div class="jdsmall' . $css . '"><strong>' . $xdata['point'] . '</strong></div>
                    <br class="clear" />
                </div>
            ';

            //file_put_contents($member_page_file, $counter . "\t" . trim($team_name) . "\t" . $xdata['played'] . "\t" . $xdata['point'] . "\n", FILE_APPEND);

            if ($counter == 3) {
                $content .= '<!--EKLAE-->';
            }

            $css = $css == 1 ? 2 : 1;
            $zone_start = '';
            $zone_end = '';
            $deduction_point = '0';
            if ($team_id) {
                $klasement_query = "REPLACE INTO dbklasemen (`rank`, `last_rank`, `zone_start`, `zone_end`, `team_id`, `club_name`, `session_id`, `matches_total`, `matches_won`, `matches_draw`, `matches_lost`, `goals_pro`, `goals_against`, `points`, `deduction_points`, `league`, `last_update_time`) VALUES (
        '$counter','$counter','$zone_start','$zone_end','$team_id','$team_name','$season_id','" . $xdata['played'] . "','" . $xdata['win'] . "','" . $xdata['draw'] . "','" . $xdata['loose'] . "','" . $xdata['home_goal'] . "','" . $xdata['away_goal'] . "','" . $xdata['point'] . "','$deduction_point','$v',NOW()
        )";
                //$sqlDB->sql_query($klasement_query);
            }
            
            $standing_data[] = array(
                'no' => $counter,
                'club' => $team_name,
                'club_url' => $_standing_club_url_,
                'played' => $xdata['played'],
                'win' => $xdata['win'],
                'draw' => $xdata['draw'],
                'lost' => $xdata['loose'],
                'home_goal' => $xdata['home_goal'],
                'away_goal' => $xdata['away_goal'],
                'point' => $xdata['point'],
                'mark' => $mark,
                'css_rank' => $css_row
            );
            
            $counter++;
        }
        exit;
        
        //cache
        if ($standing_data) {
            //$memcache_obj->set($cache_name, serialize($standing_data), false, 7 * 24 * 3600);
            //file_put_contents($_cache_filename_, serialize($standing_data));
        }
        //echo $cache_name . $k;
        
        /*if($standing_data)
        {
            $memcache_obj = new Memcache;
            bola_memcached_connect($memcache_obj);
            $memcache_obj->set($cache_name, serialize($standing_data), false, 7 * 24 * 3600);
            write_file_direct(TEMPDIR.'klasemen/_bola_standing_data_isc', serialize($standing_data));
            $memcache_obj->close();
        }*/
        
        $topscore = get_ten_topscore($v);
        $topscore = str_replace('topskor_liga_'.$v.'.html', 'topskor_'.$v.'.html', $topscore);

        $additional_text_html = '';
        if ($additional_text) {
            $additional_text_html = '<p><strong>Keterangan</strong><br/>'.$additional_text.'</p>';
        }
        $content .= '
                    <!--ESTANDINGE-->    
                    '.$additional_text_html.'
                    <!--<div class="klasemen_note">
                        <p><span class="note_color champion">&nbsp;</span><span class="note_text">UEFA Champions League</span></p>
                        <p><span class="note_color champion_qual">&nbsp;</span><span class="note_text">Champions League Qualifier</span></p>
                        <p><span class="note_color euro">&nbsp;</span><span class="note_text">UEFA Europa League</span></p>
                        <p style="margin: 0px; width: 90px;"><span class="note_color relegation">&nbsp;</span><span class="note_text" style="width: 65px;">Relegation</span></p>
                        <br class="clear"/>
                    </div>-->
                    <br/>' . $topscore . '
                    <br/>' . $recent_news . '
                </div>
            </div>
        ';
        echo $content;
        exit;
        $filename = $schedule_dir . 'klasemen_liga_' . $v . '.html';
        $fileurl = $schedule_url . 'klasemen_liga_' . $v . '.html';

        $metakey = explode(' ', trim(strtolower($metadesc)));
        $metakey = array_unique(array_filter(array_map('trim', $metakey)));
        $metakey = array_slice($metakey, 0, 50);
        $metakey = implode(',', $metakey);
        write_file($filename, $content, $metatitle, $metakey, $metadesc, '', true, 'full', 5);
        insert_property_og($filename, $name_liga . ' ' . $CURRENT_SEASON_YEAR, $fileurl, '', '', $name_liga, 'Jadwal & Skor/Klasemen');
        
        echo generated_link($fileurl);
    }
    $memcache_obj->close();
}
/**
 * Generate top skore for another leage ( euro 2016, sea games, copa america, isc )
 * And view 10 top score
 */
function generate_schedule_topscore_global($sqlDB, $url, $league_title, $db_country, $SEASON_ID = 0)
{
    global $schedule_url, $schedule_dir, $tv_url, $tv_jadwal_url;
    
    $nama_club = ($db_country == 'indonesia') ? 'player_club' : 'player_nationality';
    $v = $url;
    $menu_top = '
        <div class="topmenu">
            <a href="' . $schedule_url . 'score.html">Skor Terkini</a>
            &nbsp;&nbsp;&nbsp;
            <a href="' . $schedule_url . '">Jadwal Pertandingan</a>
            &nbsp;&nbsp;&nbsp;
            <a href="' . $tv_jadwal_url . '">Jadwal TV</a>
            &nbsp;&nbsp;
            <a href="' . $schedule_url . 'klasemen_liga_inggris.html">Klasemen</a>
            &nbsp;&nbsp;
            <a href="' . $schedule_url . 'preview.html">Preview</a>
            &nbsp;&nbsp;
            <a href="' . $schedule_url . 'review.html">Review</a>
        </div>
    ';

    $content = '
        <div class="bigcon">
            <div class="bigcon2">
                <div class="nav">
                    <a href="/" style="text-decoration:none;">HOME</a> &raquo;
                    TOP SKOR ' . (strtoupper($league_title)) . '
                </div>
                ' . $menu_top . '
                <br/>
                <div class="greentitle">Top Skor ' . (ucwords($league_title)) . '</div>
                <br/>
    ';

    $tmp_file = BOLADIR . 'topscore_tmp_file.txt';
    $tmp_file2 = BOLADIR . 'topscore_tmp_file2.txt';
    if (is_file($tmp_file)) {
        unlink($tmp_file);
    }
    if (is_file($tmp_file2)) {
        unlink($tmp_file2);
    }

    /*$q = "(SELECT CONCAT(home, home2) as team_name FROM dbschedule
            WHERE country LIKE '$db_country%' AND idseason='$SEASON_ID' AND level<>'0')
            UNION
            (SELECT CONCAT(away, away2) as team_name FROM dbschedule
            WHERE country LIKE '$db_country%' AND idseason='$SEASON_ID' AND level<>'0')";  */    
    $q = "
            SELECT A.team_id, A.team_name FROM dbteam A, dbparticipant B
            WHERE part_season_id='$SEASON_ID' AND team_id=part_team_id AND B.part_status<>'0'
            ORDER BY team_name";
    $r = $sqlDB->sql_query($q, true);
    while ($row = $sqlDB->sql_fetchrow($r)) {
        $qsc = "SELECT * FROM dbschedule WHERE (home='$row[team_name]' OR home2='$row[team_name]') AND idseason='$SEASON_ID' AND country LIKE '$db_country' AND level='2' AND schedule<>'0000-00-00 00:00:00'";
        $rsc = $sqlDB->sql_query($qsc, true);
        while ($rowsc = $sqlDB->sql_fetchrow($rsc)) {
            $qd = "SELECT * FROM dbscore WHERE id_schedule='$rowsc[id]' AND info='goal' AND level='1'";
            $rd = $sqlDB->sql_query($qd, true);
            while ($rowd = $sqlDB->sql_fetchrow($rd)) {
                $is_my_team = '';

                /* get player club from player_profile (case midseason player transfer) [2013-08-27] */
                $rowd['player_name'] = kln_real_escape_string($rowd['player_name']);
                $qplayerclub = "SELECT B.team_name as player_club FROM player_profile A JOIN dbteam B ON A.$nama_club = B.team_id WHERE (player_name='{$rowd['player_name']}' OR player_fullname='{$rowd['player_name']}') AND player_status='1' LIMIT 1";

                $rplayerclub = $sqlDB->sql_query($qplayerclub, true);
                while ($rowplayerclub = $sqlDB->sql_fetchrow($rplayerclub))
                {
                    $is_my_team = (isset($rowplayerclub['player_club']) && $rowplayerclub['player_club']) ? str_replace(' ', '_', $rowplayerclub['player_club']) : '';
                }
                /* end of midseason transfer */
                
                if (!$is_my_team) {
                    $is_my_team = ($rowd['home'] == 1) ? $rowsc['home'] . $rowsc['home2'] : $rowsc['away'] . $rowsc['away2'];
                }
                file_put_contents($tmp_file, str_replace(' ', '_', $rowd['player_name']) . "\t" . str_replace(' ', '_', $is_my_team) . "\n", FILE_APPEND);
            }
            $sqlDB->sql_freeresult($rd);
        }
        $sqlDB->sql_freeresult($rsc);
    }   
    $sqlDB->sql_freeresult($r);

    exec('cat ' . $tmp_file . ' | awk \'{data[$1]+=1;kelub[$1]=$2;} END {for (x in data) {print x "\t" data[x] "\t" kelub[x]}}\' | sort -k2nr > ' . $tmp_file2);
    $data = array_filter(array_map('trim', file($tmp_file2)));
    $count = 2;
    $counter = 1;
    $content .= '
        <div class="jdtop1"><strong>No</strong></div>
        <div class="jdtopa1"><strong>Nama</strong></div>
        <div class="jdtop1"><strong>Goal</strong></div>
        <div class="jdtopa1"><strong>Klub</strong></div>
        <div class="clear"></div>
    ';
    
    $topscore_data = array();
    $cache_topscore = '';
    foreach ($data as $vdata) {
        list($player, $goal, $team) = explode("\t", $vdata);
        //echo $player . $goal . "<br/>";
        $player = str_replace('_', ' ', $player);
        $team = str_replace('_', ' ', $team);

        $qp = "SELECT player_url FROM player_profile WHERE player_name='" . kln_real_escape_string($player) . "' LIMIT 1";
        $rp = $sqlDB->sql_query($qp, true) or die(__LINE__ . ' = ' . mysql_error());
        $rowp = $sqlDB->sql_fetchrow($rp);

        $content .= '
            <div class="jdtop' . $count . '">' . $counter . '</div>
            <div class="jdtopa' . $count . '"><a href="/profile/' . $rowp['player_url'] . '/" class="greenlink">' . stripslashes($player) . '</a></div>
            <div class="jdtop' . $count . '">' . $goal . '</div>
            <div class="jdtopa' . $count . '">' . $team . '</div>
            <div class="clear"></div>
        ';
        $cache_topscore .= stripslashes($player) . "\t$goal\t$team\t" . $rowp['player_url'] . "\n";

        $count = $count == 1 ? 2 : 1;
        $counter++;

        $sqlDB->sql_freeresult($rp);
        
        $topscore_item = array();
        $topscore_item['player_name'] = $player;
        $topscore_item['player_club'] = $team;
        $topscore_item['total_goal'] = $goal;
        $topscore_data[] = $topscore_item;
    }

    file_put_contents(CACHEDIR . 'cache_topscore_' . md5($v), $cache_topscore);
    file_put_contents(CACHEDIR . 'xcache_topscore_' . md5($v), $cache_topscore);

    $content .= '
                <br/>
            </div>
        </div>
    ';

    $filename = $schedule_dir . 'topskor_' . $v . '.html';
    $fileurl = $schedule_url . 'topskor_' . $v . '.html';

    if ($topscore_data) {
        $memcache_obj = new Memcache;
        bola_memcached_connect($memcache_obj);
        $memcache_obj->set('bolanet_topskor_'.$v, serialize($topscore_data), false, 7 * 24 * 3600);
        write_file_direct(TEMPDIR.'topscore/bolanet_topskor_'.$v, serialize($topscore_data));
        $memcache_obj->close();
    }
    
    write_file($filename, $content, 'Top Skor Klasemen '.$league_title.'', 'Top Skor Klasemen Liga Inggris, Top Skor Liga Italia, Top Skor Liga Spanyol', 'Top Skor Klasemen Liga Inggris, Top Skor Liga Italia, Top Skor Liga Spanyol', '', true, 'full', 5);
    echo generated_link($fileurl);
}
/** 16 besar
 * seminfinal, final */
function generate_klasemen_euro2016_big16($sqlDB)
{
    global $schedule_url, $schedule_dir, $tv_url, $tv_jadwal_url;

    //$CURRENT_SEASON_YEAR = 2016;
    $v = 'euro_2016';
    $league_title = 'Euro 2016';
    $array_champions = range('A', 'F');
    $db_country = 'Prancis';
    $db_title = array('Euro 2016 Final', 'Euro 2016 Semi Final', 'Euro 2016 Perempat Final', 'EURO 2016 16 Besar' );
    
    
    $menu_top = '
        <div class="topmenu">
            <a href="' . $schedule_url . 'score.html">Skor Terkini</a>
            &nbsp;&nbsp;&nbsp;
            <a href="' . $schedule_url . '">Jadwal Pertandingan</a>
            &nbsp;&nbsp;&nbsp;
            <a href="' . $tv_jadwal_url . '">Jadwal TV</a>
            &nbsp;&nbsp;
            <a href="' . $schedule_url . 'klasemen_liga_inggris.html">Klasemen</a>
            &nbsp;&nbsp;
            <a href="' . $schedule_url . 'preview.html">Preview</a>
            &nbsp;&nbsp;
            <a href="' . $schedule_url . 'review.html">Review</a>
        </div>
    ';

    $recent_news = '
        <div class="greentitle" style="margin-bottom:10px; margin-top:10px; text-align:left; font-size:18px">
            BERITA TERBARU ' . (strtoupper($league_title)) . '
        </div>
        <ul style="list-style:none; margin:0px; padding:0px">
    ';

    $recent_news_arr = get_related_news_league_bytag($sqlDB, '5934');

    foreach ($recent_news_arr as $row) {
        $recent_news .= '
            <li style="padding:5px 10px; border-bottom:1px dashed #aaa">
                <span style="color:#666">(' . date("d-m-Y H:i", strtotime($row["schedule"])) . ')</span>
                <a href="/' . $row["category_url"] . '/' . $row["url"] . '.html" class="greenlink">' . $row["title"] . '</a>
            </li>
        ';
    }

    $recent_news .= '
        </ul>
    ';

    /*$share = '
        <!--SOCIALTAB-->
            <div id="bl-social-tabs"></div>
            <script type="text/javascript">
                $(document).ready(function() {
                                    $("#bl-social-tabs").klsocial({
                                            url:"' . $schedule_url . 'klasemen_'.$v.'html",
                                            comment_count:"-1"
                                    });
                });
            </script>
        <!--ENDSOCIALTAB-->
    ';*/

    $content = '
        <div class="bigcon">
            <div class="bigcon2">
                <div class="nav">
                    <a href="/" style="text-decoration:none;">HOME</a> &raquo;
                    KLASEMEN ' . (strtoupper($league_title)) . '
                </div>
                ' . $menu_top . '
                <h1 class="greentitle" style="margin: 0px;">Klasemen ' . (ucwords($league_title)) . '</h1>
                <div style="padding: 5px 0px 0px 0px;">Update terbaru ' . (ucwords($league_title)) . ' dari pertandingan ' . (ucwords($league_title)) . '.</div>
                <div class="jdskor1">
                                    Pilih Kompetisi: '.dropdown_klasemen_options($v).'
                </div>
                <!--SKLAS-->
    ';

    $season_id = 57;
    $standing_data = array();

    $unique_random_id = 0;
    $today = date("Y-m-d H:i:s");
    
    generate_schedule_topscore_global($sqlDB, $v, $league_title, $db_country, $season_id);
    foreach ($db_title as $ac) {
        $q = "SELECT home, home2, away, away2, schedule, kickoff, goal_home, goal_away FROM dbschedule
        WHERE title='$ac' AND country='$db_country' AND level<>'0'";

        $r = $sqlDB->sql_query($q, true);
        
        if (!$r) {
            return false;
        }
        $title_klasemen = str_replace(array("Euro 2016","EURO 2016"), "", $ac);
        $content .= '<div class="knockout-box">
                <div class="greentitle" style="text-align: left;">Babak '.$title_klasemen.'</div>
                ';
        while ($row = $sqlDB->sql_fetchrowset($r)) {
            $content .= '<div class="knockout-list">
                <table class="table-knockout"><tbody>';
            foreach ($row as $row) {
                $day = get_day_ind($row['schedule']);
            
                $date = preg_replace('/\:\d\d$/', '', $row['schedule']);
                $date = preg_replace('/(\d{4,4})\-(\d{2,2})\-(\d{2,2}) (\d{2,2})\:(\d{2,2})/', '\3-\2-\1', $date);
                $date = str_replace('2016', '16', $date);
                $date = str_replace('-', '/', $date);
                
                $time = preg_replace('/\:\d\d$/', '', $row['schedule']);
                $time = preg_replace('/(\d{4,4})\-(\d{2,2})\-(\d{2,2}) (\d{2,2})\:(\d{2,2})/', '\4:\5', $time);
                
                if ($row['schedule'] <= $today) {
                    $goal = $row['goal_home']."-".$row['goal_away'];
                } else {
                    $goal = '';
                }
                $data = (($goal != '') ? $goal : $time);
                $row['home'] = !empty($row['home']) ? $row['home'] : $row['home2'];
                $row['away'] = !empty($row['away']) ? $row['away'] : $row['away2'];
                $content .= '
                            <tr>
                                <td class="day text-left">'.$day.'</td>
                                <td class="date">'.$date.'</td>
                                <td class="klub-box text-right"><span class="klub">'.$row['home'].'</span></td>
                                <td class="time text-center">'.$data.'</td>
                                <td class="klub-box text-left"><span class="klub">'.$row['away'].'</span></td>
                            </tr>';
                $standing_data[$ac][] = array('day' => $day, 'date' => $date, 'home' => $row['home'], 'away'=> $row['away'], 'data' => $data);
            }
            $content .= '</table></tbody></div>';
        }
        $content .= '</div>';
    }

    $content .= '<style>.knockout-list {border-top: 16px solid #F4F4F4 !important;}</style>';
    if ($standing_data) {
        $memcache_obj = new Memcache;
        bola_memcached_connect($memcache_obj);
        $memcache_obj->set('bolanet_standing_'.$v.'_final', serialize($standing_data), false, 7 * 24 * 3600);
        write_file_direct(TEMPDIR.'klasemen/bolanet_standing_'.$v.'_final', serialize($standing_data));
        $memcache_obj->close();
    }

    $topscore = get_ten_topscore($v);
    $topscore = str_replace('topskor_liga_'.$v.'.html', 'topskor_'.$v.'.html', $topscore);
    $content .= '
                <br/><br/>' . $topscore . '
                <br/>' . $recent_news . '
            </div>
        </div>
    ';

    $filename = $schedule_dir . 'klasemen_' . $v . '.html';
    $fileurl = $schedule_url . 'klasemen_' . $v . '.html';
    
    $metatitle = 'Update Klasemen '.$league_title.' Terbaru';
    $metadesc = 'Update terbaru Klasemen '.$league_title.' dari Live Score pertandingan '.$league_title.' terakhir disertai daftar Top Skor';
    $metakey = explode(' ', trim(strtolower($metadesc)));
    $metakey = array_unique(array_filter(array_map('trim', $metakey)));
    $metakey = array_slice($metakey, 0, 50);
    $metakey = implode(',', $metakey);

    write_file($filename, $content, $metatitle, $metakey, $metadesc, '', true, 'full', 5);
    insert_property_og($filename, 'Daftar Klasemen '.$league_title, $fileurl, '');
    echo generated_link($fileurl);
}
