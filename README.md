# RaidBillBoard
Billboard to show active raids sorted by which raids will end first or show gym control in a geofence sorted by team control.

# Install
```
git clone https://github.com/darthbutcher/RealDeviceMap-RaidBillBoard.git
cp gym-control.php to your directory root for gym control billboard or 
cp raid.pho to your directory root for raid billboard 
(i.e. /var/www/site/)
```
# raid.php
Configure the database variables to match what is needed to access your RealDeviceMap Database
You can alter the column names by changing what is between the <th></th> tags

# gym-control.php
Configure the database variables to match what is needed to access your RealDeviceMap Database. Insert a geofence with the followin schema: (LAT LONG, LAT LONG, LAT LONG) in the appropriate section of the query.

You can alter the column names by changing what is between the <th></th> tags

# db_tables.sql

Copy and paste these queries into your SQL DB to create the appropriate database files for Guarding Pokemon and Team Name.

# create_pokedex.sql - This is deprecated. Do not use.
