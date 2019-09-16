# Pokemon Application

## Database
<p>Update the config in .env file for your database.</p>

<p>Create the database<br>
<code>php bin/console doctrine:database:create</code><br>
<code>php bin/console doctrine:schema:create</code><br>
<code>php bin/console doctrine:schema:update --force</code>
<br><br>
To launch the server<br>
<code>composer require --dev symfony/web-server-bundle</code><br>
<code>php bin/console server:start</code><br>

## Application
<p>You can:</p>
<ul>
  <li>create an account user</li>
  <li>select a pokemon when you register</li>
  <li>reset your password if you don't remember it</li>
  <li>log in</li>
  <li>modify your password</li>
  <li>capture pokemons</li>
  <li>buy items (pokeballs, health potions)</li>
  <li>participate in tournament</li>
  <li>delete your account</li>
</ul>
<br>
<p>It send an email:</p>
<ul>
  <li>to activate your account/to confirm your email address</li>
  <li>to reset your password if forgotten</li>
  <li>to confirm that your password has been changed</li>
</ul>
<br>
<p>A pokemon can:</p>
<ul>
  <li>be captured</li>
  <li>fight another pokemon</li>
  <li>be healed</li>
  <li>faint when lose all health points</li>
  <li>be restored</li>
  <li>level up after a victory</li>
  <li>evolve</li>
</ul>
