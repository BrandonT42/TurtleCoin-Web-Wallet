using System;
using System.Collections.Generic;
using System.Net;
using System.Net.Sockets;
using System.Text;
using System.Threading.Tasks;

namespace TurtleCoinAPI
{
    public partial class Daemon
    {
        /// <summary>
        /// Throws an error
        /// </summary>
        /// <param name="e">Error code</param>
        private void ThrowError(ErrorCode e)
        {
            if (e == ErrorCode.BAD_CONNECTION) Connected = false;
            Error?.Invoke(this, new TurtleCoinErrorEventArgs { Time = DateTime.Now, ErrorCode = e });
        }

        /// <summary>
        /// Logs a message
        /// </summary>
        /// <param name="Input">String to log</param>
        private void LogLine(string Input, params object[] args)
        {
            Log?.Invoke(this, new TurtleCoinLogEventArgs { Time = DateTime.Now, Message = String.Format(Input, args) });
        }
    }

    /// <summary>
    /// Network info utility class
    /// </summary>
    public struct NetworkInfo
    {
        
    }

    /// <summary>
    /// Recent blocks utility class
    /// </summary>
    public struct RecentBlocks
    {
        public string Height { get; set; }
        public string Date { get; set; }
        public string Size { get; set; }
        public string Hash { get; set; }
        public string Difficulty { get; set; }
        public string Transactions { get; set; }
    }

    /// <summary>
    /// Transaction pool utility class
    /// </summary>
    public struct TransactionPool
    {
        public string Amount { get; set; }
        public string Fee { get; set; }
        public string Size { get; set; }
        public string Hash { get; set; }
    }
}
