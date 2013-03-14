Connecting an identity to an existing account
=============================================

This app allows users to connect more than one social login to their accounts.
If an authenticated user connects a social login to their account that was
currently in use by another account (the use case here would be to merge an
account that was created in error into the account they want to keep using),
an event is fired which you can catch and react to (e.g. for assigning the
merging user account to existing data of the merged account).

The ID of that event is `security.user_accounts_merged`, and it's got two
methods to retrieve the User objects involved in the merge: `getMergedUser()`
and `getMergingUser()` - the latter referring to the account that the user
intends to keep.

Tag your event listener like this:

```
- { name: kernel.event_listener, event: security.user_accounts_merged, method: onEvent }
```