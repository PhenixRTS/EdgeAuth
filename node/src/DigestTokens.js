/**
 * Copyright 2019 Phenix Real Time Solutions, Inc. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

const crypto = require('crypto');
const digestTokenPrefix = 'DIGEST:';
const digestAlgorithm = 'SHA512';
const digestEncoding = 'base64';
const encodedTokenEncoding = 'base64';
const decodedTokenEncoding = 'utf8';

/**
 * Digest token helper functions.
 *
 * @constructor
 */
function DigestTokens() {
}

/**
 * Check if a value is a valid digest token.
 *
 * @param encodedToken an encoded token
 * @returns {*|boolean} true, if the encodedToken is a valid digest token
 */
DigestTokens.prototype.isDigestToken = function(encodedToken) {
  return encodedToken && typeof encodedToken === 'string' && encodedToken.startsWith(digestTokenPrefix);
};

/**
 * Verifies and decodes a digest token.
 *
 * @param secret the shared secret used to sign the token
 * @param encodedToken the encoded token
 * @returns {*} An object {verified,code,value} with verified set to true if the token was successfully verified. And with verified set to false if the token was not verified. In that case, code is provided to indicate the type of problem.
 */
DigestTokens.prototype.verifyAndDecode = function(secret, encodedToken) {
  if (typeof secret !== 'string') {
    throw new Error('Secret must be a string');
  }

  if (typeof encodedToken !== 'string') {
    throw new Error('Encoded token must be a string');
  }

  if (!this.isDigestToken(encodedToken)) {
    return {
      verified: false,
      code: 'not-a-digest-token'
    };
  }

  const encodedDigestToken = encodedToken.substring(digestTokenPrefix.length);
  const decodedDigestTokenAsString = Buffer.from(encodedDigestToken, encodedTokenEncoding).toString(decodedTokenEncoding);
  let info;

  try {
    info = JSON.parse(decodedDigestTokenAsString);
  } catch (e) {
    return {
      verified: false,
      code: 'bad-token'
    };
  }

  if (!info.applicationId || typeof info.applicationId !== 'string') {
    return {
      verified: false,
      code: 'bad-token'
    };
  }

  if (!info.digest || typeof info.digest !== 'string') {
    return {
      verified: false,
      code: 'bad-token'
    };
  }

  if (!info.token || typeof info.token !== 'string') {
    return {
      verified: false,
      code: 'bad-token'
    };
  }

  const digestAsString = calculateDigest(info.applicationId, secret, info.token);
  const digest = info.digest;

  if (digestAsString !== digest) {
    return {
      verified: false,
      code: 'bad-digest'
    };
  }

  const value = JSON.parse(info.token);

  value.applicationId = info.applicationId;

  return {
    verified: true,
    code: 'verified',
    value
  };
};

/**
 * Signs and encodes a digest token.
 *
 * @param applicationId the application ID used to sign the token
 * @param secret the shared secret used to sign the token
 * @param token the raw token object to sign
 * @returns {string} the signed and encoded digest token
 */
DigestTokens.prototype.signAndEncode = function(applicationId, secret, token) {
  if (typeof applicationId !== 'string') {
    throw new Error('Application ID must be a string');
  }

  if (typeof secret !== 'string') {
    throw new Error('Secret must be a string');
  }

  if (typeof token !== 'object') {
    throw new Error('Encoded token must be a object');
  }

  if (!token.expires || typeof token.expires !== 'number') {
    throw new Error('Token must have an expiration (milliseconds since UNIX epoch)');
  }

  if (token.applicationId) {
    throw new Error('Token should not have an application ID property');
  }

  const tokenAsString = JSON.stringify(token);
  const digest = calculateDigest(applicationId, secret, tokenAsString);
  const info = {
    applicationId,
    digest,
    token: tokenAsString
  };
  const decodedDigestTokenAsString = JSON.stringify(info);
  const encodedDigestToken = Buffer.from(decodedDigestTokenAsString, decodedTokenEncoding).toString(encodedTokenEncoding);

  return digestTokenPrefix + encodedDigestToken;
};

/**
 * Calculates the digest for a token.
 *
 * @param applicationId
 * @param secret
 * @param token
 * @returns The Base64 encoded digest
 */
function calculateDigest(applicationId, secret, token) {
  if (typeof applicationId !== 'string') {
    throw new Error('Application ID must be a string');
  }

  if (typeof secret !== 'string') {
    throw new Error('Secret must be a string');
  }

  if (typeof token !== 'string') {
    throw new Error('Token must be a string');
  }

  // The hmac salt is the concatenation of application ID and secret to eliminate the use of lookup table for brute force attacks.
  const salt = applicationId + secret;
  const verify = crypto.createHmac(digestAlgorithm, salt);

  verify.update(token);

  return verify.digest(digestEncoding);
}

module.exports = DigestTokens;