namespace PhenixRTS.EdgeAuth
{
    /// <summary>
    /// Verification result code.
    /// </summary>
    public enum ECode
    {
        VERIFIED,
        BAD_TOKEN,
        BAD_DIGEST,
        NOT_A_DIGEST_TOKEN,
        UNSUPPORTED
    }
}
