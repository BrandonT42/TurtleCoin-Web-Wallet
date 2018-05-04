using System;
using System.Net;
using System.IO;
using System.Text;
using System.Threading.Tasks;
using Newtonsoft.Json.Linq;

namespace Wallet_Optimizer
{
    class Program
    {
        static string connection = "", password = "", address = "";
        static uint balance = 0;
        static void Main(string[] args)
        {
            if (args.Length < 4) return;
            connection = args[0];
            password = args[1];
            address = args[2];
            balance = uint.Parse(args[3]);
            if (!address.StartsWith("TRTL")) return;
            else if (address.Length != 99) return;
            else optimizeAddess(address);
        }

        static string sendFusionTransaction(string address)
        {
            // Set initial variables
            uint threshold = balance;
            uint bestThreshold = threshold;
		    int optimizable = 0;

            // Loop the estimate while dividing in half to find optimal values
            while (threshold > 10) // 10 = minimum fee (0.1 * 100)
		    {
			    JObject parameters = new JObject
                {
                    ["threshold"] = threshold,
                    ["addresses"] = new JArray
                    {
                        address
                    }
                };

			    SendRequestAsync("estimateFusion", parameters, out JObject estimate);
                if (estimate["result"] == null) break;
                else if ((int)estimate["result"]["fusionReadyCount"] > optimizable)
                {
                    optimizable = (int)estimate["result"]["fusionReadyCount"];
                    bestThreshold = threshold;
                }
                threshold /= 2;
            }

            // Check if wallet is optimizable
            if (optimizable == 0) return "";

            // Send fusion transaction
            JObject fusionparameters = new JObject
            {
                ["anonymity"] = 4, // Default mixin count
                ["threshold"] = bestThreshold,
                ["addresses"] = new JArray
                {
                    address
                }
            };

            try
            {
                SendRequestAsync("sendFusionTransaction", fusionparameters, out JObject Result);
                if (Result["error"] != null) return "";
                else return (string)Result["result"]["transactionHash"];
            }
            catch
            {
                return "";
            }
        }

        static void optimizeAddess(string address)
        {
            Console.WriteLine("Optimizing address " + address);
            int transactions = 0;
            while (true)
            {
                if (sendFusionTransaction(address) == "")
                {
                    Console.WriteLine("Optimization finished");
                    break;
                }
                else
                {
                    transactions++;
                    Console.WriteLine("Performed fusion #{0}", transactions);
                }
            }
        }

        static Task SendRequestAsync(string Method, JObject Params, out JObject Result)
        {
            try
            {
                // Create a POST request
                HttpWebRequest HttpWebRequest = (HttpWebRequest)WebRequest.Create(connection);
                HttpWebRequest.ContentType = "application/json-rpc";
                HttpWebRequest.Method = "POST";

                // Create a JSON request
                JObject JRequest = new JObject();
                if (Params.Count > 0) JRequest["params"] = JObject.FromObject(Params);
                JRequest.Add(new JProperty("jsonrpc", "2.0"));
                JRequest.Add(new JProperty("id", "0"));
                JRequest.Add(new JProperty("method", Method));
                JRequest.Add(new JProperty("password", password));
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

                // Dispose of pieces
                reader.Dispose();
                WebResponse.Dispose();
            }
            catch
            {
                Result = new JObject();
            }

            // Completed
            return Task.CompletedTask;
        }
    }
}
