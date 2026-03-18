# 🔐 Security Notes

## Google Maps API Key

### ⚠️ Important Security Fix
A Google Maps API key was previously hardcoded in `app/Views/display/footer.php`. This has been fixed by:

1. **Replaced hardcoded key** with environment variable: `$_ENV['GOOGLE_MAPS_API_KEY']`
2. **Created `.env.example`** file with required variables
3. **Protected `.env`** file via `.gitignore` (already configured)

### Setup Instructions

1. Copy `.env.example` to `.env`:
   ```bash
   cp .env.example .env
   ```

2. Add your Google Maps API key to `.env`:
   ```
   GOOGLE_MAPS_API_KEY=your_actual_api_key_here
   ```

3. Get your API key from: https://console.cloud.google.com/

### Security Best Practices

- ✅ API keys are stored in environment variables
- ✅ `.env` files are excluded from git
- ✅ Only `.env.example` is tracked (contains placeholders)
- ✅ No sensitive data in code repository

## Rotation Required

The previously exposed API key `AIzaSyDuJqKAlW6qajR3FrK5oVz-Mba5Jz6WZmY` should be:
1. **Disabled** in Google Cloud Console
2. **Rotated** (create new key)
3. **Updated** in your `.env` file

## Other Security Considerations

- Database credentials should also use environment variables
- API keys should have appropriate restrictions (IP, referrer, etc.)
- Regular security audits recommended
