<?php

/**
 * generate news per id
 *  - related player, photo, wallpaper
 *  - related news, latest news
 *  - generate comment page(s) if total comment > 3
 *  - create printable news
 *  - add share link from addthis
 *
 * @url: http://www.bola.net/$category/$title.html
 */
function generate_per_id($sqlDB, $id, $message = true, $match_submenu = '', $devel = false)
{
    global $headline_media, $headline_media_url, $thumbnail_media, $thumbnail_media_url, $tag_url, $library_url, $cdn_url, $library_dir, $def_liga_eropa, $def_bola_dunia, $def_olahraga_lain, $def_bola_indonesia, $BALLBALL_CAT_CODES, $img_lazy_load, $image_library_dir, $image_library_url, $_PROPS_;
    
    /* check in schedule preview review */
    $qs = "SELECT id FROM dbschedule WHERE news_preview = '$id' OR news_review = '$id' LIMIT 1";
    $rs = $sqlDB->sql_query($qs, true) or die(__LINE__ . ' = ' . mysql_error());
    while ($rows = $sqlDB->sql_fetchrow($rs)) {
        $schedule_id = isset($rows['id']) ? $rows['id'] : '';

        if ($schedule_id && !$match_submenu) {
            if (!function_exists('generate_schedule_score_byid')) {
                include FUNCTIONDIR . "function.schedule.php";
            }
            generate_schedule_score_byid($sqlDB, $schedule_id);
            return '';
        }
    }

    $sql = "SELECT dbnews.*, category_name, category_url
            FROM dbnews,dbcategory
            WHERE idnews = '$id' AND schedule <> '00-00-0000 00:00:00' AND dbnews.category = dbcategory.category_id
            LIMIT 1";

    $res = $sqlDB->sql_query($sql, true) or die(__LINE__ . ' = ' . mysql_error());
    if (!$res) {
        if ($message) {
            echo __LINE__ . ' = ' . mysql_error();
        }
        return false;
    }
    
    $dir = getAllNewsCat($sqlDB);

    // euro include
    if (!function_exists("euro_get_header")) {
        include(FUNCTIONDIR . "function.euro.php");
        include(FUNCTIONDIR . "function.euro.news.php");
        include(FUNCTIONDIR . "function.euro.pernik.php");
    }

    while ($row = $sqlDB->sql_fetchrow($res)) {
        // commercial tidak di generate
        if ($row['category'] == '34') {
            continue;
        }
        /* open play */
        if ($row['category'] == '28') {
            if (!function_exists('openplay_gen_byid')) {
                include(FUNCTIONDIR . 'function.openplay.php');
            }
            openplay_gen_byid($sqlDB, $row, 0, $dir, $message);

            // euro pernik
            //if($row['category'] == 28 && strtolower($row['keyword1']) == 'euro 2012')
            //{
            //    euro_pernik_byid($row['idnews'], $sqlDB);
            //}
            continue;
        }
        /* end of open play */

        /* video unik */
        if ($row['category'] == '33') {
            if (!function_exists('videounik_gen_byid')) {
                include(FUNCTIONDIR . 'function.videounik.php');
            }
            videounik_gen_byid($sqlDB, $row, 0, $dir, $message);

            continue;
        }
        /* end of open play */
// hery edit

        if ($devel == false) {
            $filename = BOLADIR . $dir[$row['category']][1] . '/' . $row['url'] . '.html';
            $fileurl = BOLAURL . $dir[$row['category']][1] . '/' . $row['url'] . '.html';
            $filename_print = BOLAURL . $dir[$row['category']][1] . '/print/' . str_pad($id, 10, '0', STR_PAD_LEFT) . '.html';
        } else {
            $filename = APPSDIR . 'devel/generate/www/news/' . $dir[$row['category']][1] . '/' . $row['url'] . '.html';
            $fileurl = APPSURL . 'devel/generate/www/news/' . $dir[$row['category']][1] . '/' . $row['url'] . '.html';
            $filename_print = APPSURL . 'devel/generate/www/news/' . $dir[$row['category']][1] . '/print/' . str_pad($id, 10, '0', STR_PAD_LEFT) . '.html';
        }


        //reformat schedule
        list($date, $time) = explode(" ", $row['schedule']);
        list($year, $month, $day) = explode("-", $date);
        list($hour, $minute, $second) = explode(":", $time);
        $datetime = $day . '-' . $month . '-' . $year . ' ' . $hour . ':' . $minute;

        // when FB comment was started
        $comment_form = '';
        $link_comment = '';
        $all_comment = '';
        $box_blue_comment = '';
        $titlecomment = '';

        $comment_form = '
            <br/>
            <div id="news_comment" class="box_related">
                <div class="rel_header" style="border:none">
                    Komentar Anda
                </div>
            </div>
        ';
        $comment_form .= fb_comment_form($fileurl, 5, 468);
        $comment_form = ($row['news_sensitive'] == 1) ? '' : $comment_form ;
        $comment_ticker_info = '';

        $addthis = '
            <script type="text/javascript">
                $(document).ready(function() {
                    $(".no3").hover(
                        function() {
                            $(".newshid").slideDown(400);
                        },
                        function() {
                            return false;
                        }
                    );
                    im("#kvy");
                    recomm_view("' . $row['idnews'] . '|news");
                });
            </script>
        ';

        $share = '
            <!--SOCIALTAB-->
            <link rel="stylesheet" href="'.APPSURL_KL.'v5/css/plugin.socmed.css">
            <script type="text/javascript" src="'.APPSURL_KL.'v5/js/plugin.socmed.js?v1.6"></script>
            <div id="kl-social-share"></div>
            <!--ENDSOCIALTAB-->
        ';

        //comment_count:'.($is_facebook_comment === false ? 'total' : '-1').'

        $meta_og_image = '';

        /* show related image */
        $content_image = '';
        if ($row['image'] != '') {
            $row['image'] = str_replace($library_url, '/', $row['image']);
            $news_image = $cdn_url . 'library/' . str_replace('/p/', 'p/', $row['image']);
            $content_image .= '
                <div class="imgboxright">
                    <img src="' . $news_image . '" alt="' . $row['imageinfo'] . '" /><br />' . $row['imageinfo'] . '
                </div>
            ';
            $meta_og_image = $news_image;
        }

        /* headline image */
        $headline_image = '';
        $headline_image_file = ((strlen($row['image_headline']) == 14) ? $headline_media. $row['image_headline'] : $image_library_dir.$row['image_headline']);
        //if (is_file($headline_image_file))
        if ($row['image_headline']) {
            $headline_image = ((strlen($row['image_headline']) == 14) ? $headline_media_url. $row['image_headline'] : $image_library_url.$row['image_headline']);
            //$meta_og_image = $thumbnail_media_url. $row['image_headline']; //str_pad($row['idnews'], 10, '0', STR_PAD_LEFT) .'.jpg';
            $meta_og_image = $headline_image;
            $content_image = '';
        }

        /* persib news */
        $persib_mark = $persib_source = '';
        if (strtolower($row['source']) == 'persib.co.id') {
            $headline_image = persibnews_image_url($row['image_headline']);
            $meta_og_image = persibnews_image_url($row['image_headline'], "320");

            $persib_mark = '';
            $persib_source = '
        <p style="text-align:left;font-style:italic;margin-top:0;">
        Sumber: 
        <a href="' . $row['source_url'] . '" target="_blank" class="bluelink">
            Persib.co.id
        </a>
        </p>
        ';
            $row['title'] = str_replace('PERSIB', 'Persib', $row['title']);
            $row['news'] = str_replace('PERSIB', 'Persib', $row['news']);
        }

        /* news paging */
        $is_paging = false;
        $idpaging = 0;
        $paging_order = '';

        $np_navrows = array();
        $paging_top_nav = '';
        $paging_nav = '';
        $paging_js = ''; //'<script type="text/javascript" src="'.APPSURL2.'assets/js/paging.js?c"></script>';
        $paging_nav_js = '';
        $paging_btn = '';

        if ($row['is_pagging'] == 1) {
            $paging_js = '<script type="text/javascript" src="' . APPSURL2 . 'assets/js/min/single/1.0/paging2.js"></script>';

            //get paging options
            $npq = "SELECT id, `order` FROM dbpagging WHERE relation_id = '{$row['idnews']}' AND type_key = '1'";
            $res_npq = $sqlDB->sql_query($npq, true) or die(__LINE__ . ' = ' . mysql_error());
            if ($res_npq) {
                $npq_row = $sqlDB->sql_fetchrow($res_npq);
                $idpaging = isset($npq_row['id']) ? $npq_row['id'] : '';
                $np_order = isset($npq_row['order']) ? $npq_row['order'] : 0;
                if ($np_order != 1) {
                    $np_order = 0;
                    $paging_order = "ASC";
                } else {
                    $paging_order = "DESC";
                }

                //get paging detail
                if ($idpaging) {
                    $npq = "SELECT id, no, title FROM dbpagging_detail WHERE idpagging = '$idpaging' AND status = '1' ORDER BY `no` $paging_order";
                    $res_npq = $sqlDB->sql_query($npq, true);
                    if ($res_npq) {
                        $np_count = $sqlDB->sql_numrows($res_npq);

                        $current = 0;
                        $next_url = get_paging_url($fileurl, '1');
                        if ($np_order) {
                            $next_url = get_paging_url($fileurl, $np_count);
                            $current = $np_count + 1;
                        }
                        $paging_top_nav = get_paging_top_nav($fileurl, $next_url);

                        $np_navrows = $sqlDB->sql_fetchrowset($res_npq);
                        $start = $np_navrows[0]['no'];
                        $start_name = $np_navrows[0]['title'];
                        $paging_nav = get_paging_nav($np_navrows, $fileurl, 0);

                        $paging_nav_js = '
                            <script type="text/javascript">
                                $(function(){
                                    BolaPaging.init({
                                        current: ' . $current . ',
                                        max: ' . $np_count . ',
                                        order: ' . $np_order . ',
                                        url: "' . $fileurl . '",
                                        container: "#newspaging_content",
                                        contentid: ' . $row['idnews'] . '
                                    });
                                });
                            </script>                            
                        ';
                        if ($row['idnews'] != '97090') {
                            //$paging_nav_js = ''; // disable ajax
                        }
                        $start_url = str_replace('.html', '-' . $start . '.html', $fileurl);
                        $paging_btn = '
                            <br/>
                            <div class="np_link_nav">
                                <a class="np_next" href="' . $start_url . '" class="first" onclick="_gaq.push([\'_trackEvent\', \'Paging - Bottom\', \'click\', \'' . $start_url . '\']);">' . ($start_name ? 'Mulai dari ' . $start_name : 'MULAI DENGAN NO. ' . $start) . '</a>
                            </div>                        
                        ';

                        $is_paging = true;
                    }
                }
            }
        }

        /* Send to Friends [2012-05-22] */
        $s2f_title = addslashes($row['title']);
        $s2f_player = addslashes($row['celebrity']);
        $s2f_synopsis = addslashes(str_replace(array("\n", "\r"), "", $row['synopsis']));
        $s2f = "
            <script type='text/javascript'>            
            $(document).ready(function() {
                s2f.init({
                    s2f1: {
                        element: '#sendfriendsbtn',
                        news_id: '{$row['idnews']}',
                        news_type: 'news',
                        title: '$s2f_title',
                        date: '$datetime',
                        player: '$s2f_player',
                        synopsis: '$s2f_synopsis'
                    }
                });
            });
            </script>
        ";
        
        /* subtitle and tag subtitle */
        $subtitle = isset($row['subtitle']) ? $row['subtitle'] : '';
        $tag_subtitle = ($row['tag_subtitle'] != 0) ? $row['tag_subtitle'] : '';
        $url_tag_subtitle = isset($tag_subtitle) ? BOLAURL.'tag/'.getURLTag($sqlDB, $tag_subtitle, '1') : '#';
        $htmlsub = '';
        if ($subtitle) {
            $htmlsub .= '<p class="subtitle"><a href="'.$url_tag_subtitle.'" ' . ga_trackEvent('subtitle', $subtitle) . '>'.$subtitle.'</a></p>';
        }
        /* eof subtitle and tag subtitle */
        
        /* top content */
        $content_top = get_bola_content_tracker($row['idnews'], 'news', $fileurl).$s2f;
        if ($is_paging) {
            $content_top .= $paging_js;
        }
        $content_top .= '
            <div class="bigcon">
                <div class="bigcon2">
                    <div class="nav">
                        <a ' . ga_trackEvent('link_nav') . ' href="/" style="text-decoration:none;">HOME</a> &raquo;
                        <a ' . ga_trackEvent('link_nav') . ' href="/' . $row['category_url'] . '/" style="text-decoration:none;">' . strtoupper($row['category_name']) . '</a> &raquo;
                        ' . strtoupper($row['title']) . '
                    </div>
                    <%HEADER_ADVERTORIAL%>
                    <%HEADER_CONTENT%>
                    ' . $htmlsub . '
                    ' . $match_submenu . '
                    <h1 class="newstitle news">' . $row['title'] . '</h1>
                    <div class="newsdatetime" style="float: left;">' . $datetime . '</div>
                    ' . (isset($row['celebrity']) && $row['celebrity'] ? '<h2 style="margin: 0px; float: left;" class="newsdatetime">&nbsp;| ' . $row['celebrity'] . '</h2>' : '') . '
                    
            <div class="clear"></div>
            ' . $persib_mark . '
            <div class="clear"></div>
        ';
        $content_top .= $share;

        // if timo, show header
        $header_content = '';
        // tags
        $desc_tags = '';
        $suggestion_tags = '';
        $advetorial_header = '';
        $_21_keatas = '';
        $first_tag = array();
        $generatetags_detail = array();
        $qtags = "
            SELECT A.tags_id, A.tags_name, A.tags_url
            FROM `dbtags` A INNER JOIN dbtags_assosiation B ON A.tags_id=B.tags_id
            WHERE A.tags_level='1' AND B.idnews='$id'";
        
        $tags_content = '';
        if ($rtags = $sqlDB->sql_query($qtags, true)) {
            while ($rowtags = $sqlDB->sql_fetchrow($rtags)) {
                if (!$first_tag) {
                    $first_tag = $rowtags;
                }
                $generatetags_detail[] = $rowtags['tags_id'];
                $tags_content .= '
                <li>
                    <a href="' . $tag_url . $rowtags['tags_url'] . '/" ' . ga_trackEvent('tag', stripslashes($rowtags['tags_name'])) . '>' . stripslashes($rowtags['tags_name']) . '</a>
                </li>
                ';

                if ($rowtags['tags_id'] == '5218') {
                    $header_content = get_pengamat_header();
                }
                if ($rowtags['tags_id'] == '9275') {
                    $comment_form = $comment_ticker_info = '';
                    $advetorial_header = '<br/><span style="background:#9BCC01;border-radius:4px;-moz-border-radius:4px;padding: 5px 10px; color:#fff;display:inline-block;font-size:16px;font-weight:bold;">Advertorial</span>';
                }
                $suggestion_tags .= $rowtags['tags_id'].',';
                $desc_tags[] = $rowtags['tags_name'];
                
                // add 21 ke atas in meta keywords
                if ($rowtags['tags_id'] == '15302') {
                    $comment_form = $comment_ticker_info = '';
                    $_21_keatas = $rowtags['tags_name'].", ";
                }
                // end 21 ke atas in meta keywords
            }
            $sqlDB->sql_freeresult($rtags);

            if ($tags_content) {
                $tags_content = '
                    <!-- tag v2 -->
                    <div class="box-tag">
                        <img src="'.KLIMGURL.'library/m/icon/icon-tag-v2.png" class="img-tag"/>
                        <ul class="list-tag clearfix">
                        ' . $tags_content . '
                        </ul>
                    </div>
                    <!-- end tag v2 -->
                ';
            }
        }

        $content_top = str_replace('<%HEADER_CONTENT%>', $header_content, $content_top);
        $content_top = str_replace('<%HEADER_ADVERTORIAL%>', $advetorial_header, $content_top);
        
        /* main content */
        $content_main = $paging_top_nav;
        if ($headline_image) {
            if ($row['idnews'] == 247249 || $row['idnews'] == 247247) {
                $headline_image .= '?dwadwa';
            }
            
            $content_main .= '
                <div class="news-headline-image news">
                    <img class="lazy_loaded" src="'.$img_lazy_load.'" data-src="' . $headline_image . '" width="468" alt="' . htmlentities($row['title']) . '" />
                    ' . ($row['imageinfo'] ? '<div>' . str_replace('(c)', '&copy;', $row['imageinfo']) . '</div>' : '') . '
                </div>
            ';
        }
        $content_main .= $paging_nav;
        $content_main .= $content_image;


        // hard hard hard
        $row['news'] = preg_replace('/^\<b\>KapanLagi\.com\<\/b\>/si', '<b>Bola.net</b>', $row['news']);


        $row['news'] = putProfileLink($row['news'], $row['celebrity'], $row['celebrity1'], $row['celebrity2'], $row['celebrity3'], $row['celebrity4'], $row['celebrity5']);
        $row['news'] = putClubLink($row['news'], $row['club1'], $row['club2'], $row['club3']);

        # embed sosmed [2015-11-03]-------------------------------------------------------------------------------
        //echo "here";
        $news = $row['news'];
        preg_match_all('/sosmed\_([0-9]+)/', $news, $data);
        //print_r($data);
        $id_all = implode("','", $data[1]);
        //echo "SELECT * FROM oto_embed_sosmed WHERE id IN ('$id_all')";
        if (isset($id_all) && !empty($id_all)) {
            $hasil = embed_sosmed($id_all);
            if (isset($hasil) && !empty($hasil)) {
                foreach ($hasil as $h) {
                    //echo $h->code;
                    //print_r($h);
                    $datas = getembedsosmed($h['code'], $h['url']);
                    $code = "<!-- sosmed_".$h['id']." -->";
                    $news = str_replace($code, $datas, $news);
                }
                $row['news'] = $news;
            }
        }
        //------------------------------------------------------------------------------------------------------------------
        
        #hafid imageslider 09-06-2016 -------------------------------------------------------------
        //$news = $row['news'];
        //$row['news'] = image_slider($news);
        //end imageslider -------------------------------------------------------------------------
        
        
        // scan [polling]
        if (preg_match_all('/\[polling\](\d+)\[\/polling\]/s', $row['news'], $mf)) {
            if (!defined('POLLING_CLASS')) {
                include(CLASSDIR . 'Polling.php');
            }

            foreach ($mf[1] as $fm) {
                $polling = new Polling();
                $polling->set_id($fm);
                $polling = $polling->show();
                $row['news'] = str_replace('[polling]' . $fm . '[/polling]', $polling, $row['news']);
            }
            unset($mf);
        }

        // scan [formation]
        if (preg_match_all('/\[formation\s*(enemy=("|\&quot;)(\d+)("|\&quot;))?\](\d+)\[\/formation\]/s', $row['news'], $mf)) {
            if (!defined('FORMATION_CLASS')) {
                include(CLASSDIR . 'Formation.php');
            }

            foreach ($mf[5] as $fk => $fm) {
                $enemy = isset($mf[3][$fk]) ? intval($mf[3][$fk]) : '';
                $formation = new Formation();
                $formation->set_id($fm);

                if ($enemy) {
                    $formation->set_enemy($enemy);
                    $formation = $formation->show(false);
                } else {
                    $formation = $formation->show();
                }

                $row['news'] = preg_replace('/\[formation\s*(enemy=("|\&quot;)(\d+)("|\&quot;))?\]' . $fm . '\[\/formation\]/si', $formation, $row['news']);
            }
            unset($mf);
        }

        // scan [quotes]
        if (preg_match_all('/\[quotes\](\d+)\[\/quotes\]/s', $row['news'], $mf)) {
            if (!defined('QUOTES_CLASS')) {
                include(CLASSDIR . 'Quotes.php');
            }

            foreach ($mf[1] as $fm) {
                $quotes = new Quotes();
                $quotes->set_id($fm);
                $quotes = $quotes->show();
                $row['news'] = str_replace('[quotes]' . $fm . '[/quotes]', $quotes, $row['news']);
            }
            unset($mf);
        }


        // scan [initial]
        if (preg_match('/\[initial\]/s', $row['news'])) {
            preg_match('/\((([a-zA-Z0-9]{2,})\/([a-zA-Z0-9]{2,})\/?([a-zA-Z0-9]{2,})?)\)$/s', $row['news'], $m);
            if (isset($m[0])) {
                $row['news'] = str_replace($m[0], '', $row['news']);
                $row['news'] = str_replace('[initial]', '<b>' . $m[0] . '</b>', $row['news']);
            }
        }
        
        // hack
        $row['news'] = str_replace('<p><p>', '<br /><br />', $row['news']);
        $row['news'] = preg_replace('/((?<=\<p\>|\<\/p\>|\<li\>|\<\/li\>|\<ol\>|\<ul\>|\<\/ol\>|\<\/ul\>|\<blockquote\>|\<\/blockquote\>|\<br \/\>|\<tbody\>|\<\/tbody\>|\<tr\>|\<\/tr\>|\<td\>|\<\/td\>|\<\/table\>|\<\/div\>|\&lt\;p\&gt\;|\&lt\;\/p\&gt\;|\&lt\;li\&gt\;|\&lt\;\/li\&gt\;|\&lt\;ol\&gt\;|\&lt\;ul\&gt\;|\&lt\;\/ol\&gt\;|\&lt\;\/ul\&gt\;|\&lt\;blockquote\&gt\;|\&lt\;\/blockquote\&gt\;|\&lt\;\/br \/\&gt\;|\&lt\;tbody\&gt\;|\&lt\;\/tbody \/\&gt\;|\&lt\;tr\&gt\;|\&lt\;\/tr\&gt\;|\&lt\;td\&gt\;|\&lt\;\/td\&gt\;|\&lt\;\/table\&gt\;|\&lt\;\/div\&gt\;)\<p\>)/si', '', $row['news']);
        $row['news'] = preg_replace('/(\<table([^\>]*?)\>)\<p\>/si', '$1', $row['news']);
        $row['news'] = preg_replace('/((\<|\&lt;)div([^\>]*?)(\>|\&gt;))\<p\>/si', '$1', $row['news']);
        $row['news'] = preg_replace('/(\<img([^\>]*?)\/?\>)\<p\>/si', '$1', $row['news']);
        $row['news'] = preg_replace('/((?<!\<br\s\/\>|\&lt\;br \/\&gt\;)\<p\>)/si', '<br />', $row['news']);
        $row['news'] = str_replace('<br <br />', '<br />', $row['news']);
        
        //adjust paragraph in persib
        if (strtolower($row['source']) == 'persib.co.id') {
            $row['news'] = str_replace('<br />', '<br /><br />', $row['news']);
            $row['news'] = preg_replace('/\<br \/\>\<br \/\>/', '', $row['news'], 1);
            $row['news'] = preg_replace("#(<br */?>\s*)+#i", "<br /><br />", $row['news']);
        }

        /* Grab Image Body if alt not empty */
        if (preg_match_all("/<img[^>]+>/i", $row['news'], $matchesarray)) {
            $count = count($matchesarray[0]);
            if ($count > 0) {
                foreach ($matchesarray[0] as $img_tag) {
                    preg_match('/alt="([^"]*)"/i', $img_tag, $img_alt);
                    if (isset($img_alt[1]) && $img_alt[1]) {
                        $img_alt[1] = '<p>' . $img_alt[1] . '</p>';

                        $row['news'] = str_replace($img_tag, '<div class="bola-image-body">' . $img_tag . $img_alt[1] . '</div>', $row['news']);
                        $row['news'] .= "<script>
                                        $(document).ready(function() {
                                            $('.bola-image-body img').addClass('lazy_loaded');
                                            $('.bola-image-body img').attr('src','http://cdn.klimg.com/vemale.com/p/i/logo/1px_white.JPG');
                                            $('.lazy_loaded').unveil(100, function(){
                                                $(this).load(function(){
                                                    this.style.opacity = 1;
                                                });
                                            });
                                        });
                                        </script>";
                    }
                }
            }
        }

        /*
          if (preg_match_all("/<img[^>]+>/i", $row['news'], $matchesarray)) {
          $count = count($matchesarray[0]);
          if ($count > 0) {
          foreach ($matchesarray[0] as $img_tag) {
          preg_match('/alt="([^"]*)"/i', $img_tag, $img_alt);
          if(isset($img_alt[1])) {
          $row['news'] = str_replace($img_tag, '<div class="bola-image-body">' . $img_tag . '<p>'.$img_alt[1] .'</p></div>', $row['news']);
          }
          }
          }
          }
         */
         
        /* Tiket Pesawat Link [2013-11-29] */
        if (!$advetorial_header) {
            $row['news'] = tiket_pesawat_link($row['news']);
        }
        
        /* replace all tag img src to img data-src */
        $row['news'] = preg_replace('/<img(.*?)src/', '<img data-src', $row['news']);
        
        /* replace youtube */
        //$row['news'] = filterYoutube($row['news']);

        /* track ext link 20141007 */
        $row['news'] = track_externalLink($row['news'], $row['title']);
        
        /* Parsing Related Content [2013-09-23] */
        $related_content = get_related_content($sqlDB, $row['idnews'], '1'); //type = 1 [news]

        if ($related_content && is_array($related_content)) {
            foreach ($related_content as $pos => $value) {
                $value = preg_replace("/http:\/\/www.bola.net\/commercial\//", MICROSITE_EURO2016.'berita/', $value);
                $row['news'] = str_replace("<!--$pos-->", $value, $row['news']);
            }
        }
        /* End of Parse Related Content */
        
        /* Parsing Related Quote [2013-10-30] */
        //connect.facebook.net/en_US/all.js#xfbml=1&appId=109215469105623
        $related_quote_html = '         
        <div id="fb-root"></div>
        <script>(function(d, s, id) {
          var js, fjs = d.getElementsByTagName(s)[0];
          if (d.getElementById(id)) return;
          js = d.createElement(s); js.id = id;
          js.src = "//connect.facebook.net/en_US/sdk.js#version=v2.0&xfbml=1&appId=109215469105623";
          fjs.parentNode.insertBefore(js, fjs);
        }(document, "script", "facebook-jssdk"));</script>
            <script>
            $(function(){
                $(".rel_quote_fb_share").click(function(e){
                    var this_url = $(this).attr("href");
                    window.open(this_url, "Bola.net Share Quote", "height=300, width=600");
                    e.preventDefault();
                });
            })
            </script>
            <style>
            @import url(https://fonts.googleapis.com/css?family=Archivo+Narrow:700italic,700);
            </style>
        ';
        $related_quotes = get_related_quote($sqlDB, $row['idnews'], '1', $fileurl); //type = 1 [news]
        if ($related_quotes && is_array($related_quotes)) {
            foreach ($related_quotes as $pos => $value) {
                $row['news'] = str_replace("<!--$pos-->", $value, $row['news']);
            }
        }
        /* End of Parse Related Quote */
        
        $logo_src = $sumber = '';
        if ($row['source'] == 'otosia') {
            $logo_src .= '<div style="padding-bottom: 35px;border-bottom: 1px solid #ECECEC;">
                            <a href="http://www.otosia.com/" alt="http://www.otosia.com/" onclick="_gaq.push([\'_trackEvent\', \'Out Bound Links\', \'Click\', \'Logo Bola.net\']);" target="_blank"> 
                              <img src="'.KLIMGURL.'library/upload/10/2015/06/175/otosia_bec0690.png" alt="otosia.com" style="float: left;">
                           </a>
                        </div><br/>';
            $sumber .= '<br/>
            Sumber <a href="http://www.otosia.com/" alt="http://www.otosia.com/" onclick="_gaq.push([\'_trackEvent\', \'Out Bound Links\', \'Click\', \''.$row['title'].'\']);" target="_blank">Otosia</a>';
        }

        $related_bf = news_get_photonews($sqlDB, $id);
        

        $news_socmed = '<!-- social tab -->

        <script>
        $(document).ready(function() {
            $("#kl-social-share").klnShareSocmed({
                url:"' . $fileurl . '",
                comment_count:-1,
                twitter_user: "bolanet",
                email_button: "email"
            });
      
            socilatabs_pintit( "' . $fileurl . '" );
        });
        </script>

        <!-- end social tab -->';


        $next_prev_array = array();
        
        //berita terkait
        ###related news from tags 2014-06-12
        $_rel_max_ = 3;
        $related_news_tags = '';
        $_tags_news_id_ = '';
        if ($first_tag) {
            $qreltags = "SELECT * FROM dbtags_content WHERE tags_id = '{$first_tag['tags_id']}' AND type = '1' AND id!='$id'
                                    AND schedule <= NOW() AND level > '0' 
                                    ORDER BY schedule DESC LIMIT 1";
            $rftags = $sqlDB->sql_query($qreltags, true);
            while ($rowftags = $sqlDB->sql_fetchrow($rftags)) {
                $_tag_name_ = ucwords($first_tag['tags_name']);
                $_tag_url_ = $tag_url.$first_tag['tags_url'].'/';
                $_item_url_ = $tag_url.$first_tag['tags_url'].'/'.$rowftags['url'].'.html';
                if (strlen($rowftags['image_headline']) == 14) {
                    $_item_image_ = $thumbnail_media_url . $rowftags['image_headline'];
                } else {
                    $_item_image_ = $image_library_url.bola_image_news($rowftags['image_headline'], '175/');
                }
                $related_news_tags .= '
                    <li >
                        <a href="'.$_item_url_.'" ' . ga_trackEvent('berita_terkait', $rowftags['title']) . ' class="img-left">
                            <img class="lazy_loaded" src="'.$img_lazy_load.'" data-src="'.$_item_image_.'" alt="'.$rowftags['title'].'" width="120" height="60"/>
                        </a>
                        <div class="deskrip-right">
                            <a href="'.$_tag_url_.'" class="link-kategori" ' . ga_trackEvent('berita_terkait', $rowftags['title']) . '>Berita '.$_tag_name_.'</a>
                            <p>
                                <a href="'.$_item_url_.'" class="rel_item_title" ' . ga_trackEvent('berita_terkait', $rowftags['title']) . '><span>'.$rowftags['title'].'</span></a>
                            </p>
                        </div>
                    </li>';
                $_tags_news_id_ = $rowftags['id'];
            }
        }
//        $related_news_tags = $_tags_news_id_ = '';
        
        $related_news = '';
        $linkednews = getLinkedNews($sqlDB, $row['category'], $row['keyword1'], $row['schedule'], $_tags_news_id_);
        
        if (count($linkednews) > 0) {
            $related_news .= '
                <br/>
                <div class="box_related box_relatedv2">
                    <div class="rel_header">
                        Berita Terkait 
                    </div>
                    <ul class="list-block">
            ';
            $refresh_to_related = '';
            $jsslideterkait = array();
            $count_rel = 0;
            $_rel_max_ = 5;
            if ($related_news_tags) {
                $_rel_max_ = 4;
            }
            foreach ($linkednews as $v) {
                if (strlen($v['image_headline']) == 14) {
                    $rel_news_image = $thumbnail_media_url . $v['image_headline'];
                } else {
                    $rel_news_image = $image_library_url . bola_image_news($v['image_headline'], '125/');
                }
                if (strtolower($v['source']) == 'persib.co.id') {
                    $rel_news_image = persibnews_image_url($v['image_headline'], '175');
                }
                if (count($linkednews) > 5 && count($jsslideterkait) < 2) {
                    //if (is_file($rel_news_image))
                    if ($v['image_headline']) {
                        if (strlen($v['image_headline']) == 14) {
                            $rel_news_image = $thumbnail_media_url . $v['image_headline'];
                        } else {
                            $rel_news_image = $image_library_url.bola_image_news($v['image_headline'], '125/');
                        }
                        if (strtolower($v['source']) == 'persib.co.id') {
                            $rel_news_image = persibnews_image_url($v['image_headline'], '175');
                        }
                        $jsslideterkait[] = "{
                            idnews: '" . $v['idnews'] . "',
                            title: ['" . addslashes($v['title']) . "', '" . BOLAURL . $dir[$v['category']][1] . '/' . $v['url'] . ".html'],
                            category: ['" . strtoupper($dir[$v['category']][0]) . "', '" . BOLAURL . $dir[$v['category']][1] . "/'],
                            image: '$rel_news_image' 
                        }";


                        continue;
                    }
                }
                
                $count_rel++;
                if ($related_news_tags && $count_rel == 1) {
                    $related_news .= $related_news_tags;
                }
                if ($count_rel > $_rel_max_) {
                    continue;
                }

                //if(is_file($rel_news_image))
                if ($v['image_headline']) {
                    #$rel_news_image = $thumbnail_media_url . $v['image_headline']; //str_pad($v['idnews'], 10, '0', STR_PAD_LEFT) .'.jpg';
                    if (strlen($v['image_headline']) == 14) {
                        $rel_news_image = $thumbnail_media_url . $v['image_headline'];
                    } else {
                        $rel_news_image = $image_library_url . bola_image_news($v['image_headline'], '175/');
                    }
                    if (strtolower($v['source']) == 'persib.co.id') {
                        $rel_news_image = persibnews_image_url($v['image_headline'], '175');
                    }
                    $rel_news_image = '<img class="lazy_loaded" src="'.$img_lazy_load.'" data-src="' . $rel_news_image . '" alt="' . $v['title'] . '" width="120" height="60"/>';
                } else {
                    $rel_news_image = '';
                }
                $related_news .= '
                <li>

                    <a class="img-left" href="'  . $v['url'] . '.html" ' . ga_trackEvent('berita_terkait', $v['title']) . '>
                        ' . $rel_news_image . '
                    </a>
                    <div class="deskrip-right">
                        <a  href="/' . $row['category_url'] . '/"  class="link-kategori" ' . ga_trackEvent('berita_terkait', $v['title']) . '>'.$row['category_name'].'</a>

                        <p>
                            <a href="'  . $v['url'] . '.html" ' . ga_trackEvent('berita_terkait', $v['title']) . '>
                        ' . $v['title'] . '</a>
                        </p>
                    </div>
                </li>
                ';

                /*ads infeed & Domino*/
                if ($count_rel=='1') {
                    $related_news.=box_infeed_1();
                    $related_news.=box_domino_1();
                }


                if ($count_rel=='2') {
                    $related_news.=box_infeed_2();
                    $related_news.=box_domino_2();
                }


                if ($count_rel=='3') {
                    $related_news.=box_infeed_3();
                    $related_news.=box_domino_3();
                }


                if ($count_rel < $_rel_max_) {
                    // $related_news .= '<div class="rel_space">&nbsp;</div>';
                }
                if (!$refresh_to_related) {
                    //$_refresh_url_ = BOLAURL . $dir[$row['category']][1].'/'.$v['url'].'.html';
                    $_refresh_url_ = BOLAURL . $dir[$row['category']][1] . '/';
                    $refresh_to_related = 'url = "' . $_refresh_url_ . '";
                    timeout = 1020;
                    window.setTimeout(\'window.location= "\' + url + \'"; \',timeout*1000);';
                }
            }//end foreach

            $related_news .= '</ul>'; //end UL list-block

            if ($refresh_to_related) {
                $refresh_to_related .= " \n";
                if (count($jsslideterkait) == 2) {
                    $refresh_to_related .= "readnext.init([" . implode(',', $jsslideterkait) . "], '.detail-jsview:last');";
                    $refresh_to_related .= " \n";
                }
                $addthis = str_replace('recomm_view', $refresh_to_related . 'recomm_view', $addthis);
            }
            $related_news .= '</div><br/>'; //end div box_related
        }


        /* news related information [profil, tim, galeri, wallpaper terkait] */
        $player_array = array($row['celebrity'], $row['celebrity1'], $row['celebrity2'], $row['celebrity3'], $row['celebrity4'], $row['celebrity5']);
        $team_array = array($row['club1'], $row['club2'], $row['club3']);
        // $related_other = get_news_related_other($sqlDB, $player_array, $team_array);

        $related_other="";

        /*
         * #charis 20140327 Google AdXSeller: 468x60
         * '<!-- ADVETORIAL --><script type="text/javascript" src="'.APPSURL2.'contentmatch/index.php?v2"></script><!-- ENDOFADVETORIAL -->';
         *
         */

        //berita lain v2
        $othernews = getOtherNews($sqlDB, $row['category'], $row['keyword1'], $row['schedule']);
        $other_news = '';
        if (count($othernews) > 0) {
            $other_news .= '
               <div class="box_related box_relatedv2">
                    <div class="rel_header">
                        Berita Lainnya
                    </div>
                    <ul class="list-item3 clearfix">
            ';
            $_count = 1;
            foreach ($othernews as $k=>$v) {
                //if(is_file($other_news_image))

                if ($v['image_headline']) {
                    $other_news_image = $thumbnail_media_url . $v['image_headline']; //str_pad($v['idnews'], 10, '0', STR_PAD_LEFT) .'.jpg';
                    if (strlen($v['image_headline']) == 14) {
                        $other_news_image = $thumbnail_media_url . $v['image_headline'];
                    } else {
                        $other_news_image = $image_library_url.bola_image_news($v['image_headline'], '175/');
                    }
                    $other_news_image = '<img class="lazy_loaded" src="'.$img_lazy_load.'" data-src="' . $other_news_image . '" alt="' . $v['title'] . '" width="150" height="75"/>';
                } else {
                    $other_news_image = '';
                }
                if (strtolower($v['source']) == 'persib.co.id') {
                    $other_news_image = '<img class="lazy_loaded" src="'.$img_lazy_load.'" data-src="' . persibnews_image_url($v['image_headline'], '175') . '" alt="' . $v['title'] . '" width="150" height="75"/>';
                }
                $other_news .= '
                    <li>
                    <a class="img-block" href="'  . $v['url'] . '.html" ' . ga_trackEvent('berita_lainnya', $v['title']) . '>
                        ' . $other_news_image . '
                    </a>
                    <div class="deskrip-bottom">
                        <p>
                        <a  href="'  . $v['url'] . '.html" ' . ga_trackEvent('berita_lainnya', $v['title']) . '>
                ' . $v['title'] . '
                        </a>
                        </p>
                    </div>
                    </li>
                ';
            }
            $other_news .= '
                    </ul>
                </div> 
            ';
        }

        $merdeka_brand="";
        $otosia_widget="";

        $news_arsip = '';

        $content_bottom = '
            <!--JSVIEW-->
            <div class="detail-jsview">
                <script type="text/javascript" src="' . APPSURL2 . 'jscounter/?i=' . $id . '&g=news" async="async"></script>
            </div>
            <!--JSVIEW-->
            ' . $addthis . '
            ' . $related_bf . '
            
            ' . $news_socmed . '
            
            ' . $related_news . '
            ' . $related_other . '
            ' . $comment_form . '
            ' . $tags_content . '
            ' . $other_news . '
            ' . $news_arsip . '          
            ' . $otosia_widget . '
            ' . $merdeka_brand . '
            ';

        $content_bottom .= '
                </div> <!-- end of bigcon2 -->
                <br />
            </div>  <!-- end of bigcon -->
        ';
        
        if (is_array($desc_tags)) {
            $desc_tags = implode(', ', $desc_tags);
        }
        if ($row['synopsis']) {
            $description = htmlspecialchars(strip_tags($row['synopsis']), ENT_QUOTES).', '.$desc_tags;
        } else {
            $description = htmlspecialchars(strip_tags($row['title'])).', '.$desc_tags;
        }
        
        //$allwords = explode(' ', preg_replace('/[^0-9a-z \-_]/', '', strtolower((isset($contentHasil->topics)?$contentHasil->topics:""))));
        $allwords = explode(' ', str_replace(',', '', $description));
        $allkeywords = array();
        foreach ($allwords as $v) {
            $allkeywords[] = $v;
        }
        $allkeywords = array_unique($allkeywords);
        $setKeyword = implode(', ', $allkeywords);

        $active_menu = 0;
        if (in_array($row['category'], $def_liga_eropa)) {
            $active_menu = 1;
        } elseif (in_array($row['category'], $def_bola_dunia)) {
            $active_menu = 2;
        } elseif (in_array($row['category'], $def_bola_indonesia)) {
            $active_menu = 3;
        } elseif ($row['category'] == '14') {
            $active_menu = 4;
        } elseif (in_array($row['category'], $def_olahraga_lain)) {
            $active_menu = 9;
        }
        
        $extra_js = ($row['extra_js']) ? $row['extra_js'] : '';
        
        $anak = '';
        $anak .= box_tokopedia('', $advetorial_header);
        // br anak artis 2
        if (!$advetorial_header && ($row['news_sensitive'] == 0) && ($row['is_mature'] == 0)) {
            //$anak .= box_anak_artis().'<br/>';
            //$anak .= box_jomblohunt().'<br/>';
        }
        
        // props insight
        $props_insight = isset($_PROPS_[$row['category']]) ? $_PROPS_[$row['category']] : '';
        
        /* splitnews or default news*/
        $count_pagebreak = substr_count($row['news'], '<!-- Splitter Content -->');
        if ($count_pagebreak > 1) {
            if ($is_paging) {
                $infeed_template_id="48";
                $content_main .= $related_quote_html . '
                    <br/>
                    <div class="ncont">
                    ' . $logo_src .' 
                    <%splitnews%>
                    <%nexprevsplitnews%>
                    ' . $persib_source . '
                    ' . $sumber . '
                    <div class="clear"></div>
                    ' . $paging_btn . '
                    ' . $anak . '
                </div>
                ';
            } else {
                $infeed_template_id="47";
                $content_main .= $related_quote_html . '
                    <br/>
                    <div class="ncont">
                    ' . $logo_src .' 
                    <%splitnews%>
                    <%nexprevsplitnews%>
                    ' . $persib_source . '
                    ' . $sumber . '
                    ' . $anak . '
                    <div class="clear"></div>
                    ' . $paging_btn . '
                </div>
                ';
            }
            
            $content = $content_top . $paging_nav_js . '<div id="newspaging_content">' . $content_main . '</div>' . $content_bottom;
                
            preg_match_all('/\W.*?<!-- splitter content -->/i', $row['news'], $split_news);

            for ($i = 0 ; $i < $count_pagebreak; $i++) {
                $bolanet = ($i > 0) ? '<b>Bola.net</b> - ' : '';
                $content_split = $bolanet.$split_news[0][$i];
                $content_split = str_replace(array('<b>Bola.net</b> - <br /><br />', '<b>Bola.net</b> - <br />'), '<b>Bola.net</b> - ', $content_split);
                
                $filename = ($i == 0) ? BOLADIR . $dir[$row['category']][1] . '/' . $row['url'] . '.html' : BOLADIR . $dir[$row['category']][1] . '/' . $row['url'] . '-splitnews-'.($i+1). '.html';
                $fileurl = ($i == 0) ? BOLAURL . $dir[$row['category']][1] . '/' . $row['url'] . '.html' : BOLAURL . $dir[$row['category']][1] . '/' . $row['url'] . '-splitnews-'.($i+1). '.html';
                $filename_print = ($i == 0) ? BOLAURL . $dir[$row['category']][1] . '/print/' . str_pad($id, 10, '0', STR_PAD_LEFT) . '.html' : BOLAURL . $dir[$row['category']][1] . '/print/' . str_pad($id, 10, '0', STR_PAD_LEFT) . '-splitnews-'.($i+1). '.html';
                
                if ($i == 0) {
                    $next_split = next_prev_splitnews(BOLAURL . $dir[$row['category']][1] . '/' . $row['url'] . '-splitnews-'.($i+2). '.html', '', $row['title']);
                } elseif (($i == 1) && ($i != ($count_pagebreak-1))) {
                    $next_split = next_prev_splitnews(BOLAURL . $dir[$row['category']][1] . '/' . $row['url'] . '-splitnews-'.($i+2). '.html', BOLAURL . $dir[$row['category']][1] . '/' . $row['url'] . '.html', $row['title']);
                } elseif (($i == 1) && ($i == ($count_pagebreak-1))) {
                    $next_split = next_prev_splitnews('', BOLAURL . $dir[$row['category']][1] . '/' . $row['url'] . '.html', $row['title']);
                } elseif ($i == ($count_pagebreak-1)) {
                    $next_split = next_prev_splitnews('', BOLAURL . $dir[$row['category']][1] . '/' . $row['url'] . '-splitnews-'.($i). '.html', $row['title']);
                } else {
                    $next_split = next_prev_splitnews(BOLAURL . $dir[$row['category']][1] . '/' . $row['url'] . '-splitnews-'.($i+2). '.html', BOLAURL . $dir[$row['category']][1] . '/' . $row['url'] . '-splitnews-'.($i). '.html', $row['title']);
                }
                $news  = preg_replace('/<b>\((.+?)\)/', '', $row['news']);//sumber bug jika ada <b>(isi sembarang)</b> ==> </b>
                preg_match('/<b>\((.+?)\)/', $row['news'], $new);
                $src_int = isset($new[1])?$new[1]:'';
                $src_int = ($src_int) ? '<b>('.$src_int.')</b>' : '';
                
                $content_split = str_replace('<%splitnews%>', balanceTags($content_split, true).$src_int, $content);
                $content_split = str_replace('<%nexprevsplitnews%>', $next_split, $content_split);
                
                write_file($filename, $content_split, $row['title'], $_21_keatas.$row['title'], $row['title'], '', true, 'full', $active_menu, array('schedule' => $row['schedule'], 'amphtml' => true,'infeed_template_id'=>$infeed_template_id));
                insert_dfp_interest_segmen($filename, $row['dfp_interest']);
                insert_banner_inImageAds($filename);
                
                if (!empty($extra_js)) {
                    extra_js($extra_js, $filename);
                }
                
                if ($message) {
                    echo generated_link($fileurl);
                }
                insert_property_og($filename, $row['title'], $fileurl, $meta_og_image, '109215469105623', strip_tags($row["synopsis"]), $props_insight);
            }
        } else {
            if ($is_paging) {
                $infeed_template_id="48";

                $content_main .= $related_quote_html . '
                    <br/>
                    <div class="ncont">
                    ' . $logo_src .' 
                    ' . balanceTags($row['news'], true) . '
                    ' . $persib_source . '
                    ' . $sumber . '
                    <div class="clear"></div>
                    ' . $paging_btn . '
                    ' . $anak . '
                </div>
                ';
            } else {
                $infeed_template_id="47";

                $content_main .= $related_quote_html . '
                    <br/>
                    <div class="ncont">
                    ' . $logo_src .' 
                    ' . balanceTags($row['news'], true) . '
                    ' . $persib_source . '
                    ' . $sumber . '
                    ' . $anak . '
                    <div class="clear"></div>
                    ' . $paging_btn . '
                </div>
                ';
            }
            
            $content = $content_top . $paging_nav_js . '<div id="newspaging_content">' . $content_main . '</div>' . $content_bottom;
            if ($devel == false) {
                write_file($filename, $content, $row['title'], $_21_keatas.$setKeyword, $description, '', true, 'full', $active_menu, array('schedule' => $row['schedule'], 'amphtml' => true,'infeed_template_id'=>$infeed_template_id));
                insert_dfp_interest_segmen($filename, $row['dfp_interest']);
                insert_banner_inImageAds($filename);
            } else {
                write_file_dev($filename, $content, $row['title'], $_21_keatas.$setKeyword, $description, '', true, 'full', $active_menu, array('schedule' => $row['schedule'], 'amphtml' => true,'infeed_template_id'=>$infeed_template_id));
                insert_dfp_interest_segmen($filename, $row['dfp_interest']);
                insert_banner_inImageAds($filename);
            }
            
            
            if (!empty($extra_js)) {
                extra_js($extra_js, $filename);
            }
                
            if ($message) {
                echo generated_link($fileurl);
            }
        }
        /* eof splitnews or default news*/

        if ($row['source'] == 'otosia') {
            $detail_content = file_get_contents($filename);
            $can = '<link rel="canonical" href="'.$row['source_url'].'" />'."\n".'</head>';
            $detail_content = str_replace('</head>', $can, $detail_content);
            $detail_content = file_put_contents($filename, $detail_content);
        }
        
        
        if ($is_paging) {
            $infeed_template_id="48";

            write_file_direct(str_replace('.html', '-data.html', $filename), $content_main);

            $npq = "SELECT * FROM dbpagging_detail WHERE idpagging = '$idpaging' AND status = '1' ORDER BY `no` $paging_order";
            $res_npq = $sqlDB->sql_query($npq, true);
            if ($res_npq) {
                $np_count = $sqlDB->sql_numrows($res_npq);

                $prev_url = $fileurl;
                $next_url = $np_next = '';
                while ($np_row = $sqlDB->sql_fetchrow($res_npq)) {
                    $np_current = $np_row['no'];

                    //build navigation
                    $paging_nav = get_paging_nav($np_navrows, $fileurl, $np_current);

                    //get prev, next url
                    if ($paging_order == 'DESC') {
                        $np_next = $np_current - 1;
                        $next_url = get_paging_url($fileurl, $np_next);
                        $next_name = isset($np_navrows[$np_count - $np_current + 1]['title']) ? $np_navrows[$np_count - $np_current + 1]['title'] : '';
                        if ($np_next < 1) {
                            $next_url = $fileurl;
                            $np_next = 0;
                        }
                    } else {
                        $np_next = $np_current + 1;
                        $next_url = get_paging_url($fileurl, $np_next);
                        $next_name = isset($np_navrows[$np_next - 1]['title']) ? $np_navrows[$np_next - 1]['title'] : '';
                        if ($np_next > $np_count) {
                            $next_url = $fileurl;
                            $np_next = 0;
                        }
                    }

                    $paging_top_nav = get_paging_top_nav($prev_url, $next_url, '<span style="font-size:16px;font-weight:bold;color:#fff;">' . $np_row['no'] . '. ' . $np_row['title'] . '</span>');

                    $paging_nav_js = '
                        <script type="text/javascript">
                            $(function(){
                                BolaPaging.init({
                                    current: ' . $np_current . ',
                                    max: ' . $np_count . ',
                                    order: ' . $np_order . ',
                                    url: "' . $fileurl . '",
                                    container: "#newspaging_content",
                                    contentid: ' . $row['idnews'] . '
                                });
                            });
                        </script>                            
                    ';
                    if ($row['idnews'] != '97090') {
                        //$paging_nav_js = ''; // disable ajax
                    }
                    $paging_btn = '';
                    if ($np_next) {
                        $paging_btn = '
                            <br/>
                            <div class="np_link_nav">
                                <a class="np_next" href="' . $next_url . '" onclick="_gaq.push([\'_trackEvent\', \'Paging - Bottom\', \'click\', \'' . $next_url . '\']);">' . ($next_name ? 'Lanjut ke ' . $next_name : 'LANJUT KE NO. ' . $np_next) . '</a>
                            </div>                        
                        ';
                    } else {
                        $q_paging = "SELECT category, url, title, image_headline FROM `dbnews` WHERE level != '0' AND level !='3' AND is_pagging='1' AND category<>'28' AND schedule<'" . $row['schedule'] . "' AND schedule<NOW() ORDER BY schedule DESC LIMIT 3";
                        $r_paging = $sqlDB->sql_query($q_paging, true) or die(__LINE__ . ' = ' . mysql_error());
                        $next_thumb_img = '';
                        
                        while ($row_paging = $sqlDB->sql_fetchrow($r_paging)) {
                            $next_paging = BOLAURL . $dir[$row_paging['category']][1] . '/' . $row_paging['url'] . '.html';
                            if (!isset($intro_url) || !$intro_url) {
                                $intro_url = $next_paging;
                            }
                            $intro_title = 'SELANJUTNYA';
                            $url_image = ((strlen($row_paging['image_headline']) == 16) ? $thumbnail_media_url . $row_paging['image_headline'] : $image_library_url.$row_paging['image_headline']);
                            if ($row_paging['image_headline']) {
                                //$next_thumb_img = '<center><a href="'.$next_paging.'"><img src="'. $thumbnail_media_url. $row_paging['image_headline'] .'" width="125" alt="'. strip_tags($row_paging['title']) .'" title="'. strip_tags($row_paging['title']) .'"></a></center>';
                                $next_thumb_img .= '<a class="rel_item" href="' . $next_paging . '"><img src="' . $url_image . '" alt="' . strip_tags($row_paging['title']) . '" title="' . strip_tags($row_paging['title']) . '">
                            <span>' . $row_paging['title'] . '</span>
                        </a><div class="rel_space">&nbsp;</div>';
                            }
                        }

                        if ($next_thumb_img) {
                            $next_thumb_img = '<div class="box_related"><div class="rel_content">' . $next_thumb_img . '<div class="clear"></div></div></div>';
                        } else {
                            $next_paging = '';
                            $intro_url = $fileurl;
                            $intro_title = 'KEMBALI KE INTRO';
                        }

                        $paging_btn = '
                            <br/>
                            <div class="np_link_nav">
                                <a class="np_next" href="' . $intro_url . '" id="np_intro_btn" class="first" onclick="_gaq.push([\'_trackEvent\', \'Paging - Bottom\', \'click\', \'' . $intro_url . '\']);">' . $intro_title . '</a>
                            </div>                        
                ' . $next_thumb_img . '
                        ';
                    }

                    $paging_content_main = '';
                    $np_resource = $np_row['path'];
                    if ($np_row['type'] == 0) {
                        $np_image_info = isset($np_row['imageinfo']) ? '<div>' . $np_row['imageinfo'] . '</div>' : '';
                        $np_resource = '<img class="lazy_loaded" src="'.$img_lazy_load.'" data-src="' . $cdn_url . 'library/' . $np_row['path'] . '" width="468" alt="' . htmlentities($np_row['title']) . '" />' . $np_image_info;
                    }
                    
                    $np_row['desc'] = preg_replace('/<img(.*?)src/', '<img data-src', $np_row['desc']);
                    
                    /* Tiket Pesawat Link [2013-09-23] */
                    if (!$advetorial_header) {
                        $np_row['desc'] = tiket_pesawat_link($np_row['desc']);
                    }

                    /* Parse Related COntent From Paging [2013-09-23] */

                    if ($related_content && is_array($related_content)) {
                        foreach ($related_content as $pos => $value) {
                            $np_row['desc'] = str_replace("<!--$pos-->", $value, $np_row['desc']);
                        }
                    }
                    /* End Parse Related COntent From Paging [2013-09-19] */

                    #new embed sosmed pagging  [2015-12-07]--------------------------------------------------------------------
                    $cnews = stripslashes($np_row['desc']);
                    preg_match_all('/sosmed\_([0-9]+)/', $cnews, $data);
                    $id_all = implode("','", $data[1]);
                    if (isset($id_all) && !empty($id_all)) {
                        $hasil = embed_sosmed($id_all);
                        if (isset($hasil) && !empty($hasil)) {
                            foreach ($hasil as $h) {
                                //echo $h->code;
                                $datas = getembedsosmed($h['code'], $h['url']);
                                $code = "<!-- sosmed_".$h['id']." -->";
                                $cnews = str_replace($code, $datas, $cnews);
                            }
                        }
                    }
                    $np_row['desc']=$cnews;
                    //end--------------------------------------------------------------------------------------------------
                    
                    #hafid imageslider 09-06-2016 -------------------------------------------------------------
                    //$np_row['desc'] = stripslashes($np_row['desc']);
                    //$np_row['desc'] = image_slider($news);
                    //end imageslider -------------------------------------------------------------------------
                    
                    /* Parse Related Quote From Paging [2013-10-28] */
                    if ($related_quotes && is_array($related_quotes)) {
                        foreach ($related_quotes as $pos => $value) {
                            $np_row['desc'] = str_replace("<!--$pos-->", $value, $np_row['desc']);
                        }
                    }
                    /* End Parse Related Quote From Paging [2013-10-28] */

                    
                    $paging_content_main .= $related_quote_html . $paging_top_nav . '
                        <div class="news-headline-image np_image">
                            ' . $np_resource . '
                        </div>
                        ' . $paging_nav . '
                        <div class="ncont">
                            ' . balanceTags($np_row['desc'], true) . '
                            <div class="clear"></div>
                            ' . $paging_btn . '
                            ' . $anak . '
                        </div>
                    ';

                    $np_fileurl = get_paging_url($fileurl, $np_row['no']);
                    $paging_content = $content_top . $paging_nav_js . '
                        <div id="newspaging_content">' . $paging_content_main . '</div> <!-- end of #newspaging_content -->' .
                            $content_bottom;

                    $np_filename = str_replace(BOLAURL, BOLADIR, $np_fileurl);
                    write_file($np_filename, $paging_content, $row['title'] . ' - ' . $np_row['title'], $_21_keatas.$setKeyword, $description, '', true, 'full', $active_menu, array('schedule' => $row['schedule'], 'amphtml' => true,'infeed_template_id'=>$infeed_template_id));
                    insert_dfp_interest_segmen($np_filename, $row['dfp_interest']);
                    insert_banner_inImageAds($np_filename);
                    
                    if (!empty($extra_js)) {
                        extra_js($extra_js, $np_filename);
                    }
                
                    if ($message) {
                        echo generated_link($np_fileurl);
                    }
                    insert_property_og($np_filename, $np_row['title'], $np_fileurl, $meta_og_image, '109215469105623', strip_tags($np_row['desc']), $props_insight);
                    write_file_direct(str_replace('.html', '-data.html', $np_filename), $paging_content_main);

                    $prev_url = $np_fileurl;
                }
            }
        }
        
        insert_property_og($filename, $row['title'], $fileurl, $meta_og_image, '109215469105623', strip_tags($row["synopsis"]), $props_insight);
        update_footer_v2($sqlDB, $filename);

        if (in_array($id, array('19651', '19655', '19665', '19667', '19668', '19671', '19677', '19638', '19628', '19618', '71044', '70975', '70974'))) {
            news_inject_socialfb($filename);
        }

        // piala dunia
        if ($row['category'] == '7' || $row['category'] == '14' && !defined('THIS_IS_CRON')) {
            if (!function_exists('wc_generate_per_id')) {
                include(FUNCTIONDIR . 'function.worldcup.news.php');
            }
            //wc_generate_per_id($sqlDB, $id);
        }

        // euro
        if ($row['category'] == 8 && !defined('THIS_IS_CRON')) {
            #euro_news_byid($row['idnews'], true, $sqlDB);
        }
        // end euro
        // generate tags, please disable when do generate all
        if (count($generatetags_detail) > 0 && defined('DBDIRECT') && !defined('THIS_IS_CRON')) {
            if (!function_exists('generate_tags_byid')) {
                include(FUNCTIONDIR . 'function.tags.php');
            }

            $generatetags_detail = array_filter(array_unique($generatetags_detail));
            foreach ($generatetags_detail as $vt) {
                // sementara, untuk generate all
                ###generate_tags_byid($sqlDB, $vt);
            }
        }
    }
    $sqlDB->sql_freeresult();
    if ($match_submenu) {
        return $fileurl;
    }
    return true;
}

/**
 * get news with same category and keyword
 */
function getLinkedNews($sqlDB, $category, $keyword, $schedule, $_excl_id_ = '')
{
    $return = array();
    $not_id = '';
    if ($_excl_id_) {
        $not_id = " AND idnews != '$_excl_id_' ";
    }
    $keyword = strtolower($keyword);
    $sql = "
        SELECT title,url,idnews,category, image_headline, source
        FROM dbnews
        WHERE
            category=" . $category . "
            AND (
                LOWER(keyword1)='" . $keyword . "'
                OR LOWER(keyword2)='" . $keyword . "'
                OR LOWER(keyword3)='" . $keyword . "'
                OR LOWER(keyword4)='" . $keyword . "'
                OR LOWER(keyword5)='" . $keyword . "'
                OR LOWER(keyword6)='" . $keyword . "'
            )
            AND schedule < '" . $schedule . "'
            AND schedule <> '00-00-0000 00:00:00'
            AND level != '0' AND level !='3' $not_id
        ORDER BY schedule DESC
        LIMIT 0,7
    ";
    $res = $sqlDB->sql_query($sql);
    $return = $sqlDB->sql_fetchrowset($res);
    $sqlDB->sql_freeresult();
    return $return;
}

/**
 * get news with same category and keyword
 */
function getLinkedNewsPrev($sqlDB, $category, $keyword, $schedule)
{
    $return = array();
    $keyword = strtolower($keyword);
    $sql = "
        SELECT title,url,idnews,category, image_headline
        FROM dbnews
        WHERE
            category=" . $category . "
            AND (
                LOWER(keyword1)='" . $keyword . "'
                OR LOWER(keyword2)='" . $keyword . "'
                OR LOWER(keyword3)='" . $keyword . "'
                OR LOWER(keyword4)='" . $keyword . "'
                OR LOWER(keyword5)='" . $keyword . "'
                OR LOWER(keyword6)='" . $keyword . "'
            )
            AND schedule > '" . $schedule . "'
        AND schedule < NOW()
            AND level != '0' AND level !='3'
        ORDER BY schedule
        LIMIT 0,1
    ";
    //echo htmlentities($sql);
    $res = $sqlDB->sql_query($sql);
    $return = $sqlDB->sql_fetchrowset($res);
    $sqlDB->sql_freeresult();
    return $return;
}

///**
// * get all news category [id, name, url]
// */
//function getAllNewsCat($sqlDB)
//{
//    $dir = array();
//    static $cache_allnewscat = array();
//
//    if (isset($cache_allnewscat) && count($cache_allnewscat) > 0)
//    {
//  $dir = unserialize($cache_allnewscat);
//    }
//    else
//    {
//  $q = "SELECT category_id, category_name, category_url FROM dbcategory WHERE category_level='1' ORDER BY category_id";
//  $r = $sqlDB->sql_query($q, true) or die (__LINE__ .' : '. mysql_error());
//  while ($row = $sqlDB->sql_fetchrow($r))
//  {
//      $dir[$row['category_id']] = array($row['category_name'], $row['category_url']);
//  }
//  $sqlDB->sql_freeresult();
//
//  $cache_allnewscat = serialize($dir);
//    }
//
//    if (count($dir) == 0)
//    {
//  echo 'Die DB Die!';
//  exit(0);
//    }
//
//    return $dir;
//}

/**
 * generate news detail between selected $startdate and $enddate
 * also used for generate all news detail
 */
function CreateNews($sqlDB, $level, $limit = "", $startdate = "", $enddate = "", $Type=true, $show=true)
{
    if (!empty($limit)) {
        $limit = " LIMIT " . $limit;
    }

    $levelX = $level;
    if ($level == 0) {
        $level = '(level<>0)';
    } else {
        $level = '(level=' . $level . ')';
    }

    if ($Type === true) {
        if (!empty($startdate) && !empty($enddate)) {
            $strWhere = " WHERE " . $level . " AND ( date(schedule) >= '" . $startdate . "' AND date(schedule) <= '" . $enddate . "') ORDER BY schedule DESC " . $limit;
        } else {
            $strWhere = " WHERE " . $level . " ORDER BY schedule DESC " . $limit;
        }
        $query = "SELECT `idnews`,`schedule` FROM dbnews " . $strWhere;
    } else {
        $query = "SELECT `idnews`,`schedule` FROM dbnews WHERE (schedule <= NOW() AND schedule >= (NOW() - INTERVAL 1 HOUR) ) AND level <>0  ORDER BY schedule DESC";
    }

    if (($levelX == "-1") && ($limit == "") && ($startdate == "") && ($enddate == "") && ($Type === true)) {
        // generate all desc
        $query = "SELECT `idnews`,`schedule` FROM dbnews WHERE schedule <= NOW() AND level <>0 ORDER BY idnews DESC";
    } elseif (($levelX == "-2") && ($limit == "") && ($startdate == "") && ($enddate == "") && ($Type === true)) {
        // generate all asc
        $query = "SELECT `idnews`,`schedule` FROM dbnews WHERE schedule <= NOW() AND level <>0 ORDER BY idnews ASC";
    }

    $exec = $sqlDB->sql_query($query, true);
    if ($exec) {
        while ($row = $sqlDB->sql_fetchrow($exec)) {
            $idnews = $row['idnews'];
            $schedule = $row['schedule'];
            
            generate_per_id($sqlDB, $idnews);
            if ($show) {
                echo $idnews . " - " . $schedule . " <br />\n";
                flush();
            }
        }
        return true;
    } else {
        return false;
    }
}

/**
 * generate arsip news per category
 * liga inggris, italia, and spanyol have own page for index
 * so, arsip started with index1.html
 *
 * @map url: http://www.bola.net/$category/index.html
 */
function generate_cat_news_bola($sqlDB, $catid, $LIMIT_PAGE = 0, $devel = false)
{
    global $library_url, $library_dir, $day_list_ina, $cdn_url, $headline_media, $headline_media_url, $thumbnail_media, $thumbnail_media_url,$img_lazy_load, $image_library_url;

    if (in_array($catid, array('1', '2', '3', '4', '5'))) {
        generate_index_category($sqlDB, $catid, $LIMIT_PAGE, $devel);
        return false;
    }

    $dir = getAllNewsCat($sqlDB);
    $props_insight = '';
    $i = $catid;

    $q = "SELECT idnews, schedule, title, synopsis, image, url, category, image_headline, source
        FROM dbnews WHERE category='$catid' AND level != '0' AND level !='3' AND schedule<NOW()
        ORDER BY schedule DESC LIMIT 1";
    $r = $sqlDB->sql_query($q, true) or die(__LINE__ . ' = ' . mysql_error());
    $row = $sqlDB->sql_fetchrow($r);
    //echo htmlentities ( $q );

    $title_cencored = $row['title'];
    #$img = $headline_media_url . $row['image_headline']; //str_pad($row['idnews'], 10, '0', STR_PAD_LEFT) .'.jpg';
    $img = ((strlen($row['image_headline']) == 14) ? $headline_media_url . $row['image_headline'] : $image_library_url . $row['image_headline']);
    $headline_url = BOLAURL . $dir[$row['category']][1] . '/' . $row['url'];
    /*if ($catid == 8)
    {
        $headline_url = EUROURL . "berita/" . $row['url'];
    }*/
    if (strtolower($row['source']) == 'persib.co.id') {
        $img = persibnews_image_url($row['image_headline']);
    }
    
    $row['schedule'] = preg_replace('/(\d{4,4})\-(\d{2,2})\-(\d{2,2}) (\d{2,2})\:(\d{2,2})\:(\d{2,2})/', '\3-\2-\1 \4:\5', $row['schedule']);
    $headline_content = '
    <div class="ligi">
            <a href="' . $headline_url . '.html"><img class="lazy_loaded" src="'.$img_lazy_load.'" data-src="' . $img . '" width="468" height="240" alt="" style="margin: -18px -5px;"/></a>
            <div class="clear"></div><br/>
            <span><a href="' . $headline_url . '.html" class="bluelink">' . $title_cencored . '</a></span>
            
            <br/>
            <div>
                ' . balanceTags($row['synopsis'], true) . '
                <a href="' . $headline_url . '.html" class="bluelink">Selengkapnya...</a>
                <div class="clear"></div>
            </div>
    </div>
    ';
    $sqlDB->sql_freeresult();

    $not_in_hl = " AND idnews<>'" . $row['idnews'] . "'";
    $headline_content = (in_array($catid, array('1', '2', '3'))) ? '' : $headline_content;

    $_first_title = $row['title'] . ' - ' . $dir[$row['category']][0];
    $_first_desc = $row['synopsis'] . ' - ' . $dir[$row['category']][0];
    $_first_keywords = implode(", ", explode(" ", $row['synopsis'] . ' - ' . $dir[$row['category']][0]));
    
    if ($catid == 6) {
        $hsubd = '<h2 class="hsubd">Jadwal, Hasil Pertandingan, Klasemen, Berita dan Foto</h2>';
    } else {
        $hsubd = '';
    }
    
    $content = '
        <div class="bigcon">
            <div class="bigcon2">
                <div class="nav">
                    <a ' . ga_trackEvent('link_nav') . ' href="/" style="text-decoration:none;">HOME</a> &raquo;
                    <a ' . ga_trackEvent('link_nav') . ' href="/' . $dir[$i][1] . '/" style="text-decoration:none;">' . strtoupper($dir[$i][0]) . '</a>
                </div>
                <br />
                <div class="subindexcat_v2">' . strtoupper($dir[$i][0]) . '</div>'.$hsubd.'
                ' . $headline_content . '
    ';
    $sql = "SELECT idnews, title, url, schedule, category, idnews, synopsis, image, image_headline, source
            FROM dbnews
            WHERE level != '0' AND level !='3' AND category = " . $i . " AND schedule <> '00-00-0000 00:00:00' AND schedule <= NOW() $not_in_hl
            ORDER BY schedule DESC";

    $res = $sqlDB->sql_query($sql, true) or die(__LINE__ . ' = ' . mysql_error());
    $num = $sqlDB->sql_numrows($res);

    $pagenum = ceil($num / 30);
    $counter = 1;
    $ischeck = true;
    $page = (in_array($catid, array('1', '2', '3'))) ? 1 : 0;
    $paging = global_paging_10($page, $pagenum, 'index');
    $index_page = 0;

    while ($row = $sqlDB->sql_fetchrow($res)) {
        list($date, $time) = explode(" ", $row['schedule']);
        list($year, $month, $day) = explode("-", $date);
        list($hour, $minute, $second) = explode(":", $time);
        $datetime = $day . '-' . $month . '-' . $year . ' ' . $hour . ':' . $minute;

        if ($counter == 1) {
            $first_title = $row['title'] . ' - ' . $dir[$row['category']][0];
            $first_desc = $row['synopsis'] . ' - ' . $dir[$row['category']][0];
            $first_keywords = implode(", ", explode(" ", $row['synopsis'] . ' - ' . $dir[$row['category']][0]));
        }

        if ($index_page == 0 && $counter == 7 && in_array($row["category"], array(1, 2, 3))) {
            $first_title = $row['title'] . ' - ' . $dir[$row['category']][0];
            $first_desc = $row['synopsis'] . ' - ' . $dir[$row['category']][0];
            $first_keywords = implode(", ", explode(" ", $row['synopsis'] . ' - ' . $dir[$row['category']][0]));
            $index_page = 1;
        }

        if ($index_page == 0 && !in_array($row["category"], array(1, 2, 3))) {
            $first_title = $_first_title;
            $first_desc = $_first_desc;
            $first_keywords = $_first_keywords;
            $index_page = 1;
        }
/*
        if ($catid == 8)
        {
            $detailurl = EUROURL . 'berita/' . $row['url'] . '.html';
        }
        else
        {
            $detailurl = BOLAURL . $dir[$row['category']][1] . '/' . $row['url'] . '.html';
        }
        */
        $detailurl = BOLAURL . $dir[$row['category']][1] . '/' . $row['url'] . '.html';

        $hlimage = $thumbnail_media . $row['image_headline']; //str_pad($row['idnews'], 10, '0', STR_PAD_LEFT) .'.jpg';
        if (strtolower($row['source']) == 'persib.co.id') {
            $hlimage = persibnews_image_url($row['image_headline'], '175');
        }
        //if (is_file($hlimage))
        if ($row['image_headline']) {
            #$img = $thumbnail_media_url . $row['image_headline']; //str_pad($row['idnews'], 10, '0', STR_PAD_LEFT) .'.jpg';
            $img = ((strlen($row['image_headline']) == 14) ? $thumbnail_media_url . $row['image_headline'] : $image_library_url . bola_image_news($row['image_headline'], '125/'));
            if (strtolower($row['source']) == 'persib.co.id') {
                $img = persibnews_image_url($row['image_headline']);
            }

            if (in_array($catid, array('1', '2', '3', '14'))) {
                $useclass = 'bcontent2_image';
            } else {
                $useclass = 'bcontent2_image_right';
            }
            $line_image = '<div class="' . $useclass . '"><a href="' . $detailurl . '"><img class="lazy_loaded" src="'.$img_lazy_load.'" data-src="' . $img . '" width="130" height="84"/></a><br class="clear"/><div>&nbsp;</div></div>';
        } else {
            $img = str_replace('//p', '/p', $cdn_url . 'library/' . str_replace($library_url, '/', $row['image']));
            $line_image = '';
        }

        $title_cencored = strlen($row['title']) > 55 ? substr($row['title'], 0, 53) . '..' : $row['title'];
        $synopsis_cencored = strlen($row['synopsis']) > 170 ? substr($row['synopsis'], 0, 168) . '..' : $row['synopsis'];

        $row['schedule'] = preg_replace('/(\d{4,4})\-(\d{2,2})\-(\d{2,2}) (\d{2,2})\:(\d{2,2})\:(\d{2,2})/', '\3-\2-\1 \4:\5', $row['schedule']);
        $hari = $day_list_ina[date('w', strtotime($row['schedule']))];

        $counter++;
        if (in_array($catid, array('1', '2', '3')) && $counter <= 7 && $ischeck) {
            continue;
        } else {
            if ($ischeck) {
                $counter = 2;
            }
            $ischeck = false;
        }

        
    
        //advertorial mark
        $advertorial_mark = '';
        $qtags = "
                                SELECT A.tags_id, A.tags_name, A.tags_url
                                FROM `dbtags` A INNER JOIN dbtags_assosiation B ON A.tags_id=B.tags_id
                                WHERE A.tags_level='1' AND A.tags_id = '9275' AND B.idnews='{$row['idnews']}'";
        if ($rtags = $sqlDB->sql_query($qtags, true)) {
            while ($rowtags = $sqlDB->sql_fetchrow($rtags)) {
                $advertorial_mark = '&nbsp;&nbsp;<span style="background:#9bcc01; border-radius: 4px; color: rgb(255, 255, 255); display: inline-block; font-weight: bold; font-size: 13px; padding: 3px 7px;">Advertorial</span>';
            }
        }
        
        
        
        $content .= '
            <div class="bcontent2">
                <div class="indschedule">' . $hari . ', ' . $row['schedule'] . $advertorial_mark.'</div>
                <span><a href="' . $detailurl . '">' . $title_cencored . '</a></span>
                
                <br class="clear"/>
                ' . $line_image . '
                <div>' . balanceTags($row['synopsis'], true) . '</div>
                <br class="clear" />
            </div>
        ';

        if ($counter > 10) {
            $content .= '
                    <br />
                    <center>' . $paging . '</center>
                </div>
                </div>
            ';
            
            if ($devel == false) {
                $filename = BOLADIR . $dir[$i][1] . '/index.html';
                $fileurl = BOLAURL . $dir[$i][1] . '/index.html';
            } else {
                $filename = APPSDIR . 'devel/generate/www/categori/' . $dir[$i][1] . '/index.html';
                $fileurl = APPSURL . 'devel/generate/www/categori/' . $dir[$i][1] . '/index.html';
            }
            
            if ($page > 0) {
                if ($devel == false) {
                    $filename = BOLADIR . $dir[$i][1] . '/index' . $page . '.html';
                    $fileurl = BOLAURL . $dir[$i][1] . '/index' . $page . '.html';
                } else {
                    $filename = APPSDIR . 'devel/generate/www/categori/' . $dir[$i][1] . '/index' . $page . '.html';
                    $fileurl = APPSURL . 'devel/generate/www/categori/' . $dir[$i][1] . '/index' . $page . '.html';
                }
            }
            $active_menu = 0;
            if ($catid == 1) {
                $active_menu = 1;
                $metatitle = 'Berita dan Foto Sepak Bola Terbaru Liga Inggris, Jadwal dan Hasil Pertandingan, Klasemen, Profil Klub dan Profil Pemain';
                $metakey = 'Sepak Bola, Liga Inggris, Piala FA, Piala Carling, Piala Liga Inggris, Community Shield, Wayne Rooney, Steven Gerrard, Frank Lampard, John Terry, Cristiano Ronaldo, Cesc Fabregas, David Beckham, Fernando Torres, Didier Drogba, Foto Pemain, Foto Pertandingan, Profil Klub, Preview Pertandingan, Review Pertandingan, Manchester United, Liverpool, Chelsea, Arsenal, Manchester City, Tottenham Hotspur, Everton, West Ham, Newcastle, Fulham, Prediksi, Jadwal Pertandingan, Hasil Pertandingan, Football, Soccer.';
                $metadesc = 'Berita sepak bola terkini dan terlengkap Liga Inggris, live score, jadwal dan hasil pertandingan, profil dan foto pemain serta gaya hidup para pemain dan WAGS di luar lapangan.';
            } elseif ($catid == 2) {
                $active_menu = 1;
                $metatitle = 'Berita dan Foto Sepak Bola Terbaru Liga Italia, Jadwal dan Hasil Pertandingan, Klasemen, Profil Klub dan Profil Pemain.';
                $metakey = 'Sepak Bola, Liga Italia, Coppa Italia, Super Coppa Italia, Ronaldinho, Alexandre Pato, Diego Milito, Zlatan Ibrahimovic, Kaka, Francesco Totti, Alessandro Del Piero, Mourinho, David Beckham, Foto Pemain, Foto Pertandingan, Profil Klub, Preview Pertandingan, Review Pertandingan, AC Milan, Inter Milan, Juventus, AS Roma, Fiorentina, Lazio, Sampdoria, Bologna, Napoli, Parma, Udinese, Prediksi, Jadwal Pertandingan, Hasil Pertandingan, Football, Soccer.';
                $metadesc = 'Berita sepak bola terkini dan terlengkap Liga Italia, live score, jadwal dan hasil pertandingan, profil dan foto pemain serta gaya hidup para pemain dan WAGS di luar lapangan.';
            } elseif ($catid == 3) {
                $active_menu = 1;
                $metatitle = 'Berita dan Foto Sepak Bola Terbaru Liga Spanyol, Jadwal dan Hasil Pertandingan, Klasemen, Profil Klub dan Profil Pemain.';
                $metakey = 'Sepak Bola, Liga Spanyol, Piala Raja Spanyol, Cristiano Ronaldo, Kaka, Lionel Messi, David Villa, Iniesta, Xavi, Xabi Alonso, Sergio Ramos, Puyol, Iker Casillas, Mourinho, Fabiano, Diego Forlan, Zlatan Ibrahimovic, Foto Pemain, Foto Pertandingan, Profil Klub, Preview Pertandingan, Review Pertandingan, Real Madrid, Barcelona, Valencia, Atletico Madrid, Sevilla, Prediksi, Jadwal Pertandingan, Hasil Pertandingan, Football, Soccer.';
                $metadesc = 'Berita sepak bola terkini dan terlengkap Liga Spanyol, live score, jadwal dan hasil pertandingan, profil dan foto pemain serta gaya hidup para pemain dan WAGS di luar lapangan.';
            } elseif ($catid == 4) {
                $active_menu = 3;
                $metatitle = 'Berita dan Foto Sepak Bola Terbaru Liga Indonesia, Jadwal dan Hasil Pertandingan, Klasemen, Profil Klub dan Profil Pemain.';
                $metakey = 'Sepak Bola, Liga Indonesia, Piala Indonesia, Bambang Pamungkas, Preview Pertandingan, Review Pertandingan, Ulasan Sepak Bola, Prediksi, Jadwal Pertandingan, Hasil Pertandingan, Football, Soccer. Arema Malang, Srwijaya FC, Persebaya, Persija Jakarta, Persipura Jayapura, PSM Makasar, Persisam Samarinda, Persiba Balikpapan, PSSI.';
                $metadesc = 'Berita sepak bola terkini dan terlengkap Liga Indonesia, live score, jadwal dan hasil pertandingan, profil dan foto pemain serta gaya hidup para pemain di luar lapangan.';
            } elseif ($catid == 5) {
                $active_menu = 1;
                $metatitle = 'Berita dan Foto Sepak Bola Terbaru Liga Champions, Jadwal dan Hasil Pertandingan, Klasemen, Profil Klub dan Profil Pemain';
                $metakey = 'Sepak Bola, Liga Champions, Cristiano Ronaldo, Wayne Rooney, Lionel Messi, Diego Milito, Kaka, Iniesta, David Villa, Xavi, Xabi Alonso, Sergio Ramos, Mourinho, Zlatan Ibrahimovic, David Beckham, Pato, Steven Gerrard, Frank Lampard, John Terry, Cesc Fabregas, Didier Drogba, Preview Pertandingan, Review Pertandingan, Manchester United, Arsenal, Chelsea, Barcelona, Real Madrid, Inter Milan, AS Roma, AC Milan, Bayern Munchen, Valencia, Schalke 04, Marseille. Lyon, Twente, Marseille, Prediksi, Jadwal Pertandingan, Hasil Pertandingan, Football, Soccer.';
                $metadesc = 'Berita sepak bola terkini dan terlengkap Liga Champions, live score, jadwal dan hasil pertandingan, profil dan foto pemain serta gaya hidup para pemain dan WAGS di luar lapangan.';
            } elseif ($catid == 6) {
                $active_menu = 1;
                //$metatitle = 'Liga Eropa : Jadwal, Hasil, Klasemen, Berita';
                $props_insight = 'Bola Eropa/Liga Europa';
                $metatitle = 'Berita dan Foto Sepak Bola Terbaru Liga Eropa UEFA, Jadwal dan Hasil Pertandingan, Klasemen, Profil Klub dan Profil Pemain';
                $metakey = 'Sepak Bola, Liga Eropa UEFA, Steven Gerrard, Fernando Torres, Alessandro Del Piero, Amauri, Diego Ribas, Preview Pertandingan, Review Pertandingan, Liverpool, Juventus, Villarreal, Atletico Madrid, Napoli, Paris Saint-Germain, Bayer Leverkusen, Aston Villa, Manchester City, Prediksi, Jadwal Pertandingan, Hasil Pertandingan, Football, Soccer.';
                $metadesc = 'Berita sepak bola terkini dan terlengkap Liga Eropa UEFA, live score, jadwal dan hasil pertandingan, profil dan foto pemain serta gaya hidup para pemain dan WAGS di luar lapangan.';
            } elseif ($catid == 7) {
                $active_menu = 2;
                $props_insight = 'Bola Dunia/Piala Dunia';
                $metatitle = 'Berita dan Foto Sepak Bola Terbaru Piala Dunia, Jadwal dan Hasil Pertandingan, Klasemen, Profil Klub dan Profil Pemain';
                $metakey = 'Sepak Bola, Piala Dunia, Kualifikasi Piala Dunia, WAG, David Villa, Andres Iniesta, Cristiano Ronaldo, Lionel Messi, Wayne Rooney, Kaka, Maradona, Brasil, Inggris, Italia, Argentina, Spanyol, Belanda, Jerman, Uruguay, Prancis, Afrika Selatan, World Cup, South Africa 2010, Brazil 2014, Foto Pemain, Foto Pertandingan, Profil Pemain, Profil Klub, Wallpaper, Preview Pertandingan, Review Pertandingan, Ulasan Sepak Bola, Prediksi, Jadwal Pertandingan, Hasil Pertandingan, Football, Soccer.';
                $metadesc = 'Berita sepak bola terkini dan terlengkap Piala Dunia, live score, jadwal dan hasil pertandingan, profil dan foto pemain serta gaya hidup para pemain dan WAGS di luar lapangan.';
            } elseif ($catid == 8) {
                $active_menu = 2;
                $props_insight = 'Bola Dunia/Piala Eropa';
                $metatitle = 'Berita dan Foto Sepak Bola Terbaru Piala Eropa 2012, Jadwal dan Hasil Pertandingan, Klasemen, Profil Klub dan Profil Pemain';
                $metakey = 'Sepak Bola, Piala Eropa, Kualifikasi Piala Eropa 2012, Spanyol, Belanda, Jerman, Denmark, Inggris, Irlandia, Prancis, Italia, David Villa, Andres Iniesta, Cristiano Ronaldo, Wayne Rooney, Steven Gerrard, Thomas Mueller, Mesut Ozil, Deco, Karim Benzema, Foto Pemain, Foto Pertandingan, Profil Pemain, Profil Klub, Wallpaper, Preview Pertandingan, Review Pertandingan, Ulasan Sepak Bola, Prediksi, Jadwal Pertandingan, Hasil Pertandingan, Football, Soccer.';
                $metadesc = 'Berita terkini Piala Eropa Euro 2012 disertai Jadwal, Prediksi, Skor, Klasemen Euro 2012 - Piala Eropa lengkap dengan Foto, Video dan Wallpaper. Rebut bola bertanda tangan Cristiano Ronaldo.';
            } elseif ($catid == 12) {
                $active_menu = 1;
                $props_insight = 'Bola Eropa/Lain-Lain';
                $metatitle = 'Berita dan Foto Sepak Bola Terbaru Liga Jerman, Prancis, Belanda dan Liga Eropa Lainnya, Jadwal dan Hasil Pertandingan, Klasemen, Profil Klub dan Profil Pemain';
                $metakey = 'Sepak Bola, Liga Jerman, Liga Prancis, Liga Belanda, Franck Ribery, Arjen Robben, Bayern Munchen, Miroslav Klose, Mesut Ozil, Thomas Mueller, Preview Pertandingan, Review Pertandingan, Prediksi, Jadwal Pertandingan, Hasil Pertandingan, Football, Soccer.';
                $metadesc = 'Berita sepak bola terkini dan terlengkap Liga Jerman, Liga Prancis, Liga Belanda dan kompetisi Eropa lainnya, live score, jadwal dan hasil pertandingan, profil dan foto pemain serta gaya hidup para pemain dan WAGS di luar lapangan.';
            } elseif ($catid == 14) {
                $active_menu = 4;
                $props_insight = 'Bolatainment';
                $metatitle = 'Berita dan foto pemain serta gaya hidup para pemain dan WAGS di luar lapangan';
                $metakey = 'Berita dan foto pemain serta gaya hidup para pemain dan WAGS di luar lapangan.';
                $metadesc = 'Sepak Bola, Gosip Bola, WAGS, Vanessa Peroncel, Victoria Beckham, Paris Hilton, Cristiano Ronaldo. Cheryl Cole, Kim Kadashian, Bolatainment.';
            } elseif ($catid == 24) {
                $active_menu = 3;
                $props_insight = 'Bola Indonesia/Tim Nasional';
                $metatitle = 'Berita dan Foto Sepak Bola Terbaru Tim Nasional Indonesia, Jadwal dan Hasil Pertandingan, Klasemen, Profil Klub dan Profil Pemain';
                $metakey = 'Sepak Bola, Indonesia, Tim Nasional Indonesia, Bambang Pamungkas, Boas Salosa, Firman Utina, Budi Sudarsono, Alfred Riedl, Nurdin Halid, Markus Horison, PSSI, Tim Garuda.';
                $metadesc = 'Berita sepak bola terkini dan terlengkap Tim Nasional Indonesia, live score, jadwal dan hasil pertandingan, profil dan foto pemain serta gaya hidup para pemain di luar lapangan.';
            }
            // other sport
            elseif ($catid == 17) {
                $active_menu = 9;
                $props_insight = 'Sport/Basket';
                $metatitle = 'Berita Terbaru Bola Basket';
                $metakey = 'Bola Basket, NBA, NBL, IBL, LA Lakers, New York Knicks, Miami Heat, Chicago Bulls, Phoenix Suns, Houston Rockets, San Antonio Spurs, Kobe Bryant, Michael Jordan.';
                $metadesc = 'Berita terbaru NBA, NBL, IBL dan kompetisi bola basket lainnya.';
            } elseif ($catid == 18) {
                $active_menu = 9;
                $props_insight = 'Sport/Bulutangkis';
                $metatitle = 'Berita Terbaru Bulu Tangkis';
                $metakey = 'Bulu Tangkis, Badminton, Thomas Cup, Uber Cup, Sudirman Cup, All-England Championships.';
                $metadesc = 'Berita terkni dari dunia Bulu Tangkis.';
            } elseif ($catid == 19) {
                $active_menu = 9;
                $props_insight = 'Sport/Tenis';
                $metatitle = 'Berita Terbaru Olahraga Tenis';
                $metakey = 'Tenis, Wimbledon, Australia Terbuka, Prancis Terbuka, Amerika Terbuka.';
                $metadesc = 'Berita terbaru dari olahraga tenis.';
            } elseif ($catid == 20) {
                $active_menu = 9;
                $props_insight = 'Sport/Otomotif';
                $metatitle = 'Berita Terbaru Dari Dunia Balap, F1, MotoGP dan Lainnya';
                $metakey = 'Otomotif, F1, Motogp, balap, racing.';
                $metadesc = 'Berita terbaru dari dunia balap, F1, MotoGP dan kompetisi otomotif lainnya.';
            } elseif ($catid == 21) {
                $active_menu = 9;
                $props_insight = 'Sport/Voli';
                $metatitle = 'Bola Voli';
                $metakey = 'Voli';
                $metadesc = 'Berita dari semua kompetisi bola voli.';
            } elseif ($catid == 22) {
                $active_menu = 9;
                $props_insight = 'Sport/Sport';
                $metatitle = 'Olahraga lain - lain';
                $metakey = 'Olahraga lain - lain';
                $metadesc = 'Berita Terbaru olahraga lain-lain ';
            } elseif ($catid == 13) {
                $active_menu = 2;
                $props_insight = 'Bola Dunia/Lain-Lain';
                $metatitle = 'Berita dan Foto Sepak Bola Terbaru Piala dan Liga Dunia Lainnya, Jadwal dan Hasil Pertandingan, Klasemen, Profil Klub dan Profil Pemain';
                $metakey = 'Sepak Bola, Piala Asia, Piala Afrika, Piala Amerika, Park Ji-Sung, Kaka, Alexandre Pato, Didier Drogba, Samuel Etoo, Preview Pertandingan, Review Pertandingan, Ulasan Sepak Bola, Prediksi, Jadwal Pertandingan, Hasil Pertandingan, Football, Soccer.';
                $metadesc = 'Berita sepak bola terkini dan terlengkap Piala Asia, Piala Afrika, Piala Amerika, Liga Amerika Latin, Liga Asia, Liga Afrika dan pertandingan persahabatan, live score, jadwal dan hasil pertandingan, profil dan foto pemain serta gaya hidup para pemain dan WAGS di luar lapangan.';
            } elseif ($catid == 29) {
                $active_menu = 5;
                $props_insight = 'Jadwal & Skor/Jadwal Tv';
                $metatitle = 'Jadwal Televisi';
                $metakey = 'Jadwal Televisi, sctv, rcti, indonesia, tvone, antv, metro tv, global sport';
                $metadesc = 'Jadwal Televisi terlengkap dan update setiap saat.';
            } elseif ($catid == 30) {
                $active_menu = 2;
                $props_insight = 'Bola Dunia/Amerika Latin';
                $metatitle = 'Sepak Bola Amerika Latin';
                $metakey = 'Berita Terbaru Amerika Latin, Robinho, Neymar';
                $metadesc = 'Berita Terbaru dari Amerika Latin.';
            } elseif ($catid == 31) {
                $active_menu = 2;
                $props_insight = 'Bola Dunia/Asia';
                $metatitle = 'Sepak Bola Asia';
                $metakey = 'Berita Terbaru dari Asia, Jepang, Indonesia, Arab Saudi, India, China';
                $metadesc = 'Berita Terbaru dari Asia.';
            } else {
                $metatitle = 'Tinju, Golf, Atletik, Balap Sepeda dan Olahraga Lainnya';
                $metakey = 'Tinju, Golf, Atletik, Balap Sepeda.';
                $metadesc = 'Berita dari dunia tinju, golf, atletik, balap sepeda dan olahraga lainnya.';
            }

            if ($page > 0) {
                $metatitle = $first_title;
                $metadesc = $first_desc;
                $metakey = $first_keywords;
            }
            
            if ($devel == false) {
                write_file($filename, $content, $metatitle, $metakey, $metadesc, '', true, 'full', $active_menu);
                insert_property_og($filename, $metatitle, $fileurl, '', '109215469105623', $metadesc, $props_insight);
                if ($page == 0) {
                    set_top_tagbar($sqlDB, $filename);
                }
                echo generated_link($fileurl);
            } else {
                write_file_dev($filename, $content, $metatitle, $metakey, $metadesc, '', true, 'full', $active_menu);
                insert_property_og($filename, $metatitle, $fileurl, '', '109215469105623', $metadesc, $props_insight);
                echo generated_link($fileurl);
            }
            

            $counter = 1;
            $page++;
            $paging = global_paging_10($page, $pagenum, 'index'); //$dir[$i][1]
            $content = '
        <div class="bigcon">
                    <div class="bigcon2">
                        <div class="nav">
                            <a ' . ga_trackEvent('link_nav') . ' href="/" style="text-decoration:none;">HOME</a> &raquo;
                            <a ' . ga_trackEvent('link_nav') . ' href="/' . $dir[$i][1] . '/" style="text-decoration:none;">' . strtoupper($dir[$i][0]) . '</a>
                        </div>
                        <br />
                        <div class="subindexcat_v2">' . strtoupper($dir[$i][0]) . '</div>
        ';
        }
        
        if ($LIMIT_PAGE && $page >= $LIMIT_PAGE) {
            break;
        }
    }
    $sqlDB->sql_freeresult();

    $content .= '
                <br />
                <center>' . $paging . '</center>
            </div>
        </div>
    ';
    
    if ($devel == false) {
        $filename = BOLADIR . $dir[$i][1] . '/index.html';
        $fileurl = BOLAURL . $dir[$i][1] . '/index.html';
        if ($page > 0) {
            $filename = BOLADIR . $dir[$i][1] . '/index' . $page . '.html';
            $fileurl = BOLAURL . $dir[$i][1] . '/index' . $page . '.html';
        }
    
        if ($page > 0) {
            $metatitle = $first_title;
            $metadesc = $first_desc;
            $metakey = $first_keywords;
        }
        
    
        write_file($filename, $content, $metatitle, $metakey, $metadesc, '', true, 'full', $active_menu);
        insert_property_og($filename, $metatitle, $fileurl, '', '109215469105623', $metadesc, $props_insight);
        set_top_tagbar($sqlDB, $filename);
        echo generated_link($fileurl);
    }
}

/*
  function pagingNews($page, $pagenum, $dir)
  {
  $return = '
  <div style="text-align:center;">
  ';

  $pb = 11;
  $pt = $pagenum-10;

  if($page >= $pb && $page <= $pt) {
  $bl = $page - 10;
  $tl = $page + 9;
  } elseif($page < $pb) {
  $bl = 1;
  $tl = 20;
  } else {
  $bl = $pagenum - 19;
  $tl = $pagenum;
  }

  if ($tl > $pagenum) $tl = $pagenum;

  for($i=$bl;$i<=$tl;$i++) {
  $ap = $i-1;
  if($ap == $page) {
  $return .= '<span class="paging_nolink">'.str_pad($i, 2, '0', STR_PAD_LEFT).'</span>';
  } elseif($i==1) {
  $return .= '<span class="paging"><a href="/'.$dir.'/">'.str_pad($i, 2, '0', STR_PAD_LEFT).'</a></span>';
  } else {
  $return .= '<span class="paging"><a href="/'.$dir.'/index_'.$ap.'.html">'.str_pad($i, 2, '0', STR_PAD_LEFT).'</a></span>';
  }
  }

  $return .= '
  </div>
  ';
  return $return;
  }
 */

/**
 * generate news group page
 *
 * @map url: http://www.bola.net/$news_group.html
 */
function generate_news_groups($sqlDB, $devel = false)
{
    global $def_olahraga_lain, $def_liga_eropa, $def_bola_dunia, $def_ragam, $def_bola_indonesia, $cdn_url;
    global $library_url, $library_dir, $day_list_ina, $club_url, $profile_url, $galeri_url, $wallpaper_url, $profile_dir;
    global $headline_media, $headline_media_url, $thumbnail_media, $thumbnail_media_url, $club_media_url, $club_media, $img_lazy_load, $image_library_url;
    $array_group = array(
        1 => 'olahraga_lain.html',
        2 => 'bola_eropa.html',
        3 => 'bola_dunia.html',
        4 => 'bola_indonesia.html'
    );

    $all_category = array();
    $q = "SELECT * FROM dbcategory
        WHERE (category_level='1' OR category_level='2') AND category_status='1'
        ORDER BY category_id";
    $rc = $sqlDB->sql_query($q, true) or die(__LINE__ . ' = ' . mysql_error());
    while ($row = $sqlDB->sql_fetchrow($rc)) {
        $all_category[$row['category_id']] = array($row['category_url'], $row['category_name']);
    }
    $sqlDB->sql_freeresult();

    $active_menu = 0;
    foreach ($array_group as $key => $value) {
        switch ($key) {
            case 1: $arr_category = $def_olahraga_lain;
                $title = 'Olahraga Lain';
                $description = 'Olahraga Lainnya, tenis, basket, voli, otomotif';
                $props_insight = 'Sport';
                $active_menu = 9;
                break;
            case 2: $arr_category = $def_liga_eropa;
                $title = 'Liga Eropa';
                $description = 'Liga Eropa, Liga Inggris, Liga Italia, Liga Spanyol, Liga Champions, Liga Eropa UEFA';
                $props_insight = 'Bola Eropa';
                $active_menu = 1;
                break;
            case 3: $arr_category = $def_bola_dunia;
                $title = 'Piala Dunia, Piala Eropa';
                $description = 'Piala Dunia, Piala Eropa';
                $props_insight = 'Bola Dunia';
                $active_menu = 2;
                break;
            case 4: $arr_category = $def_bola_indonesia;
                $title = 'Bola Indonesia';
                $description = 'Bola Indonesia, Liga Indonesia, Tim Nasional, Super Liga Indonesia';
                $props_insight = 'Bola Indonesia';
                $active_menu = 3;
                break;
        }

        $content = '
            <div class="bigcon">
                <div class="bigcon2">
                    <div class="nav"><a href="/" style="text-decoration:none;" ' . ga_trackEvent('link_nav') . '>HOME</a> &raquo; ' . strtoupper($title) . '</div>
                </div>
            </div>
            <br/>
        ';
        foreach ($arr_category as $cat) {
            $q = "SELECT idnews, schedule, title, synopsis, image, url, category, image_headline, source
                FROM `dbnews` WHERE category='$cat' AND schedule<NOW() AND level != '0' AND level !='3'
                ORDER BY schedule DESC LIMIT 10";
            $r = $sqlDB->sql_query($q, true) or die(__LINE__ . ' = ' . mysql_error());

            while ($row = $sqlDB->sql_fetchrow($r)) {
                $hlimage = $thumbnail_media . $row['image_headline']; //str_pad($row['idnews'], 10, '0', STR_PAD_LEFT) .'.jpg';
                //if (is_file($hlimage))
                if ($row['image_headline']) {
                    #$img = $thumbnail_media_url . $row['image_headline']; //str_pad($row['idnews'], 10, '0', STR_PAD_LEFT) .'.jpg';
                    $img = ((strlen($row['image_headline']) == 14) ? $thumbnail_media_url . $row['image_headline'] : $image_library_url . bola_image_news($row['image_headline'], '125/'));
                    if (strtolower($row['source']) == 'persib.co.id') {
                        $img = persibnews_image_url($row['image_headline']);
                    }

                    break;
                } else {
                    $row['image'] = str_replace($library_url, '/', $row['image']);
                    $hlimage = $library_dir . str_replace('/p/', 'p/', $row['image']);

                    if (is_file($hlimage)) {
                        $img = $cdn_url . 'library/' . str_replace('/p/', 'p/', $row['image']);
                        break;
                    } else {
                        continue;
                        $img = str_replace('//p', '/p', $cdn_url . 'library/' . str_replace($library_url, '/', $row['image']));
                    }
                    if (strtolower($row['source']) == 'persib.co.id') {
                        $img = persibnews_image_url($row['image_headline'], '175');
                    }
                }
            }

            $last_id = $row['idnews'];
            $title_cencored = strlen($row['title']) > 55 ? substr($row['title'], 0, 53) . '..' : $row['title'];
            $synopsis_cencored = $row['synopsis'];

            $nschedule = preg_replace('/(\d{4,4})\-(\d{2,2})\-(\d{2,2}) (\d{2,2})\:(\d{2,2})\:(\d{2,2})/', '\3-\2-\1 \4:\5', $row['schedule']);
            $hari = $day_list_ina[date('w', strtotime($nschedule))];

            $ntitle = strtoupper($all_category[$row['category']][1]);
            $nurl = $all_category[$row['category']][0];
            $nurl2 = $row['url'];

            $sqlDB->sql_freeresult();

            $limit_view = $key == 4 ? 10 : 5;
            $q = "SELECT idnews, schedule, title, synopsis, image, url, category
                FROM `dbnews` WHERE idnews<>'$last_id' AND category='$cat' AND schedule<NOW() AND level != '0' AND level !='3'
                ORDER BY schedule DESC LIMIT $limit_view";
            $r = $sqlDB->sql_query($q, true) or die(__LINE__ . ' = ' . mysql_error());

            $related = '';
            while ($row = $sqlDB->sql_fetchrow($r)) {
                $row['schedule'] = preg_replace('/(\d{4,4})\-(\d{2,2})\-(\d{2,2}) (\d{2,2})\:(\d{2,2})\:(\d{2,2})/', '\3-\2-\1 \4:\5', $row['schedule']);
                $related .= '
                    <div class="bcontent1">
                        <span>(' . $row['schedule'] . ')<span>
                        <a href="' . BOLAURL . $all_category[$row['category']][0] . '/' . $row['url'] . '.html" ' . ga_trackEvent($all_category[$row['category']][0], $row['title']) . '>' . $row['title'] . '</a>
                    </div>
                ';
            }

            $content .= '
                <div class="box1">
                    <div class="boxheader3"><a href="' . BOLAURL . $nurl . '/" ' . ga_trackEvent($nurl) . '>' . $ntitle . '</a></div>
                        <div class="bcontent2">
                            <div class="indschedule">' . $hari . ', ' . $nschedule . '</div>
                            <span><a href="' . BOLAURL . $nurl . '/' . $nurl2 . '.html" ' . ga_trackEvent($nurl, $title_cencored) . '>' . $title_cencored . '</a></span>
                            
                            <br class="clear"/>
                            <div class="bcontent2_image">
                                <img class="lazy_loaded" src="'.$img_lazy_load.'" data-src="' . $img . '" width="130" height="84" /><br class="clear"/><div>&nbsp;</div>
                            </div>
                            
                            <div>' . $synopsis_cencored . '</div>
                            <div class="clear"></div>
                        </div>
                    
                    <div class="boxcontent3">
                        ' . $related . '
                        <div class="grdetil"><a href="' . BOLAURL . $nurl . '/" ' . ga_trackEvent($nurl) . '>Selengkapnya</a></div>
                    </div>
                </div>
                <br/>
            ';

            $sqlDB->sql_freeresult();
        }

        $content .= '<div class="clear"></div>';
        
        if ($devel == false) {
            $filename = BOLADIR . $value;
            $filename_url = BOLAURL . $value;
    
            write_file($filename, $content, $title, $title, $description, '', true, 'full', $active_menu);
            insert_property_og($filename, $title, $filename_url, '', '109215469105623', $description, $props_insight);
            set_top_tagbar($sqlDB, $filename);
            echo generated_link($filename_url);
        } else {
            $filename = APPSDIR . 'devel/generate/www/' . $value;
            $filename_url = APPSURL . 'devel/generate/www/' . $value;
    
            write_file_dev($filename, $content, $title, $title, $description, '', true, 'full', $active_menu);
            insert_property_og($filename, $title, $filename_url, '', '109215469105623', $description, $props_insight);
            set_top_tagbar($sqlDB, $filename);
            echo generated_link($filename_url);
        }
    }
    
    if ($devel == true) {
        exit();
    }

    // ragam
    $title = 'Editorial, Profil Pemain, Profil Klub';
    $description = 'Editorial, Profil Pemain, Profil Klub';
    $filename = BOLADIR . 'ragam.html';
    $filename_url = BOLAURL . 'ragam.html';
    $props_insight = 'Ragam';
    
    $content = '
        <div class="bigcon">
            <div class="bigcon2">
                <div class="nav"><a href="/" style="text-decoration:none;" ' . ga_trackEvent('link_nav') . '>HOME</a> &raquo; EDITORIAL</div>
            </div>
        </div>
        <br/>
    ';

    $row = array();
    $q = "SELECT idarticle, schedule, title, synopsis, image, url, category
        FROM `dbarticles` WHERE category='10' AND level <> '0' AND schedule<NOW() AND image<>''
        ORDER BY schedule DESC LIMIT 10";
    $r = $sqlDB->sql_query($q, true) or die(__LINE__ . ' = ' . mysql_error());

    while ($row = $sqlDB->sql_fetchrow($r)) {
        $row['image'] = str_replace($library_url, '/', $row['image']);
        $hlimage = MEDIADIR . str_replace('/p/', 'p/', $row['image']);
        if (is_file($hlimage)) {
            $img = $cdn_url . 'library/' . str_replace('/p/', 'p/', $row['image']);
            break;
        } else {
            continue;
            $img = str_replace('//p', '/p', $cdn_url . 'library/' . str_replace($library_url, '/', $row['image']));
        }
    }

    $last_id = $row['idarticle'];
    $title_cencored = strlen($row['title']) > 55 ? substr($row['title'], 0, 53) . '..' : $row['title'];
    $synopsis_cencored = $row['synopsis'];

    $nschedule = preg_replace('/(\d{4,4})\-(\d{2,2})\-(\d{2,2}) (\d{2,2})\:(\d{2,2})\:(\d{2,2})/', '\3-\2-\1 \4:\5', $row['schedule']);
    $hari = $day_list_ina[date('w', strtotime($nschedule))];

    $ntitle = strtoupper($all_category[$row['category']][1]);
    $nurl = $all_category[$row['category']][0];
    $nurl2 = $row['url'];

    $sqlDB->sql_freeresult();

    $q = "SELECT idarticle, schedule, title, synopsis, image, url, category FROM `dbarticles` WHERE idarticle<>'$last_id' AND category='10' AND schedule<NOW() AND level>0 ORDER BY schedule DESC LIMIT 5";
    $r = $sqlDB->sql_query($q, true) or die(__LINE__ . ' = ' . mysql_error());

    $related = '';
    while ($row = $sqlDB->sql_fetchrow($r)) {
        $row['schedule'] = preg_replace('/(\d{4,4})\-(\d{2,2})\-(\d{2,2}) (\d{2,2})\:(\d{2,2})\:(\d{2,2})/', '\3-\2-\1 \4:\5', $row['schedule']);
        $related .= '
            <div class="bcontent1"><span>(' . $row['schedule'] . ')<span> <a href="' . BOLAURL . $all_category[$row['category']][0] . '/' . $row['url'] . '.html">' . $row['title'] . '</a></div>
        ';
    }
    $sqlDB->sql_freeresult();

    $content .= '
        <div class="box1">
            <div class="boxheader3"><a href="' . BOLAURL . $nurl . '/">' . $ntitle . '</a></div>
                <div class="bcontent2">
                    <div class="indschedule">' . $hari . ', ' . $nschedule . '</div>
                    <span><a href="' . BOLAURL . $nurl . '/' . $nurl2 . '.html">' . $title_cencored . '</a></span>
                    
                    <br class="clear"/>
                    <div class="bcontent2_image"><img class="lazy_loaded" src="'.$img_lazy_load.'" data-src="' . $img . '" width="130" height="71" /><br class="clear"/><div>&nbsp;</div></div>
                    
                    <div>' . $synopsis_cencored . '</div>
                    <div class="clear"></div>
                </div>
                <div class="boxcontent3">
                    ' . $related . '
                    <div class="grdetil"><a href="' . BOLAURL . $nurl . '/">Selengkapnya</a></div>
                </div>
            </div>
            <br/>
    ';

    // club profile
    $array = array(1 => 'inggris', 2 => 'italia', 3 => 'spanyol');
    $inggris = '';
    $team_image_url = $club_media_url;
    $team_image_dir = $club_media;

    foreach ($array as $k => $v) {
        $qs = "SELECT season_id FROM dbseason WHERE season_cat_id='$k' AND season_status='1' ORDER BY season_id DESC LIMIT 1";
        $rs = $sqlDB->sql_query($qs, true);
        $rows = $sqlDB->sql_fetchrow($rs);

        $q = "SELECT A.team_id, A.team_name, A.team_image FROM dbteam A, dbparticipant B WHERE part_season_id='$rows[season_id]' AND team_id=part_team_id ORDER BY RAND()";
        $r = $sqlDB->sql_query($q, true);
        $num = $sqlDB->sql_numrows($r);

        $ccount = 1;
        $related = '';
        while ($row = $sqlDB->sql_fetchrow($r)) {
            $image_club = $team_image_url . $v . '/' . $row['team_image'];
            $image_club_dir = $team_image_dir . $v . '/' . $row['team_image'];
            if (!is_file($image_club_dir)) {
                continue;
            }

            $q2 = "SELECT url FROM dbarticles WHERE team_id='$row[team_id]' LIMIT 1";
            $r2 = $sqlDB->sql_query($q2);
            $row2 = $sqlDB->sql_fetchrow($r2);

            $related .= '
                <div class="c1">
                    <a href="' . $club_url . $row2['url'] . '.html">
                        <img class="lazy_loaded" src="'.$img_lazy_load.'" data-src="' . $image_club . '"  width="80" height="71"/>
                    </a><br/>
                    ' . $row['team_name'] . '
                </div>
            ';

            if ($ccount == 4) {
                break;
            }
            $ccount++;
        }
        $sqlDB->sql_freeresult();

        switch ($v) {
            case 'inggris': $club_detail = $club_url;
                break;
            case 'italia': $club_detail = $club_url . 'italia.html';
                break;
            case 'spanyol': $club_detail = $club_url . 'spanyol.html';
                break;
        }

        $content .= '
            <div class="box1">
                <div class="boxheader3"><a href="' . $club_detail . '">LIGA ' . strtoupper($v) . '</a></div>
                    <div class="boxcontent3">
                        ' . $related . '
                        <div class="grdetil"><a href="' . $club_detail . '">Selengkapnya</a></div>
                    </div>
                </div>
                <br/>
        ';
    }



    // player profile
    $q = "SELECT player_name, player_url FROM player_profile WHERE player_status='1' AND player_bio<>'' ORDER BY RAND() LIMIT 10";
    $r = $sqlDB->sql_query($q);

    $related = '';
    while ($row = $sqlDB->sql_fetchrow($r)) {
        $related .= '
            <div class="bcontent1"><a href="' . $profile_url . $row['player_url'] . '/">' . $row['player_name'] . '</a></div>
        ';
    }
    $sqlDB->sql_freeresult();

    $content .= '
        <div class="box1">
            <div class="boxheader3"><a href="' . $profile_url . '">PROFIL PEMAIN</a></div>
                <div class="boxcontent3">
                    ' . $related . '
                    <div class="grdetil"><a href="' . $profile_url . '">Selengkapnya</a></div>
                </div>
            </div>
        <br/>
    ';

    write_file($filename, $content, $title, $title, $description, '', true, 'full', 6);
    insert_property_og($filename, $title, $filename_url, '', '109215469105623', $description, $props_insight);
    set_top_tagbar($sqlDB, $filename);
    echo generated_link($filename_url);

    // gallery
    $title = 'Berita Foto, Wallpaper, Foto Pemain';
    $description = 'Berita Foto, Wallpaper, Foto Pemain';
    $filename = BOLADIR . 'gallery.html';
    $filename_url = BOLAURL . 'gallery.html';
    $props_insight = 'Gallery';
    $content = '
        <div class="bigcon">
            <div class="bigcon2">
                <div class="nav"><a href="/" style="text-decoration:none;" ' . ga_trackEvent('link_nav') . '>HOME</a> &raquo; GALERI</div>
            </div>
        </div>
        <br/>
    ';

    $q = "SELECT * FROM dbgallery WHERE level>0 AND schedule<NOW() AND catvalue<>'23' AND level <>'0' ORDER BY schedule DESC LIMIT 3";
    $r = $sqlDB->sql_query($q, true);
    $content_foto_terbaru = '';
    while ($row = $sqlDB->sql_fetchrow($r)) {
        $fixed_url = strtolower(str_replace(' ', '_', $row['category']));
        $index_img = $cdn_url . 'galeri/' . $fixed_url . '/t/index.jpg';
        $short_title = strlen($row['title']) > 40 ? substr($row['title'], 0, 38) . '..' : $row['title'];
        $content_foto_terbaru .= '
        <div class="boxfoto">
                <a class="boxfotolink" href="' . $galeri_url . $fixed_url . '.html">
                    <div><img src="' . $index_img . '" width="135"/><br />' . $short_title . '</div>
                </a>
            </div>
        ';
    }
    $sqlDB->sql_freeresult();

    $content .= '
        <div class="box1">
            <div class="boxheader3"><a href="' . $galeri_url . '">BERITA FOTO</a></div>
                <div class="boxcontent3">
                    ' . $content_foto_terbaru . '
                    <div class="grdetil"><a href="' . $galeri_url . '">Selengkapnya</a></div>
                </div>
            </div>
            <br/>
    ';

    // wallpaper
    $wallpaper = '';
    $q = "SELECT * FROM dbwallpaper WHERE level>0 ORDER BY RAND() LIMIT 3";
    $r = $sqlDB->sql_query($q, true) or die(__LINE__ . ' = ' . mysql_error());
    while ($row = $sqlDB->sql_fetchrow($r)) {
        $img_url = $cdn_url . 'wallpaper/t/' . str_pad($row['id'], 5, '0', STR_PAD_LEFT) . '.' . $row['type'];
        $wallpaper .= '
            <div style="float:left;width:147px;text-align:center;">
                <a href="' . $wallpaper_url . $row['url'] . '.html"><img src="' . $img_url . '" width="140" alt=""/></a>
                <br/>
                ' . $row['title'] . '
            </div>
        ';
    }
    $sqlDB->sql_freeresult();

    $content .= '
        <div class="box1">
            <div class="boxheader3"><a href="' . $wallpaper_url . '">WALLPAPER</a></div>
                <div class="boxcontent3">
                    ' . $wallpaper . '
                    <div class="grdetil"><a href="' . $wallpaper_url . '">Selengkapnya</a></div>
                </div>
            </div>
            <br/>
    ';

    // foto pemain
    $sql = "SELECT player_name,player_url FROM player_profile WHERE player_status='1' ORDER BY RAND()";
    $res = $sqlDB->sql_query($sql);
    $counter = 0;

    $arr_list = array();
    while ($row = $sqlDB->sql_fetchrow($res)) {
        $photo_dir = $profile_dir . $row['player_url'] . '/foto/';
        if (!is_file($photo_dir . 'index.html')) {
            continue;
        }

        $arr_list[] = $row;
        if ($counter > 50) {
            break;
        }
        $counter++;
    }
    $sqlDB->sql_freeresult();

    shuffle($arr_list);
    $arr_list = array_slice($arr_list, 0, 10);
    $foto = '';
    foreach ($arr_list as $row) {
        $foto .= '
            <div class="bcontent1"><a href="' . $profile_url . $row['player_url'] . '/foto/">' . $row['player_name'] . '</a></div>
        ';
    }

    $content .= '
        <div class="box1">
            <div class="boxheader3"><a href="' . $profile_url . 'foto_index.html">FOTO PEMAIN</a></div>
                <div class="boxcontent3">
                    ' . $foto . '
                    <div class="grdetil"><a href="' . $profile_url . 'foto_index.html">Selengkapnya</a></div>
                </div>
            </div>
            <br/>
    ';

    write_file($filename, $content, $title, $title, $description, '', true, 'full', 7);
    insert_property_og($filename, $title, $filename_url, '', '109215469105623', $description, $props_insight);
    set_top_tagbar($sqlDB, $filename);
    echo generated_link($filename_url);
    // end gallery

    generate_review_preview($sqlDB, 'review');
    generate_review_preview($sqlDB, 'preview');
}

/**
 * generate news comment
 * also generate comment page(s)
 */
function generate_news_comment($sqlDB, $id, $message = true)
{
    global $library_url, $month_list_ina, $cdn_url;

    $q2 = "
        SELECT A.`date`, A.`name`, A.`email`, A.`comment`, A.`location`, A.`avatar` AS avatar2, A.`flag`, B.`avatar`, B.`id`
        FROM dbnews_comment A LEFT JOIN dbmember B ON B.id=A.member_id
        WHERE A.idnews_comment='$id' AND A.confirmation > 0
        ORDER BY A.`date` DESC";
    $r2 = $sqlDB->sql_query($q2, true) or die(__LINE__ . ' = ' . mysql_error());
    $num2 = $sqlDB->sql_numrows($r2);
    if ($num2 == 0) {
        return 0;
    }

    $sql = "
        SELECT dbnews.*, category_name, category_url
        FROM dbnews,dbcategory
        WHERE idnews=$id AND schedule <> '00-00-0000 00:00:00' AND dbnews.category=dbcategory.category_id LIMIT 1";
    $res = $sqlDB->sql_query($sql, true) or die(__LINE__ . ' = ' . mysql_error());
    $row = $sqlDB->sql_fetchrow($res);

    $dir = getAllNewsCat($sqlDB);

    $comment_dir = BOLADIR . $dir[$row['category']][1] . '/komentar/';
    $comment_url = BOLAURL . $dir[$row['category']][1] . '/komentar/';
    $news_url = BOLAURL . $dir[$row['category']][1] . '/' . $row['url'] . '.html';

    if (!is_dir($comment_dir)) {
        mkdir($comment_dir);
    }

    $counter = 0;
    $page = 0;
    $total_page = ceil($num2 / 20);

    $comment_form = '
        <div id="frmcomment"></div>
        <div class="clear"></div>
        <script type="text/javascript">loadfrmcomment("newscomment|' . $row['idnews'] . '|' . $num2 . '|' . $row['url'] . '");</script>
    ';

    $latest = '';
    while ($row2 = $sqlDB->sql_fetchrow($r2)) {
        if ($counter % 20 == 0) {
            $content = '
                <div class="bigcon">
                    <div class="bigcon2">
                    <div class="nav" ' . ga_trackEvent('link_nav') . '><a href="/" style="text-decoration:none;">HOME</a> &raquo; <a href="/' . $row['category_url'] . '/" style="text-decoration:none;">' . strtoupper($row['category_name']) . '</a> &raquo; ' . strtoupper($row['title']) . ' &raquo; KOMENTAR</div>
                    <br/>
                    <div style="font-size:20px;font-weight:bold;line-height:24px;text-align:center;padding-bottom:10px;"><a href="' . $news_url . '">' . $row['title'] . '</a></div>
                    <div class="coms">' . $row['synopsis'] . ' <a href="' . $news_url . '" class="bluelink">...selengkapnya</a></div>
                    ' . $comment_form . '
                    <br/>
            ';
        }
        $counter++;

        list($tgl, $jm) = explode(' ', $row2['date']);
        list($jm1, $jm2, $jm3) = explode(':', $jm);
        $tgly = substr($tgl, 0, 4);
        $tglm = substr($tgl, 5, 2);
        $tgld = substr($tgl, 8, 2);
        $tglmi = $month_list_ina[intval($tglm) - 1];
        $tgl_indo = $jm1 . ':' . $jm2 . ' ' . $tgld . ' ' . $tglmi . ' ' . $tgly;

        $date = preg_replace('/(\d{4,4})\-(\d{2,2})\-(\d{2,2}) (\d{2,2})\:(\d{2,2})\:(\d{2,2})/', '\4:\5 \3-\2-\1', $row2['date']);
        $set_location = '';

        if ($row2['location']) {
            $set_location = ', ' . $row2['location'];
        }

        $image_avatar = ($row2['avatar'] && $row2['avatar'] != 'NULL') ? $row2['avatar'] : $cdn_url . 'library/i/v1/avatar.jpg';
        $span_style = '';
        if ($row2['flag'] == '2') {
            $listcomment = explode(' : ', $row2['comment']);
            $to_delete = $listcomment[0];
            $row2['comment'] = preg_replace('/^' . preg_quote($to_delete) . ' : /si', '', $row2['comment']);
        }

        if ($row2['avatar2'] && $row2['flag'] == '2') {
            $row2['avatar2'] = str_replace('_normal.', '_bigger.', $row2['avatar2']);
            $user_image_thumb = '<a href="http://twitter.com/' . $to_delete . '" target="_blank"><img src="' . $row2['avatar2'] . '" alt="" width="67" height="68"></a>';
            $span_style = ' &nbsp;&nbsp; <img src="' . $cdn_url . 'library/i/v2/twitter_small.jpeg" alt=""/>';
        } elseif ($row2['id']) {
            $user_image_thumb = '<a href="' . create_user_link($row2['id'], 'news') . '"><img src="' . $image_avatar . '" alt="" width="67" height="68"/></a>';
        } else {
            $user_image_thumb = '<img src="' . $image_avatar . '" alt="" width="67" height="68"/>';
        }

        if ($counter < 6) {
            // get latest comment, show in news
            $latest .= '
                <div class="coleft">
                    ' . $user_image_thumb . '
                </div>
                <div class="coright">
                    <div class="cotop">
                        <strong>' . $row2['name'] . '</strong>' . $set_location . ' ' . $span_style . '<br/>
                        <span class="codate">' . $tgl_indo . '</span>
                        <div class="cocont">
                        ' . nl2br(striped_content($row2['comment'])) . '
                        </div>
                    </div>
                    <div class="cobot"></div>
                </div>
            ';
        }

        $content .= '
            <div class="coleft">
                ' . $user_image_thumb . '
            </div>
            <div class="coright">
                <div class="cotop">
                    <strong>' . $row2['name'] . '</strong>' . $set_location . ' ' . $span_style . '<br/>
                    <span class="codate">' . $tgl_indo . '</span>
                    <div class="cocont">
                    ' . nl2br(striped_content($row2['comment'])) . '
                    </div>
                </div>
                <div class="cobot"></div>
            </div>
        ';

        if ($counter % 20 == 0 || $counter == $num2) {
            $paging = global_paging_10($page, $total_page, $row['url']);

            $content .= '
                    <div class="clear"></div><br/>
                    ' . $paging . '
                    <div class="clear"></div>
                    </div>
                </div>
            ';

            $filename = $comment_dir . $row['url'] . ($page == 0 ? '' : $page) . '.html';
            $fileurl = $comment_url . $row['url'] . ($page == 0 ? '' : $page) . '.html';

            write_file($filename, $content, 'Komentar Berita ' . $row['title'], $row['title'], $row['title']);
            if ($message) {
                echo generated_link($fileurl);
            }

            $page++;
        }
    }

    $sqlDB->sql_freeresult($res);
    $sqlDB->sql_freeresult($r2);

    return array($num2, $latest);
}

/**
 * DEPRECATED ===> CHANGED TO apps/textclip/
 * create textclip
 * category = all category from 'olahraga lain'
 */
/*
  function generate_news_textclip($sqlDB)
  {
  global $def_olahraga_lain;

  $textclip = 'document.write(\'<ul>\');';
  $q = "SELECT * FROM dbtextclip WHERE status='1' ORDER BY schedule DESC LIMIT 15";
  $r = $sqlDB->sql_query($q, true) or die (__LINE__ .' = '. mysql_error());
  while ($row = $sqlDB->sql_fetchrow($r))
  {
  $row['schedule'] = preg_replace('/(\d{4,4})\-(\d{2,2})\-(\d{2,2}) (\d{2,2})\:(\d{2,2})\:(\d{2,2})/', '\3-\2-\1 \4:\5', $row['schedule']);
  $textclip .= '
  document.write(\'<li> &nbsp;'.$row['schedule'].' <a href="http://apps.bola.net/click.php?id='.$row['id'].'&n=tck&url='.$row['url'].'"><b>'.(addslashes($row['title'])).'</b></a></li>\');
  ';
  }
  $textclip .= 'document.write(\'</ul>\');';
  file_put_contents(BOLADIR .'data/textclip.js', $textclip, LOCK_EX);

  echo BOLADIR ."data/textclip.js<br/>\n";
  }
 */

/**
 * create review and preview page(s)
 * get pattern from news title which started with review or preview
 */
function generate_review_preview($sqlDB, $patt = 'review')
{
    global $library_dir, $library_url, $day_list_ina, $schedule_dir, $schedule_url, $cdn_url,$img_lazy_load, $thumbnail_media_url, $image_library_url;

    $memcache = new Memcache;
    bola_memcached_connect($memcache);

    $all_category = array();
    $all_category_str = '';

    $q = "SELECT * FROM dbcategory WHERE category_level='1' AND category_status='1' ORDER BY category_id";
    $rc = $sqlDB->sql_query($q);
    while ($row = $sqlDB->sql_fetchrow($rc)) {
        $all_category[$row['category_id']] = $row['category_url'];
        $all_category_str .= "'" . $row['category_id'] . "',";
    }
    $sqlDB->sql_freeresult();
    $all_category_str = substr($all_category_str, 0, -1);

    $menu_top = '
        <div class="topmenu">
            <a href="' . $schedule_url . 'score.html">Skor Terkini</a>
            &nbsp;&nbsp;&nbsp;&nbsp;
            <a href="' . $schedule_url . '">Jadwal</a>
            &nbsp;&nbsp;&nbsp;&nbsp;
            <a href="' . $schedule_url . 'klasemen_liga_inggris.html">Klasemen</a>
            &nbsp;&nbsp;&nbsp;&nbsp;
            <a href="' . $schedule_url . 'preview.html">Preview</a>
            &nbsp;&nbsp;&nbsp;&nbsp;
            <a href="' . $schedule_url . 'review.html">Review</a>
        </div>
    ';

    $content_terbaru = $ajax_req = '';
    if ($patt == 'review') {
        $pattern = ' AND LOWER(LEFT(title, 6))=\'review\'';
    } else {
        $pattern = ' AND LOWER(LEFT(title, 7))=\'preview\'';
    }
    $q = "SELECT idnews, schedule, title, synopsis, image, image_headline, url, category FROM dbnews WHERE category IN ($all_category_str) AND level != '0' AND level !='3' AND schedule<NOW() $pattern ORDER BY schedule DESC LIMIT 20";
    $r = $sqlDB->sql_query($q, true) or die(__LINE__ . ' = ' . mysql_error());
    $total = $sqlDB->sql_numrows($r);

    $total_page = ceil($total / 6);
    $counter = 1;
    $page = '';
    while ($row = $sqlDB->sql_fetchrow($r)) {
        $hlimage = $library_dir . 'p/thumbnail/' . $row['image_headline']; //str_pad($row['idnews'], 10, '0', STR_PAD_LEFT) .'.jpg';
        //if (is_file($hlimage))
        if ($row['image_headline']) {
            #$img = $cdn_url . 'library/p/thumbnail/' . $row['image_headline']; //str_pad($row['idnews'], 10, '0', STR_PAD_LEFT) .'.jpg';
            $img = ((strlen($row['image_headline']) == 14) ? $thumbnail_media_url . $row['image_headline'] : $image_library_url . bola_image_news($row['image_headline'], '125/'));
        } else {
            continue;
            $img = str_replace('//p', '/p', $cdn_url . 'library/' . str_replace($library_url, '/', $row['image']));
        }
        $title_cencored = strlen($row['title']) > 55 ? substr($row['title'], 0, 53) . '..' : $row['title'];
        $synopsis_cencored = strlen($row['synopsis']) > 170 ? substr($row['synopsis'], 0, 168) . '..' : $row['synopsis'];

        $row['schedule'] = preg_replace('/(\d{4,4})\-(\d{2,2})\-(\d{2,2}) (\d{2,2})\:(\d{2,2})\:(\d{2,2})/', '\3-\2-\1 \4:\5', $row['schedule']);
        $hari = $day_list_ina[date('w', strtotime($row['schedule']))];

        $content_terbaru .= '
            <div class="bcontent2">
                <div class="indschedule">' . $hari . ', ' . $row['schedule'] . '</div>
                <span><a href="' . BOLAURL . $all_category[$row['category']] . '/' . $row['url'] . '.html">' . $title_cencored . '</a></span>
                <br class="clear"/>
                <div class="bcontent2_image">
                    <img class="lazy_loaded" src="'.$img_lazy_load.'" data-src="' . $img . '" width="130" height="84"/><br class="clear"/><div>&nbsp;</div></div>
                <div>' . balanceTags($row['synopsis'], true) . '</div>
                <br class="clear" />
            </div>
        ';
        $ajax_req .= '
            <li>
                <a href="' . BOLAURL . $all_category[$row['category']] . '/' . $row['url'] . '.html" target="_top" class="bluelink">
                    ' . $row['title'] . '
                </a>
            </li>
        ';

        //if ($counter == 5 && $memcache_key)
        //{
        //    write_file_direct(BOLADATA. $patt.'.txt', '<ul>'. $ajax_req .'</ul>');
        //}
        if ($counter % 6 == 0 || $counter == $total) {
            $paging = global_paging_10($page, $total_page, $patt);

            $content = '
                <div class="bigcon">
                    <div class="bigcon2">
                        <div class="nav"><a href="/" style="text-decoration:none;"  ' . ga_trackEvent('link_nav') . '>HOME</a> &raquo; ' . strtoupper($patt) . '</div>
                        ' . $menu_top . '
                        <br/>
                        <div class="greentitle">' . strtoupper($patt) . '</div>
                        <div style="font-size:12px;color:#999999">' . $content_terbaru . '</div>
                        <div class="clear"></div>
                        ' . $paging . '
                        <br />
                    </div>
                </div>
            ';
            $filename = $schedule_dir . $patt . $page . '.html';
            $fileurl = $schedule_url . $patt . $page . '.html';

            write_file($filename, $content, 'Review dan Preview Pertandingan, Skor Sepakbola', 'Review dan Preview Pertandingan, Skor Sepakbola Terkini', 'Review dan Preview Pertandingan, Skor Sepakbola Terkini', '', true, 'full', 5);
            echo generated_link($fileurl);

            $content_terbaru = '';
            $page++;
        }
        $counter++;
    }
    $sqlDB->sql_freeresult();
    $memcache->close();
}

/**
 * create printable news, used for all news
 *
 * @map url: http://www.bola.net/$cateogry/print/$page.html
 */
function create_print_document($filename, $id, $title, $news, $image)
{
    global $library_url, $cdn_url;

    $dir = dirname($filename) . '/print/';
    if (!is_dir($dir)) {
        mkdir($dir);
    }
    $fname = str_pad($id, 10, '0', STR_PAD_LEFT);
    $new = $dir . $fname . '.html';

    $news_image = '';
    if ($image != '') {
        $image = str_replace($library_url, '/', $image);
        $news_image = $library_url . str_replace('/p/', 'p/', $image);
        $news_image = '
            <div style="width:150px;float:right;padding:4px;border:1px solid #cccccc;background-color:#ececec;margin:0px 0px 10px 10px;text-align:center;font-size:11px;">
                <img src="' . $news_image . '" style="width:150px;margin-bottom:4px;" alt="" />
            </div>
        ';
    }

    $content = '
    <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
    <html>
    <head>
            <meta http-equiv="Content-type" content="text/html; charset=ISO-8859-1">
            <link rel="canonical" href="' . str_replace(BOLADIR, BOLAURL, $filename) . '" />
            <title>' . $title . '</title>
            <link rel="stylesheet" href="' . $cdn_url . 'library/bola_print.css">
            <link href="'.BOLAURL.'favicon.ico" rel="shortcut icon">
    </head>
        
    <body bgcolor="#FFFFFF" text="#000000" link="#0000FF" vlink="#C0C0C0" alink="#0000FF">
            <div align="center"> 
              <table class="main" border="0" cellpadding="0" cellspacing="0" width="600" bgcolor="#FFFFFF">
                <tr><td class="line" align="left" colspan="3"><img border="0" src="' . $cdn_url . 'library/logo.jpg" alt=""></td></tr>
                <tr> 
                  <td valign="top">
                  <table class="main" border="0" cellpadding="4" cellspacing="0" width="100%" bgcolor="#FFFFFF">
                      <tr>
                        <td valign="top" align="left">
                            <table width=100% cellpadding=4 cellspacing=0 border=0><tr><td align=left class=main><br/><span class="title"><b>' . $title . '</b></span><br /><br />
                            ' . $news_image . $news . '
                            </td></tr></table>
                        </td>
                      </tr>
                      </table>
                  </td>
                </tr>
              </table>
            </div>
            <p align="center"><b><font class="p" size="2" face="Arial" color="#FF0000">&#169;2013 Bola.net</font></b></p>
    </body>
    </html>
    ';

    write_file_direct($new, $content);
}

/**
 * get related:
 *      - player
 *      - club
 *      - player photo
 *      - wallpaper
 * display on below image
 * return empty if no related player
 */
function get_news_related_other($sqlDB, $arr_seleb = array(), $arr_team = array())
{
    global $profile_dir, $profile_url, $club_url, $wallpaper_url;

    $arr_seleb = array_unique(array_filter(array_map('trim', $arr_seleb)));
    $arr_team = array_unique(array_filter(array_map('trim', $arr_team)));
    if ((!is_array($arr_seleb) || count($arr_seleb) == 0) && (!is_array($arr_team) || count($arr_team) == 0)) {
        return '';
    }

    $total_content = 0;
    $content_photo = $impl = '';
    $content = '';
    if (count($arr_seleb) > 0) {
        $total_content++;
        $content = '';

        //$arr_seleb = array_map('kln_real_escape_string', $arr_seleb);
        $arr_seleb = array_map('addslashes', $arr_seleb);
        $impl = implode("','", $arr_seleb);
        $q = "SELECT player_name, player_url FROM `player_profile` WHERE player_name IN ('$impl') AND player_status = '1'";
        $r = $sqlDB->sql_query($q);
        while ($row = $sqlDB->sql_fetchrow($r)) {
            $content .= '<li><a href="' . $profile_url . $row['player_url'] . '" ' . ga_trackEvent('profil_pemain', $row['player_name']) . '>' . $row['player_name'] . '</a></li>';

            $photo_dir = $profile_dir . $row['player_url'] . '/foto/';
            if (is_file($photo_dir . 'index.html')) {
                $content_photo .= '<li><a href="' . $profile_url . $row['player_url'] . '/foto">' . $row['player_name'] . '</a></li>';
            }
        }
        $sqlDB->sql_freeresult($r);
        if ($content) {
            $content = '
                <div class="list_rel_item">
                    <span>PROFIL TERKAIT</span>
                    <ul>
                        ' . $content . '
                    </ul>
                </div>
            ';
        }
    }

    if (count($arr_team) > 0) {
        $total_content++;

        $content = '';
        $impl2 = implode("','", $arr_team);
        $q = "SELECT team_id, team_name FROM dbteam WHERE team_name IN ('$impl2') AND team_status='1'";
        $r = $sqlDB->sql_query($q);
        while ($row = $sqlDB->sql_fetchrow($r)) {
            $q2 = "SELECT url FROM dbarticles WHERE team_id='$row[team_id]' AND category='16' LIMIT 1";
            $r2 = $sqlDB->sql_query($q2);
            if ($row2 = $sqlDB->sql_fetchrow($r2)) {
                $content .= '<li><a href="' . $club_url . $row2['url'] . '.html" ' . ga_trackEvent('profil_klub', $row['team_name']) . '>' . $row['team_name'] . '</a></li>';
            }
            $sqlDB->sql_freeresult($r2);
        }
        $sqlDB->sql_freeresult($r);
        if ($content) {
            $content = '
                <div class="list_rel_item">
                    <span>TIM TERKAIT</span>
                    <ul>
                        ' . $content . '
                    </ul>
                </div>
            ';
        }
    }

    if ($total_content >= 2) {
        $content .= '<div class="clear" style="height:10px;"></div>';
        $total_content = 0;
    }

    if ($content_photo) {
        $total_content++;
        $content .= '
            <div class="list_rel_item">
                <span>GALERI FOTO</span>
                <ul>' . $content_photo . '</ul>
            </div>
        ';
    }

    if ($total_content >= 2) {
        $content .= '<div class="clear" style="height:10px;"></div>';
        $total_content = 0;
    }

    if ($impl) {
        $q = "SELECT title, url FROM dbwallpaper WHERE celebrity IN ('$impl') AND level<>'0' ORDER BY schedule DESC LIMIT 2";
        $r = $sqlDB->sql_query($q);
        $num = $sqlDB->sql_numrows($r);

        if ($num > 0) {
            $content .= '
                <div class="list_rel_item">
                    <span>WALLPAPER</span>
                    <ul>
            ';
            while ($row = $sqlDB->sql_fetchrow($r)) {
                $content .= '<li><a href="' . $wallpaper_url . $row['url'] . '.html">' . $row['title'] . '</a></li>';
            }
            $sqlDB->sql_freeresult($r);
            $content .= '
                    </ul>
                </div>
            ';
        }
    }

    if ($content) {
        $content = '<div class="list_related">' . $content . '<div class="clear"></div></div>';
    }

    return $content;
}


/**
 * generate index category (liga inggris, liga italia, liga spanyol, liga indonesia, liga champions)
 * old view moved as arsip with name index1.html, index2.html, ...
 *
 * @map url: www.bola.net/inggris/$index.html
 */
function generate_index_category($sqlDB, $catid, $LIMIT_PAGE = 3, $devel = 0)
{
    global  $cdn_url, $headline_media, $headline_media_url, $thumbnail_media, $thumbnail_media_url, $library_url, $BALLBALL_CAT_CODES, $img_lazy_load, $image_library_url;
    
    if (!in_array($catid, array('1', '2', '3', '4', '5'))) {
        return false;
    }
    
    $stitle = '';
    $sleague = '';
    $sclublink = '';
    $active_menu = 0;
    
    $metatitle = '';
    $metakey = '';
    $metadesc = '';
    $hdsub = '';
    $props_insight = '';
    switch ($catid) {
        case '1':
            $stitle = 'Liga Inggris';
            $sleague = 'inggris';
            $sclublink = '';
            $active_menu = 1;
            $props_insight = 'Bola Eropa/Liga Inggris';
            $metatitle = 'Liga Inggris : Jadwal, Hasil, Klasemen, Berita';
            $metakey = 'Sepak Bola, Liga Inggris, Piala FA, Piala Carling, Piala Liga Inggris, Community Shield, Wayne Rooney, Steven Gerrard, Frank Lampard, John Terry, Cristiano Ronaldo, Cesc Fabregas, David Beckham, Fernando Torres, Didier Drogba, Foto Pemain, Foto Pertandingan, Profil Klub, Preview Pertandingan, Review Pertandingan, Manchester United, Liverpool, Chelsea, Arsenal, Manchester City, Tottenham Hotspur, Everton, West Ham, Newcastle, Fulham, Prediksi, Jadwal Pertandingan, Hasil Pertandingan, Football, Soccer.';
            $metadesc = 'Jadwal, hasil pertandingan, klasemen Liga Inggris, top skor dan berita sepakbola terkini dan terlengkap.';
            $hdsub = '<h2 class="hsubd">Jadwal, Hasil Pertandingan, Klasemen, Berita dan Foto</h2>';
            break;
        
        case '2':
            $stitle = 'Liga Seri A';
            $sleague = 'italia';
            $sclublink = 'italia.html';
            $active_menu = 1;
            $props_insight = 'Bola Eropa/Liga Italia';
            $metatitle = 'Liga Italia Seri A : Jadwal, Hasil, Klasemen, Berita';
            $metakey = 'Sepak Bola, Liga Italia, Coppa Italia, Super Coppa Italia, Ronaldinho, Alexandre Pato, Diego Milito, Zlatan Ibrahimovic, Kaka, Francesco Totti, Alessandro Del Piero, Mourinho, David Beckham, Foto Pemain, Foto Pertandingan, Profil Klub, Preview Pertandingan, Review Pertandingan, AC Milan, Inter Milan, Juventus, AS Roma, Fiorentina, Lazio, Sampdoria, Bologna, Napoli, Parma, Udinese, Prediksi, Jadwal Pertandingan, Hasil Pertandingan, Football, Soccer.';
            $metadesc = 'Jadwal, hasil pertandingan, klasemen Liga Italia, top skor dan berita sepakbola terkini dan terlengkap.';
            $hdsub = '<h2 class="hsubd">Jadwal, Hasil Pertandingan, Klasemen, Berita dan Foto</h2>';
            break;
        
        case '3':
            $stitle = 'La Liga';
            $sleague = 'spanyol';
            $sclublink = 'spanyol.html';
            $active_menu = 1;
            $props_insight = 'Bola Eropa/Liga Spanyol';
            $metatitle = 'Liga Spanyol : Jadwal, Hasil, Klasemen, Berita';
            $metakey = 'Sepak Bola, Liga Spanyol, Piala Raja Spanyol, Cristiano Ronaldo, Kaka, Lionel Messi, David Villa, Iniesta, Xavi, Xabi Alonso, Sergio Ramos, Puyol, Iker Casillas, Mourinho, Fabiano, Diego Forlan, Zlatan Ibrahimovic, Foto Pemain, Foto Pertandingan, Profil Klub, Preview Pertandingan, Review Pertandingan, Real Madrid, Barcelona, Valencia, Atletico Madrid, Sevilla, Prediksi, Jadwal Pertandingan, Hasil Pertandingan, Football, Soccer.';
            $metadesc = 'Jadwal, hasil pertandingan, klasemen Liga Spanyol, top skor dan berita sepakbola terkini dan terlengkap.';
            $hdsub = '<h2 class="hsubd">Jadwal, Hasil Pertandingan, Klasemen, Berita dan Foto</h2>';
            break;
        
        case '4':
            $stitle = 'Liga Super Indonesia';
            $sleague = 'indonesia';
            $sclublink = 'indonesia.html';
            $active_menu = 3;
            $props_insight = 'Bola Indonesia/Liga Indonesia';
            $metatitle = 'Liga Indonesia : Jadwal, Hasil, Klasemen, Berita';
            $metadesc = 'Jadwal, hasil pertandingan, klasemen Liga Indonesia, top skor dan berita sepakbola terkini dan terlengkap.';
            $hdsub = '<h2 class="hsubd">Jadwal, Hasil Pertandingan, Klasemen, Berita dan Foto</h2>';
            break;
        
        case '5':
            $stitle = 'Liga Champions';
            $sleague = 'champions';
            $sclublink = '';
            $active_menu = 1;
            $props_insight = 'Bola Eropa/Liga Champions';
            $metatitle = 'Liga Champions : Jadwal, Hasil, Klasemen, Berita';
            $metakey = 'Sepak Bola, Liga Champions, Cristiano Ronaldo, Wayne Rooney, Lionel Messi, Diego Milito, Kaka, Iniesta, David Villa, Xavi, Xabi Alonso, Sergio Ramos, Mourinho, Zlatan Ibrahimovic, David Beckham, Pato, Steven Gerrard, Frank Lampard, John Terry, Cesc Fabregas, Didier Drogba, Preview Pertandingan, Review Pertandingan, Manchester United, Arsenal, Chelsea, Barcelona, Real Madrid, Inter Milan, AS Roma, AC Milan, Bayern Munchen, Valencia, Schalke 04, Marseille. Lyon, Twente, Marseille, Prediksi, Jadwal Pertandingan, Hasil Pertandingan, Football, Soccer.';
            $metadesc = 'Jadwal, hasil pertandingan, klasemen Liga Champions, top skor dan berita sepakbola terkini dan terlengkap.';
            $hdsub = '<h2 class="hsubd">Jadwal, Hasil Pertandingan, Klasemen, Berita dan Foto</h2>';
            break;
         
        default:
            $stitle = '';
            $sleague = '';
    }
    
    $all_category = array();
    $all_category_name = array();

    $q = "SELECT * FROM dbcategory WHERE category_level='1' AND category_status='1' ORDER BY category_id";
    $rc = $sqlDB->sql_query($q);
    while ($row = $sqlDB->sql_fetchrow($rc)) {
        $all_category[$row['category_id']] = $row['category_url'];
        $all_category_name[$row['category_id']] = $row['category_name'];
    }
    $sqlDB->sql_freeresult();

    $rightcontent_html = '<div class="rightbox_news" name="">'.generate_news_rightcontent($sqlDB, $catid).'</div>';
    //$rightcontent_ajax = '<div id="rightbox_news_'.$catid.'" class="rightbox_news" name="news_'.$catid.'"></div>';
    $rightcontent_ajax = $rightcontent_html;
    
    $PERPAGE = 30;
    if ($devel == 0) {
        $filename = BOLADIR . $all_category[$catid] . '/index.html';
        $filename_url = BOLAURL . $all_category[$catid] . '/index.html';
    } else {
        $filename = APPSDIR . 'devel/generate/www/index_liga/' . $all_category[$catid] . '/index.html';
        $filename_url = APPSURL .  'devel/generate/www/index_liga/' . $all_category[$catid] . '/index.html';
    }
    

    $q = "SELECT 
        idnews, schedule, title, synopsis, image, image_headline, url, category, category_name, source, celebrity, celebrity1, celebrity2, celebrity3, celebrity4, celebrity5, subtitle, tag_subtitle 
        FROM dbnews, dbcategory 
        WHERE dbnews.category=dbcategory.category_id AND category='$catid' AND level != '0' AND level !='3' AND schedule<NOW() 
        ORDER BY schedule DESC LIMIT 1";
    $r = $sqlDB->sql_query($q, true) or die(__LINE__ . ' = ' . mysql_error());
    $row = $sqlDB->sql_fetchrow($r);
    $title_cencored = $row['title'];

    $first_title = $row['title'] . " - " . $row['category_name'];
    $first_desc = $row['synopsis'] . " " . $row['category_name'];
    $first_keywords = implode(", ", explode(" ", $row['synopsis'] . " " . $row['category_name']));

    #$img = $headline_media_url . $row['image_headline'];
    $img = ((strlen($row['image_headline']) == 14) ? $headline_media_url . $row['image_headline'] : $image_library_url . $row['image_headline']);
    if (strtolower($row['source']) == 'persib.co.id') {
        $img = persibnews_image_url($row['image_headline']);
    }
    
    $player_mark = strtoupper(get_news_player_mark($row));
    $schedule_mark = get_news_schedule_mark($row['schedule']);
    
    /* subtitle and tag subtitle */
    $subtitle = isset($row['subtitle']) ? $row['subtitle'] : '';
    $tag_subtitle = ($row['tag_subtitle'] != 0) ? $row['tag_subtitle'] : '';
    $url_tag_subtitle = isset($tag_subtitle) ? BOLAURL.'tag/'.getURLTag($sqlDB, $tag_subtitle, '1') : '#';
    $htmlsub = '';
    if ($subtitle) {
        $htmlsub .= '<p class="subtitle"><a href="'.$url_tag_subtitle.'" ' . ga_trackEvent('subtitle', $subtitle) . '>'.$subtitle.'</a></p>';
    }
    /* eof subtitle and tag subtitle */
        
    $headline_content = '
        <div class="item_hl">
            <a href="' . BOLAURL . $all_category[$row['category']] . '/' . $row['url'] . '.html" class="img">
                <img class="lazy_loaded" src="'.$img_lazy_load.'" data-src="' . $img . '" width="478" width="478" alt="' . $title_cencored . '" />
            </a>
            
            <h2 class="ntitle">
                '.$htmlsub.'
                <a href="' . BOLAURL . $all_category[$row['category']] . '/' . $row['url'] . '.html" >' . $title_cencored . '</a>
            </h2>
            <p class="syn">' . balanceTags($row['synopsis'], true) . '</p>
            <div class="info">
                '.($player_mark?('<span class="link">'.$player_mark.'</span>'):'').'
                <span class="date">'.$schedule_mark.'</span>
                <a href="' . BOLAURL . $all_category[$row['category']] . '/' . $row['url'] . '.html" target="_blank" class="linkarrow">&nbsp;</a>
            </div>
        </div>
    ';
    $sqlDB->sql_freeresult();

    $not_in_hl = ' AND idnews<>' . $row['idnews'];

    $content = '
        <div class="newslist">
            <div class="nav">
                <a ' . ga_trackEvent('link_nav') . ' href="/" style="text-decoration:none;">HOME</a> &raquo;
                <a ' . ga_trackEvent('link_nav') . ' href="/' . $all_category[$catid] . '/" style="text-decoration:none;">'.  strtoupper($all_category_name[$catid]).'</a>
            </div>
            <h1 class="hd">'.  strtoupper($all_category_name[$catid]).'</h1>
            '.$hdsub.'
            '.$headline_content;
    
    /* ballball video widget 20141219*/
    /*$ballball_widget = '';
    $ballball_code = isset($BALLBALL_CAT_CODES[$catid])?$BALLBALL_CAT_CODES[$catid]:'';
    if($ballball_code)
    {
        if(date('w') == 1)
        {
            $ballball_code = isset($ballball_code['gotw'])?$ballball_code['gotw']:'';
        }
        else
        {
            $ballball_code = isset($ballball_code['player'])?$ballball_code['player']:'';
        }
    }
    if($ballball_code)
    {
        $ballball_widget = '
        <div class="box1">
            <div class="boxheader_index" style="text-align:left;padding:5px;">
                <div class="index-n-b2">
                    <a href="" ' . ga_trackEvent('ballball_index') . ' style="text-decoration:none;">
                        <span class="first">VIDEO</span> <span class="last">BOLA</span>
                    </a>
                </div>
                <div class="clear"></div>
            </div>
            <div class="boxcontent1" style="padding:0px;">
                <script src="http://www.ballball.com/static/js/enhanced_player/1.1/enhanced_player_host.js"></script>
                <script id="enhanced-player">
                     ballball_player_generate("enhanced-player", "in-id","'.$ballball_code.'","www.ballball.com");
                </script>
            </div>
        </div>
        <br />
        ';
    }*/
    
    $counter = 1;
    $page = 0;
    
    $q_paging = "SELECT COUNT(idnews) as total FROM dbnews WHERE category='$catid' AND level != '0' AND level !='3' AND schedule<NOW()";
    $r_paging = $sqlDB->sql_query($q_paging, true) or die(__LINE__ . ' = ' . mysql_error());
    $row_paging = $sqlDB->sql_fetchrow($r_paging);
    $total = $row_paging['total'];
    $paging = newslist_paging_5(0, ceil($total / $PERPAGE), BOLAURL . $all_category[$catid] . '/index');
    
    $q = "SELECT 
            idnews, schedule, title, synopsis, image, image_headline, url, source, category, celebrity, celebrity1, celebrity2, celebrity3, celebrity4, celebrity5, subtitle, tag_subtitle
        FROM dbnews 
        WHERE category='$catid' AND level != '0' AND level !='3' AND schedule<NOW() $not_in_hl 
        ORDER BY schedule DESC";
    $r = $sqlDB->sql_query($q, true) or die(__LINE__ . ' = ' . mysql_error());
    while ($row = $sqlDB->sql_fetchrow($r)) {
        #$img = $thumbnail_media_url . $row['image_headline'];
        $img = ((strlen($row['image_headline']) == 14) ? $thumbnail_media_url . $row['image_headline'] : $image_library_url . bola_image_news($row['image_headline'], '125/'));
        if (strtolower($row['source']) == 'persib.co.id') {
            $img = persibnews_image_url($row['image_headline'], '175');
        }
        
        if (!$row['image_headline'] && $row['image'] != '') {
            $row['image'] = str_replace($library_url, '/', $row['image']);
            $img = $cdn_url . 'library/' . str_replace('/p/', 'p/', $row['image']);
        }
        
        $title_cencored = $row['title']; //strlen($row['title']) > 55 ? substr($row['title'], 0, 53) . '..' : $row['title'];
        $synopsis_cencored = strlen($row['synopsis']) > 170 ? substr($row['synopsis'], 0, 168) . '..' : $row['synopsis'];

        $player_mark = strtoupper(get_news_player_mark($row));
        $schedule_mark = get_news_schedule_mark($row['schedule']);
    
        //advertorial mark
        $advertorial_mark = '';
        $qtags = "
                                SELECT A.tags_id, A.tags_name, A.tags_url
                                FROM `dbtags` A INNER JOIN dbtags_assosiation B ON A.tags_id=B.tags_id
                                WHERE A.tags_level='1' AND A.tags_id = '9275' AND B.idnews='{$row['idnews']}'";
        if ($rtags = $sqlDB->sql_query($qtags, true)) {
            while ($rowtags = $sqlDB->sql_fetchrow($rtags)) {
                $advertorial_mark = '&nbsp;&nbsp;<span class="advertorial_indexnews_mark">Advertorial</span> &nbsp;';
            }
        }
        /* subtitle and tag subtitle */
        $subtitle = isset($row['subtitle']) ? $row['subtitle'] : '';
        $tag_subtitle = ($row['tag_subtitle'] != 0) ? $row['tag_subtitle'] : '';
        $url_tag_subtitle = isset($tag_subtitle) ? BOLAURL.'tag/'.getURLTag($sqlDB, $tag_subtitle, '1') : '#';
        $htmlsub_item = '';
        if ($subtitle) {
            $htmlsub_item .= '<p class="subtitle"><a href="'.$url_tag_subtitle.'" ' . ga_trackEvent('subtitle', $subtitle) . '>'.$subtitle.'</a></p>';
        }
        /* eof subtitle and tag subtitle */
        $content .= '            
            <div class="item">
                <a href="' . BOLAURL . $all_category[$row['category']] . '/' . $row['url'] . '.html" class="img">
                    <img class="lazy_loaded" src="'.$img_lazy_load.'" data-src="' . $img . '" width="130" height="79" alt="' . $title_cencored . '"/>
                </a>
                <div class="text">  
                    '.$htmlsub_item.'                  
                    <a href="' . BOLAURL . $all_category[$row['category']] . '/' . $row['url'] . '.html" class="ntitle">'.$advertorial_mark. $title_cencored . '</a>
                    <div class="info">
                        '.($player_mark?('<span class="link">'.$player_mark.'</span>'):'').'                        
                        <span class="date">'.$schedule_mark.'</span>
                        <a href="' . BOLAURL . $all_category[$row['category']] . '/' . $row['url'] . '.html" target="_blank" class="linkarrow">&nbsp;</a>
                    </div>   
                </div>
                <br class="clear" />
            </div>
        ';
        
        /*if($counter == ($PERPAGE/2))
        {
            $content .= $ballball_widget;
        }*/
        
        $counter++;
        if ($counter == $PERPAGE) {
            $content .= '
                    '.$paging.'
                </div>
            ';

            if ($page > 0) {
                if ($devel == 0) {
                    $filename = BOLADIR . $all_category[$catid] . '/index'.$page.'.html';
                    $filename_url = BOLAURL . $all_category[$catid] . '/index'.$page.'.html';
                } else {
                    $filename = APPSDIR . 'devel/generate/www/index_liga/' . $all_category[$catid] . '/index'.$page.'.html';
                    $filename_url = APPSURL . 'devel/generate/www/index_liga/' . $all_category[$catid] . '/index'.$page.'.html';
                }
                
                //$metatitle .= "Halaman $page";
            }
            
            if ($devel == 0) {
                write_file($filename, $content, $metatitle, $metakey, $metadesc, '', true, 'full', $active_menu);
                insert_property_og($filename, $metatitle, $filename_url, '', '109215469105623', $metadesc, $props_insight);
            } else {
                write_file_dev($filename, $content, $metatitle, $metakey, $metadesc, '', true, 'full', $active_menu);
                insert_property_og($filename, $metatitle, $filename_url, '', '109215469105623', $metadesc, $props_insight);
            }
            
            
            $rightcontent = $rightcontent_ajax;
            if ($page == 0) {
                set_top_tagbar($sqlDB, $filename);
                $rightcontent = $rightcontent_html;
            }

            //replace right content
            $rightcontent = str_replace('http://www.bola.net/', BOLAURL, $rightcontent);
            $temp = file_get_contents($filename);
            $temp = bola_block_replace('<!-- RIGHTCONTENT -->', '<!-- ENDRIGHTCONTENT -->', $temp, $rightcontent);
            $temp = file_put_contents($filename, $temp);
            echo generated_link($filename_url);
            
            $counter = 1;
            $page++;
            $paging = newslist_paging_5($page, ceil($total / $PERPAGE), BOLAURL . $all_category[$catid] . '/index');
            $content = '
                <div class="newslist">
                    <div class="nav">
                        <a ' . ga_trackEvent('link_nav') . ' href="/" style="text-decoration:none;">HOME</a> &raquo;
                        <a ' . ga_trackEvent('link_nav') . ' href="/' . $all_category[$catid] . '/" style="text-decoration:none;">'.  strtoupper($all_category_name[$catid]).'</a>
                    </div>
                    <h1 class="hd">'.  strtoupper($all_category_name[$catid]).'</h1>';
        }
            
        if ($LIMIT_PAGE && $page >= $LIMIT_PAGE) {
            break;
        }
    }
    $sqlDB->sql_freeresult();
    
    $content .= '
            '.$paging.'
        </div>
    ';

    if ($page > 0) {
        if ($devel == 0) {
            $filename = BOLADIR . $all_category[$catid] . '/index'.$page.'.html';
            $filename_url = BOLAURL . $all_category[$catid] . '/index'.$page.'.html';
        } else {
            $filename = APPSDIR . 'devel/generate/www/index_liga/' . $all_category[$catid] . '/index'.$page.'.html';
            $filename_url = APPSURL . 'devel/generate/www/index_liga/' . $all_category[$catid] . '/index'.$page.'.html';
        }
        
        //$metatitle .= "Halaman $page";
    }
    
    if ($devel == 0) {
        write_file($filename, $content, $metatitle, $metakey, $metadesc, '', true, 'full', $active_menu);
        insert_property_og($filename, $metatitle, $filename_url, '', '109215469105623', $metadesc, $props_insight);
        
        $rightcontent = $rightcontent_ajax;
        if ($page == 0) {
            set_top_tagbar($sqlDB, $filename);
            $rightcontent = $rightcontent_html;
        }
    } else {
        write_file_dev($filename, $content, $metatitle, $metakey, $metadesc, '', true, 'full', $active_menu);
        insert_property_og($filename, $metatitle, $filename_url, '', '109215469105623', $metadesc, $props_insight);
        
        $rightcontent = $rightcontent_ajax;
        if ($page == 0) {
            //set_top_tagbar($sqlDB, $filename);
            $rightcontent = $rightcontent_html;
        }
    }
    

    //replace right content
    $rightcontent = str_replace('http://www.bola.net/', BOLAURL, $rightcontent);
    $temp = file_get_contents($filename);
    $temp = bola_block_replace('<!-- RIGHTCONTENT -->', '<!-- ENDRIGHTCONTENT -->', $temp, $rightcontent);
    $temp = file_put_contents($filename, $temp);

    echo generated_link($filename_url);
}




function generate_news_rightcontent($sqlDB, $catid, $devel = 0)
{
    global  $cdn_url, $galeri_url,  $galeri_media, $galeri_media_url, $schedule_url, $schedule_dir, $profile_url, $profile_dir, $club_url;
    
    if (!in_array($catid, array('1', '2', '3', '4', '5'))) {
        return false;
    }
    
    $stitle = '';
    $sleague = '';
    $sclublink = '';
    $arr_top_team = array();
    $arr_top_team_name = array();
    switch ($catid) {
        case '1':
            $where = "AND country='Inggris' AND title='Liga Premier'";
            $stitle = 'Liga Inggris';
            $sleague = 'inggris';
            $sclublink = '';
            
            $arr_top_team[1] = '3,4,9,10,20';
            $arr_top_team_name[1] = array('Manchester United','Chelsea','Liverpool','Arsenal','Manchester City');
            break;
        
        case '2':
            #$where = "AND country='Italia' AND title='Liga Serie A'";
            $where = "AND country='Italia' AND title='Liga Seri A'";
            $stitle = 'Liga Seri A';
            $sleague = 'italia';
            $sclublink = 'italia.html';
            
            $arr_top_team[2] = '26,28,2,1,72';
            $arr_top_team_name[2] = array('AC Milan','Inter Milan','Juventus','AS Roma','Napoli');
            break;
        
        case '3':
            $where = "AND country='Spanyol' AND title='La Liga'";
            $stitle = 'La Liga';
            $sleague = 'spanyol';
            $sclublink = 'spanyol.html';
            
            $arr_top_team[3] = '6,7,8,44,46';
            $arr_top_team_name[3] = array('Barcelona','Real Madrid','Atletico Madrid','Valencia','Sevilla');
            break;
        
        case '4':
            $where = "AND idseason = '51' AND country='Indonesia'";
            $stitle = 'Liga Super Indonesia';
            $sleague = 'indonesia';
            $sclublink = 'indonesia.html';
            
            $arr_top_team[4] = '83,100,103,105,108,109,112,130,149';
            break;
        
        case '5':
            $where = "AND idseason = '56' AND title='Liga Champions'";
            $stitle = 'Liga Champions';
            $sleague = 'champions';
            $sclublink = '';
            break;
         
        default:
            $where = "";
            $stitle = '';
            $sleague = '';
    }
    
    /* RIGHT CONTENT */
    $memcache = new Memcache;
    bola_memcached_connect($memcache);
    
    // berita foto
    $berita_foto = '';
    $q = "SELECT * FROM dbgallery WHERE level>'0' AND schedule<NOW() AND catvalue='$catid' ORDER BY schedule DESC LIMIT 5";
    $r = $sqlDB->sql_query($q, true) or die(__LINE__ . ' = ' . mysql_error());
    $num = $sqlDB->sql_numrows($r);
    $counter = 1;
    while ($row = $sqlDB->sql_fetchrow($r)) {
        $photocat = $row['category'];
        $photocat = trim($photocat);
        $catdir = str_replace(' ', '_', strtolower($photocat));
        $title = strip_tags($row['title']);

        if (file_exists($galeri_media . $catdir .'/t/index.jpg')) {
            //generate thumbnail preview
            $q1 = "SELECT filename, player_id, club_id FROM dbphoto WHERE idcat = '{$row['idcat']}'";
            $r1 = $sqlDB->sql_query($q1);
            $total_thumb = $sqlDB->sql_numrows($r1);
            $ti = 1;
            $thumbnail_preview = '';
            while ($r = $sqlDB->sql_fetchrow($r1)) {
                if (is_file($galeri_media . $catdir .'/t/'.$r['filename'])) {
                    $thumbnail_preview .= '
                    <li>
                        <a href="'. $galeri_url.$catdir .'.html" ' . ga_trackEvent('berita_foto', stripslashes($title)) . '>
                            <img src="'.$galeri_media_url . $catdir .'/t/'.$r['filename'].'" alt="'. stripslashes($title) .'"/>
                        </a>
                    </li>';
                    $ti++;
                }
                if ($ti>4) {
                    break;
                }
            }
            if ($ti < 4) {
                continue;
            }
            if ($thumbnail_preview) {
                $thumbnail_preview = '<ul class="thumb_list">'.$thumbnail_preview.'</ul>';
            }

            $image_index = $galeri_media_url. $catdir .'/t/index.jpg';
            $berita_foto = '                
                <div class="rightbf_item">
                    <a href="'. $galeri_url.$catdir .'.html" class="img" ' . ga_trackEvent('berita_foto', stripslashes($title)) . '>
                        <img src="'. $image_index .'" alt="'. stripslashes($title) .'"/>
                    </a>
                    <a href="'. $galeri_url.$catdir .'.html" class="item_title" ' . ga_trackEvent('berita_foto', stripslashes($title)) . '>'. stripslashes($title) .'</a>
                    <span class="date">'.date('F d, Y', strtotime($row["schedule"])).'</span>
                    '.$thumbnail_preview.'
                    <a href="'. $galeri_url.$catdir .'.html" class="link_detail" ' . ga_trackEvent('berita_foto', stripslashes($title)) . '>Lihat '.$total_thumb.' foto dalam album ini &raquo;</a>
                </div>
            ';
            break;
        } else {
            continue;
        }
    }
    $sqlDB->sql_freeresult();
    if ($berita_foto) {
        $berita_foto = '<div class="rightbox rightbf">
                            <a href="'.$galeri_url.'" class="boxhd">BERITA FOTO</a>
                            '.$berita_foto.'
                        </div>';
    }
   
    //skor dan jadwal
    $schedule_score = '';
    
    // skor
     
    $_score_index_url_ = $schedule_url . 'score_' . (strtolower(str_replace(' ', '_', $stitle))) . '.html';
    if (strtolower($stitle) == 'liga seri a') {
        $_score_index_url_ = $schedule_url . 'score_' . (strtolower(str_replace(' ', '_', str_replace('Liga ', '', $stitle)))) . '.html';
    } elseif ($stitle == 'Liga Inggris') {
        $_score_index_url_ = $schedule_url . 'score_' . (strtolower(str_replace(array(' ', 'Inggris'), array('_', 'premier'), $stitle))) . '.html';
    } elseif ($stitle == 'Liga Super Indonesia') {
        $_score_index_url_ = $schedule_url . 'score_indonesia.html';
    }
   
    
    
    $skor_terkini = '';
    $score_url = $schedule_url . 'hasil_pertandingan/';
    $q = "SELECT * FROM `dbschedule` WHERE schedule<=NOW() AND level='2' $where ORDER BY schedule DESC LIMIT 5";
    
    $r = $sqlDB->sql_query($q, true) or die(__LINE__ . ' = ' . mysql_error());
    while ($row = $sqlDB->sql_fetchrow($r)) {
        $_score_detail_url_ = getURL($row['title'] . '-' . $row['home'] . $row['home2'] . '-vs-' . $row['away'] . $row['away2'] . '-' . date('Y-m-d', strtotime($row['schedule'])));
        $_score_detail_url_ = $score_url . $_score_detail_url_ . '.html';
        
        $home_club = $row['home'] . $row['home2'];
        $away_club = $row['away'] . $row['away2'];
        
        $default_logo = $cdn_url . 'library/i/v2/club-logo-default-32.png';
        $home_logo = $away_logo = $default_logo;
        
        $home_link = $club_url;
        $q4 = "SELECT A.team_logo, B.url FROM `dbteam` A LEFT JOIN dbarticles B ON A.team_id=B.team_id AND B.category='16' WHERE A.team_name='$home_club' AND A.team_status = '1' LIMIT 1";
        $r4 = $sqlDB->sql_query($q4, true) or die('something happen');
        if ($row4 = $sqlDB->sql_fetchrow($r4)) {
            $row4['url'] = trim($row4['url']);
            if ($row4['url']) {
                $home_link = $club_url . $row4['url'] . '.html';
            }
            if ($row4['team_logo']) {
                $home_logo = imagelib_url($row4['team_logo'], 'i');
            }
        }
        $sqlDB->sql_freeresult($r4);
        
        $away_link = $club_url;
        $q4 = "SELECT A.team_logo, B.url FROM `dbteam` A LEFT JOIN dbarticles B ON A.team_id=B.team_id AND B.category='16' WHERE A.team_name='$away_club' AND A.team_status = '1' LIMIT 1";
        $r4 = $sqlDB->sql_query($q4, true) or die('something happen');
        if ($row4 = $sqlDB->sql_fetchrow($r4)) {
            $row4['url'] = trim($row4['url']);
            if ($row4['url']) {
                $away_link = $club_url . $row4['url'] . '.html';
            }
            if ($row4['team_logo']) {
                $away_logo = imagelib_url($row4['team_logo'], 'i');
            }
        }
        $sqlDB->sql_freeresult($r4);
        
        $skor_terkini .= '
            <li class="jadwalskor_item">
                <div class="comp">'.  strtoupper($stitle).' | '.date('d-m-Y', strtotime($row['schedule'])).'</div>
                <div class="teams">
                    <a href="'.$_score_detail_url_.'" class="team_name" ' . ga_trackEvent('skor_terkini', $stitle) . '>
                        <span class="logo"><img src="'.$home_logo.'" alt="'.$home_club.'"/></span>
                        <span class="text">'.$home_club.'</span>
                    </a>
                    <a href="'.$_score_detail_url_.'" class="team_name" ' . ga_trackEvent('skor_terkini', $stitle) . '>
                        <span class="logo"><img src="'.$away_logo.'" alt="'.$away_club.'"/></span>
                        <span class="text">'.$away_club.'</span>
                    </a>
                </div>
                <div class="info">
                    <a href="'.$_score_detail_url_.'" class="skor" ' . ga_trackEvent('skor_terkini', $stitle) . '>
                        <span>' . $row['goal_home'] . '</span>
                        <span>' . $row['goal_away'] . '</span>
                    </a>
                    <span class="ft">FT</span>
                </div>
                <br class="clear"/>
            </li>
    ';
    }
    $sqlDB->sql_freeresult($r);
    
    //jadwal
    $jadwal_terkini = '';
    $sql_topteam = isset($arr_top_team_name[$catid])?$arr_top_team_name[$catid]:'';
    if ($sql_topteam) {
        $sql_topteam = " AND (home IN ('" . implode("','", $sql_topteam) . "') OR away IN ('" . implode("','", $sql_topteam) . "')) ";
    }
    $q = "SELECT * FROM `dbschedule` WHERE schedule>=NOW() AND level='1' $where $sql_topteam ORDER BY schedule ASC LIMIT 5";
    $r = $sqlDB->sql_query($q, true) or die(__LINE__ . ' = ' . mysql_error());
    $tr = $sqlDB->sql_numrows($r);
    if ($tr < 5) {
        $q = "SELECT * FROM `dbschedule` WHERE schedule>=NOW() AND level='1' $where ORDER BY schedule ASC LIMIT 5";
        $r = $sqlDB->sql_query($q, true) or die(__LINE__ . ' = ' . mysql_error());
    }
    
    while ($row = $sqlDB->sql_fetchrow($r)) {
        $preview_text = '-';
        $preview_url = $schedule_url;
        if ($row['news_preview']) {
            $sql = "SELECT A.url, B.category_name, B.category_url
            FROM dbnews A, dbcategory B
            WHERE A.idnews = '{$row['news_preview']}' AND A.schedule <= NOW() AND A.level != '0' AND A.level !='3' AND A.category = B.category_id
            LIMIT 1";
            $resprev = $sqlDB->sql_query($sql, true) or die(__LINE__ . ' = ' . mysql_error());
            while ($rowprev = $sqlDB->sql_fetchrow($resprev)) {
                $preview_url = BOLAURL . $rowprev['category_url'] . '/' . $rowprev['url'] . '.html';
                $preview_text = 'PREVIEW';
            }
        }
        
        $home_club = $row['home'] . $row['home2'];
        $away_club = $row['away'] . $row['away2'];
        
        $default_logo = $cdn_url . 'library/i/v2/club-logo-default-32.png';
        $home_logo = $away_logo = $default_logo;
        
        $home_link = $club_url;
        $q4 = "SELECT A.team_logo, B.url FROM `dbteam` A LEFT JOIN dbarticles B ON A.team_id=B.team_id AND B.category='16' WHERE A.team_name='$home_club' AND A.team_status = '1' LIMIT 1";
        $r4 = $sqlDB->sql_query($q4, true) or die('something happen');
        if ($row4 = $sqlDB->sql_fetchrow($r4)) {
            $row4['url'] = trim($row4['url']);
            if ($row4['url']) {
                $home_link = $club_url . $row4['url'] . '.html';
            }
            if ($row4['team_logo']) {
                $home_logo = imagelib_url($row4['team_logo'], 'i');
            }
        }
        $sqlDB->sql_freeresult($r4);
        
        $away_link = $club_url;
        $q4 = "SELECT A.team_logo, B.url FROM `dbteam` A LEFT JOIN dbarticles B ON A.team_id=B.team_id AND B.category='16' WHERE A.team_name='$away_club' AND A.team_status = '1' LIMIT 1";
        $r4 = $sqlDB->sql_query($q4, true) or die('something happen');
        if ($row4 = $sqlDB->sql_fetchrow($r4)) {
            $row4['url'] = trim($row4['url']);
            if ($row4['url']) {
                $away_link = $club_url . $row4['url'] . '.html';
            }
            if ($row4['team_logo']) {
                $away_logo = imagelib_url($row4['team_logo'], 'i');
            }
        }
        $sqlDB->sql_freeresult($r4);
        
        $jadwal_terkini .= '
            <li class="jadwalskor_item">
                <div class="comp">'.  strtoupper($stitle).' | '.date('d-m-Y', strtotime($row['schedule'])).'</div>
                <div class="teams">
                    <a href="'.$preview_url.'" class="team_name" ' . ga_trackEvent('skor_terkini', $stitle) . '>
                        <span class="logo"><img src="'.$home_logo.'" alt="'.$home_club.'"/></span>
                        <span class="text">'.$home_club.'</span>
                    </a>
                    <a href="'.$preview_url.'" class="team_name" ' . ga_trackEvent('skor_terkini', $stitle) . '>
                        <span class="logo"><img src="'.$away_logo.'" alt="'.$away_club.'"/></span>
                        <span class="text">'.$away_club.'</span>
                    </a>
                </div>
                <div class="info">
                    <a href="'.$preview_url.'" class="skor matchinfo" ' . ga_trackEvent('skor_terkini', $stitle) . '>
                        <span>'.date('H:i', strtotime($row['schedule'])).' WIB</span>
                        <span>'.$preview_text.'</span>
                    </a>
                </div>
                <br class="clear"/>
            </li>
    ';
    }
    $sqlDB->sql_freeresult($r);
    
    $schedule_score = '
        <div class="rightbox rightjadwalskor">
            <a href="'.$schedule_url.'" class="boxhd" ' . ga_trackEvent('skor_terkini') . '>SKOR DAN JADWAL</a>
            <div class="boxwrap">
                <div class="boxopt">
                    <a href="'.$schedule_url.'score.html" class="active" name="rightjadwalskor_jadwal">SKOR</a>
                    <a href="'.$schedule_url.'" name="rightjadwalskor_skor">JADWAL</a>
                </div>
                <div class="boxlist">
                    <div id="rightjadwalskor_jadwal">
                        <ul>'.$skor_terkini.'</ul>
                        <a href="'.$_score_index_url_.'" class="link_detail" ' . ga_trackEvent('skor_terkini') . '>SKOR SELENGKAPNYA</a>
                    </div>
                    <div id="rightjadwalskor_skor">
                        <ul>'.$jadwal_terkini.'</ul>
                        <a href="'.$schedule_url.'" class="link_detail" ' . ga_trackEvent('skor_terkini') . '>JADWAL SELENGKAPNYA</a>
                    </div>
                </div>
            </div>
        </div>
    ';
    
    // klasemen
    $klasemen = '';
    $klasemen_url = $schedule_url . 'klasemen_liga_' . $sleague . '.html';
    $klasemen_data = $memcache->get("_bola_standing_data_" . $catid);
    
    if (!$klasemen_data) {
        $klasemen_file = TEMPDIR."klasemen/_bola_standing_data_" . $catid;
        if (is_file($klasemen_file)) {
            $klasemen_data = file_get_contents($klasemen_file);
        }
    }
    if ($klasemen_data) {
        if (is_string($klasemen_data)) {
            $klasemen_data = unserialize($klasemen_data);
        }
    }
    
    if ($klasemen_data && is_array($klasemen_data)) {
        if (in_array($catid, array(1,2,3))) {
            $css = 1;
            $count = 1;
            foreach ($klasemen_data as $item) {
                $_club_url_ = $club_url;
                if ($item['club_url']) {
                    $_club_url_ = $club_url.$item['club_url'].'.html';
                }
                $klasemen .= '
                    <div class="klasemen_row">
                        <div class="jdsmall' . $css . '"><span class="'.$item['css_rank'].'">' . $item['no'] . '</span></div>
                        <div class="jdwide' . $css . '"><span class="mark '.$item['mark'].'">&nbsp;</span><strong><a href="'.$_club_url_.'">'.trim($item['club']) . '</a></strong></div>
                        <div class="jdsmall' . $css . '">' . $item['played'] . '</div>
                        <div class="jdsmall' . $css . '"><strong>' . $item['point'] . '</strong></div>
                        <br class="clear" />
                    </div>
                ';
                $css = $css == 1 ? 2 : 1;
            }
        } elseif ($catid==4) {
            $klasemen =  '
                    <h1>Klasemen ISL 2015</h1>
                    <div class="klasemen_row">
                        <div class="jdsmall2"><strong>No</strong></div>
                        <div class="jdwide2"><strong>Klub</strong></div>
                        <div class="jdsmall2"><strong>Main</strong></div>
                        <div class="jdsmall2"><strong>Poin</strong></div>
                        <br class="clear" />
                    </div>
                ';
            $css = 1;
            foreach ($klasemen_data as $group => $item) {
                if ($item && is_array($item)) {
                    //foreach($klasemen_group as $item)
                    //{
                        $_club_url_ = $club_url;
                    if ($item['club_url']) {
                        $_club_url_ = $club_url.$item['club_url'].'.html';
                    }
                    $klasemen .= '
                            <div class="klasemen_row">
                                <div class="jdsmall' . $css . '">' . $item['no'] . '</div>
                                <div class="jdwide' . $css . '"><a href="'.$_club_url_.'">'.trim($item['club']) . '</a></div>
                                <div class="jdsmall' . $css . '">' . $item['played'] . '</div>
                                <div class="jdsmall' . $css . '">' . $item['point'] . '</div>
                                <br class="clear" />
                            </div>
                            ';
                    $css = $css == 1 ? 2 : 1;
                    //}
                }
            }
        } elseif ($catid == 5) {
            // klasemen liga champion agregat ( 16 besar )
            //$klasemen .= $memcache->get('_right_klasemen_champion_agregat_');
            /*if( empty($klasemen) ){
                echo 'set cache';
                $css = 1;
                foreach ($klasemen_data as $item)
                {
                    $sql = $sqlDB->sql_query('SELECT team_id, team_name, team_logo FROM dbteam WHERE team_name IN ("'.$item['data'][0]['home'].'", "'.$item['data'][0]['away'].'")');
                    $row = $sqlDB->sql_fetchrowset($sql);
                    foreach($row as $team){
                        $allteam[$team['team_name']] = $team;
                    }
                }
                ksort($allteam);
                foreach($allteam as $team){
                    $klasemen .= '
                            <div class="klasemen_row">
                                <div class="jdwide'.$css.'" style="width:281px;">
                                    <img src='.imagelib_url($team['team_logo'], 'i').' />
                                    <strong>'.$team['team_name'].'</strong>
                                </div>
                                <br class="clear">
                            </div>
                        ';
                    $css = $css == 1 ? 2 : 1;
                }
                $memcache->set('_right_klasemen_champion_agregat_', $klasemen, false ,172800);
            }*/
            // klasemen liga champion grup
            foreach ($klasemen_data as $group => $klasemen_group) {
                if ($group == range('A', 'H')) {
                    $klasemen .=  '
                    <h1>Klasemen Grup ' . strtoupper($group) . '</h1>
                    <div class="klasemen_row">
                        <div class="jdsmall1"><strong>No</strong></div>
                        <div class="jdwide1"><strong>Klub</strong></div>
                        <div class="jdsmall1"><strong>Main</strong></div>
                        <div class="jdsmall1"><strong>Poin</strong></div>
                        <br class="clear" />
                    </div>
                ';
                    if ($klasemen_group && is_array($klasemen_group)) {
                        $css = 1;
                        foreach ($klasemen_group as $item) {
                            $_club_url_ = $club_url;
                            if ($item['club_url']) {
                                $_club_url_ = $club_url.$item['club_url'].'.html';
                            }
                            $klasemen .= '
                            <div class="klasemen_row">
                                <div class="jdsmall' . $css . '">' . $item['no'] . '</div>
                                <div class="jdwide' . $css . '"><a href="'.$_club_url_.'">'.trim($item['club']) . '</a></div>
                                <div class="jdsmall' . $css . '">' . $item['played'] . '</div>
                                <div class="jdsmall' . $css . '">' . $item['point'] . '</div>
                                <br class="clear" />
                            </div>
                            ';
                            $css = $css == 1 ? 2 : 1;
                        }
                    }
                }
            }
        } else {
            $klasemen .= '';
        }
    }
    
    
    // top score
    $topscore = '';
    $topscore_url = $schedule_url . 'topskor_liga_' . $sleague . '.html';
    $topscore_data = $memcache->get("bolanet_topscore_data_" . $catid);
    
    if (!$topscore_data) {
        $topscore_file = TEMPDIR."topscore/bolanet_topscore_data_" . $catid;
        if (is_file($topscore_file)) {
            $topscore_data = file_get_contents($topscore_file);
        }
    }
    
    if ($topscore_data) {
        if (is_string($topscore_data)) {
            $topscore_data = unserialize($topscore_data);
        }
    }
    if ($topscore_data && is_array($topscore_data)) {
        $count = 1;
        foreach ($topscore_data as $item) {
            $class = '1';
            if ($count%2) {
                $class = '2';
            }
            $topscore .= '
                <div class="klasemen_row">
                        <div class="jdsmall'.$class.'"><span class="rank">'.$count.'</span></div>
                        <div class="jdwide'.$class.'"><strong><a href="'.$item['player_url'].'">'.$item['player'].'</a></strong></div>
                        <div class="jdsmall'.$class.'"><strong>'.$item['goal'].'</strong></div>
                        <br class="clear">
                    </div>
            ';
            $count++;
            if ($count > 20) {
                break;
            }
        }
    }
    
    $klasemen_topscore = '
        <div class="rightbox rightstanding">
            <a href="'.$klasemen_url.'" class="boxhd" ' . ga_trackEvent('klasemen_sementara') . '>KLASEMEN</a>
            <div class="boxwrap">
                <div class="boxopt">
                    <a href="'.$klasemen_url.'" class="active" name="rightstanding_klasemen">KLASEMEN</a>
                    <a href="'.$topscore_url.'" name="rightstanding_topskor">TOPSKOR</a>
                </div>                                

                <div class="standing_list '.$sleague.'" id="rightstanding_klasemen">
                    '.$klasemen.'
                    <a href="'.$klasemen_url.'" class="link_detail" ' . ga_trackEvent('klasemen_sementara') . '>DETAIL KLASEMEN</a>
                </div>
                <div class="topskor_list" id="rightstanding_topskor">
                    '.$topscore.'
                    <a href="'.$topscore_url.'" class="link_detail" ' . ga_trackEvent('top_skor') . '>DETAIL TOPSKOR</a>
                </div>
            </div>
        </div>
    ';
    
    // teams
    $team_list = '';
    $team_default_logo = $cdn_url . 'library/i/v2/club-logo-default-175.png';
    $clubindex_url = $club_url . $sclublink;
    
    $qs = "SELECT season_id FROM dbseason WHERE season_cat_id='$catid' AND season_status='1' ORDER BY season_id DESC LIMIT 1";
    $rs = $sqlDB->sql_query($qs, true) or die(__LINE__ . ' = ' . mysql_error());
    $rows = $sqlDB->sql_fetchrow($rs);

    $q = "SELECT A.team_id, A.team_name, A.team_logo FROM dbteam A, dbparticipant B WHERE A.team_status='1' AND part_season_id='$rows[season_id]' AND team_id=part_team_id ORDER BY team_name";
    $r = $sqlDB->sql_query($q, true) or die(__LINE__ . ' = ' . mysql_error());
    
    while ($row = $sqlDB->sql_fetchrow($r)) {
        $q2 = "SELECT url FROM dbarticles WHERE team_id='$row[team_id]' AND category='16' LIMIT 1";
        $r2 = $sqlDB->sql_query($q2);
        $row2 = $sqlDB->sql_fetchrow($r2);
        
        $team_url = $clubindex_url;
        if (isset($row2['url']) && $row2['url']) {
            $team_url = $club_url . $row2['url'] . '.html';
        }
        
        $team_logo = $team_default_logo;
        if ($row['team_logo']) {
            $team_logo = imagelib_url($row['team_logo'], '175');
        }
        
        $team_list .= '
            <li>
                <a href="'.$team_url.'">
                    <img src="'.$team_logo.'" alt="'.$row['team_name'].'" ' . ga_trackEvent('profil_klub', $row['team_name']) . '/>
                </a>
            </li>
    ';
    }
    $sqlDB->sql_freeresult($r);
    if ($team_list) {
        $team_list = '
            <div class="rightbox rightclublist">
                <a href="'.$clubindex_url.'" class="boxhd">BERITA KLUB</a>
                <ul>'.$team_list.'</ul>
            </div>
        ';
    }
    
    //bolatainement
    if ($devel == 0) {
        $bolatainement_list = unserialize($memcache->get('bola_bolatainment_rightbox'));
    } else {
        $bolatainement_list = unserialize($memcache->get('bola_bolatainment_rightbox_devel'));
    }
    
    $bolatainement = '
        <div class="rightbox rightbolatainment">
            <a href="" class="boxhd">BOLATAINMENT</a>
            <div class="box_wrap">'.$bolatainement_list.'</div>
        </div>
    ';
    
    $rightbox_content = '
        <script>
        $(function(){
            if($(".rightjadwalskor").length >0)
            {            
                $(".rightjadwalskor .boxopt a").click(function(e){
                    $(".rightjadwalskor .boxopt a").removeClass("active");
                    $(this).addClass("active");

                    var name = $(this).attr("name");
                    $("#rightjadwalskor_jadwal").hide();
                    $("#rightjadwalskor_skor").hide();
                    $("#"+name).show();

                    e.preventDefault();
                });
            }

            if($(".rightstanding").length >0)
            {
                $(".rightstanding .boxopt a").click(function(e){
                    $(".rightstanding .boxopt a").removeClass("active");
                    $(this).addClass("active");

                    var name = $(this).attr("name");
                    $("#rightstanding_klasemen").hide();
                    $("#rightstanding_topskor").hide();
                    $("#"+name).show();

                    e.preventDefault();
                });
            }
        });
        </script>
        '.$berita_foto.'
        '.$schedule_score.'
        '.$klasemen_topscore.'
        '.$team_list.'
        '.$bolatainement.'
    ';
    
    if ($devel == 0) {
        $memcache->set('bolanet_right_content_news_'.$catid, serialize($rightbox_content), false, MCACHE_TIME);
    } else {
        $memcache->set('bolanet_right_content_news_devel'.$catid, serialize($rightbox_content), false, MCACHE_TIME);
    }
    
    
    $memcache->close();
    
    return $rightbox_content;
}

function get_news_player_mark($row)
{
    $player = isset($row['celebrity'])?trim($row['celebrity']):'';
    if (!$player) {
        for ($i=1;$i<=5;$i++) {
            $player = isset($row['celebrity'.$i])?trim($row['celebrity'.$i]):'';
            if ($player) {
                break;
            }
        }
    }
    return $player;
}

function get_news_schedule_mark($schedule = '')
{
    global $day_list_ina;
    
    $start_date = new DateTime($schedule);
    $date_diff = $start_date->diff(new DateTime(date('Y-m-d H:i:s')));
    
    if ($date_diff->d >= 7) {
        $schedule = preg_replace('/(\d{4,4})\-(\d{2,2})\-(\d{2,2}) (\d{2,2})\:(\d{2,2})\:(\d{2,2})/', '\3-\2-\1 \4:\5', $schedule);
        $hari = $day_list_ina[date('w', strtotime($schedule))];
        return $hari . ', ' . $schedule;
    } else {
        $mark = '';
        if ($date_diff->d) {
            $mark = 'Sekitar ' . $date_diff->d.' Hari ';
        } elseif ($date_diff->h) {
            $mark = 'Sekitar ' . $date_diff->h.' Jam ';
        } elseif ($date_diff->i) {
            if ($date_diff->i < 30) {
                $date_diff->i = 15;
            }
            if ($date_diff->i > 30) {
                $date_diff->i = 30;
            }
            
            $mark = 'Sekitar ' . $date_diff->i.' Menit ';
        }
        if (!$mark) {
            $mark = 'Sekitar 1 Menit ';
        }
        return $mark.'yang lalu';
    }
}

/**
 * Generate news arsip, all category
 *
 * @map url: www.bola.net/arsip/$category/$year/$month/$day
 */
function generate_news_arsip($sqlDB, $gendate = '')
{
    global $arsip_dir, $arsip_url, $month_list_ina, $day_list_ina;

    if ($gendate == "") {
        $gendate = date('Y-m-d');
    }

    $dir = getAllNewsCat($sqlDB);
    #$dir =  array(7 => array(0 => "Piala Dunia", 1 => "piala_dunia" ));
    
    $filter_date = '';
    if ($gendate != '-1') {
        $filter_date = " AND DATE(schedule)='$gendate'";
    }
    
    // inisiasi memcache
    $memcache = new Memcache;
    bola_memcached_connect($memcache);

    foreach ($dir as $i => $dir_v) {
        // hack seagames
        if ($i != '32') {
            //continue;
        }


        $arsip_title = "<a href=\"" . $arsip_url . $dir[$i][1] . "/\" style=\"color:#64982A;\">LIHAT BERITA ";
        $arsip_title_end = "</a>";

        $caturl = BOLAURL . $dir[$i][1] . '/';
        $q = "SELECT DATE(schedule) AS d FROM `dbnews` WHERE DATE(schedule)<>'0000-00-00' AND category='$i' $filter_date GROUP BY DATE(schedule) ORDER BY d DESC";
        
        $r = $sqlDB->sql_query($q, true) or die(__LINE__ . ' = ' . mysql_error());

        if ($sqlDB->sql_numrows() > 0) {
            while ($row2 = $sqlDB->sql_fetchrow($r)) {
                $new_dir = $arsip_dir . $dir[$i][1] . '/';
                if (!is_dir($new_dir)) {
                    mkdir($new_dir);
                }

                $select_date = $row2['d'];
                list($sy, $sm, $sd) = explode('-', $select_date);
                $new_dir .= $sy . '/';
                if (!is_dir($new_dir)) {
                    mkdir($new_dir);
                }

                $new_dir .= $sm . '/';
                if (!is_dir($new_dir)) {
                    mkdir($new_dir);
                }

                $new_dir .= $sd . '/';
                if (!is_dir($new_dir)) {
                    mkdir($new_dir);
                }
                
                $new_url = $arsip_url . $dir[$i][1] . '/' . $sy . '/' . $sm . '/' . $sd . '/';
                $month_indo = $month_list_ina[intval($sm) - 1];
                $content = '
                    <div class="bigcon">
                        <div class="bigcon2">
                            <div class="nav">
                                <a href="/" style="text-decoration:none;"  ' . ga_trackEvent('link_nav') . '>HOME</a> &raquo;
                                <a href="' . $arsip_url . '" style="text-decoration:none;"  ' . ga_trackEvent('link_nav') . '>ARSIP</a> &raquo;
                                <a href="' . $arsip_url . $dir[$i][1] . '/"  ' . ga_trackEvent('link_nav') . '>' . strtoupper($dir[$i][0]) . '</a> &raquo;
                                <a href="' . $arsip_url . $dir[$i][1] . '/' . $sy . '/"  ' . ga_trackEvent('link_nav') . '>' . $sy . '</a> &raquo;
                                <a href="' . $arsip_url . $dir[$i][1] . '/' . $sy . '/' . $sm . '/"  ' . ga_trackEvent('link_nav') . '>' . $sm . '</a> &raquo;
                                <a href="' . $new_url . '"  ' . ga_trackEvent('link_nav') . '>' . $sd . '</a>
                            </div>
                            
                            <br />
                            ' . get_arsip_top_menu($sy, $sm, $sd, $dir, $dir[$i][1]) . '
                            <div class="subtitlenews" style="color:#64982A;">
                                ' . $arsip_title . strtoupper($dir[$i][0]) . $arsip_title_end . '
                            </div>
                            <br />
                ';

                $sql = "
                    SELECT title, url, schedule, synopsis, category
                    FROM dbnews
                    WHERE level != '0' AND level !='3' AND category=" . $i . " AND schedule <> '00-00-0000 00:00:00' AND schedule <= NOW() AND DATE(schedule)='$select_date'
                    ORDER BY schedule DESC";
                $res = $sqlDB->sql_query($sql, true) or die(__LINE__ . ' = ' . mysql_error());
                $num = $sqlDB->sql_numrows($res);

                $pagenum = floor($num / 30);
                $counter = 1;
                $page = 0;
                $paging = global_paging_10($page, $pagenum, 'index'); //$dir[$i][1]
                while ($row = $sqlDB->sql_fetchrow($res)) {
                    list($date, $time) = explode(" ", $row['schedule']);
                    list($year, $month, $day) = explode("-", $date);
                    list($hour, $minute, $second) = explode(":", $time);
                    $datetime = $hour . ':' . $minute;
                    $hari = $day_list_ina[date('w', strtotime($row['schedule']))];

                    $content .= '
                        <div style="margin-bottom:6px;">
                            <div>
                                <a href="/' . $dir[$row['category']][1] . '/" class="greenlink">' . $dir[$row['category']][0] . '</a>
                            </div>
                            <div>' . $hari . ', ' . $day . '-' . $month . '-' . $year . ' ' . $datetime . '</div>
                            <a href="' . $caturl . $row['url'] . '.html" class="bluelink" style="font-size:14px"><b>' . $row['title'] . '</b></a>
                            <div>' . balanceTags($row['synopsis'], true) . '</div>
                        </div>
                    ';
                    $counter++;
                    if ($counter > 40) {
                        $content .= '
                                <br />
                                <center>' . $paging . '</center>
                            </div>
                            </div>
                        ';
                        $filename = $new_dir . 'index.html';
                        $fileurl = $new_url . 'index.html';
                        if ($page > 0) {
                            $filename = $new_dir . 'index' . $page . '.html';
                            $fileurl = $new_url . 'index' . $page . '.html';
                        }

                        write_file($filename, $content, 'Arsip Berita ' . $dir[$i][0] . ' ' . $sd . ' ' . $month_indo . ' ' . $sy);
                        //echo generated_link($fileurl);

                        $counter = 1;
                        $page++;
                        $paging = global_paging_10($page, $pagenum, 'index'); //$dir[$i][1]
                        $content = '
                            <div class="bigcon">
                                <div class="bigcon2">
                                    <div class="nav">
                                        <a href="/" style="text-decoration:none;"  ' . ga_trackEvent('link_nav') . '>HOME</a> &raquo;
                                        <a href="' . $arsip_url . '" style="text-decoration:none;"  ' . ga_trackEvent('link_nav') . '>ARSIP</a> &raquo;
                                        <a href="' . $arsip_url . $dir[$i][1] . '/"  ' . ga_trackEvent('link_nav') . '>' . strtoupper($dir[$i][0]) . '</a> &raquo;
                                        <a href="' . $arsip_url . $dir[$i][1] . '/' . $sy . '/"  ' . ga_trackEvent('link_nav') . '>' . $sy . '</a> &raquo;
                                        <a href="' . $arsip_url . $dir[$i][1] . '/' . $sy . '/' . $sm . '/"  ' . ga_trackEvent('link_nav') . '>' . $sm . '</a> &raquo;
                                        <a href="' . $new_url . '"  ' . ga_trackEvent('link_nav') . '>' . $sd . '</a>
                                    </div>
                                    <br />
                                    ' . get_arsip_top_menu($sy, $sm, $sd, $dir, $dir[$i][1]) . '
                                    <div class="subtitlenews">ARSIP ' . strtoupper($dir[$i][0]) . ' ' . $sd . ' ' . strtoupper($month_indo) . ' ' . $sy . '</div>
                                    <br />
                        ';
                    }
                }
                $sqlDB->sql_freeresult($res);
                $content .= '
                            <br />
                            <center>' . $paging . '</center>
                        </div>
                    </div>
                ';

                $filename = $new_dir . 'index.html';
                $fileurl = $new_url . 'index.html';
                if ($page > 0) {
                    $filename = $new_dir . 'index' . $page . '.html';
                    $fileurl = $new_url . 'index' . $page . '.html';
                }

                write_file($filename, $content, 'Arsip Berita ' . $dir[$i][0] . ' ' . $sd . ' ' . $month_indo . ' ' . $sy);
                echo generated_link($fileurl);
            }
        } elseif ($sqlDB->sql_numrows() == 0) {
            list($sy, $sm, $sd) = explode('-', $gendate);
            $sd = str_pad($sd, 2, '0', STR_PAD_LEFT);

            $new_dir = $arsip_dir ;
            if (!is_dir($new_dir)) {
                mkdir($new_dir);
            }

            $new_dir .= $dir_v[1] . '/';
            if (!is_dir($new_dir)) {
                mkdir($new_dir);
            }

            $new_dir .= $sy . '/';
            if (!is_dir($new_dir)) {
                mkdir($new_dir);
            }

            $new_dir .= $sm . '/';
            if (!is_dir($new_dir)) {
                mkdir($new_dir);
            }

            $new_dir .= $sd . '/';
            if (!is_dir($new_dir)) {
                mkdir($new_dir);
            }
            
            $month_indo = $month_list_ina[intval($sm) - 1];
            $new_url = $arsip_url . $dir[$i][1] . '/' . $sy . '/' . $sm . '/' . $sd . '/';

            $qs = "SELECT YEAR(schedule) AS d FROM `dbnews` WHERE level != '0' AND level !='3' AND DATE(schedule)<>'0000-00-00' GROUP BY YEAR(schedule) ORDER BY d DESC";
            
            // cache of $qs
            $key = md5($qs);
            $data = $memcache->get($key);

            if (!$data) {
                $rs = $sqlDB->sql_query($qs, true) or die(__LINE__ . ' = ' . mysql_error());
                $data = $sqlDB->sql_fetchrowset($rs);
                $memcache->set($key, $data, false, 3600 * 24);
                $sqlDB->sql_freeresult($rs);
            }
            // end cache of $qs

            $content = '
                <div class="bigcon">
                    <div class="bigcon2">
                        <div class="nav">
                            <a href="/" style="text-decoration:none;" ' . ga_trackEvent('link_nav') . '>HOME</a> &raquo;
                            <a ' . ga_trackEvent('link_nav') . ' href="' . $arsip_url . '" style="text-decoration:none;">ARSIP</a> &raquo;
                            <a ' . ga_trackEvent('link_nav') . ' href="' . $arsip_url . $dir[$i][1] . '/">' . strtoupper($dir[$i][0]) . '</a>
                        </div>
                        <br />
                        ' . get_arsip_top_menu($sy, $sm, $sd, $dir, 'inggris') . '
                        <div class="subtitlenews">' . $arsip_title . strtoupper($dir[$i][0]) . $arsip_title_end . '</div><br />
            ';

            foreach ($data as $item) {
                $listmonth = '';
                for ($m = 1; $m <= 12; $m++) {
                    $padm = str_pad($m, 2, '0', STR_PAD_LEFT);
                    $qx2 = "SELECT MONTH(schedule) AS d
                            FROM `dbnews`
                            WHERE level != '0' AND level !='3' AND DATE(schedule)<>'0000-00-00' AND LEFT(schedule, 7)='" . $item["d"] . "-$padm'
                            LIMIT 1";

                    // cache of $qx2
                    $key = md5($qx2);
                    $data2 = $memcache->get($key);

                    if (!$data2) {
                        $rx2 = $sqlDB->sql_query($qx2, true) or die(__LINE__ . ' = ' . mysql_error());
                        $data2 = $sqlDB->sql_fetchrow($rx2);
                        $memcache->set($key, $data2, false, 3600 * 24);
                        $sqlDB->sql_freeresult($rx2);
                    }
                    // end cache of $qx2

                    if ($data2) {
                        $listmonth .= '<a href="' . $arsip_url . $dir[$i][1] . '/' . $item["d"] . '/' . $padm . '/01/" class="bluelink">' . substr($month_list_ina[$m - 1], 0, 3) . '</a> ';
                    } else {
                        $listmonth .= substr($month_list_ina[$m - 1], 0, 3) . ' ';
                    }
                }
                if ($item["d"] == "2006") {
                    $content .= '<div class="arsip1"><a href="#" class="greenlink">' . $item["d"] . '</a> : ' . $listmonth . '</div>';
                } else {
                    $content .= '
                    <div class="arsip1">
                        <a href="' . $arsip_url . $dir[$i][1] . '/' . $item["d"] . '/" class="greenlink">' . $item["d"] . '</a> :
                        ' . $listmonth . '
                    </div>';
                }
            }
            $content .= "</div></div>";

            $filename = $new_dir . 'index.html';
            $fileurl = $new_url . 'index.html';

            write_file($filename, $content, 'Arsip Berita ' . $dir[$i][0] . ' ' . $sd . ' ' . $month_indo . ' ' . $sy);
            echo generated_link($fileurl);
        }

        $sqlDB->sql_freeresult($r);
        //break;
    }
    
    $memcache->close();
}

/*
 * get arsip top menu
 */

function get_arsip_top_menu($y, $m, $d, $dir, $sel, $_css_ = '')
{
    global $month_list_ina;

    $m = intval($m);
    $d = intval($d);
    $opt_tgl = '';
    for ($i = 1; $i <= 31; $i++) {
        $opt_tgl .= '<option value="' . (str_pad($i, 2, '0', STR_PAD_LEFT)) . '" ' . ($d == $i ? 'selected="selected"' : '') . '>' . $i . '</option>';
    }

    $opt_bln = '';
    for ($i = 1; $i <= 12; $i++) {
        $opt_bln .= '<option value="' . (str_pad($i, 2, '0', STR_PAD_LEFT)) . '" ' . ($m == $i ? 'selected="selected"' : '') . '>' . $month_list_ina[$i - 1] . '</option>';
    }

    $opt_thn = '';
    $dnow = date('Y');
    for ($i = $dnow; $i >= 2006; $i--) {
        $opt_thn .= '<option value="' . $i . '" ' . ($y == $i ? 'selected="selected"' : '') . '>' . $i . '</option>';
    }

    $opt_cat = '';
    foreach ($dir as $i => $dir_v) {
        if ($dir[$i][0] != 'Commercial') {
            $opt_cat .= '<option value="' . $dir[$i][1] . '" ' . ($dir[$i][1] == $sel ? 'selected="selected"' : '') . '>' . $dir[$i][0] . '</option>';
        }
    }

    $string = '
        <div class="form" ' . $_css_ . '>
            <select id="sday">
                <option value="">Tanggal</option>
                ' . $opt_tgl . '
            </select>
            -
            <select id="smonth">
                <option value="">Bulan</option>
                ' . $opt_bln . '
            </select>
            -
            <select id="syear">
                <option value="">Tahun</option>
                ' . $opt_thn . '
            </select>
            -
            <select id="scategory">
                <option value="">Category</option>
                ' . $opt_cat . '
            </select>
            <input type="button" value=" Cari " name="sbutton" onclick="javascript:arsipgoto();"/>
        </div><br/>
    ';

    return $string;
}

/**
 * generate news arsip index
 *
 * @map url: www.bola.net/arsip/
 */
function generate_news_arsip_index($sqlDB)
{
    global $arsip_dir, $arsip_url, $month_list_ina;

    list($sy, $sm, $sd) = explode('-', date('Y-m-d'));
    $dir = getAllNewsCat($sqlDB);

    $content = '
        <div class="bigcon">
            <div class="bigcon2">
                <div class="nav">
                    <a ' . ga_trackEvent('link_nav') . ' href="/" style="text-decoration:none;">HOME</a> &raquo;
                    <a ' . ga_trackEvent('link_nav') . ' href="' . $arsip_url . '" style="text-decoration:none;">ARSIP</a>
                </div>
                <br />
                <center><h1><a href="' . $arsip_url . date('Y/m/d/') . '" class="bluelink">LIHAT ARSIP BERITA HARI INI</a></h1></center>
                <div class="inars-box">
                    <h1>CARI ARSIP BERITA</h1>
                    ' . get_arsip_top_menu($sy, $sm, $sd, $dir, 'inggris') . '
                    <div class="inars-box">
                        <h1 style="margin: 0px 0px 10px 0px;">ARSIP BERDASARKAN KATEGORI</h1>
                        <ul>
    ';

    $list_index = '
        <center><h1><a href="' . $arsip_url . date('Y/m/d/') . '" class="bluelink">LIHAT ARSIP BERITA HARI INI</a></h1></center>
        <div class="inars-box">
            <!-- CARIARSIPBERITA -->
            <h1>CARI ARSIP BERITA</h1>
            ' . get_arsip_top_menu($sy, $sm, $sd, $dir, 'inggris') . '
            <!-- ENDOFCARIARSIPBERITA -->
            
            <div class="inars-box">
            <!-- ARSIPKATEGORI -->
            <h1 style="margin: 0px 0px 10px 0px;">ARSIP BERDASARKAN KATEGORI</h1>
            <ul>
    ';

    $q = "SELECT YEAR(schedule) AS d FROM `dbnews`
        WHERE level != '0' AND level !='3' AND DATE(schedule)<>'0000-00-00'
        and YEAR(schedule) >= '2006'
        GROUP BY YEAR(schedule) ORDER BY d DESC";
    
    $r = $sqlDB->sql_query($q, true) or die(__LINE__ . ' = ' . mysql_error());
    $list_index_2 = '
        <div class="inars-box">
        <!-- ARSIPTANGGAL -->
        <h1 style="margin: 0px 0px 10px 0px;">ARSIP BERDASARKAN TANGGAL</h1>
    ';

    while ($row = $sqlDB->sql_fetchrow($r)) {
        $listmonth = '';
        for ($m = 1; $m <= 12; $m++) {
            $padm = str_pad($m, 2, '0', STR_PAD_LEFT);
            // $q2 = "SELECT MONTH(schedule) AS d FROM `dbnews`
            //     WHERE level != '0' AND level !='3' AND DATE(schedule)<>'0000-00-00' AND LEFT(schedule, 7)='" . $row['d'] . "-$padm' LIMIT 1";
            // $r2 = $sqlDB->sql_query($q2, true) or die(__LINE__ . ' = ' . mysql_error());
            // if ($row2 = $sqlDB->sql_fetchrow($r2)) {
                if ($row['d'] . $padm == '200607') {
                    $listmonth .= '<a href="' . $arsip_url . $row['d'] . '/' . $padm . '/18/" class="bluelink">' . substr($month_list_ina[$m - 1], 0, 3) . '</a> ';
                } elseif ($row['d'] . $padm == '200704') {
                    $listmonth .= '<a href="' . $arsip_url . $row['d'] . '/' . $padm . '/02/" class="bluelink">' . substr($month_list_ina[$m - 1], 0, 3) . '</a> ';
                } elseif ($row['d'] . $padm == '200806') {
                    $listmonth .= '<a href="' . $arsip_url . $row['d'] . '/' . $padm . '/02/" class="bluelink">' . substr($month_list_ina[$m - 1], 0, 3) . '</a> ';
                } elseif($row['d']==date('Y') && $m>date('m')) {
                    $listmonth .= substr($month_list_ina[$m - 1], 0, 3) . ' ';
                } elseif($row['d']=='2006' && $m<7) {
                    $listmonth .= substr($month_list_ina[$m - 1], 0, 3) . ' ';
                }else{
                    $listmonth .= '<a href="' . $arsip_url . $row['d'] . '/' . $padm . '/01/" class="bluelink">' . substr($month_list_ina[$m - 1], 0, 3) . '</a> ';
                }
            // } else {
            //     $listmonth .= substr($month_list_ina[$m - 1], 0, 3) . ' ';
            // }
            // $sqlDB->sql_freeresult($r2);
        }

        $list_index_2 .= $row['d'] . ' : ' . $listmonth . '<br/>';
    } 

    $list_index_2 .= '<!-- ENDOFARSIPTANGGAL --></div>';
    $sqlDB->sql_freeresult();
    
     $q = "SELECT DATE(schedule) AS d FROM `dbnews` WHERE DATE(schedule)<>'0000-00-00' AND level <> '0'    and YEAR(schedule) >= '2006' 
     GROUP BY DATE(schedule) ORDER BY d DESC";
        $r = $sqlDB->sql_query($q, true) or die(__LINE__ . ' = ' .'#'.$q);

    $data_row=array();
    while ($row = $sqlDB->sql_fetchrow($r)) {
        $data_row[]=$row;
    }


    foreach ($dir as $i => $dir_v) {
        $arsip_title = "<a href=\"" . $arsip_url . $dir[$i][1] . "\" style=\"color:#64982A;\">LIHAT BERITA ";
        $arsip_title_end = "</a>";

        $caturl = BOLAURL . $dir[$i][1] . '/';
       

        $contenty = '
            <div class="bigcon">
                <div class="bigcon2">
                    <div class="nav">
                        <a ' . ga_trackEvent('link_nav') . ' href="/" style="text-decoration:none;">HOME</a> &raquo;
                        <a ' . ga_trackEvent('link_nav') . ' href="' . $arsip_url . '" style="text-decoration:none;">ARSIP</a> &raquo;
                        <a ' . ga_trackEvent('link_nav') . ' href="' . $arsip_url . $dir[$i][1] . '/">' . strtoupper($dir[$i][0]) . '</a>
                    </div>
                    <br />
                    ' . get_arsip_top_menu($sy, $sm, $sd, $dir, 'inggris') . '
                    <div class="subtitlenews">' . $arsip_title . strtoupper($dir[$i][0]) . $arsip_title_end . '</div><br />
        ';
        $tmp_y = $tmp_m = $tmp_d = array();
        foreach ($data_row as $row) {

            list($my, $mm, $md) = explode('-', $row['d']);
            if (!isset($tmp_y[$my])) {
                if (!is_dir($arsip_dir . $dir[$i][1] . '/' . $my . '/')) {
                    mkdir($arsip_dir . $dir[$i][1] . '/' . $my . '/');
                }

                $listmonth = '';
                for ($m = 1; $m <= 12; $m++) {
                    $padm = str_pad($m, 2, '0', STR_PAD_LEFT);
                    // $qx2 = "SELECT MONTH(schedule) AS d FROM `dbnews` WHERE level<>'0' AND DATE(schedule)<>'0000-00-00' AND LEFT(schedule, 7)='$my-$padm' LIMIT 1";
                    // $rx2 = $sqlDB->sql_query($qx2, true) or die(__LINE__ . ' = ' . mysql_error()."#$qx2");
                    // if ($rowx2 = $sqlDB->sql_fetchrow($rx2)) {
                        // error pada link bulan pada halaman arsip
                        if ($my . $padm == '200607') {
                            $listmonth .= '<a href="' . $arsip_url . $my . '/' . $padm . '/18/" class="bluelink">' . substr($month_list_ina[$m - 1], 0, 3) . '</a> ';
                        } elseif ($my . $padm == '200704') {
                            $listmonth .= '<a href="' . $arsip_url . $my . '/' . $padm . '/02/" class="bluelink">' . substr($month_list_ina[$m - 1], 0, 3) . '</a> ';
                        } elseif ($my . $padm == '200806') {
                            $listmonth .= '<a href="' . $arsip_url . $my . '/' . $padm . '/02/" class="bluelink">' . substr($month_list_ina[$m - 1], 0, 3) . '</a> ';
                        } elseif($my==date('Y') && $m>date('m')) {
                            $listmonth .= substr($month_list_ina[$m - 1], 0, 3) . ' ';
                        } elseif($my=='2006' && $m<7) {
                            $listmonth .= substr($month_list_ina[$m - 1], 0, 3) . ' ';
                        }else{
                            $listmonth .= '<a href="' . $arsip_url . $dir[$i][1] . '/' . $my . '/' . $padm . '/01/" class="bluelink">' . substr($month_list_ina[$m - 1], 0, 3) . '</a> ';
                        }
                    // } else {
                    //     $listmonth .= substr($month_list_ina[$m - 1], 0, 3) . ' ';
                    // }
                   // $sqlDB->sql_freeresult($rx2);
                }

                // mengatasi link tahun yang broken
                if ($my == "2006") {
                    //$contenty .= '<div class="arsip1"><a href="' . $arsip_url . $dir[$i][1] . '/' . $my . '/07/18/" class="greenlink">' . $my . '</a> : ' . $listmonth . '</div>';
                    $contenty .= '<div class="arsip1"><a href="#" class="greenlink">' . $my . '</a> : ' . $listmonth . '</div>';
                } else {
                    $contenty .= '<div class="arsip1"><a href="' . $arsip_url . $dir[$i][1] . '/' . $my . '/" class="greenlink">' . $my . '</a> : ' . $listmonth . '</div>';
                }

                $tmp_y[$my] = 1;
                $contentm = '
                    <div class="bigcon">
                        <div class="bigcon2">
                            <div class="nav">
                                <a ' . ga_trackEvent('link_nav') . ' href="/" style="text-decoration:none;">HOME</a> &raquo;
                                <a ' . ga_trackEvent('link_nav') . ' href="' . $arsip_url . '" style="text-decoration:none;">ARSIP</a> &raquo;
                                <a ' . ga_trackEvent('link_nav') . ' href="' . $arsip_url . $dir[$i][1] . '/">' . strtoupper($dir[$i][0]) . '</a> &raquo;
                                <a ' . ga_trackEvent('link_nav') . ' href="' . $arsip_url . $dir[$i][1] . '/' . $my . '/">' . $my . '</a>
                            </div>
                            <br />
                            ' . get_arsip_top_menu($my, $mm, $md, $dir, 'inggris') . '
                            <div class="subtitlenews">' . $arsip_title . strtoupper($dir[$i][0]) . ' ' . $arsip_title_end . '</div><br />
                ';
            }

            if (!isset($tmp_m[$mm])) {
                if (!is_dir($arsip_dir . $dir[$i][1] . '/' . $my . '/' . $mm . '/')) {
                    mkdir($arsip_dir . $dir[$i][1] . '/' . $my . '/' . $mm . '/');
                }

                $month_indo = $month_list_ina[intval($mm) - 1];
                $contentm .= '<div class="arsip1"><a href="' . $arsip_url . $dir[$i][1] . '/' . $my . '/' . $mm . '/" class="greenlink">' . $month_indo . ' ' . $my . '</a></div>';
                $tmp_m[$mm] = 1;

                $contentd = '
                    <div class="bigcon">
                        <div class="bigcon2">
                            <div class="nav">
                                <a ' . ga_trackEvent('link_nav') . ' href="/" style="text-decoration:none;">HOME</a> &raquo;
                                <a ' . ga_trackEvent('link_nav') . ' href="' . $arsip_url . '" style="text-decoration:none;">ARSIP</a> &raquo;
                                <a ' . ga_trackEvent('link_nav') . ' href="' . $arsip_url . $dir[$i][1] . '/">' . strtoupper($dir[$i][0]) . '</a> &raquo;
                                <a ' . ga_trackEvent('link_nav') . ' href="' . $arsip_url . $dir[$i][1] . '/' . $my . '/">' . $my . '</a> &raquo;
                                <a ' . ga_trackEvent('link_nav') . ' href="' . $arsip_url . $dir[$i][1] . '/' . $my . '/' . $mm . '/">' . $mm . '</a>
                            </div>
                            <br />
                            ' . get_arsip_top_menu($my, $mm, $md, $dir, 'inggris') . '
                            <div class="subtitlenews">' . $arsip_title . strtoupper($dir[$i][0]) . ' ' . $arsip_title_end . '</div><br />
                ';
            }

            $contentd .= '<div class="arsip1"><a href="' . $arsip_url . $dir[$i][1] . '/' . $my . '/' . $mm . '/' . $md . '/" class="greenlink">' . $md . ' ' . $month_indo . ' ' . $my . '</a></div>';

            if ($mm == '01' && $md == '01') {
                $contentm .= '
                            <br/>
                        </div>
                    </div>
                ';

                $filenamem = $arsip_dir . $dir[$i][1] . '/' . $my . '/index.html';
                $fileurlm = $arsip_url . $dir[$i][1] . '/' . $my . '/';
                write_file($filenamem, $contentm, 'Arsip Berita ' . $dir[$i][0] . ' Tahun ' . $my);
                $tmp_m = array();
            }

            if ($md == '01') {
                $contentd .= '
                            <br/>
                        </div>
                    </div>
                ';

                $filenamed = $arsip_dir . $dir[$i][1] . '/' . $my . '/' . $mm . '/index.html';
                $fileurld = $arsip_url . $dir[$i][1] . '/' . $my . '/' . $mm . '/';
                write_file($filenamed, $contentd, 'Arsip Berita ' . $dir[$i][0] . ' Bulan ' . $month_indo . ' ' . $my);
                $tmp_m = array();
            }
        }

        $contenty .= '
                    <br/>
                </div>
            </div>
        ';

        if (!is_dir($arsip_dir . $dir[$i][1])) {
            mkdir($arsip_dir . $dir[$i][1]);
        }
        $filenamey = $arsip_dir . $dir[$i][1] . '/index.html';
        $fileurly = $arsip_url . $dir[$i][1] . '/';
        write_file($filenamey, $contenty, 'Arsip Berita ' . $dir[$i][0]);
        //echo '<a href="'.$fileurly.'" target="_blank">'.$fileurly.'</a>'."<br/>\n";

        $list_index .= '<li><a href="' . $fileurly . '" class="bluelink">' . $dir[$i][0] . '</a></li>';
        $content .= '<li><a href="' . $fileurly . '" class="bluelink">' . $dir[$i][0] . '</a></li>';

        $sqlDB->sql_freeresult();
    }

    // new
    $content .= '
                </ul>
            <div class="clear"></div>
        </div>
    ';
    $content .= $list_index_2;

    $content .= '
                </div>
                <br/>
            </div>
        </div>
    ';

    $list_index .= '
                </ul><!-- ENDOFARSIPKATEGORI -->
            <div class="clear"></div>
        </div>
    ';
    $list_index .= $list_index_2;

    write_file_direct(BOLADATA . 'arsip.html', $list_index);

    $filename = $arsip_dir . 'index.html';
    $fileurl = $arsip_url;
    write_file($filename, $content, 'Arsip Berita Paling Lengkap');
    echo generated_link($fileurl);
}

/**
 * get photonews related with news
 */
function news_get_photonews($sqlDB, $idnews='')
{
    global $galeri_url, $galeri_media_url;
    $query = "
        SELECT g.category,g.catvalue,p.photonews_pictures
        FROM dbphotonews p,dbgallery g
        WHERE  p.photonews_dbgallery_idcat = g.idcat AND p.photonews_dbnews_id = '$idnews' AND p.level = '1'";
    $result = $sqlDB->sql_query($query);
    $row = $sqlDB->sql_fetchrow($result);

    $arr_pic = $row['photonews_pictures'];
    $category = $row['category'];
    $catvalue = $row['catvalue'];

    if (is_array($row)) {
        $categori = BOLAURL . 'galeri/' . str_replace(" ", "_", strtolower($category)) . ".html";
        $div = "<div class='related-newsbf'>";
        $div .= '<a href=' . $categori . ' style="display:block;margin:5px 0 5px 25px;" ' . ga_trackEvent('galeri_foto', $category) . '><b>Lihat Foto ' . $category . '</b></a>';
        $arr_pic = explode(",", $arr_pic);
        if (is_array($arr_pic)) {
            foreach ($arr_pic as $img) {
                $img = str_replace($galeri_url, $galeri_media_url, $img);
                $divImg[] = '<div class="related-newsbf2"><a href="' . $categori . '"  ' . ga_trackEvent('galeri_foto', $category) . ' target="_blank"><img src="' . $img . '" alt=""></a></div>';
            }
            $div .= implode(" ", $divImg) . '<div class="clear"></div>';
        } else {
            $arr_pic = str_replace($galeri_url, $galeri_media_url, $arr_pic);
            $div .= '<div class="related-newsbf2"><a href="' . $categori . '"  ' . ga_trackEvent('galeri_foto', $category) . '><img src="' . $arr_pic . '" alt=""></a></div><div class="clear"></div>';
        }
        $sqlDB->sql_freeresult();
        $div .= "</div>";
        return $div;
    } else {
        return '';
    }
}

/**
 * Generate news arsip, all category, by date
 */
function generate_news_arsip_bydate($sqlDB, $gendate = '')
{
    global $arsip_dir, $arsip_url, $month_list_ina, $day_list_ina;

    if (!$gendate) {
        $gendate = date('Y-m-d');
        list($sy, $sm, $sd) = explode('-', $gendate);
    } else {
        list($sy, $sm, $sd) = explode('-', date('Y-m-d'));
    }

    $dir = getAllNewsCat($sqlDB);
    $filter_date = '';
    if ($gendate != '-1') {
        $filter_date = " AND DATE(schedule)='$gendate'";
    }

    $last = date('t', strtotime($gendate));
    $now = intval(date('d')) + 1;
    for ($i = $now; $i <= $last; $i++) {
        $sd = str_pad($i, 2, '0', STR_PAD_LEFT);
        $new_dir = $arsip_dir . $sy . '/';
        if (!is_dir($new_dir)) {
            mkdir($new_dir);
        }

        $new_dir .= $sm . '/';
        if (!is_dir($new_dir)) {
            mkdir($new_dir);
        }

        $new_dir .= $sd . '/';
        if (!is_dir($new_dir)) {
            mkdir($new_dir);
        }

        if (is_file($new_dir . 'index.html')) {
            continue;
        }

        $month_indo = $month_list_ina[intval($sm) - 1];
        $new_url = $arsip_url . $sy . '/' . $sm . '/' . $sd . '/';

        $paging = news_arsip_paging_date($gendate, $sd, '../');
        $content = '
        <div class="bigcon">
                <div class="bigcon2">
                    <div class="nav">
                        <a ' . ga_trackEvent('link_nav') . ' href="/" style="text-decoration:none;">HOME</a> &raquo;
                        <a ' . ga_trackEvent('link_nav') . ' href="' . $arsip_url . '" style="text-decoration:none;">ARSIP</a> &raquo;
                        ' . $sy . ' &raquo; ' . $sm . ' &raquo; <a ' . ga_trackEvent('link_nav') . ' href="' . $new_url . '">' . $sd . '</a>
                    </div>
                    <br />
                    ' . get_arsip_top_menu($sy, $sm, $sd, $dir, 'inggris') . '
                    <div class="subtitlenews">ARSIP ' . $sd . ' ' . strtoupper($month_indo) . ' ' . $sy . '</div><br /><br />
                    <div><center><b>Tidak Ada berita pada tanggal ' . $sd . ' ' . strtoupper($month_indo) . ' ' . $sy . '</b></center></div>
                    <br/><br/>
                    <center>' . $paging . '</center>
                    <br />
                </div>
        </div>
    ';
        $filename = $new_dir . 'index.html';
        $fileurl = $new_url . 'index.html';

        write_file($filename, $content, 'Arsip Berita ' . $sy . '-' . $month_indo . '-' . $sd);
        echo generated_link($fileurl);
    }

    $q = "SELECT DATE(schedule) AS d
        FROM `dbnews`
        WHERE level != '0' AND level !='3' AND DATE(schedule)<>'0000-00-00' $filter_date GROUP BY DATE(schedule) ORDER BY d DESC";
    $r = $sqlDB->sql_query($q, true) or die(__LINE__ . ' = ' . mysql_error());
    while ($row2 = $sqlDB->sql_fetchrow($r)) {
        $new_dir = $arsip_dir;
        if (!is_dir($new_dir)) {
            mkdir($new_dir);
        }

        $select_date = $row2['d'];
        list($sy, $sm, $sd) = explode('-', $select_date);
        $new_dir .= $sy . '/';
        if (!is_dir($new_dir)) {
            mkdir($new_dir);
        }

        $new_dir .= $sm . '/';
        if (!is_dir($new_dir)) {
            mkdir($new_dir);
        }

        $new_dir .= $sd . '/';
        if (!is_dir($new_dir)) {
            mkdir($new_dir);
        }

        $new_url = $arsip_url . $sy . '/' . $sm . '/' . $sd . '/';
        $month_indo = $month_list_ina[intval($sm) - 1];
        $paging = news_arsip_paging_date($select_date, $sd, '../');

        $content = '
        <div class="bigcon">
                <div class="bigcon2">
                    <div class="nav">
                        <a ' . ga_trackEvent('link_nav') . ' href="/" style="text-decoration:none;">HOME</a> &raquo;
                        <a ' . ga_trackEvent('link_nav') . ' href="' . $arsip_url . '" style="text-decoration:none;">ARSIP</a> &raquo;
                        ' . $sy . ' &raquo; ' . $sm . ' &raquo; <a ' . ga_trackEvent('link_nav') . ' href="' . $new_url . '">' . $sd . '</a>
                    </div>
                <br />
                ' . get_arsip_top_menu($sy, $sm, $sd, $dir, 'inggris') . '
                <div class="subtitlenews" style="color:#64982A;">ARSIP ' . $sd . ' ' . strtoupper($month_indo) . ' ' . $sy . '</div><br />
    ';

        $sql = "SELECT title,synopsis,url,schedule,category
            FROM dbnews
            WHERE level != '0' AND level !='3' AND category<>'' AND schedule <> '00-00-0000 00:00:00' AND schedule <= NOW() AND DATE(schedule)='$select_date'
            ORDER BY schedule DESC";
        $res = $sqlDB->sql_query($sql, true) or die(__LINE__ . ' = ' . mysql_error());
        $num = $sqlDB->sql_numrows($res);

        $pagenum = ceil($num / 30);
        $counter = 1;
        $page = 0;
        while ($row = $sqlDB->sql_fetchrow($res)) {
            list($date, $time) = explode(" ", $row['schedule']);
            list($year, $month, $day) = explode("-", $date);
            list($hour, $minute, $second) = explode(":", $time);
            $datetime = $hour . ':' . $minute;
            $caturl = BOLAURL . $dir[$row['category']][1] . '/';
            $i = $row['category'];
            $hari = $day_list_ina[date('w', strtotime($row['schedule']))];

            $content .= '
                <div style="margin-bottom:6px;">
                    <div><a href="/' . $dir[$row['category']][1] . '/" class="greenlink">' . $dir[$row['category']][0] . '</a></div>
                    <div>' . $hari . ', ' . $day . '-' . $month . '-' . $year . ' ' . $datetime . '</div>
                    <a href="' . $caturl . $row['url'] . '.html" class="bluelink" style="font-size:14px"><b>' . $row['title'] . '</b></a>
                </div>
        ';
            $counter++;
        }
        $sqlDB->sql_freeresult($res);
        $content .= '
                    <br/>
                    <center>' . $paging . '</center>
                    <br />
                </div>
        </div>
    ';
        $filename = $new_dir . 'index.html';
        $fileurl = $new_url . 'index.html';

        write_file($filename, $content, 'Arsip Berita ' . $sd . ' ' . $month_indo . ' ' . $sy);
        echo generated_link($fileurl);
    }
    $sqlDB->sql_freeresult($r);
}

/**
 * arsip pagination
 *
 */
function news_arsip_paging_date($date, $current, $url = '')
{
    $lasttime = strtotime($date);
    $last = date('t', $lasttime);
    $content = '';
    $current = intval($current);
    for ($i = 1; $i <= $last; $i++) {
        $pad = str_pad($i, 2, '0', STR_PAD_LEFT);
        if ($current == $i) {
            $content .= '<span class="paging_nolink">' . $pad . '</span>';
        } else {
            $content .= '<span class="paging"><a href="' . $url . $pad . '/">' . $pad . '</a></span>';
        }
        if ($i == 15) {
            $content .= '<br/>';
        }
    }
    return $content;
}

/**
 * inject fb social btn script to page
 */
function news_inject_socialfb($filename)
{
    if (is_file($filename)) {
        $content = @file_get_contents($filename);

        if ($content) {
            $fbjs = '';
            if (strpos($content, 'connect.facebook.net/en_US/all.js') === false) {
                //connect.facebook.net/en_US/all.js#xfbml=1&appId=318900031504252
                $fbjs = '
                    <div id="fb-root"></div>
                    <script>
                        (function(d, s, id) {
                          var js, fjs = d.getElementsByTagName(s)[0];
                          if (d.getElementById(id)) {return;}
                          js = d.createElement(s); js.id = id;
                          js.src = "//connect.facebook.net/en_US/sdk.js#version=v2.0&xfbml=1&appId=318900031504252";
                          fjs.parentNode.insertBefore(js, fjs);
                        }(document, \'script\', \'facebook-jssdk\'));
                    </script>
                ';
            }

            $replacement = '
                <style>
                .toolTip { /* This is the hook that the jQuery script will use */
                    padding-right: 20px; /* This is need to make sure that the help icon is always visible */
                    color: #3366FF;
                    position: relative; /* This contains the .toolTipWrapper div that is absolutely positioned  */
                }

                .toolTipWrapper { /* The wrapper holds its insides together */
                    width: 498px;
                    position: absolute; /* Absolute will make the tooltip float above other content in the page */
                    top: 20px;
                    display: none; /* It has to be displayed none so that the jQuery fadein and fadeout functions will work */
                    color: #FFF;
                    font-weight: bold;
                    font-size: 9pt; /* A font size is needed to maintain consistancy */
                    z-index: 11000;
                    -moz-border-radius: 5px;
                    border-radius: 5px;
                    border: 5px solid #93CA77;
                    background-color: #FFFFFF;
                }
    
                .toolTipTopWrapper { /* The wrapper holds its insides together */
                    width: 36px;
                    position: absolute; /* Absolute will make the tooltip float above other content in the page */
                    top: -8px;
                    height: 35px;
                    display: none; /* It has to be displayed none so that the jQuery fadein and fadeout functions will work */
                    z-index: 11001;
                    -moz-border-radius: 5px 5px 0px 0px;
                    border-radius: 5px 5px 0px 0px;
                    border: 5px solid #93CA77;
                    /*border-bottom: 5px solid #FFFFFF;*/
                }
        
                .toolTipTopWrapperShadow { /* The wrapper holds its insides together */
                    width: 36px;
                    position: absolute; /* Absolute will make the tooltip float above other content in the page */
                    top: -3px;
                    height: 35px;
                    display: none; /* It has to be displayed none so that the jQuery fadein and fadeout functions will work */
                    z-index: 11002;
                    border-bottom: 5px solid #FFFFFF;
                }

        .toolTipTop { /* Top section of the tooltip */
                    width: 550px;
                    height: 30px;
        }

        .toolTipMid { /* Middle section of the tooltip */
                    padding: 8px 15px;
                    background-color: #FFFFFF;
                    -moz-border-radius: 5px;
                    border-radius: 5px;
        }
        
        .toolTipBtm { /* Bottom Section of the tooltip */
            height: 13px;
        }

        .fb-social {border-bottom: 1px solid #CCCCCC; height: 70px; margin-bottom: 10px;}
        .fb-social .fb-social-left {float: left; width: 178px; color: #999999; font-size: 10px;}
        .fb-social .fb-social-left .fb-social-left-profile {float: left; padding: 10px 0px; position: relative;}
        .fb-social .fb-social-left .fb-social-left-profile img {float: left; margin: 0px 5px 3px 0px;}
        .fb-social .fb-social-left .fb-social-left-name {color: #000000; width: 165px; margin-top: -5px; font-size: 13px; padding-left: 40px; /*overflow: hidden;*/}
        .fb-social .fb-social-left .fb-social-left-name a {font-size: 11px; color: #2344A3;}
        .fb-social .fb-social-left .fb-social-left-name ul {padding: 0px; margin: 0px;}
        .fb-social .fb-social-left .fb-social-left-name ul li {float: left; list-style: none; position: relative;}
        
        .fb-social .fb-social-right {float: left; width: 492px;}
        .fb-social .fb-social-right-top {color: #999999; font-size: 10px; height: 24px;}
        /*.fb-social .fb-social-right-bottom {overflow: hidden;}*/
        .fb-social .fb-social-right-bottom ul {padding: 0px; width: 10000px; position: relative;}
        .fb-social .fb-social-right-bottom ul li {float: left; margin: 0px 3px 0px 0px; list-style: none;}
        .fb-social .fb-social-right-top .fb-social-right-top-right {float: right; color: #999999; font-size: 10px;}
        .fb-social .fb-social-right-top .fb-social-right-top-right span.bgfbnav {background-image: -webkit-gradient(linear,left top,left bottom,color-stop(0,white),color-stop(1,#EEE)); border: 1px solid #DDD; padding: 4px 7px;}

        .fb-social-no {color: #999999; font-size: 10px;}
        .fb-social-no-left {float: left; width: 40px; padding-top: 7px;}
        .fb-social-no-left2 {float: left; width: 450px; padding-top: 7px;}
        .fb-social-no-left2 span {font-size: 16px;}
        .fb-social-no-left3 {float: right; padding-top: 7px;}
        #fb-social-c {display: none;}
                </style>
            
                <script type="text/javascript" src="http://apps.bola.net/sreader/plus2.js?121"></script>
                ' . $fbjs . '
                <div class="fb-social">
                    <div class="fb-social-left">
                        SELAMAT DATANG DI BOLA.NET
                        <div class="fb-social-left-profile">
                            <center><img src="http://cdn.klimg.com//bola.net/library/i/v2/loading.gif" alt=""/></center>
                        </div>
                    </div>
                    
                    <div class="fb-social-right">
                        <div class="fb-social-right-top">
                            AKTIFITAS SAHABAT BOLA
                            <div class="fb-social-right-top-right">
                                <span id="fb-social-right-top-paging">0</span> dari <span id="fb-social-right-top-total">0</span>
                                <a href="#prev"><span class="bgfbnav"><</span></a>
                                <a href="#next"><span class="bgfbnav">></span></a>
                            </div>
                        </div>
                        
                        <div class="fb-social-right-bottom">
                            <center><img src="http://cdn.klimg.com//bola.net/library/i/v2/loading.gif" alt=""/></center>
                            <ul></ul>
                        </div>
                    </div>                    
                    <div class="clear"></div>
                </div>
                <div id="fb-social-c"></div>
        ';

            $content = str_replace('<div id="mainpageleft">', '<div id="mainpageleft">' . $replacement, $content);

            file_put_contents($filename, $content);
        }
    }
}

function generate_indexberitasepakbola($sqlDB, $LIMIT_PAGE = 0)
{
    global $library_url, $library_dir, $day_list_ina, $cdn_url, $headline_media, $headline_media_url, $thumbnail_media, $thumbnail_media_url, $image_library_url;

    $dir = getAllNewsCat($sqlDB);

    $content = '
        <div class="bigcon">
            <div class="bigcon2">
                <div class="nav">
                    <a ' . ga_trackEvent('link_nav') . ' href="/" style="text-decoration:none;">HOME</a> &raquo;
                    <a ' . ga_trackEvent('link_nav') . ' href="/berita-sepak-bola/" style="text-decoration:none;">BERITA SEPAK BOLA</a>
                </div>
                <br />
                <div class="subindexcat">BERITA SEPAK BOLA</div><br />
    ';

    $sql = "SELECT title, url, schedule, category, idnews, synopsis, image, image_headline, source
            FROM dbnews
            WHERE level != '0' AND level !='3' AND schedule <> '00-00-0000 00:00:00' AND schedule <= NOW()
        AND schedule>=DATE_SUB(NOW(), INTERVAL 1 YEAR)
            ORDER BY schedule DESC
        ";
    $res = $sqlDB->sql_query($sql, true) or die(__LINE__ . ' = ' . mysql_error());
    $num = $sqlDB->sql_numrows($res);

    $pagenum = ceil($num / 30);
    $counter = 1;
    $ischeck = true;
    $page = 0;
    $paging = global_paging_10($page, $pagenum, 'index');
    $index_page = 0;

    $_first_title = $_first_desc = $_first_keywords = '';

    while ($row = $sqlDB->sql_fetchrow($res)) {
        list($date, $time) = explode(" ", $row['schedule']);
        list($year, $month, $day) = explode("-", $date);
        list($hour, $minute, $second) = explode(":", $time);
        $datetime = $day . '-' . $month . '-' . $year . ' ' . $hour . ':' . $minute;

        if ($counter == 1) {
            $first_title = $row['title'] . ' - ' . $dir[$row['category']][0];
            $first_desc = $row['synopsis'] . ' - ' . $dir[$row['category']][0];
            $first_keywords = implode(", ", explode(" ", $row['synopsis'] . ' - ' . $dir[$row['category']][0]));
        }

        if ($index_page == 0) {
            $first_title = $_first_title;
            $first_desc = $_first_desc;
            $first_keywords = $_first_keywords;
            $index_page = 1;
        }
/*
        if ($row['category'] == 8)
        {
            $detailurl = EUROURL . 'berita/' . $row['url'] . '.html';
        }
        else
        {
            $detailurl = BOLAURL . $dir[$row['category']][1] . '/' . $row['url'] . '.html';
        }*/
        $detailurl = BOLAURL . $dir[$row['category']][1] . '/' . $row['url'] . '.html';
        
        //$hlimage = $thumbnail_media . $row['image_headline']; //str_pad($row['idnews'], 10, '0', STR_PAD_LEFT) .'.jpg';
        $hlimage = ((strlen($row['image_headline']) == 14) ? $thumbnail_media . $row['image_headline'] : $image_library_url . bola_image_news($row['image_headline'], '175/'));
        if (strtolower($row['source']) == 'persib.co.id') {
            $hlimage = persibnews_image_url($row['image_headline'], '175');
        }
        //if (is_file($hlimage))
        if ($row['image_headline']) {
            //$img = $thumbnail_media_url . $row['image_headline']; //str_pad($row['idnews'], 10, '0', STR_PAD_LEFT) .'.jpg';
            $img = ((strlen($row['image_headline']) == 14) ? $thumbnail_media_url . $row['image_headline'] : $image_library_url . bola_image_news($row['image_headline'], '175/'));
            if (strtolower($row['source']) == 'persib.co.id') {
                $img = persibnews_image_url($row['image_headline']);
            }

            $useclass = 'bcontent2_image';
            $line_image = '<div class="' . $useclass . '"><a href="' . $detailurl . '"><img src="' . $img . '" width="130"/></a><br class="clear"/><div>&nbsp;</div></div>';
        } else {
            $img = str_replace('//p', '/p', $cdn_url . 'library/' . str_replace($library_url, '/', $row['image']));
            $line_image = '';
        }

        $title_cencored = strlen($row['title']) > 55 ? substr($row['title'], 0, 53) . '..' : $row['title'];
        $synopsis_cencored = strlen($row['synopsis']) > 170 ? substr($row['synopsis'], 0, 168) . '..' : $row['synopsis'];

        $row['schedule'] = preg_replace('/(\d{4,4})\-(\d{2,2})\-(\d{2,2}) (\d{2,2})\:(\d{2,2})\:(\d{2,2})/', '\3-\2-\1 \4:\5', $row['schedule']);
        $hari = $day_list_ina[date('w', strtotime($row['schedule']))];

        $counter++;

        $content .= '
            <div class="bcontent2">
                <div class="indschedule">' . $hari . ', ' . $row['schedule'] . '</div>
                <span><a href="' . $detailurl . '">' . $title_cencored . '</a></span>
                
                <br class="clear"/>
                ' . $line_image . '
                <div>' . balanceTags($row['synopsis'], true) . '</div>
                <br class="clear" />
            </div>
        ';

        if ($counter > 30) {
            $content .= '
                    <br />
                    <center>' . $paging . '</center>
                </div>
                </div>
            ';
            $filename = BOLADIR . 'berita-sepak-bola/index.html';
            $fileurl = BOLAURL . 'berita-sepak-bola/index.html';
            if ($page > 0) {
                $filename = BOLADIR . 'berita-sepak-bola/index' . $page . '.html';
                $fileurl = BOLAURL . 'berita-sepak-bola/index' . $page . '.html';
            }

            $active_menu = 0;

            $metatitle = 'Berita Sepak Bola';
            $metakey = 'berita, sepakbola, sepak bola, bola';
            $metadesc = 'Berita Sepak Bola';

            if ($page > 0) {
                $metatitle = $first_title;
                $metadesc = $first_desc;
                $metakey = $first_keywords;
            }

            write_file($filename, $content, $metatitle, $metakey, $metadesc, '', true, 'noleft', $active_menu);
            echo generated_link($fileurl);

            $counter = 1;
            $page++;
            $paging = global_paging_10($page, $pagenum, 'index'); //$dir[$i][1]
            $content = '
        <div class="bigcon">
                    <div class="bigcon2">
                        <div class="nav">
                            <a ' . ga_trackEvent('link_nav') . ' href="/" style="text-decoration:none;">HOME</a> &raquo;
                            <a ' . ga_trackEvent('link_nav') . ' href="/berita-sepak-bola/" style="text-decoration:none;">BERITA SEPAK BOLA</a>
                        </div>
                        <br />
                        <div class="subindexcat">BERITA SEPAK BOLA</div><br />
        ';
        }
        if ($LIMIT_PAGE && $page >= $LIMIT_PAGE) {
            break;
        }
    }
    $sqlDB->sql_freeresult();

    $content .= '
                <br />
                <center>' . $paging . '</center>
            </div>
        </div>
    ';

    $filename = BOLADIR . 'berita-sepak-bola/index.html';
    $fileurl = BOLAURL . 'berita-sepak-bola/index.html';
    if ($page > 0) {
        $filename = BOLADIR . 'berita-sepak-bola/index' . $page . '.html';
        $fileurl = BOLAURL . 'berita-sepak-bola/index' . $page . '.html';
    }

    if ($page > 0) {
        $metatitle = $first_title;
        $metadesc = $first_desc;
        $metakey = $first_keywords;
    }

    write_file($filename, $content, $metatitle, $metakey, $metadesc, '', true, 'noleft', $active_menu);
    set_top_tagbar($sqlDB, $filename);
    echo generated_link($fileurl);
}

function generate_per_id_dev($sqlDB, $id, $message = true, $match_submenu = '')
{
    global $headline_media, $headline_media_url, $thumbnail_media, $thumbnail_media_url, $tag_url, $library_url, $cdn_url, $library_dir, $def_liga_eropa, $def_bola_dunia, $def_olahraga_lain, $def_bola_indonesia, $BALLBALL_CAT_CODES, $img_lazy_load, $image_library_dir, $image_library_url;

    /* check in schedule preview review */
    $qs = "SELECT id FROM dbschedule WHERE news_preview = '$id' OR news_review = '$id' LIMIT 1";
    $rs = $sqlDB->sql_query($qs, true) or die(__LINE__ . ' = ' . mysql_error());
    while ($rows = $sqlDB->sql_fetchrow($rs)) {
        $schedule_id = isset($rows['id']) ? $rows['id'] : '';

        if ($schedule_id && !$match_submenu) {
            if (!function_exists('generate_schedule_score_byid')) {
                include FUNCTIONDIR . "function.schedule.php";
            }
            generate_schedule_score_byid($sqlDB, $schedule_id);
            return '';
        }
    }

    $m_cross_selling = '
        <div class="mcross">
            Akses Bola.net melalui
            <a href="'.BOLAURL.'editorial/baca-bolanet-di-mana-saja-lewat-ponsel.html" target="_blank" class="greenlink">'.M_BASEURL.'</a>
            pada browser ponsel Anda.</i>
        </div>
    ';

    $sql = "SELECT dbnews.*, category_name, category_url
            FROM dbnews,dbcategory
            WHERE idnews = '$id' AND schedule <> '00-00-0000 00:00:00' AND dbnews.category = dbcategory.category_id
            LIMIT 1";

    $res = $sqlDB->sql_query($sql, true) or die(__LINE__ . ' = ' . mysql_error());
    if (!$res) {
        if ($message) {
            echo __LINE__ . ' = ' . mysql_error();
        }
        return false;
    }

    $dir = getAllNewsCat($sqlDB);

    // euro include
    if (!function_exists("euro_get_header")) {
        include(FUNCTIONDIR . "function.euro.php");
        include(FUNCTIONDIR . "function.euro.news.php");
        include(FUNCTIONDIR . "function.euro.pernik.php");
    }

    while ($row = $sqlDB->sql_fetchrow($res)) {
        /* open play */
        if ($row['category'] == '28') {
            if (!function_exists('openplay_gen_byid')) {
                include(FUNCTIONDIR . 'function.openplay.php');
            }
            openplay_gen_byid($sqlDB, $row, 0, $dir, $message);

            // euro pernik
            //if($row['category'] == 28 && strtolower($row['keyword1']) == 'euro 2012')
            //{
            //    euro_pernik_byid($row['idnews'], $sqlDB);
            //}
            continue;
        }
        /* end of open play */

        /* video unik */
        if ($row['category'] == '33') {
            if (!function_exists('videounik_gen_byid')) {
                include(FUNCTIONDIR . 'function.videounik.php');
            }
            videounik_gen_byid($sqlDB, $row, 0, $dir, $message);

            continue;
        }
        /* end of open play */

        $filename = BOLADIR . $dir[$row['category']][1] . '/' . $row['url'] . '.html';
        $fileurl = BOLAURL . $dir[$row['category']][1] . '/' . $row['url'] . '.html';
        $filename_print = BOLAURL . $dir[$row['category']][1] . '/print/' . str_pad($id, 10, '0', STR_PAD_LEFT) . '.html';

        //reformat schedule
        list($date, $time) = explode(" ", $row['schedule']);
        list($year, $month, $day) = explode("-", $date);
        list($hour, $minute, $second) = explode(":", $time);
        $datetime = $day . '-' . $month . '-' . $year . ' ' . $hour . ':' . $minute;

        // when FB comment was started
        $comment_form = '';
        $fbtime = 1322067600; // 2011-11-24
        $newstime = strtotime($row['schedule']);
        if ($newstime < $fbtime) {
            $is_facebook_comment = false;
            $comment_data = generate_news_comment($sqlDB, $id, $message);
            $total_comment = $comment_data[0];
            $latest_comment = $comment_data[1];
        } else {
            $is_facebook_comment = true;
            $total_comment = 0;
        }

        if ($total_comment > 0 && $is_facebook_comment === false) {
            $link = BOLAURL . $dir[$row['category']][1] . '/komentar/' . $row['url'] . '.html';
            $link_comment = ' <span style="font-size: 0.9em;color:#ff9900;">(<a style="color:#ff9900;" href="' . $link . '">' . $total_comment . ' komentar</a>)</span>';
            $all_comment = '<div style="text-align:right;"><a href="' . $link . '" class="bluelink">selengkapnya (' . $total_comment . ') >> </a></div>';
            $box_blue_comment = '<a href="' . $link . '" class="bluelink">KOMENTAR (' . $total_comment . ')</a>';
            $titlecomment = '<div class="ntbottom jdboxx">Komentar Pembaca ' . $link_comment . '</div>';
        } else {
            if ($is_facebook_comment === false) {
                $link_comment = ' <span style="font-size: 0.9em;color:#ff9900;">(0 komentar)</span>';
                $all_comment = '';
                $box_blue_comment = '<a href="#frcomn" class="bluelink">KOMENTAR (0)</a>';
                $titlecomment = '';
            } else {
                $link_comment = '';
                $all_comment = '';
                $box_blue_comment = '';
                $titlecomment = '';
            }
        }

        if ($is_facebook_comment === false) {
            $comment_form = $titlecomment . '
                <a name="frcomn"></a>
                ' . $latest_comment . $all_comment . '
                <div class="clear"></div><br/>
                <div id="frmcomment"></div>
                <div class="clear"></div>
                <script type="text/javascript">loadfrmcomment("news|' . $row['idnews'] . '|' . $total_comment . '|' . $row['url'] . '");</script>
            ';
            $comment_ticker_info = '
                <div class="no2">
                    <img src="' . $cdn_url . 'library/i/v1/newscomment.jpg" alt="">
                    ' . $box_blue_comment . '
                </div>
            ';
        } else {
            $comment_form = '
                <br/>
                <div id="news_comment" class="box_related">
                    <div class="rel_header" style="border:none">
                        Komentar Anda
                    </div>
                </div>
            ';
            $comment_form .= fb_comment_form($fileurl, 5, 468);
            $comment_form = ($row['news_sensitive'] == 1) ? '' : $comment_form ;
            $comment_ticker_info = '';
        }

        $addthis = '
            <script type="text/javascript">
                $(document).ready(function() {
                    $(".no3").hover(
                        function() {
                            $(".newshid").slideDown(400);
                        },
                        function() {
                            return false;
                        }
                    );
                    im("#kvy");
                    recomm_view("' . $row['idnews'] . '|news");
                });
            </script>
        ';
        $share = '
            <!--SOCIALTAB-->
            <link rel="stylesheet" href="'.APPSURL_KL.'v5/css/plugin.socmed.css">
            <script type="text/javascript" src="'.APPSURL_KL.'v5/js/plugin.socmed.js"></script>
            <div id="kl-social-share"></div><br/><br/>
            <!--ENDSOCIALTAB-->
        ';
        //comment_count:'.($is_facebook_comment === false ? 'total' : '-1').'

        $meta_og_image = '';

        /* show related image */
        $content_image = '';
        if ($row['image'] != '') {
            $row['image'] = str_replace($library_url, '/', $row['image']);
            $news_image = $cdn_url . 'library/' . str_replace('/p/', 'p/', $row['image']);
            $content_image .= '
                <div class="imgboxright">
                    <img src="' . $news_image . '" alt="' . $row['imageinfo'] . '" /><br />' . $row['imageinfo'] . '
                </div>
            ';
            $meta_og_image = $news_image;
        }

        /* headline image */
        $headline_image = '';
        $headline_image_file = ((strlen($row['image_headline']) == 14) ? $headline_media. $row['image_headline'] : $image_library_dir.$row['image_headline']);
        //if (is_file($headline_image_file))
        if ($row['image_headline']) {
            $headline_image = ((strlen($row['image_headline']) == 14) ? $headline_media_url. $row['image_headline'] : $image_library_url.$row['image_headline']);
            //$meta_og_image = $thumbnail_media_url. $row['image_headline']; //str_pad($row['idnews'], 10, '0', STR_PAD_LEFT) .'.jpg';
            $meta_og_image = $headline_image;
            $content_image = '';
        }

        /* persib news */
        $persib_mark = $persib_source = '';
        if (strtolower($row['source']) == 'persib.co.id') {
            $headline_image = persibnews_image_url($row['image_headline']);
            $meta_og_image = persibnews_image_url($row['image_headline'], "320");
            /*
              $persib_mark = '
              <div style="text-align:left;margin:0 0 -5px 0;padding:0;">
              <a href="http://persib.co.id/" target="_blank" style="text-decoration:none !important;outline:none !important">
              <img src="'.KLIMGLIBURL.'i/v2/logopersib.png?9" alt="Persib Bandung Official Site" />
              </a>
              </div>
              ';
             */
            $persib_mark = '';
            $persib_source = '
        <p style="text-align:left;font-style:italic;margin-top:0;">
        Sumber: 
        <a href="' . $row['source_url'] . '" target="_blank" class="bluelink">
            Persib.co.id
        </a>
        </p>
        ';
            $row['title'] = str_replace('PERSIB', 'Persib', $row['title']);
            $row['news'] = str_replace('PERSIB', 'Persib', $row['news']);
        }

        /* news paging */
        $is_paging = false;
        $idpaging = 0;
        $paging_order = '';

        $np_navrows = array();
        $paging_top_nav = '';
        $paging_nav = '';
        $paging_js = ''; //'<script type="text/javascript" src="'.APPSURL2.'assets/js/paging.js?c"></script>';
        $paging_nav_js = '';
        $paging_btn = '';

        if ($row['is_pagging'] == 1) {
            $paging_js = '<script type="text/javascript" src="' . APPSURL2 . 'assets/js/min/single/1.0/paging2.js"></script>';

            //get paging options
            $npq = "SELECT id, `order` FROM dbpagging WHERE relation_id = '{$row['idnews']}' AND type_key = '1'";
            $res_npq = $sqlDB->sql_query($npq, true) or die(__LINE__ . ' = ' . mysql_error());
            if ($res_npq) {
                $npq_row = $sqlDB->sql_fetchrow($res_npq);
                $idpaging = isset($npq_row['id']) ? $npq_row['id'] : '';
                $np_order = isset($npq_row['order']) ? $npq_row['order'] : 0;
                if ($np_order != 1) {
                    $np_order = 0;
                    $paging_order = "ASC";
                } else {
                    $paging_order = "DESC";
                }

                //get paging detail
                if ($idpaging) {
                    $npq = "SELECT id, no, title FROM dbpagging_detail WHERE idpagging = '$idpaging' AND status = '1' ORDER BY `no` $paging_order";
                    $res_npq = $sqlDB->sql_query($npq, true);
                    if ($res_npq) {
                        $np_count = $sqlDB->sql_numrows($res_npq);

                        $current = 0;
                        $next_url = get_paging_url($fileurl, '1');
                        if ($np_order) {
                            $next_url = get_paging_url($fileurl, $np_count);
                            $current = $np_count + 1;
                        }
                        $paging_top_nav = get_paging_top_nav($fileurl, $next_url);

                        $np_navrows = $sqlDB->sql_fetchrowset($res_npq);
                        $start = $np_navrows[0]['no'];
                        $start_name = $np_navrows[0]['title'];
                        $paging_nav = get_paging_nav($np_navrows, $fileurl, 0);

                        $paging_nav_js = '
                            <script type="text/javascript">
                                $(function(){
                                    BolaPaging.init({
                                        current: ' . $current . ',
                                        max: ' . $np_count . ',
                                        order: ' . $np_order . ',
                                        url: "' . $fileurl . '",
                                        container: "#newspaging_content",
                                        contentid: ' . $row['idnews'] . '
                                    });
                                });
                            </script>                            
                        ';
                        if ($row['idnews'] != '97090') {
                            //$paging_nav_js = ''; // disable ajax
                        }
                        $start_url = str_replace('.html', '-' . $start . '.html', $fileurl);
                        $paging_btn = '
                            <br/>
                            <div class="np_link_nav">
                                <a class="np_next" href="' . $start_url . '" class="first" onclick="_gaq.push([\'_trackEvent\', \'Paging - Bottom\', \'click\', \'' . $start_url . '\']);">' . ($start_name ? 'Mulai dari ' . $start_name : 'MULAI DENGAN NO. ' . $start) . '</a>
                            </div>                        
                        ';

                        $is_paging = true;
                    }
                }
            }
        }

        /* Send to Friends [2012-05-22] */
        $s2f_title = addslashes($row['title']);
        $s2f_player = addslashes($row['celebrity']);
        $s2f_synopsis = addslashes(str_replace(array("\n", "\r"), "", $row['synopsis']));
        $s2f = "
            <script type='text/javascript'>            
            $(document).ready(function() {
                s2f.init({
                    s2f1: {
                        element: '#sendfriendsbtn',
                        news_id: '{$row['idnews']}',
                        news_type: 'news',
                        title: '$s2f_title',
                        date: '$datetime',
                        player: '$s2f_player',
                        synopsis: '$s2f_synopsis'
                    }
                });
            });
            </script>
        ";
        
        /* subtitle and tag subtitle */
        $subtitle = isset($row['subtitle']) ? $row['subtitle'] : '';
        $tag_subtitle = ($row['tag_subtitle'] != 0) ? $row['tag_subtitle'] : '';
        $url_tag_subtitle = isset($tag_subtitle) ? BOLAURL.'tag/'.getURLTag($sqlDB, $tag_subtitle, '1') : '#';
        $htmlsub = '';
        if ($subtitle) {
            $htmlsub .= '<p class="subtitle"><a href="'.$url_tag_subtitle.'" ' . ga_trackEvent('subtitle', $subtitle) . '>'.$subtitle.'</a></p>';
        }
        /* eof subtitle and tag subtitle */
        
        /* top content */
        $content_top = get_bola_content_tracker($row['idnews'], 'news', $fileurl).$s2f;
        if ($is_paging) {
            $content_top .= $paging_js;
        }
        $content_top .= '
            <div class="bigcon">
                <div class="bigcon2">
                    <div class="nav">
                        <a ' . ga_trackEvent('link_nav') . ' href="/" style="text-decoration:none;">HOME</a> &raquo;
                        <a ' . ga_trackEvent('link_nav') . ' href="/' . $row['category_url'] . '/" style="text-decoration:none;">' . strtoupper($row['category_name']) . '</a> &raquo;
                        ' . strtoupper($row['title']) . '
                    </div>
                    <%HEADER_ADVERTORIAL%>
                    <%HEADER_CONTENT%>
                    ' . $htmlsub . '
                    ' . $match_submenu . '
                    <h1 class="newstitle news">' . $row['title'] . '</h1>
                    <div class="newsdatetime" style="float: left;">' . $datetime . '</div>
                    ' . (isset($row['celebrity']) && $row['celebrity'] ? '<h2 style="margin: 0px; float: left;" class="newsdatetime">&nbsp;| ' . $row['celebrity'] . '</h2>' : '') . '
                    
            <div class="clear"></div>
            ' . $persib_mark . '
            <div class="clear"></div>
        ';
        $content_top .= $share;

        // if timo, show header
        $header_content = '';
        // tags
        $suggestion_tags = '';
        $advetorial_header = '';
        $first_tag = array();
        $generatetags_detail = array();
        $qtags = "
            SELECT A.tags_id, A.tags_name, A.tags_url
            FROM `dbtags` A INNER JOIN dbtags_assosiation B ON A.tags_id=B.tags_id
            WHERE A.tags_level='1' AND B.idnews='$id'";
        
        $tags_content = '';
        if ($rtags = $sqlDB->sql_query($qtags, true)) {
            while ($rowtags = $sqlDB->sql_fetchrow($rtags)) {
                if (!$first_tag) {
                    $first_tag = $rowtags;
                }
                $generatetags_detail[] = $rowtags['tags_id'];
                $tags_content .= '<a href="' . $tag_url . $rowtags['tags_url'] . '/" ' . ga_trackEvent('tag', stripslashes($rowtags['tags_name'])) . '>' . stripslashes($rowtags['tags_name']) . '</a>';

                if ($rowtags['tags_id'] == '5218') {
                    $header_content = get_pengamat_header();
                }
                if ($rowtags['tags_id'] == '9275') {
                    $comment_form = $comment_ticker_info = '';
                    $advetorial_header = '<br/><span style="background:#9BCC01;border-radius:4px;-moz-border-radius:4px;padding: 5px 10px; color:#fff;display:inline-block;font-size:16px;font-weight:bold;">Advertorial</span>';
                }
                $suggestion_tags .= $rowtags['tags_id'].',';
            }
            $sqlDB->sql_freeresult($rtags);

            if ($tags_content) {
                $tags_content = '
                    <!--STAG-->
                    <div class="detags">
                        <span class="title">Tag</span>
                        ' . $tags_content . '
                    </div>
                    <!--ETAG-->
                ';
            }
        }

        $content_top = str_replace('<%HEADER_CONTENT%>', $header_content, $content_top);
        $content_top = str_replace('<%HEADER_ADVERTORIAL%>', $advetorial_header, $content_top);
        
        /* main content */
        $content_main = $paging_top_nav;
        if ($headline_image) {
            if ($row['idnews'] == 247249 || $row['idnews'] == 247247) {
                $headline_image .= '?dwadwa';
            }
            
            $content_main .= '
                <div class="news-headline-image news">
                    <img class="lazy_loaded" src="'.$img_lazy_load.'" data-src="' . $headline_image . '" width="468" alt="' . htmlentities($row['title']) . '" />
                    ' . ($row['imageinfo'] ? '<div>' . str_replace('(c)', '&copy;', $row['imageinfo']) . '</div>' : '') . '
                </div>
            ';
        }
        $content_main .= $paging_nav;
        $content_main .= $content_image;


        // hard hard hard
        $row['news'] = preg_replace('/^\<b\>KapanLagi\.com\<\/b\>/si', '<b>Bola.net</b>', $row['news']);


        $row['news'] = putProfileLink($row['news'], $row['celebrity'], $row['celebrity1'], $row['celebrity2'], $row['celebrity3'], $row['celebrity4'], $row['celebrity5']);
        $row['news'] = putClubLink($row['news'], $row['club1'], $row['club2'], $row['club3']);

        # embed sosmed .[2015-11-03]-------------------------------------------------------------------------------
        //echo "here";
        $news = $row['news'];
        preg_match_all('/sosmed\_([0-9]+)/', $news, $data);
        //print_r($data);
        $id_all = implode("','", $data[1]);
        //echo "SELECT * FROM oto_embed_sosmed WHERE id IN ('$id_all')";
        if (isset($id_all) && !empty($id_all)) {
            $hasil = embed_sosmed($id_all);
            if (isset($hasil) && !empty($hasil)) {
                foreach ($hasil as $h) {
                    //echo $h->code;
                    //print_r($h);
                    $datas = getembedsosmed($h['code'], $h['url']);
                    $code = "<!-- sosmed_".$h['id']." -->";
                    $news = str_replace($code, $datas, $news);
                }
                $row['news'] = $news;
            }
        }
        //------------------------------------------------------------------------------------------------------------------
        
        #hafid imageslider 09-06-2016 --------------------------------------------------------------------------------------
        //$news = $row['news'];
        //$row['news'] = image_slider($news);
        //end images -------------------------------------------------------------------------------------------------------
        
        // scan [polling]
        if (preg_match_all('/\[polling\](\d+)\[\/polling\]/s', $row['news'], $mf)) {
            if (!defined('POLLING_CLASS')) {
                include(CLASSDIR . 'Polling.php');
            }

            foreach ($mf[1] as $fm) {
                $polling = new Polling();
                $polling->set_id($fm);
                $polling = $polling->show();
                $row['news'] = str_replace('[polling]' . $fm . '[/polling]', $polling, $row['news']);
            }
            unset($mf);
        }

        // scan [formation]
        if (preg_match_all('/\[formation\s*(enemy=("|\&quot;)(\d+)("|\&quot;))?\](\d+)\[\/formation\]/s', $row['news'], $mf)) {
            if (!defined('FORMATION_CLASS')) {
                include(CLASSDIR . 'Formation.php');
            }

            foreach ($mf[5] as $fk => $fm) {
                $enemy = isset($mf[3][$fk]) ? intval($mf[3][$fk]) : '';
                $formation = new Formation();
                $formation->set_id($fm);

                if ($enemy) {
                    $formation->set_enemy($enemy);
                    $formation = $formation->show(false);
                } else {
                    $formation = $formation->show();
                }

                $row['news'] = preg_replace('/\[formation\s*(enemy=("|\&quot;)(\d+)("|\&quot;))?\]' . $fm . '\[\/formation\]/si', $formation, $row['news']);
            }
            unset($mf);
        }

        // scan [quotes]
        if (preg_match_all('/\[quotes\](\d+)\[\/quotes\]/s', $row['news'], $mf)) {
            if (!defined('QUOTES_CLASS')) {
                include(CLASSDIR . 'Quotes.php');
            }

            foreach ($mf[1] as $fm) {
                $quotes = new Quotes();
                $quotes->set_id($fm);
                $quotes = $quotes->show();
                $row['news'] = str_replace('[quotes]' . $fm . '[/quotes]', $quotes, $row['news']);
            }
            unset($mf);
        }


        // scan [initial]
        if (preg_match('/\[initial\]/s', $row['news'])) {
            preg_match('/\((([a-zA-Z0-9]{2,})\/([a-zA-Z0-9]{2,})\/?([a-zA-Z0-9]{2,})?)\)$/s', $row['news'], $m);
            $row['news'] = str_replace($m[0], '', $row['news']);
            $row['news'] = str_replace('[initial]', '<b>' . $m[0] . '</b>', $row['news']);
        }
        
        // hack
        $row['news'] = str_replace('<p><p>', '<br /><br />', $row['news']);
        $row['news'] = preg_replace('/((?<=\<p\>|\<\/p\>|\<li\>|\<\/li\>|\<ol\>|\<ul\>|\<\/ol\>|\<\/ul\>|\<blockquote\>|\<\/blockquote\>|\<br \/\>|\<tbody\>|\<\/tbody\>|\<tr\>|\<\/tr\>|\<td\>|\<\/td\>|\<\/table\>|\<\/div\>|\&lt\;p\&gt\;|\&lt\;\/p\&gt\;|\&lt\;li\&gt\;|\&lt\;\/li\&gt\;|\&lt\;ol\&gt\;|\&lt\;ul\&gt\;|\&lt\;\/ol\&gt\;|\&lt\;\/ul\&gt\;|\&lt\;blockquote\&gt\;|\&lt\;\/blockquote\&gt\;|\&lt\;\/br \/\&gt\;|\&lt\;tbody\&gt\;|\&lt\;\/tbody \/\&gt\;|\&lt\;tr\&gt\;|\&lt\;\/tr\&gt\;|\&lt\;td\&gt\;|\&lt\;\/td\&gt\;|\&lt\;\/table\&gt\;|\&lt\;\/div\&gt\;)\<p\>)/si', '', $row['news']);
        $row['news'] = preg_replace('/(\<table([^\>]*?)\>)\<p\>/si', '$1', $row['news']);
        $row['news'] = preg_replace('/((\<|\&lt;)div([^\>]*?)(\>|\&gt;))\<p\>/si', '$1', $row['news']);
        $row['news'] = preg_replace('/(\<img([^\>]*?)\/?\>)\<p\>/si', '$1', $row['news']);
        $row['news'] = preg_replace('/((?<!\<br\s\/\>|\&lt\;br \/\&gt\;)\<p\>)/si', '<br />', $row['news']);
        $row['news'] = str_replace('<br <br />', '<br />', $row['news']);
        
        //adjust paragraph in persib
        if (strtolower($row['source']) == 'persib.co.id') {
            $row['news'] = str_replace('<br />', '<br /><br />', $row['news']);
            $row['news'] = preg_replace('/\<br \/\>\<br \/\>/', '', $row['news'], 1);
            $row['news'] = preg_replace("#(<br */?>\s*)+#i", "<br /><br />", $row['news']);
        }

        /* Grab Image Body if alt not empty */
        if (preg_match_all("/<img[^>]+>/i", $row['news'], $matchesarray)) {
            $count = count($matchesarray[0]);
            if ($count > 0) {
                foreach ($matchesarray[0] as $img_tag) {
                    preg_match('/alt="([^"]*)"/i', $img_tag, $img_alt);
                    if (isset($img_alt[1]) && $img_alt[1]) {
                        $img_alt[1] = '<p>' . $img_alt[1] . '</p>';

                        $row['news'] = str_replace($img_tag, '<div class="bola-image-body">' . $img_tag . $img_alt[1] . '</div>', $row['news']);
                        $row['news'] .= "<script>
                                        $(document).ready(function() {
                                            $('.bola-image-body img').addClass('lazy_loaded');
                                            $('.bola-image-body img').attr('src','http://cdn.klimg.com/vemale.com/p/i/logo/1px_white.JPG');
                                            $('.lazy_loaded').unveil(100, function(){
                                                $(this).load(function(){
                                                    this.style.opacity = 1;
                                                });
                                            });
                                        });
                                        </script>";
                    }
                }
            }
        }

        /*
          if (preg_match_all("/<img[^>]+>/i", $row['news'], $matchesarray)) {
          $count = count($matchesarray[0]);
          if ($count > 0) {
          foreach ($matchesarray[0] as $img_tag) {
          preg_match('/alt="([^"]*)"/i', $img_tag, $img_alt);
          if(isset($img_alt[1])) {
          $row['news'] = str_replace($img_tag, '<div class="bola-image-body">' . $img_tag . '<p>'.$img_alt[1] .'</p></div>', $row['news']);
          }
          }
          }
          }
         */
        
        /* replace all tag img src to img data-src */
        $row['news'] = preg_replace('/<img(.*?)src/', '<img data-src', $row['news']);
        
        /* Tiket Pesawat Link [2013-11-29] */
        if (!$advetorial_header) {
            $row['news'] = tiket_pesawat_link($row['news']);
        }

        /* track ext link 20141007 */
        $row['news'] = track_externalLink($row['news'], $row['title']);
        
        /* Parsing Related Content [2013-09-23] */
        $related_content = get_related_content($sqlDB, $row['idnews'], '1'); //type = 1 [news]

        if ($related_content && is_array($related_content)) {
            foreach ($related_content as $pos => $value) {
                //echo "$pos <br/>";
                $row['news'] = str_replace("<!--$pos-->", $value, $row['news']);
            }
        }
        /* End of Parse Related Content */
        
        /* Parsing Related Quote [2013-10-30] */
        //connect.facebook.net/en_US/all.js#xfbml=1&appId=109215469105623
        $related_quote_html = '         
        <div id="fb-root"></div>
        <script>(function(d, s, id) {
          var js, fjs = d.getElementsByTagName(s)[0];
          if (d.getElementById(id)) return;
          js = d.createElement(s); js.id = id;
          js.src = "//connect.facebook.net/en_US/sdk.js#version=v2.0&xfbml=1&appId=109215469105623";
          fjs.parentNode.insertBefore(js, fjs);
        }(document, "script", "facebook-jssdk"));</script>
            <script>
            $(function(){
                $(".rel_quote_fb_share").click(function(e){
                    var this_url = $(this).attr("href");
                    window.open(this_url, "Bola.net Share Quote", "height=300, width=600");
                    e.preventDefault();
                });
            })
            </script>
            <style>
            @import url(https://fonts.googleapis.com/css?family=Archivo+Narrow:700italic,700);
            </style>
        ';
        $related_quotes = get_related_quote($sqlDB, $row['idnews'], '1', $fileurl); //type = 1 [news]
        if ($related_quotes && is_array($related_quotes)) {
            foreach ($related_quotes as $pos => $value) {
                $row['news'] = str_replace("<!--$pos-->", $value, $row['news']);
            }
        }
        /* End of Parse Related Quote */
        
        $logo_src = $sumber = '';
        if ($row['source'] == 'otosia') {
            $logo_src .= '<div style="padding-bottom: 35px;border-bottom: 1px solid #ECECEC;">
                            <a href="http://www.otosia.com/" alt="http://www.otosia.com/" onclick="_gaq.push([\'_trackEvent\', \'Out Bound Links\', \'Click\', \'Logo Bola.net\']);" target="_blank"> 
                              <img src="'.KLIMGURL.'library/upload/10/2015/06/175/otosia_bec0690.png" alt="otosia.com" style="float: left;">
                           </a>
                        </div><br/>';
            $sumber .= '<br/>
            Sumber <a href="http://www.otosia.com/" alt="http://www.otosia.com/" onclick="_gaq.push([\'_trackEvent\', \'Out Bound Links\', \'Click\', \''.$row['title'].'\']);" target="_blank">Otosia</a>';
        }

        $related_bf = news_get_photonews($sqlDB, $id);
        /*
          $news_socmed = '
          <br/>
          <div id="socmed_add">
          <div class="socmed_add1">
          <a href="https://twitter.com/bolanet" class="twitter-follow-button" data-show-count="false">Follow @bolanet</a>
          <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
          <iframe id="fblikeid" src="http://www.facebook.com/plugins/like.php?locale=en_US&amp;href=http%3A%2F%2Fwww.facebook.com%2Fbola.net&amp;layout=button_count&amp;show_faces=false&amp;show_faces=false&amp;width=100&amp;action=like&amp;font&amp;colorscheme=light&amp;height=20" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px;" allowtransparency="true"></iframe>
          </div>
          <div class="socmed_add2">
          <a href="#news_comment" class="btn">
          <div class="sprite1 icon cmt"></div>
          <div class="label">Comment</div>
          <br class="clear"/>
          </a>
          <a href="'.$filename_print.'" class="btn" target="_blank">
          <div class="sprite1 icon print"></div>
          <div class="label">Print</div>
          <br class="clear"/>
          </a>
          <a id="sendfriendsbtn" href="'.APPSURL.'sendtofriend.php?curl='.$fileurl.'&ctitle='.rawurlencode($row['title']).'&cfrom=news&cid='.$id.'" class="btn">
          <div class="sprite1 icon mail"></div>
          <div class="label">Email</div>
          <br class="clear"/>
          </a>
          <div class="clear"></div>
          </div>
          <div class="clear"></div>
          </div>
          ';
         */
        $news_socmed = '<!-- social tab -->
        <script>
        $(document).ready(function() {
        $("#kl-social-share").klnShareSocmed({
                url:"' . $fileurl . '",
                comment_count:-1,
                twitter_user: "bolanet",
                email_button: "email"
            });
      

            socilatabs_pintit( "' . $fileurl . '" );
        });
        </script>
        <!-- end social tab -->';
        
        
        $next_prev_array = array();
        
        //berita terkait
        ###related news from tags 2014-06-12
        $_rel_max_ = 3;
        $related_news_tags = '';
        $_tags_news_id_ = '';
        if ($first_tag) {
            $qreltags = "SELECT * FROM dbtags_content WHERE tags_id = '{$first_tag['tags_id']}' AND type = '1' AND id!='$id'
                                    AND schedule <= NOW() AND level > '0' 
                                    ORDER BY schedule DESC LIMIT 1";
            $rftags = $sqlDB->sql_query($qreltags, true);
            while ($rowftags = $sqlDB->sql_fetchrow($rftags)) {
                $_tag_name_ = ucwords($first_tag['tags_name']);
                $_tag_url_ = $tag_url.$first_tag['tags_url'].'/';
                $_item_url_ = $tag_url.$first_tag['tags_url'].'/'.$rowftags['url'].'.html';
                if (strlen($rowftags['image_headline']) == 14) {
                    $_item_image_ = $thumbnail_media_url . $rowftags['image_headline'];
                } else {
                    $_item_image_ = $image_library_url.bola_image_news($rowftags['image_headline'], '125/');
                }
                $related_news_tags .= '
                    <div class="rel_item" >
                        <a href="'.$_item_url_.'" ' . ga_trackEvent('berita_terkait', $rowftags['title']) . '>
                            <img class="lazy_loaded" src="'.$img_lazy_load.'" data-src="'.$_item_image_.'" alt="'.$rowftags['title'].'" width="125" height="76"/>
                        </a>
                        <a href="'.$_tag_url_.'" class="tag_name" ' . ga_trackEvent('berita_terkait', $rowftags['title']) . '># Berita '.$_tag_name_.'</a>
                        <a href="'.$_item_url_.'" class="rel_item_title" ' . ga_trackEvent('berita_terkait', $rowftags['title']) . '><span>'.$rowftags['title'].'</span></a>
                    </div>';
                $_tags_news_id_ = $rowftags['id'];
            }
        }
//        $related_news_tags = $_tags_news_id_ = '';
        
        $related_news = '';
        $linkednews = getLinkedNews($sqlDB, $row['category'], $row['keyword1'], $row['schedule'], $_tags_news_id_);
        
        if (count($linkednews) > 0) {
            $related_news .= '
                <br/>
                <div class="box_related">
                    <div class="rel_header">
                        Berita Terkait ' . (isset($row['keyword1']) && $row['keyword1'] ? ucwords($row['keyword1']) : '') . '
                    </div>
                    <div class="rel_content">
            ';
            $refresh_to_related = '';
            $jsslideterkait = array();
            $count_rel = 0;
            $_rel_max_ = 3;
            if ($related_news_tags) {
                $_rel_max_ = 2;
            }
            foreach ($linkednews as $v) {
                if (strlen($v['image_headline']) == 14) {
                    $rel_news_image = $thumbnail_media_url . $v['image_headline'];
                } else {
                    $rel_news_image = $image_library_url . bola_image_news($v['image_headline'], '125/');
                }
                if (strtolower($v['source']) == 'persib.co.id') {
                    $rel_news_image = persibnews_image_url($v['image_headline'], '175');
                }
                if (count($linkednews) > 5 && count($jsslideterkait) < 2) {
                    //if (is_file($rel_news_image))
                    if ($v['image_headline']) {
                        if (strlen($v['image_headline']) == 14) {
                            $rel_news_image = $thumbnail_media_url . $v['image_headline'];
                        } else {
                            $rel_news_image = $image_library_url.bola_image_news($v['image_headline'], '125/');
                        }
                        if (strtolower($v['source']) == 'persib.co.id') {
                            $rel_news_image = persibnews_image_url($v['image_headline'], '175');
                        }
                        $jsslideterkait[] = "{
                            idnews: '" . $v['idnews'] . "',
                            title: ['" . addslashes($v['title']) . "', '" . BOLAURL . $dir[$v['category']][1] . '/' . $v['url'] . ".html'],
                            category: ['" . strtoupper($dir[$v['category']][0]) . "', '" . BOLAURL . $dir[$v['category']][1] . "/'],
                            image: '$rel_news_image' 
                        }";

                        /*if (!isset($next_prev_array['next']))
                        {
                            $next_prev_array['next'] = array('id' => $v['idnews'], 'title' => $v['title'], 'image' => $rel_news_image, 'url' => BOLAURL . $dir[$v['category']][1] . '/' . $v['url'] . '.html', 'cat_name' => $dir[$v['category']][0], 'cat_url' => BOLAURL . $dir[$v['category']][1] . '/');
                        }*/

                        continue;
                    }
                }
                
                $count_rel++;
                if ($related_news_tags && $count_rel == 1) {
                    $related_news .= $related_news_tags.'<div class="rel_space">&nbsp;</div>';
                }
                if ($count_rel > $_rel_max_) {
                    continue;
                }

                //if(is_file($rel_news_image))
                if ($v['image_headline']) {
                    #$rel_news_image = $thumbnail_media_url . $v['image_headline']; //str_pad($v['idnews'], 10, '0', STR_PAD_LEFT) .'.jpg';
                    if (strlen($v['image_headline']) == 14) {
                        $rel_news_image = $thumbnail_media_url . $v['image_headline'];
                    } else {
                        $rel_news_image = $image_library_url . bola_image_news($v['image_headline'], '125/');
                    }
                    if (strtolower($v['source']) == 'persib.co.id') {
                        $rel_news_image = persibnews_image_url($v['image_headline'], '175');
                    }
                    $rel_news_image = '<img class="lazy_loaded" src="'.$img_lazy_load.'" data-src="' . $rel_news_image . '" alt="' . $v['title'] . '" width="1250" height="76"/>';
                } else {
                    $rel_news_image = '';
                }
                $related_news .= '
                    <a class="rel_item" href="' . $v['url'] . '.html" ' . ga_trackEvent('berita_terkait', $v['title']) . '>
                        ' . $rel_news_image . '
                        <span>' . $v['title'] . '</span>
                    </a>
                ';
                if ($count_rel < $_rel_max_) {
                    $related_news .= '<div class="rel_space">&nbsp;</div>';
                }
                if (!$refresh_to_related) {
                    //$_refresh_url_ = BOLAURL . $dir[$row['category']][1].'/'.$v['url'].'.html';
                    $_refresh_url_ = BOLAURL . $dir[$row['category']][1] . '/';
                    $refresh_to_related = 'url = "' . $_refresh_url_ . '";
                    timeout = 1020;
                    window.setTimeout(\'window.location= "\' + url + \'"; \',timeout*1000);';
                }
            }//end foreach
            $related_news .= '<div class="clear"></div></div>'; //end div rel_content

            if ($refresh_to_related) {
                $refresh_to_related .= " \n";
                if (count($jsslideterkait) == 2) {
                    $refresh_to_related .= "readnext.init([" . implode(',', $jsslideterkait) . "], '.detail-jsview:last');";
                    $refresh_to_related .= " \n";
                }
                $addthis = str_replace('recomm_view', $refresh_to_related . 'recomm_view', $addthis);
            }
            $related_news .= '</div><br/>'; //end div box_related
        }


        /* news related information [profil, tim, galeri, wallpaper terkait] */
        $player_array = array($row['celebrity'], $row['celebrity1'], $row['celebrity2'], $row['celebrity3'], $row['celebrity4'], $row['celebrity5']);
        $team_array = array($row['club1'], $row['club2'], $row['club3']);
        $related_other = get_news_related_other($sqlDB, $player_array, $team_array);

        /*
         * #charis 20140327 Google AdXSeller: 468x60
         * '<!-- ADVETORIAL --><script type="text/javascript" src="'.APPSURL2.'contentmatch/index.php?v2"></script><!-- ENDOFADVETORIAL -->';
         *
         */

        //berita lain
        $othernews = getOtherNews($sqlDB, $row['category'], $row['keyword1'], $row['schedule']);
        $other_news = '';
        if (count($othernews) > 0) {
            $other_news .= '
               <div class="box_related">
                    <div class="rel_header">
                        Berita Lainnya
                    </div>
            ';
            $_count = 1;
            foreach ($othernews as $k=>$v) {
                /*$other_news_image = $thumbnail_media . $v['image_headline']; //str_pad($v['idnews'], 10, '0', STR_PAD_LEFT) .'.jpg';
                if( strlen($v['image_headline']) == 14 ){
                    $other_news_image = $thumbnail_media_url . $v['image_headline'];
                }else{
                    $other_news_image = $image_library_url.bola_image_news($v['image_headline'],'125/');
                }*/
                //if(is_file($other_news_image))
                if ($k == 0 || $k == 3) {
                    $other_news .= '<div class="rel_content">';
                }
                if ($v['image_headline']) {
                    $other_news_image = $thumbnail_media_url . $v['image_headline']; //str_pad($v['idnews'], 10, '0', STR_PAD_LEFT) .'.jpg';
                    if (strlen($v['image_headline']) == 14) {
                        $other_news_image = $thumbnail_media_url . $v['image_headline'];
                    } else {
                        $other_news_image = $image_library_url.bola_image_news($v['image_headline'], '125/');
                    }
                    $other_news_image = '<img class="lazy_loaded" src="'.$img_lazy_load.'" data-src="' . $other_news_image . '" alt="' . $v['title'] . '" width="125" height="76"/>';
                } else {
                    $other_news_image = '';
                }
                if (strtolower($v['source']) == 'persib.co.id') {
                    $other_news_image = '<img class="lazy_loaded" src="'.$img_lazy_load.'" data-src="' . persibnews_image_url($v['image_headline'], '175') . '" alt="' . $v['title'] . '" width="125" height="76"/>';
                }
                $other_news .= '
                    <a class="rel_item" href="' . $v['url'] . '.html" ' . ga_trackEvent('berita_lainnya', $v['title']) . '>
                        ' . $other_news_image . '
                        <span>' . $v['title'] . '</span>
                    </a>
                ';
                if ($_count < 3) {
                    $other_news .= '<div class="rel_space">&nbsp;</div>';
                }
                
                if ($k == 2 || $k == 5) {
                    $other_news .= '<div class="clear"></div>
                                </div>';
                }
            }
            $other_news .= '
                </div>
            ';
        }

        $merdeka_brand = get_merdeka_box($row['schedule']);

        /*$linkednews_prev = $is_paging === false ? getLinkedNewsPrev($sqlDB, $row['category'], $row['keyword1'], $row['schedule']) : array();
        if (count($linkednews_prev) > 0)
        {
            foreach ($linkednews_prev as $v)
            {
                if ($v['image_headline'])
                {
                    $rel_news_image = $thumbnail_media_url . $v['image_headline'];
                    if (!isset($next_prev_array['prev']))
                    {
                        $next_prev_array['prev'] = array('id' => $v['idnews'], 'title' => $v['title'], 'image' => $rel_news_image, 'url' => BOLAURL . $dir[$v['category']][1] . '/' . $v['url'] . '.html', 'cat_name' => $dir[$v['category']][0], 'cat_url' => BOLAURL . $dir[$v['category']][1] . '/');
                        break;
                    }
                }
            }
        }

        $next_prev_content = '<div>';
        if (count($next_prev_array) > 0 && $is_paging === false) // && $idnew = '92209' )
        {
            if (isset($next_prev_array['prev']))
            {
                $next_prev_content .= '
                    <div class="prev-next-item prev-next-news-l">
                        <div class="artikel-box">
                            <div class="button-arrow-box">
                                <a href="' . $next_prev_array['prev']['url'] . '" ' . ga_trackEvent('prevnext', $next_prev_array['prev']['title']) . ' class="artikel-arrow arrow-left"></a>
                                <div class="deskrip-artikel-bola">
                                    <div>
                                        <a href="' . $next_prev_array['prev']['cat_url'] . '" ' . ga_trackEvent('prevnext', $next_prev_array['prev']['cat_name']) . ' ><h2>' . strtoupper($next_prev_array['prev']['cat_name']) . '</h2></a>
                                        <a href="' . $next_prev_array['prev']['url'] . '" ' . ga_trackEvent('prevnext', $next_prev_array['prev']['title']) . ' ><p>' . $next_prev_array['prev']['title'] . '</p></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>';
            }

            if (isset($next_prev_array['next']))
            {
                $next_prev_content .= '
                    <div class="prev-next-item prev-next-news-r">
                        <div class="artikel-box">
                            <div class="button-arrow-box">
                                <div class="deskrip-artikel-bola">
                                    <div>
                                        <a href="' . $next_prev_array['next']['cat_url'] . '" ' . ga_trackEvent('prevnext', $next_prev_array['next']['cat_name']) . '><h2>' . strtoupper($next_prev_array['next']['cat_name']) . '</h2></a>
                                        <a href="' . $next_prev_array['next']['url'] . '" ' . ga_trackEvent('prevnext', $next_prev_array['next']['title']) . '><p>' . $next_prev_array['next']['title'] . '</p></a>
                                    </div>
                                </div>
                                <a href="' . $next_prev_array['next']['url'] . '" ' . ga_trackEvent('prevnext', $next_prev_array['next']['title']) . ' class="artikel-arrow arrow-right"></a>
                            </div>
                        </div>
                    </div>';
            }
        }
        $next_prev_content .= '</div>';*/
        //$next_prev_content = '';

        /* OTOSIA Widget 2013-09-04 */
        $otosia_widget = '<div id="oto_mostlike_widget"></div>';

        $content_bottom = '
            <!--JSVIEW-->
            <div class="detail-jsview">
                <script type="text/javascript" src="' . APPSURL2 . 'jscounter/?i=' . $id . '&g=news" async="async"></script>
            </div>
            <!--JSVIEW-->
            ' . $addthis . '
            ' . $related_bf . '
            
            ' . $news_socmed . '
            ' . $m_cross_selling . '            
            
            ' . $related_news . '
            ' . $related_other . '
            ' . $tags_content . '
            ' . $comment_form . '
            <br/>
            ' . $other_news . '
            <br/>
            <div class="news_arsip">
                <h3>
                    <a href="/arsip/' . $dir[$row['category']][1] . '/" ' . ga_trackEvent('arsip') . ' class="greenlink">Lihat Berita ' . $dir[$row['category']][0] . '</a>
                </h3>
                ' . get_arsip_top_menu($year, $month, $day, $dir, $dir[$row['category']][1]) . '
            </div>          
            <br/>
        ' . $otosia_widget . '
        ' . $merdeka_brand . '
        ';

        $content_bottom .= '
                </div> <!-- end of bigcon2 -->
                <br />
            </div>  <!-- end of bigcon -->
        ';

        if ($row['synopsis']) {
            $description = htmlspecialchars(strip_tags($row['synopsis']), ENT_QUOTES);
        } else {
            $description = htmlspecialchars(strip_tags($row['title']));
        }
        $allwords = explode(' ', preg_replace('/[^0-9a-z \-_]/', '', strtolower((isset($contentHasil->topics)?$contentHasil->topics:""))));
        $allkeywords = array();
        foreach ($allwords as $v) {
            $allkeywords[] = $v;
        }
        $allkeywords = array_unique($allkeywords);
        $setKeyword = implode(', ', $allkeywords);

        $active_menu = 0;
        if (in_array($row['category'], $def_liga_eropa)) {
            $active_menu = 1;
        } elseif (in_array($row['category'], $def_bola_dunia)) {
            $active_menu = 2;
        } elseif (in_array($row['category'], $def_bola_indonesia)) {
            $active_menu = 3;
        } elseif ($row['category'] == '14') {
            $active_menu = 4;
        } elseif (in_array($row['category'], $def_olahraga_lain)) {
            $active_menu = 9;
        }
        
        $extra_js = ($row['extra_js']) ? $row['extra_js'] : '';
        
        $anak = '';
        if (!$advetorial_header && ($row['news_sensitive'] == 0) && ($row['is_mature'] == 0)) {
            $anak .= '<!--ANAK ARTIS-->
                <br/><br/>
                <div>
                    <a href="http://www.anakartis.com" onclick="_gaq.push([\'_trackEvent\', \'Anakartis widget detail\', \'click\', \'Anakartis widget detail\']);">
                        <img src="'.KLIMGURL.'library/i/v2/anakartis2_640.jpg" width="100%"/>
                    </a>
                    <iframe width="100%" height="263px" src="https://www.youtube.com/embed/videoseries?list=PL0-YBychdh8_1zs1tEDZDcM7bGxE-b78m" frameborder="0" allowfullscreen=""></iframe>
                </div>
                <br/>
                <!--END ANAK ARTIS-->';
        }
        
        /* splitnews or default news*/
        $count_pagebreak = substr_count($row['news'], '<!-- Splitter Content -->');
        if ($count_pagebreak > 1) {
            if ($is_paging) {
                $content_main .= $related_quote_html . '
                    <br/>
                    <div class="ncont">
                    ' . $logo_src .' 
                    <%splitnews%>
                    <%nexprevsplitnews%>
                    ' . $persib_source . '
                    ' . $sumber . '
                    <div class="clear"></div>
                    ' . $paging_btn . '
                    ' . $anak . '
                </div>
                ';
            } else {
                $content_main .= $related_quote_html . '
                    <br/>
                    <div class="ncont">
                    ' . $logo_src .' 
                    <%splitnews%>
                    <%nexprevsplitnews%>
                    ' . $persib_source . '
                    ' . $sumber . '
                    ' . $anak . '
                    <div class="clear"></div>
                    ' . $paging_btn . '
                </div>
                ';
            }
            
            $content = $content_top . $paging_nav_js . '<div id="newspaging_content">' . $content_main . '</div>' . $content_bottom;
                
            preg_match_all('/\W.*?<!-- splitter content -->/i', $row['news'], $split_news);

            for ($i = 0 ; $i < $count_pagebreak; $i++) {
                $bolanet = ($i > 0) ? '<b>Bola.net</b> - ' : '';
                $content_split = $bolanet.$split_news[0][$i];
                $content_split = str_replace(array('<b>Bola.net</b> - <br /><br />', '<b>Bola.net</b> - <br />'), '<b>Bola.net</b> - ', $content_split);
                
                $filename = ($i == 0) ? BOLADIR . $dir[$row['category']][1] . '/' . $row['url'] . '.html' : BOLADIR . $dir[$row['category']][1] . '/' . $row['url'] . '-splitnews-'.($i+1). '.html';
                $fileurl = ($i == 0) ? BOLAURL . $dir[$row['category']][1] . '/' . $row['url'] . '.html' : BOLAURL . $dir[$row['category']][1] . '/' . $row['url'] . '-splitnews-'.($i+1). '.html';
                $filename_print = ($i == 0) ? BOLAURL . $dir[$row['category']][1] . '/print/' . str_pad($id, 10, '0', STR_PAD_LEFT) . '.html' : BOLAURL . $dir[$row['category']][1] . '/print/' . str_pad($id, 10, '0', STR_PAD_LEFT) . '-splitnews-'.($i+1). '.html';
                
                if ($i == 0) {
                    $next_split = next_prev_splitnews(BOLAURL . $dir[$row['category']][1] . '/' . $row['url'] . '-splitnews-'.($i+2). '.html', '', $row['title']);
                } elseif (($i == 1) && ($i != ($count_pagebreak-1))) {
                    $next_split = next_prev_splitnews(BOLAURL . $dir[$row['category']][1] . '/' . $row['url'] . '-splitnews-'.($i+2). '.html', BOLAURL . $dir[$row['category']][1] . '/' . $row['url'] . '.html', $row['title']);
                } elseif (($i == 1) && ($i == ($count_pagebreak-1))) {
                    $next_split = next_prev_splitnews('', BOLAURL . $dir[$row['category']][1] . '/' . $row['url'] . '.html', $row['title']);
                } elseif ($i == ($count_pagebreak-1)) {
                    $next_split = next_prev_splitnews('', BOLAURL . $dir[$row['category']][1] . '/' . $row['url'] . '-splitnews-'.($i). '.html', $row['title']);
                } else {
                    $next_split = next_prev_splitnews(BOLAURL . $dir[$row['category']][1] . '/' . $row['url'] . '-splitnews-'.($i+2). '.html', BOLAURL . $dir[$row['category']][1] . '/' . $row['url'] . '-splitnews-'.($i). '.html', $row['title']);
                }
                $news  = preg_replace('/<b>\((.+?)\)/', '', $row['news']);//sumber bug jika ada <b>(isi sembarang)</b> ==> </b>
                preg_match('/<b>\((.+?)\)/', $row['news'], $new);
                $src_int = isset($new[1])?$new[1]:'';
                $src_int = ($src_int) ? '<b>('.$src_int.')</b>' : '';
                
                $content_split = str_replace('<%splitnews%>', balanceTags($content_split, true).$src_int, $content);
                $content_split = str_replace('<%nexprevsplitnews%>', $next_split, $content_split);
                
                write_file($filename, $content_split, $row['title'], $row['title'], $row['title'], '', true, 'full', $active_menu, array('schedule' => $row['schedule'], 'amphtml' => true));
                insert_dfp_interest_segmen($filename, $row['dfp_interest']);
                insert_banner_inImageAds($filename);
                
                if (!empty($extra_js)) {
                    extra_js($extra_js, $filename);
                }
                
                if ($message) {
                    echo generated_link($fileurl);
                }
                insert_property_og($filename, $row['title'], $fileurl, $meta_og_image, '109215469105623', strip_tags($row["synopsis"]));
            }
        } else {
            if ($is_paging) {
                $content_main .= $related_quote_html . '
                    <br/>
                    <div class="ncont">
                    ' . $logo_src .' 
                    ' . balanceTags($row['news'], true) . '
                    ' . $persib_source . '
                    ' . $sumber . '
                    <div class="clear"></div>
                    ' . $paging_btn . '
                    ' . $anak . '
                </div>
                ';
            } else {
                $content_main .= $related_quote_html . '
                    <br/>
                    <div class="ncont">
                    ' . $logo_src .' 
                    ' . balanceTags($row['news'], true) . '
                    ' . $persib_source . '
                    ' . $sumber . '
                    ' . $anak . '
                    <div class="clear"></div>
                    ' . $paging_btn . '
                </div>
                ';
            }
            
            $content = $content_top . $paging_nav_js . '<div id="newspaging_content">' . $content_main . '</div>' . $content_bottom;
            
            write_file($filename, $content, $row['title'], $setKeyword, $description, '', true, 'full', $active_menu, array('schedule' => $row['schedule'], 'amphtml' => true));
            insert_dfp_interest_segmen($filename, $row['dfp_interest']);
            insert_banner_inImageAds($filename);
            
            if (!empty($extra_js)) {
                extra_js($extra_js, $filename);
            }
                
            if ($message) {
                echo generated_link($fileurl);
            }
        }
        /* eof splitnews or default news*/
        
        
        
        if ($row['source'] == 'otosia') {
            $detail_content = file_get_contents($filename);
            $can = '<link rel="canonical" href="'.$row['source_url'].'" />'."\n".'</head>';
            $detail_content = str_replace('</head>', $can, $detail_content);
            $detail_content = file_put_contents($filename, $detail_content);
        }
        
        
        if ($is_paging) {
            write_file_direct(str_replace('.html', '-data.html', $filename), $content_main);

            $npq = "SELECT * FROM dbpagging_detail WHERE idpagging = '$idpaging' AND status = '1' ORDER BY `no` $paging_order";
            $res_npq = $sqlDB->sql_query($npq, true);
            if ($res_npq) {
                $np_count = $sqlDB->sql_numrows($res_npq);

                $prev_url = $fileurl;
                $next_url = $np_next = '';
                while ($np_row = $sqlDB->sql_fetchrow($res_npq)) {
                    $np_current = $np_row['no'];

                    //build navigation
                    $paging_nav = get_paging_nav($np_navrows, $fileurl, $np_current);

                    //get prev, next url
                    if ($paging_order == 'DESC') {
                        $np_next = $np_current - 1;
                        $next_url = get_paging_url($fileurl, $np_next);
                        $next_name = isset($np_navrows[$np_count - $np_current + 1]['title']) ? $np_navrows[$np_count - $np_current + 1]['title'] : '';
                        if ($np_next < 1) {
                            $next_url = $fileurl;
                            $np_next = 0;
                        }
                    } else {
                        $np_next = $np_current + 1;
                        $next_url = get_paging_url($fileurl, $np_next);
                        $next_name = isset($np_navrows[$np_next - 1]['title']) ? $np_navrows[$np_next - 1]['title'] : '';
                        if ($np_next > $np_count) {
                            $next_url = $fileurl;
                            $np_next = 0;
                        }
                    }

                    $paging_top_nav = get_paging_top_nav($prev_url, $next_url, '<span style="font-size:16px;font-weight:bold;color:#fff;">' . $np_row['no'] . '. ' . $np_row['title'] . '</span>');

                    $paging_nav_js = '
                        <script type="text/javascript">
                            $(function(){
                                BolaPaging.init({
                                    current: ' . $np_current . ',
                                    max: ' . $np_count . ',
                                    order: ' . $np_order . ',
                                    url: "' . $fileurl . '",
                                    container: "#newspaging_content",
                                    contentid: ' . $row['idnews'] . '
                                });
                            });
                        </script>                            
                    ';
                    if ($row['idnews'] != '97090') {
                        //$paging_nav_js = ''; // disable ajax
                    }
                    $paging_btn = '';
                    if ($np_next) {
                        $paging_btn = '
                            <br/>
                            <div class="np_link_nav">
                                <a class="np_next" href="' . $next_url . '" onclick="_gaq.push([\'_trackEvent\', \'Paging - Bottom\', \'click\', \'' . $next_url . '\']);">' . ($next_name ? 'Lanjut ke ' . $next_name : 'LANJUT KE NO. ' . $np_next) . '</a>
                            </div>                        
                        ';
                    } else {
                        $q_paging = "SELECT category, url, title, image_headline FROM `dbnews` WHERE level != '0' AND level !='3' AND is_pagging='1' AND category<>'28' AND schedule<'" . $row['schedule'] . "' AND schedule<NOW() ORDER BY schedule DESC LIMIT 3";
                        $r_paging = $sqlDB->sql_query($q_paging, true) or die(__LINE__ . ' = ' . mysql_error());
                        $next_thumb_img = '';
                        
                        while ($row_paging = $sqlDB->sql_fetchrow($r_paging)) {
                            $next_paging = BOLAURL . $dir[$row_paging['category']][1] . '/' . $row_paging['url'] . '.html';
                            if (!isset($intro_url) || !$intro_url) {
                                $intro_url = $next_paging;
                            }
                            $intro_title = 'SELANJUTNYA';
                            $url_image = ((strlen($row_paging['image_headline']) == 16) ? $thumbnail_media_url . $row_paging['image_headline'] : $image_library_url.$row_paging['image_headline']);
                            if ($row_paging['image_headline']) {
                                //$next_thumb_img = '<center><a href="'.$next_paging.'"><img src="'. $thumbnail_media_url. $row_paging['image_headline'] .'" width="125" alt="'. strip_tags($row_paging['title']) .'" title="'. strip_tags($row_paging['title']) .'"></a></center>';
                                $next_thumb_img .= '<a class="rel_item" href="' . $next_paging . '"><img src="' . $url_image . '" alt="' . strip_tags($row_paging['title']) . '" title="' . strip_tags($row_paging['title']) . '">
                            <span>' . $row_paging['title'] . '</span>
                        </a><div class="rel_space">&nbsp;</div>';
                            }
                        }

                        if ($next_thumb_img) {
                            $next_thumb_img = '<div class="box_related"><div class="rel_content">' . $next_thumb_img . '<div class="clear"></div></div></div>';
                        } else {
                            $next_paging = '';
                            $intro_url = $fileurl;
                            $intro_title = 'KEMBALI KE INTRO';
                        }

                        $paging_btn = '
                            <br/>
                            <div class="np_link_nav">
                                <a class="np_next" href="' . $intro_url . '" id="np_intro_btn" class="first" onclick="_gaq.push([\'_trackEvent\', \'Paging - Bottom\', \'click\', \'' . $intro_url . '\']);">' . $intro_title . '</a>
                            </div>                        
                ' . $next_thumb_img . '
                        ';
                    }

                    $paging_content_main = '';
                    $np_resource = $np_row['path'];
                    if ($np_row['type'] == 0) {
                        $np_image_info = isset($np_row['imageinfo']) ? '<div>' . $np_row['imageinfo'] . '</div>' : '';
                        $np_resource = '<img class="lazy_loaded" src="'.$img_lazy_load.'" data-src="' . $cdn_url . 'library/' . $np_row['path'] . '" width="468" alt="' . htmlentities($np_row['title']) . '" />' . $np_image_info;
                    }
                    
                    $np_row['desc'] = preg_replace('/<img(.*?)src/', '<img data-src', $np_row['desc']);
                    
                    /* Tiket Pesawat Link [2013-09-23] */
                    if (!$advetorial_header) {
                        $np_row['desc'] = tiket_pesawat_link($np_row['desc']);
                    }

                    /* Parse Related COntent From Paging [2013-09-23] */

                    if ($related_content && is_array($related_content)) {
                        foreach ($related_content as $pos => $value) {
                            $np_row['desc'] = str_replace("<!--$pos-->", $value, $np_row['desc']);
                        }
                    }
                    /* End Parse Related COntent From Paging [2013-09-19] */

                    #new embed sosmed pagging  [2015-12-07]--------------------------------------------------------------------
                    $cnews = stripslashes($np_row['desc']);
                    preg_match_all('/sosmed\_([0-9]+)/', $cnews, $data);
                    $id_all = implode("','", $data[1]);
                    if (isset($id_all) && !empty($id_all)) {
                        $hasil = embed_sosmed($id_all);
                        if (isset($hasil) && !empty($hasil)) {
                            foreach ($hasil as $h) {
                                //echo $h->code;
                                $datas = getembedsosmed($h['code'], $h['url']);
                                $code = "<!-- sosmed_".$h['id']." -->";
                                $cnews = str_replace($code, $datas, $cnews);
                            }
                        }
                    }
                    $np_row['desc']=$cnews;
                    //end--------------------------------------------------------------------------------------------------
                    
                    #hafid imageslider 09-06-2016 --------------------------------------------------------------------------------------
                    //$np_row['desc'] = stripslashes($np_row['desc']);
                    //$np_row['desc'] = image_slider($news);
                    //end images -------------------------------------------------------------------------------------------------------
                    
                    /* Parse Related Quote From Paging [2013-10-28] */
                    if ($related_quotes && is_array($related_quotes)) {
                        foreach ($related_quotes as $pos => $value) {
                            $np_row['desc'] = str_replace("<!--$pos-->", $value, $np_row['desc']);
                        }
                    }
                    /* End Parse Related Quote From Paging [2013-10-28] */

                    $paging_content_main .= $related_quote_html . $paging_top_nav . '
                        <div class="news-headline-image np_image">
                            ' . $np_resource . '
                        </div>
                        ' . $paging_nav . '
                        <div class="ncont">
                            ' . balanceTags($np_row['desc'], true) . '
                            <div class="clear"></div>
                            ' . $paging_btn . '
                            ' . $anak . '
                        </div>
                    ';

                    $np_fileurl = get_paging_url($fileurl, $np_row['no']);
                    $paging_content = $content_top . $paging_nav_js . '
                        <div id="newspaging_content">' . $paging_content_main . '</div> <!-- end of #newspaging_content -->' .
                            $content_bottom;

                    $np_filename = str_replace(BOLAURL, BOLADIR, $np_fileurl);
                    write_file($np_filename, $paging_content, $row['title'] . ' - ' . $np_row['title'], $setKeyword, $description, '', true, 'full', $active_menu, array('schedule' => $row['schedule'], 'amphtml' => true));
                    insert_dfp_interest_segmen($np_filename, $row['dfp_interest']);
                    insert_banner_inImageAds($np_filename);
                    
                    if (!empty($extra_js)) {
                        extra_js($extra_js, $np_filename);
                    }
                
                    if ($message) {
                        echo generated_link($np_fileurl);
                    }
                    insert_property_og($np_filename, $np_row['title'], $np_fileurl, $meta_og_image, '109215469105623', strip_tags($np_row['desc']));
                    write_file_direct(str_replace('.html', '-data.html', $np_filename), $paging_content_main);

                    $prev_url = $np_fileurl;
                }
            }
        }
        
        insert_property_og($filename, $row['title'], $fileurl, $meta_og_image, '109215469105623', strip_tags($row["synopsis"]));
        update_footer_v2($sqlDB, $filename);

        if (in_array($id, array('19651', '19655', '19665', '19667', '19668', '19671', '19677', '19638', '19628', '19618', '71044', '70975', '70974'))) {
            news_inject_socialfb($filename);
        }

        create_print_document($filename, $id, $row['title'], $row['news'], $row['image']);

        // piala dunia
        if ($row['category'] == '7' || $row['category'] == '14' && !defined('THIS_IS_CRON')) {
            if (!function_exists('wc_generate_per_id')) {
                include(FUNCTIONDIR . 'function.worldcup.news.php');
            }
            //wc_generate_per_id($sqlDB, $id);
        }

        // euro
        if ($row['category'] == 8 && !defined('THIS_IS_CRON')) {
            #euro_news_byid($row['idnews'], true, $sqlDB);
        }
        // end euro
        // generate tags, please disable when do generate all
        if (count($generatetags_detail) > 0 && defined('DBDIRECT') && !defined('THIS_IS_CRON')) {
            if (!function_exists('generate_tags_byid')) {
                include(FUNCTIONDIR . 'function.tags.php');
            }

            $generatetags_detail = array_filter(array_unique($generatetags_detail));
            foreach ($generatetags_detail as $vt) {
                // sementara, untuk generate all
                ###generate_tags_byid($sqlDB, $vt);
            }
        }
    }
    $sqlDB->sql_freeresult();
    if ($match_submenu) {
        return $fileurl;
    }
    return true;
}
