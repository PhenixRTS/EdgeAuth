using Newtonsoft.Json;
using Newtonsoft.Json.Linq;
using System;
using System.Security.Cryptography;
using System.Text;

namespace PhenixRTS.EdgeAuth
{
    /// <summary>
    /// Digest token helper functions.
    /// </summary>
    public sealed class DigestTokens
    {
        public sealed class VerifyAndDecodeResult
        {
            private readonly bool _isVerified;
            private readonly ECode _code;
            private readonly JObject _value;

            public VerifyAndDecodeResult(ECode code)
            {
                _isVerified = false;
                _code = code;
                _value = null;
            }

            public VerifyAndDecodeResult(JObject value)
            {
                _isVerified = true;
                _code = ECode.VERIFIED;
                _value = value;
            }

            /// <summary>
            /// Checks if the result is verified.
            /// </summary>
            /// <returns>True, if the token is verified</returns>
            public bool IsVerified()
            {
                return _isVerified;
            }

            /// <summary>
            /// Get the verification result.
            /// </summary>
            /// <returns>The error code if verification failed</returns>
            public ECode GetCode()
            {
                return _code;
            }

            /// <summary>
            /// Get the value.
            /// </summary>
            /// <returns>The verified JSON object</returns>
            public JObject GetValue()
            {
                return _value;
            }
        }

        private const string DIGEST_TOKEN_PREFIX = "DIGEST:";
        private const string FIELD_APPLICATION_ID = "applicationId";
        private const string FIELD_DIGEST = "digest";
        private const string FIELD_TOKEN = "token";
        public const string FIELD_EXPIRES = "expires";
        public const string FIELD_URI = "uri";

        /// <summary>
        /// Check if a value is a valid digest token.
        /// </summary>
        /// <param name="encodedToken">An encoded token</param>
        /// <returns>True, if the encodedToken is a valid digest token</returns>
        public bool IsDigestToken(string encodedToken)
        {
            return encodedToken != null && encodedToken.StartsWith(DIGEST_TOKEN_PREFIX);
        }

        /// <summary>
        /// Verify and decode an encoded token.
        /// </summary>
        /// <param name="secret">The secret used to encode the token</param>
        /// <param name="encodedToken">The encoded token</param>
        /// <returns>The verification result</returns>
        public VerifyAndDecodeResult VerifyAndDecode(string secret, string encodedToken)
        {
            if (secret == null)
            {
                throw new Exception("Secret must not be null");
            }

            if (encodedToken == null)
            {
                throw new Exception("Encoded token must not be null");
            }

            if (!IsDigestToken(encodedToken))
            {
                return new VerifyAndDecodeResult(ECode.NOT_A_DIGEST_TOKEN);
            }

            string encodedDigestToken = encodedToken.Substring(DIGEST_TOKEN_PREFIX.Length);
            byte[] decodedAsBytes;

            try
            {
                decodedAsBytes = Convert.FromBase64String(encodedDigestToken);
            }
            catch
            {
                return new VerifyAndDecodeResult(ECode.BAD_TOKEN);
            }

            string decodedAsString = Encoding.UTF8.GetString(decodedAsBytes);
            JObject info;

            try
            {
                try
                {
                    info = JObject.Parse(decodedAsString);
                }
                catch (JsonReaderException)
                {
                    return new VerifyAndDecodeResult(ECode.BAD_TOKEN);
                }

                string[] stringFields = { FIELD_APPLICATION_ID, FIELD_DIGEST, FIELD_TOKEN };

                foreach (string field in stringFields)
                {
                    if (info.ContainsKey(field) && info.GetValue(field).Type == JTokenType.String)
                    {
                        continue;
                    }

                    return new VerifyAndDecodeResult(ECode.BAD_TOKEN);
                }

                string applicationId = info.GetValue(FIELD_APPLICATION_ID).ToString();
                string token = info.GetValue(FIELD_TOKEN).ToString();

                try
                {
                    string digestAsString = CalculateDigest(applicationId, secret, token);
                    string digest = info.GetValue(FIELD_DIGEST).ToString();

                    if (!digestAsString.Equals(digest))
                    {
                        return new VerifyAndDecodeResult(ECode.BAD_DIGEST);
                    }
                }
                catch
                {
                    return new VerifyAndDecodeResult(ECode.UNSUPPORTED);
                }

                JObject value;

                try
                {
                    value = JObject.Parse(token);
                }
                catch (JsonReaderException)
                {
                    return new VerifyAndDecodeResult(ECode.BAD_TOKEN);
                }

                JObject result = new JObject();

                foreach (var property in value)
                {
                    result.Add(property.Key, property.Value);
                }

                result.Add(FIELD_APPLICATION_ID, applicationId);

                return new VerifyAndDecodeResult(result);
            }
            catch
            {
                return new VerifyAndDecodeResult(ECode.BAD_TOKEN);
            }
        }

