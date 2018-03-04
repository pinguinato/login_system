<?php include('includes/header.php'); ?>
<?php include('includes/nav.php'); ?>



	<div class="jumbotron">
		<?php
		//var_dump($_SESSION);
		?>
		<h1 class="text-center"><?php
		if(!$_SESSION){
			echo "HOME";
		}else{
			echo display_message();
		}
		?>
	 </h1>
	</div>


	<?php

	// test funzioni db

/*

	$sql = "SELECT * FROM users";
	$result = query($sql);
	confirm($result);
	$row = fetch_data($result);
	// test
	echo $row['username'];

	$numrow = row_count($result);

	echo "Numero di righe selezionate = ".$numrow;


	echo "<br>TOKEN GENERATO = ".token_generator();

*/

	?>


<?php include('includes/footer.php'); ?>
