using Newtonsoft.Json.Linq;
using System;
using System.Linq;
using Xunit;

namespace PhenixRTS.EdgeAuth.Tests
{
    public sealed class WhenVerifyingATokenForPublishingWithCapabilities
    {
        private readonly string _token;

        public WhenVerifyingATokenForPublishingWithCapabilities()
        {
            _token = new TokenBuilder().WithApplicationId("my-application-id")
                                       .WithSecret("my-secret")
                                       .ExpiresAt(DateTimeOffset.UnixEpoch.UtcDateTime.AddMilliseconds(1000))
                                       .ForPublishingOnly()
                                       .WithCapability("multi-bitrate")
                                       .WithCapability("streaming")
                                       .Build();
        }

        [Fact]
        public void TheTokenMatchesTheExpectedValue()
        {
            Assert.Equal("DIGEST:eyJhcHBsaWNhdGlvbklkIjoibXktYXBwbGljYXRpb24taWQiLCJkaWdlc3QiOiJFKytBK3EwWGhGQ09LT011RnZqcnRIOVNyeHpwZ0Q1VVZYb1B6Q1VPaGNLU3pHTGRQZmsyRVYzVkZOOWRyM2tBVGZtSWRUeCtSTlFodjJ3aVJGbUM1Zz09IiwidG9rZW4iOiJ7XCJleHBpcmVzXCI6MTAwMCxcInR5cGVcIjpcInB1Ymxpc2hcIixcImNhcGFiaWxpdGllc1wiOltcIm11bHRpLWJpdHJhdGVcIixcInN0cmVhbWluZ1wiXX0ifQ==", _token);
        }

        [Fact]
        public void TheTokenSuccessfullyVerifiesWithTheCorrectSecret()
        {
            DigestTokens.VerifyAndDecodeResult result = new DigestTokens().VerifyAndDecode("my-secret", _token);

            Assert.True(result.IsVerified());
            Assert.Equal(ECode.VERIFIED, result.GetCode());
            Assert.NotNull(result.GetValue());
            Assert.Equal("publish", result.GetValue().RootElement.GetProperty("type").ToString());
            Assert.Equal(2, result.GetValue().RootElement.GetProperty("capabilities").EnumerateArray().ToList().Count);
            Assert.Equal("multi-bitrate", result.GetValue().RootElement.GetProperty("capabilities").EnumerateArray().ToList()[0].ToString());
            Assert.Equal("streaming", result.GetValue().RootElement.GetProperty("capabilities").EnumerateArray().ToList()[1].ToString());
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
