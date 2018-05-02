using System;
using System.Diagnostics;
using System.Threading;

namespace TurtleCoinAPI
{
    public partial class Wallet
    {
        // Path to wallet application
        public string Path { get; private set; }
        public string File { get; private set; }
        public string Address { get; private set; }

        // Integers
        public int Port { get; private set; }
        public int RefreshRate { get; set; }

        // Connection status
        public bool Connected { get; private set; }
        public bool Local { get; private set; }

        // Process to run local daemon on
        private Process
            Process = new Process();

        // Event handlers
        public EventHandler<TurtleCoinLogEventArgs> Log;
        public EventHandler<TurtleCoinErrorEventArgs> Error;
        public EventHandler
            OnConnect,
            OnDisconnect,
            OnUpdate,
            OnSynced;

        // For canceling async loops
        private CancellationTokenSource
            CancellationSource = new CancellationTokenSource();

        // For capturing console output from daemon process
        private string
            WalletOutput = "";

        // Daemon to hook to
        private Daemon
            Daemon = new Daemon();

        // Wallet variables
        public bool Synced { get; private set; }
        public double PeerCount { get; private set; }
        public double BlockCount { get; private set; }
        public string LastBlockHash { get; private set; }
        public double KnownBlockCount { get; private set; }
        public double AvailableBalance { get; private set; }
        public double LockedAmount { get; private set; }

        // Internal variables
        private string InternalHash { get; set; }
        private string Password { get; set; }
    }
}
