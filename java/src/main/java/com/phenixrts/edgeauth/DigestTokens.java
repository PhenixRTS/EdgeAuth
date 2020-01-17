/**
 * Copyright 2019 Phenix Real Time Solutions, Inc. All Rights Reserved.
 * <p>
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * <p>
 * http://www.apache.org/licenses/LICENSE-2.0
 * <p>
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

package com.phenixrts.edgeauth;

import java.io.IOException;
import java.io.StringReader;
import java.io.StringWriter;
import java.io.Writer;
import java.nio.charset.StandardCharsets;
import java.security.InvalidKeyException;
import java.security.NoSuchAlgorithmException;
import java.util.Map;
import javax.crypto.Mac;
import javax.crypto.spec.SecretKeySpec;
import javax.json.Json;
import javax.json.JsonObject;
import javax.json.JsonObjectBuilder;
import javax.json.JsonReader;
import javax.json.JsonValue;
import javax.json.stream.JsonParsingException;
import javax.xml.bind.DatatypeConverter;

import org.jetbrains.annotations.Contract;
import org.jetbrains.annotations.NotNull;

/**
 * Digest token helper functions.
 */
public final class DigestTokens {

  public static final class VerifyAndDecodeResult {
    private final boolean verified;
    private final ECode code;
    private final JsonObject value;

    @Contract(pure = true)
    private VerifyAndDecodeResult(ECode code) {
      this.verified = false;
      this.code = code;
      this.value = null;
    }

    @Contract(pure = true)
    private VerifyAndDecodeResult(JsonObject value) {
      this.verified = true;
      this.code = ECode.VERIFIED;
      this.value = value;
    }

    /**
     * Checks if the result is verified.
     *
     * @return true, if the token is verified
     */
    @Contract(pure = true)
    public boolean isVerified() {
      return verified;
    }

    /**
     * Get the verification result.
     *
     * @return the error code if verification failed
     */
    @Contract(pure = true)
    public ECode getCode() {
      return code;
    }

    /**
     * Get the value.
     *
     * @return the verified JSON object
     */
    @Contract(pure = true)
    public JsonObject getValue() {
      return value;
    }
  }

  private static final String DIGEST_TOKEN_PREFIX = "DIGEST:";
  private static final String FIELD_APPLICATION_ID = "applicationId";
  private static final String FIELD_DIGEST = "digest";
  private static final String FIELD_TOKEN = "token";
  public static final String FIELD_EXPIRES = "expires";
  private static final String HMAC_ALGORITHM = "HmacSHA512";

  /**
   * Check if a value is a valid digest token.
   *
   * @param encodedToken an encoded token
   * @return true, if the encodedToken is a valid digest token
   */
  @Contract(value = "null -> false", pure = true)
  public boolean isDigestToken(String encodedToken) {
    return encodedToken != null && encodedToken.startsWith(DIGEST_TOKEN_PREFIX);
  }

  /**
   * Verify and decode an encoded token.
   *
   * @param secret       the secret used to encode the token
   * @param encodedToken the encoded token
   * @return the verification result
   */
  @NotNull
  @Contract("null, _ -> fail; !null, null -> fail")
  public VerifyAndDecodeResult verifyAndDecode(String secret, String encodedToken) {
    if (secret == null) {
      throw new RuntimeException("Secret must not be null");
    }

    if (encodedToken == null) {
      throw new RuntimeException("Encoded token must not be null");
    }

    if (!this.isDigestToken(encodedToken)) {
      return new VerifyAndDecodeResult(ECode.NOT_A_DIGEST_TOKEN);
    }

    final String encodedDigestToken = encodedToken.substring(DIGEST_TOKEN_PREFIX.length());
    final byte[] decodedAsBytes;

    try {
      decodedAsBytes = DatatypeConverter.parseBase64Binary(encodedDigestToken);
    } catch (IllegalArgumentException e) {
      return new VerifyAndDecodeResult(ECode.BAD_TOKEN);
    }

    final String decodedAsString = new String(decodedAsBytes);
    final JsonObject info;

    try {
      try (JsonReader reader = Json.createReader(new StringReader(decodedAsString))) {
        info = reader.readObject();
      }
    } catch (JsonParsingException e) {
      return new VerifyAndDecodeResult(ECode.BAD_TOKEN);
    }

    final String[] stringFields = {FIELD_APPLICATION_ID, FIELD_DIGEST, FIELD_TOKEN};
    for (String field : stringFields) {
      if (info.containsKey(field) && info.get(field).getValueType() == JsonValue.ValueType.STRING) {
        continue;
      }

      return new VerifyAndDecodeResult(ECode.BAD_TOKEN);
    }

    final String applicationId = info.getString(FIELD_APPLICATION_ID);
    final String token = info.getString(FIELD_TOKEN);

    try {
      final String digestAsString = calculateDigest(applicationId, secret, token);
      final String digest = info.getString(FIELD_DIGEST);

      if (!digestAsString.equals(digest)) {
        return new VerifyAndDecodeResult(ECode.BAD_DIGEST);
      }
    } catch (NoSuchAlgorithmException | InvalidKeyException e) {
      return new VerifyAndDecodeResult(ECode.UNSUPPORTED);
    }

    final JsonObject value;

    try (JsonReader reader = Json.createReader(new StringReader(token))) {
      value = reader.readObject();
    }

    JsonObjectBuilder builder = Json.createObjectBuilder();

    for (Map.Entry<String, JsonValue> property : value.entrySet()) {
      builder.add(property.getKey(), property.getValue());
    }

    builder.add(FIELD_APPLICATION_ID, applicationId);

    JsonObject result = builder.build();

    return new VerifyAndDecodeResult(result);
  }

