using System;
using System.Diagnostics;
using System.Threading;

namespace TurtleCoinAPI
{
    public partial class Daemon
    {
        // Daemon path (local or remote)
        public string Path { get; private set; }
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
            DaemonOutput = "";

        // Network variables
        public double AltBlocksCount { get; private set; }
        public double Difficulty { get; private set; }
        public double GreyPeerlistSize { get; private set; }
        public double Hashrate { get; private set; }
        public double Height { get; private set; }
        public double IncomingConnectionsCount { get; private set; }
        public double LastKnownBlockIndex { get; private set; }
        public double NetworkHeight { get; private set; }
        public double OutgoingConnectionsCount { get; private set; }
        public string Status { get; private set; }
        public bool Synced { get; private set; }
        public double TransactionCount { get; private set; }
        public double TransactionPoolSize { get; private set; }
        public double WhitePeerlistSize { get; private set; }
    }
}
