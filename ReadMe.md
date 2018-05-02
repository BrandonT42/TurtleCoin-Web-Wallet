To setup, you need to copy everything from Web into your web root folder.

You also need a copy of the latest turtlecoind and walletd binaries.

Edit the utility application source code with the corresponding turtlecoind and walletd paths as well as your wallet file and password.

The HashFile should be your web root folder plus "walletd.php", such as "c:/wamp/www/walletd.php"

You need a MySQL database running, edit Core.php with the corresponding credentials.

Run Database Setup.sql from the Setup folder on the database you will be using to create the needed table.

Make sure port 80 is open for external connections.

I think that's it.
