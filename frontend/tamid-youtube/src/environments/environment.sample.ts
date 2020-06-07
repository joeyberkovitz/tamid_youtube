// Create a file with environment variables
// In dev, called environment.ts and in prod: environment.prod.ts
// API URL is the base URL for the API server
// ClientID and Secret are OAuth client and secret from Laravel
// Secret is required by the OAuth server, but isn't meant to be secret

export const environment = {
  production: true,
  apiUrl: 'https://localhost',
  clientSecret: 'SECRET',
  clientId: 'CLIENT_ID'
};
