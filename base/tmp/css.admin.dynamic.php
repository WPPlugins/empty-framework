/**
 * @author Valentin Alisch <hallo@valentinalisch.de>
 * @version 2.5.8
 */
 
 
 
<?php 
$breaks = json_decode( EMPTY_BREAKS );

foreach ( $breaks as $breakpoint ) { 
?>
/* Breakpoint: <?php echo $breakpoint->slug; ?> */
[data-empty_breakpoints_current="<?php echo $breakpoint->slug; ?>"] [data-breakpoint="<?php echo $breakpoint->slug; ?>"] {
	display: inline-block;
}



<?php } ?>