package edgeauth

func iTryToVerifyATokenWithABadSecret() error {
	tmp := "bad-secret"
	secret = &tmp
	return nil
}

func iTryToVerifyATokenWithAGoodSecret() error {
	tmp := "my-secret"
	secret = &tmp
	return nil
}

func theCorrectTokenIs(arg1 string) error {
	correctToken = &arg1
	return nil
}
