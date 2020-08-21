using System;
using Xunit;

namespace PhenixRTS.EdgeAuth.Tests
{
    public sealed class WhenVerifyingATokenForATag
    {
        private readonly string _token;

        public WhenVerifyingATokenForATag()
        {
            _token = new TokenBuilder().WithApplicationId("my-application-id")
                                       .WithSecret("my-secret")
                                       .ExpiresAt(DateTimeOffset.UnixEpoch.UtcDateTime.AddMilliseconds(1000))
                                       .ForTag("my-tag=awesome")
                                       .ForStreamingOnly()
                                       .Build();
        }

        [Fact]
        public void TheTokenMatchesTheExpectedValue()
        {
            Assert.Equal("DIGEST:eyJhcHBsaWNhdGlvbklkIjoibXktYXBwbGljYXRpb24taWQiLCJkaWdlc3QiOiJGUGRrTFFyVGlsS0toRDduc2QzeDZoNWV1aXVsaDVCYy9lNEtmQWY0THB5Qno4N2trK2lrQWN5ZUppcFk3alo4clpTN1N0bWw1aERMWEJIZXkrbmw2QT09IiwidG9rZW4iOiJ7XCJleHBpcmVzXCI6MTAwMCxcInJlcXVpcmVkVGFnXCI6XCJteS10YWc9YXdlc29tZVwiLFwidHlwZVwiOlwic3RyZWFtXCJ9In0=", _token);
        }

        [Fact]
        public void TheTokenSuccessfullyVerifiesWithTheCorrectSecret()
        {
            DigestTokens.VerifyAndDecodeResult result = new DigestTokens().VerifyAndDecode("my-secret", _token);

            Assert.True(result.IsVerified());
            Assert.Equal(ECode.VERIFIED, result.GetCode());
            Assert.NotNull(result.GetValue());
            Assert.Equal("my-tag=awesome", result.GetValue().GetValue("requiredTag").ToString());
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
