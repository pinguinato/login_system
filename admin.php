<?php include('includes/header.php'); ?>
<?php include('includes/nav.php'); ?>
	<div class="jumbotron">
		<h1 class="text-center">
			<?php

			if(logged_in()){
					echo "SEI LOGGATO";
			}else{
				redirect("index.php"); // toi riporta alla pagina di index
			}
			?>
		</h1>
	</div>
<?php include("includes/footer.php"); ?>
