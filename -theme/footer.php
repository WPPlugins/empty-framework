<?php
/**
 * @author Valentin Alisch <hallo@valentinalisch.de>
 * @version 2.5.8
 */
 


global $brk_sm;
?>


				</div>
			</main>
			
			<footer id="page-footer">
				<div class="wrap">
					<div class="row">
						<div class="row-inner">
							<nav id="navigation-footer" class="col-<?php echo $brk_sm; ?>-12">
								<div class="trigger"></div>
								<?php 
								wp_nav_menu( array(
									'container'			=> false,
									'fallback_cb'		=> false,
									'theme_location'		=> 'navigation-footer'
								) );
								?>
							</nav>
						</div>
					</div>
				</div>
			</footer>
		</div>
		
		<?php wp_footer(); ?>
		
	</body>
</html>