package edgeauth

import (
	"fmt"
	"strings"
)

func buildToken() error {
	if builder != nil && token == nil {
		var err error
		token, err = builder.Build()
		if err != nil {
			return fmt.Errorf("token builder failed: %v", err)
		}
	}
	return nil
}

func bddCheckField(expectedField string, expectedValue string) error {
	if result == nil || result.Value == nil {
		return fmt.Errorf("the verification value is not set")
	}
	check, exists := result.Value.Get(expectedField)
	if !exists || check != expectedValue {
		return fmt.Errorf("required %s in value does not match", expectedField)
	}
	return nil
}

func bddCheckArrayField(expectedField string, expectedValues string) error {
	if result == nil || result.Value == nil {
		return fmt.Errorf("the verification value is not set")
	}
	expectedValue := strings.Split(expectedValues, ",")
	check, exists := result.Value.Get(expectedField)
	if !exists {
		return fmt.Errorf("required %s in value does not match", expectedField)
	}
	values, ok := check.([]interface{})
	if !ok || len(values) != len(expectedValue) {
		return fmt.Errorf("required %s in value does not match", expectedField)
	}
	for i := range values {
		s, ok := values[i].(string)
		if !ok || s != expectedValue[i] {
			return fmt.Errorf("required %s in value does not match", expectedField)
		}
	}
	return nil
}
