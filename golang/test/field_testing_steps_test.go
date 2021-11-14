package edgeauth

import (
	edgeauth "github.com/PhenixRTS/EdgeAuth/golang"
)

func theRemoteAddressFieldShouldBe(arg1 string) error {
	return bddCheckField(edgeauth.RemoteAddressField, arg1)
}

func theURIFieldShouldBe(arg1 string) error {
	return bddCheckField(edgeauth.URIField, arg1)
}

func theSessionFieldShouldBe(arg1 string) error {
	return bddCheckField(edgeauth.SessionIDField, arg1)
}

func theTypeFieldShouldBe(arg1 string) error {
	return bddCheckField(edgeauth.TypeField, arg1)
}

func theTagFieldShouldBe(arg1 string) error {
	return bddCheckField(edgeauth.RequiredTagField, arg1)
}

func theAppliedTagsFieldShouldBe(arg1 string) error {
	return bddCheckArrayField(edgeauth.ApplyTagsField, arg1)
}

func theCapabilitiesFieldShouldBe(arg1 string) error {
	return bddCheckArrayField(edgeauth.CapabilitiesField, arg1)
}
