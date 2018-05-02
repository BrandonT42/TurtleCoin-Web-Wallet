using System;
using System.Collections.Generic;
using System.IO;
using System.Security.Cryptography;
using System.Text;
using System.Text.RegularExpressions;

namespace TurtleCoinAPI
{
    public partial class Wallet
    {
        /// <summary>
        /// Throws an error
        /// </summary>
        /// <param name="e">Error code</param>
        /// <returns></returns>
        private void ThrowError(ErrorCode e)
        {
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

        /// <summary>
        /// Returns a unique session hash
        /// </summary>
        public string Hash
        {
            get
            {
                if (InternalHash == null)
                {
                    Random random = new Random(Guid.NewGuid().GetHashCode());
                    const string pool = "abcdefghijklmnopqrstuvwxyz0123456789";
                    var builder = new StringBuilder();
                    for (var i = 0; i < 256; i++)
                    {
                        var c = pool[random.Next(0, pool.Length)];
                        builder.Append(c);
                    }
                    InternalHash = builder.ToString();
                }
                return InternalHash;
            }
        }

        /// <summary>
        /// Encrypts a string using the runtime hash
        /// </summary>
        /// <param name="Input">The string to be encrypted</param>
        /// <returns>Encrypted input string</returns>
        private string EncryptString(string Input)
        {
            HashAlgorithm algorithm = new SHA256Managed();
            byte[] b = Encoding.ASCII.GetBytes(Input);
            byte[] s = Encoding.ASCII.GetBytes(Hash);
            byte[] p = new byte[b.Length + s.Length];
            for (int i = 0; i < b.Length; i++)
                p[i] = b[i];
            for (int i = 0; i < s.Length; i++)
                p[b.Length + i] = s[i];
            return Convert.ToBase64String(algorithm.ComputeHash(p));
        }
    }

    /// <summary>
    /// Transaction utility class
    /// </summary>
    public class Transaction
    {
        public TransactionType Type { get; set; }
        public string Hash { get; set; }
        public string State { get; set; }
        public int TimeStamp { get; set; }
        public int UnlockTime { get; set; }
        public double Amount { get; set; }
        public double Fee { get; set; }
        public string Extra { get; set; }
        public string PaymentID { get; set; }
        public string BlockIndex;
        public List<Transfer> Transfers;

        public Transaction(string BlockIndex, string Hash, string State, int TimeStamp, int UnlockTime, int Amount,
            int Fee, string Extra, string PaymentID, List<Transfer> Transfers)
        {
            this.BlockIndex = BlockIndex;
            this.Hash = Hash;
            this.State = State;
            this.TimeStamp = TimeStamp;
            this.UnlockTime = UnlockTime;
            this.Amount = Amount / 100;
            this.Fee = Fee / 100;
            this.PaymentID = PaymentID;
            this.Transfers = Transfers;
            this.Extra = Extra;

            if (this.Amount > 0) Type = TransactionType.IN;
            else Type = TransactionType.OUT;
        }
        public Transaction() { }
    }

    /// <summary>
    /// Transfer utility class
    /// </summary>
    public class Transfer
    {
        public string Address { get; set; }
        private double InternalAmount;
        public double Amount
        {
            get
            {
                return InternalAmount / 100;
            }
            set
            {
                InternalAmount = value * 100;
            }
        }

        public Transfer(string Address, double Amount)
        {
            this.Address = Address;
            InternalAmount = Amount;
        }
        public Transfer() { }
    }

    /// <summary>
    /// Transfer type
    /// </summary>
    public enum TransactionType
    {
        IN,
        OUT
    }
}
