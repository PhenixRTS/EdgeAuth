using System;
using Xunit;

namespace PhenixRTS.EdgeAuth.Tests
{
    public sealed class WhenVerifyingATokenForAChannel
    {
        private readonly string _token;

        public WhenVerifyingATokenForAChannel()
        {
            _token = new TokenBuilder().WithApplicationId("my-application-id")
                                       .WithSecret("my-secret")
                                       .ExpiresAt(DateTimeOffset.UnixEpoch.UtcDateTime.AddMilliseconds(1000))
                                       .ForChannel("us-northeast#my-application-id#my-channel.134566")
                                       .ForStreamingOnly()
                                       .Build();
        }

        [Fact]
        public void TheTokenMatchesTheExpectedValue()
        {
            Assert.Equal("DIGEST:eyJhcHBsaWNhdGlvbklkIjoibXktYXBwbGljYXRpb24taWQiLCJkaWdlc3QiOiJZNGM3Tmp6eDVhalkzLzRWK3pwTVliNTBBU1ZCUXc0NlAvS2dwc3JrTnpDdFAzZWM5NzVzblorN3lJNzZiM0wrTmNtb2FoL3hOTUhQZ00vNEExaDI4UT09IiwidG9rZW4iOiJ7XCJleHBpcmVzXCI6MTAwMCxcInJlcXVpcmVkVGFnXCI6XCJjaGFubmVsSWQ6dXMtbm9ydGhlYXN0I215LWFwcGxpY2F0aW9uLWlkI215LWNoYW5uZWwuMTM0NTY2XCIsXCJ0eXBlXCI6XCJzdHJlYW1cIn0ifQ==", _token);
        }

        [Fact]
        public void TheTokenSuccessfullyVerifiesWithTheCorrectSecret()
        {
            DigestTokens.VerifyAndDecodeResult result = new DigestTokens().VerifyAndDecode("my-secret", _token);

            Assert.True(result.IsVerified());
            Assert.Equal(ECode.VERIFIED, result.GetCode());
            Assert.NotNull(result.GetValue());
            Assert.Equal("channelId:us-northeast#my-application-id#my-channel.134566", result.GetValue().RootElement.GetProperty("requiredTag").ToString());
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
