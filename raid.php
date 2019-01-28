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
            <h1>Current Raids in <select name="zone">
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
            // Query Database and Build Raid Billboard
            try {
                $sql = "
            SELECT
                " . getFormattedTimeFromTimestamp( 'raid_battle_timestamp', $dbtimezone, $displaytimezone ) . ",
                " . getFormattedTimeFromTimestamp( 'raid_end_timestamp', $dbtimezone, $displaytimezone ) . ",
                raid_level,
                pokedex.name,
                teamdirectory.name,
                gym.name,
                lat,
                lon,
                url
            FROM gym
                INNER JOIN pokedex
                    ON gym.raid_pokemon_id = pokedex.pokemon_id
                INNER JOIN teamdirectory
                    ON gym.team_id = teamdirectory.team_id
            WHERE raid_pokemon_id IS NOT NULL
                && gym.name IS NOT NULL
            ORDER BY raid_end_timestamp
            ";
                $result = $pdo->query($sql);
                while( $row = $result->fetch() ) {
                    $zoneName = findZoneName( $row['lat'], $row['lon'] );
                    if ( ! $selectedZoneName || $zoneName === $selectedZoneName ) {
                        $raids[] = [
                            'starts' => $row[ 0 ],
                            'ends' => $row[ 1 ],
                            'level' => $row[ 'raid_level' ],
                            'pokemon' => $row[ 3 ],
                            'lat' => $row[ 'lat' ],
                            'lon' => $row[ 'lon' ],
                            'gym' => $row[ 'name' ],
                            'url' => $row[ 'url' ],
                            'control' => $row[ 4 ],
                            'zone' => $zoneName,
                        ];
                    }
                }

                if ( ! empty( $raids ) ): ?>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Raid Starts</th>
                            <th>Raid Ends</th>
                            <th>Raid Level</th>
                            <th>Raid Boss</th>
                            <th>Gym</th>
                            <th>Gym Image</th>
                            <th>Gym Control</th>
                            <th>Zone</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $raids as $raid ): ?>
                        <tr>
                            <td><?php echo $raid[ 'starts' ] ?></td>
                            <td><?php echo $raid[ 'ends' ] ?></td>
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
                <?php
                    // Free result set
                    unset($result);
                else:
                ?>
                    <p>No raids in <?php echo htmlspecialchars( $selectedZoneName ) ?></p>
                <?php
                endif;
            } catch(PDOException $e){
                die("ERROR: Could not able to execute $sql. " . $e->getMessage());
            }
            // Close connection
            unset($pdo);
            ?>
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
