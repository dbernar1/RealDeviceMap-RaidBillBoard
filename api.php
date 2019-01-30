<?php
require( 'functions.php' );

header('Content-Type: application/json');

echo json_encode( getCurrentRaids() );
