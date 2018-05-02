using Newtonsoft.Json.Linq;
using System;
using System.Diagnostics;
using System.IO;
using System.Net;
using System.Text;
using System.Threading;
using System.Threading.Tasks;

namespace TurtleCoinAPI
{
    public partial class Wallet
    {
        /// <summary>
        /// Initializes a daemon connection
        /// </summary>
        /// <param name="Path">Path to daemon (local path or node address)</param>
        /// <param name="Port">Port daemon will connect through</param>
        public Task InitializeAsync(Daemon Daemon, string Path, string File, string Password, int Port = 8070)
        {
            // Set local variables
            this.Daemon = Daemon;
            this.Path = Path;
            this.File = File;
            this.Password = Password;
            this.Port = Port;

            LogLine("Initializing");

            // Ensure daemon is connected
            if (Daemon.Connected)
                Connected = true;
            else
            {
                LogLine("Daemon not connected");
                ThrowError(ErrorCode.DAEMON_NOT_CONNECTED);
                return Task.CompletedTask;
            }

            // Check if given path is local or remote
            string ConvertedPath;
            try
            {
                // If path starts with http, it is external
                if (Path.StartsWith("http"))
                {
                    ConvertedPath = Path.Replace("http:", "").Replace("\\", "").Replace("/", "");
                    Local = false;
                }
                else
                {
                    ConvertedPath = Path;
                    if (IPAddress.TryParse(Path, out IPAddress i)) Local = false;
                    else Local = new Uri(Path).IsFile;
                }
            }
            catch
            {
                ThrowError(ErrorCode.INVALID_PATH);
                return Task.CompletedTask;
            }

            // If path is local
            if (Local)
            {
                // Set address
                Address = "localhost";
                LogLine("Set address to {0}", Address);

                // Check if port is already in use
                if (TurtleCoin.Ping(Address, Port))
                {
                    ThrowError(ErrorCode.PORT_IN_USE);
                    return Task.CompletedTask;
                }

                // Launch wallet locally
                LogLine("Creating process");
                if (!System.IO.File.Exists(ConvertedPath))
                {
                    ThrowError(ErrorCode.INVALID_FILE);
                    return Task.CompletedTask;
                }

                // Create wallet process
                Process = new Process();
                Process.StartInfo.FileName = ConvertedPath;
                Process.StartInfo.UseShellExecute = false;
                Process.StartInfo.RedirectStandardInput = true;
                Process.StartInfo.RedirectStandardOutput = true;
                Process.StartInfo.RedirectStandardError = true;

                // Assign event handlers
                Process.OutputDataReceived += WalletOutputDataReceived;
                Process.ErrorDataReceived += WalletErrorDataReceived;
                Process.Exited += WalletExited;

                // Create process arguments
                Process.StartInfo.Arguments = string.Format("--daemon-address {0} --daemon-port {1} -w \"{2}\" -p \"{3}\" --bind-port {4} --rpc-password \"{5}\"",
                    Daemon.Address, Daemon.Port, TurtleCoin.EncodeString(File), TurtleCoin.EncodeString(Password), Port, TurtleCoin.EncodeString(Hash));

                // Start process
                if (Process.Start())
                {
                    // Begin redirecting output
                    Process.BeginOutputReadLine();
                    Process.BeginErrorReadLine();

                    // Trigger wallet connected event
                    Connected = true;
                    OnConnect?.Invoke(this, EventArgs.Empty);
                }

                // Process failed to start
                else ThrowError(ErrorCode.PROCESS_NOT_CREATED);
            }

            // Completed
            return Task.CompletedTask;
        }

