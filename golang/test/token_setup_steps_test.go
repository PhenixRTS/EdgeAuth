package edgeauth

func theTokenIsForAChannel(arg1 string) error {
	builder = builder.ForChannel(arg1)
	return nil
}

func theTokenIsForAChannelAlias(arg1 string) error {
	builder = builder.ForChannelAlias(arg1)
	return nil
}

func theTokenIsForARoom(arg1 string) error {
	builder = builder.ForRoom(arg1)
	return nil
}

func theTokenIsForARoomAlias(arg1 string) error {
	builder = builder.ForRoomAlias(arg1)
	return nil
}

func theTokenIsForARemoteAddress(arg1 string) error {
	builder = builder.ForRemoteAddress(arg1)
	return nil
}

func theTokenIsForASession(arg1 string) error {
	builder = builder.ForSession(arg1)
	return nil
}

func theTokenIsForStreamingOnly() error {
	builder = builder.ForStreamingOnly()
	return nil
}

func theTokenIsForPublishingOnly() error {
	builder = builder.ForPublishingOnly()
	return nil
}

func theTokenIsForTag(arg1 string) error {
	builder = builder.ForTag(arg1)
	return nil
}

func theTokenHasATagApplied(arg1 string) error {
	builder = builder.ApplyTag(arg1)
	return nil
}

func theTokenHasCapability(arg1 string) error {
	builder = builder.WithCapability(arg1)
	return nil
}
