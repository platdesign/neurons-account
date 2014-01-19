#neurons-account#

A module for [Neurons]() which handles and provides typical account-methods.

##Install##

- ### Bower###
`bower install neurons-account --save`

- ### Clone from GitHub###
`git clone ssh://github.com/platdesign/neurons-account`

- ### Download###
Download latest version of [neurons-account]() from GitHub.



##Documentation##

###$accountProvider###

- **method( ***string*** $method )**

	Sets the method which is used to hold the account signed in.	
	- *session* : Stores the id of an account in a session.
	- *cookie* : Stores a token in a cookie to authenticate the user on each request.
	- *header* : Sends and expects a token in the response/request-header to authenticate.

- **PDOService( ***string*** $servicename )**

	Sets the name of the service which contains the PDO-Object of the database where account-data is stored
	

- **setTokenKey( ***string*** $keyname )**

	Sets the name of the token, when using header-method.
	
- **setSessionKey( ***string*** $keyname )**

	Sets the name of the session-key, when using session-method.

- **setCookieKey( ***string*** $keyname )**

	Sets the name of the cookie, when using cookie-method.

- **setAttributes( ***array*** $attrs )**

	Sets an array of attributes, which should be contained in account-service 
	
- **validator( ***string*** $key, ***closure*** $validator )**
 
 	Creates a validator function for a key, which is called on sign in and sign up.
 	



###$account (service)###

- *(bool)* **signin(** *string* **$username**, *string* **$secret**, *string* **$email** = NULL **)**

	Signes in an account with given arguments. Depending on `method` it sets a session, cookie or an token-header. On fail an `Exception` is thrown with information about reasons in exception-message. 

- *(bool)* **signup(** *string* **$username**, *string* **$secret**, *string* **$email** **)**

	Creates an account with given arguments. On success the account will be signed in directly. Otherwise an `Exception` is thrown with information about reasons in the exception-message.

- *(bool)* **signout()**

	Depending on `method`:
	
	- *session* : Removes the session-key from active session.
	- *cookie* : Removes the cookie by cookie-key.
	- *header* : Removes the token from account-database and stops sending the token in response-header.

- *(bool)* **destroy()**
	
	Removes the account from database and fires `signout()`.
	
- *(bool)* **isOnline()**

	Returns true if account is signedin, otherwise false. If header-method is used this function sets the response-code 401 if not signed in.

##License##
MIT

##Author##
- [mail@platdesign.de](mailto:mail@platdesign.de)
- [Twitter](http://twitter.com/platdesign)
- [GitHub](http://github.com/platdesign)












