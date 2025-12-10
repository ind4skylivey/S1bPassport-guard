# 📘 S1b Passport Guard: The Missing Security Layer

## 🛡️ Why did we build this?

Laravel Passport is the gold standard for OAuth2 in Laravel, handling token issuance and validation perfectly. However, it has a significant blind spot: **Observability**.

Once a token is issued, Passport doesn't tell you much about _how_ it's being used.

-   Are you seeing a sudden spike in login attempts?
-   Is a specific client generating 10x more tokens than usual?
-   Is a user refreshing their token every 5 minutes instead of every 5 days?

**S1b Passport Guard** was created to fill this gap. It provides the **intelligence layer** over your authentication system, transforming silent logs into actionable security insights.

---

## 🌍 Relevance in Modern Projects

In today's API-first world, **Credential Stuffing** and **Token Theft** are the most common attack vectors.

1.  **Silent Failures:** A hacker might steal a Client ID/Secret and generate tokens quietly. Without monitoring, you won't know until your database is scraped.
2.  **Zombie Sessions:** Stolen refresh tokens allow attackers to maintain access indefinitely.
3.  **Resource Exhaustion:** A bug in a mobile app might retry authentication in an infinite loop, flooding your database.

S1b Passport Guard acts as a **Watchdog**, alerting you to these anomalies before they become breaches.

---

## 💡 Real-World Use Cases

### Scenario 1: The "Leaked Secret" 🚨

**The Situation:** A developer accidentally commits a `client_secret` to a public repository.
**The Attack:** Bots find it and start generating thousands of access tokens to scrape your API.
**Without Guard:** You notice high server load days later.
**With Guard:**

> `⚠️ THREAT DETECTED: Creation spike +500% (Client #3: Mobile App)` > _You immediately revoke the client and rotate the secret._

### Scenario 2: The "Infinite Loop" Bug 🐛

**The Situation:** A frontend update introduces a bug where the "Refresh Token" logic triggers on every page load instead of on expiry.
**The Impact:** Your `oauth_access_tokens` table grows by millions of rows, slowing down queries.
**With Guard:**

> `⚠️ THREAT DETECTED: Unusual refreshes (User #105: 2400/day)` > _You identify the bug in the frontend and fix it before the database crashes._

### Scenario 3: The "Abandoned" Client 👻

**The Situation:** You have 5 different mobile apps. You want to deprecate the old v1 app.
**With Guard:**

> You run `php artisan s1b:guard` and see that "Legacy App v1" still has 5,000 active tokens.
> _You decide to send a push notification to those users before shutting down the API._

---

## 🚀 Suggestions for Best Results

1.  **Tune Your Thresholds:**
    Every app is different. If you have a viral launch, a 200% spike in tokens is good! Adjust `creation_spike_pct` in the config to match your growth.

2.  **Schedule the Watchdog:**
    Don't just run it manually. Set up a daily report or integrate the output into your admin dashboard.

3.  **Monitor "Avg Lifespan":**
    If your `Avg Lifespan` drops drastically (e.g., from 15 days to 1 hour), it usually means clients are failing to store tokens and re-authenticating constantly. This hurts user experience and server performance.

---

> _"Security is not about building walls, it's about knowing who is climbing them."_
