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