<?php exit; ?>
[2023-01-23 14:18:38] ERROR: Form 968 > Mailchimp API error: 400 Bad Request. Invalid Resource. test@te**.com looks fake or invalid, please enter a real email address.

Request: 
POST https://us10.api.mailchimp.com/3.0/lists/64c2511277/members

{"status":"pending","email_address":"test@te**.com","interests":{},"merge_fields":{"ADDRESS":{"addr1":"ok","city":"","state":"","zip":""},"FNAME":"tet","LNAME":"test","PHONE":"57577575"},"email_type":"html","ip_signup":"103.43.7.195","tags":[]}

Response: 
400 Bad Request
{"type":"https://mailchimp.com/developer/marketing/docs/errors/","title":"Invalid Resource","status":400,"detail":"test@te**.com looks fake or invalid, please enter a real email address.","instance":"24605f05-fbf7-0bfa-395a-66fb7c9dded3"}
[2023-01-23 14:18:52] ERROR: Form 968 > Mailchimp API error: 400 Bad Request. Invalid Resource. test@te**.com looks fake or invalid, please enter a real email address.

Request: 
POST https://us10.api.mailchimp.com/3.0/lists/64c2511277/members

{"status":"pending","email_address":"test@te**.com","interests":{},"merge_fields":{"ADDRESS":{"addr1":"ok","city":"","state":"","zip":""},"FNAME":"tet","LNAME":"test","PHONE":"57577575"},"email_type":"html","ip_signup":"103.43.7.195","tags":[]}

Response: 
400 Bad Request
{"type":"https://mailchimp.com/developer/marketing/docs/errors/","title":"Invalid Resource","status":400,"detail":"test@te**.com looks fake or invalid, please enter a real email address.","instance":"a0fb891f-818c-8f16-f2b1-43f9ecb312e9"}
[2023-01-23 14:26:47] ERROR: Form 968 > Mailchimp API error: 400 Bad Request. Invalid Resource. Your merge fields were invalid. 
- ADDRESS : Please enter a complete address

Request: 
POST https://us10.api.mailchimp.com/3.0/lists/64c2511277/members

{"status":"pending","email_address":"gopa*******@iq*********.com","interests":{},"merge_fields":{"ADDRESS":{"addr1":"Test Form","city":"","state":"","zip":""},"FNAME":"adom","LNAME":"james","PHONE":"12345677890"},"email_type":"html","ip_signup":"103.43.7.195","tags":[]}

Response: 
400 Bad Request
{"type":"https://mailchimp.com/developer/marketing/docs/errors/","title":"Invalid Resource","status":400,"detail":"Your merge fields were invalid.","instance":"ccf304cc-60e2-533e-0a13-8a4e557c4b52","errors":[{"field":"ADDRESS","message":"Please enter a complete address"}]}
[2023-01-24 04:53:31] ERROR: Form 968 > Mailchimp API error: 400 Bad Request. Invalid Resource. Your merge fields were invalid. 
- ADDRESS : Please enter a complete address

Request: 
POST https://us10.api.mailchimp.com/3.0/lists/64c2511277/members

{"status":"pending","email_address":"gopa*******@iq*********.com","interests":{},"merge_fields":{"ADDRESS":{"addr1":"Test Email","city":"","state":"","zip":""},"FNAME":"adom","LNAME":"james","PHONE":"12345677890"},"email_type":"html","ip_signup":"103.43.7.195","tags":[]}

Response: 
400 Bad Request
{"type":"https://mailchimp.com/developer/marketing/docs/errors/","title":"Invalid Resource","status":400,"detail":"Your merge fields were invalid.","instance":"7826c375-53ee-7732-6720-d8e83343f0db","errors":[{"field":"ADDRESS","message":"Please enter a complete address"}]}
