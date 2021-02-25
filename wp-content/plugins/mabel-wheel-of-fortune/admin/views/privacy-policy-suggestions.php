<p>
	The following section will help you adapt your privacy policy to include how you are using WP Optin Wheel Pro.
	We're explaining some of our processes so that you can draft a well-informed privacy policy.
	Please note it is your own responsibility to write a correct policy, adhering to (inter)national laws.
	We are merely helping you on your way.
</p>

<h3>Cookies</h3>
<p>
	If you are limiting the number of times a user can play,
	then WP Optin Wheel uses cookies to prevent your visitors from seeing the wheel until they are allowed to see it again
	(depending on your wheel settings).
</p>
<p>The following cookie is set by the plugin:</p>
<ul>
	<li><b>Name: </b>wof-XX, where XX is the ID of your wheel. Example: wof-129.</li>
	<li><b>Value: </b>1.</li>
	<li><b>Expiry: </b> this depends on the wheel's settings.</li>
	<li><b>Use of the cookie:</b> to help determine whether or not the user is allowed to see the wheel on the frontend.</li>
</ul>

<h3>Storage</h3>
<p>
	In addition to using cookies, we may also use the browser's storage capabilities (sessionStorage or localStorage) to prevent people from seeing the popup when they shouldn't.
</p>

<h3>Data the Plugin Captures and Why</h3>
<p>
	WP Optin Wheel captures data in your WordPress database for 2 reasons:
</p>
<ul>
	<li>1) To prevent cheating.</li>
	<li>2) To offer export features to the site owner.</li>
</ul>
<p>Below is a list of information we store in your database:</p>
<ul>
	<li>- The player's email address (if applicable)</li>
	<li>- Other data they filled out on the opt-in form (if any).</li>
	<li>- The wheel ID this record belongs to.</li>
	<li>- A timestamp of when this record was inserted.</li>
	<li>- A flag denoting whether this record is an opt-in (= the user filled out the form on the wheel) or a play result (= the user won or lost).</li>
	<li>- Whether or not the game was won, and the associated prize.</li>
	<li>- A hashed IP address.</li>
</ul>

<p>We also track views and conversions anonymously. This is achieved by keeping a numerical counter.</p>

<h3>Where We Send the Data</h3>
<p>
	When you connect WP Optin Wheel to a 3rd party (as defined in the 'integrations' tab),
	such as MailChimp or ActiveCampaign, we send the data to that tool via their API.
	The 3rd party will store this data until the user is unsubscribed or deleted.
</p>
<p>
	If you're using Zapier, we also send the data to a Zapier webhook defined by you.
</p>
<p>
	If you selected 'validate emails' in your wheel's settings, WP Optin Wheel also sends the player's email address
	to an API hosted on studiowombat.com. The API is used to validate the email address and returns a simple true/false.
	We do not log or store any data.
</p>