        /// <summary>
        /// Signs and encodes a digest token.
        /// </summary>
        /// <param name="applicationId">The application ID used to sign the token</param>
        /// <param name="secret">The shared secret used to sign the token</param>
        /// <param name="token">The raw token object to sign</param>
        /// <returns>The signed and encoded digest token</returns>
        public string SignAndEncode(string applicationId, string secret, JObject token)
        {
            if (applicationId == null)
            {
                throw new Exception("Application ID must not be null");
            }

            if (secret == null)
            {
                throw new Exception("Secret must not be null");
            }

            if (token == null)
            {
                throw new Exception("Token must not be null");
            }

            if (!token.ContainsKey(FIELD_EXPIRES) || token.GetValue(FIELD_EXPIRES).Type != JTokenType.Integer)
            {
                throw new Exception("Token must have an expiration (milliseconds since UNIX epoch)");
            }

            if (token.ContainsKey(FIELD_APPLICATION_ID))
            {
                throw new Exception("Token should not have an application ID property");
            }

            string tokenAsString;

            try
            {
                tokenAsString = token.ToString().Replace("\r", "").Replace("\n", "").Replace(" ", "");
            }
            catch
            {
                throw new Exception("Unable to convert token to string");
            }

            string digest;

            try
            {
                digest = CalculateDigest(applicationId, secret, tokenAsString);
            }
            catch
            {
                throw new Exception("Unable to calculate digest for token");
            }

            JObject info = new JObject();
            info.Add(FIELD_APPLICATION_ID, applicationId);
            info.Add(FIELD_DIGEST, digest);
            info.Add(FIELD_TOKEN, tokenAsString);

            string decodedDigestTokenAsString;

            try
            {
                decodedDigestTokenAsString = info.ToString().Replace("\r", "").Replace("\n", "").Replace(" ", "");
            }
            catch
            {
                throw new Exception("Unable to convert encoded token to string");
            }

            byte[] decodedDigestTokenAsBytes = Encoding.UTF8.GetBytes(decodedDigestTokenAsString);
            string encodedDigestToken = Convert.ToBase64String(decodedDigestTokenAsBytes, Base64FormattingOptions.None);

            return DIGEST_TOKEN_PREFIX + encodedDigestToken;
        }

        private string CalculateDigest(string applicationId, string secret, string token)
        {
            // The hmac salt is the concatenation of application ID and secret to eliminate the use of lookup table for brute force attacks.
            string salt = applicationId + secret;

            byte[] secretKeyBArr = Encoding.UTF8.GetBytes(salt);
            byte[] tokenBArr = Encoding.UTF8.GetBytes(token);
            HMACSHA512 hmacsha512 = new HMACSHA512(secretKeyBArr);
            hmacsha512.Initialize();

            byte[] final = hmacsha512.ComputeHash(tokenBArr);

            return Convert.ToBase64String(final, Base64FormattingOptions.None);
        }
    }
}