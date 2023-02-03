# Phenix EdgeAuth Digest Tokens for .Net

Easily generate secure digest tokens to use with the Phenix platform without requiring any networking activity.

## Installation

To install the Phenix Edge Authorization Digest Token library for .Net, you have multiple options:
 * Visual Studio,
 * dotnet CLI,
 * nuget CLI,
 * PowerShell Package Manager

Reference:
https://docs.microsoft.com/en-us/nuget/consume-packages/install-use-packages-visual-studio

Through Visual Studio tools with nuget.org as a Package Manager:

 1. Choose the Manage NuGet Packages...
 1. Search for PhenixRTS.EdgeAuth
 1. Install PhenixRTS.EdgeAuth

OR

Using dotnet CLI to add to your project file:

```
dotnet add package PhenixRTS.EdgeAuth
```

## C# Example

```C#
using PhenixRTS.EdgeAuth;

// Create a token to access a channel
string token = new TokenBuilder()
	.WithApplicationId("my-application-id")
	.WithSecret("my-secret")
	.ExpiresInSeconds(3600)
	.ForChannel("us-northeast#my-application-id#my-channel.1345")
	.Build();
```

## Command Line Examples

Display the help information:
```shell script
cd PhenixRTS.EdgeAuth.CLI
dotnet run --framework net6.0 -- --help
```

Create a token for channel access:
```shell script
cd PhenixRTS.EdgeAuth.CLI
dotnet run --framework net6.0 -- --applicationId "my-application-id" --secret "my-secret" --expiresInSeconds 3600 --channel "us-northeast#my-application-id#my-channel.1345"
```
