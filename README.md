# Persistent User Authentications

Persistent authentication (remember me) is done storing a cookie on a client side with unique token. Same token is kept on a server side in a database or other permanent storage and links to the user ID. Token by itself does not contains user information and is generated randomly.

Persistent sessions have expiration time (by default a month). The expiration time is set both in the cookie and in the database. During that time, any regular session will be regenerated by the persistent session. Each time a token is used to generate a session it is invalidated and a new one is generated preserving the expiration time from it's "parent" token.

One user can have several valid persistent sessions. When a user logs out the persistent cookie will be deleted and the token in the database will be invalidated. for security reasons invalidated tokens are not deleted from the database. If the user tries to access with an invalidated cookie (probably stolen) all persistent sessions will be closed. It will be a good idea to send an email to the user notifying for the issue.

It's also a good practice to close all active sessions when a user changes his/her password.

Another security precaution is to ask the user for a password if he/she is attempting some restricted operations like payments, changing email addresses or a password, etc.
