# RaidBillBoard
Billboard to show active raids sorted by which raids will end first

# Install
```
git clone https://github.com/Zyahko/RealDeviceMap-RaidBillBoard
cp raid.php to your directory root for your site (i.e. /var/www/site/)
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
