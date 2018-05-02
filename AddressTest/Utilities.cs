using Newtonsoft.Json.Linq;
using System;
using System.Collections.Generic;
using System.IO;
using System.Net.Sockets;
using System.Security.Cryptography;
using System.Text;
using System.Text.RegularExpressions;
using System.Threading.Tasks;

/*
 * This file contains miscellaneous utility functions
 */
namespace TurtleCoinAPI
{
    public partial class TurtleCoin
    {
        /// <summary>
        /// Encodes a string to pass shell arguments
        /// </summary>
        /// <param name="Input">The string to be encoded</param>
        /// <returns>Encloded input string</returns>
        internal static string EncodeString(string Input)
        {
            string result = "";
            bool enclosedInApo, wasApo;
            string subResult;
            enclosedInApo = Input.LastIndexOfAny(
                new char[] { ' ', '\t', '|', '@', '^', '<', '>', '&' }) >= 0;
            wasApo = enclosedInApo;
            subResult = "";
            for (int i = Input.Length - 1; i >= 0; i--)
                switch (Input[i])
                {
                    case '"':
                        subResult = @"\""" + subResult;
                        wasApo = true;
                        break;
                    case '\\':
                        subResult = (wasApo ? @"\\" : @"\") + subResult;
                        break;
                    default:
                        subResult = Input[i] + subResult;
                        wasApo = false;
                        break;
                }
            result += (result.Length > 0 ? " " : "") +
                (enclosedInApo ? "\"" + subResult + "\"" : subResult);
            return result;
        }

        /// <summary>
        /// Pings an address and port to check if it's active
        /// </summary>
        /// <param name="Host">Address to ping</param>
        /// <param name="Port">Port to ping</param>
        /// <returns>True if ping is successful, false if ping fails</returns>
        internal static bool Ping(string Host, int Port)
        {
            // Create disposable TCP client
            using (TcpClient tcpClient = new TcpClient())
                try
                {
                    // Attempt a connection
                    tcpClient.Connect(Host, Port);
                    return true;
                }
                catch (Exception)
                {
                    return false;
                }
        }
    }

    /// <summary>
    /// Error event arguments
    /// </summary>
    public class TurtleCoinErrorEventArgs : EventArgs
    {
        public DateTime Time { get; set; }
        public ErrorCode ErrorCode { get; set; }
        public TurtleCoinErrorEventArgs() { }
    }

    /// <summary>
    /// Log event arguments
    /// </summary>
    public class TurtleCoinLogEventArgs : EventArgs
    {
        public DateTime Time { get; set; }
        public string Message { get; set; }
    }

    /// <summary>
    /// Error codes
    /// </summary>
    public enum ErrorCode
    {
        PORT_IN_USE,
        BAD_CONNECTION,
        CONNECTION_LOST,
        INVALID_FILE,
        PROCESS_NOT_CREATED,
        NOT_INITIALIZED,
        INCORRECT_DAEMON_VERSION,
        INVALID_PATH,
        NO_IPV4,
        PACKET_RECEIVE_ERROR,
        BAD_REQUEST,
        DAEMON_NOT_CONNECTED
    }

    /// <summary>
    /// Request methods
    /// </summary>
    public sealed class RequestMethod
    {
        // Daemon Requests
        public static readonly RequestMethod GET_INFO = new RequestMethod("getinfo");
        public static readonly RequestMethod GET_BLOCK_COUNT = new RequestMethod("getblockcount");
        public static readonly RequestMethod GET_BLOCK_HASH = new RequestMethod("getblockhash");
        public static readonly RequestMethod GET_BLOCK_TEMPLATE = new RequestMethod("getblocktemplate");
        public static readonly RequestMethod SUBMIT_BLOCK = new RequestMethod("submitblock");
        public static readonly RequestMethod GET_LAST_BLOCK_HEADER = new RequestMethod("getlastblockheader");
        public static readonly RequestMethod GET_BLOCK_HEADER_BY_HASH = new RequestMethod("getblockheaderbyhash");
        public static readonly RequestMethod GET_BLOCK_HEADER_BY_HEIGHT = new RequestMethod("getblockheaderbyheight");
        public static readonly RequestMethod GET_CURRENCY_ID = new RequestMethod("getcurrencyid");

        // Wallet Requests
        public static readonly RequestMethod RESET = new RequestMethod("reset");
        public static readonly RequestMethod SAVE = new RequestMethod("save");
        public static readonly RequestMethod GET_VIEW_KEY = new RequestMethod("getViewKey");
        public static readonly RequestMethod GET_SPEND_KEYS = new RequestMethod("getSpendKeys");
        public static readonly RequestMethod GET_STATUS = new RequestMethod("getStatus");
        public static readonly RequestMethod GET_ADDRESSES = new RequestMethod("getAddresses");
        public static readonly RequestMethod CREATE_ADDRESS = new RequestMethod("createAddress");
        public static readonly RequestMethod DELETE_ADDRESS = new RequestMethod("deleteAddress");
        public static readonly RequestMethod GET_BALANCE = new RequestMethod("getBalance");
        public static readonly RequestMethod GET_BLOCK_HASHES = new RequestMethod("getBlockHashes");
        public static readonly RequestMethod GET_TRANSACTION_HASHES = new RequestMethod("getTransactionHashes");
        public static readonly RequestMethod GET_TRANSACTIONS = new RequestMethod("getTransactions");
        public static readonly RequestMethod GET_UNCONFIRMED_TRANSACTION_HASHES = new RequestMethod("getUnconfirmedTransactionHashes");
        public static readonly RequestMethod GET_TRANSACTION = new RequestMethod("getTransaction");
        public static readonly RequestMethod SEND_TRANSACTION = new RequestMethod("sendTransaction");
        public static readonly RequestMethod CREATE_DELAYED_TRANSACTION = new RequestMethod("createDelayedTransaction");
        public static readonly RequestMethod GET_DELAYED_TRANSACTION_HASHES = new RequestMethod("getDelayedTransactionHashes");
        public static readonly RequestMethod DELETE_DELAYED_TRANSACTION = new RequestMethod("deleteDelayedTransaction");
        public static readonly RequestMethod SEND_DELAYED_TRANSACTION = new RequestMethod("sendDelayedTransaction");
        public static readonly RequestMethod SEND_FUSION_TRANSACTION = new RequestMethod("sendFusionTransaction");
        public static readonly RequestMethod ESTIMATE_FUSION = new RequestMethod("estimateFusion");

        // Declarations
        private readonly string Method;
        public RequestMethod(string Method)
        {
            this.Method = Method;
        }
        public override string ToString()
        {
            return Method;
        }
    }
}
