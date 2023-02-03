using System;
using System.Collections.Generic;
using System.Linq;
using Utility.CommandLine;

namespace PhenixRTS.EdgeAuth.CLI
{
    public class EdgeAuth
    {
        [Argument('y', "uri", "The backend URI")]
        private static string Uri { get; set; }

        [Argument('u', "applicationId", "The application ID")]
        private static string ApplicationId { get; set; }

        [Argument('w', "secret", "The application secret")]
        private static string Secret { get; set; }

        [Argument('l', "expiresInSeconds", "Token life time in seconds")]
        private static string ExpiresInSeconds { get; set; }

        [Argument('e', "expiresAt", "Token expires at timestamp measured in milliseconds since UNIX epoch")]
        private static string ExpiresAt { get; set; }

        [Argument('a', "authenticationOnly", "Token can be used for authentication only")]
        private static string AuthenticationOnly { get; set; }

        [Argument('s', "streamingOnly", "Token can be used for streaming only")]
        private static string StreamingOnly { get; set; }

        [Argument('p', "publishingOnly", "Token can be used for publishing only")]
        private static string PublishingOnly { get; set; }

        [Argument('b', "capability", "Comma separated list of capabilities, e.g. for publishing")]
        private static string Capability { get; set; }

        [Argument('z', "sessionId", "Token is limited to the given session")]
        private static string SessionId { get; set; }

        [Argument('x', "remoteAddress", "Token is limited to the given remote address")]
        private static string RemoteAddress { get; set; }

        [Argument('o', "originStreamId", "[STREAMING] Token is limited to the given origin stream")]
        private static string OriginStreamId { get; set; }

        [Argument('c', "channel", "[STREAMING] Token is limited to the given channel")]
        private static string Channel { get; set; }

        [Argument('i', "channelAlias", "[STREAMING] Token is limited to the given channel alias")]
        private static string ChannelAlias { get; set; }

        [Argument('m', "room", "[STREAMING] Token is limited to the given room")]
        private static string Room { get; set; }

        [Argument('n', "roomAlias", "[STREAMING] Token is limited to the given room alias")]
        private static string RoomAlias { get; set; }

        [Argument('t', "tag", "[STREAMING] Token is limited to the given origin stream tag")]
        private static string Tag { get; set; }

        [Argument('r', "applyTag", "[REPORTING] Apply tag to the new stream")]
        private static string ApplyTag { get; set; }

        [Argument('h', "help", "Print this message")]
        private static string Help { get; set; }

        private const long DEFAULT_EXPIRATION_IN_SECONDS = 3600;

        /// <summary>
        /// Edge Auth token generator CLI.
        /// </summary>
        /// <param name="args">Args the CLI arguments</param>
        static void Main(string[] args)
        {
            try
            {
                Arguments.Populate();
                IEnumerable<ArgumentInfo> arguments = Arguments.GetArgumentInfo();
                CheckArguments(args, arguments);

                if (Help != null)
                {
                    PrintHelp(arguments);
                    return;
                }

                if (string.IsNullOrEmpty(ApplicationId) || string.IsNullOrEmpty(Secret))
                {
                    Console.WriteLine("You must provide both \"applicationId\" and \"secret\"");
                    return;
                }

                TokenBuilder tokenBuilder = new TokenBuilder().WithApplicationId(ApplicationId)
                                                              .WithSecret(Secret);

                BuildToken(tokenBuilder);

                try
                {
                    string tokenObjectJson = tokenBuilder.GetValue();
                    Console.WriteLine(tokenObjectJson);

                    string token = tokenBuilder.Build();
                    Console.WriteLine(token);
                }
                catch (Exception e)
                {
                    Console.WriteLine(e.Message);
                    return;
                }
            }
            catch (Exception e)
            {
                Console.WriteLine(e.Message);
            }
        }

        private static void BuildToken(TokenBuilder tokenBuilder)
        {
            if (!string.IsNullOrEmpty(Uri))
            {
                tokenBuilder.WithUri(Uri);
            }

            if (!string.IsNullOrEmpty(ExpiresAt))
            {
                tokenBuilder.ExpiresAt(DateTimeOffset.FromUnixTimeMilliseconds(Convert.ToInt64(ExpiresAt, 10)).UtcDateTime);
            }
            else
            {
                long expirationInSeconds = DEFAULT_EXPIRATION_IN_SECONDS;

                if (!string.IsNullOrEmpty(ExpiresInSeconds))
                {
                    expirationInSeconds = Convert.ToInt64(ExpiresInSeconds, 10);
                }

                tokenBuilder.ExpiresInSeconds(expirationInSeconds);
            }

            if (AuthenticationOnly != null)
            {
                tokenBuilder.ForAuthenticationOnly();
            }

            if (StreamingOnly != null)
            {
                tokenBuilder.ForStreamingOnly();
            }

            if (PublishingOnly != null)
            {
                tokenBuilder.ForPublishingOnly();
            }

            if (!string.IsNullOrEmpty(Capability))
            {
                foreach (string capability in Capability.Split(','))
                {
                    tokenBuilder.WithCapability(capability.Trim());
                }
            }

            if (!string.IsNullOrEmpty(SessionId))
            {
                tokenBuilder.ForSession(SessionId);
            }

            if (!string.IsNullOrEmpty(RemoteAddress))
            {
                tokenBuilder.ForRemoteAddress(RemoteAddress);
            }

            if (!string.IsNullOrEmpty(OriginStreamId))
            {
                tokenBuilder.ForOriginStream(OriginStreamId);
            }

            if (!string.IsNullOrEmpty(Channel))
            {
                tokenBuilder.ForChannel(Channel);
            }

            if (!string.IsNullOrEmpty(ChannelAlias))
            {
                tokenBuilder.ForChannelAlias(ChannelAlias);
            }

            if (!string.IsNullOrEmpty(Room))
            {
                tokenBuilder.ForRoom(Room);
            }

            if (!string.IsNullOrEmpty(RoomAlias))
            {
                tokenBuilder.ForRoomAlias(RoomAlias);
            }

            if (!string.IsNullOrEmpty(Tag))
            {
                tokenBuilder.ForTag(Tag);
            }

            if (!string.IsNullOrEmpty(ApplyTag))
            {
                tokenBuilder.ApplyTag(ApplyTag);
            }
        }

        private static void CheckArguments(string[] args, IEnumerable<ArgumentInfo> arguments)
        {
            for (int i = 0; i < args.Length; i++)
            {
                string arg = args[i];
                if (!arguments.Any(a => $"-{a.ShortName}" == arg || $"--{a.LongName}" == arg) &&
                    arg != "--" &&
                    arg != "-"
                    && (arg.StartsWith("-") || arg.StartsWith("--")))
                {
                    throw new Exception("unknown option " + arg);
                }
            }
        }

        private static void PrintHelp(IEnumerable<ArgumentInfo> arguments)
        {
            foreach (ArgumentInfo argument in arguments)
            {
                Console.WriteLine(" Short: -{0}, Long: --{1} - {2}", argument.ShortName, argument.LongName, argument.HelpText);
            }
        }
    }
}
