<?php
require( 'functions.php' );
date_default_timezone_set( $displaytimezone );
?><!doctype html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css" integrity="sha384-PmY9l28YgO4JwMKbTvgaS7XNZJ30MK9FAZjjzXtlqyZCqBY6X6bXIkM++IkyinN+" crossorigin="anonymous">
	</head>
	<body>
		<div class="container">
			<?php $selectedZoneName = $_GET[ 'zone' ] ?>
			<h1>Current Raids in <select name="zone">
				<option value="">All of Winnipeg</option>
				<?php foreach ( array_keys( $zones ) as $zoneName ): ?>
				<option <?php if ( $zoneName === $selectedZoneName ) { echo 'selected'; } ?>><?php echo $zoneName ?></option>
				<?php endforeach ?>
			</select></h1>
			<?php
			$raids = getCurrentRaids( $selectedZoneName);
			if ( ! empty( $raids ) ):
			?>
			<table class="table table-striped">
				<thead>
					<tr>
						<th>Starts</th>
						<th>Ends</th>
						<th>Level</th>
						<th>Boss</th>
						<th>Gym</th>
						<th>Gym Image</th>
						<th>Gym Control</th>
						<th>Zone</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $raids as $raid ): ?>
					<tr>
						<td><?php echo date( 'h:i', $raid[ 'starts' ] ) ?></td>
						<td><?php echo date( 'h:i', $raid[ 'ends' ] ) ?></td>
						<td><?php echo $raid[ 'level' ] ?></td>
						<td><?php echo $raid[ 'pokemon' ] ?></td>
						<td><a href="https://www.google.ca/maps/search/<?php echo $raid[ 'lat' ] ?>,<?php echo $raid[ 'lon' ] ?>"><?php echo $raid['gym'] ?></a></td>
						<td><img src="<?php echo $raid[ 'url' ] ?>" height="150" /></td>
						<td><?php echo $raid[ 'control' ] ?></td>
						<td><?php echo $raid[ 'zone' ] ?></td>
					</tr>
					<?php endforeach ?>
				</tbody>
			</table>
			<?php else: ?>
			<p>No raids in <?php echo htmlspecialchars( $selectedZoneName ) ?></p>
			<?php endif; ?>
		</div>
		<script>
			document.addEventListener(
			'DOMContentLoaded', function() {
			document.querySelector('select[name="zone"]').onchange=changeZone;
			}, false );

			function changeZone( event ) {
			document.location = '/raid.php?zone=' + encodeURIComponent( event.target.value );
			}
		</script>
	</body>
</html>
