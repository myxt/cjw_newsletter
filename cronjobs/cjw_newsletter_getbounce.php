<?php

/**
 * Cronjob cjw_newsletter_getbounce.php
 */

$message = "START: get_bounces";
$cli->output( $message );
CjwNewsletterMailbox::collectMailsFromActiveMailboxes();
CjwNewsletterMailbox::parseActiveMailboxItems();
$message = "END: get_bounces";
$cli->output( $message );

?>
