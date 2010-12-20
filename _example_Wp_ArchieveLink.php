<?php
/**************************************************************************************************
 * basic example of using Wp_ArchieveLink class
 **************************************************************************************************/
$Wp_ArchieveLink = new Wp_ArchieveLink;
$Wp_ArchieveLink->init();
$Wp_ArchieveLink->set_type('monthly');
$Wp_ArchieveLink->set_echo(FALSE);
$output_archieve = $Wp_ArchieveLink->get_archives();
/*
 * bila archieve ada isinya maka tolong diberi html container
 */
if ($output_archieve ) {
    $output_archieve  = '<ul class="Wp_ArchieveLink">' . $output_archieve  . '</ul>';
}

/**************************************************************************************************
 * example of using Wp_ArchieveLink class
 * to return looping based on yearly archieves
 **************************************************************************************************/
//include('D:/xampp/htdocs/myblog/personal/uwiuw/version/class/Wp_ArchieveLink.php');
$Wp_ArchieveLink = new Wp_ArchieveLink;
$Wp_ArchieveLink->init();
$Wp_ArchieveLink->set_target_url(get_site_url() . "/" . get_query_var('year'));
$Wp_ArchieveLink->set_type('yearly');
$Wp_ArchieveLink->set_paginationfilepath('D:\xampp\htdocs\myblog\personal\uwiuw\version\class\external\pagination.php');
$output_archieve = $Wp_ArchieveLink->get_YearlyArhievesPosts(TRUE); ?>

<!-- Adding example css than can be use to style the archive layout of Wp_ArchieveLink class -->
<style>
    .wp_archievelink {
        width:600px;
    }
    .wp_archievelink li {
        background-color:antiquewhite;
        display:block;
        margin:4px 0;
    }
    .wp_archievelink div {
        display:inline-block;
    }
    .wp_archievelink div.year {
        padding:4px;
    }
    .wp_archievelink .haspost, .wp_archievelink .nopost {
        padding:2px 4px;
    }
    .wp_archievelink .haspost {
        background-color:#fff;
    }
</style>

<?php
    foreach ($output_archieve['main_loop'] as $post) {
        /***
         * setup_postdata() is a function that convert an array of post into a standar
         * wordpress looping consist of posts. The advantages of this conversition is
         * now we're able to use any wp function that can be use in a loop
         */
        $standar_post = setup_postdata($post);
    }