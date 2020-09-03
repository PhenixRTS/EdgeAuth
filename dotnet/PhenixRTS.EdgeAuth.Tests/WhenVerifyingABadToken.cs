using Xunit;

namespace PhenixRTS.EdgeAuth.Tests
{
    public sealed class WhenVerifyingABadToken
    {
        private const string _token = "DIGEST:bad-token";

        [Fact]
        public void TheTokenFailsToVerify()
        {
            DigestTokens.VerifyAndDecodeResult result = new DigestTokens().VerifyAndDecode("bad-secret", _token);

            Assert.False(result.IsVerified());
            Assert.Equal(ECode.BAD_TOKEN, result.GetCode());
            Assert.Null(result.GetValue());
        }
    }
}