        /// <summary>
        /// Initializes a daemon connection
        /// </summary>
        /// <param name="Path">Path to daemon (local path or node address)</param>
        /// <param name="Port">Port daemon will connect through</param>
        public Task CreateOrInitializeAsync(Daemon Daemon, string Path, string File, string Password, int Port = 8070)
        {
            // Set local variables
            this.Daemon = Daemon;
            this.Path = Path;
            this.File = File;
            this.Password = Password;
            this.Port = Port;

            LogLine("Initializing");

            // Ensure daemon is connected
            if (Daemon.Connected)
                Connected = true;
            else
            {
                LogLine("Daemon not connected");
                ThrowError(ErrorCode.DAEMON_NOT_CONNECTED);
                return Task.CompletedTask;
            }

            // Check if given path is local or remote
            string ConvertedPath;
            try
            {
                // If path starts with http, it is external
                if (Path.StartsWith("http"))
                {
                    ConvertedPath = Path.Replace("http:", "").Replace("\\", "").Replace("/", "");
                    Local = false;
                }
                else
                {
                    ConvertedPath = Path;
                    if (IPAddress.TryParse(Path, out IPAddress i)) Local = false;
                    else Local = new Uri(Path).IsFile;
                }
            }
            catch
            {
                ThrowError(ErrorCode.INVALID_PATH);
                return Task.CompletedTask;
            }

            // If path is local
            if (Local)
            {
                // Set address
                Address = "localhost";
                LogLine("Set address to {0}", Address);

                // Check if port is already in use
                if (TurtleCoin.Ping(Address, Port))
                {
                    ThrowError(ErrorCode.PORT_IN_USE);
                    return Task.CompletedTask;
                }

                // Launch wallet locally
                LogLine("Creating process");

                ProcessCreation:
                // Create wallet process
                Process = new Process();
                Process.StartInfo.FileName = ConvertedPath;
                Process.StartInfo.UseShellExecute = false;
                Process.StartInfo.RedirectStandardInput = true;
                Process.StartInfo.RedirectStandardOutput = true;
                Process.StartInfo.RedirectStandardError = true;

                // Assign event handlers
                Process.OutputDataReceived += WalletOutputDataReceived;
                Process.ErrorDataReceived += WalletErrorDataReceived;
                Process.Exited += WalletExited;

                // Create process arguments
                Process.StartInfo.Arguments = string.Format("--daemon-address {0} --daemon-port {1} -w \"{2}\" -p \"{3}\" --bind-port {4} --rpc-password \"{5}\"",
                    Daemon.Address, Daemon.Port, TurtleCoin.EncodeString(File), TurtleCoin.EncodeString(Password), Port, TurtleCoin.EncodeString(Hash));
                if (!System.IO.File.Exists(File))
                    Process.StartInfo.Arguments += " --generate-container";

                // Start process
                if (Process.Start())
                {
                    // A bit hacky but saves wallet and reopens the process if a wallet was created
                    if (Process.StartInfo.Arguments.Contains("--generate-container"))
                    {
                        Process.WaitForExit();
                        goto ProcessCreation;
                    }

                    // Opening pre-existing wallet
                    else
                    {
                        // Begin redirecting output
                        Process.BeginOutputReadLine();
                        Process.BeginErrorReadLine();

                        // Trigger wallet connected event
                        Connected = true;
                        OnConnect?.Invoke(this, EventArgs.Empty);
                    }
                }

                // Process failed to start
                else ThrowError(ErrorCode.PROCESS_NOT_CREATED);
            }

            // Completed
            return Task.CompletedTask;
        }

        /// <summary>
        /// Stops updating and cleans up
        /// </summary>
        public async Task Exit(bool ForceExit = false)
        {
            // Clean up
            Connected = false;
            Synced = false;
            CancellationSource.Cancel();
            if (Local && !Process.HasExited)
            {
                LogLine("Saving");
                await SendRequestAsync(RequestMethod.SAVE, new JObject { }, out JObject Result);
                if (ForceExit) Process.Kill();
            }
        }

        /// <summary>
        /// Begins the daemon update loop
        /// </summary>
        public Task BeginUpdateAsync()
        {
            // Begin updating
            if (Connected)
            {
                LogLine("Update started");
                Update();
            }
            else ThrowError(ErrorCode.NOT_INITIALIZED);

            // Completed
            return Task.CompletedTask;
        }

