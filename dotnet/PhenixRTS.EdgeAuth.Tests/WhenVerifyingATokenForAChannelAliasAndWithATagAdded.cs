using Newtonsoft.Json.Linq;
using System;
using Xunit;

namespace PhenixRTS.EdgeAuth.Tests
{
    public sealed class WhenVerifyingATokenForAChannelAliasAndWithATagAdded
    {
        private readonly string _token;

        public WhenVerifyingATokenForAChannelAliasAndWithATagAdded()
        {
            _token = new TokenBuilder().WithApplicationId("my-application-id")
                                       .WithSecret("my-secret")
                                       .ExpiresAt(DateTimeOffset.UnixEpoch.UtcDateTime.AddMilliseconds(1000))
                                       .ForChannelAlias("my-channel")
                                       .ForStreamingOnly()
                                       .ApplyTag("customer1")
                                       .Build();
        }

        [Fact]
        public void TheTokenMatchesTheExpectedValue()
        {
            Assert.Equal("DIGEST:eyJhcHBsaWNhdGlvbklkIjoibXktYXBwbGljYXRpb24taWQiLCJkaWdlc3QiOiJMU0VnS2dGTy9aRUdxdEFLazVZb0F6cFJuTnQ4enhwUjNsdEJ3cWtOR3E1VWdjWWZpcnZKTDk3NHhpangyNS9XbHpqaUg1dk5ZMHdaYklFSkE2MzJqdz09IiwidG9rZW4iOiJ7XCJleHBpcmVzXCI6MTAwMCxcInJlcXVpcmVkVGFnXCI6XCJjaGFubmVsQWxpYXM6bXktY2hhbm5lbFwiLFwidHlwZVwiOlwic3RyZWFtXCIsXCJhcHBseVRhZ3NcIjpbXCJjdXN0b21lcjFcIl19In0=", _token);
        }

        [Fact]
        public void TheTokenSuccessfullyVerifiesWithTheCorrectSecret()
        {
            DigestTokens.VerifyAndDecodeResult result = new DigestTokens().VerifyAndDecode("my-secret", _token);

            Assert.True(result.IsVerified());
            Assert.Equal(ECode.VERIFIED, result.GetCode());
            Assert.NotNull(result.GetValue());
            Assert.Equal("channelAlias:my-channel", result.GetValue().GetValue("requiredTag").ToString());
            Assert.Single(result.GetValue().GetValue("applyTags") as JArray);
            Assert.Equal("customer1", (result.GetValue().GetValue("applyTags") as JArray)[0].ToString());
        }

        [Fact]
        public void TheTokenFailsToVerifyWithABadSecret()
        {
            DigestTokens.VerifyAndDecodeResult result = new DigestTokens().VerifyAndDecode("bad-secret", _token);

            Assert.False(result.IsVerified());
            Assert.Equal(ECode.BAD_DIGEST, result.GetCode());
            Assert.Null(result.GetValue());
        }
    }
}
