<?php
require( 'config.php' );
require( 'pointLocation.php' );

$zones = require( 'zones.php' );
$pl = new pointLocation();

function findZoneName( $latitude, $longitude ) {
	global $zones, $pl;

	foreach( $zones as $name => $polygon ) {
		if (
			'outside' !== $pl->pointInPolygon(
				"$latitude $longitude",
				$polygon
			)
		) {
			return $name;
		}
	}

	return 'Unknown';
}

function getCurrentRaids( $selectedZoneName=null ) {
	global $dbhost, $dbuser, $dbpass, $dbname;

	// Establish connection to database
	try {
		$pdo = new PDO(
			"mysql:host=$dbhost;dbname=$dbname",
			$dbuser,
			$dbpass
		);

		// Set the PDO error mode to exception
		$pdo->setAttribute(
			PDO::ATTR_ERRMODE,
			PDO::ERRMODE_EXCEPTION
		);
	} catch ( PDOException $e ) {
		die( "ERROR: Could not connect. " . $e->getMessage() );
	}

	// Query Database and Build Raid Billboard
	try {
		$sql = "
		SELECT
			raid_battle_timestamp,
			raid_end_timestamp,
			raid_level,
			pokedex.name AS pokemon,
			teamdirectory.name AS control,
			gym.name,
			lat,
			lon,
			url
		FROM gym
			INNER JOIN pokedex
				ON gym.raid_pokemon_id = pokedex.pokemon_id
			INNER JOIN teamdirectory
				ON gym.team_id = teamdirectory.team_id
		WHERE
			raid_pokemon_id IS NOT NULL
			&& gym.name IS NOT NULL
			AND raid_end_timestamp > UNIX_TIMESTAMP()
		ORDER BY raid_end_timestamp
		";

		$result = $pdo->query( $sql );

		while( $row = $result->fetch() ) {
			$zoneName = findZoneName( $row['lat'], $row['lon'] );
			if ( ! $selectedZoneName || $zoneName === $selectedZoneName ) {
				$raids[] = [
					'starts' => $row[ 'raid_battle_timestamp' ],
					'ends' => $row[ 'raid_end_timestamp' ],
					'level' => $row[ 'raid_level' ],
					'pokemon' => $row[ 'pokemon' ],
					'lat' => $row[ 'lat' ],
					'lon' => $row[ 'lon' ],
					'gym' => $row[ 'name' ],
					'url' => $row[ 'url' ],
					'control' => $row[ 'control' ],
					'zone' => $zoneName,
				];
			}
		}

		unset( $result );
	} catch ( PDOException $e ) {
		die( "ERROR: Could not able to execute $sql. " . $e->getMessage() );
	}

	unset( $pdo );

	return $raids;
}