        /// <summary>
        /// Main update loop
        /// </summary>
        private async void Update()
        {
            // Loop as long as the wallet is connected
            while (Connected)
            {
                try
                {
                    // Ensure network connection is still alive
                    if (!Local && !TurtleCoin.Ping(Address, Port))
                    {
                        // Connection was lost
                        await Exit();
                        ThrowError(ErrorCode.CONNECTION_LOST);
                        LogLine("Connection lost");
                        OnDisconnect?.Invoke(this, EventArgs.Empty);
                        break;
                    }
                    else if (Local && Process.HasExited)
                    {
                        // Connection was lost
                        await Exit();
                        ThrowError(ErrorCode.CONNECTION_LOST);
                        LogLine("Connection lost");
                        OnDisconnect?.Invoke(this, EventArgs.Empty);
                        break;
                    }

                    // Create a result object to recycle throughout updates
                    JObject Result = new JObject();

                    // Update status
                    await SendRequestAsync(RequestMethod.GET_STATUS, new JObject { }, out Result);
                    PeerCount = (double)Result["peerCount"];
                    BlockCount = (double)Result["blockCount"];
                    LastBlockHash = (string)Result["lastBlockHash"];
                    KnownBlockCount = (double)Result["knownBlockCount"];

                    // Check if wallet is synced
                    if (!Synced && Daemon.Synced && BlockCount >= Daemon.NetworkHeight - 2) // Fuzzy sync status
                    {
                        // Set ready status
                        Synced = true;

                        // Trigger ready event handler
                        LogLine("Synced");
                        OnSynced?.Invoke(this, EventArgs.Empty);
                    }
                    else Synced = false;

                    // Update balance
                    await SendRequestAsync(RequestMethod.GET_BALANCE, new JObject { }, out Result);
                    AvailableBalance = (double)Result["availableBalance"] / 100;
                    LockedAmount = (double)Result["lockedAmount"] / 100;

                    // Invoke update event
                    OnUpdate?.Invoke(this, EventArgs.Empty);

                    // Wait for specified amount of time
                    await Task.Delay(RefreshRate, CancellationSource.Token);
                }
                catch { }
            }
        }

        /// <summary>
        /// Sends a request to daemon
        /// </summary>
        /// <param name="Method">The method the request is using</param>
        /// <param name="Params">The parameters to pass in the request</param>
        public Task SendRequestAsync(RequestMethod Method, JObject Params, out JObject Result)
        {
            try
            {
                // Create a POST request
                HttpWebRequest HttpWebRequest = (HttpWebRequest)WebRequest.Create("http://" + Address + ":" + Port + "/json_rpc");
                HttpWebRequest.ContentType = "application/json-rpc";
                HttpWebRequest.Method = "POST";

                // Create a JSON request
                JObject JRequest = new JObject();
                if (Params.Count > 0) JRequest["params"] = JObject.FromObject(Params);
                JRequest.Add(new JProperty("jsonrpc", "2.0"));
                JRequest.Add(new JProperty("id", "0"));
                JRequest.Add(new JProperty("method", Method.ToString()));
                JRequest.Add(new JProperty("password", TurtleCoin.EncodeString(Hash)));
                String Request = JRequest.ToString();

                // Send bytes to server
                byte[] ByteArray = Encoding.UTF8.GetBytes(Request);
                HttpWebRequest.ContentLength = ByteArray.Length;
                Stream Stream = HttpWebRequest.GetRequestStream();
                Stream.Write(ByteArray, 0, ByteArray.Length);
                Stream.Close();

                // Receive reply from server
                WebResponse WebResponse = HttpWebRequest.GetResponse();
                StreamReader reader = new StreamReader(WebResponse.GetResponseStream(), Encoding.UTF8);

                // Get response
                Result = JObject.Parse(reader.ReadToEnd());
                if (Result["result"] != null) Result = (JObject)Result["result"];
                else ThrowError(ErrorCode.BAD_REQUEST);

                // Dispose of pieces
                reader.Dispose();
                WebResponse.Dispose();
            }
            catch
            {
                Result = new JObject();
                ThrowError(ErrorCode.BAD_REQUEST);
            }

            // Completed
            return Task.CompletedTask;
        }

        /// <summary>
        /// Event triggered when wallet process outputs data
        /// </summary>
        private void WalletOutputDataReceived(object sender, DataReceivedEventArgs e)
        {
            // Return if data received if empty
            if (String.IsNullOrEmpty(e.Data)) return;

            // Add data to internal log
            WalletOutput += e.Data;
        }

        /// <summary>
        /// Event triggered when wallet process outputs data
        /// </summary>
        private void WalletErrorDataReceived(object sender, DataReceivedEventArgs e)
        {
            // Return if data received if empty
            if (String.IsNullOrEmpty(e.Data)) return;

            // Add data to internal log
            LogLine("Wallet error: {0}", e.Data);
        }

        /// <summary>
        /// Event triggered when wallet process has exited
        /// </summary>
        private void WalletExited(object sender, EventArgs e)
        {
            // Trigger wallet disconnected event
            Connected = false;
            OnDisconnect?.Invoke(this, EventArgs.Empty);
        }
    }
}
