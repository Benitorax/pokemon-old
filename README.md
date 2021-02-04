# Pokemon Application
<p>This application fetchs some data from <a href="https://pokeapi.co">https://pokeapi.co</a> like the name, image, evolution and habitat of pokemons.</p>
<p>It only includes the first generation, so the first 151 pokemons, from Bulbasaur to Mew.</p>
<p>It unlock new features or increase the difficulty by progressing as the authorization to participate in the tournament after obtaining 3 pokemons or the infirmary service that becomes paid from 4 pokemons obtained.</p>
<p>Trainers in tournament are auto generated. Users can only interact by exchanging pokemons. They can also leave messages in the contact page that only the admin users can read.<br/>
Admin users can also delete accounts which have not been activated.</p>
<p>The application use React for the battle pages (adventure and tournament).</p>

## Database and email configuration
<p>
  Update the config in .env file for your database by defining the <code>DATABASE_URL=</code>.<br>
  In .env file you can also config an email address by defining <code>MAILER_URL=</code>, then you have to comment or delete the line with <code>disable_delivery: true</code> in swiftmailer.yaml. <br>
  If you don't config an email address, you need to check your emails in the profiler to activate the account you register.
</p>

<p>To install dependecies:<br/>
  <code>composer install</code><br/>
  <br/>
  Then, create the database:<br/>
  <code>php bin/console doctrine:database:create</code><br/>
  <code>php bin/console doctrine:schema:create</code><br/>
  <code>php bin/console doctrine:schema:update --force</code><br/>
  <br/>
  To install packages:<br/>
  <code>npm install</code><br/>
  <br/>
  To build the assets for CSS, Javascript and React:<br/>
  <code>npm run dev</code><br/>
  <br/>
  To launch the server:<br>
  <code>composer require --dev symfony/web-server-bundle</code><br>
  <code>php bin/console server:start</code><br/>
  <br/>
  If you have Symfony CLI Tool, you can launch the server with:<br>
  <code>symfony server:start</code><br/>
  <br/>
  You need to add a MAILER_DSN variable in your env file.<br/>
  If you don't want to send emails, you can configure your mailer:<br/>
  <code>dsn: 'null://null'</code> instead of <code>dsn: '%env(MAILER_DSN)%'</code><br/>
  <br/>
  Finally you have to register this site on your Recaptcha's admin panel and fill these variables in .env file:<br>
  <code>GOOGLE_RECAPTCHA_SITE_KEY=</code><br/>
  <code>GOOGLE_RECAPTCHA_SECRET=</code>
</p>

## Application
<p>You can:</p>
<ul>
  <li>create an account user</li>
  <li>select a pokemon when you register</li>
  <li>reset your password if you don't remember it</li>
  <li>log in</li>
  <li>modify your password</li>
  <li>capture pokemons</li>
  <li>buy items (pokeballs, healing potions)</li>
  <li>restore your pokemons in the infirmary</li>
  <li>participate in tournament (you need 3 pokemons in good shape)</li>
  <li>earn more money by winning a battle or the tournament</li>
  <li>exchange pokemons with another trainer</li>
  <li>delete your account</li>
  <li>leave a message in contact page</li>
  <li>read messages sent by users (admin only)</li>
</ul>
<br>
<p>It send an email:</p>
<ul>
  <li>to activate your account/to confirm your email address</li>
  <li>to reset your password if forgotten</li>
  <li>to confirm that your password has been changed</li>
  <li>to inform you that a trainer request to exchange pokemons with you</li>
  <li>to inform you that a trainer modify a request to exchange pokemons</li>
  <li>to inform you that a trainer accept or refuse a request to exchange pokemons</li>
</ul>
<br>
<p>A pokemon can:</p>
<ul>
  <li>be easier to capture if damaged</li>
  <li>fight another pokemon</li>
  <li>be healed with healing potion or infirmary service</li>
  <li>faint when lose all health points</li>
  <li>be restored at the infirmary service</li>
  <li>level up after a victory</li>
  <li>do more damage when its level is higher</li>
  <li>evolve</li>
</ul>
<br/>
<p>An admin user can:</p>
<ul>
  <li>play like every users</li>
  <li>read messages sent by others users in contact page</li>
  <li>check and delete inactivated users</li>
  <li>check and delete any user (causing problems)</li>
</ul>
