<?php
/**
 *
 */
function trending_index($sqlDB, $devel = false)
{
    global $trending_dir, $trending_url, $month_list_ina, $day_list_ina, $cdn_url, $profile_url,
    $headline_media, $headline_media_url, $galeri_url, $image_library_url, $galeri_media_url, 
    $galeri_headline_thumb_media_url,$image_resized,$library_headline,$library_article,$img_lazy_load;
    
    $sqlDB = new sql_db(DBHOST, DBUSER, DBPASS, DBNAME, false, false, "dbhits");
    
    $dir = getAllNewsCat($sqlDB, true);
    
    if (!is_dir($trending_dir))
    {
        mkdir($trending_dir);
    }
    
    //trending memcache connect
    $memcache = new Memcache();
    bola_memcached_connect($memcache);
        
    $first_date = '2012-01-01';
    $date_now = date('Y-m-d');
    for ( $i = 0; $i <= 7; $i++)
    {
        $date = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') - $i, date('Y')));
        list($my, $mm, $md) = explode('-', $date);
        $filename = $trending_dir . $my .'/'. $mm .'/'. $md .'/index.html';
        $filename_url = $trending_url . $my .'/'. $mm .'/'. $md .'/index.html';
        
        $date_left = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') - $i - 1, date('Y')));
        list($dly, $dlm, $dld) = explode('-', $date_left);
        $date_middle = $date;
        $date_right = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') - $i + 1, date('Y')));
        list($dry, $drm, $drd) = explode('-', $date_right);

        if (strtotime($first_date) > strtotime($date_left))
        {
            $prev_link = '<span style="color: #666666;">'.$dld.' '.$month_list_ina[intval($dlm) - 1].' '.$dly.'</span>';
        }
        else
        {
            $prev_link = '<a href="/trending/'.$dly.'/'.$dlm.'/'.$dld.'/">'.$dld.' '.$month_list_ina[intval($dlm) - 1].' '.$dly.'</a>';
        }
        $current_link = '<a href="/trending/'.$my.'/'.$mm.'/'.$md.'/" style="color:#FFFFFF;">'.$md.' '.$month_list_ina[intval($mm) - 1].' '.$my.'</a>';
        if ($date_now == $date)
        {
            $next_link = '<span style="color: #CCCCCC;">'.$drd.' '.$month_list_ina[intval($drm) - 1].' '.$dry.'</span>';
        }
        else
        {
            $next_link = '<a href="/trending/'.$dry.'/'.$drm.'/'.$drd.'/">'.$drd.' '.$month_list_ina[intval($drm) - 1].' '.$dry.'</a>';
        }
        
        if (!is_dir($trending_dir . $my .'/'))
        {
            mkdir($trending_dir . $my .'/');
        }
        if (!is_dir($trending_dir . $my .'/'. $mm .'/'))
        {
            mkdir($trending_dir . $my .'/'. $mm .'/');
        }
        if (!is_dir($trending_dir . $my .'/'. $mm .'/'. $md .'/'))
        {
            mkdir($trending_dir . $my .'/'. $mm .'/'. $md .'/');
        }
        
        $content = '
            <div class="bigcon">
                <div class="bigcon2">
                    <div class="nav">
                        <a href="/" style="text-decoration:none;">HOME</a> &raquo;
                        MOST VIEWED ARTICLES
                    </div>

                    <br/>
                    <style type="text/css">
                        .trending-date {padding-left: 15px;margin:10px 0px 10px -10px;background:url(\''.$cdn_url.'library/i/v2/most-viewed-background.jpg?\') no-repeat ;width:673px;height:46px;font-size:16px;}
                        .trending-date a, .trending-date span { display: block; float: left; width: 210px; height: 15px; padding-top: 12px; text-align: center; font-weight: bold; color: #5372A0;}
                        .trending-date a:hover {text-decoration: underline; color: #5372A0;}
                    </style>
                    
                    <center><a href="'.$trending_url.'"><img src="'.$cdn_url.'library/i/v2/most-viewed-header.jpg" alt=""/></a></center>
                    <div class="fb-toptitle">Berita dengan kunjungan pembaca terbanyak</div>
                    
                    <div class="trending-date">
                        '.$prev_link.$current_link.$next_link.'
                    </div>
        ';
        
        //trending memcache vars
        $trending_content = array();
        $memcahce_name = 'bolanet_trending_'.$date;
        //echo $memcahce_name;
        $hit_list = array();
        //15 hits news
        $qlist = "
            SELECT A.`group`, A.hits, B.title, B.schedule, B.category, B.idnews, B.celebrity, B.url, B.synopsis, B.imageinfo, B.news, B.image_headline
            FROM `dbhits` A LEFT JOIN dbnews B ON A.related_id=B.idnews
            WHERE A.`hits`<>'0'  AND A.`group`='news' AND B.level <> '0' AND (DATE(B.`schedule`)<='$date' AND DATE(B.`schedule`)>=DATE(DATE_SUB('$date',INTERVAL 1 DAY)))
            GROUP BY A.related_id
            ORDER BY A.`hits` DESC LIMIT 15";
        
        $rlist = $sqlDB->sql_query($qlist, true) or die (__LINE__ . ' = '. mysql_error());
        while ($rowlist = $sqlDB->sql_fetchrow($rlist))
        {
            $hit_list[$rowlist['hits']] = $rowlist;
        }
        
        //15 hits article
        $qlist = "
            SELECT A.`group`, A.hits, B.title, B.schedule, B.category, B.idarticle, B.celebrity, B.url, B.synopsis, B.imageinfo, B.news, B.image_headline
            FROM `dbhits` A LEFT JOIN dbarticles B ON A.related_id=B.idarticle
            WHERE A.`hits`<>'0'  AND A.`group`='article' AND B.level <> '0' AND (DATE(B.`schedule`)<='$date' AND DATE(B.`schedule`)>=DATE(DATE_SUB('$date',INTERVAL 1 DAY)))
            GROUP BY A.related_id
            ORDER BY A.`hits` DESC LIMIT 15";
        
        $rlist = $sqlDB->sql_query($qlist, true) or die (__LINE__ . ' = '. mysql_error());
        while ($rowlist = $sqlDB->sql_fetchrow($rlist))
        {
            $hit_list[$rowlist['hits']] = $rowlist;
        }
        //15 hits photonews
        $qlist = "
            SELECT A.`group`, A.hits, B.title, B.schedule, B.category, B.idcat, B.celebrity, B.description, B.image_headline
            FROM `dbhits` A LEFT JOIN dbgallery B ON A.related_id=B.idcat
            WHERE A.`hits`<>'0'  AND A.`group`='photonews' AND B.level <> '0' AND (DATE(B.`schedule`)<='$date' AND DATE(B.`schedule`)>=DATE(DATE_SUB('$date',INTERVAL 1 DAY)))
            GROUP BY A.related_id
            ORDER BY A.`hits` DESC LIMIT 15";
        $rlist = $sqlDB->sql_query($qlist, true) or die (__LINE__ . ' = '. mysql_error());
        while ($rowlist = $sqlDB->sql_fetchrow($rlist))
        {
            $hit_list[$rowlist['hits']] = $rowlist;
        }
        #print_r($hit_list);

        $num = count($hit_list);
        
        if ($date_now == $date && $num < 10)
        {
            $memcahce_name = 'bolanet_trending_'.$date_left;
            //echo $memcahce_name;
            $date_left_backup = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') - 14, date('Y'))); //hanya karena table dipindah

            //15 hits news
            $qlist = "
                SELECT A.`group`, A.hits, B.title, B.schedule, B.category, B.idnews, B.celebrity, B.url, B.synopsis, B.imageinfo, B.news, B.image_headline
                FROM `dbhits` A LEFT JOIN dbnews B ON A.related_id=B.idnews
                WHERE A.`hits`<>'0'  AND A.`group`='news' AND B.level <> '0' AND DATE(B.`schedule`)>='$date_left_backup' AND B.`schedule` <= NOW()
                GROUP BY A.related_id ORDER BY A.`hits` DESC LIMIT 15";
            
            $rlist = $sqlDB->sql_query($qlist, true) or die (__LINE__ . ' = '. mysql_error());
            while ($rowlist = $sqlDB->sql_fetchrow($rlist))
            {
                $hit_list[$rowlist['hits']] = $rowlist;
            }
            //15 hits article
            $qlist = "
                SELECT A.`group`, A.hits, B.title, B.schedule, B.category, B.idarticle, B.celebrity, B.url, B.synopsis, B.imageinfo, B.news, B.image_headline
                FROM `dbhits` A LEFT JOIN dbarticles B ON A.related_id=B.idarticle
                WHERE A.`hits`<>'0'  AND A.`group`='article' AND B.level <> '0' AND DATE(B.`schedule`)>='$date_left_backup' AND B.`schedule` <= NOW()
                GROUP BY A.related_id
                ORDER BY A.`hits` DESC LIMIT 15";
            $rlist = $sqlDB->sql_query($qlist, true) or die (__LINE__ . ' = '. mysql_error());
            while ($rowlist = $sqlDB->sql_fetchrow($rlist))
            {
                $hit_list[$rowlist['hits']] = $rowlist;
            }
            //15 hits photonews
            $qlist = "
                SELECT A.`group`, A.hits, B.title, B.schedule, B.category, B.idcat, B.celebrity, B.description, B.image_headline
                FROM `dbhits` A LEFT JOIN dbgallery B ON A.related_id=B.idcat
                WHERE A.`hits`<>'0'  AND A.`group`='photonews' AND B.level <> '0' AND DATE(B.`schedule`)>='$date_left_backup' AND B.`schedule` <= NOW()
                GROUP BY A.related_id
                ORDER BY A.`hits` DESC LIMIT 15";
            $rlist = $sqlDB->sql_query($qlist, true) or die (__LINE__ . ' = '. mysql_error());
            while ($rowlist = $sqlDB->sql_fetchrow($rlist))
            {
                $hit_list[$rowlist['hits']] = $rowlist;
            }
            
        }
        

        //sorting
        krsort($hit_list);
        //$hit_list = array_slice($hit_list, 0, 15);
        /*
        $q = "
            SELECT B.title, B.schedule, B.category, B.idnews, B.celebrity, A.hits, B.url, B.synopsis, B.imageinfo, B.news, B.image_headline
            FROM `dbhits` A LEFT JOIN dbnews B ON A.related_id=B.idnews
            WHERE A.`hits`<>'0'  AND A.`group`='news' AND (DATE(B.`schedule`)<='$date' AND DATE(B.`schedule`)>=DATE(DATE_SUB('$date',INTERVAL 1 DAY)))
            GROUP BY A.related_id
            ORDER BY A.`hits` DESC LIMIT 20";
        $r = $sqlDB->sql_query($q, true) or die (__LINE__ . ' = '. mysql_error());
        $num = $sqlDB->sql_numrows($r);
        
        if ($date_now == $date && $num < 10)
        {
            $sqlDB->sql_freeresult();
            $q = "
                SELECT B.title, B.schedule, B.category, B.idnews, B.celebrity, A.hits, B.url, B.synopsis, B.imageinfo, B.news
                FROM `dbhits` A LEFT JOIN dbnews B ON A.related_id=B.idnews
                WHERE A.`hits`<>'0'  AND A.`group`='news' AND DATE(B.`schedule`)='$date_left'
                GROUP BY A.related_id ORDER BY A.`hits` DESC LIMIT 20";
            $r = $sqlDB->sql_query($q, true) or die (__LINE__ . ' = '. mysql_error());
            $num = $sqlDB->sql_numrows($r);
            
            $memcahce_name = 'bolanet_trending_'.$date_left;
        }
        */
        
        $limit_show = 15;
        $counter = 1;

        //while ($row = $sqlDB->sql_fetchrow($r))
        foreach($hit_list as $row)
        {
            if ( $counter > $limit_show )
            {
                break;
            }
            
            $day = substr($row['schedule'], 8, 2);
            $month = substr($row['schedule'], 5, 2);
            $year = substr($row['schedule'], 0, 4);
            $bulan = $month_list_ina[intval($month) - 1];
            $hari = $day_list_ina[date('w', strtotime($row['schedule']))];
            $category = '';
            $category_url = '';
            if($row['group'] == 'photonews')
            {
                $row['url'] = str_replace(" ","_",strtolower(trim($row['category']))); 
                $url = $galeri_url . $row['url'] .'.html';
                $category = 'BERITA FOTO';
                $category_url = $galeri_url;
            }
            else
            {
                $url = BOLAURL . $dir[$row['category']][1] .'/'. $row['url'] .'.html';
                $category = strtoupper($dir[$row['category']][0]);
                $category_url = BOLAURL . $dir[$row['category']][1].'/';
            }
            
            $image_show_dir = $headline_media. $row['image_headline']; //str_pad($row['idnews'], 10, '0', STR_PAD_LEFT) .'.jpg';

            if ( !$row['image_headline'] && $row['group'] != 'photonews')
            {
                continue;
            }
            
            //$image_show = $headline_media_url. $row['image_headline']; //str_pad($row['idnews'], 10, '0', STR_PAD_LEFT) .'.jpg';
            #$image_show = $image_resized.'180x90'.$library_headline.$row['image_headline']; // resized image
            $image_show = ( (strlen($row['image_headline']) == 14) ? $image_resized.'180x90'.$library_headline.$row['image_headline'] : $image_resized.'180x90'.$library_article.$row['image_headline'] );
            if($row['group'] == 'article')
            {
                //$image_show = $image_library_url. $row['image_headline'];
                $image_show = $image_resized.'180x90'.$library_article.$row['image_headline']; // resized image
            }
            else if($row['group'] == 'photonews')
            {
                if($row['image_headline'])
                {
                    $image_show = $galeri_headline_thumb_media_url. $row['image_headline'];
                }
                else
                {
                    //$image_show = $galeri_media_url. $row['url'].'/t/index.jpg';
                    $image_show = $image_resized.'180x90/galeri/'.$row['url'].'/t/index.jpg'; // resized image
                }
            }
            
            $image_show = '
                <div class="fb-image">
                    <a target="_blank" href="'.$url.'" class="greenlink">
                        <img class="lazy_loaded" src="'.$img_lazy_load.'" data-src="'.$image_show.'" width="180" height="90"/>
                    </a>
                </div>
            ';
            
            $related_celeb = '';
            if ($row['celebrity'])
            {
                $player_link = getPlayerProfileLink($row['celebrity']);
                $related_celeb = ' | <a href="'.$profile_url . $player_link.'/" class="bluedarklink">'. $row['celebrity'] .'</a>';
            }
            
            $content .= '
                <div class="fb-container">
                    <div class="fb-wrap" style="background-color: #F2F2F2;">
                        <div class="fb-left">
                            '.$image_show.'
                            <div class="fb-child" style="width: 320px;">
                                <div class="fb-day">'.$hari.' '.$day.' '.$bulan.' '.$year.'</div>
                                <div class="fb-title"><a target="_blank" href="'.$url.'" class="bluedarklink"><strong>'.stripslashes($row['title']).'</strong></a></div>
                                <div>
                                <a href="'.$category_url .'" class="bluedarklink">'. $category .'</a>
                                '.$related_celeb.'
                                </div>
                                <div class="clear"></div>
                            </div>
                        </div>
                        <div class="fb-right" style="width: 90px; padding: 0px 5px;">
                            <div class="fb-num" style="font-size: 70px; color: #D0D0D0;">'.$counter.'</div>
                            <!-- <div class="fb-info">views</div> -->
                        </div>
                    </div>
                    <br class="clear"/>
                </div>
            ';
            //<div class="fb-num">'.number_format($row['hits'], 0, '.', ',').'</div>
            $trending_content[] = $row;
            $counter++;
        }
        
        //set trending memcache
        $memcache->set($memcahce_name, serialize($trending_content), false, 1*60*60);
        saveOrGetData('trending/', $memcahce_name, 0, serialize($trending_content) );
            
        $sqlDB->sql_freeresult();

        $content .= '
                <br/>
                <div class="trending-date">
                '.$prev_link.$current_link.$next_link.'
                </div><br/>
                </div>
            </div>
        ';
        
        $metatitle = 'Terpopuler di bola.net tanggal '. $date;
        $metakey = 'Sepak Bola, Foto Unik, Foto Menarik, Cristiano Ronaldo, Wayne Rooney, Lionel Messi, Diego Milito, Kaka, Iniesta, David Villa, Xavi, Xabi Alonso, Sergio Ramos, Mourinho, Zlatan Ibrahimovic, David Beckham, Pato, Steven Gerrard, Frank Lampard, John Terry, Cesc Fabregas, Didier Drogba, Mourinho, Benitez, Guardiola, Arsene Wenger, Alex Ferguson.';
        $metadesc = 'Terpopuler di bola.net tanggal '. $date;
        if ($devel == false)
        {
            write_file($filename, $content, $metatitle, $metakey, $metadesc, '', true, 'noleft', 8);
            insert_property_og($filename, $metatitle, $filename_url, $cdn_url.'library/i/v2/most-viewed-header.jpg', '109215469105623', '', 'TRENDING');
            echo generated_link($filename_url);
        }
        
        
        if ($date_now == $date)
        {
            if ($devel == false)
            {
                write_file($trending_dir .'index.html', $content, $metatitle, $metakey, $metadesc, '', true, 'noleft', 8);
                insert_property_og($trending_dir .'index.html', $metatitle, $trending_url .'index.html', $cdn_url.'library/i/v2/most-viewed-header.jpg', '109215469105623', '', 'TRENDING');
                echo generated_link($trending_url ."index.html");
                trending_popup_banner($sqlDB, $trending_content, $dir);
            }
            else
            {
                $filename = APPSDIR . 'devel/generate/www/trending/index.html';
                $filurl   = APPSURL . 'devel/generate/www/trending/index.html';
                write_file_dev($filename, $content, $metatitle, $metakey, $metadesc, '', true, 'noleft', 8);
                insert_property_og($filename, $metatitle, $trending_url .'index.html', $cdn_url.'library/i/v2/most-viewed-header.jpg', '109215469105623', '', 'TRENDING');
                echo generated_link($filurl);
            }
        }
    }
    $memcache->close();
    $sqlDB->sql_close();
}

//function getAllCategory($sqlDB)
//{
//    $dir = array();
//    static $cache_allscat = array();
//    
//    if (isset($cache_allcat) && count($cache_allcat) > 0)
//    {
//  $dir = unserialize($cache_allcat);
//    }
//    else
//    {
//  $q = "SELECT category_id, category_name, category_url FROM dbcategory WHERE category_status>'0' ORDER BY category_id";
//  $r = $sqlDB->sql_query($q, true) or die (__LINE__ .' : '. mysql_error());
//  while ($row = $sqlDB->sql_fetchrow($r))
//  {
//      $dir[$row['category_id']] = array($row['category_name'], $row['category_url']);
//  }
//  $sqlDB->sql_freeresult();
//  
//  $cache_allcat = serialize($dir);
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

function trending_popup_banner($sqlDB, $rows, $dir)
{
    global $banner_popup_generated_file2, $galeri_url;
    
    if ( !is_array ( $rows ) || count ( $rows ) == 0 )
    {
        echo "false<br/>\n";
        return false;
    }
    
    $record = '';
    $counter = 1;
    foreach ( $rows as $row )
    {
        if($row['group'] == 'photonews')
        {
            $row['url'] = str_replace(" ","_",strtolower(trim($row['category']))); 
            $url = $galeri_url . $row['url'] .'.html';
            $category = 'BERITA FOTO';
            $category_url = $galeri_url;
        }
        else
        {
            $url = BOLAURL . $dir[$row['category']][1] .'/'. $row['url'] .'.html';
            $category = strtoupper($dir[$row['category']][0]);
            $category_url = BOLAURL . $dir[$row['category']][1].'/';
        }
        
        $record .= '<li class="liItem">
             <a href="'. $url .'" target="_blank">
                <div class="number">'. $counter .'</div>
                <div class="liTitle"><strong>'.stripslashes($row['title']).'</strong></div>
                <div class="fbl"><div class="fb-like" data-href="'. $url .'" data-send="false" data-layout="button_count" data-width="450" data-show-faces="true"></div></div>
                <div style="clear:both;"></div>
             </a>
            </li>';
            
        if ( $counter >= 7 ) break;
        
        $counter++;
    }
    
    $content = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd> 
<html xmlns:fb="http://www.facebook.com/2008/fbml">
<head>
    <title>Join komunitas Bola.net di Facebook</title>
<style type="text/css">
    body {}
    .content { width: 800px; margin: 10px 50px; }
    .sparator { background-color: #888888; width: 100%; height: 5px; }
    .pastpop { 
        border: 1px solid black; 
        padding: 5px;
        background-color: black;
        padding-bottom: 30px;
        width: 800px;
    }
    .isian { 
        background-color:#BEBEBE; 
        border-top: 5px solid #DEDEDE; 
        border-left: 5px solid #CECECE; 
        border-right: 5px solid #9E9E9E; 
        border-bottom: 5px solid #7E7E7E; 
        padding: 10px; 
        border-radius: 25px; 
        -moz-border-radius: 25px; 
        -webkit-border-radius: 25px; 
        width: 600px;
    }
    .poparea {
        padding: 10px;
        background-color: white;
        moz-border-radius: 10px;
        -moz-box-shadow: 0px 0px 2px rgba(0,0,0,0.5);
        -webkit-border-radius: 10px;
        -webkit-box-shadow: 0px 0px 2px rgba(0,0,0,0.5);
        width: 530px;
    }
    .pastpop h3 {
        color: white;
    }
    .poptitle { clear: both; overflow: hidden;}
    .popkey {
        float :right;
        font-family: helvetica, arial, sans-serif;
        font-size: 12px !important;
        color: #555;
        line-height: 1.55em;
        padding: 0px 10px 15px 10px;
    }
    .button_refresh {
        margin-left: 30px;
        padding: 3px 9px;
        border: 0;
        background-color: #AEAEAE;
        cursor:pointer;
        display:inline-block;
        color: #000 !important;
    }
    .button_refresh:hover {
        background-color: #AAA;
        cursor:pointer;
    }
    
    /* box 1 */
    
    .mostView{
        list-style:none;
        padding-left: 5px;
        padding-right: 5px;
    }
    .liHeader{
        background-color: #9BCC01;
        font-size: 30px;
        color: white;
        font-family: arial;
        font-weight: bold;
        padding: 5px;
    }
    .liItem{
        background-color: #E4E4E4;
        border-bottom: solid 1px #CCC;
    }
    .liItem:hover{
        background-color: #f9f9f9;
        -webkit-box-shadow: 0px 1px 10px rgba(50, 50, 50, 0.5);
        -moz-box-shadow:    0px 1px 10px rgba(50, 50, 50, 0.5);
        box-shadow:         0px 1px 10px rgba(50, 50, 50, 0.5);
        -webkit-transition: all 0.20s ease-in-out;
        -moz-transition: all 0.20s ease-in-out;
        -ms-transition: all 0.20s ease-in-out;
        -o-transition: all 0.20s ease-in-out;
        transition: all 0.20s ease-in-out;
        z-index:100;
        position: relative;
    }
    .liItem:hover .number{
        color: #9BCC01;
    }
    .number{
        float:left;
        float: left;
        font-size: 36px;
        color: #B3B3B3;
        font-family: arial;
        font-weight: bold;
        width: 50px;
        text-align: center;
    }
    .liTitle{
        float: left;
        width: 372px;
        text-align: left;
        font-size: 14px;
        font-weight: bold;
        font-family: arial;
        color: #2A2A2A;
        padding-top: 5px;
    }
    .fbl{
        float:left;
        width: 92px;
        padding-top: 12px;
    }
    
    
    /* box 2 */
    
    
    .liHeader2{
        background-color: #9BCC01;
        font-size: 30px;
        color: #2d821d;
        font-family: arial;
        font-weight: bold;
        padding: 5px;
    }
    .liItem2{
        background-color: #E4E4E4;
        height: 50px;
    }
    .liBox{
        width: 510px;
    }
    .liBox:hover{
        background-color: #9BCC01;
        -webkit-box-shadow: 0px 1px 10px rgba(50, 50, 50, 0.5);
        -moz-box-shadow:    0px 1px 10px rgba(50, 50, 50, 0.5);
        box-shadow:         0px 1px 10px rgba(50, 50, 50, 0.5);
        -webkit-transition: all 0.20s ease-in-out;
        -moz-transition: all 0.20s ease-in-out;
        -ms-transition: all 0.20s ease-in-out;
        -o-transition: all 0.20s ease-in-out;
        transition: all 0.20s ease-in-out;
        z-index:100;
        position: relative;
    }
    .liBox:hover .number2{
        color: #FFFFFF;
    }
    .number2{
        float:left;
        float: left;
        font-size: 36px;
        
        color: #9BCC01;
        font-family: arial;
        font-weight: bold;
        width: 50px;
        text-align: center;
    }
    .liTitle2{
        float: left;
        width: 310px;
        text-align: left;
        font-size: 14px;
        font-weight: bold;
        font-family: arial;
        color: #2A2A2A;
        padding-top: 5px;
        padding-left: 6px;
    }
    .liImg{
        float:left;
        padding-top: 2px;
    }
</style>    
</head>
<body style="margin: 0px; padding: 0px;">
<div style="background-color: #FFFFFF; height: 395px; overflow: hidden;">
    <center>
        <div id="fb-root"></div>
        <script>(function(d, s, id) {
          var js, fjs = d.getElementsByTagName(s)[0];
          if (d.getElementById(id)) return;
          js = d.createElement(s); js.id = id;
          js.src = "//connect.facebook.net/id_ID/all.js#xfbml=1";
          fjs.parentNode.insertBefore(js, fjs);
        }(document, \'script\', \'facebook-jssdk\'));</script>
        
        <ul class="mostView">
            <li class="liHeader">Most Viewed Articles</li>
            '.$record.'
        </ul>
    </center>
</div>
</body>
</html>';
    if (file_put_contents($banner_popup_generated_file2, $content))
    {
        echo "sukses<br/>\n";
    }
    else
    {
        echo "<b>gagal!</b><br/>\n";
    }    
}



function trending_index_all($sqlDB,$max_date='',$min_date='')
{
    global $trending_dir, $trending_url, $month_list_ina, $day_list_ina, $cdn_url, $profile_url,
    $headline_media, $headline_media_url, $galeri_url, $image_library_url, $galeri_media_url, 
    $galeri_headline_thumb_media_url,$image_resized, $library_article, $library_headline;
    
    $dir = getAllNewsCat($sqlDB, true);
    
    if (!is_dir($trending_dir))
    {
        mkdir($trending_dir);
    }
    
    //trending memcache connect
    $memcache = new Memcache();
    bola_memcached_connect($memcache);
        
    $date_now = date('Y-m-d');
    $first_date = '2012-06-28';
    $max_date = ( !(empty($max_date)) ? $max_date : '2016-03-15');
    $min_date = ( !(empty($min_date)) ? $min_date : '2012-06-28'); 
    
    $_count_ = 0;
    while (strtotime($max_date) > strtotime($min_date))
    {
        $_count_++;
        if($_count_ == 100)
        {
            usleep(1000);
        }
        
        $max_date = date("Y-m-d", strtotime("-1 day", strtotime($max_date)));    
       
        $date = $max_date;
        list($my, $mm, $md) = explode('-', $date);
        $filename = $trending_dir . $my .'/'. $mm .'/'. $md .'/index.html';
        $filename_url = $trending_url . $my .'/'. $mm .'/'. $md .'/index.html';
        
        $date_left = date("Y-m-d", strtotime("-1 day", strtotime($date)));
        list($dly, $dlm, $dld) = explode('-', $date_left);
        $date_middle = $date;
        $date_right = date("Y-m-d", strtotime("+1 day", strtotime($date)));
        list($dry, $drm, $drd) = explode('-', $date_right);

        if (strtotime($first_date) > strtotime($date_left))
        {
            $prev_link = '<span style="color: #666666;">'.$dld.' '.$month_list_ina[intval($dlm) - 1].' '.$dly.'</span>';
        }
        else
        {
            $prev_link = '<a href="/trending/'.$dly.'/'.$dlm.'/'.$dld.'/">'.$dld.' '.$month_list_ina[intval($dlm) - 1].' '.$dly.'</a>';
        }
        $current_link = '<a href="/trending/'.$my.'/'.$mm.'/'.$md.'/" style="color:#FFFFFF;">'.$md.' '.$month_list_ina[intval($mm) - 1].' '.$my.'</a>';
        if ($date_now == $date)
        {
            $next_link = '<span style="color: #CCCCCC;">'.$drd.' '.$month_list_ina[intval($drm) - 1].' '.$dry.'</span>';
        }
        else
        {
            $next_link = '<a href="/trending/'.$dry.'/'.$drm.'/'.$drd.'/">'.$drd.' '.$month_list_ina[intval($drm) - 1].' '.$dry.'</a>';
        }
        
        if (!is_dir($trending_dir . $my .'/'))
        {
            mkdir($trending_dir . $my .'/');
        }
        if (!is_dir($trending_dir . $my .'/'. $mm .'/'))
        {
            mkdir($trending_dir . $my .'/'. $mm .'/');
        }
        if (!is_dir($trending_dir . $my .'/'. $mm .'/'. $md .'/'))
        {
            mkdir($trending_dir . $my .'/'. $mm .'/'. $md .'/');
        }
        
        $content = '
            <div class="bigcon">
                <div class="bigcon2">
                    <div class="nav">
                        <a href="/" style="text-decoration:none;">HOME</a> &raquo;
                        MOST VIEWED ARTICLES
                    </div>
                    
                    <br/>
                    <style type="text/css">
                        .trending-date {padding-left: 15px;margin:10px 0px 10px -10px;background:url(\''.$cdn_url.'library/i/v2/most-viewed-background.jpg?\') no-repeat ;width:673px;height:46px;font-size:16px;}
                        .trending-date a, .trending-date span { display: block; float: left; width: 210px; height: 15px; padding-top: 12px; text-align: center; font-weight: bold; color: #5372A0;}
                        .trending-date a:hover {text-decoration: underline; color: #5372A0;}
                    </style>
                    
                    <center><a href="'.$trending_url.'"><img src="'.$cdn_url.'library/i/v2/most-viewed-header.jpg" alt=""/></a></center>
                    <div class="fb-toptitle">Berita dengan kunjungan pembaca terbanyak</div>
                    
                    <div class="trending-date">
                        '.$prev_link.$current_link.$next_link.'
                    </div>
        ';
        
        //trending memcache vars
        $trending_content = array();
        $memcahce_name = 'bolanet_trending_'.$date;
        
        $hit_list = array();
        //15 hits news
        $qlist = "
            SELECT A.`group`, A.hits, B.title, B.schedule, B.category, B.idnews, B.celebrity, B.url, B.synopsis, B.imageinfo, B.news, B.image_headline
            FROM `dbhits` A LEFT JOIN dbnews B ON A.related_id=B.idnews
            WHERE A.`hits`<>'0'  AND A.`group`='news' AND (DATE(B.`schedule`)<='$date' AND DATE(B.`schedule`)>=DATE(DATE_SUB('$date',INTERVAL 1 DAY)))
            GROUP BY A.related_id
            ORDER BY A.`hits` DESC LIMIT 15";
        $rlist = $sqlDB->sql_query($qlist, true) or die (__LINE__ . ' = '. mysql_error());
        while ($rowlist = $sqlDB->sql_fetchrow($rlist))
        {
            $hit_list[$rowlist['hits']] = $rowlist;
        }
        //15 hits article
        $qlist = "
            SELECT A.`group`, A.hits, B.title, B.schedule, B.category, B.idarticle, B.celebrity, B.url, B.synopsis, B.imageinfo, B.news, B.image_headline
            FROM `dbhits` A LEFT JOIN dbarticles B ON A.related_id=B.idarticle
            WHERE A.`hits`<>'0'  AND A.`group`='article' AND (DATE(B.`schedule`)<='$date' AND DATE(B.`schedule`)>=DATE(DATE_SUB('$date',INTERVAL 1 DAY)))
            GROUP BY A.related_id
            ORDER BY A.`hits` DESC LIMIT 15";
        $rlist = $sqlDB->sql_query($qlist, true) or die (__LINE__ . ' = '. mysql_error());
        while ($rowlist = $sqlDB->sql_fetchrow($rlist))
        {
            $hit_list[$rowlist['hits']] = $rowlist;
        }
        //15 hits photonews
        $qlist = "
            SELECT A.`group`, A.hits, B.title, B.schedule, B.category, B.idcat, B.celebrity, B.description, B.image_headline
            FROM `dbhits` A LEFT JOIN dbgallery B ON A.related_id=B.idcat
            WHERE A.`hits`<>'0'  AND A.`group`='photonews' AND (DATE(B.`schedule`)<='$date' AND DATE(B.`schedule`)>=DATE(DATE_SUB('$date',INTERVAL 1 DAY)))
            GROUP BY A.related_id
            ORDER BY A.`hits` DESC LIMIT 15";
        $rlist = $sqlDB->sql_query($qlist, true) or die (__LINE__ . ' = '. mysql_error());
        while ($rowlist = $sqlDB->sql_fetchrow($rlist))
        {
            $hit_list[$rowlist['hits']] = $rowlist;
        }
        
        $num = count($hit_list);
        if ($date_now == $date && $num < 10)
        {
            $memcahce_name = 'bolanet_trending_'.$date_left;
            $date_left_backup = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') - 14, date('Y'))); //hanya karena table dipindah
            
            //15 hits news
            $qlist = "
                SELECT A.`group`, A.hits, B.title, B.schedule, B.category, B.idnews, B.celebrity, B.url, B.synopsis, B.imageinfo, B.news, B.image_headline
                FROM `dbhits` A LEFT JOIN dbnews B ON A.related_id=B.idnews
                WHERE A.`hits`<>'0'  AND A.`group`='news' AND DATE(B.`schedule`)>='$date_left_backup' AND B.`schedule`<NOW()
                GROUP BY A.related_id
                ORDER BY A.`hits` DESC LIMIT 15";
            $rlist = $sqlDB->sql_query($qlist, true) or die (__LINE__ . ' = '. mysql_error());
            while ($rowlist = $sqlDB->sql_fetchrow($rlist))
            {
                $hit_list[$rowlist['hits']] = $rowlist;
            }
            //15 hits article
            $qlist = "
                SELECT A.`group`, A.hits, B.title, B.schedule, B.category, B.idarticle, B.celebrity, B.url, B.synopsis, B.imageinfo, B.news, B.image_headline
                FROM `dbhits` A LEFT JOIN dbarticles B ON A.related_id=B.idarticle
                WHERE A.`hits`<>'0'  AND A.`group`='article' AND DATE(B.`schedule`)>='$date_left_backup' AND B.`schedule`<NOW()
                GROUP BY A.related_id
                ORDER BY A.`hits` DESC LIMIT 15";
            $rlist = $sqlDB->sql_query($qlist, true) or die (__LINE__ . ' = '. mysql_error());
            while ($rowlist = $sqlDB->sql_fetchrow($rlist))
            {
                $hit_list[$rowlist['hits']] = $rowlist;
            }
            //15 hits photonews
            $qlist = "
                SELECT A.`group`, A.hits, B.title, B.schedule, B.category, B.idcat, B.celebrity, B.description, B.image_headline
                FROM `dbhits` A LEFT JOIN dbgallery B ON A.related_id=B.idcat
                WHERE A.`hits`<>'0'  AND A.`group`='photonews' AND DATE(B.`schedule`)>='$date_left_backup' AND B.`schedule`<NOW()
                GROUP BY A.related_id
                ORDER BY A.`hits` DESC LIMIT 15";
            $rlist = $sqlDB->sql_query($qlist, true) or die (__LINE__ . ' = '. mysql_error());
            while ($rowlist = $sqlDB->sql_fetchrow($rlist))
            {
                $hit_list[$rowlist['hits']] = $rowlist;
            }
        }
        
        //sorting
        krsort($hit_list);
        //$hit_list = array_slice($hit_list, 0, 15);
        /*
        $q = "
            SELECT B.title, B.schedule, B.category, B.idnews, B.celebrity, A.hits, B.url, B.synopsis, B.imageinfo, B.news, B.image_headline
            FROM `dbhits` A LEFT JOIN dbnews B ON A.related_id=B.idnews
            WHERE A.`hits`<>'0'  AND A.`group`='news' AND (DATE(B.`schedule`)<='$date' AND DATE(B.`schedule`)>=DATE(DATE_SUB('$date',INTERVAL 1 DAY)))
            GROUP BY A.related_id
            ORDER BY A.`hits` DESC LIMIT 20";
        $r = $sqlDB->sql_query($q, true) or die (__LINE__ . ' = '. mysql_error());
        $num = $sqlDB->sql_numrows($r);
        
        if ($date_now == $date && $num < 10)
        {
            $sqlDB->sql_freeresult();
            $q = "
                SELECT B.title, B.schedule, B.category, B.idnews, B.celebrity, A.hits, B.url, B.synopsis, B.imageinfo, B.news
                FROM `dbhits` A LEFT JOIN dbnews B ON A.related_id=B.idnews
                WHERE A.`hits`<>'0'  AND A.`group`='news' AND DATE(B.`schedule`)='$date_left'
                GROUP BY A.related_id ORDER BY A.`hits` DESC LIMIT 20";
            $r = $sqlDB->sql_query($q, true) or die (__LINE__ . ' = '. mysql_error());
            $num = $sqlDB->sql_numrows($r);
            
            $memcahce_name = 'bolanet_trending_'.$date_left;
        }
        */
        
        $limit_show = 15;
        $counter = 1;
        //while ($row = $sqlDB->sql_fetchrow($r))
        foreach($hit_list as $row)
        {
            if ( $counter > $limit_show )
            {
                break;
            }
    
            $day = substr($row['schedule'], 8, 2);
            $month = substr($row['schedule'], 5, 2);
            $year = substr($row['schedule'], 0, 4);
            $bulan = $month_list_ina[intval($month) - 1];
            $hari = $day_list_ina[date('w', strtotime($row['schedule']))];
            $category = '';
            $category_url = '';
            
            if($row['group'] == 'photonews')
            {
                $row['url'] = str_replace(" ","_",strtolower(trim($row['category']))); 
                $url = $galeri_url . $row['url'] .'.html';
                $category = 'BERITA FOTO';
                $category_url = $galeri_url;
            }
            else
            {
                $url = BOLAURL . $dir[$row['category']][1] .'/'. $row['url'] .'.html';
                $category = strtoupper($dir[$row['category']][0]);
                $category_url = BOLAURL . $dir[$row['category']][1].'/';
            }
            
            $image_show_dir = $headline_media. $row['image_headline']; //str_pad($row['idnews'], 10, '0', STR_PAD_LEFT) .'.jpg';
            if ( !$row['image_headline'] && $row['group'] != 'photonews')
            {
                continue;
            }
            
            $image_show = ( (strlen($row['image_headline']) == 14) ? $image_resized.'180x90'.$library_headline.$row['image_headline'] : $image_resized.'180x90'.$library_article.$row['image_headline'] );
            if($row['group'] == 'article')
            {
                //$image_show = $image_library_url. $row['image_headline'];
                $image_show = $image_resized.'180x90'.$library_article.$row['image_headline']; // resized image
            }
            else if($row['group'] == 'photonews')
            {
                if($row['image_headline'])
                {
                    $image_show = $galeri_headline_thumb_media_url. $row['image_headline'];
                }
                else
                {
                    $image_show = $galeri_media_url. $row['url'].'/t/index.jpg';
                }
            }
            
            $image_show = '
                <div class="fb-image">
                    <a target="_blank" href="'.$url.'" class="greenlink">
                        <img src="'.$image_show.'" alt="" style="width:180px;"/>
                    </a>
                </div>
            ';
            
            $related_celeb = '';
            if ($row['celebrity'])
            {
                $player_link = getPlayerProfileLink($row['celebrity']);
                $related_celeb = ' | <a href="'.$profile_url . $player_link.'/" class="bluedarklink">'. $row['celebrity'] .'</a>';
            }
            
            $content .= '
                <div class="fb-container">
                    <div class="fb-wrap" style="background-color: #F2F2F2;">
                        <div class="fb-left">
                            '.$image_show.'
                            <div class="fb-child" style="width: 320px;">
                                <div class="fb-day">'.$hari.' '.$day.' '.$bulan.' '.$year.'</div>
                                <div class="fb-title"><a target="_blank" href="'.$url.'" class="bluedarklink"><strong>'.stripslashes($row['title']).'</strong></a></div>
                                <div>
                                <a href="'.$category_url .'" class="bluedarklink">'. $category .'</a>
                                '.$related_celeb.'
                                </div>
                                <div class="clear"></div>
                            </div>
                        </div>
                        <div class="fb-right" style="width: 90px; padding: 0px 5px;">
                            <div class="fb-num" style="font-size: 70px; color: #D0D0D0;">'.$counter.'</div>
                            <!-- <div class="fb-info">views</div> -->
                        </div>
                    </div>
                    <br class="clear"/>
                </div>
            ';
            //<div class="fb-num">'.number_format($row['hits'], 0, '.', ',').'</div>
            $trending_content[] = $row;
            $counter++;
        }
        
        //set trending memcache
//        $memcache->set($memcahce_name, serialize($trending_content), false, 1*60*60);
            
        $sqlDB->sql_freeresult();

        $content .= '
                <br/>
                <div class="trending-date">
                '.$prev_link.$current_link.$next_link.'
                </div><br/>
                </div>
            </div>
        ';
        
        $metatitle = 'Terpopuler di bola.net tanggal '. $date;
        $metakey = 'Sepak Bola, Foto Unik, Foto Menarik, Cristiano Ronaldo, Wayne Rooney, Lionel Messi, Diego Milito, Kaka, Iniesta, David Villa, Xavi, Xabi Alonso, Sergio Ramos, Mourinho, Zlatan Ibrahimovic, David Beckham, Pato, Steven Gerrard, Frank Lampard, John Terry, Cesc Fabregas, Didier Drogba, Mourinho, Benitez, Guardiola, Arsene Wenger, Alex Ferguson.';
        $metadesc = 'Terpopuler di bola.net tanggal '. $date;
        write_file($filename, $content, $metatitle, $metakey, $metadesc, '', true, 'noleft', 8);
        insert_property_og($filename, $metatitle, $filename_url, $cdn_url.'library/i/v2/most-viewed-header.jpg', '109215469105623', 'TRENDING');
        echo generated_link($filename_url);
        
        if ($date_now == $date)
        {
            write_file($trending_dir .'index.html', $content, $metatitle, $metakey, $metadesc, '', true, 'noleft', 8);
            insert_property_og($trending_dir .'index.html', $metatitle, $trending_url .'index.html', $cdn_url.'library/i/v2/most-viewed-header.jpg', '109215469105623', 'TRENDING');
            echo generated_link($trending_url ."index.html");
            trending_popup_banner($sqlDB, $trending_content, $dir);
        }
    }
    $memcache->close();
}


?>