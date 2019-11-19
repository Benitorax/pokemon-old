# Pokemon Application
<p>This application fetchs some data from <a href="https://pokeapi.co">https://pokeapi.co</a> needed like the name, image, evolution and habitat of pokemons.</p>
<p>It only includes the first generation, so the first 151 pokemons, from Bulbasaur to Mew.</p>
<p>Trainers in tournament are auto generated. Users can interact each other only by exchanging pokemon. They can also leave messages in contact page that only admin users can read.</p>

## Database and email configuration
<p>
  Update the config in .env file for your database by defining the <code>DATABASE_URL=</code>.<br>
  In .env file you can also config an email address by defining <code>MAILER_URL=</code>, then you have to comment or delete the line with <code>disable_delivery: true</code> in swiftmailer.yaml. <br>
  If you don't config an email address, you need to check your emails in the profiler to activate the account you register.
</p>

<p>Then, create the database:<br>
  <code>php bin/console doctrine:database:create</code><br>
  <code>php bin/console doctrine:schema:create</code><br>
  <code>php bin/console doctrine:schema:update --force</code>
  <br><br>
  To launch the server:<br>
  <code>composer require --dev symfony/web-server-bundle</code><br>
  <code>php bin/console server:start</code>
  <br><br>
  If you have Symfony CLI Tool, you can launch the server with:<br>
  <code>symfony server:start</code>
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
