To setup, you need to copy everything from Web into your web root folder.

You also need a copy of the latest turtlecoind and walletd binaries somewhere on your system.

You need a MySQL database running, edit Core.php with the corresponding credentials.

Run Database Setup.sql from the Setup folder on the database you will be using to create the needed table.

Compile and run WebWalletUtility.exe once so it generates a WebWalletUtility.exe.config file. (It should automatically close after creating the file.)

Edit the file with your correspoding variables - Daemon is the path to turtlecoind.exe or a node IP/host address, Wallet is the path to walletd.exe, WalletFile is the local path to the container you will be using, WalletPassword is the password for that container, and HashFile is the path to the walletd.php file in your web root folder.

Make sure port 80 is open for external connections.

I think that's it. Run it all, I guess.