  /**
   * Signs and encodes a digest token.
   *
   * @param applicationId the application ID used to sign the token
   * @param secret        the shared secret used to sign the token
   * @param token         the raw token object to sign
   * @return the signed and encoded digest token
   */
  @NotNull
  @Contract("null, _, _ -> fail; !null, null, _ -> fail; !null, !null, null -> fail")
  public String signAndEncode(String applicationId, String secret, JsonObject token) {
    if (applicationId == null) {
      throw new RuntimeException("Application ID must not be null");
    }

    if (secret == null) {
      throw new RuntimeException("Secret must not be null");
    }

    if (token == null) {
      throw new RuntimeException("Token must not be null");
    }

    if (!token.containsKey(FIELD_EXPIRES) || token.get(FIELD_EXPIRES).getValueType() != JsonValue.ValueType.NUMBER) {
      throw new RuntimeException("Token must have an expiration (milliseconds since UNIX epoch)");
    }

    if (token.containsKey(FIELD_APPLICATION_ID)) {
      throw new RuntimeException("Token should not have an application ID property");
    }

    final String tokenAsString;

    try (Writer writer = new StringWriter()) {
      Json.createWriter(writer).write(token);
      tokenAsString = writer.toString();
    } catch (IOException e) {
      throw new RuntimeException(e);
    }

    final String digest;
    try {
      digest = calculateDigest(applicationId, secret, tokenAsString);
    } catch (NoSuchAlgorithmException | InvalidKeyException e) {
      throw new RuntimeException(e);
    }

    final JsonObject info = Json.createObjectBuilder()
        .add(FIELD_APPLICATION_ID, applicationId)
        .add(FIELD_DIGEST, digest)
        .add(FIELD_TOKEN, tokenAsString)
        .build();
    final String decodedDigestTokenAsString;

    try (Writer writer = new StringWriter()) {
      Json.createWriter(writer).write(info);
      decodedDigestTokenAsString = writer.toString();
    } catch (IOException e) {
      throw new RuntimeException(e);
    }

    final byte[] decodedDigestTokenAsBytes = decodedDigestTokenAsString.getBytes(StandardCharsets.UTF_8);
    final String encodedDigestToken = DatatypeConverter.printBase64Binary(decodedDigestTokenAsBytes);

    return DIGEST_TOKEN_PREFIX + encodedDigestToken;
  }

  private String calculateDigest(String applicationId, String secret, String token) throws NoSuchAlgorithmException, InvalidKeyException {
    // The hmac salt is the concatenation of application ID and secret to eliminate the use of lookup table for brute force attacks.
    final String salt = applicationId + secret;
    final SecretKeySpec keySpec = new SecretKeySpec(salt.getBytes(StandardCharsets.UTF_8), HMAC_ALGORITHM);
    final Mac mac = Mac.getInstance(HMAC_ALGORITHM);

    mac.init(keySpec);

    final byte[] digestAsBytes = mac.doFinal(token.getBytes(StandardCharsets.UTF_8));
    return DatatypeConverter.printBase64Binary(digestAsBytes);
  }
}