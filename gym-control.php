<!doctype html>
<html lang="en">
    <head>
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css" integrity="sha384-PmY9l28YgO4JwMKbTvgaS7XNZJ30MK9FAZjjzXtlqyZCqBY6X6bXIkM++IkyinN+" crossorigin="anonymous">
    </head>
    <body>
        <div class="container">
            <?php
            require( 'config.php' );
            require( 'pointLocation.php' );

            $zones = require( 'zones.php' );
            $pl = new pointLocation();
            function findZoneName( $latitude, $longitude ) {
                global $zones, $pl;

                foreach( $zones as $name => $polygon ) {
                    if ( 'outside' !== $pl->pointInPolygon( "$latitude $longitude", $polygon ) ) {
                        return $name;
                    }
                }

                return 'Unknown';
            }

            $selectedZoneName = $_GET[ 'zone' ];

            ?>
            <h1>Gym Control in <select name="zone">
                <option value="">All of Winnipeg</option>
                <?php foreach ( array_keys( $zones ) as $zoneName ): ?>
                <option <?php if ( $zoneName === $selectedZoneName ) { echo 'selected'; } ?>><?php echo $zoneName ?></option>
                <?php endforeach ?>
            </select></h1>
            <?php
            function getFormattedTimeFromTimestamp( $columnName, $dbtimezone, $displaytimezone ) {
                return "time_format(convert_tz(from_unixtime($columnName), '$dbtimezone', '$displaytimezone'), '%h:%i:%s %p')";
            }

            // Establish connection to database
            try {
                $pdo = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);
                // Set the PDO error mode to exception
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch(PDOException $e) {
                die("ERROR: Could not connect. " . $e->getMessage());
            }
// Query Database and Build Gym Billboard
try 
{
    $sql = "SELECT
    	time_format(convert_tz(from_unixtime(updated), '$dbtimezone', '$displaytimezone'), '%h:%i:%s %p'),
	teamdirectory.name,
	availble_slots,
	pokedex.name,
	gym.name,
	lat,
	lon
from
	gym
	join teamdirectory
		on gym.team_id = teamdirectory.team_id
	join pokedex
		on gym.guarding_pokemon_id = pokedex.pokemon_id
where
	ST_CONTAINS(ST_GEOMFROMTEXT('POLYGON((LAT LONG, LAT LONG, LAT LONG))'), point(gym.lat, gym.lon))
	&& gym.name is not null
order by teamdirectory.name ASC";   
        $result = $pdo->query($sql);
        if($result->rowCount() > 0){
            echo '<table class="table table-striped">';
                echo "<tr>";
                    echo "<th>Last Updated</th>";
                    echo "<th>Controlling Team</th>";
                    echo "<th>Available Slots</th>";
                    echo "<th>Guarding Pokemon</th>";
                    echo "<th>Gym Name</th>";
                    echo "<th>Gym Zone</th>";
                echo "</tr>";
            while($row = $result->fetch()){
		if ( ! $selectedZoneName || findZoneName( $row['lat'], $row['lon'] ) === $selectedZoneName ) {
			echo "<tr>";
			    echo "<td>" . $row[0] . "</td>";
			    echo "<td>" . $row[1] . "</td>";
			    echo "<td>" . $row['availble_slots'] . "</td>";
			    echo "<td>" . $row[3] . "</td>";
			    echo "<td>" . $row['name'] . "</td>";
			    echo "<td>" . findZoneName( $row['lat'], $row['lon'] ) . "</td>";
			echo "</tr>";
		}
            }
            echo "</table>";
// Free result set
        unset($result);
    } else{
        echo "No records matching your query were found.";
    }
} catch(PDOException $e){
    die("ERROR: Could not able to execute $sql. " . $e->getMessage());
}
// Close connection
unset($pdo);
// Credit to Zyakho for the original billboard project
?>
        </div>
        <script>
            document.addEventListener(
                'DOMContentLoaded', function() {
                    document.querySelector('select[name="zone"]').onchange=changeZone;
                }, false );

            function changeZone( event ) {
                document.location = '/gym-control.php?zone=' + encodeURIComponent( event.target.value );
            }
        </script>
    </body>
</html>
