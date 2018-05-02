using System;
using System.IO;
using TurtleCoinAPI;

namespace WebWalletUtility
{
    partial class Program
    {
        // CONFIGURE LOCAL WALLET VARIABLES HERE
        private const string HashFile = "c:/wamp/www/walletd.php";
        private const string Daemon = "c:/turtlecoin/turtlecoind.exe";
        private const string Wallet = "c:/turtlecoin/walletd.exe";
        private const string WalletFile = "c:/turtlecoin/webwallet.wallet";
        private const string WalletPassword = "f9suS49ITw3t9jf9sjDFS94jw98FDS3j59DFGSDF2htfdsfgBDFrwfks";

        static void Main(string[] args)
        {
            TurtleCoin _session = new TurtleCoin();
            _session.Daemon.RefreshRate = 5000;
            _session.Wallet.RefreshRate = 5000;
            _session.Daemon.OnUpdate += DaemonUpdate;
            _session.Wallet.OnUpdate += WalletUpdate;
            _session.Daemon.Error += DaemonError;
            _session.Wallet.Error += WalletError;
            _session.Daemon.Log += DaemonLog;
            _session.Wallet.Log += WalletLog;
            _session.Wallet.OnConnect += WalletConntect;
            _session.Daemon.InitializeAsync(Daemon, 11898);
            _session.Daemon.BeginUpdateAsync();
            _session.Wallet.CreateOrInitializeAsync(_session.Daemon, Wallet, WalletFile, WalletPassword, 12321);
            _session.Wallet.BeginUpdateAsync();
            Console.ReadKey();
            _session.Exit(true);
        }

        private static void DaemonUpdate(object sender, EventArgs e)
        {
            if (!(sender as Daemon).Synced)
                Console.WriteLine("Daemon:\tSyncing - {0} / {1}", (sender as Daemon).Height, (sender as Daemon).NetworkHeight);
        }

        private static void WalletConntect(object sender, EventArgs e)
        {
            string PHP = "<?php \r\n" +
                "    define(\"WALLET_PASSWORD\", \"" + TurtleCoin.EncodeString((sender as Wallet).Hash) + "\");\r\n" +
                "    define(\"WALLET_URL\", \"http://" + (sender as Wallet).Address + ":" + (sender as Wallet).Port + "/json_rpc\");\r\n" +
                "?>";
            File.WriteAllText(HashFile, PHP);
        }

        private static void DaemonLog(object sender, TurtleCoinLogEventArgs e)
        {
            Console.WriteLine("Daemon:\t" + e.Message);
        }

        private static void WalletLog(object sender, TurtleCoinLogEventArgs e)
        {
            Console.WriteLine("Wallet:\t" + e.Message);
        }

        private static void DaemonError(object sender, TurtleCoinErrorEventArgs e)
        {
            Console.WriteLine("Daemon Error:\t" + e.ErrorCode);
        }

        private static void WalletError(object sender, TurtleCoinErrorEventArgs e)
        {
            Console.WriteLine("Wallet Error\t" + e.ErrorCode);
        }

        private static void WalletUpdate(object sender, EventArgs e)
        {
            if (!(sender as Wallet).Synced)
                Console.WriteLine("Wallet:\tSyncing - {0} / {1}", (sender as Wallet).BlockCount, (sender as Wallet).KnownBlockCount);
        }
    }
}
