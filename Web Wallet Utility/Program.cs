using System;
using System.Configuration;
using System.IO;
using TurtleCoinAPI;

namespace WebWalletUtility
{
    partial class Program
    {
        static void Main(string[] args)
        {
            if (string.IsNullOrEmpty(ConfigurationManager.AppSettings["HashFile"]))
            {
                AddUpdateAppSettings("HashFile", "c:/wamp/www/walletd.php");
                AddUpdateAppSettings("Daemon", "c:/turtlecoin/turtlecoind.exe");
                AddUpdateAppSettings("Wallet", "c:/turtlecoin/walletd.exe");
                AddUpdateAppSettings("WalletFile", "c:/turtlecoin/webwallet.wallet");
                AddUpdateAppSettings("WalletPassword", "f9suS49ITw3t9jf9sjDFS94jw98FDS3j59DFGSDF2htfdsfgBDFrwfks");
            }
            else
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
                _session.Daemon.InitializeAsync(ConfigurationManager.AppSettings["Daemon"], 11898);
                _session.Daemon.BeginUpdateAsync();
                _session.Wallet.CreateOrInitializeAsync(_session.Daemon, ConfigurationManager.AppSettings["Wallet"],
                    ConfigurationManager.AppSettings["WalletFile"], ConfigurationManager.AppSettings["WalletPassword"], 12321);
                _session.Wallet.BeginUpdateAsync();
                Console.WriteLine("Press any key to save sync progress and exit.");
                Console.ReadKey();
                _session.Exit(true);
            }
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
            File.WriteAllText(ConfigurationManager.AppSettings["HashFile"], PHP);
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

        static void AddUpdateAppSettings(string key, string value)
        {
            try
            {
                var configFile = ConfigurationManager.OpenExeConfiguration(ConfigurationUserLevel.None);
                var settings = configFile.AppSettings.Settings;
                if (settings[key] == null)
                {
                    settings.Add(key, value);
                }
                else
                {
                    settings[key].Value = value;
                }
                configFile.Save(ConfigurationSaveMode.Modified);
                ConfigurationManager.RefreshSection(configFile.AppSettings.SectionInformation.Name);
            }
            catch (ConfigurationErrorsException)
            {
                Console.WriteLine("Error writing app settings");
            }
        }
    }
}
