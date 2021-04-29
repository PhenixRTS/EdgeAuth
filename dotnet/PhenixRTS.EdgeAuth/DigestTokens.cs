using System;
using System.IO;
using System.Security.Cryptography;
using System.Text;
using System.Text.Json;

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
            private readonly JsonDocument value;

            public VerifyAndDecodeResult(ECode code)
            {
                _isVerified = false;
                _code = code;
                value = null;
            }

            public VerifyAndDecodeResult(JsonDocument valuedoc)
            {
                _isVerified = true;
                _code = ECode.VERIFIED;
                value = valuedoc;
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
            public JsonDocument GetValue()
            {
                return value;
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

            JsonDocument info;

            try
            {
                try
                {
                    info = JsonDocument.Parse(decodedAsString);
                }
                catch (JsonException ex)
                {
                    return new VerifyAndDecodeResult(ECode.BAD_TOKEN);
                }

                string[] stringFields = { FIELD_APPLICATION_ID, FIELD_DIGEST, FIELD_TOKEN };

                foreach (string field in stringFields)
                {
                    if (info.RootElement.TryGetProperty(field, out _) && info.RootElement.GetProperty(field).ValueKind == JsonValueKind.String)
                    {
                        continue;
                    }

                    return new VerifyAndDecodeResult(ECode.BAD_TOKEN);
                }

                string applicationId = info.RootElement.GetProperty(FIELD_APPLICATION_ID).GetString();
                string token = info.RootElement.GetProperty(FIELD_TOKEN).GetString();

                try
                {
                    string digestAsString = CalculateDigest(applicationId, secret, token);
                    string digest = info.RootElement.GetProperty(FIELD_DIGEST).GetString();

                    if (!digestAsString.Equals(digest))
                    {
                        return new VerifyAndDecodeResult(ECode.BAD_DIGEST);
                    }
                }
                catch
                {
                    return new VerifyAndDecodeResult(ECode.UNSUPPORTED);
                }

                JsonDocument value;

                try
                {
                    value = JsonDocument.Parse(token);
                }
                catch (JsonException)
                {
                    return new VerifyAndDecodeResult(ECode.BAD_TOKEN);
                }

                VerifyAndDecodeResult result;
                using (var memoryStream = new MemoryStream())
                {
                    Utf8JsonWriter JsonWriter = new Utf8JsonWriter(memoryStream);
                    JsonWriter.WriteStartObject();
                    foreach (var testDataElement in value.RootElement.EnumerateObject())
                    {
                        testDataElement.WriteTo(JsonWriter);
                    }
                    WriteStringProperty(JsonWriter, FIELD_APPLICATION_ID, applicationId);
                    JsonWriter.WriteEndObject();
                    JsonWriter.Flush();

                    result = new VerifyAndDecodeResult(JsonDocument.Parse(Encoding.UTF8.GetString(memoryStream.ToArray())));
                }                   

                return result;
            }
            catch (Exception e)
            {
                throw e;
            }
        }

        private void WriteStringProperty(Utf8JsonWriter JsonWriter, string Property, string Value)
        {
            JsonWriter.WritePropertyName(Property);
            JsonWriter.WriteStringValue(JsonEncodedText.Encode(Value, System.Text.Encodings.Web.JavaScriptEncoder.UnsafeRelaxedJsonEscaping));
        }

        public string SignAndEncode(string applicationId, string secret, JsonDocument token)
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

            if (!token.RootElement.TryGetProperty(FIELD_EXPIRES, out _) || token.RootElement.GetProperty(FIELD_EXPIRES).ValueKind != JsonValueKind.Number)
            {
                throw new Exception("Token must have an expiration (milliseconds since UNIX epoch)");
            }

            if (token.RootElement.TryGetProperty(FIELD_APPLICATION_ID, out _))
            {
                throw new Exception("Token should not have an application ID property");
            }

            string tokenAsString;

            try
            {
                tokenAsString = token.RootElement.ToString().Replace("\r", "").Replace("\n", "").Replace(" ", "");
            }
            catch (Exception e)
            {
                throw e;
            }

            string digest;

            try
            {
                digest = CalculateDigest(applicationId, secret, tokenAsString);
            }
            catch (Exception e)
            {
                throw e;
            }

            JsonDocument result;
            using (var memoryStream = new MemoryStream())
            {
                Utf8JsonWriter JsonWriter = new Utf8JsonWriter(memoryStream);
                JsonWriter.WriteStartObject();
                WriteStringProperty(JsonWriter, FIELD_APPLICATION_ID, applicationId);
                WriteStringProperty(JsonWriter, FIELD_DIGEST, digest);
                WriteStringProperty(JsonWriter, FIELD_TOKEN, tokenAsString);
                JsonWriter.WriteEndObject();
                JsonWriter.Flush();
                result = JsonDocument.Parse(Encoding.UTF8.GetString(memoryStream.ToArray()));
            }

           

            string decodedDigestTokenAsString;

            try
            {
                decodedDigestTokenAsString = result.RootElement.ToString().Replace("\r", "").Replace("\n", "").Replace(" ", ""); ;
            }
            catch (Exception e)
            {
                throw e;
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