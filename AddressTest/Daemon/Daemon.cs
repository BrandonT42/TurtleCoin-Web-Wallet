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
    public partial class Daemon
    {
        /// <summary>
        /// Initializes a daemon connection
        /// </summary>
        /// <param name="Path">Path to daemon (local path or node address)</param>
        /// <param name="Port">Port daemon will connect through</param>
        public Task InitializeAsync(string Path, int Port = 11898)
        {
            // Set local variables
            this.Path = Path;
            this.Port = Port;

            LogLine("Initializing");

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

                // Launch daemon locally
                LogLine("Creating process");
                if (!File.Exists(ConvertedPath))
                {
                    ThrowError(ErrorCode.INVALID_FILE);
                    return Task.CompletedTask;
                }

                // Create daemon process
                Process = new Process();
                Process.StartInfo.FileName = ConvertedPath;
                Process.StartInfo.UseShellExecute = false;
                Process.StartInfo.RedirectStandardInput = true;
                Process.StartInfo.RedirectStandardOutput = true;
                Process.StartInfo.RedirectStandardError = true;

                // Assign event handlers
                Process.OutputDataReceived += DaemonOutputDataReceived;
                Process.ErrorDataReceived += DaemonErrorDataReceived;
                Process.Exited += DaemonExited;

                // Start process
                if (Process.Start())
                {
                    // Begin redirecting output
                    Process.BeginOutputReadLine();
                    Process.BeginErrorReadLine();

                    // Wait for connection
                    LogLine("Waiting on ready");
                    while (!Connected)
                    {
                        SendRequestAsync(RequestMethod.GET_INFO, new JObject(), out JObject Result);
                        if (Result["height"] != null)
                            Connected = true;
                    }

                    // Trigger daemon connected event
                    OnConnect?.Invoke(this, EventArgs.Empty);
                }

                // Process failed to start
                else ThrowError(ErrorCode.PROCESS_NOT_CREATED);
            }

            // If path is remote node
            else
            {
                // Set address
                Address = ConvertedPath;
                LogLine("Set address to {0}", Address);

                // Check if a connection can be made
                if (!TurtleCoin.Ping(Address, Port))
                {
                    ThrowError(ErrorCode.BAD_CONNECTION);
                    return Task.CompletedTask;
                }

                // Trigger daemon connection event
                Connected = true;
                LogLine("Connected");
                OnConnect?.Invoke(this, EventArgs.Empty);
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
            try
            {
                if (Local && !Process.HasExited)
                    using (StreamWriter StreamWriter = Process.StandardInput)
                    {
                        await StreamWriter.WriteLineAsync("exit");
                        if (ForceExit)
                            Process.Kill();
                        LogLine("Saved");
                    }
            }
            catch { }
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
            // Loop as long as the daemon is connected
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
                    await SendRequestAsync(RequestMethod.GET_INFO, new JObject { }, out Result);

                    // Make sure daemon is correct version
                    if (Result["synced"] == null)
                    {
                        // Incorrect daemon version
                        await Exit();
                        ThrowError(ErrorCode.INCORRECT_DAEMON_VERSION);
                        LogLine("Daemon is wrong version, use a newer version");
                        OnDisconnect?.Invoke(this, EventArgs.Empty);
                        break;
                    }

                    // Populate network info
                    AltBlocksCount = (double)Result["alt_blocks_count"];
                    Difficulty = (double)Result["difficulty"];
                    GreyPeerlistSize = (double)Result["grey_peerlist_size"];
                    Hashrate = (double)Result["hashrate"];
                    Height = (double)Result["height"];
                    IncomingConnectionsCount = (double)Result["incoming_connections_count"];
                    LastKnownBlockIndex = (double)Result["last_known_block_index"];
                    NetworkHeight = (double)Result["network_height"];
                    OutgoingConnectionsCount = (double)Result["outgoing_connections_count"];
                    Status = (string)Result["status"];
                    TransactionCount = (double)Result["tx_count"];
                    TransactionPoolSize = (double)Result["tx_pool_size"];
                    WhitePeerlistSize = (double)Result["white_peerlist_size"];

                    // Check if daemon is ready
                    if (!Synced && (bool)Result["synced"] == true)
                    {
                        // Set ready status
                        Synced = true;

                        // Trigger ready event handler
                        LogLine("Synced");
                        OnSynced?.Invoke(this, EventArgs.Empty);
                    }
                    else Synced = false;

                    // Do updating

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
                // HTTP request
                if (Method == RequestMethod.GET_INFO)
                {
                    // Send request to server
                    using (WebClient client = new WebClient())
                    {
                        String result = client.DownloadString("http://" + Address + ":" + Port + "/" + Method);
                        Result = JObject.Parse(result);
                    }
                }

                // Other request
                else
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
            }
            catch
            {
                Result = new JObject();
                if (Synced) ThrowError(ErrorCode.BAD_REQUEST);
            }

            // Completed
            return Task.CompletedTask;
        }

        /// <summary>
        /// Event triggered when daemon process outputs data
        /// </summary>
        private void DaemonOutputDataReceived(object sender, DataReceivedEventArgs e)
        {
            // Return if data received if empty
            if (String.IsNullOrEmpty(e.Data)) return;

            // Add data to internal log
            DaemonOutput += e.Data;
        }

        /// <summary>
        /// Event triggered when daemon process outputs data
        /// </summary>
        private void DaemonErrorDataReceived(object sender, DataReceivedEventArgs e)
        {
            // Return if data received if empty
            if (String.IsNullOrEmpty(e.Data)) return;

            // Add data to internal log
            LogLine("Daemon error: {0}", e.Data);
        }

        /// <summary>
        /// Event triggered when daemon process has exited
        /// </summary>
        private void DaemonExited(object sender, EventArgs e)
        {
            // Trigger daemon disconnected event
            Connected = false;
            OnDisconnect?.Invoke(this, EventArgs.Empty);
        }
    }
}
